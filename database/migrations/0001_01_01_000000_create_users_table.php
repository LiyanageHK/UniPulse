<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

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

            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
