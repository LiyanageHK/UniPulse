<?php

namespace App\Http\Controllers;

use App\Models\CrisisAlert;
use App\Models\CrisisFlag;
use App\Models\Conversation;
use App\Services\CrisisAlertService;
use Illuminate\Http\Request;

class CrisisManagementController extends Controller
{
    // Service used to handle crisis alert operations.
    protected CrisisAlertService $crisisAlertService;

    // Inject the crisis alert service into the controller.
    public function __construct(CrisisAlertService $crisisAlertService)
    {
        // Store the service for later use in controller actions.
        $this->crisisAlertService = $crisisAlertService;
    }

    /**
     * List all crisis alerts (for counselor/admin dashboard).
     */
    public function listCrisisAlerts(Request $request)
    {
        // Read the requested alert status, defaulting to pending.
        $status = $request->query('status', 'pending');
        
        // Load alerts together with related user, counselor, and crisis flag data.
        $alerts = CrisisAlert::with(['user', 'counselor', 'crisisFlag'])
            ->when($status !== 'all', function ($query) use ($status) {
                // Filter by status unless all alerts are requested.
                return $query->where('status', $status);
            })
            // Sort by highest priority first.
            ->orderBy('priority', 'desc')
            // Show newest alerts first.
            ->orderBy('created_at', 'desc')
            // Paginate the result set for the dashboard.
            ->paginate(50);

        // Return the alert list and pagination metadata.
        return response()->json([
            'success' => true,
            'alerts' => $alerts->map(function ($alert) {
                // Convert each alert into a front-end friendly structure.
                return [
                    'id' => $alert->id,
                    'student' => [
                        'id' => $alert->user->id,
                        'name' => $alert->user->name,
                        'email' => $alert->user->email,
                    ],
                    'crisis_flag' => [
                        'severity' => $alert->crisisFlag->severity,
                        'severity_label' => $alert->crisisFlag->getSeverityLabel(),
                        'category' => $alert->crisisFlag->category,
                        'category_label' => $alert->crisisFlag->getCategoryLabel(),
                        'keywords' => $alert->crisisFlag->detected_keywords,
                        'context' => $alert->crisisFlag->context_snippet,
                    ],
                    'counselor' => $alert->counselor ? [
                        'id' => $alert->counselor->id,
                        'name' => $alert->counselor->name,
                        'email' => $alert->counselor->email,
                    ] : null,
                    'status' => $alert->status,
                    'priority' => $alert->priority,
                    'created_at' => $alert->created_at,
                    'acknowledged_at' => $alert->acknowledged_at,
                    'resolved_at' => $alert->resolved_at,
                ];
            }),
            'pagination' => [
                'total' => $alerts->total(),
                'per_page' => $alerts->perPage(),
                'current_page' => $alerts->currentPage(),
                'last_page' => $alerts->lastPage(),
            ],
        ]);
    }

    /**
     * Get critical alerts (red flags only).
     */
    public function getCriticalAlerts()
    {
        // Fetch only critical alerts from the service layer.
        $alerts = $this->crisisAlertService->getCriticalAlerts();

        // Return the alert count and formatted alert list.
        return response()->json([
            'success' => true,
            'count' => $alerts->count(),
            'alerts' => $alerts->map(function ($alert) {
                // Simplify each critical alert for display.
                return [
                    'id' => $alert->id,
                    'student_name' => $alert->user->name,
                    'category' => $alert->crisisFlag->getCategoryLabel(),
                    'created_at' => $alert->created_at->diffForHumans(),
                    'status' => $alert->status,
                ];
            }),
        ]);
    }

    /**
     * Acknowledge a crisis alert.
     */
    public function acknowledgeAlert(Request $request, $id)
    {
        // Retrieve the crisis alert by ID.
        $alert = CrisisAlert::findOrFail($id);
        // Use the authenticated user as the counselor identifier if available.
        $counselorId = $request->user()->id ?? null;

        // Mark the alert as acknowledged through the service.
        $this->crisisAlertService->acknowledgeAlert($alert, $counselorId);

        // Return a success response.
        return response()->json([
            'success' => true,
            'message' => 'Alert acknowledged successfully',
        ]);
    }

