@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/designer-theme.css') }}?v=7.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
@endpush

@section('content')
@php
    $inProgress    = $tasks->where('status', 'inprogress')->where('is_paused', false);
    $paused        = $tasks->where('is_paused', true);
    $todo          = $tasks->where('status', 'todo');
    $locked        = collect();
    $inReview      = $tasks->whereIn('status', ['review', 'pending_approval']);
    $completed     = $tasks->where('status', 'completed');
    
    // Revision Tasks
    $revisionTasks = $tasks->filter(fn($t) => $t->revision_count > 0 && !in_array($t->status, ['completed', 'published']));
    $revisionCount = $revisionTasks->count();

    // Streamlined 3-Status Grouping
    $activeCount   = $inProgress->count() + $inReview->count();
    $queueCount    = $todo->count();
    $doneCount     = $completed->count();
    $allCount      = $tasks->count();

    // Medium Counts for quick chips
    $mediumCounts = [
        'all'       => $tasks->count(),
        'post'      => $tasks->filter(fn($t) => in_array(strtolower((string)$t->type), ['post', 'static']))->count(),
        'video'     => $tasks->filter(fn($t) => in_array(strtolower((string)$t->type), ['reel', 'video']))->count(),
        'carousel'  => $tasks->filter(fn($t) => strtolower((string)$t->type) === 'carousel')->count(),
        'branding'  => $tasks->filter(fn($t) => in_array(strtolower((string)$t->type), ['logo', 'branding']))->count(),
        'story'     => $tasks->filter(fn($t) => strtolower((string)$t->type) === 'story')->count(),
        'print'     => $tasks->filter(fn($t) => in_array(strtolower((string)$t->type), ['flyer', 'banner', 'brochure']))->count(),
    ];

    $requestedStatus = request('status', 'all');
    $defaultFilterTab = 'all';
    if ($requestedStatus === 'revisions' || (request('filter') === 'revisions')) {
        $defaultFilterTab = 'revisions';
    } elseif (in_array($requestedStatus, ['inprogress', 'review'])) {
        $defaultFilterTab = 'active';
    } elseif ($requestedStatus === 'todo') {
        $defaultFilterTab = 'queue';
    } elseif ($requestedStatus === 'completed') {
        $defaultFilterTab = 'done';
    }

    $maxTaskId = $tasks->max('id') ?? 0;
    $maxTaskUpdated = (string) ($tasks->max('updated_at') ?? '');
    $initialFingerprint = md5("{$allCount}-{$maxTaskId}-{$maxTaskUpdated}-{$revisionCount}");
@endphp

{{-- ══════════════════════════════════════════════
     1 · ULTRA-CLEAN STUDIO HEADER & ACTIONS
     ══════════════════════════════════════════════ --}}
<div class="studio-clean-header">
    <div class="header-left">
        <div class="header-title-row">
            <h1 class="header-title">Deliverables</h1>
            <span class="header-count-badge" id="studioTotalCountBadge">{{ $allCount }} {{ Str::plural('task', $allCount) }}</span>
            <div id="studioLiveBadge" class="studio-live-badge" data-fingerprint="{{ $initialFingerprint }}" data-sync-url="{{ route('designer.tasks.sync-check') }}" title="Live sync active · Auto-checks every 12s & on tab switch · Click to check now" onclick="manualSyncTrigger()">
                <span class="studio-live-dot"></span>
                <span id="studioLiveText">Live</span>
            </div>
        </div>
        <p class="header-desc">Visual designs, social graphics, and active revision requests assigned to you</p>
    </div>

    <div class="header-actions">
        <a href="{{ route('designer.urgent-task') }}" class="btn-studio-urgent" title="Create an urgent deliverable">
            <i class="fa-solid fa-bolt"></i> Urgent Task
        </a>
        <button type="button" class="btn-studio-primary" onclick="openSelfTaskModal({ defaultType: 'logo' })" title="Create a new task">
            <i class="fa-solid fa-plus"></i> Create Task
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     2 · STUDIO PIPELINE CARD & STATUS TABS
     ══════════════════════════════════════════════ --}}
