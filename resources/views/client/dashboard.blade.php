@extends('layouts.app')

@push('styles')
<style>
/* ── Client Dashboard Premium ────────────────────────── */
.cd-wrapper { animation: fadeIn 0.6s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.cd-header-grid {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 32px;
    background: var(--card);
    padding: 24px 32px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}

.cd-header-grid::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 4px; height: 100%;
    background: var(--primary-gradient);
}

.cd-client-identity {
    display: flex;
    align-items: center;
    gap: 20px;
}

.cd-client-logo-wrap {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: var(--card2);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border);
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    flex-shrink: 0;
}

.cd-client-logo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 6px;
}

.cd-client-initials {
    font-size: 24px;
    font-weight: 800;
    color: var(--primary);
    background: var(--primary-dim);
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
}

.cd-welcome-info h1 {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    margin: 0;
    letter-spacing: -0.5px;
}

.cd-welcome-info p {
    font-size: 14px;
    color: var(--text3);
    margin: 4px 0 0;
    font-weight: 500;
}

/* Stats Enhancement */
.cd-stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 32px;
}

.cd-stat-premium {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.cd-stat-premium:hover {
    transform: translateY(-6px);
    border-color: var(--primary);
    box-shadow: var(--shadow-lg);
}

.cd-stat-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.cd-stat-data {
    display: flex;
    flex-direction: column;
}

.cd-stat-value {
    font-size: 28px;
    font-weight: 800;
    color: var(--text);
    line-height: 1.2;
}

.cd-stat-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cd-stat-premium { cursor: pointer; }

/* Stats Modal */
.cd-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(8px);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    animation: fadeInModal 0.3s ease;
}

.cd-modal-backdrop.show { display: flex; }

@keyframes fadeInModal { from { opacity: 0; } to { opacity: 1; } }

.cd-modal-box {
    width: 100%;
    max-width: 900px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 24px;
    box-shadow: 0 30px 60px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    max-height: 85vh;
    animation: slideUpModal 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideUpModal { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.cd-modal-header {
    padding: 24px 30px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.cd-modal-title {
    font-size: 20px;
    font-weight: 800;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 12px;
}

.cd-modal-close {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: none;
    background: var(--card2);
    color: var(--text3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.cd-modal-close:hover {
    background: var(--primary);
    color: #fff;
    transform: rotate(90deg);
}

.cd-modal-body {
    padding: 24px 30px;
    overflow-y: auto;
}

.cd-table-wrap {
    width: 100%;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid var(--border2);
}

.cd-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.cd-table th {
    background: var(--card2);
    text-align: left;
    padding: 14px 18px;
    font-weight: 700;
    color: var(--text2);
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--border2);
}

.cd-table td {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border2);
    color: var(--text);
    font-weight: 500;
}

.cd-table tr:last-child td { border-bottom: none; }

.cd-table tr:hover td {
    background: var(--card2);
}

.cd-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.cd-btn-view {
    padding: 6px 14px;
    background: var(--primary-dim);
    color: var(--primary);
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 800;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid transparent;
}

.cd-btn-view:hover {
    background: var(--primary);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);
}

/* Section Styling */
.cd-main-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 24px;
}

.cd-glass-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    box-shadow: var(--shadow-sm);
}

.cd-sec-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.cd-sec-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--text);
    display: flex;
    align-items: center;
    gap: 10px;
}

.cd-sec-title i {
    width: 32px;
    height: 32px;
    background: var(--card2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 14px;
}

.cd-social-viz-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.cd-social-chart-card {
    background: var(--card2);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 18px;
}

.cd-social-chart-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
}

.cd-social-chart-title {
    font-size: 13px;
    font-weight: 800;
    color: var(--text);
}

.cd-social-chart-sub {
    font-size: 11px;
    font-weight: 700;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.cd-social-filter-bar {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
    gap: 12px;
    margin-top: 20px;
    margin-bottom: 18px;
    padding: 14px;
    background: var(--card2);
    border: 1px solid var(--border);
    border-radius: 16px;
}

.cd-social-filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.cd-social-filter-label {
    font-size: 10px;
    font-weight: 800;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.cd-social-filter-input {
    width: 100%;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text);
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 700;
}

.cd-social-filter-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.12);
}

.cd-social-filter-action {
    display: flex;
    align-items: flex-end;
}

.cd-social-filter-reset {
    width: 100%;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--text);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s ease;
}

.cd-social-filter-reset:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.cd-social-filter-summary {
    margin-bottom: 18px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text2);
}

.cd-social-hidden {
    display: none !important;
}

.cd-social-chart-wrap {
    position: relative;
    min-height: 280px;
}

