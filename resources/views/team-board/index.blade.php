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
.tb-orphans a { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 9px; font-size: 12px; font-weight: 700; text-decoration: none; background: var(--card, #fff); color: var(--text, #0f172a); border: 1px solid rgba(245,158,11,0.35); }

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
.tb-task { display: flex; align-items: center; gap: 10px; padding: 8px 10px; margin-bottom: 6px; border-radius: 11px; text-decoration: none; color: inherit; background: var(--card2, #f8fafc); border: 1px solid transparent; transition: border-color .15s; }
.tb-task:last-child { margin-bottom: 0; }
.tb-task:hover { border-color: var(--tb-brand); }
.tb-task.is-now { background: var(--b-bg); }
.tb-task-num { width: 22px; height: 22px; border-radius: 7px; display: grid; place-items: center; font-size: 11px; font-weight: 800; flex-shrink: 0; background: var(--card, #fff); color: var(--text2, #475569); border: 1px solid var(--border, #e2e6ef); }
.tb-task-icon { width: 26px; height: 26px; border-radius: 8px; display: grid; place-items: center; font-size: 11px; flex-shrink: 0; background: var(--card, #fff); color: var(--b-fg); }
.tb-task-main { flex: 1; min-width: 0; }
.tb-task-main b { display: block; font-size: 12.5px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tb-task-main small { display: block; font-size: 11px; color: var(--text3, #94a3b8); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tb-due { font-size: 10.5px; font-weight: 800; padding: 3px 8px; border-radius: 99px; white-space: nowrap; background: var(--t-bg); color: var(--t-fg); }
.tb-prio { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.tb-prio.p-urgent { background: #dc2626; } .tb-prio.p-high { background: #f97316; } .tb-prio.p-normal, .tb-prio.p-medium { background: #3b82f6; } .tb-prio.p-low { background: #94a3b8; }
.tb-empty-line { font-size: 12px; color: var(--text3, #94a3b8); padding: 4px 2px; }
.tb-more { width: 100%; margin-top: 6px; padding: 6px; border: 1px dashed var(--border, #e2e6ef); border-radius: 9px; cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; background: transparent; color: var(--tb-brand); }
.tb-more:hover { background: var(--tb-brand-soft); }
.tb-foot { display: flex; flex-wrap: wrap; gap: 6px; padding: 10px 16px 14px; border-top: 1px solid var(--border, #eef1f6); }
.tb-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 99px; background: var(--t-bg); color: var(--t-fg); }
.tb-empty { grid-column: 1 / -1; padding: 50px 20px; text-align: center; border-radius: 18px; border: 1px dashed var(--border, #e2e6ef); color: var(--text3, #94a3b8); background: var(--card, #fff); }
@media (max-width: 768px) {
    .tb-hero { padding: 22px 20px; }
    .tb-hero h1 { font-size: 22px; }
    .tb-grid { grid-template-columns: 1fr; }
}
@media (prefers-reduced-motion: reduce) { .tb-live { animation: none; } .tb-lane { transition: none; } }
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
                <a href="{{ $taskUrl($item->task) }}"><i class="fas {{ $typeIcon($item->task->type) }}"></i> {{ Str::limit($item->task->title ?? 'Untitled', 34) }}</a>
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

                {{-- NOW --}}
                <div class="tb-section">
                    <div class="tb-section-title {{ $person->now->isNotEmpty() ? 'is-now' : '' }}">
                        <span>@if($person->now->isNotEmpty())<span class="tb-live"></span>@endif Working on now</span>
                        @if($person->now->count() > 1)<span>{{ $person->now->count() }} in progress</span>@endif
                    </div>
                    @forelse($person->now->take(2) as $item)
                        @php $chip = $dueChip($item); @endphp
                        <a class="tb-task is-now" href="{{ $taskUrl($item->task) }}">
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
                        </a>
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
                        <div class="tb-empty-line">+{{ $person->now->count() - 2 }} more in progress</div>
                    @endif
                </div>

                {{-- UP NEXT --}}
                <div class="tb-section">
                    <div class="tb-section-title"><span>Up next</span>@if($person->next->isNotEmpty())<span>{{ $person->next->count() }} queued</span>@endif</div>
                    @forelse($person->next as $index => $item)
                        @php $chip = $dueChip($item); @endphp
                        <a class="tb-task" href="{{ $taskUrl($item->task) }}" @if($index >= 3) data-extra hidden @endif>
                            <span class="tb-task-num">{{ $index + 1 }}</span>
                            <span class="tb-task-main">
                                <b>{{ $item->task->title ?? 'Untitled' }}</b>
                                <small><i class="fas {{ $typeIcon($item->task->type) }}"></i> {{ $item->task->client->name ?? 'No client' }}{{ $item->task->creator ? ' · by ' . $item->task->creator->name : '' }}</small>
                            </span>
                            <span class="tb-prio p-{{ strtolower($item->task->priority ?? 'normal') }}" title="{{ ucfirst($item->task->priority ?? 'normal') }} priority"></span>
                            <span class="tb-due t-{{ $chip['tone'] }}">{{ $chip['text'] }}</span>
                        </a>
                    @empty
                        <div class="tb-empty-line">Queue is empty.</div>
                    @endforelse
                    @if($person->next->count() > 3)
                        <button type="button" class="tb-more" data-more>Show {{ $person->next->count() - 3 }} more</button>
                    @endif
                </div>

                @if($person->waiting->isNotEmpty() || $person->onHold->isNotEmpty() || $person->overdue > 0)
                    <div class="tb-foot">
                        @if($person->overdue > 0)
                            <span class="tb-chip t-red"><i class="fas fa-triangle-exclamation"></i> {{ $person->overdue }} overdue</span>
                        @endif
                        @if($person->waiting->isNotEmpty())
                            <span class="tb-chip t-violet" title="{{ $person->waiting->pluck('task.title')->implode(', ') }}"><i class="fas fa-eye"></i> {{ $person->waiting->count() }} waiting for review</span>
                        @endif
                        @if($person->onHold->isNotEmpty())
                            <span class="tb-chip t-slate"><i class="fas fa-circle-pause"></i> {{ $person->onHold->count() }} on hold</span>
                        @endif
                    </div>
                @endif
            </article>
        @empty
            <div class="tb-empty">No team members found.</div>
        @endforelse
        <div class="tb-empty" id="tbNoMatch" hidden>No one matches this search.</div>
    </div>
</div>

<script>
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
        const more = e.target.closest('[data-more]');
        if (!more) return;
        const extras = more.closest('.tb-section').querySelectorAll('[data-extra]');
        const opening = extras[0]?.hidden;
        extras.forEach(el => { el.hidden = !opening; });
        more.textContent = opening ? 'Show less' : `Show ${extras.length} more`;
    });

    render();
})();
</script>
@endsection