<div class="studio-pipeline-card">
    {{-- Proportional Progress Pipeline Bar --}}
    <div class="studio-pipeline-progress" role="img" aria-label="Progress pipeline">
        <span class="seg-queue"  id="segQueueBar"  style="flex-grow:{{ max(1, $queueCount) }}"  title="{{ $queueCount }} in queue"></span>
        <span class="seg-active" id="segActiveBar" style="flex-grow:{{ max(1, $activeCount) }}" title="{{ $activeCount }} in progress or review"></span>
        <span class="seg-done"   id="segDoneBar"   style="flex-grow:{{ max(1, $doneCount) }}"   title="{{ $doneCount }} completed"></span>
    </div>

    {{-- 5 Interactive Metric Status Tabs --}}
    <div class="studio-pipeline-bar" id="pipelineFilterTabs">
        {{-- All Deliverables Tab --}}
        <button type="button" class="pipeline-tab tab-all {{ $defaultFilterTab === 'all' ? 'active' : '' }}" data-status-filter="all" title="View all deliverables">
            <div class="pipeline-tab-main">
                <div class="pipeline-tab-icon">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="pipeline-tab-text">
                    <span class="pipeline-tab-label">All Deliverables</span>
                    <span class="pipeline-tab-hint">All assigned tasks</span>
                </div>
            </div>
            <div class="pipeline-tab-counter" id="counterAll">{{ $allCount }}</div>
        </button>

        {{-- In Progress & Review (Active) Tab --}}
        <button type="button" class="pipeline-tab tab-studio {{ $defaultFilterTab === 'active' ? 'active' : '' }}" data-status-filter="active" title="View tasks in progress or in review">
            <div class="pipeline-tab-main">
                <div class="pipeline-tab-icon">
                    <i class="fa-solid fa-spinner"></i>
                </div>
                <div class="pipeline-tab-text">
                    <span class="pipeline-tab-label">In Progress</span>
                    <span class="pipeline-tab-hint"><span class="ds-pulse-dot" style="display:inline-block;width:5px;height:5px;margin-right:2px"></span> In progress & review</span>
                </div>
            </div>
            <div class="pipeline-tab-counter" id="counterActive">{{ $activeCount }}</div>
        </button>

        {{-- Revisions Tab --}}
        <button type="button" class="pipeline-tab tab-revisions {{ $revisionCount > 0 ? 'has-revisions' : '' }} {{ $defaultFilterTab === 'revisions' ? 'active' : '' }}" data-status-filter="revisions" title="View revision requests">
            <div class="pipeline-tab-main">
                <div class="pipeline-tab-icon">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div class="pipeline-tab-text">
                    <span class="pipeline-tab-label">Needs Revision</span>
                    <span class="pipeline-tab-hint">{{ $revisionCount > 0 ? 'Action required' : 'Clean slate' }}</span>
                </div>
            </div>
            <div class="pipeline-tab-counter" id="counterRevisions">{{ $revisionCount }}</div>
        </button>

        {{-- Queue Tab --}}
        <button type="button" class="pipeline-tab tab-queue {{ $defaultFilterTab === 'queue' ? 'active' : '' }}" data-status-filter="queue" title="View queued deliverables">
            <div class="pipeline-tab-main">
                <div class="pipeline-tab-icon">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <div class="pipeline-tab-text">
                    <span class="pipeline-tab-label">Up Next</span>
                    <span class="pipeline-tab-hint">Ready in queue</span>
                </div>
            </div>
            <div class="pipeline-tab-counter" id="counterQueue">{{ $queueCount }}</div>
        </button>

        {{-- Delivered Tab --}}
        <button type="button" class="pipeline-tab tab-done {{ $defaultFilterTab === 'done' ? 'active' : '' }}" data-status-filter="done" title="View completed deliverables">
            <div class="pipeline-tab-main">
                <div class="pipeline-tab-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="pipeline-tab-text">
                    <span class="pipeline-tab-label">Delivered</span>
                    <span class="pipeline-tab-hint">Shipped & done</span>
                </div>
            </div>
            <div class="pipeline-tab-counter" id="counterDone">{{ $doneCount }}</div>
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     3 · UNIFIED ACTION & FILTER TOOLBAR
     ══════════════════════════════════════════════ --}}
<div class="studio-unified-toolbar">
    <div class="studio-toolbar-left">
        {{-- Unified Instant Search --}}
        <div class="studio-toolbar-search">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input type="text" id="taskSearchInput" placeholder="Search deliverables, briefs, clients, #ID..." value="{{ request('search') }}" autocomplete="off" aria-label="Search deliverables">
            <button type="button" id="taskSearchClear" class="search-clear-btn" style="display:none" title="Clear search">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Medium / Format Filter Dropdown --}}
        <div class="toolbar-select-wrap">
            <i class="fa-solid fa-shapes select-icon"></i>
            <select id="taskMediumSelect" class="toolbar-select" aria-label="Filter by deliverable format">
                <option value="all">All Formats ({{ $allCount }})</option>
                @if($mediumCounts['post'] > 0)<option value="post">Static Posts ({{ $mediumCounts['post'] }})</option>@endif
                @if($mediumCounts['video'] > 0)<option value="video">Reels & Video ({{ $mediumCounts['video'] }})</option>@endif
                @if($mediumCounts['carousel'] > 0)<option value="carousel">Carousels ({{ $mediumCounts['carousel'] }})</option>@endif
                @if($mediumCounts['branding'] > 0)<option value="branding">Logos & Brand ({{ $mediumCounts['branding'] }})</option>@endif
                @if($mediumCounts['story'] > 0)<option value="story">Stories ({{ $mediumCounts['story'] }})</option>@endif
                @if($mediumCounts['print'] > 0)<option value="print">Print & Banners ({{ $mediumCounts['print'] }})</option>@endif
            </select>
        </div>

        {{-- Client Filter Dropdown --}}
        <div class="toolbar-select-wrap">
            <i class="fa-regular fa-building select-icon"></i>
            <select id="taskClientSelect" class="toolbar-select" aria-label="Filter by client">
                <option value="all">All Clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ (string)request('client_id') === (string)$c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Clear Active Filters Button --}}
        <button type="button" id="taskResetFiltersBtn" class="btn-clear-filters" style="display:none" onclick="resetStudioFilters()">
            <i class="fa-solid fa-xmark"></i> Reset Filters
        </button>
    </div>

    {{-- Segmented View Mode Toggle (Table vs Cards) --}}
    <div class="studio-view-segmented" role="group" aria-label="View mode">
        <button type="button" id="listModeBtn" class="seg-btn active" title="Switch to Table View" onclick="setTaskViewMode('list')">
            <i class="fa-solid fa-table-list"></i>
            <span>Table</span>
        </button>
        <button type="button" id="gridModeBtn" class="seg-btn" title="Switch to Cards View" onclick="setTaskViewMode('grid')">
            <i class="fa-solid fa-grip"></i>
            <span>Cards</span>
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     4 · VISUAL DELIVERABLES CONTENT
     ══════════════════════════════════════════════ --}}
