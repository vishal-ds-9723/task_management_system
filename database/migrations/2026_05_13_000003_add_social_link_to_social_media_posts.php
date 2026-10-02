<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('social_media_posts', function (Blueprint $table) {
            $table->foreignId('client_social_media_link_id')
                ->nullable()
                ->after('task_id')
                ->constrained('client_social_media_links')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('social_media_posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_social_media_link_id');
        });
    }
};
