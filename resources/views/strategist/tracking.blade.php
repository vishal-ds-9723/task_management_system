@extends('layouts.app')

@section('content')

{{-- Page Header --}}
<div class="trk-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px">
    <div class="trk-header-left">
        <div class="trk-title"><i class="fa-solid fa-list-check" style="color:var(--primary);margin-right:6px"></i> Task Tracking</div>
        <div class="trk-subtitle">Monitor &amp; manage team tasks — {{ $tasks->total() }} result{{ $tasks->total() !== 1 ? 's' : '' }}</div>
    </div>
    <div class="trk-header-right" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
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
        <a href="{{ route('strategist.calendar') }}" class="trk-header-btn trk-btn-ghost"><i class="fa-regular fa-calendar"></i> Calendar</a>
        <a href="{{ route('strategist.create-task') }}" class="trk-header-btn trk-btn-primary"><i class="fa-solid fa-plus"></i> New Task</a>
    </div>
</div>

<script>
function clearFilterAndNavigate(url) {
    // Reset all form inputs to defaults
    document.querySelectorAll('.trk-filter-form input[type="text"]').forEach(el => el.value = '');
    document.querySelectorAll('.trk-filter-form input[type="date"]').forEach(el => el.value = '');
    document.querySelectorAll('.trk-filter-form select').forEach(el => {
        el.value = el.querySelector('option:first-child')?.value || '';
    });
    // Navigate to clean URL
    window.location.href = url;
}
</script>