<div id="designerTasksContent">

    @if($tasks->isEmpty())
        <div class="studio-empty-state">
            <div class="studio-empty-icon">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <h3 class="studio-empty-title">No deliverables yet</h3>
            <p class="studio-empty-desc">Creative tasks assigned to you will show up here. You can also create a new task directly.</p>
            <button type="button" class="btn-studio-primary" onclick="openSelfTaskModal({ defaultType: 'logo' })" style="display:inline-flex">
                <i class="fa-solid fa-plus"></i> Create Task
            </button>
        </div>
    @else

        {{-- ── 4A · Interactive Studio DataTable (Table Mode) ── --}}
        <div class="studio-table-card" id="taskListRows" style="display:block">
            <div class="studio-table-responsive">
                <table id="designerTasksTable" class="studio-datatable stripe hover nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width:70px">ID</th>
                            <th>Deliverable & Task</th>
                            <th>Client</th>
                            <th>Format</th>
                            <th>Platforms</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @include('designer.partials.task-rows', ['tasks' => $tasks])
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── 4B · Visual Cards Mode (Artboard Grid) ── --}}
        <div class="task-grid" id="taskCardsGrid" style="display:none">
            @include('designer.partials.task-cards', ['tasks' => $tasks])
        </div>

        {{-- Live Sync Subtle Floating Toast --}}
        <div id="studioLiveToast" class="studio-live-toast">
            <i class="fa-solid fa-arrows-rotate"></i>
            <span id="studioLiveToastText">Deliverables updated in real time</span>
        </div>

        {{-- Grid View Client-Side Pagination Bar --}}
        <div class="studio-grid-pagination" id="gridPaginationBar" style="display:none">
            <div class="grid-page-info" id="gridPageInfo">Showing 1 to 12 deliverables</div>
            <div class="grid-page-buttons" id="gridPageControls"></div>
        </div>

        {{-- Dynamic Filter Empty State --}}
        <div id="dynamicFilterEmpty" class="studio-empty-state" style="display:none">
            <div class="studio-empty-icon">
                <i class="fa-solid fa-filter"></i>
            </div>
            <h3 class="studio-empty-title">No matching deliverables</h3>
            <p class="studio-empty-desc">No tasks match your current filter selection. Try adjusting your status, format, or search keywords.</p>
            <button type="button" class="btn-studio-primary" onclick="resetStudioFilters()">
                <i class="fa-solid fa-rotate-left"></i> Reset Filter Selection
            </button>
        </div>

    @endif

</div>
@endsection

{{-- ══════════════════════════════════════════════
     5 · CONTROLLER JAVASCRIPT
     ══════════════════════════════════════════════ --}}
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script>
// ══════════════════════════════════════════════════
// STUDIO FILTER & PAGINATION PERSISTENCE CONTROLLER
// ══════════════════════════════════════════════════
const STUDIO_STATE_KEY = 'designer_tasks_filter_state_v2';

// Filter state variables
let currentStatusFilter = '{{ $defaultFilterTab }}';
let currentMediumFilter = 'all';
let currentClientFilter = '{{ request('client_id', 'all') }}';
let currentSearchQuery  = '{{ request('search', '') }}';

// Grid Pagination settings
const GRID_ITEMS_PER_PAGE = 12;
let currentGridPage = 1;

// DataTable instance and cached state
let designerDataTable = null;
let savedStudioState = null;

