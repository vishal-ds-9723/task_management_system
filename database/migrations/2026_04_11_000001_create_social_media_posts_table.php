<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // instagram, facebook, linkedin, twitter, tiktok, youtube
            $table->string('post_type'); // post, reel, story, carousel, video
            $table->text('post_url');
            $table->date('posted_at');
            $table->foreignId('posted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['task_id', 'platform']);
            $table->unique(['task_id', 'platform']); // one proof per platform per task
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_posts');
    }
};
