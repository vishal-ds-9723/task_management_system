@extends('layouts.app')

@section('content')
@php
    $prev = $monthStart->copy()->subMonth();
    $next = $monthStart->copy()->addMonth();
    $isCurrentMonth = $monthStart->isSameMonth(today());
    $initials = fn (?string $name) => collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: '?';
    $hue = fn (?string $name) => 'hsl(' . (crc32((string) $name) % 360) . ', 55%, 52%)';
    $platformIcon = fn (?string $p) => [
        'instagram' => ['fa-brands fa-instagram', '#E1306C'], 'facebook' => ['fa-brands fa-facebook-f', '#1877F2'],
        'linkedin' => ['fa-brands fa-linkedin-in', '#0A66C2'], 'twitter' => ['fa-brands fa-x-twitter', '#111827'],
        'youtube' => ['fa-brands fa-youtube', '#FF0000'], 'whatsapp' => ['fa-brands fa-whatsapp', '#22C55E'],
        'tiktok' => ['fa-brands fa-tiktok', '#111827'], 'pinterest' => ['fa-brands fa-pinterest-p', '#E60023'],
    ][$p] ?? ['fas fa-globe', '#64748B'];

    $planned = $rows->filter(fn ($r) => $r->planned > 0)->values();
    $unplanned = $rows->filter(fn ($r) => $r->planned === 0)->values();
    $attention = $planned->filter(fn ($r) => in_array($r->status, ['behind', 'needs_tasks'], true))->count();

    // Overall stacked bar, relative to everything planned (or created, if more)
    $totalScale = max($summary['planned'], $summary['created'], 1);
    $overall = [
        'posted' => $rows->sum('posted'),
        'ready' => $rows->sum('ready'),
        'prod' => $rows->sum('inProduction'),
        'missed' => $rows->sum('missed'),
        'todo' => $rows->sum('toCreate'),
    ];
    $pct = fn ($n, $scale) => round($n / max($scale, 1) * 100, 2);
@endphp

