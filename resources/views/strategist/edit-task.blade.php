@extends('layouts.app')

@section('content')
<div class="topbar">
    <div>
        <div class="breadcrumb">
            <a href="{{ route('strategist.dashboard') }}">Dashboard</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('strategist.tracking') }}">Tracking</a>
            <span class="breadcrumb-sep">›</span>
            <span>Edit Task</span>
        </div>
        <div class="page-title">Edit Task</div>
        <div class="page-subtitle">Update details for "{{ $task->title }}"</div>
    </div>
</div>

@if(isset($errors) && $errors->any())
    <div style="background:var(--red-dim);color:var(--red);padding:14px 18px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:13px;font-weight:600;border:1px solid rgba(239,68,68,0.15)">
        <div style="margin-bottom:6px"><i class="fas fa-exclamation-triangle" style="color:var(--red)"></i> Please fix the following errors:</div>
        <ul style="margin:0;padding-left:18px;font-weight:500">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card" style="border:1px solid var(--border);border-radius:16px;padding:28px;background:var(--card)">
    <form id="editTaskForm" method="POST" action="{{ route('strategist.tasks.update', $task) }}" data-no-loader>
        @csrf
        @method('PATCH')
        <div class="form-grid">
            {{-- SECTION 1: BASIC INFO --}}
            <div style="grid-column:1/-1;padding-bottom:14px;margin-bottom:20px;border-bottom:1px solid var(--border)">
                <div style="font-size:13px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px"><i class="fas fa-clipboard-list" style="margin-right:6px"></i> Task Details</div>
            </div>

            <div class="form-group full">
                <label>Task Title <span style="color:var(--red)">*</span></label>
                <input type="text" name="title" placeholder="e.g. Mango Campaign Reel - Social Media Blitz" value="{{ old('title', $task->title) }}" required style="font-size:14px;padding:10px 14px">
            </div>

            <div class="form-group">
                <label>Client <span style="color:var(--red)">*</span></label>
                <x-client-select :clients="$clients" name="client_id" id="clientIdInput" value="{{ old('client_id', $task->client_id) }}" placeholder="Select a client" />
            </div>

            <div class="form-group">
                <label>Content Type <span style="color:var(--red)">*</span></label>
                <select name="type" required style="background:var(--card2)">
                    <option value="">Choose type...</option>
                    <option value="reel" {{ old('type', $task->type) == 'reel' ? 'selected' : '' }}>Reel</option>
                    <option value="post" {{ old('type', $task->type) == 'post' ? 'selected' : '' }}>Post</option>
                    <option value="story" {{ old('type', $task->type) == 'story' ? 'selected' : '' }}>Story</option>
                    <option value="video" {{ old('type', $task->type) == 'video' ? 'selected' : '' }}>Video</option>
                    <option value="carousel" {{ old('type', $task->type) == 'carousel' ? 'selected' : '' }}>Carousel</option>
                    <option value="brochure" {{ old('type', $task->type) == 'brochure' ? 'selected' : '' }}>Brochure</option>
                    <option value="banner" {{ old('type', $task->type) == 'banner' ? 'selected' : '' }}>Banner</option>
                    <option value="flyer" {{ old('type', $task->type) == 'flyer' ? 'selected' : '' }}>Flyer</option>
                    <option value="website" {{ old('type', $task->type) == 'website' ? 'selected' : '' }}>Website</option>
                    <option value="software" {{ old('type', $task->type) == 'software' ? 'selected' : '' }}>Software</option>
                    <option value="maintenance" {{ old('type', $task->type) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="others" {{ old('type', $task->type) == 'others' ? 'selected' : '' }}>Others</option>
                </select>
            </div>

            <div class="form-group">
                <label>Assign To</label>
                <select name="assigned_to" id="assignSelect" style="background:var(--card2)">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        @php
                            $userRoles = $user->getAllRoles();
                            $rolesAttr = implode(',', $userRoles);
                            $rolesDisplay = implode(' / ', array_map(fn($r) => ucfirst(str_replace('_', ' ', $r)), $userRoles));
                        @endphp
                        <option value="{{ $user->id }}" data-role="{{ $user->role }}" data-roles="{{ $rolesAttr }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} · {{ $rolesDisplay }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- DEV SECTION (website / software only) --}}
            <div id="devSectionEdit" style="display:{{ in_array($task->type, ['website','software']) ? 'block' : 'none' }};grid-column:1/-1">
                <div style="background:#f5f3ff;border:1px solid #d8b4fe;border-radius:18px;padding:24px;margin-top:4px">
                    <div style="font-size:16px;font-weight:800;color:#6d28d9;margin-bottom:18px">
                        💻 Developer / Website &amp; Software Details
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px">

                        <div class="form-group" style="margin:0">
                            <label style="font-size:13px;font-weight:700;color:#5b21b6">Project Start Date</label>
                            <input type="date" name="project_start_date" value="{{ old('project_start_date', $task->project_start_date?->format('Y-m-d')) }}" style="background:#fff;border:1px solid #c4b5fd;border-radius:10px;padding:10px 12px;width:100%;box-sizing:border-box">
                        </div>

                        <div class="form-group" style="margin:0">
                            <label style="font-size:13px;font-weight:700;color:#5b21b6">Launch Date</label>
                            <input type="date" name="launch_date" value="{{ old('launch_date', $task->launch_date?->format('Y-m-d')) }}" style="background:#fff;border:1px solid #c4b5fd;border-radius:10px;padding:10px 12px;width:100%;box-sizing:border-box">
                        </div>

                        <div class="form-group" style="margin:0">
                            <label style="font-size:13px;font-weight:700;color:#5b21b6">Development Deadline</label>
                            <input type="date" name="dev_deadline" value="{{ old('dev_deadline', $task->dev_deadline?->format('Y-m-d')) }}" style="background:#fff;border:1px solid #c4b5fd;border-radius:10px;padding:10px 12px;width:100%;box-sizing:border-box">
                        </div>

                        <div class="form-group" style="margin:0">
                            <label style="font-size:13px;font-weight:700;color:#5b21b6">Preferred Technology</label>
                            <select name="preferred_tech" style="background:#fff;border:1px solid #c4b5fd;border-radius:10px;padding:10px 12px;width:100%;box-sizing:border-box">
                                <option value="">Need Developer Suggestion</option>
                                @foreach(['Laravel','Node.js','React','WordPress','Custom Solution'] as $tech)
                                    <option value="{{ $tech }}" {{ old('preferred_tech', $task->preferred_tech) == $tech ? 'selected' : '' }}>{{ $tech }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin:0">
                            <label style="font-size:13px;font-weight:700;color:#5b21b6">Pages / Modules</label>
                            <textarea name="modules" placeholder="Home, About, Contact OR Billing, Users, Reports" style="background:#fff;border:1px solid #c4b5fd;border-radius:10px;padding:10px 12px;width:100%;box-sizing:border-box;min-height:80px;resize:vertical">{{ old('modules', $task->modules) }}</textarea>
                        </div>

                        <div class="form-group" style="margin:0">
                            <label style="font-size:13px;font-weight:700;color:#5b21b6">Business Type</label>
                            <select name="business_type" style="background:#fff;border:1px solid #c4b5fd;border-radius:10px;padding:10px 12px;width:100%;box-sizing:border-box">
                                <option value="">Select Business Type...</option>
                                @foreach(['ecommerce'=>'E-Commerce','portfolio'=>'Portfolio','business_website'=>'Business Website','blog'=>'Blog / Magazine','landing_page'=>'Landing Page','custom_software'=>'Custom Software','etc'=>'Etc / Other'] as $val => $label)
                                    <option value="{{ $val }}" {{ old('business_type', $task->business_type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin:0;grid-column:1/-1">
                            <label style="font-size:13px;font-weight:700;color:#5b21b6">Project Notes</label>
                            <textarea name="project_notes" placeholder="Any additional notes for the developer..." style="background:#fff;border:1px solid #c4b5fd;border-radius:10px;padding:10px 12px;width:100%;box-sizing:border-box;min-height:80px;resize:vertical">{{ old('project_notes', $task->project_notes) }}</textarea>
                        </div>

                    </div>

                    {{-- Asset checklist --}}
                    <div style="margin-top:20px">
                        <div style="font-size:13px;font-weight:700;color:#5b21b6;margin-bottom:12px">Assets &amp; Access</div>
                        <div style="display:grid;gap:10px">
                            @foreach([
                                ['name'=>'logo_received',   'label'=>'Logo'],
                                ['name'=>'images_received', 'label'=>'Images'],
                                ['name'=>'content_received','label'=>'Content'],
                                ['name'=>'domain_purchased','label'=>'Domain Purchased?'],
                                ['name'=>'hosting_access',  'label'=>'Hosting Access?'],
                            ] as $asset)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:#fff;border:1px solid #ede9fe;border-radius:12px">
                                <span style="font-size:14px;font-weight:700;color:#2b2b2b">{{ $asset['label'] }}</span>
                                <div style="display:flex;gap:10px">
                                    <label style="padding:8px 18px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;border:2px solid #16a34a;background:{{ old($asset['name'], $task->{$asset['name']}) == 1 ? '#16a34a' : '#fff' }};color:{{ old($asset['name'], $task->{$asset['name']}) == 1 ? '#fff' : '#166534' }}">
                                        <input type="radio" name="{{ $asset['name'] }}" value="1" style="display:none" {{ old($asset['name'], $task->{$asset['name']}) == 1 ? 'checked' : '' }}> Yes
                                    </label>
                                    <label style="padding:8px 18px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;border:2px solid #dc2626;background:{{ old($asset['name'], $task->{$asset['name']}) === 0 ? '#dc2626' : '#fff' }};color:{{ old($asset['name'], $task->{$asset['name']}) === 0 ? '#fff' : '#991b1b' }}">
                                        <input type="radio" name="{{ $asset['name'] }}" value="0" style="display:none" {{ old($asset['name'], $task->{$asset['name']}) === 0 ? 'checked' : '' }}> No
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: PLATFORMS --}}
            <div id="platHeadingEdit" style="grid-column:1/-1;padding:20px 0 14px 0;margin-top:14px;border-top:1px solid var(--border);border-bottom:1px solid var(--border);display:{{ in_array($task->type, ['website','software']) ? 'none' : 'block' }}">
                <div style="font-size:13px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px"><i class="fas fa-share-alt" style="margin-right:6px"></i> Social Media Platforms</div>
            </div>

            <div id="platSectionEdit" class="form-group full" style="margin-bottom:0;display:{{ in_array($task->type, ['website','software']) ? 'none' : 'block' }}">
                <div class="platform-grid" id="platformGrid">
                    @php
                        $platforms = [
                            ['id' => 'instagram', 'name' => 'Instagram', 'icon' => '<i class="fab fa-instagram"></i>', 'color' => '#E1306C'],
                            ['id' => 'facebook', 'name' => 'Facebook', 'icon' => '<i class="fab fa-facebook"></i>', 'color' => '#1877F2'],
                            ['id' => 'linkedin', 'name' => 'LinkedIn', 'icon' => '<i class="fab fa-linkedin"></i>', 'color' => '#0077B5'],
                            ['id' => 'twitter', 'name' => 'Twitter/X', 'icon' => '<i class="fab fa-x-twitter"></i>', 'color' => '#000000'],
                            ['id' => 'tiktok', 'name' => 'TikTok', 'icon' => '<i class="fab fa-tiktok"></i>', 'color' => '#25F4EE'],
                            ['id' => 'youtube', 'name' => 'YouTube', 'icon' => '<i class="fab fa-youtube"></i>', 'color' => '#FF0000'],
                        ];
                        $oldPlatforms = old('platform', $task->platform ?? []);
                        if (!is_array($oldPlatforms)) {
                            $oldPlatforms = [];
                        }
                    @endphp
                    @foreach($platforms as $platform)
                        <label class="platform-checkbox" style="--plat-color:{{ $platform['color'] }};border-color:{{ $platform['color'] }}30;background:var(--card2)">
                            <input type="checkbox" name="platform[]" value="{{ $platform['id'] }}"
                                {{ in_array($platform['id'], $oldPlatforms) ? 'checked' : '' }}
                                onchange="togglePlatformCheckbox(this)">
                            <span class="plat-check-indicator"><i class="fa-solid fa-check"></i></span>
                            <div class="plat-icon-wrap">{!! $platform['icon'] !!}</div>
                            <div class="platform-name">{{ $platform['name'] }}</div>
                        </label>
                    @endforeach
                    <div id="customPlatformsArea"></div>
                </div>
                <div id="platformError" style="font-size:11px;color:var(--red);margin-top:10px;display:none;font-weight:600"><i class="fas fa-exclamation-triangle"></i> Select at least one platform</div>
                <div id="editSocialAccounts" style="display:none;margin-top:16px;padding:14px;background:var(--card2);border:1px solid var(--border);border-radius:12px">
                    <div style="font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px"><i class="fa-solid fa-link"></i> Social Media Accounts</div>
                    <div id="editSocialAccountsList" style="display:flex;flex-direction:column;gap:8px"></div>
                </div>
            </div>

            {{-- SECTION 3: TIMELINE & PRIORITY --}}
            <div style="grid-column:1/-1;padding:20px 0 14px 0;margin-top:14px;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
                <div style="font-size:13px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px"><i class="fas fa-calendar-alt" style="margin-right:6px"></i> Timeline &amp; Priority</div>
            </div>

            <div id="postDateGroupEdit" class="form-group" style="display:{{ in_array($task->type, ['website','software']) ? 'none' : 'block' }}">
                <label><i class="fas fa-paper-plane" style="margin-right:4px;font-size:11px"></i> Post Date</label>
                <input type="date" name="post_date" id="postDateInput" value="{{ old('post_date', $task->post_date?->format('Y-m-d')) }}" onchange="updateDatesAndPriority()" style="background:var(--card2);border:1px solid var(--border);color:var(--text1);border-radius:8px;padding:9px 12px;width:100%;box-sizing:border-box">
                <div style="font-size:10px;color:var(--text3);margin-top:6px"><i class="fas fa-calendar-check" style="color:var(--primary);margin-right:3px"></i> Change post date directly. Design deadline will auto-adjust.</div>
            </div>

            <div id="deadlineGroupEdit" class="form-group" style="display:{{ in_array($task->type, ['website','software']) ? 'none' : 'block' }}">
                <label><i class="fas fa-crosshairs" style="margin-right:4px;font-size:11px"></i> Deadline <span id="deadlineAutoTag" style="font-size:9px;font-weight:700;color:var(--teal);background:var(--teal-dim);padding:2px 8px;border-radius:4px;margin-left:6px;display:none">AUTO</span></label>
                <input type="date" name="deadline" id="deadlineInput" value="{{ old('deadline', $task->deadline?->format('Y-m-d')) }}" style="background:var(--card2);border:1px solid var(--border);color:var(--text1);border-radius:8px;padding:9px 12px;width:100%;box-sizing:border-box">
                <div style="font-size:10px;color:var(--text3);margin-top:6px" id="deadlineInfo">Design deadline auto-calculated</div>
            </div>

            <div class="form-group">
                <label>Priority Level <span style="color:var(--red)">*</span></label>
                <select name="priority" id="prioritySelect" required style="background:var(--card2);border:1px solid var(--border);font-weight:600" onchange="updatePriorityColor()">
                    <option value="normal" {{ old('priority', $task->priority) == 'normal' ? 'selected' : '' }}>Normal · Relaxed Timeline</option>
                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High · Quick Turnaround</option>
                    <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>Urgent · ASAP</option>
                </select>
                <div style="font-size:10px;color:var(--text3);margin-top:6px" id="priorityInfo">Auto-adjusted based on dates</div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" style="background:var(--card2);border:1px solid var(--border)">
                    <option value="todo" {{ old('status', $task->status) == 'todo' ? 'selected' : '' }}>To Do</option>
                    <option value="inprogress" {{ old('status', $task->status) == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                    <option value="review" {{ old('status', $task->status) == 'review' ? 'selected' : '' }}>Review</option>
                    <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="on_hold" {{ old('status', $task->status) == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="pending_approval" {{ old('status', $task->status) == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                </select>
            </div>

            {{-- SECTION 4: CONTENT & REFERENCES --}}
            <div id="contentHeadingEdit" style="grid-column:1/-1;padding:20px 0 14px 0;margin-top:14px;border-top:1px solid var(--border);border-bottom:1px solid var(--border);display:{{ in_array($task->type, ['website','software']) ? 'none' : 'block' }}">
                <div style="font-size:13px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px"><i class="fas fa-pen-fancy" style="margin-right:6px"></i> Content &amp; References</div>
            </div>

            <div id="briefGroupEdit" class="form-group full" style="display:{{ in_array($task->type, ['website','software']) ? 'none' : 'block' }}">
                <label>Content Brief</label>
                <textarea id="briefInput" name="brief" placeholder="Write creative direction or brief for the team…" style="min-height:100px" spellcheck="true" autocapitalize="sentences" autocomplete="on">{{ old('brief', $task->brief) }}</textarea>
            </div>

            <div id="captionGroupEdit" class="form-group full" style="display:{{ in_array($task->type, ['website','software']) ? 'none' : 'block' }}">
                <label><i class="fas fa-pen-nib" style="margin-right:4px;font-size:11px"></i> Publication Caption</label>
                <textarea id="captionInput" name="caption" placeholder="Write marketing caption for publication…" style="min-height:90px" spellcheck="true" autocapitalize="sentences" autocomplete="on">{{ old('caption', $task->caption) }}</textarea>
                <div style="font-size:10px;color:var(--text3);margin-top:6px">Final marketing caption used when publishing</div>
            </div>

            <div id="hashtagsGroupEdit" class="form-group full" style="display:{{ in_array($task->type, ['website','software']) ? 'none' : 'block' }}">
                <label>Hashtags</label>
                <textarea name="hashtags" placeholder="#marketing #branding #campaign #socialmedia" style="min-height:70px">{{ old('hashtags', $task->hashtags) }}</textarea>
                <div style="font-size:10px;color:var(--text3);margin-top:6px">Separated by spaces</div>
            </div>

            <div id="refGroupEdit" class="form-group full" style="display:{{ in_array($task->type, ['website','software']) ? 'none' : 'block' }}">
                <label><i class="fas fa-link" style="margin-right:4px;font-size:11px"></i> Reference Links</label>
                <div id="referenceLinksWrap" style="display:flex;flex-direction:column;gap:8px">
                    @php
                        $referenceLinks = old('reference_links', $task->reference_links ?? ['']);
                        if (!is_array($referenceLinks) || empty($referenceLinks)) {
                            $referenceLinks = [''];
                        }
                    @endphp

                    @foreach($referenceLinks as $link)
                        <div class="reference-link-row" style="display:flex;gap:8px;align-items:center">
                            <input type="url" name="reference_links[]" placeholder="https://example.com/reference" value="{{ $link }}" style="flex:1;background:var(--card2);border:1px solid var(--border)">
                            <button type="button" class="btn-sec remove-reference-link" style="padding:8px 14px;background:var(--red-dim);color:var(--red);border:1px solid var(--red)20;font-size:11px;font-weight:600">Remove</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="addReferenceLinkBtn" class="btn-sec" style="margin-top:12px;background:var(--primary-dim);color:var(--primary);border:1px dashed var(--primary);font-weight:600;padding:8px 14px">+ Add Reference Link</button>
                <div style="font-size:10px;color:var(--text3);margin-top:6px">Design docs, mood boards, brand guidelines, inspiration</div>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:28px;padding-top:20px;border-top:1px solid var(--border);flex-wrap:wrap;gap:12px">
            <div style="display:flex;gap:12px">
                <button type="submit" class="btn-primary" style="min-width:180px;font-weight:700;padding:12px;font-size:13px"><i class="fas fa-check"></i> Update Task</button>
                <a href="{{ route('strategist.tracking', ['task' => $task->id]) }}" class="btn-sec" style="padding:12px 20px;font-weight:600">Cancel</a>
            </div>
            <button type="button" onclick="if(confirm('Are you sure you want to delete this task: {{ addslashes($task->title) }}?')) document.getElementById('deleteTaskForm').submit();" class="btn-sec" style="padding:12px 20px;font-weight:600;color:var(--red);border-color:rgba(239,68,68,0.25);background:var(--red-dim);cursor:pointer">
                <i class="fas fa-trash-alt"></i> Delete Task
            </button>
        </div>
    </form>
    <form id="deleteTaskForm" method="POST" action="{{ route('strategist.tasks.destroy', $task) }}" style="display:none">
        @csrf
        @method('DELETE')
    </form>
</div>

{{-- Comments Section --}}
<div class="card strategist-edit-comments" style="margin-top:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <div style="font-weight:700;font-size:15px;color:var(--text1)"><i class="fas fa-comments" style="margin-right:6px;color:var(--primary)"></i> Comments ({{ $task->comments->count() }})</div>
    </div>

    <form method="POST" action="{{ route('strategist.tasks.comment', $task) }}" style="margin-bottom:16px">
        @csrf
        <div style="display:flex;gap:8px">
            <textarea name="body" placeholder="Add a comment or note…" required style="flex:1;min-height:60px;resize:vertical"></textarea>
            <button type="submit" class="btn-primary" style="align-self:flex-end;white-space:nowrap">Send</button>
        </div>
    </form>

    @forelse($task->comments as $comment)
        <div style="display:flex;gap:10px;padding:10px 0;border-top:1px solid var(--border)">
            <div class="av-sm" style="background:{{ $comment->user->avatar_color ?? '#555' }};flex-shrink:0">{{ $comment->user->initial }}</div>
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
                    <span style="font-weight:600;font-size:12px;color:var(--text1)">{{ $comment->user->name }}</span>
                    <span style="font-size:10px;color:var(--text3)">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
                <div style="font-size:13px;color:var(--text2);line-height:1.5;white-space:pre-wrap">{{ $comment->body }}</div>
            </div>
        </div>
    @empty
        <div style="text-align:center;padding:16px;color:var(--text3);font-size:12px">No comments yet</div>
    @endforelse
</div>

<style>
.custom-client-select{position:relative;font-size:13px}
.ccs-selected{display:flex;align-items:center;justify-content:space-between;gap:8px;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:8px 12px;cursor:pointer;transition:all .15s ease;user-select:none;min-height:38px}
.ccs-selected:hover{border-color:var(--primary)}
.ccs-selected.open{border-color:var(--primary);box-shadow:0 0 0 2px rgba(79,109,240,.15);border-radius:10px 10px 0 0}
.ccs-placeholder{color:var(--text3);font-weight:500;font-size:13px}
.ccs-arrow{font-size:10px;color:var(--text3);transition:transform .2s ease;flex-shrink:0}
.ccs-selected.open .ccs-arrow{transform:rotate(180deg)}
.ccs-sel-logo{width:20px;height:20px;border-radius:6px;object-fit:cover;flex-shrink:0;border:1px solid var(--border)}
.ccs-sel-emoji{font-size:16px;flex-shrink:0}
.ccs-sel-name{font-weight:600;color:var(--text1);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ccs-dropdown{display:none;position:absolute;top:100%;left:0;right:0;background:var(--card);border:1px solid var(--primary);border-top:none;border-radius:0 0 10px 10px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:100;overflow:hidden}
.ccs-dropdown.show{display:block}
.ccs-search-wrap{padding:6px 8px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px}
.ccs-search-icon{font-size:11px;color:var(--text3)}
.ccs-search{border:none;outline:none;background:transparent;font-size:12px;color:var(--text1);width:100%;font-family:inherit}
.ccs-search::placeholder{color:var(--text3)}
.ccs-options{max-height:220px;overflow-y:auto}
.ccs-option{display:flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer;transition:background .12s ease}
.ccs-option:hover{background:var(--primary-dim)}
.ccs-option.selected{background:rgba(79,109,240,.08)}
.ccs-logo{width:22px;height:22px;border-radius:6px;object-fit:cover;flex-shrink:0;border:1px solid var(--border)}
.ccs-emoji{font-size:16px;flex-shrink:0;width:22px;text-align:center}
.ccs-opt-name{font-size:12.5px;font-weight:500;color:var(--text1);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* Platform Grid */
.platform-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-top:10px}
@media(max-width:768px){.platform-grid{grid-template-columns:repeat(3,1fr)}}
.platform-checkbox{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;padding:18px 10px 14px;border:2px solid var(--border);border-radius:14px;cursor:pointer;transition:all .25s ease;background:var(--card2);position:relative;user-select:none;min-height:100px}
.platform-checkbox:hover{border-color:color-mix(in srgb, var(--plat-color) 50%, transparent);background:color-mix(in srgb, var(--plat-color) 6%, transparent);transform:translateY(-2px);box-shadow:0 4px 12px color-mix(in srgb, var(--plat-color) 10%, transparent)}
.platform-checkbox input[type="checkbox"]{position:absolute;opacity:0;width:0;height:0;pointer-events:none}
.platform-checkbox.platform-checked{border-color:var(--plat-color);background:color-mix(in srgb, var(--plat-color) 12%, transparent);box-shadow:0 0 0 1px color-mix(in srgb, var(--plat-color) 25%, transparent),0 4px 16px color-mix(in srgb, var(--plat-color) 15%, transparent)}
.platform-checkbox.platform-checked .plat-check-indicator{opacity:1;transform:scale(1)}
.platform-checkbox.platform-checked .plat-icon-wrap{background:var(--plat-color);color:#fff;transform:scale(1.1);box-shadow:0 4px 12px color-mix(in srgb, var(--plat-color) 35%, transparent)}
.platform-checkbox.platform-checked .platform-name{font-weight:700;color:var(--plat-color)}
.plat-check-indicator{position:absolute;top:8px;right:8px;width:18px;height:18px;background:var(--plat-color);border-radius:50%;display:flex;align-items:center;justify-content:center;opacity:0;transform:scale(0.5);transition:all .2s ease}
.plat-check-indicator i{font-size:9px;color:#fff}
.plat-icon-wrap{width:42px;height:42px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;color:var(--plat-color);background:color-mix(in srgb, var(--plat-color) 10%, transparent);border-radius:12px;transition:all .25s ease}
.platform-name{font-size:12px;font-weight:600;color:var(--text2);white-space:nowrap;transition:all .2s}
.custom-plat-row{display:flex;align-items:center;gap:8px;grid-column:1/-1}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrap = document.getElementById('referenceLinksWrap');
    const addBtn = document.getElementById('addReferenceLinkBtn');

    addBtn?.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'reference-link-row';
        row.style.cssText = 'display:flex;gap:8px;align-items:center';
        row.innerHTML = '<input type="url" name="reference_links[]" placeholder="https://example.com/reference" style="flex:1"><button type="button" class="btn-sec remove-reference-link" style="padding:8px 12px">Remove</button>';
        wrap.appendChild(row);
    });

    wrap?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-reference-link');
        if (!btn) return;
        const rows = wrap.querySelectorAll('.reference-link-row');
        if (rows.length === 1) {
            rows[0].querySelector('input')?.setAttribute('value', '');
            rows[0].querySelector('input').value = '';
            return;
        }
        btn.closest('.reference-link-row')?.remove();
    });

    const postDateInput = document.getElementById('postDateInput');
    const deadlineInput = document.getElementById('deadlineInput');
    const deadlineAutoTag = document.getElementById('deadlineAutoTag');
    const prioritySelect = document.getElementById('prioritySelect');
    const deadlineInfo = document.getElementById('deadlineInfo');
    const priorityInfo = document.getElementById('priorityInfo');
    const briefInput = document.getElementById('briefInput');

    function getFormattedDate(date) {
        return date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2,'0') + '-' + String(date.getDate()).padStart(2,'0');
    }

    function getDaysDifference(date1, date2) {
        const oneDay = 24 * 60 * 60 * 1000;
        return Math.round((date1 - date2) / oneDay);
    }

    // Display initial priority color on load
    window.updatePriorityColor = function() {
        const priority = prioritySelect.value;
        if (priority === 'urgent') {
            prioritySelect.style.borderColor = 'var(--red)';
            prioritySelect.style.boxShadow = '0 0 0 2px rgba(239,68,68,.1)';
        } else if (priority === 'high') {
            prioritySelect.style.borderColor = '#F97316';
            prioritySelect.style.boxShadow = '0 0 0 2px rgba(249,115,22,.1)';
        } else {
            prioritySelect.style.borderColor = 'var(--border)';
            prioritySelect.style.boxShadow = 'none';
        }
    };
    updatePriorityColor();

    window.updateDatesAndPriority = function() {
        if (!postDateInput || !postDateInput.value) {
            return;
        }

        const postDate = new Date(postDateInput.value + 'T00:00:00');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const in3Days = new Date(today);
        in3Days.setDate(in3Days.getDate() + 3);

        let deadline, priority, message, tagColor, tagBg;

        if (postDate.getTime() === today.getTime()) {
            deadline = today;
            priority = 'urgent';
            message = '<i class="fas fa-circle" style="font-size:8px;color:var(--red)"></i> Post is TODAY - Deadline set to TODAY - AUTO URGENT';
            tagColor = 'var(--red)';
            tagBg = 'var(--red-dim)';
        }
        else if (postDate.getTime() === tomorrow.getTime()) {
            deadline = today;
            priority = 'urgent';
            message = '<i class="fas fa-circle" style="font-size:8px;color:var(--red)"></i> Post is TOMORROW - Deadline set to TODAY - AUTO URGENT';
            tagColor = 'var(--red)';
            tagBg = 'var(--red-dim)';
        }
        else if (postDate.getTime() <= in3Days.getTime()) {
            deadline = new Date(postDate);
            deadline.setDate(deadline.getDate() - 1);
            priority = 'high';
            const daysUntilPost = getDaysDifference(postDate, today);
            message = `<i class="fas fa-circle" style="font-size:8px;color:#F97316"></i> Post in ${daysUntilPost} days - Deadline ${daysUntilPost -1} days - HIGH PRIORITY`;
            tagColor = 'var(--amber-800)';
            tagBg = 'var(--amber-100)';
        }
        else {
            deadline = new Date(postDate);
            deadline.setDate(deadline.getDate() - 5);
            priority = 'normal';
            const daysUntilPost = getDaysDifference(postDate, today);
            message = `<i class="fas fa-circle" style="font-size:8px;color:#EAB308"></i> Post in ${daysUntilPost} days - Deadline ${daysUntilPost - 5} days - NORMAL`;
            tagColor = 'var(--teal)';
            tagBg = 'var(--teal-dim)';
        }

        if (deadline < today) {
            deadline = new Date(today);
        }

        if (deadlineInput) deadlineInput.value = getFormattedDate(deadline);
        if (prioritySelect) prioritySelect.value = priority;
        if (deadlineAutoTag) {
            deadlineAutoTag.textContent = 'AUTO';
            deadlineAutoTag.style.display = 'inline-block';
            deadlineAutoTag.style.background = tagBg;
            deadlineAutoTag.style.color = tagColor;
        }
        if (deadlineInfo) deadlineInfo.innerHTML = `<strong>${message}</strong>`;
        if (priorityInfo) priorityInfo.innerHTML = `<i class="fas fa-chart-bar" style="font-size:10px;opacity:0.5"></i> Auto-adjusted: <strong>${priority.toUpperCase()}</strong>`;

        updatePriorityColor();
    };

    postDateInput?.addEventListener('change', updateDatesAndPriority);



    // Toggle dev vs social sections based on task type
    function toggleEditSections(typeVal) {
        const isDev = typeVal === 'website' || typeVal === 'software';
        const show = (id, visible) => {
            const el = document.getElementById(id);
            if (el) el.style.display = visible ? '' : 'none';
        };
        show('devSectionEdit',      isDev);
        show('platHeadingEdit',     !isDev);
        show('platSectionEdit',     !isDev);
        show('postDateGroupEdit',   !isDev);
        show('deadlineGroupEdit',   !isDev);
        show('contentHeadingEdit',  !isDev);
        show('briefGroupEdit',      !isDev);
        show('hashtagsGroupEdit',   !isDev);
        show('refGroupEdit',        !isDev);
    }

    function filterAssigneeDropdownEdit() {
        const assignSelect = document.getElementById('assignSelect');
        const typeField = document.querySelector('#editTaskForm [name="type"]');
        if (!typeField || !assignSelect) return;
        const typeVal = (typeField.value || '').trim().toLowerCase();
        
        let allowedRoles = null;
        const printMediaTypes = ['brochure', 'banner', 'flyer'];
        const devMediaTypes = ['website', 'software'];
        const socialMediaTypes = ['reel', 'post', 'story', 'carousel', 'video'];

        if (devMediaTypes.includes(typeVal)) {
            allowedRoles = ['developer'];
        } else if (printMediaTypes.includes(typeVal)) {
            allowedRoles = ['designer'];
        } else if (socialMediaTypes.includes(typeVal)) {
            allowedRoles = ['strategist', 'content_writer', 'editor', 'manager', 'designer'];
        }

        const options = assignSelect.querySelectorAll('option');
        let selectedOptionStillValid = false;
        const currentSelectedVal = assignSelect.value;

        options.forEach(opt => {
            if (!opt.value) {
                opt.hidden = false;
                opt.disabled = false;
                opt.style.display = '';
                return;
            }

            const rolesAttr = opt.getAttribute('data-roles') || opt.getAttribute('data-role') || '';
            const userRoles = rolesAttr.split(',').map(r => r.trim().toLowerCase()).filter(Boolean);

            let isMatch = true;
            if (allowedRoles) {
                isMatch = userRoles.some(r => allowedRoles.includes(r));
            }

            if (isMatch) {
                opt.hidden = false;
                opt.disabled = false;
                opt.style.display = '';
                if (opt.value === currentSelectedVal) {
                    selectedOptionStillValid = true;
                }
            } else {
                opt.hidden = true;
                opt.disabled = true;
                opt.style.display = 'none';
            }
        });

        if (currentSelectedVal && !selectedOptionStillValid) {
            assignSelect.value = '';
        }
    }

    const typeSelectEdit = document.querySelector('#editTaskForm [name="type"]');
    if (typeSelectEdit) {
        typeSelectEdit.addEventListener('change', function () {
            toggleEditSections(this.value.toLowerCase());
            filterAssigneeDropdownEdit();
        });
        // Run on load to set initial state
        toggleEditSections(typeSelectEdit.value.toLowerCase());
        filterAssigneeDropdownEdit();
    }

    // Apply visual checked state to pre-selected platform checkboxes
    document.querySelectorAll('input[name="platform[]"]:checked').forEach(cb => {
        cb.closest('.platform-checkbox')?.classList.add('platform-checked');
    });

    const editClientInput = document.querySelector('#editTaskForm [name="client_id"]');
    const editAccountsBox = document.getElementById('editSocialAccounts');
    const editAccountsList = document.getElementById('editSocialAccountsList');
    const savedSocialLinkIds = @json(array_map('intval', (array) old('selected_social_media_link_ids', $task->selected_social_media_link_ids ?? [])));
    let editClientSocialLinks = [];

    function renderEditSocialAccounts() {
        const selectedPlatforms = new Set(Array.from(document.querySelectorAll('input[name="platform[]"]:checked')).map(input => input.value));
        const links = editClientSocialLinks.filter(link => selectedPlatforms.has(link.platform));
        if (!links.length) {
            editAccountsBox.style.display = 'none';
            editAccountsList.innerHTML = '';
            return;
        }
        editAccountsList.innerHTML = links.map(link => `
            <label style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--card);border:1px solid var(--border);border-radius:9px;cursor:pointer">
                <input type="checkbox" name="selected_social_media_link_ids[]" value="${link.id}" ${savedSocialLinkIds.includes(Number(link.id)) ? 'checked' : ''}>
                <span style="font-size:11px;font-weight:700;text-transform:capitalize;color:var(--primary);min-width:72px">${link.platform}</span>
                <span style="font-size:12px;color:var(--text);overflow-wrap:anywhere">${link.label || link.url}</span>
                ${link.is_primary ? '<span style="margin-left:auto;font-size:9px;font-weight:800;color:var(--yellow)">PRIMARY</span>' : ''}
            </label>`).join('');
        editAccountsBox.style.display = 'block';
    }

    function loadEditSocialAccounts() {
        const clientId = editClientInput?.value;
        if (!clientId) {
            editClientSocialLinks = [];
            renderEditSocialAccounts();
            return;
        }
        fetch(`/api/clients/${clientId}/social-links`, { headers: { Accept: 'application/json' } })
            .then(response => response.ok ? response.json() : Promise.reject())
            .then(data => { editClientSocialLinks = data.links || []; renderEditSocialAccounts(); })
            .catch(() => { editClientSocialLinks = []; renderEditSocialAccounts(); });
    }

    editClientInput?.addEventListener('change', loadEditSocialAccounts);
    document.querySelectorAll('input[name="platform[]"]').forEach(input => input.addEventListener('change', renderEditSocialAccounts));
    loadEditSocialAccounts();

    // Platform validation — only for non-dev types
    window.validatePlatforms = function() {
        const typeVal = (document.querySelector('#editTaskForm [name="type"]')?.value || '').toLowerCase();
        if (typeVal === 'website' || typeVal === 'software') return true;
        const checked = document.querySelectorAll('input[name="platform[]"]:checked');
        const errorEl = document.getElementById('platformError');
        if (checked.length === 0) {
            if (errorEl) errorEl.style.display = 'block';
        } else {
            if (errorEl) errorEl.style.display = 'none';
        }
        return checked.length > 0;
    };

    // Toggle platform checkbox visual state
    window.togglePlatformCheckbox = function(checkbox) {
        const label = checkbox.closest('.platform-checkbox');
        if (checkbox.checked) {
            label.classList.add('platform-checked');
        } else {
            label.classList.remove('platform-checked');
        }
        validatePlatforms();
    };

    // Platform auto-detect map: keywords/domains → icon + color + display name
    const platformMap = [
        { keys: ['instagram', 'instagr.am', 'insta'], fa: 'fa-brands fa-instagram', color: '#E1306C', name: 'Instagram' },
        { keys: ['facebook', 'fb.com', 'fb'], fa: 'fa-brands fa-facebook-f', color: '#1877F2', name: 'Facebook' },
        { keys: ['linkedin', 'linked'], fa: 'fa-brands fa-linkedin-in', color: '#0077B5', name: 'LinkedIn' },
        { keys: ['twitter', 'x.com', 'tweet'], fa: 'fa-brands fa-x-twitter', color: '#000000', name: 'Twitter/X' },
        { keys: ['tiktok', 'tik tok'], fa: 'fa-brands fa-tiktok', color: '#010101', name: 'TikTok' },
        { keys: ['youtube', 'youtu.be', 'yt'], fa: 'fa-brands fa-youtube', color: '#FF0000', name: 'YouTube' },
        { keys: ['pinterest', 'pin.it'], fa: 'fa-brands fa-pinterest-p', color: '#E60023', name: 'Pinterest' },
        { keys: ['snapchat', 'snap'], fa: 'fa-brands fa-snapchat', color: '#FFFC00', name: 'Snapchat' },
        { keys: ['whatsapp', 'wa.me'], fa: 'fa-brands fa-whatsapp', color: '#25D366', name: 'WhatsApp' },
        { keys: ['telegram', 't.me'], fa: 'fa-brands fa-telegram', color: '#26A5E4', name: 'Telegram' },
        { keys: ['reddit'], fa: 'fa-brands fa-reddit-alien', color: '#FF4500', name: 'Reddit' },
        { keys: ['discord'], fa: 'fa-brands fa-discord', color: '#5865F2', name: 'Discord' },
        { keys: ['tumblr'], fa: 'fa-brands fa-tumblr', color: '#36465D', name: 'Tumblr' },
        { keys: ['spotify'], fa: 'fa-brands fa-spotify', color: '#1DB954', name: 'Spotify' },
        { keys: ['twitch'], fa: 'fa-brands fa-twitch', color: '#9146FF', name: 'Twitch' },
        { keys: ['threads'], fa: 'fa-brands fa-threads', color: '#000000', name: 'Threads' },
        { keys: ['behance'], fa: 'fa-brands fa-behance', color: '#1769FF', name: 'Behance' },
        { keys: ['dribbble'], fa: 'fa-brands fa-dribbble', color: '#EA4C89', name: 'Dribbble' },
        { keys: ['medium'], fa: 'fa-brands fa-medium', color: '#000000', name: 'Medium' },
        { keys: ['vimeo'], fa: 'fa-brands fa-vimeo-v', color: '#1AB7EA', name: 'Vimeo' },
        { keys: ['sharechat'], fa: 'fa-solid fa-share-nodes', color: '#F52D56', name: 'ShareChat' },
        { keys: ['moj'], fa: 'fa-solid fa-clapperboard', color: '#EE1233', name: 'Moj' },
        { keys: ['koo', 'kooapp'], fa: 'fa-solid fa-feather', color: '#FACD00', name: 'Koo' },
        { keys: ['quora'], fa: 'fa-brands fa-quora', color: '#B92B27', name: 'Quora' },
        { keys: ['github'], fa: 'fa-brands fa-github', color: '#181717', name: 'GitHub' },
        { keys: ['weibo'], fa: 'fa-brands fa-weibo', color: '#E6162D', name: 'Weibo' },
        { keys: ['signal'], fa: 'fa-solid fa-signal-messenger', color: '#3A76F0', name: 'Signal' },
        { keys: ['clubhouse'], fa: 'fa-solid fa-hand-wave', color: '#F2E351', name: 'Clubhouse' },
    ];

    function detectPlatform(input) {
        const val = input.toLowerCase().trim();
        if (!val) return null;
        for (const p of platformMap) {
            for (const k of p.keys) {
                if (val.includes(k)) return p;
            }
        }
        return null;
    }

    function extractDomainName(input) {
        const val = input.trim();
        try {
            let url = val;
            if (!url.match(/^https?:\/\//i)) url = 'https://' + url;
            const hostname = new URL(url).hostname.replace(/^www\./, '');
            const domain = hostname.split('.')[0];
            if (domain && domain.length > 1) {
                return domain.charAt(0).toUpperCase() + domain.slice(1);
            }
        } catch (e) {}
        return null;
    }

    function handlePlatformInput(inputEl) {
        const val = inputEl.value;
        const label = inputEl.closest('.platform-checkbox');
        const iconEl = label.querySelector('.plat-icon-wrap i');
        const hiddenInput = label.querySelector('input[name="platform[]"]');
        const detected = detectPlatform(val);

        if (detected) {
            iconEl.className = detected.fa;
            label.style.setProperty('--plat-color', detected.color);
            hiddenInput.value = detected.keys[0];
            inputEl.value = detected.name;
            inputEl.style.color = detected.color;
        } else {
            const domainName = extractDomainName(val);
            if (domainName) {
                iconEl.className = 'fa-solid fa-link';
                label.style.setProperty('--plat-color', '#6366F1');
                hiddenInput.value = domainName.toLowerCase();
                inputEl.value = domainName;
                inputEl.style.color = '#6366F1';
            } else {
                iconEl.className = 'fa-solid fa-globe';
                label.style.setProperty('--plat-color', '#6B7280');
                hiddenInput.value = val.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
                inputEl.style.color = '';
            }
        }
    }

    // Add More Platform button
    const addPlatBtn = document.getElementById('addPlatformBtn');
    const customArea = document.getElementById('customPlatformsArea');
    if (addPlatBtn) {
        addPlatBtn.addEventListener('click', function() {
            const row = document.createElement('div');
            row.className = 'custom-plat-row';
            row.innerHTML = `
                <label class="platform-checkbox platform-checked" style="--plat-color:#6B7280;flex:1">
                    <input type="checkbox" name="platform[]" value="" checked onchange="togglePlatformCheckbox(this)">
                    <span class="plat-check-indicator"><i class="fa-solid fa-check"></i></span>
                    <span class="plat-icon-wrap"><i class="fa-solid fa-globe"></i></span>
                    <input type="text" class="custom-plat-name" placeholder="Type name or paste link..."
                        style="border:none;background:transparent;font-size:12px;font-weight:600;color:var(--text);outline:none;width:140px;padding:0;text-align:center">
                </label>
                <button type="button" class="btn-sec" onclick="this.closest('.custom-plat-row').remove();validatePlatforms()"
                    style="padding:6px 10px;font-size:11px;color:var(--red);border-color:rgba(239,68,68,0.3);flex-shrink:0">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            customArea.appendChild(row);
            const nameInput = row.querySelector('.custom-plat-name');
            nameInput.focus();
            nameInput.addEventListener('input', function() { handlePlatformInput(this); });
            nameInput.addEventListener('paste', function() { setTimeout(() => handlePlatformInput(this), 50); });
            validatePlatforms();
        });
    }

    function applyBriefAutocorrect(text) {
        if (!text || !text.trim()) return text;

        const corrections = {
            teh: 'the',
            recieve: 'receive',
            adress: 'address',
            seperat: 'separate',
            definately: 'definitely',
            occured: 'occurred',
            untill: 'until',
            wich: 'which',
            becuase: 'because',
            improvment: 'improvement',
            sucess: 'success',
            thier: 'their',
            wotking: 'working',
            dont: "don't",
            doesnt: "doesn't",
            cant: "can't",
            wont: "won't"
        };

        let result = text;

        function escapeForRegex(value) {
            return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        Object.entries(corrections).forEach(([wrong, right]) => {
            const re = new RegExp('\\b' + escapeForRegex(wrong) + '\\b', 'gi');
            result = result.replace(re, (m) => {
                if (m === m.toUpperCase()) return right.toUpperCase();
                if (m[0] === m[0].toUpperCase()) return right[0].toUpperCase() + right.slice(1);
                return right;
            });
        });

        result = result.replace(/[ \t]{2,}/g, ' ');
        result = result.replace(/\s+([,.!?;:])/g, '$1');

        // Capitalize sentence starts without changing the rest of the sentence style.
        result = result.replace(/(^|[.!?]\s+)([a-z])/g, (m, p1, p2) => p1 + p2.toUpperCase());

        return result;
    }

    briefInput?.addEventListener('blur', function() {
        const corrected = applyBriefAutocorrect(this.value);
        if (corrected !== this.value) {
            this.value = corrected;
        }
    });

    // Form submission validation
    const form = document.querySelector('#editTaskForm');
    form?.addEventListener('submit', function(e) {
        if (briefInput) {
            briefInput.value = applyBriefAutocorrect(briefInput.value);
        }

        let hasError = false;

        // Title validation
        const titleField = form.querySelector('[name="title"]');
        if (titleField) {
            const title = titleField.value.trim();
            if (!title) {
                titleField.style.borderColor = 'var(--red)';
                hasError = true;
            } else {
                titleField.style.borderColor = '';
            }
        }

        const clientInput = document.getElementById('clientIdInput');
        const editTypeVal = (form.querySelector('[name="type"]')?.value || '').toLowerCase();
        const editIsDevType = editTypeVal === 'website' || editTypeVal === 'software';
        if (clientInput && !clientInput.value && !editIsDevType) {
            const btn = document.getElementById('cs_btn_clientIdInput');
            if (btn) { btn.style.borderColor = 'var(--red)'; btn.style.boxShadow = '0 0 0 2px rgba(239,68,68,0.15)'; }
            hasError = true;
        }

        // Type validation
        const typeField = form.querySelector('[name="type"]');
        if (typeField && !typeField.value) {
            typeField.style.borderColor = 'var(--red)';
            hasError = true;
        }

        // Platform validation — skip for dev project types
        const currentType = (form.querySelector('[name="type"]')?.value || '').toLowerCase();
        const isDevType = currentType === 'website' || currentType === 'software';
        if (!isDevType) {
            const checked = document.querySelectorAll('input[name="platform[]"]:checked');
            if (checked.length === 0) {
                const platErr = document.getElementById('platformError');
                if (platErr) platErr.style.display = 'block';
                hasError = true;
            }
        }

        if (hasError) {
            e.preventDefault();
            const firstErrField = form.querySelector('[style*="border-color: var(--red)"], [style*="border-color:var(--red)"]');
            if (firstErrField) firstErrField.scrollIntoView({ behavior:'smooth', block:'center' });
            return false;
        }

        // AJAX form submission
        e.preventDefault();
        submitEditTaskAJAX(this);
    });
});

async function submitEditTaskAJAX(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn?.innerHTML;
    let response = null;
    if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...'; }

    console.log('Starting AJAX request to:', form.action);
    console.log('Form data:', Object.fromEntries(formData));

    try {
        response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json'
            },
            body: formData
        });

        console.log('Response received:', response.status, response.statusText);

        let data = {};
        const contentType = response.headers.get('content-type');
        if (contentType?.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            try { data = JSON.parse(text); } catch (e) {
                throw new Error(`Server error (${response.status}): ${response.statusText}`);
            }
        }

        if (!response.ok) {
            if (response.status === 422 && data.errors) {
                const messages = Object.values(data.errors).flat().join(' · ');
                throw new Error(messages || data.message || 'Validation failed.');
            }
            throw new Error(data.message || `Failed to update task (${response.status})`);
        }

        if (data.success) {
            if (typeof ajax !== 'undefined') ajax.showSuccess(data.message || 'Task updated successfully!');
            setTimeout(() => window.location.reload(), 350);
        } else {
            throw new Error(data.message || 'An unknown error occurred.');
        }
    } catch (error) {
        console.error('Edit task error:', error);
        if (typeof ajax !== 'undefined') ajax.showError(error.message || 'Failed to update task.');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
}
</script>
@endsection
