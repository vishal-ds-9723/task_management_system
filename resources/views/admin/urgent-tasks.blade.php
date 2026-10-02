@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title"><i class="fas fa-bolt" style="margin-right:8px;color:#EF4444"></i>Urgent Tasks</div>
        <div class="page-subtitle">Urgent tasks requiring your immediate attention</div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <a href="{{ route('admin.dashboard') }}" class="btn-sec"><i class="fas fa-arrow-left" style="margin-right:5px"></i> Dashboard</a>
    </div>
</div>

{{-- Stats strip --}}
<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:20px">
    <x-stat-card color="#EF4444" icon="fas fa-bolt" label="All-Time Urgent" :value="$totalUrgent" />
    <x-stat-card color="#F59E0B" icon="fas fa-clock" label="Last 48 Hours" :value="$recentCount" />
    <x-stat-card color="var(--blue)" icon="fas fa-spinner" label="Processing" :value="$processingCount" />
    <x-stat-card color="var(--teal)" icon="fas fa-check-circle" label="Completed" :value="$completedCount" />
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:18px;padding:14px 18px">
    @php
        $selectedUrgentStatus = request('status') === 'published' ? 'completed' : request('status');
    @endphp
    <form method="GET" action="{{ route('admin.urgent-tasks') }}" class="ut-filters" id="urgentTasksFilterForm" novalidate>
        <div class="ut-filter-group">
            <label class="ut-filter-label">Status</label>
            <select name="status" class="ut-filter-select" onchange="submitUrgentFilterForm(this.form)">
                <option value="">All Statuses</option>
                @foreach(['todo' => 'To Do', 'inprogress' => 'In Progress', 'review' => 'Review', 'pending_approval' => 'Pending Approval', 'completed' => 'Delivered'] as $val => $lbl)
                    <option value="{{ $val }}" {{ $selectedUrgentStatus === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="ut-filter-group">
            <label class="ut-filter-label">Client</label>
            <select name="client" class="ut-filter-select" onchange="submitUrgentFilterForm(this.form)">
                <option value="">All Clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ut-filter-group">
            <label class="ut-filter-label">Role</label>
            <select name="role" class="ut-filter-select" onchange="submitUrgentFilterForm(this.form)">
                <option value="">All Roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
        </div>
        <div class="ut-filter-group">
            <label class="ut-filter-label">Member</label>
            <select name="designer" class="ut-filter-select" onchange="submitUrgentFilterForm(this.form)">
                <option value="">All Members</option>
                @foreach($designers as $d)
                    <option value="{{ $d->id }}" {{ request('designer') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ut-filter-group">
            <label class="ut-filter-label">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="ut-filter-select" onchange="submitUrgentFilterForm(this.form)">
        </div>
        <div class="ut-filter-group">
            <label class="ut-filter-label">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="ut-filter-select" onchange="submitUrgentFilterForm(this.form)">
        </div>
        <div class="ut-filter-group">
            <label class="ut-filter-label">Per Page</label>
            <select name="per_page" id="perPageSelect" class="ut-filter-select" onchange="toggleCustomPerPageUrgent(this.value); submitUrgentFilterForm(this.form)">
                <option value="10" {{ $perPageRaw == 10 ? 'selected' : '' }}>10</option>
                <option value="30" {{ $perPageRaw == 30 ? 'selected' : '' }}>30</option>
                <option value="50" {{ $perPageRaw == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $perPageRaw == 100 ? 'selected' : '' }}>100</option>
                <option value="custom" {{ $perPageRaw === 'custom' ? 'selected' : '' }}>Custom</option>
            </select>
        </div>
        <div class="ut-filter-group" id="customPerPageUrgentGroup" style="display: {{ $perPageRaw === 'custom' ? 'flex' : 'none' }}">
            <label class="ut-filter-label">Count</label>
            <input type="number" name="custom_per_page" value="{{ request('custom_per_page', 100) }}" class="ut-filter-select" style="min-width: 80px;" onchange="submitUrgentFilterForm(this.form)">
        </div>
        @if(request()->hasAny(['status','client','designer','role','from','to','per_page']))
            <a href="{{ route('admin.urgent-tasks') }}" class="ut-clear-btn"><i class="fas fa-times"></i> Clear</a>
        @endif
    </form>
    <div id="utFilterValidationMsg" class="ut-filter-validation" style="display:none"></div>
</div>

{{-- Task list --}}
<div class="card" style="padding:0;overflow:hidden">
    @forelse($tasks as $task)
        @php
            $isRecent = $task->created_at->gte(now()->subDays(2));
            $taskViewUrl = route('admin.tasks.show', $task);
            $statusLabel = in_array($task->status, ['completed', 'published'], true)
                ? 'Delivered'
                : (in_array($task->status, ['review', 'pending_approval'], true)
                    ? 'Shared'
                    : ucfirst(str_replace('_', ' ', $task->status)));
            $deliveredByName = $task->assignee?->name ?? $task->creator?->name ?? 'Team member';
            $statusColors = [
                'todo' => ['bg' => 'var(--yellow-dim)', 'color' => 'var(--yellow)'],
                'inprogress' => ['bg' => 'var(--blue-dim)', 'color' => 'var(--blue)'],
                'review' => ['bg' => 'var(--purple-dim)', 'color' => 'var(--purple)'],
                'completed' => ['bg' => 'var(--teal-dim)', 'color' => 'var(--teal)'],
                'published' => ['bg' => 'var(--teal-dim)', 'color' => 'var(--teal)'],
                'pending_approval' => ['bg' => 'var(--yellow-dim)', 'color' => 'var(--yellow)'],
            ];
            $sc = $statusColors[$task->status] ?? ['bg' => 'var(--card2)', 'color' => 'var(--text3)'];
        @endphp
        <a href="{{ $taskViewUrl }}" class="ut-row {{ $isRecent ? 'ut-row-recent' : '' }}">
            {{-- Left: red dot + info --}}
            <div class="ut-row-left">
                @if($isRecent)
                    <span class="ut-dot ut-dot-pulse"></span>
                @else
                    <span class="ut-dot ut-dot-muted"></span>
                @endif
                <div class="ut-row-info">
                    <div class="ut-row-title">
                        {{ $task->title ?? 'Untitled' }}
                        @if($isRecent)
                            <span class="ut-new-badge">NEW</span>
                        @endif
                    </div>
                    <div class="ut-row-meta">
                        @if($task->client)
                            <span class="ut-meta-item">
                                <x-client-branding :client="$task->client" size="54px" width="126px" radius="6px" />
                                {{ $task->client->name }}
                            </span>
                        @endif
                        <span class="ut-meta-sep">&middot;</span>
                        <span class="ut-meta-item"><i class="fas fa-layer-group" style="font-size:9px;opacity:0.5"></i> {{ ucfirst($task->type) }}</span>
                        @if($task->urgent_requested_by)
                            <span class="ut-meta-sep">&middot;</span>
                            <span class="ut-meta-item"><i class="fas fa-bell" style="font-size:9px;opacity:0.5"></i> Requested by {{ $task->urgent_requested_by }}</span>
                        @endif
                        @if($task->creator)
                            <span class="ut-meta-sep">&middot;</span>
                            <span class="ut-meta-item"><i class="fas fa-user" style="font-size:9px;opacity:0.5"></i> {{ $task->creator->name }}</span>
                        @endif
                        @if($task->assignee)
                            <span class="ut-meta-sep">&middot;</span>
                            <span class="ut-meta-item"><i class="fas fa-paint-brush" style="font-size:9px;opacity:0.5"></i> {{ $task->assignee->name }}</span>
                        @endif
                        @if(in_array($task->status, ['completed', 'published'], true) && $task->completed_at)
                            <span class="ut-meta-sep">&middot;</span>
                            <span class="ut-meta-item" style="color:var(--teal)"><i class="fas fa-truck-fast" style="font-size:9px;opacity:0.8"></i> Delivered by {{ $deliveredByName }} {{ $task->completed_at->diffForHumans() }}</span>
                        @endif
                        <span class="ut-meta-sep">&middot;</span>
                        <span class="ut-meta-item"><i class="fas fa-clock" style="font-size:9px;opacity:0.5"></i> {{ $task->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            {{-- Right: status + priority badges --}}
            <div class="ut-row-right">
                <span class="tag" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }}">{{ $statusLabel }}</span>
                <span class="tag tag-red"><i class="fas fa-bolt" style="font-size:9px;margin-right:3px"></i> Urgent</span>
                <span class="tag" style="background:var(--card2);color:var(--text2)">{{ ucfirst($task->type) }}</span>
                @if($task->deadline)
                    <span class="ut-deadline {{ $task->deadline->isPast() && !in_array($task->status, ['completed','published']) ? 'ut-deadline-overdue' : '' }}">
                        <i class="fas fa-calendar-alt" style="font-size:9px;margin-right:3px"></i>
                        {{ $task->deadline->format('M d') }}
                    </span>
                @endif
                <i class="fas fa-chevron-right ut-row-arrow"></i>
            </div>
        </a>
    @empty
        <div class="ut-empty">
            <div class="ut-empty-icon"><i class="fas fa-check-circle"></i></div>
            <div class="ut-empty-title">No urgent tasks found</div>
            <div class="ut-empty-sub">
                @if(request()->hasAny(['status','client','designer','from','to']))
                    Try adjusting your filters
                @else
                    Designers haven't created any urgent tasks yet
                @endif
            </div>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($tasks->hasPages())
    <div style="display:flex;justify-content:center;margin-top:20px">
        {{ $tasks->links('vendor.pagination.custom') }}
    </div>
@endif

<style>
/* ── Filters ── */
.ut-filters {
    display:flex;
    align-items:flex-end;
    gap:12px;
    flex-wrap:wrap;
}
.ut-filter-group {
    display:flex;
    flex-direction:column;
    gap:4px;
}
.ut-filter-label {
    font-size:10px;
    font-weight:700;
    color:var(--text3);
    text-transform:uppercase;
    letter-spacing:0.5px;
}
.ut-filter-select {
    padding:7px 12px;
    border:1px solid var(--border);
    border-radius:8px;
    font-size:12px;
    color:var(--text);
    background:var(--card);
    min-width:130px;
    cursor:pointer;
}
.ut-filter-select:focus {
    outline:none;
    border-color:var(--primary);
}
.ut-clear-btn {
    padding:7px 14px;
    border-radius:8px;
    font-size:11px;
    font-weight:600;
    color:var(--red);
    background:var(--red-dim);
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:5px;
    transition:all 0.15s;
    align-self:flex-end;
}
.ut-clear-btn:hover {
    background:#EF4444;
    color:#fff;
}

.ut-filter-validation {
    margin-top:10px;
    font-size:12px;
    font-weight:600;
    border-radius:8px;
    padding:8px 10px;
    display:inline-flex;
    align-items:center;
    gap:6px;
}
.ut-filter-validation.error {
    color:#EF4444;
    background:#FEE2E2;
    border:1px solid #FCA5A5;
}
.ut-filter-validation.success {
    color:#15803D;
    background:#DCFCE7;
    border:1px solid #86EFAC;
}

/* ── Task rows ── */
.ut-row {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:14px 20px;
    border-bottom:1px solid var(--border);
    text-decoration:none;
    color:inherit;
    transition:all 0.15s ease;
}
.ut-row:last-child { border-bottom:none; }
.ut-row:hover {
    background:var(--card2);
}
.ut-row-recent {
    background:rgba(239,68,68,0.03);
}
.ut-row-recent:hover {
    background:rgba(239,68,68,0.06);
}

.ut-row-left {
    display:flex;
    align-items:center;
    gap:12px;
    flex:1;
    min-width:0;
}
.ut-dot {
    width:8px;
    height:8px;
    border-radius:50%;
    flex-shrink:0;
}
.ut-dot-pulse {
    background:#EF4444;
    box-shadow:0 0 0 3px rgba(239,68,68,0.15);
    animation:utDotPulse 2s ease-in-out infinite;
}
.ut-dot-muted {
    background:#D1D5DB;
}
@keyframes utDotPulse {
    0%,100% { box-shadow:0 0 0 3px rgba(239,68,68,0.15); }
    50% { box-shadow:0 0 0 6px rgba(239,68,68,0); }
}

.ut-row-info {
    flex:1;
    min-width:0;
}
.ut-row-title {
    font-size:13.5px;
    font-weight:700;
    color:var(--text);
    line-height:1.3;
    display:flex;
    align-items:center;
    gap:8px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.ut-new-badge {
    font-size:9px;
    font-weight:800;
    color:#EF4444;
    background:#FEE2E2;
    padding:2px 7px;
    border-radius:4px;
    letter-spacing:0.5px;
    flex-shrink:0;
}
.ut-row-meta {
    font-size:11.5px;
    color:var(--text3);
    margin-top:3px;
    display:flex;
    align-items:center;
    gap:5px;
    flex-wrap:wrap;
}
.ut-meta-item {
    display:inline-flex;
    align-items:center;
    gap:4px;
    white-space:nowrap;
}
.ut-meta-sep {
    color:var(--border);
}

.ut-row-right {
    display:flex;
    align-items:center;
    gap:8px;
    flex-shrink:0;
}
.ut-deadline {
    font-size:11px;
    font-weight:600;
    color:var(--text3);
    display:inline-flex;
    align-items:center;
}
.ut-deadline-overdue {
    color:#EF4444;
    font-weight:700;
}
.ut-row-arrow {
    font-size:10px;
    color:var(--text3);
    opacity:0;
    transform:translateX(-4px);
    transition:all 0.15s;
}
.ut-row:hover .ut-row-arrow {
    opacity:1;
    transform:translateX(0);
    color:var(--primary);
}

/* ── Empty state ── */
.ut-empty {
    padding:60px 20px;
    text-align:center;
}
.ut-empty-icon {
    font-size:40px;
    color:var(--teal);
    opacity:0.5;
    margin-bottom:12px;
}
.ut-empty-title {
    font-size:16px;
    font-weight:700;
    color:var(--text);
    font-family:'Plus Jakarta Sans',sans-serif;
}
.ut-empty-sub {
    font-size:13px;
    color:var(--text3);
    margin-top:4px;
}

/* ── Responsive ── */
@media (max-width:768px) {
    .ut-filters { flex-direction:column; gap:8px; }
    .ut-filter-select { min-width:100%; }
    .ut-row { flex-direction:column; align-items:flex-start; gap:10px; padding:12px 14px; }
    .ut-row-right { flex-wrap:wrap; }
    .ut-row-arrow { display:none; }
}
</style>

<script>
function submitUrgentFilterForm(form) {
    if (!form) return;
    if (!validateUrgentTaskFilters(form, { showSuccess: true })) return;
    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
    } else {
        form.submit();
    }
}

function validateUrgentTaskFilters(form, options = {}) {
    const showSuccess = !!options.showSuccess;
    const fromEl = form.querySelector('[name="from"]');
    const toEl = form.querySelector('[name="to"]');
    const statusEl = form.querySelector('[name="status"]');
    const clientEl = form.querySelector('[name="client"]');
    const designerEl = form.querySelector('[name="designer"]');
    const roleEl = form.querySelector('[name="role"]');
    const msgEl = document.getElementById('utFilterValidationMsg');

    if (!fromEl || !toEl || !msgEl) return true;

    const from = (fromEl.value || '').trim();
    const to = (toEl.value || '').trim();
    const hasAnyFilter = [
        statusEl?.value,
        clientEl?.value,
        designerEl?.value,
        roleEl?.value,
        from,
        to,
    ].some(Boolean);

    const setFieldState = (el, state) => {
        if (!el) return;
        if (state === 'error') {
            el.style.borderColor = '#EF4444';
            el.style.boxShadow = '0 0 0 2px rgba(239,68,68,0.12)';
            return;
        }
        if (state === 'success') {
            el.style.borderColor = '#16A34A';
            el.style.boxShadow = '0 0 0 2px rgba(22,163,74,0.12)';
            return;
        }
        el.style.borderColor = '';
        el.style.boxShadow = '';
    };

    const showMessage = (type, text) => {
        msgEl.className = 'ut-filter-validation ' + type;
        msgEl.innerHTML = (type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>') + text;
        msgEl.style.display = 'inline-flex';
    };

    const hideMessage = () => {
        msgEl.style.display = 'none';
        msgEl.className = 'ut-filter-validation';
        msgEl.innerHTML = '';
    };

    setFieldState(fromEl, null);
    setFieldState(toEl, null);

    if (from && to && from > to) {
        setFieldState(fromEl, 'error');
        setFieldState(toEl, 'error');
        showMessage('error', 'From date cannot be later than To date.');
        return false;
    }

    if (from) setFieldState(fromEl, 'success');
    if (to) setFieldState(toEl, 'success');

    if (showSuccess && hasAnyFilter) {
        showMessage('success', 'Filters look good.');
    } else {
        hideMessage();
    }

    return true;
}

function toggleCustomPerPageUrgent(val) {
    const group = document.getElementById('customPerPageUrgentGroup');
    if (group) group.style.display = (val === 'custom') ? 'flex' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('urgentTasksFilterForm');
    if (!form) return;

    const watched = ['status', 'client', 'designer', 'role', 'from', 'to', 'per_page', 'custom_per_page'];
    watched.forEach(function(name) {
        const el = form.querySelector('[name="' + name + '"]');
        if (!el) return;
        el.addEventListener('input', function() {
            validateUrgentTaskFilters(form, { showSuccess: true });
        });
        el.addEventListener('change', function() {
            validateUrgentTaskFilters(form, { showSuccess: true });
        });
    });

    form.addEventListener('submit', function(e) {
        if (!validateUrgentTaskFilters(form, { showSuccess: true })) {
            e.preventDefault();
        }
    });

    validateUrgentTaskFilters(form, { showSuccess: false });
});
</script>
@endsection
