<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('created_at', 'tasks_created_at_idx');
            $table->index('deadline', 'tasks_deadline_idx');
            $table->index(['status', 'deadline'], 'tasks_status_deadline_idx');
            $table->index(['status', 'created_at'], 'tasks_status_created_at_idx');
            $table->index(['status', 'updated_at'], 'tasks_status_updated_at_idx');
            $table->index(['client_id', 'created_at'], 'tasks_client_created_at_idx');
            $table->index(['assigned_to', 'created_at'], 'tasks_assigned_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_created_at_idx');
            $table->dropIndex('tasks_deadline_idx');
            $table->dropIndex('tasks_status_deadline_idx');
            $table->dropIndex('tasks_status_created_at_idx');
            $table->dropIndex('tasks_status_updated_at_idx');
            $table->dropIndex('tasks_client_created_at_idx');
            $table->dropIndex('tasks_assigned_created_at_idx');
        });
    }
};
