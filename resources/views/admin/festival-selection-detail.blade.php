@extends('layouts.app')

@section('content')

<style>
    .fs-header {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--border);
    }

    .fs-back-btn {
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

    .fs-back-btn:hover {
        background: var(--primary-dim);
        color: var(--primary);
        border-color: var(--primary);
    }

    .fs-title {
        flex: 1;
    }

    .fs-title h2 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .fs-title-subtitle {
        font-size: 13px;
        color: var(--text3);
        margin-top: 4px;
    }

    .fs-container {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 20px;
    }

    .fs-section {
        margin-bottom: 24px;
    }

    .fs-section:last-child {
        margin-bottom: 0;
    }

    .fs-section-title {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text3);
        margin-bottom: 12px;
        letter-spacing: 0.5px;
    }

    .fs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .fs-info-card {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 16px;
    }

    .fs-info-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text3);
        text-transform: uppercase;
        margin-bottom: 6px;
        letter-spacing: 0.3px;
    }

    .fs-info-value {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .fs-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
    }

    .fs-badge-success {
        background: rgba(16, 185, 129, .1);
        color: #059669;
    }

    .fs-badge-warning {
        background: rgba(245, 158, 11, .1);
        color: #b45309;
    }

    .fs-content-types {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .fs-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--primary-dim);
        color: var(--primary);
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid var(--primary);
    }

    .fs-notes {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 16px;
        font-size: 13px;
        line-height: 1.6;
        color: var(--text2);
    }

    .fs-empty {
        color: var(--text3);
        font-size: 12px;
        font-style: italic;
    }

    .fs-tasks-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .fs-task-item {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 14px;
    }

    .fs-task-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 6px;
    }

    .fs-task-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        font-size: 11px;
        color: var(--text3);
    }

    .fs-task-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 4px;
        background: var(--primary-dim);
        color: var(--primary);
        font-weight: 600;
    }
</style>

{{-- Header --}}
<div class="fs-header">
    <a href="{{ route('admin.clients.calendar', $selection->client) }}" class="fs-back-btn">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div class="fs-title">
        <h2>
            <i class="fa-solid fa-star"></i>
            Festival Selection Details
        </h2>
        <div class="fs-title-subtitle">
            View details about this festival selection
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="fs-container">
    {{-- Festival & Client Info --}}
    <div class="fs-section">
        <div class="fs-section-title">Overview</div>
        <div class="fs-grid">
            <div class="fs-info-card">
                <div class="fs-info-label">Festival</div>
                <div class="fs-info-value">
                    <span>{{ $selection->festival->emoji ?? '🎉' }}</span>
                    {{ $selection->festival->name }}
                </div>
            </div>

            <div class="fs-info-card">
                <div class="fs-info-label">Client</div>
                <div class="fs-info-value">
                    {{ $selection->client->name }}
                </div>
            </div>

            <div class="fs-info-card">
                <div class="fs-info-label">Festival Date</div>
                <div class="fs-info-value">
                    {{ $selection->festival->date->format('M d, Y') }}
                </div>
            </div>

            <div class="fs-info-card">
                <div class="fs-info-label">Selected By</div>
                <div class="fs-info-value">
                    {{ $selection->user->name }}
                </div>
            </div>

            <div class="fs-info-card">
                <div class="fs-info-label">Selected On</div>
                <div class="fs-info-value">
                    {{ $selection->created_at->format('M d, Y h:i A') }}
                </div>
            </div>

            <div class="fs-info-card">
                <div class="fs-info-label">Status</div>
                <div class="fs-info-value">
                    @if($selection->tasks()->exists())
                        <span class="fs-badge fs-badge-success">
                            <i class="fa-solid fa-check"></i> Assigned
                        </span>
                    @else
                        <span class="fs-badge fs-badge-warning">
                            <i class="fa-solid fa-hourglass"></i> Pending
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Festival Info --}}
    @if($selection->festival->description)
        <div class="fs-section">
            <div class="fs-section-title">Festival Description</div>
            <div class="fs-notes">
                {{ $selection->festival->description }}
            </div>
        </div>
    @endif

    {{-- Content Types --}}
    @if($selection->content_types && count($selection->content_types) > 0)
        <div class="fs-section">
            <div class="fs-section-title">Content Types</div>
            <div class="fs-content-types">
                @foreach($selection->content_types as $type)
                    <span class="fs-type-badge">
                        @if($type === 'post')
                            <i class="fa-solid fa-image"></i>
                        @elseif($type === 'story')
                            <i class="fa-solid fa-mobile-screen"></i>
                        @elseif($type === 'reel')
                            <i class="fa-solid fa-film"></i>
                        @elseif($type === 'video')
                            <i class="fa-solid fa-video"></i>
                        @elseif($type === 'carousel')
                            <i class="fa-solid fa-layer-group"></i>
                        @endif
                        {{ ucfirst($type) }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Notes --}}
    @if($selection->notes)
        <div class="fs-section">
            <div class="fs-section-title">Notes</div>
            <div class="fs-notes">
                {{ $selection->notes }}
            </div>
        </div>
    @endif

    {{-- Assigned Tasks --}}
    @if($selection->tasks()->exists())
        <div class="fs-section">
            <div class="fs-section-title">Assigned Tasks ({{ $selection->tasks()->count() }})</div>
            <div class="fs-tasks-list">
                @foreach($selection->tasks as $task)
                    <div class="fs-task-item">
                        <div class="fs-task-title">
                            {{ $task->title ?? 'Untitled Task' }}
                        </div>
                        <div class="fs-task-info">
                            @if($task->assignee)
                                <span>
                                    <i class="fa-solid fa-user"></i>
                                    {{ $task->assignee->name }}
                                </span>
                            @endif
                            @if($task->status)
                                <span class="fs-task-status">
                                    {{ ucfirst($task->status) }}
                                </span>
                            @endif
                            @if($task->deadline)
                                <span>
                                    <i class="fa-solid fa-calendar"></i>
                                    {{ $task->deadline->format('M d') }}
                                </span>
                            @endif
                            <a href="{{ route('admin.tasks.show', $task) }}" class="fs-task-status" style="background: var(--primary-dim); color: var(--primary); text-decoration: none; margin-left: auto;">
                                <i class="fa-solid fa-arrow-right"></i> View
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="fs-section">
            <div class="fs-section-title">Assigned Tasks</div>
            <div class="fs-notes">
                <i class="fa-solid fa-info-circle"></i>
                No tasks have been assigned for this festival selection yet.
            </div>
        </div>
    @endif
</div>

@endsection
