@extends('layouts.app')

@section('content')
<div class="dash-wrapper">
    <div class="dash-container">
        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
            <div>
                <h1 style="font-size:28px;font-weight:800;color:var(--text);margin:0">Pending Assignments</h1>
                <p style="color:var(--text3);margin:4px 0 0 0;font-size:13px">Tasks waiting to be assigned to team members</p>
            </div>
            <a href="{{ route('strategist.dashboard') }}" class="btn-back"><i class="fa-solid fa-chevron-left"></i> Back</a>
        </div>

        {{-- Tasks Table --}}
        <div id="pendingAssignmentsContent">
        <div class="card">
            @if($unassignedTasks->count() > 0)
                <table class="pa-table">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Created By</th>
                            <th>Client</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Deadline</th>
                            <th>Priority</th>
                            <th>Post Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unassignedTasks as $task)
                            <tr class="pa-table-row {{ $task->deadline && \Carbon\Carbon::parse($task->deadline)->isPast() ? 'pa-overdue' : '' }}">
                                <td class="pa-table-title">
                                    <a href="{{ route('strategist.tasks.show', $task->id) }}" class="pa-link">
                                        {{ Str::limit($task->title, 40) }}
                                    </a>
                                </td>
                                <td>
                                    <span style="font-size:12px;font-weight:700;color:var(--primary);background:rgba(99,102,241,0.08);padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px">
                                        <i class="fa-solid fa-user-pen"></i> {{ $task->creator->name ?? 'Strategist' }}
                                    </span>
                                </td>
                                <td class="pa-table-client">
                                    @if($task->client->logo)
                                        <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:16px;height:16px;border-radius:4px;object-fit:cover;vertical-align:middle;margin-top:-2px">
                                    @else
                                        {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!}
                                    @endif
                                    {{ $task->client->name ?? 'N/A' }}
                                </td>
                                <td class="pa-table-type">
                                    @php
                                        $typeColors = ['reel'=>'#F97316','post'=>'#3B82F6','story'=>'#14B8A6','video'=>'#8B5CF6','carousel'=>'#EC4899'];
                                        $typeEmojis = ['reel'=>'<i class="fas fa-film"></i>','post'=>'<i class="fas fa-pen-fancy"></i>','story'=>'<i class="fas fa-mobile-alt"></i>','video'=>'<i class="fas fa-video"></i>','carousel'=>'<i class="fas fa-images"></i>'];
                                    @endphp
                                    <span class="pa-type-badge" style="background:{{ $typeColors[$task->type] ?? '#6B7280' }}10;color:{{ $typeColors[$task->type] ?? '#6B7280' }}">
                                        {!! $typeEmojis[$task->type] ?? '<i class="fas fa-clipboard-list"></i>' !!} {{ ucfirst($task->type) }}
                                    </span>
                                </td>
                                <td class="pa-table-status">
                                    <span class="pa-status-badge pa-status-{{ $task->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </td>
                                <td class="pa-table-deadline">
                                    @if($task->deadline)
                                        <span class="pa-deadline {{ \Carbon\Carbon::parse($task->deadline)->isPast() ? 'pa-deadline-overdue' : '' }}">
                                            {{ \Carbon\Carbon::parse($task->deadline)->format('M d') }}
                                        </span>
                                    @else
                                        <span class="pa-na">—</span>
                                    @endif
                                </td>
                                <td class="pa-table-priority">
                                    @php
                                        $priorityColors = ['urgent'=>'var(--red)','high'=>'var(--yellow)','normal'=>'var(--blue)','low'=>'var(--text3)'];
                                        $priorityEmojis = ['urgent'=>'<i class="fas fa-circle" style="font-size:8px;color:var(--red)"></i>','high'=>'<i class="fas fa-circle" style="font-size:8px;color:var(--yellow)"></i>','normal'=>'<i class="fas fa-circle" style="font-size:8px;color:var(--blue)"></i>','low'=>'<i class="fas fa-circle" style="font-size:8px;color:var(--text3)"></i>'];
                                    @endphp
                                    <span style="color:{{ $priorityColors[$task->priority_level] ?? 'var(--text3)' }};font-weight:700">
                                        {!! $priorityEmojis[$task->priority_level] ?? '<i class="fas fa-circle" style="font-size:8px"></i>' !!}
                                    </span>
                                </td>
                                <td class="pa-table-postdate">
                                    @if($task->post_date)
                                        {{ \Carbon\Carbon::parse($task->post_date)->format('M d') }}
                                    @else
                                        <span class="pa-na">—</span>
                                    @endif
                                </td>
                                <td class="pa-table-action">
                                    <a href="{{ route('strategist.tasks.show', $task->id) }}" class="pa-action-btn" title="Assign">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div style="text-align:center;padding:60px 24px;color:var(--text3)">
                    <i class="fa-solid fa-check-circle" style="font-size:48px;opacity:0.5;display:block;margin-bottom:16px"></i>
                    <div style="font-size:16px;font-weight:600;color:var(--text);margin-bottom:4px">All tasks assigned!</div>
                    <div style="font-size:13px">No pending assignments at the moment.</div>
                </div>
            @endif
        </div>

        {{-- Pagination --}}
        @if($unassignedTasks->hasPages())
            <div style="margin-top:20px;display:flex;justify-content:center">
                {{ $unassignedTasks->links() }}
            </div>
        @endif
        </div>{{-- end pendingAssignmentsContent --}}
    </div>
