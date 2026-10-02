<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendDeadlineNotifications extends Command
{
    protected $signature = 'notifications:deadlines';

    protected $description = 'Send notifications for tasks hitting their deadline or design deadline today/tomorrow';

    public function handle()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $count = 0;

        // ── Design deadline (5-day prior) alerts ──
        $designTodayTasks = Task::with('client')
            ->where('status', '!=', 'completed')
            ->whereDate('design_deadline', $today)
            ->get();

        foreach ($designTodayTasks as $task) {
            $recipients = $this->getRecipients($task);
            foreach ($recipients as $userId) {
                if ($this->alreadySent($userId, $task->id, 'design_deadline_today')) continue;
                Notification::create([
                    'user_id'  => $userId,
                    'task_id'  => $task->id,
                    'icon'     => '🎨',
                    'title'    => 'Design due today',
                    'subtitle' => $task->title . ($task->client ? ' · ' . $task->client->name : ''),
                    'link'     => $this->taskLink($task, $userId),
                ]);
                $count++;
            }
        }

        $designTomorrowTasks = Task::with('client')
            ->where('status', '!=', 'completed')
            ->whereDate('design_deadline', $tomorrow)
            ->get();

        foreach ($designTomorrowTasks as $task) {
            $recipients = $this->getRecipients($task);
            foreach ($recipients as $userId) {
                if ($this->alreadySent($userId, $task->id, 'design_deadline_tomorrow')) continue;
                Notification::create([
                    'user_id'  => $userId,
                    'task_id'  => $task->id,
                    'icon'     => '🎨',
                    'title'    => 'Design due tomorrow',
                    'subtitle' => $task->title . ($task->client ? ' · ' . $task->client->name : ''),
                    'link'     => $this->taskLink($task, $userId),
                ]);
                $count++;
            }
        }

        // ── Actual deadline alerts ──
        $deadlineTodayTasks = Task::with('client')
            ->where('status', '!=', 'completed')
            ->whereDate('deadline', $today)
            ->get();

        foreach ($deadlineTodayTasks as $task) {
            $recipients = $this->getRecipients($task);
            foreach ($recipients as $userId) {
                if ($this->alreadySent($userId, $task->id, 'deadline_today')) continue;
                Notification::create([
                    'user_id'  => $userId,
                    'task_id'  => $task->id,
                    'icon'     => '🚨',
                    'title'    => 'Deadline today!',
                    'subtitle' => $task->title . ($task->client ? ' · ' . $task->client->name : ''),
                    'link'     => $this->taskLink($task, $userId),
                ]);
                $count++;
            }
        }

        $deadlineTomorrowTasks = Task::with('client')
            ->where('status', '!=', 'completed')
            ->whereDate('deadline', $tomorrow)
            ->get();

        foreach ($deadlineTomorrowTasks as $task) {
            $recipients = $this->getRecipients($task);
            foreach ($recipients as $userId) {
                if ($this->alreadySent($userId, $task->id, 'deadline_tomorrow')) continue;
                Notification::create([
                    'user_id'  => $userId,
                    'task_id'  => $task->id,
                    'icon'     => '⏰',
                    'title'    => 'Deadline tomorrow',
                    'subtitle' => $task->title . ($task->client ? ' · ' . $task->client->name : ''),
                    'link'     => $this->taskLink($task, $userId),
                ]);
                $count++;
            }
        }

        // ── Overdue alerts ──
        $overdueTasks = Task::with('client')
            ->where('status', '!=', 'completed')
            ->whereDate('deadline', '<', $today)
            ->get();

        foreach ($overdueTasks as $task) {
            $recipients = $this->getRecipients($task);
            foreach ($recipients as $userId) {
                if ($this->alreadySent($userId, $task->id, 'overdue')) continue;
                Notification::create([
                    'user_id'  => $userId,
                    'task_id'  => $task->id,
                    'icon'     => '🔴',
                    'title'    => 'Task overdue!',
                    'subtitle' => $task->title . ($task->client ? ' · ' . $task->client->name : ''),
                    'link'     => $this->taskLink($task, $userId),
                ]);
                $count++;
            }
        }

        $this->info("Sent {$count} deadline notifications.");
    }

    private function getRecipients(Task $task): array
    {
        $ids = [];
        if ($task->assigned_to) $ids[] = $task->assigned_to;
        if ($task->created_by) $ids[] = $task->created_by;
        return array_unique($ids);
    }

    private function taskLink(Task $task, int $userId): string
    {
        $user = \App\Models\User::find($userId);
        if (!$user) return '/';
        return match ($user->role) {
            'admin'      => '/admin/tasks',
            'strategist' => '/strategist/tracking',
            'designer'   => '/designer/tasks',
            default      => '/',
        };
    }

    private function alreadySent(int $userId, int $taskId, string $titleKey): bool
    {
        $titleMap = [
            'design_deadline_today'    => 'Design due today',
            'design_deadline_tomorrow' => 'Design due tomorrow',
            'deadline_today'           => 'Deadline today!',
            'deadline_tomorrow'        => 'Deadline tomorrow',
            'overdue'                  => 'Task overdue!',
        ];
        return Notification::where('user_id', $userId)
            ->where('task_id', $taskId)
            ->where('title', $titleMap[$titleKey] ?? '')
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }
}
