@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/pages/designer-theme.css') }}?v=4.1" rel="stylesheet">
    <style>
        .st-container {
            max-width: 1400px;
            margin: 0 auto;
            padding-bottom: 50px;
        }
        .st-header {
            margin-bottom: 24px;
        }
        .st-tabs-wrap {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--dz-border);
            padding-bottom: 12px;
            overflow-x: auto;
        }
        .st-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 9px;
            border: 1px solid transparent;
            background: transparent;
            color: var(--dz-text-sub);
            cursor: pointer;
            transition: all 0.15s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
        }
        .st-tab-btn:hover {
            color: var(--dz-text-main);
            background: var(--dz-surface-sub);
        }
        .st-tab-btn.active {
            background: #eef2ff;
            color: #4f46e5;
            border-color: #c7d2fe;
            box-shadow: 0 1px 2px rgba(79, 70, 229, 0.08);
        }
        .st-card {
            background: #ffffff;
            border: 1px solid var(--dz-border);
            border-radius: 16px;
            padding: 28px 32px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
            margin-bottom: 24px;
        }
        .st-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--dz-border);
            flex-wrap: wrap;
            gap: 12px;
        }
        .st-card-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--dz-text-main);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .st-card-subtitle {
            font-size: 13px;
            color: var(--dz-text-muted);
            margin: 4px 0 0;
        }
        .st-add-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 24px;
        }
        .st-add-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .st-form-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .st-field {
            display: flex;
            flex-direction: column;
            min-width: 130px;
        }
        .st-label {
            font-size: 11.5px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .st-input {
            height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0 12px;
            font: inherit;
            font-size: 13px;
            color: #0f172a;
            background: #ffffff;
            box-sizing: border-box;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .st-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }
        .st-color-input {
            height: 38px;
            width: 60px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 2px;
            cursor: pointer;
            background: #ffffff;
        }
        .st-btn-add {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 38px;
            padding: 0 18px;
            background: #4f46e5;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }
        .st-btn-add:hover { background: #4338ca; }
        .st-btn-add:active { transform: scale(0.97); }

        .st-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 12px;
        }
        .st-item-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: #ffffff;
            border: 1px solid var(--dz-border);
            border-radius: 10px;
            transition: all 0.15s;
        }
        .st-item-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            transform: translateY(-1px);
        }
        .st-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }
        .st-item-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .st-item-label {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--dz-text-main);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .st-item-val {
            font-size: 11px;
            font-family: monospace;
            color: #64748b;
            background: #f1f5f9;
            padding: 1px 6px;
            border-radius: 4px;
            margin-left: 6px;
        }
        .st-item-group-tag {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 2px 8px;
            border-radius: 9999px;
            display: inline-block;
            margin-top: 2px;
        }
        .st-item-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        .st-action-btn {
            background: none;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s;
        }
        .st-action-btn:hover {
            color: #0f172a;
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        .st-action-btn.del:hover {
            color: #dc2626;
            background: #fef2f2;
            border-color: #fecaca;
        }
        .st-alert-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            z-index: 99999;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0;
            pointer-events: none;
            transform: translateY(10px);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .st-alert-toast.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }
    </style>
@endpush

