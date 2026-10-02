@extends('layouts.app')

@push('styles')
<style>
.smc-wrap { max-width: 1200px; margin: 0 auto; }
.smc-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
.smc-title { margin: 0; font-size: 22px; font-weight: 800; color: var(--text); }
.smc-sub { margin-top: 4px; font-size: 12px; color: var(--text3); }
.smc-note { background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.22); color: #1D4ED8; padding: 8px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; }

.smc-filter { display: grid; grid-template-columns: 1fr auto auto; gap: 8px; background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 10px; margin-bottom: 14px; }
.smc-filter input { width: 100%; border: 1px solid var(--border); background: var(--card2); color: var(--text); border-radius: 8px; padding: 8px 10px; font-size: 12px; }
.smc-btn { border: 0; border-radius: 8px; font-size: 12px; font-weight: 700; padding: 8px 12px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
.smc-btn-primary { background: var(--primary); color: #fff; }
.smc-btn-ghost { background: var(--card2); color: var(--text2); border: 1px solid var(--border); }

.smc-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.smc-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 12px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.smc-info { display: flex; align-items: center; gap: 10px; min-width: 0; }
.smc-logo { width: 42px; height: 42px; border-radius: 10px; object-fit: cover; border: 1px solid var(--border); background: var(--card2); }
.smc-emoji { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 19px; font-weight: 700; color: #fff; }
.smc-name { font-size: 14px; font-weight: 700; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.smc-meta { font-size: 11px; color: var(--text3); margin-top: 2px; }
.smc-empty { text-align: center; border: 1px dashed var(--border); border-radius: 12px; padding: 30px 16px; color: var(--text3); background: var(--card); }

@media (max-width: 1000px) {
    .smc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 700px) {
    .smc-filter { grid-template-columns: 1fr; }
    .smc-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="smc-wrap">
    <div class="smc-head">
        <div>
            <h1 class="smc-title">Social Metrics Companies</h1>
            <div class="smc-sub">Step 1: Choose a company. Step 2: On view page select social media and content type, then fill metrics.</div>
        </div>
        <div class="smc-note">Access: Strategist + approved members</div>
    </div>

    <form method="GET" action="{{ route('social-metrics.panel') }}" class="smc-filter">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search company name or category...">
        <button type="submit" class="smc-btn smc-btn-primary">Search</button>
        <a href="{{ route('social-metrics.panel') }}" class="smc-btn smc-btn-ghost">Reset</a>
    </form>

    @if($clients->count())
        <div class="smc-grid">
            @foreach($clients as $client)
                <div class="smc-card">
                    <div class="smc-info">
                        @if($client->logo)
                            <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}" class="smc-logo">
                        @else
                            <div class="smc-emoji" style="background: {{ $client->color ?: '#4F6DF0' }}">
                                {{ $client->emoji ?: strtoupper(substr($client->name, 0, 1)) }}
                            </div>
                        @endif

                        <div style="min-width:0">
                            <div class="smc-name">{{ $client->name }}</div>
                            <div class="smc-meta">Published Entries: {{ $client->social_posts_count ?? 0 }}</div>
                        </div>
                    </div>

                    <a href="{{ route('social-metrics.client', $client) }}" class="smc-btn smc-btn-primary">View</a>
                </div>
            @endforeach
        </div>

        <div style="margin-top:14px">
            {{ $clients->links() }}
        </div>
    @else
        <div class="smc-empty">No companies found for the current filter.</div>
    @endif
</div>
@endsection
