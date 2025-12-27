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
        Schema::create('crisis_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crisis_flag_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // student
            $table->foreignId('counselor_id')->nullable()->constrained()->onDelete('set null');
            
            // Alert status
            $table->enum('status', ['pending', 'acknowledged', 'in_progress', 'resolved'])->default('pending');
            $table->enum('priority', ['critical', 'high', 'medium'])->default('critical');
            
            // Notification tracking
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->boolean('sms_sent')->default(false);
            $table->timestamp('sms_sent_at')->nullable();
            
            // Resources sent to student
            $table->json('resources_sent')->nullable(); // crisis hotlines, resources provided
            $table->timestamp('resources_sent_at')->nullable();
            
            // Response tracking
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            
            $table->timestamps();
            
            $table->index('status');
            $table->index('priority');
            $table->index(['user_id', 'status']);
            $table->index(['counselor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crisis_alerts');
    }
};
