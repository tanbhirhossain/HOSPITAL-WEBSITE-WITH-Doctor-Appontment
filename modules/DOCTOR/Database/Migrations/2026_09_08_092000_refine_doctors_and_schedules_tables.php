<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original schema shipped with a few constraints that do not survive
     * contact with the admin panel:
     *
     *  - `doctors.profile_photo` was NOT NULL, so a doctor could not be created
     *    before a photo existed.
     *  - `doctor_schedules` was unique on (doctor_id, start_time), which blocks
     *    the same hour being reused on a different weekday.
     */
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table): void {
            $table->string('profile_photo')->nullable()->change();

            if (! Schema::hasColumn('doctors', 'designation')) {
                $table->string('designation')->nullable();
            }

            if (! Schema::hasColumn('doctors', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
        });

        Schema::table('doctor_schedules', function (Blueprint $table): void {
            $table->dropUnique('doc_sched_unique');
            $table->unique(['doctor_id', 'day_of_week', 'start_time'], 'doctor_schedule_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_schedules', function (Blueprint $table): void {
            $table->dropUnique('doctor_schedule_slot_unique');
            $table->unique(['doctor_id', 'start_time'], 'doc_sched_unique');
        });

        Schema::table('doctors', function (Blueprint $table): void {
            $table->dropColumn(['designation', 'is_featured']);
            $table->string('profile_photo')->nullable(false)->change();
        });
    }
};
