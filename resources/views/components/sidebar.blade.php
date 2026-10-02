{{-- resources/views/components/sidebar.blade.php --}}
@php
    $role = auth()->user()->role;
    $initial = strtoupper(substr(auth()->user()->name, 0, 1));
    $userName = auth()->user()->name;
    $avatarColor = auth()->user()->avatar_color ?? '#4F6DF0';
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $pendingAssignedCount = \App\Models\Task::query()
        ->where('assigned_to', auth()->id())
        ->where('status', '!=', 'completed')
        ->where('status', '!=', 'published')
        ->count();
    $pendingAssignedByAdminCount = \App\Models\Task::query()
        ->where('assigned_to', auth()->id())
        ->where('status', '!=', 'completed')
        ->where('status', '!=', 'published')
        ->whereHas('creator', fn ($q) => $q->where('role', 'admin'))
        ->count();

    // Count pending task approvals (for admins)
    $pendingTaskApprovalsCount = auth()->user()->role === 'admin'
        ? \App\Models\Task::query()->where('status', 'pending_approval')->count()
        : 0;

    // Count tasks awaiting proof (completed but not published)
    $awaitingProofCount = auth()->user()->role === 'strategist'
        ? \App\Models\Task::query()
            ->whereHas('creator', fn ($q) => $q->where('role', 'strategist'))
            ->where('is_urgent_task', false)
            ->where('status', 'completed')
            ->count()
        : 0;
    $adminAwaitingProofCount = auth()->user()->role === 'admin'
        ? \App\Models\Task::query()->where('is_urgent_task', false)->where('status', 'completed')->count()
        : 0;

    // Urgent tasks self-created by designers in last 48h (admin-only — count for badge)
    $urgentFromDesignersCount = auth()->user()->role === 'admin'
        ? \App\Models\Task::where('is_urgent_task', true)
            ->whereHas('creator', fn ($q) => $q->where('role', 'designer'))
            ->where('created_at', '>=', now()->subDays(2))
            ->count()
        : 0;

    // Unread chat messages for this user (clients do not have chat access)
    $unreadMessages = $role === 'client'
        ? 0
        : \App\Models\Message::query()
            ->where('user_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->whereHas('conversation', function ($q) {
                $myId = auth()->id();
                $q->where('user_one_id', $myId)->orWhere('user_two_id', $myId);
            })
            ->count();
@endphp

