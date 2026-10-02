@extends('layouts.app')

@push('styles')
<style>
/* ════════ Festival Selections — Table Layout ════════ */

@keyframes rise{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
@keyframes pop{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:scale(1)}}

.fs-page{animation:rise .5s cubic-bezier(.22,1,.36,1) both}

/* ── Header ── */
.fs-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:40px;flex-wrap:wrap;gap:20px}
.fs-title{font-size:28px;font-weight:950;display:flex;align-items:center;gap:14px;
    color:var(--text);letter-spacing:-.25px}
.fs-title i{width:46px;height:46px;display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,var(--primary-dim) 0%,rgba(var(--primary-rgb),.06) 100%);
    border-radius:13px;color:var(--primary);font-size:20px;border:1.5px solid rgba(var(--primary-rgb),.18);
    -webkit-background-clip:unset;-webkit-text-fill-color:unset;background-clip:unset}
.fs-sub{font-size:14px;color:var(--text3);margin-top:6px;font-weight:600;letter-spacing:.15px}
.fs-sub strong{color:var(--text2);font-weight:800}
.fs-back{padding:10px 22px;font-weight:800;font-size:13px;border-radius:10px;
    border:1.5px solid var(--border);background:var(--card);color:var(--text);text-decoration:none;
    display:inline-flex;align-items:center;gap:8px;transition:all .25s ease;white-space:nowrap}
.fs-back:hover{border-color:var(--primary);color:var(--primary);transform:translateY(-2px);
    box-shadow:0 8px 24px rgba(0,0,0,.06)}
.fs-back i{font-size:11px;transition:transform .2s ease}
.fs-back:hover i{transform:translateX(-3px)}

/* ── Stat Strip ── */
.fs-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:32px}
.fs-stat{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px 22px;
    display:flex;align-items:center;gap:14px;transition:all .3s cubic-bezier(.22,1,.36,1);position:relative;overflow:hidden}
.fs-stat::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2.5px;
    background:linear-gradient(90deg,var(--primary),var(--primary-light));
    transform:scaleX(0);transform-origin:left;transition:transform .35s cubic-bezier(.22,1,.36,1)}
.fs-stat:hover::after{transform:scaleX(1)}
.fs-stat:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.07)}
.fs-stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;
    font-size:20px;flex-shrink:0;border:1.5px solid transparent;transition:all .3s ease}
.fs-stat:hover .fs-stat-icon{border-color:currentColor;transform:scale(1.06)}
.fs-stat-val{font-size:28px;font-weight:900;color:var(--text);line-height:1;letter-spacing:-1px}
.fs-stat-lbl{font-size:10px;color:var(--text3);font-weight:800;text-transform:uppercase;letter-spacing:.8px;margin-top:4px}

/* ── Toolbar ── */
.fs-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:28px}
.fs-toolbar-left{display:flex;align-items:center;gap:10px;flex-wrap:wrap;flex:1}
.fs-search-wrap{position:relative;min-width:200px;max-width:320px;flex:1}
.fs-search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text3);font-size:13px}
.fs-search{width:100%;padding:9px 14px 9px 34px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-weight:600;
    color:var(--text);background:var(--card);font-family:inherit;transition:all .25s ease}
.fs-search:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-dim)}
.fs-search::placeholder{color:var(--text3);font-weight:500}
.fs-select{padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-weight:600;
    color:var(--text);background:var(--card);font-family:inherit;min-width:140px;cursor:pointer;transition:all .25s ease}
.fs-select:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-dim)}
.fs-select:hover{border-color:rgba(var(--primary-rgb),.35)}
.fs-reset{padding:8px 16px;font-weight:700;font-size:12px;border-radius:8px;border:1.5px solid var(--border);
    background:var(--card);color:var(--text3);text-decoration:none;transition:all .2s ease;display:inline-flex;align-items:center;gap:5px}
.fs-reset:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim)}
.fs-per-page{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text3);font-weight:600}

/* Toggle */
.fs-toggle{display:flex;background:var(--card);border:1.5px solid var(--border);border-radius:10px;overflow:hidden}
.fs-toggle-btn{padding:9px 18px;font-size:12px;font-weight:700;color:var(--text3);cursor:pointer;
    border:none;background:transparent;font-family:inherit;display:flex;align-items:center;gap:6px;
    transition:all .2s ease;letter-spacing:.2px;-webkit-text-fill-color:currentColor}
