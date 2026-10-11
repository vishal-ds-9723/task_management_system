<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE tasks MODIFY COLUMN status ENUM('todo', 'inprogress', 'review', 'pending_approval', 'client_review', 'completed', 'published', 'on_hold') NOT NULL DEFAULT 'todo'"
        );
    }

    public function down(): void
    {
        DB::table('tasks')->where('status', 'client_review')->update(['status' => 'pending_approval']);
        DB::statement(
            "ALTER TABLE tasks MODIFY COLUMN status ENUM('todo', 'inprogress', 'review', 'pending_approval', 'completed', 'published', 'on_hold') NOT NULL DEFAULT 'todo'"
        );
    }
};
