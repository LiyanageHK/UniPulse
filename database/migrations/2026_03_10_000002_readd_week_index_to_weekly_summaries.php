<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Re-add week_index column to weekly_summaries.
     * week_index is a 1-based sequential counter per user, auto-computed
     * from chronological week_start order.
     */
    public function up(): void
    {
        Schema::table('weekly_summaries', function (Blueprint $table) {
            $table->unsignedInteger('week_index')->default(0)->after('week_end');
        });

        // Back-fill week_index from existing rows ordered by week_start
        $users = DB::table('weekly_summaries')->distinct()->pluck('user_id');

        foreach ($users as $userId) {
            $summaries = DB::table('weekly_summaries')
                ->where('user_id', $userId)
                ->orderBy('week_start')
                ->pluck('id');

            foreach ($summaries as $index => $id) {
                DB::table('weekly_summaries')
                    ->where('id', $id)
                    ->update(['week_index' => $index + 1]);
            }
        }

        Schema::table('weekly_summaries', function (Blueprint $table) {
            $table->unique(['user_id', 'week_index'], 'weekly_summaries_user_week_index_unique');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_summaries', function (Blueprint $table) {
            $table->dropUnique('weekly_summaries_user_week_index_unique');
            $table->dropColumn('week_index');
        });
    }
};
