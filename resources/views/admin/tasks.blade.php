@extends('layouts.app')

@section('content')
<div class="tasks-container" style="padding: 30px;">
    <!-- Page Header -->
    <div class="tasks-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="margin: 0; font-size: 28px; color: var(--text); display: flex; align-items: center; gap: 10px;">
                Tasks Management
            </h1>
            <p style="margin: 5px 0 0 0; color: var(--text2); font-size: 13px;">
                {{ $tasks->total() }} total tasks • Showing {{ $tasks->perPage() }} per page
            </p>
        </div>
        <div class="tasks-header-actions" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.audit-logs') }}" class="task-btn-secondary"
                style="padding: 10px 16px; background: var(--card2); border: 1px solid var(--border); border-radius: 8px; text-decoration: none; color: var(--text); cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-weight: 500;">
                <i class="fas fa-history"></i><span class="btn-text">Audit Logs</span>
            </a>
            <button onclick="viewMode('kanban')" id="btn-kanban" class="task-btn-secondary"
                style="padding: 10px 16px; background: var(--card2); border: 1px solid var(--border); border-radius: 8px; cursor: pointer; font-weight: 500;">
                <i class="fas fa-th-large" style="margin-right: 6px;"></i><span class="btn-text">Kanban</span>
            </button>
            <button onclick="viewMode('table')" id="btn-table" class="task-btn-primary"
                style="padding: 10px 16px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                <i class="fas fa-table" style="margin-right: 6px;"></i><span class="btn-text">Table</span>
            </button>
            <a href="{{ route('admin.tasks.create') }}" class="task-btn-primary"
                style="padding: 10px 16px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                <i class="fas fa-plus" style="margin-right: 6px;"></i><span class="btn-text">New Task</span>
            </a>
        </div>
    </div>

    <!-- Stats Dashboard Cards -->
    <div class="task-stats-grid" data-ajax-area style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; margin-bottom: 24px;">
        <a href="{{ route('admin.tasks') }}" class="task-stat-card" style="background: white; border: 1.5px solid var(--border); border-radius: 14px; padding: 18px 16px; text-decoration: none; transition: all 0.2s; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-layer-group" style="color: white; font-size: 15px;"></i>
                </div>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: var(--text); line-height: 1;">{{ number_format($taskStats['total']) }}</div>
            <div style="font-size: 12px; color: var(--text3); font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Total Tasks</div>
        </a>

        <a href="{{ route('admin.tasks', ['status' => 'todo']) }}" class="task-stat-card" style="background: white; border: 1.5px solid var(--border); border-radius: 14px; padding: 18px 16px; text-decoration: none; transition: all 0.2s; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #94a3b8, #64748b); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-clipboard-list" style="color: white; font-size: 15px;"></i>
                </div>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: var(--text); line-height: 1;">{{ $taskStats['todo'] }}</div>
            <div style="font-size: 12px; color: var(--text3); font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">To Do</div>
        </a>

        <a href="{{ route('admin.tasks', ['status' => 'inprogress']) }}" class="task-stat-card" style="background: white; border: 1.5px solid var(--border); border-radius: 14px; padding: 18px 16px; text-decoration: none; transition: all 0.2s; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #3B82F6, #2563eb); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-bolt" style="color: white; font-size: 15px;"></i>
                </div>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: var(--text); line-height: 1;">{{ $taskStats['inprogress'] }}</div>
            <div style="font-size: 12px; color: var(--text3); font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">In Progress</div>
        </a>

        <a href="{{ route('admin.tasks', ['status' => 'review']) }}" class="task-stat-card" style="background: white; border: 1.5px solid var(--border); border-radius: 14px; padding: 18px 16px; text-decoration: none; transition: all 0.2s; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #F59E0B, #d97706); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-eye" style="color: white; font-size: 15px;"></i>
                </div>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: var(--text); line-height: 1;">{{ $taskStats['review'] }}</div>
            <div style="font-size: 12px; color: var(--text3); font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">In Review</div>
        </a>

        <a href="{{ route('admin.tasks', ['status' => 'completed']) }}" class="task-stat-card" style="background: white; border: 1.5px solid var(--border); border-radius: 14px; padding: 18px 16px; text-decoration: none; transition: all 0.2s; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #10B981, #059669); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-check-circle" style="color: white; font-size: 15px;"></i>
                </div>
            </div>
            <div style="font-size: 26px; font-weight: 800; color: var(--text); line-height: 1;">{{ $taskStats['completed'] + $taskStats['published'] }}</div>
            <div style="font-size: 12px; color: var(--text3); font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Done</div>
        </a>

        <a href="{{ route('admin.tasks', ['overdue' => 'yes']) }}" class="task-stat-card {{ $taskStats['overdue'] > 0 ? 'stat-card-alert' : '' }}" style="background: {{ $taskStats['overdue'] > 0 ? '#fef2f2' : 'white' }}; border: 1.5px solid {{ $taskStats['overdue'] > 0 ? '#fca5a5' : 'var(--border)' }}; border-radius: 14px; padding: 18px 16px; text-decoration: none; transition: all 0.2s; position: relative; overflow: hidden;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #EF4444, #dc2626); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-exclamation-triangle" style="color: white; font-size: 15px;"></i>
                </div>
                @if($taskStats['overdue'] > 0)
                    <span style="font-size: 10px; font-weight: 700; color: #EF4444; background: #fee2e2; padding: 2px 8px; border-radius: 10px; animation: pulse-alert 2s infinite;">ALERT</span>
                @endif
            </div>
            <div style="font-size: 26px; font-weight: 800; color: {{ $taskStats['overdue'] > 0 ? '#EF4444' : 'var(--text)' }}; line-height: 1;">{{ $taskStats['overdue'] }}</div>
            <div style="font-size: 12px; color: {{ $taskStats['overdue'] > 0 ? '#EF4444' : 'var(--text3)' }}; font-weight: 600; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px;">Overdue</div>
        </a>
    </div>

    <!-- Recently Added & Recently Completed Side-by-Side Panels -->
    <div class="recent-panels-grid" data-ajax-area style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
        <!-- Recently Added Tasks -->
        <div style="background: white; border: 1.5px solid var(--border); border-radius: 14px; padding: 0; overflow: hidden;">
            <div style="padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #3B82F6; animation: pulse-dot 2s infinite;"></div>
                    <span style="font-weight: 700; font-size: 14px; color: var(--text);">Recently Added</span>
                </div>
                <a href="{{ route('admin.tasks', ['sort' => 'latest']) }}" style="font-size: 12px; color: var(--primary); text-decoration: none; font-weight: 600;">View All &rarr;</a>
            </div>
            <div style="padding: 6px 0;">
                @forelse($recentTasks as $rt)
                    @php
                        $rtTypeColors = ['reel'=>'#F97316','post'=>'#3B82F6','story'=>'#14B8A6','video'=>'#8B5CF6','carousel'=>'#EC4899'];
                    @endphp
                    <a href="{{ route('admin.tasks.show', $rt) }}" style="display: flex; align-items: center; gap: 12px; padding: 10px 18px; text-decoration: none; transition: background 0.15s; border-bottom: 1px solid var(--border)22;" onmouseover="this.style.background='var(--card2)'" onmouseout="this.style.background='transparent'">
                        <div style="width: 36px; height: 36px; border-radius: 9px; background: {{ $rtTypeColors[$rt->type] ?? '#4F6DF0' }}14; color: {{ $rtTypeColors[$rt->type] ?? '#4F6DF0' }}; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                            @if($rt->type === 'reel') <i class="fas fa-film"></i>
                            @elseif($rt->type === 'video') <i class="fas fa-video"></i>
                            @elseif($rt->type === 'story') <i class="fas fa-camera"></i>
                            @elseif($rt->type === 'carousel') <i class="fas fa-images"></i>
                            @else <i class="fas fa-file-alt"></i>
                            @endif
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $rt->title ?? 'Untitled' }}</div>
                            <div style="font-size: 11px; color: var(--text3);">{{ $rt->client?->name ?? '—' }} &middot; {{ $rt->created_at->diffForHumans() }}</div>
                        </div>
                        <span style="padding: 3px 8px; font-size: 10px; font-weight: 700; border-radius: 5px; background: #3B82F618; color: #3B82F6; white-space: nowrap;">NEW</span>
                    </a>
                @empty
                    <div style="padding: 24px; text-align: center; color: var(--text3); font-size: 13px;">No tasks yet</div>
                @endforelse
            </div>
        </div>

        <!-- Recently Completed Tasks -->
        <div style="background: white; border: 1.5px solid var(--border); border-radius: 14px; padding: 0; overflow: hidden;">
            <div style="padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></div>
                    <span style="font-weight: 700; font-size: 14px; color: var(--text);">Recently Completed</span>
                </div>
                <a href="{{ route('admin.tasks', ['status' => 'completed']) }}" style="font-size: 12px; color: #10B981; text-decoration: none; font-weight: 600;">View All &rarr;</a>
            </div>
            <div style="padding: 6px 0;">
                @forelse($recentlyCompleted as $rc)
                    <a href="{{ route('admin.tasks.show', $rc) }}" style="display: flex; align-items: center; gap: 12px; padding: 10px 18px; text-decoration: none; transition: background 0.15s; border-bottom: 1px solid var(--border)22;" onmouseover="this.style.background='var(--card2)'" onmouseout="this.style.background='transparent'">
                        <div style="width: 36px; height: 36px; border-radius: 9px; background: #10B98114; color: #10B981; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $rc->title ?? 'Untitled' }}</div>
                            <div style="font-size: 11px; color: var(--text3);">{{ $rc->client?->name ?? '—' }} &middot; {{ $rc->completed_at ? $rc->completed_at->diffForHumans() : $rc->updated_at->diffForHumans() }}</div>
                        </div>
                        @if($rc->assignee)
                            <div style="width: 26px; height: 26px; border-radius: 50%; background: {{ $rc->assignee->avatar_color ?? '#555' }}; color: white; font-size: 9px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0;" title="{{ $rc->assignee->name }}">{{ $rc->assignee->initial }}</div>
                        @endif
                    </a>
                @empty
                    <div style="padding: 24px; text-align: center; color: var(--text3); font-size: 13px;">No completed tasks yet</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- AJAX Content Area -->
    <div id="tasks-ajax-area" data-ajax-area>

    <!-- Search & Filter Bar -->
    <form method="GET" action="{{ route('admin.tasks') }}" id="filter-form" class="tasks-filter-form" style="margin-bottom: 20px;">
            @php
                $hasFilters = $search || $status || $client || $priority || $platform || $type || $assignedTo || $dateFrom || $dateTo;
                $dateFieldLabels = ['deadline' => 'Deadline', 'post_date' => 'Post Date', 'created_at' => 'Created At'];
                $activeFilterCount = ($search ? 1 : 0) + ($status ? 1 : 0) + ($client ? 1 : 0) + ($priority ? 1 : 0) + ($platform ? 1 : 0) + ($type ? 1 : 0) + ($assignedTo ? 1 : 0) + (($dateFrom || $dateTo) ? 1 : 0) + ($overdue === 'yes' ? 1 : 0);
            @endphp

            <!-- Filters Container -->
            <div class="filters-container" id="filtersContainer" style="background: white; border: 1.5px solid var(--border); border-radius: 16px; padding: 0; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.04);">

                <!-- Search Bar Row -->
                <div style="padding: 16px 20px 0 20px;">
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: {{ $search ? 'var(--primary)' : 'var(--text3)' }}; font-size: 14px; pointer-events: none;"></i>
                        <input type="text" id="tasksSearchInput" name="search" placeholder="Search tasks by title or caption..." value="{{ $search }}"
                            style="padding: 12px 14px 12px 40px; border: 2px solid {{ $search ? 'var(--primary)' : 'var(--border)' }}; border-radius: 12px; font-size: 14px; background: {{ $search ? 'var(--primary)05' : 'var(--card2)' }}; outline: none; width: 100%; box-sizing: border-box; font-weight: 500; transition: all 0.25s ease;">
                        @if($search)
                            <a href="{{ route('admin.tasks', array_filter(request()->except('search', 'page'))) }}" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--text3); font-size: 14px; text-decoration: none; padding: 4px;" title="Clear search">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Quick Filters Row -->
                <div style="padding: 14px 20px; display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                    <div class="filter-field" style="flex: 1; min-width: 140px;">
                        <label class="filter-label"><i class="fas fa-flag" style="margin-right: 4px; font-size: 10px;"></i> Status</label>
                        <select name="status" class="filter-select {{ $status ? 'filter-active' : '' }}">
                            <option value="">All Statuses</option>
                            <option value="todo"              {{ $status === 'todo'              ? 'selected' : '' }}>To Do</option>
                            <option value="inprogress"        {{ $status === 'inprogress'        ? 'selected' : '' }}>In Progress</option>
                            <option value="review"            {{ $status === 'review'            ? 'selected' : '' }}>Review</option>
                            <option value="pending_approval"  {{ $status === 'pending_approval'  ? 'selected' : '' }}>Pending</option>
                            <option value="completed"         {{ $status === 'completed'         ? 'selected' : '' }}>Completed</option>
                            <option value="published"          {{ $status === 'published'          ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>

                    <div class="filter-field" style="flex: 1; min-width: 140px;">
                        <label class="filter-label"><i class="fas fa-building" style="margin-right: 4px; font-size: 10px;"></i> Client</label>
                        <select name="client" class="filter-select {{ $client ? 'filter-active' : '' }}">
                            <option value="">All Clients</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ $client == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-field" style="flex: 1; min-width: 140px;">
                        <label class="filter-label"><i class="fas fa-exclamation-circle" style="margin-right: 4px; font-size: 10px;"></i> Priority</label>
                        <select name="priority" class="filter-select {{ $priority ? 'filter-active' : '' }}">
                            <option value="">All Priorities</option>
                            <option value="normal"  {{ $priority === 'normal'  ? 'selected' : '' }}>Normal</option>
                            <option value="high"    {{ $priority === 'high'    ? 'selected' : '' }}>High</option>
                            <option value="urgent"  {{ $priority === 'urgent'  ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>

                    <div class="filter-field" style="flex: 1; min-width: 140px;">
                        <label class="filter-label"><i class="fas fa-sort" style="margin-right: 4px; font-size: 10px;"></i> Sort By</label>
                        <select name="sort" class="filter-select">
                            <option value="latest"        {{ $sort === 'latest'        ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest"        {{ $sort === 'oldest'        ? 'selected' : '' }}>Oldest First</option>
                            <option value="deadline_asc"  {{ $sort === 'deadline_asc'  ? 'selected' : '' }}>Deadline ↑</option>
                            <option value="deadline_desc" {{ $sort === 'deadline_desc' ? 'selected' : '' }}>Deadline ↓</option>
                            <option value="post_date_asc"  {{ $sort === 'post_date_asc'  ? 'selected' : '' }}>Post Date ↑</option>
                            <option value="post_date_desc" {{ $sort === 'post_date_desc' ? 'selected' : '' }}>Post Date ↓</option>
                        </select>
                    </div>
                </div>

                <!-- Expandable Advanced Filters -->
                <div style="border-top: 1px solid var(--border);">
                    <button type="button" id="advancedToggleBtn" style="width: 100%; padding: 11px 20px; background: transparent; border: none; cursor: pointer; font-weight: 600; font-size: 12.5px; color: var(--text3); display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; letter-spacing: 0.3px; text-transform: uppercase;">
                        <i class="fas fa-sliders-h" style="font-size: 11px;"></i>
                        <span>More Filters</span>
                        @if($platform || $type || $assignedTo || $dateFrom || $dateTo || $overdue === 'yes')
                            <span style="background: var(--primary); color: white; font-size: 10px; font-weight: 700; padding: 1px 7px; border-radius: 10px; line-height: 1.5;">{{ ($platform ? 1 : 0) + ($type ? 1 : 0) + ($assignedTo ? 1 : 0) + (($dateFrom || $dateTo) ? 1 : 0) + ($overdue === 'yes' ? 1 : 0) }}</span>
                        @endif
                        <i class="fas fa-chevron-down" style="transition: transform 0.25s; font-size: 10px; margin-left: 2px;"></i>
                    </button>

                    <!-- Advanced Filters Panel -->
                    <div id="advancedFiltersPanel" style="display: none;">
                        <div style="padding: 0 20px 16px 20px;">
                            <!-- Platform + Type + Designer Row -->
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px;">
                                <div class="filter-field" style="flex: 1; min-width: 140px;">
                                    <label class="filter-label"><i class="fas fa-hashtag" style="margin-right: 4px; font-size: 10px;"></i> Platform</label>
                                    <select name="platform" class="filter-select {{ $platform ? 'filter-active' : '' }}">
                                        <option value="">All Platforms</option>
                                        <option value="instagram"  {{ $platform === 'instagram'  ? 'selected' : '' }}>📷 Instagram</option>
                                        <option value="facebook"   {{ $platform === 'facebook'   ? 'selected' : '' }}>👍 Facebook</option>
                                        <option value="linkedin"   {{ $platform === 'linkedin'   ? 'selected' : '' }}>💼 LinkedIn</option>
                                        <option value="twitter"    {{ $platform === 'twitter'    ? 'selected' : '' }}>𝕏 Twitter</option>
                                    </select>
                                </div>

                                <div class="filter-field" style="flex: 1; min-width: 140px;">
                                    <label class="filter-label"><i class="fas fa-photo-video" style="margin-right: 4px; font-size: 10px;"></i> Content Type</label>
                                    <select name="type" class="filter-select {{ $type ? 'filter-active' : '' }}">
                                        <option value="">All Types</option>
                                        <option value="reel"     {{ $type === 'reel'     ? 'selected' : '' }}>🎬 Reel</option>
                                        <option value="post"     {{ $type === 'post'     ? 'selected' : '' }}>📝 Post</option>
                                        <option value="story"    {{ $type === 'story'    ? 'selected' : '' }}>📸 Story</option>
                                        <option value="video"    {{ $type === 'video'    ? 'selected' : '' }}>🎥 Video</option>
                                        <option value="carousel" {{ $type === 'carousel' ? 'selected' : '' }}>🎞️ Carousel</option>
                                        <option value="brochure" {{ $type === 'brochure' ? 'selected' : '' }}>📄 Brochure</option>
                                        <option value="banner"   {{ $type === 'banner'   ? 'selected' : '' }}>🧾 Banner</option>
                                        <option value="flyer"    {{ $type === 'flyer'    ? 'selected' : '' }}>📢 Flyer</option>
                                        <option value="website"  {{ $type === 'website'  ? 'selected' : '' }}>🌐 Website</option>
                                        <option value="software" {{ $type === 'software' ? 'selected' : '' }}>💻 Software</option>
                                        <option value="others"   {{ $type === 'others'   ? 'selected' : '' }}>📎 Others</option>
                                    </select>
                                </div>

                                <div class="filter-field" style="flex: 1; min-width: 140px;">
                                    <label class="filter-label"><i class="fas fa-user" style="margin-right: 4px; font-size: 10px;"></i> Assigned To</label>
                                    <select name="assigned_to" class="filter-select {{ $assignedTo ? 'filter-active' : '' }}">
                                        <option value="">All Assignees</option>
                                        @foreach($designers as $d)
                                            <option value="{{ $d->id }}" {{ $assignedTo == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Date Range + Overdue Row -->
                            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                                <div class="filter-field" style="min-width: 120px;">
                                    <label class="filter-label"><i class="fas fa-calendar" style="margin-right: 4px; font-size: 10px;"></i> Date Field</label>
                                    <select name="date_field" class="filter-select" style="font-size: 12px;">
                                        <option value="deadline"   {{ $dateField === 'deadline'   ? 'selected' : '' }}>Deadline</option>
                                        <option value="post_date"  {{ $dateField === 'post_date'  ? 'selected' : '' }}>Post Date</option>
                                        <option value="created_at" {{ $dateField === 'created_at' ? 'selected' : '' }}>Created At</option>
                                    </select>
                                </div>
                                <div class="filter-field" style="min-width: 130px;">
                                    <label class="filter-label">From</label>
                                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="filter-select {{ $dateFrom ? 'filter-active' : '' }}" style="font-size: 12px;">
                                </div>
                                <div class="filter-field" style="min-width: 130px;">
                                    <label class="filter-label">To</label>
                                    <input type="date" name="date_to" value="{{ $dateTo }}" class="filter-select {{ $dateTo ? 'filter-active' : '' }}" style="font-size: 12px;">
                                </div>
                                <label class="overdue-toggle {{ $overdue === 'yes' ? 'overdue-active' : '' }}" style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 9px 14px; border: 1.5px solid {{ $overdue === 'yes' ? '#EF4444' : 'var(--border)' }}; border-radius: 10px; background: {{ $overdue === 'yes' ? '#fef2f2' : 'white' }}; transition: all 0.2s; white-space: nowrap; height: fit-content;">
                                    <input type="checkbox" name="overdue" value="yes" {{ $overdue === 'yes' ? 'checked' : '' }}
                                        style="width: 16px; height: 16px; cursor: pointer; accent-color: #EF4444;">
                                    <span style="font-weight: 600; font-size: 12px; color: {{ $overdue === 'yes' ? '#EF4444' : 'var(--text2)' }};">⏰ Overdue Only</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Bar -->
                <div style="padding: 12px 20px; background: var(--card2); border-top: 1px solid var(--border); display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <button type="submit" class="filter-submit-btn" style="padding: 9px 20px; background: var(--primary); color: white; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; box-shadow: 0 2px 8px rgba(79, 109, 240, 0.25);">
                        <i class="fas fa-filter" style="font-size: 11px;"></i> Apply
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('admin.tasks') }}" class="filter-reset-btn" style="padding: 9px 16px; background: white; border: 1.5px solid var(--border); border-radius: 10px; text-decoration: none; color: var(--text2); font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;">
                            <i class="fas fa-redo" style="font-size: 10px;"></i> Reset
                        </a>
                    @endif

                    @if($hasFilters)
                        <div style="height: 24px; width: 1px; background: var(--border); margin: 0 4px;"></div>
                        <span style="font-size: 12px; color: var(--text3); font-weight: 600;">{{ $activeFilterCount }} filter{{ $activeFilterCount > 1 ? 's' : '' }} active</span>
                    @endif

                    <div style="margin-left: auto; display: flex; align-items: center; gap: 10px;">
                        <a href="{{ route('admin.tasks.export-csv', request()->query()) }}"
                            data-no-ajax
                            style="padding: 8px 14px; background: white; border: 1.5px solid var(--border); border-radius: 10px; text-decoration: none; color: var(--text2); font-size: 12px; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; transition: all 0.2s;">
                            <i class="fas fa-download" style="font-size: 11px;"></i> Export
                        </a>
                    </div>
                </div>
            </div>

            @if($hasFilters)
            <!-- Active Filter Pills -->
            <div class="active-filter-pills" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 12px; margin-bottom: 6px; align-items: center;">
                <span style="font-size: 11px; color: var(--text3); font-weight: 600; margin-right: 2px; text-transform: uppercase; letter-spacing: 0.5px;">Filtering by:</span>
                @if($search)
                    <a href="{{ route('admin.tasks', array_filter(request()->except('search', 'page'))) }}" class="filter-pill" title="Remove">
                        <i class="fas fa-search" style="font-size: 9px;"></i> "{{ Str::limit($search, 20) }}" <i class="fas fa-times pill-x"></i>
                    </a>
                @endif
                @if($status)
                    <a href="{{ route('admin.tasks', array_filter(request()->except('status', 'page'))) }}" class="filter-pill" title="Remove">
                        <i class="fas fa-flag" style="font-size: 9px;"></i> {{ $status === 'inprogress' ? 'In Progress' : ucfirst($status) }} <i class="fas fa-times pill-x"></i>
                    </a>
                @endif
                @if($client)
                    <a href="{{ route('admin.tasks', array_filter(request()->except('client', 'page'))) }}" class="filter-pill" title="Remove">
                        <i class="fas fa-building" style="font-size: 9px;"></i> {{ $clients->firstWhere('id', $client)?->name }} <i class="fas fa-times pill-x"></i>
                    </a>
                @endif
                @if($priority)
                    <a href="{{ route('admin.tasks', array_filter(request()->except('priority', 'page'))) }}" class="filter-pill" title="Remove">
                        <i class="fas fa-exclamation-circle" style="font-size: 9px;"></i> {{ ucfirst($priority) }} <i class="fas fa-times pill-x"></i>
                    </a>
                @endif
                @if($platform)
                    <a href="{{ route('admin.tasks', array_filter(request()->except('platform', 'page'))) }}" class="filter-pill" title="Remove">
                        <i class="fas fa-hashtag" style="font-size: 9px;"></i> {{ ucfirst($platform) }} <i class="fas fa-times pill-x"></i>
                    </a>
                @endif
                @if($type)
                    <a href="{{ route('admin.tasks', array_filter(request()->except('type', 'page'))) }}" class="filter-pill" title="Remove">
                        <i class="fas fa-photo-video" style="font-size: 9px;"></i> {{ ucfirst($type) }} <i class="fas fa-times pill-x"></i>
                    </a>
                @endif
                @if($assignedTo)
                    <a href="{{ route('admin.tasks', array_filter(request()->except('assigned_to', 'page'))) }}" class="filter-pill" title="Remove">
                        <i class="fas fa-user" style="font-size: 9px;"></i> {{ $designers->firstWhere('id', $assignedTo)?->name }} <i class="fas fa-times pill-x"></i>
                    </a>
                @endif
                @if($dateFrom || $dateTo)
                    <a href="{{ route('admin.tasks', array_filter(request()->except('date_from', 'date_to', 'page'))) }}" class="filter-pill" title="Remove">
                        <i class="fas fa-calendar" style="font-size: 9px;"></i> {{ $dateFieldLabels[$dateField] }}: {{ $dateFrom ?: '...' }} → {{ $dateTo ?: '...' }} <i class="fas fa-times pill-x"></i>
                    </a>
                @endif
                @if($overdue === 'yes')
                    <a href="{{ route('admin.tasks', array_filter(request()->except('overdue', 'page'))) }}" class="filter-pill filter-pill-danger" title="Remove">
                        ⏰ Overdue <i class="fas fa-times pill-x"></i>
                    </a>
                @endif
                <a href="{{ route('admin.tasks') }}" style="margin-left: 6px; padding: 4px 12px; background: #fee2e2; color: #dc2626; border-radius: 20px; font-size: 11px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                    <i class="fas fa-times" style="font-size: 9px;"></i> Clear All
                </a>
            </div>
            @endif

            <!-- Per Page + Count + Page Jump (table view only) -->
            <div id="table-only-controls" style="display: flex; align-items: center; gap: 10px; margin-top: 14px; margin-bottom: 10px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; background: white; border: 1px solid var(--border); border-radius: 8px;">
                    <span style="font-size: 12px; color: var(--text3);">Show</span>
                    @php $standardOptions = [10, 25, 50, 100, 200]; @endphp
                    <select name="per_page" id="per_page_select" onchange="if(this.value==='custom'){ document.getElementById('custom_per_page_wrap').style.display='flex'; } else { document.getElementById('custom_per_page_wrap').style.display='none'; applyFilters(); }"
                        style="padding: 4px 6px; border: 1px solid var(--border); border-radius: 5px; font-size: 12px; font-weight: 600; cursor: pointer; background: var(--card2);">
                        @foreach($standardOptions as $opt)
                            <option value="{{ $opt }}" {{ $perPageRaw == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                        <option value="custom" {{ $perPageRaw === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>

                    <div id="custom_per_page_wrap" style="display: {{ $perPageRaw === 'custom' ? 'flex' : 'none' }}; align-items: center; gap: 5px; margin-left: 5px;">
                        <input type="number" id="per_page_custom_input" value="{{ $perPage }}" 
                            style="width: 55px; padding: 3px 6px; border: 1px solid var(--border); border-radius: 5px; font-size: 12px; font-weight: 700; text-align: center;"
                            onkeydown="if(event.key==='Enter'){ event.preventDefault(); applyCustomPerPage(); }">
                        <button type="button" onclick="applyCustomPerPage()" 
                            style="padding: 3px 8px; background: var(--primary); color: white; border: none; border-radius: 5px; font-size: 11px; cursor: pointer; font-weight: 700;">Apply</button>
                    </div>
                    <span style="font-size: 12px; color: var(--text3);">per page</span>
                </div>

                <div style="font-size: 12.5px; color: var(--text2);">
                    <strong style="color: var(--text);">{{ number_format($tasks->total()) }}</strong> task{{ $tasks->total() !== 1 ? 's' : '' }}
                    @if($hasFilters)<span style="color: var(--primary); font-weight: 600;"> (filtered)</span>@endif
                </div>

                @if($tasks->lastPage() > 1)
                    <div style="display: flex; align-items: center; gap: 6px; padding: 5px 10px; background: white; border: 1px solid var(--border); border-radius: 8px; margin-left: auto;">
                        <span style="font-size: 11px; color: var(--text3); white-space: nowrap;">Page</span>
                        <input type="number" id="page-jump-input" min="1" max="{{ $tasks->lastPage() }}" value="{{ $tasks->currentPage() }}"
                            style="width: 48px; padding: 3px 4px; border: 1px solid var(--border); border-radius: 5px; font-size: 12px; text-align: center; font-weight: 600;"
                            onkeydown="if(event.key==='Enter'){event.preventDefault();jumpToPage(this.value)}">
                        <span style="font-size: 11px; color: var(--text3);">of {{ number_format($tasks->lastPage()) }}</span>
                        <button onclick="jumpToPage(document.getElementById('page-jump-input').value)" style="padding: 3px 10px; background: var(--primary); color: white; border: none; border-radius: 5px; font-size: 11px; cursor: pointer; font-weight: 700;">Go</button>
                    </div>
                @endif
            </div>
    </form>

    <div id="table-view" style="display: block;">

        @if($tasks->hasPages())
        <!-- Top Pagination -->
        <div style="margin-bottom: 16px;">
            {{ $tasks->links('vendor.pagination.custom') }}
        </div>
        @endif

        <!-- Mobile Task Cards (visible only on mobile) -->
        <div class="mobile-task-cards" style="display: none;">
            @forelse($tasks as $task)
                @php
                    $statusColors = ['todo'=>'#6b7280','inprogress'=>'#3B82F6','review'=>'#F59E0B','completed'=>'#10B981','pending_approval'=>'#8B5CF6','published'=>'#22c55e'];
                    $statusLabels = ['todo'=>'To Do','inprogress'=>'In Progress','review'=>'Review','completed'=>'Completed','pending_approval'=>'Pending Approval','published'=>'Published'];
                    $priorityColors = ['normal'=>'#6b7280','high'=>'#F59E0B','urgent'=>'#EF4444'];
                    $typeColors = ['reel'=>'#F97316','post'=>'#3B82F6','story'=>'#14B8A6','video'=>'#8B5CF6','carousel'=>'#EC4899'];
                    $isOverdue = $task->deadline && $task->deadline->isPast() && !in_array($task->status, ['completed', 'published']);
                    $isNew = $task->created_at && $task->created_at->gt(now()->subHours(24));
                    $isCompleted = in_array($task->status, ['completed', 'published']);
                @endphp
                <div class="mobile-task-card" onclick="window.location='{{ route('admin.tasks.show', $task) }}'" style="background: {{ $isCompleted ? '#f0fdf4' : ($isOverdue ? '#fef2f2' : 'white') }}; border: 1px solid {{ $isCompleted ? '#bbf7d0' : ($isOverdue ? '#fca5a5' : 'var(--border)') }}; border-radius: 12px; padding: 16px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.04); {{ $isCompleted ? 'opacity: 0.85;' : '' }}"
                    {{ $isNew ? 'data-new=true' : '' }}>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                                <h3 style="margin: 0; font-size: 15px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; {{ $isCompleted ? 'text-decoration: line-through; color: var(--text3);' : '' }}">
                                    <span style="color: var(--primary); font-size: 11px; margin-right: 2px;">#{{ $task->id }}</span>
                                    {{ $task->title ?? 'Untitled' }}
                                </h3>
                                @if($isNew)
                                    <span style="padding: 1px 6px; font-size: 9px; font-weight: 800; background: #3B82F6; color: white; border-radius: 4px; white-space: nowrap; flex-shrink: 0;">NEW</span>
                                @endif
                                @if($isOverdue)
                                    <span style="padding: 1px 6px; font-size: 9px; font-weight: 800; background: #EF4444; color: white; border-radius: 4px; white-space: nowrap; flex-shrink: 0;">LATE</span>
                                @endif
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: {{ $typeColors[$task->type] ?? '#4F6DF0' }}18; color: {{ $typeColors[$task->type] ?? '#4F6DF0' }}; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                    {{ ucfirst($task->type) }}
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: {{ $statusColors[$task->status] ?? '#6b7280' }}18; color: {{ $statusColors[$task->status] ?? '#6b7280' }}; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                    {{ $statusLabels[$task->status] ?? ucfirst($task->status) }}
                                </span>
                                @if($task->priority === 'urgent')
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: #EF444418; color: #EF4444; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-exclamation"></i> Urgent
                                    </span>
                                @elseif($task->priority === 'high')
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: #F59E0B18; color: #F59E0B; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                        <i class="fas fa-arrow-up"></i> High
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text2);">
                            <i class="fas fa-building" style="color: var(--text3);"></i>
                            <span>{{ $task->client?->name ?? '—' }}</span>
                        </div>
                        @if($task->assignee)
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div style="width: 24px; height: 24px; border-radius: 50%; background: {{ $task->assignee->avatar_color ?? '#555' }}; color: white; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center;">{{ $task->assignee->initial }}</div>
                                <span style="font-size: 13px; color: var(--text2);">{{ $task->assignee->name }}</span>
                            </div>
                        @endif
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; gap: 16px; font-size: 12px;">
                            @if($task->deadline)
                                <div style="display: flex; align-items: center; gap: 4px; color: {{ $isOverdue ? '#EF4444' : 'var(--text2)' }}; font-weight: {{ $isOverdue ? '600' : '400' }};">
                                    <i class="fas {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-calendar-alt' }}"></i>
                                    <span>{{ $task->deadline->format('d M Y') }}</span>
                                </div>
                            @endif
                            @if($task->post_date)
                                <div style="display: flex; align-items: center; gap: 4px; color: var(--text3);">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>{{ $task->post_date->format('d M Y') }}</span>
                                </div>
                            @endif
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('admin.tasks.show', $task) }}" style="padding: 8px 12px; background: var(--blue); color: white; border-radius: 6px; text-decoration: none; font-size: 12px; display: flex; align-items: center; gap: 4px;" onclick="event.stopPropagation()">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.tasks.edit', $task) }}" style="padding: 8px 12px; background: var(--primary); color: white; border-radius: 6px; text-decoration: none; font-size: 12px; display: flex; align-items: center; gap: 4px;" onclick="event.stopPropagation()">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding: 40px 20px; text-align: center; color: var(--text3); background: white; border: 1px solid var(--border); border-radius: 12px;">
                    <i class="fas fa-search" style="font-size: 36px; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                    <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px; color: var(--text2);">No tasks found</div>
                    <div style="font-size: 13px;">Try adjusting your filters</div>
                    @if($hasFilters)
                        <a href="{{ route('admin.tasks') }}" style="display: inline-block; margin-top: 12px; padding: 10px 20px; background: var(--primary); color: white; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500;">Clear Filters</a>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Tasks Table (desktop only) -->
        <div class="tasks-table-container" style="background: white; border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--card2); border-bottom: 2px solid var(--border);">
                        <th style="padding: 11px 14px; text-align: center; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px; width: 50px;">ID</th>
                        <th style="padding: 11px 14px; text-align: left; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;">Task</th>
                        <th style="padding: 11px 14px; text-align: left; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;">Client</th>
                        <th style="padding: 11px 14px; text-align: left; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;">Type</th>
                        <th style="padding: 11px 14px; text-align: left; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;">Platform</th>
                        <th style="padding: 11px 14px; text-align: left; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;">Status</th>
                        <th style="padding: 11px 14px; text-align: left; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;">Priority</th>
                        <th style="padding: 11px 14px; text-align: left; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;">Assigned To</th>
                        <th style="padding: 11px 14px; text-align: left; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;">Deadline</th>
                        <th style="padding: 11px 14px; text-align: left; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;">Post Date</th>
                        <th style="padding: 11px 14px; text-align: center; font-weight: 700; font-size: 11px; color: var(--text2); text-transform: uppercase; letter-spacing: 0.6px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $index => $task)
                        @php
                            $statusColors = ['todo'=>'#6b7280','inprogress'=>'#3B82F6','review'=>'#F59E0B','completed'=>'#10B981','pending_approval'=>'#8B5CF6','published'=>'#22c55e'];
                            $statusLabels = ['todo'=>'To Do','inprogress'=>'In Progress','review'=>'Review','completed'=>'Completed','pending_approval'=>'Pending Approval','published'=>'Published'];
                            $priorityColors = ['normal'=>'#6b7280','high'=>'#F59E0B','urgent'=>'#EF4444'];
                            $priorityLabels = ['normal'=>'Normal','high'=>'High','urgent'=>'Urgent'];
                            $platformIcons = ['instagram'=>'<i class="fab fa-instagram"></i>','facebook'=>'<i class="fab fa-facebook"></i>','linkedin'=>'<i class="fab fa-linkedin"></i>','twitter'=>'<i class="fab fa-twitter"></i>'];
                            $typeColors = ['reel'=>'#F97316','post'=>'#3B82F6','story'=>'#14B8A6','video'=>'#8B5CF6','carousel'=>'#EC4899'];
                            $isOverdue = $task->deadline && $task->deadline->isPast() && !in_array($task->status, ['completed', 'published']);
                            $isNew = $task->created_at && $task->created_at->gt(now()->subHours(24));
                            $isCompleted = in_array($task->status, ['completed', 'published']);
                            $rowBg = $isCompleted ? '#f0fdf4' : ($isOverdue ? '#fef2f2' : ($index % 2 === 0 ? 'white' : 'var(--card2)'));
                        @endphp
                        <tr onclick="window.location='{{ route('admin.tasks.show', $task) }}'"
                            style="border-bottom: 1px solid var(--border); background: {{ $rowBg }}; cursor: pointer; transition: background 0.15s; {{ $isCompleted ? 'opacity: 0.85;' : '' }}"
                            onmouseover="this.style.background='var(--primary)08'"
                            onmouseout="this.style.background='{{ $rowBg }}'">

                            <td style="padding: 11px 14px; text-align: center; font-weight: 700; color: var(--text3); font-size: 12px;">
                                #{{ $task->id }}
                            </td>
                            <td style="padding: 11px 14px; font-weight: 600; color: var(--text); font-size: 13.5px; max-width: 200px;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; {{ $isCompleted ? 'text-decoration: line-through; color: var(--text3);' : '' }}" title="{{ $task->title ?? 'Untitled' }}">
                                        {{ $task->title ?? 'Untitled' }}
                                    </div>
                                    @if($isNew)
                                        <span style="padding: 1px 6px; font-size: 9px; font-weight: 800; background: #3B82F6; color: white; border-radius: 4px; white-space: nowrap; flex-shrink: 0; letter-spacing: 0.5px;">NEW</span>
                                    @endif
                                    @if($isOverdue)
                                        <span style="padding: 1px 6px; font-size: 9px; font-weight: 800; background: #EF4444; color: white; border-radius: 4px; white-space: nowrap; flex-shrink: 0; letter-spacing: 0.5px;">LATE</span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 11px 14px; font-size: 13px; color: var(--text2);">
                                <div style="display:flex;align-items:center;gap:7px;">
                                    <x-client-branding :client="$task->client" size="20px" width="46px" radius="4px" />
                                    <span>{{ $task->client?->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td style="padding: 11px 14px;">
                                <span style="display: inline-block; padding: 3px 9px; background: {{ $typeColors[$task->type] ?? '#4F6DF0' }}18; color: {{ $typeColors[$task->type] ?? '#4F6DF0' }}; border-radius: 5px; font-size: 11.5px; font-weight: 600;">
                                    {{ ucfirst($task->type) }}
                                </span>
                            </td>
                            <td style="padding: 11px 14px; font-size: 13px; color: var(--text2);">
                                @if(is_array($task->platform))
                                    @foreach($task->platform as $platform)
                                        {!! $platformIcons[$platform] ?? '' !!}
                                    @endforeach
                                    {{ implode(', ', array_map('ucfirst', $task->platform)) }}
                                @else
                                {!! $platformIcons[$task->platform] ?? '' !!}
                                @endif
                            </td>
                            <td style="padding: 11px 14px;">
                                <span style="display: inline-block; padding: 3px 9px; background: {{ $statusColors[$task->status] ?? '#6b7280' }}18; color: {{ $statusColors[$task->status] ?? '#6b7280' }}; border-radius: 5px; font-size: 11.5px; font-weight: 600;">
                                    {{ $statusLabels[$task->status] ?? ucfirst($task->status) }}
                                </span>
                            </td>
                            <td style="padding: 11px 14px; font-size: 13px; color: {{ $priorityColors[$task->priority] ?? 'var(--text2)' }}; font-weight: 600;">
                                {{ $priorityLabels[$task->priority] ?? ucfirst($task->priority) }}
                            </td>
                            <td style="padding: 11px 14px; font-size: 13px; color: var(--text2);">
                                @if($task->assignee)
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <div style="width: 22px; height: 22px; border-radius: 50%; background: {{ $task->assignee->avatar_color ?? '#555' }}; color: white; font-size: 9px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">{{ $task->assignee->initial }}</div>
                                        <span>{{ $task->assignee->name }}</span>
                                    </div>
                                @else
                                    <span style="color: var(--text3); font-style: italic;">Unassigned</span>
                                @endif
                            </td>
                            <td style="padding: 11px 14px; font-size: 13px;">
                                @if($task->deadline)
                                    <span style="color: {{ $isOverdue ? '#EF4444' : 'var(--text2)' }}; font-weight: {{ $isOverdue ? '600' : '400' }};">
                                        {{ $isOverdue ? '' : '' }}<i class="fas {{ $isOverdue ? 'fa-exclamation-triangle' : 'fa-calendar-alt' }}" style="margin-right:3px"></i> {{ $task->deadline->format('d M Y') }}
                                    </span>
                                @else
                                    <span style="color: var(--text3);">—</span>
                                @endif
                            </td>
                            <td style="padding: 11px 14px; font-size: 13px; color: var(--text2);">
                                @if($task->post_date)
                                    <i class="fas fa-paper-plane" style="margin-right:3px"></i> {{ $task->post_date->format('d M Y') }}
                                @else
                                    <span style="color: var(--text3);">—</span>
                                @endif
                            </td>
                            <td style="padding: 11px 14px; text-align: center;" onclick="event.stopPropagation()">
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    @if($task->status === 'review' && $task->admin_approval_status !== 'approved')
                                        <form method="POST" action="{{ route('admin.tasks.approve-task', $task) }}" style="margin:0">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="Approve Task" onclick="return confirm('Approve this task?')"
                                                style="padding: 5px 10px; background: #10B981; color: white; border-radius: 5px; border: none; font-size: 12px; cursor: pointer;">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.tasks.show', $task) }}"
                                        title="View Details"
                                        style="padding: 5px 10px; background: var(--blue); color: white; border-radius: 5px; text-decoration: none; font-size: 12px;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.tasks.edit', $task) }}"
                                        title="Edit Task"
                                        style="padding: 5px 10px; background: var(--primary); color: white; border-radius: 5px; text-decoration: none; font-size: 12px;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="padding: 50px; text-align: center; color: var(--text3);">
                                <i class="fas fa-search" style="font-size: 36px; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                                <div style="font-size: 15px; font-weight: 600; margin-bottom: 6px; color: var(--text2);">No tasks found</div>
                                <div style="font-size: 13px;">Try adjusting your filters</div>
                                @if($hasFilters)
                                    <a href="{{ route('admin.tasks') }}" style="display: inline-block; margin-top: 12px; padding: 8px 18px; background: var(--primary); color: white; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 500;">Clear Filters</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Bulk Actions -->
        <div id="bulk-bar" style="display:none; margin-top: 10px; padding: 10px 16px; background: var(--primary)10; border: 1px solid var(--primary)30; border-radius: 8px; align-items: center; gap: 12px;">
            <span id="bulk-count" style="font-size: 13px; font-weight: 600; color: var(--primary);"></span>
            <select id="bulk-status" style="padding: 7px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px;">
                <option value="">Set Status...</option>
                <option value="todo">To Do</option>
                <option value="inprogress">In Progress</option>
                <option value="review">Review</option>
                <option value="completed">Completed</option>
                <option value="published">Published</option>
            </select>
            <button onclick="bulkUpdate()" style="padding: 7px 16px; background: var(--primary); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600;">Apply</button>
            <button onclick="clearSelection()" style="padding: 7px 14px; background: var(--card2); border: 1px solid var(--border); border-radius: 6px; cursor: pointer; font-size: 13px;">Cancel</button>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 16px;">
            {{ $tasks->links('vendor.pagination.custom') }}
        </div>
    </div>

    <!-- Kanban View -->
    <div id="kanban-view" style="display: none;">
        @php
            $kanbanTotal = $kanbanTasks->flatten()->count();
            $colLabels = ['todo' => 'To Do', 'inprogress' => 'In Progress', 'review' => 'Review', 'pending_approval' => 'Pending Approval', 'completed' => 'Completed', 'published' => 'Published'];
            $colColors = ['todo' => 'var(--text3)', 'inprogress' => 'var(--blue)', 'review' => 'var(--yellow)', 'pending_approval' => 'var(--purple)', 'completed' => 'var(--teal)', 'published' => '#22c55e'];
            $colIcons  = ['todo' => '<i class="fas fa-clipboard-list"></i>', 'inprogress' => '<i class="fas fa-bolt"></i>', 'review' => '<i class="fas fa-eye"></i>', 'pending_approval' => '<i class="fas fa-hourglass-half"></i>', 'completed' => '<i class="fas fa-check-circle"></i>', 'published' => '<i class="fas fa-rocket"></i>'];
        @endphp

        <!-- Kanban info bar -->
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px; padding: 10px 14px; background: var(--card2); border: 1px solid var(--border); border-radius: 8px; font-size: 13px; color: var(--text2);">
            <i class="fas fa-th-large" style="color: var(--primary);"></i>
            <strong style="color: var(--text);">{{ $kanbanTotal }}</strong>&nbsp;task{{ $kanbanTotal !== 1 ? 's' : '' }} across columns
            @if($hasFilters)
                <span style="color: var(--primary); font-weight: 600;">&bull; filtered</span>
                <a href="{{ route('admin.tasks') }}" style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 12px; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 20px; font-size: 11.5px; font-weight: 600; text-decoration: none;">
                    <i class="fas fa-times"></i>Clear Filters
                </a>
            @endif
            <span style="margin-left: auto; font-size: 12px; color: var(--text3);">&#x1F4C5; Sorted by earliest deadline</span>
        </div>

        <div class="kanban">
            @foreach($columns as $col)
                @php $colTasks = $kanbanTasks[$col] ?? collect(); @endphp
                <div class="kanban-col" id="kanban-col-{{ $col }}" data-status="{{ $col }}" ondragover="dragOver(event)" ondrop="drop(event, 'kanban-col-{{ $col }}')" ondragleave="dragLeave(event)">
                    <div class="kanban-col-head">
                        <div class="col-label" style="color:{{ $colColors[$col] }}">{!! $colIcons[$col] !!} {{ $colLabels[$col] }}</div>
                        <div class="col-count">{{ $colTasks->count() }}</div>
                    </div>
                    @forelse($colTasks as $task)
                        <a href="{{ route('admin.tasks.show', $task) }}" style="display: block; text-decoration: none; color: inherit;">
                            <div class="task-card" draggable="true" ondragstart="dragStart(event, {{ $task->id }})" ondragend="dragEnd(event)" data-task-id="{{ $task->id }}" style="cursor: grab; user-select: none;">
                                <div style="display:flex;align-items:start;justify-content:space-between;gap:8px;margin-bottom:6px">
                                    <div class="task-title" style="margin-bottom:0">
                                        {{ $task->title ?? 'Untitled' }}
                                        @if($task->revision_count > 0)
                                            <span class="wf-rev-badge" style="animation:none;font-size:9px"><i class="fa-solid fa-rotate-left"></i> {{ $task->revision_count }}</span>
                                        @endif
                                    </div>
                                    {!! $task->priority_tag !!}
                                </div>
                                <div style="font-size:11.5px;color:black;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                                    <x-client-branding :client="$task->client" size="18px" width="42px" radius="4px" />
                                    {{ $task->client->name }} · @if(is_array($task->platform)){{ implode(', ', array_map('ucfirst', $task->platform)) }}@else{{ ucfirst($task->platform) }}@endif
                                </div>
                                <div class="task-meta">
                                    <span class="tag" style="background: var(--blue)15; color: var(--blue);">{{ ucfirst($task->type) }}</span>
                                    @if($task->deadline)
                                        <span class="task-deadline" style="margin-left:auto; {{ $task->deadline->isPast() ? 'color: var(--red)' : '' }}"><i class="fas fa-calendar-alt" style="margin-right:2px"></i> {{ $task->deadline->format('d M') }}</span>
                                    @endif
                                </div>
                                @if($task->assignee)
                                    <div style="display:flex;align-items:center;gap:7px;margin-top:10px;padding-top:8px;border-top:1px solid var(--border)">
                                        <div class="av-sm" style="background:{{ $task->assignee->avatar_color ?? '#555' }}">{{ $task->assignee->initial }}</div>
                                        <span style="font-size:11.5px;color:var(--text3)">{{ $task->assignee->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div style="padding:24px;text-align:center;color:var(--text3);font-size:12.5px;border:2px dashed var(--border);border-radius:var(--radius-sm)">
                            No tasks here
                        </div>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>

    </div><!-- /tasks-ajax-area -->
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/admin-tasks.css') }}">
@endpush

@push('scripts')
<script>
window.AdminTasksData = {
    hasAdvancedFilters: @json($platform || $type || $assignedTo || $dateFrom || $dateTo || $overdue === 'yes'),
    tasksBaseUrl: '{{ route("admin.tasks") }}'
};
</script>
<script src="{{ asset('js/pages/admin-tasks.js') }}"></script>
@endpush

@endsection
