<?php

namespace Database\Seeders;

use App\Models\ActionItem;
use App\Models\ActionItemNote;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ActionItemSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@agency.com')->firstOrFail();
        $strategist = User::where('email', 'sara@agency.com')->firstOrFail();

        $fresh = Client::where('name', 'FreshEats')->first();
        $tech = Client::where('name', 'TechNova')->first();
        $veera = Client::where('name', 'Veera da Dhaba')->first();
        $abc = Client::where('name', 'ABC Corp')->first();
        $mango = Client::where('name', 'Mango Brand')->first();
        $auto = Client::where('name', 'AutoDrive')->first();
        $style = Client::where('name', 'StyleCraft')->first();

        // Clear existing action items
        ActionItemNote::truncate();
        Schema::disableForeignKeyConstraints();
        ActionItem::truncate();
        Schema::enableForeignKeyConstraints();

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
    }
}
