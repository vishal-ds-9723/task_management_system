@extends('layouts.app')

@section('content')
@php
    $unread = $unread ?? [];
    $unreadByUser = [];
    foreach ($conversations as $c) {
        if (! $c->is_group) {
            $other = $c->otherParticipant($me->id);
            if ($other) {
                $unreadByUser[$other->id] = $unread[$c->id] ?? 0;
            }
        }
    }
    $activeConversationId = isset($conversation) ? $conversation->id : null;
@endphp
<div class="chat-shell">
    <!-- Sidebar: users + recent conversations -->
    <aside class="chat-sidebar">
        <div class="chat-sidebar-head">
            <div class="chat-sidebar-title">
                <h2>Messages</h2>
                <button type="button" id="chat-new-group-btn" class="chat-new-group-btn" title="Create group">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
            <div class="chat-search-wrap">
                <i class="fa-solid fa-magnifying-glass chat-search-icon"></i>
                <input type="search" id="chat-user-search" placeholder="Search people or messages...">
            </div>
        </div>

        {{-- Search results panel (hidden until query >= 2 chars) --}}
        <div class="chat-search-results" id="chat-search-results" style="display:none">
            <div class="chat-search-head">
                <span id="chat-search-meta">Search results</span>
                <button type="button" id="chat-search-clear" title="Close search"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="chat-search-list" id="chat-search-list"></div>
        </div>

        @if($conversations->isNotEmpty())
            <div class="chat-section-label">Recent Conversations</div>
            <div class="chat-user-list">
                @foreach($conversations as $c)
                    @php $unreadForThis = $unread[$c->id] ?? 0; @endphp

                    @if($c->is_group)
                        <a href="{{ route('chat.groups.show', $c) }}"
                           class="chat-user-row {{ $activeConversationId === $c->id ? 'active' : '' }}"
                           data-conversation-id="{{ $c->id }}">
                            <span class="chat-avatar chat-group-avatar"><i class="fa-solid fa-users"></i></span>
                            <span class="chat-user-info">
                                <span class="chat-user-name">{{ $c->name ?: 'Group' }}</span>
                                <span class="chat-user-role">{{ $c->participants->count() }} members</span>
                            </span>
                            <span class="chat-unread-badge" data-unread-for-convo="{{ $c->id }}" {{ $unreadForThis > 0 && $activeConversationId !== $c->id ? '' : 'hidden' }}>
                                {{ $unreadForThis > 99 ? '99+' : $unreadForThis }}
                            </span>
                        </a>
                    @else
                        @php $other = $c->otherParticipant($me->id); @endphp
                        @if($other)
                            <a href="{{ route('chat.show', $other) }}"
                               class="chat-user-row {{ isset($partner) && $partner->id === $other->id ? 'active' : '' }}"
                               data-user-id="{{ $other->id }}"
                               data-conversation-id="{{ $c->id }}">
                                <span class="chat-avatar" style="background:{{ $other->avatar_color ?? '#4F6DF0' }}">
                                    {{ strtoupper(substr($other->name, 0, 1)) }}
                                    <span class="chat-online-dot" data-online-for="{{ $other->id }}"></span>
                                </span>
                                <span class="chat-user-info">
                                    <span class="chat-user-name">{{ $other->name }}</span>
                                    <span class="chat-user-role">{{ ucfirst($other->role) }}</span>
                                </span>
                                <span class="chat-unread-badge" data-unread-for="{{ $other->id }}" {{ $unreadForThis > 0 && (! isset($partner) || $partner->id !== $other->id) ? '' : 'hidden' }}>
                                    {{ $unreadForThis > 99 ? '99+' : $unreadForThis }}
                                </span>
                            </a>
                        @endif
                    @endif
                @endforeach
            </div>
        @endif

        <div class="chat-section-label">Team Members</div>
        <div class="chat-user-list" id="chat-all-users">
            @foreach($users as $u)
                @php $unreadForUser = $unreadByUser[$u->id] ?? 0; @endphp
                <a href="{{ route('chat.show', $u) }}"
                   class="chat-user-row {{ isset($partner) && $partner->id === $u->id ? 'active' : '' }}"
                   data-name="{{ strtolower($u->name) }}"
                   data-user-id="{{ $u->id }}">
                    <span class="chat-avatar" style="background:{{ $u->avatar_color ?? '#4F6DF0' }}">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                        <span class="chat-online-dot" data-online-for="{{ $u->id }}"></span>
                    </span>
                    <span class="chat-user-info">
                        <span class="chat-user-name">{{ $u->name }}</span>
                        <span class="chat-user-role">{{ ucfirst($u->role) }}</span>
                    </span>
                    <span class="chat-unread-badge" data-unread-for="{{ $u->id }}" {{ $unreadForUser > 0 && (! isset($partner) || $partner->id !== $u->id) ? '' : 'hidden' }}>
                        {{ $unreadForUser > 99 ? '99+' : $unreadForUser }}
                    </span>
                </a>
            @endforeach
        </div>
    </aside>

    <!-- Active conversation pane -->
    <main class="chat-main">
        @if(isset($conversation) && isset($messages))
            <header class="chat-main-head">
                @if(isset($group))
                    <span class="chat-avatar chat-group-avatar"><i class="fa-solid fa-users"></i></span>
                    <div style="flex:1">
                        <div class="chat-partner-name" id="chat-group-name-display">{{ $group->name }}</div>
                        <div class="chat-partner-role" id="chat-partner-status">
                            {{ $group->participants->count() }} members &middot; {{ $group->participants->pluck('name')->take(4)->join(', ') }}@if($group->participants->count() > 4)…@endif
                        </div>
                    </div>
                    <button type="button" class="chat-header-btn" id="chat-group-settings-btn" title="Group settings">
                        <i class="fa-solid fa-gear"></i>
                    </button>
                    <button type="button" class="chat-leave-btn" id="chat-leave-group-btn" title="Leave group">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                @elseif(isset($partner))
                    <span class="chat-avatar" style="background:{{ $partner->avatar_color ?? '#4F6DF0' }}">
                        {{ strtoupper(substr($partner->name, 0, 1)) }}
                        <span class="chat-online-dot" data-online-for="{{ $partner->id }}"></span>
                    </span>
                    <div>
                        <div class="chat-partner-name">{{ $partner->name }}</div>
                        <div class="chat-partner-role" id="chat-partner-status">{{ ucfirst($partner->role) }}</div>
                    </div>
                @endif
            </header>

            <div class="chat-messages" id="chat-messages">
                @foreach($messages as $m)
                    @php
                        $isMine = $m->user_id === $me->id;
                        $reactionCounts = $m->reactions->groupBy('emoji')->map->count();
                        $myReactions = $m->reactions->where('user_id', $me->id)->pluck('emoji')->all();
                    @endphp
                    <div class="chat-msg {{ $isMine ? 'mine' : 'theirs' }}" data-id="{{ $m->id }}" data-mine="{{ $isMine ? '1' : '0' }}">
                        <div class="chat-msg-tools">
                            <button type="button" class="chat-msg-tool" data-action="react" title="React"><i class="fa-regular fa-face-smile"></i></button>
                            @if($isMine)
                                <button type="button" class="chat-msg-tool" data-action="edit" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                <button type="button" class="chat-msg-tool" data-action="delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            @endif
                        </div>
                        <div class="chat-msg-bubble">
                            @if($m->attachment_path)
                                @if($m->isImageAttachment())
                                    <a href="{{ $m->attachmentUrl() }}" target="_blank" class="chat-attachment-image">
                                        <img src="{{ $m->attachmentUrl() }}" alt="{{ $m->attachment_name }}" loading="lazy">
                                    </a>
                                @else
                                    <a href="{{ $m->attachmentUrl() }}" target="_blank" class="chat-attachment-file">
                                        <i class="fa-solid fa-paperclip"></i>
                                        <span>
                                            <span class="chat-attach-name">{{ $m->attachment_name }}</span>
                                            <span class="chat-attach-size">{{ number_format($m->attachment_size / 1024, 1) }} KB</span>
                                        </span>
                                    </a>
                                @endif
                            @endif
                            @if($m->body)
                                <div class="chat-msg-text">{!! nl2br(e($m->body)) !!}</div>
                            @endif
                        </div>
                        <div class="chat-reactions" data-message-id="{{ $m->id }}">
                            @foreach($reactionCounts as $emoji => $count)
                                <button type="button"
                                    class="chat-reaction {{ in_array($emoji, $myReactions, true) ? 'mine' : '' }}"
                                    data-emoji="{{ $emoji }}">
                                    <span class="chat-reaction-emoji">{{ $emoji }}</span>
                                    <span class="chat-reaction-count">{{ $count }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="chat-msg-meta">
                            <span>{{ $m->created_at->format('g:i A') }}</span>
                            @if($m->edited_at)
                                <span class="chat-edited-tag" title="Edited at {{ $m->edited_at->format('g:i A') }}">edited</span>
                            @endif
                            @if($isMine)
                                @if($m->read_at)
                                    <span class="chat-tick read" title="Read">
                                        <i class="fa-solid fa-check-double"></i>
                                    </span>
                                @else
                                    <span class="chat-tick sent" title="Sent">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <form class="chat-input-bar" id="chat-form" autocomplete="off" enctype="multipart/form-data">
                <div id="chat-attachment-preview" class="chat-attachment-preview" hidden>
                    <span id="chat-attach-info"></span>
                    <button type="button" id="chat-attach-clear" title="Remove"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="chat-input-row">
                    <label class="chat-attach-btn" title="Attach a file (max 5MB)">
                        <i class="fa-solid fa-paperclip"></i>
                        <input type="file" id="chat-file-input" hidden
                               accept="image/jpeg,image/png,image/gif,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain">
                    </label>
                    <textarea id="chat-input" placeholder="Type a message..." rows="1"></textarea>
                    <button type="submit" class="chat-send-btn" title="Send">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        @else
            <div class="chat-empty">
                <div class="chat-empty-circle">
                    <i class="fa-regular fa-comment-dots"></i>
                </div>
                <h3>No Conversation Selected</h3>
                <p>Choose a contact or team group from the sidebar to view messages or start a new conversation.</p>
            </div>
        @endif
    </main>
</div>

{{-- Create-group modal --}}
<div id="chat-group-modal" class="chat-modal" hidden>
    <div class="chat-modal-backdrop" data-close="modal"></div>
    <div class="chat-modal-card">
        <div class="chat-modal-head">
            <h3>New group</h3>
            <button type="button" class="chat-modal-close" data-close="modal"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="chat-group-form">
            <label class="chat-form-label">Group name</label>
            <input type="text" id="chat-group-name" maxlength="80" required placeholder="e.g. Marketing team">

            <label class="chat-form-label">Add members</label>
            <input type="search" id="chat-group-member-search" placeholder="Search users...">
            <div id="chat-group-member-list" class="chat-group-member-list"></div>

            <div class="chat-modal-foot">
                <span id="chat-group-error" class="chat-modal-error"></span>
                <button type="submit" class="chat-modal-submit">Create group</button>
            </div>
        </form>
    </div>
</div>

{{-- Group settings modal (rename + add members) --}}
@if(isset($group))
<div id="chat-group-settings-modal" class="chat-modal" hidden>
    <div class="chat-modal-backdrop" data-close="settings-modal"></div>
    <div class="chat-modal-card">
        <div class="chat-modal-head">
            <h3>Group settings</h3>
            <button type="button" class="chat-modal-close" data-close="settings-modal"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="chat-modal-body">
            <form id="chat-group-rename-form" class="chat-settings-section">
                <label class="chat-form-label">Group name</label>
                <div class="chat-rename-row">
                    <input type="text" id="chat-group-rename-input" maxlength="80" required value="{{ $group->name }}">
                    <button type="submit" class="chat-modal-submit chat-rename-btn">Save</button>
                </div>
                <span id="chat-group-rename-status" class="chat-modal-status"></span>
            </form>

            <div class="chat-settings-section">
                <label class="chat-form-label">Current members (<span id="chat-current-member-count">{{ $group->participants->count() }}</span>)</label>
                <div class="chat-current-members" id="chat-current-members">
                    @foreach($group->participants as $p)
                        <span class="chat-member-pill" data-member-id="{{ $p->id }}">
                            <span class="chat-avatar chat-avatar-sm" style="background:{{ $p->avatar_color ?? '#4F6DF0' }}">
                                {{ strtoupper(substr($p->name, 0, 1)) }}
                            </span>
                            <span class="chat-member-name">{{ $p->name }}</span>
                            @if($p->id === $group->created_by)
                                <small class="chat-member-admin">· admin</small>
                            @elseif($me->id === $group->created_by)
                                <button type="button"
                                        class="chat-member-remove"
                                        data-remove-member-id="{{ $p->id }}"
                                        title="Remove from group">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>

            <form id="chat-group-add-form" class="chat-settings-section">
                <label class="chat-form-label">Add members</label>
                <input type="search" id="chat-group-add-search" placeholder="Search users...">
                <div id="chat-group-add-list" class="chat-group-member-list"></div>
                <div class="chat-modal-foot">
                    <span id="chat-group-add-status" class="chat-modal-status"></span>
                    <button type="submit" class="chat-modal-submit">Add members</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Emoji picker popup, positioned by JS near the clicked message --}}
