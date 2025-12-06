<?php

namespace App\Http\Controllers;

use App\Models\CrisisAlert;
use App\Models\CrisisFlag;
use App\Models\Conversation;
use App\Services\CrisisAlertService;
use Illuminate\Http\Request;

class CrisisManagementController extends Controller
{
    protected CrisisAlertService $crisisAlertService;

    public function __construct(CrisisAlertService $crisisAlertService)
    {
        $this->crisisAlertService = $crisisAlertService;
    }

    /**
     * List all crisis alerts (for counselor/admin dashboard).
     */
    public function listCrisisAlerts(Request $request)
    {
        $status = $request->query('status', 'pending');
        
        $alerts = CrisisAlert::with(['user', 'counselor', 'crisisFlag'])
            ->when($status !== 'all', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'alerts' => $alerts->map(function ($alert) {
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
        $alerts = $this->crisisAlertService->getCriticalAlerts();

        return response()->json([
            'success' => true,
            'count' => $alerts->count(),
            'alerts' => $alerts->map(function ($alert) {
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
        $alert = CrisisAlert::findOrFail($id);
        $counselorId = $request->user()->id ?? null;

        $this->crisisAlertService->acknowledgeAlert($alert, $counselorId);

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
        $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        $alert = CrisisAlert::findOrFail($id);
        
        $this->crisisAlertService->resolveAlert($alert, $request->notes);

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
        $conversation = Conversation::with(['crisisFlags', 'user'])->findOrFail($conversationId);

        $flags = $conversation->crisisFlags->map(function ($flag) {
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
            'summary' => $conversation->crisisFlags->groupBy('severity')->map->count(),
        ]);
    }

    /**
     * Mark a flag as reviewed.
     */
    public function reviewFlag(Request $request, $flagId)
    {
        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $flag = CrisisFlag::findOrFail($flagId);
        
        $flag->update([
            'reviewed' => true,
            'reviewed_by' => $request->user()->id ?? null,
            'reviewed_at' => now(),
            'reviewer_notes' => $request->notes,
        ]);

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
        $stats = [
            'total_flags' => CrisisFlag::count(),
            'red_flags' => CrisisFlag::red()->count(),
            'yellow_flags' => CrisisFlag::yellow()->count(),
            'blue_flags' => CrisisFlag::blue()->count(),
            'pending_alerts' => CrisisAlert::pending()->count(),
            'critical_alerts' => CrisisAlert::critical()->pending()->count(),
            'flags_last_24h' => CrisisFlag::where('created_at', '>=', now()->subDay())->count(),
            'flags_by_category' => CrisisFlag::selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category'),
            'recent_flags' => CrisisFlag::with(['user', 'conversation'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($flag) {
                    return [
                        'id' => $flag->id,
                        'student' => $flag->user->name,
                        'severity' => $flag->getSeverityLabel(),
                        'category' => $flag->getCategoryLabel(),
                        'created_at' => $flag->created_at->diffForHumans(),
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
