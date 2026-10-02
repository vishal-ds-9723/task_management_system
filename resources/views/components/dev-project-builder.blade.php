{{--
    Project setup for website / software / maintenance tasks, shared by the strategist
    create page and the admin create modal.

    Every value lives in a real named form field (radios, hidden inputs, dates) so form
    submission, validation errors (old()) and the strategist draft autosave keep working.
    The visual pieces are rebuilt from those fields whenever the task type changes.

    Props:
      id           - id of the root element (host pages toggle its visibility by id)
      members      - users who can be picked as the builder
      typeSelect   - CSS selector of the host form's task type <select>
      assignSelect - CSS selector of the host form's assigned_to <select> (kept in sync)
--}}
@props(['id', 'members' => collect(), 'typeSelect', 'assignSelect'])

@php
    $builderRoles = ['developer', 'designer'];
    $builders = collect($members)
        ->map(function ($member) use ($builderRoles) {
            $roles = method_exists($member, 'getAllRoles') ? $member->getAllRoles() : [$member->role];
            $kind = in_array('developer', $roles, true) ? 'developer' : (in_array('designer', $roles, true) ? 'designer' : null);
            return $kind ? (object) ['id' => $member->id, 'name' => $member->name, 'kind' => $kind] : null;
        })
        ->filter()
        ->sortBy('name')
        ->values();

    $workload = $builders->isEmpty() ? collect() : \App\Models\Task::query()
        ->whereIn('assigned_to', $builders->pluck('id'))
        ->whereIn('status', \App\Models\Task::ACTIVE_STATUSES)
        ->selectRaw('assigned_to, COUNT(*) as total')
        ->groupBy('assigned_to')
        ->pluck('total', 'assigned_to');

    $initials = fn (string $name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    $hue = fn (string $name) => 'hsl(' . (crc32($name) % 360) . ', 58%, 50%)';

    $businessIcons = [
        'business_website' => 'fa-briefcase', 'ecommerce' => 'fa-cart-shopping', 'landing_page' => 'fa-rocket',
        'portfolio' => 'fa-images', 'blog' => 'fa-newspaper', 'custom_software' => 'fa-gears', 'etc' => 'fa-ellipsis',
    ];
@endphp

<section id="{{ $id }}" class="dpb" data-dev-builder
         data-type-select="{{ $typeSelect }}" data-assign-select="{{ $assignSelect }}" style="display:none">

    {{-- ── Header with setup progress ── --}}
    <header class="dpb-head">
        <span class="dpb-head-icon"><i class="fas fa-globe" data-dpb-icon></i></span>
        <div class="dpb-head-text">
            <div class="dpb-head-title" data-dpb-title>Website project</div>
            <div class="dpb-head-sub" data-dpb-sub>Plan the pages, stack and handover for the build.</div>
        </div>
        <div class="dpb-progress" title="How much of the project setup is filled in">
            <svg viewBox="0 0 36 36" aria-hidden="true"><circle cx="18" cy="18" r="15.5" class="dpb-ring-bg"/><circle cx="18" cy="18" r="15.5" class="dpb-ring" data-dpb-ring/></svg>
            <span><b data-dpb-score>0</b>/5<small>set up</small></span>
        </div>
    </header>

    <ol class="dpb-checklist" aria-label="Setup checklist">
        <li data-check="scope"><i class="fas fa-circle-check"></i> Project type</li>
        <li data-check="pages"><i class="fas fa-circle-check"></i> <span data-dpb-pages-noun>Pages</span></li>
        <li data-check="builder"><i class="fas fa-circle-check"></i> Builder</li>
        <li data-check="deadline"><i class="fas fa-circle-check"></i> Handover date</li>
        <li data-check="assets"><i class="fas fa-circle-check"></i> Client assets</li>
    </ol>

    {{-- ── 1. Scope ── --}}
    <div class="dpb-step">
        <div class="dpb-step-head"><span class="dpb-num">1</span> What are we building?</div>

        <div class="dpb-label">Project type</div>
        <div class="dpb-types" role="radiogroup" aria-label="Project type">
            @foreach(\App\Models\Task::BUSINESS_TYPES as $value => $label)
                <label class="dpb-type">
                    <input type="radio" name="business_type" value="{{ $value }}" @checked(old('business_type') === $value)>
                    <span><i class="fas {{ $businessIcons[$value] ?? 'fa-shapes' }}"></i>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <div class="dpb-label"><span data-dpb-pages-label>Pages</span> <small data-dpb-pages-hint>Tap a suggestion or type your own and press Enter</small></div>
        <div class="dpb-chipbox" data-chipbox="modules">
            <div class="dpb-chips" data-chips></div>
            <input type="text" class="dpb-chip-input" data-chip-input maxlength="80" placeholder="Add a page…" aria-label="Add a page or module">
        </div>
        <div class="dpb-suggest" data-suggest="modules"></div>
        <textarea name="modules" data-store="modules" hidden>{{ old('modules') }}</textarea>

        <div class="dpb-label">Tech stack <small>Pick any, or leave empty for the builder to suggest</small></div>
        <div class="dpb-suggest is-toggle" data-suggest="tech"></div>
        <input type="hidden" name="preferred_tech" data-store="tech" value="{{ old('preferred_tech') }}">
        <div class="dpb-inline-add">
            <input type="text" class="dpb-input" data-tech-input maxlength="40" placeholder="Other tech… (press Enter)" aria-label="Add other technology">
        </div>

        <label class="dpb-switch">
            <input type="checkbox" name="has_wireframes" value="1" @checked(old('has_wireframes'))>
            <span class="dpb-switch-ui"></span>
            <span><b>Designs / wireframes are ready</b><small>The builder can start from approved layouts</small></span>
        </label>
    </div>

    {{-- ── 2. Builder ── --}}
    <div class="dpb-step">
        <div class="dpb-step-head"><span class="dpb-num">2</span> Who's building it?</div>
        @if($builders->isEmpty())
            <div class="dpb-empty">No developers or designers found. Leave it unassigned and assign later.</div>
        @else
            <div class="dpb-tabs" role="tablist">
                <button type="button" class="is-active" data-people-tab="all">All <span>{{ $builders->count() }}</span></button>
                <button type="button" data-people-tab="developer"><i class="fas fa-code"></i> Developers <span>{{ $builders->where('kind', 'developer')->count() }}</span></button>
                <button type="button" data-people-tab="designer"><i class="fas fa-pen-nib"></i> Designers <span>{{ $builders->where('kind', 'designer')->count() }}</span></button>
            </div>
            <div class="dpb-people" role="radiogroup" aria-label="Builder">
                <button type="button" class="dpb-person is-none" data-person="" role="radio" aria-checked="false">
                    <span class="dpb-avatar is-none"><i class="fas fa-user-plus"></i></span>
                    <span class="dpb-person-text"><b>Assign later</b><small>Leave unassigned</small></span>
                </button>
                @foreach($builders as $builder)
                    @php $load = (int) ($workload[$builder->id] ?? 0); @endphp
                    <button type="button" class="dpb-person" data-person="{{ $builder->id }}" data-kind="{{ $builder->kind }}" data-load="{{ $load }}" role="radio" aria-checked="false">
                        <span class="dpb-avatar" style="background: {{ $hue($builder->name) }}">{{ $initials($builder->name) }}</span>
                        <span class="dpb-person-text">
                            <b>{{ $builder->name }}</b>
                            <small><span class="dpb-role is-{{ $builder->kind }}">{{ ucfirst($builder->kind) }}</span> · {{ $load }} active</small>
                        </span>
                        <span class="dpb-badge" data-best hidden>Most available</span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── 3. Timeline ── --}}
    <div class="dpb-step">
        <div class="dpb-step-head"><span class="dpb-num">3</span> Timeline</div>
        <div class="dpb-dates">
            <label class="dpb-date"><span><i class="fas fa-flag"></i> Start</span><input type="date" class="dpb-input" name="project_start_date" data-date="start" value="{{ old('project_start_date') }}"></label>
            <label class="dpb-date"><span><i class="fas fa-box-archive"></i> Handover (dev deadline)</span><input type="date" class="dpb-input" name="dev_deadline" data-date="deadline" value="{{ old('dev_deadline') }}"></label>
            <label class="dpb-date"><span><i class="fas fa-rocket"></i> Go live</span><input type="date" class="dpb-input" name="launch_date" data-date="launch" value="{{ old('launch_date') }}"></label>
        </div>
        <div class="dpb-presets">
            <span>Build time:</span>
            <button type="button" data-preset="7">1 week</button>
            <button type="button" data-preset="14">2 weeks</button>
            <button type="button" data-preset="30">1 month</button>
            <button type="button" data-preset="60">2 months</button>
        </div>
        <div class="dpb-timeline" data-timeline>
            <div class="dpb-track"><span class="dpb-build" data-build></span><span class="dpb-qa" data-qa></span></div>
            <div class="dpb-timeline-text" data-timeline-text>Pick a handover date to see the build window.</div>
        </div>
        <div class="dpb-error" data-date-error hidden></div>
    </div>

    {{-- ── 4. Client handover ── --}}
    <div class="dpb-step">
        <div class="dpb-step-head">
            <span class="dpb-num">4</span> What has the client handed over?
            <span class="dpb-ready" data-ready>0/5 received</span>
        </div>
        <div class="dpb-bar"><span data-ready-bar></span></div>
        <div class="dpb-assets">
            @foreach(\App\Models\Task::PROJECT_ASSETS as $field => $label)
                @php $current = old($field); @endphp
                <div class="dpb-asset">
                    <span class="dpb-asset-name">{{ $label }}</span>
                    <div class="dpb-seg" role="radiogroup" aria-label="{{ $label }}">
                        <label class="is-yes"><input type="radio" name="{{ $field }}" value="1" @checked((string) $current === '1')><span><i class="fas fa-check"></i> Received</span></label>
                        <label class="is-no"><input type="radio" name="{{ $field }}" value="0" @checked((string) $current === '0')><span><i class="fas fa-hourglass-half"></i> Pending</span></label>
                        <label class="is-unknown"><input type="radio" name="{{ $field }}" value="" @checked($current === null || $current === '')><span>Not sure</span></label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@once
