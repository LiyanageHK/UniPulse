<?php

namespace App\Services;

use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\PersistentModel;
use Rubix\ML\Datasets\Unlabeled;
use Illuminate\Support\Facades\Route;

class AiRecommender
{
    protected PersistentModel $model;

    public function __construct()
    {
        // Load the trained Rubix ML model
        $persister = new Filesystem(storage_path('app/trained_model.rbx'));
        $this->model = PersistentModel::load($persister);
    }

    /**
     * Recommend a component or action based on KPI scores
     *
     * @param float $motivation
     * @param float $social
     * @param float $emotional
     * @return array
     */
    public function recommend(float $motivation, float $social, float $emotional): array
    {
        // --- Optional override for perfect KPIs ---
        if ($motivation === 5.0 && $social === 5.0 && $emotional === 5.0) {
            return [
                'type' => 'encouragement',
                'link' => null,
                'message' => "🎉 Amazing! All your KPIs are perfect. Keep up the great work!"
            ];
        }

        // Wrap input in an Unlabeled dataset (required by Rubix ML)
        $dataset = new Unlabeled([
            [$motivation, $social, $emotional]
        ]);

        // Get the ML prediction
        $predictionArray = $this->model->predict($dataset);
        $prediction = $predictionArray[0] ?? 'unknown';

        // Map predictions to component links or placeholders
        $recommendationMap = [
            'peer_matching' => Route::has('components.peer_matching') ? route('components.peer_matching') : '#',
            // Route to the Chat Support page when conversational support is recommended
            'conversational_support' => Route::has('chat.support') ? route('chat.support') : '#',
            'encouragement' => null, // handled as messages
            'risk_detection' => Route::has('components.risk_detection') ? route('components.risk_detection') : '#',
        ];

        // Encouragement messages (for non-perfect cases)
        $encouragementMessages = [
            "Keep going! You're making progress every week.",
            "Remember to take breaks and recharge your energy.",
            "Your efforts are paying off – stay motivated!",
            "Connect with peers or mentors to boost your confidence."
        ];

        return [
            'type' => $prediction,
            'link' => $recommendationMap[$prediction] ?? '#',
            'message' => $prediction === 'encouragement'
                ? $encouragementMessages[array_rand($encouragementMessages)]
                : null
        ];
    }
}
