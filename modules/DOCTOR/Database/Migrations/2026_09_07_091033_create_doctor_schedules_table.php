<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->enum('day_of_week', ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri']);
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('consultation_type', ['in_person', 'online', 'both'])->default('in_person');
            $table->enum('availability_status', ['available', 'limited', 'booked_out'])->default('available');
            $table->unsignedInteger('max_patients')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
    //DOCTOR CAN DO CONSULTANCY MULTIPLE TIME A DAY
            $table->unique(['doctor_id', 'start_time'], 'doc_sched_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
    }
};