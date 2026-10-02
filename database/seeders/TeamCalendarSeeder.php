<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TeamCalendarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed 10 calendar records for each client across the team calendar.
     */
    public function run(): void
    {
        // Fetch or fallback team users
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $strategists = User::where('role', 'strategist')->get();
        if ($strategists->isEmpty()) {
            $strategists = collect([$admin]);
        }

        $designers = User::where('role', 'designer')->get();
        if ($designers->isEmpty()) {
            $designers = collect([$admin]);
        }

        $developers = User::where('role', 'developer')->get();
        if ($developers->isEmpty()) {
            $developers = $designers;
        }

        $teamMembers = $designers->concat($developers)->values();

        $clients = Client::all();

        // 10 distinct task specifications per client profile
        $templates = [
            // Client: ABC Corp (FMCG / Food)
            'ABC Corp' => [
                [
                    'title' => 'Cold-Pressed Juice Launch Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'tiktok'],
                    'priority' => 'urgent',
                    'status' => 'completed',
                    'day_offset' => -1, // Oct 01
                    'caption' => 'Pure goodness in every drop! Introducing ABC 100% Raw Cold-Pressed Juices. No added sugar, no preservatives. 🍊✨ #ABCFresh #ColdPressedJuice',
                    'brief' => 'Dynamic 20s vertical reel featuring quick bottle transitions, fresh splash sound design, and text overlay highlights.',
                    'hashtags' => '#healthyhabits #coldpressed #organicjuice #nutrition',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Healthy Breakfast Bowl Carousel',
                    'type' => 'carousel',
                    'platform' => ['instagram', 'facebook'],
                    'priority' => 'high',
                    'status' => 'completed',
                    'day_offset' => 0, // Oct 02
                    'caption' => '5 easy breakfast bowl recipes with ABC Super Oats to fuel your mornings. Swipe to see all delicious ideas 👉',
                    'brief' => 'Clean 6-slide carousel with macro food photography and minimalist step-by-step recipe typography.',
                    'hashtags' => '#recipeideas #breakfastbowl #superfoods #morningroutine',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Weekend Supermarket Discount Post',
                    'type' => 'post',
                    'platform' => ['facebook', 'instagram'],
                    'priority' => 'normal',
                    'status' => 'review',
                    'day_offset' => 2, // Oct 04
                    'caption' => 'Stock up for the week! Buy 2 Get 1 Free on all ABC cereals this weekend at leading retail stores.',
                    'brief' => 'Retail promotional banner with vibrant brand red & gold badges, clear price strikethroughs and store badges.',
                    'hashtags' => '#weekenddeals #groceryshopping #familybreakfast #specialoffer',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Farm-to-Pack Sourcing Story Series',
                    'type' => 'story',
                    'platform' => ['instagram'],
                    'priority' => 'normal',
                    'status' => 'inprogress',
                    'day_offset' => 4, // Oct 06
                    'caption' => 'Ever wondered where our fruit comes from? Meet our partner orchard growers in Himachal Pradesh.',
                    'brief' => 'Set of 4 interactive Instagram stories with poll stickers and swipe-up farm documentary clips.',
                    'hashtags' => '#sustainablefarming #locallygrown #ethicalfood #storytelling',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Customer Loyalty Portal Bugfixes',
                    'type' => 'website',
                    'platform' => ['linkedin'],
                    'priority' => 'high',
                    'status' => 'inprogress',
                    'day_offset' => 7, // Oct 09
                    'caption' => 'Upgrades to ABC Club loyalty points sync and mobile checkout speed optimization.',
                    'brief' => 'Resolve QR code scanning session timeouts on iOS WebKit and optimize SQL queries on redemption endpoints.',
                    'hashtags' => '#devsprint #performancetuning #ecommercesolutions',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Immunity Booster Product Spotlight',
                    'type' => 'post',
                    'platform' => ['instagram', 'linkedin'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 11, // Oct 13
                    'caption' => 'Packed with Vitamin C & Zinc. Discover why over 100,000 families trust ABC Immunity Daily.',
                    'brief' => 'Infographic style social banner showcasing clinical ingredients and key wellness stats.',
                    'hashtags' => '#immunityboost #healthyliving #wellnessfacts #abcnutrition',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Diwali Festive Gifting Hamper Video',
                    'type' => 'video',
                    'platform' => ['youtube', 'facebook'],
                    'priority' => 'urgent',
                    'status' => 'todo',
                    'day_offset' => 15, // Oct 17
                    'caption' => 'Celebrate the festival of lights with royal indulgence. Pre-order our limited edition luxury gourmet box.',
                    'brief' => 'Warm cinematic 45s product film showing golden packaging opening with traditional festive instrumental score.',
                    'hashtags' => '#diwaligifting #festiveseason #luxuryhampers #celebrateinstyle',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Interactive Nutrition Calculator Web App',
                    'type' => 'software',
                    'platform' => ['linkedin'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 19, // Oct 21
                    'caption' => 'Calculate your daily protein and fiber target with ABC smart health widget.',
                    'brief' => 'Build reactive single-page widget in Vue/React with instant BMI and daily macro breakdown export.',
                    'hashtags' => '#healthtech #interactiveweb #macrocalculator',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Zero Plastic Packaging Milestone Post',
                    'type' => 'post',
                    'platform' => ['linkedin', 'twitter'],
                    'priority' => 'high',
                    'status' => 'todo',
                    'day_offset' => 23, // Oct 25
                    'caption' => 'Proud to announce that 95% of our beverage packaging is now fully compostable and ocean-safe. 🌿',
                    'brief' => 'Corporate sustainability milestone graphic with certified badges and impactful green typography.',
                    'hashtags' => '#sustainability #zerowaste #ecofriendly #corporateimpact',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Halloween Spooky Shake Recipe Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'tiktok'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 28, // Oct 30
                    'caption' => 'Spooky, sweet, and totally delicious! Try our Midnight Berry Monster Shake this Halloween weekend. 🎃👻',
                    'brief' => 'Fun themed transitions, spooky sound FX, dripping purple berry puree visual accents.',
                    'hashtags' => '#halloweenrecipes #festivedrinks #spookyseason #kidfriendly',
                    'team_type' => 'designer',
                ],
            ],

            // Client: TechNova (Technology / SaaS)
            'TechNova' => [
                [
                    'title' => 'Q3 Platform Release Highlights Video',
                    'type' => 'video',
                    'platform' => ['youtube', 'linkedin'],
                    'priority' => 'high',
                    'status' => 'completed',
                    'day_offset' => -1, // Oct 01
                    'caption' => 'Faster workflows, AI automations, and enhanced SOC2 compliance. Tour everything new in TechNova 3.4.',
                    'brief' => 'Screen recordings animated inside modern device mockups with smooth camera pans and subtitle overlays.',
                    'hashtags' => '#saasrelease #productupdate #cloudtech #enterpriseai',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Cloud Migration Security Checklist Carousel',
                    'type' => 'carousel',
                    'platform' => ['linkedin'],
                    'priority' => 'normal',
                    'status' => 'completed',
                    'day_offset' => 1, // Oct 03
                    'caption' => 'Planning a multi-cloud transition in 2026? Don’t overlook these 7 critical security checkpoints.',
                    'brief' => 'High-contrast dark-mode carousel (navy & neon cyan) with crisp security architecture diagrams.',
                    'hashtags' => '#cybersecurity #cloudmigration #devops #infosec',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'AI Copilot Integration REST API Sprint',
                    'type' => 'software',
                    'platform' => ['twitter'],
                    'priority' => 'urgent',
                    'status' => 'inprogress',
                    'day_offset' => 3, // Oct 05
                    'caption' => 'Deploying streaming LLM tokens via WebSockets for real-time document analysis in TechNova workspace.',
                    'brief' => 'Develop middleware adapter for Ollama/Claude APIs with Redis rate limiting and token analytics.',
                    'hashtags' => '#backendengineering #langchain #aiengine #laravel',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Customer Case Study: FinTech Scale Post',
                    'type' => 'post',
                    'platform' => ['linkedin', 'twitter'],
                    'priority' => 'high',
                    'status' => 'review',
                    'day_offset' => 6, // Oct 08
                    'caption' => 'How NeoBank cut incident response latency by 68% using TechNova Automated Observability.',
                    'brief' => 'Stat-callout graphic with customer headshot, company logo, and large percentage metric badges.',
                    'hashtags' => '#fintech #casestudy #customerwin #cloudobservability',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Tech Summit Virtual Booth Landing Page',
                    'type' => 'website',
                    'platform' => ['linkedin'],
                    'priority' => 'normal',
                    'status' => 'inprogress',
                    'day_offset' => 9, // Oct 11
                    'caption' => 'Interactive landing page for Global Cloud Summit 2026 attendees with calendar booking integration.',
                    'brief' => 'Build high-converting responsive page with Calendly embed, dynamic agenda filters and Tailwind styling.',
                    'hashtags' => '#webdev #landingpage #eventmarketing #conversionrate',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Developer Documentation Redesign Banner',
                    'type' => 'banner',
                    'platform' => ['twitter', 'github'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 13, // Oct 15
                    'caption' => 'Revamped API docs with interactive code playgrounds and Postman collection downloads.',
                    'brief' => 'Modern glassmorphic illustration with code snippets, syntax highlighting, and API endpoints.',
                    'hashtags' => '#apidocs #developerfirst #devtools #softwareengineering',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Behind The Code: Engineering Culture Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'linkedin'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 17, // Oct 19
                    'caption' => 'What does a day in the life of a TechNova distributed core engineer look like? Take a peek!',
                    'brief' => 'Fast-paced lo-fi montage of desk setups, mechanical keyboards, coffee rituals, and sprint planning laughs.',
                    'hashtags' => '#companyculture #remotework #developerlife #techcareers',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'GraphQL vs REST API Comparison Post',
                    'type' => 'post',
                    'platform' => ['linkedin', 'twitter'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 20, // Oct 22
                    'caption' => 'Should your microservices migrate to GraphQL in 2027? Here is our performance audit benchmark.',
                    'brief' => 'Side-by-side technical infographic comparing payload sizes, roundtrip latency, and developer ergonomics.',
                    'hashtags' => '#graphql #restapi #softwarearchitecture #webperformance',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Enterprise SLA Guarantee Whitepaper Flyer',
                    'type' => 'flyer',
                    'platform' => ['linkedin'],
                    'priority' => 'high',
                    'status' => 'todo',
                    'day_offset' => 24, // Oct 26
                    'caption' => 'Download our comprehensive 99.999% uptime guarantee architecture and disaster recovery breakdown.',
                    'brief' => 'Two-page PDF flyer with executive typography, compliance seals, and data sovereignty guarantees.',
                    'hashtags' => '#enterprisegrowth #highavailability #sla #cloudsecurity',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Global Hackathon Announcement Story',
                    'type' => 'story',
                    'platform' => ['instagram', 'twitter'],
                    'priority' => 'urgent',
                    'status' => 'todo',
                    'day_offset' => 27, // Oct 29
                    'caption' => '$50,000 in bounties. Build the next generation of generative AI agents with TechNova SDK. Register now!',
                    'brief' => 'Cyberpunk visual aesthetic with glitch title animations and countdown sticker integration.',
                    'hashtags' => '#hackathon2026 #coderbounty #aiagents #buildinpublic',
                    'team_type' => 'designer',
                ],
            ],

            // Client: FreshEats (Food & Beverage)
            'FreshEats' => [
                [
                    'title' => 'Autumn Harvest Salad Promo Post',
                    'type' => 'post',
                    'platform' => ['instagram', 'facebook'],
                    'priority' => 'normal',
                    'status' => 'completed',
                    'day_offset' => -1, // Oct 01
                    'caption' => 'Crisp roasted butternut squash, candied pecans, and goat cheese on a bed of fresh baby kale. 🥗🍁',
                    'brief' => 'Warm, appetizing overhead flat-lay photo with clean text banner and limited-time season badge.',
                    'hashtags' => '#eatclean #freshsalads #autumnharvest #organicfood',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Chef Secret Vinaigrette Making Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'tiktok'],
                    'priority' => 'high',
                    'status' => 'completed',
                    'day_offset' => 1, // Oct 03
                    'caption' => 'The secret to our house Dijon maple dressing in under 30 seconds! Save this for dinner tonight.',
                    'brief' => 'Close-up culinary ASMR style whisking and drizzle shots with recipe text cues synced to upbeat lo-fi beat.',
                    'hashtags' => '#foodasmr #saladrecipe #homechef #easyrecipes',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Weekly Meal Subscription Plan Carousel',
                    'type' => 'carousel',
                    'platform' => ['instagram', 'facebook'],
                    'priority' => 'normal',
                    'status' => 'inprogress',
                    'day_offset' => 3, // Oct 05
                    'caption' => 'Healthy eating on autopilot. Discover our 5-day chef prepped box delivered straight to your door.',
                    'brief' => 'Card-style carousel featuring Monday through Friday lunch options with calorie and macro badges.',
                    'hashtags' => '#mealprep #healthyeating #fitnessnutrition #deliveryfood',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Meal Subscription Customizer Mobile Web Page',
                    'type' => 'website',
                    'platform' => ['instagram'],
                    'priority' => 'urgent',
                    'status' => 'inprogress',
                    'day_offset' => 6, // Oct 08
                    'caption' => 'Swap meals, pause delivery dates, and adjust dietary filters in your account dashboard.',
                    'brief' => 'Develop drag-and-drop meal swapper UI with instant dietary calculation and seamless mobile touch support.',
                    'hashtags' => '#webdeveloper #frontenddev #uxdesign #foodtech',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Customer Transformation Story Video',
                    'type' => 'video',
                    'platform' => ['youtube', 'facebook'],
                    'priority' => 'normal',
                    'status' => 'review',
                    'day_offset' => 9, // Oct 11
                    'caption' => 'Meet Sarah: How 90 days of balanced meals helped her reclaim sustained energy without cooking stress.',
                    'brief' => 'Heartwarming interview style documentary with kitchen b-roll and weekly meal plan charts.',
                    'hashtags' => '#customerstory #nutritionjourney #healthyweight #lifetips',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Hydration & Daily Green Juice Stories',
                    'type' => 'story',
                    'platform' => ['instagram'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 12, // Oct 14
                    'caption' => 'Mid-week slump? Boost your afternoon clarity with cold-pressed celery and ginger juice.',
                    'brief' => 'Bright morning aesthetic stories with tap-to-order sticker and interactive water tracker.',
                    'hashtags' => '#hydrationchallenge #greenjuice #wellnessroutine',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Nutritional Myths Debunked Infographic Post',
                    'type' => 'post',
                    'platform' => ['instagram', 'linkedin'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 16, // Oct 18
                    'caption' => 'Are carbs the enemy? Our certified clinical nutritionist breaks down the science of complex carbs.',
                    'brief' => 'Clean dual-column comparison infographic with modern pastel tones and clear takeaway cards.',
                    'hashtags' => '#nutritionfacts #dietmyth #healthyliving #evidencebased',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Diwali Guilt-Free Dessert Box Flyer',
                    'type' => 'flyer',
                    'platform' => ['facebook', 'instagram'],
                    'priority' => 'high',
                    'status' => 'todo',
                    'day_offset' => 20, // Oct 22
                    'caption' => 'Indulge without the sugar spike! Sugar-free dates and almond sweets hand-crafted for the festive season.',
                    'brief' => 'Print and digital promo flyer with warm festive lighting and nutritional comparisons to classic sweets.',
                    'hashtags' => '#healthysweets #diwalifestive #refinedsugarfree #artisansweets',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Delivery Tracking SMS Gateway Integration',
                    'type' => 'software',
                    'platform' => ['twitter'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 24, // Oct 26
                    'caption' => 'Automated Twilio SMS order tracking updates with live courier dispatch maps.',
                    'brief' => 'Integrate Twilio Webhooks and background queue workers for delivery driver dispatch notifications.',
                    'hashtags' => '#apiintegration #backend #logistics #laraveldev',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Weekend Farmers Market Pop-Up Story',
                    'type' => 'story',
                    'platform' => ['instagram'],
                    'priority' => 'urgent',
                    'status' => 'todo',
                    'day_offset' => 28, // Oct 30
                    'caption' => 'Visit our live tasting booth this Saturday at the City Green Farmers Market! First 50 guests get free sample kits.',
                    'brief' => 'Location tag sticker, live countdown timer, and map pin graphic with festive market music.',
                    'hashtags' => '#farmersmarket #livebooth #tastingevent #localcommunity',
                    'team_type' => 'designer',
                ],
            ],

            // Client: Mango Brand (Lifestyle / Wellness)
            'Mango Brand' => [
                [
                    'title' => 'Slow Sunday Morning Routine Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'tiktok'],
                    'priority' => 'normal',
                    'status' => 'completed',
                    'day_offset' => -1, // Oct 01
                    'caption' => 'Take it slow today. Scented soy candles, fresh linen, and mindful journaling. ✨ #MangoLifestyle',
                    'brief' => 'Warm cinematic morning light, ambient aesthetic soundtrack, smooth pan across bedside wellness items.',
                    'hashtags' => '#slowliving #mindfulness #homewellness #aestheticreel',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Eco-Tote Bag Drop Carousel',
                    'type' => 'carousel',
                    'platform' => ['instagram', 'pinterest'],
                    'priority' => 'high',
                    'status' => 'completed',
                    'day_offset' => 1, // Oct 03
                    'caption' => 'Heavyweight 100% organic cotton canvas totes in 4 earthy tones. Ready for your daily commute.',
                    'brief' => 'Lookbook style 5-image gallery with urban street backdrop, pocket detail close-ups, and color swatches.',
                    'hashtags' => '#sustainablefashion #totebag #minimaliststyle #capsulewardrobe',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Aromatherapy Diffuser Blend Guide Post',
                    'type' => 'post',
                    'platform' => ['instagram', 'pinterest'],
                    'priority' => 'normal',
                    'status' => 'review',
                    'day_offset' => 4, // Oct 06
                    'caption' => '3 essential oil pairings for deep work and evening relaxation. Save this post for your desk diffuser.',
                    'brief' => 'Minimalist editorial graphic with frosted bottle render and drop ratios in delicate typography.',
                    'hashtags' => '#essentialoils #aromatherapy #workspacezen #calmingroutine',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'E-Commerce Checkout One-Click Pay Web Update',
                    'type' => 'website',
                    'platform' => ['linkedin'],
                    'priority' => 'urgent',
                    'status' => 'inprogress',
                    'day_offset' => 7, // Oct 09
                    'caption' => 'Integrating Apple Pay and Google Pay native sheets for seamless sub-10 second checkout.',
                    'brief' => 'Implement Stripe Payment Element SDK with express checkout buttons on cart drawer and product detail pages.',
                    'hashtags' => '#stripe #applepay #ecommerceoptimization #shopify',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Studio Tour Behind The Scenes Video',
                    'type' => 'video',
                    'platform' => ['youtube', 'instagram'],
                    'priority' => 'normal',
                    'status' => 'inprogress',
                    'day_offset' => 11, // Oct 13
                    'caption' => 'A walk through our design studio in Bangalore: from clay ceramic molds to packaging warehouse.',
                    'brief' => 'Engaging 60s creator walk-and-talk video with voiceover, creative studio highlights, and founder remarks.',
                    'hashtags' => '#behindthescenes #meetthemakers #artisanstudio #craftsmanship',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Hydrating Body Mist Product Launch Post',
                    'type' => 'post',
                    'platform' => ['instagram'],
                    'priority' => 'high',
                    'status' => 'todo',
                    'day_offset' => 14, // Oct 16
                    'caption' => 'Dewy hydration on the go. Formulated with rosewater, hyaluronic acid, and botanical botanicals.',
                    'brief' => 'Hero product image with subtle water droplets, soft blush pink backdrop, and clean ingredient badges.',
                    'hashtags' => '#cleanbeauty #skincareessential #hydratingspray #dewyskin',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Festive Gift Box Packaging Brochure',
                    'type' => 'brochure',
                    'platform' => ['facebook', 'instagram'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 18, // Oct 20
                    'caption' => 'Curated luxury lifestyle gifts for friends, corporate partners, and family celebrations.',
                    'brief' => 'Trifold digital and print catalogue showing gift bundle tiers, custom embossing options and corporate rates.',
                    'hashtags' => '#giftguide #festivecuration #corporategifting #mindfulliving',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Weekend Community Yoga Event Stories',
                    'type' => 'story',
                    'platform' => ['instagram'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 21, // Oct 23
                    'caption' => 'Join us for sunset rooftop yoga & sound bath session. Limited spots available — RSVP in bio.',
                    'brief' => 'Golden hour aesthetic story sequence with link sticker to eventbrite and attendee FAQs.',
                    'hashtags' => '#communityevent #rooftopyoga #soundhealing #bengaluru',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Customer Reviews & UGC Collage Post',
                    'type' => 'post',
                    'platform' => ['instagram', 'facebook'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 25, // Oct 27
                    'caption' => '"The best candle I have ever burned. 10/10." Real words from our incredible community this week.',
                    'brief' => 'Polaroid layout collage showcasing tagged customer photos alongside quote bubbles and gold stars.',
                    'hashtags' => '#customerlove #socialproof #ugccontent #happycustomers',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Halloween Cozy Home Decor Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'tiktok'],
                    'priority' => 'urgent',
                    'status' => 'todo',
                    'day_offset' => 29, // Oct 31
                    'caption' => 'Elevate your autumn space without the tacky plastic. Subtle, chic Halloween home touches. 🍂✨',
                    'brief' => 'Styling sequence showing ceramic pumpkins, amber glass candleholders, and warm knitted throw blankets.',
                    'hashtags' => '#fallhomedecor #cozyvibes #neutralhalloween #autumnspaces',
                    'team_type' => 'designer',
                ],
            ],

            // Client: StyleCraft (Fashion & Apparel)
            'StyleCraft' => [
                [
                    'title' => 'Fall Winter Runway Highlights Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'tiktok'],
                    'priority' => 'urgent',
                    'status' => 'completed',
                    'day_offset' => 0, // Oct 02
                    'caption' => 'Elegance redefined. Unveiling our FW26 collection straight from the runway spotlight. 🖤✨',
                    'brief' => 'High-energy fashion runway cuts with rhythmic bass drops, slow-motion fabric flow, and model walk angles.',
                    'hashtags' => '#fw26 #runwayfashion #highfashion #couturestyle',
                    'team_type' => 'designer',
                ],
                [
                    'title' => '5 Ways To Style An Oversized Blazer Carousel',
                    'type' => 'carousel',
                    'platform' => ['instagram', 'pinterest'],
                    'priority' => 'high',
                    'status' => 'completed',
                    'day_offset' => 2, // Oct 04
                    'caption' => 'From 9 AM boardroom meetings to 9 PM rooftop cocktails. Swipe for full outfit formulas. 👉',
                    'brief' => 'Streetwear editorial carousel with clean background cutouts, product tags, and accessories breakdown.',
                    'hashtags' => '#styleinspo #fashiontips #blazerstyle #howtostyle',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Virtual Fitting Room AR Filter Web App',
                    'type' => 'software',
                    'platform' => ['linkedin'],
                    'priority' => 'urgent',
                    'status' => 'inprogress',
                    'day_offset' => 5, // Oct 07
                    'caption' => 'Try on shades, coats, and scarves in augmented reality straight inside the mobile browser.',
                    'brief' => 'Optimize WebGL face and body tracking shaders for smooth 60fps performance on Safari and Chrome.',
                    'hashtags' => '#fashiontech #artech #webgl #experientialcommerce',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Sustainable Silk Sourcing Story Series',
                    'type' => 'story',
                    'platform' => ['instagram'],
                    'priority' => 'normal',
                    'status' => 'inprogress',
                    'day_offset' => 8, // Oct 10
                    'caption' => 'Behind every seam: Meet our master weavers preserving heritage silk techniques.',
                    'brief' => 'Editorial story frames with delicate serif fonts, close-up thread weaves, and question box for styling tips.',
                    'hashtags' => '#heritagefashion #sustainableluxury #artisanal #slowfashion',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Pre-Festive Flash Sale Banner',
                    'type' => 'banner',
                    'platform' => ['facebook', 'instagram'],
                    'priority' => 'high',
                    'status' => 'review',
                    'day_offset' => 11, // Oct 13
                    'caption' => 'VIP Access Only: Enjoy 30% off our signature festive eveningwear for the next 48 hours.',
                    'brief' => 'Deep emerald green and champagne gold palette with luxury foil textures and bold typographic lockup.',
                    'hashtags' => '#exclusiveaccess #vipsale #festivelook #designerwear',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Monochrome Minimalism Fashion Post',
                    'type' => 'post',
                    'platform' => ['instagram'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 15, // Oct 17
                    'caption' => 'Simplicity is the keynote of all true elegance. Discover the new ivory tailored co-ord set.',
                    'brief' => 'Editorial magazine cover style social post with generous negative space and refined typography.',
                    'hashtags' => '#minimalchic #monochromeoutfit #editorialfashion #outfitsoftheday',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Behind The Seams: Tailoring Atelier Video',
                    'type' => 'video',
                    'platform' => ['youtube', 'instagram'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 19, // Oct 21
                    'caption' => 'Over 40 hours of hand stitching goes into every StyleCraft bespoke blazer. Watch the artistry.',
                    'brief' => 'Detailed 90s documentary capturing fabric cutting, pattern chalking, and hand buttonhole sewing.',
                    'hashtags' => '#bespoketailoring #couturecraft #handcrafted #luxuryfashion',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Size Recommendation AI Widget Sprint',
                    'type' => 'website',
                    'platform' => ['twitter'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 22, // Oct 24
                    'caption' => 'Predict perfect customer fits using height, weight, and preferred tightness to reduce return rates.',
                    'brief' => 'Develop responsive sizing modal integrated with size chart database and Google Analytics fit tracking.',
                    'hashtags' => '#retailtech #uximprovement #conversionoptimization #devsprint',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Diwali Contemporary Ethnic Wear Post',
                    'type' => 'post',
                    'platform' => ['instagram', 'facebook'],
                    'priority' => 'urgent',
                    'status' => 'todo',
                    'day_offset' => 26, // Oct 28
                    'caption' => 'Modern silhouettes infused with rich Banarasi brocade. The ultimate statement for festive evenings.',
                    'brief' => 'Stunning model portrait against ambient architectural arches with warm lantern illumination.',
                    'hashtags' => '#festivestyle #modernethnic #diwalioutfit #contemporaryindian',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Winter Knitwear Lookbook Carousel',
                    'type' => 'carousel',
                    'platform' => ['instagram', 'pinterest'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 30, // Oct 31
                    'caption' => 'Wrap yourself in cloud-soft Mongolian cashmere. 6 cozy looks to inspire your cold-weather dressing.',
                    'brief' => 'Textured lifestyle imagery in coffee shops and winter gardens with subtle tone-on-tone price badges.',
                    'hashtags' => '#cashmere #winterfashion #knitweardesign #cozystyle',
                    'team_type' => 'designer',
                ],
            ],

            // Client: AutoDrive (Automotive / EV)
            'AutoDrive' => [
                [
                    'title' => 'Next-Gen Electric SUV Acceleration Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'youtube'],
                    'priority' => 'high',
                    'status' => 'completed',
                    'day_offset' => -1, // Oct 01
                    'caption' => '0 to 100 in 3.6 seconds. Instant torque, zero emissions. Experience the pulse of AutoDrive Volt.',
                    'brief' => 'Night track footage with neon speed streaks, tire smoke sound design, and digital speedometer HUD overlay.',
                    'hashtags' => '#evperformance #electriccar #instanttorque #autodrivevolt',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'EV Charging Myths vs Facts Carousel',
                    'type' => 'carousel',
                    'platform' => ['linkedin', 'twitter'],
                    'priority' => 'normal',
                    'status' => 'completed',
                    'day_offset' => 1, // Oct 03
                    'caption' => 'Can you charge in heavy rain? How long does battery health actually last? We answer top EV queries.',
                    'brief' => 'High-tech infographic carousel with 3D battery renders, charging network map, and verified data stats.',
                    'hashtags' => '#evmyths #greenenergy #futuremobility #electricvehicle',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Festive Season Trade-In Bonus Post',
                    'type' => 'post',
                    'platform' => ['facebook', 'instagram'],
                    'priority' => 'urgent',
                    'status' => 'review',
                    'day_offset' => 4, // Oct 06
                    'caption' => 'Upgrade to the future today! Get up to ₹1,50,000 exchange bonus when you trade in any petrol/diesel car.',
                    'brief' => 'High-impact dealership sales creative with dynamic angled SUV render and clear bold promotional offer terms.',
                    'hashtags' => '#festivedeals #carupgrade #exchangebonus #newcarday',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Test Drive Booking Portal Speed Optimization',
                    'type' => 'website',
                    'platform' => ['linkedin'],
                    'priority' => 'high',
                    'status' => 'inprogress',
                    'day_offset' => 7, // Oct 09
                    'caption' => 'Instant calendar booking and OTP verification for doorstep test drive appointments.',
                    'brief' => 'Refactor geo-location dealership locator and integrate instant SMS appointment slot booking pipeline.',
                    'hashtags' => '#webperformance #geospatial #crmintegration #automotivetech',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Safety First: 5-Star Global NCAP Post',
                    'type' => 'post',
                    'platform' => ['linkedin', 'facebook'],
                    'priority' => 'normal',
                    'status' => 'inprogress',
                    'day_offset' => 10, // Oct 12
                    'caption' => 'Engineered like a fortress. AutoDrive Terra receives top honors with 5 stars in crash safety testing.',
                    'brief' => 'Technical diagram highlighting high-strength steel chassis frame and 8 standard airbags placement.',
                    'hashtags' => '#carsafety #ncap #familyprotection #solidbuild',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Off-Road Mountain Adventure Video',
                    'type' => 'video',
                    'platform' => ['youtube', 'facebook'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 14, // Oct 16
                    'caption' => 'Taking the AWD AutoDrive Terra across the treacherous Spiti Valley passes. Watch the expedition!',
                    'brief' => 'Drone aerials over winding Himalayan passes, muddy water crossings, and cinematic landscape horizons.',
                    'hashtags' => '#offroading #spitivalley #4x4adventure #mountainclimb',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Connected Car Mobile App Live Demo Stories',
                    'type' => 'story',
                    'platform' => ['instagram'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 18, // Oct 20
                    'caption' => 'Pre-cool your cabin, check battery range, and flash headlights from anywhere using AutoDrive App.',
                    'brief' => 'UI screen capture demo on iPhone mockups showing cabin climate slide control and digital key unlock.',
                    'hashtags' => '#connectedcar #smarttech #remotekeyless #mobilesoftware',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Telemetry API WebSocket Server Upgrade',
                    'type' => 'software',
                    'platform' => ['twitter'],
                    'priority' => 'high',
                    'status' => 'todo',
                    'day_offset' => 21, // Oct 23
                    'caption' => 'Scaling low-latency telemetry ingestion for fleet management dashboard over secure MQTT/WebSockets.',
                    'brief' => 'Setup Redis cluster for message queuing and live GPS coordinate broadcasting to frontend map layers.',
                    'hashtags' => '#mqtt #iotcloud #fleetmanagement #websockets',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Diwali Night Drive Highway Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'tiktok'],
                    'priority' => 'urgent',
                    'status' => 'todo',
                    'day_offset' => 25, // Oct 27
                    'caption' => 'Lighting up the expressway with intelligent matrix LED headlights. Safe festive journeys home.',
                    'brief' => 'Moody night highway drive with ambient interior ambient lighting color cycling and calm relaxing beat.',
                    'hashtags' => '#nightdrive #highwaycruise #festiveroadtrip #ambientlight',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Winter Road Trip Checklist Carousel',
                    'type' => 'carousel',
                    'platform' => ['instagram', 'facebook'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 29, // Oct 31
                    'caption' => 'Preparing your vehicle for foggy highways and colder mornings? 6 must-check items before departure.',
                    'brief' => 'Helpful automotive maintenance tip slides with tire pressure ratings, coolant check icons, and defogger tips.',
                    'hashtags' => '#cartips #winterdriving #roadtripsafety #maintenancecheck',
                    'team_type' => 'designer',
                ],
            ],

            // Client: Veera da Dhaba (Food & Beverage / Restaurant)
            'Veera da Dhaba' => [
                [
                    'title' => 'Slow-Cooked Dal Makhani ASMR Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'tiktok'],
                    'priority' => 'urgent',
                    'status' => 'completed',
                    'day_offset' => 0, // Oct 02
                    'caption' => 'Simmered overnight for 18 hours on slow charcoal flame with pure white butter. Pure soul food. 🍲🧈',
                    'brief' => 'Rich bubbling clay handi closeups, slow ladle swirl, dollop of melting makhan with sizzling sound design.',
                    'hashtags' => '#dalmakhani #punjabifood #dhabafood #comfortfood',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Weekend Family Feast Combo Post',
                    'type' => 'post',
                    'platform' => ['facebook', 'instagram'],
                    'priority' => 'high',
                    'status' => 'completed',
                    'day_offset' => 2, // Oct 04
                    'caption' => 'Gather the whole family! Maharaja Thali feast featuring 8 signature dishes, lassi, and gulab jamun.',
                    'brief' => 'Top-down banquet table photo with warm brass serveware, colorful curries, and bold weekend price badge.',
                    'hashtags' => '#familyfeast #thali #weekenddining #punjabilunch',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Online Table Reservation Web Module',
                    'type' => 'website',
                    'platform' => ['facebook'],
                    'priority' => 'high',
                    'status' => 'inprogress',
                    'day_offset' => 5, // Oct 07
                    'caption' => 'Skip the weekend queue! Reserve your favourite dhaba table online with instant WhatsApp confirmation.',
                    'brief' => 'Build lightweight mobile-first table booking form with party size picker and automated WhatsApp Business webhook.',
                    'hashtags' => '#webdev #tablereservation #dhabalife #restauranttech',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Tandoori Tikka Live Sizzle Stories',
                    'type' => 'story',
                    'platform' => ['instagram'],
                    'priority' => 'normal',
                    'status' => 'inprogress',
                    'day_offset' => 8, // Oct 10
                    'caption' => 'Fresh out of the earthen clay tandoor! Which one are you picking: Paneer or Chicken Tikka?',
                    'brief' => 'Vertical stories with glowing charcoal embers, smoking skewers, and interactive poll sticker.',
                    'hashtags' => '#tandoorisizzle #tikkalover #streetfooddelhi #foodiepoll',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Top 5 Signature Starters Carousel',
                    'type' => 'carousel',
                    'platform' => ['instagram', 'facebook'],
                    'priority' => 'normal',
                    'status' => 'review',
                    'day_offset' => 12, // Oct 14
                    'caption' => 'First time dining at Veera da Dhaba? Here are the 5 appetizers our regulars swear by. Swipe right 👉',
                    'brief' => 'Mouthwatering cards with crisp product names, spice meter icons, and recommended pairing drinks.',
                    'hashtags' => '#appetizers #startermenu #foodlovers #musttryfood',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Meet Head Chef Balwinder Video',
                    'type' => 'video',
                    'platform' => ['youtube', 'facebook'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 15, // Oct 17
                    'caption' => '30 years of culinary passion. Master Chef Balwinder shares the secret behind our heritage masala blend.',
                    'brief' => 'Warm kitchen interview showing roasted whole spices in mortar pestle, smiling kitchen team, and authentic stories.',
                    'hashtags' => '#chefstory #heritageflavors #punjabitradition #meetthechef',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Diwali Corporate Party Catering Brochure',
                    'type' => 'brochure',
                    'platform' => ['linkedin', 'facebook'],
                    'priority' => 'high',
                    'status' => 'todo',
                    'day_offset' => 18, // Oct 20
                    'caption' => 'Bring authentic dhaba flavors to your office Diwali party. Live live counters, chaat, and full bar packages.',
                    'brief' => 'Corporate catering catalogue with set menu tiers, live station photos, and customer testimonials.',
                    'hashtags' => '#corporatecatering #diwaliparty #officecelebration #eventcatering',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Dhaba POS Bill & Loyalty Points API Sprint',
                    'type' => 'software',
                    'platform' => ['twitter'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 22, // Oct 24
                    'caption' => 'Integrating Petpooja POS webhooks with digital customer reward points ledger.',
                    'brief' => 'Develop webhook receiver for order closed events and trigger automated loyalty point credit alerts.',
                    'hashtags' => '#posintegration #petpooja #loyaltyapp #laravelbackend',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Dussehra & Festive Celebrations Post',
                    'type' => 'post',
                    'platform' => ['instagram', 'facebook'],
                    'priority' => 'urgent',
                    'status' => 'todo',
                    'day_offset' => 25, // Oct 27
                    'caption' => 'Wishing everyone a joyous Dussehra! May the victory of good over evil bring happiness and feast to your homes.',
                    'brief' => 'Festive gold & saffron artistic graphic blending traditional celebratory elements with traditional dhaba sweets.',
                    'hashtags' => '#happydussehra #festivegreetings #celebrations #vijayadashami',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Winter Special Sarson Ka Saag Teaser Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'tiktok'],
                    'priority' => 'high',
                    'status' => 'todo',
                    'day_offset' => 29, // Oct 31
                    'caption' => 'Winter is coming... and so is fresh Sarson Ka Saag with Makki Di Roti and organic gur. Coming this Friday! 🌾',
                    'brief' => 'Steaming saag topped with melting yellow butter, hand-patted corn roti on hot tawa, traditional rustic audio.',
                    'hashtags' => '#sarsonkasaag #makkikiroti #winterdelicacy #punjabifoodie',
                    'team_type' => 'designer',
                ],
            ],

            // Client: Acme Corporation (Corporate / Enterprise)
            'Acme Corporation' => [
                [
                    'title' => 'Global Sustainability Impact Report 2026',
                    'type' => 'carousel',
                    'platform' => ['linkedin', 'twitter'],
                    'priority' => 'urgent',
                    'status' => 'completed',
                    'day_offset' => 0, // Oct 02
                    'caption' => 'Carbon neutrality achieved across 4 continents. Explore the key milestones from our ESG 2026 report.',
                    'brief' => 'Corporate infographic carousel with clean charts, globe vector graphics, and executive letter excerpt.',
                    'hashtags' => '#esgimpact #sustainability #climateaction #corporategovernance',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Enterprise Cloud Security Keynote Video',
                    'type' => 'video',
                    'platform' => ['youtube', 'linkedin'],
                    'priority' => 'high',
                    'status' => 'completed',
                    'day_offset' => 2, // Oct 04
                    'caption' => 'Catch the full keynote from our Chief Technology Officer at the International Cyber Security Summit.',
                    'brief' => 'Polished broadcast-quality video snippet with title animations, branded lower thirds, and audio mastering.',
                    'hashtags' => '#cybersecurity #keynote #techleadership #cloudenterprise',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Partner Ecosystem Integration Portal',
                    'type' => 'website',
                    'platform' => ['linkedin'],
                    'priority' => 'high',
                    'status' => 'inprogress',
                    'day_offset' => 5, // Oct 07
                    'caption' => 'Self-service API onboarding and sandbox credentials generation for global enterprise partners.',
                    'brief' => 'Implement OAuth2 client credential management and automated webhook testing playground in partner dashboard.',
                    'hashtags' => '#oauth2 #apigateway #partnerportal #enterpriseweb',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Q3 Global Quarterly Financial Results Post',
                    'type' => 'post',
                    'platform' => ['linkedin', 'twitter'],
                    'priority' => 'normal',
                    'status' => 'inprogress',
                    'day_offset' => 8, // Oct 10
                    'caption' => 'Strong double-digit YoY growth driven by digital transformation contracts across EMEA and APAC regions.',
                    'brief' => 'Wall-street journal style executive graphic with clean financial charts and quote from CEO.',
                    'hashtags' => '#quarterlyresults #financialgrowth #investorrelations #marketgrowth',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Future of Supply Chain AI Whitepaper Flyer',
                    'type' => 'flyer',
                    'platform' => ['linkedin'],
                    'priority' => 'normal',
                    'status' => 'review',
                    'day_offset' => 12, // Oct 14
                    'caption' => 'How autonomous predictive analytics are reshaping global container maritime shipping schedules.',
                    'brief' => 'Modern 2-page digital whitepaper preview sheet with download QR code and key executive summaries.',
                    'hashtags' => '#supplychain #automation #logisticsai #globaltrade',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Global Employee Spotlight Stories',
                    'type' => 'story',
                    'platform' => ['instagram', 'linkedin'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 16, // Oct 18
                    'caption' => 'Meet Elena from our Zurich Innovation Lab: Designing high-frequency trading architectures.',
                    'brief' => 'Candid workstation portrait, rapid-fire Q&A cards, and team shout-out stickers.',
                    'hashtags' => '#womenintech #employeespotlight #insideacme #lifeatacme',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Smart City Infrastructure Project Banner',
                    'type' => 'banner',
                    'platform' => ['linkedin'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 19, // Oct 21
                    'caption' => 'Powering next-generation electric grid sensors across 12 metropolitan smart city hubs.',
                    'brief' => 'Wide 1200x627 LinkedIn sponsored post banner with illuminated isometric 3D city grid visual.',
                    'hashtags' => '#smartcities #iotinfrastructure #cleantech #urbantech',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Internal Single Sign-On (SSO) SAML Upgrade',
                    'type' => 'software',
                    'platform' => ['twitter'],
                    'priority' => 'urgent',
                    'status' => 'todo',
                    'day_offset' => 23, // Oct 25
                    'caption' => 'Deploying Okta and Azure Active Directory SAML 2.0 federation for 50,000 global employee accounts.',
                    'brief' => 'Implement SAML 2.0 assertions parser and enforce automatic role-based group provisioning.',
                    'hashtags' => '#okta #azuread #identitymanagement #infosecdev',
                    'team_type' => 'developer',
                ],
                [
                    'title' => 'Annual Global Innovation Award Winner Reel',
                    'type' => 'reel',
                    'platform' => ['instagram', 'linkedin'],
                    'priority' => 'normal',
                    'status' => 'todo',
                    'day_offset' => 27, // Oct 29
                    'caption' => 'Celebrating the team behind the autonomous carbon capture sensor project at our annual summit.',
                    'brief' => 'Fast celebration reel with trophy stage lights, team high-fives, and proud quotes.',
                    'hashtags' => '#innovationawards #excellence #teamtriumph #leadership',
                    'team_type' => 'designer',
                ],
                [
                    'title' => 'Industry Leadership Trends Report Carousel',
                    'type' => 'carousel',
                    'platform' => ['linkedin', 'twitter'],
                    'priority' => 'high',
                    'status' => 'todo',
                    'day_offset' => 29, // Oct 31
                    'caption' => '5 macroeconomic and digital shifts that will define enterprise technology budgets in 2027.',
                    'brief' => 'Thought-leadership carousel with high-contrast typography, data points, and executive takeaways.',
                    'hashtags' => '#macrotrends #techoutlook #csuite #futureplanning',
                    'team_type' => 'designer',
                ],
            ],
        ];

        // Anchor date for calendar is October 2026
        $baseDate = Carbon::create(2026, 10, 2);

        $totalCreated = 0;

        foreach ($clients as $client) {
            $clientTemplates = $templates[$client->name] ?? null;

            // If a client doesn't have a custom profile, generate 10 tailored tasks for it
            if (!$clientTemplates) {
                $clientTemplates = $this->generateGenericTemplates($client->name);
            }

            // Ensure exactly 10 records for this client
            $clientTemplates = array_slice($clientTemplates, 0, 10);

            foreach ($clientTemplates as $idx => $t) {
                // Determine assignee based on team_type
                if ($t['team_type'] === 'developer' && $developers->isNotEmpty()) {
                    $assignee = $developers[$idx % $developers->count()];
                } elseif ($designers->isNotEmpty()) {
                    $assignee = $designers[$idx % $designers->count()];
                } else {
                    $assignee = $teamMembers[$idx % $teamMembers->count()];
                }

                // Alternating creator between strategists and admin
                $creator = ($idx % 2 === 0 && $strategists->isNotEmpty())
                    ? $strategists[$idx % $strategists->count()]
                    : $admin;

                // Compute realistic deadline & post_date relative to base date in October 2026
                $dayOffset = $t['day_offset'] ?? ($idx * 3);
                $deadlineDate = $baseDate->copy()->addDays($dayOffset);
                $postDate = $deadlineDate->copy()->addDay();

                $taskAttributes = [
                    'client_id' => $client->id,
                    'title' => $t['title'],
                ];

                $taskValues = [
                    'type' => $t['type'],
                    'platform' => is_array($t['platform']) ? $t['platform'] : [$t['platform']],
                    'priority' => $t['priority'],
                    'status' => $t['status'],
                    'deadline' => $deadlineDate->toDateString(),
                    'post_date' => $postDate->toDateString(),
                    'design_deadline' => $deadlineDate->toDateString(),
                    'dev_deadline' => $t['team_type'] === 'developer' ? $deadlineDate->toDateString() : null,
                    'caption' => $t['caption'] ?? null,
                    'brief' => $t['brief'] ?? null,
                    'hashtags' => $t['hashtags'] ?? null,
                    'assigned_to' => $assignee->id,
                    'created_by' => $creator->id,
                    'reference_links' => [
                        'https://dribbble.com/tags/branding',
                        'https://www.behance.net/search/projects?search=marketing',
                    ],
                    'started_at' => in_array($t['status'], ['inprogress', 'review', 'completed']) ? $deadlineDate->copy()->subDays(2) : null,
                    'completed_at' => $t['status'] === 'completed' ? $deadlineDate->copy()->setTime(17, 30) : null,
                ];

                Task::updateOrCreate($taskAttributes, $taskValues);
                $totalCreated++;
            }
        }

        $this->command?->info("Successfully seeded {$totalCreated} team calendar records across {$clients->count()} clients (10 records each).");
    }

    /**
     * Fallback generator for any client not hardcoded above.
     */
    private function generateGenericTemplates(string $clientName): array
    {
        return [
            ['title' => "Brand Awareness Campaign Reel - {$clientName}", 'type' => 'reel', 'platform' => ['instagram'], 'priority' => 'high', 'status' => 'completed', 'day_offset' => 0, 'caption' => "Discover the vision behind {$clientName}.", 'brief' => 'High-energy brand story reel.', 'hashtags' => '#brand #vision #future', 'team_type' => 'designer'],
            ['title' => "Weekly Offer Carousel - {$clientName}", 'type' => 'carousel', 'platform' => ['facebook', 'instagram'], 'priority' => 'normal', 'status' => 'completed', 'day_offset' => 2, 'caption' => 'Special promo carousel for community.', 'brief' => 'Multi-slide design with key highlights.', 'hashtags' => '#promo #deals', 'team_type' => 'designer'],
            ['title' => "Website Landing Page Revamp - {$clientName}", 'type' => 'website', 'platform' => ['linkedin'], 'priority' => 'urgent', 'status' => 'inprogress', 'day_offset' => 5, 'caption' => 'Refactoring hero section and CTA buttons.', 'brief' => 'Tailwind and responsive mobile improvements.', 'hashtags' => '#webdev #landingpage', 'team_type' => 'developer'],
            ['title' => "Behind The Scenes Story Set - {$clientName}", 'type' => 'story', 'platform' => ['instagram'], 'priority' => 'normal', 'status' => 'inprogress', 'day_offset' => 8, 'caption' => 'Meet our team and production floor.', 'brief' => 'Interactive 4-part Instagram stories.', 'hashtags' => '#bts #behindthescenes', 'team_type' => 'designer'],
            ['title' => "Product Spotlight Post - {$clientName}", 'type' => 'post', 'platform' => ['instagram', 'linkedin'], 'priority' => 'high', 'status' => 'review', 'day_offset' => 11, 'caption' => 'Engineered for quality and reliability.', 'brief' => 'Hero product image with callout badges.', 'hashtags' => '#spotlight #quality', 'team_type' => 'designer'],
            ['title' => "Customer Success Story Video - {$clientName}", 'type' => 'video', 'platform' => ['youtube'], 'priority' => 'normal', 'status' => 'todo', 'day_offset' => 15, 'caption' => 'How our solutions helped our client grow.', 'brief' => 'Customer interview video with b-roll.', 'hashtags' => '#casestudy #success', 'team_type' => 'designer'],
            ['title' => "API Integration Web Module - {$clientName}", 'type' => 'software', 'platform' => ['twitter'], 'priority' => 'normal', 'status' => 'todo', 'day_offset' => 18, 'caption' => 'Connecting third-party webhooks for real-time sync.', 'brief' => 'Secure webhook endpoints with signature validation.', 'hashtags' => '#backend #api', 'team_type' => 'developer'],
            ['title' => "Festive Promotion Banner - {$clientName}", 'type' => 'banner', 'platform' => ['facebook'], 'priority' => 'high', 'status' => 'todo', 'day_offset' => 22, 'caption' => 'Celebrate the festive season with exclusive perks.', 'brief' => 'Festive banner with gold & warm colors.', 'hashtags' => '#festivedeals #celebrate', 'team_type' => 'designer'],
            ['title' => "Educational Guide Infographic - {$clientName}", 'type' => 'post', 'platform' => ['linkedin'], 'priority' => 'normal', 'status' => 'todo', 'day_offset' => 26, 'caption' => '5 industry tips you need to know this month.', 'brief' => 'Clean infographic with key takeaways.', 'hashtags' => '#tips #education #industry', 'team_type' => 'designer'],
            ['title' => "Monthly Wrap-up & Highlights Reel - {$clientName}", 'type' => 'reel', 'platform' => ['instagram', 'tiktok'], 'priority' => 'urgent', 'status' => 'todo', 'day_offset' => 29, 'caption' => 'A recap of an incredible month at our company.', 'brief' => 'Fast pacing recap montage with music sync.', 'hashtags' => '#monthlywrapup #recap #highlights', 'team_type' => 'designer'],
        ];
    }
}
