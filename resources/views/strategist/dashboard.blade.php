@extends('layouts.app')

@section('content')
{{-- ===== GREETING HEADER ===== --}}
<div class="dash-hero anim-fade" style="--delay:0">
    <div class="dash-hero-left">
        <div class="dash-greeting">{{ $greeting }}, {{ $strategistName }}</div>
        @if($overdueCount > 0)
            <div class="dash-alert">
                <span class="dash-alert-dot"></span>
                {{ $overdueCount }} overdue task{{ $overdueCount > 1 ? 's' : '' }} need attention
                @if($dueSoonCount > 0) &middot; {{ $dueSoonCount }} due soon @endif
            </div>
        @elseif($dueSoonCount > 0)
            <div class="dash-alert dash-alert-warn">
                <span class="dash-alert-dot warn"></span>
                {{ $dueSoonCount }} task{{ $dueSoonCount > 1 ? 's' : '' }} due in the next 48h
            </div>
        @endif
    </div>
    <div class="dash-hero-center">
        <div class="dh-quick-stats">
            <div class="dh-qs"><span class="dh-qs-val">{{ $createdToday }}</span> created today</div>
            <div class="dh-qs"><span class="dh-qs-val dh-qs-green">{{ $approvedToday }}</span> done today</div>
            <div class="dh-qs"><span class="dh-qs-val dh-qs-blue">{{ $avgTasksPerDay }}</span>/day avg</div>
        </div>
        <a href="{{ route('strategist.create-task') }}" class="btn-primary dash-create-btn">
            <i class="fa-solid fa-plus"></i> Create Task
        </a>
    </div>
    <div class="dash-hero-right">
        <div class="dash-datetime-box">
            <div class="dash-ist-clock" id="istClock">
                <i class="fa-regular fa-clock"></i>
                <span class="dash-time" id="istTime">--:--:--</span>
                <span class="dash-ampm" id="istAmPm">--</span>
            </div>
            <div class="dash-date">
                <i class="fa-regular fa-calendar"></i>
                <span id="istDate">{{ now()->format('l, F j, Y') }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ===== COMMAND METRICS BAR ===== --}}
<div class="cmd-metrics">
    {{-- Completion --}}
    <div class="cmd-metric-card cmd-metric-clickable" data-metric="completion">
        <div class="cmd-metric-ring" style="--pct:{{ $completionRate }}; --ring-c:var(--teal)">
            <svg viewBox="0 0 36 36"><path class="ring-bg" d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831"/><path class="ring-fill" stroke-dasharray="{{ $completionRate }},100" d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831"/></svg>
            <span class="ring-num">{{ $completionRate }}%</span>
        </div>
        <div>
            <div class="cmd-metric-label">Completion</div>
            <div class="cmd-metric-val">{{ $completedTasks }}<small>/{{ $totalTasks }}</small></div>
            <div class="cmd-metric-sub">
                @if($completedLastWeek > 0)

    @if($completedThisWeek > $completedLastWeek)

        <span class="up">
            Completed {{ $completedThisWeek - $completedLastWeek }}
            more task{{ ($completedThisWeek - $completedLastWeek) > 1 ? 's' : '' }}
            than last week
        </span>

    @elseif($completedThisWeek < $completedLastWeek)

        <span class="down">
            Completed {{ $completedLastWeek - $completedThisWeek }}
            fewer task{{ ($completedLastWeek - $completedThisWeek) > 1 ? 's' : '' }}
            than last week
        </span>

    @else

        <span style="color:#64748b">
            Completed the same number of tasks as last week
        </span>

    @endif

@else

    <span style="color:#64748b">
        {{ $completedThisWeek }} completed this week
    </span>

@endif
            </div>
        </div>
    </div>

    {{-- Productivity --}}
    {{--
    <div class="cmd-metric-card cmd-metric-clickable" data-metric="productivity">
        <div class="cmd-metric-ring" style="--pct:{{ $productivityScore }}; --ring-c:{{ $productivityScore >= 60 ? 'var(--blue)' : ($productivityScore >= 35 ? 'var(--yellow)' : 'var(--red)') }}">
            <svg viewBox="0 0 36 36"><path class="ring-bg" d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831"/><path class="ring-fill" stroke-dasharray="{{ $productivityScore }},100" d="M18 2.0845a15.9155 15.9155 0 010 31.831 15.9155 15.9155 0 010-31.831"/></svg>
            <span class="ring-num">{{ $productivityScore }}</span>
        </div>

        <div>
            <div class="cmd-metric-label">Productivity</div>
            <div class="cmd-metric-sub" style="margin-top:2px">
                @if($productivityScore >= 70) <span class="up">Excellent</span>
                @elseif($productivityScore >= 50) <span class="up">Good</span>
                @elseif($productivityScore >= 30) <span style="color:var(--yellow)">Fair</span>
                @else <span class="down">Needs push</span>
                @endif
            </div>
        </div>

    </div>--}}

    {{-- Overdue --}}
    <div class="cmd-metric-card cmd-metric-clickable {{ $overdueCount > 0 ? 'cmd-metric-danger' : '' }}" data-metric="overdue">
        <div class="cmd-metric-icon cmd-icon-red"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div>
            <div class="cmd-metric-label">Overdue</div>
            <div class="cmd-metric-val {{ $overdueCount > 0 ? 'danger' : 'safe' }}">{{ $overdueCount }}</div>
            <div class="cmd-metric-sub">

@if($dueSoonCount > 0)

    {{ $dueSoonCount }}
    task{{ $dueSoonCount > 1 ? 's' : '' }}
    due within 48 hours

@else

    No tasks due within 48 hours

@endif

</div>
        </div>
    </div>

    {{-- In Review --}}
    <div class="cmd-metric-card cmd-metric-clickable {{ $pendingApprovals > 0 ? 'cmd-metric-purple' : '' }}" data-metric="review">
        <div class="cmd-metric-icon cmd-icon-purple"><i class="fa-solid fa-flask"></i>
        </div>
        <div>
            <div class="cmd-metric-label">In Review</div>
            <div class="cmd-metric-val" style="color:var(--purple)">{{ $pendingApprovals }}</div>
            <div class="cmd-metric-sub">

@if($approvalPressure > 0)

    {{ $approvalPressure }}
    task{{ $approvalPressure > 1 ? 's' : '' }}
    waiting over 24 hours

@else

    No tasks waiting over 24 hours

@endif

</div>
        </div>
    </div>

    {{-- In Progress --}}
    <div class="cmd-metric-card cmd-metric-clickable" data-metric="progress">
        <div class="cmd-metric-icon cmd-icon-blue"><i class="fa-solid fa-rocket"></i></div>
        <div>
            <div class="cmd-metric-label">In Progress</div>
            <div class="cmd-metric-val" style="color:var(--blue)">{{ $inProgressCount }}</div>
            <div class="cmd-metric-sub">

@if($approvedToday > 0)

    {{ $approvedToday }}
    task{{ $approvedToday > 1 ? 's' : '' }}
    completed today

@else

    No tasks completed today

@endif

</div>
        </div>
    </div>


</div>

