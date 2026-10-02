@extends('layouts.app')

@section('content')

<style>

#receivedBox{
    width:100%;
    padding:22px;
    border-radius:16px;
    background:#ffffff;
    border:1px solid #e5e7eb;
    margin-top:14px;
    box-shadow:0 8px 24px rgba(0,0,0,0.04);
}

#receivedBox h4{
    margin:0 0 18px 0;
    font-size:16px;
    font-weight:800;
    color:#111827;
}

.receive-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:14px 0;
    border-bottom:1px solid #f1f5f9;
}

.receive-row:last-child{
    border-bottom:none;
}

.receive-row span{
    font-size:14px;
    font-weight:700;
    color:#111827;
}

.receive-row label{
    margin-left:8px;
    cursor:pointer;
}

.receive-row input[type="radio"]{
    display:none;
}

.receive-row label{
    padding:8px 14px;
    border-radius:10px;
    font-size:13px;
    font-weight:700;
    transition:all .2s ease;
    border:1px solid transparent;
}

/* YES */
.receive-row label:has(input[value="1"]){
    background:#ecfdf5;
    color:#166534;
    border-color:#bbf7d0;
}

/* NO */
.receive-row label:has(input[value="0"]){
    background:#fef2f2;
    color:#991b1b;
    border-color:#fecaca;
}

/* Checked YES */
.receive-row label:has(input[value="1"]:checked){
    background:#22c55e;
    color:#fff;
    transform:scale(1.03);
}

/* Checked NO */
.receive-row label:has(input[value="0"]:checked){
    background:#ef4444;
    color:#fff;
    transform:scale(1.03);
}



.receive-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:12px 0;
    border-bottom:1px solid #f1f5f9;
}

.receive-row:last-child{
    border-bottom:none;
}

.receive-row span{
    font-weight:700;
}

.receive-row label{
    margin-left:10px;
    opacity:.65;
}




/* option buttons */
.receive-row label{
    margin-left:10px;
    cursor:pointer;
    padding:9px 16px;
    border-radius:12px;
    font-size:13px;
    font-weight:800;
    border:2px solid transparent;
    transition:all .22s ease;
    letter-spacing:.2px;
    min-width:62px;
    text-align:center;
    box-shadow:0 2px 6px rgba(0,0,0,.03);
}

/* hide radio */
.receive-row input[type="radio"]{
    display:none;
}

/* YES normal */
.receive-row label:has(input[value="1"]){
    background:#ecfdf5;
    color:#166534;
    border-color:#22c55e;
}

/* NO normal */
.receive-row label:has(input[value="0"]){
    background:#fef2f2;
    color:#991b1b;
    border-color:#ef4444;
}

/* hover */
.receive-row label:hover{
    transform:translateY(-1px);
    box-shadow:0 6px 14px rgba(0,0,0,.06);
}

/* YES selected */
.receive-row label:has(input[value="1"]:checked){
    background:#16a34a;
    color:#ffffff;
    border-color:#166534;
    transform:scale(1.03);
}

/* NO selected */
.receive-row label:has(input[value="0"]:checked){
    background:#dc2626;
    color:#ffffff;
    border-color:#7f1d1d;
    transform:scale(1.03);
}







#devSection .dev-card{
    background:#f5f3ff;
    border:1px solid #d8b4fe;
    border-radius:18px;
    padding:24px;
    margin-top:14px;
    box-sizing:border-box;
}

#devSection .dev-title{
    font-size:16px;
    font-weight:800;
    color:#6d28d9;
    margin-bottom:18px;
}

#devSection .dev-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:16px;
    width:100%;
}

#devSection .form-group{
    margin:0;
    width:100%;
}

#devSection .form-group label{
    display:block;
    font-size:13px;
    font-weight:700;
    color:#5b21b6;
    margin-bottom:7px;
}

#devSection input,
#devSection select,
#devSection textarea{
    width:100%;
    max-width:100%;
    box-sizing:border-box;
    background:#fff;
    border:1px solid #c4b5fd;
    border-radius:10px;
    padding:10px 12px;
}

#devSection textarea{
    min-height:90px;
    resize:vertical;
}

@media(max-width:768px){
    #devSection .dev-grid{
        grid-template-columns:1fr;
    }
}



.asset-title{
   margin-bottom:10px;
}



/* heading gap */
.full-width{
    grid-column:1/-1;
    margin-top:28px;
}

/* actions */
.asset-actions{
    display:flex;
    gap:12px;
}

/* common buttons */
.asset-row label{
    min-width:74px;
    text-align:center;
    padding:10px 0;
    border-radius:14px;
    font-size:14px;
    font-weight:800;
    cursor:pointer;
    border:2px solid;
    transition:.22s ease;
    background:#fff;
}

/* hide radio */
.asset-row input[type="radio"]{
    display:none;
}

/* YES default */
.asset-row .yes-btn{
    color:#166534 !important;
    border-color:#16a34a !important;
    background:#ffffff !important;
}

/* NO default */
.asset-row .no-btn{
    color:#991b1b !important;
    border-color:#dc2626 !important;
    background:#ffffff !important;
}

/* YES selected */
.asset-row .yes-btn:has(input:checked){
    background:#dcfce7 !important;
    color:#166534 !important;
    border-color:#16a34a !important;
}

/* NO selected */
.asset-row .no-btn:has(input:checked){
    background:#fee2e2 !important;
    color:#991b1b !important;
    border-color:#dc2626 !important;
}

/* hover */
.asset-row label:hover{
    transform:translateY(-1px);
    box-shadow:0 6px 14px rgba(0,0,0,.05);
}




/* wrapper */
.asset-grid{
    display:grid;
    gap:14px;
    margin-top:10px;
}

/* each row card */
.asset-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:18px 22px;
    background:#ffffff;
    border:1px solid #ececec;
    border-radius:16px;
    box-shadow:0 8px 20px rgba(0,0,0,.05);
    transition:.22s ease;
}

/* hover */
.asset-row:hover{
    transform:translateY(-2px);
    box-shadow:0 12px 28px rgba(0,0,0,.08);
}

/* text */
.asset-row span{
    font-size:15px;
    font-weight:800;
    color:#2b2b2b;
}

/* buttons right */
.asset-actions{
    display:flex;
    gap:12px;
    align-items:center;
}


#briefGroup,
#referenceGroup,
#contentSection{
    clear:both;
    width:100%;
}

.account-link-url {
    font-size: 11px;
    color: #6b7280;
    margin-top: 2px;
    line-height: 1.35;
    max-width: 100%;
    white-space: normal;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.input-invalid {
    border-color: var(--red) !important;
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.12) !important;
}

.input-valid {
    border-color: #16a34a !important;
    box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.14) !important;
}

#platformGrid.input-valid {
    outline: 2px solid rgba(22, 163, 74, 0.35);
    outline-offset: 6px;
    border-radius: 14px;
}

.field-error-msg {
    margin-top: 6px;
    font-size: 11px;
    font-weight: 600;
    color: var(--red);
    line-height: 1.35;
}

.field-ok-msg {
    margin-top: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #16a34a;
    line-height: 1.35;
}
</style>


<div class="topbar ct-create-hero">
    <div>
        <div class="breadcrumb">
            <a href="{{ route('strategist.dashboard') }}">Dashboard</a>
            <span class="breadcrumb-sep">›</span>
            <span>Create Task</span>
        </div>
        <div class="page-title"><i class="fas fa-plus-circle" style="margin-right:8px;opacity:0.7"></i>Create New Task</div>
        <div class="page-subtitle">Plan, assign &amp; track your content or development workflow</div>
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

<div class="ct-create-layout">
<aside class="ct-task-rail">
    <div class="ct-rail-label">Task forms <span id="draftSaveStatus" class="ct-draft-status">Draft ready</span></div>
    <div id="taskFormNav" class="ct-task-nav"></div>
    <button type="button" id="addBatchTaskBtn" class="ct-rail-add"><i class="fa-solid fa-plus"></i><span>Add Task</span></button>
    <button type="button" id="previousTaskBtn" class="ct-rail-back" disabled><i class="fa-solid fa-arrow-left"></i><span>Previous</span></button>
</aside>
<div class="card ct-create-card" id="ctFormPanel" style="border:1px solid var(--border);border-radius:16px;padding:28px;background:var(--card)">
    <form id="createTaskForm" method="POST" action="{{ route('strategist.tasks.store') }}" data-no-loader="true">
        @csrf
        <div class="form-grid">
            {{-- SECTION 1: BASIC INFO --}}
            <div style="grid-column:1/-1;padding-bottom:14px;margin-bottom:20px;border-bottom:1px solid var(--border)">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
                    <div style="font-size:13px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px"><i class="fas fa-clipboard-list" style="margin-right:4px"></i> Task Details</div>
                    <span id="activeTaskNumber" class="ct-active-task-number">Task 01</span>
                </div>
            </div>

            <div class="form-group full">
                <label for="title">Task Title <span style="color:var(--red)">*</span></label>
                <input id="title" type="text" name="title" placeholder="e.g. Mango Campaign Reel - Social Media Blitz" value="{{ old('title') }}" style="font-size:14px;padding:10px 14px">
            </div>

            <div class="form-group">
                <label>Client <span style="color:var(--red)">*</span></label>
                <x-client-select :clients="$clients" name="client_id" id="clientIdInput" value="{{ old('client_id') }}" placeholder="Select a client" />
            </div>

            <div class="form-group">
                <label>Content Type <span style="color:var(--red)">*</span></label>
                <select name="type" style="background:var(--card2)" id="typeSelect">
                    <option value="">Choose type...</option>
                    <optgroup label="Social Media">
                        <option value="reel"     {{ old('type') == 'reel'     ? 'selected' : '' }}>Reel</option>
                        <option value="post"     {{ old('type') == 'post'     ? 'selected' : '' }}>Post</option>
                        <option value="story"    {{ old('type') == 'story'    ? 'selected' : '' }}>Story</option>
                        <option value="carousel" {{ old('type') == 'carousel' ? 'selected' : '' }}>Carousel</option>
                        <option value="video"    {{ old('type') == 'video'    ? 'selected' : '' }}>Video</option>
                    </optgroup>
                    <optgroup label="Print Media">
                        <option value="brochure" {{ old('type') == 'brochure' ? 'selected' : '' }}>Brochure</option>
                        <option value="banner"   {{ old('type') == 'banner'   ? 'selected' : '' }}>Banner</option>
                        <option value="flyer"    {{ old('type') == 'flyer'    ? 'selected' : '' }}>Flyer</option>
                    </optgroup>
                    <optgroup label="Development">
                        <option value="website"  {{ old('type') == 'website'  ? 'selected' : '' }}>Website</option>
                        <option value="software" {{ old('type') == 'software' ? 'selected' : '' }}>Software</option>
                    </optgroup>
                    <option value="others" {{ old('type') == 'others' ? 'selected' : '' }}>Others</option>
                </select>
            </div>


            <div id="typeNotice" style="display:none;"></div>
            <div id="devClientNote" style="display:none;grid-column:1/-1;margin-top:-8px;padding:10px 14px;background:#eff6ff;border:1px solid #93c5fd;border-radius:10px;font-size:12px;color:#1d4ed8;font-weight:600">
                <i class="fas fa-info-circle" style="margin-right:6px"></i>
                A client must be selected. If the client doesn't exist yet, ask an admin to add the client first, then come back to create this task.
                All other dev fields (tech, dates, assets) can be filled in or updated later via the task edit page.
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
                        <option value="{{ $user->id }}" data-role="{{ $user->role }}" data-roles="{{ $rolesAttr }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} · {{ $rolesDisplay }}
                        </option>
                    @endforeach
                </select>
            </div>




            <div id="devSection" style="display:none;grid-column:1/-1;">
    <div class="dev-card">

        <div class="dev-title">
            💻 Developer / Website & Software Details
        </div>

        <div class="dev-grid">

            <div class="form-group">
                <label>Project Start Date</label>
                <input type="date" name="project_start_date">
            </div>

            <div class="form-group">
                <label>Launch Date</label>
                <input type="date" name="launch_date">
            </div>

            <div class="form-group">
                <label>Development Deadline</label>
                <input type="date" name="dev_deadline">
            </div>

            <div class="form-group">
                <label>Preferred Technology</label>
                <select name="preferred_tech">
                    <option value="">Need Developer Suggestion</option>
                    <option>Laravel</option>
                    <option>Node.js</option>
                    <option>React</option>
                    <option>WordPress</option>
                    <option>Custom Solution</option>
                </select>
            </div>

            <div class="form-group">
                <label>Pages / Modules</label>
                <textarea name="modules" placeholder="Home, About, Contact OR Billing, Users, Reports"></textarea>
            </div>

            <div class="form-group">
                <label>Business Type</label>
                <select name="business_type">
                    <option value="">Select Business Type...</option>
                    <option value="ecommerce">E-Commerce</option>
                    <option value="portfolio">Portfolio</option>
                    <option value="business_website">Business Website</option>
                    <option value="blog">Blog / Magazine</option>
                    <option value="landing_page">Landing Page</option>
                    <option value="custom_software">Custom Software</option>
                    <option value="etc">Etc / Other</option>
                </select>
            </div>

        </div>



        <div class="form-group full-width">
    <label>Assets & Access</label>

    <div class="asset-grid">







        <div class="asset-row">
    <span>Logo</span>
    <div class="asset-actions">
        <label class="yes-btn">
            <input type="radio" name="logo_received" value="1"> Yes
        </label>
        <label class="no-btn">
            <input type="radio" name="logo_received" value="0"> No
        </label>
    </div>
