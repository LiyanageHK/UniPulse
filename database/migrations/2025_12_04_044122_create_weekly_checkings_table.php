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
        Schema::create('weekly_checkings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('overall_mood');
            $table->tinyInteger('felt_supported');
            $table->string('emotion_description');
            $table->tinyInteger('trouble_sleeping');
            $table->tinyInteger('hard_to_focus');
            $table->tinyInteger('open_to_counselor');
            $table->tinyInteger('know_access_support');
            $table->tinyInteger('feeling_tense');
            $table->tinyInteger('worrying');
            $table->tinyInteger('interact_peers');
            $table->tinyInteger('keep_up_workload');
            $table->json('group_activities')->nullable();
            $table->json('academic_challenges')->nullable();
            $table->integer('feel_left_out');
            $table->integer('no_one_to_talk');
            $table->integer('no_energy');
            $table->integer('little_pleasure');
            $table->integer('feeling_down');
            $table->integer('emotionally_drained');
            $table->integer('going_through_motions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_checkings');
    }
};
