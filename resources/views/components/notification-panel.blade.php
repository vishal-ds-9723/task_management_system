{{-- resources/views/components/notification-panel.blade.php --}}
@php
    $notifications = auth()->user()->visibleCustomNotifications()->take(30)->get();
@endphp

<div class="notif-panel" id="notifPanel">
    <div class="notif-head">
        <div class="notif-title"><i class="fas fa-bell" style="margin-right:6px"></i>Notifications</div>
        <div class="notif-close" onclick="closeNotif()"><i class="fas fa-times"></i></div>
    </div>

    {{-- Sound & Desktop Alerts Quick Controls --}}
    <div class="notif-toolbar">
        <div class="notif-toolbar-left">
            <button type="button" class="notif-tool-btn" id="tmsSoundToggleBtn" onclick="toggleTmsSound()" title="Toggle notification sound chime">
                <i class="fas fa-volume-up" id="tmsSoundIcon"></i>
                <span id="tmsSoundLabel">Sound: ON</span>
            </button>
            <button type="button" class="notif-tool-btn notif-test-btn" onclick="testTmsSound()" title="Play a test notification chime in your browser">
                <i class="fas fa-play"></i>
                <span>Test Chime</span>
            </button>
        </div>
        <div class="notif-toolbar-right">
            <button type="button" class="notif-tool-btn notif-desktop-btn" id="tmsDesktopPromptBtn" onclick="requestTmsDesktopPermission()" title="Enable desktop notifications in Google Chrome">
                <i class="fas fa-desktop"></i>
                <span id="tmsDesktopLabel">Desktop Alerts</span>
            </button>
        </div>
    </div>

    <div class="notif-list" id="notifList">
        @forelse($notifications as $notification)
            <a href="{{ $notification->link ?? '#' }}"
               class="notif-item {{ $notification->read_at ? 'notif-read' : 'notif-unread' }}"
               style="text-decoration:none;display:flex;align-items:flex-start;gap:10px">
                <div class="notif-icon">{{ $notification->icon }}</div>
                <div style="flex:1;min-width:0">
                    <div class="notif-item-title">{{ $notification->title }}</div>
                    @if($notification->subtitle)
                        <div class="notif-item-sub">{{ $notification->subtitle }}</div>
                    @endif
                    <div class="notif-time">{{ $notification->created_at->diffForHumans() }}</div>
                </div>
                @if(!$notification->read_at)
                    <span class="notif-unread-dot"></span>
                @endif
            </a>
        @empty
            <div id="notifEmpty" style="padding:48px 20px;text-align:center">
                <div style="font-size:36px;margin-bottom:10px;opacity:0.4"><i class="fas fa-bell"></i></div>
                <div class="notif-item-title" style="margin-bottom:4px">No notifications yet</div>
                <div class="notif-item-sub">Deadline alerts & task updates will appear here.</div>
            </div>
        @endforelse
    </div>

    @if($notifications->isNotEmpty())
        <div class="notif-footer">
            <button type="button" class="notif-clear-btn" onclick="clearAllNotifications()">
                <i class="fas fa-trash-alt"></i> Clear All
            </button>
        </div>
    @endif
</div>

<style>
.notif-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 16px;
    background: rgba(0, 0, 0, 0.02);
    border-bottom: 1px solid var(--border, #e5e7eb);
    gap: 8px;
    flex-wrap: wrap;
}
.notif-toolbar-left,
.notif-toolbar-right {
    display: flex;
    align-items: center;
    gap: 6px;
}
.notif-tool-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 9px;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--text2, #4b5563);
    background: var(--card, #ffffff);
    border: 1px solid var(--border, #d1d5db);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
    line-height: 1.3;
}
.notif-tool-btn:hover {
    color: var(--primary, #4f46e5);
    border-color: var(--primary, #4f46e5);
    background: rgba(79, 70, 229, 0.04);
}
.notif-tool-btn.active {
    color: #059669;
    border-color: #10b981;
    background: #ecfdf5;
}
.notif-tool-btn.muted {
    color: #9ca3af;
    background: #f9fafb;
    border-color: #e5e7eb;
}
.notif-test-btn:hover {
    color: #2563eb;
    border-color: #3b82f6;
}
.notif-desktop-btn {
    color: #4f46e5;
    background: rgba(79, 70, 229, 0.06);
    border-color: rgba(79, 70, 229, 0.2);
}
.notif-desktop-btn.granted {
    color: #059669;
    background: #ecfdf5;
    border-color: rgba(16, 185, 129, 0.3);
}
body.dark-mode .notif-toolbar {
    background: rgba(255, 255, 255, 0.03);
    border-bottom-color: var(--border, #374151);
}
body.dark-mode .notif-tool-btn {
    background: var(--card, #1f2937);
    color: var(--text2, #9ca3af);
    border-color: var(--border, #374151);
}
</style>
