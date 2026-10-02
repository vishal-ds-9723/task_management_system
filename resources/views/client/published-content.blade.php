@extends('layouts.app')

@push('styles')
<style>
/* ── Published Content Premium ────────────────────────── */
.pc-wrapper { animation: fadeIn 0.6s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.pc-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    padding: 24px 32px;
    background: var(--card);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}

.pc-header::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 4px; height: 100%;
    background: var(--primary-gradient);
}

.pc-title-box h1 {
    font-size: 24px;
    font-weight: 900;
    color: var(--text);
    margin: 0;
    letter-spacing: -0.5px;
}

.pc-title-box p {
    font-size: 13px;
    color: var(--text3);
    margin-top: 4px;
    font-weight: 500;
}

/* Stats Row */
.pc-stats {
    display: flex;
    gap: 12px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}

.pc-stat-chip {
    padding: 10px 18px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    color: var(--text2);
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
    box-shadow: var(--shadow-xs);
}

.pc-stat-chip:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.pc-stat-chip strong {
    color: var(--text);
    font-size: 15px;
}

/* Filter Bar */
.pc-filter-bar {
    display: grid;
    grid-template-columns: 1fr auto auto auto auto;
    gap: 12px;
    padding: 16px;
    background: var(--card2);
    border: 1px solid var(--border);
    border-radius: 18px;
    margin-bottom: 32px;
    align-items: center;
}

.pc-input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.pc-input-group i {
    position: absolute;
    left: 14px;
    color: var(--text3);
    font-size: 14px;
}

.pc-filter-bar input, .pc-filter-bar select {
    height: 44px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 0 16px;
    font-size: 13.5px;
    color: var(--text);
    font-weight: 600;
    outline: none;
    transition: all 0.2s;
}

.pc-filter-bar input { padding-left: 40px; width: 100%; }

.pc-filter-bar input:focus, .pc-filter-bar select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
}

.pc-btn-filter {
    height: 44px;
    padding: 0 24px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.2s;
}

.pc-btn-filter:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(var(--primary-rgb), 0.25);
}

/* Grid & Cards */
.pc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
}

.pc-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 24px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    text-decoration: none;
    display: flex;
    flex-direction: column;
}

.pc-card:hover {
    transform: translateY(-8px);
    border-color: var(--primary);
    box-shadow: var(--shadow-lg);
}

.pc-card-media {
    width: 100%;
    height: 220px;
    background: var(--card2);
    position: relative;
    overflow: hidden;
}

.pc-card-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.pc-card:hover .pc-card-media img {
    transform: scale(1.1);
}

.pc-badge-type {
    position: absolute;
    top: 16px;
    left: 16px;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(8px);
    color: #fff;
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    z-index: 2;
}

.pc-card-body { padding: 20px; flex: 1; }

.pc-card-title {
    font-size: 16px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 8px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pc-card-desc {
    font-size: 13px;
    color: var(--text3);
    line-height: 1.6;
    margin-bottom: 16px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pc-plat-row {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.pc-plat-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: var(--card2);
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    color: var(--text2);
}

.pc-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: var(--card2);
    border-top: 1px solid var(--border);
}

.pc-card-date {
    font-size: 11.5px;
    font-weight: 700;
    color: var(--text3);
    display: flex;
    align-items: center;
    gap: 6px;
}

@media(max-width: 900px) {
    .pc-filter-bar { grid-template-columns: 1fr; }
    .pc-header { flex-direction: column; align-items: flex-start; gap: 20px; }
}

/* Empty */
.pc-empty{text-align:center;padding:80px 20px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius)}
.pc-empty-icon{width:72px;height:72px;border-radius:20px;background:var(--card2);display:flex;align-items:center;justify-content:center;font-size:28px;color:var(--text3);margin:0 auto 16px}
.pc-empty-title{font-size:15px;font-weight:700;color:var(--text);margin-bottom:6px}
.pc-empty-text{font-size:12.5px;color:var(--text3);max-width:320px;margin:0 auto}

