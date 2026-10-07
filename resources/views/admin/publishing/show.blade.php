@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
/* ── Admin Publishing Hub ─────────────────────────── */
.aps-back{display:inline-flex;align-items:center;gap:6px;color:var(--text3);font-size:12.5px;text-decoration:none;margin-bottom:20px;font-weight:600;transition:all .2s;padding:6px 12px;border-radius:8px}
.aps-back:hover{color:var(--primary);background:var(--primary-dim)}

/* Top Banners */
.aps-banner{border-radius:var(--radius);padding:18px 24px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;box-shadow:var(--shadow-sm);flex-wrap:wrap}
.aps-banner-published{background:linear-gradient(135deg,#059669 0%,#10B981 100%);color:#fff;box-shadow:0 4px 18px rgba(16,185,129,.25)}
.aps-banner-pending{background:linear-gradient(135deg,#2563EB 0%,#3B82F6 100%);color:#fff;box-shadow:0 4px 18px rgba(37,99,235,.25)}
.aps-banner-overdue{background:linear-gradient(135deg,#DC2626 0%,#EF4444 100%);color:#fff;box-shadow:0 4px 18px rgba(220,38,38,.25)}
.aps-banner-left{display:flex;align-items:center;gap:14px}
.aps-banner-icon{width:46px;height:46px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.aps-banner h3{font-size:16px;font-weight:800;margin:0 0 2px;letter-spacing:-.2px}
.aps-banner p{font-size:12px;opacity:.9;margin:0;font-weight:500}
.aps-banner-actions{display:flex;align-items:center;gap:10px}

/* Banner Action Buttons */
.aps-banner-btn{padding:9px 18px;border-radius:10px;font-size:12.5px;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;gap:7px;cursor:pointer;border:none;transition:all .2s;white-space:nowrap}
.aps-banner-btn-white{background:#fff;color:#0f172a;box-shadow:0 2px 10px rgba(0,0,0,.15)}
.aps-banner-btn-white:hover{background:#f8fafc;transform:translateY(-1px);box-shadow:0 4px 14px rgba(0,0,0,.2)}
.aps-banner-btn-outline{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3)}
.aps-banner-btn-outline:hover{background:rgba(255,255,255,.25)}

/* Header Card */
.aps-header{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;margin-bottom:20px;position:relative;overflow:hidden}
.aps-header::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),#6882F5)}
.aps-title{font-size:20px;font-weight:800;color:var(--text);margin-bottom:10px;font-family:'Plus Jakarta Sans',sans-serif}
.aps-meta{display:flex;flex-wrap:wrap;gap:18px;font-size:12.5px;color:var(--text3)}
.aps-meta-item{display:flex;align-items:center;gap:6px;font-weight:500}
.aps-meta-item i{font-size:12px;width:14px;text-align:center}

/* Grid Layout */
.aps-grid{display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start}

/* Cards */
.aps-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:20px;box-shadow:var(--shadow-sm);transition:border-color .2s}
.aps-card:hover{border-color:var(--border2)}
.aps-card-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border);background:var(--card2)}
.aps-card-title{font-size:13.5px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:9px;text-transform:uppercase;letter-spacing:.4px}
.aps-card-title i{color:var(--primary);font-size:14px}
.aps-card-body{padding:20px}

/* Creative Assets Display */
.aps-asset-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px}
.aps-asset-box{position:relative;border-radius:12px;overflow:hidden;background:var(--card2);border:1px solid var(--border);cursor:pointer;aspect-ratio:1/1;transition:all .25s ease}
.aps-asset-box:hover{transform:scale(1.03);border-color:var(--primary);box-shadow:0 6px 18px rgba(79,109,240,.2)}
.aps-asset-box img,.aps-asset-box video{width:100%;height:100%;object-fit:cover;display:block}
.aps-asset-overlay{position:absolute;inset:0;background:rgba(15,23,42,.45);display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;opacity:0;transition:opacity .2s;backdrop-filter:blur(2px)}
.aps-asset-box:hover .aps-asset-overlay{opacity:1}
.aps-asset-badge{position:absolute;bottom:6px;right:6px;background:rgba(0,0,0,.75);color:#fff;font-size:9px;padding:3px 6px;border-radius:5px;font-weight:700}
.aps-asset-empty{padding:40px 20px;text-align:center;color:var(--text3);background:var(--card2);border-radius:12px;border:1px dashed var(--border)}

/* Copy & Hashtags */
.aps-copy-block{background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:14px;position:relative}
.aps-copy-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.aps-copy-label{font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:.5px}
.aps-copy-content{font-size:13.5px;color:var(--text);line-height:1.6;white-space:pre-wrap;word-break:break-word}
.aps-btn-copy{padding:5px 11px;border-radius:7px;background:var(--card);border:1px solid var(--border);color:var(--text2);font-size:11px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all .15s}
.aps-btn-copy:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim)}
.aps-btn-copy.copied{background:#10B981;color:#fff;border-color:#10B981}

/* Platforms list */
.aps-platforms-list{display:flex;flex-direction:column;gap:12px}
.aps-platform-item{display:flex;flex-direction:column;padding:14px 18px;background:var(--card2);border:1px solid var(--border);border-radius:12px;transition:all .2s;gap:10px}
.aps-platform-item:hover{border-color:var(--border2)}
.aps-platform-top{display:flex;align-items:center;justify-content:space-between}
.aps-platform-left{display:flex;align-items:center;gap:12px}
.aps-platform-icon-wrap{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px}
.aps-platform-name-wrap{font-size:13.5px;font-weight:700;color:var(--text)}
.aps-platform-tag{padding:3px 9px;border-radius:6px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.3px}
.aps-platform-link-btn{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:600;color:var(--primary);text-decoration:none;padding:5px 10px;border-radius:6px}
.aps-platform-link-btn:hover{background:var(--primary-dim);text-decoration:underline}

/* Sidebar */
.aps-sidebar{position:sticky;top:20px;display:flex;flex-direction:column;gap:16px}
.aps-sidebar-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;overflow:hidden;box-shadow:var(--shadow-sm)}
.aps-sidebar-title{font-size:12px;font-weight:800;color:var(--text);margin-bottom:14px;display:flex;align-items:center;gap:7px;text-transform:uppercase;letter-spacing:.5px}
.aps-sidebar-title i{color:var(--primary);font-size:13px}

.aps-detail-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid var(--border);font-size:12.5px}
.aps-detail-row:last-child{border-bottom:none}
.aps-detail-key{color:var(--text3);font-weight:500}
.aps-detail-val{font-weight:700;color:var(--text);text-align:right}

.aps-view-task{display:flex;align-items:center;justify-content:center;gap:6px;padding:11px;border-radius:10px;background:var(--card2);color:var(--text2);font-size:12px;font-weight:700;text-decoration:none;transition:all .2s;border:1px solid var(--border)}
.aps-view-task:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim)}

