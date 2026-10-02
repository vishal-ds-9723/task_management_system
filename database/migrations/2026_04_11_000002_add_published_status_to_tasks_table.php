<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL-only enum alteration
        if (\Illuminate\Support\Facades\Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('todo', 'inprogress', 'review', 'pending_approval', 'completed', 'published') DEFAULT 'todo'");
        }
    }

    public function down(): void
    {
        // MySQL-only enum revert
        if (\Illuminate\Support\Facades\Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('todo', 'inprogress', 'review', 'pending_approval', 'completed') DEFAULT 'todo'");
        }
    }
};
