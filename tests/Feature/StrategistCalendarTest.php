<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\SocialMediaPost;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrategistCalendarTest extends TestCase
{
    use RefreshDatabase;

    private User $strategist;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->strategist = User::factory()->create(['role' => 'strategist']);
        $this->client = Client::create(['name' => 'ACME']);
    }

    private function task(array $attributes = []): Task
    {
        return Task::create(array_merge([
            'title' => 'Launch reel',
            'client_id' => $this->client->id,
            'created_by' => $this->strategist->id,
            'type' => 'reel',
            'priority' => 'normal',
            'status' => 'todo',
            'platform' => ['instagram', 'facebook'],
            'post_date' => today()->toDateString(),
        ], $attributes));
    }

    public function test_week_view_tracks_each_channel_and_flags_missed_posts(): void
    {
        $posted = $this->task(['title' => 'Half posted carousel', 'status' => 'completed']);
        SocialMediaPost::create([
            'task_id' => $posted->id,
            'platform' => 'instagram',
            'post_type' => 'reel',
            'post_url' => 'https://instagram.com/p/abc',
            'posted_at' => today(),
            'posted_by' => $this->strategist->id,
        ]);
        $this->task(['title' => 'Yesterday story', 'status' => 'inprogress', 'post_date' => today()->subDay()->toDateString()]);

        $response = $this->actingAs($this->strategist)
            ->get(route('strategist.calendar', ['date' => today()->toDateString()]))
            ->assertOk()
            ->assertSee('Half posted carousel')
            ->assertSee('https://instagram.com/p/abc');

        $summary = $response->viewData('summary');
        $this->assertSame(1, $summary['counts']['done']);    // Instagram card of the carousel
        $this->assertSame(1, $summary['counts']['ready']);   // Facebook card: approved but not posted yet

        // Yesterday's unposted story counts as missed (only when yesterday falls in this week)
        if (today()->subDay()->gte(today()->startOfWeek())) {
            $this->assertSame(2, $summary['counts']['missed']);
        }
        $this->assertTrue($response->viewData('attention')['missed']->contains(fn ($i) => $i->task->title === 'Yesterday story'));
    }

    public function test_admin_calendar_uses_the_same_planner_with_admin_links(): void
    {
        $task = $this->task(['title' => 'Admin visible reel', 'post_date' => today()->addDay()->toDateString()]);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->get(route('admin.calendar', ['date' => today()->addDay()->toDateString()]))
            ->assertOk()
            ->assertSee('Admin visible reel')
            ->assertSee(route('admin.tasks.show', $task), false)
            ->assertSee('Drag a post to another day to reschedule');

        // Admin can drag every post; navigation stays on the admin route
        $this->assertStringContainsString('data-move-task="' . $task->id . '"', $response->getContent());
        $this->assertStringContainsString(route('admin.calendar'), $response->getContent());
    }

    public function test_strategists_can_only_drag_their_own_posts(): void
    {
        $mine = $this->task(['title' => 'Mine', 'post_date' => today()->addDay()->toDateString()]);
        $theirs = $this->task([
            'title' => 'Theirs',
            'post_date' => today()->addDay()->toDateString(),
            'created_by' => User::factory()->create(['role' => 'strategist'])->id,
        ]);

        $html = $this->actingAs($this->strategist)
            ->get(route('strategist.calendar', ['date' => today()->addDay()->toDateString()]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-move-task="' . $mine->id . '"', $html);
        $this->assertStringNotContainsString('data-move-task="' . $theirs->id . '"', $html);
    }

    public function test_month_view_and_filters_render(): void
    {
        $this->task(['title' => 'Mine']);
        $this->task(['title' => 'Someone else', 'created_by' => User::factory()->create(['role' => 'strategist'])->id]);
        $this->task(['title' => 'Dev work', 'type' => 'website']);

        $this->actingAs($this->strategist)
            ->get(route('strategist.calendar', ['view' => 'month']))
            ->assertOk()
            ->assertSee('Mine')
            ->assertSee('Someone else')
            ->assertDontSee('Dev work');

        $this->actingAs($this->strategist)
            ->get(route('strategist.calendar', ['mine' => 1]))
            ->assertOk()
            ->assertSee('Mine')
            ->assertDontSee('Someone else');
    }
}
