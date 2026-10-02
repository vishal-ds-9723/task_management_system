<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/icon.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />


    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TheLayout – Task Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="preload" as="style" href="{{ asset('css/agencyflow.css') }}?v=3.0">
    <link rel="preload" as="style" href="{{ asset('css/premium-ui.css') }}">
    <link rel="preload" as="style" href="{{ asset('css/ui-enhancements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/agencyflow.css') }}?v=3.0">
    <link rel="stylesheet" href="{{ asset('css/premium-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui-enhancements.css') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    @stack('styles')
    <script>
        // Read sidebar expansion state synchronously, before first paint.
        // Without this, the sidebar paints collapsed (52px logo), the
        // IIFE at the bottom of body adds .expanded, and the logo jumps to
        // 160px — visible as a "zoomed logo flash" on every admin page load.
        try {
            if (localStorage.getItem('sidebar-expanded') === '1') {
                document.documentElement.classList.add('sidebar-pre-expanded');
            }
        } catch (_) {}
    </script>
    <style>
        /* Suppress sidebar / brand-logo transitions during the very first
           render so even if the .expanded class is toggled by JS after the
           paint, the user never sees the animation. Re-enabled once the body
           gets the .preload-done class (added by JS after the first frame). */
        body:not(.preload-done) .sidebar,
        body:not(.preload-done) .sidebar *,
        body:not(.preload-done) .brand-logo,
        body:not(.preload-done) .main {
            transition: none !important;
        }
        /* Apply expanded sidebar state on the very first paint, BEFORE the
           body-level IIFE has had a chance to add .expanded to the sidebar.
           Scoped to body:not(.preload-done) so these rules stop applying once
           the user can toggle — at which point .sidebar.expanded from
           agencyflow.css owns the styling. Mirrors agencyflow.css L291-311. */
        html.sidebar-pre-expanded body:not(.preload-done) .sidebar { width: 220px; align-items: stretch; padding: 10px 0 12px; }
        html.sidebar-pre-expanded body:not(.preload-done) .sidebar-brand { padding: 10px 16px 8px; text-align: left; justify-content: center; }
        html.sidebar-pre-expanded body:not(.preload-done) .sidebar-brand .brand-name { text-align: left; flex: 1; }
        html.sidebar-pre-expanded body:not(.preload-done) .sidebar-brand .brand-logo { width: 160px; height: auto; margin: 0 auto; }
        html.sidebar-pre-expanded body:not(.preload-done) .nav-section { align-items: stretch; padding: 2px 10px; }
        html.sidebar-pre-expanded body:not(.preload-done) .nav-item { width: 100%; height: auto; padding: 9px 12px; justify-content: flex-start; gap: 10px; font-size: 13px; border-radius: 10px; }
        html.sidebar-pre-expanded body:not(.preload-done) .nav-item .icon { font-size: 17px; width: 22px; text-align: center; flex-shrink: 0; }
        html.sidebar-pre-expanded body:not(.preload-done) .nav-item > span:not(.icon):not(.nav-tooltip) { display: inline; }
        html.sidebar-pre-expanded body:not(.preload-done) .nav-item .nav-tooltip { display: none !important; }
        html.sidebar-pre-expanded body:not(.preload-done) .sidebar-footer { padding: 8px 10px 0; justify-content: stretch; }
        html.sidebar-pre-expanded body:not(.preload-done) .user-pill { justify-content: flex-start; gap: 10px; padding: 8px 10px; }
        html.sidebar-pre-expanded body:not(.preload-done) .user-pill > div:not(.avatar) { display: block; }
        html.sidebar-pre-expanded body:not(.preload-done) .user-pill .nav-tooltip { display: none !important; }
        html.sidebar-pre-expanded body:not(.preload-done) .sidebar ~ .main { margin-left: 220px; }
    </style>
</head>
<body>
@php
    $unreadNotificationCount = auth()->user()->visibleUnreadCustomNotifications()->count();
    $pendingAssignedCount = \App\Models\Task::query()
        ->where('assigned_to', auth()->id())
        ->where('status', '!=', 'completed')
        ->count();
    $pendingAssignedByAdminCount = \App\Models\Task::query()
        ->where('assigned_to', auth()->id())
        ->where('status', '!=', 'completed')
        ->whereHas('creator', fn ($q) => $q->where('role', 'admin'))
        ->count();
    $myTasksUrl = auth()->user()->role === 'designer'
        ? route('designer.tasks')
        : (auth()->user()->role === 'strategist' ? route('strategist.tracking') : (auth()->user()->role === 'developer' ? route('developer.tasks') : route('admin.tasks')));
@endphp

{{-- Page Loader --}}
<div class="page-loader" id="pageLoader" hidden>
    <div class="loader-container">
        <img src="{{ asset('images/logo.png') }}" alt="The Layout" class="loader-logo">
        <div class="loader-spinner"></div>
    </div>
</div>

{{-- Logout Farewell Overlay --}}
<div class="logout-farewell" id="logoutFarewell" aria-hidden="true">
    <div class="logout-farewell-card">
        <div class="logout-farewell-icon" id="logoutFarewellIcon">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        <div class="logout-farewell-title" id="logoutFarewellTitle">Bye!</div>
        <div class="logout-farewell-subtitle" id="logoutFarewellSubtitle">Signing you out...</div>
    </div>
</div>

{{-- PWA Install Banner --}}
<div id="pwaInstallBanner" class="pwa-banner">
    <div class="pwa-banner-content">
        <div class="pwa-banner-info">
            <div class="pwa-icon-box">
                <img src="{{ asset('images/logo.png') }}" alt="App Icon">
            </div>
            <div class="pwa-text-box">
                <div class="pwa-title">Install TheLayout App</div>
                <div class="pwa-desc">Get a faster, more premium experience on your device.</div>
            </div>
        </div>
        <div class="pwa-btns">
            <button id="pwaInstallBtn" class="btn-pwa-install">Install Now</button>
            <button onclick="hidePwaBanner()" class="btn-pwa-close" title="Dismiss">Not now</button>
        </div>
    </div>
</div>

{{-- Notification Overlay --}}
<div class="overlay-bg" id="overlayBg" onclick="handleOverlayClick(event)"></div>
<x-notification-panel />

{{-- Global Reminders Modal --}}
<div class="modal-overlay" id="globalRemindersModal">
    <div class="modal" style="max-width:550px">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-bell" style="color:#1565c0;margin-right:8px"></i> Active Reminders</div>
            <div class="modal-close" onclick="closeModal('globalRemindersModal')"><i class="fas fa-times"></i></div>
        </div>
        <div class="grm-body" id="grmBody" style="padding:0;max-height:500px;overflow-y:auto">
            <div style="padding:24px;text-align:center;color:var(--text3)">
                <div class="loader-spinner" style="margin:0 auto 12px;scale:.6"></div>
                <div>Loading reminders...</div>
            </div>
        </div>
    </div>
</div>

{{-- Create Task Modal --}}
@if(auth()->user()->role !== 'admin')
    <x-create-task-modal :clients="$createTaskClients ?? $clients ?? collect()" :members="$createTaskMembers ?? $designers ?? collect()" />
@endif

{{-- Task Detail Modal --}}
<div class="modal-overlay" id="taskModal">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-title" id="modalTitle">Task Detail</div>
            <div class="modal-close" onclick="closeModal('taskModal')"><i class="fas fa-times"></i></div>
        </div>
        <div id="modalBody"></div>
    </div>
</div>

