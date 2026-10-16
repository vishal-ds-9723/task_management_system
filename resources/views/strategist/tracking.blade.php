@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/strategist-tracking.css') }}?v={{ time() }}">
<style>
/* Additional helper styles for modal overlays & inline drawer actions */
.trk-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 10000;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.trk-modal-box {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    width: 100%;
    max-width: 480px;
    padding: 24px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    position: relative;
    animation: trkModalIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes trkModalIn {
    from { opacity: 0; transform: scale(0.96) translateY(-10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.trk-modal-close {
    position: absolute;
    right: 18px;
    top: 16px;
    font-size: 20px;
    color: var(--text3);
    cursor: pointer;
    line-height: 1;
    transition: color 0.15s;
}
.trk-modal-close:hover { color: var(--text); }
.trk-modal-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}
.trk-modal-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(99, 102, 241, 0.12);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
}
.trk-modal-title { font-size: 17px; font-weight: 800; color: var(--text); }
.trk-modal-sub { font-size: 12px; color: var(--text3); margin-top: 2px; }
.trk-modal-label { font-size: 11px; font-weight: 700; color: var(--text2); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; display: block; }
.trk-modal-select, .trk-modal-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: 9px;
    background: var(--card2);
    font-size: 13px;
    color: var(--text);
    font-family: inherit;
    margin-bottom: 16px;
    box-sizing: border-box;
}
.trk-modal-select:focus, .trk-modal-textarea:focus { outline: none; border-color: var(--primary); }
.trk-modal-actions { display: flex; gap: 8px; justify-content: flex-end; }
.trk-modal-btn-ghost {
    padding: 9px 16px;
    border-radius: 8px;
    background: var(--card2);
    color: var(--text2);
    border: 1px solid var(--border);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
}
.trk-modal-btn-primary {
    padding: 9px 18px;
    border-radius: 8px;
    background: var(--primary);
    color: #fff;
    border: none;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="trk-header">
    <div class="trk-header-left">
        <div class="trk-title">
            <i class="fa-solid fa-list-check" style="color:var(--primary)"></i>
            Task Tracking &amp; Situational Command
        </div>
        <div class="trk-subtitle" id="trackingSubtitle">
            Live production oversight &mdash; {{ $tasks->total() }} total task{{ $tasks->total() !== 1 ? 's' : '' }}
        </div>
    </div>
    <div class="trk-header-right" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        {{-- Scope Toggle --}}
        {{-- Scope Toggle --}}
        <div class="trk-scope-toggle">
            <button type="button" class="trk-scope-btn {{ ($scope ?? 'all') === 'all' ? 'is-active' : '' }}" data-scope="all" onclick="setTrackingScope('all')">
                <i class="fa-solid fa-users"></i> All Tasks
            </button>
            <button type="button" class="trk-scope-btn {{ ($scope ?? 'all') === 'my' ? 'is-active' : '' }}" data-scope="my" onclick="setTrackingScope('my')">
                <i class="fa-solid fa-user-pen"></i> My Created
            </button>
        </div>

        {{-- View Mode Toggle (Dense Table vs Card Grid) --}}
        <div class="trk-view-mode-toggle">
            <button type="button" class="trk-view-btn is-active" id="trkViewTableBtn" onclick="setTrackingViewMode('table')" title="Dense Table View">
                <i class="fa-solid fa-table-list"></i> Table
            </button>
            <button type="button" class="trk-view-btn" id="trkViewGridBtn" onclick="setTrackingViewMode('grid')" title="Visual Card Grid">
                <i class="fa-solid fa-table-cells-large"></i> Cards
            </button>
        </div>

        <a href="{{ route('strategist.calendar') }}" class="trk-header-btn trk-btn-ghost"><i class="fa-regular fa-calendar"></i> Calendar</a>
        <a href="{{ route('strategist.create-task') }}" class="trk-header-btn trk-btn-primary"><i class="fa-solid fa-plus"></i> New Task</a>
    </div>
</div>

{{-- Summary Stats Row (Enhanced with In Revision card) --}}
<div class="trk-stats-row" id="trkStatsRow">
    @include('strategist.partials.tracking-stats')
</div>

{{-- Situational Awareness Banner: Active Urgent Designer Tasks while in My Created Scope --}}
<div id="trkUrgentAlertContainer">
    @include('strategist.partials.tracking-urgent-alert')
</div>

{{-- Filter Bar --}}
<div class="trk-filter-bar">
    <form method="GET" action="{{ route('strategist.tracking') }}" class="trk-filter-form" id="trkFilterForm" onsubmit="event.preventDefault(); submitFilterFormAjax();">
        <input type="hidden" name="scope" id="trkScopeInput" value="{{ $scope ?? 'all' }}">
        
        <div class="trk-search-wrap" style="position:relative">
            <i class="fa-solid fa-magnifying-glass trk-search-icon"></i>
            <input type="text" name="search" id="trkSearchInput" placeholder="Search tasks, brief, copy, or client in real-time…" value="{{ request('search') }}" class="trk-search-input" autocomplete="off" style="padding-right:32px">
            <button type="button" id="trkSearchClearBtn" style="display:{{ request('search') ? 'block' : 'none' }};position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;padding:4px;font-size:12px;line-height:1" title="Clear search">
                <i class="fa-solid fa-circle-xmark"></i>
            </button>
        </div>

        <div class="trk-filter-selects">
            <select name="sort" class="trk-select" id="trkSortSelect">
                <option value="deadline_asc" {{ request('sort', 'deadline_asc') == 'deadline_asc' ? 'selected' : '' }}>Deadline ↑</option>
                <option value="deadline_desc" {{ request('sort') == 'deadline_desc' ? 'selected' : '' }}>Deadline ↓</option>
                <option value="post_date_asc" {{ request('sort') == 'post_date_asc' ? 'selected' : '' }}>Post Date ↑</option>
                <option value="post_date_desc" {{ request('sort') == 'post_date_desc' ? 'selected' : '' }}>Post Date ↓</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
            </select>
            <select name="creator_id" class="trk-select" id="trkCreatorSelect">
                <option value="">All Strategists</option>
                @foreach($strategists ?? [] as $st)
                    <option value="{{ $st->id }}" {{ request('creator_id') == $st->id ? 'selected' : '' }}>
                        ✍️ {{ $st->name }}
                    </option>
                @endforeach
            </select>
            <select name="assigned_to" class="trk-select" id="trkAssigneeSelect">
                <option value="">All Assignees</option>
                @foreach($designers ?? [] as $ds)
                    <option value="{{ $ds->id }}" {{ request('assigned_to') == $ds->id ? 'selected' : '' }}>
                        🎨 {{ $ds->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="trk-select" id="trkStatusSelect">
                <option value="all">All Status</option>
                <option value="urgent" {{ request('status') == 'urgent' ? 'selected' : '' }}>⚡ Urgent Tasks</option>
                <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                <option value="inprogress" {{ request('status') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                <option value="in_revision" {{ request('status') == 'in_revision' ? 'selected' : '' }}>In Revision</option>
                <option value="review" {{ request('status') == 'review' ? 'selected' : '' }}>Review</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
            <div class="trk-select-wrap" style="min-width:180px">
                <x-client-select :clients="$clients" name="client" :value="request('client')" placeholder="All Clients" width="100%" :onchange="'submitFilterFormAjax()'" />
            </div>
            <select name="type" class="trk-select" id="trkTypeSelect">
                <option value="all">All Types</option>
                <option value="reel" {{ request('type') == 'reel' ? 'selected' : '' }}>Reel</option>
                <option value="post" {{ request('type') == 'post' ? 'selected' : '' }}>Post</option>
                <option value="story" {{ request('type') == 'story' ? 'selected' : '' }}>Story</option>
                <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
                <option value="carousel" {{ request('type') == 'carousel' ? 'selected' : '' }}>Carousel</option>
                <option value="brochure" {{ request('type') == 'brochure' ? 'selected' : '' }}>Brochure</option>
                <option value="banner" {{ request('type') == 'banner' ? 'selected' : '' }}>Banner</option>
                <option value="flyer" {{ request('type') == 'flyer' ? 'selected' : '' }}>Flyer</option>
                <option value="website" {{ request('type') == 'website' ? 'selected' : '' }}>Website</option>
                <option value="software" {{ request('type') == 'software' ? 'selected' : '' }}>Software</option>
                <option value="maintenance" {{ request('type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="others" {{ request('type') == 'others' ? 'selected' : '' }}>Others</option>
            </select>
            <select name="per_page" class="trk-select trk-select-sm" id="trkPerPageSelect">
                <option value="25" {{ request('per_page', '25') == '25' ? 'selected' : '' }}>25 / page</option>
                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 / page</option>
                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 / page</option>
            </select>
        </div>

        <div class="trk-date-range">
            <div class="trk-date-field">
                <label class="trk-date-label">From</label>
                <input type="date" name="date_from" id="trkDateFrom" value="{{ request('date_from') }}" class="trk-date-input">
            </div>
            <div class="trk-date-field">
                <label class="trk-date-label">To</label>
                <input type="date" name="date_to" id="trkDateTo" value="{{ request('date_to') }}" class="trk-date-input">
            </div>
        </div>

        <button type="submit" class="trk-filter-btn" id="trkFilterBtn"><i class="fa-solid fa-filter"></i> Filter</button>
        <button type="button" onclick="clearAllFilters()" class="trk-clear-btn" id="trkClearBtn" style="display:none">
            <i class="fa-solid fa-xmark"></i> Clear
        </button>
    </form>
</div>

{{-- Main Container (Switches between Table and Card Grid) --}}
<div class="trk-table-card" id="trkMainContainer">
    @include('strategist.partials.tracking-content')
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SLIDE-OVER TASK INSPECTOR DRAWER
     ═══════════════════════════════════════════════════════════════ --}}
<div class="trk-drawer-overlay" id="trkDrawerOverlay" onclick="closeTaskInspector()"></div>
<aside class="trk-drawer" id="trkDrawer" aria-hidden="true">
    {{-- Header --}}
    <div class="trk-drawer-header">
        <div style="min-width:0;flex:1">
            <div id="drawerClientPill" style="font-size:12px;font-weight:700;color:var(--primary);margin-bottom:6px;display:flex;align-items:center;gap:6px">
                <span id="drawerClientEmoji">🏢</span>
                <span id="drawerClientName">Client Name</span>
            </div>
            <h2 id="drawerTaskTitle" style="font-size:18px;font-weight:800;color:var(--text);margin:0 0 8px;line-height:1.3">
                Loading task details...
            </h2>
            <div id="drawerMetaBadges" style="display:flex;gap:6px;flex-wrap:wrap">
                <span id="drawerStatusBadge" class="status">Loading</span>
                <span id="drawerTypeBadge" class="tag">Type</span>
                <span id="drawerPriorityBadge" class="trk-priority">Priority</span>
                <span id="drawerUrgentBadge" class="trk-priority trk-prio-urgent" style="display:none;background:#EF4444;color:#fff;box-shadow:0 2px 8px rgba(239,68,68,0.35);font-size:10.5px">
                    <i class="fa-solid fa-bolt"></i> Urgent Designer
                </span>
            </div>
        </div>
        <button type="button" class="trk-drawer-close" onclick="closeTaskInspector()" title="Close Inspector (&times;)">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    {{-- Body --}}
    <div class="trk-drawer-body" id="drawerBody">
        {{-- Urgent Designer Task Triage Card (Shown only if is_urgent_task) --}}
        <div id="drawerUrgentCard" class="trk-d-card" style="display:none;background:linear-gradient(135deg,rgba(239,68,68,0.08),rgba(249,115,22,0.06));border:1.5px solid rgba(239,68,68,0.35);box-shadow:0 4px 15px rgba(239,68,68,0.08)">
            <div class="trk-d-title" style="color:#DC2626;display:flex;justify-content:space-between;align-items:center">
                <span><i class="fa-solid fa-bolt"></i> Urgent Designer Self-Initiated Task</span>
                <span class="trk-live-pulse" style="background:#EF4444;color:#fff"><span class="trk-pulse-dot" style="background:#fff"></span> Fast-Track Priority</span>
            </div>
            <div style="font-size:12px;color:var(--text);display:flex;flex-direction:column;gap:6px;margin-top:6px">
                <div style="display:flex;align-items:center;gap:8px">
                    <strong style="color:var(--text3);min-width:100px">Requested By:</strong>
                    <span id="drawerUrgentRequestedBy" style="font-weight:700;color:#DC2626">Emergency Request</span>
                </div>
                <div id="drawerPausedPredecessorRow" style="display:none;align-items:center;gap:8px">
                    <strong style="color:var(--text3);min-width:100px">Paused Task:</strong>
                    <span id="drawerPausedPredecessorText" style="font-weight:600;color:var(--text)">—</span>
                    <button type="button" id="drawerInspectPausedBtn" class="trk-btn-ghost" style="padding:2px 8px;font-size:10px;border-radius:4px;cursor:pointer">
                        <i class="fa-solid fa-eye"></i> View Paused
                    </button>
                </div>
            </div>
        </div>

        {{-- Designer & Timer Status Strip --}}
        <div class="trk-d-card">
            <div class="trk-d-title"><i class="fa-solid fa-stopwatch"></i> Creative Production &amp; Timer Status</div>
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                <div style="display:flex;align-items:center;gap:10px">
                    <div id="drawerAssigneeAvatar" class="av-sm" style="background:#6366f1;width:36px;height:36px;font-size:13px">D</div>
                    <div>
                        <div id="drawerAssigneeName" style="font-size:13px;font-weight:700;color:var(--text)">Designer Name</div>
                        <div id="drawerAssigneeRole" style="font-size:11px;color:var(--text3)">Designer</div>
                    </div>
                </div>
                <div id="drawerTimerBadge" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:10px;background:var(--card);border:1px solid var(--border);font-size:12px;font-weight:700">
                    <span class="trk-pulse-dot" style="display:none" id="drawerPulseDot"></span>
                    <span id="drawerTimerText">Timer idle</span>
                </div>
            </div>
            <div id="drawerScheduleRow" style="display:flex;gap:16px;margin-top:6px;padding-top:10px;border-top:1px solid var(--border);font-size:11.5px;color:var(--text2)">
                <div><strong style="color:var(--text3)">Deadline:</strong> <span id="drawerDeadline">—</span></div>
                <div><strong style="color:var(--text3)">Post Date:</strong> <span id="drawerPostDate">—</span></div>
                <div><strong style="color:var(--text3)">Client Sign-Off:</strong> <span id="drawerClientStatus">—</span></div>
            </div>
        </div>

        {{-- Revision Alert Banner (Shown only if revision_count > 0) --}}
        <div id="drawerRevisionCard" class="trk-d-card" style="display:none;background:rgba(245,158,11,0.06);border-color:rgba(245,158,11,0.3)">
            <div class="trk-d-title" style="color:#D97706"><i class="fa-solid fa-rotate-left"></i> Active Revision Phase (<span id="drawerRevisionCount">0</span>)</div>
            <div id="drawerRevisionNotes" style="font-size:12.5px;color:#92400E;line-height:1.45;background:var(--card);padding:10px 12px;border-radius:8px;border:1px solid rgba(245,158,11,0.25)">
                No revision notes recorded.
            </div>
            <div id="drawerRevisionMeta" style="font-size:11px;color:#B45309;display:flex;justify-content:space-between">
                <span>Requested by: <strong id="drawerRevisionAuthor">—</strong></span>
                <span id="drawerRevisionDate">—</span>
            </div>
        </div>

        {{-- Deliverables Section --}}
        <div class="trk-d-card">
            <div class="trk-d-title" style="justify-content:space-between">
                <span><i class="fa-solid fa-paintbrush"></i> Submitted Deliverables (<span id="drawerDeliverablesCount">0</span>)</span>
            </div>
            <div id="drawerDeliverablesGrid" class="trk-d-media-grid">
                <div style="font-size:12px;color:var(--text3);grid-column:1/-1;padding:12px;text-align:center">No design deliverables submitted yet.</div>
            </div>
        </div>

        {{-- Copywriting & Brief Section --}}
        <div class="trk-d-card">
            <div class="trk-d-title" style="justify-content:space-between">
                <span><i class="fa-solid fa-file-lines"></i> Copywriting &amp; Social Brief</span>
                <button type="button" class="trk-btn-ghost" onclick="copyDrawerCaption()" style="padding:3px 8px;font-size:10.5px;border-radius:6px;gap:4px">
                    <i class="fa-regular fa-copy"></i> Copy Copy &amp; Hashtags
                </button>
            </div>
            <div id="drawerBriefBox" style="font-size:12.5px;color:var(--text2);line-height:1.5;background:var(--card);padding:10px 12px;border-radius:8px;border:1px solid var(--border);max-height:140px;overflow-y:auto">
                No brief provided.
            </div>
            <div id="drawerCopyBox" style="display:none;background:var(--card);padding:10px 12px;border-radius:8px;border:1px solid var(--border)">
                <div style="font-size:11px;font-weight:700;color:var(--text3);margin-bottom:4px">CAPTION &amp; HASHTAGS</div>
                <div id="drawerCaptionText" style="font-size:12px;color:var(--text);white-space:pre-wrap;line-height:1.4"></div>
                <div id="drawerHashtagsText" style="font-size:12px;color:var(--primary);margin-top:6px;font-weight:600"></div>
            </div>
        </div>

        {{-- Discussion & Live Team Comments --}}
        <div class="trk-d-card">
            <div class="trk-d-title"><i class="fa-solid fa-comments"></i> Team Discussion</div>
            <div id="drawerCommentsFeed" style="display:flex;flex-direction:column;gap:10px;max-height:220px;overflow-y:auto;padding-right:4px">
                <div style="font-size:12px;color:var(--text3);text-align:center;padding:10px">No comments yet.</div>
            </div>
            <div style="display:flex;gap:6px;margin-top:8px">
                <input type="text" id="drawerCommentInput" placeholder="Add comment for designer / team..." style="flex:1;padding:8px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card);font-size:12px;color:var(--text)" onkeydown="if(event.key==='Enter') submitDrawerComment()">
                <button type="button" class="trk-filter-btn" style="padding:8px 14px" onclick="submitDrawerComment()"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="trk-drawer-footer">
        <div style="display:flex;gap:6px;flex-wrap:wrap">
            <button type="button" id="drawerNudgeBtn" class="trk-btn-ghost" style="color:#D97706;border-color:rgba(245,158,11,0.3)" onclick="triggerDrawerNudge()">
                <i class="fa-solid fa-bell"></i> Nudge Designer
            </button>
            <button type="button" id="drawerRevisionBtn" class="trk-btn-ghost" style="color:#F97316;border-color:rgba(249,115,22,0.3)" onclick="openDrawerRevisionModal()">
                <i class="fa-solid fa-rotate-left"></i> Request Revision
            </button>
        </div>
        <div style="display:flex;gap:6px">
            <button type="button" id="drawerApproveBtn" class="trk-btn-primary" style="display:none;background:#059669;border:none;color:#fff;font-size:12px;font-weight:700;padding:8px 14px;border-radius:8px;cursor:pointer;align-items:center;gap:6px" onclick="approveTaskFromDrawer()">
                <i class="fa-solid fa-check-double"></i> Approve &amp; Send to Admin
            </button>
            <a href="#" id="drawerEditLink" class="trk-btn-ghost" style="font-size:12px" data-no-loader><i class="fa-solid fa-pen"></i> Edit</a>
            <a href="#" id="drawerFullPageLink" class="trk-btn-primary" style="font-size:12px"><i class="fa-solid fa-arrow-up-right-from-square"></i> Full Workspace</a>
        </div>
    </div>
</aside>

{{-- Reassign Modal --}}
<div id="reassignModal" class="trk-modal-overlay" style="display:none">
    <div class="trk-modal-box">
        <span class="trk-modal-close" onclick="closeReassignModal()">&times;</span>
        <div class="trk-modal-header">
            <div class="trk-modal-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
            <div>
                <div class="trk-modal-title">Reassign Task</div>
                <div id="reassignTaskTitle" class="trk-modal-sub"></div>
            </div>
        </div>
        <form id="reassignForm" method="POST" onsubmit="submitReassignFormAjax(event)">
            @csrf
            @method('PATCH')
            <label class="trk-modal-label">Select Team Member</label>
            <select name="assigned_to" id="reassignSelect" required class="trk-modal-select">
                @foreach($designers as $designer)
                    <option value="{{ $designer->id }}">{{ $designer->name }} ({{ ucfirst($designer->role) }})</option>
                @endforeach
            </select>
            <div class="trk-modal-actions">
                <button type="button" class="trk-modal-btn-ghost" onclick="closeReassignModal()">Cancel</button>
                <button type="submit" class="trk-modal-btn-primary"><i class="fa-solid fa-check"></i> Confirm Reassign</button>
            </div>
        </form>
    </div>
</div>

{{-- Quick Revision Modal from Tracking --}}
<div id="trkRevisionModal" class="trk-modal-overlay" style="display:none">
    <div class="trk-modal-box">
        <span class="trk-modal-close" onclick="closeDrawerRevisionModal()">&times;</span>
        <div class="trk-modal-header">
            <div class="trk-modal-icon" style="background:rgba(245,158,11,0.14);color:#D97706"><i class="fa-solid fa-rotate-left"></i></div>
            <div>
                <div class="trk-modal-title">Request Revision</div>
                <div id="trkRevisionTaskTitle" class="trk-modal-sub"></div>
            </div>
        </div>
        <form id="trkRevisionForm" method="POST" enctype="multipart/form-data" onsubmit="submitRevisionFormAjax(event)">
            @csrf
            @method('PATCH')
            <label class="trk-modal-label">Revision Instructions &amp; Changes Needed</label>
            <textarea name="revision_note" id="trkRevisionNote" required rows="4" class="trk-modal-textarea" placeholder="Detail the exact adjustments required (e.g. fix typo in slide 2, adjust logo size, slow down reel pacing)..."></textarea>
            
            <label class="trk-modal-label">Reference Attachments / Markups (Optional)</label>
            <input type="file" name="reference_files[]" multiple class="trk-modal-select" style="padding:6px;height:auto">

            <div class="trk-modal-actions">
                <button type="button" class="trk-modal-btn-ghost" onclick="closeDrawerRevisionModal()">Cancel</button>
                <button type="submit" class="trk-modal-btn-primary" style="background:#F59E0B"><i class="fa-solid fa-paper-plane"></i> Send Revision Request</button>
            </div>
        </form>
    </div>
</div>

{{-- Toast Notification Container --}}
<div class="trk-toast-container" id="strategistToastContainer"></div>

@push('scripts')
<script>
/**
 * ═══════════════════════════════════════════════════════════════
 * STRATEGIST TRACKING & COMMAND CENTER JAVASCRIPT
 * ═══════════════════════════════════════════════════════════════
 */

let activeInspectTaskId = null;
let currentTaskData = null;

// ── 1. Toast Notification Helper ─────────────────────────────────
function showStrategistToast(type, message) {
    const container = document.getElementById('strategistToastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `trk-toast ${type === 'success' ? 'trk-toast-success' : 'trk-toast-error'}`;
    const icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
    toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${escapeHtml(message)}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 250);
    }, 3500);
}

function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, function(m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
    });
}

// ── 2. View Mode Toggle (Table vs Cards) ─────────────────────────
function setTrackingViewMode(mode) {
    const tableView = document.getElementById('trkTableView');
    const gridView = document.getElementById('trkGridView');
    const tableBtn = document.getElementById('trkViewTableBtn');
    const gridBtn = document.getElementById('trkViewGridBtn');

    if (mode === 'grid') {
        if (tableView) tableView.style.display = 'none';
        if (gridView) gridView.style.display = 'grid';
        if (tableBtn) tableBtn.classList.remove('is-active');
        if (gridBtn) gridBtn.classList.add('is-active');
        localStorage.setItem('trk_view_mode', 'grid');
    } else {
        if (tableView) tableView.style.display = 'block';
        if (gridView) gridView.style.display = 'none';
        if (tableBtn) tableBtn.classList.add('is-active');
        if (gridBtn) gridBtn.classList.remove('is-active');
        localStorage.setItem('trk_view_mode', 'table');
    }
}

// ── 3. Slide-Over Task Inspector Drawer ──────────────────────────
async function openTaskInspector(taskId) {
    activeInspectTaskId = taskId;
    const drawer = document.getElementById('trkDrawer');
    const overlay = document.getElementById('trkDrawerOverlay');
    if (!drawer || !overlay) return;

    overlay.classList.add('is-open');
    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');

    // Reset drawer state to loading
    document.getElementById('drawerTaskTitle').textContent = 'Loading task details...';
    document.getElementById('drawerDeliverablesGrid').innerHTML = '<div style="font-size:12px;color:var(--text3);grid-column:1/-1;padding:16px;text-align:center"><i class="fa-solid fa-spinner fa-spin"></i> Loading deliverables...</div>';

    try {
        const response = await fetch(`/strategist/tasks/${taskId}/quick-inspect`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const data = await response.json();
        if (!data.success || !data.task) throw new Error('Task payload missing');

        currentTaskData = data.task;
        renderTaskInDrawer(data.task);

    } catch (err) {
        console.error('Drawer inspect error:', err);
        document.getElementById('drawerTaskTitle').textContent = 'Task #' + taskId;
        showStrategistToast('error', 'Failed to load task details into inspector.');
    }
}

function closeTaskInspector() {
    const drawer = document.getElementById('trkDrawer');
    const overlay = document.getElementById('trkDrawerOverlay');
    if (drawer) {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
    }
    if (overlay) {
        overlay.classList.remove('is-open');
    }
    activeInspectTaskId = null;
}

function renderTaskInDrawer(task) {
    // Header
    document.getElementById('drawerClientEmoji').textContent = task.client?.emoji || '🏢';
    document.getElementById('drawerClientName').textContent = task.client?.name || 'Unknown Client';
    document.getElementById('drawerTaskTitle').textContent = task.title || 'Untitled Task';

    // Badges
    const statusEl = document.getElementById('drawerStatusBadge');
    statusEl.className = 'status ' + (task.status_class || '');
    statusEl.textContent = task.status_label || task.status;

    const typeEl = document.getElementById('drawerTypeBadge');
    typeEl.className = 'tag ' + (task.type_class || '');
    typeEl.textContent = task.type || 'Task';

    const prioEl = document.getElementById('drawerPriorityBadge');
    prioEl.className = 'trk-priority ' + (task.priority === 'urgent' ? 'trk-prio-urgent' : (task.priority === 'high' ? 'trk-prio-high' : 'trk-prio-normal'));
    prioEl.textContent = (task.priority || 'Normal').toUpperCase();

    // Assignee & Timer
    if (task.assignee) {
        document.getElementById('drawerAssigneeName').textContent = task.assignee.name;
        document.getElementById('drawerAssigneeRole').textContent = task.assignee.role || 'Designer';
        const av = document.getElementById('drawerAssigneeAvatar');
        av.textContent = task.assignee.initial || 'D';
        av.style.background = task.assignee.avatar_color || '#6366f1';
    } else {
        document.getElementById('drawerAssigneeName').textContent = 'Unassigned';
        document.getElementById('drawerAssigneeRole').textContent = 'No designer assigned';
    }

    const timerText = document.getElementById('drawerTimerText');
    const pulseDot = document.getElementById('drawerPulseDot');
    if (task.is_timer_running) {
        pulseDot.style.display = 'inline-block';
        timerText.innerHTML = `<span style="color:#059669">Working Now (${task.timer_formatted})</span>`;
    } else if (task.is_paused) {
        pulseDot.style.display = 'none';
        timerText.innerHTML = `<span style="color:#D97706"><i class="fa-solid fa-pause"></i> Paused</span>`;
    } else {
        pulseDot.style.display = 'none';
        timerText.textContent = task.status === 'completed' ? 'Completed' : 'Timer Idle';
    }

    // Schedule row
    document.getElementById('drawerDeadline').textContent = task.deadline || '—';
    if (task.deadline_overdue) {
        document.getElementById('drawerDeadline').innerHTML = `<span style="color:#EF4444;font-weight:700">${task.deadline} (Overdue)</span>`;
    }
    document.getElementById('drawerPostDate').textContent = task.post_date || '—';
    document.getElementById('drawerClientStatus').innerHTML = task.is_client_approved 
        ? '<span style="color:#059669;font-weight:700"><i class="fa-solid fa-check"></i> Approved</span>' 
        : '<span style="color:#EF4444;font-weight:700"><i class="fa-solid fa-clock"></i> Pending</span>';

    // Revision Card
    const revCard = document.getElementById('drawerRevisionCard');
    if (task.revision_count > 0) {
        revCard.style.display = 'flex';
        document.getElementById('drawerRevisionCount').textContent = task.revision_count;
        document.getElementById('drawerRevisionNotes').textContent = task.revision_info?.latest_note || 'Revision requested without detailed notes.';
        document.getElementById('drawerRevisionAuthor').textContent = task.revision_info?.requested_by || 'Strategist';
        document.getElementById('drawerRevisionDate').textContent = task.revision_info?.date || 'recently';
    } else {
        revCard.style.display = 'none';
    }

    // Deliverables Grid
    const delGrid = document.getElementById('drawerDeliverablesGrid');
    document.getElementById('drawerDeliverablesCount').textContent = task.deliverables ? task.deliverables.length : 0;
    if (task.deliverables && task.deliverables.length > 0) {
        delGrid.innerHTML = task.deliverables.map(d => {
            const isVid = d.is_video;
            const previewContent = isVid 
                ? `<video src="${escapeHtml(d.url)}" muted preload="metadata"></video><span style="position:absolute;top:4px;right:4px;background:rgba(0,0,0,0.7);color:#fff;padding:2px 4px;border-radius:4px;font-size:9px"><i class="fa-solid fa-play"></i></span>`
                : (d.is_image ? `<img src="${escapeHtml(d.url)}" alt="${escapeHtml(d.name)}" loading="lazy">` : `<div style="height:100%;display:flex;align-items:center;justify-content:center;background:var(--card2);font-size:24px;color:var(--text3)"><i class="fa-solid fa-file"></i></div>`);

            return `
                <div class="trk-d-media-item" onclick="window.open('${escapeHtml(d.url)}', '_blank')" title="Click to view ${escapeHtml(d.name)}">
                    ${previewContent}
                    <div class="trk-d-media-badge">${escapeHtml(d.version_label || d.name)}</div>
                </div>
            `;
        }).join('');
    } else {
        delGrid.innerHTML = '<div style="font-size:12px;color:var(--text3);grid-column:1/-1;padding:12px;text-align:center">No design deliverables uploaded yet.</div>';
    }

    // Brief & Copy
    document.getElementById('drawerBriefBox').textContent = task.brief || 'No creative brief provided.';
    const copyBox = document.getElementById('drawerCopyBox');
    if (task.caption || task.hashtags) {
        copyBox.style.display = 'block';
        document.getElementById('drawerCaptionText').textContent = task.caption || '';
        document.getElementById('drawerHashtagsText').textContent = task.hashtags || '';
    } else {
        copyBox.style.display = 'none';
    }

    // Comments feed
    const commentsFeed = document.getElementById('drawerCommentsFeed');
    if (task.comments && task.comments.length > 0) {
        commentsFeed.innerHTML = task.comments.map(c => `
            <div style="display:flex;gap:8px;align-items:flex-start">
                <div class="av-sm" style="background:${escapeHtml(c.user_avatar)};width:26px;height:26px;font-size:10px;flex-shrink:0">${escapeHtml(c.user_initial)}</div>
                <div style="background:var(--card);border:1px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;flex:1">
                    <div style="font-weight:700;color:var(--text);font-size:11px;margin-bottom:2px">${escapeHtml(c.user_name)} <span style="font-weight:500;color:var(--text3);font-size:10px;margin-left:4px">${escapeHtml(c.created_at)}</span></div>
                    <div style="color:var(--text2);line-height:1.35">${escapeHtml(c.body)}</div>
                </div>
            </div>
        `).join('');
    } else {
        commentsFeed.innerHTML = '<div style="font-size:12px;color:var(--text3);text-align:center;padding:8px">No comments yet.</div>';
    }

    // Route links in footer
    document.getElementById('drawerEditLink').href = task.routes?.edit || `/strategist/tasks/${task.id}/edit`;
    document.getElementById('drawerFullPageLink').href = task.routes?.show || `/strategist/tasks/${task.id}`;

    // Urgent Designer Task Triage
    const urgentBadge = document.getElementById('drawerUrgentBadge');
    const urgentCard = document.getElementById('drawerUrgentCard');
    if (task.is_urgent_task) {
        if (urgentBadge) urgentBadge.style.display = 'inline-flex';
        if (urgentCard) {
            urgentCard.style.display = 'block';
            document.getElementById('drawerUrgentRequestedBy').textContent = task.urgent_requested_by || 'Emergency Client Request';
            const pausedRow = document.getElementById('drawerPausedPredecessorRow');
            if (task.paused_predecessor) {
                pausedRow.style.display = 'flex';
                document.getElementById('drawerPausedPredecessorText').textContent = `#${task.paused_predecessor.id} ${task.paused_predecessor.title}`;
                const btn = document.getElementById('drawerInspectPausedBtn');
                if (btn) btn.onclick = () => openTaskInspector(task.paused_predecessor.id);
            } else {
                pausedRow.style.display = 'none';
            }
        }
    } else {
        if (urgentBadge) urgentBadge.style.display = 'none';
        if (urgentCard) urgentCard.style.display = 'none';
    }

    // Fast-Track Approve Button (Shown when task is in review)
    const approveBtn = document.getElementById('drawerApproveBtn');
    if (approveBtn) {
        if (task.status === 'review') {
            approveBtn.style.display = 'inline-flex';
        } else {
            approveBtn.style.display = 'none';
        }
    }
}

// ── Fast-Track Approve from Drawer & Tracking ───────────────────
async function approveTaskFromDrawer() {
    if (!currentTaskData) return;
    await approveTaskFromTracking(currentTaskData.id, currentTaskData.title);
}

async function approveTaskFromTracking(taskId, taskTitle, event) {
    if (event) event.stopPropagation();
    if (!confirm(`Fast-Track Approve "${taskTitle}" and send to Admin for final sign-off?`)) return;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res = await fetch(`/strategist/tasks/${taskId}/approve`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ force: true })
        });
        const data = await res.json();
        if (data.success) {
            showStrategistToast('success', data.message || 'Task approved and sent to Admin!');
            closeTaskInspector();
            submitFilterFormAjax();
        } else {
            showStrategistToast('error', data.message || 'Failed to approve task.');
        }
    } catch (err) {
        showStrategistToast('error', 'Network error approving task.');
    }
}

