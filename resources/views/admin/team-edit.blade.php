@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@section('content')
<div class="team-edit-page-container">

    {{-- ── Top Navigation & Page Header ── --}}
    <div class="te-page-header">
        <div class="te-header-left">
            <a href="{{ route('admin.team') }}" class="te-back-btn" title="Back to Team List">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <div class="te-breadcrumbs">
                    <a href="{{ route('admin.team') }}">Team Management</a>
                    <i class="fas fa-chevron-right te-bc-sep"></i>
                    <span>Edit Member</span>
                </div>
                <h1 class="te-page-title">
                    <span>Edit Team Member</span>
                    @if($user->id === auth()->id())
                        <span class="tm-you-badge">You</span>
                    @endif
                </h1>
                <p class="te-page-subtitle">Update account identity, assigned clients, operational roles, credentials, and permissions.</p>
            </div>
        </div>
        <div class="te-header-actions">
            <a href="{{ route('admin.team') }}" class="btn-sec te-btn-cancel">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" form="editTeamMemberForm" class="btn-primary te-btn-save">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    {{-- ── Alerts / Validation Errors ── --}}
    @if(session('success'))
        <div class="tm-flash tm-flash-success" id="tmFlash">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="tm-flash-close"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if(session('error'))
        <div class="tm-flash tm-flash-error" id="tmFlash">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="tm-flash-close"><i class="fas fa-times"></i></button>
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="tm-flash tm-flash-error" id="tmFlash">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <div style="font-weight:700;margin-bottom:2px">Please correct the errors below:</div>
                <ul style="margin:0;padding-left:16px;font-weight:500;font-size:12px">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button onclick="this.parentElement.remove()" class="tm-flash-close"><i class="fas fa-times"></i></button>
        </div>
    @endif

    @php
        $roleIcons = [
            'admin' => 'fas fa-shield-alt',
            'strategist' => 'fas fa-chess-queen',
            'designer' => 'fas fa-palette',
            'developer' => 'fas fa-code',
            'client' => 'fas fa-building',
            'manager' => 'fas fa-briefcase',
            'editor' => 'fas fa-pen-nib',
            'content_writer' => 'fas fa-feather-alt',
        ];
        $roleColors = [
            'admin' => '#EF4444',
            'strategist' => '#8B5CF6',
            'designer' => '#3B82F6',
            'developer' => '#F97316',
            'client' => '#0EA5E9',
            'manager' => '#6366F1',
            'editor' => '#14B8A6',
            'content_writer' => '#EC4899',
        ];
        $presetColors = ['#4F6DF0','#3B82F6','#8B5CF6','#EC4899','#EF4444','#F97316','#14B8A6','#10B981','#6366F1','#0EA5E9'];
        $selectedRole = old('role', $user->role);
        $userClientIds = $user->clients->pluck('id')->toArray();
        if (empty($userClientIds) && $user->client_id) {
            $userClientIds = [$user->client_id];
        }
        $selectedClientIds = (array) old('client_ids', $userClientIds);
        $selectedAdditionalRoles = (array) old('additional_roles', $user->additional_roles ?? []);
        $selectedAvatarColor = old('avatar_color', $user->avatar_color ?: '#4F6DF0');
        $completionRate = $user->assigned_tasks_count > 0 ? round(($user->completed_tasks_count / $user->assigned_tasks_count) * 100) : 0;
        $assignedClients = $clients->whereIn('id', $selectedClientIds);
    @endphp

    <div class="te-layout-grid">
        {{-- ── Left Main Form Column ── --}}
        <div class="te-form-column">
            <form action="{{ route('admin.team.update', $user) }}" method="POST" id="editTeamMemberForm" novalidate>
                @csrf
                @method('PUT')

                {{-- Hidden Inputs for Multiple Client IDs --}}
                <div id="clientHiddenInputs">
                    @foreach($selectedClientIds as $cid)
                        <input type="hidden" name="client_ids[]" value="{{ $cid }}" class="hidden-client-input" data-client-id="{{ $cid }}">
                    @endforeach
                </div>

                {{-- Section 1: Basic Identity --}}
                <div class="te-card">
                    <div class="te-card-header">
                        <div class="te-card-icon" style="background:rgba(79,109,240,0.1);color:var(--primary)">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div>
                            <h2 class="te-card-title">Identity & Contact</h2>
                            <p class="te-card-subtitle">Member's full display name, avatar brand color, and primary login email address.</p>
                        </div>
                    </div>
                    <div class="te-card-body">
                        <div class="te-fields-row">
                            <div class="te-field">
                                <label for="nameInput">Full Name <span class="te-req">*</span></label>
                                <div class="te-input-wrap {{ (isset($errors) && $errors->has('name')) ? 'has-error' : '' }}">
                                    <i class="fas fa-user te-input-icon"></i>
                                    <input type="text" name="name" id="nameInput" value="{{ old('name', $user->name) }}" required maxlength="255" placeholder="e.g. John Doe" autocomplete="name">
                                </div>
                                @error('name')
                                    <span class="te-error-msg">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="te-field">
                                <label for="emailInput">Email Address <span class="te-req">*</span></label>
                                <div class="te-input-wrap {{ (isset($errors) && $errors->has('email')) ? 'has-error' : '' }}">
                                    <i class="fas fa-envelope te-input-icon"></i>
                                    <input type="email" name="email" id="emailInput" value="{{ old('email', $user->email) }}" required maxlength="255" placeholder="e.g. john@company.com" autocomplete="email">
                                </div>
                                @error('email')
                                    <span class="te-error-msg">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        {{-- Avatar Color Swatches --}}
                        <div class="te-field" style="margin-top:20px;padding-top:16px;border-top:1px dashed var(--border)">
                            <label>Avatar Brand Color</label>
                            <p class="te-label-sub" style="margin-bottom:12px">Choose the avatar background color used on badges, comments, and member cards.</p>
                            <div class="te-color-palette" id="teColorPalette">
                                @foreach($presetColors as $pc)
                                    @php $isColorSelected = (strtolower($selectedAvatarColor) === strtolower($pc)); @endphp
                                    <label class="te-color-swatch {{ $isColorSelected ? 'active' : '' }}" style="background:{{ $pc }}">
                                        <input type="radio" name="avatar_color" value="{{ $pc }}" {{ $isColorSelected ? 'checked' : '' }} onchange="onAvatarColorChange('{{ $pc }}')">
                                        <i class="fas fa-check"></i>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Assigned Clients / Organizations (Multi-Select) --}}
                <div class="te-card">
                    <div class="te-card-header">
                        <div class="te-card-icon" style="background:rgba(14,165,233,0.1);color:#0EA5E9">
                            <i class="fas fa-building"></i>
                        </div>
                        <div style="flex:1">
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                                <h2 class="te-card-title">Assigned Clients / Organizations</h2>
                                <span class="te-client-count-pill" id="clientCountBadge">
                                    <span id="clientCountNum">{{ count($selectedClientIds) }}</span> Assigned
                                </span>
                            </div>
                            <p class="te-card-subtitle">Link this user to one or multiple client accounts for client portal access, project oversight, or dedicated handling.</p>
                        </div>
                    </div>
                    <div class="te-card-body">
                        <div class="te-field">
                            <label>Client Assignments</label>

                            {{-- Selected Client Chips Display --}}
                            <div class="te-selected-chips-wrap" id="selectedClientChips">
                                @forelse($assignedClients as $ac)
                                    <div class="te-client-chip" data-client-id="{{ $ac->id }}">
                                        <span class="te-chip-icon">
                                            @if($ac->logo)
                                                <img src="{{ asset('storage/' . $ac->logo) }}" alt="" class="te-chip-img">
                                            @else
                                                <span class="te-chip-emoji">{{ $ac->emoji ?: '🏢' }}</span>
                                            @endif
                                        </span>
                                        <span class="te-chip-name">{{ $ac->name }}</span>
                                        <button type="button" class="te-chip-remove" onclick="removeClientChip({{ $ac->id }})" title="Remove client">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @empty
                                    <div class="te-no-chips" id="noClientsNotice">
                                        <i class="fas fa-users-viewfinder" style="margin-right:6px;opacity:0.6"></i> None (Internal Agency Staff — No client restrictions)
                                    </div>
                                @endforelse
                            </div>

                            {{-- Multi-Select Dropdown Component --}}
                            <div class="custom-client-selector" id="clientSelectorWrapper" style="margin-top:12px">
                                <div class="ccs-trigger" id="ccsTrigger" onclick="toggleClientSelectDropdown()">
                                    <div class="ccs-trigger-content">
                                        <i class="fas fa-search" style="color:var(--text3);margin-right:8px;font-size:13px"></i>
                                        <span class="ccs-trigger-text" id="ccsTriggerText">Click to add / manage client assignments...</span>
                                    </div>
                                    <div class="ccs-trigger-icons">
                                        <button type="button" class="ccs-clear-btn" id="ccsClearAllBtn" onclick="event.stopPropagation(); clearAllClients()" title="Clear all clients" style="{{ count($selectedClientIds) > 0 ? '' : 'display:none;' }}">
                                            <i class="fas fa-trash-can"></i> Clear All
                                        </button>
                                        <i class="fas fa-chevron-down ccs-arrow" id="ccsArrow"></i>
                                    </div>
                                </div>

                                {{-- Dropdown Panel --}}
                                <div class="ccs-menu" id="ccsMenu" style="display:none">
                                    <div class="ccs-search-box">
                                        <i class="fas fa-search ccs-search-icon"></i>
                                        <input type="text" id="ccsSearchInput" class="ccs-search-field" placeholder="Type to filter clients..." autocomplete="off">
                                    </div>

                                    <div class="ccs-quick-actions">
                                        <button type="button" class="ccs-qa-link" onclick="selectAllClients()"><i class="fas fa-check-double"></i> Select All</button>
                                        <button type="button" class="ccs-qa-link ccs-qa-clear" onclick="clearAllClients()"><i class="fas fa-xmark"></i> Unassign All</button>
                                    </div>

                                    <div class="ccs-options-list" id="ccsOptionsList">
                                        @foreach($clients as $c)
                                            @php $isAssigned = in_array($c->id, $selectedClientIds); @endphp
                                            <div class="ccs-item {{ $isAssigned ? 'selected' : '' }}"
                                                 data-id="{{ $c->id }}"
                                                 data-name="{{ $c->name }}"
                                                 data-emoji="{{ $c->emoji ?: '🏢' }}"
                                                 data-logo="{{ $c->logo ? asset('storage/' . $c->logo) : '' }}"
                                                 data-color="{{ $c->color ?: '#4F6DF0' }}"
                                                 data-category="{{ $c->category ?: 'Client Organization' }}"
                                                 onclick="toggleClientOption(this)">
                                                <div class="ccs-checkbox-box">
                                                    <input type="checkbox" class="ccs-real-check" {{ $isAssigned ? 'checked' : '' }} onclick="event.stopPropagation(); toggleClientOption(this.closest('.ccs-item'))">
                                                </div>
                                                <div class="ccs-item-avatar" style="{{ $c->color ? 'border-color:'.$c->color.'30' : '' }}">
                                                    @if($c->logo)
                                                        <img src="{{ asset('storage/' . $c->logo) }}" alt="" class="ccs-client-thumb">
                                                    @else
                                                        <span class="ccs-client-emoji">{{ $c->emoji ?: '🏢' }}</span>
                                                    @endif
                                                </div>
                                                <div class="ccs-item-info">
                                                    <div class="ccs-item-title">{{ $c->name }}</div>
                                                    <div class="ccs-item-subtitle">{{ $c->category ?: 'Client Organization' }}</div>
                                                </div>
                                                <i class="fas fa-check ccs-item-check"></i>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <span class="te-hint"><i class="fas fa-info-circle"></i> You can select multiple clients. If no clients are assigned, this member is considered internal staff with unrestricted general workspace access.</span>
                            @error('client_ids')
                                <span class="te-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Section 3: Primary & Secondary Roles --}}
                <div class="te-card">
                    <div class="te-card-header">
                        <div class="te-card-icon" style="background:rgba(139,92,246,0.1);color:#8B5CF6">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <div>
                            <h2 class="te-card-title">Role & Department Capabilities</h2>
                            <p class="te-card-subtitle">Primary operational role and secondary cross-functional capabilities for task assignments.</p>
                        </div>
                    </div>
                    <div class="te-card-body">
                        {{-- Primary Role --}}
                        <div class="te-field">
                            <label>Primary Role <span class="te-req">*</span></label>
                            <div class="te-role-grid" id="teRoleGrid">
                                @foreach($mergedRoles as $r)
                                    @php
                                        $rc = $roleColors[$r] ?? '#6B7280';
                                        $isChecked = ($selectedRole === $r);
                                    @endphp
                                    <label class="te-role-option {{ $isChecked ? 'selected' : '' }}" style="--rc:{{ $rc }}">
                                        <input type="radio" name="role" value="{{ $r }}" {{ $isChecked ? 'checked' : '' }} onchange="onPrimaryRoleChange(this)">
                                        <div class="te-role-option-inner">
                                            <div class="te-role-icon-box">
                                                <i class="{{ $roleIcons[$r] ?? 'fas fa-user' }}"></i>
                                            </div>
                                            <span class="te-role-name">{{ ucfirst(str_replace('_', ' ', $r)) }}</span>
                                            <i class="fas fa-check-circle te-role-check"></i>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('role')
                                <span class="te-error-msg">{{ $message }}</span>
                            @enderror

                            {{-- Custom Role adder --}}
                            <div class="te-custom-role-row">
                                <div class="te-input-wrap">
                                    <i class="fas fa-plus te-input-icon"></i>
                                    <input type="text" id="customRoleInput" placeholder="Add custom role (e.g. video_editor, copywriter)...">
                                </div>
                                <button type="button" class="btn-sec te-add-role-btn" onclick="handleAddCustomRole()">
                                    <i class="fas fa-plus"></i> Add Role
                                </button>
                            </div>
                        </div>

                        {{-- Secondary Roles --}}
                        <div class="te-field" style="margin-top:24px;border-top:1px dashed var(--border);padding-top:20px">
                            <label>
                                Secondary Roles / Multi-Skill Capabilities
                                <span class="te-label-sub">(Allows this member to be assigned to tasks across different departments)</span>
                            </label>
                            <div class="te-secondary-roles-grid" id="teAdditionalRolesContainer">
                                @foreach($mergedRoles as $r)
                                    @php
                                        $rc = $roleColors[$r] ?? '#6B7280';
                                        $isSecondaryChecked = in_array($r, $selectedAdditionalRoles, true);
                                    @endphp
                                    <label class="te-sec-role-item {{ $isSecondaryChecked ? 'checked' : '' }}" style="--rc:{{ $rc }}">
                                        <input type="checkbox" name="additional_roles[]" value="{{ $r }}" {{ $isSecondaryChecked ? 'checked' : '' }} onchange="this.parentElement.classList.toggle('checked', this.checked)">
                                        <i class="{{ $roleIcons[$r] ?? 'fas fa-user' }}" style="color:{{ $rc }}"></i>
                                        <span>{{ ucfirst(str_replace('_', ' ', $r)) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('additional_roles')
                                <span class="te-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Section 4: Security & Permissions --}}
                <div class="te-card">
                    <div class="te-card-header">
                        <div class="te-card-icon" style="background:rgba(16,185,129,0.1);color:#10B981">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <div>
                            <h2 class="te-card-title">Security & Permissions</h2>
                            <p class="te-card-subtitle">Password credential management and granular feature permissions.</p>
                        </div>
                    </div>
                    <div class="te-card-body">
                        {{-- Password --}}
                        <div class="te-field">
                            <label for="passwordInput">New Password</label>
                            <div class="te-input-wrap {{ (isset($errors) && $errors->has('password')) ? 'has-error' : '' }}">
                                <i class="fas fa-lock te-input-icon"></i>
                                <input type="password" name="password" id="passwordInput" minlength="6" placeholder="Leave blank to keep existing password" autocomplete="new-password">
                                <button type="button" class="te-pw-toggle" onclick="togglePasswordVisibility('passwordInput', this)" title="Toggle password visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="te-error-msg">{{ $message }}</span>
                            @enderror
                            <div class="te-pw-strength-box" id="passwordStrengthBox" style="display:none">
                                <div class="te-pw-bar-track">
                                    <div class="te-pw-bar-fill" id="pwBarFill"></div>
                                </div>
                                <span class="te-pw-strength-text" id="pwStrengthText"></span>
                            </div>
                            <span class="te-hint"><i class="fas fa-info-circle"></i> Minimum 6 characters. Leave blank if password does not need to be changed.</span>
                        </div>

                        {{-- Permission Toggle --}}
                        <div class="te-field" style="margin-top:22px;border-top:1px dashed var(--border);padding-top:20px">
                            <label>Feature Permissions</label>
                            <label class="te-permission-card">
                                <input type="hidden" name="can_manage_social_metrics" value="0">
                                <input type="checkbox" name="can_manage_social_metrics" value="1" {{ old('can_manage_social_metrics', $user->can_manage_social_metrics) ? 'checked' : '' }}>
                                <div class="te-permission-info">
                                    <div class="te-permission-title">Social Media Analytics Management</div>
                                    <div class="te-permission-desc">Allows this member to add, update, and manage social metrics and platform performance reports.</div>
                                </div>
                                <div class="te-switch-visual"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Bottom Action Bar for Mobile / Convenience --}}
                <div class="te-form-footer">
                    <a href="{{ route('admin.team') }}" class="btn-sec" style="padding:12px 24px;font-weight:700">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn-primary" style="padding:12px 28px;font-weight:700">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Right Sidebar / Profile Preview & Stats Column ── --}}
        <div class="te-sidebar-column">
            {{-- Live Profile Preview Card --}}
            <div class="te-side-card te-preview-card">
                <div class="te-side-card-header">
                    <span class="te-preview-badge"><i class="fas fa-sparkles"></i> Live Preview</span>
                </div>
                <div class="te-preview-body">
                    <div class="te-preview-avatar" id="liveAvatarPreview" style="background:{{ $selectedAvatarColor }}">
                        <span id="liveAvatarInitials">{{ $user->initial ?: 'U' }}</span>
                    </div>
                    <div class="te-preview-name" id="livePreviewName">{{ $user->name }}</div>
                    <div class="te-preview-email" id="livePreviewEmail">{{ $user->email }}</div>

                    <div class="te-preview-badges-wrap" style="margin-top:12px;display:flex;flex-direction:column;align-items:center;gap:6px">
                        @php $primaryColor = $roleColors[$selectedRole] ?? '#6B7280'; @endphp
                        <span class="tm-role-badge" id="livePreviewRoleBadge" style="--role-color:{{ $primaryColor }}">
                            <i id="livePreviewRoleIcon" class="{{ $roleIcons[$selectedRole] ?? 'fas fa-user' }}" style="font-size:10px"></i>
                            <span id="livePreviewRoleText">{{ ucfirst(str_replace('_', ' ', $selectedRole)) }}</span>
                        </span>

                        {{-- Multiple Clients Badges in Preview --}}
                        <div class="te-preview-clients-list" id="livePreviewClientsList">
                            @foreach($assignedClients as $ac)
                                <span class="te-preview-client-badge" data-preview-client-id="{{ $ac->id }}">
                                    @if($ac->logo)
                                        <img src="{{ asset('storage/' . $ac->logo) }}" class="te-prev-c-thumb">
                                    @else
                                        <span class="te-prev-c-emoji">{{ $ac->emoji ?: '🏢' }}</span>
                                    @endif
                                    <span>{{ $ac->name }}</span>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <div class="te-preview-divider"></div>

                    <div class="te-preview-meta-list">
                        <div class="te-preview-meta-row">
                            <span class="te-meta-label"><i class="fas fa-hashtag"></i> Member ID</span>
                            <span class="te-meta-val">#{{ $user->id }}</span>
                        </div>
                        <div class="te-preview-meta-row">
                            <span class="te-meta-label"><i class="fas fa-building"></i> Clients</span>
                            <span class="te-meta-val" id="livePreviewClientMeta">
                                @if($assignedClients->count() > 0)
                                    {{ $assignedClients->count() }} Assigned
                                @else
                                    None (Internal)
                                @endif
                            </span>
                        </div>
                        <div class="te-preview-meta-row">
                            <span class="te-meta-label"><i class="fas fa-calendar-alt"></i> Joined</span>
                            <span class="te-meta-val">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</span>
                        </div>
                        <div class="te-preview-meta-row">
                            <span class="te-meta-label"><i class="fas fa-circle-dot"></i> Status</span>
                            <span class="te-meta-val" style="color:{{ $user->isOnline() ? 'var(--teal)' : 'var(--text3)' }}">
                                <i class="fas fa-circle" style="font-size:8px;margin-right:4px"></i>
                                {{ $user->isOnline() ? 'Online Now' : 'Offline' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Task Activity & Performance Card --}}
            <div class="te-side-card">
                <div class="te-side-card-title">
                    <i class="fas fa-chart-pie" style="color:var(--primary)"></i> Task Performance
                </div>
                <div class="te-task-stats-grid">
                    <div class="te-task-stat-item">
                        <div class="te-stat-num">{{ $user->assigned_tasks_count }}</div>
                        <div class="te-stat-lbl">Total Tasks</div>
                    </div>
                    <div class="te-task-stat-item">
                        <div class="te-stat-num" style="color:var(--primary)">{{ $user->active_tasks_count }}</div>
                        <div class="te-stat-lbl">Active</div>
                    </div>
                    <div class="te-task-stat-item">
                        <div class="te-stat-num" style="color:var(--teal)">{{ $user->completed_tasks_count }}</div>
                        <div class="te-stat-lbl">Completed</div>
                    </div>
                </div>

                <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                        <span style="font-size:12px;font-weight:600;color:var(--text2)">Completion Rate</span>
                        <span style="font-size:12px;font-weight:800;color:{{ $completionRate >= 70 ? 'var(--teal)' : ($completionRate >= 40 ? '#F59E0B' : 'var(--red)') }}">{{ $completionRate }}%</span>
                    </div>
                    <div style="height:6px;background:var(--card2);border-radius:999px;overflow:hidden">
                        <div style="height:100%;border-radius:999px;width:{{ $completionRate }}%;background:{{ $completionRate >= 70 ? 'var(--teal)' : ($completionRate >= 40 ? '#F59E0B' : 'var(--red)') }}"></div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Card --}}
            <div class="te-side-card">
                <div class="te-side-card-title">
                    <i class="fas fa-bolt" style="color:#F59E0B"></i> Quick Actions
                </div>
                <div class="te-quick-actions">
                    <a href="{{ route('admin.team') }}" class="te-qa-btn">
                        <i class="fas fa-users"></i>
                        <span>View All Team Members</span>
                    </a>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.team.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove team member \'{{ addslashes($user->name) }}\'? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="te-qa-btn te-qa-btn-danger">
                                <i class="fas fa-trash-alt"></i>
                                <span>Remove Team Member</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════
   TEAM MEMBER EDIT PAGE STYLES — REFINED SAAS LAYOUT
   ═══════════════════════════════════════════════════════════ */
