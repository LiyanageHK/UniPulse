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
        Schema::create('counselors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable(); // e.g., "Licensed Clinical Psychologist"
            
            // Specialization and categories
            $table->string('category'); // mental_health, academic, career, financial
            $table->json('specializations'); // ["depression", "anxiety", "suicide_prevention"]
            $table->text('bio')->nullable();
            
            // Contact information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('office_location')->nullable();
            
            // Location for city-based matching
            $table->string('city');
            $table->string('region')->nullable(); // province/state
            $table->string('university')->nullable(); // if affiliated with specific university
            
            // Availability
            $table->boolean('is_available')->default(true);
            $table->json('availability_schedule')->nullable(); // working hours, days
            
            // Online consultation options
            $table->boolean('offers_online')->default(false);
            $table->string('online_booking_url')->nullable();
            
            $table->timestamps();
            
            $table->index('category');
            $table->index('city');
            $table->index('is_available');
            $table->index(['city', 'category', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselors');
    }
};
