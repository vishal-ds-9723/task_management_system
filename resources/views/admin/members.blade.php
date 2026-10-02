@extends('layouts.app')

@section('title', 'Members - Admin')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title"><i class="fas fa-user" style="margin-right:8px;color:var(--primary)"></i> Members</div>
        <div class="page-subtitle">Manage team members, freelancers & contractors — {{ $stats['total'] }} total</div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <button class="btn-primary" onclick="openAddMemberModal()"><i class="fas fa-plus" style="margin-right:5px"></i> Add Member</button>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div class="flash-success" id="flashMsg">
    <i class="fas fa-check-circle"></i>
    <div>
        @if(strpos(session('success'), 'Password:') !== false)
            <div style="font-weight:600;margin-bottom:6px">Member Added Successfully</div>
            <div style="font-size:12px;line-height:1.5;white-space:pre-line;font-family:monospace" id="credentialsMsg">{{ session('success') }}</div>
            <button onclick="copyCredentials()" style="margin-top:8px;padding:4px 8px;background:rgba(16,185,129,.2);border:1px solid rgba(16,185,129,.3);border-radius:4px;cursor:pointer;font-size:12px;color:#059669">
                <i class="fas fa-clipboard"></i> Copy Credentials
            </button>
        @else
            {{ session('success') }}
        @endif
    </div>
    <span onclick="hideFlash()">&times;</span>
</div>
@endif

@if(session('error'))
<div class="flash-error" onclick="this.remove()">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    <span style="margin-left:auto;opacity:.6;cursor:pointer">&times;</span>
</div>
@endif