<div class="layout">
    {{-- Sidebar --}}
    <x-sidebar />

    @if(auth()->user()->role === 'admin')
    {{-- Team Activity Panel --}}
    <div id="teamActivityBackdrop" class="ta-backdrop" onclick="closeTeamActivity()"></div>
    <div id="teamActivityPanel" class="ta-panel">
        <div class="ta-header">
            <div>
                <div class="ta-title"><i class="fas fa-users" style="margin-right:6px;opacity:0.7"></i>Team Activity</div>
                <div class="ta-subtitle">Who's working on what right now</div>
            </div>
            <button class="ta-close" onclick="closeTeamActivity()"><i class="fas fa-times"></i></button>
        </div>
        <div class="ta-filters">
            <input type="text" id="taSearch" class="ta-search" placeholder="Search member or task..." oninput="filterTeamActivity()">
        </div>
        <div class="ta-filters-row">
            <select id="taRoleFilter" class="ta-filter-select" onchange="filterTeamActivity()">
                <option value="">All Roles</option>
                <option value="designer">Designer</option>
                <option value="strategist">Strategist</option>
                <option value="developer">Developer</option>
                <option value="editor">Editor</option>
                <option value="manager">Manager</option>
            </select>
            <select id="taStatusFilter" class="ta-filter-select" onchange="filterTeamActivity()">
                <option value="">All Status</option>
                <option value="working"><i class="fas fa-crosshairs"></i> Working</option>
                <option value="has_tasks"><i class="fas fa-tasks"></i> Has Tasks</option>
                <option value="idle"><i class="fas fa-moon"></i> Idle</option>
            </select>
        </div>
        <div class="ta-stats" id="taStats"></div>
        <div class="ta-body" id="taBody">
            <div class="ta-loader">Loading team activity...</div>
        </div>
    </div>
    @endif

    {{-- Main Content --}}
    <div class="main">
        {{-- Global Top Bar --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:10px">
            <div style="display:flex;align-items:center;gap:12px">
                <div class="mobile-menu-btn"><i class="fas fa-bars"></i></div>
            </div>
            <div style="display:flex;align-items:center;gap:12px;margin-left:auto">

                @if(auth()->user()->role === 'admin')
                <div class="ta-toggle-btn" onclick="toggleTeamActivity()" title="Team Activity">
                    <div><i class="fas fa-users"></i></div>
                </div>
                @endif
                {{-- Global Reminder Widget --}}
                @if(auth()->user()->role === 'strategist')
                <div class="global-reminder-widget" id="globalReminderWidget" title="View all reminders">
                    <div class="grw-icon" onclick="openGlobalRemindersModal()"><i class="fas fa-bell"></i></div>
                    <div class="grw-badge" id="grwBadge" style="display:none">0</div>
                </div>
                @endif
                <div style="display:flex;align-items:center;gap:6px;font-size:12.5px;color:var(--text3);background:var(--card2);padding:6px 14px;border-radius:var(--radius-sm);border:1px solid var(--border)">
                    <i class="fas fa-calendar-alt" style="margin-right:4px"></i> {{ now()->format('D, d M Y') }}
                </div>
                <div class="notif-bell" onclick="openNotif()">
                    <div><i class="fas fa-bell"></i></div>
                    <div class="notif-dot" id="notifDot" style="{{ $unreadNotificationCount > 0 ? '' : 'display:none' }}"></div>
                    <span class="notif-count-badge" id="notifCountBadge" style="{{ $unreadNotificationCount > 0 ? '' : 'display:none' }}">{{ $unreadNotificationCount }}</span>
                </div>
                <div class="av-sm" style="background:{{ auth()->user()->avatar_color ?? 'var(--primary)' }};width:32px;height:32px;font-size:11px;cursor:pointer" title="{{ auth()->user()->name }}">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </div>
        </div>

        @if($pendingAssignedCount > 0)
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;background:var(--yellow-dim);border:1px solid var(--border);padding:11px 14px;border-radius:var(--radius-sm);margin-bottom:14px">
                <div style="font-size:13px;color:var(--text);font-weight:600">
                    <i class="fas fa-thumbtack" style="margin-right:4px"></i> You have {{ $pendingAssignedCount }} pending assigned task{{ $pendingAssignedCount > 1 ? 's' : '' }}
                    @if($pendingAssignedByAdminCount > 0)
                        · {{ $pendingAssignedByAdminCount }} from admin
                    @endif
                </div>
                <a href="{{ $myTasksUrl }}" style="font-size:12px;font-weight:700;color:var(--primary);text-decoration:none">View tasks →</a>
            </div>
        @endif

        {{-- Flash Messages --}}
        @if(session('success'))
            <div id="flashToast" style="position:fixed;bottom:24px;right:24px;background:linear-gradient(135deg,var(--teal),#059669);color:#fff;padding:14px 22px;border-radius:12px;font-weight:700;z-index:1000;font-size:13.5px;box-shadow:0 8px 32px rgba(16,185,129,0.30);display:flex;align-items:center;gap:8px;animation:toastSlideIn 0.4s ease">
                <span style="font-size:16px"><i class="fas fa-check"></i></span> {{ session('success') }}
            </div>
            <script>setTimeout(() => { const t=document.getElementById('flashToast'); if(t){t.style.opacity='0';t.style.transform='translateY(10px)';t.style.transition='all 0.3s ease';setTimeout(()=>t.remove(),300);} }, 3000);</script>
        @endif
        @if(session('error'))
            <div id="flashToastErr" style="position:fixed;bottom:24px;right:24px;background:linear-gradient(135deg,var(--red),#DC2626);color:#fff;padding:14px 22px;border-radius:12px;font-weight:700;z-index:1000;font-size:13.5px;box-shadow:0 8px 32px rgba(239,68,68,0.30);display:flex;align-items:center;gap:8px;animation:toastSlideIn 0.4s ease">
                <span style="font-size:16px"><i class="fas fa-times"></i></span> {{ session('error') }}
            </div>
            <script>setTimeout(() => { const t=document.getElementById('flashToastErr'); if(t){t.style.opacity='0';t.style.transform='translateY(10px)';t.style.transition='all 0.3s ease';setTimeout(()=>t.remove(),300);} }, 4000);</script>
        @endif

        {{-- Page Content --}}
        @yield('content')
    </div>
</div>

<script>
// Sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('appSidebar');
    if (sidebar.classList.contains('expanded')) {
        sidebar.classList.remove('expanded');
        localStorage.setItem('sidebar-expanded', '0');
    } else {
        sidebar.classList.add('expanded');
        localStorage.setItem('sidebar-expanded', '1');
    }
}
function collapseSidebar() {
    const sidebar = document.getElementById('appSidebar');
    if (sidebar.classList.contains('expanded')) {
        sidebar.classList.remove('expanded');
        localStorage.setItem('sidebar-expanded', '0');
    }
}
(function() {
    const sidebar = document.getElementById('appSidebar');
    // The html.sidebar-pre-expanded class (set in <head>) already styles
    // the sidebar correctly on first paint. We still need the .expanded
    // class on the sidebar element itself because the rest of the JS
    // (toggle, hover, mobile menu) reads from it.
    if (sidebar && document.documentElement.classList.contains('sidebar-pre-expanded')) {
        sidebar.classList.add('expanded');
    }
    // Re-enable transitions after the very first frame.
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            document.body.classList.add('preload-done');
        });
    });
})();

function openNotif() {
    document.getElementById('notifPanel').classList.add('open');
    document.getElementById('overlayBg').classList.add('show');
    markAllNotificationsAsRead();
}
function closeNotif() {
    document.getElementById('notifPanel').classList.remove('open');
    document.getElementById('overlayBg').classList.remove('show');
}
function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }
function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('overlayBg');
    if (sidebar && overlay) {
        const isOpening = !sidebar.classList.contains('mobile-open');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('show');
        // Offset overlay so it doesn't cover the sidebar area
        if (isOpening) {
            overlay.style.left = sidebar.offsetWidth + 'px';
        } else {
            overlay.style.left = '0';
        }
    }
}
function closeMobileSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('overlayBg');
    if (sidebar) sidebar.classList.remove('mobile-open');
    if (overlay) {
        overlay.classList.remove('show');
        overlay.style.left = '0';
    }
}
function handleOverlayClick(e) {
    // Only close if clicking on the overlay itself
    if (e && e.target !== document.getElementById('overlayBg')) {
        return;
    }
    closeNotif();
    closeMobileSidebar();
}

// Toggle Sidebar (for desktop expand/collapse)
function toggleSidebar() {
    const sidebar = document.getElementById('appSidebar');
    if (sidebar) {
        sidebar.classList.toggle('expanded');
        localStorage.setItem('sidebar-expanded', sidebar.classList.contains('expanded') ? '1' : '0');
    }
}

// Collapse Sidebar (for mobile)
function collapseSidebar() {
    const sidebar = document.getElementById('appSidebar');
    if (sidebar) {
        sidebar.classList.remove('expanded');
        localStorage.setItem('sidebar-expanded', '0');
    }
}

// Initialize mobile menu button on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Attach click handler to mobile menu button
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    if (mobileBtn) {
        mobileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMobileMenu();
        });
    }

    // Prevent clicks inside sidebar from reaching the overlay
    const sidebarEl = document.getElementById('appSidebar');
    if (sidebarEl) {
        sidebarEl.addEventListener('click', function(e) {
            // Don't let sidebar clicks bubble to overlay
            e.stopPropagation();
        });
    }

    // Close mobile sidebar when a nav link is clicked — let navigation happen first
    document.querySelectorAll('.sidebar a.nav-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // Don't prevent default — let the link navigate
            const sidebar = document.querySelector('.sidebar');
            if (sidebar && sidebar.classList.contains('mobile-open')) {
                setTimeout(closeMobileSidebar, 200);
            }
        });
    });

    // Handle logout form button separately
    document.querySelectorAll('.sidebar button.nav-item').forEach(item => {
        item.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar && sidebar.classList.contains('mobile-open')) {
                setTimeout(closeMobileSidebar, 200);
            }
        });
    });

    const logoutForm = document.querySelector('form[data-logout-form]');
    if (logoutForm) {
        logoutForm.addEventListener('submit', function(e) {
            if (logoutForm.dataset.logoutConfirmed === '1') {
                return;
            }

            e.preventDefault();
            e.stopPropagation();
            triggerLogoutFarewell(logoutForm);
        });
    }

    // Handle user-pill click
    document.querySelectorAll('.sidebar .user-pill').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            collapseSidebar();
        });
    });

    // Prefetch sidebar destinations on hover/touch to speed up navigation.
    const prefetchedHrefs = new Set();
    document.querySelectorAll('.sidebar a.nav-item[href]').forEach(link => {
        const prefetch = () => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || prefetchedHrefs.has(href)) return;
            if (/^https?:\/\//i.test(href) && !href.includes(location.host)) return;

            const prefetchLink = document.createElement('link');
            prefetchLink.rel = 'prefetch';
            prefetchLink.href = href;
            document.head.appendChild(prefetchLink);
            prefetchedHrefs.add(href);
        };

        link.addEventListener('mouseenter', prefetch, { passive: true });
        link.addEventListener('touchstart', prefetch, { passive: true });
    });
});
function markAllNotificationsAsRead() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) return;

    fetch('/api/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    }).finally(() => {
        const dot = document.getElementById('notifDot');
        if (dot) dot.style.display = 'none';
        const badge = document.getElementById('notifCountBadge');
        if (badge) badge.style.display = 'none';
        // Remove unread styling
        document.querySelectorAll('.notif-unread').forEach(el => {
            el.classList.remove('notif-unread');
            el.classList.add('notif-read');
        });
        document.querySelectorAll('.notif-unread-dot').forEach(el => el.remove());
    });
}