@section('content')
<div class="st-container">
    {{-- Header --}}
    <div class="st-header">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="btn-sec" style="padding:6px 12px !important;font-size:12px !important;margin-bottom:12px;display:inline-flex">
                    <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                </a>
                <h1 class="designer-greeting" style="font-size:24px;margin:0">
                    <i class="fa-solid fa-gear" style="color:var(--dz-accent, #6366F1)"></i> System & Dropdown Settings
                </h1>
                <p style="font-size:13px;color:var(--dz-text-muted);margin:4px 0 0">
                    Admin control panel to manage dynamic dropdown options, categories, platforms, and choices.
                </p>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <a href="{{ route('admin.urgent-tasks') }}" class="btn-sec" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                    <i class="fa-solid fa-bolt" style="color:#ef4444;font-size:11px"></i> Urgent Tasks
                </a>
                <a href="{{ route('admin.tasks') }}" class="btn-action-urgent" style="text-decoration:none">
                    <i class="fa-solid fa-tasks" style="font-size:11px"></i> All Tasks
                </a>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="st-tabs-wrap">
        <button type="button" class="st-tab-btn active" data-tab="urgent_requested_by" onclick="switchSettingsTab('urgent_requested_by')">
            <i class="fa-solid fa-user-tag"></i> Requested By
            <span style="font-size:11px;background:#e2e8f0;color:#334155;padding:1px 6px;border-radius:9999px" id="tabCount-urgent_requested_by">{{ $requesters->count() }}</span>
        </button>
        <button type="button" class="st-tab-btn" data-tab="task_type" onclick="switchSettingsTab('task_type')">
            <i class="fa-solid fa-layer-group"></i> Task Types & Deliverables
            <span style="font-size:11px;background:#e2e8f0;color:#334155;padding:1px 6px;border-radius:9999px" id="tabCount-task_type">{{ $optionsByCategory->get('task_type', collect())->count() }}</span>
        </button>
        <button type="button" class="st-tab-btn" data-tab="platform" onclick="switchSettingsTab('platform')">
            <i class="fa-solid fa-share-nodes"></i> Target Platforms
            <span style="font-size:11px;background:#e2e8f0;color:#334155;padding:1px 6px;border-radius:9999px" id="tabCount-platform">{{ $platforms->count() }}</span>
        </button>
        <button type="button" class="st-tab-btn" data-tab="priority" onclick="switchSettingsTab('priority')">
            <i class="fa-solid fa-bolt"></i> Priorities
            <span style="font-size:11px;background:#e2e8f0;color:#334155;padding:1px 6px;border-radius:9999px" id="tabCount-priority">{{ $priorities->count() }}</span>
        </button>
        <button type="button" class="st-tab-btn" data-tab="status" onclick="switchSettingsTab('status')">
            <i class="fa-solid fa-list-check"></i> Statuses
            <span style="font-size:11px;background:#e2e8f0;color:#334155;padding:1px 6px;border-radius:9999px" id="tabCount-status">{{ $statuses->count() }}</span>
        </button>
    </div>

    {{-- Category Content Pane --}}
    <div class="st-card">
        <div class="st-card-head">
            <div>
                <h3 class="st-card-title" id="paneTitle">
                    <i class="fa-solid fa-user-tag" style="color:var(--dz-accent)"></i> Requested By Options
                </h3>
                <p class="st-card-subtitle" id="paneSubtitle">
                    Manage requester entities available on the Urgent Task form (e.g. Admin, Client, Strategist).
                </p>
            </div>
            <div style="font-size:12px;color:var(--dz-text-muted)">
                Changes save instantly to database
            </div>
        </div>

        {{-- Add New Option Bar --}}
        <div class="st-add-box">
            <div class="st-add-title">
                <i class="fa-solid fa-plus-circle" style="color:var(--dz-accent)"></i> Add New Choice
            </div>
            <form id="stAddForm" onsubmit="handleSettingsAdd(event)">
                <input type="hidden" id="stCategoryInput" name="category" value="urgent_requested_by">
                <div class="st-form-row">
                    <div class="st-field" style="flex:1.5">
                        <label class="st-label">Display Name *</label>
                        <input type="text" id="stLabelInput" name="label" required placeholder="e.g. Creative Lead" class="st-input" oninput="handleSettingsLabelInput(this.value)">
                    </div>

                    <div class="st-field" style="flex:1.2">
                        <label class="st-label">Code / Value *</label>
                        <input type="text" id="stValueInput" name="value" required placeholder="e.g. creative_lead" class="st-input">
                    </div>

                    <div class="st-field" id="stGroupWrap" style="flex:1.2;display:none">
                        <label class="st-label">Group Category</label>
                        <select id="stGroupInput" name="group_name" class="st-input">
                            <option value="Social Media">Social Media</option>
                            <option value="Design Work" selected>Design Work</option>
                            <option value="Web & Technical">Web & Technical</option>
                            <option value="General">General</option>
                        </select>
                    </div>

                    <div class="st-field" style="flex:1">
                        <label class="st-label">Icon / Emoji</label>
                        <input type="text" id="stIconInput" name="icon" placeholder="fa-solid fa-star or 🎨" class="st-input">
                    </div>

                    <div class="st-field" style="width:60px">
                        <label class="st-label">Color</label>
                        <input type="color" id="stColorInput" name="color" value="#6366F1" class="st-color-input" title="Choose accent color">
                    </div>

                    <div style="align-self:flex-end">
                        <button type="submit" id="stAddBtn" class="st-btn-add">
                            <i class="fa-solid fa-plus"></i> Add Option
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Active Options Grid --}}
        <div>
            <div style="font-size:12px;font-weight:700;color:var(--dz-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:12px">
                Active Options List
            </div>
            <div id="stItemsGrid" class="st-items-grid">
                {{-- Populated dynamically --}}
            </div>
        </div>
    </div>
