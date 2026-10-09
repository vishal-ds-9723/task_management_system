@extends('layouts.app')

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
@push('styles')
    <link href="{{ asset('css/task-detail.css') }}?v=4.0" rel="stylesheet">
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pages/designer-theme.css') }}?v=4.0" rel="stylesheet">
@endpush

<div class="task-minimal">
    {{-- ═══ TOP STUDIO HEADER STRIP ═══ --}}
    <header class="topbar">
        <div>
            <nav class="breadcrumb">
                <a href="{{ route('designer.tasks') }}" class="btn-sec" style="padding:3px 9px !important;font-size:11px !important">
                    <i class="fa-solid fa-arrow-left"></i> My Tasks
                </a>
                <span class="breadcrumb-sep">/</span>
                <span style="font-weight:700;color:var(--text-sub);font-size:11px">#TSK-{{ str_pad($task->id, 4, '0', STR_PAD_LEFT) }}</span>
            </nav>
            <div style="display:flex;align-items:center;gap:10px;margin-top:4px;flex-wrap:wrap">
                <x-client-branding :client="$task->client" size="24px" radius="5px" fit="contain" />
                <h1 class="page-title">{{ $task->title }}</h1>
                <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                @if($task->priority === 'urgent')
                    <span class="tag tag-red" style="font-size:9.5px;padding:1px 5px">URGENT</span>
                @endif
                @if($task->revision_count > 0)
                    <span class="tag tag-red" style="font-size:9.5px;padding:1px 5px">Rev #{{ $task->revision_count }}</span>
                @endif
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            @if($task->status === 'inprogress' && $task->started_at)
                @php
                    $elapsed = $task->started_at->diff(now());
                    $elapsedStr = '';
                    if ($elapsed->d > 0) $elapsedStr .= $elapsed->d . 'd ';
                    if ($elapsed->h > 0) $elapsedStr .= $elapsed->h . 'h ';
                    $elapsedStr .= $elapsed->i . 'm';
                @endphp
                <div style="display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:700;background:var(--card-bg);padding:5px 10px;border-radius:var(--radius-sm);border:1px solid var(--border)">
                    <span class="ds-pulse-dot" style="width:6px;height:6px"></span>
                    <span>{{ trim($elapsedStr) }} active</span>
                </div>
            @endif

            <a href="{{ route('designer.tasks') }}" class="btn-sec" style="font-size:12px !important;padding:5px 10px !important">
                <i class="fa-solid fa-list-check" style="font-size:11px"></i> All Tasks
            </a>
        </div>
    </header>

    {{-- ═══ SLIM WORKFLOW STEPPER ═══ --}}
    @if(!empty($workflowSteps))
    <div class="wf-progress-bar">
        @foreach($workflowSteps as $i => $step)
            <div class="wf-step {{ $step['done'] ? 'wf-done' : '' }} {{ $step['current'] ?? false ? 'wf-current' : '' }}">
                <div class="wf-step-icon">
                    @if($step['done'])
                        <i class="fas fa-check"></i>
                    @else
                        {!! is_numeric($step['icon']) ? $step['icon'] : $step['icon'] !!}
                    @endif
                </div>
                <div class="wf-step-info">
                    <div class="wf-step-label">{{ $step['label'] }}</div>
                    @if($step['time'])
                        <div class="wf-step-time">{{ $step['time']->format('d M, h:i A') }}</div>
                    @elseif($step['current'] ?? false)
                        <div class="wf-step-time" style="color:var(--primary);font-weight:700">Current Phase</div>
                    @endif
                </div>
            </div>
            @if($i < count($workflowSteps) - 1)
                <div class="wf-connector {{ $step['done'] ? 'wf-conn-done' : '' }}"></div>
            @endif
        @endforeach
    </div>
    @endif

    {{-- ═══ SLIM ALERT BANNERS ═══ --}}
    @if($task->is_paused)
        <div class="wf-status-bar" style="background:#FFFBEB;border-left:3px solid #D97706;color:#92400E">
            <div style="display:flex;align-items:center;gap:10px">
                <span>⏸️</span>
                <div>
                    <span style="font-weight:800;font-size:13px">Task is paused</span>
                    <span style="font-size:11.5px;opacity:0.85;margin-left:6px">({{ $task->pause_reason }})</span>
                </div>
            </div>
            <form method="POST" action="{{ route('designer.tasks.resume', $task) }}" style="margin:0">
                @csrf
                <button type="submit" class="btn-action-primary" style="background:#059669 !important;border-color:#059669 !important;padding:4px 12px !important;font-size:12px !important">
                    <i class="fa-solid fa-play"></i> Resume
                </button>
            </form>
        </div>
    @elseif($task->status === 'review')
        <div class="wf-status-bar wf-review-bar">
            <div style="display:flex;align-items:center;gap:8px">
                <i class="fas fa-hourglass-half" style="color:var(--warning)"></i>
                <span>Awaiting Strategist & Client Review</span>
            </div>
            <span style="font-size:12px;color:var(--text-muted)">Submitted deliverables are pending approval</span>
        </div>
    @endif

    {{-- ═══ BALANCED TWO-COLUMN WORKSPACE (ZERO ENDLESS SCROLL) ═══ --}}
    <div class="two-col">
        {{-- ── COLUMN 1: CREATIVE WORKSPACE (LEFT 58%) ── --}}
        <main>
            {{-- 1 · CREATIVE BRIEF & SPECIFICATIONS CARD --}}
            <div class="card">
                <div class="sec-header">
                    <div class="sec-header-left">
                        <div class="sec-icon"><i class="fa-solid fa-file-lines"></i></div>
                        <h2 class="sec-title">Creative Brief & Content Specs</h2>
                    </div>
                    <span class="tag tag-primary" style="font-size:10.5px;padding:2px 7px">{{ ucfirst($task->type) }}</span>
                </div>

                {{-- Compact Metadata Strip --}}
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Client</span>
                        <div class="detail-value" style="display:flex;align-items:center;gap:6px">
                            <x-client-branding :client="$task->client" size="18px" radius="4px" fit="contain" />
                            <span>{{ $task->client->name }}</span>
                        </div>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Target Platforms</span>
                        <div style="display:flex;gap:4px;flex-wrap:wrap">
                            @if(!empty($taskPlatforms))
                                @foreach($taskPlatforms as $platform)
                                    <span class="platform-pill" style="font-size:10px;padding:1px 5px">
                                        @if(in_array($platform, ['instagram', 'facebook', 'linkedin', 'twitter', 'whatsapp']))
                                            <i class="fa-brands fa-{{ $platform === 'twitter' ? 'x-twitter' : $platform }}"></i>
                                        @endif
                                        {{ ucfirst($platform) }}
                                    </span>
                                @endforeach
                            @else
                                <span style="font-size:12px;color:var(--text-muted)">Standard</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Brief / Content Copy (Capped Height to Prevent Vertical Bloat) --}}
                @if($task->is_urgent_task && $urgentBrief !== '')
                    <div class="task-brief" style="border-left:3px solid #DC2626;background:#FEF2F2">
                        <div class="task-brief-label" style="color:#DC2626">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Urgent Creative Brief</span>
                        </div>
                        <div class="task-brief-body" style="color:#991B1B">{{ $urgentBrief }}</div>
                    </div>
                @endif

                @if($task->caption && (!$task->is_urgent_task || $showUrgentCaption))
                    <div class="task-brief">
                        <div class="task-brief-label">
                            <i class="fa-solid fa-align-left"></i>
                            <span>Post Caption & Copy</span>
                        </div>
                        <div class="task-brief-body">{{ $task->caption }}</div>
                    </div>
                @endif

                @if($task->brief && !$task->is_urgent_task)
                    <div class="task-brief">
                        <div class="task-brief-label">
                            <i class="fa-solid fa-pen-nib"></i>
                            <span>Detailed Creative Brief</span>
                        </div>
                        <div class="task-brief-body">{{ $task->brief }}</div>
                    </div>
                @endif

                {{-- Reference Links --}}
                @if(!empty($task->reference_links))
                    <div style="margin-top:10px">
                        <span class="detail-label" style="display:block;margin-bottom:6px">Reference Materials</span>
                        <div style="display:flex;flex-wrap:wrap;gap:6px">
                            @foreach((is_array($task->reference_links) ? $task->reference_links : json_decode($task->reference_links, true)) ?? [] as $link)
                                @if(filter_var($link, FILTER_VALIDATE_URL))
                                    <a href="{{ $link }}" target="_blank" class="btn-sec" style="font-size:11.5px;padding:4px 10px">
                                        <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px"></i>
                                        {{ Str::limit(str_replace(['https://','www.'],'',$link), 22) }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 📥 RECEIVED REFERENCE ASSETS & CLIENT MATERIALS --}}
                <div style="margin-top:16px;padding-top:14px;border-top:1px dashed var(--border)">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px">
                        <div style="display:flex;align-items:center;gap:7px">
                            <i class="fa-solid fa-folder-open" style="color:var(--primary);font-size:12px"></i>
                            <span class="detail-label" style="margin:0;font-size:11px;font-weight:700">Received Reference Assets & Client Materials</span>
                            <span class="task-tab-count" style="font-size:10px;padding:1px 6px;background:rgba(59,130,246,0.12);color:#2563EB">{{ $referenceMedia->count() }}</span>
                        </div>
                        @if(empty($isReadOnly))
                            <button type="button" onclick="triggerReferenceUpload()" class="btn-sec" style="font-size:10.5px !important;padding:2px 8px !important">
                                <i class="fa-solid fa-plus"></i> Add Reference File
                            </button>
                        @endif
                    </div>

                    @if($referenceMedia->isNotEmpty())
                        <div class="media-grid" style="grid-template-columns:repeat(auto-fill, minmax(130px, 1fr));gap:10px">
                            @foreach($referenceMedia as $refItem)
                                <div class="media-item" style="border:1.5px solid rgba(59,130,246,0.3);border-radius:10px;overflow:hidden">
                                    @if($refItem->isVideo())
                                        <video class="media-file" muted preload="metadata" style="background:#0F172A;object-fit:cover"><source src="{{ $refItem->getUrl() }}"></video>
                                        <div style="position:absolute;top:6px;left:6px;background:rgba(15,23,42,0.75);color:#fff;font-size:9px;font-weight:700;padding:2px 5px;border-radius:3px;z-index:2">
                                            <i class="fa-solid fa-video"></i> Video
                                        </div>
                                    @elseif($refItem->isPdf())
                                        <div style="height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--bg-main)">
                                            <i class="fa-solid fa-file-pdf" style="font-size:24px;color:#DC2626"></i>
                                        </div>
                                        <div style="position:absolute;top:6px;left:6px;background:rgba(220,38,38,0.85);color:#fff;font-size:9px;font-weight:700;padding:2px 5px;border-radius:3px;z-index:2">
                                            <i class="fa-solid fa-file-pdf"></i> PDF
                                        </div>
                                    @elseif($refItem->isImage())
                                        <img src="{{ $refItem->getUrl() }}" class="media-file" loading="lazy" alt="Reference image">
                                    @else
                                        <div style="height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--bg-main)">
                                            <i class="fa-solid fa-file" style="font-size:24px;color:var(--text-muted)"></i>
                                        </div>
                                    @endif

                                    {{-- Reference Badge --}}
                                    <div style="position:absolute;bottom:6px;left:6px;right:6px;background:rgba(15,23,42,0.82);color:#fff;font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;z-index:2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        <i class="fa-solid fa-paperclip" style="color:#60A5FA"></i> {{ $refItem->getVersionBadgeLabel() }}
                                    </div>

                                    {{-- Hover Actions Overlay --}}
                                    <div class="media-overlay">
                                        <a href="{{ $refItem->getUrl() }}" target="_blank" class="media-btn" title="View Fullscreen">
                                            <i class="fas fa-expand"></i>
                                        </a>
                                        <a href="{{ $refItem->getUrl() }}" download="{{ $refItem->name }}" class="media-btn" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if(empty($isReadOnly))
                                            <button type="button" onclick="deleteMediaAJAX({{ $refItem->id }}, event)" class="media-btn btn-danger" title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="padding:10px 14px;background:var(--card2);border:1px dashed var(--border);border-radius:8px;font-size:11.5px;color:var(--text-muted);display:flex;align-items:center;gap:8px">
                            <i class="fa-regular fa-image" style="opacity:0.5;font-size:14px"></i>
                            <span>No reference assets attached yet. Reference files provided by strategist/client appear here.</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2 · DELIVERABLES & DESIGN OUTPUTS STUDIO (HERO) --}}
            <div class="card" id="deliverablesSection" style="margin-bottom:0">
                <div class="sec-header">
                    <div class="sec-header-left">
                        <div class="sec-icon"><i class="fa-solid fa-paintbrush"></i></div>
                        <div>
                            <h2 class="sec-title" style="margin:0">Deliverables & Design Outputs</h2>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:2px">Upload your completed design files or revisions for review</div>
                        </div>
                        <span class="task-tab-count" id="deliverableTabCount" style="margin-left:6px">{{ $deliverableMedia->count() }}</span>
                    </div>

                    @if($deliverableMedia->count() > 0 && empty($isReadOnly))
                        <button type="button" onclick="triggerDeliverableUpload()" class="btn-sec" style="font-size:11.5px !important;padding:4px 10px !important">
                            <i class="fa-solid fa-plus"></i> Upload New Version
                        </button>
                    @endif
                </div>

                {{-- Upload Dropzone if 0 deliverables --}}
                @if($deliverableMedia->count() === 0 && empty($isReadOnly))
                    <div class="upload-zone" id="emptyUploadZone" onclick="triggerDeliverableUpload()">
                        <div class="upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                        <div style="font-size:13.5px;font-weight:700;color:var(--text-main)">Upload design deliverable</div>
                        <p style="font-size:11.5px;color:var(--text-muted);margin:0">Click to attach finished creative (PNG, JPG, MP4, PDF)</p>
                    </div>
                @endif

                {{-- Uploaded Deliverables Grid --}}
                <div class="media-grid" id="deliverablesPreviewContainer">
                    @foreach($deliverableMedia as $item)
                        <div class="media-item" style="border:2px solid {{ $item->getRevisionCount() > 0 ? '#F59E0B' : 'var(--border)' }};border-radius:10px">
                            @if($item->isVideo())
                                <video class="media-file" muted preload="metadata" style="background:#0F172A;object-fit:cover"><source src="{{ $item->getUrl() }}"></video>
                                <div style="position:absolute;top:6px;left:6px;background:rgba(15,23,42,0.75);color:#fff;font-size:9.5px;font-weight:700;padding:2px 5px;border-radius:3px;z-index:2">
                                    <i class="fa-solid fa-video"></i> Video
                                </div>
                            @elseif($item->isPdf())
                                <div style="height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--bg-main)">
                                    <i class="fa-solid fa-file-pdf" style="font-size:26px;color:#DC2626"></i>
                                </div>
                                <div style="position:absolute;top:6px;left:6px;background:rgba(220,38,38,0.85);color:#fff;font-size:9.5px;font-weight:700;padding:2px 5px;border-radius:3px;z-index:2">
                                    <i class="fa-solid fa-file-pdf"></i> PDF
                                </div>
                            @elseif($item->isImage())
                                <img src="{{ $item->getUrl() }}" class="media-file" loading="lazy" alt="Design preview">
                            @else
                                <div style="height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--bg-main)">
                                    <i class="fa-solid fa-file" style="font-size:26px;color:var(--text-muted)"></i>
                                </div>
                            @endif

                            {{-- Version Badge (v1 Initial vs Revision #1) --}}
                            <div style="position:absolute;bottom:6px;left:6px;right:6px;background:{{ $item->getRevisionCount() > 0 ? 'rgba(217,119,6,0.92)' : 'rgba(16,185,129,0.92)' }};color:#fff;font-size:9.5px;font-weight:700;padding:3px 7px;border-radius:4px;z-index:2;display:flex;align-items:center;justify-content:space-between">
                                <span>{{ $item->getVersionBadgeLabel() }}</span>
                                <span style="font-size:8.5px;opacity:0.9">{{ $item->created_at->format('d M') }}</span>
                            </div>

                            {{-- Hover Actions Overlay --}}
                            <div class="media-overlay">
                                <a href="{{ $item->getUrl() }}" target="_blank" class="media-btn" title="View Fullscreen">
                                    <i class="fas fa-expand"></i>
                                </a>
                                <a href="{{ $item->getUrl() }}" download="{{ $item->name }}" class="media-btn" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                @if(empty($isReadOnly))
                                    <button type="button" onclick="deleteMediaAJAX({{ $item->id }}, event)" class="media-btn btn-danger" title="Remove">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Hidden Form & File Selection --}}
                @if(empty($isReadOnly))
                <form method="POST" action="{{ route('designer.tasks.upload-media', $task) }}" enctype="multipart/form-data" id="mediaUploadForm" style="margin-top:12px">
                    @csrf
                    <input type="hidden" name="collection_name" id="uploadCollectionName" value="task-media">
                    <input type="file" name="media[]" id="mediaInput" accept="image/*,video/*,.pdf,.doc,.docx,.zip,.rar" multiple style="display:none" onchange="handleMediaSelect()">
                    
                    <div id="mediaPreview" style="display:none;margin-top:12px;padding:12px;background:var(--primary-light);border-radius:var(--radius-md);border:1px solid var(--primary-border)">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                            <div id="uploadTypeHeading" style="font-size:10.5px;font-weight:800;color:var(--primary);text-transform:uppercase;letter-spacing:0.06em">Selected deliverable for upload:</div>
                            <button type="submit" id="mediaUploadBtn" class="btn-action-primary" style="padding:4px 12px !important;font-size:11.5px !important">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Now
                            </button>
                        </div>
                        <div id="fileList"></div>
                    </div>
                </form>
                @endif
            </div>
        </main>

        {{-- ── COLUMN 2: ACTIONS & FEEDBACK (RIGHT 42%) ── --}}
        <aside class="action-card">
            {{-- 1 · WORKFLOW ACTION & PROPERTIES CARD --}}
            <div class="card">
                <div class="sec-header">
                    <div class="sec-header-left">
                        <div class="sec-icon"><i class="fa-solid fa-sliders"></i></div>
                        <h2 class="sec-title">Status & Actions</h2>
                    </div>
                </div>
                
                {{-- Phase badge & Submit Button --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                    <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                    <span style="font-size:11.5px;font-weight:700;color:{{ $task->isOverdue() ? '#DC2626' : 'var(--text-muted)' }}">
                        Due {{ $task->deadline?->format('d M') ?? 'Flexible' }}
                    </span>
                </div>

                @if($task->status === 'inprogress' && !$task->is_paused && empty($isReadOnly))
                    @if($task->revision_count > 0)
                        <div class="rev-card">
                            <div class="rev-icon"><i class="fa-solid fa-rotate-left"></i></div>
                            <div>
                                <div class="rev-title">Revision #{{ $task->revision_count }}</div>
                                <div class="rev-meta">Review notes before submitting</div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('designer.tasks.submit', $task) }}" id="submitForm">
                        @csrf
                        <button type="submit" class="wf-submit-btn" id="submitBtn" {{ $deliverableMedia->count() === 0 ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane"></i> Submit for Review
                        </button>
                        @if($deliverableMedia->count() === 0)
                            <p class="submit-lock-msg" style="font-size:11px;color:#DC2626;text-align:center;margin-top:6px;font-weight:600">
                                <i class="fas fa-lock"></i> Upload a deliverable first to submit
                            </p>
                        @endif
                    </form>

                    {{-- Pause Button --}}
                    <button type="button" onclick="document.getElementById('pauseModal').style.display='flex'" class="btn-sec" style="margin-top:8px;width:100%;justify-content:center;color:#D97706;border-color:rgba(217,119,6,0.3);font-size:12px !important;padding:6px 12px !important">
                        ⏸️ Pause Task
                    </button>
                @elseif($task->is_paused && empty($isReadOnly))
                    <form method="POST" action="{{ route('designer.tasks.resume', $task) }}">
                        @csrf
                        <button type="submit" class="btn-action-primary" style="width:100%;background:#059669 !important;border-color:#059669 !important;padding:8px !important">
                            ▶️ Resume & Continue Working
                        </button>
                    </form>
                @endif

                {{-- Compact Properties Row --}}
                <div style="border-top:1px solid var(--border);padding-top:10px;margin-top:12px;display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px">
                    <div>
                        <span class="detail-label">Strategist</span>
                        <div style="font-weight:600;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $task->creator->name ?? 'Admin' }}
                        </div>
                    </div>
                    <div>
                        <span class="detail-label">Creative</span>
                        <div style="font-weight:600;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $task->assignee->name ?? 'Me' }}
                        </div>
                    </div>
                </div>

                {{-- Client Social Links (if any) --}}
                @if(($selectedSocialMediaLinks ?? collect())->isNotEmpty())
                    <div style="border-top:1px solid var(--border);padding-top:10px;margin-top:10px">
                        <span class="detail-label" style="display:block;margin-bottom:6px">Client Socials</span>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            @foreach($selectedSocialMediaLinks as $selectedLink)
                                @php $linkInfo = $selectedLink->getPlatformIcon(); @endphp
                                <a href="{{ $selectedLink->url }}" target="_blank" class="btn-sec" style="padding:4px 8px;font-size:11px">
                                    <i class="fa-brands {{ $linkInfo['icon'] }}" style="color:var(--primary)"></i>
                                    <span>{{ $selectedLink->label ?: ucfirst($selectedLink->platform) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2 · DISCUSSION & FEEDBACK STREAM CARD (CAPPED HEIGHT) --}}
            <div class="card" style="margin-bottom:0">
                <div class="sec-header">
                    <div class="sec-header-left">
                        <div class="sec-icon"><i class="fa-solid fa-comments"></i></div>
                        <h2 class="sec-title">Discussion & Feedback</h2>
                        <span class="task-tab-count" id="commentCount">{{ $task->comments->count() }}</span>
                    </div>
                </div>

                <div class="comments-list" id="commentsList">
                    @forelse($task->comments as $comment)
                        <div class="comment-item">
                            <div class="comment-header">
                                <div class="av-sm" style="background:{{ $comment->user->avatar_color ?? 'var(--primary)' }}">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                                <div style="flex:1">
                                    <div style="font-weight:700;font-size:12px;color:var(--text-main)">{{ $comment->user->name }}</div>
                                    <div style="font-size:10px;color:var(--text-muted)">{{ $comment->created_at->diffForHumans() }}</div>
                                </div>
                                @if($comment->user->role !== 'designer')
                                    <span class="tag tag-primary" style="font-size:8.5px;padding:1px 5px">{{ strtoupper($comment->user->role) }}</span>
                                @endif
                            </div>
                            <div style="font-size:12.5px;line-height:1.45;color:var(--text-main);padding-left:30px">
                                {{ $comment->body }}
                            </div>
                        </div>
                    @empty
                        <div class="no-comments-msg" style="text-align:center;padding:16px 8px;font-size:12px;color:var(--text-muted)">
                            <i class="fa-regular fa-comment-dots" style="font-size:18px;display:block;margin-bottom:4px"></i>
                            No messages or revision notes yet.
                        </div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('designer.tasks.comment', $task) }}" id="commentForm" style="margin-top:10px;display:flex;gap:6px">
                    @csrf
                    <input name="body" placeholder="Write feedback, revision reply..." class="filter-input" style="flex:1;padding:6px 10px !important;font-size:12px !important" required autocomplete="off">
                    <button type="submit" class="btn-action-primary" style="padding:0 12px !important;font-size:11.5px !important">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
/**
 * Handle media file selection and display visual preview
 */
function handleMediaSelect() {
    const input = document.getElementById('mediaInput');
    const preview = document.getElementById('mediaPreview');
    const fileList = document.getElementById('fileList');
    const uploadBtn = document.getElementById('mediaUploadBtn');

    if (input.files && input.files.length > 0) {
        fileList.innerHTML = '';
        preview.style.display = 'block';
        if (uploadBtn) uploadBtn.style.display = 'inline-flex';

        Array.from(input.files).forEach((file) => {
            const item = document.createElement('div');
            item.className = 'preview-item';
            item.style.cssText = 'position:relative;aspect-ratio:1/1;border-radius:6px;overflow:hidden;border:1px solid var(--border);background:#fff;display:inline-block;width:75px;margin-right:6px;margin-bottom:6px';

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    item.innerHTML = `
                        <img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover">
                        <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.6);color:#fff;font-size:8px;padding:2px;text-align:center;font-weight:700">${(file.size / 1024 / 1024).toFixed(1)}MB</div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                item.innerHTML = `
                    <div style="display:flex;align-items:center;justify-content:center;height:100%;background:#F1F5F9;color:var(--primary);font-size:18px">
                        <i class="fas fa-file"></i>
                    </div>
                    <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.6);color:#fff;font-size:8px;padding:2px;text-align:center;font-weight:700">${(file.size / 1024 / 1024).toFixed(1)}MB</div>
                `;
            }

            fileList.appendChild(item);
        });
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

function triggerReferenceUpload() {
    const colInput = document.getElementById('uploadCollectionName');
    if (colInput) colInput.value = 'task-reference';
    const heading = document.getElementById('uploadTypeHeading');
    if (heading) heading.textContent = 'Selected reference asset for upload:';
    document.getElementById('mediaInput')?.click();
}
window.triggerReferenceUpload = triggerReferenceUpload;

function updateSubmitButtonState() {
    const deliverableContainer = document.getElementById('deliverablesPreviewContainer');
    const deliverableCount = deliverableContainer ? deliverableContainer.querySelectorAll('.media-item').length : 0;
    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.disabled = deliverableCount === 0;
    }
}
window.updateSubmitButtonState = updateSubmitButtonState;

document.addEventListener('DOMContentLoaded', function() {
    updateSubmitButtonState();

    const mediaUploadForm = document.getElementById('mediaUploadForm');
    if (mediaUploadForm) {
        mediaUploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            uploadMediaAJAX(this);
        });
    }

    const submitForm = document.querySelector('#submitForm');
    if (submitForm) {
        submitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitTaskForReviewAJAX(this);
        });
    }

    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
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
    if (event) event.preventDefault();
    if (!confirm('Permanently remove this deliverable?')) return;

    const btn = event ? event.target.closest('button') : null;
    const mediaContainer = event ? event.target.closest('.media-item') : null;

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
            setTimeout(() => mediaContainer.remove(), 250);
        }

        if (window.ajax && window.ajax.showSuccess) {
            window.ajax.showSuccess('File removed');
        }
        updateSubmitButtonState();

        const tabCountEl = document.getElementById('mediaTabCount');
        if (tabCountEl) tabCountEl.textContent = Math.max(0, parseInt(tabCountEl.textContent || 0) - 1);

    } catch (error) {
        if (window.ajax && window.ajax.showError) {
            window.ajax.showError('Could not delete file');
        } else {
            alert('Could not delete file');
        }
    } finally {
        if (btn) hideLoadingOverlay(btn);
    }
}
window.deleteMediaAJAX = deleteMediaAJAX;

async function uploadMediaAJAX(form) {
    const input = document.getElementById('mediaInput');
    if (!input.files || input.files.length === 0) return;

    const btn = form.querySelector('button[type="submit"]');

    try {
        if (btn) showLoadingOverlay(btn, 'Uploading...');
        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        });

        if (!response.ok) throw new Error('Upload failed');

        if (window.ajax && window.ajax.showSuccess) {
            window.ajax.showSuccess('Designs uploaded');
        }

        setTimeout(() => location.reload(), 900);

    } catch (error) {
        if (window.ajax && window.ajax.showError) {
            window.ajax.showError('Upload failed. Check file size.');
        } else {
            alert('Upload failed. Check file size.');
        }
    } finally {
        if (btn) hideLoadingOverlay(btn);
    }
}

async function submitTaskForReviewAJAX(form) {
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
            },
        });

        if (!response.ok) throw new Error('Submission failed');

        if (window.ajax && window.ajax.showSuccess) {
            window.ajax.showSuccess(isUrgentTask ? 'Urgent task submitted for review!' : 'Task submitted for review!');
        }
        setTimeout(() => location.reload(), 1000);

    } catch (error) {
        if (window.ajax && window.ajax.showError) {
            window.ajax.showError(isUrgentTask ? 'Failed to update urgent task' : 'Failed to submit task');
        } else {
            alert(isUrgentTask ? 'Failed to update urgent task' : 'Failed to submit task');
        }
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
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
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
        ? `<span class="tag tag-primary" style="font-size:8.5px;padding:1px 5px">${escapeHtml(data.user_role.toUpperCase())}</span>`
        : '';

    const html = `
        <div class="comment-item" style="animation: slideIn 0.3s ease-out">
            <div class="comment-header">
                <div class="av-sm" style="background:${data.user_color || 'var(--primary)'}">
                    ${escapeHtml(data.user_initial)}
                </div>
                <div style="flex:1">
                    <div style="font-weight:700;font-size:12px;color:var(--text-main)">${escapeHtml(data.user_name)}</div>
                    <div style="font-size:10px;color:var(--text-muted)">Just now</div>
                </div>
                ${roleTag}
            </div>
            <div style="font-size:12.5px;line-height:1.45;color:var(--text-main);padding-left:30px">
                ${escapeHtml(data.content)}
            </div>
        </div>
    `;

    list.insertAdjacentHTML('beforeend', html);

    const countEl = document.getElementById('commentCount');
    if (countEl) countEl.textContent = parseInt(countEl.textContent || 0) + 1;

    list.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function updateSubmitButtonState() {
    const submitBtn = document.getElementById('submitBtn');
    if (!submitBtn) return;

    const mediaPreviewContainer = document.getElementById('mediaPreviewContainer');
    const mediaItems = mediaPreviewContainer ? mediaPreviewContainer.querySelectorAll('.media-item') : [];
    const hasMedia = mediaItems && mediaItems.length > 0;

    const lockMsg = document.querySelector('.submit-lock-msg');

    if (hasMedia) {
        submitBtn.disabled = false;
        if (lockMsg) lockMsg.style.display = 'none';
    } else {
        submitBtn.disabled = true;
        if (lockMsg) lockMsg.style.display = 'block';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush

{{-- ═══ PAUSE TASK MODAL ═══ --}}
@if($task->status === 'inprogress' && !$task->is_paused)
<div id="pauseModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,0.45);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px">
    <div class="wf-pause-modal-card" style="background:#FFFFFF;border:1px solid var(--border);border-radius:var(--radius-lg);max-width:440px;width:100%;box-shadow:var(--shadow-md);overflow:hidden;animation:fadeSlideUp 0.25s ease">
        <div style="padding:18px 20px 0">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h3 style="font-size:16px;font-weight:800;color:#92400E;display:flex;align-items:center;gap:6px;margin:0">⏸️ Pause Task</h3>
                <button onclick="document.getElementById('pauseModal').style.display='none'" style="background:none;border:none;font-size:20px;color:var(--text-muted);cursor:pointer">&times;</button>
            </div>
            <div style="padding:10px 12px;background:#FFFBEB;border:1px solid #FDE68A;border-radius:var(--radius-sm);font-size:12px;color:#92400E;line-height:1.5;margin-bottom:14px">
                <strong>"{{ $task->title }}"</strong> will be paused. Your progress and timer are preserved.
            </div>
        </div>
        <form method="POST" action="{{ route('designer.tasks.pause', $task) }}" id="pauseForm">
            @csrf
            <div style="padding:0 20px 20px">
                <div style="margin-bottom:10px;">
                    <label style="display:block;font-size:11px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px">Accomplished</label>
                    <textarea name="work_logged" placeholder="E.g. Completed initial design variations" rows="2" class="filter-input" style="width:100%;font-size:12.5px !important;resize:vertical"></textarea>
                </div>
                <label style="display:block;font-size:11px;font-weight:700;color:var(--text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:4px">Reason *</label>
                <textarea name="reason" required placeholder="E.g. Urgent brief from client" rows="2" class="filter-input" style="width:100%;font-size:12.5px !important;resize:vertical"></textarea>
                <div style="display:flex;gap:8px;margin-top:14px">
                    <button type="button" onclick="document.getElementById('pauseModal').style.display='none'" class="btn-sec" style="flex:1;justify-content:center;padding:7px !important;font-size:12px !important">Cancel</button>
                    <button type="submit" class="btn-action-primary" style="flex:1;background:#D97706 !important;border-color:#D97706 !important;justify-content:center;padding:7px !important;font-size:12px !important">⏸️ Pause</button>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
@keyframes fadeSlideUp { from { opacity:0;transform:translateY(12px); } to { opacity:1;transform:translateY(0); } }
</style>
@endif

@endsection
