@extends('layouts.app')

@section('content')
<div style="width: 100%; max-width: 1320px; margin: 0 auto; padding: 16px 20px 40px;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('admin.tasks') }}" title="Back to tasks" style="width: 40px; height: 40px; border-radius: 10px; border: 1px solid var(--border); background: var(--card); display: flex; align-items: center; justify-content: center; color: var(--text2); text-decoration: none; font-size: 15px; box-shadow: 0 2px 8px rgba(15,23,42,0.04);">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 700; color: var(--text);">Edit Task #{{ $task->id }}</h1>
                <p class="page-subtitle" style="margin: 3px 0 0; font-size: 13px; color: var(--text3);">Update task details, schedule, and team assignments.</p>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.tasks.show', $task) }}" class="btn-sec" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; font-size: 13px; font-weight: 600; text-decoration: none;">
                <i class="fas fa-eye"></i> View Task
            </a>
        </div>
    </div>

    <div style="width: 100%; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 32px; box-shadow: 0 2px 12px rgba(15,23,42,0.04);">
        @if(isset($errors) && $errors->any())
            <div style="background:var(--red-dim);color:var(--red);padding:14px 18px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:13px;font-weight:600;border:1px solid rgba(239,68,68,0.15)">
                <div style="margin-bottom:6px"><i class="fas fa-exclamation-triangle" style="color:var(--red)"></i> Please fix the following errors:</div>
                <ul style="margin:0;padding-left:18px;font-weight:500">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.tasks.update', $task) }}" method="POST">
            @csrf
            @method('PATCH')
            @php
                $selectedType = old('type', $task->type);
                $selectedPlatform = old('platform', is_array($task->platform) ? ($task->platform[0] ?? '') : $task->platform);
                $selectedStatus = old('status', $task->status);
                $selectedPriority = old('priority', $task->priority);
                $selectedAssignedTo = old('assigned_to', $task->assigned_to);
                $assignableMembers = collect($members ?? $designers ?? collect());
                $postDateValue = old('post_date', $task->post_date ? date('Y-m-d', strtotime((string) $task->post_date)) : '');
                $deadlineValue = old('deadline', $task->deadline ? date('Y-m-d', strtotime((string) $task->deadline)) : '');
            @endphp

            <!-- Title -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text);">
                    Task Title <span style="color: var(--red);">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $task->title) }}"
                    placeholder="e.g., Instagram Reels for Summer Campaign" required maxlength="255"
                    style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: 'Plus Jakarta Sans';">
                @error('title')
                    <p style="color: var(--red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                @enderror
                <span class="field-error" data-err="title" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
            </div>

            <!-- Client -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text);">
                    Client <span style="color: var(--red);">*</span>
                </label>
                <x-client-select :clients="$clients" name="client_id" id="adminClientIdInput" value="{{ old('client_id', $task->client_id) }}" placeholder="Select a client" />
                @error('client_id')
                    <p style="color: var(--red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                @enderror
                <span class="field-error" data-err="client_id" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
            </div>

            <!-- Type & Platform in 2 columns -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text);">
                        Content Type <span style="color: var(--red);">*</span>
                    </label>
                    <select name="type" id="adminEditTypeSelect" required
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: 'Plus Jakarta Sans';">
                        <option value="">Select type</option>
                        <optgroup label="Social Media">
                            <option value="reel" {{ $selectedType == 'reel' ? 'selected' : '' }}>Reel</option>
                            <option value="post" {{ $selectedType == 'post' ? 'selected' : '' }}>Post</option>
                            <option value="story" {{ $selectedType == 'story' ? 'selected' : '' }}>Story</option>
                            <option value="video" {{ $selectedType == 'video' ? 'selected' : '' }}>Video</option>
                            <option value="carousel" {{ $selectedType == 'carousel' ? 'selected' : '' }}>Carousel</option>
                        </optgroup>
                        <optgroup label="Print Media">
                            <option value="brochure" {{ $selectedType == 'brochure' ? 'selected' : '' }}>Brochure</option>
                            <option value="banner" {{ $selectedType == 'banner' ? 'selected' : '' }}>Banner</option>
                            <option value="flyer" {{ $selectedType == 'flyer' ? 'selected' : '' }}>Flyer</option>
                        </optgroup>
                        <optgroup label="Development">
                            <option value="website" {{ $selectedType == 'website' ? 'selected' : '' }}>Website</option>
                            <option value="software" {{ $selectedType == 'software' ? 'selected' : '' }}>Software</option>
                        </optgroup>
                        @if(!in_array($selectedType, ['reel', 'post', 'story', 'video', 'carousel', 'brochure', 'banner', 'flyer', 'website', 'software'], true) && !empty($selectedType))
                            <option value="{{ $selectedType }}" selected>{{ ucfirst(str_replace('_', ' ', $selectedType)) }} (Current)</option>
                        @else
                            <option value="others" {{ $selectedType == 'others' ? 'selected' : '' }}>Others</option>
                        @endif
                    </select>
                    <span class="field-error" data-err="type" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                </div>

                <div>
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text);">
                        Platform <span style="color: var(--red);">*</span>
                    </label>
                    <select name="platform" required
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: 'Plus Jakarta Sans';">
                        <option value="">Select platform</option>
                        <option value="instagram" {{ $selectedPlatform == 'instagram' ? 'selected' : '' }}>Instagram</option>
                        <option value="facebook" {{ $selectedPlatform == 'facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="linkedin" {{ $selectedPlatform == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                        <option value="twitter" {{ $selectedPlatform == 'twitter' ? 'selected' : '' }}>Twitter</option>
                    </select>
                    <span class="field-error" data-err="platform" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                </div>
            </div>

            <!-- Status & Priority in 2 columns -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text);">
                        Status <span style="color: var(--red);">*</span>
                    </label>
                    <select name="status" required
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: 'Plus Jakarta Sans';">
                        <option value="">Select status</option>
                        <option value="todo" {{ $selectedStatus == 'todo' ? 'selected' : '' }}>To Do</option>
                        <option value="inprogress" {{ $selectedStatus == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                        <option value="review" {{ $selectedStatus == 'review' ? 'selected' : '' }}>Review</option>
                        <option value="pending_approval" {{ $selectedStatus == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="completed" {{ $selectedStatus == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="published" {{ $selectedStatus == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                    <span class="field-error" data-err="status" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                </div>

                <div>
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text);">
                        Priority <span id="editPriorityAutoTag" style="font-size:9px;font-weight:700;color:var(--teal);background:var(--teal-dim);padding:2px 8px;border-radius:4px;margin-left:6px;display:none">AUTO</span> <span style="color: var(--red);">*</span>
                    </label>
                    <select name="priority" id="editPrioritySelect" required
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: 'Plus Jakarta Sans';">
                        <option value="">Select priority</option>
                        <option value="normal" {{ $selectedPriority == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ $selectedPriority == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ $selectedPriority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    <span class="field-error" data-err="priority" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                    <div id="editPriorityInfo" style="font-size:10px;color:var(--text3);margin-top:4px"></div>
                </div>
            </div>

            <!-- Assigned To -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text);">
                    Assign To
                </label>
                <select name="assigned_to" id="adminEditAssignSelect"
                    style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: 'Plus Jakarta Sans';">
                    <option value="">Unassigned</option>
                    @forelse($assignableMembers as $member)
                        @php
                            $memberRoles = method_exists($member, 'getAllRoles') ? $member->getAllRoles() : (isset($member->role) ? [$member->role] : []);
                            $rolesAttr = implode(',', $memberRoles);
                            $rolesDisplay = implode(' / ', array_map(fn($r) => ucfirst(str_replace('_', ' ', $r)), $memberRoles));
                        @endphp
                        <option value="{{ $member->id }}" data-roles="{{ $rolesAttr }}" data-role="{{ $member->role ?? '' }}" {{ (string) $selectedAssignedTo === (string) $member->id ? 'selected' : '' }}>
                            {{ $member->name }}{{ !empty($rolesDisplay) ? ' · ' . $rolesDisplay : '' }}
                        </option>
                    @empty
                        <option value="" disabled>No members found</option>
                    @endforelse
                </select>
                <span class="field-error" data-err="assigned_to" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
            </div>

            <!-- Team Members Assignment -->
            <div style="margin-bottom: 20px; padding: 16px; background: var(--card2); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                <label style="display: block; font-weight: 500; margin-bottom: 12px; color: var(--text);">
                    <i class="fas fa-users" style="margin-right: 8px;"></i>Team Members Assigned to This Task
                </label>

                <div id="employeesList" style="margin-bottom: 15px;">
                    @if($task->employees->count() > 0)
                        @foreach($task->employees as $emp)
                            <div class="employee-badge" data-employee-id="{{ $emp->id }}"
                                style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background: white; border: 1px solid var(--border); border-radius: 20px; margin: 6px 6px 6px 0; font-size: 13px;">
                                <div style="width: 24px; height: 24px; border-radius: 50%; background: {{ $emp->avatar_color }}; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 600;">
                                    {{ $emp->initials }}
                                </div>
                                <span style="color: var(--text);">{{ $emp->name }}</span>
                                <span style="color: var(--text3); font-size: 11px;">({{ $emp->role }})</span>
                                <button type="button" onclick="removeEmployee(this, {{ $emp->id }})"
                                    style="background: none; border: none; color: var(--text3); cursor: pointer; font-size: 14px; padding: 0 4px;">×</button>
                            </div>
                        @endforeach
                    @else
                        <p style="color: var(--text3); font-size: 13px; margin: 0;">No team members assigned yet</p>
                    @endif
                </div>

                <div style="display: grid; grid-template-columns: 1fr auto; gap: 8px;">
                    <select id="employeeSelect" name="employee_id"
                        style="padding: 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; font-family: 'Plus Jakarta Sans';">
                        <option value="">+ Add team member...</option>
                        @if(isset($allEmployees))
                            @foreach($allEmployees as $emp)
                                @if(!$task->employees->contains('id', $emp->id))
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->role }})</option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                    <button type="button" onclick="addEmployee()"
                        style="padding: 10px 16px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 13px;">
                        Add
                    </button>
                </div>

                <input type="hidden" id="assignedEmployees" name="assigned_employees" value="{{ json_encode($task->employees->pluck('id')->toArray()) }}">
            </div>

            <!-- Dates in 2 columns: Post Date first, then Deadline -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text);">
                        Post Date
                    </label>
                    <input type="date" name="post_date" id="editPostDateInput" value="{{ $postDateValue }}"
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: 'Plus Jakarta Sans';">
                    @error('post_date')
                        <p style="color: var(--red); font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                    @enderror
                    <span class="field-error" data-err="post_date" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                    <div style="font-size:10px;color:var(--text3);margin-top:4px">ⓘ Changing post date auto-updates deadline & priority</div>
                </div>

                <div>
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text);">
                        Deadline <span id="editDeadlineAutoTag" style="font-size:9px;font-weight:700;color:var(--teal);background:var(--teal-dim);padding:2px 8px;border-radius:4px;margin-left:6px;display:none">AUTO</span>
                    </label>
                    <input type="date" name="deadline" id="editDeadlineInput" value="{{ $deadlineValue }}" readonly
                        style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: 'Plus Jakarta Sans'; background:var(--card2);color:var(--text2);cursor:not-allowed;opacity:0.75">
                    <span class="field-error" data-err="deadline" style="display:none;color:var(--red);font-size:11px;margin-top:4px;font-weight:600"></span>
                    <div id="editDeadlineInfo" style="font-size:10px;color:var(--text3);margin-top:4px">Auto-calculated from post date</div>
                </div>
            </div>

            <!-- Content Brief -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text);">
                    Content Brief / Description
                </label>
                <textarea name="brief" rows="5" placeholder="Add creative direction or brief for this task..."
                    style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 14px; font-family: 'Plus Jakarta Sans'; resize: vertical;">{{ old('brief', $task->brief) }}</textarea>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; justify-content: space-between;">
                <div style="display: flex; gap: 10px;">
                    <button type="submit"
                        style="padding: 12px 24px; background: var(--primary); color: white; border: none; border-radius: var(--radius-sm); cursor: pointer; font-weight: 500; font-size: 14px; transition: 0.2s;">
                        <i class="fas fa-save" style="margin-right: 8px;"></i>Save Changes
                    </button>
                    <a href="{{ route('admin.tasks') }}"
                        style="padding: 12px 24px; background: var(--card2); color: var(--text); border: none; border-radius: var(--radius-sm); cursor: pointer; font-weight: 500; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center;">
                        <i class="fas fa-times" style="margin-right: 8px;"></i>Cancel
                    </a>
                </div>

                <!-- Right side buttons -->
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('admin.tasks.audit-history', $task) }}"
                        style="padding: 12px 16px; background: var(--card2); color: var(--primary); border: 1px solid var(--primary); border-radius: var(--radius-sm); cursor: pointer; font-weight: 500; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center;">
                        <i class="fas fa-history" style="margin-right: 8px;"></i>View History
                    </a>

                    <!-- Delete Button -->
                    <button type="button" onclick="confirmDeleteTask()"
                        style="padding: 12px 24px; background: var(--red); color: white; border: none; border-radius: var(--radius-sm); cursor: pointer; font-weight: 500; font-size: 14px; transition: 0.2s;">
                        <i class="fas fa-trash" style="margin-right: 8px;"></i>Delete
                    </button>
                </div>
            </div>
        </form>

        <!-- Hidden Delete Form (separated from main form to avoid nested form issues) -->
        <form id="deleteTaskForm" action="{{ route('admin.tasks.destroy', $task) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
    function filterAdminEditAssigneeDropdown() {
        const typeSelect = document.getElementById('adminEditTypeSelect');
        const assignSelect = document.getElementById('adminEditAssignSelect');
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

    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('adminEditTypeSelect');
        if (typeSelect) {
            typeSelect.addEventListener('change', filterAdminEditAssigneeDropdown);
            filterAdminEditAssigneeDropdown();
        }
    });

    function confirmDeleteTask() {
        if (confirm('Are you sure you want to permanently delete this task?\n\nTask: #{{ $task->id }} - {{ addslashes($task->title ?? "Untitled") }}\n\nThis action cannot be undone.')) {
            document.getElementById('deleteTaskForm').submit();
        }
    }


    function addEmployee() {
        const select = document.getElementById('employeeSelect');
        const employeeId = select.value;

        if (!employeeId) return;

        const option = select.options[select.selectedIndex];
        const employeeName = option.text;
        const role = employeeName.match(/\(([^)]+)\)/)?.[1] || 'contributor';
        const initials = employeeName.split(' ').map(n => n.charAt(0)).join('').toUpperCase();
        const colors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#EC4899'];
        const color = colors[Math.floor(Math.random() * colors.length)];

        // Prevent duplicates
        if (document.querySelector(`[data-employee-id="${employeeId}"]`)) {
            alert('This employee is already assigned!');
            return;
        }

        const badge = document.createElement('div');
        badge.className = 'employee-badge';
        badge.setAttribute('data-employee-id', employeeId);
        badge.style.cssText = `display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background: white; border: 1px solid var(--border); border-radius: 20px; margin: 6px 6px 6px 0; font-size: 13px;`;
        badge.innerHTML = `
            <div style="width: 24px; height: 24px; border-radius: 50%; background: ${color}; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 600;">
                ${initials}
            </div>
            <span style="color: var(--text);">${employeeName.split(' (')[0]}</span>
            <span style="color: var(--text3); font-size: 11px;">(${role})</span>
            <button type="button" onclick="removeEmployee(this, ${employeeId})"
                style="background: none; border: none; color: var(--text3); cursor: pointer; font-size: 14px; padding: 0 4px;">×</button>
        `;

        document.getElementById('employeesList').appendChild(badge);
        updateAssignedEmployees();
        select.value = '';
    }

    function removeEmployee(btn, employeeId) {
        btn.closest('.employee-badge').remove();
        updateAssignedEmployees();

        // Re-add to select dropdown
        const select = document.getElementById('employeeSelect');
        const allOptions = Array.from(select.options).filter(opt => opt.value !== '');
        const hadOption = allOptions.some(opt => opt.value == employeeId);

        if (!hadOption && allOptions.length > 0) {
            const firstOption = allOptions[0];
            const option = document.createElement('option');
            option.value = employeeId;
            option.text = firstOption.text; // You might need to store full text somewhere
            select.appendChild(option);
        }
    }

    function updateAssignedEmployees() {
        const badges = document.querySelectorAll('.employee-badge');
        const employeeIds = Array.from(badges).map(b => b.getAttribute('data-employee-id'));
        document.getElementById('assignedEmployees').value = JSON.stringify(employeeIds);
    }

    // Handle form submission with validation aligned to UpdateTaskRequest
    const editForm = document.querySelector('form[action*="/admin/tasks/"][method="POST"]');
    editForm.setAttribute('novalidate', '');

    const allowed = {
        type: ['reel', 'post', 'story', 'video', 'carousel', 'brochure', 'banner', 'flyer', 'website', 'software'],
        platform: ['instagram', 'facebook', 'linkedin', 'twitter'],
        status: ['todo', 'inprogress', 'review', 'pending_approval', 'completed', 'published'],
        priority: ['normal', 'high', 'urgent'],
    };

    const fieldRefs = {
        title: editForm.querySelector('[name="title"]'),
        client_id: editForm.querySelector('[name="client_id"]'),
        type: editForm.querySelector('[name="type"]'),
        platform: editForm.querySelector('[name="platform"]'),
        status: editForm.querySelector('[name="status"]'),
        priority: editForm.querySelector('[name="priority"]'),
        assigned_to: editForm.querySelector('[name="assigned_to"]'),
        post_date: editForm.querySelector('[name="post_date"]'),
        deadline: editForm.querySelector('[name="deadline"]'),
    };

    function setFieldVisual(el, state) {
        if (!el) return;
        if (state === 'error') {
            el.style.borderColor = 'var(--red)';
            el.style.boxShadow = '0 0 0 2px rgba(239,68,68,.12)';
            return;
        }
        if (state === 'success') {
            el.style.borderColor = '#16a34a';
            el.style.boxShadow = '0 0 0 2px rgba(22,163,74,.12)';
            return;
        }
        el.style.borderColor = '';
        el.style.boxShadow = '';
    }

    function setFeedback(name, text, state) {
        const errEl = editForm.querySelector('[data-err="' + name + '"]');
        if (!errEl) return;
        errEl.textContent = state === 'success' ? ('✓ ' + text) : text;
        errEl.style.display = 'block';
        errEl.style.color = state === 'success' ? '#16a34a' : 'var(--red)';
        errEl.dataset.state = state;
    }

    function clearFeedback(name) {
        const errEl = editForm.querySelector('[data-err="' + name + '"]');
        if (!errEl) return;
        errEl.textContent = '';
        errEl.style.display = 'none';
        errEl.style.color = 'var(--red)';
        delete errEl.dataset.state;
    }

    function resetField(name) {
        setFieldVisual(fieldRefs[name], null);
        clearFeedback(name);
    }

    function validateTitle(showSuccess = true) {
        const value = (fieldRefs.title?.value || '').trim();
        if (!value) {
            setFieldVisual(fieldRefs.title, 'error');
            setFeedback('title', 'Task title is required.', 'error');
            return false;
        }
        if (value.length > 255) {
            setFieldVisual(fieldRefs.title, 'error');
            setFeedback('title', 'Title must be 255 characters or fewer.', 'error');
            return false;
        }

        setFieldVisual(fieldRefs.title, 'success');
        if (showSuccess) setFeedback('title', 'Title looks good.', 'success');
        else clearFeedback('title');
        return true;
    }

    function validateClient(showSuccess = true) {
        const value = (fieldRefs.client_id?.value || '').trim();
        const pickerBtn = document.getElementById('cs_btn_adminClientIdInput');
        if (!value) {
            if (pickerBtn) setFieldVisual(pickerBtn, 'error');
            setFeedback('client_id', 'Please select a client.', 'error');
            return false;
        }

        if (pickerBtn) setFieldVisual(pickerBtn, 'success');
        if (showSuccess) setFeedback('client_id', 'Client selected.', 'success');
        else clearFeedback('client_id');
        return true;
    }

    function validateEnumField(name, label, required, allowedValues, showSuccess = true) {
        const field = fieldRefs[name];
        const value = (field?.value || '').trim();

        if (required && !value) {
            setFieldVisual(field, 'error');
            setFeedback(name, label + ' is required.', 'error');
            return false;
        }

        if (value && allowedValues && !allowedValues.includes(value)) {
            setFieldVisual(field, 'error');
            setFeedback(name, 'Invalid ' + label.toLowerCase() + ' selected.', 'error');
            return false;
        }

        if (value) {
            setFieldVisual(field, 'success');
            if (showSuccess) setFeedback(name, label + ' is valid.', 'success');
            else clearFeedback(name);
            return true;
        }

        resetField(name);
        return true;
    }

    function validateDateField(name, label, required, showSuccess = true) {
        const field = fieldRefs[name];
        const value = (field?.value || '').trim();

        if (required && !value) {
            setFieldVisual(field, 'error');
            setFeedback(name, label + ' is required.', 'error');
            return false;
        }

        if (value && Number.isNaN(Date.parse(value + 'T00:00:00'))) {
            setFieldVisual(field, 'error');
            setFeedback(name, label + ' must be a valid date.', 'error');
            return false;
        }

        if (value) {
            setFieldVisual(field, 'success');
            if (showSuccess) setFeedback(name, label + ' is valid.', 'success');
            else clearFeedback(name);
            return true;
        }

        resetField(name);
        return true;
    }

    function validateAssignedTo(showSuccess = true) {
        const field = fieldRefs.assigned_to;
        const value = (field?.value || '').trim();
        const optionValues = Array.from(field?.options || []).map(opt => String(opt.value));

        if (!value) {
            resetField('assigned_to');
            return true;
        }

        if (!optionValues.includes(value)) {
            setFieldVisual(field, 'error');
            setFeedback('assigned_to', 'Selected assignee is invalid.', 'error');
            return false;
        }

        setFieldVisual(field, 'success');
        if (showSuccess) setFeedback('assigned_to', 'Assignee is valid.', 'success');
        else clearFeedback('assigned_to');
        return true;
    }

    function validateDateOrder(showSuccess = true) {
        const postDate = (fieldRefs.post_date?.value || '').trim();
        const deadline = (fieldRefs.deadline?.value || '').trim();
        if (!postDate || !deadline) {
            clearFeedback('deadline');
            return true;
        }

        const post = Date.parse(postDate + 'T00:00:00');
        const dead = Date.parse(deadline + 'T00:00:00');
        if (!Number.isNaN(post) && !Number.isNaN(dead) && dead > post) {
            setFieldVisual(fieldRefs.deadline, 'error');
            setFeedback('deadline', 'Deadline cannot be later than Post Date.', 'error');
            return false;
        }

        if (showSuccess) {
            setFieldVisual(fieldRefs.deadline, 'success');
            setFeedback('deadline', 'Date order is valid.', 'success');
        } else {
            clearFeedback('deadline');
        }
        return true;
    }

    function runAllValidation(showSuccess = true) {
        return [
            validateTitle(showSuccess),
            validateClient(showSuccess),
            validateEnumField('type', 'Content type', true, allowed.type, showSuccess),
            validateEnumField('platform', 'Platform', true, allowed.platform, showSuccess),
            validateEnumField('status', 'Status', true, allowed.status, showSuccess),
            validateEnumField('priority', 'Priority', true, allowed.priority, showSuccess),
            validateAssignedTo(showSuccess),
            validateDateField('post_date', 'Post Date', false, showSuccess),
            validateDateField('deadline', 'Deadline', false, showSuccess),
            validateDateOrder(showSuccess),
        ].every(Boolean);
    }

    editForm.addEventListener('submit', function(e) {
        updateAssignedEmployees();

        editForm.querySelectorAll('.field-error[data-err]').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
            el.style.color = 'var(--red)';
            delete el.dataset.state;
        });

        Object.keys(fieldRefs).forEach(name => {
            if (fieldRefs[name]) setFieldVisual(fieldRefs[name], null);
        });
        const pickerBtn = document.getElementById('cs_btn_adminClientIdInput');
        if (pickerBtn) setFieldVisual(pickerBtn, null);

        if (!runAllValidation(true)) {
            e.preventDefault();
            const firstError = editForm.querySelector('.field-error[data-state="error"]');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            else window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    fieldRefs.title?.addEventListener('input', () => validateTitle(false));
    fieldRefs.title?.addEventListener('blur', () => validateTitle(true));

    fieldRefs.client_id?.addEventListener('change', () => validateClient(true));
    fieldRefs.type?.addEventListener('change', () => validateEnumField('type', 'Content type', true, allowed.type, true));
    fieldRefs.platform?.addEventListener('change', () => validateEnumField('platform', 'Platform', true, allowed.platform, true));
    fieldRefs.status?.addEventListener('change', () => validateEnumField('status', 'Status', true, allowed.status, true));
    fieldRefs.priority?.addEventListener('change', () => validateEnumField('priority', 'Priority', true, allowed.priority, true));
    fieldRefs.assigned_to?.addEventListener('change', () => validateAssignedTo(true));
    fieldRefs.post_date?.addEventListener('change', () => {
        validateDateField('post_date', 'Post Date', false, true);
        validateDateOrder(true);
    });

    // ===== Smart Deadline & Priority Auto-Calculation =====
    (function() {
        const postDateInput = document.getElementById('editPostDateInput');
        const deadlineInput = document.getElementById('editDeadlineInput');
        const deadlineAutoTag = document.getElementById('editDeadlineAutoTag');
        const deadlineInfo = document.getElementById('editDeadlineInfo');
        const prioritySelect = document.getElementById('editPrioritySelect');
        const priorityAutoTag = document.getElementById('editPriorityAutoTag');
        const priorityInfo = document.getElementById('editPriorityInfo');
        if (!postDateInput || !deadlineInput) return;

        function fmt(d) {
            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        }
        function daysDiff(a, b) {
            return Math.round((a - b) / 86400000);
        }

        function updateEditDatesAndPriority() {
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

            if (priority === 'urgent') {
                prioritySelect.style.borderColor = 'var(--red)'; prioritySelect.style.boxShadow = '0 0 0 2px rgba(239,68,68,.1)';
            } else if (priority === 'high') {
                prioritySelect.style.borderColor = '#F97316'; prioritySelect.style.boxShadow = '0 0 0 2px rgba(249,115,22,.1)';
            } else {
                prioritySelect.style.borderColor = ''; prioritySelect.style.boxShadow = '';
            }
        }

        postDateInput.addEventListener('change', updateEditDatesAndPriority);
    })();
</script>
@endsection