</div>

{{-- Global Toast --}}
<div id="stToast" class="st-alert-toast">
    <i class="fa-solid fa-check-circle" style="color:#10B981"></i>
    <span id="stToastMsg">Settings updated successfully</span>
</div>

<script>
window._currentTab = 'urgent_requested_by';

const SETTINGS_TAB_CONFIG = {
    'urgent_requested_by': {
        title: 'Requested By Options',
        icon: 'fa-solid fa-user-tag',
        subtitle: 'Manage requester entities available on the Urgent Task form.',
        hasGroup: false,
        placeholder: 'e.g. Director, Partner Agency'
    },
    'task_type': {
        title: 'Task Types & Deliverables',
        icon: 'fa-solid fa-layer-group',
        subtitle: 'Manage creative, development, and social deliverable types across all task forms.',
        hasGroup: true,
        placeholder: 'e.g. 3D Model, Motion Graphics'
    },
    'platform': {
        title: 'Target Platforms',
        icon: 'fa-solid fa-share-nodes',
        subtitle: 'Manage social networks and delivery platforms selectable on tasks.',
        hasGroup: false,
        placeholder: 'e.g. Threads, Discord'
    },
    'priority': {
        title: 'Priority Levels',
        icon: 'fa-solid fa-bolt',
        subtitle: 'Manage priority rankings (Normal, High, Urgent, etc.).',
        hasGroup: false,
        placeholder: 'e.g. Critical, Low'
    },
    'status': {
        title: 'Workflow Statuses',
        icon: 'fa-solid fa-list-check',
        subtitle: 'Manage initial status workflow stages available when creating tasks.',
        hasGroup: false,
        placeholder: 'e.g. In Progress, Backlog'
    }
};

function switchSettingsTab(tabKey) {
    window._currentTab = tabKey;

    document.querySelectorAll('.st-tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabKey);
    });

    const cfg = SETTINGS_TAB_CONFIG[tabKey] || { title: tabKey, subtitle: '', icon: 'fa-solid fa-sliders', hasGroup: false };
    document.getElementById('paneTitle').innerHTML = `<i class="${cfg.icon}" style="color:var(--dz-accent)"></i> ${cfg.title}`;
    document.getElementById('paneSubtitle').textContent = cfg.subtitle;
    document.getElementById('stCategoryInput').value = tabKey;
    document.getElementById('stLabelInput').placeholder = cfg.placeholder;
    document.getElementById('stGroupWrap').style.display = cfg.hasGroup ? 'flex' : 'none';

    document.getElementById('stLabelInput').value = '';
    document.getElementById('stValueInput').value = '';
    document.getElementById('stIconInput').value = '';

    loadTabOptions(tabKey);
}

function handleSettingsLabelInput(val) {
    const tabKey = window._currentTab;
    const valueInput = document.getElementById('stValueInput');
    if (!valueInput) return;

    if (tabKey === 'urgent_requested_by') {
        valueInput.value = val.trim();
    } else {
        valueInput.value = val.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
    }
}

