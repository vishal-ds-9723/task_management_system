@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
/* ── Admin Publishing Tracker ──────────────────── */
.apt-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px}
.apt-header-left{display:flex;align-items:center;gap:14px}
.apt-icon-wrap{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--primary),#6882F5);display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;box-shadow:0 4px 12px rgba(79,109,240,.25)}
.apt-title{font-size:22px;font-weight:800;color:var(--text);letter-spacing:-.3px;font-family:'Plus Jakarta Sans',sans-serif}
.apt-subtitle{font-size:12px;color:var(--text3);margin-top:2px;font-weight:500}
.apt-header-right{display:flex;gap:8px;align-items:center}
.apt-result-count{font-size:12px;color:var(--text3);font-weight:500;background:var(--card2);padding:6px 14px;border-radius:8px;display:flex;align-items:center;gap:6px}
.apt-result-count strong{color:var(--text);font-weight:700}

/* Stats */
.apt-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.apt-stat{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:18px 16px;display:flex;align-items:center;gap:14px;cursor:pointer;transition:all .25s ease;text-decoration:none;position:relative;overflow:hidden}
.apt-stat:hover{border-color:var(--primary);transform:translateY(-2px);box-shadow:var(--shadow)}
.apt-stat-active{border-color:var(--primary) !important;box-shadow:0 0 0 3px rgba(79,109,240,.08),var(--shadow-sm)}
.apt-stat-active .apt-stat-val{color:var(--primary)}
.apt-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;transition:transform .2s}
.apt-stat:hover .apt-stat-icon{transform:scale(1.08)}
.apt-stat-val{font-size:24px;font-weight:800;color:var(--text);line-height:1;transition:color .2s}
.apt-stat-label{font-size:11px;color:var(--text3);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:3px}

