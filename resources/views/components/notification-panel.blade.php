{{-- resources/views/components/notification-panel.blade.php --}}
@php
    $notifications = auth()->user()->visibleCustomNotifications()->take(30)->get();
@endphp

<div class="notif-panel" id="notifPanel">
    <div class="notif-head">
        <div class="notif-title"><i class="fas fa-bell" style="margin-right:6px"></i>Notifications</div>
        <div class="notif-close" onclick="closeNotif()"><i class="fas fa-times"></i></div>
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
