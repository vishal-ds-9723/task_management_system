@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@section('content')
<div class="topbar">
    <div>
        <div class="page-title"><i class="fas fa-calendar-alt" style="margin-right:8px;color:var(--primary)"></i> Team Calendar</div>
        <div class="page-subtitle">All strategist-created tasks — see work assigned by all strategists</div>
    </div>
    <div class="topbar-actions" style="display:flex;gap:10px;align-items:center">
        {{-- Hidden native select for JS value tracking --}}
        <input type="hidden" id="clientFilter" value="">
        <div class="custom-client-select" id="clientSelectWrapper">
            <div class="ccs-selected" id="ccsSelected" onclick="toggleClientDropdown()">
                <span class="ccs-placeholder">All Clients</span>
                <i class="fa-solid fa-chevron-down ccs-arrow"></i>
            </div>
            <div class="ccs-dropdown" id="ccsDropdown">
                <div class="ccs-search-wrap">
                    <i class="fa-solid fa-magnifying-glass ccs-search-icon"></i>
                    <input type="text" class="ccs-search" id="ccsSearch" placeholder="Search clients..." autocomplete="off">
                </div>
                <div class="ccs-options" id="ccsOptions">
                    <div class="ccs-option" data-value="" data-emoji="" data-color="" data-category="" data-total="0" data-active="0" data-overdue="0" data-logo="" onclick="selectClient(this)">
                        <span class="ccs-opt-name">All Clients</span>
                    </div>
                    @foreach($clients as $client)
                        <div class="ccs-option"
                            data-value="{{ $client->id }}"
                            data-emoji="{{ $client->emoji }}"
                            data-color="{{ $client->color }}"
                            data-category="{{ $client->category }}"
                            data-total="{{ $client->total_tasks }}"
                            data-active="{{ $client->active_tasks }}"
                            data-overdue="{{ $client->overdue_tasks }}"
                            data-logo="{{ $client->logo }}"
                            onclick="selectClient(this)">
                            @if($client->logo)
                                <img src="{{ asset('storage/' . $client->logo) }}" alt="" class="ccs-logo">
                            @else
                                <span class="ccs-emoji">{{ $client->emoji }}</span>
                            @endif
                            <span class="ccs-opt-name">{{ $client->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Client Stats Banner (hidden by default, shown on client select) --}}
<div id="clientBanner" class="client-banner" style="display:none">
    <div class="cb-info">
        <span id="cbEmoji" class="cb-emoji"></span>
        <div>
            <div id="cbName" class="cb-name"></div>
            <div id="cbCategory" class="cb-category"></div>
        </div>
    </div>
    <div class="cb-stats">
        <div class="cb-stat"><span id="cbTotal" class="cb-num">0</span><span class="cb-label">Total</span></div>
        <div class="cb-stat"><span id="cbActive" class="cb-num cb-num-blue">0</span><span class="cb-label">Active</span></div>
        <div class="cb-stat"><span id="cbOverdue" class="cb-num cb-num-red">0</span><span class="cb-label">Overdue</span></div>
    </div>
</div>