/* Pagination */
.pc-pagination{display:flex;justify-content:center;margin-top:24px}
.pc-pagination .pagination{display:flex;gap:4px;list-style:none;padding:0}
.pc-pagination .page-link{padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;color:var(--text2);background:var(--card);border:1px solid var(--border);text-decoration:none;transition:all .2s}
.pc-pagination .page-link:hover{border-color:var(--primary);color:var(--primary)}
.pc-pagination .page-item.active .page-link{background:var(--primary);color:#fff;border-color:var(--primary)}

/* Detail Modal */
.pc-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:none;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)}
.pc-modal-overlay.active{display:flex}
.pc-modal{background:var(--card);border-radius:var(--radius);max-width:640px;width:100%;max-height:85vh;overflow-y:auto;padding:28px;position:relative;box-shadow:0 25px 50px rgba(0,0,0,.15)}
.pc-modal-close{position:absolute;top:16px;right:16px;width:32px;height:32px;border-radius:8px;border:none;background:var(--card2);color:var(--text3);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;font-size:14px}
.pc-modal-close:hover{background:var(--danger-dim,#fef2f2);color:var(--danger,#ef4444)}
.pc-modal-title{font-size:18px;font-weight:800;color:var(--text);margin-bottom:16px;padding-right:40px}
.pc-modal-media{width:100%;border-radius:10px;overflow:hidden;margin-bottom:16px;max-height:300px}
.pc-modal-media img{width:100%;object-fit:cover}
.pc-modal-caption{font-size:13px;color:var(--text2);line-height:1.6;margin-bottom:12px;white-space:pre-wrap}
.pc-modal-meta{display:flex;flex-direction:column;gap:8px}
.pc-modal-meta-row{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text3)}
.pc-modal-meta-row i{width:16px;text-align:center;color:var(--primary)}
.pc-modal-meta-row strong{color:var(--text);font-weight:600}
.pc-modal-hashtags{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}

@media(max-width:900px){.pc-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:500px){.pc-grid{grid-template-columns:1fr}.pc-filter-bar{flex-direction:column;align-items:stretch}.pc-filter-bar input[type="text"]{width:100%}.pc-filter-sep{display:none}}
</style>
@endpush

@section('content')

<div class="pc-wrapper">
    {{-- Header --}}
    <div class="pc-header">
        <div class="pc-title-box">
            <h1>Published Content</h1>
            <p>Archive of all your completed and live posts</p>
        </div>
        <div class="pc-header-actions">
            <a href="{{ route('client.dashboard') }}" class="pc-btn" style="background:var(--card2); color:var(--text2); border:1px solid var(--border); padding:10px 20px; border-radius:12px; font-weight:700; text-decoration:none;">
                <i class="fa-solid fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="pc-stats">
        <div class="pc-stat-chip">
            <i class="fa-solid fa-circle-check" style="color:#059669"></i>
            <span><strong>{{ $totalPublished }}</strong> Total</span>
        </div>
        @foreach($platformStats as $platform => $count)
            <div class="pc-stat-chip">
                <i class="fa-brands fa-{{ $platform === 'twitter' ? 'x-twitter' : $platform }}" style="color:var(--primary)"></i>
                <span><strong>{{ $count }}</strong> {{ ucfirst($platform) }}</span>
            </div>
        @endforeach
    </div>

    {{-- Filter Bar --}}
    <form class="pc-filter-bar" method="GET">
        <div class="pc-input-group">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or caption...">
        </div>
        
        <select name="type">
            <option value="">All Types</option>
            @foreach(['post','story','reel','video','carousel'] as $type)
                <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
            @endforeach
        </select>

        <select name="platform">
            <option value="">All Platforms</option>
            @foreach(['instagram','facebook','linkedin','twitter','tiktok','youtube'] as $p)
                <option value="{{ $p }}" {{ request('platform') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
            @endforeach
        </select>

        <select name="month">
            <option value="">All Months</option>
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
            @endfor
        </select>

        <div style="display:flex; gap:10px;">
            <button type="submit" class="pc-btn-filter">Filter Results</button>
            @if(request()->hasAny(['search','type','platform','month']))
                <a href="{{ route('client.published-content') }}" class="pc-btn" style="background:var(--card2); color:var(--text3); border-radius:12px; display:flex; align-items:center; text-decoration:none; padding:0 16px;">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </div>
    </form>

{{-- Content Grid --}}
<div id="pcContentGrid">
    @include('client.published-content-list', ['tasks' => $tasks])
</div>
@endsection

