<?php

namespace App\Services;

class KpiCalculator
{
    public function calculateFromExcelData($excelData)
    {
        $studentKPIs = [];
        
        foreach ($excelData as $row) {
            try {
                $kpis = $this->calculateStudentKPIs($row);
                $studentKPIs[] = $kpis;
            } catch (\Exception $e) {
                // Skip rows with calculation errors
                continue;
            }
        }
        
        return $studentKPIs;
    }
    
    private function calculateStudentKPIs($student)
    {
        $kpiData = $this->calculateKPIsFromOnboarding($student);
        
        return [
            'student_id' => $student['Name ( First Name & Last Name )'] ?? 'Unknown',
            'motivation' => $kpiData['motivationScore'],
            'social' => $kpiData['socialScore'],
            'emotional' => $kpiData['emotionalScore'],
            'motivation_interpretation' => $kpiData['motivationInterpretation'],
            'social_interpretation' => $kpiData['socialInterpretation'],
            'emotional_interpretation' => $kpiData['emotionalInterpretation']
        ];
    }

    private function calculateKPIsFromOnboarding($student)
    {
        // Goal Clarity (from "I have a clear goal..." question)
        $goalClarity = floatval($student['I have a clear goal or purpose for my university journey.”']) ?? 3;

        // Transition Confidence  
        $transitionConfidence = floatval($student['How confident are you in transitioning to university life?']) ?? 3;

        // Primary Motivator
        $motivatorText = $student['What motivates you most about university life?'] ?? '';
        $primaryMotivatorScore = $this->getPrimaryMotivatorScore($motivatorText);

        // A/L Grades - Average of top 3 subjects
        $academicPerformance = $this->calculateAcademicPerformance($student);

        // Employment
        $isEmployed = isset($student['Are you currently employed?']) && 
                     strtolower($student['Are you currently employed?']) === 'yes';
        $employmentAdjustment = $isEmployed ? -0.5 : 0;

        // Motivation Score (EXACT SAME FORMULA AS YOUR CODE)
        $motivationScore = round((
            $goalClarity + 
            $transitionConfidence + 
            $academicPerformance + 
            $primaryMotivatorScore + 
            $employmentAdjustment
        ) / 4, 2);

        // Social KPI (EXACT SAME FORMULA AS YOUR CODE)
        $socialPreference = $student['Which social setting do you prefer most?'] ?? '';
        $socialPreferenceScore = $this->getSocialPreferenceScore($socialPreference);
        
        $introvertScore = (floatval($student['Where would you place yourself on the Introvert–Extrovert scale?']) ?? 5) / 10 * 5;
        
        $groupComfortScore = floatval($student['How comfortable are you working in group activities?']) ?? 3;

        $livingArrangement = $student['What is your current living arrangement?'] ?? '';
        $livingArrangementScore = $this->getLivingArrangementScore($livingArrangement);

        $communicationMethods = $student['Which communication methods do you prefer? (Select all that apply)'] ?? '';
        $communicationVariety = $this->countCommunicationMethods($communicationMethods);
        $communicationScore = $communicationVariety >= 3 ? 5 : ($communicationVariety == 2 ? 4 : ($communicationVariety == 1 ? 3 : 1));

        $socialScore = round((
            $socialPreferenceScore + 
            $groupComfortScore + 
            $communicationScore + 
            $livingArrangementScore + 
            $introvertScore
        ) / 5, 2);

        // Emotional KPI (EXACT SAME FORMULA AS YOUR CODE)
        $stressLevel = $student['In general, how would you describe your usual level of stress or anxiety?'] ?? '';
        $stressScore = $this->getStressScore($stressLevel);
        
        $overwhelmLevel = floatval($student['I often feel overwhelmed or anxious.']) ?? 3;
        $overwhelmScore = 6 - $overwhelmLevel;
        
        $peerStruggle = floatval($student['I struggle to connect with peers.']) ?? 3;
        $peerStruggleScore = 6 - $peerStruggle;
        
        $emotionalScore = round((
            $stressScore + 
            $overwhelmScore + 
            $peerStruggleScore + 
            $goalClarity
        ) / 4, 2);

        return [
            'motivationScore' => $motivationScore,
            'socialScore' => $socialScore,
            'emotionalScore' => $emotionalScore,
            'motivationInterpretation' => $this->interpretMotivation($motivationScore),
            'socialInterpretation' => $this->interpretSocial($socialScore),
            'emotionalInterpretation' => $this->interpretEmotional($emotionalScore)
        ];
    }

    // Helper methods that match your exact logic
    private function getPrimaryMotivatorScore($motivatorText)
    {
        $motivatorScores = [
            'Academic growth' => 5,
            'Career opportunities' => 4,
            'Experiences and exposure' => 3,
            'Friends and connections' => 2,
        ];

        foreach ($motivatorScores as $key => $score) {
            if (str_contains($motivatorText, $key)) {
                return $score;
            }
        }
        
        return 3; // Default
    }

    private function calculateAcademicPerformance($student)
    {
        $gradeScores = ['A' => 5, 'B' => 4, 'C' => 3, 'S' => 2, 'F' => 1];
        
        $subjectGrades = [];
        
        // Get grades from all subject columns
        $subjectColumns = [
            'AL Results [Subject 1]',
            'AL Results [Subject 2]', 
            'AL Results [Subject 3]',
            'AL Results [English]',
            'AL Results [General Knowledge]'
        ];
        
        foreach ($subjectColumns as $column) {
            if (isset($student[$column]) && !empty($student[$column])) {
                $grade = trim($student[$column]);
                if (isset($gradeScores[$grade])) {
                    $subjectGrades[] = $gradeScores[$grade];
                }
            }
        }
        
        // If no grades found, return average
        if (empty($subjectGrades)) {
            return 3.0;
        }
        
        // Sort grades descending and take top 3 (EXACT SAME LOGIC AS YOUR CODE)
        rsort($subjectGrades);
        $academicPerformance = count($subjectGrades) >= 3 ? 
            array_sum(array_slice($subjectGrades, 0, 3)) / 3 : 
            (count($subjectGrades) > 0 ? array_sum($subjectGrades) / count($subjectGrades) : 3);
            
        return $academicPerformance;
    }

    private function getSocialPreferenceScore($preference)
    {
        $socialPreferenceScores = [
            'Large Groups' => 5,
            'Small Groups' => 4,
            '1-on-1 interactions' => 3,
            '1-on-1' => 3,
            'Online-only' => 1
        ];
        
        return $socialPreferenceScores[$preference] ?? 3;
    }

    private function getLivingArrangementScore($arrangement)
    {
        $livingArrangementScores = [
            'Hostel' => 5,
            'Boarding place' => 4,
            'Boarding' => 4,
            'Home' => 3,
            'Other' => 2
        ];
        
        return $livingArrangementScores[$arrangement] ?? 2;
    }

    private function countCommunicationMethods($communicationMethods)
    {
        $methods = [];
        if (str_contains($communicationMethods, 'Texts')) $methods[] = 1;
        if (str_contains($communicationMethods, 'Calls')) $methods[] = 1;
        if (str_contains($communicationMethods, 'In-person conversations')) $methods[] = 1;
        
        return count($methods);
    }

    private function getStressScore($stressLevel)
    {
        $stressScores = [
            'Low' => 5,
            'Moderate' => 3,
            'High' => 1
        ];
        
        return $stressScores[$stressLevel] ?? 3;
    }

    // Interpretation methods (EXACT SAME AS YOUR CODE)
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