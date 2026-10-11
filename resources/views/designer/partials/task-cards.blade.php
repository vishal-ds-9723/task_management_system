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

        $typeConfig = match($typeGroup) {
            'video'    => [
                'class' => 'type-video',
                'label' => 'Video',
                'icon'  => 'fa-solid fa-film',
            ],
            'carousel' => [
                'class' => 'type-carousel',
                'label' => 'Carousel',
                'icon'  => 'fa-solid fa-layer-group',
            ],
            'post' => [
                'class' => 'type-post',
                'label' => 'Static Post',
                'icon'  => 'fa-solid fa-image',
            ],
            'story' => [
                'class' => 'type-story',
                'label' => 'Story',
                'icon'  => 'fa-solid fa-mobile-screen-button',
            ],
            'branding' => [
                'class' => 'type-branding',
                'label' => 'Branding Kit',
                'icon'  => 'fa-solid fa-shapes',
            ],
            'print' => [
                'class' => 'type-print',
                'label' => ucfirst($task->type),
                'icon'  => 'fa-solid fa-newspaper',
            ],
            default => [
                'class' => 'type-default',
                'label' => ucfirst($task->type ?: 'Creative'),
                'icon'  => 'fa-solid fa-palette',
            ],
        };

        // Deadline calculations
        $deadlineText  = 'No deadline';
        $deadlineClass = '';
        if ($task->deadline) {
            if ($task->isOverdue()) {
                $deadlineClass = 'is-overdue';
                $deadlineText  = 'Overdue · ' . $task->deadline->format('d M');
            } elseif ($task->deadline->isToday()) {
                $deadlineClass = 'is-today';
                $deadlineText  = 'Due Today';
            } elseif ($task->deadline->isTomorrow()) {
                $deadlineClass = 'is-tomorrow';
                $deadlineText  = 'Tomorrow';
            } else {
                $deadlineText  = $task->deadline->format('d M');
            }
        }

        $platforms = $task->platformList();
        $isDone = in_array($task->status, ['completed', 'published']);
    @endphp
    <a href="{{ route('designer.tasks.show', $task) }}" 
       class="studio-card task-item-container {{ $typeConfig['class'] }} {{ $isDone ? 'is-done-card' : '' }}" 
       data-task-id="{{ $task->id }}"
       data-status="{{ $statusSlug }}"
       data-status-group="{{ $statusGroup }}"
       data-medium="{{ $typeGroup }}"
       data-client-id="{{ $task->client_id ?? '' }}"
       data-revision="{{ $task->revision_count > 0 && !in_array($task->status, ['completed', 'published']) ? '1' : '0' }}">
        
        {{-- Top Dynamic Medium Ribbon --}}
        <div class="studio-card-ribbon"></div>

        <div class="studio-card-inner">
            {{-- Header: Client & Status --}}
            <div class="studio-card-header">
                <div class="studio-card-client">
                    <div class="studio-client-avatar">
                        <x-client-branding :client="$task->client" size="22px" radius="4px" fit="contain" />
                    </div>
                    <span class="studio-client-name">{{ $task->client?->name ?? 'Internal' }}</span>
                </div>
                @if($isDone)
                    <span class="status status-done-pill">
                        <i class="fa-solid fa-circle-check"></i> {{ $task->status_label }}
                    </span>
                @else
                    <span class="status {{ $task->status_class }}">{{ $task->status_label }}</span>
                @endif
            </div>

            {{-- Body: Title & Creative Medium Chips --}}
            <div>
                <h3 class="studio-card-title">{{ $task->title }}</h3>
                
                <div class="studio-tag-row">
                    {{-- Deliverable Medium Pill --}}
                    <span class="studio-type-pill {{ $typeConfig['class'] }}">
                        <i class="{{ $typeConfig['icon'] }}"></i> {{ $typeConfig['label'] }}
                    </span>

                    {{-- Platform Badges --}}
                    @foreach($platforms as $plat)
                        @php
                            $p = strtolower($plat);
                            $platClass = match($p) {
                                'instagram' => 'platform-instagram',
                                'facebook'  => 'platform-facebook',
                                'linkedin'  => 'platform-linkedin',
                                'twitter', 'x' => 'platform-twitter',
                                'whatsapp'  => 'platform-whatsapp',
                                'youtube'   => 'platform-youtube',
                                default     => 'platform-default',
                            };
                            $platIcon = match($p) {
                                'instagram' => 'fa-brands fa-instagram',
                                'facebook'  => 'fa-brands fa-facebook-f',
                                'linkedin'  => 'fa-brands fa-linkedin-in',
                                'twitter', 'x' => 'fa-brands fa-x-twitter',
                                'whatsapp'  => 'fa-brands fa-whatsapp',
                                'youtube'   => 'fa-brands fa-youtube',
                                default     => 'fa-solid fa-share-nodes',
                            };
                        @endphp
                        <span class="platform-brand-pill {{ $platClass }}">
                            <i class="{{ $platIcon }}"></i>
                            <span>{{ ucfirst($p === 'twitter' ? 'X' : $plat) }}</span>
                        </span>
                    @endforeach

                    {{-- Revision Alert Tag --}}
                    @if($task->revision_count > 0 && !in_array($task->status, ['completed', 'published']))
                        <span class="studio-badge-revision">
                            <i class="fa-solid fa-rotate-left"></i> Rev #{{ $task->revision_count }}
                        </span>
                    @endif

                    {{-- Urgent Badge --}}
                    @if($task->priority === 'urgent')
                        <span class="studio-badge-urgent">
                            <i class="fa-solid fa-bolt"></i> URGENT
                        </span>
                    @endif

                    {{-- Paused Badge --}}
                    @if($task->is_paused)
                        <span class="studio-badge-paused">
                            <i class="fa-solid fa-pause"></i> Paused
                        </span>
                    @endif
                </div>
            </div>

            {{-- Footer: Deadline & Open Workspace --}}
            <div class="studio-card-footer">
                <div class="studio-deadline-tag {{ $deadlineClass }}">
                    <i class="fa-regular fa-calendar"></i>
                    <span>{{ $deadlineText }}</span>
                </div>
                <span class="studio-card-cta">
                    View Task <i class="fa-solid fa-arrow-right" style="font-size:10px"></i>
                </span>
            </div>
        </div>
    </a>
@endforeach