// Retrieve saved state object from localStorage
function getSavedStudioState() {
    try {
        const raw = localStorage.getItem(STUDIO_STATE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch (e) {
        return null;
    }
}

// Persist complete filter, view mode, and pagination state
function saveStudioState() {
    try {
        let tablePage = 0;
        let tableLength = 10;
        if (designerDataTable && typeof designerDataTable.page === 'function') {
            tablePage = designerDataTable.page();
            tableLength = designerDataTable.page.len();
        } else if (savedStudioState) {
            tablePage = savedStudioState.tablePage || 0;
            tableLength = savedStudioState.tableLength || 10;
        }

        const state = {
            status: currentStatusFilter,
            medium: currentMediumFilter,
            client: currentClientFilter,
            search: currentSearchQuery,
            gridPage: currentGridPage,
            tablePage: tablePage,
            tableLength: tableLength,
            viewMode: localStorage.getItem('designer-task-view') || 'list'
        };

        savedStudioState = state;
        localStorage.setItem(STUDIO_STATE_KEY, JSON.stringify(state));
    } catch (e) {
        console.warn('Unable to persist studio filter state:', e);
    }
}

// Restore saved state on page load or back-navigation
function loadStudioState() {
    savedStudioState = getSavedStudioState();

    // 1. Inspect URL parameters (if explicitly provided via link or bookmark)
    const urlParams = new URLSearchParams(window.location.search);
    const hasUrlStatus = urlParams.has('status') || urlParams.has('filter');
    const hasUrlClient = urlParams.has('client_id');
    const hasUrlSearch = urlParams.has('search');

    if (hasUrlStatus) {
        const urlStatus = (urlParams.get('status') || urlParams.get('filter') || '').toLowerCase();
        if (urlStatus === 'revisions') currentStatusFilter = 'revisions';
        else if (['inprogress', 'review', 'active'].includes(urlStatus)) currentStatusFilter = 'active';
        else if (urlStatus === 'todo' || urlStatus === 'queue') currentStatusFilter = 'queue';
        else if (urlStatus === 'completed' || urlStatus === 'done') currentStatusFilter = 'done';
        else currentStatusFilter = 'all';
    } else if (savedStudioState && savedStudioState.status) {
        currentStatusFilter = savedStudioState.status;
    }

    if (savedStudioState && savedStudioState.medium) {
        currentMediumFilter = savedStudioState.medium;
    }

    if (hasUrlClient) {
        currentClientFilter = urlParams.get('client_id') || 'all';
    } else if (savedStudioState && savedStudioState.client) {
        currentClientFilter = savedStudioState.client;
    }

    if (hasUrlSearch) {
        currentSearchQuery = (urlParams.get('search') || '').trim();
    } else if (savedStudioState && typeof savedStudioState.search === 'string') {
        currentSearchQuery = savedStudioState.search.trim();
    }

    if (savedStudioState && savedStudioState.gridPage) {
        currentGridPage = Math.max(1, parseInt(savedStudioState.gridPage, 10) || 1);
    }

    // Clean address bar query parameters to avoid trapping future page refreshes
    if (window.location.search) {
        try {
            window.history.replaceState({}, document.title, window.location.pathname);
        } catch (e) {}
    }

    // 2. Synchronize UI controls with restored state
    // Pipeline Tabs
    document.querySelectorAll('#pipelineFilterTabs .pipeline-tab').forEach(t => t.classList.remove('active'));
    const targetTab = document.querySelector(`#pipelineFilterTabs .pipeline-tab[data-status-filter="${currentStatusFilter}"]`);
    if (targetTab) {
        targetTab.classList.add('active');
    } else {
        document.querySelector('#pipelineFilterTabs .pipeline-tab[data-status-filter="all"]')?.classList.add('active');
        currentStatusFilter = 'all';
    }

    // Medium Dropdown
    const mediumSelect = document.getElementById('taskMediumSelect');
    if (mediumSelect) {
        const mediumOpt = mediumSelect.querySelector(`option[value="${currentMediumFilter}"]`);
        if (mediumOpt) {
            mediumSelect.value = currentMediumFilter;
        } else {
            currentMediumFilter = 'all';
            mediumSelect.value = 'all';
        }
    }

    // Client Dropdown
    const clientSelect = document.getElementById('taskClientSelect');
    if (clientSelect) {
        const clientOpt = clientSelect.querySelector(`option[value="${currentClientFilter}"]`);
        if (clientOpt) {
            clientSelect.value = currentClientFilter;
        } else {
            currentClientFilter = 'all';
            clientSelect.value = 'all';
        }
    }

    // Search Input & Clear Button
    const searchInput = document.getElementById('taskSearchInput');
    const clearSearchBtn = document.getElementById('taskSearchClear');
    if (searchInput) {
        searchInput.value = currentSearchQuery;
        if (clearSearchBtn) {
            clearSearchBtn.style.display = currentSearchQuery ? 'flex' : 'none';
        }
    }

    // Persist normalized state
    saveStudioState();
}

// Synchronized Filter Logic (Used by both DataTables and Grid cards)
function rowMatches(node, fullText) {
    if (!node) return true;

    const group    = node.getAttribute('data-status-group');
    const hasRev   = node.getAttribute('data-revision') === '1';
    const medium   = node.getAttribute('data-medium');
    const clientId = node.getAttribute('data-client-id');

    // 1. Status Match
    let matchStatus = false;
    if (currentStatusFilter === 'all') matchStatus = true;
    else if (currentStatusFilter === 'revisions') matchStatus = hasRev;
    else if (group === currentStatusFilter) matchStatus = true;

    // 2. Format / Medium Match
    let matchMedium = false;
    if (currentMediumFilter === 'all') matchMedium = true;
    else if (medium === currentMediumFilter) matchMedium = true;

    // 3. Client Match
    let matchClient = false;
    if (currentClientFilter === 'all' || !currentClientFilter) matchClient = true;
    else if (String(clientId) === String(currentClientFilter)) matchClient = true;

    // 4. Live Search Text Match
    let matchSearch = true;
    if (currentSearchQuery) {
        matchSearch = (fullText || '').toLowerCase().includes(currentSearchQuery.toLowerCase());
    }

    return matchStatus && matchMedium && matchClient && matchSearch;
}

// Initialize DataTables with persistent state saving
function initStudioDataTable() {
    const tableEl = document.getElementById('designerTasksTable');
    if (!tableEl) return;

    // Register DataTables Custom Filter hook once
    const extSearch = (typeof DataTable !== 'undefined' && DataTable.ext) 
        ? DataTable.ext.search 
        : (window.jQuery && window.jQuery.fn && window.jQuery.fn.dataTable ? window.jQuery.fn.dataTable.ext.search : null);

    if (extSearch && !window._designerDtSearchHookRegistered) {
        window._designerDtSearchHookRegistered = true;
        extSearch.push(function(settings, data, dataIndex) {
            if (settings.nTable && settings.nTable.id !== 'designerTasksTable') return true;

            const rowNode = (designerDataTable ? designerDataTable.row(dataIndex).node() : null)
                || (settings.aoData && settings.aoData[dataIndex] ? settings.aoData[dataIndex].nTr : null);
            if (!rowNode) return true;

            return rowMatches(rowNode, rowNode.textContent);
        });
    }

    const initialLength = (savedStudioState && savedStudioState.tableLength) 
        ? parseInt(savedStudioState.tableLength, 10) 
        : 10;

    const dtOptions = {
        stateSave: true,
        stateDuration: 0, // 0 = localStorage with indefinite duration
        pageLength: initialLength,
        lengthMenu: [10, 25, 50, 100],
        responsive: true,
        order: [], // Preserve server ordering (inprogress -> queue -> review -> completed)
        dom: '<"studio-dt-top">t<"studio-table-footer"<"dt-info-wrap"i><"dt-actions-wrap"lp>>',
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: ['pageLength', 'paging']
        },
        language: {
            search: '',
            searchPlaceholder: '',
            lengthMenu: '_MENU_ per page',
            info: 'Showing _START_ to _END_ of _TOTAL_ deliverables',
            infoEmpty: 'No deliverables found',
            emptyTable: 'No deliverables match your filter selection',
            zeroRecords: 'No matching deliverables found',
            paginate: {
                first: '«',
                previous: '‹ Prev',
                next: 'Next ›',
                last: '»'
            }
        }
    };

    try {
        if (typeof DataTable === 'function') {
            designerDataTable = new DataTable('#designerTasksTable', dtOptions);
        } else if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.DataTable === 'function') {
            designerDataTable = window.jQuery('#designerTasksTable').DataTable(dtOptions);
        }
    } catch (e) {
        console.warn('DataTable init notice:', e);
    }

    if (designerDataTable) {
        // Restore table page if state exists and is within valid range
        if (savedStudioState && typeof savedStudioState.tablePage === 'number' && savedStudioState.tablePage > 0) {
            const pageInfo = designerDataTable.page.info();
            if (pageInfo && pageInfo.pages > 0 && savedStudioState.tablePage < pageInfo.pages) {
                designerDataTable.page(savedStudioState.tablePage);
            }
        }

        // Listen for table page & length changes and persist immediately
        designerDataTable.on('page.dt', function() {
            saveStudioState();
        });
        designerDataTable.on('length.dt', function() {
            saveStudioState();
        });
    }
}

