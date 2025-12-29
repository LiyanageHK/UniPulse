<?php
// scripts/process_weekly_data.php
require __DIR__.'/../vendor/autoload.php';

use App\Services\KpiCalculator;
use App\Services\ExpertRules;

function loadCSVData($filePath) {
    $data = [];
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headers = fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== FALSE) {
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[trim($header)] = $row[$index] ?? '';
            }
            $data[] = $rowData;
        }
        fclose($handle);
    }
    return $data;
}

// Load your CSV data
$csvFilePath = __DIR__.'/../storage/app/weekly_checkin_data.csv';
if (!file_exists($csvFilePath)) {
    die(" CSV file not found: $csvFilePath\n" .
        "Please convert Excel to CSV first.\n" .
        "Run: php scripts/convert_excel_to_csv.php\n");
}

echo " Processing Weekly Check-in Data...\n";
echo "=====================================\n\n";

$csvData = loadCSVData($csvFilePath);
echo " Loaded " . count($csvData) . " student records from CSV\n";

// Calculate KPIs
$calculator = new KpiCalculator();
$studentKPIs = $calculator->calculateFromWeeklyData($csvData);
echo " Calculated KPIs for " . count($studentKPIs) . " students\n";

// Save student KPIs
$kpisPath = __DIR__.'/../storage/app/student_kpis.json';
file_put_contents($kpisPath, json_encode($studentKPIs, JSON_PRETTY_PRINT));
echo " Saved student KPIs to: storage/app/student_kpis.json\n";

// Create training data
$expertRules = new ExpertRules();
$trainingData = [];

foreach ($studentKPIs as $kpi) {
    $trainingData[] = [
        'motivation' => $kpi['motivation'],
        'social' => $kpi['social'],
        'emotional' => $kpi['emotional'],
        'recommendation' => $expertRules->getRecommendation(
            $kpi['motivation'],
            $kpi['social'],
            $kpi['emotional']
        )
    ];
}

// Save training data
$trainingJsonPath = __DIR__.'/../storage/app/training_data.json';
file_put_contents($trainingJsonPath, json_encode($trainingData, JSON_PRETTY_PRINT));
echo " Saved training data (JSON) to: storage/app/training_data.json\n";

// Save as CSV for ML
$trainingCsvPath = __DIR__.'/../storage/app/training_data.csv';
$csvFile = fopen($trainingCsvPath, 'w');
fputcsv($csvFile, ['motivation', 'social', 'emotional', 'recommendation']);
foreach ($trainingData as $row) {
    fputcsv($csvFile, $row);
}
fclose($csvFile);
echo " Saved training data (CSV) to: storage/app/training_data.csv\n";

// Show sample results
echo "\n SAMPLE RESULTS (first 3 students):\n";
echo "=====================================\n";
foreach (array_slice($studentKPIs, 0, 3) as $student) {
    echo " Student: " . $student['student_name'] . "\n";
    echo "    Week: " . $student['week'] . "\n";
    echo "    Motivation: " . $student['motivation'] . " (" . $student['motivation_interpretation'] . ")\n";
    echo "    Social: " . $student['social'] . " (" . $student['social_interpretation'] . ")\n";
    echo "    Emotional: " . $student['emotional'] . " (" . $student['emotional_interpretation'] . ")\n";
    echo "   ---\n";
}

// Statistics
echo "\n STATISTICS:\n";
echo "=============\n";
$total = count($studentKPIs);
$motivationHigh = 0; $motivationMod = 0; $motivationLow = 0;
$socialHigh = 0; $socialMod = 0; $socialLow = 0;
$emotionalHigh = 0; $emotionalMod = 0; $emotionalLow = 0;

foreach ($studentKPIs as $student) {
    // Count motivation levels
    if ($student['motivation'] >= 4.0) $motivationHigh++;
    elseif ($student['motivation'] >= 2.5) $motivationMod++;
    else $motivationLow++;
    
    // Count social levels
    if ($student['social'] >= 4.0) $socialHigh++;
    elseif ($student['social'] >= 2.5) $socialMod++;
    else $socialLow++;
    
    // Count emotional levels
    if ($student['emotional'] >= 4.0) $emotionalHigh++;
    elseif ($student['emotional'] >= 2.5) $emotionalMod++;
    else $emotionalLow++;
}

echo "Total Students: $total\n\n";
echo "Motivation Levels:\n";
echo "   High: $motivationHigh (" . round(($motivationHigh/$total)*100, 1) . "%)\n";
echo "   Moderate: $motivationMod (" . round(($motivationMod/$total)*100, 1) . "%)\n";
echo "   Low: $motivationLow (" . round(($motivationLow/$total)*100, 1) . "%)\n\n";

echo "Social Levels:\n";
echo "   Integrated: $socialHigh (" . round(($socialHigh/$total)*100, 1) . "%)\n";
echo "   Moderate: $socialMod (" . round(($socialMod/$total)*100, 1) . "%)\n";
echo "   Isolated: $socialLow (" . round(($socialLow/$total)*100, 1) . "%)\n\n";

echo "Emotional Levels:\n";
echo "   Stable: $emotionalHigh (" . round(($emotionalHigh/$total)*100, 1) . "%)\n";
echo "   Moderate: $emotionalMod (" . round(($emotionalMod/$total)*100, 1) . "%)\n";
echo "   At-risk: $emotionalLow (" . round(($emotionalLow/$total)*100, 1) . "%)\n";

echo "\n PROCESSING COMPLETE!\n";