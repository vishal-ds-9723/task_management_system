@extends('layouts.app')

@section('content')
@php
    $isAdmin = auth()->user()->role === 'admin';
    $taskUrl = fn ($task) => $isAdmin ? route('admin.tasks.show', $task) : route('strategist.tracking', ['task' => $task->id]);
    $url = fn (array $overrides = []) => route($boardRoute, array_filter(array_merge([
        'client' => $clientId,
        'mine' => $mine ? 1 : null,
    ], $overrides), fn ($v) => $v !== null && $v !== ''));

    $initials = fn (?string $name) => collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('') ?: '?';
    $hue = fn (?string $name) => 'hsl(' . (crc32((string) $name) % 360) . ', 58%, 50%)';
    $typeIcon = fn (?string $type) => [
        'reel' => 'fa-film', 'post' => 'fa-image', 'story' => 'fa-mobile-screen', 'video' => 'fa-video', 'carousel' => 'fa-images',
        'brochure' => 'fa-book-open', 'banner' => 'fa-rectangle-ad', 'flyer' => 'fa-file-image',
        'website' => 'fa-globe', 'software' => 'fa-laptop-code', 'maintenance' => 'fa-screwdriver-wrench',
    ][$type] ?? 'fa-shapes';
    $roleLabel = fn (?string $role) => ucfirst(str_replace('_', ' ', (string) $role));
    $roleGroup = fn (?string $role) => in_array($role, ['designer', 'developer'], true) ? $role : 'other';

    $statusMeta = [
        'available'  => ['label' => 'Available',  'tone' => 'green',  'icon' => 'fa-circle-check'],
        'working'    => ['label' => 'Working',    'tone' => 'blue',   'icon' => 'fa-bolt'],
        'queued'     => ['label' => 'Not started','tone' => 'slate',  'icon' => 'fa-hourglass-start'],
        'behind'     => ['label' => 'Behind',     'tone' => 'amber',  'icon' => 'fa-triangle-exclamation'],
        'overloaded' => ['label' => 'Overloaded', 'tone' => 'red',    'icon' => 'fa-fire'],
    ];
    $dueChip = function ($item) {
        if (!$item->due) return ['text' => 'No date', 'tone' => 'muted'];
        if ($item->isOverdue) return ['text' => abs($item->daysLeft) . 'd overdue', 'tone' => 'red'];
        return match (true) {
            $item->daysLeft === 0 => ['text' => 'Due today', 'tone' => 'amber'],
            $item->daysLeft === 1 => ['text' => 'Tomorrow', 'tone' => 'amber'],
            $item->daysLeft < 0 => ['text' => $item->due->format('M d'), 'tone' => 'muted'],
            default => ['text' => $item->due->format('M d'), 'tone' => 'muted'],
        };
    };
    $groupCounts = $people->countBy(fn ($p) => $roleGroup($p->kind));
@endphp

<style>
.tb-wrap {
    --tb-brand: #ef4444; --tb-brand-soft: rgba(239,68,68,0.09);
    --tb-shadow: 0 1px 2px rgba(15,23,42,0.04), 0 1px 3px rgba(15,23,42,0.06);
    --tb-shadow-md: 0 14px 30px -16px rgba(15,23,42,0.25);
    --g: #10b981; --g-bg: rgba(16,185,129,0.12); --g-fg: #047857;
    --b: #3b82f6; --b-bg: rgba(59,130,246,0.12); --b-fg: #1d4ed8;
    --a: #f59e0b; --a-bg: rgba(245,158,11,0.14); --a-fg: #b45309;
    --r: #ef4444; --r-bg: rgba(239,68,68,0.11); --r-fg: #dc2626;
    --s: #94a3b8; --s-bg: rgba(100,116,139,0.12); --s-fg: #475569;
    --v: #8b5cf6; --v-bg: rgba(139,92,246,0.12); --v-fg: #6d28d9;
    padding: 4px 0 48px; font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; color: var(--text, #0f172a);
}
body.dark-mode .tb-wrap { --g-fg: #34d399; --b-fg: #93c5fd; --a-fg: #fbbf24; --r-fg: #f87171; --s-fg: #cbd5e1; --v-fg: #a78bfa; --tb-shadow-md: 0 14px 30px -14px rgba(0,0,0,0.6); }
.tb-wrap *, .tb-wrap *::before { box-sizing: border-box; }
.tb-wrap [hidden] { display: none !important; }
.t-green { --t: var(--g); --t-bg: var(--g-bg); --t-fg: var(--g-fg); }
.t-blue  { --t: var(--b); --t-bg: var(--b-bg); --t-fg: var(--b-fg); }
.t-amber { --t: var(--a); --t-bg: var(--a-bg); --t-fg: var(--a-fg); }
.t-red   { --t: var(--r); --t-bg: var(--r-bg); --t-fg: var(--r-fg); }
.t-slate, .t-muted { --t: var(--s); --t-bg: var(--s-bg); --t-fg: var(--s-fg); }
.t-violet { --t: var(--v); --t-bg: var(--v-bg); --t-fg: var(--v-fg); }

/* Hero */
.tb-hero {
    position: relative; overflow: hidden; color: #fff; border-radius: 20px; padding: 26px 30px; margin-bottom: 16px;
    background: linear-gradient(135deg, #f43f3f 0%, #e02424 45%, #a51d1d 100%); box-shadow: 0 18px 40px -18px rgba(220,38,38,0.55);
    display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;
}
.tb-hero::before { content: ''; position: absolute; width: 340px; height: 340px; top: -170px; right: -50px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.18), transparent 70%); }
.tb-hero > * { position: relative; }
.tb-eyebrow { display: inline-flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 700; letter-spacing: .9px; text-transform: uppercase; padding: 5px 12px; border-radius: 99px; margin-bottom: 10px; background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); }
.tb-hero h1 { margin: 0 0 6px; font-size: 27px; font-weight: 800; letter-spacing: -.4px; }
.tb-hero p { margin: 0; font-size: 13.5px; color: rgba(255,255,255,0.85); }
.tb-hero-stats { display: flex; gap: 10px; flex-wrap: wrap; }
.tb-hero-stat { min-width: 96px; padding: 10px 14px; border-radius: 14px; background: rgba(255,255,255,0.13); border: 1px solid rgba(255,255,255,0.22); backdrop-filter: blur(8px); }
.tb-hero-stat b { display: block; font-size: 22px; font-weight: 800; line-height: 1.1; }
.tb-hero-stat span { font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.85); text-transform: uppercase; letter-spacing: .4px; }