.team-edit-page-container {
    max-width: 1320px;
    margin: 0 auto;
    padding: 24px 28px 60px;
}

/* ── Top Header & Navigation ── */
.te-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}
.te-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.te-back-btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: var(--card);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text2);
    font-size: 16px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.te-back-btn:hover {
    background: var(--card2);
    color: var(--primary);
    border-color: var(--primary);
    transform: translateX(-3px);
}
.te-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text3);
    margin-bottom: 4px;
}
.te-breadcrumbs a {
    color: var(--text3);
    text-decoration: none;
    transition: color 0.15s;
}
.te-breadcrumbs a:hover {
    color: var(--primary);
}
.te-bc-sep {
    font-size: 9px;
    opacity: 0.6;
}
.te-page-title {
    margin: 0;
    font-size: 26px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.5px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.te-page-subtitle {
    margin: 4px 0 0;
    font-size: 13px;
    color: var(--text3);
}
.te-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.te-btn-cancel {
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.te-btn-save {
    padding: 10px 22px;
    font-size: 13px;
    font-weight: 700;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(79,109,240,0.3);
}

/* ── Flash Messages ── */
.tm-flash {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 22px;
    font-size: 13px;
    font-weight: 600;
}
.tm-flash-success { background: rgba(16,185,129,0.08); color: #059669; border: 1px solid rgba(16,185,129,0.2); }
.tm-flash-error { background: rgba(239,68,68,0.08); color: #DC2626; border: 1px solid rgba(239,68,68,0.2); }
.tm-flash-close { background: none; border: none; color: inherit; cursor: pointer; opacity: 0.6; margin-left: auto; font-size: 14px; }
.tm-flash-close:hover { opacity: 1; }

/* ── Grid Layout ── */
.te-layout-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 1024px) {
    .te-layout-grid {
        grid-template-columns: 1fr;
    }
}

/* ── Cards ── */
.te-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    transition: box-shadow 0.2s;
}
.te-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
}
.te-card-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}
.te-card-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.te-card-title {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: var(--text);
}
.te-card-subtitle {
    margin: 2px 0 0;
    font-size: 12px;
    color: var(--text3);
}

