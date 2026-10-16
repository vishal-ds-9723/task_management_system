@foreach($tasks as $task)
    @php
        $statusSlug  = $task->status === 'pending_approval' ? 'review' : $task->status;
        $statusGroup = in_array($statusSlug, ['inprogress', 'review']) ? 'active' : ($statusSlug === 'todo' ? 'queue' : 'done');

        $typeStr = strtolower((string)$task->type);
        $typeGroup = match($typeStr) {
            'reel', 'video'              => 'video',
            'carousel'                   => 'carousel',
            'post', 'static'             => 'post',
            'story'                      => 'story',
            'logo', 'branding'           => 'branding',
            'flyer', 'banner', 'brochure'=> 'print',
            default                      => 'other'
        };

        $typeConfig = match($typeStr) {
            'reel'     => ['label' => 'Reel', 'icon' => 'fa-solid fa-clapperboard', 'color' => '#FF5376', 'class' => 'type-reel'],
            'video'    => ['label' => 'Video', 'icon' => 'fa-solid fa-film', 'color' => '#8B5CF6', 'class' => 'type-video'],
            'carousel' => ['label' => 'Carousel', 'icon' => 'fa-solid fa-layer-group', 'color' => '#A855F7', 'class' => 'type-carousel'],
            'post'     => ['label' => 'Static Post', 'icon' => 'fa-solid fa-image', 'color' => '#3B82F6', 'class' => 'type-post'],
            'story'    => ['label' => 'Story', 'icon' => 'fa-solid fa-mobile-screen-button', 'color' => '#F59E0B', 'class' => 'type-story'],
            'logo'     => ['label' => 'Logo', 'icon' => 'fa-solid fa-pen-nib', 'color' => '#10B981', 'class' => 'type-logo'],
            'branding' => ['label' => 'Branding', 'icon' => 'fa-solid fa-shapes', 'color' => '#10B981', 'class' => 'type-branding'],
            default    => ['label' => ucfirst($task->type ?: 'Creative'), 'icon' => 'fa-solid fa-palette', 'color' => '#6366F1', 'class' => 'type-default'],
        };

        $platforms = $task->platformList();
        $isDone = in_array($task->status, ['completed', 'published']);
    @endphp
    <tr class="table-task-row {{ $isDone ? 'is-done-row' : '' }}"
        data-task-id="{{ $task->id }}"
        data-href="{{ route('designer.tasks.show', $task) }}"
        data-status="{{ $statusSlug }}"
        data-status-group="{{ $statusGroup }}"
        data-medium="{{ $typeGroup }}"
        data-client-id="{{ $task->client_id ?? '' }}"
        data-revision="{{ $task->revision_count > 0 && !in_array($task->status, ['completed', 'published']) ? '1' : '0' }}"
        tabindex="0"
        role="link"
        aria-label="Open deliverable {{ $task->title }}"
        onclick="if(!event.target.closest('a, button, input, select, textarea')) { if(event.ctrlKey || event.metaKey || event.button === 1) { window.open('{{ route('designer.tasks.show', $task) }}', '_blank'); } else { window.location.href = '{{ route('designer.tasks.show', $task) }}'; } }"
        style="cursor:pointer">
        
        {{-- ID Column --}}
        <td>
            <span style="font-size:11.5px;font-weight:800;color:var(--dz-text-muted);font-family:monospace">
                #{{ str_pad($task->id, 4, '0', STR_PAD_LEFT) }}
            </span>
        </td>

        {{-- Task Title & Meta --}}
        <td>
            <div style="display:flex;flex-direction:column;gap:3px">
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                    <a href="{{ route('designer.tasks.show', $task) }}" style="font-weight:700;font-size:13.5px;color:var(--dz-text-main);text-decoration:none;transition:color 0.15s ease" onmouseover="this.style.color='#4F46E5'" onmouseout="this.style.color='var(--dz-text-main)'">
                        {{ $task->title }}
                    </a>
                    @if($task->priority === 'urgent')
                        <span class="studio-badge-urgent" style="font-size:9.5px;padding:1px 5px">URGENT</span>
                    @endif
                    @if($task->revision_count > 0 && !in_array($task->status, ['completed', 'published']))
                        <span class="studio-badge-revision" style="font-size:9.5px;padding:1px 5px">⟲ Rev #{{ $task->revision_count }}</span>
                    @endif
                    @if($task->is_paused)
                        <span class="studio-badge-paused" style="font-size:9.5px;padding:1px 5px">⏸️ Paused</span>
                    @endif
                </div>
                @if($task->brief)
                    <div style="font-size:11px;color:var(--dz-text-muted);max-width:340px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ Str::limit($task->brief, 48) }}
                    </div>
                @endif
            </div>
        </td>

        {{-- Client Column --}}
        <td>
            <div style="display:flex;align-items:center;gap:7px">
                <x-client-branding :client="$task->client" size="20px" radius="4px" fit="contain" />
                <span style="font-weight:700;font-size:12.5px;color:var(--dz-text-sub)">{{ $task->client?->name ?? 'Internal' }}</span>
            </div>
        </td>

        {{-- Format Column --}}
        <td>
            <span class="studio-type-pill {{ $typeConfig['class'] }}" style="font-size:10.5px;padding:2px 7px">
                <i class="{{ $typeConfig['icon'] }}"></i> {{ $typeConfig['label'] }}
            </span>
        </td>

        {{-- Platforms Column --}}
        <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap">
                @forelse($platforms as $plat)
                    <span class="platform-brand-pill platform-{{ strtolower($plat) }}" style="font-size:9.5px;padding:1px 5px">
                        {{ ucfirst(strtolower($plat) === 'twitter' ? 'X' : $plat) }}
                    </span>
                @empty
                    <span style="font-size:11px;color:var(--dz-text-muted)">—</span>
                @endforelse
            </div>
        </td>

        {{-- Status Column --}}
        <td>
            @if($isDone)
                <span class="status status-done-pill" style="font-size:10.5px;padding:2px 8px">
                    <i class="fa-solid fa-circle-check"></i> {{ $task->status_label }}
                </span>
            @else
                <span class="status {{ $task->status_class }}" style="font-size:10.5px;padding:2px 8px">
                    {{ $task->status_label }}
                </span>
            @endif
        </td>

        {{-- Deadline Column --}}
        <td>
            <div style="font-size:12px;font-weight:{{ $task->isOverdue() ? '800' : '600' }};color:{{ $task->isOverdue() ? '#DC2626' : 'var(--dz-text-sub)' }};white-space:nowrap">
                <i class="fa-regular fa-calendar" style="font-size:10.5px"></i>
                {{ $task->deadline?->format('d M') ?? 'Flexible' }}
                @if($task->isOverdue())
                    <span style="font-size:10px;color:#DC2626">(Overdue)</span>
                @endif
            </div>
        </td>

        {{-- Action Column --}}
        <td style="text-align:right">
            <a href="{{ route('designer.tasks.show', $task) }}" class="btn-sec" style="font-size:11.5px !important;padding:4px 10px !important;display:inline-flex;align-items:center;gap:5px" title="View deliverable details">
                <span>View</span>
                <i class="fa-solid fa-arrow-right" style="font-size:9px"></i>
            </a>
        </td>
    </tr>
@endforeach