// ── 4. 1-Click Copy Caption from Drawer ──────────────────────────
function copyDrawerCaption() {
    if (!currentTaskData) return;
    const caption = (currentTaskData.caption || '').trim();
    const hashtags = (currentTaskData.hashtags || '').trim();
    const fullText = [caption, hashtags].filter(Boolean).join("\n\n");

    if (!fullText) {
        showStrategistToast('error', 'No caption or hashtags available to copy.');
        return;
    }

    navigator.clipboard.writeText(fullText).then(() => {
        showStrategistToast('success', 'Caption & Hashtags copied to clipboard! 📋');
    }).catch(() => {
        showStrategistToast('error', 'Failed to copy text.');
    });
}

// ── 5. Instant Nudge Designer Action ─────────────────────────────
async function nudgeTask(taskId, assigneeName, event) {
    if (event) event.stopPropagation();

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/strategist/tasks/${taskId}/follow-up`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();
        if (data.success) {
            showStrategistToast('success', data.message || `Follow-up reminder sent to ${assigneeName}!`);
        } else {
            showStrategistToast('error', data.message || 'Could not send reminder.');
        }
    } catch (err) {
        console.error('Nudge error:', err);
        showStrategistToast('error', 'Network error sending follow-up reminder.');
    }
}

function triggerDrawerNudge() {
    if (!activeInspectTaskId || !currentTaskData) return;
    nudgeTask(activeInspectTaskId, currentTaskData.assignee?.name || 'Designer');
}

// ── 6. Drawer Revision Modal ─────────────────────────────────────
function openDrawerRevisionModal() {
    if (!activeInspectTaskId || !currentTaskData) return;
    document.getElementById('trkRevisionTaskTitle').textContent = `Task #${activeInspectTaskId}: "${currentTaskData.title}"`;
    document.getElementById('trkRevisionForm').action = `/strategist/tasks/${activeInspectTaskId}/request-revision`;
    document.getElementById('trkRevisionModal').style.display = 'flex';
}