<style>
.dpb {
    --dpb-accent: #2563eb; --dpb-accent-soft: rgba(37,99,235,0.08); --dpb-accent-border: rgba(37,99,235,0.28);
    grid-column: 1 / -1; margin: 6px 0 4px; border-radius: 18px; overflow: hidden;
    border: 1px solid var(--dpb-accent-border); background: var(--card, #fff);
    box-shadow: 0 10px 30px -18px rgba(37,99,235,0.45); font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
}
.dpb[data-kind="software"] { --dpb-accent: #7c3aed; --dpb-accent-soft: rgba(124,58,237,0.08); --dpb-accent-border: rgba(124,58,237,0.28); }
.dpb[data-kind="maintenance"] { --dpb-accent: #0d9488; --dpb-accent-soft: rgba(13,148,136,0.08); --dpb-accent-border: rgba(13,148,136,0.3); }
.dpb *, .dpb *::before { box-sizing: border-box; }
.dpb [hidden] { display: none !important; }

.dpb-head { display: flex; align-items: center; gap: 14px; padding: 16px 18px; background: linear-gradient(135deg, var(--dpb-accent-soft), transparent 70%); border-bottom: 1px solid var(--dpb-accent-border); }
.dpb-head-icon { width: 44px; height: 44px; border-radius: 13px; display: grid; place-items: center; font-size: 19px; color: #fff; background: var(--dpb-accent); box-shadow: 0 8px 18px -8px var(--dpb-accent); flex-shrink: 0; }
.dpb-head-text { flex: 1; min-width: 0; }
.dpb-head-title { font-size: 16px; font-weight: 800; color: var(--text, #0f172a); }
.dpb-head-sub { font-size: 12px; color: var(--text3, #94a3b8); margin-top: 2px; }
.dpb-progress { position: relative; width: 66px; height: 66px; flex-shrink: 0; }
.dpb-progress svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.dpb-ring-bg { fill: none; stroke: var(--border, #e2e6ef); stroke-width: 3.5; }
.dpb-ring { fill: none; stroke: var(--dpb-accent); stroke-width: 3.5; stroke-linecap: round; stroke-dasharray: 97.4; stroke-dashoffset: 97.4; transition: stroke-dashoffset .35s ease; }
.dpb-progress > span { position: absolute; inset: 0; display: grid; place-content: center; text-align: center; font-size: 13px; font-weight: 800; color: var(--text, #0f172a); line-height: 1.05; }
.dpb-progress small { display: block; margin-top: 1px; font-size: 7.5px; font-weight: 700; color: var(--text3, #94a3b8); text-transform: uppercase; letter-spacing: .3px; }

.dpb-checklist { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; padding: 10px 18px; list-style: none; border-bottom: 1px solid var(--border, #e2e6ef); background: var(--card2, #f8fafc); }
.dpb-checklist li { display: inline-flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700; padding: 4px 10px; border-radius: 99px; color: var(--text3, #94a3b8); background: var(--card, #fff); border: 1px solid var(--border, #e2e6ef); }
.dpb-checklist li i { color: var(--border, #cbd5e1); }
.dpb-checklist li.is-done { color: var(--dpb-accent); border-color: var(--dpb-accent-border); background: var(--dpb-accent-soft); }
.dpb-checklist li.is-done i { color: var(--dpb-accent); }

.dpb-step { padding: 16px 18px 18px; border-bottom: 1px solid var(--border, #e2e6ef); }
.dpb-step:last-child { border-bottom: none; }
.dpb-step-head { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 800; color: var(--text, #0f172a); margin-bottom: 12px; }
.dpb-num { width: 24px; height: 24px; border-radius: 8px; display: grid; place-items: center; font-size: 12px; font-weight: 800; color: var(--dpb-accent); background: var(--dpb-accent-soft); border: 1px solid var(--dpb-accent-border); }
.dpb-label { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; font-size: 12px; font-weight: 800; color: var(--text2, #475569); margin: 14px 0 8px; }
.dpb-label:first-of-type { margin-top: 0; }
.dpb-label small { font-weight: 500; color: var(--text3, #94a3b8); }

.dpb-types { display: grid; grid-template-columns: repeat(auto-fill, minmax(165px, 1fr)); gap: 8px; }
.dpb-type input, .dpb-seg input, .dpb-switch input { position: absolute; opacity: 0; pointer-events: none; }
.dpb-type span {
    display: flex; align-items: center; gap: 9px; padding: 10px 12px; border-radius: 12px; cursor: pointer; font-size: 12.5px; font-weight: 700;
    border: 1.5px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text2, #475569); transition: all .15s;
}
.dpb-type span i { width: 26px; height: 26px; border-radius: 8px; display: grid; place-items: center; font-size: 12px; background: var(--card2, #f1f5f9); color: var(--text3, #94a3b8); flex-shrink: 0; }
.dpb-type span:hover { border-color: var(--dpb-accent-border); }
.dpb-type input:checked + span { border-color: var(--dpb-accent); background: var(--dpb-accent-soft); color: var(--text, #0f172a); box-shadow: 0 0 0 3px var(--dpb-accent-soft); }
.dpb-type input:checked + span i { background: var(--dpb-accent); color: #fff; }
.dpb-type input:focus-visible + span, .dpb-seg input:focus-visible + span { outline: 2px solid var(--dpb-accent); outline-offset: 2px; }

.dpb-chipbox {
    display: flex; flex-wrap: wrap; align-items: center; gap: 6px; min-height: 46px; padding: 7px 8px; border-radius: 12px; cursor: text;
    border: 1.5px solid var(--border, #e2e6ef); background: var(--card2, #f8fafc);
}
.dpb-chipbox:focus-within { border-color: var(--dpb-accent); box-shadow: 0 0 0 3px var(--dpb-accent-soft); background: var(--card, #fff); }
.dpb-chips { display: contents; }
.dpb-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 6px 5px 10px; border-radius: 9px; font-size: 12.5px; font-weight: 700; background: var(--dpb-accent); color: #fff; }
.dpb-chip b { font-size: 10px; opacity: .75; font-weight: 800; }
.dpb-chip button { width: 18px; height: 18px; border: none; border-radius: 6px; cursor: pointer; display: grid; place-items: center; background: rgba(255,255,255,0.22); color: #fff; font-size: 10px; padding: 0; }
.dpb-chip button:hover { background: rgba(255,255,255,0.4); }
.dpb-chip-input { flex: 1 1 140px; min-width: 120px; border: none !important; outline: none; background: transparent !important; box-shadow: none !important; font: inherit; font-size: 13px; color: var(--text, #0f172a); padding: 4px !important; height: auto !important; }
.dpb-suggest { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.dpb-suggest button {
    display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 99px; cursor: pointer; font: inherit; font-size: 12px; font-weight: 600;
    border: 1px dashed var(--dpb-accent-border); background: transparent; color: var(--dpb-accent); transition: all .15s;
}
.dpb-suggest button:hover { background: var(--dpb-accent-soft); border-style: solid; }
.dpb-suggest.is-toggle button { border-style: solid; border-color: var(--border, #e2e6ef); color: var(--text2, #475569); }
.dpb-suggest.is-toggle button.is-on { background: var(--dpb-accent); border-color: var(--dpb-accent); color: #fff; }
.dpb-suggest.is-toggle button.is-on::before { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; font-size: 9px; }
.dpb-inline-add { margin-top: 8px; max-width: 320px; }
.dpb-input {
    width: 100%; height: 40px; padding: 0 12px; border-radius: 11px; font: inherit; font-size: 13px;
    border: 1.5px solid var(--border, #e2e6ef); background: var(--card2, #f8fafc); color: var(--text, #0f172a);
}
.dpb-input:focus { outline: none; border-color: var(--dpb-accent); box-shadow: 0 0 0 3px var(--dpb-accent-soft); }

.dpb-switch { display: flex; align-items: center; gap: 12px; margin-top: 14px; padding: 10px 12px; border-radius: 12px; cursor: pointer; border: 1px solid var(--border, #e2e6ef); }
.dpb-switch-ui { position: relative; width: 38px; height: 22px; border-radius: 99px; background: #cbd5e1; flex-shrink: 0; transition: background .2s; }
.dpb-switch-ui::after { content: ''; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .2s; }
.dpb-switch input:checked + .dpb-switch-ui { background: var(--dpb-accent); }
.dpb-switch input:checked + .dpb-switch-ui::after { transform: translateX(16px); }
.dpb-switch b { display: block; font-size: 13px; color: var(--text, #0f172a); }
.dpb-switch small { font-size: 11.5px; color: var(--text3, #94a3b8); }

.dpb-tabs { display: inline-flex; gap: 4px; padding: 4px; border-radius: 11px; background: var(--card2, #f1f5f9); border: 1px solid var(--border, #e2e6ef); margin-bottom: 10px; flex-wrap: wrap; }
.dpb-tabs button { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border: none; border-radius: 8px; cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; background: transparent; color: var(--text2, #475569); }
.dpb-tabs button span { font-size: 10.5px; padding: 1px 6px; border-radius: 99px; background: var(--border, #e2e6ef); }
.dpb-tabs button.is-active { background: var(--card, #fff); color: var(--dpb-accent); box-shadow: 0 1px 3px rgba(15,23,42,0.08); }
.dpb-people { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 8px; max-height: 248px; overflow-y: auto; padding: 2px; }
.dpb-person {
    position: relative; display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 13px; cursor: pointer; text-align: left;
    font: inherit; color: inherit; border: 1.5px solid var(--border, #e2e6ef); background: var(--card, #fff); transition: all .15s;
}
.dpb-person:hover { border-color: var(--dpb-accent-border); }
.dpb-person[aria-checked="true"] { border-color: var(--dpb-accent); background: var(--dpb-accent-soft); box-shadow: 0 0 0 3px var(--dpb-accent-soft); }
.dpb-person[aria-checked="true"]::after { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; border-radius: 50%; display: grid; place-items: center; font-size: 9px; color: #fff; background: var(--dpb-accent); border: 2px solid var(--card, #fff); }
.dpb-avatar { width: 34px; height: 34px; border-radius: 50%; display: grid; place-items: center; font-size: 12px; font-weight: 800; color: #fff; flex-shrink: 0; }
.dpb-avatar.is-none { background: var(--card2, #f1f5f9); color: var(--text3, #94a3b8); border: 1.5px dashed var(--border, #cbd5e1); }
.dpb-person-text { min-width: 0; flex: 1; }
.dpb-person-text b { display: block; font-size: 13px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text, #0f172a); }
.dpb-person-text small { font-size: 11px; color: var(--text3, #94a3b8); }
.dpb-role { font-weight: 800; }
.dpb-role.is-developer { color: #2563eb; }
.dpb-role.is-designer { color: #db2777; }
.dpb-badge { position: absolute; bottom: -7px; left: 12px; font-size: 9.5px; font-weight: 800; padding: 1px 7px; border-radius: 99px; color: #047857; background: #d1fae5; border: 1px solid #a7f3d0; }
.dpb-empty { font-size: 12.5px; color: var(--text3, #94a3b8); padding: 10px 12px; border-radius: 12px; background: var(--card2, #f8fafc); }

.dpb-dates { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
.dpb-date span { display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700; color: var(--text2, #475569); margin-bottom: 6px; }
.dpb-date span i { color: var(--dpb-accent); font-size: 11px; }
.dpb-presets { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 10px; font-size: 12px; color: var(--text3, #94a3b8); font-weight: 600; }
.dpb-presets button { padding: 5px 11px; border-radius: 99px; cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; border: 1px solid var(--border, #e2e6ef); background: var(--card, #fff); color: var(--text2, #475569); }
.dpb-presets button:hover { border-color: var(--dpb-accent); color: var(--dpb-accent); }
.dpb-timeline { margin-top: 12px; padding: 12px 14px; border-radius: 12px; background: var(--card2, #f8fafc); border: 1px solid var(--border, #e2e6ef); }
.dpb-track { position: relative; height: 10px; border-radius: 99px; background: var(--border, #e2e6ef); overflow: hidden; display: flex; }
.dpb-build { height: 100%; width: 0; background: var(--dpb-accent); transition: width .3s; }
.dpb-qa { height: 100%; width: 0; background: repeating-linear-gradient(45deg, #f59e0b, #f59e0b 6px, #fbbf24 6px, #fbbf24 12px); transition: width .3s; }
.dpb-timeline-text { margin-top: 8px; font-size: 12.5px; color: var(--text2, #475569); font-weight: 600; }
.dpb-timeline-text b { color: var(--text, #0f172a); }
.dpb-error { margin-top: 10px; padding: 9px 12px; border-radius: 10px; font-size: 12.5px; font-weight: 700; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; }

.dpb-ready { margin-left: auto; font-size: 11.5px; font-weight: 800; padding: 3px 10px; border-radius: 99px; color: var(--dpb-accent); background: var(--dpb-accent-soft); }
.dpb-bar { height: 6px; border-radius: 99px; background: var(--border, #e2e6ef); overflow: hidden; margin: -2px 0 12px; }
.dpb-bar span { display: block; height: 100%; width: 0; border-radius: inherit; background: linear-gradient(90deg, #10b981, #34d399); transition: width .3s; }
.dpb-assets { display: grid; gap: 8px; }
.dpb-asset { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 8px 8px 14px; border-radius: 12px; border: 1px solid var(--border, #e2e6ef); }
.dpb-asset-name { font-size: 13px; font-weight: 700; color: var(--text, #0f172a); }
.dpb-seg { display: inline-flex; padding: 3px; gap: 3px; border-radius: 10px; background: var(--card2, #f1f5f9); }
.dpb-seg span { display: inline-flex; align-items: center; gap: 5px; padding: 6px 10px; border-radius: 8px; cursor: pointer; font-size: 11.5px; font-weight: 700; color: var(--text3, #94a3b8); white-space: nowrap; transition: all .15s; }
.dpb-seg .is-yes input:checked + span { background: #10b981; color: #fff; }
.dpb-seg .is-no input:checked + span { background: #f59e0b; color: #fff; }
.dpb-seg .is-unknown input:checked + span { background: var(--card, #fff); color: var(--text2, #475569); box-shadow: 0 1px 3px rgba(15,23,42,0.1); }

body.dark-mode .dpb-badge { background: rgba(16,185,129,0.15); border-color: rgba(16,185,129,0.35); color: #34d399; }
body.dark-mode .dpb-error { background: rgba(239,68,68,0.12); border-color: rgba(239,68,68,0.35); color: #fca5a5; }
@media (max-width: 640px) {
    .dpb-dates { grid-template-columns: 1fr; }
    .dpb-asset { flex-direction: column; align-items: flex-start; }
    .dpb-head { padding: 14px; }
    .dpb-step { padding: 14px; }
}
</style>

<script>
(function () {
    const CONFIG = {
        website: {
            icon: 'fa-globe', title: 'Website project', sub: 'Plan the pages, stack and client handover for the build.',
            noun: 'Pages', placeholder: 'Add a page…',
            items: ['Home', 'About', 'Services', 'Contact', 'Blog', 'Gallery', 'Shop', 'Checkout', 'FAQ', 'Privacy policy'],
            tech: ['WordPress', 'Laravel', 'Shopify', 'React / Next.js', 'Webflow', 'HTML / CSS'],
        },
        software: {
            icon: 'fa-laptop-code', title: 'Software project', sub: 'List the modules, stack and what the client still owes.',
            noun: 'Modules', placeholder: 'Add a module…',
            items: ['Login & roles', 'Dashboard', 'User management', 'Reports', 'Billing', 'Notifications', 'Settings', 'Admin panel', 'API', 'Mobile app'],
            tech: ['Laravel', 'Node.js', 'React', 'Vue', 'Flutter', 'React Native', 'MySQL'],
        },
        maintenance: {
            icon: 'fa-screwdriver-wrench', title: 'Maintenance / support', sub: 'What needs fixing or updating, and by when.',
            noun: 'Work items', placeholder: 'Add a work item…',
            items: ['Bug fixes', 'Speed optimisation', 'Security updates', 'Content update', 'Plugin / package updates', 'Backup & restore', 'SEO fixes'],
            tech: ['WordPress', 'Laravel', 'Shopify', 'React', 'Hosting / server'],
        },
    };
    const ASSET_FIELDS = @json(array_keys(\App\Models\Task::PROJECT_ASSETS));
    const DAY = 86400000;

    function fire(el) { el.dispatchEvent(new Event('change', { bubbles: true })); }
    function splitList(value, pattern) { return (value || '').split(pattern).map(s => s.replace(/^\s*(\d+[.)]|[-*•])\s*/, '').trim()).filter(Boolean); }
    function parseDate(value) { return value ? new Date(value + 'T00:00:00') : null; }
    function iso(date) { return new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 10); }
    function fmt(date) { return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }); }

    function init(root) {
        if (root.dataset.ready) return;
        root.dataset.ready = '1';

        const form = root.closest('form');
        const typeSelect = form?.querySelector(root.dataset.typeSelect) || document.querySelector(root.dataset.typeSelect);
        const assignSelect = form?.querySelector(root.dataset.assignSelect) || document.querySelector(root.dataset.assignSelect);
        const assignGroup = assignSelect?.closest('.form-group');
        const modulesStore = root.querySelector('[data-store="modules"]');
        const techStore = root.querySelector('[data-store="tech"]');
        const chipsEl = root.querySelector('[data-chips]');
        const chipInput = root.querySelector('[data-chip-input]');
        const dates = {
            start: root.querySelector('[data-date="start"]'),
            deadline: root.querySelector('[data-date="deadline"]'),
            launch: root.querySelector('[data-date="launch"]'),
        };
        let kind = 'website';
        let peopleTab = 'all';

        // ── Pages / modules (stored newline-separated in a hidden textarea) ──
        const getModules = () => splitList(modulesStore.value, /\r?\n|,|•/);
        function setModules(list) {
            modulesStore.value = Array.from(new Set(list)).join('\n');
            fire(modulesStore);
            renderModules();
        }
        function renderModules() {
            const list = getModules();
            chipsEl.replaceChildren(...list.map((name, i) => {
                const chip = document.createElement('span');
                chip.className = 'dpb-chip';
                const num = document.createElement('b');
                num.textContent = i + 1;
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.setAttribute('aria-label', 'Remove ' + name);
                remove.innerHTML = '<i class="fas fa-xmark"></i>';
                remove.addEventListener('click', () => setModules(getModules().filter(m => m !== name)));
                chip.append(num, ' ' + name, remove);
                return chip;
            }));
            const box = root.querySelector('[data-suggest="modules"]');
            box.replaceChildren(...CONFIG[kind].items.filter(item => !list.includes(item)).map(item => {
                const b = document.createElement('button');
                b.type = 'button';
                b.innerHTML = '<i class="fas fa-plus"></i> ';
                b.append(item);
                b.addEventListener('click', () => setModules([...getModules(), item]));
                return b;
            }));
            update();
        }
        chipInput.addEventListener('keydown', (e) => {
            if ((e.key === 'Enter' || e.key === ',') && chipInput.value.trim()) {
                e.preventDefault();
                setModules([...getModules(), ...splitList(chipInput.value, /,/)]);
                chipInput.value = '';
            } else if (e.key === 'Enter') {
                e.preventDefault();
            } else if (e.key === 'Backspace' && !chipInput.value && getModules().length) {
                setModules(getModules().slice(0, -1));
            }
        });
        chipInput.addEventListener('blur', () => {
            if (chipInput.value.trim()) { setModules([...getModules(), ...splitList(chipInput.value, /,/)]); chipInput.value = ''; }
        });
        root.querySelector('[data-chipbox]').addEventListener('click', () => chipInput.focus());

        // ── Tech stack (stored comma-separated, max 100 chars server-side) ──
        const getTech = () => splitList(techStore.value, /,/);
        function setTech(list) {
            let joined = Array.from(new Set(list)).join(', ');
            while (joined.length > 100 && list.length) { list = list.slice(0, -1); joined = list.join(', '); }
            techStore.value = joined;
            fire(techStore);
            renderTech();
        }
        function renderTech() {
            const current = getTech();
            const options = Array.from(new Set([...CONFIG[kind].tech, ...current]));
            const box = root.querySelector('[data-suggest="tech"]');
            box.replaceChildren(...options.map(item => {
                const b = document.createElement('button');
                b.type = 'button';
                b.textContent = item;
                b.classList.toggle('is-on', current.includes(item));
                b.setAttribute('aria-pressed', current.includes(item) ? 'true' : 'false');
                b.addEventListener('click', () => {
                    const now = getTech();
                    setTech(now.includes(item) ? now.filter(t => t !== item) : [...now, item]);
                });
                return b;
            }));
        }
        root.querySelector('[data-tech-input]').addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const value = e.target.value.trim();
            if (value) setTech([...getTech(), value]);
            e.target.value = '';
        });

        // ── Builder picker (mirrors the host form's assigned_to select) ──
        const people = Array.from(root.querySelectorAll('[data-person]'));
        function renderPeople() {
            const selected = assignSelect ? assignSelect.value : '';
            const visible = people.filter(p => !p.dataset.kind || peopleTab === 'all' || p.dataset.kind === peopleTab);
            people.forEach(p => {
                p.hidden = !visible.includes(p);
                p.setAttribute('aria-checked', p.dataset.person === selected ? 'true' : 'false');
                p.querySelector('[data-best]')?.setAttribute('hidden', '');
            });
            const candidates = visible.filter(p => p.dataset.kind);
            if (candidates.length > 1) {
                const best = candidates.reduce((a, b) => Number(b.dataset.load) < Number(a.dataset.load) ? b : a);
                best.querySelector('[data-best]')?.removeAttribute('hidden');
            }
            root.querySelectorAll('[data-people-tab]').forEach(t => t.classList.toggle('is-active', t.dataset.peopleTab === peopleTab));
            update();
        }
        people.forEach(p => p.addEventListener('click', () => {
            if (!assignSelect) return;
            assignSelect.value = p.dataset.person;
            fire(assignSelect);
            renderPeople();
        }));
        root.querySelectorAll('[data-people-tab]').forEach(t => t.addEventListener('click', () => { peopleTab = t.dataset.peopleTab; renderPeople(); }));
        assignSelect?.addEventListener('change', renderPeople);

        // ── Timeline ──
        function renderTimeline() {
            const start = parseDate(dates.start.value);
            const deadline = parseDate(dates.deadline.value);
            const launch = parseDate(dates.launch.value);
            const errorEl = root.querySelector('[data-date-error]');
            const textEl = root.querySelector('[data-timeline-text]');
            const build = root.querySelector('[data-build]');
            const qa = root.querySelector('[data-qa]');
            const problems = [];
            if (start && deadline && deadline < start) problems.push('Handover is before the start date.');
            if (start && launch && launch < start) problems.push('Go-live is before the start date.');
            if (deadline && launch && launch < deadline) problems.push('Go-live is before handover. Leave time to test.');
            errorEl.hidden = !problems.length;
            errorEl.textContent = problems.join(' ');

            const from = start || new Date(new Date().setHours(0, 0, 0, 0));
            if (!deadline || problems.length) {
                build.style.width = qa.style.width = '0';
                textEl.innerHTML = deadline ? 'Fix the dates to see the build window.' : 'Pick a handover date to see the build window.';
                return;
            }
            const buildDays = Math.max(0, Math.round((deadline - from) / DAY));
            const qaDays = launch ? Math.max(0, Math.round((launch - deadline) / DAY)) : 0;
            const total = Math.max(1, buildDays + qaDays);
            build.style.width = (buildDays / total * 100) + '%';
            qa.style.width = (qaDays / total * 100) + '%';
            textEl.innerHTML = '';
            const b = document.createElement('b');
            b.textContent = buildDays + ' day' + (buildDays === 1 ? '' : 's');
            textEl.append('Build ', b, ' (' + fmt(from) + ' → ' + fmt(deadline) + ')');
            if (launch) {
                const q = document.createElement('b');
                q.textContent = qaDays + ' day' + (qaDays === 1 ? '' : 's');
                textEl.append(' · testing & launch ', q, ' → live ' + fmt(launch));
            }
        }
        Object.values(dates).forEach(input => input.addEventListener('change', () => { renderTimeline(); update(); }));
        root.querySelectorAll('[data-preset]').forEach(btn => btn.addEventListener('click', () => {
            if (!dates.start.value) { dates.start.value = iso(new Date()); fire(dates.start); }
            const start = parseDate(dates.start.value);
            dates.deadline.value = iso(new Date(start.getTime() + Number(btn.dataset.preset) * DAY));
            fire(dates.deadline);
            if (!dates.launch.value || parseDate(dates.launch.value) < parseDate(dates.deadline.value)) {
                dates.launch.value = iso(new Date(parseDate(dates.deadline.value).getTime() + 3 * DAY));
                fire(dates.launch);
            }
            renderTimeline();
            update();
        }));

        // ── Assets readiness ──
        root.querySelectorAll('.dpb-seg input').forEach(r => r.addEventListener('change', update));
        root.querySelectorAll('input[name="business_type"]').forEach(r => r.addEventListener('change', update));

        function update() {
            const received = ASSET_FIELDS.filter(name => root.querySelector(`input[name="${name}"]:checked`)?.value === '1').length;
            const answered = ASSET_FIELDS.filter(name => (root.querySelector(`input[name="${name}"]:checked`)?.value ?? '') !== '').length;
            root.querySelector('[data-ready]').textContent = received + '/' + ASSET_FIELDS.length + ' received';
            root.querySelector('[data-ready-bar]').style.width = (received / ASSET_FIELDS.length * 100) + '%';

            const checks = {
                scope: !!root.querySelector('input[name="business_type"]:checked'),
                pages: getModules().length > 0,
                builder: !!(assignSelect && assignSelect.value),
                deadline: !!dates.deadline.value && root.querySelector('[data-date-error]').hidden,
                assets: answered === ASSET_FIELDS.length,
            };
            const score = Object.values(checks).filter(Boolean).length;
            Object.entries(checks).forEach(([key, done]) => root.querySelector(`[data-check="${key}"]`)?.classList.toggle('is-done', done));
            root.querySelector('[data-dpb-score]').textContent = score;
            root.querySelector('[data-dpb-ring]').style.strokeDashoffset = 97.4 - (97.4 * score / 5);
        }

        // ── Type switching (also re-syncs after drafts or old() repopulate the fields) ──
        function applyType() {
            const type = (typeSelect?.value || '').toLowerCase();
            const active = Object.prototype.hasOwnProperty.call(CONFIG, type);
            root.style.display = active ? '' : 'none';
            if (assignGroup) assignGroup.style.display = active && people.length ? 'none' : '';
            if (!active) return;

            kind = type;
            const cfg = CONFIG[kind];
            root.dataset.kind = kind;
            root.querySelector('[data-dpb-icon]').className = 'fas ' + cfg.icon;
            root.querySelector('[data-dpb-title]').textContent = cfg.title;
            root.querySelector('[data-dpb-sub]').textContent = cfg.sub;
            root.querySelector('[data-dpb-pages-label]').textContent = cfg.noun;
            root.querySelector('[data-dpb-pages-noun]').textContent = cfg.noun;
            chipInput.placeholder = cfg.placeholder;
            renderModules();
            renderTech();
            renderPeople();
            renderTimeline();
        }
        typeSelect?.addEventListener('change', () => setTimeout(applyType, 0));
        root.devBuilderSync = applyType;
        applyType();
    }

    function boot() { document.querySelectorAll('[data-dev-builder]').forEach(init); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot); else boot();
})();
</script>
@endonce
