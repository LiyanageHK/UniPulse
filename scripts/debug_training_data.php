<?php
// scripts/debug_training_data.php
echo " Debugging Training Data...\n";
echo "==============================\n\n";

$baseDir = __DIR__ . '/../storage/app/';
echo "Base directory: " . realpath($baseDir) . "\n\n";

// List all files in storage/app
echo " Files in storage/app:\n";
$files = scandir($baseDir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $filePath = $baseDir . $file;
        $size = filesize($filePath);
        echo "  - {$file} (" . round($size / 1024, 2) . " KB)\n";
    }
}

echo "\n";

// Check training_data.csv specifically
$trainingCsvPath = $baseDir . 'training_data.csv';
echo " Checking training_data.csv:\n";
echo "Path: " . realpath($trainingCsvPath) . "\n";

if (!file_exists($trainingCsvPath)) {
    die(" File does not exist at: " . $trainingCsvPath . "\n");
}

$fileSize = filesize($trainingCsvPath);
echo "File size: " . $fileSize . " bytes\n";

if ($fileSize === 0) {
    die(" File is empty!\n");
}

// Try to read the file
echo "\n File content (first 200 characters):\n";
$content = file_get_contents($trainingCsvPath, false, null, 0, 200);
echo "```\n" . $content . "\n```\n";

// Try to parse CSV
echo "\n Parsing CSV file:\n";
if (($handle = fopen($trainingCsvPath, "r")) !== FALSE) {
    // Read header
    $header = fgetcsv($handle);
    echo "Header: " . json_encode($header) . "\n";
    
    // Read first 5 rows
    echo "\nFirst 5 rows:\n";
    $rowCount = 0;
    while (($row = fgetcsv($handle)) !== FALSE && $rowCount < 5) {
        echo "Row {$rowCount}: " . json_encode($row) . "\n";
        $rowCount++;
    }
    
    // Count total rows
    rewind($handle);
    fgetcsv($handle); // Skip header
    $totalRows = 0;
    while (fgetcsv($handle) !== FALSE) {
        $totalRows++;
    }
    
    echo "\nTotal data rows (excluding header): {$totalRows}\n";
    fclose($handle);
} else {
    echo " Could not open file for reading\n";
}

// Also check student_kpis.json
echo "\n Checking student_kpis.json:\n";
$kpisPath = $baseDir . 'student_kpis.json';
if (file_exists($kpisPath)) {
    $kpisContent = file_get_contents($kpisPath);
    $kpisData = json_decode($kpisContent, true);
    if ($kpisData) {
        echo "student_kpis.json has " . count($kpisData) . " entries\n";
        if (count($kpisData) > 0) {
            echo "First entry keys: " . implode(", ", array_keys($kpisData[0])) . "\n";
        }
    } else {
        echo "Could not parse student_kpis.json\n";
    }
} else {
    echo "student_kpis.json not found\n";
}

echo "\n Debug complete!\n";