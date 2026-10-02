@extends('layouts.app')

@push('styles')
<style>
/* Admin Festival Selections — Table Layout */
.afsel-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px; }
.afsel-title  { font-size:22px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:10px; }
.afsel-title i { color:var(--primary); }
.afsel-sub    { font-size:13px;color:var(--text3);margin-top:2px; }

.afsel-stats { display:flex;gap:12px;flex-wrap:wrap;margin-bottom:22px; }
.afsel-stat  { flex:1;min-width:120px;background:var(--card);border:1px solid var(--border);border-radius:14px;padding:16px 18px;display:flex;align-items:center;gap:12px; }
.afsel-stat-icon { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0; }
.afsel-stat-val  { font-size:22px;font-weight:800;color:var(--text);line-height:1; }
.afsel-stat-lbl  { font-size:11px;color:var(--text3);font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-top:2px; }

/* Content type demand */
.afsel-demand { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:22px; }
.afsel-demand-card { flex:1;min-width:100px;background:var(--card);border:1px solid var(--border);border-radius:12px;padding:14px;text-align:center; }
.afsel-demand-val  { font-size:22px;font-weight:800;color:var(--primary); }
.afsel-demand-icon { font-size:18px;margin-bottom:4px;opacity:.7;color:var(--primary); }
.afsel-demand-lbl  { font-size:11px;color:var(--text3);font-weight:600;text-transform:uppercase;margin-top:2px; }

/* Toolbar: search + filters */
.afsel-toolbar { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:center; }
.afsel-search-wrap { position:relative;flex:1;min-width:200px;max-width:360px; }
.afsel-search-wrap i { position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text3);font-size:13px; }
.afsel-search { width:100%;padding:9px 12px 9px 34px;border:1px solid var(--border);border-radius:8px;font-size:13px;color:var(--text);background:var(--card);font-family:inherit; }
.afsel-search::placeholder { color:var(--text3); }
.afsel-select  { padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;color:var(--text);background:var(--card);font-family:inherit;min-width:130px; }
.afsel-toolbar-right { margin-left:auto;display:flex;gap:8px;align-items:center; }
.afsel-per-page { display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text3); }

/* Table */
.afsel-table-wrap { background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden; }
.afsel-table { width:100%;border-collapse:collapse; }
.afsel-table th { padding:12px 16px;font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.4px;text-align:left;background:var(--card2, rgba(0,0,0,.02));border-bottom:1px solid var(--border);white-space:nowrap;user-select:none; }
.afsel-table th a { color:var(--text3);text-decoration:none;display:inline-flex;align-items:center;gap:4px; }
.afsel-table th a:hover { color:var(--text); }
.afsel-table th .sort-icon { font-size:10px;opacity:.5; }
.afsel-table th .sort-icon.active { opacity:1;color:var(--primary); }
.afsel-table td { padding:12px 16px;font-size:13px;color:var(--text);border-bottom:1px solid var(--border);vertical-align:middle; }
.afsel-table tbody tr { cursor:pointer;transition:background .15s; }
.afsel-table tbody tr:hover { background:var(--card2, rgba(0,0,0,.02)); }
.afsel-table tbody tr:last-child td { border-bottom:0; }
.afsel-table tbody tr.expanded { background:var(--card2, rgba(0,0,0,.02)); }
.afsel-table tbody tr.expanded td { border-bottom:1px solid var(--border); }

/* Client cell */
.afsel-client-cell { display:flex;align-items:center;gap:10px; }
.afsel-client-avatar { width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0; }
.afsel-client-name { font-weight:700;font-size:13px; }
.afsel-client-cat  { font-size:11px;color:var(--text3); }

