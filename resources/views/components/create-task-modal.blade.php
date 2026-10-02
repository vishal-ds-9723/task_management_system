{{-- resources/views/components/create-task-modal.blade.php --}}
@props(['clients' => collect(), 'designers' => collect(), 'members' => null, 'page' => false])

@php
    $assignableMembers = collect($members ?? $designers ?? collect());
@endphp

<div class="{{ $page ? 'ct-page-shell' : 'modal-overlay' }}" id="createModal">
    <div class="{{ $page ? 'ct-page-card' : 'modal' }}" style="width:580px">
        <div class="modal-head">
            <div class="modal-title"><i class="fas fa-plus-circle" style="margin-right:6px"></i>Create Task</div>
            @if(!$page)
                <div class="modal-close" onclick="closeModal('createModal')"><i class="fas fa-times"></i></div>
            @endif
        </div>
        <form method="POST" action="{{ auth()->user()?->role === 'admin' ? route('admin.tasks.store') : route('strategist.tasks.store') }}" id="createTaskModalForm" novalidate>
            @csrf
            @if($page)
                <input type="hidden" name="_from_create_page" value="1">
            @endif
            <div class="form-grid">
                <div class="form-group full">
                    <label>Task Title <span style="color:var(--red)">*</span></label>
                    <input type="text" name="title" placeholder="e.g., Instagram Reels for Summer Campaign" required maxlength="255" style="font-size:14px;padding:10px 14px">
                    <span class="field-error" id="err-title" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                </div>
                <div class="form-group">
                    <label>Client <span style="color:var(--red)">*</span></label>
                    <x-client-select :clients="$clients" name="client_id" id="modalClientIdInput" placeholder="Select client" />
                    <span class="field-error" id="err-client" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                </div>
                <div class="form-group">
                    <label>Content Type <span style="color:var(--red)">*</span></label>
                    <select name="type" id="modalTypeSelect" required>
                        <option value="">Select type</option>
                        <optgroup label="Social Media">
                            <option value="reel">Reel</option>
                            <option value="post">Post</option>
                            <option value="story">Story</option>
                            <option value="video">Video</option>
                            <option value="carousel">Carousel</option>
                        </optgroup>
                        <optgroup label="Print Media">
                            <option value="brochure">Brochure</option>
                            <option value="banner">Banner</option>
                            <option value="flyer">Flyer</option>
                        </optgroup>
                        <optgroup label="Development">
                            <option value="website">Website</option>
                            <option value="software">Software</option>
                        </optgroup>
                        <option value="others">Others</option>
                    </select>
                    <span class="field-error" id="err-type" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                </div>
                <div class="form-group" id="modalPlatformAccountGroup">
                    <label>Platform & Account <span style="color:var(--red)">*</span></label>
                    <select name="client_social_media_link_id" id="modalSocialMediaLinkSelect" required style="background:var(--card2)">
                        <option value="">Select platform & account</option>
                    </select>
                    <span class="field-error" id="err-social-media" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                    <div id="modalSocialMediaInfo" style="font-size:11px;color:var(--text3);margin-top:5px;display:flex;align-items:center;gap:4px">
                        <i class="fas fa-circle-info" style="opacity:0.5"></i>
                        <span>Select a client first to see available accounts</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Priority <span id="modalPriorityAutoTag" style="font-size:9px;font-weight:700;color:var(--teal);background:var(--teal-dim);padding:2px 8px;border-radius:4px;margin-left:6px;display:none">AUTO</span> <span style="color:var(--red)">*</span></label>
                    <select name="priority" id="modalPrioritySelect" required>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    <div id="modalPriorityInfo" style="font-size:10px;color:var(--text3);margin-top:4px"></div>
                </div>
                <div class="form-group">
                    <label>Assign To</label>
                    <select name="assigned_to" id="modalAssignSelect">
                        <option value="">Unassigned</option>
                        @forelse($assignableMembers as $member)
                            @php
                                $memberRoles = method_exists($member, 'getAllRoles') ? $member->getAllRoles() : (isset($member->role) ? [$member->role] : []);
                                $rolesAttr = implode(',', $memberRoles);
                                $rolesDisplay = implode(' / ', array_map(fn($r) => ucfirst(str_replace('_', ' ', $r)), $memberRoles));
                            @endphp
                            <option value="{{ $member->id }}" data-roles="{{ $rolesAttr }}" data-role="{{ $member->role ?? '' }}" {{ (string) old('assigned_to') === (string) $member->id ? 'selected' : '' }}>
                                {{ $member->name }}{{ !empty($rolesDisplay) ? ' · ' . $rolesDisplay : '' }}
                            </option>
                        @empty
                            <option value="" disabled>No members found</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group">
                    <label>Post Date <span style="color:var(--red)">*</span></label>
                    <input type="date" name="post_date" id="modalPostDateInput" required>
                    <span class="field-error" id="err-post_date" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                    <div style="font-size:10px;color:var(--text3);margin-top:4px">ⓘ Today or future dates only</div>
                </div>
                <div class="form-group">
                    <label>Deadline <span id="modalDeadlineAutoTag" style="font-size:9px;font-weight:700;color:var(--teal);background:var(--teal-dim);padding:2px 8px;border-radius:4px;margin-left:6px;display:none">AUTO</span></label>
                    <input type="date" name="deadline" id="modalDeadlineInput" readonly style="background:var(--card2);color:var(--text2);cursor:not-allowed;opacity:0.75">
                    <div id="modalDeadlineInfo" style="font-size:10px;color:var(--text3);margin-top:4px">Auto-calculated from post date</div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="todo">To Do</option>
                        <option value="inprogress">In Progress</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Task Brief</label>
                    <textarea name="brief" placeholder="Write the creative or project brief for the team…">{{ old('brief') }}</textarea>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;justify-content:flex-end">
                @if($page)
                    <a href="{{ route('admin.tasks') }}" class="btn-sec" style="text-decoration:none">Cancel</a>
                @else
                    <button type="button" class="btn-sec" onclick="closeModal('createModal')">Cancel</button>
                @endif
                <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Create Task</button>
            </div>
        </form>
    </div>
