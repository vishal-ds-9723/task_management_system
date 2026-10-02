@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
/* ── Admin Publishing Show ──────────────────── */
.aps-back{display:inline-flex;align-items:center;gap:6px;color:var(--text3);font-size:12.5px;text-decoration:none;margin-bottom:20px;font-weight:600;transition:all .2s;padding:6px 12px;border-radius:8px}
.aps-back:hover{color:var(--primary);background:var(--primary-dim)}

.aps-banner{background:linear-gradient(135deg,#10B981 0%,#059669 100%);color:#fff;border-radius:var(--radius);padding:20px 24px;margin-bottom:20px;display:flex;align-items:center;gap:16px;box-shadow:0 4px 16px rgba(16,185,129,.25)}
.aps-banner-icon{width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.aps-banner h3{font-size:16px;font-weight:700;margin:0 0 2px}
.aps-banner p{font-size:12.5px;opacity:.85;margin:0}

.aps-header{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;margin-bottom:20px;position:relative;overflow:hidden}
.aps-header::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--primary),#6882F5)}
.aps-title{font-size:18px;font-weight:800;color:var(--text);margin-bottom:10px;font-family:'Plus Jakarta Sans',sans-serif}
.aps-meta{display:flex;flex-wrap:wrap;gap:18px;font-size:12.5px;color:var(--text3)}
.aps-meta-item{display:flex;align-items:center;gap:5px;font-weight:500}
.aps-meta-item i{font-size:12px;width:14px;text-align:center}

.aps-grid{display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start}
.aps-platforms{display:flex;flex-direction:column;gap:14px}

.aps-platform-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:all .25s ease}
.aps-platform-card:hover{border-color:var(--primary);box-shadow:var(--shadow)}
.aps-platform-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.aps-platform-name{display:flex;align-items:center;gap:10px;font-size:14.5px;font-weight:700;color:var(--text)}
.aps-platform-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0}
.aps-platform-body{padding:16px 20px}

.aps-status-badge{padding:4px 12px;border-radius:20px;font-size:10.5px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;display:inline-flex;align-items:center;gap:4px}
.aps-status-done{background:rgba(16,185,129,.1);color:#059669}
.aps-status-pending{background:rgba(245,158,11,.08);color:#D97706}

.aps-proof-box{background:rgba(16,185,129,.04);border:1px solid rgba(16,185,129,.12);border-radius:10px;padding:14px 16px}
.aps-proof-link{font-size:13px;color:var(--primary);word-break:break-all;text-decoration:none;font-weight:600;display:flex;align-items:center;gap:6px}
.aps-proof-link:hover{text-decoration:underline}
.aps-proof-meta{display:flex;gap:14px;font-size:11.5px;color:var(--text3);margin-top:8px;flex-wrap:wrap;font-weight:500}
.aps-proof-meta span{display:flex;align-items:center;gap:4px}

.aps-no-proof{padding:20px;text-align:center;color:var(--text3);font-size:12.5px;background:var(--card2);border:1px dashed var(--border2);border-radius:10px;font-weight:500}
.aps-no-proof i{font-size:20px;display:block;margin-bottom:6px;color:var(--border2)}

/* Sidebar */
.aps-sidebar{position:sticky;top:20px;display:flex;flex-direction:column;gap:14px}
.aps-sidebar-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;overflow:hidden}
.aps-sidebar-title{font-size:12px;font-weight:700;color:var(--text);margin-bottom:14px;display:flex;align-items:center;gap:7px;text-transform:uppercase;letter-spacing:.5px}
.aps-sidebar-title i{color:var(--primary);font-size:13px}

/* Progress ring */
.aps-progress-wrap{text-align:center;padding:10px 0}
.aps-progress-ring{position:relative;width:110px;height:110px;margin:0 auto 14px}
.aps-progress-ring svg{transform:rotate(-90deg);width:110px;height:110px}
.aps-progress-ring circle{fill:none;stroke-width:8;stroke-linecap:round}
.aps-progress-ring .ring-bg{stroke:var(--border)}
.aps-progress-ring .ring-fill{transition:stroke-dashoffset .6s ease}
.aps-progress-val{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.aps-progress-big{font-size:28px;font-weight:800;color:var(--text);line-height:1}
.aps-progress-sub{font-size:11px;color:var(--text3);font-weight:600;margin-top:2px}
.aps-progress-label{font-size:13px;font-weight:700;margin-top:4px}

/* Detail rows */
.aps-detail-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);font-size:12.5px}
.aps-detail-row:last-child{border-bottom:none}
.aps-detail-key{color:var(--text3);font-weight:500}
.aps-detail-val{font-weight:600;color:var(--text);text-align:right}

/* View task link */
.aps-view-task{display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:8px;background:var(--card2);color:var(--text2);font-size:12px;font-weight:600;text-decoration:none;transition:all .2s;border:1px solid var(--border)}
.aps-view-task:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim)}

@media(max-width:900px){
    .aps-grid{grid-template-columns:1fr}
    .aps-sidebar{position:static}
}
</style>
@endpush