{{-- ── Urgent Tasks Alert (While Away) ── --}}
@if($urgentTasksWhileAway->count() > 0)
<div class="db-alert-banner urgent-away-alert" style="margin:20px 0;background:linear-gradient(135deg,#FEE2E2 0%,#FECACA 100%);border:1.5px solid #EF4444;border-radius:12px;padding:16px 20px;display:flex;align-items:center;gap:20px;box-shadow:0 8px 20px -5px rgba(239,68,68,0.2)">
    <div style="width:44px;height:44px;background:#EF4444;color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;animation:pulse-red 2s infinite">
        <i class="fas fa-phone-volume"></i>
    </div>
    <div style="flex:1">
        <h3 style="font-size:15px;font-weight:800;color:#991B1B;margin:0">Urgent Tasks Created While You Were Away</h3>
        <p style="font-size:12px;color:#991B1B;opacity:0.8;margin:4px 0 0">Designers self-created <strong>{{ $urgentTasksWhileAway->count() }} urgent task{{ $urgentTasksWhileAway->count() > 1 ? 's' : '' }}</strong> while you were away.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;max-width:350px;justify-content:flex-end">
        @foreach($urgentTasksWhileAway->take(3) as $ut)
            <a href="{{ route('strategist.tasks.show', $ut) }}" class="ut-chip" style="background:#fff;padding:6px 10px;border-radius:8px;text-decoration:none;color:#991B1B;font-size:11px;font-weight:700;display:flex;align-items:center;gap:6px;border:1px solid #FCA5A5;transition:all 0.2s">
                <span>
                    <x-client-branding :client="$ut->client" size="22px" radius="5px" />
                </span>
                {{ Str::limit($ut->title, 12) }}
            </a>
        @endforeach
    </div>
</div>
@endif

{{-- ===== MONTHLY SCHEDULE OVERVIEW WIDGET ===== --}}
@if(($scheduleOverview['clientsWithSchedule'] ?? 0) > 0)
<div style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px 20px;margin:16px 0;box-shadow:var(--shadow-sm);position:relative;overflow:hidden">
    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),#8B5CF6,#3B82F6)"></div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:34px;height:34px;border-radius:9px;background:rgba(99,102,241,.1);color:#6366F1;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
                <i class="fa-solid fa-chart-bar"></i>
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--text)">Monthly Schedule — {{ $scheduleOverview['monthLabel'] }}</div>
                <div style="font-size:11px;color:var(--text3);font-weight:500">{{ $scheduleOverview['clientsWithSchedule'] }}/{{ $scheduleOverview['totalClients'] }} clients · {{ $scheduleOverview['totalPlanned'] }} planned · {{ $scheduleOverview['totalDone'] }} done</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <div style="font-size:22px;font-weight:800;color:{{ $scheduleOverview['overallPct'] >= 80 ? '#10B981' : ($scheduleOverview['overallPct'] >= 50 ? '#F59E0B' : 'var(--primary)') }};line-height:1">{{ $scheduleOverview['overallPct'] }}%</div>
            <a href="{{ route('strategist.content-schedules') }}" style="padding:7px 14px;border-radius:8px;background:var(--primary);color:#fff;font-size:11px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:all .15s">
                <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px"></i> View All
            </a>
        </div>
    </div>
    {{-- Overall bar --}}
    <div style="height:6px;background:var(--bg);border-radius:3px;overflow:hidden;position:relative;margin-bottom:10px">
        @if($scheduleOverview['totalPlanned'] > 0)
            <div style="height:100%;width:{{ min(100, ($scheduleOverview['totalDone'] / $scheduleOverview['totalPlanned']) * 100) }}%;background:linear-gradient(90deg,#10B981,#059669);border-radius:3px;position:absolute;left:0;top:0;z-index:2;transition:width .4s ease"></div>
            <div style="height:100%;width:{{ min(100, (($scheduleOverview['totalDone'] + $scheduleOverview['totalActive']) / $scheduleOverview['totalPlanned']) * 100) }}%;background:rgba(59,130,246,0.25);border-radius:3px;position:absolute;left:0;top:0;z-index:1;transition:width .4s ease"></div>
        @endif
    </div>
    {{-- Top clients needing attention --}}
    @if(count($scheduleOverview['topClients']) > 0)
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @foreach($scheduleOverview['topClients'] as $tc)
                @if($tc['remaining'] > 0)
                <div style="display:flex;align-items:center;gap:6px;padding:5px 10px;background:var(--bg);border-radius:7px;font-size:11px;min-width:0">
                    <span style="font-size:14px">{{ $tc['emoji'] }}</span>
                    <span style="font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:80px">{{ $tc['name'] }}</span>
                    <span style="font-weight:700;color:{{ $tc['pct'] >= 80 ? '#10B981' : ($tc['pct'] >= 50 ? '#F59E0B' : 'var(--primary)') }}">{{ $tc['pct'] }}%</span>
                    <span style="color:var(--text3);font-size:10px">{{ $tc['remaining'] }} left</span>
                </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
@endif

{{-- ===== DEV PROJECTS SECTION ===== --}}
@if($devTasksActive > 0 || $devTasksList->count() > 0)
<div style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px 20px;margin:16px 0;box-shadow:var(--shadow-sm);position:relative;overflow:hidden">
    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#6366F1,#8B5CF6,#3B82F6)"></div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:34px;height:34px;border-radius:9px;background:rgba(99,102,241,.12);color:#6366F1;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0">
                <i class="fas fa-laptop-code"></i>
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--text)">Dev Projects</div>
                <div style="font-size:11px;color:var(--text3);font-weight:500">{{ $devTasksActive }} active project{{ $devTasksActive !== 1 ? 's' : '' }} — website, software &amp; maintenance</div>
            </div>
        </div>
        <a href="{{ route('strategist.tracking', ['type' => 'website']) }}" style="padding:7px 14px;border-radius:8px;background:rgba(99,102,241,.1);color:#6366F1;font-size:11px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:all .15s;border:1px solid rgba(99,102,241,.2)">
            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px"></i> View All
        </a>
    </div>
    @if($devTasksList->count() > 0)
    <div style="display:grid;gap:8px">
        @foreach($devTasksList as $dev)
        @php
            $devDl = $dev->dev_deadline ?? $dev->launch_date ?? $dev->deadline;
            $devDlLabel = $dev->dev_deadline ? 'Dev DL' : ($dev->launch_date ? 'Launch' : 'Deadline');
            $devDlOverdue = $devDl && $devDl->isPast() && !in_array($dev->status, ['completed','published']);
            $typeIcon = $dev->type === 'website' ? 'fa-globe' : ($dev->type === 'software' ? 'fa-microchip' : 'fa-wrench');
            $typeColor = $dev->type === 'website' ? '#6366F1' : ($dev->type === 'software' ? '#8B5CF6' : '#64748B');
        @endphp
        <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;background:var(--bg);border-radius:9px;border:1px solid var(--border);transition:all .15s" onmouseover="this.style.borderColor='#6366F180'" onmouseout="this.style.borderColor='var(--border)'">
            <div style="width:32px;height:32px;border-radius:8px;background:{{ $typeColor }}18;color:{{ $typeColor }};display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0">
                <i class="fas {{ $typeIcon }}"></i>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:12.5px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $dev->title }}</div>
                <div style="display:flex;align-items:center;gap:8px;font-size:11px;color:var(--text3);margin-top:2px;flex-wrap:wrap">
                    <span>
                        <x-client-branding :client="$dev->client" size="20px" radius="4px" :showName="true" />
                    </span>
                    @if($dev->tech_stack)
                        <span style="background:rgba(99,102,241,.08);color:#6366F1;padding:1px 6px;border-radius:4px;font-size:10px;font-weight:600">{{ $dev->tech_stack }}</span>
                    @endif
                    @if($dev->assignee)
                        <span><i class="fas fa-user" style="font-size:9px;opacity:.6"></i> {{ $dev->assignee->name }}</span>
                    @else
                        <span style="color:var(--red);font-weight:600"><i class="fas fa-exclamation-circle" style="font-size:9px"></i> Unassigned</span>
                    @endif
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                @if($devDl)
                    <div style="text-align:right">
                        <div style="font-size:9.5px;color:var(--text3);font-weight:600;text-transform:uppercase;letter-spacing:.3px">{{ $devDlLabel }}</div>
                        <div style="font-size:11.5px;font-weight:700;color:{{ $devDlOverdue ? 'var(--red)' : 'var(--text2)' }}">
                            {{ $devDl->format('d M Y') }}
                            @if($devDlOverdue) <span style="font-size:9px;background:var(--red-dim);color:var(--red);padding:1px 5px;border-radius:4px;margin-left:2px">Overdue</span> @endif
                        </div>
                    </div>
                @endif
                <span class="status {{ $dev->status_class }}" style="font-size:10px;white-space:nowrap">{{ $dev->status_label }}</span>
                <a href="{{ route('strategist.tasks.edit', $dev) }}" style="width:28px;height:28px;border-radius:7px;border:1px solid var(--border);background:var(--card);display:flex;align-items:center;justify-content:center;color:var(--text3);text-decoration:none;font-size:11px;transition:all .15s" title="Edit" data-no-loader onmouseover="this.style.background='var(--primary-dim)';this.style.color='var(--primary)'" onmouseout="this.style.background='var(--card)';this.style.color='var(--text3)'">
                    <i class="fas fa-pen"></i>
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align:center;padding:24px;color:var(--text3);font-size:13px">
        <i class="fas fa-laptop-code" style="font-size:24px;opacity:.25;display:block;margin-bottom:8px"></i>
        No active dev projects
    </div>
    @endif