</div>

<style>
#modalClientSelectWrapper { position:relative; }
#modalCcsDropdown .ccs-option:hover { background:var(--card2); }
.ct-page-shell { width:100%; }
.ct-page-card { width:100% !important; background:var(--card); border:1px solid var(--border); border-radius:18px; box-shadow:0 12px 36px rgba(15,23,42,.08); padding:22px; }
.ct-page-card .modal-head { margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--border); }
@media (max-width:640px) {
    .ct-page-card { padding:16px; border-radius:14px; }
    .ct-page-card .form-grid { grid-template-columns:1fr; }
    .ct-page-card .form-group.full { grid-column:auto; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('modalClientIdInput');
    if (input) {
        input.addEventListener('change', function() {
            loadModalSocialMediaLinks(this.value);
        });
    }
});

function loadModalSocialMediaLinks(clientId) {
    if (!clientId) {
        const select = document.getElementById('modalSocialMediaLinkSelect');
        select.innerHTML = '<option value="">Select platform & account</option>';
        document.getElementById('modalSocialMediaInfo').textContent = 'Select a client first to see available accounts';
        return;
    }

    fetch(`/api/clients/${clientId}/social-links`)
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById('modalSocialMediaLinkSelect');
            const info = document.getElementById('modalSocialMediaInfo');

            if (!data.links || data.links.length === 0) {
                select.innerHTML = '<option value="">No social media accounts configured</option>';
                info.innerHTML = '<i class="fas fa-circle-info" style="margin-right:4px;opacity:0.6"></i>This client has no social media accounts. Add them in the client details.';
                return;
            }

            // Platform icons for visual recognition
            const platformIcons = {
                'instagram': '📸', 'facebook': '👥', 'twitter': '𝕏',
                'linkedin': '💼', 'youtube': '▶️', 'tiktok': '♪', 'whatsapp': '💬'
            };

            // Group by platform to show account counts
            const byPlatform = {};
            data.links.forEach(link => {
                if (!byPlatform[link.platform]) byPlatform[link.platform] = [];
                byPlatform[link.platform].push(link);
            });

            let html = '<option value="">Select platform & account</option>';
            let hasMultiple = false;

            Object.entries(byPlatform).forEach(([platform, links]) => {
                const icon = platformIcons[platform] || '🔗';
                if (links.length > 1) {
                    hasMultiple = true;
                    html += `<optgroup label="${icon} ${platform.toUpperCase()} (${links.length} accounts)" style="font-weight:700">`;
                    links.forEach(link => {
                        const label = link.label ? `${link.label}` : link.url.replace(/^https?:\/\/(www\.)?/, '');
                        const primary = link.is_primary ? ' ⭐ PRIMARY' : '';
                        html += `<option value="${link.id}">  ${label}${primary}</option>`;
                    });
                    html += '</optgroup>';
                } else {
                    links.forEach(link => {
                        const label = link.label ? ` (${link.label})` : '';
                        const primary = link.is_primary ? ' ⭐' : '';
                        html += `<option value="${link.id}">${icon} ${platform.charAt(0).toUpperCase() + platform.slice(1)}${label}${primary}</option>`;
                    });
                }
            });

            select.innerHTML = html;

            // Update info with better visual feedback
            if (hasMultiple) {
                info.innerHTML = `<i class="fas fa-network-wired" style="margin-right:4px;color:var(--primary)"></i><strong>Multi-account client:</strong> ${data.links.length} account(s) available`;
                select.style.borderColor = 'var(--primary)';
                select.style.backgroundColor = 'rgba(79,109,240,0.04)';
            } else {
                info.innerHTML = `<i class="fas fa-check-circle" style="margin-right:4px;color:var(--teal)"></i> ${data.links.length} account available`;
            }
        })
        .catch(err => {
            console.error('Failed to load social media links:', err);
            document.getElementById('modalSocialMediaInfo').innerHTML = '<i class="fas fa-exclamation-circle" style="margin-right:4px;color:var(--red)"></i>Error loading accounts. Try again.';
        });
}