{{-- Summary Stats --}}
<div class="trk-stats-row">
    <a href="javascript:void(0)" onclick="clearFilterAndNavigate('{{ route('strategist.tracking', ['scope' => $scope ?? 'all']) }}')" class="trk-stat {{ !request('status') || request('status') === 'all' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--blue-dim);color:var(--blue)"><i class="fa-solid fa-layer-group"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val">{{ $totalCount }}</div>
            <div class="trk-stat-label">Total</div>
        </div>
    </a>
    <a href="javascript:void(0)" onclick="clearFilterAndNavigate('{{ route('strategist.tracking', ['scope' => $scope ?? 'all', 'status' => 'todo']) }}')" class="trk-stat {{ request('status') === 'todo' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--card2);color:var(--text3)"><i class="fa-solid fa-clipboard-list"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val">{{ $todoCount }}</div>
            <div class="trk-stat-label">To Do</div>
        </div>
    </a>
    <a href="javascript:void(0)" onclick="clearFilterAndNavigate('{{ route('strategist.tracking', ['scope' => $scope ?? 'all', 'status' => 'inprogress']) }}')" class="trk-stat {{ request('status') === 'inprogress' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--blue-dim);color:var(--blue)"><i class="fa-solid fa-person-running"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val">{{ $inProgressCount }}</div>
            <div class="trk-stat-label">In Progress</div>
        </div>
    </a>
    <a href="javascript:void(0)" onclick="clearFilterAndNavigate('{{ route('strategist.tracking', ['scope' => $scope ?? 'all', 'status' => 'review']) }}')" class="trk-stat {{ request('status') === 'review' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--yellow-dim);color:var(--yellow)"><i class="fa-solid fa-flask"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val">{{ $reviewCount }}</div>
            <div class="trk-stat-label">Review</div>
        </div>
    </a>
    <a href="javascript:void(0)" onclick="clearFilterAndNavigate('{{ route('strategist.tracking', ['scope' => $scope ?? 'all', 'status' => 'completed']) }}')" class="trk-stat {{ request('status') === 'completed' ? 'trk-stat-active' : '' }}">
        <div class="trk-stat-icon" style="background:var(--teal-dim);color:var(--teal)"><i class="fa-solid fa-circle-check"></i></div>
        <div class="trk-stat-body">
            <div class="trk-stat-val">{{ $completedCount }}</div>
            <div class="trk-stat-label">Done</div>
        </div>
    </a>
    @if($overdueCount > 0)
    <a href="javascript:void(0)" onclick="clearFilterAndNavigate('{{ route('strategist.tracking', ['scope' => $scope ?? 'all', 'status' => 'overdue']) }}')" class="trk-stat {{ request('status') === 'overdue' ? 'trk-stat-active' : '' }} trk-stat-danger">
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
    <form method="GET" action="{{ route('strategist.tracking') }}" class="trk-filter-form">
        <input type="hidden" name="scope" value="{{ $scope ?? 'all' }}">
        <div class="trk-search-wrap">
            <i class="fa-solid fa-magnifying-glass trk-search-icon"></i>
            <input type="text" name="search" placeholder="Search tasks…" value="{{ request('search') }}" class="trk-search-input">
        </div>
        <div class="trk-filter-selects">
            <select name="sort" class="trk-select">
                <option value="deadline_asc" {{ request('sort', 'deadline_asc') == 'deadline_asc' ? 'selected' : '' }}>Deadline ↑</option>
                <option value="deadline_desc" {{ request('sort') == 'deadline_desc' ? 'selected' : '' }}>Deadline ↓</option>
                <option value="post_date_asc" {{ request('sort') == 'post_date_asc' ? 'selected' : '' }}>Post Date ↑</option>
                <option value="post_date_desc" {{ request('sort') == 'post_date_desc' ? 'selected' : '' }}>Post Date ↓</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
            </select>
            <select name="creator_id" class="trk-select">
                <option value="">All Strategists</option>
                @foreach($strategists ?? [] as $st)
                    <option value="{{ $st->id }}" {{ request('creator_id') == $st->id ? 'selected' : '' }}>
                        ✍️ {{ $st->name }}
                    </option>
                @endforeach
            </select>
            <select name="assigned_to" class="trk-select">
                <option value="">All Assignees</option>
                @foreach($designers ?? [] as $ds)
                    <option value="{{ $ds->id }}" {{ request('assigned_to') == $ds->id ? 'selected' : '' }}>
                        🎨 {{ $ds->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="trk-select">
                <option value="all">All Status</option>
                <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                <option value="inprogress" {{ request('status') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                <option value="review" {{ request('status') == 'review' ? 'selected' : '' }}>Review</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
            <div class="trk-select-wrap" style="min-width:180px">
                <x-client-select :clients="$clients" name="client" :value="request('client')" placeholder="All Clients" width="100%" />
            </div>
            <select name="type" class="trk-select">
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
            <select name="per_page" class="trk-select trk-select-sm">
                <option value="25" {{ request('per_page', '25') == '25' ? 'selected' : '' }}>25 / page</option>
                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 / page</option>
                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 / page</option>
            </select>
        </div>
        <div class="trk-date-range">
            <div class="trk-date-field">
                <label class="trk-date-label">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="trk-date-input">
            </div>
            <div class="trk-date-field">
                <label class="trk-date-label">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="trk-date-input">
            </div>
        </div>
        <button type="submit" class="trk-filter-btn"><i class="fa-solid fa-filter"></i> Filter</button>
        @if(request()->hasAny(['search', 'status', 'client', 'creator_id', 'assigned_to', 'type', 'date_from', 'date_to', 'sort', 'per_page']))
            <a href="{{ route('strategist.tracking', ['scope' => $scope ?? 'all']) }}" class="trk-clear-btn" id="trkClearBtn"><i class="fa-solid fa-xmark"></i> Clear</a>
        @endif
    </form>
</div>

{{-- Task Table --}}
<div class="trk-table-card">
    <div class="trk-table-wrap">
        <table class="trk-table">
            <thead>
                <tr>
                    <th style="min-width:220px">Task</th>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Assigned&nbsp;To</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Deadline</th>
                    <th style="width:120px;text-align:center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr id="task-{{ $task->id }}" class="{{ (string) request('task') === (string) $task->id ? 'trk-row-highlight' : '' }}">
                        <td>
                            <div class="trk-task-title">{{ $task->title }}</div>
                            <div class="trk-task-meta">
                                @php $isDevTask = in_array($task->type, ['website', 'software', 'maintenance']); @endphp
                                @if($isDevTask)
                                    @if($task->tech_stack)
                                        <span><i class="fas fa-code" style="font-size:9px;opacity:.7"></i> {{ $task->tech_stack }}</span>
                                    @endif
                                    @if($task->dev_deadline)
                                        <span><i class="fas fa-laptop-code" style="font-size:9px;opacity:.7"></i> Dev: {{ $task->dev_deadline->format('d M') }}</span>
                                    @elseif($task->launch_date)
                                        <span><i class="fas fa-rocket" style="font-size:9px;opacity:.7"></i> Launch: {{ $task->launch_date->format('d M') }}</span>
                                    @endif
                                @else
                                    @if(is_array($task->platform))
                                        <span>{{ implode(', ', array_map('ucfirst', $task->platform)) }}</span>
                                    @else
                                        <span>{{ ucfirst($task->platform ?? '') }}</span>
                                    @endif
                                @endif
                                @if($task->comments_count > 0)
                                    <span class="trk-comment-badge"><i class="fas fa-comment" style="font-size:10px"></i> {{ $task->comments_count }}</span>
                                @endif
                                @if($task->creator)
                                    <span style="font-size: 11.5px; font-weight: 600; color: var(--primary); background: rgba(99,102,241,0.08); padding: 2px 7px; border-radius: 6px; border: 1px solid rgba(99,102,241,0.2); margin-left: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-user-pen" style="font-size: 10px;"></i> By: {{ $task->creator->name }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="trk-client-cell">
                                <x-client-branding :client="$task->client" size="28px" radius="6px" :showName="true" fit="contain" />
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
                                <span class="wf-rev-badge" style="animation:none"><i class="fa-solid fa-rotate-left"></i> {{ $task->revision_count }}</span>
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
                            @php $isDevTaskDl = in_array($task->type, ['website', 'software', 'maintenance']); @endphp
                            @if($isDevTaskDl)
                                @if($task->dev_deadline)
                                    <div class="trk-deadline {{ $task->dev_deadline->isPast() && !in_array($task->status, ['completed','published']) ? 'trk-deadline-overdue' : '' }}" title="Dev Deadline">
                                        <i class="fas fa-laptop-code" style="font-size:10px"></i>
                                        {{ $task->dev_deadline->format('d M Y') }}
                                        @if($task->dev_deadline->isPast() && !in_array($task->status, ['completed','published']))
                                            <span class="trk-overdue-tag">Overdue</span>
                                        @endif
                                    </div>
                                @elseif($task->launch_date)
                                    <div class="trk-deadline" title="Launch Date">
                                        <i class="fas fa-rocket" style="font-size:10px"></i>
                                        {{ $task->launch_date->format('d M Y') }}
                                    </div>
                                @elseif($task->deadline)
                                    <div class="trk-deadline {{ $task->isOverdue() ? 'trk-deadline-overdue' : '' }}">
                                        <i class="fa-regular {{ $task->isOverdue() ? 'fa-clock' : 'fa-calendar' }}"></i>
                                        {{ $task->deadline->format('d M Y') }}
                                        @if($task->isOverdue()) <span class="trk-overdue-tag">Overdue</span> @endif
                                    </div>
                                @else
                                    <span class="trk-no-deadline">—</span>
                                @endif
                                @if($task->launch_date && $task->dev_deadline)
                                    <div style="font-size:10px;color:var(--text3);margin-top:2px"><i class="fas fa-rocket" style="font-size:9px"></i> {{ $task->launch_date->format('d M Y') }}</div>
                                @endif
                            @else
                                @if($task->deadline)
                                    <div class="trk-deadline {{ $task->isOverdue() ? 'trk-deadline-overdue' : '' }}">
                                        <i class="fa-regular {{ $task->isOverdue() ? 'fa-clock' : 'fa-calendar' }}"></i>
                                        {{ $task->deadline->format('d M Y') }}
                                        @if($task->isOverdue())
                                            <span class="trk-overdue-tag">Overdue</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="trk-no-deadline">—</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <div class="trk-actions">
                                <a href="{{ route('strategist.tasks.show', $task) }}" class="trk-action-btn trk-action-view" title="View"><i class="fa-solid fa-eye"></i></a>
                                <a href="{{ route('strategist.tasks.edit', $task) }}" class="trk-action-btn" title="Edit" data-no-loader><i class="fa-solid fa-pen-to-square"></i></a>
                                <button type="button" class="trk-action-btn" title="Reassign" onclick="openReassignModal({{ $task->id }}, '{{ e($task->title) }}', {{ $task->assigned_to ?? 'null' }})"><i class="fa-solid fa-arrows-rotate"></i></button>
                                @if($task->assignee && $task->status !== 'completed')
                                    <form method="POST" action="{{ route('strategist.tasks.follow-up', $task) }}" style="margin:0" onsubmit="return confirm('Send follow-up reminder to {{ e($task->assignee->name) }}?')">
                                        @csrf
                                        <button type="submit" class="trk-action-btn trk-action-followup" title="Follow Up"><i class="fa-solid fa-bell"></i></button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('strategist.tasks.destroy', $task) }}" style="margin:0" onsubmit="return confirm('Are you sure you want to delete this task: {{ addslashes($task->title) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="trk-action-btn trk-action-delete" title="Delete Task" style="color:var(--red)"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
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

    {{-- Pagination --}}
    @if($tasks->hasPages())
        <div class="trk-pagination">
            {{ $tasks->links('vendor.pagination.custom') }}
        </div>
    @endif
    @if($tasks->total() > 0)
        <div class="trk-table-footer">
            <span class="trk-table-footer-text">Page {{ $tasks->currentPage() }} of {{ $tasks->lastPage() }} · {{ $tasks->total() }} task{{ $tasks->total() !== 1 ? 's' : '' }}</span>
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
                    <option value="{{ $designer->id }}">{{ $designer->name }} <span style="text-transform:capitalize">({{ $designer->role }})</span></option>
                @endforeach
            </select>
            <div class="trk-modal-actions">
                <button type="button" class="trk-modal-btn-ghost" onclick="closeReassignModal()">Cancel</button>
                <button type="submit" class="trk-modal-btn-primary"><i class="fa-solid fa-check"></i> Reassign</button>
            </div>
        </form>
    </div>
</div>

<style>
/* ===== Tracking Page ===== */

/* Header */
.trk-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; gap:16px; flex-wrap:wrap; }
.trk-header-left { }
.trk-title { font-size:22px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:-0.3px; }
.trk-subtitle { font-size:12.5px; color:var(--text3); margin-top:3px; font-weight:500; }
.trk-header-right { display:flex; gap:8px; }
.trk-header-btn { padding:9px 18px; border-radius:10px; font-size:12.5px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s ease; border:none; cursor:pointer; }
.trk-btn-primary { background:var(--primary); color:#fff; box-shadow:0 2px 8px rgba(204,49,14,0.25); }
.trk-btn-primary:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(204,49,14,0.3); opacity:0.9; }
.trk-btn-ghost { background:var(--card); color:var(--text2); border:1px solid var(--border); }
.trk-btn-ghost:hover { background:var(--card2); color:var(--text); border-color:var(--border2); }

/* Stats Row */
.trk-stats-row { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
.trk-stat { display:flex; align-items:center; gap:10px; padding:12px 16px; background:var(--card); border:1px solid var(--border); border-radius:12px; flex:1; min-width:120px; transition:all 0.2s ease; text-decoration:none; cursor:pointer; box-shadow:var(--shadow-sm); }
.trk-stat:hover { border-color:var(--border2); transform:translateY(-2px); box-shadow:var(--shadow); }
.trk-stat-active { border-color:var(--primary); box-shadow:0 0 0 2px rgba(204,49,14,0.12); background:rgba(204,49,14,0.03); }
.trk-stat-danger { border-color:rgba(239,68,68,0.2); }
.trk-stat-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
.trk-stat-body { }
.trk-stat-val { font-size:20px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; line-height:1.1; }
.trk-stat-label { font-size:10.5px; color:var(--text3); font-weight:600; text-transform:uppercase; letter-spacing:0.3px; margin-top:1px; }

/* Filter Bar */
.trk-filter-bar { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:12px 16px; margin-bottom:16px; box-shadow:var(--shadow-sm); }
.trk-filter-form { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.trk-search-wrap { flex:1; min-width:200px; position:relative; }
.trk-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:12px; color:var(--text3); pointer-events:none; }
.trk-search-input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--border); border-radius:8px; background:var(--card2); font-size:12.5px; color:var(--text); font-family:inherit; transition:all 0.2s; }
.trk-search-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(204,49,14,0.08); background:var(--card); }
.trk-search-input::placeholder { color:var(--text3); }
.trk-filter-selects { display:flex; gap:8px; flex-wrap:wrap; }
.trk-select { padding:9px 12px; border:1px solid var(--border); border-radius:8px; background:var(--card); font-size:12px; color:var(--text2); font-family:inherit; cursor:pointer; transition:all 0.2s; min-width:120px; }
.trk-select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(204,49,14,0.08); }
.trk-filter-btn { padding:9px 16px; border-radius:8px; background:var(--primary); color:#fff; border:none; font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all 0.2s; font-family:inherit; }
.trk-filter-btn:hover { opacity:0.9; transform:translateY(-1px); }
.trk-clear-btn { padding:9px 14px; border-radius:8px; background:var(--red-dim); color:var(--red); text-decoration:none; font-size:11.5px; font-weight:600; display:inline-flex; align-items:center; gap:4px; transition:all 0.2s; }
.trk-clear-btn:hover { background:var(--red); color:#fff; }

/* Date Range */
.trk-date-range { display:flex; gap:8px; align-items:flex-end; }
.trk-date-field { display:flex; flex-direction:column; gap:2px; }
.trk-date-label { font-size:9.5px; font-weight:700; color:var(--text3); text-transform:uppercase; letter-spacing:0.3px; padding-left:2px; }
.trk-date-input { padding:8px 10px; border:1px solid var(--border); border-radius:8px; background:var(--card); font-size:12px; color:var(--text2); font-family:inherit; cursor:pointer; transition:all 0.2s; min-width:130px; }
.trk-date-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(204,49,14,0.08); }
.trk-select-sm { min-width:90px!important; }

/* Table Card */
.trk-table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow-sm); overflow:hidden; }
.trk-table-wrap { overflow-x:auto; }
.trk-table { width:100%; border-collapse:collapse; }
.trk-table thead th { text-align:left; padding:12px 16px; font-size:10.5px; color:var(--text3); letter-spacing:0.5px; text-transform:uppercase; font-weight:700; background:var(--card2); border-bottom:1px solid var(--border); white-space:nowrap; }
.trk-table tbody tr { border-bottom:1px solid var(--border); transition:all 0.15s ease; }
.trk-table tbody tr:last-child { border-bottom:none; }
.trk-table tbody tr:nth-child(even) { background:rgba(0,0,0,0.01); }
.trk-table tbody tr:hover { background:var(--primary-dim); }
.trk-table tbody td { padding:14px 16px; font-size:12.5px; color:var(--text2); vertical-align:middle; }
.trk-row-highlight { box-shadow:inset 3px 0 0 var(--primary); background:rgba(204,49,14,0.04)!important; }