/* Filter Bar */
.apt-filter-bar{display:flex;align-items:center;gap:10px;padding:12px 16px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);margin-bottom:20px;flex-wrap:wrap}
.apt-filter-bar i.fa-filter{color:var(--text3);font-size:13px;margin-right:2px}
.apt-filter-bar select,.apt-filter-bar input[type="text"]{height:36px;border:1px solid var(--border);border-radius:8px;padding:0 12px;font-size:12.5px;background:var(--bg);color:var(--text);transition:border-color .2s;outline:none;font-weight:500}
.apt-filter-bar select:focus,.apt-filter-bar input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,109,240,.08)}
.apt-filter-bar input[type="text"]{width:200px}
.apt-filter-sep{width:1px;height:24px;background:var(--border);margin:0 4px}
.apt-btn{padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;cursor:pointer;border:none;transition:all .2s;white-space:nowrap}
.apt-btn-primary{background:var(--primary);color:#fff}
.apt-btn-primary:hover{background:var(--primary-hover);box-shadow:0 2px 8px rgba(79,109,240,.2)}
.apt-btn-ghost{background:transparent;color:var(--text3);font-weight:500}
.apt-btn-ghost:hover{color:var(--primary);background:var(--primary-dim)}

/* Table Container */
.apt-table-wrap{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.apt-table{width:100%;border-collapse:collapse;border-spacing:0}
.apt-table thead{position:sticky;top:0;z-index:2}
.apt-table th{background:var(--card2);padding:11px 16px;font-size:10.5px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.6px;text-align:left;border-bottom:2px solid var(--border);white-space:nowrap;-webkit-user-select:none;user-select:none}
.apt-table th:first-child{padding-left:20px}
.apt-table th:last-child{padding-right:20px}
.apt-table td{padding:14px 16px;font-size:12.5px;color:var(--text);border-bottom:1px solid var(--border);vertical-align:middle;transition:background .15s}
.apt-table td:first-child{padding-left:20px}
.apt-table td:last-child{padding-right:20px}
.apt-table tbody tr:last-child td{border-bottom:none}
.apt-table tbody tr{transition:background .15s}
.apt-table tbody tr:hover td{background:rgba(79,109,240,.02)}
.apt-table tbody tr:nth-child(even) td{background:rgba(0,0,0,.008)}
.apt-table tbody tr:nth-child(even):hover td{background:rgba(79,109,240,.03)}

/* Task cell */
.apt-task-cell{display:flex;align-items:center;gap:10px;min-width:180px}
.apt-task-num{width:28px;height:28px;border-radius:8px;background:var(--card2);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:var(--text3);flex-shrink:0;border:1px solid var(--border)}
.apt-task-info{min-width:0;flex:1}
.apt-task-title{font-weight:700;color:var(--text);font-size:13px;max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;line-height:1.3}
.apt-task-type{font-size:10px;color:var(--text3);font-weight:500;margin-top:1px;display:flex;align-items:center;gap:4px}
.apt-task-type i{font-size:9px}

/* Person cell */
.apt-person{display:flex;align-items:center;gap:7px;font-size:12px;font-weight:500;color:var(--text2)}
.apt-avatar{width:26px;height:26px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0;text-transform:uppercase}

/* Platform dots */
.apt-platform-dots{display:flex;gap:5px;flex-wrap:wrap}
.apt-platform-dot{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;transition:all .2s;cursor:default;position:relative}
.apt-platform-dot-done{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2)}
.apt-platform-dot-done::after{content:'✓';position:absolute;bottom:-1px;right:-1px;font-size:7px;font-weight:900;background:#10B981;color:#fff;width:12px;height:12px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--card)}
.apt-platform-dot-pending{background:var(--card2);border:1px dashed var(--border2);opacity:.6}
.apt-platform-dot-pending:hover{opacity:1}

/* Progress */
.apt-progress-mini{display:flex;align-items:center;gap:8px}
.apt-progress-bar{width:72px;height:6px;background:var(--card2);border-radius:3px;overflow:hidden}
.apt-progress-fill{height:100%;border-radius:3px;transition:width .3s ease}
.apt-progress-fill-full{background:linear-gradient(135deg,#10B981,#059669)}
.apt-progress-fill-partial{background:linear-gradient(135deg,#3B82F6,#2563EB)}
.apt-progress-fill-none{background:var(--border)}
.apt-progress-text{font-size:11px;font-weight:700;min-width:30px}

/* Status */
.apt-status-tag{padding:4px 10px;border-radius:6px;font-size:10.5px;font-weight:700;white-space:nowrap;display:inline-flex;align-items:center;gap:4px;letter-spacing:.2px}
.apt-status-awaiting{background:rgba(245,158,11,.08);color:#D97706}
.apt-status-partial{background:rgba(59,130,246,.08);color:#2563EB}
.apt-status-published{background:rgba(16,185,129,.08);color:#059669}
.apt-status-icon{font-size:8px}

/* View button */
.apt-view-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:8px;font-size:11.5px;font-weight:600;text-decoration:none;transition:all .2s;border:1px solid var(--border);color:var(--text2);background:var(--card)}
.apt-view-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim);transform:translateY(-1px)}

/* Date cell */
.apt-date{font-size:11.5px;color:var(--text3);font-weight:500;white-space:nowrap}
.apt-date-ago{display:block;font-size:10px;color:var(--text3);opacity:.7;margin-top:1px}

/* Empty */
.apt-empty{text-align:center;padding:80px 20px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius)}
.apt-empty-icon{width:72px;height:72px;border-radius:20px;background:var(--card2);display:flex;align-items:center;justify-content:center;font-size:28px;color:var(--text3);margin:0 auto 16px}
.apt-empty-title{font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px}
.apt-empty-text{font-size:12.5px;color:var(--text3);max-width:320px;margin:0 auto}

/* Pagination */
.apt-pagination{margin-top:20px;display:flex;justify-content:center;align-items:center;gap:4px;flex-wrap:wrap}
.apt-page-btn{width:36px;height:36px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;text-decoration:none;transition:all .2s;cursor:pointer;border:1px solid var(--border);background:var(--card);color:var(--text2)}
.apt-page-btn:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-dim);transform:translateY(-1px)}
.apt-page-active{background:var(--primary) !important;color:#fff !important;border-color:var(--primary) !important;box-shadow:0 2px 8px rgba(79,109,240,.25);cursor:default;pointer-events:none}
.apt-page-disabled{opacity:.4;cursor:not-allowed;pointer-events:none}
.apt-page-dots{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;font-size:14px;color:var(--text3);letter-spacing:2px;cursor:default}
.apt-page-summary{margin-left:12px;font-size:12px;color:var(--text3);font-weight:500;white-space:nowrap}
.apt-page-info{text-align:center;font-size:12px;color:var(--text3);margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)}

/* Responsive */
@media(max-width:900px){
    .apt-stats{grid-template-columns:repeat(2,1fr)}
    .apt-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
    .apt-table{min-width:800px}
    .apt-filter-bar{flex-direction:column;align-items:stretch}
    .apt-filter-bar input[type="text"]{width:100%}
    .apt-filter-sep{display:none}
}
@media(max-width:500px){
    .apt-stats{grid-template-columns:1fr 1fr}
}
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="apt-header">
    <div class="apt-header-left">
        <div class="apt-icon-wrap"><i class="fa-solid fa-tower-broadcast"></i></div>
        <div>
            <div class="apt-title">Publishing Tracker</div>
            <div class="apt-subtitle">Monitor social media proof uploads across all approved tasks</div>
        </div>
    </div>
    <div class="apt-header-right">
        <div class="apt-result-count">
            <i class="fa-solid fa-list-check"></i>
            Showing <strong>{{ $tasks->count() }}</strong> of <strong>{{ $tasks->total() }}</strong> tasks
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="apt-stats">
    <a href="{{ route('admin.publishing') }}" class="apt-stat {{ !request('filter') ? 'apt-stat-active' : '' }}">
        <div class="apt-stat-icon" style="background:var(--primary-dim);color:var(--primary)"><i class="fa-solid fa-layer-group"></i></div>
        <div>
            <div class="apt-stat-val">{{ $totalCount }}</div>
            <div class="apt-stat-label">All Tasks</div>
        </div>
    </a>
    <a href="{{ route('admin.publishing', ['filter' => 'awaiting']) }}" class="apt-stat {{ request('filter') === 'awaiting' ? 'apt-stat-active' : '' }}">
        <div class="apt-stat-icon" style="background:var(--yellow-dim);color:var(--yellow)"><i class="fa-solid fa-hourglass-half"></i></div>
        <div>
            <div class="apt-stat-val">{{ $awaitingCount }}</div>
            <div class="apt-stat-label">Awaiting Proof</div>
        </div>
    </a>
    <a href="{{ route('admin.publishing', ['filter' => 'partial']) }}" class="apt-stat {{ request('filter') === 'partial' ? 'apt-stat-active' : '' }}">
        <div class="apt-stat-icon" style="background:var(--blue-dim);color:var(--blue)"><i class="fa-solid fa-circle-half-stroke"></i></div>
        <div>
            <div class="apt-stat-val">{{ $partialCount }}</div>
            <div class="apt-stat-label">Partial Proof</div>
        </div>
    </a>
    <a href="{{ route('admin.publishing', ['filter' => 'published']) }}" class="apt-stat {{ request('filter') === 'published' ? 'apt-stat-active' : '' }}">
        <div class="apt-stat-icon" style="background:var(--teal-dim);color:var(--teal)"><i class="fa-solid fa-circle-check"></i></div>
        <div>
            <div class="apt-stat-val">{{ $publishedCount }}</div>
            <div class="apt-stat-label">Published</div>
        </div>
    </a>
</div>

{{-- Filter Bar --}}
<form class="apt-filter-bar" method="GET" action="{{ route('admin.publishing') }}" id="publishingFilterForm">
    <i class="fa-solid fa-filter"></i>
    <input type="text" name="search" id="publishingSearchInput" value="{{ request('search') }}" placeholder="Search by task or client..." autocomplete="off">
    <div class="apt-filter-sep"></div>
    <select name="client">
        <option value="">All Clients</option>
        @foreach($clients as $client)
            <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
        @endforeach
    </select>
    <select name="creator">
        <option value="">All Strategists</option>
        @foreach($strategists as $s)
            <option value="{{ $s->id }}" {{ request('creator') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
    </select>
    @if(request('filter'))
        <input type="hidden" name="filter" value="{{ request('filter') }}">
    @endif
    <button type="submit" class="apt-btn apt-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    @if(request()->hasAny(['search', 'client', 'creator']))
        <a href="{{ route('admin.publishing', request('filter') ? ['filter' => request('filter')] : []) }}" class="apt-btn apt-btn-ghost"><i class="fa-solid fa-xmark"></i> Clear</a>
    @endif
</form>

{{-- Page Info --}}
@if($tasks->hasPages())
    <div class="apt-page-info">
        Showing <strong>{{ ($tasks->currentPage()-1) * $tasks->perPage() + 1 }}</strong>–<strong>{{ min($tasks->currentPage() * $tasks->perPage(), $tasks->total()) }}</strong> of <strong>{{ $tasks->total() }}</strong> tasks • Page <strong>{{ $tasks->currentPage() }}</strong> of <strong>{{ $tasks->lastPage() }}</strong>
    </div>
@endif

{{-- Table --}}
@if($tasks->count())
@php
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
    $avatarColors = ['#4F6DF0','#8B5CF6','#EC4899','#F59E0B','#10B981','#EF4444','#06B6D4','#F97316'];
@endphp

<div class="apt-table-wrap">
<table class="apt-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Task</th>
            <th>Client</th>
            <th>Strategist</th>
            <th>Platforms</th>
            <th>Progress</th>
            <th>Status</th>
            <th>Approved</th>
            <th style="text-align:right">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tasks as $i => $task)
            @php
                $progress = $task->getPublishingProgress();
                $pct = $progress['total'] > 0 ? round(($progress['posted'] / $progress['total']) * 100) : 0;
                $platforms = is_array($task->platform) ? $task->platform : [];
                $postedPlatforms = $task->socialMediaPosts->pluck('platform')->toArray();
                $rowNum = ($tasks->currentPage() - 1) * $tasks->perPage() + $i + 1;

                if ($task->status === 'published') {
                    $statusLabel = 'Published';
                    $statusClass = 'apt-status-published';
                    $statusIcon = 'fa-check-double';
                } elseif ($progress['posted'] > 0) {
                    $statusLabel = 'Partial';
                    $statusClass = 'apt-status-partial';
                    $statusIcon = 'fa-circle-half-stroke';
                } else {
                    $statusLabel = 'Awaiting';
                    $statusClass = 'apt-status-awaiting';
                    $statusIcon = 'fa-hourglass-half';
                }

                $progressClass = $pct >= 100 ? 'apt-progress-fill-full' : ($pct > 0 ? 'apt-progress-fill-partial' : 'apt-progress-fill-none');
                $progressColor = $pct >= 100 ? '#059669' : ($pct > 0 ? '#2563EB' : 'var(--text3)');

                $creatorColor = $avatarColors[($task->created_by ?? 0) % count($avatarColors)];
                $creatorInitials = $task->creator ? strtoupper(substr($task->creator->name, 0, 2)) : '—';
            @endphp
            <tr>
                <td><span class="apt-task-num">{{ $rowNum }}</span></td>
                <td>
                    <div class="apt-task-info">
                        <div class="apt-task-title" title="{{ $task->title }}">{{ Str::limit($task->title ?: 'Untitled', 40) }}</div>
                        @if($task->type)
                            <div class="apt-task-type"><i class="fa-solid fa-tag"></i> {{ ucfirst($task->type) }}</div>
                        @endif
                    </div>
                </td>
                <td>
                    <span class="apt-person" style="font-size:12px">{{ $task->client->name ?? '—' }}</span>
                </td>
                <td>
                    <div class="apt-person">
                        <div class="apt-avatar" style="background:{{ $creatorColor }}">{{ $creatorInitials }}</div>
                        {{ $task->creator->name ?? '—' }}
                    </div>
                </td>
                <td>
                    <div class="apt-platform-dots">
                        @foreach($platforms as $platform)
                            @php
                                $done = in_array($platform, $postedPlatforms);
                                $pIcon = $platformIcons[$platform] ?? 'fa-solid fa-globe';
                                $pColor = $platformColors[$platform] ?? '#6B7280';
                            @endphp
                            <span class="apt-platform-dot {{ $done ? 'apt-platform-dot-done' : 'apt-platform-dot-pending' }}" title="{{ ucfirst($platform) }}: {{ $done ? 'Posted' : 'Pending' }}">
                                <i class="{{ $pIcon }}" style="color:{{ $pColor }}"></i>
                            </span>
                        @endforeach
                    </div>
                </td>
                <td>
                    <div class="apt-progress-mini">
                        <div class="apt-progress-bar">
                            <div class="apt-progress-fill {{ $progressClass }}" style="width:{{ $pct }}%"></div>
                        </div>
                        <span class="apt-progress-text" style="color:{{ $progressColor }}">{{ $progress['posted'] }}/{{ $progress['total'] }}</span>
                    </div>
                </td>
                <td>
                    <span class="apt-status-tag {{ $statusClass }}">
                        <i class="fa-solid {{ $statusIcon }} apt-status-icon"></i> {{ $statusLabel }}
                    </span>
                </td>
                <td>
                    <div class="apt-date">
                        {{ $task->completed_at ? $task->completed_at->format('M d, Y') : '—' }}
                        @if($task->completed_at)
                            <span class="apt-date-ago">{{ $task->completed_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </td>
                <td style="text-align:right">
                    <a href="{{ route('admin.publishing.show', $task) }}" class="apt-view-btn"><i class="fa-solid fa-eye"></i> View</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>

@if($tasks->hasPages())
    <div class="apt-pagination">
        {{-- Previous --}}
        @if($tasks->onFirstPage())
            <span class="apt-page-btn apt-page-disabled"><i class="fa-solid fa-chevron-left"></i></span>
        @else
            <a href="{{ $tasks->previousPageUrl() }}" class="apt-page-btn"><i class="fa-solid fa-chevron-left"></i></a>
        @endif

        @foreach($tasks->getUrlRange(1, $tasks->lastPage()) as $page => $url)
            @if($page == $tasks->currentPage())
                <span class="apt-page-btn apt-page-active">{{ $page }}</span>
            @elseif($page == 1 || $page == $tasks->lastPage() || abs($page - $tasks->currentPage()) <= 2)
                <a href="{{ $url }}" class="apt-page-btn">{{ $page }}</a>
            @elseif($page == 2 && $tasks->currentPage() > 4)
                <span class="apt-page-dots">...</span>
            @elseif($page == $tasks->lastPage() - 1 && $tasks->currentPage() < $tasks->lastPage() - 3)
                <span class="apt-page-dots">...</span>
            @endif
        @endforeach

        {{-- Next --}}
        @if($tasks->hasMorePages())
            <a href="{{ $tasks->nextPageUrl() }}" class="apt-page-btn"><i class="fa-solid fa-chevron-right"></i></a>
        @else
            <span class="apt-page-btn apt-page-disabled"><i class="fa-solid fa-chevron-right"></i></span>
        @endif

        <span class="apt-page-summary">Page {{ $tasks->currentPage() }} of {{ $tasks->lastPage() }}</span>
    </div>
@endif

@else
    <div class="apt-empty">
        <div class="apt-empty-icon"><i class="fa-regular fa-paper-plane"></i></div>
        <div class="apt-empty-title">{{ request('filter') ? 'No matching tasks' : 'All clear!' }}</div>
        <div class="apt-empty-text">{{ request('filter') ? 'No tasks match this filter. Try adjusting your criteria.' : 'No approved tasks awaiting publishing yet.' }}</div>
    </div>
@endif

@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('publishingFilterForm');
    const searchInput = document.getElementById('publishingSearchInput');

    if (!form || !searchInput) return;

    let debounceTimer = null;

    const submitSearch = () => {
        form.submit();
    };

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(submitSearch, 300);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            clearTimeout(debounceTimer);
            submitSearch();
        }
    });
})();
</script>
@endpush