<style>
.cp2 {
    --ink: var(--text, #0f172a); --muted: var(--text3, #94a3b8); --soft: var(--text2, #475569);
    --line: var(--border, #e7eaf0); --panel: var(--card, #fff); --tint: var(--card2, #f6f7fa);
    --accent: #ef4444;
    --c-posted: #10b981; --c-ready: #5eead4; --c-prod: #93c5fd; --c-missed: #f87171; --c-todo: #e5e7eb;
    max-width: 1180px; margin: 0 auto; padding: 8px 0 56px;
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; color: var(--ink);
}
body.dark-mode .cp2 { --c-todo: #334155; --c-ready: #2dd4bf; --c-prod: #60a5fa; }
.cp2 *, .cp2 *::before { box-sizing: border-box; }
.cp2 [hidden] { display: none !important; }

/* Header */
.cp2-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
.cp2-kicker { font-size: 12px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--accent); margin-bottom: 6px; }
.cp2-head h1 { margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -.5px; }
.cp2-head p { margin: 6px 0 0; font-size: 13.5px; color: var(--muted); }
.cp2-month { display: inline-flex; align-items: center; gap: 2px; padding: 4px; border-radius: 12px; background: var(--panel); border: 1px solid var(--line); }
.cp2-month a, .cp2-month span { display: inline-flex; align-items: center; justify-content: center; height: 34px; min-width: 34px; padding: 0 10px; border-radius: 9px; font-size: 13px; font-weight: 700; color: var(--soft); text-decoration: none; }
.cp2-month a:hover { background: var(--tint); color: var(--ink); }
.cp2-month span { min-width: 130px; color: var(--ink); }
.cp2-month a.is-today { font-size: 12px; color: var(--accent); }

/* Summary */
.cp2-summary { display: grid; grid-template-columns: 1.6fr 1fr; gap: 28px; align-items: center; padding: 24px 26px; margin-bottom: 18px; border-radius: 20px; background: var(--panel); border: 1px solid var(--line); }
.cp2-big { display: flex; align-items: baseline; gap: 10px; margin-bottom: 14px; }
.cp2-big b { font-size: 38px; font-weight: 800; letter-spacing: -1px; line-height: 1; }
.cp2-big span { font-size: 14px; color: var(--muted); font-weight: 600; }
.cp2-big em { margin-left: auto; font-style: normal; font-size: 13px; font-weight: 800; padding: 4px 10px; border-radius: 99px; background: rgba(16,185,129,0.12); color: #059669; }
.cp2-bar { display: flex; gap: 2px; height: 12px; border-radius: 99px; overflow: hidden; background: var(--c-todo); }
.cp2-bar span { height: 100%; }
.cp2-bar.is-sm { height: 8px; }
.b-posted { background: var(--c-posted); } .b-ready { background: var(--c-ready); } .b-prod { background: var(--c-prod); }
.b-missed { background: var(--c-missed); } .b-todo { background: var(--c-todo); }
.cp2-legend { display: flex; flex-wrap: wrap; gap: 6px 16px; margin-top: 12px; font-size: 12px; font-weight: 600; color: var(--soft); }
.cp2-legend span { display: inline-flex; align-items: center; gap: 7px; }
.cp2-legend i { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
.cp2-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; border-left: 1px solid var(--line); }
.cp2-kpi { padding: 4px 18px; }
.cp2-kpi + .cp2-kpi { border-left: 1px solid var(--line); }
.cp2-kpi b { display: block; font-size: 26px; font-weight: 800; letter-spacing: -.5px; }
.cp2-kpi span { font-size: 12px; font-weight: 600; color: var(--muted); }
.cp2-kpi.is-warn b { color: #d97706; } .cp2-kpi.is-bad b { color: #dc2626; }

/* Toolbar */
.cp2-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
.cp2-seg { display: inline-flex; padding: 4px; gap: 2px; border-radius: 12px; background: var(--tint); border: 1px solid var(--line); }
.cp2-seg button { height: 32px; padding: 0 14px; border: none; border-radius: 9px; cursor: pointer; background: transparent; font: inherit; font-size: 12.5px; font-weight: 700; color: var(--soft); }
.cp2-seg button b { margin-left: 4px; font-weight: 700; color: var(--muted); }
.cp2-seg button.is-active { background: var(--panel); color: var(--ink); box-shadow: 0 1px 3px rgba(15,23,42,0.08); }
.cp2-search { position: relative; width: 240px; max-width: 100%; }
.cp2-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--muted); }
.cp2-search input { width: 100%; height: 38px; padding: 0 12px 0 32px; border-radius: 11px; border: 1px solid var(--line); background: var(--panel); color: var(--ink); font: inherit; font-size: 13px; }
.cp2-search input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }

/* Client rows */
.cp2-list { border-radius: 18px; background: var(--panel); border: 1px solid var(--line); overflow: hidden; }
.cp2-cols, .cp2-row-main { display: grid; grid-template-columns: minmax(190px, 1.3fr) minmax(200px, 2fr) 96px minmax(170px, 1.3fr) 28px; gap: 18px; align-items: center; }
.cp2-cols { padding: 12px 20px; font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--line); background: var(--tint); }
.cp2-row { border-bottom: 1px solid var(--line); }
.cp2-row:last-child { border-bottom: none; }
.cp2-row-main { width: 100%; padding: 16px 20px; border: none; background: transparent; cursor: pointer; text-align: left; font: inherit; color: inherit; transition: background .15s; }
.cp2-row-main:hover, .cp2-row.is-open .cp2-row-main { background: var(--tint); }
.cp2-row-main:focus-visible { outline: 2px solid var(--accent); outline-offset: -2px; }
.cp2-client { display: flex; align-items: center; gap: 12px; min-width: 0; }
.cp2-logo { width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0; display: grid; place-items: center; overflow: hidden; font-size: 17px; font-weight: 800; color: #fff; }
.cp2-logo img { width: 100%; height: 100%; object-fit: cover; background: #fff; }
.cp2-client b { display: block; font-size: 14.5px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp2-status { display: inline-flex; align-items: center; gap: 6px; margin-top: 2px; font-size: 12px; font-weight: 700; }
.cp2-status::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.s-behind { color: #dc2626; } .s-needs_tasks { color: #d97706; } .s-on_track { color: #2563eb; } .s-complete { color: #059669; } .s-no_plan { color: var(--muted); }
body.dark-mode .s-behind { color: #f87171; } body.dark-mode .s-needs_tasks { color: #fbbf24; } body.dark-mode .s-on_track { color: #93c5fd; } body.dark-mode .s-complete { color: #34d399; }
.cp2-count { font-size: 15px; font-weight: 800; white-space: nowrap; }
.cp2-count small { font-size: 12px; font-weight: 600; color: var(--muted); }
.cp2-needs { display: flex; flex-wrap: wrap; gap: 6px; }
.cp2-tag { display: inline-flex; align-items: center; gap: 5px; height: 24px; padding: 0 9px; border-radius: 99px; font-size: 11.5px; font-weight: 700; background: var(--tint); color: var(--soft); white-space: nowrap; }
.cp2-tag.is-todo { background: rgba(217,119,6,0.1); color: #b45309; }
.cp2-tag.is-missed { background: rgba(220,38,38,0.09); color: #dc2626; }
.cp2-tag.is-ok { background: rgba(16,185,129,0.1); color: #047857; }
body.dark-mode .cp2-tag.is-todo { color: #fbbf24; } body.dark-mode .cp2-tag.is-missed { color: #f87171; } body.dark-mode .cp2-tag.is-ok { color: #34d399; }
.cp2-chev { color: var(--muted); font-size: 12px; transition: transform .2s; justify-self: end; }
.cp2-row.is-open .cp2-chev { transform: rotate(180deg); }

/* Expanded detail */
.cp2-detail { padding: 4px 20px 20px 70px; background: var(--tint); }
.cp2-carried { margin-bottom: 12px; font-size: 12px; font-weight: 600; color: #b45309; }
.cp2-types { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 10px; }
.cp2-type { padding: 12px 14px; border-radius: 14px; background: var(--panel); border: 1px solid var(--line); }
.cp2-type-top { display: flex; align-items: center; gap: 8px; margin-bottom: 9px; font-size: 13px; font-weight: 800; }
.cp2-type-top i { width: 24px; height: 24px; border-radius: 7px; display: grid; place-items: center; font-size: 11px; color: #fff; }
.cp2-type-top small { margin-left: auto; font-size: 12.5px; font-weight: 800; }
.cp2-type-top small span { color: var(--muted); font-weight: 600; }
.cp2-type-meta { display: flex; align-items: center; flex-wrap: wrap; gap: 4px 10px; margin-top: 9px; font-size: 11.5px; font-weight: 600; color: var(--muted); }
.cp2-type-meta .is-todo { color: #b45309; } .cp2-type-meta .is-missed { color: #dc2626; }
.cp2-create { margin-left: auto; display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px; border-radius: 8px; font-size: 11.5px; font-weight: 800; text-decoration: none; color: #fff; background: var(--accent); }
.cp2-create:hover { filter: brightness(1.08); }
.cp2-detail-foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-top: 12px; font-size: 12.5px; color: var(--soft); }
.cp2-detail-foot a { font-weight: 700; color: var(--accent); text-decoration: none; }
.cp2-detail-foot a:hover { text-decoration: underline; }

/* Clickable type tiles */
.cp2-type { position: relative; cursor: pointer; transition: border-color .15s, box-shadow .15s, transform .15s; }
.cp2-type:hover { border-color: rgba(239,68,68,0.45); box-shadow: 0 8px 20px -12px rgba(15,23,42,0.3); transform: translateY(-1px); }
.cp2-type:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
.cp2-type-open { display: inline-flex; align-items: center; gap: 5px; margin-top: 8px; font-size: 11.5px; font-weight: 700; color: var(--muted); }
.cp2-type:hover .cp2-type-open { color: var(--accent); }

/* Popup */
.cp2-overlay { --accent: #ef4444; position: fixed; inset: 0; z-index: 9995; display: none; align-items: center; justify-content: center; padding: 20px; background: rgba(15,23,42,0.5); backdrop-filter: blur(3px); }
.cp2-overlay.is-open { display: flex; animation: cp2Fade .15s ease; }
@keyframes cp2Fade { from { opacity: 0; } to { opacity: 1; } }
.cp2-pop { width: min(680px, 100%); max-height: calc(100vh - 40px); display: flex; flex-direction: column; border-radius: 20px; overflow: hidden;
    background: var(--card, #fff); color: var(--text, #0f172a); box-shadow: 0 30px 80px -20px rgba(15,23,42,0.5); font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; animation: cp2Rise .2s ease; }
@keyframes cp2Rise { from { transform: translateY(12px); opacity: .6; } to { transform: none; opacity: 1; } }
.cp2-pop-head { display: flex; align-items: center; gap: 14px; padding: 18px 20px; border-bottom: 1px solid var(--border, #e7eaf0); }
.cp2-pop-icon { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; color: #fff; font-size: 16px; flex-shrink: 0; }
.cp2-pop-head h2 { margin: 0; font-size: 17px; font-weight: 800; }
.cp2-pop-head p { margin: 2px 0 0; font-size: 12.5px; color: var(--text3, #94a3b8); }
.cp2-pop-close { margin-left: auto; width: 34px; height: 34px; flex-shrink: 0; border-radius: 10px; cursor: pointer; border: 1px solid var(--border, #e7eaf0); background: var(--card, #fff); color: var(--text2, #475569); }
.cp2-pop-close:hover { color: #ef4444; border-color: #ef4444; }
.cp2-pop-stats { display: flex; flex-wrap: wrap; gap: 6px 18px; padding: 12px 20px; font-size: 12.5px; color: var(--text3, #94a3b8); background: var(--card2, #f6f7fa); border-bottom: 1px solid var(--border, #e7eaf0); }
.cp2-pop-stats b { color: var(--text, #0f172a); font-size: 14px; margin-right: 2px; }
.cp2-pop-stats .is-bad b { color: #dc2626; } .cp2-pop-stats .is-warn b { color: #d97706; }
.cp2-pop-body { overflow-y: auto; padding: 12px 20px 20px; display: flex; flex-direction: column; gap: 8px; }
.cp2-item { display: flex; align-items: center; gap: 14px; padding: 10px 12px; border-radius: 14px; border: 1px solid var(--border, #e7eaf0); }
.cp2-item.is-slot { border-style: dashed; background: var(--card2, #f6f7fa); }
.cp2-item-date { width: 46px; flex-shrink: 0; text-align: center; padding: 5px 0; border-radius: 10px; background: var(--card2, #f6f7fa); line-height: 1.1; color: var(--text3, #94a3b8); }
.cp2-item-date span, .cp2-item-date small { display: block; font-size: 9.5px; font-weight: 800; text-transform: uppercase; }
.cp2-item-date b { display: block; font-size: 17px; font-weight: 800; color: var(--text, #0f172a); }
.cp2-item-date.is-today { background: #ef4444; color: rgba(255,255,255,0.85); }
.cp2-item-date.is-today b { color: #fff; }
.cp2-item.is-slot .cp2-item-date { height: 46px; display: grid; place-items: center; background: transparent; border: 1.5px dashed var(--border, #cbd5e1); }
.cp2-item-main { flex: 1; min-width: 0; }
.cp2-item-title { display: block; font-size: 13.5px; font-weight: 700; color: var(--text, #0f172a); text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
a.cp2-item-title:hover { color: #ef4444; }
.cp2-item.is-slot .cp2-item-title { color: var(--text3, #94a3b8); }
.cp2-item-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 10px; margin-top: 4px; font-size: 11.5px; color: var(--text3, #94a3b8); }
.cp2-ch { display: inline-flex; align-items: center; gap: 4px; height: 22px; padding: 0 7px; border-radius: 7px; font-size: 11px; text-decoration: none;
    color: var(--ch); background: color-mix(in srgb, var(--ch) 12%, transparent); }
.cp2-ch .fa-check { font-size: 9px; }
.cp2-ch.is-posted { background: var(--ch); color: #fff; }
a.cp2-ch:hover { filter: brightness(1.1); }
.cp2-state { display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 800; white-space: nowrap; }
.cp2-state i { font-size: 10px; }
.st-green { background: rgba(16,185,129,0.12); color: #047857; } .st-teal { background: rgba(20,184,166,0.12); color: #0f766e; }
.st-violet { background: rgba(139,92,246,0.12); color: #6d28d9; } .st-amber { background: rgba(245,158,11,0.14); color: #b45309; }
.st-blue { background: rgba(59,130,246,0.12); color: #1d4ed8; } .st-slate { background: rgba(100,116,139,0.12); color: #475569; }
.st-red { background: rgba(239,68,68,0.11); color: #dc2626; }
body.dark-mode .st-green { color: #34d399; } body.dark-mode .st-teal { color: #5eead4; } body.dark-mode .st-violet { color: #a78bfa; }
body.dark-mode .st-amber { color: #fbbf24; } body.dark-mode .st-blue { color: #93c5fd; } body.dark-mode .st-slate { color: #cbd5e1; } body.dark-mode .st-red { color: #f87171; }
@media (max-width: 640px) {
    .cp2-item { flex-wrap: wrap; }
    .cp2-state { margin-left: 60px; }
}

/* No plan group */
.cp2-unplanned { margin-top: 18px; padding: 18px 20px; border-radius: 18px; border: 1px dashed var(--line); background: var(--panel); }
.cp2-unplanned h3 { margin: 0 0 4px; font-size: 14px; font-weight: 800; }
.cp2-unplanned p { margin: 0 0 12px; font-size: 12.5px; color: var(--muted); }
.cp2-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.cp2-chip { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px 6px 6px; border-radius: 99px; background: var(--tint); font-size: 12.5px; font-weight: 700; }
.cp2-chip .cp2-logo { width: 24px; height: 24px; border-radius: 50%; font-size: 10px; }
.cp2-chip small { font-weight: 600; color: var(--muted); }
.cp2-empty { padding: 48px 20px; text-align: center; color: var(--muted); font-size: 13px; }
.cp2-empty i { display: block; margin: 0 auto 10px; font-size: 26px; opacity: .5; }

@media (max-width: 900px) {
    .cp2-summary { grid-template-columns: 1fr; gap: 18px; }
    .cp2-kpis { border-left: none; border-top: 1px solid var(--line); padding-top: 14px; }
    .cp2-kpi:first-child { padding-left: 0; }
    .cp2-cols { display: none; }
    .cp2-row-main { grid-template-columns: 1fr auto; gap: 10px 14px; }
    .cp2-row-main > .cp2-barcell { grid-column: 1 / -1; grid-row: 2; }
    .cp2-row-main > .cp2-needs { grid-column: 1 / -1; grid-row: 3; }
    .cp2-row-main > .cp2-chev { display: none; }
    .cp2-detail { padding: 4px 16px 16px; }
}
</style>

<div class="cp2">
    <header class="cp2-head">
        <div>
            <div class="cp2-kicker">Content plans</div>
            <h1>What each client gets this month</h1>
            <p>Monthly packages set by admin, and how much of each is created and posted.</p>
        </div>
        <nav class="cp2-month" aria-label="Month">
            <a href="{{ route('strategist.content-schedules', ['month' => $prev->month, 'year' => $prev->year]) }}" aria-label="Previous month"><i class="fas fa-chevron-left"></i></a>
            <span>{{ $monthStart->format('F Y') }}</span>
            <a href="{{ route('strategist.content-schedules', ['month' => $next->month, 'year' => $next->year]) }}" aria-label="Next month"><i class="fas fa-chevron-right"></i></a>
            @unless($isCurrentMonth)
                <a href="{{ route('strategist.content-schedules') }}" class="is-today">Today</a>
            @endunless
        </nav>
    </header>

    {{-- One summary: how far along the whole month is --}}
    <section class="cp2-summary" aria-label="Month summary">
        <div>
            <div class="cp2-big">
                <b>{{ $summary['posted'] }}</b>
                <span>of {{ $summary['planned'] }} planned posts are live</span>
                @if($summary['planned'] > 0)<em>{{ $summary['pct'] }}%</em>@endif
            </div>
            <div class="cp2-bar" role="img" aria-label="{{ $overall['posted'] }} posted, {{ $overall['ready'] }} ready, {{ $overall['prod'] }} in production, {{ $overall['missed'] }} missed, {{ $overall['todo'] }} not created yet">
                @foreach(['posted', 'ready', 'prod', 'missed', 'todo'] as $seg)
                    @if($overall[$seg] > 0)<span class="b-{{ $seg }}" style="width: {{ $pct($overall[$seg], $totalScale) }}%"></span>@endif
                @endforeach
            </div>
            <div class="cp2-legend">
                <span><i class="b-posted"></i> Posted</span>
                <span><i class="b-ready"></i> Ready to post</span>
                <span><i class="b-prod"></i> In production</span>
                <span><i class="b-missed"></i> Missed</span>
                <span><i class="b-todo"></i> Not created yet</span>
            </div>
        </div>
        <div class="cp2-kpis">
            <div class="cp2-kpi {{ $summary['toCreate'] ? 'is-warn' : '' }}"><b>{{ $summary['toCreate'] }}</b><span>Still to create</span></div>
            <div class="cp2-kpi {{ $summary['missed'] ? 'is-bad' : '' }}"><b>{{ $summary['missed'] }}</b><span>Missed</span></div>
            <div class="cp2-kpi"><b>{{ $overall['ready'] }}</b><span>Ready to post</span></div>
        </div>
    </section>

    @if($planned->isNotEmpty())
        <div class="cp2-toolbar">
            <div class="cp2-seg" role="tablist" aria-label="Filter">
                <button type="button" class="is-active" data-filter="all">All <b>{{ $planned->count() }}</b></button>
                <button type="button" data-filter="attention">Needs attention <b>{{ $attention }}</b></button>
                <button type="button" data-filter="on_track">On track <b>{{ $planned->where('status', 'on_track')->count() }}</b></button>
                <button type="button" data-filter="complete">Complete <b>{{ $planned->where('status', 'complete')->count() }}</b></button>
            </div>
            <label class="cp2-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" id="cp2Search" placeholder="Search clients" autocomplete="off" aria-label="Search clients">
            </label>
        </div>

        <div class="cp2-list">
            <div class="cp2-cols" aria-hidden="true">
                <span>Client</span><span>Progress</span><span>Posted</span><span>Needs</span><span></span>
            </div>

            @foreach($planned as $row)
                @php $scale = max($row->planned, $row->created, 1); @endphp
                <div class="cp2-row" data-status="{{ $row->status }}" data-name="{{ mb_strtolower($row->client->name) }}">
                    <button type="button" class="cp2-row-main" aria-expanded="false" aria-controls="cp2-detail-{{ $row->client->id }}">
                        <span class="cp2-client">
                            <span class="cp2-logo" style="background: {{ $row->client->color ?: $hue($row->client->name) }}">
                                @if($row->client->logo)<img src="{{ asset('storage/' . $row->client->logo) }}" alt="">@elseif($row->client->emoji){{ $row->client->emoji }}@else{{ $initials($row->client->name) }}@endif
                            </span>
                            <span style="min-width:0">
                                <b>{{ $row->client->name }}</b>
                                <span class="cp2-status s-{{ $row->status }}">{{ $statuses[$row->status]['label'] }}</span>
                            </span>
                        </span>
                        <span class="cp2-barcell">
                            <span class="cp2-bar is-sm">
                                @if($row->posted)<span class="b-posted" style="width: {{ $pct($row->posted, $scale) }}%"></span>@endif
                                @if($row->ready)<span class="b-ready" style="width: {{ $pct($row->ready, $scale) }}%"></span>@endif
                                @if($row->inProduction)<span class="b-prod" style="width: {{ $pct($row->inProduction, $scale) }}%"></span>@endif
                                @if($row->missed)<span class="b-missed" style="width: {{ $pct($row->missed, $scale) }}%"></span>@endif
                            </span>
                        </span>
                        <span class="cp2-count">{{ $row->posted }}<small> / {{ $row->planned }}</small></span>
                        <span class="cp2-needs">
                            @if($row->toCreate)<span class="cp2-tag is-todo">{{ $row->toCreate }} to create</span>@endif
                            @if($row->missed)<span class="cp2-tag is-missed">{{ $row->missed }} missed</span>@endif
                            @if(!$row->toCreate && !$row->missed)
                                <span class="cp2-tag is-ok"><i class="fas fa-check"></i> {{ $row->status === 'complete' ? 'Delivered' : 'All scheduled' }}</span>
                            @endif
                        </span>
                        <i class="fas fa-chevron-down cp2-chev" aria-hidden="true"></i>
                    </button>

                    <div class="cp2-detail" id="cp2-detail-{{ $row->client->id }}" hidden>
                        @if($row->isCarried)
                            <div class="cp2-carried"><i class="fas fa-rotate-right"></i> Carried over from the {{ $row->schedule->getMonthYearLabel() }} plan</div>
                        @endif
                        <div class="cp2-types">
                            @foreach($row->types as $type)
                                @php $tScale = max($type->planned, $type->created, 1); @endphp
                                @php $popupId = 'cp2-pop-' . $row->client->id . '-' . $type->key; @endphp
                                <div class="cp2-type" role="button" tabindex="0" data-popup="{{ $popupId }}"
                                     aria-label="See all {{ $type->meta['label'] }} for {{ $row->client->name }}">
                                    <div class="cp2-type-top">
                                        <i class="fas {{ $type->meta['icon'] }}" style="background: {{ $type->meta['color'] }}"></i>
                                        {{ $type->meta['label'] }}
                                        <small>{{ $type->posted }}<span> / {{ $type->planned ?: '–' }}</span></small>
                                    </div>
                                    <div class="cp2-bar is-sm">
                                        @if($type->posted)<span class="b-posted" style="width: {{ $pct($type->posted, $tScale) }}%"></span>@endif
                                        @if($type->ready)<span class="b-ready" style="width: {{ $pct($type->ready, $tScale) }}%"></span>@endif
                                        @if($type->inProduction)<span class="b-prod" style="width: {{ $pct($type->inProduction, $tScale) }}%"></span>@endif
                                        @if($type->missed)<span class="b-missed" style="width: {{ $pct($type->missed, $tScale) }}%"></span>@endif
                                    </div>
                                    <div class="cp2-type-meta">
                                        @if($type->planned === 0)
                                            <span>Not in plan · {{ $type->created }} created</span>
                                        @else
                                            <span>{{ $type->created }} created</span>
                                            @if($type->missed)<span class="is-missed">{{ $type->missed }} missed</span>@endif
                                            @if($type->toCreate)<span class="is-todo">{{ $type->toCreate }} to create</span>@endif
                                        @endif
                                        @if($type->toCreate && $type->meta['taskType'] && !$isPastMonth)
                                            <a class="cp2-create" href="{{ route('strategist.create-task', ['client_id' => $row->client->id, 'type' => $type->meta['taskType']]) }}"><i class="fas fa-plus"></i> Create</a>
                                        @endif
                                    </div>
                                    <span class="cp2-type-open">View all <i class="fas fa-arrow-right"></i></span>
                                </div>

                                {{-- Popup content: every piece of this type for this client in the month --}}
                                <template id="{{ $popupId }}">
                                    <div class="cp2-pop-head">
                                        <span class="cp2-pop-icon" style="background: {{ $type->meta['color'] }}"><i class="fas {{ $type->meta['icon'] }}"></i></span>
                                        <div>
                                            <h2>{{ $row->client->name }} · {{ $type->meta['label'] }}</h2>
                                            <p>{{ $monthStart->format('F Y') }} · {{ $type->planned ? $type->planned . ' planned' : 'not in plan' }} · {{ $type->created }} created</p>
                                        </div>
                                        <button type="button" class="cp2-pop-close" data-close aria-label="Close"><i class="fas fa-xmark"></i></button>
                                    </div>
                                    <div class="cp2-pop-stats">
                                        <span><b>{{ $type->posted }}</b> posted</span>
                                        <span><b>{{ $type->ready }}</b> ready</span>
                                        <span><b>{{ $type->inProduction }}</b> in production</span>
                                        <span class="{{ $type->missed ? 'is-bad' : '' }}"><b>{{ $type->missed }}</b> missed</span>
                                        <span class="{{ $type->toCreate ? 'is-warn' : '' }}"><b>{{ $type->toCreate }}</b> to create</span>
                                    </div>
                                    <div class="cp2-pop-body">
                                        @foreach($type->items as $item)
                                            @php
                                                $task = $item->task;
                                                $st = $itemStates[$item->state];
                                            @endphp
                                            <div class="cp2-item">
                                                <div class="cp2-item-date {{ $item->isToday ? 'is-today' : '' }}">
                                                    <span>{{ $item->planDate->format('M') }}</span>
                                                    <b>{{ $item->planDate->format('d') }}</b>
                                                    <small>{{ $item->planDate->format('D') }}</small>
                                                </div>
                                                <div class="cp2-item-main">
                                                    <a class="cp2-item-title" href="{{ route('strategist.tracking', ['task' => $task->id]) }}">{{ $task->title ?? 'Untitled' }}</a>
                                                    <div class="cp2-item-meta">
                                                        <span><i class="fas fa-user"></i> {{ $task->assignee->name ?? 'Unassigned' }}</span>
                                                        @foreach($item->channels as $channel)
                                                            @php $pi = $platformIcon($channel['platform']); @endphp
                                                            @if($channel['state'] === 'done' && $channel['postUrl'])
                                                                <a class="cp2-ch is-posted" href="{{ $channel['postUrl'] }}" target="_blank" rel="noopener" title="{{ ucfirst($channel['platform']) }}: posted, view post" style="--ch: {{ $pi[1] }}"><i class="{{ $pi[0] }}"></i> <i class="fas fa-check"></i></a>
                                                            @else
                                                                <span class="cp2-ch {{ $channel['state'] === 'done' ? 'is-posted' : '' }}" title="{{ ucfirst($channel['platform']) }}: {{ $itemStates[$channel['state']]['label'] }}" style="--ch: {{ $pi[1] }}"><i class="{{ $pi[0] }}"></i>@if($channel['state'] === 'done') <i class="fas fa-check"></i>@endif</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <span class="cp2-state st-{{ $st['tone'] }}"><i class="fas {{ $st['icon'] }}"></i> {{ $st['label'] }}</span>
                                            </div>
                                        @endforeach

                                        @for($i = 0; $i < $type->toCreate; $i++)
                                            <div class="cp2-item is-slot">
                                                <div class="cp2-item-date"><i class="fas fa-plus"></i></div>
                                                <div class="cp2-item-main">
                                                    <span class="cp2-item-title">Not created yet</span>
                                                    <div class="cp2-item-meta"><span>Planned {{ Str::singular(strtolower($type->meta['label'])) }} #{{ $type->created + $i + 1 }}</span></div>
                                                </div>
                                                @if($i === 0 && $type->meta['taskType'] && !$isPastMonth)
                                                    <a class="cp2-create" href="{{ route('strategist.create-task', ['client_id' => $row->client->id, 'type' => $type->meta['taskType']]) }}"><i class="fas fa-plus"></i> Create</a>
                                                @endif
                                            </div>
                                        @endfor

                                        @if($type->items->isEmpty() && !$type->toCreate)
                                            <div class="cp2-empty">Nothing scheduled.</div>
                                        @endif
                                    </div>
                                </template>
                            @endforeach
                        </div>
                        <div class="cp2-detail-foot">
                            <span>
                                @if($row->next)
                                    Next go-live <b>{{ $row->next->planDate->isToday() ? 'today' : $row->next->planDate->format('D, M j') }}</b> · {{ Str::limit($row->next->task->title ?? 'Untitled', 46) }}
                                @else
                                    Nothing else scheduled this month.
                                @endif
                            </span>
                            <a href="{{ route('strategist.calendar', ['client' => $row->client->id, 'view' => 'month', 'date' => $monthStart->toDateString()]) }}">Open in calendar <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="cp2-empty" id="cp2NoMatch" hidden><i class="fas fa-filter-circle-xmark"></i> No clients match.</div>
        </div>
    @else
        <div class="cp2-list">
            <div class="cp2-empty">
                <i class="fas fa-calendar-plus"></i>
                No content plans for {{ $monthStart->format('F Y') }} yet. Plans are set by an admin on each client's page.
            </div>
        </div>
    @endif

    @if($planned->isNotEmpty() && $unplanned->isNotEmpty())
        <section class="cp2-unplanned">
            <h3>No plan yet · {{ $unplanned->count() }} {{ Str::plural('client', $unplanned->count()) }}</h3>
            <p>Ask an admin to set their monthly package. Posts already scheduled are shown in brackets.</p>
            <div class="cp2-chips">
                @foreach($unplanned as $row)
                    <span class="cp2-chip">
                        <span class="cp2-logo" style="background: {{ $row->client->color ?: $hue($row->client->name) }}">
                            @if($row->client->logo)<img src="{{ asset('storage/' . $row->client->logo) }}" alt="">@else{{ $initials($row->client->name) }}@endif
                        </span>
                        {{ $row->client->name }} <small>({{ $row->created }})</small>
                    </span>
                @endforeach
            </div>
        </section>
    @endif
</div>

<div class="cp2-overlay" id="cp2Overlay" aria-hidden="true">
    <div class="cp2-pop" id="cp2Pop" role="dialog" aria-modal="true" aria-label="Content details"></div>
</div>

<script>
// Content type popup: click a Posts / Reels / Stories… tile to list every piece for that client
(function () {
    const overlay = document.getElementById('cp2Overlay');
    const pop = document.getElementById('cp2Pop');
    let lastFocus = null;

    function open(tile) {
        const template = document.getElementById(tile.dataset.popup);
        if (!template) return;
        lastFocus = tile;
        pop.replaceChildren(template.content.cloneNode(true));
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        pop.querySelector('[data-close]')?.focus();
    }
    function close() {
        if (!overlay.classList.contains('is-open')) return;
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        lastFocus?.focus?.();
    }

    document.querySelectorAll('[data-popup]').forEach(tile => {
        tile.addEventListener('click', (e) => {
            if (e.target.closest('a')) return; // the tile's own "Create" link still navigates
            open(tile);
        });
        tile.addEventListener('keydown', (e) => {
            if ((e.key === 'Enter' || e.key === ' ') && e.target === tile) { e.preventDefault(); open(tile); }
        });
    });
    overlay.addEventListener('click', (e) => { if (e.target === overlay || e.target.closest('[data-close]')) close(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
})();
</script>

<script>
(function () {
    const rows = Array.from(document.querySelectorAll('.cp2-row'));
    const search = document.getElementById('cp2Search');
    const noMatch = document.getElementById('cp2NoMatch');
    if (!rows.length) return;
    let filter = 'all';

    function render() {
        const q = (search?.value || '').trim().toLowerCase();
        let shown = 0;
        rows.forEach(row => {
            const s = row.dataset.status;
            const okFilter = filter === 'all' || (filter === 'attention' ? (s === 'behind' || s === 'needs_tasks') : s === filter);
            const visible = okFilter && (!q || row.dataset.name.includes(q));
            row.hidden = !visible;
            if (visible) shown++;
        });
        noMatch.hidden = shown > 0;
    }

    document.querySelectorAll('[data-filter]').forEach(btn => btn.addEventListener('click', () => {
        filter = btn.dataset.filter;
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.toggle('is-active', b === btn));
        render();
    }));
    search?.addEventListener('input', render);

    // Click a client to see its per-type breakdown
    rows.forEach(row => {
        const toggle = row.querySelector('.cp2-row-main');
        const detail = row.querySelector('.cp2-detail');
        toggle.addEventListener('click', () => {
            const open = detail.hidden;
            detail.hidden = !open;
            row.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    // Open the first client that needs attention so the page starts with something actionable
    const firstUrgent = rows.find(r => r.dataset.status === 'behind' || r.dataset.status === 'needs_tasks');
    firstUrgent?.querySelector('.cp2-row-main').click();

    render();
})();
</script>
@endsection
