<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/icon.png') }}" type="image/png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer">


    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TheLayout – Task Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="preload" as="style" href="{{ asset('css/agencyflow.css') }}?v=3.2">
    <link rel="preload" as="style" href="{{ asset('css/premium-ui.css') }}?v=3.2">
    <link rel="preload" as="style" href="{{ asset('css/ui-enhancements.css') }}?v=3.2">
    <link rel="stylesheet" href="{{ asset('css/agencyflow.css') }}?v=3.2">
    <link rel="stylesheet" href="{{ asset('css/premium-ui.css') }}?v=3.2">
    <link rel="stylesheet" href="{{ asset('css/ui-enhancements.css') }}?v=3.2">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
    <script>
        // Read sidebar expansion state synchronously, before first paint (desktop only).
        try {
            if (window.innerWidth > 900 && localStorage.getItem('sidebar-expanded') === '1') {
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
            animation: none !important;
        }

        /* Prevent mobile sidebar / logo flash on initial paint */
        @media (max-width: 900px) {
            .sidebar:not(.mobile-open) {
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                transform: translateX(-100%) !important;
            }
        }

        /* Apply expanded sidebar state on desktop only, BEFORE the
           body-level IIFE has had a chance to add .expanded to the sidebar. */
        @media (min-width: 901px) {
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
            html.sidebar-pre-expanded body:not(.preload-done) .sidebar ~ .main { margin-left: 220px; }
        }

        /* Ensure page loader / logo preloader / PWA banner is completely suppressed across all page navigation */
        #pageLoader,
        .page-loader,
        .loader-container,
        .loader-logo,
        #pwaInstallBanner,
        .pwa-banner {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
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
        : (in_array(auth()->user()->role, ['strategist', 'manager', 'editor', 'content_writer'], true) ? route('strategist.tracking') : (auth()->user()->role === 'developer' ? route('developer.tasks') : route('admin.tasks')));
@endphp

{{-- Page Loader Completely Disabled to prevent logo/loader flashing on page transitions --}}

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

{{-- Self-Task Creation Modal for Team Members --}}
<x-self-task-modal />

{{-- Universal Media Lightbox Modal --}}
<x-media-lightbox />

@if(auth()->check() && auth()->user()->role === 'admin')
{{-- Universal Dynamic Dropdown Options Manager Modal --}}
<x-dropdown-options-modal />
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
    if (link.closest('.sidebar')) return false;

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

// Page navigation loader completely disabled to ensure instant, seamless transitions without logo flash
// Form submission loader can still be used if explicitly desired

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

// Do not show loader on full-page refresh/navigation
window.addEventListener('beforeunload', () => {
    // Disabled to prevent logo flash when moving between pages
});

// Hide loader on browser back/forward (bfcache restore — must be instant, no delay)
window.addEventListener('pageshow', () => {
    if (pageLoader) {
        pageLoader.removeAttribute('data-fast');
    }
    hidePageLoader(true);
});

// ===== REAL-TIME LIVE NOTIFICATION & AUDIO CHIME ENGINE =====
(function() {
    'use strict';

    const POLL_INTERVAL = 12000; // 12 seconds
    let lastUnreadCount = parseInt(document.getElementById('notifCountBadge')?.textContent || '0', 10);
    const knownNotificationIds = new Set();
    let isInitialLoad = true;
    let isPolling = false;

    // ─────────── 1. WEB AUDIO API CHIME & CHROME AUTOPLAY UNLOCKER ───────────
    const TMSAudio = {
        ctx: null,
        unlocked: false,
        lastChimeTime: 0,

        getCtx() {
            if (!this.ctx) {
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                if (AudioContextClass) {
                    this.ctx = new AudioContextClass();
                }
            }
            return this.ctx;
        },

        unlock() {
            const ctx = this.getCtx();
            if (ctx && ctx.state === 'suspended') {
                ctx.resume().then(() => {
                    this.unlocked = true;
                    updateSoundUi();
                }).catch(() => {});
            } else if (ctx && ctx.state === 'running') {
                this.unlocked = true;
            }
        },

        /**
         * Generates a studio-grade two-tone harmonic chime
         * Tone 1: 587.33 Hz (D5) - warm bell onset
         * Tone 2: 880.00 Hz (A5) - crisp upper chime with overtone shimmer
         */
        playChime(isTest) {
            // Check user sound preference
            const isSoundEnabled = localStorage.getItem('tms_sound_enabled') !== 'false';
            if (!isSoundEnabled && !isTest) return;

            // Throttle consecutive chimes (minimum 1.5s between plays) unless testing
            const nowTime = Date.now();
            if (!isTest && (nowTime - this.lastChimeTime < 1500)) return;
            this.lastChimeTime = nowTime;

            this.unlock();
            const ctx = this.getCtx();

            if (ctx && ctx.state === 'running') {
                try {
                    const t = ctx.currentTime;

                    // Note 1: 587.33 Hz (D5)
                    const osc1 = ctx.createOscillator();
                    const gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(587.33, t);
                    gain1.gain.setValueAtTime(0.0001, t);
                    gain1.gain.exponentialRampToValueAtTime(0.24, t + 0.02);
                    gain1.gain.exponentialRampToValueAtTime(0.0001, t + 0.28);
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.start(t);
                    osc1.stop(t + 0.30);

                    // Note 2: 880.00 Hz (A5) - starts 90ms after Note 1
                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880.00, t + 0.09);
                    gain2.gain.setValueAtTime(0.0001, t + 0.09);
                    gain2.gain.exponentialRampToValueAtTime(0.30, t + 0.12);
                    gain2.gain.exponentialRampToValueAtTime(0.0001, t + 0.55);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(t + 0.09);
                    osc2.stop(t + 0.58);

                    // Subtle Harmonic Shimmer (1760 Hz, A6)
                    const osc3 = ctx.createOscillator();
                    const gain3 = ctx.createGain();
                    osc3.type = 'sine';
                    osc3.frequency.setValueAtTime(1760.00, t + 0.09);
                    gain3.gain.setValueAtTime(0.0001, t + 0.09);
                    gain3.gain.exponentialRampToValueAtTime(0.05, t + 0.12);
                    gain3.gain.exponentialRampToValueAtTime(0.0001, t + 0.40);
                    osc3.connect(gain3);
                    gain3.connect(ctx.destination);
                    osc3.start(t + 0.09);
                    osc3.stop(t + 0.42);

                    return;
                } catch (e) {
                    // Fall back to HTML5 Audio if Web Audio API threw
                }
            }

            // Fallback: HTML5 Audio element
            try {
                let audio = document.getElementById('tmsNotificationAudio');
                if (!audio) {
                    audio = document.createElement('audio');
                    audio.id = 'tmsNotificationAudio';
                    audio.src = '/sounds/notification.wav';
                    audio.preload = 'auto';
                    document.body.appendChild(audio);
                }
                audio.currentTime = 0;
                audio.play().catch(() => {});
            } catch (_) {}
        }
    };

    // Attach user gesture unlockers for Chrome Autoplay Policy
    ['click', 'pointerdown', 'keydown'].forEach(evt => {
        document.addEventListener(evt, () => TMSAudio.unlock(), { passive: true, once: false });
    });

    // ─────────── 2. SOUND UI CONTROLS ───────────
    function updateSoundUi() {
        const isSoundEnabled = localStorage.getItem('tms_sound_enabled') !== 'false';
        const toggleBtn = document.getElementById('tmsSoundToggleBtn');
        const icon = document.getElementById('tmsSoundIcon');
        const label = document.getElementById('tmsSoundLabel');

        if (toggleBtn) {
            if (isSoundEnabled) {
                toggleBtn.classList.remove('muted');
                toggleBtn.classList.add('active');
                if (icon) icon.className = 'fas fa-volume-up';
                if (label) label.textContent = 'Sound: ON';
            } else {
                toggleBtn.classList.remove('active');
                toggleBtn.classList.add('muted');
                if (icon) icon.className = 'fas fa-volume-mute';
                if (label) label.textContent = 'Sound: OFF';
            }
        }

        // Desktop permission button state
        const desktopBtn = document.getElementById('tmsDesktopPromptBtn');
        const desktopLabel = document.getElementById('tmsDesktopLabel');
        if (desktopBtn && 'Notification' in window) {
            if (Notification.permission === 'granted') {
                desktopBtn.classList.add('granted');
                desktopBtn.title = 'Desktop alerts enabled in Chrome';
                if (desktopLabel) desktopLabel.textContent = 'Alerts: ON';
            } else if (Notification.permission === 'denied') {
                desktopBtn.classList.remove('granted');
                desktopBtn.title = 'Notifications blocked in Chrome site settings';
                if (desktopLabel) desktopLabel.textContent = 'Alerts Blocked';
            } else {
                desktopBtn.classList.remove('granted');
                desktopBtn.title = 'Click to enable Chrome desktop notifications';
                if (desktopLabel) desktopLabel.textContent = 'Desktop Alerts';
            }
        }
    }

    window.toggleTmsSound = function() {
        const current = localStorage.getItem('tms_sound_enabled') !== 'false';
        const next = !current;
        localStorage.setItem('tms_sound_enabled', next ? 'true' : 'false');
        updateSoundUi();
        if (next) {
            TMSAudio.playChime(true);
        }
    };

    window.testTmsSound = function() {
        TMSAudio.playChime(true);
        const btn = document.querySelector('.notif-test-btn');
        if (btn) {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check" style="color:#059669"></i> <span style="color:#059669">Chime Played!</span>';
            setTimeout(() => { if (btn) btn.innerHTML = originalHtml; }, 1800);
        }
    };

    window.requestTmsDesktopPermission = function() {
        if (!('Notification' in window)) {
            alert('Desktop notifications are not supported in this browser.');
            return;
        }
        Notification.requestPermission().then(p => {
            updateSoundUi();
            if (p === 'granted') {
                try {
                    new Notification('TheLayout TMS Desktop Alerts Active', {
                        body: 'You will receive notifications and sound alerts here even when Chrome is minimized.',
                        icon: '/images/icon.png'
                    });
                    TMSAudio.playChime(true);
                } catch (_) {}
            }
        });
    };

    window.playTmsNotificationChime = function(force) {
        TMSAudio.playChime(!!force);
    };

    // ─────────── 3. DESKTOP NOTIFICATIONS ───────────
    function showDesktopAlert(item) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        if (document.hidden || !document.hasFocus()) {
            try {
                const title = item.title || 'New TMS Notification';
                const body = (item.subtitle ? item.subtitle + '\n' : '') + 'TheLayout Task Management';
                const n = new Notification(title, {
                    body: body,
                    icon: '/images/icon.png',
                    tag: 'tms-notif-' + (item.id || Date.now()),
                    silent: false
                });
                n.onclick = function() {
                    window.focus();
                    if (item.link && item.link !== '#') {
                        window.location.href = item.link;
                    }
                    n.close();
                };
                setTimeout(() => n.close(), 9000);
            } catch (_) {}
        }
    }

    // ─────────── 4. RICH IN-APP TOAST ───────────
    function showRichNotifToast(item, extraCount) {
        if (document.getElementById('notifPanel')?.classList.contains('open')) return;

        const existing = document.getElementById('notifToastLive');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'notifToastLive';
        toast.className = 'tms-live-toast';

        const extraBadge = extraCount > 0 ? `<span class="tms-toast-extra">+${extraCount} more</span>` : '';
        const titleText = escapeHtml(item?.title || 'New Notification');
        const subText = item?.subtitle ? `<div class="tms-toast-sub">${escapeHtml(item.subtitle)}</div>` : '';

        toast.innerHTML = `
            <div class="tms-toast-icon"><i class="fas fa-bell"></i></div>
            <div class="tms-toast-body">
                <div class="tms-toast-title">${titleText} ${extraBadge}</div>
                ${subText}
            </div>
            <button type="button" class="tms-toast-close" aria-label="Dismiss">&times;</button>
        `;

        toast.onclick = function(e) {
            if (e.target.closest('.tms-toast-close')) {
                e.stopPropagation();
                toast.remove();
                return;
            }
            toast.remove();
            if (item && item.link && item.link !== '#') {
                window.location.href = item.link;
            } else {
                openNotif();
            }
        };

        document.body.appendChild(toast);

        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-12px) scale(0.96)';
                toast.style.transition = 'all 0.35s ease';
                setTimeout(() => toast.remove(), 350);
            }
        }, 6000);
    }

    // ─────────── 5. BADGE & LIST RENDERING ───────────
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

        const footer = document.querySelector('.notif-footer');
        if (footer) footer.style.display = '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    // ─────────── 6. LIVE POLLING LOGIC ───────────
    function pollNotifications() {
        if (isPolling) return;
        isPolling = true;

        fetch('/api/notifications/poll', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => { if (!r.ok) throw new Error('Poll failed'); return r.json(); })
        .then(data => {
            const newCount = data.unread_count || 0;
            const notifs = data.notifications || [];

            // Populate known IDs on initial load
            if (isInitialLoad) {
                notifs.forEach(n => { if (n.id) knownNotificationIds.add(n.id); });
                lastUnreadCount = newCount;
                isInitialLoad = false;
                updateNotifBadge(newCount);
                updateSoundUi();
                return;
            }

            // Find brand-new notifications
            const freshItems = notifs.filter(n => n.id && !knownNotificationIds.has(n.id) && !n.read);
            const countIncreased = newCount > lastUnreadCount;

            if (freshItems.length > 0 || (countIncreased && lastUnreadCount >= 0)) {
                // Register newly seen IDs
                freshItems.forEach(n => knownNotificationIds.add(n.id));

                // 🔔 1. PLAY THE CHIME!
                TMSAudio.playChime(false);

                // 🖥️ 2. TRIGGER NATIVE DESKTOP NOTIFICATION (if tab is hidden or minimized)
                const leadItem = freshItems[0] || notifs[0] || { title: 'New Notification', subtitle: `${newCount} unread tasks` };
                showDesktopAlert(leadItem);

                // 🍞 3. SHOW RICH IN-APP TOAST
                showRichNotifToast(leadItem, Math.max(0, freshItems.length - 1));
            }

            updateNotifBadge(newCount);
            lastUnreadCount = newCount;

            // If notification panel is open, refresh the list in real-time
            if (document.getElementById('notifPanel')?.classList.contains('open')) {
                renderNotifList(notifs);
            }
        })
        .catch(() => { /* silent fail, will retry next interval */ })
        .finally(() => {
            isPolling = false;
        });
    }

    // Start background interval
    setInterval(pollNotifications, POLL_INTERVAL);

    // Instant check when user switches back to tab or focuses Chrome
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            TMSAudio.unlock();
            pollNotifications();
        }
    });

    window.addEventListener('focus', function() {
        TMSAudio.unlock();
        pollNotifications();
    });

    // Panel open hook
    const origOpen = window.openNotif;
    window.openNotif = function() {
        TMSAudio.unlock();
        origOpen();
        updateSoundUi();
        fetch('/api/notifications/poll', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(data => {
            renderNotifList(data.notifications);
            lastUnreadCount = 0;
            if (data.notifications) {
                data.notifications.forEach(n => { if (n.id) knownNotificationIds.add(n.id); });
            }
        }).catch(() => {});
    };

    // Initialize UI on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            updateSoundUi();
            pollNotifications();
        });
    } else {
        updateSoundUi();
        pollNotifications();
    }
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

