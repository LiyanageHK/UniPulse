<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->enum('al_result_english', ['A', 'B', 'C', 'S', 'F'])->nullable()->change();
            $table->enum('al_result_gk',      ['A', 'B', 'C', 'S', 'F'])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->enum('al_result_english', ['A', 'B', 'C', 'S', 'F'])->nullable(false)->change();
            $table->enum('al_result_gk',      ['A', 'B', 'C', 'S', 'F'])->nullable(false)->change();
        });
    }
};
