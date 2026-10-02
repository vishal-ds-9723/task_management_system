<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->longText('google_calendar_token')->nullable()->after('remember_token');
            $table->string('google_calendar_id')->nullable()->after('google_calendar_token');
        });

        Schema::table('festival_selections', function (Blueprint $table) {
            $table->string('google_event_id')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_calendar_token', 'google_calendar_id']);
        });

        Schema::table('festival_selections', function (Blueprint $table) {
            $table->dropColumn('google_event_id');
        });
    }
};
