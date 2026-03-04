<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\KnowledgeBaseService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CreateMessageEmbedding implements ShouldQueue
{
    use Queueable;

    /**
     * Retry once on failure.
     */
    public int $tries = 2;

    /**
     * Wait 30 seconds before retrying.
     */
    public int $backoff = 30;

    public function __construct(public readonly int $messageId) {}

    public function handle(KnowledgeBaseService $knowledgeBase): void
    {
        $message = Message::find($this->messageId);

        if (!$message) {
            Log::warning('CreateMessageEmbedding: message not found', ['message_id' => $this->messageId]);
            return;
        }

        $knowledgeBase->createMessageEmbedding($message);

        Log::info('CreateMessageEmbedding: embedding created', ['message_id' => $this->messageId]);
    }
}
