@if(!request()->ajax())
@extends('layouts.app')

@section('content')
@endif
<div class="workload-container" style="padding: 30px;">
    <div class="workload-header" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div class="page-title" style="font-size: 28px; font-weight: 700; color: var(--text); margin-bottom: 6px;">Employee Workload</div>
            <div class="page-subtitle" style="font-size: 13px; color: var(--text2);">Balance work across your team – {{ $totalMembers }} members</div>
        </div>
        
        <!-- Search & Filter Bar -->
        <form method="GET" action="{{ route('admin.workload') }}" id="filter-form" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="position: relative; width: 280px;">
                <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text3); font-size: 14px;"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email..." 
                    style="width: 100%; padding: 10px 14px 10px 40px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; outline: none; transition: all 0.2s; background: white;"
                    onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 3px var(--primary)15'"
                    onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
            </div>

            <div style="position: relative;">
                <select name="role" onchange="applyWorkloadFilters()" 
                    style="padding: 10px 32px 10px 14px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 14px; outline: none; background: white; appearance: none; cursor: pointer; min-width: 150px;">
                    <option value="">All Roles</option>
                    @foreach(['admin' => 'Admin', 'strategist' => 'Strategist', 'designer' => 'Designer', 'developer' => 'Developer', 'manager' => 'Manager', 'editor' => 'Editor', 'content_writer' => 'Content Writer'] as $val => $lbl)
                        <option value="{{ $val }}" {{ request('role') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 11px; color: var(--text3); pointer-events: none;"></i>
            </div>

            @if(request()->filled('search') || request()->filled('role'))
                <a href="{{ route('admin.workload') }}" style="font-size: 13px; color: var(--red); text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 4px; padding: 10px 14px; background: var(--red-dim); border-radius: 10px; transition: all 0.2s;">
                    <i class="fas fa-times"></i> Clear
                </a>
            @endif

            <button type="submit" style="display: none;">Search</button>
        </form>
    </div>

    <div id="workload-ajax-area">
    @fragment('workload_content')


<!-- Stats Grid -->
<div class="workload-stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
    <div class="workload-stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary-dim); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 20px;">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <div style="font-size: 24px; font-weight: 700; color: var(--text);">{{ $totalMembers }}</div>
            <div style="font-size: 12px; color: var(--text2); font-weight: 500;">Team Members</div>
            <div style="font-size: 11px; color: var(--text3); margin-top: 2px;">People available for task assignment</div>
        </div>
    </div>
     <div class="workload-stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--teal-dim); display: flex; align-items: center; justify-content: center; color: var(--teal); font-size: 20px;">
            <i class="fas fa-bolt"></i>
        </div>
        <div>
            <div style="font-size: 24px; font-weight: 700; color: var(--text);">{{ $avgEfficiency }}%</div>
            <div style="font-size: 12px; color: var(--text2); font-weight: 500;">Avg Efficiency</div>
            <div style="font-size: 11px; color: var(--text3); margin-top: 2px;">Completed tasks ÷ total assigned tasks</div>
        </div>
    </div>
    
    <div class="workload-stat-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $overloadedCount > 0 ? 'var(--red-dim)' : 'var(--teal-dim)' }}; display: flex; align-items: center; justify-content: center; color: {{ $overloadedCount > 0 ? 'var(--red)' : 'var(--teal)' }}; font-size: 20px;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <div style="font-size: 24px; font-weight: 700; color: {{ $overloadedCount > 0 ? 'var(--red)' : 'var(--text)' }};">{{ $overloadedCount }}</div>
            <div style="font-size: 12px; color: {{ $overloadedCount > 0 ? 'var(--red)' : 'var(--teal)' }}; font-weight: 500;">{{ $overloadedCount > 0 ? 'Needs rebalancing' : 'All good!' }}</div>
            <div style="font-size: 11px; color: var(--text3); margin-top: 2px;">Members currently holding delayed tasks</div>
        </div>
    </div>
</div>

<!-- Mobile Employee Cards (visible only on mobile) -->
<div class="mobile-workload-cards" style="display: none;">
    @foreach($employees as $emp)
        @php
            $efficiencyColor = $emp->efficiency > 80 ? 'var(--teal)' : ($emp->efficiency > 50 ? 'var(--yellow)' : 'var(--red)');
            $roleClass = $emp->role === 'admin' ? 'tag-orange' : ($emp->role === 'strategist' ? 'tag-teal' : 'tag-purple');
            $roleColor = $emp->role === 'admin' ? '#F97316' : ($emp->role === 'strategist' ? '#14B8A6' : '#8B5CF6');
            $roleBg = $emp->role === 'admin' ? '#F9731618' : ($emp->role === 'strategist' ? '#14B8A618' : '#8B5CF618');
        @endphp
        <div class="mobile-workload-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <!-- Employee Header -->
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: {{ $emp->avatar_color ?? 'var(--primary)' }}; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; font-weight: 700; flex-shrink: 0;">
                    {{ $emp->initial }}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; color: var(--text); font-size: 15px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $emp->name }}</div>
                    <div style="font-size: 12px; color: var(--text3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $emp->email }}</div>
                </div>
                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: {{ $roleBg }}; color: {{ $roleColor }}; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: capitalize;">
                    {{ $emp->role }}
                </span>
            </div>
            
            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px;">
                <div style="text-align: center; padding: 12px; background: var(--card2); border-radius: 8px;">
                    <div style="font-size: 20px; font-weight: 700; color: var(--text);">{{ $emp->total_tasks }}</div>
                    <div style="font-size: 11px; color: var(--text2); font-weight: 500;">Total</div>
                </div>
                <div style="text-align: center; padding: 12px; background: var(--card2); border-radius: 8px;">
                    <div style="font-size: 20px; font-weight: 700; color: var(--blue);">{{ $emp->inprogress_tasks }}</div>
                    <div style="font-size: 11px; color: var(--text2); font-weight: 500;">In Progress</div>
                </div>
                <div style="text-align: center; padding: 12px; background: var(--card2); border-radius: 8px;">
                    <div style="font-size: 20px; font-weight: 700; color: {{ $emp->delayed_tasks > 0 ? 'var(--red)' : 'var(--teal)' }};">{{ $emp->delayed_tasks }}</div>
                    <div style="font-size: 11px; color: var(--text2); font-weight: 500;">Delayed</div>
                </div>
            </div>
            
            <!-- Efficiency Bar -->
            <div style="margin-bottom: 8px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 12px; color: var(--text2); font-weight: 500;">Efficiency</span>
                    <span style="font-size: 14px; font-weight: 700; color: {{ $efficiencyColor }};">{{ $emp->efficiency }}%</span>
                </div>
                <div style="height: 8px; background: var(--card2); border-radius: 99px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $emp->efficiency }}%; background: {{ $efficiencyColor }}; border-radius: 99px; transition: width 0.3s ease;"></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Desktop Table (hidden on mobile) -->
