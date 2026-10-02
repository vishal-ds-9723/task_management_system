<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Notification;
use App\Models\Task;
use App\Models\TaskPause;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $strategist;
    private User $developer;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->strategist = User::factory()->create(['role' => 'strategist']);
        $this->developer = User::factory()->create(['role' => 'developer']);
        $this->client = Client::create(['name' => 'ACME']);
    }

    private function task(array $attributes = []): Task
    {
        return Task::create(array_merge([
            'title' => 'ACME Website',
            'client_id' => $this->client->id,
            'assigned_to' => $this->developer->id,
            'created_by' => $this->strategist->id,
            'type' => 'website',
            'priority' => 'normal',
            'status' => 'inprogress',
        ], $attributes));
    }

    public function test_resuming_a_paused_task_records_a_positive_pause_duration(): void
    {
        $task = $this->task();
        $this->actingAs($this->developer);

        $this->postJson(route('developer.tasks.pause', $task), ['reason' => 'Waiting on client'])->assertOk();
        $this->travel(90)->minutes();
        $this->postJson(route('developer.tasks.resume', $task))->assertOk();

        $pause = TaskPause::firstOrFail();
        $this->assertGreaterThanOrEqual(5400, $pause->duration_seconds);
        $this->assertGreaterThanOrEqual(5400, $task->fresh()->total_paused_seconds);
    }

    public function test_submit_requires_task_in_progress_and_notifies_only_the_creator(): void
    {
        $otherStrategist = User::factory()->create(['role' => 'strategist']);
        $this->actingAs($this->developer);

        $completed = $this->task(['status' => 'completed']);
        $this->postJson(route('developer.tasks.submit', $completed), ['link' => 'https://example.com'])
            ->assertStatus(422);
        $this->assertSame('completed', $completed->fresh()->status);

        // The tasks page posts via fetch with X-Requested-With, like this
        $task = $this->task();
        $this->postJson(route('developer.tasks.submit', $task), ['link' => 'https://example.com'], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $this->assertSame('review', $task->fresh()->status);
        $this->assertSame(1, Notification::where('user_id', $this->strategist->id)->count());
        $this->assertSame(0, Notification::where('user_id', $otherStrategist->id)->count());
    }

    public function test_developer_cannot_comment_on_or_delete_media_from_a_task_assigned_to_someone_else(): void
    {
        $otherDeveloper = User::factory()->create(['role' => 'developer']);
        $task = $this->task(['assigned_to' => $otherDeveloper->id]);

        $this->actingAs($this->developer)
            ->postJson(route('developer.tasks.comment', $task), ['body' => 'hi'])
            ->assertForbidden();

        $this->actingAs($this->developer)
            ->deleteJson(route('developer.tasks.delete-media', $task), ['media_id' => 1])
            ->assertForbidden();
    }

    public function test_all_team_scope_only_lists_tasks_assigned_to_developers(): void
    {
        $designer = User::factory()->create(['role' => 'designer']);
        $this->task(['title' => 'Team dev project', 'assigned_to' => User::factory()->create(['role' => 'developer'])->id]);
        $this->task(['title' => 'Designer reel', 'type' => 'reel', 'assigned_to' => $designer->id]);

        $this->actingAs($this->developer)
            ->get(route('developer.tasks', ['scope' => 'all']))
            ->assertOk()
            ->assertSee('Team dev project')
            ->assertDontSee('Designer reel');
    }

    public function test_tasks_page_shows_revision_comments_and_actions_only_on_own_tasks(): void
    {
        $mine = $this->task(['title' => 'My todo project', 'status' => 'todo']);
        $theirs = $this->task([
            'title' => 'Teammate project',
            'status' => 'todo',
            'assigned_to' => User::factory()->create(['role' => 'developer'])->id,
        ]);
        \App\Models\Comment::create([
            'task_id' => $mine->id,
            'user_id' => $this->strategist->id,
            'body' => '🔄 Revision requested: Fix the mobile menu',
        ]);

        $response = $this->actingAs($this->developer)
            ->get(route('developer.tasks', ['scope' => 'all']))
            ->assertOk()
            ->assertSee('My todo project')
            ->assertSee('Teammate project')
            ->assertSee('Fix the mobile menu')
            ->assertSee('Revision request');

        $html = $response->getContent();
        $this->assertStringContainsString('data-start="' . route('developer.tasks.start', $mine) . '"', $html);
        $this->assertStringNotContainsString('data-start="' . route('developer.tasks.start', $theirs) . '"', $html);
    }

    public function test_start_returns_json_errors_and_comment_endpoint_works_for_assignee(): void
    {
        $this->actingAs($this->developer);
        $ajax = ['X-Requested-With' => 'XMLHttpRequest'];

        $inProgress = $this->task();
        $this->postJson(route('developer.tasks.start', $inProgress), [], $ajax)->assertStatus(422);

        $todo = $this->task(['status' => 'todo']);
        $this->postJson(route('developer.tasks.start', $todo), [], $ajax)->assertOk()->assertJson(['success' => true]);
        $this->assertSame('inprogress', $todo->fresh()->status);
        $this->assertNotNull($todo->fresh()->started_at);

        $this->postJson(route('developer.tasks.comment', $todo), ['body' => 'Staging is up'], $ajax)
            ->assertOk()
            ->assertJson(['success' => true, 'body' => 'Staging is up']);
        $this->assertSame(1, $todo->comments()->count());
    }

    public function test_dashboard_counts_overdue_and_shows_signed_deadline_labels(): void
    {
        $this->task(['title' => 'Late one', 'dev_deadline' => today()->subDays(2)->toDateString()]);
        $this->task(['title' => 'Soon one', 'dev_deadline' => today()->addDays(4)->toDateString()]);
        $this->task(['title' => 'Done one', 'status' => 'completed', 'dev_deadline' => today()->subDays(5)->toDateString()]);

        $this->actingAs($this->developer)
            ->get(route('developer.dashboard'))
            ->assertOk()
            ->assertViewHas('overdueTasks', 1)
            ->assertViewHas('upcomingDeadlines', 1)
            ->assertSee('Overdue by 2 days')
            ->assertSee('4 days left');
    }
}
