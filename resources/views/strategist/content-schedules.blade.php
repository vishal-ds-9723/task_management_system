@extends('layouts.app')

@section('content')

{{-- Page Header --}}
<div class="cs-header">
    <div>
        <div class="cs-title"><i class="fa-solid fa-chart-bar" style="color:var(--primary);margin-right:6px"></i> Content Schedules</div>
        <div class="cs-subtitle">Monthly content plans & progress for all clients — {{ $monthLabel }}</div>
    </div>
    <div class="cs-month-nav">
        @php
            $prevMonth = $month - 1;
            $prevYear = $year;
            if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
            $nextMonth = $month + 1;
            $nextYear = $year;
            if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
        @endphp
        <a href="{{ route('strategist.content-schedules', ['month' => $prevMonth, 'year' => $prevYear]) }}" class="cs-nav-btn" title="Previous Month">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <span class="cs-month-label">{{ $monthLabel }}</span>
        <a href="{{ route('strategist.content-schedules', ['month' => $nextMonth, 'year' => $nextYear]) }}" class="cs-nav-btn" title="Next Month">
            <i class="fa-solid fa-chevron-right"></i>
        </a>
        @if($month !== now()->month || $year !== now()->year)
            <a href="{{ route('strategist.content-schedules') }}" class="cs-nav-btn cs-nav-today" title="Go to current month">
                <i class="fa-solid fa-rotate-left"></i> Today
            </a>
        @endif
    </div>
</div>

{{-- Overall Stats --}}
<div class="cs-stats-row">
    <div class="cs-stat">
        <div class="cs-stat-icon" style="background:rgba(99,102,241,.1);color:#6366F1"><i class="fa-solid fa-building"></i></div>
        <div class="cs-stat-body">
            <div class="cs-stat-val">{{ $clientsWithSchedule }}<span class="cs-stat-sub">/{{ $totalClients }}</span></div>
            <div class="cs-stat-label">Clients with Schedule</div>
        </div>
    </div>
    <div class="cs-stat">
        <div class="cs-stat-icon" style="background:rgba(59,130,246,.1);color:#3B82F6"><i class="fa-solid fa-layer-group"></i></div>
        <div class="cs-stat-body">
            <div class="cs-stat-val">{{ $overallPlanned }}</div>
            <div class="cs-stat-label">Total Planned</div>
        </div>
    </div>
    <div class="cs-stat">
        <div class="cs-stat-icon" style="background:rgba(16,185,129,.1);color:#10B981"><i class="fa-solid fa-circle-check"></i></div>
        <div class="cs-stat-body">
            <div class="cs-stat-val">{{ $overallDone }}</div>
            <div class="cs-stat-label">Completed</div>
        </div>
    </div>
    <div class="cs-stat">
        <div class="cs-stat-icon" style="background:rgba(249,115,22,.1);color:#F97316"><i class="fa-solid fa-spinner"></i></div>
        <div class="cs-stat-body">
            <div class="cs-stat-val">{{ $overallActive }}</div>
            <div class="cs-stat-label">In Progress</div>
        </div>
    </div>
    <div class="cs-stat cs-stat-pct">
        <div class="cs-stat-icon" style="background:{{ $overallPct >= 80 ? 'rgba(16,185,129,.1)' : ($overallPct >= 50 ? 'rgba(245,158,11,.1)' : 'rgba(239,68,68,.1)') }};color:{{ $overallPct >= 80 ? '#10B981' : ($overallPct >= 50 ? '#F59E0B' : '#EF4444') }}"><i class="fa-solid fa-trophy"></i></div>
        <div class="cs-stat-body">
            <div class="cs-stat-val" style="color:{{ $overallPct >= 80 ? '#10B981' : ($overallPct >= 50 ? '#F59E0B' : '#EF4444') }}">{{ $overallPct }}%</div>
            <div class="cs-stat-label">Overall Progress</div>
        </div>
    </div>
</div>