</div>
@endif

{{-- ===== METRIC DRILL-DOWN PANEL ===== --}}
<div id="cmdDrillDown" class="cmd-drilldown" style="display:none">
    <div class="cmd-drilldown-header">
        <div class="cmd-drilldown-title">
            <i id="ddIcon" class="fa-solid fa-list"></i>
            <span id="ddTitle">Tasks</span>
            <span id="ddCount" class="cmd-drilldown-count"></span>
        </div>
        <button class="cmd-drilldown-close" onclick="closeDrillDown()">
            <i class="fa-solid fa-xmark"></i> Close
        </button>
    </div>
    <div class="cmd-drilldown-body">

        {{-- Completion Panel --}}
        <div class="cmd-dd-panel" id="ddCompletion">
            @forelse($completedTasksList as $task)
                <div class="cmd-task-row">
                    <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                    <div class="cmd-task-body">
                        <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            <x-client-branding :client="$task->client" size="22px" radius="5px" />
                            {{ $task->client->name ?? 'N/A' }}
                            · <i class="fas fa-user" style="font-size:10px;opacity:.6"></i> {{ $task->assignee?->name ?? 'Unassigned' }}
                            · Completed {{ $task->updated_at->diffForHumans() }}
                        </div>
                    </div>
                    <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                </div>
            @empty
                <div class="cmd-empty-inline"><i class="fas fa-check-circle" style="color:var(--teal)"></i> No completed tasks yet</div>
            @endforelse
        </div>

        {{-- Productivity Panel --}}
        <div class="cmd-dd-panel" id="ddProductivity">
            <div class="cmd-dd-info-grid">
                <div class="cmd-dd-info-card">
                    <div class="cmd-dd-info-label">Score</div>
                    <div class="cmd-dd-info-val" style="color:{{ $productivityScore >= 60 ? 'var(--teal)' : ($productivityScore >= 35 ? 'var(--yellow)' : 'var(--red)') }}">{{ $productivityScore }}/100</div>
                </div>
                <div class="cmd-dd-info-card">
                    <div class="cmd-dd-info-label">Completed This Week</div>
                    <div class="cmd-dd-info-val">{{ $completedThisWeek }}</div>
                </div>
                <div class="cmd-dd-info-card">
                    <div class="cmd-dd-info-label">Avg Tasks/Day</div>
                    <div class="cmd-dd-info-val">{{ $avgTasksPerDay }}</div>
                </div>
                <div class="cmd-dd-info-card">
                    <div class="cmd-dd-info-label">Created Today</div>
                    <div class="cmd-dd-info-val">{{ $createdToday }}</div>
                </div>
                <div class="cmd-dd-info-card">
                    <div class="cmd-dd-info-label">Approved Today</div>
                    <div class="cmd-dd-info-val" style="color:var(--teal)">{{ $approvedToday }}</div>
                </div>
                <div class="cmd-dd-info-card">
                    <div class="cmd-dd-info-label">Overdue</div>
                    <div class="cmd-dd-info-val" style="color:{{ $overdueCount > 0 ? 'var(--red)' : 'var(--teal)' }}">{{ $overdueCount }}</div>
                </div>
            </div>
            <div class="cmd-dd-tip">
                <i class="fa-solid fa-lightbulb" style="color:var(--yellow)"></i>
                Score is based on completion rate, daily output, and overdue ratio.
            </div>
        </div>

        {{-- Overdue Panel --}}
        <div class="cmd-dd-panel" id="ddOverdue">
            @if($dueSoonTasks->count() > 0)
                <div class="cmd-mini-head" style="--mh-c:var(--yellow)"><i class="fa-solid fa-clock"></i> Due Soon (48h) <span class="cmd-mini-count">{{ $dueSoonTasks->count() }}</span></div>
            @endif
            @foreach($dueSoonTasks as $task)
                <div class="cmd-task-row">
                    <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                    <div class="cmd-task-body">
                        <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            @if($task->client->logo)
                                <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                            @else
                                {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!}
                            @endif
                            {{ $task->client->name ?? 'N/A' }}
                            · <i class="fas fa-user" style="font-size:10px;opacity:.6"></i> {{ $task->assignee?->name ?? 'Unassigned' }}
                            · Due {{ \Carbon\Carbon::parse($task->deadline ?? $task->post_date)->format('M d, g:i A') }}
                        </div>
                    </div>
                    <a href="{{ route('strategist.tasks.show', $task) }}" class="cmd-btn-sm cmd-btn-ghost" data-no-loader>Open</a>
                </div>
            @endforeach

            @if($overdueTasks->count() > 0)
                <div class="cmd-mini-head" style="--mh-c:var(--red)"><i class="fa-solid fa-triangle-exclamation"></i> Overdue <span class="cmd-mini-count">{{ $overdueTasks->count() }}</span></div>
            @endif
            @foreach($overdueTasks as $task)
                <div class="cmd-task-row">
                    <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                    <div class="cmd-task-body">
                        <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            @if($task->client->logo)
                                <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                            @else
                                {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!}
                            @endif
                            {{ $task->client->name ?? 'N/A' }}
                            · <i class="fas fa-user" style="font-size:10px;opacity:.6"></i> {{ $task->assignee?->name ?? 'Unassigned' }}
                            · Deadline was {{ \Carbon\Carbon::parse($task->deadline)->diffForHumans() }}
                        </div>
                    </div>
                    <a href="{{ route('strategist.tracking', ['task' => $task->id]) }}" class="cmd-btn-sm">Fix →</a>
                </div>
            @endforeach

            @if($overdueTasks->count() === 0 && $dueSoonTasks->count() === 0)
                <div class="cmd-empty-inline"><i class="fas fa-check-circle" style="color:var(--teal)"></i> No overdue or due-soon tasks!</div>
            @endif
        </div>

        {{-- In Review Panel --}}
        <div class="cmd-dd-panel" id="ddReview">
            @forelse($inReviewTasks as $task)
                <div class="cmd-task-row">
                    <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                    <div class="cmd-task-body">
                        <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            @if($task->client->logo)
                                <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                            @else
                                {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!}
                            @endif
                            {{ $task->client->name ?? 'N/A' }}
                            · <i class="fas fa-user" style="font-size:10px;opacity:.6"></i> {{ $task->assignee?->name ?? 'Unassigned' }}
                            · In review {{ $task->updated_at->diffForHumans() }}
                        </div>
                    </div>
                    <a href="{{ route('strategist.approvals') }}" class="cmd-btn-sm cmd-btn-purple">Review</a>
                </div>
            @empty
                <div class="cmd-empty-inline"><i class="fas fa-check-circle" style="color:var(--teal)"></i> No tasks in review</div>
            @endforelse
        </div>

        {{-- In Progress Panel --}}
        <div class="cmd-dd-panel" id="ddProgress">
            @forelse($inProgressTasks as $task)
                <div class="cmd-task-row">
                    <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                    <div class="cmd-task-body">
                        <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            @if($task->client->logo)
                                <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                            @else
                                {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!}
                            @endif
                            {{ $task->client->name ?? 'N/A' }}
                            · <i class="fas fa-user" style="font-size:10px;opacity:.6"></i> {{ $task->assignee?->name ?? 'Unassigned' }}
                            @if($task->deadline) · Due {{ \Carbon\Carbon::parse($task->deadline)->format('M d') }} @endif
                        </div>
                    </div>
                    <a href="{{ route('strategist.tasks.show', $task) }}" class="cmd-btn-sm cmd-btn-ghost" data-no-loader>Open</a>
                </div>
            @empty
                <div class="cmd-empty-inline">No tasks in progress</div>
            @endforelse
        </div>

        {{-- Pending Assign Panel --}}
        <div class="cmd-dd-panel" id="ddPending">
            @forelse($pendingAssignAll as $task)
                <div class="cmd-task-row">
                    <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                    <div class="cmd-task-body">
                        <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            @if($task->client->logo)
                                <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                            @else
                                <x-client-branding :client="$task->client" size="22px" radius="5px" />
                            @endif
                            {{ $task->client->name ?? 'N/A' }}
                            @if($task->deadline) · Due {{ \Carbon\Carbon::parse($task->deadline)->format('M d') }} @endif
                        </div>
                    </div>
                    <a href="{{ route('strategist.tracking', ['task' => $task->id]) }}" class="cmd-btn-sm">Assign →</a>
                </div>
            @empty
                <div class="cmd-empty-inline"><i class="fas fa-check-circle" style="color:var(--teal)"></i> All tasks assigned!</div>
            @endforelse
        </div>

    </div>
