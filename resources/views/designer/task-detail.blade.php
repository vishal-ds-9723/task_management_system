@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/task-detail.css') }}?v=6.2">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}?v=2.0">
    <link rel="stylesheet" href="{{ asset('css/pages/designer-theme.css') }}?v=6.0">
@endpush

@section('content')
@php
    $workflowSteps = $workflowSteps ?? [];
    $taskPlatforms = array_values(array_filter((array) $task->platform));
    $urgentBrief = $task->is_urgent_task
        ? trim((string) ($task->brief ?: $task->caption ?: ''))
        : '';
    $showUrgentCaption = $task->is_urgent_task
        && trim((string) ($task->caption ?? '')) !== ''
        && trim((string) $task->caption) !== $urgentBrief;
    $allMedia = $task->media()->get();
    $referenceMedia = $allMedia->filter(fn($m) => $m->isReference())->values();
    $deliverableMedia = $allMedia->filter(fn($m) => $m->isDeliverable())->values();
@endphp

<div class="task-minimal">
    {{-- ══════════════════════════════════════════════
         1 · STUDIO TOPBAR / HEADER HERO STRIP
         ══════════════════════════════════════════════ --}}
    <header class="dt-hero">
        <div>
            <div class="dt-breadcrumbs">
                <a href="{{ route('designer.tasks') }}" class="dt-back-btn">
                    <i class="fa-solid fa-arrow-left"></i> My Deliverables
                </a>
                <span class="dt-sep">/</span>
                <span class="dt-task-code">#TSK-{{ str_pad($task->id, 4, '0', STR_PAD_LEFT) }}</span>
                <span class="dt-sep">·</span>
                <span style="font-weight:700;color:var(--dt-text-sub)">{{ $task->client->name }}</span>
            </div>
            
            <div class="dt-title-row">
                <x-client-branding :client="$task->client" size="26px" radius="6px" fit="contain" />
                <h1 class="dt-title">{{ $task->title }}</h1>
                <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                
                @if($task->priority === 'urgent')
                    <span class="dt-revision-tag" style="background:#FFF1F2;color:#E11D48;border-color:rgba(244,63,94,0.3)">
                        <i class="fa-solid fa-bolt"></i> Urgent Priority
                    </span>
                @endif
                
                @if($task->revision_count > 0)
                    <span class="dt-revision-tag">
                        <i class="fa-solid fa-rotate-left"></i> Revision #{{ $task->revision_count }}
                    </span>
                @endif
            </div>
        </div>

        <div class="dt-hero-actions">
            @if($task->status === 'inprogress' && $task->started_at)
                @php
                    $elapsed = $task->started_at->diff(now());
                    $elapsedStr = '';
                    if ($elapsed->d > 0) $elapsedStr .= $elapsed->d . 'd ';
                    if ($elapsed->h > 0) $elapsedStr .= $elapsed->h . 'h ';
                    $elapsedStr .= $elapsed->i . 'm';
                @endphp
                <div class="dt-active-timer-badge">
                    <span class="dt-pulse-emerald"></span>
                    <span>{{ trim($elapsedStr) ?: 'Just started' }} active</span>
                </div>
            @endif

            <a href="{{ route('designer.tasks') }}" class="btn-dt-all-tasks">
                <i class="fa-solid fa-layer-group"></i> All Tasks
            </a>
        </div>
    </header>

    {{-- ══════════════════════════════════════════════
         2 · SLIM WORKFLOW STEPPER PIPELINE
         ══════════════════════════════════════════════ --}}
    @if(!empty($workflowSteps))
    <div class="dt-stepper-card">
        <div class="dt-stepper-track">
            @foreach($workflowSteps as $i => $step)
                <div class="dt-step {{ $step['done'] ? 'dt-done' : '' }} {{ $step['current'] ?? false ? 'dt-current' : '' }}">
                    <div class="dt-step-icon">
                        @if($step['done'])
                            <i class="fa-solid fa-check"></i>
                        @else
                            {!! is_numeric($step['icon']) ? $step['icon'] : $step['icon'] !!}
                        @endif
                    </div>
                    <div>
                        <div class="dt-step-label">{{ $step['label'] }}</div>
                        @if($step['time'])
                            <div class="dt-step-time">{{ $step['time']->format('d M, h:i A') }}</div>
                        @elseif($step['current'] ?? false)
                            <div class="dt-step-time">Current Phase</div>
                        @endif
                    </div>
                </div>
                @if($i < count($workflowSteps) - 1)
                    <div class="dt-connector {{ $step['done'] ? 'dt-conn-done' : '' }}"></div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════
         3 · HIGH-CRAFT NOTICES & REVISION CALLOUT
         ══════════════════════════════════════════════ --}}
    @if($task->status === 'todo' && empty($isReadOnly))
        <div class="dt-status-banner dt-banner-queue">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(37,99,235,0.12);display:flex;align-items:center;justify-content:center;color:#2563EB;font-size:16px">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
                <div>
                    <div style="font-weight:800;font-size:14px;color:#1E3A8A">Ready to Design · Task in Queue</div>
                    <div style="font-size:12px;color:#3B82F6;margin-top:2px">Review the creative brief and content specs below. When ready, click Start Working to begin working on this task.</div>
                </div>
            </div>
            <form method="POST" action="{{ route('designer.tasks.start', $task) }}" style="margin:0">
                @csrf
                <button type="submit" class="btn-dt-action-start" style="width:auto;padding:8px 18px;font-size:12.5px">
                    <i class="fa-solid fa-play"></i> Start Working
                </button>
            </form>
        </div>
    @elseif($task->revision_count > 0 && $task->status === 'inprogress')
        <div class="dt-revision-callout">
            <div class="dt-revision-header">
                <div class="dt-revision-badge-wrap">
                    <div class="dt-revision-icon">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <div>
                        <div class="dt-revision-title">
                            <span>Revision #{{ $task->revision_count }} Requested</span>
                            <span class="dt-revision-tag">Changes Required</span>
                        </div>
                        <div class="dt-revision-meta">
                            Requested by <strong>{{ $latestRevisionComment?->user?->name ?? 'Strategist / Admin' }}</strong>
                            @if($latestRevisionComment?->created_at)
                                · {{ $latestRevisionComment->created_at->diffForHumans() }}
                            @endif
                        </div>
                    </div>
                </div>

                <a href="#commentsList" class="btn-dt-copy" style="background:#FFFFFF;border-color:rgba(244,63,94,0.3);color:#BE123C">
                    <i class="fa-solid fa-comments"></i> View Discussion Thread
                </a>
            </div>

            {{-- Exact Revision Feedback Quote --}}
            <div class="dt-revision-quote">
                <div class="dt-revision-quote-title">
                    <i class="fa-solid fa-quote-left"></i> Feedback & Changes Requested:
                </div>
                <div class="dt-revision-quote-body">{{ $latestRevisionNotes ?: ($latestRevisionComment?->body ?: 'Please review the notes and upload revised creative files.') }}</div>
            </div>

            {{-- Attached Markup/Screenshots for this Revision --}}
            @if(isset($latestRevisionMedia) && $latestRevisionMedia->isNotEmpty())
                <div style="margin-top:14px;padding-top:12px;border-top:1px dashed rgba(244,63,94,0.35)">
                    <div style="font-size:11.5px;font-weight:800;color:#9F1239;margin-bottom:8px;display:flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-paperclip"></i>
                        <span>Attached Reference & Markup Screenshots ({{ $latestRevisionMedia->count() }}):</span>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:10px">
                        @foreach($latestRevisionMedia as $revMedia)
                            <div style="display:flex;align-items:center;gap:8px;background:#FFFFFF;border:1px solid rgba(244,63,94,0.3);border-radius:9px;padding:6px 10px;max-width:280px;box-shadow:0 1px 3px rgba(0,0,0,0.04)">
                                @if($revMedia->isImage())
                                    <a href="{{ $revMedia->getUrl() }}" target="_blank">
                                        <img src="{{ $revMedia->getUrl() }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #E2E8F0" alt="Markup preview">
                                    </a>
                                @elseif($revMedia->isPdf())
                                    <i class="fa-solid fa-file-pdf" style="font-size:26px;color:#DC2626"></i>
                                @else
                                    <i class="fa-solid fa-file" style="font-size:26px;color:#64748B"></i>
                                @endif
                                <div style="flex:1;min-width:0">
                                    <div style="font-size:11.5px;font-weight:700;color:#0F172A;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        {{ $revMedia->name ?: $revMedia->file_name }}
                                    </div>
                                    <div style="font-size:10px;color:#64748B">{{ $revMedia->getFormattedSize() }}</div>
                                </div>
                                <a href="{{ $revMedia->getUrl() }}" target="_blank" class="btn-dt-copy" title="View Fullscreen">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @elseif($task->is_paused)
        <div class="dt-status-banner dt-banner-paused">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:22px">⏸️</span>
                <div>
                    <div style="font-weight:800;font-size:14px;color:#92400E">Task Currently Paused</div>
                    <div style="font-size:12px;color:#B45309;margin-top:2px">Reason: {{ $task->pause_reason ?: 'Designer paused work' }} · Timer preserved</div>
                </div>
            </div>
            <form method="POST" action="{{ route('designer.tasks.resume', $task) }}" style="margin:0">
                @csrf
                <button type="submit" class="btn-dt-resume" style="width:auto;padding:8px 18px;font-size:12.5px">
                    <i class="fa-solid fa-play"></i> Resume Work
                </button>
            </form>
        </div>
    @elseif($task->status === 'review')
        <div class="dt-status-banner dt-banner-review">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(124,58,237,0.12);display:flex;align-items:center;justify-content:center;color:#7C3AED;font-size:16px">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div>
                    <div style="font-weight:800;font-size:14px;color:#5B21B6">Deliverables Submitted · Awaiting Review</div>
                    <div style="font-size:12px;color:#7C3AED;margin-top:2px">Submitted deliverables are currently with the strategist and client for quality approval.</div>
                </div>
            </div>
            <span class="tag tag-review" style="font-size:11px;font-weight:800;padding:4px 10px">In Review</span>
        </div>
    @elseif(in_array($task->status, ['completed', 'published']))
        <div class="dt-status-banner dt-banner-completed">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:38px;height:38px;border-radius:10px;background:rgba(16,185,129,0.12);display:flex;align-items:center;justify-content:center;color:#059669;font-size:16px">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <div style="font-weight:800;font-size:14px;color:#065F46">Deliverable Completed & Approved</div>
                    <div style="font-size:12px;color:#059669;margin-top:2px">Great job! All design outputs have been finalized and signed off.</div>
                </div>
            </div>
            <span class="tag tag-completed" style="font-size:11px;font-weight:800;padding:4px 10px">Shipped</span>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════
         4 · BALANCED TWO-COLUMN WORKSPACE
         ══════════════════════════════════════════════ --}}
    <div class="dt-workspace">
        {{-- ── COLUMN 1: CREATIVE WORKSPACE (LEFT 60%) ── --}}
        <main>
            {{-- 1 · CREATIVE BRIEF & CONTENT SPECIFICATIONS --}}
            <div class="dt-card">
                <div class="dt-card-header">
                    <div class="dt-card-header-left">
                        <div class="dt-card-icon"><i class="fa-solid fa-file-lines"></i></div>
                        <div>
                            <h2 class="dt-card-title">Creative Brief & Content Specs</h2>
                            <div class="dt-card-subtitle">Essential guidelines, platform requirements, and brief</div>
                        </div>
                    </div>
                    <span class="tag tag-primary" style="font-size:11px;font-weight:800;padding:3px 9px">{{ ucfirst($task->type) }}</span>
                </div>

                {{-- Compact Metadata Strip --}}
                <div class="dt-meta-grid">
                    <div class="dt-meta-item">
                        <span class="dt-meta-label">Client Account</span>
                        <div class="dt-meta-value">
                            <x-client-branding :client="$task->client" size="20px" radius="4px" fit="contain" />
                            <span>{{ $task->client->name }}</span>
                        </div>
                    </div>

                    <div class="dt-meta-item">
                        <span class="dt-meta-label">Target Platforms</span>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            @if(!empty($taskPlatforms))
                                @foreach($taskPlatforms as $platform)
                                    @php $platLower = strtolower($platform); @endphp
                                    <span class="dt-platform-pill platform-{{ $platLower }}">
                                        @if(in_array($platLower, ['instagram', 'facebook', 'linkedin', 'twitter', 'x', 'whatsapp']))
                                            <i class="fa-brands fa-{{ $platLower === 'twitter' || $platLower === 'x' ? 'x-twitter' : $platLower }}"></i>
                                        @endif
                                        {{ ucfirst($platform) }}
                                    </span>
                                @endforeach
                            @else
                                <span style="font-size:12.5px;color:var(--dt-text-muted);font-weight:600">Standard Channel</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Urgent Creative Brief Callout (if urgent task) --}}
                @if($task->is_urgent_task && $urgentBrief !== '')
                    <div class="dt-brief-box" style="border-left:3.5px solid #E11D48;background:#FFF1F2">
                        <div class="dt-brief-box-header">
                            <span class="dt-brief-box-label" style="color:#BE123C">
                                <i class="fa-solid fa-bolt"></i> Urgent Brief Instruction
                            </span>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ addslashes($urgentBrief) }}'); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy', 1500)" class="btn-dt-copy">Copy</button>
                        </div>
                        <div class="dt-brief-content" style="color:#9F1239;font-weight:600">{{ $urgentBrief }}</div>
                    </div>
                @endif

                {{-- Standard Creative Brief --}}
                @if($task->brief && !$task->is_urgent_task)
                    <div class="dt-brief-box">
                        <div class="dt-brief-box-header">
                            <span class="dt-brief-box-label">
                                <i class="fa-solid fa-pen-nib"></i> Creative Brief
                            </span>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ addslashes($task->brief) }}'); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy', 1500)" class="btn-dt-copy">Copy</button>
                        </div>
                        <div class="dt-brief-content">{{ $task->brief }}</div>
                    </div>
                @endif

                {{-- Reference Links (Clean chips with direct URLs) --}}
                @if(!empty($task->reference_links))
                    @php
                        $refLinks = (is_array($task->reference_links) ? $task->reference_links : json_decode($task->reference_links, true)) ?? [];
                        $validRefLinks = array_filter($refLinks, fn($l) => filter_var($l, FILTER_VALIDATE_URL));
                    @endphp
                    @if(count($validRefLinks) > 0)
                        <div style="margin-top:12px">
                            <span class="dt-meta-label" style="display:block;margin-bottom:6px">Reference Materials & Links</span>
                            <div class="dt-ref-links">
                                @foreach($validRefLinks as $link)
                                    <a href="{{ $link }}" target="_blank" class="dt-ref-chip">
                                        <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px;color:var(--dt-primary)"></i>
                                        <span>{{ Str::limit(str_replace(['https://','http://','www.'],'',$link), 26) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            {{-- 2 · DELIVERABLES & DESIGN OUTPUTS STUDIO (HERO CANVAS) --}}
            <div class="dt-card dt-deliverables-card" id="deliverablesSection" style="margin-bottom:0">
                <div class="dt-card-header dt-deliverables-header">
                    <div class="dt-card-header-left">
                        <div class="dt-card-icon dt-card-icon-emerald">
                            <i class="fa-solid fa-paintbrush"></i>
                        </div>
                        <div class="dt-header-text-wrap">
                            <div class="dt-title-with-badge">
                                <h2 class="dt-card-title">Deliverables & Design Outputs</h2>
                                <span class="dt-badge-counter dt-badge-emerald" id="deliverableTabCount">
                                    {{ $deliverableMedia->count() }}
                                </span>
                            </div>
                            <div class="dt-card-subtitle">Upload finished visual artboards, reels, or revisions for review</div>
                        </div>
                    </div>

                    @if($deliverableMedia->count() > 0 && empty($isReadOnly))
                        <button type="button" onclick="triggerDeliverableUpload()" class="btn-dt-upload-new dt-header-upload-btn" title="Upload new version">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span class="dt-btn-text">Upload New Version</span>
                            <span class="dt-btn-text-mobile">+ Upload</span>
                        </button>
                    @endif
                </div>

                {{-- Zero-State Interactive Dropzone --}}
                @if($deliverableMedia->count() === 0 && empty($isReadOnly))
                    <div class="dt-dropzone" id="emptyUploadZone" onclick="triggerDeliverableUpload()" role="button" tabindex="0">
                        <div class="dt-dropzone-icon">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                        </div>
                        <div class="dt-dropzone-title">Upload Creative Deliverable</div>
                        <p class="dt-dropzone-desc">Drag & drop artboard files, or tap to browse camera / gallery (PNG, JPG, MP4, PDF).</p>
                    </div>
                @endif

                {{-- Deliverables Gallery Grid --}}
                <div class="dt-media-grid" id="deliverablesPreviewContainer">
                    @foreach($deliverableMedia as $item)
                        @php $isRev = ($item->getRevisionCount() > 0); @endphp
                        <div class="dt-media-card {{ $isRev ? 'is-revision' : '' }}"
                             data-media-id="{{ $item->id }}"
                             onclick="openDeliverablePreview(event, '{{ $item->getUrl() }}', '{{ addslashes($task->title) }}', '{{ addslashes($task->client->name) }}', {{ $item->isVideo() ? 'true' : 'false' }}, '{{ addslashes($item->name) }}')"
                             title="Tap to preview high-res">
                            
                            {{-- Media Thumbnail View --}}
                            @if($item->isVideo())
                                <video class="dt-media-thumb" muted preload="metadata" playsinline style="background:#0F172A">
                                    <source src="{{ $item->getUrl() }}">
                                </video>
                                <div class="dt-media-chip-video">
                                    <i class="fa-solid fa-play"></i> Video
                                </div>
                            @elseif($item->isPdf())
                                <div class="dt-media-doc-view">
                                    <i class="fa-solid fa-file-pdf"></i>
                                    <span class="dt-media-doc-name">{{ Str::limit($item->name, 16) }}</span>
                                </div>
                            @elseif($item->isImage())
                                <img src="{{ $item->getUrl() }}" class="dt-media-thumb" loading="lazy" alt="{{ $item->name }}">
                            @else
                                <div class="dt-media-doc-view">
                                    <i class="fa-solid fa-file"></i>
                                    <span class="dt-media-doc-name">{{ Str::limit($item->name, 16) }}</span>
                                </div>
                            @endif

                            {{-- Mobile & Touch Quick Actions (Always accessible on phones) --}}
                            <div class="dt-media-touch-actions" onclick="event.stopPropagation()">
                                <a href="{{ $item->getUrl() }}" download="{{ $item->name }}" class="dt-touch-action-btn" title="Download" onclick="event.stopPropagation()">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                @if(empty($isReadOnly))
                                    <button type="button" onclick="deleteMediaAJAX({{ $item->id }}, event)" class="dt-touch-action-btn btn-trash" title="Delete deliverable" aria-label="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                @endif
                            </div>

                            {{-- Version Badge Ribbon --}}
                            <div class="dt-version-pill {{ $isRev ? 'pill-revision' : 'pill-initial' }}">
                                <span class="dt-v-label">{{ $item->getVersionBadgeLabel() }}</span>
                                <span class="dt-v-date">{{ $item->created_at->format('d M') }}</span>
                            </div>

                            {{-- Desktop Hover Actions Overlay --}}
                            <div class="dt-media-overlay" onclick="event.stopPropagation()">
                                <button type="button" onclick="openDeliverablePreview(event, '{{ $item->getUrl() }}', '{{ addslashes($task->title) }}', '{{ addslashes($task->client->name) }}', {{ $item->isVideo() ? 'true' : 'false' }}, '{{ addslashes($item->name) }}')" class="dt-media-action-btn" title="View Fullscreen">
                                    <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                                </button>
                                <a href="{{ $item->getUrl() }}" download="{{ $item->name }}" class="dt-media-action-btn" title="Download Asset" onclick="event.stopPropagation()">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                @if(empty($isReadOnly))
                                    <button type="button" onclick="deleteMediaAJAX({{ $item->id }}, event)" class="dt-media-action-btn btn-trash" title="Delete Deliverable">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Add Version Card in grid (Visible when deliverables exist on mobile/desktop) --}}
                    @if($deliverableMedia->count() > 0 && empty($isReadOnly))
                        <div class="dt-media-card dt-media-add-card" onclick="triggerDeliverableUpload()" title="Upload additional deliverable" role="button" tabindex="0">
                            <div class="dt-add-card-content">
                                <div class="dt-add-card-icon">
                                    <i class="fa-solid fa-plus"></i>
                                </div>
                                <span class="dt-add-card-text">Add Version</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Hidden Multi-File Upload Form --}}
                @if(empty($isReadOnly))
                <form method="POST" action="{{ route('designer.tasks.upload-media', $task) }}" enctype="multipart/form-data" id="mediaUploadForm" style="margin-top:14px">
                    @csrf
                    <input type="hidden" name="collection_name" id="uploadCollectionName" value="task-media">
                    <input type="file" name="media[]" id="mediaInput" accept="image/*,video/*,.pdf,.doc,.docx,.zip,.rar" multiple style="display:none" onchange="handleMediaSelect()">
                    
                    <div id="mediaPreview" class="dt-upload-tray" style="display:none">
                        <div class="dt-upload-tray-header">
                            <div>
                                <div id="uploadTypeHeading" style="font-size:11px;font-weight:800;color:var(--dt-primary);text-transform:uppercase;letter-spacing:0.06em">
                                    Selected for upload:
                                </div>
                                <div id="selectedFileCount" style="font-size:11.5px;color:var(--dt-text-muted);font-weight:600"></div>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px">
                                <button type="button" onclick="clearSelectedMedia()" class="btn-dt-clear" title="Clear selection">
                                    <i class="fa-solid fa-xmark"></i> Cancel
                                </button>
                                <button type="submit" id="mediaUploadBtn" class="btn-dt-upload-new dt-upload-tray-btn">
                                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload Now
                                </button>
                            </div>
                        </div>
                        <div id="fileList" style="margin-top:10px"></div>
                    </div>
                </form>
                @endif
            </div>
        </main>

        {{-- ── COLUMN 2: STUDIO ACTION RAIL & INSPECTOR (RIGHT 40%) ── --}}
        <aside class="dt-rail">
            {{-- 1 · WORKFLOW STATUS & ACTIONS CARD --}}
            <div class="dt-card">
                <div class="dt-card-header">
                    <div class="dt-card-header-left">
                        <div class="dt-card-icon"><i class="fa-solid fa-sliders"></i></div>
                        <h2 class="dt-card-title">Status & Actions</h2>
                    </div>
                </div>

                {{-- Status & Due Date Strip --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                    <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                    <span style="font-size:12px;font-weight:700;color:{{ $task->isOverdue() ? '#DC2626' : 'var(--dt-text-muted)' }}">
                        Due {{ $task->deadline?->format('d M') ?? 'Flexible' }}
                        @if($task->isOverdue())
                            <span style="color:#DC2626;font-size:10.5px">(Overdue)</span>
                        @endif
                    </span>
                </div>

                {{-- Primary Action Buttons based on Status --}}
                @if($task->status === 'todo' && empty($isReadOnly))
                    <form method="POST" action="{{ route('designer.tasks.start', $task) }}" id="startTaskColumnForm">
                        @csrf
                        <button type="submit" class="btn-dt-action-start">
                            <i class="fa-solid fa-play"></i> Start Working
                        </button>
                        <p style="font-size:11px;color:var(--dt-text-muted);text-align:center;margin-top:6px;font-weight:600">
                            Click to mark this task as In Progress
                        </p>
                    </form>
                @elseif($task->status === 'inprogress' && !$task->is_paused && empty($isReadOnly))
                    @if($task->revision_count > 0)
                        <div class="rev-card" style="background:#FFF1F2;border:1px solid rgba(244,63,94,0.3);margin-bottom:14px;border-radius:10px;padding:10px 12px">
                            <div class="rev-icon" style="color:#E11D48;background:#FFE4E6"><i class="fa-solid fa-rotate-left"></i></div>
                            <div style="flex:1;min-width:0">
                                <div class="rev-title" style="color:#9F1239">Revision #{{ $task->revision_count }} Active</div>
                                <div class="rev-meta" style="color:#BE123C;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    {{ $latestRevisionNotes ? Str::limit($latestRevisionNotes, 38) : 'Review revision feedback before submitting' }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('designer.tasks.submit', $task) }}" id="submitForm">
                        @csrf
                        <button type="submit" class="btn-dt-action-submit" id="submitBtn">
                            <i class="fa-solid fa-paper-plane"></i> Submit for Review
                        </button>
                        <p class="dt-submit-lock submit-lock-msg" style="display:{{ $deliverableMedia->count() === 0 ? 'flex' : 'none' }}">
                            <i class="fa-solid fa-lock"></i> Upload at least one deliverable to submit
                        </p>
                    </form>

                    {{-- Pause Task Button --}}
                    <button type="button" onclick="document.getElementById('pauseModal').style.display='flex'" class="btn-dt-pause">
                        ⏸️ Pause Task
                    </button>
                @elseif($task->is_paused && empty($isReadOnly))
                    <form method="POST" action="{{ route('designer.tasks.resume', $task) }}">
                        @csrf
                        <button type="submit" class="btn-dt-resume">
                            ▶️ Resume & Continue Working
                        </button>
                    </form>
                @elseif($task->status === 'review')
                    <div style="text-align:center;padding:12px;background:var(--dt-purple-light);border:1px solid var(--dt-purple-border);border-radius:10px;font-size:12px;color:var(--dt-purple);font-weight:700">
                        <i class="fa-solid fa-hourglass-half" style="margin-right:5px"></i> Awaiting Strategist / Client Approval
                    </div>
                @endif

                {{-- Team Members Inspector Row --}}
                <div class="dt-team-grid">
                    <div class="dt-team-card">
                        <div class="dt-av-sm" style="background:linear-gradient(135deg, #4F46E5, #7C3AED)">
                            {{ strtoupper(substr($task->creator->name ?? 'A', 0, 1)) }}
                        </div>
                        <div style="min-width:0">
                            <div class="dt-team-name">{{ $task->creator->name ?? 'Admin' }}</div>
                            <div class="dt-team-role">Strategist</div>
                        </div>
                    </div>

                    <div class="dt-team-card">
                        <div class="dt-av-sm" style="background:linear-gradient(135deg, #059669, #10B981)">
                            {{ strtoupper(substr($task->assignee->name ?? 'M', 0, 1)) }}
                        </div>
                        <div style="min-width:0">
                            <div class="dt-team-name">{{ $task->assignee->name ?? 'Me' }}</div>
                            <div class="dt-team-role">Creative Designer</div>
                        </div>
                    </div>
                </div>

                {{-- Client Social Profile Links (if configured) --}}
                @if(($selectedSocialMediaLinks ?? collect())->isNotEmpty())
                    <div style="border-top:1px solid var(--dt-border);padding-top:12px;margin-top:14px">
                        <span class="dt-meta-label" style="display:block;margin-bottom:8px">Client Social Accounts</span>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            @foreach($selectedSocialMediaLinks as $selectedLink)
                                @php $linkInfo = $selectedLink->getPlatformIcon(); @endphp
                                <a href="{{ $selectedLink->url }}" target="_blank" class="dt-platform-pill" style="font-size:11px">
                                    <i class="fa-brands {{ $linkInfo['icon'] }}" style="color:var(--dt-primary)"></i>
                                    <span>{{ $selectedLink->label ?: ucfirst($selectedLink->platform) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2 · STUDIO DISCUSSION & FEEDBACK STREAM --}}
            <div class="dt-card" style="margin-bottom:0">
                <div class="dt-card-header">
                    <div class="dt-card-header-left">
                        <div class="dt-card-icon"><i class="fa-solid fa-comments"></i></div>
                        <h2 class="dt-card-title">Discussion & Feedback</h2>
                        <span class="dt-badge-counter" id="commentCount">{{ $task->comments->count() }}</span>
                    </div>
                </div>

                <div class="dt-comments-wrap" id="commentsList">
                    @forelse($task->comments as $comment)
                        <div class="dt-comment-bubble">
                            <div class="dt-comment-bubble-header">
                                <div class="dt-av-sm" style="width:24px;height:24px;font-size:10px;background:{{ $comment->user->avatar_color ?? 'var(--dt-primary)' }}">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                                <div style="flex:1;min-width:0">
                                    <div class="dt-comment-author">{{ $comment->user->name }}</div>
                                    <div class="dt-comment-time">{{ $comment->created_at->diffForHumans() }}</div>
                                </div>
                                @if($comment->user->role !== 'designer')
                                    <span class="dt-comment-role-pill">{{ strtoupper($comment->user->role) }}</span>
                                @endif
                            </div>
                            <div class="dt-comment-body">
                                {{ $comment->body }}
                            </div>
                        </div>
                    @empty
                        <div class="no-comments-msg" style="text-align:center;padding:22px 10px;font-size:12px;color:var(--dt-text-muted)">
                            <i class="fa-regular fa-comment-dots" style="font-size:22px;display:block;margin-bottom:6px;color:var(--dt-border-sub)"></i>
                            No messages or comments on this deliverable yet.
                        </div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('designer.tasks.comment', $task) }}" id="commentForm" class="dt-comment-form">
                    @csrf
                    <input name="body" placeholder="Write feedback, question, or update..." class="dt-comment-input" required autocomplete="off">
                    <button type="submit" class="btn-dt-comment-submit" title="Send message">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </aside>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     5 · PAUSE TASK MODAL
     ══════════════════════════════════════════════ --}}
