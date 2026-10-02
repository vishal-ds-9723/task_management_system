(function () {
    'use strict';

    const data = window.ChatData;
    if (!data) return;

    // ─────────── DOM refs ───────────
    const search        = document.getElementById('chat-user-search');
    const allUsersList  = document.getElementById('chat-all-users');
    const searchPanel   = document.getElementById('chat-search-results');
    const searchList    = document.getElementById('chat-search-list');
    const searchMeta    = document.getElementById('chat-search-meta');
    const searchClear   = document.getElementById('chat-search-clear');
    const msgList       = document.getElementById('chat-messages');
    const form          = document.getElementById('chat-form');
    const input         = document.getElementById('chat-input');
    const sendBtn       = form ? form.querySelector('.chat-send-btn') : null;
    const partnerStatus = document.getElementById('chat-partner-status');
    const partnerRoleText = partnerStatus ? partnerStatus.textContent : '';
    const fileInput     = document.getElementById('chat-file-input');
    const attachPreview = document.getElementById('chat-attachment-preview');
    const attachInfo    = document.getElementById('chat-attach-info');
    const attachClear   = document.getElementById('chat-attach-clear');

    // ─────────── Helpers ───────────
    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }
    function formatTime(iso) {
        try {
            return new Date(iso).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        } catch (e) { return ''; }
    }
    function debounce(fn, ms) {
        let t;
        return function () {
            const ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, ms);
        };
    }
    function setOnline(userId, isOnline) {
        document.querySelectorAll('[data-online-for="' + userId + '"]').forEach(function (el) {
            el.classList.toggle('is-online', !!isOnline);
        });
    }
    function formatBytes(n) {
        if (!n && n !== 0) return '';
        if (n < 1024) return n + ' B';
        if (n < 1024 * 1024) return (n / 1024).toFixed(1) + ' KB';
        return (n / 1024 / 1024).toFixed(1) + ' MB';
    }

    // ─────────── Search panel ───────────
    function showSearchResults(results, query) {
        if (!searchPanel || !searchList) return;
        searchPanel.style.display = 'block';
        searchMeta.textContent = results.length
            ? results.length + ' result' + (results.length === 1 ? '' : 's') + ' for "' + query + '"'
            : 'No matches for "' + query + '"';
        searchList.innerHTML = results.map(function (r) {
            const partner = r.partner || { name: 'Unknown', avatar_color: '#999' };
            const initial = (partner.name || '?').charAt(0).toUpperCase();
            const me = data.me.id === r.sender_id ? 'You' : escapeHtml(r.sender_name || partner.name || '');
            const partnerHref = partner.id ? '/chat/users/' + partner.id : '#';
            return '<a class="chat-search-item" href="' + partnerHref + '">' +
                '<span class="chat-avatar chat-avatar-sm" style="background:' + escapeHtml(partner.avatar_color) + '">' + escapeHtml(initial) + '</span>' +
                '<span class="chat-search-text">' +
                    '<span class="chat-search-row1"><strong>' + escapeHtml(partner.name) + '</strong>' +
                    '<span class="chat-search-time">' + formatTime(r.created_at) + '</span></span>' +
                    '<span class="chat-search-row2"><em>' + me + ':</em> ' + escapeHtml(r.body) + '</span>' +
                '</span>' +
            '</a>';
        }).join('');
    }
    function hideSearchResults() {
        if (!searchPanel) return;
        searchPanel.style.display = 'none';
        searchList.innerHTML = '';
    }

    const fetchSearch = debounce(async function (q) {
        if (q.length < 2) { hideSearchResults(); return; }
        try {
            const res = await fetch(data.searchUrl + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const json = await res.json();
            showSearchResults(json.results || [], q);
        } catch (e) { console.error('Search failed', e); }
    }, 250);

    if (search && allUsersList) {
        search.addEventListener('input', function () {
            const q = this.value.trim();
            const lower = q.toLowerCase();
            allUsersList.querySelectorAll('.chat-user-row').forEach(function (row) {
                const name = row.dataset.name || '';
                row.style.display = (lower === '' || name.includes(lower)) ? '' : 'none';
            });
            if (q.length >= 2) fetchSearch(q);
            else hideSearchResults();
        });
    }
    if (searchClear) {
        searchClear.addEventListener('click', function () {
            if (search) search.value = '';
            allUsersList && allUsersList.querySelectorAll('.chat-user-row').forEach(function (r) { r.style.display = ''; });
            hideSearchResults();
        });
    }

    // ─────────── Auto-scroll ───────────
    if (msgList) msgList.scrollTop = msgList.scrollHeight;

    // ─────────── Render a message bubble ───────────
    function renderAttachmentHtml(att) {
        if (!att) return '';
        if (att.is_image) {
            return '<a href="' + escapeHtml(att.url) + '" target="_blank" class="chat-attachment-image">' +
                   '<img src="' + escapeHtml(att.url) + '" alt="' + escapeHtml(att.name) + '" loading="lazy"></a>';
        }
        return '<a href="' + escapeHtml(att.url) + '" target="_blank" class="chat-attachment-file">' +
               '<i class="fa-solid fa-paperclip"></i>' +
               '<span><span class="chat-attach-name">' + escapeHtml(att.name) + '</span>' +
               '<span class="chat-attach-size">' + formatBytes(att.size) + '</span></span></a>';
    }

    function appendMessage(msg, isMine) {
        if (!msgList) return;
        if (msgList.querySelector('[data-id="' + msg.id + '"]')) return;
        const wrap = document.createElement('div');
        wrap.className = 'chat-msg ' + (isMine ? 'mine' : 'theirs');
        wrap.dataset.id = msg.id;
        wrap.dataset.mine = isMine ? '1' : '0';

        if (isMine) {
            const tools = document.createElement('div');
            tools.className = 'chat-msg-tools';
            tools.innerHTML =
                '<button type="button" class="chat-msg-tool" data-action="edit" title="Edit"><i class="fa-solid fa-pen"></i></button>' +
                '<button type="button" class="chat-msg-tool" data-action="delete" title="Delete"><i class="fa-solid fa-trash"></i></button>';
            wrap.appendChild(tools);
        }

        const bubble = document.createElement('div');
        bubble.className = 'chat-msg-bubble';
        bubble.innerHTML = renderAttachmentHtml(msg.attachment) +
            (msg.body ? '<div class="chat-msg-text">' + escapeHtml(msg.body).replace(/\n/g, '<br>') + '</div>' : '');

        const meta = document.createElement('div');
        meta.className = 'chat-msg-meta';
        const time = document.createElement('span');
        time.textContent = formatTime(msg.created_at);
        meta.appendChild(time);
        if (isMine) {
            const tick = document.createElement('span');
            tick.className = 'chat-tick sent';
            tick.title = 'Sent';
            tick.innerHTML = '<i class="fa-solid fa-check"></i>';
            meta.appendChild(tick);
        }

        wrap.appendChild(bubble);
        wrap.appendChild(meta);
        msgList.appendChild(wrap);
        msgList.scrollTop = msgList.scrollHeight;
    }

    // ─────────── Edit / Delete on my own messages ───────────
    function applyEdit(messageId, newBody, editedAtIso) {
        const wrap = msgList && msgList.querySelector('[data-id="' + messageId + '"]');
        if (!wrap) return;
        const text = wrap.querySelector('.chat-msg-text');
        if (text) text.innerHTML = escapeHtml(newBody).replace(/\n/g, '<br>');
        else {
            const newText = document.createElement('div');
            newText.className = 'chat-msg-text';
            newText.innerHTML = escapeHtml(newBody).replace(/\n/g, '<br>');
            wrap.querySelector('.chat-msg-bubble').appendChild(newText);
        }
        const meta = wrap.querySelector('.chat-msg-meta');
        if (meta && !meta.querySelector('.chat-edited-tag')) {
            const tag = document.createElement('span');
            tag.className = 'chat-edited-tag';
            tag.textContent = 'edited';
            tag.title = 'Edited at ' + formatTime(editedAtIso);
            // Insert before the tick if present
            const tick = meta.querySelector('.chat-tick');
            if (tick) meta.insertBefore(tag, tick);
            else meta.appendChild(tag);
        }
    }

    function beginInlineEdit(wrap, messageId) {
        if (!wrap || wrap.querySelector('.chat-msg-edit-area')) return; // already editing

        const text = wrap.querySelector('.chat-msg-text');
        const originalHtml = text ? text.innerHTML : '';
        const current = text ? text.textContent : '';

        // Build inline editor
        const editor = document.createElement('div');
        editor.className = 'chat-msg-edit-area';
        editor.innerHTML =
            '<textarea class="chat-msg-edit-input" maxlength="5000" rows="2"></textarea>' +
            '<div class="chat-msg-edit-actions">' +
                '<button type="button" class="chat-msg-edit-cancel" title="Cancel (Esc)">Cancel</button>' +
                '<button type="button" class="chat-msg-edit-save" title="Save (Enter)">Save</button>' +
            '</div>';

        // Swap text node out for editor
        if (text) text.style.display = 'none';
        wrap.querySelector('.chat-msg-bubble').appendChild(editor);

        const input = editor.querySelector('.chat-msg-edit-input');
        const saveBtn = editor.querySelector('.chat-msg-edit-save');
        const cancelBtn = editor.querySelector('.chat-msg-edit-cancel');
        input.value = current;
        input.focus();
        // Place cursor at end + auto-grow
        input.setSelectionRange(input.value.length, input.value.length);
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 160) + 'px';
        input.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 160) + 'px';
        });

        function close() {
            editor.remove();
            if (text) {
                text.innerHTML = originalHtml;
                text.style.display = '';
            }
        }

        async function commit() {
            const next = (input.value || '').trim();
            if (next === '' || next === current) { close(); return; }
            saveBtn.disabled = true;
            try {
                const res = await fetch('/chat/messages/' + messageId, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': data.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ body: next }),
                });
                if (!res.ok) {
                    saveBtn.disabled = false;
                    alert('Could not save edit.');
                    return;
                }
                const json = await res.json();
                editor.remove();
                if (text) text.style.display = '';
                applyEdit(messageId, json.body, json.edited_at);
            } catch (err) {
                saveBtn.disabled = false;
                console.error(err);
            }
        }

        saveBtn.addEventListener('click', commit);
        cancelBtn.addEventListener('click', close);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); commit(); }
            else if (e.key === 'Escape') { e.preventDefault(); close(); }
        });
    }

    function applyDelete(messageId) {
        const wrap = msgList && msgList.querySelector('[data-id="' + messageId + '"]');
        if (!wrap) return;
        wrap.classList.add('deleted');
        const bubble = wrap.querySelector('.chat-msg-bubble');
        if (bubble) bubble.innerHTML = '<em class="chat-msg-deleted-text">Message deleted</em>';
        const tools = wrap.querySelector('.chat-msg-tools');
        if (tools) tools.remove();
    }

    // ─────────── Group: create + leave ───────────
    const groupBtn      = document.getElementById('chat-new-group-btn');
    const groupModal    = document.getElementById('chat-group-modal');
    const groupForm     = document.getElementById('chat-group-form');
    const groupName     = document.getElementById('chat-group-name');
    const groupMemberSearch = document.getElementById('chat-group-member-search');
    const groupMemberList   = document.getElementById('chat-group-member-list');
    const groupError    = document.getElementById('chat-group-error');
    const leaveBtn      = document.getElementById('chat-leave-group-btn');
    let selectedMemberIds = new Set();

    function renderGroupMembers(filter) {
        if (!groupMemberList) return;
        const q = (filter || '').toLowerCase();
        groupMemberList.innerHTML = '';
        (data.allUsers || []).forEach(function (u) {
            if (q && !u.name.toLowerCase().includes(q)) return;
            const id = 'gm-' + u.id;
            const row = document.createElement('label');
            row.className = 'chat-group-member';
            row.htmlFor = id;
            row.innerHTML =
                '<input type="checkbox" id="' + id + '" value="' + u.id + '"' + (selectedMemberIds.has(u.id) ? ' checked' : '') + '>' +
                '<span><strong>' + escapeHtml(u.name) + '</strong> <em>' + escapeHtml(u.role || '') + '</em></span>';
            row.querySelector('input').addEventListener('change', function () {
                if (this.checked) selectedMemberIds.add(u.id);
                else selectedMemberIds.delete(u.id);
            });
            groupMemberList.appendChild(row);
        });
    }
    function openGroupModal() {
        if (!groupModal) return;
        selectedMemberIds = new Set();
        if (groupName) groupName.value = '';
        if (groupMemberSearch) groupMemberSearch.value = '';
        if (groupError) groupError.textContent = '';
        renderGroupMembers('');
        groupModal.removeAttribute('hidden');
    }
    function closeGroupModal() {
        if (!groupModal) return;
        groupModal.setAttribute('hidden', 'hidden');
    }

    if (groupBtn) groupBtn.addEventListener('click', openGroupModal);
    if (groupModal) {
        groupModal.querySelectorAll('[data-close="modal"]').forEach(function (el) {
            el.addEventListener('click', closeGroupModal);
        });
    }
    if (groupMemberSearch) {
        groupMemberSearch.addEventListener('input', function () {
            renderGroupMembers(this.value);
        });
    }
    if (groupForm) {
        groupForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const name = (groupName.value || '').trim();
            if (!name) { groupError.textContent = 'Please name the group.'; return; }
            const ids = Array.from(selectedMemberIds);
            if (ids.length === 0) { groupError.textContent = 'Pick at least one member.'; return; }
            try {
                const res = await fetch(data.createGroupUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': data.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ name: name, member_ids: ids }),
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    groupError.textContent = err.message || 'Could not create group.';
                    return;
                }
                const json = await res.json();
                window.location.href = json.url;
            } catch (err) {
                groupError.textContent = 'Network error.';
            }
        });
    }

    // ─────────── Group settings (rename + add members) ───────────
    const settingsBtn   = document.getElementById('chat-group-settings-btn');
    const settingsModal = document.getElementById('chat-group-settings-modal');
    const renameForm    = document.getElementById('chat-group-rename-form');
    const renameInput   = document.getElementById('chat-group-rename-input');
    const renameStatus  = document.getElementById('chat-group-rename-status');
    const renameDisplay = document.getElementById('chat-group-name-display');
    const addForm       = document.getElementById('chat-group-add-form');
    const addSearch     = document.getElementById('chat-group-add-search');
    const addList       = document.getElementById('chat-group-add-list');
    const addStatus     = document.getElementById('chat-group-add-status');
    let pendingAddIds   = new Set();

    function renderAddMemberCandidates(filter) {
        if (!addList) return;
        const q = (filter || '').toLowerCase();
        const existing = new Set(data.existingMemberIds || []);
        addList.innerHTML = '';
        (data.allUsers || []).forEach(function (u) {
            if (existing.has(u.id)) return; // skip already-in-group
            if (q && !u.name.toLowerCase().includes(q)) return;
            const id = 'addmem-' + u.id;
            const row = document.createElement('label');
            row.className = 'chat-group-member';
            row.htmlFor = id;
            row.innerHTML =
                '<input type="checkbox" id="' + id + '" value="' + u.id + '"' + (pendingAddIds.has(u.id) ? ' checked' : '') + '>' +
                '<span><strong>' + escapeHtml(u.name) + '</strong> <em>' + escapeHtml(u.role || '') + '</em></span>';
            row.querySelector('input').addEventListener('change', function () {
                if (this.checked) pendingAddIds.add(u.id);
                else pendingAddIds.delete(u.id);
            });
            addList.appendChild(row);
        });
        if (! addList.children.length) {
            addList.innerHTML = '<div class="chat-empty-small">No more users to add.</div>';
        }
    }

    function openSettings() {
        if (!settingsModal) return;
        pendingAddIds = new Set();
        if (addSearch) addSearch.value = '';
        if (renameStatus) renameStatus.textContent = '';
        if (addStatus) addStatus.textContent = '';
        renderAddMemberCandidates('');
        settingsModal.removeAttribute('hidden');
    }
    function closeSettings() {
        if (!settingsModal) return;
        settingsModal.setAttribute('hidden', 'hidden');
    }
    if (settingsBtn) settingsBtn.addEventListener('click', openSettings);
    if (settingsModal) {
        settingsModal.querySelectorAll('[data-close="settings-modal"]').forEach(function (el) {
            el.addEventListener('click', closeSettings);
        });
    }
    if (addSearch) addSearch.addEventListener('input', function () { renderAddMemberCandidates(this.value); });

    if (renameForm && data.updateGroupUrl) {
        renameForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const name = (renameInput.value || '').trim();
            if (!name) { renameStatus.textContent = 'Name required.'; return; }
            renameStatus.textContent = 'Saving...';
            try {
                const res = await fetch(data.updateGroupUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': data.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ name: name }),
                });
                if (!res.ok) { renameStatus.textContent = 'Could not save.'; return; }
                const json = await res.json();
                if (renameDisplay) renameDisplay.textContent = json.name;
                renameStatus.textContent = 'Saved.';
                setTimeout(function () { renameStatus.textContent = ''; }, 1500);
            } catch (err) { renameStatus.textContent = 'Network error.'; }
        });
    }

    if (addForm && data.addMembersUrl) {
        addForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const ids = Array.from(pendingAddIds);
            if (ids.length === 0) { addStatus.textContent = 'Pick at least one user.'; return; }
            addStatus.textContent = 'Adding...';
            try {
                const res = await fetch(data.addMembersUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': data.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ member_ids: ids }),
                });
                if (!res.ok) { addStatus.textContent = 'Could not add.'; return; }
                const json = await res.json();
                addStatus.textContent = 'Added ' + (json.count || 0) + ' member(s). Reload to see updated header.';
                // Track them as now-existing so they don't show in the picker
                ids.forEach(function (id) { data.existingMemberIds.push(id); });
                pendingAddIds = new Set();
                renderAddMemberCandidates(addSearch ? addSearch.value : '');
            } catch (err) { addStatus.textContent = 'Network error.'; }
        });
    }

    // Remove-member buttons inside the settings modal
    if (settingsModal) {
        settingsModal.addEventListener('click', async function (e) {
            const btn = e.target.closest('.chat-member-remove');
            if (!btn) return;
            const userId = parseInt(btn.dataset.removeMemberId, 10);
            if (!userId) return;
            if (!window.confirm('Remove this member from the group?')) return;
            btn.disabled = true;
            try {
                const res = await fetch('/chat/groups/' + data.conversationId + '/members/' + userId, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': data.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) { alert('Could not remove member.'); btn.disabled = false; return; }
                // Remove the pill from the UI immediately; the broadcast will update other viewers
                const pill = btn.closest('.chat-member-pill');
                if (pill) pill.remove();
                // Update existingMemberIds so the user reappears in "Add members"
                data.existingMemberIds = (data.existingMemberIds || []).filter(function (id) { return id !== userId; });
                renderAddMemberCandidates(addSearch ? addSearch.value : '');
                const json = await res.json();
                const cnt = document.getElementById('chat-current-member-count');
                if (cnt && typeof json.total_members === 'number') cnt.textContent = String(json.total_members);
                updateHeaderMemberCount(json.total_members);
            } catch (err) { console.error(err); btn.disabled = false; }
        });
    }

    function updateHeaderMemberCount(total) {
        const status = document.getElementById('chat-partner-status');
        if (!status || typeof total !== 'number') return;
        // Best-effort rewrite of the leading "N members" segment
        status.textContent = total + ' members';
    }

    if (leaveBtn && data.leaveGroupUrl) {
        leaveBtn.addEventListener('click', async function () {
            if (!window.confirm('Leave this group?')) return;
            try {
                const res = await fetch(data.leaveGroupUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': data.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) { alert('Could not leave group.'); return; }
                window.location.href = '/chat';
            } catch (err) { console.error(err); }
        });
    }

    // ─────────── Reactions ───────────
    const emojiPicker = document.getElementById('chat-emoji-picker');
    let pickerTargetMessageId = null;

    function openPickerFor(button) {
        if (!emojiPicker) return;
        const wrap = button.closest('.chat-msg');
        if (!wrap) return;
        pickerTargetMessageId = wrap.dataset.id;
        const rect = button.getBoundingClientRect();
        emojiPicker.style.top = (window.scrollY + rect.bottom + 6) + 'px';
        emojiPicker.style.left = (window.scrollX + rect.left) + 'px';
        emojiPicker.removeAttribute('hidden');
    }
    function closePicker() {
        if (!emojiPicker) return;
        emojiPicker.setAttribute('hidden', 'hidden');
        pickerTargetMessageId = null;
    }
    document.addEventListener('click', function (e) {
        if (!emojiPicker || emojiPicker.hasAttribute('hidden')) return;
        if (!emojiPicker.contains(e.target) && !e.target.closest('[data-action="react"]')) {
            closePicker();
        }
    });

    async function toggleReaction(messageId, emoji) {
        try {
            const res = await fetch('/chat/messages/' + messageId + '/react', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': data.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ emoji: emoji }),
            });
            if (!res.ok) { console.error('React failed', res.status); return; }
            const json = await res.json();
            renderReactions(messageId, json.counts || {}, json.mine || []);
        } catch (err) { console.error(err); }
    }

    function renderReactions(messageId, counts, myEmojis) {
        const row = document.querySelector('.chat-reactions[data-message-id="' + messageId + '"]');
        if (!row) return;
        row.innerHTML = '';
        Object.keys(counts).forEach(function (emoji) {
            const count = counts[emoji];
            if (count <= 0) return;
            const isMine = myEmojis.indexOf(emoji) !== -1;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'chat-reaction' + (isMine ? ' mine' : '');
            btn.dataset.emoji = emoji;
            btn.innerHTML = '<span class="chat-reaction-emoji">' + escapeHtml(emoji) + '</span>' +
                            '<span class="chat-reaction-count">' + count + '</span>';
            row.appendChild(btn);
        });
    }

    if (emojiPicker) {
        emojiPicker.addEventListener('click', function (e) {
            const btn = e.target.closest('.chat-emoji-opt');
            if (!btn || !pickerTargetMessageId) return;
            const emoji = btn.dataset.emoji;
            toggleReaction(pickerTargetMessageId, emoji);
            closePicker();
        });
    }

    if (msgList) {
        msgList.addEventListener('click', async function (e) {
            // Reaction-pill toggle (clicking an existing reaction)
            const reactBtn = e.target.closest('.chat-reaction');
            if (reactBtn) {
                const wrap = reactBtn.closest('.chat-msg');
                if (wrap) toggleReaction(wrap.dataset.id, reactBtn.dataset.emoji);
                return;
            }

            const tool = e.target.closest('.chat-msg-tool');
            if (!tool) return;

            // Open emoji picker (for any message, mine or not)
            if (tool.dataset.action === 'react') {
                openPickerFor(tool);
                return;
            }

            const wrap = tool.closest('.chat-msg');
            if (!wrap || wrap.dataset.mine !== '1' || wrap.classList.contains('deleted')) return;
            const id = wrap.dataset.id;
            const action = tool.dataset.action;

            if (action === 'edit') {
                beginInlineEdit(wrap, id);
            }

            if (action === 'delete') {
                if (!window.confirm('Delete this message?')) return;
                try {
                    const res = await fetch('/chat/messages/' + id, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': data.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (!res.ok) {
                        console.error('Delete failed', res.status, await res.text());
                        alert('Could not delete message.');
                        return;
                    }
                    applyDelete(id);
                } catch (err) { console.error(err); }
            }
        });
    }

    // ─────────── Mark a message bubble as read (turn tick blue) ───────────
    function markBubblesRead(messageIds) {
        messageIds.forEach(function (id) {
            const wrap = msgList && msgList.querySelector('[data-id="' + id + '"]');
            if (!wrap) return;
            const tick = wrap.querySelector('.chat-tick');
            if (!tick) return;
            tick.classList.remove('sent');
            tick.classList.add('read');
            tick.title = 'Read';
            tick.innerHTML = '<i class="fa-solid fa-check-double"></i>';
        });
    }

    // ─────────── Sidebar unread badges ───────────
    function setUnreadBadge(userId, count) {
        document.querySelectorAll('[data-unread-for="' + userId + '"]').forEach(function (el) {
            if (count > 0) {
                el.textContent = count > 99 ? '99+' : String(count);
                el.removeAttribute('hidden');
            } else {
                el.setAttribute('hidden', 'hidden');
            }
        });
    }
    function bumpUnreadBadge(userId) {
        document.querySelectorAll('[data-unread-for="' + userId + '"]').forEach(function (el) {
            const cur = parseInt(el.textContent || '0', 10) || 0;
            el.textContent = (cur + 1) > 99 ? '99+' : String(cur + 1);
            el.removeAttribute('hidden');
        });
    }

    // ─────────── File picker handling ───────────
    const MAX_FILE_BYTES = 500 * 1024 * 1024;

    function showAttachPreview(file) {
        if (!attachPreview || !attachInfo) return;
        attachInfo.textContent = file.name + ' (' + formatBytes(file.size) + ')';
        attachPreview.removeAttribute('hidden');
    }
    function clearAttach() {
        if (fileInput) fileInput.value = '';
        if (attachPreview) attachPreview.setAttribute('hidden', 'hidden');
        if (attachInfo) attachInfo.textContent = '';
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) { clearAttach(); return; }
            if (file.size > MAX_FILE_BYTES) {
                alert('File is too large. Max 500MB.');
                clearAttach();
                return;
            }
            showAttachPreview(file);
        });
    }
    if (attachClear) attachClear.addEventListener('click', clearAttach);

    // ─────────── Send a message (text + optional attachment) ───────────
    if (form && input && data.conversationId && data.sendUrl) {
        input.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.requestSubmit();
            }
        });

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const body = input.value.trim();
            const file = fileInput && fileInput.files && fileInput.files[0];
            if (!body && !file) return;

            sendBtn.disabled = true;
            try {
                const fd = new FormData();
                if (body) fd.append('body', body);
                if (file) fd.append('attachment', file);

                const res = await fetch(data.sendUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': data.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: fd,
                });
                if (!res.ok) {
                    const err = await res.text();
                    console.error('Send failed', res.status, err);
                    alert('Could not send message. ' + (res.status === 422 ? 'Check the file type/size.' : ''));
                    return;
                }
                const msg = await res.json();
                appendMessage(msg, true);
                input.value = '';
                input.style.height = 'auto';
                clearAttach();
                if (window._chatStopTyping) window._chatStopTyping();
            } catch (err) {
                console.error('Send error', err);
            } finally {
                sendBtn.disabled = false;
                input.focus();
            }
        });
    }

    // ─────────── Realtime ───────────
    if (typeof Pusher === 'undefined' || typeof Echo === 'undefined') {
        console.warn('Pusher/Echo not loaded — realtime updates disabled.');
        return;
    }
    window.Pusher = Pusher;
    const echo = new Echo({
        broadcaster: 'reverb',
        key: data.reverb.key,
        wsHost: data.reverb.host,
        wsPort: data.reverb.port,
        wssPort: data.reverb.port,
        forceTLS: data.reverb.scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: { headers: { 'X-CSRF-TOKEN': data.csrfToken } },
    });

    // Incoming events on my own private channel
    echo.private('chat.' + data.me.id)
        .listen('.message.sent', function (e) {
            if (e.conversation_id === data.conversationId) {
                appendMessage(e, false);
            } else {
                // Bump unread badge for the sender in the sidebar
                bumpUnreadBadge(e.user_id);
            }
        })
        .listen('.message.read', function (e) {
            // The recipient read my messages — flip their ticks to blue
            if (e.conversation_id === data.conversationId) {
                markBubblesRead(e.message_ids || []);
            }
        })
        .listen('.message.edited', function (e) {
            if (e.conversation_id === data.conversationId) {
                applyEdit(e.id, e.body, e.edited_at);
            }
        })
        .listen('.message.deleted', function (e) {
            if (e.conversation_id === data.conversationId) {
                applyDelete(e.id);
            }
        })
        .listen('.message.reacted', function (e) {
            if (e.conversation_id !== data.conversationId) return;
            // The actor's emoji set isn't mine — we only update the public counts.
            // 'mine' on this side is unchanged because the actor wasn't us.
            const myEmojis = currentMyEmojis(e.message_id);
            renderReactions(e.message_id, e.counts || {}, myEmojis);
        })
        .listen('.group.updated', function (e) {
            if (e.conversation_id !== data.conversationId) return;
            // Update group name in header
            const nameEl = document.getElementById('chat-group-name-display');
            if (nameEl) nameEl.textContent = e.name;
            // Also update the rename input if the settings modal happens to be open
            if (renameInput) renameInput.value = e.name;
        })
        .listen('.group.members.changed', function (e) {
            if (e.conversation_id !== data.conversationId) return;

            // Update header count
            updateHeaderMemberCount(e.total_members);

            const cnt = document.getElementById('chat-current-member-count');
            if (cnt) cnt.textContent = String(e.total_members);

            // Add pills for new members
            const list = document.getElementById('chat-current-members');
            if (list && Array.isArray(e.added)) {
                e.added.forEach(function (u) {
                    if (list.querySelector('[data-member-id="' + u.id + '"]')) return;
                    const pill = document.createElement('span');
                    pill.className = 'chat-member-pill';
                    pill.dataset.memberId = u.id;
                    pill.innerHTML =
                        '<span class="chat-avatar chat-avatar-sm" style="background:' + escapeHtml(u.avatar_color || '#4F6DF0') + '">' +
                            escapeHtml((u.name || '?').charAt(0).toUpperCase()) +
                        '</span>' +
                        '<span class="chat-member-name">' + escapeHtml(u.name) + '</span>';
                    list.appendChild(pill);
                    // Keep the candidates list in sync
                    if (! (data.existingMemberIds || []).includes(u.id)) {
                        data.existingMemberIds = (data.existingMemberIds || []).concat(u.id);
                    }
                });
                renderAddMemberCandidates(addSearch ? addSearch.value : '');
            }

            // Remove pills for removed members; if it's me, bounce back to /chat
            if (Array.isArray(e.removed_ids)) {
                if (e.removed_ids.indexOf(data.me.id) !== -1) {
                    window.location.href = '/chat';
                    return;
                }
                e.removed_ids.forEach(function (uid) {
                    const pill = list && list.querySelector('[data-member-id="' + uid + '"]');
                    if (pill) pill.remove();
                    data.existingMemberIds = (data.existingMemberIds || []).filter(function (id) { return id !== uid; });
                });
                renderAddMemberCandidates(addSearch ? addSearch.value : '');
            }
        });

    // Helper: read current "mine" set from the existing rendered pills
    function currentMyEmojis(messageId) {
        const row = document.querySelector('.chat-reactions[data-message-id="' + messageId + '"]');
        if (!row) return [];
        return Array.from(row.querySelectorAll('.chat-reaction.mine')).map(function (b) { return b.dataset.emoji; });
    }

    // Presence
    echo.join('chat.online')
        .here(function (users) { users.forEach(function (u) { setOnline(u.id, true); }); })
        .joining(function (u) { setOnline(u.id, true); })
        .leaving(function (u) { setOnline(u.id, false); })
        .error(function (err) { console.warn('Presence channel error', err); });

    // Typing whispers
    if (data.conversationId && input) {
        const convoChannel = echo.private('chat.conversation.' + data.conversationId);

        let typingTimeout = null;
        let lastTypingSent = 0;

        function sendTyping() {
            const now = Date.now();
            if (now - lastTypingSent > 2000) {
                convoChannel.whisper('typing', { userId: data.me.id, name: data.me.name });
                lastTypingSent = now;
            }
        }
        function stopTyping() {
            convoChannel.whisper('stopped-typing', { userId: data.me.id });
            lastTypingSent = 0;
        }
        window._chatStopTyping = stopTyping;

        input.addEventListener('input', function () {
            if (this.value.trim() === '') { stopTyping(); return; }
            sendTyping();
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(stopTyping, 2500);
        });

        let clearStatusTimer = null;
        convoChannel
            .listenForWhisper('typing', function (e) {
                if (e.userId === data.me.id) return;
                if (partnerStatus) partnerStatus.innerHTML = '<span class="chat-typing"><span></span><span></span><span></span></span> typing...';
                clearTimeout(clearStatusTimer);
                clearStatusTimer = setTimeout(function () {
                    if (partnerStatus) partnerStatus.textContent = partnerRoleText;
                }, 3000);
            })
            .listenForWhisper('stopped-typing', function (e) {
                if (e.userId === data.me.id) return;
                clearTimeout(clearStatusTimer);
                if (partnerStatus) partnerStatus.textContent = partnerRoleText;
            });
    }
})();
