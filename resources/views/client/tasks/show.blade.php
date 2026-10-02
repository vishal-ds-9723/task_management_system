@extends('layouts.app')

@push('styles')
<style>
.ts-wrapper { animation: fadeIn 0.6s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.ts-header {
    background: var(--card);
    padding: 32px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    position: relative;
    overflow: hidden;
}

.ts-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 4px; height: 100%;
    background: var(--primary-gradient);
}

.ts-title-box h1 {
    font-size: 26px;
    font-weight: 900;
    color: var(--text);
    margin: 0 0 8px;
    letter-spacing: -0.5px;
}

.ts-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
}

.ts-grid {
    display: grid;
    grid-template-columns: 1.8fr 1fr;
    gap: 24px;
}

.ts-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 24px;
}

.ts-sec-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.ts-media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}

.ts-media-item {
    border-radius: 16px;
    overflow: hidden;
    aspect-ratio: 1;
    background: var(--card2);
    border: 1px solid var(--border);
    position: relative;
}

.ts-media-item img, .ts-media-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ts-info-row {
    display: flex;
    justify-content: space-between;
    padding: 14px 0;
    border-bottom: 1px solid var(--border2);
}

.ts-info-row:last-child { border-bottom: none; }

.ts-info-lbl { font-size: 13px; font-weight: 700; color: var(--text3); }
.ts-info-val { font-size: 14px; font-weight: 700; color: var(--text); }

.ts-caption {
    background: var(--card2);
    padding: 8px 12px 8px 6px;
    border-radius: 12px;
    font-size: 14.5px;
    line-height: 1.6;
    color: var(--text);
    white-space: pre-wrap;
    border: 1px solid var(--border2);
    position: relative;
    min-height: auto;
}

.ts-sub-lbl {
    font-size: 12px;
    font-weight: 800;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.ts-copy-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    padding: 8px 12px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    color: var(--text3);
    cursor: pointer;
    transition: all 0.2s;
}

.ts-copy-btn:hover {
    color: var(--primary);
    border-color: var(--primary);
}

.ts-hashtag-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 20px;
}

.ts-hashtag-chip {
    padding: 4px 12px;
    background: var(--primary-dim);
    color: var(--primary);
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
}

.ts-client-logo {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: var(--card2);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border);
    overflow: hidden;
}

.ts-client-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.ts-meta-box {
    display: flex;
    align-items: center;
    gap: 6px;
}

.ts-visit-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
    font-size: 11.5px;
    font-weight: 800;
    color: var(--primary);
    text-decoration: none;
    transition: all 0.2s;
}

.ts-visit-link:hover {
    color: var(--primary-hover);
    transform: translateX(4px);
}

