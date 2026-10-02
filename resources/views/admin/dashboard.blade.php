@extends('layouts.app')

@section('content')
@php
    $hour = now()->hour;
    $timeClass = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');
    $timeEmoji = $hour < 12 ? '<i class="fas fa-sun" style="color:#F59E0B"></i>' : ($hour < 17 ? '<i class="fas fa-cloud-sun" style="color:#F59E0B"></i>' : '<i class="fas fa-moon" style="color:#8B5CF6"></i>');
    $awayMode = \App\Models\Setting::isStrategistAway();
@endphp

<div class="db-dashboard-wrapper" id="dashboardMainContainer">

    {{-- ── Topbar / Header ── --}}
    <div class="db-header">
        <div class="db-header-left">
            <div class="db-title-row">
                <h1 class="db-title">Command Center</h1>
                <span class="db-live-pill"><span class="db-live-dot"></span> Live</span>
            </div>
            <div class="db-subtitle">{{ now()->format('l, F d, Y') }} — Real-time agency operations & performance</div>
        </div>
        <div class="db-header-actions">
            {{-- Strategist Away Mode Toggle --}}
            <form method="POST" action="{{ route('admin.settings.toggle-away-mode') }}" style="margin:0">
                @csrf
                <button type="submit" class="db-btn db-btn-away {{ $awayMode ? 'active' : '' }}" title="{{ $awayMode ? 'Strategist Away Mode is ON — tasks bypass strategist review' : 'Turn on if strategist is unavailable' }}">
                    <i class="fas fa-circle" style="color:{{ $awayMode ? '#D97706' : '#10B981' }};font-size:9px"></i>
                    <span>{{ $awayMode ? 'Away Mode ON' : 'Away Mode' }}</span>
                </button>
            </form>
            <a href="{{ route('admin.audit-logs') }}" class="db-btn db-btn-ghost"><i class="fas fa-history"></i> Audit Logs</a>
            <a href="{{ route('admin.tasks.create') }}" class="db-btn db-btn-primary"><i class="fas fa-plus"></i> New Task</a>
        </div>
    </div>

    {{-- ── Strategist Away Mode Alert Banner (if active) ── --}}
    @if($awayMode)
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 18px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:var(--db-card-radius,#14px)">
        <div style="display:flex;align-items:center;gap:10px">
            <i class="fas fa-shield-halved" style="color:#D97706;font-size:16px"></i>
            <div>
                <strong style="color:#D97706;font-size:13px">Strategist Away Mode is Active</strong>
                <div style="font-size:11.5px;color:var(--db-text-muted,#94A3B8)">Designer submissions bypass strategist review and route directly to admin.</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.settings.toggle-away-mode') }}" style="margin:0">
            @csrf
            <button type="submit" class="db-btn db-btn-ghost" style="padding:5px 12px;font-size:11.5px;color:#D97706;border-color:rgba(245,158,11,0.3)">Turn Off</button>
        </form>
    </div>
    @endif

    {{-- ── Executive Welcome & Quick Status Strip ── --}}
    <div class="db-welcome-strip">
        <div class="db-welcome-left">
            <div class="db-welcome-user">
                Good {{ ucfirst($timeClass) }}, <strong>{{ auth()->user()->name }}</strong>
            </div>
            <div class="db-welcome-chips">
                @if($delayedTasks > 0)
                <a href="{{ route('admin.tasks') }}" class="db-chip db-chip-danger" title="View overdue tasks">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span>{{ $delayedTasks }} Overdue</span>
                </a>
                @endif
                @if($pendingReview > 0)
                <a href="{{ route('admin.tasks') }}" class="db-chip db-chip-warning" title="View pending review tasks">
                    <i class="fas fa-hourglass-half"></i>
                    <span>{{ $pendingReview }} In Review</span>
                </a>
                @endif
                <span class="db-chip db-chip-success">
                    <i class="fas fa-circle-check"></i>
                    <span>{{ $completedToday }} Done Today</span>
                </span>
                @if($tasksCreatedToday > 0)
                <span class="db-chip db-chip-info">
                    <i class="fas fa-plus-circle"></i>
                    <span>{{ $tasksCreatedToday }} New Today</span>
                </span>
                @endif
            </div>
        </div>
        <div class="db-welcome-right">
            <div class="db-clock-pill">
                <span id="ghClockTime">{{ now()->format('h:i') }}</span>
                <span id="ghClockAmpm" style="font-size:10px;color:var(--db-text-muted)">{{ now()->format('A') }}</span>
            </div>
            <div style="font-size:15px">{!! $timeEmoji !!}</div>
        </div>
    </div>

    {{-- ── KPI Metrics Grid (8 High-Impact Cards) ── --}}
    @php
        $statusLabelsMap = ['todo' => 'To Do', 'inprogress' => 'In Progress', 'review' => 'Review', 'completed' => 'Completed'];
        $filterStatusLabel = ($filterStatus && is_string($filterStatus)) ? ($statusLabelsMap[$filterStatus] ?? ucfirst($filterStatus)) : null;
        $filterContext = collect(array_filter([
            request('client_id') ? ($clients->firstWhere('id', request('client_id'))->name ?? 'Client') : null,
            request('assigned_to') ? ($designers->firstWhere('id', request('assigned_to'))->name ?? 'Member') : null,
            $filterStatusLabel,
            request('priority') ? ucfirst(request('priority')) : null,
            request('platform') ? ucfirst(request('platform')) : null,
        ]))->implode(' · ');
        $hasAnyFilter = request()->hasAny(['client_id', 'assigned_to', 'status', 'priority', 'platform', 'date_range']);
    @endphp

    <div class="db-metrics">
        {{-- Total Tasks --}}
        <div class="db-metric-card" onclick="showMetricTasks('total')" style="--mc:#6366F1">
            @if($hasAnyFilter)<span class="db-mc-filter-badge"><i class="fas fa-filter"></i></span>@endif
            <div class="db-mc-header">
                <div class="db-mc-icon-wrap"><i class="fas fa-clipboard-list"></i></div>
                @if($tasksCreatedToday > 0)
                <span class="db-mc-trend up">+{{ $tasksCreatedToday }} new</span>
                @endif
            </div>
            <div class="db-mc-body">
                <div class="db-mc-val">{{ $totalTasks }}</div>
                <div class="db-mc-label">Total Tasks</div>
                <div class="db-mc-sub">+{{ $tasksCreatedToday }} added today</div>
            </div>
        </div>

        {{-- In Progress --}}
        <div class="db-metric-card{{ $filterStatus && $filterStatus !== 'inprogress' ? ' db-mc-dimmed' : '' }}" onclick="showMetricTasks('inprogress')" style="--mc:#3B82F6">
            @if($hasAnyFilter)<span class="db-mc-filter-badge"><i class="fas fa-filter"></i></span>@endif
            <div class="db-mc-header">
                <div class="db-mc-icon-wrap"><i class="fas fa-arrows-rotate"></i></div>
            </div>
            <div class="db-mc-body">
                <div class="db-mc-val">{{ $inProgress }}</div>
                <div class="db-mc-label">In Progress</div>
                <div class="db-mc-sub">{{ $filterStatus && $filterStatus !== 'inprogress' ? 'Excluded by filter' : 'Active in production' }}</div>
            </div>
        </div>

        {{-- Pending Review --}}
        <div class="db-metric-card{{ $filterStatus && $filterStatus !== 'review' ? ' db-mc-dimmed' : '' }}" onclick="showMetricTasks('review')" style="--mc:#F59E0B">
            @if($hasAnyFilter)<span class="db-mc-filter-badge"><i class="fas fa-filter"></i></span>@endif
            <div class="db-mc-header">
                <div class="db-mc-icon-wrap"><i class="fas fa-hourglass-half"></i></div>
            </div>
            <div class="db-mc-body">
                <div class="db-mc-val">{{ $pendingReview }}</div>
                <div class="db-mc-label">Pending Review</div>
                <div class="db-mc-sub">{{ $filterStatus && $filterStatus !== 'review' ? 'Excluded by filter' : 'Awaiting sign-off' }}</div>
            </div>
        </div>

        {{-- Done This Week --}}
        <div class="db-metric-card{{ $filterStatus && $filterStatus !== 'completed' ? ' db-mc-dimmed' : '' }}" onclick="showMetricTasks('completed_week')" style="--mc:#10B981">
            @if($hasAnyFilter)<span class="db-mc-filter-badge"><i class="fas fa-filter"></i></span>@endif
            <div class="db-mc-header">
                <div class="db-mc-icon-wrap"><i class="fas fa-circle-check"></i></div>
                @if(!$filterStatus && isset($kpiTrends['completed_week']) && $kpiTrends['completed_week']['dir'] !== 'neutral')
                <span class="db-mc-trend {{ $kpiTrends['completed_week']['dir'] }}">
                    {{ $kpiTrends['completed_week']['dir'] === 'up' ? '↑' : '↓' }} {{ $kpiTrends['completed_week']['pct'] }}%
                </span>
                @endif
            </div>
            <div class="db-mc-body">
                <div class="db-mc-val">{{ $completedWeek }}</div>
                <div class="db-mc-label">Done This Week</div>
                <div class="db-mc-sub">{{ $completedMonth }} completed this month</div>
            </div>
        </div>

        {{-- Overdue Tasks --}}
        <div class="db-metric-card{{ $filterStatus === 'completed' ? ' db-mc-dimmed' : '' }}" onclick="showMetricTasks('overdue')" style="--mc:#EF4444">
            @if($hasAnyFilter)<span class="db-mc-filter-badge"><i class="fas fa-filter"></i></span>@endif
            <div class="db-mc-header">
                <div class="db-mc-icon-wrap"><i class="fas fa-triangle-exclamation"></i></div>
                @if($delayedTasks > 0)
                <span class="db-mc-trend down">Action Required</span>
                @endif
            </div>
            <div class="db-mc-body">
                <div class="db-mc-val" style="{{ $delayedTasks > 0 ? 'color:#EF4444' : '' }}">{{ $delayedTasks }}</div>
                <div class="db-mc-label">Overdue Tasks</div>
                <div class="db-mc-sub">{{ $delayedTasks > 0 ? 'Past agreed deadline' : 'All deliverables on track' }}</div>
            </div>
        </div>

        {{-- Active Clients --}}
        <div class="db-metric-card" onclick="window.location.href='{{ route('admin.clients') }}'" style="--mc:#8B5CF6">
            @if($hasAnyFilter)<span class="db-mc-filter-badge"><i class="fas fa-filter"></i></span>@endif
            <div class="db-mc-header">
                <div class="db-mc-icon-wrap"><i class="fas fa-building-user"></i></div>
            </div>
            <div class="db-mc-body">
                <div class="db-mc-val">{{ $activeClients }}</div>
                <div class="db-mc-label">Active Clients</div>
                <div class="db-mc-sub">With active projects</div>
            </div>
        </div>

        {{-- To Do --}}
        <div class="db-metric-card{{ $filterStatus && $filterStatus !== 'todo' ? ' db-mc-dimmed' : '' }}" onclick="showMetricTasks('todo')" style="--mc:#F97316">
            @if($hasAnyFilter)<span class="db-mc-filter-badge"><i class="fas fa-filter"></i></span>@endif
            <div class="db-mc-header">
                <div class="db-mc-icon-wrap"><i class="fas fa-list-check"></i></div>
            </div>
            <div class="db-mc-body">
                <div class="db-mc-val">{{ $todoTasks }}</div>
                <div class="db-mc-label">To Do Queue</div>
                <div class="db-mc-sub">Ready to be started</div>
            </div>
        </div>

        {{-- Unassigned --}}
        <div class="db-metric-card{{ $filterStatus && !in_array($filterStatus, ['todo', 'inprogress']) ? ' db-mc-dimmed' : '' }}" onclick="showMetricTasks('unassigned')" style="--mc:#EC4899">
            @if($hasAnyFilter)<span class="db-mc-filter-badge"><i class="fas fa-filter"></i></span>@endif
            <div class="db-mc-header">
                <div class="db-mc-icon-wrap"><i class="fas fa-user-xmark"></i></div>
            </div>
            <div class="db-mc-body">
                <div class="db-mc-val">{{ $unassignedTasks }}</div>
                <div class="db-mc-label">Unassigned</div>
                <div class="db-mc-sub">Needs team member</div>
            </div>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <form id="dashboardFilters" method="GET" action="{{ route('admin.dashboard') }}" class="db-filter-bar">
        <div class="db-filter-group">
            <label class="db-filter-label"><i class="fas fa-building"></i> Client</label>
            <x-client-select :clients="$clients" name="client_id" value="{{ request('client_id') }}" placeholder="All Clients" class="db-filter-select{{ request('client_id') ? ' db-filter-active' : '' }}" />
        </div>
        <div class="db-filter-group">
            <label class="db-filter-label"><i class="fas fa-user"></i> Assignee</label>
            <select name="assigned_to" class="db-filter-select{{ request('assigned_to') ? ' db-filter-active' : '' }}">
                <option value="">All Members</option>
                @foreach($designers as $d)
                    <option value="{{ $d->id }}" {{ request('assigned_to') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="db-filter-group">
            <label class="db-filter-label"><i class="fas fa-signal"></i> Status</label>
            <select name="status" class="db-filter-select{{ request('status') ? ' db-filter-active' : '' }}">
                <option value="">All Statuses</option>
                <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                <option value="inprogress" {{ request('status') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                <option value="review" {{ request('status') == 'review' ? 'selected' : '' }}>Review</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <div class="db-filter-group">
            <label class="db-filter-label"><i class="fas fa-flag"></i> Priority</label>
            <select name="priority" class="db-filter-select{{ request('priority') ? ' db-filter-active' : '' }}">
                <option value="">All Priorities</option>
                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
            </select>
        </div>
        <div class="db-filter-group">
            <label class="db-filter-label"><i class="fas fa-shapes"></i> Platform</label>
            <select name="platform" class="db-filter-select{{ request('platform') ? ' db-filter-active' : '' }}">
                <option value="">All Platforms</option>
                <option value="instagram" {{ request('platform') == 'instagram' ? 'selected' : '' }}>Instagram</option>
                <option value="facebook" {{ request('platform') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                <option value="linkedin" {{ request('platform') == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                <option value="twitter" {{ request('platform') == 'twitter' ? 'selected' : '' }}>Twitter</option>
                <option value="whatsapp" {{ request('platform') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
            </select>
        </div>
        <div class="db-filter-group">
            <label class="db-filter-label"><i class="fas fa-calendar"></i> Period</label>
            <select name="date_range" class="db-filter-select{{ request('date_range') ? ' db-filter-active' : '' }}">
                <option value="">All Time</option>
                <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
                <option value="last_week" {{ request('date_range') == 'last_week' ? 'selected' : '' }}>Last Week</option>
                <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                <option value="this_quarter" {{ request('date_range') == 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>This Year</option>
            </select>
        </div>
        <div class="db-filter-actions">
            @if($hasAnyFilter)
                <a href="{{ route('admin.dashboard') }}" class="db-filter-clear"><i class="fas fa-rotate-left"></i> Reset</a>
            @endif
        </div>
    </form>

    {{-- ── Active Filter Summary Pills ── --}}
    @php
        $activeFilters = [];
        if (request('client_id')) {
            $fClient = $clients->firstWhere('id', request('client_id'));
            $activeFilters[] = ['icon' => 'fa-building', 'label' => 'Client', 'value' => $fClient ? $fClient->name : '#' . request('client_id')];
        }
        if (request('assigned_to')) {
            $fDesigner = $designers->firstWhere('id', request('assigned_to'));
            $activeFilters[] = ['icon' => 'fa-user', 'label' => 'Assignee', 'value' => $fDesigner->name ?? '#' . request('assigned_to')];
        }
        if (request('status')) {
            $activeFilters[] = ['icon' => 'fa-signal', 'label' => 'Status', 'value' => $statusLabelsMap[request('status')] ?? ucfirst(request('status'))];
        }
        if (request('priority')) {
            $activeFilters[] = ['icon' => 'fa-flag', 'label' => 'Priority', 'value' => ucfirst(request('priority'))];
        }
        if (request('platform')) {
            $activeFilters[] = ['icon' => 'fa-shapes', 'label' => 'Platform', 'value' => ucfirst(request('platform'))];
        }
        if (request('date_range')) {
            $dateLabels = ['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'last_week' => 'Last Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_quarter' => 'This Quarter', 'this_year' => 'This Year'];
            $activeFilters[] = ['icon' => 'fa-calendar', 'label' => 'Period', 'value' => $dateLabels[request('date_range')] ?? request('date_range')];
        }
    @endphp

    <div id="dbFilterSummary">
        @if(count($activeFilters) > 0)
        <div class="db-filter-summary">
            <div class="db-filter-tags">
                <span style="font-size:11px;font-weight:700;color:var(--db-text-muted);text-transform:uppercase;letter-spacing:0.04em"><i class="fas fa-filter" style="margin-right:4px"></i> Active:</span>
                @foreach($activeFilters as $af)
                    <span class="db-filter-tag"><i class="fas {{ $af['icon'] }}" style="color:#6366F1"></i> {{ $af['label'] }}: <strong>{{ $af['value'] }}</strong></span>
                @endforeach
            </div>
            <a href="{{ route('admin.dashboard') }}" class="db-filter-summary-clear"><i class="fas fa-times"></i> Clear All</a>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- ── SYMMETRICAL 2-COLUMN DASHBOARD GRID ── --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div class="db-command-grid">

        {{-- ════════ ROW 1: ACTION CENTER & DESIGNER WORKLOAD ════════ --}}
        <div class="db-sym-row">

            {{-- ── Card 1: Action Center ── --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <i class="fas fa-bolt" style="color:#EF4444"></i>
                        Action Center
                    </h2>
                    <a href="{{ route('admin.tasks') }}" class="db-card-link">
                        View All Tasks <i class="fas fa-chevron-right" style="font-size:10px"></i>
                    </a>
                </div>

                {{-- Segmented Navigation Bar --}}
                <div class="db-segmented-nav">
                    <button type="button" class="db-seg-btn active" id="segBtn-overdue" onclick="switchActionTab('overdue')">
                        <i class="fas fa-triangle-exclamation" style="color:#EF4444"></i>
                        Overdue
                        <span class="db-seg-badge db-seg-badge-red">{{ $overdueTasks->count() }}</span>
                    </button>
                    <button type="button" class="db-seg-btn" id="segBtn-upcoming" onclick="switchActionTab('upcoming')">
                        <i class="fas fa-clock" style="color:#F59E0B"></i>
                        Upcoming
                        <span class="db-seg-badge db-seg-badge-yellow">{{ $upcomingDeadlines->count() }}</span>
                    </button>
                    @if($dateChangeRequests->count() > 0)
                    <button type="button" class="db-seg-btn" id="segBtn-datereqs" onclick="switchActionTab('datereqs')">
                        <i class="fas fa-calendar-alt" style="color:#6366F1"></i>
                        Date Requests
                        <span class="db-seg-badge db-seg-badge-red">{{ $dateChangeRequests->count() }}</span>
                    </button>
                    @endif
                </div>

                {{-- Tab Pane: Overdue Tasks --}}
                <div class="db-seg-pane active" id="segPane-overdue">
                    <div class="db-task-list">
                        @forelse($overdueTasks as $ot)
                            <a href="{{ route('admin.tasks.show', $ot) }}" class="db-task-item is-overdue">
                                <div class="db-task-left">
                                    <x-client-branding :client="$ot->client" size="26px" width="56px" radius="6px" />
                                    <div class="db-task-info">
                                        <div class="db-task-client-line">{{ $ot->client->name ?? 'No Client' }}</div>
                                        <div class="db-task-name">{{ $ot->title ?? 'Untitled Task' }}</div>
                                        <div class="db-task-meta-line">
                                            <span class="db-task-tag db-task-tag-red">
                                                <i class="fas fa-clock" style="margin-right:3px"></i>{{ $ot->deadline?->diffForHumans() }}
                                            </span>
                                            <span>{{ ucfirst($ot->type) }} · {{ is_array($ot->platform) ? implode(', ', array_map('ucfirst', $ot->platform)) : ucfirst($ot->platform) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="db-task-right">
                                    @if($ot->assignee)
                                        <div class="av-sm" style="background:{{ $ot->assignee->avatar_color ?? '#6366F1' }};width:26px;height:26px;font-size:10px;border-radius:8px" title="{{ $ot->assignee->name }}">
                                            {{ $ot->assignee->initial ?? '?' }}
                                        </div>
                                    @else
                                        <span class="db-task-tag db-task-tag-red">Unassigned</span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="db-empty">
                                <i class="fas fa-circle-check" style="color:#10B981;font-size:28px"></i>
                                <span>All clear! No overdue tasks right now.</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tab Pane: Upcoming Deadlines --}}
                <div class="db-seg-pane" id="segPane-upcoming">
                    <div class="db-task-list">
                        @forelse($upcomingDeadlines as $ud)
                            <a href="{{ route('admin.tasks.show', $ud) }}" class="db-task-item">
                                <div class="db-task-left">
                                    <x-client-branding :client="$ud->client" size="26px" width="56px" radius="6px" />
                                    <div class="db-task-info">
                                        <div class="db-task-client-line">{{ $ud->client->name ?? 'No Client' }}</div>
                                        <div class="db-task-name">{{ $ud->title ?? 'Untitled Task' }}</div>
                                        <div class="db-task-meta-line">
                                            <span class="db-task-tag {{ $ud->deadline->isToday() ? 'db-task-tag-red' : ($ud->deadline->isTomorrow() ? 'db-task-tag-amber' : 'db-task-tag-blue') }}">
                                                {{ $ud->deadline->isToday() ? 'TODAY' : ($ud->deadline->isTomorrow() ? 'Tomorrow' : $ud->deadline->format('D, M d')) }}
                                            </span>
                                            <span class="status-badge {{ $ud->status }}" style="font-size:9.5px;padding:1px 6px">{{ ucfirst($ud->status) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="db-task-right">
                                    @if($ud->assignee)
                                        <div class="av-sm" style="background:{{ $ud->assignee->avatar_color ?? '#6366F1' }};width:26px;height:26px;font-size:10px;border-radius:8px" title="{{ $ud->assignee->name }}">
                                            {{ $ud->assignee->initial ?? '?' }}
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="db-empty">
                                <i class="fas fa-calendar-check" style="color:#10B981;font-size:28px"></i>
                                <span>No upcoming deadlines in the next 7 days.</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tab Pane: Date Change Requests (if any) --}}
                @if($dateChangeRequests->count() > 0)
                <div class="db-seg-pane" id="segPane-datereqs">
                    <div class="db-task-list">
                        @foreach($dateChangeRequests as $req)
                            @php
                                $task = $req->task;
                                preg_match('/→\s*([\d]{4}-[\d]{2}-[\d]{2}|[\d]{2}\s\w+\s[\d]{4}|\S+)$/', $req->subtitle ?? '', $dateMatch);
                                $requestedDate = $dateMatch[1] ?? '';
                                $requesterName = $task?->creator?->name ?? 'Team Member';
                                $parsedDate = null;
                                try { $parsedDate = $requestedDate ? \Carbon\Carbon::parse($requestedDate) : null; } catch (\Exception $e) {}
                            @endphp
                            @if($task)
                            <div class="db-dcr-item">
                                <div class="db-dcr-header">
                                    <div style="flex:1;min-width:0">
                                        <div style="font-size:13px;font-weight:700;color:var(--db-text-primary)">{{ $task->title }}</div>
                                        <div style="font-size:11px;color:var(--db-text-muted);margin-top:2px">
                                            {{ $task->client->name ?? '' }} · Requested by <strong>{{ $requesterName }}</strong> · {{ $req->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                                <div class="db-dcr-dates">
                                    <span>Current: <strong>{{ $task->post_date?->format('d M Y') ?? 'Not set' }}</strong></span>
                                    <span style="color:#6366F1;font-weight:700">→</span>
                                    <span>Requested: <strong style="color:#EF4444">{{ $parsedDate ? $parsedDate->format('d M Y') : $requestedDate }}</strong></span>
                                </div>
                                <div class="db-dcr-actions">
                                    <form method="POST" action="{{ route('admin.tasks.approve-date', $task) }}" style="margin:0">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="new_post_date" value="{{ $requestedDate }}">
                                        <input type="hidden" name="notification_id" value="{{ $req->id }}">
                                        <button type="submit" class="db-dcr-btn db-dcr-btn-approve" onclick="return confirm('Approve post date change to {{ $requestedDate }}?')">
                                             <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <button type="button" class="db-dcr-btn db-dcr-btn-decline" onclick="toggleRejectForm({{ $req->id }})">
                                        <i class="fas fa-times"></i> Decline
                                    </button>
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="db-dcr-btn db-dcr-btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                                <div id="rejectForm-{{ $req->id }}" style="display:none;margin-top:8px">
                                    <form method="POST" action="{{ route('admin.tasks.reject-date', $task) }}" style="display:flex;gap:8px;align-items:center">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="notification_id" value="{{ $req->id }}">
                                        <input type="text" name="reject_reason" placeholder="Reason for declining (optional)" style="flex:1;padding:6px 10px;border:1px solid var(--db-card-border);border-radius:7px;font-size:12px;background:var(--db-card-bg-subtle);color:var(--db-text-primary)">
                                        <button type="submit" class="db-btn db-btn-primary" style="padding:6px 12px;font-size:11.5px">Confirm</button>
                                        <button type="button" class="db-btn db-btn-ghost" style="padding:6px 10px;font-size:11.5px" onclick="toggleRejectForm({{ $req->id }})">Cancel</button>
                                    </form>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- ── Card 2: Designer Workload & Capacity ── --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <i class="fas fa-users" style="color:#8B5CF6"></i>
                        Designer Workload
                    </h2>
                    <a href="{{ route('admin.workload') }}" class="db-card-link">
                        Manage <i class="fas fa-chevron-right" style="font-size:10px"></i>
                    </a>
                </div>
                <div class="db-card-body-flush">
                    <div class="db-intel-list">
                        @forelse($workloadByDesigner as $designer)
                            @php
                                $maxLoad = max(1, $workloadByDesigner->max('active_tasks_count'));
                                $barPct = $maxLoad > 0 ? round(($designer->active_tasks_count / $maxLoad) * 100) : 0;
                            @endphp
                            <div class="db-intel-row">
                                <div class="db-workload-left">
                                    <div class="av-sm" style="background:{{ $designer->avatar_color ?? '#6366F1' }};width:28px;height:28px;font-size:11px;border-radius:8px">
                                        {{ $designer->initial }}
                                    </div>
                                    <div>
                                        <div class="db-workload-name">{{ $designer->name }}</div>
                                        <div class="db-workload-meta">{{ $designer->active_tasks_count }} active tasks</div>
                                    </div>
                                </div>
                                <div class="db-workload-right">
                                    <div class="db-progress-track">
                                        <div class="db-progress-fill" style="width:{{ $barPct }}%;background:{{ $designer->load_color ?? '#6366F1' }}"></div>
                                    </div>
                                    <div class="db-workload-badges">
                                        @if($designer->overdue_tasks_count > 0)
                                            <span class="db-task-tag db-task-tag-red" style="font-size:9px">{{ $designer->overdue_tasks_count }} overdue</span>
                                        @endif
                                        <span class="db-load-pill" style="--pill-color:{{ $designer->load_color ?? '#6366F1' }}">{{ $designer->load_level }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="db-empty">
                                <i class="fas fa-users"></i>
                                <span>No designer workload data available</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- ════════ ROW 2: PUBLISHING / DEV OPERATIONS & EFFICIENCY ════════ --}}
        <div class="db-sym-row">

            {{-- ── Card 3: Publishing & Dev Operations Hub ── --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <i class="fas fa-tower-broadcast" style="color:#EF4444"></i>
                        Publishing & Projects
                    </h2>
                    <a href="{{ route('admin.publishing') }}" class="db-card-link">
                        Publishing Hub <i class="fas fa-chevron-right" style="font-size:10px"></i>
                    </a>
                </div>

                @if($devTasksActive > 0 || $devTasksList->count() > 0)
                <div class="db-segmented-nav">
                    <button type="button" class="db-seg-btn active" id="pubSegBtn-social" onclick="switchPubTab('social')">
                        <i class="fas fa-share-nodes" style="color:#EF4444"></i>
                        Social Media
                        <span class="db-seg-badge db-seg-badge-yellow">{{ $publishingTotalCount }}</span>
                    </button>
                    <button type="button" class="db-seg-btn" id="pubSegBtn-dev" onclick="switchPubTab('dev')">
                        <i class="fas fa-laptop-code" style="color:#6366F1"></i>
                        Dev Projects
                        <span class="db-seg-badge" style="background:rgba(99,102,241,0.12);color:#6366F1">{{ $devTasksActive }}</span>
                    </button>
                </div>
                @endif

                {{-- Social Media Publishing Pane --}}
                <div class="db-pub-seg-pane active" id="pubPane-social">
                    <div class="db-pub-scroll">
                        {{-- Mini Counter Grid --}}
                        <div class="db-pub-stats">
                            <div class="db-pub-stat-box" style="--psc-color:#6366F1">
                                <div class="db-pub-stat-num">{{ $publishingTotalCount }}</div>
                                <div class="db-pub-stat-label">Total Posts</div>
                            </div>
                            <div class="db-pub-stat-box" style="--psc-color:#F59E0B">
                                <div class="db-pub-stat-num">{{ $publishingAwaitingCount }}</div>
                                <div class="db-pub-stat-label">Awaiting Proof</div>
                            </div>
                            <div class="db-pub-stat-box" style="--psc-color:#3B82F6">
                                <div class="db-pub-stat-num">{{ $publishingPartialCount }}</div>
                                <div class="db-pub-stat-label">Partial</div>
                            </div>
                            <div class="db-pub-stat-box" style="--psc-color:#10B981">
                                <div class="db-pub-stat-num">{{ $publishingPublishedCount }}</div>
                                <div class="db-pub-stat-label">Published</div>
                            </div>
                        </div>

                        {{-- Platform Pills --}}
                        @if($publishingByPlatform->count())
                        @php
                            $platformIcons = ['instagram'=>'fa-instagram','facebook'=>'fa-facebook','linkedin'=>'fa-linkedin','twitter'=>'fa-x-twitter','tiktok'=>'fa-tiktok','youtube'=>'fa-youtube'];
                            $platformColors = ['instagram'=>'#E1306C','facebook'=>'#1877F2','linkedin'=>'#0A66C2','twitter'=>'#1DA1F2','tiktok'=>'#010101','youtube'=>'#FF0000'];
                        @endphp
                        <div class="db-pub-platforms">
                            @foreach($publishingByPlatform as $platform => $count)
                                <div class="db-pub-plat-pill" style="--pp-color:{{ $platformColors[$platform] ?? '#6B7280' }}">
                                    <i class="fa-brands {{ $platformIcons[$platform] ?? 'fa-globe' }}"></i>
                                    <span>{{ ucfirst($platform) }}</span>
                                    <strong>{{ $count }}</strong>
                                </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Recent Proof Uploads --}}
                        @if($recentPublishing->count())
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:var(--db-text-muted);margin:12px 0 8px 0">
                            <i class="fas fa-clock-rotate-left" style="margin-right:4px"></i> Recent Proof Uploads
                        </div>
                        <div class="db-pub-recent-list">
                            @foreach($recentPublishing as $proof)
                                @php
                                    $pColor = $platformColors[$proof->platform] ?? '#6B7280';
                                    $pIcon = $platformIcons[$proof->platform] ?? 'fa-globe';
                                @endphp
                                <div class="db-pub-recent-item">
                                    <div style="color:{{ $pColor }};font-size:16px;width:24px;text-align:center">
                                        <i class="fa-brands {{ $pIcon }}"></i>
                                    </div>
                                    <div style="flex:1;min-width:0">
                                        <div style="font-size:12.5px;font-weight:600;color:var(--db-text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                            {{ $proof->task->title ?? 'Untitled Post' }}
                                        </div>
                                        <div style="font-size:11px;color:var(--db-text-muted)">
                                            {{ $proof->task->client->name ?? '' }} · by {{ $proof->poster->name ?? 'Team Member' }} · {{ $proof->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    @if($proof->post_url)
                                        <a href="{{ $proof->post_url }}" target="_blank" class="db-btn db-btn-ghost" style="padding:4px 8px;font-size:11px" title="Open Post URL">
                                            <i class="fas fa-arrow-up-right-from-square"></i>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Dev Projects Pane (if available) --}}
                @if($devTasksActive > 0 || $devTasksList->count() > 0)
                <div class="db-pub-seg-pane" id="pubPane-dev" style="display:none">
                    <div class="db-dev-list-scroll">
                        <div class="db-dev-list">
                            @foreach($devTasksList as $dev)
                                @php
                                    $devDl = $dev->dev_deadline ?? $dev->launch_date ?? $dev->deadline;
                                    $devDlLabel = $dev->dev_deadline ? 'Dev DL' : ($dev->launch_date ? 'Launch' : 'Deadline');
                                    $devDlOverdue = $devDl && $devDl->isPast() && !in_array($dev->status, ['completed','published']);
                                    $typeIcon = $dev->type === 'website' ? 'fa-globe' : ($dev->type === 'software' ? 'fa-microchip' : 'fa-wrench');
                                    $typeColor = $dev->type === 'website' ? '#6366F1' : ($dev->type === 'software' ? '#8B5CF6' : '#64748B');
                                @endphp
                                <div class="db-dev-row">
                                    <div class="db-dev-icon" style="background:{{ $typeColor }}15;color:{{ $typeColor }}">
                                        <i class="fas {{ $typeIcon }}"></i>
                                    </div>
                                    <div class="db-dev-meta">
                                        <div class="db-dev-title">{{ $dev->title }}</div>
                                        <div class="db-dev-tags">
                                            <x-client-branding :client="$dev->client" size="18px" width="38px" radius="4px" :showName="true" />
                                            @if($dev->tech_stack)
                                                <span class="db-dev-badge" style="background:rgba(99,102,241,0.08);color:#6366F1">{{ $dev->tech_stack }}</span>
                                            @endif
                                            @if($dev->assignee)
                                                <span><i class="fas fa-user" style="font-size:9px;opacity:0.6"></i> {{ $dev->assignee->name }}</span>
                                            @else
                                                <span style="color:#EF4444;font-weight:600"><i class="fas fa-circle-exclamation" style="font-size:9px"></i> Unassigned</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                                        @if($devDl)
                                            <div style="text-align:right">
                                                <div style="font-size:9.5px;color:var(--db-text-muted);font-weight:700;text-transform:uppercase">{{ $devDlLabel }}</div>
                                                <div style="font-size:11.5px;font-weight:700;color:{{ $devDlOverdue ? '#EF4444' : 'var(--db-text-secondary)' }}">
                                                    {{ $devDl->format('d M') }}
                                                </div>
                                            </div>
                                        @endif
                                        <span class="status-badge {{ $dev->status_class ?? $dev->status }}" style="font-size:10px">{{ $dev->status_label ?? ucfirst($dev->status) }}</span>
                                        <a href="{{ route('admin.tasks.show', $dev) }}" class="db-btn db-btn-ghost" style="padding:4px 8px;font-size:11px" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- ── Card 4: Designer Efficiency Leaderboard ── --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <i class="fas fa-ranking-star" style="color:#F59E0B"></i>
                        Designer Efficiency
                    </h2>
                </div>
                <div class="db-card-body-flush">
                    <div class="db-intel-list">
                        @forelse($designerEfficiency as $idx => $designer)
                            <div class="db-intel-row">
                                <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                                    <div class="db-rank-badge {{ $idx === 0 ? 'rank-1' : ($idx === 1 ? 'rank-2' : ($idx === 2 ? 'rank-3' : '')) }}">
                                        {{ $idx + 1 }}
                                    </div>
                                    <div class="av-sm" style="background:{{ $designer->avatar_color ?? '#6366F1' }};width:26px;height:26px;font-size:10px;border-radius:8px">
                                        {{ $designer->initial }}
                                    </div>
                                    <div style="flex:1;min-width:0">
                                        <div style="font-size:12.5px;font-weight:600;color:var(--db-text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $designer->name }}</div>
                                        <div style="font-size:10.5px;color:var(--db-text-muted)">{{ $designer->total_completed }}/{{ $designer->total_assigned }} done</div>
                                    </div>
                                </div>
                                <div style="text-align:right;flex-shrink:0">
                                    <div style="font-size:14px;font-weight:800;font-family:'Plus Jakarta Sans',sans-serif;color:{{ $designer->completion_pct >= 70 ? '#10B981' : ($designer->completion_pct >= 40 ? '#F59E0B' : '#EF4444') }}">
                                        {{ $designer->completion_pct }}%
                                    </div>
                                    @if($designer->avg_turnaround)
                                        <div style="font-size:10px;color:var(--db-text-muted)"><i class="fas fa-stopwatch" style="font-size:9px"></i> {{ $designer->avg_turnaround }}d avg</div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="db-empty">
                                <i class="fas fa-ranking-star"></i>
                                <span>No efficiency records found</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- ════════ ROW 3: CLIENT PERFORMANCE & RECENT ACTIVITY (SIDE BY SIDE) ════════ --}}
        <div class="db-sym-row">

            {{-- ── Card 5: Client Deliverables & Performance ── --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <i class="fas fa-building" style="color:#3B82F6"></i>
                        Client Performance
                    </h2>
                    <a href="{{ route('admin.clients') }}" class="db-card-link">
                        All Clients <i class="fas fa-chevron-right" style="font-size:10px"></i>
                    </a>
                </div>
                <div class="db-card-body-flush">
                    <div class="db-intel-list">
                        @forelse($clientPerformance as $cp)
                            @php $cpPct = $cp->tasks_count > 0 ? round(($cp->completed_tasks / $cp->tasks_count) * 100) : 0; @endphp
                            <div class="db-intel-row">
                                <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0">
                                    <x-client-branding :client="$cp" size="30px" width="56px" radius="8px" fit="contain" />
                                    <div style="flex:1;min-width:0">
                                        <div style="font-size:13px;font-weight:700;color:var(--db-text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $cp->name }}</div>
                                        <div style="font-size:11px;color:var(--db-text-muted)">{{ $cp->completed_tasks }}/{{ $cp->tasks_count }} tasks · {{ $cp->overdue_tasks }} overdue</div>
                                    </div>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;width:110px;flex-shrink:0">
                                    <div class="db-progress-track" style="flex:1">
                                        <div class="db-progress-fill" style="width:{{ $cpPct }}%;background:#10B981"></div>
                                    </div>
                                    <span style="font-size:11.5px;font-weight:700;color:var(--db-text-primary);min-width:28px;text-align:right">{{ $cpPct }}%</span>
                                </div>
                            </div>
                        @empty
                            <div class="db-empty">
                                <i class="fas fa-building"></i>
                                <span>No client performance metrics</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ── Card 6: Live Activity Trail ── --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h2 class="db-card-title">
                        <i class="fas fa-stream" style="color:#10B981"></i>
                        Recent Activity
                    </h2>
                    <a href="{{ route('admin.audit-logs') }}" class="db-card-link">
                        Audit Logs <i class="fas fa-chevron-right" style="font-size:10px"></i>
                    </a>
                </div>
                <div class="db-card-body-flush">
                    <div class="db-intel-list">
                        @forelse($recentActivity as $act)
                            @php
                                $actIcon = 'fa-pen-to-square'; $actColor = '#3B82F6';
                                if (str_contains($act->action, 'Created')) { $actIcon = 'fa-plus-circle'; $actColor = '#10B981'; }
                                elseif (str_contains($act->action, 'Deleted')) { $actIcon = 'fa-trash'; $actColor = '#EF4444'; }
                                elseif (str_contains($act->action, 'Cloned')) { $actIcon = 'fa-clone'; $actColor = '#8B5CF6'; }
                                elseif (str_contains($act->action, 'status')) { $actIcon = 'fa-arrow-right-arrow-left'; $actColor = '#F59E0B'; }
                            @endphp
                            <div class="db-act-item">
                                <div class="db-act-icon" style="color:{{ $actColor }};background:{{ $actColor }}15">
                                    <i class="fas {{ $actIcon }}"></i>
                                </div>
                                <div class="db-act-content">
                                    <div class="db-act-text">
                                        <strong>{{ $act->user->name ?? 'System' }}</strong>
                                        <span>{{ Str::limit($act->action, 45) }}</span>
                                    </div>
                                    <div class="db-act-time">{{ $act->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="db-empty">
                                <i class="fas fa-stream"></i>
                                <span>No activity recorded today</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

{{-- ══════════════ METRIC TASKS MODAL ══════════════ --}}
<div id="metricModal" class="metric-modal" style="display:none">
    <div class="metric-modal-backdrop" onclick="closeMetricModal()"></div>
    <div class="metric-modal-content">
        <div class="metric-modal-header">
            <h3 id="metricModalTitle">Tasks</h3>
            <button class="metric-modal-close" onclick="closeMetricModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="metric-modal-body">
            <div id="metricModalLoader" class="metric-modal-loader" style="padding:40px;text-align:center;color:var(--db-text-muted)">
                <i class="fas fa-circle-notch fa-spin" style="font-size:24px;margin-bottom:8px"></i>
                <div>Loading tasks...</div>
            </div>
            <table id="metricModalTable" class="metric-modal-table" style="display:none">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:25%">Title</th>
                        <th style="width:16%">Client</th>
                        <th style="width:10%">Type</th>
                        <th style="width:12%">Platform</th>
                        <th style="width:10%">Status</th>
                        <th style="width:12%">Assignee</th>
                        <th style="width:10%">Deadline</th>
                    </tr>
                </thead>
                <tbody id="metricModalTableBody"></tbody>
            </table>
            <div id="metricModalEmpty" class="metric-modal-empty" style="display:none;padding:40px;text-align:center;color:var(--db-text-muted)">
                <i class="fas fa-clipboard-check" style="font-size:32px;opacity:0.4;display:block;margin-bottom:8px"></i>
                No tasks found matching this criteria.
            </div>
        </div>
    </div>
</div>

<script>
function switchActionTab(tabId) {
    document.querySelectorAll('.db-seg-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.db-seg-pane').forEach(pane => pane.classList.remove('active'));
    
    const targetBtn = document.getElementById('segBtn-' + tabId);
    const targetPane = document.getElementById('segPane-' + tabId);
    if(targetBtn) targetBtn.classList.add('active');
    if(targetPane) targetPane.classList.add('active');
}

function switchPubTab(tabId) {
    document.querySelectorAll('#pubPane-social, #pubPane-dev').forEach(p => p.style.display = 'none');
    document.querySelectorAll('#pubSegBtn-social, #pubSegBtn-dev').forEach(b => b.classList.remove('active'));
    
    const targetPane = document.getElementById('pubPane-' + tabId);
    const targetBtn = document.getElementById('pubSegBtn-' + tabId);
    if (targetPane) targetPane.style.display = 'block';
    if (targetBtn) targetBtn.classList.add('active');
}
</script>

<script src="{{ asset('js/pages/admin-dashboard.js') }}?v=3.1"></script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/admin-dashboard.css') }}?v=3.1">
@endpush

