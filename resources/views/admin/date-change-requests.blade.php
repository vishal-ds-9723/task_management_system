@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title"><i class="fas fa-calendar-alt" style="margin-right:8px;color:var(--primary)"></i> Post Date Change Requests</div>
        <div class="page-subtitle">Review and manage date change requests from strategists</div>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn-sec">← Back to Dashboard</a>
</div>

<div style="display:grid;gap:16px">
    @if($dateChangeRequests->count() > 0)
        @foreach($dateChangeRequests as $req)
            @php
                $task = $req->task;
                // Parse requested date from subtitle: '... post date → 2026-04-15'
                preg_match('/→\s*(\S+)$/', $req->subtitle, $dateMatch);
                $requestedDate = $dateMatch[1] ?? '';
                $requesterName = $task?->creator?->name ?? 'Unknown';
            @endphp
            @if($task)
            <div class="card">
                <div class="dcr-card">
                    <div class="dcr-info">
                        <div class="dcr-title">{{ $task->title }}</div>
                        <div class="dcr-meta">
                            {{ $task->client->emoji ?? '' }} {{ $task->client->name ?? '' }}
                            · Requested by <strong>{{ $requesterName }}</strong>
                            · {{ $req->created_at->diffForHumans() }}
                        </div>
                        <div class="dcr-dates">
                            <span class="dcr-date-old"><i class="fas fa-calendar-alt" style="font-size:10px;opacity:0.5"></i> Current: <strong>{{ $task->post_date?->format('d M Y') ?? 'Not set' }}</strong></span>
                            <span class="dcr-arrow">→</span>
                            <span class="dcr-date-new"><i class="fas fa-calendar-alt" style="font-size:10px;opacity:0.5"></i> Requested: <strong>{{ $requestedDate ? \Carbon\Carbon::parse($requestedDate)->format('d M Y') : 'N/A' }}</strong></span>
                        </div>
                    </div>
                    <div class="dcr-actions">
                        {{-- Approve --}}
                        <form method="POST" action="{{ route('admin.tasks.approve-date', $task) }}" style="margin:0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="new_post_date" value="{{ $requestedDate }}">
                            <input type="hidden" name="notification_id" value="{{ $req->id }}">
                            <button type="submit" class="dcr-btn dcr-approve" onclick="return confirm('Approve post date change to {{ $requestedDate }}?')"><i class="fas fa-check-circle"></i> Approve</button>
                        </form>
                        {{-- Reject --}}
                        <button type="button" class="dcr-btn dcr-reject" onclick="toggleRejectForm({{ $req->id }})"><i class="fas fa-times-circle"></i> Decline</button>
                        {{-- View Task --}}
                        <a href="{{ route('admin.tasks.show', $task) }}" class="dcr-btn dcr-view" style="text-decoration:none"><i class="fas fa-eye"></i> View</a>
                    </div>
                    {{-- Reject reason form (hidden by default) --}}
                    <div id="rejectForm-{{ $req->id }}" class="dcr-reject-form" style="display:none;width:100%;margin-top:12px">
                        <form method="POST" action="{{ route('admin.tasks.reject-date', $task) }}" style="display:flex;gap:8px;align-items:flex-end;width:100%">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="notification_id" value="{{ $req->id }}">
                            <input type="text" name="reject_reason" placeholder="Reason for declining (optional)" style="flex:1;padding:7px 12px;border:1px solid var(--border);border-radius:8px;font-size:12px;background:var(--card2);color:var(--text1)">
                            <button type="submit" class="btn-primary" style="padding:7px 14px;font-size:12px;white-space:nowrap">Confirm Decline</button>
                            <button type="button" class="btn-sec" style="padding:7px 10px;font-size:12px" onclick="toggleRejectForm({{ $req->id }})">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @endforeach

        {{-- Pagination --}}
        <div style="display:flex;justify-content:center;margin-top:20px">
            {{ $dateChangeRequests->links('vendor.pagination.custom') }}
        </div>
    @else
        <div class="card" style="text-align:center;padding:40px;color:var(--text3)">
            <div style="font-size:48px;margin-bottom:16px"><i class="fas fa-star" style="color:var(--primary)"></i></div>
            <div style="font-size:18px;font-weight:700;margin-bottom:6px">No pending requests</div>
            <div style="font-size:13px">All date change requests have been reviewed</div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleRejectForm(id){
    const el=document.getElementById('rejectForm-'+id);
    if(el) el.style.display = el.style.display==='none'?'block':'none';
}

/**
 * DATE CHANGE REQUESTS AJAX - Approve/Reject Without Page Refresh
 */
document.addEventListener('DOMContentLoaded', function() {
    // Intercept approve forms
    document.querySelectorAll('form[action*="approve-date"]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!confirm(this.querySelector('button').getAttribute('onclick')?.match(/confirm\('(.+?)'\)/)?.[1] || 'Approve this date change?')) return;

            const card = this.closest('.card');
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: formData
                });
                if (!response.ok) throw new Error('Approval failed');
                const data = await response.json();

                if (card) {
                    card.style.transition = 'opacity 0.4s, transform 0.4s';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => card.remove(), 400);
                }
                if (typeof ajax !== 'undefined') ajax.showSuccess(data.message || 'Date change approved!');
            } catch (error) {
                console.error('Approve error:', error);
                if (typeof ajax !== 'undefined') ajax.showError('Failed to approve date change');
            }
        });
    });

    // Intercept reject forms
    document.querySelectorAll('form[action*="reject-date"]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const card = this.closest('.card');
            try {
                const formData = new FormData(this);
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                    body: formData
                });
                if (!response.ok) throw new Error('Rejection failed');
                const data = await response.json();

                if (card) {
                    card.style.transition = 'opacity 0.4s, transform 0.4s';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => card.remove(), 400);
                }
                if (typeof ajax !== 'undefined') ajax.showSuccess(data.message || 'Date change declined.');
            } catch (error) {
                console.error('Reject error:', error);
                if (typeof ajax !== 'undefined') ajax.showError('Failed to decline date change');
            }
        });
    });
});
</script>
@endpush