function clearAllNotifications() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) return;

    fetch('/api/notifications/clear-all', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    }).then(r => r.json()).then(() => {
        const list = document.getElementById('notifList');
        if (list) {
            list.innerHTML = '<div style="padding:48px 20px;text-align:center">' +
                '<div style="font-size:36px;margin-bottom:10px;opacity:0.4"><i class="fas fa-bell"></i></div>' +
                '<div class="notif-item-title" style="margin-bottom:4px">All clear!</div>' +
                '<div class="notif-item-sub">No notifications.</div></div>';
        }
        const footer = document.querySelector('.notif-footer');
        if (footer) footer.style.display = 'none';
        const dot = document.getElementById('notifDot');
        if (dot) dot.style.display = 'none';
        const badge = document.getElementById('notifCountBadge');
        if (badge) badge.style.display = 'none';
    });
}
// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if(e.target === el) el.classList.remove('show'); });
});
// Close modal on Escape key
document.addEventListener('keydown', e => {
    if(e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(m => m.classList.remove('show'));
        closeNotif();
    }
});

// ===== PAGE LOADER =====
const pageLoader = document.getElementById('pageLoader');
const logoutFarewell = document.getElementById('logoutFarewell');
const logoutFarewellIcon = document.getElementById('logoutFarewellIcon');
const logoutFarewellTitle = document.getElementById('logoutFarewellTitle');
const logoutFarewellSubtitle = document.getElementById('logoutFarewellSubtitle');
const currentUserRole = @json((string) (auth()->user()->role ?? ''));
const LOADER_MAX_VISIBLE_MS = 12000;
const LOADER_MIN_VISIBLE_MS = 120;
// Defer the loader show by this much. Fast navigations (cached pages,
// calendar↔workload, etc.) finish before the timer fires, so the big logo
// never flashes onto the screen at all. Slow navigations still see it.
// Tuned to 320ms — covers FullCalendar / heavy admin pages whose JS
// execution would otherwise let the loader appear on the *outgoing* page.
const LOADER_SHOW_DELAY_MS = 320;
let pageLoaderVisibleAt = 0;
let pageLoaderFailsafeTimer = null;
let pageLoaderHideTimer = null;
let pageLoaderShowTimer = null;

function showPageLoader(immediate = false) {
    if (!pageLoader) return;
    clearTimeout(pageLoaderHideTimer);
    clearTimeout(pageLoaderShowTimer);

    const reveal = () => {
        pageLoaderVisibleAt = Date.now();
        pageLoader.removeAttribute('hidden');
        pageLoader.classList.add('show');

        clearTimeout(pageLoaderFailsafeTimer);
        pageLoaderFailsafeTimer = setTimeout(() => {
            hidePageLoader(true);
        }, LOADER_MAX_VISIBLE_MS);
    };

    if (immediate) {
        reveal();
    } else {
        pageLoaderShowTimer = setTimeout(reveal, LOADER_SHOW_DELAY_MS);
    }
}

function hidePageLoader(force = false) {
    if (!pageLoader) return;

    // Cancel any pending deferred show — if the navigation finished before
    // the loader had a chance to appear, leave it hidden.
    clearTimeout(pageLoaderShowTimer);

    const applyHide = () => {
        clearTimeout(pageLoaderFailsafeTimer);
        pageLoader.classList.remove('show');
        pageLoader.setAttribute('hidden', 'hidden');
        pageLoaderVisibleAt = 0;
    };

    if (force) {
        clearTimeout(pageLoaderHideTimer);
        applyHide();
        return;
    }

    // If the loader was never actually shown (still in deferred-pending state),
    // there's nothing to keep visible — bail out without the min-visible delay.
    if (!pageLoaderVisibleAt) {
        clearTimeout(pageLoaderHideTimer);
        applyHide();
        return;
    }

    const elapsed = Date.now() - pageLoaderVisibleAt;
    const delay = Math.max(0, LOADER_MIN_VISIBLE_MS - elapsed);

    clearTimeout(pageLoaderHideTimer);
    pageLoaderHideTimer = setTimeout(applyHide, delay);
}

function roleLabel(role) {
    const map = {
        admin: 'Admin',
        strategist: 'Strategist',
        designer: 'Designer',
        client: 'Client',
        developer: 'Developer',
        manager: 'Manager',
        editor: 'Editor',
        content_writer: 'Content Writer'
    };

    return map[role] || 'User';
}

function roleByeMessage(role) {
    const map = {
        admin: 'Control room secured. See you soon.',
        strategist: 'Strategy board saved. See you soon.',
        designer: 'Creative workspace saved. See you soon.',
        client: 'Your dashboard is up to date. See you soon.',
        developer: 'All systems are stable. See you soon.'
    };

    return map[role] || 'Session closed safely. See you soon.';
}

function roleByeIcon(role) {
    const map = {
        admin: 'fa-user-shield',
        strategist: 'fa-chess-knight',
        designer: 'fa-pen-ruler',
        client: 'fa-handshake',
        developer: 'fa-code'
    };

    return map[role] || 'fa-right-from-bracket';
}

function triggerLogoutFarewell(form) {
    if (!form || form.dataset.logoutConfirmed === '1') {
        return;
    }

    const role = (form.dataset.userRole || '').toLowerCase();
    const userName = (form.dataset.userName || 'there').trim();
    const firstName = userName.split(' ')[0] || userName;

    if (logoutFarewellTitle) {
        logoutFarewellTitle.textContent = `Bye, ${firstName}!`;
    }

    if (logoutFarewellSubtitle) {
        logoutFarewellSubtitle.textContent = `Signing out as ${roleLabel(role)}. ${roleByeMessage(role)}`;
    }

    if (logoutFarewellIcon) {
        logoutFarewellIcon.innerHTML = `<i class="fa-solid ${roleByeIcon(role)}"></i>`;
    }

    logoutFarewell?.classList.add('show');

    const logoutBtn = form.querySelector('button[type="submit"]');
    if (logoutBtn) {
        logoutBtn.disabled = true;
        logoutBtn.style.opacity = '0.75';
        logoutBtn.style.pointerEvents = 'none';
    }

    setTimeout(() => {
        form.dataset.logoutConfirmed = '1';
        form.submit();
    }, 550);
}

function shouldTriggerPageLoader(link, event) {
    if (!link || event.defaultPrevented) return false;

    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return false;
    }

    const target = (link.getAttribute('target') || '').toLowerCase();
    if (target && target !== '_self') return false;
    if (link.hasAttribute('download')) return false;

    const href = (link.getAttribute('href') || '').trim();
    if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) return false;
    if (href.startsWith('mailto:') || href.startsWith('tel:')) return false;

    if (link.closest('[data-ajax-area]') || link.hasAttribute('data-no-loader')) return false;

    let url;
    try {
        url = new URL(link.href, window.location.href);
    } catch (_) {
        return false;
    }

    if (url.origin !== window.location.origin) return false;

    const samePath =
        url.pathname === window.location.pathname &&
        url.search === window.location.search;

    // Same-document hash jump does not need a page loader.
    if (samePath && url.hash) return false;

    return true;
}

// Hide loader as soon as DOM is ready for faster perceived navigation
document.addEventListener('DOMContentLoaded', () => {
    if (pageLoader) {
        pageLoader.removeAttribute('data-fast');
    }
    hidePageLoader(true);
});

// Hide loader when page fully loads
window.addEventListener('load', () => {
    if (pageLoader) {
        pageLoader.removeAttribute('data-fast');
    }
    hidePageLoader(true);
});