</div>

<div class="asset-row">
    <span>Images</span>
    <div class="asset-actions">
        <label class="yes-btn">
            <input type="radio" name="images_received" value="1"> Yes
        </label>
        <label class="no-btn">
            <input type="radio" name="images_received" value="0"> No
        </label>
    </div>
</div>

<div class="asset-row">
    <span>Content</span>
    <div class="asset-actions">
        <label class="yes-btn">
            <input type="radio" name="content_received" value="1"> Yes
        </label>
        <label class="no-btn">
            <input type="radio" name="content_received" value="0"> No
        </label>
    </div>
</div>

<div class="asset-row">
    <span>Domain Purchased?</span>
    <div class="asset-actions">
        <label class="yes-btn">
            <input type="radio" name="domain_purchased" value="1"> Yes
        </label>
        <label class="no-btn">
            <input type="radio" name="domain_purchased" value="0"> No
        </label>
    </div>
</div>

<div class="asset-row">
    <span>Hosting Access?</span>
    <div class="asset-actions">
        <label class="yes-btn">
            <input type="radio" name="hosting_access" value="1"> Yes
        </label>
        <label class="no-btn">
            <input type="radio" name="hosting_access" value="0"> No
        </label>
    </div>
</div>



    </div>
</div>




    </div>
</div>




            {{-- SECTION 2: PLATFORMS --}}
            <div id="socialHeading" style="grid-column:1/-1;padding:20px 0 14px 0;margin-top:14px;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
                <div style="font-size:13px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px"><i class="fas fa-share-alt" style="margin-right:4px"></i> Social Media Platforms</div>
            </div>

            <div id="socialSection" class="form-group full" style="margin-bottom:0">
                <div class="platform-grid" id="platformGrid">
                    @php
                        $platforms = [
                            ['id' => 'instagram', 'name' => 'Instagram', 'fa' => 'fa-instagram', 'color' => '#E1306C'],
                            ['id' => 'facebook', 'name' => 'Facebook', 'fa' => 'fa-facebook-f', 'color' => '#1877F2'],
                            ['id' => 'linkedin', 'name' => 'LinkedIn', 'fa' => 'fa-linkedin-in', 'color' => '#0077B5'],
                            ['id' => 'twitter', 'name' => 'Twitter/X', 'fa' => 'fa-x-twitter', 'color' => '#000000'],
                            ['id' => 'whatsapp', 'name' => 'WhatsApp', 'fa' => 'fa-whatsapp', 'color' => '#25D366'],
                            ['id' => 'youtube', 'name' => 'YouTube', 'fa' => 'fa-youtube', 'color' => '#FF0000'],
                        ];
                        $oldPlatforms = old('platform', []);
                        if (!is_array($oldPlatforms)) {
                            $oldPlatforms = [];
                        }
                    @endphp
                    @foreach($platforms as $platform)
                        <label class="platform-checkbox" data-platform="{{ $platform['id'] }}" style="--plat-color:{{ $platform['color'] }};border-color:{{ $platform['color'] }}30;background:var(--card2)">
                            <input type="checkbox" name="platform[]" value="{{ $platform['id'] }}"
                                {{ in_array($platform['id'], $oldPlatforms) ? 'checked' : '' }}
                                onchange="togglePlatformCheckbox(this)">
                            <span class="plat-check-indicator"><i class="fa-solid fa-check"></i></span>
                            <span class="plat-availability">Connected</span>
                            <span class="plat-icon-wrap"><i class="fa-brands {{ $platform['fa'] }}"></i></span>
                            <div class="platform-name">{{ $platform['name'] }}</div>
                        </label>
                    @endforeach
                    <div id="customPlatformsArea" style="grid-column:1/-1;display:flex;flex-direction:column;gap:10px;"></div>
                </div>
                <div style="display:flex;align-items:center;gap:12px;margin-top:16px;flex-wrap:wrap">
                    <button type="button" id="addPlatformBtn" class="add-plat-btn">
                        <span class="add-plat-icon"><i class="fa-solid fa-plus"></i></span>
                        <span>Add Platform</span>
                    </button>
                    <div id="platformError" class="plat-error-msg"><i class="fas fa-exclamation-circle"></i> Select at least one platform</div>
                </div>

            {{-- Account Selection Modal --}}
            <div id="accountSelectionModal" style="display:none">
                <div style="background:var(--card);border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.15);overflow:hidden">
                    <div style="padding:24px;border-bottom:1px solid var(--border)">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                            <h2 style="font-size:18px;font-weight:700;color:var(--text);margin:0">
                                <span id="modalPlatformName">Select Account</span>
                            </h2>
                            <button type="button" onclick="closeAccountModal()" style="background:none;border:none;color:var(--text3);cursor:pointer;font-size:20px"><i class="fas fa-xmark"></i></button>
                        </div>
                        <p style="font-size:12px;color:var(--text2);margin:0">Select which account to create content for</p>
                    </div>
                    <div style="padding:20px;max-height:300px;overflow-y:auto">
                        <div id="accountSelectOptions" style="display:flex;flex-direction:column;gap:8px"></div>
                    </div>
                    <div style="padding:16px;background:var(--card2);border-top:1px solid var(--border);display:flex;justify-content:space-between;gap:12px">
                        <button type="button" onclick="usePrimaryAccount()" style="flex:1;padding:10px;background:var(--border);border:none;border-radius:8px;color:var(--text);cursor:pointer;font-weight:600;font-size:12px">Use Primary</button>
                        <button type="button" onclick="applyAccountSelection()" style="flex:1;padding:10px;background:var(--primary);border:none;border-radius:8px;color:#fff;cursor:pointer;font-weight:600;font-size:12px;transition:all 0.2s">✓ Confirm</button>
                    </div>
                </div>
            </div>

            {{-- Selected Accounts Summary --}}
            <div id="selectedAccountsSummary" style="grid-column:1/-1;margin-top:16px;padding:14px;background:linear-gradient(135deg, #10B98110 0%, #4F6DF010 100%);border:1.5px solid var(--primary);border-radius:10px;display:none">
                <div style="font-size:11px;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;display:flex;align-items:center;gap:6px">
                    <i class="fas fa-check-circle"></i> Selected Accounts
                </div>
                <div id="selectedAccountsList" style="display:flex;flex-direction:column;gap:6px"></div>
            </div>
            </div>

<style>
.add-plat-btn {
    display:inline-flex;align-items:center;gap:8px;
    padding:7px 16px 7px 8px;
    background:var(--card);
    border:1.5px dashed color-mix(in srgb, var(--primary) 40%, var(--border));
    border-radius:10px;
    color:var(--primary);
    font-size:12px;font-weight:650;
    cursor:pointer;
    transition:all 0.25s cubic-bezier(0.25,0.8,0.25,1);
    position:relative;overflow:hidden;
    font-family:inherit;
}
.add-plat-btn::before {
    content:'';position:absolute;inset:0;
    background:linear-gradient(135deg, var(--primary-dim), transparent 60%);
    opacity:0;transition:opacity 0.25s;
}
.add-plat-btn:hover {
    border-color:var(--primary);border-style:solid;
    background:var(--primary-dim);
    transform:translateY(-1px);
    box-shadow:0 4px 12px color-mix(in srgb, var(--primary) 15%, transparent);
}
.add-plat-btn:hover::before { opacity:1; }
.add-plat-btn:hover .add-plat-icon { background:var(--primary);color:#fff;transform:rotate(90deg); }
.add-plat-btn:active { transform:translateY(0);box-shadow:none; }
.add-plat-icon {
    width:22px;height:22px;
    display:flex;align-items:center;justify-content:center;
    background:var(--primary-dim);color:var(--primary);
    border-radius:6px;font-size:10px;
    transition:all 0.3s cubic-bezier(0.25,0.8,0.25,1);
    position:relative;z-index:1;
}
.plat-error-msg {
    display:none;font-size:11px;font-weight:600;
    color:var(--red);
    background:var(--red-dim);
    padding:6px 12px;border-radius:8px;
    border:1px solid rgba(239,68,68,0.12);
    animation:platErrShake 0.4s ease;
}
@keyframes platErrShake {
    0%,100%{transform:translateX(0)} 20%{transform:translateX(-4px)} 40%{transform:translateX(4px)} 60%{transform:translateX(-2px)} 80%{transform:translateX(2px)}
}

#accountSelectionModal{
    display:none;
    position:fixed;
    inset:0;
    justify-content:center !important;
    align-items:center;
    padding:20px !important;
    animation:fadeIn .2s ease !important;
    z-index:2000;
}


