@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
/* ── Publishing Command Hub ─────────────────────────── */
.pd-back{display:inline-flex;align-items:center;gap:6px;color:var(--text3);font-size:12.5px;text-decoration:none;margin-bottom:20px;font-weight:600;transition:all .2s;padding:6px 12px;border-radius:8px}
.pd-back:hover{color:var(--primary);background:var(--primary-dim)}

/* Top Banners */
.pd-banner{border-radius:var(--radius);padding:18px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;box-shadow:var(--shadow-sm);flex-wrap:wrap}
.pd-banner-published{background:linear-gradient(135deg,#059669 0%,#10B981 100%);color:#fff;box-shadow:0 4px 18px rgba(16,185,129,.25)}
.pd-banner-pending{background:linear-gradient(135deg,#2563EB 0%,#3B82F6 100%);color:#fff;box-shadow:0 4px 18px rgba(37,99,235,.25)}
.pd-banner-overdue{background:linear-gradient(135deg,#DC2626 0%,#EF4444 100%);color:#fff;box-shadow:0 4px 18px rgba(220,38,38,.25)}
.pd-banner-left{display:flex;align-items:center;gap:14px}
.pd-banner-icon{width:46px;height:46px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.pd-banner h3{font-size:16px;font-weight:800;margin:0 0 2px;letter-spacing:-.2px}
.pd-banner p{font-size:12px;opacity:.9;margin:0;font-weight:500}
.pd-banner-actions{display:flex;align-items:center;gap:10px}

/* Banner Action Buttons */
.pd-banner-btn{padding:9px 18px;border-radius:10px;font-size:12.5px;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;gap:7px;cursor:pointer;border:none;transition:all .2s;white-space:nowrap}
.pd-banner-btn-white{background:#fff;color:#0f172a;box-shadow:0 2px 10px rgba(0,0,0,.15)}
.pd-banner-btn-white:hover{background:#f8fafc;transform:translateY(-1px);box-shadow:0 4px 14px rgba(0,0,0,.2)}
.pd-banner-btn-outline{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3)}
.pd-banner-btn-outline:hover{background:rgba(255,255,255,.25)}

/* Header card */
.pd-header{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;margin-bottom:20px;position:relative;overflow:hidden}
.pd-header::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),#6882F5)}
.pd-title{font-size:20px;font-weight:800;color:var(--text);margin-bottom:10px;font-family:'Plus Jakarta Sans',sans-serif}
.pd-meta{display:flex;flex-wrap:wrap;gap:18px;font-size:12.5px;color:var(--text3)}
.pd-meta-item{display:flex;align-items:center;gap:6px;font-weight:500}
.pd-meta-item i{font-size:12px;width:14px;text-align:center}

/* Grid layout */
.pd-grid{display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start}

/* Cards */
.pd-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:20px;box-shadow:var(--shadow-sm);transition:border-color .2s}
.pd-card:hover{border-color:var(--border2)}
.pd-card-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);background:var(--card2)}
.pd-card-title{font-size:13.5px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:9px;text-transform:uppercase;letter-spacing:.4px}
.pd-card-title i{color:var(--primary);font-size:14px}
.pd-card-body{padding:20px}

/* Creative Assets Display */
.pd-asset-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px}
.pd-asset-box{position:relative;border-radius:12px;overflow:hidden;background:var(--card2);border:1px solid var(--border);cursor:pointer;aspect-ratio:1/1;transition:all .25s ease}
.pd-asset-box:hover{transform:scale(1.03);border-color:var(--primary);box-shadow:0 6px 18px rgba(79,109,240,.2)}
.pd-asset-box img,.pd-asset-box video{width:100%;height:100%;object-fit:cover;display:block}
.pd-asset-overlay{position:absolute;inset:0;background:rgba(15,23,42,.45);display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;opacity:0;transition:opacity .2s;backdrop-filter:blur(2px)}
.pd-asset-box:hover .pd-asset-overlay{opacity:1}
.pd-asset-badge{position:absolute;bottom:6px;right:6px;background:rgba(0,0,0,.75);color:#fff;font-size:9px;padding:3px 6px;border-radius:5px;font-weight:700}
.pd-asset-empty{padding:40px 20px;text-align:center;color:var(--text3);background:var(--card2);border-radius:12px;border:1px dashed var(--border)}

/* Copy & Hashtags */
.pd-copy-block{background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:14px;position:relative}
.pd-copy-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.pd-copy-label{font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:.5px}
.pd-copy-content{font-size:13.5px;color:var(--text);line-height:1.6;white-space:pre-wrap;word-break:break-word}
.pd-btn-copy{padding:5px 11px;border-radius:7px;background:var(--card);border:1px solid var(--border);color:var(--text2);font-size:11px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all .15s}
.pd-btn-copy:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim)}
.pd-btn-copy.copied{background:#10B981;color:#fff;border-color:#10B981}

/* Platforms list */
.pd-platforms-list{display:flex;flex-direction:column;gap:10px}
.pd-platform-item{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:var(--card2);border:1px solid var(--border);border-radius:12px;transition:all .2s}
.pd-platform-item:hover{border-color:var(--border2);transform:translateX(2px)}
.pd-platform-left{display:flex;align-items:center;gap:12px}
.pd-platform-icon-wrap{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px}
.pd-platform-name-wrap{font-size:13.5px;font-weight:700;color:var(--text)}
.pd-platform-tag{padding:3px 9px;border-radius:6px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.3px}
.pd-platform-link-btn{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:600;color:var(--primary);text-decoration:none;padding:5px 10px;border-radius:6px}
.pd-platform-link-btn:hover{background:var(--primary-dim);text-decoration:underline}

/* Sidebar */
.pd-sidebar{position:sticky;top:20px;display:flex;flex-direction:column;gap:16px}
.pd-sidebar-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;overflow:hidden;box-shadow:var(--shadow-sm)}
.pd-sidebar-title{font-size:12px;font-weight:800;color:var(--text);margin-bottom:14px;display:flex;align-items:center;gap:7px;text-transform:uppercase;letter-spacing:.5px}
.pd-sidebar-title i{color:var(--primary);font-size:13px}

.pd-detail-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--border);font-size:12.5px}
.pd-detail-row:last-child{border-bottom:none}
.pd-detail-key{color:var(--text3);font-weight:500}
.pd-detail-val{font-weight:700;color:var(--text);text-align:right}

