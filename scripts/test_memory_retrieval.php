<?php

/**
 * Test script: check memory storage and retrieval for user 1.
 * Run: php scripts/test_memory_retrieval.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Memory;
use App\Models\User;
use App\Services\RagRetrievalService;

$userId = 1;
$user = User::find($userId);

echo "=== MEMORIES FOR USER {$userId} ===\n\n";

$memories = Memory::where('user_id', $userId)->get();
echo "Total memories: " . $memories->count() . "\n\n";

foreach ($memories as $mem) {
    $hasEmb = !empty($mem->embedding) ? 'YES' : 'NO';
    echo "  [{$mem->id}] {$mem->category} | {$mem->memory_key}\n";
    echo "       Value: {$mem->memory_value}\n";
    echo "       Importance: {$mem->importance_score} | Has Embedding: {$hasEmb}\n\n";
}

echo "\n=== TEST RAG RETRIEVAL ===\n\n";

$rag = app(RagRetrievalService::class);

$testQuery = "what is my old school?";
echo "Query: \"{$testQuery}\"\n";

$context = $rag->getSmartContext($user, $testQuery, null, 5, false, 0.4);

echo "Retrieved chunks: " . $context['retrieved_chunks'] . "\n";
echo "Memories count: " . $context['memories_count'] . "\n";
echo "Has profile data: " . ($context['has_profile_data'] ? 'YES' : 'NO') . "\n\n";

echo "=== RAG CONTEXT SENT TO LLM ===\n";
echo $context['rag_context'] ?: "(EMPTY - no context retrieved)\n";
echo "\n";
