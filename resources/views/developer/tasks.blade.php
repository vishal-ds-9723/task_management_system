@extends('layouts.app')

@section('content')
@php
    $scope = $scope ?? 'my';
    $me = auth()->id();
    $today = today();

    $statusMeta = [
        'todo'             => ['label' => 'To Do',             'icon' => 'fa-circle-dot',     'tone' => 'slate',  'group' => 'todo'],
        'inprogress'       => ['label' => 'In Progress',       'icon' => 'fa-bolt',           'tone' => 'red',    'group' => 'inprogress'],
        'review'           => ['label' => 'In Review',         'icon' => 'fa-hourglass-half', 'tone' => 'amber',  'group' => 'review'],
        'pending_approval' => ['label' => 'Awaiting Approval', 'icon' => 'fa-user-check',     'tone' => 'violet', 'group' => 'review'],
        'on_hold'          => ['label' => 'On Hold',           'icon' => 'fa-circle-pause',   'tone' => 'slate',  'group' => 'hold'],
        'completed'        => ['label' => 'Completed',         'icon' => 'fa-circle-check',   'tone' => 'green',  'group' => 'done'],
        'published'        => ['label' => 'Published',         'icon' => 'fa-rocket',         'tone' => 'green',  'group' => 'done'],
    ];
    $priorityRank = ['urgent' => 0, 'high' => 1, 'normal' => 2, 'medium' => 2, 'low' => 3];

    $avatarColor = fn (?string $name) => 'hsl(' . (crc32((string) $name) % 360) . ', 62%, 52%)';
    $initials = fn (?string $name) => collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: '?';

    // Build one view-model per task so the card and the detail panel stay in sync
    $rows = $tasks->values()->map(function ($task, $index) use ($statusMeta, $priorityRank, $today, $me) {
        $meta = $statusMeta[$task->status] ?? ['label' => ucfirst(str_replace('_', ' ', (string) $task->status)), 'icon' => 'fa-circle', 'tone' => 'slate', 'group' => 'todo'];
        $priority = strtolower($task->priority ?? 'normal');
        $isOpen = in_array($task->status, ['todo', 'inprogress'], true);

        // Carbon 3 diffs are signed: positive = days left, negative = days overdue
        $daysLeft = $task->dev_deadline ? (int) $today->diffInDays($task->dev_deadline) : null;
        $deadline = null;
        if ($task->dev_deadline) {
            if (!$isOpen) {
                $deadline = ['tone' => 'muted', 'text' => null];
            } elseif ($daysLeft < 0) {
                $deadline = ['tone' => 'danger', 'text' => 'Overdue by ' . abs($daysLeft) . ' ' . Str::plural('day', abs($daysLeft))];
            } elseif ($daysLeft === 0) {
                $deadline = ['tone' => 'warn', 'text' => 'Due today'];
            } elseif ($daysLeft <= 3) {
                $deadline = ['tone' => 'warn', 'text' => $daysLeft . ' ' . Str::plural('day', $daysLeft) . ' left'];
            } else {
                $deadline = ['tone' => 'ok', 'text' => $daysLeft . ' days left'];
            }
        }

        $assets = collect([
            'Logo' => $task->logo_received,
            'Images' => $task->images_received,
            'Content' => $task->content_received,
            'Domain' => $task->domain_purchased,
            'Hosting' => $task->hosting_access,
        ])->reject(fn ($value) => $value === null);

        // Modules arrive as "1. Foo\n2. Bar" or "Foo, Bar • Baz"
        $rawModules = trim((string) $task->modules);
        $modules = collect(preg_split(str_contains($rawModules, "\n") ? '/\r\n|\r|\n/' : '/[,•]/', $rawModules))
            ->map(fn ($line) => trim(preg_replace('/^\s*(\d+[.)]|[-*•])\s*/', '', $line)))
            ->filter()
            ->values();

        $tech = $task->preferred_tech ?: $task->tech_stack;
        $progressStep = match ($task->status) {
            'completed', 'published' => 4,
            'review', 'pending_approval' => 3,
            'inprogress' => 2,
            default => 1,
        };

        return (object) [
            'task' => $task,
            'index' => $index,
            'meta' => $meta,
            'priority' => $priority,
            'priorityRank' => $priorityRank[$priority] ?? 2,
            'isMine' => $task->assigned_to === $me,
            'isOpen' => $isOpen,
            'isOverdue' => $isOpen && $daysLeft !== null && $daysLeft < 0,
            'deadline' => $deadline,
            'assets' => $assets,
            'assetsReady' => $assets->filter()->count(),
            'modules' => $modules,
            'tech' => $tech,
            'techList' => collect(preg_split('/[,\/|]/', (string) $tech))->map(fn ($t) => trim($t))->filter()->values(),
            'hours' => round((float) ($task->logged_hours ?? 0), 2),
            'progressStep' => $progressStep,
            'search' => mb_strtolower(implode(' ', array_filter([
                $task->title, $task->client->name ?? null, $task->creator->name ?? null,
                $task->assignee->name ?? null, $task->type, $tech,
            ]))),
        ];
    });

    $counts = [
        'all' => $rows->count(),
        'todo' => $rows->where('meta.group', 'todo')->count(),
        'inprogress' => $rows->where('meta.group', 'inprogress')->count(),
        'review' => $rows->where('meta.group', 'review')->count(),
        'done' => $rows->where('meta.group', 'done')->count(),
        'hold' => $rows->where('meta.group', 'hold')->count(),
        'overdue' => $rows->where('isOverdue', true)->count(),
    ];
    $dueThisWeek = $rows->filter(fn ($r) => $r->isOpen && $r->task->dev_deadline && $r->task->dev_deadline->betweenIncluded($today, $today->copy()->addDays(7)))->count();
    $activeCount = $counts['todo'] + $counts['inprogress'];

    $clientOptions = $tasks->pluck('client')->filter()->unique('id')->sortBy('name');
    $assigneeOptions = $tasks->pluck('assignee')->filter()->unique('id')->sortBy('name');

    $kpis = [
        ['key' => 'all',        'label' => 'All Projects', 'icon' => 'fa-layer-group',     'tone' => 'brand'],
        ['key' => 'todo',       'label' => 'To Do',        'icon' => 'fa-circle-dot',      'tone' => 'slate'],
        ['key' => 'inprogress', 'label' => 'In Progress',  'icon' => 'fa-bolt',            'tone' => 'red'],
        ['key' => 'review',     'label' => 'In Review',    'icon' => 'fa-hourglass-half',  'tone' => 'amber'],
        ['key' => 'done',       'label' => 'Completed',    'icon' => 'fa-circle-check',    'tone' => 'green'],
        ['key' => 'overdue',    'label' => 'Overdue',      'icon' => 'fa-fire',            'tone' => 'danger'],
    ];
    $boardColumns = [
        'todo' => 'To Do',
        'inprogress' => 'In Progress',
        'review' => 'In Review',
        'hold' => 'On Hold',
        'done' => 'Completed',
    ];
@endphp

