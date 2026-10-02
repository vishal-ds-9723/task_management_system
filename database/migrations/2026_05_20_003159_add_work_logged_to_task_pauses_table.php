<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_pauses', function (Blueprint $table) {
            $table->text('work_logged')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('task_pauses', function (Blueprint $table) {
            $table->dropColumn('work_logged');
        });
    }
};
