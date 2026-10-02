@php
    $platformIcons = [
        'instagram' => 'fa-brands fa-instagram',
        'facebook' => 'fa-brands fa-facebook-f',
        'linkedin' => 'fa-brands fa-linkedin-in',
        'twitter' => 'fa-brands fa-x-twitter',
        'tiktok' => 'fa-brands fa-tiktok',
        'youtube' => 'fa-brands fa-youtube',
    ];
@endphp

@if($tasks->count())
    <div class="pc-grid">
        @foreach($tasks as $task)
        <a href="{{ route('client.tasks.show', $task->id) }}" class="pc-card">
            <div class="pc-card-media">
                @if($task->media->count())
                    <img src="{{ Storage::url($task->media->first()->path) }}" alt="{{ $task->title }}">
                @else
                    <div style="display:flex; align-items:center; justify-content:center; height:100%; color:var(--text3); opacity:0.3">
                        <i class="fa-solid fa-photo-film" style="font-size:40px"></i>
                    </div>
                @endif
                <span class="pc-badge-type">{{ ucfirst($task->type) }}</span>
            </div>

            <div class="pc-card-body">
                <div class="pc-card-title">{{ $task->title ?: 'Untitled Content' }}</div>
                <div class="pc-card-desc">{{ $task->caption ?: 'No caption provided.' }}</div>

                <div class="pc-plat-row">
                    @foreach(is_array($task->platform) ? $task->platform : [] as $p)
                        <span class="pc-plat-tag">
                            <i class="fa-brands fa-{{ $p === 'twitter' ? 'x-twitter' : $p }}"></i>
                            {{ ucfirst($p) }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="pc-card-footer">
                <div class="pc-card-date">
                    <i class="fa-solid fa-calendar-check"></i>
                    {{ $task->completed_at ? $task->completed_at->format('M d, Y') : 'Published' }}
                </div>
                <div style="color:var(--primary); font-size:12px; font-weight:800;">
                    View Details <i class="fa-solid fa-arrow-right" style="margin-left:4px"></i>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <div class="pc-pagination">
        {{ $tasks->links() }}
    </div>
@else
    <div class="pc-empty">
        <div class="pc-empty-icon"><i class="fa-solid fa-bullhorn"></i></div>
        <div class="pc-empty-title">No published content yet</div>
        <div class="pc-empty-text">Your published posts, stories and reels will appear here once the team publishes them.</div>
    </div>
@endif
