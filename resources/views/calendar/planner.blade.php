@extends('layouts.app')

@section('content')
@php
    $isWeek = $view === 'week';

    $platformMeta = function (?string $platform) {
        $map = [
            'instagram' => ['Instagram', 'fa-brands fa-instagram', '#E1306C'],
            'facebook'  => ['Facebook', 'fa-brands fa-facebook-f', '#1877F2'],
            'linkedin'  => ['LinkedIn', 'fa-brands fa-linkedin-in', '#0A66C2'],
            'twitter'   => ['X / Twitter', 'fa-brands fa-x-twitter', '#111827'],
            'threads'   => ['Threads', 'fa-brands fa-threads', '#111827'],
            'tiktok'    => ['TikTok', 'fa-brands fa-tiktok', '#111827'],
            'youtube'   => ['YouTube', 'fa-brands fa-youtube', '#FF0000'],
            'pinterest' => ['Pinterest', 'fa-brands fa-pinterest-p', '#E60023'],
            'whatsapp'  => ['WhatsApp', 'fa-brands fa-whatsapp', '#22C55E'],
            'snapchat'  => ['Snapchat', 'fa-brands fa-snapchat', '#EAB308'],
            'telegram'  => ['Telegram', 'fa-brands fa-telegram', '#229ED9'],
            'reddit'    => ['Reddit', 'fa-brands fa-reddit-alien', '#FF4500'],
            'general'   => ['General / Design', 'fa-solid fa-layer-group', '#64748B'],
        ];
        [$label, $icon, $color] = $map[$platform] ?? [ucfirst((string) $platform), 'fa-solid fa-globe', '#64748B'];
        return (object) compact('label', 'icon', 'color');
    };
    $typeIcon = fn (?string $type) => [
        'reel' => 'fa-film', 'post' => 'fa-image', 'story' => 'fa-mobile-screen', 'video' => 'fa-video',
        'carousel' => 'fa-images', 'brochure' => 'fa-book-open', 'banner' => 'fa-rectangle-ad', 'flyer' => 'fa-file-image',
    ][$type] ?? 'fa-shapes';
    $initials = fn (?string $name) => collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') ?: '?';
    $hue = fn (?string $name) => 'hsl(' . (crc32((string) $name) % 360) . ', 60%, 50%)';

    // Shared by the admin and strategist panels: $calendarRoute is this page's route name
    $isAdmin = auth()->user()->role === 'admin';
    $taskUrl = fn ($task) => $isAdmin ? route('admin.tasks.show', $task) : route('strategist.tracking', ['task' => $task->id]);
    // Matches the reschedule API: admins, strategists, or task creators can move
    $canMove = fn ($task) => $isAdmin || auth()->user()?->role === 'strategist' || $task->created_by === auth()->id();

    $isMonth = $view === 'month';
    $isFeed = $view === 'feed';
    $activeFilters = $activeFilters ?? [];
    $festivalsByDate = $festivalsByDate ?? collect();
    $feedGrid = $feedGrid ?? collect();
    $designers = $designers ?? collect();
    $storeTaskUrl = $isAdmin ? route('admin.tasks.store') : route('strategist.tasks.store');

    // Build calendar URLs that keep the current filters
    $url = function (array $overrides = []) use ($view, $anchor, $clientId, $mine, $calendarRoute, $activeFilters) {
        $params = array_merge([
            'view' => $view,
            'date' => $anchor->toDateString(),
            'client' => $clientId,
            'mine' => $mine ? 1 : null,
            'platform' => $activeFilters['platform'] ?? null,
            'type' => $activeFilters['type'] ?? null,
            'assignee' => $activeFilters['assignee'] ?? null,
        ], $overrides);
        return route($calendarRoute, array_filter($params, fn ($v) => $v !== null && $v !== ''));
    };
    $prevUrl = $url(['date' => ($isWeek ? $periodStart->copy()->subWeek() : $periodStart->copy()->subMonth())->toDateString()]);
    $nextUrl = $url(['date' => ($isWeek ? $periodStart->copy()->addWeek() : $periodStart->copy()->addMonth())->toDateString()]);

    $title = $isWeek
        ? $periodStart->format('M d') . ' – ' . $periodEnd->format($periodStart->month === $periodEnd->month ? 'd, Y' : 'M d, Y')
        : $periodStart->format('F Y');
    $isCurrentPeriod = today()->betweenIncluded($periodStart, $periodEnd);

    $cardsByDate = $cards->groupBy('date');
    $stateOrder = array_flip(array_keys($states));
    $selectedClient = $clientId ? $clients->firstWhere('id', $clientId) : null;
    $ringDeg = round($summary['completion'] * 3.6);
@endphp

