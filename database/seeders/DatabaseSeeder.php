<?php

namespace Database\Seeders;

use App\Models\ActionItem;
use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── USERS ──
        $admin = User::create([
            'name' => 'Admin Owner',
            'email' => 'admin@agency.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'avatar_color' => 'linear-gradient(135deg,#4F6DF0,#7C8DF8)',
        ]);

        $strategist = User::create([
            'name' => 'Sara',
            'email' => 'sara@agency.com',
            'password' => Hash::make('password'),
            'role' => 'strategist',
            'avatar_color' => 'linear-gradient(135deg,#3B82F6,#60A5FA)',
        ]);

        $priya = User::create([
            'name' => 'Priya',
            'email' => 'priya@agency.com',
            'password' => Hash::make('password'),
            'role' => 'designer',
            'avatar_color' => '#10B981',
        ]);

        $raj = User::create([
            'name' => 'Raj',
            'email' => 'raj@agency.com',
            'password' => Hash::make('password'),
            'role' => 'designer',
            'avatar_color' => '#3B82F6',
        ]);

        $arjun = User::create([
            'name' => 'Arjun',
            'email' => 'arjun@agency.com',
            'password' => Hash::make('password'),
            'role' => 'designer',
            'avatar_color' => '#4F6DF0',
        ]);

        $neha = User::create([
            'name' => 'Neha',
            'email' => 'neha@agency.com',
            'password' => Hash::make('password'),
            'role' => 'designer',
            'avatar_color' => '#8B5CF6',
        ]);

        // ── CLIENTS ──
        $abc = Client::create(['name' => 'ABC Corp', 'category' => 'FMCG / Food', 'emoji' => '🍎', 'color' => '#4F6DF0']);
        $tech = Client::create(['name' => 'TechNova', 'category' => 'Technology', 'emoji' => '💻', 'color' => '#3B82F6']);
        $fresh = Client::create(['name' => 'FreshEats', 'category' => 'Food & Bev', 'emoji' => '🥗', 'color' => '#10B981']);
        $mango = Client::create(['name' => 'Mango Brand', 'category' => 'Lifestyle', 'emoji' => '🥭', 'color' => '#F59E0B']);
        $style = Client::create(['name' => 'StyleCraft', 'category' => 'Fashion', 'emoji' => '👗', 'color' => '#8B5CF6']);
        $auto = Client::create(['name' => 'AutoDrive', 'category' => 'Automotive', 'emoji' => '🚗', 'color' => '#4F6DF0']);
        $veera = Client::create(['name' => 'Veera da Dhaba', 'category' => 'Food & Bev / Restaurant', 'emoji' => '🍛', 'color' => '#DC2626']);

        // ── TASKS ──
        $taskData = [
            ['title' => 'Mango Campaign Reel', 'client_id' => $abc->id, 'assigned_to' => $priya->id, 'type' => 'reel', 'platform' => 'instagram', 'priority' => 'urgent', 'status' => 'review', 'deadline' => '2026-03-25', 'post_date' => '2026-03-27', 'caption' => 'Create a vibrant reel for Mango summer campaign. Use bright yellow/orange tones. Show product in lifestyle context. Add trending audio. Duration: 30 seconds.'],
            ['title' => 'FreshEats Summer Post', 'client_id' => $fresh->id, 'assigned_to' => $raj->id, 'type' => 'post', 'platform' => 'facebook', 'priority' => 'high', 'status' => 'inprogress', 'deadline' => '2026-03-28', 'post_date' => '2026-03-30'],
            ['title' => 'TechNova Banner Design', 'client_id' => $tech->id, 'assigned_to' => $arjun->id, 'type' => 'post', 'platform' => 'linkedin', 'priority' => 'normal', 'status' => 'todo', 'deadline' => '2026-03-30', 'post_date' => '2026-04-01'],
            ['title' => 'Brand Story – ABC', 'client_id' => $abc->id, 'assigned_to' => $priya->id, 'type' => 'story', 'platform' => 'instagram', 'priority' => 'normal', 'status' => 'inprogress', 'deadline' => '2026-04-01', 'post_date' => '2026-04-02'],
            ['title' => 'Product Launch Reel', 'client_id' => $mango->id, 'assigned_to' => $raj->id, 'type' => 'reel', 'platform' => 'instagram', 'priority' => 'high', 'status' => 'todo', 'deadline' => '2026-04-02', 'post_date' => '2026-04-03'],
            ['title' => 'Monthly Report Graphics', 'client_id' => $tech->id, 'assigned_to' => $neha->id, 'type' => 'post', 'platform' => 'linkedin', 'priority' => 'normal', 'status' => 'completed', 'deadline' => '2026-03-29', 'post_date' => '2026-03-29'],
            ['title' => 'Influencer Collab Video', 'client_id' => $fresh->id, 'assigned_to' => $arjun->id, 'type' => 'video', 'platform' => 'instagram', 'priority' => 'high', 'status' => 'todo', 'deadline' => '2026-04-03', 'post_date' => '2026-04-05'],
            ['title' => 'Festival Post Series', 'client_id' => $abc->id, 'assigned_to' => $priya->id, 'type' => 'post', 'platform' => 'facebook', 'priority' => 'normal', 'status' => 'completed', 'deadline' => '2026-04-04', 'post_date' => '2026-04-04'],
            ['title' => 'StyleCraft Instagram Story', 'client_id' => $style->id, 'assigned_to' => $neha->id, 'type' => 'story', 'platform' => 'instagram', 'priority' => 'normal', 'status' => 'review', 'deadline' => '2026-03-28', 'post_date' => '2026-03-29'],
            ['title' => 'AutoDrive Product Post', 'client_id' => $auto->id, 'assigned_to' => $raj->id, 'type' => 'post', 'platform' => 'facebook', 'priority' => 'normal', 'status' => 'inprogress', 'deadline' => '2026-04-01', 'post_date' => '2026-04-02'],
            ['title' => 'Mango Brand Carousel', 'client_id' => $mango->id, 'assigned_to' => $raj->id, 'type' => 'post', 'platform' => 'instagram', 'priority' => 'high', 'status' => 'review', 'deadline' => '2026-03-27', 'post_date' => '2026-03-28'],
            ['title' => 'FreshEats Product Reel', 'client_id' => $fresh->id, 'assigned_to' => $raj->id, 'type' => 'reel', 'platform' => 'instagram', 'priority' => 'normal', 'status' => 'review', 'deadline' => '2026-03-26', 'post_date' => '2026-03-28'],

            // ── VEERA DA DHABA TASKS ──
            ['title' => 'Food Photography – Main Menu', 'client_id' => $veera->id, 'assigned_to' => $priya->id, 'type' => 'post', 'platform' => 'instagram', 'priority' => 'urgent', 'status' => 'inprogress', 'deadline' => '2026-04-05', 'post_date' => '2026-04-06', 'caption' => 'High-quality food photography of signature dishes. Showcasing Butter Chicken, Paneer Tikka, and Naan. Use warm lighting. Professional plating. Ready for Instagram feed.'],
            ['title' => 'Veera da Dhaba Welcome Reel', 'client_id' => $veera->id, 'assigned_to' => $raj->id, 'type' => 'reel', 'platform' => 'instagram', 'priority' => 'high', 'status' => 'todo', 'deadline' => '2026-04-08', 'post_date' => '2026-04-10', 'caption' => 'Create an engaging reel showing the restaurant ambiance, food preparation, and happy customers. Include music and text overlays.'],
            ['title' => 'Weekly Lunch Special Post', 'client_id' => $veera->id, 'assigned_to' => $arjun->id, 'type' => 'post', 'platform' => 'facebook', 'priority' => 'high', 'status' => 'todo', 'deadline' => '2026-04-07', 'post_date' => '2026-04-07', 'caption' => 'Post about weekly lunch special offer. Include pricing and available items. Call to action for reservations.'],
            ['title' => 'Special Event – Grand Opening Stories', 'client_id' => $veera->id, 'assigned_to' => $neha->id, 'type' => 'story', 'platform' => 'instagram', 'priority' => 'normal', 'status' => 'review', 'deadline' => '2026-04-06', 'post_date' => '2026-04-06', 'caption' => 'Create 5-6 Instagram stories for Grand Opening event. Include behind-the-scenes, customer testimonials, and special offers.'],
            ['title' => 'Menu Cards Design', 'client_id' => $veera->id, 'assigned_to' => $priya->id, 'type' => 'post', 'platform' => 'facebook', 'priority' => 'normal', 'status' => 'completed', 'deadline' => '2026-04-03', 'post_date' => '2026-04-03', 'caption' => 'Design digital menu cards with high-res images of all dishes and pricing.'],
            ['title' => 'Customer Testimonial Video', 'client_id' => $veera->id, 'assigned_to' => $raj->id, 'type' => 'video', 'platform' => 'instagram', 'priority' => 'normal', 'status' => 'todo', 'deadline' => '2026-04-10', 'post_date' => '2026-04-12', 'caption' => 'Collect and edit 3-4 customer testimonial videos. Show their dining experience and favorite dishes.'],
            ['title' => 'Diwali Campaign Content', 'client_id' => $veera->id, 'assigned_to' => $arjun->id, 'type' => 'reel', 'platform' => 'instagram', 'priority' => 'normal', 'status' => 'todo', 'deadline' => '2026-04-15', 'post_date' => '2026-04-15', 'caption' => 'Create festive reel for Diwali promotion with special pricing and offers. Include celebration elements.'],
            ['title' => 'Veera da Dhaba Location Stories', 'client_id' => $veera->id, 'assigned_to' => $neha->id, 'type' => 'story', 'platform' => 'instagram', 'priority' => 'normal', 'status' => 'inprogress', 'deadline' => '2026-04-09', 'post_date' => '2026-04-09', 'caption' => 'Create location-based stories showing restaurant exterior, entrance, and seating areas.'],
            ['title' => 'Loyalty Program Announcement', 'client_id' => $veera->id, 'assigned_to' => $priya->id, 'type' => 'post', 'platform' => 'facebook', 'priority' => 'high', 'status' => 'review', 'deadline' => '2026-04-11', 'post_date' => '2026-04-12', 'caption' => 'Announce new loyalty program with benefits and how to enroll. Include attractive graphics.'],
        ];

        foreach ($taskData as $data) {
            $data['created_by'] = $strategist->id;
            Task::create($data);
        }

        // ── ACTION ITEMS ──
        $doneItem = ActionItem::create([
            'title' => 'Send FreshEats contract for signing',
            'description' => 'Email the updated contract PDF to FreshEats marketing head. Include revised pricing.',
            'client_id' => $fresh->id,
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->subDays(3),
            'priority' => 'urgent',
            'status' => 'done',
            'completed_at' => now()->subDays(2),
            'completion_note' => 'Sent via email. They confirmed receipt and will sign by Monday.',
            'follow_up_date' => now()->addDays(2),
        ]);

        // Follow-up of the done item (auto-created)
        ActionItem::create([
            'title' => 'Follow up: Send FreshEats contract for signing',
            'description' => 'Check if FreshEats has signed and returned the contract.',
            'client_id' => $fresh->id,
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->addDays(2)->setTime(10, 0),
            'priority' => 'normal',
            'status' => 'pending',
            'parent_id' => $doneItem->id,
        ]);

        ActionItem::create([
            'title' => 'Get brand guidelines from TechNova',
            'description' => 'They promised to send logo files, color codes and font pack. Ping their design lead on WhatsApp.',
            'client_id' => $tech->id,
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->subHours(5),
            'priority' => 'urgent',
            'status' => 'in_progress',
        ]);

        ActionItem::create([
            'title' => 'Confirm Veera da Dhaba shoot date',
            'description' => 'Call the owner and lock the date for food photography shoot. We need minimum 3 hours.',
            'client_id' => $veera->id,
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->subDay(),
            'priority' => 'normal',
            'status' => 'pending',
        ]);

        ActionItem::create([
            'title' => 'Prepare April content calendar for ABC Corp',
            'description' => 'Create a Google Sheet with all post dates, types, and captions for April.',
            'client_id' => $abc->id,
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->addDays(1)->setTime(14, 0),
            'priority' => 'urgent',
            'status' => 'pending',
        ]);

        ActionItem::create([
            'title' => 'Invoice Mango Brand for March deliverables',
            'description' => 'Generate invoice with all completed tasks. Include reel + carousel charges.',
            'client_id' => $mango->id,
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->addDays(3)->setTime(18, 0),
            'priority' => 'normal',
            'status' => 'pending',
        ]);

        ActionItem::create([
            'title' => 'Share AutoDrive competitor analysis',
            'description' => 'Check what Maruti and Hyundai are posting on Instagram. Screenshot 5-6 good examples.',
            'client_id' => $auto->id,
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->addHours(6),
            'priority' => 'normal',
            'status' => 'pending',
        ]);

        ActionItem::create([
            'title' => 'Onboard StyleCraft on project tracker',
            'description' => 'Add them as client, set up their task pipeline, and share the link with their team.',
            'client_id' => $style->id,
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->addDays(2)->setTime(11, 0),
            'priority' => 'normal',
            'status' => 'pending',
        ]);

        ActionItem::create([
            'title' => 'Weekly client report — all accounts',
            'description' => 'Compile reach, engagement, and follower growth for all active clients. Send summary on WhatsApp group.',
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->addDays(5)->setTime(17, 0),
            'priority' => 'normal',
            'status' => 'pending',
            'recurring' => 'weekly',
        ]);

        ActionItem::create([
            'title' => 'Renew Canva Pro subscription',
            'description' => 'Check if the team Canva Pro license is expiring. Renew before it lapses.',
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->subDays(2),
            'priority' => 'normal',
            'status' => 'done',
            'completed_at' => now()->subDays(2)->setTime(16, 30),
            'completion_note' => 'Renewed for 1 year. Invoice saved in Google Drive.',
        ]);

        ActionItem::create([
            'title' => 'Collect Veera da Dhaba menu photos',
            'description' => 'Owner will WhatsApp dish photos today. Download and organize in the shared folder.',
            'client_id' => $veera->id,
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->addHours(3),
            'priority' => 'urgent',
            'status' => 'pending',
        ]);

        ActionItem::create([
            'title' => 'Check daily social media comments',
            'description' => 'Reply to all pending comments and DMs across client accounts.',
            'assigned_to' => $strategist->id,
            'created_by' => $admin->id,
            'due_at' => now()->addDay()->setTime(10, 0),
            'priority' => 'normal',
            'status' => 'pending',
            'recurring' => 'daily',
        ]);

        // ── CALL DOMAIN SEEDERS ──
        $this->call([
            DeveloperSeeder::class,
            FestivalSeeder::class,
            DummyUserSeeder::class,
            PostInsightsSeeder::class,
            TaskApprovalFakeDataSeeder::class,
        ]);
    }
}
