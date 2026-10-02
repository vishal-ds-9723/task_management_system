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
        Schema::table('social_media_posts', function (Blueprint $table) {
            $table->string('proof_image')->nullable()->after('post_url');
        });

        // Make post_url nullable (for image-only proofs like WhatsApp)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \DB::statement('ALTER TABLE social_media_posts MODIFY post_url TEXT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_media_posts', function (Blueprint $table) {
            $table->dropColumn('proof_image');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            \DB::statement('ALTER TABLE social_media_posts MODIFY post_url TEXT NOT NULL');
        }
    }
};
