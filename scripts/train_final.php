<?php
require __DIR__.'/../vendor/autoload.php';

use Rubix\ML\Classifiers\RandomForest;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

// Load training data
$trainingData = json_decode(file_get_contents(__DIR__.'/../storage/app/training_data.json'), true);

echo "🚀 TRAINING AI MODEL\n";
echo "===================\n";
echo "Examples: " . count($trainingData) . "\n";

$samples = [];
$labels = [];

foreach ($trainingData as $item) {
    $samples[] = $item['samples'];
    $labels[] = $item['label'];
}

// Show distribution
echo "Distribution:\n";
print_r(array_count_values($labels));

// Train with SIMPLE defaults
$estimator = new RandomForest(new ClassificationTree(5), 50, 0.5);

echo "Training...\n";
$dataset = new Labeled($samples, $labels);
$estimator->train($dataset);

echo "✅ TRAINED!\n";

// Save
$model = new PersistentModel($estimator, new Filesystem(__DIR__.'/../storage/app/trained_model.rbx'));
$model->save();
echo "💾 Saved: trained_model.rbx\n";

// Test - FIXED: Use Unlabeled dataset for predictions
echo "\nPREDICTIONS:\n";
$tests = [
    [1.5, 2.0, 1.8],
    [4.5, 4.2, 4.8],
    [3.0, 2.0, 3.5],
];

foreach ($tests as $test) {
    $testDataset = new Unlabeled([$test]); // Create dataset for prediction
    $pred = $estimator->predict($testDataset)[0];
    echo "[" . implode(', ', $test) . "] → {$pred}\n";
}

echo "🎉 DONE! AI model ready.\n";