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
        Schema::table('shoot_days', function (Blueprint $table) {
            // Drop end_time column if it exists
            if (Schema::hasColumn('shoot_days', 'end_time')) {
                $table->dropColumn('end_time');
            }
            // Drop start_time column if it exists
            if (Schema::hasColumn('shoot_days', 'start_time')) {
                $table->dropColumn('start_time');
            }
            // Add start_date (time) column if it doesn't exist
            if (!Schema::hasColumn('shoot_days', 'start_date')) {
                $table->time('start_date')->nullable()->after('shoot_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shoot_days', function (Blueprint $table) {
            // Add back the columns we dropped
            if (!Schema::hasColumn('shoot_days', 'start_time')) {
                $table->time('start_time')->nullable();
            }
            if (!Schema::hasColumn('shoot_days', 'end_time')) {
                $table->time('end_time')->nullable();
            }
            // Drop the start_date we added
            if (Schema::hasColumn('shoot_days', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};
