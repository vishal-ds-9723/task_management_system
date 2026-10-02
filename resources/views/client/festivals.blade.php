@php
$carbon = '\Carbon\Carbon';
@endphp
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/client-festivals.css') }}">
@endpush

@section('content')
@php
    $catIcon = fn($c) => match($c) {
        'religious'=>'fa-hands-praying','national'=>'fa-flag','international'=>'fa-globe',
        'awareness'=>'fa-ribbon','cultural'=>'fa-masks-theater',default=>'fa-calendar-star'
    };
    $totalCount = $festivals->count();
    $remainingCount = $totalCount - $selectedCount;
    $progressPct = $totalCount > 0 ? round(($selectedCount / $totalCount) * 100) : 0;
@endphp

<div class="fest-page">

    {{-- Header --}}
    <div class="fest-header">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:20px;">
            <div>
                <div class="fest-title">
                    <div class="fest-title-icon"><i class="fa-solid fa-calendar-star"></i></div>
                    Festival Calendar
                </div>
                <div class="fest-sub">Select festivals for <strong>{{ $monthLabel }}</strong> and choose what content you want created</div>
            </div>

            <div class="fest-nav-wrap">
                @php
                    $minMonth = now()->format('Y-m');
                    $maxMonth = now()->addMonth()->format('Y-m');
                @endphp
                <input type="month" class="fest-month-picker" id="monthPicker" 
                    value="{{ \Carbon\Carbon::parse($monthDate)->format('Y-m') }}" 
                    min="{{ $minMonth }}" 
                    max="{{ $maxMonth }}" 
                    onchange="jumpMonth(this.value)">
                
                <div class="fest-search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" class="fest-search-input" id="festSearch" placeholder="Search festivals..." oninput="filterCards('all')">
                </div>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    @if($totalCount > 0)
    {{--<div class="fest-progress-wrap">
        <div class="fest-progress-header">
            <div class="fest-progress-label">Your Progress</div>
            <div class="fest-progress-count">{{ $selectedCount }} / {{ $totalCount }} festivals selected</div>
        </div>
        <div class="fest-progress-bar">
            <div class="fest-progress-fill" style="width:{{ $progressPct }}%"></div>
        </div>
    </div>--}}
    @endif

    {{-- Stats --}}
    <div class="fest-stats">
        <div class="fest-stat">
            <div class="fest-stat-icon" style="background:rgba(var(--primary-rgb),.08);color:var(--primary)">
                <i class="fa-solid fa-calendar-star"></i>
            </div>
            <div>
                <div class="fest-stat-val">{{ $totalCount }}</div>
                <div class="fest-stat-lbl">Festivals</div>
            </div>
        </div>
        <div class="fest-stat">
            <div class="fest-stat-icon" style="background:rgba(16,185,129,.08);color:#059669">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="fest-stat-val">{{ $selectedCount }}</div>
                <div class="fest-stat-lbl">Selected</div>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    @if($totalCount > 0)
    <div class="fest-filters">
        <button style="font-weight: 700;" class="fest-filter-btn active" onclick="filterCards('all')" data-filter="all">
            <i class="fa-solid fa-border-all"></i> All <span class="count">{{ $totalCount }}</span>
        </button>
        <button style="font-weight: 700;" class="fest-filter-btn" onclick="filterCards('selected')" data-filter="selected">
            <i class="fa-solid fa-circle-check"></i> Selected <span class="count">{{ $selectedCount }}</span>
        </button>
        <button style="font-weight: 700;" class="fest-filter-btn" onclick="filterCards('unselected')" data-filter="unselected">
            <i class="fa-regular fa-circle"></i> Not Selected <span class="count">{{ $remainingCount }}</span>
        </button>
        @php $categories = $festivals->pluck('category')->filter()->unique()->sort(); @endphp
        @foreach($categories as $cat)
        <button style="font-weight: 700;" class="fest-filter-btn" onclick="filterCards('cat-{{ $cat }}')" data-filter="cat-{{ $cat }}">
            <i class="fa-solid {{ $catIcon($cat) }}"></i> {{ ucfirst($cat) }}
            <span class="count">{{ $festivals->where('category', $cat)->count() }}</span>
        </button>
        @endforeach
    </div>
    @endif

    {{-- Festival Calendar --}}
    @if($festivals->isNotEmpty())
    @php
        $currentMonth = \Carbon\Carbon::parse($monthDate ?? now());
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth   = $currentMonth->copy()->endOfMonth();
        $startGrid = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
        $endGrid   = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
    @endphp

    {{-- Hero Calendar --}}
    <div class="fest-calendar-section" style="margin: 40px 0;">
        <div class="fest-cal-head">
            <div>
                <div class="fest-cal-title" style="font-size: 28px;">
                    <i class="fa-solid fa-calendar-days" style="margin-right: 12px;"></i>
                    {{ $monthLabel }}
                </div>
                <div class="fest-cal-sub">Click days to preview festivals</div>
            </div>
            <div class="fest-cal-logo">
                <img src="{{ asset('images/logo.png') }}" alt="The Layout" style="width: 145px; height: 70px;">
            </div>
        </div>

        <div class="fest-week-row" style="gap: 12px; margin-bottom: 20px;">
            <div style="padding: 12px 4px;">Sun</div>
            <div style="padding: 12px 4px;">Mon</div>
            <div style="padding: 12px 4px;">Tue</div>
            <div style="padding: 12px 4px;">Wed</div>
            <div style="padding: 12px 4px;">Thu</div>
            <div style="padding: 12px 4px;">Fri</div>
            <div style="padding: 12px 4px;">Sat</div>
        </div>

        <div class="fest-calendar-grid" style="gap: 16px;">
            @for($date = $startGrid->copy(); $date->lte($endGrid); $date->addDay())
                @php
                    $dayFestivals = $festivals->filter(fn($f) => $f->date->isSameDay($date));
                    $isToday = $date->isToday();
                    $isCurrentMonth = $date->month == $currentMonth->month;
                @endphp
                <x-client-festival-day 
                    :date="$date" 
                    :day-festivals="$dayFestivals" 
                    :is-today="$isToday" 
                    :is-current-month="$isCurrentMonth"
                    :cat-icon="$catIcon" />
            @endfor
        </div>
    </div>

    {{-- Festival Cards --}}
    <div class="fest-cards-side" style="width: 100%; max-height: none; padding: 0;">
        <div class="fest-cal-head">
            <div class="fest-cal-title">
                <i class="fa-solid fa-list-check" style="margin-right: 12px;"></i>
                Festivals ({{ $totalCount }} available)
            </div>
            <div class="fest-cal-sub">Select content types and platforms</div>
        </div>

        @if(!$isSelectionAllowed)
            <div style="background:var(--amber-soft); color:var(--amber); padding:20px; border-radius:var(--radius-sm); margin:0 0 24px 0; font-size:13px; font-weight:800; border:1px solid rgba(255,54,54,0.15); display:flex; align-items:center; gap:12px;">
                <i class="fa-solid fa-circle-info" style="font-size:16px;"></i>
                Selection is only allowed for current/next month
            </div>
        @endif

        <div class="fest-grid" id="festGrid" style="grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 28px; padding-right: 0;">
            @foreach($festivals as $festival)
                <x-client-festival-card :festival="$festival" :is-selection-allowed="$isSelectionAllowed" />
            @endforeach
        </div>
    </div>

    @else
    <div class="fest-empty" style="max-width: 600px; margin: 80px auto;">
        <i class="fa-solid fa-calendar-xmark" style="font-size: 64px;"></i>
        <div class="fest-empty-title">No festivals for {{ $monthLabel }}</div>
        <div class="fest-empty-desc">Your strategist hasn't scheduled festivals yet. Check back soon!</div>
        <div style="margin-top: 24px;">
            <a href="{{ route('client.dashboard') }}" class="fest-btn primary" style="font-size: 14px; padding: 12px 28px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    @endif

    {{-- Content Selection Modal --}}
    <div class="fest-modal-bg" id="festModalBg">
        <div class="fest-modal">
            <div class="fest-modal-head">
                <div class="fest-modal-icon" id="modalIcon"><i class="fa-solid fa-calendar-star"></i></div>
                <div class="fest-modal-name" id="modalName">Festival Selection</div>
                <div class="fest-modal-date" id="modalDate"><i class="fa-regular fa-calendar"></i> <span></span></div>
                <button class="fest-modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="fest-modal-body">
                {{-- Content Types --}}
                <div class="fest-section-title"><i class="fa-solid fa-layer-group"></i> Content Types</div>
                <div class="fest-chips">
                    @foreach(['post'=>'Post','reel'=>'Reel','story'=>'Story','carousel'=>'Carousel','video'=>'Video'] as $val => $lbl)
                    @php $icon = match($val){'post'=>'fa-image','reel'=>'fa-video','story'=>'fa-mobile-screen','carousel'=>'fa-images','video'=>'fa-video'}; @endphp
                    <div class="fest-chip" data-val="{{ $val }}" onclick="toggleChip(this)">
                        <i class="fa-solid {{ $icon }}"></i> {{ $lbl }}
                        <i class="fa-solid fa-circle-check fest-chip-check"></i>
                    </div>
                    @endforeach
                </div>

                {{-- Platforms --}}
                <div class="fest-section-title"><i class="fa-solid fa-share-nodes"></i> Platforms</div>
                <div class="fest-plat-chips">
                    <div class="fest-plat-chip" data-plat="instagram" onclick="togglePlat(this)">
                        <i class="fa-brands fa-instagram"></i> Instagram
                    </div>
                    <div class="fest-plat-chip" data-plat="facebook" onclick="togglePlat(this)">
                        <i class="fa-brands fa-facebook-f"></i> Facebook
                    </div>
                </div>

                {{-- Notes --}}
                <div class="fest-section-title"><i class="fa-solid fa-sticky-note"></i> Notes (Optional)</div>
                <textarea class="fest-notes" id="modalNotes" placeholder="Special requirements, hashtags, brand guidelines... (max 500 chars)" rows="4"></textarea>
                <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--muted);margin-top:6px;">
                    <span>Share your content vision here</span>
                    <span id="charCount">0 / 500</span>
                </div>
            </div>
            <div class="fest-modal-foot">
                <button class="fest-btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="fest-btn-save" id="btnSave">Save Selection</button>
            </div>
        </div>
    </div>

    {{-- Day Detail Modal --}}
    <div class="fest-day-modal-bg" id="dayModalBg">
        <div class="fest-day-modal">
            <button class="fest-day-modal-close" onclick="closeDayModal()"><i class="fa-solid fa-xmark"></i></button>
            <div class="fest-day-modal-title" id="dayModalTitle">
                <i class="fa-solid fa-calendar-day"></i>
                Select Date
            </div>
            <div class="fest-day-modal-sub" id="dayModalSubtitle">Festivals available</div>
            <div class="fest-day-modal-list" id="dayModalList">
                <!-- Dynamic content -->
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
// Enhanced Festival JS - Production Ready
const festivalsData = @json($festivalMap ?? []);
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let currentFestivalId = null;