<div id="chat-emoji-picker" class="chat-emoji-picker" hidden>
    <button type="button" class="chat-emoji-opt" data-emoji="👍">👍</button>
    <button type="button" class="chat-emoji-opt" data-emoji="❤️">❤️</button>
    <button type="button" class="chat-emoji-opt" data-emoji="😂">😂</button>
    <button type="button" class="chat-emoji-opt" data-emoji="😮">😮</button>
    <button type="button" class="chat-emoji-opt" data-emoji="😢">😢</button>
    <button type="button" class="chat-emoji-opt" data-emoji="🎉">🎉</button>
    <button type="button" class="chat-emoji-opt" data-emoji="🔥">🔥</button>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/chat.css') }}">
@endpush

@push('scripts')
<script>
// Tell the global chat-realtime listener (loaded via x-chat-realtime in the layout)
// to stand down — this page handles its own toast/inline UX.
window.ChatActive = true;
window.ChatData = {
    me: { id: {{ $me->id }}, name: @json($me->name) },
    csrfToken: '{{ csrf_token() }}',
    searchUrl: '{{ route('chat.search') }}',
    createGroupUrl: '{{ route('chat.groups.create') }}',
    @if(isset($conversation))
    conversationId: {{ $conversation->id }},
    isGroup: {{ isset($group) ? 'true' : 'false' }},
    leaveGroupUrl: @if(isset($group)) '{{ route('chat.groups.leave', $group) }}' @else null @endif,
    updateGroupUrl: @if(isset($group)) '{{ route('chat.groups.update', $group) }}' @else null @endif,
    addMembersUrl:  @if(isset($group)) '{{ route('chat.groups.members.add', $group) }}' @else null @endif,
    existingMemberIds: @if(isset($group)) @json($group->participants->pluck('id')->values()) @else [] @endif,
    partnerId: @if(isset($partner)) {{ $partner->id }} @else null @endif,
    partnerName: @if(isset($partner)) @json($partner->name) @else null @endif,
    sendUrl: '{{ route('chat.messages.store', $conversation) }}',
    @else
    conversationId: null,
    isGroup: false,
    leaveGroupUrl: null,
    updateGroupUrl: null,
    addMembersUrl: null,
    existingMemberIds: [],
    partnerId: null,
    partnerName: null,
    sendUrl: null,
    @endif
    allUsers: @json($users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'role' => $u->role])->values()),
    reverb: {
        key:  '{{ env('REVERB_APP_KEY') }}',
        host: '{{ env('REVERB_HOST', '127.0.0.1') }}',
        port: {{ (int) env('REVERB_PORT', 8080) }},
        scheme: '{{ env('REVERB_SCHEME', 'http') }}',
    }
};
</script>
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script src="{{ asset('js/pages/chat.js') }}"></script>
@endpush
@endsection
