@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/pages/designer-theme.css') }}?v=2.0" rel="stylesheet">
@endpush

@section('content')
@php
    $urgentSocialTypes = \App\Models\Task::SOCIAL_TYPES;
    $urgentDesignTypes = \App\Models\Task::DESIGN_TYPES;
@endphp

<div style="max-width:1100px;margin:0 auto;padding-bottom:40px">
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
                        <select name="urgent_requested_by" required class="filter-input" style="width:100%">
                            <option value="">Select requester...</option>
                            <option value="Admin">Admin</option>
                            <option value="Client">Client</option>
                            <option value="Other">Other</option>
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
                                <optgroup label="Social Media">
                                    @foreach($urgentSocialTypes as $urgentType)
                                        <option value="{{ $urgentType }}">{{ ucfirst($urgentType) }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Design Work">
                                    @foreach($urgentDesignTypes as $urgentType)
                                        <option value="{{ $urgentType }}">{{ ucfirst($urgentType) }}</option>
                                    @endforeach
                                </optgroup>
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
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            @foreach(['instagram','facebook','linkedin','twitter','whatsapp'] as $plat)
                            <label style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:var(--dz-surface-sub);border:1px solid var(--dz-border);border-radius:8px;font-size:12px;font-weight:600;cursor:pointer">
                                <input type="checkbox" name="platform[]" value="{{ $plat }}" style="accent-color:var(--dz-red)">
                                <i class="fa-brands fa-{{ $plat === 'twitter' ? 'x-twitter' : $plat }}" style="color:var(--dz-text-sub)"></i>
                                {{ ucfirst($plat) }}
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
function syncUrgentTaskFields() {
    const socialTypes = @json(\App\Models\Task::SOCIAL_TYPES);
    const designTypes = @json(\App\Models\Task::DESIGN_TYPES);
    const typeSelect = document.getElementById('urgentTypeSelect');
    const platformsGroup = document.getElementById('urgentPlatformsGroup');
    const designDeadlineGroup = document.getElementById('urgentDesignDeadlineGroup');
    const designDeadlineInput = document.getElementById('urgentDesignDeadlineInput');
    const selectedType = typeSelect?.value || 'post';
    const isSocial = socialTypes.includes(selectedType);
    const isDesign = designTypes.includes(selectedType);

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
