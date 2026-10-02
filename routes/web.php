<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SocialMetricsPanelController;
use App\Http\Controllers\SocialMediaPostMetricController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Strategist;
use App\Http\Controllers\Designer;
use App\Http\Controllers\Client;
use App\Http\Controllers\Strategist\TaskController;

// ──────────── AUTH ────────────
Route::get('/', [LoginController::class, 'showLogin'])->name('login');
Route::post('/', [LoginController::class, 'login'])->name('login.post');
Route::post('/auth/login/stream', [LoginController::class, 'loginStream'])->name('login.stream');
Route::post('/auth/login/progress/start', [LoginController::class, 'startLoginProgress'])->name('login.progress.start');
Route::get('/auth/login/progress/state', [LoginController::class, 'loginProgressState'])->name('login.progress.state');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ──────────── API CALENDAR EVENTS (all authenticated roles) ────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/api/calendar/events', [Admin\CalendarEventController::class, 'getEvents']);
    Route::get('/api/clients', [Admin\CalendarEventController::class, 'getClients']);
    Route::get('/api/clients/{client}/social-links', [Admin\ClientController::class, 'getSocialLinksApi']);
    Route::post('/api/tasks/{task}/ask-review', [Admin\CalendarEventController::class, 'askForReview']);
    Route::patch('/api/tasks/{task}/reschedule', [Admin\CalendarEventController::class, 'reschedule']);

    // Media library routes
    Route::post('/api/media/upload', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/api/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::get('/api/media', [MediaController::class, 'index'])->name('media.index');
});

Route::middleware(['auth', 'role:admin,strategist,designer,developer,manager,editor,content_writer'])->group(function () {
    // 1:1 Chat (Reverb-powered realtime)
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/search', [\App\Http\Controllers\ChatController::class, 'search'])->name('chat.search');
    Route::get('/chat/users/{user}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/conversations/{conversation}/messages', [\App\Http\Controllers\ChatController::class, 'store'])->name('chat.messages.store');
    Route::patch('/chat/messages/{message}', [\App\Http\Controllers\ChatController::class, 'update'])->name('chat.messages.update');
    Route::delete('/chat/messages/{message}', [\App\Http\Controllers\ChatController::class, 'destroy'])->name('chat.messages.destroy');
    Route::post('/chat/messages/{message}/react', [\App\Http\Controllers\ChatController::class, 'react'])->name('chat.messages.react');
    Route::post('/chat/groups', [\App\Http\Controllers\ChatController::class, 'createGroup'])->name('chat.groups.create');
    Route::get('/chat/groups/{conversation}', [\App\Http\Controllers\ChatController::class, 'showGroup'])->name('chat.groups.show');
    Route::patch('/chat/groups/{conversation}', [\App\Http\Controllers\ChatController::class, 'updateGroup'])->name('chat.groups.update');
    Route::post('/chat/groups/{conversation}/members', [\App\Http\Controllers\ChatController::class, 'addGroupMembers'])->name('chat.groups.members.add');
    Route::delete('/chat/groups/{conversation}/members/{user}', [\App\Http\Controllers\ChatController::class, 'removeGroupMember'])->name('chat.groups.members.remove');
    Route::post('/chat/groups/{conversation}/leave', [\App\Http\Controllers\ChatController::class, 'leaveGroup'])->name('chat.groups.leave');
});

Route::middleware(['auth', 'can.manage.social.metrics'])->group(function () {
    Route::get('/social-metrics-panel', [SocialMetricsPanelController::class, 'index'])->name('social-metrics.panel');
    Route::get('/social-metrics-panel/{client}', [SocialMetricsPanelController::class, 'show'])->name('social-metrics.client');
    Route::post('/social-metrics/{post}', [SocialMediaPostMetricController::class, 'store'])->name('social-metrics.store');
    Route::patch('/social-metrics/{metric}', [SocialMediaPostMetricController::class, 'update'])->name('social-metrics.update');
    Route::delete('/social-metrics/{metric}', [SocialMediaPostMetricController::class, 'destroy'])->name('social-metrics.destroy');
});

// ──────────── API ADMIN-ONLY ────────────
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/api/dashboard/metric-tasks', [Admin\TaskController::class, 'getMetricTasks']);
    Route::get('/api/team-activity', [Admin\DashboardController::class, 'teamActivity']);
    Route::get('/api/chatbot/tab-data', [Admin\DashboardController::class, 'getChatbotTabData']);
    Route::get('/api/chatbot/wizard-data', [Admin\DashboardController::class, 'getChatbotWizardData']);
});

