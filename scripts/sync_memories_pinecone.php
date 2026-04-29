<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Memory;
use App\Services\PineconeService;

$pinecone = app(PineconeService::class);

$memories = Memory::whereNotNull('embedding')->get();
echo "Syncing " . $memories->count() . " memories to Pinecone...\n";

foreach ($memories as $mem) {
    $ok = $pinecone->upsert('mem_' . $mem->id, $mem->embedding, [
        'user_id'          => (int) $mem->user_id,
        'type'             => 'memory',
        'category'         => $mem->category,
        'memory_key'       => $mem->memory_key,
        'importance_score' => $mem->importance_score,
        'content'          => substr($mem->memory_value, 0, 1000),
    ]);
    echo "  mem_{$mem->id} ({$mem->memory_value}): " . ($ok ? 'OK' : 'FAIL') . "\n";
}
echo "Done!\n";
