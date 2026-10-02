<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('google_connected')->default(false)->after('is_active');
            $table->string('google_calendar_id')->nullable()->after('google_connected');
            $table->text('google_access_token')->nullable()->after('google_calendar_id');
            $table->text('google_refresh_token')->nullable()->after('google_access_token');
            $table->timestamp('google_token_expires_at')->nullable()->after('google_refresh_token');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'google_connected', 'google_calendar_id', 'google_access_token', 'google_refresh_token', 'google_token_expires_at'
            ]);
        });
    }
};
