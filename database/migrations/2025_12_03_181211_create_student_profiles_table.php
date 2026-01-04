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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('university');
            $table->string('university_other')->nullable();
            $table->string('faculty');
            $table->string('faculty_other')->nullable();
            $table->string('al_stream');
            $table->string('al_stream_other')->nullable();
            $table->enum('al_result_subject1', ['A', 'B', 'C', 'S', 'F']);
            $table->enum('al_result_subject2', ['A', 'B', 'C', 'S', 'F']);
            $table->enum('al_result_subject3', ['A', 'B', 'C', 'S', 'F']);
            $table->enum('al_result_english', ['A', 'B', 'C', 'S', 'F']);
            $table->enum('al_result_gk', ['A', 'B', 'C', 'S', 'F']);
            $table->enum('learning_style', ['Online', 'Physical', 'Hybrid']);
            $table->tinyInteger('confidence')->comment('1-5 scale');
            
            // Social & Personality Traits
            $table->string('social_setting');
            $table->tinyInteger('intro_extro')->comment('1-10 scale');
            $table->enum('stress_level', ['Low', 'Moderate', 'High']);
            $table->tinyInteger('group_comfort')->comment('1-5 scale');
            $table->json('communication_methods')->comment('Array of methods');
            
            // Interests & Lifestyle
            $table->string('motivation');
            $table->tinyInteger('clear_goal')->comment('1-5 scale');
            $table->json('top_interests')->comment('Array of interests');
            $table->json('hobbies')->comment('Array of hobbies');
            $table->string('living_arrangement');
            $table->enum('employed', ['Yes', 'No']);
            
            // Wellbeing & Support Needs
            $table->tinyInteger('overwhelmed')->comment('1-5 scale');
            $table->tinyInteger('struggle_connect')->comment('1-5 scale');
            $table->tinyInteger('ai_platform_support')->comment('1-5 scale');
            $table->json('support_types')->comment('Array of support types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
