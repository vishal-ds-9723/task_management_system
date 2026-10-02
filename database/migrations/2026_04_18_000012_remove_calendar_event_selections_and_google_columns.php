<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the calendar selections table and google columns from clients if present
        Schema::dropIfExists('calendar_event_selections');

        if (Schema::hasTable('clients')) {
            $cols = [
                'google_connected',
                'google_calendar_id',
                'google_access_token',
                'google_refresh_token',
                'google_token_expires_at',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('clients', $col)) {
                    Schema::table('clients', function (Blueprint $table) use ($col) {
                        $table->dropColumn($col);
                    });
                }
            }
        }
    }

    public function down(): void
    {
        // Recreate selections table
        Schema::create('calendar_event_selections', function (Blueprint $table) {
            $table->id();
            $table->string('calendar_event_id');
            $table->string('calendar_id')->nullable();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('user_id');
            $table->string('event_title')->nullable();
            $table->timestamp('start')->nullable();
            $table->timestamp('end')->nullable();
            $table->json('content_types')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['calendar_event_id', 'client_id']);
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add back google columns
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('google_connected')->default(false)->after('is_active');
            $table->string('google_calendar_id')->nullable()->after('google_connected');
            $table->text('google_access_token')->nullable()->after('google_calendar_id');
            $table->text('google_refresh_token')->nullable()->after('google_access_token');
            $table->timestamp('google_token_expires_at')->nullable()->after('google_refresh_token');
        });
    }
};
