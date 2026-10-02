<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldMediaCommand extends Command
{
    protected $signature = 'media:cleanup-old
                            {--months=12 : Delete media older than this many months}
                            {--chunk=200 : Batch size for cleanup}
                            {--dry-run : Show what would be deleted without deleting}';

    protected $description = 'Delete old media files and their database rows based on age';

    public function handle(): int
    {
        $months = max(1, (int) $this->option('months'));
        $chunkSize = max(50, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subMonths($months);

        $query = Media::query()->where('created_at', '<', $cutoff);
        $total = (clone $query)->count();

        $this->info('Old media cleanup started.');
        $this->line('Cutoff date: ' . $cutoff->toDateTimeString());
        $this->line('Mode: ' . ($dryRun ? 'DRY RUN' : 'DELETE'));
        $this->line('Matching records: ' . $total);

        if ($total === 0) {
            $this->info('No old media found.');
            return self::SUCCESS;
        }

        $deletedRows = 0;
        $deletedFiles = 0;
        $missingFiles = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->orderBy('id')->chunkById($chunkSize, function ($rows) use (
            $dryRun,
            &$deletedRows,
            &$deletedFiles,
            &$missingFiles,
            &$errors,
            $bar
        ): void {
            foreach ($rows as $media) {
                if (!$media instanceof Media) {
                    $errors++;
                    $bar->advance();
                    continue;
                }

                $disk = $media->disk ?: 'public';

                try {
                    $storage = Storage::disk($disk);
                    $exists = $media->path ? $storage->exists($media->path) : false;

                    if ($dryRun) {
                        if ($exists) {
                            $deletedFiles++;
                        } else {
                            $missingFiles++;
                        }

                        $deletedRows++;
                        $bar->advance();
                        continue;
                    }

                    if ($exists) {
                        $storage->delete($media->path);
                        $deletedFiles++;
                    } else {
                        $missingFiles++;
                    }

                    Media::query()->whereKey($media->id)->delete();
                    $deletedRows++;
                } catch (\Throwable $e) {
                    $errors++;
                    $this->newLine();
                    $this->warn('Failed media ID ' . $media->id . ': ' . $e->getMessage());
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info(($dryRun ? 'Dry run summary:' : 'Cleanup summary:'));
        $this->line('Rows ' . ($dryRun ? 'to delete' : 'deleted') . ': ' . $deletedRows);
        $this->line('Files ' . ($dryRun ? 'to delete' : 'deleted') . ': ' . $deletedFiles);
        $this->line('Missing files skipped: ' . $missingFiles);
        $this->line('Errors: ' . $errors);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
