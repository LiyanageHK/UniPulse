<?php
require __DIR__.'/../vendor/autoload.php';

function loadCSVData($filePath) {
    $data = [];
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $headers = fgetcsv($handle);
        
        echo "📋 ACTUAL COLUMN NAMES IN YOUR CSV:\n";
        foreach ($headers as $index => $header) {
            echo ($index + 1) . ". '" . trim($header) . "'\n";
        }
        
        // Show first row data
        if (($firstRow = fgetcsv($handle)) !== FALSE) {
            echo "\n📝 FIRST ROW DATA:\n";
            foreach ($headers as $index => $header) {
                echo "'" . trim($header) . "' => '" . ($firstRow[$index] ?? '') . "'\n";
            }
        }
        
        fclose($handle);
    }
    return $headers;
}

$csvFilePath = __DIR__.'/../storage/app/student_data.csv';
if (!file_exists($csvFilePath)) {
    die("❌ CSV file not found: $csvFilePath\n");
}

loadCSVData($csvFilePath);