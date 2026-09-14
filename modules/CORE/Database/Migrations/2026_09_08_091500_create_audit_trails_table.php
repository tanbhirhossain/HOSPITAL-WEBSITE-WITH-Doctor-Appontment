<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table): void {
            $table->id();

            // Who performed the action (nullable for guest/system driven events).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable(); // denormalised so history survives user deletion

            // What happened.
            $table->string('event', 32); // created | updated | deleted | restored | login | logout | failed-login
            $table->string('module', 32)->nullable(); // CORE | DOCTOR | ...
            $table->text('description')->nullable();

            // Which record was touched (nullable for auth events).
            $table->nullableMorphs('auditable');

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Request context.
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();

            $table->timestamps();

            $table->index(['event', 'created_at'], 'audit_event_created_idx');
            $table->index('module', 'audit_module_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