/* ── Form Inputs ── */
.te-fields-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}
@media (max-width: 640px) {
    .te-fields-row {
        grid-template-columns: 1fr;
    }
}
.te-field {
    display: flex;
    flex-direction: column;
}
.te-field label {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.te-req { color: #EF4444; }
.te-label-sub {
    font-size: 11px;
    font-weight: 500;
    color: var(--text3);
}
.te-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--card2);
    transition: all 0.2s;
}
.te-input-wrap:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,109,240,0.12);
    background: var(--card);
}
.te-input-wrap.has-error {
    border-color: #EF4444;
    background: rgba(239,68,68,0.02);
}
.te-input-icon {
    position: absolute;
    left: 14px;
    color: var(--text3);
    font-size: 14px;
    pointer-events: none;
}
.te-input-wrap input {
    width: 100%;
    padding: 12px 14px 12px 42px;
    border: none;
    background: transparent;
    font-size: 14px;
    font-family: inherit;
    color: var(--text);
    outline: none;
}
.te-pw-toggle {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    color: var(--text3);
    font-size: 14px;
    cursor: pointer;
    padding: 4px;
    transition: color 0.15s;
}
.te-pw-toggle:hover {
    color: var(--text);
}
.te-error-msg {
    color: #EF4444;
    font-size: 12px;
    font-weight: 600;
    margin-top: 6px;
}
.te-hint {
    font-size: 11px;
    color: var(--text3);
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ── Selected Client Chips ── */
.te-client-count-pill {
    font-size: 11px;
    font-weight: 800;
    background: rgba(14,165,233,0.1);
    color: #0284C7;
    border: 1px solid rgba(14,165,233,0.2);
    padding: 3px 10px;
    border-radius: 999px;
}
.te-selected-chips-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    min-height: 42px;
    padding: 10px;
    border: 1px dashed var(--border);
    border-radius: 12px;
    background: var(--card2);
    align-items: center;
}
.te-no-chips {
    font-size: 12px;
    font-weight: 600;
    color: var(--text3);
}
.te-client-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--card);
    border: 1px solid var(--border);
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    animation: chipFadeIn 0.2s ease;
}
@keyframes chipFadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}
.te-chip-img {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    object-fit: cover;
}
.te-chip-emoji {
    font-size: 13px;
    line-height: 1;
}
.te-chip-name {
    white-space: nowrap;
}
.te-chip-remove {
    background: none;
    border: none;
    color: var(--text3);
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 4px;
    font-size: 10px;
    transition: all 0.15s;
    margin-left: 2px;
}
.te-chip-remove:hover {
    background: rgba(239,68,68,0.1);
    color: #EF4444;
}

