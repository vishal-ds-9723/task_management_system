{{-- ===== POST INSIGHTS PANEL (Organic vs Paid) ===== --}}
@php
    $pi = $postInsights;
    $hasData = $pi['has_data'] ?? false;
    $totalPosts = $pi['total_posts'] ?? 0;
    $org = $pi['organic'];
    $pd = $pi['paid'];
    $platforms = $pi['platforms'] ?? [];
    $topPosts = $pi['top_posts'] ?? [];
    $organicPct = $totalPosts > 0 ? round(($org['posts'] / $totalPosts) * 100) : 0;
    $paidPct = $totalPosts > 0 ? round(($pd['posts'] / $totalPosts) * 100) : 0;
    $totalReach = $org['reach'] + $pd['reach'];
    $totalImpressions = $org['impressions'] + $pd['impressions'];
    $totalEngagement = ($org['likes'] + $org['comments'] + $org['shares']) + ($pd['likes'] + $pd['comments'] + $pd['shares']);

    $platformMeta = [
        'instagram' => ['icon' => 'fa-brands fa-instagram', 'color' => '#E1306C', 'label' => 'Instagram'],
        'facebook'  => ['icon' => 'fa-brands fa-facebook', 'color' => '#1877F2', 'label' => 'Facebook'],
        'twitter'   => ['icon' => 'fa-brands fa-x-twitter', 'color' => '#000', 'label' => 'Twitter/X'],
        'linkedin'  => ['icon' => 'fa-brands fa-linkedin', 'color' => '#0A66C2', 'label' => 'LinkedIn'],
        'youtube'   => ['icon' => 'fa-brands fa-youtube', 'color' => '#FF0000', 'label' => 'YouTube'],
        'tiktok'    => ['icon' => 'fa-brands fa-tiktok', 'color' => '#000', 'label' => 'TikTok'],
    ];
@endphp