function closeDrawerRevisionModal() {
    document.getElementById('trkRevisionModal').style.display = 'none';
    document.getElementById('trkRevisionNote').value = '';
}

async function submitRevisionFormAjax(e) {
    e.preventDefault();
    const form = document.getElementById('trkRevisionForm');
    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();
        if (data.success) {
            showStrategistToast('success', data.message || 'Revision requested — sent back to designer.');
            closeDrawerRevisionModal();
            closeTaskInspector();
            submitFilterFormAjax(); // Refresh table state
        } else {
            showStrategistToast('error', data.message || 'Failed to submit revision.');
        }
    } catch (err) {
        showStrategistToast('error', 'Network error submitting revision.');
    }
}

// ── 7. Post Comment from Drawer ──────────────────────────────────
async function submitDrawerComment() {
    if (!activeInspectTaskId) return;
    const input = document.getElementById('drawerCommentInput');
    const text = (input.value || '').trim();
    if (!text) return;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/strategist/tasks/${activeInspectTaskId}/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ body: text })
        });

        const data = await response.json();
        if (data.success) {
            input.value = '';
            showStrategistToast('success', 'Comment posted!');
            // Re-inspect to refresh comment feed
            openTaskInspector(activeInspectTaskId);
        } else {
            showStrategistToast('error', data.message || 'Could not post comment.');
        }
    } catch (err) {
        showStrategistToast('error', 'Network error posting comment.');
    }
}

