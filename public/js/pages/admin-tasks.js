    let draggedTaskId = null;
    const statusMap = {
        'kanban-col-todo': 'todo',
        'kanban-col-inprogress': 'inprogress',
        'kanban-col-review': 'review',
        'kanban-col-pending_approval': 'pending_approval',
        'kanban-col-completed': 'completed',
        'kanban-col-published': 'published'
    };

    function viewMode(mode) {
        const tableView = document.getElementById('table-view');
        const kanbanView = document.getElementById('kanban-view');
        const btnTable = document.getElementById('btn-table');
        const btnKanban = document.getElementById('btn-kanban');
        const tableOnlyControls = document.getElementById('table-only-controls');

        if (mode === 'table') {
            tableView.style.display = 'block';
            kanbanView.style.display = 'none';
            if (tableOnlyControls) tableOnlyControls.style.display = 'flex';
            btnTable.style.background = 'var(--primary)';
            btnTable.style.color = 'white';
            btnTable.style.borderColor = 'var(--primary)';
            btnKanban.style.background = 'var(--card2)';
            btnKanban.style.color = 'var(--text)';
            btnKanban.style.borderColor = 'var(--border)';
            localStorage.setItem('admin_view_mode', 'table');
        } else {
            tableView.style.display = 'none';
            kanbanView.style.display = 'block';
            if (tableOnlyControls) tableOnlyControls.style.display = 'none';
            btnKanban.style.background = 'var(--primary)';
            btnKanban.style.color = 'white';
            btnKanban.style.borderColor = 'var(--primary)';
            btnTable.style.background = 'var(--card2)';
            btnTable.style.color = 'var(--text)';
            btnTable.style.borderColor = 'var(--border)';
            localStorage.setItem('admin_view_mode', 'kanban');
        }
    }

    function selectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.task-select');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateBulkBar();
    }

    function updateBulkBar() {
        const selected = document.querySelectorAll('.task-select:checked');
        const bar = document.getElementById('bulk-bar');
        const count = document.getElementById('bulk-count');
        if (selected.length > 0) {
            bar.style.display = 'flex';
            count.textContent = selected.length + ' task' + (selected.length > 1 ? 's' : '') + ' selected';
        } else {
            bar.style.display = 'none';
        }
    }

    function clearSelection() {
        document.querySelectorAll('.task-select, #select-all').forEach(cb => cb.checked = false);
        updateBulkBar();
    }

    function bulkUpdate() {
        const status = document.getElementById('bulk-status').value;
        if (!status) { alert('Please select a status.'); return; }
        const ids = [...document.querySelectorAll('.task-select:checked')].map(cb => cb.value);
        if (!ids.length) return;
        fetch('/admin/tasks/bulk/update-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: JSON.stringify({ ids, status })
        }).then(r => r.json()).then(d => { if (d.success) location.reload(); });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.task-select').forEach(cb => {
            cb.addEventListener('change', updateBulkBar);
        });
    });

    function dragStart(event, taskId) {
        draggedTaskId = taskId;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', taskId);
        event.target.style.opacity = '0.5';
    }

    function dragEnd(event) {
        draggedTaskId = null;
        event.target.style.opacity = '1';
    }

    function dragOver(event) {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }

    function dragLeave(event) {
        event.target.style.background = '';
    }

    function drop(event, colId) {
        event.preventDefault();

        if (!draggedTaskId) return;

        const newStatus = statusMap[colId];
        if (!newStatus) return;

        // Find the task element and get its current status
        const taskCard = document.querySelector(`[draggable="true"][data-task-id="${draggedTaskId}"]`);
        if (!taskCard) return;

        // Send AJAX request to update status
        fetch(`/admin/tasks/${draggedTaskId}/kanban-status`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                status: newStatus
            })
        }).then(response => {
            if (!response.ok) {
                throw new Error('Failed to update task status');
            }
            return response.json();
        }).then(data => {
            // Update the UI
            if (data.success) {
                // Move the task card (and its <a> wrapper if present) to the new column
                const targetCol = document.getElementById(colId);
                if (targetCol) {
                    const cardWrapper = taskCard.closest('a') || taskCard;
                    targetCol.appendChild(cardWrapper);
                }
                // Show success message (optional)
                console.log('Task moved successfully to ' + newStatus);
            }
        }).catch(error => {
            console.error('Error:', error);
            alert('Failed to move task. Please try again.');
        });
    }

    // Make kanban columns droppable
    function initKanbanDragDrop() {
        document.querySelectorAll('.kanban-col').forEach(col => {
            col.addEventListener('dragover', dragOver);
            col.addEventListener('drop', (e) => drop(e, 'kanban-col-' + e.currentTarget.dataset.status));
            col.addEventListener('dragleave', dragLeave);
        });
    }

    // Restore view mode on page load
    document.addEventListener('DOMContentLoaded', () => {
        const viewMode_ = localStorage.getItem('admin_view_mode') || 'table';
        viewMode(viewMode_);

        // Initialize kanban drag-drop
        setTimeout(initKanbanDragDrop, 100);
    });

    // Jump to specific page number
    function jumpToPage(page) {
        page = parseInt(page);
        if (isNaN(page) || page < 1) return;
        const url = new URL(window.location.href);
        url.searchParams.set('page', page);
        if (typeof loadTasksFromUrl === 'function') {
            loadTasksFromUrl(url.toString(), true);
        } else {
            window.location.href = url.toString();
        }
    }

