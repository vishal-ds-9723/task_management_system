<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Change platform from ENUM to LONGTEXT (JSON) to support multiple platforms (MySQL only)
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE tasks MODIFY platform LONGTEXT NULL');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Revert back to ENUM if needed
            $table->enum('platform', ['instagram', 'facebook', 'linkedin', 'twitter'])->default('instagram')->change();
        });
    }
};
