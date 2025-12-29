<?php

namespace App\Services;

class ExpertRules
{
    public function getRecommendation($motivation, $social, $emotional)
    {
        // Round scores to 2 decimal places for consistency
        $motivation = round($motivation, 2);
        $social = round($social, 2);
        $emotional = round($emotional, 2);
        
        // 1. EMERGENCY OVERRIDE (Highest Priority) - emotional ≤ 2.0
        if ($emotional <= 2.0) {
            return 'risk_detection';
        }
        
        // 2. SOCIAL ISOLATION INTERVENTIONS
        // If social is low (≤ 2.4) AND emotional is also problematic (≤ 3.0)
        if ($social <= 2.4 && $emotional <= 3.0) {
            return 'peer_matching';
        }
        
        // If social is low (≤ 2.4)
        if ($social <= 2.4) {
            return 'peer_matching';
        }
        
        // If social is moderate (2.5 ≤ social ≤ 3.0)
        if ($social <= 3.0 && $social > 2.4) {
            return 'peer_matching';
        }
        
        // 3. MOTIVATION SUPPORT (only if no social issues above)
        if ($motivation <= 2.4) {
            return 'conversational_support';
        }
        
        // 4. MODERATE CONCERNS (non-social)
        if ($motivation <= 3.0 && $motivation > 2.4) {
            return 'conversational_support';
        }
        
        if ($emotional <= 3.0 && $emotional > 2.4) {
            return 'conversational_support';
        }
        
        // 5. ENCOURAGEMENT - all KPIs ≥ 4.0
        if ($motivation >= 4.0 && $social >= 4.0 && $emotional >= 4.0) {
            return 'encouragement';
        }
        
        // 6. DEFAULT - for neutral/good scores (all > 3.0 but not all ≥ 4.0)
        return 'conversational_support';
    }
}