/* Task Cell */
.trk-task-title { font-size:13px; font-weight:600; color:var(--text); line-height:1.3; margin-bottom:2px; }
.trk-task-meta { display:flex; align-items:center; gap:8px; font-size:11px; color:var(--text3); font-weight:500; }
.trk-comment-badge { background:var(--blue-dim); color:var(--blue); padding:1px 6px; border-radius:4px; font-size:10px; font-weight:600; }

/* Client Cell */
.trk-client-cell { display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:500; color:var(--text2); }
.trk-client-emoji { font-size:16px; }

/* Assignee Cell */
.trk-assignee-cell { display:flex; align-items:center; gap:7px; font-size:12.5px; font-weight:500; color:var(--text2); }
.trk-unassigned { font-size:11px; font-weight:600; color:var(--red); display:inline-flex; align-items:center; gap:4px; }

/* Priority */
.trk-priority { font-size:11px; font-weight:600; padding:3px 9px; border-radius:6px; white-space:nowrap; }
.trk-prio-urgent { background:var(--red-dim); color:var(--red); }
.trk-prio-high { background:rgba(249,115,22,0.1); color:#F97316; }
.trk-prio-normal { background:var(--teal-dim); color:var(--teal); }

/* Deadline */
.trk-deadline { display:flex; align-items:center; gap:5px; font-size:12px; font-weight:500; color:var(--text2); white-space:nowrap; }
.trk-deadline-overdue { color:var(--red); font-weight:600; }
.trk-overdue-tag { font-size:9px; font-weight:700; padding:2px 6px; border-radius:4px; background:var(--red-dim); color:var(--red); text-transform:uppercase; letter-spacing:0.3px; }
.trk-no-deadline { color:var(--text3); }

/* Actions */
.trk-actions { display:flex; gap:4px; justify-content:center; }
.trk-action-btn { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:8px; border:1px solid var(--border); background:var(--card); cursor:pointer; font-size:12px; text-decoration:none; transition:all 0.15s ease; color:var(--text3); }
.trk-action-btn:hover { background:var(--primary-dim); border-color:var(--primary); color:var(--primary); transform:translateY(-1px); }
.trk-action-view:hover { background:var(--blue-dim); border-color:var(--blue); color:var(--blue); }
.trk-action-followup:hover { background:var(--yellow-dim); border-color:var(--yellow); color:var(--yellow); }
.trk-action-delete:hover { background:var(--red-dim); border-color:var(--red); color:var(--red); }

/* Empty State */
.trk-empty { padding:0!important; }
.trk-empty-box { text-align:center; padding:48px 24px; }
.trk-empty-icon { font-size:32px; color:var(--text3); opacity:0.3; margin-bottom:10px; }
.trk-empty-title { font-size:14px; font-weight:700; color:var(--text2); margin-bottom:4px; }
.trk-empty-sub { font-size:12px; color:var(--text3); }

/* Pagination */
.trk-pagination { display:flex; justify-content:center; padding:16px 20px; border-top:1px solid var(--border); }
.pg-nav { display:flex; align-items:center; justify-content:space-between; width:100%; gap:16px; flex-wrap:wrap; }
.pg-info { font-size:12px; color:var(--text3); font-weight:500; }
.pg-info strong { color:var(--text2); font-weight:700; }
.pg-links { display:flex; align-items:center; gap:4px; }
.pg-btn { display:inline-flex; align-items:center; justify-content:center; min-width:34px; height:34px; padding:0 10px; border-radius:8px; border:1px solid var(--border); background:var(--card); color:var(--text2); font-size:12.5px; font-weight:600; text-decoration:none; transition:all 0.15s ease; cursor:pointer; font-family:inherit; }
.pg-btn:hover { background:var(--primary-dim); border-color:var(--primary); color:var(--primary); transform:translateY(-1px); }
.pg-btn-active { background:var(--primary); color:#fff; border-color:var(--primary); box-shadow:0 2px 8px rgba(204,49,14,0.25); pointer-events:none; }
.pg-btn-active:hover { transform:none; }
.pg-btn-disabled { opacity:0.4; cursor:not-allowed; pointer-events:none; }
.pg-dots { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; font-size:14px; color:var(--text3); font-weight:700; letter-spacing:2px; }
.trk-table-footer { padding:10px 20px; display:flex; align-items:center; justify-content:space-between; border-top:1px solid var(--border); flex-wrap:wrap; gap:8px; }
.trk-table-footer-text { font-size:11px; color:var(--text3); font-weight:500; }
.trk-perpage-footer { display:flex; align-items:center; gap:6px; }
.trk-perpage-label { font-size:11px; color:var(--text3); font-weight:500; }
.trk-perpage-select { padding:4px 8px; border:1px solid var(--border); border-radius:6px; background:var(--card); font-size:11.5px; color:var(--text2); font-family:inherit; cursor:pointer; transition:all 0.2s; }
.trk-perpage-select:focus { outline:none; border-color:var(--primary); }

/* Modal */
.trk-modal-overlay { position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; backdrop-filter:blur(2px); }
.trk-modal-box { background:var(--card); padding:24px; border-radius:16px; width:90%; max-width:420px; border:1px solid var(--border); box-shadow:0 12px 40px rgba(0,0,0,0.18); position:relative; }
.trk-modal-close { position:absolute; right:16px; top:14px; font-size:20px; color:var(--text3); cursor:pointer; line-height:1; transition:color 0.15s; }
.trk-modal-close:hover { color:var(--text); }
.trk-modal-header { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
.trk-modal-icon { width:40px; height:40px; border-radius:10px; background:var(--blue-dim); color:var(--blue); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
.trk-modal-title { font-size:16px; font-weight:700; color:var(--text); }
.trk-modal-sub { font-size:12px; color:var(--text3); margin-top:2px; }
.trk-modal-label { font-size:11px; font-weight:700; color:var(--text2); text-transform:uppercase; letter-spacing:0.3px; margin-bottom:6px; display:block; }
.trk-modal-select { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:10px; background:var(--card2); font-size:13px; color:var(--text); font-family:inherit; margin-bottom:16px; transition:all 0.2s; }
.trk-modal-select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(204,49,14,0.08); }
.trk-modal-actions { display:flex; gap:8px; justify-content:flex-end; }
.trk-modal-btn-ghost { padding:9px 16px; border-radius:8px; background:var(--card2); color:var(--text2); border:1px solid var(--border); font-size:12.5px; font-weight:600; cursor:pointer; transition:all 0.2s; font-family:inherit; }
.trk-modal-btn-ghost:hover { background:var(--border); color:var(--text); }
.trk-modal-btn-primary { padding:9px 18px; border-radius:8px; background:var(--primary); color:#fff; border:none; font-size:12.5px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all 0.2s; font-family:inherit; }
.trk-modal-btn-primary:hover { opacity:0.9; transform:translateY(-1px); }

/* Responsive */
@media (max-width:980px) {
    .trk-stats-row { gap:8px; }
    .trk-stat { min-width:100px; padding:10px 12px; }
    .trk-stat-val { font-size:17px; }
    .trk-stat-icon { width:32px; height:32px; font-size:12px; }
    .trk-filter-selects { width:100%; }
    .trk-search-wrap { min-width:100%; }
}
@media (max-width:640px) {
    .trk-header { flex-direction:column; align-items:flex-start; }
    .trk-stats-row { display:grid; grid-template-columns:repeat(3,1fr); }
    .trk-stat { min-width:0; }
    .trk-stat-icon { display:none; }
    .trk-stat { justify-content:center; text-align:center; }
    .trk-stat-body { text-align:center; }
    .trk-filter-form { flex-direction:column; }
    .trk-filter-selects { width:100%; }
    .trk-select { flex:1; min-width:0; }
    .trk-table thead th { font-size:9.5px; padding:10px 10px; }
    .trk-table tbody td { padding:10px 10px; font-size:11.5px; }
    .pg-nav { flex-direction:column; gap:10px; }
    .pg-info { text-align:center; }
    .pg-links { flex-wrap:wrap; justify-content:center; }
    .pg-btn { min-width:30px; height:30px; font-size:11px; padding:0 8px; }
    .trk-date-range { width:100%; }
    .trk-date-input { min-width:0; flex:1; }
    .trk-table-footer { flex-direction:column; align-items:center; gap:6px; }
}
@media (max-width:400px) {
    .trk-stats-row { grid-template-columns:repeat(2,1fr); gap:6px; }
    .trk-title { font-size:18px; }
    .pg-info { display:none; }
    .pg-btn { min-width:28px; height:28px; font-size:10px; padding:0 6px; }
    .trk-date-range { flex-direction:column; }
    .trk-table-footer { text-align:center; }
}
</style>

<script>
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
document.getElementById('reassignModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeReassignModal();
});

// Per-page change from footer
function changePerPage(val) {
    var params = new URLSearchParams(window.location.search);
    params.set('per_page', val);
    params.delete('page'); // reset to page 1
    window.location.search = params.toString();
}

// ===== Filter Cache (localStorage) =====
var CACHE_KEY = 'trk_filters';
var FILTER_FIELDS = ['search', 'sort', 'status', 'client', 'type', 'per_page', 'date_from', 'date_to'];

// Save current filters to localStorage on form submit
var filterForm = document.querySelector('.trk-filter-form');
if (filterForm) {
    filterForm.addEventListener('submit', function() {
        var data = {};
        FILTER_FIELDS.forEach(function(name) {
            var el = filterForm.querySelector('[name="' + name + '"]');
            if (el && el.value) data[name] = el.value;
        });
        localStorage.setItem(CACHE_KEY, JSON.stringify(data));
    });
}

// Clear cache when Clear button is clicked
var clearBtn = document.getElementById('trkClearBtn');
if (clearBtn) {
    clearBtn.addEventListener('click', function() {
        localStorage.removeItem(CACHE_KEY);
    });
}

// On first load (no query params), restore from cache
document.addEventListener('DOMContentLoaded', function() {
    var params = new URLSearchParams(window.location.search);
    var taskId = params.get('task');
    if (taskId) {
        var row = document.getElementById('task-' + taskId);
        if (row) row.scrollIntoView({ behavior:'smooth', block:'center' });
    }

    // Restore cached filters only if URL has zero filter params (fresh visit)
    var hasFilters = FILTER_FIELDS.some(function(f) { return params.has(f); }) || params.has('page');
    if (!hasFilters) {
        var cached = localStorage.getItem(CACHE_KEY);
        if (cached) {
            try {
                var data = JSON.parse(cached);
                var hasAny = Object.keys(data).some(function(k) {
                    return data[k] && data[k] !== 'all' && data[k] !== 'newest' && data[k] !== '25' && data[k] !== '';
                });
                if (hasAny) {
                    var newParams = new URLSearchParams();
                    Object.keys(data).forEach(function(k) {
                        if (data[k]) newParams.set(k, data[k]);
                    });
                    window.location.search = newParams.toString();
                }
            } catch(e) {}
        }
    }

    // Always save current URL state to cache (so pagination preserves filter cache)
    if (params.toString()) {
        var data = {};
        FILTER_FIELDS.forEach(function(name) {
            if (params.has(name)) data[name] = params.get(name);
        });
        if (Object.keys(data).length > 0) {
            localStorage.setItem(CACHE_KEY, JSON.stringify(data));
        }
    }
});
</script>

@push('scripts')
<script>
/**
 * ═══════════════════════════════════════════════════════════
 * STRATEGIST TRACKING AJAX - Filter Without Page Refresh
 * ═══════════════════════════════════════════════════════════
 */

document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('.trk-filter-form');
    if (!filterForm) return;

    // Get all filter controls
    const filterControls = filterForm.querySelectorAll('input, select');

    // Add change listeners to filter controls
    filterControls.forEach(control => {
        if (control.name !== 'search') {
            control.addEventListener('change', function() {
                applyStrategistFilterAJAX();
            });
        }
    });

    // Handle search with debounce
    const searchInput = filterForm.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyStrategistFilterAJAX();
            }, 250);
        });
    }

    // Handle filter button
    const filterBtn = filterForm.querySelector('button[type="submit"]');
    if (filterBtn) {
        filterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            applyStrategistFilterAJAX();
        });
    }
});

