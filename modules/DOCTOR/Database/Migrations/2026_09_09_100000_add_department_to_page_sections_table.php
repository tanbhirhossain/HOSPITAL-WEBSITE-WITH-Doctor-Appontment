<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Page sections are owned by a department: every department page carries its
 * own hero / intro / call-to-action blocks instead of one global set.
 *
 * `section_key` therefore stops being globally unique and becomes unique per
 * department. The column stays nullable so existing rows survive the deploy;
 * sections with no department are simply not attached to any page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_sections', function (Blueprint $table): void {
            $table->foreignId('department_id')
                ->nullable()
                ->after('id')
                ->constrained('departments')
                ->cascadeOnDelete();
        });

        Schema::table('page_sections', function (Blueprint $table): void {
            $table->dropUnique(['section_key']);
            $table->unique(['department_id', 'section_key'], 'page_section_department_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('page_sections', function (Blueprint $table): void {
            $table->dropUnique('page_section_department_key_unique');
            $table->unique('section_key');
        });

        Schema::table('page_sections', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('department_id');
        });
    }
};
