@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
/* ── Publishing Queue ─────────────────────────── */
.pq-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px}
.pq-header-left{display:flex;align-items:center;gap:14px}
.pq-icon-wrap{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--primary),#6882F5);display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;box-shadow:0 4px 12px rgba(79,109,240,.25)}
.pq-title{font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.3px;font-family:'Plus Jakarta Sans',sans-serif}
.pq-subtitle{font-size:12px;color:var(--text3);margin-top:2px;font-weight:500}

/* Stats Grid */
.pq-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.pq-stat{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px 16px;display:flex;align-items:center;gap:14px;cursor:pointer;transition:all .25s ease;text-decoration:none;position:relative;overflow:hidden}
.pq-stat::before{content:'';position:absolute;inset:0;opacity:0;transition:opacity .25s;pointer-events:none;border-radius:inherit}
.pq-stat:hover{border-color:var(--primary);transform:translateY(-2px);box-shadow:var(--shadow)}
.pq-stat-active{border-color:var(--primary) !important;box-shadow:0 0 0 3px rgba(79,109,240,.08),var(--shadow-sm)}
.pq-stat-active .pq-stat-val{color:var(--primary)}
.pq-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;transition:transform .2s}
.pq-stat:hover .pq-stat-icon{transform:scale(1.08)}
.pq-stat-val{font-size:24px;font-weight:800;color:var(--text);line-height:1;transition:color .2s}
.pq-stat-label{font-size:11px;color:var(--text3);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:3px}

/* Filter Bar */
.pq-filter-bar{display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:20px;flex-wrap:wrap}
.pq-filter-bar i.fa-filter{color:var(--text3);font-size:13px;margin-right:2px}
.pq-filter-bar select,.pq-filter-bar input[type="text"]{height:36px;border:1px solid var(--border);border-radius:8px;padding:0 12px;font-size:12.5px;background:var(--bg);color:var(--text);transition:border-color .2s;outline:none;font-weight:500}
.pq-filter-bar select:focus,.pq-filter-bar input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,109,240,.08)}
.pq-filter-bar input[type="text"]{width:220px}
.pq-filter-bar .pq-filter-sep{width:1px;height:24px;background:var(--border);margin:0 4px}
.pq-btn{padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;cursor:pointer;border:none;transition:all .2s;white-space:nowrap}
.pq-btn-primary{background:var(--primary);color:#fff}
.pq-btn-primary:hover{background:var(--primary-hover);box-shadow:0 2px 8px rgba(79,109,240,.2)}
.pq-btn-ghost{background:transparent;color:var(--text3);font-weight:500}
.pq-btn-ghost:hover{color:var(--primary);background:var(--primary-dim)}
.pq-btn-outline{background:var(--card);color:var(--text2);border:1px solid var(--border)}
.pq-btn-outline:hover{border-color:var(--primary);color:var(--primary)}

/* Task List */
.pq-list{display:flex;flex-direction:column;gap:10px}

/* Task Card */
.pq-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:0;transition:all .25s ease;overflow:hidden;position:relative}
.pq-card:hover{border-color:var(--primary);box-shadow:var(--shadow);transform:translateY(-1px)}
.pq-card-inner{display:grid;grid-template-columns:1fr auto;align-items:center;gap:16px;padding:18px 20px}
.pq-card-left{display:flex;flex-direction:column;gap:10px;min-width:0}
.pq-card-right{display:flex;align-items:center;gap:10px;flex-shrink:0}

/* Task title row */
.pq-task-title-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.pq-task-title{font-size:14.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:420px}
.pq-task-type{display:inline-flex;align-items:center;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.3px;background:var(--primary-dim);color:var(--primary)}

/* Meta */
.pq-meta{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.pq-meta-item{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;color:var(--text3);font-weight:500}
.pq-meta-item i{font-size:11px;width:14px;text-align:center}
.pq-meta-sep{width:3px;height:3px;border-radius:50%;background:var(--border2);flex-shrink:0}

/* Platform pills */
.pq-platforms{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
.pq-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:600;transition:all .2s;cursor:default}
.pq-pill-done{background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.2)}
.pq-pill-done i{font-size:9px}
.pq-pill-pending{background:var(--card2);color:var(--text3);border:1px dashed var(--border2)}
.pq-pill-icon{font-size:13px;line-height:1}

