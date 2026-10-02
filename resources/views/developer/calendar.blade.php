@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
<style>
    .dev-wrap {
        --dev-theme: #f13535;
        --dev-theme-dark: #c92323;
        --dev-theme-soft: #fee2e2;
        --dev-theme-border: #fecaca;
        padding: 24px;
        font-family: 'Inter', sans-serif;
        color: #1f2937;
    }


    

    .dev-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .dev-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        color: #111111;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .summary-badges {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .summary-badge {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 999px;
        padding: 8px 16px;
        font-size: 13px;
        color: #374151;
        font-weight: 700;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    /* Filters */
    .filter-bar {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 16px;
        padding: 12px 16px;
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        align-items: center;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
        flex-wrap: wrap;
    }

    .filter-label {
        font-size: 13px;
        font-weight: 700;
        color: #6b7280;
        margin-right: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid #e5e7eb;
        background: rgba(255, 255, 255, 0.6);
        color: #4b5563;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .filter-btn:hover {
        background: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    .filter-btn.active {
        color: #fff;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-1px);
    }

    .filter-btn[data-status="all"].active { background: var(--dev-theme); }
    .filter-btn[data-status="todo"].active { background: var(--dev-theme); }
    .filter-btn[data-status="inprogress"].active { background: var(--dev-theme); }
    .filter-btn[data-status="review"].active { background: var(--dev-theme); }
    .filter-btn[data-status="completed"].active { background: var(--dev-theme); }

    .layout-grid {
        display: grid;
        grid-template-columns: 2.2fr 1fr;
        gap: 24px;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .layout-grid {
            grid-template-columns: 1fr;
        }
    }

    .glass-panel {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .calendar-header h2 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: #111827;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }

    /* Week Names (SUN MON TUE...) */
.calendar-day-header {
    text-align: center;
    font-size: 12px;
    font-weight: 800;
    padding: 10px 0;
    color: var(--dev-theme);
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* Each Day Box */
.calendar-day {
    background: #ffffff; /* WHITE */
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    min-height: 90px;
    padding: 8px;
    position: relative;
    transition: all 0.2s ease;
}

    .calendar-day:hover {
        border-color: #d1d5db;
        box-shadow: 0 8px 16px rgba(0,0,0,0.03);
    }

    .calendar-day.not-current-month {
        background: #f9fafb;
        opacity: 0.7;
    }

    /* Hover effect */
.calendar-day:hover {
    border-color: var(--dev-theme);
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.1);
}

/* Day Number */
.day-number {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
}

/* Today Highlight */
.calendar-day.today {
    border: 2px solid var(--dev-theme);
}


/* Not Current Month (fade effect) */
.calendar-day.not-current-month {
    opacity: 0.4;
}

/* Task inside calendar */
.cal-task-item {
    background: #f9fafb;
    border-radius: 6px;
    padding: 4px 6px;
    font-size: 10px;
    margin-top: 4px;
    border-left: 3px solid var(--dev-theme);
}


    .day-tasks {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1;
        overflow-y: auto;
    }

    .day-tasks::-webkit-scrollbar { width: 4px; }
    .day-tasks::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    .cal-task-item {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-left: 3px solid var(--dev-theme);
        border-radius: 6px;
        padding: 6px 8px;
        font-size: 11px;
        cursor: pointer;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        display: block;
        text-decoration: none;
        color: inherit;
    }

    .cal-task-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .cal-task-item.status-todo { border-left-color: var(--dev-theme); }
    .cal-task-item.status-inprogress { border-left-color: #dc2626; }
    .cal-task-item.status-review { border-left-color: #b91c1c; }
    .cal-task-item.status-completed { border-left-color: #7f1d1d; opacity: 0.7; }

    .cal-task-client {
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 2px;
        color: #4b5563;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cal-task-client img {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        object-fit: cover;
    }

    .cal-task-title {
        font-weight: 500;
        color: #111827;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-section {
        margin-bottom: 24px;
    }

    .sidebar-section h3 {
        margin: 0 0 16px;
        font-size: 16px;
        font-weight: 800;
        color: #111827;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sidebar-section h3 i {
        color: var(--dev-theme);
    }

    .ongoing-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 20px;
    }

    .mini-stat {
        background: linear-gradient(145deg, #ffffff, #f9fafb);
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    }

    .mini-stat .label {
        font-size: 11px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .mini-stat .value {
        font-size: 24px;
        color: #111827;
        font-weight: 800;
        line-height: 1;
    }

    .task-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-height: 480px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .task-list::-webkit-scrollbar { width: 4px; }
    .task-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    .sb-task-item {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
    }

    .sb-task-item:hover {
        box-shadow: 0 8px 16px rgba(0,0,0,0.04);
        border-color: #d1d5db;
        transform: translateY(-2px);
    }

    .sb-task-title {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
    }

    .sb-task-meta {
        font-size: 12px;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .sb-task-client {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
    }

    .sb-task-client img {
        width: 16px;
        height: 16px;
        border-radius: 50%;
    }

    .status-chip {
        font-size: 10px;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-chip.todo { background: #ffe6e6; color: #f13535; }
    .status-chip.inprogress { background: #ffd9d9; color: #dd2f2f; }
    .status-chip.review { background: #ffcdcd; color: #c92323; }
    .status-chip.completed { background: #ffeff0; color: #a81d1d; }

    .empty-state {
        text-align: center;
        padding: 30px 20px;
        background: #f9fafb;
        border-radius: 12px;
        border: 1px dashed #d1d5db;
        color: #6b7280;
        font-size: 13px;
        font-weight: 500;
    }

    .hidden-task {
        display: none !important;
    }

</style>

<div class="dev-wrap">
    <div class="dev-header">
        <h1><i class="fa-solid fa-calendar-days"></i> Developer Calendar</h1>
        <div class="summary-badges">
            <span class="summary-badge"><i class="fa-solid fa-list-check" style="color: #f13535; margin-right: 6px;"></i> Total: {{ $tasks->count() }}</span>
            <span class="summary-badge"><i class="fa-solid fa-clock-rotate-left" style="color: #f13535; margin-right: 6px;"></i> Ongoing: {{ $ongoingTasks->count() }}</span>
            <span class="summary-badge"><i class="fa-solid fa-spinner fa-spin" style="color: #f13535; margin-right: 6px;"></i> In Progress: {{ $ongoingInProgressCount }}</span>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <span class="filter-label"><i class="fa-solid fa-filter"></i> Filter Tasks:</span>
        <button class="filter-btn active" data-status="all" onclick="filterTasks('all')">
            <i class="fa-solid fa-layer-group"></i> All
        </button>
        <button class="filter-btn" data-status="todo" onclick="filterTasks('todo')">
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> To Do
        </button>
        <button class="filter-btn" data-status="inprogress" onclick="filterTasks('inprogress')">
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> In Progress
        </button>
        <button class="filter-btn" data-status="review" onclick="filterTasks('review')">
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> Review
        </button>
        <button class="filter-btn" data-status="completed" onclick="filterTasks('completed')">
            <i class="fa-solid fa-circle" style="font-size: 8px; color: #f13535;"></i> Completed
        </button>
    </div>

    <div class="layout-grid">
        <!-- Calendar Main Panel -->
        <div class="glass-panel">
            <div class="calendar-header">
                <h2>{{ now()->format('F Y') }}</h2>
                <div>
                    <!-- You could add month navigation here in the future -->
                </div>
            </div>

            <div class="calendar-grid">
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>

                @php
                    $now = now();
                    $year = $now->year;
                    $month = $now->month;
                    $firstDay = \Carbon\Carbon::create($year, $month, 1);
                    $lastDay = $firstDay->copy()->endOfMonth();
                    $startDate = $firstDay->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                    $endDate = $lastDay->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                    $currentDate = $startDate->copy();
                @endphp

                @while ($currentDate <= $endDate)
                    @php
                        $dateKey = $currentDate->format('Y-m-d');
                        $dayTasks = $tasksByDate[$dateKey] ?? collect();
                        $isCurrentMonth = $currentDate->month === $month;
                        $isToday = $currentDate->isToday();
                    @endphp
                    <div class="calendar-day {{ !$isCurrentMonth ? 'not-current-month' : '' }} {{ $isToday ? 'today' : '' }}">
                        <span class="day-number">{{ $currentDate->day }}</span>
                        
                        <div class="day-tasks">
                            @foreach ($dayTasks as $task)
                                <!-- Assuming developer tasks might have an edit/show link, we wrap it in a div/a -->
                                <div class="cal-task-item status-{{ $task->status }}" data-task-status="{{ $task->status }}" title="{{ $task->title }}">
                                    <div class="cal-task-client">
                                        @if($task->client && $task->client->logo)
                                            <img src="{{ asset('storage/' . $task->client->logo) }}" alt="">
                                        @else
                                            <span>{{ $task->client->emoji ?? '📁' }}</span>
                                        @endif
                                        <span>{{ $task->client->name ?? 'Unknown Client' }}</span>
                                    </div>
                                    <div class="cal-task-title">{{ $task->title ?? 'Untitled Task' }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @php $currentDate->addDay(); @endphp
                @endwhile
            </div>
        </div>

        <!-- Sidebar Panel -->
        <div class="glass-panel">
            <div class="sidebar-section">
                <h3><i class="fa-solid fa-thumbtack"></i> Ongoing Update</h3>
                <div class="ongoing-stats">
                    <div class="mini-stat">
                        <div class="label">To Do</div>
                        <div class="value" style="color: #f13535;">{{ $ongoingTodoCount }}</div>
                    </div>
                    <div class="mini-stat">
                        <div class="label">In Progress</div>
                        <div class="value" style="color: #f13535;">{{ $ongoingInProgressCount }}</div>
                    </div>
                </div>
            </div>

            <div class="sidebar-section">
                <h3><i class="fa-solid fa-bars-staggered"></i> Priority Tasks</h3>
                <div class="task-list">
                    @forelse ($ongoingTasks as $task)
                        <div class="sb-task-item">
                            <div class="sb-task-title">{{ $task->title ?? 'Untitled Task' }}</div>
                            <div class="sb-task-meta">
                                <div class="sb-task-client">
                                    @if($task->client && $task->client->logo)
                                        <img src="{{ asset('storage/' . $task->client->logo) }}" alt="">
                                    @else
                                        <span>{{ $task->client->emoji ?? '📁' }}</span>
                                    @endif
                                    <span>{{ $task->client->name ?? 'Unknown' }}</span>
                                </div>
                                <span class="status-chip {{ $task->status }}">
                                    {{ $task->status === 'inprogress' ? 'In Progress' : $task->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fa-solid fa-mug-hot" style="font-size: 24px; color: #d1d5db; margin-bottom: 10px; display: block;"></i>
                            No ongoing tasks right now.<br>Time for a coffee break!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>


</div>

<script>
    function filterTasks(status) {
        // Update active button state
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.currentTarget.classList.add('active');

        // Filter tasks in the calendar grid
        const tasks = document.querySelectorAll('.cal-task-item');
        tasks.forEach(task => {
            const taskStatus = task.getAttribute('data-task-status');
            if (status === 'all' || taskStatus === status) {
                task.classList.remove('hidden-task');
            } else {
                task.classList.add('hidden-task');
            }
        });
    }
</script>
@endsection
