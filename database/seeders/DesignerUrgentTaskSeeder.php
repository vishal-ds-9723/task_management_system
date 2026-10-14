<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Notification;
use App\Models\Task;
use App\Models\TaskPause;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds realistic urgent tasks created directly by designers.
 *
 * Simulates urgent emergency requests raised by designers through the designer portal
 * (/designer/urgent-task), including auto-paused active tasks, audit logs, and notifications.
 *
 * Run independently via:
 *   php artisan db:seed --class=DesignerUrgentTaskSeeder
 */
class DesignerUrgentTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ── 1. ENSURE DESIGNERS EXIST ──
        $designers = $this->resolveDesigners();
        if ($designers->isEmpty()) {
            $this->command?->error('No designers found or could be created. Aborting seeder.');
            return;
        }

        // ── 2. ENSURE CLIENTS EXIST ──
        $clients = $this->resolveClients();
        if ($clients->isEmpty()) {
            $this->command?->error('No active clients found or could be created. Aborting seeder.');
            return;
        }

        // ── 3. RESOLVE ADMIN & STRATEGIST FOR NOTIFICATIONS ──
        $admin = User::where('role', 'admin')->first() ?? User::firstOrCreate(
            ['email' => 'admin@agency.com'],
            [
                'name' => 'Admin Owner',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'avatar_color' => 'linear-gradient(135deg,#4F6DF0,#7C8DF8)',
            ]
        );

        $strategist = User::where('role', 'strategist')->first() ?? User::firstOrCreate(
            ['email' => 'strategist@agency.com'],
            [
                'name' => 'Sophia Strategist',
                'password' => Hash::make('password'),
                'role' => 'strategist',
                'avatar_color' => '#F59E0B',
            ]
        );

        // Helper maps
        $designerPriya = $designers->firstWhere('name', 'Priya') ?? $designers->first();
        $designerRaj   = $designers->firstWhere('name', 'Raj') ?? $designers->skip(1)->first() ?? $designerPriya;
        $designerArjun = $designers->firstWhere('name', 'Arjun') ?? $designers->skip(2)->first() ?? $designerPriya;
        $designerNeha  = $designers->firstWhere('name', 'Neha') ?? $designers->skip(3)->first() ?? $designerPriya;
        $designerAlex  = $designers->firstWhere('name', 'Alex Designer') ?? $designers->skip(4)->first() ?? $designerRaj;

        $clientVeera = $clients->firstWhere('name', 'Veera da Dhaba') ?? $clients->first();
        $clientTech  = $clients->firstWhere('name', 'TechNova') ?? $clients->skip(1)->first() ?? $clients->first();
        $clientFresh = $clients->firstWhere('name', 'FreshEats') ?? $clients->skip(2)->first() ?? $clients->first();
        $clientMango = $clients->firstWhere('name', 'Mango Brand') ?? $clients->skip(3)->first() ?? $clients->first();
        $clientStyle = $clients->firstWhere('name', 'StyleCraft') ?? $clients->skip(4)->first() ?? $clients->first();
        $clientAcme  = $clients->firstWhere('name', 'Acme Corporation') ?? $clients->skip(5)->first() ?? $clients->first();
        $clientAbc   = $clients->firstWhere('name', 'ABC Corp') ?? $clients->first();

        // ── 4. PREPARE AN ACTIVE TASK TO AUTO-PAUSE FOR PRIYA ──
        $pausedTask = Task::firstOrCreate(
            [
                'title' => 'Veera da Dhaba – Spring Menu & Table Tent Card Redesign',
            ],
            [
                'client_id'           => $clientVeera->id,
                'assigned_to'         => $designerPriya->id,
                'created_by'          => $strategist->id,
                'type'                => 'brochure',
                'platform'            => null,
                'priority'            => 'normal',
                'status'              => 'inprogress',
                'is_urgent_task'      => false,
                'started_at'          => Carbon::now()->subDays(1)->setTime(10, 0),
                'deadline'            => Carbon::now()->addDays(4)->toDateString(),
                'design_deadline'     => Carbon::now()->addDays(2)->toDateString(),
                'brief'               => 'Redesign table tent cards and drink menu layout with rustic theme and updated QR code for order-at-table.',
                'total_paused_seconds' => 7200,
            ]
        );

        // ── 5. SPECIFY URGENT TASKS CREATED BY DESIGNERS ──
        $urgentTaskDefinitions = [
            // [1] In Progress - Auto-paused Priya's previous task
            [
                'title'               => 'Urgent Flash Offer: BOGO Tandoori Feast Weekend Poster',
                'client_id'           => $clientVeera->id,
                'designer'            => $designerPriya,
                'type'                => 'post',
                'platform'            => ['instagram', 'facebook'],
                'priority'            => 'urgent',
                'status'              => 'inprogress',
                'urgent_requested_by' => 'Client',
                'brief'               => "EMERGENCY: Client called store footfall is down 40% this weekend. Need high-voltage Buy 1 Get 1 Free Tandoori Platter creative live across Instagram & Facebook within 3 hours. Deep crimson background, sizzling kababs, bold gold typography 'THIS WEEKEND ONLY'.",
                'created_at'          => Carbon::now()->subHours(2)->subMinutes(15),
                'started_at'          => Carbon::now()->subHours(2),
                'deadline'            => Carbon::now()->addHours(3)->toDateString(),
                'design_deadline'     => Carbon::now()->addHours(2)->toDateString(),
                'auto_pause_task'     => $pausedTask,
            ],

            // [2] In Review - Raj created for TechNova server downtime notice
            [
                'title'               => 'Critical Incident Notice: CloudEngine API Maintenance Window',
                'client_id'           => $clientTech->id,
                'designer'            => $designerRaj,
                'type'                => 'post',
                'platform'            => ['linkedin', 'twitter'],
                'priority'            => 'urgent',
                'status'              => 'review',
                'urgent_requested_by' => 'Admin',
                'brief'               => 'TechNova engineering alerted of unscheduled core cluster reboot. Deliver clean, high-trust system status graphic for Twitter/X and LinkedIn. Include status page URL (status.technova.io) and 24/7 hotline. Minimalist tech dark mode theme.',
                'created_at'          => Carbon::now()->subHours(4)->subMinutes(30),
                'started_at'          => Carbon::now()->subHours(4),
                'submitted_at'        => Carbon::now()->subMinutes(45),
                'deadline'            => Carbon::now()->addHours(1)->toDateString(),
                'design_deadline'     => Carbon::now()->toDateString(),
            ],

            // [3] In Progress - Reel turnaround for FreshEats Organic Certification
            [
                'title'               => 'Emergency Viral Reel: 100% Farm-Certified Organic Proof',
                'client_id'           => $clientFresh->id,
                'designer'            => $designerArjun,
                'type'                => 'reel',
                'platform'            => ['instagram'],
                'priority'            => 'urgent',
                'status'              => 'inprogress',
                'urgent_requested_by' => 'Strategist',
                'brief'               => 'Competitor published misleading claim about local farm produce. Strategist urgently requested counter-narrative 15-second reel showing raw audit certificates, farm visits, and fresh harvest footage. Fast pace, punchy captions.',
                'created_at'          => Carbon::now()->subHours(1)->subMinutes(20),
                'started_at'          => Carbon::now()->subHours(1)->subMinutes(10),
                'deadline'            => Carbon::now()->addHours(4)->toDateString(),
                'design_deadline'     => Carbon::now()->addHours(3)->toDateString(),
            ],

            // [4] Pending Approval - Neha created for Mango Brand 24hr Flash Clearance
            [
                'title'               => 'Flash Clearance Alert: 40% Off Code FLASH40 Story Set',
                'client_id'           => $clientMango->id,
                'designer'            => $designerNeha,
                'type'                => 'story',
                'platform'            => ['instagram'],
                'priority'            => 'urgent',
                'status'              => 'pending_approval',
                'urgent_requested_by' => 'Client',
                'brief'               => 'Client marketing lead called at 9 AM requesting a 4-frame Instagram story blitz for unexpected warehouse inventory clearance. High urgency, countdown sticker space, swipe-up link mockup. Ready for final strategist approval.',
                'created_at'          => Carbon::now()->subHours(6),
                'started_at'          => Carbon::now()->subHours(5)->subMinutes(30),
                'submitted_at'        => Carbon::now()->subHours(1),
                'deadline'            => Carbon::now()->addHours(2)->toDateString(),
                'design_deadline'     => Carbon::now()->toDateString(),
            ],

            // [5] To Do - Alex Designer queued for StyleCraft Influencer Collab
            [
                'title'               => 'Breaking Influencer Collab Reveal Carousel (5 Slides)',
                'client_id'           => $clientStyle->id,
                'designer'            => $designerAlex,
                'type'                => 'carousel',
                'platform'            => ['instagram'],
                'priority'            => 'urgent',
                'status'              => 'todo',
                'urgent_requested_by' => 'Client',
                'brief'               => 'Top fashion influencer suddenly agreed to an exclusive reveal drop tonight at 7:00 PM. Designer needs to assemble high-fashion carousel using raw camera photos uploaded to client Drive. Editorial typography with serif headers.',
                'created_at'          => Carbon::now()->subMinutes(35),
                'deadline'            => Carbon::now()->addHours(5)->toDateString(),
                'design_deadline'     => Carbon::now()->addHours(4)->toDateString(),
            ],

            // [6] Completed / Delivered - Priya created urgent banner for Acme SaaS
            [
                'title'               => 'Urgent SOC2 Compliance Badge & Enterprise Trust Hero Banner',
                'client_id'           => $clientAcme->id,
                'designer'            => $designerPriya,
                'type'                => 'banner',
                'platform'            => null,
                'priority'            => 'urgent',
                'status'              => 'completed',
                'urgent_requested_by' => 'Admin',
                'brief'               => 'Enterprise client pitching Fortune 500 prospect this afternoon requested urgent hero banner and email header showcasing freshly awarded SOC2 Type II certification shield.',
                'created_at'          => Carbon::now()->subDays(1)->setTime(9, 30),
                'started_at'          => Carbon::now()->subDays(1)->setTime(10, 0),
                'submitted_at'        => Carbon::now()->subDays(1)->setTime(12, 15),
                'completed_at'        => Carbon::now()->subDays(1)->setTime(13, 0),
                'deadline'            => Carbon::now()->subDays(1)->toDateString(),
                'design_deadline'     => Carbon::now()->subDays(1)->toDateString(),
            ],

            // [7] Completed / Delivered - Raj created urgent flyer for Veera Grand Buffet
            [
                'title'               => 'Same-Day Print Flyer: Shahi Biryani Weekend Buffet 15% VIP Pass',
                'client_id'           => $clientVeera->id,
                'designer'            => $designerRaj,
                'type'                => 'flyer',
                'platform'            => null,
                'priority'            => 'urgent',
                'status'              => 'completed',
                'urgent_requested_by' => 'Client',
                'brief'               => 'Print shop cutoff is 2 PM. Client requested print-ready A5 flyer for Friday corporate lunch pass distribution. CMYK with 3mm bleed, vector logos, and map QR.',
                'created_at'          => Carbon::now()->subDays(2)->setTime(11, 0),
                'started_at'          => Carbon::now()->subDays(2)->setTime(11, 15),
                'submitted_at'        => Carbon::now()->subDays(2)->setTime(13, 0),
                'completed_at'        => Carbon::now()->subDays(2)->setTime(13, 30),
                'deadline'            => Carbon::now()->subDays(2)->toDateString(),
                'design_deadline'     => Carbon::now()->subDays(2)->toDateString(),
            ],

            // [8] Review - Arjun created urgent Diwali packaging mockup for ABC Corp
            [
                'title'               => 'Urgent Festival Hamper Custom Sleeve Mockup & 3D Render',
                'client_id'           => $clientAbc->id,
                'designer'            => $designerArjun,
                'type'                => 'product_design',
                'platform'            => null,
                'priority'            => 'urgent',
                'status'              => 'review',
                'urgent_requested_by' => 'Client',
                'brief'               => 'Corporate gifting client requested immediate 3D sleeve render of festive dry fruit and mango preserve gift box before signing off on 2,000 units manufacturing run.',
                'created_at'          => Carbon::now()->subHours(5),
                'started_at'          => Carbon::now()->subHours(4)->subMinutes(30),
                'submitted_at'        => Carbon::now()->subHours(1)->subMinutes(15),
                'deadline'            => Carbon::now()->addHours(6)->toDateString(),
                'design_deadline'     => Carbon::now()->addHours(4)->toDateString(),
            ],
        ];

        // ── 6. SEED URGENT TASKS ──
        $seededCount = 0;

        foreach ($urgentTaskDefinitions as $taskDef) {
            $designer = $taskDef['designer'];

            /** @var Task $urgentTask */
            $urgentTask = Task::updateOrCreate(
                [
                    'title'          => $taskDef['title'],
                    'is_urgent_task' => true,
                ],
                [
                    'client_id'           => $taskDef['client_id'],
                    'assigned_to'         => $designer->id,
                    'created_by'          => $designer->id, // Designer self-created
                    'type'                => $taskDef['type'],
                    'platform'            => $taskDef['platform'],
                    'priority'            => 'urgent',
                    'status'              => $taskDef['status'],
                    'urgent_requested_by' => $taskDef['urgent_requested_by'],
                    'brief'               => $taskDef['brief'],
                    'caption'             => null,
                    'created_at'          => $taskDef['created_at'],
                    'started_at'          => $taskDef['started_at'] ?? null,
                    'submitted_at'        => $taskDef['submitted_at'] ?? null,
                    'completed_at'        => $taskDef['completed_at'] ?? null,
                    'deadline'            => $taskDef['deadline'] ?? null,
                    'design_deadline'     => $taskDef['design_deadline'] ?? null,
                ]
            );

            // Handle auto-paused task simulation if specified
            if (!empty($taskDef['auto_pause_task'])) {
                /** @var Task $autoPauseTarget */
                $autoPauseTarget = $taskDef['auto_pause_task'];
                $pauseReason = 'Auto-paused for urgent task: ' . $urgentTask->title;

                $autoPauseTarget->update([
                    'is_paused'    => true,
                    'paused_at'    => $urgentTask->started_at ?? now(),
                    'pause_reason' => $pauseReason,
                ]);

                TaskPause::updateOrCreate(
                    [
                        'task_id' => $autoPauseTarget->id,
                        'user_id' => $designer->id,
                        'reason'  => $pauseReason,
                    ],
                    [
                        'replacement_task_id' => $urgentTask->id,
                        'work_logged'         => 'Paused while working on urgent deliverable: ' . $urgentTask->title,
                        'duration_seconds'    => 7200,
                    ]
                );

                AuditLog::firstOrCreate(
                    [
                        'task_id' => $autoPauseTarget->id,
                        'action'  => 'Auto-paused for urgent task',
                    ],
                    [
                        'user_id'    => $designer->id,
                        'old_values' => ['is_paused' => false],
                        'new_values' => ['is_paused' => true, 'urgent_task_id' => $urgentTask->id],
                        'ip_address' => '127.0.0.1',
                        'created_at' => $urgentTask->started_at ?? now(),
                    ]
                );
            }

            // Create AuditLog for the urgent task creation
            AuditLog::firstOrCreate(
                [
                    'task_id' => $urgentTask->id,
                    'action'  => 'Created urgent task',
                ],
                [
                    'user_id'    => $designer->id,
                    'old_values' => [],
                    'new_values' => [
                        'title'        => $urgentTask->title,
                        'type'         => $urgentTask->type,
                        'requested_by' => $urgentTask->urgent_requested_by,
                        'priority'     => 'urgent',
                    ],
                    'ip_address' => '127.0.0.1',
                    'created_at' => $urgentTask->created_at,
                ]
            );

            // Create in-app notifications without dispatching external FCM events
            Notification::withoutEvents(function () use ($urgentTask, $designer, $admin, $strategist) {
                // Admin notification
                if ($admin) {
                    Notification::firstOrCreate(
                        [
                            'user_id' => $admin->id,
                            'task_id' => $urgentTask->id,
                            'title'   => 'Urgent task created by designer',
                        ],
                        [
                            'icon'       => '🚨',
                            'subtitle'   => "{$designer->name} created urgent task \"{$urgentTask->title}\" (requested by {$urgentTask->urgent_requested_by})",
                            'link'       => route('admin.tasks.show', $urgentTask->id),
                            'created_at' => $urgentTask->created_at,
                        ]
                    );
                }

                // Strategist notification
                if ($strategist) {
                    Notification::firstOrCreate(
                        [
                            'user_id' => $strategist->id,
                            'task_id' => $urgentTask->id,
                            'title'   => 'Urgent task created by designer',
                        ],
                        [
                            'icon'       => '🚨',
                            'subtitle'   => "{$designer->name} raised urgent task for {$urgentTask->client?->name}: \"{$urgentTask->title}\"",
                            'link'       => route('admin.tasks.show', $urgentTask->id),
                            'created_at' => $urgentTask->created_at,
                        ]
                    );
                }
            });

            $seededCount++;
        }

        $this->command?->info("Successfully seeded {$seededCount} urgent tasks created by designers.");
    }

    /**
     * Resolve existing designers or create default ones if none exist.
     */
    protected function resolveDesigners()
    {
        $existing = User::where('role', 'designer')->get();
        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $defaults = [
            ['name' => 'Priya',         'email' => 'priya@agency.com',         'avatar_color' => '#10B981'],
            ['name' => 'Raj',           'email' => 'raj@agency.com',           'avatar_color' => '#3B82F6'],
            ['name' => 'Arjun',         'email' => 'arjun@agency.com',         'avatar_color' => '#4F6DF0'],
            ['name' => 'Neha',          'email' => 'neha@agency.com',          'avatar_color' => '#8B5CF6'],
            ['name' => 'Alex Designer', 'email' => 'designer@thelayout.co.in', 'avatar_color' => '#06B6D4'],
        ];

        foreach ($defaults as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'         => $data['name'],
                    'password'     => Hash::make('password'),
                    'role'         => 'designer',
                    'avatar_color' => $data['avatar_color'],
                ]
            );
        }

        return User::where('role', 'designer')->get();
    }

    /**
     * Resolve active clients or create fallback clients if none exist.
     */
    protected function resolveClients()
    {
        $existing = Client::where('is_active', true)->get();
        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $defaults = [
            ['name' => 'Veera da Dhaba',   'category' => 'Food & Bev / Restaurant', 'emoji' => '🍛', 'color' => '#DC2626'],
            ['name' => 'TechNova',         'category' => 'Technology',              'emoji' => '💻', 'color' => '#3B82F6'],
            ['name' => 'FreshEats',        'category' => 'Food & Bev',              'emoji' => '🥗', 'color' => '#10B981'],
            ['name' => 'Mango Brand',      'category' => 'Lifestyle',              'emoji' => '🥭', 'color' => '#F59E0B'],
            ['name' => 'StyleCraft',       'category' => 'Fashion',                'emoji' => '👗', 'color' => '#8B5CF6'],
            ['name' => 'Acme Corporation', 'category' => 'Technology / SaaS',      'emoji' => '🚀', 'color' => '#6366F1'],
            ['name' => 'ABC Corp',         'category' => 'FMCG / Food',            'emoji' => '🍎', 'color' => '#4F6DF0'],
        ];

        foreach ($defaults as $clientData) {
            Client::firstOrCreate(
                ['name' => $clientData['name']],
                array_merge($clientData, ['is_active' => true])
            );
        }

        return Client::where('is_active', true)->get();
    }
}
