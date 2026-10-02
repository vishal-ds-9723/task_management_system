@extends('layouts.app')

@section('content')
<style>
.dr-wrap {
    --dr-red: #f13535;
    --dr-red-dim: rgba(241,53,53,0.08);
    padding: 4px 0 40px;
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    color: var(--text, #111827);
}
.dr-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 20px; }
.dr-head h1 { margin: 0; font-size: 26px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
.dr-head h1 i { color: var(--dr-red); }
.dr-head p { margin: 4px 0 0; font-size: 13px; color: var(--text3, #9ca3af); }
.dr-filters {
    display: flex; flex-wrap: wrap; align-items: center; gap: 6px;
    background: var(--card, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: 12px; padding: 5px;
}
.dr-filters a {
    display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px;
    padding: 0 10px; border-radius: 8px; color: var(--text, #374151); text-decoration: none;
    font-size: 12.5px; font-weight: 700;
}
.dr-filters a:hover { background: var(--dr-red-dim); color: var(--dr-red); }
.dr-filters a.is-disabled { opacity: .35; pointer-events: none; }
.dr-filters input, .dr-filters select {
    height: 34px; border: 1px solid var(--border, #e5e7eb); border-radius: 8px; padding: 0 8px;
    font: inherit; font-size: 13px; background: var(--card, #fff); color: var(--text, #111827);
}

.dr-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
.dr-stat { background: var(--card, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--radius, 16px); padding: 16px 18px; }
.dr-stat-val { font-size: 26px; font-weight: 800; line-height: 1.1; }
.dr-stat-val small { font-size: 13px; color: var(--text3, #9ca3af); }
.dr-stat-label { font-size: 11.5px; font-weight: 700; color: var(--text3, #9ca3af); text-transform: uppercase; letter-spacing: .5px; margin-top: 4px; }
.dr-stat.is-alert .dr-stat-val { color: #ea580c; }

.dr-card { background: var(--card, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--radius, 16px); overflow: hidden; margin-bottom: 20px; }
.dr-card-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 16px 20px; border-bottom: 1px solid var(--border, #e5e7eb); }
.dr-card-title { font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 8px; }
.dr-card-title i { color: var(--dr-red); }

/* ── Week grid ── */
.dr-table-scroll { overflow-x: auto; }
.dr-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 640px; }
.dr-table th, .dr-table td { padding: 10px 12px; text-align: center; border-bottom: 1px solid var(--border, #f3f4f6); }
.dr-table th { font-size: 11px; font-weight: 800; color: var(--text3, #9ca3af); text-transform: uppercase; letter-spacing: .4px; }
.dr-table th.is-selected { color: var(--dr-red); }
.dr-table td:first-child, .dr-table th:first-child { text-align: left; font-weight: 700; }
.dr-table tr:last-child td { border-bottom: none; }
.dr-cell { display: inline-block; min-width: 44px; padding: 4px 8px; border-radius: 8px; font-weight: 700; }
.dr-cell-ok { background: rgba(16,185,129,.12); color: #059669; }
.dr-cell-low { background: rgba(249,115,22,.12); color: #ea580c; }
.dr-cell-missing { color: #d1d5db; }
.dr-cell-weekend { color: #e5e7eb; }

/* ── Per-developer day cards ── */
.dr-devs { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
.dr-dev { background: var(--card, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--radius, 16px); overflow: hidden; }
.dr-dev.is-missing { border-style: dashed; }
.dr-dev-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 18px; border-bottom: 1px solid var(--border, #f3f4f6); }
.dr-dev-name { font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
.dr-avatar {
    width: 32px; height: 32px; border-radius: 50%; background: var(--dr-red-dim); color: var(--dr-red);
    display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800;
}
.dr-dev-body { padding: 4px 18px 16px; }
.dr-pill { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; padding: 3px 10px; border-radius: 99px; white-space: nowrap; }
.dr-pill-ok { background: rgba(16,185,129,.12); color: #059669; }
.dr-pill-missing { background: var(--dr-red-dim); color: var(--dr-red); }
.dr-item { padding: 11px 0; border-bottom: 1px solid var(--border, #f3f4f6); }
.dr-item:last-of-type { border-bottom: none; }
.dr-item-top { display: flex; justify-content: space-between; gap: 10px; align-items: baseline; }
.dr-item-task { font-size: 13px; font-weight: 700; }
.dr-item-meta { font-size: 11.5px; color: var(--text3, #9ca3af); margin-top: 2px; }
.dr-item-hours { font-size: 13px; font-weight: 800; white-space: nowrap; }
.dr-item-desc { font-size: 13px; margin-top: 5px; white-space: pre-line; color: var(--text2, #374151); }
.dr-note { margin-top: 10px; padding: 10px 12px; border-radius: 10px; font-size: 13px; white-space: pre-line; background: var(--card2, #f9fafb); border: 1px solid var(--border, #e5e7eb); }
.dr-note.is-blocker { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
.dr-note strong { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }
.dr-missing-msg { padding: 18px 0 4px; font-size: 13px; color: var(--text3, #9ca3af); }
.dr-empty { padding: 30px 20px; text-align: center; color: var(--text3, #9ca3af); font-size: 13px; }

@media (max-width: 1024px) {
    .dr-devs { grid-template-columns: 1fr; }
    .dr-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .dr-head h1 { font-size: 22px; }
}
</style>

@php
    $isToday = $date->isToday();
    $filterParams = request()->only('user_id');
    $missingCount = $summary['developers'] - $summary['submitted'];
@endphp

<div class="dr-wrap">

    <div class="dr-head">
        <div>
            <h1><i class="fas fa-clipboard-list"></i> Developer Daily Logs</h1>
            <p>What each developer worked on, hours spent, and what is blocking them.</p>
        </div>
        <form class="dr-filters" method="GET" action="{{ route('daily-logs.report') }}">
            <a href="{{ route('daily-logs.report', $filterParams + ['date' => $date->copy()->subDay()->toDateString()]) }}" title="Previous day"><i class="fas fa-chevron-left"></i></a>
            <input type="date" name="date" value="{{ $date->toDateString() }}" max="{{ today()->toDateString() }}" onchange="this.form.submit()">
            <a href="{{ route('daily-logs.report', $filterParams + ['date' => $date->copy()->addDay()->toDateString()]) }}" class="{{ $isToday ? 'is-disabled' : '' }}" title="Next day"><i class="fas fa-chevron-right"></i></a>
            <select name="user_id" onchange="this.form.submit()">
                <option value="">All developers</option>
                @foreach($allDevelopers as $dev)
                    <option value="{{ $dev->id }}" @selected((string) request('user_id') === (string) $dev->id)>{{ $dev->name }}</option>
                @endforeach
            </select>
            @unless($isToday)
                <a href="{{ route('daily-logs.report', $filterParams) }}">Today</a>
            @endunless
        </form>
    </div>

    <div class="dr-stats">
        <div class="dr-stat">
            <div class="dr-stat-val">{{ $summary['submitted'] }}<small> / {{ $summary['developers'] }}</small></div>
            <div class="dr-stat-label">Logs submitted</div>
        </div>
        <div class="dr-stat {{ $missingCount > 0 ? 'is-alert' : '' }}">
            <div class="dr-stat-val">{{ $missingCount }}</div>
            <div class="dr-stat-label">Missing logs</div>
        </div>
        <div class="dr-stat">
            <div class="dr-stat-val">{{ $summary['hours'] }}<small> h</small></div>
            <div class="dr-stat-label">Hours logged</div>
        </div>
        <div class="dr-stat {{ $summary['blockers'] > 0 ? 'is-alert' : '' }}">
            <div class="dr-stat-val">{{ $summary['blockers'] }}</div>
            <div class="dr-stat-label">Blockers reported</div>
        </div>
    </div>

    {{-- ── 7-day overview ── --}}
    <div class="dr-card">
        <div class="dr-card-head">
            <div class="dr-card-title"><i class="fas fa-table-cells"></i> Last 7 days · hours logged</div>
        </div>
        @if($developers->isEmpty())
            <div class="dr-empty">No developers found.</div>
        @else
            <div class="dr-table-scroll">
                <table class="dr-table">
                    <thead>
                        <tr>
                            <th>Developer</th>
                            @foreach($weekDays as $day)
                                <th class="{{ $day->isSameDay($date) ? 'is-selected' : '' }}">
                                    <a href="{{ route('daily-logs.report', $filterParams + ['date' => $day->toDateString()]) }}" style="color:inherit;text-decoration:none">
                                        {{ $day->format('D') }}<br>{{ $day->format('M d') }}
                                    </a>
                                </th>
                            @endforeach
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($developers as $dev)
                            @php $devWeek = $weekHours->get($dev->id, collect()); @endphp
                            <tr>
                                <td>{{ $dev->name }}</td>
                                @foreach($weekDays as $day)
                                    @php $hours = $devWeek->get($day->toDateString()); @endphp
                                    <td>
                                        @if($hours !== null)
                                            <span class="dr-cell {{ $hours >= 6 ? 'dr-cell-ok' : 'dr-cell-low' }}">{{ round($hours, 2) }}</span>
                                        @elseif($day->isWeekend())
                                            <span class="dr-cell dr-cell-weekend">·</span>
                                        @else
                                            <span class="dr-cell dr-cell-missing" title="No log">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td><strong>{{ round($devWeek->sum(), 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ── Selected day detail ── --}}
    <div class="dr-card-title" style="margin:0 0 12px"><i class="fas fa-calendar-day"></i> {{ $isToday ? 'Today' : $date->format('l') }} · {{ $date->format('M d, Y') }}</div>
    <div class="dr-devs">
        @foreach($developers as $dev)
            @php $log = $logs->get($dev->id); @endphp
            <div class="dr-dev {{ $log ? '' : 'is-missing' }}">
                <div class="dr-dev-head">
                    <div class="dr-dev-name">
                        <span class="dr-avatar">{{ strtoupper(substr($dev->name, 0, 1)) }}</span>
                        {{ $dev->name }}
                    </div>
                    @if($log)
                        <span class="dr-pill dr-pill-ok">{{ round($log->totalHours(), 2) }} h logged</span>
                    @else
                        <span class="dr-pill dr-pill-missing">No log</span>
                    @endif
                </div>
                <div class="dr-dev-body">
                    @if($log)
                        @foreach($log->entries as $entry)
                            <div class="dr-item">
                                <div class="dr-item-top">
                                    <div>
                                        <div class="dr-item-task">{{ $entry->task->title ?? 'Non-task work' }}</div>
                                        <div class="dr-item-meta">
                                            {{ $entry->workTypeLabel() }}
                                            @if($entry->task && $entry->task->client) · {{ $entry->task->client->name }} @endif
                                            @if($entry->progress !== null) · {{ $entry->progress }}% done @endif
                                        </div>
                                    </div>
                                    <div class="dr-item-hours">{{ $entry->hours }} h</div>
                                </div>
                                <div class="dr-item-desc">{{ $entry->description }}</div>
                            </div>
                        @endforeach
                        @if($log->blockers)
                            <div class="dr-note is-blocker"><strong><i class="fas fa-triangle-exclamation"></i> Blockers</strong>{{ $log->blockers }}</div>
                        @endif
                        @if($log->plan_next)
                            <div class="dr-note"><strong>Plan for next day</strong>{{ $log->plan_next }}</div>
                        @endif
                    @else
                        <div class="dr-missing-msg">{{ $dev->name }} has not submitted a log for this day.</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
