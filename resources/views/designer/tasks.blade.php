@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/pages/designer-theme.css') }}?v=2.0" rel="stylesheet">
@endpush

@section('content')
@php
    $inProgress = $tasks->where('status', 'inprogress')->where('is_paused', false);
    $paused     = $tasks->where('is_paused', true);
    $todo       = $tasks->filter(fn($t) => $t->status === 'todo' && !$t->is_locked);
    $locked     = $tasks->filter(fn($t) => $t->status === 'todo' && $t->is_locked);
    $inReview   = $tasks->whereIn('status', ['review', 'pending_approval']);
    $completed  = $tasks->where('status', 'completed');
@endphp

{{-- ══════════════════════════════════════════════
     1 · PAGE HEADER & SCOPE SWITCHER
     ══════════════════════════════════════════════ --}}
<div class="designer-header">
    <div class="designer-header-left">
        <div class="designer-date">
            <span>{{ ($scope ?? 'my') === 'all' ? 'Team Workspace' : 'Personal Workspace' }}</span>
        </div>
        <h1 class="designer-greeting">
            {{ ($scope ?? 'my') === 'all' ? 'All Team Tasks' : 'My Tasks' }}
            <span style="font-size:14px;color:var(--dz-text-muted);font-weight:600;margin-left:4px">
                ({{ $tasks->count() }})
            </span>
        </h1>
    </div>

    <div class="designer-actions">
        {{-- Scope Toggle --}}
        <div style="display:inline-flex;background:var(--dz-surface);border:1px solid var(--dz-border);border-radius:var(--dz-radius-sm);padding:3px;gap:3px">
            <a href="{{ route('designer.tasks', array_merge(request()->except('scope'), ['scope' => 'my'])) }}" 
               class="task-tab-btn {{ ($scope ?? 'my') === 'my' ? 'active' : '' }}">
                <i class="fa-solid fa-user-check"></i> My Tasks
            </a>
            <a href="{{ route('designer.tasks', array_merge(request()->except('scope'), ['scope' => 'all'])) }}" 
               class="task-tab-btn {{ ($scope ?? 'my') === 'all' ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> All Team
            </a>
        </div>

        <a href="{{ route('designer.urgent-task') }}" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Urgent Task
        </a>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     2 · VISUAL STATUS TABS & VIEW TOGGLE
     ══════════════════════════════════════════════ --}}
<div class="task-tabs-wrap">
    {{-- Status Filter Tabs --}}
    <div class="task-tabs" id="statusFilterTabs">
        <button type="button" class="task-tab-btn active" data-status-filter="all">
            All <span class="task-tab-count">{{ $tasks->count() }}</span>
        </button>
        <button type="button" class="task-tab-btn" data-status-filter="inprogress">
            <span class="ds-pulse-dot" style="width:6px;height:6px;background:var(--dz-blue)"></span>
            In Progress <span class="task-tab-count">{{ $inProgress->count() }}</span>
        </button>
        <button type="button" class="task-tab-btn" data-status-filter="todo">
            Ready <span class="task-tab-count">{{ $todo->count() }}</span>
        </button>
        <button type="button" class="task-tab-btn" data-status-filter="review">
            In Review <span class="task-tab-count">{{ $inReview->count() }}</span>
        </button>
        <button type="button" class="task-tab-btn" data-status-filter="completed">
            Completed <span class="task-tab-count">{{ $completed->count() }}</span>
        </button>
    </div>

    {{-- View Mode Toggle (Grid vs List) --}}
    <div class="view-toggle">
        <button type="button" id="gridModeBtn" class="view-toggle-btn active" title="Visual Cards View" onclick="setTaskViewMode('grid')">
            <i class="fa-solid fa-grip"></i>
        </button>
        <button type="button" id="listModeBtn" class="view-toggle-btn" title="Compact List View" onclick="setTaskViewMode('list')">
            <i class="fa-solid fa-list-ul"></i>
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     3 · MINIMAL SEARCH & CLIENT FILTER BAR
     ══════════════════════════════════════════════ --}}
