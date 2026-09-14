<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Symptoms are a reusable master list; a department advertises the ones that
 * matter to it. The pivot keeps its own ordering so the department page can
 * render them in the order the editor chose.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_symptom', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('department_id')
                ->constrained('departments')
                ->cascadeOnDelete();

            $table->foreignId('symptom_id')
                ->constrained('symptoms')
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['department_id', 'symptom_id'], 'department_symptom_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_symptom');
    }
};
