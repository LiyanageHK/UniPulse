<?php
require __DIR__.'/../vendor/autoload.php';

use App\Services\ExpertRules;

// Load the KPIs we calculated
$studentKPIs = json_decode(file_get_contents(__DIR__.'/../storage/app/student_kpis.json'), true);

$expertRules = new ExpertRules();
$trainingData = [];

foreach ($studentKPIs as $student) {
    $recommendation = $expertRules->getRecommendation(
        $student['motivation'],
        $student['social'], 
        $student['emotional']
    );
    
    $trainingData[] = [
        'samples' => [$student['motivation'], $student['social'], $student['emotional']],
        'label' => $recommendation,
        'student_id' => $student['student_id']
    ];
}

// Save training data
file_put_contents(__DIR__.'/../storage/app/training_data.json', 
    json_encode($trainingData, JSON_PRETTY_PRINT));

echo "✅ Generated training data for " . count($trainingData) . " students!\n";

// Show distribution
$distribution = [];
foreach ($trainingData as $item) {
    $distribution[$item['label']] = ($distribution[$item['label']] ?? 0) + 1;
}

echo "📊 Recommendation Distribution:\n";
foreach ($distribution as $type => $count) {
    $percentage = round(($count / count($trainingData)) * 100, 1);
    echo "  {$type}: {$count} students ({$percentage}%)\n";
}