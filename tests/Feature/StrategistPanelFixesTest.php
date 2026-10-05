<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrategistPanelFixesTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Client::create(['name' => 'ACME']);
    }

    private function task(User $creator, array $attributes = []): Task
    {
        return Task::create(array_merge([
            'title' => 'Manager post', 'client_id' => $this->client->id, 'created_by' => $creator->id,
            'type' => 'post', 'priority' => 'normal', 'status' => 'review', 'platform' => ['instagram'],
            'caption' => 'Hi', 'hashtags' => '#hi',
        ], $attributes));
    }

    public function test_managers_get_the_strategist_menu(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($manager)->get(route('strategist.dashboard'))
            ->assertOk()
            ->assertSee(route('strategist.approvals'), false)
            ->assertSee(route('strategist.team-board'), false);
    }

    public function test_strategist_revision_returns_task_to_in_progress(): void
    {
        $strategist = User::factory()->create(['role' => 'strategist']);
        $task = $this->task($strategist, ['assigned_to' => User::factory()->create(['role' => 'designer'])->id]);

        $this->actingAs($strategist)
            ->patchJson(route('strategist.tasks.request-revision', $task), ['revision_note' => 'Fix the logo'])
            ->assertSuccessful();

        $this->assertSame('inprogress', $task->fresh()->status);
        $this->assertSame(1, $task->fresh()->revision_count);
    }

    public function test_manager_created_posts_reach_the_publishing_queue(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $this->task($manager, ['title' => 'Manager approved post', 'status' => 'completed']);
        $this->task(User::factory()->create(['role' => 'admin']), ['title' => 'Admin own post', 'status' => 'completed']);

        $this->actingAs(User::factory()->create(['role' => 'strategist']))
            ->get(route('strategist.publishing'))
            ->assertOk()
            ->assertSee('Manager approved post')
            ->assertDontSee('Admin own post'); // admins publish their own from the admin panel
    }
}
