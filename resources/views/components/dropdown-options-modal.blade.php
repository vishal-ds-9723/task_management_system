{{-- Global Dynamic Dropdown Options Manager Modal (CRUD) --}}
<div id="dropdownManagerModal" class="ddm-backdrop" style="display:none;" aria-hidden="true">
    <div class="ddm-dialog">
        {{-- Header --}}
        <div class="ddm-header">
            <div class="ddm-title-wrap">
                <div class="ddm-title-icon"><i class="fa-solid fa-sliders"></i></div>
                <div>
                    <h3 class="ddm-title">Manage Dropdown Options</h3>
                    <p class="ddm-subtitle">Add, edit, or remove dropdown choices dynamically across task forms.</p>
                </div>
            </div>
            <button type="button" class="ddm-close-btn" onclick="closeDropdownManager()" title="Close">&times;</button>
        </div>

        {{-- Category Tabs --}}
        <div class="ddm-tabs">
            <button type="button" class="ddm-tab-btn active" data-ddm-cat="urgent_requested_by" onclick="switchDdmCategory('urgent_requested_by')">
                <i class="fa-solid fa-user-tag"></i> Requested By
            </button>
            <button type="button" class="ddm-tab-btn" data-ddm-cat="task_type" onclick="switchDdmCategory('task_type')">
                <i class="fa-solid fa-layer-group"></i> Task Types
            </button>
            <button type="button" class="ddm-tab-btn" data-ddm-cat="platform" onclick="switchDdmCategory('platform')">
                <i class="fa-solid fa-share-nodes"></i> Platforms
            </button>
            <button type="button" class="ddm-tab-btn" data-ddm-cat="priority" onclick="switchDdmCategory('priority')">
                <i class="fa-solid fa-bolt"></i> Priorities
            </button>
            <button type="button" class="ddm-tab-btn" data-ddm-cat="status" onclick="switchDdmCategory('status')">
                <i class="fa-solid fa-list-check"></i> Statuses
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="ddm-body">
            {{-- Alert / Toast --}}
            <div id="ddmAlert" class="ddm-alert" style="display:none"></div>

            {{-- Category Context Banner --}}
            <div class="ddm-cat-banner">
                <div>
                    <span class="ddm-cat-title" id="ddmCurrentCatTitle">Requested By Options</span>
                    <span class="ddm-cat-desc" id="ddmCurrentCatDesc">Manage requester entities available on the Urgent Task form.</span>
                </div>
                <div class="ddm-badge-count" id="ddmOptionCount">0 options</div>
            </div>

            {{-- Add New Option Form --}}
            <div class="ddm-add-box">
                <div class="ddm-add-box-title">
                    <i class="fa-solid fa-plus-circle" style="color:var(--dz-accent, #6366F1)"></i> Add New Option
                </div>
                <form id="ddmAddForm" onsubmit="handleDdmAddSubmit(event)">
                    <input type="hidden" id="ddmAddCategory" name="category" value="urgent_requested_by">
                    <div class="ddm-form-row">
                        <div class="ddm-field" style="flex:1.4">
                            <label class="ddm-label">Display Name *</label>
                            <input type="text" id="ddmAddLabel" name="label" required placeholder="e.g. Creative Lead, TikTok, 3D Render" class="ddm-input" oninput="handleDdmLabelInput(this.value)">
                        </div>

                        <div class="ddm-field" style="flex:1.2">
                            <label class="ddm-label">Key / Code *</label>
                            <input type="text" id="ddmAddValue" name="value" required placeholder="e.g. creative_lead" class="ddm-input">
                        </div>

                        <div class="ddm-field" id="ddmGroupField" style="flex:1.2;display:none">
                            <label class="ddm-label">Category Group</label>
                            <select id="ddmAddGroup" name="group_name" class="ddm-input">
                                <option value="Social Media">Social Media</option>
                                <option value="Design Work" selected>Design Work</option>
                                <option value="Web & Technical">Web & Technical</option>
                                <option value="General">General</option>
                            </select>
                        </div>

                        <div class="ddm-field" style="flex:1">
                            <label class="ddm-label">Icon / Emoji</label>
                            <input type="text" id="ddmAddIcon" name="icon" placeholder="fa-solid fa-star or 🎨" class="ddm-input">
                        </div>

                        <div class="ddm-field" style="width:70px">
                            <label class="ddm-label">Color</label>
                            <input type="color" id="ddmAddColor" name="color" value="#6366F1" class="ddm-input-color" title="Choose accent color">
                        </div>

                        <div class="ddm-field-btn" style="align-self:flex-end">
                            <button type="submit" id="ddmSubmitBtn" class="ddm-btn ddm-btn-primary">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Options List / Table --}}
            <div class="ddm-list-wrap">
                <div class="ddm-list-header">
                    <span>Active Options</span>
                    <span style="font-size:11px;color:#94a3b8">Changes save instantly & sync across all open forms</span>
                </div>
                <div id="ddmOptionsList" class="ddm-options-list">
                    {{-- Dynamically populated --}}
                    <div style="text-align:center;padding:30px;color:#94a3b8">
                        <i class="fa-solid fa-spinner fa-spin"></i> Loading options...
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="ddm-footer">
            <div style="font-size:12px;color:#64748b">
                <i class="fa-solid fa-circle-info" style="color:var(--dz-accent,#6366F1);margin-right:4px"></i>
                Removing an option removes it from future task dropdowns.
            </div>
            <button type="button" class="ddm-btn ddm-btn-secondary" onclick="closeDropdownManager()">Done</button>
        </div>
    </div>