/* ── Custom Multi-Select Dropdown ── */
.custom-client-selector {
    position: relative;
    user-select: none;
}
.ccs-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--card2);
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 44px;
}
.ccs-trigger:hover {
    border-color: var(--primary);
    background: var(--card);
}
.custom-client-selector.open .ccs-trigger {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,109,240,0.12);
    background: var(--card);
}
.ccs-trigger-content {
    display: flex;
    align-items: center;
    font-size: 13px;
    font-weight: 600;
    color: var(--text3);
}
.ccs-trigger-icons {
    display: flex;
    align-items: center;
    gap: 10px;
}
.ccs-clear-btn {
    background: rgba(239,68,68,0.08);
    border: 1px solid rgba(239,68,68,0.2);
    color: #EF4444;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.ccs-clear-btn:hover {
    background: #EF4444;
    color: #fff;
}
.ccs-arrow {
    font-size: 11px;
    color: var(--text3);
    transition: transform 0.2s;
}
.custom-client-selector.open .ccs-arrow {
    transform: rotate(180deg);
}
.ccs-menu {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    z-index: 100;
    overflow: hidden;
    animation: ccsSlideDown 0.18s ease-out;
}
@keyframes ccsSlideDown {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
.ccs-search-box {
    padding: 10px 12px;
    border-bottom: 1px solid var(--border);
    position: relative;
    background: var(--card2);
}
.ccs-search-icon {
    position: absolute;
    left: 22px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    color: var(--text3);
}
.ccs-search-field {
    width: 100%;
    padding: 8px 12px 8px 30px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--card);
    font-size: 12px;
    color: var(--text);
    outline: none;
    font-family: inherit;
}
.ccs-search-field:focus {
    border-color: var(--primary);
}
.ccs-quick-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px;
    background: var(--card2);
    border-bottom: 1px solid var(--border);
    font-size: 11px;
}
.ccs-qa-link {
    background: none;
    border: none;
    color: var(--primary);
    font-weight: 700;
    cursor: pointer;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 4px;
    border-radius: 4px;
}
.ccs-qa-link:hover {
    text-decoration: underline;
}
.ccs-qa-clear {
    color: #EF4444;
}
.ccs-options-list {
    max-height: 240px;
    overflow-y: auto;
    padding: 6px;
}
.ccs-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s;
}
.ccs-item:hover {
    background: var(--card2);
}
.ccs-item.selected {
    background: rgba(79,109,240,0.06);
}
.ccs-checkbox-box {
    display: flex;
    align-items: center;
}
.ccs-real-check {
    width: 16px;
    height: 16px;
    accent-color: var(--primary);
    cursor: pointer;
}
.ccs-item-avatar {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--card2);
    border: 1px solid var(--border);
    flex-shrink: 0;
}
.ccs-client-thumb {
    width: 26px;
    height: 26px;
    border-radius: 5px;
    object-fit: cover;
}
.ccs-client-emoji {
    font-size: 16px;
    line-height: 1;
}
.ccs-item-info {
    flex: 1;
    min-width: 0;
}
.ccs-item-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ccs-item-subtitle {
    font-size: 11px;
    color: var(--text3);
}
.ccs-item-check {
    font-size: 12px;
    color: var(--primary);
    opacity: 0;
}
.ccs-item.selected .ccs-item-check {
    opacity: 1;
}