// ===== Create Task Modal Validation =====
(function() {
    const form = document.getElementById('createTaskModalForm');
    if (!form) return;

    const fields = {
        title: form.querySelector('[name="title"]'),
        client: form.querySelector('[name="client_id"]'),
        type: form.querySelector('[name="type"]'),
        social: form.querySelector('[name="client_social_media_link_id"]'),
        postDate: form.querySelector('[name="post_date"]'),
    };
    const platformAccountGroup = document.getElementById('modalPlatformAccountGroup');
    const socialContentTypes = ['reel', 'post', 'story', 'video', 'carousel'];

    function requiresSocialAccount() {
        return socialContentTypes.includes(fields.type?.value || '');
    }

    function syncPlatformAccountVisibility() {
        const shouldShow = !fields.type?.value || requiresSocialAccount();

        if (platformAccountGroup) {
            platformAccountGroup.style.display = shouldShow ? '' : 'none';
        }

        if (fields.social) {
            fields.social.required = requiresSocialAccount();
            if (!shouldShow) {
                fields.social.value = '';
                clearFieldVisual(fields.social);
                clearFeedback('err-social-media');
            }
        }
    }

    function feedbackEl(id) {
        return document.getElementById(id);
    }

    function setFeedback(id, msg, state) {
        const el = feedbackEl(id);
        if (!el) return;
        el.textContent = state === 'success' ? ('✓ ' + msg) : msg;
        el.style.display = 'block';
        el.style.color = state === 'success' ? '#16a34a' : 'var(--red)';
        el.dataset.state = state;
    }

    function clearFeedback(id) {
        const el = feedbackEl(id);
        if (!el) return;
        el.textContent = '';
        el.style.display = 'none';
        el.style.color = 'var(--red)';
        delete el.dataset.state;
    }

    function setFieldVisual(field, state) {
        if (!field) return;
        field.style.borderColor = state === 'success' ? '#16a34a' : 'var(--red)';
        field.style.boxShadow = state === 'success'
            ? '0 0 0 2px rgba(22,163,74,0.12)'
            : '0 0 0 2px rgba(239,68,68,0.12)';
    }

    function clearFieldVisual(field) {
        if (!field) return;
        field.style.borderColor = '';
        field.style.boxShadow = '';
    }

    function clientPickerBtn() {
        return document.getElementById('cs_btn_modalClientIdInput');
    }

    function validateTitle(showSuccess = true) {
        const value = (fields.title?.value || '').trim();
        if (!value) {
            setFieldVisual(fields.title, 'error');
            setFeedback('err-title', 'Title is required.', 'error');
            return false;
        }
        if (value.length < 3) {
            setFieldVisual(fields.title, 'error');
            setFeedback('err-title', 'Title must be at least 3 characters.', 'error');
            return false;
        }
        if (value.length > 255) {
            setFieldVisual(fields.title, 'error');
            setFeedback('err-title', 'Title must be under 255 characters.', 'error');
            return false;
        }

        setFieldVisual(fields.title, 'success');
        if (showSuccess) setFeedback('err-title', 'Title looks good.', 'success');
        else clearFeedback('err-title');
        return true;
    }

    function validateClient(showSuccess = true) {
        const value = fields.client?.value || '';
        const pickerBtn = clientPickerBtn();
        if (!value) {
            if (pickerBtn) setFieldVisual(pickerBtn, 'error');
            setFeedback('err-client', 'Please select a client.', 'error');
            return false;
        }

        if (pickerBtn) setFieldVisual(pickerBtn, 'success');
        if (showSuccess) setFeedback('err-client', 'Client selected.', 'success');
        else clearFeedback('err-client');
        return true;
    }

    function validateType(showSuccess = true) {
        const value = fields.type?.value || '';
        if (!value) {
            setFieldVisual(fields.type, 'error');
            setFeedback('err-type', 'Please select a content type.', 'error');
            return false;
        }

        setFieldVisual(fields.type, 'success');
        if (showSuccess) setFeedback('err-type', 'Content type selected.', 'success');
        else clearFeedback('err-type');
        return true;
    }

    function validateSocial(showSuccess = true) {
        if (!requiresSocialAccount()) {
            clearFieldVisual(fields.social);
            clearFeedback('err-social-media');
            return true;
        }

        const value = fields.social?.value || '';
        if (!value) {
            setFieldVisual(fields.social, 'error');
            setFeedback('err-social-media', 'Please select a platform & account.', 'error');
            return false;
        }

        setFieldVisual(fields.social, 'success');
        if (showSuccess) setFeedback('err-social-media', 'Platform & account selected.', 'success');
        else clearFeedback('err-social-media');
        return true;
    }

    function validatePostDate(showSuccess = true) {
        const value = fields.postDate?.value || '';
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayISO = today.toISOString().split('T')[0];

        if (!value) {
            setFieldVisual(fields.postDate, 'error');
            setFeedback('err-post_date', 'Post date is required.', 'error');
            return false;
        }

        if (value < todayISO) {
            setFieldVisual(fields.postDate, 'error');
            setFeedback('err-post_date', 'Post date must be today or a future date.', 'error');
            return false;
        }

        setFieldVisual(fields.postDate, 'success');
        if (showSuccess) setFeedback('err-post_date', 'Post date is valid.', 'success');
        else clearFeedback('err-post_date');
        return true;
    }

    function resetValidationUI() {
        ['err-title', 'err-client', 'err-type', 'err-social-media', 'err-post_date'].forEach(clearFeedback);
        clearFieldVisual(fields.title);
        clearFieldVisual(fields.type);
        clearFieldVisual(fields.social);
        clearFieldVisual(fields.postDate);
        clearFieldVisual(clientPickerBtn());
    }

    function runAllValidations(showSuccess = true) {
        return [
            validateTitle(showSuccess),
            validateClient(showSuccess),
            validateType(showSuccess),
            validateSocial(showSuccess),
            validatePostDate(showSuccess),
        ].every(Boolean);
    }

    form.addEventListener('submit', function(e) {
        const isValid = runAllValidations(true);
        if (!isValid) {
            e.preventDefault();
            const firstError = form.querySelector('.field-error[data-state="error"]');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    fields.title?.addEventListener('input', () => validateTitle(false));
    fields.title?.addEventListener('blur', () => validateTitle(true));

    fields.client?.addEventListener('change', () => {
        validateClient(true);
        clearFeedback('err-social-media');
        clearFieldVisual(fields.social);
    });

    fields.type?.addEventListener('change', () => {
        validateType(true);
        syncPlatformAccountVisibility();
    });
    fields.social?.addEventListener('change', () => validateSocial(true));
    fields.postDate?.addEventListener('change', () => validatePostDate(true));

    const modal = document.getElementById('createModal');
    if (modal) {
        const observer = new MutationObserver(() => {
            if (modal.classList.contains('show')) {
                resetValidationUI();
                syncPlatformAccountVisibility();
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
    }

    syncPlatformAccountVisibility();
})();

// ===== Smart Deadline & Priority Auto-Calculation =====
(function() {
    const postDateInput = document.getElementById('modalPostDateInput');
    const deadlineInput = document.getElementById('modalDeadlineInput');
    const deadlineAutoTag = document.getElementById('modalDeadlineAutoTag');
    const deadlineInfo = document.getElementById('modalDeadlineInfo');
    const prioritySelect = document.getElementById('modalPrioritySelect');
    const priorityAutoTag = document.getElementById('modalPriorityAutoTag');
    const priorityInfo = document.getElementById('modalPriorityInfo');
    if (!postDateInput || !deadlineInput) return;

    // Set min date to today
    const todayISO = new Date().toISOString().split('T')[0];
    postDateInput.min = todayISO;

    function fmt(d) {
        return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
    }
    function daysDiff(a, b) {
        return Math.round((a - b) / 86400000);
    }

    function updateModalDatesAndPriority() {
        if (!postDateInput.value) {
            deadlineInput.value = '';
            deadlineAutoTag.style.display = 'none';
            deadlineInfo.textContent = 'Auto-calculated from post date';
            priorityAutoTag.style.display = 'none';
            priorityInfo.textContent = '';
            prioritySelect.style.borderColor = '';
            prioritySelect.style.boxShadow = '';
            return;
        }

        const postDate = new Date(postDateInput.value + 'T00:00:00');
        const today = new Date(); today.setHours(0,0,0,0);
        const tomorrow = new Date(today); tomorrow.setDate(tomorrow.getDate() + 1);
        const in3Days = new Date(today); in3Days.setDate(in3Days.getDate() + 3);

        let deadline, priority, message, tagColor, tagBg;

        if (postDate.getTime() === tomorrow.getTime()) {
            deadline = new Date(today);
            priority = 'urgent';
            message = '<i class="fas fa-circle" style="font-size:8px;color:var(--red)"></i> Post is TOMORROW — Deadline set to TODAY — AUTO URGENT';
            tagColor = 'var(--red)'; tagBg = 'var(--red-dim)';
        } else if (postDate.getTime() <= in3Days.getTime()) {
            deadline = new Date(postDate); deadline.setDate(deadline.getDate() - 1);
            priority = 'high';
            const days = daysDiff(postDate, today);
            message = '<i class="fas fa-circle" style="font-size:8px;color:#F97316"></i> Post in ' + days + ' days — HIGH PRIORITY';
            tagColor = 'var(--amber-800)'; tagBg = 'var(--amber-100)';
        } else {
            deadline = new Date(postDate); deadline.setDate(deadline.getDate() - 5);
            priority = 'normal';
            const days = daysDiff(postDate, today);
            message = '<i class="fas fa-circle" style="font-size:8px;color:#EAB308"></i> Post in ' + days + ' days — Deadline ' + (days - 5) + ' days — NORMAL';
            tagColor = 'var(--teal)'; tagBg = 'var(--teal-dim)';
        }

        if (deadline < today) {
            deadline = new Date(today); deadline.setDate(deadline.getDate() + 1);
        }

        deadlineInput.value = fmt(deadline);
        prioritySelect.value = priority;

        deadlineAutoTag.textContent = 'AUTO';
        deadlineAutoTag.style.display = 'inline-block';
        deadlineAutoTag.style.background = tagBg;
        deadlineAutoTag.style.color = tagColor;
        deadlineInfo.innerHTML = '<strong>' + message + '</strong>';

        priorityAutoTag.textContent = 'AUTO';
        priorityAutoTag.style.display = 'inline-block';
        priorityAutoTag.style.background = tagBg;
        priorityAutoTag.style.color = tagColor;
        priorityInfo.innerHTML = '<i class="fas fa-chart-bar" style="font-size:10px;opacity:0.5"></i> Auto-adjusted: <strong>' + priority.toUpperCase() + '</strong>';

        // Priority color
        if (priority === 'urgent') {
            prioritySelect.style.borderColor = 'var(--red)'; prioritySelect.style.boxShadow = '0 0 0 2px rgba(239,68,68,.1)';
        } else if (priority === 'high') {
            prioritySelect.style.borderColor = '#F97316'; prioritySelect.style.boxShadow = '0 0 0 2px rgba(249,115,22,.1)';
        } else {
            prioritySelect.style.borderColor = ''; prioritySelect.style.boxShadow = '';
        }
    }

    function filterModalAssigneeDropdown() {
        const typeSelect = document.getElementById('modalTypeSelect');
        const assignSelect = document.getElementById('modalAssignSelect');
        if (!typeSelect || !assignSelect) return;
        const typeVal = (typeSelect.value || '').trim().toLowerCase();
        
        let allowedRoles = null;
        const printMediaTypes = ['brochure', 'banner', 'flyer'];
        const devMediaTypes = ['website', 'software'];
        const socialMediaTypes = ['reel', 'post', 'story', 'carousel', 'video'];

        if (devMediaTypes.includes(typeVal)) {
            allowedRoles = ['developer'];
        } else if (printMediaTypes.includes(typeVal)) {
            allowedRoles = ['designer'];
        } else if (socialMediaTypes.includes(typeVal)) {
            allowedRoles = ['strategist', 'content_writer', 'editor', 'manager', 'designer'];
        }

        const options = assignSelect.querySelectorAll('option');
        let selectedOptionStillValid = false;
        const currentSelectedVal = assignSelect.value;

        options.forEach(opt => {
            if (!opt.value) {
                opt.hidden = false;
                opt.disabled = false;
                opt.style.display = '';
                return;
            }

            const rolesAttr = opt.getAttribute('data-roles') || opt.getAttribute('data-role') || '';
            const userRoles = rolesAttr.split(',').map(r => r.trim().toLowerCase()).filter(Boolean);

            let isMatch = true;
            if (allowedRoles) {
                isMatch = userRoles.some(r => allowedRoles.includes(r));
            }

            if (isMatch) {
                opt.hidden = false;
                opt.disabled = false;
                opt.style.display = '';
                if (opt.value === currentSelectedVal) {
                    selectedOptionStillValid = true;
                }
            } else {
                opt.hidden = true;
                opt.disabled = true;
                opt.style.display = 'none';
            }
        });

        if (currentSelectedVal && !selectedOptionStillValid) {
            assignSelect.value = '';
        }
    }

    const typeSelectModal = document.getElementById('modalTypeSelect');
    if (typeSelectModal) {
        typeSelectModal.addEventListener('change', filterModalAssigneeDropdown);
        filterModalAssigneeDropdown();
    }

    postDateInput.addEventListener('change', updateModalDatesAndPriority);
})();
</script>
