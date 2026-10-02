@extends('layouts.app')

@push('styles')
<style>
/* ── Social Analysis Detailed ────────────────────────── */
.sa-wrapper { animation: fadeIn 0.6s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

.sa-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 32px;
    background: var(--card);
    padding: 24px 32px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
}

.sa-title-info h1 {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    margin: 0;
}

.sa-title-info p {
    font-size: 14px;
    color: var(--text3);
    margin: 4px 0 0;
}

/* Filters */
.sa-filters-bar {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    margin-bottom: 24px;
    display: flex;
    gap: 16px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.sa-filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.sa-filter-group label {
    font-size: 11px;
    font-weight: 700;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sa-filter-input {
    background: var(--card2);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    min-width: 160px;
}

/* Stats Cards */
.sa-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.sa-stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.sa-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.sa-stat-info .label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text3);
}

.sa-stat-info .value {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
}

/* Main Content Grid - Changed to vertical stack for better visibility */
.sa-main-grid {
    display: flex;
    flex-direction: column;
    gap: 24px;
    max-width: 1000px; /* Constrain width to prevent going too far right */
    margin-right: auto;
}

.sa-charts-stack {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.sa-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    box-shadow: var(--shadow-sm);
    width: 100%; /* Ensure it takes full available width but not more */
}

.sa-card-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sa-chart-container {
    height: 400px;
    position: relative;
    width: 100%;
}

.sa-pie-container {
    height: 300px;
    position: relative;
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
}

.sa-slider-wrap {
    margin-top: 30px;
    background: var(--card2);
    padding: 20px;
    border-radius: 12px;
}

.sa-wrapper {
    padding-right: 24px; /* Added margin/padding to the right */
}

.sa-custom-slider {
    -webkit-appearance: none;
    width: 100%;
    height: 6px;
    background: var(--border2);
    border-radius: 10px;
    outline: none;
}

.sa-custom-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    background: var(--primary);
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 0 10px rgba(var(--primary-rgb), 0.3);
}

.sa-location-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: var(--card2);
    border-radius: 10px;
    margin-bottom: 8px;
    font-size: 14px;
}

