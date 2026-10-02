<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        User::updateOrCreate(
            ['email' => 'admin@thelayout.co.in'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'avatar_color' => '#6366F1',
                'can_manage_social_metrics' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'designer@thelayout.co.in'],
            [
                'name' => 'Alex Designer',
                'password' => Hash::make('designer123'),
                'role' => 'designer',
                'avatar_color' => '#06B6D4',
                'can_manage_social_metrics' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'dev@thelayout.co.in'],
            [
                'name' => 'David Developer',
                'password' => Hash::make('dev123'),
                'role' => 'developer',
                'avatar_color' => '#10B981',
                'can_manage_social_metrics' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'strategist@thelayout.co.in'],
            [
                'name' => 'Sophia Strategist',
                'password' => Hash::make('strategist123'),
                'role' => 'strategist',
                'avatar_color' => '#F59E0B',
                'can_manage_social_metrics' => true,
            ]
        );

        User::updateOrCreate(
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
    }
}
