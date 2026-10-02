@extends('layouts.app')

@push('styles')
<style>
.smp-wrap { max-width: 1320px; margin: 0 auto; padding-bottom: 42px; }
.smp-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:18px; }
.smp-title { font-size:28px; font-weight:800; color:var(--text); margin:0; letter-spacing:-0.5px; }
.smp-sub { margin-top:6px; color:var(--text2); font-size:13px; font-weight:500; line-height:1.7; max-width:760px; }
.smp-note { background:rgba(59,130,246,0.08); color:#2563EB; border:1px solid rgba(59,130,246,0.18); padding:8px 12px; border-radius:8px; font-size:12px; font-weight:700; display:flex; align-items:center; gap:6px; }
.smp-header-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:flex-end; }

.smp-summary { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px; margin-bottom:18px; }
.smp-summary-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:14px 16px; }
.smp-summary-label { font-size:10px; font-weight:800; color:var(--text3); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px; }
.smp-summary-value { font-size:26px; font-weight:800; color:var(--text); line-height:1; letter-spacing:-0.04em; }
.smp-summary-meta { margin-top:8px; font-size:12px; color:var(--text2); }

.smp-filter { display:flex; gap:12px; background:var(--card); border:1px solid var(--border); border-radius:14px; padding:12px; margin-bottom:22px; box-shadow:0 4px 12px rgba(0,0,0,0.02); }
.smp-filter-input-wrap { flex:1; position:relative; }
.smp-filter-input-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--text3); }
.smp-filter input, .smp-filter select { width:100%; border:1px solid var(--border); background:var(--bg); color:var(--text); border-radius:10px; padding:10px 14px; font-size:13px; font-weight:600; transition:all 0.2s; }
.smp-filter input { padding-left:36px; }
.smp-filter input:focus, .smp-filter select:focus { border-color:var(--primary); outline:none; box-shadow:0 0 0 3px rgba(139,92,246,0.1); }
.smp-filter .btn { border:0; border-radius:10px; font-size:13px; font-weight:700; padding:10px 18px; cursor:pointer; transition:all 0.2s; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
.smp-filter .btn-primary { background:linear-gradient(135deg, var(--primary), var(--purple)); color:#fff; box-shadow:0 4px 12px rgba(139,92,246,0.25); }
.smp-filter .btn-primary:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(139,92,246,0.35); }
.smp-filter .btn-ghost { background:var(--bg); color:var(--text2); border:1px solid var(--border); }
.smp-filter .btn-ghost:hover { background:var(--card2); color:var(--text); }

.smp-alert { margin-bottom:18px; padding:14px 18px; border-radius:12px; font-size:13px; font-weight:700; display:flex; align-items:flex-start; gap:10px; }
.smp-alert ul { margin:0; padding-left:18px; }
.smp-alert-error { background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.25); color:#B91C1C; }
.smp-alert-success { background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.25); color:#059669; }

.smp-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(380px, 1fr)); gap:20px; }
.smp-card { background:var(--card); border:1px solid var(--border); border-radius:18px; overflow:hidden; transition:all 0.25s ease; display:flex; flex-direction:column; min-height:100%; }
.smp-card:hover { transform:translateY(-3px); box-shadow:0 14px 34px rgba(0,0,0,0.07); border-color:color-mix(in srgb, var(--primary) 28%, var(--border)); }

.smp-card-top { display:flex; gap:14px; padding:16px; border-bottom:1px solid var(--border); background:linear-gradient(to bottom, rgba(255,255,255,0.03), transparent); }
.smp-thumb { width:68px; height:68px; border-radius:14px; background:var(--bg); border:1px solid var(--border); overflow:hidden; display:flex; align-items:center; justify-content:center; font-size:26px; color:var(--text3); flex-shrink:0; }
.smp-thumb img, .smp-thumb video { width:100%; height:100%; object-fit:cover; }
.smp-info { flex:1; min-width:0; }
.smp-meta-title { font-size:15px; font-weight:800; color:var(--text); margin-bottom:6px; line-height:1.4; }
.smp-meta-row { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:10px; }
.smp-chip { display:inline-flex; align-items:center; gap:5px; background:var(--bg); border:1px solid var(--border); color:var(--text2); font-size:10px; font-weight:700; padding:4px 8px; border-radius:999px; }
.smp-date { font-size:11px; color:var(--text3); font-weight:600; display:flex; align-items:center; gap:4px; flex-wrap:wrap; }

.smp-card-info { padding:14px 16px 0; display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:10px; }
.smp-mini-box { background:var(--bg); border:1px solid var(--border); border-radius:10px; padding:10px; }
.smp-mini-label { font-size:10px; font-weight:800; color:var(--text3); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:6px; }
.smp-mini-value { font-size:12px; color:var(--text); font-weight:700; line-height:1.5; }

