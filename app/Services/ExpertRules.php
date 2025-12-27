<?php

namespace App\Services;

class ExpertRules
{
    public function getRecommendation($motivation, $social, $emotional)
    {
        // MORE BALANCED RULES:
        
        // Priority 1: Critical emotional risk
        if ($emotional < 3.0) {
            return 'risk_detection';
        }
        
        // Priority 2: Social isolation  
        if ($social < 3.0) {
            return 'peer_matching';
        }
        
        // Priority 3: Motivation issues
        if ($motivation < 3.0) {
            return 'conversational_support';
        }
        
        // Only show encouragement for truly high scores
        if ($motivation > 4.0 && $social > 4.0 && $emotional > 4.0) {
            return 'encouragement';
        }
        
        // Default to conversational support for moderate cases
        return 'conversational_support';
    }
}