@media(max-width: 1024px) {
    .sa-main-grid { grid-template-columns: 1fr; }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="sa-wrapper">
    <div class="sa-header">
        <div class="sa-title-info">
            <h1>Social Media Analysis</h1>
            <p>Comprehensive performance metrics and insights for your social presence</p>
        </div>
        <div class="sa-header-actions">
            <button onclick="window.print()" class="btn-sec" style="font-size:12px">
                <i class="fa-solid fa-download"></i> Export PDF
            </button>
        </div>
    </div>

    {{-- Filters Bar --}}
    <div class="sa-filters-bar">
        <div class="sa-filter-group">
            <label>Date Range Filter</label>
            <div style="display:flex; gap:8px">
                <input type="date" class="sa-filter-input" id="startDate" value="{{ now()->subMonth()->format('Y-m-d') }}">
                <input type="date" class="sa-filter-input" id="endDate" value="{{ now()->format('Y-m-d') }}">
            </div>
        </div>
        <div class="sa-filter-group">
            <label>Platform</label>
            <select class="sa-filter-input" id="platformFilter">
                <option value="all">All Platforms</option>
                @foreach($availablePlatforms as $p)
                    <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div class="sa-filter-group">
            <label>Specific Account</label>
            <select class="sa-filter-input" id="accountFilter">
                <option value="all">All Accounts</option>
                @foreach($availableAccounts as $acc)
                    @php($accLabel = data_get($acc, 'label', 'Main Account'))
                    @php($accPlatform = data_get($acc, 'platform', 'unknown'))
                    <option value="{{ $accLabel }}" data-platform="{{ $accPlatform }}">{{ $accLabel }} ({{ ucfirst($accPlatform) }})</option>
                @endforeach
            </select>
        </div>
        <div class="sa-filter-group">
            <label>Content Type</label>
            <select class="sa-filter-input" id="typeFilter">
                <option value="all">All Types</option>
                @foreach($availableTypes as $t)
                    <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="sa-filter-group">
            <label>Promotion</label>
            <select class="sa-filter-input" id="promotionFilter">
                <option value="all">All Types</option>
                <option value="organic">Organic Only</option>
                <option value="paid">Paid Only</option>
            </select>
        </div>
        <div class="sa-filter-group" style="flex:1; min-width: 200px;">
            <label>Post Title</label>
            <select class="sa-filter-input" id="titleFilter" style="width:100%">
                <option value="all">All Titles</option>
                @foreach($availableTitles as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-primary" style="padding: 10px 24px; font-size: 13px" onclick="applyFilters()">
            Apply Filters
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="sa-stats-grid">
        <div class="sa-stat-card">
            <div class="sa-stat-icon" style="background:var(--teal-dim); color:var(--teal)">
                <i class="fa-solid fa-indian-rupee-sign"></i>
            </div>
            <div class="sa-stat-info">
                <div class="label">Total Ad Spend</div>
                <div class="value" id="statAdSpend">₹{{ number_format($totalAdSpend, 2) }}</div>
            </div>
        </div>
        <div class="sa-stat-card">
            <div class="sa-stat-icon" style="background:var(--blue-dim); color:var(--blue)">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="sa-stat-info">
                <div class="label">Total Reach</div>
                <div class="value" id="statReach">{{ number_format($timelineData->sum('reach')) }}</div>
            </div>
        </div>
        <div class="sa-stat-card">
            <div class="sa-stat-icon" style="background:var(--purple-dim); color:var(--purple)">
                <i class="fa-solid fa-eye"></i>
            </div>
            <div class="sa-stat-info">
                <div class="label">Impressions</div>
                <div class="value" id="statImpressions">{{ number_format($timelineData->sum('impressions')) }}</div>
            </div>
        </div>
        <div class="sa-stat-card">
            <div class="sa-stat-icon" style="background:var(--pink-dim); color:var(--pink)">
                <i class="fa-solid fa-heart"></i>
            </div>
            <div class="sa-stat-info">
                <div class="label">Total Likes</div>
                <div class="value" id="statLikes">{{ number_format($timelineData->sum('likes')) }}</div>
            </div>
        </div>
    </div>

    <div class="sa-main-grid">
        {{-- Performance Trends --}}
        <div class="sa-card">
            <div class="sa-card-title">
                <i class="fa-solid fa-chart-area" style="color:var(--primary)"></i>
                Performance Trends Over Time
            </div>
            <div class="sa-chart-container">
                <canvas id="detailedBarChart"></canvas>
            </div>
            <div class="sa-slider-wrap">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px">
                    <span style="font-size:13px; font-weight:700; color:var(--text)">Timeline Focus</span>
                    <span id="sliderRangeLabel" style="font-size:12px; font-weight:600; color:var(--primary)">All History</span>
                </div>
                <input type="range" min="0" max="{{ max(0, $timelineData->count() - 1) }}" value="0" class="sa-custom-slider" id="dateRangeSlider">
                <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text3); font-weight:700; margin-top:8px">
                    <span>OLDEST DATA</span>
                    <span>DRAG TO FOCUS ON RECENT SNAPSHOTS</span>
                    <span>LATEST DATA</span>
                </div>
            </div>
        </div>

        {{-- Promotion Mix & Locations --}}
        <div style="display:flex; flex-direction:column; gap:24px">
            {{-- Promotion Distribution --}}
            <div class="sa-card">
                <div class="sa-card-title">Promotion Mix</div>
                <div class="sa-pie-container" style="max-width: 400px; margin: 0 auto;">
                    <canvas id="promotionPieChart"></canvas>
                </div>
                <div style="display:flex; justify-content:center; gap:20px; margin-top:20px">
                    <div style="text-align:center; padding:12px 30px; background:rgba(16,185,129,0.06); border-radius:10px">
                        <div id="organicCountValue" style="font-size:24px; font-weight:800; color:#10B981">{{ $organicCount }}</div>
                        <div style="font-size:11px; font-weight:700; color:#10B981; text-transform:uppercase">Organic</div>
                    </div>
                    <div style="text-align:center; padding:12px 30px; background:rgba(59,130,246,0.06); border-radius:10px">
                        <div id="paidCountValue" style="font-size:24px; font-weight:800; color:#3B82F6">{{ $inorganicCount }}</div>
                        <div style="font-size:11px; font-weight:700; color:#3B82F6; text-transform:uppercase">Paid</div>
                    </div>
                </div>
            </div>

            <div class="sa-card">
                <div class="sa-card-title">Top Target Areas</div>
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:12px">
                    @foreach($locations as $loc => $count)
                    <div class="sa-location-item" style="margin-bottom:0">
                        <span style="font-weight:600; color:var(--text2)">{{ $loc }}</span>
                        <span style="background:var(--primary-dim); color:var(--primary); padding:2px 10px; border-radius:6px; font-weight:700; font-size:12px">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Post List Section --}}
            <div class="sa-card">
                <div class="sa-card-title">
                    <i class="fa-solid fa-list-ul"></i>
                    Post Details
                </div>
                <div style="overflow-x: auto">
                    <table style="width:100%; border-collapse:collapse; font-size:13px">
                        <thead>
                            <tr style="text-align:left; border-bottom:1px solid var(--border)">
                                <th style="padding:12px">Date</th>
                                <th style="padding:12px">Title</th>
                                <th style="padding:12px">Platform</th>
                                <th style="padding:12px">Type</th>
                                <th style="padding:12px">Reach</th>
                                <th style="padding:12px">Likes</th>
                            </tr>
                        </thead>
                        <tbody id="postListBody">
                            {{-- Data from JS --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const allMetrics = @json($rawMetrics);
    let myChart;
    let promotionPieChart;
    let filteredMetrics = [...allMetrics];

    function formatNumber(value) {
        return Number(value || 0).toLocaleString();
    }

    function formatCurrency(value) {
        return `₹${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function updateSummaryCards(data) {
        const totals = data.reduce((carry, row) => {
            carry.reach += Number(row.reach || 0);
            carry.impressions += Number(row.impressions || 0);
            carry.likes += Number(row.likes || 0);
            carry.adSpend += Number(row.ad_spend_inr || 0);
            if (row.paid) {
                carry.paid += 1;
            } else {
                carry.organic += 1;
            }
            return carry;
        }, {
            reach: 0,
            impressions: 0,
            likes: 0,
            adSpend: 0,
            organic: 0,
            paid: 0,
        });

        document.getElementById('statAdSpend').textContent = formatCurrency(totals.adSpend);
        document.getElementById('statReach').textContent = formatNumber(totals.reach);
        document.getElementById('statImpressions').textContent = formatNumber(totals.impressions);
        document.getElementById('statLikes').textContent = formatNumber(totals.likes);
        document.getElementById('organicCountValue').textContent = formatNumber(totals.organic);
        document.getElementById('paidCountValue').textContent = formatNumber(totals.paid);

        if (promotionPieChart) {
            promotionPieChart.data.datasets[0].data = [totals.organic, totals.paid];
            promotionPieChart.update();
        }
    }

    function renderPostList(data) {
        const body = document.getElementById('postListBody');
        if (!body) return;

        if (data.length === 0) {
            body.innerHTML = '<tr><td colspan="6" style="padding:24px; text-align:center; color:var(--text3)">No data matches the selected filters.</td></tr>';
            return;
        }

        // Only show latest 20 for performance in list
        const displayData = data.slice(-20).reverse();

        body.innerHTML = displayData.map(d => `
            <tr style="border-bottom:1px solid var(--card2)">
                <td style="padding:12px; white-space:nowrap">${d.display_date}</td>
                <td style="padding:12px"><strong>${d.post_title}</strong></td>
                <td style="padding:12px"><span style="text-transform:capitalize">${d.platform}</span> <br> <small style="color:var(--text3)">${d.account}</small></td>
                <td style="padding:12px"><span style="background:var(--card2); padding:2px 6px; border-radius:4px; font-size:11px">${d.post_type}</span></td>
                <td style="padding:12px">${d.reach.toLocaleString()}</td>
                <td style="padding:12px">${d.likes.toLocaleString()}</td>
            </tr>
        `).join('');
    }

    function initChart(data) {
        if (myChart) myChart.destroy();

        // Group data by date for the chart
        const grouped = data.reduce((acc, curr) => {
            if (!acc[curr.date]) {
                acc[curr.date] = { date: curr.display_date, reach: 0, views: 0, likes: 0, impressions: 0 };
            }
            acc[curr.date].reach += curr.reach;
            acc[curr.date].views += curr.views;
            acc[curr.date].likes += curr.likes;
            acc[curr.date].impressions += curr.impressions;
            return acc;
        }, {});

        const chartData = Object.values(grouped);

        const ctx = document.getElementById('detailedBarChart').getContext('2d');
        myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [
                    {
                        label: 'Reach',
                        data: chartData.map(d => d.reach),
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderRadius: 5,
                    },
                    {
                        label: 'Impressions',
                        data: chartData.map(d => d.impressions),
                        backgroundColor: 'rgba(139, 92, 246, 0.7)',
                        borderRadius: 5,
                    },
                    {
                        label: 'Views',
                        data: chartData.map(d => d.views),
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderRadius: 5,
                    },
                    {
                        label: 'Likes',
                        data: chartData.map(d => d.likes),
                        backgroundColor: 'rgba(236, 72, 153, 0.7)',
                        borderRadius: 5,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, font: { weight: '600' } } }
                }
            }
        });
    }

    function applyFilters() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const platform = document.getElementById('platformFilter').value;
        const account = document.getElementById('accountFilter').value;
        const type = document.getElementById('typeFilter').value;
        const title = document.getElementById('titleFilter').value;
        const promo = document.getElementById('promotionFilter').value;

        filteredMetrics = allMetrics.filter(m => {
            const dateMatch = (!start || m.date >= start) && (!end || m.date <= end);
            const platformMatch = platform === 'all' || m.platform === platform;
            const accountMatch = account === 'all' || m.account === account;
            const typeMatch = type === 'all' || m.post_type === type;
            const titleMatch = title === 'all' || m.post_title === title;
            const promoMatch = promo === 'all' ||
                               (promo === 'organic' && !m.paid) ||
                               (promo === 'paid' && m.paid);

            return dateMatch && platformMatch && accountMatch && typeMatch && titleMatch && promoMatch;
        });

        initChart(filteredMetrics);
        renderPostList(filteredMetrics);
        updateSummaryCards(filteredMetrics);

        // Update range slider max based on filtered data
        const slider = document.getElementById('dateRangeSlider');
        if (slider) {
            slider.max = Math.max(0, filteredMetrics.length - 1);
            slider.value = 0;
        }

        ajax.showSuccess('Filters applied successfully');
    }

    // Pie Chart
    const ctxPie = document.getElementById('promotionPieChart').getContext('2d');
    promotionPieChart = new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Organic', 'Paid'],
            datasets: [{
                data: [{{ $organicCount }}, {{ $inorganicCount }}],
                backgroundColor: ['#10B981', '#3B82F6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Slider
    const slider = document.getElementById('dateRangeSlider');
    const label = document.getElementById('sliderRangeLabel');
    if (slider) {
        slider.addEventListener('input', function() {
            const val = parseInt(this.value);
            const filtered = filteredMetrics.slice(val);
            if (filtered.length > 0) {
                label.textContent = `Focusing from ${filtered[0].display_date} onwards`;
                initChart(filtered);
                renderPostList(filtered);
            }
        });
    }

    // Reactively filter account options when platform changes
    document.getElementById('platformFilter').addEventListener('change', function() {
        const platform = this.value;
        const accountFilter = document.getElementById('accountFilter');
        const options = accountFilter.querySelectorAll('option');

        accountFilter.value = 'all';
        options.forEach(opt => {
            if (opt.value === 'all') {
                opt.style.display = '';
            } else {
                const optPlatform = opt.getAttribute('data-platform');
                opt.style.display = (platform === 'all' || optPlatform === platform) ? '' : 'none';
            }
        });
    });

    // Initial Load
    initChart(allMetrics);
    renderPostList(allMetrics);
    updateSummaryCards(allMetrics);
</script>
@endpush
