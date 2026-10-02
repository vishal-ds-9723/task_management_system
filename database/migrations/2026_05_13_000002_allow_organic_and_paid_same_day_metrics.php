<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = Schema::getIndexes('social_media_post_metrics');
        $hasOldIndex = collect($indexes)->contains(fn ($index) => ($index['name'] ?? null) === 'smp_metrics_post_date_unique');
        $hasNewIndex = collect($indexes)->contains(fn ($index) => ($index['name'] ?? null) === 'smp_metrics_post_date_source_unique');

        Schema::table('social_media_post_metrics', function (Blueprint $table) use ($hasOldIndex, $hasNewIndex) {
            if ($hasOldIndex) {
                $table->dropUnique('smp_metrics_post_date_unique');
            }
            if (!$hasNewIndex) {
                $table->unique(['social_media_post_id', 'snapshot_date', 'paid_promotion'], 'smp_metrics_post_date_source_unique');
            }
        });
    }

    public function down(): void
    {
        $indexes = Schema::getIndexes('social_media_post_metrics');
        $hasOldIndex = collect($indexes)->contains(fn ($index) => ($index['name'] ?? null) === 'smp_metrics_post_date_unique');
        $hasNewIndex = collect($indexes)->contains(fn ($index) => ($index['name'] ?? null) === 'smp_metrics_post_date_source_unique');

        Schema::table('social_media_post_metrics', function (Blueprint $table) use ($hasOldIndex, $hasNewIndex) {
            if ($hasNewIndex) {
                $table->dropUnique('smp_metrics_post_date_source_unique');
            }
            if (!$hasOldIndex) {
                $table->unique(['social_media_post_id', 'snapshot_date'], 'smp_metrics_post_date_unique');
            }
        });
    }
};
