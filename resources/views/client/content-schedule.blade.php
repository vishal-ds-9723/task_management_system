@extends('layouts.app')

@push('styles')
<style>
/* ── Client Content Schedule Page ── */
.ccs-wrapper { animation: ccs-fadeIn 0.5s ease-out; }
@keyframes ccs-fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }

.ccs-header {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}
.ccs-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--primary), #8B5CF6, #3B82F6);
}
.ccs-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.ccs-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
}
.ccs-title i {
    width: 36px; height: 36px;
    background: rgba(99,102,241,.1);
    color: #6366F1;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.ccs-month {
    font-size: 13px;
    color: var(--text3);
    font-weight: 600;
    margin-top: 4px;
}
.ccs-carried-badge {
    font-size: 10.5px;
    font-weight: 600;
    background: rgba(245,158,11,.1);
    color: #D97706;
    padding: 5px 12px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.ccs-carried-badge i { font-size: 9px; }

/* ── Summary Stats ── */
.ccs-summary-row {
    display: flex;
    align-items: stretch;
    gap: 14px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.ccs-sum-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px;
    flex: 1;
    min-width: 130px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    box-shadow: var(--shadow-sm);
    transition: all .2s;
}
.ccs-sum-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.ccs-sum-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.ccs-sum-val {
    font-size: 26px;
    font-weight: 800;
    color: var(--text);
    line-height: 1.1;
}
.ccs-sum-label {
    font-size: 10.5px;
    color: var(--text3);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .3px;
}

/* ── Progress Bar ── */
.ccs-progress-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 24px;
    margin-bottom: 24px;
    box-shadow: var(--shadow-sm);
}
.ccs-progress-header {
    display: flex;
    align-items: baseline;
    gap: 10px;
    margin-bottom: 10px;
}
.ccs-pct {
    font-size: 32px;
    font-weight: 800;
    line-height: 1;
}
.ccs-pct-label {
    font-size: 12px;
    color: var(--text3);
    font-weight: 600;
}
.ccs-bar-track {
    height: 10px;
    background: var(--bg);
    border-radius: 5px;
    overflow: hidden;
    position: relative;
    margin-bottom: 8px;
}
.ccs-bar-done {
    height: 100%;
    background: linear-gradient(90deg, #10B981, #059669);
    border-radius: 5px;
    position: absolute; left: 0; top: 0; z-index: 2;
    transition: width .6s ease;
}
.ccs-bar-active {
    height: 100%;
    background: rgba(59,130,246,0.3);
    border-radius: 5px;
    position: absolute; left: 0; top: 0; z-index: 1;
    transition: width .6s ease;
}
.ccs-legend {
    display: flex;
    gap: 20px;
    font-size: 11.5px;
    color: var(--text3);
    font-weight: 500;
    flex-wrap: wrap;
}
.ccs-dot {
    display: inline-block;
    width: 8px; height: 8px;
    border-radius: 2px;
    margin-right: 5px;
    vertical-align: middle;
}
.ccs-dot-empty { background: var(--bg); border: 1px solid var(--border); }

/* ── Content Type Cards ── */
.ccs-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 28px;
}
.ccs-type-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px;
    position: relative;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all .2s;
}
.ccs-type-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
.ccs-type-card.ccs-type-done { border-color: rgba(16,185,129,.25); }
.ccs-type-fill {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    transition: height .5s ease;
    pointer-events: none;
}
.ccs-type-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    position: relative; z-index: 1;
}
.ccs-type-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.ccs-type-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
}
.ccs-type-nums {
    display: flex;
    align-items: baseline;
    gap: 5px;
    margin-bottom: 6px;
    position: relative; z-index: 1;
}
.ccs-type-done-val {
    font-size: 24px;
    font-weight: 800;
    line-height: 1;
}
.ccs-type-planned {
    font-size: 13px;
    color: var(--text3);
    font-weight: 600;
}
.ccs-type-active-badge {
    font-size: 9.5px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 4px;
}
.ccs-type-bar {
    height: 4px;
    background: var(--bg);
    border-radius: 2px;
    overflow: hidden;
    position: relative; z-index: 1;
}
.ccs-type-bar-fill {
    height: 100%;
    border-radius: 2px;
    transition: width .4s ease;
}