#accountSelectionModal > div {
    background: var(--card);
    border-radius: 16px;
    width: 90%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

#accountSelectOptions label:hover {
    background: var(--primary-dim) !important;
    border-color: var(--primary) !important;
}

#accountSelectOptions label input:checked + div {
    color: var(--primary);
}

.plat-availability {
    position: absolute;
    top: 6px;
    right: 8px;
    font-size: 8px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
    display: none;
    z-index: 2;
    letter-spacing: 0.3px;
}
.platform-checkbox.is-available {
    border-style: solid;
    border-width: 1.5px;
}
.platform-checkbox.is-available .plat-availability {
    display: block;
    background: var(--teal-dim);
    color: var(--teal);
}
.platform-checkbox.not-available {
    opacity: 0.6;
    filter: grayscale(0.4);
}
.platform-checkbox.not-available .plat-availability {
    display: block;
    background: var(--text3);
    color: #fff;
    opacity: 0.5;
}




#typeNotice{
    display:none;
    grid-column:1/-1;
    margin-top:12px;
    padding:14px 16px;
    border-radius:12px;
    font-size:14px;
    font-weight:700;
    border:1px solid transparent;
    animation:fadeType .25s ease;
}

#typeNotice.website{
    background:linear-gradient(135deg,#eff6ff,#dbeafe);
    color:#1d4ed8;
    border-color:#93c5fd;
}

#typeNotice.software{
    background:linear-gradient(135deg,#ecfeff,#cffafe);
    color:#0f766e;
    border-color:#67e8f9;
}

@keyframes fadeType{
    from{opacity:0;transform:translateY(8px)}
    to{opacity:1;transform:translateY(0)}
}





#briefGroup textarea,
#referenceGroup input,
#referenceLinksWrap input{
    width:100% !important;
    max-width:100% !important;
    box-sizing:border-box !important;
    display:block;
}