@section('content')

<a href="{{ route('admin.publishing') }}" class="aps-back"><i class="fa-solid fa-arrow-left"></i> Publishing Tracker</a>

@if($task->status === 'published')
    <div class="aps-banner">
        <div class="aps-banner-icon"><i class="fa-solid fa-rocket"></i></div>
        <div>
            <h3>Fully Published</h3>
            <p>All social media proofs uploaded — this task is complete!</p>
        </div>
    </div>
@endif

<div class="aps-header">
    <div class="aps-title">{{ $task->title ?: 'Untitled Task' }}</div>
    <div class="aps-meta">
        <div class="aps-meta-item"><i class="fa-solid fa-building"></i> {{ $task->client->name ?? '—' }}</div>
        <div class="aps-meta-item"><i class="fa-solid fa-tag"></i> {{ ucfirst($task->type) }}</div>
        @if($task->creator)
            <div class="aps-meta-item"><i class="fa-solid fa-user"></i> Strategist: {{ $task->creator->name }}</div>
        @endif
        @if($task->assignee)
            <div class="aps-meta-item"><i class="fa-solid fa-palette"></i> Designer: {{ $task->assignee->name }}</div>
        @endif
        @if($task->completed_at)
            <div class="aps-meta-item"><i class="fa-solid fa-check-circle" style="color:#10B981"></i> Approved: {{ $task->completed_at->format('M d, Y') }}</div>
        @endif
    </div>
</div>

@php
    $pct = $progress['total'] > 0 ? round(($progress['posted'] / $progress['total']) * 100) : 0;
    $postedPlatforms = $task->socialMediaPosts->pluck('platform')->toArray();
    $circumference = 2 * 3.14159 * 45;
    $dashoffset = $circumference - ($pct / 100) * $circumference;
    $ringColor = $pct >= 100 ? '#10B981' : ($pct > 0 ? '#3B82F6' : '#94A3B8');
    $platformIcons = [
        'instagram' => 'fa-brands fa-instagram',
        'facebook'  => 'fa-brands fa-facebook',
        'linkedin'  => 'fa-brands fa-linkedin',
        'twitter'   => 'fa-brands fa-x-twitter',
        'tiktok'    => 'fa-brands fa-tiktok',
        'youtube'   => 'fa-brands fa-youtube',
    ];
    $platformColors = [
        'instagram' => '#E1306C', 'facebook' => '#1877F2', 'linkedin' => '#0A66C2',
        'twitter' => '#1DA1F2', 'tiktok' => '#000', 'youtube' => '#FF0000',
    ];
@endphp

