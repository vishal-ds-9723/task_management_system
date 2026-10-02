<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE tasks MODIFY COLUMN status ENUM('todo', 'inprogress', 'review', 'pending_approval', 'completed', 'published', 'on_hold') NOT NULL DEFAULT 'todo'"
        );
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('tasks')->where('status', 'on_hold')->update(['status' => 'todo']);

        DB::statement(
            "ALTER TABLE tasks MODIFY COLUMN status ENUM('todo', 'inprogress', 'review', 'pending_approval', 'completed', 'published') NOT NULL DEFAULT 'todo'"
        );
    }
};