/* Badges in table */
.afsel-badge { display:inline-flex;align-items:center;gap:3px;padding:3px 8px;border-radius:5px;font-size:10px;font-weight:700; }
.afsel-badge.type { background:var(--primary-dim);color:var(--primary); }
.afsel-badge.insta { background:rgba(225,48,108,.1);color:#e1306c; }
.afsel-badge.fb { background:rgba(24,119,242,.1);color:#1877f2; }
.afsel-type-summary { display:flex;flex-wrap:wrap;gap:4px; }
.afsel-count-badge { display:inline-flex;align-items:center;justify-content:center;background:var(--primary-dim);color:var(--primary);min-width:24px;height:24px;border-radius:6px;font-size:12px;font-weight:800;padding:0 6px; }

/* Expanded detail row */
.afsel-detail-row td { padding:0 !important;border-bottom:1px solid var(--border); }
.afsel-detail-row:last-child td { border-bottom:0; }
.afsel-detail-inner { padding:8px 16px 16px 58px; }
.afsel-detail-grid { display:flex;flex-direction:column;gap:6px; }
.afsel-detail-item { display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--bg, #f9fafb);border-radius:8px;flex-wrap:wrap; }
.afsel-detail-fest { font-weight:700;font-size:13px;color:var(--text);display:flex;align-items:center;gap:6px;min-width:180px; }
.afsel-detail-fest-date { font-size:11px;color:var(--text3);font-weight:400; }
.afsel-detail-badges { display:flex;flex-wrap:wrap;gap:4px; }
.afsel-detail-notes { font-size:11px;color:var(--text3);font-style:italic;margin-left:auto; }
.afsel-detail-user  { font-size:10px;color:var(--text3); }

/* Expand icon */
.afsel-expand-icon { transition:transform .2s;font-size:11px;color:var(--text3); }
.afsel-expand-icon.open { transform:rotate(90deg); }

/* Pagination */
.afsel-pagination { display:flex;align-items:center;justify-content:space-between;padding:14px 16px;flex-wrap:wrap;gap:10px; }
.afsel-pagination-info { font-size:12px;color:var(--text3); }
.afsel-pagination-links { display:flex;gap:4px; }
.afsel-pagination-links a,
.afsel-pagination-links span { display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 8px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-weight:600;color:var(--text);text-decoration:none;background:var(--card); }
.afsel-pagination-links a:hover { background:var(--primary-dim);color:var(--primary);border-color:var(--primary); }
.afsel-pagination-links span.current { background:var(--primary);color:#fff;border-color:var(--primary); }
.afsel-pagination-links span.dots { border:0;background:none;color:var(--text3); }

.afsel-empty { text-align:center;padding:60px 20px;background:var(--card);border:1px solid var(--border);border-radius:16px; }
.afsel-empty i { font-size:40px;opacity:.2;display:block;margin-bottom:12px;color:var(--text3); }

@media(max-width:700px){
    .afsel-stats{flex-direction:column;}
    .afsel-demand{flex-direction:column;}
    .afsel-toolbar{flex-direction:column;}
    .afsel-search-wrap{max-width:100%;}
    .afsel-toolbar-right{margin-left:0;width:100%;justify-content:space-between;}
    .afsel-table-wrap{overflow-x:auto;}
    .afsel-detail-inner{padding:8px 12px 12px 12px;}
}
</style>
@endpush

@section('content')
@php
    $typeIcon = fn($t) => match($t) { 'post'=>'fa-image','story'=>'fa-mobile-screen','reel'=>'fa-film','video'=>'fa-video','carousel'=>'fa-layer-group',default=>'fa-image' };
    $avatarColors = ['#4F6DF0','#7c3aed','#059669','#d97706','#e1306c','#0284c7','#be185d','#0891b2'];
    $sortUrl = fn($col) => request()->fullUrlWithQuery(['sort' => $col, 'dir' => ($sortBy === $col && $sortDir === 'asc') ? 'desc' : 'asc']);
@endphp

<div>
    <div class="afsel-header">
        <div>
            <div class="afsel-title"><i class="fa-solid fa-calendar-star"></i> Festival Selections</div>
            <div class="afsel-sub">{{ $monthLabel }} — {{ $totalSelections }} total selections from {{ $totalClients }} clients</div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-sec">← Dashboard</a>
    </div>

    <div class="afsel-stats">
        <div class="afsel-stat">
            <div class="afsel-stat-icon" style="background:var(--primary-dim);color:var(--primary)"><i class="fa-solid fa-calendar-star"></i></div>
            <div><div class="afsel-stat-val">{{ $festivals->count() }}</div><div class="afsel-stat-lbl">Festivals</div></div>
        </div>
        <div class="afsel-stat">
            <div class="afsel-stat-icon" style="background:rgba(16,185,129,.12);color:#059669"><i class="fa-solid fa-circle-check"></i></div>
            <div><div class="afsel-stat-val">{{ $totalSelections }}</div><div class="afsel-stat-lbl">Selections</div></div>
        </div>
        <div class="afsel-stat">
            <div class="afsel-stat-icon" style="background:rgba(245,158,11,.12);color:#d97706"><i class="fa-solid fa-users"></i></div>
            <div><div class="afsel-stat-val">{{ $totalClients }}</div><div class="afsel-stat-lbl">Clients Active</div></div>
        </div>
    </div>

    {{-- Content Type Demand --}}
    @if(!empty($typeCounts))
    <div style="margin-bottom:8px;font-size:12px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.4px">Content Type Demand</div>
    <div class="afsel-demand">
        @foreach([
            'post'=>['fa-image','Posts'],'story'=>['fa-mobile-screen','Stories'],
            'reel'=>['fa-film','Reels'],'carousel'=>['fa-layer-group','Carousels'],
            'video'=>['fa-video','Videos'],
        ] as $type => [$icon, $label])
        <div class="afsel-demand-card">
            <div class="afsel-demand-icon"><i class="fa-solid {{ $icon }}"></i></div>
            <div class="afsel-demand-val">{{ $typeCounts[$type] ?? 0 }}</div>
            <div class="afsel-demand-lbl">{{ $label }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Toolbar: Search + Filters --}}
    <form method="GET" class="afsel-toolbar" id="afselForm">
        <input type="hidden" name="sort" value="{{ $sortBy }}">
        <input type="hidden" name="dir" value="{{ $sortDir }}">

        <div class="afsel-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" class="afsel-search" placeholder="Search clients by name..." value="{{ $search }}" autocomplete="off">
        </div>

        <x-client-select :clients="$clients" name="client_id" value="{{ $clientId }}" placeholder="All Clients" class="afsel-select" onchange="this.form.submit()" />

        <div class="afsel-toolbar-right">
            <div class="afsel-per-page">
                <span>Show</span>
                <select name="per_page" class="afsel-select" style="min-width:70px" onchange="this.form.submit()">
                    @foreach([25, 50, 100] as $pp)
                        <option value="{{ $pp }}" {{ $perPage == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>
            @if($search || $clientId)
                <a href="{{ route('admin.festival-selections') }}" class="btn-sec" style="font-size:12px;padding:7px 12px">Reset</a>
            @endif
        </div>
    </form>

    @if($paginatedClients->isEmpty())
        <div class="afsel-empty">
            <i class="fa-solid fa-clipboard-list"></i>
            @if($search || $clientId)
                <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:4px">No results found</div>
                <div style="font-size:13px;color:var(--text3)">Try a different search term or filter.</div>
            @else
                <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:4px">No selections yet for {{ $monthLabel }}</div>
                <div style="font-size:13px;color:var(--text3)">Clients haven't selected any festivals yet.</div>
            @endif
        </div>
    @else
        <div class="afsel-table-wrap">
            <table class="afsel-table">
                <thead>
                    <tr>
                        <th style="width:30px"></th>
                        <th>
                            <a href="{{ $sortUrl('name') }}">
                                Client
                                <i class="fa-solid fa-sort sort-icon {{ $sortBy === 'name' ? 'active' : '' }}"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortUrl('count') }}">
                                Festivals
                                <i class="fa-solid fa-sort sort-icon {{ $sortBy === 'count' ? 'active' : '' }}"></i>
                            </a>
                        </th>
                        <th>Content Types</th>
                        <th>Platforms</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paginatedClients as $client)
                    @php
                        $sels = $client->festivalSelections;
                        $ci = $client->id % count($avatarColors);
                        $allTypes = $sels->flatMap(fn($s) => $s->content_types ?? [])->countBy();
                        $allPlatforms = $sels->flatMap(fn($s) => $s->platforms ?? [])->unique()->values();
                        $hasNotes = $sels->contains(fn($s) => !empty($s->notes));
                    @endphp
                    <tr onclick="toggleDetail({{ $client->id }})" id="row-{{ $client->id }}">
                        <td><i class="fa-solid fa-chevron-right afsel-expand-icon" id="icon-{{ $client->id }}"></i></td>
                        <td>
                            <div class="afsel-client-cell">
                                <div class="afsel-client-avatar" style="background:{{ $avatarColors[$ci] }}">{{ strtoupper(substr($client->name,0,1)) }}</div>
                                <div>
                                    <div class="afsel-client-name">{{ $client->name }}</div>
                                    @if($client->category)<div class="afsel-client-cat">{{ $client->category }}</div>@endif
                                </div>
                            </div>
                        </td>
                        <td><span class="afsel-count-badge">{{ $sels->count() }}</span></td>
                        <td>
                            <div class="afsel-type-summary">
                                @foreach($allTypes as $t => $cnt)
                                    <span class="afsel-badge type"><i class="fa-solid {{ $typeIcon($t) }}"></i> {{ $cnt }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @foreach($allPlatforms as $p)
                                <span class="afsel-badge {{ $p==='instagram'?'insta':'fb' }}"><i class="fa-brands fa-{{ $p }}"></i></span>
                            @endforeach
                        </td>
                        <td>
                            @if($hasNotes)<i class="fa-solid fa-note-sticky" style="color:var(--text3);font-size:12px" title="Has notes"></i>@endif
                        </td>
                    </tr>
                    <tr class="afsel-detail-row" id="detail-{{ $client->id }}" style="display:none">
                        <td colspan="6">
                            <div class="afsel-detail-inner">
                                <div class="afsel-detail-grid">
                                    @foreach($sels as $sel)
                                    <div class="afsel-detail-item">
                                        <div class="afsel-detail-fest">
                                            <span>{{ $sel->festival->emoji ?? '🎉' }}</span>
                                            {{ $sel->festival->name }}
                                            <span class="afsel-detail-fest-date">{{ $sel->festival->date->format('M d') }}</span>
                                        </div>
                                        <div class="afsel-detail-badges">
                                            @foreach($sel->content_types ?? [] as $t)
                                                <span class="afsel-badge type"><i class="fa-solid {{ $typeIcon($t) }}"></i> {{ ucfirst($t) }}</span>
                                            @endforeach
                                            @foreach($sel->platforms ?? [] as $p)
                                                <span class="afsel-badge {{ $p==='instagram'?'insta':'fb' }}"><i class="fa-brands fa-{{ $p }}"></i> {{ ucfirst($p) }}</span>
                                            @endforeach
                                        </div>
                                        @if($sel->notes)<div class="afsel-detail-notes">"{{ Str::limit($sel->notes, 60) }}"</div>@endif
                                        @if($sel->user)<div class="afsel-detail-user"><i class="fa-solid fa-user"></i> {{ $sel->user->name }}</div>@endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($paginatedClients->lastPage() > 1)
            <div class="afsel-pagination">
                <div class="afsel-pagination-info">
                    Showing {{ $paginatedClients->firstItem() }}–{{ $paginatedClients->lastItem() }} of {{ $paginatedClients->total() }} clients
                </div>
                <div class="afsel-pagination-links">
                    @if($paginatedClients->onFirstPage())
                        <span style="opacity:.4"><i class="fa-solid fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $paginatedClients->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i></a>
                    @endif

                    @foreach($paginatedClients->getUrlRange(max(1, $paginatedClients->currentPage()-2), min($paginatedClients->lastPage(), $paginatedClients->currentPage()+2)) as $page => $url)
                        @if($page == $paginatedClients->currentPage())
                            <span class="current">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($paginatedClients->currentPage() + 2 < $paginatedClients->lastPage())
                        <span class="dots">…</span>
                        <a href="{{ $paginatedClients->url($paginatedClients->lastPage()) }}">{{ $paginatedClients->lastPage() }}</a>
                    @endif

                    @if($paginatedClients->hasMorePages())
                        <a href="{{ $paginatedClients->nextPageUrl() }}"><i class="fa-solid fa-chevron-right"></i></a>
                    @else
                        <span style="opacity:.4"><i class="fa-solid fa-chevron-right"></i></span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleDetail(id) {
    const row = document.getElementById('detail-' + id);
    const icon = document.getElementById('icon-' + id);
    const parentRow = document.getElementById('row-' + id);
    if (row.style.display === 'none') {
        row.style.display = '';
        icon.classList.add('open');
        parentRow.classList.add('expanded');
    } else {
        row.style.display = 'none';
        icon.classList.remove('open');
        parentRow.classList.remove('expanded');
    }
}

// Debounced search
let searchTimer;
document.querySelector('.afsel-search').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        document.getElementById('afselForm').submit();
    }, 400);
});
</script>
@endpush
