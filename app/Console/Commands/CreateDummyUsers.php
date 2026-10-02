<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateDummyUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tms:create-dummy-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create dummy Admin and team users for development and mobile testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating dummy users...');

        // 1. Ensure at least one Client exists
        $client = Client::firstOrCreate(
            ['name' => 'Acme Corporation'],
            [
                'category' => 'Technology / SaaS',
                'emoji' => '🚀',
                'color' => '#6366F1',
                'website' => 'https://acme.example.com',
                'is_active' => true,
            ]
        );

        // 2. Dummy Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@thelayout.co.in'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'avatar_color' => '#6366F1',
                'can_manage_social_metrics' => true,
            ]
        );
        $this->line("✔ Admin created: <fg=green>admin@thelayout.co.in</> / <fg=yellow>admin123</>");

        // 3. Dummy Designer
        $designer = User::updateOrCreate(
            ['email' => 'designer@thelayout.co.in'],
            [
                'name' => 'Alex Designer',
                'password' => Hash::make('designer123'),
                'role' => 'designer',
                'avatar_color' => '#06B6D4',
                'can_manage_social_metrics' => false,
            ]
        );
        $this->line("✔ Designer created: <fg=green>designer@thelayout.co.in</> / <fg=yellow>designer123</>");

        // 4. Dummy Developer
        $developer = User::updateOrCreate(
            ['email' => 'dev@thelayout.co.in'],
            [
                'name' => 'David Developer',
                'password' => Hash::make('dev123'),
                'role' => 'developer',
                'avatar_color' => '#10B981',
                'can_manage_social_metrics' => false,
            ]
        );
        $this->line("✔ Developer created: <fg=green>dev@thelayout.co.in</> / <fg=yellow>dev123</>");

        // 5. Dummy Strategist / Manager
        $strategist = User::updateOrCreate(
            ['email' => 'strategist@thelayout.co.in'],
            [
                'name' => 'Sophia Strategist',
                'password' => Hash::make('strategist123'),
                'role' => 'strategist',
                'avatar_color' => '#F59E0B',
                'can_manage_social_metrics' => true,
            ]
        );
        $this->line("✔ Strategist created: <fg=green>strategist@thelayout.co.in</> / <fg=yellow>strategist123</>");

        // 6. Dummy Client User
        $clientUser = User::updateOrCreate(
            ['email' => 'client@thelayout.co.in'],
            [
                'name' => 'Chris Client',
                'password' => Hash::make('client123'),
                'role' => 'client',
                'client_id' => $client->id,
                'avatar_color' => '#8B5CF6',
                'can_manage_social_metrics' => false,
            ]
        );
        $this->line("✔ Client created: <fg=green>client@thelayout.co.in</> / <fg=yellow>client123</>");

        $this->info("\nAll dummy accounts are ready to use!");
        return Command::SUCCESS;
    }
}
