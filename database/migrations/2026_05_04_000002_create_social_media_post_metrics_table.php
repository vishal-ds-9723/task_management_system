<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_media_post_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_media_post_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');

            $table->unsignedBigInteger('views')->nullable();
            $table->unsignedBigInteger('impressions')->nullable();
            $table->unsignedBigInteger('reach')->nullable();
            $table->unsignedBigInteger('likes')->nullable();
            $table->unsignedBigInteger('comments')->nullable();
            $table->unsignedBigInteger('shares')->nullable();
            $table->unsignedBigInteger('profile_visits')->nullable();

            $table->boolean('paid_promotion')->default(false);
            $table->decimal('ad_spend_inr', 12, 2)->nullable();
            $table->string('target_area', 255)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['social_media_post_id', 'snapshot_date'], 'smp_metrics_post_date_idx');
            $table->unique(['social_media_post_id', 'snapshot_date'], 'smp_metrics_post_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_post_metrics');
    }
};
