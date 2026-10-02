<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('due_at');
            $table->enum('priority', ['normal', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'in_progress', 'done', 'overdue'])->default('pending');
            $table->dateTime('completed_at')->nullable();
            $table->text('completion_note')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->enum('recurring', ['daily', 'weekly', 'monthly'])->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('action_items')->nullOnDelete();
            $table->timestamps();

            $table->index(['assigned_to', 'status']);
            $table->index(['created_by', 'status']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_items');
    }
};
