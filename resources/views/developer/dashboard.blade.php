@extends('layouts.app')

@section('content')
<style>
:root {
    --dev-red: #f13535;
    --dev-red-dark: #c92323;
    --dev-red-dim: rgba(241,53,53,0.08);
    --dev-red-glow: rgba(241,53,53,0.25);
}

/* ── Wrap ── */
.dv-wrap {
    padding: 4px 0 40px;
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    color: var(--text);
}

/* ── Hero ── */
.dv-hero {
    position: relative;
    overflow: hidden;
    background: #f54436;
    border-radius: var(--radius, 16px);
    padding: 32px 36px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    min-height: 160px;
}
.dv-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(241,53,53,0.35) 0%, transparent 70%);
    pointer-events: none;
}
.dv-hero::after {
    content: '';
    position: absolute;
    bottom: -40px; left: 30%;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(241,53,53,0.15) 0%, transparent 70%);
    pointer-events: none;
}
.dv-hero-left { position: relative; z-index: 1; }
.dv-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.85);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    padding: 5px 12px;
    border-radius: 99px;
    margin-bottom: 12px;
}
.dv-hero-badge span { width: 6px; height: 6px; border-radius: 50%; background: #4ade80; display: inline-block; box-shadow: 0 0 0 3px rgba(74,222,128,0.3); animation: devPulse 2s ease-in-out infinite; }
@keyframes devPulse { 0%,100%{box-shadow:0 0 0 0 rgba(74,222,128,0.4);} 50%{box-shadow:0 0 0 6px rgba(74,222,128,0);} }
.dv-hero-title {
    font-size: 28px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 6px;
    line-height: 1.2;
}
.dv-hero-sub {
    font-size: 13px;
    color: rgba(255,255,255,0.6);
    margin: 0;
}
.dv-hero-right {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}
.dv-hero-chip {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,0.15);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    padding: 10px 18px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dv-hero-link {
    background: #fff;
    color: var(--dev-red-dark);
    font-size: 13px;
    font-weight: 700;
    padding: 10px 18px;
    border-radius: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.2s;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}
.dv-hero-link:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }

/* ── Stat Cards ── */
.dv-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.dv-stat {
    background: var(--card, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: var(--radius, 16px);
    padding: 20px 22px;
    position: relative;
    overflow: hidden;
    transition: all 0.25s ease;
    cursor: default;
}
.dv-stat:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.07); }
.dv-stat::before {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 0 0 var(--radius, 16px) var(--radius, 16px);
}
.dv-stat-red::before { background: linear-gradient(90deg, var(--dev-red), var(--dev-red-dark)); }
.dv-stat-orange::before { background: linear-gradient(90deg, #f97316, #ea580c); }
.dv-stat-green::before { background: linear-gradient(90deg, #10b981, #059669); }
.dv-stat-blue::before { background: linear-gradient(90deg, #3b82f6, #1d4ed8); }

.dv-stat-icon {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    margin-bottom: 14px;
}
.dv-stat-red .dv-stat-icon   { background: var(--dev-red-dim); color: var(--dev-red); }
.dv-stat-orange .dv-stat-icon { background: rgba(249,115,22,0.1); color: #f97316; }
.dv-stat-green .dv-stat-icon  { background: rgba(16,185,129,0.1); color: #10b981; }
.dv-stat-blue .dv-stat-icon   { background: rgba(59,130,246,0.1); color: #3b82f6; }

.dv-stat-val {
    font-size: 34px;
    font-weight: 800;
    color: var(--text, #111827);
    line-height: 1;
    margin-bottom: 4px;
}
.dv-stat-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text3, #9ca3af);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ── Quick Nav ── */
.dv-nav-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}
.dv-nav-card {
    background: var(--card, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: var(--radius, 16px);
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    text-decoration: none;
    color: inherit;
    transition: all 0.25s ease;
}
.dv-nav-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.07); border-color: var(--dev-red); }
.dv-nav-card-icon {
    width: 46px; height: 46px;
    background: var(--dev-red-dim);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    color: var(--dev-red);
    font-size: 18px;
    flex-shrink: 0;
}
.dv-nav-card-info { flex: 1; min-width: 0; }
.dv-nav-card-title { font-size: 14px; font-weight: 700; color: var(--text, #111827); margin-bottom: 3px; }
.dv-nav-card-sub { font-size: 12px; color: var(--text3, #9ca3af); }
.dv-nav-card-arrow {
    width: 34px; height: 34px;
    background: var(--dev-red-dim);
    color: var(--dev-red);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    transition: all 0.2s;
    flex-shrink: 0;
}
.dv-nav-card:hover .dv-nav-card-arrow { background: var(--dev-red); color: #fff; }

/* ── Main grid ── */
.dv-main-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    align-items: start;
}

/* ── Card base ── */
.dv-card {
    background: var(--card, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: var(--radius, 16px);
    overflow: hidden;
}
.dv-card-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px;
    border-bottom: 1px solid var(--border, #e5e7eb);
}
.dv-card-title {
    font-size: 14px; font-weight: 700; color: var(--text, #111827);
    display: flex; align-items: center; gap: 8px;
}
.dv-card-title i { color: var(--dev-red); font-size: 14px; }
.dv-card-link {
    font-size: 12px; font-weight: 600; color: var(--dev-red);
    text-decoration: none;
    display: inline-flex; align-items: center; gap: 4px;
}
.dv-card-link:hover { text-decoration: underline; }
.dv-card-body { padding: 6px 0; }

/* ── Project rows ── */
.dv-project-row {
    display: flex; align-items: center; gap: 12px;
    padding: 13px 22px;
    border-bottom: 1px solid var(--border, #f3f4f6);
    transition: background 0.15s;
    text-decoration: none;
    color: inherit;
}
.dv-project-row:last-child { border-bottom: none; }
.dv-project-row:hover { background: var(--card2, #f9fafb); }
.dv-project-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.dot-todo     { background: #9ca3af; }
.dot-inprogress { background: var(--dev-red); box-shadow: 0 0 0 3px var(--dev-red-dim); animation: devPulse 2s ease-in-out infinite; }
.dot-completed  { background: #10b981; }
.dot-review    { background: #f97316; }
.dv-project-info { flex: 1; min-width: 0; }
.dv-project-title {
    font-size: 13px; font-weight: 600; color: var(--text, #111827);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    margin-bottom: 3px;
}
.dv-project-meta { font-size: 11.5px; color: var(--text3, #9ca3af); }
.dv-status-pill {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.4px; padding: 3px 10px; border-radius: 99px;
    flex-shrink: 0;
}
.pill-todo     { background: rgba(156,163,175,0.15); color: #6b7280; }
.pill-inprogress { background: var(--dev-red-dim); color: var(--dev-red); }
.pill-completed  { background: rgba(16,185,129,0.12); color: #059669; }
.pill-review    { background: rgba(249,115,22,0.12); color: #f97316; }

/* ── Deadline items ── */
.dv-deadline-row {
    display: flex; align-items: center; gap: 12px;
    padding: 13px 22px;
    border-bottom: 1px solid var(--border, #f3f4f6);
    transition: background 0.15s;
}
.dv-deadline-row:last-child { border-bottom: none; }
.dv-deadline-row:hover { background: var(--card2, #f9fafb); }
.dv-deadline-cal {
    min-width: 44px; text-align: center;
    background: var(--dev-red-dim);
    border: 1px solid rgba(241,53,53,0.15);
    border-radius: 10px;
    padding: 6px 4px;
}
.dv-deadline-cal-mon { font-size: 9px; font-weight: 700; color: var(--dev-red); text-transform: uppercase; }
.dv-deadline-cal-day { font-size: 17px; font-weight: 800; color: var(--dev-red); line-height: 1; }
.dv-deadline-info { flex: 1; min-width: 0; }
.dv-deadline-title { font-size: 13px; font-weight: 600; color: var(--text, #111827); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dv-deadline-diff { font-size: 11px; color: var(--text3, #9ca3af); margin-top: 2px; }

/* ── Summary ── */
.dv-summary-list { list-style: none; padding: 0; margin: 0; }
.dv-summary-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 13px 22px;
    border-bottom: 1px solid var(--border, #f3f4f6);
    font-size: 13px;
}
.dv-summary-row:last-child { border-bottom: none; }
.dv-summary-label { color: var(--text3, #9ca3af); font-weight: 500; display: flex; align-items: center; gap: 7px; }
.dv-summary-val { font-weight: 800; color: var(--text, #111827); font-size: 15px; }

/* ── Empty state ── */
.dv-empty {
    padding: 36px 20px; text-align: center;
    color: var(--text3, #9ca3af);
}
.dv-empty i { font-size: 28px; margin-bottom: 10px; opacity: 0.4; display: block; }
.dv-empty p { font-size: 13px; margin: 0; }

/* ── Responsive ── */
@media (max-width: 1024px) {
    .dv-stats { grid-template-columns: repeat(2, 1fr); }
    .dv-main-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .dv-hero { flex-direction: column; align-items: flex-start; padding: 24px 20px; }
    .dv-hero-right { flex-direction: column; align-items: flex-start; width: 100%; }
    .dv-hero-title { font-size: 22px; }
    .dv-nav-grid { grid-template-columns: 1fr; }
    .dv-stats { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="dv-wrap">

    {{-- ── HERO ── --}}
    <div class="dv-hero">
        <div class="dv-hero-left">
            <div class="dv-hero-badge">
                <span></span> Developer Portal
            </div>
            <h1 class="dv-hero-title">Hello, {{ Auth::user()->name }} 👋</h1>
            <p class="dv-hero-sub">{{ now()->format('l, d F Y') }} · {{ $inProgressTasks > 0 ? $inProgressTasks . ' task' . ($inProgressTasks > 1 ? 's' : '') . ' in progress' : 'All caught up!' }}</p>
        </div>
        <div class="dv-hero-right">
            <div class="dv-hero-chip">
                @if($inProgressTasks > 0)
                    <i class="fas fa-fire" style="color:#fbbf24"></i> {{ $inProgressTasks }} In Progress
                @else
                    <i class="fas fa-check-circle" style="color:#4ade80"></i> All Caught Up
                @endif
            </div>
            <a href="{{ route('developer.tasks') }}" class="dv-hero-link">
                <i class="fas fa-laptop-code"></i> My Tasks
            </a>
        </div>
    </div>

    {{-- ── STAT CARDS ── --}}
    <div class="dv-stats">
        <div class="dv-stat dv-stat-red">
            <div class="dv-stat-icon"><i class="fas fa-layer-group"></i></div>
            <div class="dv-stat-val">{{ $totalTasks }}</div>
            <div class="dv-stat-label">Total Projects</div>
        </div>
        <div class="dv-stat dv-stat-orange">
            <div class="dv-stat-icon"><i class="fas fa-spinner"></i></div>
            <div class="dv-stat-val">{{ $inProgressTasks }}</div>
            <div class="dv-stat-label">In Progress</div>
        </div>
        <div class="dv-stat dv-stat-green">
            <div class="dv-stat-icon"><i class="fas fa-check-double"></i></div>
            <div class="dv-stat-val">{{ $completedTasks }}</div>
            <div class="dv-stat-label">Completed</div>
        </div>
        <div class="dv-stat dv-stat-blue">
            <div class="dv-stat-icon"><i class="fas fa-calendar-clock"></i></div>
            <div class="dv-stat-val">{{ $upcomingDeadlines }}</div>
            <div class="dv-stat-label">Due This Week</div>
        </div>
    </div>

    {{-- ── QUICK NAV ── --}}
    <div class="dv-nav-grid">
        <a href="{{ route('developer.calendar') }}" class="dv-nav-card">
            <div class="dv-nav-card-icon"><i class="fas fa-calendar-days"></i></div>
            <div class="dv-nav-card-info">
                <div class="dv-nav-card-title">Task Calendar</div>
                <div class="dv-nav-card-sub">View all deadlines and delivery dates</div>
            </div>
            <div class="dv-nav-card-arrow"><i class="fas fa-arrow-right"></i></div>
        </a>
        <a href="{{ route('developer.tasks') }}" class="dv-nav-card">
            <div class="dv-nav-card-icon"><i class="fas fa-list-check"></i></div>
            <div class="dv-nav-card-info">
                <div class="dv-nav-card-title">All Projects</div>
                <div class="dv-nav-card-sub">Manage your full task list</div>
            </div>
            <div class="dv-nav-card-arrow"><i class="fas fa-arrow-right"></i></div>
        </a>
    </div>

    {{-- ── MAIN GRID ── --}}
    <div class="dv-main-grid">

        {{-- LEFT: Recent Projects --}}
        <div class="dv-card">
            <div class="dv-card-head">
                <div class="dv-card-title"><i class="fas fa-code-branch"></i> Recent Projects</div>
                <a href="{{ route('developer.tasks') }}" class="dv-card-link">View all <i class="fas fa-arrow-right" style="font-size:10px"></i></a>
            </div>
            <div class="dv-card-body">
                @if($tasks->count() > 0)
                    @foreach($tasks->take(6) as $task)
                        @php
                            $statusDot = ['todo'=>'dot-todo','inprogress'=>'dot-inprogress','completed'=>'dot-completed','review'=>'dot-review'][$task->status] ?? 'dot-todo';
                            $statusPill = ['todo'=>'pill-todo','inprogress'=>'pill-inprogress','completed'=>'pill-completed','review'=>'pill-review'][$task->status] ?? 'pill-todo';
                            $statusLabel = ['todo'=>'To Do','inprogress'=>'In Progress','completed'=>'Completed','review'=>'In Review'][$task->status] ?? ucfirst($task->status);
                        @endphp
                        <div class="dv-project-row">
                            <div class="dv-project-dot {{ $statusDot }}"></div>
                            <div class="dv-project-info">
                                <div class="dv-project-title">{{ $task->title }}</div>
                                <div class="dv-project-meta">
                                    <i class="fas fa-building" style="font-size:9px;margin-right:3px"></i>{{ $task->client->name ?? 'Unknown Client' }}
                                    @if($task->dev_deadline)
                                        &nbsp;·&nbsp;<i class="fas fa-calendar-alt" style="font-size:9px;margin-right:2px"></i>{{ $task->dev_deadline->format('M d, Y') }}
                                    @endif
                                </div>
                            </div>
                            <span class="dv-status-pill {{ $statusPill }}">{{ $statusLabel }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="dv-empty">
                        <i class="fas fa-mug-hot"></i>
                        <p>No projects assigned yet.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT col --}}
        <div style="display:grid;gap:20px;">

            {{-- Upcoming Deadlines --}}
            <div class="dv-card">
                <div class="dv-card-head">
                    <div class="dv-card-title"><i class="fas fa-clock"></i> Upcoming Deadlines</div>
                </div>
                <div class="dv-card-body">
                    @php
                        $upcomingTasks = $tasks->filter(fn($t) => $t->dev_deadline && $t->dev_deadline->isFuture())->sortBy('dev_deadline');
                    @endphp
                    @if($upcomingTasks->count() > 0)
                        @foreach($upcomingTasks->take(5) as $t)
                            <div class="dv-deadline-row">
                                <div class="dv-deadline-cal">
                                    <div class="dv-deadline-cal-mon">{{ $t->dev_deadline->format('M') }}</div>
                                    <div class="dv-deadline-cal-day">{{ $t->dev_deadline->format('d') }}</div>
                                </div>
                                <div class="dv-deadline-info">
                                    <div class="dv-deadline-title">{{ $t->title }}</div>
                                    <div class="dv-deadline-diff">
                                        @php $dl = $t->dev_deadline->diffInDays(now()); @endphp
                                        {{ $dl }} day{{ $dl !== 1 ? 's' : '' }} left
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="dv-empty">
                            <i class="fas fa-calendar-check"></i>
                            <p>No upcoming deadlines.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Project Summary --}}
            <div class="dv-card">
                <div class="dv-card-head">
                    <div class="dv-card-title"><i class="fas fa-chart-pie"></i> Project Summary</div>
                </div>
                <ul class="dv-summary-list">
                    <li class="dv-summary-row">
                        <span class="dv-summary-label"><i class="fas fa-layer-group" style="color:#9ca3af;font-size:12px"></i> Total Assigned</span>
                        <span class="dv-summary-val">{{ $totalTasks }}</span>
                    </li>
                    <li class="dv-summary-row">
                        <span class="dv-summary-label"><i class="fas fa-spinner" style="color:#f97316;font-size:12px"></i> Active</span>
                        <span class="dv-summary-val" style="color:#f97316">{{ $inProgressTasks }}</span>
                    </li>
                    <li class="dv-summary-row">
                        <span class="dv-summary-label"><i class="fas fa-check-double" style="color:#10b981;font-size:12px"></i> Completed</span>
                        <span class="dv-summary-val" style="color:#10b981">{{ $completedTasks }}</span>
                    </li>
                    <li class="dv-summary-row">
                        <span class="dv-summary-label"><i class="fas fa-circle-dot" style="color:#9ca3af;font-size:12px"></i> Pending</span>
                        <span class="dv-summary-val">{{ max(0, $totalTasks - $completedTasks - $inProgressTasks) }}</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>

</div>
@endsection