{{-- Unified Filter Toolbar --}}
<div class="cal-toolbar">
    <div class="ct-row-main">
        <div class="ct-status-pills">
            <button type="button" class="csf-pill csf-all active" data-status="all" onclick="toggleStatusFilter(this)">
                <i class="fa-solid fa-layer-group"></i> All
            </button>
            <button type="button" class="csf-pill csf-todo" data-status="todo" onclick="toggleStatusFilter(this)">
                <span class="csf-dot" style="background:#FFA500"></span> To Do <span class="csf-pill-count" id="filterTodoCount">0</span>
            </button>
            <button type="button" class="csf-pill csf-inprogress" data-status="inprogress" onclick="toggleStatusFilter(this)">
                <span class="csf-dot" style="background:#3B82F6"></span> In Progress <span class="csf-pill-count" id="filterInprogressCount">0</span>
            </button>
            <button type="button" class="csf-pill csf-review" data-status="review" onclick="toggleStatusFilter(this)">
                <span class="csf-dot" style="background:#8B5CF6"></span> Review <span class="csf-pill-count" id="filterReviewCount">0</span>
            </button>
            <button type="button" class="csf-pill csf-completed" data-status="completed" onclick="toggleStatusFilter(this)">
                <span class="csf-dot" style="background:#10B981"></span> Done <span class="csf-pill-count" id="filterCompletedCount">0</span>
            </button>
        </div>
        <div class="ct-actions">
            <button type="button" class="ct-toggle-btn" id="advFilterToggle" onclick="toggleAdvPanel()">
                <i class="fa-solid fa-sliders"></i> Filters
                <span class="ct-filter-badge" id="activeFilterBadge" style="display:none"></span>
            </button>
            <button type="button" class="af-today-btn" onclick="jumpToToday()">
                <i class="fa-solid fa-crosshairs"></i> Today
            </button>
            <button type="button" class="af-overdue-btn" onclick="showOverdueTasks()">
                <i class="fa-solid fa-triangle-exclamation"></i> <span class="af-overdue-count" id="overdueCount">0</span>
            </button>
            <button type="button" class="af-clear-btn" onclick="clearAllFilters()" id="clearFiltersBtn" style="display:none">
                <i class="fa-solid fa-xmark"></i> Clear
            </button>
        </div>
    </div>
    {{-- Collapsible Advanced Filters --}}
    <div class="ct-adv-panel" id="advFilterPanel" style="display:none">
        <div class="ct-adv-row">
            <span class="ct-adv-label">Type</span>
            <div class="adv-filter-group" id="typeFilterGroup">
                <button type="button" class="af-pill active" data-type="all" onclick="toggleAdvFilter('type', this)">All</button>
                <button type="button" class="af-pill af-reel" data-type="reel" onclick="toggleAdvFilter('type', this)"><i class="fas fa-film"></i> Reel</button>
                <button type="button" class="af-pill af-post" data-type="post" onclick="toggleAdvFilter('type', this)"><i class="fas fa-pen-fancy"></i> Post</button>
                <button type="button" class="af-pill af-story" data-type="story" onclick="toggleAdvFilter('type', this)"><i class="fas fa-mobile-alt"></i> Story</button>
                <button type="button" class="af-pill af-video" data-type="video" onclick="toggleAdvFilter('type', this)"><i class="fas fa-video"></i> Video</button>
                <button type="button" class="af-pill af-carousel" data-type="carousel" onclick="toggleAdvFilter('type', this)"><i class="fas fa-images"></i> Carousel</button>
            </div>
        </div>
        <div class="ct-adv-row">
            <span class="ct-adv-label">Platform</span>
            <div class="adv-filter-group" id="platformFilterGroup">
                <button type="button" class="af-pill active" data-platform="all" onclick="toggleAdvFilter('platform', this)">All</button>
                <button type="button" class="af-pill" data-platform="instagram" onclick="toggleAdvFilter('platform', this)"><i class="fab fa-instagram"></i> Instagram</button>
                <button type="button" class="af-pill" data-platform="facebook" onclick="toggleAdvFilter('platform', this)"><i class="fab fa-facebook"></i> Facebook</button>
                <button type="button" class="af-pill" data-platform="linkedin" onclick="toggleAdvFilter('platform', this)"><i class="fab fa-linkedin"></i> LinkedIn</button>
                <button type="button" class="af-pill" data-platform="twitter" onclick="toggleAdvFilter('platform', this)"><i class="fab fa-x-twitter"></i> Twitter</button>
            </div>
        </div>
        <div class="ct-adv-row">
            <span class="ct-adv-label">Priority</span>
            <div class="adv-filter-group" id="priorityFilterGroup">
                <button type="button" class="af-pill active" data-priority="all" onclick="toggleAdvFilter('priority', this)">All</button>
                <button type="button" class="af-pill af-urgent" data-priority="urgent" onclick="toggleAdvFilter('priority', this)"><i class="fas fa-circle" style="font-size:8px;color:#EF4444"></i> Urgent</button>
                <button type="button" class="af-pill af-high" data-priority="high" onclick="toggleAdvFilter('priority', this)"><i class="fas fa-circle" style="font-size:8px;color:#F97316"></i> High</button>
                <button type="button" class="af-pill af-normal" data-priority="normal" onclick="toggleAdvFilter('priority', this)">Normal</button>
            </div>
        </div>
    </div>
</div>