/**
 * ═════════════════════════════════════════════════════════════
 * MOBILE FILTER TOGGLE
 * ═════════════════════════════════════════════════════════════
 */
function toggleMobileFilters() {
    const container = document.getElementById('filtersContainer');
    if (container) {
        container.classList.toggle('show');
    }
}

/**
 * ═════════════════════════════════════════════════════════════
 * ADMIN TASKS AJAX - Filter, Paginate, Sort Without Page Refresh
 * ═════════════════════════════════════════════════════════════
 */

(function() {
    let ajaxController = null; // AbortController for cancelling in-flight requests
    let searchTimeout = null;

    document.addEventListener('DOMContentLoaded', function() {
        initTasksAjax();
    });

    // Re-init after browser back/forward
    window.addEventListener('popstate', function() {
        loadTasksFromUrl(window.location.href);
    });

    function initTasksAjax() {
        const area = document.getElementById('tasks-ajax-area');
        if (!area) return;

        initAdvancedToggle();
        initFilterListeners();
        initLinkInterception();
    }

    /**
     * Advanced Filters Toggle (mobile)
     */
    function initAdvancedToggle() {
        const btn = document.getElementById('advancedToggleBtn');
        const panel = document.getElementById('advancedFiltersPanel');
        if (!btn || !panel) return;

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const isExpanded = panel.style.display !== 'none';
            panel.style.display = isExpanded ? 'none' : 'block';

            const chevron = btn.querySelector('.fa-chevron-down');
            if (chevron) chevron.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
            btn.style.color = isExpanded ? 'var(--text3)' : 'var(--primary)';
        });

        // Auto-expand if advanced filters are active
        if (window.AdminTasksData.hasAdvancedFilters) {
            panel.style.display = 'block';
            btn.style.color = 'var(--primary)';
            const ch = btn.querySelector('.fa-chevron-down');
            if (ch) ch.style.transform = 'rotate(180deg)';
        }
    }

    /**
     * Listen to filter changes and trigger AJAX
     */
    function initFilterListeners() {
        const form = document.getElementById('filter-form');
        if (!form) return;

        // Prevent normal form submit — use AJAX instead
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });

        // Select/checkbox change → immediate AJAX
        form.querySelectorAll('select, input[type="checkbox"]').forEach(el => {
            el.addEventListener('change', function() {
                // per_page change also triggers AJAX
                applyFilters();
            });
        });

        // Search input -> debounced AJAX without showing loader flicker
        const searchInput = form.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => applyFilters(), 250);
            });
        }

        // Date inputs → AJAX on change
        form.querySelectorAll('input[type="date"]').forEach(el => {
            el.addEventListener('change', () => applyFilters());
        });
    }

    /**
     * Intercept clicks on links inside the ajax area (pills, pagination, clear all, etc.)
     */
    function initLinkInterception() {
        const area = document.getElementById('tasks-ajax-area');
        if (!area || area.dataset.linkInterceptInit) return;
        area.dataset.linkInterceptInit = 'true';

        area.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            // Skip external links, javascript:, #, and export links
            if (link.dataset.noAjax || !href || href.startsWith('#') || href.startsWith('javascript:') || href.includes('export/csv') || href.includes('export-csv')) return;

            // Only intercept links that point to the tasks list itself (filter pills, clear filters, pagination, sort)
            // Task detail (/admin/tasks/{id}), task edit, client show, etc. must navigate normally!
            try {
                const linkUrl = new URL(link.href, window.location.origin);
                const baseUrl = new URL(window.AdminTasksData?.tasksBaseUrl || '/admin/tasks', window.location.origin);

                // If path is not exactly /admin/tasks (e.g. /admin/tasks/18 or /admin/clients/1), do not intercept
                if (linkUrl.pathname.replace(/\/+$/, '') !== baseUrl.pathname.replace(/\/+$/, '')) {
                    return;
                }
            } catch (_) {
                return;
            }

            // This is an internal filter/pagination link — handle via AJAX
            e.preventDefault();
            loadTasksFromUrl(href);
        });
    }

    /**
     * Build URL from current form state and load via AJAX
     */
    function applyFilters() {
        const form = document.getElementById('filter-form');
        if (!form) return;

        const formData = new FormData(form);
        // Remove page param when filters change (reset to page 1)
        formData.delete('page');
        const params = new URLSearchParams(formData);
        // Clean empty params
        for (const [key, val] of [...params.entries()]) {
            if (!val) params.delete(key);
        }
        const url = form.action + (params.toString() ? '?' + params.toString() : '');
        loadTasksFromUrl(url, false, { preserveSearchFocus: true });
    }

    /**
     * Core AJAX: fetch URL and swap content
     * @param {string} url - The URL to fetch
     * @param {boolean} isPagination - true shows full overlay, false shows inline indicator
     */
    async function loadTasksFromUrl(url, isPagination = true, options = {}) {
        const opts = {
            preserveSearchFocus: false,
            ...options,
        };

        let previousSearchValue = '';
        let previousSelectionStart = null;
        let previousSelectionEnd = null;

        if (opts.preserveSearchFocus) {
            const currentSearchInput = document.querySelector('#filter-form input[name="search"]');
            if (currentSearchInput) {
                previousSearchValue = currentSearchInput.value;
                previousSelectionStart = currentSearchInput.selectionStart;
                previousSelectionEnd = currentSearchInput.selectionEnd;
            }
        }

        // Cancel any in-flight request
        if (ajaxController) ajaxController.abort();
        ajaxController = new AbortController();

        const area = document.getElementById('tasks-ajax-area');
        if (!area) return;

        // Show loading state only for pagination/full navigation.
        if (isPagination) {
            showOverlayLoader();
        }

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                signal: ajaxController.signal,
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const html = await response.text();
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            // Swap the entire ajax area content
            const newArea = newDoc.getElementById('tasks-ajax-area');
            if (newArea) {
                area.innerHTML = newArea.innerHTML;
            }

            // Update the page header subtitle
            const oldSub = document.querySelector('.tasks-container > .tasks-header p');
            const newSub = newDoc.querySelector('.tasks-container > .tasks-header p');
            if (oldSub && newSub) oldSub.innerHTML = newSub.innerHTML;

            // Update stats cards
            const oldStats = document.querySelector('.task-stats-grid');
            const newStats = newDoc.querySelector('.task-stats-grid');
            if (oldStats && newStats) oldStats.innerHTML = newStats.innerHTML;

            // Update recent panels
            const oldRecent = document.querySelector('.recent-panels-grid');
            const newRecent = newDoc.querySelector('.recent-panels-grid');
            if (oldRecent && newRecent) oldRecent.innerHTML = newRecent.innerHTML;

            // Update URL
            window.history.pushState(null, '', url);

            // Re-initialize everything in the new content
            initAdvancedToggle();
            initFilterListeners();
            initLinkInterception();

            // Restore view mode
            const viewMode_ = localStorage.getItem('admin_view_mode') || 'table';
            if (typeof viewMode === 'function') viewMode(viewMode_);

            // Re-init kanban drag drop
            if (typeof initKanbanDragDrop === 'function') setTimeout(initKanbanDragDrop, 100);

            // Re-init bulk checkboxes
            document.querySelectorAll('.task-select').forEach(cb => {
                cb.addEventListener('change', updateBulkBar);
            });

            if (opts.preserveSearchFocus) {
                const refreshedSearchInput = document.querySelector('#filter-form input[name="search"]');
                if (refreshedSearchInput) {
                    refreshedSearchInput.focus({ preventScroll: true });

                    if (typeof previousSelectionStart === 'number' && typeof previousSelectionEnd === 'number') {
                        const maxLength = refreshedSearchInput.value.length;
                        const safeStart = Math.min(previousSelectionStart, maxLength);
                        const safeEnd = Math.min(previousSelectionEnd, maxLength);
                        refreshedSearchInput.setSelectionRange(safeStart, safeEnd);
                    } else {
                        const cursorAtEnd = previousSearchValue.length;
                        refreshedSearchInput.setSelectionRange(cursorAtEnd, cursorAtEnd);
                    }
                }
            }

            // Scroll to top of content on pagination
            if (isPagination) {
                area.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

        } catch (error) {
            if (error.name === 'AbortError') return; // Request was cancelled by a newer one
            console.error('Tasks AJAX error:', error);
            // Fallback: do a normal navigation
            window.location.href = url;
        } finally {
            hideOverlayLoader();
        }
    }

    /**
     * Inline loader — subtle opacity + small spinner bar on the content area
     */
    function showInlineLoader(area) {
        area.style.opacity = '0.5';
        area.style.pointerEvents = 'none';
        area.style.transition = 'opacity 0.15s';

        // Add a thin progress bar at top of area
        let bar = document.getElementById('tasks-inline-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'tasks-inline-bar';
            bar.style.cssText = `
                position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 999;
                background: linear-gradient(90deg, var(--primary), #667eea, var(--primary));
                background-size: 200% 100%;
                animation: ajaxBarSlide 1s ease-in-out infinite;
            `;
            document.body.appendChild(bar);
        }
    }

    function hideInlineLoader(area) {
        if (area) {
            area.style.opacity = '';
            area.style.pointerEvents = '';
            area.style.transition = '';
        }
        const bar = document.getElementById('tasks-inline-bar');
        if (bar) bar.remove();
    }

    /**
     * Full overlay loader — for pagination changes
     */
    function showOverlayLoader() {
        if (document.getElementById('tasksLoadingOverlay')) return;
        const overlay = document.createElement('div');
        overlay.id = 'tasksLoadingOverlay';
        overlay.style.cssText = `
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.6); display: flex; align-items: center;
            justify-content: center; z-index: 998; backdrop-filter: blur(2px);
        `;
        overlay.innerHTML = `
            <div style="background:white; padding:18px 36px; border-radius:14px; box-shadow:0 8px 32px rgba(0,0,0,0.12); font-size:14px; font-weight:600; display:flex; align-items:center; gap:12px;">
                <i class="fas fa-spinner fa-spin" style="font-size:16px;color:var(--primary)"></i> Loading tasks...
            </div>
        `;
        document.body.appendChild(overlay);
    }

    function hideOverlayLoader() {
        const overlay = document.getElementById('tasksLoadingOverlay');
        if (overlay) {
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.2s';
            setTimeout(() => overlay.remove(), 200);
        }
    }

    // Expose for external use (jumpToPage, etc.)
    window.loadTasksFromUrl = loadTasksFromUrl;
    window.applyTasksFilter = applyFilters;

    /**
     * Custom Per Page logic
     */
    window.applyCustomPerPage = function() {
        const customInput = document.getElementById('per_page_custom_input');
        const select = document.getElementById('per_page_select');
        if(!customInput || !select) return;
        
        const val = parseInt(customInput.value);
        if(isNaN(val) || val < 1) {
            alert('Please enter a valid number');
            return;
        }

        // Set the value on the select hidden option or just trigger the filter with the value
        // Easiest way is to just call loadTasksFromUrl with updated param
        const form = document.getElementById('filter-form');
        const formData = new FormData(form);
        formData.set('per_page', val); // Override per_page with custom value
        formData.delete('page');
        
        const params = new URLSearchParams(formData);
        for (const [key, val] of [...params.entries()]) {
            if (!val) params.delete(key);
        }
        const url = form.action + (params.toString() ? '?' + params.toString() : '');
        loadTasksFromUrl(url, false, { preserveSearchFocus: true });
    };

    window.jumpToPage = function(page) {
        if(!page) return;
        const form = document.getElementById('filter-form');
        const formData = new FormData(form);
        
        // Handle per_page custom if selected
        const select = document.getElementById('per_page_select');
        if(select && select.value === 'custom') {
            const customVal = document.getElementById('per_page_custom_input')?.value;
            if(customVal) formData.set('per_page', customVal);
        }

        formData.set('page', page);
        const params = new URLSearchParams(formData);
        for (const [key, val] of [...params.entries()]) {
            if (!val) params.delete(key);
        }
        const url = form.action + (params.toString() ? '?' + params.toString() : '');
        loadTasksFromUrl(url, true);
    };

    /**
     * Intercept stat card and recent panel "View All" links
     * These are outside the ajax area but should still load via AJAX
     */
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.task-stat-card, .recent-panels-grid > div > div:first-child a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

        // Only handle links to the tasks page
        const tasksBaseUrl = window.AdminTasksData.tasksBaseUrl;
        if (!href.startsWith(tasksBaseUrl)) return;

        e.preventDefault();
        loadTasksFromUrl(href, true);
    });
})();

