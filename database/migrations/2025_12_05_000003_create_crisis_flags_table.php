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
        Schema::create('crisis_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Flag severity based on uploaded document
            $table->enum('severity', ['red', 'yellow', 'blue']);
            
            // Category for counselor matching
            $table->string('category'); // suicide_risk, self_harm, depression, anxiety, stress, etc.
            
            // Detection details
            $table->json('detected_keywords'); // array of keywords that triggered the flag
            $table->text('context_snippet'); // surrounding text for context
            $table->float('confidence_score')->default(1.0); // AI confidence in detection
            
            // Escalation tracking
            $table->boolean('escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
            
            // Flag is hidden from student, only visible to counselors/admins
            $table->boolean('reviewed')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();
            
            $table->timestamps();
            
            $table->index('severity');
            $table->index('category');
            $table->index(['user_id', 'severity']);
            $table->index('escalated');
            $table->index('reviewed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crisis_flags');
    }
};