<aside class="sidebar" id="appSidebar">
    <div class="sidebar-brand" onclick="if(window.innerWidth > 900) toggleSidebar()" style="cursor:pointer" title="Toggle sidebar">
        <img src="{{ asset('images/logo.png') }}" alt="The Layout" class="brand-logo">
    </div>

    @if($role === 'admin')
        <div class="nav-section">
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                <span class="icon"><i class="fas fa-th-large"></i></span>
                <span class="nav-text">Dashboard</span>
                <span class="nav-tooltip">Dashboard</span>
            </a>
            <a href="{{ route('admin.tasks.create') }}" class="nav-item {{ request()->routeIs('admin.tasks.create') ? 'active' : '' }}" title="Create New Task">
                <span class="icon"><i class="fas fa-plus-circle" style="color:var(--primary)"></i></span>
                <span class="nav-text">Create Task</span>
                <span class="nav-tooltip">Create Task</span>
            </a>
            <a href="{{ route('admin.urgent-tasks') }}" class="nav-item {{ request()->routeIs('admin.urgent-tasks') ? 'active' : '' }}" title="Urgent Tasks from Designers" style="{{ $urgentFromDesignersCount > 0 ? 'color:#DC2626' : '' }}">
                <span class="icon" style="position:relative">
                    <i class="fas fa-bolt" style="color:#EF4444"></i>
                    @if($urgentFromDesignersCount > 0)
                        <span class="urgent-away-pulse"></span>
                    @endif
                </span>
                <span class="nav-text">Urgent Tasks</span>
                <span class="nav-tooltip">Urgent from Designers</span>
                @if($urgentFromDesignersCount > 0)
                    <span class="nav-badge" style="margin-left:auto;background:#EF4444;color:#fff;padding:2px 7px;border-radius:10px;font-size:11px;font-weight:700">{{ $urgentFromDesignersCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.tasks') }}" class="nav-item {{ request()->routeIs('admin.tasks*') && !request()->routeIs('admin.urgent-tasks') ? 'active' : '' }}" title="All Tasks">
                <span class="icon"><i class="fas fa-tasks"></i></span>
                <span class="nav-text">All Tasks</span>
                <span class="nav-tooltip">All Tasks</span>
            </a>
            <a href="{{ route('admin.calendar') }}" class="nav-item {{ request()->routeIs('admin.calendar') ? 'active' : '' }}" title="Content Calendar">
                <span class="icon"><i class="fas fa-calendar-alt"></i></span>
                <span class="nav-text">Calendar</span>
                <span class="nav-tooltip">Calendar</span>
            </a>
            <a href="{{ route('admin.shoot-days') }}" class="nav-item {{ request()->routeIs('admin.shoot-days*') ? 'active' : '' }}" title="Schedule a Photoshoot">
                <span class="icon"><i class="fas fa-camera"></i></span>
                <span class="nav-text">Photoshoots</span>
                <span class="nav-tooltip">Schedule a Photoshoot</span>
            </a>
        </div>
        <div class="nav-section">
            <a href="{{ route('admin.workload') }}" class="nav-item {{ request()->routeIs('admin.workload') ? 'active' : '' }}" title="Workload View">
                <span class="icon"><i class="fas fa-users"></i></span>
                <span class="nav-text">Workload</span>
                <span class="nav-tooltip">Workload</span>
            </a>
            <a href="{{ route('admin.clients') }}" class="nav-item {{ request()->routeIs('admin.clients*') ? 'active' : '' }}" title="Client Overview">
                <span class="icon"><i class="fas fa-briefcase"></i></span>
                <span class="nav-text">Clients</span>
                <span class="nav-tooltip">Clients</span>
            </a>
            <a href="{{ route('admin.visits') }}" class="nav-item {{ request()->routeIs('admin.visits*') ? 'active' : '' }}" title="Admin's Client & Lead Visits">
                <span class="icon"><i class="fa-solid fa-person-walking-luggage"></i></span>
                <span class="nav-text">Client Visits</span>
                <span class="nav-tooltip">Client & Lead Visits</span>
            </a>
            <a href="{{ route('admin.date-change-requests') }}" class="nav-item {{ request()->routeIs('admin.date-change-requests') ? 'active' : '' }}" title="Date Change Requests">
                <span class="icon"><i class="fas fa-calendar-check"></i></span>
                <span class="nav-text">Date Requests</span>
                <span class="nav-tooltip">Date Requests</span>
            </a>
            <a href="{{ route('admin.approvals') }}" class="nav-item {{ request()->routeIs('admin.approvals*') ? 'active' : '' }}" title="Approvals">
                <span class="icon"><i class="fas fa-check-circle"></i></span>
                <span class="nav-text">Approvals</span>
                <span class="nav-tooltip">Approvals</span>
                @if($pendingTaskApprovalsCount > 0)
                    <span class="nav-badge" style="margin-left:auto;background:#ef4444;padding:2px 6px;border-radius:10px;font-size:11px;font-weight:600">{{ $pendingTaskApprovalsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.publishing') }}" class="nav-item {{ request()->routeIs('admin.publishing*') ? 'active' : '' }}" title="Publishing Tracker">
                <span class="icon"><i class="fas fa-bullhorn"></i></span>
                <span class="nav-text">Publishing</span>
                <span class="nav-tooltip">Publishing</span>
                @if($adminAwaitingProofCount > 0)
                    <span class="nav-badge" style="margin-left:auto;background:#d97706;padding:2px 6px;border-radius:10px;font-size:11px;font-weight:600">{{ $adminAwaitingProofCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.report') }}" class="nav-item {{ request()->routeIs('admin.report') ? 'active' : '' }}" title="Monthly Report">
                <span class="icon"><i class="fas fa-chart-bar"></i></span>
                <span class="nav-text">Report</span>
                <span class="nav-tooltip">Report</span>
            </a>
            <a href="{{ route('admin.team') }}" class="nav-item {{ request()->routeIs('admin.team') ? 'active' : '' }}" title="Team Management">
                <span class="icon"><i class="fas fa-user-tie"></i></span>
                <span class="nav-text">Team</span>
                <span class="nav-tooltip">Team</span>
            </a>
            {{--<a href="{{ route('admin.members') }}" class="nav-item {{ request()->routeIs('admin.members') ? 'active' : '' }}" title="Members">
                <span class="icon"><i class="fas fa-user-friends"></i></span>
                <span class="nav-text">Members</span>
                <span class="nav-tooltip">Members</span>
            </a>--}}
            <a href="{{ route('admin.action-items') }}" class="nav-item {{ request()->routeIs('admin.action-items') ? 'active' : '' }}" title="Notes">
                <span class="icon"><i class="fas fa-thumbtack"></i></span>
                <span class="nav-text">Notes</span>
                <span class="nav-tooltip">Notes</span>
                @php $adminOverdueActions = \App\Models\ActionItem::where('created_by', auth()->id())->where('status', '!=', 'done')->where('due_at', '<', now())->count(); @endphp
                @if($adminOverdueActions > 0)
                    <span class="nav-badge" style="margin-left:auto;background:#ef4444;padding:2px 6px;border-radius:10px;font-size:11px;font-weight:600">{{ $adminOverdueActions }}</span>
                @endif
            </a>
            <a href="{{ route('admin.festival-calendar') }}" class="nav-item {{ request()->routeIs('admin.festival-calendar') ? 'active' : '' }}" title="Festival Calendar">
                <span class="icon"><i class="fas fa-calendar-check"></i></span>
                <span class="nav-text">Festival Calendar</span>
                <span class="nav-tooltip">Festival Calendar</span>
            </a>
            <a href="{{ route('admin.festival-selections') }}" class="nav-item {{ request()->routeIs('admin.festival-selections') ? 'active' : '' }}" title="Festival Selections">
                <span class="icon"><i class="fas fa-list-check"></i></span>
                <span class="nav-text">Festival Selections</span>
                <span class="nav-tooltip">Client Festival Selections</span>
            </a>
           {{-- <a href="{{ route('admin.active-users') }}" class="nav-item {{ request()->routeIs('admin.active-users') ? 'active' : '' }}" title="Active Users">
                <span class="icon"><i class="fas fa-circle" style="font-size:8px;color:#22c55e"></i></span>
                <span class="nav-text">Active Users</span>
                <span class="nav-tooltip">Active Users</span>
            </a>
            --}}
        </div>

    @elseif($role === 'strategist')
        <div class="nav-section">
            <a href="{{ route('strategist.dashboard') }}" class="nav-item {{ request()->routeIs('strategist.dashboard') ? 'active' : '' }}" title="Dashboard">
                <span class="icon"><i class="fas fa-th-large"></i></span>
                <span class="nav-text">Dashboard</span>
                <span class="nav-tooltip">Dashboard</span>
            </a>
            <a href="{{ route('strategist.create-task') }}" class="nav-item {{ request()->routeIs('strategist.create-task') ? 'active' : '' }}" title="Create Task">
                <span class="icon"><i class="fas fa-plus-circle"></i></span>
                <span class="nav-text">Create Task</span>
                <span class="nav-tooltip">Create Task</span>
            </a>
            <a href="{{ route('strategist.calendar') }}" class="nav-item {{ request()->routeIs('strategist.calendar') ? 'active' : '' }}" title="Content Calendar">
                <span class="icon"><i class="fas fa-calendar-alt"></i></span>
                <span class="nav-text">Calendar</span>
                <span class="nav-tooltip">Calendar</span>
            </a>
            <a href="{{ route('strategist.shoot-days') }}" class="nav-item {{ request()->routeIs('strategist.shoot-days*') ? 'active' : '' }}" title="Schedule a Photoshoot">
                <span class="icon"><i class="fas fa-camera"></i></span>
                <span class="nav-text">Photoshoots</span>
                <span class="nav-tooltip">Schedule a Photoshoot</span>
            </a>
            <a href="{{ route('strategist.festival-calendar') }}"
   class="nav-item {{ request()->routeIs('strategist.festival-calendar') ? 'active' : '' }}"
   title="Festival Calendar">

    <span class="icon">
        <i class="fas fa-calendar-check"></i>

    </span>

    <span class="nav-text">Festival Calendar</span>
    <span class="nav-tooltip">Festival Calendar</span>
</a>
            <a href="{{ route('strategist.festival-selections') }}" class="nav-item {{ request()->routeIs('strategist.festival-selections') ? 'active' : '' }}" title="Festival Selections">
                <span class="icon"><i class="fas fa-list-check"></i></span>
                <span class="nav-text">Festival Selections</span>
                <span class="nav-tooltip">Client Festival Selections</span>
            </a>
            <a href="{{ route('strategist.content-schedules') }}" class="nav-item {{ request()->routeIs('strategist.content-schedules') ? 'active' : '' }}" title="Content Schedules">
                <span class="icon"><i class="fas fa-chart-bar"></i></span>
                <span class="nav-text">Content Schedules</span>
                <span class="nav-tooltip">Monthly Content Schedules</span>
            </a>
        </div>
        <div class="nav-section">

            <a href="{{ route('strategist.tracking') }}" class="nav-item {{ request()->routeIs('strategist.tracking') ? 'active' : '' }}" title="Task Tracking">
                <span class="icon"><i class="fas fa-route"></i></span>
                <span class="nav-text">Tracking</span>
                <span class="nav-tooltip">Tracking</span>
                @if($pendingAssignedCount > 0)
                    <span class="nav-badge-dot"></span>
                @endif
            </a>
            <a href="{{ route('strategist.approvals') }}" class="nav-item {{ request()->routeIs('strategist.approvals') ? 'active' : '' }}" title="Approval Tracker" data-force-loader>
                <span class="icon"><i class="fas fa-bell"></i></span>
                <span class="nav-text">Approvals</span>
                <span class="nav-tooltip">Approvals</span>
            </a>
            <a href="{{ route('strategist.publishing') }}" class="nav-item {{ request()->routeIs('strategist.publishing*') ? 'active' : '' }}" title="Publishing Queue">
                <span class="icon"><i class="fas fa-bullhorn"></i></span>
                <span class="nav-text">Publishing</span>
                <span class="nav-tooltip">Publishing</span>
                @if($awaitingProofCount > 0)
                    <span class="nav-badge" style="margin-left:auto;background:#d97706;padding:2px 6px;border-radius:10px;font-size:11px;font-weight:600">{{ $awaitingProofCount }}</span>
                @endif
            </a>
            <a href="{{ route('strategist.action-items') }}" class="nav-item {{ request()->routeIs('strategist.action-items') ? 'active' : '' }}" title="Notes">
                <span class="icon"><i class="fas fa-thumbtack"></i></span>
                <span class="nav-text">Notes</span>
                <span class="nav-tooltip">Notes</span>
                @php $myPendingActions = \App\Models\ActionItem::where('assigned_to', auth()->id())->whereIn('status', ['pending', 'in_progress'])->count(); @endphp
                @if($myPendingActions > 0)
                    <span class="nav-badge" style="margin-left:auto;background:#ef4444;padding:2px 6px;border-radius:10px;font-size:11px;font-weight:600">{{ $myPendingActions }}</span>
                @endif
            </a>
        </div>



    @elseif($role === 'developer')
        <div class="nav-section">
            <a href="{{ route('developer.dashboard') }}" class="nav-item {{ request()->routeIs('developer.dashboard') ? 'active' : '' }}" title="Dashboard">
                <span class="icon"><i class="fas fa-th-large"></i></span>
                <span class="nav-text">Dashboard</span>
                <span class="nav-tooltip">Dashboard</span>
            </a>
        </div>
        <div class="nav-section">

            <a href="{{ route('developer.tasks') }}" class="nav-item {{ request()->routeIs('developer.tasks') ? 'active' : '' }}" title="My Tasks">
                <span class="icon"><i class="fas fa-code"></i></span>
                <span class="nav-text">My Tasks</span>
                <span class="nav-tooltip">My Tasks</span>
                @if($pendingAssignedCount > 0)
                    <span class="nav-badge-dot"></span>
                @endif
            </a>
            <a href="{{ route('developer.calendar') }}" class="nav-item {{ request()->routeIs('developer.calendar') ? 'active' : '' }}" title="My Calendar">
                <span class="icon"><i class="fas fa-calendar-alt"></i></span>
                <span class="nav-text">Calendar</span>
                <span class="nav-tooltip">Calendar</span>
            </a>

            <a href="{{ route('developer.skills') }}"
                class="nav-item {{ request()->routeIs('developer.skills') ? 'active' : '' }}"
                title="My Skills">

                <span class="icon"><i class="fas fa-bolt"></i></span>
                <span class="nav-text">My Skills</span>
                <span class="nav-tooltip">My Skills</span>
            </a>
        </div>




    @elseif($role === 'designer')
        <div class="nav-section">
            <a href="{{ route('designer.dashboard') }}" class="nav-item {{ request()->routeIs('designer.dashboard') ? 'active' : '' }}" title="My Dashboard">
                <span class="icon"><i class="fas fa-th-large"></i></span>
                <span class="nav-text">Dashboard</span>
                <span class="nav-tooltip">Dashboard</span>
            </a>
            <a href="{{ route('designer.tasks') }}" class="nav-item {{ request()->routeIs('designer.tasks') ? 'active' : '' }}" title="My Tasks">
                <span class="icon"><i class="fas fa-paint-brush"></i></span>
                <span class="nav-text">My Tasks</span>
                <span class="nav-tooltip">My Tasks</span>
                @if($pendingAssignedCount > 0)
                    <span class="nav-badge-dot"></span>
                @endif
            </a>
            <a href="{{ route('designer.urgent-task') }}" class="nav-item {{ request()->routeIs('designer.urgent-task*') ? 'active' : '' }}" title="Create Urgent Task">
                <span class="icon"><i class="fas fa-bolt" style="color:var(--dz-red, #EF4444)"></i></span>
                <span class="nav-text">Urgent Task</span>
                <span class="nav-tooltip">Create Urgent Task</span>
            </a>
            <a href="{{ route('designer.calendar') }}" class="nav-item {{ request()->routeIs('designer.calendar') ? 'active' : '' }}" title="My Calendar">
                <span class="icon"><i class="fas fa-calendar-alt"></i></span>
                <span class="nav-text">Calendar</span>
                <span class="nav-tooltip">Calendar</span>
            </a>
            <a href="{{ route('designer.performance') }}" class="nav-item {{ request()->routeIs('designer.performance') ? 'active' : '' }}" title="My Performance">
                <span class="icon"><i class="fas fa-chart-line"></i></span>
                <span class="nav-text">Performance</span>
                <span class="nav-tooltip">Performance</span>
            </a>
        </div>
    @elseif($role === 'client')
        <div class="nav-section">
            <a href="{{ route('client.dashboard') }}" class="nav-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" title="Dashboard">
                <span class="icon"><i class="fas fa-th-large"></i></span>
                <span class="nav-text">Dashboard</span>
                <span class="nav-tooltip">Dashboard</span>
            </a>
            <a href="{{ route('client.published-content') }}" class="nav-item {{ request()->routeIs('client.published-content') ? 'active' : '' }}" title="Published Content">
                <span class="icon"><i class="fas fa-bullhorn"></i></span>
                <span class="nav-text">Published</span>
                <span class="nav-tooltip">Published Content</span>
            </a>
            <a href="{{ route('client.content-schedule') }}" class="nav-item {{ request()->routeIs('client.content-schedule') ? 'active' : '' }}" title="Content Schedule">
                <span class="icon"><i class="fas fa-chart-bar"></i></span>
                <span class="nav-text">Content Schedule</span>
                <span class="nav-tooltip">Monthly Content Schedule</span>
            </a>
            <a href="{{ route('client.festivals') }}" class="nav-item {{ request()->routeIs('client.festivals') ? 'active' : '' }}" title="Festival Calendar">
                <span class="icon"><i class="fas fa-calendar-alt"></i></span>

                <span class="nav-text">Festivals</span>
                <span class="nav-tooltip">Festival Calendar</span>
            </a>
            <a href="{{ route('client.social-analysis') }}" class="nav-item {{ request()->routeIs('client.social-analysis') ? 'active' : '' }}" title="Social Analysis">
                <span class="icon"><i class="fas fa-chart-pie"></i></span>
                <span class="nav-text">Social Analysis</span>
                <span class="nav-tooltip">Social Analysis</span>
            </a>
        </div>
    @endif

    @if(auth()->user()->isStrategist() || auth()->user()->canManageSocialMetrics())
        <div class="nav-section">
            <a href="{{ route('social-metrics.panel') }}" class="nav-item {{ request()->routeIs('social-metrics.panel') ? 'active' : '' }}" title="Social Metrics Panel">
                <span class="icon"><i class="fas fa-chart-line"></i></span>
                <span class="nav-text">Social Metrics</span>
                <span class="nav-tooltip">Social Metrics</span>
            </a>
        </div>
    @endif




    @if($role !== 'client')
        {{-- Chat (internal roles only) --}}
        <div class="nav-section">
            <a href="{{ route('chat.index') }}" class="nav-item {{ request()->routeIs('chat.*') ? 'active' : '' }}" title="Messages">
                <span class="icon" style="position:relative">
                    <i class="fas fa-comments"></i>
                </span>
                <span class="nav-text">Messages</span>
                <span class="nav-tooltip">Messages</span>
                @if($unreadMessages > 0)
                    <span class="nav-badge" style="margin-left:auto;background:#EF4444;color:#fff;padding:2px 7px;border-radius:10px;font-size:11px;font-weight:700">{{ $unreadMessages > 99 ? '99+' : $unreadMessages }}</span>
                @endif
            </a>
        </div>
    @endif

    <div class="nav-section nav-section-logout">
        <form method="POST" action="{{ route('logout') }}" style="margin:0;width:100%;display:flex;justify-content:center" id="sidebarLogoutForm" data-logout-form data-no-loader data-user-name="{{ $userName }}" data-user-role="{{ $role }}">
            @csrf
            <button type="submit" class="nav-item nav-item-logout" title="Logout">
                <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                <span class="nav-text">Logout</span>
                <span class="nav-tooltip">Logout</span>
            </button>
        </form>
    </div>

    <div class="sidebar-footer">
        <div class="user-pill" onclick="collapseSidebar()" style="cursor:pointer" title="Collapse sidebar">
            <div class="avatar" style="background:{{ $avatarColor }}">{{ $initial }}</div>
            <div>
                <div class="user-name">{{ $userName }}</div>
                <div class="user-sub">{{ $greeting }}</div>
            </div>
            <span class="nav-tooltip">{{ $userName }}</span>
        </div>
    </div>
</aside>

{{-- Pulse animation for urgent alerts badge (used by admin sidebar nav item) --}}
<style>
.urgent-away-pulse {
    position:absolute; top:-3px; right:-3px;
    width:8px; height:8px; border-radius:50%;
    background:#EF4444;
    box-shadow:0 0 0 0 rgba(239,68,68,0.6);
    animation:urgentAwayPulse 1.8s ease-in-out infinite;
}
@keyframes urgentAwayPulse {
    0%,100% { box-shadow:0 0 0 0 rgba(239,68,68,0.6); }
    50% { box-shadow:0 0 0 7px rgba(239,68,68,0); }
}

/* Sidebar Logout Button styling with explicit Red text */
.sidebar form#sidebarLogoutForm {
    width: 100%;
    margin: 0;
    display: flex;
    justify-content: center;
}
.sidebar .nav-item-logout,
.sidebar button.nav-item-logout {
    color: #EF4444 !important;
    background: transparent !important;
    border: 1px solid transparent !important;
    cursor: pointer !important;
    font-family: inherit !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    padding: 0;
    position: relative;
    box-shadow: none !important;
}
.sidebar .nav-item-logout .icon,
.sidebar .nav-item-logout .icon i {
    color: #EF4444 !important;
    font-size: 18px;
    transition: all 0.2s ease;
}
.sidebar .nav-item-logout .nav-text {
    color: #EF4444 !important;
    font-weight: 700 !important;
    letter-spacing: 0.2px;
}
.sidebar .nav-item-logout:hover,
.sidebar button.nav-item-logout:hover {
    background: rgba(239, 68, 68, 0.09) !important;
    border-color: rgba(239, 68, 68, 0.25) !important;
    transform: scale(1.06) !important;
}
.sidebar .nav-item-logout:hover .icon,
.sidebar .nav-item-logout:hover .icon i,
.sidebar .nav-item-logout:hover .nav-text {
    color: #DC2626 !important;
}
.sidebar.expanded .nav-section-logout {
    padding: 2px 10px;
}
.sidebar.expanded form#sidebarLogoutForm {
    width: 100%;
    display: block;
}
.sidebar.expanded .nav-item-logout,
.sidebar.expanded button.nav-item-logout {
    width: 100% !important;
    height: auto !important;
    padding: 9px 12px !important;
    justify-content: flex-start !important;
    gap: 10px !important;
    font-size: 13px !important;
    border-radius: 10px !important;
    display: flex !important;
}
.sidebar.expanded .nav-item-logout .icon {
    width: 22px;
    text-align: center;
    flex-shrink: 0;
}
.sidebar.expanded .nav-item-logout .nav-text {
    display: inline !important;
}
.sidebar.expanded .nav-item-logout:hover {
    transform: translateX(3px) !important;
}
.sidebar.mobile-open form#sidebarLogoutForm {
    width: 100%;
}
.sidebar.mobile-open .nav-item-logout,
.sidebar.mobile-open button.nav-item-logout {
    width: 100% !important;
    height: auto !important;
    padding: 12px 16px !important;
    justify-content: flex-start !important;
    gap: 14px !important;
    font-size: 14px !important;
    display: flex !important;
}
.sidebar.mobile-open .nav-item-logout .nav-text {
    display: inline !important;
}
</style>
