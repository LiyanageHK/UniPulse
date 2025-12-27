<?php
require __DIR__.'/../vendor/autoload.php';

use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use Rubix\ML\NeuralNet\ActivationFunctions\Softmax;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

// Load training data
$trainingData = json_decode(file_get_contents(__DIR__.'/../storage/app/training_data.json'), true);

echo "📊 DATASET OVERVIEW:\n";
echo "Total training examples: " . count($trainingData) . "\n";

// Prepare features and labels
$samples = [];
$labels = [];

foreach ($trainingData as $item) {
    $samples[] = $item['samples'];
    $labels[] = $item['label'];
}

// Show class distribution
$distribution = array_count_values($labels);
echo "Class distribution:\n";
foreach ($distribution as $class => $count) {
    $percentage = round(($count / count($labels)) * 100, 1);
    echo "  {$class}: {$count} ({$percentage}%)\n";
}

// Create dataset
$dataset = new Labeled($samples, $labels);

echo "\n🧠 TRAINING NEURAL NETWORK MODEL:\n";
echo "Features: [Motivation, Social, Emotional] KPIs\n";
echo "Target: Intervention Type (4 classes)\n";
echo "Algorithm: Multilayer Perceptron\n";

// Use Neural Network that supports multi-class classification
$estimator = new MultilayerPerceptron([
    new Dense(10),
    new Activation(new ReLU()),
    new Dense(8),
    new Activation(new ReLU()),
    new Dense(4), // Output layer - 4 classes
    new Activation(new Softmax()),
], 128, 0.001, 100, 1e-4);

echo "Starting training...\n";
$estimator->train($dataset);

echo "✅ MODEL TRAINING COMPLETE!\n";
echo "Algorithm: Neural Network (Multilayer Perceptron)\n";
echo "Features: 3 (Motivation, Social, Emotional)\n";
echo "Classes: 4 (risk_detection, peer_matching, conversational_support, encouragement)\n";

// Save model
$persister = new Filesystem(__DIR__.'/../storage/app/trained_model.rbx');
$model = new PersistentModel($estimator, $persister);
$model->save();

echo "💾 Model saved: storage/app/trained_model.rbx\n";

// Test predictions
echo "\n🎯 SAMPLE PREDICTIONS:\n";
$testCases = [
    [1.2, 1.8, 1.5],
    [4.5, 4.2, 4.8],
    [3.0, 2.0, 3.5],
    [2.0, 3.5, 3.0],
];

$descriptions = [
    "Low scores → risk_detection",
    "High scores → encouragement", 
    "Low social → peer_matching",
    "Low motivation → conversational_support"
];

foreach ($testCases as $index => $testCase) {
    $prediction = $estimator->predict([$testCase])[0];
    echo "Test " . ($index + 1) . ":\n";
    echo "  Input: [" . implode(', ', $testCase) . "]\n";
    echo "  Predicted: {$prediction}\n";
    echo "  Expected: {$descriptions[$index]}\n";
    echo "---\n";
}

echo "🎉 NEURAL NETWORK TRAINING SUCCESSFUL!\n";
echo "🤖 AI model is now ready to provide personalized recommendations!\n";