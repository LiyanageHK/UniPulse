<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add week_index column and unique constraint for rolling 7-day weeks.
     * Prevents duplicate weekly summaries for the same user + week cycle.
     */
    public function up(): void
    {
        Schema::table('weekly_summaries', function (Blueprint $table) {
            $table->unsignedInteger('week_index')->default(0)->after('week_end');
            $table->unique(['user_id', 'week_index'], 'weekly_summaries_user_week_unique');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_summaries', function (Blueprint $table) {
            $table->dropUnique('weekly_summaries_user_week_unique');
            $table->dropColumn('week_index');
        });
    }
};
