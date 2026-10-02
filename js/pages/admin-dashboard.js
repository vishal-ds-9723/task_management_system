// Live clock update
setInterval(()=>{
    const now = new Date();
    const h = now.getHours() % 12 || 12;
    const m = String(now.getMinutes()).padStart(2,'0');
    const el = document.getElementById('ghClockTime');
    const ap = document.getElementById('ghClockAmpm');
    if(el) el.textContent = h+':'+m;
    if(ap) ap.textContent = now.getHours() >= 12 ? 'PM' : 'AM';
}, 30000);

function showMetricTasks(metric) {
    const modal = document.getElementById('metricModal');
    const loader = document.getElementById('metricModalLoader');
    const table = document.getElementById('metricModalTable');
    const tbody = document.getElementById('metricModalTableBody');
    const empty = document.getElementById('metricModalEmpty');
    const title = document.getElementById('metricModalTitle');

    const metricLabels = {
        'total': 'Total Tasks',
        'inprogress': 'In Progress Tasks',
        'review': 'Pending Review Tasks',
        'completed_week': 'Completed This Week',
        'overdue': 'Overdue Tasks',
        'todo': 'To Do Tasks',
        'unassigned': 'Unassigned Tasks'
    };

    title.textContent = metricLabels[metric] || 'Tasks';
    loader.style.display = 'flex';
    table.style.display = 'none';
    empty.style.display = 'none';
    modal.style.display = 'flex';

    // Build query params dynamically
    const urlParams = new URLSearchParams();
    urlParams.append('metric', metric);

    const clientId = new URLSearchParams(window.location.search).get('client_id');
    const assignedTo = new URLSearchParams(window.location.search).get('assigned_to');
    const status = new URLSearchParams(window.location.search).get('status');
    const priority = new URLSearchParams(window.location.search).get('priority');
    const platform = new URLSearchParams(window.location.search).get('platform');
    const dateRange = new URLSearchParams(window.location.search).get('date_range');

    if (clientId) urlParams.append('client_id', clientId);
    if (assignedTo) urlParams.append('assigned_to', assignedTo);
    if (status) urlParams.append('status', status);
    if (priority) urlParams.append('priority', priority);
    if (platform) urlParams.append('platform', platform);
    if (dateRange) urlParams.append('date_range', dateRange);

    const apiUrl = `/api/dashboard/metric-tasks?${urlParams.toString()}`;

    // DEBUG: Log the complete request
    console.log('🔵 METRIC TASKS REQUEST');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('Metric:', metric);
    console.log('Dashboard Filters from URL:');
    console.log('  - client_id:', clientId || '(none)');
    console.log('  - assigned_to:', assignedTo || '(none)');
    console.log('  - status:', status || '(none - will use metric status)');
    console.log('  - priority:', priority || '(none)');
    console.log('  - platform:', platform || '(none)');
    console.log('  - date_range:', dateRange || '(none)');
    console.log('Full API URL:', apiUrl);

    // Special debug for overdue metric
    if (metric === 'overdue') {
        console.log('⚠️ OVERDUE METRIC - Time comparison:');
        console.log('  Dashboard count uses: now() =', new Date().toISOString());
        console.log('  API query uses: now() (current time, NOT startOfDay)');
        console.log('  If counts differ, check tasks with deadline between startOfDay and now');
    }
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                console.error('❌ HTTP Error:', response.status, response.statusText);
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ API RESPONSE RECEIVED');
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('Total tasks returned:', data.count || data.tasks?.length);
            if (metric === 'overdue') {
                console.log('📊 OVERDUE TASKS COMPARISON:');
                console.log('  Dashboard metric card showed: (check metric card in UI)');
                console.log('  API returned:', data.count || data.tasks?.length);
                console.log('  If different, dashboard used now() and API now uses now() too');
                if (data.tasks?.length > 0) {
                    console.log('  Sample overdue task:', {
                        title: data.tasks[0].title,
                        deadline: data.tasks[0].deadline,
                        status: data.tasks[0].status,
                        client: data.tasks[0].client
                    });
                }
            }
            console.log('Sample task:', data.tasks?.[0]);
            console.log('Full response:', data);
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

            tbody.innerHTML = '';

            if (!data.tasks || data.tasks.length === 0) {
                loader.style.display = 'none';
                empty.style.display = 'block';
                console.warn('⚠️ No tasks returned from API');
                return;
            }

            data.tasks.forEach((task, idx) => {
                const row = document.createElement('tr');
                const statusClass = `status-badge ${task.status}`;

                // Handle platform (could be array or string)
                let platformDisplay = '—';
                if (task.platform) {
                    if (Array.isArray(task.platform)) {
                        platformDisplay = task.platform.map(p => p.charAt(0).toUpperCase() + p.slice(1)).join(', ');
                    } else if (typeof task.platform === 'string') {
                        platformDisplay = task.platform.charAt(0).toUpperCase() + task.platform.slice(1);
                    }
                }

                // Handle type display
                const typeDisplay = task.type ? task.type.charAt(0).toUpperCase() + task.type.slice(1) : '—';
                const statusDisplay = task.status ? task.status.charAt(0).toUpperCase() + task.status.slice(1).replace(/_/g, ' ') : '—';

                row.innerHTML = `
                    <td>${idx + 1}</td>
                    <td><a href="${task.url}" target="_blank">${task.title}</a></td>
                    <td>${task.client_logo ? `<img src="/storage/${task.client_logo}" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-top:-2px">` : (task.client_emoji || '')} ${task.client || '—'}</td>
                    <td>${typeDisplay}</td>
                    <td>${platformDisplay}</td>
                    <td><span class="${statusClass}">${statusDisplay}</span></td>
                    <td>${task.assigned_to || '—'}</td>
                    <td>${task.deadline || '—'}</td>
                `;
                tbody.appendChild(row);
            });

            loader.style.display = 'none';
            table.style.display = 'table';
            console.log('✅ Rendered', data.tasks.length, 'tasks in table');
        })
        .catch(error => {
            console.error('❌ FETCH ERROR:', error);
            console.error('Error message:', error.message);
            console.error('Stack:', error.stack);
            loader.style.display = 'none';
            empty.style.display = 'block';
            empty.textContent = `Error loading tasks: ${error.message}`;
        });
}

function closeMetricModal() {
    document.getElementById('metricModal').style.display = 'none';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMetricModal();
    }
});

function toggleRejectForm(id){
    const el=document.getElementById('rejectForm-'+id);
    if(el) el.style.display = el.style.display==='none'?'block':'none';
}

/**
 * ═══════════════════════════════════════════════════════════
 * DASHBOARD AJAX - Filter Without Page Refresh
 * ═══════════════════════════════════════════════════════════
 */

let dashboardFilterDebounce = null;
let activeDashboardRequest = null;

function animateStatsNumbers() {
    document.querySelectorAll('.mcs-val').forEach(el => {
        const target = parseInt(el.getAttribute('data-target') || el.textContent, 10);
        if (isNaN(target)) return;
        
        if (!el.getAttribute('data-target')) {
            el.setAttribute('data-target', target);
        }
        
        let start = 0;
        const duration = 1000;
        const startTime = performance.now();
        
        function updateNumber(currentTime) {
            const elapsedTime = currentTime - startTime;
            if (elapsedTime >= duration) {
                el.textContent = target;
                return;
            }
            
            const progress = elapsedTime / duration;
            const easeProgress = progress * (2 - progress);
            const currentVal = Math.round(start + (target - start) * easeProgress);
            
            el.textContent = currentVal;
            requestAnimationFrame(updateNumber);
        }
        
        requestAnimationFrame(updateNumber);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const dashboardFilters = document.getElementById('dashboardFilters');
    
    // Trigger count-up animation on initial page load
    animateStatsNumbers();
    
    if (!dashboardFilters) return;

    // Intercept all select changes in the filter form
    const selects = dashboardFilters.querySelectorAll('select');
    selects.forEach(select => {
        select.addEventListener('change', function(e) {
            e.preventDefault();
            if (dashboardFilterDebounce) clearTimeout(dashboardFilterDebounce);
            dashboardFilterDebounce = setTimeout(() => applyDashboardFilterAJAX(), 250);
        });
    });
});

/**
 * Apply dashboard filters via AJAX without page refresh
 */
async function applyDashboardFilterAJAX() {
    const form = document.getElementById('dashboardFilters');
    if (!form) return;

    if (activeDashboardRequest) {
        activeDashboardRequest.abort();
    }
    activeDashboardRequest = new AbortController();
    const { signal } = activeDashboardRequest;

    const formData = new FormData(form);
    const queryString = new URLSearchParams(formData).toString();
    const url = `${form.action}${form.action.includes('?') ? '&' : '?'}${queryString}`;

    try {
        // Show loading on the metrics area
        const metricsArea = document.querySelector('.db-metrics');
        if (metricsArea) {
            metricsArea.style.opacity = '0.6';
            metricsArea.style.pointerEvents = 'none';
        }

        // Make AJAX request
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
            signal,
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        // Parse the response HTML
        const html = await response.text();
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');

        // Update main components smartly
        const selectors = [
            '#dbFilterSummary',
            '.db-welcome-strip',
            '.db-metrics',
            '.db-command-grid',
        ];
        selectors.forEach(selector => updateDashboardComponent(selector, newDoc));

        // Update URL without page reload
        window.history.pushState({ path: url }, '', url);

        // Re-attach event listeners to metric cards
        attachMetricCardListeners();

        // Re-trigger counter animation for mobile stats
        animateStatsNumbers();

        // Show success
        ajax.showSuccess('Filters applied successfully!');

    } catch (error) {
        if (error.name === 'AbortError') return;
        console.error('Dashboard filter error:', error);
        ajax.showError('Failed to apply filters. Please try again.');
    } finally {
        // Restore opacity
        const metricsArea = document.querySelector('.db-metrics');
        if (metricsArea) {
            metricsArea.style.opacity = '1';
            metricsArea.style.pointerEvents = 'auto';
        }
    }
}

/**
 * Update a specific dashboard component
 */
function updateDashboardComponent(selector, newDoc) {
    const currentElements = document.querySelectorAll(selector);
    const newElements = newDoc.querySelectorAll(selector);

    if (!currentElements.length || !newElements.length) return;

    currentElements.forEach((currentElement, index) => {
        const newElement = newElements[index];
        if (!newElement) return;

        currentElement.style.transition = 'opacity 0.3s ease';
        currentElement.style.opacity = '0.5';

        setTimeout(() => {
            currentElement.innerHTML = newElement.innerHTML;
            currentElement.style.opacity = '1';
        }, 150);
    });
}

/**
 * Re-attach event listeners to metric cards after AJAX update
 */
function attachMetricCardListeners() {
    const metricCards = document.querySelectorAll('.db-metric-card');
    metricCards.forEach(card => {
        // Remove old click listeners by cloning
        const newCard = card.cloneNode(true);

        // Re-add onclick from original attribute
        const onclickAttr = card.getAttribute('onclick');
        if (onclickAttr) {
            newCard.setAttribute('onclick', onclickAttr);
            card.parentNode.replaceChild(newCard, card);
        }
    });
}