</div>

{{-- ===== STATUS PIPELINE (COMPACT) ===== --}}
@php
    $pipelineTotal = max(1, $contentToPlan + $inProgressCount + $pendingApprovals + $completedTasks);
    $pipeStages = [
        ['label' => 'To Do', 'count' => $contentToPlan, 'color' => '#FFA500', 'icon' => 'fa-clipboard-list'],
        ['label' => 'In Progress', 'count' => $inProgressCount, 'color' => '#3B82F6', 'icon' => 'fa-spinner'],
        ['label' => 'Review', 'count' => $pendingApprovals, 'color' => '#8B5CF6', 'icon' => 'fa-flask'],
        ['label' => 'Completed', 'count' => $completedTasks, 'color' => '#10B981', 'icon' => 'fa-circle-check'],
    ];
@endphp
<div class="cmd-pipeline">
    <div class="cmd-pipeline-bar">
        @foreach($pipeStages as $s)
            <div class="cmd-pipe-seg" style="flex:{{ max(1,$s['count']) }};background:{{ $s['color'] }}" title="{{ $s['label'] }}: {{ $s['count'] }}"></div>
        @endforeach
    </div>
    <div class="cmd-pipeline-labels">
        @foreach($pipeStages as $s)
            <div class="cmd-pipe-label">
                <i class="fa-solid {{ $s['icon'] }}" style="color:{{ $s['color'] }}"></i>
                <span class="cmd-pipe-count" style="color:{{ $s['color'] }}">{{ $s['count'] }}</span>
                <span class="cmd-pipe-name">{{ $s['label'] }}</span>
                <span class="cmd-pipe-pct">{{ round(($s['count']/$pipelineTotal)*100) }}%</span>
            </div>
        @endforeach
    </div>
</div>

