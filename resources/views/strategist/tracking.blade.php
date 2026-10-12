@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/strategist-tracking.css') }}?v={{ time() }}">
<style>
/* Additional helper styles for modal overlays & inline drawer actions */
.trk-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 10000;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.trk-modal-box {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    width: 100%;
    max-width: 480px;
    padding: 24px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    position: relative;
    animation: trkModalIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes trkModalIn {
    from { opacity: 0; transform: scale(0.96) translateY(-10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.trk-modal-close {
    position: absolute;
    right: 18px;
    top: 16px;
    font-size: 20px;
    color: var(--text3);
    cursor: pointer;
    line-height: 1;
    transition: color 0.15s;
}
.trk-modal-close:hover { color: var(--text); }
.trk-modal-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}
.trk-modal-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(99, 102, 241, 0.12);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
}
.trk-modal-title { font-size: 17px; font-weight: 800; color: var(--text); }
.trk-modal-sub { font-size: 12px; color: var(--text3); margin-top: 2px; }
.trk-modal-label { font-size: 11px; font-weight: 700; color: var(--text2); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; display: block; }
.trk-modal-select, .trk-modal-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: 9px;
    background: var(--card2);
    font-size: 13px;
    color: var(--text);
    font-family: inherit;
    margin-bottom: 16px;
    box-sizing: border-box;
}
.trk-modal-select:focus, .trk-modal-textarea:focus { outline: none; border-color: var(--primary); }
.trk-modal-actions { display: flex; gap: 8px; justify-content: flex-end; }
.trk-modal-btn-ghost {
    padding: 9px 16px;
    border-radius: 8px;
    background: var(--card2);
    color: var(--text2);
    border: 1px solid var(--border);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}
.trk-modal-btn-primary {
    padding: 9px 18px;
    border-radius: 8px;
    background: var(--primary);
    color: #fff;
    border: none;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="trk-header">
    <div class="trk-header-left">
        <div class="trk-title">
            <i class="fa-solid fa-list-check" style="color:var(--primary)"></i>
            Task Tracking &amp; Situational Command
        </div>
        <div class="trk-subtitle" id="trackingSubtitle">
            Live production oversight &mdash; {{ $tasks->total() }} total task{{ $tasks->total() !== 1 ? 's' : '' }}
        </div>
    </div>
    <div class="trk-header-right" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        {{-- Scope Toggle --}}
        <div class="trk-scope-toggle" style="display:inline-flex;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:4px;gap:4px;box-shadow:var(--shadow-sm)">
            <a href="{{ route('strategist.tracking', array_merge(request()->except('scope'), ['scope' => 'all'])) }}" 
               style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s; {{ ($scope ?? 'all') === 'all' ? 'background:var(--primary);color:#fff;box-shadow:0 2px 8px rgba(99,102,241,0.35);' : 'color:var(--text2);background:transparent;' }}">
                <i class="fa-solid fa-users"></i> All Tasks
            </a>
            <a href="{{ route('strategist.tracking', array_merge(request()->except('scope'), ['scope' => 'my'])) }}" 
               style="padding:6px 14px;border-radius:8px;font-size:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s; {{ ($scope ?? 'all') === 'my' ? 'background:var(--primary);color:#fff;box-shadow:0 2px 8px rgba(99,102,241,0.35);' : 'color:var(--text2);background:transparent;' }}">
                <i class="fa-solid fa-user-pen"></i> My Created
            </a>
        </div>

        {{-- View Mode Toggle (Dense Table vs Card Grid) --}}
        <div class="trk-view-mode-toggle">
            <button type="button" class="trk-view-btn is-active" id="trkViewTableBtn" onclick="setTrackingViewMode('table')" title="Dense Table View">
                <i class="fa-solid fa-table-list"></i> Table
            </button>
            <button type="button" class="trk-view-btn" id="trkViewGridBtn" onclick="setTrackingViewMode('grid')" title="Visual Card Grid">
                <i class="fa-solid fa-table-cells-large"></i> Cards
            </button>
        </div>

        <a href="{{ route('strategist.calendar') }}" class="trk-header-btn trk-btn-ghost"><i class="fa-regular fa-calendar"></i> Calendar</a>
        <a href="{{ route('strategist.create-task') }}" class="trk-header-btn trk-btn-primary"><i class="fa-solid fa-plus"></i> New Task</a>
    </div>
</div>

{{-- Summary Stats Row (Enhanced with In Revision card) --}}
<div class="trk-stats-row">
    <a href="javascript:void(0)" onclick="filterByStatus('')" class="trk-stat {{ !request('status') || request('status') === 'all' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--blue-dim);color:var(--blue)"><i class="fa-solid fa-layer-group"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val">{{ $totalCount }}</div>
            <div class="trk-stat-label">Total</div>
        </div>
    </a>
    <a href="javascript:void(0)" onclick="filterByStatus('todo')" class="trk-stat {{ request('status') === 'todo' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--card2);color:var(--text3)"><i class="fa-solid fa-clipboard-list"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val">{{ $todoCount }}</div>
            <div class="trk-stat-label">To Do</div>
        </div>
    </a>
    <a href="javascript:void(0)" onclick="filterByStatus('inprogress')" class="trk-stat {{ request('status') === 'inprogress' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--blue-dim);color:var(--blue)"><i class="fa-solid fa-person-running"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val">{{ $inProgressCount }}</div>
            <div class="trk-stat-label">In Progress</div>
        </div>
    </a>
    <a href="javascript:void(0)" onclick="filterByStatus('review')" class="trk-stat {{ request('status') === 'review' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--yellow-dim);color:var(--yellow)"><i class="fa-solid fa-flask"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val">{{ $reviewCount }}</div>
            <div class="trk-stat-label">Review</div>
        </div>
    </a>
    {{-- In Revision Quick Filter --}}
    <a href="javascript:void(0)" onclick="filterByStatus('in_revision')" class="trk-stat trk-stat-revision {{ request('status') === 'in_revision' ? 'trk-stat-active' : '' }}" title="Tasks actively being revised by designer">
        <div class="trk-stat-icon" style="background:rgba(245,158,11,0.12);color:#D97706"><i class="fa-solid fa-rotate-left"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val" style="color:#D97706">{{ $inRevisionCount ?? 0 }}</div>
            <div class="trk-stat-label">In Revision</div>
        </div>
    </a>
    <a href="javascript:void(0)" onclick="filterByStatus('completed')" class="trk-stat {{ request('status') === 'completed' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--teal-dim);color:var(--teal)"><i class="fa-solid fa-circle-check"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val">{{ $completedCount }}</div>
            <div class="trk-stat-label">Done</div>
        </div>
    </a>
    @if(($overdueCount ?? 0) > 0)
    <a href="javascript:void(0)" onclick="filterByStatus('overdue')" class="trk-stat trk-stat-danger {{ request('status') === 'overdue' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--red-dim);color:var(--red)"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val" style="color:var(--red)">{{ $overdueCount }}</div>
            <div class="trk-stat-label">Overdue</div>
        </div>
    </a>
    @endif
</div>

{{-- Filter Bar --}}
<div class="trk-filter-bar">
    <form method="GET" action="{{ route('strategist.tracking') }}" class="trk-filter-form" id="trkFilterForm">
        <input type="hidden" name="scope" value="{{ $scope ?? 'all' }}">
        <div class="trk-search-wrap">
            <i class="fa-solid fa-magnifying-glass trk-search-icon"></i>
            <input type="text" name="search" id="trkSearchInput" placeholder="Search tasks, copy, or brief in real-time…" value="{{ request('search') }}" class="trk-search-input" autocomplete="off">
        </div>
        <div class="trk-filter-selects">
            <select name="sort" class="trk-select" onchange="submitFilterFormAjax()">
                <option value="deadline_asc" {{ request('sort', 'deadline_asc') == 'deadline_asc' ? 'selected' : '' }}>Deadline ↑</option>
                <option value="deadline_desc" {{ request('sort') == 'deadline_desc' ? 'selected' : '' }}>Deadline ↓</option>
                <option value="post_date_asc" {{ request('sort') == 'post_date_asc' ? 'selected' : '' }}>Post Date ↑</option>
                <option value="post_date_desc" {{ request('sort') == 'post_date_desc' ? 'selected' : '' }}>Post Date ↓</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
            </select>
            <select name="creator_id" class="trk-select" onchange="submitFilterFormAjax()">
                <option value="">All Strategists</option>
                @foreach($strategists ?? [] as $st)
                    <option value="{{ $st->id }}" {{ request('creator_id') == $st->id ? 'selected' : '' }}>
                        ✍️ {{ $st->name }}
                    </option>
                @endforeach
            </select>
            <select name="assigned_to" class="trk-select" onchange="submitFilterFormAjax()">
                <option value="">All Assignees</option>
                @foreach($designers ?? [] as $ds)
                    <option value="{{ $ds->id }}" {{ request('assigned_to') == $ds->id ? 'selected' : '' }}>
                        🎨 {{ $ds->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="trk-select" id="trkStatusSelect" onchange="submitFilterFormAjax()">
                <option value="all">All Status</option>
                <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                <option value="inprogress" {{ request('status') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                <option value="in_revision" {{ request('status') == 'in_revision' ? 'selected' : '' }}>In Revision</option>
                <option value="review" {{ request('status') == 'review' ? 'selected' : '' }}>Review</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
            <div class="trk-select-wrap" style="min-width:180px">
                <x-client-select :clients="$clients" name="client" :value="request('client')" placeholder="All Clients" width="100%" />
            </div>
            <select name="type" class="trk-select" onchange="submitFilterFormAjax()">
                <option value="all">All Types</option>
                <option value="reel" {{ request('type') == 'reel' ? 'selected' : '' }}>Reel</option>
                <option value="post" {{ request('type') == 'post' ? 'selected' : '' }}>Post</option>
                <option value="story" {{ request('type') == 'story' ? 'selected' : '' }}>Story</option>
                <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
                <option value="carousel" {{ request('type') == 'carousel' ? 'selected' : '' }}>Carousel</option>
                <option value="brochure" {{ request('type') == 'brochure' ? 'selected' : '' }}>Brochure</option>
                <option value="banner" {{ request('type') == 'banner' ? 'selected' : '' }}>Banner</option>
                <option value="flyer" {{ request('type') == 'flyer' ? 'selected' : '' }}>Flyer</option>
                <option value="website" {{ request('type') == 'website' ? 'selected' : '' }}>Website</option>
                <option value="software" {{ request('type') == 'software' ? 'selected' : '' }}>Software</option>
                <option value="maintenance" {{ request('type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="others" {{ request('type') == 'others' ? 'selected' : '' }}>Others</option>
            </select>
            <select name="per_page" class="trk-select trk-select-sm" onchange="submitFilterFormAjax()">
                <option value="25" {{ request('per_page', '25') == '25' ? 'selected' : '' }}>25 / page</option>
                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 / page</option>
                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 / page</option>
            </select>
        </div>
        <div class="trk-date-range">
            <div class="trk-date-field">
                <label class="trk-date-label">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="trk-date-input" onchange="submitFilterFormAjax()">
            </div>
            <div class="trk-date-field">
                <label class="trk-date-label">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="trk-date-input" onchange="submitFilterFormAjax()">
            </div>
        </div>
        <button type="submit" class="trk-filter-btn"><i class="fa-solid fa-filter"></i> Filter</button>
        @if(request()->hasAny(['search', 'status', 'client', 'creator_id', 'assigned_to', 'type', 'date_from', 'date_to', 'sort', 'per_page']))
            <a href="javascript:void(0)" onclick="clearAllFilters()" class="trk-clear-btn" id="trkClearBtn"><i class="fa-solid fa-xmark"></i> Clear</a>
        @endif
    </form>
</div>

{{-- Main Container (Switches between Table and Card Grid) --}}
<div class="trk-table-card" id="trkMainContainer">
    {{-- TABLE VIEW --}}
    <div class="trk-table-wrap" id="trkTableView">
        <table class="trk-table" id="trkDataTable">
            <thead>
                <tr>
                    <th style="min-width:240px">Task &amp; Brief</th>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Deadline</th>
                    <th style="width:130px;text-align:center">Actions</th>
                </tr>
            </thead>
            <tbody id="trkTableBody">
                @forelse($tasks as $task)
                    @php
                        $isDevTask = in_array($task->type, ['website', 'software', 'maintenance']);
                        $isTimerRunning = ($task->status === 'inprogress' && !$task->is_paused && !empty($task->started_at));
                        $isClientApproved = $task->isClientApproved();
                    @endphp
                    <tr id="task-row-{{ $task->id }}" class="trk-data-row {{ (string) request('task') === (string) $task->id ? 'trk-row-highlight' : '' }}" onclick="openTaskInspector({{ $task->id }})" data-task-id="{{ $task->id }}" data-search-text="{{ strtolower($task->title . ' ' . ($task->client?->name ?? '') . ' ' . ($task->assignee?->name ?? '') . ' ' . $task->type) }}">
                        <td>
                            <div class="trk-task-title">
                                <span>{{ $task->title }}</span>
                                @if($isTimerRunning)
                                    <span class="trk-live-pulse" title="Designer is working on this task right now!">
                                        <span class="trk-pulse-dot"></span> Active Now
                                    </span>
                                @endif
                            </div>
                            <div class="trk-task-meta">
                                @if($isDevTask)
                                    @if($task->tech_stack)
                                        <span><i class="fas fa-code" style="font-size:9px;opacity:.7"></i> {{ $task->tech_stack }}</span>
                                    @endif
                                    @if($task->dev_deadline)
                                        <span><i class="fas fa-laptop-code" style="font-size:9px;opacity:.7"></i> Dev: {{ $task->dev_deadline->format('d M') }}</span>
                                    @endif
                                @else
                                    @if(is_array($task->platform))
                                        <span>{{ implode(', ', array_map('ucfirst', $task->platform)) }}</span>
                                    @elseif($task->platform)
                                        <span>{{ ucfirst($task->platform) }}</span>
                                    @endif
                                @endif

                                @if($task->comments_count > 0)
                                    <span class="trk-comment-badge"><i class="fas fa-comment" style="font-size:10px"></i> {{ $task->comments_count }}</span>
                                @endif

                                @if($task->creator)
                                    <span class="trk-creator-pill">
                                        <i class="fa-solid fa-user-pen" style="font-size:9px"></i> By: {{ $task->creator->name }}
                                    </span>
                                @endif

                                @if($task->requires_client_approval)
                                    <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:6px;background:{{ $isClientApproved ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.1)' }};color:{{ $isClientApproved ? '#059669' : '#EF4444' }};border:1px solid {{ $isClientApproved ? 'rgba(16,185,129,0.25)' : 'rgba(239,68,68,0.2)' }}">
                                        <i class="fa-solid {{ $isClientApproved ? 'fa-check' : 'fa-clock' }}" style="font-size:8px"></i> Client: {{ $isClientApproved ? 'Yes' : 'Pending' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="trk-client-cell">
                                <x-client-branding :client="$task->client" size="26px" radius="6px" :showName="true" fit="contain" />
                            </div>
                        </td>
                        <td><span class="tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span></td>
                        <td>
                            @if($task->assignee)
                                <div class="trk-assignee-cell">
                                    <div class="av-sm" style="background:{{ $task->assignee->avatar_color ?? '#555' }}">{{ $task->assignee->initial }}</div>
                                    <span>{{ $task->assignee->name }}</span>
                                </div>
                            @else
                                <span class="trk-unassigned"><i class="fa-solid fa-circle-exclamation"></i> Unassigned</span>
                            @endif
                        </td>
                        <td>
                            <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                            @if($task->revision_count > 0)
                                <span class="wf-rev-badge" style="animation:none;margin-left:4px" title="{{ $task->revision_count }} revision{{ $task->revision_count > 1 ? 's' : '' }} requested">
                                    <i class="fa-solid fa-rotate-left"></i> Rev #{{ $task->revision_count }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($task->priority === 'urgent')
                                <span class="trk-priority trk-prio-urgent">Urgent</span>
                            @elseif($task->priority === 'high')
                                <span class="trk-priority trk-prio-high">High</span>
                            @else
                                <span class="trk-priority trk-prio-normal">Normal</span>
                            @endif
                        </td>
                        <td>
                            @if($task->deadline)
                                <div class="trk-deadline {{ $task->isOverdue() ? 'trk-deadline-overdue' : '' }}">
                                    <i class="fa-regular {{ $task->isOverdue() ? 'fa-clock' : 'fa-calendar' }}"></i>
                                    {{ $task->deadline->format('d M Y') }}
                                    @if($task->isOverdue()) <span class="trk-overdue-tag">Overdue</span> @endif
                                </div>
                            @else
                                <span class="trk-no-deadline">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="trk-actions" onclick="event.stopPropagation()">
                                <button type="button" class="trk-action-btn trk-action-view" title="Quick Drawer Inspector" onclick="openTaskInspector({{ $task->id }})">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <a href="{{ route('strategist.tasks.show', $task) }}" class="trk-action-btn" title="Open Full Task Details">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                                <a href="{{ route('strategist.tasks.edit', $task) }}" class="trk-action-btn" title="Edit Task" data-no-loader>
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <button type="button" class="trk-action-btn" title="Reassign" onclick="openReassignModal({{ $task->id }}, '{{ addslashes($task->title) }}', {{ $task->assigned_to ?? 'null' }})">
                                    <i class="fa-solid fa-arrows-rotate"></i>
                                </button>
                                @if($task->assignee && !in_array($task->status, ['completed', 'published']))
                                    <button type="button" class="trk-action-btn trk-action-followup" title="Nudge Designer via Live Alert" onclick="nudgeTask({{ $task->id }}, '{{ addslashes($task->assignee->name) }}', event)">
                                        <i class="fa-solid fa-bell"></i>
                                    </button>
                                @endif
                                <button type="button" class="trk-action-btn trk-action-delete" title="Delete Task" onclick="deleteTaskAjax({{ $task->id }}, '{{ addslashes($task->title) }}', event)">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="trk-empty">
                            <div class="trk-empty-box">
                                <div class="trk-empty-icon"><i class="fa-solid fa-inbox"></i></div>
                                <div class="trk-empty-title">No tasks found</div>
                                <div class="trk-empty-sub">Try adjusting your filters or create a new task</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- CARD GRID VIEW (Alternative visual layout) --}}
    <div class="trk-cards-grid" id="trkGridView" style="display:none">
        @forelse($tasks as $task)
            @php
                $isTimerRunning = ($task->status === 'inprogress' && !$task->is_paused && !empty($task->started_at));
                $thumb = $task->getPreviewThumbnail();
            @endphp
            <div class="trk-card trk-data-row" onclick="openTaskInspector({{ $task->id }})" data-task-id="{{ $task->id }}" data-search-text="{{ strtolower($task->title . ' ' . ($task->client?->name ?? '') . ' ' . ($task->assignee?->name ?? '') . ' ' . $task->type) }}">
                <div class="trk-card-top">
                    <div style="min-width:0;flex:1">
                        <div class="trk-card-title">{{ $task->title }}</div>
                        <div style="font-size:11.5px;color:var(--text3);display:flex;align-items:center;gap:6px">
                            <span>{{ $task->client?->emoji ?? '🏢' }} {{ $task->client?->name ?? 'Unknown Client' }}</span>
                            @if($task->revision_count > 0)
                                <span class="wf-rev-badge"><i class="fa-solid fa-rotate-left"></i> Rev #{{ $task->revision_count }}</span>
                            @endif
                        </div>
                    </div>
                    @if($isTimerRunning)
                        <span class="trk-live-pulse"><span class="trk-pulse-dot"></span> Active</span>
                    @endif
                </div>

                <div class="trk-card-badge-row">
                    <span class="tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                    <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                    @if($task->priority === 'urgent')
                        <span class="trk-priority trk-prio-urgent">Urgent</span>
                    @elseif($task->priority === 'high')
                        <span class="trk-priority trk-prio-high">High</span>
                    @endif
                </div>

                <div class="trk-card-middle">
                    <div style="display:flex;align-items:center;gap:8px">
                        @if($task->assignee)
                            <div class="av-sm" style="background:{{ $task->assignee->avatar_color ?? '#555' }}">{{ $task->assignee->initial }}</div>
                            <span style="font-size:12px;font-weight:600">{{ $task->assignee->name }}</span>
                        @else
                            <span class="trk-unassigned"><i class="fa-solid fa-circle-exclamation"></i> Unassigned</span>
                        @endif
                    </div>
                    <div style="text-align:right">
                        @if($task->deadline)
                            <div style="font-size:11.5px;font-weight:700;color:{{ $task->isOverdue() ? '#EF4444' : 'var(--text)' }}">
                                <i class="fa-regular fa-calendar"></i> {{ $task->deadline->format('d M') }}
                            </div>
                        @else
                            <span style="font-size:11px;color:var(--text3)">No Deadline</span>
                        @endif
                    </div>
                </div>

                <div class="trk-card-foot" onclick="event.stopPropagation()">
                    <button type="button" class="trk-btn-ghost" style="padding:5px 12px;font-size:11px;border-radius:6px" onclick="openTaskInspector({{ $task->id }})">
                        <i class="fa-solid fa-eye"></i> Inspect
                    </button>
                    <div style="display:flex;gap:4px">
                        @if($task->assignee && !in_array($task->status, ['completed', 'published']))
                            <button type="button" class="trk-action-btn trk-action-followup" title="Nudge Designer" onclick="nudgeTask({{ $task->id }}, '{{ addslashes($task->assignee->name) }}', event)">
                                <i class="fa-solid fa-bell"></i>
                            </button>
                        @endif
                        <a href="{{ route('strategist.tasks.edit', $task) }}" class="trk-action-btn" title="Edit" data-no-loader>
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="trk-empty" style="grid-column:1/-1">
                <div class="trk-empty-box">
                    <div class="trk-empty-icon"><i class="fa-solid fa-inbox"></i></div>
                    <div class="trk-empty-title">No tasks found</div>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination & Footer --}}
    @if($tasks->hasPages())
        <div class="trk-pagination">
            {{ $tasks->links('vendor.pagination.custom') }}
        </div>
    @endif
    @if($tasks->total() > 0)
        <div class="trk-table-footer">
            <span class="trk-table-footer-text">Page {{ $tasks->currentPage() }} of {{ $tasks->lastPage() }} &middot; {{ $tasks->total() }} task{{ $tasks->total() !== 1 ? 's' : '' }}</span>
            <div class="trk-perpage-footer">
                <label class="trk-perpage-label">Show</label>
                <select class="trk-perpage-select" onchange="changePerPage(this.value)">
                    <option value="25" {{ request('per_page', '25') == '25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                </select>
                <label class="trk-perpage-label">per page</label>
            </div>
        </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SLIDE-OVER TASK INSPECTOR DRAWER
     ═══════════════════════════════════════════════════════════════ --}}
<div class="trk-drawer-overlay" id="trkDrawerOverlay" onclick="closeTaskInspector()"></div>
<aside class="trk-drawer" id="trkDrawer" aria-hidden="true">
    {{-- Header --}}
    <div class="trk-drawer-header">
        <div style="min-width:0;flex:1">
            <div id="drawerClientPill" style="font-size:12px;font-weight:700;color:var(--primary);margin-bottom:6px;display:flex;align-items:center;gap:6px">
                <span id="drawerClientEmoji">🏢</span>
                <span id="drawerClientName">Client Name</span>
            </div>
            <h2 id="drawerTaskTitle" style="font-size:18px;font-weight:800;color:var(--text);margin:0 0 8px;line-height:1.3">
                Loading task details...
            </h2>
            <div id="drawerMetaBadges" style="display:flex;gap:6px;flex-wrap:wrap">
                <span id="drawerStatusBadge" class="status">Loading</span>
                <span id="drawerTypeBadge" class="tag">Type</span>
                <span id="drawerPriorityBadge" class="trk-priority">Priority</span>
            </div>
        </div>
        <button type="button" class="trk-drawer-close" onclick="closeTaskInspector()" title="Close Inspector (&times;)">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- Body --}}
    <div class="trk-drawer-body" id="drawerBody">
        {{-- Designer & Timer Status Strip --}}
        <div class="trk-d-card">
            <div class="trk-d-title"><i class="fa-solid fa-stopwatch"></i> Creative Production &amp; Timer Status</div>
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                <div style="display:flex;align-items:center;gap:10px">
                    <div id="drawerAssigneeAvatar" class="av-sm" style="background:#6366f1;width:36px;height:36px;font-size:13px">D</div>
                    <div>
                        <div id="drawerAssigneeName" style="font-size:13px;font-weight:700;color:var(--text)">Designer Name</div>
                        <div id="drawerAssigneeRole" style="font-size:11px;color:var(--text3)">Designer</div>
                    </div>
                </div>
                <div id="drawerTimerBadge" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:10px;background:var(--card);border:1px solid var(--border);font-size:12px;font-weight:700">
                    <span class="trk-pulse-dot" style="display:none" id="drawerPulseDot"></span>
                    <span id="drawerTimerText">Timer idle</span>
                </div>
            </div>
            <div id="drawerScheduleRow" style="display:flex;gap:16px;margin-top:6px;padding-top:10px;border-top:1px solid var(--border);font-size:11.5px;color:var(--text2)">
                <div><strong style="color:var(--text3)">Deadline:</strong> <span id="drawerDeadline">—</span></div>
                <div><strong style="color:var(--text3)">Post Date:</strong> <span id="drawerPostDate">—</span></div>
                <div><strong style="color:var(--text3)">Client Sign-Off:</strong> <span id="drawerClientStatus">—</span></div>
            </div>
        </div>

        {{-- Revision Alert Banner (Shown only if revision_count > 0) --}}
        <div id="drawerRevisionCard" class="trk-d-card" style="display:none;background:rgba(245,158,11,0.06);border-color:rgba(245,158,11,0.3)">
            <div class="trk-d-title" style="color:#D97706"><i class="fa-solid fa-rotate-left"></i> Active Revision Phase (<span id="drawerRevisionCount">0</span>)</div>
            <div id="drawerRevisionNotes" style="font-size:12.5px;color:#92400E;line-height:1.45;background:var(--card);padding:10px 12px;border-radius:8px;border:1px solid rgba(245,158,11,0.25)">
                No revision notes recorded.
            </div>
            <div id="drawerRevisionMeta" style="font-size:11px;color:#B45309;display:flex;justify-content:space-between">
                <span>Requested by: <strong id="drawerRevisionAuthor">—</strong></span>
                <span id="drawerRevisionDate">—</span>
            </div>
        </div>

        {{-- Deliverables Section --}}
        <div class="trk-d-card">
            <div class="trk-d-title" style="justify-content:space-between">
                <span><i class="fa-solid fa-paintbrush"></i> Submitted Deliverables (<span id="drawerDeliverablesCount">0</span>)</span>
            </div>
            <div id="drawerDeliverablesGrid" class="trk-d-media-grid">
                <div style="font-size:12px;color:var(--text3);grid-column:1/-1;padding:12px;text-align:center">No design deliverables submitted yet.</div>
            </div>
        </div>

        {{-- Copywriting & Brief Section --}}
        <div class="trk-d-card">
            <div class="trk-d-title" style="justify-content:space-between">
                <span><i class="fa-solid fa-file-lines"></i> Copywriting &amp; Social Brief</span>
                <button type="button" class="trk-btn-ghost" onclick="copyDrawerCaption()" style="padding:3px 8px;font-size:10.5px;border-radius:6px;gap:4px">
                    <i class="fa-regular fa-copy"></i> Copy Copy &amp; Hashtags
                </button>
            </div>
            <div id="drawerBriefBox" style="font-size:12.5px;color:var(--text2);line-height:1.5;background:var(--card);padding:10px 12px;border-radius:8px;border:1px solid var(--border);max-height:140px;overflow-y:auto">
                No brief provided.
            </div>
            <div id="drawerCopyBox" style="display:none;background:var(--card);padding:10px 12px;border-radius:8px;border:1px solid var(--border)">
                <div style="font-size:11px;font-weight:700;color:var(--text3);margin-bottom:4px">CAPTION &amp; HASHTAGS</div>
                <div id="drawerCaptionText" style="font-size:12px;color:var(--text);white-space:pre-wrap;line-height:1.4"></div>
                <div id="drawerHashtagsText" style="font-size:12px;color:var(--primary);margin-top:6px;font-weight:600"></div>
            </div>
        </div>

        {{-- Discussion & Live Team Comments --}}
        <div class="trk-d-card">
            <div class="trk-d-title"><i class="fa-solid fa-comments"></i> Team Discussion</div>
            <div id="drawerCommentsFeed" style="display:flex;flex-direction:column;gap:10px;max-height:220px;overflow-y:auto;padding-right:4px">
                <div style="font-size:12px;color:var(--text3);text-align:center;padding:10px">No comments yet.</div>
            </div>
            <div style="display:flex;gap:6px;margin-top:8px">
                <input type="text" id="drawerCommentInput" placeholder="Add comment for designer / team..." style="flex:1;padding:8px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card);font-size:12px;color:var(--text)" onkeydown="if(event.key==='Enter') submitDrawerComment()">
                <button type="button" class="trk-filter-btn" style="padding:8px 14px" onclick="submitDrawerComment()"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="trk-drawer-footer">
        <div style="display:flex;gap:6px;flex-wrap:wrap">
            <button type="button" id="drawerNudgeBtn" class="trk-btn-ghost" style="color:#D97706;border-color:rgba(245,158,11,0.3)" onclick="triggerDrawerNudge()">
                <i class="fa-solid fa-bell"></i> Nudge Designer
            </button>
            <button type="button" id="drawerRevisionBtn" class="trk-btn-ghost" style="color:#F97316;border-color:rgba(249,115,22,0.3)" onclick="openDrawerRevisionModal()">
                <i class="fa-solid fa-rotate-left"></i> Request Revision
            </button>
        </div>
        <div style="display:flex;gap:6px">
            <a href="#" id="drawerEditLink" class="trk-btn-ghost" style="font-size:12px" data-no-loader><i class="fa-solid fa-pen"></i> Edit</a>
            <a href="#" id="drawerFullPageLink" class="trk-btn-primary" style="font-size:12px"><i class="fa-solid fa-arrow-up-right-from-square"></i> Full Workspace</a>
        </div>
    </div>
</aside>

{{-- Reassign Modal --}}
<div id="reassignModal" class="trk-modal-overlay" style="display:none">
    <div class="trk-modal-box">
        <span class="trk-modal-close" onclick="closeReassignModal()">&times;</span>
        <div class="trk-modal-header">
            <div class="trk-modal-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
            <div>
                <div class="trk-modal-title">Reassign Task</div>
                <div id="reassignTaskTitle" class="trk-modal-sub"></div>
            </div>
        </div>
        <form id="reassignForm" method="POST">
            @csrf
            @method('PATCH')
            <label class="trk-modal-label">Select Team Member</label>
            <select name="assigned_to" id="reassignSelect" required class="trk-modal-select">
                @foreach($designers as $designer)
                    <option value="{{ $designer->id }}">{{ $designer->name }} ({{ ucfirst($designer->role) }})</option>
                @endforeach
            </select>
            <div class="trk-modal-actions">
                <button type="button" class="trk-modal-btn-ghost" onclick="closeReassignModal()">Cancel</button>
                <button type="submit" class="trk-modal-btn-primary"><i class="fa-solid fa-check"></i> Confirm Reassign</button>
            </div>
        </form>
    </div>
</div>

{{-- Quick Revision Modal from Tracking --}}
<div id="trkRevisionModal" class="trk-modal-overlay" style="display:none">
    <div class="trk-modal-box">
        <span class="trk-modal-close" onclick="closeDrawerRevisionModal()">&times;</span>
        <div class="trk-modal-header">
            <div class="trk-modal-icon" style="background:rgba(245,158,11,0.14);color:#D97706"><i class="fa-solid fa-rotate-left"></i></div>
            <div>
                <div class="trk-modal-title">Request Revision</div>
                <div id="trkRevisionTaskTitle" class="trk-modal-sub"></div>
            </div>
        </div>
        <form id="trkRevisionForm" method="POST" enctype="multipart/form-data" onsubmit="submitRevisionFormAjax(event)">
            @csrf
            @method('PATCH')
            <label class="trk-modal-label">Revision Instructions &amp; Changes Needed</label>
            <textarea name="revision_note" id="trkRevisionNote" required rows="4" class="trk-modal-textarea" placeholder="Detail the exact adjustments required (e.g. fix typo in slide 2, adjust logo size, slow down reel pacing)..."></textarea>
            
            <label class="trk-modal-label">Reference Attachments / Markups (Optional)</label>
            <input type="file" name="reference_files[]" multiple class="trk-modal-select" style="padding:6px;height:auto">

            <div class="trk-modal-actions">
                <button type="button" class="trk-modal-btn-ghost" onclick="closeDrawerRevisionModal()">Cancel</button>
                <button type="submit" class="trk-modal-btn-primary" style="background:#F59E0B"><i class="fa-solid fa-paper-plane"></i> Send Revision Request</button>
            </div>
        </form>
    </div>
</div>

{{-- Toast Notification Container --}}
<div class="trk-toast-container" id="strategistToastContainer"></div>

@push('scripts')
<script>
/**
 * ═══════════════════════════════════════════════════════════════
 * STRATEGIST TRACKING & COMMAND CENTER JAVASCRIPT
 * ═══════════════════════════════════════════════════════════════
 */

let activeInspectTaskId = null;
let currentTaskData = null;

// ── 1. Toast Notification Helper ─────────────────────────────────
function showStrategistToast(type, message) {
    const container = document.getElementById('strategistToastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `trk-toast ${type === 'success' ? 'trk-toast-success' : 'trk-toast-error'}`;
    const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
    toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${escapeHtml(message)}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 250);
    }, 3500);
}

function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, function(m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
    });
}

// ── 2. View Mode Toggle (Table vs Cards) ─────────────────────────
function setTrackingViewMode(mode) {
    const tableView = document.getElementById('trkTableView');
    const gridView = document.getElementById('trkGridView');
    const tableBtn = document.getElementById('trkViewTableBtn');
    const gridBtn = document.getElementById('trkViewGridBtn');

    if (mode === 'grid') {
        if (tableView) tableView.style.display = 'none';
        if (gridView) gridView.style.display = 'grid';
        if (tableBtn) tableBtn.classList.remove('is-active');
        if (gridBtn) gridBtn.classList.add('is-active');
        localStorage.setItem('trk_view_mode', 'grid');
    } else {
        if (tableView) tableView.style.display = 'block';
        if (gridView) gridView.style.display = 'none';
        if (tableBtn) tableBtn.classList.add('is-active');
        if (gridBtn) gridBtn.classList.remove('is-active');
        localStorage.setItem('trk_view_mode', 'table');
    }
}

// ── 3. Slide-Over Task Inspector Drawer ──────────────────────────
async function openTaskInspector(taskId) {
    activeInspectTaskId = taskId;
    const drawer = document.getElementById('trkDrawer');
    const overlay = document.getElementById('trkDrawerOverlay');
    if (!drawer || !overlay) return;

    overlay.classList.add('is-open');
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');

    // Reset drawer state to loading
    document.getElementById('drawerTaskTitle').textContent = 'Loading task details...';
    document.getElementById('drawerDeliverablesGrid').innerHTML = '<div style="font-size:12px;color:var(--text3);grid-column:1/-1;padding:16px;text-align:center"><i class="fa-solid fa-spinner fa-spin"></i> Loading deliverables...</div>';

    try {
        const response = await fetch(`/strategist/tasks/${taskId}/quick-inspect`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        if (!data.success || !data.task) throw new Error('Task payload missing');

        currentTaskData = data.task;
        renderTaskInDrawer(data.task);

    } catch (err) {
        console.error('Drawer inspect error:', err);
        document.getElementById('drawerTaskTitle').textContent = 'Task #' + taskId;
        showStrategistToast('error', 'Failed to load task details into inspector.');
    }
}

function closeTaskInspector() {
    const drawer = document.getElementById('trkDrawer');
    const overlay = document.getElementById('trkDrawerOverlay');
    if (drawer) {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
    }
    if (overlay) {
        overlay.classList.remove('is-open');
    }
    activeInspectTaskId = null;
}

function renderTaskInDrawer(task) {
    // Header
    document.getElementById('drawerClientEmoji').textContent = task.client?.emoji || '🏢';
    document.getElementById('drawerClientName').textContent = task.client?.name || 'Unknown Client';
    document.getElementById('drawerTaskTitle').textContent = task.title || 'Untitled Task';

    // Badges
    const statusEl = document.getElementById('drawerStatusBadge');
    statusEl.className = 'status ' + (task.status_class || '');
    statusEl.textContent = task.status_label || task.status;

    const typeEl = document.getElementById('drawerTypeBadge');
    typeEl.className = 'tag ' + (task.type_class || '');
    typeEl.textContent = task.type || 'Task';

    const prioEl = document.getElementById('drawerPriorityBadge');
    prioEl.className = 'trk-priority ' + (task.priority === 'urgent' ? 'trk-prio-urgent' : (task.priority === 'high' ? 'trk-prio-high' : 'trk-prio-normal'));
    prioEl.textContent = (task.priority || 'Normal').toUpperCase();

    // Assignee & Timer
    if (task.assignee) {
        document.getElementById('drawerAssigneeName').textContent = task.assignee.name;
        document.getElementById('drawerAssigneeRole').textContent = task.assignee.role || 'Designer';
        const av = document.getElementById('drawerAssigneeAvatar');
        av.textContent = task.assignee.initial || 'D';
        av.style.background = task.assignee.avatar_color || '#6366f1';
    } else {
        document.getElementById('drawerAssigneeName').textContent = 'Unassigned';
        document.getElementById('drawerAssigneeRole').textContent = 'No designer assigned';
    }

    const timerText = document.getElementById('drawerTimerText');
    const pulseDot = document.getElementById('drawerPulseDot');
    if (task.is_timer_running) {
        pulseDot.style.display = 'inline-block';
        timerText.innerHTML = `<span style="color:#059669">Working Now (${task.timer_formatted})</span>`;
    } else if (task.is_paused) {
        pulseDot.style.display = 'none';
        timerText.innerHTML = `<span style="color:#D97706"><i class="fa-solid fa-pause"></i> Paused</span>`;
    } else {
        pulseDot.style.display = 'none';
        timerText.textContent = task.status === 'completed' ? 'Completed' : 'Timer Idle';
    }

    // Schedule row
    document.getElementById('drawerDeadline').textContent = task.deadline || '—';
    if (task.deadline_overdue) {
        document.getElementById('drawerDeadline').innerHTML = `<span style="color:#EF4444;font-weight:700">${task.deadline} (Overdue)</span>`;
    }
    document.getElementById('drawerPostDate').textContent = task.post_date || '—';
    document.getElementById('drawerClientStatus').innerHTML = task.is_client_approved 
        ? '<span style="color:#059669;font-weight:700"><i class="fa-solid fa-check"></i> Approved</span>' 
        : '<span style="color:#EF4444;font-weight:700"><i class="fa-solid fa-clock"></i> Pending</span>';

    // Revision Card
    const revCard = document.getElementById('drawerRevisionCard');
    if (task.revision_count > 0) {
        revCard.style.display = 'flex';
        document.getElementById('drawerRevisionCount').textContent = task.revision_count;
        document.getElementById('drawerRevisionNotes').textContent = task.revision_info?.latest_note || 'Revision requested without detailed notes.';
        document.getElementById('drawerRevisionAuthor').textContent = task.revision_info?.requested_by || 'Strategist';
        document.getElementById('drawerRevisionDate').textContent = task.revision_info?.date || 'recently';
    } else {
        revCard.style.display = 'none';
    }

    // Deliverables Grid
    const delGrid = document.getElementById('drawerDeliverablesGrid');
    document.getElementById('drawerDeliverablesCount').textContent = task.deliverables ? task.deliverables.length : 0;
    if (task.deliverables && task.deliverables.length > 0) {
        delGrid.innerHTML = task.deliverables.map(d => {
            const isVid = d.is_video;
            const previewContent = isVid 
                ? `<video src="${escapeHtml(d.url)}" muted preload="metadata"></video><span style="position:absolute;top:4px;right:4px;background:rgba(0,0,0,0.7);color:#fff;padding:2px 4px;border-radius:4px;font-size:9px"><i class="fa-solid fa-play"></i></span>`
                : (d.is_image ? `<img src="${escapeHtml(d.url)}" alt="${escapeHtml(d.name)}" loading="lazy">` : `<div style="height:100%;display:flex;align-items:center;justify-content:center;background:var(--card2);font-size:24px;color:var(--text3)"><i class="fa-solid fa-file"></i></div>`);

            return `
                <div class="trk-d-media-item" onclick="window.open('${escapeHtml(d.url)}', '_blank')" title="Click to view ${escapeHtml(d.name)}">
                    ${previewContent}
                    <div class="trk-d-media-badge">${escapeHtml(d.version_label || d.name)}</div>
                </div>
            `;
        }).join('');
    } else {
        delGrid.innerHTML = '<div style="font-size:12px;color:var(--text3);grid-column:1/-1;padding:12px;text-align:center">No design deliverables uploaded yet.</div>';
    }

    // Brief & Copy
    document.getElementById('drawerBriefBox').textContent = task.brief || 'No creative brief provided.';
    const copyBox = document.getElementById('drawerCopyBox');
    if (task.caption || task.hashtags) {
        copyBox.style.display = 'block';
        document.getElementById('drawerCaptionText').textContent = task.caption || '';
        document.getElementById('drawerHashtagsText').textContent = task.hashtags || '';
    } else {
        copyBox.style.display = 'none';
    }

    // Comments feed
    const commentsFeed = document.getElementById('drawerCommentsFeed');
    if (task.comments && task.comments.length > 0) {
        commentsFeed.innerHTML = task.comments.map(c => `
            <div style="display:flex;gap:8px;align-items:flex-start">
                <div class="av-sm" style="background:${escapeHtml(c.user_avatar)};width:26px;height:26px;font-size:10px;flex-shrink:0">${escapeHtml(c.user_initial)}</div>
                <div style="background:var(--card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;flex:1">
                    <div style="font-weight:700;color:var(--text);font-size:11px;margin-bottom:2px">${escapeHtml(c.user_name)} <span style="font-weight:500;color:var(--text3);font-size:10px;margin-left:4px">${escapeHtml(c.created_at)}</span></div>
                    <div style="color:var(--text2);line-height:1.35">${escapeHtml(c.body)}</div>
                </div>
            </div>
        `).join('');
    } else {
        commentsFeed.innerHTML = '<div style="font-size:12px;color:var(--text3);text-align:center;padding:8px">No comments yet.</div>';
    }

    // Route links in footer
    document.getElementById('drawerEditLink').href = task.routes?.edit || `/strategist/tasks/${task.id}/edit`;
    document.getElementById('drawerFullPageLink').href = task.routes?.show || `/strategist/tasks/${task.id}`;
}

// ── 4. 1-Click Copy Caption from Drawer ──────────────────────────
function copyDrawerCaption() {
    if (!currentTaskData) return;
    const caption = (currentTaskData.caption || '').trim();
    const hashtags = (currentTaskData.hashtags || '').trim();
    const fullText = [caption, hashtags].filter(Boolean).join("\n\n");

    if (!fullText) {
        showStrategistToast('error', 'No caption or hashtags available to copy.');
        return;
    }

    navigator.clipboard.writeText(fullText).then(() => {
        showStrategistToast('success', 'Caption & Hashtags copied to clipboard! 📋');
    }).catch(() => {
        showStrategistToast('error', 'Failed to copy text.');
    });
}

// ── 5. Instant Nudge Designer Action ─────────────────────────────
async function nudgeTask(taskId, assigneeName, event) {
    if (event) event.stopPropagation();

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/strategist/tasks/${taskId}/follow-up`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();
        if (data.success) {
            showStrategistToast('success', data.message || `Follow-up reminder sent to ${assigneeName}!`);
        } else {
            showStrategistToast('error', data.message || 'Could not send reminder.');
        }
    } catch (err) {
        console.error('Nudge error:', err);
        showStrategistToast('error', 'Network error sending follow-up reminder.');
    }
}

function triggerDrawerNudge() {
    if (!activeInspectTaskId || !currentTaskData) return;
    nudgeTask(activeInspectTaskId, currentTaskData.assignee?.name || 'Designer');
}

// ── 6. Drawer Revision Modal ─────────────────────────────────────
function openDrawerRevisionModal() {
    if (!activeInspectTaskId || !currentTaskData) return;
    document.getElementById('trkRevisionTaskTitle').textContent = `Task #${activeInspectTaskId}: "${currentTaskData.title}"`;
    document.getElementById('trkRevisionForm').action = `/strategist/tasks/${activeInspectTaskId}/request-revision`;
    document.getElementById('trkRevisionModal').style.display = 'flex';
}

function closeDrawerRevisionModal() {
    document.getElementById('trkRevisionModal').style.display = 'none';
    document.getElementById('trkRevisionNote').value = '';
}

async function submitRevisionFormAjax(e) {
    e.preventDefault();
    const form = document.getElementById('trkRevisionForm');
    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();
        if (data.success) {
            showStrategistToast('success', data.message || 'Revision requested — sent back to designer.');
            closeDrawerRevisionModal();
            closeTaskInspector();
            submitFilterFormAjax(); // Refresh table state
        } else {
            showStrategistToast('error', data.message || 'Failed to submit revision.');
        }
    } catch (err) {
        showStrategistToast('error', 'Network error submitting revision.');
    }
}

// ── 7. Post Comment from Drawer ──────────────────────────────────
async function submitDrawerComment() {
    if (!activeInspectTaskId) return;
    const input = document.getElementById('drawerCommentInput');
    const text = (input.value || '').trim();
    if (!text) return;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/strategist/tasks/${activeInspectTaskId}/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ body: text })
        });

        const data = await response.json();
        if (data.success) {
            input.value = '';
            showStrategistToast('success', 'Comment posted!');
            // Re-inspect to refresh comment feed
            openTaskInspector(activeInspectTaskId);
        } else {
            showStrategistToast('error', data.message || 'Could not post comment.');
        }
    } catch (err) {
        showStrategistToast('error', 'Network error posting comment.');
    }
}

