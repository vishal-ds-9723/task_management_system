@auth
{{--
    Global chat realtime listener.
    Subscribes to the user's private chat channel from EVERY page and shows:
      - In-app toast popup (bottom-right)
      - Native browser notification (if user grants permission)
      - Short ding sound (Web Audio API — no asset file needed)
    The chat page suppresses these via window.ChatActive = true so it can render messages itself.
--}}
<div id="chat-toast-stack" class="chat-toast-stack" aria-live="polite"></div>

<style>
.chat-toast-stack {
    position: fixed;
    right: 20px;
    bottom: 90px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 99997;
    pointer-events: none;
    max-width: 340px;
}
.chat-toast {
    background: var(--card, #fff);
    border: 1px solid var(--border, #e5e7eb);
    border-radius: 12px;
    box-shadow: 0 14px 40px rgba(0,0,0,.18);
    padding: 12px 14px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
    pointer-events: auto;
    transform: translateX(380px);
    opacity: 0;
    transition: transform .3s cubic-bezier(.22,1,.36,1), opacity .3s;
    text-decoration: none;
    color: inherit;
}
.chat-toast.shown { transform: translateX(0); opacity: 1; }
.chat-toast:hover { border-color: var(--primary, #4F6DF0); }
.chat-toast-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.chat-toast-body { flex: 1; min-width: 0; }
.chat-toast-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text, #111);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.chat-toast-preview {
    font-size: 12px;
    color: var(--text2, #6b7280);
    margin-top: 2px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.chat-toast-close {
    background: none;
    border: none;
    color: var(--text3, #9ca3af);
    cursor: pointer;
    font-size: 12px;
    padding: 2px;
    flex-shrink: 0;
}
.chat-toast-close:hover { color: #ef4444; }
@media (max-width: 768px) {
    .chat-toast-stack { right: 12px; bottom: 80px; max-width: calc(100vw - 24px); }
}
</style>

{{-- Reverb/Echo deps (CDN). Idempotent — won't double-load if /chat already loaded them. --}}
<script>
window.ChatGlobalConfig = {
    me: { id: {{ auth()->id() }}, name: @json(auth()->user()->name) },
    csrfToken: '{{ csrf_token() }}',
    reverb: {
        key:  '{{ env('REVERB_APP_KEY') }}',
        host: '{{ env('REVERB_HOST', '127.0.0.1') }}',
        port: {{ (int) env('REVERB_PORT', 8080) }},
        scheme: '{{ env('REVERB_SCHEME', 'http') }}',
    },
};
</script>
@if(! request()->routeIs('chat.*'))
    {{-- /chat already loads these; non-chat pages need them too --}}
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
@endif
<script src="{{ asset('js/pages/chat-realtime.js') }}" defer></script>
@endauth