// PWA + Firebase Messaging Service Worker Registration
// (single file at '/' scope — Firebase getToken() needs this exact
// registration to deliver background/closed-browser push notifications)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/firebase-messaging-sw.js')
            .then(reg => console.log('Service Worker registered', reg))
            .catch(err => console.error('Service Worker registration failed', err));
    });
}

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

.tms-live-toast {
    position: fixed;
    top: 24px;
    right: 24px;
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);
    color: #fff;
    padding: 13px 18px;
    border-radius: 14px;
    z-index: 100001;
    font-size: 13px;
    box-shadow: 0 14px 40px rgba(49, 46, 129, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.18) inset;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    max-width: 380px;
    animation: tmsToastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.tms-live-toast:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 48px rgba(49, 46, 129, 0.55), 0 0 0 1px rgba(255, 255, 255, 0.28) inset;
}
.tms-toast-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.16);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #fde047;
    flex-shrink: 0;
}
.tms-toast-body {
    flex: 1;
    min-width: 0;
}
.tms-toast-title {
    font-weight: 700;
    font-size: 13px;
    line-height: 1.35;
    color: #ffffff;
    display: flex;
    align-items: center;
    gap: 6px;
}
.tms-toast-extra {
    font-size: 10px;
    background: rgba(255, 255, 255, 0.22);
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: 700;
}
.tms-toast-sub {
    font-size: 11.5px;
    color: #c7d2fe;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.tms-toast-close {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.6);
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 4px;
}
.tms-toast-close:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.15);
}
@keyframes tmsToastSlideIn {
    from { opacity: 0; transform: translateY(-16px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>
<script>
    const firebaseConfig = {
      apiKey: "AIzaSyCM1JPND9WPN_8l-rWQl4U1ppeV8r0MZEw",
      authDomain: "tms-task-4e9b9.firebaseapp.com",
      projectId: "tms-task-4e9b9",
      storageBucket: "tms-task-4e9b9.firebasestorage.app",
      messagingSenderId: "169931045249",
      appId: "1:169931045249:web:61ba9c60a44618019bd080"
    };

    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    function requestNotificationPermission() {
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                console.log('Notification permission granted.');
                messaging.getToken({ vapidKey: "BPTU9GIYzO8MN52n2AAn2txDU27OZpTfGUp6jg59M9017fCbEMdyHbPVjaqd1EW0sib1Ci-8uU7i_v6u-QO1CzI" }).then((currentToken) => {
                    if (currentToken) {
                        console.log('FCM Token:', currentToken);
                        saveTokenToServer(currentToken);
                    } else {
                        console.log('No registration token available.');
                    }
                }).catch((err) => {
                    console.log('An error occurred while retrieving token. ', err);
                });
            } else {
                console.log('Unable to get permission to notify.');
            }
        });
    }

    function saveTokenToServer(token) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if(!csrf) return;
        fetch('/api/notifications/save-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ token: token })
        }).then(res => res.json()).then(data => console.log('Token saved', data)).catch(err => console.log(err));
    }

    // Request permission when page loads
    document.addEventListener('DOMContentLoaded', () => {
        if (Notification.permission === 'default' || Notification.permission === 'granted') {
            requestNotificationPermission();
        }
    });

    messaging.onMessage((payload) => {
        console.log('Message received. ', payload);
        const notificationTitle = payload.notification.title;
        const notificationOptions = {
            body: payload.notification.body,
            icon: '/images/logo.png'
        };
        new Notification(notificationTitle, notificationOptions);
        if (typeof window.playTmsNotificationChime === 'function') {
            window.playTmsNotificationChime();
        }
    });
</script>
<audio id="tmsNotificationAudio" src="{{ asset('sounds/notification.wav') }}" preload="auto" style="display:none"></audio>
@stack('scripts')
</body>
</html>