// Core Functions
function closeModal() {
    document.getElementById('festModalBg')?.classList.remove('show');
    document.body.style.overflow = '';
}

function closeDayModal() {
    document.getElementById('dayModalBg')?.classList.remove('show');
    document.body.style.overflow = '';
}

function toggleChip(el) { el.classList.toggle('active'); }

function togglePlat(el) {
    const plat = el.dataset.plat;
    if (plat === 'instagram') el.classList.toggle('active-insta');
    else el.classList.toggle('active-fb');
}

function updateCharCount(el) {
    const len = el.value.length;
    document.getElementById('charCount').textContent = `${len} / 500`;
}

// Filter Cards (optimized)
function filterCards(filter) {
    document.querySelectorAll('.fest-filter-btn.active')?.forEach(b => b.classList.remove('active'));
    const targetBtn = document.querySelector(`[data-filter="${filter}"]`);
    if (targetBtn) targetBtn.classList.add('active');

    const q = (document.getElementById('festSearch')?.value || '').toLowerCase();
    const activeFilter = document.querySelector('.fest-filter-btn.active')?.dataset.filter || 'all';

    document.querySelectorAll('.fest-card').forEach(card => {
        const name = card.querySelector('.fest-card-name')?.textContent.toLowerCase() || '';
        const desc = card.querySelector('.fest-card-desc')?.textContent.toLowerCase() || '';
        const isSel = card.classList.contains('selected');
        const cat = card.dataset.category || '';
        
        let show = activeFilter === 'all' || 
                   (activeFilter === 'selected' && isSel) || 
                   (activeFilter === 'unselected' && !isSel) ||
                   (activeFilter.startsWith('cat-') && cat === activeFilter.slice(4));
                   
        if (q && !name.includes(q) && !desc.includes(q)) show = false;
        
        card.style.display = show ? '' : 'none';
        card.style.opacity = show ? '1' : '0';
    });
}

