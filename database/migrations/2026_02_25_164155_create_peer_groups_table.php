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
        Schema::create('peer_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('cluster_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('purpose'); // study, sports, social
            $table->string('group_name')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['purpose', 'cluster_id']);
            $table->unique(['user_id', 'purpose'], 'unique_user_purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peer_groups');
    }
};