.fs-toggle-btn:hover{color:var(--text);background:rgba(var(--primary-rgb),.04)}
.fs-toggle-btn.active{color:var(--primary);
    background:var(--primary-dim);
    box-shadow:inset 0 0 0 1px rgba(var(--primary-rgb),.25);
    -webkit-text-fill-color:currentColor}
.fs-toggle-btn i{font-size:11px}

.fs-empty-cell{font-size:12px;color:var(--text3);font-weight:700}
.fs-note-chip{display:inline-flex;align-items:center;max-width:220px;padding:4px 10px;border-radius:8px;
    background:rgba(var(--primary-rgb),.06);border:1px solid rgba(var(--primary-rgb),.14);
    color:var(--text2);font-size:11px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.fs-note-count{display:inline-flex;align-items:center;margin-left:6px;padding:3px 7px;border-radius:6px;
    background:var(--card2);border:1px solid var(--border);color:var(--text3);font-size:10px;font-weight:700}

/* ── Table ── */
.fs-table-wrap{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden;
    transition:all .3s cubic-bezier(.22,1,.36,1)}
.fs-table{width:100%;border-collapse:collapse}
.fs-table th{padding:14px 18px;font-size:10px;font-weight:800;color:var(--text3);text-transform:uppercase;
    letter-spacing:.8px;text-align:left;background:rgba(var(--primary-rgb),.015);
    border-bottom:1.5px solid var(--border);white-space:nowrap;user-select:none}
.fs-table th a{color:var(--text3);text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:color .2s}
.fs-table th a:hover{color:var(--text)}
.fs-table th .sort-icon{font-size:10px;opacity:.4;transition:all .2s}
.fs-table th .sort-icon.active{opacity:1;color:var(--primary)}
.fs-table td{padding:14px 18px;font-size:13px;color:var(--text);border-bottom:1px solid var(--border);vertical-align:middle}
.fs-table tbody tr{cursor:pointer;transition:all .2s ease}
.fs-table tbody tr:hover{background:rgba(var(--primary-rgb),.02)}
.fs-table tbody tr:last-child td{border-bottom:0}
.fs-table tbody tr.expanded{background:rgba(var(--primary-rgb),.03)}
.fs-table tbody tr.expanded td{border-bottom:1px solid var(--border)}

/* Client cell */
.fs-client-cell{display:flex;align-items:center;gap:12px}
.fs-avatar{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
    font-size:13px;font-weight:900;color:#fff;flex-shrink:0;box-shadow:0 3px 10px rgba(0,0,0,.12)}
.fs-client-name{font-weight:800;font-size:13px;letter-spacing:-.1px}
.fs-client-cat{font-size:11px;color:var(--text3);font-weight:600}

/* Badges */
.fs-badges{display:flex;flex-wrap:wrap;gap:5px}
.fs-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:6px;
    font-size:10px;font-weight:800;letter-spacing:.2px;border:1px solid transparent;transition:all .15s ease}
.fs-badge:hover{transform:translateY(-1px)}
.fs-badge.type{background:rgba(var(--primary-rgb),.08);color:var(--primary);border-color:rgba(var(--primary-rgb),.12)}
.fs-badge.insta{background:rgba(225,48,108,.08);color:#e1306c;border-color:rgba(225,48,108,.12)}
.fs-badge.fb{background:rgba(24,119,242,.08);color:#1877f2;border-color:rgba(24,119,242,.12)}
.fs-count-badge{display:inline-flex;align-items:center;justify-content:center;
    background:var(--primary-dim);color:var(--primary);min-width:28px;height:28px;
    border-radius:8px;font-size:13px;font-weight:900;padding:0 8px}

/* Expand icon */
.fs-expand-icon{transition:transform .2s ease;font-size:11px;color:var(--text3)}
.fs-expand-icon.open{transform:rotate(90deg);color:var(--primary)}

/* Detail row */
.fs-detail-row td{padding:0 !important;border-bottom:1px solid var(--border)}
.fs-detail-row:last-child td{border-bottom:0}
.fs-detail-inner{padding:8px 18px 18px 66px}
.fs-detail-grid{display:flex;flex-direction:column;gap:6px}
.fs-detail-item{display:flex;align-items:center;gap:14px;padding:12px 16px;border-radius:10px;
    background:rgba(var(--primary-rgb),.015);flex-wrap:wrap;transition:all .2s ease}
.fs-detail-item:hover{background:rgba(var(--primary-rgb),.04)}
.fs-detail-fest{display:flex;align-items:center;gap:8px;font-weight:800;font-size:13px;color:var(--text);min-width:190px}
.fs-detail-fest .emoji{font-size:20px;line-height:1}
.fs-detail-date{font-size:10px;font-weight:700;color:var(--text3);padding:2px 8px;border-radius:5px;
    background:rgba(var(--primary-rgb),.05)}
.fs-detail-notes{font-size:11px;color:var(--text3);font-style:italic;margin-left:auto;
    padding:3px 10px;border-radius:6px;background:rgba(var(--primary-rgb),.03);
    max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.fs-detail-user{font-size:10px;color:var(--text3);font-weight:600}

/* ── Pagination ── */
.fs-pagination{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;flex-wrap:wrap;gap:10px}
.fs-pagination-info{font-size:12px;color:var(--text3);font-weight:600}
.fs-pagination-links{display:flex;gap:4px}
.fs-pagination-links a,
.fs-pagination-links span{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;
    padding:0 10px;border:1.5px solid var(--border);border-radius:8px;font-size:12px;font-weight:700;
    color:var(--text);text-decoration:none;background:var(--card);transition:all .2s ease}
.fs-pagination-links a:hover{background:var(--primary-dim);color:var(--primary);border-color:rgba(var(--primary-rgb),.3);
    transform:translateY(-1px)}
.fs-pagination-links span.current{background:linear-gradient(135deg,var(--primary),var(--primary-light));
    color:#fff;border-color:var(--primary)}
.fs-pagination-links span.dots{border:0;background:none;color:var(--text3)}

/* ── Festival View ── */
.fs-fests{display:flex;flex-direction:column;gap:14px}
.fs-fcard{background:var(--card);border:1px solid var(--border);border-radius:16px;
    transition:all .3s cubic-bezier(.22,1,.36,1);overflow:hidden}
.fs-fcard:hover{box-shadow:0 8px 28px rgba(0,0,0,.06);border-color:rgba(var(--primary-rgb),.2)}
.fs-fcard-head{display:flex;align-items:center;justify-content:space-between;
    padding:20px 24px;border-bottom:1px solid var(--border);flex-wrap:wrap;gap:10px}
.fs-fcard-info{display:flex;align-items:center;gap:14px}
.fs-fcard-emoji{font-size:36px;line-height:1}
.fs-fcard-name{font-size:16px;font-weight:900;color:var(--text);letter-spacing:-.2px}
.fs-fcard-date{font-size:12px;color:var(--text3);font-weight:600;margin-top:2px;display:flex;align-items:center;gap:4px}
.fs-fcard-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:20px;
    font-size:12px;font-weight:800;transition:all .2s ease}
.fs-fcard-pill.has{background:rgba(16,185,129,.08);color:#059669;border:1px solid rgba(16,185,129,.15)}
.fs-fcard-pill.none{background:rgba(239,68,68,.06);color:#dc2626;border:1px solid rgba(239,68,68,.12)}
.fs-fcard-rows{padding:8px 12px}
.fs-frow{display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;
    transition:all .2s ease;flex-wrap:wrap}
.fs-frow:hover{background:rgba(var(--primary-rgb),.03)}
.fs-frow+.fs-frow{border-top:1px solid rgba(var(--primary-rgb),.05)}
.fs-frow-avatar{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;
    font-size:11px;font-weight:900;color:#fff;flex-shrink:0;box-shadow:0 2px 6px rgba(0,0,0,.1)}
.fs-frow-name{font-size:13px;font-weight:800;color:var(--text);min-width:110px}
.fs-fcard-empty{padding:20px;text-align:center;color:var(--text3);font-size:13px;font-weight:600}

/* ── Not Responded ── */
.fs-pending{margin-top:32px;background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;
    animation:rise .6s cubic-bezier(.22,1,.36,1) both}
.fs-pending-head{font-size:14px;font-weight:800;color:var(--text);margin-bottom:14px;
    display:flex;align-items:center;gap:8px}
.fs-pending-head i{color:#f59e0b;font-size:15px}
.fs-pending-head span{font-weight:600;color:var(--text3);font-size:13px;margin-left:4px}
.fs-pending-list{display:flex;flex-wrap:wrap;gap:8px}
.fs-pending-chip{display:inline-flex;align-items:center;gap:7px;padding:7px 14px;border-radius:9px;
    font-size:12px;font-weight:700;color:var(--text3);background:var(--card2);border:1px solid var(--border);transition:all .2s ease}
.fs-pending-chip:hover{border-color:rgba(var(--primary-rgb),.25);color:var(--text2);transform:translateY(-1px)}
.fs-pending-chip-av{width:24px;height:24px;border-radius:7px;display:flex;align-items:center;justify-content:center;
    font-size:10px;font-weight:900;color:#fff;flex-shrink:0}

/* ── Empty State ── */
.fs-empty{text-align:center;padding:80px 40px;background:var(--card);border:1.5px dashed var(--border);
    border-radius:18px;position:relative;overflow:hidden;animation:pop .4s cubic-bezier(.22,1,.36,1) both}
.fs-empty::before{content:'';position:absolute;inset:0;
    background:radial-gradient(circle at 30% 40%,var(--primary) 0%,transparent 50%);opacity:.025;pointer-events:none}
.fs-empty i{font-size:56px;opacity:.12;display:block;margin-bottom:18px;color:var(--primary)}
.fs-empty h3{font-size:17px;font-weight:900;color:var(--text);margin:0 0 6px}
.fs-empty p{font-size:13px;color:var(--text3);margin:0;font-weight:500}

/* Content type demand */
.fs-demand { display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:12px;margin-bottom:24px; }
.fs-demand-card { background:var(--card);border:1px solid var(--border);border-radius:14px;padding:16px;text-align:center;
    transition:all .3s ease;animation:rise .4s ease both; }
.fs-demand-card:hover { transform:translateY(-4px);box-shadow:0 8px 24px rgba(0,0,0,.04);border-color:rgba(var(--primary-rgb),.15) }
.fs-demand-val  { font-size:24px;font-weight:900;color:var(--primary);line-height:1;margin-bottom:4px; }
.fs-demand-icon { font-size:18px;opacity:.5;color:var(--text3);margin-bottom:6px; }
.fs-demand-lbl  { font-size:11px;color:var(--text3);font-weight:700;text-transform:uppercase;letter-spacing:.5px; }

/* Responsive */
@media(max-width:900px){.fs-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:700px){
    .fs-header{flex-direction:column;gap:14px}
    .fs-stats{grid-template-columns:1fr 1fr}.fs-stat{padding:16px}
    .fs-demand{grid-template-columns:repeat(3,1fr)}
    .fs-stat-val{font-size:24px}.fs-stat-icon{width:40px;height:40px;font-size:17px}
    .fs-toolbar{flex-direction:column;align-items:stretch}
    .fs-toolbar-left{flex-direction:column}
    .fs-search-wrap{max-width:100%}
    .fs-table-wrap{overflow-x:auto}
    .fs-detail-inner{padding:8px 12px 12px 12px}
    .fs-fcard-head{flex-direction:column;align-items:flex-start}
}
@media(max-width:480px){
    .fs-title{font-size:22px}.fs-title i{width:38px;height:38px;font-size:17px;border-radius:10px}
    .fs-stats, .fs-demand{grid-template-columns:1fr}
    .fs-toggle-btn{padding:8px 12px;font-size:11px}
}
</style>
@endpush

@section('content')
@php
    $avatarColors = ['#4F6DF0','#7c3aed','#059669','#d97706','#e1306c','#0284c7','#be185d','#0891b2'];
    $colorsCount = count($avatarColors) ?: 1;
    $sortBy = $sortBy ?? request('sort', 'name');
    $sortDir = $sortDir ?? request('dir', 'asc');
    $sortUrl = fn($col) => request()->fullUrlWithQuery(['sort' => $col, 'dir' => ($sortBy === $col && $sortDir === 'asc') ? 'desc' : 'asc']);
    $platformMeta = function($p) {
        $key = strtolower((string) $p);
        return match($key) {
            'instagram' => ['icon' => 'fa-instagram', 'brand' => true],
            'facebook' => ['icon' => 'fa-facebook-f', 'brand' => true],
            'twitter', 'x', 'twitter_x' => ['icon' => 'fa-x-twitter', 'brand' => true],
            'linkedin' => ['icon' => 'fa-linkedin-in', 'brand' => true],
            'youtube' => ['icon' => 'fa-youtube', 'brand' => true],
            'whatsapp' => ['icon' => 'fa-whatsapp', 'brand' => true],
            'tiktok' => ['icon' => 'fa-tiktok', 'brand' => true],
            'pinterest' => ['icon' => 'fa-pinterest-p', 'brand' => true],
            'telegram' => ['icon' => 'fa-telegram', 'brand' => true],
            'reddit' => ['icon' => 'fa-reddit-alien', 'brand' => true],
            default => ['icon' => 'fa-hashtag', 'brand' => false],
        };
    };
    $platformLabel = fn($p) => ucfirst(str_replace('_', ' ', (string) $p));
@endphp

<div class="fs-page">

    {{-- Header --}}
    <div class="fs-header">
        <div>
            <div class="fs-title"><i class="fa-solid fa-list-check" aria-hidden="true"></i> Festival Selections</div>
            <div class="fs-sub">{{ $monthLabel }} — <strong>{{ $totalSelections }}</strong> selections from <strong>{{ $totalClients }}</strong> clients</div>
        </div>
        <a href="{{ route('strategist.festival-calendar') }}" class="fs-back"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Calendar</a>
    </div>

    {{-- Stats --}}
    <div class="fs-stats">
        <div class="fs-stat">
            <div class="fs-stat-icon" style="background:rgba(var(--primary-rgb),.08);color:var(--primary)"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i></div>
            <div><div class="fs-stat-val">{{ $festivals->count() }}</div><div class="fs-stat-lbl">Festivals</div></div>
        </div>
        <div class="fs-stat">
            <div class="fs-stat-icon" style="background:rgba(16,185,129,.08);color:#059669"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></div>
            <div><div class="fs-stat-val">{{ $totalSelections }}</div><div class="fs-stat-lbl">Selections</div></div>
        </div>
        <div class="fs-stat">
            <div class="fs-stat-icon" style="background:rgba(79,109,240,.08);color:#4F6DF0"><i class="fa-solid fa-users" aria-hidden="true"></i></div>
            <div><div class="fs-stat-val">{{ $totalClients }}</div><div class="fs-stat-lbl">Active</div></div>
        </div>
        <div class="fs-stat">
            <div class="fs-stat-icon" style="background:rgba(245,158,11,.08);color:#f59e0b"><i class="fa-solid fa-user-clock" aria-hidden="true"></i></div>
            <div><div class="fs-stat-val">{{ $inactiveClients->count() }}</div><div class="fs-stat-lbl">Pending</div></div>
        </div>
    </div>

    {{-- Content Type Demand --}}
    @if(!empty($typeCounts))
    <div style="margin-bottom:12px;font-size:12px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:.6px">Content Demand</div>
    <div class="fs-demand">
        @foreach([
            'post'=>['fa-image','Posts'],'story'=>['fa-mobile-screen','Stories'],
            'reel'=>['fa-film','Reels'],'carousel'=>['fa-layer-group','Carousels'],
            'video'=>['fa-video','Videos'],
        ] as $type => [$icon, $label])
        <div class="fs-demand-card" style="animation-delay:{{ $loop->index * 0.05 }}s">
            <div class="fs-demand-icon"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></div>
            <div class="fs-demand-val">{{ $typeCounts[$type] ?? 0 }}</div>
            <div class="fs-demand-lbl">{{ $label }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="fs-toolbar">
        <form method="GET" class="fs-toolbar-left" id="fsForm">
            <input type="hidden" name="sort" value="{{ $sortBy }}">
            <input type="hidden" name="dir" value="{{ $sortDir }}">

            <div class="fs-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" class="fs-search" placeholder="Search clients by name..." value="{{ $search }}" autocomplete="off">
            </div>

            <x-client-select :clients="$clients" name="client_id" value="{{ $clientId }}" placeholder="All Clients" class="fs-select" onchange="this.form.submit()" />

            <div class="fs-per-page">
                <span>Show</span>
                <select name="per_page" class="fs-select" style="min-width:70px" onchange="this.form.submit()">
                    @foreach([25, 50, 100] as $pp)
                        <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>

            @if($search || $clientId)
                <a href="{{ route('strategist.festival-selections') }}" class="fs-reset"><i class="fa-solid fa-xmark" aria-hidden="true"></i> Reset</a>
            @endif
        </form>

        @if($totalSelections > 0)
        <div class="fs-toggle" role="tablist">
            <button class="fs-toggle-btn active" onclick="switchView('client')" data-view="client" role="tab" aria-selected="true" type="button">
                <i class="fa-solid fa-users" aria-hidden="true"></i> By Client
            </button>
            <button class="fs-toggle-btn" onclick="switchView('festival')" data-view="festival" role="tab" aria-selected="false" type="button">
                <i class="fa-solid fa-calendar-days" aria-hidden="true"></i> By Festival
            </button>
        </div>
        @endif
    </div>

    @if($paginatedClients->isEmpty() && collect($festivalSelections)->flatten()->isEmpty())
        <div class="fs-empty">
            <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i>
            <h3>No selections yet for {{ $monthLabel }}</h3>
            <p>Clients haven't selected any festivals yet.</p>
        </div>
    @else

        {{-- ═══ VIEW 1: By Client (Table) ═══ --}}
        <div id="view-client">
            @if($paginatedClients->isEmpty())
                <div class="fs-empty">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <h3>No results found</h3>
                    <p>Try a different search term or filter.</p>
                </div>
            @else
            <div class="fs-table-wrap">
                <table class="fs-table">
                    <thead>
                        <tr>
                            <th style="width:30px"></th>
                            <th>
                                <a href="{{ $sortUrl('name') }}">
                                    Client
                                    <i class="fa-solid fa-sort sort-icon {{ $sortBy === 'name' ? 'active' : '' }}"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('count') }}">
                                    Festivals
                                    <i class="fa-solid fa-sort sort-icon {{ $sortBy === 'count' ? 'active' : '' }}"></i>
                                </a>
                            </th>
                            <th>Content Types</th>
                            <th>Platforms</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paginatedClients as $client)
                        @php
                            $sels = $client->festivalSelections;
                            $ci = is_numeric($client->id) ? intval($client->id) % $colorsCount : 0;
                            $allTypes = $sels->flatMap(fn($s) => $s->content_types ?? [])->countBy();
                            $allPlatforms = $sels->flatMap(fn($s) => $s->platforms ?? [])->unique()->values();
                            $noteSamples = $sels->pluck('notes')->filter()->values();
                            $firstNote = $noteSamples->first();
                            $typeIcon = fn($t) => match($t) { 'post'=>'fa-image','story'=>'fa-mobile-screen','reel'=>'fa-film','video'=>'fa-video','carousel'=>'fa-layer-group',default=>'fa-image' };
                        @endphp
                        <tr onclick="toggleDetail({{ $client->id }})" id="row-{{ $client->id }}">
                            <td><i class="fa-solid fa-chevron-right fs-expand-icon" id="icon-{{ $client->id }}"></i></td>
                            <td>
                                <div class="fs-client-cell">
                                    <div class="fs-avatar" style="background:{{ $avatarColors[$ci] }}">{{ strtoupper(substr($client->name,0,1)) }}</div>
                                    <div>
                                        <div class="fs-client-name">{{ $client->name }}</div>
                                        @if($client->category)<div class="fs-client-cat">{{ $client->category }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="fs-count-badge">{{ $sels->count() }}</span></td>
                            <td>
                                <div style="display:flex;flex-wrap:wrap;gap:4px">
                                    @foreach($allTypes as $t => $cnt)
                                        <span class="fs-badge" style="background:var(--primary-dim);color:var(--primary)" title="{{ ucfirst($t) }}"><i class="fa-solid {{ $typeIcon($t) }}"></i> {{ $cnt }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div style="display:flex;flex-wrap:wrap;gap:4px">
                                    @forelse($allPlatforms->take(3) as $p)
                                        @php $pm = $platformMeta($p); @endphp
                                        <span class="fs-badge {{ $p==='instagram'?'insta':'fb' }}">
                                            <i class="{{ $pm['brand'] ? 'fa-brands' : 'fa-solid' }} {{ $pm['icon'] }}" aria-hidden="true"></i>
                                            {{ $platformLabel($p) }}
                                        </span>
                                    @empty
                                        <span class="fs-empty-cell">—</span>
                                    @endforelse
                                    @if($allPlatforms->count() > 3)
                                        <span class="fs-badge type">+{{ $allPlatforms->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($noteSamples->isNotEmpty())
                                    <span class="fs-note-chip" title="{{ $firstNote }}"><i class="fa-solid fa-note-sticky" style="margin-right:6px"></i>{{ Str::limit($firstNote, 34) }}</span>
                                    @if($noteSamples->count() > 1)
                                        <span class="fs-note-count">+{{ $noteSamples->count() - 1 }}</span>
                                    @endif
                                @else
                                    <span class="fs-empty-cell">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="fs-detail-row" id="detail-{{ $client->id }}" style="display:none">
                            <td colspan="5">
                                <div class="fs-detail-inner">
                                    <div class="fs-detail-grid">
                                        @foreach($sels as $sel)
                                        <div class="fs-detail-item">
                                            <div class="fs-detail-fest">
                                                <span class="emoji" aria-hidden="true">{{ optional($sel->festival)->emoji ?? '🎉' }}</span>
                                                {{ optional($sel->festival)->name ?? 'Untitled' }}
                                                <span class="fs-detail-date">{{ optional(optional($sel->festival)->date)->format('M d') ?? '' }}</span>
                                            </div>
                                            <div class="fs-badges">
                                                @foreach($sel->content_types ?? [] as $t)
                                                    <span class="fs-badge" style="background:var(--primary-dim);color:var(--primary)"><i class="fa-solid {{ $typeIcon($t) }}"></i> {{ ucfirst($t) }}</span>
                                                @endforeach
                                                @foreach($sel->platforms ?? [] as $p)
                                                    @php $pm = $platformMeta($p); @endphp
                                                    <span class="fs-badge {{ $p==='instagram'?'insta':'fb' }}"><i class="{{ $pm['brand'] ? 'fa-brands' : 'fa-solid' }} {{ $pm['icon'] }}" aria-hidden="true"></i> {{ $platformLabel($p) }}</span>
                                                @endforeach
                                            </div>
                                            @if($sel->notes)<div class="fs-detail-notes" title="{{ $sel->notes }}">"{{ Str::limit($sel->notes, 60) }}"</div>@endif
                                            @if($sel->user)<div class="fs-detail-user"><i class="fa-solid fa-user" aria-hidden="true"></i> {{ $sel->user->name }}</div>@endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if($paginatedClients->lastPage() > 1)
                <div class="fs-pagination">
                    <div class="fs-pagination-info">
                        Showing {{ $paginatedClients->firstItem() }}–{{ $paginatedClients->lastItem() }} of {{ $paginatedClients->total() }} clients
                    </div>
                    <div class="fs-pagination-links">
                        @if($paginatedClients->onFirstPage())
                            <span style="opacity:.4"><i class="fa-solid fa-chevron-left"></i></span>
                        @else
                            <a href="{{ $paginatedClients->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i></a>
                        @endif

                        @foreach($paginatedClients->getUrlRange(max(1, $paginatedClients->currentPage()-2), min($paginatedClients->lastPage(), $paginatedClients->currentPage()+2)) as $page => $url)
                            @if($page == $paginatedClients->currentPage())
                                <span class="current">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($paginatedClients->currentPage() + 2 < $paginatedClients->lastPage())
                            <span class="dots">…</span>
                            <a href="{{ $paginatedClients->url($paginatedClients->lastPage()) }}">{{ $paginatedClients->lastPage() }}</a>
                        @endif

                        @if($paginatedClients->hasMorePages())
                            <a href="{{ $paginatedClients->nextPageUrl() }}"><i class="fa-solid fa-chevron-right"></i></a>
                        @else
                            <span style="opacity:.4"><i class="fa-solid fa-chevron-right"></i></span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- ═══ VIEW 2: By Festival ═══ --}}
        <div id="view-festival" class="fs-fests" style="display:none">
            @foreach($festivals as $fest)
            @php $festSels = $festivalSelections[$fest->id] ?? collect(); @endphp
            <div class="fs-fcard">
                <div class="fs-fcard-head">
                    <div class="fs-fcard-info">
                        <div class="fs-fcard-emoji" aria-hidden="true">{{ $fest->emoji ?? '🎉' }}</div>
                        <div>
                            <div class="fs-fcard-name">{{ $fest->name }}</div>
                            <div class="fs-fcard-date"><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ $fest->date->format('D, M d') }}</div>
                        </div>
                    </div>
                    <div class="fs-fcard-pill {{ $festSels->isEmpty() ? 'none' : 'has' }}">
                        <i class="fa-solid {{ $festSels->isEmpty() ? 'fa-xmark' : 'fa-users' }}" aria-hidden="true"></i>
                        {{ $festSels->count() }} {{ Str::plural('client', $festSels->count()) }}
                    </div>
                </div>

                @if($festSels->isNotEmpty())
                <div class="fs-fcard-rows" role="list">
                    @foreach($festSels as $sel)
                    @php
                        $selClient = $sel->client ?? $clients->find($sel->client_id);
                        $sci = $selClient ? (is_numeric($selClient->id) ? intval($selClient->id) % $colorsCount : 0) : 0;
                    @endphp
                    @if($selClient)
                    <div class="fs-frow" role="listitem">
                        <div class="fs-frow-avatar" style="background:{{ $avatarColors[$sci] }}">{{ strtoupper(substr($selClient->name,0,1)) }}</div>
                        <div class="fs-frow-name">{{ $selClient->name }}</div>
                        <div class="fs-badges">
                            @foreach($sel->platforms ?? [] as $p)
                                @php $pm = $platformMeta($p); @endphp
                                <span class="fs-badge {{ $p==='instagram'?'insta':'fb' }}"><i class="{{ $pm['brand'] ? 'fa-brands' : 'fa-solid' }} {{ $pm['icon'] }}" aria-hidden="true"></i> {{ $platformLabel($p) }}</span>
                            @endforeach
                        </div>
                        @if($sel->notes)<div class="fs-detail-notes" title="{{ $sel->notes }}">"{{ Str::limit($sel->notes, 50) }}"</div>@endif
                    </div>
                    @endif
                    @endforeach
                </div>
                @else
                <div class="fs-fcard-empty">
                    <i class="fa-regular fa-circle-xmark" aria-hidden="true"></i> No clients selected this festival yet
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @endif

    {{-- Pending clients --}}
    @if($inactiveClients->isNotEmpty() && !$clientId)
    <div class="fs-pending">
        <div class="fs-pending-head">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            Haven't responded <span>— {{ $inactiveClients->count() }} {{ Str::plural('client', $inactiveClients->count()) }}</span>
        </div>
        <div class="fs-pending-list">
            @foreach($inactiveClients as $ic)
            @php $ici = is_numeric($ic->id) ? intval($ic->id) % $colorsCount : 0; @endphp
            <div class="fs-pending-chip">
                <div class="fs-pending-chip-av" style="background:{{ $avatarColors[$ici] }}">{{ strtoupper(substr($ic->name,0,1)) }}</div>
                {{ $ic->name }}
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleDetail(id) {
    const row = document.getElementById('detail-' + id);
    const icon = document.getElementById('icon-' + id);
    const parentRow = document.getElementById('row-' + id);
    if (row.style.display === 'none') {
        row.style.display = '';
        icon.classList.add('open');
        parentRow.classList.add('expanded');
    } else {
        row.style.display = 'none';
        icon.classList.remove('open');
        parentRow.classList.remove('expanded');
    }
}

function switchView(view) {
    document.getElementById('view-client').style.display = view === 'client' ? '' : 'none';
    document.getElementById('view-festival').style.display = view === 'festival' ? '' : 'none';
    document.querySelectorAll('.fs-toggle-btn').forEach(btn => {
        const active = btn.dataset.view === view;
        btn.classList.toggle('active', active);
        btn.setAttribute('aria-selected', active);
    });
}

// Debounced search
let searchTimer;
document.querySelector('.fs-search')?.addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        document.getElementById('fsForm').submit();
    }, 400);
});
</script>
@endpush
