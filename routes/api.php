<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileDashboardController;
use App\Http\Controllers\Api\MobileTaskController;
use App\Http\Controllers\Api\MobileTeamController;
use App\Http\Controllers\Api\MobileApprovalController;
use App\Http\Controllers\Api\MobilePublishingController;
use App\Http\Controllers\Api\MobileClientController;
use App\Http\Controllers\Api\MobileCalendarController;
use App\Http\Controllers\Api\MobileActionItemController;
use App\Http\Controllers\Api\MobileNotificationController;
use App\Http\Controllers\Api\MobileAuditController;
use App\Http\Controllers\Api\MobileReportController;
use App\Http\Controllers\Api\MobileChatController;

/*
|--------------------------------------------------------------------------
| Mobile API v1 Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public Auth & Media
    Route::post('/auth/login', [MobileAuthController::class, 'login']);
    Route::get('/media/{id}/view', [MobileTaskController::class, 'viewMedia']);
    Route::get('/media/file/{path}', [MobileTaskController::class, 'viewMediaFile'])->where('path', '.*');

    // Authenticated Routes
    Route::middleware(['api.auth'])->group(function () {
        // Auth / User Profile
        Route::get('/auth/me', [MobileAuthController::class, 'me']);
        Route::post('/auth/logout', [MobileAuthController::class, 'logout']);
        Route::post('/auth/toggle-away-mode', [MobileAuthController::class, 'toggleAwayMode']);

        // Executive Dashboard
        Route::get('/dashboard', [MobileDashboardController::class, 'index']);

        // Tasks Hub
        Route::get('/tasks', [MobileTaskController::class, 'index']);
        Route::post('/tasks', [MobileTaskController::class, 'store']);
        Route::get('/tasks/urgent', [MobileTaskController::class, 'urgentTasks']);
        Route::get('/tasks/date-change-requests', [MobileTaskController::class, 'dateChangeRequests']);
        Route::get('/tasks/{id}', [MobileTaskController::class, 'show']);
        Route::put('/tasks/{id}', [MobileTaskController::class, 'update']);
        Route::delete('/tasks/{id}', [MobileTaskController::class, 'destroy']);
        Route::post('/tasks/{id}/reassign', [MobileTaskController::class, 'reassign']);
        Route::post('/tasks/{id}/approve-date', [MobileTaskController::class, 'approveDate']);
        Route::post('/tasks/{id}/reject-date', [MobileTaskController::class, 'rejectDate']);
        Route::post('/tasks/{id}/start', [MobileTaskController::class, 'start']);
        Route::post('/tasks/{id}/pause', [MobileTaskController::class, 'pause']);
        Route::post('/tasks/{id}/resume', [MobileTaskController::class, 'resume']);
        Route::post('/tasks/{id}/submit', [MobileTaskController::class, 'submit']);
        Route::post('/tasks/{id}/approve', [MobileTaskController::class, 'approve']);
        Route::post('/tasks/{id}/revision', [MobileTaskController::class, 'requestRevision']);
        Route::post('/tasks/{id}/comments', [MobileTaskController::class, 'addComment']);
        Route::post('/tasks/{id}/media', [MobileTaskController::class, 'uploadMedia']);
        Route::delete('/tasks/{id}/media/{mediaId}', [MobileTaskController::class, 'deleteMedia']);

        // Approvals Hub
        Route::get('/approvals', [MobileApprovalController::class, 'index']);
        Route::get('/approvals/{id}', [MobileApprovalController::class, 'show']);
        Route::post('/approvals/{id}/approve', [MobileApprovalController::class, 'approve']);
        Route::post('/approvals/{id}/reject', [MobileApprovalController::class, 'reject']);

        // Team & Workload
        Route::get('/team', [MobileTeamController::class, 'index']);
        Route::post('/team', [MobileTeamController::class, 'store']);
        Route::get('/team/workload', [MobileTeamController::class, 'workload']);
        Route::get('/team/active-users', [MobileTeamController::class, 'activeUsers']);
        Route::get('/team/{id}', [MobileTeamController::class, 'show']);
        Route::put('/team/{id}', [MobileTeamController::class, 'update']);
        Route::delete('/team/{id}', [MobileTeamController::class, 'destroy']);

        // Clients Hub
        Route::get('/clients', [MobileClientController::class, 'index']);
        Route::post('/clients', [MobileClientController::class, 'store']);
        Route::get('/clients/{id}', [MobileClientController::class, 'show']);
        Route::put('/clients/{id}', [MobileClientController::class, 'update']);
        Route::delete('/clients/{id}', [MobileClientController::class, 'destroy']);
        Route::post('/clients/{id}/login', [MobileClientController::class, 'createLogin']);
        Route::delete('/clients/{id}/login/{userId}', [MobileClientController::class, 'deleteLogin']);
        Route::post('/clients/{id}/contacts', [MobileClientController::class, 'storeContact']);
        Route::delete('/clients/{id}/contacts/{contactId}', [MobileClientController::class, 'destroyContact']);
        Route::post('/clients/{id}/social-links', [MobileClientController::class, 'addSocialLink']);
        Route::delete('/clients/{id}/social-links/{linkId}', [MobileClientController::class, 'deleteSocialLink']);
        Route::get('/clients/{id}/schedules', [MobileClientController::class, 'monthlySchedules']);
        Route::post('/clients/{id}/schedules', [MobileClientController::class, 'createMonthlySchedule']);

        // Publishing Tracker
        Route::get('/publishing', [MobilePublishingController::class, 'index']);
        Route::get('/publishing/{id}', [MobilePublishingController::class, 'show']);
        Route::post('/publishing/{id}/proof', [MobilePublishingController::class, 'storeProof']);
        Route::delete('/publishing/{id}/proof/{proofId}', [MobilePublishingController::class, 'destroyProof']);

        // Calendar & Shoot Days & Festivals
        Route::get('/calendar/events', [MobileCalendarController::class, 'events']);
        Route::get('/calendar/shoot-days', [MobileCalendarController::class, 'shootDays']);
        Route::post('/calendar/shoot-days', [MobileCalendarController::class, 'storeShootDay']);
        Route::put('/calendar/shoot-days/{id}', [MobileCalendarController::class, 'updateShootDay']);
        Route::delete('/calendar/shoot-days/{id}', [MobileCalendarController::class, 'deleteShootDay']);
        Route::get('/calendar/festivals', [MobileCalendarController::class, 'festivals']);
        Route::post('/calendar/festivals/{id}/select', [MobileCalendarController::class, 'selectFestival']);
        Route::get('/calendar/festival-selections', [MobileCalendarController::class, 'festivalSelections']);

        // Action Items & Reminders
        Route::get('/action-items', [MobileActionItemController::class, 'index']);
        Route::post('/action-items', [MobileActionItemController::class, 'store']);
        Route::post('/action-items/{id}/toggle', [MobileActionItemController::class, 'toggleDone']);
        Route::post('/action-items/{id}/note', [MobileActionItemController::class, 'addNote']);
        Route::post('/action-items/{id}/snooze', [MobileActionItemController::class, 'snooze']);

        // Notifications
        Route::get('/notifications', [MobileNotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [MobileNotificationController::class, 'markAsRead']);
        Route::post('/notifications/mark-all-read', [MobileNotificationController::class, 'markAllRead']);
        Route::post('/notifications/register-device', [MobileNotificationController::class, 'registerDevice']);
        Route::post('/notifications/unregister-device', [MobileNotificationController::class, 'unregisterDevice']);

        // Audit Logs
        Route::get('/audit-logs', [MobileAuditController::class, 'index']);
        Route::get('/audit-logs/{id}', [MobileAuditController::class, 'show']);
        Route::get('/tasks/{id}/audit-history', [MobileAuditController::class, 'taskHistory']);

        // Executive Reports
        Route::get('/reports/summary', [MobileReportController::class, 'summary']);

        // Chat & Realtime Messaging
        Route::get('/chat/users', [MobileChatController::class, 'users']);
        Route::post('/chat/users/{userId}', [MobileChatController::class, 'startDirectChat']);
        Route::get('/chat/conversations', [MobileChatController::class, 'conversations']);
        Route::get('/chat/conversations/{id}/messages', [MobileChatController::class, 'messages']);
        Route::post('/chat/conversations/{id}/messages', [MobileChatController::class, 'sendMessage']);
        Route::patch('/chat/messages/{id}', [MobileChatController::class, 'updateMessage']);
        Route::delete('/chat/messages/{id}', [MobileChatController::class, 'deleteMessage']);
        Route::post('/chat/messages/{id}/react', [MobileChatController::class, 'react']);
        Route::post('/chat/groups', [MobileChatController::class, 'createGroup']);
        Route::get('/chat/groups/{id}/members', [MobileChatController::class, 'groupMembers']);
        Route::post('/chat/groups/{id}/members', [MobileChatController::class, 'addGroupMembers']);
        Route::delete('/chat/groups/{id}/members/{userId}', [MobileChatController::class, 'removeGroupMember']);
        Route::post('/chat/groups/{id}/leave', [MobileChatController::class, 'leaveGroup']);
    });
});
