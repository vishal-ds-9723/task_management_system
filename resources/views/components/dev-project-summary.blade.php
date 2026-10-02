{{-- Read-only project details for website / software / maintenance tasks (designer task page) --}}
@props(['task'])

@php
    $modules = collect(preg_split(str_contains((string) $task->modules, "\n") ? '/\r\n|\r|\n/' : '/[,•]/', (string) $task->modules))
        ->map(fn ($line) => trim(preg_replace('/^\s*(\d+[.)]|[-*•])\s*/', '', $line)))
        ->filter()->values();
    $tech = collect(explode(',', (string) ($task->preferred_tech ?: $task->tech_stack)))->map(fn ($t) => trim($t))->filter()->values();
    $assets = collect(\App\Models\Task::PROJECT_ASSETS)->map(fn ($label, $field) => ['label' => $label, 'value' => $task->{$field}]);
    $received = $assets->where('value', true)->count();
    $days = $task->dev_deadline ? (int) today()->diffInDays($task->dev_deadline) : null;
    $noun = ['website' => 'Pages', 'software' => 'Modules', 'maintenance' => 'Work items'][$task->type] ?? 'Pages';
@endphp

<div class="dps">
    <div class="dps-head">
        <span class="dps-icon"><i class="fas {{ ['software' => 'fa-laptop-code', 'maintenance' => 'fa-screwdriver-wrench'][$task->type] ?? 'fa-globe' }}"></i></span>
        <div>
            <div class="dps-title">{{ ucfirst($task->type) }} project</div>
            <div class="dps-sub">{{ \App\Models\Task::BUSINESS_TYPES[$task->business_type] ?? 'Development work' }}{{ $task->has_wireframes ? ' · Designs ready' : '' }}</div>
        </div>
        @if($days !== null && !in_array($task->status, ['completed', 'published'], true))
            <span class="dps-due {{ $days < 0 ? 'is-late' : ($days <= 3 ? 'is-soon' : '') }}">
                {{ $days < 0 ? 'Handover overdue by ' . abs($days) . 'd' : ($days === 0 ? 'Handover today' : $days . ' days to handover') }}
            </span>
        @endif
    </div>

    <div class="dps-dates">
        <div><span>Start</span><b>{{ $task->project_start_date?->format('M d, Y') ?? '—' }}</b></div>
        <div><span>Handover</span><b>{{ $task->dev_deadline?->format('M d, Y') ?? '—' }}</b></div>
        <div><span>Go live</span><b>{{ $task->launch_date?->format('M d, Y') ?? '—' }}</b></div>
    </div>

    @if($modules->isNotEmpty())
        <div class="dps-label">{{ $noun }} · {{ $modules->count() }}</div>
        <div class="dps-chips">
            @foreach($modules as $i => $module)
                <span><b>{{ $i + 1 }}</b> {{ $module }}</span>
            @endforeach
        </div>
    @endif

    @if($tech->isNotEmpty())
        <div class="dps-label">Tech stack</div>
        <div class="dps-chips is-tech">
            @foreach($tech as $item)
                <span>{{ $item }}</span>
            @endforeach
        </div>
    @endif

    <div class="dps-label">Client handover · {{ $received }}/{{ $assets->count() }} received</div>
    <div class="dps-assets">
        @foreach($assets as $asset)
            <span class="{{ $asset['value'] === true ? 'is-yes' : ($asset['value'] === false ? 'is-no' : '') }}">
                <i class="fas {{ $asset['value'] === true ? 'fa-check' : ($asset['value'] === false ? 'fa-hourglass-half' : 'fa-question') }}"></i>
                {{ $asset['label'] }}
            </span>
        @endforeach
    </div>
</div>

@once
<style>
.dps { margin-bottom: 14px; padding: 16px; border-radius: 16px; border: 1px solid rgba(37,99,235,0.25); background: linear-gradient(135deg, rgba(37,99,235,0.06), transparent 60%); }
.dps-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.dps-icon { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; color: #fff; background: #2563eb; font-size: 17px; flex-shrink: 0; }
.dps-title { font-size: 15px; font-weight: 800; color: var(--text, #0f172a); }
.dps-sub { font-size: 12px; color: var(--text3, #94a3b8); }
.dps-due { margin-left: auto; font-size: 11.5px; font-weight: 800; padding: 4px 10px; border-radius: 99px; background: rgba(16,185,129,0.12); color: #047857; white-space: nowrap; }
.dps-due.is-soon { background: rgba(245,158,11,0.14); color: #b45309; }
.dps-due.is-late { background: rgba(239,68,68,0.12); color: #dc2626; }
.dps-dates { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
.dps-dates div { padding: 9px 11px; border-radius: 11px; background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); }
.dps-dates span { display: block; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--text3, #94a3b8); }
.dps-dates b { font-size: 13px; color: var(--text, #0f172a); }
.dps-label { margin: 14px 0 8px; font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .4px; color: var(--text3, #94a3b8); }
.dps-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.dps-chips span { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 9px; font-size: 12.5px; font-weight: 700; background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); color: var(--text, #0f172a); }
.dps-chips span b { width: 18px; height: 18px; border-radius: 6px; display: grid; place-items: center; font-size: 10px; background: rgba(37,99,235,0.1); color: #2563eb; }
.dps-chips.is-tech span { background: rgba(37,99,235,0.08); border-color: rgba(37,99,235,0.2); color: #1d4ed8; }
.dps-assets { display: flex; flex-wrap: wrap; gap: 6px; }
.dps-assets span { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 99px; font-size: 12px; font-weight: 700; border: 1px dashed var(--border, #cbd5e1); color: var(--text3, #94a3b8); }
.dps-assets span.is-yes { border-style: solid; border-color: rgba(16,185,129,0.35); background: rgba(16,185,129,0.1); color: #047857; }
.dps-assets span.is-no { border-style: solid; border-color: rgba(245,158,11,0.35); background: rgba(245,158,11,0.1); color: #b45309; }
body.dark-mode .dps-chips.is-tech span { color: #93c5fd; }
body.dark-mode .dps-assets span.is-yes { color: #34d399; }
body.dark-mode .dps-assets span.is-no { color: #fbbf24; }
@media (max-width: 640px) { .dps-dates { grid-template-columns: 1fr; } }
</style>
@endonce
