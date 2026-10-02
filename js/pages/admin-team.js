(function() {
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const ROLE_ICONS = {
        admin: 'fas fa-shield-alt', strategist: 'fas fa-chess-queen', designer: 'fas fa-palette',
        developer: 'fas fa-code', client: 'fas fa-building'
    };
    const ROLE_COLORS = {
        admin: '#EF4444', strategist: '#8B5CF6', designer: '#3B82F6',
        developer: '#F97316', client: '#0EA5E9'
    };
    const STATUS_COLORS = {
        todo: { bg: 'rgba(107,114,128,0.1)', text: '#6B7280', icon: 'fas fa-circle' },
        inprogress: { bg: 'rgba(79,109,240,0.1)', text: '#4F6DF0', icon: 'fas fa-spinner' },
        review: { bg: 'rgba(245,158,11,0.1)', text: '#F59E0B', icon: 'fas fa-eye' },
        completed: { bg: 'rgba(16,185,129,0.1)', text: '#10B981', icon: 'fas fa-check-circle' }
    };

    let currentDrawerMember = null;

    function normalizeRoleValue(value) {
        return (value || '')
            .toString()
            .trim()
            .toLowerCase()
            .replace(/\s+/g, '_')
            .replace(/[^a-z0-9_]/g, '')
            .slice(0, 50);
    }

    function formatRoleLabel(value) {
        return (value || '').toString().replace(/_/g, ' ');
    }

    function ensureRoleOption(prefix, roleValue, select = false) {
        const normalized = normalizeRoleValue(roleValue);
        if (!normalized) return null;

        const grid = document.getElementById(prefix + 'RoleGrid');
        if (!grid) return null;

        let radio = grid.querySelector('input[name="role"][value="' + normalized + '"]');
        if (!radio) {
            const rc = ROLE_COLORS[normalized] || '#6B7280';
            const icon = ROLE_ICONS[normalized] || 'fas fa-user-tag';
            const option = document.createElement('label');
            option.className = 'tm-role-option';
            option.style.setProperty('--rc', rc);
            option.innerHTML = `
                <input type="radio" name="role" value="${normalized}">
                <div class="tm-role-option-inner">
                    <i class="${icon}"></i>
                    <span>${formatRoleLabel(normalized)}</span>
                </div>
            `;
            grid.appendChild(option);
            radio = option.querySelector('input[name="role"]');
        }

        if (select && radio) {
            radio.checked = true;
        }

        return radio;
    }

    window.addCustomRole = function(prefix) {
        const input = document.getElementById(prefix + 'RoleInput');
        if (!input) return;

        const normalized = normalizeRoleValue(input.value);
        if (!normalized) {
            showFlash('Enter a role name to add.', 'error');
            return;
        }

        const radio = ensureRoleOption(prefix, normalized, true);
        if (!radio) return;

        input.value = '';

        const previewRole = document.getElementById(prefix + 'PreviewRole');
        if (previewRole) {
            previewRole.textContent = formatRoleLabel(normalized);
        }

        const form = document.getElementById(prefix + 'MemberForm');
        if (form) {
            validateSingleField(form, 'role', prefix === 'add', { live: false, showSuccess: true });
        }
    };

    // ══════════ Flash Messages ══════════
    function showFlash(message, type = 'success') {
        const existing = document.getElementById('tmFlash');
        if (existing) existing.remove();

        const flash = document.createElement('div');
        flash.id = 'tmFlash';
        flash.className = 'tm-flash ' + (type === 'success' ? 'tm-flash-success' : 'tm-flash-error');
        flash.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="tm-flash-close"><i class="fas fa-times"></i></button>
        `;
        document.querySelector('.team-container').prepend(flash);
        setTimeout(() => { flash.style.opacity = '0'; flash.style.transform = 'translateY(-8px)'; setTimeout(() => flash.remove(), 300); }, 5000);
    }

    // ══════════ Modal Open/Close ══════════
    window.openAddModal = function() {
        const modal = document.getElementById('addMemberModal');
        const form = document.getElementById('addMemberForm');
        form.reset();
        form.querySelectorAll('.tm-field-err').forEach(e => { e.classList.remove('show', 'success'); e.textContent = ''; });
        form.querySelectorAll('.tm-input-wrap').forEach(e => e.classList.remove('has-error', 'has-success'));
        document.getElementById('addAvatarInitials').textContent = '?';
        document.getElementById('addPreviewName').textContent = 'New Member';
        document.getElementById('addPreviewRole').textContent = 'Select a role';
        document.getElementById('addAvatarPreview').style.background = '#4F6DF0';
        document.getElementById('addPwBar').style.width = '0';
        document.getElementById('addPwText').textContent = '';
        const addRoleInput = document.getElementById('addRoleInput');
        if (addRoleInput) addRoleInput.value = '';
        const firstColor = form.querySelector('.tm-color-swatch input');
        if (firstColor) firstColor.checked = true;
        const firstRole = form.querySelector('#addRoleGrid input[name="role"]');
        if (firstRole) {
            firstRole.checked = true;
            document.getElementById('addPreviewRole').textContent = formatRoleLabel(firstRole.value);
        }
        document.querySelectorAll('#addAdditionalRolesContainer input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
        modal.classList.add('show');
    };

    window.openEditModal = function(member) {
        currentDrawerMember = member;
        document.getElementById('editModalTitle').innerHTML = '<i class="fas fa-pen" style="margin-right:8px;color:var(--primary)"></i>Edit — ' + member.name;
        document.getElementById('editModalSubtitle').textContent = member.email;
        document.getElementById('editName').value = member.name;
        document.getElementById('editEmail').value = member.email;
        document.getElementById('editPassword').value = '';
        document.getElementById('editMemberForm').dataset.memberId = member.id;

        const initials = member.name.split(' ').map(w => w.charAt(0).toUpperCase()).join('').slice(0,2);
        document.getElementById('editAvatarInitials').textContent = initials;
        document.getElementById('editPreviewName').textContent = member.name;
        document.getElementById('editPreviewRole').textContent = formatRoleLabel(member.role);
        document.getElementById('editAvatarPreview').style.background = member.avatar_color || '#4F6DF0';

        ensureRoleOption('add', member.role, false);
        const roleRadio = ensureRoleOption('edit', member.role, true);
        if (roleRadio) roleRadio.checked = true;

        const editRoleInput = document.getElementById('editRoleInput');
        if (editRoleInput) editRoleInput.value = '';

        const additionalRoles = Array.isArray(member.additional_roles) ? member.additional_roles : [];
        document.querySelectorAll('#editAdditionalRolesContainer input[type="checkbox"]').forEach(cb => {
            cb.checked = additionalRoles.includes(cb.value);
        });

        const color = member.avatar_color || '#4F6DF0';
        const colorRadio = document.querySelector('#editColorPalette input[value="' + color + '"]');
        if (colorRadio) colorRadio.checked = true;
        else {
            const first = document.querySelector('#editColorPalette input');
            if (first) first.checked = true;
        }

        const canManageMetricsInput = document.querySelector('#editMemberForm input[name="can_manage_social_metrics"][value="1"]');
        if (canManageMetricsInput) {
            canManageMetricsInput.checked = !!Number(member.can_manage_social_metrics);
        }

        const form = document.getElementById('editMemberForm');
        form.querySelectorAll('.tm-field-err').forEach(e => { e.classList.remove('show', 'success'); e.textContent = ''; });
        form.querySelectorAll('.tm-input-wrap').forEach(e => e.classList.remove('has-error', 'has-success'));
        document.getElementById('editPwBar').style.width = '0';
        document.getElementById('editPwText').textContent = '';

        document.getElementById('editMemberModal').classList.add('show');
    };

    // ══════════ AJAX Form Submission ══════════
    function getFormData(form) {
        const formData = new FormData(form);
        const data = {};
        for (const [key, value] of formData.entries()) {
            if (key.endsWith('[]')) {
                const cleanKey = key.slice(0, -2);
                if (!data[cleanKey]) data[cleanKey] = [];
                data[cleanKey].push(value);
            } else {
                data[key] = value;
            }
        }
        return data;
    }

    async function submitForm(form, url, method, isAdd) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right:6px"></i>Saving...';

        const payload = getFormData(form);
        payload.role = normalizeRoleValue(payload.role);

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 20000);

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload),
                signal: controller.signal
            });

            const contentType = response.headers.get('content-type') || '';
            const isJson = contentType.includes('application/json');
            const result = isJson ? await response.json() : null;

            if (response.ok && result?.success) {
                closeModal(isAdd ? 'addMemberModal' : 'editMemberModal');
                showFlash(result.message, 'success');

                if (isAdd) {
                    // Add new card to grid
                    addMemberCard(result.member);
                } else {
                    // Update existing card
                    updateMemberCard(result.member);
                }

                // Update stats
                if (result.stats) updateStats(result.stats);
            } else {
                if (result?.errors) {
                    displayFormErrors(form, result.errors);
                } else {
                    const sessionExpired = response.redirected || response.status === 401 || response.status === 419;
                    const fallbackMessage = response.status >= 500
                        ? 'Server error while saving member. Please try again.'
                        : (sessionExpired ? 'Your session expired. Please refresh and login again.' : 'Unable to save member. Please check your inputs.');
                    showFlash(result?.message || fallbackMessage, 'error');
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);
            if (error.name === 'AbortError') {
                showFlash('Request timed out. Please try again.', 'error');
            } else {
                showFlash('Network error. Please try again.', 'error');
            }
        } finally {
            clearTimeout(timeoutId);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    }

    function displayFormErrors(form, errors) {
        form.querySelectorAll('.tm-input-wrap').forEach(el => el.classList.remove('has-success'));
        Object.keys(errors).forEach(field => {
            const errEl = form.querySelector('.tm-field-err[data-field="' + field + '"]');
            if (errEl) {
                errEl.textContent = errors[field][0];
                errEl.classList.remove('success');
                errEl.classList.add('show');
            }
            const wrap = form.querySelector('[name="' + field + '"]')?.closest('.tm-input-wrap');
            if (wrap) wrap.classList.add('has-error');
        });
        const firstErr = form.querySelector('.tm-field-err.show');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Add member form AJAX submit
    document.getElementById('addMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (!validateFormFields(this, true)) return;
        submitForm(this, '/admin/api/team', 'POST', true);
    });

    // Edit member form AJAX submit
    document.getElementById('editMemberForm').addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (!validateFormFields(this, false)) return;
        const memberId = this.dataset.memberId;
        submitForm(this, '/admin/api/team/' + memberId, 'PUT', false);
    });

    // ══════════ Delete Member AJAX ══════════
    window.deleteMember = async function(id, name) {
        if (!confirm('Remove ' + name + ' from the team? This cannot be undone.')) return;

        try {
            const response = await fetch('/admin/api/team/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                showFlash(result.message, 'success');
                const card = document.querySelector('.tm-card[data-member-id="' + id + '"]');
                if (card) {
                    card.style.transform = 'scale(0.9)';
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 300);
                }
                if (result.stats) updateStats(result.stats);
            } else {
                showFlash(result.message || 'Failed to remove member', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            showFlash('Network error. Please try again.', 'error');
        }
    };

    // ══════════ Update UI Functions ══════════
    function addMemberCard(member) {
        const grid = document.getElementById('tmMembersGrid');
        const emptyState = grid.querySelector('.tm-empty');
        if (emptyState) emptyState.remove();

        const rc = ROLE_COLORS[member.role] || '#6B7280';
        const eff = member.assigned_tasks_count > 0 ? Math.round((member.completed_tasks_count / member.assigned_tasks_count) * 100) : 0;
        const initials = member.name.split(' ').map(w => w.charAt(0).toUpperCase()).join('').slice(0,2);

        const card = document.createElement('div');
        card.className = 'tm-card';
        card.dataset.memberId = member.id;
        card.dataset.name = member.name;
        card.dataset.active = member.active_tasks_count;
        card.dataset.completion = eff;
        card.dataset.created = new Date(member.created_at).getTime() / 1000;
        card.style.animation = 'tmFlashIn 0.3s ease';

        card.innerHTML = `
            <div class="tm-card-header" onclick="openMemberDrawer(${member.id})" style="cursor:pointer">
                <div class="tm-avatar" style="background:${member.avatar_color || '#4F6DF0'}">${initials}</div>
                <div class="tm-info">
                    <div class="tm-name">${member.name}</div>
                    <div class="tm-email"><i class="fas fa-envelope" style="font-size:9px;margin-right:4px;opacity:0.5"></i>${member.email}</div>
                </div>
                <div class="tm-actions" onclick="event.stopPropagation()">
                    <button class="tm-action-btn tm-edit-btn" onclick='openEditModal(${JSON.stringify(member).replace(/'/g, "&#39;")})' title="Edit">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="tm-action-btn tm-del-btn" onclick="deleteMember(${member.id}, '${member.name.replace(/'/g, "\\'")}')" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="tm-card-body" style="flex-wrap:wrap;gap:6px">
                <span class="tm-role-badge" style="--role-color:${rc}">
                    <i class="${ROLE_ICONS[member.role] || 'fas fa-user'}" style="font-size:10px"></i>
                    ${formatRoleLabel(member.role)}
                </span>
                ${(member.additional_roles || []).map(ar => `<span class="tm-role-badge" style="--role-color:${ROLE_COLORS[ar] || '#6B7280'};opacity:0.85;font-size:10px;padding:2px 6px">+ ${formatRoleLabel(ar)}</span>`).join('')}
                <div class="tm-eff-bar-wrap" title="${eff}% completion rate" style="margin-left:auto">
                    <div class="tm-eff-bar" style="width:${eff}%;background:${eff >= 70 ? 'var(--teal)' : (eff >= 40 ? '#F59E0B' : 'var(--red)')}"></div>
                </div>
                <span class="tm-eff-text" style="color:${eff >= 70 ? 'var(--teal)' : (eff >= 40 ? '#F59E0B' : 'var(--red)')}">${eff}%</span>
            </div>
            <div class="tm-card-footer">
                <div class="tm-task-stat">
                    <span class="tm-ts-icon"><i class="fas fa-layer-group"></i></span>
                    <span class="tm-ts-num">${member.assigned_tasks_count}</span>
                    <span class="tm-ts-label">Total</span>
                </div>
                <div class="tm-task-stat">
                    <span class="tm-ts-icon" style="color:var(--primary)"><i class="fas fa-spinner"></i></span>
                    <span class="tm-ts-num" style="color:var(--primary)">${member.active_tasks_count}</span>
                    <span class="tm-ts-label">Active</span>
                </div>
                <div class="tm-task-stat">
                    <span class="tm-ts-icon" style="color:var(--teal)"><i class="fas fa-check-circle"></i></span>
                    <span class="tm-ts-num" style="color:var(--teal)">${member.completed_tasks_count}</span>
                    <span class="tm-ts-label">Done</span>
                </div>
            </div>
        `;

        grid.prepend(card);
        applySorting();
    }

    function updateMemberCard(member) {
        const card = document.querySelector('.tm-card[data-member-id="' + member.id + '"]');
        if (!card) return;

        const rc = ROLE_COLORS[member.role] || '#6B7280';
        const eff = member.assigned_tasks_count > 0 ? Math.round((member.completed_tasks_count / member.assigned_tasks_count) * 100) : 0;
        const initials = member.name.split(' ').map(w => w.charAt(0).toUpperCase()).join('').slice(0,2);

        card.dataset.name = member.name;
        card.dataset.active = member.active_tasks_count;
        card.dataset.completion = eff;

        card.querySelector('.tm-avatar').style.background = member.avatar_color || '#4F6DF0';
        card.querySelector('.tm-avatar').textContent = initials;
        card.querySelector('.tm-name').innerHTML = member.name;
        card.querySelector('.tm-email').innerHTML = `<i class="fas fa-envelope" style="font-size:9px;margin-right:4px;opacity:0.5"></i>${member.email}`;
        
        const bodyEl = card.querySelector('.tm-card-body');
        if (bodyEl) {
            bodyEl.querySelectorAll('.tm-role-badge').forEach(b => b.remove());
            const primaryBadge = document.createElement('span');
            primaryBadge.className = 'tm-role-badge';
            primaryBadge.style.setProperty('--role-color', rc);
            primaryBadge.innerHTML = `<i class="${ROLE_ICONS[member.role] || 'fas fa-user'}" style="font-size:10px"></i> ${formatRoleLabel(member.role)}`;
            bodyEl.prepend(primaryBadge);

            (member.additional_roles || []).forEach(ar => {
                const secBadge = document.createElement('span');
                secBadge.className = 'tm-role-badge';
                secBadge.style.setProperty('--role-color', ROLE_COLORS[ar] || '#6B7280');
                secBadge.style.opacity = '0.85';
                secBadge.style.fontSize = '10px';
                secBadge.style.padding = '2px 6px';
                secBadge.textContent = '+ ' + formatRoleLabel(ar);
                bodyEl.insertBefore(secBadge, bodyEl.querySelector('.tm-eff-bar-wrap'));
            });
        }

        card.querySelector('.tm-eff-bar').style.width = eff + '%';
        card.querySelector('.tm-eff-bar').style.background = eff >= 70 ? 'var(--teal)' : (eff >= 40 ? '#F59E0B' : 'var(--red)');
        card.querySelector('.tm-eff-text').textContent = eff + '%';
        card.querySelector('.tm-eff-text').style.color = eff >= 70 ? 'var(--teal)' : (eff >= 40 ? '#F59E0B' : 'var(--red)');

        // Update edit button onclick
        card.querySelector('.tm-edit-btn').setAttribute('onclick', `openEditModal(${JSON.stringify(member).replace(/'/g, "&#39;")})`);

        // Flash animation
        card.style.animation = 'none';
        card.offsetHeight; // Trigger reflow
        card.style.animation = 'tmFlashIn 0.3s ease';
    }

    function updateStats(stats) {
        const totalEl = document.querySelector('.tm-stat-total .tm-stat-num');
        if (totalEl) totalEl.textContent = stats.total;

        // Note: Role stats would require page reload or more complex DOM manipulation
    }

    // ══════════ Member Drawer ══════════
    window.openMemberDrawer = async function(memberId) {
        const drawer = document.getElementById('memberDrawer');
        const overlay = document.getElementById('memberDrawerOverlay');

        drawer.classList.add('loading');
        drawer.classList.add('show');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';

        try {
            const response = await fetch('/admin/api/team/' + memberId, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();

            if (result.success) {
                currentDrawerMember = result.member;
                populateDrawer(result);
            } else {
                showFlash('Failed to load member details', 'error');
                closeMemberDrawer();
            }
        } catch (error) {
            console.error('Drawer fetch error:', error);
            showFlash('Network error loading member details', 'error');
            closeMemberDrawer();
        } finally {
            drawer.classList.remove('loading');
        }
    };

    window.closeMemberDrawer = function() {
        document.getElementById('memberDrawer').classList.remove('show');
        document.getElementById('memberDrawerOverlay').classList.remove('show');
        document.body.style.overflow = '';
        currentDrawerMember = null;
    };

    window.editFromDrawer = function() {
        if (currentDrawerMember) {
            closeMemberDrawer();
            setTimeout(() => openEditModal(currentDrawerMember), 200);
        }
    };

    function populateDrawer(data) {
        const member = data.member;
        const initials = member.name.split(' ').map(w => w.charAt(0).toUpperCase()).join('').slice(0,2);

        document.getElementById('drawerAvatar').style.background = member.avatar_color || '#4F6DF0';
        document.getElementById('drawerInitials').textContent = initials;
        document.getElementById('drawerName').textContent = member.name;
        const allMemberRoles = [member.role, ...(member.additional_roles || [])];
        document.getElementById('drawerRole').innerHTML = allMemberRoles.map(r => `<span class="tm-role-badge" style="--role-color:${ROLE_COLORS[r] || '#6B7280'};font-size:11px;padding:3px 8px;display:inline-flex;align-items:center;gap:4px"><i class="${ROLE_ICONS[r] || 'fas fa-user'}"></i>${formatRoleLabel(r)}</span>`).join(' ');
        document.getElementById('drawerEmail').textContent = member.email;
        document.getElementById('drawerJoined').textContent = 'Joined ' + (member.created_at || 'Unknown');

        // Stats
        document.getElementById('drawerTotal').textContent = member.assigned_tasks_count;
        document.getElementById('drawerActive').textContent = member.active_tasks_count;
        document.getElementById('drawerCompleted').textContent = member.completed_tasks_count;

        // Completion rate ring
        const rate = member.assigned_tasks_count > 0 ? Math.round((member.completed_tasks_count / member.assigned_tasks_count) * 100) : 0;
        const ring = document.getElementById('drawerProgressRing');
        const circumference = 264;
        const offset = circumference - (rate / 100) * circumference;
        ring.style.strokeDashoffset = offset;
        ring.style.stroke = rate >= 70 ? 'var(--teal)' : (rate >= 40 ? '#F59E0B' : 'var(--red)');
        document.getElementById('drawerCompletionRate').textContent = rate + '%';

        // Avg completion time
        document.getElementById('drawerAvgTime').innerHTML = `<i class="fas fa-hourglass-half"></i> Avg: ${data.avgCompletionDays ? data.avgCompletionDays + ' days' : '-- days'}`;

        // Status breakdown
        const breakdownEl = document.getElementById('drawerBreakdown');
        const breakdown = data.statusBreakdown || {};
        if (Object.keys(breakdown).length === 0) {
            breakdownEl.innerHTML = '<div class="tm-drawer-breakdown-empty">No task data</div>';
        } else {
            breakdownEl.innerHTML = Object.entries(breakdown).map(([status, count]) => {
                const sc = STATUS_COLORS[status] || { bg: '#F3F4F6', text: '#6B7280', icon: 'fas fa-circle' };
                return `
                    <div class="tm-drawer-breakdown-item">
                        <div class="tm-drawer-breakdown-dot" style="background:${sc.text}"></div>
                        <div class="tm-drawer-breakdown-label">${status.replace(/_/g, ' ')}</div>
                        <div class="tm-drawer-breakdown-num">${count}</div>
                    </div>
                `;
            }).join('');
        }

        // Recent activity
        const activityEl = document.getElementById('drawerActivity');
        const tasks = data.recentTasks || [];
        if (tasks.length === 0) {
            activityEl.innerHTML = '<div class="tm-drawer-activity-empty"><i class="fas fa-inbox"></i><span>No recent tasks</span></div>';
        } else {
            activityEl.innerHTML = tasks.map(task => {
                const sc = STATUS_COLORS[task.status] || { bg: '#F3F4F6', text: '#6B7280', icon: 'fas fa-circle' };
                return `
                    <div class="tm-drawer-activity-item">
                        <div class="tm-drawer-activity-icon" style="background:${sc.bg};color:${sc.text}">
                            <i class="${sc.icon}"></i>
                        </div>
                        <div class="tm-drawer-activity-info">
                            <div class="tm-drawer-activity-title">${task.title}</div>
                            <div class="tm-drawer-activity-meta">
                                <span>${task.updated_at}</span>
                                ${task.deadline ? `<span>• Due ${task.deadline}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
    }

    // ══════════ Sorting ══════════
    let currentSort = 'name';

    function applySorting() {
        const grid = document.getElementById('tmMembersGrid');
        const cards = Array.from(grid.querySelectorAll('.tm-card'));

        cards.sort((a, b) => {
            switch (currentSort) {
                case 'name':
                    return (a.dataset.name || '').localeCompare(b.dataset.name || '');
                case 'active':
                    return parseInt(b.dataset.active || 0) - parseInt(a.dataset.active || 0);
                case 'newest':
                    return parseInt(b.dataset.created || 0) - parseInt(a.dataset.created || 0);
                case 'completion':
                    return parseInt(b.dataset.completion || 0) - parseInt(a.dataset.completion || 0);
                default:
                    return 0;
            }
        });

        cards.forEach(card => grid.appendChild(card));
    }

    document.querySelectorAll('.tm-sort-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tm-sort-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSort = this.dataset.sort;
            applySorting();
        });
    });

    // ══════════ Live Avatar Preview ══════════
    function setupLivePreview(prefix) {
        const nameInput = document.getElementById(prefix + 'Name');
        const avatarEl = document.getElementById(prefix + 'AvatarPreview');
        const initialsEl = document.getElementById(prefix + 'AvatarInitials');
        const previewNameEl = document.getElementById(prefix + 'PreviewName');
        const previewRoleEl = document.getElementById(prefix + 'PreviewRole');
        const roleGrid = document.getElementById(prefix + 'RoleGrid');
        const colorPalette = document.getElementById(prefix + 'ColorPalette');

        if (nameInput) {
            nameInput.addEventListener('input', function() {
                const val = this.value.trim();
                const parts = val.split(/\s+/).filter(Boolean);
                const initials = parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : (parts[0] ? parts[0][0].toUpperCase() : '?');
                initialsEl.textContent = initials;
                previewNameEl.textContent = val || (prefix === 'add' ? 'New Member' : 'Member');
            });
        }

        if (roleGrid) {
            roleGrid.addEventListener('change', function(e) {
                if (e.target.name === 'role') {
                    previewRoleEl.textContent = formatRoleLabel(e.target.value);
                }
            });
        }

        if (colorPalette) {
            colorPalette.addEventListener('change', function(e) {
                if (e.target.name === 'avatar_color') {
                    avatarEl.style.background = e.target.value;
                }
            });
        }
    }
    setupLivePreview('add');
    setupLivePreview('edit');

    ['add', 'edit'].forEach(prefix => {
        const roleInput = document.getElementById(prefix + 'RoleInput');
        roleInput?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addCustomRole(prefix);
            }
        });
    });

    // ══════════ Password Strength ══════════
    window.togglePassword = function(btn) {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    };

    function setupPwStrength(inputId, barId, textId) {
        const input = document.getElementById(inputId);
        const bar = document.getElementById(barId);
        const text = document.getElementById(textId);
        if (!input || !bar || !text) return;
        input.addEventListener('input', function() {
            const pw = this.value;
            if (!pw) { bar.style.width = '0'; text.textContent = ''; return; }
            let score = 0;
            if (pw.length >= 6) score++;
            if (pw.length >= 10) score++;
            if (/[A-Z]/.test(pw)) score++;
            if (/[0-9]/.test(pw)) score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;
            const levels = [
                { w: '20%', c: '#EF4444', t: 'Weak' },
                { w: '40%', c: '#F97316', t: 'Fair' },
                { w: '60%', c: '#EAB308', t: 'Good' },
                { w: '80%', c: '#10B981', t: 'Strong' },
                { w: '100%', c: '#059669', t: 'Excellent' },
            ];
            const level = levels[Math.min(score, levels.length) - 1] || levels[0];
            bar.style.width = level.w;
            bar.style.background = level.c;
            text.textContent = level.t;
            text.style.color = level.c;
        });
    }
    setupPwStrength('addPassword', 'addPwBar', 'addPwText');
    setupPwStrength('editPassword', 'editPwBar', 'editPwText');

    // ══════════ Form Validation ══════════
    function setFieldState(form, field, state, message) {
        const errEl = form.querySelector('.tm-field-err[data-field="' + field + '"]');
        const input = form.querySelector('[name="' + field + '"]');
        const wrap = input ? input.closest('.tm-input-wrap') : null;

        if (errEl) {
            errEl.classList.remove('show', 'success');
            errEl.textContent = '';
        }
        if (wrap) {
            wrap.classList.remove('has-error', 'has-success');
        }

        if (!state) return;

        if (errEl) {
            errEl.textContent = state === 'success' ? ('✓ ' + message) : message;
            errEl.classList.add('show');
            if (state === 'success') errEl.classList.add('success');
        }

        if (wrap) {
            wrap.classList.add(state === 'success' ? 'has-success' : 'has-error');
        }
    }

    function validateSingleField(form, field, isAdd, options = {}) {
        const live = options.live === true;
        const showSuccess = options.showSuccess !== false;

        const getVal = (name) => (form.querySelector('[name="' + name + '"]')?.value || '').trim();
        const clear = () => { setFieldState(form, field, null, ''); return true; };
        const fail = (msg) => { setFieldState(form, field, 'error', msg); return false; };
        const pass = (msg) => {
            if (showSuccess) setFieldState(form, field, 'success', msg);
            else clear();
            return true;
        };

        if (field === 'name') {
            const name = getVal('name');
            if (!name) return live ? clear() : fail('Name is required');
            if (name.length < 2) return fail('Name must be at least 2 characters');
            if (name.length > 255) return fail('Name must be 255 characters or fewer');
            return pass('Name looks good.');
        }

        if (field === 'email') {
            const email = getVal('email');
            if (!email) return live ? clear() : fail('Email is required');
            if (email.length > 255) return fail('Email must be 255 characters or fewer');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return fail('Enter a valid email address');
            return pass('Email format looks valid.');
        }

        if (field === 'password') {
            const pwInput = form.querySelector('[name="password"]');
            const pw = pwInput ? pwInput.value : '';
            if (!pw) {
                if (isAdd) return live ? clear() : fail('Password is required');
                return clear();
            }
            if (pw.length < 6) return fail('Minimum 6 characters required');
            if (pw.length > 255) return fail('Password must be 255 characters or fewer');
            return pass('Password length is valid.');
        }

        if (field === 'role') {
            const roleChecked = form.querySelector('[name="role"]:checked');
            if (!roleChecked) return live ? clear() : fail('Please select a role');
            if ((roleChecked.value || '').trim().length > 50) return fail('Role is too long');
            return pass('Role selected.');
        }

        return true;
    }

    function validateFormFields(form, isAdd) {
        let valid = true;
        ['name', 'email', 'password', 'role'].forEach(field => {
            if (!validateSingleField(form, field, isAdd, { live: false, showSuccess: true })) {
                valid = false;
            }
        });

        if (!valid) {
            const firstErr = form.querySelector('.tm-field-err.show:not(.success)');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return valid;
    }

    function setupAddFormLiveValidation() {
        const addForm = document.getElementById('addMemberForm');
        if (!addForm) return;

        const name = addForm.querySelector('[name="name"]');
        const email = addForm.querySelector('[name="email"]');
        const password = addForm.querySelector('[name="password"]');
        const roleGrid = addForm.querySelector('#addRoleGrid');

        name?.addEventListener('input', () => validateSingleField(addForm, 'name', true, { live: true, showSuccess: false }));
        name?.addEventListener('blur', () => validateSingleField(addForm, 'name', true, { live: false, showSuccess: true }));

        email?.addEventListener('input', () => validateSingleField(addForm, 'email', true, { live: true, showSuccess: false }));
        email?.addEventListener('blur', () => validateSingleField(addForm, 'email', true, { live: false, showSuccess: true }));

        password?.addEventListener('input', () => validateSingleField(addForm, 'password', true, { live: true, showSuccess: false }));
        password?.addEventListener('blur', () => validateSingleField(addForm, 'password', true, { live: false, showSuccess: true }));

        roleGrid?.addEventListener('change', () => validateSingleField(addForm, 'role', true, { live: false, showSuccess: true }));
    }
    setupAddFormLiveValidation();

    // ══════════ Auto-dismiss flash ══════════
    const flash = document.getElementById('tmFlash');
    if (flash) setTimeout(() => { flash.style.opacity = '0'; flash.style.transform = 'translateY(-8px)'; setTimeout(() => flash.remove(), 300); }, 5000);

    // ══════════ Search auto-submit on Enter ══════════
    const searchInput = document.querySelector('.tm-search-wrap input');
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }

    // ══════════ Keyboard shortcuts ══════════
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMemberDrawer();
            closeModal('addMemberModal');
            closeModal('editMemberModal');
        }
    });
})();