<div class="pi-panel" id="postInsightsPanel">
    {{-- Header --}}
    <div class="pi-header">
        <div class="pi-header-left">
            <div class="pi-icon-wrap"><i class="fa-solid fa-chart-mixed"></i></div>
            <div>
                <h3 class="pi-title">Post Insights</h3>
                <div class="pi-subtitle">Performance analytics · Organic vs Paid</div>
            </div>
        </div>
        @if($hasData)
        <div class="pi-header-badges">
            <span class="pi-badge pi-badge-organic"><i class="fa-solid fa-leaf"></i> {{ $org['posts'] }} Organic</span>
            <span class="pi-badge pi-badge-paid"><i class="fa-solid fa-bullhorn"></i> {{ $pd['posts'] }} Paid</span>
        </div>
        @endif
    </div>

    @if(!$hasData)
        <div class="pi-empty">
            <div class="pi-empty-icon"><i class="fa-solid fa-chart-column"></i></div>
            <div class="pi-empty-title">No post insights yet</div>
            <div class="pi-empty-desc">Insights will appear here once posts are published and metrics are recorded.</div>
        </div>
    @else
        {{-- Organic vs Paid Split Bar --}}
        <div class="pi-split-section">
            <div class="pi-split-bar">
                <div class="pi-split-organic" style="width:{{ max($organicPct, 2) }}%">
                    <span>{{ $organicPct }}%</span>
                </div>
                <div class="pi-split-paid" style="width:{{ max($paidPct, 2) }}%">
                    <span>{{ $paidPct }}%</span>
                </div>
            </div>
            <div class="pi-split-legend">
                <span><span class="pi-dot" style="background:#10B981"></span> Organic ({{ $org['posts'] }})</span>
                <span><span class="pi-dot" style="background:#F59E0B"></span> Paid ({{ $pd['posts'] }})</span>
                <span style="margin-left:auto;color:var(--text3)">{{ $totalPosts }} total posts</span>
            </div>
        </div>

        {{-- KPI Cards Row --}}
        <div class="pi-kpi-grid">
            <div class="pi-kpi" style="--kc:#3B82F6">
                <div class="pi-kpi-icon"><i class="fa-solid fa-eye"></i></div>
                <div class="pi-kpi-body">
                    <div class="pi-kpi-val">{{ number_format($totalReach) }}</div>
                    <div class="pi-kpi-label">Total Reach</div>
                </div>
                <div class="pi-kpi-split">
                    <span class="pi-kpi-org">{{ number_format($org['reach']) }}</span>
                    <span class="pi-kpi-pd">{{ number_format($pd['reach']) }}</span>
                </div>
            </div>
            <div class="pi-kpi" style="--kc:#8B5CF6">
                <div class="pi-kpi-icon"><i class="fa-solid fa-signal"></i></div>
                <div class="pi-kpi-body">
                    <div class="pi-kpi-val">{{ number_format($totalImpressions) }}</div>
                    <div class="pi-kpi-label">Impressions</div>
                </div>
                <div class="pi-kpi-split">
                    <span class="pi-kpi-org">{{ number_format($org['impressions']) }}</span>
                    <span class="pi-kpi-pd">{{ number_format($pd['impressions']) }}</span>
                </div>
            </div>
            <div class="pi-kpi" style="--kc:#EC4899">
                <div class="pi-kpi-icon"><i class="fa-solid fa-heart"></i></div>
                <div class="pi-kpi-body">
                    <div class="pi-kpi-val">{{ number_format($totalEngagement) }}</div>
                    <div class="pi-kpi-label">Engagement</div>
                </div>
                <div class="pi-kpi-split">
                    <span class="pi-kpi-org">{{ number_format($org['likes'] + $org['comments'] + $org['shares']) }}</span>
                    <span class="pi-kpi-pd">{{ number_format($pd['likes'] + $pd['comments'] + $pd['shares']) }}</span>
                </div>
            </div>
            @if($pd['ad_spend'] > 0)
            <div class="pi-kpi" style="--kc:#F59E0B">
                <div class="pi-kpi-icon"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                <div class="pi-kpi-body">
                    <div class="pi-kpi-val">₹{{ number_format($pd['ad_spend'], 0) }}</div>
                    <div class="pi-kpi-label">Ad Spend</div>
                </div>
                <div class="pi-kpi-split">
                    <span class="pi-kpi-org">—</span>
                    <span class="pi-kpi-pd">₹{{ number_format($pd['ad_spend'], 0) }}</span>
                </div>
            </div>
            @endif
        </div>

        {{-- Detailed Organic vs Paid Comparison --}}
        <div class="pi-comparison">
            <div class="pi-comp-col pi-comp-organic">
                <div class="pi-comp-header">
                    <i class="fa-solid fa-leaf"></i> Organic
                    <span class="pi-comp-count">{{ $org['posts'] }} posts</span>
                </div>
                <div class="pi-comp-metrics">
                    <div class="pi-comp-row"><span>Views</span><strong>{{ number_format($org['views']) }}</strong></div>
                    <div class="pi-comp-row"><span>Reach</span><strong>{{ number_format($org['reach']) }}</strong></div>
                    <div class="pi-comp-row"><span>Impressions</span><strong>{{ number_format($org['impressions']) }}</strong></div>
                    <div class="pi-comp-row"><span>Likes</span><strong>{{ number_format($org['likes']) }}</strong></div>
                    <div class="pi-comp-row"><span>Comments</span><strong>{{ number_format($org['comments']) }}</strong></div>
                    <div class="pi-comp-row"><span>Shares</span><strong>{{ number_format($org['shares']) }}</strong></div>
                    <div class="pi-comp-row"><span>Profile Visits</span><strong>{{ number_format($org['profile_visits']) }}</strong></div>
                </div>
            </div>
            <div class="pi-comp-divider">
                <div class="pi-comp-vs">VS</div>
            </div>
            <div class="pi-comp-col pi-comp-paid">
                <div class="pi-comp-header">
                    <i class="fa-solid fa-bullhorn"></i> Paid
                    <span class="pi-comp-count">{{ $pd['posts'] }} posts</span>
                </div>
                <div class="pi-comp-metrics">
                    <div class="pi-comp-row"><span>Views</span><strong>{{ number_format($pd['views']) }}</strong></div>
                    <div class="pi-comp-row"><span>Reach</span><strong>{{ number_format($pd['reach']) }}</strong></div>
                    <div class="pi-comp-row"><span>Impressions</span><strong>{{ number_format($pd['impressions']) }}</strong></div>
                    <div class="pi-comp-row"><span>Likes</span><strong>{{ number_format($pd['likes']) }}</strong></div>
                    <div class="pi-comp-row"><span>Comments</span><strong>{{ number_format($pd['comments']) }}</strong></div>
                    <div class="pi-comp-row"><span>Shares</span><strong>{{ number_format($pd['shares']) }}</strong></div>
                    <div class="pi-comp-row"><span>Profile Visits</span><strong>{{ number_format($pd['profile_visits']) }}</strong></div>
                </div>
                @if($pd['ad_spend'] > 0)
                <div class="pi-comp-spend">
                    <i class="fa-solid fa-indian-rupee-sign"></i>
                    Total Spend: <strong>₹{{ number_format($pd['ad_spend'], 2) }}</strong>
                </div>
                @endif
            </div>
        </div>

        {{-- Platform Breakdown --}}
        @if(count($platforms) > 0)
        <div class="pi-platforms">
            <div class="pi-section-title"><i class="fa-solid fa-layer-group"></i> Platform Breakdown</div>
            <div class="pi-platform-grid">
                @foreach($platforms as $pKey => $pData)
                    @php $meta = $platformMeta[$pKey] ?? ['icon' => 'fa-solid fa-globe', 'color' => '#6B7280', 'label' => ucfirst($pKey)]; @endphp
                    <div class="pi-platform-card" style="--plc:{{ $meta['color'] }}">
                        <div class="pi-platform-head">
                            <i class="{{ $meta['icon'] }}" style="color:{{ $meta['color'] }};font-size:16px"></i>
                            <span class="pi-platform-name">{{ $meta['label'] }}</span>
                            <div class="pi-platform-tags">
                                @if($pData['organic'] > 0)<span class="pi-ptag pi-ptag-org">{{ $pData['organic'] }} organic</span>@endif
                                @if($pData['paid'] > 0)<span class="pi-ptag pi-ptag-pd">{{ $pData['paid'] }} paid</span>@endif
                            </div>
                        </div>
                        <div class="pi-platform-stats">
                            <div><span>{{ number_format($pData['reach']) }}</span><small>Reach</small></div>
                            <div><span>{{ number_format($pData['likes']) }}</span><small>Likes</small></div>
                            <div><span>{{ number_format($pData['comments']) }}</span><small>Comments</small></div>
                            <div><span>{{ number_format($pData['shares']) }}</span><small>Shares</small></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Top Performing Posts --}}
        @if(count($topPosts) > 0)
        <div class="pi-top-posts">
            <div class="pi-section-title"><i class="fa-solid fa-trophy"></i> Top Performing Posts</div>
            @foreach($topPosts as $idx => $tp)
                @php $tPost = $tp['post']; $tMetric = $tp['metric']; $tMeta = $platformMeta[$tPost->platform] ?? ['icon'=>'fa-solid fa-globe','color'=>'#6B7280','label'=>ucfirst($tPost->platform)]; @endphp
                <div class="pi-top-row">
                    <div class="pi-top-rank" style="background:{{ $idx === 0 ? 'linear-gradient(135deg,#F59E0B,#FBBF24)' : ($idx === 1 ? 'linear-gradient(135deg,#94A3B8,#CBD5E1)' : 'linear-gradient(135deg,#D97706,#F59E0B)') }}">{{ $idx + 1 }}</div>
                    <div class="pi-top-info">
                        <div class="pi-top-title">{{ Str::limit($tPost->task->title ?? 'Untitled', 40) }}</div>
                        <div class="pi-top-meta">
                            <i class="{{ $tMeta['icon'] }}" style="color:{{ $tMeta['color'] }}"></i>
                            <span>{{ ucfirst($tPost->post_type) }}</span>
                            <span class="pi-top-type {{ $tp['is_paid'] ? 'pi-top-paid' : 'pi-top-organic' }}">{{ $tp['is_paid'] ? 'Paid' : 'Organic' }}</span>
                        </div>
                    </div>
                    <div class="pi-top-stats">
                        <div><strong>{{ number_format($tMetric->likes ?? 0) }}</strong><small>Likes</small></div>
                        <div><strong>{{ number_format($tMetric->comments ?? 0) }}</strong><small>Comments</small></div>
                        <div><strong>{{ number_format($tMetric->shares ?? 0) }}</strong><small>Shares</small></div>
                        <div><strong>{{ number_format($tMetric->reach ?? 0) }}</strong><small>Reach</small></div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    @endif
