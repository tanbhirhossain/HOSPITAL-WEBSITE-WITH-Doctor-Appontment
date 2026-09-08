<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('specialty');
            $table->string('qualification')->nullable();
            $table->string('experience')->nullable();
            $table->string('hospital_name')->default('AMZ Hospital Ltd.');
            $table->string('location')->default('AMZ Hospital, Dhaka');
            $table->string('profile_photo');
            $table->text('bio')->nullable();
            
            // Experience & Metrics
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->unsignedInteger('reviews_count')->default(0);
            
            // Verification flags
         
            // Social Links (JSON)
            $table->json('social_links')->nullable(); // {"facebook": "...", "linkedin": "...", "twitter": "...", "youtube": "..."}
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};