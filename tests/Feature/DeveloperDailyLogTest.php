<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DailyWorkLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperDailyLogTest extends TestCase
{
    use RefreshDatabase;

    private User $developer;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $strategist = User::factory()->create(['role' => 'strategist']);
        $this->developer = User::factory()->create(['role' => 'developer']);
        $client = Client::create(['name' => 'ACME']);

        $this->task = Task::create([
            'title' => 'ACME Website',
            'client_id' => $client->id,
            'assigned_to' => $this->developer->id,
            'created_by' => $strategist->id,
            'type' => 'website',
            'priority' => 'normal',
            'status' => 'inprogress',
        ]);
    }

    private function entry(array $overrides = []): array
    {
        return array_merge([
            'task_id' => $this->task->id,
            'work_type' => 'development',
            'description' => 'Built the checkout page',
            'hours' => 3.5,
            'progress' => 40,
        ], $overrides);
    }

    public function test_developer_can_submit_a_daily_log_with_task_and_non_task_entries(): void
    {
        $this->actingAs($this->developer)
            ->post(route('developer.daily-log.store'), [
                'log_date' => today()->toDateString(),
                'entries' => [
                    $this->entry(),
                    $this->entry(['task_id' => null, 'work_type' => 'meeting', 'description' => 'Sprint call', 'hours' => 0.5, 'progress' => null]),
                ],
                'blockers' => 'Waiting for client logo',
                'plan_next' => 'Payment gateway',
            ])
            ->assertRedirect(route('developer.daily-log', ['date' => today()->toDateString()]))
            ->assertSessionHasNoErrors();

        $log = DailyWorkLog::with('entries')->firstOrFail();
        $this->assertSame($this->developer->id, $log->user_id);
        $this->assertSame('Waiting for client logo', $log->blockers);
        $this->assertCount(2, $log->entries);
        $this->assertEquals(4.0, $log->totalHours());
    }

    public function test_resubmitting_the_same_day_replaces_entries_instead_of_duplicating(): void
    {
        $this->actingAs($this->developer);
        $payload = ['log_date' => today()->toDateString(), 'entries' => [$this->entry()]];

        $this->post(route('developer.daily-log.store'), $payload);
        $payload['entries'] = [$this->entry(['hours' => 6, 'description' => 'Updated'])];
        $this->post(route('developer.daily-log.store'), $payload)->assertSessionHasNoErrors();

        $this->assertSame(1, DailyWorkLog::count());
        $log = DailyWorkLog::with('entries')->first();
        $this->assertCount(1, $log->entries);
        $this->assertSame('Updated', $log->entries->first()->description);
    }

    public function test_cannot_log_work_on_a_task_assigned_to_someone_else(): void
    {
        $other = Task::create([
            'title' => 'Not mine',
            'client_id' => $this->task->client_id,
            'assigned_to' => User::factory()->create(['role' => 'developer'])->id,
            'type' => 'website',
            'status' => 'inprogress',
        ]);

        $this->actingAs($this->developer)
            ->post(route('developer.daily-log.store'), [
                'log_date' => today()->toDateString(),
                'entries' => [$this->entry(['task_id' => $other->id])],
            ])
            ->assertSessionHasErrors('entries.0.task_id');

        $this->assertSame(0, DailyWorkLog::count());
    }

    public function test_rejects_more_than_24_hours_future_dates_and_dates_outside_the_edit_window(): void
    {
        $this->actingAs($this->developer);

        $this->post(route('developer.daily-log.store'), [
            'log_date' => today()->toDateString(),
            'entries' => [$this->entry(['hours' => 20]), $this->entry(['hours' => 5])],
        ])->assertSessionHasErrors('entries');

        $this->post(route('developer.daily-log.store'), [
            'log_date' => today()->addDay()->toDateString(),
            'entries' => [$this->entry()],
        ])->assertSessionHasErrors('log_date');

        $this->post(route('developer.daily-log.store'), [
            'log_date' => today()->subDays(DailyWorkLog::EDIT_WINDOW_DAYS + 1)->toDateString(),
            'entries' => [$this->entry()],
        ])->assertSessionHasErrors('log_date');

        $this->assertSame(0, DailyWorkLog::count());
    }

    public function test_developer_pages_render_with_daily_log_data(): void
    {
        $this->actingAs($this->developer)->post(route('developer.daily-log.store'), [
            'log_date' => today()->toDateString(),
            'entries' => [$this->entry()],
        ]);

        $this->get(route('developer.daily-log'))
            ->assertOk()
            ->assertSee('Built the checkout page')
            ->assertSee('Update log');

        $this->get(route('developer.dashboard'))
            ->assertOk()
            ->assertSee('Submitted today');

        $this->get(route('developer.tasks'))
            ->assertOk()
            ->assertSee('3.5 h');
    }

    public function test_team_report_shows_logs_and_missing_developers_to_admins_only(): void
    {
        $missing = User::factory()->create(['role' => 'developer', 'name' => 'Quiet Dev']);

        $this->actingAs($this->developer)->post(route('developer.daily-log.store'), [
            'log_date' => today()->toDateString(),
            'entries' => [$this->entry()],
            'blockers' => 'Need hosting access',
        ]);

        $this->get(route('daily-logs.report'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('daily-logs.report'))
            ->assertOk()
            ->assertSee('Built the checkout page')
            ->assertSee('Need hosting access')
            ->assertSee('Quiet Dev has not submitted a log for this day.');
    }
}
