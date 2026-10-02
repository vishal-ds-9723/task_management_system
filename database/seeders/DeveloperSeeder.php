<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\DeveloperSkill;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeveloperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create / Update Developer Users
        $john = User::updateOrCreate(
            ['email' => 'john@developer.com'],
            [
                'name' => 'John Dev',
                'password' => Hash::make('password'),
                'role' => 'developer',
                'avatar_color' => 'linear-gradient(135deg,#8B5CF6,#A78BFA)',
            ]
        );

        $sarah = User::updateOrCreate(
            ['email' => 'sarah@developer.com'],
            [
                'name' => 'Sarah Developer',
                'password' => Hash::make('password'),
                'role' => 'developer',
                'avatar_color' => 'linear-gradient(135deg,#EC4899,#F472B6)',
            ]
        );

        $mike = User::updateOrCreate(
            ['email' => 'mike@developer.com'],
            [
                'name' => 'Mike Engineer',
                'password' => Hash::make('password'),
                'role' => 'developer',
                'avatar_color' => 'linear-gradient(135deg,#06B6D4,#22D3EE)',
            ]
        );

        // 2. Add Developer Skills
        $skillsData = [
            $john->id => [
                ['name' => 'Laravel / PHP', 'type' => 'primary'],
                ['name' => 'MySQL / Database Architecture', 'type' => 'primary'],
                ['name' => 'REST APIs & WebSockets', 'type' => 'primary'],
                ['name' => 'Docker & DevOps', 'type' => 'secondary'],
                ['name' => 'Redis', 'type' => 'secondary'],
            ],
            $sarah->id => [
                ['name' => 'Flutter / Dart', 'type' => 'primary'],
                ['name' => 'Riverpod State Management', 'type' => 'primary'],
                ['name' => 'Mobile UI / UX Engineering', 'type' => 'primary'],
                ['name' => 'Firebase / Push Notifications', 'type' => 'secondary'],
                ['name' => 'iOS & Android Build Pipelines', 'type' => 'secondary'],
            ],
            $mike->id => [
                ['name' => 'Vue.js / React', 'type' => 'primary'],
                ['name' => 'Tailwind CSS / Frontend', 'type' => 'primary'],
                ['name' => 'TypeScript / JavaScript', 'type' => 'primary'],
                ['name' => 'Vite & Build Tools', 'type' => 'secondary'],
                ['name' => 'Performance Optimization', 'type' => 'secondary'],
            ],
        ];

        foreach ($skillsData as $userId => $skills) {
            foreach ($skills as $skill) {
                DeveloperSkill::firstOrCreate(
                    ['user_id' => $userId, 'name' => $skill['name']],
                    ['type' => $skill['type']]
                );
            }
        }

        // 3. Create Sample Development Tasks
        $client = Client::first();
        $admin = User::where('role', 'admin')->first() ?? User::first();

        if ($client && $admin) {
            $devTasks = [
                [
                    'title' => 'Implement Mobile Push Notifications & FCM Integration',
                    'client_id' => $client->id,
                    'assigned_to' => $sarah->id,
                    'created_by' => $admin->id,
                    'type' => 'post',
                    'priority' => 'high',
                    'status' => 'inprogress',
                    'preferred_tech' => 'Flutter, Firebase Cloud Messaging, Riverpod',
                    'dev_deadline' => now()->addDays(4)->toDateString(),
                    'project_start_date' => now()->subDays(2)->toDateString(),
                    'launch_date' => now()->addDays(10)->toDateString(),
                    'modules' => "1. Device Token Registration API\n2. Local Notifications Payload Handler\n3. Foreground & Background Notification Listeners",
                    'brief' => 'Integrate Firebase push notifications for instant alerts on task assignments and approvals.',
                ],
                [
                    'title' => 'Real-time WebSocket Chat Channels & Presence',
                    'client_id' => $client->id,
                    'assigned_to' => $john->id,
                    'created_by' => $admin->id,
                    'type' => 'post',
                    'priority' => 'urgent',
                    'status' => 'inprogress',
                    'preferred_tech' => 'Laravel Reverb, Pusher Protocol, MySQL',
                    'dev_deadline' => now()->addDays(2)->toDateString(),
                    'project_start_date' => now()->subDays(3)->toDateString(),
                    'launch_date' => now()->addDays(7)->toDateString(),
                    'modules' => "1. Direct & Group Chat Broadcast Events\n2. Read Receipt Indicators\n3. Reaction Synchronization",
                    'brief' => 'Implement low-latency messaging with real-time websocket broadcasting across web and mobile apps.',
                ],
                [
                    'title' => 'Client Analytics Dashboard & Performance Charts',
                    'client_id' => $client->id,
                    'assigned_to' => $mike->id,
                    'created_by' => $admin->id,
                    'type' => 'post',
                    'priority' => 'normal',
                    'status' => 'todo',
                    'preferred_tech' => 'Vue.js, Chart.js, Tailwind CSS',
                    'dev_deadline' => now()->addDays(6)->toDateString(),
                    'project_start_date' => now()->toDateString(),
                    'launch_date' => now()->addDays(14)->toDateString(),
                    'modules' => "1. Post Reach & Engagement Visualizer\n2. Monthly Schedule Progress Tracking\n3. Export to CSV & PDF",
                    'brief' => 'Create interactive client-facing analytics graphs with date filtering and data exports.',
                ],
            ];

            foreach ($devTasks as $taskData) {
                Task::updateOrCreate(
                    ['title' => $taskData['title'], 'client_id' => $taskData['client_id']],
                    $taskData
                );
            }
        }
    }
}
