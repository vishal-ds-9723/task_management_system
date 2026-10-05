<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Urgent tasks raised by designers now follow the same path as every other task:
 * designer submits → strategist review → admin approval → completed → (publishing for social posts).
 */
class UrgentTaskApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $designer;
    private User $strategist;
    private User $admin;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->designer = User::factory()->create(['role' => 'designer', 'name' => 'Dana Designer']);
        $this->strategist = User::factory()->create(['role' => 'strategist']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->client = Client::create(['name' => 'ACME']);
    }

    private function createAndSubmitUrgent(array $overrides = []): Task
    {
        $this->actingAs($this->designer)->postJson(route('designer.urgent-task.store'), array_merge([
            'title' => 'Flash sale story',
            'client_id' => $this->client->id,
            'type' => 'story',
            'platform' => ['instagram'],
            'urgent_requested_by' => 'Client CEO',
            'brief' => 'Same-day flash sale',
        ], $overrides), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();

        $task = Task::latest('id')->firstOrFail();
        $task->media()->create([
            'collection_name' => 'task-media', 'name' => 'design.png', 'file_name' => 'design.png',
            'mime_type' => 'image/png', 'disk' => 'public', 'path' => 'task-media/design.png', 'size' => 100, 'type' => 'image',
        ]);

        $this->actingAs($this->designer)
            ->postJson(route('designer.tasks.submit', $task), [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();

        return $task->fresh();
    }

    public function test_urgent_task_goes_to_strategist_review_instead_of_straight_to_completed(): void
    {
        $task = $this->createAndSubmitUrgent();

        $this->assertTrue($task->is_urgent_task);
        $this->assertSame('review', $task->status);
        $this->assertNull($task->completed_at);
        $this->assertTrue(Notification::where('user_id', $this->strategist->id)->where('title', 'Urgent task ready for review')->exists());

        // Visible in the strategist's Approvals queue, flagged as urgent
        $this->actingAs($this->strategist)->get(route('strategist.approvals'))->assertOk()->assertViewHas('pendingReviewCount', 1);
        $this->actingAs($this->strategist)
            ->getJson(route('strategist.approvals.data', ['draw' => 1, 'start' => 0, 'length' => 25]))
            ->assertOk()
            ->assertSee('Flash sale story')
            ->assertSee('by Dana Designer for Client CEO');

        // …and in Tracking
        $this->actingAs($this->strategist)->get(route('strategist.tracking'))->assertOk()->assertSee('Flash sale story');
    }

    public function test_full_flow_through_admin_approval_into_publishing(): void
    {
        $task = $this->createAndSubmitUrgent();
        $task->update(['caption' => 'Flash sale today only', 'hashtags' => '#sale']);

        $this->actingAs($this->strategist)->patchJson(route('strategist.tasks.approve', $task))->assertOk();
        $this->assertSame('pending_approval', $task->fresh()->status);

        // Shows on the admin approval screen (it used to redirect urgent tasks away)
        $this->actingAs($this->admin)->get(route('admin.approvals.show', $task))->assertOk();
        $this->actingAs($this->admin)->patchJson(route('admin.approvals.approve', $task))->assertOk();

        $task->refresh();
        $this->assertSame('completed', $task->status);
        $this->assertSame('approved', $task->admin_approval_status);
        $this->assertTrue(Notification::where('user_id', $this->strategist->id)->where('title', 'Urgent task approved — upload proof!')->exists());

        // Now waiting in the strategist publishing queue for proof links
        $this->actingAs($this->strategist)->get(route('strategist.publishing'))->assertOk()->assertSee('Flash sale story');
        $this->actingAs($this->strategist)->get(route('strategist.publishing.show', $task))->assertOk();
    }

    public function test_print_urgent_task_does_not_need_a_caption(): void
    {
        $task = $this->createAndSubmitUrgent(['title' => 'Urgent banner', 'type' => 'banner', 'platform' => null]);

        $this->actingAs($this->strategist)->patchJson(route('strategist.tasks.approve', $task))->assertOk();
        $this->actingAs($this->admin)->patchJson(route('admin.approvals.approve', $task))->assertOk();
        $this->assertSame('completed', $task->fresh()->status);
    }

    public function test_urgent_tasks_delivered_under_the_old_flow_stay_out_of_publishing(): void
    {
        Task::create([
            'title' => 'Old delivered urgent', 'client_id' => $this->client->id, 'type' => 'post', 'platform' => ['instagram'],
            'priority' => 'urgent', 'status' => 'completed', 'is_urgent_task' => true,
            'created_by' => $this->designer->id, 'assigned_to' => $this->designer->id,
        ]);

        $this->actingAs($this->strategist)->get(route('strategist.publishing'))->assertOk()->assertDontSee('Old delivered urgent');
    }
}