Route::post('/api/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->middleware('auth');
Route::post('/api/notifications/clear-all', [NotificationController::class, 'clearAll'])->middleware('auth');
Route::get('/api/notifications/poll', [NotificationController::class, 'poll'])->middleware('auth');

// Global reminders API
Route::get('/api/reminders/active', [Strategist\ActionItemController::class, 'getActiveReminders'])->middleware('auth');

// ──────────── ADMIN ────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tasks', [Admin\TaskController::class, 'index'])->name('tasks');
    Route::get('/tasks/create', [Admin\TaskController::class, 'create'])->name('tasks.create');
    Route::get('/urgent-tasks', [Admin\TaskController::class, 'urgentTasks'])->name('urgent-tasks');
    Route::post('/tasks', [Admin\TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [Admin\TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/edit', [Admin\TaskController::class, 'edit'])->name('tasks.edit');
    Route::patch('/tasks/{task}', [Admin\TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [Admin\TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{task}/kanban-status', [Admin\TaskController::class, 'kanbanUpdateStatus'])->name('tasks.kanban-status');
    Route::post('/tasks/bulk/update-status', [Admin\TaskController::class, 'bulkUpdateStatus'])->name('tasks.bulk-update-status');
    Route::get('/tasks/export/csv', [Admin\TaskController::class, 'exportCsv'])->name('tasks.export-csv');
    Route::get('/calendar', [Admin\CalendarController::class, 'index'])->name('calendar');
    Route::get('/clients/{client}/calendar', [Admin\CalendarController::class, 'clientCalendar'])->name('clients.calendar');
    Route::get('/festival-selections/{selection}', [Admin\FestivalSelectionController::class, 'show'])->name('festival.selection.show');
    Route::get('/workload', [Admin\WorkloadController::class, 'index'])->name('workload');
    Route::get('/clients', [Admin\ClientController::class, 'index'])->name('clients');
    Route::post('/clients', [Admin\ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}', [Admin\ClientController::class, 'show'])->name('clients.show');
    Route::put('/clients/{client}', [Admin\ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [Admin\ClientController::class, 'destroy'])->name('clients.destroy');
    Route::post('/clients/{client}/login', [Admin\ClientController::class, 'createLogin'])->name('clients.create-login');
    Route::put('/clients/{client}/login/{user}', [Admin\ClientController::class, 'updateLogin'])->name('clients.update-login');
    Route::delete('/clients/{client}/login/{user}', [Admin\ClientController::class, 'deleteLogin'])->name('clients.delete-login');
    Route::post('/clients/{client}/social-link', [Admin\ClientController::class, 'addSocialLink'])->name('clients.add-social-link');
    Route::post('/clients/{client}/social-links/batch', [Admin\ClientController::class, 'addSocialLinksBatch'])->name('clients.social-links.batch');
    Route::patch('/clients/{client}/social-link/{link}', [Admin\ClientController::class, 'updateSocialLink'])->name('clients.update-social-link');
    Route::delete('/clients/{client}/social-link/{link}', [Admin\ClientController::class, 'deleteSocialLink'])->name('clients.delete-social-link');

    // Dynamic client contacts (multi-contact CRUD)
    Route::post('/clients/{client}/contacts',                       [Admin\ClientController::class, 'storeContact'])->name('clients.contacts.store');
    Route::patch('/clients/{client}/contacts/{contact}',            [Admin\ClientController::class, 'updateContact'])->name('clients.contacts.update');
    Route::delete('/clients/{client}/contacts/{contact}',           [Admin\ClientController::class, 'destroyContact'])->name('clients.contacts.destroy');
    Route::post('/clients/{client}/contacts/{contact}/set-primary', [Admin\ClientController::class, 'setPrimaryContact'])->name('clients.contacts.set-primary');
    Route::post('/clients/{client}/monthly-schedule', [Admin\ClientController::class, 'createMonthlySchedule'])->name('clients.create-monthly-schedule');
    Route::patch('/clients/{client}/monthly-schedule/{schedule}', [Admin\ClientController::class, 'updateMonthlySchedule'])->name('clients.update-monthly-schedule');

    // Admin Client Visits (Leads & Clients)
    Route::get('/visits', [Admin\ClientVisitController::class, 'index'])->name('visits');
    Route::post('/visits', [Admin\ClientVisitController::class, 'store'])->name('visits.store');
    Route::get('/visits/{visit}', [Admin\ClientVisitController::class, 'show'])->name('visits.show');
    Route::put('/visits/{visit}', [Admin\ClientVisitController::class, 'update'])->name('visits.update');
    Route::delete('/visits/{visit}', [Admin\ClientVisitController::class, 'destroy'])->name('visits.destroy');
    Route::post('/visits/{visit}/updates', [Admin\ClientVisitController::class, 'addUpdate'])->name('visits.updates.store');
    Route::post('/visits/{visit}/convert', [Admin\ClientVisitController::class, 'convert'])->name('visits.convert');

    // Admin Photoshoot Schedule
    Route::get('/photoshoots', [Admin\ShootDayController::class, 'index'])->name('shoot-days');
    Route::post('/photoshoots', [Admin\ShootDayController::class, 'store'])->name('shoot-days.store');
    Route::patch('/photoshoots/{shootDay}', [Admin\ShootDayController::class, 'update'])->name('shoot-days.update');
    Route::delete('/photoshoots/{shootDay}', [Admin\ShootDayController::class, 'destroy'])->name('shoot-days.destroy');

    Route::get('/report', [Admin\ReportController::class, 'index'])->name('report');
    Route::get('/team', [Admin\TeamController::class, 'index'])->name('team');
    Route::get('/team/{user}/edit', [Admin\TeamController::class, 'edit'])->name('team.edit');
    Route::post('/team', [Admin\TeamController::class, 'store'])->name('team.store');
    Route::put('/team/{user}', [Admin\TeamController::class, 'update'])->name('team.update');
    Route::delete('/team/{user}', [Admin\TeamController::class, 'destroy'])->name('team.destroy');

    // Team AJAX endpoints
    Route::post('/api/team', [Admin\TeamController::class, 'storeAjax'])->name('team.store.ajax');
    Route::put('/api/team/{user}', [Admin\TeamController::class, 'updateAjax'])->name('team.update.ajax');
    Route::delete('/api/team/{user}', [Admin\TeamController::class, 'destroyAjax'])->name('team.destroy.ajax');
    Route::get('/api/team/{user}', [Admin\TeamController::class, 'show'])->name('team.show.ajax');

    Route::get('/members', [Admin\MemberController::class, 'index'])->name('members');
    Route::post('/members', [Admin\MemberController::class, 'store'])->name('members.store');
    Route::put('/members/{employee}', [Admin\MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{employee}', [Admin\MemberController::class, 'destroy'])->name('members.destroy');
    Route::get('/audit-logs', [Admin\AuditController::class, 'index'])->name('audit-logs');
    Route::get('/audit-logs/{auditLog}', [Admin\AuditController::class, 'show'])->name('audit-logs.show');
    Route::get('/tasks/{task}/audit-history', [Admin\AuditController::class, 'taskHistory'])->name('tasks.audit-history');
    Route::get('/date-change-requests', [Admin\TaskController::class, 'dateChangeRequests'])->name('date-change-requests');
    Route::patch('/tasks/{task}/approve-date', [Admin\TaskController::class, 'approveDate'])->name('tasks.approve-date');
    Route::patch('/tasks/{task}/reject-date', [Admin\TaskController::class, 'rejectDate'])->name('tasks.reject-date');
    Route::patch('/tasks/{task}/approve-task', [Admin\ApprovalController::class, 'approve'])->name('tasks.approve-task');
    Route::patch('/tasks/{task}/reject-task', [Admin\ApprovalController::class, 'reject'])->name('tasks.reject-task');

    // Admin approvals interface for task submissions with media review
    Route::get('/approvals', [Admin\ApprovalController::class, 'index'])->name('approvals');
    Route::get('/approvals/{task}', [Admin\ApprovalController::class, 'show'])->name('approvals.show');
    Route::patch('/approvals/{task}/approve', [Admin\ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::patch('/approvals/{task}/reject', [Admin\ApprovalController::class, 'reject'])->name('approvals.reject');

    Route::get('/active-users', [Admin\ActiveUsersController::class, 'index'])->name('active-users');

    // Publishing tracker
    Route::get('/publishing', [Admin\PublishingController::class, 'index'])->name('publishing');
    Route::get('/publishing/{task}', [Admin\PublishingController::class, 'show'])->name('publishing.show');

    // Action Items
    Route::get('/action-items', [Admin\ActionItemController::class, 'index'])->name('action-items');
    Route::post('/action-items', [Admin\ActionItemController::class, 'store'])->name('action-items.store');
    Route::put('/action-items/{actionItem}', [Admin\ActionItemController::class, 'update'])->name('action-items.update');
    Route::delete('/action-items/{actionItem}', [Admin\ActionItemController::class, 'destroy'])->name('action-items.destroy');

    // Settings
    Route::post('/settings/toggle-away-mode', [Admin\SettingsController::class, 'toggleAwayMode'])->name('settings.toggle-away-mode');

    // Festival Calendar & Selections for Admin
    Route::get('/festival-calendar', [Strategist\FestivalController::class, 'index'])->name('festival-calendar');
    Route::post('/festivals', [Strategist\FestivalController::class, 'store'])->name('festivals.store');
    Route::post('/festivals/import', [Strategist\FestivalController::class, 'import'])->name('festivals.import');
    Route::put('/festivals/{festival}', [Strategist\FestivalController::class, 'update'])->name('festivals.update');
    Route::patch('/festivals/{festival}/toggle', [Strategist\FestivalController::class, 'toggle'])->name('festivals.toggle');
    Route::delete('/festivals/{festival}', [Strategist\FestivalController::class, 'destroy'])->name('festivals.destroy');
    Route::get('/festival-selections', [Admin\FestivalSelectionController::class, 'index'])->name('festival-selections');
    Route::get('/api/festival-events', [Strategist\FestivalController::class, 'calendarEvents'])->name('festival-events.api');

    // Chatbot API endpoints
    Route::get('/api/chatbot/tasks-summary', [Admin\DashboardController::class, 'getChatbotTasksSummary'])->name('api.tasks-summary');
    Route::get('/api/chatbot/clients-summary', [Admin\DashboardController::class, 'getChatbotClientsSummary'])->name('api.clients-summary');
    Route::get('/api/chatbot/reports-summary', [Admin\DashboardController::class, 'getChatbotReportsSummary'])->name('api.reports-summary');
    Route::post('/api/chatbot/message', [Admin\DashboardController::class, 'chatbotMessage'])->name('api.chat-message');
});

// ──────────── STRATEGIST / MANAGER / EDITOR ────────────
Route::prefix('strategist')->name('strategist.')->middleware(['auth', 'role:strategist,manager,editor,content_writer'])->group(function () {
    Route::get('/dashboard', [Strategist\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/create-task', [Strategist\TaskController::class, 'create'])->name('create-task');
    Route::post('/tasks', [Strategist\TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [Strategist\TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/edit', [Strategist\TaskController::class, 'edit'])->name('tasks.edit');
    Route::patch('/tasks/{task}', [Strategist\TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [Strategist\TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{task}/reassign', [Strategist\TaskController::class, 'reassign'])->name('tasks.reassign');
    Route::post('/tasks/{task}/follow-up', [Strategist\TaskController::class, 'followUp'])->name('tasks.follow-up');
    Route::post('/tasks/{task}/comment', [Strategist\TaskController::class, 'addComment'])->name('tasks.comment');
    Route::post('/tasks/{task}/updateCaptionHashtags', [Strategist\TaskController::class, 'updateCaptionHashtags'])->name('tasks.updateCaptionHashtags');
    Route::post('/tasks/{task}/request-date-change', [Strategist\TaskController::class, 'requestDateChange'])->name('tasks.request-date-change');
    Route::patch('/tasks/{task}/approve', [Strategist\TaskController::class, 'approve'])->name('tasks.approve');
    Route::patch('/tasks/{task}/request-revision', [Strategist\TaskController::class, 'requestRevision'])->name('tasks.request-revision');
    Route::patch('/tasks/{task}/hashtags', [Strategist\TaskController::class, 'updateHashtags'])->name('tasks.update-hashtags');
    Route::post('/tasks/{task}/upload-media', [Strategist\TaskController::class, 'uploadMedia'])->name('tasks.upload-media');
    Route::delete('/tasks/{task}/delete-media', [Strategist\TaskController::class, 'deleteMedia'])->name('tasks.delete-media');
    Route::post('/tasks/{task}/on-hold', [Strategist\TaskController::class, 'putOnHold'])->name('tasks.on-hold');
    Route::post('/tasks/{task}/resume-hold', [Strategist\TaskController::class, 'resumeFromHold'])->name('tasks.resume-hold');
    Route::post('/tasks/{task}/maintenance', [Strategist\TaskController::class, 'createMaintenanceTask'])->name('tasks.maintenance');
    Route::get('/calendar', [Strategist\CalendarController::class, 'index'])->name('calendar');
    Route::get('/tracking', [Strategist\TaskController::class, 'tracking'])->name('tracking');
    Route::get('/approvals/data', [Strategist\TaskController::class, 'approvalsData'])->name('approvals.data');
    Route::get('/approvals', [Strategist\TaskController::class, 'approvals'])->name('approvals');
    Route::get('/pending-assignments', [Strategist\DashboardController::class, 'pendingAssignments'])->name('pending-assignments');
    Route::get('/priority-heatmap', [Strategist\DashboardController::class, 'priorityHeatmap'])->name('priority-heatmap');
    Route::get('/photoshoots', [Strategist\DashboardController::class, 'allShootDays'])->name('shoot-days');
    Route::get('/shoot-days-all', [Strategist\DashboardController::class, 'allShootDays'])->name('shoot-days-all');
    Route::post('/shoot-days', [Strategist\ShootDayController::class, 'store'])->name('shoot-days.store');
    Route::patch('/shoot-days/{shootDay}', [Strategist\ShootDayController::class, 'update'])->name('shoot-days.update');
    Route::delete('/shoot-days/{shootDay}', [Strategist\ShootDayController::class, 'destroy'])->name('shoot-days.destroy');

    // Publishing queue
    Route::get('/publishing', [Strategist\PublishingController::class, 'index'])->name('publishing');
    Route::get('/publishing/{task}', [Strategist\PublishingController::class, 'show'])->name('publishing.show');
    Route::post('/publishing/{task}/proof', [Strategist\PublishingController::class, 'storeProof'])->name('publishing.store-proof');
    Route::delete('/publishing/{task}/proof/{proof}', [Strategist\PublishingController::class, 'destroyProof'])->name('publishing.destroy-proof');

    // Action Items
    Route::get('/action-items', [Strategist\ActionItemController::class, 'index'])->name('action-items');
    Route::post('/action-items', [Strategist\ActionItemController::class, 'store'])->name('action-items.store');
    Route::patch('/action-items/{actionItem}', [Strategist\ActionItemController::class, 'update'])->name('action-items.update');
    Route::patch('/action-items/{actionItem}/done', [Strategist\ActionItemController::class, 'markDone'])->name('action-items.done');
    Route::patch('/action-items/{actionItem}/start', [Strategist\ActionItemController::class, 'startProgress'])->name('action-items.start');
    Route::patch('/action-items/{actionItem}/reopen', [Strategist\ActionItemController::class, 'reopen'])->name('action-items.reopen');
    Route::post('/action-items/{actionItem}/note', [Strategist\ActionItemController::class, 'addNote'])->name('action-items.add-note');
    Route::patch('/action-items/{actionItem}/snooze', [Strategist\ActionItemController::class, 'snooze'])->name('action-items.snooze');
    Route::post('/action-items/{actionItem}/reminder', [Strategist\ActionItemController::class, 'setReminder'])->name('action-items.set-reminder');
    Route::delete('/action-items/{actionItem}/reminder', [Strategist\ActionItemController::class, 'clearReminder'])->name('action-items.clear-reminder');

    // Festival Calendar (strategist manages festivals + views client selections)
    Route::get('/festival-calendar', [Strategist\FestivalController::class, 'index'])->name('festival-calendar');
    Route::post('/festivals', [Strategist\FestivalController::class, 'store'])->name('festivals.store');
    Route::post('/festivals/import', [Strategist\FestivalController::class, 'import'])->name('festivals.import');
    Route::put('/festivals/{festival}', [Strategist\FestivalController::class, 'update'])->name('festivals.update');
    Route::patch('/festivals/{festival}/toggle', [Strategist\FestivalController::class, 'toggle'])->name('festivals.toggle');
    Route::delete('/festivals/{festival}', [Strategist\FestivalController::class, 'destroy'])->name('festivals.destroy');
    Route::get('/festival-selections', [Strategist\FestivalSelectionController::class, 'index'])->name('festival-selections');
    Route::get('/content-schedules', [Strategist\ContentScheduleController::class, 'index'])->name('content-schedules');

    // Festival calendar events API
    Route::get('/api/festival-events', [Strategist\FestivalController::class, 'calendarEvents'])->name('festival-events.api');

    // Note: Task deletion disabled to prevent data loss. All strategists share view of all strategist-created tasks.
});

// ──────────── DESIGNER ────────────
Route::prefix('designer')->name('designer.')->middleware(['auth', 'role:designer'])->group(function () {
    Route::get('/dashboard', [Designer\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tasks', [Designer\TaskController::class, 'index'])->name('tasks');
    Route::get('/tasks/{task}', [Designer\TaskController::class, 'show'])->name('tasks.show');
    Route::patch('/tasks/{task}/status', [Designer\TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('/tasks/{task}/submit', [Designer\TaskController::class, 'submitForReview'])->name('tasks.submit');
    Route::post('/tasks/{task}/upload-media', [Designer\TaskController::class, 'uploadMedia'])->name('tasks.upload-media');
    Route::delete('/tasks/{task}/delete-media', [Designer\TaskController::class, 'deleteMedia'])->name('tasks.delete-media');
    Route::post('/tasks/{task}/comment', [Designer\TaskController::class, 'addComment'])->name('tasks.comment');
    Route::post('/tasks/{task}/pause', [Designer\TaskController::class, 'pauseTask'])->name('tasks.pause');
    Route::post('/tasks/{task}/resume', [Designer\TaskController::class, 'resumeTask'])->name('tasks.resume');
    Route::get('/urgent-task', [Designer\TaskController::class, 'urgentTaskPage'])->name('urgent-task');
    Route::post('/urgent-task', [Designer\TaskController::class, 'createUrgentTask'])->name('urgent-task.store');
    Route::get('/calendar', [Designer\CalendarController::class, 'index'])->name('calendar');
    Route::get('/performance', [Designer\PerformanceController::class, 'index'])->name('performance');
});

// ──────────── CLIENT ────────────
Route::prefix('client')->name('client.')->middleware(['auth', 'role:client'])->group(function () {
    Route::get('/dashboard', [Client\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stat-details/{type}', [Client\DashboardController::class, 'getStatDetails'])->name('dashboard.stat-details');
    Route::get('/tasks/{task}', [Client\DashboardController::class, 'showTask'])->name('tasks.show');
    Route::get('/published-content', [Client\PublishedContentController::class, 'index'])->name('published-content');
    Route::get('/content-schedule', [Client\DashboardController::class, 'contentSchedule'])->name('content-schedule');
    Route::get('/social-analysis', [Client\DashboardController::class, 'socialAnalysis'])->name('social-analysis');
    Route::get('/festivals', [Client\FestivalController::class, 'index'])->name('festivals');

    Route::post('/festivals/{festival}/select', [Client\FestivalController::class, 'select'])->name('festivals.select');
    Route::delete('/festivals/{festival}/deselect', [Client\FestivalController::class, 'deselect'])->name('festivals.deselect');
});

// legacy google callback alias removed

Route::prefix('developer')
    ->name('developer.')
    ->middleware(['auth', 'role:developer'])
    ->group(function () {

    Route::controller(\App\Http\Controllers\Developer\TaskController::class)->group(function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/tasks', 'tasks')->name('tasks');
        Route::get('/calendar', 'calendar')->name('calendar');
        Route::get('/skills', 'skills')->name('skills');
        Route::post('/skills', 'storeSkill')->name('skills.store');
        Route::delete('/skills/{skill}', 'deleteSkill')->name('skills.delete');
        Route::post('/tasks/{task}/start', 'start')->name('tasks.start');
        Route::post('/tasks/{task}/submit', 'submit')->name('tasks.submit');
        Route::post('/tasks/{task}/pause', 'pauseTask')->name('tasks.pause');
        Route::post('/tasks/{task}/resume', 'resumeTask')->name('tasks.resume');
        Route::post('/tasks/{task}/on-hold', 'putOnHold')->name('tasks.on-hold');
        Route::post('/tasks/{task}/resume-hold', 'resumeFromHold')->name('tasks.resume-hold');
        Route::post('/tasks/{task}/comment', 'addComment')->name('tasks.comment');
        Route::post('/tasks/{task}/upload-media', 'uploadMedia')->name('tasks.upload-media');
        Route::delete('/tasks/{task}/delete-media', 'deleteMedia')->name('tasks.delete-media');
    });

});