</div>

<style>
.ddm-backdrop {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(5px);
    z-index: 100010;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.ddm-backdrop.is-open {
    opacity: 1;
    pointer-events: auto;
}
.ddm-dialog {
    background: #ffffff;
    border-radius: 18px;
    width: 100%;
    max-width: 820px;
    box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.35);
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transform: scale(0.97);
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    font-family: inherit;
    display: flex;
    flex-direction: column;
    max-height: 90vh;
}
.ddm-backdrop.is-open .ddm-dialog {
    transform: scale(1);
}
.ddm-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 26px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafafa;
}
.ddm-title-wrap {
    display: flex;
    align-items: center;
    gap: 14px;
}
.ddm-title-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #eef2ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.ddm-title {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.01em;
}
.ddm-subtitle {
    margin: 3px 0 0;
    font-size: 12.5px;
    color: #64748b;
}
.ddm-close-btn {
    background: none;
    border: none;
    font-size: 26px;
    line-height: 1;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 8px;
    transition: color 0.15s, background 0.15s;
}
.ddm-close-btn:hover {
    color: #0f172a;
    background: #f1f5f9;
}
.ddm-tabs {
    display: flex;
    gap: 6px;
    padding: 10px 24px;
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    overflow-x: auto;
}
.ddm-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    font-size: 12.5px;
    font-weight: 700;
    border: 1px solid transparent;
    border-radius: 8px;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.15s;
}
.ddm-tab-btn:hover {
    color: #0f172a;
    background: #f8fafc;
}
.ddm-tab-btn.active {
    background: #eef2ff;
    color: #4f46e5;
    border-color: #c7d2fe;
}
.ddm-body {
    padding: 22px 26px;
    overflow-y: auto;
    flex: 1;
}
.ddm-alert {
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 16px;
}
.ddm-alert.success {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}
.ddm-alert.error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.ddm-cat-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 20px;
}
.ddm-cat-title {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}
.ddm-cat-desc {
    display: block;
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}
.ddm-badge-count {
    font-size: 12px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 9999px;
    background: #e2e8f0;
    color: #334155;
}
.ddm-add-box {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    padding: 16px 18px;
    margin-bottom: 22px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
}
.ddm-add-box-title {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ddm-form-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: flex-end;
}
.ddm-field {
    display: flex;
    flex-direction: column;
    min-width: 120px;
}
.ddm-label {
    font-size: 11.5px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.ddm-input {
    height: 38px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 0 10px;
    font: inherit;
    font-size: 13px;
    color: #0f172a;
    background: #ffffff;
    box-sizing: border-box;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.ddm-input:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
}
.ddm-input-color {
    height: 38px;
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 2px;
    cursor: pointer;
    background: #ffffff;
}
.ddm-list-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}
.ddm-list-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
}
.ddm-options-list {
    max-height: 280px;
    overflow-y: auto;
}
.ddm-item-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.1s;
}
.ddm-item-row:last-child {
    border-bottom: none;
}
.ddm-item-row:hover {
    background: #f8fafc;
}
.ddm-item-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}
.ddm-item-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.ddm-item-label {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
}
.ddm-item-val {
    font-size: 11.5px;
    font-family: monospace;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    margin-left: 8px;
}
.ddm-item-group {
    font-size: 11px;
    font-weight: 600;
    color: #475569;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 2px 8px;
    border-radius: 9999px;
    margin-left: 8px;
}
.ddm-item-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}
.ddm-action-btn {
    background: none;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 12px;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s;
}
.ddm-action-btn:hover {
    color: #0f172a;
    background: #f1f5f9;
}
.ddm-action-btn.delete:hover {
    color: #dc2626;
    background: #fef2f2;
    border-color: #fecaca;
}
.ddm-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 38px;
    padding: 0 16px;
    font-size: 13px;
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    transition: background 0.15s, transform 0.1s;
}
.ddm-btn-primary {
    background: #4f46e5;
    color: #ffffff;
}
.ddm-btn-primary:hover { background: #4338ca; }
.ddm-btn-secondary {
    background: #f1f5f9;
    color: #475569;
}
.ddm-btn-secondary:hover { background: #e2e8f0; }
.ddm-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 26px;
    border-top: 1px solid #f1f5f9;
    background: #fafafa;
}

/* Button to open manager next to dropdown labels */
.btn-dd-manage {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #4f46e5;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-left: 8px;
    transition: all 0.15s ease;
    vertical-align: middle;
}
.btn-dd-manage:hover {
    background: #eef2ff;
    border-color: #c7d2fe;
    color: #4338ca;
    transform: translateY(-1px);
}
</style>

<script>
window._ddmCurrentCategory = 'urgent_requested_by';
window._ddmCachedOptions = {};

const DDM_CATEGORY_META = {
    'urgent_requested_by': {
        title: 'Requested By Options',
        desc: 'Manage requester choices shown on Urgent Task form.',
        hasGroup: false,
        placeholder: 'e.g. Director, Partner Agency'
    },
    'task_type': {
        title: 'Task Types & Categories',
        desc: 'Manage task deliverables across Urgent Task, Self-Task, and Main Task creation.',
        hasGroup: true,
        placeholder: 'e.g. 3D Model, Infographic, Motion Poster'
    },
    'platform': {
        title: 'Target Platforms',
        desc: 'Manage social & delivery platforms selectable on tasks.',
        hasGroup: false,
        placeholder: 'e.g. Threads, Discord, Snapchat'
    },
    'priority': {
        title: 'Priority Levels',
        desc: 'Manage task priority rankings (Normal, High, Urgent, etc.).',
        hasGroup: false,
        placeholder: 'e.g. Critical, Low'
    },
    'status': {
        title: 'Initial Statuses',
        desc: 'Manage status workflow stages available when creating a task.',
        hasGroup: false,
        placeholder: 'e.g. In Progress, Backlog'
    }
};

function openDropdownManager(category = 'urgent_requested_by') {
    const modal = document.getElementById('dropdownManagerModal');
    if (!modal) return;

    modal.style.display = 'flex';
    requestAnimationFrame(() => {
        modal.classList.add('is-open');
    });

    switchDdmCategory(category);
}

function closeDropdownManager() {
    const modal = document.getElementById('dropdownManagerModal');
    if (!modal) return;
    modal.classList.remove('is-open');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 200);
}