{{-- ===== MAIN CONTENT: BENTO GRID ===== --}}
<div class="cmd-bento">

    {{-- â”€â”€â”€â”€ LEFT COLUMN: TODAY'S FOCUS â”€â”€â”€â”€ --}}
    <div class="cmd-col-main">

        {{-- Today's Focus Queue --}}
        <div class="cmd-card cmd-card-focus">
            <div class="cmd-card-head">
                <div class="cmd-card-title"><i class="fa-solid fa-bullseye" style="color:var(--red)"></i> Today's Focus Queue</div>
                <a href="{{ route('strategist.approvals') }}" class="cmd-card-link">Approvals →</a>
            </div>

            {{-- Needs Assignment --}}
            <div class="cmd-mini-head" style="--mh-c:var(--red)"><i class="fa-solid fa-user-plus"></i> Needs Assignment <span class="cmd-mini-count">{{ $todayNeedsAssignment->count() }}</span></div>
            @forelse($todayNeedsAssignment as $task)
                <div class="cmd-task-row">
                    <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                    <div class="cmd-task-body">
                        <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            <x-client-branding :client="$task->client" size="22px" radius="5px" />
                            {{ $task->client->name }}
                            Â· @if(is_array($task->platform)){{ implode(', ', array_map('ucfirst', $task->platform)) }}@else{{ ucfirst($task->platform) }}@endif
                            @if($task->priority === 'urgent') <span class="cmd-urgent">â— Urgent</span>
                            @elseif($task->priority === 'high') <span class="cmd-high">â— High</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('strategist.tracking', ['task' => $task->id]) }}" class="cmd-btn-sm">Assign â†’</a>
                </div>
            @empty
                <div class="cmd-empty-inline"><i class="fas fa-check-circle" style="color:var(--teal)"></i> All tasks assigned</div>
            @endforelse

            {{-- In Progress --}}
            <div class="cmd-mini-head" style="--mh-c:var(--blue)"><i class="fa-solid fa-rocket"></i> In Progress <span class="cmd-mini-count">{{ $todayInProgress->count() }}</span></div>
            @forelse($todayInProgress as $task)
                <div class="cmd-task-row">
                    <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                    <div class="cmd-task-body">
                        <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            @if($task->client->logo)
                                <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                            @else
                                {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!}
                            @endif
                            {{ $task->client->name }} Â· <i class="fas fa-user" style="font-size:10px;opacity:.6"></i> {{ $task->assignee?->name ?? 'Unassigned' }}
                        </div>
                    </div>
                    <a href="{{ route('strategist.tasks.show', $task) }}" class="cmd-btn-sm cmd-btn-ghost" data-no-loader>Open</a>
                </div>
            @empty
                <div class="cmd-empty-inline">No in-progress tasks today</div>
            @endforelse

            {{-- Needs Review --}}
            <div class="cmd-mini-head" style="--mh-c:var(--purple)"><i class="fa-solid fa-flask"></i> Needs Review <span class="cmd-mini-count">{{ $todayNeedsReview->count() }}</span></div>
            @forelse($todayNeedsReview as $task)
                <div class="cmd-task-row">
                    <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                    <div class="cmd-task-body">
                        <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            @if($task->client->logo)
                                <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                            @else
                                <x-client-branding :client="$task->client" size="22px" radius="5px" />
                            @endif
                            {{ $task->client->name }} Â· waiting review
                        </div>
                    </div>
                    <a href="{{ route('strategist.approvals') }}" class="cmd-btn-sm cmd-btn-purple">Review</a>
                </div>
            @empty
                <div class="cmd-empty-inline">No tasks pending review</div>
            @endforelse
        </div>

        {{-- Upcoming by Window --}}
        <div class="cmd-card">
            <div class="cmd-card-head">
                <div class="cmd-card-title"><i class="fa-regular fa-calendar-check" style="color:var(--teal)"></i> Upcoming Schedule</div>
                <a href="{{ route('strategist.calendar') }}" class="cmd-card-link">Calendar →</a>
            </div>
            <div class="cmd-card-scroll">
                <div class="cmd-mini-head" style="--mh-c:var(--yellow)"><i class="fa-solid fa-sun"></i> Tomorrow</div>
                @forelse($upcomingTomorrow as $task)
                    <div class="cmd-task-row">
                        <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                        <div class="cmd-task-body">
                            <div class="cmd-task-title">{{ $task->title }}</div>
                            <div class="cmd-task-meta">
                                @if($task->client->logo)
                                    <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                                @else
                                    {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!}
                                @endif
                                {{ $task->client->name }} Â· {!! $task->effective_date_type === 'post_date' ? '<i class="fas fa-share-square" style="font-size:10px;opacity:.6"></i> Post' : '<i class="fas fa-bullseye" style="font-size:10px;opacity:.6"></i> Deadline' !!}
                            </div>
                        </div>
                        <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                    </div>
                @empty
                    <div class="cmd-empty-inline">Nothing for tomorrow</div>
                @endforelse

                <div class="cmd-mini-head" style="--mh-c:var(--blue)"><i class="fa-solid fa-forward"></i> Next 3 Days</div>
                @forelse($upcomingNextThree as $task)
                    <div class="cmd-task-row">
                        <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                        <div class="cmd-task-body">
                            <div class="cmd-task-title">{{ $task->title }}</div>
                            <div class="cmd-task-meta">
                                @if($task->client->logo)
                                    <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                                @else
                                    {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!}
                                @endif
                                {{ $task->client->name }} Â· {{ \Carbon\Carbon::parse($task->effective_date)->format('D, M d') }}
                            </div>
                        </div>
                        <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                    </div>
                @empty
                    <div class="cmd-empty-inline">Clear for next 3 days</div>
                @endforelse

                <div class="cmd-mini-head" style="--mh-c:var(--text3)"><i class="fa-regular fa-calendar-days"></i> Later This Week</div>
                @forelse($upcomingLaterWeek as $task)
                    <div class="cmd-task-row">
                        <span class="cmd-tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                        <div class="cmd-task-body">
                            <div class="cmd-task-title">{{ $task->title }}</div>
                            <div class="cmd-task-meta">
                                @if($task->client->logo)
                                    <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                                @else
                                    <x-client-branding :client="$task->client" size="22px" radius="5px" />
                                @endif
                                {{ $task->client->name }} Â· {{ \Carbon\Carbon::parse($task->effective_date)->format('D, M d') }}
                            </div>
                        </div>
                        <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                    </div>
                @empty
                    <div class="cmd-empty-inline">No more tasks this week</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- â”€â”€â”€â”€ RIGHT COLUMN: INSIGHTS â”€â”€â”€â”€ --}}
    <div class="cmd-col-side">

        {{-- Pending Assignments --}}
        <div class="cmd-card cmd-card-compact">
            <div class="cmd-card-head">
                <div class="cmd-card-title"><i class="fa-solid fa-clipboard-list" style="color:var(--yellow)"></i> Pending Assignments</div>
                <a href="{{ route('strategist.pending-assignments') }}" class="cmd-card-link">All <i class="fas fa-arrow-right"></i></a>
            </div>
            @if($unassignedDueSoon > 0)
                <div class="cmd-pa-summary">
                    <span class="cmd-pa-stat">{{ $pendingAssign }} tasks</span>
                    <span class="cmd-pa-urgent">{{ $unassignedDueSoon }} urgent</span>
                </div>
                <div class="cmd-pa-list">
                    @foreach($pendingAssignmentTasks as $task)
                        <div class="cmd-pa-item">
                            <div class="cmd-pa-title">{{ Str::limit($task->title, 35) }}</div>
                            <div class="cmd-pa-meta">
                                @if($task->client->logo)
                                    <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:12px;height:12px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                                @else
                                    <x-client-branding :client="$task->client" size="22px" radius="5px" />
                                @endif
                                {{ $task->client->name ?? 'N/A' }}
                                @if($task->deadline) Â· {{ \Carbon\Carbon::parse($task->deadline)->format('M d') }} @endif
                            </div>
                        </div>
                    @endforeach
                    @if($pendingAssign > 5)
                        <div class="cmd-pa-more">+{{ $pendingAssign - 5 }} more...</div>
                    @endif
                </div>
            @else
                <div class="cmd-empty-inline"><i class="fas fa-check-circle" style="color:var(--teal)"></i> All tasks assigned!</div>
            @endif
        </div>

        {{-- Client Health --}}
        <div class="cmd-card cmd-card-compact">
            <div class="cmd-card-head">
                <div class="cmd-card-title"><i class="fa-solid fa-heart-pulse" style="color:var(--teal)"></i> Client Health</div>
                <a href="{{ route('strategist.tracking') }}" class="cmd-card-link">Tasks <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="cmd-card-scroll-sm">
                @forelse($clientHealth as $client)
                    @php
                        $hColor = 'var(--teal)'; $hLabel = 'Healthy';
                        if($client['overdue'] > 0) { $hColor = 'var(--red)'; $hLabel = 'Critical'; }
                        elseif($client['due_this_week'] > 2) { $hColor = 'var(--yellow)'; $hLabel = 'Busy'; }
                    @endphp
                    <div class="cmd-ch-row">
                        <span class="cmd-ch-emoji">
                            <x-client-branding :client="(object)['logo' => $client['logo'] ?? null, 'emoji' => $client['emoji'] ?? '🏢', 'name' => $client['name'] ?? 'Client']" size="28px" radius="6px" />
                        </span>
                        <div class="cmd-ch-info">
                            <div class="cmd-ch-name">{{ $client['name'] }}</div>
                            <div class="cmd-ch-meta">{{ $client['open_count'] }} open Â· {{ $client['due_this_week'] }} due</div>
                        </div>
                        <div class="cmd-ch-badges">
                            @if($client['overdue'] > 0)
                                <span class="cmd-ch-overdue">{{ $client['overdue'] }} late</span>
                            @endif
                            <span class="cmd-ch-health" style="--hc:{{ $hColor }}">{{ $hLabel }}</span>
                        </div>
                    </div>
                @empty
                    <div class="cmd-empty-inline">No client activity</div>
                @endforelse
            </div>
        </div>

        {{-- Design Deadlines --}}
        <div class="cmd-card cmd-card-compact">
            <div class="cmd-card-head">
                <div class="cmd-card-title"><i class="fa-solid fa-pen-ruler" style="color:var(--purple)"></i> Design Deadlines</div>
            </div>
            <div class="cmd-dd-summary">
                <div class="cmd-dd-stat {{ $designDeadlineOverdue > 0 ? 'danger' : '' }}"><span>{{ $designDeadlineOverdue }}</span> Overdue</div>
                <div class="cmd-dd-stat warn"><span>{{ $designDeadlineSoon }}</span> 48h</div>
                <div class="cmd-dd-stat"><span>{{ $designDeadlineTasks->count() }}</span> 5 days</div>
            </div>
            <div class="cmd-card-scroll-sm">
                @forelse($designDeadlineTasks as $task)
                    @php
                        $ddDate = \Carbon\Carbon::parse($task->design_deadline);
                        $ddDiff = $ddDate->diffInDays(now(), false);
                        $ddColor = $ddDiff > 0 ? 'var(--red)' : ($ddDiff >= -2 ? 'var(--yellow)' : 'var(--text3)');
                    @endphp
                    <div class="cmd-task-row cmd-task-row-sm">
                        <span class="cmd-dd-date" style="color:{{ $ddColor }}">{{ $ddDate->format('M d') }}@if($ddDiff > 0) <small>({{ (int)$ddDiff }}d)</small>@endif</span>
                        <div class="cmd-task-body">
                            <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            @if($task->client->logo)
                                <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:12px;height:12px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                            @else
                                <x-client-branding :client="$task->client" size="22px" radius="5px" />
                            @endif
                            {{ $task->client->name }} Â· {{ $task->assignee?->name ?? 'Unassigned' }}
                        </div>
                        </div>
                        <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                    </div>
                @empty
                    <div class="cmd-empty-inline">No upcoming design deadlines</div>
                @endforelse
            </div>
        </div>

        {{-- Stuck Tasks --}}
        <div class="cmd-card cmd-card-compact">
            <div class="cmd-card-head">
                <div class="cmd-card-title"><i class="fa-solid fa-hourglass-half" style="color:var(--red)"></i> Stuck Tasks</div>
                <span class="cmd-badge-red">{{ $stuckTasks->count() }}</span>
            </div>
            <div class="cmd-card-scroll-sm">
                @forelse($stuckTasks as $task)
                    <div class="cmd-task-row cmd-task-row-sm">
                        <span class="cmd-stuck-days">{{ $task->stuck_days }}d</span>
                        <div class="cmd-task-body">
                            <div class="cmd-task-title">{{ $task->title }}</div>
                            <div class="cmd-task-meta">
                                <span class="status {{ $task->status_class }}" style="font-size:9px;padding:1px 6px">{{ $task->status_label }}</span>
                                Â· {!! $task->client->emoji ?? '' !!} {{ $task->client->name }}
                                Â· {{ $task->assignee?->name ?? 'Unassigned' }}
                            </div>
                        </div>
                        <a href="{{ route('strategist.tracking', ['task' => $task->id]) }}" class="cmd-btn-sm cmd-btn-ghost">Nudge</a>
                    </div>
                @empty
                    <div class="cmd-empty-inline"><i class="fas fa-check-circle" style="color:var(--teal)"></i> All tasks moving!</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ===== BOTTOM GRID: INSIGHTS & DETAILS ===== --}}
