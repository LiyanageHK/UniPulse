<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyChecking extends Model
{
    
    protected $fillable = [
        'user_id',
        'overall_mood',
        'felt_supported',
        'emotion_description',
        'trouble_sleeping',
        'hard_to_focus',
        'open_to_counselor',
        'know_access_support',
        'feeling_tense',
        'worrying',
        'interact_peers',
        'keep_up_workload',
        'group_activities',
        'academic_challenges',
        'feel_left_out',
        'no_one_to_talk',
        'no_energy',
        'little_pleasure',
        'feeling_down',
        'emotionally_drained',
        'going_through_motions',
    ];

    protected $casts = [
        'group_activities' => 'array',
        'academic_challenges' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateAreaScores()
    {
        $scores = [];

        // Stress & Anxiety
        $stressFields = ['trouble_sleeping','feeling_tense','worrying','keep_up_workload'];
        $scores['stress'] = $this->averageFields($stressFields);

        // Depression / Low Mood
        $depressionFields = ['no_energy', 'little_pleasure', 'feeling_down', 'overall_mood'];
        $scores['depression'] = $this->averageFields($depressionFields);

        // Disengagement / Burnout
        $disengagementFields = ['emotionally_drained', 'going_through_motions', 'hard_to_focus', 'felt_supported'];
        $scores['disengagement'] = $this->averageFields($disengagementFields);

        // Social Isolation (reverse first item if needed)
        $socialFields = ['feel_left_out', 'no_one_to_talk', 'interact_peers'];
        $socialScores = $this->getFieldValues($socialFields, true); // second param = reverse arrays
        $scores['social_isolation'] = array_sum($socialScores) / count($socialScores);

        // Openness (reverse items if needed)
        $opennessFields = ['open_to_counselor', 'know_access_support'];
        $opennessScores = $this->getFieldValues($opennessFields, true);
        $scores['openness'] = array_sum($opennessScores) / count($opennessScores);

        return $scores;
    }

    private function averageFields($fields)
    {
        $values = $this->getFieldValues($fields);
        return count($values) ? array_sum($values) / count($values) : 0;
    }

    private function getFieldValues($fields, $reverse = false)
    {
        $values = [];
        foreach ($fields as $field) {
            $value = $this->{$field};

            if (is_array($value) || is_string($value) && $this->isJson($value)) {
                $value = json_decode($value, true);
                // Take average if array
                $value = count($value) ? array_sum($value) / count($value) : 0;
            } elseif (!is_numeric($value)) {
                $value = 0;
            }

            if ($reverse) {
                $value = 6 - $value;
            }

            $values[] = $value;
        }
        return $values;
    }

    private function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function getLast4WeeksScores($userId)
    {
        $last4 = self::where('user_id', $userId)
            ->latest('created_at')
            ->take(4)
            ->get();

        $areas = ['stress', 'depression', 'disengagement', 'social_isolation', 'openness'];
        $weeklyScores = [];

        foreach ($areas as $area) {
            $weeklyScores[$area] = [];
            foreach ($last4 as $week) {
                $scores = $week->calculateAreaScores();
                $weeklyScores[$area][] = $scores[$area];
            }
            // pad if < 4 weeks
            while (count($weeklyScores[$area]) < 4) {
                $weeklyScores[$area][] = end($weeklyScores[$area]);
            }
        }

        return $weeklyScores;
    }

    public static function calculateWeightedScores($weeklyScores)
    {
        $weights = [0.5, 0.2, 0.2, 0.1];
        $weightedScores = [];

        foreach ($weeklyScores as $area => $scores) {
            $weightedScores[$area] = 0;
            foreach ($scores as $i => $score) {
                $weightedScores[$area] += $score * $weights[$i];
            }
        }
        return $weightedScores;
    }

    public static function detectTrend($weeklyScores)
    {
        $trends = [];
        foreach ($weeklyScores as $area => $scores) {
            $d1 = $scores[0] - $scores[1];
            $d2 = $scores[1] - $scores[2];
            $d3 = $scores[2] - $scores[3];
            $sum = $d1 + $d2 + $d3;

            if ($d1 <= -2) $trend = 'Sudden Drop';
            elseif ($sum >= 2) $trend = 'Improving';
            elseif ($sum <= -2) $trend = 'Worsening';
            else $trend = 'Stable';

            $trends[$area] = $trend;
        }
        return $trends;
    }

    public static function determineRisk($area, $score)
    {
        switch ($area) {
            case 'stress':
            case 'depression':
            case 'disengagement':
                if ($score > 3.5) return 'High';
                elseif ($score >= 2.5) return 'Moderate';
                else return 'Low';
            case 'social_isolation':
            case 'openness':
                if ($score < 2.5) return 'High';
                elseif ($score <= 3.5) return 'Moderate';
                else return 'Low';
        }
        return 'Low';
    }

    public static function getSuggestionPriority($risk, $trend)
    {
        $map = [
            'High' => ['Worsening' => 1, 'Stable' => 1, 'Improving' => 2],
            'Moderate' => ['Worsening' => 1, 'Stable' => 2, 'Improving' => 3],
            'Low' => ['Worsening' => 2, 'Stable' => 3, 'Improving' => 4],
        ];
        return $map[$risk][$trend] ?? 4;
    }
}
