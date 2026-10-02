@extends('layouts.app')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&display=swap');

.approval-page { position: relative; padding: 14px; border-radius: 18px; background: linear-gradient(135deg, #f8fafc 0%, #f0fdfa 50%, #fff7ed 100%); overflow: hidden; }
.approval-page::before, .approval-page::after { content: ""; position: absolute; border-radius: 999px; opacity: 0.55; z-index: 0; }
.approval-page::before { width: 260px; height: 260px; right: -120px; top: -140px; background: radial-gradient(circle, rgba(14,116,144,0.2) 0%, transparent 70%); }
.approval-page::after { width: 220px; height: 220px; left: -110px; bottom: -130px; background: radial-gradient(circle, rgba(249,115,22,0.2) 0%, transparent 70%); }
.approval-page > * { position: relative; z-index: 1; }
.approval-topbar { padding: 18px; border-radius: 14px; background: linear-gradient(120deg, #b91c1c 0%, #dc2626 55%, #ef4444 100%); color: #fff1f2; box-shadow: 0 12px 30px rgba(220, 38, 38, 0.28); }
.approval-topbar .page-title { font-family: "Space Grotesk", "Plus Jakarta Sans", sans-serif; font-size: 20px; letter-spacing: 0.02em; }
.approval-topbar .breadcrumb a { color: #ffe4e6; }
.approval-topbar .btn-sec { border-color: rgba(255, 241, 242, 0.35); color: #fff1f2; background: rgba(127, 29, 29, 0.22); }

.approval-container { display: grid; grid-template-columns: 1fr 420px; gap: 24px; align-items: start; }
.media-viewer { background: var(--card2); border-radius: var(--radius-lg); overflow: hidden; position: relative; }
.media-viewer-content { aspect-ratio: 16/9; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(79,109,240,0.06) 0%, rgba(168,85,247,0.06) 100%); }
.media-viewer img, .media-viewer video { width: 100%; height: 100%; object-fit: contain; }
.media-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 14px; background:var(--card2); border-top:1px solid var(--border); font-size:11px; color:var(--text3); }
.media-toolbar strong { color: var(--text); font-weight: 600; }
.media-thumbs { display:grid; grid-template-columns:repeat(auto-fill, minmax(90px, 1fr)); gap:10px; margin-top:12px; }
.media-thumb { position:relative; border-radius:10px; overflow:hidden; border:2px solid var(--border); background:#000; aspect-ratio:1; cursor:pointer; transition:border-color 0.2s, transform 0.2s; }
.media-thumb:hover { border-color:var(--primary); transform:translateY(-2px); }
.media-thumb.active { border-color:var(--primary); box-shadow:0 0 0 2px var(--primary)20; }
.media-thumb img, .media-thumb video { width:100%; height:100%; object-fit:cover; }
.media-thumb .thumb-badge { position:absolute; top:6px; left:6px; background:rgba(0,0,0,0.65); color:#fff; font-size:9px; padding:2px 6px; border-radius:6px; display:flex; align-items:center; gap:4px; }
.media-thumb .thumb-name { position:absolute; left:0; right:0; bottom:0; padding:6px 8px; font-size:9.5px; color:#fff; background:linear-gradient(transparent, rgba(0,0,0,0.75)); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.section-card { background:var(--card2); padding:14px 16px; border-radius:var(--radius-md); border:1px solid var(--border); }
.section-title { font-size:12px; font-weight:700; color:var(--text3); margin-bottom:10px; display:flex; align-items:center; gap:6px; }
.pill-wrap { display:flex; flex-wrap:wrap; gap:6px; }
.pill { display:inline-flex; align-items:center; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; background:var(--primary)15; color:var(--primary); }
.task-badges { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.badge-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.task-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 16px; background: var(--card2); border-radius: var(--radius-md); margin-bottom: 16px; }
.meta-item { display: flex; flex-direction: column; gap: 4px; }
.meta-label { font-size: 11px; font-weight: 700; color: var(--text3); text-transform: uppercase; letter-spacing: 0.5px; }
.meta-value { font-size: 13px; color: var(--text); font-weight: 500; }
.desc-box { background: var(--card2); padding: 16px; border-radius: var(--radius-md); border-left: 4px solid var(--primary); }
.comment-item { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border); }
.comment-item:last-child { border-bottom: none; }
.comment-avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.right-panel { position: sticky; top: 20px; }
.revision-chip { padding:5px 10px; border:1px solid var(--border); border-radius:20px; background:var(--card2); font-size:11px; font-weight:500; color:var(--text3); cursor:pointer; transition:all 0.2s; white-space:nowrap; }
.revision-chip:hover { border-color:var(--primary); color:var(--primary); background:var(--primary)08; }
.content-grid { display:grid; grid-template-columns:1.1fr 0.9fr; gap:16px; }
.card.premium { border-radius: 16px; border: 1px solid rgba(148,163,184,0.35); box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); background: linear-gradient(160deg, rgba(255,255,255,0.95) 0%, rgba(248,250,252,0.95) 100%); }
.card.premium .section-title { text-transform: uppercase; letter-spacing: 0.06em; font-size: 10.5px; }
.card.premium .section-card { background: #fff; }
.card.premium .section-card, .card.premium .pill { box-shadow: inset 0 0 0 1px rgba(148,163,184,0.35); }
@media (max-width: 1200px) { .approval-container { grid-template-columns: 1fr; } .right-panel { position: relative; top: 0; } }
@media (max-width: 980px) { .content-grid { grid-template-columns: 1fr; } }
</style>

<div class="approval-page">
<div class="topbar approval-topbar">
    <div>
        <div class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('admin.approvals') }}">Approvals</a>
            <span class="breadcrumb-sep">›</span>
            <span>Review & Approve</span>
        </div>
        <div class="page-title">Review Task Submission</div>
    </div>
    <a href="{{ route('admin.approvals') }}" class="btn-sec">← Back</a>
</div>

{{-- MAIN LAYOUT --}}
<div class="approval-container" style="margin-top:20px">
    {{-- LEFT COLUMN: Media + Details --}}
    <div>

        {{-- TASK TITLE AND STATUS --}}
        <div class="card premium" style="margin-bottom:16px;padding:20px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:12px">
                <div style="flex:1">
                    <h1 style="font-size:24px;font-weight:800;color:var(--text);margin:0;line-height:1.3;margin-bottom:8px;font-family:'Space Grotesk','Plus Jakarta Sans',sans-serif">{{ $task->title }}</h1>
                    @php
                        $isDevType = in_array($task->type, ['website', 'software'], true);
                        $isUrgentTask = (bool) $task->is_urgent_task;
                        $hasCaption = filled(trim((string) ($task->caption ?? '')));
                        $hasHashtags = filled(trim((string) ($task->hashtags ?? '')));
                        $urgentBrief = $isUrgentTask
                            ? trim((string) ($task->brief ?: $task->caption ?: ''))
                            : '';
                        $creatorRoleLabel = $task->creator?->role
                            ? \Illuminate\Support\Str::title(str_replace('_', ' ', $task->creator->role))
                            : 'Creator';
                        $taskSummary = $isDevType
                            ? ($task->project_notes ?? $task->brief ?? 'Development task pending technical review.')
                            : ($urgentBrief !== '' ? $urgentBrief : ($task->brief ?? $task->caption ?? 'No content summary provided.'));
                    @endphp
                    <p style="font-size:13px;color:var(--text2);margin:0">{{ Str::limit($taskSummary, 140) }}</p>
                </div>
                <div class="task-badges">
                    @php
                        $typeColors = ['reel'=>'#F97316','post'=>'#3B82F6','story'=>'#14B8A6','video'=>'#8B5CF6','carousel'=>'#EC4899','website'=>'#0EA5E9','software'=>'#14B8A6'];
                        $typeColor = $typeColors[$task->type] ?? '#4F6DF0';
                    @endphp
                    <div class="badge-pill" style="background:{{ $typeColor }}18;color:{{ $typeColor }};border:1px solid {{ $typeColor }}30">
                        <i class="fas fa-paperclip" style="font-size:11px"></i> {{ ucfirst($task->type) }}
                    </div>
                    @if($isUrgentTask)
                        <div class="badge-pill" style="background:rgba(239,68,68,0.12);color:#dc2626;border:1px solid rgba(239,68,68,0.24)">
                            <i class="fas fa-bolt" style="font-size:10px"></i> Urgent Task
                        </div>
                    @endif
                    <div class="badge-pill" style="background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid rgba(34,197,94,0.3)">
                        <i class="fas fa-check" style="font-size:10px"></i> Ready for Approval
                    </div>
                </div>
            </div>
        </div>

        @if($isUrgentTask)
            <div class="card premium" style="margin-bottom:16px;padding:16px;border-color:rgba(239,68,68,0.22);background:linear-gradient(135deg, rgba(254,242,242,0.96) 0%, rgba(255,255,255,0.96) 100%)">
                <div class="section-title"><i class="fas fa-bolt" style="opacity:0.75;color:#dc2626"></i> Urgent Task Context</div>
                <div class="pill-wrap">
                    <span class="pill" style="background:rgba(239,68,68,0.12);color:#dc2626">Created by {{ $task->creator?->name ?? 'Unknown user' }}</span>
                    @if($task->urgent_requested_by)
                        <span class="pill" style="background:rgba(249,115,22,0.12);color:#ea580c">Requested by {{ $task->urgent_requested_by }}</span>
                    @endif
                    @if($task->assignee)
                        <span class="pill" style="background:rgba(14,165,233,0.12);color:#0284c7">Handled by {{ $task->assignee->name }}</span>
                    @endif
                </div>
            </div>
        @endif

        {{-- MEDIA VIEWER --}}
        @php
            $mediaItems = $task->media;
            $media = $mediaItems->first();
            $hasMedia = $mediaItems->isNotEmpty();
        @endphp
        @if($isDevType)
            <div class="card premium" style="margin-bottom:16px">
                <div class="section-title"><i class="fas fa-note-sticky" style="opacity:0.6"></i> Developer Submission Notes</div>
                <div class="section-card" style="border-left:4px solid #0EA5E9;margin-bottom:10px">
                    {{ $task->dev_submission_notes ?: ($task->project_notes ?: 'No developer notes submitted yet.') }}
                </div>
                <div style="display:grid;gap:8px">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px">
                        <span style="font-size:12px;color:var(--text)"><i class="fas fa-link" style="margin-right:6px;color:var(--text3)"></i> Submitted URL</span>
                        <span style="font-size:11px;font-weight:700;color:{{ $task->dev_submission_link ? '#16a34a' : '#ef4444' }}">{{ $task->dev_submission_link ? 'Provided' : 'Not Provided' }}</span>
                    </div>
                    @if($task->dev_submission_link)
                        @php
                            $devUrlRaw = trim((string) $task->dev_submission_link);
                            $devUrl = \Illuminate\Support\Str::startsWith($devUrlRaw, ['http://', 'https://']) ? $devUrlRaw : ('https://' . $devUrlRaw);
                        @endphp
                        <a href="{{ $devUrl }}" target="_blank" rel="noopener" class="btn-sec" style="width:100%;padding:10px 12px;font-size:12px;border-radius:8px;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none">
                            <i class="fas fa-up-right-from-square"></i> Open Developer Link
                        </a>
                    @endif
                </div>
            </div>
        @else
            @if($media)
                <div class="card" style="margin-bottom:16px;padding:0;overflow:hidden">
                    <div class="media-viewer">
                        <div class="media-viewer-content">
                            <div id="mediaPreview" style="width:100%;height:100%"></div>
                        </div>
                    </div>
                    <div class="media-toolbar">
                        <span><i class="fas fa-file"></i> <strong id="mediaName">{{ $media->name }}</strong></span>
                        <a id="mediaDownload" href="{{ $media->getUrl() }}" data-type="{{ $media->isVideo() ? 'video' : ($media->isImage() ? 'image' : 'file') }}" download class="btn-link" style="color:var(--primary);text-decoration:none;font-size:11px;font-weight:600">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </div>
                </div>

                @if($mediaItems->count() > 1)
                    <div class="card" style="margin-bottom:16px">
                        <div class="section-title"><i class="fas fa-images" style="opacity:0.6"></i> All Media ({{ $mediaItems->count() }})</div>
                        <div class="media-thumbs" id="mediaThumbs">
                            @foreach($mediaItems as $index => $m)
                                @php
                                    $mType = $m->isVideo() ? 'video' : ($m->isImage() ? 'image' : ($m->isPdf() ? 'pdf' : 'file'));
                                    $typeLabel = $m->isVideo() ? 'Video' : ($m->isImage() ? 'Image' : ($m->isPdf() ? 'PDF' : 'File'));
                                    $typeIcon = $m->isVideo() ? 'fa-video' : ($m->isImage() ? 'fa-image' : ($m->isPdf() ? 'fa-file-pdf' : 'fa-file'));
                                @endphp
                                <div class="media-thumb {{ $index === 0 ? 'active' : '' }}" data-url="{{ $m->getUrl() }}" data-type="{{ $mType }}" data-name="{{ $m->name ?: $m->file_name }}">
                                    @if($m->isImage())
                                        <img src="{{ $m->getUrl() }}" alt="{{ $m->name ?: 'Media' }}" loading="lazy">
                                    @elseif($m->isVideo())
                                        <video src="{{ $m->getUrl() }}" muted preload="metadata"></video>
                                        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;background:rgba(0,0,0,0.35)"><i class="fa-solid fa-play"></i></div>
                                    @elseif($m->isPdf())
                                        <div style="height:100%;display:flex;align-items:center;justify-content:center;background:var(--card2)">
                                            <i class="fa-solid fa-file-pdf" style="font-size:24px;color:#EF4444"></i>
                                        </div>
                                    @else
                                        <div style="height:100%;display:flex;align-items:center;justify-content:center;background:var(--card2)">
                                            <i class="fa-solid {{ $typeIcon }}" style="font-size:22px;color:var(--text3)"></i>
                                        </div>
                                    @endif
                                    <div class="thumb-badge"><i class="fa-solid {{ $typeIcon }}" style="font-size:9px"></i> {{ $typeLabel }}</div>
                                    <div class="thumb-name">{{ $m->name ?: $m->file_name }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="card" style="margin-bottom:16px;background:rgba(239,68,68,0.06);border:2px dashed rgba(239,68,68,0.3);padding:40px;text-align:center">
                    <div style="font-size:40px;margin-bottom:12px;opacity:0.6"><i class="fas fa-image" style="color:var(--text3)"></i></div>
                    <div style="font-size:13px;font-weight:600;color:#ef4444;margin-bottom:4px">No Media Uploaded</div>
                    <div style="font-size:12px;color:var(--text2)">Designer didn't upload design file</div>
                </div>
            @endif
        @endif

        {{-- TASK DETAILS GRID --}}
        <div class="task-meta-grid">
            <div class="meta-item">
                <span class="meta-label"><i class="fas fa-building" style="margin-right:4px;opacity:0.5"></i> Client</span>
                <span class="meta-value">{{ $task->client?->name ?? 'Not specified' }}</span>
            </div>
            <div class="meta-item">
                @php
                    $assigneeRole = $task->assignee?->role
                        ? \Illuminate\Support\Str::title(str_replace('_', ' ', $task->assignee->role))
                        : 'Assignee';
                @endphp
                <span class="meta-label"><i class="fas fa-palette" style="margin-right:4px;opacity:0.5"></i> {{ $assigneeRole }}</span>
                <span class="meta-value">{{ $task->assignee?->name ?? 'Unassigned' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label"><i class="fas fa-user" style="margin-right:4px;opacity:0.5"></i> {{ $creatorRoleLabel }}</span>
                <span class="meta-value">{{ $task->creator?->name ?? 'Not specified' }}</span>
            </div>
            @if($isUrgentTask && $task->urgent_requested_by)
                <div class="meta-item">
                    <span class="meta-label"><i class="fas fa-bell" style="margin-right:4px;opacity:0.5"></i> Requested By</span>
                    <span class="meta-value">{{ $task->urgent_requested_by }}</span>
                </div>
            @endif
            <div class="meta-item">
                <span class="meta-label"><i class="fas fa-calendar-alt" style="margin-right:4px;opacity:0.5"></i> Deadline</span>
                <span class="meta-value">{{ $task->deadline?->format('d M Y') ?? 'Not set' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label"><i class="fas fa-clipboard-check" style="margin-right:4px;opacity:0.5"></i> Submitted</span>
                <span class="meta-value">{{ $task->submitted_at?->format('d M, h:i A') ?? 'N/A' }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label"><i class="fas fa-clock" style="margin-right:4px;opacity:0.5"></i> Time Ago</span>
                <span class="meta-value">{{ $task->submitted_at?->diffForHumans() ?? 'N/A' }}</span>
            </div>
        </div>

        @if(!$isDevType)
            <div class="card premium" style="margin-bottom:16px">
                <div class="section-title"><i class="fas {{ $isUrgentTask ? 'fa-bolt' : 'fa-align-left' }}" style="opacity:0.6"></i> {{ $isUrgentTask ? 'Urgent Brief' : 'Content Brief' }}</div>
                <div class="section-card" style="border-left:4px solid {{ $isUrgentTask ? '#dc2626' : 'var(--primary)' }}">
                    {{ $isUrgentTask ? ($urgentBrief ?: 'No urgent brief provided.') : ($task->brief ?? 'No brief provided.') }}
                </div>
            </div>
        @else
            <div class="card premium" style="margin-bottom:16px">
                <div class="section-title"><i class="fas fa-code" style="opacity:0.6"></i> Development Specification</div>
                <div class="section-card" style="border-left:4px solid #0EA5E9">
                    {{ $task->project_notes ?? $task->brief ?? 'No development notes provided.' }}
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
                    <div style="padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px;font-size:12px">
                        <div style="font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase">Business Type</div>
                        <div style="margin-top:4px;color:var(--text)">{{ $task->business_type ?: 'Not specified' }}</div>
                    </div>
                    <div style="padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px;font-size:12px">
                        <div style="font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase">Preferred Tech</div>
                        <div style="margin-top:4px;color:var(--text)">{{ $task->preferred_tech ?: 'Not specified' }}</div>
                    </div>
                    <div style="padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px;font-size:12px">
                        <div style="font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase">Tech Stack</div>
                        <div style="margin-top:4px;color:var(--text)">{{ $task->tech_stack ?: 'Not specified' }}</div>
                    </div>
                    <div style="padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px;font-size:12px">
                        <div style="font-size:10px;font-weight:700;color:var(--text3);text-transform:uppercase">Modules</div>
                        <div style="margin-top:4px;color:var(--text)">{{ $task->modules ?: 'Not specified' }}</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- PLATFORM & SOCIAL MEDIA LINKS --}}
        @php
            $platforms = is_array($task->platform) ? $task->platform : (is_string($task->platform) ? json_decode($task->platform, true) : []);
            $platColors = ['instagram'=>'#E1306C','facebook'=>'#1877F2','linkedin'=>'#0077B5','twitter'=>'#000000','youtube'=>'#FF0000','whatsapp'=>'#25D366','tiktok'=>'#000000'];
            $platIcons  = ['instagram'=>'fa-instagram','facebook'=>'fa-facebook-f','linkedin'=>'fa-linkedin-in','twitter'=>'fa-x-twitter','youtube'=>'fa-youtube','whatsapp'=>'fa-whatsapp','tiktok'=>'fa-tiktok'];
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
        @endphp
        @if(!empty($platforms))
            <div class="card" style="margin-bottom:16px">
                <div style="font-size:12px;font-weight:700;color:var(--text3);margin-bottom:10px"><i class="fas fa-share-alt" style="margin-right:4px"></i> PLATFORMS & SOCIAL MEDIA LINKS</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px">
                    @foreach($platforms as $plat)
                        @php $pc = $platColors[$plat] ?? '#4F6DF0'; $pi = $platIcons[$plat] ?? 'fa-globe'; @endphp
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $pc }}18;color:{{ $pc }};border:1px solid {{ $pc }}40">
                            <i class="fa-brands {{ $pi }}"></i> {{ ucfirst($plat) }}
                        </span>
                    @endforeach
                </div>
                @if($selectedSocialLinks->isNotEmpty())
                    <div style="display:grid;gap:8px">
                        @foreach($selectedSocialLinks as $sl)
                            @php
                                $info = $sl->getPlatformIcon();
                                $accountLabel = $sl->label ?: str_replace(['https://', 'http://', 'www.'], '', $sl->url);
                            @endphp
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 14px;background:var(--card2);border-radius:8px;border:1px solid {{ $info['color'] }}24;flex-wrap:wrap">
                                <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1">
                                    <span style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:{{ $info['color'] }}20;color:{{ $info['color'] }};font-size:14px;flex-shrink:0">
                                        <i class="fa-brands {{ $info['icon'] }}"></i>
                                    </span>
                                    <div style="flex:1;min-width:0">
                                        <div style="font-size:12px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                            <span>{{ ucfirst($sl->platform) }}</span>
                                            <span style="font-weight:600;color:var(--text2)">{{ $accountLabel }}</span>
                                            @if($sl->is_primary)
                                                <span style="font-size:9px;font-weight:800;background:#F59E0B18;border:1px solid #F59E0B40;color:#D97706;padding:2px 7px;border-radius:999px">Primary</span>
                                            @endif
                                        </div>
                                        <div style="font-size:11px;color:var(--text3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $sl->url }}</div>
                                    </div>
                                </div>
                                <a href="{{ $sl->url }}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:5px;padding:6px 10px;border-radius:8px;border:1px solid {{ $info['color'] }}30;background:{{ $info['color'] }}12;color:{{ $info['color'] }};text-decoration:none;font-size:11px;font-weight:700;white-space:nowrap">
                                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px"></i> Visit
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- CONTENT BRIEF --}}
        {{-- REFERENCE LINKS --}}
        @if(!empty($task->reference_links))
            @php
                $links = is_array($task->reference_links) ? $task->reference_links : json_decode($task->reference_links, true);
            @endphp
            @if(!empty($links))
                <div class="card" style="margin-bottom:16px">
                    <div class="section-title"><i class="fas fa-link" style="opacity:0.6"></i> Reference Links</div>
                    <div style="display:flex;flex-direction:column;gap:6px">
                        @foreach($links as $link)
                            @if(!empty($link))
                                <a href="{{ $link }}" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:var(--card2);border-radius:8px;border:1px solid var(--border);color:var(--primary);font-size:12px;text-decoration:none;word-break:break-all">
                                    <i class="fas fa-external-link-alt" style="flex-shrink:0;font-size:11px"></i> {{ $link }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        @if(!$isDevType)
            {{-- CAPTIONS & HASHTAGS --}}
            <div class="content-grid" style="margin-bottom:16px">
                <div class="card premium">
                    <div class="section-title"><i class="fas fa-pen-nib" style="opacity:0.6"></i> Caption</div>
                    <div class="section-card" style="margin-bottom:12px">
                        {{ $task->caption ?? 'No caption provided.' }}
                    </div>
                </div>
                <div class="card premium">
                    <div class="section-title"><i class="fas fa-hashtag" style="opacity:0.6"></i> Hashtags</div>
                    @php
                        $htags = is_array($task->hashtags) ? $task->hashtags : array_filter(explode(' ', $task->hashtags ?? ''));
                    @endphp
                    <div class="pill-wrap">
                        @forelse($htags as $htag)
                            @if(trim($htag))
                                <span class="pill">{{ trim($htag) }}</span>
                            @endif
                        @empty
                            <span style="font-size:12px;color:var(--text3)">No hashtags provided.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        {{-- COMMENTS SECTION --}}
        @if($task->comments->count() > 0)
            <div class="card">
                <div style="font-size:12px;font-weight:700;color:var(--text3);margin-bottom:12px"><i class="fas fa-comments" style="margin-right:4px"></i> COMMENTS & NOTES ({{ $task->comments->count() }})</div>
                @foreach($task->comments as $comment)
                    <div class="comment-item">
                        <div class="comment-avatar" style="background:{{ $comment->user->avatar_color ?? 'var(--primary)' }}">
                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                        </div>
                        <div style="flex:1">
                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
                                <span style="font-weight:600;font-size:12px;color:var(--text)">{{ $comment->user->name }}</span>
                                <span style="font-size:9px;padding:2px 6px;background:var(--primary)18;color:var(--primary);border-radius:4px;font-weight:600">{{ ucfirst($comment->user->role) }}</span>
                                <span style="font-size:11px;color:var(--text3)">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="font-size:12px;color:var(--text2);line-height:1.5">{{ $comment->body }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- RIGHT COLUMN: Approval Action --}}
    <div class="right-panel">
        <div class="card" style="margin-bottom:16px;background:linear-gradient(135deg, rgba(34,197,94,0.08) 0%, rgba(79,109,240,0.06) 100%);border:1px solid rgba(34,197,94,0.2)">
            @if($isDevType)
                @php
                    $rawLiveUrl = trim((string) ($task->dev_submission_link ?? ''));
                    $liveUrl = $rawLiveUrl
                        ? (Str::startsWith($rawLiveUrl, ['http://', 'https://']) ? $rawLiveUrl : ('https://' . $rawLiveUrl))
                        : null;
                    $hasLiveUrl = !empty($liveUrl);
                    $domainHost = $hasLiveUrl ? parse_url($liveUrl, PHP_URL_HOST) : null;
                    $domainPurchased = (bool) $task->domain_purchased;
                    $hostingAccess = (bool) $task->hosting_access;
                @endphp

                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
                    <span style="font-size:20px"><i class="fas fa-server" style="color:#0EA5E9"></i></span>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:var(--text)">Deployment Review</div>
                        <div style="font-size:11px;color:var(--text3)">Live/server and domain verification</div>
                    </div>
                </div>

                <div style="display:grid;gap:8px;margin-bottom:14px">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px">
                        <span style="font-size:12px;color:var(--text)"><i class="fas fa-link" style="margin-right:6px;color:var(--text3)"></i> Live link provided</span>
                        <span style="font-size:11px;font-weight:700;color:{{ $hasLiveUrl ? '#16a34a' : '#ef4444' }}">{{ $hasLiveUrl ? 'YES' : 'NO' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px">
                        <span style="font-size:12px;color:var(--text)"><i class="fas fa-globe" style="margin-right:6px;color:var(--text3)"></i> Domain</span>
                        <span style="font-size:11px;font-weight:700;color:var(--text)">{{ $domainHost ?: 'Not detected' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px">
                        <span style="font-size:12px;color:var(--text)"><i class="fas fa-receipt" style="margin-right:6px;color:var(--text3)"></i> Domain purchased</span>
                        <span style="font-size:11px;font-weight:700;color:{{ $domainPurchased ? '#16a34a' : '#ef4444' }}">{{ $domainPurchased ? 'YES' : 'NO' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px">
                        <span style="font-size:12px;color:var(--text)"><i class="fas fa-key" style="margin-right:6px;color:var(--text3)"></i> Hosting access</span>
                        <span style="font-size:11px;font-weight:700;color:{{ $hostingAccess ? '#16a34a' : '#ef4444' }}">{{ $hostingAccess ? 'YES' : 'NO' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px">
                        <span style="font-size:12px;color:var(--text)"><i class="fas fa-signal" style="margin-right:6px;color:var(--text3)"></i> Live on server</span>
                        <span style="font-size:11px;font-weight:700;color:{{ $hasLiveUrl ? '#16a34a' : '#ef4444' }}">{{ $hasLiveUrl ? 'LIVE LINK ADDED' : 'NOT CONFIRMED' }}</span>
                    </div>
                    @if($task->launch_date)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#fff;border:1px solid var(--border);border-radius:8px">
                            <span style="font-size:12px;color:var(--text)"><i class="fas fa-calendar-check" style="margin-right:6px;color:var(--text3)"></i> Launch date</span>
                            <span style="font-size:11px;font-weight:700;color:var(--text)">{{ \Illuminate\Support\Carbon::parse($task->launch_date)->format('d M Y') }}</span>
                        </div>
                    @endif
                </div>

                @if($hasLiveUrl)
                    <a href="{{ $liveUrl }}" target="_blank" rel="noopener" class="btn-sec" style="width:100%;padding:10px 12px;font-size:12px;border-radius:8px;margin-bottom:10px;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none">
                        <i class="fas fa-up-right-from-square"></i> Open Submitted Link
                    </a>

                    <form method="POST" action="{{ route('admin.approvals.approve', $task) }}" id="approveForm" style="margin:0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-primary" style="width:100%;padding:14px;font-size:13px;font-weight:600;border-radius:10px;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:10px;transition:all 0.3s;background:linear-gradient(135deg, #22c55e 0%, #16a34a 100%);border:none;cursor:pointer;box-shadow:0 4px 12px rgba(34,197,94,0.3)">
                            <i class="fas fa-check-circle"></i> Approve & Complete
                        </button>
                    </form>
                @else
                    <div style="background:#fff;border:2px dashed rgba(239,68,68,0.3);border-radius:8px;padding:12px;margin-bottom:16px;text-align:center">
                        <div style="font-size:24px;margin-bottom:4px"><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i></div>
                        <div style="font-size:12px;color:#ef4444;font-weight:600">Missing Live URL</div>
                        <div style="font-size:11px;color:var(--text3);margin-top:2px">Cannot approve without submitted deployment link</div>
                    </div>

                    <button type="button" disabled class="btn-primary" style="width:100%;padding:14px;font-size:13px;border-radius:10px;opacity:0.4;cursor:not-allowed;display:flex;align-items:center;justify-content:center;gap:8px">
                        <i class="fas fa-ban"></i> Cannot Approve
                    </button>
                @endif
            @else
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
                    <span style="font-size:20px"><i class="fas fa-check-circle" style="color:#22c55e"></i></span>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:var(--text)">Final Approval</div>
                        <div style="font-size:11px;color:var(--text3)">Marketing materials review</div>
                    </div>
                </div>

                @if($hasMedia)
                    <div style="background:#fff;border:1px solid rgba(34,197,94,0.2);border-radius:8px;padding:12px;margin-bottom:16px;text-align:center">
                        <div style="font-size:28px;margin-bottom:4px"><i class="fas fa-image" style="color:#22c55e"></i></div>
                        <div style="font-size:12px;color:#22c55e;font-weight:600">Media Ready</div>
                        <div style="font-size:11px;color:var(--text3);margin-top:2px">{{ $task->media()->count() }} {{ Str::plural('file', $task->media()->count()) }} uploaded</div>
                    </div>

                    @if($hasCaption && $hasHashtags)
                        <form method="POST" action="{{ route('admin.approvals.approve', $task) }}" id="approveForm" style="margin:0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-primary" style="width:100%;padding:14px;font-size:13px;font-weight:600;border-radius:10px;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:10px;transition:all 0.3s;background:linear-gradient(135deg, #22c55e 0%, #16a34a 100%);border:none;cursor:pointer;box-shadow:0 4px 12px rgba(34,197,94,0.3)">
                                <i class="fas fa-check-circle"></i> Approve & Complete
                            </button>
                        </form>
                    @else
                        <div style="background:#fff;border:2px dashed rgba(245,158,11,0.35);border-radius:8px;padding:12px;margin-bottom:16px;text-align:center">
                            <div style="font-size:24px;margin-bottom:4px"><i class="fas fa-pen-to-square" style="color:#F59E0B"></i></div>
                            <div style="font-size:12px;color:#D97706;font-weight:600">Caption And Hashtags Required</div>
                            <div style="font-size:11px;color:var(--text3);margin-top:2px">Keep approval locked until both caption and hashtags are added.</div>
                        </div>

                        <button type="button" disabled class="btn-primary" style="width:100%;padding:14px;font-size:13px;border-radius:10px;opacity:0.4;cursor:not-allowed;display:flex;align-items:center;justify-content:center;gap:8px">
                            <i class="fas fa-lock"></i> Approval Locked
                        </button>
                    @endif
                @else
                    <div style="background:#fff;border:2px dashed rgba(239,68,68,0.3);border-radius:8px;padding:12px;margin-bottom:16px;text-align:center">
                        <div style="font-size:24px;margin-bottom:4px"><i class="fas fa-exclamation-triangle" style="color:#ef4444"></i></div>
                        <div style="font-size:12px;color:#ef4444;font-weight:600">Missing Media</div>
                        <div style="font-size:11px;color:var(--text3);margin-top:2px">Cannot approve without design</div>
                    </div>

                    <button type="button" disabled class="btn-primary" style="width:100%;padding:14px;font-size:13px;border-radius:10px;opacity:0.4;cursor:not-allowed;display:flex;align-items:center;justify-content:center;gap:8px">
                        <i class="fas fa-ban"></i> Cannot Approve
                    </button>
                @endif
            @endif
        </div>

        {{-- REJECT / REQUEST REVISION --}}
        <div class="card" style="margin-bottom:16px;border:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
                <span style="font-size:18px"><i class="fas fa-sync-alt" style="color:var(--text2)"></i></span>
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text)">Request Revision</div>
                    <div style="font-size:11px;color:var(--text3)">Send back to designer with feedback</div>
                </div>
            </div>

            @if($task->revision_count > 0)
                <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#F59E0B10;border:1px solid #F59E0B30;border-radius:8px;margin-bottom:12px">
                    <i class="fa-solid fa-rotate-left" style="color:#F59E0B;font-size:12px"></i>
                    <span style="font-size:11px;font-weight:600;color:#D97706">Already revised {{ $task->revision_count }} {{ Str::plural('time', $task->revision_count) }}</span>
                </div>
            @endif

            <button type="button" id="showRejectBtn" onclick="document.getElementById('rejectSection').style.display='block'; this.style.display='none'" class="btn-sec" style="width:100%;padding:12px;font-size:12.5px;font-weight:600;border-radius:8px;display:flex;align-items:center;justify-content:center;gap:8px;border-color:var(--red);color:var(--red)">
                <i class="fas fa-times-circle"></i> Reject & Request Changes
            </button>

            <div id="rejectSection" style="display:none;margin-top:12px">
                <form method="POST" action="{{ route('admin.approvals.reject', $task) }}" id="rejectForm">
                    @csrf
                    @method('PATCH')
                    <div style="margin-bottom:10px">
                        <label style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:6px">What needs to change?</label>
                        <textarea name="rejection_reason" rows="4" required placeholder="Be specific — e.g. 'Logo placement needs to be top-right, font should be bolder, wrong brand color used...'" style="width:100%;padding:12px;border:1.5px solid var(--border);border-radius:8px;font-size:12.5px;font-family:inherit;resize:vertical;background:var(--card2);color:var(--text);line-height:1.5;box-sizing:border-box"></textarea>
                    </div>

                    {{-- Quick feedback chips --}}
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px">
                        <button type="button" class="revision-chip" onclick="addFeedback('Wrong colors / branding')"><i class="fas fa-palette" style="font-size:10px"></i> Wrong colors</button>
                        <button type="button" class="revision-chip" onclick="addFeedback('Text/copy needs changes')"><i class="fas fa-pen" style="font-size:10px"></i> Text changes</button>
                        <button type="button" class="revision-chip" onclick="addFeedback('Layout/composition issues')"><i class="fas fa-ruler-combined" style="font-size:10px"></i> Layout issue</button>
                        <button type="button" class="revision-chip" onclick="addFeedback('Low resolution / quality')"><i class="fas fa-search-plus" style="font-size:10px"></i> Quality issue</button>
                        <button type="button" class="revision-chip" onclick="addFeedback('Wrong format/dimensions')"><i class="fas fa-expand-arrows-alt" style="font-size:10px"></i> Wrong size</button>
                        <button type="button" class="revision-chip" onclick="addFeedback('Missing elements from brief')"><i class="fas fa-clipboard-list" style="font-size:10px"></i> Missing elements</button>
                    </div>

                    <button type="submit" class="btn-primary" style="width:100%;padding:12px;font-size:12.5px;font-weight:600;border-radius:8px;background:var(--red);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px">
                        <i class="fas fa-paper-plane"></i> Send Back for Revision
                    </button>
                </form>
            </div>
        </div>

        <a href="{{ route('admin.approvals') }}" class="btn-sec" style="width:100%;text-align:center;padding:10px;font-size:12px;border-radius:8px;margin-bottom:16px">
            ← Back to List
        </a>

        {{-- TIMELINE/CHECKLIST --}}
        <div class="card" style="background:var(--card2);border:1px solid var(--border)">
            <div style="font-size:12px;font-weight:700;color:var(--text3);margin-bottom:12px"><i class="fas fa-clock" style="margin-right:4px"></i> APPROVAL TIMELINE</div>

            @php
                $submittedRole = $task->assignee?->role
                    ? \Illuminate\Support\Str::title(str_replace('_', ' ', $task->assignee->role))
                    : 'Assignee';
                $submittedRoleIcon = match ($task->assignee?->role) {
                    'developer' => 'fa-code',
                    'designer' => 'fa-palette',
                    'strategist' => 'fa-lightbulb',
                    'admin' => 'fa-user-shield',
                    default => 'fa-user',
                };
            @endphp

            <div style="display:flex;gap:16px;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                <div style="text-align:center;flex-shrink:0">
                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(34,197,94,0.2);display:flex;align-items:center;justify-content:center;font-size:14px"><i class="fas {{ $submittedRoleIcon }}" style="color:#22c55e"></i></div>
                </div>
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:600;color:var(--text)">{{ $submittedRole }} Submitted</div>
                    <div style="font-size:11px;color:var(--text3)">{{ $task->submitted_at?->format('d M Y, h:i A') ?? 'Not submitted yet' }}</div>
                </div>
            </div>

            <div style="display:flex;gap:16px;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                <div style="text-align:center;flex-shrink:0">
                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(168,85,247,0.2);display:flex;align-items:center;justify-content:center;font-size:14px"><i class="fas fa-user" style="color:#a855f7"></i></div>
                </div>
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:600;color:var(--text)">Strategist Approved</div>
                    <div style="font-size:11px;color:var(--text3)">{{ $task->status === 'pending_approval' ? 'Just now' : 'Awaiting approval' }}</div>
                </div>
            </div>

            <div style="display:flex;gap:16px">
                <div style="text-align:center;flex-shrink:0">
                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(59,130,246,0.2);display:flex;align-items:center;justify-content:center;font-size:14px"><i class="fas fa-check-circle" style="color:#3b82f6"></i></div>
                </div>
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:600;color:var(--text)">Your Approval</div>
                    <div style="font-size:11px;color:var(--text3)">Pending your action</div>
                </div>
            </div>
        </div>

        {{-- INFO BOX --}}
        <div class="card" style="background:rgba(59,130,246,0.06);border:1px solid rgba(59,130,246,0.15);margin-top:16px">
            <div style="display:flex;align-items:flex-start;gap:10px">
                <span style="font-size:18px;flex-shrink:0">ℹ️</span>
                <div>
                    <div style="font-size:12px;font-weight:700;color:var(--text3)">ABOUT THIS TASK</div>
                    <div style="font-size:12px;color:var(--text2);line-height:1.6;margin-top:8px">
                        When you approve, this task will be marked as <strong>completed</strong>. Both designer and strategist will receive confirmation.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function renderMediaPreview(url, type, name) {
    const preview = document.getElementById('mediaPreview');
    const label = document.getElementById('mediaName');
    const download = document.getElementById('mediaDownload');
    if (!preview) return;
    preview.innerHTML = '';

    if (label) label.textContent = name || 'Media file';
    if (download) {
        download.href = url;
        download.setAttribute('download', name || 'file');
    }

    if (type === 'video') {
        const video = document.createElement('video');
        video.src = url;
        video.controls = true;
        video.autoplay = true;
        video.playsInline = true;
        video.preload = 'auto';
        video.style.width = '100%';
        video.style.height = '100%';
        video.style.maxHeight = '70vh';
        video.style.background = '#000';
        video.style.borderRadius = '8px';
        video.style.outline = 'none';
        preview.appendChild(video);
        return;
    }

    if (type === 'image') {
        const img = document.createElement('img');
        img.src = url;
        img.alt = name || 'Design media';
        img.style.maxWidth = '100%';
        img.style.maxHeight = '70vh';
        img.style.objectFit = 'contain';
        preview.appendChild(img);
        return;
    }

    if (type === 'pdf') {
        const iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.style.width = '100%';
        iframe.style.height = '70vh';
        iframe.style.border = 'none';
        iframe.style.borderRadius = '8px';
        preview.appendChild(iframe);
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.style.textAlign = 'center';
    wrapper.style.padding = '40px';
    wrapper.style.width = '100%';
    wrapper.innerHTML =
        '<div style="font-size:48px;margin-bottom:12px"><i class="fa-solid fa-file"></i></div>' +
        '<div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:8px">' +
        (name || 'File') +
        '</div>' +
        '<a href="' + url + '" download="' + (name || 'file') + '" class="btn-primary" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;font-size:12px">' +
        '<i class="fas fa-download"></i> Download</a>';
    preview.appendChild(wrapper);
}

function initMediaPreview() {
    const thumbs = document.querySelectorAll('#mediaThumbs .media-thumb');
    if (!thumbs.length) {
        const defaultDownload = document.getElementById('mediaDownload');
        const defaultUrl = defaultDownload?.href;
        const defaultType = defaultDownload?.dataset.type || 'file';
        const defaultName = document.getElementById('mediaName')?.textContent?.trim() || 'Media';
        if (defaultUrl) {
            renderMediaPreview(defaultUrl, defaultType, defaultName);
        }
        return;
    }

    const first = thumbs[0];
    renderMediaPreview(first.dataset.url, first.dataset.type, first.dataset.name);

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            thumbs.forEach((t) => t.classList.remove('active'));
            thumb.classList.add('active');
            renderMediaPreview(thumb.dataset.url, thumb.dataset.type, thumb.dataset.name);
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initMediaPreview();
    const approveForm = document.getElementById('approveForm');
    if (approveForm) {
        approveForm.addEventListener('submit', function(e) {
            e.preventDefault();
            approveTaskAJAX(this);
        });
    }
});

async function approveTaskAJAX(form) {
    const confirmed = confirm('Approve this task for completion?');
    if (!confirmed) return;

    try {
        ajax.showLoadingState(form);

        const response = await fetch(form.action, {
            method: 'PATCH',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
        });

        if (!response.ok) {
            const contentType = response.headers.get('content-type');
            let errorMsg = 'Approval failed';

            if (contentType?.includes('application/json')) {
                const errorData = await response.json();
                errorMsg = errorData.message || errorMsg;
            }
            throw new Error(errorMsg);
        }

        ajax.hideLoadingState(form);

        const contentType = response.headers.get('content-type');
        if (contentType?.includes('application/json')) {
            await response.json();
        }

        // Show success
        ajax.showSuccess('Task approved and marked as completed!');

        // Reload page to show updated status
        setTimeout(() => {
            location.href = '{{ route('admin.approvals') }}';
        }, 1500);

    } catch (error) {
        ajax.hideLoadingState(form);
        console.error('Approval error:', error);
        ajax.showError(error.message || 'Failed to approve task');
    }
}

function addFeedback(text) {
    const ta = document.querySelector('#rejectForm textarea[name="rejection_reason"]');
    if (!ta) return;
    const current = ta.value.trim();
    if (current.includes(text)) return; // don't duplicate
    ta.value = current ? current + '\n• ' + text : '• ' + text;
    ta.focus();
}

document.addEventListener('DOMContentLoaded', function() {
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            rejectTaskAJAX(this);
        });
    }
});

async function rejectTaskAJAX(form) {
    const reason = form.querySelector('textarea[name="rejection_reason"]').value.trim();
    if (!reason) { alert('Please provide a reason for the revision.'); return; }
    if (!confirm('Send this task back for revision?')) return;

    try {
        if (typeof ajax !== 'undefined' && ajax.showLoadingState) ajax.showLoadingState(form);

        const response = await fetch(form.action, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ rejection_reason: reason }),
        });

        if (!response.ok) {
            const ct = response.headers.get('content-type');
            let msg = 'Rejection failed';
            if (ct?.includes('application/json')) {
                const d = await response.json();
                msg = d.message || msg;
            }
            throw new Error(msg);
        }

        if (typeof ajax !== 'undefined' && ajax.hideLoadingState) ajax.hideLoadingState(form);
        if (typeof ajax !== 'undefined' && ajax.showSuccess) ajax.showSuccess('Task sent back for revision!');

        setTimeout(() => { location.href = '{{ route("admin.approvals") }}'; }, 1500);
    } catch (error) {
        if (typeof ajax !== 'undefined' && ajax.hideLoadingState) ajax.hideLoadingState(form);
        console.error('Reject error:', error);
        if (typeof ajax !== 'undefined' && ajax.showError) ajax.showError(error.message || 'Failed to reject task');
        else alert(error.message || 'Failed to reject task');
    }
}
</script>
@endpush

@endsection
</div>
