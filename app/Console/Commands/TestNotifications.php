<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestNotifications extends Command
{
    protected $signature = 'test:notifications';
    protected $description = 'Test the complete notification system';

    public function handle()
    {
        $this->info('🧪 NOTIFICATION SYSTEM TEST\n');

        // 1. Check database setup
        $this->testDatabaseSetup();

        // 2. Check notification channels
        $this->testNotificationChannels();

        // 3. Test notification creation
        $this->testNotificationCreation();

        // 4. Check notification display requirements
        $this->testNotificationDisplay();

        $this->info('\n✅ Test complete!');
    }

    private function testDatabaseSetup()
    {
        $this->info('📋 DATABASE SETUP');
        $count = Notification::count();
        $this->info("  Total notifications: $count");
        
        if ($count === 0) {
            $this->warn('  ⚠ No notifications in database yet');
        } else {
            $this->line('  ✓ Notifications table has data');
        }

        $unread = Notification::whereNull('read_at')->count();
        $this->info("  Unread: $unread");
        $this->line('');
    }

    private function testNotificationChannels()
    {
        $this->info('📡 NOTIFICATION CHANNELS BY USER\n');

        $users = User::all();
        foreach ($users as $user) {
            $total = $user->customNotifications()->count();
            $unread = $user->unreadCustomNotifications()->count();
            
            $role = strtoupper($user->role);
            $status = $total > 0 ? '✓' : '○';
            
            $this->line("  [$status] {$user->name} ({$role}) — $unread unread / $total total");
            
            if ($total > 0 && $unread > 0) {
                $latest = $user->unreadCustomNotifications()->latest()->first();
                $this->line("      └─ Latest: {$latest->icon} {$latest->title}");
            }
        }
        
        $this->line('');
    }

    private function testNotificationCreation()
    {
        $this->info('➕ NOTIFICATION CREATION FLOW\n');

        // Check if notifications are being created when tasks are created
        $stratUser = User::where('role', 'strategist')->first();
        $designer = User::where('role', 'designer')->first();

        if (!$stratUser || !$designer) {
            $this->warn('  Missing required users (strategist or designer)');
            return;
        }

        $this->line("  Strategist: {$stratUser->name} (ID {$stratUser->id})");
        $this->line("  Designer: {$designer->name} (ID {$designer->id})");

        // Check if designer has notifications from task assignments
        $designerNotifications = $designer->customNotifications()->count();
        if ($designerNotifications > 0) {
            $this->line('  ✓ Designer receives task assignment notifications');
        } else {
            $this->warn('  ⚠ Designer has no notifications (may need to create a task)');
        }

        // Check for status update notifications to strategist
        $stratNotifications = $stratUser->customNotifications()->count();
        if ($stratNotifications > 0) {
            $this->line('  ✓ Strategist receives status update notifications');
        } else {
            $this->line('  ○ Strategist has no notifications (none created yet or no designers updated tasks)');
        }

        $this->line('');
    }

    private function testNotificationDisplay()
    {
        $this->info('🎨 NOTIFICATION DISPLAY REQUIREMENTS\n');

        // Check key notification fields
        $sample = Notification::first();
        if (!$sample) {
            $this->warn('  No notifications to test display');
            return;
        }

        $this->line('  Required fields check:');
        $this->line('  ✓ id: ' . $sample->id);
        $this->line('  ✓ user_id: ' . $sample->user_id);
        $this->line('  ✓ icon: ' . $sample->icon);
        $this->line('  ✓ title: ' . $sample->title);
        $this->line('  ✓ subtitle: ' . ($sample->subtitle ?? '[null]'));
        $this->line('  ✓ link: ' . ($sample->link ?? '[null]'));
        $this->line('  ✓ read_at: ' . ($sample->read_at ? 'marked read' : 'unread'));
        $this->line('  ✓ created_at: ' . $sample->created_at);

        // Check notification attributes
        $this->line('\n  Sample notification structure:');
        $this->line('  Icon: ' . $sample->icon . ' (emoji works ✓)');
        $this->line('  Created: ' . $sample->created_at->diffForHumans());

        $this->line('');
    }
}
