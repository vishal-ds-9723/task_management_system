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
    // Matches the reschedule API: admins can move anything, others only tasks they created
    $canMove = fn ($task) => $isAdmin || $task->created_by === auth()->id();

    // Build calendar URLs that keep the current filters
    $url = function (array $overrides = []) use ($view, $anchor, $clientId, $mine, $calendarRoute) {
        $params = array_merge([
            'view' => $view,
            'date' => $anchor->toDateString(),
            'client' => $clientId,
            'mine' => $mine ? 1 : null,
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

/* ── Drawer ── */
.cp-overlay { position: fixed; inset: 0; z-index: 9995; background: rgba(15,23,42,0.45); backdrop-filter: blur(3px); opacity: 0; visibility: hidden; transition: opacity .25s, visibility .25s; }
.cp-overlay.is-open { opacity: 1; visibility: visible; }
.cp-drawer {
    position: fixed; top: 0; right: 0; bottom: 0; z-index: 10000; width: min(520px, 100vw); display: flex; flex-direction: column;
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

<div class="cp-wrap">

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
                <a href="{{ $url(['view' => 'week']) }}" class="{{ $isWeek ? 'is-active' : '' }}" role="tab"><i class="fas fa-table-cells"></i> Week</a>
                <a href="{{ $url(['view' => 'month']) }}" class="{{ !$isWeek ? 'is-active' : '' }}" role="tab"><i class="fas fa-calendar-days"></i> Month</a>
            </div>
            <div class="cp-glass">
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

        @if($isWeek)
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
                            @endphp
                            <div class="cp-col-head {{ $day->isToday() ? 'is-today' : '' }} {{ $day->lt(today()) ? 'is-past' : '' }}">
                                <div class="cp-day-name">{{ $day->format('D') }}</div>
                                <div class="cp-day-num">{{ $day->format('d') }}</div>
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
                                    </div>
                                @endforeach
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endif
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
                        @endphp
                        <div @if($dayTasks->isNotEmpty()) data-open-day="{{ $day->toDateString() }}" tabindex="0" role="button" title="See all {{ $dayTasks->count() }} posts on {{ $day->format('M d') }}" @endif @unless($day->lt(today())) data-drop-date="{{ $day->toDateString() }}" @endunless
                           class="cp-mday {{ $inMonth ? '' : 'is-out' }} {{ $day->isToday() ? 'is-today' : '' }} {{ $dayTasks->isEmpty() ? 'is-empty' : '' }}">
                            <span class="cp-mday-top">
                                <span class="cp-mday-num">{{ $day->format('j') }}</span>
                                @if($dayCards->isNotEmpty())
                                    <span class="cp-mday-ratio {{ $dayDone === $dayCards->count() ? 'is-complete' : '' }}">{{ $dayDone }}/{{ $dayCards->count() }} posted</span>
                                @endif
                            </span>
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
                <div class="cp-fact"><div class="cp-fact-label">Go-live</div><div class="cp-fact-val">{{ $item->planDate->format('D, M d') }}</div></div>
                <div class="cp-fact"><div class="cp-fact-label">Deadline</div><div class="cp-fact-val {{ $deadlineLate ? 'is-late' : '' }}">{{ $task->deadline ? \Illuminate\Support\Carbon::parse($task->deadline)->format('M d') : '—' }}</div></div>
                <div class="cp-fact"><div class="cp-fact-label">Design due</div><div class="cp-fact-val">{{ $task->design_deadline ? \Illuminate\Support\Carbon::parse($task->design_deadline)->format('M d') : '—' }}</div></div>
                <div class="cp-fact"><div class="cp-fact-label">Designer</div><div class="cp-fact-val">{{ $task->assignee->name ?? 'Unassigned' }}</div></div>
                <div class="cp-fact"><div class="cp-fact-label">Created by</div><div class="cp-fact-val">{{ $task->creator->name ?? '—' }}</div></div>
                <div class="cp-fact"><div class="cp-fact-label">Task status</div><div class="cp-fact-val" style="text-transform:capitalize">{{ str_replace('_', ' ', $task->status) }}</div></div>
            </div>

            @if($task->caption)
                <div>
                    <div class="cp-d-section"><i class="fas fa-quote-left"></i> Caption</div>
                    <p class="cp-text">{{ $task->caption }}</p>
                </div>
            @endif
            @if($task->brief)
                <div>
                    <div class="cp-d-section"><i class="fas fa-file-lines"></i> Brief</div>
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

<script>
(function () {
    const items = Array.from(document.querySelectorAll('.cp-card, .cp-mitem'));
    const search = document.getElementById('cpSearch');
    const stateButtons = Array.from(document.querySelectorAll('[data-state-filter]'));
    let activeState = null;

    function applyFilters() {
        const q = (search.value || '').trim().toLowerCase();
        items.forEach(el => {
            const okState = !activeState || el.dataset.state === activeState;
            const okSearch = !q || (el.dataset.search || '').includes(q);
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
    search.addEventListener('input', applyFilters);

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

    // fromDay: the month day this post was opened from, so the panel can offer a way back
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
        drawer.classList.remove('is-open');
        overlay.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        lastFocus?.focus?.();
    }

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-open-task]');
        if (trigger) {
            e.preventDefault();
            openTask(trigger.dataset.openTask, trigger.dataset.fromDay);
            return;
        }
        // Month view: clicking a day (or its "+N more") lists every post that day
        const day = e.target.closest('[data-open-day]');
        if (day) openDay(day.dataset.openDay);
    });
    document.querySelectorAll('[data-open-day]').forEach(day => day.addEventListener('keydown', (e) => {
        if ((e.key === 'Enter' || e.key === ' ') && e.target === day) { e.preventDefault(); openDay(day.dataset.openDay); }
    }));
    document.getElementById('cpDrawerClose').addEventListener('click', closeTask);
    overlay.addEventListener('click', closeTask);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeTask();
        const typing = /^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement?.tagName);
        if (e.key === '/' && !typing) { e.preventDefault(); search.focus(); }
    });

    // ── Drag a post onto another (today or future) day to move its go-live date ──
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
            const newDate = target.dataset.dropDate;
            const label = card.dataset.moveField === 'post_date' ? 'go-live date' : 'deadline';
            const title = card.getAttribute('title') || card.textContent.trim();
            const pretty = new Date(newDate + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
            if (!confirm(`Move the ${label} of "${title.split(' · ')[0]}" to ${pretty}?\nThe designer and task creator will be notified.`)) return;
            try {
                const response = await fetch(`/api/tasks/${card.dataset.moveTask}/reschedule`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ date_type: card.dataset.moveField, new_date: newDate }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Could not reschedule this post.');
                if (typeof ajax !== 'undefined') ajax.showSuccess(`Moved to ${pretty}`);
                // Reload so summaries, day totals and the attention lists all reflect the move
                setTimeout(() => window.location.reload(), 500);
            } catch (err) {
                if (typeof ajax !== 'undefined') ajax.showError(err.message); else alert(err.message);
            }
        });
    });

    // Allow linking straight to a post's details: ?task=ID
    const params = new URL(window.location.href).searchParams;
    if (params.get('task')) openTask(params.get('task'));
    else if (params.get('day')) openDay(params.get('day'));
})();
</script>
@endsection