@media(max-width:960px){
    .aps-grid{grid-template-columns:1fr}
    .aps-sidebar{position:static}
    .aps-banner{flex-direction:column;align-items:stretch}
    .aps-banner-actions{justify-content:flex-start}
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

<a href="{{ route('admin.publishing') }}" class="aps-back"><i class="fa-solid fa-arrow-left"></i> Publishing Tracker</a>

{{-- Top Action Banner --}}
@if($isPublished)
    <div class="aps-banner aps-banner-published">
        <div class="aps-banner-left">
            <div class="aps-banner-icon"><i class="fa-solid fa-check-double"></i></div>
            <div>
                <h3>Published Live</h3>
                <p>This content has been marked as published on social media.</p>
            </div>
        </div>
        <div class="aps-banner-actions">
            <form method="POST" action="{{ route('admin.publishing.unpublish', $task) }}" onsubmit="return confirm('Revert this task back to Ready to Publish?')" style="margin:0">
                @csrf
                <button type="submit" class="aps-banner-btn aps-banner-btn-outline"><i class="fa-solid fa-rotate-left"></i> Revert to Pending</button>
            </form>
        </div>
    </div>
@elseif($isOverdue)
    <div class="aps-banner aps-banner-overdue">
        <div class="aps-banner-left">
            <div class="aps-banner-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <h3>Due to Post</h3>
                <p>Scheduled date was {{ $postDateCarbon->format('M d, Y') }} ({{ $postDateCarbon->diffForHumans() }}). Ready to go live.</p>
            </div>
        </div>
        <div class="aps-banner-actions">
            <form method="POST" action="{{ route('admin.publishing.publish', $task) }}" onsubmit="return confirm('Mark this task as Published on social media?')" style="margin:0">
                @csrf
                <button type="submit" class="aps-banner-btn aps-banner-btn-white"><i class="fa-solid fa-rocket"></i> Mark as Published</button>
            </form>
        </div>
    </div>
@else
    <div class="aps-banner aps-banner-pending">
        <div class="aps-banner-left">
            <div class="aps-banner-icon"><i class="fa-solid fa-hourglass-half"></i></div>
            <div>
                <h3>Ready to Publish</h3>
                <p>{{ $postDateCarbon ? 'Scheduled for ' . $postDateCarbon->format('M d, Y') : 'Artwork approved and ready to post on social media.' }}</p>
            </div>
        </div>
        <div class="aps-banner-actions">
            <form method="POST" action="{{ route('admin.publishing.publish', $task) }}" onsubmit="return confirm('Mark this task as Published on social media?')" style="margin:0">
                @csrf
                <button type="submit" class="aps-banner-btn aps-banner-btn-white"><i class="fa-solid fa-rocket"></i> Mark as Published</button>
            </form>
        </div>
    </div>
@endif

{{-- Task Header --}}
<div class="aps-header">
    <div class="aps-title">{{ $task->title ?: 'Untitled Task' }}</div>
    <div class="aps-meta">
        <div class="aps-meta-item"><i class="fa-solid fa-building"></i> Client: <strong>{{ $task->client->name ?? '—' }}</strong></div>
        <div class="aps-meta-item"><i class="fa-solid fa-tag"></i> Type: <strong>{{ ucfirst($task->type) }}</strong></div>
        @if($task->creator)
            <div class="aps-meta-item"><i class="fa-solid fa-user-pen"></i> Strategist: <strong>{{ $task->creator->name }}</strong></div>
        @endif
        @if($task->assignee)
            <div class="aps-meta-item"><i class="fa-solid fa-palette"></i> Designer: <strong>{{ $task->assignee->name }}</strong></div>
        @endif
        @if($task->post_date)
            <div class="aps-meta-item" style="color:{{ $isOverdue ? '#EF4444' : 'inherit' }}">
                <i class="fa-regular fa-calendar-check"></i> Post Date: <strong>{{ $postDateCarbon->format('M d, Y') }}</strong>
            </div>
        @endif
        @if($task->completed_at)
            <div class="aps-meta-item"><i class="fa-solid fa-circle-check" style="color:#10B981"></i> Approved: <strong>{{ $task->completed_at->format('M d, Y') }}</strong></div>
        @endif
    </div>
</div>

<div class="aps-grid">
    {{-- LEFT: Assets, Copy, Platforms --}}
    <div class="aps-main">
        {{-- Creative Assets Card --}}
        <div class="aps-card">
            <div class="aps-card-header">
                <div class="aps-card-title"><i class="fa-solid fa-photo-film"></i> Approved Creatives & Artwork</div>
                @if($task->media && $task->media->count())
                    <span style="font-size:11px;font-weight:700;color:var(--text3)">{{ $task->media->count() }} {{ $task->media->count() > 1 ? 'Files' : 'File' }}</span>
                @endif
            </div>
            <div class="aps-card-body">
                @if($task->media && $task->media->count())
                    <div class="aps-asset-grid">
                        @foreach($task->media as $mediaItem)
                            @php
                                $isVid = (bool) $mediaItem->isVideo();
                                $url = $mediaItem->getUrl();
                            @endphp
                            <div class="aps-asset-box"
                                 onclick="window.openMediaLightbox('{{ addslashes($url) }}', '{{ addslashes($task->title) }}', {{ $isVid ? 'true' : 'false' }}, '{{ route('admin.tasks.show', $task) }}')"
                                 title="Click to view full preview">
                                @if($isVid)
                                    <video src="{{ $url }}" muted></video>
                                    <span class="aps-asset-badge"><i class="fa-solid fa-play"></i> Video</span>
                                @else
                                    <img src="{{ $url }}" alt="{{ $task->title }}" loading="lazy">
                                    <span class="aps-asset-badge"><i class="fa-solid fa-image"></i> Image</span>
                                @endif
                                <div class="aps-asset-overlay">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="aps-asset-empty">
                        <i class="fa-regular fa-image" style="font-size:32px;display:block;margin-bottom:8px;opacity:.5"></i>
                        No creative files uploaded for this task.
                    </div>
                @endif
            </div>
        </div>

        {{-- Copywriting & Hashtags Card --}}
        @if($task->caption || $task->hashtags)
            <div class="aps-card">
                <div class="aps-card-header">
                    <div class="aps-card-title"><i class="fa-solid fa-pen-nib"></i> Marketing Copy & Hashtags</div>
                    <button type="button" class="aps-btn-copy" onclick="copyText('{{ addslashes($task->caption . "\n\n" . $task->hashtags) }}', this)">
                        <i class="fa-regular fa-copy"></i> Copy All
                    </button>
                </div>
                <div class="aps-card-body">
                    @if($task->caption)
                        <div class="aps-copy-block">
                            <div class="aps-copy-header">
                                <span class="aps-copy-label">Caption</span>
                                <button type="button" class="aps-btn-copy" onclick="copyText('{{ addslashes($task->caption) }}', this)">
                                    <i class="fa-regular fa-copy"></i> Copy Caption
                                </button>
                            </div>
                            <div class="aps-copy-content">{{ $task->caption }}</div>
                        </div>
                    @endif

                    @if($task->hashtags)
                        <div class="aps-copy-block" style="margin-bottom:0">
                            <div class="aps-copy-header">
                                <span class="aps-copy-label">Hashtags</span>
                                <button type="button" class="aps-btn-copy" onclick="copyText('{{ addslashes($task->hashtags) }}', this)">
                                    <i class="fa-regular fa-copy"></i> Copy Hashtags
                                </button>
                            </div>
                            <div class="aps-copy-content" style="color:var(--primary);font-weight:600">{{ $task->hashtags }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Target Social Platforms Card --}}
        <div class="aps-card">
            <div class="aps-card-header">
                <div class="aps-card-title"><i class="fa-solid fa-share-nodes"></i> Target Platforms</div>
                <span style="font-size:11px;font-weight:700;color:var(--text3)">{{ count($platforms) }} Platforms</span>
            </div>
            <div class="aps-card-body">
                <div class="aps-platforms-list">
                    @forelse($platforms as $platform)
                        @php
                            $post = $task->socialMediaPosts->firstWhere('platform', $platform);
                            $done = $isPublished || !is_null($post);
                            $pIcon = $platformIcons[$platform] ?? 'fa-solid fa-globe';
                            $pColor = $platformColors[$platform] ?? '#6B7280';
                        @endphp
                        <div class="aps-platform-item">
                            <div class="aps-platform-top">
                                <div class="aps-platform-left">
                                    <div class="aps-platform-icon-wrap" style="background:{{ $pColor }}15;color:{{ $pColor }}">
                                        <i class="{{ $pIcon }}"></i>
                                    </div>
                                    <div>
                                        <div class="aps-platform-name-wrap">{{ ucfirst($platform) }}</div>
                                        <span class="aps-platform-tag" style="background:{{ $done ? 'rgba(16,185,129,.1)' : 'rgba(245,158,11,.1)' }};color:{{ $done ? '#059669' : '#D97706' }}">
                                            {{ $done ? 'Published Live' : 'Ready to Publish' }}
                                        </span>
                                    </div>
                                </div>
                                @if($post && $post->post_url)
                                    <a href="{{ $post->post_url }}" target="_blank" rel="noopener noreferrer" class="aps-platform-link-btn">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Live Link
                                    </a>
                                @endif
                            </div>

                            @if($post && $post->metrics && $post->metrics->count())
                                @php $latestMetric = $post->metrics->first(); @endphp
                                <div style="margin-top:6px;padding-top:8px;border-top:1px dashed var(--border);display:grid;grid-template-columns:repeat(4,1fr);gap:6px;font-size:11px;color:var(--text2)">
                                    <div><strong>Views:</strong> {{ $latestMetric->views !== null ? number_format($latestMetric->views) : '—' }}</div>
                                    <div><strong>Likes:</strong> {{ $latestMetric->likes !== null ? number_format($latestMetric->likes) : '—' }}</div>
                                    <div><strong>Comments:</strong> {{ $latestMetric->comments !== null ? number_format($latestMetric->comments) : '—' }}</div>
                                    <div><strong>Shares:</strong> {{ $latestMetric->shares !== null ? number_format($latestMetric->shares) : '—' }}</div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div style="font-size:12.5px;color:var(--text3);text-align:center;padding:12px">No specific platforms selected.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Sidebar Details --}}
    <div class="aps-sidebar">
        <div class="aps-sidebar-card">
            <div class="aps-sidebar-title"><i class="fa-solid fa-circle-info"></i> Publishing Status</div>
            
            <div class="aps-detail-row">
                <span class="aps-detail-key">Current Status</span>
                <span class="aps-detail-val">
                    @if($isPublished)
                        <span style="color:#059669"><i class="fa-solid fa-check-double"></i> Published</span>
                    @elseif($isOverdue)
                        <span style="color:#EF4444"><i class="fa-solid fa-triangle-exclamation"></i> Due to Post</span>
                    @else
                        <span style="color:#D97706"><i class="fa-solid fa-hourglass-half"></i> Ready to Publish</span>
                    @endif
                </span>
            </div>

            @if($task->post_date)
                <div class="aps-detail-row">
                    <span class="aps-detail-key">Scheduled Post Date</span>
                    <span class="aps-detail-val" style="color:{{ $isOverdue ? '#EF4444' : 'inherit' }}">
                        {{ $postDateCarbon->format('M d, Y') }}
                    </span>
                </div>
            @endif

            <div class="aps-detail-row">
                <span class="aps-detail-key">Client</span>
                <span class="aps-detail-val">{{ $task->client->name ?? '—' }}</span>
            </div>

            @if($task->creator)
                <div class="aps-detail-row">
                    <span class="aps-detail-key">Strategist</span>
                    <span class="aps-detail-val">{{ $task->creator->name }}</span>
                </div>
            @endif

            @if($task->assignee)
                <div class="aps-detail-row">
                    <span class="aps-detail-key">Designer</span>
                    <span class="aps-detail-val">{{ $task->assignee->name }}</span>
                </div>
            @endif

            @if($task->completed_at)
                <div class="aps-detail-row">
                    <span class="aps-detail-key">Approved On</span>
                    <span class="aps-detail-val">{{ $task->completed_at->format('M d, Y') }}</span>
                </div>
            @endif
        </div>

        {{-- 1-Click Action in Sidebar --}}
        <div class="aps-sidebar-card" style="text-align:center">
            @if($isPublished)
                <form method="POST" action="{{ route('admin.publishing.unpublish', $task) }}" onsubmit="return confirm('Revert this task back to Ready to Publish?')" style="margin:0">
                    @csrf
                    <button type="submit" class="aps-banner-btn aps-banner-btn-outline" style="width:100%;justify-content:center;background:var(--card2);color:var(--text2);border:1px solid var(--border)">
                        <i class="fa-solid fa-rotate-left"></i> Revert to Pending
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.publishing.publish', $task) }}" onsubmit="return confirm('Mark this task as Published on social media?')" style="margin:0">
                    @csrf
                    <button type="submit" class="aps-banner-btn" style="width:100%;justify-content:center;background:linear-gradient(135deg,#10B981,#059669);color:#fff">
                        <i class="fa-solid fa-rocket"></i> Mark as Published
                    </button>
                </form>
            @endif
        </div>

        {{-- View Full Task --}}
        <a href="{{ route('admin.tasks.show', $task) }}" class="aps-view-task">
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
</script>
@endpush

@endsection