// ── 8. Reassign Modal ────────────────────────────────────────────
function openReassignModal(taskId, title, currentAssignee) {
    document.getElementById('reassignForm').action = '/strategist/tasks/' + taskId + '/reassign';
    document.getElementById('reassignTaskTitle').textContent = '"' + title + '"';
    if (currentAssignee) {
        document.getElementById('reassignSelect').value = currentAssignee;
    }
    document.getElementById('reassignModal').style.display = 'flex';
}
function closeReassignModal() {
    document.getElementById('reassignModal').style.display = 'none';
}

// ── 9. Delete Task AJAX ──────────────────────────────────────────
async function deleteTaskAjax(taskId, title, event) {
    if (event) event.stopPropagation();
    if (!confirm(`Are you sure you want to delete task "${title}"?`)) return;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/strategist/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();
        if (data.success) {
            showStrategistToast('success', data.message || 'Task deleted.');
            // Remove row from table / cards immediately
            const row = document.getElementById(`task-row-${taskId}`);
            if (row) row.remove();
            submitFilterFormAjax();
        } else {
            showStrategistToast('error', data.message || 'Failed to delete task.');
        }
    } catch (err) {
        showStrategistToast('error', 'Error deleting task.');
    }
}

// ── 10. Real-time Live DOM Search & Filter Logic ─────────────────
document.addEventListener('DOMContentLoaded', function() {
    // Restore preferred view mode (table vs card grid)
    const savedMode = localStorage.getItem('trk_view_mode') || 'table';
    setTrackingViewMode(savedMode);

    // Instant client-side row search as user types
    const searchInput = document.getElementById('trkSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = (this.value || '').trim().toLowerCase();
            const rows = document.querySelectorAll('.trk-data-row');
            rows.forEach(r => {
                const text = r.getAttribute('data-search-text') || '';
                if (!query || text.includes(query)) {
                    r.style.display = '';
                } else {
                    r.style.display = 'none';
                }
            });
        });
    }

    // Escape key closes modals / drawer
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTaskInspector();
            closeReassignModal();
            closeDrawerRevisionModal();
        }
    });
});

