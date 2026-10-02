<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['assigned_to', 'status'], 'tasks_assigned_to_status_idx');
            $table->index(['created_by', 'assigned_to', 'status'], 'tasks_creator_assignee_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_assigned_to_status_idx');
            $table->dropIndex('tasks_creator_assignee_status_idx');
        });
    }
};