.pd-badge-client-yes{background:rgba(16,185,129,.12);color:#059669;border:1px solid rgba(16,185,129,.3);padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:4px}
.pd-badge-client-no{background:rgba(239,68,68,.1);color:#EF4444;border:1px solid rgba(239,68,68,.25);padding:3px 8px;border-radius:6px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:4px}
.pd-btn-toggle-client{font-size:10.5px;font-weight:700;padding:3px 8px;border-radius:6px;cursor:pointer;transition:all .18s;border:1px solid var(--border);display:inline-flex;align-items:center;gap:3px;white-space:nowrap}
.pd-btn-toggle-client.is-no{background:rgba(16,185,129,.1);color:#059669;border-color:rgba(16,185,129,.3)}
.pd-btn-toggle-client.is-no:hover{background:#10B981;color:#fff}
.pd-btn-toggle-client.is-yes{background:var(--card2);color:var(--text3);border-color:var(--border)}
.pd-btn-toggle-client.is-yes:hover{background:rgba(239,68,68,.1);color:#EF4444;border-color:rgba(239,68,68,.3)}

.pd-view-task{display:flex;align-items:center;justify-content:center;gap:6px;padding:11px;border-radius:10px;background:var(--card2);color:var(--text2);font-size:12px;font-weight:700;text-decoration:none;transition:all .2s;border:1px solid var(--border)}
.pd-view-task:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim)}

@media(max-width:960px){
    .pd-grid{grid-template-columns:1fr}
    .pd-sidebar{position:static}
    .pd-banner{flex-direction:column;align-items:stretch}
    .pd-banner-actions{justify-content:flex-start}
}
</style>
@endpush

@section('content')

@php
    $platforms = is_array($task->platform) ? $task->platform : (is_string($task->platform) ? [$task->platform] : []);
    $platformIcons = [
        'instagram' => 'fa-brands fa-instagram', 'facebook' => 'fa-brands fa-facebook-f',
        'linkedin'  => 'fa-brands fa-linkedin-in', 'twitter' => 'fa-brands fa-x-twitter',
        'tiktok'    => 'fa-brands fa-tiktok', 'youtube' => 'fa-brands fa-youtube',
        'pinterest' => 'fa-brands fa-pinterest-p', 'threads' => 'fa-brands fa-threads',
    ];
    $platformColors = [
        'instagram' => '#E1306C', 'facebook' => '#1877F2', 'linkedin' => '#0077B5',
        'twitter'   => '#000000', 'tiktok'   => '#010101', 'youtube'  => '#FF0000',
        'pinterest' => '#E60023', 'threads'  => '#000000',
    ];

    $isPublished = $task->status === 'published';
    $postDateCarbon = $task->post_date ? \Illuminate\Support\Carbon::parse($task->post_date) : null;
    $isOverdue = !$isPublished && $postDateCarbon && $postDateCarbon->isPast() && !$postDateCarbon->isToday();
    $isToday = !$isPublished && $postDateCarbon && $postDateCarbon->isToday();
@endphp

<a href="{{ route('strategist.publishing') }}" class="pd-back"><i class="fa-solid fa-arrow-left"></i> Publishing Queue</a>

{{-- Top Action Banner --}}
@if($isPublished)
    <div class="pd-banner pd-banner-published">
        <div class="pd-banner-left">
            <div class="pd-banner-icon"><i class="fa-solid fa-check-double"></i></div>
            <div>
                <h3>Published Live</h3>
                <p>This content has been marked as published on social media.</p>
            </div>
        </div>
        <div class="pd-banner-actions">
            <form method="POST" action="{{ route('strategist.publishing.unpublish', $task) }}" onsubmit="return confirm('Revert this task back to Ready to Publish?')" style="margin:0">
                @csrf
                <button type="submit" class="pd-banner-btn pd-banner-btn-outline"><i class="fa-solid fa-rotate-left"></i> Revert to Pending</button>
            </form>
        </div>
    </div>
@elseif($isOverdue)
    <div class="pd-banner pd-banner-overdue">
        <div class="pd-banner-left">
            <div class="pd-banner-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <h3>Due to Post</h3>
                <p>Scheduled date was {{ $postDateCarbon->format('M d, Y') }} ({{ $postDateCarbon->diffForHumans() }}). Ready to go live.</p>
            </div>
        </div>
        <div class="pd-banner-actions">
            <form method="POST" action="{{ route('strategist.publishing.publish', $task) }}" onsubmit="return confirmPublishWithClientCheck(event, this, {{ $task->isClientApproved() ? 'true' : 'false' }})" style="margin:0">
                @csrf
                <button type="submit" class="pd-banner-btn pd-banner-btn-white"><i class="fa-solid fa-rocket"></i> Mark as Published</button>
            </form>
        </div>
    </div>
@else
    <div class="pd-banner pd-banner-pending">
        <div class="pd-banner-left">
            <div class="pd-banner-icon"><i class="fa-solid fa-hourglass-half"></i></div>
            <div>
                <h3>Ready to Publish</h3>
                <p>{{ $postDateCarbon ? 'Scheduled for ' . $postDateCarbon->format('M d, Y') : 'Artwork approved and ready to post on social media.' }}</p>
            </div>
        </div>
        <div class="pd-banner-actions">
            <form method="POST" action="{{ route('strategist.publishing.publish', $task) }}" onsubmit="return confirmPublishWithClientCheck(event, this, {{ $task->isClientApproved() ? 'true' : 'false' }})" style="margin:0">
                @csrf
                <button type="submit" class="pd-banner-btn pd-banner-btn-white"><i class="fa-solid fa-rocket"></i> Mark as Published</button>
            </form>
        </div>
    </div>
@endif

{{-- Task Header --}}
<div class="pd-header">
    <div class="pd-title">{{ $task->title ?: 'Untitled Task' }}</div>
    <div class="pd-meta">
        <div class="pd-meta-item"><i class="fa-solid fa-building"></i> Client: <strong>{{ $task->client->name ?? '—' }}</strong></div>
        <div class="pd-meta-item"><i class="fa-solid fa-tag"></i> Type: <strong>{{ ucfirst($task->type) }}</strong></div>
        @if($task->creator)
            <div class="pd-meta-item"><i class="fa-solid fa-user-pen"></i> Strategist: <strong>{{ $task->creator->name }}</strong></div>
        @endif
        @if($task->assignee)
            <div class="pd-meta-item"><i class="fa-solid fa-palette"></i> Designer: <strong>{{ $task->assignee->name }}</strong></div>
        @endif
        @if($task->post_date)
            <div class="pd-meta-item" style="color:{{ $isOverdue ? '#EF4444' : 'inherit' }}">
                <i class="fa-regular fa-calendar-check"></i> Post Date: <strong>{{ $postDateCarbon->format('M d, Y') }}</strong>
            </div>
        @endif
    </div>
</div>

<div class="pd-grid">
    {{-- LEFT: Command Hub & Assets --}}
    <div class="pd-main">
        {{-- Creative Assets Card --}}
        <div class="pd-card">
            <div class="pd-card-header">
                <div class="pd-card-title"><i class="fa-solid fa-photo-film"></i> Approved Creatives & Artwork</div>
                @if($task->media && $task->media->count())
                    <span style="font-size:11px;font-weight:700;color:var(--text3)">{{ $task->media->count() }} {{ $task->media->count() > 1 ? 'Files' : 'File' }}</span>
                @endif
            </div>
            <div class="pd-card-body">
                @if($task->media && $task->media->count())
                    <div class="pd-asset-grid">
                        @foreach($task->media as $mediaItem)
                            @php
                                $isVid = (bool) $mediaItem->isVideo();
                                $url = $mediaItem->getUrl();
                            @endphp
                            <div class="pd-asset-box"
                                 onclick="window.openMediaLightbox('{{ addslashes($url) }}', '{{ addslashes($task->title) }}', {{ $isVid ? 'true' : 'false' }}, '{{ route('strategist.tasks.show', $task) }}')"
                                 title="Click to view full size">
                                @if($isVid)
                                    <video src="{{ $url }}" muted></video>
                                    <span class="pd-asset-badge"><i class="fa-solid fa-play"></i> Video</span>
                                @else
                                    <img src="{{ $url }}" alt="{{ $task->title }}" loading="lazy">
                                    <span class="pd-asset-badge"><i class="fa-solid fa-image"></i> Image</span>
                                @endif
                                <div class="pd-asset-overlay">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="pd-asset-empty">
                        <i class="fa-regular fa-image" style="font-size:32px;display:block;margin-bottom:8px;opacity:.5"></i>
                        No creative files uploaded for this task.
                    </div>
                @endif
            </div>
        </div>

        {{-- Copywriting & Hashtags Card --}}
        @if($task->caption || $task->hashtags)
            <div class="pd-card">
                <div class="pd-card-header">
                    <div class="pd-card-title"><i class="fa-solid fa-pen-nib"></i> Marketing Copy & Hashtags</div>
                    <button type="button" class="pd-btn-copy" onclick="copyText('{{ addslashes($task->caption . "\n\n" . $task->hashtags) }}', this)">
                        <i class="fa-regular fa-copy"></i> Copy All
                    </button>
                </div>
                <div class="pd-card-body">
                    @if($task->caption)
                        <div class="pd-copy-block">
                            <div class="pd-copy-header">
                                <span class="pd-copy-label">Caption</span>
                                <button type="button" class="pd-btn-copy" onclick="copyText('{{ addslashes($task->caption) }}', this)">
                                    <i class="fa-regular fa-copy"></i> Copy Caption
                                </button>
                            </div>
                            <div class="pd-copy-content">{{ $task->caption }}</div>
                        </div>
                    @endif

                    @if($task->hashtags)
                        <div class="pd-copy-block" style="margin-bottom:0">
                            <div class="pd-copy-header">
                                <span class="pd-copy-label">Hashtags</span>
                                <button type="button" class="pd-btn-copy" onclick="copyText('{{ addslashes($task->hashtags) }}', this)">
                                    <i class="fa-regular fa-copy"></i> Copy Hashtags
                                </button>
                            </div>
                            <div class="pd-copy-content" style="color:var(--primary);font-weight:600">{{ $task->hashtags }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Target Social Platforms Card --}}
        <div class="pd-card">
            <div class="pd-card-header">
                <div class="pd-card-title"><i class="fa-solid fa-share-nodes"></i> Target Platforms</div>
                <span style="font-size:11px;font-weight:700;color:var(--text3)">{{ count($platforms) }} Platforms</span>
            </div>
            <div class="pd-card-body">
                <div class="pd-platforms-list">
                    @forelse($platforms as $platform)
                        @php
                            $pIcon = $platformIcons[$platform] ?? 'fa-solid fa-globe';
                            $pColor = $platformColors[$platform] ?? '#6B7280';
                        @endphp
                        <div class="pd-platform-item">
                            <div class="pd-platform-left">
                                <div class="pd-platform-icon-wrap" style="background:{{ $pColor }}15;color:{{ $pColor }}">
                                    <i class="{{ $pIcon }}"></i>
                                </div>
                                <div>
                                    <div class="pd-platform-name-wrap">{{ ucfirst($platform) }}</div>
                                    <span class="pd-platform-tag" style="background:{{ $isPublished ? 'rgba(16,185,129,.1)' : 'rgba(245,158,11,.1)' }};color:{{ $isPublished ? '#059669' : '#D97706' }}">
                                        {{ $isPublished ? 'Published' : 'Target Platform' }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                @if($task->client && method_exists($task->client, 'socialLinks'))
                                    {{-- Optional client profile launcher --}}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="font-size:12.5px;color:var(--text3);text-align:center;padding:12px">No specific platforms selected.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Sidebar Details --}}
    <div class="pd-sidebar">
        <div class="pd-sidebar-card">
            <div class="pd-sidebar-title"><i class="fa-solid fa-circle-info"></i> Publishing Status</div>
            
            <div class="pd-detail-row">
                <span class="pd-detail-key">Current Status</span>
                <span class="pd-detail-val">
                    @if($isPublished)
                        <span style="color:#059669"><i class="fa-solid fa-check-double"></i> Published</span>
                    @elseif($isOverdue)
                        <span style="color:#EF4444"><i class="fa-solid fa-triangle-exclamation"></i> Due to Post</span>
                    @else
                        <span style="color:#D97706"><i class="fa-solid fa-hourglass-half"></i> Ready to Publish</span>
                    @endif
                </span>
            </div>

            @php $isClientApproved = $task->isClientApproved(); @endphp
            <div class="pd-detail-row" style="align-items:flex-start">
                <span class="pd-detail-key" style="margin-top:3px">Client Approved</span>
                <div style="text-align:right">
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                        <span class="pd-badge {{ $isClientApproved ? 'pd-badge-client-yes' : 'pd-badge-client-no' }}"
                              id="client-badge-{{ $task->id }}">
                            <i class="fa-solid {{ $isClientApproved ? 'fa-check' : 'fa-xmark' }}"></i>
                            {{ $isClientApproved ? 'Yes' : 'No' }}
                        </span>
                        <button type="button"
                                class="pd-btn-toggle-client {{ $isClientApproved ? 'is-yes' : 'is-no' }}"
                                id="client-toggle-btn-{{ $task->id }}"
                                onclick="toggleClientApproval({{ $task->id }}, this)">
                            {{ $isClientApproved ? 'Mark No' : 'Mark Yes' }}
                        </button>
                    </div>
                    <div id="client-approver-note-{{ $task->id }}" style="font-size:10.5px;color:var(--text3);margin-top:4px">
                        @if($isClientApproved)
                            Confirmed by {{ $task->clientApprover->name ?? 'Strategist' }}
                            @if($task->client_approved_at)
                                ({{ \Carbon\Carbon::parse($task->client_approved_at)->format('M d, g:i A') }})
                            @endif
                        @else
                            Pending client confirmation
                        @endif
                    </div>
                </div>
            </div>

            @if($task->post_date)
                <div class="pd-detail-row">
                    <span class="pd-detail-key">Scheduled Post Date</span>
                    <span class="pd-detail-val" style="color:{{ $isOverdue ? '#EF4444' : 'inherit' }}">
                        {{ $postDateCarbon->format('M d, Y') }}
                    </span>
                </div>
            @endif

            <div class="pd-detail-row">
                <span class="pd-detail-key">Client</span>
                <span class="pd-detail-val">{{ $task->client->name ?? '—' }}</span>
            </div>

            @if($task->assignee)
                <div class="pd-detail-row">
                    <span class="pd-detail-key">Designer</span>
                    <span class="pd-detail-val">{{ $task->assignee->name }}</span>
                </div>
            @endif

            @if($task->completed_at)
                <div class="pd-detail-row">
                    <span class="pd-detail-key">Approved On</span>
                    <span class="pd-detail-val">{{ $task->completed_at->format('M d, Y') }}</span>
                </div>
            @endif
        </div>

        {{-- 1-Click Action in Sidebar as well --}}
        <div class="pd-sidebar-card" style="text-align:center">
            @if($isPublished)
                <form method="POST" action="{{ route('strategist.publishing.unpublish', $task) }}" onsubmit="return confirm('Revert this task back to Ready to Publish?')" style="margin:0">
                    @csrf
                    <button type="submit" class="pd-banner-btn pd-banner-btn-outline" style="width:100%;justify-content:center;background:var(--card2);color:var(--text2);border:1px solid var(--border)">
                        <i class="fa-solid fa-rotate-left"></i> Revert to Pending
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('strategist.publishing.publish', $task) }}" onsubmit="return confirmPublishWithClientCheck(event, this, {{ $isClientApproved ? 'true' : 'false' }})" style="margin:0">
                    @csrf
                    <button type="submit" class="pd-banner-btn" style="width:100%;justify-content:center;background:linear-gradient(135deg,#10B981,#059669);color:#fff">
                        <i class="fa-solid fa-rocket"></i> Mark as Published
                    </button>
                </form>
            @endif
        </div>

        {{-- View Full Task --}}
        <a href="{{ route('strategist.tasks.show', $task) }}" class="pd-view-task">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Full Task Details
        </a>
    </div>