<div class="calendar-layout">
    {{-- Calendar --}}
    <div class="card cal-card">
        <div class="cal-branding">
            <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="cal-brand-logo">
        </div>
        <div id="calendarView"></div>
    </div>

    {{-- Right Sidebar Panel --}}
    <div class="card task-panel">
        {{-- Range Tabs --}}
        <div class="range-tabs-wrap">
            <div class="range-tabs" id="rangeTabs">
                <button class="range-tab active" data-range="today" onclick="switchRange('today')">Today</button>
                <button class="range-tab" data-range="2" onclick="switchRange('2')">2 Days</button>
                <button class="range-tab" data-range="3" onclick="switchRange('3')">3 Days</button>
                <button class="range-tab" data-range="5" onclick="switchRange('5')">5 Days</button>
                <button class="range-tab" data-range="15" onclick="switchRange('15')">15 Days</button>
                <button class="range-tab" data-range="30" onclick="switchRange('30')">Month</button>
            </div>
        </div>

        {{-- Range Summary Header --}}
        <div class="range-summary" id="rangeSummary">
            <div class="rs-header">
                <div class="rs-title-row">
                    <span class="rs-icon" id="rsIcon"><i class="fas fa-bolt"></i></span>
                    <span class="rs-title" id="rsTitle">Today</span>
                    <span class="rs-total-badge" id="rsTotalBadge">0</span>
                </div>
                <span class="rs-date-range" id="rsDateRange"></span>
            </div>
            <div class="rs-stats-grid">
                <button class="rs-stat rs-stat-todo" onclick="filterRangeByStatus('todo')">
                    <span class="rs-stat-num" id="rsTodoCount">0</span>
                    <span class="rs-stat-label">To Do</span>
                    <span class="rs-stat-bar"><span class="rs-stat-fill rs-fill-todo" id="rsTodoBar"></span></span>
                </button>
                <button class="rs-stat rs-stat-inp" onclick="filterRangeByStatus('inprogress')">
                    <span class="rs-stat-num" id="rsInpCount">0</span>
                    <span class="rs-stat-label">In Progress</span>
                    <span class="rs-stat-bar"><span class="rs-stat-fill rs-fill-inp" id="rsInpBar"></span></span>
                </button>
                <button class="rs-stat rs-stat-rev" onclick="filterRangeByStatus('review')">
                    <span class="rs-stat-num" id="rsRevCount">0</span>
                    <span class="rs-stat-label">Review</span>
                    <span class="rs-stat-bar"><span class="rs-stat-fill rs-fill-rev" id="rsRevBar"></span></span>
                </button>
                <button class="rs-stat rs-stat-done" onclick="filterRangeByStatus('completed')">
                    <span class="rs-stat-num" id="rsDoneCount">0</span>
                    <span class="rs-stat-label">Done</span>
                    <span class="rs-stat-bar"><span class="rs-stat-fill rs-fill-done" id="rsDoneBar"></span></span>
                </button>
            </div>
            <div class="rs-meta-row">
                <span class="rs-meta"><i class="fa-solid fa-bullseye"></i> <b id="rsDeadlines">0</b> deadlines</span>
                <span class="rs-meta"><i class="fa-solid fa-paper-plane"></i> <b id="rsPosts">0</b> posts</span>
                <span class="rs-meta rs-meta-warn" id="rsOverdueWrap" style="display:none"><i class="fa-solid fa-triangle-exclamation"></i> <b id="rsOverdue">0</b> overdue</span>
                <span class="rs-meta rs-meta-upcoming"><i class="fa-solid fa-forward"></i> <b id="rsUpcoming">0</b> upcoming</span>
            </div>
        </div>

        {{-- Range Status Filter Active Indicator --}}
        <div class="range-filter-active" id="rangeFilterActive" style="display:none">
            <span id="rangeFilterLabel"></span>
            <button class="rfa-clear" onclick="clearRangeStatusFilter()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        {{-- Range Tasks — Grouped by Date --}}
        <div id="rangeTasksList" class="range-tasks-list"></div>

        {{-- Date-specific section (when clicking a calendar date) --}}
        <div id="dateClickSection" class="date-click-section" style="display:none">
            <div class="panel-divider"></div>
            <div class="dcs-header">
                <div>
                    <div class="dcs-title"><i class="fa-regular fa-calendar-check"></i> <span id="dcsDateTitle">Selected Date</span></div>
                    <div class="dcs-subtitle" id="dcsDateLabel"></div>
                </div>
                <div class="dcs-actions">
                    <span id="dcsCount" class="date-count">0</span>
                    <button class="dcs-close" onclick="closeDateSection()" title="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>
            </div>
            <div id="dateBreakdown" class="date-breakdown" style="display:none">
                <span id="deadlineCount" class="db-tag db-deadline"><i class="fa-solid fa-bullseye"></i> <b>0</b> Deadlines</span>
                <span id="postDateCount" class="db-tag db-post"><i class="fa-solid fa-paper-plane"></i> <b>0</b> Posts</span>
            </div>
            <div id="tasksListPanel" style="font-size:13px"></div>
        </div>

        {{-- Hidden counters for JS compat --}}
        <div id="statusSummaryBoxes" style="display:none">
            <span id="sbTodoCount">0</span><span id="sbInprogressCount">0</span>
            <span id="sbReviewCount">0</span><span id="sbCompletedCount">0</span>
        </div>
        <div id="todayTasksList" style="display:none"></div>
    </div>
</div>

{{-- Task Detail Modal --}}
<div id="calTaskModal" class="cal-modal-overlay">
    <div class="cal-modal-box">
        <span class="cal-modal-close" onclick="closeCalTaskModal()">&times;</span>
        <div id="calTaskDetails"></div>
    </div>
</div>

@push('styles')<link rel="stylesheet" href="{{ asset('css/pages/strategist-calendar.css') }}">@endpush

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<script src="{{ asset('js/pages/strategist-calendar.js') }}?v={{ time() }}"></script>
@endsection
