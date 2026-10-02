<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('role'); // designer, strategist, developer, etc.
            $table->string('phone')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('avatar_color')->default('#3B82F6');
            $table->text('bio')->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
            $table->date('hire_date')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->integer('max_hours_per_week')->default(40);
            $table->timestamps();
            $table->index('role');
            $table->index('status');
        });

        Schema::create('task_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('role', ['lead', 'contributor', 'reviewer'])->default('contributor');
            $table->integer('estimated_hours')->default(8);
            $table->integer('actual_hours')->nullable();
            $table->date('assigned_date')->useCurrent();
            $table->date('completed_date')->nullable();
            $table->timestamps();
            $table->unique(['task_id', 'employee_id']);
            $table->index(['task_id', 'role']);
            $table->index(['employee_id', 'assigned_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_employees');
        Schema::dropIfExists('employees');
    }
};
