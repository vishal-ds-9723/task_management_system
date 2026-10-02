@extends('layouts.app')

@section('content')
<style>
    .td-grid { display:grid; grid-template-columns:1.1fr 0.9fr; gap:20px; align-items:start; }
    .td-card { background:var(--card); border-radius:var(--radius); padding:24px; margin-bottom:16px; border:1px solid var(--border); }
    .td-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:20px; }
    .td-title { font-size:20px; font-weight:700; font-family:'Plus Jakarta Sans',sans-serif; color:var(--text); margin-bottom:10px; line-height:1.3; }
    .td-tags { display:flex; flex-wrap:wrap; align-items:center; gap:8px; }
    .td-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .td-info-box { background:var(--card2); padding:14px; border-radius:var(--radius-sm); border:1px solid var(--border); }
    .td-info-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:var(--text3); margin-bottom:6px; }
    .td-info-val { font-size:13px; font-weight:500; color:var(--text); display:flex; align-items:center; gap:6px; }
    .td-section-title { font-size:14px; font-weight:700; color:var(--text); margin-bottom:14px; display:flex; align-items:center; gap:8px; font-family:'Plus Jakarta Sans',sans-serif; }
    .td-caption { background:var(--card2); padding:16px; border-radius:var(--radius-sm); line-height:1.8; font-size:13px; border:1px solid var(--border); color:var(--text2); white-space:pre-wrap; }
    .td-hashtags { display:flex; flex-wrap:wrap; gap:6px; }
    .td-hashtag { background:var(--primary); color:#fff; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }

    /* Media Gallery */
    .td-media-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:14px; }
    .td-media-card { background:var(--card2); border:1px solid var(--border); border-radius:10px; overflow:hidden; display:flex; flex-direction:column; transition:transform .2s, border-color .2s, box-shadow .2s; }
    .td-media-card:hover { transform:translateY(-2px); border-color:var(--primary); box-shadow:0 8px 20px -4px rgba(0,0,0,0.12); }
    .td-thumb-wrap { position:relative; aspect-ratio:16/10; background:#090d16; cursor:pointer; overflow:hidden; display:flex; align-items:center; justify-content:center; }
    .td-thumb-wrap img, .td-thumb-wrap video { width:100%; height:100%; object-fit:cover; transition:transform .25s ease; }
    .td-media-card:hover .td-thumb-wrap img, .td-media-card:hover .td-thumb-wrap video { transform:scale(1.04); }
    .td-thumb-badge { position:absolute; top:6px; left:6px; background:rgba(15,23,42,0.85); backdrop-filter:blur(4px); color:#fff; font-size:9.5px; font-weight:600; padding:2px 7px; border-radius:4px; display:inline-flex; align-items:center; gap:4px; z-index:2; }
    .td-thumb-overlay { position:absolute; inset:0; background:rgba(15,23,42,0.55); backdrop-filter:blur(2px); display:flex; align-items:center; justify-content:center; gap:8px; opacity:0; transition:opacity .15s; z-index:3; }
    .td-thumb-wrap:hover .td-thumb-overlay { opacity:1; }
    .td-icon-btn { width:34px; height:34px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:13px; border:none; cursor:pointer; text-decoration:none; transition:transform .15s, background .15s; }
    .td-icon-btn-view { background:#fff; color:#0f172a; }
    .td-icon-btn-view:hover { transform:scale(1.1); color:var(--primary); }
    .td-icon-btn-dl { background:#10B981; color:#fff; }
    .td-icon-btn-dl:hover { transform:scale(1.1); background:#059669; }
    .td-media-meta { padding:10px 12px; display:flex; align-items:center; justify-content:space-between; gap:8px; background:var(--card); border-top:1px solid var(--border); }
    .td-media-filename { font-size:11.5px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .td-dl-chip { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; background:rgba(16,185,129,0.1); color:#10B981; font-size:10.5px; font-weight:700; text-decoration:none; white-space:nowrap; transition:background .15s; }
    .td-dl-chip:hover { background:rgba(16,185,129,0.2); }
    .td-media-empty { padding:40px; text-align:center; color:var(--text3); font-size:13px; background:var(--card2); border-radius:var(--radius-sm); border:2px dashed var(--border); }

    /* Modern Lightbox for Video & Images */
    .td-lightbox-modal { display:none; position:fixed; inset:0; z-index:99999; background:rgba(9,13,22,0.92); backdrop-filter:blur(8px); flex-direction:column; }
    .td-lightbox-modal.open { display:flex; }
    .td-lb-header { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; background:rgba(15,23,42,0.7); border-bottom:1px solid rgba(255,255,255,0.1); color:#fff; z-index:10; }
    .td-lb-title-wrap { display:flex; align-items:center; gap:10px; min-width:0; }
    .td-lb-title { font-size:14px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .td-lb-counter { font-size:11px; font-weight:700; background:rgba(255,255,255,0.15); padding:2px 8px; border-radius:99px; color:#cbd5e1; }
    .td-lb-actions { display:flex; align-items:center; gap:10px; flex-shrink:0; }
    .td-lb-btn { display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none; cursor:pointer; border:1px solid transparent; transition:all .15s; }
    .td-lb-btn-dl { background:#10B981; color:#fff; }
    .td-lb-btn-dl:hover { background:#059669; }
    .td-lb-btn-sec { background:rgba(255,255,255,0.1); color:#fff; border-color:rgba(255,255,255,0.2); }
    .td-lb-btn-sec:hover { background:rgba(255,255,255,0.2); }
    .td-lb-close { background:transparent; border:none; color:#94a3b8; font-size:20px; cursor:pointer; padding:6px; border-radius:6px; transition:color .15s; display:flex; align-items:center; justify-content:center; }
    .td-lb-close:hover { color:#fff; }
    .td-lb-body { flex:1; position:relative; display:flex; align-items:center; justify-content:center; padding:20px; overflow:hidden; }
    .td-lb-nav { position:absolute; top:50%; transform:translateY(-50%); width:44px; height:44px; border-radius:50%; background:rgba(255,255,255,0.12); color:#fff; border:1px solid rgba(255,255,255,0.2); font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s; z-index:5; backdrop-filter:blur(4px); }
    .td-lb-nav:hover { background:rgba(255,255,255,0.25); transform:translateY(-50%) scale(1.08); }
    .td-lb-nav.prev { left:20px; }
    .td-lb-nav.next { right:20px; }
    .td-lb-content { max-width:92vw; max-height:82vh; display:flex; align-items:center; justify-content:center; }
    .td-lb-content img { max-width:100%; max-height:82vh; object-fit:contain; border-radius:8px; box-shadow:0 20px 40px rgba(0,0,0,0.5); }
    .td-lb-content video { max-width:100%; max-height:82vh; border-radius:8px; box-shadow:0 20px 40px rgba(0,0,0,0.5); background:#000; outline:none; }

    /* Timeline */
    .td-timeline { position:relative; padding-left:20px; }
    .td-timeline::before { content:''; position:absolute; left:6px; top:4px; bottom:4px; width:2px; background:var(--border); }
    .td-timeline-item { position:relative; margin-bottom:14px; padding-left:16px; }
    .td-timeline-item::before { content:''; position:absolute; left:-16px; top:5px; width:10px; height:10px; border-radius:50%; border:2px solid var(--border); background:var(--card); }
    .td-timeline-item.done::before { background:var(--teal); border-color:var(--teal); }
    .td-timeline-item.active::before { background:var(--blue); border-color:var(--blue); box-shadow:0 0 0 3px rgba(59,130,246,0.2); }
    .td-timeline-label { font-size:11px; font-weight:600; color:var(--text3); text-transform:uppercase; letter-spacing:0.3px; }
    .td-timeline-val { font-size:12.5px; color:var(--text); font-weight:500; margin-top:2px; }

    /* Social Posts */
    .td-post-row { display:flex; align-items:center; gap:12px; padding:12px; background:var(--card2); border-radius:var(--radius-sm); border:1px solid var(--border); margin-bottom:8px; transition:border-color 0.2s; }
    .td-post-row:hover { border-color:var(--primary); }
    .td-post-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
    .td-post-info { flex:1; min-width:0; }
    .td-post-platform { font-size:13px; font-weight:600; color:var(--text); }
    .td-post-meta { font-size:11px; color:var(--text3); margin-top:2px; }
    .td-post-link { color:var(--primary); font-size:12px; font-weight:600; text-decoration:none; white-space:nowrap; }
    .td-post-link:hover { text-decoration:underline; }

    /* Publishing Progress */
    .td-pub-progress { display:flex; align-items:center; gap:12px; padding:14px; background:var(--card2); border-radius:var(--radius-sm); border:1px solid var(--border); margin-bottom:16px; }
    .td-pub-bar { flex:1; height:6px; background:var(--border); border-radius:3px; overflow:hidden; }
    .td-pub-fill { height:100%; border-radius:3px; transition:width 0.3s; }
    .td-pub-text { font-size:12px; font-weight:600; color:var(--text); white-space:nowrap; }

    /* Approval Banner */
    .td-approval-banner { padding:16px; border-radius:var(--radius-sm); display:flex; align-items:center; gap:12px; margin-bottom:16px; }
    .td-approval-banner.pending { background:#F59E0B18; border:1px solid #F59E0B40; }
    .td-approval-banner.approved { background:#10B98118; border:1px solid #10B98140; }
    .td-approval-banner.rejected { background:#EF444418; border:1px solid #EF444440; }
    .td-approval-icon { font-size:24px; flex-shrink:0; }
    .td-approval-text { flex:1; }
    .td-approval-title { font-size:14px; font-weight:700; }
    .td-approval-sub { font-size:12px; color:var(--text3); margin-top:2px; }

    /* Team */
    .td-team-row { display:flex; align-items:center; gap:10px; padding:10px 14px; background:var(--card2); border-radius:var(--radius-sm); border:1px solid var(--border); margin-bottom:8px; }
    .td-team-av { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; font-weight:700; flex-shrink:0; }
    .td-team-info { flex:1; }
    .td-team-name { font-size:13px; font-weight:600; color:var(--text); }
    .td-team-role { font-size:11px; color:var(--text3); }
    .td-team-hours { font-size:11px; color:var(--text3); text-align:right; white-space:nowrap; }

    /* Refs */
    .td-ref-link { display:flex; align-items:center; gap:8px; padding:10px 14px; background:var(--card2); border-radius:var(--radius-sm); border:1px solid var(--border); margin-bottom:6px; text-decoration:none; color:var(--primary); font-size:12px; word-break:break-all; transition:border-color 0.2s; }
    .td-ref-link:hover { border-color:var(--primary); }
    .td-ref-link i { color:var(--text3); flex-shrink:0; }

    /* ===== Status Workflow Stepper ===== */
    .td-status-current { display:flex; align-items:center; gap:12px; padding:14px 16px; border-radius:var(--radius-sm); margin-bottom:18px; }
    .td-status-current-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
    .td-status-current-info { flex:1; }
    .td-status-current-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; opacity:0.7; }
    .td-status-current-val { font-size:16px; font-weight:800; font-family:'Plus Jakarta Sans',sans-serif; }

    .td-stepper { display:flex; align-items:flex-start; gap:0; margin-bottom:20px; position:relative; }
    .td-step { flex:1; text-align:center; position:relative; cursor:pointer; }
    .td-step-dot-wrap { display:flex; align-items:center; justify-content:center; margin-bottom:8px; position:relative; z-index:2; }
    .td-step-dot { width:32px; height:32px; border-radius:50%; border:2.5px solid var(--border); background:var(--card); display:flex; align-items:center; justify-content:center; font-size:13px; transition:all 0.25s ease; position:relative; }
    .td-step-line { position:absolute; top:16px; left:calc(50% + 16px); right:calc(-50% + 16px); height:3px; background:var(--border); z-index:1; transition:background 0.3s; }
    .td-step:last-child .td-step-line { display:none; }
    .td-step-label { font-size:10px; font-weight:600; color:var(--text3); line-height:1.2; transition:color 0.2s; }

    /* Step states */
    .td-step.past .td-step-dot { background:var(--teal); border-color:var(--teal); color:#fff; }
    .td-step.past .td-step-line { background:var(--teal); }
    .td-step.past .td-step-label { color:var(--teal); }

    .td-step.current .td-step-dot { border-color:var(--blue); background:var(--blue); color:#fff; box-shadow:0 0 0 4px rgba(59,130,246,0.18); transform:scale(1.15); }
    .td-step.current .td-step-label { color:var(--blue); font-weight:700; }

    .td-step.current-done .td-step-dot { border-color:var(--teal); background:var(--teal); color:#fff; box-shadow:0 0 0 4px rgba(16,185,129,0.18); transform:scale(1.15); }
    .td-step.current-done .td-step-label { color:var(--teal); font-weight:700; }

    .td-step.current-review .td-step-dot { border-color:#F59E0B; background:#F59E0B; color:#fff; box-shadow:0 0 0 4px rgba(245,158,11,0.18); transform:scale(1.15); }
    .td-step.current-review .td-step-label { color:#F59E0B; font-weight:700; }

    .td-step:not(.past):not(.current):not(.current-done):not(.current-review):hover .td-step-dot { border-color:var(--text3); background:var(--card2); transform:scale(1.08); }
    .td-step:not(.past):not(.current):not(.current-done):not(.current-review):hover .td-step-label { color:var(--text2); }

    /* Priority Selector */
    .td-priority-row { display:flex; gap:8px; }
    .td-priority-btn { flex:1; padding:10px 8px; border:2px solid var(--border); border-radius:var(--radius-sm); background:var(--card2); cursor:pointer; text-align:center; font-size:12px; font-weight:600; color:var(--text3); transition:all 0.2s; display:flex; align-items:center; justify-content:center; gap:6px; }
    .td-priority-btn:hover { border-color:var(--text3); }
    .td-priority-btn.pri-active-normal { border-color:var(--teal); color:var(--teal); background:#10B98110; }
    .td-priority-btn.pri-active-high { border-color:#F59E0B; color:#F59E0B; background:#F59E0B10; }
    .td-priority-btn.pri-active-urgent { border-color:var(--red); color:var(--red); background:#EF444410; animation:td-pulse-urgent 2s infinite; }
    @keyframes td-pulse-urgent { 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,0.15)} 50%{box-shadow:0 0 0 6px rgba(239,68,68,0)} }

    /* Quick Actions */
    .td-quick-actions { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .td-quick-btn { padding:10px; border:1.5px solid var(--border); border-radius:var(--radius-sm); background:var(--card2); cursor:pointer; text-align:center; font-size:12px; font-weight:600; color:var(--text3); transition:all 0.2s; display:flex; align-items:center; justify-content:center; gap:6px; }
    .td-quick-btn:hover { border-color:var(--primary); color:var(--primary); background:var(--card); }
    .td-quick-btn.danger:hover { border-color:var(--red); color:var(--red); }

    @media(max-width:900px) {
        .td-grid { grid-template-columns:1fr; }
        .td-info-grid { grid-template-columns:1fr; }
        .td-media-grid { grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); }
        .td-stepper { flex-wrap:wrap; gap:4px; }
        .td-step-line { display:none !important; }
    }
</style>

<div class="topbar">
    <div>
        <div class="breadcrumb">
            <a href="{{ route('admin.tasks') }}">All Tasks</a>
            <span class="breadcrumb-sep">›</span>
            @if($task->client)
                <a href="{{ route('admin.clients.show', $task->client) }}">{{ $task->client->name }}</a>
                <span class="breadcrumb-sep">›</span>
            @endif
            <span>{{ Str::limit($task->title, 30) }}</span>
        </div>
        <div class="page-title">{{ $task->title }}</div>
    </div>
    <div style="display:flex;gap:10px;align-items:center">
        <a href="{{ route('admin.tasks.edit', $task) }}" class="btn-sec" style="display:flex;align-items:center;gap:6px"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
        <a href="{{ route('admin.tasks') }}" class="btn-sec">← Back</a>
    </div>
</div>

{{-- Approval Banner (full-width, above the grid) --}}
@php
    $isUrgentTask = (bool) $task->is_urgent_task;
    $isDevTask = in_array($task->type, ['website', 'software', 'maintenance']);
    $urgentDelivered = $isUrgentTask && in_array($task->status, ['completed', 'published'], true);
    $urgentShared = $isUrgentTask && in_array($task->status, ['review', 'pending_approval'], true);
@endphp

@if($isUrgentTask)
    @php
        $urgentActorName = $task->assignee?->name ?? $task->creator?->name ?? 'Team member';
        $urgentStatusLabel = $urgentDelivered
            ? 'Delivered to client'
            : ($urgentShared ? 'Latest work shared for admin view' : 'Still in progress');
        $urgentStatusTimeLabel = $urgentDelivered
            ? 'Delivered At'
            : ($urgentShared ? 'Shared At' : 'Last Update');
        $urgentStatusTime = $urgentDelivered
            ? $task->completed_at
            : ($urgentShared ? $task->submitted_at : $task->updated_at);
        $urgentBannerStyle = $urgentDelivered
            ? 'background:#10B98118;border:1px solid #10B98140;'
            : ($urgentShared
                ? 'background:#0EA5E918;border:1px solid #0EA5E940;'
                : 'background:#F59E0B18;border:1px solid #F59E0B40;');
        $urgentBannerIcon = $urgentDelivered ? 'fa-truck-fast' : ($urgentShared ? 'fa-eye' : 'fa-person-running');
        $urgentBannerColor = $urgentDelivered ? '#10B981' : ($urgentShared ? '#0EA5E9' : '#F59E0B');
        $urgentBannerTitle = $urgentDelivered
            ? 'Delivered To Client'
            : ($urgentShared ? 'View Only Update' : 'Urgent Task In Progress');
        $urgentBannerSubtitle = $urgentDelivered
            ? ('Designer marked this urgent task as delivered to the client' . ($task->completed_at ? ' ' . $task->completed_at->diffForHumans() : '') . '.')
            : ($urgentShared
                ? ('Designer shared the latest urgent-task work' . ($task->submitted_at ? ' ' . $task->submitted_at->diffForHumans() : '') . '. No admin approval is required.')
                : 'This urgent task is still being worked on. Open the details below to view the latest files and notes.');
    @endphp
    <div class="td-approval-banner" style="{{ $urgentBannerStyle }}">
        <div class="td-approval-icon"><i class="fas {{ $urgentBannerIcon }}" style="color:{{ $urgentBannerColor }}"></i></div>
        <div class="td-approval-text">
            <div class="td-approval-title" style="color:{{ $urgentBannerColor }}">{{ $urgentBannerTitle }}</div>
            <div class="td-approval-sub">{{ $urgentBannerSubtitle }}</div>
        </div>
    </div>

    <div class="td-card" style="padding:16px;margin-bottom:16px">
        <div class="td-section-title"><span><i class="fas fa-receipt"></i></span> Urgent Delivery Note</div>
        <div class="td-info-grid" style="margin-bottom:0">
            <div class="td-info-box">
                <div class="td-info-label">Requested By</div>
                <div class="td-info-val">{{ $task->urgent_requested_by ?: 'Not specified' }}</div>
            </div>
            <div class="td-info-box">
                <div class="td-info-label">Handled By</div>
                <div class="td-info-val">{{ $urgentActorName }}</div>
            </div>
            <div class="td-info-box">
                <div class="td-info-label">Delivery Status</div>
                <div class="td-info-val" style="color:{{ $urgentDelivered ? 'var(--teal)' : ($urgentShared ? 'var(--blue)' : 'var(--yellow)') }}">
                    {{ $urgentStatusLabel }}
                </div>
            </div>
            <div class="td-info-box">
                <div class="td-info-label">{{ $urgentStatusTimeLabel }}</div>
                <div class="td-info-val">{{ $urgentStatusTime?->format('d M Y, h:i A') ?? 'Waiting for update' }}</div>
            </div>
        </div>
    </div>
@elseif(in_array($task->status, ['review', 'pending_approval']) && $task->admin_approval_status !== 'approved')
    <div class="td-approval-banner pending">
        <div class="td-approval-icon"><i class="fas fa-hourglass-half" style="color:#F59E0B"></i></div>
        <div class="td-approval-text">
            <div class="td-approval-title" style="color:#F59E0B">Awaiting Your Approval</div>
            <div class="td-approval-sub">
                This task was submitted for review{{ $task->submitted_at ? ' ' . $task->submitted_at->diffForHumans() : '' }}.
                @if($task->assignee) Submitted by {{ $task->assignee->name }}.@endif
                Review the content below and approve or reject.
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0">
            <form method="POST" action="{{ route('admin.tasks.approve-task', $task) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn-sec" style="border-color:var(--teal);color:var(--teal);font-weight:700;padding:8px 20px" onclick="return confirm('Approve this task?')"><i class="fas fa-check"></i> Approve</button>
            </form>
            <button type="button" class="btn-sec" style="border-color:var(--red);color:var(--red);font-weight:700;padding:8px 20px" onclick="document.getElementById('reject-panel').style.display='block'; this.style.display='none'"><i class="fas fa-times"></i> Reject</button>
        </div>
    </div>
    <form id="reject-panel" method="POST" action="{{ route('admin.tasks.reject-task', $task) }}" style="display:none;margin-bottom:16px">
        @csrf @method('PATCH')
        <div class="td-card" style="border-color:var(--red);border-style:dashed;margin-bottom:0">
            <div style="font-size:13px;font-weight:600;color:var(--red);margin-bottom:8px">Rejection Reason</div>
            <textarea name="rejection_reason" rows="3" placeholder="Explain what needs to change..." style="width:100%;padding:10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-family:inherit;resize:vertical;margin-bottom:10px;background:var(--card2);color:var(--text)"></textarea>
            <button type="submit" class="btn-sec" style="border-color:var(--red);color:white;background:var(--red);font-weight:600;padding:8px 24px" onclick="return confirm('Reject this task?')">Confirm Rejection</button>
        </div>
    </form>
@elseif($task->admin_approval_status === 'approved')
    <div class="td-approval-banner approved">
        <div class="td-approval-icon"><i class="fas fa-check-circle" style="color:#10B981"></i></div>
        <div class="td-approval-text">
            <div class="td-approval-title" style="color:#10B981">Approved</div>
            <div class="td-approval-sub">By {{ $task->adminApprover?->name ?? 'Admin' }} · {{ $task->admin_approved_at?->diffForHumans() }}</div>
        </div>
    </div>
@elseif($task->admin_approval_status === 'rejected')
    <div class="td-approval-banner rejected">
        <div class="td-approval-icon"><i class="fas fa-times-circle" style="color:#EF4444"></i></div>
        <div class="td-approval-text">
            <div class="td-approval-title" style="color:#EF4444">Rejected — Sent Back for Revision</div>
            <div class="td-approval-sub">By {{ $task->adminApprover?->name ?? 'Admin' }} · {{ $task->admin_approved_at?->diffForHumans() }}{{ $task->revision_count > 0 ? ' · Revision #' . $task->revision_count : '' }}</div>
        </div>
    </div>
@endif

<div class="td-grid">
    {{-- ============ LEFT COLUMN ============ --}}
    <div>
        {{-- Task Info Card --}}
        <div class="td-card">
            <div class="td-header">
                <div style="flex:1">
                    <h2 class="td-title">{{ $task->title }}</h2>
                    <div class="td-tags">
                        <span class="tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                        @if(!$isDevTask)
                            @if(is_array($task->platform))
                                @foreach($task->platform as $p)
                                    @php
                                        $platformIcons = [
                                            'instagram' => ['icon' => 'fa-instagram', 'color' => '#E1306C'],
                                            'facebook' => ['icon' => 'fa-facebook-f', 'color' => '#1877F2'],
                                            'twitter' => ['icon' => 'fa-x-twitter', 'color' => '#000000'],
                                            'linkedin' => ['icon' => 'fa-linkedin-in', 'color' => '#0077B5'],
                                            'youtube' => ['icon' => 'fa-youtube', 'color' => '#FF0000'],
                                            'tiktok' => ['icon' => 'fa-tiktok', 'color' => '#000000'],
                                            'whatsapp' => ['icon' => 'fa-whatsapp', 'color' => '#25D366'],
                                        ];
                                        $icon = $platformIcons[$p] ?? ['icon' => 'fa-link', 'color' => '#888888'];
                                    @endphp
                                    <span class="tag" style="background:{{ $icon['color'] }}18;border-color:{{ $icon['color'] }}40;color:{{ $icon['color'] }};display:inline-flex;align-items:center;gap:5px">
                                        <i class="fa-brands {{ $icon['icon'] }}"></i>
                                        {{ ucfirst($p) }}
                                    </span>
                                @endforeach
                            @elseif(is_string($task->platform) && $task->platform !== '')
                                @php
                                    $platformIcons = [
                                        'instagram' => ['icon' => 'fa-instagram', 'color' => '#E1306C'],
                                        'facebook' => ['icon' => 'fa-facebook-f', 'color' => '#1877F2'],
                                        'twitter' => ['icon' => 'fa-x-twitter', 'color' => '#000000'],
                                        'linkedin' => ['icon' => 'fa-linkedin-in', 'color' => '#0077B5'],
                                        'youtube' => ['icon' => 'fa-youtube', 'color' => '#FF0000'],
                                        'tiktok' => ['icon' => 'fa-tiktok', 'color' => '#000000'],
                                        'whatsapp' => ['icon' => 'fa-whatsapp', 'color' => '#25D366'],
                                    ];
                                    $platformKey = is_string($task->platform) ? $task->platform : '';
                                    $icon = $platformIcons[$platformKey] ?? ['icon' => 'fa-link', 'color' => '#888888'];
                                @endphp
                                <span class="tag" style="background:{{ $icon['color'] }}18;border-color:{{ $icon['color'] }}40;color:{{ $icon['color'] }};display:inline-flex;align-items:center;gap:5px">
                                    <i class="fa-brands {{ $icon['icon'] }}"></i>
                                    {{ ucfirst($platformKey) }}
                                </span>
                            @endif
                        @endif

                        <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                        @if($task->revision_count > 0)
                            <span class="wf-rev-badge"><i class="fa-solid fa-rotate-left"></i> {{ $task->revision_count }} {{ Str::plural('revision', $task->revision_count) }}</span>
                        @endif
                    </div>
                </div>
                {!! $task->priority_tag !!}
            </div>

            <div class="td-info-grid" style="margin-bottom:20px">
                <div class="td-info-box">
                    <div class="td-info-label">Client</div>
                    <div class="td-info-val">
                        <x-client-branding :client="$task->client" size="66px" width="154px" radius="8px" />
                        <a href="{{ route('admin.clients.show', $task->client) }}" style="color:var(--text);text-decoration:none;font-weight:600">{{ $task->client->name }}</a>
                    </div>
                </div>
                <div class="td-info-box">
                    <div class="td-info-label">Assigned To</div>
                    <div class="td-info-val">
                        @if($task->assignee)
                            <div class="av-sm" style="background:{{ $task->assignee->avatar_color ?? 'var(--teal)' }};width:24px;height:24px;font-size:10px">{{ $task->assignee->initial }}</div>
                            {{ $task->assignee->name }}
                        @else
                            <span style="color:var(--text3)">Unassigned</span>
                        @endif
                    </div>
                </div>
                <div class="td-info-box">
                    <div class="td-info-label">Created By</div>
                    <div class="td-info-val">
                        @if($task->creator)
                            <div class="av-sm" style="background:{{ $task->creator->avatar_color ?? 'var(--purple)' }};width:24px;height:24px;font-size:10px">{{ strtoupper(substr($task->creator->name, 0, 1)) }}</div>
                            {{ $task->creator->name }}
                        @else
                            <span style="color:var(--text3)">System</span>
                        @endif
                    </div>
                </div>
                @if($isUrgentTask)
                    <div class="td-info-box">
                        <div class="td-info-label">Client Delivery</div>
                        <div class="td-info-val" style="color:{{ $urgentDelivered ? 'var(--teal)' : ($urgentShared ? 'var(--blue)' : 'var(--yellow)') }}">
                            <span><i class="fas {{ $urgentDelivered ? 'fa-check-circle' : ($urgentShared ? 'fa-eye' : 'fa-clock') }}"></i></span>
                            {{ $urgentDelivered ? 'Delivered to client' : ($urgentShared ? 'Shared for admin view' : 'Work in progress') }}
                        </div>
                    </div>
                @endif
                @if($isDevTask)
                    <div class="td-info-box">
                        <div class="td-info-label">Dev Deadline</div>
                        <div class="td-info-val" style="color:{{ ($task->dev_deadline && $task->dev_deadline->isPast() && !in_array($task->status, ['completed','published'])) ? 'var(--red)' : 'var(--text)' }}">
                            <span><i class="fas fa-laptop-code"></i></span>
                            {{ $task->dev_deadline?->format('d M Y') ?? 'Not set' }}
                            @if($task->dev_deadline && $task->dev_deadline->isPast() && !in_array($task->status, ['completed','published']))
                                <span class="tag tag-red" style="font-size:9px;padding:2px 6px">OVERDUE</span>
                            @endif
                        </div>
                    </div>
                    @if($task->launch_date)
                        <div class="td-info-box">
                            <div class="td-info-label">Launch Date</div>
                            <div class="td-info-val"><span><i class="fas fa-rocket"></i></span> {{ $task->launch_date->format('d M Y') }}</div>
                        </div>
                    @endif
                    @if($task->project_start_date)
                        <div class="td-info-box">
                            <div class="td-info-label">Project Start</div>
                            <div class="td-info-val"><span><i class="fas fa-calendar-plus"></i></span> {{ $task->project_start_date->format('d M Y') }}</div>
                        </div>
                    @endif
                @else
                    <div class="td-info-box">
                        <div class="td-info-label">Deadline</div>
                        <div class="td-info-val" style="color:{{ $task->isOverdue() ? 'var(--red)' : 'var(--text)' }}">
                            <span><i class="fas fa-calendar-alt"></i></span>
                            {{ $task->deadline?->format('d M Y') ?? 'No deadline' }}
                            @if($task->isOverdue())
                                <span class="tag tag-red" style="font-size:9px;padding:2px 6px">OVERDUE</span>
                            @endif
                        </div>
                    </div>
                    @if($task->design_deadline)
                        <div class="td-info-box">
                            <div class="td-info-label">Design Deadline</div>
                            <div class="td-info-val"><span><i class="fas fa-palette"></i></span> {{ $task->design_deadline->format('d M Y') }}</div>
                        </div>
                    @endif
                    @if($task->post_date)
                        <div class="td-info-box">
                            <div class="td-info-label">Post Date</div>
                            <div class="td-info-val"><span><i class="fas fa-paper-plane"></i></span> {{ $task->post_date->format('d M Y') }}</div>
                        </div>
                    @endif
                @endif
            </div>

            {{-- ═══ SELECTED SOCIAL MEDIA LINKS (Dynamic) ═══ --}}
            @php
                $selectedSocialLinkIds = collect((array) ($task->selected_social_media_link_ids ?? []))
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->values();

                $availableSocialLinks = $task->client?->socialMediaLinks ?? collect();

                $selectedSocialLinks = $selectedSocialLinkIds->isNotEmpty()
                    ? $selectedSocialLinkIds
                        ->map(fn ($id) => $availableSocialLinks->firstWhere('id', $id))
                        ->filter()
                        ->values()
                    : ($task->socialMediaLink ? collect([$task->socialMediaLink]) : collect());
            @endphp
            @if($selectedSocialLinks->isNotEmpty())
                <div style="margin-bottom:20px;background:linear-gradient(135deg, rgba(79,109,240,0.07) 0%, rgba(79,109,240,0.02) 100%);border:1.5px solid rgba(79,109,240,0.16);border-radius:12px;padding:16px;position:relative;overflow:hidden">
                    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg, var(--primary), rgba(79,109,240,0.35))"></div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:12px">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--primary)">Selected Social Media Accounts</div>
                        <div style="font-size:11px;font-weight:700;color:var(--text3)">{{ $selectedSocialLinks->count() }} selected</div>
                    </div>

                    <div style="display:grid;gap:10px">
                        @foreach($selectedSocialLinks as $link)
                            @php
                                $linkInfo = $link->getPlatformIcon();
                                $accountLabel = $link->label ?: str_replace(['https://', 'http://', 'www.'], '', $link->url);
                            @endphp
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;background:#fff;border:1px solid {{ $linkInfo['color'] }}24;border-radius:12px;padding:12px 14px">
                                <div style="display:flex;align-items:center;gap:12px;min-width:0;flex:1">
                                    <div style="width:42px;height:42px;border-radius:12px;background:{{ $linkInfo['color'] }}15;border:1px solid {{ $linkInfo['color'] }}2e;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                        <i class="fa-brands {{ $linkInfo['icon'] }}" style="font-size:18px;color:{{ $linkInfo['color'] }}"></i>
                                    </div>
                                    <div style="min-width:0;display:grid;gap:2px">
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                            <span style="font-size:13px;font-weight:800;color:var(--text)">{{ ucfirst($link->platform) }}</span>
                                            @if($link->is_primary)
                                                <span style="font-size:9px;font-weight:800;background:linear-gradient(135deg,#F59E0B,#F97316);color:#fff;padding:2px 8px;border-radius:999px">Primary</span>
                                            @endif
                                        </div>
                                        <div style="font-size:12px;color:var(--text2);font-weight:600;overflow-wrap:anywhere">{{ $accountLabel }}</div>
                                    </div>
                                </div>
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;background:{{ $linkInfo['color'] }}12;border:1px solid {{ $linkInfo['color'] }}30;border-radius:8px;font-size:11px;font-weight:700;color:{{ $linkInfo['color'] }};text-decoration:none;white-space:nowrap" onmouseover="this.style.background='{{ $linkInfo['color'] }}20';this.style.transform='translateY(-1px)'" onmouseout="this.style.background='{{ $linkInfo['color'] }}12';this.style.transform=''">
                                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px"></i> Visit
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ═══ CLIENT SOCIAL MEDIA ACCOUNTS (Multi-Account) ═══ --}}
            @if($task->client->socialMediaLinks->count())
            <div style="margin-bottom:20px">
                <div class="td-section-title"><span><i class="fa-solid fa-share-nodes"></i></span> Client Social Accounts</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                    @foreach($task->client->socialMediaLinks as $link)
                        @php $sInfo = $link->getPlatformIcon(); @endphp
                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--card2);border:1px solid var(--border);border-radius:8px;font-size:12px;font-weight:600;color:{{ $sInfo['color'] }};text-decoration:none;transition:all 0.15s" onmouseover="this.style.background='{{ $sInfo['color'] }}10';this.style.borderColor='{{ $sInfo['color'] }}40'" onmouseout="this.style.background='var(--card2)';this.style.borderColor='var(--border)'">
                            <i class="fa-brands {{ $sInfo['icon'] }}"></i>
                            <span>{{ ucfirst($link->platform) }}{{ $link->label ? ' · ' . $link->label : '' }}</span>
                            @if($link->is_primary)
                                <span style="color:#F59E0B;font-size:10px" title="Primary">★</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
            @elseif($task->client->website)
            <div style="margin-bottom:20px">
                <div class="td-section-title"><span><i class="fa-solid fa-share-nodes"></i></span> Client Social Media</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px">
                    <a href="{{ $task->client->website }}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:var(--card2);border:1px solid var(--border);border-radius:8px;font-size:12px;font-weight:600;color:#6366F1;text-decoration:none;transition:all 0.15s">
                        <i class="fa-solid fa-globe"></i> Website
                    </a>
                </div>
            </div>
            @endif

            @php
                $urgentBrief = $task->is_urgent_task
                    ? trim((string) ($task->brief ?: $task->caption ?: ''))
                    : '';
                $showUrgentCaption = $task->is_urgent_task
                    && trim((string) ($task->caption ?? '')) !== ''
                    && trim((string) $task->caption) !== $urgentBrief;
            @endphp

            {{-- Content Brief (strategist's direction for the task) --}}
            @if($task->is_urgent_task && $urgentBrief !== '')
                <div style="margin-bottom:20px">
                    <div class="td-section-title"><span><i class="fa-solid fa-bolt"></i></span> Urgent Brief</div>
                    <div class="td-caption">{{ $urgentBrief }}</div>
                </div>
            @elseif($task->brief)
                <div style="margin-bottom:20px">
                    <div class="td-section-title"><span><i class="fa-regular fa-file-lines"></i></span> Content Brief</div>
                    <div class="td-caption">{{ $task->brief }}</div>
                </div>
            @endif

            {{-- Caption (the social post caption) --}}
            @if($task->caption && (!$task->is_urgent_task || $showUrgentCaption))
                <div style="margin-bottom:20px">
                    <div class="td-section-title"><span><i class="fas fa-align-left"></i></span> Caption</div>
                    <div class="td-caption">{{ $task->caption }}</div>
                </div>
            @endif

            {{-- Hashtags --}}
            @if($task->hashtags)
                <div style="margin-bottom:20px">
                    <div class="td-section-title"><span>#️⃣</span> Hashtags</div>
                    <div class="td-hashtags">
                        @foreach(explode(' ', $task->hashtags) as $ht)
                            @if(trim($ht))
                                <span class="td-hashtag">{{ $ht }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Reference Links --}}
            @if(!empty($task->reference_links))
                <div style="margin-bottom:6px">
                    <div class="td-section-title"><span><i class="fas fa-link"></i></span> Reference Links</div>
                    @foreach((is_array($task->reference_links) ? $task->reference_links : json_decode($task->reference_links, true)) ?? [] as $link)
                        <a href="{{ $link }}" target="_blank" rel="noopener" class="td-ref-link">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            {{ Str::limit($link, 70) }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Dev Details Card --}}
            @if($isDevTask)
                <div style="background:linear-gradient(135deg,#6366F108,#818CF808);border:1.5px solid #6366F130;border-radius:12px;padding:16px;margin-top:16px;position:relative;overflow:hidden">
                    <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#6366F1,#818CF8)"></div>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
                        <i class="fas fa-laptop-code" style="color:#6366F1;font-size:14px"></i>
                        <span style="font-size:12px;font-weight:700;color:#6366F1;text-transform:uppercase;letter-spacing:0.3px">Developer Details</span>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        @if($task->tech_stack)
                            <div>
                                <div style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:4px">Tech Stack</div>
                                <div style="font-size:13px;font-weight:600;color:var(--text)">{{ $task->tech_stack }}</div>
                            </div>
                        @endif
                        @if($task->preferred_tech)
                            <div>
                                <div style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:4px">Preferred Tech</div>
                                <div style="font-size:13px;color:var(--text)">{{ $task->preferred_tech }}</div>
                            </div>
                        @endif
                        @if($task->business_type)
                            <div>
                                <div style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:4px">Business Type</div>
                                <div style="font-size:13px;color:var(--text)">{{ $task->business_type }}</div>
                            </div>
                        @endif
                        @if($task->modules)
                            <div>
                                <div style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:4px">Modules</div>
                                <div style="font-size:13px;color:var(--text)">{{ $task->modules }}</div>
                            </div>
                        @endif
                        <div>
                            <div style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:4px">Domain Purchased</div>
                            <div style="font-size:13px;color:var(--text)">
                                @if($task->domain_purchased)
                                    <span style="color:#10B981;font-weight:600"><i class="fas fa-check-circle"></i> Yes</span>
                                @else
                                    <span style="color:var(--text3)">No</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:4px">Hosting Access</div>
                            <div style="font-size:13px;color:var(--text)">
                                @if($task->hosting_access)
                                    <span style="color:#10B981;font-weight:600"><i class="fas fa-check-circle"></i> Yes</span>
                                @else
                                    <span style="color:var(--text3)">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($task->project_notes)
                        <div style="margin-top:12px;padding-top:12px;border-top:1px solid #6366F120">
                            <div style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px">Project Notes</div>
                            <div style="font-size:13px;color:var(--text2);line-height:1.6;white-space:pre-wrap">{{ $task->project_notes }}</div>
                        </div>
                    @endif
                    @if($task->dev_submission_link)
                        <div style="margin-top:12px;padding-top:12px;border-top:1px solid #6366F120">
                            <div style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px">Submission Link</div>
                            <a href="{{ $task->dev_submission_link }}" target="_blank" rel="noopener" style="font-size:13px;color:#6366F1;word-break:break-all">{{ $task->dev_submission_link }}</a>
                        </div>
                    @endif
                    @if($task->dev_submission_notes)
                        <div style="margin-top:12px;padding-top:12px;border-top:1px solid #6366F120">
                            <div style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:6px">Submission Notes</div>
                            <div style="font-size:13px;color:var(--text2);line-height:1.6;white-space:pre-wrap">{{ $task->dev_submission_notes }}</div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- ====== MEDIA & ATTACHMENTS (Video & Image Review) ====== --}}
        <div class="td-card">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                <div class="td-section-title" style="margin-bottom:0">
                    <span><i class="fas fa-images" style="color:var(--primary)"></i></span>
                    <span>Media & Attachments</span>
                    @if($task->media->count())
                        <span style="font-size:11px;font-weight:700;background:rgba(79,109,240,0.12);color:var(--primary);padding:2px 8px;border-radius:99px">{{ $task->media->count() }}</span>
                    @endif
                </div>
                @if($task->media->count() > 1)
                    <button type="button" class="btn-sec" onclick="adminDownloadAllMedia()" style="font-size:11px;font-weight:600;padding:5px 12px;display:inline-flex;align-items:center;gap:6px;border-radius:6px;cursor:pointer">
                        <i class="fa-solid fa-cloud-arrow-down" style="color:#10B981"></i> Download All ({{ $task->media->count() }})
                    </button>
                @endif
            </div>

            @if($task->media->count())
                <div class="td-media-grid">
                    @foreach($task->media as $index => $m)
                        @php
                            $isVid = $m->isVideo();
                            $isPdf = $m->isPdf();
                            $mUrl = $m->getUrl();
                            $mName = $m->name ?: $m->file_name ?: ('Media File #' . ($index + 1));
                        @endphp
                        <div class="td-media-card">
                            <div class="td-thumb-wrap" onclick="openAdminLightbox({{ $index }})" title="Click to view full preview">
                                @if($isVid)
                                    <video src="{{ $mUrl }}" muted preload="metadata" class="td-vid-thumb"></video>
                                    <div style="position:absolute;font-size:22px;color:#fff;pointer-events:none;background:rgba(0,0,0,0.45);width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(2px)">
                                        <i class="fa-solid fa-play" style="margin-left:3px"></i>
                                    </div>
                                    <span class="td-thumb-badge"><i class="fa-solid fa-video"></i> Video</span>
                                @elseif($isPdf)
                                    <div style="height:100%;width:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--card2)">
                                        <i class="fa-solid fa-file-pdf" style="font-size:36px;color:#EF4444"></i>
                                        <span style="font-size:10px;font-weight:700;color:var(--text3);margin-top:6px">PDF Document</span>
                                    </div>
                                    <span class="td-thumb-badge" style="background:rgba(239,68,68,0.85)"><i class="fa-solid fa-file-pdf"></i> PDF</span>
                                @elseif($m->isImage())
                                    <img src="{{ $mUrl }}" alt="{{ $mName }}" loading="lazy">
                                    <span class="td-thumb-badge"><i class="fa-solid fa-image"></i> Image</span>
                                @else
                                    <div style="height:100%;width:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--card2)">
                                        <i class="fa-solid fa-file" style="font-size:36px;color:var(--text3)"></i>
                                    </div>
                                    <span class="td-thumb-badge"><i class="fa-solid fa-paperclip"></i> File</span>
                                @endif

                                <div class="td-thumb-overlay">
                                    <button type="button" class="td-icon-btn td-icon-btn-view" onclick="event.stopPropagation(); openAdminLightbox({{ $index }})" title="Review Video/File">
                                        <i class="fa-solid fa-expand"></i>
                                    </button>
                                    <a href="{{ $mUrl }}" download="{{ $mName }}" class="td-icon-btn td-icon-btn-dl" title="Download {{ $mName }}" onclick="event.stopPropagation()">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="td-media-meta">
                                <div style="min-width:0;flex:1">
                                    <div class="td-media-filename" title="{{ $mName }}">{{ $mName }}</div>
                                    @if($m->size)
                                        <div style="font-size:10px;color:var(--text3)">{{ $m->getFormattedSize() }}</div>
                                    @endif
                                </div>
                                <a href="{{ $mUrl }}" download="{{ $mName }}" class="td-dl-chip" title="Download File" onclick="event.stopPropagation()">
                                    <i class="fa-solid fa-arrow-down-to-line"></i> Download
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="td-media-empty">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size:28px;margin-bottom:8px;display:block;opacity:0.5"></i>
                    No media uploaded yet
                </div>
            @endif
        </div>

        {{-- Social Media Posts / Proof Links --}}
        @if($task->socialMediaPosts->count() || ($task->status === 'completed' || $task->status === 'published'))
            <div class="td-card">
                <div class="td-section-title"><span><i class="fas fa-share-alt"></i></span> Publishing & Proof Links</div>

                @php
                    $progress = $task->getPublishingProgress();
                    $pct = $progress['total'] > 0 ? round(($progress['posted'] / $progress['total']) * 100) : 0;
                @endphp
                <div class="td-pub-progress">
                    <div class="td-pub-text">{{ $progress['posted'] }}/{{ $progress['total'] }} platforms</div>
                    <div class="td-pub-bar">
                        <div class="td-pub-fill" style="width:{{ $pct }}%;background:{{ $pct >= 100 ? 'var(--teal)' : 'var(--blue)' }}"></div>
                    </div>
                    <div class="td-pub-text">{{ $pct }}%</div>
                </div>

                @forelse($task->socialMediaPosts as $post)
                    <div class="td-post-row">
                        <div class="td-post-icon" style="background:{{ $post->platform_color }}18">{{ $post->platform_icon }}</div>
                        <div class="td-post-info">
                            <div class="td-post-platform">{{ $post->platform_label }}</div>
                            <div class="td-post-meta">
                                {{ ucfirst($post->post_type ?? 'Post') }}
                                @if($post->poster) · by {{ $post->poster->name }}@endif
                                @if($post->posted_at) · {{ $post->posted_at->format('d M Y') }}@endif
                            </div>
                        </div>
                        @if($post->post_url)
                            <a href="{{ $post->post_url }}" target="_blank" rel="noopener" class="td-post-link">
                                View Post <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px"></i>
                            </a>
                        @endif
                    </div>
                @empty
                    <div style="padding:16px;text-align:center;color:var(--text3);font-size:12.5px">No posts published yet</div>
                @endforelse
            </div>
        @endif
    </div>

    {{-- ============ RIGHT COLUMN ============ --}}
    <div>
        {{-- Status Controls --}}
        <div class="td-card">
            {{-- Current Status Display --}}
            @php
                $statusConfig = [
                    'todo'             => ['icon' => '<i class="fas fa-clipboard-list"></i>', 'label' => 'To Do',     'color' => 'var(--text3)',  'bg' => 'var(--card2)'],
                    'inprogress'       => ['icon' => '<i class="fas fa-bolt"></i>', 'label' => 'In Progress','color' => 'var(--blue)',   'bg' => '#3B82F610'],
                    'review'           => ['icon' => '<i class="fas fa-eye"></i>', 'label' => 'In Review',  'color' => '#F59E0B',      'bg' => '#F59E0B10'],
                    'pending_approval' => ['icon' => '<i class="fas fa-hourglass-half"></i>', 'label' => 'Pending Approval','color' => '#F59E0B', 'bg' => '#F59E0B10'],
                    'completed'        => ['icon' => '<i class="fas fa-check-circle"></i>', 'label' => 'Completed',  'color' => 'var(--teal)',   'bg' => '#10B98110'],
                    'published'        => ['icon' => '<i class="fas fa-rocket"></i>', 'label' => 'Published',  'color' => 'var(--purple)', 'bg' => '#8B5CF610'],
                ];
                if ($isUrgentTask) {
                    $statusConfig['completed'] = ['icon' => '<i class="fas fa-truck-fast"></i>', 'label' => 'Delivered', 'color' => 'var(--teal)', 'bg' => '#10B98110'];
                    $statusConfig['published'] = $statusConfig['completed'];
                }

                $workflowStatus = $isUrgentTask && $task->status === 'published'
                    ? 'completed'
                    : $task->status;
                $cur = $statusConfig[$workflowStatus] ?? $statusConfig['todo'];

                $stepOrder = ($isUrgentTask || $isDevTask)
                    ? ['todo', 'inprogress', 'review', 'pending_approval', 'completed']
                    : ['todo', 'inprogress', 'review', 'pending_approval', 'completed', 'published'];
                $currentIndex = array_search($workflowStatus, $stepOrder, true);
                $currentIndex = $currentIndex === false ? 0 : $currentIndex;

                $steps = $isUrgentTask
                    ? [
                        ['key' => 'todo',             'icon' => '<i class="fas fa-clipboard-list"></i>', 'short' => 'To Do'],
                        ['key' => 'inprogress',       'icon' => '<i class="fas fa-bolt"></i>', 'short' => 'In Progress'],
                        ['key' => 'review',           'icon' => '<i class="fas fa-eye"></i>', 'short' => 'Review'],
                        ['key' => 'pending_approval', 'icon' => '<i class="fas fa-hourglass-half"></i>', 'short' => 'Approval'],
                        ['key' => 'completed',        'icon' => '<i class="fas fa-truck-fast"></i>', 'short' => 'Delivered'],
                    ]
                    : ($isDevTask
                        ? [
                            ['key' => 'todo',             'icon' => '<i class="fas fa-clipboard-list"></i>', 'short' => 'To Do'],
                            ['key' => 'inprogress',       'icon' => '<i class="fas fa-laptop-code"></i>', 'short' => 'In Dev'],
                            ['key' => 'review',           'icon' => '<i class="fas fa-eye"></i>', 'short' => 'Review'],
                            ['key' => 'pending_approval', 'icon' => '<i class="fas fa-hourglass-half"></i>', 'short' => 'Approval'],
                            ['key' => 'completed',        'icon' => '<i class="fas fa-check-circle"></i>', 'short' => 'Done'],
                        ]
                        : [
                            ['key' => 'todo',             'icon' => '<i class="fas fa-clipboard-list"></i>', 'short' => 'To Do'],
                            ['key' => 'inprogress',       'icon' => '<i class="fas fa-bolt"></i>', 'short' => 'In Progress'],
                            ['key' => 'review',           'icon' => '<i class="fas fa-eye"></i>', 'short' => 'Review'],
                            ['key' => 'pending_approval', 'icon' => '<i class="fas fa-hourglass-half"></i>', 'short' => 'Approval'],
                            ['key' => 'completed',        'icon' => '<i class="fas fa-check-circle"></i>', 'short' => 'Done'],
                            ['key' => 'published',        'icon' => '<i class="fas fa-rocket"></i>', 'short' => 'Published'],
                        ]);
            @endphp

            <div class="td-status-current" style="background:{{ $cur['bg'] }}; border:1px solid {{ $cur['color'] }}30">
                <div class="td-status-current-icon" style="background:{{ $cur['color'] }}20">{!! $cur['icon'] !!}</div>
                <div class="td-status-current-info">
                    <div class="td-status-current-label" style="color:{{ $cur['color'] }}">Current Status</div>
                    <div class="td-status-current-val" style="color:{{ $cur['color'] }}">{{ $cur['label'] }}</div>
                </div>
                @if($task->isOverdue())
                    <span class="tag tag-red" style="font-size:10px;padding:4px 10px;animation:td-pulse-urgent 2s infinite"><i class="fas fa-exclamation-triangle"></i> OVERDUE</span>
                @endif
            </div>

            {{-- Workflow Stepper (Read-Only) --}}
            <div class="td-section-title" style="font-size:11px;color:var(--text3);margin-bottom:10px"><i class="fa-solid fa-route" style="font-size:10px"></i> Workflow — view only</div>
            <div class="td-stepper">
                @foreach($steps as $i => $step)
                    @php
                        $stepClass = '';
                        if ($i < $currentIndex) $stepClass = 'past';
                        elseif ($i == $currentIndex) {
                            if (in_array($step['key'], ['completed', 'published'])) $stepClass = 'current-done';
                            elseif (in_array($step['key'], ['review', 'pending_approval'])) $stepClass = 'current-review';
                            else $stepClass = 'current';
                        }
                    @endphp
                    <div class="td-step {{ $stepClass }}" style="cursor:default" title="{{ $step['short'] }}">
                        <div class="td-step-dot-wrap">
                            <div class="td-step-dot">
                                @if($i < $currentIndex)
                                    <i class="fa-solid fa-check" style="font-size:12px"></i>
                                @else
                                    {!! $step['icon'] !!}
                                @endif
                            </div>
                            @if($i < count($steps) - 1)
                                <div class="td-step-line"></div>
                            @endif
                        </div>
                        <div class="td-step-label">{{ $step['short'] }}</div>
                    </div>
                @endforeach
            </div>

            <hr class="divider">

            {{-- Priority --}}
            <div class="td-section-title" style="font-size:12px;margin-bottom:10px"><i class="fa-solid fa-flag" style="font-size:11px"></i> Priority</div>
            <div class="td-priority-row">
                @foreach(['normal' => ['label' => 'Normal', 'icon' => '<i class="fas fa-circle" style="color:#10B981;font-size:8px"></i>'], 'high' => ['label' => 'High', 'icon' => '<i class="fas fa-circle" style="color:#F97316;font-size:8px"></i>'], 'urgent' => ['label' => 'Urgent', 'icon' => '<i class="fas fa-circle" style="color:#EF4444;font-size:8px"></i>']] as $priVal => $priConf)
                    <form method="POST" action="{{ route('admin.tasks.update', $task) }}" style="flex:1;display:flex">
                        @csrf @method('PATCH')
                        <input type="hidden" name="priority" value="{{ $priVal }}">
                        <button type="submit" class="td-priority-btn {{ $task->priority === $priVal ? 'pri-active-' . $priVal : '' }}" {{ $task->priority === $priVal ? 'disabled' : '' }}>
                            {!! $priConf['icon'] !!} {{ $priConf['label'] }}
                        </button>
                    </form>
                @endforeach
            </div>

            @if($task->deadline)
                <hr class="divider">
                {{-- Deadline Countdown --}}
                @php
                    $daysLeft = now()->startOfDay()->diffInDays($task->deadline, false);
                @endphp
                <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:{{ $daysLeft < 0 ? '#EF444410' : ($daysLeft <= 2 ? '#F59E0B10' : 'var(--card2)') }};border-radius:var(--radius-sm);border:1px solid {{ $daysLeft < 0 ? '#EF444430' : ($daysLeft <= 2 ? '#F59E0B30' : 'var(--border)') }}">
                    <span style="font-size:20px">{!! $daysLeft < 0 ? '<i class="fas fa-fire" style="color:var(--red)"></i>' : ($daysLeft <= 2 ? '<i class="fas fa-clock" style="color:#F59E0B"></i>' : '<i class="fas fa-calendar-alt" style="color:var(--text3)"></i>') !!}</span>
                    <div style="flex:1">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text3)">Deadline</div>
                        <div style="font-size:13px;font-weight:600;color:{{ $daysLeft < 0 ? 'var(--red)' : 'var(--text)' }}">
                            {{ $task->deadline->format('d M Y') }}
                            <span style="font-weight:400;color:var(--text3);margin-left:4px">
                                @if($daysLeft < 0)
                                    ({{ abs($daysLeft) }} {{ Str::plural('day', abs($daysLeft)) }} overdue)
                                @elseif($daysLeft == 0)
                                    (Due today)
                                @else
                                    ({{ $daysLeft }} {{ Str::plural('day', $daysLeft) }} left)
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Timeline --}}
        <div class="td-card">
            <div class="td-section-title"><span><i class="fas fa-clock"></i></span> Timeline</div>
            <div class="td-timeline">
                <div class="td-timeline-item done">
                    <div class="td-timeline-label">Created</div>
                    <div class="td-timeline-val">{{ $task->created_at->format('d M Y, h:i A') }}</div>
                </div>
                @if($task->started_at)
                    <div class="td-timeline-item done">
                        <div class="td-timeline-label">Started</div>
                        <div class="td-timeline-val">{{ $task->started_at->format('d M Y, h:i A') }}</div>
                    </div>
                @endif
                @if($task->submitted_at)
                    <div class="td-timeline-item done">
                        <div class="td-timeline-label">Submitted for Review</div>
                        <div class="td-timeline-val">{{ $task->submitted_at->format('d M Y, h:i A') }}</div>
                    </div>
                @endif
                @if($task->admin_approved_at)
                    <div class="td-timeline-item {{ $task->admin_approval_status === 'approved' ? 'done' : '' }}">
                        <div class="td-timeline-label">{{ $task->admin_approval_status === 'approved' ? 'Approved' : 'Reviewed' }}</div>
                        <div class="td-timeline-val">{{ $task->admin_approved_at->format('d M Y, h:i A') }} by {{ $task->adminApprover?->name ?? 'Admin' }}</div>
                    </div>
                @endif
                @if($task->completed_at)
                    <div class="td-timeline-item done">
                        <div class="td-timeline-label">Completed</div>
                        <div class="td-timeline-val">{{ $task->completed_at->format('d M Y, h:i A') }}</div>
                    </div>
                @endif
                @if(!$task->completed_at && !$task->submitted_at && $task->status === 'inprogress')
                    <div class="td-timeline-item active">
                        <div class="td-timeline-label">In Progress</div>
                        <div class="td-timeline-val" style="color:var(--blue)">Currently being worked on</div>
                    </div>
                @elseif(!$task->completed_at && !$task->started_at && $task->status === 'todo')
                    <div class="td-timeline-item active">
                        <div class="td-timeline-label">Waiting</div>
                        <div class="td-timeline-val" style="color:var(--text3)">Not yet started</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Team Members --}}
        @if($task->employees->count())
            <div class="td-card">
                <div class="td-section-title"><span><i class="fas fa-users"></i></span> Team ({{ $task->employees->count() }})</div>
                @foreach($task->employees as $emp)
                    <div class="td-team-row">
                        <div class="td-team-av" style="background:{{ $emp->avatar_color ?? 'var(--purple)' }}">{{ strtoupper(substr($emp->name, 0, 1)) }}</div>
                        <div class="td-team-info">
                            <div class="td-team-name">{{ $emp->name }}</div>
                            <div class="td-team-role">{{ ucfirst($emp->pivot->role ?? 'Member') }}</div>
                        </div>
                        @if($emp->pivot->estimated_hours || $emp->pivot->actual_hours)
                            <div class="td-team-hours">
                                @if($emp->pivot->actual_hours){{ $emp->pivot->actual_hours }}h done @endif
                                @if($emp->pivot->estimated_hours) / {{ $emp->pivot->estimated_hours }}h est @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Comments --}}
        <div class="td-card">
            <div class="td-section-title"><span><i class="fas fa-comments"></i></span> Comments ({{ $task->comments->count() }})</div>
            @forelse($task->comments as $comment)
                <div style="background:var(--card2);padding:12px;border-radius:var(--radius-sm);margin-bottom:8px;border:1px solid var(--border)">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px">
                        <div class="av-sm" style="background:{{ $comment->user->avatar_color ?? 'var(--primary)' }};width:22px;height:22px;font-size:9px">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</div>
                        <span style="font-size:12px;font-weight:600;color:var(--text)">{{ $comment->user->name }}</span>
                        <span style="font-size:11px;color:var(--text3)">· {{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div style="font-size:13px;line-height:1.6;color:var(--text2)">{{ $comment->body }}</div>
                </div>
            @empty
                <div style="padding:20px;text-align:center;color:var(--text3);font-size:12.5px">No comments yet</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Modern Admin Lightbox Modal --}}
<div class="td-lightbox-modal" id="adminLightbox" onclick="closeAdminLightbox(event)">
    <div class="td-lb-header" onclick="event.stopPropagation()">
        <div class="td-lb-title-wrap">
            <span class="td-lb-counter" id="adminLightboxIndex">1 / 1</span>
            <span class="td-lb-title" id="adminLightboxTitle">Media Preview</span>
        </div>
        <div class="td-lb-actions">
            <a href="#" download id="adminLightboxDownload" class="td-lb-btn td-lb-btn-dl" title="Download this file">
                <i class="fa-solid fa-arrow-down-to-line"></i> Download
            </a>
            <a href="#" target="_blank" rel="noopener" id="adminLightboxNewTab" class="td-lb-btn td-lb-btn-sec" title="Open original in new tab">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open
            </a>
            <button type="button" class="td-lb-close" onclick="closeAdminLightbox()" title="Close (Esc)">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>
    <div class="td-lb-body">
        <button type="button" class="td-lb-nav prev" id="adminLightboxPrev" onclick="event.stopPropagation(); adminLightboxNav(-1)" title="Previous (←)">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="td-lb-content" id="adminLightboxContent" onclick="event.stopPropagation()"></div>
        <button type="button" class="td-lb-nav next" id="adminLightboxNext" onclick="event.stopPropagation(); adminLightboxNav(1)" title="Next (→)">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </div>
</div>

<script>
const adminMediaList = [
    @foreach($task->media as $m)
        {
            url: '{{ $m->getUrl() }}',
            name: '{{ addslashes($m->name ?: $m->file_name ?: "Media File") }}',
            isVideo: {{ $m->isVideo() ? 'true' : 'false' }},
            isPdf: {{ $m->isPdf() ? 'true' : 'false' }},
            size: '{{ $m->getFormattedSize() }}'
        },
    @endforeach
];

let adminCurrentMediaIdx = 0;

function openAdminLightbox(index) {
    if (!adminMediaList || !adminMediaList.length) return;
    adminCurrentMediaIdx = Math.max(0, Math.min(index, adminMediaList.length - 1));
    renderAdminLightbox();

    const wrap = document.getElementById('adminLightbox');
    if (wrap) {
        wrap.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function renderAdminLightbox() {
    const wrap = document.getElementById('adminLightbox');
    const content = document.getElementById('adminLightboxContent');
    const counter = document.getElementById('adminLightboxIndex');
    const title = document.getElementById('adminLightboxTitle');
    const dlBtn = document.getElementById('adminLightboxDownload');
    const newTabBtn = document.getElementById('adminLightboxNewTab');
    const prevBtn = document.getElementById('adminLightboxPrev');
    const nextBtn = document.getElementById('adminLightboxNext');

    if (!wrap || !content || !adminMediaList.length) return;

    const item = adminMediaList[adminCurrentMediaIdx];
    if (!item) return;

    if (counter) counter.textContent = (adminCurrentMediaIdx + 1) + ' / ' + adminMediaList.length;
    if (title) title.textContent = item.name + (item.size ? ' (' + item.size + ')' : '');
    if (dlBtn) {
        dlBtn.href = item.url;
        dlBtn.setAttribute('download', item.name);
    }
    if (newTabBtn) newTabBtn.href = item.url;

    if (prevBtn) prevBtn.style.display = (adminMediaList.length > 1) ? 'flex' : 'none';
    if (nextBtn) nextBtn.style.display = (adminMediaList.length > 1) ? 'flex' : 'none';

    if (item.isVideo) {
        content.innerHTML = '<video src="' + item.url + '" controls autoplay playsinline preload="auto" style="max-width:92vw;max-height:82vh;border-radius:10px;background:#000;box-shadow:0 20px 50px rgba(0,0,0,0.6)"></video>';
    } else if (item.isPdf) {
        content.innerHTML = '<iframe src="' + item.url + '" style="width:85vw;height:82vh;border:none;border-radius:10px;background:#fff;box-shadow:0 20px 50px rgba(0,0,0,0.6)"></iframe>';
    } else {
        content.innerHTML = '<img src="' + item.url + '" alt="' + item.name + '">';
    }
}

function adminLightboxNav(dir) {
    if (!adminMediaList.length) return;
    adminCurrentMediaIdx = (adminCurrentMediaIdx + dir + adminMediaList.length) % adminMediaList.length;
    renderAdminLightbox();
}

function closeAdminLightbox(e) {
    if (e && e.target && e.target.closest && (e.target.closest('.td-lb-header') || e.target.closest('.td-lb-nav') || e.target.closest('.td-lb-content'))) {
        return;
    }
    const wrap = document.getElementById('adminLightbox');
    if (!wrap) return;
    wrap.classList.remove('open');
    const content = document.getElementById('adminLightboxContent');
    if (content) content.innerHTML = '';
    document.body.style.overflow = '';
}

function adminDownloadAllMedia() {
    if (!adminMediaList || !adminMediaList.length) return;

    adminMediaList.forEach((media, idx) => {
        setTimeout(() => {
            const link = document.createElement('a');
            link.href = media.url;
            link.download = media.name || ('Media_' + (idx + 1));
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }, idx * 350);
    });
}

function setStatus(status) {
    const currentStatus = '{{ $task->status }}';
    if (status === currentStatus) return;

    const labels = {
        'todo': 'To Do', 'inprogress': 'In Progress', 'review': 'Review',
        'pending_approval': 'Pending Approval', 'completed': 'Completed', 'published': 'Published'
    };
    if (!confirm('Change status to "' + labels[status] + '"?')) return;

    document.getElementById('statusInput').value = status;
    document.getElementById('statusForm').submit();
}

document.addEventListener('keydown', function(e) {
    const wrap = document.getElementById('adminLightbox');
    if (!wrap || !wrap.classList.contains('open')) return;

    if (e.key === 'Escape') closeAdminLightbox();
    else if (e.key === 'ArrowLeft') adminLightboxNav(-1);
    else if (e.key === 'ArrowRight') adminLightboxNav(1);
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.td-vid-thumb').forEach(function(v) {
        v.addEventListener('loadedmetadata', function() { v.currentTime = 0.5; });
    });
});
</script>
@endsection
