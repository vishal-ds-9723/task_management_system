@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <div class="page-title"><i class="fas fa-thumbtack" style="margin-right:8px;opacity:0.7"></i>My Action Items</div>
        <div class="page-subtitle">Instructions from Admin — stay on top of your tasks</div>
    </div>
    <div>
        <button class="btn-primary" onclick="openCreateModal()" style="display:flex;align-items:center;gap:6px"><i class="fa-solid fa-plus"></i> Create Sticky Note</button>
    </div>
</div>

{{-- Stats + Progress Bar (#10) --}}
<div class="ai-stats-grid">
    <div class="ai-stat-card" style="--asc:var(--blue)">
        <div class="ai-stat-icon"><i class="fa-solid fa-list-check"></i></div>
        <div class="ai-stat-info">
            <div class="ai-stat-val">{{ $totalActive }}</div>
            <div class="ai-stat-label">To Do</div>
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
    <span style="font-size:12px; font-weight:700; color:var(--text2);">Overall Progress</span>
    <div class="ai-progress-bar" style="flex:1; height:8px;">
        <div class="ai-progress-fill" style="width:{{ round(($totalDone / $totalItems) * 100) }}%"></div>
    </div>
    <span style="font-size:12px; font-weight:800; color:var(--teal);">{{ round(($totalDone / $totalItems) * 100) }}%</span>
    <span style="font-size:11px; color:var(--text3);">{{ $totalDone }}/{{ $totalItems }}</span>
</div>
@endif

{{-- #1 Search + #2 Client Filter + #6 Sort --}}
<div class="ai-toolbar">
    <form method="GET" action="{{ route('strategist.action-items') }}" class="ai-toolbar-form">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <div class="ai-search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search action items..." class="ai-search-input" autocomplete="off">
            @if($search)
                <a href="{{ route('strategist.action-items', ['filter' => $filter]) }}" class="ai-search-clear" title="Clear"><i class="fa-solid fa-xmark"></i></a>
            @endif
        </div>
        <x-client-select :clients="$clients" name="client" value="{{ $clientFilter }}" placeholder="All Clients" class="ai-filter-select" onchange="this.form.submit()" />
        <select name="sort" class="ai-filter-select" onchange="this.form.submit()">
            <option value="due_date" {{ $sort === 'due_date' ? 'selected' : '' }}>Sort by Due Date</option>
            <option value="priority" {{ $sort === 'priority' ? 'selected' : '' }}>Sort by Priority</option>
            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Sort by Newest</option>
        </select>
    </form>
</div>

{{-- Tabs with #7 In Progress + #8 Counts --}}
<div class="ai-filter-bar">
    <div class="ai-filter-tabs">
        <a href="{{ route('strategist.action-items', array_merge(request()->only(['search', 'client', 'sort']), ['filter' => 'active'])) }}" class="ai-tab {{ $filter === 'active' ? 'active' : '' }}">
            <i class="fa-solid fa-list-check"></i> Active
            @if($totalActive > 0)<span class="ai-tab-count">{{ $totalActive }}</span>@endif
        </a>
        <a href="{{ route('strategist.action-items', array_merge(request()->only(['search', 'client', 'sort']), ['filter' => 'in_progress'])) }}" class="ai-tab {{ $filter === 'in_progress' ? 'active' : '' }}">
            <i class="fa-solid fa-spinner"></i> In Progress
            @if($totalInProgress > 0)<span class="ai-tab-count">{{ $totalInProgress }}</span>@endif
        </a>
        <a href="{{ route('strategist.action-items', array_merge(request()->only(['search', 'client', 'sort']), ['filter' => 'overdue'])) }}" class="ai-tab {{ $filter === 'overdue' ? 'active' : '' }}">
            <i class="fa-solid fa-clock"></i> Overdue
            @if($totalOverdue > 0)<span class="ai-tab-count ai-tab-count-red">{{ $totalOverdue }}</span>@endif
        </a>
        <a href="{{ route('strategist.action-items', array_merge(request()->only(['search', 'client', 'sort']), ['filter' => 'reminders'])) }}" class="ai-tab {{ $filter === 'reminders' ? 'active' : '' }}">
            <i class="fa-solid fa-bell"></i> Reminders
            @if($totalReminders > 0)<span class="ai-tab-count {{ $remindersDue > 0 ? 'ai-tab-count-red' : '' }}">{{ $totalReminders }}</span>@endif
        </a>
        <a href="{{ route('strategist.action-items', array_merge(request()->only(['search', 'client', 'sort']), ['filter' => 'done'])) }}" class="ai-tab {{ $filter === 'done' ? 'active' : '' }}">
            <i class="fa-solid fa-circle-check"></i> Done
            @if($totalDone > 0)<span class="ai-tab-count">{{ $totalDone }}</span>@endif
        </a>
        <a href="{{ route('strategist.action-items', array_merge(request()->only(['search', 'client', 'sort']), ['filter' => 'all'])) }}" class="ai-tab {{ $filter === 'all' ? 'active' : '' }}">
            <i class="fa-solid fa-layer-group"></i> All
            <span class="ai-tab-count">{{ $totalAll }}</span>
        </a>
    </div>
</div>

{{-- Sticky Notes Grid with #3 Timeline Grouping --}}
<div class="sn-board">
    @php $lastGroup = null; @endphp
    @forelse($items as $index => $item)
        @php
            // #3 Timeline group headers
            if (in_array($filter, ['active', 'overdue', 'in_progress', 'all'])) {
                if ($item->status === 'done') {
                    $group = '<i class="fas fa-check-circle"></i> Completed';
                    $groupIcon = 'fa-solid fa-circle-check';
                } elseif ($item->isOverdue()) {
                    $group = '<i class="fas fa-exclamation-triangle"></i> Overdue';
                    $groupIcon = 'fa-solid fa-triangle-exclamation';
                } elseif ($item->due_at->isToday()) {
                    $group = '<i class="fas fa-calendar-day"></i> Due Today';
                    $groupIcon = 'fa-solid fa-calendar-day';
                } elseif ($item->due_at->isTomorrow()) {
                    $group = '<i class="fas fa-calendar"></i> Due Tomorrow';
                    $groupIcon = 'fa-solid fa-calendar';
                } elseif ($item->due_at->isBefore(now()->endOfWeek())) {
                    $group = '<i class="fas fa-calendar-week"></i> This Week';
                    $groupIcon = 'fa-solid fa-calendar-week';
                } else {
                    $group = '<i class="far fa-calendar"></i> Later';
                    $groupIcon = 'fa-regular fa-calendar';
                }
            } else {
                $group = null;
            }

            $colors = ['sn-yellow', 'sn-blue', 'sn-green', 'sn-pink', 'sn-purple', 'sn-orange'];
            $noteColor = $item->status === 'done' ? 'sn-done' : ($item->isOverdue() ? 'sn-red' : ($item->priority === 'urgent' ? 'sn-red' : $colors[$index % count($colors)]));
            $rotations = ['-2deg', '1.5deg', '-1deg', '2deg', '-1.5deg', '0.8deg', '-0.5deg', '1.8deg'];
            $rotation = $rotations[$index % count($rotations)];
        @endphp

        @if($group && $group !== $lastGroup)
            @php $lastGroup = $group; @endphp
            <div class="sn-group-header">{!! $group !!}</div>
        @endif

        <div class="sn-note {{ $noteColor }} {{ $item->isOverdue() ? 'sn-overdue-shake' : '' }}" style="--sn-rotate:{{ $rotation }}; animation-delay:{{ $index * 0.06 }}s" data-item-id="{{ $item->id }}" data-due="{{ $item->due_at->toIso8601String() }}" data-status="{{ $item->status }}">
            {{-- Pushpin --}}
            <div class="sn-pin"></div>

            {{-- Reminder attention-seeker icon (top-right) --}}
            @if($item->hasActiveReminder())
                <div class="sn-reminder-icon {{ $item->isReminderDue() ? 'sn-reminder-icon-due' : '' }}" onclick="openReminderDetailsModal({{ $item->id }}, '{{ addslashes($item->title) }}', '{{ $item->reminder_at->format('Y-m-d H:i') }}', '{{ addslashes($item->reminder_note ?? '') }}')" title="{{ $item->isReminderDue() ? 'Reminder Due!' : 'Reminder Set' }}">
                    <i class="fa-solid {{ $item->isReminderDue() ? 'fa-bell-slash fa-flip-horizontal' : 'fa-bell' }}"></i>
                </div>
            @endif

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
                {{-- Reminder due badge --}}
                @if($item->isReminderDue())
                    <span class="sn-badge sn-badge-reminder-due"><i class="fa-solid fa-bell"></i> Reminder!</span>
                @elseif($item->hasActiveReminder())
                    <span class="sn-badge sn-badge-reminder"><i class="fa-regular fa-bell"></i> {{ $item->reminder_at->format('d M, h:i A') }}</span>
                @endif
                {{-- Live countdown timer --}}
                @if($item->status !== 'done')
                    <div class="sn-timer {{ $item->isOverdue() ? 'sn-timer-overdue' : '' }}" data-timer-due="{{ $item->due_at->toIso8601String() }}">
                        <div class="sn-timer-icon">
                            <i class="fa-solid {{ $item->isOverdue() ? 'fa-triangle-exclamation' : 'fa-stopwatch' }}"></i>
                        </div>
                        <div class="sn-timer-display">
                            <span class="sn-timer-label">{{ $item->isOverdue() ? 'OVERDUE BY' : 'TIME LEFT' }}</span>
                            <span class="sn-timer-clock">00:00:00</span>
                        </div>
                    </div>
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

            {{-- From --}}
            <div class="sn-from"><i class="fa-regular fa-paper-plane"></i> {{ $item->creator->name }}</div>

            {{-- Reminder info strip --}}
            @if($item->hasActiveReminder())
                <div class="sn-reminder-strip {{ $item->isReminderDue() ? 'sn-reminder-due' : '' }}">
                    <div class="sn-reminder-icon"><i class="fa-solid fa-bell{{ $item->isReminderDue() ? ' fa-shake' : '' }}"></i></div>
                    <div class="sn-reminder-info">
                        <div class="sn-reminder-reason">{{ $item->reminder_note }}</div>
                        <div class="sn-reminder-time">
                            @if($item->isReminderDue())
                                <i class="fas fa-circle" style="color:var(--red);font-size:8px"></i> Due {{ $item->reminder_at->diffForHumans() }}
                            @else
                                <i class="fas fa-clock"></i> {{ $item->reminder_at->format('d M, h:i A') }} ({{ $item->reminder_at->diffForHumans() }})
                            @endif
                        </div>
                    </div>
                    <button type="button" class="sn-reminder-dismiss" title="Dismiss reminder" onclick="clearReminder({{ $item->id }})"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endif

            {{-- #11 Mid-progress notes --}}
            @if($item->notes->count() > 0)
                <div class="sn-notes-list">
                    @foreach($item->notes->take(2) as $note)
                        <div class="sn-note-entry">
                            <span class="sn-note-text">{{ \Illuminate\Support\Str::limit($note->note, 60) }}</span>
                            <span class="sn-note-meta">— {{ $note->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                    @if($item->notes->count() > 2)
                        <div class="sn-note-more">+{{ $item->notes->count() - 2 }} more</div>
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
                    @if($item->status === 'pending')
                        <button type="button" class="sn-action-btn sn-act-start" title="Start working" onclick="confirmStart({{ $item->id }}, '{{ addslashes($item->title) }}')"><i class="fa-solid fa-play"></i></button>
                    @endif
                    @if($item->created_by === Auth::id())
                        <button type="button" class="sn-action-btn sn-act-edit" title="Edit" onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->title) }}', '{{ addslashes($item->description ?? '') }}', '{{ $item->client_id }}', '{{ $item->priority }}', '{{ $item->due_at->format('Y-m-d\\TH:i') }}', '{{ $item->recurring ?? '' }}')"><i class="fa-solid fa-pen"></i></button>
                    @endif
                    <button type="button" class="sn-action-btn sn-act-note" title="Add note" onclick="openNoteModal({{ $item->id }}, '{{ addslashes($item->title) }}')"><i class="fa-regular fa-comment"></i></button>
                    <button type="button" class="sn-action-btn sn-act-reminder {{ $item->hasActiveReminder() ? 'sn-act-reminder-active' : '' }}" title="{{ $item->hasActiveReminder() ? 'Change reminder' : 'Set reminder' }}" onclick="openReminderModal({{ $item->id }}, '{{ addslashes($item->title) }}')"><i class="fa-solid fa-bell"></i></button>
                    <button type="button" class="sn-action-btn sn-act-done" title="Mark done" onclick="openDoneModal({{ $item->id }}, '{{ addslashes($item->title) }}', '{{ addslashes($item->description ?? '') }}')"><i class="fa-solid fa-check"></i></button>
                </div>
            @else
                {{-- #14 Reopen button --}}
                <div class="sn-actions">
                    <button type="button" class="sn-action-btn sn-act-reopen" title="Reopen" onclick="confirmReopen({{ $item->id }}, '{{ addslashes($item->title) }}')"><i class="fa-solid fa-rotate-left"></i></button>
                </div>
            @endif
        </div>
    @empty
        <div class="sn-empty">
            @if($filter === 'overdue')
                <div class="sn-empty-icon"><i class="fas fa-check-circle" style="font-size:24px;color:var(--teal)"></i></div>
                <div class="sn-empty-title">Nothing overdue!</div>
                <div class="sn-empty-text">Great job keeping up with everything</div>
            @elseif($filter === 'reminders')
                <div class="sn-empty-icon"><i class="fas fa-bell" style="font-size:24px;color:var(--yellow)"></i></div>
                <div class="sn-empty-title">No reminders set</div>
                <div class="sn-empty-text">Use the bell icon on any item to set a reminder for client callbacks or follow-ups</div>
            @elseif($filter === 'done')
                <div class="sn-empty-icon"><i class="fas fa-trophy" style="font-size:24px;color:var(--teal)"></i></div>
                <div class="sn-empty-title">No completed items yet</div>
                <div class="sn-empty-text">Complete action items and they'll appear here</div>
            @elseif($filter === 'in_progress')
                <div class="sn-empty-icon"><i class="fas fa-moon" style="font-size:24px;color:var(--text3)"></i></div>
                <div class="sn-empty-title">Nothing in progress</div>
                <div class="sn-empty-text">Start working on an item to see it here</div>
            @elseif($search)
                <div class="sn-empty-icon"><i class="fas fa-search" style="font-size:24px;color:var(--text3)"></i></div>
                <div class="sn-empty-title">No results for "{{ $search }}"</div>
                <div class="sn-empty-text">Try a different search term</div>
            @else
                <div class="sn-empty-icon"><i class="fas fa-inbox" style="font-size:24px;color:var(--teal)"></i></div>
                <div class="sn-empty-title">No action items right now</div>
                <div class="sn-empty-text">You're all caught up!</div>
            @endif
        </div>
    @endforelse
</div>

@if($items->hasPages())
    <div style="padding:16px 0;text-align:center">{{ $items->appends(request()->query())->links() }}</div>
@endif

{{-- Done Modal (#12 shows description) --}}
<div class="modal-overlay" id="doneModal">
    <div class="modal" style="max-width:500px">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-circle-check" style="color:var(--teal);margin-right:6px"></i> Mark as Done</div>
            <div class="modal-close" onclick="closeModal('doneModal')"><i class="fas fa-times"></i></div>
        </div>
        <form method="POST" id="doneForm" class="ai-form">
            @csrf @method('PATCH')
            <div class="ai-done-item-title" id="doneItemTitle"></div>
            {{-- #12 Description in done modal --}}
            <div class="ai-done-item-desc" id="doneItemDesc" style="display:none"></div>
            <div class="ai-form-group">
                <label><i class="fa-solid fa-pen"></i> What did you do? *</label>
                <textarea name="completion_note" id="doneCompletionNote" required rows="3" placeholder="e.g. Called them, they'll send files by Friday" class="ai-input" maxlength="500"></textarea>
            </div>
            <div class="ai-form-group">
                <label><i class="fa-solid fa-calendar-plus"></i> Need to follow up again?</label>
                <input type="date" name="follow_up_date" class="ai-input" min="{{ now()->format('Y-m-d') }}">
                <small style="color:var(--text3);font-size:11px">A new action item will be auto-created for follow-up</small>
            </div>
            <div class="ai-form-actions">
                <button type="button" class="btn-sec" onclick="closeModal('doneModal')">Cancel</button>
                <button type="submit" class="btn-primary" id="doneSubmitBtn"><i class="fa-solid fa-check"></i> Mark Done</button>
            </div>
        </form>
    </div>
</div>

{{-- #11 Add Note Modal --}}
<div class="modal-overlay" id="noteModal">
    <div class="modal" style="max-width:460px">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-regular fa-comment" style="color:var(--blue);margin-right:6px"></i> Add Progress Note</div>
            <div class="modal-close" onclick="closeModal('noteModal')"><i class="fas fa-times"></i></div>
        </div>
        <form id="noteForm" class="ai-form" onsubmit="submitNote(event)">
            @csrf
            <div class="ai-done-item-title" id="noteItemTitle"></div>
            <div class="ai-form-group">
                <label><i class="fa-solid fa-pen"></i> Your update *</label>
                <textarea name="note" id="noteText" required rows="3" placeholder="e.g. Called them, waiting for callback..." class="ai-input" maxlength="500"></textarea>
            </div>
            <div class="ai-form-actions">
                <button type="button" class="btn-sec" onclick="closeModal('noteModal')">Cancel</button>
                <button type="submit" class="btn-primary" id="noteSubmitBtn"><i class="fa-regular fa-comment"></i> Add Note</button>
            </div>
        </form>
    </div>
</div>

{{-- #13 Start Confirmation Modal --}}
<div class="modal-overlay" id="startModal">
    <div class="modal" style="max-width:420px">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-play" style="color:var(--blue);margin-right:6px"></i> Start Working</div>
            <div class="modal-close" onclick="closeModal('startModal')"><i class="fas fa-times"></i></div>
        </div>
        <div class="ai-form" style="padding:18px">
            <div class="ai-done-item-title" id="startItemTitle"></div>
            <p style="color:var(--text2);font-size:13px;margin:12px 0">This will mark the item as "In Progress". Continue?</p>
            <div class="ai-form-actions">
                <button type="button" class="btn-sec" onclick="closeModal('startModal')">Cancel</button>
                <button type="button" class="btn-primary" id="startConfirmBtn" onclick="doStart()"><i class="fa-solid fa-play"></i> Start Working</button>
            </div>
        </div>
    </div>
</div>

{{-- Set Reminder Modal --}}
<div class="modal-overlay" id="reminderModal">
    <div class="modal" style="max-width:500px">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-bell" style="color:#ff9800;margin-right:6px"></i> Set Reminder</div>
            <div class="modal-close" onclick="closeModal('reminderModal')"><i class="fas fa-times"></i></div>
        </div>
        <form id="reminderForm" class="ai-form" onsubmit="submitReminder(event)">
            @csrf
            <div class="ai-done-item-title" id="reminderItemTitle"></div>

            <div class="sn-reminder-quick-picks">
                <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:6px"><i class="fa-solid fa-clock-rotate-left"></i> Quick Pick:</div>
                <div class="sn-reminder-picks-row">
                    <button type="button" class="sn-pick-btn" onclick="setQuickReminder(1)">In 1 hour</button>
                    <button type="button" class="sn-pick-btn" onclick="setQuickReminder(2)">In 2 hours</button>
                    <button type="button" class="sn-pick-btn" onclick="setQuickReminder(3)">In 3 hours</button>
                    <button type="button" class="sn-pick-btn" onclick="setQuickReminder(0, 'tomorrow_morning')">Tomorrow 10 AM</button>
                    <button type="button" class="sn-pick-btn" onclick="setQuickReminder(0, 'tomorrow_afternoon')">Tomorrow 2 PM</button>
                </div>
            </div>

            <div class="ai-form-group">
                <label><i class="fa-solid fa-calendar-day"></i> Remind me at *</label>
                <input type="datetime-local" name="reminder_at" id="reminderAt" required class="ai-input" min="{{ now()->format('Y-m-d\TH:i') }}">
            </div>
            <div class="ai-form-group">
                <label><i class="fa-solid fa-pen"></i> Why? (so you remember the context) *</label>
                <textarea name="reminder_note" id="reminderNote" required rows="2" placeholder="e.g. Client said to call back at 3 PM&#10;e.g. Waiting for client to send files by afternoon&#10;e.g. Check if payment received" class="ai-input" maxlength="500"></textarea>
            </div>
            <div class="ai-form-actions">
                <button type="button" class="btn-sec" onclick="closeModal('reminderModal')">Cancel</button>
                <button type="submit" class="btn-primary" id="reminderSubmitBtn"><i class="fa-solid fa-bell"></i> Set Reminder</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Action Item Modal --}}
<div class="modal-overlay" id="editStickyModal">
    <div class="modal" style="max-width:520px">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-pen" style="color:var(--primary);margin-right:6px"></i> Edit Sticky Note</div>
            <div class="modal-close" onclick="closeModal('editStickyModal')">✕</div>
        </div>
        <form id="editForm" class="ai-form" onsubmit="submitEdit(event)">
            @csrf @method('PATCH')
            <div class="ai-form-group">
                <label><i class="fa-solid fa-thumbtack"></i> Title <span style="color:var(--red)">*</span></label>
                <input type="text" name="title" id="editTitle" required placeholder="e.g. Call client about proposal" class="ai-input" maxlength="255">
            </div>
            <div class="ai-form-group">
                <label><i class="fa-solid fa-align-left"></i> Description</label>
                <textarea name="description" id="editDesc" rows="3" placeholder="Add details or notes..." class="ai-input" maxlength="1000"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="ai-form-group">
                    <label><i class="fa-solid fa-building"></i> Client</label>
                    <x-client-select :clients="$allClients" name="client_id" id="editClient" class="ai-input" placeholder="No client" />
                </div>
                <div class="ai-form-group">
                    <label><i class="fa-solid fa-flag"></i> Priority</label>
                    <select name="priority" id="editPriority" class="ai-input">
                        <option value="normal">🟡 Normal</option>
                        <option value="urgent">🔴 Urgent</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="ai-form-group">
                    <label><i class="fa-solid fa-clock"></i> Due Date & Time <span style="color:var(--red)">*</span></label>
                    <input type="datetime-local" name="due_at" id="editDueAt" required class="ai-input" min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
                <div class="ai-form-group">
                    <label><i class="fa-solid fa-rotate"></i> Recurring</label>
                    <select name="recurring" id="editRecurring" class="ai-input">
                        <option value="">One-time</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
            </div>
            <div class="ai-form-actions">
                <button type="button" class="btn-sec" onclick="closeModal('editStickyModal')">Cancel</button>
                <button type="submit" class="btn-primary" id="editSubmitBtn"><i class="fa-solid fa-check"></i> Update</button>
            </div>
        </form>
    </div>
</div>

{{-- Reminder Details Modal --}}
<div class="modal-overlay" id="reminderDetailsModal">
    <div class="modal" style="max-width:500px">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-bell" style="color:#1565c0;margin-right:6px"></i> Reminder Details</div>
            <div class="modal-close" onclick="closeModal('reminderDetailsModal')"><i class="fas fa-times"></i></div>
        </div>
        <div class="ai-form" style="padding:18px">
            <div class="ai-done-item-title" id="reminderDetailsTitle"></div>

            <div style="margin-top:16px;padding:14px;background:rgba(59,130,246,.05);border-radius:8px;border-left:3px solid #1565c0">
                <div style="font-size:12px;color:var(--text3);margin-bottom:4px"><i class="fa-solid fa-clock"></i> Reminder Time:</div>
                <div style="font-size:15px;font-weight:600;color:var(--text1)" id="reminderDetailsTime"></div>
            </div>

            <div style="margin-top:12px;padding:14px;background:rgba(0,0,0,.03);border-radius:8px">
                <div style="font-size:12px;color:var(--text3);margin-bottom:6px"><i class="fa-solid fa-message"></i> Context:</div>
                <div style="font-size:13px;line-height:1.6;color:var(--text2);white-space:pre-wrap;word-break:break-word" id="reminderDetailsNote"></div>
            </div>

            <div class="ai-form-actions" style="margin-top:16px">
                <button type="button" class="btn-sec" onclick="closeModal('reminderDetailsModal')"><i class="fa-solid fa-times"></i> Close</button>
                <button type="button" class="btn-primary" onclick="openEditReminder()"><i class="fa-solid fa-pen"></i> Edit Reminder</button>
            </div>
        </div>
    </div>
</div>

{{-- Create Action Item Modal --}}
<div class="modal-overlay" id="createStickyModal">
    <div class="modal" style="max-width:520px">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-plus" style="color:var(--primary);margin-right:6px"></i> Create Sticky Note</div>
            <div class="modal-close" onclick="closeModal('createStickyModal')">✕</div>
        </div>
        <form id="createForm" class="ai-form" onsubmit="submitCreate(event)">
            @csrf
            <div class="ai-form-group">
                <label><i class="fa-solid fa-thumbtack"></i> Title <span style="color:var(--red)">*</span></label>
                <input type="text" name="title" id="createTitle" required placeholder="e.g. Call client about proposal" class="ai-input" maxlength="255">
            </div>
            <div class="ai-form-group">
                <label><i class="fa-solid fa-align-left"></i> Description</label>
                <textarea name="description" id="createDesc" rows="3" placeholder="Add details or notes..." class="ai-input" maxlength="1000"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="ai-form-group">
                    <label><i class="fa-solid fa-building"></i> Client</label>
                    <x-client-select :clients="$allClients" name="client_id" id="createClient" class="ai-input" placeholder="No client" />
                </div>
                <div class="ai-form-group">
                    <label><i class="fa-solid fa-flag"></i> Priority</label>
                    <select name="priority" id="createPriority" class="ai-input">
                        <option value="normal">🟡 Normal</option>
                        <option value="urgent">🔴 Urgent</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="ai-form-group">
                    <label><i class="fa-solid fa-clock"></i> Due Date & Time <span style="color:var(--red)">*</span></label>
                    <input type="datetime-local" name="due_at" id="createDueAt" required class="ai-input" min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
                <div class="ai-form-group">
                    <label><i class="fa-solid fa-rotate"></i> Recurring</label>
                    <select name="recurring" id="createRecurring" class="ai-input">
                        <option value="">One-time</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
            </div>

            <div class="sn-reminder-quick-picks" style="margin-top:4px">
                <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:6px"><i class="fa-solid fa-clock-rotate-left"></i> Quick Due:</div>
                <div class="sn-reminder-picks-row">
                    <button type="button" class="sn-pick-btn" onclick="setCreateQuickDue('today_evening')">Today EOD</button>
                    <button type="button" class="sn-pick-btn" onclick="setCreateQuickDue('tomorrow_morning')">Tomorrow 10 AM</button>
                    <button type="button" class="sn-pick-btn" onclick="setCreateQuickDue('tomorrow_evening')">Tomorrow EOD</button>
                    <button type="button" class="sn-pick-btn" onclick="setCreateQuickDue('next_week')">Next Monday</button>
                </div>
            </div>

            {{-- Reminder Section --}}
            <div style="margin-top:8px;padding:14px;background:rgba(255,152,0,.06);border-radius:10px;border:1px dashed rgba(255,152,0,.25)">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <label style="font-size:13px;font-weight:700;color:#ff9800;margin:0"><i class="fa-solid fa-bell"></i> Set Reminder (optional)</label>
                    <label style="font-size:11px;color:var(--text3);display:flex;align-items:center;gap:4px;cursor:pointer">
                        <input type="checkbox" id="createReminderToggle" onchange="toggleCreateReminder()" style="accent-color:#ff9800"> Enable
                    </label>
                </div>
                <div id="createReminderFields" style="display:none">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                        <div class="ai-form-group" style="margin-bottom:8px">
                            <label style="font-size:12px"><i class="fa-solid fa-calendar-day"></i> Remind at</label>
                            <input type="datetime-local" name="reminder_at" id="createReminderAt" class="ai-input" min="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                        <div class="ai-form-group" style="margin-bottom:8px">
                            <label style="font-size:12px">&nbsp;</label>
                            <div class="sn-reminder-picks-row" style="flex-wrap:wrap;gap:4px">
                                <button type="button" class="sn-pick-btn" style="font-size:10px;padding:4px 8px" onclick="setCreateQuickReminder(1)">+1hr</button>
                                <button type="button" class="sn-pick-btn" style="font-size:10px;padding:4px 8px" onclick="setCreateQuickReminder(2)">+2hr</button>
                                <button type="button" class="sn-pick-btn" style="font-size:10px;padding:4px 8px" onclick="setCreateQuickReminder(3)">+3hr</button>
                                <button type="button" class="sn-pick-btn" style="font-size:10px;padding:4px 8px" onclick="setCreateQuickReminder(0, 'tomorrow_morning')">Tom 10AM</button>
                                <button type="button" class="sn-pick-btn" style="font-size:10px;padding:4px 8px" onclick="setCreateQuickReminder(0, 'tomorrow_afternoon')">Tom 2PM</button>
                            </div>
                        </div>
                    </div>
                    <div class="ai-form-group" style="margin-bottom:0">
                        <label style="font-size:12px"><i class="fa-solid fa-pen"></i> Reminder note</label>
                        <textarea name="reminder_note" id="createReminderNote" rows="2" placeholder="e.g. Call client back at 3 PM&#10;e.g. Check if files received" class="ai-input" maxlength="500"></textarea>
                    </div>
                </div>
            </div>

            <div class="ai-form-actions">
                <button type="button" class="btn-sec" onclick="closeModal('createStickyModal')">Cancel</button>
                <button type="submit" class="btn-primary" id="createSubmitBtn"><i class="fa-solid fa-plus"></i> Create</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
let currentItemId = null;

// #12 Done modal with description
function openDoneModal(itemId, title, description) {
    currentItemId = itemId;
    document.getElementById('doneForm').action = '/strategist/action-items/' + itemId + '/done';
    document.getElementById('doneItemTitle').textContent = title;
    const descEl = document.getElementById('doneItemDesc');
    if (description && description.trim()) {
        descEl.textContent = description;
        descEl.style.display = 'block';
    } else {
        descEl.style.display = 'none';
    }
    document.getElementById('doneCompletionNote').value = '';
    openModal('doneModal');
}

// #11 Note modal
function openNoteModal(itemId, title) {
    currentItemId = itemId;
    document.getElementById('noteForm').dataset.itemId = itemId;
    document.getElementById('noteItemTitle').textContent = title;
    document.getElementById('noteText').value = '';
    openModal('noteModal');
}

// #11 Submit note via AJAX (#15)
function submitNote(e) {
    e.preventDefault();
    const itemId = document.getElementById('noteForm').dataset.itemId;
    const note = document.getElementById('noteText').value.trim();
    if (!note) return;

    const btn = document.getElementById('noteSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    fetch('/strategist/action-items/' + itemId + '/note', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: JSON.stringify({ note: note })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('noteModal');
            // Add note to the card
            const card = document.querySelector('[data-item-id="' + itemId + '"]');
            if (card) {
                let notesList = card.querySelector('.sn-notes-list');
                if (!notesList) {
                    notesList = document.createElement('div');
                    notesList.className = 'sn-notes-list';
                    const fromEl = card.querySelector('.sn-from');
                    if (fromEl) fromEl.after(notesList);
                }
                const entry = document.createElement('div');
                entry.className = 'sn-note-entry sn-note-new';
                entry.innerHTML = '<span class="sn-note-text">' + escapeHtml(data.note.note).substring(0, 60) + '</span><span class="sn-note-meta">— just now</span>';
                notesList.prepend(entry);
            }
            showToast('Note added!');
        }
    })
    .catch(() => showToast('Error adding note', true))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-regular fa-comment"></i> Add Note';
    });
}

// #13 Start confirmation
function confirmStart(itemId, title) {
    currentItemId = itemId;
    document.getElementById('startItemTitle').textContent = title;
    openModal('startModal');
}

// #15 AJAX start
function doStart() {
    const btn = document.getElementById('startConfirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Starting...';

    fetch('/strategist/action-items/' + currentItemId + '/start', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('startModal');
            showToast('Started!');
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(() => showToast('Error starting item', true))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-play"></i> Start Working';
    });
}

// #14 Reopen confirmation
function confirmReopen(itemId, title) {
    if (!confirm('Reopen "' + title + '"? This will move it back to pending.')) return;

    fetch('/strategist/action-items/' + itemId + '/reopen', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Reopened!');
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(() => showToast('Error reopening item', true));
}

// #15 AJAX done form
document.getElementById('doneForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById('doneSubmitBtn');
    const note = document.getElementById('doneCompletionNote').value.trim();
    if (!note) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('doneModal');
            showToast('Marked as done!');
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(() => showToast('Error marking done', true))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Mark Done';
    });
});

// Toast helper
function showToast(msg, isError) {
    const toast = document.createElement('div');
    toast.className = 'ai-toast' + (isError ? ' ai-toast-error' : '');
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('ai-toast-show'), 10);
    setTimeout(() => { toast.classList.remove('ai-toast-show'); setTimeout(() => toast.remove(), 300); }, 2500);
}

function escapeHtml(text) {
    const d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}

// Reminder modal
function openReminderModal(itemId, title) {
    currentItemId = itemId;
    document.getElementById('reminderForm').dataset.itemId = itemId;
    document.getElementById('reminderItemTitle').textContent = title;
    document.getElementById('reminderAt').value = '';
    document.getElementById('reminderNote').value = '';
    openModal('reminderModal');
}

// Quick pick helper
function setQuickReminder(hours, preset) {
    const input = document.getElementById('reminderAt');
    let d = new Date();
    if (preset === 'tomorrow_morning') {
        d.setDate(d.getDate() + 1);
        d.setHours(10, 0, 0, 0);
    } else if (preset === 'tomorrow_afternoon') {
        d.setDate(d.getDate() + 1);
        d.setHours(14, 0, 0, 0);
    } else {
        d.setHours(d.getHours() + hours);
        d.setMinutes(Math.ceil(d.getMinutes() / 5) * 5, 0, 0);
    }
    // Format as datetime-local
    const pad = n => String(n).padStart(2, '0');
    input.value = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

// Submit reminder via AJAX
function submitReminder(e) {
    e.preventDefault();
    const itemId = document.getElementById('reminderForm').dataset.itemId;
    const reminderAt = document.getElementById('reminderAt').value;
    const reminderNote = document.getElementById('reminderNote').value.trim();
    if (!reminderAt || !reminderNote) return;

    const btn = document.getElementById('reminderSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Setting...';

    fetch('/strategist/action-items/' + itemId + '/reminder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: JSON.stringify({ reminder_at: reminderAt, reminder_note: reminderNote })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('reminderModal');
            showToast(data.message || 'Reminder set!');
            setTimeout(() => location.reload(), 600);
        }
    })
    .catch(() => showToast('Error setting reminder', true))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-bell"></i> Set Reminder';
    });
}

// Clear/dismiss reminder
function clearReminder(itemId) {
    if (!confirm('Dismiss this reminder?')) return;

    fetch('/strategist/action-items/' + itemId + '/reminder', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Reminder dismissed');
            setTimeout(() => location.reload(), 500);
        }
    })
    .catch(() => showToast('Error dismissing reminder', true));
}
// Live countdown timers
function updateTimers() {
    document.querySelectorAll('.sn-timer[data-timer-due]').forEach(el => {
        const due = new Date(el.dataset.timerDue);
        const now = new Date();
        const diff = due - now;
        const isOverdue = diff < 0;
        const absDiff = Math.abs(diff);

        const totalSec = Math.floor(absDiff / 1000);
        const days = Math.floor(totalSec / 86400);
        const hrs = Math.floor((totalSec % 86400) / 3600);
        const mins = Math.floor((totalSec % 3600) / 60);
        const secs = totalSec % 60;

        const pad = n => String(n).padStart(2, '0');
        const clockEl = el.querySelector('.sn-timer-clock');
        const labelEl = el.querySelector('.sn-timer-label');

        if (days > 0) {
            clockEl.textContent = days + 'd ' + pad(hrs) + ':' + pad(mins) + ':' + pad(secs);
        } else {
            clockEl.textContent = pad(hrs) + ':' + pad(mins) + ':' + pad(secs);
        }

        // Update classes based on urgency
        el.classList.remove('sn-timer-overdue', 'sn-timer-critical', 'sn-timer-warn', 'sn-timer-ok');
        if (isOverdue) {
            el.classList.add('sn-timer-overdue');
            labelEl.textContent = 'OVERDUE BY';
        } else if (totalSec <= 10800) { // <= 3 hours
            el.classList.add('sn-timer-critical');
            labelEl.textContent = 'TIME LEFT';
        } else if (totalSec <= 86400) { // <= 24 hours
            el.classList.add('sn-timer-warn');
            labelEl.textContent = 'TIME LEFT';
        } else {
            el.classList.add('sn-timer-ok');
            labelEl.textContent = 'TIME LEFT';
        }
    });
}
updateTimers();
setInterval(updateTimers, 1000);

// Reminder Details Modal
let currentReminderItem = {};

function openReminderDetailsModal(itemId, title, reminderTime, reminderNote) {
    currentReminderItem = { itemId, title, reminderTime, reminderNote };

    document.getElementById('reminderDetailsTitle').textContent = title;

    // Parse and format reminder time
    const dt = new Date(reminderTime);
    const dateStr = dt.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
    const timeStr = dt.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    document.getElementById('reminderDetailsTime').textContent = dateStr + ' at ' + timeStr;

    document.getElementById('reminderDetailsNote').textContent = reminderNote || '(No context added)';

    openModal('reminderDetailsModal');
}

function openEditReminder() {
    closeModal('reminderDetailsModal');
    openReminderModal(currentReminderItem.itemId, currentReminderItem.title);
}

// ===== Edit Action Item =====
function openEditModal(itemId, title, description, clientId, priority, dueAt, recurring) {
    document.getElementById('editForm').action = '/strategist/action-items/' + itemId;
    document.getElementById('editTitle').value = title;
    document.getElementById('editDesc').value = description;
    document.getElementById('editClient').value = clientId || '';
    document.getElementById('editPriority').value = priority;
    document.getElementById('editDueAt').value = dueAt;
    document.getElementById('editRecurring').value = recurring || '';
    openModal('editStickyModal');
    setTimeout(() => document.getElementById('editTitle').focus(), 200);
}

function submitEdit(e) {
    e.preventDefault();
    const form = document.getElementById('editForm');
    const btn = document.getElementById('editSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';

    const formData = {
        title: document.getElementById('editTitle').value.trim(),
        description: document.getElementById('editDesc').value.trim(),
        client_id: document.getElementById('editClient').value || null,
        priority: document.getElementById('editPriority').value,
        due_at: document.getElementById('editDueAt').value,
        recurring: document.getElementById('editRecurring').value || null,
    };

    fetch(form.action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-HTTP-Method-Override': 'PATCH' },
        body: JSON.stringify(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('editStickyModal');
            showToast(data.message || 'Updated!');
            setTimeout(() => location.reload(), 500);
        } else if (data.errors) {
            const firstError = Object.values(data.errors)[0][0];
            showToast(firstError, true);
        }
    })
    .catch(() => showToast('Error updating item', true))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Update';
    });
}

// ===== Create Action Item =====
function openCreateModal() {
    document.getElementById('createTitle').value = '';
    document.getElementById('createDesc').value = '';
    document.getElementById('createClient').value = '';
    document.getElementById('createPriority').value = 'normal';
    document.getElementById('createDueAt').value = '';
    document.getElementById('createRecurring').value = '';
    document.getElementById('createReminderToggle').checked = false;
    document.getElementById('createReminderFields').style.display = 'none';
    document.getElementById('createReminderAt').value = '';
    document.getElementById('createReminderNote').value = '';
    openModal('createStickyModal');
    setTimeout(() => document.getElementById('createTitle').focus(), 200);
}

function setCreateQuickDue(preset) {
    const input = document.getElementById('createDueAt');
    const pad = n => String(n).padStart(2, '0');
    let d = new Date();
    switch (preset) {
        case 'today_evening':
            d.setHours(18, 0, 0, 0);
            if (d < new Date()) d.setDate(d.getDate() + 1);
            break;
        case 'tomorrow_morning':
            d.setDate(d.getDate() + 1);
            d.setHours(10, 0, 0, 0);
            break;
        case 'tomorrow_evening':
            d.setDate(d.getDate() + 1);
            d.setHours(18, 0, 0, 0);
            break;
        case 'next_week':
            d.setDate(d.getDate() + ((8 - d.getDay()) % 7 || 7));
            d.setHours(10, 0, 0, 0);
            break;
    }
    input.value = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

function toggleCreateReminder() {
    const fields = document.getElementById('createReminderFields');
    const toggle = document.getElementById('createReminderToggle');
    fields.style.display = toggle.checked ? 'block' : 'none';
    if (!toggle.checked) {
        document.getElementById('createReminderAt').value = '';
        document.getElementById('createReminderNote').value = '';
    }
}

function setCreateQuickReminder(hours, preset) {
    const input = document.getElementById('createReminderAt');
    const pad = n => String(n).padStart(2, '0');
    let d = new Date();
    if (preset === 'tomorrow_morning') {
        d.setDate(d.getDate() + 1);
        d.setHours(10, 0, 0, 0);
    } else if (preset === 'tomorrow_afternoon') {
        d.setDate(d.getDate() + 1);
        d.setHours(14, 0, 0, 0);
    } else {
        d.setHours(d.getHours() + hours);
        d.setMinutes(Math.ceil(d.getMinutes() / 5) * 5, 0, 0);
    }
    input.value = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}

function submitCreate(e) {
    e.preventDefault();
    const btn = document.getElementById('createSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';

    const formData = {
        title: document.getElementById('createTitle').value.trim(),
        description: document.getElementById('createDesc').value.trim(),
        client_id: document.getElementById('createClient').value || null,
        priority: document.getElementById('createPriority').value,
        due_at: document.getElementById('createDueAt').value,
        recurring: document.getElementById('createRecurring').value || null,
    };

    // Include reminder if enabled
    if (document.getElementById('createReminderToggle').checked) {
        const reminderAt = document.getElementById('createReminderAt').value;
        const reminderNote = document.getElementById('createReminderNote').value.trim();
        if (reminderAt) formData.reminder_at = reminderAt;
        if (reminderNote) formData.reminder_note = reminderNote;
    }

    fetch('{{ route("strategist.action-items.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal('createStickyModal');
            showToast(data.message || 'Created!');
            setTimeout(() => location.reload(), 500);
        } else if (data.errors) {
            const firstError = Object.values(data.errors)[0][0];
            showToast(firstError, true);
        }
    })
    .catch(() => showToast('Error creating item', true))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-plus"></i> Create';
    });
}
</script>
@endpush
