@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* ══════════════════════════════════════════════════════════════
           REFINED & PROPER STRATEGIST TASK DETAIL - TEST EDIT
           ══════════════════════════════════════════════════════════════ */
        .std-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding-bottom: 48px;
        }

        /* Topbar Header */
        .std-topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }
        .std-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text3);
            margin-bottom: 6px;
        }
        .std-breadcrumb a {
            color: var(--text2);
            text-decoration: none;
            transition: color .15s;
        }
        .std-breadcrumb a:hover {
            color: var(--primary);
        }
        .std-heading {
            font-size: 22px;
            font-weight: 800;
            color: var(--text);
            margin: 0 0 8px 0;
            line-height: 1.25;
            font-family: 'Plus Jakarta Sans', sans-serif;
            letter-spacing: -0.02em;
            word-break: break-word;
        }
        .std-header-tags {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .std-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1.2;
            background: var(--card2);
            border: 1px solid var(--border);
            color: var(--text2);
        }

        /* Stepper Progress Bar */
        .std-stepper-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 0;
            overflow-x: auto;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .std-step-item {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 150px;
            position: relative;
        }
        .std-step-bubble {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            background: var(--card2);
            border: 2px solid var(--border);
            color: var(--text3);
            flex-shrink: 0;
            transition: all .2s ease;
        }
        .std-step-item.is-done .std-step-bubble {
            background: #10B981;
            border-color: #10B981;
            color: #fff;
        }
        .std-step-item.is-current .std-step-bubble {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.18);
        }
        .std-step-text {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }
        .std-step-label {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
        }
        .std-step-sub {
            font-size: 10px;
            color: var(--text3);
            white-space: nowrap;
        }
        .std-step-arrow {
            flex-shrink: 0;
            margin: 0 12px;
            color: var(--border2);
            font-size: 11px;
        }

        /* 2-Column Main Layout */
        .std-main-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 20px;
            align-items: stretch;
        }
        .std-main-col {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        .std-main-col > .std-card:last-child {
            margin-bottom: 0;
        }
        .std-aside-col {
            display: flex;
            flex-direction: column;
            min-width: 0;
            height: 100%;
        }
        .std-aside-col > .std-card:last-child {
            margin-bottom: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Standard Cards */
        .std-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            transition: border-color .2s;
        }
        .std-card:hover {
            border-color: color-mix(in srgb, var(--primary) 25%, var(--border));
        }
        .std-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }
        .std-card-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text3);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        /* Media Assets Grid */
        .std-media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 14px;
        }
        .std-media-item {
            background: var(--card2);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .2s, border-color .2s, box-shadow .2s;
        }
        .std-media-item:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
            box-shadow: 0 8px 20px -4px rgba(0,0,0,0.12);
        }
        .std-thumb-wrap {
            position: relative;
            aspect-ratio: 16/10;
            background: #090d16;
            cursor: pointer;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .std-thumb-wrap img, .std-thumb-wrap video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .25s ease;
        }
        .std-media-item:hover .std-thumb-wrap img,
        .std-media-item:hover .std-thumb-wrap video {
            transform: scale(1.04);
        }
        .std-thumb-badge {
            position: absolute;
            top: 6px;
            left: 6px;
            background: rgba(15,23,42,0.85);
            backdrop-filter: blur(4px);
            color: #fff;
            font-size: 9.5px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            z-index: 2;
        }
        .std-thumb-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15,23,42,0.55);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            opacity: 0;
            transition: opacity .15s;
            z-index: 3;
        }
        .std-thumb-wrap:hover .std-thumb-overlay {
            opacity: 1;
        }
        .std-icon-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: transform .15s, background .15s;
        }
        .std-icon-btn-view {
            background: #fff;
            color: #0f172a;
        }
        .std-icon-btn-view:hover {
            transform: scale(1.1);
            color: var(--primary);
        }
        .std-icon-btn-dl {
            background: #10B981;
            color: #fff;
        }
        .std-icon-btn-dl:hover {
            background: #059669;
            transform: scale(1.1);
        }
        .std-media-meta {
            padding: 9px 12px;
            background: var(--card);
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .std-media-filename {
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }
        .std-dl-chip {
            font-size: 11px;
            font-weight: 700;
            color: #10B981;
            background: rgba(16,185,129,0.1);
            padding: 3px 8px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            transition: background .15s, color .15s;
        }
        .std-dl-chip:hover {
            background: #10B981;
            color: #fff;
        }

        /* Content Boxes */
        .std-box {
            background: var(--card2);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 14px 16px;
            font-size: 13px;
            line-height: 1.65;
            color: var(--text);
            white-space: pre-wrap;
            word-break: break-word;
        }

        /* Sidebar Info Table */
        .std-meta-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .std-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 12px;
            background: var(--card2);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 12.5px;
        }
        .std-meta-k {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--text3);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .std-meta-v {
            font-weight: 600;
            color: var(--text);
            text-align: right;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Comments Feed */
        .std-comment-stream {
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow-y: auto;
            flex: 1;
            min-height: 140px;
            padding-right: 4px;
            margin-bottom: 12px;
        }
        .std-comment-item {
            padding: 10px 12px;
            background: var(--card2);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 12px;
        }

        /* Action Link */
        .std-action-link {
            font-size: 11px;
            font-weight: 600;
            color: var(--primary);
            background: transparent;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 6px;
            border-radius: 4px;
            transition: background .15s;
        }
        .std-action-link:hover {
            background: color-mix(in srgb, var(--primary) 10%, transparent);
        }

        /* Cylindrical Hashtags Box & Pills */
        .std-hashtags-box {
            background: var(--card2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 16px;
            margin-top: 10px;
        }
        .std-hashtags-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 10px;
        }
        .std-hashtags-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text3);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .std-count-pill {
            font-size: 10px;
            font-weight: 800;
            background: color-mix(in srgb, var(--primary) 12%, transparent);
            color: var(--primary);
            padding: 1px 7px;
            border-radius: 9999px;
        }
        .std-hashtag-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .std-hashtag-pill {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            padding: 6px 14px;
            border-radius: 9999px; /* Pure cylindrical capsule */
            background: var(--card);
            border: 1.5px solid color-mix(in srgb, var(--primary) 22%, var(--border));
            color: var(--text);
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
        }
        .std-hashtag-pill:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px color-mix(in srgb, var(--primary) 30%, transparent);
        }
        .std-hashtag-pill:hover .std-hashtag-hash {
            color: #fff;
            opacity: 0.9;
        }
        .std-hashtag-hash {
            color: var(--primary);
            font-weight: 800;
            font-size: 12.5px;
            margin-right: 1px;
            transition: color 0.15s;
        }

        /* ─── Studio Revision History & Comparison Engine ─── */
        .std-rev-card {
            background: var(--card);
            border: 1.5px solid rgba(245, 158, 11, 0.35);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.06);
        }
        .std-rev-entry {
            position: relative;
            padding-left: 28px;
            margin-bottom: 18px;
            border-left: 2px solid rgba(245, 158, 11, 0.28);
        }
        .std-rev-entry:last-child {
            margin-bottom: 0;
            border-left-color: transparent;
        }
        .std-rev-dot {
            position: absolute;
            left: -7px;
            top: 2px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #F59E0B;
            border: 2px solid var(--card);
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.35);
        }
        .std-rev-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .std-rev-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #fff;
        }
        .std-rev-box {
            background: var(--card2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12.5px;
            color: var(--text);
            line-height: 1.55;
            white-space: pre-wrap;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        /* Version Switcher Tabs */
        .std-ver-tabs {
            display: inline-flex;
            align-items: center;
            background: var(--card2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 3px;
            gap: 3px;
        }
        .std-ver-tab {
            padding: 4px 11px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 6px;
            border: none;
            background: transparent;
            color: var(--text3);
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-family: inherit;
        }
        .std-ver-tab:hover {
            color: var(--text);
            background: rgba(255,255,255,0.06);
        }
        .std-ver-tab.active {
            background: var(--card);
            color: var(--primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid var(--border);
        }

        /* Side-by-Side Comparison Container */
        .std-comparison-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 14px;
        }
        .std-comp-col {
            background: var(--card2);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .std-comp-col.is-latest {
            border-color: rgba(16, 185, 129, 0.45);
            background: linear-gradient(180deg, rgba(16, 185, 129, 0.04) 0%, var(--card2) 100%);
        }
        .std-comp-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .std-comparison-container {
                grid-template-columns: 1fr;
            }
            .std-main-grid {
                grid-template-columns: 1fr;
            }
            .std-aside-col {
                height: auto;
            }
            .std-aside-col > .std-card:last-child {
                flex: none;
            }
            .std-comment-stream {
                flex: none;
                max-height: 280px;
            }
            .std-topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('content')
<div class="std-wrapper">

    @php
        $isUrgentTask = (bool) $task->is_urgent_task;
        $isTaskOwner = (int) $task->created_by === (int) Auth::id();
        $isDevTask = in_array($task->type, ['website', 'software', 'maintenance']);
        $taskMedia = $task->media;
        $deliverableMedia = $taskMedia->filter(fn($m) => $m->isDeliverable())->values();
        $referenceMedia = $taskMedia->filter(fn($m) => $m->isReference())->values();
        $hasMedia = $deliverableMedia->isNotEmpty();

        // ─── Revision History Trail Extraction ───
        $revisionAuditLogs = $task->relationLoaded('auditLogs')
            ? $task->auditLogs->where('action', 'revision_requested')->values()
            : $task->auditLogs()->where('action', 'revision_requested')->with('user')->get();

        $revisionEntries = collect();

        if ($revisionAuditLogs->isNotEmpty()) {
            foreach ($revisionAuditLogs as $log) {
                $newVals = is_array($log->new_values) ? $log->new_values : (json_decode($log->new_values, true) ?: []);
                $revNum = (int) ($newVals['revision_count'] ?? 1);
                $revRefs = $referenceMedia->filter(function($m) use ($revNum) {
                    return (int) $m->getRevisionCount() === $revNum;
                })->values();

                $revisionEntries->push([
                    'revision_number' => $revNum,
                    'requester_name'  => $log->user?->name ?? 'Strategist',
                    'requester_role'  => $log->user?->role ?? 'strategist',
                    'created_at'      => $log->created_at,
                    'reason'          => $newVals['reason'] ?? 'Revision Requested',
                    'note'            => $newVals['note'] ?? '',
                    'references'      => $revRefs,
                ]);
            }
        }

        // Fallback: If task has revision_count > 0 but no audit logs, extract from comments
        if ($revisionEntries->isEmpty() && (int) $task->revision_count > 0) {
            $revComments = $task->comments->filter(fn($c) => str_contains($c->body, 'Revision requested') || str_starts_with(trim($c->body), '🔄'))->values();
            foreach ($revComments as $idx => $comment) {
                $revNum = $revComments->count() - $idx;
                $reason = 'Revision Requested';
                $note = $comment->body;
                if (preg_match('/\[(.*?)\]:\s*(.*)/s', $comment->body, $matches)) {
                    $reason = $matches[1];
                    $note = $matches[2];
                } else {
                    $note = preg_replace('/^🔄\s*Revision requested:\s*/', '', $note);
                }

                $revRefs = $referenceMedia->filter(function($m) use ($revNum) {
                    return (int) $m->getRevisionCount() === $revNum;
                })->values();

                $revisionEntries->push([
                    'revision_number' => $revNum,
                    'requester_name'  => $comment->user?->name ?? 'Strategist',
                    'requester_role'  => $comment->user?->role ?? 'strategist',
                    'created_at'      => $comment->created_at,
                    'reason'          => $reason,
                    'note'            => $note,
                    'references'      => $revRefs,
                ]);
            }
        }

        // Sort revision entries newest first for display
        $revisionEntries = $revisionEntries->sortByDesc('revision_number')->values();

        // Deliverables Version Analysis
        $distinctVersions = $deliverableMedia->map(fn($m) => (int)$m->getRevisionCount())->unique()->sort()->values();
        $hasMultipleVersions = $distinctVersions->count() > 1;
        $maxDeliverableRev = $distinctVersions->max() ?? 0;
        $initialDeliverables = $deliverableMedia->filter(fn($m) => (int)$m->getRevisionCount() === 0)->values();
        $latestDeliverables = $deliverableMedia->filter(fn($m) => (int)$m->getRevisionCount() === $maxDeliverableRev)->values();

        $statusColors = [
            'todo' => '#F59E0B',
            'inprogress' => '#3B82F6',
            'review' => '#8B5CF6',
            'pending_approval' => '#EC4899',
            'client_review' => '#0D9488',
            'completed' => '#10B981',
            'published' => '#059669',
            'on_hold' => '#EF4444',
        ];
        $currColor = $statusColors[$task->status] ?? '#6366F1';

        // Workflow Step Computation
        $isTodo = $task->status === 'todo';
        $isInProgress = $task->status === 'inprogress';
        $isReview = $task->status === 'review';
        $isPendingApproval = $task->status === 'pending_approval';
        $isClientReview = $task->status === 'client_review';
        $isCompleted = in_array($task->status, ['completed', 'published']);

        $steps = [
            [
                'label' => 'Task Created',
                'sub' => $task->created_at ? $task->created_at->format('d M, h:i A') : 'Initiated',
                'done' => true,
                'current' => $isTodo,
                'icon' => '<i class="fa-solid fa-plus"></i>'
            ],
            [
                'label' => 'Designer Working',
                'sub' => $task->started_at ? $task->started_at->format('d M, h:i A') : ($isInProgress ? 'In Progress' : 'Pending Start'),
                'done' => !$isTodo,
                'current' => $isInProgress,
                'icon' => '<i class="fa-solid fa-paintbrush"></i>'
            ],
            [
                'label' => 'Design Submitted',
                'sub' => $task->submitted_at ? $task->submitted_at->format('d M, h:i A') : ($isReview ? 'Under Review' : 'Awaiting Output'),
                'done' => $isReview || $isPendingApproval || $isClientReview || $isCompleted,
                'current' => $isReview,
                'icon' => '<i class="fa-solid fa-cloud-arrow-up"></i>'
            ],
            [
                'label' => 'Internal Double-Check',
                'sub' => $isPendingApproval ? 'Admin Reviewing' : ($task->admin_approved_at ? 'Admin Approved' : ($isCompleted ? 'Approved' : 'Pending')),
                'done' => $isCompleted,
                'current' => $isPendingApproval,
                'icon' => '<i class="fa-solid fa-check-double"></i>'
            ],
        ];

        $steps[] = [
            'label' => 'Published / Complete',
            'sub' => $task->completed_at ? $task->completed_at->format('d M, h:i A') : 'Final Step',
            'done' => $isCompleted,
            'current' => $isCompleted,
            'icon' => '<i class="fa-solid fa-paper-plane"></i>'
        ];
    @endphp

    {{-- TOPBAR: BREADCRUMB & HEADER --}}
    <div class="std-topbar">
        <div>
            <div class="std-breadcrumb">
                <a href="{{ route('strategist.tracking') }}">Task Tracking</a>
                <span>›</span>
                <span>Task #{{ $task->id }}</span>
                @if($task->client)
                    <span>›</span>
                    <span style="color:var(--text2);font-weight:600">{{ $task->client->emoji ?? '' }} {{ $task->client->name }}</span>
                @endif
            </div>
            <h1 class="std-heading">{{ $task->title }}</h1>
            <div class="std-header-tags">
                <span class="std-pill" style="background:{{ $currColor }}14;border-color:{{ $currColor }}30;color:{{ $currColor }}">
                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $currColor }}"></span>
                    {{ $task->status_label }}
                </span>
                <span class="std-pill">
                    <i class="fa-solid fa-paperclip" style="font-size:10px;opacity:0.6"></i> {{ ucfirst($task->type) }}
                </span>
                @if($isUrgentTask)
                    <span class="std-pill" style="background:#EF444418;border-color:#EF444450;color:#EF4444;font-weight:800">
                        <i class="fa-solid fa-bolt" style="font-size:10px"></i> Urgent Task · Designer Initiated
                    </span>
                @elseif($task->priority === 'urgent')
                    <span class="std-pill" style="background:#EF444414;border-color:#EF444430;color:#EF4444">
                        <i class="fa-solid fa-bolt" style="font-size:10px"></i> Urgent Priority
                    </span>
                @elseif($task->priority === 'high')
                    <span class="std-pill" style="background:#F59E0B14;border-color:#F59E0B30;color:#D97706">
                        <i class="fa-solid fa-arrow-up" style="font-size:10px"></i> High Priority
                    </span>
                @endif
                @if($task->revision_count > 0)
                    <span class="std-pill" style="background:#F59E0B14;border-color:#F59E0B30;color:#D97706">
                        <i class="fa-solid fa-rotate-left" style="font-size:10px"></i> Rev #{{ $task->revision_count }}
                    </span>
                @endif
                @php $isClientApproved = $task->isClientApproved(); @endphp
                <span class="std-pill" id="std-header-client-pill-{{ $task->id }}" style="background:{{ $isClientApproved ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.1)' }};border-color:{{ $isClientApproved ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.25)' }};color:{{ $isClientApproved ? '#059669' : '#EF4444' }};font-weight:700">
                    <i class="fa-solid {{ $isClientApproved ? 'fa-check' : 'fa-xmark' }}" style="font-size:10px" id="std-header-client-icon-{{ $task->id }}"></i>
                    Client: <span id="std-header-client-status-{{ $task->id }}">{{ $isClientApproved ? 'Yes' : 'No' }}</span>
                </span>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;flex-wrap:wrap">
            <a href="{{ route('strategist.tasks.edit', $task) }}" class="btn-sec" style="font-size:12px;padding:7px 14px;border-radius:8px">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </a>
            <form method="POST" action="{{ route('strategist.tasks.destroy', $task) }}" style="margin:0" onsubmit="return confirm('Are you sure you want to delete this task: {{ addslashes($task->title) }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-sec" style="font-size:12px;padding:7px 14px;border-radius:8px;color:#EF4444;border-color:rgba(239,68,68,0.25);background:rgba(239,68,68,0.04);cursor:pointer">
                    <i class="fa-solid fa-trash-can"></i> Delete
                </button>
            </form>
            <a href="{{ route('strategist.tracking') }}" class="btn-sec" style="font-size:12px;padding:7px 14px;border-radius:8px">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @if($isUrgentTask)
        @php
            $urgentDelivered = in_array($task->status, ['completed', 'published'], true);
            $urgentShared = in_array($task->status, ['review', 'pending_approval'], true);
            $urgentActorName = $task->assignee?->name ?? $task->creator?->name ?? 'Designer';
            $urgentBannerStyle = $urgentDelivered
                ? 'background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);'
                : ($urgentShared
                    ? 'background:rgba(14,165,233,0.1);border:1px solid rgba(14,165,233,0.3);'
                    : 'background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);');
            $urgentBannerColor = $urgentDelivered ? '#10B981' : ($urgentShared ? '#0EA5E9' : '#EF4444');
            $urgentBannerIcon = $urgentDelivered ? 'fa-truck-fast' : ($urgentShared ? 'fa-eye' : 'fa-bolt');
            $urgentBannerTitle = $urgentDelivered
                ? 'Urgent Task Completed & Delivered'
                : ($urgentShared ? 'Urgent Work Submitted for Review' : 'Urgent Production In Progress');
            $urgentBannerSubtitle = $urgentDelivered
                ? ('Designer delivered this emergency task to client' . ($task->completed_at ? ' ' . $task->completed_at->diffForHumans() : '') . '.')
                : ($urgentShared
                    ? ('Designer completed the artwork and submitted for review. Please check deliverables and approve or request revision.')
                    : 'This emergency task was initiated by the designer to handle an urgent client request. Work is actively in progress.');
        @endphp
        <div style="{{ $urgentBannerStyle }}border-radius:12px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $urgentBannerColor }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;box-shadow:0 4px 12px {{ $urgentBannerColor }}40">
                    <i class="fas {{ $urgentBannerIcon }}"></i>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:800;color:{{ $urgentBannerColor }}">{{ $urgentBannerTitle }}</div>
                    <div style="font-size:12px;color:var(--text2);margin-top:2px">{{ $urgentBannerSubtitle }}</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                <div style="font-size:11.5px;color:var(--text3);text-align:right">
                    <div>Requested By: <strong style="color:var(--text)">{{ $task->urgent_requested_by ?: 'Client Emergency' }}</strong></div>
                    <div>Handled By: <strong style="color:var(--text)">{{ $urgentActorName }}</strong></div>
                </div>
                @if($task->status === 'review')
                    <form method="POST" action="{{ route('strategist.tasks.approve', $task) }}" style="margin:0">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="force" value="1">
                        <button type="submit" class="btn-pri" style="background:#059669;border-color:#059669;font-size:12px;padding:8px 16px;border-radius:8px;box-shadow:0 2px 8px rgba(5,150,105,0.3);color:#fff;cursor:pointer">
                            <i class="fa-solid fa-check-double"></i> Fast-Track Approve
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- WORKFLOW LIFECYCLE STEPPER --}}
    <div class="std-stepper-card">
        @foreach($steps as $i => $st)
            <div class="std-step-item {{ $st['done'] ? 'is-done' : '' }} {{ $st['current'] ? 'is-current' : '' }}">
                <div class="std-step-bubble">
                    @if($st['done'] && !$st['current'])
                        <i class="fa-solid fa-check"></i>
                    @else
                        {!! $st['icon'] !!}
                    @endif
                </div>
                <div class="std-step-text">
                    <span class="std-step-label">{{ $st['label'] }}</span>
                    <span class="std-step-sub">{{ $st['sub'] }}</span>
                </div>
            </div>
            @if($i < count($steps) - 1)
                <i class="fa-solid fa-chevron-right std-step-arrow"></i>
            @endif
        @endforeach
    </div>

    {{-- COPY REVISION ACTION BANNER --}}
    @if($task->revision_target === 'strategist' && $task->status === 'review')
        <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 18px;background:linear-gradient(135deg, rgba(139,92,246,0.1) 0%, rgba(239,68,68,0.08) 100%);border:1.5px solid rgba(139,92,246,0.35);border-radius:12px;margin-bottom:20px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:36px;height:36px;border-radius:50%;background:#8B5CF6;color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;box-shadow:0 3px 8px rgba(139,92,246,0.35)">
                    <i class="fa-solid fa-pen-nib"></i>
                </div>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:#5B21B6;display:flex;align-items:center;gap:8px">
                        <span>Action Required: Copy / Strategy Revision</span>
                        <span style="font-size:10px;font-weight:800;padding:2px 8px;border-radius:99px;background:#8B5CF6;color:#fff">TARGET: STRATEGIST</span>
                    </div>
                    <div style="font-size:12px;color:#6D28D9;margin-top:2px">
                        Admin requested changes to the caption, hashtags, or strategic messaging. Please adjust the copy below and re-submit for Admin approval.
                    </div>
                </div>
            </div>
            <button type="button" onclick="toggleCaptionEdit(true)" class="btn-primary" style="font-size:12px;padding:8px 16px;border-radius:8px;background:#8B5CF6;text-decoration:none;white-space:nowrap;border:none;cursor:pointer">
                <i class="fa-solid fa-pen"></i> Edit Copy Now
            </button>
        </div>
    @endif

    {{-- MAIN 2-COLUMN GRID --}}
    <div class="std-main-grid">

        {{-- LEFT COLUMN: PRIMARY WORKSPACE --}}
        <main class="std-main-col">

            {{-- 0. DEDICATED REVISION & CHANGE HISTORY TIMELINE --}}
            @if((int)$task->revision_count > 0 || $revisionEntries->isNotEmpty())
                <div class="std-rev-card">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid rgba(245,158,11,0.2)">
                        <div style="display:flex;align-items:center;gap:8px">
                            <i class="fa-solid fa-clock-rotate-left" style="color:#D97706;font-size:16px"></i>
                            <span style="font-size:14px;font-weight:800;color:var(--text)">Revision & Change History</span>
                            <span class="std-rev-badge">{{ $task->revision_count }} {{ Str::plural('Round', $task->revision_count) }}</span>
                        </div>
                        <div>
                            @if($task->status === 'inprogress')
                                <span class="std-pill" style="background:rgba(59,130,246,0.1);color:#2563EB;border-color:rgba(59,130,246,0.25);font-size:11px;font-weight:700">
                                    <span style="width:6px;height:6px;border-radius:50%;background:#3B82F6;display:inline-block"></span> Designer Revising Now
                                </span>
                            @elseif($task->status === 'review')
                                <span class="std-pill" style="background:rgba(16,185,129,0.1);color:#059669;border-color:rgba(16,185,129,0.25);font-size:11px;font-weight:700">
                                    <i class="fa-solid fa-check"></i> Revised Output Ready for Review
                                </span>
                            @endif
                        </div>
                    </div>

                    @forelse($revisionEntries as $entry)
                        <div class="std-rev-entry">
                            <span class="std-rev-dot"></span>
                            <div class="std-rev-header">
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                    <span style="font-size:12px;font-weight:800;color:#D97706">Revision #{{ $entry['revision_number'] }}</span>
                                    <span class="std-pill" style="background:rgba(245,158,11,0.14);border-color:rgba(245,158,11,0.3);color:#B45309;font-weight:700;font-size:11px">
                                        {{ $entry['reason'] }}
                                    </span>
                                </div>
                                <div style="font-size:11px;color:var(--text3)">
                                    Requested by <strong style="color:var(--text2)">{{ $entry['requester_name'] }}</strong>
                                    ({{ ucfirst($entry['requester_role']) }})
                                    @if($entry['created_at'])
                                        · {{ $entry['created_at']->diffForHumans() }}
                                    @endif
                                </div>
                            </div>

                            <div class="std-rev-box">
                                {!! nl2br(e($entry['note'])) !!}
                            </div>

                            @if($entry['references']->isNotEmpty())
                                <div style="margin-top:10px;padding:8px 12px;background:rgba(59,130,246,0.05);border:1px solid rgba(59,130,246,0.2);border-radius:8px">
                                    <div style="font-size:11px;font-weight:700;color:#2563EB;margin-bottom:6px;display:flex;align-items:center;gap:6px">
                                        <i class="fa-solid fa-paperclip"></i> Attached Reference / Markup Files ({{ $entry['references']->count() }}):
                                    </div>
                                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                                        @foreach($entry['references'] as $ref)
                                            @php
                                                $overallIndex = $taskMedia->search(fn($item) => $item->id === $ref->id);
                                                if ($overallIndex === false) $overallIndex = 0;
                                            @endphp
                                            <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 8px;background:var(--card);border:1px solid var(--border);border-radius:6px;cursor:pointer;font-size:11px" onclick="strategistOpenLightbox({{ $overallIndex }})" title="View Reference File">
                                                <i class="fa-solid {{ $ref->isImage() ? 'fa-image' : ($ref->isVideo() ? 'fa-video' : 'fa-file') }}" style="color:#2563EB"></i>
                                                <span style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text)">{{ $ref->name ?: $ref->file_name }}</span>
                                                <a href="{{ $ref->getUrl() }}" download onclick="event.stopPropagation()" style="color:var(--text3);margin-left:2px" title="Download"><i class="fa-solid fa-download"></i></a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div style="font-size:12px;color:var(--text3);padding:8px 0">
                            {{ $task->revision_count }} revision rounds recorded. Designer is working on requested updates.
                        </div>
                    @endforelse
                </div>
            @endif

            {{-- 1. SUBMITTED CREATIVE DELIVERABLES --}}
            <div class="std-card">
                <div class="std-card-header" style="flex-wrap:wrap;gap:12px">
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                        <div class="std-card-title" style="margin-bottom:0">
                            <i class="fa-solid fa-paintbrush" style="color:var(--primary)"></i>
                            <span>Submitted Design Deliverables</span>
                            <span style="font-size:10px;font-weight:800;background:rgba(79,109,240,0.12);color:var(--primary);padding:1px 6px;border-radius:99px">{{ $deliverableMedia->count() }}</span>
                        </div>

                        {{-- Version Switcher Tabs --}}
                        @if($distinctVersions->count() > 1)
                            <div class="std-ver-tabs" id="stdVersionTabs">
                                <button type="button" class="std-ver-tab active" data-version="all" onclick="filterDeliverableVersion('all')">
                                    All ({{ $deliverableMedia->count() }})
                                </button>
                                @foreach($distinctVersions as $ver)
                                    @php
                                        $verItems = $deliverableMedia->filter(fn($m) => (int)$m->getRevisionCount() === $ver);
                                    @endphp
                                    <button type="button" class="std-ver-tab" data-version="{{ $ver }}" onclick="filterDeliverableVersion({{ $ver }})">
                                        V{{ $ver + 1 }} {{ $ver === 0 ? '(Initial)' : ($ver === $maxDeliverableRev ? '(Latest)' : '') }}
                                        <span style="font-size:9.5px;opacity:0.75">({{ $verItems->count() }})</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div style="display:flex;align-items:center;gap:8px;margin-left:auto">
                        @if($hasMultipleVersions)
                            <button type="button" class="btn-sec" id="stdToggleCompareBtn" onclick="toggleDeliverableComparison()" style="font-size:11px;font-weight:700;padding:5px 12px;display:inline-flex;align-items:center;gap:6px;border-radius:6px;cursor:pointer">
                                <i class="fa-solid fa-columns" style="color:#8B5CF6"></i> <span>Compare Versions</span>
                            </button>
                        @endif

                        @if($deliverableMedia->count() > 1)
                            <button type="button" class="btn-sec" onclick="strategistDownloadAllMedia()" style="font-size:11px;font-weight:600;padding:5px 12px;display:inline-flex;align-items:center;gap:6px;border-radius:6px;cursor:pointer">
                                <i class="fa-solid fa-cloud-arrow-down" style="color:#10B981"></i> Download All
                            </button>
                        @endif
                    </div>
                </div>

                @if($deliverableMedia->isNotEmpty())
                    {{-- Standard Media Grid --}}
                    <div class="std-media-grid" id="stdNormalMediaGrid">
                        @foreach($deliverableMedia as $index => $m)
                            @php
                                $isVid = $m->isVideo();
                                $isPdf = $m->isPdf();
                                $mUrl = $m->getUrl();
                                $mName = $m->name ?: $m->file_name ?: ('Deliverable #' . ($index + 1));
                                $overallIndex = $taskMedia->search(fn($item) => $item->id === $m->id);
                                if ($overallIndex === false) $overallIndex = $index;
                            @endphp
                            <div class="std-media-item std-deliverable-item" data-revision-count="{{ (int)$m->getRevisionCount() }}" style="border:1.5px solid {{ $m->getRevisionCount() > 0 ? '#F59E0B' : 'var(--border)' }};border-radius:10px">
                                <div class="std-thumb-wrap" onclick="strategistOpenLightbox({{ $overallIndex }})" title="Click to view full preview">
                                    @if($isVid)
                                        <video src="{{ $mUrl }}" muted preload="metadata"></video>
                                        <div style="position:absolute;font-size:22px;color:#fff;pointer-events:none;background:rgba(0,0,0,0.45);width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(2px)">
                                            <i class="fa-solid fa-play" style="margin-left:2px"></i>
                                        </div>
                                        <span class="std-thumb-badge"><i class="fa-solid fa-video"></i> Video</span>
                                    @elseif($isPdf)
                                        <div style="height:100%;width:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--card2)">
                                            <i class="fa-solid fa-file-pdf" style="font-size:32px;color:#EF4444"></i>
                                            <span style="font-size:9.5px;font-weight:700;color:var(--text3);margin-top:4px">PDF Document</span>
                                        </div>
                                        <span class="std-thumb-badge" style="background:rgba(239,68,68,0.85)"><i class="fa-solid fa-file-pdf"></i> PDF</span>
                                    @elseif($m->isImage())
                                        <img src="{{ $mUrl }}" alt="{{ $mName }}" loading="lazy">
                                        <span class="std-thumb-badge"><i class="fa-solid fa-image"></i> Image</span>
                                    @else
                                        <div style="height:100%;width:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--card2)">
                                            <i class="fa-solid fa-file" style="font-size:32px;color:var(--text3)"></i>
                                        </div>
                                        <span class="std-thumb-badge"><i class="fa-solid fa-paperclip"></i> File</span>
                                    @endif

                                    {{-- Deliverable Version Badge --}}
                                    <div style="position:absolute;bottom:6px;left:6px;right:6px;background:{{ $m->getRevisionCount() > 0 ? 'rgba(217,119,6,0.92)' : 'rgba(16,185,129,0.92)' }};color:#fff;font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;z-index:2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        {{ $m->getVersionBadgeLabel() }}
                                    </div>

                                    <div class="std-thumb-overlay">
                                        <button type="button" class="std-icon-btn std-icon-btn-view" onclick="event.stopPropagation(); strategistOpenLightbox({{ $overallIndex }})" title="Review Video/File">
                                            <i class="fa-solid fa-expand"></i>
                                        </button>
                                        <a href="{{ $mUrl }}" download="{{ $mName }}" class="std-icon-btn std-icon-btn-dl" title="Download {{ $mName }}" onclick="event.stopPropagation()">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="std-media-meta">
                                    <div style="min-width:0;flex:1">
                                        <span class="std-media-filename" title="{{ $mName }}">{{ $mName }}</span>
                                        <div style="font-size:9.5px;color:var(--text3)">
                                            @if($m->getUploaderName())
                                                By {{ $m->getUploaderName() }} · 
                                            @endif
                                            {{ $m->getFormattedSize() }}
                                        </div>
                                    </div>
                                    <a href="{{ $mUrl }}" download="{{ $mName }}" class="std-dl-chip" title="Download" onclick="event.stopPropagation()">
                                        <i class="fa-solid fa-arrow-down-to-line"></i> Download
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Side-by-Side Comparison Container --}}
                    @if($hasMultipleVersions)
                        <div id="stdComparisonView" style="display:none;margin-top:10px">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 14px;background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.25);border-radius:8px;margin-bottom:14px;font-size:12px">
                                <div style="display:flex;align-items:center;gap:8px">
                                    <i class="fa-solid fa-columns" style="color:#8B5CF6"></i>
                                    <span style="color:var(--text)"><strong>Side-by-Side Review Mode:</strong> Compare <strong>Version 1 (Initial Draft)</strong> directly alongside <strong>Version {{ $maxDeliverableRev + 1 }} (Latest Revision)</strong>.</span>
                                </div>
                                <button type="button" class="btn-sec" onclick="toggleDeliverableComparison()" style="font-size:11px;padding:4px 10px;border-radius:6px;cursor:pointer">Exit Comparison</button>
                            </div>

                            <div class="std-comparison-container">
                                {{-- Column 1: Initial Draft --}}
                                <div class="std-comp-col">
                                    <div class="std-comp-header">
                                        <div style="font-size:12px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:6px">
                                            <span style="width:8px;height:8px;border-radius:50%;background:#64748B"></span>
                                            Version 1 (Initial Draft)
                                        </div>
                                        <span class="std-count-pill" style="background:rgba(100,116,139,0.15);color:#475569">{{ $initialDeliverables->count() }} file(s)</span>
                                    </div>

                                    @if($initialDeliverables->isNotEmpty())
                                        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(170px, 1fr));gap:12px">
                                            @foreach($initialDeliverables as $index => $m)
                                                @php
                                                    $isVid = $m->isVideo();
                                                    $isPdf = $m->isPdf();
                                                    $mUrl = $m->getUrl();
                                                    $mName = $m->name ?: $m->file_name ?: ('Deliverable #' . ($index + 1));
                                                    $overallIndex = $taskMedia->search(fn($item) => $item->id === $m->id);
                                                    if ($overallIndex === false) $overallIndex = $index;
                                                @endphp
                                                <div class="std-media-item" style="border:1.5px solid var(--border);border-radius:10px">
                                                    <div class="std-thumb-wrap" onclick="strategistOpenLightbox({{ $overallIndex }})" title="Click to view full preview">
                                                        @if($isVid)
                                                            <video src="{{ $mUrl }}" muted preload="metadata"></video>
                                                            <span class="std-thumb-badge"><i class="fa-solid fa-video"></i> Video</span>
                                                        @elseif($isPdf)
                                                            <div style="height:100%;width:100%;display:flex;align-items:center;justify-content:center;background:var(--card2)">
                                                                <i class="fa-solid fa-file-pdf" style="font-size:32px;color:#EF4444"></i>
                                                            </div>
                                                            <span class="std-thumb-badge" style="background:rgba(239,68,68,0.85)">PDF</span>
                                                        @elseif($m->isImage())
                                                            <img src="{{ $mUrl }}" alt="{{ $mName }}" loading="lazy">
                                                            <span class="std-thumb-badge"><i class="fa-solid fa-image"></i> Image</span>
                                                        @else
                                                            <div style="height:100%;width:100%;display:flex;align-items:center;justify-content:center;background:var(--card2)">
                                                                <i class="fa-solid fa-file" style="font-size:32px;color:var(--text3)"></i>
                                                            </div>
                                                        @endif
                                                        <div class="std-thumb-overlay">
                                                            <button type="button" class="std-icon-btn std-icon-btn-view" onclick="event.stopPropagation(); strategistOpenLightbox({{ $overallIndex }})" title="Review File">
                                                                <i class="fa-solid fa-expand"></i>
                                                            </button>
                                                            <a href="{{ $mUrl }}" download="{{ $mName }}" class="std-icon-btn std-icon-btn-dl" title="Download" onclick="event.stopPropagation()">
                                                                <i class="fa-solid fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="std-media-meta">
                                                        <div style="min-width:0;flex:1">
                                                            <span class="std-media-filename" title="{{ $mName }}">{{ $mName }}</span>
                                                            <div style="font-size:9.5px;color:var(--text3)">{{ $m->getFormattedSize() }}</div>
                                                        </div>
                                                        <a href="{{ $mUrl }}" download="{{ $mName }}" class="std-dl-chip" title="Download" onclick="event.stopPropagation()">
                                                            <i class="fa-solid fa-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="font-size:12px;color:var(--text3);text-align:center;padding:24px 0">No initial draft files found.</div>
                                    @endif
                                </div>

                                {{-- Column 2: Latest Revision --}}
                                <div class="std-comp-col is-latest">
                                    <div class="std-comp-header">
                                        <div style="font-size:12px;font-weight:700;color:#059669;display:flex;align-items:center;gap:6px">
                                            <span style="width:8px;height:8px;border-radius:50%;background:#10B981"></span>
                                            Version {{ $maxDeliverableRev + 1 }} (Latest Revised)
                                        </div>
                                        <span class="std-count-pill" style="background:rgba(16,185,129,0.15);color:#059669">{{ $latestDeliverables->count() }} file(s)</span>
                                    </div>

                                    @if($latestDeliverables->isNotEmpty())
                                        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(170px, 1fr));gap:12px">
                                            @foreach($latestDeliverables as $index => $m)
                                                @php
                                                    $isVid = $m->isVideo();
                                                    $isPdf = $m->isPdf();
                                                    $mUrl = $m->getUrl();
                                                    $mName = $m->name ?: $m->file_name ?: ('Deliverable #' . ($index + 1));
                                                    $overallIndex = $taskMedia->search(fn($item) => $item->id === $m->id);
                                                    if ($overallIndex === false) $overallIndex = $index;
                                                @endphp
                                                <div class="std-media-item" style="border:1.5px solid #10B981;border-radius:10px">
                                                    <div class="std-thumb-wrap" onclick="strategistOpenLightbox({{ $overallIndex }})" title="Click to view full preview">
                                                        @if($isVid)
                                                            <video src="{{ $mUrl }}" muted preload="metadata"></video>
                                                            <span class="std-thumb-badge"><i class="fa-solid fa-video"></i> Video</span>
                                                        @elseif($isPdf)
                                                            <div style="height:100%;width:100%;display:flex;align-items:center;justify-content:center;background:var(--card2)">
                                                                <i class="fa-solid fa-file-pdf" style="font-size:32px;color:#EF4444"></i>
                                                            </div>
                                                            <span class="std-thumb-badge" style="background:rgba(239,68,68,0.85)">PDF</span>
                                                        @elseif($m->isImage())
                                                            <img src="{{ $mUrl }}" alt="{{ $mName }}" loading="lazy">
                                                            <span class="std-thumb-badge"><i class="fa-solid fa-image"></i> Image</span>
                                                        @else
                                                            <div style="height:100%;width:100%;display:flex;align-items:center;justify-content:center;background:var(--card2)">
                                                                <i class="fa-solid fa-file" style="font-size:32px;color:var(--text3)"></i>
                                                            </div>
                                                        @endif
                                                        <div class="std-thumb-overlay">
                                                            <button type="button" class="std-icon-btn std-icon-btn-view" onclick="event.stopPropagation(); strategistOpenLightbox({{ $overallIndex }})" title="Review File">
                                                                <i class="fa-solid fa-expand"></i>
                                                            </button>
                                                            <a href="{{ $mUrl }}" download="{{ $mName }}" class="std-icon-btn std-icon-btn-dl" title="Download" onclick="event.stopPropagation()">
                                                                <i class="fa-solid fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <div class="std-media-meta">
                                                        <div style="min-width:0;flex:1">
                                                            <span class="std-media-filename" title="{{ $mName }}">{{ $mName }}</span>
                                                            <div style="font-size:9.5px;color:var(--text3)">{{ $m->getFormattedSize() }}</div>
                                                        </div>
                                                        <a href="{{ $mUrl }}" download="{{ $mName }}" class="std-dl-chip" title="Download" onclick="event.stopPropagation()">
                                                            <i class="fa-solid fa-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="font-size:12px;color:var(--text3);text-align:center;padding:24px 0">No latest revision files found.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div style="text-align:center;padding:36px 16px;color:var(--text3);background:var(--card2);border-radius:8px;border:1px dashed var(--border)">
                        <i class="fa-regular fa-image" style="font-size:32px;opacity:0.4;margin-bottom:8px"></i>
                        <div style="font-size:13px;font-weight:600;color:var(--text2)">No design deliverables submitted yet</div>
                        <div style="font-size:11.5px;margin-top:2px">Completed designs uploaded by the designer will appear here for review.</div>
                    </div>
                @endif
            </div>

            {{-- 2. CONTENT BRIEF --}}
            <div class="std-card">
                <div class="std-card-header">
                    <div class="std-card-title">
                        <i class="fa-regular fa-file-lines" style="color:var(--primary)"></i>
                        <span>Content Brief</span>
                    </div>
                    @if($task->brief)
                        <button type="button" class="std-action-link" onclick="navigator.clipboard.writeText(`{{ addslashes($task->brief) }}`); if(typeof ajax!=='undefined') ajax.showSuccess('Brief copied!');">
                            <i class="fa-regular fa-copy"></i> Copy Brief
                        </button>
                    @endif
                </div>
                <div class="std-box">
                    {{ $task->brief ?: 'No content brief provided for this task.' }}
                </div>
            </div>

            {{-- 3. MARKETING CAPTION & HASHTAGS --}}
            @if(!$isDevTask)
                <div class="std-card" id="marketingCaptionCard">
                    <div class="std-card-header">
                        <div class="std-card-title">
                            <i class="fa-solid fa-pen-nib" style="color:var(--primary)"></i>
                            <span>Marketing Caption & Hashtags</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px">
                            @if(!empty($task->caption))
                                <button type="button" class="std-action-link" onclick="navigator.clipboard.writeText(`{{ addslashes($task->caption) }}`); if(typeof ajax!=='undefined') ajax.showSuccess('Caption copied!');">
                                    <i class="fa-regular fa-copy"></i> Copy Caption
                                </button>
                            @endif
                            <button type="button" class="std-action-link" id="toggleCaptionEditBtn" onclick="toggleCaptionEdit()" style="font-weight:700">
                                <i class="fa-solid fa-pen-to-square"></i> <span id="captionEditBtnText">{{ (empty($task->caption) && empty($task->hashtags)) ? 'Add Caption' : 'Edit' }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- VIEW MODE --}}
                    <div id="captionViewSection" style="{{ (empty($task->caption) && empty($task->hashtags)) ? 'display:none;' : 'display:flex;flex-direction:column;gap:14px;' }}">
                        <div>
                            <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:6px">Caption</div>
                            <div class="std-box" style="white-space:pre-wrap;line-height:1.6">{{ $task->caption ?: 'No caption provided.' }}</div>
                        </div>

                        {{-- Proper Cylindrical Hashtags Box --}}
                        <div class="std-hashtags-box">
                            @php
                                $rawTags = is_array($task->hashtags) ? $task->hashtags : array_filter(explode(' ', (string)$task->hashtags));
                                $tags = array_values(array_filter(array_map('trim', $rawTags)));
                            @endphp
                            <div class="std-hashtags-head">
                                <div class="std-hashtags-title">
                                    <i class="fa-solid fa-hashtag" style="color:var(--primary)"></i>
                                    <span>Hashtags</span>
                                    @if(count($tags) > 0)
                                        <span class="std-count-pill">{{ count($tags) }}</span>
                                    @endif
                                </div>
                                @if(count($tags) > 0)
                                    <button type="button" class="std-action-link" onclick="navigator.clipboard.writeText(`{{ addslashes(implode(' ', $tags)) }}`); if(typeof ajax!=='undefined') ajax.showSuccess('All hashtags copied!');">
                                        <i class="fa-regular fa-copy"></i> Copy All
                                    </button>
                                @endif
                            </div>

                            @if(count($tags) > 0)
                                <div class="std-hashtag-cloud">
                                    @foreach($tags as $t)
                                        @php
                                            $cleanTag = ltrim($t, '#');
                                        @endphp
                                        <span class="std-hashtag-pill" onclick="navigator.clipboard.writeText('#{{ addslashes($cleanTag) }}'); if(typeof ajax!=='undefined') ajax.showSuccess('Copied #{{ addslashes($cleanTag) }}');" title="Click to copy #{{ $cleanTag }}">
                                            <span class="std-hashtag-hash">#</span>{{ $cleanTag }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div style="font-size:12px;color:var(--text3);font-style:italic">No hashtags provided.</div>
                            @endif
                        </div>
                    </div>

                    {{-- EDIT MODE FORM (Accessible by ANY strategist) --}}
                    <div id="captionEditSection" style="{{ (empty($task->caption) && empty($task->hashtags)) ? 'display:block;' : 'display:none;' }}">
                        <form id="captionHashtagsForm" method="POST" action="{{ route('strategist.tasks.updateCaptionHashtags', $task) }}" style="display:flex;flex-direction:column;gap:12px">
                            @csrf
                            <div>
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                                    <label for="captionInput" style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;display:block">Publication Caption</label>
                                    <span id="captionCharCount" style="font-size:11px;color:var(--text3)">{{ strlen((string)$task->caption) }} chars</span>
                                </div>
                                <textarea id="captionInput" name="caption" rows="4" placeholder="Write marketing caption for publication..." oninput="document.getElementById('captionCharCount').textContent = this.value.length + ' chars'" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card2);font-size:12.5px;color:var(--text);font-family:inherit;resize:vertical;box-sizing:border-box">{{ $task->caption }}</textarea>
                            </div>
                            <div>
                                <label for="hashtagsInput" style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:4px;display:block">Hashtags (space-separated)</label>
                                <input type="text" id="hashtagsInput" name="hashtags" value="{{ $task->hashtags }}" placeholder="#brand #marketing #campaign" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card2);font-size:12.5px;color:var(--text);box-sizing:border-box" oninput="renderHashtagsLivePreview(this.value)" />
                            </div>

                            {{-- Live Cylindrical Preview in edit mode --}}
                            <div class="std-hashtags-box">
                                <div class="std-hashtags-head">
                                    <div class="std-hashtags-title">
                                        <i class="fa-solid fa-eye" style="color:var(--primary)"></i>
                                        <span>Hashtags Live Preview</span>
                                    </div>
                                </div>
                                <div id="hashtagsLivePreview" class="std-hashtag-cloud">
                                    @php
                                        $rawTags = is_array($task->hashtags) ? $task->hashtags : array_filter(explode(' ', (string)$task->hashtags));
                                        $tags = array_values(array_filter(array_map('trim', $rawTags)));
                                    @endphp
                                    @forelse($tags as $t)
                                        @php $cleanTag = ltrim($t, '#'); @endphp
                                        <span class="std-hashtag-pill">
                                            <span class="std-hashtag-hash">#</span>{{ $cleanTag }}
                                        </span>
                                    @empty
                                        <div style="font-size:12px;color:var(--text3);font-style:italic">Type hashtags above to preview cylindrical tags.</div>
                                    @endforelse
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:4px">
                                <button type="submit" class="btn-primary" style="padding:8px 18px;font-size:12px;border-radius:7px">
                                    <i class="fa-solid fa-floppy-disk"></i> Save Caption & Hashtags
                                </button>
                                <button type="button" class="btn-sec" onclick="clearCaptionAndHashtags()" style="padding:8px 16px;font-size:12px;border-radius:7px;color:#EF4444;border-color:rgba(239,68,68,0.25);background:rgba(239,68,68,0.04)">
                                    <i class="fa-solid fa-trash-can"></i> Clear All
                                </button>
                                @if(!empty($task->caption) || !empty($task->hashtags))
                                    <button type="button" class="btn-sec" onclick="toggleCaptionEdit(false)" style="padding:8px 16px;font-size:12px;border-radius:7px">
                                        <i class="fa-solid fa-xmark"></i> Cancel
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- 4. PUBLICATION CHANNELS & REFERENCES --}}
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

                $refLinks = is_array($task->reference_links) ? $task->reference_links : (is_string($task->reference_links) ? json_decode($task->reference_links, true) : []);
            @endphp

            @if($selectedSocialLinks->isNotEmpty() || !empty($refLinks))
                <div class="std-card">
                    <div class="std-card-header">
                        <div class="std-card-title">
                            <i class="fa-solid fa-share-nodes" style="color:var(--primary)"></i>
                            <span>Connected Channels & Resources</span>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:14px">
                        @if($selectedSocialLinks->isNotEmpty())
                            <div>
                                <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:6px">Social Media Accounts</div>
                                <div style="display:flex;flex-wrap:wrap;gap:8px">
                                    @foreach($selectedSocialLinks as $link)
                                        @php
                                            $linkInfo = $link->getPlatformIcon();
                                            $accountLabel = $link->label ?: str_replace(['https://', 'http://', 'www.'], '', $link->url);
                                        @endphp
                                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:7px;padding:6px 12px;background:var(--card2);border:1px solid {{ $linkInfo['color'] }}30;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;color:var(--text);transition:border-color .15s">
                                            <i class="fa-brands {{ $linkInfo['icon'] }}" style="color:{{ $linkInfo['color'] }}"></i>
                                            <span>{{ $accountLabel }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px;color:var(--text3)"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($refLinks) && is_array($refLinks))
                            <div>
                                <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;margin-bottom:6px">Reference Links</div>
                                <div style="display:flex;flex-direction:column;gap:6px">
                                    @foreach($refLinks as $rLink)
                                        @if(trim((string)$rLink))
                                            <a href="{{ $rLink }}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;background:var(--card2);border:1px solid var(--border);border-radius:8px;text-decoration:none;font-size:12px;color:var(--primary);word-break:break-all">
                                                <i class="fa-solid fa-external-link-alt" style="font-size:10.5px;flex-shrink:0"></i>
                                                <span>{{ $rLink }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </main>

        {{-- RIGHT COLUMN: STICKY SIDEBAR INSPECTOR --}}
        <aside class="std-aside-col">

            {{-- 1. WORKFLOW ACTIONS --}}
            <div class="std-card">
                <div class="std-card-header">
                    <div class="std-card-title">
                        <i class="fa-solid fa-bolt" style="color:var(--primary)"></i>
                        <span>Workflow Actions</span>
                    </div>
                </div>

                @if($task->status === 'review')
                    @php
                        $isDeveloperTask = in_array($task->type, ['website', 'software', 'maintenance']);
                        $hasCaptionHashtags = !empty(trim((string)$task->caption)) && !empty(trim((string)$task->hashtags));
                        $canApprove = $isDeveloperTask || $hasCaptionHashtags;
                    @endphp

                    @if($canApprove)
                        <div style="display:flex;flex-direction:column;gap:8px">
                            <form method="POST" action="{{ route('strategist.tasks.approve', $task) }}" style="margin:0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-primary" style="width:100%;padding:11px 16px;font-size:13px;font-weight:700;background:linear-gradient(135deg, #10B981 0%, #059669 100%);border:none;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:7px;box-shadow:0 4px 12px rgba(16,185,129,0.25)">
                                    <i class="fa-solid fa-check-circle"></i> Approve & Send to Admin
                                </button>
                            </form>
                            <button type="button" class="btn-sec" onclick="document.getElementById('revisionModal').style.display='flex'" style="width:100%;padding:10px 16px;font-size:12.5px;font-weight:600;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;gap:6px;color:#D97706;border-color:rgba(245,158,11,0.35);background:rgba(245,158,11,0.04)">
                                <i class="fa-solid fa-rotate-left"></i> Request Revision
                            </button>
                        </div>
                    @else
                        <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:8px;padding:10px 12px;margin-bottom:10px;display:flex;align-items:center;gap:8px">
                            <i class="fa-solid fa-triangle-exclamation" style="color:#D97706;font-size:14px;flex-shrink:0"></i>
                            <div style="font-size:11px;color:#92400e;line-height:1.3">
                                <strong>Notice:</strong> Caption & hashtags not yet added. You can still approve or request revision.
                            </div>
                        </div>
                        <div style="display:grid;gap:8px">
                            <form method="POST" action="{{ route('strategist.tasks.approve', $task) }}" style="margin:0" onsubmit="return confirmStrategistApproveSwal(event, this)">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="force" value="1">
                                <button type="submit" class="btn-primary" style="width:100%;padding:11px 16px;font-size:13px;font-weight:700;background:linear-gradient(135deg, #10B981 0%, #059669 100%);border:none;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:7px;box-shadow:0 4px 12px rgba(16,185,129,0.25)">
                                    <i class="fa-solid fa-check-circle"></i> Approve & Send to Admin
                                </button>
                            </form>
                            <button type="button" class="btn-sec" onclick="document.getElementById('revisionModal').style.display='flex'" style="width:100%;padding:10px 16px;font-size:12.5px;font-weight:600;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;gap:6px;color:#D97706;border-color:rgba(245,158,11,0.35);background:rgba(245,158,11,0.04)">
                                <i class="fa-solid fa-rotate-left"></i> Request Revision
                            </button>
                        </div>
                    @endif
                @elseif($task->status === 'pending_approval')
                    <div style="background:rgba(168,85,247,0.08);border:1px solid rgba(168,85,247,0.25);border-radius:8px;padding:14px;text-align:center">
                        <i class="fa-solid fa-hourglass-half" style="color:#A855F7;font-size:16px;margin-bottom:4px"></i>
                        <div style="font-size:12.5px;font-weight:700;color:#A855F7">Awaiting Admin Approval</div>
                        <div style="font-size:11px;color:var(--text3);margin-top:2px">Strategist approved. Task is queued for final publishing by admin.</div>
                    </div>
                @elseif($task->status === 'completed' || $task->status === 'published')
                    <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);border-radius:8px;padding:14px;text-align:center">
                        <i class="fa-solid fa-circle-check" style="color:#10B981;font-size:16px;margin-bottom:4px"></i>
                        <div style="font-size:12.5px;font-weight:700;color:#10B981">Task Completed</div>
                        @if($task->completed_at)
                            <div style="font-size:11px;color:var(--text3);margin-top:2px">{{ $task->completed_at->format('d M Y, h:i A') }}</div>
                        @endif
                    </div>
                @else
                    <div style="background:var(--card2);border:1px solid var(--border);border-radius:8px;padding:12px;text-align:center">
                        <i class="fa-solid fa-person-digging" style="color:var(--primary);font-size:16px;margin-bottom:4px"></i>
                        <div style="font-size:12px;font-weight:700;color:var(--text)">In Progress by {{ $task->assignee?->name ?? 'Designer' }}</div>
                        <div style="font-size:11px;color:var(--text3);margin-top:2px">Started {{ $task->started_at?->diffForHumans() ?? 'recently' }}</div>
                    </div>
                @endif

                @if(!in_array($task->status, ['completed', 'published']))
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:10px">
                        @if($task->assigned_to)
                            <form method="POST" action="{{ route('strategist.tasks.follow-up', $task) }}" style="margin:0">
                                @csrf
                                <button type="submit" class="btn-sec" style="width:100%;font-size:11.5px;padding:7px 10px;justify-content:center">
                                    <i class="fa-solid fa-bell"></i> Follow-Up
                                </button>
                            </form>
                        @endif
                        <button type="button" class="btn-sec" onclick="document.getElementById('onHoldModal').style.display='flex'" style="width:100%;font-size:11.5px;padding:7px 10px;justify-content:center;color:#EF4444;border-color:rgba(239,68,68,0.25)">
                            <i class="fa-solid fa-pause"></i> On Hold
                        </button>
                    </div>
                @endif
            </div>

            {{-- 2. TASK PROPERTIES & METADATA --}}
            <div class="std-card">
                <div class="std-card-header">
                    <div class="std-card-title">
                        <i class="fa-solid fa-list-check" style="color:var(--primary)"></i>
                        <span>Task Details</span>
                    </div>
                </div>

                <div class="std-meta-list">
                    <div class="std-meta-row">
                        <span class="std-meta-k"><i class="fa-regular fa-building"></i> Client</span>
                        <span class="std-meta-v" title="{{ $task->client?->name ?? 'None' }}">
                            {{ $task->client?->emoji ? $task->client->emoji . ' ' : '' }}{{ $task->client?->name ?? 'None' }}
                        </span>
                    </div>
                    <div class="std-meta-row">
                        <span class="std-meta-k"><i class="fa-regular fa-user"></i> Assigned Designer</span>
                        <span class="std-meta-v" style="display:flex;align-items:center;gap:6px">
                            @if($task->assignee)
                                <span style="width:18px;height:18px;border-radius:50%;background:{{ $task->assignee->avatar_color ?? 'var(--primary)' }};color:#fff;font-size:8.5px;display:inline-flex;align-items:center;justify-content:center;font-weight:700">
                                    {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                </span>
                                {{ $task->assignee->name }}
                            @else
                                <span style="color:var(--text3)">Unassigned</span>
                            @endif
                        </span>
                    </div>
                    <div class="std-meta-row">
                        <span class="std-meta-k"><i class="fa-regular fa-calendar-check"></i> Due Date</span>
                        <span class="std-meta-v" style="color:{{ $task->isOverdue() ? '#EF4444' : 'var(--text)' }}">
                            {{ $task->deadline ? $task->deadline->format('d M Y') : ($task->dev_deadline ? $task->dev_deadline->format('d M Y') : 'No deadline') }}
                        </span>
                    </div>
                    @if($task->post_date)
                        <div class="std-meta-row">
                            <span class="std-meta-k"><i class="fa-regular fa-paper-plane"></i> Scheduled Post</span>
                            <span class="std-meta-v">{{ $task->post_date->format('d M Y') }}</span>
                        </div>
                    @endif
                    <div class="std-meta-row" style="align-items:flex-start">
                        <span class="std-meta-k" style="margin-top:2px"><i class="fa-solid fa-user-check"></i> Client Approved</span>
                        <div style="text-align:right">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                                <span id="std-meta-client-badge-{{ $task->id }}" style="padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:4px;background:{{ $isClientApproved ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.1)' }};color:{{ $isClientApproved ? '#059669' : '#EF4444' }};border:1px solid {{ $isClientApproved ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.25)' }}">
                                    <i class="fa-solid {{ $isClientApproved ? 'fa-check' : 'fa-xmark' }}"></i>
                                    {{ $isClientApproved ? 'Yes' : 'No' }}
                                </span>
                                <button type="button"
                                        id="std-meta-client-btn-{{ $task->id }}"
                                        onclick="toggleClientApproval({{ $task->id }}, this)"
                                        style="font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:6px;cursor:pointer;border:1px solid var(--border);background:var(--card2);color:var(--text);transition:all .18s">
                                    {{ $isClientApproved ? 'Mark No' : 'Mark Yes' }}
                                </button>
                            </div>
                            <div id="std-meta-client-note-{{ $task->id }}" style="font-size:10px;color:var(--text3);margin-top:3px">
                                @if($isClientApproved)
                                    Confirmed by {{ $task->clientApprover->name ?? 'Strategist' }}
                                @else
                                    Pending client confirmation
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="std-meta-row">
                        <span class="std-meta-k"><i class="fa-solid fa-user-pen"></i> Created By</span>
                        <span class="std-meta-v" style="color:var(--text2)">{{ $task->creator?->name ?? 'Team' }}</span>
                    </div>
                </div>
            </div>

            {{-- 3. ACTIVITY & DISCUSSION --}}
            <div class="std-card">
                <div class="std-card-header">
                    <div class="std-card-title">
                        <i class="fa-solid fa-comments" style="color:var(--primary)"></i>
                        <span>Activity & Notes</span>
                        <span style="font-size:10px;font-weight:800;background:rgba(79,109,240,0.1);color:var(--primary);padding:1px 6px;border-radius:99px">{{ $task->comments->count() }}</span>
                    </div>
                </div>

                <div class="std-comment-stream comments-list">
                    @forelse($task->comments as $comment)
                        <div class="std-comment-item">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;margin-bottom:4px">
                                <div style="display:flex;align-items:center;gap:6px">
                                    <span style="width:18px;height:18px;border-radius:50%;background:{{ $comment->user->avatar_color ?? 'var(--primary)' }};color:#fff;font-size:8.5px;display:inline-flex;align-items:center;justify-content:center;font-weight:700">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </span>
                                    <span style="font-weight:700;color:var(--text);font-size:12px">{{ $comment->user->name }}</span>
                                </div>
                                <span style="font-size:10.5px;color:var(--text3)">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="color:var(--text2);font-size:12px;line-height:1.5;white-space:pre-wrap">{{ $comment->body }}</div>
                        </div>
                    @empty
                        <div style="text-align:center;padding:16px;color:var(--text3);font-size:12px">No discussion notes yet.</div>
                    @endforelse
                </div>

                @if(in_array($task->status, ['todo', 'inprogress', 'review']))
                    <form method="POST" action="{{ route('strategist.tasks.comment', $task) }}" style="margin-top:auto;display:flex;flex-direction:column;gap:8px">
                        @csrf
                        <textarea name="body" placeholder="Write a note or feedback..." required rows="2" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card2);font-size:12px;color:var(--text);font-family:inherit;resize:vertical;box-sizing:border-box;line-height:1.5"></textarea>
                        <button type="submit" class="btn-primary" style="width:100%;padding:9px 16px;font-size:12.5px;font-weight:700;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;gap:7px;cursor:pointer">
                            <i class="fa-solid fa-paper-plane" style="font-size:11.5px"></i> Post Note
                        </button>
                    </form>
                @endif
            </div>

        </aside>

    </div>

</div>



{{-- ═══ LIGHTBOX MODAL ═══ --}}
<div id="strategistLightbox" class="strategist-lightbox" onclick="strategistCloseLightbox(event)">
    <div class="strategist-lightbox-dialog" onclick="event.stopPropagation()">
        <div class="strategist-lightbox-header">
            <div class="strategist-lightbox-title-wrap">
                <span id="strategistLightboxIndex" class="strategist-lightbox-counter">1 / 1</span>
                <span id="strategistLightboxTitle" class="strategist-lightbox-title">Media Preview</span>
            </div>
            <div class="strategist-lightbox-actions">
                <a id="strategistLightboxDownload" href="#" download class="btn-primary strategist-lb-btn strategist-lb-dl" title="Download File">
                    <i class="fa-solid fa-download"></i> <span>Download</span>
                </a>
                <a id="strategistLightboxNewTab" href="#" target="_blank" rel="noopener" class="btn-sec strategist-lb-btn" title="Open Full File">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <span>Open</span>
                </a>
                <button type="button" class="strategist-lightbox-close" onclick="strategistCloseLightbox(event)" aria-label="Close">&times;</button>
            </div>
        </div>

        <div class="strategist-lightbox-content-wrap">
            <button type="button" id="strategistLightboxPrev" class="strategist-lb-nav strategist-lb-prev" onclick="strategistLightboxNav(-1)" aria-label="Previous">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <div class="strategist-lightbox-body" id="strategistLightboxBody"></div>
            <button type="button" id="strategistLightboxNext" class="strategist-lb-nav strategist-lb-next" onclick="strategistLightboxNav(1)" aria-label="Next">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<style>
    /* Lightbox Modal CSS */
    .strategist-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99998;
        background: rgba(10,15,29,0.92);
        backdrop-filter: blur(8px);
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .strategist-lightbox.open { display: flex; animation: slbFade .18s ease; }
    @keyframes slbFade { from { opacity: 0; } to { opacity: 1; } }
    .strategist-lightbox-dialog {
        max-width: 94vw;
        max-height: 94vh;
        width: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .strategist-lightbox-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 18px;
        background: rgba(15,23,42,0.88);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 12px 12px 0 0;
        color: #fff;
    }
    .strategist-lightbox-title-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1;
    }
    .strategist-lightbox-counter {
        font-size: 11px;
        font-weight: 800;
        padding: 3px 9px;
        border-radius: 20px;
        background: rgba(79,109,240,0.3);
        color: #93c5fd;
        border: 1px solid rgba(147,197,253,0.3);
        white-space: nowrap;
    }
    .strategist-lightbox-title {
        font-size: 13px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .strategist-lightbox-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }
    .strategist-lb-btn {
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        cursor: pointer;
    }
    .strategist-lb-dl {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
        border: none !important;
        color: #fff !important;
    }
    .strategist-lightbox-close {
        background: rgba(255,255,255,0.12);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.2);
        width: 32px;
        height: 32px;
        border-radius: 8px;
        font-size: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .strategist-lightbox-close:hover {
        background: rgba(239,68,68,0.4);
        border-color: #ef4444;
    }
    .strategist-lightbox-content-wrap {
        position: relative;
        background: #000;
        border: 1px solid rgba(255,255,255,0.12);
        border-top: none;
        border-radius: 0 0 12px 12px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 350px;
        max-height: calc(88vh - 65px);
    }
    .strategist-lightbox-body {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .strategist-lightbox-body img,
    .strategist-lightbox-body video {
        max-width: 100%;
        max-height: calc(85vh - 90px);
        object-fit: contain;
        border-radius: 6px;
    }
    .strategist-lb-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: rgba(15,23,42,0.65);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255,255,255,0.25);
        color: #fff;
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all .2s;
        z-index: 10;
    }
    .strategist-lb-nav:hover {
        background: var(--primary);
        border-color: var(--primary);
        transform: translateY(-50%) scale(1.1);
    }
    .strategist-lb-prev { left: 16px; }
    .strategist-lb-next { right: 16px; }
