document.addEventListener('DOMContentLoaded', function() {
    setupDashboardShootDayAJAX();
    setupMetricDrillDown();
    startISTClock();
});

// ===== IST Live Clock =====
function startISTClock() {
    function updateClock() {
        const now = new Date();

        // Convert to IST (UTC+5:30)
        const istOffset = 5.5 * 60 * 60 * 1000; // 5 hours 30 minutes in milliseconds
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const istTime = new Date(utc + istOffset);

        // Format time in 12-hour format with AM/PM
        let hours = istTime.getHours();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'

        const minutes = istTime.getMinutes().toString().padStart(2, '0');
        const seconds = istTime.getSeconds().toString().padStart(2, '0');

        // Update the clock display
        const timeElement = document.getElementById('istTime');
        const ampmElement = document.getElementById('istAmPm');

        if (timeElement && ampmElement) {
            timeElement.textContent = `${hours}:${minutes}:${seconds}`;
            ampmElement.textContent = ampm;
        }
    }

    // Update immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);
}

// ===== Metric Drill-Down =====
function setupMetricDrillDown() {
    const cards = document.querySelectorAll('.cmd-metric-clickable');
    cards.forEach(card => {
        card.addEventListener('click', function() {
            const metric = this.dataset.metric;
            const panel = document.getElementById('cmdDrillDown');
            const wasActive = this.classList.contains('cmd-metric-active');

            // Deactivate all cards
            cards.forEach(c => c.classList.remove('cmd-metric-active'));

            if (wasActive) {
                closeDrillDown();
                return;
            }

            // Activate this card
            this.classList.add('cmd-metric-active');

            // Map metric -> panel, icon, title, count
            const config = {
                completion: { panel: 'ddCompletion', icon: 'fa-circle-check', title: 'Completed Tasks', color: 'var(--teal)', count: window.StrategistDashboardData.completedTasks },
                productivity: { panel: 'ddProductivity', icon: 'fa-gauge-high', title: 'Productivity Breakdown', color: 'var(--blue)', count: window.StrategistDashboardData.productivityScoreLabel },
                overdue: { panel: 'ddOverdue', icon: 'fa-triangle-exclamation', title: 'Overdue & Due Soon', color: 'var(--red)', count: window.StrategistDashboardData.overdueAndDueSoon },
                review: { panel: 'ddReview', icon: 'fa-flask', title: 'In Review Tasks', color: 'var(--purple)', count: window.StrategistDashboardData.pendingApprovals },
                progress: { panel: 'ddProgress', icon: 'fa-rocket', title: 'In Progress Tasks', color: 'var(--blue)', count: window.StrategistDashboardData.inProgressCount },
                pending: { panel: 'ddPending', icon: 'fa-clipboard-list', title: 'Pending Assignment', color: 'var(--yellow)', count: window.StrategistDashboardData.pendingAssign },
            };

            const cfg = config[metric];
            if (!cfg) return;

            // Update header
            document.getElementById('ddIcon').className = 'fa-solid ' + cfg.icon;
            document.getElementById('ddIcon').style.color = cfg.color;
            document.getElementById('ddTitle').textContent = cfg.title;
            document.getElementById('ddCount').textContent = cfg.count;

            // Show correct panel
            document.querySelectorAll('.cmd-dd-panel').forEach(p => p.classList.remove('active'));
            const targetPanel = document.getElementById(cfg.panel);
            if (targetPanel) targetPanel.classList.add('active');

            // Show the drill-down container
            panel.style.display = 'block';
            panel.style.animation = 'none';
            panel.offsetHeight; // force reflow
            panel.style.animation = 'cmdDrillOpen .35s cubic-bezier(.25,.8,.25,1) both';

            // Smooth scroll to it
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
}

function closeDrillDown() {
    const panel = document.getElementById('cmdDrillDown');
    document.querySelectorAll('.cmd-metric-clickable').forEach(c => c.classList.remove('cmd-metric-active'));
    panel.style.animation = 'cmdDrillClose .3s cubic-bezier(.25,.8,.25,1) both';
    setTimeout(() => { panel.style.display = 'none'; }, 300);
}

function setupDashboardShootDayAJAX() {
    const createForm = document.getElementById('sdCreateForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleDashShootCreate(this);
        });
    }
    document.querySelectorAll('.cmd-sd-actions form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const isDelete = this.querySelector('input[name="_method"][value="DELETE"]');
            if (isDelete) {
                if (!confirm('Delete this shoot?')) return;
                handleDashShootDelete(this);
            } else {
                handleDashShootUpdate(this);
            }
        });
    });
}