/* Progress */
.pq-progress{display:flex;align-items:center;gap:10px;padding:0 20px 14px}
.pq-progress-track{flex:1;height:5px;background:var(--card2);border-radius:3px;overflow:hidden}
.pq-progress-fill{height:100%;border-radius:3px;transition:width .4s ease}
.pq-progress-fill-full{background:linear-gradient(135deg,#10B981,#059669)}
.pq-progress-fill-partial{background:linear-gradient(135deg,#3B82F6,#2563EB)}
.pq-progress-fill-none{background:var(--border)}
.pq-progress-label{font-size:11px;font-weight:700;white-space:nowrap;min-width:32px;text-align:right}

/* Status badges */
.pq-badge{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;font-size:11px;font-weight:700;letter-spacing:.2px;white-space:nowrap}
.pq-badge-published{background:rgba(16,185,129,.1);color:#059669}
.pq-badge-partial{background:rgba(59,130,246,.1);color:#2563EB}
.pq-badge-awaiting{background:rgba(245,158,11,.08);color:#D97706}

/* Action button */
.pq-action-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;color:#fff;background:var(--primary);transition:all .2s;border:none;cursor:pointer}
.pq-action-btn:hover{background:var(--primary-hover);box-shadow:0 4px 12px rgba(79,109,240,.2);transform:translateY(-1px)}
.pq-action-btn-view{background:var(--card2);color:var(--text2);border:1px solid var(--border)}
.pq-action-btn-view:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim)}

/* Empty State */
.pq-empty{text-align:center;padding:80px 20px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius)}
.pq-empty-icon{width:72px;height:72px;border-radius:20px;background:var(--card2);display:flex;align-items:center;justify-content:center;font-size:28px;color:var(--text3);margin:0 auto 16px}
.pq-empty-title{font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px}
.pq-empty-text{font-size:12.5px;color:var(--text3);max-width:320px;margin:0 auto}

/* Responsive */
@media(max-width:900px){
    .pq-stats{grid-template-columns:repeat(2,1fr)}
    .pq-card-inner{grid-template-columns:1fr;gap:12px}
    .pq-card-right{justify-content:flex-start;flex-wrap:wrap}
    .pq-task-title{max-width:100%}
}
@media(max-width:500px){
    .pq-stats{grid-template-columns:1fr 1fr}
    .pq-filter-bar{flex-direction:column;align-items:stretch}
    .pq-filter-bar input[type="text"]{width:100%}
    .pq-filter-bar .pq-filter-sep{display:none}
}
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="pq-header">
    <div class="pq-header-left">
        <div class="pq-icon-wrap"><i class="fa-solid fa-share-nodes"></i></div>
        <div>
            <div class="pq-title">Publishing Queue</div>
            <div class="pq-subtitle">Upload proof links for approved tasks before they're marked done</div>
        </div>
    </div>
    <a href="{{ route('strategist.tracking') }}" class="pq-btn pq-btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Tracking</a>
</div>

{{-- Stats --}}
<div class="pq-stats">
    <a href="{{ route('strategist.publishing') }}" class="pq-stat {{ !request('filter') ? 'pq-stat-active' : '' }}">
        <div class="pq-stat-icon" style="background:var(--primary-dim);color:var(--primary)"><i class="fa-solid fa-layer-group"></i></div>
        <div>
            <div class="pq-stat-val">{{ $totalCompleted }}</div>
            <div class="pq-stat-label">All Tasks</div>
        </div>
    </a>
    <a href="{{ route('strategist.publishing', ['filter' => 'awaiting']) }}" class="pq-stat {{ request('filter') === 'awaiting' ? 'pq-stat-active' : '' }}">
        <div class="pq-stat-icon" style="background:var(--yellow-dim);color:var(--yellow)"><i class="fa-solid fa-hourglass-half"></i></div>
        <div>
            <div class="pq-stat-val">{{ $awaitingCount }}</div>
            <div class="pq-stat-label">Awaiting Proof</div>
        </div>
    </a>
    <a href="{{ route('strategist.publishing', ['filter' => 'partial']) }}" class="pq-stat {{ request('filter') === 'partial' ? 'pq-stat-active' : '' }}">
        <div class="pq-stat-icon" style="background:var(--blue-dim);color:var(--blue)"><i class="fa-solid fa-circle-half-stroke"></i></div>
        <div>
            <div class="pq-stat-val">{{ $partialCount }}</div>
            <div class="pq-stat-label">Partial Proof</div>
        </div>
    </a>
    <a href="{{ route('strategist.publishing', ['filter' => 'published']) }}" class="pq-stat {{ request('filter') === 'published' ? 'pq-stat-active' : '' }}">
        <div class="pq-stat-icon" style="background:var(--teal-dim);color:var(--teal)"><i class="fa-solid fa-circle-check"></i></div>
        <div>
            <div class="pq-stat-val">{{ $publishedCount }}</div>
            <div class="pq-stat-label">Published</div>
        </div>
    </a>
</div>

{{-- Filter Bar --}}
<form class="pq-filter-bar" method="GET">
    <i class="fa-solid fa-filter"></i>
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by task name...">
    <div class="pq-filter-sep"></div>
    <select name="client">
        <option value="">All Clients</option>
        @foreach($clients as $client)
            <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
        @endforeach
    </select>
    @if(request('filter'))
        <input type="hidden" name="filter" value="{{ request('filter') }}">
    @endif
    <button type="submit" class="pq-btn pq-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    @if(request()->hasAny(['search', 'client']))
        <a href="{{ route('strategist.publishing', request('filter') ? ['filter' => request('filter')] : []) }}" class="pq-btn pq-btn-ghost"><i class="fa-solid fa-xmark"></i> Clear</a>
    @endif
</form>

{{-- Task List --}}
<div class="pq-list">
@forelse($tasks as $task)
    @php
        $progress = $task->getPublishingProgress();
        $pct = $progress['total'] > 0 ? round(($progress['posted'] / $progress['total']) * 100) : 0;
        $platforms = is_array($task->platform) ? $task->platform : [];
        $postedPlatforms = $task->socialMediaPosts->pluck('platform')->toArray();
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
    <div class="pq-card">
        <div class="pq-card-inner">
            <div class="pq-card-left">
                {{-- Title row --}}
                <div class="pq-task-title-row">
                    <span class="pq-task-title">{{ $task->title ?: 'Untitled Task' }}</span>
                    @if($task->type)
                        <span class="pq-task-type">{{ $task->type }}</span>
                    @endif
                </div>

                {{-- Meta --}}
                <div class="pq-meta">
                    <span class="pq-meta-item"><i class="fa-solid fa-building"></i> {{ $task->client->name ?? '—' }}</span>
                    @if($task->creator)
                        <span class="pq-meta-sep"></span>
                        <span class="pq-meta-item" style="color:var(--primary);font-weight:700"><i class="fa-solid fa-user-pen"></i> By: {{ $task->creator->name }}</span>
                    @endif
                    @if($task->completed_at)
                        <span class="pq-meta-sep"></span>
                        <span class="pq-meta-item"><i class="fa-solid fa-check-circle" style="color:var(--teal)"></i> Approved {{ $task->completed_at->diffForHumans() }}</span>
                    @endif
                </div>

                {{-- Platforms --}}
                <div class="pq-platforms">
                    @foreach($platforms as $platform)
                        @php
                            $posted = in_array($platform, $postedPlatforms);
                            $pIcon = $platformIcons[$platform] ?? 'fa-solid fa-globe';
                            $pColor = $platformColors[$platform] ?? '#6B7280';
                        @endphp
                        <span class="pq-pill {{ $posted ? 'pq-pill-done' : 'pq-pill-pending' }}">
                            <i class="{{ $pIcon }}" style="font-size:13px;{{ $posted ? '' : 'color:'.$pColor }}"></i>
                            {{ ucfirst($platform) }}
                            @if($posted) <i class="fa-solid fa-check"></i> @endif
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="pq-card-right">
                @if($task->status === 'published')
                    <span class="pq-badge pq-badge-published"><i class="fa-solid fa-check-double"></i> Published</span>
                    <a href="{{ route('strategist.publishing.show', $task) }}" class="pq-action-btn pq-action-btn-view"><i class="fa-solid fa-eye"></i> View</a>
                @else
                    <span class="pq-badge {{ $progress['posted'] > 0 ? 'pq-badge-partial' : 'pq-badge-awaiting' }}">
                        <i class="fa-solid fa-{{ $progress['posted'] > 0 ? 'circle-half-stroke' : 'hourglass-half' }}"></i>
                        {{ $progress['posted'] }}/{{ $progress['total'] }}
                    </span>
                    <a href="{{ route('strategist.publishing.show', $task) }}" class="pq-action-btn"><i class="fa-solid fa-arrow-up-from-bracket"></i> Upload Proof</a>
                @endif
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="pq-progress">
            <div class="pq-progress-track">
                <div class="pq-progress-fill {{ $pct >= 100 ? 'pq-progress-fill-full' : ($pct > 0 ? 'pq-progress-fill-partial' : 'pq-progress-fill-none') }}" style="width:{{ $pct }}%"></div>
            </div>
            <span class="pq-progress-label" style="color:{{ $pct >= 100 ? '#059669' : ($pct > 0 ? '#2563EB' : 'var(--text3)') }}">{{ $pct }}%</span>
        </div>
    </div>
@empty
    <div class="pq-empty">
        <div class="pq-empty-icon"><i class="fa-regular fa-paper-plane"></i></div>
        <div class="pq-empty-title">{{ request('filter') ? 'No matching tasks' : 'All clear!' }}</div>
        <div class="pq-empty-text">{{ request('filter') ? 'No tasks match this filter. Try adjusting your criteria.' : 'No approved tasks awaiting publishing yet. Check back later!' }}</div>
    </div>
@endforelse
</div>

{{-- Pagination --}}
@if($tasks->hasPages())
    <div style="margin-top:20px;display:flex;justify-content:center">
        {{ $tasks->links() }}
    </div>
@endif

@endsection