@if($task->status === 'inprogress' && !$task->is_paused)
<div id="pauseModal" class="dt-modal-overlay" style="display:none">
    <div class="dt-modal-card">
        <div class="dt-modal-header">
            <h3 class="dt-modal-title">⏸️ Pause Creative Task</h3>
            <button type="button" onclick="document.getElementById('pauseModal').style.display='none'" class="dt-modal-close">&times;</button>
        </div>
        <div class="dt-modal-body">
            <div style="padding:12px 14px;background:#FFFBEB;border:1px solid #FDE68A;border-radius:10px;font-size:12.5px;color:#92400E;line-height:1.5;margin-bottom:16px">
                <strong>"{{ $task->title }}"</strong> will be paused. Your active timer and session are safely preserved.
            </div>
            <form method="POST" action="{{ route('designer.tasks.pause', $task) }}" id="pauseForm">
                @csrf
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:11px;font-weight:800;color:var(--dt-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:5px">Accomplished So Far</label>
                    <textarea name="work_logged" placeholder="E.g. Completed initial design variations" rows="2" class="dt-comment-input" style="width:100%;resize:vertical"></textarea>
                </div>
                <div style="margin-bottom:16px">
                    <label style="display:block;font-size:11px;font-weight:800;color:var(--dt-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:5px">Pause Reason *</label>
                    <textarea name="reason" required placeholder="E.g. Waiting on client brand assets" rows="2" class="dt-comment-input" style="width:100%;resize:vertical"></textarea>
                </div>
                <div style="display:flex;gap:10px">
                    <button type="button" onclick="document.getElementById('pauseModal').style.display='none'" class="btn-dt-all-tasks" style="flex:1;justify-content:center;padding:9px">Cancel</button>
                    <button type="submit" class="btn-dt-action-start" style="flex:1;background:linear-gradient(135deg, #D97706, #B45309);justify-content:center;padding:9px">⏸️ Confirm Pause</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
/**
 * Open Deliverable full-screen interactive preview / Lightbox
 */
function openDeliverablePreview(e, url, title, client, isVideo, filename) {
    if (e && e.target && e.target.closest('.dt-touch-action-btn, .dt-media-action-btn, a, button')) {
        return;
    }
    if (typeof window.openMediaLightbox === 'function') {
        window.openMediaLightbox({
            url: url,
            title: title || 'Deliverable Preview',
            client: client || '',
            isVideo: !!isVideo,
            downloadUrl: url,
            filename: filename || 'deliverable'
        });
    } else {
        window.open(url, '_blank');
    }
}
window.openDeliverablePreview = openDeliverablePreview;

/**
 * Clear selected files from the upload tray
 */
function clearSelectedMedia() {
    const input = document.getElementById('mediaInput');
    if (input) input.value = '';
    const preview = document.getElementById('mediaPreview');
    if (preview) preview.style.display = 'none';
    const fileList = document.getElementById('fileList');
    if (fileList) fileList.innerHTML = '';
    const form = document.getElementById('mediaUploadForm');
    if (form) form.dataset.uploading = 'false';
}
window.clearSelectedMedia = clearSelectedMedia;

/**
 * Handle media file selection and display live visual previews
 */
function handleMediaSelect() {
    const input = document.getElementById('mediaInput');
    const preview = document.getElementById('mediaPreview');
    const fileList = document.getElementById('fileList');
    const uploadBtn = document.getElementById('mediaUploadBtn');
    const countEl = document.getElementById('selectedFileCount');

    if (input.files && input.files.length > 0) {
        fileList.innerHTML = '';
        preview.style.display = 'block';
        if (uploadBtn) {
            uploadBtn.style.display = 'inline-flex';
            uploadBtn.disabled = false;
        }
        if (countEl) {
            countEl.textContent = `${input.files.length} deliverable file${input.files.length > 1 ? 's' : ''} ready`;
        }

        Array.from(input.files).forEach((file) => {
            const item = document.createElement('div');
            item.className = 'preview-item';
            item.style.cssText = 'position:relative;aspect-ratio:1/1;border-radius:10px;overflow:hidden;border:1px solid var(--dt-border);background:#fff;display:inline-block;width:76px;margin-right:8px;margin-bottom:8px;box-shadow:0 1px 4px rgba(0,0,0,0.06)';

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    item.innerHTML = `
                        <img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover" alt="Preview">
                        <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(15,23,42,0.75);color:#fff;font-size:8.5px;padding:2px;text-align:center;font-weight:700">${(file.size / 1024 / 1024).toFixed(1)}MB</div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                item.innerHTML = `
                    <div style="display:flex;align-items:center;justify-content:center;height:100%;background:#F1F5F9;color:var(--dt-primary);font-size:20px">
                        <i class="fa-solid fa-file"></i>
                    </div>
                    <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(15,23,42,0.75);color:#fff;font-size:8.5px;padding:2px;text-align:center;font-weight:700">${(file.size / 1024 / 1024).toFixed(1)}MB</div>
                `;
            }

            fileList.appendChild(item);
        });

        preview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        preview.style.display = 'none';
        if (uploadBtn) uploadBtn.style.display = 'none';
    }
}

function triggerDeliverableUpload() {
    const colInput = document.getElementById('uploadCollectionName');
    if (colInput) colInput.value = 'task-media';
    const heading = document.getElementById('uploadTypeHeading');
    if (heading) heading.textContent = 'Selected deliverable for upload:';
    document.getElementById('mediaInput')?.click();
}
window.triggerDeliverableUpload = triggerDeliverableUpload;

function updateSubmitButtonState() {
    const deliverableContainer = document.getElementById('deliverablesPreviewContainer');
    const deliverableCount = deliverableContainer ? deliverableContainer.querySelectorAll('.dt-media-card:not(.dt-media-add-card)').length : 0;
    const lockMsg = document.querySelector('.submit-lock-msg');
    const tabCount = document.getElementById('deliverableTabCount');

    if (tabCount) tabCount.textContent = deliverableCount;
    if (lockMsg) {
        lockMsg.style.display = deliverableCount === 0 ? 'flex' : 'none';
    }
}
window.updateSubmitButtonState = updateSubmitButtonState;

document.addEventListener('DOMContentLoaded', function() {
    updateSubmitButtonState();

    const mediaUploadForm = document.getElementById('mediaUploadForm');
    if (mediaUploadForm && !mediaUploadForm.dataset.listenerBound) {
        mediaUploadForm.dataset.listenerBound = 'true';
        mediaUploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            uploadMediaAJAX(this);
        });
    }

    const submitForm = document.querySelector('#submitForm');
    if (submitForm && !submitForm.dataset.listenerBound) {
        submitForm.dataset.listenerBound = 'true';
        submitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitTaskForReviewAJAX(this);
        });
    }

    const commentForm = document.getElementById('commentForm');
    if (commentForm && !commentForm.dataset.listenerBound) {
        commentForm.dataset.listenerBound = 'true';
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitCommentAJAX(this);
        });
    }
});

function showLoadingOverlay(btn, text = 'Loading...') {
    if (!btn) return;
    btn.dataset.originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${text}`;
}

function hideLoadingOverlay(btn) {
    if (!btn) return;
    btn.disabled = false;
    btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
}

async function deleteMediaAJAX(mediaId, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    if (!confirm('Permanently remove this deliverable?')) return;

    const btn = event ? event.target.closest('button') : null;
    const mediaContainer = event ? event.target.closest('.dt-media-card') : null;

    try {
        if (btn) showLoadingOverlay(btn, '');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(`{{ route('designer.tasks.delete-media', $task) }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ media_id: mediaId }),
        });

        if (!response.ok) throw new Error('Delete failed');

        if (mediaContainer) {
            mediaContainer.style.opacity = '0';
            mediaContainer.style.transform = 'scale(0.9)';
            setTimeout(() => {
                mediaContainer.remove();
                updateSubmitButtonState();
            }, 250);
        }

        if (window.ajax && window.ajax.showSuccess) {
            window.ajax.showSuccess('Deliverable removed');
        }

    } catch (error) {
        if (window.ajax && window.ajax.showError) {
            window.ajax.showError('Could not delete deliverable');
        } else {
            alert('Could not delete deliverable');
        }
    } finally {
        if (btn) hideLoadingOverlay(btn);
    }
}
window.deleteMediaAJAX = deleteMediaAJAX;

async function uploadMediaAJAX(form) {
    if (!form || form.dataset.uploading === 'true') {
        return; // Prevent duplicate concurrent requests
    }

    const input = document.getElementById('mediaInput');
    if (!input || !input.files || input.files.length === 0) {
        if (window.ajax && window.ajax.showError) {
            window.ajax.showError('Please select at least one file to upload.');
        } else {
            alert('Please select at least one file to upload.');
        }
        return;
    }

    form.dataset.uploading = 'true';
    const btn = form.querySelector('button[type="submit"]') || document.getElementById('mediaUploadBtn');

    try {
        if (btn) {
            btn.disabled = true;
            showLoadingOverlay(btn, 'Uploading...');
        }

        // Construct FormData explicitly with all selected files
        const formData = new FormData();
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('input[name="_token"]')?.value;
        const colInput = document.getElementById('uploadCollectionName');
        
        formData.append('_token', csrfToken);
        formData.append('collection_name', colInput ? colInput.value : 'task-media');

        for (let i = 0; i < input.files.length; i++) {
            formData.append('media[]', input.files[i]);
        }

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            let errorMsg = data.message || 'Upload failed';
            if (data.errors) {
                const firstKey = Object.keys(data.errors)[0];
                if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
                    errorMsg = data.errors[firstKey][0];
                }
            }
            throw new Error(errorMsg);
        }

        if (window.ajax && window.ajax.showSuccess) {
            window.ajax.showSuccess(data.message || 'Deliverables uploaded successfully');
        }

        form.reset();
        const preview = document.getElementById('mediaPreview');
        if (preview) preview.style.display = 'none';

        setTimeout(() => location.reload(), 700);

    } catch (error) {
        form.dataset.uploading = 'false';
        if (btn) hideLoadingOverlay(btn);

        if (window.ajax && window.ajax.showError) {
            window.ajax.showError(error.message || 'Upload failed. Check file size.');
        } else {
            alert(error.message || 'Upload failed. Check file size.');
        }
    }
}

async function submitTaskForReviewAJAX(form) {
    const deliverableContainer = document.getElementById('deliverablesPreviewContainer');
    const deliverableCount = deliverableContainer ? deliverableContainer.querySelectorAll('.dt-media-card').length : 0;

    if (deliverableCount === 0) {
        Swal.fire({
            title: 'Deliverable Required',
            text: 'You must upload at least one design image or video deliverable before submitting this task for review.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4F46E5',
            cancelButtonColor: '#64748B',
            confirmButtonText: '<i class="fa-solid fa-cloud-arrow-up"></i> Upload Now',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                triggerDeliverableUpload();
            }
        });
        return;
    }

    const confirmResult = await Swal.fire({
        title: 'Submit for Review?',
        text: 'Are you sure you want to submit your design deliverables to the strategist and client for review?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Yes, Submit for Review',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    });
    if (!confirmResult.isConfirmed) return;

    const btn = form.querySelector('button[type="submit"]');
    const isUrgentTask = @json((bool) $task->is_urgent_task);

    try {
        if (btn) showLoadingOverlay(btn, 'Submitting...');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'Submission failed');
        }

        Swal.fire({
            title: 'Submitted!',
            text: data.message || (isUrgentTask ? 'Urgent deliverable submitted for review!' : 'Task submitted for review!'),
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });

        setTimeout(() => location.reload(), 1200);

    } catch (error) {
        Swal.fire({
            title: 'Submission Blocked',
            text: error.message || 'Failed to submit task.',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    } finally {
        if (btn) hideLoadingOverlay(btn);
    }
}

async function submitCommentAJAX(form) {
    const input = form.querySelector('input[name="body"]');
    const btn = form.querySelector('button[type="submit"]');
    if (!input || !input.value.trim()) return;

    const origText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    }

    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        if (!response.ok) throw new Error('Comment failed');

        const data = await response.json();
        if (window.ajax && window.ajax.showSuccess) {
            window.ajax.showSuccess('Message sent');
        }
        form.reset();
        addCommentToList(data);

    } catch (error) {
        if (window.ajax && window.ajax.showError) {
            window.ajax.showError('Could not post message');
        } else {
            alert('Could not post message');
        }
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origText;
        }
    }
}

function addCommentToList(data) {
    const list = document.getElementById('commentsList');
    if (!list) return;

    const noMsg = list.querySelector('.no-comments-msg');
    if (noMsg) noMsg.remove();

    const roleTag = data.user_role !== 'designer'
        ? `<span class="dt-comment-role-pill">${escapeHtml(data.user_role.toUpperCase())}</span>`
        : '';

    const html = `
        <div class="dt-comment-bubble" style="animation: dtFadeIn 0.3s ease-out">
            <div class="dt-comment-bubble-header">
                <div class="dt-av-sm" style="width:24px;height:24px;font-size:10px;background:${data.user_color || 'var(--dt-primary)'}">
                    ${escapeHtml(data.user_initial)}
                </div>
                <div style="flex:1;min-width:0">
                    <div class="dt-comment-author">${escapeHtml(data.user_name)}</div>
                    <div class="dt-comment-time">Just now</div>
                </div>
                ${roleTag}
            </div>
            <div class="dt-comment-body">
                ${escapeHtml(data.content)}
            </div>
        </div>
    `;

    list.insertAdjacentHTML('beforeend', html);

    const countEl = document.getElementById('commentCount');
    if (countEl) countEl.textContent = parseInt(countEl.textContent || 0) + 1;

    list.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
