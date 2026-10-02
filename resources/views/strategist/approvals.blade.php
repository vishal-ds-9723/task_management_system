@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" />
<style>
.apv-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; gap:16px; flex-wrap:wrap; }
.apv-title { font-size:22px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:-0.3px; }
.apv-subtitle { font-size:12.5px; color:var(--text3); margin-top:3px; font-weight:500; }
.apv-back-btn { padding:9px 18px; border-radius:10px; font-size:12.5px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px; background:var(--card); color:var(--text2); border:1px solid var(--border); transition:all 0.2s ease; }
.apv-back-btn:hover { background:var(--card2); color:var(--text); border-color:var(--border2); transform:translateY(-1px); }

.apv-filter-bar { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:12px 16px; margin-bottom:18px; box-shadow:var(--shadow-sm); }
.apv-filter-form { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.apv-search-wrap { flex:1; min-width:220px; position:relative; }
.apv-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:12px; color:var(--text3); pointer-events:none; }
.apv-search-input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--border); border-radius:8px; background:var(--card2); font-size:12.5px; color:var(--text); font-family:inherit; }
.apv-search-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(204,49,14,0.08); background:var(--card); }
.apv-filter-selects { display:flex; gap:8px; flex-wrap:wrap; }
.apv-select { padding:9px 12px; border:1px solid var(--border); border-radius:8px; background:var(--card); font-size:12px; color:var(--text2); font-family:inherit; min-width:132px; }
.apv-select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(204,49,14,0.08); }
.apv-date-range { display:flex; gap:8px; align-items:flex-end; }
.apv-date-field { display:flex; flex-direction:column; gap:2px; }
.apv-date-label { font-size:9.5px; font-weight:700; color:var(--text3); text-transform:uppercase; letter-spacing:0.3px; padding-left:2px; }
.apv-date-input { padding:8px 10px; border:1px solid var(--border); border-radius:8px; background:var(--card); font-size:12px; color:var(--text2); font-family:inherit; min-width:130px; }
.apv-date-input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(204,49,14,0.08); }
.apv-filter-btn { padding:9px 16px; border-radius:8px; background:var(--primary); color:#fff; border:none; font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:5px; }
.apv-clear-btn { padding:9px 14px; border-radius:8px; background:var(--red-dim); color:var(--red); text-decoration:none; font-size:11.5px; font-weight:600; display:inline-flex; align-items:center; gap:4px; border:none; cursor:pointer; }

.apv-table-card { background:var(--card); border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow-sm); overflow:hidden; }
.apv-table-wrap { overflow-x:auto; }
.apv-table { width:100%; border-collapse:collapse; }
.apv-table thead th { text-align:left; padding:11px 14px; font-size:10.5px; color:var(--text3); letter-spacing:0.5px; text-transform:uppercase; font-weight:700; background:var(--card2); border-bottom:1px solid var(--border); white-space:nowrap; }
.apv-table tbody td { padding:12px 14px; font-size:12.5px; color:var(--text2); vertical-align:middle; border-bottom:1px solid var(--border); }
.apv-task-title { font-size:13px; font-weight:600; color:var(--text); line-height:1.3; margin-bottom:1px; }
.apv-task-sub { font-size:11px; color:var(--text3); font-weight:500; }
.apv-client { display:flex; align-items:center; gap:5px; font-size:12px; font-weight:500; }
.apv-assignee { display:flex; align-items:center; gap:6px; font-size:12px; font-weight:500; }
.apv-prio { font-size:10px; font-weight:700; padding:3px 8px; border-radius:6px; white-space:nowrap; }
.apv-prio-urgent { background:var(--red-dim); color:var(--red); }
.apv-prio-high { background:rgba(249,115,22,0.1); color:#F97316; }
.apv-prio-normal { background:var(--teal-dim); color:var(--teal); }
.apv-deadline { display:flex; align-items:center; gap:4px; font-size:11.5px; font-weight:500; color:var(--text2); white-space:nowrap; }
.apv-deadline-overdue { color:var(--red); font-weight:600; }
.apv-overdue-badge { font-size:9px; font-weight:800; width:16px; height:16px; border-radius:50%; background:var(--red-dim); color:var(--red); display:inline-flex; align-items:center; justify-content:center; }
.apv-comment-count { display:inline-flex; align-items:center; justify-content:center; min-width:22px; height:22px; padding:0 5px; border-radius:6px; background:var(--card2); border:1px solid var(--border); font-size:11px; font-weight:600; color:var(--text3); }
.apv-actions { display:flex; gap:4px; justify-content:center; }
.apv-act-btn { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:8px; border:1px solid var(--border); background:var(--card); cursor:pointer; font-size:11px; text-decoration:none; color:var(--text3); }
.apv-act-approve { color:#10B981; border-color:rgba(16,185,129,0.3); background:rgba(16,185,129,0.06); }
.apv-act-revision { color:#F97316; border-color:rgba(249,115,22,0.3); background:rgba(249,115,22,0.06); }
.apv-act-comment { color:#3B82F6; border-color:rgba(59,130,246,0.3); background:rgba(59,130,246,0.06); }
.apv-act-view { color:var(--yellow); border-color:rgba(245,158,11,0.3); background:rgba(245,158,11,0.06); }
.apv-act-edit:hover { background:var(--primary-dim); border-color:var(--primary); color:var(--primary); }

.appr-modal-overlay { position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,.5); display:none; align-items:center; justify-content:center; backdrop-filter:blur(2px); }
.appr-modal-box { background:var(--card); padding:24px; border-radius:16px; width:90%; max-width:480px; border:1px solid var(--border); box-shadow:0 12px 40px rgba(0,0,0,.18); position:relative; }
.appr-modal-close { position:absolute; right:16px; top:14px; font-size:22px; color:var(--text3); cursor:pointer; line-height:1; }

.dt-container .dt-layout-row { padding:10px 14px; margin:0; }
.dt-container .dt-layout-cell { color:var(--text3); font-size:12px; }
.dt-container select,
.dt-container input { border:1px solid var(--border); border-radius:8px; background:var(--card2); color:var(--text2); font-size:12px; padding:6px 8px; }
.dt-container .dt-paging .dt-paging-button { border:1px solid var(--border) !important; background:var(--card2) !important; border-radius:8px; color:var(--text2) !important; }
.dt-container .dt-paging .dt-paging-button.current { background:var(--primary) !important; color:#fff !important; border-color:var(--primary) !important; }

/* Disable global/page loaders for this screen only */
#pageLoader,
.page-loader,
div.dt-processing {
    display: none !important;
}

@media (max-width:900px) {
    .apv-filter-selects { width:100%; }
    .apv-search-wrap { min-width:100%; }
    .apv-table thead th { font-size:9.5px; padding:10px 10px; }
    .apv-table tbody td { padding:10px 10px; font-size:11.5px; }
}
@media (max-width:640px) {
    .apv-header { flex-direction:column; align-items:flex-start; }
    .apv-filter-form { flex-direction:column; }
    .apv-filter-selects,
    .apv-date-range { width:100%; }
    .apv-select,
    .apv-date-input { width:100%; min-width:0; }
}
</style>
@endpush

@section('content')
<div class="apv-header">
    <div>
        <div class="apv-title"><i class="fa-solid fa-clipboard-check" style="color:var(--yellow);margin-right:6px"></i> Approval Tracker</div>
        <div class="apv-subtitle" id="approvalSubtitle">{{ number_format($pendingReviewCount) }} pending approval{{ $pendingReviewCount !== 1 ? 's' : '' }} — review and take action</div>
    </div>
    <a href="{{ route('strategist.tracking') }}" class="apv-back-btn" data-no-loader><i class="fa-solid fa-arrow-left"></i> Back to Tracking</a>
</div>

<div class="apv-filter-bar">
    <form id="approvalFilters" class="apv-filter-form" autocomplete="off" data-no-loader>
        <div class="apv-search-wrap">
            <i class="fa-solid fa-magnifying-glass apv-search-icon"></i>
            <input type="text" id="filter_search" name="search_term" placeholder="Search tasks, client, designer..." class="apv-search-input">
        </div>

        <div class="apv-filter-selects">
            <select id="filter_sort" name="sort" class="apv-select">
                <option value="deadline_asc" selected>Deadline ↑</option>
                <option value="deadline_desc">Deadline ↓</option>
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
            </select>

            <select id="filter_client" name="client" class="apv-select">
                <option value="">All Clients</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                @endforeach
            </select>

            <select id="filter_type" name="type" class="apv-select">
                <option value="all">All Types</option>
                <option value="reel">Reel</option>
                <option value="post">Post</option>
                <option value="story">Story</option>
                <option value="video">Video</option>
                <option value="carousel">Carousel</option>
                <option value="website">Website</option>
                <option value="software">Software</option>
            </select>

            <select id="filter_priority" name="priority" class="apv-select">
                <option value="all">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="normal">Normal</option>
            </select>

            <select id="filter_platform" name="platform" class="apv-select">
                <option value="all">All Platforms</option>
                <option value="instagram">Instagram</option>
                <option value="facebook">Facebook</option>
                <option value="linkedin">LinkedIn</option>
                <option value="twitter">Twitter</option>
                <option value="youtube">YouTube</option>
                <option value="tiktok">TikTok</option>
            </select>

            <select id="filter_assigned_to" name="assigned_to" class="apv-select">
                <option value="">All Designers</option>
                @foreach($designers as $designer)
                    <option value="{{ $designer->id }}">{{ $designer->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="apv-date-range">
            <div class="apv-date-field">
                <label class="apv-date-label" for="filter_date_from">From</label>
                <input type="date" id="filter_date_from" name="date_from" class="apv-date-input">
            </div>
            <div class="apv-date-field">
                <label class="apv-date-label" for="filter_date_to">To</label>
                <input type="date" id="filter_date_to" name="date_to" class="apv-date-input">
            </div>
        </div>

        <button type="submit" class="apv-filter-btn"><i class="fa-solid fa-filter"></i> Apply</button>
        <button type="button" id="clearApprovalFilters" class="apv-clear-btn"><i class="fa-solid fa-xmark"></i> Clear</button>
    </form>
</div>

<div class="apv-table-card">
    <div class="apv-table-wrap">
        <table id="approvalsTable" class="apv-table display" style="width:100%">
            <thead>
                <tr>
                    <th style="min-width:200px">Task</th>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Assigned To</th>
                    <th>Priority</th>
                    <th>Deadline</th>
                    <th><i class="fas fa-comments" style="color:var(--text3)"></i></th>
                    <th style="width:180px;text-align:center">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div id="revisionModal" class="appr-modal-overlay">
    <div class="appr-modal-box">
        <span class="appr-modal-close" onclick="closeRevisionModal()">&times;</span>
        <div style="font-weight:700;font-size:15px;margin-bottom:4px"><i class="fas fa-sync-alt" style="color:var(--primary);margin-right:4px"></i> Request Revision</div>
        <div id="revisionTaskTitle" style="font-size:12px;color:var(--text3);margin-bottom:14px"></div>
        <form id="revisionForm" method="POST" data-no-loader>
            @csrf
            @method('PATCH')
            <textarea name="revision_note" placeholder="Describe what needs to be changed..." required style="width:100%;min-height:80px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card2);font-size:13px;margin-bottom:12px;resize:vertical;font-family:inherit;color:var(--text1)"></textarea>
            <div style="display:flex;gap:8px;justify-content:flex-end">
                <button type="button" class="btn-sec" onclick="closeRevisionModal()">Cancel</button>
                <button type="submit" class="btn-primary">Send Revision Request</button>
            </div>
        </form>
    </div>
</div>

<div id="commentModal" class="appr-modal-overlay">
    <div class="appr-modal-box">
        <span class="appr-modal-close" onclick="closeCommentModal()">&times;</span>
        <div style="font-weight:700;font-size:15px;margin-bottom:4px"><i class="fas fa-comment" style="color:var(--primary);margin-right:4px"></i> Add Comment</div>
        <div id="commentTaskTitle" style="font-size:12px;color:var(--text3);margin-bottom:14px"></div>
        <form id="commentForm" method="POST" data-no-loader>
            @csrf
            <textarea name="body" placeholder="Write your comment..." required style="width:100%;min-height:80px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card2);font-size:13px;margin-bottom:12px;resize:vertical;font-family:inherit;color:var(--text1)"></textarea>
            <div style="display:flex;gap:8px;justify-content:flex-end">
                <button type="button" class="btn-sec" onclick="closeCommentModal()">Cancel</button>
                <button type="submit" class="btn-primary">Post Comment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script>
let approvalsTable = null;
let searchDebounce = null;
const revisionActionTemplate = @json(route('strategist.tasks.request-revision', ['task' => '__TASK__']));
const commentActionTemplate = @json(route('strategist.tasks.comment', ['task' => '__TASK__']));

function initApprovalsDataTable(selector, options) {
    if (typeof DataTable === 'function') {
        return new DataTable(selector, options);
    }

    if (typeof window.jQuery !== 'undefined' && window.jQuery.fn && typeof window.jQuery.fn.DataTable === 'function') {
        return window.jQuery(selector).DataTable(options);
    }

    return null;
}

function reloadApprovalsTable() {
    if (!approvalsTable) return;

    if (approvalsTable.ajax && typeof approvalsTable.ajax.reload === 'function') {
        approvalsTable.ajax.reload();
        return;
    }

    if (typeof approvalsTable.draw === 'function') {
        approvalsTable.draw(false);
    }
}

function collectApprovalFilters() {
    return {
        search_term: document.getElementById('filter_search')?.value?.trim() || '',
        sort: document.getElementById('filter_sort')?.value || 'deadline_asc',
        client: document.getElementById('filter_client')?.value || '',
        type: document.getElementById('filter_type')?.value || 'all',
        priority: document.getElementById('filter_priority')?.value || 'all',
        platform: document.getElementById('filter_platform')?.value || 'all',
        assigned_to: document.getElementById('filter_assigned_to')?.value || '',
        date_from: document.getElementById('filter_date_from')?.value || '',
        date_to: document.getElementById('filter_date_to')?.value || ''
    };
}

function applyApprovalFilterDefaults() {
    const defaults = {
        filter_search: '',
        filter_sort: 'deadline_asc',
        filter_client: '',
        filter_type: 'all',
        filter_priority: 'all',
        filter_platform: 'all',
        filter_assigned_to: '',
        filter_date_from: '',
        filter_date_to: ''
    };

    Object.entries(defaults).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value;
        }
    });
}

function updateApprovalSubtitle(count) {
    const subtitle = document.getElementById('approvalSubtitle');
    if (!subtitle) return;
    const numericCount = Number(count) || 0;
    subtitle.textContent = `${numericCount.toLocaleString()} pending approval${numericCount === 1 ? '' : 's'} — review and take action`;
}

function showAjaxMessage(type, message) {
    if (typeof ajax !== 'undefined') {
        if (type === 'success') {
            ajax.showSuccess(message);
            return;
        }
        ajax.showError(message);
        return;
    }
    alert(message);
}

function openRevisionModal(taskId, title) {
    const modal = document.getElementById('revisionModal');
    const form = document.getElementById('revisionForm');
    const taskTitle = document.getElementById('revisionTaskTitle');
    if (!modal || !form || !taskTitle) return;

    form.action = revisionActionTemplate.replace('__TASK__', String(taskId));
    taskTitle.textContent = `"${title}"`;
    modal.style.display = 'flex';
    form.querySelector('textarea')?.focus();
}

function closeRevisionModal() {
    const modal = document.getElementById('revisionModal');
    if (modal) modal.style.display = 'none';
}

function openCommentModal(taskId, title) {
    const modal = document.getElementById('commentModal');
    const form = document.getElementById('commentForm');
    const taskTitle = document.getElementById('commentTaskTitle');
    if (!modal || !form || !taskTitle) return;

    form.action = commentActionTemplate.replace('__TASK__', String(taskId));
    taskTitle.textContent = `"${title}"`;
    modal.style.display = 'flex';
    form.querySelector('textarea')?.focus();
}

function closeCommentModal() {
    const modal = document.getElementById('commentModal');
    if (modal) modal.style.display = 'none';
}

async function postTaskAction(form, fallbackError) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const response = await fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: new FormData(form)
    });

    let payload = {};
    try {
        payload = await response.json();
    } catch (error) {
        payload = {};
    }

    if (!response.ok || payload.success === false) {
        throw new Error(payload.message || fallbackError);
    }

    return payload;
}

