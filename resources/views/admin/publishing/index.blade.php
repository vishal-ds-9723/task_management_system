@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
/* ── Admin Publishing Tracker ──────────────────── */
.apt-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:16px}
.apt-header-left{display:flex;align-items:center;gap:14px}
.apt-icon-wrap{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,var(--primary),#6882F5);display:flex;align-items:center;justify-content:center;color:#fff;font-size:19px;box-shadow:0 4px 12px rgba(79,109,240,.25)}
.apt-title{font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.3px;font-family:'Plus Jakarta Sans',sans-serif}
.apt-subtitle{font-size:12px;color:var(--text3);margin-top:2px;font-weight:500}
.apt-header-right{display:flex;gap:10px;align-items:center}
.apt-result-count{font-size:12px;color:var(--text3);font-weight:500;background:var(--card2);padding:6px 14px;border-radius:8px;display:flex;align-items:center;gap:6px}
.apt-result-count strong{color:var(--text);font-weight:700}

/* Stats */
.apt-stats{display:grid;grid-template-columns:repeat(6, 1fr);gap:12px;margin-bottom:20px}
.apt-stat{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:16px 14px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:all .2s ease;text-decoration:none;position:relative;overflow:hidden;box-shadow:var(--shadow-sm)}
.apt-stat:hover{border-color:var(--primary);transform:translateY(-2px);box-shadow:var(--shadow)}
.apt-stat-active{border-color:var(--primary) !important;box-shadow:0 0 0 3px rgba(79,109,240,.08),var(--shadow-sm)}
.apt-stat-active .apt-stat-val{color:var(--primary)}
.apt-stat-icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;transition:transform .2s}
.apt-stat:hover .apt-stat-icon{transform:scale(1.08)}
.apt-stat-val{font-size:22px;font-weight:800;color:var(--text);line-height:1}
.apt-stat-label{font-size:10.5px;color:var(--text3);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:3px}

