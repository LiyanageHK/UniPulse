<?php

namespace App\Services;

class KpiCalculator
{
    public function calculateFromWeeklyData($weeklyData)
    {
        $studentKPIs = [];
        
        foreach ($weeklyData as $row) {
            try {
                $kpis = $this->calculateStudentKPIs($row);
                $studentKPIs[] = $kpis;
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $studentKPIs;
    }
    
    private function calculateStudentKPIs($student)
    {
        // Calculate all three KPIs
        $motivationScore = $this->calculateMotivationScore($student);
        $socialScore = $this->calculateSocialScore($student);
        $emotionalScore = $this->calculateEmotionalScore($student);
        
        return [
            'student_name' => $student['Name ( Use the First Name & Last Name as the onboarding form )'] ?? 'Unknown',
            'week' => $this->extractWeekFromTimestamp($student['Timestamp'] ?? ''),
            'motivation' => round($motivationScore, 2),
            'social' => round($socialScore, 2),
            'emotional' => round($emotionalScore, 2),
            'motivation_interpretation' => $this->interpretMotivation($motivationScore),
            'social_interpretation' => $this->interpretSocial($socialScore),
            'emotional_interpretation' => $this->interpretEmotional($emotionalScore)
        ];
    }

    // Extract week from timestamp (e.g., "Week of Nov 18")
    private function extractWeekFromTimestamp($timestamp)
    {
        if (empty($timestamp)) return 'Unknown';
        
        try {
            $date = new \DateTime($timestamp);
            return 'Week of ' . $date->format('M d');
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    // --- MOTIVATION FORMULA ---
    private function calculateMotivationScore($student)
    {
        // Studies Interesting (scale 1-5)
        $studiesInteresting = floatval($student['I found my studies interesting and engaging this week.'] ?? 3);
        
        // Academic Confidence (scale 1-5)
        $academicConfidence = floatval($student['I felt confident in my ability to succeed academically.'] ?? 3);
        
        // Workload Management (scale 1-5)
        $workloadManagement = floatval($student['I was able to keep up with my academic workload this week.'] ?? 3);
        
        // No Energy/Motivation (reverse scale: 6 - value)
        $noEnergy = floatval($student['I often feel like I have no energy or motivation.'] ?? 3);
        $reverseNoEnergy = 6 - $noEnergy;
        
        // Hard to Focus (reverse scale: 6 - value)
        $hardToFocus = floatval($student['I find it hard to stay focused on academic tasks.'] ?? 3);
        $reverseHardToFocus = 6 - $hardToFocus;
        
        // Calculate final score
        return ($studiesInteresting + $academicConfidence + $workloadManagement + 
                $reverseNoEnergy + $reverseHardToFocus) / 5;
    }

    // --- SOCIAL INCLUSION FORMULA ---
    private function calculateSocialScore($student)
    {
        // Peer Connection (scale 1-5)
        $peerConnection = floatval($student['I feel connected to at least one friend or peer in university.'] ?? 3);
        
        // Peer Interaction Frequency (scale 1-5)
        $peerInteraction = floatval($student['How often did you interact with peers outside class this week?'] ?? 3);
        
        // University Belonging (scale 1-5)
        $universityBelonging = floatval($student['I felt I belonged to the university community.'] ?? 3);
        
        // Meaningful Connections (scale 1-5)
        $meaningfulConnections = floatval($student['I had meaningful connections with peers this week.'] ?? 3);
        
        // Feel Left Out (reverse scale: 6 - value)
        $feelLeftOut = floatval($student['I often feel left out or disconnected from others.'] ?? 3);
        $reverseFeelLeftOut = 6 - $feelLeftOut;
        
        // No One to Talk To (reverse scale: 6 - value)
        $noOneToTalk = floatval($student['I feel like I don\'t have anyone to talk to when I\'m struggling'] ?? 3);
        $reverseNoOneToTalk = 6 - $noOneToTalk;
        
        // Calculate final score
        return ($peerConnection + $peerInteraction + $universityBelonging + 
                $meaningfulConnections + $reverseFeelLeftOut + $reverseNoOneToTalk) / 6;
    }

    // --- EMOTIONAL FORMULA ---
    private function calculateEmotionalScore($student)
    {
        // Mood (scale 1-5)
        $mood = floatval($student['This week, my overall mood was'] ?? 3);
        
        // Reverse scores (6 - value)
        $reverseTensed = 6 - floatval($student['I\'ve been feeling tense or unable to relax.'] ?? 3);
        $reverseOverwhelmed = 6 - floatval($student['I get overwhelmed easily by academic tasks.'] ?? 3);
        $reverseWorried = 6 - floatval($student['I\'ve been worrying about many things lately.'] ?? 3);
        $reverseTroubleSleeping = 6 - floatval($student['I had trouble sleeping because of stress or thoughts'] ?? 3);
        $reverseLowPleasure = 6 - floatval($student['I find little pleasure or enjoyment in things I used to like.'] ?? 3);
        $reverseFeelingDown = 6 - floatval($student['I\'ve been feeling down, hopeless, or sad most of the time.'] ?? 3);
        $reverseEmotionallyDrained = 6 - floatval($student['I feel emotionally drained by my studies.'] ?? 3);
        $reverseJustThroughMotions = 6 - floatval($student['I feel like I\'m just going through the motions without interest.'] ?? 3);
        
        // Positive items (scale 1-5)
        $opennessToMentor = floatval($student['I would be open to talking to a mentor or counselor if I needed help'] ?? 3);
        $knowledgeOfSupport = floatval($student['I know how to access mental health support if I needed it'] ?? 3);
        
        // Calculate final score 
        return ($mood + $reverseTensed + $reverseOverwhelmed + $reverseWorried + 
                $reverseTroubleSleeping + $opennessToMentor + $knowledgeOfSupport + 
                $reverseLowPleasure + $reverseFeelingDown + $reverseEmotionallyDrained + 
                $reverseJustThroughMotions) / 11;
    }

    // --- Interpretation Methods ---
    private function interpretMotivation($score)
    {
        return $score >= 4.0 ? 'High' : ($score >= 2.5 ? 'Moderate' : 'Low');
    }

    private function interpretSocial($score)
    {
        return $score >= 4.0 ? 'Integrated' : ($score >= 2.5 ? 'Moderate' : 'Isolated');
    }

    private function interpretEmotional($score)
    {
        return $score >= 4.0 ? 'Stable' : ($score >= 2.5 ? 'Moderate' : 'At-risk');
    }
}