@extends('layouts.app')

@section('content')
@php
    $workflowSteps = $workflowSteps ?? [];
@endphp
@push('styles')
    <link href="{{ asset('css/task-detail.css') }}?v=2.0" rel="stylesheet">
    <link href="{{ asset('css/animations.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pages/designer-theme.css') }}?v=2.0" rel="stylesheet">
@endpush

<div class="task-minimal">
    {{-- ═══ TOP NAVIGATION ═══ --}}
    <header class="topbar">
        <div>
            <nav class="breadcrumb">
                <a href="{{ route('designer.tasks') }}">My Tasks</a>
                <span class="breadcrumb-sep">/</span>
                <span>Task Details</span>
            </nav>
            <h1 class="page-title">{{ $task->title }}</h1>
        </div>
        <a href="{{ route('designer.tasks') }}" class="btn-sec">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </header>

    {{-- ═══ WORKFLOW PROGRESS ═══ --}}
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
                        <div class="wf-step-time" style="color:var(--primary)">Current Phase</div>
                    @endif
                </div>
            </div>
            @if($i < count($workflowSteps) - 1)
                <div class="wf-connector {{ $step['done'] ? 'wf-conn-done' : '' }}"></div>
            @endif
        @endforeach
    </div>

    {{-- ═══ STATUS INDICATORS ═══ --}}
    @if($task->status === 'inprogress' && $task->started_at)
        <div class="wf-status-bar wf-timer-bar">
            <div style="display:flex;align-items:center;gap:12px">
                <span class="wf-pulse"></span>
                <span>Design Phase: Active</span>
            </div>
            <div style="font-size:14px;color:var(--text-muted)">
                Started {{ $task->started_at->diffForHumans() }} ·
                <span style="font-weight:700;color:var(--primary)">
                    <i class="fas fa-clock"></i> {{ $task->started_at->diff(now())->format('%hh %im') }} elapsed
                </span>
            </div>
        </div>
    @elseif($task->status === 'review')
        <div class="wf-status-bar wf-review-bar">
            <div style="display:flex;align-items:center;gap:12px">
                <i class="fas fa-hourglass-half" style="color:var(--warning)"></i>
                <span>Awaiting Review</span>
            </div>
            <span style="font-size:14px;color:var(--text-muted)">Waiting for strategist feedback</span>
        </div>
    @elseif($task->status === 'completed')
        <div class="wf-status-bar wf-done-bar">
            <div style="display:flex;align-items:center;gap:12px">
                <i class="fas fa-check-circle" style="color:var(--success)"></i>
                <span>{{ $task->is_urgent_task ? 'Urgent Task Delivered' : 'Task Successfully Completed' }}</span>
            </div>
            <span style="font-size:14px;color:var(--text-muted)">{{ $task->is_urgent_task ? 'Marked as delivered to client' : 'Approved by Strategist' }}</span>
        </div>
    @endif

    {{-- ═══ PAUSED BANNER ═══ --}}
    @if($task->is_paused)
        <div class="wf-status-bar wf-paused-bar" style="background:linear-gradient(135deg,#FEF3C7,#FDE68A);border:1.5px dashed #D97706;color:#92400E">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:22px">⏸️</span>
                <div>
                    <div style="font-weight:800;font-size:15px">Task Paused</div>
                    <div style="font-size:12px;opacity:0.8">{{ $task->pause_reason }}</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:13px;font-weight:600">
                    ⏱️ {{ $task->paused_at->diffForHumans(null, true) }} paused
                </span>
                <form method="POST" action="{{ route('designer.tasks.resume', $task) }}" style="margin:0">
                    @csrf
                    <button type="submit" class="wf-inline-resume-btn" style="padding:8px 20px;background:#059669;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all 0.2s">
                        ▶️ Resume Task
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- ═══ URGENT TASK BADGE ═══ --}}
    @if($task->is_urgent_task)
        <div class="wf-status-bar wf-urgent-bar" style="background:linear-gradient(135deg,#FEE2E2,#FECACA);border:1.5px solid #EF4444;color:#991B1B">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:22px">🚨</span>
                <div>
                    <div style="font-weight:800;font-size:15px">Urgent Task</div>
                    <div style="font-size:12px;opacity:0.8">Requested by {{ $task->urgent_requested_by ?? 'Admin' }}</div>
                </div>
            </div>
            <span class="tag" style="background:#EF4444;color:#fff;font-size:10px;font-weight:800;padding:4px 10px;border-radius:6px">PRIORITY</span>
        </div>
    @endif

    {{-- ═══ READ-ONLY PEER BANNER ═══ --}}
    @if(!empty($isReadOnly))
        <div class="wf-status-bar" style="background:rgba(99,102,241,0.08);border:1.5px solid rgba(99,102,241,0.25);color:var(--text);margin-bottom:16px;border-radius:14px">
            <div style="display:flex;align-items:center;gap:12px">
                <i class="fa-solid fa-users-viewfinder" style="font-size:22px;color:var(--primary)"></i>
                <div>
                    <div style="font-weight:800;font-size:14px;color:var(--primary)">Team Overview (Read-Only)</div>
                    <div style="font-size:12px;color:var(--text-muted)">This task is assigned to <strong>{{ $task->assignee->name ?? 'another team member' }}</strong>. You are viewing this task in agency peer review mode.</div>
                </div>
            </div>
        </div>
    @endif

    <div class="two-col">
        {{-- ═══ MAIN CONTENT ═══ --}}
        <main>
            {{-- Tab Navigation --}}
            <div class="tab-nav">
                <button class="tab-btn active" onclick="switchTab('tab-details', event)">
                    <i class="fas fa-info-circle"></i> Details
                </button>
                <button class="tab-btn" onclick="switchTab('tab-media', event)">
                    <i class="fas fa-images"></i> Media & Assets
                    <span class="count-pill" id="mediaTabCount">{{ $task->media()->count() }}</span>
                </button>
                <button class="tab-btn" onclick="switchTab('tab-comments', event)">
                    <i class="fas fa-comments"></i> Feedback
                    <span class="count-pill" id="commentTabCount">{{ $task->comments->count() }}</span>
                </button>
            </div>

            {{-- ═══ TAB: DETAILS ═══ --}}
            <div id="tab-details" class="tab-content active">
                <div class="card">
                    <div class="sec-header">
                        <div class="sec-icon"><i class="fas fa-info-circle"></i></div>
                        <h2 class="sec-title">Task Overview</h2>
                    </div>

                    @php
                        $taskPlatforms = array_values(array_filter((array) $task->platform));
                        $urgentBrief = $task->is_urgent_task
                            ? trim((string) ($task->brief ?: $task->caption ?: ''))
                            : '';
                        $showUrgentCaption = $task->is_urgent_task
                            && trim((string) ($task->caption ?? '')) !== ''
                            && trim((string) $task->caption) !== $urgentBrief;
                    @endphp

                    <div class="detail-grid" style="margin-bottom:24px">
                        <div class="detail-item">
                            <span class="detail-label">Client</span>
                            <div class="detail-value" style="display:flex;align-items:center;gap:8px">
                                @if($task->client->logo)
                                    <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:20px;height:20px;border-radius:4px;object-fit:cover">
                                @else
                                    <span style="font-size:18px">{!! $task->client->emoji ?? '🏢' !!}</span>
                                @endif
                                {{ $task->client->name }}
                            </div>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Assigned By (Strategist)</span>
                            <div class="detail-value" style="display:flex;align-items:center;gap:6px;font-weight:700;color:var(--primary)">
                                <i class="fa-solid fa-user-pen"></i> {{ $task->creator->name ?? 'Strategist' }}
                            </div>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Assigned Creative</span>
                            <div class="detail-value" style="display:flex;align-items:center;gap:6px;font-weight:700;color:var(--teal)">
                                <i class="fa-solid fa-user-check"></i> {{ $task->assignee->name ?? 'Unassigned' }}
                            </div>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Deadline</span>
                            <div class="detail-value {{ $task->isOverdue() ? 'text-danger' : '' }}" style="font-size:14px">
                                <i class="far fa-calendar-alt"></i> {{ $task->deadline?->format('d M Y') ?? 'Flexible' }}
                            </div>
                        </div>
                        @if($task->design_deadline)
                            <div class="detail-item">
                                <span class="detail-label">Design Deadline</span>
                                <div class="detail-value" style="font-size:14px">
                                    <i class="far fa-calendar-check"></i> {{ $task->design_deadline->format('d M Y') }}
                                </div>
                            </div>
                        @endif
                        <div class="detail-item">
                            <span class="detail-label">Category</span>
                            <div><span class="tag tag-primary">{{ ucfirst($task->type) }}</span></div>
                        </div>
                        @if(!empty($taskPlatforms))
                            <div class="detail-item">
                                <span class="detail-label">Platforms</span>
                                <div style="display:flex;gap:4px;flex-wrap:wrap">
                                    @foreach($taskPlatforms as $platform)
                                        <span class="tag" style="background:#F1F5F9;border:none;font-size:10px">{{ ucfirst($platform) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($task->is_urgent_task && $urgentBrief !== '')
                        <div class="task-brief">
                            <div class="task-brief-label">
                                <i class="fa-solid fa-bolt"></i>
                                <span>Urgent Brief</span>
                            </div>
                            <div class="task-brief-body">{{ $urgentBrief }}</div>
                        </div>
                    @endif

                    @if($task->caption && (!$task->is_urgent_task || $showUrgentCaption))
                        <div class="task-brief">
                            <div class="task-brief-label">
                                <i class="fa-solid fa-pen"></i>
                                <span>Caption</span>
                            </div>
                            <div class="task-brief-body">{{ $task->caption }}</div>
                        </div>
                    @endif

                    @if($task->brief && !$task->is_urgent_task)
                        <div class="task-brief" style="margin-top:12px">
                            <div class="task-brief-label">
                                <i class="fa-solid fa-pen"></i>
                                <span>Content Brief</span>
                            </div>
                            <div class="task-brief-body">{{ $task->brief }}</div>
                        </div>
                    @endif

                    @if(!empty($task->reference_links))
                        <div class="detail-item">
                            <span class="detail-label">Reference Materials</span>
                            <div style="display:flex;flex-wrap:wrap;gap:8px">
                                @foreach((is_array($task->reference_links) ? $task->reference_links : json_decode($task->reference_links, true)) ?? [] as $link)
                                    @if(filter_var($link, FILTER_VALIDATE_URL))
                                        <a href="{{ $link }}" target="_blank" class="btn-sec" style="font-size:12px;padding:6px 12px">
                                            <i class="fas fa-link"></i> {{ Str::limit(str_replace(['https://','www.'],'',$link), 15) }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ═══ TAB: MEDIA ═══ --}}
            <div id="tab-media" class="tab-content">
                <div class="card">
                    <div class="sec-header">
                        <div class="sec-icon"><i class="fas fa-images"></i></div>
                        <h2 class="sec-title">Media & Design Files</h2>
                    </div>

                    @php $allMedia = $task->media()->get(); @endphp

                    <div class="media-grid" id="mediaPreviewContainer" style="grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap:16px">
                        @foreach($allMedia as $item)
                            <div class="media-item">
                                @if($item->isVideo())
                                    <video class="media-file" muted preload="metadata" style="background:#000;object-fit:cover"><source src="{{ $item->getUrl() }}"></video>
                                    <div style="position:absolute;top:8px;left:8px;background:rgba(0,0,0,0.7);color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;z-index:2">
                                        <i class="fa-solid fa-video"></i> Video
                                    </div>
                                @elseif($item->isPdf())
                                    <div style="height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--card2)">
                                        <i class="fa-solid fa-file-pdf" style="font-size:32px;color:#EF4444"></i>
                                    </div>
                                    <div style="position:absolute;top:8px;left:8px;background:rgba(239,68,68,0.85);color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;z-index:2">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </div>
                                @elseif($item->isImage())
                                    <img src="{{ $item->getUrl() }}" class="media-file" loading="lazy">
                                @else
                                    <div style="height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--card2)">
                                        <i class="fa-solid fa-file" style="font-size:32px;color:var(--text3)"></i>
                                    </div>
                                @endif
                                <div class="media-overlay">
                                    <a href="{{ $item->getUrl() }}" target="_blank" class="media-btn" title="View / Play Preview"><i class="fas fa-expand"></i></a>
                                    <a href="{{ $item->getUrl() }}" download="{{ $item->name }}" class="media-btn" title="Download File"><i class="fas fa-download"></i></a>
                                    <button type="button" onclick="deleteMediaAJAX({{ $item->id }}, event)" class="media-btn btn-danger" title="Remove"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($allMedia->count() === 0)
                        <div class="upload-zone" id="emptyUploadZone" onclick="document.getElementById('mediaInput').click()" style="padding:32px">
                            <div class="upload-icon"><i class="fas fa-cloud-upload-alt" style="font-size:24px"></i></div>
                            <h3 style="font-size:16px;font-weight:700">Upload your work</h3>
                            <p style="font-size:13px;color:var(--text-muted)">Click to browse images, videos, documents</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('designer.tasks.upload-media', $task) }}" enctype="multipart/form-data" id="mediaUploadForm" style="margin-top:20px">
                        @csrf
                        <div style="display:flex;gap:12px;align-items:center">
                            <input type="file" name="media[]" id="mediaInput" accept="image/*,video/*,.pdf,.doc,.docx,.zip,.rar" multiple style="display:none" onchange="handleMediaSelect()">
                            <button type="button" onclick="document.getElementById('mediaInput').click()" class="btn-sec" style="font-size:13px;font-weight:800;border-radius:12px">
                                <i class="fas fa-file-arrow-up" style="margin-right:6px"></i> Select Files / Videos / Images...
                            </button>
                            <button type="submit" id="mediaUploadBtn" class="wf-submit-btn" style="display:none;width:auto;padding:10px 24px;font-size:13px;border-radius:12px">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Selected
                            </button>
                        </div>
                        <div id="mediaPreview" style="display:none;margin-top:20px;padding:20px;background:rgba(var(--primary-rgb), 0.03);border-radius:16px;border:1.5px solid var(--primary-light)">
                            <div style="font-size:11px;font-weight:800;color:var(--primary);text-transform:uppercase;letter-spacing:1px;margin-bottom:12px">Ready for Upload:</div>
                            <div id="fileList"></div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ═══ TAB: FEEDBACK ═══ --}}
            <div id="tab-comments" class="tab-content">
                <div class="card">
                    <div class="sec-header">
                        <div class="sec-icon"><i class="fas fa-comments"></i></div>
                        <h2 class="sec-title">Discussion</h2>
                    </div>

                    <div class="comments-list" id="commentsList" style="max-height:400px;overflow-y:auto;padding-right:10px">
                        @forelse($task->comments as $comment)
                            <div class="comment-item">
                                <div class="comment-header">
                                    <div class="av-sm" style="background:{{ $comment->user->avatar_color ?? 'var(--primary)' }}">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                    <div style="flex:1">
                                        <div style="font-weight:700;font-size:13px">{{ $comment->user->name }}</div>
                                        <div style="font-size:10px;color:var(--text-muted)">{{ $comment->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                                <div style="font-size:13px;line-height:1.5;color:var(--text-main);padding-left:42px">
                                    {{ $comment->body }}
                                </div>
                            </div>
                        @empty
                            <div class="no-comments-msg" style="text-align:center;padding:20px;font-size:13px;color:var(--text-muted)">No discussion yet.</div>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('designer.tasks.comment', $task) }}" id="commentForm" style="margin-top:20px;display:flex;gap:10px">
                        @csrf
                        <input name="body" placeholder="Write a message..." style="flex:1;padding:12px 16px;font-size:13px;border-radius:10px;border:1px solid var(--border)" required>
                        <button type="submit" class="wf-submit-btn" style="width:auto;padding:0 20px;font-size:13px">Send</button>
                    </form>
                </div>
            </div>
        </main>

        {{-- ═══ SIDEBAR ACTIONS ═══ --}}
        <aside class="action-card">
            {{-- Status Card --}}
            <div class="card" style="padding:24px">
                <div class="detail-label" style="margin-bottom:16px">Current Status</div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
                    <div class="wf-step-icon {{ $task->status === 'completed' ? 'badge-success' : 'badge-primary' }}" style="width:48px;height:48px;font-size:20px">
                        <i class="fas fa-{{ $task->status === 'completed' ? 'check-double' : 'pencil-ruler' }}"></i>
                    </div>
                    <div>
                        <div style="font-weight:800;font-size:18px">{{ $task->status_label }}</div>
                        <div style="font-size:12px;color:var(--text-muted)">ID: #TSK-{{ str_pad($task->id, 4, '0', STR_PAD_LEFT) }}</div>
                    </div>
                </div>

                @if($task->status === 'inprogress' && !$task->is_paused)
                    @if($task->revision_count > 0)
                        <div class="rev-card">
                            <div class="rev-icon"><i class="fas fa-exclamation-circle"></i></div>
                            <div>
                                <div class="rev-title">Revision #{{ $task->revision_count }}</div>
                                <div class="rev-meta">Review notes before re-submitting</div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('designer.tasks.submit', $task) }}" id="submitForm">
                        @csrf
                        <button type="submit" class="wf-submit-btn" id="submitBtn" {{ $task->media()->count() === 0 ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane"></i> {{ $task->is_urgent_task ? 'Mark as Delivered to Client' : 'Submit for Review' }}
                        </button>
                        @if($task->media()->count() === 0)
                            <p class="submit-lock-msg" style="font-size:11px;color:var(--danger);text-align:center;margin-top:12px;font-weight:600">
                                <i class="fas fa-lock"></i> {{ $task->is_urgent_task ? 'Upload media to mark delivery' : 'Upload media to unlock' }}
                            </p>
                        @endif
                    </form>

                    {{-- Pause Button --}}
                    <button type="button" onclick="document.getElementById('pauseModal').style.display='flex'" class="wf-pause-btn" style="margin-top:10px;width:100%;padding:12px;background:transparent;border:1.5px dashed #D97706;color:#D97706;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.2s">
                        ⏸️ Pause Task
                    </button>
                @elseif($task->is_paused)
                    {{-- Resume Button --}}
                    <div style="text-align:center;padding:16px 0">
                        <div style="font-size:32px;margin-bottom:8px">⏸️</div>
                        <div style="font-size:14px;font-weight:700;color:#D97706;margin-bottom:4px">Task is Paused</div>
                        <div style="font-size:12px;color:var(--text-muted);margin-bottom:16px">{{ $task->pause_reason }}</div>
                        <form method="POST" action="{{ route('designer.tasks.resume', $task) }}">
                            @csrf
                            <button type="submit" class="wf-resume-btn" style="width:100%;padding:14px;background:#059669;color:#fff;border:none;border-radius:12px;font-size:14px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.2s">
                                ▶️ Resume & Continue Working
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Social Media Links --}}
            @if(($selectedSocialMediaLinks ?? collect())->isNotEmpty())
                <div class="card" style="padding:24px">
                    <div class="detail-label" style="margin-bottom:16px">Social Media Links</div>
                    <div style="display:flex;flex-direction:column;gap:12px">
                        @foreach($selectedSocialMediaLinks as $selectedLink)
                            @php $linkInfo = $selectedLink->getPlatformIcon(); @endphp
                            <a href="{{ $selectedLink->url }}" target="_blank" class="btn-sec" style="background:{{ $linkInfo['color'] }}08;border-color:{{ $linkInfo['color'] }}20;color:{{ $linkInfo['color'] }};padding:12px">
                                <i class="fa-brands {{ $linkInfo['icon'] }}"></i>
                                <span style="flex:1;text-align:left;font-weight:700">{{ $selectedLink->label ?: ucfirst($selectedLink->platform) }}</span>
                                <i class="fas fa-external-link-alt" style="font-size:10px"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>