<style>
/* Tokens are shared by the page and the drawer (which lives outside .dt-wrap) */
.dt-wrap, .dt-drawer {
    --dt-brand: #ef4444;
    --dt-brand-dark: #c81e1e;
    --dt-brand-soft: rgba(239,68,68,0.09);
    --dt-shadow-sm: 0 1px 2px rgba(15,23,42,0.04), 0 1px 3px rgba(15,23,42,0.06);
    --dt-shadow-md: 0 10px 30px -12px rgba(15,23,42,0.18);
    --dt-shadow-lg: 0 24px 60px -20px rgba(15,23,42,0.35);
    --tone-red-bg: rgba(239,68,68,0.10);     --tone-red-fg: #dc2626;
    --tone-amber-bg: rgba(245,158,11,0.13);  --tone-amber-fg: #b45309;
    --tone-green-bg: rgba(16,185,129,0.12);  --tone-green-fg: #047857;
    --tone-violet-bg: rgba(139,92,246,0.12); --tone-violet-fg: #6d28d9;
    --tone-slate-bg: rgba(100,116,139,0.12); --tone-slate-fg: #475569;
    --tone-blue-bg: rgba(59,130,246,0.11);   --tone-blue-fg: #1d4ed8;
}
.dt-wrap {
    padding: 4px 0 48px;
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    color: var(--text, #0f172a);
}
/* Component display rules would otherwise beat the [hidden] attribute */
.dt-wrap [hidden] { display: none !important; }
body.dark-mode .dt-wrap, body.dark-mode .dt-drawer {
    --dt-shadow-sm: 0 1px 2px rgba(0,0,0,0.3);
    --dt-shadow-md: 0 12px 30px -12px rgba(0,0,0,0.6);
    --dt-shadow-lg: 0 24px 60px -16px rgba(0,0,0,0.7);
    --tone-red-fg: #f87171; --tone-amber-fg: #fbbf24; --tone-green-fg: #34d399;
    --tone-violet-fg: #a78bfa; --tone-slate-fg: #cbd5e1; --tone-blue-fg: #93c5fd;
}
.dt-wrap *, .dt-drawer * { box-sizing: border-box; }

/* ── Tones ── */
.tone-red    { background: var(--tone-red-bg);    color: var(--tone-red-fg); }
.tone-amber  { background: var(--tone-amber-bg);  color: var(--tone-amber-fg); }
.tone-green  { background: var(--tone-green-bg);  color: var(--tone-green-fg); }
.tone-violet { background: var(--tone-violet-bg); color: var(--tone-violet-fg); }
.tone-slate  { background: var(--tone-slate-bg);  color: var(--tone-slate-fg); }
.tone-blue   { background: var(--tone-blue-bg);   color: var(--tone-blue-fg); }

/* ── Hero ── */
.dt-hero {
    position: relative; overflow: hidden;
    background: linear-gradient(135deg, #f43f3f 0%, #e02424 45%, #a51d1d 100%);
    border-radius: 20px; padding: 28px 32px; margin-bottom: 18px; color: #fff;
    display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap;
    box-shadow: 0 18px 40px -18px rgba(220,38,38,0.55);
}
.dt-hero::before, .dt-hero::after {
    content: ''; position: absolute; border-radius: 50%; pointer-events: none;
    background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
}
.dt-hero::before { width: 320px; height: 320px; top: -140px; right: -60px; }
.dt-hero::after  { width: 220px; height: 220px; bottom: -120px; left: 32%; }
.dt-hero > * { position: relative; z-index: 1; }
.dt-eyebrow {
    display: inline-flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 700;
    text-transform: uppercase; letter-spacing: .9px; padding: 5px 12px; border-radius: 99px;
    background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.2); margin-bottom: 12px;
}
.dt-eyebrow i { font-size: 10px; }
.dt-hero h1 { margin: 0 0 6px; font-size: 28px; font-weight: 800; letter-spacing: -.4px; line-height: 1.15; }
.dt-hero-sub { margin: 0; font-size: 13.5px; color: rgba(255,255,255,0.82); display: flex; flex-wrap: wrap; gap: 6px 16px; }
.dt-hero-sub span { display: inline-flex; align-items: center; gap: 6px; }
.dt-hero-sub strong { color: #fff; }
.dt-hero-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.dt-scope {
    display: inline-flex; padding: 4px; gap: 4px; border-radius: 12px;
    background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22); backdrop-filter: blur(8px);
}
.dt-scope a {
    display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; border-radius: 9px;
    font-size: 12.5px; font-weight: 700; color: rgba(255,255,255,0.88); text-decoration: none; transition: all .2s;
}
.dt-scope a:hover { color: #fff; background: rgba(255,255,255,0.1); }
.dt-scope a.is-active { background: #fff; color: var(--dt-brand-dark); box-shadow: 0 4px 14px rgba(0,0,0,0.18); }
.dt-hero-cta {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 12px;
    background: #fff; color: var(--dt-brand-dark); font-size: 13px; font-weight: 800; text-decoration: none;
    box-shadow: 0 6px 18px rgba(0,0,0,0.2); transition: transform .2s, box-shadow .2s;
}
.dt-hero-cta:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,0.25); }

/* ── KPI tiles (also the status filter) ── */
.dt-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
.dt-kpi {
    display: flex; align-items: center; gap: 12px; text-align: left; cursor: pointer;
    padding: 14px 16px; border-radius: 16px; font: inherit; color: inherit;
    background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); box-shadow: var(--dt-shadow-sm);
    transition: transform .18s, box-shadow .18s, border-color .18s;
}
.dt-kpi:hover { transform: translateY(-2px); box-shadow: var(--dt-shadow-md); }
.dt-kpi.is-active { border-color: var(--dt-brand); box-shadow: 0 0 0 3px var(--dt-brand-soft), var(--dt-shadow-md); }
.dt-kpi-icon {
    width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center; font-size: 15px;
}
.dt-kpi-icon.tone-brand  { background: var(--dt-brand-soft); color: var(--dt-brand); }
.dt-kpi-icon.tone-danger { background: var(--tone-red-bg); color: var(--tone-red-fg); }
.dt-kpi-val { font-size: 22px; font-weight: 800; line-height: 1; letter-spacing: -.3px; }
.dt-kpi-label { font-size: 11.5px; font-weight: 600; color: var(--text3, #94a3b8); margin-top: 4px; white-space: nowrap; }
.dt-kpi.is-alert .dt-kpi-val { color: var(--tone-red-fg); }

/* ── Toolbar ── */
.dt-toolbar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 10px; margin-bottom: 14px; border-radius: 16px;
    background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); box-shadow: var(--dt-shadow-sm);
}
.dt-search { position: relative; flex: 1 1 260px; min-width: 200px; }
.dt-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text3, #94a3b8); font-size: 13px; }
.dt-search kbd {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    font: 600 11px/1 'Inter', sans-serif; color: var(--text3, #94a3b8);
    border: 1px solid var(--border, #e2e6ef); border-bottom-width: 2px; border-radius: 6px; padding: 3px 6px;
}
.dt-input, .dt-select {
    height: 40px; border-radius: 11px; border: 1px solid var(--border, #e2e6ef);
    background: var(--card2, #f5f7fb); color: var(--text, #0f172a); font: inherit; font-size: 13px;
    transition: border-color .15s, box-shadow .15s, background .15s;
}
.dt-input { width: 100%; padding: 10px 12px; height: auto; }
.dt-search .dt-input { height: 40px; padding: 0 44px 0 36px; }
.dt-select { padding: 0 30px 0 12px; max-width: 220px; cursor: pointer; }
.dt-input:focus, .dt-select:focus { outline: none; border-color: var(--dt-brand); box-shadow: 0 0 0 3px var(--dt-brand-soft); background: var(--card, #fff); }.dt-view {
    display: inline-flex; padding: 3px; gap: 3px; border-radius: 11px;
    background: var(--card2, #f5f7fb); border: 1px solid var(--border, #e2e6ef);
}
.dt-view button {
    width: 36px; height: 32px; border-radius: 8px; border: none; cursor: pointer;
    background: transparent; color: var(--text3, #94a3b8); font-size: 13px; transition: all .15s;
}
.dt-view button.is-active { background: var(--card, #fff); color: var(--dt-brand); box-shadow: var(--dt-shadow-sm); }

.dt-meta { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 0 2px 12px; font-size: 12.5px; color: var(--text3, #94a3b8); }
.dt-meta strong { color: var(--text, #0f172a); }
.dt-link-btn { border: none; background: none; padding: 0; font: inherit; font-weight: 700; color: var(--dt-brand); cursor: pointer; }
.dt-link-btn:hover { text-decoration: underline; }

/* ── Task cards ── */
.dt-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.dt-card {
    position: relative; display: flex; flex-direction: column; gap: 12px; cursor: pointer;
    padding: 18px 18px 14px 20px; border-radius: 18px; overflow: hidden;
    background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); box-shadow: var(--dt-shadow-sm);
    transition: transform .2s, box-shadow .2s, border-color .2s;
}
.dt-card:hover { transform: translateY(-3px); box-shadow: var(--dt-shadow-md); border-color: rgba(239,68,68,0.35); }
.dt-card:focus-visible { outline: 3px solid var(--dt-brand-soft); outline-offset: 2px; border-color: var(--dt-brand); }
.dt-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--prio, #cbd5e1); }
.prio-urgent { --prio: #dc2626; }
.prio-high   { --prio: #f97316; }
.prio-normal, .prio-medium { --prio: #3b82f6; }
.prio-low    { --prio: #94a3b8; }
.dt-card.is-overdue { border-color: rgba(220,38,38,0.35); }

.dt-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.dt-client { display: flex; align-items: center; gap: 10px; min-width: 0; }
.dt-logo {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; overflow: hidden;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 800; color: #fff; border: 1px solid var(--border, #e2e6ef);
}
.dt-logo img { width: 100%; height: 100%; object-fit: cover; background: #fff; }
.dt-client-name { font-size: 12.5px; font-weight: 700; color: var(--text2, #475569); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dt-client-type { font-size: 11px; font-weight: 600; color: var(--text3, #94a3b8); text-transform: capitalize; }
.dt-status {
    display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0;
    font-size: 11px; font-weight: 800; padding: 5px 10px; border-radius: 99px; white-space: nowrap;
}
.dt-status i { font-size: 10px; }
.dt-card-title {
    margin: 0; font-size: 15.5px; font-weight: 800; line-height: 1.35; letter-spacing: -.1px; color: var(--text, #0f172a);
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.dt-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.dt-chip {
    display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700;
    padding: 4px 9px; border-radius: 8px; background: var(--card2, #f5f7fb); color: var(--text2, #475569);
    border: 1px solid var(--border, #e2e6ef); white-space: nowrap;
}
.dt-chip .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--prio, #94a3b8); }
.dt-chip.tone-red, .dt-chip.tone-amber, .dt-chip.tone-violet, .dt-chip.tone-slate { border-color: transparent; }

.dt-deadline {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 9px 12px; border-radius: 12px; font-size: 12.5px; font-weight: 600;
    background: var(--card2, #f5f7fb); color: var(--text2, #475569);
}
.dt-deadline > span { display: inline-flex; align-items: center; gap: 7px; }
.dt-countdown { font-size: 11.5px; font-weight: 800; padding: 3px 9px; border-radius: 99px; }
.dt-deadline.is-danger { background: var(--tone-red-bg); color: var(--tone-red-fg); }
.dt-deadline.is-danger .dt-countdown { background: var(--tone-red-fg); color: #fff; }
.dt-deadline.is-warn .dt-countdown { background: var(--tone-amber-bg); color: var(--tone-amber-fg); }
.dt-deadline.is-ok .dt-countdown { background: var(--tone-green-bg); color: var(--tone-green-fg); }

.dt-assets-row { display: flex; align-items: center; gap: 10px; font-size: 11.5px; font-weight: 700; color: var(--text3, #94a3b8); }
.dt-bar { flex: 1; height: 6px; border-radius: 99px; background: var(--card2, #eef1f6); overflow: hidden; }
.dt-bar span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #10b981, #34d399); }
.dt-bar.is-partial span { background: linear-gradient(90deg, #f59e0b, #fbbf24); }

.dt-card-foot {
    display: flex; align-items: center; gap: 10px; margin-top: auto;
    padding-top: 12px; border-top: 1px dashed var(--border, #e2e6ef);
}
.dt-people { display: flex; align-items: center; }
.dt-avatar {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 10.5px; font-weight: 800; color: #fff; border: 2px solid var(--card, #fff);
}
.dt-people .dt-avatar + .dt-avatar { margin-left: -5px; }
.dt-foot-text { font-size: 11.5px; color: var(--text3, #94a3b8); font-weight: 600; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dt-hours { margin-left: auto; display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 800; color: var(--text2, #475569); white-space: nowrap; }
.dt-hours i { color: var(--text3, #94a3b8); }

.dt-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px;
    height: 34px; padding: 0 14px; border-radius: 10px; cursor: pointer; white-space: nowrap;
    font: inherit; font-size: 12.5px; font-weight: 800; text-decoration: none;
    border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text, #0f172a);
    transition: all .15s;
}
.dt-btn:hover { border-color: var(--dt-brand); color: var(--dt-brand); }
.dt-btn-primary { background: linear-gradient(135deg, #f43f3f, #dc2626); border-color: transparent; color: #fff; box-shadow: 0 6px 16px -6px rgba(220,38,38,0.6); }
.dt-btn-primary:hover { color: #fff; filter: brightness(1.06); transform: translateY(-1px); }
.dt-btn-amber { background: linear-gradient(135deg, #f59e0b, #d97706); border-color: transparent; color: #fff; box-shadow: 0 6px 16px -6px rgba(217,119,6,0.5); }
.dt-btn-amber:hover { color: #fff; filter: brightness(1.06); transform: translateY(-1px); }
.dt-btn-success { background: linear-gradient(135deg, #10b981, #059669); border-color: transparent; color: #fff; box-shadow: 0 6px 16px -6px rgba(5,150,105,0.5); }
.dt-btn-success:hover { color: #fff; filter: brightness(1.06); transform: translateY(-1px); }
.dt-btn-lg { height: 42px; padding: 0 18px; font-size: 13.5px; border-radius: 12px; }
.dt-btn[disabled] { opacity: .6; pointer-events: none; }

/* ── Board ── */
.dt-board { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(290px, 1fr); gap: 14px; overflow-x: auto; padding-bottom: 8px; }
.dt-col { background: var(--card2, #f5f7fb); border: 1px solid var(--border, #e2e6ef); border-radius: 18px; padding: 12px; min-height: 160px; }
.dt-col-head { display: flex; align-items: center; justify-content: space-between; padding: 4px 6px 12px; font-size: 13px; font-weight: 800; }
.dt-col-head span { display: inline-flex; align-items: center; gap: 8px; }
.dt-col-dot { width: 9px; height: 9px; border-radius: 50%; }
.dt-col-count { font-size: 11.5px; font-weight: 800; color: var(--text3, #94a3b8); background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); padding: 2px 9px; border-radius: 99px; }
.dt-col-body { display: flex; flex-direction: column; gap: 12px; }
.dt-col-empty { font-size: 12px; color: var(--text3, #94a3b8); text-align: center; padding: 22px 8px; border: 1px dashed var(--border, #e2e6ef); border-radius: 14px; }
.dt-board .dt-card { padding: 14px 14px 12px 16px; gap: 10px; }

/* ── Empty states ── */
.dt-empty { text-align: center; padding: 56px 20px; border-radius: 20px; background: var(--card, #fff); border: 1px dashed var(--border, #e2e6ef); }
.dt-empty-icon { width: 64px; height: 64px; margin: 0 auto 14px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 24px; background: var(--dt-brand-soft); color: var(--dt-brand); }
.dt-empty h3 { margin: 0 0 6px; font-size: 17px; font-weight: 800; }
.dt-empty p { margin: 0 0 16px; font-size: 13px; color: var(--text3, #94a3b8); }

/* ── Detail drawer ── */
.dt-overlay { position: fixed; inset: 0; z-index: 9995; background: rgba(15,23,42,0.45); backdrop-filter: blur(3px); opacity: 0; visibility: hidden; transition: opacity .25s, visibility .25s; }
.dt-overlay.is-open { opacity: 1; visibility: visible; }
.dt-drawer {
    position: fixed; top: 0; right: 0; bottom: 0; z-index: 10000; width: min(600px, 100vw);
    display: flex; flex-direction: column; background: var(--card, #fff); color: var(--text, #0f172a);
    box-shadow: var(--dt-shadow-lg, -20px 0 60px rgba(0,0,0,.3)); border-left: 1px solid var(--border, #e2e6ef);
    transform: translateX(104%); transition: transform .32s cubic-bezier(.22,.9,.3,1);
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
}
.dt-drawer.is-open { transform: translateX(0); }
.dt-drawer-close {
    position: absolute; top: 16px; right: 16px; z-index: 2; width: 36px; height: 36px; border-radius: 10px;
    border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text2, #475569); cursor: pointer; font-size: 15px;
}
.dt-drawer-close:hover { color: var(--dt-brand, #ef4444); border-color: var(--dt-brand, #ef4444); }
.dt-drawer-content { display: flex; flex-direction: column; height: 100%; min-height: 0; }
.dt-d-head { padding: 22px 64px 18px 24px; border-bottom: 1px solid var(--border, #e2e6ef); background: linear-gradient(180deg, rgba(239,68,68,0.06), transparent); }
.dt-d-title { margin: 12px 0 10px; font-size: 21px; font-weight: 800; line-height: 1.3; letter-spacing: -.3px; }
.dt-d-body { flex: 1; overflow-y: auto; padding: 18px 24px 24px; display: flex; flex-direction: column; gap: 18px; }
.dt-d-foot { padding: 14px 24px; border-top: 1px solid var(--border, #e2e6ef); display: flex; gap: 10px; justify-content: space-between; align-items: center; flex-wrap: wrap; background: var(--card, #fff); }
.dt-section-title { display: flex; align-items: center; gap: 8px; margin: 0 0 10px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--text3, #94a3b8); }
.dt-section-title i { color: var(--dt-brand, #ef4444); font-size: 12px; }

.dt-banner { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px; font-size: 13px; font-weight: 600; }
.dt-banner i { font-size: 16px; }
.dt-banner.is-danger { background: var(--tone-red-bg); color: var(--tone-red-fg); }
.dt-banner.is-warn { background: var(--tone-amber-bg); color: var(--tone-amber-fg); }
.dt-banner.is-info { background: var(--tone-blue-bg); color: var(--tone-blue-fg); }
.dt-banner.is-muted { background: var(--card2, #f5f7fb); color: var(--text2, #475569); }

.dt-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.dt-fact { padding: 11px 12px; border-radius: 12px; background: var(--card2, #f5f7fb); border: 1px solid var(--border, #e2e6ef); min-width: 0; }
.dt-fact-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--text3, #94a3b8); margin-bottom: 4px; }
.dt-fact-val { font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.dt-steps { display: grid; grid-template-columns: repeat(4, 1fr); position: relative; }
.dt-step { position: relative; text-align: center; padding: 0 4px; }
.dt-step::before { content: ''; position: absolute; top: 14px; left: -50%; right: 50%; height: 3px; background: var(--border, #e2e6ef); }
.dt-step:first-child::before { display: none; }
.dt-step.is-done::before, .dt-step.is-current::before { background: linear-gradient(90deg, #f43f3f, #dc2626); }
.dt-step-dot {
    position: relative; z-index: 1; width: 30px; height: 30px; margin: 0 auto 8px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 12px;
    background: var(--card, #fff); border: 2px solid var(--border, #e2e6ef); color: var(--text3, #94a3b8);
}
.dt-step.is-done .dt-step-dot { background: #dc2626; border-color: #dc2626; color: #fff; }
.dt-step.is-current .dt-step-dot { border-color: #dc2626; color: #dc2626; box-shadow: 0 0 0 5px rgba(239,68,68,0.15); }
.dt-step-label { font-size: 12px; font-weight: 800; }
.dt-step-time { font-size: 11px; color: var(--text3, #94a3b8); margin-top: 2px; }

.dt-text { font-size: 13.5px; line-height: 1.65; color: var(--text2, #475569); white-space: pre-line; margin: 0; }
.dt-modules { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
.dt-modules li { display: flex; gap: 10px; align-items: flex-start; font-size: 13px; font-weight: 600; padding: 10px 12px; border-radius: 12px; background: var(--card2, #f5f7fb); border: 1px solid var(--border, #e2e6ef); }
.dt-modules li span { flex-shrink: 0; width: 22px; height: 22px; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; background: var(--dt-brand-soft, rgba(239,68,68,.09)); color: var(--dt-brand, #ef4444); }
.dt-tags { display: flex; flex-wrap: wrap; gap: 6px; }

.dt-checklist { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px; }
.dt-check { display: flex; align-items: center; gap: 9px; padding: 10px 12px; border-radius: 12px; font-size: 13px; font-weight: 700; border: 1px solid var(--border, #e2e6ef); }
.dt-check i { width: 22px; height: 22px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
.dt-check.is-yes i { background: var(--tone-green-bg); color: var(--tone-green-fg); }
.dt-check.is-no { color: var(--text3, #94a3b8); border-style: dashed; }
.dt-check.is-no i { background: var(--tone-red-bg); color: var(--tone-red-fg); }

.dt-links { display: grid; gap: 8px; }
.dt-linkbox { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 12px; border: 1px solid var(--border, #e2e6ef); font-size: 13px; font-weight: 600; color: var(--text, #0f172a); text-decoration: none; min-width: 0; }
.dt-linkbox span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dt-linkbox:hover { border-color: var(--dt-brand, #ef4444); color: var(--dt-brand, #ef4444); }

.dt-panel { padding: 16px; border-radius: 16px; border: 1px solid var(--border, #e2e6ef); background: var(--card2, #f5f7fb); }
.dt-field { margin-bottom: 12px; }
.dt-field label { display: block; font-size: 11.5px; font-weight: 700; color: var(--text2, #475569); margin-bottom: 6px; }
.dt-field .dt-input, .dt-comment-form .dt-input { background: var(--card, #fff); }
textarea.dt-input { resize: vertical; min-height: 76px; line-height: 1.5; }

.dt-comments { display: flex; flex-direction: column; gap: 12px; }
.dt-comment { display: flex; gap: 10px; }
.dt-comment-bubble { flex: 1; min-width: 0; padding: 10px 12px; border-radius: 4px 14px 14px 14px; background: var(--card2, #f5f7fb); border: 1px solid var(--border, #e2e6ef); }
.dt-comment.is-revision .dt-comment-bubble { background: var(--tone-amber-bg); border-color: rgba(245,158,11,0.35); }
.dt-comment-meta { display: flex; justify-content: space-between; gap: 8px; font-size: 11.5px; margin-bottom: 4px; }
.dt-comment-meta strong { font-weight: 800; }
.dt-comment-meta span { color: var(--text3, #94a3b8); white-space: nowrap; }
.dt-comment-body { font-size: 13px; line-height: 1.55; white-space: pre-line; word-wrap: break-word; }
.dt-comment-form { display: flex; gap: 8px; align-items: flex-end; margin-top: 4px; }
.dt-comment-form textarea { min-height: 42px; flex: 1; }
.dt-muted { font-size: 12.5px; color: var(--text3, #94a3b8); }

/* ── Responsive ── */
@media (max-width: 1200px) { .dt-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 768px) {
    .dt-hero { padding: 22px 20px; border-radius: 18px; }
    .dt-hero h1 { font-size: 22px; }
    .dt-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dt-grid { grid-template-columns: 1fr; }
    .dt-select { flex: 1 1 140px; max-width: none; }
    .dt-search kbd { display: none; }
    .dt-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .dt-d-head, .dt-d-body, .dt-d-foot { padding-left: 18px; padding-right: 18px; }
}
@media (prefers-reduced-motion: reduce) {
    .dt-card, .dt-kpi, .dt-drawer, .dt-overlay, .dt-btn { transition: none; }
}
</style>

<div class="dt-wrap">

    {{-- ── Hero ── --}}
    <section class="dt-hero">
        <div>
            <div class="dt-eyebrow"><i class="fas fa-code"></i> Developer Workspace</div>
            <h1>{{ $scope === 'all' ? 'Team Projects & Tasks' : 'My Projects & Tasks' }}</h1>
            <p class="dt-hero-sub">
                <span><i class="fas fa-bolt"></i> <strong>{{ $activeCount }}</strong> active</span>
                <span><i class="fas fa-calendar-week"></i> <strong>{{ $dueThisWeek }}</strong> due this week</span>
                @if($counts['overdue'] > 0)
                    <span><i class="fas fa-fire"></i> <strong>{{ $counts['overdue'] }}</strong> overdue</span>
                @endif
            </p>
        </div>
        <div class="dt-hero-actions">
            <div class="dt-scope" role="tablist" aria-label="Task scope">
                <a href="{{ route('developer.tasks', array_merge(request()->except('scope', 'task'), ['scope' => 'my'])) }}" class="{{ $scope !== 'all' ? 'is-active' : '' }}" role="tab" aria-selected="{{ $scope !== 'all' ? 'true' : 'false' }}">
                    <i class="fas fa-user-check"></i> My Tasks
                </a>
                <a href="{{ route('developer.tasks', array_merge(request()->except('scope', 'task'), ['scope' => 'all'])) }}" class="{{ $scope === 'all' ? 'is-active' : '' }}" role="tab" aria-selected="{{ $scope === 'all' ? 'true' : 'false' }}">
                    <i class="fas fa-users"></i> Team
                </a>
            </div>
            <button type="button" class="dt-hero-cta" onclick="openSelfTaskModal({ defaultType: 'website' })" style="background:#4f46e5;color:#fff;border:none;cursor:pointer">
                <i class="fas fa-plus"></i> New Task
            </button>
            <a href="{{ route('developer.daily-log') }}" class="dt-hero-cta"><i class="fas fa-clipboard-list"></i> Log today's work</a>
        </div>
    </section>

    {{-- ── KPI tiles: click to filter ── --}}
    <div class="dt-kpis">
        @foreach($kpis as $kpi)
            <button type="button" class="dt-kpi {{ $kpi['key'] === 'all' ? 'is-active' : '' }} {{ $kpi['key'] === 'overdue' && $counts['overdue'] > 0 ? 'is-alert' : '' }}" data-filter="{{ $kpi['key'] }}" aria-pressed="{{ $kpi['key'] === 'all' ? 'true' : 'false' }}">
                <span class="dt-kpi-icon tone-{{ $kpi['tone'] }}"><i class="fas {{ $kpi['icon'] }}"></i></span>
                <span>
                    <div class="dt-kpi-val">{{ $counts[$kpi['key']] }}</div>
                    <div class="dt-kpi-label">{{ $kpi['label'] }}</div>
                </span>
            </button>
        @endforeach
    </div>

    @if($rows->isEmpty())
        <div class="dt-empty">
            <div class="dt-empty-icon"><i class="fas fa-mug-hot"></i></div>
            <h3>{{ $scope === 'all' ? 'No team projects yet' : 'No projects assigned' }}</h3>
            <p>{{ $scope === 'all' ? 'Nothing has been assigned to the development team yet.' : "You don't have any assigned tasks right now. Check back later!" }}</p>
        </div>
    @else
        {{-- ── Toolbar ── --}}
        <div class="dt-toolbar">
            <label class="dt-search">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search" class="dt-input" id="dtSearch" placeholder="Search projects, clients, tech..." value="{{ request('search') }}" autocomplete="off" aria-label="Search tasks">
                <kbd>/</kbd>
            </label>
            @if($clientOptions->count() > 1)
                <select class="dt-select" id="dtClient" aria-label="Filter by client">
                    <option value="">All clients</option>
                    @foreach($clientOptions as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            @endif
            @if($scope === 'all' && $assigneeOptions->count() > 1)
                <select class="dt-select" id="dtAssignee" aria-label="Filter by developer">
                    <option value="">All developers</option>
                    @foreach($assigneeOptions as $assignee)
                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                    @endforeach
                </select>
            @endif
            <select class="dt-select" id="dtSort" aria-label="Sort tasks">
                <option value="smart">Sort: Recommended</option>
                <option value="deadline">Sort: Deadline</option>
                <option value="priority">Sort: Priority</option>
                <option value="newest">Sort: Newest</option>
                <option value="client">Sort: Client A–Z</option>
            </select>
            <div class="dt-view" role="group" aria-label="Layout">
                <button type="button" data-view="grid" class="is-active" title="Grid view" aria-label="Grid view"><i class="fas fa-grip"></i></button>
                <button type="button" data-view="board" title="Board view" aria-label="Board view"><i class="fas fa-table-columns"></i></button>
            </div>
        </div>

        <div class="dt-meta">
            <span>Showing <strong id="dtShown">{{ $rows->count() }}</strong> of {{ $rows->count() }} {{ Str::plural('task', $rows->count()) }}</span>
            <button type="button" class="dt-link-btn" id="dtClear" hidden>Clear filters</button>
        </div>

        {{-- ── Cards (moved between grid and board by the script) ── --}}
        <div class="dt-grid" id="dtGrid">
            @foreach($rows as $row)
                @php $task = $row->task; @endphp
                <article class="dt-card prio-{{ $row->priority }} {{ $row->isOverdue ? 'is-overdue' : '' }}"
                         tabindex="0" role="button" aria-label="Open {{ $task->title ?? 'task' }}"
                         data-id="{{ $task->id }}"
                         data-group="{{ $row->meta['group'] }}"
                         data-overdue="{{ $row->isOverdue ? '1' : '0' }}"
                         data-client="{{ $task->client_id }}"
                         data-assignee="{{ $task->assigned_to }}"
                         data-search="{{ $row->search }}"
                         data-order="{{ $row->index }}"
                         data-deadline="{{ $task->dev_deadline ? $task->dev_deadline->format('Y-m-d') : '9999-12-31' }}"
                         data-priority="{{ $row->priorityRank }}"
                         data-created="{{ $task->created_at?->timestamp ?? 0 }}"
                         data-client-name="{{ mb_strtolower($task->client->name ?? 'zzz') }}">
                    <div class="dt-card-top">
                        <div class="dt-client">
                            <span class="dt-logo" style="background: {{ $avatarColor($task->client->name ?? '') }}">
                                @if($task->client && $task->client->logo)
                                    <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" loading="lazy">
                                @else
                                    {{ $initials($task->client->name ?? '?') }}
                                @endif
                            </span>
                            <div style="min-width:0">
                                <div class="dt-client-name">{{ $task->client->name ?? 'No client' }}</div>
                                <div class="dt-client-type">{{ str_replace('_', ' ', $task->type ?? 'project') }}</div>
                            </div>
                        </div>
                        <span class="dt-status tone-{{ $row->meta['tone'] }}"><i class="fas {{ $row->meta['icon'] }}"></i> {{ $row->meta['label'] }}</span>
                    </div>

                    <h3 class="dt-card-title">{{ $task->title ?? 'Untitled Project' }}</h3>

                    <div class="dt-chips">
                        <span class="dt-chip"><span class="dot"></span> {{ ucfirst($row->priority) }}</span>
                        @if(($task->revision_count ?? 0) > 0)
                            <span class="dt-chip tone-amber"><i class="fas fa-rotate"></i> Revision {{ $task->revision_count }}</span>
                        @endif
                        @if($task->is_paused)
                            <span class="dt-chip tone-slate"><i class="fas fa-pause"></i> Paused</span>
                        @endif
                        @foreach($row->techList->take(2) as $tech)
                            <span class="dt-chip">{{ $tech }}</span>
                        @endforeach
                        @if($row->techList->count() > 2)
                            <span class="dt-chip">+{{ $row->techList->count() - 2 }}</span>
                        @endif
                    </div>

                    @if($task->dev_deadline)
                        <div class="dt-deadline is-{{ $row->deadline['tone'] }}">
                            <span><i class="far fa-calendar"></i> {{ $task->dev_deadline->format('M d, Y') }}</span>
                            @if($row->deadline['text'])
                                <span class="dt-countdown">{{ $row->deadline['text'] }}</span>
                            @endif
                        </div>
                    @else
                        <div class="dt-deadline"><span><i class="far fa-calendar"></i> No deadline set</span></div>
                    @endif

                    @if($row->assets->isNotEmpty())
                        <div class="dt-assets-row" title="Client assets & access received">
                            <span><i class="fas fa-box-open"></i> Assets</span>
                            <div class="dt-bar {{ $row->assetsReady < $row->assets->count() ? 'is-partial' : '' }}"><span style="width: {{ round($row->assetsReady / $row->assets->count() * 100) }}%"></span></div>
                            <span>{{ $row->assetsReady }}/{{ $row->assets->count() }}</span>
                        </div>
                    @endif

                    <div class="dt-card-foot">
                        <div class="dt-people">
                            @if($task->creator)
                                <span class="dt-avatar" style="background: {{ $avatarColor($task->creator->name) }}" title="Created by {{ $task->creator->name }}">{{ $initials($task->creator->name) }}</span>
                            @endif
                            @if($scope === 'all' && $task->assignee)
                                <span class="dt-avatar" style="background: {{ $avatarColor($task->assignee->name) }}" title="Assigned to {{ $task->assignee->name }}">{{ $initials($task->assignee->name) }}</span>
                            @endif
                        </div>
                        <span class="dt-foot-text">
                            {{ $scope === 'all' && $task->assignee ? $task->assignee->name : ($task->creator ? 'By ' . $task->creator->name : '') }}
                        </span>
                        <span class="dt-hours" title="Hours logged in daily logs"><i class="fas fa-stopwatch"></i> {{ $row->hours }}h</span>
                        @if($row->isMine && $task->status === 'todo')
                            <button type="button" class="dt-btn dt-btn-primary" data-start="{{ route('developer.tasks.start', $task) }}" data-task-id="{{ $task->id }}"><i class="fas fa-play"></i> Start</button>
                        @elseif($row->isMine && $task->status === 'inprogress')
                            <button type="button" class="dt-btn dt-btn-primary" data-open-submit="{{ $task->id }}"><i class="fas fa-paper-plane"></i> Submit</button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="dt-board" id="dtBoard" hidden>
            @foreach($boardColumns as $group => $label)
                @continue($group === 'hold' && $counts['hold'] === 0)
                @php
                    $dotColor = ['todo' => '#94a3b8', 'inprogress' => '#ef4444', 'review' => '#f59e0b', 'hold' => '#64748b', 'done' => '#10b981'][$group];
                @endphp
                <section class="dt-col" data-col="{{ $group }}">
                    <div class="dt-col-head">
                        <span><span class="dt-col-dot" style="background: {{ $dotColor }}"></span> {{ $label }}</span>
                        <span class="dt-col-count" data-col-count>0</span>
                    </div>
                    <div class="dt-col-body"></div>
                    <div class="dt-col-empty" hidden>Nothing here</div>
                </section>
            @endforeach
        </div>

        <div class="dt-empty" id="dtNoResults" hidden>
            <div class="dt-empty-icon"><i class="fas fa-filter-circle-xmark"></i></div>
            <h3>No tasks match these filters</h3>
            <p>Try a different search term or clear the filters.</p>
            <button type="button" class="dt-btn" data-clear-filters><i class="fas fa-rotate-left"></i> Clear filters</button>
        </div>
    @endif
</div>

{{-- ── Detail drawer content, one template per task ── --}}
@foreach($rows as $row)
    @php
        $task = $row->task;
        $steps = [
            ['label' => 'Assigned',  'icon' => 'fa-inbox',        'at' => $task->created_at],
            ['label' => 'Started',   'icon' => 'fa-play',         'at' => $task->started_at],
            ['label' => 'Submitted', 'icon' => 'fa-paper-plane',  'at' => $task->submitted_at],
            ['label' => 'Completed', 'icon' => 'fa-flag-checkered', 'at' => $task->completed_at],
        ];
        $comments = $task->comments->sortBy('created_at');
        $refLinks = collect(is_array($task->reference_links) ? $task->reference_links : [])->filter(fn ($l) => is_string($l) && filled($l));
    @endphp
    <template id="dt-detail-{{ $task->id }}">
        <div class="dt-d-head">
            <div class="dt-client">
                <span class="dt-logo" style="background: {{ $avatarColor($task->client->name ?? '') }}">
                    @if($task->client && $task->client->logo)
                        <img src="{{ asset('storage/' . $task->client->logo) }}" alt="">
                    @else
                        {{ $initials($task->client->name ?? '?') }}
                    @endif
                </span>
                <div style="min-width:0">
                    <div class="dt-client-name">{{ $task->client->name ?? 'No client' }}</div>
                    <div class="dt-client-type">{{ str_replace('_', ' ', $task->type ?? 'project') }} · #{{ $task->id }}</div>
                </div>
            </div>
            <h2 class="dt-d-title">{{ $task->title ?? 'Untitled Project' }}</h2>
            <div class="dt-chips">
                <span class="dt-status tone-{{ $row->meta['tone'] }}"><i class="fas {{ $row->meta['icon'] }}"></i> {{ $row->meta['label'] }}</span>
                <span class="dt-chip prio-{{ $row->priority }}"><span class="dot"></span> {{ ucfirst($row->priority) }} priority</span>
                @if(($task->revision_count ?? 0) > 0)
                    <span class="dt-chip tone-amber"><i class="fas fa-rotate"></i> Revision {{ $task->revision_count }}</span>
                @endif
            </div>
        </div>

        <div class="dt-d-body">
            @if($row->isOverdue)
                <div class="dt-banner is-danger"><i class="fas fa-fire"></i> {{ $row->deadline['text'] }}. The deadline was {{ $task->dev_deadline->format('l, M d') }}.</div>
            @elseif($row->deadline && $row->deadline['tone'] === 'warn')
                <div class="dt-banner is-warn"><i class="fas fa-triangle-exclamation"></i> {{ $row->deadline['text'] }}: the deadline is {{ $task->dev_deadline->format('l, M d') }}.</div>
            @endif
            @if($task->status === 'on_hold')
                <div class="dt-banner is-muted"><i class="fas fa-circle-pause"></i> On hold{{ $task->on_hold_reason ? ': ' . $task->on_hold_reason : '' }}</div>
            @elseif($task->is_paused)
                <div class="dt-banner is-muted"><i class="fas fa-pause"></i> Paused{{ $task->pause_reason ? ': ' . $task->pause_reason : '' }}</div>
            @endif

            <div class="dt-facts">
                <div class="dt-fact"><div class="dt-fact-label">Dev deadline</div><div class="dt-fact-val">{{ $task->dev_deadline?->format('M d, Y') ?? 'Not set' }}</div></div>
                <div class="dt-fact"><div class="dt-fact-label">Launch date</div><div class="dt-fact-val">{{ $task->launch_date?->format('M d, Y') ?? 'Not set' }}</div></div>
                <div class="dt-fact"><div class="dt-fact-label">Hours logged</div><div class="dt-fact-val">{{ $row->hours }} h</div></div>
                <div class="dt-fact"><div class="dt-fact-label">Strategist</div><div class="dt-fact-val">{{ $task->creator->name ?? 'N/A' }}</div></div>
                <div class="dt-fact"><div class="dt-fact-label">Developer</div><div class="dt-fact-val">{{ $task->assignee->name ?? 'Unassigned' }}</div></div>
                <div class="dt-fact"><div class="dt-fact-label">Start date</div><div class="dt-fact-val">{{ $task->project_start_date?->format('M d, Y') ?? 'Not set' }}</div></div>
            </div>

            <div>
                <div class="dt-section-title"><i class="fas fa-route"></i> Progress</div>
                <div class="dt-steps">
                    @foreach($steps as $i => $step)
                        @php $state = $i + 1 < $row->progressStep ? 'is-done' : ($i + 1 === $row->progressStep ? ($row->progressStep === 4 ? 'is-done' : 'is-current') : ''); @endphp
                        <div class="dt-step {{ $state }}">
                            <div class="dt-step-dot"><i class="fas {{ $state === 'is-done' ? 'fa-check' : $step['icon'] }}"></i></div>
                            <div class="dt-step-label">{{ $step['label'] }}</div>
                            <div class="dt-step-time">{{ $step['at'] ? $step['at']->format('M d, H:i') : '—' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($task->brief || $task->project_notes)
                <div>
                    <div class="dt-section-title"><i class="fas fa-file-lines"></i> Brief</div>
                    @if($task->brief)<p class="dt-text">{{ $task->brief }}</p>@endif
                    @if($task->project_notes)<p class="dt-text" style="margin-top:8px">{{ $task->project_notes }}</p>@endif
                </div>
            @endif

            @if($row->modules->isNotEmpty())
                <div>
                    <div class="dt-section-title"><i class="fas fa-cubes"></i> Pages / Modules · {{ $row->modules->count() }}</div>
                    <ul class="dt-modules">
                        @foreach($row->modules as $i => $module)
                            <li><span>{{ $i + 1 }}</span> {{ $module }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($row->techList->isNotEmpty() || $task->business_type)
                <div>
                    <div class="dt-section-title"><i class="fas fa-microchip"></i> Stack</div>
                    <div class="dt-tags">
                        @foreach($row->techList as $tech)
                            <span class="dt-chip tone-blue">{{ $tech }}</span>
                        @endforeach
                        @if($task->business_type)
                            <span class="dt-chip"><i class="fas fa-briefcase"></i> {{ $task->business_type }}</span>
                        @endif
                    </div>
                </div>
            @endif

            @if($row->assets->isNotEmpty())
                <div>
                    <div class="dt-section-title"><i class="fas fa-box-open"></i> Assets & Access · {{ $row->assetsReady }}/{{ $row->assets->count() }} ready</div>
                    <div class="dt-checklist">
                        @foreach($row->assets as $label => $received)
                            <div class="dt-check {{ $received ? 'is-yes' : 'is-no' }}">
                                <i class="fas {{ $received ? 'fa-check' : 'fa-xmark' }}"></i> {{ $label }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($refLinks->isNotEmpty())
                <div>
                    <div class="dt-section-title"><i class="fas fa-link"></i> Reference links</div>
                    <div class="dt-links">
                        @foreach($refLinks as $link)
                            <a href="{{ $link }}" target="_blank" rel="noopener" class="dt-linkbox"><i class="fas fa-arrow-up-right-from-square"></i> <span>{{ $link }}</span></a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($task->dev_submission_link)
                <div>
                    <div class="dt-section-title"><i class="fas fa-paper-plane"></i> Latest submission{{ $task->submitted_at ? ' · ' . $task->submitted_at->format('M d, H:i') : '' }}</div>
                    <div class="dt-links">
                        <a href="{{ $task->dev_submission_link }}" target="_blank" rel="noopener" class="dt-linkbox"><i class="fas fa-globe"></i> <span>{{ $task->dev_submission_link }}</span></a>
                    </div>
                    @if($task->dev_submission_notes)
                        <p class="dt-text" style="margin-top:8px">{{ $task->dev_submission_notes }}</p>
                    @endif
                </div>
            @endif

            @if($row->isMine)
                @if($task->status === 'inprogress')
                    <div class="dt-panel" id="dt-submit-{{ $task->id }}">
                        <div class="dt-section-title"><i class="fas fa-paper-plane"></i> Submit for review</div>
                        <form class="dt-submit-form" action="{{ route('developer.tasks.submit', $task) }}" method="POST" data-no-loader="true">
                            @csrf
                            <div class="dt-field">
                                <label>Live server / submission link</label>
                                <input type="url" name="link" class="dt-input" required placeholder="https://staging.example.com">
                            </div>
                            <div class="dt-field">
                                <label>Notes for the strategist (optional)</label>
                                <textarea name="notes" class="dt-input" placeholder="What changed, test credentials, known issues..."></textarea>
                            </div>
                            <button type="submit" class="dt-btn dt-btn-primary dt-btn-lg"><i class="fas fa-paper-plane"></i> Submit for review</button>
                        </form>
                    </div>
                @elseif($task->status === 'review' || $task->status === 'pending_approval')
                    <div class="dt-banner is-info"><i class="fas fa-hourglass-half"></i> Submitted and waiting for review. You'll be notified if changes are requested.</div>
                @endif
            @endif

            <div>
                <div class="dt-section-title"><i class="fas fa-comments"></i> Discussion · <span data-comment-count>{{ $comments->count() }}</span></div>
                <div class="dt-comments" data-comments>
                    @forelse($comments as $comment)
                        @php $isRevision = str_starts_with($comment->body, '🔄'); @endphp
                        <div class="dt-comment {{ $isRevision ? 'is-revision' : '' }}">
                            <span class="dt-avatar" style="background: {{ $avatarColor($comment->user->name ?? '') }}">{{ $initials($comment->user->name ?? '?') }}</span>
                            <div class="dt-comment-bubble">
                                <div class="dt-comment-meta">
                                    <strong>{{ $comment->user->name ?? 'Unknown' }}{{ $isRevision ? ' · Revision request' : '' }}</strong>
                                    <span title="{{ $comment->created_at?->format('M d, Y H:i') }}">{{ $comment->created_at?->diffForHumans() }}</span>
                                </div>
                                <div class="dt-comment-body">{{ $comment->body }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="dt-muted" data-no-comments>No comments yet.</div>
                    @endforelse
                </div>
                @if($row->isMine)
                    <form class="dt-comment-form" action="{{ route('developer.tasks.comment', $task) }}" method="POST" data-no-loader="true">
                        @csrf
                        <textarea name="body" class="dt-input" rows="1" maxlength="2000" required placeholder="Ask a question or post an update..."></textarea>
                        <button type="submit" class="dt-btn dt-btn-primary" style="height:42px" aria-label="Send comment"><i class="fas fa-paper-plane"></i></button>
                    </form>
                @endif
            </div>
        </div>

        <div class="dt-d-foot">
            <span class="dt-muted">Updated {{ $task->updated_at?->diffForHumans() }}</span>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
                @if($row->isMine)
                    <a href="{{ route('developer.daily-log') }}" class="dt-btn"><i class="fas fa-clipboard-list"></i> Log work</a>
                @endif
                @if($row->isMine && $task->status === 'todo')
                    <button type="button" class="dt-btn dt-btn-primary" data-start="{{ route('developer.tasks.start', $task) }}" data-task-id="{{ $task->id }}"><i class="fas fa-play"></i> Start working</button>
                @endif
                @if($row->isMine && in_array($task->status, ['todo', 'inprogress']))
                    @if($task->is_paused)
                        <button type="button" class="dt-btn dt-btn-success" data-resume="{{ route('developer.tasks.resume', $task) }}" data-task-id="{{ $task->id }}"><i class="fas fa-play"></i> Resume working</button>
                    @elseif($task->status === 'inprogress')
                        <button type="button" class="dt-btn dt-btn-amber" data-open-pause="{{ route('developer.tasks.pause', $task) }}" data-task-id="{{ $task->id }}" data-task-title="{{ $task->title }}"><i class="fas fa-pause"></i> Pause task</button>
                    @endif
                @endif
            </div>
        </div>
    </template>
@endforeach

<div class="dt-overlay" id="dtOverlay"></div>
<aside class="dt-drawer" id="dtDrawer" role="dialog" aria-modal="true" aria-label="Task details" aria-hidden="true">
    <button type="button" class="dt-drawer-close" id="dtDrawerClose" aria-label="Close details"><i class="fas fa-xmark"></i></button>
    <div class="dt-drawer-content" id="dtDrawerContent"></div>
</aside>

{{-- ═══ PAUSE TASK MODAL ═══ --}}
<div id="dtPauseModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,0.55);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px">
    <div style="background:var(--card,#fff);border:1px solid var(--border,#e2e6ef);border-radius:20px;max-width:460px;width:100%;box-shadow:0 25px 65px rgba(0,0,0,0.25);overflow:hidden;animation:dtDrawerIn .25s ease">
        <div style="padding:24px 24px 0">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
                <h3 style="font-size:18px;font-weight:800;color:var(--text,#0f172a);display:flex;align-items:center;gap:8px;margin:0">
                    <span style="color:#d97706">⏸️</span> Pause Task
                </h3>
                <button type="button" id="dtClosePauseModal" style="background:none;border:none;font-size:22px;color:var(--text3,#94a3b8);cursor:pointer;line-height:1;padding:4px">&times;</button>
            </div>
            <div style="padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;font-size:12.5px;color:#92400e;line-height:1.5;margin-bottom:18px">
                <strong id="dtPauseTaskTitle">Task</strong> will be paused. Your progress will be saved and you can resume anytime.
            </div>
        </div>
        <form id="dtPauseForm" method="POST" action="">
            <div style="padding:0 24px 24px">
                <div style="margin-bottom:14px">
                    <label style="display:block;font-size:11.5px;font-weight:700;color:var(--text2,#475569);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">
                        What did you accomplish in this session? (optional)
                    </label>
                    <textarea name="work_logged" rows="3" class="dt-input" style="width:100%;box-sizing:border-box" placeholder="E.g. Implemented the API endpoints and set up database migrations..."></textarea>
                </div>
                <div style="margin-bottom:18px">
                    <label style="display:block;font-size:11.5px;font-weight:700;color:var(--text2,#475569);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">
                        Why are you pausing? <span style="color:#ef4444">*</span>
                    </label>
                    <textarea name="reason" required rows="2" class="dt-input" style="width:100%;box-sizing:border-box" placeholder="E.g. Waiting on client credentials, daily standup, shifting to urgent hotfix..."></textarea>
                </div>
                <div style="display:flex;gap:10px">
                    <button type="button" id="dtCancelPauseBtn" class="dt-btn" style="flex:1">Cancel</button>
                    <button type="submit" class="dt-btn dt-btn-amber" style="flex:1"><i class="fas fa-pause"></i> Pause Task</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const grid = document.getElementById('dtGrid');
    if (!grid) return;

    const board = document.getElementById('dtBoard');
    const cards = Array.from(grid.querySelectorAll('.dt-card'));
    const searchInput = document.getElementById('dtSearch');
    const clientSelect = document.getElementById('dtClient');
    const assigneeSelect = document.getElementById('dtAssignee');
    const sortSelect = document.getElementById('dtSort');
    const shownEl = document.getElementById('dtShown');
    const clearBtn = document.getElementById('dtClear');
    const noResults = document.getElementById('dtNoResults');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const store = {
        get(key) { try { return localStorage.getItem('devTasks.' + key); } catch (_) { return null; } },
        set(key, value) { try { localStorage.setItem('devTasks.' + key, value); } catch (_) {} },
    };

    const state = {
        filter: 'all',
        q: (searchInput?.value || '').trim().toLowerCase(),
        client: '',
        assignee: '',
        sort: store.get('sort') || 'smart',
        view: store.get('view') || 'grid',
    };
    if (sortSelect) sortSelect.value = state.sort;

    const sorters = {
        smart: (a, b) => a.dataset.order - b.dataset.order,
        deadline: (a, b) => a.dataset.deadline.localeCompare(b.dataset.deadline) || a.dataset.order - b.dataset.order,
        priority: (a, b) => a.dataset.priority - b.dataset.priority || a.dataset.deadline.localeCompare(b.dataset.deadline),
        newest: (a, b) => b.dataset.created - a.dataset.created,
        client: (a, b) => a.dataset.clientName.localeCompare(b.dataset.clientName) || a.dataset.order - b.dataset.order,
    };

    function matches(card) {
        if (state.filter === 'overdue' && card.dataset.overdue !== '1') return false;
        if (!['all', 'overdue'].includes(state.filter) && card.dataset.group !== state.filter) return false;
        if (state.client && card.dataset.client !== state.client) return false;
        if (state.assignee && card.dataset.assignee !== state.assignee) return false;
        if (state.q && !card.dataset.search.includes(state.q)) return false;
        return true;
    }

    function render() {
        const visible = cards.filter(matches).sort(sorters[state.sort] || sorters.smart);
        cards.forEach(card => { card.hidden = true; });
        visible.forEach(card => { card.hidden = false; });

        if (state.view === 'board') {
            board.querySelectorAll('.dt-col').forEach(col => {
                const body = col.querySelector('.dt-col-body');
                const colCards = visible.filter(card => card.dataset.group === col.dataset.col);
                colCards.forEach(card => body.appendChild(card));
                col.querySelector('[data-col-count]').textContent = colCards.length;
                col.querySelector('.dt-col-empty').hidden = colCards.length > 0;
            });
        } else {
            visible.forEach(card => grid.appendChild(card));
        }

        grid.hidden = state.view !== 'grid' || visible.length === 0;
        board.hidden = state.view !== 'board' || visible.length === 0;
        noResults.hidden = visible.length > 0;
        shownEl.textContent = visible.length;

        const filtered = state.filter !== 'all' || state.q || state.client || state.assignee;
        clearBtn.hidden = !filtered;

        document.querySelectorAll('.dt-kpi').forEach(btn => {
            const active = btn.dataset.filter === state.filter;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
        document.querySelectorAll('.dt-view button').forEach(btn => {
            btn.classList.toggle('is-active', btn.dataset.view === state.view);
        });
    }

    function clearFilters() {
        state.filter = 'all'; state.q = ''; state.client = ''; state.assignee = '';
        if (searchInput) searchInput.value = '';
        if (clientSelect) clientSelect.value = '';
        if (assigneeSelect) assigneeSelect.value = '';
        render();
    }

    document.querySelectorAll('.dt-kpi').forEach(btn => btn.addEventListener('click', () => {
        state.filter = state.filter === btn.dataset.filter ? 'all' : btn.dataset.filter;
        render();
    }));
    document.querySelectorAll('.dt-view button').forEach(btn => btn.addEventListener('click', () => {
        state.view = btn.dataset.view;
        store.set('view', state.view);
        render();
    }));
    searchInput?.addEventListener('input', () => { state.q = searchInput.value.trim().toLowerCase(); render(); });
    clientSelect?.addEventListener('change', () => { state.client = clientSelect.value; render(); });
    assigneeSelect?.addEventListener('change', () => { state.assignee = assigneeSelect.value; render(); });
    sortSelect?.addEventListener('change', () => { state.sort = sortSelect.value; store.set('sort', state.sort); render(); });
    clearBtn.addEventListener('click', clearFilters);
    document.querySelector('[data-clear-filters]')?.addEventListener('click', clearFilters);

    // ── Drawer ──
    const drawer = document.getElementById('dtDrawer');
    const overlay = document.getElementById('dtOverlay');
    const drawerContent = document.getElementById('dtDrawerContent');
    let lastFocus = null;
    let currentTaskId = null;

    function setTaskParam(id) {
        const url = new URL(window.location.href);
        if (id) url.searchParams.set('task', id); else url.searchParams.delete('task');
        history.replaceState(null, '', url);
    }

    function openDrawer(id, focusSubmit) {
        const template = document.getElementById('dt-detail-' + id);
        if (!template) return;
        lastFocus = document.activeElement;
        currentTaskId = id;
        drawerContent.replaceChildren(template.content.cloneNode(true));
        drawer.classList.add('is-open');
        overlay.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTaskParam(id);

        const submitPanel = drawerContent.querySelector('#dt-submit-' + id);
        if (focusSubmit && submitPanel) {
            setTimeout(() => {
                submitPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                submitPanel.querySelector('input[name="link"]')?.focus({ preventScroll: true });
            }, 320);
        } else {
            document.getElementById('dtDrawerClose').focus();
        }
    }

    function closeDrawer() {
        if (!drawer.classList.contains('is-open')) return;
        drawer.classList.remove('is-open');
        overlay.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        currentTaskId = null;
        setTaskParam(null);
        lastFocus?.focus?.();
    }

    document.getElementById('dtDrawerClose').addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDrawer();
        const typing = /^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement?.tagName);
        if (e.key === '/' && !typing && !drawer.classList.contains('is-open')) {
            e.preventDefault();
            searchInput?.focus();
        }
        if ((e.key === 'Enter' || e.key === ' ') && document.activeElement?.classList.contains('dt-card')) {
            e.preventDefault();
            openDrawer(document.activeElement.dataset.id);
        }
    });

    cards.forEach(card => card.addEventListener('click', (e) => {
        if (e.target.closest('button, a, form')) return;
        openDrawer(card.dataset.id);
    }));

    // ── Actions (cards and drawer share these via delegation) ──
    function toast(message, isError) {
        if (typeof ajax !== 'undefined') {
            isError ? ajax.showError(message) : ajax.showSuccess(message);
        } else {
            alert(message);
        }
    }

    async function postForm(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body,
        });
        const data = await response.json().catch(() => null);
        if (!response.ok || !data || data.success === false) {
            const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
            throw new Error(firstError || data?.message || 'Something went wrong. Please try again.');
        }
        return data;
    }

    function reloadWithTask(id) {
        const url = new URL(window.location.href);
        url.searchParams.set('task', id);
        window.location.href = url.toString();
    }

    function setBusy(button, busy, html) {
        if (!button) return;
        if (busy) {
            button.dataset.html = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (html || '');
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.html;
        }
    }

    document.addEventListener('click', async (e) => {
        const startBtn = e.target.closest('[data-start]');
        if (startBtn) {
            e.preventDefault();
            setBusy(startBtn, true, 'Starting');
            try {
                const fd = new FormData();
                fd.append('_token', csrf);
                const data = await postForm(startBtn.dataset.start, fd);
                toast(data.message || 'Task started.');
                setTimeout(() => reloadWithTask(startBtn.dataset.taskId), 600);
            } catch (err) {
                toast(err.message, true);
                setBusy(startBtn, false);
            }
            return;
        }

        const submitBtn = e.target.closest('[data-open-submit]');
        if (submitBtn) {
            e.preventDefault();
            openDrawer(submitBtn.dataset.openSubmit, true);
            return;
        }

        const pauseBtn = e.target.closest('[data-open-pause]');
        if (pauseBtn) {
            e.preventDefault();
            const modal = document.getElementById('dtPauseModal');
            const form = document.getElementById('dtPauseForm');
            const titleEl = document.getElementById('dtPauseTaskTitle');
            if (modal && form) {
                form.action = pauseBtn.dataset.openPause;
                form.dataset.taskId = pauseBtn.dataset.taskId;
                if (titleEl) titleEl.textContent = `"${pauseBtn.dataset.taskTitle || 'This task'}"`;
                form.reset();
                modal.style.display = 'flex';
            }
            return;
        }

        const resumeBtn = e.target.closest('[data-resume]');
        if (resumeBtn) {
            e.preventDefault();
            setBusy(resumeBtn, true, 'Resuming');
            try {
                const fd = new FormData();
                fd.append('_token', csrf);
                const data = await postForm(resumeBtn.dataset.resume, fd);
                toast(data.message || 'Task resumed.');
                setTimeout(() => reloadWithTask(resumeBtn.dataset.taskId), 600);
            } catch (err) {
                toast(err.message, true);
                setBusy(resumeBtn, false);
            }
            return;
        }

        if (e.target.closest('#dtClosePauseModal') || e.target.closest('#dtCancelPauseBtn') || e.target.id === 'dtPauseModal') {
            const modal = document.getElementById('dtPauseModal');
            if (modal) modal.style.display = 'none';
            return;
        }
    });

    document.addEventListener('submit', async (e) => {
        const form = e.target;

        if (form.id === 'dtPauseForm') {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            setBusy(btn, true, 'Pausing');
            try {
                const data = await postForm(form.action, new FormData(form));
                toast(data.message || 'Task paused.');
                document.getElementById('dtPauseModal').style.display = 'none';
                setTimeout(() => reloadWithTask(form.dataset.taskId), 600);
            } catch (err) {
                toast(err.message, true);
                setBusy(btn, false);
            }
            return;
        }

        if (form.classList.contains('dt-submit-form')) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            setBusy(btn, true, 'Submitting');
            try {
                const data = await postForm(form.action, new FormData(form));
                toast(data.message || 'Task submitted for review.');
                setTimeout(() => window.location.reload(), 800);
            } catch (err) {
                toast(err.message, true);
                setBusy(btn, false);
            }
        }

        if (form.classList.contains('dt-comment-form')) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const textarea = form.querySelector('textarea');
            setBusy(btn, true);
            try {
                const data = await postForm(form.action, new FormData(form));
                const list = form.parentElement.querySelector('[data-comments]');
                list.querySelector('[data-no-comments]')?.remove();

                const item = document.createElement('div');
                item.className = 'dt-comment';
                const avatar = document.createElement('span');
                avatar.className = 'dt-avatar';
                avatar.style.background = 'var(--dt-brand, #ef4444)';
                avatar.textContent = data.user_initial || '?';
                const bubble = document.createElement('div');
                bubble.className = 'dt-comment-bubble';
                const meta = document.createElement('div');
                meta.className = 'dt-comment-meta';
                const who = document.createElement('strong');
                who.textContent = data.user_name || 'You';
                const when = document.createElement('span');
                when.textContent = data.time || 'just now';
                meta.append(who, when);
                const body = document.createElement('div');
                body.className = 'dt-comment-body';
                body.textContent = data.body;
                bubble.append(meta, body);
                item.append(avatar, bubble);
                list.appendChild(item);

                const counter = form.parentElement.querySelector('[data-comment-count]');
                if (counter) counter.textContent = list.querySelectorAll('.dt-comment').length;
                textarea.value = '';
                item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                // Save the updated drawer back into its template so reopening keeps the new comment
                const template = document.getElementById('dt-detail-' + currentTaskId);
                if (template) {
                    template.content.replaceChildren(...Array.from(drawerContent.childNodes, node => node.cloneNode(true)));
                }
                toast('Comment posted.');
            } catch (err) {
                toast(err.message, true);
            } finally {
                setBusy(btn, false);
            }
        }
    });

    // Restore layout and open a task linked from a notification (?task=ID)
    render();
    const linkedTask = new URL(window.location.href).searchParams.get('task');
    if (linkedTask) openDrawer(linkedTask);
})();
</script>
@include('components.self-task-modal')
@endsection
