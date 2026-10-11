@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/pages/designer-theme.css') }}?v=3.0" rel="stylesheet">
@endpush

@section('content')
@php
    $inProgress    = $tasks->where('status', 'inprogress')->where('is_paused', false);
    $paused        = $tasks->where('is_paused', true);
    $todo          = $tasks->where('status', 'todo');
    $locked        = collect();
    $inReview      = $tasks->whereIn('status', ['review', 'pending_approval']);
    $completed     = $tasks->where('status', 'completed');
    
    // Streamlined 3-Status Grouping
    $activeCount   = $inProgress->count() + $inReview->count();
    $queueCount    = $todo->count();
    $doneCount     = $completed->count();
    $allCount      = $tasks->count();

    $requestedStatus = request('status', '');
    $defaultFilterTab = 'all';
    if (in_array($requestedStatus, ['inprogress', 'review'])) {
        $defaultFilterTab = 'active';
    } elseif ($requestedStatus === 'todo') {
        $defaultFilterTab = 'queue';
    } elseif ($requestedStatus === 'completed') {
        $defaultFilterTab = 'done';
    }
@endphp

{{-- ══════════════════════════════════════════════
     1 · PAGE HEADER & ACTIONS
     ══════════════════════════════════════════════ --}}
<div class="designer-header">
    <div class="designer-header-left">
        <div class="designer-date">
            <i class="fa-solid fa-shapes" style="font-size:11px"></i>
            <span>Personal Workspace</span>
        </div>
        <h1 class="designer-greeting">
            My Tasks
            <span style="font-size:14px;color:var(--dz-text-muted);font-weight:600;margin-left:4px">
                ({{ $tasks->count() }})
            </span>
        </h1>
    </div>

    <div class="designer-actions">
        <a href="{{ route('designer.urgent-task') }}" class="btn-action-urgent" title="Create an urgent task">
            <i class="fa-solid fa-bolt" style="font-size:11px"></i> Urgent Task
        </a>

        <button type="button" class="btn-action-primary" onclick="openSelfTaskModal({ defaultType: 'logo' })" title="Create a new task">
            <i class="fa-solid fa-plus" style="font-size:11px"></i> Create Task
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     2 · STREAMLINED 3-STATUS TABS & VIEW CONTROLS
     ══════════════════════════════════════════════ --}}
<div class="task-tabs-wrap">
    {{-- 3-Status Streamlined Tabs (Active / Queue / Done / All) --}}
    <div class="task-tabs" id="statusFilterTabs">
        <button type="button" class="task-tab-btn {{ $defaultFilterTab === 'all' ? 'active' : '' }}" data-status-filter="all">
            All <span class="task-tab-count">{{ $allCount }}</span>
        </button>
        <button type="button" class="task-tab-btn {{ $defaultFilterTab === 'active' ? 'active' : '' }}" data-status-filter="active">
            <span class="ds-pulse-dot" style="width:6px;height:6px"></span>
            Active <span class="task-tab-count">{{ $activeCount }}</span>
        </button>
        <button type="button" class="task-tab-btn {{ $defaultFilterTab === 'queue' ? 'active' : '' }}" data-status-filter="queue">
            Queue <span class="task-tab-count">{{ $queueCount }}</span>
        </button>
        <button type="button" class="task-tab-btn {{ $defaultFilterTab === 'done' ? 'active' : '' }}" data-status-filter="done">
            Done <span class="task-tab-count">{{ $doneCount }}</span>
        </button>
    </div>

    {{-- View Mode Toggle (Grid vs List) --}}
    <div class="view-toggle">
        <button type="button" id="gridModeBtn" class="view-toggle-btn active" title="Visual Cards View" onclick="setTaskViewMode('grid')">
            <i class="fa-solid fa-grip"></i>
        </button>
        <button type="button" id="listModeBtn" class="view-toggle-btn" title="Compact Table List" onclick="setTaskViewMode('list')">
            <i class="fa-solid fa-list-ul"></i>
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     3 · UNIFIED MINIMAL FILTER BAR
     ══════════════════════════════════════════════ --}}
