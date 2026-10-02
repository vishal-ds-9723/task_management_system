/* ═══════════════════════════════════════════════════════════════
   UI ENHANCEMENTS — JavaScript
   TheLayout Task Management System
   ═══════════════════════════════════════════════════════════════ */

(function () {
    'use strict';

    /* ──────────────────────────────────────────────────────────────
       1. TOAST SYSTEM
    ────────────────────────────────────────────────────────────── */

    const TOAST_ICONS = {
        success: '<i class="fas fa-check-circle"></i>',
        error:   '<i class="fas fa-times-circle"></i>',
        info:    '<i class="fas fa-info-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
    };

    const TOAST_DURATIONS = { success: 3500, error: 5000, info: 4000, warning: 4500 };

    window.showToast = function (message, type = 'success', subtitle = '', duration = null) {
        const ms = duration || TOAST_DURATIONS[type] || 3500;
        const id = 'toast-' + Date.now();

        const el = document.createElement('div');
        el.id = id;
        el.className = 'toast toast-' + type;
        el.setAttribute('role', 'alert');
        el.innerHTML = `
            <div class="toast-icon">${TOAST_ICONS[type] || TOAST_ICONS.info}</div>
            <div class="toast-body">
                <div class="toast-title">${escHtml(message)}</div>
                ${subtitle ? `<div class="toast-subtitle">${escHtml(subtitle)}</div>` : ''}
            </div>
            <div class="toast-close" onclick="dismissToast('${id}')"><i class="fas fa-times"></i></div>
            <div class="toast-progress" style="animation-duration:${ms}ms"></div>
        `;
        el.addEventListener('click', function (e) {
            if (!e.target.closest('.toast-close')) dismissToast(id);
        });

        document.body.appendChild(el);
        setTimeout(() => dismissToast(id), ms);
    };

    window.dismissToast = function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('dismissing');
        setTimeout(() => el && el.remove(), 300);
    };

    /* Override global ajax helpers to use new toast system */
    if (window.ajax) {
        window.ajax.showSuccess = (msg, sub) => showToast(msg, 'success', sub);
        window.ajax.showError   = (msg, sub) => showToast(msg, 'error', sub);
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    /* ──────────────────────────────────────────────────────────────
       2. BUTTON LOADING STATES — Auto-attach on all forms
    ────────────────────────────────────────────────────────────── */

    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.hasAttribute('data-no-loader')) return;
        if (form.closest('[data-ajax-area]')) return;

        const btn = form.querySelector('button[type="submit"]:not([data-no-loading])');
        if (!btn) return;

        btn.classList.add('btn-loading');
        btn.disabled = true;

        /* Safety release after 12s — prevents permanently stuck buttons */
        setTimeout(() => {
            btn.classList.remove('btn-loading');
            btn.disabled = false;
        }, 12000);
    });

    /* AJAX form submit helper — call data-loading on buttons that fire fetch() */
    window.setButtonLoading = function (btn, loading) {
        if (!btn) return;
        if (loading) {
            btn.classList.add('btn-loading');
            btn.disabled = true;
            btn._originalHtml = btn.innerHTML;
        } else {
            btn.classList.remove('btn-loading');
            btn.disabled = false;
            if (btn._originalHtml) btn.innerHTML = btn._originalHtml;
        }
    };

    /* ──────────────────────────────────────────────────────────────
       3. SKELETON LOADERS — for Team Activity and async panels
    ────────────────────────────────────────────────────────────── */

    window.showSkeletonLoader = function (containerId, rows = 4, type = 'ta') {
        const el = document.getElementById(containerId);
        if (!el) return;

        if (type === 'ta') {
            let html = '';
            for (let i = 0; i < rows; i++) {
                html += `
                <div class="ta-skeleton-member">
                    <div class="ta-skeleton-head">
                        <div class="skeleton skeleton-avatar"></div>
                        <div style="flex:1;display:flex;flex-direction:column;gap:6px">
                            <div class="skeleton skeleton-text wide"></div>
                            <div class="skeleton skeleton-text short"></div>
                        </div>
                        <div class="skeleton skeleton-badge"></div>
                    </div>
                    <div class="ta-skeleton-body">
                        <div class="skeleton skeleton-text wide"></div>
                        <div class="skeleton skeleton-text med"></div>
                    </div>
                </div>`;
            }
            el.innerHTML = html;
        } else if (type === 'stat') {
            let html = '';
            for (let i = 0; i < rows; i++) {
                html += `
                <div class="skeleton-stat-card">
                    <div class="skeleton sk-icon"></div>
                    <div class="skeleton sk-value"></div>
                    <div class="skeleton sk-label"></div>
                </div>`;
            }
            el.innerHTML = html;
        } else {
            let html = '';
            for (let i = 0; i < rows; i++) {
                html += `
                <div class="skeleton-row">
                    <div class="skeleton skeleton-avatar"></div>
                    <div style="flex:1;display:flex;flex-direction:column;gap:6px">
                        <div class="skeleton skeleton-text wide"></div>
                        <div class="skeleton skeleton-text med"></div>
                    </div>
                    <div class="skeleton skeleton-badge"></div>
                </div>`;
            }
            el.innerHTML = html;
        }
    };

    /* Patch loadTeamActivity to use skeleton loader */
    const _origLoadTA = window.loadTeamActivity;
    if (typeof _origLoadTA === 'function') {
        window.loadTeamActivity = function () {
            showSkeletonLoader('taBody', 4, 'ta');
            _origLoadTA();
        };
    }

    /* ──────────────────────────────────────────────────────────────
       4. DARK MODE TOGGLE
    ────────────────────────────────────────────────────────────── */

    const DARK_KEY = 'tms-dark-mode';

    function applyDark(on) {
        document.body.classList.toggle('dark-mode', on);
        const btn = document.getElementById('darkModeBtn');
        if (btn) btn.innerHTML = on
            ? '<i class="fas fa-sun"></i>'
            : '<i class="fas fa-moon"></i>';
        if (btn) btn.title = on ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    }

    window.toggleDarkMode = function () {
        const isDark = document.body.classList.contains('dark-mode');
        applyDark(!isDark);
        localStorage.setItem(DARK_KEY, !isDark ? '1' : '0');
    };

    /* Restore on load */
    (function () {
        if (localStorage.getItem(DARK_KEY) === '1') applyDark(true);
    })();

    /* ──────────────────────────────────────────────────────────────
       5. KPI TREND INDICATORS — Auto-inject into stat cards
    ────────────────────────────────────────────────────────────── */

    window.injectKpiTrend = function (cardEl, pct, label) {
        if (!cardEl) return;
        const dir   = pct > 0 ? 'up' : pct < 0 ? 'down' : 'neutral';
        const arrow = pct > 0 ? '↑' : pct < 0 ? '↓' : '→';
        const abs   = Math.abs(pct);
        const existing = cardEl.querySelector('.kpi-trend');
        if (existing) existing.remove();
        const trend = document.createElement('div');
        trend.className = 'kpi-trend ' + dir;
        trend.innerHTML = `<span class="trend-arrow">${arrow}</span><span>${abs}%</span><span class="trend-label"> ${label || 'vs last week'}</span>`;
        cardEl.appendChild(trend);
    };

    /* ──────────────────────────────────────────────────────────────
       6. TABLE ROW CLICK NAVIGATION — Add pointer + row link
    ────────────────────────────────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', function () {
        /* Make any tr[data-href] clickable */
        document.querySelectorAll('tr[data-href]').forEach(function (row) {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function (e) {
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' ||
                    e.target.tagName === 'INPUT' || e.target.closest('a, button, input')) return;
                window.location.href = row.dataset.href;
            });
        });

        /* Inject dark mode toggle button into topbar */
        injectDarkModeButton();
    });

    function injectDarkModeButton () {
        /* Find the topbar right side (notification bell row) */
        const bellParent = document.querySelector('.notif-bell')?.parentElement;
        if (!bellParent) return;
        if (document.getElementById('darkModeBtn')) return;

        const btn = document.createElement('div');
        btn.id = 'darkModeBtn';
        btn.className = 'dark-mode-toggle';
        btn.title = 'Toggle Dark Mode';
        btn.innerHTML = document.body.classList.contains('dark-mode')
            ? '<i class="fas fa-sun"></i>'
            : '<i class="fas fa-moon"></i>';
        btn.onclick = window.toggleDarkMode;

        /* Insert before the notification bell */
        const bell = document.querySelector('.notif-bell');
        if (bell) bellParent.insertBefore(btn, bell);
    }

    /* ──────────────────────────────────────────────────────────────
       7. FORM REAL-TIME VALIDATION FEEDBACK
    ────────────────────────────────────────────────────────────── */

    document.addEventListener('DOMContentLoaded', function () {
        /* Required field immediate feedback on blur */
        document.querySelectorAll('input[required], select[required], textarea[required]')
            .forEach(function (el) {
                el.addEventListener('blur', function () {
                    if (!el.value.trim()) {
                        el.classList.add('is-invalid');
                        el.classList.remove('is-valid');
                    } else {
                        el.classList.remove('is-invalid');
                        el.classList.add('is-valid');
                    }
                });
                el.addEventListener('input', function () {
                    if (el.classList.contains('is-invalid') && el.value.trim()) {
                        el.classList.remove('is-invalid');
                        el.classList.add('is-valid');
                    }
                });
            });
    });

}());