function filterByStatus(statusVal) {
    const sel = document.getElementById('trkStatusSelect');
    if (sel) sel.value = statusVal || 'all';
    submitFilterFormAjax();
}

function clearAllFilters() {
    const form = document.getElementById('trkFilterForm');
    if (!form) return;
    form.querySelectorAll('input[type="text"]').forEach(el => el.value = '');
    form.querySelectorAll('input[type="date"]').forEach(el => el.value = '');
    form.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    submitFilterFormAjax();
}

function submitFilterFormAjax() {
    const form = document.getElementById('trkFilterForm');
    if (!form) return;
    const formData = new FormData(form);
    const queryString = new URLSearchParams(formData).toString();
    const url = `${form.action}${form.action.includes('?') ? '&' : '?'}${queryString}`;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html',
        }
    })
    .then(res => res.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Swap stats row
        const newStats = doc.querySelector('.trk-stats-row');
        if (newStats) document.querySelector('.trk-stats-row').innerHTML = newStats.innerHTML;

        // Swap table body
        const newBody = doc.getElementById('trkTableBody');
        if (newBody) document.getElementById('trkTableBody').innerHTML = newBody.innerHTML;

        // Swap grid view
        const newGrid = doc.getElementById('trkGridView');
        if (newGrid) document.getElementById('trkGridView').innerHTML = newGrid.innerHTML;

        // Swap subtitle & pagination
        const newSub = doc.getElementById('trackingSubtitle');
        if (newSub) document.getElementById('trackingSubtitle').innerHTML = newSub.innerHTML;

        const newPag = doc.querySelector('.trk-pagination');
        const currPag = document.querySelector('.trk-pagination');
        if (newPag && currPag) currPag.innerHTML = newPag.innerHTML;

        window.history.pushState({ path: url }, '', url);
    })
    .catch(err => {
        console.error('Filter AJAX error:', err);
    });
}
</script>
@endpush

@endsection