/* ── Task List ── */
.ccs-tasks-section {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 24px;
    box-shadow: var(--shadow-sm);
}
.ccs-tasks-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ccs-tasks-title i {
    color: var(--primary);
    font-size: 14px;
}
.ccs-type-group { margin-bottom: 16px; }
.ccs-type-group:last-child { margin-bottom: 0; }
.ccs-type-group-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text3);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.ccs-task-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    background: var(--bg);
    margin-bottom: 6px;
    transition: all .15s;
    text-decoration: none !important;
}
.ccs-task-row:hover {
    background: var(--card2);
    transform: translateX(4px);
}
.ccs-task-status {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.ccs-task-info { flex: 1; min-width: 0; }
.ccs-task-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ccs-task-meta {
    font-size: 11px;
    color: var(--text3);
    margin-top: 1px;
}
.ccs-task-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 5px;
    white-space: nowrap;
}

/* ── Empty State ── */
.ccs-empty {
    text-align: center;
    padding: 60px 20px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
}
.ccs-empty i {
    font-size: 40px;
    color: var(--text3);
    opacity: .3;
    margin-bottom: 12px;
}
.ccs-empty-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 4px;
}
.ccs-empty-sub {
    font-size: 12.5px;
    color: var(--text3);
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .ccs-summary-row { display: grid; grid-template-columns: 1fr 1fr; }
    .ccs-types-grid { grid-template-columns: 1fr 1fr; }
    .ccs-header { padding: 18px 20px; }
    .ccs-title { font-size: 17px; }
    .ccs-pct { font-size: 26px; }
}
@media (max-width: 480px) {
    .ccs-types-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
@php
    $contentTypes = [
        'posts' => ['icon' => 'fa-pen-to-square', 'label' => 'Posts', 'color' => '#3B82F6'],
        'reels' => ['icon' => 'fa-film', 'label' => 'Reels', 'color' => '#F97316'],
        'stories' => ['icon' => 'fa-book-open', 'label' => 'Stories', 'color' => '#10B981'],
        'carousel' => ['icon' => 'fa-images', 'label' => 'Carousels', 'color' => '#EC4899'],
        'videos' => ['icon' => 'fa-video', 'label' => 'Videos', 'color' => '#8B5CF6'],
        'guides' => ['icon' => 'fa-compass', 'label' => 'Guides', 'color' => '#06B6D4'],
        'collections' => ['icon' => 'fa-folder-open', 'label' => 'Collections', 'color' => '#F59E0B'],
        'other' => ['icon' => 'fa-ellipsis', 'label' => 'Other', 'color' => '#6B7280'],
    ];
@endphp

<div class="ccs-wrapper">

    @if($monthlySchedule)
        @php
            $totalPlanned = $monthlySchedule->getTotalContent();
            $totalDone = collect($scheduleProgress)->sum('done');
            $totalActive = collect($scheduleProgress)->sum('active');
            $overallPct = $totalPlanned > 0 ? min(100, round(($totalDone / $totalPlanned) * 100)) : 0;
            $remaining = max(0, $totalPlanned - $totalDone - $totalActive);
        @endphp

        {{-- Header --}}
        <div class="ccs-header">
            <div class="ccs-header-top">
                <div>
                    <div class="ccs-title">
                        <i class="fa-solid fa-calendar-days"></i>
                        Content Schedule
                    </div>
                    <div class="ccs-month">{{ \Carbon\Carbon::create(null, $currentMonth, 1)->format('F Y') }} · {{ $client->name ?? 'My Account' }}</div>
                </div>
                <div style="display:flex;align-items:center;gap:10px">
                    @if($isCarriedForward)
                        <div class="ccs-carried-badge">
                            <i class="fa-solid fa-arrow-rotate-left"></i> Carried from {{ $monthlySchedule->getMonthYearLabel() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="ccs-summary-row">
            <div class="ccs-sum-card">
                <div class="ccs-sum-icon" style="background:rgba(99,102,241,.1);color:#6366F1"><i class="fa-solid fa-layer-group"></i></div>
                <div class="ccs-sum-val">{{ $totalPlanned }}</div>
                <div class="ccs-sum-label">Total Planned</div>
            </div>
            <div class="ccs-sum-card">
                <div class="ccs-sum-icon" style="background:rgba(16,185,129,.1);color:#10B981"><i class="fa-solid fa-circle-check"></i></div>
                <div class="ccs-sum-val" style="color:#10B981">{{ $totalDone }}</div>
                <div class="ccs-sum-label">Completed</div>
            </div>
            <div class="ccs-sum-card">
                <div class="ccs-sum-icon" style="background:rgba(59,130,246,.1);color:#3B82F6"><i class="fa-solid fa-spinner"></i></div>
                <div class="ccs-sum-val" style="color:#3B82F6">{{ $totalActive }}</div>
                <div class="ccs-sum-label">In Progress</div>
            </div>
            <div class="ccs-sum-card">
                <div class="ccs-sum-icon" style="background:{{ $overallPct >= 80 ? 'rgba(16,185,129,.1)' : ($overallPct >= 50 ? 'rgba(245,158,11,.1)' : 'rgba(239,68,68,.1)') }};color:{{ $overallPct >= 80 ? '#10B981' : ($overallPct >= 50 ? '#F59E0B' : '#EF4444') }}"><i class="fa-solid fa-trophy"></i></div>
                <div class="ccs-sum-val" style="color:{{ $overallPct >= 80 ? '#10B981' : ($overallPct >= 50 ? '#F59E0B' : '#EF4444') }}">{{ $overallPct }}%</div>
                <div class="ccs-sum-label">Progress</div>
            </div>
        </div>

        {{-- Overall Progress Bar --}}
        <div class="ccs-progress-card">
            <div class="ccs-progress-header">
                <div class="ccs-pct" style="color:{{ $overallPct >= 80 ? '#10B981' : ($overallPct >= 50 ? '#F59E0B' : 'var(--primary)') }}">{{ $overallPct }}%</div>
                <div class="ccs-pct-label">of monthly content delivered</div>
            </div>
            <div class="ccs-bar-track">
                @if($totalPlanned > 0)
                    <div class="ccs-bar-done" style="width:{{ min(100, ($totalDone / $totalPlanned) * 100) }}%"></div>
                    <div class="ccs-bar-active" style="width:{{ min(100, (($totalDone + $totalActive) / $totalPlanned) * 100) }}%"></div>
                @endif
            </div>
            <div class="ccs-legend">
                <span><span class="ccs-dot" style="background:#10B981"></span> Done ({{ $totalDone }})</span>
                <span><span class="ccs-dot" style="background:rgba(59,130,246,0.5)"></span> In Progress ({{ $totalActive }})</span>
                <span><span class="ccs-dot ccs-dot-empty"></span> Remaining ({{ $remaining }})</span>
            </div>
        </div>

        {{-- Content Type Cards --}}
        <div class="ccs-types-grid">
            @foreach($contentTypes as $key => $type)
                @php
                    $planned = $monthlySchedule->$key ?? 0;
                    if ($planned == 0) continue;
                    $done = $scheduleProgress[$key]['done'] ?? 0;
                    $active = $scheduleProgress[$key]['active'] ?? 0;
                    $pct = $planned > 0 ? min(100, round(($done / $planned) * 100)) : 0;
                @endphp
                <div class="ccs-type-card {{ $pct >= 100 ? 'ccs-type-done' : '' }}">
                    <div class="ccs-type-fill" style="height:{{ $pct }}%;background:{{ $type['color'] }}08"></div>
                    <div class="ccs-type-head">
                        <div class="ccs-type-icon" style="background:{{ $type['color'] }}15;color:{{ $type['color'] }}">
                            <i class="fa-solid {{ $type['icon'] }}"></i>
                        </div>
                        <div class="ccs-type-name">{{ $type['label'] }}</div>
                    </div>
                    <div class="ccs-type-nums">
                        <span class="ccs-type-done-val" style="color:{{ $pct >= 100 ? '#10B981' : 'var(--text)' }}">{{ $done }}</span>
                        <span class="ccs-type-planned">/ {{ $planned }}</span>
                        @if($active > 0)
                            <span class="ccs-type-active-badge" style="background:{{ $type['color'] }}15;color:{{ $type['color'] }}">+{{ $active }} active</span>
                        @endif
                    </div>
                    <div class="ccs-type-bar">
                        <div class="ccs-type-bar-fill" style="width:{{ $pct }}%;background:{{ $type['color'] }}"></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tasks List --}}
        @if($monthTasks->count() > 0)
            <div class="ccs-tasks-section">
                <div class="ccs-tasks-title">
                    <i class="fa-solid fa-list-check"></i>
                    This Month's Tasks ({{ $monthTasks->count() }})
                </div>

                @foreach($contentTypes as $key => $type)
                    @if(!empty($tasksByType[$key]))
                        <div class="ccs-type-group">
                            <div class="ccs-type-group-label">
                                <i class="fa-solid {{ $type['icon'] }}" style="color:{{ $type['color'] }}"></i>
                                {{ $type['label'] }} ({{ count($tasksByType[$key]) }})
                            </div>
                            @foreach($tasksByType[$key] as $task)
                                @php
                                    $statusColors = [
                                        'published' => ['bg' => 'rgba(16,185,129,.1)', 'color' => '#10B981', 'dot' => '#10B981', 'label' => 'Published'],
                                        'completed' => ['bg' => 'rgba(59,130,246,.1)', 'color' => '#3B82F6', 'dot' => '#3B82F6', 'label' => 'Completed'],
                                        'in-progress' => ['bg' => 'rgba(245,158,11,.1)', 'color' => '#F59E0B', 'dot' => '#F59E0B', 'label' => 'In Progress'],
                                        'pending_approval' => ['bg' => 'rgba(139,92,246,.1)', 'color' => '#8B5CF6', 'dot' => '#8B5CF6', 'label' => 'In Review'],
                                        'revision' => ['bg' => 'rgba(239,68,68,.1)', 'color' => '#EF4444', 'dot' => '#EF4444', 'label' => 'Revision'],
                                    ];
                                    $s = $statusColors[$task->status] ?? ['bg' => 'var(--card2)', 'color' => 'var(--text3)', 'dot' => 'var(--text3)', 'label' => ucfirst(str_replace('_', ' ', $task->status))];
                                @endphp
                                <a href="{{ route('client.tasks.show', $task) }}" class="ccs-task-row">
                                    <div class="ccs-task-status" style="background:{{ $s['dot'] }}"></div>
                                    <div class="ccs-task-info">
                                        <div class="ccs-task-title">{{ $task->title ?: 'Untitled' }}</div>
                                        <div class="ccs-task-meta">
                                            {{ $task->deadline ? $task->deadline->format('M d') : ($task->post_date ? $task->post_date->format('M d') : 'No date') }}
                                            @if($task->assignee)
                                                · {{ $task->assignee->name }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ccs-task-badge" style="background:{{ $s['bg'] }};color:{{ $s['color'] }}">{{ $s['label'] }}</div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

    @else
        {{-- No schedule set --}}
        <div class="ccs-empty">
            <i class="fa-solid fa-calendar-xmark"></i>
            <div class="ccs-empty-title">No Content Schedule Set</div>
            <div class="ccs-empty-sub">Your monthly content schedule hasn't been configured yet. Please contact your strategist or admin.</div>
        </div>
    @endif

</div>
@endsection
