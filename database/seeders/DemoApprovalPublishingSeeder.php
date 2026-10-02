<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoApprovalPublishingSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            [
                'title' => 'Demo Instagram Campaign - Awaiting Approval',
                'status' => 'review',
                'platform' => ['instagram', 'facebook'],
                'type' => 'carousel',
                'priority' => 'high',
                'submitted_at' => now()->subHours(3),
                'completed_at' => null,
            ],
            [
                'title' => 'Demo Product Launch - Ready to Publish',
                'status' => 'completed',
                'platform' => ['instagram', 'linkedin'],
                'type' => 'post',
                'priority' => 'normal',
                'submitted_at' => now()->subDay(),
                'completed_at' => now()->subHours(2),
            ],
            [
                'title' => 'Demo Brand Story - Published',
                'status' => 'published',
                'platform' => ['facebook'],
                'type' => 'story',
                'priority' => 'normal',
                'submitted_at' => now()->subDays(2),
                'completed_at' => now()->subDay(),
            ],
        ];

        DB::transaction(function () use ($tasks): void {
            foreach ($tasks as $task) {
                Task::updateOrCreate(
                    ['title' => $task['title'], 'created_by' => 4],
                    array_merge($task, [
                        'client_id' => 1,
                        'assigned_to' => 5,
                        'created_by' => 4,
                        'is_urgent_task' => false,
                        'brief' => 'Demo content created to preview the strategist approval and publishing workflow.',
                        'caption' => 'Discover our latest campaign update.',
                        'hashtags' => '#demo #campaign',
                        'deadline' => now()->addDays(7)->toDateString(),
                        'design_deadline' => now()->addDays(3)->toDateString(),
                        'post_date' => now()->addDays(5)->toDateString(),
                    ]),
                );
            }
        });
    }
}
