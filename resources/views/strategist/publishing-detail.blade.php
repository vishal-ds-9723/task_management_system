@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
/* ── Publishing Detail ─────────────────────────── */
.pd-back{display:inline-flex;align-items:center;gap:6px;color:var(--text3);font-size:12.5px;text-decoration:none;margin-bottom:20px;font-weight:600;transition:all .2s;padding:6px 12px;border-radius:8px}
.pd-back:hover{color:var(--primary);background:var(--primary-dim)}

/* Published banner */
.pd-banner{background:linear-gradient(135deg,#10B981 0%,#059669 100%);color:#fff;border-radius:var(--radius);padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;gap:16px;box-shadow:0 4px 16px rgba(16,185,129,.25)}
.pd-banner-icon{width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.pd-banner h3{font-size:16px;font-weight:700;margin:0 0 2px}
.pd-banner p{font-size:12.5px;opacity:.85;margin:0}

/* Header card */
.pd-header{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;margin-bottom:20px;position:relative;overflow:hidden}
.pd-header::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),#6882F5)}
.pd-title{font-size:18px;font-weight:800;color:var(--text);margin-bottom:10px;font-family:'Plus Jakarta Sans',sans-serif}
.pd-meta{display:flex;flex-wrap:wrap;gap:18px;font-size:12.5px;color:var(--text3)}
.pd-meta-item{display:flex;align-items:center;gap:5px;font-weight:500}
.pd-meta-item i{font-size:12px;width:14px;text-align:center}

/* Grid layout */
.pd-grid{display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start}

/* Platform cards */
.pd-platforms{display:flex;flex-direction:column;gap:14px}
.pd-platform-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:all .25s ease}
.pd-platform-card:hover{border-color:var(--primary);box-shadow:var(--shadow)}
.pd-platform-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.pd-platform-name{display:flex;align-items:center;gap:10px;font-size:14.5px;font-weight:700;color:var(--text)}
.pd-platform-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
.pd-status-badge{padding:4px 12px;border-radius:20px;font-size:10.5px;font-weight:700;letter-spacing:.3px;text-transform:uppercase}
.pd-status-posted{background:rgba(16,185,129,.1);color:#059669}
.pd-status-pending{background:rgba(245,158,11,.08);color:#D97706}
.pd-platform-body{padding:16px 20px}

/* Proof display */
.pd-proof-info{background:rgba(16,185,129,.04);border:1px solid rgba(16,185,129,.12);border-radius:10px;padding:14px 16px}
.pd-proof-link{display:flex;align-items:center;gap:6px;font-size:13px;color:var(--primary);word-break:break-all;text-decoration:none;font-weight:600}
.pd-proof-link:hover{text-decoration:underline}
.pd-proof-meta{display:flex;gap:14px;font-size:11.5px;color:var(--text3);margin-top:8px;font-weight:500}
.pd-proof-meta span{display:flex;align-items:center;gap:4px}

/* Form */
.pd-form{margin:0}
.pd-form-row{display:flex;gap:10px;margin-bottom:10px;flex-wrap:wrap}
.pd-form-group{flex:1;min-width:140px}
.pd-form-group label{font-size:11px;font-weight:700;color:var(--text2);display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.3px}
.pd-form-group input,.pd-form-group select{height:38px;border:1px solid var(--border);border-radius:8px;padding:0 12px;font-size:12.5px;background:var(--bg);color:var(--text);width:100%;transition:border-color .2s;outline:none;font-weight:500}
.pd-form-group input:focus,.pd-form-group select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,109,240,.08)}

/* Buttons */
.pd-btn{padding:9px 18px;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:5px;transition:all .2s}
.pd-btn-save{background:var(--primary);color:#fff}
.pd-btn-save:hover{background:var(--primary-hover);box-shadow:0 4px 12px rgba(79,109,240,.2);transform:translateY(-1px)}
.pd-btn-remove{background:rgba(239,68,68,.06);color:#dc2626;font-size:11px;padding:5px 10px;border-radius:6px;margin-top:10px}
.pd-btn-remove:hover{background:rgba(239,68,68,.12)}

/* Sidebar */
.pd-sidebar{position:sticky;top:20px;display:flex;flex-direction:column;gap:14px}
.pd-sidebar-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;overflow:hidden}
.pd-sidebar-title{font-size:12px;font-weight:700;color:var(--text);margin-bottom:14px;display:flex;align-items:center;gap:7px;text-transform:uppercase;letter-spacing:.5px}
.pd-sidebar-title i{color:var(--primary);font-size:13px}

/* Progress ring */
.pd-progress-wrap{text-align:center;padding:10px 0}
.pd-progress-ring{position:relative;width:110px;height:110px;margin:0 auto 14px}
.pd-progress-ring svg{transform:rotate(-90deg);width:110px;height:110px}
.pd-progress-ring circle{fill:none;stroke-width:8;stroke-linecap:round}
.pd-progress-ring .ring-bg{stroke:var(--border)}
.pd-progress-ring .ring-fill{transition:stroke-dashoffset .6s ease}
.pd-progress-val{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.pd-progress-big{font-size:28px;font-weight:800;color:var(--text);line-height:1}
.pd-progress-sub{font-size:11px;color:var(--text3);font-weight:600;margin-top:2px}
.pd-progress-label{font-size:13px;font-weight:700;margin-top:4px}

/* Task details list */
.pd-detail-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);font-size:12.5px}
.pd-detail-row:last-child{border-bottom:none}
.pd-detail-key{color:var(--text3);font-weight:500}
.pd-detail-val{font-weight:600;color:var(--text);text-align:right}

/* Timeline */
.pd-timeline-item{display:flex;gap:12px;padding:10px 0;position:relative}
.pd-timeline-item:not(:last-child)::after{content:'';position:absolute;left:5px;top:24px;bottom:0;width:2px;background:var(--border)}
.pd-timeline-dot{width:12px;height:12px;border-radius:50%;flex-shrink:0;margin-top:2px;border:2px solid var(--card);box-shadow:0 0 0 2px currentColor;z-index:1}
.pd-timeline-content{flex:1;min-width:0}
.pd-timeline-label{font-size:12px;font-weight:600;color:var(--text)}
.pd-timeline-time{font-size:10.5px;color:var(--text3);margin-top:2px;font-weight:500}

/* View task link */
.pd-view-task{display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:8px;background:var(--card2);color:var(--text2);font-size:12px;font-weight:600;text-decoration:none;transition:all .2s;border:1px solid var(--border)}
.pd-view-task:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim)}

