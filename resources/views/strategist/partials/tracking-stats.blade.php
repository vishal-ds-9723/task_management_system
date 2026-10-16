@php
    $currentStatus = request('status', 'all');
    if (request('filter') === 'urgent') {
        $currentStatus = 'urgent';
    }
@endphp

<a href="javascript:void(0)" onclick="filterByStatus('all')" class="trk-stat {{ !$currentStatus || $currentStatus === 'all' ? 'trk-stat-active' : '' }}">
    <div class="trk-stat-icon" style="background:var(--blue-dim);color:var(--blue)"><i class="fa-solid fa-layer-group"></i></div>
    <div class="trk-stat-body">
        <div class="trk-stat-val">{{ $totalCount }}</div>
        <div class="trk-stat-label">Total</div>
    </div>
</a>

<a href="javascript:void(0)" onclick="filterByStatus('todo')" class="trk-stat {{ $currentStatus === 'todo' ? 'trk-stat-active' : '' }}">
    <div class="trk-stat-icon" style="background:var(--card2);color:var(--text3)"><i class="fa-solid fa-clipboard-list"></i></div>
    <div class="trk-stat-body">
        <div class="trk-stat-val">{{ $todoCount }}</div>
        <div class="trk-stat-label">To Do</div>
    </div>
</a>

<a href="javascript:void(0)" onclick="filterByStatus('inprogress')" class="trk-stat {{ $currentStatus === 'inprogress' ? 'trk-stat-active' : '' }}">
    <div class="trk-stat-icon" style="background:var(--blue-dim);color:var(--blue)"><i class="fa-solid fa-person-running"></i></div>
    <div class="trk-stat-body">
        <div class="trk-stat-val">{{ $inProgressCount }}</div>
        <div class="trk-stat-label">In Progress</div>
    </div>
</a>

<a href="javascript:void(0)" onclick="filterByStatus('review')" class="trk-stat {{ $currentStatus === 'review' ? 'trk-stat-active' : '' }}">
    <div class="trk-stat-icon" style="background:var(--yellow-dim);color:var(--yellow)"><i class="fa-solid fa-flask"></i></div>
    <div class="trk-stat-body">
        <div class="trk-stat-val">{{ $reviewCount }}</div>
        <div class="trk-stat-label">Review</div>
    </div>
</a>

{{-- In Revision Quick Filter --}}
<a href="javascript:void(0)" onclick="filterByStatus('in_revision')" class="trk-stat trk-stat-revision {{ in_array($currentStatus, ['in_revision', 'revision']) ? 'trk-stat-active' : '' }}" title="Tasks actively being revised by designer">
    <div class="trk-stat-icon" style="background:rgba(245,158,11,0.12);color:#D97706"><i class="fa-solid fa-rotate-left"></i></div>
    <div class="trk-stat-body">
        <div class="trk-stat-val" style="color:#D97706">{{ $inRevisionCount ?? 0 }}</div>
        <div class="trk-stat-label">In Revision</div>
    </div>
</a>

<a href="javascript:void(0)" onclick="filterByStatus('completed')" class="trk-stat {{ $currentStatus === 'completed' ? 'trk-stat-active' : '' }}">
    <div class="trk-stat-icon" style="background:var(--teal-dim);color:var(--teal)"><i class="fa-solid fa-circle-check"></i></div>
    <div class="trk-stat-body">
        <div class="trk-stat-val">{{ $completedCount }}</div>
        <div class="trk-stat-label">Done</div>
    </div>
</a>

@if(($overdueCount ?? 0) > 0)
<a href="javascript:void(0)" onclick="filterByStatus('overdue')" class="trk-stat trk-stat-danger {{ $currentStatus === 'overdue' ? 'trk-stat-active' : '' }}">
    <div class="trk-stat-icon" style="background:var(--red-dim);color:var(--red)"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div class="trk-stat-body">
        <div class="trk-stat-val" style="color:var(--red)">{{ $overdueCount }}</div>
        <div class="trk-stat-label">Overdue</div>
    </div>
</a>
@endif

@if(($urgentCount ?? 0) > 0 || ($systemUrgentCount ?? 0) > 0)
<a href="javascript:void(0)" onclick="filterByStatus('urgent')" class="trk-stat trk-stat-urgent {{ $currentStatus === 'urgent' ? 'trk-stat-active' : '' }}" style="border-color:rgba(239,68,68,0.3)" title="Urgent tasks requiring fast-track attention">
    <div class="trk-stat-icon" style="background:rgba(239,68,68,0.15);color:#EF4444;position:relative">
        <i class="fa-solid fa-bolt"></i>
        <span class="trk-pulse-dot" style="position:absolute;top:2px;right:2px;background:#EF4444"></span>
    </div>
    <div class="trk-stat-body">
        <div class="trk-stat-val" style="color:#EF4444">{{ $urgentCount > 0 ? $urgentCount : $systemUrgentCount }}</div>
        <div class="trk-stat-label" style="color:#EF4444">Urgent</div>
    </div>
</a>
@endif
