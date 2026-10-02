@extends('layouts.app')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   REPORT PAGE — Fully Responsive
   ═══════════════════════════════════════════════════════════════ */

/* ── Page wrapper ── */
.rpt-wrap {
    padding: 20px 24px 40px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    min-height: calc(100vh - 100px);
    overflow-x: hidden; /* Prevent horizontal scroll on the main wrap */
}

/* Keep direct children from overflowing chart/table containers */
.rpt-wrap > * {
    min-width: 0;
}

/* ── Filter Bar ── */
.rpt-filter-bar {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 14px 16px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Period buttons row — scrollable on small screens */
.rpt-period-row {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 2px;
}
.rpt-period-row::-webkit-scrollbar { display: none; }

.rpt-filter-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    flex-shrink: 0;
}

.rpt-range-btn {
    padding: 6px 14px;
    border-radius: 8px;
    border: 1.5px solid var(--border);
    background: var(--card2);
    color: var(--text2);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
    flex-shrink: 0;
}
.rpt-range-btn:hover  { border-color: var(--primary); color: var(--primary); background: var(--primary-dim); }
.rpt-range-btn.active { background: var(--primary); border-color: var(--primary); color: #fff; }

/* Dropdowns + export row */
.rpt-controls-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.rpt-filter-select {
    flex: 1;
    min-width: 130px;
    padding: 8px 12px;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    background: var(--card2);
    color: var(--text);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    outline: none;
    transition: border-color 0.15s;
}
.rpt-filter-select:focus { border-color: var(--primary); }

.rpt-export-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 8px 16px;
    border-radius: 9px;
    border: 1.5px solid var(--border);
    background: var(--card);
    color: var(--text2);
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s;
    white-space: nowrap;
}
.rpt-export-btn:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-dim); }

/* ── KPI Grid ── */
.rpt-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.rpt-kpi {
    background: var(--card);
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    padding: 16px 14px;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.rpt-kpi:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.09); }
.rpt-kpi::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--kpi-color, var(--primary));
}
.rpt-kpi-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
    margin-bottom: 10px;
    background: var(--kpi-dim, var(--primary-dim));
    color: var(--kpi-color, var(--primary));
}
.rpt-kpi-val {
    font-size: 26px;
    font-weight: 800;
    color: var(--text);
    line-height: 1;
    letter-spacing: -0.5px;
}
.rpt-kpi-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--text3);
    margin-top: 5px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.rpt-kpi-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 700;
    margin-top: 5px;
}
.rpt-kpi-trend.up      { color: #10B981; }
.rpt-kpi-trend.down    { color: #EF4444; }
.rpt-kpi-trend.neutral { color: var(--text3); }
.rpt-kpi-trend .sub    { font-weight: 500; color: var(--text3); font-size: 10px; }

/* ── Insight Cards ── */
.rpt-insight-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}
.rpt-insight {
    background: var(--card);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 13px 14px;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.rpt-insight-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.rpt-insight-text { font-size: 12px; color: var(--text2); line-height: 1.5; min-width: 0; }
.rpt-insight-text strong { color: var(--text); display: block; margin-bottom: 2px; font-size: 12.5px; }

/* ── Chart Cards ── */
.rpt-chart-card {
    background: var(--card);
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    padding: 20px 18px;
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.2s;
    margin-bottom: 16px;
    width: 100%;
    min-width: 0;
    overflow: hidden;
}
.rpt-chart-card:hover { box-shadow: 0 6px 28px rgba(0,0,0,0.07); }

.rpt-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
    gap: 10px;
    flex-wrap: wrap;
}
.rpt-section-meta { font-size: 11.5px; color: var(--text3); }
.rpt-section-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 8px;
}
.rpt-section-title i { color: var(--primary); }

/* Chart grid layouts */
.rpt-grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 320px), 1fr));
    gap: 16px;
    margin-bottom: 20px;
    width: 100%;
}

/* Prevent grid children/charts from forcing horizontal overflow */
.rpt-grid-2 > *,
.rpt-kpi-grid > *,
.rpt-insight-grid > * {
    min-width: 0;
}

#trendChart,
#statusChart,
#platformChart,
#workloadChart,
#contentTypeChart {
    min-width: 0;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
}

.rpt-chart-card .apexcharts-canvas,
.rpt-chart-card .apexcharts-svg {
    max-width: 100% !important;
}

/* ── Progress Bar ── */
.rpt-prog-bar {
    height: 6px;
    border-radius: 99px;
    background: var(--border);
    overflow: hidden;
    margin-top: 5px;
}
.rpt-prog-fill {
    height: 100%;
    border-radius: 99px;
    transition: width 0.8s cubic-bezier(0.4,0,0.2,1);
}