@media(max-width:900px){
    .pd-grid{grid-template-columns:1fr}
    .pd-sidebar{position:static}
}

/* Image upload */
.pd-image-upload:hover{border-color:var(--primary) !important;background:var(--primary-dim) !important}
.pd-image-upload.dragover{border-color:var(--primary) !important;background:rgba(var(--primary-rgb),.08) !important}
.pd-image-upload img{max-width:100%;max-height:200px;border-radius:8px;display:block;margin:0 auto}
</style>
@endpush

@section('content')

<a href="{{ route('strategist.publishing') }}" class="pd-back"><i class="fa-solid fa-arrow-left"></i> Publishing Queue</a>

{{-- Published banner --}}
@if($task->status === 'published')
    <div class="pd-banner">
        <div class="pd-banner-icon"><i class="fa-solid fa-rocket"></i></div>
        <div>
            <h3>Fully Published</h3>
            <p>All social media proofs uploaded — this task is complete!</p>
        </div>
    </div>
@endif

{{-- Task Header --}}
<div class="pd-header">
    <div class="pd-title">{{ $task->title ?: 'Untitled Task' }}</div>
    <div class="pd-meta">
        <div class="pd-meta-item"><i class="fa-solid fa-building"></i> {{ $task->client->name ?? '—' }}</div>
        <div class="pd-meta-item"><i class="fa-solid fa-tag"></i> {{ ucfirst($task->type) }}</div>
        @if($task->creator)
            <div class="pd-meta-item"><i class="fa-solid fa-user"></i> {{ $task->creator->name }}</div>
        @endif
        @if($task->post_date)
            <div class="pd-meta-item"><i class="fa-regular fa-calendar"></i> Post Date: {{ $task->post_date ? \Illuminate\Support\Carbon::parse($task->post_date)->format('M d, Y') : '—' }}</div>
        @endif
        @if($task->completed_at)
            <div class="pd-meta-item"><i class="fa-solid fa-check-circle" style="color:#10B981"></i> Approved: {{ $task->completed_at->format('M d, Y') }}</div>
        @endif
    </div>
