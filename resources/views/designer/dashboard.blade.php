@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/pages/designer-theme.css') }}?v=3.0" rel="stylesheet">
@endpush

@section('content')
@php
    $hour = now()->hour;
    $greetText = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

{{-- ══════════════════════════════════════════════
     1 · MINIMAL STUDIO HEADER & QUICK ACTIONS
     ══════════════════════════════════════════════ --}}
<div class="designer-header">
    <div class="designer-header-left">
        <div class="designer-date">
            <i class="fa-regular fa-calendar" style="font-size:11px"></i>
            <span>{{ now()->format('l, d F Y') }}</span>
        </div>
        <h1 class="designer-greeting">
            {{ $greetText }}, {{ auth()->user()->name }}
            @if($overdueTasks->count() > 0)
                <a href="{{ route('designer.tasks') }}?status=overdue" class="tag tag-red" style="font-size:11px;font-weight:700;text-decoration:none;margin-left:6px" title="Tasks past their deadline">
                    {{ $overdueTasks->count() }} Overdue
                </a>
            @endif
        </h1>
    </div>

    <div class="designer-actions">
        <a href="{{ route('designer.tasks') }}" class="btn-sec">
            <i class="fa-solid fa-list-check" style="font-size:12px"></i> My Tasks
        </a>
        <a href="{{ route('designer.urgent-task') }}" class="btn-action-urgent" title="Create an urgent task">
            <i class="fa-solid fa-bolt" style="font-size:11px"></i> Urgent Task
        </a>
        <button type="button" class="btn-action-primary" onclick="openSelfTaskModal({ defaultType: 'logo' })" title="Create a new task">
            <i class="fa-solid fa-plus" style="font-size:11px"></i> New Task
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     2 · BENTO METRIC RIBBON (4 CLEAN CARDS)
     ══════════════════════════════════════════════ --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-label">
            <span>Today's Tasks</span>
            <i class="fa-solid fa-inbox" style="font-size:11px"></i>
        </div>
        <div class="stat-card-value">{{ $todayTasks }}</div>
        <div class="stat-card-sub">Assigned for today</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-label">
            <span>Due This Week</span>
            <i class="fa-regular fa-clock" style="font-size:11px"></i>
        </div>
        <div class="stat-card-value">{{ $dueThisWeek }}</div>
        <div class="stat-card-sub">Upcoming deliverables</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-label">
            <span>Delivered</span>
            <i class="fa-solid fa-circle-check" style="font-size:11px;color:var(--dz-emerald)"></i>
        </div>
        <div class="stat-card-value">{{ $completedMonth }}</div>
        <div class="stat-card-sub">Completed this month</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-label">
            <span>Efficiency</span>
            <a href="{{ route('designer.performance') }}" class="sec-link" style="padding:0;font-size:11px" title="View performance metrics">
                Details →
            </a>
        </div>
        <div class="stat-card-value">{{ $efficiency }}%</div>
        <div class="stat-card-sub">On-time delivery rate</div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     3 · ACTIVE SPRINT FOCUS BANNER (LINEAR STYLE)
     ══════════════════════════════════════════════ --}}