/* ── Data Tables — desktop ── */
.rpt-table-wrap { overflow-x: auto; border-radius: 10px; }
.rpt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.rpt-table thead th {
    padding: 10px 14px;
    text-align: left;
    font-size: 11px;
    font-weight: 700;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: var(--card2);
    border-bottom: 2px solid var(--border);
    white-space: nowrap;
}
.rpt-table tbody tr { border-bottom: 1px solid var(--border); transition: background 0.1s; }
.rpt-table tbody tr:last-child { border-bottom: none; }
.rpt-table tbody tr:hover { background: var(--card2); }
.rpt-table tbody td { padding: 11px 14px; color: var(--text); vertical-align: middle; }
.rpt-table .num   { font-weight: 700; }
.rpt-table .muted { color: var(--text3); font-size: 12px; }

/* ── Mobile Cards — shown instead of tables on small screens ── */
.rpt-mobile-cards { display: none; }

.rpt-mc {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 10px;
    background: var(--card);
}
.rpt-mc:last-child { margin-bottom: 0; }
.rpt-mc-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    gap: 10px;
}
.rpt-mc-identity {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.rpt-mc-name { font-weight: 700; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rpt-mc-role { font-size: 11px; color: var(--text3); text-transform: capitalize; margin-top: 1px; }
.rpt-mc-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-bottom: 10px;
}
.rpt-mc-stat {
    text-align: center;
    padding: 8px 4px;
    background: var(--card2);
    border-radius: 8px;
}
.rpt-mc-stat-val  { font-size: 18px; font-weight: 800; color: var(--text); line-height: 1; }
.rpt-mc-stat-lbl  { font-size: 9.5px; color: var(--text3); text-transform: uppercase; letter-spacing: 0.3px; margin-top: 3px; font-weight: 600; }
.rpt-mc-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
}
.rpt-mc-rate-wrap { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 120px; }