</div>

@php
    $platforms = is_array($task->platform) ? $task->platform : (is_string($task->platform) ? [$task->platform] : []);
    $progress = $task->getPublishingProgress();
    $pct = $progress['total'] > 0 ? round(($progress['posted'] / $progress['total']) * 100) : 0;
    $circumference = 2 * 3.14159 * 45;
    $dashoffset = $circumference - ($pct / 100) * $circumference;
    $ringColor = $pct >= 100 ? '#10B981' : ($pct > 0 ? '#3B82F6' : '#94A3B8');
    $imageProofPlatforms = \App\Models\SocialMediaPost::IMAGE_PROOF_PLATFORMS;
    $platformIcons = [
        'instagram'  => 'fa-brands fa-instagram',
        'facebook'   => 'fa-brands fa-facebook-f',
        'linkedin'   => 'fa-brands fa-linkedin-in',
        'twitter'    => 'fa-brands fa-x-twitter',
        'tiktok'     => 'fa-brands fa-tiktok',
        'youtube'    => 'fa-brands fa-youtube',
        'pinterest'  => 'fa-brands fa-pinterest-p',
        'snapchat'   => 'fa-brands fa-snapchat',
        'whatsapp'   => 'fa-brands fa-whatsapp',
        'telegram'   => 'fa-brands fa-telegram',
        'reddit'     => 'fa-brands fa-reddit-alien',
        'discord'    => 'fa-brands fa-discord',
        'tumblr'     => 'fa-brands fa-tumblr',
        'spotify'    => 'fa-brands fa-spotify',
        'twitch'     => 'fa-brands fa-twitch',
        'threads'    => 'fa-brands fa-threads',
        'behance'    => 'fa-brands fa-behance',
        'dribbble'   => 'fa-brands fa-dribbble',
        'medium'     => 'fa-brands fa-medium',
        'vimeo'      => 'fa-brands fa-vimeo-v',
        'sharechat'  => 'fa-solid fa-share-nodes',
        'moj'        => 'fa-solid fa-clapperboard',
        'koo'        => 'fa-solid fa-feather',
        'quora'      => 'fa-brands fa-quora',
        'github'     => 'fa-brands fa-github',
        'weibo'      => 'fa-brands fa-weibo',
        'signal'     => 'fa-solid fa-signal',
        'clubhouse'  => 'fa-solid fa-hand',
    ];
    $platformColors = [
        'instagram' => '#E1306C', 'facebook'  => '#1877F2', 'linkedin'  => '#0077B5',
        'twitter'   => '#000000', 'tiktok'    => '#010101', 'youtube'   => '#FF0000',
        'pinterest' => '#E60023', 'snapchat'  => '#FFFC00', 'whatsapp'  => '#25D366',
        'telegram'  => '#26A5E4', 'reddit'    => '#FF4500', 'discord'   => '#5865F2',
        'tumblr'    => '#36465D', 'spotify'   => '#1DB954', 'twitch'    => '#9146FF',
        'threads'   => '#000000', 'behance'   => '#1769FF', 'dribbble'  => '#EA4C89',
        'medium'    => '#000000', 'vimeo'     => '#1AB7EA', 'sharechat' => '#F52D56',
        'moj'       => '#EE1233', 'koo'       => '#FACD00', 'quora'     => '#B92B27',
        'github'    => '#181717', 'weibo'     => '#E6162D', 'signal'    => '#3A76F0',
        'clubhouse' => '#F2E351',
    ];