#briefGroup,
#referenceGroup{
    grid-column:1/-1 !important;
    width:100% !important;
}
</style>

            {{-- SECTION 3: TIMELINE & PRIORITY --}}
            <div id="timelineSection" style="grid-column:1/-1;padding:20px 0 14px 0;margin-top:14px;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
                <div style="font-size:13px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px"><i class="fas fa-calendar-alt" style="margin-right:4px"></i> Timeline & Priority</div>
            </div>

            <div id="postDateGroup" class="form-group">
                <label><i class="fas fa-paper-plane" style="margin-right:3px"></i> Post Date <span style="color:var(--red)">*</span></label>
                <input type="date" name="post_date" id="postDateInput" value="{{ old('post_date') }}" onchange="updateDatesAndPriority()" style="background:var(--card2);border:1px solid var(--border)">
                <div style="font-size:10px;color:var(--text3);margin-top:6px">ⓘ Today or future dates only</div>
            </div>

            <div id="deadlineGroup" class="form-group">
                <label><i class="fas fa-crosshairs" style="margin-right:3px"></i> Deadline <span id="deadlineAutoTag" style="font-size:9px;font-weight:700;color:var(--teal);background:var(--teal-dim);padding:2px 8px;border-radius:4px;margin-left:6px;display:none">AUTO</span></label>
                <input type="date" name="deadline" id="deadlineInput" value="{{ old('deadline') }}" readonly style="background:var(--card2);color:var(--text2);cursor:not-allowed;opacity:0.75;border:1px solid var(--border)">
                <div style="font-size:10px;color:var(--text3);margin-top:6px" id="deadlineInfo">Design deadline auto-calculated</div>
            </div>

            <div class="form-group">
                <label for="priority">Priority Level <span style="color:var(--red)">*</span></label>
                <select name="priority" id="prioritySelect" style="background:var(--card2);border:1px solid var(--border);font-weight:600" onchange="updatePriorityColor()">
                    <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal · Relaxed Timeline</option>
                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High · Quick Turnaround</option>
                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent · ASAP</option>
                </select>
                <div style="font-size:10px;color:var(--text3);margin-top:6px" id="priorityInfo">Auto-adjusted based on dates</div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" style="background:var(--card2);border:1px solid var(--border)">
                    <option value="todo" {{ old('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                    <option value="inprogress" {{ old('status') == 'inprogress' ? 'selected' : '' }}>In Progress</option>
                </select>
            </div>

            {{-- SECTION 4: CONTENT & REFERENCES --}}
            <div id="contentSection" style="grid-column:1/-1;padding:20px 0 14px 0;margin-top:14px;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
                <div style="font-size:13px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px"><i class="fas fa-pen-fancy" style="margin-right:4px"></i> Content & References</div>
            </div>

            <div id="briefGroup" class="form-group full">
                <label>Content Brief <span style="color:var(--red)">*</span></label>
                <textarea id="briefInput" name="brief" placeholder="Write caption..." style="min-height:100px;width:100%;display:block;">{{ old('brief') }}</textarea>
                <div id="briefOkMsg" class="field-ok-msg" style="display:none"><i class="fas fa-check-circle"></i> Okay</div>
            </div>

            <div id="referenceGroup" class="form-group full">
                <label><i class="fas fa-link" style="margin-right:3px"></i> Reference Links</label>
                <div id="referenceLinksWrap" style="display:flex;flex-direction:column;gap:8px">
                    @php
                        $referenceLinks = old('reference_links', ['']);
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

        <div id="batchTaskList" hidden></div>
        <div id="batchTaskError" class="field-error-msg" style="display:none"></div>

        <div class="ct-form-actions" style="display:flex;gap:12px;margin-top:28px;padding-top:20px;border-top:1px solid var(--border)">
            <button type="submit" name="submit_scope" value="current" class="btn-sec" style="min-width:190px;font-weight:700;padding:12px;font-size:13px">
                <i class="fa-regular fa-square-check"></i> <span id="createCurrentTaskLabel">Create This Task Only</span>
            </button>
            <button type="submit" name="submit_scope" value="all" class="btn-primary" style="min-width:190px;font-weight:700;padding:12px;font-size:13px">
                <i class="fa-solid fa-layer-group"></i> <span id="createAllTasksLabel">Create All Tasks</span>
            </button>
            <a href="{{ route('strategist.dashboard') }}" class="btn-sec" style="padding:12px 20px;font-weight:600">Cancel</a>
        </div>
    </form>
</div>
</div>

<style>
.ct-create-hero { border-radius:18px !important; padding:22px 24px !important; background:linear-gradient(135deg,var(--card),color-mix(in srgb,var(--primary) 7%,var(--card))) !important; border:1px solid var(--border) !important; }
.ct-create-layout { width:100%; max-width:1320px; margin:0 auto; display:flex; flex-direction:column; gap:14px; }
.ct-create-card { width:100%; min-width:0; margin:0; box-shadow:0 14px 40px rgba(15,23,42,.07); }
.ct-task-rail { position:sticky; top:12px; padding:11px 12px; border:1px solid color-mix(in srgb,var(--primary) 14%,var(--border)); border-radius:14px; background:color-mix(in srgb,var(--card) 94%,transparent); backdrop-filter:blur(14px); box-shadow:0 8px 28px rgba(15,23,42,.08); z-index:20; display:flex; align-items:center; gap:10px; }
.ct-rail-label { padding:0 10px 0 4px; color:var(--text3); font-size:10px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; white-space:nowrap; border-right:1px solid var(--border); }
.ct-draft-status { display:block; margin-top:3px; color:#059669; font-size:8px; font-weight:700; letter-spacing:.02em; text-transform:none; }
.ct-task-nav { display:flex; flex:1; min-width:0; flex-direction:row; gap:7px; overflow-x:auto; scrollbar-width:thin; padding:1px; }
.ct-task-nav-btn,.ct-rail-add,.ct-rail-back { width:auto; min-width:max-content; min-height:38px; display:flex; align-items:center; justify-content:center; gap:8px; padding:8px 11px; border-radius:10px; border:1px solid var(--border); background:var(--card2); color:var(--text2); font-size:11.5px; font-weight:700; cursor:pointer; transition:all .18s ease; }
.ct-task-nav-btn:hover,.ct-rail-back:hover:not(:disabled) { border-color:var(--primary); color:var(--primary); transform:translateY(-1px); }
.ct-task-nav-btn.active { color:#fff; border-color:var(--primary); background:var(--primary); box-shadow:0 5px 14px color-mix(in srgb,var(--primary) 25%,transparent); }
.ct-task-nav-btn .ct-nav-index { width:22px; height:22px; display:flex; align-items:center; justify-content:center; border-radius:7px; background:rgba(255,255,255,.16); font-size:9px; }
.ct-task-nav-item { display:flex; align-items:center; min-width:max-content; border-radius:10px; }
.ct-task-delete { width:28px; height:28px; margin-left:3px; display:flex; align-items:center; justify-content:center; flex-shrink:0; border:0; border-radius:8px; color:var(--red); background:var(--red-dim); cursor:pointer; opacity:.75; transition:all .15s ease; }
.ct-task-delete:hover { opacity:1; transform:scale(1.06); }
.ct-rail-add { color:#fff; border-color:var(--primary); background:var(--primary); box-shadow:0 4px 12px color-mix(in srgb,var(--primary) 22%,transparent); }
.ct-rail-add:hover:not(:disabled) { transform:translateY(-1px); box-shadow:0 7px 16px color-mix(in srgb,var(--primary) 28%,transparent); }
.ct-rail-back { background:transparent; }
.ct-rail-back:disabled { opacity:.4; cursor:not-allowed; }
.ct-active-task-number { padding:5px 9px; border-radius:999px; color:var(--primary); background:var(--primary-dim); font-size:10px; font-weight:800; white-space:nowrap; }
.ct-create-card.ct-form-enter { animation:ctFormEnter .32s cubic-bezier(.2,.8,.2,1); }
.ct-create-card.ct-form-back { animation:ctFormBack .32s cubic-bezier(.2,.8,.2,1); }
@keyframes ctFormEnter { from { opacity:.25; transform:translateX(26px) scale(.985); } to { opacity:1; transform:none; } }
@keyframes ctFormBack { from { opacity:.25; transform:translateX(-26px) scale(.985); } to { opacity:1; transform:none; } }
.ct-create-card .form-grid { gap:18px 20px; }
.ct-create-card .form-group > label { font-weight:700; margin-bottom:7px; }
.ct-create-card input:not([type="radio"]):not([type="checkbox"]),.ct-create-card select,.ct-create-card textarea { border-radius:10px !important; min-height:42px; }
.ct-batch-panel { margin-top:26px; padding:18px; border:1px solid color-mix(in srgb,var(--primary) 20%,var(--border)); border-radius:14px; background:color-mix(in srgb,var(--primary) 4%,var(--card)); }
.ct-batch-head { display:flex; align-items:center; justify-content:space-between; gap:16px; }
.ct-batch-title { display:flex; align-items:center; gap:8px; color:var(--text); font-size:14px; font-weight:800; }
.ct-batch-title i { color:var(--primary); }
.ct-batch-subtitle { color:var(--text3); font-size:11.5px; margin-top:4px; }
.ct-add-task-btn { display:inline-flex; align-items:center; gap:7px; white-space:nowrap; }
.ct-batch-list { display:flex; flex-direction:column; gap:10px; margin-top:14px; }
.ct-batch-row { display:flex; align-items:center; gap:10px; padding:10px; border:1px solid var(--border); border-radius:11px; background:var(--card); }
.ct-batch-number { width:28px; height:28px; flex-shrink:0; display:flex; align-items:center; justify-content:center; border-radius:8px; color:var(--primary); background:var(--primary-dim); font-size:11px; font-weight:800; }
.ct-batch-row input { flex:1; min-width:0; margin:0; }
.ct-batch-remove { width:36px; height:36px; flex-shrink:0; display:flex; align-items:center; justify-content:center; border:0; border-radius:9px; color:var(--red); background:var(--red-dim); cursor:pointer; }
.ct-form-actions { justify-content:flex-end; align-items:center; }
.ct-submit-choice { position:fixed; inset:0; z-index:10020; display:flex; align-items:center; justify-content:center; padding:20px; background:rgba(15,23,42,.48); backdrop-filter:blur(5px); opacity:0; visibility:hidden; pointer-events:none; transition:opacity .2s ease,visibility .2s ease; }
.ct-submit-choice.show { opacity:1; visibility:visible; pointer-events:auto; }
.ct-submit-choice-card { width:min(520px,100%); padding:28px; border:1px solid color-mix(in srgb,var(--primary) 18%,var(--border)); border-radius:20px; background:var(--card); box-shadow:0 24px 70px rgba(15,23,42,.24); text-align:center; transform:translateY(18px) scale(.97); transition:transform .25s cubic-bezier(.2,.8,.2,1); }
.ct-submit-choice.show .ct-submit-choice-card { transform:none; }
.ct-submit-choice-icon { width:54px; height:54px; margin:0 auto 14px; display:flex; align-items:center; justify-content:center; border-radius:16px; color:var(--primary); background:var(--primary-dim); font-size:21px; }
.ct-submit-choice-card h3 { margin:0; color:var(--text); font-size:18px; font-weight:800; }
.ct-submit-choice-card p { max-width:400px; margin:8px auto 22px; color:var(--text3); font-size:12.5px; line-height:1.6; }
.ct-submit-choice-actions { display:flex; align-items:center; justify-content:center; gap:9px; flex-wrap:wrap; }
.ct-submit-choice-actions button { min-height:42px; padding:10px 14px; font-weight:700; }
@media (max-width:700px) {
    .ct-task-rail { top:0; display:grid; grid-template-columns:minmax(0,1fr) auto auto; gap:8px; padding:9px; border-radius:12px; }
    .ct-rail-label { display:none; }
    .ct-task-nav { flex-direction:row; overflow-x:auto; }
    .ct-task-nav-btn { width:auto; min-width:max-content; }
    .ct-rail-add,.ct-rail-back { width:42px; height:40px; margin:0; padding:0; justify-content:center; }
    .ct-rail-add span,.ct-rail-back span { display:none; }
    .ct-create-card { padding:18px !important; }
    .ct-batch-head { align-items:flex-start; flex-direction:column; }
    .ct-add-task-btn { width:100%; justify-content:center; }
    .ct-form-actions { flex-direction:column; }
    .ct-form-actions > * { width:100% !important; justify-content:center; text-align:center; box-sizing:border-box; }
    .ct-submit-choice-actions { flex-direction:column; }
    .ct-submit-choice-actions button { width:100%; }
}
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
.selected-account-badge{position:absolute;bottom:6px;left:6px;right:6px;font-size:8.5px;background:var(--plat-color);color:#fff;padding:3px 5px;border-radius:5px;text-overflow:ellipsis;overflow:hidden;white-space:nowrap;font-weight:600;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.2);z-index:2}
</style>

<script>
// Global toggle function for received box
window.toggleReceivedBox = function () {

const receivedBox = document.getElementById('receivedBox');
    const socialHeading = document.getElementById('socialHeading');
const socialSection = document.getElementById('socialSection');
const timelineSection = document.getElementById('timelineSection');
const contentSection = document.getElementById('contentSection');

const postDateGroup = document.getElementById('postDateGroup');
const postDateInput = document.getElementById('postDateInput');
const deadlineGroup = document.getElementById('deadlineGroup');
const briefGroup = document.getElementById('briefGroup');
const referenceGroup = document.getElementById('referenceGroup');


    const devSection = document.getElementById('devSection');
    const typeSelectEl = document.getElementById('typeSelect');
    const notice = document.getElementById('typeNotice');

    const platformSection = document.getElementById('platformGrid')?.closest('.form-group.full');

    if (!typeSelectEl || !notice) return;

    const val = typeSelectEl.value.trim().toLowerCase();

    notice.style.display = 'none';

    notice.className = '';

    if (devSection) devSection.style.display = 'none';

    const clientNote = document.getElementById('devClientNote');

    const hideSocialSections = () => {
        if (socialHeading)  socialHeading.style.display  = 'none';
        if (socialSection)  socialSection.style.display  = 'none';
        if (timelineSection)timelineSection.style.display= 'none';
        if (contentSection) contentSection.style.display = 'none';
        if (postDateGroup)  postDateGroup.style.display  = 'none';
        if (deadlineGroup)  deadlineGroup.style.display  = 'none';
        if (briefGroup)     briefGroup.style.display     = 'none';
        if (referenceGroup) referenceGroup.style.display = 'none';
        if (platformSection)platformSection.style.display= 'none';
    };

    const showSocialSections = () => {
        if (socialHeading)  socialHeading.style.display  = 'block';
        if (socialSection)  socialSection.style.display  = 'block';
        if (timelineSection)timelineSection.style.display= 'block';
        if (contentSection) contentSection.style.display = 'block';
        if (postDateGroup)  postDateGroup.style.display  = 'block';
        if (deadlineGroup)  deadlineGroup.style.display  = 'block';
        if (briefGroup)     briefGroup.style.display     = 'block';
        if (referenceGroup) referenceGroup.style.display = 'block';
        if (platformSection)platformSection.style.display= 'block';
    };

    if (val === 'website') {
        hideSocialSections();
        if (devSection) devSection.style.display = 'block';
        if (clientNote) clientNote.style.display = 'block';
        notice.style.display = 'block';
        notice.classList.add('website');
        notice.innerHTML = '🌐 Website Project — Fill developer details below. Priority &amp; status are always saved.';
    }

    else if (val === 'software') {
        hideSocialSections();
        if (devSection) devSection.style.display = 'block';
        if (clientNote) clientNote.style.display = 'block';
        notice.style.display = 'block';
        notice.classList.add('software');
        notice.innerHTML = '💻 Software Project — Fill developer details below. Priority &amp; status are always saved.';
    }

    else {
        showSocialSections();
        if (devSection) devSection.style.display = 'none';
        if (clientNote) clientNote.style.display = 'none';
    }
};


document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('createTaskForm');
    const postDateInput = document.getElementById('postDateInput');

    const wrap = document.getElementById('referenceLinksWrap');
    const addBtn = document.getElementById('addReferenceLinkBtn');
    const clientIdInput = document.getElementById('clientIdInput');

    const deadlineInput = document.getElementById('deadlineInput');
    const deadlineAutoTag = document.getElementById('deadlineAutoTag');
    const prioritySelect = document.getElementById('prioritySelect');
    const deadlineInfo = document.getElementById('deadlineInfo');
    const priorityInfo = document.getElementById('priorityInfo');
    const briefInput = document.getElementById('briefInput');
    const assignSelect = document.getElementById('assignSelect');
    const statusSelect = form?.querySelector('[name="status"]');

    const typeSelect = document.getElementById('typeSelect');
    const devTypes = ['website', 'software'];

    function normalizeFieldName(field) {
        if (!field) return '';
        if (field === 'platform' || field.startsWith('platform.')) return 'platform';
        if (field === 'reference_links' || field.startsWith('reference_links.')) return 'reference_links';
        return field;
    }

    function isNonDevTypeSelected() {
        const typeVal = (typeSelect?.value || '').trim().toLowerCase();
        return !devTypes.includes(typeVal);
    }

    function isFieldRequired(field) {
        const normalized = normalizeFieldName(field);
        const alwaysRequired = ['title', 'client_id', 'type', 'priority', 'status'];
        const nonDevRequired = ['brief', 'platform', 'post_date'];

        if (alwaysRequired.includes(normalized)) return true;
        if (isNonDevTypeSelected() && nonDevRequired.includes(normalized)) return true;
        return false;
    }

    function clearInlineValidationErrors() {
        form?.querySelectorAll('.field-error-msg').forEach(el => el.remove());
        form?.querySelectorAll('.input-invalid').forEach(el => el.classList.remove('input-invalid'));

        const platformError = document.getElementById('platformError');
        if (platformError) {
            platformError.textContent = 'Select at least one platform';
            platformError.style.display = 'none';
        }
    }

    function appendFieldError(target, message) {
        if (!target || !message) return;

        target.classList.remove('input-valid');
        target.classList.add('input-invalid');

        const container = target.closest('.form-group, .reference-link-row, .asset-row, .custom-client-select') || target.parentElement;
        if (!container) return;

        if (container.querySelector('.field-error-msg')) return;

        const msg = document.createElement('div');
        msg.className = 'field-error-msg';
        msg.textContent = message;
        container.appendChild(msg);
    }

    function getFieldElement(field) {
        const normalized = normalizeFieldName(field);
        if (!normalized) return null;

        if (normalized === 'client_id') {
            return document.querySelector('#clientIdInput')?.closest('.custom-client-select')?.querySelector('.ccs-selected')
                || document.getElementById('clientIdInput');
        }

        if (normalized === 'platform') {
            return document.getElementById('platformGrid');
        }

        if (normalized === 'reference_links') {
            return document.querySelector('input[name="reference_links[]"]');
        }

        if (normalized === 'status') {
            return statusSelect;
        }

        return form?.querySelector(`[name="${normalized}"]`) || form?.querySelector(`[name="${normalized}[]"]`);
    }

    function getFieldContainer(target, field) {
        if (!target) return null;

        const normalized = normalizeFieldName(field);
        if (normalized === 'platform') {
            return document.getElementById('socialSection') || target.closest('.form-group') || target.parentElement;
        }

        return target.closest('.form-group, .reference-link-row, .asset-row, .custom-client-select') || target.parentElement;
    }

    function getOrCreateOkMessage(field, container) {
        if (!container) return null;

        const normalized = normalizeFieldName(field);
        const existingInContainer = container.querySelector(`.field-ok-msg[data-field-ok="${normalized}"]`);
        if (existingInContainer) return existingInContainer;

        if (normalized === 'brief') {
            const briefExisting = document.getElementById('briefOkMsg');
            if (briefExisting) {
                briefExisting.dataset.fieldOk = 'brief';
                return briefExisting;
            }
        }

        const msg = document.createElement('div');
        msg.className = 'field-ok-msg';
        msg.dataset.fieldOk = normalized;
        msg.style.display = 'none';
        msg.innerHTML = '<i class="fas fa-check-circle"></i> Okay';
        container.appendChild(msg);
        return msg;
    }

    function toggleFieldOkayState(field, shouldShow) {
        const normalized = normalizeFieldName(field);
        const target = getFieldElement(normalized);

        if (target) {
            target.classList.toggle('input-valid', Boolean(shouldShow));
        }

        if (normalized === 'platform') {
            const platformError = document.getElementById('platformError');
            if (platformError && shouldShow) {
                platformError.style.display = 'none';
            }
        }

        const container = getFieldContainer(target, normalized);
        const okMsg = getOrCreateOkMessage(normalized, container);
        if (okMsg) {
            okMsg.style.display = shouldShow ? 'block' : 'none';
        }
    }

    function clearFieldError(field) {
        const normalized = normalizeFieldName(field);

        if (normalized === 'platform') {
            const platformError = document.getElementById('platformError');
            if (platformError) {
                platformError.textContent = 'Select at least one platform';
                platformError.style.display = 'none';
            }
            return;
        }

        const target = getFieldElement(normalized);
        if (!target) return;

        target.classList.remove('input-invalid');
        const container = getFieldContainer(target, normalized);
        container?.querySelectorAll('.field-error-msg').forEach(el => el.remove());
    }

    function isFieldValid(field) {
        const normalized = normalizeFieldName(field);
        if (!isFieldRequired(normalized)) return false;

        if (normalized === 'title') {
            const titleInput = form?.querySelector('[name="title"]');
            return Boolean(titleInput?.value.trim());
        }

        if (normalized === 'client_id') {
            return Boolean(clientIdInput?.value);
        }

        if (normalized === 'type') {
            return Boolean(typeSelect?.value);
        }

        if (normalized === 'priority') {
            return Boolean(prioritySelect?.value);
        }

        if (normalized === 'status') {
            return Boolean(statusSelect?.value);
        }

        if (normalized === 'brief') {
            return Boolean(briefInput?.value.trim());
        }

        
        if (normalized === 'post_date') {
            const raw = postDateInput?.value;
            if (!raw) return false;

            const selectedDate = new Date(raw + 'T00:00:00');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return selectedDate >= today;
        }

        if (normalized === 'platform') {
            const checked = document.querySelectorAll('input[name="platform[]"]:checked');
            return checked.length > 0;
        }

        const el = getFieldElement(normalized);
        return Boolean(el && el.value);
    }

    function updateFieldValidationState(field) {
        const normalized = normalizeFieldName(field);
        const required = isFieldRequired(normalized);

        if (!required) {
            clearFieldError(normalized);
            toggleFieldOkayState(normalized, false);
            return;
        }

        const valid = isFieldValid(normalized);
        toggleFieldOkayState(normalized, valid);

        if (valid) {
            clearFieldError(normalized);
        }
    }

    function updateAllFieldValidationStates() {
        ['title', 'client_id', 'type', 'priority', 'status', 'brief', 'platform', 'post_date']
            .forEach(updateFieldValidationState);
    }

    function renderValidationErrors(errors) {
        clearInlineValidationErrors();

        let firstInvalid = null;

        Object.entries(errors || {}).forEach(([field, messages]) => {
            const message = Array.isArray(messages) ? messages[0] : messages;
            const normalizedField = normalizeFieldName(field);

            toggleFieldOkayState(normalizedField, false);

            if (field === 'platform' || field.startsWith('platform.')) {
                const platformError = document.getElementById('platformError');
                if (platformError) {
                    platformError.textContent = message || 'Select at least one platform';
                    platformError.style.display = 'block';
                }
                if (!firstInvalid) firstInvalid = document.getElementById('platformGrid');
                return;
            }

            const el = getFieldElement(field);
            if (el) {
                appendFieldError(el, message);
                if (!firstInvalid) firstInvalid = el;
            }
        });

        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        updateAllFieldValidationStates();
    }

    form?.querySelector('[name="title"]')?.addEventListener('input', () => updateFieldValidationState('title'));
    clientIdInput?.addEventListener('change', () => updateFieldValidationState('client_id'));
    typeSelect?.addEventListener('change', () => updateFieldValidationState('type'));
    prioritySelect?.addEventListener('change', () => updateFieldValidationState('priority'));
    statusSelect?.addEventListener('change', () => updateFieldValidationState('status'));
    briefInput?.addEventListener('input', () => updateFieldValidationState('brief'));
    postDateInput?.addEventListener('change', () => updateFieldValidationState('post_date'));

    document.addEventListener('change', function(e) {
        if (e.target.matches('input[name="platform[]"]')) {
            updateFieldValidationState('platform');
        }
    });







    // Store social links globally for modal
    window.platformAccountMap = {};
    window.allClientSocialLinks = null;


    window.filterAssigneeDropdown = function () {
        if (!typeSelect || !assignSelect) return;
        const typeVal = (typeSelect.value || '').trim().toLowerCase();
        
        let recommendedRoles = null;
        
        const printMediaTypes = ['brochure', 'banner', 'flyer'];
        const devMediaTypes = ['website', 'software'];
        const socialMediaTypes = ['reel', 'post', 'story', 'carousel', 'video'];

        if (devMediaTypes.includes(typeVal)) {
            recommendedRoles = ['developer', 'admin', 'manager'];
        } else if (printMediaTypes.includes(typeVal)) {
            recommendedRoles = ['designer', 'admin', 'manager'];
        } else if (socialMediaTypes.includes(typeVal)) {
            recommendedRoles = ['strategist', 'content_writer', 'editor', 'manager', 'designer', 'admin'];
        }

        const options = assignSelect.querySelectorAll('option');
        options.forEach(opt => {
            if (!opt.value) return;
            opt.hidden = false;
            opt.disabled = false;
            opt.style.display = '';
        });
    };

if (typeSelect) {
    typeSelect.addEventListener('change', function () {
        window.filterAssigneeDropdown();
        window.toggleReceivedBox();
        window.validatePlatforms();
        updateAllFieldValidationStates();
    });
}

// Initial run
window.filterAssigneeDropdown();
window.toggleReceivedBox();
updateAllFieldValidationStates();


    // --- Reference Links Logic ---
    function createRow(value = '') {
        const row = document.createElement('div');
        row.className = 'reference-link-row';
        row.style.display = 'flex';
        row.style.gap = '8px';
        row.style.alignItems = 'center';
        row.innerHTML = `
            <input type="url" name="reference_links[]" placeholder="https://example.com/reference" style="flex:1" value="${value}">
            <button type="button" class="btn-sec remove-reference-link" style="padding:8px 12px">Remove</button>
        `;
        return row;
    }

    addBtn?.addEventListener('click', function () {
        wrap.appendChild(createRow());
    });

    wrap?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-reference-link');
        if (!btn) return;

        const rows = wrap.querySelectorAll('.reference-link-row');
        if (rows.length === 1) {
            const input = rows[0].querySelector('input[name="reference_links[]"]');
            if (input) input.value = '';
            return;
        }

        btn.closest('.reference-link-row')?.remove();
    });

    // --- Smart Date & Priority Logic ---
    function setMinDate() {
        const today = new Date();
        const iso = today.getFullYear() + '-' + String(today.getMonth()+1).padStart(2,'0') + '-' + String(today.getDate()).padStart(2,'0');
        if (postDateInput) postDateInput.min = iso;
    }
    setMinDate();

    function getFormattedDate(date) {
        return date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2,'0') + '-' + String(date.getDate()).padStart(2,'0');
    }

    function getDaysDifference(date1, date2) {
        const oneDay = 24 * 60 * 60 * 1000;
        return Math.round((date1 - date2) / oneDay);
    }

    window.updateDatesAndPriority = function() {
        if (!postDateInput || !postDateInput.value) {
            if (deadlineInput) deadlineInput.value = '';
            if (deadlineAutoTag) deadlineAutoTag.style.display = 'none';
            if (deadlineInfo) deadlineInfo.textContent = '';
            if (priorityInfo) priorityInfo.textContent = '';
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
    if (postDateInput?.value) updateDatesAndPriority();


    function togglePostDateRequired() {

    if (!typeSelect || !postDateInput) return;

    const val = typeSelect.value.toLowerCase();

    if (val === 'website' || val === 'software') {
    } else {
    }
}

typeSelect?.addEventListener('change', togglePostDateRequired);

togglePostDateRequired();

    window.updatePriorityColor = function() {
        if (!prioritySelect) return;
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

    // --- Social Links Logic ---
    window.loadClientSocialMediaLinks = function(clientId) {
        // Reset platform indicators
        document.querySelectorAll('.platform-checkbox').forEach(el => {
            el.classList.remove('is-available', 'not-available');
            const badge = el.querySelector('.plat-availability');
            if (badge) badge.textContent = 'Connected';
        });

        if (!clientId) {
            window.allClientSocialLinks = null;
            window.platformAccountMap = {};
            document.getElementById('selectedAccountsSummary').style.display = 'none';
            return;
        }

        fetch(`/api/clients/${clientId}/social-links`)

            .then(res => res.ok ? res.json() : Promise.reject())
            .then(data => {
                window.allClientSocialLinks = data.links || [];

                // Update availability indicators
                const availablePlatforms = new Set(window.allClientSocialLinks.map(l => l.platform));
                document.querySelectorAll('.platform-checkbox[data-platform]').forEach(el => {
                    const p = el.getAttribute('data-platform');
                    if (availablePlatforms.has(p)) {
                        el.classList.add('is-available');
                        const count = window.allClientSocialLinks.filter(l => l.platform === p).length;
                        const badge = el.querySelector('.plat-availability');
                        if (badge) badge.textContent = count > 1 ? `${count} Accounts` : 'Connected';
                    } else {
                        el.classList.add('not-available');
                        const badge = el.querySelector('.plat-availability');
                        if (badge) badge.textContent = 'Missing';
                    }
                });

                // Re-apply badges if platforms were already selected
                document.querySelectorAll('input[name="platform[]"]:checked').forEach(cb => {
                    const platform = cb.value;
                    const links = window.allClientSocialLinks.filter(l => l.platform === platform);
                    if (links.length === 1 && !window.platformAccountMap[platform]) {
                        window.platformAccountMap[platform] = links[0].id;
                        updatePlatformBadge(platform, links[0].id);
                    }
                });
                updateSelectedAccountsSummary();
            })
            .catch(err => console.error('Error loading social links:', err));
    };

    clientIdInput?.addEventListener('change', function() {
        loadClientSocialMediaLinks(this.value);
    });

    window.showAccountSelectionModal = function(platform, clientId) {
        const modal = document.getElementById('accountSelectionModal');
        if (!window.allClientSocialLinks) {
            document.getElementById('modalPlatformName').textContent = 'Loading accounts...';
            document.getElementById('accountSelectOptions').innerHTML = '<div style="text-align:center;padding:20px;color:var(--text3)"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
            modal.style.display = 'flex';

            fetch(`/api/clients/${clientId}/social-links`)
                .then(res => res.ok ? res.json() : Promise.reject())
                .then(data => {
                    window.allClientSocialLinks = data.links || [];
                    displayAccountModal(platform);
                })
                .catch(err => {
                    document.getElementById('accountSelectOptions').innerHTML = '<div style="text-align:center;color:var(--red)">Failed to load accounts.</div>';
                });
        } else {
            displayAccountModal(platform);
        }
    };

    function displayAccountModal(platform) {
        const links = window.allClientSocialLinks.filter(link => link.platform === platform);
        if (links.length === 0) return closeAccountModal();
        if (links.length === 1) {
            window.platformAccountMap[platform] = links[0].id;
            updatePlatformBadge(platform, links[0].id);
            updateSelectedAccountsSummary();
            return closeAccountModal();
        }

        const modal = document.getElementById('accountSelectionModal');
        document.getElementById('modalPlatformName').textContent = platform.charAt(0).toUpperCase() + platform.slice(1) + ' - Select Account';

        const optionsHtml = links.map(link => `
            <label style="display:flex;gap:12px;align-items:flex-start;padding:14px;border:1px solid #e5e7eb;border-radius:12px;cursor:pointer;background:#f9fafb;margin-bottom:10px">
                <input type="radio" name="accountSelect" value="${link.id}" ${window.platformAccountMap[platform] == link.id ? 'checked' : ''} style="margin-top:4px">
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:700;color:#111827">${link.label || 'Account'} ${link.is_primary ? '<span style="color:#f59e0b;font-size:10px">★ PRIMARY</span>' : ''}</div>
                    <div class="account-link-url">${link.url}</div>
                </div>
            </label>
        `).join('');

        document.getElementById('accountSelectOptions').innerHTML = optionsHtml;
        window.modalPlatform = platform;
        modal.style.display = 'flex';
    }

    window.closeAccountModal = function() {
        document.getElementById('accountSelectionModal').style.display = 'none';
        window.modalPlatform = null;
    };

    window.usePrimaryAccount = function() {
        const platform = window.modalPlatform;
        if (!platform || !window.allClientSocialLinks) return closeAccountModal();
        const links = window.allClientSocialLinks.filter(l => l.platform === platform);
        const primaryLink = links.find(l => l.is_primary) || links[0];
        if (primaryLink) {
            window.platformAccountMap[platform] = primaryLink.id;
            updatePlatformBadge(platform, primaryLink.id);
            updateSelectedAccountsSummary();
        }
        closeAccountModal();
    };

    window.applyAccountSelection = function() {
        const selected = document.querySelector('input[name="accountSelect"]:checked');
        if (selected && window.modalPlatform) {
            window.platformAccountMap[window.modalPlatform] = selected.value;
            updatePlatformBadge(window.modalPlatform, selected.value);
            updateSelectedAccountsSummary();
        }
        closeAccountModal();
    };

    function updatePlatformBadge(platform, linkId) {
        const link = window.allClientSocialLinks?.find(l => l.id == linkId);
        if (!link) return;
        const checkbox = document.querySelector(`input[name="platform[]"][value="${platform}"]`);
        const label = checkbox?.closest('.platform-checkbox');
        if (!label) return;
        label.querySelector('.selected-account-badge')?.remove();
        const badge = document.createElement('div');
        badge.className = 'selected-account-badge';
        badge.textContent = link.label || link.url.substring(0, 20);
        label.appendChild(badge);
    }

    function updateSelectedAccountsSummary() {
        const summary = document.getElementById('selectedAccountsSummary');
        const list = document.getElementById('selectedAccountsList');
        if (!window.platformAccountMap || Object.keys(window.platformAccountMap).length === 0) {
            summary.style.display = 'none';
            return;
        }
        let html = '';
        Object.entries(window.platformAccountMap).forEach(([platform, linkId]) => {
            const link = window.allClientSocialLinks?.find(l => l.id == linkId);
            if (link) {
                html += `
                    <div style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:var(--card);border-radius:8px;border:1px solid var(--border)">
                        <span style="font-weight:600;font-size:12px;text-transform:capitalize;color:var(--text2);min-width:70px">${platform}:</span>
                        <span style="font-size:11px;color:var(--text);flex:1">${link.label || link.url}</span>
                        <button type="button" onclick="reSelectAccount('${platform}')" style="padding:4px 10px;font-size:10px;background:var(--primary-dim);color:var(--primary);border:none;border-radius:5px;cursor:pointer">Change</button>
                    </div>
                `;
            }
        });
        list.innerHTML = html;
        summary.style.display = 'block';
    }

    window.reSelectAccount = function(platform) {
        if (clientIdInput?.value) showAccountSelectionModal(platform, clientIdInput.value);
    };

    // --- Platform Checkbox Logic ---
    window.togglePlatformCheckbox = function(checkbox) {
        const label = checkbox.closest('.platform-checkbox');
        const platform = checkbox.value;
        const clientId = clientIdInput?.value;

        if (checkbox.checked) {
            // Check if a client is selected first
            if (!clientId) {
                checkbox.checked = false;
                // Show warning message
                const warningMsg = document.createElement('div');
                warningMsg.style.cssText = 'position:fixed;top:20px;right:20px;background:var(--red-dim);color:var(--red);padding:12px 16px;border-radius:8px;border:1px solid rgba(239,68,68,0.3);font-weight:600;z-index:2000;animation:slideIn 0.3s ease;font-size:13px';
                warningMsg.innerHTML = '<i class="fas fa-info-circle"></i> Please select a client first';
                document.body.appendChild(warningMsg);
                setTimeout(() => warningMsg.remove(), 3000);
                return;
            }
            label.classList.add('platform-checked');
            if (clientId && platform) showAccountSelectionModal(platform, clientId);
        } else {
            label.classList.remove('platform-checked');
            label.querySelector('.selected-account-badge')?.remove();
            delete window.platformAccountMap[platform];
            updateSelectedAccountsSummary();
        }
        window.validatePlatforms();
    };

    window.validatePlatforms = function() {

    const errorEl = document.getElementById('platformError');
    const typeSelect = document.querySelector('select[name="type"]');

    if (!typeSelect) return;

    const typeVal = typeSelect.value.trim().toLowerCase();

    /* website/software don't need platforms */
    if (typeVal === 'website' || typeVal === 'software') {

        if (errorEl) errorEl.style.display = 'none';
        return true;
    }

    const checked = document.querySelectorAll('input[name="platform[]"]:checked');

    if (errorEl) {
        errorEl.style.display = checked.length === 0 ? 'block' : 'none';
    }

    if (typeof updateFieldValidationState === 'function') {
        updateFieldValidationState('platform');
    }

    return checked.length > 0;
};

    const customPlatformVisuals = {
        instagram: { iconClass: 'fa-brands fa-instagram', color: '#E1306C' },
        facebook: { iconClass: 'fa-brands fa-facebook-f', color: '#1877F2' },
        linkedin: { iconClass: 'fa-brands fa-linkedin-in', color: '#0077B5' },
        twitter: { iconClass: 'fa-brands fa-x-twitter', color: '#111827' },
        youtube: { iconClass: 'fa-brands fa-youtube', color: '#FF0000' },
        whatsapp: { iconClass: 'fa-brands fa-whatsapp', color: '#25D366' },
        pinterest: { iconClass: 'fa-brands fa-pinterest-p', color: '#E60023' },
        snapchat: { iconClass: 'fa-brands fa-snapchat', color: '#EAB308' },
        tiktok: { iconClass: 'fa-brands fa-tiktok', color: '#111827' },
        telegram: { iconClass: 'fa-brands fa-telegram', color: '#229ED9' },
        reddit: { iconClass: 'fa-brands fa-reddit-alien', color: '#FF4500' },
        website: { iconClass: 'fa-solid fa-globe', color: '#2563EB' },
    };

    function normalizePlatformKey(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '');
    }

    function resolveCustomPlatformVisual(name) {
        const normalized = normalizePlatformKey(name);
        if (!normalized) {
            return { iconClass: 'fa-solid fa-hashtag', color: '#6B7280' };
        }

        const aliases = {
            pinterst: 'pinterest',
            pinterest: 'pinterest',
            x: 'twitter',
            twitterx: 'twitter',
            yt: 'youtube',
        };

        const direct = aliases[normalized] || normalized;
        if (customPlatformVisuals[direct]) {
            return customPlatformVisuals[direct];
        }

        if (normalized.includes('pinter')) return customPlatformVisuals.pinterest;
        if (normalized.includes('insta')) return customPlatformVisuals.instagram;
        if (normalized.includes('face')) return customPlatformVisuals.facebook;
        if (normalized.includes('linked')) return customPlatformVisuals.linkedin;
        if (normalized.includes('tweet') || normalized.includes('twitter')) return customPlatformVisuals.twitter;
        if (normalized.includes('tube') || normalized.includes('yt')) return customPlatformVisuals.youtube;
        if (normalized.includes('snap')) return customPlatformVisuals.snapchat;
        if (normalized.includes('tik')) return customPlatformVisuals.tiktok;
        if (normalized.includes('tele')) return customPlatformVisuals.telegram;
        if (normalized.includes('reddit')) return customPlatformVisuals.reddit;

        return { iconClass: 'fa-solid fa-hashtag', color: '#6B7280' };
    }

    function applyCustomPlatformVisual(row, name) {
        if (!row) return;

        const visual = resolveCustomPlatformVisual(name);
        const label = row.querySelector('.platform-checkbox');
        const iconEl = row.querySelector('.plat-icon-wrap i');

        if (label) {
            label.style.setProperty('--plat-color', visual.color);
        }

        if (iconEl) {
            iconEl.className = visual.iconClass;
        }
    }

    // --- Custom Platform Logic ---
    const addPlatBtn = document.getElementById('addPlatformBtn');
    const customArea = document.getElementById('customPlatformsArea');

    addPlatBtn?.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'custom-plat-row';
        row.innerHTML = `
            <label class="platform-checkbox platform-checked" style="--plat-color:#6B7280;flex:1;min-height:0;flex-direction:row;padding:10px 14px;justify-content:flex-start;gap:12px;border-radius:10px">
                <input type="checkbox" name="platform[]" value="" checked onchange="togglePlatformCheckbox(this)">
                <span class="plat-check-indicator" style="position:static;margin-right:auto;transform:scale(1);opacity:1"><i class="fa-solid fa-check"></i></span>
                <span class="plat-icon-wrap" style="width:30px;height:30px;font-size:14px;border-radius:8px"><i class="fa-solid fa-hashtag"></i></span>
                <input type="text" class="custom-plat-name" placeholder="Type name..." style="border:none;background:transparent;font-size:13px;font-weight:600;color:var(--text);outline:none;flex:1">
            </label>
            <button type="button" class="btn-sec" onclick="this.closest('.custom-plat-row').remove();validatePlatforms()" style="color:var(--red)"><i class="fa-solid fa-xmark"></i></button>
        `;
        customArea.appendChild(row);
        const nameInput = row.querySelector('.custom-plat-name');
        const checkbox = row.querySelector('input[name="platform[]"]');
        applyCustomPlatformVisual(row, '');
        nameInput.focus();
        nameInput.addEventListener('input', function() {
            const platformName = this.value.trim();
            checkbox.value = platformName.toLowerCase().replace(/[^a-z0-9]/g, '_');
            applyCustomPlatformVisual(row, platformName);
        });
        window.validatePlatforms();
    });

    // --- Helper for x-client-select compatibility ---
    window.selectClient = function(element) {
        const uid = element.closest('.custom-client-select').id.split('_')[0];
        const val = element.getAttribute('data-value');
        const name = element.querySelector('.ccs-opt-name').textContent;
        const logo = element.querySelector('.ccs-logo')?.src || '';
        const emoji = element.querySelector('.ccs-emoji')?.textContent || '';
        window.selectCcsOption(uid, val, logo, emoji, name, '', element);
        setTimeout(() => updateFieldValidationState('client_id'), 0);
    };

    // --- Stacked Task Forms ---
    const batchTaskList = document.getElementById('batchTaskList');
    const addBatchTaskBtn = document.getElementById('addBatchTaskBtn');
    const previousTaskBtn = document.getElementById('previousTaskBtn');
    const taskFormNav = document.getElementById('taskFormNav');
    const activeTaskNumber = document.getElementById('activeTaskNumber');
    const formPanel = document.getElementById('ctFormPanel');
    const draftSaveStatus = document.getElementById('draftSaveStatus');
    const batchTaskError = document.getElementById('batchTaskError');
    const createTaskSubmitLabel = document.getElementById('createTaskSubmitLabel');
    const openTaskSubmitChoice = document.getElementById('openTaskSubmitChoice');
    const taskSubmitChoice = document.getElementById('taskSubmitChoice');
    const cancelTaskSubmitChoice = document.getElementById('cancelTaskSubmitChoice');
    const taskSubmitChoiceSummary = document.getElementById('taskSubmitChoiceSummary');
    const createCurrentTaskLabel = document.getElementById('createCurrentTaskLabel');
    const createAllTasksLabel = document.getElementById('createAllTasksLabel');
    const taskTitleInput = form?.querySelector('[name="title"]');
    const restoredAdditionalTitles = @json(array_values(array_filter((array) old('additional_titles', []))));
    const taskDraftTitles = [taskTitleInput?.value || '', ...restoredAdditionalTitles];
    let activeTaskIndex = 0;

    function saveActiveTaskTitle() {
        if (taskTitleInput) taskDraftTitles[activeTaskIndex] = taskTitleInput.value;
    }

    function renderTaskNavigator() {
        if (taskFormNav) {
            taskFormNav.innerHTML = taskDraftTitles.map((title, index) => `
                <div class="ct-task-nav-item">
                    <button type="button" class="ct-task-nav-btn ${index === activeTaskIndex ? 'active' : ''}" data-task-index="${index}">
                        <span class="ct-nav-index">${String(index + 1).padStart(2, '0')}</span>
                        <span>Task ${String(index + 1).padStart(2, '0')}</span>
                    </button>
                    ${taskDraftTitles.length > 1 ? `<button type="button" class="ct-task-delete" data-delete-task-index="${index}" title="Delete Task ${index + 1}" aria-label="Delete Task ${index + 1}"><i class="fa-solid fa-trash-can"></i></button>` : ''}
                </div>
            `).join('');
        }

        const total = taskDraftTitles.length;
        if (activeTaskNumber) activeTaskNumber.textContent = `Task ${String(activeTaskIndex + 1).padStart(2, '0')} of ${String(total).padStart(2, '0')}`;
        if (createTaskSubmitLabel) {
            createTaskSubmitLabel.textContent = total === 1 ? 'Create Task' : `Create Tasks (${total})`;
        }
        if (createCurrentTaskLabel) createCurrentTaskLabel.textContent = `Create Task ${activeTaskIndex + 1} Only`;
        if (createAllTasksLabel) createAllTasksLabel.textContent = total === 1 ? 'Create This Task' : `Create All ${total} Tasks`;
        if (taskSubmitChoiceSummary) {
            taskSubmitChoiceSummary.textContent = total === 1
                ? 'Create the task currently open.'
                : `You are viewing Task ${activeTaskIndex + 1} of ${total}. Create only this task or create all ${total} tasks.`;
        }
        if (addBatchTaskBtn) addBatchTaskBtn.disabled = total >= 20;
        if (previousTaskBtn) previousTaskBtn.disabled = activeTaskIndex === 0;
    }

    function switchTaskForm(index, direction = 'forward') {
        if (index < 0 || index >= taskDraftTitles.length || index === activeTaskIndex) return;
        saveActiveTaskTitle();
        activeTaskIndex = index;
        if (taskTitleInput) taskTitleInput.value = taskDraftTitles[index] || '';
        clearInlineValidationErrors();
        renderTaskNavigator();

        if (formPanel) {
            formPanel.classList.remove('ct-form-enter', 'ct-form-back');
            void formPanel.offsetWidth;
            formPanel.classList.add(direction === 'back' ? 'ct-form-back' : 'ct-form-enter');
        }
        taskTitleInput?.focus();
        scheduleDraftSave();
    }

    addBatchTaskBtn?.addEventListener('click', function () {
        saveActiveTaskTitle();
        if (taskDraftTitles.length >= 20) return;
        taskDraftTitles.push('');
        switchTaskForm(taskDraftTitles.length - 1, 'forward');
    });

    previousTaskBtn?.addEventListener('click', () => switchTaskForm(activeTaskIndex - 1, 'back'));
    taskFormNav?.addEventListener('click', function (event) {
        const deleteButton = event.target.closest('[data-delete-task-index]');
        if (deleteButton) {
            saveActiveTaskTitle();
            const deleteIndex = Number(deleteButton.dataset.deleteTaskIndex);
            taskDraftTitles.splice(deleteIndex, 1);
            if (deleteIndex < activeTaskIndex) activeTaskIndex--;
            if (activeTaskIndex >= taskDraftTitles.length) activeTaskIndex = taskDraftTitles.length - 1;
            if (taskTitleInput) taskTitleInput.value = taskDraftTitles[activeTaskIndex] || '';
            renderTaskNavigator();
            if (formPanel) {
                formPanel.classList.remove('ct-form-enter', 'ct-form-back');
                void formPanel.offsetWidth;
                formPanel.classList.add('ct-form-back');
            }
            scheduleDraftSave();
            return;
        }

        const button = event.target.closest('[data-task-index]');
        if (!button) return;
        const nextIndex = Number(button.dataset.taskIndex);
        switchTaskForm(nextIndex, nextIndex < activeTaskIndex ? 'back' : 'forward');
    });
    taskTitleInput?.addEventListener('input', function () {
        taskDraftTitles[activeTaskIndex] = this.value;
        this.classList.remove('input-invalid');
        scheduleDraftSave();
    });

    // --- Automatic Draft Save ---
    const DRAFT_STORAGE_KEY = 'strategist_create_task_draft_v2_{{ auth()->id() }}';
    let draftSaveTimer = null;
    let draftSubmissionComplete = false;

    function collectDraftFields() {
        const values = {};
        form?.querySelectorAll('[name]').forEach(field => {
            if (field.name === '_token' || field.name === 'additional_titles[]' || field.dataset.autoHidden) return;
            if (field.tagName === 'BUTTON' || field.type === 'submit' || field.type === 'button') return;
            if (field.type === 'radio') {
                if (field.checked) values[field.name] = field.value;
                return;
            }
            if (field.type === 'checkbox') {
                if (!Array.isArray(values[field.name])) values[field.name] = [];
                if (field.checked) values[field.name].push(field.value);
                return;
            }
            if (field.name.endsWith('[]')) {
                if (!Array.isArray(values[field.name])) values[field.name] = [];
                values[field.name].push(field.value);
                return;
            }
            values[field.name] = field.value;
        });
        return values;
    }

    function saveDraftWorkspace() {
        if (draftSubmissionComplete) return;
        saveActiveTaskTitle();
        const payload = {
            version: 2,
            savedAt: Date.now(),
            activeTaskIndex,
            taskTitles: taskDraftTitles,
            fields: collectDraftFields(),
            platformAccountMap: window.platformAccountMap || {},
        };
        try {
            localStorage.setItem(DRAFT_STORAGE_KEY, JSON.stringify(payload));
            if (draftSaveStatus) draftSaveStatus.textContent = 'Draft saved';
        } catch (_) {
            if (draftSaveStatus) draftSaveStatus.textContent = 'Draft unavailable';
        }
    }

    function scheduleDraftSave() {
        clearTimeout(draftSaveTimer);
        if (draftSaveStatus) draftSaveStatus.textContent = 'Saving…';
        draftSaveTimer = setTimeout(saveDraftWorkspace, 350);
    }

    function restoreDraftWorkspace() {
        let draft;
        try {
            draft = JSON.parse(localStorage.getItem(DRAFT_STORAGE_KEY) || 'null');
        } catch (_) {
            return;
        }
        if (!draft || draft.version !== 2 || !Array.isArray(draft.taskTitles)) return;

        taskDraftTitles.splice(0, taskDraftTitles.length, ...draft.taskTitles.slice(0, 20));
        if (!taskDraftTitles.length) taskDraftTitles.push('');
        activeTaskIndex = Math.min(Math.max(Number(draft.activeTaskIndex) || 0, 0), taskDraftTitles.length - 1);

        const fields = draft.fields || {};
        form?.querySelectorAll('input[type="checkbox"],input[type="radio"]').forEach(field => { field.checked = false; });
        Object.entries(fields).forEach(([name, value]) => {
            const matchingFields = Array.from(form?.querySelectorAll(`[name="${CSS.escape(name)}"]`) || []);
            if (!matchingFields.length) return;
            matchingFields.forEach((field, index) => {
                if (field.type === 'checkbox') field.checked = Array.isArray(value) && value.includes(field.value);
                else if (field.type === 'radio') field.checked = String(field.value) === String(value);
                else if (Array.isArray(value)) field.value = value[index] ?? '';
                else field.value = value ?? '';
            });
        });

        window.platformAccountMap = draft.platformAccountMap || {};
        if (taskTitleInput) taskTitleInput.value = taskDraftTitles[activeTaskIndex] || '';

        const clientValue = fields.client_id;
        const clientWrapper = clientIdInput?.closest('.custom-client-select');
        @php
            $draftClientLookup = $clients->mapWithKeys(function ($client) {
                return [(string) $client->id => [
                    'name' => $client->name,
                    'logo' => $client->logo ? asset('storage/' . $client->logo) : '',
                    'emoji' => $client->emoji,
                ]];
            });
        @endphp
        const client = {{ Illuminate\Support\Js::from($draftClientLookup) }};
        if (clientValue && clientWrapper && client[String(clientValue)] && typeof window.selectCcsOption === 'function') {
            const uid = clientWrapper.id.replace(/_wrapper$/, '');
            const selectedClient = client[String(clientValue)];
            window.selectCcsOption(uid, String(clientValue), selectedClient.logo, selectedClient.emoji, selectedClient.name, '', null);
        }

        typeSelect?.dispatchEvent(new Event('change', { bubbles:true }));
        form?.querySelectorAll('input[name="platform[]"]').forEach(field => {
            if (field.checked && typeof window.togglePlatformCheckbox === 'function') window.togglePlatformCheckbox(field);
        });
        renderTaskNavigator();
        if (draftSaveStatus) draftSaveStatus.textContent = 'Draft restored';
    }

    form?.addEventListener('input', scheduleDraftSave);
    form?.addEventListener('change', scheduleDraftSave);
    window.addEventListener('pagehide', saveDraftWorkspace);
    restoreDraftWorkspace();
    renderTaskNavigator();

    function closeTaskSubmitChoice() {
        taskSubmitChoice?.classList.remove('show');
        taskSubmitChoice?.setAttribute('aria-hidden', 'true');
    }

    openTaskSubmitChoice?.addEventListener('click', function () {
        saveActiveTaskTitle();
        taskSubmitChoice?.classList.add('show');
        taskSubmitChoice?.setAttribute('aria-hidden', 'false');
        taskSubmitChoice?.querySelector('button[name="submit_scope"]')?.focus();
    });
    cancelTaskSubmitChoice?.addEventListener('click', closeTaskSubmitChoice);
    taskSubmitChoice?.addEventListener('click', function (event) {
        if (event.target === taskSubmitChoice) closeTaskSubmitChoice();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && taskSubmitChoice?.classList.contains('show')) closeTaskSubmitChoice();
    });

    // --- Form Submission ---
    form?.addEventListener('submit', function(e) {
        e.preventDefault();
        closeTaskSubmitChoice();
        saveActiveTaskTitle();
        const submitAllTasks = (e.submitter?.value || 'all') === 'all';
        form.dataset.submitScope = submitAllTasks ? 'all' : 'current';
        form.dataset.submittedTaskIndex = String(activeTaskIndex);
        const submissionTitles = submitAllTasks
            ? [taskDraftTitles[activeTaskIndex], ...taskDraftTitles.filter((_, index) => index !== activeTaskIndex)]
            : [taskDraftTitles[activeTaskIndex]];
        if (batchTaskList) {
            batchTaskList.innerHTML = submissionTitles.slice(1).map(titleValue => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'additional_titles[]';
                input.value = titleValue;
                return input.outerHTML;
            }).join('');
        }
        clearInlineValidationErrors();
        let hasError = false;
        const localErrors = {};

        const title = form.querySelector('[name="title"]');
        if (!title.value.trim()) {
            localErrors.title = ['Task title is required.'];
            hasError = true;
        }

        const additionalTitleInputs = Array.from(form.querySelectorAll('input[name="additional_titles[]"]'));
        const batchTitles = additionalTitleInputs.map(input => input.value.trim());
        const normalizedTitles = [title.value.trim(), ...batchTitles].map(value => value.toLowerCase());
        const hasBlankBatchTitle = batchTitles.some(value => !value);
        const hasDuplicateTitle = normalizedTitles.some((value, index) => value && normalizedTitles.indexOf(value) !== index);

        additionalTitleInputs.forEach(input => input.classList.remove('input-invalid'));
        if (batchTaskError) batchTaskError.style.display = 'none';

        if (hasBlankBatchTitle || hasDuplicateTitle) {
            additionalTitleInputs.forEach(input => {
                const value = input.value.trim();
                const normalized = value.toLowerCase();
                if (!value || normalizedTitles.filter(titleValue => titleValue === normalized).length > 1) {
                    input.classList.add('input-invalid');
                }
            });
            if (batchTaskError) {
                batchTaskError.textContent = hasBlankBatchTitle
                    ? 'Enter a title for every added task or remove the empty row.'
                    : 'Each task title must be unique.';
                batchTaskError.style.display = 'block';
            }
            hasError = true;
        }

        if (!clientIdInput?.value) {
            localErrors.client_id = ['Please select a client.'];
            hasError = true;
        }

        if (!typeSelect?.value) {
            localErrors.type = ['Please choose a content type.'];
            hasError = true;
        }

        // For non-dev tasks, validate platforms
        const typeVal = typeSelect?.value || '';
        if (!['website', 'software'].includes(typeVal)) {
            if (!briefInput?.value.trim()) {
                localErrors.brief = ['Content brief is required.'];
                hasError = true;
            }

            if (!window.validatePlatforms()) {
                localErrors.platform = ['Please select at least one platform.'];
                hasError = true;
            }

            const postDate = form.querySelector('[name="post_date"]');
            if (!postDate?.value) {
                localErrors.post_date = ['Post date is required.'];
                hasError = true;
            } else {
                const selectedDate = new Date(postDate.value + 'T00:00:00');
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (selectedDate < today) {
                    localErrors.post_date = ['Post date must be today or a future date.'];
                    hasError = true;
                }
            }
        }

        if (hasError) {
            renderValidationErrors(localErrors);
            if (typeof ajax !== 'undefined') ajax.showError('Please fill all required fields.');
            return;
        }

        // Add priority if it's hidden (for dev projects)
        const priorityField = form.querySelector('input[name="priority"]');
        if (!priorityField && !form.querySelector('select[name="priority"]')) {
            const hiddenPriority = document.createElement('input');
            hiddenPriority.type = 'hidden';
            hiddenPriority.name = 'priority';
            hiddenPriority.value = 'normal';
            form.appendChild(hiddenPriority);
        }

        // Add status if it's hidden (for dev projects)
        const statusField = form.querySelector('select[name="status"]');
        if (!statusField) {
            const hiddenStatus = document.createElement('input');
            hiddenStatus.type = 'hidden';
            hiddenStatus.name = 'status';
            hiddenStatus.value = 'todo';
            form.appendChild(hiddenStatus);
        }

        // Add hidden fields for social media links
        form.querySelectorAll('input[data-auto-hidden]').forEach(el => el.remove());

        if (window.platformAccountMap) {
            Object.entries(window.platformAccountMap).forEach(([platform, linkId]) => {
                let hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = `social_media_link_id_${platform}`;
                hidden.value = linkId;
                hidden.setAttribute('data-auto-hidden', '1');
                form.appendChild(hidden);
            });

            // Persist a single selected account on the task model.
            const selectedPlatforms = Array.from(form.querySelectorAll('input[name="platform[]"]:checked')).map(el => el.value);
            let selectedLinkId = null;

            for (const platform of selectedPlatforms) {
                if (window.platformAccountMap[platform]) {
                    selectedLinkId = window.platformAccountMap[platform];
                    break;
                }
            }

            if (!selectedLinkId) {
                selectedLinkId = Object.values(window.platformAccountMap).find(Boolean) || null;
            }

            const selectedLinkIds = [...new Set(
                selectedPlatforms
                    .map(platform => window.platformAccountMap[platform] || null)
                    .filter(Boolean)
            )];

            selectedLinkIds.forEach((linkId) => {
                const selectedLinksHidden = document.createElement('input');
                selectedLinksHidden.type = 'hidden';
                selectedLinksHidden.name = 'selected_social_media_link_ids[]';
                selectedLinksHidden.value = linkId;
                selectedLinksHidden.setAttribute('data-auto-hidden', '1');
                form.appendChild(selectedLinksHidden);
            });

            if (selectedLinkId) {
                const selectedAccountHidden = document.createElement('input');
                selectedAccountHidden.type = 'hidden';
                selectedAccountHidden.name = 'client_social_media_link_id';
                selectedAccountHidden.value = selectedLinkId;
                selectedAccountHidden.setAttribute('data-auto-hidden', '1');
                form.appendChild(selectedAccountHidden);
            }
        }

        submitCreateTaskAJAX(this);
    });

async function submitCreateTaskAJAX(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn ? submitBtn.innerHTML : '';

    try {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            throw new Error('CSRF token not found. Please refresh the page.');
        }

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        let data = {};
        const contentType = response.headers.get('content-type');

        if (contentType?.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error(`Server error (${response.status}): ${response.statusText}`);
            }
        }

        if (!response.ok) {
            if (response.status === 422 && data.errors) {
                renderValidationErrors(data.errors);
                if (typeof ajax !== 'undefined') ajax.showError('Please fix the highlighted fields.');
                return;
            }
            throw new Error(data.message || `Failed to create task (${response.status})`);
        }

        if (typeof ajax !== 'undefined') ajax.showSuccess(data.message || 'Task created successfully!');
        if (form.dataset.submitScope === 'all') {
            draftSubmissionComplete = true;
            localStorage.removeItem(DRAFT_STORAGE_KEY);
        } else {
            const submittedIndex = Number(form.dataset.submittedTaskIndex) || 0;
            taskDraftTitles.splice(submittedIndex, 1);
            if (!taskDraftTitles.length) {
                localStorage.removeItem(DRAFT_STORAGE_KEY);
            } else {
                activeTaskIndex = Math.min(submittedIndex, taskDraftTitles.length - 1);
                if (taskTitleInput) taskTitleInput.value = taskDraftTitles[activeTaskIndex] || '';
                saveDraftWorkspace();
            }
        }
        setTimeout(() => {
            window.location.href = data.task_id
                ? `/strategist/tracking?task=${encodeURIComponent(data.task_id)}`
                : '/strategist/tracking';
        }, 800);

    } catch (error) {
        console.error('Create task error:', error);
        if (typeof ajax !== 'undefined') ajax.showError(error.message || 'Failed to create task. Please try again.');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
}

    if (clientIdInput?.value) {
        loadClientSocialMediaLinks(clientIdInput.value);
    }
});


</script>

@endsection