// ── 8. Reassign Modal ────────────────────────────────────────────
function openReassignModal(taskId, title, currentAssignee) {
    document.getElementById('reassignForm').action = '/strategist/tasks/' + taskId + '/reassign';
    document.getElementById('reassignTaskTitle').textContent = '"' + title + '"';
    if (currentAssignee) {
        document.getElementById('reassignSelect').value = currentAssignee;
    }
    document.getElementById('reassignModal').style.display = 'flex';
}
function closeReassignModal() {
    document.getElementById('reassignModal').style.display = 'none';
}

// ── 9. Delete Task AJAX ──────────────────────────────────────────
async function deleteTaskAjax(taskId, title, event) {
    if (event) event.stopPropagation();
    if (!confirm(`Are you sure you want to delete task "${title}"?`)) return;

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch(`/strategist/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();
        if (data.success) {
            showStrategistToast('success', data.message || 'Task deleted.');
            // Remove row from table / cards immediately
            const row = document.getElementById(`task-row-${taskId}`);
            if (row) row.remove();
            submitFilterFormAjax();
        } else {
            showStrategistToast('error', data.message || 'Failed to delete task.');
        }
    } catch (err) {
        showStrategistToast('error', 'Error deleting task.');
    }
}

// ── 9b. Submit Reassign via AJAX ────────────────────────────────
async function submitReassignFormAjax(e) {
    e.preventDefault();
    const form = document.getElementById('reassignForm');
    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json();
        if (data.success) {
            showStrategistToast('success', data.message || 'Task reassigned successfully!');
            closeReassignModal();
            closeTaskInspector();
            submitFilterFormAjax();
        } else {
            showStrategistToast('error', data.message || 'Failed to reassign task.');
        }
    } catch (err) {
        showStrategistToast('error', 'Network error reassigning task.');
    }
}

// ── 10. Real-time Live AJAX Search & Filter Engine ────────────────
let trkFilterAbortController = null;
let trkSearchDebounceTimer = null;

document.addEventListener('DOMContentLoaded', function() {
    // Restore preferred view mode (table vs card grid)
    const savedMode = localStorage.getItem('trk_view_mode') || 'table';
    setTrackingViewMode(savedMode);

    // Initial check for clear button visibility
    updateClearButtonVisibility();

    // Debounced real-time server search as user types
    const searchInput = document.getElementById('trkSearchInput');
    const searchClearBtn = document.getElementById('trkSearchClearBtn');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const hasVal = this.value.trim().length > 0;
            if (searchClearBtn) searchClearBtn.style.display = hasVal ? 'block' : 'none';

            clearTimeout(trkSearchDebounceTimer);
            trkSearchDebounceTimer = setTimeout(() => {
                submitFilterFormAjax();
            }, 300);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(trkSearchDebounceTimer);
                submitFilterFormAjax();
            }
        });
    }

    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (searchInput) {
                searchInput.value = '';
                searchClearBtn.style.display = 'none';
                clearTimeout(trkSearchDebounceTimer);
                submitFilterFormAjax();
            }
        });
    }

    // Form change event listener (captures sort, creator, assignee, status, type, dates, per_page)
    const form = document.getElementById('trkFilterForm');
    if (form) {
        form.addEventListener('change', function(e) {
            // Avoid triggering on search input change event since handled on input
            if (e.target && e.target.id === 'trkSearchInput') return;
            submitFilterFormAjax();
        });
    }

    // Intercept pagination clicks inside trkMainContainer via delegation
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#trkMainContainer .pg-links a, #trkMainContainer .trk-pagination a');
        if (pageLink && pageLink.href) {
            e.preventDefault();
            submitFilterFormAjax(pageLink.href);
            const tableCard = document.getElementById('trkMainContainer');
            if (tableCard) {
                tableCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });

    // Escape key closes modals / drawer
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTaskInspector();
            closeReassignModal();
            closeDrawerRevisionModal();
        }
    });
});

// Popstate event listener for browser history (back/forward navigation)
window.addEventListener('popstate', function(e) {
    if (e.state && e.state.trkUrl) {
        submitFilterFormAjax(e.state.trkUrl, false);
    } else {
        submitFilterFormAjax(window.location.href, false);
    }
});

function setTrackingScope(scopeVal) {
    const form = document.getElementById('trkFilterForm');
    if (!form) return;
    const scopeInput = document.getElementById('trkScopeInput');
    if (scopeInput) scopeInput.value = scopeVal;

    document.querySelectorAll('.trk-scope-btn').forEach(btn => {
        if (btn.dataset.scope === scopeVal) {
            btn.classList.add('is-active');
        } else {
            btn.classList.remove('is-active');
        }
    });

    submitFilterFormAjax();
}

function filterByStatus(statusVal) {
    const sel = document.getElementById('trkStatusSelect');
    if (sel) sel.value = statusVal || 'all';
    submitFilterFormAjax();
}

function changePerPage(val) {
    const perPageSelect = document.getElementById('trkPerPageSelect');
    if (perPageSelect) perPageSelect.value = val;
    submitFilterFormAjax();
}

function updateClearButtonVisibility() {
    const form = document.getElementById('trkFilterForm');
    const clearBtn = document.getElementById('trkClearBtn');
    if (!form || !clearBtn) return;

    const search = (document.getElementById('trkSearchInput')?.value || '').trim();
    const sort = document.getElementById('trkSortSelect')?.value || 'deadline_asc';
    const creator = document.getElementById('trkCreatorSelect')?.value || '';
    const assignee = document.getElementById('trkAssigneeSelect')?.value || '';
    const status = document.getElementById('trkStatusSelect')?.value || 'all';
    const client = form.querySelector('input[name="client"]')?.value || '';
    const type = document.getElementById('trkTypeSelect')?.value || 'all';
    const perPage = document.getElementById('trkPerPageSelect')?.value || '25';
    const dateFrom = document.getElementById('trkDateFrom')?.value || '';
    const dateTo = document.getElementById('trkDateTo')?.value || '';

    const hasActiveFilter = search !== '' ||
        (sort !== 'deadline_asc' && sort !== '') ||
        creator !== '' ||
        assignee !== '' ||
        (status !== 'all' && status !== '') ||
        client !== '' ||
        (type !== 'all' && type !== '') ||
        dateFrom !== '' ||
        dateTo !== '' ||
        perPage !== '25';

    clearBtn.style.display = hasActiveFilter ? 'inline-flex' : 'none';
}

function clearAllFilters() {
    const form = document.getElementById('trkFilterForm');
    if (!form) return;

    // Reset search
    const searchInput = document.getElementById('trkSearchInput');
    if (searchInput) searchInput.value = '';
    const searchClearBtn = document.getElementById('trkSearchClearBtn');
    if (searchClearBtn) searchClearBtn.style.display = 'none';

    // Reset dates
    const dFrom = document.getElementById('trkDateFrom');
    if (dFrom) dFrom.value = '';
    const dTo = document.getElementById('trkDateTo');
    if (dTo) dTo.value = '';

    // Reset selects
    const sortSel = document.getElementById('trkSortSelect');
    if (sortSel) sortSel.value = 'deadline_asc';

    const creatorSel = document.getElementById('trkCreatorSelect');
    if (creatorSel) creatorSel.selectedIndex = 0;

    const assigneeSel = document.getElementById('trkAssigneeSelect');
    if (assigneeSel) assigneeSel.selectedIndex = 0;

    const statusSel = document.getElementById('trkStatusSelect');
    if (statusSel) statusSel.value = 'all';

    const typeSel = document.getElementById('trkTypeSelect');
    if (typeSel) typeSel.value = 'all';

    const perPageSel = document.getElementById('trkPerPageSelect');
    if (perPageSel) perPageSel.value = '25';

    // Reset custom client-select
    const clientInput = form.querySelector('input[name="client"]');
    if (clientInput) {
        clientInput.value = '';
        const wrapper = clientInput.closest('.custom-client-select');
        if (wrapper) {
            const sel = wrapper.querySelector('.ccs-selected');
            if (sel) {
                sel.innerHTML = '<span class="ccs-placeholder">All Clients</span><i class="fa-solid fa-chevron-down ccs-arrow"></i>';
            }
            wrapper.querySelectorAll('.ccs-option').forEach(o => {
                if (o.dataset.value === '') o.classList.add('selected');
                else o.classList.remove('selected');
            });
        }
    }

    updateClearButtonVisibility();
    submitFilterFormAjax();
}

function submitFilterFormAjax(customUrl = null, updateHistory = true) {
    const form = document.getElementById('trkFilterForm');
    if (!form) return;

    let targetUrl;
    if (customUrl) {
        targetUrl = customUrl;
    } else {
        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value !== '' && value !== null) {
                params.append(key, value);
            }
        }
        const qs = params.toString();
        targetUrl = `${form.action}${qs ? '?' + qs : ''}`;
    }

    // Cancel pending request if any
    if (trkFilterAbortController) {
        trkFilterAbortController.abort();
    }
    trkFilterAbortController = new AbortController();

    const mainContainer = document.getElementById('trkMainContainer');
    if (mainContainer) {
        mainContainer.classList.add('trk-is-loading');
    }

    fetch(targetUrl, {
        signal: trkFilterAbortController.signal,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(async res => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const contentType = res.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            return await res.json();
        } else {
            const htmlText = await res.text();
            return { isHtml: true, html: htmlText };
        }
    })
    .then(data => {
        if (data.isHtml) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data.html, 'text/html');

            const newContent = doc.getElementById('trkMainContainer');
            if (newContent && mainContainer) mainContainer.innerHTML = newContent.innerHTML;

            const newStats = doc.getElementById('trkStatsRow');
            if (newStats) {
                const curStats = document.getElementById('trkStatsRow');
                if (curStats) curStats.innerHTML = newStats.innerHTML;
            }

            const newAlert = doc.getElementById('trkUrgentAlertContainer');
            if (newAlert) {
                const curAlert = document.getElementById('trkUrgentAlertContainer');
                if (curAlert) curAlert.innerHTML = newAlert.innerHTML;
            }

            const newSub = doc.getElementById('trackingSubtitle');
            if (newSub) {
                const curSub = document.getElementById('trackingSubtitle');
                if (curSub) curSub.innerHTML = newSub.innerHTML;
            }
        } else if (data.success) {
            if (mainContainer && data.content_html !== undefined) {
                mainContainer.innerHTML = data.content_html;
            }
            if (data.stats_html !== undefined) {
                const statsRow = document.getElementById('trkStatsRow');
                if (statsRow) statsRow.innerHTML = data.stats_html;
            }
            if (data.urgent_alert_html !== undefined) {
                const alertContainer = document.getElementById('trkUrgentAlertContainer');
                if (alertContainer) alertContainer.innerHTML = data.urgent_alert_html;
            }
            if (data.subtitle !== undefined) {
                const subEl = document.getElementById('trackingSubtitle');
                if (subEl) subEl.textContent = data.subtitle;
            }
        }

        // Restore active view mode (table vs cards)
        const currentMode = localStorage.getItem('trk_view_mode') || 'table';
        setTrackingViewMode(currentMode);

        // Update clear button
        updateClearButtonVisibility();

        // Push URL to browser history
        if (updateHistory) {
            window.history.pushState({ trkUrl: targetUrl }, '', targetUrl);
        }
    })
    .catch(err => {
        if (err.name === 'AbortError') return;
        console.error('Filter AJAX error:', err);
        showStrategistToast('error', 'Error refreshing task tracking.');
    })
    .finally(() => {
        if (mainContainer) {
            mainContainer.classList.remove('trk-is-loading');
        }
    });
}
</script>
@endpush

@endsection
