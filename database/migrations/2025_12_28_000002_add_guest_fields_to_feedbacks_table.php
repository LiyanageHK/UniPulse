<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Allow guest feedback by making user_id nullable and adding guest_name field.
     */
    public function up(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['user_id']);
            
            // Make user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Add foreign key back as nullable
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add guest fields
            $table->string('guest_name', 100)->nullable()->after('user_id');
            $table->string('guest_email', 255)->nullable()->after('guest_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['guest_name', 'guest_email']);
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->change();
        });
    }
};