    /**
     * Resolve a crisis alert.
     */
    public function resolveAlert(Request $request, $id)
    {
        // Validate the resolution notes provided by the counselor.
        $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        // Retrieve the alert to be resolved.
        $alert = CrisisAlert::findOrFail($id);
        
        // Pass the alert and notes to the service layer for resolution.
        $this->crisisAlertService->resolveAlert($alert, $request->notes);

        // Confirm successful resolution.
        return response()->json([
            'success' => true,
            'message' => 'Alert resolved successfully',
        ]);
    }

    /**
     * View all crisis flags for a specific conversation.
     */
    public function viewConversationFlags($conversationId)
    {
        // Load the conversation with its crisis flags and owner.
        $conversation = Conversation::with(['crisisFlags', 'user'])->findOrFail($conversationId);

        // Transform each crisis flag into a readable response payload.
        $flags = $conversation->crisisFlags->map(function ($flag) {
            // Return the relevant details for one flag.
            return [
                'id' => $flag->id,
                'severity' => $flag->severity,
                'severity_label' => $flag->getSeverityLabel(),
                'category' => $flag->category,
                'category_label' => $flag->getCategoryLabel(),
                'detected_keywords' => $flag->detected_keywords,
                'context_snippet' => $flag->context_snippet,
                'confidence_score' => $flag->confidence_score,
                'escalated' => $flag->escalated,
                'reviewed' => $flag->reviewed,
                'created_at' => $flag->created_at,
            ];
        });

        // Return conversation details, all flags, and a severity summary.
        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'student' => [
                    'id' => $conversation->user->id,
                    'name' => $conversation->user->name,
                ],
            ],
            'flags' => $flags,
            // Group flags by severity and count them for quick analysis.
            'summary' => $conversation->crisisFlags->groupBy('severity')->map->count(),
        ]);
    }

    /**
     * Mark a flag as reviewed.
     */
    public function reviewFlag(Request $request, $flagId)
    {
        // Validate optional reviewer notes.
        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        // Fetch the crisis flag to be reviewed.
        $flag = CrisisFlag::findOrFail($flagId);
        
        // Update the flag with review metadata.
        $flag->update([
            'reviewed' => true,
            'reviewed_by' => $request->user()->id ?? null,
            'reviewed_at' => now(),
            'reviewer_notes' => $request->notes,
        ]);

        // Return a success message.
        return response()->json([
            'success' => true,
            'message' => 'Flag marked as reviewed',
        ]);
    }

    /**
     * Get crisis statistics dashboard data.
     */
    public function getDashboardStats()
    {
        // Build a statistics array for the crisis dashboard.
        $stats = [
            // Total number of crisis flags in the system.
            'total_flags' => CrisisFlag::count(),
            // Number of red severity flags.
            'red_flags' => CrisisFlag::red()->count(),
            // Number of yellow severity flags.
            'yellow_flags' => CrisisFlag::yellow()->count(),
            // Number of blue severity flags.
            'blue_flags' => CrisisFlag::blue()->count(),
            // Count alerts that are still pending.
            'pending_alerts' => CrisisAlert::pending()->count(),
            // Count critical alerts that are still pending.
            'critical_alerts' => CrisisAlert::critical()->pending()->count(),
            // Count flags created in the last 24 hours.
            'flags_last_24h' => CrisisFlag::where('created_at', '>=', now()->subDay())->count(),
            // Count flags grouped by category.
            'flags_by_category' => CrisisFlag::selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category'),
            // Load the most recent flags with user and conversation data.
            'recent_flags' => CrisisFlag::with(['user', 'conversation'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($flag) {
                    // Format each recent flag for the dashboard.
                    return [
                        'id' => $flag->id,
                        'student' => $flag->user->name,
                        'severity' => $flag->getSeverityLabel(),
                        'category' => $flag->getCategoryLabel(),
                        'created_at' => $flag->created_at->diffForHumans(),
                    ];
                }),
        ];

        // Return the compiled dashboard statistics.
        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