</div>
@push('scripts')
<script>
/**
 * Tab Switching Logic
 */
function switchTab(tabId, event) {
    // Hide all contents
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    // Deactivate all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

    // Show target content
    const target = document.getElementById(tabId);
    if (target) {
        target.classList.add('active');
    }

    // Activate clicked button
    if (event && event.currentTarget) {
        event.currentTarget.classList.add('active');
    } else if (event && event.target) {
        const btn = event.target.closest('.tab-btn');
        if (btn) btn.classList.add('active');
    }
}
window.switchTab = switchTab;

/**
 * Handle media file selection and display premium visual preview
 */
function handleMediaSelect() {
    const input = document.getElementById('mediaInput');
    const preview = document.getElementById('mediaPreview');
    const fileList = document.getElementById('fileList');
    const uploadBtn = document.getElementById('mediaUploadBtn');

    if (input.files && input.files.length > 0) {
        fileList.innerHTML = '';
        preview.style.display = 'block';
        uploadBtn.style.display = 'inline-flex';

        Array.from(input.files).forEach((file) => {
            const item = document.createElement('div');
            item.className = 'preview-item';

            // Generate visual preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    item.innerHTML = `
                        <img src="${e.target.result}" class="preview-img">
                        <div class="preview-info">${(file.size / 1024 / 1024).toFixed(1)}MB</div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                // Fallback for videos or other files
                item.innerHTML = `
                    <div style="display:flex;align-items:center;justify-content:center;height:100%;background:#F1F5F9;color:var(--primary);font-size:24px">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="preview-info">VIDEO</div>
                `;
            }

            fileList.appendChild(item);
        });
    } else {
        preview.style.display = 'none';
        uploadBtn.style.display = 'none';
    }
}
window.handleMediaSelect = handleMediaSelect;

