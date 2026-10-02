@extends('layouts.app')

@push('styles')
<style>
    /* Google Calendar Integration */
    .gc-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .gc-calendar-section {
        display: flex;
        flex-direction: column;
    }

    .gc-selections-section {
        display: flex;
        flex-direction: column;
    }

    /* Header */
    .gc-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .gc-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .gc-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #F59E0B, #D97706);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(245, 158, 11, .25);
    }

    .gc-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -.3px;
    }

    .gc-subtitle {
        font-size: 12px;
        color: var(--text3);
        margin-top: 2px;
        font-weight: 500;
    }

    .gc-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 700;
        background: var(--primary-dim);
        color: var(--primary);
    }

    .gc-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        background: var(--card2);
        color: var(--text2);
    }

    .gc-status-connected {
        background: rgba(16, 185, 129, .1);
        color: #059669;
    }

    .gc-status-disconnected {
        background: rgba(239, 68, 68, .1);
        color: #dc2626;
    }

    /* Google Calendar Embed */
    .gc-embed-container {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 16px;
        height: 600px;
        overflow: hidden;
    }

    .gc-embed-container iframe {
        width: 100%;
        height: 100%;
        border: none;
        border-radius: var(--radius);
    }

    .gc-embed-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        background: var(--bg);
        border-radius: var(--radius);
        color: var(--text3);
    }

    .gc-embed-placeholder i {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.3;
    }

    /* Connect Button */
    .gc-connect-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        background: #4285F4;
        color: #fff;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .gc-connect-btn:hover {
        background: #357ae8;
        box-shadow: 0 4px 12px rgba(66, 133, 244, .2);
    }

    .gc-disconnect-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: var(--card2);
        color: var(--text2);
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .gc-disconnect-btn:hover {
        background: rgba(239, 68, 68, .1);
        color: #dc2626;
    }

    /* Filter */
    .gc-filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .gc-filter-bar i.fa-filter {
        color: var(--text3);
        font-size: 13px;
    }

    .gc-filter-bar select {
        height: 36px;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 0 12px;
        font-size: 12.5px;
        background: var(--bg);
        color: var(--text);
        outline: none;
        font-weight: 500;
        transition: border-color .2s;
    }

    .gc-filter-bar select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 109, 240, .08);
    }

    /* Month Groups */
    .gc-month-group {
        margin-bottom: 20px;
    }

    .gc-month-label {
        font-size: 13px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--border);
    }

    .gc-month-label i {
        color: var(--primary);
        font-size: 12px;
    }

    /* Festival List */
    .gc-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .gc-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all .25s;
        position: relative;
    }

    .gc-card:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
    }

    .gc-card-selected {
        border-color: var(--primary);
        background: var(--primary-dim);
    }

    .gc-card-emoji {
        font-size: 24px;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--card2);
        border-radius: 10px;
        flex-shrink: 0;
    }

    .gc-card-selected .gc-card-emoji {
        background: rgba(79, 109, 240, .12);
    }

    .gc-card-body {
        flex: 1;
        min-width: 0;
    }

    .gc-card-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 2px;
    }

    .gc-card-info {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        font-size: 11px;
        color: var(--text3);
    }

    .gc-card-date {
        display: inline-flex;
        align-items: center;
        gap: 2px;
    }

    .gc-card-days {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 6px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .gc-card-days-upcoming {
        background: var(--primary-dim);
        color: var(--primary);
    }

    .gc-card-days-soon {
        background: rgba(245, 158, 11, .1);
        color: #D97706;
    }

    .gc-card-days-today {
        background: rgba(16, 185, 129, .1);
        color: #059669;
    }

    .gc-card-days-past {
        background: var(--card2);
        color: var(--text3);
    }

    .gc-card-btn {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid var(--border);
        background: var(--card);
        color: var(--text2);
        cursor: pointer;
        transition: all .2s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
    }

    .gc-card-btn:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: var(--primary-dim);
    }

    .gc-card-btn-selected {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }

    .gc-card-btn-selected:hover {
        background: var(--primary-hover);
    }

    /* Empty State */
    .gc-empty {
        text-align: center;
        padding: 60px 20px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
    }

    .gc-empty-icon {
        font-size: 48px;
        margin-bottom: 12px;
        opacity: 0.3;
    }

    .gc-empty-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
    }

    .gc-empty-text {
        font-size: 12px;
        color: var(--text3);
    }

    /* Modal */
    .gc-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .5);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .gc-modal-overlay.active {
        display: flex;
    }

    .gc-modal {
        background: var(--card);
        border-radius: var(--radius);
        max-width: 450px;
        width: 100%;
        padding: 28px;
        position: relative;
        box-shadow: 0 25px 50px rgba(0, 0, 0, .15);
    }

    .gc-modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        background: var(--card2);
        color: var(--text3);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all .2s;
    }

    .gc-modal-close:hover {
        background: #fef2f2;
        color: #ef4444;
    }

    .gc-modal-title {
        font-size: 17px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 6px;
        padding-right: 40px;
    }

    .gc-modal-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 8px;
        display: block;
        margin-top: 14px;
    }

    .gc-type-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 16px;
    }

    .gc-type-option {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 8px 10px;
        border: 1px solid var(--border);
        border-radius: 6px;
        cursor: pointer;
        transition: all .2s;
        font-size: 11px;
        font-weight: 600;
        color: var(--text2);
    }

    .gc-type-option:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .gc-type-option input {
        display: none;
    }

    .gc-type-option.checked {
        border-color: var(--primary);
        background: var(--primary-dim);
        color: var(--primary);
    }

    .gc-modal-notes {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 8px 10px;
        font-size: 12px;
        background: var(--bg);
        color: var(--text);
        resize: vertical;
        min-height: 50px;
        outline: none;
        font-family: inherit;
        transition: border-color .2s;
    }

    .gc-modal-notes:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 109, 240, .08);
    }

    .gc-sync-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding: 10px 12px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 12px;
    }

    .gc-sync-toggle input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .gc-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 18px;
    }

    .gc-modal-btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all .2s;
    }

    .gc-modal-btn-cancel {
        background: var(--card2);
        color: var(--text2);
    }

    .gc-modal-btn-cancel:hover {
        background: var(--border);
    }

    .gc-modal-btn-save {
        background: var(--primary);
        color: #fff;
    }

    .gc-modal-btn-save:hover {
        background: var(--primary-hover);
    }

    @media (max-width: 1024px) {
        .gc-container {
            grid-template-columns: 1fr;
        }

        .gc-embed-container {
            height: 500px;
        }
    }

    @media (max-width: 768px) {
        .gc-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .gc-type-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }






    /* =====================================================
   CALENDAR DAY CLICK FOCUS VIEW
   Click specific date => centered card popup
   Background blur + close button + active box
===================================================== */