async function loadTabOptions(category) {
    const grid = document.getElementById('stItemsGrid');
    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:#94a3b8"><i class="fa-solid fa-spinner fa-spin"></i> Loading choices...</div>';

    try {
        const res = await fetch(`/api/dropdown-options?category=${encodeURIComponent(category)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Failed to load options');
        }

        const options = data.options || [];
        const countBadge = document.getElementById(`tabCount-${category}`);
        if (countBadge) countBadge.textContent = options.length;

        renderOptionsGrid(options, category);
    } catch (err) {
        grid.innerHTML = `<div style="grid-column:1/-1;color:#dc2626;padding:20px;text-align:center">${err.message}</div>`;
    }
}

function renderOptionsGrid(options, category) {
    const grid = document.getElementById('stItemsGrid');
    if (!options.length) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#94a3b8"><i class="fa-solid fa-folder-open" style="font-size:28px;margin-bottom:10px;display:block"></i>No options yet. Add your first option above.</div>';
        return;
    }

    let html = '';
    options.forEach(opt => {
        const iconHtml = opt.icon 
            ? (opt.icon.startsWith('fa-') ? `<i class="${opt.icon}"></i>` : `<span>${opt.icon}</span>`) 
            : `<i class="fa-solid fa-tag"></i>`;
        
        const bgColor = opt.color ? `${opt.color}15` : '#f1f5f9';
        const textColor = opt.color || '#475569';

        html += `
        <div class="st-item-card" id="st-item-${opt.id}">
            <div class="st-item-left">
                <div class="st-item-icon" style="background:${bgColor};color:${textColor}">
                    ${iconHtml}
                </div>
                <div>
                    <div style="display:flex;align-items:center">
                        <span class="st-item-label">${escapeHtml(opt.label)}</span>
                        <span class="st-item-val">${escapeHtml(opt.value)}</span>
                    </div>
                    ${opt.group_name ? `<span class="st-item-group-tag">${escapeHtml(opt.group_name)}</span>` : ''}
                </div>
            </div>
            <div class="st-item-actions">
                <button type="button" class="st-action-btn" onclick="editSettingsOption(${opt.id}, '${escapeHtml(opt.label)}')" title="Edit label">
                    <i class="fa-solid fa-pen"></i>
                </button>
                <button type="button" class="st-action-btn del" onclick="deleteSettingsOption(${opt.id}, '${escapeHtml(opt.label)}', '${category}')" title="Delete choice">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
        </div>`;
    });

    grid.innerHTML = html;
}

async function handleSettingsAdd(e) {
    e.preventDefault();
    const btn = document.getElementById('stAddBtn');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';

    const formData = new FormData(e.target);
    const cat = window._currentTab;

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

        showSettingsToast(data.message || 'Option created successfully');
        loadTabOptions(cat);

        document.getElementById('stLabelInput').value = '';
        document.getElementById('stValueInput').value = '';
        document.getElementById('stIconInput').value = '';

    } catch (err) {
        alert(err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

async function deleteSettingsOption(id, label, category) {
    if (!confirm(`Are you sure you want to remove "${label}"?`)) return;

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
            throw new Error(data.message || 'Failed to delete');
        }

        showSettingsToast(`"${label}" removed successfully`);
        loadTabOptions(category);
    } catch (err) {
        alert(err.message);
    }
}

async function editSettingsOption(id, currentLabel) {
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
            throw new Error(data.message || 'Failed to update');
        }

        showSettingsToast(`Updated to "${newLabel.trim()}"`);
        loadTabOptions(window._currentTab);
    } catch (err) {
        alert(err.message);
    }
}

function showSettingsToast(msg) {
    const toast = document.getElementById('stToast');
    const msgEl = document.getElementById('stToastMsg');
    if (!toast || !msgEl) return;
    msgEl.textContent = msg;
    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3500);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, function(m) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
    });
}

document.addEventListener('DOMContentLoaded', function() {
    switchSettingsTab('urgent_requested_by');
});
</script>
@endsection
