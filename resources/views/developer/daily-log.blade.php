@extends('layouts.app')

@section('content')
<style>
.dl-wrap {
    --dl-red: #f13535;
    --dl-red-dark: #c92323;
    --dl-red-dim: rgba(241,53,53,0.08);
    padding: 4px 0 40px;
    font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    color: var(--text, #111827);
}

/* ── Header ── */
.dl-head {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 14px; margin-bottom: 20px;
}
.dl-head h1 {
    margin: 0; font-size: 26px; font-weight: 800;
    display: flex; align-items: center; gap: 10px;
}
.dl-head h1 i { color: var(--dl-red); }
.dl-head p { margin: 4px 0 0; font-size: 13px; color: var(--text3, #9ca3af); }
.dl-datebar {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--card, #fff); border: 1px solid var(--border, #e5e7eb);
    border-radius: 12px; padding: 5px;
}
.dl-datebar a, .dl-datebar button {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px;
    border: none; background: transparent; color: var(--text, #374151);
    font-size: 12.5px; font-weight: 700; text-decoration: none; cursor: pointer;
}
.dl-datebar a:hover, .dl-datebar button:hover { background: var(--dl-red-dim); color: var(--dl-red); }
.dl-datebar a.is-disabled { opacity: .35; pointer-events: none; }
.dl-datebar input[type=date] {
    height: 34px; border: 1px solid var(--border, #e5e7eb); border-radius: 8px;
    padding: 0 8px; font: inherit; font-size: 13px; color: var(--text, #111827);
    background: var(--card, #fff);
}

/* ── Stats ── */
.dl-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
.dl-stat {
    background: var(--card, #fff); border: 1px solid var(--border, #e5e7eb);
    border-radius: var(--radius, 16px); padding: 16px 18px;
}
.dl-stat-val { font-size: 26px; font-weight: 800; line-height: 1.1; }
.dl-stat-val small { font-size: 13px; font-weight: 700; color: var(--text3, #9ca3af); }
.dl-stat-label {
    font-size: 11.5px; font-weight: 700; color: var(--text3, #9ca3af);
    text-transform: uppercase; letter-spacing: .5px; margin-top: 4px;
}

/* ── Layout ── */
.dl-grid { display: grid; grid-template-columns: minmax(0, 3fr) minmax(0, 2fr); gap: 20px; align-items: start; }
.dl-card {
    background: var(--card, #fff); border: 1px solid var(--border, #e5e7eb);
    border-radius: var(--radius, 16px); overflow: hidden;
}
.dl-card-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 16px 20px; border-bottom: 1px solid var(--border, #e5e7eb);
}
.dl-card-title { font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 8px; }
.dl-card-title i { color: var(--dl-red); }
.dl-card-body { padding: 18px 20px; }

.dl-pill {
    font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .4px;
    padding: 3px 10px; border-radius: 99px;
}
.dl-pill-ok { background: rgba(16,185,129,.12); color: #059669; }
.dl-pill-missing { background: var(--dl-red-dim); color: var(--dl-red); }
.dl-pill-locked { background: rgba(156,163,175,.15); color: #6b7280; }

/* ── Form ── */
.dl-errors {
    background: #fff1f1; border: 1px solid #fecaca; color: #b91c1c;
    border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px;
}
.dl-errors ul { margin: 0; padding-left: 18px; }
.dl-entry {
    border: 1px solid var(--border, #e5e7eb); border-radius: 12px;
    padding: 14px; margin-bottom: 12px; background: var(--card2, #fafafa);
}
.dl-entry-row { display: grid; grid-template-columns: minmax(0, 2.2fr) minmax(0, 1.2fr) 90px 90px 36px; gap: 10px; align-items: end; }
.dl-field label {
    display: block; font-size: 11px; font-weight: 700; color: var(--text3, #6b7280);
    text-transform: uppercase; letter-spacing: .4px; margin-bottom: 5px;
}
.dl-input {
    width: 100%; box-sizing: border-box; border: 1px solid var(--border, #e5e7eb);
    border-radius: 9px; padding: 9px 11px; font: inherit; font-size: 13px;
    background: var(--card, #fff); color: var(--text, #111827);
}
.dl-input:focus { outline: none; border-color: var(--dl-red); box-shadow: 0 0 0 3px var(--dl-red-dim); }
textarea.dl-input { resize: vertical; min-height: 64px; }
.dl-entry-desc { margin-top: 10px; }
.dl-remove {
    width: 36px; height: 38px; border-radius: 9px; border: 1px solid var(--border, #e5e7eb);
    background: var(--card, #fff); color: #9ca3af; cursor: pointer;
}
.dl-remove:hover { color: var(--dl-red); border-color: var(--dl-red); }
.dl-form-foot {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    flex-wrap: wrap; margin: 4px 0 18px;
}
.dl-total { font-size: 13px; font-weight: 700; color: var(--text3, #6b7280); }
.dl-total strong { font-size: 18px; color: var(--text, #111827); }
.dl-total.is-over strong { color: var(--dl-red); }
.dl-btn {
    display: inline-flex; align-items: center; gap: 7px; border-radius: 10px;
    padding: 10px 16px; font: inherit; font-size: 13px; font-weight: 700; cursor: pointer;
    border: 1px solid var(--border, #e5e7eb); background: var(--card, #fff); color: var(--text, #374151);
}
.dl-btn:hover { border-color: var(--dl-red); color: var(--dl-red); }
.dl-btn-primary { background: var(--dl-red); border-color: var(--dl-red); color: #fff; }
.dl-btn-primary:hover { background: var(--dl-red-dark); color: #fff; }
.dl-section-label {
    font-size: 12px; font-weight: 800; color: var(--text, #111827);
    display: flex; align-items: center; gap: 7px; margin: 16px 0 6px;
}
.dl-section-label i { color: var(--dl-red); }
.dl-actions { display: flex; justify-content: flex-end; margin-top: 18px; }

/* ── Read-only entries & history ── */
.dl-item { padding: 12px 0; border-bottom: 1px solid var(--border, #f3f4f6); }
.dl-item:last-child { border-bottom: none; }
.dl-item-top { display: flex; justify-content: space-between; gap: 10px; align-items: baseline; }
.dl-item-task { font-size: 13px; font-weight: 700; }
.dl-item-meta { font-size: 11.5px; color: var(--text3, #9ca3af); margin-top: 2px; }
.dl-item-hours { font-size: 13px; font-weight: 800; white-space: nowrap; }
.dl-item-desc { font-size: 13px; margin-top: 6px; white-space: pre-line; color: var(--text2, #374151); }
.dl-note {
    margin-top: 12px; padding: 10px 12px; border-radius: 10px; font-size: 13px; white-space: pre-line;
    background: var(--card2, #f9fafb); border: 1px solid var(--border, #e5e7eb);
}
.dl-note.is-blocker { background: #fff7ed; border-color: #fed7aa; color: #9a3412; }
.dl-note strong { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 3px; }

.dl-history-day {
    display: block; padding: 14px 20px; border-bottom: 1px solid var(--border, #f3f4f6);
    text-decoration: none; color: inherit; transition: background .15s;
}
.dl-history-day:hover { background: var(--card2, #f9fafb); }
.dl-history-day.is-active { background: var(--dl-red-dim); }
.dl-history-top { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.dl-history-date { font-size: 13px; font-weight: 800; }
.dl-history-sub { font-size: 12px; color: var(--text3, #9ca3af); margin-top: 3px; }
.dl-history-flag { color: #ea580c; font-size: 11.5px; font-weight: 700; margin-top: 4px; }
.dl-empty { padding: 30px 20px; text-align: center; color: var(--text3, #9ca3af); font-size: 13px; }
.dl-empty i { display: block; font-size: 26px; opacity: .4; margin-bottom: 8px; }
.dl-pagination { padding: 12px 20px; }

@media (max-width: 1024px) {
    .dl-grid { grid-template-columns: 1fr; }
    .dl-stats { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .dl-entry-row { grid-template-columns: 1fr 1fr; }
    .dl-entry-row .dl-field-task { grid-column: 1 / -1; }
    .dl-head h1 { font-size: 22px; }
}
</style>

@php
    $isToday = $date->isToday();
    $prevDate = $date->copy()->subDay()->toDateString();
    $nextDate = $date->copy()->addDay()->toDateString();

    $rows = old('entries');
    if (!$rows) {
        $rows = $log
            ? $log->entries->map(fn ($e) => [
                'task_id' => $e->task_id,
                'work_type' => $e->work_type,
                'description' => $e->description,
                'hours' => $e->hours,
                'progress' => $e->progress,
            ])->values()->all()
            : [[
                'task_id' => optional($tasks->firstWhere('status', 'inprogress'))->id,
                'work_type' => 'development',
                'description' => '',
                'hours' => '',
                'progress' => '',
            ]];
    }
    $rows = array_values($rows);
@endphp

<div class="dl-wrap">

    {{-- ── Header ── --}}
    <div class="dl-head">
        <div>
            <h1><i class="fas fa-clipboard-list"></i> Daily Work Log</h1>
            <p>Log what you worked on each day. Your manager sees this in the team report.</p>
        </div>
        <form class="dl-datebar" method="GET" action="{{ route('developer.daily-log') }}">
            <a href="{{ route('developer.daily-log', ['date' => $prevDate]) }}" title="Previous day"><i class="fas fa-chevron-left"></i></a>
            <input type="date" name="date" value="{{ $date->toDateString() }}" max="{{ today()->toDateString() }}" onchange="this.form.submit()">
            <a href="{{ route('developer.daily-log', ['date' => $nextDate]) }}" class="{{ $isToday ? 'is-disabled' : '' }}" title="Next day"><i class="fas fa-chevron-right"></i></a>
            @unless($isToday)
                <a href="{{ route('developer.daily-log') }}">Today</a>
            @endunless
        </form>
    </div>

    {{-- ── Stats ── --}}
    <div class="dl-stats">
        <div class="dl-stat">
            <div class="dl-stat-val">{{ $log ? round($log->totalHours(), 2) : 0 }}<small> h</small></div>
            <div class="dl-stat-label">{{ $isToday ? 'Logged today' : 'Logged ' . $date->format('M d') }}</div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-val">{{ round($hoursThisWeek, 2) }}<small> h</small></div>
            <div class="dl-stat-label">This week</div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-val">{{ $daysLoggedThisMonth }}<small> days</small></div>
            <div class="dl-stat-label">Logged this month</div>
        </div>
        <div class="dl-stat">
            <div class="dl-stat-val">{{ $avgHoursThisMonth }}<small> h</small></div>
            <div class="dl-stat-label">Avg per logged day</div>
        </div>
    </div>

    <div class="dl-grid">

        {{-- ── LEFT: the selected day's log ── --}}
        <div class="dl-card">
            <div class="dl-card-head">
                <div class="dl-card-title">
                    <i class="fas fa-calendar-day"></i>
                    {{ $isToday ? 'Today' : $date->format('l') }} · {{ $date->format('M d, Y') }}
                </div>
                @if(!$canEdit)
                    <span class="dl-pill dl-pill-locked"><i class="fas fa-lock"></i> Locked</span>
                @elseif($log)
                    <span class="dl-pill dl-pill-ok"><i class="fas fa-check"></i> Submitted</span>
                @else
                    <span class="dl-pill dl-pill-missing">Not submitted</span>
                @endif
            </div>

            <div class="dl-card-body">
                @if($canEdit)
                    @if($errors->any())
                        <div class="dl-errors">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('developer.daily-log.store') }}" id="dlForm">
                        @csrf
                        <input type="hidden" name="log_date" value="{{ $date->toDateString() }}">

                        <div id="dlEntries"></div>

                        <div class="dl-form-foot">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                                <button type="button" class="dl-btn" id="dlAddEntry"><i class="fas fa-plus"></i> Add entry</button>
                                <button type="button" class="dl-btn" onclick="openSelfTaskModal()" style="background:#eef2ff;color:#4f46e5;border:1px solid #c7d2fe;font-weight:700" title="Create a new task and assign to yourself">
                                    <i class="fas fa-plus-circle"></i> Quick Create Task
                                </button>
                            </div>
                            <div class="dl-total" id="dlTotal">Total: <strong>0</strong> h</div>
                        </div>

                        <div class="dl-section-label"><i class="fas fa-triangle-exclamation"></i> Blockers / waiting on</div>
                        <textarea name="blockers" class="dl-input" rows="2" maxlength="2000" placeholder="e.g. Waiting for client logo, need hosting cPanel access...">{{ old('blockers', $log->blockers ?? '') }}</textarea>

                        <div class="dl-section-label"><i class="fas fa-forward"></i> Plan for next working day</div>
                        <textarea name="plan_next" class="dl-input" rows="2" maxlength="2000" placeholder="What you'll pick up next...">{{ old('plan_next', $log->plan_next ?? '') }}</textarea>

                        <div class="dl-actions">
                            <button type="submit" class="dl-btn dl-btn-primary">
                                <i class="fas fa-paper-plane"></i> {{ $log ? 'Update log' : 'Submit log' }}
                            </button>
                        </div>
                    </form>

                    {{-- One entry row; cloned by the script below for every entry --}}
                    <template id="dlEntryTemplate">
                        <div class="dl-entry">
                            <div class="dl-entry-row">
                                <div class="dl-field dl-field-task">
                                    <label>Task</label>
                                    <select class="dl-input" data-field="task_id">
                                        <option value="">— Non-task work —</option>
                                        @foreach($tasks as $task)
                                            <option value="{{ $task->id }}">
                                                {{ $task->title ?? 'Untitled' }}{{ $task->client ? ' · ' . $task->client->name : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="dl-field">
                                    <label>Type</label>
                                    <select class="dl-input" data-field="work_type">
                                        <optgroup label="🎨 Creative & Graphics Design">
                                            <option value="graphics_design">Graphics Design</option>
                                            <option value="logo_design">Logo Design</option>
                                            <option value="product_design">Product Design / UI-UX</option>
                                            <option value="banner_flyer">Banner / Flyer / Print</option>
                                            <option value="branding">Branding & Identity</option>
                                            <option value="video_motion">Video / Motion / Reel</option>
                                        </optgroup>
                                        <optgroup label="💻 Web & Technical">
                                            <option value="development" selected>Website / Software Development</option>
                                            <option value="bug_fix">Bug Fix / Debugging</option>
                                            <option value="server_maintenance">Server Maintenance / DevOps</option>
                                            <option value="deployment">Deployment & Hosting</option>
                                            <option value="testing">Testing & QA</option>
                                        </optgroup>
                                        <optgroup label="📋 Operations & Support">
                                            <option value="revision">Client Revision</option>
                                            <option value="research">Research & Planning</option>
                                            <option value="meeting">Meeting & Discussion</option>
                                            <option value="support">Technical Support</option>
                                            <option value="other">Other Work</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="dl-field">
                                    <label>Hours</label>
                                    <input type="number" class="dl-input" data-field="hours" min="0.25" max="24" step="0.25" placeholder="2.5" required>
                                </div>
                                <div class="dl-field">
                                    <label>Progress %</label>
                                    <input type="number" class="dl-input" data-field="progress" min="0" max="100" step="5" placeholder="—">
                                </div>
                                <button type="button" class="dl-remove" title="Remove entry"><i class="fas fa-trash"></i></button>
                            </div>
                            <div class="dl-field dl-entry-desc">
                                <label>What did you do?</label>
                                <textarea class="dl-input" data-field="description" rows="2" maxlength="2000" required placeholder="e.g. Built checkout page UI, fixed mobile menu overlap..."></textarea>
                            </div>
                        </div>
                    </template>
                @elseif($log)
                    @foreach($log->entries as $entry)
                        <div class="dl-item">
                            <div class="dl-item-top">
                                <div>
                                    <div class="dl-item-task">{{ $entry->task->title ?? 'Non-task work' }}</div>
                                    <div class="dl-item-meta">
                                        {{ $entry->workTypeLabel() }}
                                        @if($entry->task && $entry->task->client) · {{ $entry->task->client->name }} @endif
                                        @if($entry->progress !== null) · {{ $entry->progress }}% done @endif
                                    </div>
                                </div>
                                <div class="dl-item-hours">{{ $entry->hours }} h</div>
                            </div>
                            <div class="dl-item-desc">{{ $entry->description }}</div>
                        </div>
                    @endforeach
                    @if($log->blockers)
                        <div class="dl-note is-blocker"><strong>Blockers</strong>{{ $log->blockers }}</div>
                    @endif
                    @if($log->plan_next)
                        <div class="dl-note"><strong>Plan for next day</strong>{{ $log->plan_next }}</div>
                    @endif
                @else
                    <div class="dl-empty">
                        <i class="fas fa-lock"></i>
                        No log was submitted for this day. Logs can only be added or edited for the last {{ \App\Models\DailyWorkLog::EDIT_WINDOW_DAYS }} days.
                    </div>
                @endif
            </div>
        </div>

        {{-- ── RIGHT: history ── --}}
        <div class="dl-card">
            <div class="dl-card-head">
                <div class="dl-card-title"><i class="fas fa-clock-rotate-left"></i> Log History</div>
            </div>
            @forelse($history as $day)
                <a href="{{ route('developer.daily-log', ['date' => $day->log_date->toDateString()]) }}"
                   class="dl-history-day {{ $day->log_date->isSameDay($date) ? 'is-active' : '' }}">
                    <div class="dl-history-top">
                        <div class="dl-history-date">{{ $day->log_date->format('D, M d, Y') }}</div>
                        <div class="dl-item-hours">{{ $day->totalHours() }} h</div>
                    </div>
                    <div class="dl-history-sub">
                        {{ $day->entries->count() }} {{ Str::plural('entry', $day->entries->count()) }}
                        · {{ $day->entries->pluck('task.title')->filter()->unique()->take(2)->implode(', ') ?: 'Non-task work' }}
                    </div>
                    @if($day->blockers)
                        <div class="dl-history-flag"><i class="fas fa-triangle-exclamation"></i> Blocker reported</div>
                    @endif
                </a>
            @empty
                <div class="dl-empty">
                    <i class="fas fa-clipboard"></i>
                    No logs yet. Your first one starts today.
                </div>
            @endforelse
            @if($history->hasPages())
                <div class="dl-pagination">{{ $history->links() }}</div>
            @endif
        </div>

    </div>
</div>

@if($canEdit)
<script>
(function () {
    const container = document.getElementById('dlEntries');
    const template = document.getElementById('dlEntryTemplate');
    const totalEl = document.getElementById('dlTotal');
    const initialRows = @json($rows);

    function reindex() {
        container.querySelectorAll('.dl-entry').forEach((entry, i) => {
            entry.querySelectorAll('[data-field]').forEach(input => {
                input.name = `entries[${i}][${input.dataset.field}]`;
            });
        });
        const single = container.querySelectorAll('.dl-entry').length === 1;
        container.querySelectorAll('.dl-remove').forEach(btn => { btn.disabled = single; });
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        container.querySelectorAll('[data-field="hours"]').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        totalEl.querySelector('strong').textContent = Math.round(total * 100) / 100;
        totalEl.classList.toggle('is-over', total > 24);
    }

    function addRow(data) {
        const node = template.content.firstElementChild.cloneNode(true);
        data = data || {};
        node.querySelectorAll('[data-field]').forEach(input => {
            const value = data[input.dataset.field];
            if (value !== undefined && value !== null) input.value = value;
        });
        if (!data.work_type) node.querySelector('[data-field="work_type"]').value = 'development';
        node.querySelector('.dl-remove').addEventListener('click', () => { node.remove(); reindex(); });
        node.querySelector('[data-field="hours"]').addEventListener('input', updateTotal);
        container.appendChild(node);
        reindex();
        return node;
    }

    (initialRows.length ? initialRows : [{}]).forEach(addRow);

    document.getElementById('dlAddEntry').addEventListener('click', () => {
        addRow().querySelector('[data-field="task_id"]').focus();
    });

    window.onSelfTaskCreatedInDailyLog = function(task) {
        // 1. Add option to template for future rows
        const templateSelect = template.content.querySelector('[data-field="task_id"]');
        if (templateSelect) {
            const opt = document.createElement('option');
            opt.value = task.id;
            opt.textContent = `${task.title}${task.client_name ? ' · ' + task.client_name : ''}`;
            templateSelect.appendChild(opt);
        }

        // 2. Add option to all existing row selects
        container.querySelectorAll('[data-field="task_id"]').forEach(sel => {
            const opt = document.createElement('option');
            opt.value = task.id;
            opt.textContent = `${task.title}${task.client_name ? ' · ' + task.client_name : ''}`;
            sel.appendChild(opt);
        });

        // 3. Find first empty row or create new row
        let targetRow = null;
        const rows = container.querySelectorAll('.dl-entry');
        for (let r of rows) {
            const taskSelect = r.querySelector('[data-field="task_id"]');
            const descInput = r.querySelector('[data-field="description"]');
            if ((!taskSelect.value || taskSelect.value === '') && !descInput.value.trim()) {
                targetRow = r;
                break;
            }
        }

        if (!targetRow) {
            targetRow = addRow();
        }

        const taskSelect = targetRow.querySelector('[data-field="task_id"]');
        if (taskSelect) {
            taskSelect.value = task.id;
        }

        // Infer work_type from task type
        const workTypeSelect = targetRow.querySelector('[data-field="work_type"]');
        if (workTypeSelect && task.type) {
            const typeMap = {
                'logo': 'logo_design',
                'product_design': 'product_design',
                'banner': 'banner_flyer',
                'flyer': 'banner_flyer',
                'brochure': 'banner_flyer',
                'branding': 'branding',
                'reel': 'video_motion',
                'video': 'video_motion',
                'server_maintenance': 'server_maintenance',
                'maintenance': 'server_maintenance',
                'website': 'development',
                'software': 'development',
                'post': 'graphics_design',
            };
            if (typeMap[task.type]) {
                workTypeSelect.value = typeMap[task.type];
            }
        }

        const descInput = targetRow.querySelector('[data-field="description"]');
        if (descInput) {
            descInput.placeholder = `Worked on ${task.title}...`;
            descInput.focus();
        }
    };
})();
</script>
@endif

@include('components.self-task-modal')
@endsection
