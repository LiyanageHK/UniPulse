<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_snapshots', function (Blueprint $table) {
            // Remove any existing duplicates first (optional but recommended)
            DB::statement('
                DELETE k1 FROM kpi_snapshots k1
                INNER JOIN kpi_snapshots k2 
                WHERE 
                    k1.id < k2.id AND 
                    k1.user_id = k2.user_id AND 
                    k1.week_start = k2.week_start
            ');

            // Add the unique constraint
            $table->unique(['user_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::table('kpi_snapshots', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'week_start']);
        });
    }
};