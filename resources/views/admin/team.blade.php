@extends('layouts.app')

@section('content')
<div class="team-container">

    {{-- ── Success/Error Flash ── --}}
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
            <span>{{ $errors->first() }}</span>
            <button onclick="this.parentElement.remove()" class="tm-flash-close"><i class="fas fa-times"></i></button>
        </div>
    @endif

    {{-- ── Page Header ── --}}
    <div class="team-header">
        <div>
            <div class="page-title"><i class="fas fa-users" style="margin-right:10px;color:var(--primary)"></i>Team Management</div>
            <div class="page-subtitle">{{ $stats['total'] }} members across {{ count($stats['byRole']) }} roles</div>
        </div>
        <button class="btn-primary team-add-btn" onclick="openAddModal()">
            <i class="fas fa-user-plus"></i><span class="btn-text">Add Member</span>
        </button>
    </div>

    <div id="team-ajax-area">
    {{-- ── Stats ── --}}
    <div class="tm-stats">
        @php
            $roleIcons = [
                'admin' => 'fas fa-shield-alt', 'strategist' => 'fas fa-chess-queen', 'designer' => 'fas fa-palette',
                'developer' => 'fas fa-code', 'client' => 'fas fa-building',
            ];
            $roleColors = [
                'admin' => '#EF4444', 'strategist' => '#8B5CF6', 'designer' => '#3B82F6',
                'developer' => '#F97316', 'client' => '#0EA5E9',
            ];
        @endphp
        <div class="tm-stat-card tm-stat-total">
            <div class="tm-stat-icon" style="background:rgba(79,109,240,0.1);color:var(--primary)"><i class="fas fa-users"></i></div>
            <div>
                <div class="tm-stat-num">{{ $stats['total'] }}</div>
                <div class="tm-stat-label">Total Members</div>
            </div>
        </div>
        @foreach($stats['byRole'] as $role => $count)
            @php $rc = $roleColors[$role] ?? '#6B7280'; @endphp
            <div class="tm-stat-card">
                <div class="tm-stat-icon" style="background:{{ $rc }}12;color:{{ $rc }}"><i class="{{ $roleIcons[$role] ?? 'fas fa-user' }}"></i></div>
                <div>
                    <div class="tm-stat-num" style="color:{{ $rc }}">{{ $count }}</div>
                    <div class="tm-stat-label">{{ ucfirst(str_replace('_', ' ', $role)) }}{{ $count !== 1 ? 's' : '' }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Filters ── --}}
    <div class="team-filters-card">
        <form method="GET" action="{{ route('admin.team') }}" class="team-filter-form">
            <div class="tm-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email or role...">
            </div>
            <div class="tm-filter-pills">
                <a href="{{ route('admin.team') }}" class="tm-pill {{ !request('role') ? 'active' : '' }}">All</a>
                @foreach($roles as $r)
                    <a href="{{ route('admin.team', ['role' => $r, 'search' => request('search')]) }}" class="tm-pill {{ request('role') === $r ? 'active' : '' }}">
                        <i class="{{ $roleIcons[$r] ?? 'fas fa-user' }}" style="font-size:10px"></i> {{ ucfirst(str_replace('_', ' ', $r)) }}
                    </a>
                @endforeach
            </div>
            @if(request('search'))
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <span class="tm-active-filter">
                        "{{ request('search') }}"
                        <a href="{{ route('admin.team', request('role') ? ['role' => request('role')] : []) }}"><i class="fas fa-times"></i></a>
                    </span>
                </div>
            @endif
            <noscript><button type="submit" class="btn-primary" style="padding:10px 20px">Search</button></noscript>
        </form>

        {{-- ── Sorting Controls ── --}}
        <div class="tm-sort-controls">
            <span class="tm-sort-label"><i class="fas fa-sort-amount-down"></i> Sort by:</span>
            <div class="tm-sort-pills">
                <button class="tm-sort-btn active" data-sort="name" title="Sort alphabetically">
                    <i class="fas fa-font"></i> Name
                </button>
                <button class="tm-sort-btn" data-sort="active" title="Sort by active tasks (most first)">
                    <i class="fas fa-fire"></i> Most Active
                </button>
                <button class="tm-sort-btn" data-sort="newest" title="Sort by join date (newest first)">
                    <i class="fas fa-clock"></i> Newest
                </button>
                <button class="tm-sort-btn" data-sort="completion" title="Sort by completion rate (highest first)">
                    <i class="fas fa-chart-line"></i> Completion Rate
                </button>
            </div>
        </div>
    </div>

    {{-- ── Members Grid ── --}}
    <div class="tm-grid" id="tmMembersGrid">
        @forelse($members as $member)
            @php
                $rc = $roleColors[$member->role] ?? '#6B7280';
                $eff = $member->assigned_tasks_count > 0 ? round(($member->completed_tasks_count / $member->assigned_tasks_count) * 100) : 0;
            @endphp
            <div class="tm-card" data-member-id="{{ $member->id }}"
                 data-name="{{ $member->name }}"
                 data-active="{{ $member->active_tasks_count }}"
                 data-completion="{{ $eff }}"
                 data-created="{{ $member->created_at?->timestamp ?? 0 }}">
                {{-- Clickable card area for drawer --}}
                <div class="tm-card-header" onclick="openMemberDrawer({{ $member->id }})" style="cursor:pointer">
                    <div class="tm-avatar" style="background:{{ $member->avatar_color ?? '#4F6DF0' }}">
                        {{ strtoupper(substr($member->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $member->name)[1] ?? '', 0, 1)) }}
                    </div>
                    <div class="tm-info">
                        <div class="tm-name">
                            {{ $member->name }}
                            @if($member->id === auth()->id())
                                <span class="tm-you-badge">You</span>
                            @endif
                        </div>
                        <div class="tm-email"><i class="fas fa-envelope" style="font-size:9px;margin-right:4px;opacity:0.5"></i>{{ $member->email }}</div>
                    </div>
                    <div class="tm-actions" onclick="event.stopPropagation()">
                        <a href="{{ route('admin.team.edit', $member->id) }}" class="tm-action-btn tm-edit-btn" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        @if($member->id !== auth()->id())
                            <button type="button" class="tm-action-btn tm-del-btn" onclick="deleteMember({{ $member->id }}, '{{ addslashes($member->name) }}')" title="Remove">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="tm-card-body" style="flex-wrap:wrap;gap:6px">
                    <span class="tm-role-badge" style="--role-color:{{ $rc }}">
                        <i class="{{ $roleIcons[$member->role] ?? 'fas fa-user' }}" style="font-size:10px"></i>
                        {{ ucfirst(str_replace('_', ' ', $member->role)) }}
                    </span>
                    @if(!empty($member->additional_roles))
                        @foreach($member->additional_roles as $ar)
                            <span class="tm-role-badge" style="--role-color:{{ $roleColors[$ar] ?? '#6B7280' }};opacity:0.85;font-size:10px;padding:2px 6px">
                                + {{ ucfirst(str_replace('_', ' ', $ar)) }}
                            </span>
                        @endforeach
                    @endif
                    @php
                        $memberClients = ($member->relationLoaded('clients') && $member->clients->isNotEmpty()) ? $member->clients : ($member->client ? collect([$member->client]) : collect());
                    @endphp
                    @if($memberClients->isNotEmpty())
                        @foreach($memberClients->take(2) as $mc)
                            <span class="tm-client-card-badge" title="Assigned Client: {{ $mc->name }}" style="display:inline-flex;align-items:center;gap:5px;background:rgba(14,165,233,0.08);color:#0284C7;border:1px solid rgba(14,165,233,0.2);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700">
                                @if($mc->logo)
                                    <img src="{{ asset('storage/' . $mc->logo) }}" style="width:12px;height:12px;border-radius:2px;object-fit:cover">
                                @else
                                    <span>{{ $mc->emoji ?: '🏢' }}</span>
                                @endif
                                <span>{{ $mc->name }}</span>
                            </span>
                        @endforeach
                        @if($memberClients->count() > 2)
                            <span class="tm-client-card-badge" style="background:rgba(14,165,233,0.1);color:#0284C7;padding:2px 6px;border-radius:6px;font-size:10px;font-weight:800" title="{{ $memberClients->pluck('name')->implode(', ') }}">
                                +{{ $memberClients->count() - 2 }} more
                            </span>
                        @endif
                    @endif
                    {{-- Efficiency bar --}}
                    <div class="tm-eff-bar-wrap" title="{{ $eff }}% completion rate" style="margin-left:auto">
                        <div class="tm-eff-bar" style="width:{{ $eff }}%;background:{{ $eff >= 70 ? 'var(--teal)' : ($eff >= 40 ? '#F59E0B' : 'var(--red)') }}"></div>
                    </div>
                    <span class="tm-eff-text" style="color:{{ $eff >= 70 ? 'var(--teal)' : ($eff >= 40 ? '#F59E0B' : 'var(--red)') }}">{{ $eff }}%</span>
                </div>
                <div class="tm-card-footer">
                    <div class="tm-task-stat">
                        <span class="tm-ts-icon"><i class="fas fa-layer-group"></i></span>
                        <span class="tm-ts-num">{{ $member->assigned_tasks_count }}</span>
                        <span class="tm-ts-label">Total</span>
                    </div>
                    <div class="tm-task-stat">
                        <span class="tm-ts-icon" style="color:var(--primary)"><i class="fas fa-spinner"></i></span>
                        <span class="tm-ts-num" style="color:var(--primary)">{{ $member->active_tasks_count }}</span>
                        <span class="tm-ts-label">Active</span>
                    </div>
                    <div class="tm-task-stat">
                        <span class="tm-ts-icon" style="color:var(--teal)"><i class="fas fa-check-circle"></i></span>
                        <span class="tm-ts-num" style="color:var(--teal)">{{ $member->completed_tasks_count }}</span>
                        <span class="tm-ts-label">Done</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="tm-empty">
                <div class="tm-empty-icon"><i class="fas fa-user-slash"></i></div>
                <div class="tm-empty-title">No team members found</div>
                <div class="tm-empty-desc">
                    @if(request('search') || request('role'))
                        Try adjusting your search or filters
                    @else
                        Get started by adding your first team member
                    @endif
                </div>
                @if(request('search') || request('role'))
                    <a href="{{ route('admin.team') }}" class="btn-sec" style="margin-top:12px;padding:10px 20px;font-weight:600">
                        <i class="fas fa-times" style="margin-right:6px"></i>Clear Filters
                    </a>
                @else
                    <button onclick="openAddModal()" class="btn-primary" style="margin-top:12px;padding:10px 20px;font-weight:600">
                        <i class="fas fa-user-plus" style="margin-right:6px"></i>Add First Member
                    </button>
                @endif
            </div>
        @endforelse
    </div>
    </div> <!-- /#team-ajax-area -->