<div class="cmd-bottom-grid">

    {{-- Publishing Overview --}}
    <div class="cmd-card">
        <div class="cmd-card-head">
            <div class="cmd-card-title"><i class="fa-solid fa-tower-broadcast" style="color:var(--primary)"></i> Publishing</div>
            <a href="{{ route('strategist.publishing') }}" class="cmd-card-link">Queue <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="cmd-pub-stats">
            <div class="cmd-pub-stat" style="--pc:var(--primary)"><div class="cmd-pub-num">{{ $myPublishingTotal }}</div><div class="cmd-pub-label">Total</div></div>
            <div class="cmd-pub-stat" style="--pc:#F59E0B"><div class="cmd-pub-num">{{ $myPublishingAwaiting }}</div><div class="cmd-pub-label">Awaiting</div></div>
            <div class="cmd-pub-stat" style="--pc:#3B82F6"><div class="cmd-pub-num">{{ $myPublishingPartial }}</div><div class="cmd-pub-label">Partial</div></div>
            <div class="cmd-pub-stat" style="--pc:#10B981"><div class="cmd-pub-num">{{ $myPublishingDone }}</div><div class="cmd-pub-label">Published</div></div>
        </div>
        @if($myPublishingByPlatform->count())
            @php
                $pIcons = ['instagram'=>'fa-instagram','facebook'=>'fa-facebook','linkedin'=>'fa-linkedin','twitter'=>'fa-x-twitter','tiktok'=>'fa-tiktok','youtube'=>'fa-youtube'];
                $pColors = ['instagram'=>'#E1306C','facebook'=>'#1877F2','linkedin'=>'#0A66C2','twitter'=>'#1DA1F2','tiktok'=>'#010101','youtube'=>'#FF0000'];
            @endphp
            <div class="cmd-pub-platforms">
                @foreach($myPublishingByPlatform as $platform => $count)
                    <div class="cmd-pub-pill" style="--pp-c:{{ $pColors[$platform] ?? '#6B7280' }}">
                        <i class="fa-brands {{ $pIcons[$platform] ?? 'fa-globe' }}"></i> {{ ucfirst($platform) }} <strong>{{ $count }}</strong>
                    </div>
                @endforeach
            </div>
        @endif
        @if($myRecentPublishing->count())
            <div class="cmd-section-label"><i class="fa-solid fa-clock-rotate-left"></i> Recent</div>
            @foreach($myRecentPublishing as $proof)
                @php
                    $pIcons2 = ['instagram'=>'fa-instagram','facebook'=>'fa-facebook','linkedin'=>'fa-linkedin','twitter'=>'fa-x-twitter','tiktok'=>'fa-tiktok','youtube'=>'fa-youtube'];
                    $pColors2 = ['instagram'=>'#E1306C','facebook'=>'#1877F2','linkedin'=>'#0A66C2','twitter'=>'#1DA1F2','tiktok'=>'#010101','youtube'=>'#FF0000'];
                @endphp
                <div class="cmd-pub-recent">
                    <i class="fa-brands {{ $pIcons2[$proof->platform] ?? 'fa-globe' }}" style="color:{{ $pColors2[$proof->platform] ?? '#6B7280' }};font-size:14px;width:20px;text-align:center"></i>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:12px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $proof->task->title ?? 'Unknown' }}</div>
                        <div style="font-size:10px;color:var(--text3)">
                            <x-client-branding :client="$proof->task->client" size="20px" radius="4px" />
                            {{ $proof->task->client->name ?? '' }} Â· {{ $proof->created_at->diffForHumans() }}
                        </div>
                    </div>
                    @if($proof->post_url)
                        <a href="{{ $proof->post_url }}" target="_blank" style="color:var(--primary);font-size:10px"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    {{-- Content & Platform Mix --}}
    <div class="cmd-card">
        <div class="cmd-card-head">
            <div class="cmd-card-title"><i class="fa-solid fa-palette" style="color:#EC4899"></i> Content Mix</div>
        </div>
        @php
            $typeTotal = array_sum($typeDistribution);
            $typeColors = ['reel'=>'#F97316','post'=>'#3B82F6','story'=>'#14B8A6','video'=>'#8B5CF6','carousel'=>'#EC4899'];
            $typeEmojis = ['reel'=>'<i class="fas fa-film"></i>','post'=>'<i class="fas fa-pen-fancy"></i>','story'=>'<i class="fas fa-mobile-alt"></i>','video'=>'<i class="fas fa-video"></i>','carousel'=>'<i class="fas fa-images"></i>'];
            $platTotal = array_sum($platformDistribution);
            $platColors = ['instagram'=>'#E1306C','facebook'=>'#1877F2','linkedin'=>'#0A66C2','twitter'=>'#1DA1F2','youtube'=>'#FF0000','tiktok'=>'#000000'];
            $platIcons = ['instagram'=>'fa-instagram','facebook'=>'fa-facebook','linkedin'=>'fa-linkedin','twitter'=>'fa-x-twitter','youtube'=>'fa-youtube','tiktok'=>'fa-tiktok'];
        @endphp

        <div class="cmd-section-label"><i class="fa-solid fa-shapes"></i> By Type</div>
        @if($typeTotal > 0)
            <div class="cmd-mix-bar">
                @foreach($typeDistribution as $type => $count)
                    <div style="flex:{{ $count }};background:{{ $typeColors[$type] ?? '#6B7280' }};border-radius:99px;min-width:4px" title="{{ ucfirst($type) }}: {{ $count }}"></div>
                @endforeach
            </div>
            <div class="cmd-mix-legend">
                @foreach($typeDistribution as $type => $count)
                    <div class="cmd-mix-item">
                        <span class="cmd-mix-dot" style="background:{{ $typeColors[$type] ?? '#6B7280' }}"></span>
                        {!! $typeEmojis[$type] ?? '' !!} {{ ucfirst($type) }}
                        <span class="cmd-mix-count">{{ $count }}</span>
                        <span class="cmd-mix-pct">{{ round(($count/$typeTotal)*100) }}%</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cmd-empty-inline">No active content</div>
        @endif

        <div class="cmd-section-label" style="margin-top:14px"><i class="fa-solid fa-globe"></i> By Platform</div>
        @if($platTotal > 0)
            <div class="cmd-mix-bar">
                @foreach($platformDistribution as $plat => $count)
                    <div style="flex:{{ $count }};background:{{ $platColors[$plat] ?? '#6B7280' }};border-radius:99px;min-width:4px" title="{{ ucfirst($plat) }}: {{ $count }}"></div>
                @endforeach
            </div>
            <div class="cmd-plat-pills">
                @foreach($platformDistribution as $plat => $count)
                    <div class="cmd-plat-pill" style="--pp-c:{{ $platColors[$plat] ?? '#6B7280' }}">
                        <i class="fa-brands {{ $platIcons[$plat] ?? 'fa-globe' }}"></i> {{ ucfirst($plat) }} <strong>{{ $count }}</strong>
                    </div>
                @endforeach
            </div>
        @else
            <div class="cmd-empty-inline">No platform data</div>
        @endif
    </div>

    {{-- Collaboration --}}
    <div class="cmd-card">
        <div class="cmd-card-head">
            <div class="cmd-card-title"><i class="fa-solid fa-comments" style="color:var(--blue)"></i> Collaboration</div>
            <div class="cmd-head-pills">
                <span class="cmd-head-pill">{{ $totalComments }} total</span>
                <span class="cmd-head-pill cmd-head-pill-accent">+{{ $commentsThisWeek }} wk</span>
            </div>
        </div>
        <div class="cmd-card-scroll-sm">
            @if($mostCommentedTasks->count() > 0)
                <div class="cmd-section-label"><i class="fa-solid fa-fire-flame-curved"></i> Hot Threads</div>
                @foreach($mostCommentedTasks as $task)
                    <div class="cmd-task-row cmd-task-row-sm">
                        <span class="cmd-comment-badge"><i class="fas fa-comment" style="font-size:9px"></i> {{ $task->comments_count }}</span>
                        <div class="cmd-task-body">
                            <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            @if($task->client->logo)
                                <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:12px;height:12px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                            @else
                                <x-client-branding :client="$task->client" size="22px" radius="5px" />
                            @endif
                            {{ $task->client->name ?? '' }}
                        </div>
                        </div>
                        <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                    </div>
                @endforeach
            @endif
            @if($recentComments->count() > 0)
                <div class="cmd-section-label" style="margin-top:10px"><i class="fa-regular fa-message"></i> Latest</div>
                @foreach($recentComments as $comment)
                    <div class="cmd-rc-row">
                        <div class="cmd-rc-avatar" style="background:{{ $comment->user->avatar_color ?? '#555' }}">{{ $comment->user->initial }}</div>
                        <div style="flex:1;min-width:0">
                            <div class="cmd-rc-text">{{ Str::limit($comment->body, 55) }}</div>
                            <div class="cmd-rc-meta">{{ $comment->user->name }} on <strong>{{ Str::limit($comment->task->title ?? '', 20) }}</strong> Â· {{ $comment->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @endforeach
            @endif
            @if($mostCommentedTasks->count() === 0 && $recentComments->count() === 0)
                <div class="cmd-empty-inline">No comments yet</div>
            @endif
        </div>
    </div>
</div>

{{-- ===== SECONDARY GRID: WEEKLY + SHOOT DAYS + PRESSURE ===== --}}
<div class="cmd-secondary-grid">

    {{-- Weekly Comparison + Content Readiness --}}
    <div class="cmd-card">
        <div class="cmd-card-head">
            <div class="cmd-card-title"><i class="fa-solid fa-scale-balanced" style="color:var(--purple)"></i> Weekly Pulse</div>
        </div>
        @if($unreadNotifications > 0)
            <div class="cmd-notif-banner">
                <i class="fa-solid fa-bell"></i> {{ $unreadNotifications }} unread notification{{ $unreadNotifications > 1 ? 's' : '' }}
            </div>
        @endif
        <div class="cmd-wc-grid">
            @php
                $wcItems = [
                    ['label' => 'Created', 'this' => $createdThisWeek, 'last' => $createdLastWeek, 'icon' => 'fa-plus-circle', 'color' => 'var(--blue)'],
                    ['label' => 'Completed', 'this' => $completedThisWeek, 'last' => $completedLastWeek, 'icon' => 'fa-circle-check', 'color' => 'var(--teal)'],
                    ['label' => 'Overdue', 'this' => $overdueThisWeek, 'last' => $overdueLastWeek, 'icon' => 'fa-triangle-exclamation', 'color' => 'var(--red)'],
                ];
            @endphp



            @foreach($wcItems as $item)
    @php
        $diff = $item['this'] - $item['last'];
        $amount = abs($diff);

        if ($diff == 0) {
            $diffDisplay = 'Same as last week';
        } elseif ($diff > 0) {
            $diffDisplay = $amount . ' more than last week';
        } else {
            $diffDisplay = $amount . ' fewer than last week';
        }

        if ($item['label'] === 'Overdue') {
            $diffColor = $diff > 0 ? 'var(--red)' : 'var(--teal)';
        } else {
            if ($diff > 0) {
                $diffColor = 'var(--teal)';
            } elseif ($diff < 0) {
                $diffColor = 'var(--red)';
            } else {
                $diffColor = 'var(--text3)';
            }
        }
    @endphp

    <div class="cmd-wc-card">
        <i class="fa-solid {{ $item['icon'] }}"
           style="color:{{ $item['color'] }};font-size:14px"></i>

        <div class="cmd-wc-label">{{ $item['label'] }}</div>

        <div class="cmd-wc-vals">
            <span class="cmd-wc-this">{{ $item['this'] }}</span>
            <span class="cmd-wc-vs">vs</span>
            <span class="cmd-wc-last">{{ $item['last'] }}</span>
        </div>

        <div class="cmd-wc-diff"
             style="color:{{ $diffColor }};font-size:11px;font-weight:700;">
            {{ $diffDisplay }}
        </div>
    </div>
@endforeach



        </div>

        <div class="cmd-section-label" style="margin-top:16px"><i class="fa-solid fa-clipboard-check"></i> Content Readiness</div>
        <div class="cmd-cr-bars">
            <div class="cmd-cr-row">
                <span class="cmd-cr-label"><i class="fas fa-pen-fancy" style="font-size:10px;opacity:.6"></i> Captions</span>
                <div class="cmd-cr-track"><div class="cmd-cr-fill" style="width:{{ $captionFillRate }}%;background:var(--teal)"></div></div>
                <span class="cmd-cr-pct">{{ $captionFillRate }}%</span>
            </div>
            <div class="cmd-cr-row">
                <span class="cmd-cr-label"><i class="fas fa-image" style="font-size:10px;opacity:.6"></i> Media</span>
                <div class="cmd-cr-track"><div class="cmd-cr-fill" style="width:{{ $totalActive > 0 ? round((($totalActive-$missingMediaCount)/$totalActive)*100) : 100 }}%;background:var(--blue)"></div></div>
                <span class="cmd-cr-pct">{{ $totalActive > 0 ? round((($totalActive-$missingMediaCount)/$totalActive)*100) : 100 }}%</span>
            </div>
            <div class="cmd-cr-row">
                <span class="cmd-cr-label"><i class="fas fa-calendar-alt" style="font-size:10px;opacity:.6"></i> Deadlines</span>
                <div class="cmd-cr-track"><div class="cmd-cr-fill" style="width:{{ $totalActive > 0 ? round((($totalActive-$missingDeadline)/$totalActive)*100) : 100 }}%;background:var(--purple)"></div></div>
                <span class="cmd-cr-pct">{{ $totalActive > 0 ? round((($totalActive-$missingDeadline)/$totalActive)*100) : 100 }}%</span>
            </div>
        </div>
        <div class="cmd-cr-overall">
            <span>Overall</span>
            <span class="cmd-cr-overall-val" style="color:{{ $contentReadiness >= 70 ? 'var(--teal)' : ($contentReadiness >= 40 ? 'var(--yellow)' : 'var(--red)') }}">{{ $contentReadiness }}%</span>
        </div>
    </div>

    {{-- Shoot Days --}}
    <div class="cmd-card">
        <div class="cmd-card-head">
            <div class="cmd-card-title"><i class="fa-solid fa-camera" style="color:var(--purple)"></i> Shoot Days</div>
            <button class="cmd-card-link" onclick="document.getElementById('sdFormWrap').classList.toggle('sd-hidden')" style="background:none;border:none;cursor:pointer;font:inherit;color:var(--primary)">
                <i class="fa-solid fa-plus"></i> Schedule
            </button>
        </div>

        <div class="cmd-sd-status-row">
            <span class="cmd-sd-badge cmd-sd-inprogress"><i class="fas fa-clock"></i> {{ $shootDaysInProgress }}</span>
            <span class="cmd-sd-badge cmd-sd-overdue"><i class="fas fa-exclamation-triangle"></i> {{ $shootDaysOverdue }}</span>
            <span class="cmd-sd-badge cmd-sd-completed"><i class="fas fa-check-circle"></i> {{ $shootDaysCompleted }}</span>
            <a href="{{ route('strategist.shoot-days-all') }}" class="cmd-sd-viewall">All <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        {{-- Inline Create Form --}}
        <div id="sdFormWrap" class="sd-hidden">
            <form id="sdCreateForm" action="{{ route('strategist.shoot-days.store') }}" method="POST" class="cmd-sd-form">
                @csrf
                <div class="cmd-sd-form-grid">
                    <div class="cmd-sd-field">
                        <label>Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Product shoot">
                    </div>
                    <div class="cmd-sd-field">
                        <label>Type *</label>
                        <select name="type" required>
                            <option value="photo">Photo</option>
                            <option value="video">Video</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div class="cmd-sd-field">
                        <label>Date *</label>
                        <input type="date" name="shoot_date" required min="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="cmd-sd-field">
                        <label>Start Time</label>
                        <input type="time" name="start_date">
                    </div>
                    <div class="cmd-sd-field">
                        <label>Days</label>
                        <input type="text" name="number_of_days" placeholder="# days">
                    </div>
                    <div class="cmd-sd-field">
                        <label>Location</label>
                        <input type="text" name="location" placeholder="Studio / Address">
                    </div>
                    <div class="cmd-sd-field">
                        <label>Client</label>
                        <x-client-select :clients="$clients" name="client_id" placeholder="— None —" />
                    </div>
                </div>
                <div class="cmd-sd-field" style="margin-top:6px">
                    <label>Notes</label>
                    <textarea name="notes" rows="2" placeholder="Equipment, people, instructions..."></textarea>
                </div>
                <div style="display:flex;gap:8px;margin-top:8px">
                    <button type="submit" class="cmd-btn-sm" style="padding:6px 14px"><i class="fa-solid fa-check"></i> Save</button>
                    <button type="button" class="cmd-btn-sm cmd-btn-ghost" style="padding:6px 14px" onclick="document.getElementById('sdFormWrap').classList.add('sd-hidden')">Cancel</button>
                </div>
            </form>
        </div>

        {{-- Shoots Table --}}
        <div class="cmd-card-scroll">
            @if($shootDays->count() > 0)
                <table class="cmd-sd-table">
                    <thead>
                        <tr><th>Type</th><th>Title</th><th>Date</th><th>Time</th><th>Client</th><th>Location</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($shootDays as $shoot)
                            <tr class="{{ $shoot->shoot_date->isToday() ? 'cmd-sd-today' : '' }} cmd-sd-st-{{ $shoot->status }}">
                                <td class="cmd-sd-type">
                                    {!! $shoot->type === 'video' ? '<i class="fas fa-video" style="color:var(--purple)"></i>' : ($shoot->type === 'photo' ? '<i class="fas fa-camera" style="color:var(--yellow)"></i>' : '<i class="fas fa-camera-retro" style="color:var(--blue)"></i>') !!}
                                </td>
                                <td><strong>{{ $shoot->title }}</strong></td>
                                <td>{{ $shoot->shoot_date->format('M d') }}</td>
                                <td style="font-family:monospace;font-size:11px">
                                    @if($shoot->start_date){{ \Carbon\Carbon::parse($shoot->start_date)->format('g:i A') }}@else â€”@endif
                                </td>
                                <td>
                                    @if($shoot->client)<x-client-branding :client="$shoot->client" size="22px" radius="5px" :showName="true" />@else —@endif
                                </td>
                                <td style="color:var(--text2)">{{ $shoot->location ? Str::limit($shoot->location, 15) : 'â€”' }}</td>
                                <td class="cmd-sd-actions">
                                    <form action="{{ route('strategist.shoot-days.update', $shoot) }}" method="POST" style="display:inline">
                                        @csrf @method('PATCH')
                                        @if($shoot->status === 'scheduled')
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="cmd-sd-act" title="Start"><i class="fa-solid fa-play"></i></button>
                                        @elseif($shoot->status === 'in_progress')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="cmd-sd-act cmd-sd-act-done" title="Complete"><i class="fa-solid fa-check"></i></button>
                                        @endif
                                    </form>
                                    <form action="{{ route('strategist.shoot-days.destroy', $shoot) }}" method="POST" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="cmd-sd-act cmd-sd-act-del" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="cmd-empty-state">
                    <i class="fas fa-camera" style="font-size:20px;color:var(--text3)"></i>
                    <div>No upcoming shoots</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Scheduling Pressure + Client Category --}}
    <div class="cmd-card">
        <div class="cmd-card-head">
            <div class="cmd-card-title"><i class="fa-solid fa-bolt" style="color:var(--red)"></i> Pressure & Categories</div>
        </div>
        <div class="cmd-section-label"><i class="fa-solid fa-bolt"></i> Tight Scheduling</div>
        <div class="cmd-card-scroll-sm">
            @forelse($schedulingPressure as $task)
                <div class="cmd-task-row cmd-task-row-sm">
                    <span class="cmd-sp-gap {{ $task->gap_days <= 0 ? 'danger' : 'warn' }}">{{ $task->gap_days }}d</span>
                    <div class="cmd-task-body">
                        <div class="cmd-task-title">{{ $task->title }}</div>
                        <div class="cmd-task-meta">
                            DL: {{ \Carbon\Carbon::parse($task->deadline)->format('M d') }}
                            Â· Post: {{ \Carbon\Carbon::parse($task->post_date)->format('M d') }}
                            Â· {!! $task->client->emoji ?? '' !!} {{ $task->client->name }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="cmd-empty-inline"><i class="fas fa-check-circle" style="color:var(--teal)"></i> No tight gaps</div>
            @endforelse
        </div>

        @if(count($clientCategoryBreakdown) > 0)
            <div class="cmd-section-label" style="margin-top:14px"><i class="fa-solid fa-tags"></i> By Client Category</div>
            @php $catTotal = max(1, array_sum($clientCategoryBreakdown)); @endphp
            @foreach($clientCategoryBreakdown as $cat => $cnt)
                @php $catColors = ['#3B82F6','#10B981','#8B5CF6','#F97316','#EC4899','#14B8A6','#F59E0B','#EF4444']; @endphp
                <div class="cmd-cat-row">
                    <span class="cmd-cat-label">{{ $cat }}</span>
                    <div class="cmd-cat-track"><div class="cmd-cat-fill" style="width:{{ round(($cnt/$catTotal)*100) }}%;background:{{ $catColors[$loop->index % count($catColors)] }}"></div></div>
                    <span class="cmd-cat-count">{{ $cnt }}</span>
                </div>
            @endforeach
        @endif
    </div>
</div>

{{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    STYLES
    â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/strategist-dashboard.css') }}">
@endpush

@push('scripts')<script>window.StrategistDashboardData = {    completedTasks: '{{ $completedTasks }}',    productivityScoreLabel: '{{ $productivityScore }}/100',    overdueAndDueSoon: '{{ $overdueCount + $dueSoonCount }}',    pendingApprovals: '{{ $pendingApprovals }}',    inProgressCount: '{{ $inProgressCount }}',    pendingAssign: '{{ $pendingAssign }}'};</script><script src="{{ asset('js/pages/strategist-dashboard.js') }}"></script>@endpush

{{-- â•â•â•â•â•â• Floating AI Assistant (Mia) â•â•â•â•â•â• --}}
@include('strategist.partials.assistant')

@endsection
