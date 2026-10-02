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
        $this->dropUniqueIfExists('social_media_post_metrics', 'smp_metrics_post_date_unique');
        $this->dropUniqueIfExists('social_media_post_metrics', 'smp_metrics_post_date_source_unique');

        if (! $this->hasIndex('social_media_post_metrics', 'smp_metrics_unique')) {
            Schema::table('social_media_post_metrics', function (Blueprint $table) {
                $table->unique(['social_media_post_id', 'snapshot_date', 'paid_promotion'], 'smp_metrics_unique');
            });
        }
    }

    public function down(): void
    {
        $this->dropUniqueIfExists('social_media_post_metrics', 'smp_metrics_unique');

        if (! $this->hasIndex('social_media_post_metrics', 'smp_metrics_post_date_source_unique')) {
            Schema::table('social_media_post_metrics', function (Blueprint $table) {
                $table->unique(['social_media_post_id', 'snapshot_date', 'paid_promotion'], 'smp_metrics_post_date_source_unique');
            });
        }
    }

    private function dropUniqueIfExists(string $tableName, string $indexName): void
    {
        if (! $this->hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropUnique($indexName);
        });
    }

    private function hasIndex(string $tableName, string $indexName): bool
    {
        return collect(Schema::getIndexes($tableName))
            ->contains(fn (array $index) => ($index['name'] ?? null) === $indexName);
    }
};