.cd-social-chart-empty {
    padding: 32px 18px;
    text-align: center;
    border: 1px dashed var(--border);
    border-radius: 14px;
    background: var(--card2);
    color: var(--text3);
    font-size: 13px;
    font-weight: 600;
}

.cd-social-chart-footer {
    margin-top: 14px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.cd-social-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(var(--primary-rgb), 0.08);
    color: var(--primary);
    border: 1px solid rgba(var(--primary-rgb), 0.12);
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 11px;
    font-weight: 800;
}

.cd-social-chip strong {
    color: var(--text);
}

/* List Items */
.cd-item-premium {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px;
    background: var(--card2);
    border: 1px solid transparent;
    border-radius: var(--radius-sm);
    margin-bottom: 10px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none !important;
    cursor: pointer;
}

.cd-item-premium:hover {
    background: var(--card);
    border-color: var(--primary);
    transform: translateX(6px);
    box-shadow: 0 10px 25px rgba(var(--primary-rgb), 0.08);
}

.cd-item-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: var(--surface);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    flex-shrink: 0;
}

.cd-item-info { flex: 1; min-width: 0; }
.cd-item-name { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 2px; }
.cd-item-sub { font-size: 12px; color: var(--text3); font-weight: 500; }

.cd-item-meta {
    text-align: right;
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
    white-space: nowrap;
}

