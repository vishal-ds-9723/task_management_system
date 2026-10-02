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

        DB::statement("ALTER TABLE client_social_media_links MODIFY platform ENUM('instagram','facebook','linkedin','twitter','tiktok','youtube','pinterest','snapchat','whatsapp','telegram','reddit','discord','tumblr','spotify','twitch','threads','behance','dribbble','medium','vimeo','sharechat','moj','koo','quora','github','weibo','signal','clubhouse') NOT NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE client_social_media_links MODIFY platform ENUM('instagram','facebook','twitter','linkedin','youtube','tiktok','whatsapp') NOT NULL");
    }
};