</div>

<style>
/* ===== POST INSIGHTS PANEL ===== */
.pi-panel{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:20px;position:relative;overflow:hidden}
.pi-panel::before{content:'';position:absolute;top:-40%;right:-15%;width:280px;height:280px;background:radial-gradient(circle,rgba(139,92,246,.06) 0%,transparent 70%);border-radius:50%;pointer-events:none}

/* Header */
.pi-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px}
.pi-header-left{display:flex;align-items:center;gap:12px}
.pi-icon-wrap{width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#8B5CF6,#A78BFA);color:#fff;display:flex;align-items:center;justify-content:center;font-size:17px;box-shadow:0 4px 14px rgba(139,92,246,.3)}
.pi-title{font-size:17px;font-weight:800;color:var(--text);font-family:'Plus Jakarta Sans',sans-serif;margin:0;letter-spacing:-.2px}
.pi-subtitle{font-size:11.5px;color:var(--text3);font-weight:500;margin-top:2px}
.pi-header-badges{display:flex;gap:6px}
.pi-badge{font-size:11px;font-weight:700;padding:5px 10px;border-radius:8px;display:inline-flex;align-items:center;gap:5px}
.pi-badge-organic{background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.2)}
.pi-badge-paid{background:rgba(245,158,11,.1);color:#D97706;border:1px solid rgba(245,158,11,.2)}

/* Empty State */
.pi-empty{text-align:center;padding:32px 20px}
.pi-empty-icon{width:56px;height:56px;border-radius:14px;margin:0 auto 12px;background:linear-gradient(135deg,rgba(139,92,246,.1),rgba(139,92,246,.04));display:flex;align-items:center;justify-content:center;font-size:22px;color:#8B5CF6}
.pi-empty-title{font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px}
.pi-empty-desc{font-size:12px;color:var(--text3)}

/* Split Bar */
.pi-split-section{margin-bottom:20px}
.pi-split-bar{display:flex;height:32px;border-radius:10px;overflow:hidden;background:var(--bg)}
.pi-split-organic{background:linear-gradient(90deg,#10B981,#34D399);display:flex;align-items:center;justify-content:center;min-width:32px;transition:width .6s ease}
.pi-split-organic span{color:#fff;font-size:11px;font-weight:800}
.pi-split-paid{background:linear-gradient(90deg,#F59E0B,#FBBF24);display:flex;align-items:center;justify-content:center;min-width:32px;transition:width .6s ease}
.pi-split-paid span{color:#fff;font-size:11px;font-weight:800}
.pi-split-legend{display:flex;align-items:center;gap:14px;margin-top:8px;font-size:11px;color:var(--text2);font-weight:600}
.pi-dot{display:inline-block;width:8px;height:8px;border-radius:3px;margin-right:4px}

/* KPI Cards */
.pi-kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:20px}
.pi-kpi{display:flex;align-items:center;gap:12px;padding:16px;border-radius:14px;background:var(--bg);border:1px solid var(--border);position:relative;overflow:hidden;transition:all .25s ease}
.pi-kpi::before{content:'';position:absolute;left:0;top:0;bottom:0;width:3px;background:var(--kc);border-radius:14px 0 0 14px}
.pi-kpi:hover{transform:translateY(-2px);box-shadow:0 8px 24px color-mix(in srgb,var(--kc) 12%,transparent);border-color:color-mix(in srgb,var(--kc) 25%,transparent)}
.pi-kpi-icon{width:38px;height:38px;border-radius:10px;background:color-mix(in srgb,var(--kc) 12%,transparent);color:var(--kc);display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.pi-kpi-body{flex:1;min-width:0}
.pi-kpi-val{font-size:20px;font-weight:800;color:var(--text);font-family:'Plus Jakarta Sans',sans-serif;line-height:1.1}
.pi-kpi-label{font-size:10.5px;color:var(--text3);font-weight:600;margin-top:2px;text-transform:uppercase;letter-spacing:.3px}
.pi-kpi-split{display:flex;flex-direction:column;gap:2px;font-size:10px;font-weight:700;text-align:right}
.pi-kpi-org{color:#059669}
.pi-kpi-org::before{content:'●';margin-right:3px;font-size:6px}
.pi-kpi-pd{color:#D97706}
.pi-kpi-pd::before{content:'●';margin-right:3px;font-size:6px}

/* Comparison Columns */
.pi-comparison{display:grid;grid-template-columns:1fr auto 1fr;gap:0;margin-bottom:20px;background:var(--bg);border-radius:14px;border:1px solid var(--border);overflow:hidden}
.pi-comp-col{padding:18px 20px}
.pi-comp-organic{background:linear-gradient(135deg,rgba(16,185,129,.04),transparent)}
.pi-comp-paid{background:linear-gradient(135deg,transparent,rgba(245,158,11,.04))}
.pi-comp-header{font-size:14px;font-weight:800;margin-bottom:14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.pi-comp-organic .pi-comp-header{color:#059669}
.pi-comp-paid .pi-comp-header{color:#D97706}
.pi-comp-count{font-size:10px;font-weight:700;padding:2px 8px;border-radius:5px;background:rgba(0,0,0,.05)}
.pi-comp-metrics{display:flex;flex-direction:column;gap:8px}
.pi-comp-row{display:flex;justify-content:space-between;align-items:center;font-size:12.5px;color:var(--text2);padding:4px 0}
.pi-comp-row strong{font-weight:800;color:var(--text);font-family:'Plus Jakarta Sans',sans-serif}
.pi-comp-divider{display:flex;align-items:center;justify-content:center;width:1px;background:var(--border);position:relative}
.pi-comp-vs{position:absolute;width:32px;height:32px;border-radius:50%;background:var(--card);border:2px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:900;color:var(--text3);letter-spacing:.5px}
.pi-comp-spend{margin-top:12px;padding:10px 12px;background:rgba(245,158,11,.08);border-radius:8px;font-size:11.5px;color:#D97706;font-weight:600;display:flex;align-items:center;gap:6px}
.pi-comp-spend strong{font-weight:800}

/* Section Titles */
.pi-section-title{font-size:13px;font-weight:800;color:var(--text);margin-bottom:12px;display:flex;align-items:center;gap:8px}
.pi-section-title i{color:var(--primary);font-size:14px}

/* Platform Breakdown */
.pi-platforms{margin-bottom:20px}
.pi-platform-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:10px}
.pi-platform-card{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:14px 16px;transition:all .2s ease;border-left:3px solid var(--plc)}
.pi-platform-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.06)}
.pi-platform-head{display:flex;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap}
.pi-platform-name{font-size:13px;font-weight:700;color:var(--text)}
.pi-platform-tags{display:flex;gap:4px;margin-left:auto}
.pi-ptag{font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px}
.pi-ptag-org{background:rgba(16,185,129,.1);color:#059669}
.pi-ptag-pd{background:rgba(245,158,11,.1);color:#D97706}
.pi-platform-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:6px}
.pi-platform-stats div{text-align:center;padding:6px 4px;background:var(--card);border-radius:8px}
.pi-platform-stats span{display:block;font-size:14px;font-weight:800;color:var(--text);font-family:'Plus Jakarta Sans',sans-serif}
.pi-platform-stats small{font-size:9px;color:var(--text3);font-weight:600;text-transform:uppercase;letter-spacing:.3px}

/* Top Posts */
.pi-top-posts{margin-bottom:4px}
.pi-top-row{display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg);border:1px solid var(--border);border-radius:12px;margin-bottom:8px;transition:all .2s ease}
.pi-top-row:hover{transform:translateX(4px);border-color:color-mix(in srgb,var(--primary) 25%,var(--border))}
.pi-top-rank{width:28px;height:28px;border-radius:8px;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;flex-shrink:0}
.pi-top-info{flex:1;min-width:0}
.pi-top-title{font-size:13px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pi-top-meta{display:flex;align-items:center;gap:6px;font-size:11px;color:var(--text3);margin-top:3px}
.pi-top-type{font-size:9px;font-weight:800;padding:2px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:.3px}
.pi-top-organic{background:rgba(16,185,129,.1);color:#059669}
.pi-top-paid{background:rgba(245,158,11,.1);color:#D97706}
.pi-top-stats{display:flex;gap:14px;flex-shrink:0}
.pi-top-stats div{text-align:center}
.pi-top-stats strong{display:block;font-size:13px;font-weight:800;color:var(--text);font-family:'Plus Jakarta Sans',sans-serif}
.pi-top-stats small{font-size:9px;color:var(--text3);font-weight:600}

/* Responsive */
@media(max-width:768px){
    .pi-panel{padding:16px;border-radius:12px}
    .pi-header{flex-direction:column;align-items:flex-start}
    .pi-kpi-grid{grid-template-columns:1fr 1fr}
    .pi-comparison{grid-template-columns:1fr;gap:0}
    .pi-comp-divider{width:100%;height:1px;background:var(--border)}
    .pi-comp-vs{top:-14px;left:50%;transform:translateX(-50%)}
    .pi-platform-grid{grid-template-columns:1fr}
    .pi-top-row{flex-wrap:wrap;gap:8px}
    .pi-top-stats{width:100%;justify-content:space-around;padding-top:8px;border-top:1px solid var(--border)}
}
@media(max-width:480px){
    .pi-kpi-grid{grid-template-columns:1fr}
    .pi-top-stats{gap:8px}
    .pi-platform-stats{grid-template-columns:repeat(2,1fr)}
}
</style>