.smp-card-panels { padding:14px 16px 16px; display:grid; gap:12px; flex:1; }
.smp-snapshot-panel { border:1px solid var(--border); border-radius:14px; padding:12px; background:var(--bg); }
.smp-snapshot-head { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px; }
.smp-snapshot-title { font-size:12px; font-weight:800; color:var(--text); display:flex; align-items:center; gap:6px; }
.smp-snapshot-date { font-size:11px; color:var(--text3); font-weight:600; }
.smp-snapshot-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:8px; }
.smp-stat-box { background:#fff; border:1px solid var(--border); border-radius:10px; padding:10px; text-align:center; }
.smp-stat-val { font-size:18px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; line-height:1; margin-bottom:5px; }
.smp-stat-lbl { font-size:10px; font-weight:700; color:var(--text3); text-transform:uppercase; letter-spacing:0.04em; line-height:1.4; }
.smp-snapshot-meta { margin-top:10px; display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap; font-size:11px; color:var(--text3); font-weight:600; }
.smp-empty-snapshot { padding:16px; border:1px dashed var(--border); border-radius:12px; text-align:center; color:var(--text3); font-size:12px; background:#fff; }
.badge-organic, .badge-paid { padding:4px 8px; border-radius:999px; font-weight:800; font-size:10px; display:inline-flex; align-items:center; gap:5px; }
.badge-organic { background:rgba(16,185,129,0.1); color:#059669; }
.badge-paid { background:rgba(245,158,11,0.1); color:#D97706; }

.smp-card-bottom { padding:12px 16px; border-top:1px solid var(--border); background:var(--bg); display:flex; justify-content:space-between; align-items:center; gap:12px; }
.smp-card-status { font-size:11px; font-weight:700; color:var(--text3); display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.smp-update-btn { background:var(--primary); color:#fff; border:none; border-radius:10px; padding:9px 16px; font-size:12px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px; transition:all 0.2s; box-shadow:0 2px 8px rgba(139,92,246,0.2); }
.smp-update-btn:hover { background:var(--purple); transform:translateY(-1px); box-shadow:0 4px 12px rgba(139,92,246,0.3); }

.metric-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.62); backdrop-filter:blur(4px); z-index:1000; display:none; align-items:center; justify-content:center; padding:20px; opacity:0; transition:opacity 0.25s ease; }
.metric-modal-overlay.show { display:flex; opacity:1; }
.metric-modal { background:var(--card); border:1px solid var(--border); border-radius:22px; width:100%; max-width:980px; max-height:92vh; display:flex; flex-direction:column; box-shadow:0 24px 48px rgba(0,0,0,0.25); transform:translateY(20px); transition:transform 0.25s ease; overflow:hidden; }
.metric-modal-overlay.show .metric-modal { transform:translateY(0); }
.metric-modal-header { padding:20px 24px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:flex-start; gap:14px; background:linear-gradient(to right, rgba(139,92,246,0.06), transparent); }
.metric-modal-title { font-size:20px; font-weight:800; color:var(--text); display:flex; align-items:center; gap:10px; margin:0; }
.metric-modal-sub { margin-top:6px; font-size:12px; color:var(--text3); line-height:1.6; }
.metric-modal-close { background:var(--bg); border:1px solid var(--border); color:var(--text2); width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s; }
.metric-modal-close:hover { background:var(--red); color:#fff; border-color:var(--red); }
.metric-modal-body { padding:0; overflow-y:auto; flex:1; }

.modal-tabs { display:flex; border-bottom:1px solid var(--border); background:var(--bg); padding:0 24px; }
.modal-tab { padding:14px 20px; font-size:13px; font-weight:700; color:var(--text3); border-bottom:2px solid transparent; cursor:pointer; transition:all 0.2s; }
.modal-tab:hover { color:var(--text); }
.modal-tab.active { color:var(--primary); border-bottom-color:var(--primary); }
.tab-content { display:none; padding:22px 24px; }
.tab-content.active { display:block; }

.smp-modal-overview { display:grid; grid-template-columns:1.2fr .8fr; gap:16px; }
.smp-overview-panel, .smp-editor-panel, .smp-history-panel { background:var(--bg); border:1px solid var(--border); border-radius:16px; padding:16px; }
.smp-panel-title { font-size:14px; font-weight:800; color:var(--text); margin-bottom:10px; }
.smp-overview-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px; margin-bottom:14px; }
.smp-overview-block { margin-top:12px; padding:12px 14px; border:1px solid var(--border); border-radius:12px; background:#fff; }
.smp-overview-copy { font-size:12px; color:var(--text2); line-height:1.7; white-space:pre-wrap; }
.smp-link-row { display:flex; gap:8px; flex-wrap:wrap; }
.smp-link { display:inline-flex; align-items:center; gap:6px; text-decoration:none; color:var(--primary); background:#fff; border:1px solid var(--border); padding:9px 11px; border-radius:10px; font-size:12px; font-weight:700; }
.smp-link:hover { border-color:var(--primary); background:var(--primary-dim); }
.smp-proof-preview { width:100%; aspect-ratio:16 / 10; border-radius:14px; border:1px solid var(--border); overflow:hidden; background:#fff; display:flex; align-items:center; justify-content:center; color:var(--text3); font-size:24px; }
.smp-proof-preview img, .smp-proof-preview video { width:100%; height:100%; object-fit:cover; }
.smp-media-gallery { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:10px; margin-top:14px; }
.smp-media-item { display:block; text-decoration:none; border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff; color:inherit; }
.smp-media-item:hover { border-color:var(--primary); box-shadow:0 0 0 2px rgba(139,92,246,0.08); }
.smp-media-item img, .smp-media-item video { width:100%; height:132px; object-fit:cover; display:block; background:#000; }
.smp-media-fallback { height:132px; display:flex; align-items:center; justify-content:center; background:var(--card2); color:var(--text3); font-size:26px; }
.smp-media-meta { padding:10px; display:grid; gap:4px; }
.smp-media-type { font-size:10px; font-weight:800; color:var(--text3); text-transform:uppercase; letter-spacing:0.06em; }
.smp-media-caption { font-size:11px; font-weight:700; color:var(--text); line-height:1.5; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.smp-media-empty { margin-top:14px; padding:14px; border:1px dashed var(--border); border-radius:12px; text-align:center; color:var(--text3); font-size:12px; background:#fff; }

.smp-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.smp-field { display:flex; flex-direction:column; gap:6px; }
.smp-field label { font-size:11px; font-weight:800; color:var(--text2); text-transform:uppercase; letter-spacing:0.06em; }
.smp-field input { width:100%; border:1px solid var(--border); background:#fff; color:var(--text); border-radius:10px; padding:10px 12px; font-size:14px; font-weight:600; transition:all 0.2s; }
.smp-field input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(139,92,246,0.1); outline:none; }
.smp-field.full { grid-column:1 / -1; }
.smp-form-note { margin-bottom:14px; padding:12px 14px; border-radius:12px; background:rgba(59,130,246,0.08); border:1px solid rgba(59,130,246,0.16); color:#1D4ED8; font-size:12px; font-weight:700; display:flex; align-items:flex-start; gap:8px; line-height:1.6; }
.smp-paid-box { background:rgba(245,158,11,0.05); border:1px solid rgba(245,158,11,0.2); border-radius:14px; padding:16px; margin-top:20px; }
.smp-paid-toggle-wrap { display:flex; align-items:center; justify-content:space-between; gap:12px; cursor:pointer; user-select:none; }
.smp-paid-toggle-wrap label { font-size:14px; font-weight:700; color:#D97706; display:flex; align-items:center; gap:10px; cursor:pointer; margin:0; }
.switch { position:relative; display:inline-block; width:44px; height:24px; }
.switch input { opacity:0; width:0; height:0; }
.slider { position:absolute; cursor:pointer; inset:0; background-color:var(--border); transition:.4s; border-radius:34px; }
.slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background-color:white; transition:.4s; border-radius:50%; box-shadow:0 2px 4px rgba(0,0,0,0.2); }
input:checked + .slider { background-color:#F59E0B; }
input:checked + .slider:before { transform:translateX(20px); }
.smp-paid-fields { display:none; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px; padding-top:16px; border-top:1px dashed rgba(245,158,11,0.2); }

.smp-history-table { width:100%; border-collapse:collapse; font-size:12px; min-width:960px; }
.smp-history-table th { text-align:left; padding:12px 8px; border-bottom:2px solid var(--border); color:var(--text3); font-weight:800; text-transform:uppercase; letter-spacing:0.05em; }
.smp-history-table td { padding:12px 8px; border-bottom:1px solid var(--border); color:var(--text); font-weight:600; vertical-align:top; }
.smp-history-table tr:hover td { background:#fff; }
.smp-history-wrap { overflow-x:auto; }
.action-buttons { display:flex; gap:6px; justify-content:flex-end; }
.btn-icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; border:1px solid var(--border); background:#fff; color:var(--text2); cursor:pointer; transition:all 0.2s; }
.btn-icon:hover { background:var(--bg); color:var(--primary); border-color:var(--primary); }
.btn-icon.delete:hover { color:var(--red); border-color:var(--red); }

.modal-footer { padding:16px 24px; border-top:1px solid var(--border); background:var(--bg); display:flex; justify-content:flex-end; gap:12px; border-radius:0 0 20px 20px; }
.modal-footer .btn-cancel { background:var(--card2); border:1px solid var(--border); color:var(--text); padding:10px 20px; border-radius:10px; font-weight:700; cursor:pointer; font-size:13px; }
.modal-footer .btn-submit { background:linear-gradient(135deg, var(--primary), var(--purple)); color:#fff; border:none; padding:10px 24px; border-radius:10px; font-weight:700; cursor:pointer; font-size:13px; box-shadow:0 4px 12px rgba(139,92,246,0.3); transition:all 0.2s; }
.modal-footer .btn-submit:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(139,92,246,0.4); }

.smp-empty-state { grid-column:1 / -1; text-align:center; padding:60px 20px; background:var(--card); border:1px dashed var(--border); border-radius:16px; }
.smp-empty-state i { font-size:36px; color:var(--text3); margin-bottom:16px; }
.smp-empty-state h3 { font-size:16px; font-weight:800; color:var(--text); margin-bottom:8px; }
.smp-empty-state p { font-size:13px; color:var(--text2); margin:0; }

@media (max-width: 1100px) {
    .smp-summary { grid-template-columns:repeat(2, minmax(0, 1fr)); }
    .smp-modal-overview { grid-template-columns:1fr; }
}

@media (max-width: 860px) {
    .smp-filter { flex-direction:column; }
    .smp-form-grid, .smp-paid-fields, .smp-overview-grid, .smp-card-info { grid-template-columns:1fr; }
    .smp-snapshot-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
    .smp-head { flex-direction:column; align-items:stretch; }
    .smp-header-actions { justify-content:flex-start; }
}

@media (max-width: 560px) {
    .smp-summary { grid-template-columns:1fr; }
    .smp-grid { grid-template-columns:1fr; }
    .smp-card-bottom { flex-direction:column; align-items:stretch; }
    .smp-snapshot-grid { grid-template-columns:1fr 1fr; }
}
</style>
@endpush

@section('content')
@php
    $visiblePostsCount = $posts->count();
    $trackedPostsCount = $posts->filter(fn ($post) => $post->metrics->isNotEmpty())->count();
    $organicSnapshotsCount = $posts->sum(fn ($post) => $post->metrics->where('paid_promotion', false)->count());
    $paidSnapshotsCount = $posts->sum(fn ($post) => $post->metrics->where('paid_promotion', true)->count());
@endphp

<div class="smp-wrap">
    <div class="smp-head">
        <div>
            <h1 class="smp-title">{{ $client->name }} Social Metrics</h1>
            <div class="smp-sub">Same card-and-modal workflow, but clearer. Each card now shows more post details, latest organic and paid numbers, and the modal shows the full snapshot history with every editable field.</div>
        </div>
        <div class="smp-header-actions">
            <div class="smp-note"><i class="fa-solid fa-lock"></i> Access: Strategist / Admin</div>
            <a href="{{ route('social-metrics.panel') }}" class="btn btn-ghost" style="text-decoration:none; padding:8px 16px; border-radius:8px; font-weight:700; font-size:12px">Back to Companies</a>
        </div>
    </div>

    <div class="smp-summary">
        <div class="smp-summary-card">
            <div class="smp-summary-label">Visible Posts</div>
            <div class="smp-summary-value">{{ number_format($visiblePostsCount) }}</div>
            <div class="smp-summary-meta">Shown on this page after your current filters.</div>
        </div>
        <div class="smp-summary-card">
            <div class="smp-summary-label">Posts With Metrics</div>
            <div class="smp-summary-value">{{ number_format($trackedPostsCount) }}</div>
            <div class="smp-summary-meta">{{ number_format(max($visiblePostsCount - $trackedPostsCount, 0)) }} posts still need a first snapshot.</div>
        </div>
        <div class="smp-summary-card">
            <div class="smp-summary-label">Organic Snapshots</div>
            <div class="smp-summary-value">{{ number_format($organicSnapshotsCount) }}</div>
            <div class="smp-summary-meta">Latest non-paid updates across the visible cards.</div>
        </div>
        <div class="smp-summary-card">
            <div class="smp-summary-label">Paid Snapshots</div>
            <div class="smp-summary-value">{{ number_format($paidSnapshotsCount) }}</div>
            <div class="smp-summary-meta">Boosted or promoted performance entries on this page.</div>
        </div>
    </div>

    <form method="GET" action="{{ route('social-metrics.client', $client) }}" class="smp-filter">
        <div class="smp-filter-input-wrap" style="flex: 1.5;">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts, titles, platforms...">
        </div>

        <div class="smp-filter-input-wrap">
            <select name="platform">
                <option value="">All Platforms</option>
                @foreach($platforms as $platform)
                    <option value="{{ $platform }}" {{ request('platform') === $platform ? 'selected' : '' }}>{{ ucfirst($platform) }}</option>
                @endforeach
            </select>
        </div>

        <div class="smp-filter-input-wrap">
            <select name="content_type">
                <option value="">All Content Types</option>
                @foreach($contentTypes as $type)
                    <option value="{{ $type }}" {{ request('content_type') === $type ? 'selected' : '' }}>{{ ucwords(str_replace(['_', '-'], ' ', $type)) }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:8px">
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request('search') || request('platform') || request('content_type'))
                <a class="btn btn-ghost" href="{{ route('social-metrics.client', $client) }}">Clear</a>
            @endif
        </div>
    </form>

    @if(isset($errors) && $errors->any())
        <div class="smp-alert smp-alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div>
                <div style="margin-bottom:6px">Please fix the following:</div>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    @if(session('success'))
        <div class="smp-alert smp-alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    <div class="smp-grid">
        @forelse($posts as $post)
            @php
                $task = $post->task;
                $latestOrganic = $post->latestOrganicMetric;
                $latestPaid = $post->latestPaidMetric;
                $platform = strtolower((string) $post->platform);
                $contentType = strtolower((string) ($post->post_type ?? 'post'));
                $platformTitle = ucwords(str_replace(['_', '-'], ' ', $platform));
                $contentTypeTitle = ucwords(str_replace(['_', '-'], ' ', $contentType));
                $proofThumb = $post->proof_image ? asset('storage/' . $post->proof_image) : null;
                $taskMediaItems = $task?->media ?? collect();
                $primaryTaskMedia = $taskMediaItems->first();
                $overviewText = trim((string) ($task?->brief ?: $task?->caption ?: $task?->project_notes ?: ''));
                $captionText = trim((string) ($task?->caption ?? ''));
                $hashtagsText = trim((string) ($task?->hashtags ?? ''));
                $showCaptionBlock = $captionText !== '' && $captionText !== $overviewText;

                $platformIcons = [
                    'instagram' => ['icon' => 'fa-brands fa-instagram', 'color' => '#E1306C'],
                    'facebook'  => ['icon' => 'fa-brands fa-facebook', 'color' => '#1877F2'],
                    'linkedin'  => ['icon' => 'fa-brands fa-linkedin', 'color' => '#0A66C2'],
                    'twitter'   => ['icon' => 'fa-brands fa-x-twitter', 'color' => '#000000'],
                    'x'         => ['icon' => 'fa-brands fa-x-twitter', 'color' => '#000000'],
                    'tiktok'    => ['icon' => 'fa-brands fa-tiktok', 'color' => '#000000'],
                    'youtube'   => ['icon' => 'fa-brands fa-youtube', 'color' => '#FF0000'],
                ];
                $pMeta = $platformIcons[$platform] ?? ['icon' => 'fa-solid fa-hashtag', 'color' => 'var(--text3)'];

                $metricLabels = [
                    'views' => 'Views',
                    'impressions' => 'Impressions',
                    'likes' => 'Likes',
                    'comments' => 'Comments',
                    'shares' => 'Shares',
                    'reach' => 'Reach',
                    'profile_visits' => 'Profile Visits',
                ];
                $platformLabelOverrides = [
                    'linkedin' => ['views' => 'Impressions', 'comments' => 'Engagement', 'profile_visits' => 'Profile Views'],
                    'twitter' => ['comments' => 'Replies'],
                    'x' => ['comments' => 'Replies'],
                    'youtube' => ['reach' => 'Unique Viewers'],
                ];
                if (isset($platformLabelOverrides[$platform])) {
                    $metricLabels = array_merge($metricLabels, $platformLabelOverrides[$platform]);
                }
            @endphp

            <div class="smp-card">
                <div class="smp-card-top">
                    <div class="smp-thumb">
                        @if($primaryTaskMedia?->isImage())
                            <img src="{{ $primaryTaskMedia->getUrl() }}" alt="Design Preview" loading="lazy">
                        @elseif($primaryTaskMedia?->isVideo())
                            <video muted playsinline preload="metadata">
                                <source src="{{ $primaryTaskMedia->getUrl() }}" type="{{ $primaryTaskMedia->mime_type }}">
                            </video>
                        @elseif($proofThumb)
                            <img src="{{ $proofThumb }}" alt="Thumbnail">
                        @else
                            <i class="{{ $pMeta['icon'] }}" style="color: {{ $pMeta['color'] }}"></i>
                        @endif
                    </div>
                    <div class="smp-info">
                        <div class="smp-meta-title" title="{{ $task?->title }}">{{ $task?->title ?? 'Untitled Task' }}</div>
                        <div class="smp-meta-row">
                            <span class="smp-chip" style="color:{{ $pMeta['color'] }}; border-color:{{ $pMeta['color'] }}40; background:{{ $pMeta['color'] }}10"><i class="{{ $pMeta['icon'] }}"></i> {{ $platformTitle }}</span>
                            <span class="smp-chip"><i class="fa-solid fa-layer-group"></i> {{ $contentTypeTitle }}</span>
                            <span class="smp-chip"><i class="fa-solid fa-clock-rotate-left"></i> {{ $post->metrics->count() }} snapshots</span>
                        </div>
                        <div class="smp-date">
                            <span><i class="fa-regular fa-calendar"></i> Posted: {{ $post->posted_at ? date('d M Y', strtotime((string) $post->posted_at)) : 'N/A' }}</span>
                            @if($post->post_url)
                                <span><i class="fa-solid fa-arrow-up-right-from-square"></i> Live link available</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="smp-card-info">
                    <div class="smp-mini-box">
                        <div class="smp-mini-label">Task Deadline</div>
                        <div class="smp-mini-value">{{ $task?->deadline?->format('d M Y') ?? 'Not set' }}</div>
                    </div>
                    <div class="smp-mini-box">
                        <div class="smp-mini-label">Latest Organic</div>
                        <div class="smp-mini-value">{{ $latestOrganic?->snapshot_date?->format('d M Y') ?? 'No snapshot yet' }}</div>
                    </div>
                    <div class="smp-mini-box">
                        <div class="smp-mini-label">Latest Paid</div>
                        <div class="smp-mini-value">{{ $latestPaid?->snapshot_date?->format('d M Y') ?? 'No paid snapshot' }}</div>
                    </div>
                </div>

                <div class="smp-card-panels">
                    <div class="smp-snapshot-panel">
                        <div class="smp-snapshot-head">
                            <div class="smp-snapshot-title"><span class="badge-organic"><i class="fa-solid fa-seedling"></i> Organic</span></div>
                            <div class="smp-snapshot-date">{{ $latestOrganic?->snapshot_date?->format('d M Y') ?? 'No snapshot yet' }}</div>
                        </div>

                        @if($latestOrganic)
                            <div class="smp-snapshot-grid">
                                <div class="smp-stat-box">
                                    <div class="smp-stat-val">{{ $latestOrganic->reach !== null ? number_format($latestOrganic->reach) : '—' }}</div>
                                    <div class="smp-stat-lbl">{{ $metricLabels['reach'] }}</div>
                                </div>
                                <div class="smp-stat-box">
                                    <div class="smp-stat-val">{{ $latestOrganic->likes !== null ? number_format($latestOrganic->likes) : '—' }}</div>
                                    <div class="smp-stat-lbl">{{ $metricLabels['likes'] }}</div>
                                </div>
                                <div class="smp-stat-box">
                                    <div class="smp-stat-val">{{ $latestOrganic->comments !== null ? number_format($latestOrganic->comments) : '—' }}</div>
                                    <div class="smp-stat-lbl">{{ $metricLabels['comments'] }}</div>
                                </div>
                                <div class="smp-stat-box">
                                    <div class="smp-stat-val">{{ $latestOrganic->shares !== null ? number_format($latestOrganic->shares) : '—' }}</div>
                                    <div class="smp-stat-lbl">{{ $metricLabels['shares'] }}</div>
                                </div>
                            </div>
                            <div class="smp-snapshot-meta">
                                <span>Updated by {{ $latestOrganic->updater?->name ?? $latestOrganic->creator?->name ?? 'System' }}</span>
                                <span>{{ $latestOrganic->profile_visits !== null ? number_format($latestOrganic->profile_visits) : '—' }} {{ $metricLabels['profile_visits'] }}</span>
                            </div>
                        @else
                            <div class="smp-empty-snapshot">No organic metrics yet. Open the modal to add the first snapshot.</div>
                        @endif
                    </div>

                    <div class="smp-snapshot-panel">
                        <div class="smp-snapshot-head">
                            <div class="smp-snapshot-title"><span class="badge-paid"><i class="fa-solid fa-bullhorn"></i> Paid</span></div>
                            <div class="smp-snapshot-date">{{ $latestPaid?->snapshot_date?->format('d M Y') ?? 'No paid snapshot' }}</div>
                        </div>

                        @if($latestPaid)
                            <div class="smp-snapshot-grid">
                                <div class="smp-stat-box">
                                    <div class="smp-stat-val">{{ $latestPaid->reach !== null ? number_format($latestPaid->reach) : '—' }}</div>
                                    <div class="smp-stat-lbl">{{ $metricLabels['reach'] }}</div>
                                </div>
                                <div class="smp-stat-box">
                                    <div class="smp-stat-val">{{ $latestPaid->likes !== null ? number_format($latestPaid->likes) : '—' }}</div>
                                    <div class="smp-stat-lbl">{{ $metricLabels['likes'] }}</div>
                                </div>
                                <div class="smp-stat-box">
                                    <div class="smp-stat-val">{{ $latestPaid->comments !== null ? number_format($latestPaid->comments) : '—' }}</div>
                                    <div class="smp-stat-lbl">{{ $metricLabels['comments'] }}</div>
                                </div>
                                <div class="smp-stat-box">
                                    <div class="smp-stat-val">{{ $latestPaid->ad_spend_inr !== null ? number_format((float) $latestPaid->ad_spend_inr, 2) : '—' }}</div>
                                    <div class="smp-stat-lbl">Ad Spend (INR)</div>
                                </div>
                            </div>
                            <div class="smp-snapshot-meta">
                                <span>Updated by {{ $latestPaid->updater?->name ?? $latestPaid->creator?->name ?? 'System' }}</span>
                                <span>{{ $latestPaid->target_area ?: 'No target area added' }}</span>
                            </div>
                        @else
                            <div class="smp-empty-snapshot">No paid metrics yet. Use the paid toggle in the modal when the post is promoted.</div>
                        @endif
                    </div>
                </div>

                <div class="smp-card-bottom">
                    <div class="smp-card-status">
                        @if($latestOrganic || $latestPaid)
                            <span style="color:#10B981"><i class="fa-solid fa-check-circle"></i> Metrics tracked</span>
                        @else
                            <span><i class="fa-solid fa-circle-exclamation"></i> No metrics yet</span>
                        @endif
                        <span>&middot;</span>
                        <span>Organic {{ $post->metrics->where('paid_promotion', false)->count() }}</span>
                        <span>&middot;</span>
                        <span>Paid {{ $post->metrics->where('paid_promotion', true)->count() }}</span>
                    </div>
                    <button type="button" class="smp-update-btn" onclick="openMetricsModal({{ $post->id }})">
                        <i class="fa-solid fa-chart-line"></i> View / Update
                    </button>
                </div>
            </div>

            <div id="modal-{{ $post->id }}" class="metric-modal-overlay">
                <div class="metric-modal">
                    <div class="metric-modal-header">
                        <div>
                            <h2 class="metric-modal-title"><i class="{{ $pMeta['icon'] }}" style="color:{{ $pMeta['color'] }}"></i> {{ $platformTitle }} Metrics</h2>
                            <div class="metric-modal-sub">{{ $task?->title ?? 'Untitled Task' }} · {{ $contentTypeTitle }} · Posted {{ $post->posted_at ? date('d M Y', strtotime((string) $post->posted_at)) : 'N/A' }}</div>
                        </div>
                        <button class="metric-modal-close" onclick="closeMetricsModal({{ $post->id }})"><i class="fa-solid fa-xmark"></i></button>
                    </div>

                    <div class="modal-tabs">
                        <div class="modal-tab active" onclick="switchTab(this, 'overview-tab-{{ $post->id }}')">Overview</div>
                        <div class="modal-tab" onclick="switchTab(this, 'add-tab-{{ $post->id }}')">Add / Edit Snapshot</div>
                        <div class="modal-tab" onclick="switchTab(this, 'history-tab-{{ $post->id }}')">History ({{ $post->metrics->count() }})</div>
                    </div>

                    <div class="metric-modal-body">
                        <div id="overview-tab-{{ $post->id }}" class="tab-content active">
                            <div class="smp-modal-overview">
                                <div class="smp-overview-panel">
                                    <div class="smp-panel-title">Post Information</div>
                                    <div class="smp-overview-grid">
                                        <div class="smp-mini-box">
                                            <div class="smp-mini-label">Platform</div>
                                            <div class="smp-mini-value">{{ $platformTitle }}</div>
                                        </div>
                                        <div class="smp-mini-box">
                                            <div class="smp-mini-label">Content Type</div>
                                            <div class="smp-mini-value">{{ $contentTypeTitle }}</div>
                                        </div>
                                        <div class="smp-mini-box">
                                            <div class="smp-mini-label">Posted On</div>
                                            <div class="smp-mini-value">{{ $post->posted_at ? date('d M Y', strtotime((string) $post->posted_at)) : 'N/A' }}</div>
                                        </div>
                                        <div class="smp-mini-box">
                                            <div class="smp-mini-label">Poster</div>
                                            <div class="smp-mini-value">{{ $post->poster?->name ?? 'Unknown' }}</div>
                                        </div>
                                        <div class="smp-mini-box">
                                            <div class="smp-mini-label">Organic Snapshots</div>
                                            <div class="smp-mini-value">{{ $post->metrics->where('paid_promotion', false)->count() }}</div>
                                        </div>
                                        <div class="smp-mini-box">
                                            <div class="smp-mini-label">Paid Snapshots</div>
                                            <div class="smp-mini-value">{{ $post->metrics->where('paid_promotion', true)->count() }}</div>
                                        </div>
                                    </div>

                                    <div class="smp-link-row">
                                        @if($post->post_url)
                                            <a href="{{ $post->post_url }}" target="_blank" rel="noopener noreferrer" class="smp-link"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open Live Post</a>
                                        @endif
                                        @if($proofThumb)
                                            <a href="{{ $proofThumb }}" target="_blank" rel="noopener noreferrer" class="smp-link"><i class="fa-regular fa-image"></i> View Proof Image</a>
                                        @endif
                                        @if($primaryTaskMedia)
                                            <a href="{{ $primaryTaskMedia->getUrl() }}" target="_blank" rel="noopener noreferrer" class="smp-link"><i class="fa-solid fa-photo-film"></i> Open Main Creative</a>
                                        @endif
                                    </div>

                                    <div class="smp-overview-block">
                                        <div class="smp-mini-label">Post Overview</div>
                                        <div class="smp-overview-copy">{{ $overviewText !== '' ? $overviewText : 'No post overview added for this task yet.' }}</div>
                                    </div>

                                    @if($showCaptionBlock)
                                        <div class="smp-overview-block">
                                            <div class="smp-mini-label">Caption</div>
                                            <div class="smp-overview-copy">{{ $captionText }}</div>
                                        </div>
                                    @endif

                                    @if($hashtagsText !== '')
                                        <div class="smp-overview-block">
                                            <div class="smp-mini-label">Hashtags</div>
                                            <div class="smp-overview-copy">{{ $hashtagsText }}</div>
                                        </div>
                                    @endif

                                    <div class="smp-form-note" style="margin-top:14px;margin-bottom:0">
                                        <i class="fa-solid fa-circle-info"></i>
                                        <div>Use the snapshot editor for every update. The history tab keeps all organic and paid snapshots separate so you can compare how the post performed over time.</div>
                                    </div>
                                </div>

                                <div class="smp-overview-panel">
                                    <div class="smp-panel-title">Creative Preview</div>
                                    <div class="smp-proof-preview">
                                        @if($primaryTaskMedia?->isImage())
                                            <img src="{{ $primaryTaskMedia->getUrl() }}" alt="Creative Preview" loading="lazy">
                                        @elseif($primaryTaskMedia?->isVideo())
                                            <video controls playsinline preload="metadata">
                                                <source src="{{ $primaryTaskMedia->getUrl() }}" type="{{ $primaryTaskMedia->mime_type }}">
                                            </video>
                                        @elseif($proofThumb)
                                            <img src="{{ $proofThumb }}" alt="Proof Preview">
                                        @else
                                            <i class="{{ $pMeta['icon'] }}" style="color:{{ $pMeta['color'] }}"></i>
                                        @endif
                                    </div>

                                    @if($taskMediaItems->isNotEmpty())
                                        <div class="smp-panel-title" style="margin-top:14px">Images / Video</div>
                                        <div class="smp-media-gallery">
                                            @foreach($taskMediaItems as $media)
                                                @php
                                                    $mediaType = $media->isVideo() ? 'Video' : ($media->isImage() ? 'Image' : 'File');
                                                    $mediaIcon = $media->isVideo() ? 'fa-video' : ($media->isImage() ? 'fa-image' : 'fa-file');
                                                @endphp
                                                <a href="{{ $media->getUrl() }}" target="_blank" rel="noopener noreferrer" class="smp-media-item">
                                                    @if($media->isImage())
                                                        <img src="{{ $media->getUrl() }}" alt="{{ $media->name ?: $media->file_name }}" loading="lazy">
                                                    @elseif($media->isVideo())
                                                        <video muted playsinline preload="metadata">
                                                            <source src="{{ $media->getUrl() }}" type="{{ $media->mime_type }}">
                                                        </video>
                                                    @else
                                                        <div class="smp-media-fallback"><i class="fa-solid {{ $mediaIcon }}"></i></div>
                                                    @endif
                                                    <div class="smp-media-meta">
                                                        <span class="smp-media-type">{{ $mediaType }}</span>
                                                        <span class="smp-media-caption">{{ $media->name ?: $media->file_name }}</span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @elseif(!$proofThumb)
                                        <div class="smp-media-empty">No image or video has been uploaded for this post yet.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div id="add-tab-{{ $post->id }}" class="tab-content">
                            <div class="smp-editor-panel">
                                <div class="smp-panel-title">Snapshot Editor</div>
                                <div class="smp-form-note">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <div>All available fields are visible here. Use Edit in history to load an old snapshot, change anything, then save it again.</div>
                                </div>

                                <form method="POST" action="{{ route('social-metrics.store', $post) }}" id="form-{{ $post->id }}" data-store-action="{{ route('social-metrics.store', $post) }}" data-update-base="{{ url('/social-metrics') }}">
                                    @csrf
                                    <div class="smp-form-grid">
                                        <div class="smp-field full">
                                            <label>Snapshot Date *</label>
                                            <input type="date" name="snapshot_date" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required>
                                        </div>

                                        @if($platform === 'linkedin')
                                            <input type="hidden" name="views" value="">
                                            <div class="smp-field">
                                                <label>{{ $metricLabels['views'] }}</label>
                                                <input type="number" min="0" name="impressions" placeholder="0">
                                            </div>
                                        @else
                                            <div class="smp-field">
                                                <label>{{ $metricLabels['views'] }}</label>
                                                <input type="number" min="0" name="views" placeholder="0">
                                            </div>
                                            <div class="smp-field">
                                                <label>{{ $metricLabels['impressions'] }}</label>
                                                <input type="number" min="0" name="impressions" placeholder="0">
                                            </div>
                                        @endif

                                        <div class="smp-field">
                                            <label>{{ $metricLabels['reach'] }}</label>
                                            <input type="number" min="0" name="reach" placeholder="0">
                                        </div>
                                        <div class="smp-field">
                                            <label>{{ $metricLabels['likes'] }}</label>
                                            <input type="number" min="0" name="likes" placeholder="0">
                                        </div>
                                        <div class="smp-field">
                                            <label>{{ $metricLabels['comments'] }}</label>
                                            <input type="number" min="0" name="comments" placeholder="0">
                                        </div>
                                        <div class="smp-field">
                                            <label>{{ $metricLabels['shares'] }}</label>
                                            <input type="number" min="0" name="shares" placeholder="0">
                                        </div>
                                        <div class="smp-field full">
                                            <label>{{ $metricLabels['profile_visits'] }}</label>
                                            <input type="number" min="0" name="profile_visits" placeholder="0">
                                        </div>
                                    </div>

                                    <div class="smp-paid-box">
                                        <div class="smp-paid-toggle-wrap" onclick="togglePaidFields({{ $post->id }})">
                                            <label>
                                                <span class="switch" onclick="event.stopPropagation()">
                                                    <input type="hidden" name="paid_promotion" value="0" id="hidden-paid-{{ $post->id }}">
                                                    <input type="checkbox" id="toggle-paid-{{ $post->id }}" onchange="togglePaidFields({{ $post->id }})">
                                                    <span class="slider"></span>
                                                </span>
                                                Ad Spend / Paid Promotion
                                            </label>
                                            <span id="badge-paid-{{ $post->id }}" class="badge-organic">Organic</span>
                                        </div>

                                        <div class="smp-paid-fields" id="paid-fields-{{ $post->id }}">
                                            <div class="smp-field">
                                                <label>Ad Spend (INR)</label>
                                                <input type="number" step="0.01" min="0" name="ad_spend_inr" placeholder="Amount in Rs.">
                                            </div>
                                            <div class="smp-field">
                                                <label>Target Area</label>
                                                <input type="text" name="target_area" maxlength="255" placeholder="City / region">
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div id="history-tab-{{ $post->id }}" class="tab-content">
                            <div class="smp-history-panel">
                                <div class="smp-panel-title">Snapshot History</div>
                                @if($post->metrics->count() > 0)
                                    <div class="smp-history-wrap">
                                        <table class="smp-history-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    @if($platform !== 'linkedin')
                                                        <th>{{ $metricLabels['views'] }}</th>
                                                    @endif
                                                    <th>{{ $metricLabels['impressions'] }}</th>
                                                    <th>{{ $metricLabels['reach'] }}</th>
                                                    <th>{{ $metricLabels['likes'] }}</th>
                                                    <th>{{ $metricLabels['comments'] }}</th>
                                                    <th>{{ $metricLabels['shares'] }}</th>
                                                    <th>{{ $metricLabels['profile_visits'] }}</th>
                                                    <th>Ad Spend</th>
                                                    <th>Target Area</th>
                                                    <th>Updated By</th>
                                                    <th style="text-align:right">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($post->metrics->sortByDesc('snapshot_date') as $h)
                                                    <tr>
                                                        <td>{{ date('d M Y', strtotime((string) $h->snapshot_date)) }}</td>
                                                        <td>
                                                            @if($h->paid_promotion)
                                                                <span class="badge-paid"><i class="fa-solid fa-bullhorn"></i> Paid</span>
                                                            @else
                                                                <span class="badge-organic"><i class="fa-solid fa-seedling"></i> Organic</span>
                                                            @endif
                                                        </td>
                                                        @if($platform !== 'linkedin')
                                                            <td>{{ $h->views !== null ? number_format($h->views) : '—' }}</td>
                                                        @endif
                                                        <td>{{ $h->impressions !== null ? number_format($h->impressions) : '—' }}</td>
                                                        <td>{{ $h->reach !== null ? number_format($h->reach) : '—' }}</td>
                                                        <td>{{ $h->likes !== null ? number_format($h->likes) : '—' }}</td>
                                                        <td>{{ $h->comments !== null ? number_format($h->comments) : '—' }}</td>
                                                        <td>{{ $h->shares !== null ? number_format($h->shares) : '—' }}</td>
                                                        <td>{{ $h->profile_visits !== null ? number_format($h->profile_visits) : '—' }}</td>
                                                        <td>{{ $h->ad_spend_inr !== null ? number_format((float) $h->ad_spend_inr, 2) : '—' }}</td>
                                                        <td>{{ $h->target_area ?: '—' }}</td>
                                                        <td>{{ $h->updater?->name ?? $h->creator?->name ?? 'System' }}</td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <button type="button" class="btn-icon" title="Edit" onclick='editSnapshot({{ $post->id }}, @json($h))'>
                                                                    <i class="fa-solid fa-pen"></i>
                                                                </button>
                                                                <form method="POST" action="{{ route('social-metrics.destroy', $h) }}" onsubmit="return confirm('Delete this snapshot?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn-icon delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="smp-empty-snapshot" style="margin-top:10px">No history yet. Add a snapshot to start tracking performance.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" onclick="closeMetricsModal({{ $post->id }})">Close</button>
                        <button type="button" class="btn-submit" id="submit-btn-{{ $post->id }}" onclick="submitMetricsForm({{ $post->id }})">Save Snapshot</button>
                    </div>
                </div>
            </div>

        @empty
            <div class="smp-empty-state">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>No posts found</h3>
                <p>Try adjusting your filters or search query.</p>
            </div>
        @endforelse
    </div>

    <div style="margin-top: 30px;">
        {{ $posts->links() }}
    </div>
</div>

@push('scripts')
<script>
function openMetricsModal(id) {
    const modal = document.getElementById('modal-' + id);
    if (!modal) return;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    resetModalTabs(id);
}

function closeMetricsModal(id) {
    const modal = document.getElementById('modal-' + id);
    if (!modal) return;

    modal.classList.remove('show');
    document.body.style.overflow = '';

    const form = document.getElementById('form-' + id);
    if (form) {
        form.reset();
        form.action = form.dataset.storeAction || form.action;
        const methodInput = form.querySelector('[name="_method"]');
        if (methodInput) {
            methodInput.remove();
        }
    }

    const submitBtn = document.getElementById('submit-btn-' + id);
    if (submitBtn) {
        submitBtn.innerHTML = 'Save Snapshot';
    }

    const toggle = document.getElementById('toggle-paid-' + id);
    if (toggle) {
        toggle.checked = false;
    }
    togglePaidFields(id, false);
    resetModalTabs(id);
}

function resetModalTabs(id) {
    const modal = document.getElementById('modal-' + id);
    if (!modal) return;

    modal.querySelectorAll('.modal-tab').forEach((tab, index) => {
        tab.classList.toggle('active', index === 0);
    });

    modal.querySelectorAll('.tab-content').forEach((content) => {
        content.classList.remove('active');
    });

    const overview = document.getElementById('overview-tab-' + id);
    if (overview) {
        overview.classList.add('active');
    }

    const footer = modal.querySelector('.modal-footer');
    if (footer) {
        footer.style.display = 'none';
    }
}

function switchTab(btn, targetId) {
    const modal = btn.closest('.metric-modal');
    if (!modal) return;

    modal.querySelectorAll('.modal-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');

    modal.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    const target = document.getElementById(targetId);
    if (target) {
        target.classList.add('active');
    }

    const footer = modal.querySelector('.modal-footer');
    if (footer) {
        footer.style.display = targetId.includes('add-tab-') ? 'flex' : 'none';
    }
}

function togglePaidFields(id, forceValue = null) {
    const toggle = document.getElementById('toggle-paid-' + id);
    const hidden = document.getElementById('hidden-paid-' + id);
    const fields = document.getElementById('paid-fields-' + id);
    const badge = document.getElementById('badge-paid-' + id);

    if (!toggle || !hidden || !fields || !badge) return;

    if (forceValue !== null) {
        toggle.checked = forceValue;
    }

    if (toggle.checked) {
        hidden.value = '1';
        fields.style.display = 'grid';
        badge.className = 'badge-paid';
        badge.innerHTML = '<i class="fa-solid fa-bullhorn"></i> Paid';
    } else {
        hidden.value = '0';
        fields.style.display = 'none';
        badge.className = 'badge-organic';
        badge.innerHTML = '<i class="fa-solid fa-seedling"></i> Organic';
        fields.querySelectorAll('input').forEach(input => input.value = '');
    }
}

function editSnapshot(postId, data) {
    const modal = document.getElementById('modal-' + postId);
    const form = document.getElementById('form-' + postId);
    if (!modal || !form) return;

    const editTabBtn = modal.querySelectorAll('.modal-tab')[1];
    switchTab(editTabBtn, 'add-tab-' + postId);

    const normalizedDate = typeof data.snapshot_date === 'string'
        ? data.snapshot_date.split('T')[0]
        : '';

    const setVal = (name, val) => {
        const el = form.querySelector(`[name="${name}"]`);
        if (el) el.value = val !== null && val !== undefined ? val : '';
    };

    setVal('snapshot_date', normalizedDate);
    setVal('views', data.views);
    setVal('impressions', data.impressions);
    setVal('reach', data.reach);
    setVal('likes', data.likes);
    setVal('comments', data.comments);
    setVal('shares', data.shares);
    setVal('profile_visits', data.profile_visits);
    setVal('ad_spend_inr', data.ad_spend_inr);
    setVal('target_area', data.target_area);

    togglePaidFields(postId, !!data.paid_promotion);

    form.action = `${form.dataset.updateBase}/${data.id}`;
    if (!form.querySelector('[name="_method"]')) {
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PATCH';
        form.appendChild(methodInput);
    } else {
        form.querySelector('[name="_method"]').value = 'PATCH';
    }

    const submitBtn = document.getElementById('submit-btn-' + postId);
    if (submitBtn) {
        submitBtn.innerHTML = 'Update Snapshot';
    }
}

function submitMetricsForm(postId) {
    const form = document.getElementById('form-' + postId);
    if (form) {
        form.submit();
    }
}

window.addEventListener('click', function(event) {
    if (event.target.classList.contains('metric-modal-overlay')) {
        const id = event.target.id.replace('modal-', '');
        closeMetricsModal(id);
    }
});
</script>
@endpush
@endsection
