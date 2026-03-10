<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

echo "\n=== Conversation Debug Analysis ===\n\n";

// Get all conversations
$allConversations = Conversation::orderBy('user_id', 'asc')->orderBy('created_at', 'desc')->get();
$conversationsByUser = $allConversations->groupBy('user_id');

foreach ($conversationsByUser as $userId => $conversations) {
    $user = User::find($userId);
    echo "═══════════════════════════════════════\n";
    echo "User: " . ($user ? $user->name : "Unknown") . " (ID: {$userId})\n";
    echo "═══════════════════════════════════════\n\n";

    foreach ($conversations as $i => $conv) {
        $messageCount = $conv->messages()->count();
        $messages = $conv->messages()->orderBy('created_at', 'asc')->get();
        $lastMessageAt = $conv->last_message_at ? $conv->last_message_at->format('Y-m-d H:i:s') : 'None';

        echo "[{$i}] Conv #{$conv->id} | Title: \"{$conv->title}\"\n";
        echo "    Created: {$conv->created_at->format('Y-m-d H:i:s')}\n";
        echo "    Last message: {$lastMessageAt}\n";
        if ($messageCount > 0) {
            echo "    First message: {$messages->first()->created_at->format('H:i:s')}\n";
            echo "    Last message: {$messages->last()->created_at->format('H:i:s')}\n";
            echo "    Time span: " . $messages->first()->created_at->diffInMinutes($messages->last()->created_at) . " minutes\n";
        }
        echo "\n";
    }

    // Check for conversations with same title created close together
    $groupedByTitle = $conversations->groupBy('title');
    $suspiciousGroups = $groupedByTitle->filter(function ($group) {
        if ($group->count() <= 1)
            return false;

        // Check if they were created within 5 minutes of each other
        $minTime = $group->min('created_at');
        $maxTime = $group->max('created_at');
        return $minTime->diffInMinutes($maxTime) <= 5;
    });

    if ($suspiciousGroups->count() > 0) {
        echo "⚠️  SUSPICIOUS: Conversations with same title created close together:\n";
        foreach ($suspiciousGroups as $title => $group) {
            echo "  Title: \"{$title}\" ({$group->count()} conversations)\n";
            foreach ($group as $conv) {
                $msgCount = $conv->messages()->count();
                echo "    - Conv #{$conv->id}: {$msgCount} messages, created {$conv->created_at->format('H:i:s')}\n";
            }
        }
        echo "\n";
    }

    echo "\n";
}

echo "\n=== Test Scenario ===\n";
echo "If you're seeing multiple chats created when continuing one conversation:\n";
echo "1. Check if conversation_id is being maintained in frontend\n";
echo "2. Check if sendMessage is receiving correct conversation_id\n";
echo "3. Check if messages are being saved to correct conversation\n";
echo "4. Check if page refresh resets currentConversationId\n\n";
?>
