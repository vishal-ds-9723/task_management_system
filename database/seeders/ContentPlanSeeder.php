<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientMonthlySchedule;
use Illuminate\Database\Seeder;

/**
 * Demo monthly content packages for every active client, for last month, this month
 * and next month (relative to today). Safe to re-run: existing plans are updated, not duplicated.
 *
 *   php artisan db:seed --class=ContentPlanSeeder
 */
class ContentPlanSeeder extends Seeder
{
    /** Typical packages by client name; anything unlisted gets the default package. */
    private const PACKAGES = [
        'ABC Corp'         => ['posts' => 8,  'reels' => 4, 'stories' => 6, 'carousel' => 2],
        'TechNova'         => ['posts' => 6,  'reels' => 2, 'carousel' => 2, 'videos' => 1],
        'FreshEats'        => ['posts' => 6,  'reels' => 3, 'stories' => 4, 'carousel' => 2],
        'Mango Brand'      => ['posts' => 5,  'reels' => 3, 'stories' => 3],
        'StyleCraft'       => ['posts' => 6,  'reels' => 4, 'stories' => 6, 'carousel' => 3],
        'AutoDrive'        => ['posts' => 5,  'reels' => 2, 'videos' => 2, 'other' => 1],
        'Veera da Dhaba'   => ['posts' => 5,  'reels' => 2, 'stories' => 4, 'other' => 1],
        'Acme Corporation' => ['posts' => 4,  'reels' => 1, 'carousel' => 2, 'videos' => 1],
    ];

    private const DEFAULT_PACKAGE = ['posts' => 4, 'reels' => 2, 'stories' => 2];

    private const COLUMNS = ['posts', 'reels', 'stories', 'carousel', 'videos', 'guides', 'collections', 'other'];

    public function run(): void
    {
        $months = [now()->subMonthNoOverflow(), now(), now()->addMonthNoOverflow()];

        Client::where('is_active', true)->get()->each(function (Client $client) use ($months) {
            $package = self::PACKAGES[$client->name] ?? self::DEFAULT_PACKAGE;

            foreach ($months as $offset => $date) {
                // Next month is slightly bigger for a couple of clients so month-to-month changes show up
                $plan = collect(self::COLUMNS)->mapWithKeys(fn ($col) => [$col => (int) ($package[$col] ?? 0)])->all();
                if ($offset === 2 && isset($package['reels'])) {
                    $plan['reels'] += $client->id % 2;
                }

                ClientMonthlySchedule::updateOrCreate(
                    ['client_id' => $client->id, 'month' => $date->month, 'year' => $date->year],
                    $plan
                );
            }
        });

        $this->command?->info('Content plans seeded for ' . collect($months)->map->format('M Y')->implode(', ') . '.');
    }
}
