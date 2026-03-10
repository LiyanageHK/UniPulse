<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Conversation;
use App\Models\User;

// Get the currently authenticated user or use a test user
$user = User::first();

if (!$user) {
    echo "No users found in database\n";
    exit;
}

echo "\n=== Checking conversations for user: {$user->name} (ID: {$user->id}) ===\n\n";

$conversations = Conversation::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->get();

echo "Total conversations: " . count($conversations) . "\n\n";

foreach ($conversations as $conv) {
    echo "[ID: {$conv->id}] Title: {$conv->title}\n";
    echo "  Created: {$conv->created_at}\n";
    echo "  Last Message: {$conv->last_message_at}\n";
    echo "  Status: {$conv->status}\n";
    echo "  Message Count: {$conv->message_count}\n";
    echo "  Crisis Flags: {$conv->crisis_flags_count}\n\n";
}

// Check for duplicates by title
echo "\n=== Checking for duplicate titles ===\n\n";
$titleCounts = Conversation::where('user_id', $user->id)
    ->groupBy('title')
    ->selectRaw('title, COUNT(*) as count')
    ->having('count', '>', 1)
    ->get();

if (count($titleCounts) > 0) {
    echo "Found duplicate titles:\n";
    foreach ($titleCounts as $item) {
        echo "- '{$item->title}': {$item->count} times\n";
    }
} else {
    echo "No duplicate titles found.\n";
}
?>