/* Filter Bar */
.apt-filter-bar{display:flex;align-items:center;gap:10px;padding:12px 18px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:18px;flex-wrap:wrap}
.apt-filter-bar i.fa-filter{color:var(--text3);font-size:13px;margin-right:2px}
.apt-filter-bar select,.apt-filter-bar input[type="text"]{height:36px;border:1px solid var(--border);border-radius:8px;padding:0 12px;font-size:12.5px;background:var(--bg);color:var(--text);transition:border-color .2s;outline:none;font-weight:500}
.apt-filter-bar select:focus,.apt-filter-bar input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,109,240,.08)}
.apt-filter-bar input[type="text"]{width:220px}
.apt-filter-sep{width:1px;height:24px;background:var(--border);margin:0 4px}
.apt-btn{padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;cursor:pointer;border:none;transition:all .2s;white-space:nowrap}
.apt-btn-primary{background:var(--primary);color:#fff}
.apt-btn-primary:hover{background:var(--primary-hover);box-shadow:0 2px 8px rgba(79,109,240,.2)}
.apt-btn-ghost{background:transparent;color:var(--text3);font-weight:500}
.apt-btn-ghost:hover{color:var(--primary);background:var(--primary-dim)}

/* Badges */
.apt-badge-client-yes{background:rgba(16,185,129,.12);color:#059669;border:1px solid rgba(16,185,129,.3);padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:4px}
.apt-badge-client-no{background:rgba(239,68,68,.1);color:#EF4444;border:1px solid rgba(239,68,68,.25);padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:4px}
.apt-btn-toggle-client{font-size:10.5px;font-weight:700;padding:3px 8px;border-radius:6px;cursor:pointer;transition:all .18s;border:1px solid var(--border);display:inline-flex;align-items:center;gap:3px;white-space:nowrap}
.apt-btn-toggle-client.is-no{background:rgba(16,185,129,.1);color:#059669;border-color:rgba(16,185,129,.3)}
.apt-btn-toggle-client.is-no:hover{background:#10B981;color:#fff}
.apt-btn-toggle-client.is-yes{background:var(--card2);color:var(--text3);border-color:var(--border)}
.apt-btn-toggle-client.is-yes:hover{background:rgba(239,68,68,.1);color:#EF4444;border-color:rgba(239,68,68,.3)}

/* Table Container */
.apt-table-wrap{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-sm)}
.apt-table{width:100%;border-collapse:collapse;border-spacing:0}
.apt-table thead{position:sticky;top:0;z-index:2}
.apt-table th{background:var(--card2);padding:11px 14px;font-size:10.5px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.6px;text-align:left;border-bottom:2px solid var(--border);white-space:nowrap;user-select:none}
.apt-table th:first-child{padding-left:18px}
.apt-table th:last-child{padding-right:18px}
.apt-table td{padding:13px 14px;font-size:12.5px;color:var(--text);border-bottom:1px solid var(--border);vertical-align:middle;transition:background .15s}
.apt-table td:first-child{padding-left:18px}
.apt-table td:last-child{padding-right:18px}
.apt-table tbody tr:hover td{background:rgba(79,109,240,.02)}
.apt-table tbody tr.selected td{background:rgba(79,109,240,.04)}

/* Checkbox */
.apt-check{width:16px;height:16px;accent-color:var(--primary);cursor:pointer;border-radius:4px}

/* Task cell */
.apt-task-cell{display:flex;align-items:center;gap:12px;min-width:200px}
.apt-thumb-box{position:relative;width:44px;height:44px;border-radius:10px;overflow:hidden;flex-shrink:0;background:var(--card2);border:1px solid var(--border);cursor:pointer;transition:all .2s ease}
.apt-thumb-box:hover{transform:scale(1.06);border-color:var(--primary);box-shadow:0 4px 14px rgba(79,109,240,.25)}
.apt-thumb-box img{width:100%;height:100%;object-fit:cover;display:block}
.apt-thumb-overlay{position:absolute;inset:0;background:rgba(15,23,42,.45);display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;opacity:0;transition:opacity .2s;backdrop-filter:blur(2px)}
.apt-thumb-box:hover .apt-thumb-overlay{opacity:1}
.apt-thumb-video-badge{position:absolute;bottom:2px;right:2px;background:rgba(0,0,0,.75);color:#fff;font-size:8px;padding:2px 4px;border-radius:4px;line-height:1}
.apt-thumb-empty{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--text3);font-size:14px;background:var(--card2);opacity:.7}
.apt-task-info{min-width:0;flex:1}
.apt-task-title{font-weight:700;color:var(--text);font-size:13px;max-width:230px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;line-height:1.3;cursor:pointer}
.apt-task-title:hover{color:var(--primary);text-decoration:underline}
.apt-task-type{font-size:10px;color:var(--text3);font-weight:500;margin-top:2px;display:flex;align-items:center;gap:4px}

/* Person cell */
.apt-person{display:flex;align-items:center;gap:7px;font-size:12px;font-weight:500;color:var(--text2)}
.apt-avatar{width:24px;height:24px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:9.5px;font-weight:700;color:#fff;flex-shrink:0;text-transform:uppercase}

/* Platform dots */
.apt-platform-dots{display:flex;gap:5px;flex-wrap:wrap}
.apt-platform-dot{width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:13px;transition:all .2s;cursor:default;position:relative}
.apt-platform-dot-done{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2)}
.apt-platform-dot-done::after{content:'✓';position:absolute;bottom:-2px;right:-2px;font-size:7px;font-weight:900;background:#10B981;color:#fff;width:11px;height:11px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid var(--card)}
.apt-platform-dot-pending{background:var(--card2);border:1px dashed var(--border2);opacity:.6}

/* Status */
.apt-status-tag{padding:4px 9px;border-radius:6px;font-size:10.5px;font-weight:700;white-space:nowrap;display:inline-flex;align-items:center;gap:4px;letter-spacing:.2px}
.apt-status-awaiting{background:rgba(245,158,11,.08);color:#D97706}
.apt-status-overdue{background:rgba(239,68,68,.08);color:#DC2626;border:1px solid rgba(239,68,68,.2)}
.apt-status-today{background:rgba(79,109,240,.08);color:var(--primary);border:1px solid rgba(79,109,240,.2)}
.apt-status-published{background:rgba(16,185,129,.08);color:#059669}

/* Action buttons */
.apt-view-btn{display:inline-flex;align-items:center;gap:4px;padding:6px 11px;border-radius:7px;font-size:11.5px;font-weight:600;text-decoration:none;transition:all .2s;border:1px solid var(--border);color:var(--text2);background:var(--card)}
.apt-view-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--card2)}
.apt-btn-publish{display:inline-flex;align-items:center;gap:4px;padding:6px 11px;border-radius:7px;font-size:11.5px;font-weight:700;border:none;cursor:pointer;background:linear-gradient(135deg,#10B981,#059669);color:#fff;box-shadow:0 2px 6px rgba(16,185,129,.25);transition:all .2s}
.apt-btn-publish:hover{transform:translateY(-1px);filter:brightness(1.06)}
.apt-btn-revert{display:inline-flex;align-items:center;gap:4px;padding:6px 10px;border-radius:7px;font-size:11.5px;font-weight:600;border:1px solid rgba(239,68,68,.3);cursor:pointer;background:rgba(239,68,68,.06);color:#DC2626;transition:all .2s}
.apt-btn-revert:hover{background:rgba(239,68,68,.12)}

/* 1-Click Copy Button */
.apt-btn-copy-inline{padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--card2);color:var(--text2);font-size:11px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;transition:all 0.15s}
.apt-btn-copy-inline:hover{border-color:var(--primary);color:var(--primary)}

/* Date cell */
.apt-date{font-size:11.5px;color:var(--text3);font-weight:500;white-space:nowrap}
.apt-date-ago{display:block;font-size:10px;color:var(--text3);opacity:.7;margin-top:1px}

/* Floating Bulk Bar */
.apt-bulk-bar{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(100px);background:var(--card);border:1px solid var(--border);border-radius:16px;padding:12px 24px;display:flex;align-items:center;gap:18px;box-shadow:0 12px 36px rgba(0,0,0,0.18);z-index:900;transition:transform 0.3s cubic-bezier(0.16,1,0.3,1)}
.apt-bulk-bar.visible{transform:translateX(-50%) translateY(0)}

/* Slide-Over Drawer */
.apt-drawer-backdrop{position:fixed;inset:0;background:rgba(15,23,42,0.5);backdrop-filter:blur(3px);z-index:1000;opacity:0;pointer-events:none;transition:opacity 0.25s ease}
.apt-drawer-backdrop.open{opacity:1;pointer-events:auto}
.apt-drawer{position:fixed;top:0;right:0;bottom:0;width:540px;max-width:92vw;background:var(--card);border-left:1px solid var(--border);box-shadow:-8px 0 32px rgba(0,0,0,0.16);z-index:1001;transform:translateX(100%);transition:transform 0.3s cubic-bezier(0.16,1,0.3,1);display:flex;flex-direction:column}
.apt-drawer.open{transform:translateX(0)}
.apt-drawer-header{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:12px;background:var(--card2)}
.apt-drawer-body{flex:1;overflow-y:auto;padding:22px;display:flex;flex-direction:column;gap:18px}
.apt-drawer-footer{padding:16px 22px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;background:var(--card2)}

/* Pagination */
.apt-pagination{display:flex;justify-content:center;align-items:center;gap:6px;margin-top:20px}
.apt-page-btn{min-width:32px;height:32px;padding:0 8px;border-radius:6px;border:1px solid var(--border);background:var(--card);color:var(--text2);font-size:12px;font-weight:600;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:all .15s}
.apt-page-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--card2)}
.apt-page-active{background:var(--primary) !important;color:#fff !important;border-color:var(--primary) !important}
.apt-page-disabled{opacity:.4;pointer-events:none}
.apt-page-dots{width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;color:var(--text3)}

@media(max-width:1100px){
    .apt-stats{grid-template-columns:repeat(3,1fr)}
}
@media(max-width:700px){
    .apt-stats{grid-template-columns:repeat(2,1fr)}
    .apt-drawer{width:100vw}
}
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="apt-header">
    <div class="apt-header-left">
        <div class="apt-icon-wrap"><i class="fa-solid fa-tower-broadcast"></i></div>
        <div>
            <div class="apt-title">Publishing Tracker</div>
            <div class="apt-subtitle">Track and manage post-launch publishing across all approved tasks</div>
        </div>
    </div>
    <div class="apt-header-right">
        <div class="apt-result-count">
            <i class="fa-solid fa-list-check"></i>
            Showing <strong>{{ $tasks->count() }}</strong> of <strong>{{ $tasks->total() }}</strong> tasks
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="apt-stats">
    <a href="{{ route('admin.publishing') }}" class="apt-stat {{ !request('filter') ? 'apt-stat-active' : '' }}">
        <div class="apt-stat-icon" style="background:rgba(79,109,240,.1);color:var(--primary)"><i class="fa-solid fa-layer-group"></i></div>
        <div>
            <div class="apt-stat-val">{{ $totalCount }}</div>
            <div class="apt-stat-label">All Tasks</div>
        </div>
    </a>
    <a href="{{ route('admin.publishing', ['filter' => 'pending']) }}" class="apt-stat {{ in_array(request('filter'), ['pending', 'awaiting']) ? 'apt-stat-active' : '' }}">
        <div class="apt-stat-icon" style="background:rgba(245,158,11,.1);color:#D97706"><i class="fa-solid fa-clock"></i></div>
        <div>
            <div class="apt-stat-val">{{ $pendingCount }}</div>
            <div class="apt-stat-label">Ready to Publish</div>
        </div>
    </a>
    <a href="{{ route('admin.publishing', ['filter' => 'overdue']) }}" class="apt-stat {{ request('filter') === 'overdue' ? 'apt-stat-active' : '' }}">
        <div class="apt-stat-icon" style="background:rgba(239,68,68,.1);color:#EF4444"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div>
            <div class="apt-stat-val" style="color:{{ ($overdueCount ?? 0) > 0 ? '#EF4444' : 'inherit' }}">{{ $overdueCount ?? 0 }}</div>
            <div class="apt-stat-label">Overdue to Post</div>
        </div>
    </a>
    <a href="{{ route('admin.publishing', ['filter' => 'client_yes']) }}" class="apt-stat {{ request('filter') === 'client_yes' ? 'apt-stat-active' : '' }}">
        <div class="apt-stat-icon" style="background:rgba(16,185,129,.12);color:#059669"><i class="fa-solid fa-check"></i></div>
        <div>
            <div class="apt-stat-val" style="color:#059669">{{ $clientApprovedCount ?? 0 }}</div>
            <div class="apt-stat-label">Client: Yes</div>
        </div>
    </a>
    <a href="{{ route('admin.publishing', ['filter' => 'client_no']) }}" class="apt-stat {{ request('filter') === 'client_no' ? 'apt-stat-active' : '' }}">
        <div class="apt-stat-icon" style="background:rgba(239,68,68,.1);color:#EF4444"><i class="fa-solid fa-xmark"></i></div>
        <div>
            <div class="apt-stat-val" style="color:#EF4444">{{ $clientPendingCount ?? 0 }}</div>
            <div class="apt-stat-label">Client: No</div>
        </div>
    </a>
    <a href="{{ route('admin.publishing', ['filter' => 'published']) }}" class="apt-stat {{ request('filter') === 'published' ? 'apt-stat-active' : '' }}">
        <div class="apt-stat-icon" style="background:rgba(16,185,129,.12);color:#059669"><i class="fa-solid fa-circle-check"></i></div>
        <div>
            <div class="apt-stat-val" style="color:#059669">{{ $publishedCount }}</div>
            <div class="apt-stat-label">Published Live</div>
        </div>
    </a>
</div>

{{-- Filter Bar --}}
<form class="apt-filter-bar" method="GET" action="{{ route('admin.publishing') }}" id="publishingFilterForm">
    <i class="fa-solid fa-filter"></i>
    <input type="text" name="search" id="publishingSearchInput" value="{{ request('search') }}" placeholder="Search by task or client..." autocomplete="off">
    <select name="filter" onchange="this.form.submit()">
        <option value="" {{ !request('filter') ? 'selected' : '' }}>All Status</option>
        <option value="pending" {{ in_array(request('filter'), ['pending', 'awaiting']) ? 'selected' : '' }}>Ready to Publish</option>
        <option value="overdue" {{ request('filter') === 'overdue' ? 'selected' : '' }}>Overdue to Post</option>
        <option value="client_yes" {{ request('filter') === 'client_yes' ? 'selected' : '' }}>Client Approved: Yes</option>
        <option value="client_no" {{ request('filter') === 'client_no' ? 'selected' : '' }}>Client Approved: No</option>
        <option value="published" {{ request('filter') === 'published' ? 'selected' : '' }}>Published Live</option>
    </select>
    <div class="apt-filter-sep"></div>
    <select name="client" onchange="this.form.submit()">
        <option value="">All Clients</option>
        @foreach($clients as $client)
            <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
        @endforeach
    </select>
    <select name="creator" onchange="this.form.submit()">
        <option value="">All Strategists</option>
        @foreach($strategists as $s)
            <option value="{{ $s->id }}" {{ request('creator') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="apt-btn apt-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    @if(request()->hasAny(['search', 'filter', 'client', 'creator']))
        <a href="{{ route('admin.publishing') }}" class="apt-btn apt-btn-ghost"><i class="fa-solid fa-xmark"></i> Clear</a>
    @endif
</form>

{{-- Table --}}
@if($tasks->count())
@php
    $platformIcons = [
        'instagram' => 'fa-brands fa-instagram',
        'facebook'  => 'fa-brands fa-facebook',
        'linkedin'  => 'fa-brands fa-linkedin',
        'twitter'   => 'fa-brands fa-x-twitter',
        'tiktok'    => 'fa-brands fa-tiktok',
        'youtube'   => 'fa-brands fa-youtube',
    ];
    $platformColors = [
        'instagram' => '#E1306C', 'facebook' => '#1877F2', 'linkedin' => '#0A66C2',
        'twitter' => '#1DA1F2', 'tiktok' => '#000', 'youtube' => '#FF0000',
    ];
    $avatarColors = ['#4F6DF0','#8B5CF6','#EC4899','#F59E0B','#10B981','#EF4444','#06B6D4','#F97316'];
@endphp

<div class="apt-table-wrap">
<table class="apt-table">
    <thead>
        <tr>
            <th style="width:36px;text-align:center">
                <input type="checkbox" id="pubSelectAllCheckbox" class="apt-check" onchange="togglePubSelectAll(this)">
            </th>
            <th>#</th>
            <th>Task</th>
            <th>Client</th>
            <th>Strategist</th>
            <th>Platforms</th>
            <th>Schedule</th>
            <th>Client Approval</th>
            <th>Status</th>
            <th>Completed</th>
            <th style="text-align:right">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tasks as $i => $task)
            @php
                $platforms = is_array($task->platform) ? $task->platform : [];
                $postedPlatforms = $task->socialMediaPosts->pluck('platform')->toArray();
                $rowNum = ($tasks->currentPage() - 1) * $tasks->perPage() + $i + 1;

                $postDateCarbon = $task->post_date ? \Illuminate\Support\Carbon::parse($task->post_date) : null;
                $isOverdue = $task->status !== 'published' && $postDateCarbon && $postDateCarbon->isPast() && !$postDateCarbon->isToday();
                $isToday = $task->status !== 'published' && $postDateCarbon && $postDateCarbon->isToday();

                if ($task->status === 'published') {
                    $statusLabel = 'Published';
                    $statusClass = 'apt-status-published';
                    $statusIcon = 'fa-check-double';
                } elseif ($isOverdue) {
                    $statusLabel = 'Due to Post';
                    $statusClass = 'apt-status-overdue';
                    $statusIcon = 'fa-triangle-exclamation';
                } elseif ($isToday) {
                    $statusLabel = 'Post Today';
                    $statusClass = 'apt-status-today';
                    $statusIcon = 'fa-clock';
                } else {
                    $statusLabel = 'Ready to Publish';
                    $statusClass = 'apt-status-awaiting';
                    $statusIcon = 'fa-hourglass-half';
                }

                $creatorColor = $avatarColors[($task->created_by ?? 0) % count($avatarColors)];
                $creatorInitials = $task->creator ? strtoupper(substr($task->creator->name, 0, 2)) : '—';
                $thumb = $task->getPreviewThumbnail();
                $hasThumb = !empty($thumb);
                $detailUrl = route('admin.publishing.show', $task);
                $isClientApproved = !is_null($task->client_approved_at);
            @endphp
            <tr id="pub-row-{{ $task->id }}">
                <td style="text-align:center">
                    <input type="checkbox" class="apt-check pub-task-check" value="{{ $task->id }}" data-status="{{ $task->status }}" onchange="handlePubCheck(this)">
                </td>
                <td><span style="font-size:11px;font-weight:700;color:var(--text3)">#{{ $task->id }}</span></td>
                <td>
                    <div class="apt-task-cell">
                        @if($hasThumb)
                            <div class="apt-thumb-box"
                                 onclick="window.openMediaLightbox({
                                     url: '{{ $thumb['full_url'] }}',
                                     isVideo: {{ $thumb['is_video'] ? 'true' : 'false' }},
                                     title: '{{ addslashes($task->title) }}',
                                     client: '{{ addslashes($task->client?->name ?? 'Client') }}',
                                     taskUrl: '{{ $detailUrl }}',
                                     downloadUrl: '{{ $thumb['full_url'] }}'
                                 })"
                                 title="Click to view full preview">
                                <img src="{{ $thumb['url'] }}" alt="{{ $task->title }}" loading="lazy" />
                                <div class="apt-thumb-overlay">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                </div>
                                @if($thumb['is_video'])
                                    <span class="apt-thumb-video-badge"><i class="fa-solid fa-play"></i></span>
                                @endif
                            </div>
                        @else
                            <div class="apt-thumb-box" onclick="openPublishingDrawer({{ $task->id }})" title="Click to inspect task">
                                <div class="apt-thumb-empty">
                                    <i class="fa-regular fa-image"></i>
                                </div>
                            </div>
                        @endif
                        <div class="apt-task-info">
                            <div class="apt-task-title" title="{{ $task->title }}" onclick="openPublishingDrawer({{ $task->id }})">
                                {{ Str::limit($task->title ?: 'Untitled', 40) }}
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;margin-top:2px">
                                @if($task->type)
                                    <span class="apt-task-type"><i class="fa-solid fa-tag"></i> {{ ucfirst($task->type) }}</span>
                                @endif
                                @if($task->caption)
                                    <button type="button" class="apt-btn-copy-inline" onclick="copyText('{{ addslashes($task->caption) }}')" title="Copy Caption">
                                        <i class="fa-regular fa-copy"></i> Copy
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="apt-person" style="font-size:12px;font-weight:600">{{ $task->client->name ?? '—' }}</span>
                </td>
                <td>
                    <div class="apt-person">
                        <div class="apt-avatar" style="background:{{ $creatorColor }}">{{ $creatorInitials }}</div>
                        {{ $task->creator->name ?? '—' }}
                    </div>
                </td>
                <td>
                    <div class="apt-platform-dots">
                        @foreach($platforms as $platform)
                            @php
                                $done = ($task->status === 'published') || in_array($platform, $postedPlatforms);
                                $pIcon = $platformIcons[$platform] ?? 'fa-solid fa-globe';
                                $pColor = $platformColors[$platform] ?? '#6B7280';
                            @endphp
                            <span class="apt-platform-dot {{ $done ? 'apt-platform-dot-done' : 'apt-platform-dot-pending' }}" title="{{ ucfirst($platform) }}: {{ $done ? 'Published' : 'Pending' }}">
                                <i class="{{ $pIcon }}" style="color:{{ $pColor }}"></i>
                            </span>
                        @endforeach
                    </div>
                </td>
                <td>
                    @if($postDateCarbon)
                        <div class="apt-date" style="{{ $isOverdue ? 'color:#DC2626;font-weight:700' : ($isToday ? 'color:var(--primary);font-weight:700' : '') }}">
                            <i class="fa-regular fa-calendar-check" style="margin-right:2px"></i> {{ $postDateCarbon->format('M d, Y') }}
                            @if($isOverdue)
                                <span class="apt-date-ago" style="color:#DC2626;font-weight:700">Overdue</span>
                            @elseif($isToday)
                                <span class="apt-date-ago" style="color:var(--primary);font-weight:700">Today</span>
                            @endif
                        </div>
                    @else
                        <span class="apt-date" style="opacity:.6">Unscheduled</span>
                    @endif
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px">
                        <span class="apt-badge {{ $isClientApproved ? 'apt-badge-client-yes' : 'apt-badge-client-no' }}" id="admin-client-badge-{{ $task->id }}">
                            <i class="fa-solid {{ $isClientApproved ? 'fa-check' : 'fa-xmark' }}"></i>
                            {{ $isClientApproved ? 'Yes' : 'No' }}
                        </span>
                        <button type="button"
                                class="apt-btn-toggle-client {{ $isClientApproved ? 'is-yes' : 'is-no' }}"
                                id="admin-client-toggle-btn-{{ $task->id }}"
                                onclick="toggleClientApproval({{ $task->id }}, this)">
                            {{ $isClientApproved ? 'Mark No' : 'Mark Yes' }}
                        </button>
                    </div>
                </td>
                <td>
                    <span class="apt-status-tag {{ $statusClass }}" id="pub-status-tag-{{ $task->id }}">
                        <i class="fa-solid {{ $statusIcon }} apt-status-icon"></i> {{ $statusLabel }}
                    </span>
                </td>
                <td>
                    <div class="apt-date">
                        {{ $task->completed_at ? $task->completed_at->format('M d, Y') : '—' }}
                    </div>
                </td>
                <td style="text-align:right">
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                        <div id="pub-action-btn-wrap-{{ $task->id }}">
                            @if($task->status === 'published')
                                <button type="button" class="apt-btn-revert" onclick="revertPublishAjax({{ $task->id }})" title="Revert publishing status to pending">
                                    <i class="fa-solid fa-rotate-left"></i> Revert
                                </button>
                            @else
                                <button type="button" class="apt-btn-publish" onclick="publishAjax({{ $task->id }}, {{ $isClientApproved ? 'true' : 'false' }})" title="1-Click Mark as Published">
                                    <i class="fa-solid fa-rocket"></i> Publish
                                </button>
                            @endif
                        </div>
                        <button type="button" class="apt-view-btn" onclick="openPublishingDrawer({{ $task->id }})" title="Quick Inspect">
                            <i class="fa-solid fa-sliders"></i>
                        </button>
                        <a href="{{ $detailUrl }}" class="apt-view-btn" title="Full Publishing Hub">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>

{{-- Pagination --}}
@if($tasks->hasPages())
    <div class="apt-pagination">
        @if($tasks->onFirstPage())
            <span class="apt-page-btn apt-page-disabled"><i class="fa-solid fa-chevron-left"></i></span>
        @else
            <a href="{{ $tasks->previousPageUrl() }}" class="apt-page-btn"><i class="fa-solid fa-chevron-left"></i></a>
        @endif

        @foreach($tasks->getUrlRange(1, $tasks->lastPage()) as $page => $url)
            @if($page == $tasks->currentPage())
                <span class="apt-page-btn apt-page-active">{{ $page }}</span>
            @elseif($page == 1 || $page == $tasks->lastPage() || abs($page - $tasks->currentPage()) <= 2)
                <a href="{{ $url }}" class="apt-page-btn">{{ $page }}</a>
            @elseif($page == 2 && $tasks->currentPage() > 4)
                <span class="apt-page-dots">...</span>
            @elseif($page == $tasks->lastPage() - 1 && $tasks->currentPage() < $tasks->lastPage() - 3)
                <span class="apt-page-dots">...</span>
            @endif
        @endforeach

        @if($tasks->hasMorePages())
            <a href="{{ $tasks->nextPageUrl() }}" class="apt-page-btn"><i class="fa-solid fa-chevron-right"></i></a>
        @else
            <span class="apt-page-btn apt-page-disabled"><i class="fa-solid fa-chevron-right"></i></span>
        @endif
    </div>
@endif

@else
    <div style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:60px 24px;text-align:center;">
        <div style="width:64px;height:64px;border-radius:18px;background:rgba(79,109,240,.08);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 16px">
            <i class="fa-regular fa-paper-plane"></i>
        </div>
        <div style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px">{{ request('filter') ? 'No matching tasks' : 'All clear!' }}</div>
        <div style="font-size:13px;color:var(--text3);max-width:380px;margin:0 auto">{{ request('filter') ? 'No tasks match this filter. Try adjusting your criteria.' : 'No approved tasks awaiting publishing yet.' }}</div>
    </div>
@endif

{{-- FLOATING BULK PUBLISH BAR --}}
<div class="apt-bulk-bar" id="pubBulkActionBar">
    <div style="font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px">
        <i class="fa-solid fa-check-double" style="color:var(--primary);font-size:16px"></i>
        <span id="pubBulkSelectedText">0 tasks selected</span>
    </div>
    <button type="button" class="apt-btn-publish" style="padding:9px 18px;border-radius:10px" onclick="submitPubBulkPublish()">
        <i class="fa-solid fa-rocket"></i> Publish Selected Tasks
    </button>
    <button type="button" class="btn-sec" style="padding:8px 12px;border-radius:10px;font-size:12px" onclick="clearPubSelections()">
        Cancel
    </button>
</div>

{{-- SLIDE-OVER PUBLISHING INSPECTOR DRAWER --}}
<div class="apt-drawer-backdrop" id="pubDrawerBackdrop" onclick="closePublishingDrawer()"></div>
<div class="apt-drawer" id="pubDrawer">
    <div class="apt-drawer-header">
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                <span class="tag tag-blue" id="pubDrawerType" style="font-size:10px">Post</span>
                <span class="apt-status-tag apt-status-awaiting" id="pubDrawerStatus" style="font-size:10px">Ready to Publish</span>
            </div>
            <h3 id="pubDrawerTitle" style="margin:0;font-size:16px;font-weight:800;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">Task Title</h3>
            <div id="pubDrawerClient" style="font-size:12px;color:var(--text3);margin-top:2px">Client Name</div>
        </div>
        <button type="button" class="btn-sec" style="padding:6px 10px;border-radius:8px" onclick="closePublishingDrawer()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="apt-drawer-body">
        {{-- Deliverables Preview --}}
        <div>
            <div style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">Creative Assets</div>
            <div id="pubDrawerMedia" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px"></div>
        </div>

        {{-- Copy Block with 1-Click Copy --}}
        <div style="background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:14px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <span style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px">Caption</span>
                <button type="button" class="btn-sec" style="padding:4px 8px;font-size:11px;border-radius:6px" onclick="copyDrawerElementText('pubDrawerCaption')">
                    <i class="fa-solid fa-copy"></i> Copy
                </button>
            </div>
            <div id="pubDrawerCaption" style="font-size:13px;color:var(--text);line-height:1.5;white-space:pre-wrap;max-height:130px;overflow-y:auto">No caption written.</div>
        </div>

        <div style="background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:14px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <span style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px">Hashtags</span>
                <button type="button" class="btn-sec" style="padding:4px 8px;font-size:11px;border-radius:6px" onclick="copyDrawerElementText('pubDrawerHashtags')">
                    <i class="fa-solid fa-copy"></i> Copy
                </button>
            </div>
            <div id="pubDrawerHashtags" style="font-size:12.5px;color:var(--primary);line-height:1.4;white-space:pre-wrap">No hashtags specified.</div>
        </div>

        {{-- Platforms List --}}
        <div>
            <div style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">Platform Distribution</div>
            <div id="pubDrawerPlatforms" style="display:flex;flex-direction:column;gap:8px"></div>
        </div>
    </div>

    <div class="apt-drawer-footer">
        <a id="pubDrawerFullLink" href="#" class="btn-sec" style="font-size:12px;padding:8px 12px;border-radius:9px">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Full Publishing Hub
        </a>
        <div id="pubDrawerActionSlot"></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    // Copy text utility
    window.copyText = (text) => {
        navigator.clipboard.writeText(text).then(() => {
            if (window.toastSuccess) window.toastSuccess('Copied to clipboard!');
            else alert('Copied to clipboard!');
        });
    };

    window.copyDrawerElementText = (id) => {
        window.copyText(document.getElementById(id).textContent);
    };

    // Checkboxes & Bulk Selection
    window.handlePubCheck = () => {
        const checkedBoxes = document.querySelectorAll('.pub-task-check:checked');
        const count = checkedBoxes.length;
        const total = document.querySelectorAll('.pub-task-check').length;

        document.getElementById('pubBulkSelectedText').textContent = `${count} task${count !== 1 ? 's' : ''} selected`;

        const master = document.getElementById('pubSelectAllCheckbox');
        if (master) {
            master.checked = count === total && total > 0;
            master.indeterminate = count > 0 && count < total;
        }

        const bulkBar = document.getElementById('pubBulkActionBar');
        if (count > 0) {
            bulkBar.classList.add('visible');
        } else {
            bulkBar.classList.remove('visible');
        }

        document.querySelectorAll('#pub-row-' + taskId => {});
    };

    window.togglePubSelectAll = (master) => {
        document.querySelectorAll('.pub-task-check').forEach(cb => {
            cb.checked = master.checked;
        });
        window.handlePubCheck();
    };

    window.clearPubSelections = () => {
        document.querySelectorAll('.pub-task-check').forEach(cb => cb.checked = false);
        window.handlePubCheck();
    };

    window.submitPubBulkPublish = () => {
        const checked = Array.from(document.querySelectorAll('.pub-task-check:checked')).map(cb => parseInt(cb.value));
        if (checked.length === 0) return;

        if (!confirm(`Mark ${checked.length} selected task(s) as Published on social media?`)) return;

        fetch('{{ route('admin.publishing.bulk-publish') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ task_ids: checked })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (window.toastSuccess) window.toastSuccess(data.message);
                else alert(data.message);
                setTimeout(() => window.location.reload(), 600);
            } else {
                alert(data.message || 'Bulk publish failed');
            }
        })
        .catch(err => {
            console.error('Bulk publish error:', err);
            alert('Failed to publish selected tasks.');
        });
    };

    // 1-Click AJAX Publish
    window.publishAjax = (taskId, isClientApproved) => {
        if (!isClientApproved) {
            if (!confirm('⚠️ Note: Client approval is currently marked as NO.\n\nAre you sure you want to mark this task as Published?')) return;
        }

        fetch(`/admin/publishing/${taskId}/publish`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tag = document.getElementById(`pub-status-tag-${taskId}`);
                if (tag) {
                    tag.className = 'apt-status-tag apt-status-published';
                    tag.innerHTML = '<i class="fa-solid fa-check-double apt-status-icon"></i> Published';
                }
                const btnWrap = document.getElementById(`pub-action-btn-wrap-${taskId}`);
                if (btnWrap) {
                    btnWrap.innerHTML = `
                        <button type="button" class="apt-btn-revert" onclick="revertPublishAjax(${taskId})" title="Revert publishing status to pending">
                            <i class="fa-solid fa-rotate-left"></i> Revert
                        </button>
                    `;
                }
                if (window.toastSuccess) window.toastSuccess(data.message);
                else alert(data.message);
                if (document.getElementById('pubDrawer').classList.contains('open')) {
                    window.openPublishingDrawer(taskId);
                }
            } else {
                alert(data.message || 'Publishing failed');
            }
        })
        .catch(err => {
            console.error('Publish error:', err);
            alert('Publishing request failed.');
        });
    };

    // 1-Click AJAX Revert
    window.revertPublishAjax = (taskId) => {
        if (!confirm('Revert publishing status back to Ready to Publish?')) return;

        fetch(`/admin/publishing/${taskId}/unpublish`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const tag = document.getElementById(`pub-status-tag-${taskId}`);
                if (tag) {
                    tag.className = 'apt-status-tag apt-status-awaiting';
                    tag.innerHTML = '<i class="fa-solid fa-hourglass-half apt-status-icon"></i> Ready to Publish';
                }
                const btnWrap = document.getElementById(`pub-action-btn-wrap-${taskId}`);
                if (btnWrap) {
                    btnWrap.innerHTML = `
                        <button type="button" class="apt-btn-publish" onclick="publishAjax(${taskId}, true)" title="1-Click Mark as Published">
                            <i class="fa-solid fa-rocket"></i> Publish
                        </button>
                    `;
                }
                if (window.toastSuccess) window.toastSuccess(data.message);
                else alert(data.message);
                if (document.getElementById('pubDrawer').classList.contains('open')) {
                    window.openPublishingDrawer(taskId);
                }
            } else {
                alert(data.message || 'Revert failed');
            }
        })
        .catch(err => {
            console.error('Revert error:', err);
            alert('Revert request failed.');
        });
    };

    // Slide-Over Publishing Inspector Drawer
    window.openPublishingDrawer = (taskId) => {
        const drawer = document.getElementById('pubDrawer');
        const backdrop = document.getElementById('pubDrawerBackdrop');

        drawer.classList.add('open');
        backdrop.classList.add('open');
        document.getElementById('pubDrawerTitle').textContent = 'Loading task details…';

        fetch(`/admin/tasks/${taskId}/quick-inspect`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const task = data.task;

            document.getElementById('pubDrawerTitle').textContent = task.title;
            document.getElementById('pubDrawerType').textContent = task.type || 'Post';
            document.getElementById('pubDrawerClient').textContent = task.client?.name || 'Client';

            const statusTag = document.getElementById('pubDrawerStatus');
            if (task.status === 'published') {
                statusTag.className = 'apt-status-tag apt-status-published';
                statusTag.innerHTML = '<i class="fa-solid fa-check-double apt-status-icon"></i> Published';
            } else {
                statusTag.className = 'apt-status-tag apt-status-awaiting';
                statusTag.innerHTML = '<i class="fa-solid fa-hourglass-half apt-status-icon"></i> Ready to Publish';
            }

            // Media
            const mediaBox = document.getElementById('pubDrawerMedia');
            mediaBox.innerHTML = '';
            if (task.media && task.media.length > 0) {
                task.media.forEach(m => {
                    const item = document.createElement('div');
                    item.className = 'apt-thumb-box';
                    item.style.width = '100%';
                    item.style.height = '120px';
                    item.onclick = () => window.openMediaLightbox({
                        url: m.url,
                        isVideo: m.is_video,
                        title: task.title,
                        client: task.client?.name,
                        taskUrl: `/admin/publishing/${task.id}`,
                        downloadUrl: m.url
                    });

                    if (m.is_video) {
                        item.innerHTML = `
                            <video src="${m.url}" style="width:100%;height:100%;object-fit:cover"></video>
                            <span class="apt-thumb-video-badge"><i class="fa-solid fa-play"></i></span>
                            <div class="apt-thumb-overlay"><i class="fa-solid fa-expand"></i></div>
                        `;
                    } else {
                        item.innerHTML = `
                            <img src="${m.url}" style="width:100%;height:100%;object-fit:cover" alt="${m.name}">
                            <div class="apt-thumb-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                        `;
                    }
                    mediaBox.appendChild(item);
                });
            } else {
                mediaBox.innerHTML = '<div style="grid-column:1/-1;padding:16px;text-align:center;color:var(--text3);font-size:12px;background:var(--card2);border-radius:8px">No creative assets attached.</div>';
            }

            // Copy
            document.getElementById('pubDrawerCaption').textContent = task.caption || task.brief || 'No caption provided.';
            document.getElementById('pubDrawerHashtags').textContent = task.hashtags || 'No hashtags provided.';

            // Platforms
            const platBox = document.getElementById('pubDrawerPlatforms');
            platBox.innerHTML = '';
            const platforms = task.platform || [];
            if (platforms.length > 0) {
                platforms.forEach(p => {
                    const pItem = document.createElement('div');
                    pItem.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--card2);border:1px solid var(--border);border-radius:10px;font-size:12.5px;font-weight:600';
                    pItem.innerHTML = `
                        <div style="display:flex;align-items:center;gap:8px">
                            <i class="fa-brands fa-${p === 'twitter' ? 'x-twitter' : p}" style="font-size:16px;color:var(--primary)"></i>
                            <span style="text-transform:capitalize">${p}</span>
                        </div>
                        <span class="tag ${task.status === 'published' ? 'tag-green' : 'tag-amber'}" style="font-size:10px">
                            ${task.status === 'published' ? 'Live' : 'Pending Proof'}
                        </span>
                    `;
                    platBox.appendChild(pItem);
                });
            } else {
                platBox.innerHTML = '<div style="padding:10px;color:var(--text3);font-size:12px">No platforms assigned.</div>';
            }

            // Links & Actions
            document.getElementById('pubDrawerFullLink').href = `/admin/publishing/${task.id}`;
            const actionSlot = document.getElementById('pubDrawerActionSlot');
            if (task.status === 'published') {
                actionSlot.innerHTML = `
                    <button type="button" class="apt-btn-revert" style="padding:8px 14px" onclick="revertPublishAjax(${task.id})">
                        <i class="fa-solid fa-rotate-left"></i> Revert to Pending
                    </button>
                `;
            } else {
                actionSlot.innerHTML = `
                    <button type="button" class="apt-btn-publish" style="padding:8px 18px" onclick="publishAjax(${task.id}, ${task.is_client_approved ? 'true' : 'false'})">
                        <i class="fa-solid fa-rocket"></i> Mark as Published
                    </button>
                `;
            }
        })
        .catch(err => {
            console.error('Drawer inspect error:', err);
            document.getElementById('pubDrawerTitle').textContent = 'Error loading details';
        });
    };

    window.closePublishingDrawer = () => {
        document.getElementById('pubDrawer').classList.remove('open');
        document.getElementById('pubDrawerBackdrop').classList.remove('open');
    };
})();

// Client Approval Toggle
async function toggleClientApproval(taskId, btn) {
    if (btn.disabled) return;
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        const response = await fetch(`/tasks/${taskId}/toggle-client-approval`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const data = await response.json();
        if (data.success) {
            const badge = document.getElementById(`admin-client-badge-${taskId}`);
            if (badge) {
                if (data.is_approved) {
                    badge.className = 'apt-badge apt-badge-client-yes';
                    badge.innerHTML = '<i class="fa-solid fa-check"></i> Yes';
                    btn.className = 'apt-btn-toggle-client is-yes';
                    btn.innerHTML = 'Mark No';
                } else {
                    badge.className = 'apt-badge apt-badge-client-no';
                    badge.innerHTML = '<i class="fa-solid fa-xmark"></i> No';
                    btn.className = 'apt-btn-toggle-client is-no';
                    btn.innerHTML = 'Mark Yes';
                }
            }
            if (window.toastSuccess) window.toastSuccess(data.message || 'Client approval updated');
        } else {
            alert(data.message || 'Failed to update client approval');
            btn.innerHTML = originalText;
        }
    } catch (e) {
        console.error(e);
        btn.innerHTML = originalText;
    } finally {
        btn.disabled = false;
    }
}
</script>
@endpush
