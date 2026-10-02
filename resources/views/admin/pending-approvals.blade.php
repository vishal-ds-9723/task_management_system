@extends('layouts.app')

@section('content')
<div class="pending-approvals-page">
    <div class="topbar">
        <div>
            <div class="page-title"><i class="fas fa-clipboard-check" style="margin-right:8px;color:var(--primary)"></i> Pending Task Approvals</div>
            <div class="page-subtitle">Review and approve or reject tasks submitted by designers</div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-sec">← Back to Dashboard</a>
    </div>

    <div class="pending-approvals">
        <div class="pending-grid">
    @if($tasks->count() > 0)
        @foreach($tasks as $task)
            @php
                $priorityColors = ['normal'=>'#6b7280','high'=>'#F59E0B','urgent'=>'#EF4444'];
                $typeColors = ['reel'=>'#F97316','post'=>'#3B82F6','story'=>'#14B8A6','video'=>'#8B5CF6','carousel'=>'#EC4899'];
            @endphp
            <div class="card task-card" style="--priority-color: {{ $priorityColors[$task->priority] ?? '#6b7280' }}; --type-color: {{ $typeColors[$task->type] ?? '#4F6DF0' }}; --card-index: {{ $loop->index }}">
                <div class="dcr-card">
                    <div class="dcr-info">
                        <div class="dcr-title task-title">
                            {{ $task->title ?? 'Untitled' }}
                            <span class="badge badge--type" style="--badge-color: {{ $typeColors[$task->type] ?? '#4F6DF0' }}">{{ ucfirst($task->type ?? 'Task') }}</span>
                            <span class="badge badge--priority" style="--badge-color: {{ $priorityColors[$task->priority] ?? '#6b7280' }}">{{ ucfirst($task->priority ?? 'Normal') }}</span>
                        </div>
                        <div class="dcr-meta task-meta">
                            {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!} {{ $task->client->name ?? 'Unknown' }}
                            · Assigned to <strong>{{ $task->assignee->name ?? 'Unassigned' }}</strong>
                            · Created by <strong>{{ $task->creator->name ?? 'Unknown' }}</strong>
                        </div>
                        <div class="dcr-dates task-dates">
                            @if($task->submitted_at)
                                <span><i class="fas fa-clipboard-check" style="font-size:10px;opacity:0.5"></i> Submitted: <strong>{{ $task->submitted_at->format('d M Y, h:i A') }}</strong> ({{ $task->submitted_at->diffForHumans() }})</span>
                            @endif
                            @if($task->deadline)
                                <span><i class="fas fa-calendar-alt" style="font-size:10px;opacity:0.5"></i> Deadline: <strong>{{ $task->deadline->format('d M Y') }}</strong></span>
                            @endif
                            @if($task->post_date)
                                <span><i class="fas fa-paper-plane" style="font-size:10px;opacity:0.5"></i> Post Date: <strong>{{ $task->post_date->format('d M Y') }}</strong></span>
                            @endif
                        </div>
                    </div>
                    <div class="dcr-actions task-actions">
                        {{-- Approve --}}
                        <form method="POST" action="{{ route('admin.tasks.approve-task', $task) }}" style="margin:0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="dcr-btn dcr-approve" onclick="return confirm('Approve this task?')"><i class="fas fa-check-circle"></i> Approve</button>
                        </form>
                        {{-- Reject --}}
                        <button id="toggleRejectBtn-{{ $task->id }}" type="button" class="dcr-btn dcr-reject" onclick="toggleRejectTaskForm({{ $task->id }})" aria-expanded="false" aria-controls="rejectTaskForm-{{ $task->id }}"><i class="fas fa-times-circle"></i> Reject</button>
                        {{-- View Task --}}
                        <a href="{{ route('admin.tasks.show', $task) }}" class="dcr-btn dcr-view" style="text-decoration:none"><i class="fas fa-eye"></i> View</a>
                    </div>
                    {{-- Reject reason form (hidden by default) --}}
                    <div id="rejectTaskForm-{{ $task->id }}" class="dcr-reject-form reject-form" style="display:none" data-open="false">
                        <form method="POST" action="{{ route('admin.tasks.reject-task', $task) }}" class="reject-row">
                            @csrf
                            @method('PATCH')
                            <div class="reject-field">
                                <label class="sr-only" for="rejection-reason-{{ $task->id }}">Rejection reason</label>
                                <textarea id="rejection-reason-{{ $task->id }}" name="rejection_reason" rows="2" maxlength="200" placeholder="Reason for rejection (optional)" class="reject-input"></textarea>
                                <div class="reject-help">Optional, max 200 characters</div>
                            </div>
                            <div class="reject-actions">
                                <button type="submit" class="btn-primary" onclick="return confirm('Reject and send back for revision?')">Confirm Reject</button>
                                <button type="button" class="btn-sec" onclick="toggleRejectTaskForm({{ $task->id }})">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="card empty-state" style="text-align:center;padding:40px;color:var(--text3)">
            <div style="font-size:48px;margin-bottom:16px"><i class="fas fa-star" style="color:var(--primary)"></i></div>
            <div style="font-size:18px;font-weight:700;margin-bottom:6px">No pending approvals</div>
            <div style="font-size:13px">All submitted tasks have been reviewed</div>
        </div>
    @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Space+Grotesk:wght@500;700&display=swap');