@if($activeTask)
<a href="{{ route('designer.tasks.show', $activeTask) }}" class="designer-focus-banner" title="Click to continue this task">
    <div style="display:flex;align-items:center;gap:14px;min-width:0">
        <div class="df-badge">
            <span class="ds-pulse-dot"></span>
            In Progress
        </div>
        <div style="min-width:0">
            <div class="df-title" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ $activeTask->title }}
            </div>
            <div style="font-size:12px;color:var(--dz-text-muted);display:flex;align-items:center;gap:6px;margin-top:2px">
                <x-client-branding :client="$activeTask->client" size="16px" radius="3px" fit="contain" />
                <span style="font-weight:600;color:var(--dz-text-sub)">{{ $activeTask->client->name }}</span>
                <span>·</span>
                <span style="text-transform:capitalize">{{ $activeTask->type }}</span>
                @if($activeTask->deadline)
                    <span>·</span>
                    <span style="color:{{ $activeTask->isOverdue() ? '#DC2626' : 'var(--dz-text-muted)' }};font-weight:{{ $activeTask->isOverdue() ? '700' : '500' }}">
                        Due {{ $activeTask->deadline->format('d M') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div style="display:flex;align-items:center;gap:12px;flex-shrink:0">
        @if($activeTask->started_at)
            @php
                $elapsed = $activeTask->started_at->diff(now());
                $elapsedStr = '';
                if ($elapsed->d > 0) $elapsedStr .= $elapsed->d . 'd ';
                if ($elapsed->h > 0) $elapsedStr .= $elapsed->h . 'h ';
                $elapsedStr .= $elapsed->i . 'm';
            @endphp
            <span style="font-size:12px;font-weight:600;color:var(--dz-text-muted);display:inline-flex;align-items:center;gap:4px">
                <i class="fa-regular fa-clock" style="font-size:11px"></i> {{ trim($elapsedStr) }}
            </span>
        @endif
        <span class="btn-sec" style="padding:6px 12px !important;font-size:12px !important">
            Continue Work <i class="fa-solid fa-arrow-right" style="font-size:10px"></i>
        </span>
    </div>
</a>
@endif

{{-- ══════════════════════════════════════════════
     4 · MAIN STUDIO DASHBOARD (2 COLUMNS)
     ══════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1.6fr 1fr;gap:20px;align-items:start">
    {{-- Left: Today's Tasks Queue --}}
    <div class="card">
        <div class="sec-head">
            <div class="sec-title">
                <i class="fa-solid fa-list-check" style="color:var(--dz-primary);font-size:13px"></i>
                Today's Focus Queue
                <span style="font-size:12px;color:var(--dz-text-muted);font-weight:500">({{ $todayTasksList->count() }})</span>
            </div>
            <a href="{{ route('designer.tasks') }}" class="sec-link">View all →</a>
        </div>

        @forelse($todayTasksList as $task)
            <a href="{{ route('designer.tasks.show', $task) }}" class="dd-task-row">
                <x-client-branding :client="$task->client" size="24px" radius="4px" fit="contain" />
                <div style="flex:1;min-width:0">
                    <div class="dd-task-title" style="display:flex;align-items:center;gap:6px">
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $task->title }}</span>
                        @if($task->priority === 'urgent')
                            <span class="tag tag-red" style="font-size:9px;padding:1px 5px">URGENT</span>
                        @endif
                        @if($task->revision_count > 0)
                            <span class="tag tag-red" style="font-size:9px;padding:1px 5px">REV #{{ $task->revision_count }}</span>
                        @endif
                    </div>
                    <div class="dd-task-meta">
                        <span style="font-weight:600">{{ $task->client->name }}</span>
                        <span>·</span>
                        <span style="text-transform:capitalize">{{ $task->type }}</span>
                    </div>
                </div>
                <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                <div style="font-size:12px;color:{{ $task->isOverdue() ? '#DC2626' : 'var(--dz-text-muted)' }};white-space:nowrap;font-weight:{{ $task->isOverdue() ? '700' : '500' }}">
                    {{ $task->deadline?->format('d M') ?? '—' }}
                </div>
            </a>
        @empty
            <div style="text-align:center;padding:36px 16px;color:var(--dz-text-muted);font-size:13px">
                <i class="fa-solid fa-circle-check" style="font-size:22px;color:var(--dz-emerald);display:block;margin-bottom:8px"></i>
                All clear! No pending tasks scheduled for today
            </div>
        @endforelse
    </div>

    {{-- Right Column: Deadlines & Revisions --}}
    <div style="display:flex;flex-direction:column;gap:20px">
        {{-- Revisions Card (if any pending) --}}
        @if(isset($revisionInbox) && $revisionInbox->count() > 0)
        <div class="card" style="border-left:3px solid #DC2626">
            <div class="sec-head">
                <div class="sec-title" style="color:#DC2626">
                    <i class="fa-solid fa-rotate-left"></i>
                    Revisions Requested
                    <span style="font-size:12px;color:#DC2626;font-weight:700">({{ $revisionInbox->count() }})</span>
                </div>
            </div>

            @foreach($revisionInbox as $task)
                <a href="{{ route('designer.tasks.show', $task) }}" class="dd-task-row">
                    <x-client-branding :client="$task->client" size="20px" radius="4px" fit="contain" />
                    <div style="flex:1;min-width:0">
                        <div class="dd-task-title" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $task->title }}
                        </div>
                        <div class="dd-task-meta">
                            <span>{{ $task->client->name }}</span>
                            <span>·</span>
                            <span class="tag tag-red" style="font-size:9.5px;padding:1px 5px">Rev #{{ $task->revision_count }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        @endif

        {{-- Upcoming Deadlines Card --}}
        <div class="card">
            <div class="sec-head">
                <div class="sec-title">
                    <i class="fa-regular fa-calendar-check" style="color:var(--dz-primary);font-size:13px"></i>
                    Upcoming Deadlines
                </div>
                <a href="{{ route('designer.calendar') }}" class="sec-link">Calendar →</a>
            </div>

            @forelse($upcoming as $task)
                <a href="{{ route('designer.tasks.show', $task) }}" class="dd-task-row">
                    <x-client-branding :client="$task->client" size="22px" radius="4px" fit="contain" />
                    <div style="flex:1;min-width:0">
                        <div class="dd-task-title" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $task->title }}
                        </div>
                        <div class="dd-task-meta">
                            <span style="font-weight:600">{{ $task->client->name }}</span>
                        </div>
                    </div>
                    <div style="font-size:11.5px;color:{{ $task->isOverdue() ? '#DC2626' : 'var(--dz-text-muted)' }};font-weight:600;white-space:nowrap">
                        {{ $task->deadline?->format('d M') }}
                    </div>
                </a>
            @empty
                <div style="text-align:center;padding:36px 16px;color:var(--dz-text-muted);font-size:13px">
                    No upcoming deadlines this week
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