@media(max-width: 1100px) {
    .cd-main-grid { grid-template-columns: 1fr; }
    .cd-social-viz-grid { grid-template-columns: 1fr; }
    .cd-social-filter-bar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media(max-width: 768px) {
    .cd-stats-row { grid-template-columns: 1fr 1fr; }
    .cd-header-grid { flex-direction: column; text-align: center; padding: 32px 20px; }
    .cd-client-identity { flex-direction: column; gap: 12px; }
    .cd-header-grid::before { width: 100%; height: 4px; }
    .cd-social-chart-wrap { min-height: 240px; }
    .cd-social-filter-bar { grid-template-columns: 1fr; }
}
@media(max-width: 1100px) {
    .cd-main-grid { grid-template-columns: 1fr; }
}
</style>

@endpush

@section('content')

@php
    $clientName = $client->name ?? 'Client';
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $initials = collect(explode(' ', $clientName))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
@endphp

<div class="cd-wrapper">
    {{-- Enhanced Header with Client Logo --}}
    <div class="cd-header-grid">
        <div class="cd-client-identity">
            <div class="cd-client-logo-wrap">
                @if($client && $client->logo)
                    <img src="{{ Storage::url($client->logo) }}" alt="{{ $clientName }}">
                @else
                    <div class="cd-client-initials" style="color: {{ $client->color ?? 'var(--primary)' }}; background: {{ ($client->color ?? 'var(--primary)') . '15' }}">
                        {{ $initials }}
                    </div>
                @endif
            </div>
            <div class="cd-welcome-info">
                <h1>{{ $greeting }}, {{ auth()->user()->name }}</h1>
                <p>Manage your content and festivals for <strong>{{ $clientName }}</strong></p>
            </div>
        </div>
        <div class="cd-header-actions">
            <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--text3);background:var(--card2);padding:8px 16px;border-radius:var(--radius-sm);border:1px solid var(--border)">
                <i class="fas fa-calendar-alt"></i> {{ now()->format('l, jS F') }}
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="cd-stats-row">
        <div class="cd-stat-premium" onclick="openStatModal('published')">
            <div class="cd-stat-icon-box" style="background:var(--teal-dim);color:var(--teal)"><i class="fa-solid fa-circle-check"></i></div>
            <div class="cd-stat-data">
                <div class="cd-stat-value">{{ $publishedCount }}</div>
                <div class="cd-stat-label">Published</div>
            </div>
        </div>
        <div class="cd-stat-premium" onclick="openStatModal('in_progress')">
            <div class="cd-stat-icon-box" style="background:var(--blue-dim);color:var(--blue)"><i class="fa-solid fa-spinner fa-spin-pulse"></i></div>
            <div class="cd-stat-data">
                <div class="cd-stat-value">{{ $inProgressCount }}</div>
                <div class="cd-stat-label">In Progress</div>
            </div>
        </div>
        <div class="cd-stat-premium" onclick="openStatModal('completed')">
            <div class="cd-stat-icon-box" style="background:var(--primary-dim);color:var(--primary)"><i class="fa-solid fa-check-double"></i></div>
            <div class="cd-stat-data">
                <div class="cd-stat-value">{{ $completedCount }}</div>
                <div class="cd-stat-label">Completed</div>
            </div>
        </div>
        <div class="cd-stat-premium" onclick="openStatModal('festivals')">
            <div class="cd-stat-icon-box" style="background:var(--yellow-dim);color:var(--yellow)"><i class="fa-solid fa-star"></i></div>
            <div class="cd-stat-data">
                <div class="cd-stat-value">{{ $selectedFestivalsCount }}</div>
                <div class="cd-stat-label">Festivals Selected</div>
            </div>
        </div>
    </div>

    {{-- ===== MONTHLY CONTENT SCHEDULE ===== --}}
    @if($monthlySchedule)
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
        $totalPlanned = $monthlySchedule->getTotalContent();
        $totalDone = collect($scheduleProgress)->sum('done');
        $totalActive = collect($scheduleProgress)->sum('active');
        $overallPct = $totalPlanned > 0 ? min(100, round(($totalDone / $totalPlanned) * 100)) : 0;
        $remaining = max(0, $totalPlanned - $totalDone - $totalActive);
    @endphp
    <div class="cd-glass-card" style="margin-bottom:24px;position:relative;overflow:hidden">
        {{-- Accent stripe --}}
        <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),#8B5CF6,#3B82F6)"></div>

        <div class="cd-sec-header" style="margin-bottom:16px">
            <div class="cd-sec-title">
                <i class="fa-solid fa-calendar-days"></i>
                Monthly Schedule — {{ \Carbon\Carbon::create(null, $currentMonth, 1)->format('F Y') }}
            </div>
            @if($isCarriedForward)
                <div style="font-size:10px;font-weight:600;background:rgba(245,158,11,.1);color:#D97706;padding:4px 10px;border-radius:6px;display:inline-flex;align-items:center;gap:5px">
                    <i class="fa-solid fa-arrow-rotate-left" style="font-size:8px"></i> Carried from {{ $monthlySchedule->getMonthYearLabel() }}
                </div>
            @endif
        </div>

        {{-- Progress summary --}}
        <div style="display:flex;align-items:center;gap:20px;margin-bottom:14px;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
                <div style="display:flex;align-items:baseline;gap:8px;margin-bottom:6px">
                    <span style="font-size:28px;font-weight:800;color:{{ $overallPct >= 80 ? '#10B981' : ($overallPct >= 50 ? '#F59E0B' : 'var(--primary)') }};line-height:1">{{ $overallPct }}%</span>
                    <span style="font-size:12px;color:var(--text3);font-weight:600">complete</span>
                </div>
                <div style="height:8px;background:var(--card2);border-radius:4px;overflow:hidden;position:relative;margin-bottom:6px">
                    @if($totalPlanned > 0)
                        <div style="height:100%;width:{{ min(100, ($totalDone / $totalPlanned) * 100) }}%;background:linear-gradient(90deg,#10B981,#059669);border-radius:4px;position:absolute;left:0;top:0;z-index:2;transition:width .5s ease"></div>
                        <div style="height:100%;width:{{ min(100, (($totalDone + $totalActive) / $totalPlanned) * 100) }}%;background:rgba(59,130,246,0.25);border-radius:4px;position:absolute;left:0;top:0;z-index:1;transition:width .5s ease"></div>
                    @endif
                </div>
                <div style="display:flex;gap:16px;font-size:11px;color:var(--text3);font-weight:500">
                    <span style="display:flex;align-items:center;gap:4px"><span style="width:7px;height:7px;border-radius:2px;background:#10B981"></span> {{ $totalDone }} Done</span>
                    <span style="display:flex;align-items:center;gap:4px"><span style="width:7px;height:7px;border-radius:2px;background:rgba(59,130,246,0.5)"></span> {{ $totalActive }} Active</span>
                    <span style="display:flex;align-items:center;gap:4px"><span style="width:7px;height:7px;border-radius:2px;background:var(--card2);border:1px solid var(--border)"></span> {{ $remaining }} Left</span>
                </div>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
                <div style="text-align:center;padding:8px 14px;background:var(--card2);border-radius:10px;min-width:60px">
                    <div style="font-size:20px;font-weight:800;color:var(--text)">{{ $totalPlanned }}</div>
                    <div style="font-size:9px;color:var(--text3);font-weight:600;text-transform:uppercase;letter-spacing:.3px">Planned</div>
                </div>
                <div style="text-align:center;padding:8px 14px;background:rgba(16,185,129,.06);border-radius:10px;min-width:60px">
                    <div style="font-size:20px;font-weight:800;color:#10B981">{{ $totalDone }}</div>
                    <div style="font-size:9px;color:#10B981;font-weight:600;text-transform:uppercase;letter-spacing:.3px">Done</div>
                </div>
            </div>
        </div>

        {{-- Content types grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(160px, 1fr));gap:10px">
            @foreach($contentTypes as $key => $type)
                @php
                    $planned = $monthlySchedule->$key;
                    if ($planned == 0) continue;
                    $done = $scheduleProgress[$key]['done'] ?? 0;
                    $active = $scheduleProgress[$key]['active'] ?? 0;
                    $pct = $planned > 0 ? min(100, round(($done / $planned) * 100)) : 0;
                @endphp
                <div style="background:var(--card2);border-radius:10px;padding:12px;position:relative;overflow:hidden;border:1px solid {{ $pct >= 100 ? 'rgba(16,185,129,.2)' : 'transparent' }}">
                    {{-- Fill background --}}
                    <div style="position:absolute;bottom:0;left:0;right:0;height:{{ $pct }}%;background:{{ $type['color'] }}08;transition:height .4s ease"></div>
                    <div style="position:relative;z-index:1">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                            <div style="width:28px;height:28px;border-radius:7px;background:{{ $type['color'] }}12;color:{{ $type['color'] }};display:flex;align-items:center;justify-content:center;font-size:12px">
                                <i class="fa-solid {{ $type['icon'] }}"></i>
                            </div>
                            <div>
                                <div style="font-size:11px;font-weight:700;color:var(--text2)">{{ $type['label'] }}</div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:baseline;gap:4px;margin-bottom:4px">
                            <span style="font-size:18px;font-weight:800;color:{{ $pct >= 100 ? '#10B981' : 'var(--text)' }}">{{ $done }}</span>
                            <span style="font-size:11px;color:var(--text3);font-weight:600">/ {{ $planned }}</span>
                            @if($active > 0)
                                <span style="font-size:9px;color:{{ $type['color'] }};font-weight:700;margin-left:2px">+{{ $active }}</span>
                            @endif
                        </div>
                        <div style="height:3px;background:var(--border);border-radius:2px;overflow:hidden">
                            <div style="height:100%;width:{{ $pct }}%;background:{{ $type['color'] }};border-radius:2px;transition:width .3s ease"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ===== SOCIAL MEDIA ANALYSIS SUMMARY ===== --}}
    <div class="cd-glass-card" style="margin-bottom:24px; position:relative; overflow:hidden">
        <div style="position:absolute; top:0; left:0; width:4px; height:100%; background:var(--teal)"></div>
        <div class="cd-sec-header">
            <div class="cd-sec-title">
                <i class="fa-solid fa-chart-line"></i>
                Social Media Analysis Summary
            </div>
            <a href="{{ route('client.social-analysis') }}" class="btn-sec" style="font-size:12px; padding:6px 14px">View Full Analysis</a>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px">
            <div style="background:var(--card2); padding:16px; border-radius:12px; border:1px solid var(--border)">
                <div style="font-size:11px; font-weight:700; color:var(--text3); text-transform:uppercase; margin-bottom:4px">Total Ad Spend</div>
                <div style="font-size:22px; font-weight:800; color:var(--teal)">₹{{ number_format($totalAdSpend, 2) }}</div>
            </div>
            <div style="background:var(--card2); padding:16px; border-radius:12px; border:1px solid var(--border)">
                <div style="font-size:11px; font-weight:700; color:var(--text3); text-transform:uppercase; margin-bottom:4px">Organic vs Inorganic</div>
                <div style="font-size:14px; font-weight:700; color:var(--text)">
                    <span style="color:var(--teal)">{{ $organicCount }} Organic</span> ·
                    <span style="color:var(--primary)">{{ $inorganicCount }} Paid</span>
                </div>
            </div>
            <div style="background:var(--card2); padding:16px; border-radius:12px; border:1px solid var(--border)">
                <div style="font-size:11px; font-weight:700; color:var(--text3); text-transform:uppercase; margin-bottom:8px">Linked Accounts</div>
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap">
                    @php $pIcons = ['instagram'=>'fa-instagram','facebook'=>'fa-facebook','twitter'=>'fa-x-twitter','linkedin'=>'fa-linkedin','youtube'=>'fa-youtube','tiktok'=>'fa-tiktok','whatsapp'=>'fa-whatsapp']; @endphp
                    @forelse($availableAccounts as $acc)
                        <div title="{{ ucfirst($acc['platform']) }}: {{ $acc['label'] }}" style="display:flex; align-items:center; gap:6px; background:var(--card); border:1px solid var(--border); padding:4px 10px; border-radius:8px; color:var(--text2); font-size:12px; font-weight:600">
                            <i class="fa-brands {{ $pIcons[$acc['platform']] ?? 'fa-link' }}" style="color:var(--primary)"></i>
                            <span>{{ $acc['label'] }}</span>
                        </div>
                    @empty
                        <span style="font-size:12px; color:var(--text3)">No platforms linked</span>
                    @endforelse
                </div>
            </div>
            <div style="background:var(--card2); padding:16px; border-radius:12px; border:1px solid var(--border)">
                <div style="font-size:11px; font-weight:700; color:var(--text3); text-transform:uppercase; margin-bottom:4px">Top Target Area</div>
                <div style="font-size:18px; font-weight:800; color:var(--text)">{{ $locations->keys()->first() ?? 'N/A' }}</div>
            </div>
        </div>

        @if($rawMetrics->isNotEmpty())
            @php
                $metricsStartDate = data_get($rawMetrics->first(), 'date', now()->format('Y-m-d'));
                $metricsEndDate = data_get($rawMetrics->last(), 'date', now()->format('Y-m-d'));
            @endphp

            <div class="cd-social-filter-bar">
                <div class="cd-social-filter-group">
                    <label class="cd-social-filter-label" for="clientDashboardStartDate">Start Date</label>
                    <input class="cd-social-filter-input" type="date" id="clientDashboardStartDate" value="{{ $metricsStartDate }}" min="{{ $metricsStartDate }}" max="{{ $metricsEndDate }}">
                </div>
                <div class="cd-social-filter-group">
                    <label class="cd-social-filter-label" for="clientDashboardEndDate">End Date</label>
                    <input class="cd-social-filter-input" type="date" id="clientDashboardEndDate" value="{{ $metricsEndDate }}" min="{{ $metricsStartDate }}" max="{{ $metricsEndDate }}">
                </div>
                <div class="cd-social-filter-group">
                    <label class="cd-social-filter-label" for="clientDashboardPlatformFilter">Platform</label>
                    <select class="cd-social-filter-input" id="clientDashboardPlatformFilter">
                        <option value="all">All Platforms</option>
                        @foreach($availablePlatforms as $platform)
                            <option value="{{ $platform }}">{{ ucfirst($platform) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="cd-social-filter-group">
                    <label class="cd-social-filter-label" for="clientDashboardPromotionFilter">Traffic Source</label>
                    <select class="cd-social-filter-input" id="clientDashboardPromotionFilter">
                        <option value="all">Organic + Paid</option>
                        <option value="organic">Organic Only</option>
                        <option value="paid">Paid Only</option>
                    </select>
                </div>
                <div class="cd-social-filter-action">
                    <button type="button" class="cd-social-filter-reset" id="clientDashboardResetFilters">Reset Filters</button>
                </div>
            </div>

            <div class="cd-social-filter-summary" id="clientDashboardFilterSummary"></div>
            <div class="cd-social-chart-empty cd-social-hidden" id="clientDashboardNoData">
                No social snapshots match the current dashboard filters.
            </div>

            <div class="cd-social-viz-grid" id="clientDashboardSocialVizGrid">
                <div class="cd-social-chart-card">
                    <div class="cd-social-chart-head">
                        <div>
                            <div class="cd-social-chart-title">Performance Trend</div>
                            <div class="cd-social-chart-sub" id="clientDashboardTrendSubtitle">Latest {{ min($timelineData->count(), 8) }} snapshot dates</div>
                        </div>
                    </div>
                    <div class="cd-social-chart-wrap">
                        <canvas id="clientDashboardTrendChart"></canvas>
                    </div>
                    <div class="cd-social-chart-footer">
                        <div class="cd-social-chip"><span>Reach</span> <strong id="clientDashboardReachChip">{{ number_format($timelineData->sum('reach')) }}</strong></div>
                        <div class="cd-social-chip"><span>Likes</span> <strong id="clientDashboardLikesChip">{{ number_format($timelineData->sum('likes')) }}</strong></div>
                    </div>
                </div>

                <div class="cd-social-chart-card">
                    <div class="cd-social-chart-head">
                        <div>
                            <div class="cd-social-chart-title">Platform Reach Mix</div>
                            <div class="cd-social-chart-sub" id="clientDashboardPlatformSubtitle">Aggregated from all tracked snapshots</div>
                        </div>
                    </div>
                    <div class="cd-social-chart-wrap">
                        <canvas id="clientDashboardPlatformChart"></canvas>
                    </div>
                    <div class="cd-social-chart-footer">
                        <div class="cd-social-chip"><span>Organic</span> <strong id="clientDashboardOrganicChip">{{ number_format($organicCount) }}</strong></div>
                        <div class="cd-social-chip"><span>Paid</span> <strong id="clientDashboardPaidChip">{{ number_format($inorganicCount) }}</strong></div>
                    </div>
                </div>
            </div>
        @else
            <div class="cd-social-chart-empty" style="margin-top:20px;">
                Social charts will appear here after your team adds social metric snapshots for this client.
            </div>
        @endif
    </div>

    <div class="cd-main-grid">

        {{-- Recent Activity / Published Content --}}
        <div class="cd-glass-card">
            <div class="cd-sec-header">
                <div class="cd-sec-title">
                    <i class="fa-solid fa-bullhorn"></i>
                    Recent Published
                </div>
                <a href="{{ route('client.published-content') }}" class="btn-sec" style="font-size:12px;padding:6px 14px">View All</a>
            </div>

            @if($recentPublished->count())
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:16px">
                    @foreach($recentPublished->take(4) as $task)
                    <a href="{{ route('client.tasks.show', $task->id) }}" class="cd-item-premium">
                        <div class="cd-item-icon" style="color: var(--primary)">
                            <i class="fa-solid fa-{{ $task->type === 'reel' ? 'film' : ($task->type === 'story' ? 'mobile-screen' : ($task->type === 'video' ? 'video' : 'image')) }}"></i>
                        </div>
                        <div class="cd-item-info">
                            <div class="cd-item-name">{{ $task->title ?: 'Untitled Post' }}</div>
                            <div class="cd-item-sub">{{ ucfirst($task->type) }} · {{ $task->completed_at?->diffForHumans() }}</div>
                        </div>
                        <div class="cd-item-meta">
                             <div style="display:flex;gap:4px">
                                @foreach(is_array($task->platform) ? $task->platform : [] as $p)
                                    <i class="fa-brands fa-{{ $p === 'twitter' ? 'x-twitter' : $p }}" style="font-size:12px;opacity:0.6"></i>
                                @endforeach
                             </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state" style="padding:40px 0">
                    <div class="empty-state-icon"><i class="fa-solid fa-bullhorn"></i></div>
                    <div class="empty-state-text">No published content yet</div>
                </div>
            @endif
        </div>

        {{-- Upcoming Festivals --}}
        <div class="cd-glass-card">
            <div class="cd-sec-header">
                <div class="cd-sec-title">
                    <i class="fa-solid fa-calendar-star"></i>
                    Festivals
                </div>
                <a href="{{ route('client.festivals') }}" class="btn-sec" style="font-size:12px;padding:6px 14px">Full Calendar</a>
            </div>

            @if($upcomingFestivals->count())
                @foreach($upcomingFestivals as $festival)
                @php $daysAway = now()->startOfDay()->diffInDays($festival->date->startOfDay(), false); @endphp
                <a href="{{ route('client.festivals', ['month' => $festival->date->format('Y-m')]) }}" class="cd-item-premium">
                    <div class="cd-item-icon" style="font-size: 20px">
                        {{ $festival->emoji ?? '🎉' }}
                    </div>
                    <div class="cd-item-info">
                        <div class="cd-item-name">{{ $festival->name }}</div>
                        <div class="cd-item-sub">{{ $festival->date->format('M d, Y') }}</div>
                    </div>
                    <div class="cd-item-meta">
                        @if($daysAway == 0) <span style="color:var(--teal)">Today</span>
                        @elseif($daysAway == 1) <span style="color:var(--primary)">Tomorrow</span>
                        @elseif($daysAway > 0) {{ $daysAway }} days
                        @else <span style="opacity:0.5">Passed</span>
                        @endif
                    </div>
                </a>
                @endforeach
            @else
                <div class="empty-state" style="padding:40px 0">
                    <div class="empty-state-icon"><i class="fa-solid fa-calendar"></i></div>
                    <div class="empty-state-text">No upcoming festivals</div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Stats Modal --}}