</div>

{{-- ═══════════════════════════════════════════════════════════════
     ADD MEMBER MODAL — Premium stepped UI
     ═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="addMemberModal">
    <div class="modal-box tm-modal">
        <div class="tm-modal-header">
            <div>
                <h3 class="tm-modal-title"><i class="fas fa-user-plus" style="margin-right:8px;color:var(--primary)"></i>Add Team Member</h3>
                <p class="tm-modal-subtitle">Fill in the details to invite a new member</p>
            </div>
            <button class="tm-modal-close" onclick="closeModal('addMemberModal')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.team.store') }}" id="addMemberForm" data-no-loader="true" novalidate>
            @csrf
            <div class="tm-modal-body" style="overflow-y: auto; max-height: 60vh; padding-right: 8px;">
                {{-- Avatar Preview --}}
                <div class="tm-avatar-preview-section">
                    <div class="tm-avatar-preview" id="addAvatarPreview" style="background:#4F6DF0">
                        <span id="addAvatarInitials">?</span>
                    </div>
                    <div class="tm-avatar-preview-info">
                        <div class="tm-avatar-preview-name" id="addPreviewName">New Member</div>
                        <div class="tm-avatar-preview-role" id="addPreviewRole">Select a role</div>
                    </div>
                </div>

                {{-- Name & Email --}}
                <div class="tm-form-section">
                    <div class="tm-form-section-title"><i class="fas fa-id-card"></i> Identity</div>
                    <div class="tm-form-row">
                        <div class="tm-field">
                            <label>Full Name <span class="tm-req">*</span></label>
                            <div class="tm-input-wrap">
                                <i class="fas fa-user"></i>
                                <input type="text" name="name" id="addName" required maxlength="255" placeholder="e.g. John Doe" autocomplete="off">
                            </div>
                            <span class="tm-field-err" data-field="name"></span>
                        </div>
                        <div class="tm-field">
                            <label>Email Address <span class="tm-req">*</span></label>
                            <div class="tm-input-wrap">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" id="addEmail" required maxlength="255" placeholder="e.g. john@company.com" autocomplete="off">
                            </div>
                            <span class="tm-field-err" data-field="email"></span>
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="tm-form-section">
                    <div class="tm-form-section-title"><i class="fas fa-lock"></i> Security</div>
                    <div class="tm-field">
                        <label>Password <span class="tm-req">*</span></label>
                        <div class="tm-input-wrap tm-input-password">
                            <i class="fas fa-key"></i>
                            <input type="password" name="password" id="addPassword" required minlength="6" placeholder="Minimum 6 characters" autocomplete="new-password">
                            <button type="button" class="tm-pw-toggle" onclick="togglePassword(this)"><i class="fas fa-eye"></i></button>
                        </div>
                        <div class="tm-pw-strength" id="addPwStrength">
                            <div class="tm-pw-bar"><div class="tm-pw-bar-fill" id="addPwBar"></div></div>
                            <span class="tm-pw-text" id="addPwText"></span>
                        </div>
                        <span class="tm-field-err" data-field="password"></span>
                    </div>
                </div>

                {{-- Role & Color --}}
                <div class="tm-form-section">
                    <div class="tm-form-section-title"><i class="fas fa-user-tag"></i> Role & Appearance</div>
                    <div class="tm-field">
                        <label>Primary Role <span class="tm-req">*</span></label>
                        <div class="tm-role-grid" id="addRoleGrid">
                            @php
                                $allRoles = \App\Models\User::ALLOWED_ROLES;
                                $existingRoles = $roles->toArray();
                                $mergedRoles = array_values(array_unique(array_merge($allRoles, $existingRoles)));
                            @endphp
                            @foreach($mergedRoles as $r)
                                @php $rc2 = $roleColors[$r] ?? '#6B7280'; @endphp
                                <label class="tm-role-option" style="--rc:{{ $rc2 }}">
                                    <input type="radio" name="role" value="{{ $r }}">
                                    <div class="tm-role-option-inner">
                                        <i class="{{ $roleIcons[$r] ?? 'fas fa-user' }}"></i>
                                        <span>{{ ucfirst(str_replace('_', ' ', $r)) }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <div class="tm-role-custom">
                            <div class="tm-input-wrap">
                                <i class="fas fa-plus"></i>
                                <input type="text" id="addRoleInput" placeholder="Add custom role (e.g. manager)">
                            </div>
                            <button type="button" class="btn-sec tm-role-add-btn" onclick="addCustomRole('add')">Add Role</button>
                        </div>
                        <span class="tm-field-err" data-field="role"></span>
                    </div>

                    <div class="tm-field" style="margin-top:14px">
                        <label>Secondary Roles / Capabilities <span style="font-size:11px;font-weight:400;color:var(--text3)">(Allows user to appear in task assignment for multiple types)</span></label>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px" id="addAdditionalRolesContainer">
                            @foreach($mergedRoles as $r)
                                @php $rc2 = $roleColors[$r] ?? '#6B7280'; @endphp
                                <label style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:var(--card);border:1px solid var(--border);border-radius:10px;font-size:12px;cursor:pointer;font-weight:600;color:var(--text2)">
                                    <input type="checkbox" name="additional_roles[]" value="{{ $r }}" style="accent-color:{{ $rc2 }};width:14px;height:14px">
                                    <i class="{{ $roleIcons[$r] ?? 'fas fa-user' }}" style="font-size:10px;color:{{ $rc2 }}"></i>
                                    <span>{{ ucfirst(str_replace('_', ' ', $r)) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="tm-field" style="margin-top:14px">
                        <label>Assigned Client / Organization</label>
                        <div class="tm-input-wrap">
                            <i class="fas fa-building"></i>
                            <select name="client_id" id="addClientId" style="width:100%;padding:12px 14px 12px 38px;border:none;background:transparent;font-size:13px;font-weight:600;color:var(--text);outline:none;cursor:pointer">
                                <option value="">None (Internal Agency Staff)</option>
                                @if(isset($clients))
                                    @foreach($clients as $cl)
                                        <option value="{{ $cl->id }}">{{ $cl->emoji ?: '🏢' }} {{ $cl->name }} ({{ $cl->category ?: 'Client' }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <span class="tm-field-err" data-field="client_id"></span>
                    </div>

                    <div class="tm-field" style="margin-top:14px">
                        <label>Permissions</label>
                        <label style="display:flex;align-items:center;gap:10px;padding:12px 14px;border:1px solid var(--border);border-radius:12px;background:var(--card)">
                            <input type="hidden" name="can_manage_social_metrics" value="0">
                            <input type="checkbox" name="can_manage_social_metrics" value="1" style="width:16px;height:16px;accent-color:var(--primary)">
                            <span style="font-size:12px;font-weight:600;color:var(--text2)">Can manage social media analytics metrics</span>
                        </label>
                        <span class="tm-field-err" data-field="can_manage_social_metrics"></span>
                    </div>

                    <div class="tm-form-row" style="margin-top:14px">
                        <div class="tm-field" style="flex:1">
                            <label>Avatar Color</label>
                            <div class="tm-color-palette" id="addColorPalette">
                                @php $presetColors = ['#4F6DF0','#3B82F6','#8B5CF6','#EC4899','#EF4444','#F97316','#14B8A6','#10B981','#6366F1','#0EA5E9']; @endphp
                                @foreach($presetColors as $pc)
                                    <label class="tm-color-swatch" style="background:{{ $pc }}">
                                        <input type="radio" name="avatar_color" value="{{ $pc }}" {{ $loop->first ? 'checked' : '' }}>
                                        <i class="fas fa-check"></i>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tm-modal-footer">
                <button type="button" class="btn-sec" onclick="closeModal('addMemberModal')">Cancel</button>
                <button type="submit" class="btn-primary" id="addMemberSubmit">
                    <i class="fas fa-user-plus" style="margin-right:6px"></i>Add Member
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MEMBER DETAIL DRAWER — Slide-out panel with activity summary
     ═══════════════════════════════════════════════════════════════ --}}
<div class="tm-drawer-overlay" id="memberDrawerOverlay" onclick="closeMemberDrawer()"></div>
<div class="tm-drawer" id="memberDrawer">
    <div class="tm-drawer-header">
        <div class="tm-drawer-avatar" id="drawerAvatar">
            <span id="drawerInitials">?</span>
        </div>
        <div class="tm-drawer-info">
            <h3 id="drawerName">Loading...</h3>
            <span class="tm-drawer-role" id="drawerRole">-</span>
        </div>
        <button class="tm-drawer-close" onclick="closeMemberDrawer()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="tm-drawer-body">
        {{-- Member Quick Stats --}}
        <div class="tm-drawer-section">
            <div class="tm-drawer-section-title"><i class="fas fa-chart-bar"></i> Overview</div>
            <div class="tm-drawer-stats-row">
                <div class="tm-drawer-stat">
                    <div class="tm-drawer-stat-num" id="drawerTotal">0</div>
                    <div class="tm-drawer-stat-label">Total Tasks</div>
                </div>
                <div class="tm-drawer-stat">
                    <div class="tm-drawer-stat-num" id="drawerActive" style="color:var(--primary)">0</div>
                    <div class="tm-drawer-stat-label">Active</div>
                </div>
                <div class="tm-drawer-stat">
                    <div class="tm-drawer-stat-num" id="drawerCompleted" style="color:var(--teal)">0</div>
                    <div class="tm-drawer-stat-label">Completed</div>
                </div>
            </div>
        </div>

        {{-- Completion Rate Ring --}}
        <div class="tm-drawer-section">
            <div class="tm-drawer-section-title"><i class="fas fa-trophy"></i> Performance</div>
            <div class="tm-drawer-performance">
                <div class="tm-drawer-ring-wrap">
                    <svg class="tm-drawer-ring" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" stroke="var(--border)" stroke-width="8" fill="none"/>
                        <circle id="drawerProgressRing" cx="50" cy="50" r="42" stroke="var(--teal)" stroke-width="8" fill="none"
                                stroke-linecap="round" stroke-dasharray="264" stroke-dashoffset="264" transform="rotate(-90 50 50)"/>
                    </svg>
                    <div class="tm-drawer-ring-value" id="drawerCompletionRate">0%</div>
                </div>
                <div class="tm-drawer-perf-info">
                    <div class="tm-drawer-perf-label">Completion Rate</div>
                    <div class="tm-drawer-perf-sub" id="drawerAvgTime">
                        <i class="fas fa-hourglass-half"></i> Avg: -- days
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Breakdown --}}
        <div class="tm-drawer-section">
            <div class="tm-drawer-section-title"><i class="fas fa-tasks"></i> Task Breakdown</div>
            <div class="tm-drawer-breakdown" id="drawerBreakdown">
                <div class="tm-drawer-breakdown-empty">No task data</div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="tm-drawer-section">
            <div class="tm-drawer-section-title"><i class="fas fa-history"></i> Recent Activity</div>
            <div class="tm-drawer-activity" id="drawerActivity">
                <div class="tm-drawer-activity-empty">
                    <i class="fas fa-inbox"></i>
                    <span>No recent tasks</span>
                </div>
            </div>
        </div>

        {{-- Member Info --}}
        <div class="tm-drawer-section">
            <div class="tm-drawer-section-title"><i class="fas fa-info-circle"></i> Details</div>
            <div class="tm-drawer-detail-list">
                <div class="tm-drawer-detail">
                    <span class="tm-drawer-detail-icon"><i class="fas fa-envelope"></i></span>
                    <span class="tm-drawer-detail-text" id="drawerEmail">-</span>
                </div>
                <div class="tm-drawer-detail">
                    <span class="tm-drawer-detail-icon"><i class="fas fa-calendar-plus"></i></span>
                    <span class="tm-drawer-detail-text" id="drawerJoined">-</span>
                </div>
            </div>
        </div>
    </div>

    <div class="tm-drawer-footer">
        <button class="btn-sec" onclick="closeMemberDrawer()">Close</button>
        <button class="btn-primary" onclick="editFromDrawer()" id="drawerEditBtn">
            <i class="fas fa-pen"></i> Edit Member
        </button>
    </div>
</div>

@push('styles')<link rel="stylesheet" href="{{ asset('css/pages/admin-team.css') }}">@endpush

<script src="{{ asset('js/pages/admin-team.js') }}"></script>
@push('scripts')
@endpush

@endsection
