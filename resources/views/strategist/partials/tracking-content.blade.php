{{-- Top subtle loading shimmer bar (active when AJAX request in flight) --}}
<div class="trk-loading-bar"></div>

{{-- TABLE VIEW --}}
<div class="trk-table-wrap" id="trkTableView">
    <table class="trk-table" id="trkDataTable">
        <thead>
            <tr>
                <th style="min-width:240px">Task &amp; Brief</th>
                <th>Client</th>
                <th>Type</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Deadline</th>
                <th style="width:130px;text-align:center">Actions</th>
            </tr>
        </thead>
        <tbody id="trkTableBody">
            @forelse($tasks as $task)
                @php
                    $isDevTask = in_array($task->type, ['website', 'software', 'maintenance']);
                    $isTimerRunning = ($task->status === 'inprogress' && !$task->is_paused && !empty($task->started_at));
                    $isClientApproved = $task->isClientApproved();
                @endphp
                <tr id="task-row-{{ $task->id }}" class="trk-data-row {{ (string) request('task') === (string) $task->id ? 'trk-row-highlight' : '' }}" onclick="openTaskInspector({{ $task->id }})" data-task-id="{{ $task->id }}" data-search-text="{{ strtolower($task->title . ' ' . ($task->client?->name ?? '') . ' ' . ($task->assignee?->name ?? '') . ' ' . $task->type) }}">
                    <td>
                        <div class="trk-task-title">
                            <span>{{ $task->title }}</span>
                            @if($task->is_urgent_task)
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 7px;border-radius:6px;font-size:10px;font-weight:800;background:rgba(239,68,68,0.15);color:#EF4444;border:1px solid rgba(239,68,68,0.35);letter-spacing:0.2px">
                                    <i class="fa-solid fa-bolt"></i> Urgent Designer
                                </span>
                            @endif
                            @if($isTimerRunning)
                                <span class="trk-live-pulse" title="Designer is working on this task right now!">
                                    <span class="trk-pulse-dot"></span> Active Now
                                </span>
                            @endif
                        </div>
                        <div class="trk-task-meta">
                            @if($task->is_urgent_task && $task->urgent_requested_by)
                                <span style="font-size:10.5px;font-weight:700;color:#DC2626;display:inline-flex;align-items:center;gap:3px">
                                    <i class="fa-solid fa-bullhorn" style="font-size:9px"></i> Req: {{ $task->urgent_requested_by }}
                                </span>
                            @endif

                            @if($isDevTask)
                                @if($task->tech_stack)
                                    <span><i class="fas fa-code" style="font-size:9px;opacity:.7"></i> {{ $task->tech_stack }}</span>
                                @endif
                                @if($task->dev_deadline)
                                    <span><i class="fas fa-laptop-code" style="font-size:9px;opacity:.7"></i> Dev: {{ $task->dev_deadline->format('d M') }}</span>
                                @endif
                            @else
                                @if(is_array($task->platform))
                                    <span>{{ implode(', ', array_map('ucfirst', $task->platform)) }}</span>
                                @elseif($task->platform)
                                    <span>{{ ucfirst($task->platform) }}</span>
                                @endif
                            @endif

                            @if($task->comments_count > 0)
                                <span class="trk-comment-badge"><i class="fas fa-comment" style="font-size:10px"></i> {{ $task->comments_count }}</span>
                            @endif

                            @if($task->creator)
                                <span class="trk-creator-pill">
                                    <i class="fa-solid fa-user-pen" style="font-size:9px"></i> By: {{ $task->creator->name }}
                                </span>
                            @endif

                            @if($task->requires_client_approval)
                                <span style="font-size:10px;font-weight:700;padding:2px 7px;border-radius:6px;background:{{ $isClientApproved ? 'rgba(16,185,129,0.12)' : 'rgba(239,68,68,0.1)' }};color:{{ $isClientApproved ? '#059669' : '#EF4444' }};border:1px solid {{ $isClientApproved ? 'rgba(16,185,129,0.25)' : 'rgba(239,68,68,0.2)' }}">
                                    <i class="fa-solid {{ $isClientApproved ? 'fa-check' : 'fa-clock' }}" style="font-size:8px"></i> Client: {{ $isClientApproved ? 'Yes' : 'Pending' }}
                                </span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="trk-client-cell">
                            <x-client-branding :client="$task->client" size="26px" radius="6px" :showName="true" fit="contain" />
                        </div>
                    </td>
                    <td><span class="tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span></td>
                    <td>
                        @if($task->assignee)
                            <div class="trk-assignee-cell">
                                <div class="av-sm" style="background:{{ $task->assignee->avatar_color ?? '#555' }}">{{ $task->assignee->initial }}</div>
                                <span>{{ $task->assignee->name }}</span>
                            </div>
                        @else
                            <span class="trk-unassigned"><i class="fa-solid fa-circle-exclamation"></i> Unassigned</span>
                        @endif
                    </td>
                    <td>
                        <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                        @if($task->revision_count > 0)
                            <span class="wf-rev-badge" style="animation:none;margin-left:4px" title="{{ $task->revision_count }} revision{{ $task->revision_count > 1 ? 's' : '' }} requested">
                                <i class="fa-solid fa-rotate-left"></i> Rev #{{ $task->revision_count }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($task->is_urgent_task || $task->priority === 'urgent')
                            <span class="trk-priority trk-prio-urgent" style="background:#EF4444;color:#fff;box-shadow:0 2px 8px rgba(239,68,68,0.35);display:inline-flex;align-items:center;gap:4px">
                                <i class="fa-solid fa-bolt" style="font-size:9px"></i> Urgent
                            </span>
                        @elseif($task->priority === 'high')
                            <span class="trk-priority trk-prio-high">High</span>
                        @else
                            <span class="trk-priority trk-prio-normal">Normal</span>
                        @endif
                    </td>
                    <td>
                        @if($task->deadline)
                            <div class="trk-deadline {{ $task->isOverdue() ? 'trk-deadline-overdue' : '' }}">
                                <i class="fa-regular {{ $task->isOverdue() ? 'fa-clock' : 'fa-calendar' }}"></i>
                                {{ $task->deadline->format('d M Y') }}
                                @if($task->isOverdue()) <span class="trk-overdue-tag">Overdue</span> @endif
                            </div>
                        @else
                            <span class="trk-no-deadline">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="trk-actions" onclick="event.stopPropagation()">
                            <button type="button" class="trk-action-btn trk-action-view" title="Quick Drawer Inspector" onclick="openTaskInspector({{ $task->id }})">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            @if($task->status === 'review')
                                <button type="button" class="trk-action-btn" style="color:#059669;border-color:rgba(5,150,105,0.3);background:rgba(5,150,105,0.08)" title="Fast-Track: Approve & Send to Admin" onclick="approveTaskFromTracking({{ $task->id }}, '{{ addslashes($task->title) }}', event)">
                                    <i class="fa-solid fa-check-double"></i>
                                </button>
                            @endif
                            <a href="{{ route('strategist.tasks.show', $task) }}" class="trk-action-btn" title="Open Full Task Details">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                            <a href="{{ route('strategist.tasks.edit', $task) }}" class="trk-action-btn" title="Edit Task" data-no-loader>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <button type="button" class="trk-action-btn" title="Reassign" onclick="openReassignModal({{ $task->id }}, '{{ addslashes($task->title) }}', {{ $task->assigned_to ?? 'null' }})">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </button>
                            @if($task->assignee && !in_array($task->status, ['completed', 'published']))
                                <button type="button" class="trk-action-btn trk-action-followup" title="Nudge Designer via Live Alert" onclick="nudgeTask({{ $task->id }}, '{{ addslashes($task->assignee->name) }}', event)">
                                    <i class="fa-solid fa-bell"></i>
                                </button>
                            @endif
                            <button type="button" class="trk-action-btn trk-action-delete" title="Delete Task" onclick="deleteTaskAjax({{ $task->id }}, '{{ addslashes($task->title) }}', event)">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="trk-empty">
                        <div class="trk-empty-box">
                            <div class="trk-empty-icon"><i class="fa-solid fa-inbox"></i></div>
                            <div class="trk-empty-title">No tasks found</div>
                            <div class="trk-empty-sub">Try adjusting your filters or search keywords</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- CARD GRID VIEW (Alternative visual layout) --}}
