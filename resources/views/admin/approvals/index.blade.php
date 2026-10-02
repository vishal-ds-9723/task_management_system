@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <div class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span class="breadcrumb-sep">›</span>
            <span>Task Approvals</span>
        </div>
        <div class="page-title"><i class="fas fa-clipboard-check" style="margin-right:8px;color:var(--primary)"></i> Task Approvals</div>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn-sec">← Dashboard</a>
</div>

{{-- MAIN CONTENT --}}
<div class="layout-main">
    {{-- FILTERS --}}
    <div class="card" style="margin-bottom:20px">
        <form method="GET" action="{{ route('admin.approvals') }}" id="approvalsFilterForm" style="display:flex;gap:12px;align-items:flex-end">
            <div style="flex:1">
                <label style="font-size:12px;font-weight:600;color:var(--text);display:block;margin-bottom:6px">Search Tasks</label>
                <input type="text" name="search" id="approvalsSearchInput" value="{{ request('search') }}" placeholder="Search by title or client…" autocomplete="off"
                       style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--card2);font-size:12.5px;color:var(--text)">
            </div>
            <button type="submit" class="btn-primary" style="padding:10px 20px;font-size:12px">
                <i class="fas fa-search"></i> Search
            </button>
            <a href="{{ route('admin.approvals') }}" class="btn-sec" style="padding:10px 20px;font-size:12px">Clear</a>
        </form>
    </div>

    <div id="approvalsResultsContainer">
        {{-- NO TASKS MESSAGE --}}
        @if($tasks->isEmpty())
            <div class="card" style="padding:40px;text-align:center">
                <div style="font-size:40px;margin-bottom:12px"><i class="fas fa-sparkles" style="color:var(--primary)"></i></div>
                <div style="font-size:14px;font-weight:600;color:var(--text);margin-bottom:6px">No tasks pending approval</div>
                <div style="font-size:12px;color:var(--text3)">All submitted tasks have been reviewed!</div>
            </div>
        @else
            {{-- TASKS LIST --}}
            <div style="display:grid;gap:12px">
                @foreach($tasks as $task)
                    <a href="{{ route('admin.approvals.show', $task) }}" class="card" style="display:flex;gap:16px;padding:16px;transition:all 0.2s;text-decoration:none;color:inherit;cursor:pointer;border:2px solid var(--border)">

                        {{-- LEFT: Status Badge + Title --}}
                        <div style="flex:1">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                                <span class="tag" style="background:rgba(168,85,247,0.15);color:#a855f7;font-size:10px;padding:4px 8px;border-radius:4px">
                                    <i class="fas fa-hourglass-half" style="font-size:9px"></i> Pending Admin Review
                                </span>
                                {{-- Icon for media presence --}}
                                @if($task->media()->count() > 0)
                                    <span class="tag" style="background:rgba(34,197,94,0.15);color:#22c55e;font-size:10px;padding:4px 8px;border-radius:4px">
                                        <i class="fas fa-image" style="font-size:9px"></i> Media Uploaded
                                    </span>
                                @else
                                    <span class="tag" style="background:rgba(239,68,68,0.15);color:#ef4444;font-size:10px;padding:4px 8px;border-radius:4px">
                                        <i class="fas fa-exclamation-triangle" style="font-size:9px"></i> No Media
                                    </span>
                                @endif
                            </div>

                            <div style="font-weight:600;font-size:14px;color:var(--text);margin-bottom:4px;line-height:1.4">
                                {{ Str::limit($task->title, 60) }}
                            </div>

                            <div style="display:flex;gap:16px;font-size:12px;color:var(--text3)">
                                <span>
                                    <strong style="color:var(--text)">Designer:</strong> {{ $task->assignee?->name ?? 'Unassigned' }}
                                </span>
                                <span>
                                    <strong style="color:var(--text)">Client:</strong> {{ $task->client?->name ?? 'N/A' }}
                                </span>
                                <span>
                                    <strong style="color:var(--text)">Submitted:</strong> {{ $task->submitted_at?->format('d M, h:i A') ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        {{-- RIGHT: Action Button --}}
                        <div style="display:flex;align-items:center;gap:8px">
                            <button type="button" class="btn-primary" style="padding:10px 16px;font-size:12px;white-space:nowrap;cursor:pointer" onclick="event.preventDefault(); window.location.href='{{ route('admin.approvals.show', $task) }}'">
                                <i class="fas fa-eye"></i> Review
                            </button>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- PAGINATION --}}
            @if($tasks->hasPages())
                <div style="margin-top:20px;display:flex;justify-content:center;gap:8px">
                    {{ $tasks->links('vendor.pagination.custom') }}
                </div>
            @endif
        @endif
    </div>
</div>

<style>
.card:hover {
    border-color: var(--primary) !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
</style>

@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('approvalsFilterForm');
    const searchInput = document.getElementById('approvalsSearchInput');
    const resultsContainer = document.getElementById('approvalsResultsContainer');

    if (!form || !searchInput || !resultsContainer) return;

    let debounceTimer = null;
    let requestController = null;

    const buildSearchUrl = () => {
        const params = new URLSearchParams(new FormData(form));
        params.delete('page');
        return form.action + (params.toString() ? ('?' + params.toString()) : '');
    };

    const setLoadingState = (isLoading) => {
        resultsContainer.style.opacity = isLoading ? '0.65' : '1';
        resultsContainer.style.pointerEvents = isLoading ? 'none' : 'auto';
    };

    const renderFromResponse = (html, url) => {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const nextContainer = doc.getElementById('approvalsResultsContainer');
        if (!nextContainer) return;
        resultsContainer.innerHTML = nextContainer.innerHTML;
        window.history.replaceState({}, '', url);
    };

    const fetchAndRender = (url) => {
        if (requestController) requestController.abort();
        requestController = new AbortController();

        setLoadingState(true);

        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: requestController.signal,
        })
            .then((response) => response.text())
            .then((html) => renderFromResponse(html, url))
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    console.error('Approvals live search failed:', error);
                }
            })
            .finally(() => setLoadingState(false));
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        clearTimeout(debounceTimer);
        fetchAndRender(buildSearchUrl());
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchAndRender(buildSearchUrl());
        }, 300);
    });

    resultsContainer.addEventListener('click', (event) => {
        const pageLink = event.target.closest('a[href]');
        if (!pageLink) return;

        const pageUrl = new URL(pageLink.href, window.location.origin);
        const formUrl = new URL(form.action, window.location.origin);

        if (pageUrl.pathname !== formUrl.pathname || !pageUrl.searchParams.has('page')) {
            return;
        }

        event.preventDefault();
        fetchAndRender(pageUrl.toString());
    });
})();
</script>
@endpush