/* Lightbox Premium */
.ts-lightbox {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(10px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 40px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.ts-lightbox.active {
    display: flex;
    opacity: 1;
}

.ts-lightbox-close {
    position: absolute;
    top: 30px;
    right: 30px;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.ts-lightbox-close:hover {
    background: var(--primary);
    transform: rotate(90deg);
}

.ts-lightbox-content {
    max-width: 100%;
    max-height: 100%;
    border-radius: 12px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    transform: scale(0.9);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.ts-lightbox.active .ts-lightbox-content {
    transform: scale(1);
}

.ts-media-item { cursor: pointer; }
.ts-media-item:hover { opacity: 0.8; }
</style>
@endpush

@section('content')
<div class="ts-wrapper">
    <div style="margin-bottom: 16px;">
        <a href="{{ route('client.dashboard') }}" style="text-decoration:none; color:var(--text3); font-weight:700; font-size:13px; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="ts-header">
        <div style="display:flex; gap:20px; align-items:center">
            <div class="ts-client-logo">
                @if($client->logo)
                    <img src="{{ Storage::url($client->logo) }}" alt="Logo">
                @else
                    <i class="fa-solid fa-briefcase" style="font-size:24px; color:var(--primary)"></i>
                @endif
            </div>
            <div class="ts-title-box">
                <h1>{{ $task->title ?: 'Untitled Content' }}</h1>
                <div style="display:flex; gap:10px;">
                    <span class="ts-badge" style="background:var(--primary-dim); color:var(--primary)">
                        <i class="fa-solid fa-{{ $task->type === 'reel' ? 'film' : 'image' }}"></i>
                        {{ ucfirst($task->type) }}
                    </span>
                    @php
                        $statusColors = [
                            'published' => ['#059669', '#ecfdf5'],
                            'completed' => ['#d97706', '#fffbeb'],
                            'default' => ['#2563eb', '#eff6ff']
                        ];
                        $sColor = $statusColors[$task->status] ?? $statusColors['default'];
                    @endphp
                    <span class="ts-badge" style="background:{{ $sColor[1] }}; color:{{ $sColor[0] }}">
                        <i class="fa-solid fa-circle-check"></i>
                        {{ $task->status === 'published' ? 'Live' : ($task->status === 'completed' ? 'Awaiting Review' : 'In Progress') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="ts-actions">
            @if($task->status === 'published')
                <div style="text-align:right; color:var(--text3); font-size:12px; font-weight:700;">
                    Published on {{ $task->completed_at?->format('M d, Y') }}
                </div>
            @endif
        </div>
    </div>

    <div class="ts-grid">
        <div class="ts-main">
            {{-- Media Section --}}
            <div class="ts-card">
                <div class="ts-sec-title">
                    <i class="fa-solid fa-photo-film"></i>
                    Content Media
                </div>
                @if($task->media->count())
                    <div class="ts-media-grid">
                        @foreach($task->media as $media)
                            <div class="ts-media-item" onclick="openLightbox('{{ Storage::url($media->path) }}', '{{ str_contains($media->mime_type, 'video') ? 'video' : 'image' }}')">
                                @if(str_contains($media->mime_type, 'video'))
                                    <video src="{{ Storage::url($media->path) }}"></video>
                                    <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:24px; background:rgba(0,0,0,0.2)">
                                        <i class="fa-solid fa-play"></i>
                                    </div>
                                @else
                                    <img src="{{ Storage::url($media->path) }}" alt="Content">
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="padding:40px; text-align:center; background:var(--card2); border-radius:16px; color:var(--text3)">
                        <i class="fa-solid fa-cloud-upload" style="font-size:32px; margin-bottom:12px; opacity:0.3"></i>
                        <p style="margin:0; font-weight:700;">No media files uploaded yet</p>
                    </div>
                @endif
            </div>

            {{-- Caption & Hashtags Section --}}
            <div class="ts-card" style="
    background:#ffffff;
    border:1px solid #e8ecf4;
    border-radius:18px;
    padding:26px;
    box-shadow:0 10px 30px rgba(18,38,63,0.06);
    margin-bottom:24px;
">

    <!-- Title -->
    <div class="ts-sec-title" style="
        display:flex;
        align-items:center;
        gap:10px;
        font-size:20px;
        font-weight:700;
        color:#1e293b;
        margin-bottom:24px;
        padding-bottom:14px;
        border-bottom:1px solid #eef2f7;
    ">
        <i class="fa-solid fa-message-lines" style="color:#6366f1;"></i>
        Post Content
    </div>

    <!-- Caption Label -->
    <!-- Caption Label -->
<div class="ts-sub-lbl" style="
    font-size:13px;
    font-weight:700;
    color:#64748b;
    text-transform:uppercase;
    letter-spacing:.7px;
    margin-bottom:10px;
    display:flex;
    align-items:center;
    gap:8px;
">
    <i class="fa-solid fa-quote-left" style="font-size:11px;color:#6366f1;"></i>
    Caption
</div>

<!-- Properly Arranged Caption Box -->
<div class="ts-caption" id="captionText" style="
    background:#f8fafc;
    border:1px solid #dbe2ea;
    border-radius:18px;
    padding:1px 22px;
    font-size:16px;
    color:#334155;
    line-height:1.7;
    word-break:break-word;
    white-space:pre-line;
    min-height:64px;
    height:auto;
    display:flex;
    align-items:center;
    box-sizing:border-box;
    transition:0.3s ease;
">
    {{ $task->caption ?: 'No caption provided.' }}
</div>

    @if($task->hashtags)
        @php
            $tags = preg_split('/\s+/', trim($task->hashtags));
        @endphp

        <!-- Hashtag Section -->
        <div style="margin-top:28px;">

            <div class="ts-sub-lbl" style="
                font-size:13px;
                font-weight:700;
                color:#64748b;
                text-transform:uppercase;
                letter-spacing:.6px;
                margin-bottom:14px;
                display:flex;
                align-items:center;
                gap:8px;
            ">
                <i class="fa-solid fa-hashtag" style="font-size:10px;color:#6366f1;"></i>
                Hashtags
            </div>

            <div class="ts-hashtag-grid" style="
                display:flex;
                flex-wrap:wrap;
                gap:10px;
            ">
                @foreach($tags as $tag)
                    <span class="ts-hashtag-chip" style="
                        background:#eef2ff;
                        color:#4338ca;
                        padding:9px 14px;
                        border-radius:999px;
                        font-size:13px;
                        font-weight:600;
                        border:1px solid #c7d2fe;
                    ">
                        {{ $tag }}
                    </span>
                @endforeach
            </div>

        </div>
    @endif

</div>
        </div>

        <div class="ts-sidebar">
            <div class="ts-card">
                <div class="ts-sec-title"><i class="fa-solid fa-circle-info"></i> Information</div>
                <div class="ts-info-row">
                    <span class="ts-info-lbl"><i class="fa-solid fa-calendar-day" style="width:20px"></i> Deadline</span>
                    <span class="ts-info-val">{{ $task->deadline ? $task->deadline->format('M d, Y') : 'No deadline' }}</span>
                </div>
                <div class="ts-info-row">
                    <span class="ts-info-lbl"><i class="fa-solid fa-layer-group" style="width:20px"></i> Platform</span>
                    <span class="ts-info-val">
                        @foreach(is_array($task->platform) ? $task->platform : [] as $p)
                            {{ ucfirst($p) }}{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </span>
                </div>
                <div class="ts-info-row">
                    <span class="ts-info-lbl"><i class="fa-solid fa-user-pen" style="width:20px"></i> Creator</span>
                    <span class="ts-info-val">{{ $task->creator->name ?? 'System' }}</span>
                </div>
                <div class="ts-info-row">
                    <span class="ts-info-lbl"><i class="fa-solid fa-user-check" style="width:20px"></i> Assignee</span>
                    <span class="ts-info-val">{{ $task->assignee->name ?? 'Unassigned' }}</span>
                </div>
            </div>

            <div class="ts-card">
                <div class="ts-sec-title"><i class="fa-solid fa-share-nodes"></i> Activity</div>
                @if($task->socialMediaPosts->count())
                    @foreach($task->socialMediaPosts as $post)
                        <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:18px;">
                            <div style="width:36px; height:36px; border-radius:10px; background:var(--card2); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="fa-brands fa-{{ $post->platform === 'twitter' ? 'x-twitter' : $post->platform }}" style="font-size:18px; color:var(--primary)"></i>
                            </div>
                            <div>
                                <div style="font-size:13px; font-weight:800; color:var(--text)">Posted on {{ ucfirst($post->platform) }}</div>
                                <div style="font-size:11px; color:var(--text3); font-weight:700; margin-bottom:4px;">{{ $post->posted_at?->format('M d, Y') }}</div>
                                
                                @if($post->post_url)
                                    <a href="{{ $post->post_url }}" target="_blank" class="ts-visit-link">
                                        Visit Live Post <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                @endif

                                @if($post->proof_image)
                                    <a href="javascript:void(0)" onclick="openLightbox('{{ Storage::url($post->proof_image) }}', 'image')" class="ts-visit-link" style="color:var(--teal)">
                                        View Proof Screenshot <i class="fa-solid fa-image"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="font-size:13px; color:var(--text3); font-weight:700; text-align:center; padding:10px;">
                        No social media activity logged.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Lightbox --}}
<div class="ts-lightbox" id="tsLightbox" onclick="closeLightbox()">
    <button class="ts-lightbox-close" onclick="closeLightbox()"><i class="fa-solid fa-xmark"></i></button>
    <div id="tsLightboxBody" onclick="event.stopPropagation()">
        <!-- Media content -->
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openLightbox(url, type) {
        const lb = document.getElementById('tsLightbox');
        const body = document.getElementById('tsLightboxBody');
        
        if (type === 'video') {
            body.innerHTML = `<video src="${url}" controls autoplay class="ts-lightbox-content"></video>`;
        } else {
            body.innerHTML = `<img src="${url}" class="ts-lightbox-content">`;
        }
        
        lb.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        const lb = document.getElementById('tsLightbox');
        lb.classList.remove('active');
        document.body.style.overflow = '';
        // Clear content after animation
        setTimeout(() => {
            document.getElementById('tsLightboxBody').innerHTML = '';
        }, 300);
    }

    // Escape to close
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeLightbox();
    });

    function copyCaption() {
        const text = document.getElementById('captionText').innerText.replace('Copy', '').trim();
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.querySelector('.ts-copy-btn');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
            btn.style.color = 'var(--teal)';
            btn.style.borderColor = 'var(--teal)';
            setTimeout(() => {
                btn.innerHTML = original;
                btn.style.color = '';
                btn.style.borderColor = '';
            }, 2000);
        });
    }
</script>
@endpush
