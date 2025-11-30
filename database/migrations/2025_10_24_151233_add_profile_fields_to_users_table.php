<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Academic & Demographic
            $table->string('university')->nullable();
            $table->string('faculty')->nullable();
            $table->string('al_stream')->nullable();
            $table->json('al_results')->nullable();
            $table->json('learning_style')->nullable();
            $table->integer('transition_confidence')->nullable();
            
            // Social & Personality
            $table->string('social_preference')->nullable();
            $table->integer('introvert_extrovert_scale')->nullable();
            $table->enum('stress_level', ['Low', 'Moderate', 'High'])->nullable();
            $table->integer('group_work_comfort')->nullable();
            $table->json('communication_preferences')->nullable();
            
            // Interests & Lifestyle
            $table->string('primary_motivator')->nullable();
            $table->integer('goal_clarity')->nullable();
            $table->json('interests')->nullable();
            $table->json('hobbies')->nullable();
            $table->string('living_arrangement')->nullable();
            $table->boolean('is_employed')->default(false);
            
            // Wellbeing & Support
            $table->integer('overwhelm_level')->nullable();
            $table->integer('peer_struggle')->nullable();
            $table->integer('ai_openness')->nullable();
            $table->json('preferred_support_types')->nullable();
            
            // Onboarding status
            $table->boolean('onboarding_completed')->default(false);
            $table->timestamp('onboarding_completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'university', 'faculty', 'al_stream', 'al_results', 'learning_style',
                'transition_confidence', 'social_preference', 'introvert_extrovert_scale',
                'stress_level', 'group_work_comfort', 'communication_preferences',
                'primary_motivator', 'goal_clarity', 'interests', 'hobbies',
                'living_arrangement', 'is_employed', 'overwhelm_level',
                'peer_struggle', 'ai_openness', 'preferred_support_types',
                'onboarding_completed', 'onboarding_completed_at'
            ]);
        });
    }
};