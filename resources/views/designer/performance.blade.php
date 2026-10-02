@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/pages/designer-theme.css') }}?v=2.0" rel="stylesheet">
@endpush

@section('content')
<div class="designer-performance-wrap" style="width:100%;max-width:100%;padding-bottom:60px">
    {{-- Page Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px">
        <div>
            <h1 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:22px;font-weight:800;color:var(--dz-text-main);margin:0;letter-spacing:-0.02em">
                Performance & Analytics
            </h1>
            <p style="font-size:13px;color:var(--dz-text-muted);margin:3px 0 0">
                {{ now()->format('F Y') }} · Output throughput, velocity, and quality overview.
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:8px">
            <span class="perf-date-pill">
                <i class="fa-regular fa-calendar" style="color:var(--dz-red);font-size:12px"></i>
                <span>{{ now()->format('M 01') }} – {{ now()->format('M t, Y') }}</span>
            </span>
        </div>
    </div>

    @php
        $renderDelta = function ($delta, $suffix = '') {
            if ($delta === 0 || $delta === null) {
                return '<span class="perf-delta-badge delta-neutral"><i class="fa-solid fa-minus" style="font-size:8px"></i> 0% vs last mo</span>';
            }
            $up = $delta > 0;
            $class = $up ? 'delta-positive' : 'delta-negative';
            $icon = $up ? 'fa-arrow-up' : 'fa-arrow-down';
            $sign = $up ? '+' : '';
            return '<span class="perf-delta-badge ' . $class . '"><i class="fa-solid ' . $icon . '" style="font-size:8px"></i> ' . $sign . $delta . $suffix . ' vs last mo</span>';
        };
    @endphp

    {{-- Top 4 Minimal Metric Cards --}}
    <div class="perf-stats-grid" style="display:grid;grid-template-columns:repeat(4, 1fr);gap:18px;margin-bottom:24px">
        {{-- Completed --}}
        <div class="card perf-stat-card">
            <div class="perf-stat-top">
                <span class="perf-stat-label">Tasks Completed</span>
                <span class="perf-stat-dot" style="background:var(--dz-emerald)"></span>
            </div>
            <div class="perf-stat-value">{{ $tasksCompleted }}</div>
            <div class="perf-stat-sub">{!! $renderDelta($deltaCompleted) !!}</div>
        </div>

        {{-- Assigned --}}
        <div class="card perf-stat-card">
            <div class="perf-stat-top">
                <span class="perf-stat-label">Tasks Assigned</span>
                <span class="perf-stat-dot" style="background:#64748B"></span>
            </div>
            <div class="perf-stat-value">{{ $tasksAssigned }}</div>
            <div class="perf-stat-sub">{!! $renderDelta($deltaAssigned) !!}</div>
        </div>

        {{-- Efficiency Rate --}}
        <div class="card perf-stat-card">
            <div class="perf-stat-top">
                <span class="perf-stat-label">Efficiency Rate</span>
                <span class="perf-stat-dot" style="background:{{ $efficiency >= 75 ? 'var(--dz-emerald)' : ($efficiency >= 50 ? '#F59E0B' : 'var(--dz-red)') }}"></span>
            </div>
            <div class="perf-stat-value" style="color:{{ $efficiency >= 75 ? 'var(--dz-emerald)' : 'var(--dz-text-main)' }}">
                {{ $efficiency }}<span style="font-size:18px;font-weight:700;margin-left:2px">%</span>
            </div>
            <div class="perf-stat-sub">{!! $renderDelta($deltaEfficiency, '%') !!}</div>
        </div>

        {{-- Revision Rate --}}
        <div class="card perf-stat-card">
            <div class="perf-stat-top">
                <span class="perf-stat-label">Revision Rate</span>
                <span class="perf-stat-dot" style="background:{{ $revisionRate <= 20 ? 'var(--dz-emerald)' : 'var(--dz-red)' }}"></span>
            </div>
            <div class="perf-stat-value" style="color:{{ $revisionRate <= 20 ? 'var(--dz-text-main)' : 'var(--dz-red)' }}">
                {{ $revisionRate }}<span style="font-size:18px;font-weight:700;margin-left:2px">%</span>
            </div>
            <div class="perf-stat-sub">
                <span style="font-size:11.5px;font-weight:600;color:var(--dz-text-muted)">{{ $revisedThisMonth }} of {{ $tasksAssigned }} revised</span>
            </div>
        </div>
    </div>

    {{-- Weekly Output Velocity Chart Card --}}
    <div class="card perf-chart-card" style="margin-bottom:24px">
        <div class="perf-card-header">
            <div>
                <h3 class="perf-card-title">Weekly Output Velocity</h3>
                <p class="perf-card-subtitle">Deliverables completed across the last 7 days</p>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <span class="perf-pill-badge">
                    Total: <strong style="color:var(--dz-text-main)">{{ $weeklyCompleted ?? ($weeklyTrend->sum('count') ?? 0) }}</strong> completed
                </span>
                <span class="perf-pill-badge" style="background:#F8FAFC">
                    Avg: <strong style="color:var(--dz-text-main)">{{ $avgDailyCompleted ?? round(($weeklyCompleted ?? $weeklyTrend->sum('count')) / 7, 1) }}</strong> / day
                </span>
            </div>
        </div>

        {{-- Chart Canvas with Guideline Tracks --}}
        <div class="perf-chart-container">
            {{-- Background Grid Guidelines --}}
            <div class="perf-chart-guidelines">
                <div class="guideline-line"></div>
                <div class="guideline-line"></div>
                <div class="guideline-line"></div>
            </div>

            {{-- 7 Days Column Grid --}}
            <div class="perf-bars-flex">
                @foreach($weeklyTrend as $day)
                    @php 
                        $pct = $weeklyMax > 0 ? max(6, ($day['count'] / $weeklyMax) * 100) : 6; 
                        $hasTasks = $day['count'] > 0;
                    @endphp
                    <div class="perf-bar-wrapper">
                        {{-- Bar Track Column --}}
                        <div class="perf-bar-track">
                            <div class="perf-bar-fill {{ $hasTasks ? 'active-fill' : '' }}" 
                                 style="height:{{ $pct }}%" 
                                 data-count="{{ $day['count'] }}"
                                 data-day="{{ $day['label'] }}">
                                @if($hasTasks)
                                    <span class="perf-bar-floating-val">{{ $day['count'] }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Day Name & Date --}}
                        <div class="perf-bar-meta">
                            <span class="perf-day-label">{{ $day['label'] }}</span>
                            @if(isset($day['date']))
                                <span class="perf-date-sublabel">{{ $day['date'] }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bottom Two Equal Columns --}}
    <div class="perf-dual-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:stretch">
        {{-- Card 1: Deliverable Mix (By Task Type) --}}
        <div class="card perf-section-card">
            <div class="perf-card-header">
                <div>
                    <h3 class="perf-card-title">Deliverable Mix</h3>
                    <p class="perf-card-subtitle">Volume distribution by creative format</p>
                </div>
                <span class="perf-count-chip">
                    {{ $typeBreakdown->count() }} {{ \Illuminate\Support\Str::plural('category', $typeBreakdown->count()) }}
                </span>
            </div>

            <div class="perf-type-list">
                @php
                    $maxCount = $typeBreakdown->max() ?: 1;
                    $totalTypeCount = $typeBreakdown->sum() ?: 1;
                @endphp
                @forelse($typeBreakdown as $type => $count)
                    @php $typePct = round(($count / $totalTypeCount) * 100); @endphp
                    <div class="perf-type-row">
                        <div class="perf-type-info">
                            <div style="display:flex;align-items:center;gap:8px">
                                <span class="perf-type-dot"></span>
                                <span class="perf-type-name">{{ $type }}</span>
                            </div>
                            <span class="perf-type-metric">
                                <strong style="color:var(--dz-text-main)">{{ $count }}</strong>
                                <span style="color:var(--dz-text-muted);font-weight:500">({{ $typePct }}%)</span>
                            </span>
                        </div>
                        <div class="perf-progress-track">
                            <div class="perf-progress-bar" style="width:{{ ($count / $maxCount) * 100 }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="perf-empty-state">
                        <i class="fa-regular fa-folder-open" style="font-size:22px;color:var(--dz-text-muted);margin-bottom:8px"></i>
                        <div>No deliverables logged for this month yet.</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Card 2: Quality & Throughput Insights --}}
        <div class="card perf-section-card">
            <div class="perf-card-header">
                <div>
                    <h3 class="perf-card-title">Output & Quality</h3>
                    <p class="perf-card-subtitle">Monthly velocity and review metrics</p>
                </div>
                <span class="perf-status-badge {{ $efficiency >= 75 ? 'status-optimal' : 'status-progress' }}">
                    <span class="status-indicator"></span>
                    {{ $efficiency >= 75 ? 'Optimal Pace' : 'Active Cadence' }}
                </span>
            </div>

            <div class="perf-breakdown-list">
                <div class="perf-breakdown-row">
                    <div class="perf-breakdown-meta">
                        <span class="perf-breakdown-title">Assigned Volume</span>
                        <span class="perf-breakdown-desc">Total tasks allocated this month</span>
                    </div>
                    <span class="perf-breakdown-value">{{ $tasksAssigned }} <small style="font-size:12px;color:var(--dz-text-muted);font-weight:600">tasks</small></span>
                </div>

                <div class="perf-breakdown-row">
                    <div class="perf-breakdown-meta">
                        <span class="perf-breakdown-title">Delivered Output</span>
                        <span class="perf-breakdown-desc">Fully completed and submitted</span>
                    </div>
                    <span class="perf-breakdown-value" style="color:var(--dz-emerald)">{{ $tasksCompleted }} <small style="font-size:12px;color:var(--dz-text-muted);font-weight:600">done</small></span>
                </div>

                <div class="perf-breakdown-row">
                    <div class="perf-breakdown-meta">
                        <span class="perf-breakdown-title">Active / In-Flight</span>
                        <span class="perf-breakdown-desc">Remaining in backlog or revision</span>
                    </div>
                    <span class="perf-breakdown-value">{{ max(0, $tasksAssigned - $tasksCompleted) }} <small style="font-size:12px;color:var(--dz-text-muted);font-weight:600">items</small></span>
                </div>

                <div class="perf-breakdown-row">
                    <div class="perf-breakdown-meta">
                        <span class="perf-breakdown-title">First-Pass Approval</span>
                        <span class="perf-breakdown-desc">Deliverables approved without rework</span>
                    </div>
                    <span class="perf-breakdown-value" style="color:var(--dz-emerald)">
                        {{ max(0, 100 - $revisionRate) }}<small style="font-size:12px;font-weight:700">%</small>
                    </span>
                </div>

                <div class="perf-breakdown-row" style="border-bottom:none;padding-bottom:0">
                    <div class="perf-breakdown-meta">
                        <span class="perf-breakdown-title">Rework Frequency</span>
                        <span class="perf-breakdown-desc">{{ $revisedThisMonth }} task(s) requested revisions</span>
                    </div>
                    <span class="perf-breakdown-value" style="color:{{ $revisionRate > 20 ? 'var(--dz-red)' : 'var(--dz-text-sub)' }}">
                        {{ $revisionRate }}<small style="font-size:12px;font-weight:700">%</small>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ── Date Pill ── */
    .perf-date-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        font-weight: 700;
        color: var(--dz-text-sub);
        background: #FFFFFF;
        border: 1px solid var(--dz-border);
        padding: 7px 14px;
        border-radius: 10px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }

    /* ── Stat Cards ── */
    .perf-stat-card {
        padding: 22px 24px;
        border: 1px solid var(--dz-border);
        border-radius: 16px;
        background: #FFFFFF;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .perf-stat-card:hover {
        border-color: var(--dz-red);
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(239, 68, 68, 0.06);
    }
    .perf-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .perf-stat-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--dz-text-muted);
    }
    .perf-stat-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }
    .perf-stat-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 32px;
        font-weight: 800;
        color: var(--dz-text-main);
        line-height: 1.1;
        letter-spacing: -0.03em;
        margin-bottom: 10px;
    }
    .perf-stat-sub {
        font-size: 11.5px;
        display: flex;
        align-items: center;
    }
    .perf-delta-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
    }
    .delta-positive {
        color: var(--dz-emerald);
        background: rgba(5, 150, 105, 0.08);
    }
    .delta-negative {
        color: var(--dz-red);
        background: rgba(239, 68, 68, 0.08);
    }
    .delta-neutral {
        color: var(--dz-text-muted);
        background: #F1F5F9;
    }

    /* ── Section Cards & Headers ── */
    .perf-chart-card,
    .perf-section-card {
        padding: 26px 30px;
        border: 1px solid var(--dz-border);
        border-radius: 16px;
        background: #FFFFFF;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: border-color 0.2s ease;
    }
    .perf-chart-card:hover,
    .perf-section-card:hover {
        border-color: #CBD5E1;
    }
    .perf-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--dz-border-light);
        flex-wrap: wrap;
        gap: 12px;
    }
    .perf-card-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: var(--dz-text-main);
        margin: 0;
        letter-spacing: -0.01em;
    }
    .perf-card-subtitle {
        font-size: 12.5px;
        color: var(--dz-text-muted);
        margin: 2px 0 0;
    }
    .perf-pill-badge {
        font-size: 12px;
        font-weight: 600;
        color: var(--dz-text-sub);
        background: var(--dz-surface-sub);
        padding: 5px 12px;
        border-radius: 99px;
        border: 1px solid var(--dz-border);
    }
    .perf-count-chip {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--dz-text-muted);
        background: #F8FAFC;
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid var(--dz-border);
    }

    /* ── Status Badge ── */
    .perf-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 11px;
        border-radius: 99px;
        border: 1px solid transparent;
    }
    .status-optimal {
        background: rgba(5, 150, 105, 0.08);
        color: var(--dz-emerald);
        border-color: rgba(5, 150, 105, 0.2);
    }
    .status-progress {
        background: rgba(37, 99, 235, 0.08);
        color: var(--dz-blue);
        border-color: rgba(37, 99, 235, 0.2);
    }
    .status-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    /* ── Velocity Bar Chart ── */
    .perf-chart-container {
        position: relative;
        height: 200px;
        padding-top: 10px;
    }
    .perf-chart-guidelines {
        position: absolute;
        inset: 10px 0 38px 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        pointer-events: none;
        z-index: 1;
    }
    .guideline-line {
        width: 100%;
        height: 1px;
        border-bottom: 1px dashed #F1F5F9;
    }
    .perf-bars-flex {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        height: 100%;
        gap: 16px;
    }
    .perf-bar-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        gap: 8px;
    }
    .perf-bar-track {
        flex: 1;
        width: 100%;
        max-width: 58px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        background: #F8FAFC;
        border: 1px solid #F1F5F9;
        border-radius: 8px;
        overflow: visible;
        position: relative;
    }
    .perf-bar-fill {
        width: 100%;
        background: #E2E8F0;
        border-radius: 7px;
        position: relative;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
    }
    .perf-bar-fill.active-fill {
        background: var(--dz-text-main);
    }
    .perf-bar-fill:hover {
        background: var(--dz-red) !important;
    }
    .perf-bar-floating-val {
        position: absolute;
        top: -22px;
        left: 50%;
        transform: translateX(-50%);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11px;
        font-weight: 800;
        color: var(--dz-text-main);
        transition: color 0.2s ease;
    }
    .perf-bar-fill:hover .perf-bar-floating-val {
        color: var(--dz-red);
    }
    .perf-bar-meta {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1px;
    }
    .perf-day-label {
        font-size: 11px;
        font-weight: 800;
        color: var(--dz-text-main);
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .perf-date-sublabel {
        font-size: 10px;
        font-weight: 600;
        color: var(--dz-text-muted);
    }

    /* ── Deliverable Mix List ── */
    .perf-type-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .perf-type-row {
        transition: var(--dz-transition);
        padding: 4px 0;
    }
    .perf-type-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }
    .perf-type-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--dz-text-main);
        transition: background 0.2s ease;
    }
    .perf-type-row:hover .perf-type-dot {
        background: var(--dz-red);
    }
    .perf-type-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--dz-text-main);
        text-transform: capitalize;
    }
    .perf-type-metric {
        font-size: 12.5px;
    }
    .perf-progress-track {
        background: #F1F5F9;
        border-radius: 99px;
        height: 5px;
        overflow: hidden;
    }
    .perf-progress-bar {
        background: var(--dz-text-main);
        height: 100%;
        border-radius: 99px;
        transition: all 0.4s ease;
    }
    .perf-type-row:hover .perf-progress-bar {
        background: var(--dz-red);
    }
    .perf-empty-state {
        padding: 36px 0;
        text-align: center;
        color: var(--dz-text-muted);
        font-size: 13px;
        font-weight: 500;
    }

    /* ── Output Breakdown List ── */
    .perf-breakdown-list {
        display: flex;
        flex-direction: column;
    }
    .perf-breakdown-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 13px 0;
        border-bottom: 1px solid var(--dz-border-light);
    }
    .perf-breakdown-meta {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }
    .perf-breakdown-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--dz-text-main);
    }
    .perf-breakdown-desc {
        font-size: 11.5px;
        color: var(--dz-text-muted);
    }
    .perf-breakdown-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 16px;
        font-weight: 800;
        color: var(--dz-text-main);
    }

    /* ── Responsive ── */
    @media (max-width: 960px) {
        .perf-stats-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        .perf-dual-grid {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 600px) {
        .perf-stats-grid {
            grid-template-columns: 1fr !important;
        }
        .perf-bar-track {
            max-width: 30px;
        }
    }
</style>
@endsection

