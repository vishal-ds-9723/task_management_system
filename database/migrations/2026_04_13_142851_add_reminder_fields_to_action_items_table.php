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
        Schema::table('action_items', function (Blueprint $table) {
            $table->dateTime('reminder_at')->nullable()->after('follow_up_date');
            $table->string('reminder_note', 500)->nullable()->after('reminder_at');
            $table->index('reminder_at');
        });
    }

    public function down(): void
    {
        Schema::table('action_items', function (Blueprint $table) {
            $table->dropIndex(['reminder_at']);
            $table->dropColumn(['reminder_at', 'reminder_note']);
        });
    }
};
