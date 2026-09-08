<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('department_category_id')
                ->nullable()
                ->constrained('department_categories')
                ->nullOnDelete();
            
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_popular_search')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->string('featured_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};