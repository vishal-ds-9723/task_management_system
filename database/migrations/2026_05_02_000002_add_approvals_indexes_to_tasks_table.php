<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['created_by', 'status', 'deadline', 'id'], 'tasks_creator_status_deadline_id_idx');
            $table->index(['created_by', 'status', 'created_at', 'id'], 'tasks_creator_status_created_id_idx');
            $table->index(['created_by', 'status', 'assigned_to'], 'tasks_creator_status_assigned_idx');
            $table->index(['created_by', 'status', 'client_id'], 'tasks_creator_status_client_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_creator_status_deadline_id_idx');
            $table->dropIndex('tasks_creator_status_created_id_idx');
            $table->dropIndex('tasks_creator_status_assigned_idx');
            $table->dropIndex('tasks_creator_status_client_idx');
        });
    }
};