{{-- Overall progress bar --}}
<div class="cs-overall-bar">
    <div class="cs-bar-track">
        @if($overallPlanned > 0)
            <div class="cs-bar-done" style="width:{{ min(100, ($overallDone / $overallPlanned) * 100) }}%"></div>
            <div class="cs-bar-active" style="width:{{ min(100, (($overallDone + $overallActive) / $overallPlanned) * 100) }}%"></div>
        @endif
    </div>
    <div class="cs-bar-legend">
        <span><span class="cs-dot" style="background:#10B981"></span> Done ({{ $overallDone }})</span>
        <span><span class="cs-dot" style="background:rgba(59,130,246,0.5)"></span> In Progress ({{ $overallActive }})</span>
        <span><span class="cs-dot cs-dot-empty"></span> Remaining ({{ max(0, $overallPlanned - $overallDone - $overallActive) }})</span>
    </div>
</div>

{{-- Client Cards Grid --}}
<div class="cs-grid">
    @forelse($scheduleData as $data)
        @php
            $c = $data->client;
            $s = $data->schedule;
        @endphp
        <div class="cs-card {{ !$s ? 'cs-card-empty' : '' }}">
            {{-- Card Header --}}
            <div class="cs-card-head">
                <div class="cs-card-client">
                    @if($c->logo)
                        <img src="{{ asset('storage/' . $c->logo) }}" alt="" class="cs-client-logo">
                    @elseif($c->emoji)
                        <span class="cs-client-emoji">{{ $c->emoji }}</span>
                    @else
                        <div class="cs-client-avatar" style="background:{{ $c->color ?? '#6366F1' }}20;color:{{ $c->color ?? '#6366F1' }}">
                            {{ strtoupper(substr($c->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="cs-client-name">{{ $c->name }}</div>
                        @if($c->category)
                            <div class="cs-client-cat">{{ $c->category }}</div>
                        @endif
                    </div>
                </div>
                @if($s)
                    <div class="cs-card-pct" style="color:{{ $data->pct >= 100 ? '#10B981' : ($data->pct >= 50 ? '#F59E0B' : ($data->pct > 0 ? 'var(--primary)' : 'var(--text3)')) }}">
                        {{ $data->pct }}%
                    </div>
                @endif
            </div>

            @if($s)
                {{-- Carried badge --}}
                @if($data->isCarried)
                    <div class="cs-carried-badge">
                        <i class="fa-solid fa-arrow-rotate-left"></i> Carried from {{ $s->getMonthYearLabel() }}
                    </div>
                @endif

                {{-- Summary line --}}
                <div class="cs-summary">
                    <span><strong>{{ $data->totalDone }}</strong> done</span>
                    <span class="cs-sep">·</span>
                    <span><strong>{{ $data->totalActive }}</strong> active</span>
                    <span class="cs-sep">·</span>
                    <span><strong>{{ $data->totalPlanned }}</strong> planned</span>
                </div>

                {{-- Progress bar --}}
                <div class="cs-card-bar-track">
                    @if($data->totalPlanned > 0)
                        <div class="cs-card-bar-done" style="width:{{ min(100, ($data->totalDone / $data->totalPlanned) * 100) }}%"></div>
                        <div class="cs-card-bar-active" style="width:{{ min(100, (($data->totalDone + $data->totalActive) / $data->totalPlanned) * 100) }}%"></div>
                    @endif
                </div>

                {{-- Content type breakdown --}}
                <div class="cs-type-grid">
                    @foreach($contentTypes as $key => $type)
                        @php
                            $planned = $s->$key;
                            if ($planned == 0) continue;
                            $done = $data->progress[$key]['done'] ?? 0;
                            $active = $data->progress[$key]['active'] ?? 0;
                            $pct = $planned > 0 ? min(100, round(($done / $planned) * 100)) : 0;
                        @endphp
                        <div class="cs-type-item">
                            <div class="cs-type-icon" style="color:{{ $type['color'] }}"><i class="fa-solid {{ $type['icon'] }}"></i></div>
                            <div class="cs-type-info">
                                <div class="cs-type-name">{{ $type['label'] }}</div>
                                <div class="cs-type-count">
                                    <span style="font-weight:800;color:{{ $pct >= 100 ? '#10B981' : 'var(--text)' }}">{{ $done }}</span><span class="cs-type-of">/{{ $planned }}</span>
                                    @if($active > 0)
                                        <span class="cs-type-active">{{ $active }} active</span>
                                    @endif
                                </div>
                            </div>
                            <div class="cs-type-bar">
                                <div class="cs-type-bar-fill" style="width:{{ $pct }}%;background:{{ $type['color'] }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- No schedule --}}
                <div class="cs-empty-state">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <span>No schedule set</span>
                </div>
            @endif
        </div>
    @empty
        <div class="cs-no-clients">
            <i class="fa-solid fa-building" style="font-size:32px;opacity:0.2;margin-bottom:10px"></i>
            <div style="font-weight:700;color:var(--text)">No active clients found</div>
            <div style="font-size:12px;color:var(--text3);margin-top:4px">Create clients from the admin panel to get started</div>
        </div>
    @endforelse
</div>

<style>
/* ===== Content Schedules Page ===== */

/* Header */
.cs-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; gap:16px; flex-wrap:wrap; }
.cs-title { font-size:22px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:-0.3px; }
.cs-subtitle { font-size:12.5px; color:var(--text3); margin-top:3px; font-weight:500; }
.cs-month-nav { display:flex; align-items:center; gap:8px; }
.cs-nav-btn { width:36px; height:36px; border-radius:10px; border:1px solid var(--border); background:var(--card); display:flex; align-items:center; justify-content:center; color:var(--text2); font-size:13px; text-decoration:none; transition:all .15s; }
.cs-nav-btn:hover { background:var(--primary-dim); color:var(--primary); border-color:var(--primary); transform:translateY(-1px); }
.cs-nav-today { width:auto; padding:0 12px; gap:5px; font-size:11px; font-weight:600; }
.cs-month-label { font-size:15px; font-weight:700; color:var(--text); min-width:140px; text-align:center; }

/* Stats */
.cs-stats-row { display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
.cs-stat { display:flex; align-items:center; gap:10px; padding:12px 16px; background:var(--card); border:1px solid var(--border); border-radius:12px; flex:1; min-width:130px; box-shadow:var(--shadow-sm); }
.cs-stat-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
.cs-stat-val { font-size:20px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; line-height:1.1; }
.cs-stat-sub { font-size:13px; font-weight:600; color:var(--text3); }
.cs-stat-label { font-size:10.5px; color:var(--text3); font-weight:600; text-transform:uppercase; letter-spacing:.3px; margin-top:1px; }

/* Overall Bar */
.cs-overall-bar { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:14px 18px; margin-bottom:20px; box-shadow:var(--shadow-sm); }
.cs-bar-track { height:10px; background:var(--bg); border-radius:5px; overflow:hidden; position:relative; }
.cs-bar-done { height:100%; background:linear-gradient(90deg,#10B981,#059669); border-radius:5px; position:absolute; left:0; top:0; z-index:2; transition:width .5s ease; }
.cs-bar-active { height:100%; background:rgba(59,130,246,0.3); border-radius:5px; position:absolute; left:0; top:0; z-index:1; transition:width .5s ease; }
.cs-bar-legend { display:flex; gap:16px; margin-top:8px; font-size:11px; color:var(--text3); font-weight:500; flex-wrap:wrap; }
.cs-dot { display:inline-block; width:8px; height:8px; border-radius:2px; margin-right:4px; vertical-align:middle; }
.cs-dot-empty { background:var(--bg); border:1px solid var(--border); }

/* Card Grid */
.cs-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:16px; }
.cs-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:18px; box-shadow:var(--shadow-sm); transition:all .2s; }
.cs-card:hover { border-color:var(--border2); box-shadow:var(--shadow); transform:translateY(-2px); }
.cs-card-empty { opacity:0.6; }
.cs-card-empty:hover { opacity:0.8; }

/* Card Header */
.cs-card-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; gap:8px; }
.cs-card-client { display:flex; align-items:center; gap:10px; min-width:0; }
.cs-client-logo { width:32px; height:32px; border-radius:8px; object-fit:cover; border:1px solid var(--border); flex-shrink:0; }
.cs-client-emoji { font-size:22px; flex-shrink:0; }
.cs-client-avatar { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; flex-shrink:0; }
.cs-client-name { font-size:14px; font-weight:700; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px; }
.cs-client-cat { font-size:10.5px; color:var(--text3); font-weight:500; margin-top:1px; }
.cs-card-pct { font-size:22px; font-weight:800; line-height:1; flex-shrink:0; }

/* Carried badge */
.cs-carried-badge { font-size:10px; font-weight:600; background:rgba(245,158,11,.1); color:#D97706; padding:3px 8px; border-radius:5px; display:inline-flex; align-items:center; gap:4px; margin-bottom:8px; }
.cs-carried-badge i { font-size:8px; }

/* Summary */
.cs-summary { font-size:11.5px; color:var(--text3); margin-bottom:8px; display:flex; align-items:center; gap:4px; flex-wrap:wrap; }
.cs-sep { opacity:0.4; }

/* Card progress bar */
.cs-card-bar-track { height:6px; background:var(--bg); border-radius:3px; overflow:hidden; position:relative; margin-bottom:12px; }
.cs-card-bar-done { height:100%; background:linear-gradient(90deg,#10B981,#059669); border-radius:3px; position:absolute; left:0; top:0; z-index:2; transition:width .4s ease; }
.cs-card-bar-active { height:100%; background:rgba(59,130,246,0.3); border-radius:3px; position:absolute; left:0; top:0; z-index:1; transition:width .4s ease; }

/* Content type grid */
.cs-type-grid { display:flex; flex-direction:column; gap:6px; }
.cs-type-item { display:flex; align-items:center; gap:8px; padding:6px 8px; border-radius:8px; background:var(--bg); transition:background .15s; }
.cs-type-item:hover { background:var(--card2); }
.cs-type-icon { width:24px; text-align:center; font-size:12px; flex-shrink:0; }
.cs-type-info { flex:1; min-width:0; display:flex; align-items:center; gap:6px; }
.cs-type-name { font-size:11.5px; font-weight:600; color:var(--text2); min-width:60px; }
.cs-type-count { font-size:12px; color:var(--text); }
.cs-type-of { font-size:10px; color:var(--text3); font-weight:500; }
.cs-type-active { font-size:9.5px; color:var(--blue); font-weight:600; margin-left:4px; padding:1px 5px; background:var(--blue-dim); border-radius:4px; }
.cs-type-bar { width:60px; height:4px; background:var(--border); border-radius:2px; overflow:hidden; flex-shrink:0; }
.cs-type-bar-fill { height:100%; border-radius:2px; transition:width .3s ease; }

/* Empty state */
.cs-empty-state { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:24px 0; color:var(--text3); font-size:12px; gap:6px; }
.cs-empty-state i { font-size:24px; opacity:0.3; }
.cs-no-clients { grid-column:1/-1; text-align:center; padding:48px 24px; }

/* Responsive */
@media (max-width:900px) {
    .cs-stats-row { display:grid; grid-template-columns:repeat(3,1fr); }
    .cs-stat-pct { grid-column:span 3; }
    .cs-grid { grid-template-columns:1fr; }
}
@media (max-width:600px) {
    .cs-header { flex-direction:column; align-items:flex-start; }
    .cs-stats-row { grid-template-columns:repeat(2,1fr); }
    .cs-stat-pct { grid-column:span 2; }
    .cs-title { font-size:18px; }
    .cs-month-label { font-size:13px; min-width:110px; }
}
</style>
@endsection