document.addEventListener('DOMContentLoaded', function() {
    applyApprovalFilterDefaults();

    const tableElement = document.getElementById('approvalsTable');
    if (!tableElement) return;
    if (typeof DataTable === 'undefined' && (typeof window.jQuery === 'undefined' || !window.jQuery.fn || typeof window.jQuery.fn.DataTable !== 'function')) {
        showAjaxMessage('error', 'Failed to load approvals table library. Please refresh the page.');
        return;
    }

    approvalsTable = initApprovalsDataTable('#approvalsTable', {
        processing: false,
        serverSide: true,
        searching: false,
        ordering: false,
        deferRender: true,
        pageLength: 25,
        lengthMenu: [25, 50, 100],
        ajax: {
            url: '{{ route('strategist.approvals.data') }}',
            data: function(d) {
                Object.assign(d, collectApprovalFilters());
            },
            dataSrc: function(json) {
                updateApprovalSubtitle(json.recordsFiltered ?? 0);
                return json.data || [];
            },
            error: function(xhr) {
                updateApprovalSubtitle(0);
                let message = 'Failed to load approvals data.';
                if (xhr?.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                showAjaxMessage('error', message);
            }
        },
        columns: [
            { data: 'task', name: 'task' },
            { data: 'client', name: 'client' },
            { data: 'type', name: 'type' },
            { data: 'assignee', name: 'assignee' },
            { data: 'priority', name: 'priority' },
            { data: 'deadline', name: 'deadline' },
            { data: 'comments_count', name: 'comments_count' },
            { data: 'actions', name: 'actions' }
        ],
        language: {
            processing: 'Loading approvals...',
            emptyTable: 'No pending approvals found.',
            zeroRecords: 'No matching approvals found.'
        }
    });

    if (!approvalsTable) {
        showAjaxMessage('error', 'Failed to initialize approvals table. Please refresh the page.');
        return;
    }

    const filterForm = document.getElementById('approvalFilters');
    const clearButton = document.getElementById('clearApprovalFilters');
    const searchInput = document.getElementById('filter_search');
    const autoReloadFields = ['filter_sort', 'filter_client', 'filter_type', 'filter_priority', 'filter_platform', 'filter_assigned_to', 'filter_date_from', 'filter_date_to'];

    filterForm?.addEventListener('submit', function(event) {
        event.preventDefault();
        reloadApprovalsTable();
    });

    autoReloadFields.forEach(function(fieldId) {
        document.getElementById(fieldId)?.addEventListener('change', function() {
            reloadApprovalsTable();
        });
    });

    searchInput?.addEventListener('input', function() {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(function() {
            reloadApprovalsTable();
        }, 450);
    });

    clearButton?.addEventListener('click', function() {
        applyApprovalFilterDefaults();
        reloadApprovalsTable();
    });

    tableElement.addEventListener('submit', async function(event) {
        const form = event.target.closest('.js-approve-form');
        if (!form) return;

        event.preventDefault();
        if (!confirm('Approve and send to admin for final approval?')) return;

        try {
            const payload = await postTaskAction(form, 'Failed to approve task.');
            showAjaxMessage('success', payload.message || 'Task approved.');
            reloadApprovalsTable();
        } catch (error) {
            showAjaxMessage('error', error.message);
        }
    });

    document.getElementById('revisionForm')?.addEventListener('submit', async function(event) {
        event.preventDefault();
        const form = event.target;

        try {
            const payload = await postTaskAction(form, 'Failed to request revision.');
            showAjaxMessage('success', payload.message || 'Revision requested.');
            closeRevisionModal();
            form.reset();
            reloadApprovalsTable();
        } catch (error) {
            showAjaxMessage('error', error.message);
        }
    });

    document.getElementById('commentForm')?.addEventListener('submit', async function(event) {
        event.preventDefault();
        const form = event.target;

        try {
            const payload = await postTaskAction(form, 'Failed to post comment.');
            showAjaxMessage('success', payload.message || 'Comment posted.');
            closeCommentModal();
            form.reset();
            reloadApprovalsTable();
        } catch (error) {
            showAjaxMessage('error', error.message);
        }
    });

    document.getElementById('revisionModal')?.addEventListener('click', function(event) {
        if (event.target === this) closeRevisionModal();
    });

    document.getElementById('commentModal')?.addEventListener('click', function(event) {
        if (event.target === this) closeCommentModal();
    });
});
</script>
@endpush