function switchDdmCategory(category) {
    window._ddmCurrentCategory = category;

    // Update tab styling
    document.querySelectorAll('.ddm-tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.ddmCat === category);
    });

    // Update banner & fields
    const meta = DDM_CATEGORY_META[category] || { title: category, desc: '', hasGroup: false };
    document.getElementById('ddmCurrentCatTitle').textContent = meta.title;
    document.getElementById('ddmCurrentCatDesc').textContent = meta.desc;
    document.getElementById('ddmAddCategory').value = category;
    document.getElementById('ddmAddLabel').placeholder = meta.placeholder || 'Option name';

    const groupField = document.getElementById('ddmGroupField');
    if (groupField) {
        groupField.style.display = meta.hasGroup ? 'flex' : 'none';
    }

    // Reset add form inputs
    document.getElementById('ddmAddLabel').value = '';
    document.getElementById('ddmAddValue').value = '';
    document.getElementById('ddmAddIcon').value = '';
    document.getElementById('ddmAddColor').value = '#6366F1';

    // Clear alert
    const alertBox = document.getElementById('ddmAlert');
    if (alertBox) alertBox.style.display = 'none';

    // Fetch and render
    loadDdmOptions(category);
}

function handleDdmLabelInput(label) {
    const cat = window._ddmCurrentCategory;
    const valueInput = document.getElementById('ddmAddValue');
    if (!valueInput) return;

    if (cat === 'urgent_requested_by') {
        valueInput.value = label.trim();
    } else {
        valueInput.value = label.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
    }
}