async function handleDashShootCreate(form) {
    const btn = form.querySelector('button[type="submit"]');
    const origText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    btn.disabled = true;
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (response.ok && data.success) {
            showDashToast(data.message || 'Shoot day created!', 'success');
            form.reset();
            document.getElementById('sdFormWrap').classList.add('sd-hidden');
            refreshDashboardPage();
        } else {
            const errors = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Failed to create');
            showDashToast(errors, 'error');
        }
    } catch (err) {
        showDashToast('Network error', 'error');
    } finally {
        btn.innerHTML = origText;
        btn.disabled = false;
    }
}

async function handleDashShootUpdate(form) {
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (response.ok && data.success) {
            showDashToast(data.message || 'Status updated!', 'success');
            const row = form.closest('tr');
            if (row) { row.style.transition = 'opacity 0.3s'; row.style.opacity = '0.5'; }
            refreshDashboardPage();
        } else {
            showDashToast(data.message || 'Update failed', 'error');
        }
    } catch (err) {
        showDashToast('Network error', 'error');
    }
}

async function handleDashShootDelete(form) {
    const row = form.closest('tr');
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (response.ok && data.success) {
            showDashToast(data.message || 'Shoot deleted!', 'success');
            if (row) {
                row.style.transition = 'all 0.3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    row.remove();
                    if (!document.querySelector('.cmd-sd-table tbody tr')) refreshDashboardPage();
                }, 300);
            }
        } else {
            showDashToast(data.message || 'Delete failed', 'error');
        }
    } catch (err) {
        showDashToast('Network error', 'error');
    }
}

async function refreshDashboardPage() {
    try {
        const response = await fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        });
        const html = await response.text();
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');
        const oldScroll = document.querySelector('.cmd-sd-table')?.closest('.cmd-card-scroll');
        const newScroll = newDoc.querySelector('.cmd-sd-table')?.closest('.cmd-card-scroll');
        if (oldScroll && newScroll) {
            oldScroll.innerHTML = newScroll.innerHTML;
        } else if (oldScroll) {
            const newCard = newDoc.querySelector('.cmd-sd-table')?.closest('.cmd-card') || newDoc.querySelector('.cmd-empty-state')?.closest('.cmd-card-scroll');
            if (newCard) oldScroll.innerHTML = newCard.innerHTML;
        }
        const oldStatus = document.querySelector('.cmd-sd-status-row');
        const newStatus = newDoc.querySelector('.cmd-sd-status-row');
        if (oldStatus && newStatus) oldStatus.innerHTML = newStatus.innerHTML;
        setupDashboardShootDayAJAX();
    } catch (err) {
        console.error('Dashboard refresh error:', err);
    }
}

function showDashToast(message, type) {
    const existing = document.querySelector('.dash-toast');
    if (existing) existing.remove();
    const toast = document.createElement('div');
    toast.className = 'dash-toast';
    toast.style.cssText = 'position:fixed;top:24px;right:24px;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:600;z-index:9999;max-width:400px;box-shadow:0 8px 24px rgba(0,0,0,0.15);backdrop-filter:blur(8px);';
    toast.style.background = type === 'success' ? 'linear-gradient(135deg,#10B981,#059669)' : 'linear-gradient(135deg,#EF4444,#DC2626)';
    toast.style.color = '#fff';
    toast.textContent = message;
    document.body.appendChild(toast);
    toast.animate([{opacity:0,transform:'translateY(-10px)'},{opacity:1,transform:'translateY(0)'}], {duration:300,easing:'ease'});
    setTimeout(() => {
        toast.animate([{opacity:1},{opacity:0}], {duration:300,easing:'ease'}).onfinish = () => toast.remove();
    }, 3000);
}
