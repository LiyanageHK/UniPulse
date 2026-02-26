<?php

/**
 * Quick end-to-end test for the peer matching integration.
 * Run: php artisan tinker scripts/test_peer_matching.php
 */

use Illuminate\Support\Facades\Http;
use App\Models\PeerGroup;
use Illuminate\Support\Facades\DB;

echo "=== Peer Matching E2E Test ===" . PHP_EOL;

// 1. Call ML API
$response = Http::timeout(30)->post('http://127.0.0.1:5000/run-clustering', [
    'group_size' => 4,
    'purpose'    => 'study',
]);

$data = $response->json();

echo "API Status: " . $response->status() . PHP_EOL;
echo "Students:   " . $data['total_students'] . PHP_EOL;
echo "Clusters:   " . $data['clusters_count'] . PHP_EOL;
echo "Silhouette: " . $data['silhouette_score'] . PHP_EOL;

// 2. Save to DB
DB::beginTransaction();
PeerGroup::where('purpose', 'study')->delete();

$records = [];
$now = now();
for ($i = 0; $i < count($data['user_ids']); $i++) {
    $clusterId = $data['cluster_assignments'][$i];
    $records[] = [
        'cluster_id'  => $clusterId,
        'user_id'     => $data['user_ids'][$i],
        'purpose'     => 'study',
        'group_name'  => 'Study Group ' . ($clusterId + 1),
        'created_at'  => $now,
    ];
}
PeerGroup::insert($records);
DB::commit();

// 3. Verify
$groups = PeerGroup::getGroupedByCluster('study');
echo "Saved groups: " . $groups->count() . PHP_EOL;
echo "Group sizes:  ";
foreach ($groups as $cid => $members) {
    echo "G" . ($cid + 1) . "=" . $members->count() . " ";
}
echo PHP_EOL;
echo "=== DONE ===" . PHP_EOL;
