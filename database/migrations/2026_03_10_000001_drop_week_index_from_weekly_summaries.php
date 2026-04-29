<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop week_index column and its unique constraint.
     * Add a unique constraint on (user_id, week_start) instead —
     * week_start is the natural unique key for a user's rolling 7-day window.
     */
    public function up(): void
    {
        Schema::table('weekly_summaries', function (Blueprint $table) {
            // Drop old unique constraint and column
            $table->dropUnique('weekly_summaries_user_week_unique');
            $table->dropColumn('week_index');

            // New unique key: one summary per user per week_start date
            $table->unique(['user_id', 'week_start'], 'weekly_summaries_user_week_start_unique');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_summaries', function (Blueprint $table) {
            $table->dropUnique('weekly_summaries_user_week_start_unique');
            $table->unsignedInteger('week_index')->default(0)->after('week_end');
            $table->unique(['user_id', 'week_index'], 'weekly_summaries_user_week_unique');
        });
    }
};