</div>

<style>
    .dash-wrapper { min-height:100vh; background:var(--body-bg); }
    .dash-container { max-width:1200px; margin:0 auto; padding:24px; }
    .btn-back { display:inline-flex; align-items:center; gap:6px; padding:8px 12px; background:var(--card); border:1px solid var(--border); border-radius:6px; color:var(--text); text-decoration:none; font-size:13px; font-weight:600; transition:all 0.2s; }
    .btn-back:hover { background:var(--card2); transform:translateX(-2px); }

    .pa-table { width:100%; border-collapse:collapse; }
    .pa-table thead { border-bottom:2px solid var(--border); }
    .pa-table thead th { text-align:left; padding:12px 10px; font-size:11px; font-weight:700; color:var(--text2); text-transform:uppercase; letter-spacing:0.3px; white-space:nowrap; }
    .pa-table tbody tr { border-bottom:1px solid var(--border); transition:all 0.2s; }
    .pa-table tbody tr:hover { background:var(--card2); }
    .pa-table tbody tr.pa-overdue { background:rgba(239,68,68,0.05); }
    .pa-table td { padding:11px 10px; font-size:12px; color:var(--text); }
    .pa-table-title { font-weight:600; }
    .pa-link { color:var(--primary); text-decoration:none; font-weight:600; }
    .pa-link:hover { text-decoration:underline; }
    .pa-table-client { }
    .pa-table-type { }
    .pa-type-badge { display:inline-block; padding:4px 10px; border-radius:4px; font-size:11px; font-weight:700; }
    .pa-table-status { }
    .pa-status-badge { display:inline-block; padding:4px 10px; border-radius:4px; font-size:11px; font-weight:700; }
    .pa-status-badge.pa-status-unassigned { background:rgba(245,158,11,0.1); color:var(--yellow); }
    .pa-status-badge.pa-status-assigned { background:rgba(59,182,246,0.1); color:var(--blue); }
    .pa-status-badge.pa-status-in_progress { background:rgba(204,49,14,0.1); color:var(--primary); }
    .pa-status-badge.pa-status-pending_approval { background:rgba(139,92,246,0.1); color:var(--purple); }
    .pa-status-badge.pa-status-completed { background:rgba(16,185,129,0.1); color:var(--teal); }
    .pa-table-deadline { font-weight:600; }
    .pa-deadline { padding:4px 8px; border-radius:4px; background:var(--card2); }
    .pa-deadline-overdue { background:rgba(239,68,68,0.1); color:var(--red); font-weight:700; }
    .pa-table-priority { text-align:center; font-size:14px; }
    .pa-table-postdate { font-weight:600; }
    .pa-table-action { text-align:right; width:60px; }
    .pa-action-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; background:var(--primary); color:#fff; text-decoration:none; font-size:13px; transition:all 0.2s; }
    .pa-action-btn:hover { opacity:0.9; transform:scale(1.1); box-shadow:0 2px 8px rgba(204,49,14,0.3); }
    .pa-na { color:var(--text3); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setupPaginationAJAX();
});

function setupPaginationAJAX() {
    const container = document.getElementById('pendingAssignmentsContent');
    if (!container) return;

    container.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (!link) return;
        e.preventDefault();
        loadPage(link.href);
    });
}

async function loadPage(url) {
    const container = document.getElementById('pendingAssignmentsContent');
    container.style.opacity = '0.5';
    container.style.pointerEvents = 'none';

    try {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        });

        if (!response.ok) throw new Error('Failed to load page');

        const html = await response.text();
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');
        const newContent = newDoc.getElementById('pendingAssignmentsContent');

        if (newContent) {
            container.innerHTML = newContent.innerHTML;
        }

        // Update URL without reload
        window.history.pushState({}, '', url);
    } catch (error) {
        console.error('Pagination error:', error);
    } finally {
        container.style.opacity = '1';
        container.style.pointerEvents = '';
    }
}

// Handle browser back/forward
window.addEventListener('popstate', function() {
    loadPage(window.location.href);
});
</script>
@endsection