/* ──────────────────────────────────────────────────────────────
   8. SIDEBAR — Ripple, hamburger ↔ X morph, swipe-to-close
────────────────────────────────────────────────────────────── */
(function () {
    function initSidebarFX() {
        // ── Ripple on every nav-item click ──
        document.querySelectorAll('.nav-item').forEach(function (item) {
            item.addEventListener('click', function (e) {
                var rect = this.getBoundingClientRect();
                var ripple = document.createElement('span');
                ripple.className = 'nav-ripple';
                ripple.style.left = (e.clientX - rect.left) + 'px';
                ripple.style.top  = (e.clientY - rect.top)  + 'px';
                this.appendChild(ripple);
                setTimeout(function () { ripple.remove(); }, 600);
            });
        });

        // ── Replace text ☰ with FontAwesome icon (avoids FOUC) ──
        var menuBtn = document.querySelector('.mobile-menu-btn');
        if (menuBtn && !menuBtn.querySelector('i')) {
            menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        }

        // ── Observe sidebar class → morph hamburger icon ──
        var sidebar = document.getElementById('appSidebar');
        if (sidebar && menuBtn) {
            new MutationObserver(function () {
                var isOpen = sidebar.classList.contains('mobile-open');
                menuBtn.classList.toggle('is-open', isOpen);

                var icon = menuBtn.querySelector('i');
                if (!icon) return;

                // Fade-scale out → swap → fade-scale in
                icon.style.opacity   = '0';
                icon.style.transform = 'scale(0.55)';
                setTimeout(function () {
                    icon.className       = isOpen ? 'fas fa-times' : 'fas fa-bars';
                    icon.style.opacity   = '1';
                    icon.style.transform = 'scale(1)';
                }, 110);
            }).observe(sidebar, { attributes: true, attributeFilter: ['class'] });
        }

        // ── Swipe-left to close mobile sidebar ──
        var touchStartX = null;
        var touchStartY = null;

        document.addEventListener('touchstart', function (e) {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        }, { passive: true });

        document.addEventListener('touchend', function (e) {
            if (touchStartX === null) return;
            var dx = touchStartX - e.changedTouches[0].clientX;
            var dy = Math.abs(touchStartY - e.changedTouches[0].clientY);

            if (dx >= 60 && dy < 80) {
                var sb = document.querySelector('.sidebar');
                if (sb && sb.classList.contains('mobile-open')) {
                    if (typeof closeMobileSidebar === 'function') closeMobileSidebar();
                }
            }
            touchStartX = null;
            touchStartY = null;
        }, { passive: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarFX);
    } else {
        initSidebarFX();
    }
}());
