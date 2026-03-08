<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Conversation;
use Illuminate\Support\Facades\DB;

echo "\n=== UniPulse Conversation Duplicate Cleanup ===\n\n";

// Find all groups of conversations with the same user and title
$duplicateGroups = Conversation::query()
    ->groupBy('user_id', 'title')
    ->havingRaw('COUNT(*) > 1')
    ->selectRaw('user_id, title, COUNT(*) as count')
    ->get();

echo "Found " . count($duplicateGroups) . " duplicate group(s).\n\n";

$totalRemoved = 0;

foreach ($duplicateGroups as $group) {
    echo "Processing: User #{$group->user_id}, Title: \"{$group->title}\" ({$group->count} copies)\n";

    // Find all conversations in this group, ordered by creation (keep the earliest)
    $conversations = Conversation::where('user_id', $group->user_id)
        ->where('title', $group->title)
        ->orderBy('created_at', 'asc')
        ->get();

    // Keep first, mark others for deletion
    $keepId = $conversations->first()->id;
    $removeIds = $conversations->skip(1)->pluck('id')->toArray();

    echo "  Keeping: conversation #{$keepId} (created: {$conversations->first()->created_at})\n";
    echo "  Removing: conversations #" . implode(', #', $removeIds) . "\n";

    // Delete the duplicate conversations and their messages
    foreach ($removeIds as $id) {
        $conv = Conversation::find($id);
        if ($conv) {
            // Count messages before deletion
            $msgCount = $conv->messages()->count();

            // Delete messages and embeddings first (cascade will handle, but we log for clarity)
            $conv->messages()->delete();
            $conv->embeddings()->delete();
            $conv->crisisFlags()->delete();

            // Delete the conversation
            $conv->delete();
            echo "    ✓ Deleted conversation #{$id} ({$msgCount} messages)\n";
            $totalRemoved++;
        }
    }
    echo "\n";
}

echo "\n=== Summary ===\n";
echo "Total duplicate conversations removed: {$totalRemoved}\n";
echo "Cleanup complete!\n\n";
?>
