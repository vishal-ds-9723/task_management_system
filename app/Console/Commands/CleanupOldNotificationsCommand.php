<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class CleanupOldNotificationsCommand extends Command
{
    protected $signature = 'notifications:cleanup-old
                            {--months=12 : Delete notifications older than this many months}
                            {--chunk=500 : Batch size for cleanup}
                            {--dry-run : Show what would be deleted without deleting}';

    protected $description = 'Delete old notifications based on age';

    public function handle(): int
    {
        $months = max(1, (int) $this->option('months'));
        $chunkSize = max(50, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subMonths($months);

        $query = Notification::query()->where('created_at', '<', $cutoff);
        $total = (clone $query)->count();

        $this->info('Old notifications cleanup started.');
        $this->line('Cutoff date: ' . $cutoff->toDateTimeString());
        $this->line('Mode: ' . ($dryRun ? 'DRY RUN' : 'DELETE'));
        $this->line('Matching records: ' . $total);

        if ($total === 0) {
            $this->info('No old notifications found.');
            return self::SUCCESS;
        }

        $deletedRows = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->orderBy('id')->chunkById($chunkSize, function ($rows) use (
            $dryRun,
            &$deletedRows,
            &$errors,
            $bar
        ): void {
            foreach ($rows as $notification) {
                if (!$notification instanceof Notification) {
                    $errors++;
                    $bar->advance();
                    continue;
                }

                try {
                    if (!$dryRun) {
                        Notification::query()->whereKey($notification->id)->delete();
                    }

                    $deletedRows++;
                } catch (\Throwable $e) {
                    $errors++;
                    $this->newLine();
                    $this->warn('Failed notification ID ' . $notification->id . ': ' . $e->getMessage());
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info(($dryRun ? 'Dry run summary:' : 'Cleanup summary:'));
        $this->line('Rows ' . ($dryRun ? 'to delete' : 'deleted') . ': ' . $deletedRows);
        $this->line('Errors: ' . $errors);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