// Visual Grid Pagination Renderer
function renderGridPagination(matchingCards) {
    const paginationBar = document.getElementById('gridPaginationBar');
    const infoEl = document.getElementById('gridPageInfo');
    const controlsEl = document.getElementById('gridPageControls');
    if (!paginationBar || !infoEl || !controlsEl) return;

    const total = matchingCards.length;
    if (total === 0) {
        paginationBar.style.display = 'none';
        return;
    }

    const totalPages = Math.ceil(total / GRID_ITEMS_PER_PAGE) || 1;
    if (currentGridPage > totalPages) currentGridPage = totalPages;
    if (currentGridPage < 1) currentGridPage = 1;

    const startIdx = (currentGridPage - 1) * GRID_ITEMS_PER_PAGE;
    const endIdx = Math.min(startIdx + GRID_ITEMS_PER_PAGE, total);

    // Show/hide cards based on current page
    matchingCards.forEach((card, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });

    paginationBar.style.display = 'flex';
    infoEl.textContent = `Showing ${startIdx + 1} to ${endIdx} of ${total} deliverables · Page ${currentGridPage} of ${totalPages}`;

    // Render pagination buttons
    let html = '';
    html += `<button type="button" class="grid-page-btn" ${currentGridPage === 1 ? 'disabled' : ''} onclick="goToGridPage(${currentGridPage - 1})">‹ Prev</button>`;
    
    // Page range logic
    for (let p = 1; p <= totalPages; p++) {
        if (totalPages <= 7 || p === 1 || p === totalPages || (p >= currentGridPage - 1 && p <= currentGridPage + 1)) {
            html += `<button type="button" class="grid-page-btn ${p === currentGridPage ? 'active' : ''}" onclick="goToGridPage(${p})">${p}</button>`;
        } else if (p === currentGridPage - 2 || p === currentGridPage + 2) {
            html += `<span style="padding:0 4px;color:var(--dz-text-muted)">…</span>`;
        }
    }

    html += `<button type="button" class="grid-page-btn ${currentGridPage === totalPages ? 'disabled' : ''} onclick="goToGridPage(${currentGridPage + 1})">Next ›</button>`;
    controlsEl.innerHTML = html;
}

