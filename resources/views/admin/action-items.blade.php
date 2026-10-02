@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title"><i class="fas fa-thumbtack" style="margin-right:8px;color:var(--primary)"></i> Action Items</div>
        <div class="page-subtitle">Quick instructions & follow-ups — full visibility into your team's progress</div>
    </div>
    <button class="btn-primary" onclick="openCreateActionModal()"><i class="fa-solid fa-plus"></i> New Instruction</button>
</div>

{{-- Stats --}}
<div class="ai-stats-grid">
    <div class="ai-stat-card" style="--asc:var(--blue)">
        <div class="ai-stat-icon"><i class="fa-solid fa-list-check"></i></div>
        <div class="ai-stat-info">
            <div class="ai-stat-val">{{ $totalActive }}</div>
            <div class="ai-stat-label">Active</div>
        </div>
    </div>
    <div class="ai-stat-card {{ $totalOverdue > 0 ? 'ai-stat-alert' : '' }}" style="--asc:var(--red)">
        <div class="ai-stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="ai-stat-info">
            <div class="ai-stat-val">{{ $totalOverdue }}</div>
            <div class="ai-stat-label">Overdue</div>
        </div>
    </div>
    <div class="ai-stat-card" style="--asc:var(--purple)">
        <div class="ai-stat-icon"><i class="fa-solid fa-spinner"></i></div>
        <div class="ai-stat-info">
            <div class="ai-stat-val">{{ $totalInProgress }}</div>
            <div class="ai-stat-label">In Progress</div>
        </div>
    </div>
    <div class="ai-stat-card" style="--asc:var(--teal)">
        <div class="ai-stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="ai-stat-info">
            <div class="ai-stat-val">{{ $completedToday }}</div>
            <div class="ai-stat-label">Done Today</div>
        </div>
    </div>
</div>

{{-- Overall Progress --}}
@if($totalItems > 0)
<div style="margin:-10px 0 18px; display:flex; align-items:center; gap:12px;">
    <span style="font-size:12px; font-weight:700; color:var(--text2);">Team Progress</span>
    <div class="ai-progress-bar" style="flex:1; height:8px;">
        <div class="ai-progress-fill" style="width:{{ round(($totalDone / $totalItems) * 100) }}%"></div>
    </div>
    <span style="font-size:12px; font-weight:800; color:var(--teal);">{{ round(($totalDone / $totalItems) * 100) }}%</span>
    <span style="font-size:11px; color:var(--text3);">{{ $totalDone }}/{{ $totalItems }}</span>
</div>
@endif

{{-- Recent Activity Feed --}}
@if($recentNotes->count() > 0)
<div class="admin-activity-feed">
    <div class="admin-activity-header" onclick="this.parentElement.classList.toggle('collapsed')">
        <span><i class="fa-solid fa-bolt" style="color:#ff9800"></i> Live Activity <span class="admin-activity-badge">{{ $recentNotes->count() }} update{{ $recentNotes->count() > 1 ? 's' : '' }} in 24h</span></span>
        <i class="fa-solid fa-chevron-down admin-activity-chevron"></i>
    </div>
    <div class="admin-activity-body">
        @foreach($recentNotes as $rn)
        <div class="admin-activity-row">
            <div class="admin-activity-avatar">{{ strtoupper(substr($rn->user->name ?? '?', 0, 1)) }}</div>
            <div class="admin-activity-content">
                <div class="admin-activity-text">
                    <strong>{{ $rn->user->name }}</strong> noted on <span class="admin-activity-link">{{ \Illuminate\Support\Str::limit($rn->actionItem->title, 40) }}</span>
                </div>
                <div class="admin-activity-note">"{{ \Illuminate\Support\Str::limit($rn->note, 120) }}"</div>
                <div class="admin-activity-time">{{ $rn->created_at->diffForHumans() }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Search + Client Filter + Assignee Toolbar --}}
