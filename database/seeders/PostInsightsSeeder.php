<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use App\Models\SocialMediaPost;
use App\Models\SocialMediaPostMetric;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PostInsightsSeeder extends Seeder
{
    public function run()
    {
        $client = Client::first();
        if (!$client) {
            $this->command->error('No client found to attach insights to.');
            return;
        }

        $admin = User::where('role', 'admin')->first() ?? User::first();
        
        // Find or create some completed tasks for this client to attach posts to
        $tasks = Task::where('client_id', $client->id)->where('status', 'completed')->take(5)->get();
        
        if ($tasks->count() < 5) {
            // Create some dummy tasks if needed
            for ($i = $tasks->count(); $i < 5; $i++) {
                $tasks->push(Task::create([
                    'title' => 'Sample Campaign Post ' . ($i + 1),
                    'client_id' => $client->id,
                    'created_by' => $admin->id,
                    'type' => 'post',
                    'priority' => 'high',
                    'status' => 'completed',
                    'platform' => ['instagram', 'facebook'],
                    'deadline' => Carbon::now()->subDays(rand(5, 30)),
                    'post_date' => Carbon::now()->subDays(rand(5, 30)),
                ]));
            }
        }

        DB::beginTransaction();

        try {
            $platforms = ['instagram', 'facebook', 'linkedin', 'twitter'];
            $postTypes = ['post', 'reel', 'carousel'];

            foreach ($tasks as $index => $task) {
                // Determine if this task's posts will be paid (e.g. task 1 and 3 are paid)
                $isPaid = in_array($index, [1, 3]);

                // Create 1-3 platforms per task
                $numPlatforms = rand(1, 3);
                $selectedPlatforms = array_rand(array_flip($platforms), $numPlatforms);
                if (!is_array($selectedPlatforms)) $selectedPlatforms = [$selectedPlatforms];

                foreach ($selectedPlatforms as $platform) {
                    // Check if post already exists for this task & platform (unique constraint)
                    $post = SocialMediaPost::firstOrCreate(
                        ['task_id' => $task->id, 'platform' => $platform],
                        [
                            'post_type' => $postTypes[array_rand($postTypes)],
                            'post_url' => 'https://example.com/post/' . uniqid(),
                            'posted_at' => Carbon::now()->subDays(rand(2, 20)),
                            'posted_by' => $admin->id,
                        ]
                    );

                    // Add metrics
                    $snapshotDate = Carbon::now()->subDays(1);
                    
                    // Generate realistic-looking numbers
                    $baseMultiplier = $isPaid ? rand(5, 20) : rand(1, 5);
                    $reach = rand(500, 2000) * $baseMultiplier;
                    $impressions = (int) ($reach * rand(12, 18) / 10); // 1.2-1.8x reach
                    $views = in_array($post->post_type, ['reel', 'video']) ? (int) ($impressions * rand(6, 9) / 10) : null;
                    
                    $likes = (int) ($reach * (rand(2, 8) / 100)); // 2-8% engagement rate
                    $comments = (int) ($likes * (rand(5, 15) / 100));
                    $shares = (int) ($likes * (rand(2, 10) / 100));
                    $profileVisits = (int) ($reach * (rand(1, 5) / 100));

                    SocialMediaPostMetric::updateOrCreate(
                        ['social_media_post_id' => $post->id, 'snapshot_date' => $snapshotDate],
                        [
                            'views' => $views,
                            'impressions' => $impressions,
                            'reach' => $reach,
                            'likes' => $likes,
                            'comments' => $comments,
                            'shares' => $shares,
                            'profile_visits' => $profileVisits,
                            'paid_promotion' => $isPaid,
                            'ad_spend_inr' => $isPaid ? rand(1000, 5000) : null,
                            'target_area' => $isPaid ? 'Pan India' : null,
                            'created_by' => $admin->id,
                        ]
                    );
                }
            }
            
            DB::commit();
            $this->command->info('Post insights seeded successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding data: ' . $e->getMessage());
        }
    }
}
