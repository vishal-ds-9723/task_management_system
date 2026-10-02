<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevProjectCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $strategist;
    private User $admin;
    private User $developer;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->strategist = User::factory()->create(['role' => 'strategist']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->developer = User::factory()->create(['role' => 'developer']);
        $this->client = Client::create(['name' => 'ACME']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'ACME Website',
            'client_id' => $this->client->id,
            'type' => 'website',
            'priority' => 'normal',
            'status' => 'todo',
            'assigned_to' => $this->developer->id,
            'brief' => 'Five-page marketing site',
            'project_start_date' => '2026-10-10',
            'dev_deadline' => '2026-10-25',
            'launch_date' => '2026-10-30',
            'preferred_tech' => 'Laravel',
            'modules' => "Home\nAbout\nContact",
            'logo_received' => 1,
        ], $overrides);
    }

    public function test_strategist_creates_website_project_and_developer_is_notified(): void
    {
        $this->actingAs($this->strategist)
            ->postJson(route('strategist.tasks.store'), $this->payload())
            ->assertOk();

        $task = Task::firstOrFail();
        $this->assertSame('2026-10-25', $task->dev_deadline->toDateString());
        $this->assertSame('Five-page marketing site', $task->brief);
        $this->assertTrue($task->logo_received);
        $this->assertNull($task->images_received, 'Unanswered asset questions stay unknown');

        $notification = Notification::where('user_id', $this->developer->id)->firstOrFail();
        $this->assertSame(route('developer.tasks', ['task' => $task->id]), $notification->link);
    }

    public function test_dev_projects_reject_non_builder_assignees_and_backwards_dates(): void
    {
        $contentWriter = User::factory()->create(['role' => 'content_writer']);

        $this->actingAs($this->strategist)
            ->postJson(route('strategist.tasks.store'), $this->payload(['assigned_to' => $contentWriter->id]))
            ->assertStatus(422)->assertJsonValidationErrors('assigned_to');

        $this->actingAs($this->strategist)
            ->postJson(route('strategist.tasks.store'), $this->payload(['launch_date' => '2026-10-01']))
            ->assertStatus(422)->assertJsonValidationErrors('launch_date');

        $this->assertSame(0, Task::count());
    }

    public function test_designer_can_be_assigned_a_website_and_sees_project_details(): void
    {
        $designer = User::factory()->create(['role' => 'designer']);

        $this->actingAs($this->strategist)
            ->postJson(route('strategist.tasks.store'), $this->payload([
                'assigned_to' => $designer->id,
                'business_type' => 'ecommerce',
                'images_received' => '0',
            ]))
            ->assertOk();

        $task = Task::firstOrFail();
        $this->assertSame('2026-10-25', $task->deadline->toDateString(), 'Designer views sort by deadline');
        $this->assertFalse($task->images_received);
        $this->assertSame(route('designer.tasks.show', $task), Notification::where('user_id', $designer->id)->value('link'));

        $this->actingAs($designer)
            ->get(route('designer.tasks.show', $task))
            ->assertOk()
            ->assertSee('Website project')
            ->assertSee('E-commerce store')
            ->assertSee('Contact')
            ->assertSee('Project Brief');
    }

    public function test_create_forms_render_the_project_builder(): void
    {
        $this->actingAs($this->strategist)->get(route('strategist.create-task'))
            ->assertOk()->assertSee('data-dev-builder', false)->assertSee("Who's building it?", false);

        $this->actingAs($this->admin)->get(route('admin.tasks.create'))
            ->assertOk()->assertSee('id="modalDevSection"', false);
    }

    public function test_maintenance_type_can_be_created(): void
    {
        $this->actingAs($this->strategist)
            ->postJson(route('strategist.tasks.store'), $this->payload(['type' => 'maintenance', 'title' => 'Monthly upkeep']))
            ->assertOk();

        $this->assertSame('maintenance', Task::firstOrFail()->type);
    }

    public function test_admin_creates_website_project_without_post_date_and_keeps_dev_fields(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.tasks.store'), $this->payload(), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        $task = Task::firstOrFail();
        $this->assertNull($task->post_date);
        $this->assertSame('2026-10-25', $task->dev_deadline->toDateString());
        $this->assertSame("Home\nAbout\nContact", $task->modules);
        $this->assertSame('Laravel', $task->preferred_tech);

        $notification = Notification::where('user_id', $this->developer->id)->firstOrFail();
        $this->assertSame(route('developer.tasks', ['task' => $task->id]), $notification->link);
    }

    public function test_social_tasks_still_require_post_date_for_admin(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.tasks.store'), [
                'title' => 'Reel', 'client_id' => $this->client->id, 'type' => 'reel',
                'priority' => 'normal', 'status' => 'todo', 'platform' => ['instagram'],
            ])
            ->assertStatus(422)->assertJsonValidationErrors('post_date');
    }
}
