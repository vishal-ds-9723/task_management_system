<?php

use App\Models\Client;
use App\Models\Task;
use App\Models\SocialMediaPost;
use App\Models\SocialMediaPostMetric;
use App\Models\User;
use Carbon\Carbon;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$clientId = 1; // ABC Corp
$client = Client::find($clientId);

if (!$client) {
    echo "Client not found.\n";
    exit(1);
}

echo "Cleaning up existing social media data for {$client->name}...\n";
$taskIds = Task::where('client_id', $clientId)->pluck('id');
$posts = SocialMediaPost::whereIn('task_id', $taskIds)->get();
foreach ($posts as $post) {
    SocialMediaPostMetric::where('social_media_post_id', $post->id)->delete();
}

$admin = User::where('role', 'admin')->first();

$dataConfig = [
    'instagram' => [
        'types' => ['reel', 'post', 'story', 'carousel'],
        'base' => ['views' => 1200, 'reach' => 1000, 'likes' => 150, 'growth' => 20],
        'titles' => ['Summer Vibes', 'New Collection Launch', 'Behind the Scenes', 'Client Testimonial']
    ],
    'facebook' => [
        'types' => ['post', 'video'],
        'base' => ['views' => 800, 'reach' => 2500, 'likes' => 80, 'growth' => 15],
        'titles' => ['Company Milestone', 'Community Outreach', 'Industry Insights', 'Event Highlights']
    ],
    'linkedin' => [
        'types' => ['post', 'carousel', 'video'],
        'base' => ['views' => 3000, 'reach' => 800, 'likes' => 40, 'growth' => 10],
        'titles' => ['Professional Growth Tips', 'Quarterly Report Summary', 'Hiring Announcement', 'B2B Strategy']
    ],
    'youtube' => [
        'types' => ['video', 'reel'], // reel as shorts
        'base' => ['views' => 5000, 'reach' => 4000, 'likes' => 300, 'growth' => 50],
        'titles' => ['Product Deep Dive', 'How-to Guide', 'Customer Success Story', 'Brand Story Video']
    ],
    'twitter' => [
        'types' => ['post'],
        'base' => ['views' => 1500, 'reach' => 1200, 'likes' => 25, 'growth' => 5],
        'titles' => ['Daily Tech News', 'Quick Update', 'Poll of the Day', 'Thread: Future of Tech']
    ]
];

foreach ($dataConfig as $platform => $config) {
    echo "Generating unique data for {$platform}...\n";

    foreach ($config['titles'] as $idx => $title) {
        $type = $config['types'][$idx % count($config['types'])];
        
        // Create a unique task for each title/type combo
        $task = Task::create([
            'client_id' => $clientId,
            'title' => "[{$platform}] {$title}",
            'type' => $type,
            'status' => 'published',
            'platform' => [$platform],
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'post_date' => now()->subDays(rand(5, 25)),
            'deadline' => now(),
        ]);

        $post = SocialMediaPost::create([
            'task_id' => $task->id,
            'platform' => $platform,
            'post_type' => $type,
            'post_url' => "https://{$platform}.com/post/" . uniqid(),
            'posted_at' => $task->post_date,
            'posted_by' => $admin->id
        ]);

        // Generate metrics for this specific post
        $daysOfData = rand(10, 20);
        $base = $config['base'];

        for ($i = $daysOfData; $i >= 0; $i--) {
            $date = Carbon::parse($task->post_date)->addDays($daysOfData - $i);
            if ($date->isFuture()) continue;

            $multiplier = ($daysOfData - $i + 1);
            $randomFactor = rand(90, 110) / 100;

            SocialMediaPostMetric::create([
                'social_media_post_id' => $post->id,
                'snapshot_date' => $date,
                'views' => (int)($base['views'] + ($base['growth'] * $multiplier * $randomFactor)),
                'impressions' => (int)(($base['views'] + ($base['growth'] * $multiplier)) * 1.3),
                'reach' => (int)($base['reach'] + ($base['growth'] * 0.7 * $multiplier)),
                'likes' => (int)($base['likes'] + ($base['growth'] * 0.1 * $multiplier)),
                'comments' => rand(2, 15),
                'shares' => rand(1, 10),
                'paid_promotion' => ($idx === 0 && $i === 0), // One paid post per platform
                'ad_spend_inr' => ($idx === 0 && $i === 0) ? rand(1000, 5000) : null,
                'target_area' => ($idx === 0 && $i === 0) ? 'Tier 1 Cities' : null,
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]);
        }
    }
}

echo "Done! Varied fake data generated for ABC Corp.\n";
