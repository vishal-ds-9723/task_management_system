<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One log per user per day: the day-level summary (blockers, plan for next day)
        Schema::create('daily_work_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->text('blockers')->nullable();
            $table->text('plan_next')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'log_date']);
            $table->index('log_date');
        });

        // What was worked on that day; task_id is null for non-task work (meetings, research, ...)
        Schema::create('daily_work_log_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_work_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('work_type', 30)->default('development');
            $table->text('description');
            $table->decimal('hours', 4, 2);
            $table->unsignedTinyInteger('progress')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_work_log_entries');
        Schema::dropIfExists('daily_work_logs');
    }
};
