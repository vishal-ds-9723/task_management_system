/**
 * Global chat realtime listener.
 * Loaded on EVERY page (via <x-chat-realtime />). Subscribes to private-chat.{userId}
 * and surfaces incoming messages as toast + sound + native notification.
 * The /chat page sets window.ChatActive = true so we don't double-fire there.
 */
(function () {
    'use strict';

    const cfg = window.ChatGlobalConfig;
    if (!cfg || !cfg.me) return;

    // The chat page already handles its own UX. Don't duplicate.
    if (window.ChatActive) return;

    // ─────────── DOM ───────────
    const stack = document.getElementById('chat-toast-stack');

    // ─────────── Helpers ───────────
    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    // ─────────── Sound (Web Audio API "ding") ───────────
    // Uses localStorage flag chat.muted=1 to skip.
    const MUTED_KEY = 'chat.muted';
    let audioCtx = null;
    function getCtx() {
        if (audioCtx) return audioCtx;
        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) { return null; }
        return audioCtx;
    }
    let lastDing = 0;
    function playDing() {
        if (localStorage.getItem(MUTED_KEY) === '1') return;
        // Throttle: at most one ding per 1.5s to avoid spam in busy threads
        const now = Date.now();
        if (now - lastDing < 1500) return;
        lastDing = now;

        const ctx = getCtx();
        if (!ctx) return;
        const o = ctx.createOscillator();
        const g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.type = 'sine';
        o.frequency.setValueAtTime(880, ctx.currentTime);
        o.frequency.exponentialRampToValueAtTime(660, ctx.currentTime + 0.12);
        g.gain.setValueAtTime(0.0001, ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.18, ctx.currentTime + 0.02);
        g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.18);
        o.start(ctx.currentTime);
        o.stop(ctx.currentTime + 0.2);
    }

    // ─────────── Native browser notification ───────────
    let permission = 'Notification' in window ? Notification.permission : 'denied';
    function maybeRequestPermission() {
        if (!('Notification' in window)) return;
        if (permission === 'default') {
            // Ask once, lazily, on first message arrival
            Notification.requestPermission().then(p => { permission = p; });
        }
    }
    function showNative(title, body, link) {
        if (!('Notification' in window) || permission !== 'granted') return;
        if (document.hasFocus()) return; // skip if user is already looking at the tab
        try {
            const n = new Notification(title, {
                body: body,
                icon: '/icons/icon-192.png',
                tag: 'chat-' + Date.now(),
                silent: true, // we handle sound ourselves so the OS doesn't double up
            });
            n.onclick = function () { window.focus(); if (link) window.location.href = link; n.close(); };
            setTimeout(() => n.close(), 7000);
        } catch (e) { /* some browsers block constructor in worker contexts; ignore */ }
    }

    // ─────────── Toast ───────────
    function avatarHtmlFor(name) {
        const initial = (name || '?').charAt(0).toUpperCase();
        // Simple deterministic color from name
        let hash = 0;
        for (let i = 0; i < (name || '').length; i++) hash = (hash * 31 + name.charCodeAt(i)) | 0;
        const hues = ['#4F6DF0', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#ec4899'];
        const bg = hues[Math.abs(hash) % hues.length];
        return '<span class="chat-toast-avatar" style="background:' + bg + '">' + escapeHtml(initial) + '</span>';
    }

    function showToast(payload) {
        if (!stack) return;

        const link = payload.conversation_link ||
            ('/chat/users/' + payload.user_id); // fallback

        const toast = document.createElement('a');
        toast.className = 'chat-toast';
        toast.href = link;
        toast.innerHTML =
            avatarHtmlFor(payload.user_name) +
            '<div class="chat-toast-body">' +
                '<div class="chat-toast-title">' + escapeHtml(payload.user_name || 'New message') + '</div>' +
                '<div class="chat-toast-preview">' + escapeHtml(payload.preview) + '</div>' +
            '</div>' +
            '<button type="button" class="chat-toast-close" aria-label="Dismiss"><i class="fa-solid fa-xmark"></i></button>';

        toast.querySelector('.chat-toast-close').addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dismiss(toast);
        });

        stack.appendChild(toast);
        // Animate in
        requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('shown')));
        // Auto-dismiss after 6s
        setTimeout(() => dismiss(toast), 6000);
    }
    function dismiss(toast) {
        if (!toast || !toast.parentNode) return;
        toast.classList.remove('shown');
        setTimeout(() => { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
    }

    // ─────────── Echo connection ───────────
    if (typeof Pusher === 'undefined' || typeof Echo === 'undefined') {
        // Couldn't load CDN — silent fail, no realtime
        return;
    }
    window.Pusher = Pusher;
    const echo = new Echo({
        broadcaster: 'reverb',
        key: cfg.reverb.key,
        wsHost: cfg.reverb.host,
        wsPort: cfg.reverb.port,
        wssPort: cfg.reverb.port,
        forceTLS: cfg.reverb.scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: { headers: { 'X-CSRF-TOKEN': cfg.csrfToken } },
    });

    echo.private('chat.' + cfg.me.id)
        .listen('.message.sent', function (e) {
            // Ignore messages I sent myself
            if (e.user_id === cfg.me.id) return;

            // Lazy: request native-notification permission on first incoming message
            maybeRequestPermission();

            // Build preview text (attachment-aware)
            let preview = e.body || '';
            if (!preview && e.attachment) {
                preview = e.attachment.is_image ? '📷 Photo' : '📎 ' + (e.attachment.name || 'Attachment');
            }
            if (preview.length > 140) preview = preview.slice(0, 137) + '…';

            // Choose a link target (1:1 vs group — we know via user_id; group conv detection requires extra info,
            // but pointing to the sender's 1:1 thread is a safe fallback that lands them in the right pane).
            const link = '/chat/users/' + e.user_id;

            showToast({
                user_id: e.user_id,
                user_name: e.user_name || 'Someone',
                preview: preview,
                conversation_link: link,
            });
            playDing();
            showNative(e.user_name || 'New message', preview, link);
        });
})();