<div class="cd-modal-backdrop" id="statsModal">
    <div class="cd-modal-box">
        <div class="cd-modal-header">
            <div class="cd-modal-title">
                <i class="fa-solid fa-chart-line"></i>
                <span id="modalTitle">Details</span>
            </div>
            <button class="cd-modal-close" onclick="closeStatModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="cd-modal-body">
            <div class="cd-table-wrap">
                <table class="cd-table">
                    <thead>
                        <tr>
                            <th>Name / Title</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Status / Meta</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="modalTableBody">
                        <!-- Data rows here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const clientDashboardMetrics = @json($rawMetrics);
    let clientDashboardTrendChart;
    let clientDashboardPlatformChart;

    function destroyClientDashboardCharts() {
        if (clientDashboardTrendChart) {
            clientDashboardTrendChart.destroy();
            clientDashboardTrendChart = null;
        }

        if (clientDashboardPlatformChart) {
            clientDashboardPlatformChart.destroy();
            clientDashboardPlatformChart = null;
        }
    }

    function formatClientDashboardMetric(value) {
        return Number(value || 0).toLocaleString();
    }

    function formatClientDashboardPlatform(platform) {
        const label = String(platform || 'other').replace(/[_-]+/g, ' ');
        return label.charAt(0).toUpperCase() + label.slice(1);
    }

    function aggregateClientDashboardTimeline(metrics) {
        const grouped = metrics.reduce((carry, row) => {
            if (!carry[row.date]) {
                carry[row.date] = {
                    date: row.date,
                    label: row.display_date,
                    reach: 0,
                    views: 0,
                    likes: 0,
                };
            }

            carry[row.date].reach += Number(row.reach || 0);
            carry[row.date].views += Number(row.views || 0);
            carry[row.date].likes += Number(row.likes || 0);
            return carry;
        }, {});

        return Object.values(grouped).sort((a, b) => a.date.localeCompare(b.date));
    }

    function getClientDashboardFilteredMetrics() {
        const startDate = document.getElementById('clientDashboardStartDate')?.value || '';
        const endDate = document.getElementById('clientDashboardEndDate')?.value || '';
        const platform = document.getElementById('clientDashboardPlatformFilter')?.value || 'all';
        const promotion = document.getElementById('clientDashboardPromotionFilter')?.value || 'all';

        return clientDashboardMetrics.filter(row => {
            const matchesDate = (!startDate || row.date >= startDate) && (!endDate || row.date <= endDate);
            const matchesPlatform = platform === 'all' || row.platform === platform;
            const matchesPromotion = promotion === 'all'
                || (promotion === 'paid' && row.paid)
                || (promotion === 'organic' && !row.paid);

            return matchesDate && matchesPlatform && matchesPromotion;
        });
    }

    function updateClientDashboardSocialSummary(metrics) {
        const totals = metrics.reduce((carry, row) => {
            carry.reach += Number(row.reach || 0);
            carry.views += Number(row.views || 0);
            carry.likes += Number(row.likes || 0);
            carry.organic += row.paid ? 0 : 1;
            carry.paid += row.paid ? 1 : 0;
            return carry;
        }, {
            reach: 0,
            views: 0,
            likes: 0,
            organic: 0,
            paid: 0,
        });

        const platform = document.getElementById('clientDashboardPlatformFilter')?.value || 'all';
        const promotion = document.getElementById('clientDashboardPromotionFilter')?.value || 'all';
        const startDate = document.getElementById('clientDashboardStartDate')?.value || '';
        const endDate = document.getElementById('clientDashboardEndDate')?.value || '';
        const timeline = aggregateClientDashboardTimeline(metrics);

        document.getElementById('clientDashboardReachChip').textContent = formatClientDashboardMetric(totals.reach);
        document.getElementById('clientDashboardLikesChip').textContent = formatClientDashboardMetric(totals.likes);
        document.getElementById('clientDashboardOrganicChip').textContent = formatClientDashboardMetric(totals.organic);
        document.getElementById('clientDashboardPaidChip').textContent = formatClientDashboardMetric(totals.paid);

        document.getElementById('clientDashboardTrendSubtitle').textContent = timeline.length
            ? `Showing ${Math.min(timeline.length, 8)} of ${timeline.length} filtered snapshot dates`
            : 'No data for the current filters';

        document.getElementById('clientDashboardPlatformSubtitle').textContent = metrics.length
            ? 'Reach mix for the filtered snapshot set'
            : 'No platform mix available';

        const summaryParts = [platform === 'all' ? 'all platforms' : formatClientDashboardPlatform(platform)];
        summaryParts.push(promotion === 'all' ? 'organic and paid traffic' : `${promotion} traffic only`);

        if (startDate && endDate) {
            summaryParts.push(`${startDate} to ${endDate}`);
        }

        document.getElementById('clientDashboardFilterSummary').textContent = metrics.length
            ? `Showing ${formatClientDashboardMetric(metrics.length)} snapshots across ${summaryParts.join(' • ')}.`
            : 'No social snapshots match the selected dashboard filters.';
    }

    function toggleClientDashboardNoDataState(hasData) {
        const noData = document.getElementById('clientDashboardNoData');
        const grid = document.getElementById('clientDashboardSocialVizGrid');

        if (!noData || !grid) {
            return;
        }

        noData.classList.toggle('cd-social-hidden', hasData);
        grid.classList.toggle('cd-social-hidden', !hasData);
    }

    function renderClientDashboardSocialCharts(metrics) {
        destroyClientDashboardCharts();

        if (!metrics.length || !window.Chart) {
            return;
        }

        const trendCanvas = document.getElementById('clientDashboardTrendChart');
        const platformCanvas = document.getElementById('clientDashboardPlatformChart');

        if (!trendCanvas || !platformCanvas) {
            return;
        }

        const recentTimeline = aggregateClientDashboardTimeline(metrics).slice(-8);
        const platformBuckets = metrics.reduce((carry, row) => {
            const platform = row.platform || 'other';
            if (!carry[platform]) {
                carry[platform] = 0;
            }

            carry[platform] += Number(row.reach || 0);
            return carry;
        }, {});

        const platformData = Object.entries(platformBuckets)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 6);

        clientDashboardTrendChart = new Chart(trendCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: recentTimeline.map(item => item.label),
                datasets: [
                    {
                        label: 'Reach',
                        data: recentTimeline.map(item => item.reach),
                        borderColor: '#0EA5E9',
                        backgroundColor: 'rgba(14, 165, 233, 0.14)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                    {
                        label: 'Likes',
                        data: recentTimeline.map(item => item.likes),
                        borderColor: '#EC4899',
                        backgroundColor: 'rgba(236, 72, 153, 0.08)',
                        fill: false,
                        tension: 0.35,
                        pointRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            font: { weight: '700' }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback(value) {
                                return Number(value).toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        clientDashboardPlatformChart = new Chart(platformCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: platformData.map(([platform]) => formatClientDashboardPlatform(platform)),
                datasets: [{
                    data: platformData.map(([, total]) => total),
                    backgroundColor: ['#4F46E5', '#0EA5E9', '#10B981', '#F59E0B', '#EC4899', '#8B5CF6'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 16,
                            font: { weight: '700' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const label = context.label || 'Unknown';
                                const value = Number(context.parsed || 0).toLocaleString();
                                return `${label}: ${value} reach`;
                            }
                        }
                    }
                }
            }
        });
    }

    function applyClientDashboardSocialFilters() {
        const filteredMetrics = getClientDashboardFilteredMetrics();
        toggleClientDashboardNoDataState(filteredMetrics.length > 0);
        updateClientDashboardSocialSummary(filteredMetrics);
        renderClientDashboardSocialCharts(filteredMetrics);
    }

    function initClientDashboardSocialCharts() {
        if (!window.Chart || !clientDashboardMetrics.length) {
            return;
        }

        document.getElementById('clientDashboardStartDate')?.addEventListener('change', applyClientDashboardSocialFilters);
        document.getElementById('clientDashboardEndDate')?.addEventListener('change', applyClientDashboardSocialFilters);
        document.getElementById('clientDashboardPlatformFilter')?.addEventListener('change', applyClientDashboardSocialFilters);
        document.getElementById('clientDashboardPromotionFilter')?.addEventListener('change', applyClientDashboardSocialFilters);
        document.getElementById('clientDashboardResetFilters')?.addEventListener('click', () => {
            const startDateInput = document.getElementById('clientDashboardStartDate');
            const endDateInput = document.getElementById('clientDashboardEndDate');
            const platformFilter = document.getElementById('clientDashboardPlatformFilter');
            const promotionFilter = document.getElementById('clientDashboardPromotionFilter');

            if (startDateInput) startDateInput.value = startDateInput.min;
            if (endDateInput) endDateInput.value = endDateInput.max;
            if (platformFilter) platformFilter.value = 'all';
            if (promotionFilter) promotionFilter.value = 'all';

            applyClientDashboardSocialFilters();
        });

        applyClientDashboardSocialFilters();
    }

    async function openStatModal(type) {
        const modal = document.getElementById('statsModal');
        const titleEl = document.getElementById('modalTitle');
        const tableBody = document.getElementById('modalTableBody');

        // Show loading state
        tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text3)"><i class="fas fa-spinner fa-spin"></i> Loading data...</td></tr>';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        try {
            const res = await fetch(`/client/dashboard/stat-details/${type}`);
            const data = await res.json();

            titleEl.textContent = data.title;

            if (data.data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text3)">No data found for this category.</td></tr>';
                return;
            }

            tableBody.innerHTML = data.data.map(item => `
                <tr>
                    <td><strong>${item.title}</strong></td>
                    <td><span class="cd-badge" style="background:var(--card2);color:var(--text2)">${item.type}</span></td>
                    <td><i class="far fa-calendar-alt" style="margin-right:6px;opacity:0.6"></i> ${item.date}</td>
                    <td><span style="font-weight:600;font-size:13px">${item.meta}</span></td>
                    <td style="text-align:right">
                        ${item.url !== '#' ? `
                            <a href="${item.url}" class="cd-btn-view">
                                <i class="fa-solid fa-eye"></i> View
                            </a>
                        ` : '<span style="color:var(--text3);font-size:11px;font-style:italic">Details on dashboard</span>'}
                    </td>
                </tr>
            `).join('');

        } catch (err) {
            tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:40px;color:var(--red)">Failed to load data. Please try again.</td></tr>';
        }
    }

    function closeStatModal() {
        const modal = document.getElementById('statsModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    initClientDashboardSocialCharts();

    // Close on escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeStatModal();
    });

    // Close on backdrop click
    document.getElementById('statsModal').addEventListener('click', e => {
        if (e.target.id === 'statsModal') closeStatModal();
    });
</script>

@endpush