function goToGridPage(page) {
    currentGridPage = page;
    saveStudioState();
    applyStudioFilters(false);
    document.getElementById('taskCardsGrid')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Check & Update Reset Button Visibility
function updateResetButtonVisibility() {
    const resetBtn = document.getElementById('taskResetFiltersBtn');
    if (!resetBtn) return;
    const isFiltered = (currentStatusFilter !== 'all') || 
                       (currentMediumFilter !== 'all') || 
                       (currentClientFilter !== 'all' && currentClientFilter !== '') || 
                       (currentSearchQuery !== '');
    resetBtn.style.display = isFiltered ? 'inline-flex' : 'none';
}

// Combined Filter Function (Synchronizes Grid Cards and DataTables)
function applyStudioFilters(resetPage = false) {
    const cards = Array.from(document.querySelectorAll('#taskCardsGrid .task-item-container'));
    const matchingCards = [];

    // Filter Grid Cards
    cards.forEach(card => {
        if (rowMatches(card, card.textContent)) {
            matchingCards.push(card);
        } else {
            card.style.display = 'none';
        }
    });

    const isTableMode = document.getElementById('taskListRows')?.style.display !== 'none';

    if (!isTableMode) {
        renderGridPagination(matchingCards);
    }

    // Filter DataTables
    if (designerDataTable) {
        if (resetPage) {
            designerDataTable.page(0).draw();
        } else {
            designerDataTable.draw(false);
        }
    }

    // Dynamic Empty state (shown primarily in grid mode or when whole list is empty)
    const emptyNotice = document.getElementById('dynamicFilterEmpty');
    if (emptyNotice) {
        let count = isTableMode 
            ? (designerDataTable ? designerDataTable.rows({ search: 'applied' }).count() : matchingCards.length)
            : matchingCards.length;

        emptyNotice.style.display = (!isTableMode && count === 0 && cards.length > 0) ? 'block' : 'none';
    }

    updateResetButtonVisibility();
}

// Reset All Studio Filters & Clear Saved State
function resetStudioFilters() {
    currentStatusFilter = 'all';
    currentMediumFilter = 'all';
    currentClientFilter = 'all';
    currentSearchQuery  = '';
    currentGridPage     = 1;

    // Clear saved storage state
    localStorage.removeItem(STUDIO_STATE_KEY);

    if (designerDataTable) {
        try {
            designerDataTable.state.clear();
        } catch (e) {}
    }

    // Reset pipeline tabs
    document.querySelectorAll('#pipelineFilterTabs .pipeline-tab').forEach(t => t.classList.remove('active'));
    document.querySelector('#pipelineFilterTabs .pipeline-tab[data-status-filter="all"]')?.classList.add('active');

    // Reset dropdowns
    const mediumSelect = document.getElementById('taskMediumSelect');
    if (mediumSelect) mediumSelect.value = 'all';

    const clientSelect = document.getElementById('taskClientSelect');
    if (clientSelect) clientSelect.value = 'all';

    // Reset search input
    const searchInput = document.getElementById('taskSearchInput');
    const clearSearchBtn = document.getElementById('taskSearchClear');
    if (searchInput) searchInput.value = '';
    if (clearSearchBtn) clearSearchBtn.style.display = 'none';

    applyStudioFilters(true);
    saveStudioState();
}

// View Mode Switching (DataTable Table vs Grid Cards)
function setTaskViewMode(mode) {
    const grid = document.getElementById('taskCardsGrid');
    const gridPagination = document.getElementById('gridPaginationBar');
    const tableCard = document.getElementById('taskListRows');
    const gridBtn = document.getElementById('gridModeBtn');
    const listBtn = document.getElementById('listModeBtn');
    if (!grid || !tableCard) return;

    if (mode === 'grid') {
        grid.style.display = 'grid';
        tableCard.style.display = 'none';
        gridBtn?.classList.add('active');
        listBtn?.classList.remove('active');
        localStorage.setItem('designer-task-view', 'grid');
        applyStudioFilters(false);
    } else {
        grid.style.display = 'none';
        if (gridPagination) gridPagination.style.display = 'none';
        tableCard.style.display = 'block';
        listBtn?.classList.add('active');
        gridBtn?.classList.remove('active');
        localStorage.setItem('designer-task-view', 'list');
        applyStudioFilters(false);

        // Adjust DataTables columns on reveal
        setTimeout(() => {
            if (designerDataTable && typeof designerDataTable.columns?.adjust === 'function') {
                designerDataTable.columns.adjust().draw(false);
            }
        }, 50);
    }
    saveStudioState();
}

// Wire Events on Page Load
document.addEventListener('DOMContentLoaded', function() {
    // 1. Restore persistent filter & pagination state
    loadStudioState();

    // 2. Pipeline Tabs Click Event
    document.querySelectorAll('#pipelineFilterTabs .pipeline-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('#pipelineFilterTabs .pipeline-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentStatusFilter = this.getAttribute('data-status-filter') || 'all';
            currentGridPage = 1;
            applyStudioFilters(true);
            saveStudioState();
        });
    });

    // 3. Medium Filter Dropdown Change
    const mediumSelect = document.getElementById('taskMediumSelect');
    if (mediumSelect) {
        mediumSelect.addEventListener('change', function() {
            currentMediumFilter = this.value;
            currentGridPage = 1;
            applyStudioFilters(true);
            saveStudioState();
        });
    }

    // 4. Client Filter Dropdown Change
    const clientSelect = document.getElementById('taskClientSelect');
    if (clientSelect) {
        clientSelect.addEventListener('change', function() {
            currentClientFilter = this.value;
            currentGridPage = 1;
            applyStudioFilters(true);
            saveStudioState();
        });
    }

    // 5. Search Input Event
    const searchInput = document.getElementById('taskSearchInput');
    const clearSearchBtn = document.getElementById('taskSearchClear');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearchQuery = this.value.trim();
            if (clearSearchBtn) clearSearchBtn.style.display = currentSearchQuery ? 'flex' : 'none';
            currentGridPage = 1;
            applyStudioFilters(true);
            saveStudioState();
        });
    }

    // 6. Clear Search Button
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            currentSearchQuery = '';
            this.style.display = 'none';
            currentGridPage = 1;
            applyStudioFilters(true);
            saveStudioState();
            searchInput?.focus();
        });
    }

    // 7. Initialize DataTable with state persistence
    initStudioDataTable();

    // 8. Apply synchronized filters to both Table and Cards (preserving pagination)
    applyStudioFilters(false);

    // 9. Restore user's preferred view mode (Table vs Cards)
    const savedView = (savedStudioState && savedStudioState.viewMode) 
        ? savedStudioState.viewMode 
        : (localStorage.getItem('designer-task-view') || 'list');
    setTaskViewMode(savedView);

    // 10. Start Real-Time AJAX Live Sync Background Worker
    initStudioLiveSync();
});

