<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientMonthlySchedule;
use App\Models\SocialMediaPost;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPlanTest extends TestCase
{
    use RefreshDatabase;

    private User $strategist;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 10, 10)->setTime(10, 0));
        $this->strategist = User::factory()->create(['role' => 'strategist']);
        $this->client = Client::create(['name' => 'ACME', 'is_active' => true]);
    }

    private function task(array $attributes = []): Task
    {
        return Task::create(array_merge([
            'title' => 'Post',
            'client_id' => $this->client->id,
            'created_by' => $this->strategist->id,
            'type' => 'reel',
            'priority' => 'normal',
            'status' => 'todo',
            'platform' => ['instagram'],
            'post_date' => '2026-10-20',
        ], $attributes));
    }

    public function test_plan_progress_counts_posted_ready_missed_and_still_to_create(): void
    {
        ClientMonthlySchedule::create(['client_id' => $this->client->id, 'month' => 10, 'year' => 2026, 'reels' => 5, 'posts' => 2]);

        $posted = $this->task(['title' => 'Live reel', 'status' => 'completed', 'post_date' => '2026-10-05']);
        SocialMediaPost::create(['task_id' => $posted->id, 'platform' => 'instagram', 'post_type' => 'reel', 'posted_at' => '2026-10-05', 'posted_by' => $this->strategist->id]);
        $this->task(['title' => 'Approved reel', 'status' => 'completed', 'post_date' => '2026-10-15']);
        $this->task(['title' => 'Late reel', 'status' => 'inprogress', 'post_date' => '2026-10-08']);
        $this->task(['title' => 'Next month reel', 'post_date' => '2026-11-03']);          // outside October
        $this->task(['title' => 'Website', 'type' => 'website', 'post_date' => null, 'deadline' => '2026-10-20']); // not content

        $response = $this->actingAs($this->strategist)->get(route('strategist.content-schedules', ['month' => 10, 'year' => 2026]))->assertOk();
        $row = $response->viewData('rows')->firstWhere('client.id', $this->client->id);
        $reels = $row->types['reels'];

        $this->assertSame(5, $reels->planned);
        $this->assertSame(3, $reels->created);
        $this->assertSame(1, $reels->posted);
        $this->assertSame(1, $reels->ready);
        $this->assertSame(1, $reels->missed);
        $this->assertSame(2, $reels->toCreate);
        $this->assertSame(2, $row->types['posts']->toCreate);
        $this->assertSame('behind', $row->status);
        $this->assertSame(14, $row->pct); // 1 of 7 planned posted

        $response->assertSee(route('strategist.create-task', ['client_id' => $this->client->id, 'type' => 'reel']));

        // The Reels popup lists every reel this month, plus a slot for each one not yet created
        $this->assertSame(['Live reel', 'Late reel', 'Approved reel'], $reels->items->pluck('task.title')->all());
        $html = $response->getContent();
        $popup = substr($html, strpos($html, 'id="cp2-pop-' . $this->client->id . '-reels"'));
        $popup = substr($popup, 0, strpos($popup, '</template>'));
        $this->assertStringContainsString('Live reel', $popup);
        $this->assertStringContainsString('Missed', $popup);
        $this->assertSame(2, substr_count($popup, 'Not created yet'));
    }

    public function test_page_is_read_only_for_strategists(): void
    {
        $this->actingAs($this->strategist)
            ->get(route("strategist.content-schedules", ["month" => 10, "year" => 2026]))
            ->assertOk()
            ->assertSee("No content plans for October 2026 yet")
            ->assertDontSee("Set plan");

        // No write endpoints exist for strategists; plans are managed by admins
        $this->actingAs($this->strategist)
            ->post("/strategist/content-schedules/" . $this->client->id, ["month" => 10, "year" => 2026, "posts" => 5])
            ->assertNotFound();
        $this->assertDatabaseCount("client_monthly_schedules", 0);
    }

    public function test_create_task_link_prefills_client_and_type(): void
    {
        $this->actingAs($this->strategist)
            ->get(route('strategist.create-task', ['client_id' => $this->client->id, 'type' => 'reel']))
            ->assertOk()
            ->assertSee('value="' . $this->client->id . '"', false)
            ->assertSee('<option value="reel"     selected', false);
    }
}
