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
        Schema::table('counselors', function (Blueprint $table) {
            $table->renameColumn('office_location', 'hospital');
            $table->dropColumn([
                'specializations',
                'bio',
                'email',
                'phone',
                'city',
                'region',
                'university',
                'is_available',
                'availability_schedule',
                'offers_online',
                'online_booking_url'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counselors', function (Blueprint $table) {
            $table->renameColumn('hospital', 'office_location');
            $table->json('specializations')->nullable();
            $table->text('bio')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('university')->nullable();
            $table->boolean('is_available')->default(true);
            $table->json('availability_schedule')->nullable();
            $table->boolean('offers_online')->default(false);
            $table->string('online_booking_url')->nullable();
        });
    }
};
