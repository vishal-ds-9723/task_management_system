@extends('layouts.app')

@section('content')
<div class="audit-container" style="padding: 30px;">
    @php
        $hasFilters = $search || $user_id || $action_type || $date_from || $date_to;
    @endphp
    
    <!-- Page Header -->
    <div class="audit-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="margin: 0; font-size: 28px; color: var(--text);">
                <i class="fas fa-history" style="margin-right: 10px; color: var(--primary);"></i>Audit Logs
            </h1>
            <p style="margin: 5px 0 0 0; color: var(--text2); font-size: 13px;">
                Complete tracking of all admin activities • {{ $logs->total() }} entries
            </p>
        </div>
        <a href="{{ route('admin.tasks') }}" class="audit-btn-secondary"
            style="padding: 10px 16px; background: var(--card2); border: 1px solid var(--border); border-radius: 8px; text-decoration: none; color: var(--text); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-weight: 500;">
            <i class="fas fa-arrow-left"></i><span class="btn-text">Back to Tasks</span>
        </a>
    </div>

    <!-- Filter Section -->
    <form method="GET" action="{{ route('admin.audit-logs') }}" class="audit-filter-form" style="margin-bottom: 25px;">
        
        <!-- Mobile Filter Toggle -->
        <div class="mobile-filter-toggle" style="display: none; margin-bottom: 12px;">
            <button type="button" onclick="toggleAuditMobileFilters()" class="audit-btn-secondary" style="width: 100%; padding: 12px; background: var(--card2); border: 1.5px solid var(--border); border-radius: 10px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <i class="fas fa-filter"></i>
                <span>Filters</span>
                @if($hasFilters)
                    <span style="background: var(--primary); color: white; padding: 2px 8px; border-radius: 99px; font-size: 11px;">Active</span>
                @endif
            </button>
        </div>
        
        <div class="filters-container" id="auditFiltersContainer">
            <div class="filter-row audit-filter-row" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 12px; margin-bottom: 15px;">
                <div>
                    <input type="text" name="search" placeholder="Search action or task..." value="{{ $search }}"
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <select name="user_id" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
                        <option value="">All Users</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $user_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="action_type" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
                        <option value="">All Actions</option>
                        @foreach($actionTypes as $key => $label)
                            <option value="{{ $key }}" {{ $action_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" name="date_from" value="{{ $date_from }}"
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
                </div>
                <div>
                    <input type="date" name="date_to" value="{{ $date_to }}"
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 14px;">
                </div>
                <button type="submit" class="filter-submit-btn"
                    style="padding: 12px 16px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                    <i class="fas fa-filter" style="margin-right: 5px;"></i><span class="btn-text">Filter</span>
                </button>
            </div>
        </div>

        <!-- Per Page Selector & Clear -->
        <div class="audit-controls" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <label style="color: var(--text2); font-size: 13px;">Per page:</label>
            <select name="per_page" onchange="this.form.submit()"
                style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px;">
                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
            </select>

            @if($hasFilters)
                <a href="{{ route('admin.audit-logs') }}"
                    style="margin-left: auto; padding: 8px 12px; background: var(--card2); border: 1px solid var(--border); border-radius: 6px; text-decoration: none; color: var(--text); font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-weight: 500;">
                    <i class="fas fa-redo"></i><span class="btn-text">Clear Filters</span>
                </a>
            @endif
        </div>
    </form>

    <!-- Mobile Audit Log Cards (visible only on mobile) -->
    <div class="mobile-audit-cards" style="display: none;">
        @forelse($logs as $log)
            @php
                $actionIcon = 'fa-edit';
                $actionColor = 'var(--blue)';
                $actionBg = 'var(--blue-dim)';
                if (str_contains($log->action, 'Created')) {
                    $actionIcon = 'fa-plus-circle';
                    $actionColor = 'var(--teal)';
                    $actionBg = 'var(--teal-dim)';
                } elseif (str_contains($log->action, 'Deleted')) {
                    $actionIcon = 'fa-trash';
                    $actionColor = 'var(--red)';
                    $actionBg = 'var(--red-dim)';
                } elseif (str_contains($log->action, 'Cloned')) {
                    $actionIcon = 'fa-clone';
                    $actionColor = 'var(--purple)';
                    $actionBg = 'var(--purple-dim)';
                } elseif (str_contains($log->action, 'status')) {
                    $actionIcon = 'fa-exchange-alt';
                    $actionColor = 'var(--yellow)';
                    $actionBg = 'var(--yellow-dim)';
                }
            @endphp
            <div class="mobile-audit-card" style="background: white; border: 1px solid var(--border); border-radius: 12px; padding: 16px; margin-bottom: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $actionBg }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas {{ $actionIcon }}" style="color: {{ $actionColor }}; font-size: 16px;"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-weight: 600; color: var(--text); font-size: 14px; margin-bottom: 4px;">{{ $log->action }}</div>
                        <div style="font-size: 12px; color: var(--text3);">
                            {{ $log->created_at->format('M d, Y') }} at {{ $log->created_at->format('H:i:s') }}
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding: 10px; background: var(--card2); border-radius: 8px;">
                    @if($log->user)
                        <div class="av-sm" style="background: {{ $log->user->avatar_color ?? '#3B82F6' }}; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; color: white; border-radius: 50%; font-weight: 600; font-size: 11px; flex-shrink: 0;">
                            {{ $log->user->initial ?? 'A' }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 500; color: var(--text); font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $log->user->name }}</div>
                            <div style="font-size: 11px; color: var(--text3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $log->user->email }}</div>
                        </div>
                    @else
                        <span style="color: var(--text3); font-size: 13px;">System</span>
                    @endif
                </div>
                
                @if($log->task)
                    <div style="margin-bottom: 12px;">
                        <a href="{{ route('admin.tasks.edit', $log->task) }}" style="color: var(--primary); text-decoration: none; font-weight: 500; font-size: 13px; display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-external-link-alt" style="font-size: 11px;"></i>
                            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $log->task->title ?? 'Untitled' }}</span>
                        </a>
                    </div>
                @endif
                
                <div style="display: flex; gap: 8px;">
                    <button onclick="showDetails({{ $log->id }})" class="audit-btn-primary"
                        style="flex: 1; padding: 10px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <i class="fas fa-info-circle"></i> View Details
                    </button>
                </div>
            </div>
        @empty
            <div style="padding: 40px 20px; text-align: center; color: var(--text3); background: white; border: 1px solid var(--border); border-radius: 12px;">
                <i class="fas fa-inbox" style="font-size: 36px; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px; color: var(--text2);">No audit logs found</div>
                <div style="font-size: 13px;">Try adjusting your filters</div>
                @if($hasFilters)
                    <a href="{{ route('admin.audit-logs') }}" style="display: inline-block; margin-top: 12px; padding: 10px 20px; background: var(--primary); color: white; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500;">Clear Filters</a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Audit Logs Table (desktop only) -->
    <div class="audit-table-container" style="background: white; border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: var(--card2); border-bottom: 1px solid var(--border);">
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px; width: 15%;">User</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px; width: 35%;">Action</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px; width: 20%;">Task</th>
                    <th style="padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px; width: 18%;">Date & Time</th>
                    <th style="padding: 12px 16px; text-align: center; font-weight: 600; font-size: 12px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.5px; width: 12%;">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $index => $log)
                    <tr style="border-bottom: 1px solid var(--border); background: {{ $index % 2 === 0 ? 'white' : 'var(--card2)' }};">
                        <td style="padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                @if($log->user)
                                    <div class="av-sm" style="background: {{ $log->user->avatar_color ?? '#3B82F6' }}; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; color: white; border-radius: 50%; font-weight: 600; font-size: 12px;">
                                        {{ $log->user->initial ?? 'A' }}
                                    </div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-weight: 500; color: var(--text);">{{ $log->user->name }}</span>
                                        <span style="font-size: 11px; color: var(--text3);">{{ $log->user->email }}</span>
                                    </div>
                                @else
                                    <span style="color: var(--text3);">System</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 12px 16px;">
                            @php
                                $actionIcon = 'fa-edit';
                                $actionColor = 'var(--blue)';
                                if (str_contains($log->action, 'Created')) {
                                    $actionIcon = 'fa-plus-circle';
                                    $actionColor = 'var(--teal)';
                                } elseif (str_contains($log->action, 'Deleted')) {
                                    $actionIcon = 'fa-trash';
                                    $actionColor = 'var(--red)';
                                } elseif (str_contains($log->action, 'Cloned')) {
                                    $actionIcon = 'fa-clone';
                                    $actionColor = 'var(--purple)';
                                } elseif (str_contains($log->action, 'status')) {
                                    $actionIcon = 'fa-exchange-alt';
                                    $actionColor = 'var(--yellow)';
                                }
                            @endphp
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="fas {{ $actionIcon }}" style="color: {{ $actionColor }}; font-size: 16px;"></i>
                                <span style="color: var(--text); font-weight: 500;">{{ $log->action }}</span>
                            </div>
                        </td>
                        <td style="padding: 12px 16px; color: var(--text2); font-size: 13px;">
                            @if($log->task)
                                <a href="{{ route('admin.tasks.edit', $log->task) }}" style="color: var(--primary); text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                                    <i class="fas fa-external-link-alt" style="font-size: 11px;"></i>
                                    {{ $log->task->title ?? 'Untitled' }}
                                </a>
                            @else
                                <span style="color: var(--text3);">—</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; color: var(--text2); font-size: 13px;">
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 500;">{{ $log->created_at->format('M d, Y') }}</span>
                                <span style="font-size: 11px; color: var(--text3);">{{ $log->created_at->format('H:i:s') }}</span>
                            </div>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <button onclick="showDetails({{ $log->id }})"
                                style="padding: 6px 10px; background: var(--primary); color: white; border: none; border-radius: 4px; text-decoration: none; cursor: pointer; font-size: 12px;">
                                <i class="fas fa-info-circle"></i> View
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding: 40px; text-align: center; color: var(--text3);">
                            <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                            No audit logs found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 20px; display: flex; align-items: center; justify-content: space-between;">
        <div style="color: var(--text2); font-size: 13px;">
            Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
        </div>
        <div style="display: flex; gap: 5px;">
            {{ $logs->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: var(--radius); max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; padding: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 id="detailsTitle" style="margin: 0; font-size: 20px; color: var(--text);">Action Details</h2>
            <button onclick="closeDetails()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text3);">×</button>
        </div>

        <div id="detailsContent" style="color: var(--text2); font-size: 13px; line-height: 1.8;">
            Loading...
        </div>
    </div>
</div>

<script>
    function showDetails(logId) {
        // Fetch audit log details
        fetch(`/admin/audit-logs/${logId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        }).then(r => r.json()).then(data => {
            const modal = document.getElementById('detailsModal');
            const title = document.getElementById('detailsTitle');
            const content = document.getElementById('detailsContent');

            title.textContent = data.action;

            let html = `
                <div style="display: grid; gap: 15px;">
                    <div style="padding: 12px; background: var(--card2); border-radius: 8px;">
                        <p style="margin: 0 0 5px 0; color: var(--text3); font-weight: 500;">User</p>
                        <p style="margin: 0; color: var(--text);">${data.user_name || 'System'} (${data.user_email || 'N/A'})</p>
                    </div>
                    <div style="padding: 12px; background: var(--card2); border-radius: 8px;">
                        <p style="margin: 0 0 5px 0; color: var(--text3); font-weight: 500;">IP Address</p>
                        <p style="margin: 0; color: var(--text); font-family: monospace;">${data.ip_address || 'Unknown'}</p>
                    </div>
                    <div style="padding: 12px; background: var(--card2); border-radius: 8px;">
                        <p style="margin: 0 0 5px 0; color: var(--text3); font-weight: 500;">Timestamp</p>
                        <p style="margin: 0; color: var(--text);">${data.timestamp}</p>
                    </div>
            `;

            if (data.old_values) {
                html += `
                    <div style="padding: 12px; background: var(--card2); border-radius: 8px;">
                        <p style="margin: 0 0 5px 0; color: var(--text3); font-weight: 500;">Previous Values</p>
                        <pre style="margin: 0; color: var(--text); font-size: 12px; overflow-x: auto;">${JSON.stringify(data.old_values, null, 2)}</pre>
                    </div>
                `;
            }

            if (data.new_values) {
                html += `
                    <div style="padding: 12px; background: var(--card2); border-radius: 8px;">
                        <p style="margin: 0 0 5px 0; color: var(--text3); font-weight: 500;">New Values</p>
                        <pre style="margin: 0; color: var(--text); font-size: 12px; overflow-x: auto;">${JSON.stringify(data.new_values, null, 2)}</pre>
                    </div>
                `;
            }

            html += `</div>`;

            content.innerHTML = html;
            modal.style.display = 'flex';
        }).catch(e => {
            alert('Failed to load details');
            console.error(e);
        });
    }

    function closeDetails() {
        document.getElementById('detailsModal').style.display = 'none';
    }

    // Close modal on background click
    document.getElementById('detailsModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'detailsModal') closeDetails();
    });
</script>

@push('styles')
<style>
/* ═════════════════════════════════════════════════════════════
   MOBILE RESPONSIVE STYLES FOR AUDIT LOGS PAGE
   ═════════════════════════════════════════════════════════════ */

/* Mobile Header Adjustments */
@media (max-width: 768px) {
    .audit-container {
        padding: 16px !important;
    }
    
    .audit-header {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 16px !important;
    }
    
    .audit-header h1 {
        font-size: 22px !important;
    }
    
    .audit-btn-secondary .btn-text {
        display: none !important;
    }
    
    .audit-btn-secondary {
        padding: 10px 12px !important;
    }
    
    /* Mobile Filter Toggle */
    .mobile-filter-toggle {
        display: block !important;
    }
    
    .filters-container {
        display: none !important;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .filters-container.show {
        display: block !important;
    }
    
    .audit-filter-row {
        grid-template-columns: 1fr 1fr !important;
        gap: 12px !important;
    }
    
    .audit-filter-row > div:first-child {
        grid-column: 1 / -1 !important;
    }
    
    .filter-submit-btn {
        grid-column: 1 / -1 !important;
    }
    
    .audit-controls {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
    }
    
    .audit-controls > a {
        margin-left: 0 !important;
    }
    
    /* Hide desktop table, show mobile cards */
    .audit-table-container {
        display: none !important;
    }
    
    .mobile-audit-cards {
        display: block !important;
    }
    
    /* Mobile audit card hover effect */
    .mobile-audit-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.08) !important;
        border-color: var(--primary) !important;
    }
    
    /* Modal mobile adjustments */
    #detailsModal > div {
        width: 95% !important;
        max-height: 85vh !important;
        padding: 20px !important;
    }
    
    /* Pagination mobile */
    .pagination {
        flex-wrap: wrap !important;
        justify-content: center !important;
    }
}

/* Smaller mobile screens */
@media (max-width: 480px) {
    .audit-container {
        padding: 12px !important;
    }
    
    .audit-header h1 {
        font-size: 20px !important;
    }
    
    .audit-filter-row {
        grid-template-columns: 1fr !important;
    }
    
    .mobile-audit-card {
        padding: 12px !important;
    }
}

/* Desktop - hide mobile elements */
@media (min-width: 769px) {
    .mobile-filter-toggle {
        display: none !important;
    }
    
    .mobile-audit-cards {
        display: none !important;
    }
    
    .filters-container {
        display: block !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
/**
 * ═════════════════════════════════════════════════════════════
 * MOBILE FILTER TOGGLE FOR AUDIT LOGS
 * ═════════════════════════════════════════════════════════════
 */
function toggleAuditMobileFilters() {
    const container = document.getElementById('auditFiltersContainer');
    if (container) {
        container.classList.toggle('show');
    }
}
</script>
@endpush

@endsection