// Support back-forward cache (bfcache) navigation
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        loadStudioState();
        applyStudioFilters(false);
    }
});

// ══════════════════════════════════════════════════
// REAL-TIME AJAX LIVE SYNC WORKER (FILTER-SAFE)
// ══════════════════════════════════════════════════
const SYNC_INTERVAL_MS = 12000; // 12 seconds auto-check
const SYNC_URL = '{{ route('designer.tasks.sync-check') }}';
let currentFingerprint = '{{ $initialFingerprint }}';
let isLiveSyncing = false;
let liveToastTimer = null;

// Visual floating toast notification
function showStudioLiveToast(message = 'Deliverables updated in real time') {
    const toast = document.getElementById('studioLiveToast');
    const toastText = document.getElementById('studioLiveToastText');
    if (!toast) return;
    if (toastText) toastText.textContent = message;
    toast.classList.add('show');
    clearTimeout(liveToastTimer);
    liveToastTimer = setTimeout(() => {
        toast.classList.remove('show');
    }, 3200);
}

// Update header live badge indicator state
function setLiveBadgeState(state, label = 'Live') {
    const badge = document.getElementById('studioLiveBadge');
    const text = document.getElementById('studioLiveText');
    if (!badge) return;
    if (text) text.textContent = label;

    badge.classList.remove('is-syncing', 'has-error');
    if (state === 'syncing') {
        badge.classList.add('is-syncing');
    } else if (state === 'error') {
        badge.classList.add('has-error');
    }
}

// User-triggered manual check (by clicking the Live badge)
function manualSyncTrigger() {
    checkTaskLiveSync(true);
}

