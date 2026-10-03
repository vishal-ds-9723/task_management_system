<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamBoardTest extends TestCase
{
    use RefreshDatabase;

    private User $strategist;
    private User $designer;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->strategist = User::factory()->create(['role' => 'strategist']);
        $this->designer = User::factory()->create(['role' => 'designer', 'name' => 'Dana Designer']);
        $this->client = Client::create(['name' => 'ACME']);
    }

    private function task(array $attributes = []): Task
    {
        return Task::create(array_merge([
            'title' => 'Task',
            'client_id' => $this->client->id,
            'created_by' => $this->strategist->id,
            'assigned_to' => $this->designer->id,
            'type' => 'post',
            'priority' => 'normal',
            'status' => 'todo',
            'platform' => ['instagram'],
            'deadline' => today()->addDays(5)->toDateString(),
        ], $attributes));
    }

    public function test_board_shows_current_work_and_orders_the_queue(): void
    {
        $this->task(['title' => 'Current reel', 'status' => 'inprogress', 'started_at' => now()->subHour()]);
        $this->task(['title' => 'Later post', 'deadline' => today()->addDays(9)->toDateString()]);
        $this->task(['title' => 'Late banner', 'deadline' => today()->subDays(2)->toDateString()]);
        $this->task(['title' => 'Soon story', 'deadline' => today()->addDay()->toDateString(), 'priority' => 'urgent']);
        $this->task(['title' => 'In review carousel', 'status' => 'review']);
        User::factory()->create(['role' => 'developer', 'name' => 'Idle Dev']);
        $this->task(['title' => 'Orphan flyer', 'assigned_to' => null]);

        $response = $this->actingAs($this->strategist)->get(route('strategist.team-board'))->assertOk();

        $lane = $response->viewData('people')->firstWhere('user.id', $this->designer->id);
        $this->assertSame(['Current reel'], $lane->now->pluck('task.title')->all());
        // Overdue first, then nearest due date
        $this->assertSame(['Late banner', 'Soon story', 'Later post'], $lane->next->pluck('task.title')->all());
        $this->assertSame(1, $lane->waiting->count());
        $this->assertSame(1, $lane->overdue);
        $this->assertSame('behind', $lane->status);

        $idle = $response->viewData('people')->firstWhere('user.name', 'Idle Dev');
        $this->assertSame('available', $idle->status);

        $response->assertSee('Orphan flyer')->assertSee('with no one assigned');
    }

    public function test_board_is_available_to_strategists_and_admins_only(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('admin.team-board'))->assertOk();
        $this->actingAs($this->designer)->get(route('strategist.team-board'))->assertForbidden();
    }
}
