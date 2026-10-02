{{-- resources/views/components/calendar-grid.blade.php --}}
@props(['tasks' => collect(), 'month' => now()->month, 'year' => now()->year])

@php
    $startOfMonth = \Carbon\Carbon::create($year, $month, 1);
    $daysInMonth  = $startOfMonth->daysInMonth;
    $startDay     = ($startOfMonth->dayOfWeekIso - 1);
    $today        = now()->day;
    $isCurrentMonth = now()->month == $month && now()->year == $year;

    $prevMonth = \Carbon\Carbon::create($year, $month, 1)->subMonth();
    $nextMonth = \Carbon\Carbon::create($year, $month, 1)->addMonth();

    $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    $totalTasks = is_array($tasks) ? collect($tasks)->flatten()->count() : $tasks->flatten()->count();

    $statusColors = [
        'todo'       => 'var(--yellow)',
        'inprogress' => 'var(--blue)',
        'review'     => 'var(--purple)',
        'completed'  => 'var(--teal)',
    ];
    $priorityIcons = [
        'urgent' => '<i class="fas fa-circle" style="font-size:8px;color:#EF4444"></i>',
        'high'   => '<i class="fas fa-circle" style="font-size:8px;color:#F97316"></i>',
        'normal' => '',
    ];
@endphp

{{-- ── Calendar Header ── --}}
<div class="calg-header">
    <div class="calg-header-left">
        <span class="calg-month-name">{{ $startOfMonth->format('F Y') }}</span>
        <span class="calg-task-count">{{ $totalTasks }} {{ Str::plural('task', $totalTasks) }}</span>
    </div>
    <div class="calg-header-nav">
        <a href="?month={{ $prevMonth->month }}&year={{ $prevMonth->year }}" class="calg-nav-btn" title="Previous">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <a href="?month={{ now()->month }}&year={{ now()->year }}" class="calg-nav-today">Today</a>
        <a href="?month={{ $nextMonth->month }}&year={{ $nextMonth->year }}" class="calg-nav-btn" title="Next">
            <i class="fa-solid fa-chevron-right"></i>
        </a>
    </div>
</div>

{{-- ── Calendar Grid ── --}}
<div class="cal-grid">
    {{-- Day labels --}}
    @foreach($days as $i => $d)
        <div class="cal-day-label {{ $i >= 5 ? 'weekend' : '' }}">{{ $d }}</div>
    @endforeach

    {{-- Empty leading days --}}
    @for($i = 0; $i < $startDay; $i++)
        <div class="cal-day empty"></div>
    @endfor

    {{-- Day cells --}}
    @for($d = 1; $d <= $daysInMonth; $d++)
        @php
            $isToday   = $isCurrentMonth && $d === $today;
            $dayTasks  = $tasks[$d] ?? collect();
            $dayTasks  = $dayTasks instanceof \Illuminate\Support\Collection ? $dayTasks : collect($dayTasks);
            $taskCount = $dayTasks->count();

            // Day-of-week for weekend styling (0=Mon..6=Sun)
            $cellDow = ($startDay + $d - 1) % 7;
            $isWeekend = $cellDow >= 5;
        @endphp
        <div class="cal-day {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }} {{ $taskCount > 0 ? 'has-tasks' : '' }}">
            {{-- Date row --}}
            <div class="cal-day-head">
                <div class="cal-date-wrap {{ $isToday ? 'today-circle' : '' }}">
                    <span class="cal-date {{ $isToday ? 'today-num' : '' }}">{{ $d }}</span>
                </div>
                @if($taskCount > 0)
                    <div class="cal-day-dots">
                        @foreach($dayTasks->take(4)->groupBy(fn($t) => $t->status) as $status => $group)
                            <span class="cal-status-dot" style="background:{{ $statusColors[$status] ?? 'var(--text3)' }}" title="{{ ucfirst($status) }}: {{ $group->count() }}"></span>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Task events --}}
            <div class="cal-events">
                @foreach($dayTasks->take(3) as $task)
                    @php
                        $color = $task->client->color ?? 'var(--primary)';
                        $logo  = $task->client->logo ?? null;
                        $emoji = $task->client->emoji ?? '';
                    @endphp
                    <div class="cal-event" title="{{ $task->title }} — {{ $task->client->name ?? '' }} ({{ ucfirst($task->status) }})">
                        <span class="cal-ev-bar" style="background:{{ $color }}"></span>
                        @if($logo)
                            <img src="{{ asset('storage/' . $logo) }}" alt="" class="cal-ev-logo">
                        @elseif($emoji)
                            <span class="cal-ev-emoji">{{ $emoji }}</span>
                        @endif
                        <span class="cal-ev-title">{{ Str::limit($task->title, 16) }}</span>
                        @if(($task->priority ?? 'normal') !== 'normal')
                            <span class="cal-ev-priority">{!! $priorityIcons[$task->priority] ?? '' !!}</span>
                        @endif
                    </div>
                @endforeach
                @if($taskCount > 3)
                    <div class="cal-more">+{{ $taskCount - 3 }} more</div>
                @endif
            </div>
        </div>
    @endfor
</div>
