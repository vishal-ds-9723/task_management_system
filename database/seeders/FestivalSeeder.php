<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Festival;

class FestivalSeeder extends Seeder
{
    public function run(): void
    {
        $festivals = [
            ['name' => 'New Year', 'date' => '2026-01-01', 'emoji' => '🎆', 'category' => 'international', 'description' => 'Celebrate the start of a new year with festive content.'],
            ['name' => 'Republic Day', 'date' => '2026-01-26', 'emoji' => '🇮🇳', 'category' => 'national', 'description' => 'Honor India\'s Republic Day with patriotic posts.'],
            ['name' => 'Valentine\'s Day', 'date' => '2026-02-14', 'emoji' => '❤️', 'category' => 'international', 'description' => 'Share love-themed content for Valentine\'s Day.'],
            ['name' => 'Holi', 'date' => '2026-03-17', 'emoji' => '🎨', 'category' => 'religious', 'description' => 'Festival of colors - vibrant and colorful content.'],
            ['name' => 'Women\'s Day', 'date' => '2026-03-08', 'emoji' => '👩', 'category' => 'awareness', 'description' => 'Celebrate and empower women worldwide.'],
            ['name' => 'Earth Day', 'date' => '2026-04-22', 'emoji' => '🌍', 'category' => 'awareness', 'description' => 'Promote environmental awareness and sustainability.'],
            ['name' => 'Labour Day', 'date' => '2026-05-01', 'emoji' => '👷', 'category' => 'international', 'description' => 'Honor workers and their contributions.'],
            ['name' => 'Mother\'s Day', 'date' => '2026-05-10', 'emoji' => '💐', 'category' => 'international', 'description' => 'Celebrate mothers with heartfelt content.'],
            ['name' => 'World Environment Day', 'date' => '2026-06-05', 'emoji' => '🌱', 'category' => 'awareness', 'description' => 'Raise awareness about environmental protection.'],
            ['name' => 'Father\'s Day', 'date' => '2026-06-21', 'emoji' => '👔', 'category' => 'international', 'description' => 'Celebrate fathers with appreciation posts.'],
            ['name' => 'Yoga Day', 'date' => '2026-06-21', 'emoji' => '🧘', 'category' => 'awareness', 'description' => 'International Day of Yoga - wellness content.'],
            ['name' => 'Independence Day', 'date' => '2026-08-15', 'emoji' => '🇮🇳', 'category' => 'national', 'description' => 'Celebrate India\'s Independence Day with pride.'],
            ['name' => 'Raksha Bandhan', 'date' => '2026-08-28', 'emoji' => '🎀', 'category' => 'religious', 'description' => 'Celebrate the bond between siblings.'],
            ['name' => 'Ganesh Chaturthi', 'date' => '2026-09-07', 'emoji' => '🐘', 'category' => 'religious', 'description' => 'Festival celebrating Lord Ganesha.'],
            ['name' => 'Teacher\'s Day', 'date' => '2026-09-05', 'emoji' => '📚', 'category' => 'national', 'description' => 'Honor teachers and their dedication.'],
            ['name' => 'Navratri', 'date' => '2026-10-07', 'emoji' => '🪔', 'category' => 'religious', 'description' => 'Nine nights of devotion, dance, and celebration.'],
            ['name' => 'Dussehra', 'date' => '2026-10-16', 'emoji' => '🏹', 'category' => 'religious', 'description' => 'Victory of good over evil - Vijayadashami.'],
            ['name' => 'Diwali', 'date' => '2026-11-04', 'emoji' => '🪔', 'category' => 'religious', 'description' => 'Festival of lights - the biggest celebration of the year.'],
            ['name' => 'Children\'s Day', 'date' => '2026-11-14', 'emoji' => '👧', 'category' => 'national', 'description' => 'Celebrate children and their joy.'],
            ['name' => 'Christmas', 'date' => '2026-12-25', 'emoji' => '🎄', 'category' => 'international', 'description' => 'Season of joy, giving, and festive content.'],
        ];

        foreach ($festivals as $festival) {
            Festival::updateOrCreate(
                ['name' => $festival['name'], 'date' => $festival['date']],
                $festival
            );
        }
    }
}
