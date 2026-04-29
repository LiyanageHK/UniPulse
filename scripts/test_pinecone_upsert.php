<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Memory;
use App\Services\PineconeService;
use App\Services\EmbeddingService;

$mem = Memory::find(142);
echo "Memory 142 embedding dims: " . (is_array($mem->embedding) ? count($mem->embedding) : 'NULL') . "\n";
echo "Embedding provider: " . config('services.openai.embedding_provider', config('services.openai.provider')) . "\n";

// Test: manually upsert memory 142 to Pinecone
$pinecone = app(PineconeService::class);
echo "Pinecone available: " . ($pinecone->isAvailable() ? 'YES' : 'NO') . "\n";

if ($mem->embedding && $pinecone->isAvailable()) {
    echo "\nAttempting manual upsert of mem_142 to Pinecone...\n";
    $result = $pinecone->upsert('mem_' . $mem->id, $mem->embedding, [
        'user_id'          => (int) $mem->user_id,
        'type'             => 'memory',
        'category'         => $mem->category,
        'memory_key'       => $mem->memory_key,
        'importance_score' => $mem->importance_score,
        'content'          => substr($mem->memory_value, 0, 1000),
    ]);
    echo "Upsert result: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";

    // Now try query again
    $emb = app(EmbeddingService::class);
    $qe = $emb->generateEmbedding("what is my old school?");
    if ($qe) {
        $res = $pinecone->query($qe, 5, ['user_id' => 1, 'type' => 'memory']);
        echo "Memory query results after upsert: " . count($res) . "\n";
        foreach ($res as $r) {
            echo "  " . ($r['id'] ?? '?') . " | score: " . round($r['score'] ?? 0, 3) . " | " . ($r['metadata']['content'] ?? '') . "\n";
        }
    }
}