<form id="designerTaskFilters" method="GET" action="{{ route('designer.tasks') }}" class="filter-bar" style="margin-bottom:24px">
    <input type="hidden" name="scope" value="{{ $scope ?? 'my' }}">
    <div style="flex:1;min-width:220px;position:relative">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--dz-text-muted);pointer-events:none"></i>
        <input type="text" name="search" placeholder="Search tasks, briefs, clients..." value="{{ request('search') }}" class="filter-input" style="width:100%;padding-left:36px !important" autocomplete="off">
    </div>

    <div style="min-width:180px">
        <x-client-select :clients="$clients" name="client_id" value="{{ request('client_id') }}" placeholder="All Clients" class="filter-input" />
    </div>

    @if(($scope ?? 'my') === 'all')
    <select name="assigned_to" class="filter-input" style="min-width:160px">
        <option value="">All Designers</option>
        @foreach($designers ?? [] as $d)
            <option value="{{ $d->id }}" {{ request('assigned_to') == $d->id ? 'selected' : '' }}>
                {{ $d->name }}
            </option>
        @endforeach
    </select>
    @endif

    <select name="status" class="filter-input" style="min-width:140px">
        <option value="">All Statuses</option>
        <option value="inprogress" {{ request('status') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
        <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>Ready to Start</option>
        <option value="review" {{ request('status') == 'review' ? 'selected' : '' }}>In Review</option>
        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
    </select>

    @if(request()->hasAny(['search', 'client_id', 'creator_id', 'assigned_to', 'status']))
        <a href="{{ route('designer.tasks', ['scope' => $scope ?? 'my']) }}" class="btn-sec" style="color:var(--dz-red);border-color:var(--dz-red-border)">
            <i class="fa-solid fa-xmark"></i> Clear
        </a>
    @endif
</form>

{{-- ══════════════════════════════════════════════
     4 · VISUAL TASK CONTAINER
     ══════════════════════════════════════════════ --}}
<div id="designerTasksContent">

    {{-- Visual Grid Mode (Cards) --}}
    <div class="task-grid" id="taskCardsGrid">
        @forelse($tasks as $task)
            <a href="{{ route('designer.tasks.show', $task) }}" 
               class="task-card task-item-container" 
               data-status="{{ $task->status === 'pending_approval' ? 'review' : $task->status }}">
                
                {{-- Card Header: Client & Status --}}
                <div class="task-card-header">
                    <div class="task-card-client">
                        <x-client-branding :client="$task->client" size="26px" radius="6px" fit="contain" />
                        <span class="task-card-client-name">{{ $task->client->name }}</span>
                    </div>
                    <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                </div>

                {{-- Card Body: Title & Visual Badges --}}
                <div>
                    <h3 class="task-card-title">{{ $task->title }}</h3>
                    <div class="task-card-chips">
                        <span class="tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>

                        @if(is_array($task->platform))
                            @foreach($task->platform as $plat)
                                <span class="platform-pill">
                                    @if(in_array($plat, ['instagram', 'facebook', 'linkedin', 'twitter', 'whatsapp']))
                                        <i class="fa-brands fa-{{ $plat === 'twitter' ? 'x-twitter' : $plat }}"></i>
                                    @endif
                                    {{ ucfirst($plat) }}
                                </span>
                            @endforeach
                        @elseif($task->platform)
                            <span class="platform-pill">{{ ucfirst($task->platform) }}</span>
                        @endif

                        @if($task->revision_count > 0)
                            <span class="tag tag-red">
                                <i class="fa-solid fa-rotate-left"></i> Rev #{{ $task->revision_count }}
                            </span>
                        @endif

                        @if($task->priority === 'urgent')
                            <span class="tag tag-red">URGENT</span>
                        @endif
                    </div>
                </div>

                {{-- Card Footer: Due Date & Action Arrow --}}
                <div class="task-card-footer">
                    <div class="task-card-due {{ $task->isOverdue() ? 'is-overdue' : '' }}">
                        <i class="fa-solid fa-calendar-day"></i>
                        <span>{{ $task->deadline ? $task->deadline->format('d M') : 'No deadline' }}</span>
                    </div>
                    <span class="task-card-action">
                        Open <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </div>
            </a>
        @empty
            <div style="grid-column:1/-1;text-align:center;padding:56px 20px;background:var(--dz-surface);border:1px solid var(--dz-border);border-radius:var(--dz-radius)">
                <i class="fa-solid fa-inbox" style="font-size:32px;color:var(--dz-text-muted);display:block;margin-bottom:10px"></i>
                <div style="font-size:15px;font-weight:700;color:var(--dz-text-main)">No tasks found</div>
                <div style="font-size:12.5px;color:var(--dz-text-muted);margin-top:4px">No deliverables match your current filter selection</div>
            </div>
        @endforelse
    </div>

    {{-- Compact List Mode (Rows) --}}
    <div class="task-list-view" id="taskListRows" style="display:none">
        @forelse($tasks as $task)
            <a href="{{ route('designer.tasks.show', $task) }}" 
               class="task-list-row task-item-container" 
               data-status="{{ $task->status === 'pending_approval' ? 'review' : $task->status }}">
                
                <x-client-branding :client="$task->client" size="28px" radius="6px" fit="contain" />
                
                <div class="task-list-title-wrap">
                    <div style="display:flex;align-items:center;gap:6px">
                        <span class="task-list-title">{{ $task->title }}</span>
                        @if($task->priority === 'urgent')
                            <span class="tag tag-red" style="font-size:9.5px;padding:1px 5px">URGENT</span>
                        @endif
                        @if($task->revision_count > 0)
                            <span class="tag tag-red" style="font-size:9.5px;padding:1px 5px">#{{ $task->revision_count }}</span>
                        @endif
                    </div>
                    <div class="task-list-sub">
                        <strong>{{ $task->client->name }}</strong>
                        <span>·</span>
                        <span class="tag" style="font-size:10px;padding:1px 6px">{{ ucfirst($task->type) }}</span>
                    </div>
                </div>

                <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                
                <div style="font-size:12px;color:{{ $task->isOverdue() ? 'var(--dz-red)' : 'var(--dz-text-muted)' }};white-space:nowrap;font-weight:600">
                    <i class="fa-solid fa-calendar-day"></i> {{ $task->deadline?->format('d M') ?? '—' }}
                </div>

                <i class="fa-solid fa-chevron-right task-list-arrow"></i>
            </a>
        @empty
            <div style="text-align:center;padding:56px 20px;background:var(--dz-surface);border:1px solid var(--dz-border);border-radius:var(--dz-radius)">
                <i class="fa-solid fa-inbox" style="font-size:32px;color:var(--dz-text-muted);display:block;margin-bottom:10px"></i>
                <div style="font-size:15px;font-weight:700;color:var(--dz-text-main)">No tasks found</div>
            </div>
        @endforelse
    </div>

</div>

<script>
// View Mode Switching (Grid vs List)
function setTaskViewMode(mode) {
    const grid = document.getElementById('taskCardsGrid');
    const list = document.getElementById('taskListRows');
    const gridBtn = document.getElementById('gridModeBtn');
    const listBtn = document.getElementById('listModeBtn');

    if (mode === 'list') {
        if (grid) grid.style.display = 'none';
        if (list) list.style.display = 'flex';
        gridBtn?.classList.remove('active');
        listBtn?.classList.add('active');
        localStorage.setItem('designer-task-view', 'list');
    } else {
        if (grid) grid.style.display = 'grid';
        if (list) list.style.display = 'none';
        gridBtn?.classList.add('active');
        listBtn?.classList.remove('active');
        localStorage.setItem('designer-task-view', 'grid');
    }
}

// Restore user view preference
(function() {
    const saved = localStorage.getItem('designer-task-view');
    if (saved === 'list') setTaskViewMode('list');
})();

// Quick Status Filter Tabs (Instant DOM filtering)
document.querySelectorAll('#statusFilterTabs button').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#statusFilterTabs button').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const filter = this.getAttribute('data-status-filter');
        document.querySelectorAll('.task-item-container').forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            if (filter === 'all' || cardStatus === filter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
</script>
@endsection