// Show loader on navigation
document.addEventListener('click', (e) => {
    const link = e.target.closest('a[href]');
    if (shouldTriggerPageLoader(link, e)) {
        const isSidebarNav = !!link.closest('.sidebar a.nav-item');
        if (currentUserRole === 'strategist' && !link.hasAttribute('data-force-loader')) {
            return;
        }

        if (isSidebarNav && pageLoader) {
            pageLoader.setAttribute('data-fast', '1');
        } else {
            if (pageLoader) {
                pageLoader.removeAttribute('data-fast');
            }
        }

        showPageLoader();
    }
});

// Show loader on form submission
document.addEventListener('submit', (e) => {
    if (e.defaultPrevented) return;

    if (!e.target.closest('[data-ajax-area]') && !e.target.hasAttribute('data-no-loader')) {
        if (pageLoader) {
            pageLoader.removeAttribute('data-fast');
        }
        showPageLoader();
    }
});

// Show loader on full-page refresh/navigation (F5, browser reload, location change).
window.addEventListener('beforeunload', () => {
    if (!pageLoader) return;

    pageLoader.setAttribute('data-fast', '1');
    showPageLoader();
});

// Hide loader on browser back/forward (bfcache restore — must be instant, no delay)
window.addEventListener('pageshow', () => {
    if (pageLoader) {
        pageLoader.removeAttribute('data-fast');
    }
    hidePageLoader(true);
});

// ===== REAL-TIME NOTIFICATION POLLING =====
(function() {
    const POLL_INTERVAL = 15000; // 15 seconds
    let lastUnreadCount = parseInt(document.getElementById('notifCountBadge')?.textContent || '0', 10);
    let notifPanelOpen = false;

    function updateNotifBadge(count) {
        const dot = document.getElementById('notifDot');
        const badge = document.getElementById('notifCountBadge');
        if (count > 0) {
            if (dot) dot.style.display = '';
            if (badge) { badge.style.display = ''; badge.textContent = count > 99 ? '99+' : count; }
        } else {
            if (dot) dot.style.display = 'none';
            if (badge) badge.style.display = 'none';
        }
    }

    function renderNotifList(notifications) {
        const list = document.getElementById('notifList');
        if (!list) return;

        if (!notifications || notifications.length === 0) {
            list.innerHTML = '<div id="notifEmpty" style="padding:48px 20px;text-align:center">' +
                '<div style="font-size:36px;margin-bottom:10px;opacity:0.4"><i class="fas fa-bell"></i></div>' +
                '<div class="notif-item-title" style="margin-bottom:4px">No notifications yet</div>' +
                '<div class="notif-item-sub">Deadline alerts & task updates will appear here.</div></div>';
            // Remove footer if exists
            const footer = document.querySelector('.notif-footer');
            if (footer) footer.style.display = 'none';
            return;
        }

        let html = '';
        notifications.forEach(n => {
            const cls = n.read ? 'notif-read' : 'notif-unread';
            const link = n.link || '#';
            html += '<a href="' + link + '" class="notif-item ' + cls + '" style="text-decoration:none;display:flex;align-items:flex-start;gap:10px">' +
                '<div class="notif-icon">' + (n.icon || '<i class="fas fa-bell"></i>') + '</div>' +
                '<div style="flex:1;min-width:0">' +
                '<div class="notif-item-title">' + escapeHtml(n.title) + '</div>' +
                (n.subtitle ? '<div class="notif-item-sub">' + escapeHtml(n.subtitle) + '</div>' : '') +
                '<div class="notif-time">' + n.time + '</div></div>' +
                (!n.read ? '<span class="notif-unread-dot"></span>' : '') +
                '</a>';
        });
        list.innerHTML = html;

        // Show footer
        const footer = document.querySelector('.notif-footer');
        if (footer) footer.style.display = '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function pollNotifications() {
        fetch('/api/notifications/poll', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => { if (!r.ok) throw new Error('Poll failed'); return r.json(); })
        .then(data => {
            const newCount = data.unread_count || 0;

            // Show toast if new notifications arrived
            if (newCount > lastUnreadCount && lastUnreadCount >= 0) {
                const diff = newCount - lastUnreadCount;
                showNotifToast(diff);
            }

            updateNotifBadge(newCount);
            lastUnreadCount = newCount;

            // If notification panel is open, refresh the list
            if (document.getElementById('notifPanel')?.classList.contains('open')) {
                renderNotifList(data.notifications);
            }
        })
        .catch(() => { /* silent fail — will retry next interval */ });
    }

    function showNotifToast(count) {
        // Don't show toast if panel is already open
        if (document.getElementById('notifPanel')?.classList.contains('open')) return;

        const existing = document.getElementById('notifToastLive');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'notifToastLive';
        toast.style.cssText = 'position:fixed;top:20px;right:24px;background:linear-gradient(135deg,var(--primary),#6366F1);color:#fff;padding:12px 20px;border-radius:12px;font-weight:600;z-index:10001;font-size:13px;box-shadow:0 8px 32px rgba(99,102,241,0.35);display:flex;align-items:center;gap:8px;cursor:pointer;animation:toastSlideIn 0.4s ease';
        toast.innerHTML = '<span style="font-size:16px"><i class="fas fa-bell"></i></span> ' + count + ' new notification' + (count > 1 ? 's' : '');
        toast.onclick = function() { toast.remove(); openNotif(); };
        document.body.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }
        }, 4000);
    }

    // Start polling
    setInterval(pollNotifications, POLL_INTERVAL);

    // Track panel open state for smarter updates
    const origOpen = window.openNotif;
    window.openNotif = function() {
        origOpen();
        // Immediate refresh when opening panel
        fetch('/api/notifications/poll', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(data => {
            renderNotifList(data.notifications);
            lastUnreadCount = 0; // Will be 0 after mark-all-read
        }).catch(() => {});
    };
})();

/**
 * Global AJAX Utility
 * Provides consistent notifications and loading states
 */
window.ajax = {
    showSuccess: function(message) {
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:linear-gradient(135deg,var(--teal),#059669);color:#fff;padding:14px 22px;border-radius:12px;font-weight:700;z-index:10001;font-size:13.5px;box-shadow:0 8px 32px rgba(16,185,129,0.30);display:flex;align-items:center;gap:8px;animation:toastSlideIn 0.4s ease';
        toast.innerHTML = '<span style="font-size:16px"><i class="fas fa-check"></i></span> ' + message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },
    showError: function(message) {
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:linear-gradient(135deg,var(--red),#DC2626);color:#fff;padding:14px 22px;border-radius:12px;font-weight:700;z-index:10001;font-size:13.5px;box-shadow:0 8px 32px rgba(239,68,68,0.30);display:flex;align-items:center;gap:8px;animation:toastSlideIn 0.4s ease';
        toast.innerHTML = '<span style="font-size:16px"><i class="fas fa-times"></i></span> ' + message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    },
    showLoadingState: function(container) {
        if (!container) return;
        container.style.opacity = '0.6';
        container.style.pointerEvents = 'none';
        const submitBtn = container.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.dataset.originalHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }
    },
    hideLoadingState: function(container) {
        if (!container) return;
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
        const submitBtn = container.querySelector('button[type="submit"]');
        if (submitBtn && submitBtn.dataset.originalHtml) {
            submitBtn.innerHTML = submitBtn.dataset.originalHtml;
        }
    }
};

// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('Service Worker registered', reg))
            .catch(err => console.error('Service Worker registration failed', err));
    });
}

// PWA Install Logic
let deferredPrompt;
const pwaBanner = document.getElementById('pwaInstallBanner');
const installBtn = document.getElementById('pwaInstallBtn');

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the native mini-infobar
    e.preventDefault();
    // Save event for later
    deferredPrompt = e;
    // Show our custom banner
    if (pwaBanner && !localStorage.getItem('pwa-dismissed')) {
        pwaBanner.classList.add('show');
    }
});

if (installBtn) {
    installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return;

        pwaBanner.classList.remove('show');
        deferredPrompt.prompt();

        const { outcome } = await deferredPrompt.userChoice;
        console.log(`User response to install prompt: ${outcome}`);
        deferredPrompt = null;
    });
}

function hidePwaBanner() {
    if (pwaBanner) pwaBanner.classList.remove('show');
    // Don't show again for 24 hours
    localStorage.setItem('pwa-dismissed', Date.now());
}

// Check if already installed
window.addEventListener('appinstalled', () => {
    if (pwaBanner) pwaBanner.classList.remove('show');
    deferredPrompt = null;
});
</script>

<style>
/* ===== Mobile Menu Button (All Users) ===== */
.mobile-menu-btn {
    display: none;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--card);
    border: 1px solid var(--border);
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    color: var(--text);
    transition: background 0.2s ease, border-color 0.2s ease, transform 0.15s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* ===== PWA Install Banner ===== */
