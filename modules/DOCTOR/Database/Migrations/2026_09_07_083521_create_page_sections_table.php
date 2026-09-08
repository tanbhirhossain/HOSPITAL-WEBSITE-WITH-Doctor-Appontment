<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key')->unique(); // 'hero', 'what_brings_you_here', 'urgent_care', 'bottom_cta'
            $table->string('badge')->nullable();      // 'AMZ MEDICAL DEPARTMENTS'
            $table->string('title');                  // 'Find the Right Department for Your Care.'
            $table->text('subtitle')->nullable();     // Section body text
            $table->string('primary_button_text')->nullable();
            $table->string('primary_button_url')->nullable();
            $table->string('secondary_button_text')->nullable();
            $table->string('secondary_button_url')->nullable();
            $table->string('image')->nullable();      // Background or section image path
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};