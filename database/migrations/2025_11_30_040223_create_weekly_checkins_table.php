<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');

            // Emotional & Mental Wellbeing
            $table->integer('mood')->nullable();
            $table->integer('tense')->nullable();
            $table->integer('overwhelmed')->nullable();
            $table->integer('worry')->nullable();
            $table->integer('sleep_trouble')->nullable();
            $table->integer('openness_to_mentor')->nullable();
            $table->integer('knowledge_of_support')->nullable();

            // Social Inclusion
            $table->integer('peer_connection')->nullable();
            $table->integer('peer_interaction')->nullable();
            $table->string('group_participation')->nullable(); // multiple choice
            $table->integer('feel_left_out')->nullable();
            $table->integer('no_one_to_talk')->nullable();
            $table->integer('university_belonging')->nullable();
            $table->integer('meaningful_connections')->nullable();

            // Motivational Level
            $table->integer('studies_interesting')->nullable();
            $table->integer('academic_confidence')->nullable();
            $table->integer('workload_management')->nullable();

            // Depressive Feelings & Burnout
            $table->integer('no_energy')->nullable();
            $table->integer('low_pleasure')->nullable();
            $table->integer('feeling_down')->nullable();
            $table->integer('emotionally_drained')->nullable();
            $table->integer('hard_to_stay_focused')->nullable();
            $table->integer('just_through_motions')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_checkins');
    }
};
