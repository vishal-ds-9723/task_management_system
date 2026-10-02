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
        Schema::create('client_social_media_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'tiktok', 'whatsapp']);
            $table->string('url');
            $table->string('label')->nullable(); // e.g., "Main Account", "Brand Account", "Business Account"
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['client_id', 'platform', 'url']);
            $table->index(['client_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_social_media_links');
    }
};