.pwa-banner {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(150%);
    width: 90%;
    max-width: 500px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    padding: 16px 20px;
    z-index: 99999;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    display: block;
}
.pwa-banner.show {
    transform: translateX(-50%) translateY(0);
}
.pwa-banner-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}
.pwa-banner-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}
.pwa-icon-box {
    width: 48px;
    height: 48px;
    background: var(--primary);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
}
.pwa-icon-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.pwa-title {
    font-weight: 800;
    font-size: 15px;
    color: #1f2937;
    margin-bottom: 2px;
}
.pwa-desc {
    font-size: 12px;
    color: #6b7280;
    font-weight: 500;
}
.pwa-btns {
    display: flex;
    gap: 10px;
    align-items: center;
}
.btn-pwa-install {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-pwa-install:hover {
    background: #4338ca;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}
.btn-pwa-close {
    background: #f3f4f6;
    color: #4b5563;
    border: none;
    padding: 10px 16px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-pwa-close:hover {
    background: #e5e7eb;
}

@media (max-width: 600px) {
    .pwa-banner {
        bottom: 12px;
        width: calc(100% - 24px);
        padding: 12px 16px;
    }
    .pwa-banner-info {
        width: 100%;
    }
    .pwa-btns {
        width: 100%;
        justify-content: flex-end;
        margin-top: 4px;
    }
    .btn-pwa-install {
        flex: 1;
        text-align: center;
    }
}
.mobile-menu-btn {
    -webkit-tap-highlight-color: transparent;
    user-select: none;
    touch-action: manipulation;
    flex-shrink: 0;
}
.mobile-menu-btn i {
    font-size: 18px;
    display: inline-block;
    transition: opacity 0.1s ease, transform 0.15s ease;
    line-height: 1;
}
.mobile-menu-btn:hover {
    background: var(--card2);
    border-color: var(--primary);
    transform: scale(1.05);
}
.mobile-menu-btn:active {
    transform: scale(0.93);
}

.logout-farewell {
    position: fixed;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: radial-gradient(circle at 20% 20%, rgba(79, 109, 240, 0.16), rgba(17, 24, 39, 0.72));
    backdrop-filter: blur(8px);
    z-index: 20000;
}

.logout-farewell.show {
    display: flex;
    animation: logoutFadeIn 0.25s ease;
}

.logout-farewell-card {
    width: min(440px, 92vw);
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 28px 24px;
    text-align: center;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
    animation: logoutCardIn 0.32s cubic-bezier(0.22, 1, 0.36, 1);
}

.logout-farewell-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 14px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
    background: linear-gradient(135deg, var(--primary), #0ea5e9);
    box-shadow: 0 12px 26px rgba(79, 109, 240, 0.35);
}

.logout-farewell-title {
    font-size: 24px;
    line-height: 1.2;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 8px;
}

.logout-farewell-subtitle {
    font-size: 13px;
    line-height: 1.6;
    color: var(--text2);
}

@keyframes logoutFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes logoutCardIn {
    from { transform: translateY(10px) scale(0.96); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

/* ===== Mobile Sidebar Styles (All Users) ===== */
@media (max-width: 900px) {
    .mobile-menu-btn {
        display: flex !important;
    }

    .layout {
        position: relative;
    }

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        width: 280px;
        transform: translateX(-100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 10001;
        background: var(--card);
        box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        pointer-events: none;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        padding: 10px 0 12px 0;
        overflow-y: auto;
    }

    .sidebar.mobile-open {
        transform: translateX(0);
        pointer-events: auto;
    }

    .sidebar.mobile-open .nav-section {
        align-items: stretch;
        padding: 8px 12px;
    }

    .sidebar.mobile-open .nav-item {
        width: 100%;
        height: auto;
        padding: 12px 16px;
        justify-content: flex-start;
        gap: 14px;
        font-size: 14px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        pointer-events: auto !important;
        position: relative;
        z-index: 2;
        cursor: pointer;
    }

    /* Ensure the ::before pseudo-element doesn't block clicks on mobile */
    .sidebar.mobile-open .nav-item::before {
        z-index: -1 !important;
        pointer-events: none !important;
    }

    /* Also target button nav-items (logout) */
    .sidebar.mobile-open button.nav-item {
        width: 100%;
        height: auto;
        padding: 12px 16px;
        justify-content: flex-start;
        gap: 14px;
        font-size: 14px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        pointer-events: auto !important;
        position: relative;
        z-index: 2;
        cursor: pointer;
        background: none;
        border: none;
        color: var(--text2);
    }

    .sidebar.mobile-open .nav-item .icon {
        font-size: 18px;
        width: 24px;
        text-align: center;
        flex-shrink: 0;
    }

    .sidebar.mobile-open .nav-item > span:not(.icon):not(.nav-tooltip) {
        display: inline !important;
        flex: 1;
        font-size: 14px;
        pointer-events: none;
    }

    /* Ensure form elements inside sidebar don't block layout */
    .sidebar.mobile-open form {
        width: 100%;
        margin: 0;
    }

    .sidebar.mobile-open .nav-item .nav-tooltip {
        display: none !important;
    }

    .sidebar.mobile-open .sidebar-brand {
        padding: 12px 16px;
        cursor: default;
    }

    .sidebar.mobile-open .sidebar-brand:hover .brand-logo {
        transform: none;
        opacity: 1;
    }

    .sidebar.mobile-open .sidebar-footer {
        padding: 12px 12px 0;
    }

    .sidebar.mobile-open .user-pill {
        justify-content: flex-start;
        gap: 12px;
        padding: 10px 12px;
        pointer-events: auto;
    }

    .sidebar.mobile-open .user-pill > div:not(.avatar) {
        display: block;
    }

    .sidebar .nav-item,
    .sidebar .user-pill,
    .sidebar a {
        touch-action: manipulation;
        -webkit-user-select: none;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
    }

    .main {
        margin-left: 0 !important;
        width: 100%;
        padding: 16px;
        padding-top: 70px;
    }

    /* Overlay when sidebar is open */
    .overlay-bg.show {
        display: block;
        opacity: 1;
        pointer-events: auto;
        background: rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(4px);
    }

    .overlay-bg {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0);
        z-index: 10000;
        opacity: 0;
        transition: all 0.3s ease;
        pointer-events: none;
    }
}

@media (max-width: 600px) {
    .mobile-menu-btn {
        width: 36px;
        height: 36px;
        font-size: 18px;
    }

    .sidebar {
        width: 260px;
    }

    .main {
        padding: 12px;
        padding-top: 60px;
    }
}

@media (max-width: 480px) {
    .sidebar {
        width: 240px;
    }

    .main {
        padding: 10px;
        padding-top: 55px;
    }
}
</style>

@if(auth()->user()->role === 'admin')
<style>
/* ── Team Activity Toggle Button ── */
.ta-toggle-btn {
    width:36px; height:36px; border-radius:var(--radius-sm);
    background:var(--card2); border:1px solid var(--border);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; transition:all 0.15s; font-size:16px;
    position:relative;
}
.ta-toggle-btn:hover { border-color:var(--primary); background:var(--card); transform:scale(1.05); }

/* ── Backdrop ── */
.ta-backdrop {
    display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.35); z-index:9998;
}
.ta-backdrop.open { display:block; }

/* ── Panel ── */
.ta-panel {
    position:fixed; top:0; right:-480px; width:460px; height:100vh;
    background:var(--card); border-left:1px solid var(--border);
    box-shadow:-8px 0 40px rgba(0,0,0,0.15);
    z-index:9999; display:flex; flex-direction:column;
    transition:right 0.3s cubic-bezier(0.4,0,0.2,1);
}
.ta-panel.open { right:0; }

.ta-header {
    padding:20px 20px 14px; display:flex; align-items:flex-start;
    justify-content:space-between; border-bottom:1px solid var(--border);
    flex-shrink:0;
}
.ta-title { font-size:16px; font-weight:700; color:var(--text); }
.ta-subtitle { font-size:11px; color:var(--text3); margin-top:2px; }
.ta-close {
    background:none; border:none; color:var(--text3); font-size:20px;
    cursor:pointer; transition:all 0.15s; padding:0; line-height:1;
}
.ta-close:hover { color:var(--text); transform:scale(1.15); }

/* ── Filters ── */
.ta-filters {
    padding:12px 20px 8px; display:flex; gap:8px; flex-shrink:0;
    border-bottom:1px solid var(--border);
}
.ta-filters-row {
    padding:0 20px 12px; display:flex; gap:8px; flex-shrink:0;
    border-bottom:1px solid var(--border);
}
.ta-search {
    flex:1; padding:8px 12px; border:1px solid var(--border);
    border-radius:8px; background:var(--card2); color:var(--text);
    font-size:12px; outline:none; transition:border 0.15s 0.05s;
}
.ta-search:focus { border-color:var(--primary); background:var(--bg); }
.ta-filter-select {
    flex:1; padding:8px 11px; border:1px solid var(--border);
    border-radius:8px; background:var(--card2); color:var(--text);
    font-size:12px; cursor:pointer; outline:none; transition:all 0.15s;
}
.ta-filter-select:hover { border-color:var(--primary); }
.ta-filter-select:focus { border-color:var(--primary); background:var(--bg); }

/* ── Stats ── */
.ta-stats {
    padding:10px 20px; display:flex; gap:10px; flex-shrink:0;
    border-bottom:1px solid var(--border);
}
.ta-stat-chip {
    padding:4px 10px; border-radius:99px; font-size:10px; font-weight:700;
    display:flex; align-items:center; gap:4px;
}

/* ── Body ── */
.ta-body {
    flex:1; overflow-y:auto; padding:12px 20px;
}
.ta-body::-webkit-scrollbar { width:7px; }
.ta-body::-webkit-scrollbar-track { background:transparent; margin-right:2px; }
.ta-body::-webkit-scrollbar-thumb { background:rgba(139,92,246,0.3); border-radius:99px; transition:background 0.2s; }
.ta-body::-webkit-scrollbar-thumb:hover { background:rgba(139,92,246,0.6); }

.ta-loader {
    padding:40px; text-align:center; color:var(--text3); font-size:13px;
}

/* ── Member Card ── */
.ta-member {
    margin-bottom:16px; border:1px solid var(--border);
    border-radius:10px; overflow:hidden; background:var(--bg);
}
.ta-member-head {
    display:flex; align-items:center; gap:10px; padding:12px 14px;
    background:var(--card2); cursor:pointer; transition:background 0.15s;
}
.ta-member-head:hover { background:var(--card); }
.ta-member-avatar {
    width:32px; height:32px; border-radius:50%; display:flex;
    align-items:center; justify-content:center; font-size:12px;
    font-weight:700; color:#fff; flex-shrink:0;
}
.ta-member-info { flex:1; min-width:0; }
.ta-member-name { font-size:13px; font-weight:700; color:var(--text); }
.ta-member-meta { font-size:10px; color:var(--text3); display:flex; gap:6px; align-items:center; }
.ta-role-badge {
    padding:1px 7px; border-radius:99px; font-size:9px; font-weight:700;
    text-transform:uppercase; letter-spacing:0.3px;
}
.ta-role-badge.designer { background:rgba(139,92,246,0.1); color:#8B5CF6; }
.ta-role-badge.strategist { background:rgba(59,130,246,0.1); color:#3B82F6; }
.ta-role-badge.developer { background:rgba(16,185,129,0.1); color:#10B981; }
.ta-role-badge.editor { background:rgba(249,115,22,0.1); color:#F97316; }
.ta-role-badge.manager { background:rgba(236,72,153,0.1); color:#EC4899; }
.ta-role-badge.content_writer { background:rgba(234,179,8,0.1); color:#EAB308; }
.ta-task-count {
    font-size:11px; font-weight:700; padding:2px 8px; border-radius:99px;
    flex-shrink:0;
}
.ta-toggle-icon {
    font-size:10px; color:var(--text3); transition:transform 0.2s; flex-shrink:0;
}
.ta-member.expanded .ta-toggle-icon { transform:rotate(180deg); }

/* ── Task List ── */
.ta-tasks { display:none; }
.ta-member.expanded .ta-tasks { display:block; }

.ta-task {
    display:flex; align-items:flex-start; gap:10px; padding:10px 14px;
    border-top:1px solid var(--border); transition:background 0.1s;
    text-decoration:none;
}
.ta-task:hover { background:var(--card2); }

.ta-task-status {
    width:8px; height:8px; border-radius:50%; margin-top:5px; flex-shrink:0;
}
.ta-task-status.inprogress { background:#3B82F6; box-shadow:0 0 6px rgba(59,130,246,0.4); }
.ta-task-status.review { background:#8B5CF6; box-shadow:0 0 6px rgba(139,92,246,0.4); }
.ta-task-status.todo { background:#F97316; }

.ta-task-info { flex:1; min-width:0; }
.ta-task-title {
    font-size:12px; font-weight:600; color:var(--text);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.ta-task-details {
    font-size:10px; color:var(--text3); margin-top:2px;
    display:flex; flex-wrap:wrap; gap:4px 8px; align-items:center;
}
.ta-task-priority {
    font-size:9px; font-weight:700; padding:1px 6px; border-radius:4px;
}
.ta-task-priority.urgent { background:rgba(239,68,68,0.1); color:#EF4444; }
.ta-task-priority.high { background:rgba(249,115,22,0.1); color:#F97316; }
.ta-task-priority.normal { background:rgba(99,102,241,0.1); color:#6366F1; }
.ta-task-deadline { font-size:10px; color:var(--text3); white-space:nowrap; flex-shrink:0; }
.ta-task-deadline.overdue { color:#EF4444; font-weight:600; }

.ta-no-tasks {
    padding:12px 14px; font-size:11px; color:var(--text3);
    text-align:center; border-top:1px solid var(--border);
    font-style:italic;
}

.ta-empty-state {
    padding:60px 20px; text-align:center; color:var(--text3);
}
.ta-empty-state .icon { font-size:32px; margin-bottom:8px; }
.ta-empty-state .text { font-size:13px; }

/* Online status dot */
.ta-online-dot {
    position:absolute; bottom:0; right:0;
    width:10px; height:10px; border-radius:50%;
    border:2px solid var(--card);
}

/* Focus Section */
.ta-focus-section {
    padding:10px 14px; border-bottom:1px solid var(--border);
    background:rgba(16,185,129,0.04);
}
.ta-focus-label {
    font-size:10px; font-weight:700; text-transform:uppercase;
    letter-spacing:0.5px; color:#10B981; margin-bottom:6px;
}
.ta-focus-card {
    display:block; padding:10px 12px; border-radius:8px;
    background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.15);
    text-decoration:none; color:var(--text); transition:all .2s;
}
.ta-focus-card:hover { background:rgba(16,185,129,0.14); transform:translateY(-1px); }
.ta-focus-title { font-size:12px; font-weight:600; margin-bottom:4px; }
.ta-focus-meta { font-size:10px; color:var(--text2); display:flex; flex-wrap:wrap; gap:4px; align-items:center; }
.ta-focus-deadline { font-size:10px; margin-top:4px; color:var(--text2); }
.ta-focus-deadline.overdue { color:#EF4444; font-weight:600; }

/* Stats Row */
.ta-stats-row {
    display:flex; gap:2px; padding:8px 14px; border-bottom:1px solid var(--border);
    background:var(--card2);
}
.ta-mini-stat {
    flex:1; text-align:center; padding:6px 2px;
    border-radius:6px; background:var(--card);
}
.ta-mini-val { font-size:14px; font-weight:700; color:var(--text); line-height:1.2; }
.ta-mini-label { font-size:8px; text-transform:uppercase; color:var(--text3); letter-spacing:0.3px; font-weight:600; }

/* Actions Section */
.ta-actions-section {
    padding:8px 14px; border-bottom:1px solid var(--border);
}
.ta-actions-label, .ta-tasklist-label {
    font-size:10px; font-weight:700; text-transform:uppercase;
    letter-spacing:0.5px; color:var(--text3); margin-bottom:6px;
    padding:0 0 4px 0;
}
.ta-tasklist-label { padding:10px 14px 4px; }
.ta-action-item {
    display:flex; align-items:flex-start; gap:8px; padding:4px 0;
    font-size:11px;
}
.ta-action-icon { flex-shrink:0; font-size:12px; margin-top:1px; }
.ta-action-text { flex:1; min-width:0; color:var(--text2); line-height:1.4; }
.ta-action-text span:first-child { display:block; }
.ta-action-task {
    display:inline-block; font-size:10px; color:var(--text3);
    background:var(--card2); padding:1px 6px; border-radius:4px;
    margin-top:2px;
}
.ta-action-time { flex-shrink:0; font-size:9px; color:var(--text3); white-space:nowrap; margin-top:2px; }

@media (max-width:520px) {
    .ta-panel { width:100%; right:-100%; }
}
</style>

<script>
let teamActivityData = [];

function toggleTeamActivity() {
    const panel = document.getElementById('teamActivityPanel');
    const backdrop = document.getElementById('teamActivityBackdrop');
    const isOpen = panel.classList.contains('open');
    if (isOpen) {
        closeTeamActivity();
    } else {
        panel.classList.add('open');
        backdrop.classList.add('open');
        loadTeamActivity();
    }
}

function closeTeamActivity() {
    document.getElementById('teamActivityPanel').classList.remove('open');
    document.getElementById('teamActivityBackdrop').classList.remove('open');
}

function loadTeamActivity() {
    const body = document.getElementById('taBody');
    body.innerHTML = '<div class="ta-loader">Loading team activity...</div>';

    fetch('/api/team-activity', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(async r => {
            if (!r.ok) {
                const text = await r.text();
                throw new Error(`Server error: ${r.status} ${text}`);
            }
            return r.json();
        })
        .then(data => {
            teamActivityData = data.members || [];
            console.log('Team Activity Loaded:', teamActivityData);
            renderTeamActivity(teamActivityData);
        })
        .catch(err => {
            console.error('Failed to load team activity:', err);
            body.innerHTML = '<div class="ta-loader">Failed to load. Try again.</div>';
        });
}

function filterTeamActivity() {
    const searchInput = document.getElementById('taSearch');
    const search = (searchInput?.value || '').toLowerCase().trim();
    const role = document.getElementById('taRoleFilter').value;
    const status = document.getElementById('taStatusFilter').value;

    if (!teamActivityData || teamActivityData.length === 0) {
        renderTeamActivity([]);
        return;
    }

    const filtered = teamActivityData.filter(m => {
        // Filter by role
        const matchRole = !role || m.role === role;

        // Filter by status
        let matchStatus = true;
        if (status) {
            if (status === 'working') matchStatus = m.current_focus !== null;
            else if (status === 'has_tasks') matchStatus = m.active_count > 0 && !m.current_focus;
            else if (status === 'idle') matchStatus = m.active_count === 0;
        }

        // Filter by search term
        let matchSearch = true;
        if (search) {
            const nameMatch = m.name && m.name.toLowerCase().includes(search);
            const emailMatch = m.email && m.email.toLowerCase().includes(search);
            let taskMatch = false;

            if (m.tasks && Array.isArray(m.tasks) && m.tasks.length > 0) {
                taskMatch = m.tasks.some(t => {
                    const titleMatch = t.title && t.title.toLowerCase().includes(search);
                    const clientMatch = t.client && t.client.toLowerCase().includes(search);
                    return titleMatch || clientMatch;
                });
            }

            matchSearch = nameMatch || emailMatch || taskMatch;
        }

        return matchRole && matchStatus && matchSearch;
    });

    renderTeamActivity(filtered);
}

function renderTeamActivity(members) {
    const body = document.getElementById('taBody');
    const stats = document.getElementById('taStats');

    const totalMembers = members.length;
    const busyMembers = members.filter(m => m.active_count > 0).length;
    const idleMembers = totalMembers - busyMembers;
    const totalActiveTasks = members.reduce((sum, m) => sum + m.active_count, 0);
    const totalOverdue = members.reduce((sum, m) => sum + (m.stats?.overdue || 0), 0);
    const completedToday = members.reduce((sum, m) => sum + (m.stats?.completed_today || 0), 0);

    stats.innerHTML = `
        <span class="ta-stat-chip" style="background:rgba(59,130,246,0.1);color:#3B82F6" title="Total Members"><i class="fas fa-users"></i> ${totalMembers}</span>
        <span class="ta-stat-chip" style="background:rgba(16,185,129,0.1);color:#10B981" title="Active Members"><i class="fas fa-sync-alt"></i> ${busyMembers}</span>
        <span class="ta-stat-chip" style="background:rgba(234,179,8,0.1);color:#EAB308" title="Idle Members"><i class="fas fa-moon"></i> ${idleMembers}</span>
        <span class="ta-stat-chip" style="background:rgba(139,92,246,0.1);color:#8B5CF6" title="Active Tasks"><i class="fas fa-tasks"></i> ${totalActiveTasks}</span>
        ${totalOverdue > 0 ? `<span class="ta-stat-chip" style="background:rgba(239,68,68,0.1);color:#EF4444" title="Overdue Tasks"><i class="fas fa-exclamation-triangle"></i> ${totalOverdue}</span>` : ''}
        ${completedToday > 0 ? `<span class="ta-stat-chip" style="background:rgba(16,185,129,0.1);color:#10B981" title="Completed Today"><i class="fas fa-check-circle"></i> ${completedToday}</span>` : ''}
    `;

    if (members.length === 0) {
        body.innerHTML = `<div class="ta-empty-state"><div class="icon"><i class="fas fa-search"></i></div><div class="text">No members found</div></div>`;
        return;
    }

    let html = '';
    members.forEach(member => {
        const s = member.stats || {};
        const taskCountColor = member.active_count === 0
            ? 'background:var(--card2);color:var(--text3)'
            : member.active_count >= 5
                ? 'background:rgba(239,68,68,0.1);color:#EF4444'
                : 'background:rgba(59,130,246,0.1);color:#3B82F6';

        // Status indicator
        let statusDot = '', statusText = '';
        if (member.current_focus) {
            statusDot = 'background:#10B981;box-shadow:0 0 8px rgba(16,185,129,0.5)';
            statusText = 'Working';
        } else if (member.active_count > 0) {
            statusDot = 'background:#EAB308';
            statusText = 'Has tasks';
        } else {
            statusDot = 'background:var(--text3)';
            statusText = 'Idle';
        }

        html += `<div class="ta-member${member.active_count > 0 ? ' expanded' : ''}" data-id="${member.id}">
            <div class="ta-member-head" onclick="toggleTaMember(this)">
                <div style="position:relative">
                    <div class="ta-member-avatar" style="background:${member.avatar_color}">${member.initial}</div>
                    <div class="ta-online-dot" style="${statusDot}" title="${statusText}"></div>
                </div>
                <div class="ta-member-info">
                    <div class="ta-member-name">${member.name}</div>
                    <div class="ta-member-meta">
                        <span class="ta-role-badge ${member.role}">${member.role}</span>
                        ${member.last_action ? `<span title="${member.last_action.action}">Last: ${member.last_action.time}</span>` : ''}
                    </div>
                </div>
                <span class="ta-task-count" style="${taskCountColor}">${member.active_count}</span>
                <span class="ta-toggle-icon">▼</span>
            </div>
            <div class="ta-tasks">`;

        // ── Current Focus Section ──
        if (member.current_focus) {
            const cf = member.current_focus;
            html += `<div class="ta-focus-section">
                <div class="ta-focus-label"><i class="fas fa-crosshairs"></i> Currently Working On</div>
                <a href="${cf.url}" class="ta-focus-card">
                    <div class="ta-focus-title">${cf.title}</div>
                    <div class="ta-focus-meta">
                        ${cf.client} · ${capitalize(cf.type)} · ${formatPlatform(cf.platform)}
                        <span class="ta-task-priority ${cf.priority}">${capitalize(cf.priority)}</span>
                    </div>
                    ${cf.deadline ? `<div class="ta-focus-deadline ${cf.is_overdue ? 'overdue' : ''}">${cf.is_overdue ? '<i class="fas fa-exclamation-triangle"></i> Overdue: ' : '<i class="fas fa-calendar-alt"></i> Due: '}${cf.deadline}</div>` : ''}
                </a>
            </div>`;
        }

        // ── Performance Stats Row ──
        html += `<div class="ta-stats-row">
            <div class="ta-mini-stat">
                <div class="ta-mini-val">${s.completed_today || 0}</div>
                <div class="ta-mini-label">Done Today</div>
            </div>
            <div class="ta-mini-stat">
                <div class="ta-mini-val">${s.completed_this_week || 0}</div>
                <div class="ta-mini-label">This Week</div>
            </div>
            <div class="ta-mini-stat">
                <div class="ta-mini-val" style="color:${s.overdue > 0 ? '#EF4444' : 'var(--text)'}">${s.overdue || 0}</div>
                <div class="ta-mini-label">Overdue</div>
            </div>
            <div class="ta-mini-stat">
                <div class="ta-mini-val">${s.completion_rate || 0}%</div>
                <div class="ta-mini-label">Rate</div>
            </div>
            <div class="ta-mini-stat">
                <div class="ta-mini-val">${s.comments_today || 0}</div>
                <div class="ta-mini-label">Comments</div>
            </div>
        </div>`;

        // ── Recent Actions Timeline ──
        if (member.recent_actions && member.recent_actions.length > 0) {
            html += `<div class="ta-actions-section">
                <div class="ta-actions-label">Recent Activity</div>`;
            member.recent_actions.forEach(act => {
                let aIcon = '<i class="fas fa-pen"></i>', aColor = 'var(--blue)';
                if (act.action.includes('Created')) { aIcon = '<i class="fas fa-plus"></i>'; aColor = 'var(--teal)'; }
                else if (act.action.includes('status')) { aIcon = '<i class="fas fa-sync-alt"></i>'; aColor = 'var(--yellow)'; }
                else if (act.action.includes('comment') || act.action.includes('Comment')) { aIcon = '<i class="fas fa-comment"></i>'; aColor = 'var(--purple)'; }
                else if (act.action.includes('Deleted')) { aIcon = '<i class="fas fa-trash"></i>'; aColor = 'var(--red)'; }

                html += `<div class="ta-action-item">
                    <span class="ta-action-icon">${aIcon}</span>
                    <div class="ta-action-text">
                        <span>${truncate(act.action, 50)}</span>
                        ${act.task ? `<span class="ta-action-task">${truncate(act.task, 30)}</span>` : ''}
                    </div>
                    <span class="ta-action-time">${act.time}</span>
                </div>`;
            });
            html += `</div>`;
        }

        // ── All Active Tasks ──
        if (member.tasks.length === 0) {
            html += `<div class="ta-no-tasks">No active tasks — currently idle <i class="fas fa-moon"></i></div>`;
        } else {
            html += `<div class="ta-tasklist-label"><i class="fas fa-tasks"></i> All Active Tasks (${member.tasks.length})</div>`;
            member.tasks.forEach(task => {
                const statusLabels = { inprogress: '<i class="fas fa-circle" style="font-size:8px"></i> Working', review: '<i class="fas fa-circle" style="font-size:8px;color:#8B5CF6"></i> Review', todo: '<i class="fas fa-circle" style="font-size:8px;color:#F97316"></i> To Do' };
                html += `<a href="${task.url}" class="ta-task">
                    <div class="ta-task-status ${task.status}"></div>
                    <div class="ta-task-info">
                        <div class="ta-task-title">${task.title}</div>
                        <div class="ta-task-details">
                            <span>${task.client}</span>
                            <span>·</span>
                            <span>${capitalize(task.type)} · ${formatPlatform(task.platform)}</span>
                            <span class="ta-task-priority ${task.priority}">${capitalize(task.priority)}</span>
                            <span style="color:${task.status === 'inprogress' ? '#3B82F6' : task.status === 'review' ? '#8B5CF6' : '#F97316'}">${statusLabels[task.status] || task.status}</span>
                        </div>
                    </div>
                    <div>
                        ${task.deadline
                            ? `<div class="ta-task-deadline${task.is_overdue ? ' overdue' : ''}">${task.is_overdue ? '<i class="fas fa-exclamation-triangle"></i> ' : '<i class="fas fa-calendar-alt"></i> '}${task.deadline}</div>`
                            : ''}
                    </div>
                </a>`;
            });
        }

        html += `</div></div>`;
    });

    body.innerHTML = html;
}

function toggleTaMember(el) {
    el.closest('.ta-member').classList.toggle('expanded');
}

function capitalize(str) {
    return str ? str.toString().charAt(0).toUpperCase() + str.toString().slice(1) : '';
}

function formatPlatform(platform) {
    if (Array.isArray(platform)) {
        return platform.map(p => capitalize(p)).join(', ');
    }
    return capitalize(platform);
}

function truncate(str, len) {
    if (!str) return '';
    return str.length > len ? str.substring(0, len) + '…' : str;
}

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const panel = document.getElementById('teamActivityPanel');
        if (panel && panel.classList.contains('open')) {
            closeTeamActivity();
        }
    }
});

// Attach search event listener to Team Activity search
document.addEventListener('DOMContentLoaded', function() {
    const taSearch = document.getElementById('taSearch');
    if (taSearch) {
        taSearch.addEventListener('keyup', filterTeamActivity);
        taSearch.addEventListener('paste', function() {
            setTimeout(filterTeamActivity, 10);
        });
    }
});
</script>
@endif

{{-- AJAX Helper - Core Utility for All AJAX Operations --}}
<script src="{{ asset('js/ajax-helper.js') }}"></script>

{{-- CRUD AJAX Helper - Universal CRUD Operations (Create, Read, Update, Delete) --}}
<script src="{{ asset('js/crud-ajax-helper.js') }}"></script>

{{-- UI Enhancements — Toast, Skeleton, Dark Mode, Button States --}}
<script src="{{ asset('js/ui-enhancements.js') }}"></script>

{{-- Global Reminders Widget Script --}}
<script>
// Global reminder variables
let globalReminders = [];
let remindersCheckInterval = null;

// Fetch and update global reminders
function updateGlobalReminders() {
    @if(auth()->user()->role === 'strategist')
    fetch('/api/reminders/active', {
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            globalReminders = data.reminders || [];
            const badge = document.getElementById('grwBadge');
            const widget = document.getElementById('globalReminderWidget');

            if (!badge) return;

            if (globalReminders.length === 0) {
                badge.style.display = 'none';
            } else {
                badge.textContent = globalReminders.length;
                badge.style.display = 'flex';

                // Add "due" class if any reminders are overdue
                const hasDue = globalReminders.some(r => r.is_due);
                if (hasDue) {
                    badge.classList.add('due');
                } else {
                    badge.classList.remove('due');
                }
            }
        }
    })
    .catch(err => console.log('Error fetching reminders:', err));
    @endif
}

// Open global reminders modal
function openGlobalRemindersModal() {
    const body = document.getElementById('grmBody');

    if (globalReminders.length === 0) {
        body.innerHTML = `
            <div class="grm-empty">
                <div class="grm-empty-icon"><i class="fas fa-check-circle" style="font-size:28px;color:var(--teal)"></i></div>
                <div class="grm-empty-text">No active reminders. You're all set!</div>
            </div>
        `;
    } else {
        let html = '<div class="grm-list">';
        globalReminders.forEach(r => {
            const dt = new Date(r.reminder_at);
            const dateStr = dt.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
            const timeStr = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            const isDue = r.is_due ? ' due' : ' pending';
            const badge = r.is_due ? '<span class="grm-item-badge due">DUE NOW</span>' : '<span class="grm-item-badge pending">PENDING</span>';

            html += `
                <div class="grm-item" onclick="viewReminderDetail(${r.id}, '${r.title.replace(/'/g, "\\'")}', '${r.reminder_at}', '${(r.reminder_note || '').replace(/'/g, "\\'")}')">
                    <div class="grm-item-content">
                        <div class="grm-item-title">${escapeHtmlForModal(r.title)}</div>
                        <div class="grm-item-time"><i class="fa-solid fa-clock"></i> ${dateStr} at ${timeStr}</div>
                        ${r.reminder_note ? `<div class="grm-item-note">${escapeHtmlForModal(r.reminder_note.substring(0, 80))}${r.reminder_note.length > 80 ? '...' : ''}</div>` : ''}
                    </div>
                    ${badge}
                </div>
            `;
        });
        html += '</div>';
        body.innerHTML = html;
    }

    openModal('globalRemindersModal');
}

// View reminder detail in modal
function viewReminderDetail(itemId, title, reminderTime, reminderNote) {
    // This reuses the reminder details modal from action-items page
    // If we're on action-items page, use that function; otherwise show a simple alert
    if (typeof openReminderDetailsModal === 'function') {
        closeModal('globalRemindersModal');
        openReminderDetailsModal(itemId, title, reminderTime, reminderNote);
    } else {
        const dt = new Date(reminderTime);
        const dateStr = dt.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
        const timeStr = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        alert(`${title}\n\nReminder: ${dateStr} at ${timeStr}\n\n${reminderNote}`);
    }
}

// Helper to escape HTML in modal
function escapeHtmlForModal(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return (text || '').replace(/[&<>"']/g, m => map[m]);
}

// Initialize and set up interval
updateGlobalReminders();
remindersCheckInterval = setInterval(updateGlobalReminders, 30000); // Check every 30 seconds
</script>

{{-- ══════════════ GLOBAL BOTTOM NAVIGATION (Mobile Only) ══════════════ --}}
@php
    $currentRoute = Route::currentRouteName() ?? '';
    $isAdminRoute = str_starts_with($currentRoute, 'admin.');
@endphp

@if($isAdminRoute)
<nav class="mobile-bottom-nav">
    <a href="{{ route('admin.dashboard') }}" class="mbn-item {{ $currentRoute === 'admin.dashboard' ? 'active' : '' }}">
        <div class="mbn-icon"><i class="fas fa-home"></i></div>
        <span class="mbn-label">Home</span>
    </a>
    <a href="{{ route('admin.tasks') }}" class="mbn-item {{ str_starts_with($currentRoute, 'admin.tasks') ? 'active' : '' }}">
        <div class="mbn-icon"><i class="fas fa-tasks"></i></div>
        <span class="mbn-label">Tasks</span>
    </a>
    <a href="{{ route('admin.clients') }}" class="mbn-item {{ str_starts_with($currentRoute, 'admin.clients') ? 'active' : '' }}">
        <div class="mbn-icon"><i class="fas fa-users"></i></div>
        <span class="mbn-label">Clients</span>
    </a>
    <a href="{{ route('admin.calendar') }}" class="mbn-item {{ $currentRoute === 'admin.calendar' ? 'active' : '' }}">
        <div class="mbn-icon"><i class="fas fa-calendar-alt"></i></div>
        <span class="mbn-label">Calendar</span>
    </a>
    <a href="{{ route('admin.report') }}" class="mbn-item {{ $currentRoute === 'admin.report' ? 'active' : '' }}">
        <div class="mbn-icon"><i class="fas fa-chart-bar"></i></div>
        <span class="mbn-label">Report</span>
    </a>
</nav>
@endif

@if(auth()->user()->role === 'admin')
{{-- Admin Chatbot --}}
@if(request()->routeIs('admin.dashboard'))
    <x-admin-chatbot />
@endif
@endif

{{-- Global chat realtime: toast + sound + native notification on incoming messages --}}
@if(! auth()->user()->isClient())
    <x-chat-realtime />
@endif

<style>
@keyframes toastSlideIn {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

@stack('scripts')
</body>
</html>
