@extends('layouts.app')

@push('styles')
    <link href="{{ asset('css/pages/designer-theme.css') }}?v=2.0" rel="stylesheet">
@endpush

@section('content')
<div class="designer-minimal-schedule" style="max-width:1240px;margin:0 auto;padding-bottom:50px">
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
        <div>
            <h1 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:22px;font-weight:800;color:var(--dz-text-main);margin:0;letter-spacing:-0.02em">
                Schedule & Calendar
            </h1>
            <p style="font-size:13px;color:var(--dz-text-muted);margin:3px 0 0">
                Deliverables, client deadlines, and scheduled posts.
            </p>
        </div>

        <div style="display:flex;align-items:center;gap:12px">
            <x-client-select :clients="$clients" name="client_id" id="clientFilter" placeholder="Filter by client..." width="220px" />
        </div>
    </div>

    {{-- 2-Column Equal Width & Height Layout --}}
    <div class="schedule-dual-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:stretch">
        {{-- Left: Enlarged Calendar Radar (Equal 50% Width & Height) --}}
        <div class="card schedule-card-fixed" style="height:580px;display:flex;flex-direction:column;justify-content:space-between;padding:26px 30px;border:1px solid var(--dz-border);border-radius:16px;background:#FFFFFF;box-shadow:var(--dz-shadow-xs)">
            <div>
                {{-- Month Navigator --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                    <span id="miniMonthTitle" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:16px;font-weight:800;color:var(--dz-text-main)"></span>
                    <div style="display:flex;gap:6px">
                        <button type="button" id="miniPrevBtn" class="mini-cal-btn" title="Previous Month">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button type="button" id="miniNextBtn" class="mini-cal-btn" title="Next Month">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                {{-- Day Name Headers --}}
                <div class="mini-cal-grid" style="margin-bottom:10px">
                    <div class="mini-cal-day-head">MON</div>
                    <div class="mini-cal-day-head">TUE</div>
                    <div class="mini-cal-day-head">WED</div>
                    <div class="mini-cal-day-head">THU</div>
                    <div class="mini-cal-day-head">FRI</div>
                    <div class="mini-cal-day-head">SAT</div>
                    <div class="mini-cal-day-head">SUN</div>
                </div>

                {{-- Date Numbers Grid --}}
                <div id="miniCalDates" class="mini-cal-grid">
                    {{-- Injected dynamically --}}
                </div>
            </div>

            {{-- Quick Filter Buttons --}}
            <div style="display:flex;gap:10px;margin-top:auto;padding-top:18px;border-top:1px solid var(--dz-border)">
                <button type="button" id="filterTodayBtn" class="btn-sec" style="flex:1;padding:9px 14px !important;font-size:12.5px !important;font-weight:700 !important;justify-content:center">
                    Today
                </button>
                <button type="button" id="filterAllBtn" class="btn-sec active" style="flex:1;padding:9px 14px !important;font-size:12.5px !important;font-weight:700 !important;justify-content:center">
                    All Upcoming
                </button>
            </div>
        </div>

        {{-- Right: Scrollable Agenda Stream (Equal 50% Width & Height) --}}
        <div class="card schedule-card-fixed" style="height:580px;display:flex;flex-direction:column;padding:26px 30px;border:1px solid var(--dz-border);border-radius:16px;background:#FFFFFF;box-shadow:var(--dz-shadow-xs)">
            {{-- Fixed Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--dz-border);flex-shrink:0">
                <h2 id="agendaViewTitle" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:16px;font-weight:800;color:var(--dz-text-main);margin:0">
                    Upcoming Deliverables
                </h2>
                <span id="agendaCountBadge" style="font-size:12px;font-weight:700;color:var(--dz-text-muted);background:var(--dz-surface-sub);padding:3px 10px;border-radius:99px;border:1px solid var(--dz-border)">
                    0 items
                </span>
            </div>

            {{-- Scrollable Tasks List --}}
            <div id="agendaList" class="agenda-scroll-container">
                <div style="padding:40px 0;text-align:center;color:var(--dz-text-muted);font-size:13px">
                    <i class="fa-solid fa-spinner fa-spin" style="color:var(--dz-red);font-size:18px;margin-bottom:8px"></i>
                    <div>Loading schedule...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ── Responsive Grid ── */
    @media (max-width: 960px) {
        .schedule-dual-grid {
            grid-template-columns: 1fr !important;
        }
        .schedule-card-fixed {
            height: auto !important;
            min-height: 520px;
        }
    }

    /* ── Mini Calendar Radar ── */
    .mini-cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
        text-align: center;
    }
    .mini-cal-day-head {
        font-size: 11px;
        font-weight: 700;
        color: var(--dz-text-muted);
        letter-spacing: 0.04em;
        padding: 4px 0;
    }
    .mini-cal-day {
        position: relative;
        height: 46px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--dz-text-main);
        border-radius: 10px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: var(--dz-transition);
        user-select: none;
    }
    .mini-cal-day:hover {
        background: var(--dz-surface-sub);
        border-color: var(--dz-border);
        color: var(--dz-red);
    }
    .mini-cal-day.is-other {
        color: #CBD5E1;
        cursor: default;
    }
    .mini-cal-day.is-other:hover {
        background: transparent;
        border-color: transparent;
        color: #CBD5E1;
    }
    .mini-cal-day.is-today {
        color: var(--dz-red);
        font-weight: 800;
        background: var(--dz-red-light);
        border-color: var(--dz-red-border);
    }
    .mini-cal-day.is-selected {
        background: var(--dz-text-main) !important;
        border-color: var(--dz-text-main) !important;
        color: #FFFFFF !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    }
    .mini-cal-day.is-selected .mini-cal-dot {
        background: #FFFFFF !important;
    }
    .mini-cal-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--dz-red);
        position: absolute;
        bottom: 5px;
    }
    .mini-cal-btn {
        background: var(--dz-surface);
        border: 1px solid var(--dz-border);
        color: var(--dz-text-sub);
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        cursor: pointer;
        transition: var(--dz-transition);
    }
    .mini-cal-btn:hover {
        background: var(--dz-red-light);
        border-color: var(--dz-red);
        color: var(--dz-red);
    }

    /* ── Right Box Scroll Container & Scrollbar ── */
    .agenda-scroll-container {
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-right: 6px;
    }
    .agenda-scroll-container::-webkit-scrollbar {
        width: 6px;
    }
    .agenda-scroll-container::-webkit-scrollbar-track {
        background: #F8FAFC;
        border-radius: 99px;
    }
    .agenda-scroll-container::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 99px;
        transition: background 0.2s ease;
    }
    .agenda-scroll-container::-webkit-scrollbar-thumb:hover {
        background: var(--dz-red);
    }

    /* ── Agenda List Rows (Pure Minimalism) ── */
    .agenda-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid var(--dz-border-light);
        background: var(--dz-surface);
        text-decoration: none;
        color: inherit;
        transition: var(--dz-transition);
        gap: 14px;
    }
    .agenda-row:hover {
        background: #F8FAFC;
        border-color: var(--dz-border);
        transform: translateX(2px);
    }
    .agenda-row:hover .agenda-title {
        color: var(--dz-red);
    }
    .agenda-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        min-width: 0;
    }
    .agenda-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .agenda-title {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--dz-text-main);
        transition: var(--dz-transition);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .agenda-client {
        font-size: 12px;
        font-weight: 600;
        color: var(--dz-text-muted);
        display: flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
    }
    .agenda-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .agenda-date {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--dz-text-sub);
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .agenda-date.is-overdue {
        color: var(--dz-red);
        font-weight: 700;
    }

    /* ── Section Date Divider ── */
    .agenda-section-divider {
        font-size: 11px;
        font-weight: 700;
        color: var(--dz-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 12px 0 4px 2px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let allEvents = [];
    let currentFilteredEvents = [];
    let displayMonth = new Date();
    let selectedDateFilter = null; // null = all upcoming

    const clientFilterEl = document.getElementById('clientFilter');

    // Fetch Events
    async function loadEvents() {
        try {
            const clientId = clientFilterEl ? (clientFilterEl.value || '') : '';
            const res = await fetch(`/api/calendar/events?client_id=${clientId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Failed to load events');
            allEvents = await res.json();
            filterAndRender();
        } catch (err) {
            console.error('Error fetching calendar events:', err);
            document.getElementById('agendaList').innerHTML = `
                <div style="padding:30px;text-align:center;color:var(--dz-text-muted);font-size:13px">
                    Could not load schedule. Please refresh.
                </div>
            `;
        }
    }

    function filterAndRender() {
        const clientId = clientFilterEl ? (clientFilterEl.value || '') : '';
        currentFilteredEvents = allEvents.filter(e => {
            if (!clientId) return true;
            return e.extendedProps?.clientId == clientId || e.extendedProps?.client_id == clientId;
        });

        renderMiniCalendar();
        renderAgendaList();
    }

    // ── Mini Calendar ──
    function renderMiniCalendar() {
        const titleEl = document.getElementById('miniMonthTitle');
        const gridEl = document.getElementById('miniCalDates');
        if (!titleEl || !gridEl) return;

        const year = displayMonth.getFullYear();
        const month = displayMonth.getMonth();

        titleEl.textContent = displayMonth.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

        const firstDayIndex = (new Date(year, month, 1).getDay() + 6) % 7; // Monday = 0
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        const today = new Date();
        const todayISO = today.toISOString().split('T')[0];

        let html = '';

        // Previous month filler days
        for (let i = firstDayIndex - 1; i >= 0; i--) {
            html += `<div class="mini-cal-day is-other">${daysInPrevMonth - i}</div>`;
        }

        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const dateObj = new Date(year, month, day);
            const dateISO = dateObj.toISOString().split('T')[0];
            const isToday = dateISO === todayISO;
            const isSelected = selectedDateFilter === dateISO;

            // Has tasks on this date?
            const hasTasks = currentFilteredEvents.some(e => {
                const eDate = e.start ? e.start.split('T')[0] : '';
                return eDate === dateISO;
            });

            html += `
                <div class="mini-cal-day ${isToday ? 'is-today' : ''} ${isSelected ? 'is-selected' : ''}" data-date="${dateISO}">
                    ${day}
                    ${hasTasks ? '<span class="mini-cal-dot"></span>' : ''}
                </div>
            `;
        }

        gridEl.innerHTML = html;

        gridEl.querySelectorAll('.mini-cal-day:not(.is-other)').forEach(el => {
            el.addEventListener('click', function() {
                const date = this.dataset.date;
                if (selectedDateFilter === date) {
                    selectedDateFilter = null; // Toggle off
                    document.getElementById('filterAllBtn').classList.add('active');
                    document.getElementById('filterTodayBtn').classList.remove('active');
                } else {
                    selectedDateFilter = date;
                    document.getElementById('filterAllBtn').classList.remove('active');
                    if (date === todayISO) {
                        document.getElementById('filterTodayBtn').classList.add('active');
                    } else {
                        document.getElementById('filterTodayBtn').classList.remove('active');
                    }
                }
                renderMiniCalendar();
                renderAgendaList();
            });
        });
    }

    // ── Agenda Stream ──
    function renderAgendaList() {
        const container = document.getElementById('agendaList');
        const titleEl = document.getElementById('agendaViewTitle');
        const badgeEl = document.getElementById('agendaCountBadge');
        if (!container) return;

        const todayISO = new Date().toISOString().split('T')[0];

        let displayItems = [];

        if (selectedDateFilter) {
            const formattedSelected = new Date(selectedDateFilter + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' });
            titleEl.textContent = formattedSelected;
            displayItems = currentFilteredEvents.filter(e => {
                const eDate = e.start ? e.start.split('T')[0] : '';
                return eDate === selectedDateFilter;
            });
        } else {
            titleEl.textContent = 'Upcoming Deliverables';
            // Sort by start date
            displayItems = [...currentFilteredEvents].sort((a, b) => {
                const dateA = a.start || '';
                const dateB = b.start || '';
                return dateA.localeCompare(dateB);
            });
        }

        badgeEl.textContent = `${displayItems.length} items`;

        if (displayItems.length === 0) {
            container.innerHTML = `
                <div style="padding:48px 0;text-align:center;color:var(--dz-text-muted)">
                    <i class="fa-regular fa-calendar-check" style="font-size:26px;color:var(--dz-text-muted);margin-bottom:8px"></i>
                    <div style="font-size:13.5px;font-weight:600;color:var(--dz-text-main)">No deliverables scheduled</div>
                    <div style="font-size:12px;margin-top:2px">Enjoy the open schedule or select another date.</div>
                </div>
            `;
            return;
        }

        // Group by Date for cleaner scanning if viewing all
        let html = '';
        let lastDateGroup = null;

        displayItems.forEach(e => {
            const props = e.extendedProps || {};
            const dateISO = e.start ? e.start.split('T')[0] : '';
            const isOverdue = dateISO && dateISO < todayISO && props.status !== 'completed';
            const isUrgent = props.priority === 'urgent';

            // Dot color
            let dotColor = '#2563EB';
            if (isUrgent) dotColor = '#EF4444';
            else if (props.status === 'completed') dotColor = '#10B981';
            else if (props.status === 'review') dotColor = '#8B5CF6';

            // Date formatting
            let dateText = 'No date';
            if (dateISO) {
                if (dateISO === todayISO) dateText = 'Today';
                else {
                    const d = new Date(dateISO + 'T00:00:00');
                    dateText = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }
            }

            // Section divider if viewing all
            if (!selectedDateFilter && dateISO !== lastDateGroup) {
                lastDateGroup = dateISO;
                let sectionLabel = dateText;
                if (dateISO === todayISO) sectionLabel = '⚡ Due Today';
                else if (isOverdue) sectionLabel = '🔴 Overdue';
                else if (dateISO) {
                    const d = new Date(dateISO + 'T00:00:00');
                    sectionLabel = d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
                }
                html += `<div class="agenda-section-divider">${sectionLabel}</div>`;
            }

            // Client badge
            let clientText = props.client || 'Client';
            if (props.clientEmoji) clientText = `${props.clientEmoji} ${clientText}`;

            html += `
                <a href="${props.viewUrl || `/designer/tasks/${props.taskId}`}" class="agenda-row">
                    <div class="agenda-left">
                        <span class="agenda-dot" style="background:${dotColor}"></span>
                        <div class="agenda-title" title="${e.title}">
                            ${e.title}
                        </div>
                        <div class="agenda-client">
                            ${clientText}
                        </div>
                    </div>

                    <div class="agenda-right">
                        ${isUrgent ? '<span class="tag tag-red" style="font-size:10px;padding:1px 6px"><i class="fa-solid fa-bolt"></i> Urgent</span>' : ''}
                        <span class="agenda-date ${isOverdue ? 'is-overdue' : ''}">
                            <i class="fa-regular fa-calendar" style="font-size:11px"></i>
                            ${dateText}
                        </span>
                        <i class="fa-solid fa-chevron-right" style="font-size:10px;color:var(--dz-text-muted)"></i>
                    </div>
                </a>
            `;
        });

        container.innerHTML = html;
    }

    // Controls
    document.getElementById('miniPrevBtn')?.addEventListener('click', () => {
        displayMonth.setMonth(displayMonth.getMonth() - 1);
        renderMiniCalendar();
    });
    document.getElementById('miniNextBtn')?.addEventListener('click', () => {
        displayMonth.setMonth(displayMonth.getMonth() + 1);
        renderMiniCalendar();
    });

    document.getElementById('filterTodayBtn')?.addEventListener('click', function() {
        const todayISO = new Date().toISOString().split('T')[0];
        selectedDateFilter = todayISO;
        displayMonth = new Date();
        document.getElementById('filterTodayBtn').classList.add('active');
        document.getElementById('filterAllBtn').classList.remove('active');
        renderMiniCalendar();
        renderAgendaList();
    });

    document.getElementById('filterAllBtn')?.addEventListener('click', function() {
        selectedDateFilter = null;
        document.getElementById('filterAllBtn').classList.add('active');
        document.getElementById('filterTodayBtn').classList.remove('active');
        renderMiniCalendar();
        renderAgendaList();
    });

    if (clientFilterEl) {
        clientFilterEl.addEventListener('change', loadEvents);
    }

    // Initial Load
    loadEvents();
});
</script>
@endsection