/* ── Password Strength ── */
.te-pw-strength-box {
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.te-pw-bar-track {
    flex: 1;
    height: 4px;
    background: var(--border);
    border-radius: 999px;
    overflow: hidden;
}
.te-pw-bar-fill {
    height: 100%;
    width: 0;
    border-radius: 999px;
    transition: all 0.3s ease;
}
.te-pw-strength-text {
    font-size: 11px;
    font-weight: 700;
}

/* ── Primary Role Grid ── */
.te-role-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
    gap: 10px;
}
.te-role-option {
    position: relative;
    cursor: pointer;
    user-select: none;
}
.te-role-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.te-role-option-inner {
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px 10px;
    background: var(--card2);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 8px;
    position: relative;
    transition: all 0.2s ease;
}
.te-role-icon-box {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: color-mix(in srgb, var(--rc) 12%, transparent);
    color: var(--rc);
    font-size: 14px;
}
.te-role-name {
    font-size: 12px;
    font-weight: 700;
    color: var(--text2);
    text-transform: capitalize;
}
.te-role-check {
    position: absolute;
    top: 8px;
    right: 8px;
    font-size: 12px;
    color: var(--rc);
    opacity: 0;
    transform: scale(0.6);
    transition: all 0.2s ease;
}
.te-role-option:hover .te-role-option-inner {
    border-color: var(--rc);
    background: color-mix(in srgb, var(--rc) 4%, var(--card2));
}
.te-role-option.selected .te-role-option-inner,
.te-role-option input[type="radio"]:checked + .te-role-option-inner {
    border-color: var(--rc);
    background: color-mix(in srgb, var(--rc) 10%, var(--card));
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--rc) 25%, transparent);
}
.te-role-option.selected .te-role-check,
.te-role-option input[type="radio"]:checked + .te-role-option-inner .te-role-check {
    opacity: 1;
    transform: scale(1);
}