(function() {
    let teamAjaxController = null;
    let teamSearchTimeout = null;

    document.addEventListener('DOMContentLoaded', () => {
        initTeamAjax();
    });

    function initTeamAjax() {
        const form = document.querySelector('.team-filter-form');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            applyTeamFilters();
        });

        const searchInput = form.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(teamSearchTimeout);
                teamSearchTimeout = setTimeout(() => applyTeamFilters(), 250);
            });
        }

        initTeamLinkInterception();
    }

    function initTeamLinkInterception() {
        const filtersCard = document.querySelector('.team-filters-card');
        if (filtersCard) {
            filtersCard.addEventListener('click', (e) => {
                const link = e.target.closest('.tm-filter-pills a, .tm-active-filter a');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#')) return;

                e.preventDefault();
                loadTeamFromUrl(href);
            });
        }
    }

    window.applyTeamFilters = function() {
        const form = document.querySelector('.team-filter-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        // Preserve role if present in current URL but not in form
        const currentUrl = new URL(window.location.href);
        const role = currentUrl.searchParams.get('role');
        if (role && !params.has('role')) {
            params.set('role', role);
        }

        const url = form.action + (params.toString() ? '?' + params.toString() : '');
        loadTeamFromUrl(url);
    };

    async function loadTeamFromUrl(url) {
        if (teamAjaxController) teamAjaxController.abort();
        teamAjaxController = new AbortController();

        const area = document.getElementById('team-ajax-area');
        if (!area) return;

        // Visual feedback
        area.style.opacity = '0.6';
        area.style.pointerEvents = 'none';

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: teamAjaxController.signal
            });

            if (!response.ok) throw new Error('Network error');
            const html = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newArea = doc.getElementById('team-ajax-area');
            const newHeader = doc.querySelector('.team-header');
            const newFilters = doc.querySelector('.team-filters-card');

            if (newArea) area.innerHTML = newArea.innerHTML;
            if (newHeader) {
                const oldSub = document.querySelector('.team-header .page-subtitle');
                const newSub = newHeader.querySelector('.page-subtitle');
                if (oldSub && newSub) oldSub.innerHTML = newSub.innerHTML;
            }
            if (newFilters) {
                const oldFiltersCard = document.querySelector('.team-filters-card');
                if (oldFiltersCard) {
                    const oldPills = oldFiltersCard.querySelector('.tm-filter-pills');
                    const newPills = newFilters.querySelector('.tm-filter-pills');
                    if (oldPills && newPills) oldPills.innerHTML = newPills.innerHTML;
                    
                    // Update active filter badge if it exists
                    const oldActive = oldFiltersCard.querySelector('.tm-active-filter')?.parentElement;
                    const newActive = newFilters.querySelector('.tm-active-filter')?.parentElement;
                    if (oldActive && newActive) {
                        oldActive.innerHTML = newActive.innerHTML;
                    } else if (oldActive) {
                        oldActive.innerHTML = '';
                    } else if (newActive) {
                        // find insertion point
                        const formElem = oldFiltersCard.querySelector('.team-filter-form');
                        if (formElem) {
                             const div = document.createElement('div');
                             div.style.display = 'flex'; div.style.alignItems = 'center'; div.style.gap = '8px'; div.style.flexWrap = 'wrap';
                             div.innerHTML = newActive.innerHTML;
                             formElem.appendChild(div);
                        }
                    }
                }
            }

            window.history.pushState(null, '', url);
            
            // Re-sort if needed (if your sort logic is client-side, re-apply it here)
            if (typeof initSorting === 'function') initSorting();

        } catch (e) {
            if (e.name !== 'AbortError') {
                console.error(e);
                window.location.href = url;
            }
        } finally {
            area.style.opacity = '1';
            area.style.pointerEvents = 'auto';
        }
    }
})();