<div class="ai-toolbar">
    <form method="GET" action="{{ route('admin.action-items') }}" class="ai-toolbar-form">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <div class="ai-search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search action items..." class="ai-search-input" autocomplete="off">
            @if($search)
                <a href="{{ route('admin.action-items', ['filter' => $filter, 'assignee' => $assigneeFilter, 'client' => $clientFilter]) }}" class="ai-search-clear" title="Clear"><i class="fa-solid fa-xmark"></i></a>
            @endif
        </div>
        <select name="assignee" class="ai-filter-select" onchange="this.form.submit()">
            <option value=""><i class="fas fa-users"></i> All Members</option>
            @foreach($strategists as $s)
                <option value="{{ $s->id }}" {{ $assigneeFilter == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <x-client-select :clients="$clients" name="client" value="{{ $clientFilter }}" placeholder="All Clients" class="ai-filter-select" onchange="this.form.submit()" />
    </form>
</div>

{{-- Tabs --}}
<div class="ai-filter-bar">
    <div class="ai-filter-tabs">
        @php $qp = array_filter(request()->only(['search', 'assignee', 'client'])); @endphp
        <a href="{{ route('admin.action-items', array_merge($qp, ['filter' => 'active'])) }}" class="ai-tab {{ $filter === 'active' ? 'active' : '' }}">
            <i class="fa-solid fa-list-check"></i> Active
            @if($totalActive > 0)<span class="ai-tab-count">{{ $totalActive }}</span>@endif
        </a>
        <a href="{{ route('admin.action-items', array_merge($qp, ['filter' => 'in_progress'])) }}" class="ai-tab {{ $filter === 'in_progress' ? 'active' : '' }}">
            <i class="fa-solid fa-spinner"></i> In Progress
            @if($totalInProgress > 0)<span class="ai-tab-count">{{ $totalInProgress }}</span>@endif
        </a>
        <a href="{{ route('admin.action-items', array_merge($qp, ['filter' => 'overdue'])) }}" class="ai-tab {{ $filter === 'overdue' ? 'active' : '' }}">
            <i class="fa-solid fa-clock"></i> Overdue
            @if($totalOverdue > 0)<span class="ai-tab-count ai-tab-count-red">{{ $totalOverdue }}</span>@endif
        </a>
        <a href="{{ route('admin.action-items', array_merge($qp, ['filter' => 'reminders'])) }}" class="ai-tab {{ $filter === 'reminders' ? 'active' : '' }}">
            <i class="fa-solid fa-bell"></i> Reminders
            @if($totalReminders > 0)<span class="ai-tab-count {{ $remindersDue > 0 ? 'ai-tab-count-red' : '' }}">{{ $totalReminders }}</span>@endif
        </a>
        <a href="{{ route('admin.action-items', array_merge($qp, ['filter' => 'done'])) }}" class="ai-tab {{ $filter === 'done' ? 'active' : '' }}">
            <i class="fa-solid fa-circle-check"></i> Done
            @if($totalDone > 0)<span class="ai-tab-count">{{ $totalDone }}</span>@endif
        </a>
        <a href="{{ route('admin.action-items', array_merge($qp, ['filter' => 'all'])) }}" class="ai-tab {{ $filter === 'all' ? 'active' : '' }}">
            <i class="fa-solid fa-layer-group"></i> All
            <span class="ai-tab-count">{{ $totalAll }}</span>
        </a>
    </div>
</div>

{{-- Sticky Notes Grid --}}
<div class="sn-board">
    @forelse($items as $index => $item)
        @php
            $colors = ['sn-yellow', 'sn-blue', 'sn-green', 'sn-pink', 'sn-purple', 'sn-orange'];
            $noteColor = $item->status === 'done' ? 'sn-done' : ($item->isOverdue() ? 'sn-red' : ($item->priority === 'urgent' ? 'sn-red' : $colors[$index % count($colors)]));
            $rotations = ['-2deg', '1.5deg', '-1deg', '2deg', '-1.5deg', '0.8deg', '-0.5deg', '1.8deg'];
            $rotation = $rotations[$index % count($rotations)];
            $hasRecentNote = $item->notes->where('created_at', '>=', now()->subHours(6))->count() > 0;
        @endphp
        <div class="sn-note {{ $noteColor }} {{ $item->isOverdue() ? 'sn-overdue-shake' : '' }}" style="--sn-rotate:{{ $rotation }}; animation-delay:{{ $index * 0.06 }}s">
            {{-- Pushpin --}}
            <div class="sn-pin"></div>

            {{-- Top badges --}}
            <div class="sn-top-row">
                @if($item->priority === 'urgent')
                    <span class="sn-badge sn-badge-urgent"><i class="fa-solid fa-bolt"></i> URGENT</span>
                @endif
                @if($item->status === 'in_progress')
                    <span class="sn-badge sn-badge-progress"><i class="fa-solid fa-spinner fa-spin"></i> Working</span>
                @endif
                @if($item->recurring)
                    <span class="sn-badge sn-badge-recurring"><i class="fa-solid fa-rotate"></i> {{ ucfirst($item->recurring) }}</span>
                @endif
                @if($item->status === 'done')
                    <span class="sn-badge sn-badge-done"><i class="fa-solid fa-check"></i> Done</span>
                @endif
                {{-- Reminder badge (admin can see strategist's reminder) --}}
                @if($item->isReminderDue())
                    <span class="sn-badge sn-badge-reminder-due"><i class="fa-solid fa-bell"></i> Reminder Due!</span>
                @elseif($item->hasActiveReminder())
                    <span class="sn-badge sn-badge-reminder"><i class="fa-regular fa-bell"></i> Reminder</span>
                @endif
                {{-- Recent activity indicator --}}
                @if($hasRecentNote)
                    <span class="sn-badge sn-badge-new-activity"><i class="fa-solid fa-bolt"></i> New Update</span>
                @endif
            </div>

            {{-- Title --}}
            <div class="sn-title">{{ $item->title }}</div>

            {{-- Description --}}
            @if($item->description)
                <div class="sn-desc">{{ \Illuminate\Support\Str::limit($item->description, 100) }}</div>
            @endif

            {{-- Client tag --}}
            @if($item->client)
                <div class="sn-client">{{ $item->client->emoji }} {{ $item->client->name }}</div>
            @endif

            {{-- Assigned to --}}
            <div class="sn-assigned"><i class="fa-regular fa-user"></i> {{ $item->assignee->name ?? 'Unassigned' }}</div>

            {{-- Due date --}}
            <div class="sn-due {{ $item->isOverdue() ? 'sn-due-overdue' : '' }}">
                <i class="fa-regular fa-clock"></i>
                @if($item->isOverdue())
                    Overdue {{ $item->due_at->diffForHumans() }}
                @elseif($item->status === 'done')
                    Done {{ $item->completed_at?->diffForHumans() }}
                @else
                    Due {{ $item->due_at->format('d M, h:i A') }}
                @endif
            </div>

            {{-- Reminder info (admin can see what reminder the strategist set) --}}
            @if($item->hasActiveReminder())
                <div class="sn-reminder-strip {{ $item->isReminderDue() ? 'sn-reminder-due' : '' }}">
                    <div class="sn-reminder-icon"><i class="fa-solid fa-bell{{ $item->isReminderDue() ? ' fa-shake' : '' }}"></i></div>
                    <div class="sn-reminder-info">
                        <div class="sn-reminder-reason">{{ $item->reminder_note }}</div>
                        <div class="sn-reminder-time">
                            @if($item->isReminderDue())
                                <i class="fas fa-circle" style="font-size:8px;color:var(--red)"></i> Due {{ $item->reminder_at->diffForHumans() }}
                            @else
                                <i class="fas fa-clock" style="font-size:10px;opacity:0.5"></i> {{ $item->reminder_at->format('d M, h:i A') }} ({{ $item->reminder_at->diffForHumans() }})
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Strategist's progress notes (admin can now see!) --}}
            @if($item->notes->count() > 0)
                <div class="sn-notes-list">
                    <div style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;opacity:.45;margin-bottom:2px"><i class="fa-solid fa-comments"></i> Team Updates</div>
                    @foreach($item->notes->take(3) as $note)
                        <div class="sn-note-entry {{ $note->created_at >= now()->subHours(6) ? 'sn-note-new' : '' }}">
                            <span class="sn-note-text"><strong style="opacity:.7">{{ $note->user->name }}:</strong> {{ \Illuminate\Support\Str::limit($note->note, 55) }}</span>
                            <span class="sn-note-meta">{{ $note->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                    @if($item->notes->count() > 3)
                        <div class="sn-note-more">+{{ $item->notes->count() - 3 }} more note{{ $item->notes->count() - 3 > 1 ? 's' : '' }}</div>
                    @endif
                </div>
            @endif

            {{-- Completion note (done items) --}}
            @if($item->status === 'done' && $item->completion_note)
                <div class="sn-completion">
                    <i class="fa-solid fa-quote-left" style="opacity:.3;font-size:10px"></i>
                    {{ \Illuminate\Support\Str::limit($item->completion_note, 80) }}
                </div>
                @if($item->follow_up_date)
                    <div class="sn-followup"><i class="fa-solid fa-calendar-check"></i> Follow-up: {{ $item->follow_up_date->format('d M') }}</div>
                @endif
            @endif

            {{-- Parent link --}}
            @if($item->parent)
                <div class="sn-parent"><i class="fa-solid fa-link"></i> Follow-up of: {{ \Illuminate\Support\Str::limit($item->parent->title, 35) }}</div>
            @endif

            {{-- Action buttons --}}
            @if($item->status !== 'done')
                <div class="sn-actions">
                    <button type="button" class="sn-action-btn sn-act-edit" title="Edit" onclick="openEditModal({{ json_encode([
                        'id' => $item->id,
                        'title' => $item->title,
                        'description' => $item->description,
                        'client_id' => $item->client_id,
                        'assigned_to' => $item->assigned_to,
                        'due_at' => $item->due_at->format('Y-m-d\TH:i'),
                        'priority' => $item->priority,
                        'recurring' => $item->recurring,
                    ]) }})"><i class="fa-solid fa-pen-to-square"></i></button>
                    <form method="POST" action="{{ route('admin.action-items.destroy', $item) }}" onsubmit="return confirm('Delete this action item?')" style="margin-left:auto">
                        @csrf @method('DELETE')
                        <button type="submit" class="sn-action-btn sn-act-delete" title="Delete"><i class="fa-regular fa-trash-can"></i></button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="sn-empty">
            @if($filter === 'done')
                <div class="sn-empty-icon"><i class="fas fa-trophy" style="font-size:24px;color:var(--teal)"></i></div>
                <div class="sn-empty-title">No completed items yet</div>
                <div class="sn-empty-text">Items will appear here once the strategist completes them</div>
            @elseif($filter === 'overdue')
                <div class="sn-empty-icon"><i class="fas fa-check-circle" style="font-size:24px;color:var(--teal)"></i></div>
                <div class="sn-empty-title">Nothing overdue!</div>
                <div class="sn-empty-text">All action items are on track</div>
            @elseif($filter === 'in_progress')
                <div class="sn-empty-icon"><i class="fas fa-moon" style="font-size:24px;color:var(--text3)"></i></div>
                <div class="sn-empty-title">Nothing in progress</div>
                <div class="sn-empty-text">No team member is currently working on any item</div>
            @elseif($filter === 'reminders')
                <div class="sn-empty-icon"><i class="fas fa-bell" style="font-size:24px;color:var(--yellow)"></i></div>
                <div class="sn-empty-title">No reminders set</div>
                <div class="sn-empty-text">Strategists can set reminders for callbacks, follow-ups, etc.</div>
            @elseif($search)
                <div class="sn-empty-icon"><i class="fas fa-search" style="font-size:24px;color:var(--text3)"></i></div>
                <div class="sn-empty-title">No results for "{{ $search }}"</div>
                <div class="sn-empty-text">Try a different search term</div>
            @else
                <div class="sn-empty-icon"><i class="fas fa-inbox" style="font-size:24px;color:var(--teal)"></i></div>
                <div class="sn-empty-title">No action items</div>
                <div class="sn-empty-text">Create a new instruction to assign tasks to your team</div>
                <button class="btn-primary" style="margin-top:12px" onclick="openCreateActionModal()"><i class="fa-solid fa-plus"></i> New Instruction</button>
            @endif
        </div>
    @endforelse
</div>

@if($items->hasPages())
    <div style="padding:16px 0;text-align:center">{{ $items->appends(request()->query())->links('vendor.pagination.custom') }}</div>
@endif

{{-- Create Modal --}}
<div class="modal-overlay" id="createActionModal">
    <div class="modal" style="max-width:540px">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-thumbtack" style="color:var(--primary);margin-right:6px"></i> New Instruction</div>
            <div class="modal-close" onclick="closeModal('createActionModal')"><i class="fas fa-times"></i></div>
        </div>
        <form method="POST" action="{{ route('admin.action-items.store') }}" class="ai-form" id="createActionForm">
            @csrf
            <div class="ai-form-group">
                <label><i class="fa-solid fa-pen"></i> What needs to be done? *</label>
                <input type="text" name="title" required placeholder="e.g. Follow up with Nike for brand guidelines" class="ai-input" maxlength="255">
                <span class="ai-field-feedback" data-field="title"></span>
            </div>
            <div class="ai-form-group">
                <label><i class="fa-regular fa-file-lines"></i> Details (optional)</label>
                <textarea name="description" rows="2" placeholder="Any extra context or instructions..." class="ai-input" maxlength="1000"></textarea>
                <span class="ai-field-feedback" data-field="description"></span>
            </div>
            <div class="ai-form-row">
                <div class="ai-form-group">
                    <label><i class="fa-regular fa-user"></i> Assign to *</label>
                    <select name="assigned_to" required class="ai-input">
                        <option value="">Select member...</option>
                        @foreach($strategists as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <span class="ai-field-feedback" data-field="assigned_to"></span>
                </div>
                <div class="ai-form-group">
                    <label><i class="fa-regular fa-building"></i> Client (optional)</label>
                    <x-client-select :clients="$clients" name="client_id" class="ai-input" placeholder="No client" />
                    <span class="ai-field-feedback" data-field="client_id"></span>
                </div>
            </div>
            <div class="ai-form-row">
                <div class="ai-form-group">
                    <label><i class="fa-regular fa-clock"></i> Due date & time *</label>
                    <input type="datetime-local" name="due_at" required class="ai-input" min="{{ now()->format('Y-m-d\TH:i') }}">
                    <span class="ai-field-feedback" data-field="due_at"></span>
                </div>
                <div class="ai-form-group">
                    <label><i class="fa-solid fa-flag"></i> Priority *</label>
                    <select name="priority" required class="ai-input">
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    <span class="ai-field-feedback" data-field="priority"></span>
                </div>
            </div>
            <div class="ai-form-group">
                <label><i class="fa-solid fa-rotate"></i> Recurring?</label>
                <select name="recurring" class="ai-input">
                    <option value="">One-time only</option>
                    <option value="daily">Daily — repeats every day</option>
                    <option value="weekly">Weekly — repeats every week</option>
                    <option value="monthly">Monthly — repeats every month</option>
                </select>
                <span class="ai-field-feedback" data-field="recurring"></span>
            </div>
            <div class="ai-form-actions">
                <button type="button" class="btn-sec" onclick="closeModal('createActionModal')">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-paper-plane"></i> Create Instruction</button>
            </div>
        </form>
    </div>
</div>

{{-- #4 Edit Modal --}}
<div class="modal-overlay" id="editActionModal">
    <div class="modal" style="max-width:540px">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-pen-to-square" style="color:var(--primary);margin-right:6px"></i> Edit Instruction</div>
            <div class="modal-close" onclick="closeModal('editActionModal')"><i class="fas fa-times"></i></div>
        </div>
        <form method="POST" id="editActionForm" class="ai-form">
            @csrf @method('PUT')
            <div class="ai-form-group">
                <label><i class="fa-solid fa-pen"></i> What needs to be done? *</label>
                <input type="text" name="title" id="editTitle" required class="ai-input" maxlength="255">
                <span class="ai-field-feedback" data-field="title"></span>
            </div>
            <div class="ai-form-group">
                <label><i class="fa-regular fa-file-lines"></i> Details (optional)</label>
                <textarea name="description" id="editDescription" rows="2" class="ai-input" maxlength="1000"></textarea>
                <span class="ai-field-feedback" data-field="description"></span>
            </div>
            <div class="ai-form-row">
                <div class="ai-form-group">
                    <label><i class="fa-regular fa-user"></i> Assign to * (#5 Reassign)</label>
                    <select name="assigned_to" id="editAssignedTo" required class="ai-input">
                        @foreach($strategists as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <span class="ai-field-feedback" data-field="assigned_to"></span>
                </div>
                <div class="ai-form-group">
                    <label><i class="fa-regular fa-building"></i> Client (optional)</label>
                    <x-client-select :clients="$clients" name="client_id" id="editClientId" class="ai-input" placeholder="No client" />
                    <span class="ai-field-feedback" data-field="client_id"></span>
                </div>
            </div>
            <div class="ai-form-row">
                <div class="ai-form-group">
                    <label><i class="fa-regular fa-clock"></i> Due date & time *</label>
                    <input type="datetime-local" name="due_at" id="editDueAt" required class="ai-input">
                    <span class="ai-field-feedback" data-field="due_at"></span>
                </div>
                <div class="ai-form-group">
                    <label><i class="fa-solid fa-flag"></i> Priority *</label>
                    <select name="priority" id="editPriority" required class="ai-input">
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    <span class="ai-field-feedback" data-field="priority"></span>
                </div>
            </div>
            <div class="ai-form-group">
                <label><i class="fa-solid fa-rotate"></i> Recurring?</label>
                <select name="recurring" id="editRecurring" class="ai-input">
                    <option value="">One-time only</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
                <span class="ai-field-feedback" data-field="recurring"></span>
            </div>
            <div class="ai-form-actions">
                <button type="button" class="btn-sec" onclick="closeModal('editActionModal')">Cancel</button>
                <button type="submit" class="btn-primary"><i class="fa-solid fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
.ai-field-feedback {
    display: none;
    margin-top: 5px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.35;
    color: var(--red);
}

.ai-field-feedback.is-success {
    color: #16a34a;
}

.ai-field-feedback.is-error {
    color: var(--red);
}
</style>
@endpush

@push('scripts')
<script>
const createActionForm = document.getElementById('createActionForm');
const editActionForm = document.getElementById('editActionForm');

function clearActionValidationUI(form) {
    if (!form) return;
    form.querySelectorAll('.ai-input').forEach((field) => {
        field.style.borderColor = '';
        field.style.boxShadow = '';
    });
    form.querySelectorAll('.ai-field-feedback').forEach((el) => {
        el.style.display = 'none';
        el.textContent = '';
        el.classList.remove('is-success', 'is-error');
    });
}

function setActionFieldVisual(form, fieldName, state) {
    const field = form.querySelector('[name="' + fieldName + '"]');
    if (!field) return;

    if (state === 'error') {
        field.style.borderColor = 'var(--red)';
        field.style.boxShadow = '0 0 0 2px rgba(239,68,68,0.12)';
        return;
    }

    if (state === 'success') {
        field.style.borderColor = '#16a34a';
        field.style.boxShadow = '0 0 0 2px rgba(22,163,74,0.12)';
        return;
    }

    field.style.borderColor = '';
    field.style.boxShadow = '';
}

function setActionFeedback(form, fieldName, message, state) {
    const feedback = form.querySelector('.ai-field-feedback[data-field="' + fieldName + '"]');
    if (!feedback) return;

    feedback.style.display = 'block';
    feedback.textContent = state === 'success' ? ('✓ ' + message) : message;
    feedback.classList.remove('is-success', 'is-error');
    feedback.classList.add(state === 'success' ? 'is-success' : 'is-error');
}

function clearActionFeedback(form, fieldName) {
    const feedback = form.querySelector('.ai-field-feedback[data-field="' + fieldName + '"]');
    if (!feedback) return;

    feedback.style.display = 'none';
    feedback.textContent = '';
    feedback.classList.remove('is-success', 'is-error');
}

function validateActionField(form, fieldName, showSuccess = true, live = false) {
    const field = form.querySelector('[name="' + fieldName + '"]');
    if (!field) return true;

    const value = typeof field.value === 'string' ? field.value.trim() : '';
    const fail = (msg) => {
        setActionFieldVisual(form, fieldName, 'error');
        setActionFeedback(form, fieldName, msg, 'error');
        return false;
    };
    const pass = (msg) => {
        setActionFieldVisual(form, fieldName, 'success');
        if (showSuccess) setActionFeedback(form, fieldName, msg, 'success');
        else clearActionFeedback(form, fieldName);
        return true;
    };
    const clear = () => {
        setActionFieldVisual(form, fieldName, null);
        clearActionFeedback(form, fieldName);
        return true;
    };

    if (fieldName === 'title') {
        if (!value) return live ? clear() : fail('Title is required.');
        if (value.length > 255) return fail('Title must be 255 characters or fewer.');
        return pass('Title looks good.');
    }

    if (fieldName === 'description') {
        if (!value) return clear();
        if (value.length > 1000) return fail('Details must be 1000 characters or fewer.');
        return pass('Details length is valid.');
    }

    if (fieldName === 'assigned_to') {
        if (!value) return live ? clear() : fail('Assignee is required.');
        return pass('Assignee selected.');
    }

    if (fieldName === 'client_id') {
        if (!value) return clear();
        if (!/^\d+$/.test(value)) return fail('Client selection is invalid.');
        return pass('Client selected.');
    }

    if (fieldName === 'due_at') {
        if (!value) return live ? clear() : fail('Due date and time is required.');
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return fail('Please enter a valid due date and time.');
        return pass('Due date and time looks valid.');
    }

    if (fieldName === 'priority') {
        if (!['normal', 'urgent'].includes(value)) return live ? clear() : fail('Please select a valid priority.');
        return pass('Priority selected.');
    }

    if (fieldName === 'recurring') {
        if (!value) return clear();
        if (!['daily', 'weekly', 'monthly'].includes(value)) return fail('Recurring value is invalid.');
        return pass('Recurring rule selected.');
    }

    return true;
}

function validateActionForm(form, showSuccess = true) {
    const fields = ['title', 'description', 'assigned_to', 'client_id', 'due_at', 'priority', 'recurring'];
    let valid = true;

    fields.forEach((fieldName) => {
        if (!validateActionField(form, fieldName, showSuccess, false)) valid = false;
    });

    return valid;
}

function bindActionFormValidation(form) {
    if (!form) return;

    form.addEventListener('submit', function(e) {
        if (!validateActionForm(form, true)) {
            e.preventDefault();
        }
    });

    ['title', 'description', 'due_at'].forEach((fieldName) => {
        const field = form.querySelector('[name="' + fieldName + '"]');
        if (!field) return;
        field.addEventListener('input', () => validateActionField(form, fieldName, false, true));
        field.addEventListener('blur', () => validateActionField(form, fieldName, true, false));
    });

    ['assigned_to', 'client_id', 'priority', 'recurring'].forEach((fieldName) => {
        const field = form.querySelector('[name="' + fieldName + '"]');
        if (!field) return;
        field.addEventListener('change', () => validateActionField(form, fieldName, true, false));
    });
}

bindActionFormValidation(createActionForm);
bindActionFormValidation(editActionForm);

function openCreateActionModal() {
    if (createActionForm) {
        createActionForm.reset();
        clearActionValidationUI(createActionForm);
        const dueField = createActionForm.querySelector('[name="due_at"]');
        if (dueField) dueField.min = '{{ now()->format('Y-m-d\\TH:i') }}';
    }
    openModal('createActionModal');
}

function openEditModal(item) {
    clearActionValidationUI(editActionForm);
    document.getElementById('editActionForm').action = '/admin/action-items/' + item.id;
    document.getElementById('editTitle').value = item.title;
    document.getElementById('editDescription').value = item.description || '';
    document.getElementById('editAssignedTo').value = item.assigned_to;
    document.getElementById('editClientId').value = item.client_id || '';
    document.getElementById('editDueAt').value = item.due_at;
    document.getElementById('editPriority').value = item.priority;
    document.getElementById('editRecurring').value = item.recurring || '';
    openModal('editActionModal');
}
</script>
@endpush