/* ── Custom Role Adder ── */
.te-custom-role-row {
    display: flex;
    gap: 10px;
    margin-top: 12px;
}
.te-custom-role-row .te-input-wrap {
    flex: 1;
}
.te-add-role-btn {
    padding: 0 16px;
    font-size: 12px;
    font-weight: 700;
    border-radius: 10px;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}

/* ── Secondary Roles ── */
.te-secondary-roles-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}
.te-sec-role-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--card2);
    font-size: 12px;
    font-weight: 700;
    color: var(--text2);
    cursor: pointer;
    transition: all 0.15s;
    user-select: none;
}
.te-sec-role-item input[type="checkbox"] {
    accent-color: var(--rc);
    width: 15px;
    height: 15px;
    cursor: pointer;
}
.te-sec-role-item:hover {
    border-color: var(--rc);
    background: color-mix(in srgb, var(--rc) 6%, var(--card2));
}
.te-sec-role-item.checked {
    border-color: var(--rc);
    background: color-mix(in srgb, var(--rc) 10%, var(--card));
    color: var(--text);
}

/* ── Permission Toggle Card ── */
.te-permission-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 20px;
    border: 1px solid var(--border);
    border-radius: 14px;
    background: var(--card2);
    cursor: pointer;
    transition: all 0.2s;
}
.te-permission-card:hover {
    border-color: var(--primary);
}
.te-permission-card input[type="checkbox"] {
    display: none;
}
.te-permission-info {
    flex: 1;
}
.te-permission-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 2px;
}
.te-permission-desc {
    font-size: 12px;
    color: var(--text3);
    line-height: 1.4;
}
.te-switch-visual {
    width: 44px;
    height: 24px;
    background: var(--border);
    border-radius: 999px;
    position: relative;
    transition: background 0.2s;
    flex-shrink: 0;
}
.te-switch-visual::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    top: 3px;
    left: 3px;
    transition: transform 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.te-permission-card input[type="checkbox"]:checked ~ .te-switch-visual {
    background: var(--teal);
}
.te-permission-card input[type="checkbox"]:checked ~ .te-switch-visual::after {
    transform: translateX(20px);
}

/* ── Color Palette ── */
.te-color-palette {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.te-color-swatch {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 14px;
    position: relative;
    transition: transform 0.15s, box-shadow 0.15s;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.te-color-swatch input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.te-color-swatch i {
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.15s;
}
.te-color-swatch:hover {
    transform: scale(1.1);
}
.te-color-swatch.active i,
.te-color-swatch input[type="radio"]:checked + i {
    opacity: 1;
    transform: scale(1);
}
.te-color-swatch.active {
    outline: 3px solid var(--text);
    outline-offset: 2px;
}

/* ── Form Footer ── */
.te-form-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
}

/* ── Right Sidebar Cards ── */
.te-side-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.03);
}
.te-side-card-title {
    font-size: 14px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.te-preview-card {
    text-align: center;
    background: linear-gradient(180deg, color-mix(in srgb, var(--primary) 3%, var(--card)) 0%, var(--card) 100%);
}
.te-side-card-header {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}
.te-preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(79,109,240,0.1);
    color: var(--primary);
}
.te-preview-avatar {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    margin: 0 auto 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 26px;
    font-weight: 800;
    font-family: 'Plus Jakarta Sans', sans-serif;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    transition: background 0.3s ease;
}
.te-preview-name {
    font-size: 18px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 2px;
    word-break: break-word;
}
.te-preview-email {
    font-size: 12px;
    color: var(--text3);
    word-break: break-all;
}
.te-preview-clients-list {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    justify-content: center;
    max-width: 100%;
}
.te-preview-client-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 2px 8px;
    border-radius: 6px;
    background: rgba(14,165,233,0.1);
    color: #0284C7;
    border: 1px solid rgba(14,165,233,0.2);
    font-size: 10px;
    font-weight: 700;
}
.te-prev-c-thumb {
    width: 12px;
    height: 12px;
    border-radius: 2px;
    object-fit: cover;
}
.te-prev-c-emoji {
    font-size: 11px;
    line-height: 1;
}
.te-preview-divider {
    height: 1px;
    background: var(--border);
    margin: 18px 0;
}
.te-preview-meta-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    text-align: left;
}
.te-preview-meta-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
}
.te-meta-label {
    color: var(--text3);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}
.te-meta-val {
    color: var(--text);
    font-weight: 700;
}

/* ── Task Stats Grid in Sidebar ── */
.te-task-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
    text-align: center;
}
.te-task-stat-item {
    background: var(--card2);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px 6px;
}
.te-stat-num {
    font-size: 20px;
    font-weight: 800;
    color: var(--text);
    font-family: 'Plus Jakarta Sans', sans-serif;
    line-height: 1.1;
}
.te-stat-lbl {
    font-size: 10px;
    font-weight: 700;
    color: var(--text3);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-top: 3px;
}

