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
        Schema::table('crisis_flags', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['reviewed_by']);
            
            $table->dropColumn([
                'escalated',
                'escalated_at',
                'reviewed',
                'reviewed_by',
                'reviewed_at',
                'reviewer_notes',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crisis_flags', function (Blueprint $table) {
            $table->boolean('escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->boolean('reviewed')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_notes')->nullable();
        });
    }
};