/**
 * Apply strategist tracking filters via AJAX
 */
async function applyStrategistFilterAJAX() {
    const form = document.querySelector('.trk-filter-form');
    if (!form) return;

    const formData = new FormData(form);
    const queryString = new URLSearchParams(formData).toString();
    const url = `${form.action}${form.action.includes('?') ? '&' : '?'}${queryString}`;

    try {
        showTrackingLoadingState();

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const html = await response.text();
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');

        // Update header subtitle
        updateTrackingHeader(newDoc);

        // Update stat cards
        updateTrackingStats(newDoc);

        // Update task list
        updateTrackingList(newDoc);

        // Update pagination
        updateTrackingPagination(newDoc);

        // Update URL
        window.history.pushState({ path: url }, '', url);

        if (window.ajax?.showSuccess) {
    ajax.showSuccess('Filters applied!');
}

    } catch (error) {
        console.error('Tracking filter error:', error);
        if (window.ajax?.showError) {
    ajax.showError('Failed to apply filters');
} else {
    alert('Failed to apply filters');
}
    } finally {
        hideTrackingLoadingState();
    }
}

function showTrackingLoadingState() {
    const overlay = document.createElement('div');
    overlay.id = 'trackingLoadingOverlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 998;
        backdrop-filter: blur(1px);
    `;
    const spinner = document.createElement('div');
    spinner.style.cssText = `
        background: white;
        padding: 16px 32px;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    `;
    spinner.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:16px;color:var(--primary)"></i> Filtering...';
    overlay.appendChild(spinner);
    document.body.appendChild(overlay);
}

function hideTrackingLoadingState() {
    const overlay = document.getElementById('trackingLoadingOverlay');
    if (overlay) {
        setTimeout(() => overlay.remove(), 150);
    }
}

function updateTrackingHeader(newDoc) {
    const currentHeader = document.querySelector('.trk-header-left .trk-subtitle');
    const newHeader = newDoc.querySelector('.trk-header-left .trk-subtitle');
    if (currentHeader && newHeader) {
        currentHeader.textContent = newHeader.textContent;
    }
}

function updateTrackingStats(newDoc) {
    const currentStats = document.querySelector('.trk-stats-row');
    const newStats = newDoc.querySelector('.trk-stats-row');
    if (currentStats && newStats) {
        currentStats.innerHTML = newStats.innerHTML;
    }
}

function updateTrackingList(newDoc) {

    const currentList =
        document.querySelector('.trk-table-wrap');

    const newList =
        newDoc.querySelector('.trk-table-wrap');

    if (currentList && newList) {

        currentList.style.opacity = '0.5';

        setTimeout(() => {
            currentList.innerHTML = newList.innerHTML;
            currentList.style.opacity = '1';
        }, 100);
    }
}

function updateTrackingPagination(newDoc) {
    const currentPagination = document.querySelector('.trk-pagination');
    const newPagination = newDoc.querySelector('.trk-pagination');
    if (currentPagination && newPagination) {
        currentPagination.innerHTML = newPagination.innerHTML;
    }
}

</script>
@endpush
@endsection
