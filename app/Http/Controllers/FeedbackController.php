<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Services\FeedbackValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    protected FeedbackValidationService $validationService;

    public function __construct(FeedbackValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Submit new feedback.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|min:20|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'show_name' => 'boolean',
        ]);

        $user = Auth::user();

        try {
            // Validate content with LLM
            $validation = $this->validationService->validateFeedback(
                $request->content,
                $request->rating
            );

            // Create feedback
            $feedback = Feedback::create([
                'content' => $request->content,
                'guest_name' => $user->name,
                'guest_email' => $user->email,
                'rating' => $request->rating,
                'show_name' => $request->show_name ?? true,
                'llm_validation_score' => $validation['score'],
                'llm_validation_notes' => $validation['notes'],
                'status' => 'pending',
            ]);

            // Auto-approve if conditions met
            if ($feedback->canAutoApprove()) {
                $feedback->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);
            }

            Log::info('Feedback submitted', [
                'feedback_id' => $feedback->id,
                'rating' => $feedback->rating,
                'llm_score' => $validation['score'],
                'status' => $feedback->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => $feedback->status === 'approved' 
                    ? 'Thank you! Your feedback has been published.' 
                    : 'Thank you! Your feedback has been submitted for review.',
                'feedback' => [
                    'id' => $feedback->id,
                    'status' => $feedback->status,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Feedback submission failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to submit feedback. Please try again.',
            ], 500);
        }
    }

    /**
     * Submit feedback as a guest (no login required).
     */
    public function storeGuest(Request $request)
    {
        $request->validate([
            'content' => 'required|string|min:1|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'guest_name' => 'required|string|max:100',
            'guest_email' => 'nullable|email|max:255',
            'show_name' => 'boolean',
        ]);

        try {
            // Validate content with LLM
            $validation = $this->validationService->validateFeedback(
                $request->content,
                $request->rating
            );

            // Create guest feedback
            $feedback = Feedback::create([
                'guest_name' => $request->guest_name,
                'guest_email' => $request->guest_email,
                'content' => $request->content,
                'rating' => $request->rating,
                'show_name' => $request->show_name ?? true,
                'llm_validation_score' => $validation['score'],
                'llm_validation_notes' => $validation['notes'],
                'status' => 'pending',
            ]);

            // Auto-approve if conditions met
            if ($feedback->canAutoApprove()) {
                $feedback->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);
            }

            Log::info('Guest feedback submitted', [
                'feedback_id' => $feedback->id,
                'guest_name' => $request->guest_name,
                'rating' => $feedback->rating,
                'llm_score' => $validation['score'],
                'status' => $feedback->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => $feedback->status === 'approved' 
                    ? 'Thank you! Your feedback has been published.' 
                    : 'Thank you! Your feedback has been submitted for review.',
            ]);

        } catch (\Exception $e) {
            Log::error('Guest feedback submission failed', [
                'guest_name' => $request->guest_name,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to submit feedback. Please try again.',
            ], 500);
        }
    }

    /**
     * Get approved feedback for public display (home page).
     */
    public function getApproved(Request $request)
    {
        $limit = $request->query('limit', 6);

        $feedbacks = Feedback::approved()
            ->highRated()
            ->orderBy('approved_at', 'desc')
            ->limit(min($limit, 12))
            ->get()
            ->map(function ($feedback) {
                return [
                    'id' => $feedback->id,
                    'content' => $feedback->content,
                    'rating' => $feedback->rating,
                    'display_name' => $feedback->display_name,
                    'display_initial' => $feedback->display_initial,
                    'approved_at' => $feedback->approved_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'feedbacks' => $feedbacks,
        ]);
    }

    /**
     * Check if user has already submitted feedback.
     */
    public function checkStatus()
    {
        return response()->json([
            'success' => true,
            'can_submit' => true,
            'last_feedback' => null,
        ]);
    }
}