{{-- Stats --}}
<div class="stats-row" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap">
    @foreach($stats as $key => $value)
        <div class="stat-card">
            <div class="stat-icon">{{ $statsIcons[$key] ?? '👤' }}</div>
            <div class="stat-numbers">{{ $value }}</div>
            <div class="stat-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</div>
        </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="filter-bar" style="background:var(--card);border-radius:12px;padding:16px;margin-bottom:20px">
    <form id="filterForm" method="GET">
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
            <div style="position:relative;flex:1;min-width:250px">
                <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px;z-index:2"></i>
                <input name="search" value="{{ request('search') }}" placeholder="Search members..." style="padding:10px 12px 10px 36px;border:1px solid var(--border);border-radius:8px;width:100%;background:var(--bg-light)">
            </div>
            <select name="role">
                <option value="">All Roles</option>
                @foreach($professions as $role)
                    <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="on_leave" {{ request('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
            </select>
            <button type="submit" class="btn-primary" style="padding:10px 20px;font-weight:600">Apply Filters</button>
            @if(request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('admin.members') }}" class="btn-secondary" style="padding:10px 20px">Clear</a>
            @endif
        </div>
    </form>
</div>

{{-- Members Grid --}}
<div class="grid-container">
    @forelse($members as $member)
        <div class="member-card">
            <div class="member-header">
                <div class="avatar" style="background: {{ $member->avatar_color ?? '#3b82f6' }}">
                    {{ strtoupper(substr($member->name, 0, 1)) }}
                </div>
                <div class="member-info">
                    <h3>{{ $member->name }}</h3>
                    <p>{{ $member->email }}</p>
                </div>
                <div class="actions">
                    <button onclick="editMember({{ $member->id }})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.members.destroy', $member) }}" style="display:inline" onsubmit="return confirm('Delete {{ $member->name }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Delete" style="color: #ef4444">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="member-details">
                <div class="role-badge" style="background: {{ $roleColors[$member->role] ?? '#6b7280' }};color:white">
                    {{ ucfirst(str_replace('_', ' ', $member->role)) }}
                </div>
                <div class="status-badge" style="background: {{ $statusColors[$member->status] ?? '#6b7280' }};color:white">
                    {{ ucfirst(str_replace('_', ' ', $member->status)) }}
                </div>
                @if($member->phone)
                    <p><i class="fas fa-phone"></i> {{ $member->phone }}</p>
                @endif
                @if($member->bio)
                    <p class="bio">{{ $member->bio }}</p>
                @endif
            </div>
            <div class="stats">
                <div class="stat">
                    <div class="stat-number">{{ $member->tasks_count }}</div>
                    <div class="stat-label">Total Tasks</div>
                </div>
                <div class="stat">
                    <div class="stat-number active-tasks">{{ $member->active_tasks_count }}</div>
                    <div class="stat-label">Active</div>
                </div>
                <div class="stat">
                    <div class="stat-number completed-tasks">{{ $member->completed_tasks_count }}</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <i class="fas fa-users-slash" style="font-size:64px;color:var(--text-muted);opacity:0.5;margin-bottom:16px"></i>
            <h3>No Members</h3>
            <p>Add your first team member to get started</p>
            <button class="btn-primary" onclick="openAddMemberModal()" style="margin-top:16px">
                <i class="fas fa-plus"></i> Add Member
            </button>
        </div>
    @endforelse
</div>

{{-- Add Member Modal --}}
<div class="modal" id="addMemberModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Member</h2>
            <button class="close-btn" onclick="closeModal('addMemberModal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.members.store') }}" id="addMemberForm">
            @csrf
            <div class="form-grid">
                <div class="form-group">
                    <label>Name <span style="color:red">*</span></label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email <span style="color:red">*</span></label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Role <span style="color:red">*</span></label>
                    <select name="role" required>
                        @foreach($professions as $role)
                            <option value="{{ $role }}">{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Status <span style="color:red">*</span></label>
                    <select name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="on_leave">On Leave</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone">
                </div>
                <div class="form-group">
                    <label>Avatar Color</label>
                    <input type="color" name="avatar_color" value="#3b82f6">
                </div>
                <div class="form-group full">
                    <label>Bio (optional)</label>
                    <textarea name="bio" rows="3" placeholder="Short bio or notes..."></textarea>
                </div>
                <div class="form-group full">
                    <label>Password <span style="color:red">*</span></label>
                    <input type="password" name="password" required>
                    <div style="font-size:12px;color:#6b7280;margin-top:4px">
                        <input type="checkbox" id="showPassword" style="margin-right:5px">
                        <label for="showPassword">Show password</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addMemberModal')">Cancel</button>
                <button type="submit" class="btn-primary">Add Member</button>
            </div>
        </form>
    </div>
</div>

{{-- Styles --}}
<style>
:root {
    --primary: #3b82f6;
    --primary-dim: rgba(59,130,246,0.1);
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --text: #1e293b;
    --text-muted: #64748b;
    --bg-light: #f8fafc;
    --border: #e2e8f0;
    --card: #ffffff;
    --shadow: 0 10px 25px rgba(0,0,0,0.1);
}

* { box-sizing: border-box; }

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal-overlay.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 16px;
    max-width: 600px;
    max-height: 90vh;
    width: 100%;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.modal-header {
    padding: 24px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: var(--text);
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--text-muted);
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items:center;
    justify-content:center;
    border-radius: 50%;
    transition: background 0.2s;
}

.close-btn:hover {
    background: var(--bg-light);
}

.form-grid {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-height: 60vh;
    overflow-y: auto;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group.full {
    grid-column: 1 / -1;
}

.form-group label {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
    background: var(--bg-light);
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.modal-footer {
    padding: 20px 24px;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: transparent;
    color: var(--text);
    border: 1px solid var(--border);
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background: var(--bg-light);
}

.stats-row {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.stat-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    min-width: 120px;
    flex: 1;
}

.stat-icon {
    font-size: 32px;
    margin-bottom: 8px;
}

.stat-numbers {
    font-size: 28px;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.member-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.member-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.member-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

.member-info h3 {
    margin: 0 0 4px 0;
    font-size: 18px;
    font-weight: 700;
    color: var(--text);
}

.member-info p {
    margin: 0;
    color: var(--text-muted);
    font-size: 14px;
}

.actions {
    margin-left: auto;
    display: flex;
    gap: 8px;
}

.actions button {
    background: none;
    border: none;
    padding: 8px;
    border-radius: 8px;
    cursor: pointer;
    color: var(--text-muted);
    transition: all 0.2s;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.actions button:hover {
    background: var(--bg-light);
    color: var(--primary);
}

.member-details {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.role-badge, .status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

.stats {
    display: flex;
    gap: 16px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}

.stat {
    flex: 1;
    text-align: center;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: var(--text-muted);
    font-weight: 600;
}

.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 40px;
    background: var(--bg-light);
    border-radius: 16px;
    border: 2px dashed var(--border);
}

.empty-state i {
    font-size: 64px;
    color: var(--text-muted);
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    font-size: 24px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
}

.empty-state p {
    color: var(--text-muted);
    font-size: 16px;
    margin-bottom: 24px;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-row {
        flex-direction: column;
    }
    
    .grid-container {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .member-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        text-align: left;
    }
    
    .actions {
        margin-left: 0;
        position: absolute;
        top: 16px;
        right: 16px;
    }
    
    .form-grid {
        padding: 20px;
    }
    
    .modal-content {
        margin: 10px;
        max-height: 95vh;
    }
}

/* Modal Animations */
.modal-overlay {
    animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.modal-content {
    animation: modalSlideUp 0.3s ease-out;
}

@keyframes modalSlideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Prevent body scroll when modal open */
.modal-overlay.show ~ body {
    overflow: hidden;
}
</style>

{{-- Scripts --}}
<script>
let currentEditingMember = null;

function openAddMemberModal() {
    document.getElementById('addMemberForm').reset();
    document.getElementById('addMemberModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
    document.body.style.overflow = 'auto';
}

function editMember(id) {
    // Fetch member data via AJAX
    fetch(`/admin/members/${id}/edit`)
        .then(response => response.json())
        .then(data => {
            currentEditingMember = data.member;
            // Populate form
            document.getElementById('editName').value = data.member.name;
            // ... populate other fields
            document.getElementById('editMemberModal').classList.add('show');
        });
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        closeModal(e.target.id);
    }
});

// Password toggle
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('showPassword');
    const password = document.querySelector('input[name="password"]');
    if (toggle && password) {
        toggle.addEventListener('change', function() {
            password.type = this.checked ? 'text' : 'password';
        });
    }
});

// Form submission
document.getElementById('addMemberForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              location.reload();
          }
      });
});
</script>
@endsection