<div class="workload-table-container card" style="background: white; border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; margin-bottom: 16px;">
    <div class="table-wrap">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--card2); border-bottom: 1px solid var(--border);">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px;">Employee</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px;">Role</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px;">Total Tasks</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px;">In Progress</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px;">Delayed</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px; min-width: 360px;">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                                <span>Efficiency</span>
                                <span style="font-size: 10px; color: var(--text3); font-weight: 600; text-transform: none; letter-spacing: 0;">Guide: 0-40 Low, 41-70 Medium, 71-100 High</span>
                            </div>
                            <div style="width: 170px; display: flex; justify-content: space-between; font-size: 9px; color: var(--text3); font-weight: 600; text-transform: none; letter-spacing: 0;">
                                @for($i = 0; $i <= 100; $i += 10)
                                    <span>{{ $i }}</span>
                                @endfor
                            </div>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $emp)
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 9px;">
                                <div class="av-sm" style="background: {{ $emp->avatar_color ?? 'var(--primary)' }}; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 600;">{{ $emp->initial }}</div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text); font-size: 13px;">{{ $emp->name }}</div>
                                    <div style="font-size: 11px; color: var(--text3);">{{ $emp->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: {{ $emp->role === 'admin' ? '#F9731618' : ($emp->role === 'strategist' ? '#14B8A618' : '#8B5CF618') }}; color: {{ $emp->role === 'admin' ? '#F97316' : ($emp->role === 'strategist' ? '#14B8A6' : '#8B5CF6') }}; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: capitalize;">
                                {{ $emp->role }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px;">
                            <strong style="color: var(--text); font-size: 14px;">{{ $emp->total_tasks }}</strong>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: var(--blue-dim); color: var(--blue); border-radius: 6px; font-size: 11px; font-weight: 600;">
                                {{ $emp->inprogress_tasks }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px;">
                            @if($emp->delayed_tasks > 0)
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: var(--red-dim); color: var(--red); border-radius: 6px; font-size: 11px; font-weight: 600;">
                                    {{ $emp->delayed_tasks }}
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: var(--teal-dim); color: var(--teal); border-radius: 6px; font-size: 11px; font-weight: 600;">
                                    0
                                </span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 170px; height: 8px; background: var(--card2); border-radius: 99px; overflow: hidden; position: relative; background-image: repeating-linear-gradient(to right, transparent, transparent calc(10% - 1px), rgba(148,163,184,0.35) calc(10% - 1px), rgba(148,163,184,0.35) 10%);">
                                    <div style="height: 100%; width: {{ $emp->efficiency }}%; background: {{ $emp->efficiency > 80 ? 'var(--teal)' : ($emp->efficiency > 50 ? 'var(--yellow)' : 'var(--red)') }}; border-radius: 99px;"></div>
                                </div>
                                <span style="font-size: 12px; font-weight: 600; color: {{ $emp->efficiency > 80 ? 'var(--teal)' : ($emp->efficiency > 50 ? 'var(--yellow)' : 'var(--red)') }};">{{ $emp->efficiency }}%</span>
                                <span style="font-size: 11px; color: var(--text3); font-weight: 600; white-space: nowrap;">
                                    @if($emp->efficiency <= 40)
                                        Low (0-40)
                                    @elseif($emp->efficiency <= 70)
                                        Medium (41-70)
                                    @else
                                        High (71-100)
                                    @endif
                                </span>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Per Page + Pagination Controls -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 20px; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; background: white; border: 1px solid var(--border); border-radius: 8px;">
                <span style="font-size: 12px; color: var(--text3);">Show</span>
                @php $standardOptions = [10, 25, 50, 100]; @endphp
                <select id="per_page_select" onchange="if(this.value==='custom'){ document.getElementById('custom_per_page_wrap').style.display='flex'; } else { applyWorkloadFilters(); }"
                    style="padding: 4px 6px; border: 1px solid var(--border); border-radius: 5px; font-size: 12px; font-weight: 600; cursor: pointer; background: var(--card2);">
                    @foreach($standardOptions as $opt)
                        <option value="{{ $opt }}" {{ $perPageRaw == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                    <option value="custom" {{ $perPageRaw === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>

                <div id="custom_per_page_wrap" style="display: {{ $perPageRaw === 'custom' ? 'flex' : 'none' }}; align-items: center; gap: 5px; margin-left: 5px;">
                    <input type="number" id="per_page_custom_input" value="{{ $perPage }}" 
                        style="width: 55px; padding: 3px 6px; border: 1px solid var(--border); border-radius: 5px; font-size: 12px; font-weight: 700; text-align: center;"
                        onkeydown="if(event.key==='Enter'){ applyWorkloadCustomPerPage(); }">
                    <button type="button" onclick="applyWorkloadCustomPerPage()" 
                        style="padding: 3px 8px; background: var(--primary); color: white; border: none; border-radius: 5px; font-size: 11px; cursor: pointer; font-weight: 700;">Apply</button>
                </div>
                <span style="font-size: 12px; color: var(--text3);">per page</span>
            </div>
            
            <div style="font-size: 12px; color: var(--text3); font-weight: 600;">
                Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }} members
            </div>
        </div>

        <div class="workload-pagination">
            {{ $employees->links('vendor.pagination.custom') }}
        </div>
    @endfragment
    </div> <!-- /#workload-ajax-area -->
</div>

@push('styles')
<style>
/* ═════════════════════════════════════════════════════════════
   MOBILE RESPONSIVE STYLES FOR WORKLOAD PAGE
   ═════════════════════════════════════════════════════════════ */

/* Mobile Layout */
@media (max-width: 768px) {
    .workload-container {
        padding: 16px !important;
    }
    
    .workload-header {
        margin-bottom: 20px !important;
    }
    
    .workload-header .page-title {
        font-size: 22px !important;
    }
    
    /* Stats Grid - Stack on mobile */
    .workload-stats-grid {
        grid-template-columns: 1fr !important;
        gap: 12px !important;
    }
    
    .workload-stat-card {
        padding: 16px !important;
    }
    
    .workload-stat-card > div:first-child {
        width: 40px !important;
        height: 40px !important;
        font-size: 18px !important;
    }
    
    .workload-stat-card > div:last-child > div:first-child {
         font-size: 20px !important;
    }
    
    /* Hide desktop table, show mobile cards */
    .workload-table-container {
        display: none !important;
    }
    
    .mobile-workload-cards {
        display: block !important;
    }
    
    /* Mobile card hover effect */
    .mobile-workload-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.08) !important;
        border-color: var(--primary) !important;
    }
}

/* Smaller mobile screens */
@media (max-width: 480px) {
    .workload-container {
        padding: 12px !important;
    }
    
    .workload-header .page-title {
        font-size: 20px !important;
    }
    
    .mobile-workload-card {
        padding: 12px !important;
    }
    
    .mobile-workload-card > div:first-child > div:first-child {
        width: 40px !important;
        height: 40px !important;
        font-size: 14px !important;
    }
}

/* Desktop - hide mobile elements */
@media (min-width: 769px) {
    .mobile-workload-cards {
        display: none !important;
    }
    
    .workload-table-container {
        display: block !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    let ajaxController = null;
    let searchTimeout = null;

    document.addEventListener('DOMContentLoaded', () => {
        initWorkloadAjax();
    });

    function initWorkloadAjax() {
        const form = document.getElementById('filter-form');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            applyWorkloadFilters();
        });

        const searchInput = form.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => applyWorkloadFilters(), 250);
            });
        }

        initWorkloadLinkInterception();
    }

    function initWorkloadLinkInterception() {
        const area = document.getElementById('workload-ajax-area');
        if (!area || area.dataset.intercepted === 'true') return;
        area.dataset.intercepted = 'true';

        area.addEventListener('click', (e) => {
            const link = e.target.closest('.workload-pagination a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#')) return;

            e.preventDefault();
            loadWorkloadFromUrl(href);
        });
    }

    window.applyWorkloadFilters = function() {
        const form = document.getElementById('filter-form');
        const formData = new FormData(form);
        const perPageSelect = document.getElementById('per_page_select');
        
        if (perPageSelect) {
            formData.set('per_page', perPageSelect.value);
            if (perPageSelect.value === 'custom') {
                const customVal = document.getElementById('per_page_custom_input')?.value;
                if (customVal) formData.set('per_page_custom', customVal);
            }
        }

        const params = new URLSearchParams(formData);
        params.delete('page'); // Reset to page 1 on filter change

        const url = form.action + (params.toString() ? '?' + params.toString() : '');
        loadWorkloadFromUrl(url);
    };

    window.applyWorkloadCustomPerPage = function() {
        applyWorkloadFilters();
    };

    async function loadWorkloadFromUrl(url) {
        if (ajaxController) ajaxController.abort();
        ajaxController = new AbortController();

        const area = document.getElementById('workload-ajax-area');
        if (!area) return;

        // Overlay loader
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.7);display:flex;align-items:center;justify-content:center;z-index:9999;transition:opacity 0.2s;';
        overlay.innerHTML = '<div style="background:white;padding:16px 32px;border:1px solid var(--border);border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.1);font-weight:600;display:flex;align-items:center;gap:12px;"><i class="fas fa-circle-notch fa-spin" style="color:var(--primary);font-size:18px;"></i> Updating...</div>';
        document.body.appendChild(overlay);

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: ajaxController.signal
            });

            if (!response.ok) throw new Error('Network error');
            const data = await response.json();
            
            // Update the content area
            if (data.html) area.innerHTML = data.html;

            // Update the header subtitle
            const sub = document.querySelector('.workload-header .page-subtitle');
            if (sub && data.totalMembers !== undefined) {
                sub.innerHTML = `Balance work across your team – ${data.totalMembers} members`;
            }

            window.history.pushState(null, '', url);
            
            // Re-intercept links in the new content
            const freshArea = document.getElementById('workload-ajax-area');
            if (freshArea) freshArea.dataset.intercepted = 'false';
            initWorkloadLinkInterception();

        } catch (e) {
            if (e.name !== 'AbortError') {
                console.error(e);
                window.location.href = url;
            }
        } finally {
            overlay.remove();
        }
    }
})();
</script>
@endpush

@if(!request()->ajax())
@endsection
@endif
