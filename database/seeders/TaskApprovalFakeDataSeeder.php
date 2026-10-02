<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ClientSocialMediaLink;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class TaskApprovalFakeDataSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure media storage directories exist
        $storageDir = storage_path('app/public/task-media');
        $publicDir = public_path('storage/task-media');
        File::ensureDirectoryExists($storageDir);
        File::ensureDirectoryExists($publicDir);

        // Fetch primary users
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $strategist = User::where('role', 'strategist')->first() ?? User::first();
        $priya = User::where('email', 'priya@agency.com')->first() ?? User::where('role', 'designer')->first() ?? $admin;
        $raj = User::where('email', 'raj@agency.com')->first() ?? User::where('role', 'designer')->skip(1)->first() ?? $priya;
        $arjun = User::where('email', 'arjun@agency.com')->first() ?? User::where('role', 'designer')->skip(2)->first() ?? $priya;
        $neha = User::where('email', 'neha@agency.com')->first() ?? User::where('role', 'designer')->skip(3)->first() ?? $priya;

        // Fetch clients
        $veera = Client::where('name', 'like', '%Veera%')->first() ?? Client::firstOrCreate(['name' => 'Veera da Dhaba'], ['category' => 'Food & Bev / Restaurant', 'emoji' => '🍛', 'color' => '#DC2626']);
        $fresh = Client::where('name', 'like', '%Fresh%')->first() ?? Client::firstOrCreate(['name' => 'FreshEats'], ['category' => 'Food & Bev', 'emoji' => '🥗', 'color' => '#10B981']);
        $tech = Client::where('name', 'like', '%Tech%')->first() ?? Client::firstOrCreate(['name' => 'TechNova'], ['category' => 'Technology', 'emoji' => '💻', 'color' => '#3B82F6']);
        $style = Client::where('name', 'like', '%Style%')->first() ?? Client::firstOrCreate(['name' => 'StyleCraft'], ['category' => 'Fashion', 'emoji' => '👗', 'color' => '#8B5CF6']);
        $auto = Client::where('name', 'like', '%Auto%')->first() ?? Client::firstOrCreate(['name' => 'AutoDrive'], ['category' => 'Automotive', 'emoji' => '🚗', 'color' => '#4F6DF0']);
        $abc = Client::where('name', 'like', '%ABC%')->first() ?? Client::firstOrCreate(['name' => 'ABC Corp'], ['category' => 'FMCG / Food', 'emoji' => '🍎', 'color' => '#EA580C']);

        // Setup client social media links
        $this->ensureSocialLink($veera, 'facebook', 'https://www.facebook.com/veeradadhabaofficial', '@veeradadhaba', true);
        $this->ensureSocialLink($veera, 'instagram', 'https://www.instagram.com/veeradadhaba', '@veeradadhaba');
        $this->ensureSocialLink($fresh, 'instagram', 'https://www.instagram.com/fresheats_organics', '@fresheats_organics', true);
        $this->ensureSocialLink($fresh, 'facebook', 'https://www.facebook.com/fresheatsindia', '@fresheatsindia');
        $this->ensureSocialLink($tech, 'linkedin', 'https://www.linkedin.com/company/technova-cloud', 'TechNova Cloud Solutions', true);
        $this->ensureSocialLink($tech, 'instagram', 'https://www.instagram.com/technova.ai', '@technova.ai');
        $this->ensureSocialLink($style, 'instagram', 'https://www.instagram.com/stylecraft_studio', '@stylecraft_studio', true);
        $this->ensureSocialLink($style, 'facebook', 'https://www.facebook.com/stylecraftcouture', '@stylecraftcouture');
        $this->ensureSocialLink($auto, 'facebook', 'https://www.facebook.com/autodrivemotors', '@autodrivemotors', true);
        $this->ensureSocialLink($auto, 'linkedin', 'https://www.linkedin.com/company/autodrive-ev', 'AutoDrive Motors', false);
        $this->ensureSocialLink($abc, 'instagram', 'https://www.instagram.com/abcfoods.official', '@abcfoods.official', true);
        $this->ensureSocialLink($abc, 'whatsapp', 'https://wa.me/919876543210', 'WhatsApp Customer Care');

        // =========================================================================
        // 1. ENRICH TASK #29 (Veera da Dhaba - Loyalty Program Announcement)
        // =========================================================================
        $task29 = Task::find(29);
        if (!$task29) {
            $task29 = Task::create([
                'id' => 29,
                'title' => 'Loyalty Program Announcement',
                'client_id' => $veera->id,
                'assigned_to' => $priya->id,
                'created_by' => $strategist->id,
                'type' => 'post',
                'priority' => 'high',
                'status' => 'review',
            ]);
        }

        $veeraLinks = $veera->socialMediaLinks()->pluck('id')->toArray();
        $task29CreatedAt = Carbon::now()->subDays(3)->setTime(10, 15);
        $task29StartedAt = Carbon::now()->subDays(2)->setTime(11, 30);
        $task29SubmittedAt = Carbon::now()->subHours(4)->setTime(14, 20);

        $task29->update([
            'client_id' => $veera->id,
            'assigned_to' => $priya->id,
            'created_by' => $strategist->id,
            'title' => 'Loyalty Program Announcement',
            'type' => 'post',
            'priority' => 'high',
            'status' => 'review',
            'is_urgent_task' => false,
            'platform' => ['facebook', 'instagram'],
            'client_social_media_link_id' => $veeraLinks[0] ?? null,
            'selected_social_media_link_ids' => $veeraLinks,
            'brief' => 'Design a high-impact promotional banner announcing the launch of Veera da Dhaba’s "Shahi Rewards" Loyalty Club. Emphasize 20% instant cashback on the next 3 dine-ins, exclusive weekend biryani vouchers, and simple WhatsApp scan-to-join QR step. Brand palette: Deep crimson red (#DC2626) & warm saffron (#F59E0B).',
            'caption' => "👑 Introducing SHAHI REWARDS by Veera da Dhaba! 🍛✨\n\nEvery royal meal now earns you royal points! Sign up today and get:\n🎁 20% Instant Cashback on your next 3 dine-in visits\n🍛 Exclusive access to Chef's Special Weekend Biryani menu\n🎂 Surprise Birthday & Anniversary feasts on the house\n\n👉 Joining takes just 10 seconds! Scan the QR code at your table or tap the link in bio to enroll instantly via WhatsApp.\n\n📍 Visit us at Veera da Dhaba, Ring Road | 📞 Table Reservations: +91 98765 43210",
            'hashtags' => '#VeeraDaDhaba #ShahiRewards #DhabaLoyalty #IndianFoodLovers #BangaloreFoodies #DesiKhana #FoodieRewards #ButterChickenSpecial',
            'reference_links' => ['https://dribbble.com/shots/loyalty-cards-mockup', 'https://instagram.com/p/royal-dhaba-experience'],
            'deadline' => Carbon::now()->addDays(5)->toDateString(),
            'design_deadline' => Carbon::now()->addDays(2)->toDateString(),
            'post_date' => Carbon::now()->addDays(3)->toDateString(),
            'created_at' => $task29CreatedAt,
            'started_at' => $task29StartedAt,
            'submitted_at' => $task29SubmittedAt,
        ]);

        // Clean up any old media for Task 29
        foreach ($task29->media as $oldM) {
            $oldM->delete();
        }

        // Attach Media for Task 29
        $this->attachMediaToTask(
            $task29,
            'veera_shahi_rewards_banner.jpg',
            'Shahi Rewards Launch Banner (1080x1080)',
            'VEERA DA DHABA',
            'SHAHI REWARDS CLUB',
            '20% INSTANT CASHBACK ON NEXT 3 VISITS',
            ['#DC2626', '#991B1B', '#F59E0B'],
            1080,
            1080,
            1
        );

        $this->attachMediaToTask(
            $task29,
            'veera_loyalty_tiers_infographic.jpg',
            'Loyalty Tiers & Benefits Infographic',
            'MEMBER PRIVILEGES',
            'SILVER - GOLD - SHAHI ROYAL',
            'Scan WhatsApp QR Code to Unlock Free Biryani',
            ['#B91C1C', '#7F1D1D', '#FCD34D'],
            1080,
            1080,
            2
        );

        // Add Comments for Task 29
        $this->addComments($task29, [
            [
                'user_id' => $priya->id,
                'body' => 'I have crafted the 1080x1080 creative with rich crimson tones and authentic tandoori imagery. Also generated the member privileges infographic slide.',
                'created_at' => $task29SubmittedAt->copy()->subMinutes(25),
            ],
            [
                'user_id' => $strategist->id,
                'body' => 'Looks fantastic Priya! The offer badge stands out clearly and captions match the brand voice. Sending to Admin for final approval.',
                'created_at' => $task29SubmittedAt->copy()->subMinutes(10),
            ],
        ]);

        // Add Audit Logs for Task 29
        $this->addAuditLogs($task29, [
            ['user_id' => $strategist->id, 'action' => 'Task created', 'time' => $task29CreatedAt],
            ['user_id' => $priya->id, 'action' => 'Auto-started task', 'time' => $task29StartedAt],
            ['user_id' => $priya->id, 'action' => 'Uploaded design media (2 files)', 'time' => $task29SubmittedAt->copy()->subMinutes(20)],
            ['user_id' => $priya->id, 'action' => 'Submitted task for review', 'time' => $task29SubmittedAt],
        ]);

        // =========================================================================
        // 2. CREATE 5 COMPLETE FAKE TASKS (CREATION TO SUBMISSION WITH MEDIA)
        // =========================================================================

        $fakeTasksData = [
            // FAKE TASK 1: FreshEats
            [
                'title' => 'Fresh Organic Quinoa & Avocado Super Bowl Promo',
                'client' => $fresh,
                'creator' => $strategist,
                'assignee' => $raj,
                'type' => 'post',
                'priority' => 'high',
                'status' => 'review',
                'platform' => ['instagram', 'facebook'],
                'brief' => 'Create clean, aesthetic Instagram feed graphic highlighting our new Organic Quinoa & Avocado Super Bowl. Emphasize farm-to-table freshness, 18g clean plant protein, and an introductory 15% discount code BOWL15.',
                'caption' => "🥗 Fuel your day the clean way with our NEW Avocado & Quinoa Super Bowl! 🌱🥑\n\nPacked with 18g plant-based protein, crisp hydroponic greens, cherry tomatoes, and tangy lime-cilantro dressing. 100% Organic & Gluten-free.\n\n💥 Order online today and use code BOWL15 for 15% OFF your first bowl! 💚\n\n👉 Tap the link in bio to order on Swiggy / Zomato / FreshEats App.",
                'hashtags' => '#FreshEats #EatClean #OrganicSalad #HealthyLifestyle #VeganBowl #PlantProtein #CleanEating #NutritiousAndDelicious',
                'reference_links' => ['https://instagram.com/p/fresh-bowl-inspo', 'https://fresheats.example.com/menu/avocado-bowl'],
                'created_ago_days' => 4,
                'started_ago_days' => 3,
                'submitted_ago_hours' => 6,
                'media' => [
                    [
                        'file' => 'fresheats_avocado_super_bowl.jpg',
                        'name' => 'Avocado Quinoa Bowl Hero Post (1080x1080)',
                        'header' => 'FRESHEATS KITCHEN',
                        'title' => 'AVOCADO & QUINOA BOWL',
                        'subtitle' => '18g Clean Plant Protein - Code: BOWL15',
                        'colors' => ['#10B981', '#047857', '#34D399'],
                        'w' => 1080,
                        'h' => 1080,
                    ],
                    [
                        'file' => 'fresheats_nutrition_breakdown.jpg',
                        'name' => 'Macro & Nutritional Breakdown Card',
                        'header' => 'NUTRITION FACTS',
                        'title' => '100% ORGANIC & GLUTEN FREE',
                        'subtitle' => 'Zero Preservatives - Fresh Hydroponic Greens',
                        'colors' => ['#059669', '#064E3B', '#A7F3D0'],
                        'w' => 1080,
                        'h' => 1080,
                    ],
                ],
                'comments' => [
                    ['user' => $raj, 'msg' => 'Uploaded both the hero food visual and the nutritional breakdown slide. Green tones color-calibrated to brand palette.'],
                    ['user' => $strategist, 'msg' => 'Looks mouthwatering and fresh! Copy and promotional promo code are accurate. Ready for admin sign-off.'],
                ],
            ],

            // FAKE TASK 2: TechNova
            [
                'title' => 'TechNova AI CloudEngine 3.0 Enterprise Launch',
                'client' => $tech,
                'creator' => $strategist,
                'assignee' => $arjun,
                'type' => 'reel',
                'priority' => 'urgent',
                'status' => 'review',
                'platform' => ['linkedin', 'instagram'],
                'brief' => 'Design teaser reel graphics & motion cover for TechNova CloudEngine 3.0. Highlight enterprise microservices auto-scaling, 99.999% SLA uptime, sub-millisecond Redis sync, and zero-downtime migration.',
                'caption' => "⚡ Meet CloudEngine 3.0 — The Next Generation of Scalable Cloud Infrastructure! ☁️🚀\n\nAccelerate your backend deployments with:\n🔹 10x faster response times & edge routing\n🔹 Enterprise-grade AES-256 encryption & SOC2 compliance\n🔹 Seamless automated Kubernetes cluster auto-scaling\n\n👉 Start your 14-day free trial today! Link in bio. 💻🔥",
                'hashtags' => '#TechNova #CloudEngine #CloudComputing #DevOps #SaaSArchitecture #TechInnovation #AIInfrastructure #Kubernetes #ScaleFast',
                'reference_links' => ['https://technova.io/cloudengine3', 'https://github.com/technova-demos'],
                'created_ago_days' => 3,
                'started_ago_days' => 2,
                'submitted_ago_hours' => 3,
                'media' => [
                    [
                        'file' => 'technova_cloudengine_keynote.jpg',
                        'name' => 'CloudEngine 3.0 Keynote Poster (1080x1920)',
                        'header' => 'TECHNOVA CLOUD',
                        'title' => 'CLOUDENGINE 3.0 LAUNCH',
                        'subtitle' => '99.999% SLA Uptime - 10x Faster Query Engine',
                        'colors' => ['#1E1B4B', '#312E81', '#38BDF8'],
                        'w' => 1080,
                        'h' => 1920,
                    ],
                    [
                        'file' => 'technova_performance_benchmark.jpg',
                        'name' => 'Performance Benchmark Comparison Chart',
                        'header' => 'BENCHMARK STATS',
                        'title' => 'SUB-MILLISECOND LATENCY',
                        'subtitle' => 'Zero Downtime Live DB Migration',
                        'colors' => ['#0F172A', '#1E293B', '#818CF8'],
                        'w' => 1080,
                        'h' => 1080,
                    ],
                ],
                'comments' => [
                    ['user' => $arjun, 'msg' => 'Rendered the reel key visual at 1080x1920 with high-tech obsidian & neon blue styling. Added benchmark slide for carousel cross-posting.'],
                    ['user' => $strategist, 'msg' => 'High tech vibe is executed perfectly. Tech metrics are verified by the engineering team.'],
                ],
            ],

            // FAKE TASK 3: StyleCraft
            [
                'title' => 'StyleCraft Monsoon Pure Linen Editorial Lookbook',
                'client' => $style,
                'creator' => $strategist,
                'assignee' => $neha,
                'type' => 'carousel',
                'priority' => 'normal',
                'status' => 'review',
                'platform' => ['instagram', 'facebook'],
                'brief' => 'Multi-slide lookbook showcasing breathable pastel linen shirts, co-ord sets, and palazzo pants for the monsoon season. Modern minimalist editorial layout with warm earthy tones.',
                'caption' => "Breathe easy in pure comfort. ✨🌧️\n\nDiscover the StyleCraft Monsoon Linen Collection — handcrafted from 100% organic European flax. Light, effortlessly stylish, and tailored for every mood.\n\nSwipe left to explore the curation ➡️\n\n🛍️ Tap the shop link in bio to explore the full collection with complimentary pan-India shipping.",
                'hashtags' => '#StyleCraft #LinenFashion #SustainableStyle #MonsoonLooks #MinimalistAesthetic #OOTDIndia #SlowFashion #HandcraftedLuxury',
                'reference_links' => ['https://stylecraft.example.com/collections/monsoon-linen', 'https://vogue.in/fashion/linen-trends'],
                'created_ago_days' => 5,
                'started_ago_days' => 4,
                'submitted_ago_hours' => 5,
                'media' => [
                    [
                        'file' => 'stylecraft_linen_slide_1.jpg',
                        'name' => 'Lookbook Cover: Organic Flax Co-ord (1080x1080)',
                        'header' => 'STYLECRAFT STUDIO',
                        'title' => 'MONSOON LINEN 2026',
                        'subtitle' => '100% Organic European Flax - Handcrafted',
                        'colors' => ['#8B5CF6', '#6D28D9', '#DDD6FE'],
                        'w' => 1080,
                        'h' => 1080,
                    ],
                    [
                        'file' => 'stylecraft_linen_slide_2.jpg',
                        'name' => 'Lookbook Slide 2: Pastel Lavender Tunic',
                        'header' => 'EFFORTLESS ELEGANCE',
                        'title' => 'BREATHABLE PASTELS',
                        'subtitle' => 'Featherlight Weave - Earthy Aesthetics',
                        'colors' => ['#7C3AED', '#5B21B6', '#C4B5FD'],
                        'w' => 1080,
                        'h' => 1080,
                    ],
                    [
                        'file' => 'stylecraft_linen_slide_3.jpg',
                        'name' => 'Lookbook Slide 3: Sustainability Note',
                        'header' => 'SUSTAINABILITY FIRST',
                        'title' => 'ETHICALLY SOURCED',
                        'subtitle' => 'Zero Plastic Packaging - Fair Trade Certified',
                        'colors' => ['#6D28D9', '#4C1D95', '#EDE9FE'],
                        'w' => 1080,
                        'h' => 1080,
                    ],
                ],
                'comments' => [
                    ['user' => $neha, 'msg' => 'Completed all 3 lookbook slides with consistent editorial typography and soft muted lilac backgrounds.'],
                    ['user' => $strategist, 'msg' => 'Beautiful aesthetics Neha! Perfectly targets our urban luxury audience.'],
                ],
            ],

            // FAKE TASK 4: AutoDrive
            [
                'title' => 'AutoDrive APEX EV SUV VIP Weekend Test Drive',
                'client' => $auto,
                'creator' => $strategist,
                'assignee' => $raj,
                'type' => 'post',
                'priority' => 'high',
                'status' => 'pending_approval',
                'platform' => ['facebook', 'linkedin'],
                'brief' => 'High-impact promotional banner announcing weekend VIP test drives for the new Apex EV SUV. Emphasize 520km certified range on single charge, 0-100 in 4.2s, and zero down-payment launch offers.',
                'caption' => "⚡ The Future of Driving Has Arrived.\n\nExperience the all-new AutoDrive APEX EV this Saturday & Sunday at your nearest flagship experience center!\n\n🔋 520 km Certified Range on Single Charge\n⚡ 0-100 km/h in 4.2 Seconds (Dual Motor AWD)\n🛡️ 5-Star Bharat NCAP Safety Rating\n🎁 Complimentary Home Fast Charger on Weekend Bookings\n\n👉 Limited VIP slots available — Book your test drive now via link in bio!",
                'hashtags' => '#AutoDrive #ApexEV #ElectricVehicles #FutureOfMobility #EVIndia #DriveElectric #SustainableDriving #ZeroEmissions',
                'reference_links' => ['https://autodrive.example.com/apex-ev', 'https://youtube.com/watch?v=autodrive-ev-review'],
                'created_ago_days' => 2,
                'started_ago_days' => 1,
                'submitted_ago_hours' => 2,
                'media' => [
                    [
                        'file' => 'autodrive_apex_ev_showcase.jpg',
                        'name' => 'Apex EV VIP Test Drive Banner (1200x630)',
                        'header' => 'AUTODRIVE MOTORS',
                        'title' => 'APEX EV - VIP TEST DRIVE',
                        'subtitle' => '520km Range - 0-100 in 4.2s - Free Fast Charger',
                        'colors' => ['#1E3A8A', '#172554', '#60A5FA'],
                        'w' => 1200,
                        'h' => 630,
                    ],
                    [
                        'file' => 'autodrive_specs_sheet.jpg',
                        'name' => 'Apex EV Technical Specifications Graphic',
                        'header' => 'PERFORMANCE HIGHLIGHTS',
                        'title' => 'DUAL MOTOR AWD PERFORMANCE',
                        'subtitle' => 'Ultra Fast 150kW DC Fast Charging Supported',
                        'colors' => ['#1E293B', '#0F172A', '#38BDF8'],
                        'w' => 1080,
                        'h' => 1080,
                    ],
                ],
                'comments' => [
                    ['user' => $raj, 'msg' => 'Designed the VIP Test Drive banner with high contrast electric cyan accents and bold automotive specs layout.'],
                    ['user' => $strategist, 'msg' => 'Looks commanding and premium. All pricing and booking links verified with AutoDrive marketing head.'],
                ],
            ],

            // FAKE TASK 5: ABC Corp
            [
                'title' => 'ABC Royal Alphonso Mango Pulp Festival Stories',
                'client' => $abc,
                'creator' => $strategist,
                'assignee' => $priya,
                'type' => 'story',
                'priority' => 'urgent',
                'status' => 'review',
                'platform' => ['instagram', 'whatsapp'],
                'brief' => 'Instagram story series promoting 100% Ratnagiri Alphonso Mango pulp real juice drink. Eye-catching summer vibes, interactive poll sticker mockup, and swipe-up delivery link.',
                'caption' => "🥭 Squeeze the sunshine with 100% Real Alphonso Mango Pulp! ☀️\n\nNo added sugar, zero artificial preservatives — pure orchard goodness in every single sip.\n\n🥤 Grab your summer bundle now on Blinkit, Zepto, and Instamart with flat 20% discount!\n\n👉 Swipe up to order right now!",
                'hashtags' => '#ABCFoods #RealMangoJuice #AlphonsoLove #SummerRefreshment #MangoFestival #RatnagiriAlphonso #PureJuice',
                'reference_links' => ['https://abccorp.example.com/products/mango-nectar', 'https://instagram.com/abcfoods'],
                'created_ago_days' => 2,
                'started_ago_days' => 1,
                'submitted_ago_hours' => 1,
                'media' => [
                    [
                        'file' => 'abc_mango_story_frame1.jpg',
                        'name' => 'Alphonso Mango Pulp Story Frame 1 (1080x1920)',
                        'header' => 'ABC FOODS FESTIVAL',
                        'title' => '100% RATNAGIRI ALPHONSO',
                        'subtitle' => 'Pure Orchard Goodness - No Added Sugar',
                        'colors' => ['#EA580C', '#C2410C', '#FDE047'],
                        'w' => 1080,
                        'h' => 1920,
                    ],
                    [
                        'file' => 'abc_mango_story_frame2.jpg',
                        'name' => 'Alphonso Mango Pulp Story Frame 2 (Interactive Poll)',
                        'header' => 'SUMMER REFRESHMENT',
                        'title' => 'GRAB YOUR 20% OFF PACK',
                        'subtitle' => 'Available on Blinkit, Zepto & Instamart',
                        'colors' => ['#D97706', '#92400E', '#FEF08A'],
                        'w' => 1080,
                        'h' => 1920,
                    ],
                ],
                'comments' => [
                    ['user' => $priya, 'msg' => 'Story frames exported at 1080x1920 with safe zones reserved for Instagram interactive poll widgets and swipe-up stickers.'],
                    ['user' => $strategist, 'msg' => 'Vibrant summer energy! Delivery partner logos and discount tags are clear.'],
                ],
            ],
        ];

        foreach ($fakeTasksData as $tData) {
            $client = $tData['client'];
            $creator = $tData['creator'];
            $assignee = $tData['assignee'];
            $socialLinks = $client->socialMediaLinks()->pluck('id')->toArray();

            $createdAt = Carbon::now()->subDays($tData['created_ago_days'])->setTime(10, 0);
            $startedAt = Carbon::now()->subDays($tData['started_ago_days'])->setTime(11, 30);
            $submittedAt = Carbon::now()->subHours($tData['submitted_ago_hours']);

            $task = Task::updateOrCreate(
                [
                    'title' => $tData['title'],
                    'client_id' => $client->id,
                ],
                [
                    'client_id' => $client->id,
                    'created_by' => $creator->id,
                    'assigned_to' => $assignee->id,
                    'type' => $tData['type'],
                    'priority' => $tData['priority'],
                    'status' => $tData['status'],
                    'platform' => $tData['platform'],
                    'client_social_media_link_id' => $socialLinks[0] ?? null,
                    'selected_social_media_link_ids' => $socialLinks,
                    'brief' => $tData['brief'],
                    'caption' => $tData['caption'],
                    'hashtags' => $tData['hashtags'],
                    'reference_links' => $tData['reference_links'],
                    'deadline' => Carbon::now()->addDays(6)->toDateString(),
                    'design_deadline' => Carbon::now()->addDays(2)->toDateString(),
                    'post_date' => Carbon::now()->addDays(4)->toDateString(),
                    'is_urgent_task' => ($tData['priority'] === 'urgent'),
                    'created_at' => $createdAt,
                    'started_at' => $startedAt,
                    'submitted_at' => $submittedAt,
                ]
            );

            // Clean up any old media for this task if re-seeding
            foreach ($task->media as $oldM) {
                $oldM->delete();
            }

            // Create and attach media
            foreach ($tData['media'] as $idx => $mInfo) {
                $this->attachMediaToTask(
                    $task,
                    $mInfo['file'],
                    $mInfo['name'],
                    $mInfo['header'],
                    $mInfo['title'],
                    $mInfo['subtitle'],
                    $mInfo['colors'],
                    $mInfo['w'],
                    $mInfo['h'],
                    $idx + 1
                );
            }

            // Add comments
            $formattedComments = [];
            foreach ($tData['comments'] as $cIdx => $c) {
                $formattedComments[] = [
                    'user_id' => $c['user']->id,
                    'body' => $c['msg'],
                    'created_at' => $submittedAt->copy()->subMinutes(15 - ($cIdx * 10)),
                ];
            }
            $this->addComments($task, $formattedComments);

            // Add audit logs
            $this->addAuditLogs($task, [
                ['user_id' => $creator->id, 'action' => 'Task created & assigned to ' . $assignee->name, 'time' => $createdAt],
                ['user_id' => $assignee->id, 'action' => 'Auto-started task', 'time' => $startedAt],
                ['user_id' => $assignee->id, 'action' => 'Uploaded ' . count($tData['media']) . ' design asset(s)', 'time' => $submittedAt->copy()->subMinutes(20)],
                ['user_id' => $assignee->id, 'action' => 'Submitted task for review', 'time' => $submittedAt],
            ]);

            // Add notification for admin
            Notification::create([
                'user_id' => $admin->id,
                'task_id' => $task->id,
                'icon' => '🎨',
                'title' => 'Task Ready for Approval',
                'subtitle' => '"' . $task->title . '" for ' . $client->name . ' submitted by ' . $assignee->name,
                'link' => '/admin/approvals/' . $task->id,
                'read_at' => null,
                'created_at' => $submittedAt,
            ]);
        }
    }

    /**
     * Generate visual image and attach as Media model
     */
    private function attachMediaToTask(
        Task $task,
        string $filename,
        string $displayName,
        string $header,
        string $title,
        string $subtitle,
        array $palette,
        int $width = 1080,
        int $height = 1080,
        int $order = 1
    ): Media {
        $storageDir = storage_path('app/public/task-media');
        $publicDir = public_path('storage/task-media');
        $storageFilePath = $storageDir . '/' . $filename;
        $publicFilePath = $publicDir . '/' . $filename;

        // Generate dynamic PNG/JPEG image using GD
        $this->generateGraphicArtwork($storageFilePath, $width, $height, $header, $title, $subtitle, $palette);

        // Copy to public storage as well for universal availability
        File::copy($storageFilePath, $publicFilePath);

        $fileSize = filesize($storageFilePath);

        return Media::create([
            'model_type' => 'App\\Models\\Task',
            'model_id' => $task->id,
            'collection_name' => 'task-media',
            'name' => $displayName,
            'file_name' => $filename,
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'path' => 'task-media/' . $filename,
            'size' => $fileSize ?: 145000,
            'type' => 'image',
            'metadata' => [
                'width' => $width,
                'height' => $height,
                'resolution' => '300dpi',
                'color_space' => 'RGB',
            ],
            'order_column' => $order,
        ]);
    }

    /**
     * Draw stylish high-resolution banner using PHP GD
     */
    private function generateGraphicArtwork(
        string $targetPath,
        int $width,
        int $height,
        string $header,
        string $title,
        string $subtitle,
        array $palette
    ): void {
        $im = imagecreatetruecolor($width, $height);
        imagealphablending($im, true);
        imagesavealpha($im, true);

        // Parse color palette
        $c1 = $this->hexToRgb($palette[0] ?? '#1E293B');
        $c2 = $this->hexToRgb($palette[1] ?? '#0F172A');
        $accent = $this->hexToRgb($palette[2] ?? '#38BDF8');

        // Draw vertical gradient background
        for ($y = 0; $y < $height; $y++) {
            $t = $y / max(1, $height - 1);
            $r = (int) ($c1[0] * (1 - $t) + $c2[0] * $t);
            $g = (int) ($c1[1] * (1 - $t) + $c2[1] * $t);
            $b = (int) ($c1[2] * (1 - $t) + $c2[2] * $t);
            $color = imagecolorallocate($im, $r, $g, $b);
            imageline($im, 0, $y, $width, $y, $color);
        }

        // Draw decorative abstract shapes
        $accentColor = imagecolorallocatealpha($im, $accent[0], $accent[1], $accent[2], 90);
        $accentBright = imagecolorallocate($im, $accent[0], $accent[1], $accent[2]);
        $white = imagecolorallocate($im, 255, 255, 255);
        $whiteFaded = imagecolorallocatealpha($im, 255, 255, 255, 30);
        $blackCard = imagecolorallocatealpha($im, 0, 0, 0, 50);

        // Geometric background circles
        imagefilledellipse($im, (int) ($width * 0.9), (int) ($height * 0.15), (int) ($width * 0.6), (int) ($width * 0.6), $accentColor);
        imagefilledellipse($im, (int) ($width * 0.1), (int) ($height * 0.85), (int) ($width * 0.5), (int) ($width * 0.5), $accentColor);

        // Card container
        $margin = (int) ($width * 0.08);
        $cardTop = (int) ($height * 0.12);
        $cardBottom = (int) ($height * 0.88);
        imagefilledrectangle($im, $margin, $cardTop, $width - $margin, $cardBottom, $blackCard);
        imagerectangle($im, $margin, $cardTop, $width - $margin, $cardBottom, $accentBright);

        // Top Header Badge
        $badgeTop = $cardTop + (int) ($height * 0.06);
        $badgeBottom = $badgeTop + 50;
        imagefilledrectangle($im, $margin + 40, $badgeTop, $width - $margin - 40, $badgeBottom, $accentBright);
        $darkText = imagecolorallocate($im, 15, 23, 42);
        imagestring($im, 5, $margin + 60, $badgeTop + 16, strtoupper($header), $darkText);

        // Main Title Banner
        $titleY = (int) ($height * 0.38);
        imagestring($im, 5, $margin + 50, $titleY, '----------------------------------------', $accentBright);
        imagestring($im, 5, $margin + 50, $titleY + 30, $title, $white);
        imagestring($im, 5, $margin + 50, $titleY + 65, '----------------------------------------', $accentBright);

        // Subtitle & Highlights
        $subY = (int) ($height * 0.56);
        imagestring($im, 5, $margin + 50, $subY, '>> ' . $subtitle, $white);

        // Bottom CTA Bar
        $ctaY = $cardBottom - (int) ($height * 0.10);
        imagefilledrectangle($im, $margin + 50, $ctaY, $width - $margin - 50, $ctaY + 60, $whiteFaded);
        imagestring($im, 5, $margin + 70, $ctaY + 22, 'OFFICIAL CAMPAIGN ASSET - READY FOR PUBLISHING', $white);

        // Output JPEG
        imagejpeg($im, $targetPath, 92);
        imagedestroy($im);
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function ensureSocialLink(Client $client, string $platform, string $url, ?string $label = null, bool $isPrimary = false): ClientSocialMediaLink
    {
        return ClientSocialMediaLink::updateOrCreate(
            [
                'client_id' => $client->id,
                'platform' => $platform,
            ],
            [
                'url' => $url,
                'label' => $label,
                'is_primary' => $isPrimary,
            ]
        );
    }

    private function addComments(Task $task, array $comments): void
    {
        // Remove existing comments if any
        Comment::where('task_id', $task->id)->delete();

        foreach ($comments as $c) {
            Comment::create([
                'task_id' => $task->id,
                'user_id' => $c['user_id'],
                'body' => $c['body'],
                'created_at' => $c['created_at'] ?? now(),
                'updated_at' => $c['created_at'] ?? now(),
            ]);
        }
    }

    private function addAuditLogs(Task $task, array $logs): void
    {
        foreach ($logs as $l) {
            AuditLog::create([
                'task_id' => $task->id,
                'user_id' => $l['user_id'],
                'action' => $l['action'],
                'ip_address' => '127.0.0.1',
                'created_at' => $l['time'] ?? now(),
                'updated_at' => $l['time'] ?? now(),
            ]);
        }
    }
}
