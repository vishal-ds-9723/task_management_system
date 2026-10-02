<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Task;
use App\Models\Client;
use App\Models\Setting;

class TaskUnlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_task_locked_before_submission_and_unlocked_after_review()
    {
        $strategist = User::factory()->create(['role' => 'strategist']);
        $designer = User::factory()->create(['role' => 'designer']);

        $client = Client::create(['name' => 'ACME']);

        $taskA = Task::create([
            'title' => 'Task A',
            'client_id' => $client->id,
            'assigned_to' => $designer->id,
            'created_by' => $strategist->id,
            'type' => 'post',
            'platform' => ['instagram'],
            'priority' => 'normal',
            'status' => 'inprogress',
            'post_date' => now()->subDays(2)->toDateString(),
        ]);

        // Attach a minimal media record so submission validation passes
        $taskA->media()->create([
            'collection_name' => 'task-media',
            'name' => 'design.png',
            'file_name' => 'design.png',
            'mime_type' => 'image/png',
            'disk' => 'public',
            'path' => 'task-media/design.png',
            'size' => 1234,
            'type' => 'image',
        ]);

        $taskB = Task::create([
            'title' => 'Task B',
            'client_id' => $client->id,
            'assigned_to' => $designer->id,
            'created_by' => $strategist->id,
            'type' => 'post',
            'platform' => ['instagram'],
            'priority' => 'normal',
            'status' => 'todo',
            'post_date' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($designer);

        // Task B should be locked initially
        $this->get(route('designer.tasks.show', $taskB))->assertStatus(403);

        // Submit Task A for review (normal flow)
        $this->postJson(route('designer.tasks.submit', $taskA))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Now Task B should be accessible
        $this->get(route('designer.tasks.show', $taskB))->assertStatus(200);
    }

    public function test_next_task_unlocked_when_submitted_pending_approval_away_mode()
    {
        $strategist = User::factory()->create(['role' => 'strategist']);
        $designer = User::factory()->create(['role' => 'designer']);

        $client = Client::create(['name' => 'ACME']);

        $taskA = Task::create([
            'title' => 'Task A 2',
            'client_id' => $client->id,
            'assigned_to' => $designer->id,
            'created_by' => $strategist->id,
            'type' => 'post',
            'platform' => ['instagram'],
            'priority' => 'normal',
            'status' => 'inprogress',
            'post_date' => now()->subDays(2)->toDateString(),
        ]);

        // Attach a minimal media record so submission validation passes
        $taskA->media()->create([
            'collection_name' => 'task-media',
            'name' => 'design.png',
            'file_name' => 'design.png',
            'mime_type' => 'image/png',
            'disk' => 'public',
            'path' => 'task-media/design.png',
            'size' => 1234,
            'type' => 'image',
        ]);

        $taskB = Task::create([
            'title' => 'Task B 2',
            'client_id' => $client->id,
            'assigned_to' => $designer->id,
            'created_by' => $strategist->id,
            'type' => 'post',
            'platform' => ['instagram'],
            'priority' => 'normal',
            'status' => 'todo',
            'post_date' => now()->addDay()->toDateString(),
        ]);

        // Enable away mode so submit goes to pending_approval
        Setting::set('strategist_away_mode', true);

        $this->actingAs($designer);

        // Task B should be locked initially
        $this->get(route('designer.tasks.show', $taskB))->assertStatus(403);

        // Submit Task A (away mode -> pending_approval)
        $this->postJson(route('designer.tasks.submit', $taskA))
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Now Task B should be accessible
        $this->get(route('designer.tasks.show', $taskB))->assertStatus(200);
    }
}