<div class="trk-cards-grid" id="trkGridView" style="display:none">
    @forelse($tasks as $task)
        @php
            $isTimerRunning = ($task->status === 'inprogress' && !$task->is_paused && !empty($task->started_at));
            $thumb = $task->getPreviewThumbnail();
        @endphp
        <div class="trk-card trk-data-row" onclick="openTaskInspector({{ $task->id }})" data-task-id="{{ $task->id }}" data-search-text="{{ strtolower($task->title . ' ' . ($task->client?->name ?? '') . ' ' . ($task->assignee?->name ?? '') . ' ' . $task->type) }}">
            <div class="trk-card-top">
                <div style="min-width:0;flex:1">
                    <div class="trk-card-title">{{ $task->title }}</div>
                    <div style="font-size:11.5px;color:var(--text3);display:flex;align-items:center;gap:6px">
                        <span>{{ $task->client?->emoji ?? '🏢' }} {{ $task->client?->name ?? 'Unknown Client' }}</span>
                        @if($task->revision_count > 0)
                            <span class="wf-rev-badge"><i class="fa-solid fa-rotate-left"></i> Rev #{{ $task->revision_count }}</span>
                        @endif
                    </div>
                </div>
                @if($isTimerRunning)
                    <span class="trk-live-pulse"><span class="trk-pulse-dot"></span> Active</span>
                @endif
            </div>

            <div class="trk-card-badge-row">
                <span class="tag {{ $task->type_tag_class }}">{{ ucfirst($task->type) }}</span>
                <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                @if($task->is_urgent_task)
                    <span class="trk-priority trk-prio-urgent" style="background:#EF4444;color:#fff;box-shadow:0 2px 8px rgba(239,68,68,0.35);display:inline-flex;align-items:center;gap:3px"><i class="fa-solid fa-bolt" style="font-size:9px"></i> Urgent Designer</span>
                @elseif($task->priority === 'urgent')
                    <span class="trk-priority trk-prio-urgent">Urgent</span>
                @elseif($task->priority === 'high')
                    <span class="trk-priority trk-prio-high">High</span>
                @endif
            </div>

            @if($task->is_urgent_task && $task->urgent_requested_by)
                <div style="font-size:11px;font-weight:700;color:#DC2626;padding:2px 0 4px;display:flex;align-items:center;gap:4px">
                    <i class="fa-solid fa-bullhorn" style="font-size:9px"></i> Req: {{ $task->urgent_requested_by }}
                </div>
            @endif

            <div class="trk-card-middle">
                <div style="display:flex;align-items:center;gap:8px">
                    @if($task->assignee)
                        <div class="av-sm" style="background:{{ $task->assignee->avatar_color ?? '#555' }}">{{ $task->assignee->initial }}</div>
                        <span style="font-size:12px;font-weight:600">{{ $task->assignee->name }}</span>
                    @else
                        <span class="trk-unassigned"><i class="fa-solid fa-circle-exclamation"></i> Unassigned</span>
                    @endif
                </div>
                <div style="text-align:right">
                    @if($task->deadline)
                        <div style="font-size:11.5px;font-weight:700;color:{{ $task->isOverdue() ? '#EF4444' : 'var(--text)' }}">
                            <i class="fa-regular fa-calendar"></i> {{ $task->deadline->format('d M') }}
                        </div>
                    @else
                        <span style="font-size:11px;color:var(--text3)">No Deadline</span>
                    @endif
                </div>
            </div>

            <div class="trk-card-foot" onclick="event.stopPropagation()">
                <button type="button" class="trk-btn-ghost" style="padding:5px 12px;font-size:11px;border-radius:6px" onclick="openTaskInspector({{ $task->id }})">
                    <i class="fa-solid fa-eye"></i> Inspect
                </button>
                <div style="display:flex;gap:4px">
                    @if($task->status === 'review')
                        <button type="button" class="trk-action-btn" style="color:#059669;border-color:rgba(5,150,105,0.3);background:rgba(5,150,105,0.08)" title="Fast-Track: Approve & Send to Admin" onclick="approveTaskFromTracking({{ $task->id }}, '{{ addslashes($task->title) }}', event)">
                            <i class="fa-solid fa-check-double"></i>
                        </button>
                    @endif
                    @if($task->assignee && !in_array($task->status, ['completed', 'published']))
                        <button type="button" class="trk-action-btn trk-action-followup" title="Nudge Designer" onclick="nudgeTask({{ $task->id }}, '{{ addslashes($task->assignee->name) }}', event)">
                            <i class="fa-solid fa-bell"></i>
                        </button>
                    @endif
                    <a href="{{ route('strategist.tasks.edit', $task) }}" class="trk-action-btn" title="Edit" data-no-loader>
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="trk-empty" style="grid-column:1/-1">
            <div class="trk-empty-box">
                <div class="trk-empty-icon"><i class="fa-solid fa-inbox"></i></div>
                <div class="trk-empty-title">No tasks found</div>
                <div class="trk-empty-sub">Try adjusting your filters or search keywords</div>
            </div>
        </div>
    @endforelse
</div>

{{-- Pagination & Footer --}}
@if($tasks->hasPages())
    <div class="trk-pagination">
        {{ $tasks->links('vendor.pagination.custom') }}
    </div>
@endif

@if($tasks->total() > 0)
    <div class="trk-table-footer">
        <span class="trk-table-footer-text">Page {{ $tasks->currentPage() }} of {{ $tasks->lastPage() }} &middot; {{ $tasks->total() }} task{{ $tasks->total() !== 1 ? 's' : '' }}</span>
        <div class="trk-perpage-footer">
            <label class="trk-perpage-label">Show</label>
            <select class="trk-perpage-select" onchange="changePerPage(this.value)">
                <option value="25" {{ request('per_page', '25') == '25' ? 'selected' : '' }}>25</option>
                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
            </select>
            <label class="trk-perpage-label">per page</label>
        </div>
    </div>
@endif
