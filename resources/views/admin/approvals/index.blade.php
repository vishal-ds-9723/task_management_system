@extends('layouts.app')

@push('styles')
<style>
/* ── Admin Approvals Premium UI ────────────────── */
.aap-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:16px; }
.aap-header-left { display:flex; align-items:center; gap:14px; }
.aap-icon-wrap { width:46px; height:46px; border-radius:14px; background:linear-gradient(135deg, var(--primary), #818cf8); display:flex; align-items:center; justify-content:center; color:#fff; font-size:19px; box-shadow:0 4px 14px rgba(79,70,229,0.28); }
.aap-title { font-size:22px; font-weight:800; color:var(--text); letter-spacing:-0.3px; font-family:'Plus Jakarta Sans',sans-serif; margin:0; }
.aap-subtitle { font-size:12.5px; color:var(--text3); margin-top:2px; font-weight:500; }
.aap-header-right { display:flex; gap:10px; align-items:center; }
.aap-count-pill { font-size:12px; color:var(--text2); font-weight:600; background:var(--card); border:1px solid var(--border); padding:6px 14px; border-radius:10px; display:flex; align-items:center; gap:6px; box-shadow:var(--shadow-sm); }
.aap-count-pill strong { color:var(--primary); font-weight:800; }

/* Stats Bar */
.aap-stats { display:grid; grid-template-columns:repeat(4, 1fr); gap:14px; margin-bottom:20px; }
.aap-stat-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:16px; display:flex; align-items:center; gap:14px; transition:all 0.2s ease; box-shadow:var(--shadow-sm); text-decoration:none; color:inherit; }
.aap-stat-card:hover { border-color:var(--primary); transform:translateY(-2px); box-shadow:var(--shadow); }
.aap-stat-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.aap-stat-val { font-size:22px; font-weight:800; color:var(--text); line-height:1; }
.aap-stat-lbl { font-size:11px; color:var(--text3); font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-top:4px; }

/* Filter Card */
.aap-filter-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:16px 20px; margin-bottom:20px; box-shadow:var(--shadow-sm); }
.aap-filter-form { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.aap-search-wrap { flex:2; min-width:240px; position:relative; }
.aap-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text3); pointer-events:none; }
.aap-search-input { width:100%; padding:10px 14px 10px 36px; border:1px solid var(--border); border-radius:10px; background:var(--card2); font-size:13px; color:var(--text); font-family:inherit; transition:border-color 0.2s, box-shadow 0.2s; outline:none; }
.aap-search-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(79,70,229,0.1); background:var(--card); }
.aap-select { height:40px; border:1px solid var(--border); border-radius:10px; padding:0 12px; font-size:12.5px; background:var(--card2); color:var(--text); outline:none; cursor:pointer; font-family:inherit; transition:border-color 0.2s; }
.aap-select:focus { border-color:var(--primary); }

.aap-pill-toggle { padding:9px 14px; border-radius:10px; border:1px solid var(--border); background:var(--card2); color:var(--text2); font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s; user-select:none; }
.aap-pill-toggle:hover { border-color:var(--primary); color:var(--primary); }
.aap-pill-toggle.active { background:rgba(79,70,229,0.12); color:var(--primary); border-color:var(--primary); }
.aap-pill-toggle.urgent.active { background:rgba(239,68,68,0.12); color:#dc2626; border-color:#dc2626; }

.aap-filter-btn { padding:10px 18px; border-radius:10px; background:var(--primary); color:#fff; border:none; font-size:12.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s; }
.aap-filter-btn:hover { background:var(--primary-hover); box-shadow:0 2px 10px rgba(79,70,229,0.25); }
.aap-clear-btn { padding:10px 14px; border-radius:10px; background:var(--card2); color:var(--text3); border:1px solid var(--border); text-decoration:none; font-size:12.5px; font-weight:600; display:inline-flex; align-items:center; gap:5px; transition:all 0.2s; }
.aap-clear-btn:hover { color:var(--text); border-color:var(--border2); }

/* Selection Control Bar */
.aap-select-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; padding:0 6px; font-size:12.5px; color:var(--text3); }
.aap-select-all-label { display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; color:var(--text2); user-select:none; }
.aap-checkbox { width:17px; height:17px; border-radius:5px; border:1.5px solid var(--border); accent-color:var(--primary); cursor:pointer; }

/* Task Card List */
.aap-list { display:flex; flex-direction:column; gap:12px; }
.aap-card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:16px 20px; display:flex; align-items:center; gap:16px; color:inherit; transition:all 0.25s cubic-bezier(0.16,1,0.3,1); box-shadow:var(--shadow-sm); position:relative; overflow:hidden; }
.aap-card:hover { border-color:var(--primary); box-shadow:0 8px 24px rgba(79,70,229,0.08); transform:translateY(-1px); }
.aap-card.selected { border-color:var(--primary); background:rgba(79,70,229,0.02); }

/* Left Checkbox */
.aap-card-check-wrap { display:flex; align-items:center; justify-content:center; flex-shrink:0; }

