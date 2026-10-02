@extends('layouts.app')

@section('content')

<style>
    /* Calendar Container */
    .cc-container {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 20px;
    }

    .cc-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .cc-back-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--bg);
        color: var(--text2);
        text-decoration: none;
        transition: all 0.15s;
    }

    .cc-back-btn:hover {
        background: var(--primary-dim);
        color: var(--primary);
        border-color: var(--primary);
    }

    .cc-title {
        flex: 1;
    }

    .cc-title h2 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cc-title-subtitle {
        font-size: 13px;
        color: var(--text3);
        margin-top: 4px;
    }

    .cc-client-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .cc-client-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, {{ $client->color }}, {{ $client->color }}dd);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        font-weight: 700;
    }

    /* Festival Grid */
    .cc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .cc-festival-card {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        transition: all 0.2s;
    }

    .cc-festival-card:hover {
        border-color: {{ $client->color }};
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .cc-festival-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .cc-festival-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        flex: 1;
    }

    .cc-festival-date {
        font-size: 12px;
        color: var(--text3);
        font-weight: 500;
    }

    .cc-festival-types {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 12px;
    }

    .cc-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(135deg, {{ $client->color }}15, {{ $client->color }}08);
        color: {{ $client->color }};
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 600;
        border: 1px solid {{ $client->color }}20;
    }

    .cc-festival-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border);
    }

    .cc-info-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--text2);
    }

    .cc-info-label {
        color: var(--text3);
        font-size: 11px;
    }

    .cc-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .cc-status-pending {
        background: #fef3c7;
        color: #b45309;
    }

    .cc-status-assigned {
        background: #dcfce7;
        color: #15803d;
    }

    .cc-user-info {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--text2);
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid var(--border);
    }

    .cc-user-avatar {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        background: {{ $client->color }}20;
        color: {{ $client->color }};
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
    }

    .cc-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--primary);
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.15s;
    }

    .cc-action-btn:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .cc-empty {
        text-align: center;
        padding: 60px 40px;
        color: var(--text3);
    }

    .cc-empty i {
        display: block;
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.3;
    }

    .cc-empty-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 6px;
        color: var(--text2);
    }

    .cc-empty-text {
        font-size: 13px;
    }

    /* Stats Row */
    .cc-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .cc-stat-card {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
    }

    .cc-stat-value {
        font-size: 24px;
        font-weight: 700;
        color: {{ $client->color }};
        line-height: 1;
    }

    .cc-stat-label {
        font-size: 12px;
        color: var(--text3);
        margin-top: 8px;
        font-weight: 500;
    }
</style>

{{-- Header --}}
<div class="cc-container">
    <div class="cc-header">
        <a href="{{ route('admin.clients.show', $client) }}" class="cc-back-btn">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="cc-title">
            <h2>
                <i class="fa-solid fa-calendar-days"></i>
                Festival Calendar
            </h2>
            <div class="cc-title-subtitle">
                Selected festivals and their status
            </div>
        </div>
        <div class="cc-client-info">
            <div class="cc-client-avatar">
                @if($client->emoji)
                    {{ $client->emoji }}
                @else
                    {{ strtoupper(substr($client->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <div style="font-size: 14px; font-weight: 600; color: var(--text)">{{ $client->name }}</div>
                <div style="font-size: 12px; color: var(--text3)">Client's Calendar</div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="cc-stats">
        <div class="cc-stat-card">
            <div class="cc-stat-value">{{ $festivalSelections->count() }}</div>
            <div class="cc-stat-label">Total Selections</div>
        </div>
        <div class="cc-stat-card">
            <div class="cc-stat-value">{{ $festivalSelections->filter(fn($s) => $s->tasks()->exists())->count() }}</div>
            <div class="cc-stat-label">Assigned</div>
        </div>
        <div class="cc-stat-card">
            <div class="cc-stat-value">{{ $festivalSelections->filter(fn($s) => !$s->tasks()->exists())->count() }}</div>
            <div class="cc-stat-label">Pending</div>
        </div>
        <div class="cc-stat-card">
            <div class="cc-stat-value">
                {{ $festivalSelections->sum(fn($s) => count($s->content_types ?? [])) }}
            </div>
            <div class="cc-stat-label">Content Types</div>
        </div>
    </div>
</div>

{{-- Festival Grid --}}
<div class="cc-container">
    @if($festivalSelections->count() > 0)
        <div class="cc-grid">
            @foreach($festivalSelections as $selection)
                @php
                    $hasTask = $selection->tasks()->exists();
                    $isNew = $selection->created_at >= now()->subHours(24);
                @endphp
                <div class="cc-festival-card">
                    <div class="cc-festival-header">
                        <div class="cc-festival-name">{{ $selection->festival->name }}</div>
                        <div class="cc-festival-date">
                            {{ $selection->created_at->format('M d') }}
                        </div>
                    </div>

                    {{-- Content Types --}}
                    @if($selection->content_types && count($selection->content_types) > 0)
                        <div class="cc-festival-types">
                            @foreach($selection->content_types as $type)
                                <span class="cc-type-badge">
                                    {{ ucfirst($type) }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Status & Info --}}
                    <div class="cc-festival-info">
                        <div>
                            <div class="cc-info-item">
                                <span class="cc-info-label">Status:</span>
                                <span class="cc-status-badge {{ $hasTask ? 'cc-status-assigned' : 'cc-status-pending' }}">
                                    {{ $hasTask ? '✓ Assigned' : '⏳ Pending' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- User Info --}}
                    @if($selection->user)
                        <div class="cc-user-info">
                            <div class="cc-user-avatar">
                                {{ strtoupper(substr($selection->user->name, 0, 1)) }}
                            </div>
                            <span>{{ $selection->user->name }}</span>
                        </div>
                    @endif

                    {{-- Action Button --}}
                    <div style="margin-top: 12px; display: flex; gap: 8px;">
                        <a href="{{ route('admin.festival.selection.show', $selection->id) }}" class="cc-action-btn" style="background: var(--bg); color: var(--text2); border: 1px solid var(--border);">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                        @if(!$hasTask)
                            <button onclick="openAssignModal({{ $selection->id }})" class="cc-action-btn">
                                <i class="fa-solid fa-plus"></i> Assign
                            </button>
                        @else
                            <span class="cc-action-btn" style="background: #10b981; opacity: 0.7; cursor: default;">
                                <i class="fa-solid fa-check"></i> Assigned
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="cc-empty">
            <i class="fa-solid fa-calendar-xmark"></i>
            <div class="cc-empty-title">No Festival Selections</div>
            <div class="cc-empty-text">No festivals have been selected for this client yet.</div>
        </div>
    @endif
</div>

@include('admin.partials.assign-modal')

<script>
function openAssignModal(id) {
    const modal = document.getElementById('assignModal');
    const form = document.getElementById('assignForm');
    const input = document.getElementById('selection_id');

    if (!modal || !input || !form) {
        console.error('Modal, form or input not found');
        return;
    }

    input.value = id;
    form.action = `/admin/festival-selection/${id}/assign-task`;
    modal.style.display = 'flex';
}

function closeAssignModal() {
    document.getElementById('assignModal').style.display = 'none';
}
</script>

@endsection
