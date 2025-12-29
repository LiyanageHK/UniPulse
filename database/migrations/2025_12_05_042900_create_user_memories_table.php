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
        Schema::create('user_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('source_conversation_id')->nullable()->constrained('conversations')->onDelete('set null');
            $table->foreignId('source_message_id')->nullable()->constrained('messages')->onDelete('set null');
            
            // Memory categorization
            $table->enum('category', [
                'personal_info',
                'academic',
                'goals',
                'preferences',
                'emotional',
                'relationships',
                'health',
                'experiences'
            ])->default('personal_info');
            
            // Memory content
            $table->string('memory_key'); // e.g., "favorite_subject", "career_goal"
            $table->text('memory_value'); // the actual memory content
            
            // Metadata
            $table->float('importance_score')->default(0.5); // 0.0 - 1.0
            $table->timestamp('last_referenced_at')->nullable();
            
            // Vector embedding for similarity search
            $table->json('embedding')->nullable();
            $table->string('embedding_model')->default('text-embedding-3-small');
            $table->integer('embedding_dimensions')->default(1536);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id');
            $table->index('category');
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'memory_key']);
            $table->index('importance_score');
            $table->index('last_referenced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_memories');
    }
};