@endphp

<div class="pd-grid">
    {{-- LEFT: Platform-wise proof forms --}}
    <div class="pd-platforms">
        @foreach($platforms as $platform)
            @php
                $status = $platformStatus[$platform] ?? ['posted' => false, 'post' => null];
                $post = $status['post'];
                $pIcon = $platformIcons[$platform] ?? 'fa-solid fa-globe';
                $color = $platformColors[$platform] ?? '#6B7280';
            @endphp
            <div class="pd-platform-card" id="platform-{{ $platform }}">
                <div class="pd-platform-header">
                    <div class="pd-platform-name">
                        <div class="pd-platform-icon" style="background:{{ $color }}12;color:{{ $color }}"><i class="{{ $pIcon }}"></i></div>
                        {{ ucfirst($platform) }}
                    </div>
                    <span class="pd-status-badge {{ $status['posted'] ? 'pd-status-posted' : 'pd-status-pending' }}">
                        <i class="fa-solid fa-{{ $status['posted'] ? 'check-circle' : 'clock' }}" style="margin-right:3px"></i>
                        {{ $status['posted'] ? 'Posted' : 'Pending' }}
                    </span>
                </div>

                <div class="pd-platform-body">
                    @php
                        $requiresImageProof = in_array($platform, $imageProofPlatforms, true);
                    @endphp

                    @if($status['posted'] && $post)
                        <div class="pd-proof-info">
                            @if($post->proof_image)
                                <div class="pd-proof-image-wrap" style="margin-bottom:10px">
                                    <a href="{{ asset('storage/' . $post->proof_image) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $post->proof_image) }}" alt="Proof screenshot"
                                             style="max-width:100%;max-height:240px;border-radius:8px;border:1px solid var(--border);display:block;cursor:zoom-in">
                                    </a>
                                </div>
                            @endif

                            @if($post->post_url)
                                <a href="{{ $post->post_url }}" target="_blank" rel="noopener noreferrer" class="pd-proof-link">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    {{ Str::limit($post->post_url, 55) }}
                                </a>
                            @elseif(!$post->proof_image)
                                <span style="font-size:12px;color:var(--text3)">No link provided</span>
                            @endif

                            <div class="pd-proof-meta">
                                <span><i class="fa-solid fa-tag"></i> {{ ucfirst($post->post_type) }}</span>
                                <span><i class="fa-regular fa-calendar"></i> {{ $post->posted_at->format('M d, Y') }}</span>
                                <span><i class="fa-solid fa-user"></i> {{ $post->poster->name ?? '—' }}</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('strategist.publishing.destroy-proof', [$task, $post]) }}" onsubmit="return confirm('Remove proof for {{ ucfirst($platform) }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="pd-btn pd-btn-remove"><i class="fa-solid fa-trash"></i> Remove Proof</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('strategist.publishing.store-proof', $task) }}" class="pd-form" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="platform" value="{{ $platform }}">

                            <div class="pd-form-row">
                                <div class="pd-form-group">
                                    <label>Content Type</label>
                                    <select name="post_type" required>
                                        <option value="">Select type</option>
                                        <option value="post" {{ old('platform') === $platform && old('post_type') === 'post' ? 'selected' : '' }}>Post</option>
                                        <option value="reel" {{ old('platform') === $platform && old('post_type') === 'reel' ? 'selected' : '' }}>Reel</option>
                                        <option value="story" {{ old('platform') === $platform && old('post_type') === 'story' ? 'selected' : '' }}>Story</option>
                                        <option value="carousel" {{ old('platform') === $platform && old('post_type') === 'carousel' ? 'selected' : '' }}>Carousel</option>
                                        <option value="video" {{ old('platform') === $platform && old('post_type') === 'video' ? 'selected' : '' }}>Video</option>
                                    </select>
                                </div>

                                <div class="pd-form-group">
                                    <label>Date Posted</label>
                                    <input type="date"
                                           name="posted_at"
                                           required
                                           value="{{ old('platform') === $platform ? old('posted_at', now()->format('Y-m-d')) : now()->format('Y-m-d') }}"
                                           max="{{ now()->format('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="pd-form-row">
                                <div class="pd-form-group">
                                    <label>{{ $requiresImageProof ? 'Post URL (Optional)' : 'Post URL' }}</label>
                                    <input type="url"
                                           name="post_url"
                                           value="{{ old('platform') === $platform ? old('post_url') : '' }}"
                                           placeholder="{{ $requiresImageProof ? 'Optional post link' : 'https://example.com/post' }}"
                                           @unless($requiresImageProof) required @endunless>
                                </div>

                                <div class="pd-form-group">
                                    <label>{{ $requiresImageProof ? 'Proof Screenshot' : 'Proof Screenshot (Optional)' }}</label>
                                    <input type="file"
                                           name="proof_image"
                                           accept=".jpg,.jpeg,.png,.webp,image/*"
                                           style="padding:8px;height:auto"
                                           @if($requiresImageProof) required @endif>
                                </div>
                            </div>

                            <button type="submit" class="pd-btn pd-btn-save">
                                <i class="fa-solid fa-check"></i> Save Proof
                            </button>
                        </form>
                    @endif
                </div>



            </div>
        @endforeach
    </div>

    {{-- RIGHT: Sidebar --}}
    <div class="pd-sidebar">
        {{-- Progress --}}
        <div class="pd-sidebar-card">
            <div class="pd-sidebar-title"><i class="fa-solid fa-chart-pie"></i> Progress</div>
            <div class="pd-progress-wrap">
                <div class="pd-progress-ring">
                    <svg viewBox="0 0 110 110">
                        <circle class="ring-bg" cx="55" cy="55" r="45" />
                        <circle class="ring-fill" cx="55" cy="55" r="45"
                            stroke="{{ $ringColor }}"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $dashoffset }}" />
                    </svg>
                    <div class="pd-progress-val">
                        <div class="pd-progress-big">{{ $pct }}%</div>
                        <div class="pd-progress-sub">{{ $progress['posted'] }}/{{ $progress['total'] }}</div>
                    </div>
                </div>
                <div class="pd-progress-label" style="color:{{ $ringColor }}">
                    {{ $pct >= 100 ? 'All Platforms Done' : ($pct > 0 ? 'In Progress' : 'Not Started') }}
                </div>
            </div>
        </div>

        {{-- Task Details --}}
        <div class="pd-sidebar-card">
            <div class="pd-sidebar-title"><i class="fa-solid fa-info-circle"></i> Task Details</div>
            @if($task->type)
                <div class="pd-detail-row">
                    <span class="pd-detail-key">Type</span>
                    <span class="pd-detail-val" style="background:var(--primary-dim);color:var(--primary);padding:2px 10px;border-radius:6px;font-size:10.5px;text-transform:uppercase">{{ $task->type }}</span>
                </div>
            @endif
            <div class="pd-detail-row">
                <span class="pd-detail-key">Status</span>
                <span class="pd-detail-val" style="background:{{ $task->status === 'published' ? 'rgba(16,185,129,.1)' : 'rgba(245,158,11,.08)' }};color:{{ $task->status === 'published' ? '#059669' : '#D97706' }};padding:2px 10px;border-radius:6px;font-size:10.5px;text-transform:uppercase">{{ $task->status_label }}</span>
            </div>
            @if($task->assignee)
                <div class="pd-detail-row">
                    <span class="pd-detail-key">Designer</span>
                    <span class="pd-detail-val">{{ $task->assignee->name }}</span>
                </div>
            @endif
            @if($task->post_date)
                <div class="pd-detail-row">
                    <span class="pd-detail-key">Post Date</span>
                    <span class="pd-detail-val">{{ \Illuminate\Support\Carbon::parse($task->post_date)->format('M d, Y') }}</span>
                </div>
            @endif
            @if($task->deadline)
                <div class="pd-detail-row">
                    <span class="pd-detail-key">Deadline</span>
                    <span class="pd-detail-val">{{ \Illuminate\Support\Carbon::parse($task->deadline)->format('M d, Y') }}</span>
                </div>
            @endif
        </div>

        {{-- Timeline --}}
        <div class="pd-sidebar-card">
            <div class="pd-sidebar-title"><i class="fa-solid fa-timeline"></i> Timeline</div>
            @if($task->created_at)
                <div class="pd-timeline-item">
                    <div class="pd-timeline-dot" style="color:var(--text3)"></div>
                    <div class="pd-timeline-content">
                        <div class="pd-timeline-label">Task Created</div>
                        <div class="pd-timeline-time">{{ $task->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            @endif
            @if($task->submitted_at)
                <div class="pd-timeline-item">
                    <div class="pd-timeline-dot" style="color:#3B82F6"></div>
                    <div class="pd-timeline-content">
                        <div class="pd-timeline-label">Submitted for Review</div>
                        <div class="pd-timeline-time">{{ $task->submitted_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            @endif
            @if($task->completed_at)
                <div class="pd-timeline-item">
                    <div class="pd-timeline-dot" style="color:#10B981"></div>
                    <div class="pd-timeline-content">
                        <div class="pd-timeline-label">Admin Approved</div>
                        <div class="pd-timeline-time">{{ $task->completed_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            @endif
            @foreach($task->socialMediaPosts->sortBy('created_at') as $post)
                @php $ptColor = $platformColors[$post->platform] ?? '#6B7280'; @endphp
                <div class="pd-timeline-item">
                    <div class="pd-timeline-dot" style="color:{{ $ptColor }}"></div>
                    <div class="pd-timeline-content">
                        <div class="pd-timeline-label"><i class="{{ $platformIcons[$post->platform] ?? 'fa-solid fa-globe' }}" style="margin-right:3px;color:{{ $ptColor }}"></i> {{ ucfirst($post->platform) }} proof uploaded</div>
                        <div class="pd-timeline-time">{{ $post->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- View Task --}}
        <a href="{{ route('strategist.tasks.show', $task) }}" class="pd-view-task">
            <i class="fa-solid fa-eye"></i> View Full Task
        </a>
    </div>
</div>

@push('scripts')
<script>
function previewImage(input, platform) {
    const preview = document.getElementById('preview-' + platform);
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 500 * 1024 * 1024) {
            alert('File is too large. Maximum size is 500MB.');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <div style="font-size:11px;color:var(--text3);margin-top:8px;font-weight:600">
                    <i class="fa-solid fa-check-circle" style="color:#10B981;margin-right:3px"></i> ${file.name}
                    <span style="opacity:.6">(${(file.size/1024).toFixed(0)}KB)</span>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
}

// Drag & drop support
document.querySelectorAll('.pd-image-upload').forEach(zone => {
    ['dragenter','dragover'].forEach(evt => {
        zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.add('dragover'); });
    });
    ['dragleave','drop'].forEach(evt => {
        zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.remove('dragover'); });
    });
    zone.addEventListener('drop', e => {
        const input = zone.querySelector('input[type="file"]');
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
});

function syncPaidPromotionFields(checkbox) {
    const form = checkbox.closest('form');
    if (!form) {
        return;
    }

    const paidFields = form.querySelector('[data-paid-fields]');
    if (!paidFields) {
        return;
    }

    paidFields.style.display = checkbox.checked ? 'grid' : 'none';
}

document.querySelectorAll('input[data-paid-toggle]').forEach((checkbox) => {
    syncPaidPromotionFields(checkbox);
    checkbox.addEventListener('change', () => syncPaidPromotionFields(checkbox));
});
</script>
@endpush

@endsection