/* ── Rate Pill ── */
.rate-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.rate-pill.high { background: rgba(16,185,129,0.12); color: #059669; }
.rate-pill.mid  { background: rgba(245,158,11,0.12);  color: #B45309; }
.rate-pill.low  { background: rgba(239,68,68,0.12);   color: #DC2626; }

/* ── ApexCharts overrides ── */
.apexcharts-tooltip {
    background: var(--card) !important;
    border: 1px solid var(--border) !important;
    box-shadow: 0 8px 28px rgba(0,0,0,0.12) !important;
    color: var(--text) !important;
    border-radius: 10px !important;
}
.apexcharts-tooltip-title {
    background: var(--card2) !important;
    border-bottom: 1px solid var(--border) !important;
    font-family: 'Inter', sans-serif !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    color: var(--text) !important;
}
.apexcharts-text tspan    { font-family: 'Inter', sans-serif !important; }
.apexcharts-gridline      { stroke: var(--border) !important; }
.apexcharts-legend-text   { color: var(--text2) !important; font-size: 12px !important; }
.apexcharts-xaxis-label,
.apexcharts-yaxis-label   { fill: var(--text3) !important; }

/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE BREAKPOINTS
   ═══════════════════════════════════════════════════════════════ */

/* ── Tablet: 1024px ── */
@media (max-width: 1024px) {
    .rpt-wrap { padding: 16px 20px 32px; }
    .rpt-grid-2 { grid-template-columns: 1fr; }
}

/* ── Small tablet / large phone: 768px ── */
@media (max-width: 768px) {
    .rpt-wrap { padding: 12px 14px 24px; }
    .rpt-kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .rpt-insight-grid { grid-template-columns: 1fr; gap: 10px; }

    /* Switch tables to mobile card layout */
    .rpt-table-wrap  { display: none; }
    .rpt-mobile-cards { display: block; }

    .rpt-mc-stats { grid-template-columns: repeat(4, 1fr); }
}

/* ── Phone: 480px ── */
@media (max-width: 480px) {
    .rpt-wrap { padding-left: 10px; padding-right: 10px; }

    .rpt-kpi-grid     { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .rpt-insight-grid { grid-template-columns: 1fr; }

    .rpt-kpi { padding: 12px 10px; }
    .rpt-kpi-val   { font-size: 20px; }
    .rpt-kpi-label { font-size: 10px; }
    .rpt-kpi-trend { font-size: 10px; }
    .rpt-kpi-icon  { width: 28px; height: 28px; font-size: 12px; margin-bottom: 6px; }

    .rpt-chart-card { padding: 14px 12px; }
    .rpt-section-title { font-size: 13px; }
    .rpt-section-meta  { display: none; }

    .rpt-mc-stats { grid-template-columns: repeat(2, 1fr); }

    .rpt-filter-select { min-width: 0; font-size: 12px; padding: 7px 10px; }
    .rpt-export-btn    { width: 100%; }
    .rpt-controls-row  { gap: 6px; }
}

/* ── Very small: 360px ── */
@media (max-width: 360px) {
    .rpt-wrap { padding-left: 8px; padding-right: 8px; }

    .rpt-kpi-val { font-size: 18px; }
    .rpt-kpi-grid { gap: 6px; }
}
</style>
@endpush

@section('content')
<div class="rpt-wrap">

{{-- ── Page Header ── --}}
<div class="topbar" style="margin-bottom:16px">
    <div>
        <div class="page-title"><i class="fa-solid fa-chart-pie" style="color:var(--primary);margin-right:8px"></i>Analytics & Reports</div>
        <div class="page-subtitle">{{ $rangeStart->format('d M Y') }} — {{ $rangeEnd->format('d M Y') }} &nbsp;·&nbsp; Interactive performance metrics</div>
    </div>
</div>

{{-- ── Filter Bar ── --}}
<form method="GET" action="{{ route('admin.report') }}" id="rptFilterForm">
<div class="rpt-filter-bar">

    {{-- Period scroll row --}}
    <div class="rpt-period-row">
        <span class="rpt-filter-label"><i class="fas fa-calendar-alt" style="margin-right:3px"></i> Period</span>
        @foreach(['7'=>'7 Days','14'=>'14 Days','30'=>'30 Days','month'=>'This Month','lastmonth'=>'Last Month','90'=>'90 Days'] as $val => $lbl)
            <button type="submit" name="range" value="{{ $val }}"
                class="rpt-range-btn {{ $range === $val ? 'active' : '' }}">{{ $lbl }}</button>
        @endforeach
    </div>

    {{-- Dropdowns + export row --}}
    <div class="rpt-controls-row">
        <select name="client_id" class="rpt-filter-select" onchange="document.getElementById('rptFilterForm').submit()">
            <option value="">All Clients</option>
            @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ $clientId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>

        <select name="designer_id" class="rpt-filter-select" onchange="document.getElementById('rptFilterForm').submit()">
            <option value="">All Designers</option>
            @foreach($designers as $d)
                <option value="{{ $d->id }}" {{ $designer == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
            @endforeach
        </select>

        <a href="{{ route('admin.tasks.export-csv', request()->query()) }}" class="rpt-export-btn">
            <i class="fas fa-download"></i> <span>Export CSV</span>
        </a>
    </div>
</div>
</form>

{{-- ── KPI Cards ── --}}
<div class="rpt-kpi-grid">
    {{-- Completed --}}
    <div class="rpt-kpi" style="--kpi-color:#10B981;--kpi-dim:rgba(16,185,129,0.1)">
        <div class="rpt-kpi-icon"><i class="fas fa-check-double"></i></div>
        <div class="rpt-kpi-val">{{ number_format($totalDelivered) }}</div>
        <div class="rpt-kpi-label">Completed</div>
        <div class="rpt-kpi-trend {{ $deliveredTrend > 0 ? 'up' : ($deliveredTrend < 0 ? 'down' : 'neutral') }}">
            {{ $deliveredTrend > 0 ? '↑' : ($deliveredTrend < 0 ? '↓' : '→') }} {{ abs($deliveredTrend) }}%
            <span class="sub">vs prev</span>
        </div>
    </div>

    {{-- Completion Rate --}}
    <div class="rpt-kpi" style="--kpi-color:#3B82F6;--kpi-dim:rgba(59,130,246,0.1)">
        <div class="rpt-kpi-icon"><i class="fas fa-percent"></i></div>
        <div class="rpt-kpi-val">{{ $completionRate }}<span style="font-size:13px;opacity:0.6">%</span></div>
        <div class="rpt-kpi-label">Completion Rate</div>
        <div class="rpt-kpi-trend {{ $completionRate >= 70 ? 'up' : ($completionRate >= 40 ? 'neutral' : 'down') }}">
            <span class="sub">{{ $completionRate >= 70 ? 'Great' : ($completionRate >= 40 ? 'Average' : 'Needs work') }}</span>
        </div>
    </div>

    {{-- On-Time Rate --}}
    <div class="rpt-kpi" style="--kpi-color:#8B5CF6;--kpi-dim:rgba(139,92,246,0.1)">
        <div class="rpt-kpi-icon"><i class="fas fa-clock"></i></div>
        <div class="rpt-kpi-val">{{ $onTimeRate }}<span style="font-size:13px;opacity:0.6">%</span></div>
        <div class="rpt-kpi-label">On-Time</div>
        <div class="rpt-kpi-trend {{ $onTimeRate >= 80 ? 'up' : ($onTimeRate >= 50 ? 'neutral' : 'down') }}">
            <span class="sub">{{ $onTimeRate >= 80 ? 'Excellent' : ($onTimeRate >= 50 ? 'Moderate' : 'Below target') }}</span>
        </div>
    </div>

    {{-- Avg Revisions --}}
    <div class="rpt-kpi" style="--kpi-color:#F59E0B;--kpi-dim:rgba(245,158,11,0.1)">
        <div class="rpt-kpi-icon"><i class="fas fa-redo"></i></div>
        <div class="rpt-kpi-val">{{ $avgRevisions }}</div>
        <div class="rpt-kpi-label">Avg. Revisions</div>
        <div class="rpt-kpi-trend {{ $avgRevisions <= 1 ? 'up' : ($avgRevisions <= 2 ? 'neutral' : 'down') }}">
            <span class="sub">per task</span>
        </div>
    </div>

    {{-- Overdue --}}
    <div class="rpt-kpi" style="--kpi-color:{{ $overdue > 0 ? '#EF4444' : '#10B981' }};--kpi-dim:{{ $overdue > 0 ? 'rgba(239,68,68,0.1)' : 'rgba(16,185,129,0.1)' }}">
        <div class="rpt-kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="rpt-kpi-val" style="color:{{ $overdue > 0 ? '#EF4444' : 'var(--text)' }}">{{ $overdue }}</div>
        <div class="rpt-kpi-label">Overdue</div>
        <div class="rpt-kpi-trend {{ $overdue > 0 ? 'down' : 'up' }}">
            <span class="sub">{{ $overdue > 0 ? 'Need attention' : 'All clear' }}</span>
        </div>
    </div>

    {{-- In Review --}}
    <div class="rpt-kpi" style="--kpi-color:#A855F7;--kpi-dim:rgba(168,85,247,0.1)">
        <div class="rpt-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
        <div class="rpt-kpi-val">{{ $reviewCount }}</div>
        <div class="rpt-kpi-label">In Review</div>
        <div class="rpt-kpi-trend neutral"><span class="sub">Awaiting approval</span></div>
    </div>

    {{-- Published --}}
    <div class="rpt-kpi" style="--kpi-color:#06B6D4;--kpi-dim:rgba(6,182,212,0.1)">
        <div class="rpt-kpi-icon"><i class="fas fa-paper-plane"></i></div>
        <div class="rpt-kpi-val">{{ $publishedCount }}</div>
        <div class="rpt-kpi-label">Published</div>
        <div class="rpt-kpi-trend up"><span class="sub">Live content</span></div>
    </div>
</div>

{{-- ── Quick Insights ── --}}
<div class="rpt-insight-grid">
    <div class="rpt-insight">
        <div class="rpt-insight-icon" style="background:rgba(16,185,129,0.1);color:#10B981"><i class="fas fa-trophy"></i></div>
        <div class="rpt-insight-text">
            <strong>Period summary</strong>
            {{ $completionRate }}% rate · {{ $totalDelivered }} delivered
        </div>
    </div>
    <div class="rpt-insight">
        <div class="rpt-insight-icon" style="background:rgba(245,158,11,0.1);color:#D97706"><i class="fas fa-film"></i></div>
        <div class="rpt-insight-text">
            <strong>Content split</strong>
            {{ $totalReels }} Reels · {{ $totalPosts }} Posts
        </div>
    </div>
    <div class="rpt-insight">
        <div class="rpt-insight-icon" style="background:rgba(139,92,246,0.1);color:#8B5CF6"><i class="fas fa-user-check"></i></div>
        <div class="rpt-insight-text">
            <strong>Team delivery</strong>
            {{ $onTimeRate }}% on-time · {{ $avgRevisions }} avg revisions
        </div>
    </div>
    <div class="rpt-insight">
        <div class="rpt-insight-icon" style="background:{{ $overdue > 0 ? 'rgba(239,68,68,0.1)' : 'rgba(16,185,129,0.1)' }};color:{{ $overdue > 0 ? '#DC2626' : '#10B981' }}">
            <i class="fas fa-{{ $overdue > 0 ? 'fire' : 'check-circle' }}"></i>
        </div>
        <div class="rpt-insight-text">
            <strong>{{ $overdue > 0 ? 'Action needed' : 'All clear' }}</strong>
            {{ $overdue > 0 ? $overdue . ' task' . ($overdue > 1 ? 's' : '') . ' past deadline' : 'No overdue tasks' }}
        </div>
    </div>
</div>

{{-- ── Trend Chart (full width) ── --}}
<div class="rpt-chart-card">
    <div class="rpt-section-header">
        <div class="rpt-section-title"><i class="fas fa-chart-line"></i> Task Activity Trend</div>
        <div class="rpt-section-meta">{{ count($taskTrends['categories']) }} data points · {{ $rangeStart->format('d M') }}–{{ $rangeEnd->format('d M Y') }}</div>
    </div>
    <div id="trendChart"></div>
</div>

{{-- ── Status + Platform Donuts ── --}}
<div class="rpt-grid-2">
    <div class="rpt-chart-card">
        <div class="rpt-section-title" style="margin-bottom:14px"><i class="fas fa-chart-pie"></i> Status Breakdown</div>
        <div id="statusChart"></div>
    </div>
    <div class="rpt-chart-card">
        <div class="rpt-section-title" style="margin-bottom:14px"><i class="fab fa-instagram"></i> Platform Distribution</div>
        <div id="platformChart"></div>
    </div>
</div>

{{-- ── Client Workload + Content Mix ── --}}
<div class="rpt-grid-2">
    <div class="rpt-chart-card">
        <div class="rpt-section-title" style="margin-bottom:14px"><i class="fas fa-chart-bar"></i> Top Clients by Workload</div>
        <div id="workloadChart"></div>
    </div>
    <div class="rpt-chart-card">
        <div class="rpt-section-title" style="margin-bottom:14px"><i class="fas fa-shapes"></i> Content Type Mix</div>
        <div id="contentTypeChart"></div>
    </div>
</div>

{{-- ── Designer Performance ── --}}
<div class="rpt-chart-card">
    <div class="rpt-section-header">
        <div class="rpt-section-title"><i class="fas fa-users"></i> Designer Performance</div>
        <div class="rpt-section-meta">{{ $designerPerformance->count() }} team members</div>
    </div>

    @if($designerPerformance->isEmpty())
        <div style="padding:32px;text-align:center;color:var(--text3)">
            <i class="fas fa-users" style="font-size:28px;opacity:0.3;display:block;margin-bottom:10px"></i>
            No designer data available
        </div>
    @else

    {{-- Desktop table --}}
    <div class="rpt-table-wrap">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Designer</th>
                    <th>Total</th>
                    <th>Done</th>
                    <th>In Progress</th>
                    <th>Review</th>
                    <th>Overdue</th>
                    <th>Rate</th>
                    <th>Avg Rev.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($designerPerformance as $d)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px">
                            <div style="width:30px;height:30px;border-radius:50%;background:{{ $d['avatar_color'] ?? '#6B7280' }};color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $d['initial'] }}</div>
                            <div>
                                <div style="font-weight:600;font-size:13px;white-space:nowrap">{{ $d['name'] }}</div>
                                <div class="muted" style="text-transform:capitalize">{{ $d['role'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="num">{{ $d['total'] }}</td>
                    <td><span style="color:#10B981;font-weight:700">{{ $d['completed'] }}</span></td>
                    <td><span style="color:#3B82F6;font-weight:600">{{ $d['inprogress'] }}</span></td>
                    <td><span style="color:#8B5CF6;font-weight:600">{{ $d['review'] }}</span></td>
                    <td>
                        @if($d['overdue'] > 0)
                            <span style="color:#EF4444;font-weight:700">{{ $d['overdue'] }} <i class="fas fa-exclamation-circle" style="font-size:10px"></i></span>
                        @else<span class="muted">—</span>@endif
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:7px;min-width:110px">
                            <span class="rate-pill {{ $d['completion_rate'] >= 70 ? 'high' : ($d['completion_rate'] >= 40 ? 'mid' : 'low') }}">{{ $d['completion_rate'] }}%</span>
                            <div style="flex:1">
                                <div class="rpt-prog-bar">
                                    <div class="rpt-prog-fill" style="width:{{ $d['completion_rate'] }}%;background:{{ $d['completion_rate'] >= 70 ? '#10B981' : ($d['completion_rate'] >= 40 ? '#F59E0B' : '#EF4444') }}"></div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:700;color:{{ $d['avg_revisions'] <= 1 ? '#10B981' : ($d['avg_revisions'] <= 2 ? '#F59E0B' : '#EF4444') }}">{{ $d['avg_revisions'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="rpt-mobile-cards">
        @foreach($designerPerformance as $d)
        <div class="rpt-mc">
            <div class="rpt-mc-head">
                <div class="rpt-mc-identity">
                    <div style="width:36px;height:36px;border-radius:50%;background:{{ $d['avatar_color'] ?? '#6B7280' }};color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $d['initial'] }}</div>
                    <div style="min-width:0">
                        <div class="rpt-mc-name">{{ $d['name'] }}</div>
                        <div class="rpt-mc-role">{{ $d['role'] }}</div>
                    </div>
                </div>
                <span class="rate-pill {{ $d['completion_rate'] >= 70 ? 'high' : ($d['completion_rate'] >= 40 ? 'mid' : 'low') }}">{{ $d['completion_rate'] }}%</span>
            </div>
            <div class="rpt-mc-stats">
                <div class="rpt-mc-stat">
                    <div class="rpt-mc-stat-val">{{ $d['total'] }}</div>
                    <div class="rpt-mc-stat-lbl">Total</div>
                </div>
                <div class="rpt-mc-stat">
                    <div class="rpt-mc-stat-val" style="color:#10B981">{{ $d['completed'] }}</div>
                    <div class="rpt-mc-stat-lbl">Done</div>
                </div>
                <div class="rpt-mc-stat">
                    <div class="rpt-mc-stat-val" style="color:{{ $d['overdue'] > 0 ? '#EF4444' : 'var(--text)' }}">{{ $d['overdue'] }}</div>
                    <div class="rpt-mc-stat-lbl">Overdue</div>
                </div>
                <div class="rpt-mc-stat">
                    <div class="rpt-mc-stat-val" style="color:{{ $d['avg_revisions'] <= 1 ? '#10B981' : ($d['avg_revisions'] <= 2 ? '#F59E0B' : '#EF4444') }}">{{ $d['avg_revisions'] }}</div>
                    <div class="rpt-mc-stat-lbl">Avg Rev.</div>
                </div>
            </div>
            <div class="rpt-prog-bar">
                <div class="rpt-prog-fill" style="width:{{ $d['completion_rate'] }}%;background:{{ $d['completion_rate'] >= 70 ? '#10B981' : ($d['completion_rate'] >= 40 ? '#F59E0B' : '#EF4444') }}"></div>
            </div>
        </div>
        @endforeach
    </div>

    @endif
</div>

{{-- ── Client Performance ── --}}
<div class="rpt-chart-card">
    <div class="rpt-section-header">
        <div class="rpt-section-title"><i class="fas fa-building"></i> Client Performance</div>
        <div class="rpt-section-meta">{{ $clientPerformance->count() }} active clients</div>
    </div>

    @if($clientPerformance->isEmpty())
        <div style="padding:32px;text-align:center;color:var(--text3)">
            <i class="fas fa-building" style="font-size:28px;opacity:0.3;display:block;margin-bottom:10px"></i>
            No client data available
        </div>
    @else

    {{-- Desktop table --}}
    <div class="rpt-table-wrap">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Total</th>
                    <th>Done</th>
                    <th>Review</th>
                    <th>Overdue</th>
                    <th>Rate</th>
                    <th>Health</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clientPerformance as $c)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:9px">
                            <div style="width:30px;height:30px;border-radius:8px;background:{{ $c['color'] ?? '#6B7280' }}22;color:{{ $c['color'] ?? '#6B7280' }};display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0">{{ $c['emoji'] ?? '🏢' }}</div>
                            <span style="font-weight:600;font-size:13px;white-space:nowrap">{{ $c['name'] }}</span>
                        </div>
                    </td>
                    <td class="num">{{ $c['total'] }}</td>
                    <td><span style="color:#10B981;font-weight:700">{{ $c['completed'] }}</span></td>
                    <td><span style="color:#8B5CF6;font-weight:600">{{ $c['review'] }}</span></td>
                    <td>
                        @if($c['overdue'] > 0)
                            <span style="color:#EF4444;font-weight:700">{{ $c['overdue'] }} <i class="fas fa-exclamation-circle" style="font-size:10px"></i></span>
                        @else<span class="muted">—</span>@endif
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:7px;min-width:110px">
                            <span class="rate-pill {{ $c['completion_rate'] >= 70 ? 'high' : ($c['completion_rate'] >= 40 ? 'mid' : 'low') }}">{{ $c['completion_rate'] }}%</span>
                            <div style="flex:1">
                                <div class="rpt-prog-bar">
                                    <div class="rpt-prog-fill" style="width:{{ $c['completion_rate'] }}%;background:{{ $c['completion_rate'] >= 70 ? '#10B981' : ($c['completion_rate'] >= 40 ? '#F59E0B' : '#EF4444') }}"></div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($c['overdue'] > 0)
                            <span class="rate-pill low"><i class="fas fa-exclamation-triangle" style="font-size:9px"></i> At Risk</span>
                        @elseif($c['completion_rate'] >= 70)
                            <span class="rate-pill high"><i class="fas fa-check" style="font-size:9px"></i> Healthy</span>
                        @else
                            <span class="rate-pill mid"><i class="fas fa-minus" style="font-size:9px"></i> Average</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="rpt-mobile-cards">
        @foreach($clientPerformance as $c)
        <div class="rpt-mc">
            <div class="rpt-mc-head">
                <div class="rpt-mc-identity">
                    <div style="width:36px;height:36px;border-radius:10px;background:{{ $c['color'] ?? '#6B7280' }}22;color:{{ $c['color'] ?? '#6B7280' }};display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">{{ $c['emoji'] ?? '🏢' }}</div>
                    <div class="rpt-mc-name">{{ $c['name'] }}</div>
                </div>
                @if($c['overdue'] > 0)
                    <span class="rate-pill low"><i class="fas fa-exclamation-triangle" style="font-size:9px"></i> At Risk</span>
                @elseif($c['completion_rate'] >= 70)
                    <span class="rate-pill high"><i class="fas fa-check" style="font-size:9px"></i> Healthy</span>
                @else
                    <span class="rate-pill mid"><i class="fas fa-minus" style="font-size:9px"></i> Average</span>
                @endif
            </div>
            <div class="rpt-mc-stats">
                <div class="rpt-mc-stat">
                    <div class="rpt-mc-stat-val">{{ $c['total'] }}</div>
                    <div class="rpt-mc-stat-lbl">Total</div>
                </div>
                <div class="rpt-mc-stat">
                    <div class="rpt-mc-stat-val" style="color:#10B981">{{ $c['completed'] }}</div>
                    <div class="rpt-mc-stat-lbl">Done</div>
                </div>
                <div class="rpt-mc-stat">
                    <div class="rpt-mc-stat-val" style="color:#8B5CF6">{{ $c['review'] }}</div>
                    <div class="rpt-mc-stat-lbl">Review</div>
                </div>
                <div class="rpt-mc-stat">
                    <div class="rpt-mc-stat-val" style="color:{{ $c['overdue'] > 0 ? '#EF4444' : 'var(--text)' }}">{{ $c['overdue'] }}</div>
                    <div class="rpt-mc-stat-lbl">Overdue</div>
                </div>
            </div>
            <div style="margin-top:8px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                    <span style="font-size:11px;color:var(--text3);font-weight:600">Completion Rate</span>
                    <span style="font-size:12px;font-weight:700;color:{{ $c['completion_rate'] >= 70 ? '#10B981' : ($c['completion_rate'] >= 40 ? '#F59E0B' : '#EF4444') }}">{{ $c['completion_rate'] }}%</span>
                </div>
                <div class="rpt-prog-bar">
                    <div class="rpt-prog-fill" style="width:{{ $c['completion_rate'] }}%;background:{{ $c['completion_rate'] >= 70 ? '#10B981' : ($c['completion_rate'] >= 40 ? '#F59E0B' : '#EF4444') }}"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @endif
</div>

</div>{{-- /.rpt-wrap --}}

{{-- ── ApexCharts ── --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isMobile  = window.innerWidth < 600;
    const isTablet  = window.innerWidth < 900;
    const isDark    = document.body.classList.contains('dark-mode');
    const textCol   = getComputedStyle(document.documentElement).getPropertyValue('--text2').trim()  || '#64748B';
    const borderC   = getComputedStyle(document.documentElement).getPropertyValue('--border').trim()  || '#E2E6EF';
    const cardBg    = getComputedStyle(document.documentElement).getPropertyValue('--card').trim()    || '#FFFFFF';
    const teal = '#10B981', blue = '#3B82F6';

    const baseChart = {
        fontFamily: "'Inter', system-ui, sans-serif",
        toolbar: { show: false },
        zoom: { enabled: false },
        background: 'transparent',
        redrawOnParentResize: true,
        redrawOnWindowResize: true,
        parentHeightOffset: 0,
    };
    const baseGrid = { borderColor: borderC, strokeDashArray: 4, xaxis: { lines: { show: false } } };
    const baseXaxis = { labels: { style: { colors: textCol, fontSize: '11px', fontFamily: 'inherit' } }, axisBorder: { show: false }, axisTicks: { show: false } };
    const baseYaxis = { labels: { style: { colors: textCol, fontSize: '11px', fontFamily: 'inherit' }, formatter: v => Math.floor(v) } };

    // ── 1. Trend Area ────────────────────────────────────────────────
    new ApexCharts(document.querySelector('#trendChart'), {
        series: [
            { name: 'Completed', data: @json($taskTrends['completed']) },
            { name: 'Created',   data: @json($taskTrends['created']) },
        ],
        chart: { ...baseChart, height: isMobile ? 200 : 260, type: 'area' },
        colors: [teal, blue],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: isMobile ? [2, 1.5] : [3, 2] },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 90, 100] } },
        xaxis: {
            ...baseXaxis,
            categories: @json($taskTrends['categories']),
            tooltip: { enabled: false },
            tickAmount: isMobile ? 5 : undefined,
        },
        yaxis: { ...baseYaxis },
        grid: { ...baseGrid },
        legend: {
            position: 'top', horizontalAlign: 'right',
            labels: { colors: textCol },
            markers: { radius: 4, width: 10, height: 10 },
            fontSize: '12px',
        },
        tooltip: { theme: isDark ? 'dark' : 'light', x: { show: true } },
        markers: { size: 0, hover: { size: 4 } },
        responsive: [{ breakpoint: 600, options: { chart: { height: 200 }, legend: { position: 'bottom', horizontalAlign: 'center' } } }],
    }).render();

    // ── 2. Status Donut ──────────────────────────────────────────────
    const statusSeries = @json($statusBreakdown['series']);
    const statusLabels = @json($statusBreakdown['labels']);
    const sFiltered = statusLabels.map((l, i) => ({ l, v: statusSeries[i] })).filter(x => x.v > 0);

    new ApexCharts(document.querySelector('#statusChart'), {
        series: sFiltered.map(x => x.v),
        labels: sFiltered.map(x => x.l),
        chart: { ...baseChart, type: 'donut', height: isMobile ? 260 : 300 },
        colors: ['#94A3B8', '#3B82F6', '#F59E0B', '#A855F7', '#10B981', '#06B6D4'],
        stroke: { show: true, colors: [cardBg], width: 3 },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        name:  { show: true, fontSize: '12px', color: textCol, fontFamily: 'inherit' },
                        value: { show: true, fontSize: '20px', fontWeight: 800, color: 'var(--text)', fontFamily: 'inherit', formatter: v => v },
                        total: { show: true, showAlways: true, label: 'Total', fontSize: '11px', color: textCol, fontFamily: 'inherit', formatter: w => w.globals.seriesTotals.reduce((a,b) => a+b, 0) },
                    },
                },
            },
        },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', labels: { colors: textCol }, fontFamily: 'inherit', fontSize: '12px' },
        tooltip: { theme: isDark ? 'dark' : 'light' },
        responsive: [{ breakpoint: 480, options: { chart: { height: 240 }, legend: { fontSize: '11px' } } }],
    }).render();

    // ── 3. Platform Donut ────────────────────────────────────────────
    const platSeries = @json($platformBreakdown['series']);
    const platLabels = @json($platformBreakdown['labels']);
    const platColors = @json($platformBreakdown['colors']);
    const pFiltered = platLabels.map((l, i) => ({ l, v: platSeries[i], c: platColors[i] })).filter(x => x.v > 0);

    if (pFiltered.length > 0) {
        new ApexCharts(document.querySelector('#platformChart'), {
            series: pFiltered.map(x => x.v),
            labels: pFiltered.map(x => x.l),
            chart: { ...baseChart, type: 'donut', height: isMobile ? 260 : 300 },
            colors: pFiltered.map(x => x.c),
            stroke: { show: true, colors: [cardBg], width: 3 },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            name:  { show: true, fontSize: '12px', color: textCol, fontFamily: 'inherit' },
                            value: { show: true, fontSize: '20px', fontWeight: 800, color: 'var(--text)', fontFamily: 'inherit' },
                            total: { show: true, showAlways: true, label: 'Total', fontSize: '11px', color: textCol, fontFamily: 'inherit', formatter: w => w.globals.seriesTotals.reduce((a,b) => a+b, 0) },
                        },
                    },
                },
            },
            dataLabels: { enabled: false },
            legend: { position: 'bottom', labels: { colors: textCol }, fontFamily: 'inherit', fontSize: '12px' },
            tooltip: { theme: isDark ? 'dark' : 'light' },
            responsive: [{ breakpoint: 480, options: { chart: { height: 240 }, legend: { fontSize: '11px' } } }],
        }).render();
    } else {
        document.querySelector('#platformChart').innerHTML =
            '<div style="padding:80px 20px;text-align:center;color:var(--text3);font-size:13px">' +
            '<i class="fas fa-hashtag" style="font-size:28px;opacity:0.3;display:block;margin-bottom:10px"></i>No platform data yet</div>';
    }

    // ── 4. Workload Grouped Bar ──────────────────────────────────────
    new ApexCharts(document.querySelector('#workloadChart'), {
        series: [
            { name: 'Active',    data: @json($workloadData['active']) },
            { name: 'Completed', data: @json($workloadData['completed']) },
        ],
        chart: { ...baseChart, height: isMobile ? 240 : 300, type: 'bar' },
        colors: [blue, teal],
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 4,
                barHeight: isMobile ? '50%' : '60%',
                dataLabels: { position: 'top' },
            },
        },
        dataLabels: {
            enabled: !isMobile,
            offsetX: 8,
            style: { fontSize: '11px', colors: [textCol], fontFamily: 'inherit', fontWeight: 600 },
        },
        xaxis: { ...baseXaxis, categories: @json($workloadData['labels']), labels: { formatter: v => Math.floor(v) } },
        yaxis: { labels: { style: { colors: textCol, fontSize: '11px', fontFamily: 'inherit' }, maxWidth: isMobile ? 80 : 120 } },
        grid: { ...baseGrid },
        legend: { position: 'top', horizontalAlign: 'right', labels: { colors: textCol }, fontFamily: 'inherit', fontSize: '12px' },
        tooltip: { theme: isDark ? 'dark' : 'light' },
        responsive: [{ breakpoint: 480, options: { chart: { height: 220 }, yaxis: { labels: { maxWidth: 70, style: { fontSize: '10px' } } } } }],
    }).render();

    // ── 5. Content Type Radial ───────────────────────────────────────
    const ctSeries = @json($contentTypes['series']);
    const ctLabels = @json($contentTypes['labels']);
    const ctColors = @json($contentTypes['colors']);
    const ctTotal  = ctSeries.reduce((a,b) => a+b, 0) || 1;
    const ctPct    = ctSeries.map(v => Math.round((v / ctTotal) * 100));

    new ApexCharts(document.querySelector('#contentTypeChart'), {
        series: ctPct,
        labels: ctLabels.map((l, i) => `${l} (${ctSeries[i]})`),
        chart: { ...baseChart, height: isMobile ? 260 : 300, type: 'radialBar' },
        colors: ctColors,
        plotOptions: {
            radialBar: {
                startAngle: 0,
                endAngle: 270,
                hollow: { margin: 5, size: '30%', background: 'transparent' },
                dataLabels: {
                    name: { show: false },
                    value: { show: false },
                    total: { show: true, label: 'Total', color: textCol, fontSize: '12px', fontFamily: 'inherit', formatter: () => ctTotal },
                },
                track: { background: borderC, strokeWidth: '97%' },
            },
        },
        legend: {
            show: true,
            position: 'bottom',
            floating: false,
            fontSize: '12px',
            fontFamily: 'inherit',
            labels: { colors: textCol },
            markers: { size: 7, shape: 'circle' },
            itemMargin: { vertical: 3 },
        },
        dataLabels: { enabled: false },
        tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: v => v + '%' } },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: { height: 300 },
                plotOptions: { radialBar: { endAngle: 360 } },
                legend: { position: 'bottom', floating: false, fontSize: '11px' },
            },
        }],
    }).render();

    // Ensure charts reflow when the main content width changes (sidebar toggle/layout transitions).
    const triggerChartResize = (() => {
        let timer;
        return () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 120);
        };
    })();

    triggerChartResize();
    window.addEventListener('load', triggerChartResize);

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(triggerChartResize);
        const mainEl = document.querySelector('.main');
        if (mainEl) observer.observe(mainEl);
    }
});
</script>
@endpush

@endsection