</style>

{{-- REVISION MODAL --}}
<div id="revisionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(5px);">
    <div style="background:var(--card);border-radius:14px;padding:24px;width:90%;max-width:500px;border:1px solid var(--border);box-shadow:0 24px 48px rgba(0,0,0,0.25)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
            <h3 style="margin:0;font-size:16px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-rotate-left" style="color:#F59E0B"></i> Request Revision
            </h3>
            <button type="button" onclick="document.getElementById('revisionModal').style.display='none'" style="background:none;border:none;font-size:18px;color:var(--text3);cursor:pointer;padding:0;line-height:1">&times;</button>
        </div>
        <form id="revisionForm" method="POST" action="{{ route('strategist.tasks.request-revision', $task) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div style="margin-bottom:12px">
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:0.3px;margin-bottom:5px">
                    Reason / Revision Category
                </label>
                <select name="revision_reason" style="width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card2);font-size:12.5px;color:var(--text);font-family:inherit;box-sizing:border-box">
                    <option value="Creative Brief Mismatch">Creative Brief Mismatch</option>
                    <option value="Visual / Aesthetic Polish" selected>Visual / Aesthetic Polish (Colors, Layout, Font)</option>
                    <option value="Typo / Copy Error">Typo / Copy Error</option>
                    <option value="Client Requested Changes">Client Requested Changes</option>
                    <option value="Video Pacing / Music Edit">Video Pacing / Music / Transition Edit</option>
                    <option value="Format / Dimensions Incorrect">Format / Dimensions Incorrect</option>
                    <option value="Other Specific Adjustments">Other Specific Adjustments</option>
                </select>
            </div>

            <div style="margin-bottom:12px">
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:0.3px;margin-bottom:5px">
                    Detailed Change Instructions
                </label>
                <textarea name="revision_note" required rows="4" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card2);font-size:12.5px;font-family:inherit;resize:vertical;color:var(--text);box-sizing:border-box;line-height:1.5" placeholder="Describe the exact changes required so the designer can execute without ambiguity..."></textarea>
            </div>

            <div style="margin-bottom:16px">
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:0.3px;margin-bottom:5px">
                    <i class="fa-solid fa-paperclip" style="color:#F59E0B;margin-right:4px"></i> Markup Screenshots / Reference Files (Optional)
                </label>
                <input type="file" name="reference_files[]" multiple accept="image/*,video/*,.pdf,.zip,.doc,.docx" style="width:100%;padding:8px;border:1px dashed var(--border);border-radius:8px;background:var(--card2);font-size:12px;color:var(--text);box-sizing:border-box">
                <div style="font-size:10.5px;color:var(--text3);margin-top:4px">Attach screenshots with markup or new reference assets for the designer.</div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn-sec" onclick="document.getElementById('revisionModal').style.display='none'" style="font-size:12px;padding:7px 14px;border-radius:8px">Cancel</button>
                <button type="submit" class="btn-primary" style="background:#F59E0B;border-color:#F59E0B;color:#fff;font-size:12px;padding:7px 16px;border-radius:8px;font-weight:700">
                    <i class="fa-solid fa-paper-plane" style="margin-right:4px"></i> Send Revision
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ON HOLD MODAL --}}
<div id="onHoldModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:var(--card);border-radius:12px;padding:24px;width:90%;max-width:420px;border:1px solid var(--border);box-shadow:0 20px 40px rgba(0,0,0,0.2)">
        <h3 style="margin:0 0 14px 0;font-size:16px;font-weight:700;color:var(--text)"><i class="fa-solid fa-pause-circle" style="color:#EF4444;margin-right:8px"></i> Place Task On Hold</h3>
        <form method="POST" action="{{ route('strategist.tasks.on-hold', $task) }}">
            @csrf
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:12px;font-weight:600;margin-bottom:6px;color:var(--text2)">Reason for Hold</label>
                <textarea name="reason" rows="3" required placeholder="e.g. Waiting for client assets..." style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid var(--border);background:var(--card2);color:var(--text);font-family:inherit;font-size:12.5px;box-sizing:border-box"></textarea>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" class="btn-sec" onclick="document.getElementById('onHoldModal').style.display='none'" style="font-size:12px;padding:7px 14px">Cancel</button>
                <button type="submit" class="btn-primary" style="background:#EF4444;border-color:#EF4444;color:#fff;font-size:12px;padding:7px 16px">Put On Hold</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const strategistMediaList = [
        @foreach($taskMedia as $m)
            {
                url: '{{ $m->getUrl() }}',
                name: '{{ addslashes($m->name ?: $m->file_name ?: "Media File") }}',
                isVideo: {{ $m->isVideo() ? 'true' : 'false' }},
                isPdf: {{ $m->isPdf() ? 'true' : 'false' }},
                size: '{{ $m->getFormattedSize() }}'
            },
        @endforeach
    ];

    let strategistCurrentMediaIdx = 0;

    function strategistOpenLightbox(index) {
        if (!strategistMediaList || !strategistMediaList.length) return;
        strategistCurrentMediaIdx = Math.max(0, Math.min(index, strategistMediaList.length - 1));
        renderStrategistLightbox();

        const wrap = document.getElementById('strategistLightbox');
        if (wrap) {
            wrap.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    }

    function renderStrategistLightbox() {
        const wrap = document.getElementById('strategistLightbox');
        const body = document.getElementById('strategistLightboxBody');
        const counter = document.getElementById('strategistLightboxIndex');
        const title = document.getElementById('strategistLightboxTitle');
        const dlBtn = document.getElementById('strategistLightboxDownload');
        const newTabBtn = document.getElementById('strategistLightboxNewTab');
        const prevBtn = document.getElementById('strategistLightboxPrev');
        const nextBtn = document.getElementById('strategistLightboxNext');

        if (!wrap || !body || !strategistMediaList.length) return;

        const item = strategistMediaList[strategistCurrentMediaIdx];
        if (!item) return;

        if (counter) counter.textContent = (strategistCurrentMediaIdx + 1) + ' / ' + strategistMediaList.length;
        if (title) title.textContent = item.name + (item.size ? ' (' + item.size + ')' : '');
        if (dlBtn) {
            dlBtn.href = item.url;
            dlBtn.setAttribute('download', item.name);
        }
        if (newTabBtn) newTabBtn.href = item.url;

        if (prevBtn) prevBtn.style.display = (strategistMediaList.length > 1) ? 'flex' : 'none';
        if (nextBtn) nextBtn.style.display = (strategistMediaList.length > 1) ? 'flex' : 'none';

        if (item.isVideo) {
            body.innerHTML = '<video src="' + item.url + '" controls autoplay playsinline preload="auto" style="max-width:100%;max-height:80vh;border-radius:8px;background:#000;box-shadow:0 20px 40px rgba(0,0,0,0.5);outline:none"></video>';
        } else if (item.isPdf) {
            body.innerHTML = '<iframe src="' + item.url + '" style="width:85vw;height:80vh;border:none;border-radius:8px;background:#fff;box-shadow:0 20px 40px rgba(0,0,0,0.5)"></iframe>';
        } else {
            body.innerHTML = '<img src="' + item.url + '" alt="' + item.name + '">';
        }
    }

    function strategistLightboxNav(dir) {
        if (!strategistMediaList.length) return;
        strategistCurrentMediaIdx = (strategistCurrentMediaIdx + dir + strategistMediaList.length) % strategistMediaList.length;
        renderStrategistLightbox();
    }

    function strategistCloseLightbox(e) {
        if (e && e.target && e.target.classList && !e.target.classList.contains('strategist-lightbox') && !e.target.classList.contains('strategist-lightbox-close')) return;
        const wrap = document.getElementById('strategistLightbox');
        if (!wrap) return;
        wrap.classList.remove('open');
        const body = document.getElementById('strategistLightboxBody');
        if (body) body.innerHTML = '';
        document.body.style.overflow = '';
    }

    function strategistDownloadAllMedia() {
        if (!strategistMediaList || !strategistMediaList.length) return;

        strategistMediaList.forEach((media, idx) => {
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

    let currentDeliverableVersionFilter = 'all';

    function filterDeliverableVersion(version) {
        currentDeliverableVersionFilter = version;

        // Update tab buttons active state
        document.querySelectorAll('.std-ver-tab').forEach(btn => {
            const isMatch = (btn.dataset.version === String(version));
            btn.classList.toggle('active', isMatch);
        });

        // If currently in comparison mode, exit comparison mode first
        const compView = document.getElementById('stdComparisonView');
        const normalGrid = document.getElementById('stdNormalMediaGrid');
        const compBtn = document.getElementById('stdToggleCompareBtn');
        if (compView && compView.style.display !== 'none') {
            compView.style.display = 'none';
            if (normalGrid) normalGrid.style.display = 'grid';
            if (compBtn) {
                compBtn.innerHTML = '<i class="fa-solid fa-columns" style="color:#8B5CF6"></i> <span>Compare Versions</span>';
            }
        }

        // Filter deliverable items
        const items = document.querySelectorAll('.std-deliverable-item');
        items.forEach(el => {
            if (version === 'all') {
                el.style.display = '';
            } else {
                const itemRev = el.getAttribute('data-revision-count');
                el.style.display = (itemRev === String(version)) ? '' : 'none';
            }
        });
    }

    function toggleDeliverableComparison() {
        const normalGrid = document.getElementById('stdNormalMediaGrid');
        const compView = document.getElementById('stdComparisonView');
        const compBtn = document.getElementById('stdToggleCompareBtn');

        if (!compView) return;

        const isCurrentlyComparing = (compView.style.display !== 'none');

        if (isCurrentlyComparing) {
            // Revert back to normal grid
            compView.style.display = 'none';
            if (normalGrid) normalGrid.style.display = 'grid';
            if (compBtn) {
                compBtn.innerHTML = '<i class="fa-solid fa-columns" style="color:#8B5CF6"></i> <span>Compare Versions</span>';
            }
        } else {
            // Switch to side-by-side comparison
            compView.style.display = 'block';
            if (normalGrid) normalGrid.style.display = 'none';
            if (compBtn) {
                compBtn.innerHTML = '<i class="fa-solid fa-table-cells-large" style="color:#10B981"></i> <span>Standard Grid View</span>';
            }
        }
    }

    document.addEventListener('keydown', (e) => {
        const wrap = document.getElementById('strategistLightbox');
        if (!wrap || !wrap.classList.contains('open')) return;

        if (e.key === 'Escape') {
            strategistCloseLightbox({ target: { classList: { contains: () => true } } });
        } else if (e.key === 'ArrowLeft') {
            strategistLightboxNav(-1);
        } else if (e.key === 'ArrowRight') {
            strategistLightboxNav(1);
        }
    });

    function toggleCaptionEdit(forceState) {
        const viewEl = document.getElementById('captionViewSection');
        const editEl = document.getElementById('captionEditSection');
        const btnText = document.getElementById('captionEditBtnText');
        if (!viewEl || !editEl) return;

        const isCurrentlyHidden = editEl.style.display === 'none';
        const show = (typeof forceState === 'boolean') ? forceState : isCurrentlyHidden;

        if (show) {
            viewEl.style.display = 'none';
            editEl.style.display = 'block';
            if (btnText) btnText.textContent = 'Close';
            const input = document.getElementById('captionInput');
            if (input) input.focus();
        } else {
            viewEl.style.display = 'flex';
            editEl.style.display = 'none';
            if (btnText) btnText.textContent = 'Edit';
        }
    }

    function clearCaptionAndHashtags() {
        if (!confirm('Are you sure you want to clear/delete the marketing caption and hashtags?')) return;
        const captionInput = document.getElementById('captionInput');
        const hashtagsInput = document.getElementById('hashtagsInput');
        if (captionInput) captionInput.value = '';
        if (hashtagsInput) hashtagsInput.value = '';
        const charCount = document.getElementById('captionCharCount');
        if (charCount) charCount.textContent = '0 chars';
        renderHashtagsLivePreview('');
        const captionForm = document.getElementById('captionHashtagsForm');
        if (captionForm) {
            captionForm.dispatchEvent(new Event('submit', { cancelable: true }));
        }
    }

    function renderHashtagsLivePreview(val) {
        const previewEl = document.getElementById('hashtagsLivePreview');
        if (!previewEl) return;
        const tags = (val || '').split(/\s+/).map(t => t.trim().replace(/^#/, '')).filter(t => t.length > 0);
        if (!tags.length) {
            previewEl.innerHTML = '<div style="font-size:12px;color:var(--text3);font-style:italic">Type hashtags above to preview cylindrical tags.</div>';
            return;
        }
        previewEl.innerHTML = tags.map(t => `<span class="std-hashtag-pill"><span class="std-hashtag-hash">#</span>${t}</span>`).join('');
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Caption/Hashtags AJAX submit
        const captionForm = document.getElementById('captionHashtagsForm');
        if (captionForm) {
            captionForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(captionForm);
                const submitBtn = captionForm.querySelector('button[type="submit"]');
                if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; }

                fetch(captionForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw data;
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        if (typeof ajax !== 'undefined') ajax.showSuccess('Caption & hashtags saved!');
                        setTimeout(() => location.reload(), 600);
                    }
                })
                .catch(err => {
                    if (typeof ajax !== 'undefined') ajax.showError(err.message || 'Error saving');
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Caption & Hashtags'; }
                });
            });
        }
    });