/* Controls */
.tb-controls { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 10px; margin-bottom: 16px; border-radius: 16px; background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); box-shadow: var(--tb-shadow); }
.tb-tabs { display: inline-flex; padding: 4px; gap: 4px; border-radius: 12px; background: var(--card2, #f5f7fb); border: 1px solid var(--border, #e2e6ef); flex-wrap: wrap; }
.tb-tabs button { display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 12px; border: none; border-radius: 9px; cursor: pointer; font: inherit; font-size: 12.5px; font-weight: 700; background: transparent; color: var(--text2, #475569); }
.tb-tabs button span { font-size: 10.5px; padding: 1px 7px; border-radius: 99px; background: var(--border, #e2e6ef); }
.tb-tabs button.is-active { background: var(--card, #fff); color: var(--tb-brand); box-shadow: var(--tb-shadow); }
.tb-field { height: 40px; border-radius: 11px; border: 1px solid var(--border, #e2e6ef); background: var(--card2, #f5f7fb); color: var(--text, #0f172a); font: inherit; font-size: 13px; padding: 0 12px; }
.tb-field:focus { outline: none; border-color: var(--tb-brand); box-shadow: 0 0 0 3px var(--tb-brand-soft); }
select.tb-field { padding-right: 30px; cursor: pointer; }
.tb-search { position: relative; flex: 1 1 220px; min-width: 180px; }
.tb-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text3, #94a3b8); font-size: 13px; }
.tb-search .tb-field { width: 100%; padding-left: 36px; }
.tb-toggle { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 14px; border-radius: 11px; text-decoration: none; border: 1px solid var(--border, #e2e6ef); background: var(--card2, #f5f7fb); color: var(--text2, #475569); font-size: 12.5px; font-weight: 700; }
.tb-toggle .knob { width: 30px; height: 18px; border-radius: 99px; background: #cbd5e1; position: relative; }
.tb-toggle .knob::after { content: ''; position: absolute; top: 2px; left: 2px; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform .2s; }
.tb-toggle.is-on { color: var(--tb-brand); border-color: rgba(239,68,68,0.35); }
.tb-toggle.is-on .knob { background: var(--tb-brand); }
.tb-toggle.is-on .knob::after { transform: translateX(12px); }

/* Unassigned */
.tb-orphans { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding: 12px 16px; margin-bottom: 16px; border-radius: 16px; background: var(--a-bg); border: 1px solid rgba(245,158,11,0.35); }
.tb-orphans-title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: var(--a-fg); }
.tb-orphans a, .tb-orphan-item { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 9px; font-size: 12px; font-weight: 700; text-decoration: none; background: var(--card, #fff); color: var(--text, #0f172a); border: 1px solid rgba(245,158,11,0.35); cursor: pointer; transition: all .15s; }
.tb-orphans a:hover, .tb-orphan-item:hover { border-color: var(--tb-brand); transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,0.08); }

/* Lanes */
.tb-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px; align-items: start; }
.tb-lane { border-radius: 18px; background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); box-shadow: var(--tb-shadow); overflow: hidden; transition: box-shadow .2s, transform .2s; }
.tb-lane:hover { box-shadow: var(--tb-shadow-md); transform: translateY(-2px); }
.tb-lane-head { display: flex; align-items: center; gap: 12px; padding: 14px 16px 10px; }
.tb-avatar { position: relative; width: 42px; height: 42px; border-radius: 50%; display: grid; place-items: center; font-size: 14px; font-weight: 800; color: #fff; flex-shrink: 0; }
.tb-avatar.is-online::after { content: ''; position: absolute; right: 0; bottom: 0; width: 12px; height: 12px; border-radius: 50%; background: #22c55e; border: 2px solid var(--card, #fff); }
.tb-person { flex: 1; min-width: 0; }
.tb-person b { display: block; font-size: 15px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tb-person span { font-size: 11.5px; font-weight: 600; color: var(--text3, #94a3b8); }
.tb-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 99px; background: var(--t-bg); color: var(--t-fg); white-space: nowrap; }
.tb-pill i { font-size: 10px; }
.tb-load { padding: 0 16px 12px; }
.tb-load-top { display: flex; justify-content: space-between; font-size: 11.5px; font-weight: 700; color: var(--text2, #475569); margin-bottom: 5px; }
.tb-load-top small { color: var(--text3, #94a3b8); font-weight: 600; }
.tb-bar { height: 7px; border-radius: 99px; background: var(--card2, #eef1f6); overflow: hidden; }
.tb-bar span { display: block; height: 100%; border-radius: inherit; background: var(--t); transition: width .3s; }
.tb-meta { display: flex; flex-wrap: wrap; gap: 6px 12px; padding: 0 16px 12px; font-size: 11.5px; font-weight: 600; color: var(--text3, #94a3b8); }
.tb-meta i { margin-right: 4px; }

.tb-section { padding: 10px 16px 12px; border-top: 1px solid var(--border, #eef1f6); }
.tb-section-title { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--text3, #94a3b8); }
.tb-section-title.is-now { color: var(--b-fg); }
.tb-live { width: 8px; height: 8px; border-radius: 50%; background: var(--b); display: inline-block; margin-right: 6px; box-shadow: 0 0 0 0 rgba(59,130,246,0.5); animation: tbPulse 1.8s infinite; }
@keyframes tbPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(59,130,246,0.45); } 50% { box-shadow: 0 0 0 5px rgba(59,130,246,0); } }

/* Task Card (Interactive) */
.tb-task {
    display: flex; align-items: center; gap: 10px; padding: 8px 10px; margin-bottom: 6px; border-radius: 11px;
    text-decoration: none; color: inherit; background: var(--card2, #f8fafc); border: 1px solid transparent;
    transition: all .16s cubic-bezier(0.16, 1, 0.3, 1); cursor: pointer; position: relative; user-select: none;
}
.tb-task:last-child { margin-bottom: 0; }
.tb-task:hover { border-color: var(--tb-brand); background: var(--card, #fff); box-shadow: 0 4px 12px rgba(15,23,42,0.06); transform: translateY(-1px); }
.tb-task:focus-visible { outline: none; border-color: var(--tb-brand); box-shadow: 0 0 0 3px var(--tb-brand-soft); }
.tb-task.is-now { background: var(--b-bg); }
.tb-task.is-now:hover { background: var(--card, #fff); border-color: var(--b); }
.tb-task-waiting { background: rgba(139,92,246,0.06); }
.tb-task-waiting:hover { border-color: var(--v); }
.tb-task-hold { background: rgba(100,116,139,0.07); }
.tb-task-hold:hover { border-color: var(--s); }

.tb-task-num { width: 22px; height: 22px; border-radius: 7px; display: grid; place-items: center; font-size: 11px; font-weight: 800; flex-shrink: 0; background: var(--card, #fff); color: var(--text2, #475569); border: 1px solid var(--border, #e2e6ef); }
.tb-task-icon { width: 26px; height: 26px; border-radius: 8px; display: grid; place-items: center; font-size: 11px; flex-shrink: 0; background: var(--card, #fff); color: var(--b-fg); }
.tb-task-main { flex: 1; min-width: 0; }
.tb-task-main b { display: block; font-size: 12.5px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tb-task-main small { display: block; font-size: 11px; color: var(--text3, #94a3b8); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tb-due { font-size: 10.5px; font-weight: 800; padding: 3px 8px; border-radius: 99px; white-space: nowrap; background: var(--t-bg); color: var(--t-fg); flex-shrink: 0; }
.tb-prio { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.tb-prio.p-urgent { background: #dc2626; box-shadow: 0 0 0 2px rgba(220,38,38,0.25); }
.tb-prio.p-high { background: #f97316; }
.tb-prio.p-normal, .tb-prio.p-medium { background: #3b82f6; }
.tb-prio.p-low { background: #94a3b8; }

.tb-task-ext-btn {
    display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px;
    color: var(--text3, #94a3b8); opacity: 0; transition: all .15s ease; text-decoration: none; font-size: 10.5px; flex-shrink: 0;
}
.tb-task:hover .tb-task-ext-btn { opacity: 0.85; }
.tb-task-ext-btn:hover { opacity: 1 !important; background: var(--tb-brand-soft); color: var(--tb-brand); transform: scale(1.1); }

.tb-empty-line { font-size: 12px; color: var(--text3, #94a3b8); padding: 4px 2px; }
.tb-more { width: 100%; margin-top: 6px; padding: 6px; border: 1px dashed var(--border, #e2e6ef); border-radius: 9px; cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; background: transparent; color: var(--tb-brand); transition: background .15s; }
.tb-more:hover { background: var(--tb-brand-soft); }

/* Collapsible Trays (Waiting for Review & On Hold) */
.tb-foot { display: flex; flex-wrap: wrap; gap: 6px; padding: 10px 16px 14px; border-top: 1px solid var(--border, #eef1f6); }
.tb-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 99px; background: var(--t-bg); color: var(--t-fg); border: 1px solid transparent; }
.tb-chip-btn { cursor: pointer; transition: all .15s ease; }
.tb-chip-btn:hover { border-color: currentColor; transform: translateY(-1px); }
.tb-chevron { transition: transform .2s ease; }
.tb-chevron.is-open { transform: rotate(180deg); }

.tb-tray { padding: 8px 14px 12px; background: var(--card2, #f8fafc); border-top: 1px solid var(--border, #eef1f6); border-bottom: 1px solid var(--border, #eef1f6); animation: tbFadeIn .2s ease; }
.tb-tray-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; color: var(--text2, #475569); }
@keyframes tbFadeIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

.tb-empty { grid-column: 1 / -1; padding: 50px 20px; text-align: center; border-radius: 18px; border: 1px dashed var(--border, #e2e6ef); color: var(--text3, #94a3b8); background: var(--card, #fff); }

/* ── Slide-Over Task Inspector Drawer ─────────────────────────── */
.tb-drawer-overlay {
    position: fixed; inset: 0; z-index: 9998; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(5px);
    opacity: 0; visibility: hidden; transition: opacity .25s ease, visibility .25s ease;
}
.tb-drawer-overlay.is-open { opacity: 1; visibility: visible; }

.tb-drawer {
    position: fixed; top: 0; right: 0; bottom: 0; z-index: 9999; width: min(640px, 100vw);
    background: var(--card, #fff); color: var(--text, #0f172a); border-left: 1px solid var(--border, #e2e8f0);
    box-shadow: -20px 0 60px rgba(15, 23, 42, 0.25); transform: translateX(105%);
    transition: transform .28s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column;
}
.tb-drawer.is-open { transform: translateX(0); }

.tb-drawer-header {
    padding: 20px 24px; border-bottom: 1px solid var(--border, #e2e8f0); display: flex; justify-content: space-between;
    align-items: flex-start; gap: 16px; background: linear-gradient(180deg, rgba(239,68,68,0.04), transparent);
}
.tb-drawer-client { font-size: 12.5px; font-weight: 700; color: var(--tb-brand); margin-bottom: 6px; display: flex; align-items: center; gap: 7px; }
.tb-drawer-title { font-size: 18px; font-weight: 800; color: var(--text, #0f172a); margin: 0 0 10px; line-height: 1.35; }
.tb-drawer-badges { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.tb-drawer-close {
    width: 34px; height: 34px; border-radius: 9px; border: 1px solid var(--border, #e2e8f0); background: var(--card, #fff);
    color: var(--text2, #475569); cursor: pointer; font-size: 15px; display: flex; align-items: center; justify-content: center;
    transition: all .16s; flex-shrink: 0;
}
.tb-drawer-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg); }

.tb-drawer-subhead {
    display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 24px;
    background: var(--card2, #f8fafc); border-bottom: 1px solid var(--border, #e2e8f0); font-size: 12px; flex-wrap: wrap;
}
.tb-sub-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;
    cursor: pointer; border: 1px solid var(--border, #e2e8f0); background: var(--card, #fff); color: var(--text2, #475569);
    text-decoration: none; transition: all .15s;
}
.tb-sub-btn:hover { background: var(--card2, #f1f5f9); border-color: var(--text3, #94a3b8); color: var(--text, #0f172a); }
.tb-sub-btn-primary { background: var(--tb-brand); color: #fff; border-color: var(--tb-brand); }
.tb-sub-btn-primary:hover { background: #dc2626; color: #fff; border-color: #dc2626; }

.tb-drawer-body {
    flex: 1; overflow-y: auto; padding: 20px 24px; display: flex; flex-direction: column; gap: 16px;
}
.tb-dcard {
    background: var(--card2, #f8fafc); border: 1px solid var(--border, #e2e8f0); border-radius: 14px; padding: 16px;
    display: flex; flex-direction: column; gap: 10px;
}
.tb-dcard-title {
    font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--text3, #94a3b8);
    display: flex; align-items: center; gap: 7px;
}
.tb-dcard-revision { background: rgba(245,158,11,0.06); border-color: rgba(245,158,11,0.35); }
.tb-d-personnel-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
.tb-d-user-info { display: flex; align-items: center; gap: 10px; }
.tb-d-avatar { width: 38px; height: 38px; border-radius: 50%; display: grid; place-items: center; font-size: 13px; font-weight: 800; color: #fff; flex-shrink: 0; }
.tb-d-user-name { font-size: 13.5px; font-weight: 800; color: var(--text, #0f172a); }
.tb-d-user-role { font-size: 11px; color: var(--text3, #94a3b8); font-weight: 600; }
.tb-d-timer-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 10px; background: var(--card, #fff); border: 1px solid var(--border, #e2e8f0); font-size: 12px; font-weight: 700; color: var(--text2, #475569); }
.tb-live-dot { width: 7px; height: 7px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 0 rgba(16,185,129,0.7); animation: tbDotPulse 1.8s infinite; }
@keyframes tbDotPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.6); } 50% { box-shadow: 0 0 0 5px rgba(16,185,129,0); } }

.tb-d-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; padding-top: 10px; border-top: 1px solid var(--border, #e2e8f0); font-size: 11.5px; color: var(--text2, #475569); }
.tb-d-meta-grid strong { color: var(--text3, #94a3b8); display: block; font-size: 10px; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }

.tb-d-revision-note { font-size: 12.5px; color: #92400e; line-height: 1.45; background: var(--card, #fff); padding: 10px 12px; border-radius: 8px; border: 1px solid rgba(245,158,11,0.3); }
.tb-d-revision-meta { font-size: 11px; color: #b45309; display: flex; justify-content: space-between; }

.tb-d-brief-box { font-size: 13px; color: var(--text, #0f172a); line-height: 1.55; background: var(--card, #fff); padding: 12px 14px; border-radius: 10px; border: 1px solid var(--border, #e2e8f0); max-height: 170px; overflow-y: auto; white-space: pre-wrap; word-break: break-word; }
.tb-copy-block { font-size: 12.5px; color: var(--text, #0f172a); line-height: 1.5; background: var(--card, #fff); padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border, #e2e8f0); white-space: pre-wrap; max-height: 140px; overflow-y: auto; }

/* Deliverables Grid */
.tb-d-media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; }
.tb-d-media-card { position: relative; border-radius: 10px; overflow: hidden; border: 1px solid var(--border, #e2e8f0); background: var(--card, #fff); aspect-ratio: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: transform .15s, box-shadow .15s; }
.tb-d-media-card:hover { transform: scale(1.03); box-shadow: 0 6px 16px rgba(0,0,0,0.12); border-color: var(--tb-brand); }
.tb-d-media-card img { width: 100%; height: 100%; object-fit: cover; }
.tb-d-media-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, transparent 40%, rgba(15,23,42,0.8)); opacity: 0; transition: opacity .15s; display: flex; align-items: flex-end; justify-content: space-between; padding: 8px; color: #fff; font-size: 11px; }
.tb-d-media-card:hover .tb-d-media-overlay { opacity: 1; }
.tb-d-badge { font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 6px; background: rgba(0,0,0,0.6); color: #fff; backdrop-filter: blur(4px); }
.tb-d-empty { font-size: 12px; color: var(--text3, #94a3b8); grid-column: 1 / -1; padding: 14px; text-align: center; background: var(--card, #fff); border-radius: 8px; border: 1px dashed var(--border, #e2e8f0); }

/* Comments Feed */
.tb-d-comments-feed { display: flex; flex-direction: column; gap: 10px; max-height: 220px; overflow-y: auto; padding-right: 4px; }
.tb-d-comment-item { display: flex; gap: 10px; font-size: 12px; background: var(--card, #fff); padding: 9px 12px; border-radius: 10px; border: 1px solid var(--border, #e2e8f0); }
.tb-d-comment-avatar { width: 26px; height: 26px; border-radius: 50%; display: grid; place-items: center; font-size: 10.5px; font-weight: 800; color: #fff; flex-shrink: 0; }
.tb-d-comment-main { flex: 1; min-width: 0; }
.tb-d-comment-header { display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: var(--text, #0f172a); margin-bottom: 3px; }
.tb-d-comment-time { color: var(--text3, #94a3b8); font-weight: 500; font-size: 10.5px; }
.tb-d-comment-body { color: var(--text2, #475569); line-height: 1.4; word-break: break-word; }
.tb-d-comment-form { display: flex; gap: 8px; margin-top: 4px; }
.tb-d-comment-form input { flex: 1; padding: 9px 12px; border-radius: 9px; border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text, #0f172a); font-size: 12.5px; font-family: inherit; }
.tb-d-comment-form input:focus { outline: none; border-color: var(--tb-brand); box-shadow: 0 0 0 3px var(--tb-brand-soft); }
.tb-d-comment-form button { padding: 9px 14px; border-radius: 9px; border: none; background: var(--tb-brand); color: #fff; cursor: pointer; font-size: 13px; transition: background .15s; }
.tb-d-comment-form button:hover { background: #dc2626; }

.tb-drawer-footer {
    padding: 16px 24px; border-top: 1px solid var(--border, #e2e8f0); background: var(--card, #fff);
    display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
}
.tb-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; border-radius: 10px; font-size: 12.5px; font-weight: 700; cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: all .16s; }
.tb-btn-ghost { background: var(--card2, #f8fafc); border-color: var(--border, #e2e8f0); color: var(--text2, #475569); }
.tb-btn-ghost:hover { background: var(--card, #fff); color: var(--text, #0f172a); border-color: var(--text3, #94a3b8); }
.tb-btn-primary { background: var(--tb-brand); color: #fff; border-color: var(--tb-brand); box-shadow: 0 4px 12px rgba(239,68,68,0.25); }
.tb-btn-primary:hover { background: #dc2626; color: #fff; }

/* Priority pill helper */
.tb-prio-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 10.5px; font-weight: 800; padding: 3px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: .4px; }
.tb-prio-badge.p-urgent { background: rgba(220,38,38,0.12); color: #dc2626; border: 1px solid rgba(220,38,38,0.3); }
.tb-prio-badge.p-high { background: rgba(249,115,22,0.12); color: #ea580c; border: 1px solid rgba(249,115,22,0.3); }
.tb-prio-badge.p-normal, .tb-prio-badge.p-medium { background: rgba(59,130,246,0.12); color: #2563eb; border: 1px solid rgba(59,130,246,0.25); }
.tb-prio-badge.p-low { background: rgba(148,163,184,0.12); color: #475569; border: 1px solid rgba(148,163,184,0.25); }
.tb-type-tag { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 99px; background: var(--card2, #f1f5f9); border: 1px solid var(--border, #e2e8f0); color: var(--text2, #475569); }
.tb-platform-chip { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 6px; background: var(--card2, #f1f5f9); border: 1px solid var(--border, #e2e8f0); color: var(--text2, #475569); text-transform: capitalize; }

/* Toast Notification */
.tb-toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 10005; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }
.tb-toast { pointer-events: auto; background: #0f172a; color: #fff; padding: 11px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 9px; box-shadow: 0 12px 30px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1); animation: tbToastIn .2s cubic-bezier(0.16,1,0.3,1); }
.tb-toast-success i { color: #10b981; }
.tb-toast-error i { color: #ef4444; }
@keyframes tbToastIn { from { opacity: 0; transform: translateY(12px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }

/* Lightbox */
.tb-lightbox { position: fixed; inset: 0; z-index: 10010; background: rgba(0,0,0,0.85); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; padding: 30px; animation: tbFadeIn .2s ease; }
.tb-lightbox img { max-width: 90vw; max-height: 85vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0,0,0,0.5); object-fit: contain; }
.tb-lightbox-close { position: absolute; top: 20px; right: 24px; width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.15); border: none; color: #fff; font-size: 24px; cursor: pointer; display: grid; place-items: center; transition: background .15s; }
.tb-lightbox-close:hover { background: #ef4444; }

@media (max-width: 768px) {
    .tb-hero { padding: 22px 20px; }
    .tb-hero h1 { font-size: 22px; }
    .tb-grid { grid-template-columns: 1fr; }
    .tb-drawer { width: 100vw; }
}
@media (prefers-reduced-motion: reduce) { .tb-live { animation: none; } .tb-lane { transition: none; } .tb-drawer { transition: none; } }
</style>

<div class="tb-wrap">
    <section class="tb-hero">
        <div>
            <div class="tb-eyebrow"><i class="fas fa-people-group"></i> Team Board</div>
            <h1>Who's doing what</h1>
            <p>What each person is working on right now, and what they pick up next.</p>
        </div>
        <div class="tb-hero-stats">
            <div class="tb-hero-stat"><b>{{ $summary['workingNow'] }}</b><span>Working now</span></div>
            <div class="tb-hero-stat"><b>{{ $summary['idle'] }}</b><span>Free</span></div>
            <div class="tb-hero-stat"><b>{{ $summary['overloaded'] }}</b><span>Overloaded</span></div>
            <div class="tb-hero-stat"><b>{{ $summary['overdue'] }}</b><span>Overdue tasks</span></div>
        </div>
    </section>

    <div class="tb-controls">
        <div class="tb-tabs" role="tablist" aria-label="Role">
            <button type="button" class="is-active" data-role-tab="all">Everyone <span>{{ $people->count() }}</span></button>
            <button type="button" data-role-tab="designer"><i class="fas fa-pen-nib"></i> Designers <span>{{ $groupCounts['designer'] ?? 0 }}</span></button>
            <button type="button" data-role-tab="developer"><i class="fas fa-code"></i> Developers <span>{{ $groupCounts['developer'] ?? 0 }}</span></button>
            @if(($groupCounts['other'] ?? 0) > 0)
                <button type="button" data-role-tab="other"><i class="fas fa-user"></i> Others <span>{{ $groupCounts['other'] }}</span></button>
            @endif
        </div>
        <label class="tb-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="search" class="tb-field" id="tbSearch" placeholder="Find a person or task…" autocomplete="off" aria-label="Search people and tasks">
        </label>
        <select class="tb-field" id="tbSort" aria-label="Sort people">
            <option value="load">Sort: Busiest first</option>
            <option value="free">Sort: Most available first</option>
            <option value="overdue">Sort: Most overdue first</option>
            <option value="name">Sort: Name A–Z</option>
        </select>
        <select class="tb-field" aria-label="Client" onchange="window.location.href = this.value">
            <option value="{{ $url(['client' => null]) }}">All clients</option>
            @foreach($clients as $client)
                <option value="{{ $url(['client' => $client->id]) }}" @selected($clientId === $client->id)>{{ $client->name }}</option>
            @endforeach
        </select>
        <a href="{{ $url(['mine' => $mine ? null : 1]) }}" class="tb-toggle {{ $mine ? 'is-on' : '' }}" role="switch" aria-checked="{{ $mine ? 'true' : 'false' }}">
            <span class="knob"></span> Only tasks I created
        </a>
    </div>

    @if($unassigned->isNotEmpty())
        <div class="tb-orphans">
            <div class="tb-orphans-title"><i class="fas fa-user-slash"></i> {{ $unassigned->count() }} {{ Str::plural('task', $unassigned->count()) }} with no one assigned</div>
            @foreach($unassigned->take(6) as $item)
                @php $chip = $dueChip($item); @endphp
                <a href="{{ $taskUrl($item->task) }}" class="tb-orphan-item"
                   onclick="openTeamBoardInspector(event, {{ $item->task->id }}, this)"
                   data-task-id="{{ $item->task->id }}"
                   data-inspect-url="{{ route($isAdmin ? 'admin.tasks.quick-inspect' : 'strategist.tasks.quick-inspect', $item->task) }}"
                   data-comment-url="{{ route($isAdmin ? 'admin.tasks.comment' : 'strategist.tasks.comment', $item->task) }}"
                   data-full-url="{{ $taskUrl($item->task) }}"
                   data-title="{{ e($item->task->title ?? 'Untitled') }}"
                   data-client="{{ e($item->task->client->name ?? 'No client') }}"
                   data-type="{{ e(ucfirst($item->task->type ?? 'Task')) }}"
                   data-type-icon="{{ $typeIcon($item->task->type) }}"
                   data-priority="{{ e(strtolower($item->task->priority ?? 'normal')) }}"
                   data-due-text="{{ e($chip['text']) }}"
                   data-due-tone="{{ e($chip['tone']) }}"
                   data-assignee="Unassigned"
                   data-assignee-role="Not assigned"
                   data-assignee-avatar="#94a3b8"
                   data-assignee-initial="?"
                   data-status="{{ e($item->task->status) }}"
                   title="Click to view task details in drawer">
                    <i class="fas {{ $typeIcon($item->task->type) }}"></i> {{ Str::limit($item->task->title ?? 'Untitled', 34) }}
                </a>
            @endforeach
            @if($unassigned->count() > 6)
                <span class="tb-orphans-title" style="font-weight:700">+{{ $unassigned->count() - 6 }} more</span>
            @endif
        </div>
    @endif

    <div class="tb-grid" id="tbGrid">
        @forelse($people as $person)
            @php
                $st = $statusMeta[$person->status];
                $search = mb_strtolower(implode(' ', array_merge(
                    [$person->user->name, $person->kind],
                    $person->now->concat($person->next)->concat($person->waiting)->map(fn ($i) => ($i->task->title ?? '') . ' ' . ($i->task->client->name ?? ''))->all()
                )));
            @endphp
            <article class="tb-lane" data-group="{{ $roleGroup($person->kind) }}" data-search="{{ $search }}"
                     data-load="{{ $person->active }}" data-overdue="{{ $person->overdue }}" data-name="{{ mb_strtolower($person->user->name) }}">
                <div class="tb-lane-head">
                    <span class="tb-avatar {{ $person->online ? 'is-online' : '' }}" style="background: {{ $person->user->avatar_color ?: $hue($person->user->name) }}"
                          title="{{ $person->online ? 'Online now' : ($person->user->last_active_at ? 'Last active ' . $person->user->last_active_at->diffForHumans() : 'Offline') }}">
                        {{ $initials($person->user->name) }}
                    </span>
                    <div class="tb-person">
                        <b>{{ $person->user->name }}</b>
                        <span>{{ $roleLabel($person->kind) }} · {{ $person->online ? 'Online' : ($person->user->last_active_at ? 'Active ' . $person->user->last_active_at->diffForHumans() : 'Offline') }}</span>
                    </div>
                    <span class="tb-pill t-{{ $st['tone'] }}"><i class="fas {{ $st['icon'] }}"></i> {{ $st['label'] }}</span>
                </div>

                <div class="tb-load t-{{ $st['tone'] }}">
                    <div class="tb-load-top">
                        <span>{{ $person->active }} active {{ Str::plural('task', $person->active) }}</span>
                        <small>{{ $person->load }}% of capacity ({{ $capacity }})</small>
                    </div>
                    <div class="tb-bar"><span style="width: {{ max($person->load, $person->active ? 4 : 0) }}%"></span></div>
                </div>

                <div class="tb-meta">
                    <span><i class="fas fa-circle-check" style="color:var(--g)"></i>{{ $person->doneThisWeek }} done this week</span>
                    @if($person->hoursToday !== null)
                        <span><i class="fas fa-stopwatch"></i>{{ $person->hoursToday }}h logged today</span>
                    @endif
                    @if($person->freeOn)
                        <span title="Latest due date in their queue"><i class="fas fa-calendar-check"></i>Queue runs to {{ $person->freeOn->format('M d') }}</span>
                    @endif
                </div>

                {{-- NOW (Working on now) --}}
                <div class="tb-section">
                    <div class="tb-section-title {{ $person->now->isNotEmpty() ? 'is-now' : '' }}">
                        <span>@if($person->now->isNotEmpty())<span class="tb-live"></span>@endif Working on now</span>
                        @if($person->now->count() > 1)<span>{{ $person->now->count() }} in progress</span>@endif
                    </div>
                    @forelse($person->now as $nIndex => $item)
                        @php $chip = $dueChip($item); @endphp
                        <div class="tb-task is-now" role="button" tabindex="0"
                             onclick="openTeamBoardInspector(event, {{ $item->task->id }}, this)"
                             @if($nIndex >= 2) data-extra-now hidden @endif
                             data-task-id="{{ $item->task->id }}"
                             data-inspect-url="{{ route($isAdmin ? 'admin.tasks.quick-inspect' : 'strategist.tasks.quick-inspect', $item->task) }}"
                             data-comment-url="{{ route($isAdmin ? 'admin.tasks.comment' : 'strategist.tasks.comment', $item->task) }}"
                             data-full-url="{{ $taskUrl($item->task) }}"
                             data-title="{{ e($item->task->title ?? 'Untitled') }}"
                             data-client="{{ e($item->task->client->name ?? 'No client') }}"
                             data-type="{{ e(ucfirst($item->task->type ?? 'Task')) }}"
                             data-type-icon="{{ $typeIcon($item->task->type) }}"
                             data-priority="{{ e(strtolower($item->task->priority ?? 'normal')) }}"
                             data-due-text="{{ e($chip['text']) }}"
                             data-due-tone="{{ e($chip['tone']) }}"
                             data-assignee="{{ e($person->user->name) }}"
                             data-assignee-role="{{ e($roleLabel($person->kind)) }}"
                             data-assignee-avatar="{{ e($person->user->avatar_color ?: $hue($person->user->name)) }}"
                             data-assignee-initial="{{ e($initials($person->user->name)) }}"
                             data-status="{{ e($item->task->status) }}"
                             title="Click to view task details in drawer">
                            <span class="tb-task-icon"><i class="fas {{ $typeIcon($item->task->type) }}"></i></span>
                            <span class="tb-task-main">
                                <b>{{ $item->task->title ?? 'Untitled' }}</b>
                                <small>
                                    {{ $item->task->client->name ?? 'No client' }}
                                    @if($item->task->is_paused) · <i class="fas fa-pause"></i> Paused
                                    @elseif($item->task->started_at?->isPast()) · started {{ $item->task->started_at->diffForHumans() }}
                                    @endif
                                </small>
                            </span>
                            <span class="tb-due t-{{ $chip['tone'] }}">{{ $chip['text'] }}</span>
                            <a href="{{ $taskUrl($item->task) }}" class="tb-task-ext-btn" title="Open full workspace in new tab" target="_blank" onclick="event.stopPropagation()">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    @empty
                        <div class="tb-empty-line">
                            @if($person->next->isNotEmpty())
                                <i class="fas fa-circle-pause"></i> Nothing started yet. Next up is #1 below.
                            @else
                                <i class="fas fa-mug-hot"></i> Free. Nothing assigned right now.
                            @endif
                        </div>
                    @endforelse
                    @if($person->now->count() > 2)
                        <button type="button" class="tb-more" data-more-now>Show {{ $person->now->count() - 2 }} more in progress</button>
                    @endif
                </div>

                {{-- UP NEXT --}}
                <div class="tb-section">
                    <div class="tb-section-title"><span>Up next</span>@if($person->next->isNotEmpty())<span>{{ $person->next->count() }} queued</span>@endif</div>
                    @forelse($person->next as $index => $item)
                        @php $chip = $dueChip($item); @endphp
                        <div class="tb-task" role="button" tabindex="0"
                             onclick="openTeamBoardInspector(event, {{ $item->task->id }}, this)"
                             @if($index >= 3) data-extra hidden @endif
                             data-task-id="{{ $item->task->id }}"
                             data-inspect-url="{{ route($isAdmin ? 'admin.tasks.quick-inspect' : 'strategist.tasks.quick-inspect', $item->task) }}"
                             data-comment-url="{{ route($isAdmin ? 'admin.tasks.comment' : 'strategist.tasks.comment', $item->task) }}"
                             data-full-url="{{ $taskUrl($item->task) }}"
                             data-title="{{ e($item->task->title ?? 'Untitled') }}"
                             data-client="{{ e($item->task->client->name ?? 'No client') }}"
                             data-type="{{ e(ucfirst($item->task->type ?? 'Task')) }}"
                             data-type-icon="{{ $typeIcon($item->task->type) }}"
                             data-priority="{{ e(strtolower($item->task->priority ?? 'normal')) }}"
                             data-due-text="{{ e($chip['text']) }}"
                             data-due-tone="{{ e($chip['tone']) }}"
                             data-assignee="{{ e($person->user->name) }}"
                             data-assignee-role="{{ e($roleLabel($person->kind)) }}"
                             data-assignee-avatar="{{ e($person->user->avatar_color ?: $hue($person->user->name)) }}"
                             data-assignee-initial="{{ e($initials($person->user->name)) }}"
                             data-status="{{ e($item->task->status) }}"
                             title="Click to view task details in drawer">
                            <span class="tb-task-num">{{ $index + 1 }}</span>
                            <span class="tb-task-main">
                                <b>{{ $item->task->title ?? 'Untitled' }}</b>
                                <small><i class="fas {{ $typeIcon($item->task->type) }}"></i> {{ $item->task->client->name ?? 'No client' }}{{ $item->task->creator ? ' · by ' . $item->task->creator->name : '' }}</small>
                            </span>
                            <span class="tb-prio p-{{ strtolower($item->task->priority ?? 'normal') }}" title="{{ ucfirst($item->task->priority ?? 'normal') }} priority"></span>
                            <span class="tb-due t-{{ $chip['tone'] }}">{{ $chip['text'] }}</span>
                            <a href="{{ $taskUrl($item->task) }}" class="tb-task-ext-btn" title="Open full workspace in new tab" target="_blank" onclick="event.stopPropagation()">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    @empty
                        <div class="tb-empty-line">Queue is empty.</div>
                    @endforelse
                    @if($person->next->count() > 3)
                        <button type="button" class="tb-more" data-more>Show {{ $person->next->count() - 3 }} more</button>
                    @endif
                </div>

                {{-- LANE FOOTER: Overdue, In Review, On Hold Trays --}}
                @if($person->waiting->isNotEmpty() || $person->onHold->isNotEmpty() || $person->overdue > 0)
                    <div class="tb-foot">
                        @if($person->overdue > 0)
                            <span class="tb-chip t-red"><i class="fas fa-triangle-exclamation"></i> {{ $person->overdue }} overdue</span>
                        @endif
                        @if($person->waiting->isNotEmpty())
                            <button type="button" class="tb-chip tb-chip-btn t-violet" onclick="toggleLaneTray('waiting-{{ $person->user->id }}')" title="Click to view {{ $person->waiting->count() }} tasks in review">
                                <i class="fas fa-eye"></i> {{ $person->waiting->count() }} in review <i class="fas fa-chevron-down tb-chevron" id="chev-waiting-{{ $person->user->id }}" style="font-size:9px;margin-left:2px"></i>
                            </button>
                        @endif
                        @if($person->onHold->isNotEmpty())
                            <button type="button" class="tb-chip tb-chip-btn t-slate" onclick="toggleLaneTray('onhold-{{ $person->user->id }}')" title="Click to view {{ $person->onHold->count() }} tasks on hold">
                                <i class="fas fa-circle-pause"></i> {{ $person->onHold->count() }} on hold <i class="fas fa-chevron-down tb-chevron" id="chev-onhold-{{ $person->user->id }}" style="font-size:9px;margin-left:2px"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Collapsible tray for tasks waiting for review --}}
                    @if($person->waiting->isNotEmpty())
                        <div class="tb-tray" id="tray-waiting-{{ $person->user->id }}" style="display:none">
                            <div class="tb-tray-title"><i class="fas fa-eye" style="color:var(--v-fg)"></i> In Review / Awaiting Sign-off ({{ $person->waiting->count() }})</div>
                            @foreach($person->waiting as $wIndex => $item)
                                @php $chip = $dueChip($item); @endphp
                                <div class="tb-task tb-task-waiting" role="button" tabindex="0"
                                     onclick="openTeamBoardInspector(event, {{ $item->task->id }}, this)"
                                     data-task-id="{{ $item->task->id }}"
                                     data-inspect-url="{{ route($isAdmin ? 'admin.tasks.quick-inspect' : 'strategist.tasks.quick-inspect', $item->task) }}"
                                     data-comment-url="{{ route($isAdmin ? 'admin.tasks.comment' : 'strategist.tasks.comment', $item->task) }}"
                                     data-full-url="{{ $taskUrl($item->task) }}"
                                     data-title="{{ e($item->task->title ?? 'Untitled') }}"
                                     data-client="{{ e($item->task->client->name ?? 'No client') }}"
                                     data-type="{{ e(ucfirst($item->task->type ?? 'Task')) }}"
                                     data-type-icon="{{ $typeIcon($item->task->type) }}"
                                     data-priority="{{ e(strtolower($item->task->priority ?? 'normal')) }}"
                                     data-due-text="{{ e($chip['text']) }}"
                                     data-due-tone="{{ e($chip['tone']) }}"
                                     data-assignee="{{ e($person->user->name) }}"
                                     data-assignee-role="{{ e($roleLabel($person->kind)) }}"
                                     data-assignee-avatar="{{ e($person->user->avatar_color ?: $hue($person->user->name)) }}"
                                     data-assignee-initial="{{ e($initials($person->user->name)) }}"
                                     data-status="{{ e($item->task->status) }}"
                                     title="Click to view task details in drawer">
                                    <span class="tb-task-num" style="background:var(--v-bg);color:var(--v-fg);border-color:rgba(139,92,246,0.3)">{{ $wIndex + 1 }}</span>
                                    <span class="tb-task-main">
                                        <b>{{ $item->task->title ?? 'Untitled' }}</b>
                                        <small><i class="fas {{ $typeIcon($item->task->type) }}"></i> {{ $item->task->client->name ?? 'No client' }} · Review</small>
                                    </span>
                                    <span class="tb-prio p-{{ strtolower($item->task->priority ?? 'normal') }}"></span>
                                    <span class="tb-due t-{{ $chip['tone'] }}">{{ $chip['text'] }}</span>
                                    <a href="{{ $taskUrl($item->task) }}" class="tb-task-ext-btn" title="Open full workspace in new tab" target="_blank" onclick="event.stopPropagation()">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Collapsible tray for tasks on hold --}}
                    @if($person->onHold->isNotEmpty())
                        <div class="tb-tray" id="tray-onhold-{{ $person->user->id }}" style="display:none">
                            <div class="tb-tray-title"><i class="fas fa-circle-pause" style="color:var(--s-fg)"></i> On Hold ({{ $person->onHold->count() }})</div>
                            @foreach($person->onHold as $hIndex => $item)
                                @php $chip = $dueChip($item); @endphp
                                <div class="tb-task tb-task-hold" role="button" tabindex="0"
                                     onclick="openTeamBoardInspector(event, {{ $item->task->id }}, this)"
                                     data-task-id="{{ $item->task->id }}"
                                     data-inspect-url="{{ route($isAdmin ? 'admin.tasks.quick-inspect' : 'strategist.tasks.quick-inspect', $item->task) }}"
                                     data-comment-url="{{ route($isAdmin ? 'admin.tasks.comment' : 'strategist.tasks.comment', $item->task) }}"
                                     data-full-url="{{ $taskUrl($item->task) }}"
                                     data-title="{{ e($item->task->title ?? 'Untitled') }}"
                                     data-client="{{ e($item->task->client->name ?? 'No client') }}"
                                     data-type="{{ e(ucfirst($item->task->type ?? 'Task')) }}"
                                     data-type-icon="{{ $typeIcon($item->task->type) }}"
                                     data-priority="{{ e(strtolower($item->task->priority ?? 'normal')) }}"
                                     data-due-text="{{ e($chip['text']) }}"
                                     data-due-tone="{{ e($chip['tone']) }}"
                                     data-assignee="{{ e($person->user->name) }}"
                                     data-assignee-role="{{ e($roleLabel($person->kind)) }}"
                                     data-assignee-avatar="{{ e($person->user->avatar_color ?: $hue($person->user->name)) }}"
                                     data-assignee-initial="{{ e($initials($person->user->name)) }}"
                                     data-status="{{ e($item->task->status) }}"
                                     title="Click to view task details in drawer">
                                    <span class="tb-task-num">{{ $hIndex + 1 }}</span>
                                    <span class="tb-task-main">
                                        <b>{{ $item->task->title ?? 'Untitled' }}</b>
                                        <small><i class="fas {{ $typeIcon($item->task->type) }}"></i> {{ $item->task->client->name ?? 'No client' }} · On Hold</small>
                                    </span>
                                    <span class="tb-due t-{{ $chip['tone'] }}">{{ $chip['text'] }}</span>
                                    <a href="{{ $taskUrl($item->task) }}" class="tb-task-ext-btn" title="Open full workspace in new tab" target="_blank" onclick="event.stopPropagation()">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </article>
        @empty
            <div class="tb-empty">No team members found.</div>
        @endforelse
        <div class="tb-empty" id="tbNoMatch" hidden>No one matches this search.</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SLIDE-OVER TASK INSPECTOR DRAWER (Zero Redirects)
     ═══════════════════════════════════════════════════════════════ --}}
<div class="tb-drawer-overlay" id="tbDrawerOverlay" onclick="closeTeamBoardInspector()"></div>
<aside class="tb-drawer" id="tbDrawer" aria-hidden="true" role="dialog" aria-labelledby="tbDrawerTaskTitle">
    {{-- Header --}}
    <div class="tb-drawer-header">
        <div style="flex:1;min-width:0">
            <div class="tb-drawer-client" id="tbDrawerClientPill">
                <span id="tbDrawerClientEmoji" style="font-size:14px">🏢</span>
                <span id="tbDrawerClientName">Client Name</span>
            </div>
            <h2 class="tb-drawer-title" id="tbDrawerTaskTitle">Loading task details...</h2>
            <div class="tb-drawer-badges" id="tbDrawerBadges">
                <span class="tb-pill" id="tbDrawerStatusPill">STATUS</span>
                <span class="tb-type-tag" id="tbDrawerTypePill"><i class="fas fa-shapes"></i> Type</span>
                <span class="tb-prio-badge" id="tbDrawerPrioPill">PRIORITY</span>
                <div id="tbDrawerPlatformPills" style="display:inline-flex;gap:4px"></div>
            </div>
        </div>
        <button type="button" class="tb-drawer-close" onclick="closeTeamBoardInspector()" title="Close Inspector (Esc)">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- Subheader Quick Links --}}
    <div class="tb-drawer-subhead">
        <div style="display:flex;align-items:center;gap:8px">
            <button type="button" class="tb-sub-btn" onclick="copyTaskInspectorLink()" title="Copy link to task">
                <i class="fa-solid fa-link"></i> Copy Link
            </button>
            <button type="button" class="tb-sub-btn" onclick="copyTaskBrief()" id="tbCopyBriefBtn" title="Copy brief text">
                <i class="fa-regular fa-copy"></i> Copy Brief
            </button>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <a href="#" id="tbDrawerFullLink" class="tb-sub-btn tb-sub-btn-primary" target="_blank" title="Open full workspace in new tab">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Full Workspace
            </a>
        </div>
    </div>

    {{-- Body --}}
    <div class="tb-drawer-body" id="tbDrawerBody">
        {{-- Personnel & Timing Strip --}}
        <div class="tb-dcard">
            <div class="tb-dcard-title"><i class="fa-solid fa-user-clock"></i> Personnel &amp; Production Status</div>
            <div class="tb-d-personnel-row">
                <div class="tb-d-user-info">
                    <div class="tb-d-avatar" id="tbDrawerAssigneeAvatar">?</div>
                    <div>
                        <div class="tb-d-user-name" id="tbDrawerAssigneeName">Assignee Name</div>
                        <div class="tb-d-user-role" id="tbDrawerAssigneeRole">Designer</div>
                    </div>
                </div>
                <div class="tb-d-timer-badge" id="tbDrawerTimerBadge">
                    <span class="tb-live-dot" id="tbDrawerTimerDot" style="display:none"></span>
                    <span id="tbDrawerTimerText"><i class="fa-regular fa-clock"></i> Timer idle</span>
                </div>
            </div>
            <div class="tb-d-meta-grid">
                <div><strong>Created By:</strong> <span id="tbDrawerCreatorName">—</span></div>
                <div><strong>Deadline:</strong> <span id="tbDrawerDeadline">—</span></div>
                <div><strong>Post Date:</strong> <span id="tbDrawerPostDate">—</span></div>
                <div><strong>Client Sign-off:</strong> <span id="tbDrawerClientSignOff">—</span></div>
            </div>
        </div>

        {{-- Revision Alert (Hidden if 0) --}}
        <div class="tb-dcard tb-dcard-revision" id="tbDrawerRevisionCard" style="display:none">
            <div class="tb-dcard-title" style="color:#d97706">
                <i class="fa-solid fa-rotate-left"></i> Active Revision Phase (<span id="tbDrawerRevisionCount">0</span>)
            </div>
            <div class="tb-d-revision-note" id="tbDrawerRevisionNote">No revision notes.</div>
            <div class="tb-d-revision-meta">
                <span>Requested by: <strong id="tbDrawerRevisionAuthor">—</strong></span>
                <span id="tbDrawerRevisionDate">—</span>
            </div>
        </div>

        {{-- Creative Brief --}}
        <div class="tb-dcard">
            <div class="tb-dcard-title" style="justify-content:space-between">
                <span><i class="fa-solid fa-align-left"></i> Creative Brief &amp; Scope</span>
                <span id="tbDrawerDueBadge" class="tb-due t-muted">—</span>
            </div>
            <div class="tb-d-brief-box" id="tbDrawerBriefBox">
                Loading creative brief...
            </div>
            <div id="tbDrawerRefLinks" style="display:none;margin-top:8px">
                <div style="font-size:11px;font-weight:700;color:var(--text3);margin-bottom:4px">REFERENCE LINKS:</div>
                <div id="tbDrawerRefLinksList" style="display:flex;flex-direction:column;gap:4px"></div>
            </div>
        </div>

        {{-- Deliverables & Visual Artwork Preview --}}
        <div class="tb-dcard">
            <div class="tb-dcard-title" style="justify-content:space-between">
                <span><i class="fa-solid fa-paintbrush"></i> Submitted Deliverables (<span id="tbDrawerDeliverablesCount">0</span>)</span>
            </div>
            <div class="tb-d-media-grid" id="tbDrawerDeliverablesGrid">
                <div class="tb-d-empty"><i class="fa-regular fa-image"></i> No deliverables submitted yet.</div>
            </div>
        </div>

        {{-- Reference Materials (if any) --}}
        <div class="tb-dcard" id="tbDrawerRefCard" style="display:none">
            <div class="tb-dcard-title">
                <i class="fa-solid fa-paperclip"></i> Reference Files &amp; Assets (<span id="tbDrawerRefCount">0</span>)
            </div>
            <div class="tb-d-media-grid" id="tbDrawerRefGrid"></div>
        </div>

        {{-- Marketing Copy & Hashtags (if any) --}}
        <div class="tb-dcard" id="tbDrawerCopyCard" style="display:none">
            <div class="tb-dcard-title" style="justify-content:space-between">
                <span><i class="fa-solid fa-bullhorn"></i> Marketing Copy &amp; Hashtags</span>
                <button type="button" class="tb-sub-btn" onclick="copyDrawerCaptionHashtags()" title="Copy Caption & Tags">
                    <i class="fa-regular fa-copy"></i> Copy All
                </button>
            </div>
            <div id="tbDrawerHeadlineRow" style="display:none;margin-bottom:8px">
                <div style="font-size:10.5px;font-weight:700;color:var(--text3);text-transform:uppercase">Headline / Hook</div>
                <div id="tbDrawerHeadlineText" style="font-size:12.5px;font-weight:700;color:var(--text);margin-top:2px"></div>
            </div>
            <div id="tbDrawerCaptionRow" style="display:none;margin-bottom:8px">
                <div style="font-size:10.5px;font-weight:700;color:var(--text3);text-transform:uppercase">Caption</div>
                <div id="tbDrawerCaptionText" class="tb-copy-block"></div>
            </div>
            <div id="tbDrawerHashtagsRow" style="display:none">
                <div style="font-size:10.5px;font-weight:700;color:var(--text3);text-transform:uppercase">Hashtags</div>
                <div id="tbDrawerHashtagsText" style="font-size:12px;color:var(--tb-brand);margin-top:2px;font-weight:600"></div>
            </div>
        </div>

        {{-- Team Discussion & Comments --}}
        <div class="tb-dcard">
            <div class="tb-dcard-title"><i class="fa-solid fa-comments"></i> Discussion &amp; Comments</div>
            <div class="tb-d-comments-feed" id="tbDrawerCommentsFeed">
                <div class="tb-d-empty">No comments yet.</div>
            </div>
            <div class="tb-d-comment-form">
                <input type="text" id="tbDrawerCommentInput" placeholder="Add comment or feedback..." onkeydown="if(event.key==='Enter') submitTeamBoardComment()">
                <button type="button" onclick="submitTeamBoardComment()" title="Post comment">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="tb-drawer-footer">
        <div>
            <button type="button" class="tb-btn tb-btn-ghost" onclick="closeTeamBoardInspector()">Close</button>
        </div>
        <div style="display:flex;gap:8px">
            <a href="#" id="tbDrawerFooterEditLink" class="tb-btn tb-btn-ghost" title="Edit this task">
                <i class="fa-solid fa-pen"></i> Edit Task
            </a>
            <a href="#" id="tbDrawerFooterFullLink" class="tb-btn tb-btn-primary" target="_blank" title="Open full workspace in new tab">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in Workspace
            </a>
        </div>
    </div>
</aside>

{{-- Toast Container --}}
<div id="tbToastContainer" class="tb-toast-container"></div>

{{-- Lightbox Modal --}}
<div id="tbLightboxModal" class="tb-lightbox" style="display:none" onclick="closeTbLightbox()">
    <button type="button" class="tb-lightbox-close" onclick="closeTbLightbox()">&times;</button>
    <img id="tbLightboxImg" src="" alt="Deliverable full preview" onclick="event.stopPropagation()">
</div>

<script>
window.tbIsAdmin = {{ $isAdmin ? 'true' : 'false' }};
let activeInspectTaskId = null;
let currentTaskPayload = null;

(function () {
    const grid = document.getElementById('tbGrid');
    const lanes = Array.from(grid.querySelectorAll('.tb-lane'));
    const search = document.getElementById('tbSearch');
    const sort = document.getElementById('tbSort');
    const noMatch = document.getElementById('tbNoMatch');
    let group = 'all';

    const sorters = {
        load: (a, b) => b.dataset.load - a.dataset.load || b.dataset.overdue - a.dataset.overdue,
        free: (a, b) => a.dataset.load - b.dataset.load || a.dataset.name.localeCompare(b.dataset.name),
        overdue: (a, b) => b.dataset.overdue - a.dataset.overdue || b.dataset.load - a.dataset.load,
        name: (a, b) => a.dataset.name.localeCompare(b.dataset.name),
    };
    try { sort.value = localStorage.getItem('teamBoard.sort') || 'load'; } catch (_) {}

    function render() {
        const q = search.value.trim().toLowerCase();
        let shown = 0;
        lanes.sort(sorters[sort.value] || sorters.load).forEach(lane => {
            const visible = (group === 'all' || lane.dataset.group === group) && (!q || lane.dataset.search.includes(q));
            lane.hidden = !visible;
            if (visible) shown++;
            grid.insertBefore(lane, noMatch);
        });
        noMatch.hidden = shown > 0 || !lanes.length;
    }

    document.querySelectorAll('[data-role-tab]').forEach(tab => tab.addEventListener('click', () => {
        group = tab.dataset.roleTab;
        document.querySelectorAll('[data-role-tab]').forEach(t => t.classList.toggle('is-active', t === tab));
        render();
    }));
    search.addEventListener('input', render);
    sort.addEventListener('change', () => { try { localStorage.setItem('teamBoard.sort', sort.value); } catch (_) {} render(); });

    grid.addEventListener('click', (e) => {
        // Toggle Up Next extra items
        const more = e.target.closest('[data-more]');
        if (more) {
            const extras = more.closest('.tb-section').querySelectorAll('[data-extra]');
            const opening = extras[0]?.hidden;
            extras.forEach(el => { el.hidden = !opening; });
            more.textContent = opening ? 'Show less' : `Show ${extras.length} more`;
            return;
        }

        // Toggle Working on Now extra items
        const moreNow = e.target.closest('[data-more-now]');
        if (moreNow) {
            const extrasNow = moreNow.closest('.tb-section').querySelectorAll('[data-extra-now]');
            const openingNow = extrasNow[0]?.hidden;
            extrasNow.forEach(el => { el.hidden = !openingNow; });
            moreNow.textContent = openingNow ? 'Show less' : `Show ${extrasNow.length} more in progress`;
            return;
        }
    });

    render();
})();

// ── Lane Tray Collapsible (Waiting for Review & On Hold) ─────────────
function toggleLaneTray(trayId) {
    const tray = document.getElementById(`tray-${trayId}`);
    const chevron = document.getElementById(`chev-${trayId}`);
    if (!tray) return;
    const isHidden = tray.style.display === 'none' || tray.hidden;
    tray.style.display = isHidden ? 'block' : 'none';
    if (chevron) {
        chevron.classList.toggle('is-open', isHidden);
    }
}

// ── Slide-Over Task Inspector Functions ───────────────────────────────
function openTeamBoardInspector(event, taskId, el) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    activeInspectTaskId = taskId;
    const drawer = document.getElementById('tbDrawer');
    const overlay = document.getElementById('tbDrawerOverlay');
    if (!drawer || !overlay) return;

    overlay.classList.add('is-open');
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    // 1. Optimistic 0ms Render from card data attributes
    const inspectUrl = el?.dataset?.inspectUrl || (window.tbIsAdmin ? `/admin/tasks/${taskId}/quick-inspect` : `/strategist/tasks/${taskId}/quick-inspect`);
    const fullUrl = el?.dataset?.fullUrl || (window.tbIsAdmin ? `/admin/tasks/${taskId}` : `/strategist/tracking?task=${taskId}`);
    const editUrl = window.tbIsAdmin ? `/admin/tasks/${taskId}/edit` : `/strategist/tasks/${taskId}/edit`;
    const title = el?.dataset?.title || 'Loading task...';
    const client = el?.dataset?.client || 'Client';
    const type = el?.dataset?.type || 'Task';
    const typeIcon = el?.dataset?.typeIcon || 'fa-shapes';
    const priority = (el?.dataset?.priority || 'normal').toLowerCase();
    const status = el?.dataset?.status || 'todo';
    const dueText = el?.dataset?.dueText || '—';
    const dueTone = el?.dataset?.dueTone || 'muted';
    const assigneeName = el?.dataset?.assignee || 'Unassigned';
    const assigneeRole = el?.dataset?.assigneeRole || 'Team Member';
    const assigneeAvatar = el?.dataset?.assigneeAvatar || '#6366f1';
    const assigneeInitial = el?.dataset?.assigneeInitial || '?';

    document.getElementById('tbDrawerClientName').textContent = client;
    document.getElementById('tbDrawerTaskTitle').textContent = title;
    document.getElementById('tbDrawerFullLink').href = fullUrl;
    document.getElementById('tbDrawerFooterFullLink').href = fullUrl;
    document.getElementById('tbDrawerFooterEditLink').href = editUrl;

    // Badges
    const statusPill = document.getElementById('tbDrawerStatusPill');
    statusPill.textContent = status.replace('_', ' ').toUpperCase();
    statusPill.className = 'tb-pill t-' + (status === 'inprogress' ? 'blue' : (status === 'review' ? 'violet' : (status === 'completed' ? 'green' : 'slate')));

    const typePill = document.getElementById('tbDrawerTypePill');
    typePill.innerHTML = `<i class="fas ${typeIcon}"></i> ${type}`;

    const prioPill = document.getElementById('tbDrawerPrioPill');
    prioPill.textContent = priority.toUpperCase();
    prioPill.className = 'tb-prio-badge p-' + priority;

    document.getElementById('tbDrawerAssigneeName').textContent = assigneeName;
    document.getElementById('tbDrawerAssigneeRole').textContent = assigneeRole;
    const av = document.getElementById('tbDrawerAssigneeAvatar');
    av.textContent = assigneeInitial;
    av.style.background = assigneeAvatar;

    const dueBadge = document.getElementById('tbDrawerDueBadge');
    dueBadge.textContent = dueText;
    dueBadge.className = 'tb-due t-' + dueTone;

    // Reset asynchronous sections with skeleton state
    document.getElementById('tbDrawerBriefBox').innerHTML = '<span style="color:var(--text3)"><i class="fa-solid fa-spinner fa-spin"></i> Loading creative brief...</span>';
    document.getElementById('tbDrawerDeliverablesGrid').innerHTML = '<div class="tb-d-empty"><i class="fa-solid fa-spinner fa-spin"></i> Fetching deliverables...</div>';
    document.getElementById('tbDrawerCommentsFeed').innerHTML = '<div class="tb-d-empty"><i class="fa-solid fa-spinner fa-spin"></i> Loading comments...</div>';
    document.getElementById('tbDrawerRefCard').style.display = 'none';
    document.getElementById('tbDrawerCopyCard').style.display = 'none';
    document.getElementById('tbDrawerRevisionCard').style.display = 'none';

    // 2. Fetch full rich task details asynchronously
    fetch(inspectUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    })
    .then(data => {
        if (!data.success || !data.task) throw new Error('Task payload missing');
        currentTaskPayload = data.task;
        renderTaskDetailsInDrawer(data.task);
    })
    .catch(err => {
        console.error('Drawer quick inspect error:', err);
        document.getElementById('tbDrawerBriefBox').textContent = 'Unable to fetch brief details. Click "Full Workspace" to view complete task.';
        document.getElementById('tbDrawerDeliverablesGrid').innerHTML = '<div class="tb-d-empty">Deliverables could not be loaded.</div>';
        document.getElementById('tbDrawerCommentsFeed').innerHTML = '<div class="tb-d-empty">Comments unavailable.</div>';
    });
}

function closeTeamBoardInspector() {
    const drawer = document.getElementById('tbDrawer');
    const overlay = document.getElementById('tbDrawerOverlay');
    if (drawer) {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
    }
    if (overlay) {
        overlay.classList.remove('is-open');
    }
    document.body.style.overflow = '';
    activeInspectTaskId = null;
}

function renderTaskDetailsInDrawer(task) {
    // Header & Meta
    document.getElementById('tbDrawerClientName').textContent = task.client?.name || 'Unknown Client';
    document.getElementById('tbDrawerClientEmoji').textContent = task.client?.emoji || '🏢';
    document.getElementById('tbDrawerTaskTitle').textContent = task.title || 'Untitled';

    // Status & Type & Priority
    const statusPill = document.getElementById('tbDrawerStatusPill');
    statusPill.textContent = (task.status_label || task.status).toUpperCase();
    statusPill.className = 'tb-pill t-' + (task.status === 'inprogress' ? 'blue' : (task.status === 'review' ? 'violet' : (task.status === 'completed' ? 'green' : 'slate')));

    const typePill = document.getElementById('tbDrawerTypePill');
    typePill.innerHTML = `<i class="fas fa-shapes"></i> ${task.type || 'Task'}`;

    const prioPill = document.getElementById('tbDrawerPrioPill');
    const prio = (task.priority || 'normal').toLowerCase();
    prioPill.textContent = prio.toUpperCase();
    prioPill.className = 'tb-prio-badge p-' + prio;

    // Platform Pills
    const platContainer = document.getElementById('tbDrawerPlatformPills');
    platContainer.innerHTML = '';
    if (Array.isArray(task.platform) && task.platform.length) {
        task.platform.forEach(p => {
            const chip = document.createElement('span');
            chip.className = 'tb-platform-chip';
            chip.textContent = p;
            platContainer.appendChild(chip);
        });
    }

    // Assignee
    if (task.assignee) {
        document.getElementById('tbDrawerAssigneeName').textContent = task.assignee.name;
        document.getElementById('tbDrawerAssigneeRole').textContent = task.assignee.role || 'Designer';
        const av = document.getElementById('tbDrawerAssigneeAvatar');
        av.textContent = task.assignee.initial || 'D';
        av.style.background = task.assignee.avatar_color || '#6366f1';
    } else {
        document.getElementById('tbDrawerAssigneeName').textContent = 'Unassigned';
        document.getElementById('tbDrawerAssigneeRole').textContent = 'Open for assignment';
        const av = document.getElementById('tbDrawerAssigneeAvatar');
        av.textContent = '?';
        av.style.background = '#94a3b8';
    }

    // Timer & Status
    const timerText = document.getElementById('tbDrawerTimerText');
    const timerDot = document.getElementById('tbDrawerTimerDot');
    if (task.is_timer_running) {
        timerDot.style.display = 'inline-block';
        timerText.innerHTML = `<strong style="color:#059669">Running:</strong> ${task.timer_formatted || 'Active'}`;
    } else if (task.is_paused) {
        timerDot.style.display = 'none';
        timerText.innerHTML = '<span style="color:#f59e0b"><i class="fa-solid fa-pause"></i> Paused</span>';
    } else if (task.timer_formatted && task.timer_formatted !== '0m') {
        timerDot.style.display = 'none';
        timerText.innerHTML = `<i class="fa-regular fa-clock"></i> ${task.timer_formatted} logged`;
    } else {
        timerDot.style.display = 'none';
        timerText.innerHTML = '<i class="fa-regular fa-clock"></i> Not running';
    }

    // Dates & Client Sign-Off
    document.getElementById('tbDrawerCreatorName').textContent = task.creator?.name || 'Admin';
    document.getElementById('tbDrawerDeadline').textContent = task.deadline || 'No deadline';
    document.getElementById('tbDrawerPostDate').textContent = task.post_date || 'Not scheduled';
    document.getElementById('tbDrawerClientSignOff').innerHTML = task.is_client_approved 
        ? '<span style="color:#10b981;font-weight:700"><i class="fa-solid fa-circle-check"></i> Approved</span>' 
        : (task.requires_client_approval ? '<span style="color:#f59e0b;font-weight:700"><i class="fa-solid fa-hourglass-half"></i> Pending Sign-off</span>' : 'Not required');

    // Revision Alert Card
    const revCard = document.getElementById('tbDrawerRevisionCard');
    if (task.revision_info && task.revision_info.count > 0) {
        revCard.style.display = 'flex';
        document.getElementById('tbDrawerRevisionCount').textContent = task.revision_info.count;
        document.getElementById('tbDrawerRevisionNote').textContent = task.revision_info.latest_note || 'Revisions requested by strategist.';
        document.getElementById('tbDrawerRevisionAuthor').textContent = task.revision_info.requested_by || 'Strategist';
        document.getElementById('tbDrawerRevisionDate').textContent = task.revision_info.date || 'Recently';
    } else {
        revCard.style.display = 'none';
    }

    // Creative Brief & Reference Links
    const briefBox = document.getElementById('tbDrawerBriefBox');
    briefBox.textContent = task.brief ? task.brief : 'No creative brief written for this task.';

    const refLinksContainer = document.getElementById('tbDrawerRefLinks');
    const refLinksList = document.getElementById('tbDrawerRefLinksList');
    refLinksList.innerHTML = '';
    if (Array.isArray(task.reference_links) && task.reference_links.length) {
        refLinksContainer.style.display = 'block';
        task.reference_links.forEach(url => {
            if (url) {
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.rel = 'noopener';
                a.style.cssText = 'color:var(--tb-brand);font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:5px;word-break:break-all';
                a.innerHTML = `<i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px"></i> ${escapeHtml(url)}`;
                refLinksList.appendChild(a);
            }
        });
    } else {
        refLinksContainer.style.display = 'none';
    }

    // Deliverables
    const deliverablesGrid = document.getElementById('tbDrawerDeliverablesGrid');
    deliverablesGrid.innerHTML = '';
    document.getElementById('tbDrawerDeliverablesCount').textContent = task.deliverables ? task.deliverables.length : 0;
    if (Array.isArray(task.deliverables) && task.deliverables.length) {
        task.deliverables.forEach(d => {
            const card = document.createElement('div');
            card.className = 'tb-d-media-card';
            if (d.is_image) {
                card.onclick = () => openTbLightbox(d.url);
                card.innerHTML = `
                    <img src="${escapeHtml(d.url)}" alt="${escapeHtml(d.name)}" loading="lazy">
                    <div class="tb-d-media-overlay">
                        <span class="tb-d-badge">${escapeHtml(d.version_label || 'Artwork')}</span>
                        <a href="${escapeHtml(d.url)}" download onclick="event.stopPropagation()" style="color:#fff"><i class="fa-solid fa-download"></i></a>
                    </div>
                `;
            } else if (d.is_video) {
                card.onclick = () => window.open(d.url, '_blank');
                card.innerHTML = `
                    <div style="font-size:28px;color:#ef4444"><i class="fa-solid fa-circle-play"></i></div>
                    <span style="font-size:11px;font-weight:700;color:var(--text);margin-top:6px;max-width:90%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escapeHtml(d.name)}</span>
                    <div class="tb-d-media-overlay">
                        <span class="tb-d-badge">VIDEO</span>
                        <a href="${escapeHtml(d.url)}" download onclick="event.stopPropagation()" style="color:#fff"><i class="fa-solid fa-download"></i></a>
                    </div>
                `;
            } else {
                card.onclick = () => window.open(d.url, '_blank');
                card.innerHTML = `
                    <div style="font-size:26px;color:#3b82f6"><i class="fa-solid fa-file-lines"></i></div>
                    <span style="font-size:11px;font-weight:700;color:var(--text);margin-top:6px;max-width:90%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escapeHtml(d.name)}</span>
                    <div class="tb-d-media-overlay">
                        <span class="tb-d-badge">${escapeHtml(d.size || 'FILE')}</span>
                        <a href="${escapeHtml(d.url)}" download onclick="event.stopPropagation()" style="color:#fff"><i class="fa-solid fa-download"></i></a>
                    </div>
                `;
            }
            deliverablesGrid.appendChild(card);
        });
    } else {
        deliverablesGrid.innerHTML = '<div class="tb-d-empty"><i class="fa-regular fa-image"></i> No deliverables submitted yet.</div>';
    }

    // Marketing Copy & Hashtags Card
    const copyCard = document.getElementById('tbDrawerCopyCard');
    const hasHeadline = !!task.headline_copy;
    const hasCaption = !!task.caption;
    const hasHashtags = !!task.hashtags;
    if (hasHeadline || hasCaption || hasHashtags) {
        copyCard.style.display = 'flex';
        document.getElementById('tbDrawerHeadlineRow').style.display = hasHeadline ? 'block' : 'none';
        document.getElementById('tbDrawerHeadlineText').textContent = task.headline_copy || '';
        document.getElementById('tbDrawerCaptionRow').style.display = hasCaption ? 'block' : 'none';
        document.getElementById('tbDrawerCaptionText').textContent = task.caption || '';
        document.getElementById('tbDrawerHashtagsRow').style.display = hasHashtags ? 'block' : 'none';
        document.getElementById('tbDrawerHashtagsText').textContent = task.hashtags || '';
    } else {
        copyCard.style.display = 'none';
    }

    // Comments Feed
    renderCommentsFeed(task.comments || []);
}

function renderCommentsFeed(comments) {
    const feed = document.getElementById('tbDrawerCommentsFeed');
    feed.innerHTML = '';
    if (!comments.length) {
        feed.innerHTML = '<div class="tb-d-empty">No comments yet.</div>';
        return;
    }
    comments.forEach(c => {
        const item = document.createElement('div');
        item.className = 'tb-d-comment-item';
        item.innerHTML = `
            <div class="tb-d-comment-avatar" style="background:${escapeHtml(c.user_avatar || '#6366f1')}">
                ${escapeHtml(c.user_initial || 'U')}
            </div>
            <div class="tb-d-comment-main">
                <div class="tb-d-comment-header">
                    <span>${escapeHtml(c.user_name || 'Team Member')}</span>
                    <span class="tb-d-comment-time">${escapeHtml(c.created_at || 'recently')}</span>
                </div>
                <div class="tb-d-comment-body">${escapeHtml(c.body)}</div>
            </div>
        `;
        feed.appendChild(item);
    });
}

// ── Comment Submission ────────────────────────────────────────────────
function submitTeamBoardComment() {
    if (!activeInspectTaskId) return;
    const input = document.getElementById('tbDrawerCommentInput');
    const body = input.value.trim();
    if (!body) return;

    const commentUrl = window.tbIsAdmin ? `/admin/tasks/${activeInspectTaskId}/comment` : `/strategist/tasks/${activeInspectTaskId}/comment`;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    input.disabled = true;
    fetch(commentUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ body })
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    })
    .then(data => {
        input.value = '';
        input.disabled = false;
        showTbToast('success', 'Comment posted!');
        
        // Append newly created comment to feed
        if (!currentTaskPayload.comments) currentTaskPayload.comments = [];
        currentTaskPayload.comments.unshift({
            id: Date.now(),
            user_name: data.user_name || 'You',
            user_initial: data.user_initial || 'Y',
            user_avatar: data.user_color || '#ef4444',
            body: body,
            created_at: data.time || 'just now'
        });
        renderCommentsFeed(currentTaskPayload.comments);
    })
    .catch(err => {
        console.error('Comment submission error:', err);
        input.disabled = false;
        showTbToast('error', 'Could not post comment.');
    });
}

// ── Clipboard Utilities ───────────────────────────────────────────────
function copyTaskInspectorLink() {
    if (!activeInspectTaskId) return;
    const url = window.location.origin + (window.tbIsAdmin ? `/admin/tasks/${activeInspectTaskId}` : `/strategist/tasks/${activeInspectTaskId}`);
    navigator.clipboard.writeText(url).then(() => {
        showTbToast('success', 'Task link copied to clipboard!');
    }).catch(() => {
        showTbToast('error', 'Failed to copy link.');
    });
}

function copyTaskBrief() {
    const brief = document.getElementById('tbDrawerBriefBox')?.textContent || '';
    if (!brief || brief.includes('Loading')) {
        showTbToast('error', 'No brief available to copy.');
        return;
    }
    navigator.clipboard.writeText(brief).then(() => {
        showTbToast('success', 'Creative brief copied to clipboard!');
    }).catch(() => {
        showTbToast('error', 'Failed to copy brief.');
    });
}

function copyDrawerCaptionHashtags() {
    const caption = document.getElementById('tbDrawerCaptionText')?.textContent || '';
    const tags = document.getElementById('tbDrawerHashtagsText')?.textContent || '';
    const full = [caption, tags].filter(Boolean).join("\n\n");
    if (!full) {
        showTbToast('error', 'No caption or hashtags available.');
        return;
    }
    navigator.clipboard.writeText(full).then(() => {
        showTbToast('success', 'Caption & hashtags copied to clipboard!');
    }).catch(() => {
        showTbToast('error', 'Failed to copy copy.');
    });
}

// ── Lightbox Helpers ──────────────────────────────────────────────────
function openTbLightbox(src) {
    const modal = document.getElementById('tbLightboxModal');
    const img = document.getElementById('tbLightboxImg');
    if (!modal || !img) return;
    img.src = src;
    modal.style.display = 'flex';
}

function closeTbLightbox() {
    const modal = document.getElementById('tbLightboxModal');
    if (modal) modal.style.display = 'none';
}

// ── Toast Notification Helper ─────────────────────────────────────────
function showTbToast(type, message) {
    const container = document.getElementById('tbToastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `tb-toast tb-toast-${type}`;
    const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
    toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${escapeHtml(message)}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 250);
    }, 3200);
}

function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, function(m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
    });
}

// ── Keyboard Shortcuts (Esc to close) ─────────────────────────────────
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const lightbox = document.getElementById('tbLightboxModal');
        if (lightbox && lightbox.style.display !== 'none') {
            closeTbLightbox();
            return;
        }
        closeTeamBoardInspector();
    }
});
</script>
@endsection