<form id="designerTaskFilters" method="GET" action="{{ route('designer.tasks') }}" class="filter-bar">
    <div style="flex:1;min-width:240px;position:relative">
        <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--dz-text-muted);pointer-events:none"></i>
        <input type="text" name="search" placeholder="Search tasks, briefs, clients..." value="{{ request('search') }}" class="filter-input" style="width:100%;padding-left:34px !important" autocomplete="off">
    </div>

    <div style="min-width:180px">
        <x-client-select :clients="$clients" name="client_id" value="{{ request('client_id') }}" placeholder="All Clients" class="filter-input" />
    </div>

    @if(request()->hasAny(['search', 'client_id', 'creator_id', 'status']))
        <a href="{{ route('designer.tasks') }}" class="btn-sec" style="color:#DC2626;border-color:rgba(239,68,68,0.25)">
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
            @php
                $statusSlug = $task->status === 'pending_approval' ? 'review' : $task->status;
                $statusGroup = in_array($statusSlug, ['inprogress', 'review']) ? 'active' : ($statusSlug === 'todo' ? 'queue' : 'done');
            @endphp
            <a href="{{ route('designer.tasks.show', $task) }}" 
               class="task-card task-item-container" 
               data-status="{{ $statusSlug }}"
               data-status-group="{{ $statusGroup }}">
                
                {{-- Card Header: Client & Status --}}
                <div class="task-card-header">
                    <div class="task-card-client">
                        <x-client-branding :client="$task->client" size="24px" radius="5px" fit="contain" />
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
                        <i class="fa-regular fa-calendar" style="font-size:11px"></i>
                        <span>{{ $task->deadline ? $task->deadline->format('d M') : 'No deadline' }}</span>
                    </div>
                    <span class="task-card-action">
                        Open <i class="fa-solid fa-arrow-right" style="font-size:10px"></i>
                    </span>
                </div>
            </a>
        @empty
            <div style="grid-column:1/-1;text-align:center;padding:56px 20px;background:var(--dz-surface);border:1px solid var(--dz-border);border-radius:var(--dz-radius)">
                <i class="fa-solid fa-inbox" style="font-size:28px;color:var(--dz-text-muted);display:block;margin-bottom:10px"></i>
                <div style="font-size:15px;font-weight:700;color:var(--dz-text-main)">No tasks found</div>
                <div style="font-size:12.5px;color:var(--dz-text-muted);margin-top:4px">No deliverables match your current filter selection</div>
            </div>
        @endforelse
    </div>

    {{-- Compact List Mode (Rows) --}}
    <div class="task-list-view" id="taskListRows" style="display:none">
        @forelse($tasks as $task)
            @php
                $statusSlug = $task->status === 'pending_approval' ? 'review' : $task->status;
                $statusGroup = in_array($statusSlug, ['inprogress', 'review']) ? 'active' : ($statusSlug === 'todo' ? 'queue' : 'done');
            @endphp
            <a href="{{ route('designer.tasks.show', $task) }}" 
               class="task-list-row task-item-container" 
               data-status="{{ $statusSlug }}"
               data-status-group="{{ $statusGroup }}">
                
                <x-client-branding :client="$task->client" size="26px" radius="5px" fit="contain" />
                
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
                        <strong style="color:var(--dz-text-sub)">{{ $task->client->name }}</strong>
                        <span>·</span>
                        <span class="tag" style="font-size:10px;padding:1px 6px">{{ ucfirst($task->type) }}</span>
                    </div>
                </div>

                <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                
                <div style="font-size:12px;color:{{ $task->isOverdue() ? '#DC2626' : 'var(--dz-text-muted)' }};white-space:nowrap;font-weight:{{ $task->isOverdue() ? '700' : '600' }}">
                    <i class="fa-regular fa-calendar" style="font-size:11px"></i> {{ $task->deadline?->format('d M') ?? '—' }}
                </div>

                <i class="fa-solid fa-chevron-right task-list-arrow"></i>
            </a>
        @empty
            <div style="text-align:center;padding:56px 20px;background:var(--dz-surface);border:1px solid var(--dz-border);border-radius:var(--dz-radius)">
                <i class="fa-solid fa-inbox" style="font-size:28px;color:var(--dz-text-muted);display:block;margin-bottom:10px"></i>
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

// Streamlined 3-Status Tab Instant DOM filtering (Active / Queue / Done / All)
document.querySelectorAll('#statusFilterTabs button').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#statusFilterTabs button').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const filter = this.getAttribute('data-status-filter');
        document.querySelectorAll('.task-item-container').forEach(card => {
            const group = card.getAttribute('data-status-group');
            if (filter === 'all' || group === filter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Trigger initial filter tab if set
(function() {
    const activeBtn = document.querySelector('#statusFilterTabs button.active');
    if (activeBtn && activeBtn.getAttribute('data-status-filter') !== 'all') {
        activeBtn.click();
    }
})();
</script>
@endsection