function confirmStrategistApproveSwal(e, form) {
    e.preventDefault();
    Swal.fire({
        title: 'Approve Task?',
        text: 'Notice: Caption or hashtags have not been added yet for this post. Do you want to approve anyway?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.removeAttribute('onsubmit');
            form.submit();
        }
    });
    return false;
}

async function toggleClientApproval(taskId, btn) {
    if (btn.disabled) return;
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        const response = await fetch(`/tasks/${taskId}/toggle-client-approval`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const data = await response.json();
        if (data.success) {
            // Update Header Pill
            const headerPill = document.getElementById(`std-header-client-pill-${taskId}`);
            const headerIcon = document.getElementById(`std-header-client-icon-${taskId}`);
            const headerStatus = document.getElementById(`std-header-client-status-${taskId}`);
            if (headerPill && headerStatus) {
                headerStatus.innerText = data.is_approved ? 'Yes' : 'No';
                headerPill.style.background = data.is_approved ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.1)';
                headerPill.style.borderColor = data.is_approved ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.25)';
                headerPill.style.color = data.is_approved ? '#059669' : '#EF4444';
                if (headerIcon) {
                    headerIcon.className = `fa-solid ${data.is_approved ? 'fa-check' : 'fa-xmark'}`;
                }
            }

            // Update Meta Badge & Button
            const metaBadge = document.getElementById(`std-meta-client-badge-${taskId}`);
            const metaBtn = document.getElementById(`std-meta-client-btn-${taskId}`);
            const metaNote = document.getElementById(`std-meta-client-note-${taskId}`);
            if (metaBadge) {
                metaBadge.innerHTML = `<i class="fa-solid ${data.is_approved ? 'fa-check' : 'fa-xmark'}"></i> ${data.is_approved ? 'Yes' : 'No'}`;
                metaBadge.style.background = data.is_approved ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.1)';
                metaBadge.style.borderColor = data.is_approved ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.25)';
                metaBadge.style.color = data.is_approved ? '#059669' : '#EF4444';
            }
            if (metaBtn) {
                metaBtn.innerText = data.is_approved ? 'Mark No' : 'Mark Yes';
            }
            if (metaNote) {
                metaNote.innerText = data.is_approved
                    ? `Confirmed by ${data.approved_by || 'You'} (${data.approved_at || 'Just now'})`
                    : 'Pending client confirmation';
            }
        } else {
            alert(data.message || 'Failed to update client approval');
            btn.innerHTML = originalText;
        }
    } catch (e) {
        console.error(e);
        btn.innerHTML = originalText;
    } finally {
        btn.disabled = false;
    }
}
</script>
@endpush

@endsection
