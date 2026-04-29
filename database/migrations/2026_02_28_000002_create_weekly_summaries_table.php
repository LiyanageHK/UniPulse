<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('summary_text');
            $table->float('stress_score')->default(0);
            $table->float('sentiment_score')->default(0);
            $table->float('pronoun_ratio')->default(0);
            $table->float('absolutist_score')->default(0);
            $table->float('withdrawal_score')->default(0);
            $table->float('lri_score')->default(0);
            $table->string('risk_level')->default('Low');
            $table->boolean('escalation_flag')->default(false);
            $table->date('week_start');
            $table->date('week_end');
            $table->timestamps();

            $table->index(['user_id', 'week_start']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_summaries');
    }
};
