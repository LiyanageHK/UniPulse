<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Services\FeedbackValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    // Service used to validate feedback using AI/LLM logic.
    protected FeedbackValidationService $validationService;

    // Inject the validation service into the controller.
    public function __construct(FeedbackValidationService $validationService)
    {
        // Store the service for later use in feedback submission methods.
        $this->validationService = $validationService;
    }

    /**
     * Submit new feedback.
     */
    public function store(Request $request)
    {
        // Validate the incoming authenticated-user feedback request.
        $request->validate([
            'content' => 'required|string|min:20|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'show_name' => 'boolean',
        ]);

        // Get the currently authenticated user.
        $user = Auth::user();

        try {
            // Validate content with LLM
            // Ask the validation service to score the feedback content.
            $validation = $this->validationService->validateFeedback(
                $request->content,
                $request->rating
            );

            // Create feedback
            // Save the feedback record with AI validation results.
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
            // Automatically approve feedback when the model and rules allow it.
            if ($feedback->canAutoApprove()) {
                // Mark the feedback as approved and store the approval time.
                $feedback->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);
            }

            // Log the successful feedback submission.
            Log::info('Feedback submitted', [
                'feedback_id' => $feedback->id,
                'rating' => $feedback->rating,
                'llm_score' => $validation['score'],
                'status' => $feedback->status,
            ]);

            // Return a success response to the client.
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
            // Log any failure that occurs during feedback submission.
            Log::error('Feedback submission failed', [
                'error' => $e->getMessage(),
            ]);

            // Return a generic error response.
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
        // Validate guest feedback input fields.
        $request->validate([
            'content' => 'required|string|min:1|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'guest_name' => 'required|string|max:100',
            'guest_email' => 'nullable|email|max:255',
            'show_name' => 'boolean',
        ]);

        try {
            // Validate content with LLM
            // Send the guest feedback to the validation service.
            $validation = $this->validationService->validateFeedback(
                $request->content,
                $request->rating
            );

            // Create guest feedback
            // Store the guest feedback in the database.
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
            // Automatically approve the feedback if it passes the rules.
            if ($feedback->canAutoApprove()) {
                // Update the feedback status to approved.
                $feedback->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                ]);
            }

            // Log the guest feedback submission.
            Log::info('Guest feedback submitted', [
                'feedback_id' => $feedback->id,
                'guest_name' => $request->guest_name,
                'rating' => $feedback->rating,
                'llm_score' => $validation['score'],
                'status' => $feedback->status,
            ]);

            // Return a success response to the guest.
            return response()->json([
                'success' => true,
                'message' => $feedback->status === 'approved' 
                    ? 'Thank you! Your feedback has been published.' 
                    : 'Thank you! Your feedback has been submitted for review.',
            ]);

        } catch (\Exception $e) {
            // Log the guest submission failure.
            Log::error('Guest feedback submission failed', [
                'guest_name' => $request->guest_name,
                'error' => $e->getMessage(),
            ]);

            // Return a generic failure response.
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
        // Read the maximum number of feedback items to return.
        $limit = $request->query('limit', 6);

        // Load approved, highly rated feedback for public display.
        $feedbacks = Feedback::approved()
            ->highRated()
            ->orderBy('approved_at', 'desc')
            // Cap the result count to prevent overly large responses.
            ->limit(min($limit, 12))
            ->get()
            ->map(function ($feedback) {
                // Format each feedback item for the UI.
                return [
                    'id' => $feedback->id,
                    'content' => $feedback->content,
                    'rating' => $feedback->rating,
                    'display_name' => $feedback->display_name,
                    'display_initial' => $feedback->display_initial,
                    'approved_at' => $feedback->approved_at->diffForHumans(),
                ];
            });

        // Return the approved feedback list.
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
        // Return a simple response indicating feedback submission is allowed.
        return response()->json([
            'success' => true,
            'can_submit' => true,
            'last_feedback' => null,
        ]);
    }
}
