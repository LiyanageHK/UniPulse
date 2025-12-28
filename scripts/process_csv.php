<?php
require __DIR__.'/../vendor/autoload.php';

use App\Services\KpiCalculator;

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
$csvFilePath = __DIR__.'/../storage/app/student_data.csv';
if (!file_exists($csvFilePath)) {
    die("❌ CSV file not found: $csvFilePath\nPlease export your Excel to CSV and save it as: storage/app/student_data.csv\n");
}

$csvData = loadCSVData($csvFilePath);

$calculator = new KpiCalculator();
$studentKPIs = $calculator->calculateFromExcelData($csvData);

// Save results
$outputPath = __DIR__.'/../storage/app/student_kpis.json';
file_put_contents($outputPath, json_encode($studentKPIs, JSON_PRETTY_PRINT));

echo "✅ Processed " . count($studentKPIs) . " students from CSV!\n";
echo "📁 Saved to: storage/app/student_kpis.json\n";

// Show sample
echo "\n📊 Sample Results (first 3 students):\n";
foreach (array_slice($studentKPIs, 0, 3) as $student) {
    echo "Student: {$student['student_id']}\n";
    echo "  Motivation: {$student['motivation']} ({$student['motivation_interpretation']})\n";
    echo "  Social: {$student['social']} ({$student['social_interpretation']})\n";
    echo "  Emotional: {$student['emotional']} ({$student['emotional_interpretation']})\n";
    echo "---\n";
}