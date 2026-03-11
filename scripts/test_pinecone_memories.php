<?php

/**
 * Test Pinecone memory retrieval directly.
 * Run: php scripts/test_pinecone_memories.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\PineconeService;
use App\Services\EmbeddingService;

$pinecone = app(PineconeService::class);
$embedding = app(EmbeddingService::class);

echo "Pinecone available: " . ($pinecone->isAvailable() ? 'YES' : 'NO') . "\n";

// Generate test embedding
$query = "what is my old school?";
echo "Query: \"{$query}\"\n";
$qe = $embedding->generateEmbedding($query);

if (!$qe) {
    echo "FAILED to generate query embedding!\n";
    exit(1);
}

echo "Embedding generated (" . count($qe) . " dims)\n\n";

// Query for memories in Pinecone
echo "=== Pinecone: type=memory, user_id=1 ===\n";
$memResults = $pinecone->query($qe, 10, ['user_id' => 1, 'type' => 'memory']);
echo "Results: " . count($memResults) . "\n";
foreach ($memResults as $r) {
    echo "  ID: " . ($r['id'] ?? '?') . " | score: " . round($r['score'] ?? 0, 3) . " | content: " . substr($r['metadata']['content'] ?? '', 0, 60) . "\n";
}

echo "\n=== Pinecone: ALL vectors for user_id=1 ===\n";
$allResults = $pinecone->query($qe, 20, ['user_id' => 1]);
echo "Results: " . count($allResults) . "\n";
foreach ($allResults as $r) {
    $type = $r['metadata']['type'] ?? '?';
    $content = substr($r['metadata']['content'] ?? '', 0, 60);
    echo "  ID: " . ($r['id'] ?? '?') . " | type: {$type} | score: " . round($r['score'] ?? 0, 3) . " | {$content}\n";
}

// Check if mem_140, mem_141, mem_142 exist by fetching
echo "\n=== Check specific memory IDs ===\n";
$ids = ['mem_140', 'mem_141', 'mem_142'];
foreach ($ids as $id) {
    $fetch = $pinecone->fetch([$id]);
    $exists = !empty($fetch);
    echo "  {$id}: " . ($exists ? "EXISTS" : "NOT FOUND") . "\n";
}