/* CLICKABLE DAY BOX */
.fest-day{
    cursor:pointer;
    position:relative;
    transition:.32s ease;
}

.fest-day:hover{
    transform:translateY(-6px) scale(1.02);
}

/* ACTIVE CLICKED DAY */
.fest-day.active-day{
    transform:scale(1.03);
    z-index:15;
    border:2px solid #ff5a36 !important;
    box-shadow:
        0 24px 45px rgba(255,90,54,.22),
        0 10px 18px rgba(0,0,0,.08);
}

/* WHOLE PAGE BLUR */
.calendar-focus-overlay{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.38);
    backdrop-filter:blur(10px);
    z-index:9997;
    animation:fadeFocus .28s ease;
}

/* CENTER DETAIL CARD */
.calendar-focus-card{
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    width:92%;
    max-width:560px;
    max-height:88vh;
    overflow:auto;
    z-index:9998;

    background:linear-gradient(180deg,#ffffff,#fff8f4);
    border:1px solid #ffe0d2;
    border-radius:28px;
    padding:26px;
    box-shadow:
        0 45px 90px rgba(0,0,0,.20),
        0 16px 30px rgba(255,90,54,.16);

    animation:popCard .35s cubic-bezier(.2,.8,.2,1);
}

/* HEADER */
.calendar-focus-top{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:18px;
}

.calendar-focus-date{
    display:flex;
    align-items:center;
    gap:14px;
}

.calendar-focus-badge{
    width:62px;
    height:62px;
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#ff5a36,#ff7b42);
    color:#fff;
    font-size:24px;
    box-shadow:0 16px 28px rgba(255,90,54,.20);
}

.calendar-focus-title{
    font-size:28px;
    font-weight:900;
    color:#111827;
    line-height:1;
}

.calendar-focus-sub{
    margin-top:6px;
    color:#6b7280;
    font-size:14px;
    font-weight:700;
}

/* CLOSE BTN */
.calendar-focus-close{
    width:44px;
    height:44px;
    border:none;
    border-radius:14px;
    background:#fff1ea;
    color:#ff5a36;
    font-size:18px;
    cursor:pointer;
    transition:.25s ease;
}

.calendar-focus-close:hover{
    background:#ff5a36;
    color:#fff;
    transform:rotate(90deg);
}

/* EVENT LIST */
.calendar-focus-events{
    margin-top:8px;
    display:flex;
    flex-direction:column;
    gap:14px;
}

.calendar-focus-item{
    padding:16px;
    border-radius:18px;
    background:#fff;
    border:1px solid #ffe5d9;
    transition:.25s ease;
}

.calendar-focus-item:hover{
    transform:translateY(-3px);
    box-shadow:0 16px 26px rgba(255,90,54,.08);
}

.calendar-focus-item-name{
    font-size:17px;
    font-weight:900;
    color:#111827;
}

.calendar-focus-item-meta{
    margin-top:8px;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
}

.calendar-focus-pill{
    padding:7px 10px;
    border-radius:30px;
    background:#fff2ec;
    color:#ff5a36;
    font-size:12px;
    font-weight:800;
}

.calendar-focus-desc{
    margin-top:10px;
    color:#6b7280;
    line-height:1.6;
    font-size:14px;
}

/* EMPTY */
.calendar-focus-empty{
    padding:26px;
    text-align:center;
    border-radius:20px;
    background:#fff;
    border:1px dashed #ffd2c4;
    color:#9ca3af;
    font-weight:700;
}

/* ANIMATIONS */
@keyframes fadeFocus{
    from{opacity:0}
    to{opacity:1}
}

@keyframes popCard{
    from{
        opacity:0;
        transform:translate(-50%,-45%) scale(.92);
    }
    to{
        opacity:1;
        transform:translate(-50%,-50%) scale(1);
    }
}

/* BODY LOCK */
body.calendar-lock{
    overflow:hidden;
}

/* MOBILE */
@media(max-width:768px){

    .calendar-focus-card{
        width:95%;
        padding:18px;
        border-radius:22px;
    }

    .calendar-focus-title{
        font-size:22px;
    }

    .calendar-focus-badge{
        width:52px;
        height:52px;
        font-size:20px;
    }
}
</style>
@endpush

@section('content')

{{-- Header --}}
<div class="gc-header">
    <div class="gc-header-left">
        <div class="gc-icon-wrap">
            <i class="fa-brands fa-google"></i>
        </div>
        <div>
            <div class="gc-title">Festival Calendar</div>
            <div class="gc-subtitle">Select festivals and sync with Google Calendar</div>
        </div>
    </div>
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <div class="gc-badge">
            <i class="fa-solid fa-check-circle"></i>
            <span id="gcSelectedCount">{{ $selectedCount }}</span> Selected
        </div>
        @if(auth()->user()->google_calendar_token)
            <div class="gc-status-badge gc-status-connected">
                <i class="fa-solid fa-check"></i> Connected to Google
            </div>
            <button class="gc-disconnect-btn" onclick="disconnectGoogleCalendar()">
                <i class="fa-solid fa-link-slash"></i> Disconnect
            </button>
        @else
            <button class="gc-connect-btn" onclick="connectGoogleCalendar()">
                <i class="fa-brands fa-google"></i> Connect to Google Calendar
            </button>
        @endif
        <a href="{{ route('client.dashboard') }}" style="padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border); background: var(--card); color: var(--text2); text-decoration: none; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
            <i class="fa-solid fa-arrow-left"></i> Dashboard
        </a>
    </div>
</div>

{{-- Main Container --}}
<div class="gc-container">
    {{-- Google Calendar Section --}}
    <div class="gc-calendar-section">
        <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; color: var(--text);">
            <i class="fa-solid fa-calendar-days" style="margin-right: 6px; color: var(--primary);"></i>
            Google Calendar
        </h3>
        <div class="gc-embed-container">
            @if(auth()->user()->google_calendar_token)
                <iframe src="https://calendar.google.com/calendar/embed?src={{ urlencode(auth()->user()->email) }}&ctz={{ urlencode(config('app.timezone', 'UTC')) }}" frameborder="0"></iframe>
            @else
                <div class="gc-embed-placeholder">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <div style="text-align: center;">
                        <div style="font-weight: 600; font-size: 13px; margin-bottom: 4px;">Not Connected</div>
                        <div style="font-size: 12px;">Connect your Google Calendar to see and sync events</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Festival Selections Section --}}
    <div class="gc-selections-section">
        <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; color: var(--text);">
            <i class="fa-solid fa-star" style="margin-right: 6px; color: #F59E0B;"></i>
            Festival Selections
        </h3>

        {{-- Filter --}}
        <div class="gc-filter-bar">
            <i class="fa-solid fa-filter"></i>
            <select id="gcCategoryFilter">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
            <select id="gcMonthFilter">
                <option value="">All Months</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endfor
            </select>
        </div>

        {{-- Festival List --}}
        <div id="gcFestivalList" style="flex: 1; overflow-y: auto; max-height: 600px;">
            @include('client.festival-list-compact', ['festivals' => $festivals])
        </div>
    </div>
