<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_expertises', function (Blueprint $table): void {
           $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // e.g., Chest Pain
            $table->string('description'); // e.g., Evaluation of chest discomfort...
            $table->string('icon')->nullable(); // SVG path or class name
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_expertises');
    }
};