/* ── Quick Actions ── */
.te-quick-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.te-qa-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 14px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--card2);
    color: var(--text2);
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s;
    width: 100%;
    cursor: pointer;
}
.te-qa-btn:hover {
    background: var(--card);
    border-color: var(--primary);
    color: var(--primary);
}
.te-qa-btn-danger {
    color: #EF4444;
    border-color: rgba(239,68,68,0.2);
    background: rgba(239,68,68,0.03);
}
.te-qa-btn-danger:hover {
    background: #FEE2E2;
    border-color: #EF4444;
    color: #DC2626;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ROLE_ICONS = @json($roleIcons);
    const ROLE_COLORS = @json($roleColors);

    const nameInput = document.getElementById('nameInput');
    const emailInput = document.getElementById('emailInput');
    const passwordInput = document.getElementById('passwordInput');

    const liveAvatarPreview = document.getElementById('liveAvatarPreview');
    const liveAvatarInitials = document.getElementById('liveAvatarInitials');
    const livePreviewName = document.getElementById('livePreviewName');
    const livePreviewEmail = document.getElementById('livePreviewEmail');
    const livePreviewRoleBadge = document.getElementById('livePreviewRoleBadge');
    const livePreviewRoleIcon = document.getElementById('livePreviewRoleIcon');
    const livePreviewRoleText = document.getElementById('livePreviewRoleText');
    const livePreviewClientsList = document.getElementById('livePreviewClientsList');
    const livePreviewClientMeta = document.getElementById('livePreviewClientMeta');

    function updateInitials(name) {
        if (!name || !name.trim()) {
            liveAvatarInitials.textContent = '?';
            return;
        }
        const parts = name.trim().split(/\s+/);
        let initials = parts[0].charAt(0).toUpperCase();
        if (parts.length > 1) {
            initials += parts[parts.length - 1].charAt(0).toUpperCase();
        }
        liveAvatarInitials.textContent = initials.slice(0, 2);
    }

    if (nameInput) {
        nameInput.addEventListener('input', function() {
            const val = this.value.trim();
            livePreviewName.textContent = val || 'Member Name';
            updateInitials(val);
        });
    }

    if (emailInput) {
        emailInput.addEventListener('input', function() {
            livePreviewEmail.textContent = this.value.trim() || 'email@example.com';
        });
    }

    // ── Multi-Client Selector Logic ──
    const wrapper = document.getElementById('clientSelectorWrapper');
    const menu = document.getElementById('ccsMenu');
    const searchInput = document.getElementById('ccsSearchInput');
    const chipsWrap = document.getElementById('selectedClientChips');
    const hiddenInputsWrap = document.getElementById('clientHiddenInputs');
    const countNum = document.getElementById('clientCountNum');
    const clearAllBtn = document.getElementById('ccsClearAllBtn');

    window.toggleClientSelectDropdown = function() {
        const isOpen = menu.style.display !== 'none';
        if (isOpen) {
            menu.style.display = 'none';
            wrapper.classList.remove('open');
        } else {
            menu.style.display = 'block';
            wrapper.classList.add('open');
            if (searchInput) {
                searchInput.value = '';
                filterClients('');
                setTimeout(() => searchInput.focus(), 50);
            }
        }
    };

    window.toggleClientOption = function(itemEl) {
        const id = itemEl.dataset.id;
        const name = itemEl.dataset.name;
        const emoji = itemEl.dataset.emoji || '🏢';
        const logo = itemEl.dataset.logo || '';
        const isSelected = itemEl.classList.contains('selected');

        if (isSelected) {
            // Remove
            removeClientChip(id);
        } else {
            // Add
            addClient(id, name, emoji, logo);
        }
    };

    function addClient(id, name, emoji, logo) {
        // Mark selected in list
        const itemEl = document.querySelector(`.ccs-item[data-id="${id}"]`);
        if (itemEl) {
            itemEl.classList.add('selected');
            const chk = itemEl.querySelector('.ccs-real-check');
            if (chk) chk.checked = true;
        }

        // Add hidden input if not present
        if (!hiddenInputsWrap.querySelector(`input[value="${id}"]`)) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'client_ids[]';
            hidden.value = id;
            hidden.className = 'hidden-client-input';
            hidden.dataset.clientId = id;
            hiddenInputsWrap.appendChild(hidden);
        }

        // Remove placeholder if present
        const noNotice = document.getElementById('noClientsNotice');
        if (noNotice) noNotice.remove();

        // Add chip if not present
        if (!chipsWrap.querySelector(`.te-client-chip[data-client-id="${id}"]`)) {
            const chip = document.createElement('div');
            chip.className = 'te-client-chip';
            chip.dataset.clientId = id;
            const thumbHtml = logo 
                ? `<img src="${logo}" alt="" class="te-chip-img">` 
                : `<span class="te-chip-emoji">${emoji}</span>`;
            chip.innerHTML = `
                <span class="te-chip-icon">${thumbHtml}</span>
                <span class="te-chip-name">${escapeHtml(name)}</span>
                <button type="button" class="te-chip-remove" onclick="removeClientChip(${id})" title="Remove client">
                    <i class="fas fa-times"></i>
                </button>
            `;
            chipsWrap.appendChild(chip);
        }

        updateClientUiState();
    }

    window.removeClientChip = function(id) {
        id = String(id);
        // Unmark in list
        const itemEl = document.querySelector(`.ccs-item[data-id="${id}"]`);
        if (itemEl) {
            itemEl.classList.remove('selected');
            const chk = itemEl.querySelector('.ccs-real-check');
            if (chk) chk.checked = false;
        }

        // Remove hidden input
        const hidden = hiddenInputsWrap.querySelector(`input[value="${id}"]`);
        if (hidden) hidden.remove();

        // Remove chip
        const chip = chipsWrap.querySelector(`.te-client-chip[data-client-id="${id}"]`);
        if (chip) chip.remove();

        // If no chips left, add placeholder
        if (chipsWrap.querySelectorAll('.te-client-chip').length === 0) {
            if (!document.getElementById('noClientsNotice')) {
                const noNotice = document.createElement('div');
                noNotice.className = 'te-no-chips';
                noNotice.id = 'noClientsNotice';
                noNotice.innerHTML = '<i class="fas fa-users-viewfinder" style="margin-right:6px;opacity:0.6"></i> None (Internal Agency Staff — No client restrictions)';
                chipsWrap.appendChild(noNotice);
            }
        }

        updateClientUiState();
    };

    window.clearAllClients = function() {
        document.querySelectorAll('.ccs-item').forEach(item => {
            item.classList.remove('selected');
            const chk = item.querySelector('.ccs-real-check');
            if (chk) chk.checked = false;
        });

        hiddenInputsWrap.innerHTML = '';
        chipsWrap.innerHTML = '<div class="te-no-chips" id="noClientsNotice"><i class="fas fa-users-viewfinder" style="margin-right:6px;opacity:0.6"></i> None (Internal Agency Staff — No client restrictions)</div>';

        updateClientUiState();
    };

    window.selectAllClients = function() {
        document.querySelectorAll('.ccs-item').forEach(item => {
            const id = item.dataset.id;
            const name = item.dataset.name;
            const emoji = item.dataset.emoji || '🏢';
            const logo = item.dataset.logo || '';
            addClient(id, name, emoji, logo);
        });
    };

    function updateClientUiState() {
        const selectedCount = hiddenInputsWrap.querySelectorAll('input').length;
        if (countNum) countNum.textContent = selectedCount;
        if (clearAllBtn) clearAllBtn.style.display = selectedCount > 0 ? 'inline-flex' : 'none';

        // Update Live Preview Sidebar
        if (livePreviewClientsList) {
            livePreviewClientsList.innerHTML = '';
            document.querySelectorAll('.te-client-chip').forEach(chip => {
                const id = chip.dataset.clientId;
                const name = chip.querySelector('.te-chip-name')?.textContent || '';
                const img = chip.querySelector('.te-chip-img');
                const emoji = chip.querySelector('.te-chip-emoji')?.textContent || '🏢';

                const badge = document.createElement('span');
                badge.className = 'te-preview-client-badge';
                badge.dataset.previewClientId = id;
                badge.innerHTML = img 
                    ? `<img src="${img.src}" class="te-prev-c-thumb"> <span>${escapeHtml(name)}</span>` 
                    : `<span class="te-prev-c-emoji">${emoji}</span> <span>${escapeHtml(name)}</span>`;
                livePreviewClientsList.appendChild(badge);
            });
        }

        if (livePreviewClientMeta) {
            livePreviewClientMeta.textContent = selectedCount > 0 ? `${selectedCount} Assigned` : 'None (Internal)';
        }
    }

    function filterClients(q) {
        const query = (q || '').toLowerCase().trim();
        document.querySelectorAll('.ccs-item').forEach(item => {
            const title = (item.dataset.name || '').toLowerCase();
            const cat = (item.dataset.category || '').toLowerCase();
            const matches = title.includes(query) || cat.includes(query);
            item.style.display = matches ? 'flex' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterClients(this.value);
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#clientSelectorWrapper')) {
            if (menu && menu.style.display !== 'none') {
                menu.style.display = 'none';
                wrapper.classList.remove('open');
            }
        }
    });

    function escapeHtml(str) {
        return String(str ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;');
    }

    // Password visibility toggle
    window.togglePasswordVisibility = function(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.querySelector('i').className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
    };

    // Password strength check
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const val = this.value;
            const box = document.getElementById('passwordStrengthBox');
            const bar = document.getElementById('pwBarFill');
            const text = document.getElementById('pwStrengthText');

            if (!val) {
                box.style.display = 'none';
                return;
            }

            box.style.display = 'flex';
            let score = 0;
            if (val.length >= 6) score++;
            if (val.length >= 10) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            if (score <= 1) {
                bar.style.width = '25%';
                bar.style.background = '#EF4444';
                text.textContent = 'Weak';
                text.style.color = '#EF4444';
            } else if (score <= 3) {
                bar.style.width = '60%';
                bar.style.background = '#F59E0B';
                text.textContent = 'Medium';
                text.style.color = '#F59E0B';
            } else {
                bar.style.width = '100%';
                bar.style.background = '#10B981';
                text.textContent = 'Strong';
                text.style.color = '#10B981';
            }
        });
    }

    // Avatar Color Selection
    window.onAvatarColorChange = function(color) {
        liveAvatarPreview.style.background = color;
        document.querySelectorAll('.te-color-swatch').forEach(sw => {
            const radio = sw.querySelector('input');
            sw.classList.toggle('active', radio && radio.value.toLowerCase() === color.toLowerCase());
        });
    };

    // Primary Role Change
    window.onPrimaryRoleChange = function(radio) {
        const role = radio.value;
        const color = ROLE_COLORS[role] || '#6B7280';
        const icon = ROLE_ICONS[role] || 'fas fa-user';
        const label = role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

        document.querySelectorAll('.te-role-option').forEach(opt => {
            opt.classList.toggle('selected', opt.querySelector('input') === radio);
        });

        livePreviewRoleBadge.style.setProperty('--role-color', color);
        livePreviewRoleIcon.className = icon;
        livePreviewRoleText.textContent = label;
    };

    // Custom Role Addition
    window.handleAddCustomRole = function() {
        const input = document.getElementById('customRoleInput');
        if (!input) return;
        const raw = input.value.trim();
        if (!raw) return;

        const normalized = raw.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '').slice(0, 50);
        if (!normalized) return;

        const roleGrid = document.getElementById('teRoleGrid');
        const secGrid = document.getElementById('teAdditionalRolesContainer');

        let existingRadio = roleGrid.querySelector('input[name="role"][value="' + normalized + '"]');
        if (!existingRadio) {
            const rc = ROLE_COLORS[normalized] || '#6B7280';
            const icon = ROLE_ICONS[normalized] || 'fas fa-user-tag';
            const label = normalized.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

            // Add to Primary Role Grid
            const opt = document.createElement('label');
            opt.className = 'te-role-option';
            opt.style.setProperty('--rc', rc);
            opt.innerHTML = `
                <input type="radio" name="role" value="${normalized}" onchange="onPrimaryRoleChange(this)">
                <div class="te-role-option-inner">
                    <div class="te-role-icon-box"><i class="${icon}"></i></div>
                    <span class="te-role-name">${label}</span>
                    <i class="fas fa-check-circle te-role-check"></i>
                </div>
            `;
            roleGrid.appendChild(opt);
            existingRadio = opt.querySelector('input');

            // Add to Secondary Roles Container
            if (secGrid && !secGrid.querySelector('input[value="' + normalized + '"]')) {
                const secItem = document.createElement('label');
                secItem.className = 'te-sec-role-item';
                secItem.style.setProperty('--rc', rc);
                secItem.innerHTML = `
                    <input type="checkbox" name="additional_roles[]" value="${normalized}" onchange="this.parentElement.classList.toggle('checked', this.checked)">
                    <i class="${icon}" style="color:${rc}"></i>
                    <span>${label}</span>
                `;
                secGrid.appendChild(secItem);
            }
        }

        if (existingRadio) {
            existingRadio.checked = true;
            window.onPrimaryRoleChange(existingRadio);
        }

        input.value = '';
    };
});
</script>
@endpush
@endsection