</div>

{{-- Selection Modal --}}
<div class="gc-modal-overlay" id="gcModal">
    <div class="gc-modal">
        <button class="gc-modal-close" onclick="closeGCModal()">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="gc-modal-title" id="gcModalTitle">Select Festival</div>

        <input type="hidden" id="gcModalFestivalId">

        <label class="gc-modal-label">Content Types *</label>
        <div class="gc-type-grid">
            <label class="gc-type-option" onclick="toggleGCType(this)">
                <input type="checkbox" name="content_types[]" value="post">
                <i class="fa-solid fa-image"></i> Post
            </label>
            <label class="gc-type-option" onclick="toggleGCType(this)">
                <input type="checkbox" name="content_types[]" value="story">
                <i class="fa-solid fa-mobile-screen"></i> Story
            </label>
            <label class="gc-type-option" onclick="toggleGCType(this)">
                <input type="checkbox" name="content_types[]" value="reel">
                <i class="fa-solid fa-film"></i> Reel
            </label>
            <label class="gc-type-option" onclick="toggleGCType(this)">
                <input type="checkbox" name="content_types[]" value="video">
                <i class="fa-solid fa-video"></i> Video
            </label>
            <label class="gc-type-option" onclick="toggleGCType(this)">
                <input type="checkbox" name="content_types[]" value="carousel">
                <i class="fa-solid fa-layer-group"></i> Carousel
            </label>
        </div>

        <label class="gc-modal-label">Notes (optional)</label>
        <textarea class="gc-modal-notes" id="gcModalNotes" placeholder="Any ideas or preferences..."></textarea>

        @if(auth()->user()->google_calendar_token)
            <div class="gc-sync-toggle">
                <input type="checkbox" id="gcSyncToggle" checked>
                <label for="gcSyncToggle" style="margin: 0; cursor: pointer; font-weight: 500;">
                    <i class="fa-brands fa-google"></i> Sync to Google Calendar
                </label>
            </div>
        @endif

        <div class="gc-modal-actions">
            <button class="gc-modal-btn gc-modal-btn-cancel" onclick="closeGCModal()">Cancel</button>
            <button class="gc-modal-btn gc-modal-btn-save" onclick="saveGCFestivalSelection()">
                <i class="fa-solid fa-check"></i> Save
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Toggle content type
    function toggleGCType(el) {
        const cb = el.querySelector('input');
        cb.checked = !cb.checked;
        el.classList.toggle('checked', cb.checked);
    }

    // Open modal
    function openGCModal(festivalId, name, selectedTypes, notes) {
        document.getElementById('gcModalFestivalId').value = festivalId;
        document.getElementById('gcModalTitle').textContent = name;
        document.getElementById('gcModalNotes').value = notes || '';

        document.querySelectorAll('.gc-type-option').forEach(el => {
            const cb = el.querySelector('input');
            cb.checked = false;
            el.classList.remove('checked');
        });

        if (selectedTypes && Array.isArray(selectedTypes)) {
            selectedTypes.forEach(type => {
                const cb = document.querySelector(`.gc-type-option input[value="${type}"]`);
                if (cb) {
                    cb.checked = true;
                    cb.closest('.gc-type-option').classList.add('checked');
                }
            });
        }

        document.getElementById('gcModal').classList.add('active');
    }

    function closeGCModal() {
        document.getElementById('gcModal').classList.remove('active');
    }

    document.getElementById('gcModal').addEventListener('click', function(e) {
        if (e.target === this) closeGCModal();
    });

    // Save selection
    function saveGCFestivalSelection() {
        const festivalId = document.getElementById('gcModalFestivalId').value;
        const notes = document.getElementById('gcModalNotes').value;
        const syncToGoogle = document.getElementById('gcSyncToggle')?.checked || false;
        const types = [];

        document.querySelectorAll('.gc-type-option input:checked').forEach(cb => types.push(cb.value));

        if (types.length === 0) {
            alert('Please select at least one content type.');
            return;
        }

        fetch(`/client/festivals/${festivalId}/select`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                content_types: types,
                notes: notes,
                sync_to_google: syncToGoogle
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeGCModal();
                const countEl = document.getElementById('gcSelectedCount');
                countEl.textContent = parseInt(countEl.textContent) + 1;
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }

    // Deselect festival
    function deselectGCFestival(festivalId) {
        if (!confirm('Remove this festival selection?')) return;

        fetch(`/client/festivals/${festivalId}/deselect`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const countEl = document.getElementById('gcSelectedCount');
                countEl.textContent = Math.max(0, parseInt(countEl.textContent) - 1);
                location.reload();
            }
        });
    }

    // Google Calendar connection
    function connectGoogleCalendar() {
        window.location.href = '/client/google-calendar/auth';
    }

    function disconnectGoogleCalendar() {
        if (!confirm('Disconnect Google Calendar? Your events will not be removed.')) return;

        fetch('/client/google-calendar/disconnect', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    // Filtering
    function applyGCFilters() {
        const category = document.getElementById('gcCategoryFilter').value;
        const month = document.getElementById('gcMonthFilter').value;

        const params = new URLSearchParams();
        if (category) params.set('category', category);
        if (month) params.set('month', month);

        fetch(`{{ route('client.festivals') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('gcFestivalList').innerHTML = data.html;
        });
    }

    document.getElementById('gcCategoryFilter').addEventListener('change', applyGCFilters);
    document.getElementById('gcMonthFilter').addEventListener('change', applyGCFilters);
</script>



<script>
/* =========================================
   CALENDAR CLICK TO OPEN DATE DETAILS
========================================= */

document.addEventListener("DOMContentLoaded", function () {

    const dayBoxes = document.querySelectorAll(".fest-day");

    dayBoxes.forEach(day => {
        day.addEventListener("click", function () {
            openDatePopup(this);
        });
    });

});


/* OPEN POPUP */
function openDatePopup(dayBox){

    closeDatePopup();

    dayBox.classList.add("active-day");
    document.body.classList.add("calendar-lock");

    let dateNum   = dayBox.querySelector(".fest-day-num")?.innerText || "Date";
    let eventsBox = dayBox.querySelector(".fest-day-events");

    let eventsHtml = "";

    if(eventsBox){

        let pills = eventsBox.querySelectorAll(".fest-event-pill");

        if(pills.length > 0){

            pills.forEach(pill => {

                let eventName = pill.innerText.trim();

                eventsHtml += `
                    <div class="calendar-focus-item">
                        <div class="calendar-focus-item-name">${eventName}</div>

                        <div class="calendar-focus-item-meta">
                            <span class="calendar-focus-pill">Festival</span>
                            <span class="calendar-focus-pill">Scheduled</span>
                        </div>

                        <div class="calendar-focus-desc">
                            Content planning available for this date.
                        </div>
                    </div>
                `;
            });

        }else{
            eventsHtml = `
                <div class="calendar-focus-empty">
                    No festival planned for this day.
                </div>
            `;
        }

    }

    /* Overlay */
    let overlay = document.createElement("div");
    overlay.className = "calendar-focus-overlay";
    overlay.id = "calendarOverlay";
    overlay.onclick = closeDatePopup;

    /* Popup */
    let popup = document.createElement("div");
    popup.className = "calendar-focus-card";
    popup.id = "calendarPopup";

    popup.innerHTML = `
        <div class="calendar-focus-top">

            <div class="calendar-focus-date">

                <div class="calendar-focus-badge">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>

                <div>
                    <div class="calendar-focus-title">
                        ${dateNum} {{ $monthLabel }}
                    </div>

                    <div class="calendar-focus-sub">
                        Festival Schedule Details
                    </div>
                </div>

            </div>

            <button class="calendar-focus-close" onclick="closeDatePopup()">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

        <div class="calendar-focus-events">
            ${eventsHtml}
        </div>
    `;

    document.body.appendChild(overlay);
    document.body.appendChild(popup);
}


/* CLOSE POPUP */
function closeDatePopup(){

    document.body.classList.remove("calendar-lock");

    document.querySelectorAll(".fest-day").forEach(el=>{
        el.classList.remove("active-day");
    });

    let overlay = document.getElementById("calendarOverlay");
    let popup   = document.getElementById("calendarPopup");

    if(overlay) overlay.remove();
    if(popup) popup.remove();
}


/* ESC KEY CLOSE */
document.addEventListener("keydown", function(e){
    if(e.key === "Escape"){
        closeDatePopup();
    }
});
</script>
@endpush