// Month Navigation
function jumpMonth(val) {
    const url = new URLSearchParams(window.location.search);
    url.set('month', val);
    window.location.search = url.toString();
}

// Day Modal
function showDayDetails(dateKey, fullDateStr) {
    const matched = Object.values(festivalsData).filter(f => {
        const fDate = new Date(f.date);
        const dKey = new Date(dateKey);
        return fDate.toDateString() === dKey.toDateString();
    });

    if (!matched.length) return;

    document.getElementById('dayModalDate').textContent = fullDateStr;
    document.getElementById('dayModalCount').textContent = `${matched.length} festival${matched.length > 1 ? 's' : ''}`;

    const listEl = document.getElementById('dayModalList');
    listEl.innerHTML = matched.map(f => `
        <div class="fest-day-modal-item ${f.is_selected ? 'selected' : ''}" onclick="openModal(${f.id})">
            <div class="fest-day-modal-icon"><i class="fa-solid ${f.icon}"></i></div>
            <div class="fest-day-modal-info">
                <div class="fest-day-modal-name">${f.name}</div>
                <div class="fest-day-modal-cat">${f.is_selected ? '✅ Selected' : 'Select now'}</div>
            </div>
        </div>
    `).join('');

    document.getElementById('dayModalBg').classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Modal Management
function openModal(id) {
    currentFestivalId = id;
    const fest = festivalsData[id];
    if (!fest) return;

    // Populate modal
    document.getElementById('modalIcon').innerHTML = `<i class="fa-solid ${fest.icon}"></i>`;
    document.getElementById('modalName').textContent = fest.name;
    document.getElementById('modalDate').innerHTML = `<i class="fa-regular fa-calendar"></i> ${fest.date}`;

    // Reset form
    document.querySelectorAll('.fest-chip, .fest-plat-chip').forEach(el => {
        el.classList.remove('active', 'active-insta', 'active-fb');
    });
    document.getElementById('modalNotes').value = '';

    // Prefill if selected
    if (fest.is_selected) {
        document.getElementById('modalNotes').value = fest.notes || '';
        (fest.content_types || []).forEach(type => {
            document.querySelector(`.fest-chip[data-val="${type}"]`)?.classList.add('active');
        });
        (fest.platforms || []).forEach(plat => {
            document.querySelector(`.fest-plat-chip[data-plat="${plat}"]`)?.classList.add(`active-${plat}`);
        });
        document.getElementById('btnSave').textContent = 'Update Selection';
    } else {
        document.getElementById('btnSave').textContent = 'Save Selection';
    }

    document.getElementById('festModalBg').classList.add('show');
    document.body.style.overflow = 'hidden';
    document.getElementById('modalNotes').focus();
}

// Save Selection (AJAX)
async function saveSelection() {
    const notesEl = document.getElementById('modalNotes');
    const contentTypes = Array.from(document.querySelectorAll('.fest-chip.active')).map(el => el.dataset.val);
    const platforms = Array.from(document.querySelectorAll('.fest-plat-chip.active-insta, .fest-plat-chip.active-fb')).map(el => el.dataset.plat);

    if (!contentTypes.length) return showToast('Select at least one content type', 'error');

    const btn = document.getElementById('btnSave');
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';

    try {
        const response = await fetch(`/client/festivals/${currentFestivalId}/select`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                notes: notesEl.value.trim(),
                content_types: contentTypes,
                platforms
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message || 'Selection saved!', 'success');
            closeModal();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Failed to save', 'error');
        }
    } catch (error) {
        showToast('Network error. Try again.', 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

async function removeFestival(id) {
    if (!confirm('Remove this selection?')) return;

    try {
        const response = await fetch(`/client/festivals/${id}/deselect`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        });

        const data = await response.json();
        if (data.success) {
            showToast('Removed successfully', 'success');
            setTimeout(() => location.reload(), 600);
        } else {
            showToast('Failed to remove', 'error');
        }
    } catch {
        showToast('Network error', 'error');
    }
}

// Toast System
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle' };
    toast.innerHTML = `<i class="fa-solid ${icons[type] || 'fa-info-circle'}"></i> ${message}`;
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position:fixed; bottom:24px; right:24px; padding:16px 24px; border-radius:12px;
        font-weight:600; font-size:14px; z-index:9999; backdrop-filter:blur(12px);
        display:flex; gap:12px; align-items:center; box-shadow:0 12px 40px rgba(0,0,0,0.15);
        animation: toastSlide 0.4s cubic-bezier(0.4,0,0.2,1);
    `;
    
    const colors = {
        success: 'linear-gradient(135deg, #10b981, #059669)',
        error: 'linear-gradient(135deg, #ef4444, #dc2626)',
        info: 'linear-gradient(135deg, #3b82f6, #2563eb)'
    };
    toast.style.background = colors[type] || colors.info;
    toast.style.color = 'white';

    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Modal close handlers
    ['festModalBg', 'dayModalBg'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', e => e.target === el && (id.includes('Modal') ? closeDayModal() : closeModal()));
    });

    // Global ESC handler
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeModal();
            closeDayModal();
        }
    });

    // Debounced search
    const searchInput = document.getElementById('festSearch');
    if (searchInput) {
        let timeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => filterCards('all'), 250);
        });
    }
});

// Initialize on load
if (typeof showToast === 'undefined') window.showToast = showToast;
</script>
@endpush