// Core Live Sync Check Function
async function checkTaskLiveSync(isManual = false) {
    if (isLiveSyncing) return;

    // Guard: Do not interrupt the user if actively typing in the search box
    const searchInput = document.getElementById('taskSearchInput');
    const isTyping = searchInput && document.activeElement === searchInput && searchInput.value.length > 0;

    // Guard: Do not re-render if any modal (such as Create Task) is currently open
    const isModalOpen = document.querySelector('.modal.show, #selfTaskModal.is-open, #selfTaskModal.show, .studio-modal.show, [id*="TaskModal"][style*="display: block"], [id*="TaskModal"][style*="display: flex"]');
    if (!isManual && (isTyping || isModalOpen)) {
        return;
    }

    isLiveSyncing = true;
    setLiveBadgeState('syncing', 'Syncing…');

    try {
        const url = `${SYNC_URL}?fingerprint=${encodeURIComponent(currentFingerprint)}`;
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`Sync HTTP ${response.status}`);
        }

        const data = await response.json();

        if (data.has_changes) {
            currentFingerprint = data.fingerprint;
            document.getElementById('studioLiveBadge')?.setAttribute('data-fingerprint', currentFingerprint);

            // 1. Update Header Total Badge
            const totalBadge = document.getElementById('studioTotalCountBadge');
            if (totalBadge && data.counts) {
                totalBadge.textContent = `${data.counts.all} ${data.counts.all === 1 ? 'task' : 'tasks'}`;
            }

            // 2. Update Progress Segments
            if (data.counts) {
                const qBar = document.getElementById('segQueueBar');
                const aBar = document.getElementById('segActiveBar');
                const dBar = document.getElementById('segDoneBar');
                if (qBar) {
                    qBar.style.flexGrow = Math.max(1, data.counts.queue);
                    qBar.title = `${data.counts.queue} in queue`;
                }
                if (aBar) {
                    aBar.style.flexGrow = Math.max(1, data.counts.active);
                    aBar.title = `${data.counts.active} in progress or review`;
                }
                if (dBar) {
                    dBar.style.flexGrow = Math.max(1, data.counts.done);
                    dBar.title = `${data.counts.done} completed`;
                }

                // 3. Update Pipeline Tab Counters
                const cAll = document.getElementById('counterAll');
                const cActive = document.getElementById('counterActive');
                const cRev = document.getElementById('counterRevisions');
                const cQueue = document.getElementById('counterQueue');
                const cDone = document.getElementById('counterDone');
                if (cAll) cAll.textContent = data.counts.all;
                if (cActive) cActive.textContent = data.counts.active;
                if (cRev) cRev.textContent = data.counts.revisions;
                if (cQueue) cQueue.textContent = data.counts.queue;
                if (cDone) cDone.textContent = data.counts.done;
            }

            // 4. Update Grid Cards HTML
            const cardsGrid = document.getElementById('taskCardsGrid');
            if (cardsGrid && data.grid_html) {
                cardsGrid.innerHTML = data.grid_html;
            }

            // 5. Update Table HTML & Re-hydrate DataTables while preserving pagination & state
            const tbody = document.querySelector('#designerTasksTable tbody');
            if (tbody && data.table_html) {
                let currentPage = 0;
                if (designerDataTable && typeof designerDataTable.page === 'function') {
                    try {
                        currentPage = designerDataTable.page();
                    } catch (e) {}
                    try {
                        designerDataTable.destroy();
                    } catch (e) {}
                }

                tbody.innerHTML = data.table_html;

                // Re-initialize DataTable with saved preferences
                initStudioDataTable();

                // Restore page if valid
                if (designerDataTable && typeof designerDataTable.page === 'function') {
                    try {
                        const pageInfo = designerDataTable.page.info();
                        if (pageInfo && pageInfo.pages > 0) {
                            const targetPage = Math.min(currentPage, pageInfo.pages - 1);
                            designerDataTable.page(targetPage).draw(false);
                        }
                    } catch (e) {}
                }
            }

            // 6. Synchronize active filters across cards and table without resetting page
            applyStudioFilters(false);

            // 7. Show subtle visual confirmation
            setLiveBadgeState('normal', 'Updated');
            showStudioLiveToast('Deliverables synced with live updates');
            setTimeout(() => {
                setLiveBadgeState('normal', 'Live');
            }, 3000);
        } else {
            // No changes detected on server
            setLiveBadgeState('normal', 'Live');
            if (isManual) {
                showStudioLiveToast('✓ All deliverables are up to date');
            }
        }
    } catch (err) {
        console.warn('Task live sync notice:', err);
        setLiveBadgeState('error', 'Sync paused');
        setTimeout(() => {
            setLiveBadgeState('normal', 'Live');
        }, 4000);
        if (isManual) {
            showStudioLiveToast('⚠️ Live sync connection paused. Retrying shortly...');
        }
    } finally {
        isLiveSyncing = false;
    }
}

// Initialize Live Sync timers and focus/visibility hooks
function initStudioLiveSync() {
    // 1. Periodic background polling
    setInterval(() => {
        checkTaskLiveSync(false);
    }, SYNC_INTERVAL_MS);

    // 2. Instant check on tab focus or browser window activation
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            checkTaskLiveSync(false);
        }
    });

    window.addEventListener('focus', function() {
        checkTaskLiveSync(false);
    });
}
</script>
@endpush