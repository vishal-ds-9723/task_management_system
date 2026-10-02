@php
// Lightweight: just the badge count (overdue + unassigned). Everything else
// loads via /api/chatbot/tab-data when the chatbot is opened. The wizard's
// client/user lists load via /api/chatbot/wizard-data on first wizard start.
// Cached 60s — runs on every admin page load otherwise.
$badgeCount = \Illuminate\Support\Facades\Cache::remember('admin.chatbot.badge_count', 60, fn() =>
    (int) (\App\Models\Task::selectRaw("
        SUM(CASE WHEN deadline < NOW() AND status NOT IN ('completed','published') THEN 1 ELSE 0 END)
        + SUM(CASE WHEN assigned_to IS NULL THEN 1 ELSE 0 END) as n
    ")->value('n') ?? 0)
);

$hour     = (int) now()->format('H');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
@endphp

<!-- ═══════════ ADMIN CHATBOT — SARAH ASSISTANT ═══════════ -->
<div id="adminChatbot" class="acb">

    <!-- Toggle Button -->
    <button class="acb-toggle" onclick="acbToggle()" aria-label="Open Sarah Assistant" title="Open Sarah Assistant">
        <i class="fa-solid fa-headset acb-toggle-icon"></i>
        @if($badgeCount > 0)
            <span class="acb-badge">{{ $badgeCount > 99 ? '99+' : $badgeCount }}</span>
        @endif
    </button>

    <!-- Backdrop -->
    <div class="acb-backdrop" onclick="acbToggle()"></div>

    <!-- Chat Window -->
    <div id="acbWindow" class="acb-window">

        <!-- Header -->
        <div class="acb-header">
            <div class="acb-avatar"><i class="fa-solid fa-headset"></i></div>
            <div class="acb-header-info">
                <div class="acb-header-name">Sarah Assistant</div>
                <div class="acb-header-status"><span class="acb-online-dot"></span> Online</div>
            </div>
            <button class="acb-header-btn" onclick="acbRefresh()" title="Refresh data"><i class="fa-solid fa-arrows-rotate"></i></button>
            <button class="acb-header-btn" onclick="acbToggle()" title="Close (Esc)"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Tabs -->
        <div class="acb-tabs">
            <button class="acb-tab active" data-tab="overview" onclick="acbSwitchTab('overview')">
                <i class="fa-solid fa-chart-pie"></i> Overview
            </button>
            <button class="acb-tab" data-tab="assistant" onclick="acbSwitchTab('assistant')">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Assistant
            </button>
            <button class="acb-tab" data-tab="tasks" onclick="acbSwitchTab('tasks')">
                <i class="fa-solid fa-list-check"></i> Tasks
            </button>
            <button class="acb-tab" data-tab="clients" onclick="acbSwitchTab('clients')">
                <i class="fa-solid fa-building"></i> Clients
            </button>
            <button class="acb-tab" data-tab="reports" onclick="acbSwitchTab('reports')">
                <i class="fa-solid fa-chart-bar"></i> Reports
            </button>
        </div>

        <!-- Content -->
        <div class="acb-content" id="acbContent">

            <!-- Loading Overlay -->
            <div class="acb-loading" id="acbLoading">
                <div class="acb-spinner"></div>
            </div>

            <!-- ══ ASSISTANT TAB ══ -->
            <div id="acbAssistant" class="acb-pane">
                <!-- Wizard progress bar (visible only during wizard) -->
                <div id="acbWizardBar" class="acb-wizard-bar" style="display:none">
                    <button class="acb-wiz-back" id="acbWizBackBtn" onclick="wizardBack()" title="Go back one step">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <div class="acb-wiz-info">
                        <div class="acb-wiz-label"><span id="acbWizStepNum">Step 1</span><span class="acb-wiz-sep"> · </span><span id="acbWizStepName">Task Title</span></div>
                        <div class="acb-wiz-track"><div id="acbWizFill" class="acb-wiz-fill"></div></div>
                    </div>
                    <button class="acb-wiz-cancel" onclick="wizardCancel()" title="Cancel wizard">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="acb-chat-container">
                    <div class="acb-chat-history" id="acbChatHistory">
                        <div class="acb-chat-msg bot">
                            <div class="acb-msg-bubble">
                                Hello! I'm Sarah. I can help you manage your workspace.
                                <div style="margin-top:8px;font-weight:700"> Say "help" to get started.</div>
                            </div>
                        </div>
                    </div>
                    <div class="acb-chat-input-area">
                        <input type="text" id="acbChatInput" placeholder="Type a command or ask something..." onkeydown="if(event.key==='Enter') acbSendChat()">
                        <button class="acb-chat-send" onclick="acbSendChat()"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>

            <!-- ══ OVERVIEW TAB ══ -->
            <div id="acbOverview" class="acb-pane active">
                <div class="acb-greeting">
                    <span class="acb-greeting-text">{{ $greeting }}, Admin!</span>
                    <span class="acb-greeting-sub">Here's your workspace at a glance</span>
                </div>

                <div class="acb-stat-grid">
                    <div class="acb-stat-card acb-clickable" onclick="acbDrillDown('tasks','all','All Tasks')" title="View all tasks">
                        <div class="acb-stat-icon" style="background:rgba(var(--primary-rgb),.1);color:var(--primary)"><i class="fa-solid fa-layer-group"></i></div>
                        <div class="acb-stat-info"><div class="acb-stat-val" id="valTotalTasks">–</div><div class="acb-stat-lbl">Total Tasks</div></div>
                        <i class="fa-solid fa-chevron-right acb-stat-arrow"></i>
                    </div>
                    <div class="acb-stat-card acb-clickable" onclick="acbDrillDown('tasks','inprogress','In Progress')" title="View in-progress tasks">
                        <div class="acb-stat-icon" style="background:rgba(59,130,246,.1);color:#3b82f6"><i class="fa-solid fa-spinner"></i></div>
                        <div class="acb-stat-info"><div class="acb-stat-val" id="valInProgress">–</div><div class="acb-stat-lbl">In Progress</div></div>
                        <i class="fa-solid fa-chevron-right acb-stat-arrow"></i>
                    </div>
                    <div class="acb-stat-card acb-clickable" onclick="acbDrillDown('tasks','review','Pending Review')" title="View review tasks">
                        <div class="acb-stat-icon" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="fa-solid fa-eye"></i></div>
                        <div class="acb-stat-info"><div class="acb-stat-val" id="valPendingReview">–</div><div class="acb-stat-lbl">Pending Review</div></div>
                        <i class="fa-solid fa-chevron-right acb-stat-arrow"></i>
                    </div>
                    <div class="acb-stat-card acb-clickable" onclick="acbDrillDown('tasks','completed_week','Completed This Week')" title="View completed tasks">
                        <div class="acb-stat-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="acb-stat-info"><div class="acb-stat-val" id="valCompleted">–</div><div class="acb-stat-lbl">Completed (Week)</div></div>
                        <i class="fa-solid fa-chevron-right acb-stat-arrow"></i>
                    </div>
                    <div class="acb-stat-card acb-clickable" onclick="acbDrillDown('tasks','overdue','Overdue Tasks')" title="View overdue tasks" id="cardOverdue">
                        <div class="acb-stat-icon" style="background:rgba(239,68,68,.1);color:#ef4444"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div class="acb-stat-info"><div class="acb-stat-val" id="valOverdue">–</div><div class="acb-stat-lbl">Overdue</div></div>
                        <i class="fa-solid fa-chevron-right acb-stat-arrow"></i>
                    </div>
                    <div class="acb-stat-card acb-clickable" onclick="acbDrillDown('clients','all','All Clients')" title="View all clients">
                        <div class="acb-stat-icon" style="background:rgba(139,92,246,.1);color:#8b5cf6"><i class="fa-solid fa-building"></i></div>
                        <div class="acb-stat-info"><div class="acb-stat-val" id="valClients">–</div><div class="acb-stat-lbl">Clients</div></div>
                        <i class="fa-solid fa-chevron-right acb-stat-arrow"></i>
                    </div>
                    <div class="acb-stat-card acb-clickable" onclick="acbDrillDown('tasks','todo','Todo Tasks')" title="View todo tasks">
                        <div class="acb-stat-icon" style="background:rgba(107,114,128,.1);color:#6b7280"><i class="fa-solid fa-clipboard-list"></i></div>
                        <div class="acb-stat-info"><div class="acb-stat-val" id="valTodo">–</div><div class="acb-stat-lbl">Todo</div></div>
                        <i class="fa-solid fa-chevron-right acb-stat-arrow"></i>
                    </div>
                    <div class="acb-stat-card acb-clickable" onclick="acbDrillDown('tasks','unassigned','Unassigned Tasks')" title="View unassigned tasks" id="cardUnassigned">
                        <div class="acb-stat-icon" style="background:rgba(234,179,8,.1);color:#eab308"><i class="fa-solid fa-user-slash"></i></div>
                        <div class="acb-stat-info"><div class="acb-stat-val" id="valUnassigned">–</div><div class="acb-stat-lbl">Unassigned</div></div>
                        <i class="fa-solid fa-chevron-right acb-stat-arrow"></i>
                    </div>
                    <div class="acb-stat-card acb-clickable" onclick="acbDrillDown('tasks','published','Published Tasks')" title="View published tasks">
                        <div class="acb-stat-icon" style="background:rgba(5,150,105,.1);color:#059669"><i class="fa-solid fa-globe"></i></div>
                        <div class="acb-stat-info"><div class="acb-stat-val" id="valPublished">–</div><div class="acb-stat-lbl">Published</div></div>
                        <i class="fa-solid fa-chevron-right acb-stat-arrow"></i>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="acb-quick-actions">
                    <button class="acb-qa-btn acb-qa-primary" onclick="acbQuickCreateTask()" title="Open task creation wizard">
                        <i class="fa-solid fa-plus"></i> New Task
                    </button>
                    <button class="acb-qa-btn acb-qa-danger" onclick="acbDrillDown('tasks','overdue','Overdue Tasks')" title="View overdue tasks">
                        <i class="fa-solid fa-fire"></i> Overdue
                    </button>
                    <button class="acb-qa-btn acb-qa-warn" onclick="acbDrillDown('tasks','unassigned','Unassigned Tasks')" title="View unassigned tasks">
                        <i class="fa-solid fa-user-slash"></i> Unassigned
                    </button>
                    <button class="acb-qa-btn acb-qa-info" onclick="acbDrillDown('tasks','today','Due Today')" title="Tasks due today">
                        <i class="fa-solid fa-calendar-day"></i> Today
                    </button>
                </div>

                <div class="acb-hint"><i class="fa-solid fa-hand-pointer"></i> Tap any card to see its details</div>
            </div>

            <!-- ══ TASKS TAB ══ -->
            <div id="acbTasks" class="acb-pane">
                <div class="acb-filter-bar" id="acbFilterBar" style="display:none">
                    <button class="acb-back-btn" onclick="acbGoBack()" title="Back to Overview">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    <div class="acb-filter-info">
                        <i class="fa-solid fa-filter"></i>
                        <span id="acbFilterLabel">All Tasks</span>
                        <span class="acb-filter-count" id="acbFilterCount"></span>
                    </div>
                    <button class="acb-filter-clear" onclick="acbClearFilter()" title="Clear filter">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="acb-search-wrap">
                    <i class="fa-solid fa-magnifying-glass acb-search-icon"></i>
                    <input type="text" id="acbTaskSearch" class="acb-search" placeholder="Search tasks, clients, assignees..." oninput="acbSearchTasks()">
                    <button class="acb-search-clear" id="acbTaskSearchClear" onclick="acbClearSearch('task')" style="display:none" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <span class="acb-search-count" id="acbTaskCount"></span>
                </div>
                <div class="acb-list" id="acbTasksList">
                    <div class="acb-empty"><i class="fa-solid fa-arrows-rotate fa-spin"></i><span>Loading tasks...</span></div>
                </div>
                <a href="{{ route('developer.tasks') }}" class="acb-view-all" style="background:#f13535;color:#fff;padding:8px 14px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-list-check"></i> View All Projects <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <!-- ══ CLIENTS TAB ══ -->
            <div id="acbClients" class="acb-pane">
                <div class="acb-search-wrap">
                    <i class="fa-solid fa-magnifying-glass acb-search-icon"></i>
                    <input type="text" id="acbClientSearch" class="acb-search" placeholder="Search clients by name, email..." oninput="acbSearchClients()">
                    <button class="acb-search-clear" id="acbClientSearchClear" onclick="acbClearSearch('client')" style="display:none" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <span class="acb-search-count" id="acbClientCount"></span>
                </div>
                <div class="acb-list" id="acbClientsList">
                    <div class="acb-empty"><i class="fa-solid fa-arrows-rotate fa-spin"></i><span>Loading clients...</span></div>
                </div>
                <a href="/admin/clients" class="acb-view-all">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View All in Clients Page
                </a>
            </div>

            <!-- ══ REPORTS TAB ══ -->
            <div id="acbReports" class="acb-pane">
                <div class="acb-report-section">
                    <div class="acb-report-title"><i class="fa-solid fa-chart-simple"></i> Task Distribution</div>
                    <div class="acb-report-bars" id="acbReportBars"></div>
                </div>
                <div class="acb-report-section" style="margin-top:16px">
                    <div class="acb-report-title"><i class="fa-solid fa-bullseye"></i> Quick Summary</div>
                    <div class="acb-summary-grid" id="acbSummaryGrid"></div>
                </div>
                <a href="/admin/report" class="acb-view-all">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View Full Report
                </a>
            </div>

        </div>
    </div>
</div>

<!-- ═══════════ CSS ═══════════ -->
<link rel="stylesheet" href="{{ asset('css/pages/admin-chatbot.css') }}?v=1.2">

<!-- ═══════════ JS ═══════════ -->
<script src="{{ asset('js/pages/admin-chatbot.js') }}?v=1.2"></script>