.pending-approvals-page {
    position: relative;
    padding: 12px 4px 8px;
    border-radius: 18px;
    background: linear-gradient(135deg, #f8fafc 0%, #fff7ed 50%, #ecfeff 100%);
    overflow: hidden;
    font-family: "DM Sans", "Segoe UI", sans-serif;
}
.pending-approvals-page::before,
.pending-approvals-page::after {
    content: "";
    position: absolute;
    border-radius: 999px;
    opacity: 0.5;
    z-index: 0;
}
.pending-approvals-page::before {
    width: 280px;
    height: 280px;
    right: -120px;
    top: -140px;
    background: radial-gradient(circle, rgba(14, 116, 144, 0.18) 0%, transparent 70%);
}
.pending-approvals-page::after {
    width: 220px;
    height: 220px;
    left: -100px;
    bottom: -120px;
    background: radial-gradient(circle, rgba(251, 146, 60, 0.2) 0%, transparent 70%);
}
.pending-approvals-page > * {
    position: relative;
    z-index: 1;
}
.pending-approvals-page .topbar {
    padding: 18px 18px 16px;
    border-radius: 14px;
    background: linear-gradient(120deg, #0f172a 0%, #0f766e 55%, #155e75 100%);
    color: #f8fafc;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);
    margin-bottom: 18px;
}
.pending-approvals-page .topbar .page-title {
    font-family: "Space Grotesk", "DM Sans", sans-serif;
    font-size: 20px;
    letter-spacing: 0.02em;
}
.pending-approvals-page .topbar .page-subtitle {
    color: rgba(248, 250, 252, 0.8);
}
.pending-approvals-page .topbar .btn-sec {
    border-color: rgba(248, 250, 252, 0.3);
    color: #f8fafc;
    background: rgba(15, 23, 42, 0.2);
}
.pending-approvals .pending-grid {
    display: grid;
    gap: 16px;
}
.pending-approvals .task-card {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.35);
    background: linear-gradient(160deg, rgba(255, 255, 255, 0.94) 0%, rgba(248, 250, 252, 0.96) 100%);
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
    animation: rise 0.45s ease both;
    animation-delay: calc(var(--card-index) * 60ms);
}
.pending-approvals .task-card::before {
    content: "";
    position: absolute;
    inset: 0 0 0 auto;
    width: 6px;
    background: linear-gradient(180deg, var(--type-color), var(--priority-color));
    opacity: 0.9;
}
.pending-approvals .task-title {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-family: "Space Grotesk", "DM Sans", sans-serif;
    font-size: 16px;
    color: #0f172a;
}
.pending-approvals .badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    background: color-mix(in srgb, var(--badge-color) 15%, transparent);
    color: var(--badge-color);
    letter-spacing: 0.02em;
    border: 1px solid color-mix(in srgb, var(--badge-color) 35%, transparent);
}
.pending-approvals .task-meta {
    margin-top: 4px;
    color: #475569;
}
.pending-approvals .task-dates {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 16px;
    margin-top: 6px;
    color: #64748b;
}
.pending-approvals .task-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}
.pending-approvals .task-actions .dcr-btn,
.pending-approvals .task-actions .btn-sec,
.pending-approvals .task-actions .btn-primary {
    min-width: 96px;
    justify-content: center;
    border-radius: 10px;
    padding: 8px 14px;
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.pending-approvals .task-actions .dcr-btn:hover,
.pending-approvals .task-actions .btn-sec:hover,
.pending-approvals .task-actions .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.12);
}
.pending-approvals .dcr-approve {
    background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
    border: 1px solid rgba(16, 185, 129, 0.5);
    color: #0b1f1a;
}
.pending-approvals .dcr-reject {
    background: linear-gradient(135deg, #F87171 0%, #EF4444 100%);
    border: 1px solid rgba(239, 68, 68, 0.5);
    color: #3f0a10;
}
.pending-approvals .dcr-view {
    background: #0f172a;
    border: 1px solid rgba(15, 23, 42, 0.7);
    color: #f8fafc;
}
.pending-approvals .reject-form {
    margin-top: 12px;
    width: 100%;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(148, 163, 184, 0.4);
    border-radius: 10px;
    padding: 12px;
}
.pending-approvals .reject-row {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    width: 100%;
    margin: 0;
}
.pending-approvals .reject-field {
    flex: 1;
}
.pending-approvals .reject-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid rgba(148, 163, 184, 0.5);
    border-radius: 8px;
    font-size: 12px;
    background: #ffffff;
    color: #0f172a;
    resize: vertical;
    min-height: 42px;
}
.pending-approvals .reject-help {
    margin-top: 4px;
    font-size: 11px;
    color: #64748b;
}
.pending-approvals .reject-actions {
    display: flex;
    gap: 8px;
}
.pending-approvals .empty-state {
    border-radius: 16px;
    border: 1px dashed rgba(148, 163, 184, 0.6);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(241, 245, 249, 0.9) 100%);
}
.pending-approvals .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
@keyframes rise {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@media (max-width: 768px) {
    .pending-approvals-page {
        padding: 8px 0;
        border-radius: 12px;
    }
    .pending-approvals-page .topbar {
        border-radius: 12px;
    }
    .pending-approvals .task-actions {
        flex-wrap: wrap;
    }
    .pending-approvals .reject-row {
        flex-direction: column;
        align-items: stretch;
    }
    .pending-approvals .reject-actions {
        justify-content: flex-start;
    }
}
</style>
@endpush

@push('scripts')
<script>
function toggleRejectTaskForm(id){
    const el = document.getElementById('rejectTaskForm-' + id);
    const btn = document.getElementById('toggleRejectBtn-' + id);
    if (!el) return;
    const willOpen = el.getAttribute('data-open') !== 'true';
    el.style.display = willOpen ? 'block' : 'none';
    el.setAttribute('data-open', willOpen ? 'true' : 'false');
    if (btn) btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    if (willOpen) {
        const input = el.querySelector('textarea');
        if (input) input.focus();
    }
}
</script>
@endpush