</div>

@push('scripts')
<script>
function copyText(text, btn) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('copied');
        }, 2000);
    }).catch(() => {
        alert('Failed to copy to clipboard.');
    });
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
            window.isClientApprovedState = data.is_approved;
            const badge = document.getElementById(`client-badge-${taskId}`);
            const note = document.getElementById(`client-approver-note-${taskId}`);
            if (badge) {
                if (data.is_approved) {
                    badge.className = 'pd-badge pd-badge-client-yes';
                    badge.innerHTML = '<i class="fa-solid fa-check"></i> Yes';
                    btn.className = 'pd-btn-toggle-client is-yes';
                    btn.innerHTML = 'Mark No';
                    if (note) note.innerHTML = `Confirmed by ${data.approved_by || 'You'} (${data.approved_at || 'Just now'})`;
                } else {
                    badge.className = 'pd-badge pd-badge-client-no';
                    badge.innerHTML = '<i class="fa-solid fa-xmark"></i> No';
                    btn.className = 'pd-btn-toggle-client is-no';
                    btn.innerHTML = 'Mark Yes';
                    if (note) note.innerHTML = 'Pending client confirmation';
                }
            }
        } else {
            alert(data.message || 'Failed to update client approval status');
            btn.innerHTML = originalText;
        }
    } catch (e) {
        console.error(e);
        btn.innerHTML = originalText;
    } finally {
        btn.disabled = false;
    }
}

function confirmPublishWithClientCheck(event, form, isApproved) {
    const approved = (typeof window.isClientApprovedState !== 'undefined') ? window.isClientApprovedState : isApproved;
    if (!approved) {
        return confirm('⚠️ Note: Client approval is currently marked as NO.\n\nAre you sure you want to mark this task as Published?');
    }
    return confirm('Mark this task as Published on social media?');
}
</script>
@endpush

@endsection