/* Thumbnail */
.aap-thumb-container { width:68px; height:68px; border-radius:14px; overflow:hidden; position:relative; flex-shrink:0; background:var(--card2); border:1px solid var(--border); cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
.aap-thumb-container:hover { border-color:var(--primary); box-shadow:0 4px 14px rgba(79,70,229,0.22); }
.aap-thumb-img { width:100%; height:100%; object-fit:cover; display:block; transition:transform 0.3s ease; }
.aap-thumb-container:hover .aap-thumb-img { transform:scale(1.08); }
.aap-thumb-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.65) 0%, rgba(0,0,0,0.2) 60%, transparent 100%); display:flex; align-items:center; justify-content:center; opacity:0; transition:opacity 0.2s ease; color:#fff; font-size:16px; }
.aap-thumb-container:hover .aap-thumb-overlay { opacity:1; }
.aap-thumb-badge-video { position:absolute; top:4px; right:4px; width:18px; height:18px; border-radius:50%; background:rgba(0,0,0,0.7); color:#fff; display:flex; align-items:center; justify-content:center; font-size:8px; }
.aap-thumb-count { position:absolute; bottom:4px; right:4px; background:rgba(0,0,0,0.75); color:#fff; font-size:9.5px; font-weight:800; padding:1px 5px; border-radius:6px; }
.aap-thumb-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px; height:100%; width:100%; background:linear-gradient(135deg, rgba(79,70,229,0.04), rgba(129,140,248,0.04)); color:var(--text3); font-size:18px; }
.aap-thumb-empty span { font-size:8.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; }

/* Middle: Info */
.aap-card-body { flex:1; min-width:0; display:flex; flex-direction:column; gap:6px; cursor:pointer; }
.aap-tags-row { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.aap-tag { font-size:10.5px; font-weight:700; padding:3px 9px; border-radius:6px; display:inline-flex; align-items:center; gap:4px; letter-spacing:0.2px; text-transform:uppercase; }
.aap-tag-status { background:rgba(129,140,248,0.15); color:#4f46e5; border:1px solid rgba(129,140,248,0.25); }
.aap-tag-urgent { background:rgba(239,68,68,0.12); color:#dc2626; border:1px solid rgba(239,68,68,0.25); font-weight:800; animation:urgent-pulse 2s infinite; }
.aap-tag-high { background:rgba(249,115,22,0.12); color:#ea580c; border:1px solid rgba(249,115,22,0.25); }
.aap-tag-media { background:rgba(16,185,129,0.12); color:#059669; border:1px solid rgba(16,185,129,0.2); }
.aap-tag-nomedia { background:rgba(156,163,175,0.12); color:#6b7280; border:1px solid rgba(156,163,175,0.2); }

.aap-card-title { font-size:14.5px; font-weight:700; color:var(--text); margin:0; line-height:1.35; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.aap-meta-row { display:flex; align-items:center; gap:16px; font-size:12px; color:var(--text3); flex-wrap:wrap; }
.aap-meta-item { display:inline-flex; align-items:center; gap:6px; }
.aap-meta-item strong { color:var(--text2); font-weight:600; }
.aap-avatar-mini { width:20px; height:20px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; font-size:9.5px; font-weight:800; color:#fff; text-transform:uppercase; }

/* Right: Action Buttons */
.aap-card-actions { display:flex; align-items:center; gap:8px; flex-shrink:0; }
.aap-btn-inspect { padding:8px 12px; border-radius:9px; background:var(--card2); color:var(--text2); border:1px solid var(--border); font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all 0.2s; }
.aap-btn-inspect:hover { border-color:var(--primary); color:var(--primary); background:var(--card); }
.aap-btn-approve-inline { padding:8px 14px; border-radius:9px; background:linear-gradient(135deg, #10b981, #059669); color:#fff; font-size:12px; font-weight:700; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s; box-shadow:0 2px 6px rgba(16,185,129,0.2); }
.aap-btn-approve-inline:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(16,185,129,0.3); filter:brightness(1.05); }
.aap-btn-reject-inline { padding:8px 12px; border-radius:9px; background:rgba(239,68,68,0.08); color:#dc2626; border:1px solid rgba(239,68,68,0.25); font-size:12px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all 0.2s; }
.aap-btn-reject-inline:hover { background:rgba(239,68,68,0.15); }

/* Floating Bulk Bar */
.aap-bulk-bar { position:fixed; bottom:28px; left:50%; transform:translateX(-50%) translateY(100px); background:var(--card); border:1px solid var(--border); border-radius:16px; padding:12px 24px; display:flex; align-items:center; gap:18px; box-shadow:0 12px 36px rgba(0,0,0,0.18); z-index:900; transition:transform 0.3s cubic-bezier(0.16,1,0.3,1); }
.aap-bulk-bar.visible { transform:translateX(-50%) translateY(0); }
.aap-bulk-info { font-size:13px; font-weight:700; color:var(--text); display:flex; align-items:center; gap:8px; }
.aap-bulk-btn-approve { padding:8px 18px; border-radius:10px; background:linear-gradient(135deg, #10b981, #059669); color:#fff; font-size:12.5px; font-weight:700; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s; }
.aap-bulk-btn-approve:hover { filter:brightness(1.06); transform:translateY(-1px); }

/* Slide-Over Drawer */
.aap-drawer-backdrop { position:fixed; inset:0; background:rgba(15,23,42,0.5); backdrop-filter:blur(3px); z-index:1000; opacity:0; pointer-events:none; transition:opacity 0.25s ease; }
.aap-drawer-backdrop.open { opacity:1; pointer-events:auto; }
.aap-drawer { position:fixed; top:0; right:0; bottom:0; width:540px; max-width:92vw; background:var(--card); border-left:1px solid var(--border); box-shadow:-8px 0 32px rgba(0,0,0,0.16); z-index:1001; transform:translateX(100%); transition:transform 0.3s cubic-bezier(0.16,1,0.3,1); display:flex; flex-direction:column; }
.aap-drawer.open { transform:translateX(0); }
.aap-drawer-header { padding:18px 22px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; justify-content:space-between; gap:12px; background:var(--card2); }
.aap-drawer-body { flex:1; overflow-y:auto; padding:22px; display:flex; flex-direction:column; gap:18px; }
.aap-drawer-footer { padding:16px 22px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; background:var(--card2); }

/* Revision Modal */
.aap-modal-backdrop { position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(3px); z-index:1100; display:flex; align-items:center; justify-content:center; padding:16px; opacity:0; pointer-events:none; transition:opacity 0.2s ease; }
.aap-modal-backdrop.open { opacity:1; pointer-events:auto; }
.aap-modal { width:480px; max-width:100%; background:var(--card); border:1px solid var(--border); border-radius:18px; box-shadow:0 16px 40px rgba(0,0,0,0.2); overflow:hidden; }
.aap-modal-header { padding:18px 22px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; background:var(--card2); }
.aap-modal-body { padding:22px; }
.aap-modal-footer { padding:16px 22px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:10px; background:var(--card2); }
.aap-chip-group { display:flex; flex-wrap:wrap; gap:6px; margin:10px 0 16px; }
.aap-chip { padding:5px 11px; border-radius:8px; border:1px solid var(--border); background:var(--card2); font-size:11.5px; font-weight:600; color:var(--text2); cursor:pointer; transition:all 0.15s; }
.aap-chip:hover { border-color:var(--primary); color:var(--primary); }

@keyframes urgent-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
    50% { box-shadow: 0 0 0 6px rgba(239,68,68,0); }
}

@media(max-width:900px) {
    .aap-stats { grid-template-columns:repeat(2,1fr); }
    .aap-card { flex-direction:column; align-items:flex-start; }
    .aap-card-actions { width:100%; justify-content:flex-end; }
}
@media(max-width:600px) {
    .aap-stats { grid-template-columns:1fr; }
    .aap-drawer { width:100vw; }
}
</style>
@endpush

@section('content')
<div class="topbar">
    <div>
        <div class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="breadcrumb-sep">›</span>
            <span>Task Approvals</span>
        </div>
        <div class="page-title"><i class="fa-solid fa-clipboard-check" style="margin-right:8px;color:var(--primary)"></i> Task Approvals</div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <a href="{{ route('admin.tasks') }}" class="btn-sec"><i class="fa-solid fa-list-check"></i> All Tasks</a>
        <a href="{{ route('admin.dashboard') }}" class="btn-sec">← Dashboard</a>
    </div>
</div>

{{-- MAIN CONTENT --}}
<div class="layout-main" style="margin-top:16px">

    {{-- Stats Counters (Global accurate numbers) --}}
    <div class="aap-stats">
        <a href="{{ route('admin.approvals') }}" class="aap-stat-card">
            <div class="aap-stat-icon" style="background:rgba(79,70,229,0.1);color:#4f46e5"><i class="fa-solid fa-hourglass-half"></i></div>
            <div>
                <div class="aap-stat-val">{{ number_format($stats['totalPending']) }}</div>
                <div class="aap-stat-lbl">Pending Review</div>
            </div>
        </a>
        <a href="{{ route('admin.approvals', ['urgent_only' => 'yes']) }}" class="aap-stat-card">
            <div class="aap-stat-icon" style="background:rgba(239,68,68,0.1);color:#ef4444"><i class="fa-solid fa-bolt"></i></div>
            <div>
                <div class="aap-stat-val" style="color:{{ $stats['urgentCount'] > 0 ? '#ef4444' : 'inherit' }}">{{ number_format($stats['urgentCount']) }}</div>
                <div class="aap-stat-lbl">Urgent Submissions</div>
            </div>
        </a>
        <a href="{{ route('admin.approvals', ['has_media' => 'yes']) }}" class="aap-stat-card">
            <div class="aap-stat-icon" style="background:rgba(16,185,129,0.1);color:#10b981"><i class="fa-solid fa-photo-film"></i></div>
            <div>
                <div class="aap-stat-val">{{ number_format($stats['withMediaCount']) }}</div>
                <div class="aap-stat-lbl">Artwork Attached</div>
            </div>
        </a>
        <a href="{{ route('admin.approvals', ['has_media' => 'no']) }}" class="aap-stat-card">
            <div class="aap-stat-icon" style="background:rgba(245,158,11,0.1);color:#f59e0b"><i class="fa-solid fa-file-circle-question"></i></div>
            <div>
                <div class="aap-stat-val">{{ number_format($stats['awaitingFilesCount']) }}</div>
                <div class="aap-stat-lbl">Awaiting Files</div>
            </div>
        </a>
    </div>

    {{-- FILTERS MATRIX --}}
    <div class="aap-filter-card">
        <form method="GET" action="{{ route('admin.approvals') }}" id="approvalsFilterForm" class="aap-filter-form">
            <div class="aap-search-wrap">
                <i class="fa-solid fa-magnifying-glass aap-search-icon"></i>
                <input type="text" name="search" id="approvalsSearchInput" value="{{ request('search') }}" placeholder="Search by task title, client, or designer…" autocomplete="off" class="aap-search-input">
            </div>

            <select name="client_id" class="aap-select" onchange="this.form.submit()">
                <option value="">All Clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>

            <select name="assignee_id" class="aap-select" onchange="this.form.submit()">
                <option value="">All Designers</option>
                @foreach($designers as $d)
                    <option value="{{ $d->id }}" {{ request('assignee_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>

            <input type="hidden" name="urgent_only" id="filterUrgentInput" value="{{ request('urgent_only') }}">
            <button type="button" class="aap-pill-toggle urgent {{ request('urgent_only') === 'yes' ? 'active' : '' }}" onclick="toggleUrgentFilter()">
                <i class="fa-solid fa-bolt"></i> Urgent Only
            </button>

            <input type="hidden" name="has_media" id="filterMediaInput" value="{{ request('has_media') }}">
            <button type="button" class="aap-pill-toggle {{ request('has_media') === 'yes' ? 'active' : '' }}" onclick="toggleMediaFilter()">
                <i class="fa-solid fa-image"></i> With Artwork
            </button>

            <button type="submit" class="aap-filter-btn">
                <i class="fa-solid fa-filter"></i> Apply
            </button>
            @if(request()->anyFilled(['search', 'client_id', 'assignee_id', 'urgent_only', 'has_media']))
                <a href="{{ route('admin.approvals') }}" class="aap-clear-btn"><i class="fa-solid fa-xmark"></i> Reset</a>
            @endif
        </form>
    </div>

    {{-- BATCH CONTROLS --}}
    @if(!$tasks->isEmpty())
        <div class="aap-select-bar">
            <label class="aap-select-all-label">
                <input type="checkbox" id="selectAllCheckbox" class="aap-checkbox" onchange="toggleSelectAll(this)">
                <span>Select All on this page</span>
            </label>
            <span id="selectedCountText">0 tasks selected</span>
        </div>
    @endif

    <div id="approvalsResultsContainer">
        {{-- NO TASKS MESSAGE --}}
        @if($tasks->isEmpty())
            <div class="aap-empty" style="background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:60px 24px;text-align:center;">
                <div style="width:64px;height:64px;border-radius:18px;background:rgba(79,70,229,0.08);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 16px;">
                    <i class="fa-solid fa-sparkles"></i>
                </div>
                <div style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px;">No tasks pending approval</div>
                <div style="font-size:13px;color:var(--text3);max-width:380px;margin:0 auto;">All submitted deliverables have been reviewed. When designers submit deliverables, they will appear here.</div>
            </div>
        @else
            {{-- TASKS LIST --}}
            <div class="aap-list" id="approvalsTaskList">
                @foreach($tasks as $task)
                    @php
                        $thumb = $task->getPreviewThumbnail();
                        $hasMedia = !empty($thumb);
                        $typeClass = $task->type_tag_class ?? 'tag-blue';
                        $priority = strtolower((string) $task->priority);
                        $avatarColor = $task->assignee?->avatar_color ?: '#4f46e5';
                        $assigneeInitial = $task->assignee ? strtoupper(substr($task->assignee->name, 0, 1)) : '?';
                        $reviewUrl = route('admin.approvals.show', $task);
                    @endphp
                    <div class="aap-card" id="task-card-{{ $task->id }}" data-task-id="{{ $task->id }}">
                        {{-- SELECTION CHECKBOX --}}
                        <div class="aap-card-check-wrap">
                            <input type="checkbox" class="aap-checkbox task-check" value="{{ $task->id }}" onchange="handleTaskCheck(this)">
                        </div>

                        {{-- THUMBNAIL BOX --}}
                        @if($hasMedia)
                            <div class="aap-thumb-container"
                                 onclick="window.openMediaLightbox({
                                     url: '{{ $thumb['full_url'] }}',
                                     isVideo: {{ $thumb['is_video'] ? 'true' : 'false' }},
                                     title: '{{ addslashes($task->title) }}',
                                     client: '{{ addslashes($task->client?->name ?? 'Client') }}',
                                     taskUrl: '{{ $reviewUrl }}',
                                     downloadUrl: '{{ $thumb['full_url'] }}'
                                 })"
                                 title="Click to view full preview">
                                <img src="{{ $thumb['url'] }}" class="aap-thumb-img" alt="{{ $task->title }}" loading="lazy">
                                <div class="aap-thumb-overlay">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                </div>
                                @if($thumb['is_video'])
                                    <span class="aap-thumb-badge-video"><i class="fa-solid fa-play"></i></span>
                                @endif
                                @if(($thumb['count'] ?? 1) > 1)
                                    <span class="aap-thumb-count">+{{ $thumb['count'] - 1 }}</span>
                                @endif
                            </div>
                        @else
                            <div class="aap-thumb-container" onclick="openApprovalDrawer({{ $task->id }})" title="No media uploaded yet — click to inspect">
                                <div class="aap-thumb-empty">
                                    <i class="fa-solid fa-file-circle-exclamation"></i>
                                    <span>No Media</span>
                                </div>
                            </div>
                        @endif

                        {{-- MIDDLE: Content & Meta --}}
                        <div class="aap-card-body" onclick="openApprovalDrawer({{ $task->id }})">
                            {{-- Badges Row --}}
                            <div class="aap-tags-row">
                                <span class="aap-tag aap-tag-status">
                                    <i class="fa-solid fa-hourglass-half" style="font-size:9px"></i> Pending Review
                                </span>
                                @if($task->is_urgent_task || $priority === 'urgent')
                                    <span class="aap-tag aap-tag-urgent">
                                        <i class="fa-solid fa-bolt" style="font-size:9px"></i> Urgent Designer
                                    </span>
                                @elseif($priority === 'high')
                                    <span class="aap-tag aap-tag-high">
                                        <i class="fa-solid fa-arrow-trend-up" style="font-size:9px"></i> High Priority
                                    </span>
                                @endif
                                @if($task->type)
                                    <span class="tag {{ $typeClass }}" style="font-size:10px;padding:2px 7px">
                                        {{ ucfirst($task->type) }}
                                    </span>
                                @endif
                                @if($hasMedia)
                                    <span class="aap-tag aap-tag-media">
                                        <i class="fa-solid fa-image" style="font-size:9px"></i> {{ $thumb['count'] ?? 1 }} {{ ($thumb['count'] ?? 1) > 1 ? 'Files' : 'File' }}
                                    </span>
                                @else
                                    <span class="aap-tag aap-tag-nomedia">
                                        <i class="fa-solid fa-circle-exclamation" style="font-size:9px"></i> No Media
                                    </span>
                                @endif
                                @if($task->revision_count > 0)
                                    <span class="tag tag-amber" style="font-size:10px;padding:2px 7px">
                                        Rev #{{ $task->revision_count }}
                                    </span>
                                @endif
                            </div>

                            {{-- Title --}}
                            <h4 class="aap-card-title" title="{{ $task->title }}">
                                {{ $task->title ?: 'Untitled Task' }}
                            </h4>

                            {{-- Meta Row --}}
                            <div class="aap-meta-row">
                                <span class="aap-meta-item">
                                    <i class="fa-solid fa-building" style="color:var(--text3)"></i>
                                    <strong>Client:</strong> {{ $task->client?->name ?? 'N/A' }}
                                </span>
                                <span class="aap-meta-item">
                                    <span class="aap-avatar-mini" style="background:{{ $avatarColor }}">{{ $assigneeInitial }}</span>
                                    <strong>Designer:</strong> {{ $task->assignee?->name ?? 'Unassigned' }}
                                </span>
                                <span class="aap-meta-item">
                                    <i class="fa-regular fa-clock" style="color:var(--text3)"></i>
                                    <strong>Submitted:</strong> {{ $task->updated_at ? $task->updated_at->diffForHumans() : 'Recently' }}
                                </span>
                            </div>
                        </div>

                        {{-- RIGHT: Actions --}}
                        <div class="aap-card-actions">
                            <button type="button" class="aap-btn-inspect" onclick="openApprovalDrawer({{ $task->id }})" title="Quick Inspect Drawer">
                                <i class="fa-solid fa-sliders"></i> Quick View
                            </button>
                            <button type="button" class="aap-btn-approve-inline" onclick="approveTaskInline({{ $task->id }})" title="Approve & Complete">
                                <i class="fa-solid fa-check"></i> Approve
                            </button>
                            <button type="button" class="aap-btn-reject-inline" onclick="openRevisionModal({{ $task->id }}, '{{ addslashes($task->title) }}')" title="Request Revision">
                                <i class="fa-solid fa-rotate-left"></i> Revision
                            </button>
                            <a href="{{ $reviewUrl }}" class="btn-sec" style="padding:8px 10px;border-radius:9px;font-size:11.5px" title="Full Page Review">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- PAGINATION --}}
            @if($tasks->hasPages())
                <div style="margin-top:24px;display:flex;justify-content:center;gap:8px">
                    {{ $tasks->links('vendor.pagination.custom') }}
                </div>
            @endif
        @endif
    </div>
</div>

{{-- FLOATING BULK APPROVAL BAR --}}
<div class="aap-bulk-bar" id="bulkActionBar">
    <div class="aap-bulk-info">
        <i class="fa-solid fa-check-double" style="color:var(--primary);font-size:16px"></i>
        <span id="bulkSelectedText">0 tasks selected</span>
    </div>
    <button type="button" class="aap-bulk-btn-approve" onclick="submitBulkApprove()">
        <i class="fa-solid fa-circle-check"></i> Approve Selected Tasks
    </button>
    <button type="button" class="btn-sec" style="padding:8px 12px;border-radius:10px;font-size:12px" onclick="clearSelections()">
        Cancel
    </button>
</div>

{{-- SLIDE-OVER QUICK REVIEW DRAWER --}}
<div class="aap-drawer-backdrop" id="approvalDrawerBackdrop" onclick="closeApprovalDrawer()"></div>
<div class="aap-drawer" id="approvalDrawer">
    <div class="aap-drawer-header">
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                <span class="tag tag-blue" id="drawerTaskType" style="font-size:10px">Post</span>
                <span class="tag tag-amber" id="drawerTaskStatus" style="font-size:10px">Review</span>
                <span id="drawerUrgentBadge" class="aap-tag aap-tag-urgent" style="display:none;font-size:9.5px"><i class="fa-solid fa-bolt"></i> Urgent</span>
            </div>
            <h3 id="drawerTaskTitle" style="margin:0;font-size:16px;font-weight:800;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Task Title</h3>
            <div id="drawerClientName" style="font-size:12px;color:var(--text3);margin-top:2px">Client Name</div>
        </div>
        <button type="button" class="btn-sec" style="padding:6px 10px;border-radius:8px" onclick="closeApprovalDrawer()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="aap-drawer-body" id="drawerBody">
        {{-- Urgent Triage Card --}}
        <div id="drawerUrgentCard" style="display:none;padding:12px 16px;border-radius:12px;background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.25)">
            <div style="font-size:11px;font-weight:800;color:#dc2626;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">⚡ Designer-Initiated Emergency Task</div>
            <div style="font-size:12.5px;color:var(--text);margin-bottom:6px">Requested By: <strong id="drawerUrgentRequester">—</strong></div>
            <div id="drawerPausedBox" style="font-size:12px;color:var(--text2);display:none">
                Pre-empted Task: <a id="drawerPausedLink" href="#" target="_blank" style="color:var(--primary);font-weight:700;text-decoration:underline">#123 Task</a>
            </div>
        </div>

        {{-- Deliverables Gallery --}}
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <div style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px">Deliverable Artwork</div>
                <span id="drawerMediaCount" style="font-size:11px;color:var(--text3);font-weight:600">0 Files</span>
            </div>
            <div id="drawerMediaContainer" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:10px">
                {{-- Dynamic thumbnails --}}
            </div>
        </div>

        {{-- Copy Blocks --}}
        <div style="background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:14px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <span style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px">Marketing Caption</span>
                <button type="button" class="btn-sec" style="padding:4px 8px;font-size:11px;border-radius:6px" onclick="copyDrawerText('drawerCaption')">
                    <i class="fa-solid fa-copy"></i> Copy
                </button>
            </div>
            <div id="drawerCaption" style="font-size:13px;color:var(--text);line-height:1.5;white-space:pre-wrap;max-height:140px;overflow-y:auto">No caption written yet.</div>
        </div>

        <div style="background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:14px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <span style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px">Hashtags</span>
                <button type="button" class="btn-sec" style="padding:4px 8px;font-size:11px;border-radius:6px" onclick="copyDrawerText('drawerHashtags')">
                    <i class="fa-solid fa-copy"></i> Copy
                </button>
            </div>
            <div id="drawerHashtags" style="font-size:12.5px;color:var(--primary);line-height:1.4;white-space:pre-wrap">No hashtags specified.</div>
        </div>

        {{-- Meta & Designer Timer --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:12px">
            <div style="background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:10px 12px">
                <div style="color:var(--text3);font-size:10.5px;text-transform:uppercase;font-weight:700">Designer</div>
                <div id="drawerDesignerName" style="font-weight:700;color:var(--text);margin-top:2px">—</div>
            </div>
            <div style="background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:10px 12px">
                <div style="color:var(--text3);font-size:10.5px;text-transform:uppercase;font-weight:700">Production Time</div>
                <div id="drawerTimerFormatted" style="font-weight:700;color:var(--text);margin-top:2px">00:00:00</div>
            </div>
        </div>
    </div>

    <div class="aap-drawer-footer">
        <a id="drawerFullWorkspaceLink" href="#" class="btn-sec" style="font-size:12px;padding:8px 12px;border-radius:9px">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Full Review Page
        </a>
        <div style="display:flex;gap:8px">
            <button type="button" class="aap-btn-reject-inline" id="drawerRevisionBtn" style="padding:9px 14px">
                <i class="fa-solid fa-rotate-left"></i> Revision
            </button>
            <button type="button" class="aap-btn-approve-inline" id="drawerApproveBtn" style="padding:9px 18px">
                <i class="fa-solid fa-check"></i> Approve Task
            </button>
        </div>
    </div>
</div>

{{-- FAST-TRACK REVISION MODAL --}}
<div class="aap-modal-backdrop" id="revisionModalBackdrop">
    <div class="aap-modal">
        <div class="aap-modal-header">
            <div>
                <h4 style="margin:0;font-size:16px;font-weight:800;color:var(--text)" id="modalTaskTitle">Request Revision</h4>
                <div style="font-size:11.5px;color:var(--text3);margin-top:2px">Send feedback directly to the team</div>
            </div>
            <button type="button" class="btn-sec" style="padding:4px 8px;border-radius:6px" onclick="closeRevisionModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="fastTrackRevisionForm" onsubmit="submitFastTrackRevision(event)">
            <input type="hidden" id="modalTaskId" name="task_id">
            <div class="aap-modal-body">
                <div style="margin-bottom:14px">
                    <label style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px">Revision Target</label>
                    <div style="display:flex;gap:10px;margin-top:6px">
                        <label style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--text);cursor:pointer">
                            <input type="radio" name="revision_target" value="designer" checked style="accent-color:var(--primary)">
                            Visual / Design (Back to Designer)
                        </label>
                        <label style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;color:var(--text);cursor:pointer">
                            <input type="radio" name="revision_target" value="strategist" style="accent-color:var(--primary)">
                            Copy / Content (To Strategist)
                        </label>
                    </div>
                </div>

                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px">Quick Feedback Chips</label>
                    <div class="aap-chip-group">
                        <span class="aap-chip" onclick="addFeedbackChip('Fix typography / layout alignment')">Fix Typography</span>
                        <span class="aap-chip" onclick="addFeedbackChip('Update color palette & branding')">Color Palette</span>
                        <span class="aap-chip" onclick="addFeedbackChip('Change video pace / cut')">Video Pacing</span>
                        <span class="aap-chip" onclick="addFeedbackChip('Correct copy typos / grammar')">Copy Typos</span>
                        <span class="aap-chip" onclick="addFeedbackChip('High-res export required')">Export Quality</span>
                    </div>
                </div>

                <div>
                    <label style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px">Revision Notes *</label>
                    <textarea id="modalReason" name="reason" rows="3" required placeholder="Describe what adjustments are needed…" class="aap-search-input" style="width:100%;padding:10px 12px;margin-top:6px;resize:vertical;height:auto"></textarea>
                </div>
            </div>
            <div class="aap-modal-footer">
                <button type="button" class="btn-sec" onclick="closeRevisionModal()">Cancel</button>
                <button type="submit" class="aap-filter-btn" style="background:#dc2626"><i class="fa-solid fa-rotate-left"></i> Send Revision Request</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    let currentDrawerTaskId = null;

    // Filter toggles
    window.toggleUrgentFilter = () => {
        const input = document.getElementById('filterUrgentInput');
        input.value = input.value === 'yes' ? '' : 'yes';
        document.getElementById('approvalsFilterForm').submit();
    };

    window.toggleMediaFilter = () => {
        const input = document.getElementById('filterMediaInput');
        input.value = input.value === 'yes' ? '' : 'yes';
        document.getElementById('approvalsFilterForm').submit();
    };

    // Checkboxes & Bulk Selection
    window.handleTaskCheck = () => {
        const checkedBoxes = document.querySelectorAll('.task-check:checked');
        const count = checkedBoxes.length;
        const total = document.querySelectorAll('.task-check').length;

        document.getElementById('selectedCountText').textContent = `${count} task${count !== 1 ? 's' : ''} selected`;
        document.getElementById('bulkSelectedText').textContent = `${count} task${count !== 1 ? 's' : ''} selected`;

        const selectAllBox = document.getElementById('selectAllCheckbox');
        if (selectAllBox) {
            selectAllBox.checked = count === total && total > 0;
            selectAllBox.indeterminate = count > 0 && count < total;
        }

        const bulkBar = document.getElementById('bulkActionBar');
        if (count > 0) {
            bulkBar.classList.add('visible');
        } else {
            bulkBar.classList.remove('visible');
        }

        // Highlight selected cards
        document.querySelectorAll('.aap-card').forEach(card => {
            const cb = card.querySelector('.task-check');
            if (cb && cb.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    };

    window.toggleSelectAll = (master) => {
        document.querySelectorAll('.task-check').forEach(cb => {
            cb.checked = master.checked;
        });
        window.handleTaskCheck();
    };

    window.clearSelections = () => {
        document.querySelectorAll('.task-check').forEach(cb => cb.checked = false);
        window.handleTaskCheck();
    };

    window.submitBulkApprove = () => {
        const checked = Array.from(document.querySelectorAll('.task-check:checked')).map(cb => parseInt(cb.value));
        if (checked.length === 0) return;

        if (!confirm(`Are you sure you want to approve and complete ${checked.length} task(s)?`)) return;

        fetch('{{ route('admin.approvals.bulk-approve') }}', {
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
                checked.forEach(id => {
                    const card = document.getElementById(`task-card-${id}`);
                    if (card) {
                        card.style.transition = 'opacity 0.3s, transform 0.3s';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.96)';
                        setTimeout(() => card.remove(), 300);
                    }
                });
                window.clearSelections();
                if (window.toastSuccess) window.toastSuccess(data.message);
                else alert(data.message);
                setTimeout(() => window.location.reload(), 800);
            } else {
                alert(data.message || 'Bulk approval failed');
            }
        })
        .catch(err => {
            console.error('Bulk approve error:', err);
            alert('An error occurred during bulk approval.');
        });
    };

    // 1-Click Inline Approve
    window.approveTaskInline = (taskId) => {
        if (!confirm('Approve and mark this task as completed?')) return;

        fetch(`/admin/approvals/${taskId}/approve`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById(`task-card-${taskId}`);
                if (card) {
                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.96)';
                    setTimeout(() => card.remove(), 300);
                }
                if (window.closeApprovalDrawer) window.closeApprovalDrawer();
                if (window.toastSuccess) window.toastSuccess(data.message);
                else alert(data.message);
            } else {
                alert(data.message || 'Approval failed');
            }
        })
        .catch(err => {
            console.error('Approve error:', err);
            alert('Approval request failed.');
        });
    };

    // Revision Modal
    window.openRevisionModal = (taskId, taskTitle) => {
        document.getElementById('modalTaskId').value = taskId;
        document.getElementById('modalTaskTitle').textContent = `Revision: ${taskTitle}`;
        document.getElementById('modalReason').value = '';
        document.getElementById('revisionModalBackdrop').classList.add('open');
    };

    window.closeRevisionModal = () => {
        document.getElementById('revisionModalBackdrop').classList.remove('open');
    };

    window.addFeedbackChip = (text) => {
        const area = document.getElementById('modalReason');
        if (area.value.trim() === '') {
            area.value = text;
        } else {
            area.value += '; ' + text;
        }
    };

    window.submitFastTrackRevision = (e) => {
        e.preventDefault();
        const taskId = document.getElementById('modalTaskId').value;
        const form = document.getElementById('fastTrackRevisionForm');
        const formData = new FormData(form);

        fetch(`/admin/approvals/${taskId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.closeRevisionModal();
                const card = document.getElementById(`task-card-${taskId}`);
                if (card) {
                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 300);
                }
                if (window.closeApprovalDrawer) window.closeApprovalDrawer();
                if (window.toastSuccess) window.toastSuccess(data.message);
                else alert(data.message);
            } else {
                alert(data.message || 'Revision request failed');
            }
        })
        .catch(err => {
            console.error('Revision error:', err);
            alert('Failed to request revision.');
        });
    };

    // Slide-Over Task Review Drawer
    window.openApprovalDrawer = (taskId) => {
        currentDrawerTaskId = taskId;
        const drawer = document.getElementById('approvalDrawer');
        const backdrop = document.getElementById('approvalDrawerBackdrop');

        drawer.classList.add('open');
        backdrop.classList.add('open');

        // Loading state
        document.getElementById('drawerTaskTitle').textContent = 'Loading deliverables…';

        fetch(`/admin/tasks/${taskId}/quick-inspect`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            const task = data.task;

            document.getElementById('drawerTaskTitle').textContent = task.title;
            document.getElementById('drawerTaskType').textContent = task.type || 'Post';
            document.getElementById('drawerTaskStatus').textContent = task.status_label || 'Review';
            document.getElementById('drawerClientName').textContent = task.client?.name || 'Client';

            // Urgent badge
            const urgentBadge = document.getElementById('drawerUrgentBadge');
            const urgentCard = document.getElementById('drawerUrgentCard');
            if (task.is_urgent_task) {
                urgentBadge.style.display = 'inline-flex';
                urgentCard.style.display = 'block';
                document.getElementById('drawerUrgentRequester').textContent = task.urgent_requested_by || 'Client / Lead';
                if (task.paused_predecessor) {
                    document.getElementById('drawerPausedBox').style.display = 'block';
                    const link = document.getElementById('drawerPausedLink');
                    link.textContent = `#${task.paused_predecessor.id} ${task.paused_predecessor.title}`;
                    link.href = `/admin/tasks/${task.paused_predecessor.id}`;
                } else {
                    document.getElementById('drawerPausedBox').style.display = 'none';
                }
            } else {
                urgentBadge.style.display = 'none';
                urgentCard.style.display = 'none';
            }

            // Media gallery
            const mediaBox = document.getElementById('drawerMediaContainer');
            mediaBox.innerHTML = '';
            document.getElementById('drawerMediaCount').textContent = `${(task.media || []).length} Files`;

            if (task.media && task.media.length > 0) {
                task.media.forEach(m => {
                    const item = document.createElement('div');
                    item.className = 'aap-thumb-container';
                    item.style.width = '100%';
                    item.style.height = '120px';
                    item.onclick = () => window.openMediaLightbox({
                        url: m.url,
                        isVideo: m.is_video,
                        title: task.title,
                        client: task.client?.name,
                        taskUrl: `/admin/approvals/${task.id}`,
                        downloadUrl: m.url
                    });

                    if (m.is_video) {
                        item.innerHTML = `
                            <video src="${m.url}" style="width:100%;height:100%;object-fit:cover"></video>
                            <span class="aap-thumb-badge-video"><i class="fa-solid fa-play"></i></span>
                            <div class="aap-thumb-overlay"><i class="fa-solid fa-expand"></i></div>
                        `;
                    } else {
                        item.innerHTML = `
                            <img src="${m.url}" style="width:100%;height:100%;object-fit:cover" alt="${m.name}">
                            <div class="aap-thumb-overlay"><i class="fa-solid fa-magnifying-glass-plus"></i></div>
                        `;
                    }
                    mediaBox.appendChild(item);
                });
            } else {
                mediaBox.innerHTML = '<div style="grid-column:1/-1;padding:20px;text-align:center;color:var(--text3);font-size:12.5px;background:var(--card2);border-radius:10px">No artwork uploaded yet.</div>';
            }

            // Copy blocks
            document.getElementById('drawerCaption').textContent = task.caption || task.brief || 'No caption provided.';
            document.getElementById('drawerHashtags').textContent = task.hashtags || 'No hashtags provided.';

            // Designer & Timer
            document.getElementById('drawerDesignerName').textContent = task.assignee?.name || 'Unassigned';
            document.getElementById('drawerTimerFormatted').textContent = task.timer_formatted || '00:00:00';

            // Links & Actions
            document.getElementById('drawerFullWorkspaceLink').href = `/admin/approvals/${task.id}`;
            document.getElementById('drawerApproveBtn').onclick = () => window.approveTaskInline(task.id);
            document.getElementById('drawerRevisionBtn').onclick = () => window.openRevisionModal(task.id, task.title);
        })
        .catch(err => {
            console.error('Quick inspect failed:', err);
            document.getElementById('drawerTaskTitle').textContent = 'Error loading task';
        });
    };

    window.closeApprovalDrawer = () => {
        document.getElementById('approvalDrawer').classList.remove('open');
        document.getElementById('approvalDrawerBackdrop').classList.remove('open');
        currentDrawerTaskId = null;
    };

    window.copyDrawerText = (elementId) => {
        const text = document.getElementById(elementId).textContent;
        navigator.clipboard.writeText(text).then(() => {
            if (window.toastSuccess) window.toastSuccess('Copied to clipboard!');
            else alert('Copied to clipboard!');
        });
    };
})();
</script>
@endpush