<style>
.cp-wrap, .cp-drawer {
    --cp-brand: #ef4444;
    --cp-brand-soft: rgba(239,68,68,0.09);
    --cp-shadow-sm: 0 1px 2px rgba(15,23,42,0.04), 0 1px 3px rgba(15,23,42,0.06);
    --cp-shadow-md: 0 12px 30px -14px rgba(15,23,42,0.22);
    --s-green: #10b981;  --s-green-bg: rgba(16,185,129,0.12);  --s-green-fg: #047857;
    --s-teal: #14b8a6;   --s-teal-bg: rgba(20,184,166,0.12);   --s-teal-fg: #0f766e;
    --s-violet: #8b5cf6; --s-violet-bg: rgba(139,92,246,0.12); --s-violet-fg: #6d28d9;
    --s-amber: #f59e0b;  --s-amber-bg: rgba(245,158,11,0.14);  --s-amber-fg: #b45309;
    --s-blue: #3b82f6;   --s-blue-bg: rgba(59,130,246,0.12);   --s-blue-fg: #1d4ed8;
    --s-slate: #94a3b8;  --s-slate-bg: rgba(100,116,139,0.12); --s-slate-fg: #475569;
    --s-red: #ef4444;    --s-red-bg: rgba(239,68,68,0.11);     --s-red-fg: #dc2626;
}
body.dark-mode .cp-wrap, body.dark-mode .cp-drawer {
    --cp-shadow-sm: 0 1px 2px rgba(0,0,0,0.3);
    --cp-shadow-md: 0 12px 30px -12px rgba(0,0,0,0.6);
    --s-green-fg: #34d399; --s-teal-fg: #5eead4; --s-violet-fg: #a78bfa; --s-amber-fg: #fbbf24;
    --s-blue-fg: #93c5fd; --s-slate-fg: #cbd5e1; --s-red-fg: #f87171;
}
.cp-wrap { padding: 4px 0 48px; font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; color: var(--text, #0f172a); }
.cp-wrap *, .cp-drawer * { box-sizing: border-box; }
.cp-wrap [hidden], .cp-drawer [hidden] { display: none !important; }

.tone-green  { --tone: var(--s-green);  --tone-bg: var(--s-green-bg);  --tone-fg: var(--s-green-fg); }
.tone-teal   { --tone: var(--s-teal);   --tone-bg: var(--s-teal-bg);   --tone-fg: var(--s-teal-fg); }
.tone-violet { --tone: var(--s-violet); --tone-bg: var(--s-violet-bg); --tone-fg: var(--s-violet-fg); }
.tone-amber  { --tone: var(--s-amber);  --tone-bg: var(--s-amber-bg);  --tone-fg: var(--s-amber-fg); }
.tone-blue   { --tone: var(--s-blue);   --tone-bg: var(--s-blue-bg);   --tone-fg: var(--s-blue-fg); }
.tone-slate  { --tone: var(--s-slate);  --tone-bg: var(--s-slate-bg);  --tone-fg: var(--s-slate-fg); }
.tone-red    { --tone: var(--s-red);    --tone-bg: var(--s-red-bg);    --tone-fg: var(--s-red-fg); }
.cp-pill {
    display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
    font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 99px;
    background: var(--tone-bg); color: var(--tone-fg);
}
.cp-pill i { font-size: 10px; }

/* ── Hero ── */
.cp-hero {
    position: relative; overflow: hidden; color: #fff; border-radius: 20px; padding: 26px 30px; margin-bottom: 16px;
    background: linear-gradient(135deg, #f43f3f 0%, #e02424 45%, #a51d1d 100%);
    box-shadow: 0 18px 40px -18px rgba(220,38,38,0.55);
    display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
}
.cp-hero::before { content: ''; position: absolute; width: 340px; height: 340px; top: -160px; right: -40px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.18), transparent 70%); pointer-events: none; }
.cp-hero > * { position: relative; z-index: 1; }
.cp-eyebrow {
    display: inline-flex; align-items: center; gap: 7px; font-size: 11px; font-weight: 700; letter-spacing: .9px;
    text-transform: uppercase; padding: 5px 12px; border-radius: 99px; margin-bottom: 10px;
    background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.22);
}
.cp-hero h1 { margin: 0 0 6px; font-size: 27px; font-weight: 800; letter-spacing: -.4px; }
.cp-hero-sub { margin: 0; font-size: 13.5px; color: rgba(255,255,255,0.85); display: flex; flex-wrap: wrap; gap: 6px 16px; }
.cp-hero-sub strong { color: #fff; }
.cp-hero-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
.cp-glass {
    display: inline-flex; padding: 4px; gap: 4px; border-radius: 12px;
    background: rgba(255,255,255,0.14); border: 1px solid rgba(255,255,255,0.24); backdrop-filter: blur(8px);
}
.cp-glass a, .cp-glass button {
    display: inline-flex; align-items: center; justify-content: center; gap: 7px; min-width: 36px; height: 34px; padding: 0 12px;
    border: none; border-radius: 9px; background: transparent; color: rgba(255,255,255,0.9);
    font: inherit; font-size: 12.5px; font-weight: 700; text-decoration: none; cursor: pointer; transition: all .15s;
}
.cp-glass a:hover, .cp-glass button:hover { background: rgba(255,255,255,0.14); color: #fff; }
.cp-glass a.is-active { background: #fff; color: #c81e1e; box-shadow: 0 4px 14px rgba(0,0,0,0.18); }

/* ── Controls ── */
.cp-controls {
    display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 10px; margin-bottom: 16px; border-radius: 16px;
    background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); box-shadow: var(--cp-shadow-sm);
}
.cp-field {
    height: 40px; border-radius: 11px; border: 1px solid var(--border, #e2e6ef); background: var(--card2, #f5f7fb);
    color: var(--text, #0f172a); font: inherit; font-size: 13px; padding: 0 12px;
}
.cp-field:focus { outline: none; border-color: var(--cp-brand); box-shadow: 0 0 0 3px var(--cp-brand-soft); }
select.cp-field { padding-right: 30px; cursor: pointer; min-width: 190px; }
.cp-search { position: relative; flex: 1 1 220px; min-width: 180px; }
.cp-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text3, #94a3b8); font-size: 13px; }
.cp-search .cp-field { width: 100%; padding-left: 36px; }
.cp-toggle {
    display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 14px; border-radius: 11px; text-decoration: none;
    border: 1px solid var(--border, #e2e6ef); background: var(--card2, #f5f7fb); color: var(--text2, #475569); font-size: 12.5px; font-weight: 700;
}
.cp-toggle .knob { width: 30px; height: 18px; border-radius: 99px; background: #cbd5e1; position: relative; transition: background .2s; }
.cp-toggle .knob::after { content: ''; position: absolute; top: 2px; left: 2px; width: 14px; height: 14px; border-radius: 50%; background: #fff; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.cp-toggle.is-on { color: var(--cp-brand); border-color: rgba(239,68,68,0.35); }
.cp-toggle.is-on .knob { background: var(--cp-brand); }
.cp-toggle.is-on .knob::after { transform: translateX(12px); }

/* ── Summary ── */
.cp-summary { display: grid; grid-template-columns: minmax(240px, 1.4fr) repeat(8, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
.cp-score, .cp-stat {
    border-radius: 16px; background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); box-shadow: var(--cp-shadow-sm);
}
.cp-score { display: flex; align-items: center; gap: 16px; padding: 16px 18px; }
.cp-ring {
    --deg: 0deg; flex-shrink: 0; width: 76px; height: 76px; border-radius: 50%; display: grid; place-items: center;
    background: conic-gradient(var(--s-green) var(--deg), var(--card2, #eef1f6) 0);
}
.cp-ring span {
    width: 58px; height: 58px; border-radius: 50%; background: var(--card, #fff); display: grid; place-items: center;
    font-size: 17px; font-weight: 800; letter-spacing: -.3px;
}
.cp-score-title { font-size: 14px; font-weight: 800; margin-bottom: 3px; }
.cp-score-sub { font-size: 12px; color: var(--text3, #94a3b8); line-height: 1.5; }
.cp-stat {
    display: flex; flex-direction: column; justify-content: center; gap: 6px; padding: 12px 14px; cursor: pointer; text-align: left;
    font: inherit; color: inherit; transition: transform .18s, box-shadow .18s, border-color .18s; position: relative; overflow: hidden;
}
.cp-stat::before { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 3px; background: var(--tone); }
.cp-stat:hover { transform: translateY(-2px); box-shadow: var(--cp-shadow-md); }
.cp-stat.is-active { border-color: var(--tone); box-shadow: 0 0 0 3px var(--tone-bg), var(--cp-shadow-md); }
.cp-stat-top { display: flex; align-items: center; justify-content: space-between; }
.cp-stat-val { font-size: 22px; font-weight: 800; line-height: 1; }
.cp-stat-icon { width: 26px; height: 26px; border-radius: 8px; display: grid; place-items: center; font-size: 11px; background: var(--tone-bg); color: var(--tone-fg); }
.cp-stat-label { font-size: 11.5px; font-weight: 700; color: var(--text3, #94a3b8); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Attention ── */
.cp-attention { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
.cp-att {
    border-radius: 16px; padding: 14px 16px; background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef);
    box-shadow: var(--cp-shadow-sm); border-top: 3px solid var(--tone);
}
.cp-att-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
.cp-att-title { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; }
.cp-att-title i { color: var(--tone-fg); }
.cp-att-count { font-size: 12px; font-weight: 800; padding: 2px 9px; border-radius: 99px; background: var(--tone-bg); color: var(--tone-fg); }
.cp-att-list { display: flex; flex-direction: column; gap: 6px; }
.cp-att-item {
    display: flex; align-items: center; gap: 10px; width: 100%; padding: 8px 10px; border-radius: 10px; cursor: pointer; text-align: left;
    border: 1px solid transparent; background: var(--card2, #f5f7fb); color: inherit; font: inherit; transition: border-color .15s;
}
.cp-att-item:hover { border-color: var(--tone); }
.cp-att-item .t { flex: 1; min-width: 0; }
.cp-att-item .t b { display: block; font-size: 12.5px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-att-item .t span { font-size: 11px; color: var(--text3, #94a3b8); }
.cp-att-item .d { font-size: 11px; font-weight: 800; color: var(--tone-fg); white-space: nowrap; }
.cp-att-empty { font-size: 12.5px; color: var(--text3, #94a3b8); padding: 8px 2px; display: flex; align-items: center; gap: 8px; }
.cp-att-empty i { color: var(--s-green); }
.cp-att-more { font-size: 11.5px; color: var(--text3, #94a3b8); padding: 2px; }

/* ── Board frame ── */
.cp-board { border-radius: 18px; background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); box-shadow: var(--cp-shadow-sm); overflow: hidden; }
.cp-board-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 14px 18px; border-bottom: 1px solid var(--border, #e2e6ef); }
.cp-board-title { font-size: 15px; font-weight: 800; display: flex; align-items: center; gap: 9px; }
.cp-board-title i { color: var(--cp-brand); }
.cp-legend { display: flex; flex-wrap: wrap; gap: 6px 12px; font-size: 11.5px; font-weight: 600; color: var(--text3, #94a3b8); }
.cp-legend span { display: inline-flex; align-items: center; gap: 6px; }
.cp-legend span::before { content: ''; width: 9px; height: 9px; border-radius: 3px; background: var(--tone); }
.cp-scroll { overflow-x: auto; }

/* ── Week grid (channel × day, like a printed content calendar) ── */
.cp-week { display: grid; grid-template-columns: 210px repeat(7, minmax(150px, 1fr)); min-width: 1260px; }
.cp-week > div { border-right: 1px solid var(--border, #e2e6ef); border-bottom: 1px solid var(--border, #e2e6ef); }
.cp-week > div:nth-child(8n) { border-right: none; }
.cp-col-head {
    position: sticky; top: 0; z-index: 2; padding: 12px 12px 10px; background: var(--card, #fff); text-align: center;
}
.cp-col-head.is-channel { text-align: left; display: flex; align-items: flex-end; font-size: 11px; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; color: var(--text3, #94a3b8); }
.cp-day-name { font-size: 11px; font-weight: 800; letter-spacing: .7px; text-transform: uppercase; color: var(--text3, #94a3b8); }
.cp-day-num { display: inline-grid; place-items: center; width: 34px; height: 34px; margin: 4px 0 6px; border-radius: 50%; font-size: 17px; font-weight: 800; }
.cp-col-head.is-today { background: linear-gradient(180deg, rgba(239,68,68,0.07), var(--card, #fff)); }
.cp-col-head.is-today .cp-day-name { color: var(--cp-brand); }
.cp-col-head.is-today .cp-day-num { background: var(--cp-brand); color: #fff; box-shadow: 0 6px 14px -4px rgba(239,68,68,0.6); }
.cp-col-head.is-past .cp-day-num { color: var(--text3, #94a3b8); }
.cp-day-meter { height: 4px; border-radius: 99px; background: var(--card2, #eef1f6); overflow: hidden; margin: 0 auto 4px; max-width: 110px; }
.cp-day-meter span { display: block; height: 100%; background: var(--s-green); border-radius: inherit; }
.cp-day-count { font-size: 10.5px; font-weight: 700; color: var(--text3, #94a3b8); }

.cp-client-row {
    grid-column: 1 / -1; display: flex; align-items: center; gap: 12px; padding: 12px 14px;
    background: var(--card2, #f5f7fb); border-right: none !important;
}
.cp-logo {
    width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0; overflow: hidden; display: grid; place-items: center;
    font-size: 12px; font-weight: 800; color: #fff; border: 1px solid var(--border, #e2e6ef);
}
.cp-logo img { width: 100%; height: 100%; object-fit: cover; background: #fff; }
.cp-client-name { font-size: 14px; font-weight: 800; }
.cp-client-progress { margin-left: auto; display: flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 700; color: var(--text2, #475569); }
.cp-bar { width: 120px; height: 6px; border-radius: 99px; background: var(--border, #e2e6ef); overflow: hidden; }
.cp-bar span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #10b981, #34d399); }

.cp-channel {
    display: flex; align-items: center; gap: 10px; padding: 10px 12px; color: #fff; font-size: 13px; font-weight: 800;
    background: var(--ch);
}
.cp-channel i { width: 26px; height: 26px; border-radius: 8px; display: grid; place-items: center; background: rgba(255,255,255,0.2); font-size: 13px; }
.cp-channel small { display: block; font-size: 10.5px; font-weight: 700; opacity: .85; }
.cp-cell { padding: 8px; min-height: 92px; background: color-mix(in srgb, var(--ch) 6%, var(--card, #fff)); display: flex; flex-direction: column; gap: 6px; }
.cp-cell.is-today { box-shadow: inset 0 0 0 2px rgba(239,68,68,0.25); }

/* ── Content card ── */
.cp-card {
    position: relative; display: block; width: 100%; text-align: left; cursor: pointer; font: inherit; color: inherit;
    padding: 8px 9px 8px 11px; border-radius: 11px; border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff);
    box-shadow: var(--cp-shadow-sm); transition: transform .15s, box-shadow .15s;
}
.cp-card::before { content: ''; position: absolute; left: 0; top: 6px; bottom: 6px; width: 3px; border-radius: 0 3px 3px 0; background: var(--tone); }
.cp-card:hover { transform: translateY(-2px); box-shadow: var(--cp-shadow-md); }
.cp-card:focus-visible { outline: 3px solid var(--tone-bg); }
.cp-card.is-state-done { background: color-mix(in srgb, var(--s-green) 8%, var(--card, #fff)); border-color: rgba(16,185,129,0.3); }
.cp-card.is-state-missed { background: color-mix(in srgb, var(--s-red) 7%, var(--card, #fff)); border-color: rgba(239,68,68,0.35); }
.cp-card-top { display: flex; align-items: center; justify-content: space-between; gap: 6px; font-size: 10.5px; font-weight: 700; color: var(--text3, #94a3b8); text-transform: capitalize; }
.cp-card-state { width: 18px; height: 18px; border-radius: 50%; display: grid; place-items: center; font-size: 9px; background: var(--tone-bg); color: var(--tone-fg); flex-shrink: 0; }
.cp-card.is-state-done .cp-card-state { background: var(--s-green); color: #fff; }
.cp-card-title { margin: 4px 0 6px; font-size: 12.5px; font-weight: 700; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.cp-card.is-state-done .cp-card-title { color: var(--text2, #475569); }
.cp-card-foot { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.cp-card-label { font-size: 10.5px; font-weight: 800; color: var(--tone-fg); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-avatar { width: 20px; height: 20px; border-radius: 50%; display: grid; place-items: center; font-size: 8.5px; font-weight: 800; color: #fff; flex-shrink: 0; }

/* ── Month grid ── */
.cp-month { display: grid; grid-template-columns: repeat(7, minmax(130px, 1fr)); min-width: 920px; }
.cp-month-dow { padding: 10px; font-size: 11px; font-weight: 800; letter-spacing: .7px; text-transform: uppercase; color: var(--text3, #94a3b8); text-align: center; border-bottom: 1px solid var(--border, #e2e6ef); }
.cp-mday {
    cursor: pointer;
    min-height: 132px; padding: 8px; display: flex; flex-direction: column; gap: 5px; text-decoration: none; color: inherit;
    border-right: 1px solid var(--border, #e2e6ef); border-bottom: 1px solid var(--border, #e2e6ef); transition: background .15s;
}
/* nth-of-type ignores the per-day <template>s, so every 7th day still ends the row */
.cp-mday:nth-of-type(7n) { border-right: none; }
.cp-mday.is-empty { cursor: default; }
.cp-mday.is-empty:hover { background: transparent; }
.cp-mday.is-out.is-empty:hover { background: var(--card2, #f8fafc); }
.cp-mday:focus-visible { outline: 3px solid var(--cp-brand-soft); outline-offset: -3px; }
.cp-mmore { cursor: pointer; }
.cp-mmore:hover { text-decoration: underline; }

/* Day panel */
.cp-day-list { display: flex; flex-direction: column; gap: 8px; }
.cp-day-item {
    display: flex; align-items: center; gap: 12px; width: 100%; padding: 11px 12px; border-radius: 13px; cursor: pointer; text-align: left;
    font: inherit; color: inherit; background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef);
    border-left: 4px solid var(--tone); transition: transform .15s, box-shadow .15s;
}
.cp-day-item:hover { transform: translateX(-2px); box-shadow: var(--cp-shadow-md); }
.cp-day-item-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 3px; }
.cp-day-item-main b { font-size: 13.5px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-day-item-main > span { font-size: 11.5px; color: var(--text3, #94a3b8); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-day-item-channels { display: flex; gap: 5px; margin-top: 2px; }
.cp-day-item-channels i {
    width: 22px; height: 22px; border-radius: 7px; display: grid; place-items: center; font-size: 11px;
    color: var(--ch); background: color-mix(in srgb, var(--ch) 12%, transparent); position: relative;
}
.cp-day-item-channels i.is-posted { background: var(--ch); color: #fff; }
.cp-day-item-channels i.is-posted::after {
    content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: 7px; position: absolute; right: -4px; bottom: -4px;
    width: 12px; height: 12px; border-radius: 50%; display: grid; place-items: center; background: var(--s-green); color: #fff; border: 1.5px solid var(--card, #fff);
}
.cp-back {
    display: inline-flex; align-items: center; gap: 7px; margin-bottom: 10px; padding: 5px 10px; border-radius: 8px; cursor: pointer;
    font: inherit; font-size: 12px; font-weight: 700; border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text2, #475569);
}
.cp-back:hover { color: var(--cp-brand); border-color: var(--cp-brand); }
.cp-mday:hover { background: var(--card2, #f8fafc); }
.cp-mday.is-out { background: var(--card2, #f8fafc); }
.cp-mday.is-out .cp-mday-num { color: var(--text3, #94a3b8); opacity: .6; }
.cp-mday-top { display: flex; align-items: center; justify-content: space-between; }
.cp-mday-num { width: 28px; height: 28px; border-radius: 50%; display: grid; place-items: center; font-size: 13px; font-weight: 800; }
.cp-mday.is-today .cp-mday-num { background: var(--cp-brand); color: #fff; }
.cp-mday-ratio { font-size: 10.5px; font-weight: 800; color: var(--text3, #94a3b8); }
.cp-mday-ratio.is-complete { color: var(--s-green-fg); }
.cp-mday-meter { height: 3px; border-radius: 99px; background: var(--border, #e2e6ef); overflow: hidden; }
.cp-mday-meter span { display: block; height: 100%; background: var(--s-green); }
.cp-mitem {
    display: flex; align-items: center; gap: 6px; width: 100%; padding: 4px 6px; border-radius: 7px; cursor: pointer;
    border: none; background: var(--tone-bg); color: var(--text, #0f172a); font: inherit; font-size: 11.5px; font-weight: 600; text-align: left;
}
.cp-mitem i { color: var(--tone-fg); font-size: 10px; flex-shrink: 0; }
.cp-mitem span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-mmore { font-size: 11px; font-weight: 700; color: var(--cp-brand); padding: 0 4px; }

.cp-empty { padding: 56px 20px; text-align: center; }
.cp-empty-icon { width: 64px; height: 64px; margin: 0 auto 14px; border-radius: 20px; display: grid; place-items: center; font-size: 24px; background: var(--cp-brand-soft); color: var(--cp-brand); }
.cp-empty h3 { margin: 0 0 6px; font-size: 17px; font-weight: 800; }
.cp-empty p { margin: 0 0 16px; font-size: 13px; color: var(--text3, #94a3b8); }
.cp-btn {
    display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 16px; border-radius: 11px; text-decoration: none; cursor: pointer;
    font: inherit; font-size: 13px; font-weight: 800; border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text, #0f172a);
}
.cp-btn:hover { border-color: var(--cp-brand); color: var(--cp-brand); }
.cp-btn-primary { background: linear-gradient(135deg, #f43f3f, #dc2626); color: #fff; border-color: transparent; }
.cp-btn-primary:hover { color: #fff; filter: brightness(1.06); }

/* ── Filter Matrix ── */
.cp-filter-matrix {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;
    padding: 12px 16px; margin-bottom: 16px; border-radius: 16px;
    background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); box-shadow: var(--cp-shadow-sm);
}
.cp-filter-group { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.cp-filter-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--text3, #94a3b8); margin-right: 2px; }
.cp-chip {
    display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 11px; border-radius: 99px;
    font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer;
    background: var(--card2, #f5f7fb); color: var(--text2, #475569); border: 1px solid var(--border, #e2e6ef); transition: all .15s ease;
}
.cp-chip:hover { border-color: var(--cp-brand); color: var(--cp-brand); background: var(--card, #fff); }
.cp-chip.is-active { background: var(--cp-brand); color: #fff; border-color: var(--cp-brand); box-shadow: 0 3px 10px -2px rgba(239,68,68,0.5); }
.cp-chip i { font-size: 11px; }

/* ── Festival Cultural Markers ── */
.cp-fest-chip {
    display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; margin: 3px 0 2px; border-radius: 99px;
    font-size: 10.5px; font-weight: 800; color: #b45309; background: linear-gradient(135deg, rgba(254,243,199,0.95), rgba(253,230,138,0.95));
    border: 1px solid rgba(245,158,11,0.4); cursor: pointer; transition: transform .15s, box-shadow .15s; text-decoration: none; max-width: 130px;
}
body.dark-mode .cp-fest-chip { color: #fde68a; background: linear-gradient(135deg, rgba(120,53,15,0.45), rgba(180,83,9,0.35)); border-color: rgba(245,158,11,0.4); }
.cp-fest-chip:hover { transform: translateY(-1px) scale(1.02); box-shadow: 0 4px 12px rgba(245,158,11,0.25); color: #92400e; }
.cp-fest-chip .cp-fest-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-fest-chip i.fa-plus { font-size: 8.5px; opacity: 0.65; margin-left: 1px; }

.cp-fest-chip-sm {
    display: inline-flex; align-items: center; gap: 4px; padding: 2px 6px; border-radius: 99px; margin-bottom: 2px;
    font-size: 10px; font-weight: 800; color: #b45309; background: rgba(254,243,199,0.9); border: 1px solid rgba(245,158,11,0.3);
    cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;
}
body.dark-mode .cp-fest-chip-sm { color: #fde68a; background: rgba(120,53,15,0.3); }

/* ── Slot Quick-Add ── */
.cp-slot-add-btn {
    display: none; align-items: center; justify-content: center; gap: 5px; width: 100%; height: 26px; margin-top: auto;
    border-radius: 8px; border: 1px dashed var(--border, #cbd5e1); background: rgba(255,255,255,0.7);
    color: var(--text3, #94a3b8); font-size: 11px; font-weight: 700; cursor: pointer; transition: all .15s;
}
.cp-cell:hover .cp-slot-add-btn { display: flex; }
.cp-slot-add-btn:hover { border-color: var(--cp-brand); color: var(--cp-brand); background: var(--card, #fff); box-shadow: var(--cp-shadow-sm); }

.cp-mday-add-btn {
    display: none; width: 20px; height: 20px; border-radius: 50%; border: 1px solid var(--border, #cbd5e1);
    background: var(--card, #fff); color: var(--text3, #94a3b8); font-size: 9px; cursor: pointer; place-items: center; transition: all .15s;
}
.cp-mday:hover .cp-mday-add-btn { display: grid; }
.cp-mday-add-btn:hover { border-color: var(--cp-brand); color: var(--cp-brand); transform: scale(1.1); }

/* ── Visual Instagram Feed Studio (Compact & Non-Lengthy) ── */
.cp-feed-view { padding: 14px 20px 30px; max-width: 1320px; margin: 0 auto; }

/* Client Filter Bar */
.cp-feed-clients-bar {
    display: flex; align-items: center; gap: 10px; margin-bottom: 14px;
    padding: 10px 14px; border-radius: 14px; background: var(--card, #fff);
    border: 1px solid var(--border, #e2e6ef); box-shadow: var(--cp-shadow-sm);
}
.cp-feed-bar-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--text3, #94a3b8); display: flex; align-items: center; gap: 5px; flex-shrink: 0; }
.cp-feed-clients-scroll { display: flex; align-items: center; gap: 6px; overflow-x: auto; scrollbar-width: none; flex: 1; }
.cp-feed-clients-scroll::-webkit-scrollbar { display: none; }
.cp-count-pill { font-size: 10px; font-weight: 800; padding: 1px 6px; border-radius: 99px; background: rgba(0,0,0,0.1); margin-left: 4px; }

/* Toolbar */
.cp-feed-toolbar {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
    padding: 12px 16px; margin-bottom: 20px; border-radius: 16px;
    background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); box-shadow: var(--cp-shadow-sm);
}
.cp-feed-toolbar-left, .cp-feed-toolbar-right { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.cp-seg-group { display: flex; align-items: center; gap: 8px; }
.cp-seg-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--text3, #94a3b8); display: flex; align-items: center; gap: 5px; }
.cp-segmented { display: inline-flex; align-items: center; background: var(--card2, #f1f5f9); border: 1px solid var(--border, #e2e6ef); border-radius: 11px; padding: 3px; gap: 2px; }
.cp-seg-btn {
    display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 11px; border-radius: 8px;
    font-size: 12px; font-weight: 700; border: none; background: transparent; color: var(--text2, #475569);
    cursor: pointer; transition: all .15s ease;
}
.cp-seg-btn:hover { color: var(--cp-brand); }
.cp-seg-btn.is-active { background: var(--card, #fff); color: var(--text, #0f172a); box-shadow: 0 2px 6px rgba(15,23,42,0.08); font-weight: 800; }
body.dark-mode .cp-seg-btn.is-active { background: var(--card); color: #fff; }
.cp-feed-cadence-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--text2, #475569); }
.cp-feed-cadence-badge strong { color: var(--text, #0f172a); }

/* Split Studio Layout */
.cp-feed-studio {
    display: grid; grid-template-columns: minmax(370px, 420px) minmax(360px, 1fr); gap: 26px; align-items: start;
}
@media (max-width: 980px) {
    .cp-feed-studio { grid-template-columns: 1fr; }
}

/* ── PHONE MOCKUP CHASSIS ── */
.cp-phone-wrapper { display: flex; justify-content: center; width: 100%; }
.cp-phone-chassis {
    width: 100%; max-width: 395px; height: 720px; max-height: calc(100vh - 160px);
    border-radius: 46px; background: var(--card, #fff);
    border: 11px solid #0f172a; box-shadow: 0 25px 50px -12px rgba(15,23,42,0.35), 0 0 0 1px rgba(255,255,255,0.1) inset;
    display: flex; flex-direction: column; position: relative; overflow: hidden;
}
body.dark-mode .cp-phone-chassis { border-color: #1e293b; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.7); }
.cp-phone-island { width: 94px; height: 22px; background: #000; border-radius: 20px; margin: 7px auto 3px; display: grid; place-items: center; flex-shrink: 0; z-index: 10; }
.cp-phone-camera { width: 10px; height: 10px; border-radius: 50%; background: #0f172a; border: 1.5px solid #1e293b; margin-left: 45px; }
.cp-phone-status-bar { display: flex; align-items: center; justify-content: space-between; padding: 0 20px 4px; font-size: 11px; font-weight: 800; color: var(--text, #0f172a); flex-shrink: 0; }
.cp-phone-status-icons { display: flex; align-items: center; gap: 6px; font-size: 10px; }

/* Phone App Header */
.cp-phone-app-header { padding: 4px 16px 8px; border-bottom: 1px solid var(--border, #e2e6ef); flex-shrink: 0; background: var(--card, #fff); }
.cp-phone-top-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.cp-phone-username { display: flex; align-items: center; gap: 6px; font-size: 15px; font-weight: 800; color: var(--text, #0f172a); }
.cp-phone-top-actions { display: flex; align-items: center; gap: 14px; font-size: 15px; color: var(--text, #0f172a); cursor: pointer; }
.cp-phone-profile-row { display: flex; align-items: center; gap: 20px; margin-bottom: 8px; }
.cp-phone-avatar-ring {
    width: 62px; height: 62px; border-radius: 50%; padding: 2px;
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%); flex-shrink: 0;
}
.cp-phone-avatar-inner {
    width: 100%; height: 100%; border-radius: 50%; overflow: hidden; background: var(--card, #fff);
    display: grid; place-items: center; font-size: 18px; font-weight: 800; color: #fff; border: 2px solid var(--card, #fff);
}
.cp-phone-avatar-inner img { width: 100%; height: 100%; object-fit: cover; }
.cp-phone-stats-cols { display: flex; align-items: center; justify-content: space-around; flex: 1; text-align: center; }
.cp-phone-stat strong { display: block; font-size: 14px; font-weight: 800; color: var(--text, #0f172a); }
.cp-phone-stat span { font-size: 11px; color: var(--text3, #94a3b8); }
.cp-phone-bio { font-size: 11.5px; line-height: 1.35; margin-bottom: 8px; color: var(--text, #0f172a); }
.cp-phone-name { font-weight: 800; }
.cp-phone-category { color: var(--text3, #94a3b8); font-size: 10.5px; }
.cp-phone-link { color: #0284c7; font-weight: 700; font-size: 11px; text-decoration: none; }
.cp-phone-buttons { display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
.cp-phone-btn {
    flex: 1; height: 26px; border-radius: 7px; border: 1px solid var(--border, #e2e6ef);
    background: var(--card2, #f1f5f9); color: var(--text, #0f172a); font-size: 11.5px; font-weight: 700; cursor: pointer;
}
.cp-phone-btn-icon { flex: none; width: 28px; }
.cp-phone-tabs { display: flex; align-items: center; border-top: 1px solid var(--border, #e2e6ef); margin: 0 -16px -8px; }
.cp-phone-tab { flex: 1; height: 34px; display: grid; place-items: center; font-size: 14px; color: var(--text3, #94a3b8); cursor: pointer; border-bottom: 2px solid transparent; }
.cp-phone-tab.is-active { color: var(--text, #0f172a); border-bottom-color: var(--text, #0f172a); }

/* Phone Feed Scrollable Grid */
.cp-phone-feed-scroll { flex: 1; overflow-y: auto; overflow-x: hidden; background: var(--card2, #f8fafc); scrollbar-width: thin; }
.cp-phone-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; background: var(--border, #e2e8f0); }
body.dark-mode .cp-phone-grid { background: #334155; }

/* Phone Tile & Universal Feed Tile */
.cp-feed-tile {
    position: relative; aspect-ratio: 1; overflow: hidden; background: var(--card, #fff);
    cursor: pointer; transition: filter .15s ease, transform .15s ease;
}
.cp-feed-tile:hover { filter: brightness(0.92); }
.cp-feed-tile.is-selected { outline: 3px solid var(--cp-brand, #ef4444); outline-offset: -3px; z-index: 2; }
.cp-tile-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.cp-tile-empty {
    width: 100%; height: 100%; padding: 8px; display: flex; flex-direction: column; justify-content: space-between;
    background: linear-gradient(135deg, rgba(239,68,68,0.06), rgba(99,102,241,0.08));
}
.cp-tile-empty-title { font-size: 10.5px; font-weight: 800; line-height: 1.3; color: var(--text, #0f172a); }
.cp-tile-empty-client { font-size: 9px; font-weight: 700; color: var(--text3, #94a3b8); text-transform: uppercase; }
.cp-tile-date {
    position: absolute; bottom: 4px; left: 4px; z-index: 2; padding: 1px 5px; border-radius: 4px;
    background: rgba(15,23,42,0.72); backdrop-filter: blur(3px); color: #fff; font-size: 9px; font-weight: 800;
}
.cp-tile-type-badge {
    position: absolute; top: 4px; right: 4px; z-index: 2; width: 20px; height: 20px; border-radius: 5px;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(3px); color: #fff; display: grid; place-items: center; font-size: 9px;
}
.cp-phone-home-bar { width: 120px; height: 4px; background: var(--text, #0f172a); border-radius: 99px; margin: 6px auto; flex-shrink: 0; }

/* ── 9-GRID FOCUS MODE ── */
.cp-grid9-wrapper { width: 100%; max-width: 480px; margin: 0 auto; }
.cp-grid9-card {
    background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); border-radius: 20px;
    padding: 18px; box-shadow: var(--cp-shadow-sm);
}
.cp-grid9-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.cp-grid9-title { font-size: 13.5px; font-weight: 800; display: flex; align-items: center; gap: 8px; color: var(--text, #0f172a); }
.cp-grid9-pager { display: flex; align-items: center; gap: 8px; }
.cp-pager-btn { width: 28px; height: 28px; border-radius: 7px; border: 1px solid var(--border, #e2e6ef); background: var(--card2, #f1f5f9); color: var(--text, #0f172a); cursor: pointer; display: grid; place-items: center; font-size: 11px; }
.cp-pager-btn:hover { border-color: var(--cp-brand); color: var(--cp-brand); }
.cp-grid9-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.cp-grid9-grid .cp-feed-tile { border-radius: 10px; border: 1px solid var(--border, #e2e6ef); }

/* ── STUDIO GRID (COMPACT 4-COL & STANDARD 3-COL) ── */
.cp-studio-grid-wrapper { width: 100%; }
.cp-studio-grid-scroll { max-height: calc(100vh - 200px); overflow-y: auto; padding-right: 6px; }
.cp-studio-grid-scroll.is-compact { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.cp-studio-grid-scroll.is-standard { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.cp-studio-grid-scroll .cp-feed-tile { border-radius: 12px; border: 1px solid var(--border, #e2e6ef); }

/* ── LIVE POST INSPECTOR (RIGHT COLUMN) ── */
.cp-feed-inspector-col { width: 100%; }
.cp-live-inspector-card {
    background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); border-radius: 22px;
    padding: 22px; box-shadow: var(--cp-shadow-sm); position: sticky; top: 16px;
    max-height: calc(100vh - 160px); overflow-y: auto;
}
.cp-insp-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.cp-insp-badges { display: flex; align-items: center; gap: 8px; }
.cp-insp-format { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 700; color: var(--text2, #475569); }
.cp-insp-action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 8px; font-size: 11.5px; font-weight: 700; border: 1px solid var(--border, #e2e6ef); background: var(--card2, #f1f5f9); color: var(--text2, #475569); cursor: pointer; }
.cp-insp-action-btn:hover { border-color: var(--cp-brand); color: var(--cp-brand); }
.cp-insp-title { font-size: 16px; font-weight: 800; line-height: 1.35; margin: 0 0 8px; color: var(--text, #0f172a); }
.cp-insp-meta-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; font-size: 12px; color: var(--text2, #475569); margin-bottom: 16px; }
.cp-insp-meta-row span { display: inline-flex; align-items: center; gap: 5px; }

/* Artwork preview */
.cp-insp-media-box {
    position: relative; width: 100%; height: 210px; border-radius: 14px; overflow: hidden;
    background: var(--card2, #f1f5f9); border: 1px solid var(--border, #e2e6ef); margin-bottom: 16px;
    display: grid; place-items: center;
}
.cp-insp-media-box img { width: 100%; height: 100%; object-fit: contain; background: #0b0f19; }
.cp-insp-media-empty { display: flex; flex-direction: column; align-items: center; gap: 6px; color: var(--text3, #94a3b8); font-size: 12px; font-weight: 700; }
.cp-insp-media-empty i { font-size: 32px; color: #e1306c; opacity: .7; }
.cp-insp-zoom-btn {
    position: absolute; bottom: 8px; right: 8px; display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 7px; background: rgba(15,23,42,0.75); backdrop-filter: blur(4px);
    color: #fff; font-size: 11px; font-weight: 700; border: none; cursor: pointer; transition: transform .15s;
}
.cp-insp-zoom-btn:hover { transform: scale(1.05); }

/* Copywriting blocks */
.cp-insp-section { margin-bottom: 14px; }
.cp-insp-sec-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--text3, #94a3b8); }
.cp-copy-btn { display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 5px; border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text2, #475569); font-size: 10.5px; font-weight: 700; cursor: pointer; }
.cp-copy-btn:hover { border-color: var(--cp-brand); color: var(--cp-brand); }
.cp-insp-quote {
    margin: 0; padding: 10px 12px; border-radius: 10px; background: rgba(245,158,11,0.08);
    border-left: 3px solid #f59e0b; font-size: 12.5px; font-weight: 700; line-height: 1.45; color: var(--text, #0f172a);
}
.cp-insp-caption-box {
    padding: 10px 12px; border-radius: 10px; background: var(--card2, #f8fafc); border: 1px solid var(--border, #e2e6ef);
    font-size: 12px; line-height: 1.55; color: var(--text, #0f172a); max-height: 110px; overflow-y: auto; white-space: pre-wrap;
}
.cp-insp-tags-wrap {
    padding: 8px 10px; border-radius: 10px; background: var(--card2, #f8fafc); border: 1px solid var(--border, #e2e6ef);
    font-size: 11.5px; line-height: 1.5; color: #6366f1; font-weight: 600; word-break: break-word;
}
.cp-insp-footer { display: flex; align-items: center; gap: 10px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border, #e2e6ef); }

/* ── Deliverable Gallery & Copywriting Blocks ── */
.cp-media-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; margin-top: 8px; }
.cp-media-tile {
    position: relative; aspect-ratio: 1; border-radius: 10px; overflow: hidden;
    background: var(--card2, #f5f7fb); border: 1px solid var(--border, #e2e6ef); cursor: pointer;
}
.cp-media-tile img, .cp-media-tile video { width: 100%; height: 100%; object-fit: cover; }
.cp-media-badge { position: absolute; bottom: 6px; left: 6px; padding: 2px 6px; border-radius: 4px; background: rgba(0,0,0,0.65); color: #fff; font-size: 9px; font-weight: 800; text-transform: uppercase; }
.cp-media-actions { position: absolute; inset: 0; background: rgba(15,23,42,0.6); display: flex; align-items: center; justify-content: center; gap: 8px; opacity: 0; transition: opacity .15s; }
.cp-media-tile:hover .cp-media-actions { opacity: 1; }
.cp-media-btn { width: 32px; height: 32px; border-radius: 8px; background: #fff; color: #0f172a; display: grid; place-items: center; font-size: 13px; border: none; cursor: pointer; text-decoration: none; transition: transform .15s; }
.cp-media-btn:hover { transform: scale(1.1); color: var(--cp-brand); }

.cp-copy-box { position: relative; padding: 12px 14px; border-radius: 12px; background: var(--card2, #f8fafc); border: 1px solid var(--border, #e2e6ef); }
.cp-copy-box-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.cp-copy-box-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--text3, #94a3b8); }
.cp-copy-act-btn { display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 6px; border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text2, #475569); font-size: 11px; font-weight: 700; cursor: pointer; transition: all .15s; }
.cp-copy-act-btn:hover { border-color: var(--cp-brand); color: var(--cp-brand); }
.cp-copy-text { font-size: 13px; line-height: 1.6; color: var(--text, #0f172a); white-space: pre-wrap; word-break: break-word; margin: 0; }

/* ── Lightbox & Modals ── */
.cp-lightbox { position: fixed; inset: 0; z-index: 10005; background: rgba(15,23,42,0.92); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; padding: 24px; }
.cp-lightbox.is-open { display: flex; }
.cp-lightbox-content { max-width: 90vw; max-height: 90vh; display: flex; flex-direction: column; align-items: center; position: relative; }
.cp-lightbox-content img, .cp-lightbox-content video { max-width: 100%; max-height: 85vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); object-fit: contain; }
.cp-lightbox-close { position: absolute; top: -46px; right: 0; width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.2); color: #fff; border: none; font-size: 18px; cursor: pointer; display: grid; place-items: center; }
.cp-lightbox-close:hover { background: rgba(255,255,255,0.35); }

.cp-modal-overlay { position: fixed; inset: 0; z-index: 10002; background: rgba(15,23,42,0.5); backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; padding: 20px; }
.cp-modal-overlay.is-open { display: flex; }
.cp-quick-dialog { width: min(540px, 100vw); background: var(--card, #fff); border-radius: 20px; border: 1px solid var(--border, #e2e6ef); box-shadow: 0 20px 45px -15px rgba(15,23,42,0.3); display: flex; flex-direction: column; overflow: hidden; }
.cp-quick-head { padding: 20px 24px 16px; border-bottom: 1px solid var(--border, #e2e6ef); display: flex; align-items: center; justify-content: space-between; }
.cp-quick-title { font-size: 17px; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px; }
.cp-quick-title i { color: var(--cp-brand); }
.cp-quick-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; }
.cp-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.cp-form-group { display: flex; flex-direction: column; gap: 5px; }
.cp-form-group label { font-size: 11.5px; font-weight: 800; color: var(--text2, #475569); text-transform: uppercase; letter-spacing: .5px; }
.cp-form-group .cp-field { width: 100%; }
.cp-form-group textarea.cp-field { height: 72px; padding: 8px 12px; resize: vertical; }
.cp-quick-foot { padding: 14px 24px 18px; border-top: 1px solid var(--border, #e2e6ef); display: flex; align-items: center; justify-content: flex-end; gap: 10px; }

/* ── Presentation View ── */
.cp-wrap.is-presenting { background: #0f172a; color: #f8fafc; min-height: 100vh; padding: 16px 24px; border-radius: 20px; }
.cp-wrap.is-presenting .cp-controls, .cp-wrap.is-presenting .cp-attention, .cp-wrap.is-presenting .cp-hint, .cp-wrap.is-presenting .cp-score-sub, .cp-wrap.is-presenting .cp-stat { display: none !important; }
.cp-wrap.is-presenting .cp-hero { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); box-shadow: 0 10px 30px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1); }
.cp-present-banner { display: none; align-items: center; justify-content: space-between; gap: 16px; padding: 12px 20px; margin-bottom: 16px; background: linear-gradient(90deg, #1e293b, #334155); border: 1px solid rgba(255,255,255,0.15); border-radius: 14px; color: #fff; box-shadow: 0 4px 18px rgba(0,0,0,0.25); }
.cp-wrap.is-presenting .cp-present-banner { display: flex; }

/* ── Toast ── */
.cp-toast { position: fixed; bottom: 24px; right: 24px; z-index: 10010; display: flex; align-items: center; gap: 12px; padding: 12px 18px; border-radius: 12px; background: #0f172a; color: #fff; box-shadow: 0 12px 30px rgba(0,0,0,0.3); font-size: 13px; font-weight: 700; transform: translateY(100px); opacity: 0; transition: all .25s cubic-bezier(.2,.8,.2,1); pointer-events: none; }
.cp-toast.is-show { transform: translateY(0); opacity: 1; pointer-events: auto; }
.cp-toast-undo { background: #ef4444; color: #fff; border: none; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 800; cursor: pointer; }

/* ── Posting Schedule & Reschedule Card ── */
.cp-reschedule-card {
    padding: 16px 18px; border-radius: 16px;
    background: linear-gradient(135deg, rgba(239,68,68,0.05) 0%, rgba(99,102,241,0.06) 100%);
    border: 1px solid var(--border, #e2e6ef); box-shadow: var(--cp-shadow-sm);
    display: flex; flex-direction: column; gap: 12px;
}
.cp-reschedule-info { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.cp-reschedule-date-display { display: flex; align-items: center; gap: 8px; font-size: 14.5px; font-weight: 800; color: var(--text, #0f172a); }
.cp-reschedule-controls { display: flex; flex-direction: column; gap: 10px; padding-top: 12px; border-top: 1px dashed var(--border, #e2e6ef); }
.cp-reschedule-input-wrap { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
.cp-reschedule-input { height: 38px; font-size: 13px; font-weight: 700; padding: 0 10px; border-radius: 10px; background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); color: var(--text, #0f172a); }
.cp-pill-btn {
    display: inline-flex; align-items: center; height: 26px; padding: 0 10px; border-radius: 99px;
    border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text2, #475569);
    font-size: 11px; font-weight: 700; cursor: pointer; transition: all .15s ease;
}
.cp-pill-btn:hover { border-color: var(--cp-brand); color: var(--cp-brand); background: var(--card2, #f5f7fb); }
.cp-pill-btn.is-selected { background: var(--cp-brand); color: #fff; border-color: var(--cp-brand); }
.cp-reschedule-quick-pills { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
.cp-btn-edit-date {
    height: 32px; padding: 0 12px; font-size: 12px; font-weight: 700; border-radius: 9px;
    background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); color: var(--text, #0f172a);
    cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all .15s ease;
}
.cp-btn-edit-date:hover { border-color: var(--cp-brand); color: var(--cp-brand); background: var(--card2, #f5f7fb); }
.cp-btn-edit-date.is-active { background: var(--cp-brand); color: #fff; border-color: var(--cp-brand); }

/* ── Drawer ── */
.cp-overlay { position: fixed; inset: 0; z-index: 9995; background: rgba(15,23,42,0.45); backdrop-filter: blur(3px); opacity: 0; visibility: hidden; transition: opacity .25s, visibility .25s; }
.cp-overlay.is-open { opacity: 1; visibility: visible; }
.cp-drawer {
    position: fixed; top: 0; right: 0; bottom: 0; z-index: 10000; width: min(640px, 100vw); display: flex; flex-direction: column;
    background: var(--card, #fff); color: var(--text, #0f172a); border-left: 1px solid var(--border, #e2e6ef);
    box-shadow: -24px 0 60px -20px rgba(15,23,42,0.35); transform: translateX(104%); transition: transform .32s cubic-bezier(.22,.9,.3,1);
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
}
.cp-drawer.is-open { transform: translateX(0); }
.cp-drawer-close {
    position: absolute; top: 16px; right: 16px; z-index: 2; width: 36px; height: 36px; border-radius: 10px; cursor: pointer;
    border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text2, #475569); font-size: 15px;
}
.cp-drawer-close:hover { color: var(--cp-brand); border-color: var(--cp-brand); }
.cp-drawer-content { display: flex; flex-direction: column; height: 100%; min-height: 0; }
.cp-d-head { padding: 22px 64px 18px 24px; border-bottom: 1px solid var(--border, #e2e6ef); background: linear-gradient(180deg, rgba(239,68,68,0.06), transparent); }
.cp-d-client { display: flex; align-items: center; gap: 10px; font-size: 12.5px; font-weight: 700; color: var(--text2, #475569); }
.cp-d-title { margin: 12px 0 10px; font-size: 20px; font-weight: 800; line-height: 1.3; letter-spacing: -.3px; }
.cp-d-tags { display: flex; flex-wrap: wrap; gap: 6px; }
.cp-tag { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; padding: 4px 9px; border-radius: 8px; background: var(--card2, #f5f7fb); border: 1px solid var(--border, #e2e6ef); color: var(--text2, #475569); text-transform: capitalize; }
.cp-d-body { flex: 1; overflow-y: auto; padding: 18px 24px 24px; display: flex; flex-direction: column; gap: 18px; }
.cp-d-section { display: flex; align-items: center; gap: 8px; margin: 0 0 10px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--text3, #94a3b8); }
.cp-d-section i { color: var(--cp-brand); }
.cp-channels { display: flex; flex-direction: column; gap: 8px; }
.cp-ch-row { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px; border: 1px solid var(--border, #e2e6ef); }
.cp-ch-icon { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; color: #fff; background: var(--ch); flex-shrink: 0; }
.cp-ch-name { flex: 1; min-width: 0; font-size: 13px; font-weight: 800; }
.cp-ch-name small { display: block; font-size: 11px; font-weight: 600; color: var(--text3, #94a3b8); }
.cp-ch-name a { color: var(--cp-brand); text-decoration: none; }
.cp-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.cp-fact { padding: 10px 12px; border-radius: 12px; background: var(--card2, #f5f7fb); border: 1px solid var(--border, #e2e6ef); min-width: 0; }
.cp-fact-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--text3, #94a3b8); margin-bottom: 4px; }
.cp-fact-val { font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-fact-val.is-late { color: var(--s-red-fg); }
.cp-text { margin: 0; font-size: 13.5px; line-height: 1.65; color: var(--text2, #475569); white-space: pre-line; }
.cp-d-foot { padding: 14px 24px; border-top: 1px solid var(--border, #e2e6ef); display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
.cp-muted { font-size: 12px; color: var(--text3, #94a3b8); }
.cp-banner { display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 12px; font-size: 13px; font-weight: 600; background: var(--tone-bg); color: var(--tone-fg); }

/* ── Drag to reschedule ── */
[data-move-task] { cursor: grab; }
[data-move-task]:active { cursor: grabbing; }
.cp-dragging { opacity: .45; }
.cp-is-dragging [data-drop-date] { transition: background .12s, box-shadow .12s; }
.cp-is-dragging [data-drop-date].is-drop-over { background: var(--cp-brand-soft) !important; box-shadow: inset 0 0 0 2px var(--cp-brand); }
.cp-is-dragging .cp-cell:not([data-drop-date]), .cp-is-dragging .cp-mday:not([data-drop-date]) { opacity: .45; }
.cp-hint { display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 600; color: var(--text3, #94a3b8); }
.cp-hint i { color: var(--cp-brand); }

/* ── Responsive & print ── */
@media (max-width: 1400px) { .cp-summary { grid-template-columns: repeat(4, minmax(0, 1fr)); } .cp-score { grid-column: 1 / -1; } }
@media (max-width: 1024px) { .cp-attention { grid-template-columns: 1fr; } }
@media (max-width: 768px) {
    .cp-hero { padding: 22px 20px; }
    .cp-hero h1 { font-size: 22px; }
    .cp-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    select.cp-field { flex: 1 1 100%; }
    .cp-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (prefers-reduced-motion: reduce) { .cp-card, .cp-stat, .cp-drawer, .cp-overlay { transition: none; } }
@media print {
    body * { visibility: hidden; }
    .cp-print, .cp-print * { visibility: visible; }
    .cp-print { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; border: none; }
    .cp-scroll { overflow: visible; }
    .cp-week, .cp-month { min-width: 0; }
    .cp-col-head { position: static; }
    .cp-card, .cp-channel, .cp-cell, .cp-mitem { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div class="cp-wrap" id="cpCalendarWrap">

    {{-- Presentation View Header Banner --}}
    <div class="cp-present-banner" id="cpPresentBanner">
        <div>
            <h3><i class="fas fa-sparkles"></i> Client Content Presentation View</h3>
            <p style="margin:2px 0 0;font-size:12px;opacity:0.85">Clean, unbranded preview formatted for client sign-off calls & presentations.</p>
        </div>
        <button type="button" class="cp-btn" id="cpExitPresentBtn" style="height:32px;font-size:12px;background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.25)"><i class="fas fa-xmark"></i> Exit Presentation Mode</button>
    </div>

    {{-- ── Hero ── --}}
    <section class="cp-hero">
        <div>
            <div class="cp-eyebrow"><i class="fas fa-calendar-check"></i> Content Calendar{{ $selectedClient ? ' · ' . $selectedClient->name : '' }}</div>
            <h1>{{ $isWeek ? 'Week of ' . $title : $title }}</h1>
            <p class="cp-hero-sub">
                <span><strong>{{ $summary['total'] }}</strong> {{ Str::plural('post', $summary['total']) }} planned</span>
                <span><strong>{{ $summary['counts']['done'] }}</strong> posted</span>
                <span><strong>{{ $summary['completion'] }}%</strong> complete</span>
                @if($summary['counts']['missed'] > 0)
                    <span><i class="fas fa-triangle-exclamation"></i> <strong>{{ $summary['counts']['missed'] }}</strong> missed</span>
                @endif
            </p>
        </div>
        <div class="cp-hero-actions">
            <div class="cp-glass">
                <a href="{{ $prevUrl }}" title="Previous {{ $view }}" aria-label="Previous {{ $view }}"><i class="fas fa-chevron-left"></i></a>
                <a href="{{ $url(['date' => today()->toDateString()]) }}" class="{{ $isCurrentPeriod ? 'is-active' : '' }}">Today</a>
                <a href="{{ $nextUrl }}" title="Next {{ $view }}" aria-label="Next {{ $view }}"><i class="fas fa-chevron-right"></i></a>
            </div>
            <div class="cp-glass" role="tablist" aria-label="Calendar view">
                <a href="{{ $url(['view' => 'week']) }}" class="{{ $view === 'week' ? 'is-active' : '' }}" role="tab"><i class="fas fa-table-cells"></i> Week</a>
                <a href="{{ $url(['view' => 'month']) }}" class="{{ $view === 'month' ? 'is-active' : '' }}" role="tab"><i class="fas fa-calendar-days"></i> Month</a>
                <a href="{{ $url(['view' => 'feed']) }}" class="{{ $view === 'feed' ? 'is-active' : '' }}" role="tab"><i class="fa-brands fa-instagram"></i> Feed Preview</a>
            </div>
            <div class="cp-glass">
                <button type="button" id="cpPresentationToggle" title="Clean Client Presentation View"><i class="fas fa-display"></i> <span id="cpPresentLabel">Present</span></button>
                <button type="button" onclick="window.print()" title="Print this calendar"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
    </section>

    {{-- ── Controls ── --}}
    <div class="cp-controls">
        <select class="cp-field" aria-label="Client" onchange="window.location.href = this.value">
            <option value="{{ $url(['client' => null]) }}">All clients</option>
            @foreach($clients as $client)
                <option value="{{ $url(['client' => $client->id]) }}" @selected($clientId === $client->id)>{{ $client->name }}</option>
            @endforeach
        </select>
        <a href="{{ $url(['mine' => $mine ? null : 1]) }}" class="cp-toggle {{ $mine ? 'is-on' : '' }}" role="switch" aria-checked="{{ $mine ? 'true' : 'false' }}">
            <span class="knob"></span> Only tasks I created
        </a>
        <label class="cp-search">
            <i class="fas fa-magnifying-glass"></i>
            <input type="search" class="cp-field" id="cpSearch" placeholder="Search posts, clients, designers..." autocomplete="off" aria-label="Search posts">
        </label>
        <button type="button" class="cp-btn cp-btn-primary" data-quick-create="{{ today()->toDateString() }}" style="height:40px">
            <i class="fas fa-plus"></i> Quick Schedule
        </button>
    </div>

    {{-- ── Filter Matrix ── --}}
    <div class="cp-filter-matrix">
        <div class="cp-filter-group">
            <span class="cp-filter-label"><i class="fas fa-tower-broadcast"></i> Platform:</span>
            <a href="{{ $url(['platform' => null]) }}" class="cp-chip {{ empty($activeFilters['platform']) ? 'is-active' : '' }}">All</a>
            <a href="{{ $url(['platform' => 'instagram']) }}" class="cp-chip {{ ($activeFilters['platform'] ?? '') === 'instagram' ? 'is-active' : '' }}"><i class="fa-brands fa-instagram" style="color:#E1306C"></i> Instagram</a>
            <a href="{{ $url(['platform' => 'linkedin']) }}" class="cp-chip {{ ($activeFilters['platform'] ?? '') === 'linkedin' ? 'is-active' : '' }}"><i class="fa-brands fa-linkedin-in" style="color:#0A66C2"></i> LinkedIn</a>
            <a href="{{ $url(['platform' => 'facebook']) }}" class="cp-chip {{ ($activeFilters['platform'] ?? '') === 'facebook' ? 'is-active' : '' }}"><i class="fa-brands fa-facebook-f" style="color:#1877F2"></i> Facebook</a>
            <a href="{{ $url(['platform' => 'twitter']) }}" class="cp-chip {{ ($activeFilters['platform'] ?? '') === 'twitter' ? 'is-active' : '' }}"><i class="fa-brands fa-x-twitter"></i> X</a>
            <a href="{{ $url(['platform' => 'youtube']) }}" class="cp-chip {{ ($activeFilters['platform'] ?? '') === 'youtube' ? 'is-active' : '' }}"><i class="fa-brands fa-youtube" style="color:#FF0000"></i> YouTube</a>
        </div>
        <div class="cp-filter-group">
            <span class="cp-filter-label"><i class="fas fa-photo-film"></i> Format:</span>
            <a href="{{ $url(['type' => null]) }}" class="cp-chip {{ empty($activeFilters['type']) ? 'is-active' : '' }}">All</a>
            <a href="{{ $url(['type' => 'reel']) }}" class="cp-chip {{ ($activeFilters['type'] ?? '') === 'reel' ? 'is-active' : '' }}"><i class="fas fa-film"></i> Reels</a>
            <a href="{{ $url(['type' => 'carousel']) }}" class="cp-chip {{ ($activeFilters['type'] ?? '') === 'carousel' ? 'is-active' : '' }}"><i class="fas fa-images"></i> Carousels</a>
            <a href="{{ $url(['type' => 'post']) }}" class="cp-chip {{ ($activeFilters['type'] ?? '') === 'post' ? 'is-active' : '' }}"><i class="fas fa-image"></i> Posts</a>
            <a href="{{ $url(['type' => 'story']) }}" class="cp-chip {{ ($activeFilters['type'] ?? '') === 'story' ? 'is-active' : '' }}"><i class="fas fa-mobile-screen"></i> Stories</a>
        </div>
        <div class="cp-filter-group">
            <span class="cp-filter-label"><i class="fas fa-paint-brush"></i> Designer:</span>
            <select class="cp-field" style="height:32px;font-size:12px;min-width:140px;padding:0 8px" onchange="window.location.href = this.value">
                <option value="{{ $url(['assignee' => null]) }}">All Designers</option>
                @foreach($designers as $des)
                    <option value="{{ $url(['assignee' => $des->id]) }}" @selected(($activeFilters['assignee'] ?? null) == $des->id)>{{ $des->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ── Summary: completion + one tile per delivery state (tiles filter the calendar) ── --}}
    <div class="cp-summary">
        <div class="cp-score">
            <div class="cp-ring" style="--deg: {{ $ringDeg }}deg"><span>{{ $summary['completion'] }}%</span></div>
            <div>
                <div class="cp-score-title">{{ $summary['counts']['done'] }} of {{ $summary['total'] }} posted</div>
                <div class="cp-score-sub">
                    Across {{ $summary['tasks'] }} {{ Str::plural('task', $summary['tasks']) }} {{ $isWeek ? 'this week' : 'this month' }}.
                    @if($summary['total'] > 0)
                        {{ $summary['total'] - $summary['counts']['done'] }} still to go.
                    @endif
                </div>
            </div>
        </div>
        @foreach($states as $key => $state)
            <button type="button" class="cp-stat tone-{{ $state['tone'] }}" data-state-filter="{{ $key }}" aria-pressed="false">
                <span class="cp-stat-top">
                    <span class="cp-stat-val">{{ $summary['counts'][$key] }}</span>
                    <span class="cp-stat-icon"><i class="fas {{ $state['icon'] }}"></i></span>
                </span>
                <span class="cp-stat-label">{{ $state['label'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- ── Needs attention ── --}}
    @php
        $attentionCards = [
            ['key' => 'missed', 'title' => 'Missed posts', 'sub' => 'Go-live date passed, not posted (last 30 days)', 'icon' => 'fa-triangle-exclamation', 'tone' => 'red', 'empty' => 'Nothing missed. Every post went out on time.'],
            ['key' => 'atRisk', 'title' => 'Going live in 48h, not ready', 'sub' => 'Still in production, review or approval', 'icon' => 'fa-hourglass-half', 'tone' => 'amber', 'empty' => 'Everything going live soon is ready.'],
            ['key' => 'review', 'title' => 'Waiting for review', 'sub' => 'Designs submitted for strategist review', 'icon' => 'fa-eye', 'tone' => 'violet', 'empty' => 'No designs waiting for review.'],
        ];
    @endphp
    <div class="cp-attention">
        @foreach($attentionCards as $box)
            @php $items = $attention[$box['key']]; @endphp
            <div class="cp-att tone-{{ $box['tone'] }}">
                <div class="cp-att-head">
                    <div class="cp-att-title" title="{{ $box['sub'] }}"><i class="fas {{ $box['icon'] }}"></i> {{ $box['title'] }}</div>
                    <span class="cp-att-count">{{ $items->count() }}</span>
                </div>
                <div class="cp-att-list">
                    @forelse($items->take(4) as $item)
                        @php
                            $daysAway = (int) today()->diffInDays($item->planDate);
                            $when = match (true) {
                                $daysAway === 0 => 'Today',
                                $daysAway === 1 => 'Tomorrow',
                                $daysAway === -1 => 'Yesterday',
                                $daysAway < 0 => abs($daysAway) . 'd ago',
                                default => $item->planDate->format('M d'),
                            };
                        @endphp
                        <button type="button" class="cp-att-item" data-open-task="{{ $item->task->id }}">
                            <span class="cp-logo" style="width:26px;height:26px;border-radius:7px;font-size:10px;background: {{ $item->task->client->color ?? $hue($item->task->client->name ?? '') }}">
                                @if($item->task->client?->logo)
                                    <img src="{{ asset('storage/' . $item->task->client->logo) }}" alt="">
                                @else
                                    {{ $initials($item->task->client->name ?? '?') }}
                                @endif
                            </span>
                            <span class="t">
                                <b>{{ $item->task->title ?? 'Untitled' }}</b>
                                <span>{{ $item->task->client->name ?? 'No client' }} · {{ $item->task->assignee->name ?? 'Unassigned' }}</span>
                            </span>
                            <span class="d">{{ $when }}</span>
                        </button>
                    @empty
                        <div class="cp-att-empty"><i class="fas fa-circle-check"></i> {{ $box['empty'] }}</div>
                    @endforelse
                    @if($items->count() > 4)
                        <div class="cp-att-more">+{{ $items->count() - 4 }} more</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Calendar ── --}}
    <section class="cp-board cp-print">
        <div class="cp-board-head">
            <div class="cp-board-title">
                <i class="fas {{ $isWeek ? 'fa-table-cells' : 'fa-calendar-days' }}"></i>
                {{ $isWeek ? 'Channel plan · ' . $title : $title }}
                @if($tasks->contains(fn ($item) => $canMove($item->task)))
                    <span class="cp-hint"><i class="fas fa-up-down-left-right"></i> Drag a post to another day to reschedule</span>
                @endif
            </div>
            <div class="cp-legend">
                @foreach($states as $state)
                    <span class="tone-{{ $state['tone'] }}">{{ $state['label'] }}</span>
                @endforeach
            </div>
        </div>

        @if($view === 'week')
            @if($rows->isEmpty())
                <div class="cp-empty">
                    <div class="cp-empty-icon"><i class="fas fa-calendar-xmark"></i></div>
                    <h3>No content planned for this week</h3>
                    <p>{{ $selectedClient ? $selectedClient->name . ' has' : 'There are' }} no posts going live between {{ $title }}.</p>
                    @if($nextPlanned)
                        <a class="cp-btn cp-btn-primary" href="{{ $url(['date' => $nextPlanned->toDateString()]) }}"><i class="fas fa-forward"></i> Jump to next planned post · {{ $nextPlanned->format('M d') }}</a>
                    @endif
                </div>
            @else
                <div class="cp-scroll">
                    <div class="cp-week">
                        <div class="cp-col-head is-channel">Channel</div>
                        @foreach($days as $day)
                            @php
                                $dayCards = $cardsByDate->get($day->toDateString(), collect());
                                $dayDone = $dayCards->where('state', 'done')->count();
                                $dayFestivals = $festivalsByDate->get($day->toDateString(), collect());
                            @endphp
                            <div class="cp-col-head {{ $day->isToday() ? 'is-today' : '' }} {{ $day->lt(today()) ? 'is-past' : '' }}">
                                <div class="cp-day-name">{{ $day->format('D') }}</div>
                                <div class="cp-day-num">{{ $day->format('d') }}</div>
                                @if($dayFestivals->isNotEmpty())
                                    @foreach($dayFestivals as $fest)
                                        <button type="button" class="cp-fest-chip" title="Festival: {{ $fest->name }} · Click to quick-schedule" data-quick-create="{{ $day->toDateString() }}" data-fest="{{ $fest->name }}">
                                            <span>{{ $fest->emoji ?: '🎉' }}</span>
                                            <span class="cp-fest-name">{{ $fest->name }}</span>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endforeach
                                @endif
                                @if($dayCards->isNotEmpty())
                                    <div class="cp-day-meter"><span style="width: {{ round($dayDone / $dayCards->count() * 100) }}%"></span></div>
                                    <div class="cp-day-count">{{ $dayDone }}/{{ $dayCards->count() }} posted</div>
                                @else
                                    <div class="cp-day-count">—</div>
                                @endif
                            </div>
                        @endforeach

                        @foreach($rows as $row)
                            <div class="cp-client-row">
                                <span class="cp-logo" style="background: {{ $row->client->color ?? $hue($row->client->name ?? '') }}">
                                    @if($row->client?->logo)
                                        <img src="{{ asset('storage/' . $row->client->logo) }}" alt="">
                                    @else
                                        {{ $initials($row->client->name ?? '?') }}
                                    @endif
                                </span>
                                <span class="cp-client-name">{{ $row->client->name ?? 'No client' }}</span>
                                <span class="cp-client-progress">
                                    <span class="cp-bar"><span style="width: {{ $row->total ? round($row->done / $row->total * 100) : 0 }}%"></span></span>
                                    {{ $row->done }}/{{ $row->total }} posted
                                </span>
                            </div>
                            @foreach($row->channels as $channel)
                                @php $pm = $platformMeta($channel->platform); @endphp
                                <div class="cp-channel" style="--ch: {{ $pm->color }}">
                                    <i class="{{ $pm->icon }}"></i>
                                    <span>{{ $pm->label }}<small>{{ $channel->done }}/{{ $channel->total }} posted</small></span>
                                </div>
                                @foreach($days as $day)
                                    <div class="cp-cell {{ $day->isToday() ? 'is-today' : '' }}" style="--ch: {{ $pm->color }}" @unless($day->lt(today())) data-drop-date="{{ $day->toDateString() }}" @endunless>
                                        @foreach($channel->byDate->get($day->toDateString(), collect())->sortBy(fn ($c) => $stateOrder[$c->state] ?? 99) as $card)
                                            @php
                                                $task = $card->item->task;
                                                $st = $states[$card->state];
                                            @endphp
                                            <button type="button" class="cp-card tone-{{ $st['tone'] }} is-state-{{ $card->state }}"
                                                    data-open-task="{{ $task->id }}" data-state="{{ $card->state }}"
                                                    @if($canMove($task)) draggable="true" data-move-task="{{ $task->id }}" data-move-field="{{ $task->post_date ? 'post_date' : 'deadline' }}" data-move-from="{{ $card->date }}" @endif
                                                    data-search="{{ mb_strtolower(implode(' ', array_filter([$task->title, $task->client->name ?? null, $task->assignee->name ?? null, $task->type, $pm->label]))) }}"
                                                    title="{{ $task->title }} · {{ $st['label'] }}">
                                                <span class="cp-card-top">
                                                    <span><i class="fas {{ $typeIcon($task->type) }}"></i> {{ $task->type ?? 'post' }}</span>
                                                    <span class="cp-card-state"><i class="fas {{ $card->state === 'done' ? 'fa-check' : $st['icon'] }}"></i></span>
                                                </span>
                                                <span class="cp-card-title">{{ $task->title ?? 'Untitled' }}</span>
                                                <span class="cp-card-foot">
                                                    <span class="cp-card-label">{{ $st['label'] }}</span>
                                                    @if($task->assignee)
                                                        <span class="cp-avatar" style="background: {{ $hue($task->assignee->name) }}" title="{{ $task->assignee->name }}">{{ $initials($task->assignee->name) }}</span>
                                                    @endif
                                                </span>
                                            </button>
                                        @endforeach
                                        {{-- Quick Add Slot Button --}}
                                        <button type="button" class="cp-slot-add-btn" data-quick-create="{{ $day->toDateString() }}" data-platform="{{ $channel->platform }}" data-client="{{ $row->client->id ?? '' }}" title="Schedule new post for {{ $row->client->name ?? 'client' }} on {{ $day->format('M d') }}">
                                            <i class="fas fa-plus"></i> Schedule
                                        </button>
                                    </div>
                                @endforeach
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endif
        @elseif($view === 'feed')
            <div class="cp-feed-view" id="cpFeedView">
                {{-- Quick Client Switcher Bar for Admin & Multi-Client Feeds --}}
                <div class="cp-feed-clients-bar">
                    <span class="cp-feed-bar-label"><i class="fas fa-building"></i> Client Feed:</span>
                    <div class="cp-feed-clients-scroll">
                        <a href="{{ $url(['client' => null, 'view' => 'feed']) }}" class="cp-chip {{ !$clientId ? 'is-active' : '' }}">
                            <i class="fas fa-layer-group"></i> All Clients ({{ $feedGrid->count() }})
                        </a>
                        @foreach($clients as $c)
                            @php
                                $cCount = $feedGrid->filter(fn($i) => ($i->task->client_id ?? null) === $c->id)->count();
                            @endphp
                            @if($cCount > 0 || $clientId === $c->id)
                                <a href="{{ $url(['client' => $c->id, 'view' => 'feed']) }}" 
                                   class="cp-chip {{ $clientId === $c->id ? 'is-active' : '' }}"
                                   @if($clientId === $c->id) style="background: {{ $c->color ?? '#ef4444' }}; border-color: {{ $c->color ?? '#ef4444' }}; color: #fff;" @endif>
                                    <span>{{ $c->emoji ?: '🏢' }} {{ $c->name }}</span>
                                    <span class="cp-count-pill">{{ $cCount }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Feed Studio Control Toolbar --}}
                <div class="cp-feed-toolbar">
                    <div class="cp-feed-toolbar-left">
                        <div class="cp-seg-group">
                            <span class="cp-seg-label"><i class="fas fa-eye"></i> Layout:</span>
                            <div class="cp-segmented" id="cpFeedLayoutTabs">
                                <button type="button" class="cp-seg-btn is-active" data-feed-mode="phone" title="Smartphone Mockup View (Contained & Compact)">
                                    <i class="fas fa-mobile-screen"></i> <span>Phone Frame</span>
                                </button>
                                <button type="button" class="cp-seg-btn" data-feed-mode="grid9" title="Classic 9-Grid Profile Focus">
                                    <i class="fas fa-border-all"></i> <span>9-Grid Focus</span>
                                </button>
                                <button type="button" class="cp-seg-btn" data-feed-mode="compact" title="Compact 4-Column Studio (Low Scroll)">
                                    <i class="fas fa-grip"></i> <span>Compact (4-Col)</span>
                                </button>
                                <button type="button" class="cp-seg-btn" data-feed-mode="grid" title="Standard 3-Column Grid">
                                    <i class="fas fa-table-cells"></i> <span>3-Col Grid</span>
                                </button>
                            </div>
                        </div>

                        <div class="cp-seg-group">
                            <span class="cp-seg-label"><i class="fas fa-list-ol"></i> Display:</span>
                            <div class="cp-segmented" id="cpFeedLimitTabs">
                                <button type="button" class="cp-seg-btn is-active" data-feed-limit="9" title="First 9 Posts (Instagram Profile Window)">Top 9</button>
                                <button type="button" class="cp-seg-btn" data-feed-limit="18" title="First 18 Posts">Top 18</button>
                                <button type="button" class="cp-seg-btn" data-feed-limit="all" title="Show All Posts">All ({{ $feedGrid->count() }})</button>
                            </div>
                        </div>
                    </div>

                    <div class="cp-feed-toolbar-right">
                        <div class="cp-feed-cadence-badge">
                            <i class="fa-brands fa-instagram" style="color:#e1306c"></i>
                            <span><strong>{{ $feedGrid->count() }}</strong> posts scheduled</span>
                        </div>
                        <button type="button" class="cp-btn cp-btn-primary" data-quick-create="{{ today()->toDateString() }}" data-platform="instagram">
                            <i class="fas fa-plus"></i> Schedule Post
                        </button>
                    </div>
                </div>

                @if($feedGrid->isEmpty())
                    <div class="cp-empty">
                        <div class="cp-empty-icon"><i class="fa-brands fa-instagram"></i></div>
                        <h3>No visual feed posts planned</h3>
                        <p>No social media or visual posts scheduled for {{ $title }}.</p>
                        <button type="button" class="cp-btn cp-btn-primary" data-quick-create="{{ today()->toDateString() }}" data-platform="instagram">
                            <i class="fas fa-plus"></i> Plan First Post
                        </button>
                    </div>
                @else
                    {{-- Studio Split Workspace: Left feed preview + Right live post inspector --}}
                    <div class="cp-feed-studio" id="cpFeedStudio">
                        {{-- ── LEFT PREVIEW COLUMN ── --}}
                        <div class="cp-feed-stage-col">

                            {{-- 1. PHONE MOCKUP MODE --}}
                            <div class="cp-phone-wrapper" id="cpPhoneWrapper">
                                <div class="cp-phone-chassis">
                                    {{-- Dynamic Island & Speaker --}}
                                    <div class="cp-phone-island">
                                        <div class="cp-phone-camera"></div>
                                    </div>
                                    {{-- Phone Status Bar --}}
                                    <div class="cp-phone-status-bar">
                                        <span>9:41</span>
                                        <div class="cp-phone-status-icons">
                                            <i class="fas fa-signal"></i>
                                            <i class="fas fa-wifi"></i>
                                            <i class="fas fa-battery-full"></i>
                                        </div>
                                    </div>
                                    {{-- Compact Instagram App Header --}}
                                    <div class="cp-phone-app-header">
                                        <div class="cp-phone-top-bar">
                                            <div class="cp-phone-username">
                                                <span>{{ Str::lower(Str::slug($selectedClient?->name ?? 'agency_feed', '_')) }}</span>
                                                <i class="fas fa-circle-check" style="color:#0ea5e9;font-size:12px"></i>
                                                <i class="fas fa-chevron-down" style="font-size:10px;opacity:.6"></i>
                                            </div>
                                            <div class="cp-phone-top-actions">
                                                <i class="fas fa-plus-square" data-quick-create="{{ today()->toDateString() }}" data-platform="instagram" title="Quick Add Post"></i>
                                                <i class="fas fa-bars"></i>
                                            </div>
                                        </div>

                                        {{-- Profile Stats Row --}}
                                        <div class="cp-phone-profile-row">
                                            <div class="cp-phone-avatar-ring">
                                                <div class="cp-phone-avatar-inner" style="background: {{ $selectedClient?->color ?? '#ef4444' }}">
                                                    @if($selectedClient?->logo)
                                                        <img src="{{ asset('storage/' . $selectedClient->logo) }}" alt="{{ $selectedClient->name }}">
                                                    @else
                                                        {{ $initials($selectedClient?->name ?? 'TMS') }}
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="cp-phone-stats-cols">
                                                <div class="cp-phone-stat">
                                                    <strong>{{ $feedGrid->count() }}</strong>
                                                    <span>posts</span>
                                                </div>
                                                <div class="cp-phone-stat">
                                                    <strong>{{ $summary['counts']['done'] }}</strong>
                                                    <span>posted</span>
                                                </div>
                                                <div class="cp-phone-stat">
                                                    <strong>{{ $summary['completion'] }}%</strong>
                                                    <span>done</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mini Bio --}}
                                        <div class="cp-phone-bio">
                                            <div class="cp-phone-name">{{ $selectedClient ? $selectedClient->name : 'Agency Content Strategy' }}</div>
                                            <div class="cp-phone-category">Media / Agency · Content Cadence</div>
                                            <div class="cp-phone-link"><i class="fas fa-link"></i> {{ Str::lower(Str::slug($selectedClient?->name ?? 'agency')) }}.com</div>
                                        </div>

                                        {{-- App Action Buttons --}}
                                        <div class="cp-phone-buttons">
                                            <button type="button" class="cp-phone-btn">Edit Profile</button>
                                            <button type="button" class="cp-phone-btn">Share Profile</button>
                                            <button type="button" class="cp-phone-btn cp-phone-btn-icon"><i class="fas fa-user-plus"></i></button>
                                        </div>

                                        {{-- Feed Nav Tabs --}}
                                        <div class="cp-phone-tabs">
                                            <div class="cp-phone-tab is-active"><i class="fas fa-table-cells"></i></div>
                                            <div class="cp-phone-tab"><i class="fa-brands fa-youtube"></i></div>
                                            <div class="cp-phone-tab"><i class="fas fa-id-badge"></i></div>
                                        </div>
                                    </div>

                                    {{-- Phone Feed Scrollable Grid --}}
                                    <div class="cp-phone-feed-scroll" id="cpPhoneFeedScroll">
                                        <div class="cp-phone-grid" id="cpPhoneGridContainer">
                                            @foreach($feedGrid as $index => $item)
                                                @php
                                                    $task = $item->task;
                                                    $st = $states[$item->state];
                                                    $thumb = $item->thumbnail;
                                                    $previewUrl = $thumb['url'] ?? null;
                                                @endphp
                                                <div class="cp-feed-tile tone-{{ $st['tone'] }} {{ $index === 0 ? 'is-selected' : '' }}"
                                                     data-feed-index="{{ $index }}"
                                                     data-task-id="{{ $task->id }}"
                                                     data-title="{{ $task->title }}"
                                                     data-headline="{{ $task->headline_copy ?: '' }}"
                                                     data-caption="{{ $task->caption ?: '' }}"
                                                     data-hashtags="{{ $task->hashtags ?: '' }}"
                                                     data-thumb="{{ $previewUrl ?: '' }}"
                                                     data-type="{{ $task->type ?: 'post' }}"
                                                     data-state-label="{{ $st['label'] }}"
                                                     data-state-tone="{{ $st['tone'] }}"
                                                     data-date="{{ $item->planDate->format('M d, Y') }}"
                                                     data-client="{{ $task->client->name ?? 'Client' }}"
                                                     data-assignee="{{ $task->assignee->name ?? 'Unassigned' }}"
                                                     title="{{ $task->title }} · {{ $item->planDate->format('M d') }}">
                                                    
                                                    <span class="cp-tile-date">{{ $item->planDate->format('d M') }}</span>
                                                    <span class="cp-tile-type-badge">
                                                        <i class="fas {{ $typeIcon($task->type) }}"></i>
                                                    </span>
                                                    
                                                    @if($previewUrl)
                                                        <img src="{{ $previewUrl }}" alt="{{ $task->title }}" class="cp-tile-img" loading="lazy">
                                                    @else
                                                        <div class="cp-tile-empty">
                                                            <div class="cp-tile-empty-title">{{ Str::limit($task->headline_copy ?: $task->title, 28) }}</div>
                                                            <div class="cp-tile-empty-client">{{ $task->client->name ?? '' }}</div>
                                                        </div>
                                                    @endif

                                                    <div class="cp-tile-active-indicator"></div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Phone Home Indicator Bar --}}
                                    <div class="cp-phone-home-bar"></div>
                                </div>
                            </div>

                            {{-- 2. 9-GRID FOCUS MODE --}}
                            <div class="cp-grid9-wrapper" id="cpGrid9Wrapper" style="display:none">
                                <div class="cp-grid9-card">
                                    <div class="cp-grid9-header">
                                        <div class="cp-grid9-title">
                                            <i class="fas fa-border-all" style="color:var(--cp-brand)"></i>
                                            <span>Instagram 9-Grid Aesthetic Simulator</span>
                                        </div>
                                        <div class="cp-grid9-pager" id="cpGrid9Pager">
                                            <button type="button" class="cp-pager-btn" id="cpGrid9Prev" title="Previous 9 Posts"><i class="fas fa-chevron-left"></i></button>
                                            <span id="cpGrid9PageIndicator" style="font-size:12px;font-weight:700">Page 1</span>
                                            <button type="button" class="cp-pager-btn" id="cpGrid9Next" title="Next 9 Posts"><i class="fas fa-chevron-right"></i></button>
                                        </div>
                                    </div>
                                    <div class="cp-grid9-grid" id="cpGrid9Container">
                                        {{-- Injected dynamically from feed items --}}
                                    </div>
                                </div>
                            </div>

                            {{-- 3. COMPACT & STANDARD STUDIO GRID CONTAINER --}}
                            <div class="cp-studio-grid-wrapper" id="cpStudioGridWrapper" style="display:none">
                                <div class="cp-studio-grid-scroll" id="cpStudioGridContainer">
                                    {{-- Injected dynamically --}}
                                </div>
                            </div>

                        </div>

                        {{-- ── RIGHT LIVE INSPECTOR COLUMN ── --}}
                        <div class="cp-feed-inspector-col">
                            <div class="cp-live-inspector-card" id="cpLiveInspectorCard">
                                {{-- Inspector Header --}}
                                <div class="cp-insp-top">
                                    <div class="cp-insp-badges">
                                        <span class="cp-pill tone-green" id="inspStatePill">Scheduled</span>
                                        <span class="cp-insp-format" id="inspFormatPill"><i class="fas fa-image"></i> Post</span>
                                    </div>
                                    <div class="cp-insp-top-actions">
                                        <button type="button" class="cp-insp-action-btn" id="inspOpenDrawerBtn" title="Inspect Full Deliverables & Comments">
                                            <i class="fas fa-expand"></i> Full View
                                        </button>
                                    </div>
                                </div>

                                {{-- Main Title & Date --}}
                                <h3 class="cp-insp-title" id="inspTitle">Select a post to inspect</h3>
                                <div class="cp-insp-meta-row">
                                    <span><i class="fas fa-calendar-day"></i> <strong id="inspDate">—</strong></span>
                                    <span><i class="fas fa-building"></i> <span id="inspClient">—</span></span>
                                    <span><i class="fas fa-user-pen"></i> <span id="inspAssignee">—</span></span>
                                </div>

                                {{-- Artwork Preview Box --}}
                                <div class="cp-insp-media-box" id="inspMediaBox">
                                    <img src="" alt="" id="inspMediaImg" style="display:none">
                                    <div class="cp-insp-media-empty" id="inspMediaEmpty">
                                        <i class="fa-brands fa-instagram"></i>
                                        <span>No image preview uploaded yet</span>
                                    </div>
                                    <button type="button" class="cp-insp-zoom-btn" id="inspZoomBtn" title="Open Full Screen Lightbox" style="display:none">
                                        <i class="fas fa-magnifying-glass-plus"></i> Zoom
                                    </button>
                                </div>

                                {{-- Copywriting Engine: Headline Hook --}}
                                <div class="cp-insp-section" id="inspHeadlineSec">
                                    <div class="cp-insp-sec-head">
                                        <span><i class="fas fa-bolt" style="color:#f59e0b"></i> Headline Hook</span>
                                        <button type="button" class="cp-copy-btn" id="inspCopyHookBtn" title="Copy headline hook">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                    <blockquote class="cp-insp-quote" id="inspHeadlineText">
                                        No headline hook added.
                                    </blockquote>
                                </div>

                                {{-- Copywriting Engine: Caption --}}
                                <div class="cp-insp-section" id="inspCaptionSec">
                                    <div class="cp-insp-sec-head">
                                        <span><i class="fas fa-align-left" style="color:#0ea5e9"></i> Caption</span>
                                        <button type="button" class="cp-copy-btn" id="inspCopyCaptionBtn" title="Copy full caption">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                    <div class="cp-insp-caption-box" id="inspCaptionText">
                                        No caption written yet.
                                    </div>
                                </div>

                                {{-- Copywriting Engine: Hashtags --}}
                                <div class="cp-insp-section" id="inspHashtagsSec">
                                    <div class="cp-insp-sec-head">
                                        <span><i class="fas fa-hashtag" style="color:#8b5cf6"></i> Hashtags</span>
                                        <button type="button" class="cp-copy-btn" id="inspCopyTagsBtn" title="Copy all hashtags">
                                            <i class="fas fa-copy"></i> Copy Tags
                                        </button>
                                    </div>
                                    <div class="cp-insp-tags-wrap" id="inspHashtagsText">
                                        No hashtags added.
                                    </div>
                                </div>

                                {{-- Bottom Action Footer --}}
                                <div class="cp-insp-footer">
                                    <button type="button" class="cp-btn cp-btn-primary" style="flex:1" id="inspOpenFullBtn">
                                        <i class="fas fa-sliders-h"></i> Open Task Drawer
                                    </button>
                                    <a href="#" class="cp-btn" id="inspEditLink" style="padding:0 12px" title="Edit Task">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="cp-scroll">
                <div class="cp-month">
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dow)
                        <div class="cp-month-dow">{{ $dow }}</div>
                    @endforeach
                    @foreach($days as $day)
                        @php
                            $dayTasks = $tasks->filter(fn ($item) => $item->planDate->isSameDay($day))
                                ->sortBy(fn ($item) => $stateOrder[$item->state] ?? 99)->values();
                            $dayCards = $cardsByDate->get($day->toDateString(), collect());
                            $dayDone = $dayCards->where('state', 'done')->count();
                            $inMonth = $day->month === $periodStart->month;
                            $dayFestivals = $festivalsByDate->get($day->toDateString(), collect());
                        @endphp
                        <div @if($dayTasks->isNotEmpty()) data-open-day="{{ $day->toDateString() }}" tabindex="0" role="button" title="See all {{ $dayTasks->count() }} posts on {{ $day->format('M d') }}" @endif @unless($day->lt(today())) data-drop-date="{{ $day->toDateString() }}" @endunless
                           class="cp-mday {{ $inMonth ? '' : 'is-out' }} {{ $day->isToday() ? 'is-today' : '' }} {{ $dayTasks->isEmpty() ? 'is-empty' : '' }}">
                            <span class="cp-mday-top">
                                <span class="cp-mday-num">{{ $day->format('j') }}</span>
                                @if($dayCards->isNotEmpty())
                                    <span class="cp-mday-ratio {{ $dayDone === $dayCards->count() ? 'is-complete' : '' }}">{{ $dayDone }}/{{ $dayCards->count() }} posted</span>
                                @endif
                                <button type="button" class="cp-mday-add-btn" data-quick-create="{{ $day->toDateString() }}" title="Add post on {{ $day->format('M d') }}">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </span>
                            @if($dayFestivals->isNotEmpty())
                                @foreach($dayFestivals as $fest)
                                    <div class="cp-fest-chip-sm" title="Festival: {{ $fest->name }} · Click to plan" data-quick-create="{{ $day->toDateString() }}" data-fest="{{ $fest->name }}">
                                        <span>{{ $fest->emoji ?: '🎉' }} {{ $fest->name }}</span>
                                    </div>
                                @endforeach
                            @endif
                            @if($dayCards->isNotEmpty())
                                <span class="cp-mday-meter"><span style="width: {{ round($dayDone / $dayCards->count() * 100) }}%"></span></span>
                            @endif
                            @foreach($dayTasks->take(3) as $item)
                                @php $st = $states[$item->state]; @endphp
                                <button type="button" class="cp-mitem tone-{{ $st['tone'] }}" data-open-task="{{ $item->task->id }}" data-state="{{ $item->state }}"
                                        @if($canMove($item->task)) draggable="true" data-move-task="{{ $item->task->id }}" data-move-field="{{ $item->task->post_date ? 'post_date' : 'deadline' }}" data-move-from="{{ $item->planDate->toDateString() }}" @endif
                                        data-search="{{ mb_strtolower(implode(' ', array_filter([$item->task->title, $item->task->client->name ?? null, $item->task->assignee->name ?? null, $item->task->type]))) }}"
                                        title="{{ $item->task->client->name ?? '' }} · {{ $item->task->title }} · {{ $st['label'] }}">
                                    <i class="fas {{ $item->state === 'done' ? 'fa-circle-check' : $st['icon'] }}"></i>
                                    <span>{{ $item->task->title ?? 'Untitled' }}</span>
                                </button>
                            @endforeach
                            @if($dayTasks->count() > 3)
                                <span class="cp-mmore">+{{ $dayTasks->count() - 3 }} more · view all</span>
                            @endif
                        </div>

                        @if($dayTasks->isNotEmpty())
                            {{-- Full list for this day, shown in the drawer --}}
                            <template id="cp-day-{{ $day->toDateString() }}">
                                <div class="cp-d-head">
                                    <div class="cp-d-client"><i class="fas fa-calendar-day" style="color:var(--cp-brand)"></i> {{ $day->isToday() ? 'Today' : $day->format('l') }}</div>
                                    <h2 class="cp-d-title">{{ $day->format('F j, Y') }}</h2>
                                    <div class="cp-d-tags">
                                        <span class="cp-pill tone-green"><i class="fas fa-circle-check"></i> {{ $dayDone }}/{{ $dayCards->count() }} posted</span>
                                        <span class="cp-tag"><i class="fas fa-layer-group"></i> {{ $dayTasks->count() }} {{ Str::plural('task', $dayTasks->count()) }}</span>
                                    </div>
                                </div>
                                <div class="cp-d-body">
                                    <div class="cp-day-list">
                                        @foreach($dayTasks as $item)
                                            @php $st = $states[$item->state]; @endphp
                                            <button type="button" class="cp-day-item tone-{{ $st['tone'] }}" data-open-task="{{ $item->task->id }}" data-from-day="{{ $day->toDateString() }}">
                                                <span class="cp-logo" style="width:34px;height:34px;background: {{ $item->task->client->color ?? $hue($item->task->client->name ?? '') }}">
                                                    @if($item->task->client?->logo)
                                                        <img src="{{ asset('storage/' . $item->task->client->logo) }}" alt="">
                                                    @else
                                                        {{ $initials($item->task->client->name ?? '?') }}
                                                    @endif
                                                </span>
                                                <span class="cp-day-item-main">
                                                    <b>{{ $item->task->title ?? 'Untitled' }}</b>
                                                    <span>
                                                        {{ $item->task->client->name ?? 'No client' }} · <span style="text-transform:capitalize">{{ $item->task->type ?? 'post' }}</span> · {{ $item->task->assignee->name ?? 'Unassigned' }}
                                                    </span>
                                                    <span class="cp-day-item-channels">
                                                        @foreach($item->channels as $channel)
                                                            @php $pm = $platformMeta($channel['platform']); @endphp
                                                            <i class="{{ $pm->icon }} {{ $channel['state'] === 'done' ? 'is-posted' : '' }}" style="--ch: {{ $pm->color }}" title="{{ $pm->label }}: {{ $states[$channel['state']]['label'] }}"></i>
                                                        @endforeach
                                                    </span>
                                                </span>
                                                <span class="cp-pill tone-{{ $st['tone'] }}"><i class="fas {{ $st['icon'] }}"></i> {{ $st['label'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="cp-d-foot">
                                    <span class="cp-muted">Click a post for channel details</span>
                                    <a href="{{ $url(['view' => 'week', 'date' => $day->toDateString()]) }}" class="cp-btn"><i class="fas fa-table-cells"></i> Open this week</a>
                                </div>
                            </template>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </section>
</div>

{{-- ── Detail drawer content, one template per task on screen ── --}}
@php
    $drawerItems = $tasks->concat($attention['missed'])->concat($attention['atRisk'])->concat($attention['review'])
        ->unique(fn ($item) => $item->task->id);
@endphp
@foreach($drawerItems as $item)
    @php
        $task = $item->task;
        $st = $states[$item->state];
        $deadlineLate = $task->deadline && \Illuminate\Support\Carbon::parse($task->deadline)->lt(today())
            && !in_array($task->status, ['completed', 'published'], true);
    @endphp
    <template id="cp-task-{{ $task->id }}">
        <div class="cp-d-head">
            <div class="cp-d-client">
                <span class="cp-logo" style="background: {{ $task->client->color ?? $hue($task->client->name ?? '') }}">
                    @if($task->client?->logo)
                        <img src="{{ asset('storage/' . $task->client->logo) }}" alt="">
                    @else
                        {{ $initials($task->client->name ?? '?') }}
                    @endif
                </span>
                {{ $task->client->name ?? 'No client' }} · #{{ $task->id }}
            </div>
            <h2 class="cp-d-title">{{ $task->title ?? 'Untitled' }}</h2>
            <div class="cp-d-tags">
                <span class="cp-pill tone-{{ $st['tone'] }}"><i class="fas {{ $st['icon'] }}"></i> {{ $st['label'] }}</span>
                <span class="cp-tag"><i class="fas {{ $typeIcon($task->type) }}"></i> {{ $task->type ?? 'post' }}</span>
                @if($task->priority)
                    <span class="cp-tag"><i class="fas fa-flag"></i> {{ $task->priority }}</span>
                @endif
                @if(($task->revision_count ?? 0) > 0)
                    <span class="cp-tag"><i class="fas fa-rotate"></i> Revision {{ $task->revision_count }}</span>
                @endif
            </div>
        </div>
        <div class="cp-d-body">
            @if($item->state === 'missed')
                <div class="cp-banner tone-red"><i class="fas fa-triangle-exclamation"></i> Go-live date {{ $item->planDate->format('D, M d') }} has passed and this hasn't been posted on every channel.</div>
            @elseif($item->isToday && $item->state !== 'done')
                <div class="cp-banner tone-amber"><i class="fas fa-bolt"></i> Goes live today, currently {{ strtolower($st['label']) }}.</div>
            @elseif($item->state === 'done')
                <div class="cp-banner tone-green"><i class="fas fa-circle-check"></i> Posted on {{ $item->platformCount > 1 ? 'all ' . $item->platformCount . ' channels' : 'its channel' }}.</div>
            @endif

            {{-- ── Posting Schedule & Date Changer ── --}}
            <div class="cp-reschedule-card">
                <div class="cp-reschedule-info">
                    <div>
                        <div class="cp-fact-label"><i class="fas fa-calendar-day" style="color:var(--cp-brand)"></i> Scheduled Posting Date</div>
                        <div class="cp-reschedule-date-display" id="rescheduleDisplay-{{ $task->id }}">
                            <span class="cp-reschedule-text">{{ $task->post_date ? \Illuminate\Support\Carbon::parse($task->post_date)->format('l, F j, Y') : $item->planDate->format('l, F j, Y') }}</span>
                            @if($item->isToday)
                                <span class="cp-pill tone-amber" style="padding:2px 8px;font-size:10px">Live Today</span>
                            @elseif($item->state === 'missed')
                                <span class="cp-pill tone-red" style="padding:2px 8px;font-size:10px">Overdue</span>
                            @elseif($item->state === 'done')
                                <span class="cp-pill tone-green" style="padding:2px 8px;font-size:10px">Published</span>
                            @endif
                        </div>
                    </div>
                    @if($canMove($task))
                        <button type="button" class="cp-btn-edit-date" data-toggle-reschedule="{{ $task->id }}" title="Change scheduled posting date">
                            <i class="fas fa-calendar-pen"></i> <span class="cp-toggle-label">Change Date</span>
                        </button>
                    @endif
                </div>

                @if($canMove($task))
                    <div class="cp-reschedule-controls" id="rescheduleControls-{{ $task->id }}" style="display:none">
                        <div class="cp-reschedule-input-wrap">
                            <span class="cp-fact-label" style="font-weight:800;color:var(--text,#0f172a)"><i class="fas fa-calendar-pen" style="color:var(--cp-brand)"></i> New Posting Date:</span>
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                <input type="date" class="cp-field cp-reschedule-input" id="postDateInput-{{ $task->id }}"
                                       value="{{ $task->post_date ? \Illuminate\Support\Carbon::parse($task->post_date)->toDateString() : $item->planDate->toDateString() }}"
                                       aria-label="Change posting date">
                                <button type="button" class="cp-btn cp-btn-primary cp-reschedule-submit-btn" data-apply-post-date="{{ $task->id }}" title="Save new posting date">
                                    <i class="fas fa-check"></i> Save Date
                                </button>
                                <button type="button" class="cp-btn" data-cancel-reschedule="{{ $task->id }}" title="Cancel date change" style="height:38px;padding:0 12px">
                                    Cancel
                                </button>
                            </div>
                        </div>
                        <div class="cp-reschedule-quick-pills">
                            <span class="cp-muted" style="font-size:11px;font-weight:700">Quick Shift:</span>
                            <button type="button" class="cp-pill-btn" data-quick-date="{{ $task->id }}" data-quick-val="{{ today()->toDateString() }}">Today</button>
                            <button type="button" class="cp-pill-btn" data-quick-date="{{ $task->id }}" data-quick-val="{{ today()->addDay()->toDateString() }}">Tomorrow</button>
                            <button type="button" class="cp-pill-btn" data-quick-date="{{ $task->id }}" data-quick-val="{{ today()->addDays(2)->toDateString() }}">+2 Days</button>
                            <button type="button" class="cp-pill-btn" data-quick-date="{{ $task->id }}" data-quick-val="{{ today()->addDays(7)->toDateString() }}">+1 Week</button>
                            <button type="button" class="cp-pill-btn" data-quick-date="{{ $task->id }}" data-quick-val="{{ today()->next(\Illuminate\Support\Carbon::MONDAY)->toDateString() }}">Next Mon</button>
                        </div>
                    </div>
                @endif
            </div>

            <div>
                <div class="cp-d-section"><i class="fas fa-tower-broadcast"></i> Channels · {{ $item->postedCount }}/{{ max($item->platformCount, 1) }} posted</div>
                <div class="cp-channels">
                    @forelse($item->channels as $channel)
                        @php
                            $pm = $platformMeta($channel['platform']);
                            $cst = $states[$channel['state']];
                        @endphp
                        <div class="cp-ch-row">
                            <span class="cp-ch-icon" style="--ch: {{ $pm->color }}"><i class="{{ $pm->icon }}"></i></span>
                            <span class="cp-ch-name">
                                {{ $pm->label }}
                                <small>
                                    @if($channel['state'] === 'done')
                                        Posted{{ $channel['postedAt'] ? ' ' . $channel['postedAt']->format('M d') : '' }}
                                        @if($channel['postUrl'])
                                            · <a href="{{ $channel['postUrl'] }}" target="_blank" rel="noopener">View post <i class="fas fa-arrow-up-right-from-square"></i></a>
                                        @endif
                                    @else
                                        Not posted yet
                                    @endif
                                </small>
                            </span>
                            <span class="cp-pill tone-{{ $cst['tone'] }}"><i class="fas {{ $cst['icon'] }}"></i> {{ $cst['label'] }}</span>
                        </div>
                    @empty
                        <div class="cp-muted">No platform set on this task. It's tracked as design work and counts as done once completed.</div>
                    @endforelse
                </div>
            </div>

            <div class="cp-facts">
                <div class="cp-fact"><div class="cp-fact-label">Go-live</div><div class="cp-fact-val" id="factPostDate-{{ $task->id }}">{{ $item->planDate->format('D, M d') }}</div></div>
                <div class="cp-fact"><div class="cp-fact-label">Deadline</div><div class="cp-fact-val {{ $deadlineLate ? 'is-late' : '' }}">{{ $task->deadline ? \Illuminate\Support\Carbon::parse($task->deadline)->format('M d') : '—' }}</div></div>
                <div class="cp-fact"><div class="cp-fact-label">Design due</div><div class="cp-fact-val">{{ $task->design_deadline ? \Illuminate\Support\Carbon::parse($task->design_deadline)->format('M d') : '—' }}</div></div>
                <div class="cp-fact"><div class="cp-fact-label">Designer</div><div class="cp-fact-val">{{ $task->assignee->name ?? 'Unassigned' }}</div></div>
                <div class="cp-fact"><div class="cp-fact-label">Created by</div><div class="cp-fact-val">{{ $task->creator->name ?? '—' }}</div></div>
                <div class="cp-fact"><div class="cp-fact-label">Task status</div><div class="cp-fact-val" style="text-transform:capitalize">{{ str_replace('_', ' ', $task->status) }}</div></div>
            </div>

            {{-- Deliverable Media Gallery --}}
            <div>
                <div class="cp-d-section">
                    <i class="fas fa-photo-film"></i> Deliverable Creatives & Media ({{ $task->media->count() }})
                </div>
                @if($task->media->isNotEmpty())
                    <div class="cp-media-gallery">
                        @foreach($task->media as $m)
                            @php
                                $ext = strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION));
                                $isVideo = in_array($ext, ['mp4','mov','webm']);
                                $mediaUrl = asset('storage/' . $m->file_path);
                            @endphp
                            <div class="cp-media-tile" data-lightbox-src="{{ $mediaUrl }}" data-lightbox-type="{{ $isVideo ? 'video' : 'image' }}">
                                @if($isVideo)
                                    <video src="{{ $mediaUrl }}#t=0.5" preload="metadata"></video>
                                    <span class="cp-media-badge"><i class="fas fa-video"></i> {{ strtoupper($ext) }}</span>
                                @else
                                    <img src="{{ $mediaUrl }}" alt="{{ $m->file_name }}">
                                    <span class="cp-media-badge">{{ strtoupper($ext) }}</span>
                                @endif
                                <div class="cp-media-actions">
                                    <button type="button" class="cp-media-btn" title="Zoom full screen" data-lightbox-trigger><i class="fas fa-magnifying-glass-plus"></i></button>
                                    <a href="{{ $mediaUrl }}" download class="cp-media-btn" title="Download file"><i class="fas fa-download"></i></a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="cp-muted" style="padding:10px 12px;background:var(--card2,#f8fafc);border-radius:10px;border:1px dashed var(--border,#cbd5e1)">
                        <i class="fas fa-image" style="margin-right:6px"></i> Creative deliverables pending upload by {{ $task->assignee->name ?? 'assigned designer' }}.
                    </div>
                @endif
            </div>

            {{-- Copywriting Suite --}}
            @if($task->headline_copy || $task->caption || $task->hashtags)
                <div>
                    <div class="cp-d-section"><i class="fas fa-bullhorn"></i> Marketing Copy & Captions</div>
                    <div style="display:flex;flex-direction:column;gap:10px">
                        @if($task->headline_copy)
                            <div class="cp-copy-box">
                                <div class="cp-copy-box-head">
                                    <span class="cp-copy-box-title"><i class="fas fa-heading"></i> Headline Hook</span>
                                    <button type="button" class="cp-copy-act-btn" data-copy-text="{{ $task->headline_copy }}" title="Copy headline hook"><i class="fas fa-copy"></i> Copy</button>
                                </div>
                                <p class="cp-copy-text" style="font-weight:700">{{ $task->headline_copy }}</p>
                            </div>
                        @endif

                        @if($task->caption)
                            <div class="cp-copy-box">
                                <div class="cp-copy-box-head">
                                    <span class="cp-copy-box-title"><i class="fas fa-quote-left"></i> Caption</span>
                                    <button type="button" class="cp-copy-act-btn" data-copy-text="{{ $task->caption }}" title="Copy caption"><i class="fas fa-copy"></i> Copy</button>
                                </div>
                                <p class="cp-copy-text">{{ $task->caption }}</p>
                            </div>
                        @endif

                        @if($task->hashtags)
                            <div class="cp-copy-box">
                                <div class="cp-copy-box-head">
                                    <span class="cp-copy-box-title"><i class="fas fa-hashtag"></i> Hashtags</span>
                                    <button type="button" class="cp-copy-act-btn" data-copy-text="{{ $task->hashtags }}" title="Copy hashtags"><i class="fas fa-copy"></i> Copy</button>
                                </div>
                                <p class="cp-copy-text" style="color:var(--cp-brand);font-weight:600">{{ $task->hashtags }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if($task->brief)
                <div>
                    <div class="cp-d-section"><i class="fas fa-file-lines"></i> Brief & Strategy Notes</div>
                    <p class="cp-text">{{ \Illuminate\Support\Str::limit($task->brief, 600) }}</p>
                </div>
            @endif
        </div>
        <div class="cp-d-foot">
            <span class="cp-muted">Updated {{ $task->updated_at?->diffForHumans() }}</span>
            <a href="{{ $taskUrl($task) }}" class="cp-btn cp-btn-primary"><i class="fas {{ $isAdmin ? 'fa-arrow-up-right-from-square' : 'fa-route' }}"></i> {{ $isAdmin ? 'Open task' : 'Open in tracking' }}</a>
        </div>
    </template>
@endforeach

<div class="cp-overlay" id="cpOverlay"></div>
<aside class="cp-drawer" id="cpDrawer" role="dialog" aria-modal="true" aria-label="Post details" aria-hidden="true">
    <button type="button" class="cp-drawer-close" id="cpDrawerClose" aria-label="Close details"><i class="fas fa-xmark"></i></button>
    <div class="cp-drawer-content" id="cpDrawerContent"></div>
</aside>

{{-- ── Lightbox Zoom Modal ── --}}
<div class="cp-lightbox" id="cpLightbox">
    <div class="cp-lightbox-content">
        <button type="button" class="cp-lightbox-close" id="cpLightboxClose" aria-label="Close lightbox"><i class="fas fa-xmark"></i></button>
        <div id="cpLightboxTarget"></div>
    </div>
</div>

{{-- ── Quick Schedule Modal ── --}}
<div class="cp-modal-overlay" id="cpQuickModal">
    <div class="cp-quick-dialog" role="dialog" aria-modal="true" aria-labelledby="cpQuickModalTitle">
        <div class="cp-quick-head">
            <h3 class="cp-quick-title" id="cpQuickModalTitle"><i class="fas fa-calendar-plus"></i> Quick Schedule Content</h3>
            <button type="button" class="cp-drawer-close" style="position:static" id="cpQuickModalClose" aria-label="Close modal"><i class="fas fa-xmark"></i></button>
        </div>
        <form id="cpQuickScheduleForm" action="{{ $storeTaskUrl }}" method="POST">
            @csrf
            <input type="hidden" name="status" value="todo">
            <input type="hidden" name="priority" value="normal">
            <div class="cp-quick-body">
                <div class="cp-form-row">
                    <div class="cp-form-group">
                        <label for="qsDate">Go-Live Date *</label>
                        <input type="date" class="cp-field" id="qsDate" name="post_date" required min="{{ today()->toDateString() }}">
                    </div>
                    <div class="cp-form-group">
                        <label for="qsClient">Client *</label>
                        <select class="cp-field" id="qsClient" name="client_id" required>
                            <option value="">Select client...</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" @selected($clientId === $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="cp-form-row">
                    <div class="cp-form-group">
                        <label for="qsPlatform">Primary Platform *</label>
                        <select class="cp-field" id="qsPlatform" name="platform[]" required>
                            <option value="instagram">Instagram</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="facebook">Facebook</option>
                            <option value="twitter">X / Twitter</option>
                            <option value="youtube">YouTube</option>
                            <option value="pinterest">Pinterest</option>
                        </select>
                    </div>
                    <div class="cp-form-group">
                        <label for="qsType">Format *</label>
                        <select class="cp-field" id="qsType" name="type" required>
                            <option value="post">Post / Graphic</option>
                            <option value="reel">Reel / Short</option>
                            <option value="story">Story</option>
                            <option value="carousel">Carousel</option>
                            <option value="video">Long Video</option>
                        </select>
                    </div>
                </div>
                <div class="cp-form-group">
                    <label for="qsTitle">Post Title / Content Hook *</label>
                    <input type="text" class="cp-field" id="qsTitle" name="title" placeholder="e.g. Festival Special Offer Announcement" required>
                </div>
                <div class="cp-form-row">
                    <div class="cp-form-group">
                        <label for="qsAssignee">Designer Assignee</label>
                        <select class="cp-field" id="qsAssignee" name="assigned_to">
                            <option value="">Unassigned (Open pool)</option>
                            @foreach($designers as $des)
                                <option value="{{ $des->id }}">{{ $des->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="cp-form-group">
                        <label for="qsPriority">Priority</label>
                        <select class="cp-field" id="qsPriority" name="priority">
                            <option value="normal">Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div class="cp-form-group">
                    <label for="qsBrief">Brief / Instructions *</label>
                    <textarea class="cp-field" id="qsBrief" name="brief" placeholder="Key messaging, design direction, brand elements..." required>Create engaging social media creative aligned with brand guidelines.</textarea>
                </div>
            </div>
            <div class="cp-quick-foot">
                <button type="button" class="cp-btn" id="cpQuickCancelBtn">Cancel</button>
                <button type="submit" class="cp-btn cp-btn-primary" id="cpQuickSubmitBtn"><i class="fas fa-check"></i> Schedule Post</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Toast Notification ── --}}
<div class="cp-toast" id="cpToast">
    <span id="cpToastMsg"></span>
    <button type="button" class="cp-toast-undo" id="cpToastUndo" style="display:none">Undo</button>
</div>

<script>
(function () {
    const items = Array.from(document.querySelectorAll('.cp-card, .cp-mitem, .cp-feed-card'));
    const search = document.getElementById('cpSearch');
    const stateButtons = Array.from(document.querySelectorAll('[data-state-filter]'));
    let activeState = null;

    function applyFilters() {
        const q = (search.value || '').trim().toLowerCase();
        items.forEach(el => {
            const okState = !activeState || el.dataset.state === activeState;
            const okSearch = !q || (el.dataset.search || el.textContent || '').toLowerCase().includes(q);
            el.hidden = !(okState && okSearch);
        });
        stateButtons.forEach(btn => {
            const on = btn.dataset.stateFilter === activeState;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    }

    stateButtons.forEach(btn => btn.addEventListener('click', () => {
        activeState = activeState === btn.dataset.stateFilter ? null : btn.dataset.stateFilter;
        applyFilters();
    }));
    search?.addEventListener('input', applyFilters);

    // ── Drawer ──
    const drawer = document.getElementById('cpDrawer');
    const overlay = document.getElementById('cpOverlay');
    const content = document.getElementById('cpDrawerContent');
    let lastFocus = null;

    function showInDrawer(template) {
        if (!drawer.classList.contains('is-open')) lastFocus = document.activeElement;
        content.replaceChildren(template.content.cloneNode(true));
        content.querySelector('.cp-d-body')?.scrollTo(0, 0);
        drawer.classList.add('is-open');
        overlay.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        document.getElementById('cpDrawerClose').focus();
    }

    function openDay(date) {
        const template = document.getElementById('cp-day-' + date);
        if (template) showInDrawer(template);
    }

    function openTask(id, fromDay) {
        const template = document.getElementById('cp-task-' + id);
        if (!template) return;
        showInDrawer(template);
        if (fromDay) {
            const back = document.createElement('button');
            back.type = 'button';
            back.className = 'cp-back';
            back.innerHTML = '<i class="fas fa-arrow-left"></i> ';
            back.append('Back to ' + new Date(fromDay + 'T00:00:00').toLocaleDateString(undefined, { month: 'short', day: 'numeric' }));
            back.addEventListener('click', () => openDay(fromDay));
            content.querySelector('.cp-d-head')?.prepend(back);
        }
    }

    function closeTask() {
        if (!drawer.classList.contains('is-open')) return;
        drawer.querySelectorAll('.cp-reschedule-controls').forEach(c => c.style.display = 'none');
        drawer.querySelectorAll('.cp-btn-edit-date').forEach(b => {
            b.classList.remove('is-active');
            const lbl = b.querySelector('.cp-toggle-label');
            if (lbl) lbl.textContent = 'Change Date';
        });
        drawer.classList.remove('is-open');
        overlay.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        lastFocus?.focus?.();
    }

    document.getElementById('cpDrawerClose')?.addEventListener('click', closeTask);
    overlay?.addEventListener('click', closeTask);

    // ── Global Toast ──
    const toast = document.getElementById('cpToast');
    const toastMsg = document.getElementById('cpToastMsg');
    const toastUndo = document.getElementById('cpToastUndo');
    let toastTimer = null;

    function showToast(msg, onUndo = null) {
        clearTimeout(toastTimer);
        toastMsg.textContent = msg;
        if (onUndo) {
            toastUndo.style.display = 'inline-block';
            toastUndo.onclick = () => {
                hideToast();
                onUndo();
            };
        } else {
            toastUndo.style.display = 'none';
        }
        toast.classList.add('is-show');
        toastTimer = setTimeout(hideToast, onUndo ? 8000 : 4000);
    }

    function hideToast() {
        toast.classList.remove('is-show');
    }

    // ── Quick Schedule Modal ──
    const quickModal = document.getElementById('cpQuickModal');
    const quickForm = document.getElementById('cpQuickScheduleForm');
    const quickClose = document.getElementById('cpQuickModalClose');
    const quickCancel = document.getElementById('cpQuickCancelBtn');
    const quickSubmit = document.getElementById('cpQuickSubmitBtn');

    function openQuickModal(date, client = null, platform = null, festName = null) {
        if (date) document.getElementById('qsDate').value = date;
        if (client) document.getElementById('qsClient').value = client;
        if (platform) document.getElementById('qsPlatform').value = platform;
        const titleInput = document.getElementById('qsTitle');
        if (festName) {
            titleInput.value = `${festName} Special Campaign`;
        } else {
            titleInput.value = '';
        }
        quickModal.classList.add('is-open');
        setTimeout(() => titleInput.focus(), 100);
    }

    function closeQuickModal() {
        quickModal.classList.remove('is-open');
    }

    quickClose?.addEventListener('click', closeQuickModal);
    quickCancel?.addEventListener('click', closeQuickModal);
    quickModal?.addEventListener('click', (e) => {
        if (e.target === quickModal) closeQuickModal();
    });

    quickForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const originalText = quickSubmit.innerHTML;
        quickSubmit.disabled = true;
        quickSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scheduling...';

        try {
            const formData = new FormData(quickForm);
            const response = await fetch(quickForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: formData,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const errorMsg = data.message || Object.values(data.errors || {})[0]?.[0] || 'Could not schedule post.';
                throw new Error(errorMsg);
            }
            closeQuickModal();
            showToast('Post scheduled successfully! Reloading...');
            setTimeout(() => window.location.reload(), 600);
        } catch (err) {
            showToast(err.message);
            quickSubmit.disabled = false;
            quickSubmit.innerHTML = originalText;
        }
    });

    // ── Lightbox Zoom Modal ──
    const lightbox = document.getElementById('cpLightbox');
    const lightboxTarget = document.getElementById('cpLightboxTarget');
    const lightboxClose = document.getElementById('cpLightboxClose');

    function openLightbox(src, type = 'image') {
        if (!src) return;
        lightboxTarget.innerHTML = type === 'video'
            ? `<video src="${src}" controls autoplay style="max-width:100%;max-height:85vh;border-radius:12px"></video>`
            : `<img src="${src}" alt="Creative Preview" style="max-width:100%;max-height:85vh;border-radius:12px;object-fit:contain">`;
        lightbox.classList.add('is-open');
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        lightboxTarget.innerHTML = '';
    }

    lightboxClose?.addEventListener('click', closeLightbox);
    lightbox?.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    // ── Presentation Mode ──
    const calendarWrap = document.getElementById('cpCalendarWrap');
    const presentToggle = document.getElementById('cpPresentationToggle');
    const presentLabel = document.getElementById('cpPresentLabel');
    const exitPresentBtn = document.getElementById('cpExitPresentBtn');

    function togglePresentationMode() {
        const isNowPresenting = calendarWrap.classList.toggle('is-presenting');
        if (presentLabel) presentLabel.textContent = isNowPresenting ? 'Exit' : 'Present';
    }

    presentToggle?.addEventListener('click', togglePresentationMode);
    exitPresentBtn?.addEventListener('click', togglePresentationMode);

    // ── Delegated Click Handler ──
    document.addEventListener('click', (e) => {
        // Toggle Reschedule Controls
        const toggleReschedBtn = e.target.closest('[data-toggle-reschedule]');
        if (toggleReschedBtn) {
            e.preventDefault();
            e.stopPropagation();
            const taskId = toggleReschedBtn.dataset.toggleReschedule;
            const controls = document.getElementById('rescheduleControls-' + taskId);
            if (controls) {
                const isOpen = controls.style.display !== 'none';
                controls.style.display = isOpen ? 'none' : 'flex';
                toggleReschedBtn.classList.toggle('is-active', !isOpen);
                const lbl = toggleReschedBtn.querySelector('.cp-toggle-label');
                if (lbl) lbl.textContent = isOpen ? 'Change Date' : 'Close';
            }
            return;
        }

        // Cancel Reschedule Controls
        const cancelReschedBtn = e.target.closest('[data-cancel-reschedule]');
        if (cancelReschedBtn) {
            e.preventDefault();
            e.stopPropagation();
            const taskId = cancelReschedBtn.dataset.cancelReschedule;
            const controls = document.getElementById('rescheduleControls-' + taskId);
            if (controls) controls.style.display = 'none';
            const toggleBtn = document.querySelector(`[data-toggle-reschedule="${taskId}"]`);
            if (toggleBtn) {
                toggleBtn.classList.remove('is-active');
                const lbl = toggleBtn.querySelector('.cp-toggle-label');
                if (lbl) lbl.textContent = 'Change Date';
            }
            return;
        }

        // Quick Date Shift Pill inside drawer
        const qdBtn = e.target.closest('[data-quick-date]');
        if (qdBtn) {
            e.preventDefault();
            e.stopPropagation();
            const taskId = qdBtn.dataset.quickDate;
            const val = qdBtn.dataset.quickVal;
            const input = document.getElementById('postDateInput-' + taskId);
            if (input) {
                input.value = val;
                qdBtn.closest('.cp-reschedule-quick-pills')?.querySelectorAll('.cp-pill-btn')?.forEach(b => b.classList.remove('is-selected'));
                qdBtn.classList.add('is-selected');
            }
            return;
        }

        // Apply Changed Post Date from drawer
        const applyBtn = e.target.closest('[data-apply-post-date]');
        if (applyBtn) {
            e.preventDefault();
            e.stopPropagation();
            const taskId = applyBtn.dataset.applyPostDate;
            const input = document.getElementById('postDateInput-' + taskId);
            if (!input || !input.value) return;
            const newDate = input.value;
            const originalHtml = applyBtn.innerHTML;
            applyBtn.disabled = true;
            applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            fetch(`/api/tasks/${taskId}/reschedule`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ date_type: 'post_date', new_date: newDate }),
            })
            .then(res => res.json().then(data => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) throw new Error(data.message || 'Could not change posting date.');
                const pretty = new Date(newDate + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                showToast(`Posting date updated to ${pretty}! Updating calendar...`);
                const disp = document.getElementById('rescheduleDisplay-' + taskId);
                if (disp) {
                    const textSpan = disp.querySelector('.cp-reschedule-text');
                    if (textSpan) textSpan.textContent = pretty;
                }
                const factVal = document.getElementById('factPostDate-' + taskId);
                if (factVal) factVal.textContent = new Date(newDate + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
                applyBtn.innerHTML = '<i class="fas fa-check"></i> Saved!';
                setTimeout(() => window.location.reload(), 700);
            })
            .catch(err => {
                showToast(err.message);
                applyBtn.disabled = false;
                applyBtn.innerHTML = originalHtml;
            });
            return;
        }

        // Quick Create Trigger
        const quickTrigger = e.target.closest('[data-quick-create]');
        if (quickTrigger) {
            e.preventDefault();
            e.stopPropagation();
            openQuickModal(
                quickTrigger.dataset.quickCreate,
                quickTrigger.dataset.client,
                quickTrigger.dataset.platform,
                quickTrigger.dataset.fest
            );
            return;
        }

        // Copy Text Trigger
        const copyTrigger = e.target.closest('[data-copy-text]');
        if (copyTrigger) {
            e.preventDefault();
            e.stopPropagation();
            const text = copyTrigger.dataset.copyText;
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = copyTrigger.innerHTML;
                copyTrigger.innerHTML = '<i class="fas fa-check" style="color:#10b981"></i> Copied!';
                setTimeout(() => { copyTrigger.innerHTML = originalHtml; }, 2000);
            });
            return;
        }

        // Lightbox Trigger
        const lbTrigger = e.target.closest('[data-lightbox-src]');
        if (lbTrigger) {
            e.preventDefault();
            e.stopPropagation();
            openLightbox(lbTrigger.dataset.lightboxSrc, lbTrigger.dataset.lightboxType || 'image');
            return;
        }

        // Open Task Trigger
        const taskTrigger = e.target.closest('[data-open-task]');
        if (taskTrigger) {
            e.preventDefault();
            openTask(taskTrigger.dataset.openTask, taskTrigger.dataset.fromDay);
            return;
        }

        // Month view: clicking a day opens day view
        const day = e.target.closest('[data-open-day]');
        if (day) openDay(day.dataset.openDay);
    });

    document.querySelectorAll('[data-open-day]').forEach(day => day.addEventListener('keydown', (e) => {
        if ((e.key === 'Enter' || e.key === ' ') && e.target === day) { e.preventDefault(); openDay(day.dataset.openDay); }
    }));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (lightbox.classList.contains('is-open')) closeLightbox();
            else if (quickModal.classList.contains('is-open')) closeQuickModal();
            else if (drawer.classList.contains('is-open')) closeTask();
        }
        const typing = /^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement?.tagName);
        if (e.key === '/' && !typing) { e.preventDefault(); search?.focus(); }
    });

    // ── Drag & Drop Reschedule with Undo Toast ──
    let dragged = null;
    document.querySelectorAll('[data-move-task]').forEach(el => {
        el.addEventListener('dragstart', (e) => {
            dragged = el;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', el.dataset.moveTask);
            el.classList.add('cp-dragging');
            document.body.classList.add('cp-is-dragging');
        });
        el.addEventListener('dragend', () => {
            el.classList.remove('cp-dragging');
            document.body.classList.remove('cp-is-dragging');
            document.querySelectorAll('.is-drop-over').forEach(t => t.classList.remove('is-drop-over'));
            dragged = null;
        });
    });

    document.querySelectorAll('[data-drop-date]').forEach(target => {
        target.addEventListener('dragover', (e) => {
            if (!dragged || target.dataset.dropDate === dragged.dataset.moveFrom) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            target.classList.add('is-drop-over');
        });
        target.addEventListener('dragleave', (e) => {
            if (!target.contains(e.relatedTarget)) target.classList.remove('is-drop-over');
        });
        target.addEventListener('drop', async (e) => {
            e.preventDefault();
            target.classList.remove('is-drop-over');
            if (!dragged) return;
            const card = dragged;
            const taskId = card.dataset.moveTask;
            const field = card.dataset.moveField;
            const oldDate = card.dataset.moveFrom;
            const newDate = target.dataset.dropDate;
            const title = card.getAttribute('title') || card.textContent.trim();
            const pretty = new Date(newDate + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });

            async function performReschedule(targetDate) {
                const response = await fetch(`/api/tasks/${taskId}/reschedule`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ date_type: field, new_date: targetDate }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Could not reschedule post.');
                return data;
            }

            try {
                await performReschedule(newDate);
                showToast(`Moved "${title.split(' · ')[0]}" to ${pretty}`, async () => {
                    try {
                        await performReschedule(oldDate);
                        showToast(`Reverted move back to original date.`);
                        setTimeout(() => window.location.reload(), 600);
                    } catch (err) {
                        showToast(`Undo failed: ${err.message}`);
                    }
                });
                setTimeout(() => window.location.reload(), 1400);
            } catch (err) {
                showToast(err.message);
            }
        });
    });

    // ── Feed Studio Controller ──
    function initFeedStudio() {
        const feedView = document.getElementById('cpFeedView');
        if (!feedView) return;

        const layoutTabs = document.querySelectorAll('#cpFeedLayoutTabs [data-feed-mode]');
        const limitTabs = document.querySelectorAll('#cpFeedLimitTabs [data-feed-limit]');
        const phoneWrapper = document.getElementById('cpPhoneWrapper');
        const grid9Wrapper = document.getElementById('cpGrid9Wrapper');
        const studioGridWrapper = document.getElementById('cpStudioGridWrapper');
        const studioGridContainer = document.getElementById('cpStudioGridContainer');
        const grid9Container = document.getElementById('cpGrid9Container');
        const phoneGridContainer = document.getElementById('cpPhoneGridContainer');

        const allTiles = Array.from(phoneGridContainer?.querySelectorAll('.cp-feed-tile') || []);
        if (allTiles.length === 0) return;

        let currentMode = 'phone';
        let currentLimit = '9';
        let current9Page = 1;

        // Tile Inspector Elements
        const inspTitle = document.getElementById('inspTitle');
        const inspStatePill = document.getElementById('inspStatePill');
        const inspFormatPill = document.getElementById('inspFormatPill');
        const inspDate = document.getElementById('inspDate');
        const inspClient = document.getElementById('inspClient');
        const inspAssignee = document.getElementById('inspAssignee');
        const inspMediaImg = document.getElementById('inspMediaImg');
        const inspMediaEmpty = document.getElementById('inspMediaEmpty');
        const inspZoomBtn = document.getElementById('inspZoomBtn');
        const inspHeadlineText = document.getElementById('inspHeadlineText');
        const inspCaptionText = document.getElementById('inspCaptionText');
        const inspHashtagsText = document.getElementById('inspHashtagsText');
        const inspOpenDrawerBtn = document.getElementById('inspOpenDrawerBtn');
        const inspOpenFullBtn = document.getElementById('inspOpenFullBtn');
        const inspEditLink = document.getElementById('inspEditLink');

        function getTypeIcon(type) {
            const icons = {
                reel: 'fa-film', video: 'fa-play-circle', carousel: 'fa-layer-group',
                story: 'fa-circle-notch', graphic: 'fa-palette', default: 'fa-image'
            };
            return icons[type] || icons.default;
        }

        function capitalize(s) {
            return s ? s.charAt(0).toUpperCase() + s.slice(1) : 'Post';
        }

        function selectTile(tile) {
            if (!tile) return;
            const taskId = tile.dataset.taskId;
            const title = tile.dataset.title;
            const headline = tile.dataset.headline;
            const caption = tile.dataset.caption;
            const hashtags = tile.dataset.hashtags;
            const thumb = tile.dataset.thumb;
            const type = tile.dataset.type;
            const stateLabel = tile.dataset.stateLabel;
            const stateTone = tile.dataset.stateTone;
            const date = tile.dataset.date;
            const client = tile.dataset.client;
            const assignee = tile.dataset.assignee;

            // Highlight in all grids
            document.querySelectorAll('.cp-feed-tile').forEach(t => {
                t.classList.toggle('is-selected', t.dataset.taskId === taskId);
            });

            // Update live inspector
            if (inspTitle) inspTitle.textContent = title;
            if (inspStatePill) {
                inspStatePill.textContent = stateLabel;
                inspStatePill.className = `cp-pill tone-${stateTone}`;
            }
            if (inspFormatPill) {
                inspFormatPill.innerHTML = `<i class="fas ${getTypeIcon(type)}"></i> ${capitalize(type)}`;
            }
            if (inspDate) inspDate.textContent = date;
            if (inspClient) inspClient.textContent = client;
            if (inspAssignee) inspAssignee.textContent = assignee;

            if (thumb) {
                if (inspMediaImg) {
                    inspMediaImg.src = thumb;
                    inspMediaImg.style.display = 'block';
                }
                if (inspMediaEmpty) inspMediaEmpty.style.display = 'none';
                if (inspZoomBtn) {
                    inspZoomBtn.style.display = 'inline-flex';
                    inspZoomBtn.onclick = () => openLightbox(thumb, 'image');
                }
            } else {
                if (inspMediaImg) inspMediaImg.style.display = 'none';
                if (inspMediaEmpty) inspMediaEmpty.style.display = 'flex';
                if (inspZoomBtn) inspZoomBtn.style.display = 'none';
            }

            if (inspHeadlineText) inspHeadlineText.textContent = headline || 'No headline hook added.';
            if (inspCaptionText) inspCaptionText.textContent = caption || 'No caption written yet.';
            if (inspHashtagsText) inspHashtagsText.textContent = hashtags || 'No hashtags added.';

            const onDrawerClick = () => openTask(taskId);
            if (inspOpenDrawerBtn) inspOpenDrawerBtn.onclick = onDrawerClick;
            if (inspOpenFullBtn) inspOpenFullBtn.onclick = onDrawerClick;
            if (inspEditLink) inspEditLink.href = `/admin/tasks/${taskId}/edit`;
        }

        // Copy buttons
        function copyWithFeedback(btnId, text) {
            const btn = document.getElementById(btnId);
            if (!btn || !text) return;
            navigator.clipboard.writeText(text).then(() => {
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check" style="color:#22c55e"></i> Copied!';
                setTimeout(() => btn.innerHTML = orig, 1800);
            }).catch(() => {
                showToast('Unable to copy text to clipboard.');
            });
        }

        document.getElementById('inspCopyHookBtn')?.addEventListener('click', () => {
            const text = inspHeadlineText?.textContent?.trim();
            if (text && text !== 'No headline hook added.') copyWithFeedback('inspCopyHookBtn', text);
        });
        document.getElementById('inspCopyCaptionBtn')?.addEventListener('click', () => {
            const text = inspCaptionText?.textContent?.trim();
            if (text && text !== 'No caption written yet.') copyWithFeedback('inspCopyCaptionBtn', text);
        });
        document.getElementById('inspCopyTagsBtn')?.addEventListener('click', () => {
            const text = inspHashtagsText?.textContent?.trim();
            if (text && text !== 'No hashtags added.') copyWithFeedback('inspCopyTagsBtn', text);
        });

        // Click tile delegated listener
        feedView.addEventListener('click', (e) => {
            const tile = e.target.closest('.cp-feed-tile');
            if (tile) {
                selectTile(tile);
            }
        });

        // 9-Grid Pager
        function renderGrid9() {
            if (!grid9Container) return;
            const itemsPerPage = 9;
            const totalPages = Math.ceil(allTiles.length / itemsPerPage) || 1;
            if (current9Page > totalPages) current9Page = totalPages;
            if (current9Page < 1) current9Page = 1;

            const start = (current9Page - 1) * itemsPerPage;
            const pagedTiles = allTiles.slice(start, start + itemsPerPage);

            grid9Container.innerHTML = '';
            pagedTiles.forEach(t => {
                grid9Container.appendChild(t.cloneNode(true));
            });

            const indicator = document.getElementById('cpGrid9PageIndicator');
            if (indicator) indicator.textContent = `Page ${current9Page} of ${totalPages}`;

            const prevBtn = document.getElementById('cpGrid9Prev');
            const nextBtn = document.getElementById('cpGrid9Next');
            if (prevBtn) prevBtn.disabled = current9Page <= 1;
            if (nextBtn) nextBtn.disabled = current9Page >= totalPages;
        }

        document.getElementById('cpGrid9Prev')?.addEventListener('click', () => {
            if (current9Page > 1) {
                current9Page--;
                renderGrid9();
            }
        });
        document.getElementById('cpGrid9Next')?.addEventListener('click', () => {
            const totalPages = Math.ceil(allTiles.length / 9) || 1;
            if (current9Page < totalPages) {
                current9Page++;
                renderGrid9();
            }
        });

        // Studio Grid Renderer (Compact / Standard)
        function renderStudioGrid(mode) {
            if (!studioGridContainer) return;
            studioGridContainer.className = `cp-studio-grid-scroll is-${mode}`;
            studioGridContainer.innerHTML = '';

            const limit = currentLimit === 'all' ? allTiles.length : parseInt(currentLimit, 10);
            const slice = allTiles.slice(0, limit);

            slice.forEach(t => {
                studioGridContainer.appendChild(t.cloneNode(true));
            });
        }

        // Apply Layout Mode
        function setMode(mode) {
            currentMode = mode;
            layoutTabs.forEach(b => b.classList.toggle('is-active', b.dataset.feedMode === mode));

            if (mode === 'phone') {
                if (phoneWrapper) phoneWrapper.style.display = 'flex';
                if (grid9Wrapper) grid9Wrapper.style.display = 'none';
                if (studioGridWrapper) studioGridWrapper.style.display = 'none';
            } else if (mode === 'grid9') {
                if (phoneWrapper) phoneWrapper.style.display = 'none';
                if (grid9Wrapper) grid9Wrapper.style.display = 'block';
                if (studioGridWrapper) studioGridWrapper.style.display = 'none';
                renderGrid9();
            } else if (mode === 'compact' || mode === 'grid') {
                if (phoneWrapper) phoneWrapper.style.display = 'none';
                if (grid9Wrapper) grid9Wrapper.style.display = 'none';
                if (studioGridWrapper) studioGridWrapper.style.display = 'block';
                renderStudioGrid(mode === 'compact' ? 'compact' : 'standard');
            }
        }

        layoutTabs.forEach(btn => {
            btn.addEventListener('click', () => setMode(btn.dataset.feedMode));
        });

        // Apply Limit Filter
        limitTabs.forEach(btn => {
            btn.addEventListener('click', () => {
                currentLimit = btn.dataset.feedLimit;
                limitTabs.forEach(b => b.classList.toggle('is-active', b === btn));
                if (currentMode === 'compact' || currentMode === 'grid') {
                    renderStudioGrid(currentMode === 'compact' ? 'compact' : 'standard');
                } else if (currentMode === 'phone') {
                    const limit = currentLimit === 'all' ? allTiles.length : parseInt(currentLimit, 10);
                    allTiles.forEach((tile, idx) => {
                        tile.style.display = idx < limit ? '' : 'none';
                    });
                }
            });
        });

        // Set default limit in phone grid
        const defaultLimit = parseInt(currentLimit, 10);
        allTiles.forEach((tile, idx) => {
            tile.style.display = idx < defaultLimit ? '' : 'none';
        });

        // Auto select first tile on load
        if (allTiles[0]) {
            selectTile(allTiles[0]);
        }
    }

    initFeedStudio();

    // Deep link direct open (?task=ID or ?day=YYYY-MM-DD)
    const params = new URL(window.location.href).searchParams;
    if (params.get('task')) openTask(params.get('task'));
    else if (params.get('day')) openDay(params.get('day'));
})();
</script>
@endsection
