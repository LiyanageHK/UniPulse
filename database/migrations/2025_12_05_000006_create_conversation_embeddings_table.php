<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversation_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('message_id')->nullable()->constrained()->onDelete('cascade');
            
            // Embedding type
            $table->enum('type', ['message', 'profile', 'summary'])->default('message');
            
            // Text content that was embedded
            $table->text('content');
            $table->text('summary')->nullable(); // short summary for quick reference
            
            // Vector embedding (stored as JSON array for compatibility)
            // For pgvector, this would be: $table->vector('embedding', 1536);
            $table->json('embedding'); // array of floats representing the vector
            
            // Metadata for retrieval
            $table->string('topic')->nullable();
            $table->json('keywords')->nullable();
            $table->float('importance_score')->default(0.5); // for ranking
            
            // Embedding model info
            $table->string('model')->default('text-embedding-3-small');
            $table->integer('dimensions')->default(1536);
            
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('type');
            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_embeddings');
    }
};