document.addEventListener('DOMContentLoaded', function() {
    // Check media status on page load
    updateSubmitButtonState();

    // Handle media upload
    const mediaUploadForm = document.getElementById('mediaUploadForm');
    if (mediaUploadForm) {
        mediaUploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            uploadMediaAJAX(this);
        });
    }

    // Handle submit for review form
    const submitForm = document.querySelector('#submitForm');
    if (submitForm) {
        submitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitTaskForReviewAJAX(this);
        });
    }

    // Handle comment form submission
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitCommentAJAX(this);
        });
    }
});

/**
 * Show loading overlay for form
 */
function showLoadingOverlay(btn, text = 'Loading...') {
    if (!btn) return;
    btn.dataset.originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> ${text}`;
}

/**
 * Hide loading overlay from form
 */
function hideLoadingOverlay(btn) {
    if (!btn) return;
    btn.disabled = false;
    btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
}

/**
 * Delete specific media file via AJAX
 */
async function deleteMediaAJAX(mediaId, event) {
    if (event) event.preventDefault();

    if (!confirm('Permanently remove this file?')) return;

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
            setTimeout(() => mediaContainer.remove(), 300);
        }

        if (window.ajax && window.ajax.showSuccess) {
            window.ajax.showSuccess('File removed successfully');
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

/**
 * Upload media via AJAX
 */
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
            window.ajax.showSuccess('Designs uploaded successfully');
        }

        // Refresh to show new media items properly formatted
        setTimeout(() => location.reload(), 1000);

    } catch (error) {
        if (window.ajax && window.ajax.showError) {
            window.ajax.showError('Upload failed. Please check file size.');
        } else {
            alert('Upload failed. Please check file size.');
        }
    } finally {
        if (btn) hideLoadingOverlay(btn);
    }
}

/**
 * Submit task update via AJAX
 */
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
            window.ajax.showSuccess(isUrgentTask ? 'Urgent task marked as delivered to client!' : 'Task submitted for review!');
        }
        setTimeout(() => location.reload(), 1500);

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

/**
 * Submit comment via AJAX
 */
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

/**
 * Add comment to list dynamically
 */
function addCommentToList(data) {
    const list = document.getElementById('commentsList');
    if (!list) return;

    const noMsg = list.querySelector('.no-comments-msg');
    if (noMsg) noMsg.remove();

    const roleTag = data.user_role !== 'designer'
        ? `<span class="tag tag-primary" style="font-size:9px">${escapeHtml(data.user_role.toUpperCase())}</span>`
        : '';

    const html = `
        <div class="comment-item" style="animation: slideIn 0.4s ease-out">
            <div class="comment-header">
                <div class="av-sm" style="background:${data.user_color || 'var(--primary)'}">
                    ${escapeHtml(data.user_initial)}
                </div>
                <div style="flex:1">
                    <div style="font-weight:700;font-size:14px">${escapeHtml(data.user_name)}</div>
                    <div style="font-size:11px;color:var(--text-muted)">Just now</div>
                </div>
                ${roleTag}
            </div>
            <div style="font-size:14px;line-height:1.6;color:var(--text-main);padding-left:42px">
                ${escapeHtml(data.content)}
            </div>
        </div>
    `;

    list.insertAdjacentHTML('beforeend', html);

    // Update count
    const countEl = document.getElementById('commentCount');
    if (countEl) countEl.textContent = parseInt(countEl.textContent || 0) + 1;

    const tabCountEl = document.getElementById('commentTabCount');
    if (tabCountEl) tabCountEl.textContent = parseInt(tabCountEl.textContent || 0) + 1;

    // Scroll to new comment
    list.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Update submit button state
 */
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

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush

{{-- ═══ PAUSE MODAL ═══ --}}
@if($task->status === 'inprogress' && !$task->is_paused)
<div id="pauseModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px">
    <div class="wf-pause-modal-card" style="background:var(--card);border:1px solid var(--border);border-radius:16px;max-width:440px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.2);overflow:hidden;animation:fadeSlideUp 0.3s ease">
        <div style="padding:24px 24px 0">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
                <h3 style="font-size:18px;font-weight:800;color:#92400E;display:flex;align-items:center;gap:8px;margin:0">⏸️ Pause Task</h3>
                <button onclick="document.getElementById('pauseModal').style.display='none'" style="background:none;border:none;font-size:20px;color:var(--text3);cursor:pointer">&times;</button>
            </div>
            <div style="padding:14px;background:#FFFBEB;border:1px solid #FDE68A;border-radius:10px;font-size:13px;color:#92400E;line-height:1.5;margin-bottom:20px">
                <strong>"{{ $task->title }}"</strong> will be paused. You can resume it later and your progress will be saved.
            </div>
        </div>
        <form method="POST" action="{{ route('designer.tasks.pause', $task) }}" id="pauseForm">
            @csrf
            <div style="padding:0 24px 24px">
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">What did you accomplish in this session?</label>
                    <textarea name="work_logged" placeholder="E.g. Built the homepage hero section and setup database migrations" rows="3" style="width:100%;padding:12px;border:1.5px solid #E5E7EB;border-radius:10px;font-size:14px;font-family:inherit;resize:vertical;box-sizing:border-box;transition:border 0.2s" onfocus="this.style.borderColor='#D97706'" onblur="this.style.borderColor='#E5E7EB'"></textarea>
                </div>
                <label style="display:block;font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Why are you pausing? *</label>
                <textarea name="reason" required placeholder="E.g. Admin called — urgent brochure needed for XYZ client" rows="2" style="width:100%;padding:12px;border:1.5px solid #E5E7EB;border-radius:10px;font-size:14px;font-family:inherit;resize:vertical;box-sizing:border-box;transition:border 0.2s" onfocus="this.style.borderColor='#D97706'" onblur="this.style.borderColor='#E5E7EB'"></textarea>
                <div style="display:flex;gap:10px;margin-top:20px">
                    <button type="button" onclick="document.getElementById('pauseModal').style.display='none'" style="flex:1;padding:12px;background:var(--card2);border:1px solid var(--border);border-radius:10px;font-size:13px;font-weight:700;color:var(--text2);cursor:pointer">Cancel</button>
                    <button type="submit" style="flex:1;padding:12px;background:#D97706;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">⏸️ Pause Task</button>
                </div>
            </div>
        </form>
    </div>
</div>
<style>
@keyframes fadeSlideUp { from { opacity:0;transform:translateY(20px); } to { opacity:1;transform:translateY(0); } }
</style>
@endif

@endsection
