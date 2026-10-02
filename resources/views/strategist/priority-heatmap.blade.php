@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@section('content')
<div class="ph-page">
    {{-- Header --}}
    <div class="ph-header">
        <div class="ph-header-left">
            <a href="{{ route('strategist.dashboard') }}" class="ph-back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <div>
                <h1 class="ph-page-title"><i class="fa-solid fa-fire" style="color:#F97316"></i> Priority Heatmap</h1>
                <p class="ph-page-sub">All active tasks grouped by priority level</p>
            </div>
        </div>
        <div class="ph-header-total">
            @php $prioTotal = max(1, $urgentTasks + $highTasks + $normalTasks); @endphp
            <span class="ph-total-num">{{ $prioTotal }}</span>
            <span class="ph-total-label">Total Active</span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="ph-summary">
        <div class="ph-summary-card ph-urgent">
            <div class="ph-card-top">
                <div class="ph-summary-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                <div class="ph-summary-pct">{{ round(($urgentTasks/$prioTotal)*100) }}%</div>
            </div>
            <div class="ph-card-bottom">
                <div class="ph-summary-val">{{ $urgentTasks }}</div>
                <div class="ph-summary-label">Urgent</div>
            </div>
            <div class="ph-mini-bar"><div class="ph-mini-fill" style="width:{{ round(($urgentTasks/$prioTotal)*100) }}%;background:var(--red)"></div></div>
        </div>
        <div class="ph-summary-card ph-high">
            <div class="ph-card-top">
                <div class="ph-summary-icon"><i class="fa-solid fa-arrow-up"></i></div>
                <div class="ph-summary-pct">{{ round(($highTasks/$prioTotal)*100) }}%</div>
            </div>
            <div class="ph-card-bottom">
                <div class="ph-summary-val">{{ $highTasks }}</div>
                <div class="ph-summary-label">High</div>
            </div>
            <div class="ph-mini-bar"><div class="ph-mini-fill" style="width:{{ round(($highTasks/$prioTotal)*100) }}%;background:#F97316"></div></div>
        </div>
        <div class="ph-summary-card ph-normal">
            <div class="ph-card-top">
                <div class="ph-summary-icon"><i class="fa-solid fa-minus"></i></div>
                <div class="ph-summary-pct">{{ round(($normalTasks/$prioTotal)*100) }}%</div>
            </div>
            <div class="ph-card-bottom">
                <div class="ph-summary-val">{{ $normalTasks }}</div>
                <div class="ph-summary-label">Normal</div>
            </div>
            <div class="ph-mini-bar"><div class="ph-mini-fill" style="width:{{ round(($normalTasks/$prioTotal)*100) }}%;background:var(--teal)"></div></div>
        </div>
    </div>

    {{-- Stacked Bar --}}
    <div class="ph-stacked-bar">
        @if($urgentTasks > 0)<div class="ph-bar-seg" style="flex:{{ $urgentTasks }};background:var(--red)"></div>@endif
        @if($highTasks > 0)<div class="ph-bar-seg" style="flex:{{ $highTasks }};background:#F97316"></div>@endif
        @if($normalTasks > 0)<div class="ph-bar-seg" style="flex:{{ $normalTasks }};background:var(--teal)"></div>@endif
    </div>

    {{-- Task Lists Grid --}}
    <div class="ph-grid">
        {{-- Urgent --}}
        <div class="ph-box ph-box-urgent">
            <div class="ph-box-header">
                <div class="ph-box-dot" style="background:var(--red)"></div>
                <span class="ph-box-title">Urgent</span>
                <span class="ph-count-badge ph-badge-urgent">{{ $urgentTasksList->count() }}</span>
            </div>
            <div class="ph-box-scroll">
                @forelse($urgentTasksList as $task)
                    <a href="{{ route('strategist.tasks.show', $task->id) }}" class="ph-task-card">
                        <div class="ph-task-top">
                            <span class="prio-badge prio-badge-urgent">URGENT</span>
                            <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                        </div>
                        <div class="ph-task-title">{{ $task->title }}</div>
                        <div class="ph-task-details">
                            <x-client-branding :client="$task->client" size="22px" radius="5px" :showName="true" />
                            <span><i class="fas fa-user" style="font-size:10px;opacity:0.5"></i> {{ $task->assignee?->name ?? 'Unassigned' }}</span>
                            @if($task->creator)
                                <span style="font-size:11px;font-weight:600;color:var(--primary);background:rgba(99,102,241,0.08);padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px">
                                    <i class="fa-solid fa-user-pen" style="font-size:9px"></i> {{ $task->creator->name }}
                                </span>
                            @endif
                        </div>
                        @if($task->deadline)
                            <div class="ph-task-deadline {{ \Carbon\Carbon::parse($task->deadline)->isPast() ? 'overdue' : '' }}">
                                <i class="fa-regular fa-calendar"></i>
                                {{ \Carbon\Carbon::parse($task->deadline)->format('M d, Y') }}
                                @if(\Carbon\Carbon::parse($task->deadline)->isPast()) — Overdue @endif
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="ph-empty"><i class="fas fa-check-circle" style="color:var(--teal)"></i> No urgent tasks</div>
                @endforelse
            </div>
        </div>

        {{-- High --}}
        <div class="ph-box ph-box-high">
            <div class="ph-box-header">
                <div class="ph-box-dot" style="background:#F97316"></div>
                <span class="ph-box-title">High</span>
                <span class="ph-count-badge ph-badge-high">{{ $highTasksList->count() }}</span>
            </div>
            <div class="ph-box-scroll">
                @forelse($highTasksList as $task)
                    <a href="{{ route('strategist.tasks.show', $task->id) }}" class="ph-task-card">
                        <div class="ph-task-top">
                            <span class="prio-badge prio-badge-high">HIGH</span>
                            <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                        </div>
                        <div class="ph-task-title">{{ $task->title }}</div>
                        <div class="ph-task-details">
                            <span style="display:inline-flex;align-items:center;gap:4px">
                                @if($task->client->logo)
                                    <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover">
                                @else
                                    {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!}
                                @endif
                                {{ $task->client->name }}
                            </span>
                            <span><i class="fas fa-user" style="font-size:10px;opacity:0.5"></i> {{ $task->assignee?->name ?? 'Unassigned' }}</span>
                            @if($task->creator)
                                <span style="font-size:11px;font-weight:600;color:var(--primary);background:rgba(99,102,241,0.08);padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px">
                                    <i class="fa-solid fa-user-pen" style="font-size:9px"></i> {{ $task->creator->name }}
                                </span>
                            @endif
                        </div>
                        @if($task->deadline)
                            <div class="ph-task-deadline {{ \Carbon\Carbon::parse($task->deadline)->isPast() ? 'overdue' : '' }}">
                                <i class="fa-regular fa-calendar"></i>
                                {{ \Carbon\Carbon::parse($task->deadline)->format('M d, Y') }}
                                @if(\Carbon\Carbon::parse($task->deadline)->isPast()) — Overdue @endif
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="ph-empty"><i class="fas fa-check-circle" style="color:var(--teal)"></i> No high priority tasks</div>
                @endforelse
            </div>
        </div>

        {{-- Normal --}}
        <div class="ph-box ph-box-normal">
            <div class="ph-box-header">
                <div class="ph-box-dot" style="background:var(--teal)"></div>
                <span class="ph-box-title">Normal</span>
                <span class="ph-count-badge ph-badge-normal">{{ $normalTasksList->count() }}</span>
            </div>
            <div class="ph-box-scroll">
                @forelse($normalTasksList as $task)
                    <a href="{{ route('strategist.tasks.show', $task->id) }}" class="ph-task-card">
                        <div class="ph-task-top">
                            <span class="prio-badge prio-badge-normal">NORMAL</span>
                            <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                        </div>
                        <div class="ph-task-title">{{ $task->title }}</div>
                        <div class="ph-task-details">
                            <span style="display:inline-flex;align-items:center;gap:4px">
                                @if($task->client->logo)
                                    <img src="{{ asset('storage/' . $task->client->logo) }}" alt="" style="width:14px;height:14px;border-radius:3px;object-fit:cover">
                                @else
                                    {!! $task->client->emoji ?? '<i class="fas fa-building" style="color:var(--text3)"></i>' !!}
                                @endif
                                {{ $task->client->name }}
                            </span>
                            <span><i class="fas fa-user" style="font-size:10px;opacity:0.5"></i> {{ $task->assignee?->name ?? 'Unassigned' }}</span>
                            @if($task->creator)
                                <span style="font-size:11px;font-weight:600;color:var(--primary);background:rgba(99,102,241,0.08);padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px">
                                    <i class="fa-solid fa-user-pen" style="font-size:9px"></i> {{ $task->creator->name }}
                                </span>
                            @endif
                        </div>
                        @if($task->deadline)
                            <div class="ph-task-deadline">
                                <i class="fa-regular fa-calendar"></i>
                                {{ \Carbon\Carbon::parse($task->deadline)->format('M d, Y') }}
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="ph-empty">No normal priority tasks</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== Page Layout ===== */
    .ph-page { max-width:1200px; margin:0 auto; padding:24px 20px 40px; }

    /* ===== Header ===== */
    .ph-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; gap:16px; flex-wrap:wrap; }
    .ph-header-left { display:flex; align-items:center; gap:14px; }
    .ph-back-btn {
        width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center;
        background:var(--card); border:1px solid var(--border); color:var(--text2); font-size:14px;
        text-decoration:none; transition:all 0.15s;
    }
    .ph-back-btn:hover { background:var(--card2); color:var(--text); }
    .ph-page-title { font-size:22px; font-weight:800; color:var(--text); margin:0; display:flex; align-items:center; gap:8px; }
    .ph-page-sub { font-size:12px; color:var(--text3); margin:2px 0 0; }
    .ph-header-total { text-align:right; }
    .ph-total-num { font-size:32px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; display:block; line-height:1; }
    .ph-total-label { font-size:10px; font-weight:600; color:var(--text3); text-transform:uppercase; letter-spacing:0.5px; }

    /* ===== Summary Cards ===== */
    .ph-summary { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:16px; }
    .ph-summary-card {
        padding:16px 18px; border-radius:14px; background:var(--card); border:1px solid var(--border);
        display:flex; flex-direction:column; gap:10px; transition:transform 0.15s, box-shadow 0.15s;
    }
    .ph-summary-card:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,0.06); }
    .ph-card-top { display:flex; align-items:center; justify-content:space-between; }
    .ph-summary-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:15px; }
    .ph-urgent .ph-summary-icon { background:rgba(239,68,68,0.1); color:var(--red); }
    .ph-high .ph-summary-icon { background:rgba(249,115,22,0.1); color:#F97316; }
    .ph-normal .ph-summary-icon { background:rgba(16,185,129,0.1); color:var(--teal); }
    .ph-summary-pct { font-size:13px; font-weight:700; color:var(--text3); font-family:'Plus Jakarta Sans',sans-serif; }
    .ph-card-bottom { display:flex; align-items:baseline; gap:6px; }
    .ph-summary-val { font-size:30px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; line-height:1; }
    .ph-summary-label { font-size:12px; font-weight:600; color:var(--text3); text-transform:uppercase; letter-spacing:0.3px; }
    .ph-mini-bar { height:4px; border-radius:99px; background:var(--card2); overflow:hidden; }
    .ph-mini-fill { height:100%; border-radius:99px; transition:width 0.6s ease; }

    /* ===== Stacked Bar ===== */
    .ph-stacked-bar { display:flex; height:8px; border-radius:99px; overflow:hidden; gap:2px; margin-bottom:20px; }
    .ph-bar-seg { border-radius:99px; min-width:4px; }

    /* ===== Task Grid ===== */
    .ph-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }

    /* ===== Task Box ===== */
    .ph-box {
        background:var(--card); border:1px solid var(--border); border-radius:14px;
        display:flex; flex-direction:column; overflow:hidden;
    }
    .ph-box-urgent { border-top:3px solid var(--red); }
    .ph-box-high { border-top:3px solid #F97316; }
    .ph-box-normal { border-top:3px solid var(--teal); }
    .ph-box-header {
        display:flex; align-items:center; gap:8px; padding:14px 16px;
        border-bottom:1px solid var(--border); position:sticky; top:0; background:var(--card); z-index:1;
    }
    .ph-box-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
    .ph-box-title { font-size:14px; font-weight:700; color:var(--text); flex:1; }
    .ph-box-scroll {
        flex:1; overflow-y:auto; max-height:520px; padding:8px 12px 12px;
        scrollbar-width:thin; scrollbar-color:var(--border) transparent;
    }
    .ph-box-scroll::-webkit-scrollbar { width:5px; }
    .ph-box-scroll::-webkit-scrollbar-track { background:transparent; }
    .ph-box-scroll::-webkit-scrollbar-thumb { background:var(--border); border-radius:99px; }
    .ph-box-scroll::-webkit-scrollbar-thumb:hover { background:var(--text3); }

    /* ===== Task Card ===== */
    .ph-task-card {
        display:block; text-decoration:none; color:inherit;
        padding:12px; border-radius:10px; background:var(--card2); margin-bottom:8px;
        border:1px solid transparent; transition:all 0.15s;
    }
    .ph-task-card:last-child { margin-bottom:0; }
    .ph-task-card:hover { border-color:var(--border); transform:translateY(-1px); box-shadow:0 2px 8px rgba(0,0,0,0.04); }
    .ph-task-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; gap:6px; }
    .ph-task-title { font-size:13px; font-weight:600; color:var(--text); line-height:1.4; margin-bottom:6px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .ph-task-details { display:flex; flex-wrap:wrap; gap:4px 10px; font-size:11px; color:var(--text3); }
    .ph-task-deadline { font-size:10px; color:var(--text3); margin-top:6px; display:flex; align-items:center; gap:4px; }
    .ph-task-deadline.overdue { color:var(--red); font-weight:600; }

    /* ===== Badges ===== */
    .ph-count-badge { font-size:11px; font-weight:800; padding:2px 9px; border-radius:99px; }
    .ph-badge-urgent { background:rgba(239,68,68,0.1); color:var(--red); }
    .ph-badge-high { background:rgba(249,115,22,0.1); color:#F97316; }
    .ph-badge-normal { background:rgba(16,185,129,0.1); color:var(--teal); }
    .prio-badge { font-size:9px; font-weight:800; padding:3px 7px; border-radius:5px; letter-spacing:0.4px; white-space:nowrap; }
    .prio-badge-urgent { background:rgba(239,68,68,0.1); color:var(--red); border:1px solid rgba(239,68,68,0.15); }
    .prio-badge-high { background:rgba(249,115,22,0.08); color:#F97316; border:1px solid rgba(249,115,22,0.15); }
    .prio-badge-normal { background:rgba(16,185,129,0.08); color:var(--teal); border:1px solid rgba(16,185,129,0.15); }

    /* ===== Empty State ===== */
    .ph-empty { padding:32px 16px; text-align:center; font-size:13px; color:var(--text3); }

    /* ===== Responsive — Tablet ===== */
    @media (max-width:1024px) {
        .ph-grid { grid-template-columns:1fr 1fr; }
        .ph-grid .ph-box:last-child { grid-column:1 / -1; }
        .ph-box-scroll { max-height:400px; }
    }

    /* ===== Responsive — Mobile ===== */
    @media (max-width:640px) {
        .ph-page { padding:16px 12px 32px; }
        .ph-header { flex-direction:column; align-items:flex-start; gap:12px; }
        .ph-header-total { text-align:left; }
        .ph-total-num { font-size:26px; }
        .ph-page-title { font-size:18px; }
        .ph-summary { grid-template-columns:1fr; gap:10px; margin-bottom:12px; }
        .ph-summary-card { flex-direction:row; align-items:center; padding:14px; gap:12px; }
        .ph-card-top { flex-direction:row; gap:8px; flex:0 0 auto; }
        .ph-card-bottom { flex:1; }
        .ph-summary-val { font-size:22px; }
        .ph-mini-bar { display:none; }
        .ph-grid { grid-template-columns:1fr; gap:14px; }
        .ph-grid .ph-box:last-child { grid-column:auto; }
        .ph-box-scroll { max-height:350px; }
        .ph-task-card { padding:10px; }
        .ph-task-top { flex-wrap:wrap; }
        .ph-task-details { font-size:10px; }
    }

    /* ===== Responsive — Very small ===== */
    @media (max-width:380px) {
        .ph-page { padding:12px 8px 24px; }
        .ph-page-title { font-size:16px; }
        .ph-summary-card { padding:10px; }
        .ph-summary-val { font-size:20px; }
        .ph-box-header { padding:10px 12px; }
        .ph-task-card { padding:8px; }
        .ph-task-title { font-size:12px; }
    }
</style>
@endsection
