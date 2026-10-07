@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/pages/designer-theme.css') }}?v=3.0" rel="stylesheet">
@endpush

@section('content')
@php
    $urgentRequesters = \App\Models\DropdownOption::getOptions('urgent_requested_by');
    $urgentTypesGrouped = \App\Models\DropdownOption::getGrouped('task_type');
    $urgentPlatforms = \App\Models\DropdownOption::getOptions('platform');
@endphp

<div style="max-width:1380px;margin:0 auto;padding-bottom:40px">
    {{-- Back Link & Header --}}
    <div style="margin-bottom:24px">
        <a href="{{ route('designer.dashboard') }}" class="btn-sec" style="padding:6px 12px !important;font-size:12px !important;margin-bottom:14px;display:inline-flex">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <h1 class="designer-greeting" style="font-size:24px;margin:0">
                    <i class="fa-solid fa-bolt" style="color:var(--dz-red)"></i> Create Urgent Task
                </h1>
                <p style="font-size:13px;color:var(--dz-text-muted);margin:4px 0 0">
                    Fast-track emergency deliverables, client requests, and high-priority creatives.
                </p>
            </div>
            <div>
                <a href="{{ route('settings') }}" class="btn-sec" style="display:inline-flex;align-items:center;gap:6px;font-size:12px">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>
            </div>
        </div>
    </div>

    {{-- Main Form Card --}}
    <div class="card" style="padding:32px 36px">
        <form id="urgentTaskForm" method="POST" action="{{ route('designer.urgent-task.store') }}">
            @csrf

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(420px, 1fr));gap:32px">
                {{-- Left Column: Task Essentials --}}
                <div style="display:flex;flex-direction:column;gap:18px">
                    <div style="font-size:13px;font-weight:700;color:var(--dz-text-main);padding-bottom:8px;border-bottom:1px solid var(--dz-border);display:flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-circle-info" style="color:var(--dz-red)"></i> Task Essentials
                    </div>

                    {{-- Who requested --}}
                    <div>
                        <label style="display:block;font-size:12px;font-weight:700;color:var(--dz-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px">
                            Requested by *
                        </label>
                        <select name="urgent_requested_by" id="urgentRequestedBySelect" required class="filter-input" style="width:100%">
                            <option value="">Select requester...</option>
                            @foreach($urgentRequesters as $req)
                                <option value="{{ $req->value }}">{{ $req->label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Task Title --}}
                    <div>
                        <label style="display:block;font-size:12px;font-weight:700;color:var(--dz-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px">
                            Task Title *
                        </label>
                        <input type="text" name="title" required placeholder="E.g. Urgent Launch Creative & Social Poster" class="filter-input" style="width:100%">
                    </div>

                    {{-- Client --}}
                    <div>
                        <label style="display:block;font-size:12px;font-weight:700;color:var(--dz-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px">
                            Client *
                        </label>
                        <x-client-select :clients="$clients" name="client_id" placeholder="Select client..." />
                    </div>

                    {{-- Type & Deadline row --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:var(--dz-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px">
                                Type *
                            </label>
                            <select name="type" id="urgentTypeSelect" class="filter-input" style="width:100%">
                                @foreach($urgentTypesGrouped as $groupName => $types)
                                    <optgroup label="{{ $groupName }}">
                                        @foreach($types as $urgentType)
                                            <option value="{{ $urgentType->value }}" 
                                                    data-group="{{ $groupName }}"
                                                    data-is-social="{{ strtolower($groupName) === 'social media' ? '1' : '0' }}"
                                                    data-is-design="{{ in_array(strtolower($groupName), ['design work', 'creative & graphics design']) ? '1' : '0' }}">
                                                {{ $urgentType->label }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:var(--dz-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px">
                                Deadline
                            </label>
                            <input type="date" name="deadline" class="filter-input" style="width:100%">
                        </div>
                    </div>

                    <div id="urgentDesignDeadlineGroup" style="display:none">
                        <label style="display:block;font-size:12px;font-weight:700;color:var(--dz-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px">
                            Design Deadline
                        </label>
                        <input type="date" name="design_deadline" id="urgentDesignDeadlineInput" class="filter-input" style="width:100%">
                    </div>
                </div>

                {{-- Right Column: Specs & Brief --}}
                <div style="display:flex;flex-direction:column;gap:18px">
                    <div style="font-size:13px;font-weight:700;color:var(--dz-text-main);padding-bottom:8px;border-bottom:1px solid var(--dz-border);display:flex;align-items:center;gap:8px">
                        <i class="fa-solid fa-pen-ruler" style="color:var(--dz-red)"></i> Creative Specs & Brief
                    </div>

                    {{-- Platforms --}}
                    <div id="urgentPlatformsGroup">
                        <label style="display:block;font-size:12px;font-weight:700;color:var(--dz-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px">
                            Target Platforms
                        </label>
                        <div id="urgentPlatformsList" style="display:flex;gap:8px;flex-wrap:wrap">
                            @foreach($urgentPlatforms as $platform)
                            <label class="urgent-platform-pill" style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:var(--dz-surface-sub);border:1px solid var(--dz-border);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer">
                                <input type="checkbox" name="platform[]" value="{{ $platform->value }}" style="accent-color:var(--dz-accent,#6366F1)">
                                @if($platform->icon)
                                    @if(str_starts_with($platform->icon, 'fa-'))
                                        <i class="{{ $platform->icon }}" style="color:{{ $platform->color ?: 'var(--dz-text-sub)' }}"></i>
                                    @else
                                        <span>{{ $platform->icon }}</span>
                                    @endif
                                @else
                                    <i class="fa-solid fa-share-nodes" style="color:var(--dz-text-sub)"></i>
                                @endif
                                {{ $platform->label }}
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Brief --}}
                    <div style="flex:1;display:flex;flex-direction:column">
                        <label style="display:block;font-size:12px;font-weight:700;color:var(--dz-text-sub);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px">
                            Instructions / Creative Brief
                        </label>
                        <textarea name="brief" rows="4" placeholder="Key dimensions, required copy, brand references, moodboard notes..." class="filter-input" style="width:100%;flex:1;min-height:90px;resize:vertical"></textarea>
                    </div>

                    {{-- Auto-pause active task checkbox --}}
                    @if($currentActiveTask)
                    <label style="display:flex;align-items:flex-start;gap:10px;padding:12px 16px;background:#FFFBEB;border:1px solid #FDE68A;border-radius:10px;cursor:pointer;margin-top:auto">
                        <input type="checkbox" name="auto_pause_task_id" value="{{ $currentActiveTask->id }}" checked style="accent-color:var(--dz-red);margin-top:3px">
                        <div>
                            <div style="font-size:13px;font-weight:700;color:#92400E">
                                Auto-pause currently active task
                            </div>
                            <div style="font-size:12px;color:#B45309;margin-top:2px">
                                "<strong>{{ $currentActiveTask->title }}</strong>" will pause automatically until this urgent task is completed.
                            </div>
                        </div>
                    </label>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px;padding-top:20px;border-top:1px solid var(--dz-border)">
                <a href="{{ route('designer.dashboard') }}" class="btn-sec" style="padding:10px 24px;min-width:120px;justify-content:center">
                    Cancel
                </a>
                <button type="submit" id="urgentSubmitBtn" class="btn-primary" style="padding:10px 28px;min-width:220px;justify-content:center">
                    <i class="fa-solid fa-bolt"></i> Create & Start Working
                </button>
            </div>
        </form>
    </div>
</div>

<script>
window._previousSelectValues = {};

function handleDropdownSpecialSelect(selectEl, category) {
    if (selectEl.value === '__manage__') {
        const prev = window._previousSelectValues[selectEl.id] || '';
        selectEl.value = prev;
        openDropdownManager(category);
    } else {
        window._previousSelectValues[selectEl.id] = selectEl.value;
    }
}

function syncUrgentTaskFields() {
    const typeSelect = document.getElementById('urgentTypeSelect');
    const platformsGroup = document.getElementById('urgentPlatformsGroup');
    const designDeadlineGroup = document.getElementById('urgentDesignDeadlineGroup');
    const designDeadlineInput = document.getElementById('urgentDesignDeadlineInput');

    const selectedOpt = typeSelect?.options[typeSelect.selectedIndex];
    const isSocial = selectedOpt?.dataset.isSocial === '1';
    const isDesign = selectedOpt?.dataset.isDesign === '1';

    if (platformsGroup) {
        platformsGroup.style.display = isSocial ? '' : 'none';
        if (!isSocial) {
            platformsGroup.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                checkbox.checked = false;
            });
        }
    }

    if (designDeadlineGroup) {
        designDeadlineGroup.style.display = isDesign ? '' : 'none';
    }

    if (!isDesign && designDeadlineInput) {
        designDeadlineInput.value = '';
    }
}

// Live Sync when Dropdown Options change
document.addEventListener('dropdown:updated', async function(e) {
    const category = e.detail?.category;

    if (category === 'urgent_requested_by') {
        const select = document.getElementById('urgentRequestedBySelect');
        if (!select) return;
        const currentVal = select.value;
        try {
            const res = await fetch('/api/dropdown-options?category=urgent_requested_by');
            const data = await res.json();
            if (data.options) {
                let html = '<option value="">Select requester...</option>';
                data.options.forEach(opt => {
                    html += `<option value="${opt.value}" ${opt.value === currentVal ? 'selected' : ''}>${opt.label}</option>`;
                });
                html += '<option value="__manage__" style="color:var(--dz-accent,#6366f1);font-weight:700">⚙️ + Manage / Add New...</option>';
                select.innerHTML = html;
            }
        } catch (err) { console.error(err); }
    }

    if (category === 'task_type') {
        const select = document.getElementById('urgentTypeSelect');
        if (!select) return;
        const currentVal = select.value;
        try {
            const res = await fetch('/api/dropdown-options?category=task_type');
            const data = await res.json();
            if (data.options) {
                const grouped = {};
                data.options.forEach(opt => {
                    const grp = opt.group_name || 'General';
                    if (!grouped[grp]) grouped[grp] = [];
                    grouped[grp].push(opt);
                });

                let html = '';
                Object.entries(grouped).forEach(([grpName, items]) => {
                    html += `<optgroup label="${grpName}">`;
                    items.forEach(item => {
                        const isSoc = grpName.toLowerCase() === 'social media' ? '1' : '0';
                        const isDes = ['design work', 'creative & graphics design'].includes(grpName.toLowerCase()) ? '1' : '0';
                        html += `<option value="${item.value}" data-group="${grpName}" data-is-social="${isSoc}" data-is-design="${isDes}" ${item.value === currentVal ? 'selected' : ''}>${item.label}</option>`;
                    });
                    html += '</optgroup>';
                });
                html += '<option value="__manage__" style="color:var(--dz-accent,#6366f1);font-weight:700">⚙️ + Manage / Add New...</option>';
                select.innerHTML = html;
                syncUrgentTaskFields();
            }
        } catch (err) { console.error(err); }
    }

    if (category === 'platform') {
        const container = document.getElementById('urgentPlatformsList');
        if (!container) return;
        const selectedValues = Array.from(container.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);
        try {
            const res = await fetch('/api/dropdown-options?category=platform');
            const data = await res.json();
            if (data.options) {
                let html = '';
                data.options.forEach(p => {
                    const isChecked = selectedValues.includes(p.value);
                    const iconHtml = p.icon 
                        ? (p.icon.startsWith('fa-') ? `<i class="${p.icon}" style="color:${p.color || 'var(--dz-text-sub)'}"></i>` : `<span>${p.icon}</span>`) 
                        : `<i class="fa-solid fa-share-nodes" style="color:var(--dz-text-sub)"></i>`;

                    html += `
                    <label class="urgent-platform-pill" style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:var(--dz-surface-sub);border:1px solid var(--dz-border);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer">
                        <input type="checkbox" name="platform[]" value="${p.value}" ${isChecked ? 'checked' : ''} style="accent-color:var(--dz-accent,#6366F1)">
                        ${iconHtml}
                        ${p.label}
                    </label>`;
                });
                container.innerHTML = html;
            }
        } catch (err) { console.error(err); }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('urgentTaskForm');
    if (!form) return;

    document.getElementById('urgentTypeSelect')?.addEventListener('change', syncUrgentTaskFields);
    syncUrgentTaskFields();

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('urgentSubmitBtn');
        const origHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';
        btn.disabled = true;

        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData
            });
            const data = await response.json();

            if (response.ok && data.success) {
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    window.location.href = "{{ route('designer.dashboard') }}";
                }
            } else {
                const errors = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || 'Failed to create task');
                alert(errors);
                btn.innerHTML = origHTML;
                btn.disabled = false;
            }
        } catch (err) {
            alert('Network error. Please try again.');
            btn.innerHTML = origHTML;
            btn.disabled = false;
        }
    });
});
</script>
@endsection
