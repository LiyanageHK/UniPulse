<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\User;
use App\Services\MemoryExtractionService;
use App\Services\MemoryManagementService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExtractMessageMemory implements ShouldQueue
{
    use Queueable;

    /**
     * Retry once on failure, then give up (don't spam the API).
     */
    public int $tries = 2;

    /**
     * Wait 30 seconds before retrying (avoids hammering a rate-limited API).
     */
    public int $backoff = 30;

    public function __construct(
        public readonly int $messageId,
        public readonly int $userId
    ) {}

    public function handle(MemoryExtractionService $extractor, MemoryManagementService $manager): void
    {
        $message = Message::find($this->messageId);
        $user    = User::find($this->userId);

        if (!$message || !$user) {
            Log::warning('ExtractMessageMemory: message or user not found', [
                'message_id' => $this->messageId,
                'user_id'    => $this->userId,
            ]);
            return;
        }

        $memories = $extractor->extractMemoriesFromMessage($message);

        if (!empty($memories)) {
            $manager->batchSaveMemories($user, $memories);
            Log::info('ExtractMessageMemory: saved memories', [
                'message_id' => $this->messageId,
                'user_id'    => $this->userId,
                'count'      => count($memories),
            ]);
        }
    }
}
