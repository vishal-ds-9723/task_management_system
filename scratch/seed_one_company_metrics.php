<?php

use App\Models\Client;
use App\Models\ClientSocialMediaLink;
use App\Models\Task;
use App\Models\SocialMediaPost;
use App\Models\SocialMediaPostMetric;
use App\Models\User;
use Illuminate\Support\Carbon;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$clientId = 13;
$client = Client::find($clientId);

if (!$client) {
    die("Client 13 not found.\n");
}

$user = User::where('role', 'admin')->first() ?: User::first();
if (!$user) {
    die("No user found in database.\n");
}

echo "Seeding data for Client: {$client->name} using User ID: {$user->id}\n";

$accounts = ClientSocialMediaLink::where('client_id', $clientId)->get();
if ($accounts->isEmpty()) {
    echo "Creating dummy social accounts...\n";
    $accounts[] = ClientSocialMediaLink::create(['client_id' => $clientId, 'platform' => 'instagram', 'label' => 'Main Instagram', 'url' => 'https://insta.com/one']);
    $accounts[] = ClientSocialMediaLink::create(['client_id' => $clientId, 'platform' => 'facebook', 'label' => 'Facebook Page', 'url' => 'https://fb.com/one']);
}

$titles = ['Website Launch Post', 'New Reel: Spring Collection', 'Product Carousel', 'Company Story', 'LinkedIn Thought Leadership'];
$types = ['post', 'reel', 'carousel', 'story', 'post'];

foreach ($titles as $idx => $title) {
    $type = $types[$idx];
    $account = $accounts->random();
    
    echo "Creating task: $title for {$account->label}...\n";
    
    $task = Task::create([
        'title' => $title,
        'client_id' => $clientId,
        'client_social_media_link_id' => $account->id,
        'type' => $type,
        'status' => 'published',
        'platform' => [$account->platform],
        'created_by' => $user->id,
        'assigned_to' => $user->id,
        'deadline' => now()->subDays(5),
    ]);

    $post = SocialMediaPost::create([
        'task_id' => $task->id,
        'platform' => $account->platform,
        'post_type' => $type,
        'post_url' => 'https://example.com/post/' . uniqid(),
        'posted_at' => now()->subDays(rand(1, 10)),
        'posted_by' => $user->id,
    ]);

    for ($i = 10; $i >= 0; $i--) {
        SocialMediaPostMetric::create([
            'social_media_post_id' => $post->id,
            'snapshot_date' => now()->subDays($i)->format('Y-m-d'),
            'reach' => rand(500, 10000),
            'impressions' => rand(1000, 20000),
            'likes' => rand(50, 1000),
            'views' => $type === 'reel' ? rand(1000, 50000) : rand(0, 500),
            'ad_spend_inr' => rand(0, 1) ? rand(200, 2000) : 0,
            'paid_promotion' => rand(0, 1),
            'target_area' => collect(['Mumbai', 'Delhi', 'Bangalore', 'London', 'Dubai'])->random(),
        ]);
    }
}

echo "Done! Seeded data for Client 13.\n";