async function loadDdmOptions(category) {
    const listEl = document.getElementById('ddmOptionsList');
    const countEl = document.getElementById('ddmOptionCount');
    listEl.innerHTML = '<div style="text-align:center;padding:30px;color:#94a3b8"><i class="fa-solid fa-spinner fa-spin"></i> Loading options...</div>';

    try {
        const res = await fetch(`/api/dropdown-options?category=${encodeURIComponent(category)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Failed to fetch options');
        }

        const options = data.options || [];
        window._ddmCachedOptions[category] = options;
        countEl.textContent = `${options.length} options`;

        renderDdmOptionsList(options, category);
    } catch (err) {
        listEl.innerHTML = `<div style="text-align:center;padding:20px;color:#dc2626"><i class="fa-solid fa-triangle-exclamation"></i> ${err.message}</div>`;
    }
}

function renderDdmOptionsList(options, category) {
    const listEl = document.getElementById('ddmOptionsList');
    if (!options.length) {
        listEl.innerHTML = '<div style="text-align:center;padding:32px;color:#94a3b8"><i class="fa-solid fa-folder-open" style="font-size:24px;margin-bottom:8px;display:block"></i>No options yet. Add your first option above.</div>';
        return;
    }

    let html = '';
    options.forEach(opt => {
        const iconHtml = opt.icon 
            ? (opt.icon.startsWith('fa-') ? `<i class="${opt.icon}"></i>` : `<span>${opt.icon}</span>`) 
            : `<i class="fa-solid fa-tag"></i>`;
        
        const bgColor = opt.color ? `${opt.color}18` : '#f1f5f9';
        const textColor = opt.color || '#475569';

        html += `
        <div class="ddm-item-row" id="ddm-opt-${opt.id}">
            <div class="ddm-item-info">
                <div class="ddm-item-icon" style="background:${bgColor};color:${textColor}">
                    ${iconHtml}
                </div>
                <div>
                    <span class="ddm-item-label">${escapeHtml(opt.label)}</span>
                    <span class="ddm-item-val">${escapeHtml(opt.value)}</span>
                    ${opt.group_name ? `<span class="ddm-item-group">${escapeHtml(opt.group_name)}</span>` : ''}
                </div>
            </div>
            <div class="ddm-item-actions">
                <button type="button" class="ddm-action-btn" onclick="editDdmOption(${opt.id}, '${escapeHtml(opt.label)}', '${opt.color || ''}')" title="Edit Label">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button type="button" class="ddm-action-btn delete" onclick="deleteDdmOption(${opt.id}, '${escapeHtml(opt.label)}', '${category}')" title="Remove Option">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        </div>`;
    });

    listEl.innerHTML = html;
}

async function handleDdmAddSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('ddmSubmitBtn');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';

    const formData = new FormData(e.target);
    const cat = window._ddmCurrentCategory;

    try {
        const res = await fetch('/api/dropdown-options', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: formData
        });

        const data = await res.json();

        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Failed to add option');
        }

        showDdmAlert(data.message || 'Option added successfully!', 'success');
        
        // Refresh list
        loadDdmOptions(cat);

        // Reset add form
        document.getElementById('ddmAddLabel').value = '';
        document.getElementById('ddmAddValue').value = '';
        document.getElementById('ddmAddIcon').value = '';

        // Notify entire app that dropdown options have changed
        dispatchDropdownUpdateEvent(cat, data.option);

    } catch (err) {
        showDdmAlert(err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

async function deleteDdmOption(id, label, category) {
    if (!confirm(`Are you sure you want to remove "${label}" from ${category}?`)) {
        return;
    }

    try {
        const res = await fetch(`/api/dropdown-options/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Failed to delete option');
        }

        showDdmAlert(`"${label}" removed successfully.`, 'success');
        
        const row = document.getElementById(`ddm-opt-${id}`);
        if (row) row.remove();

        loadDdmOptions(category);

        // Notify entire app
        dispatchDropdownUpdateEvent(category, { id, deleted: true });

    } catch (err) {
        showDdmAlert(err.message, 'error');
    }
}

async function editDdmOption(id, currentLabel, currentColor) {
    const newLabel = prompt('Edit Display Name:', currentLabel);
    if (!newLabel || newLabel.trim() === '' || newLabel.trim() === currentLabel) return;

    try {
        const res = await fetch(`/api/dropdown-options/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({ label: newLabel.trim() })
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Failed to update option');
        }

        showDdmAlert(`Updated to "${newLabel.trim()}".`, 'success');
        loadDdmOptions(window._ddmCurrentCategory);

        dispatchDropdownUpdateEvent(window._ddmCurrentCategory, data.option);
    } catch (err) {
        showDdmAlert(err.message, 'error');
    }
}

function showDdmAlert(msg, type = 'success') {
    const el = document.getElementById('ddmAlert');
    if (!el) return;
    el.textContent = msg;
    el.className = `ddm-alert ${type}`;
    el.style.display = 'block';

    setTimeout(() => {
        if (el) el.style.display = 'none';
    }, 4000);
}

function dispatchDropdownUpdateEvent(category, optionData) {
    document.dispatchEvent(new CustomEvent('dropdown:updated', {
        detail: { category, option: optionData }
    }));
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, function(m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('dropdownManagerModal');
        if (modal && modal.classList.contains('is-open')) closeDropdownManager();
    }
});
</script>