<div class="aps-grid">
    {{-- LEFT: Platform cards --}}
    <div class="aps-platforms">
        @foreach($platforms as $platform)
            @php
                $post = $task->socialMediaPosts->firstWhere('platform', $platform);
                $done = !is_null($post);
                $pIcon = $platformIcons[$platform] ?? 'fa-solid fa-globe';
                $color = $platformColors[$platform] ?? '#6B7280';
            @endphp
            <div class="aps-platform-card">
                <div class="aps-platform-header">
                    <div class="aps-platform-name">
                        <div class="aps-platform-icon" style="background:{{ $color }}12;color:{{ $color }}"><i class="{{ $pIcon }}"></i></div>
                        {{ ucfirst($platform) }}
                    </div>
                    <span class="aps-status-badge {{ $done ? 'aps-status-done' : 'aps-status-pending' }}">
                        <i class="fa-solid fa-{{ $done ? 'check-circle' : 'clock' }}"></i>
                        {{ $done ? 'Posted' : 'Pending' }}
                    </span>
                </div>

                <div class="aps-platform-body">
                    @if($done)
                        @php
                            $latestMetric = $post->metrics->first();
                        @endphp
                        <div class="aps-proof-box">
                            @if($post->post_url)
                                <a href="{{ $post->post_url }}" target="_blank" rel="noopener noreferrer" class="aps-proof-link">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> {{ Str::limit($post->post_url, 60) }}
                                </a>
                            @else
                                <div style="font-size:12px;color:var(--text3);font-weight:600">
                                    <i class="fa-regular fa-image" style="margin-right:4px"></i>
                                    Screenshot proof uploaded
                                </div>
                            @endif

                            @if($post->proof_image)
                                <div style="margin-top:8px">
                                    <a href="{{ asset('storage/' . $post->proof_image) }}" target="_blank" style="font-size:11.5px;font-weight:600;color:var(--primary);text-decoration:none">
                                        <i class="fa-regular fa-file-image" style="margin-right:4px"></i> View proof image
                                    </a>
                                </div>
                            @endif

                            <div class="aps-proof-meta">
                                <span><i class="fa-solid fa-tag"></i> {{ ucfirst($post->post_type) }}</span>
                                <span><i class="fa-regular fa-calendar"></i> {{ date('M d, Y', strtotime((string) $post->posted_at)) }}</span>
                                <span><i class="fa-solid fa-user"></i> {{ $post->poster->name ?? '—' }}</span>
                                <span><i class="fa-regular fa-clock"></i> {{ $post->created_at->diffForHumans() }}</span>
                            </div>

                            @if($latestMetric)
                                <div style="margin-top:10px;padding-top:10px;border-top:1px dashed rgba(16,185,129,.2)">
                                    <div style="font-size:10.5px;font-weight:700;color:#047857;text-transform:uppercase;letter-spacing:.35px;margin-bottom:7px">
                                        <i class="fa-solid fa-chart-line" style="margin-right:3px"></i>
                                        Latest Analytics ({{ $latestMetric->snapshot_date->format('M d, Y') }})
                                    </div>
                                    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;font-size:11px;color:var(--text2)">
                                        <div><strong>Views:</strong> {{ $latestMetric->views !== null ? number_format($latestMetric->views) : '—' }}</div>
                                        <div><strong>Impr:</strong> {{ $latestMetric->impressions !== null ? number_format($latestMetric->impressions) : '—' }}</div>
                                        <div><strong>Reach:</strong> {{ $latestMetric->reach !== null ? number_format($latestMetric->reach) : '—' }}</div>
                                        <div><strong>Likes:</strong> {{ $latestMetric->likes !== null ? number_format($latestMetric->likes) : '—' }}</div>
                                        <div><strong>Comments:</strong> {{ $latestMetric->comments !== null ? number_format($latestMetric->comments) : '—' }}</div>
                                        <div><strong>Shares:</strong> {{ $latestMetric->shares !== null ? number_format($latestMetric->shares) : '—' }}</div>
                                    </div>
                                    <div style="font-size:10.5px;color:var(--text3);margin-top:7px;font-weight:600">
                                        Total snapshots: {{ $post->metrics->count() }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="aps-no-proof">
                            <i class="fa-regular fa-clock"></i>
                            Proof not yet uploaded by strategist
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- RIGHT: Sidebar --}}
    <div class="aps-sidebar">
        {{-- Progress --}}
        <div class="aps-sidebar-card">
            <div class="aps-sidebar-title"><i class="fa-solid fa-chart-pie"></i> Progress</div>
            <div class="aps-progress-wrap">
                <div class="aps-progress-ring">
                    <svg viewBox="0 0 110 110">
                        <circle class="ring-bg" cx="55" cy="55" r="45" />
                        <circle class="ring-fill" cx="55" cy="55" r="45"
                            stroke="{{ $ringColor }}"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $dashoffset }}" />
                    </svg>
                    <div class="aps-progress-val">
                        <div class="aps-progress-big">{{ $pct }}%</div>
                        <div class="aps-progress-sub">{{ $progress['posted'] }}/{{ $progress['total'] }}</div>
                    </div>
                </div>
                <div class="aps-progress-label" style="color:{{ $ringColor }}">
                    {{ $pct >= 100 ? 'All Platforms Done' : ($pct > 0 ? 'In Progress' : 'Not Started') }}
                </div>
            </div>
        </div>

        {{-- Task Info --}}
        <div class="aps-sidebar-card">
            <div class="aps-sidebar-title"><i class="fa-solid fa-info-circle"></i> Task Details</div>
            @if($task->type)
                <div class="aps-detail-row">
                    <span class="aps-detail-key">Type</span>
                    <span class="aps-detail-val" style="background:var(--primary-dim);color:var(--primary);padding:2px 10px;border-radius:6px;font-size:10.5px;text-transform:uppercase">{{ $task->type }}</span>
                </div>
            @endif
            <div class="aps-detail-row">
                <span class="aps-detail-key">Status</span>
                <span class="aps-detail-val" style="background:{{ $task->status === 'published' ? 'rgba(16,185,129,.1)' : 'rgba(245,158,11,.08)' }};color:{{ $task->status === 'published' ? '#059669' : '#D97706' }};padding:2px 10px;border-radius:6px;font-size:10.5px;text-transform:uppercase">{{ $task->status_label }}</span>
            </div>
            @if($task->assignee)
                <div class="aps-detail-row">
                    <span class="aps-detail-key">Designer</span>
                    <span class="aps-detail-val">{{ $task->assignee->name }}</span>
                </div>
            @endif
            @if($task->creator)
                <div class="aps-detail-row">
                    <span class="aps-detail-key">Strategist</span>
                    <span class="aps-detail-val">{{ $task->creator->name }}</span>
                </div>
            @endif
            @if($task->post_date)
                <div class="aps-detail-row">
                    <span class="aps-detail-key">Post Date</span>
                    <span class="aps-detail-val">{{ date('M d, Y', strtotime((string) $task->post_date)) }}</span>
                </div>
            @endif
            @if($task->completed_at)
                <div class="aps-detail-row">
                    <span class="aps-detail-key">Approved</span>
                    <span class="aps-detail-val">{{ $task->completed_at->format('M d, Y') }}</span>
                </div>
            @endif
        </div>

        {{-- View Task --}}
        <a href="{{ route('admin.tasks.show', $task) }}" class="aps-view-task">
            <i class="fa-solid fa-eye"></i> View Full Task
        </a>
    </div>
</div>

@endsection
