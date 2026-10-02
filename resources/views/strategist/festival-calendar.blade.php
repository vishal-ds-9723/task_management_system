@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════════════════
   Festival Calendar — Premium Glassmorphism Theme
   Designed for TimeFrame Dashboard
═══════════════════════════════════════════════════════════ */
:root {
    --primary-rgb: 239, 68, 68;
    --primary: #EF4444;
    --primary-light: #F87171;
    --primary-dark: #DC2626;
    --primary-dim: rgba(239, 68, 68, 0.08);
    
    --bg-main: #f8fafc;
    --card: rgba(255, 255, 255, 0.85);
    --card-hover: rgba(255, 255, 255, 0.95);
    --border: rgba(226, 232, 240, 0.7);
    --border-rich: rgba(226, 232, 240, 1);
    
    --text-main: #1e293b;
    --text-muted: #64748b;
    --text-dim: #94a3b8;
    
    --glass-bg: rgba(255, 255, 255, 0.7);
    --glass-border: rgba(255, 255, 255, 0.4);
    --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    
    --radius-sm: 10px;
    --radius-md: 16px;
    --radius-lg: 24px;
    
    --font-main: 'Outfit', 'Inter', -apple-system, sans-serif;
}

@keyframes fadeInRise {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInRight {
    from { opacity: 0; transform: translateX(30px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes glassIn {
    from { opacity: 0; backdrop-filter: blur(0px); }
    to { opacity: 1; backdrop-filter: blur(12px); }
}

/* ─── Global Base ─── */
#layout-wrapper { background: var(--bg-main); }

.content-page {
    background-image: 
        radial-gradient(at 0% 0%, rgba(239, 68, 68, 0.04) 0px, transparent 50%),
        radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.04) 0px, transparent 50%);
    min-height: 100vh;
}

/* ─── Dashboard Header ─── */
.fc-dashboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    gap: 20px;
    flex-wrap: wrap;
    animation: fadeInRise 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.fc-dh-left { display: flex; flex-direction: column; gap: 4px; }

.fc-dh-title {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 14px;
    letter-spacing: -0.02em;
    font-family: var(--font-main);
}

.fc-dh-title-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 10px 20px -5px rgba(var(--primary-rgb), 0.4);
}

.fc-dh-sub { 
    font-size: 14px; 
    color: var(--text-muted); 
    font-weight: 500; 
    padding-left: 2px;
}

/* Stats Section */
.fc-dh-stats { 
    display: flex; 
    gap: 12px; 
    align-items: center; 
    flex-wrap: wrap; 
}

.fc-stat-chip {
    display: flex;
    flex-direction: column;
    padding: 12px 20px;
    background: var(--card);
    backdrop-filter: blur(8px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-md);
    cursor: default;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    min-width: 100px;
    box-shadow: var(--glass-shadow);
}

.fc-stat-chip:hover { 
    transform: translateY(-4px); 
    background: var(--card-hover);
    box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.12);
}

.fc-stat-chip-val { 
    font-size: 24px; 
    font-weight: 800; 
    line-height: 1; 
    letter-spacing: -0.03em;
}

.fc-stat-chip-lbl { 
    font-size: 10px; 
    color: var(--text-dim); 
    font-weight: 700; 
    text-transform: uppercase; 
    letter-spacing: 0.1em; 
    margin-top: 6px; 
}

/* Header Actions */
.fc-dh-actions { 
    display: flex; 
    gap: 10px; 
    align-items: center; 
    flex-wrap: wrap; 
}

.fc-view-tabs {
    display: flex;
    background: rgba(15, 23, 42, 0.04);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 4px;
    gap: 4px;
    border: 1px solid rgba(15, 23, 42, 0.05);
}

.fc-view-tab {
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 700;
    border: none;
    background: transparent;
    color: var(--text-muted);
    border-radius: 9px;
    cursor: pointer;
    transition: all 0.25s ease;
    font-family: inherit;
}

.fc-view-tab.active { 
    background: #fff; 
    color: var(--primary); 
    box-shadow: 0 4px 12px rgba(0,0,0,0.06); 
}

.fc-view-tab:hover:not(.active) { 
    color: var(--text-main); 
    background: rgba(255, 255, 255, 0.6); 
}

/* ─── Main Layout ─── */
.fc-dashboard-body {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 24px;
    align-items: start;
    animation: fadeInRise 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
}

/* ─── Sidebar ─── */
.fc-sidebar { display: flex; flex-direction: column; gap: 20px; }

.fc-sb-card {
    background: var(--card);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--glass-shadow);
    transition: all 0.3s ease;
}

.fc-sb-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 20px 0;
}

.fc-sb-card-title {
    font-size: 13px;
    font-weight: 800;
    color: var(--text-main);
    display: flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.fc-sb-card-title i { color: var(--primary); font-size: 14px; }

.fc-sb-card-action {
    font-size: 11px;
    font-weight: 700;
    color: var(--primary);
    cursor: pointer;
    padding: 4px 10px;
    border-radius: 8px;
    border: none;
    background: var(--primary-dim);
    transition: all 0.2s ease;
    font-family: inherit;
}

.fc-sb-card-action:hover { background: var(--primary); color: #fff; }

/* Sidebar Search */
.fc-sb-search {
    margin: 16px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(15, 23, 42, 0.03);
    border: 1.5px solid transparent;
    border-radius: 14px;
    padding: 10px 16px;
    transition: all 0.3s ease;
}

.fc-sb-search:focus-within { 
    border-color: var(--primary-light); 
    background: #fff; 
    box-shadow: 0 0 0 4px var(--primary-dim); 
}

.fc-sb-search i { color: var(--text-dim); font-size: 14px; flex-shrink: 0; }

.fc-sb-search input {
    border: none;
    background: transparent;
    color: var(--text-main);
    font-size: 13px;
    font-weight: 600;
    width: 100%;
    font-family: inherit;
}

.fc-sb-search input::placeholder { color: var(--text-dim); font-weight: 500; }
.fc-sb-search input:focus { outline: none; }

/* Category Filter List */
.fc-cat-list { padding: 0 12px 16px; }

.fc-cat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 4px;
    border: 1px solid transparent;
}

.fc-cat-item:hover { 
    background: rgba(15, 23, 42, 0.03); 
    transform: translateX(4px);
}

.fc-cat-item.active { 
    background: var(--primary-dim); 
    border-color: rgba(239, 68, 68, 0.15);
}

.fc-cat-item-dot { 
    width: 10px; 
    height: 10px; 
    border-radius: 4px; 
    flex-shrink: 0; 
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.fc-cat-item-name { 
    font-size: 13px; 
    font-weight: 700; 
    color: var(--text-muted); 
    flex: 1; 
}

.fc-cat-item.active .fc-cat-item-name { color: var(--primary); }

.fc-cat-item-count {
    font-size: 11px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 6px;
    background: rgba(15, 23, 42, 0.05);
    color: var(--text-muted);
}

.fc-cat-item.active .fc-cat-item-count { 
    background: var(--primary); 
    color: #fff; 
}

/* Festival Checklist */
.fc-checklist { 
    padding: 0 12px 16px; 
    max-height: 380px; 
    overflow-y: auto; 
    scrollbar-width: thin; 
    scrollbar-color: var(--primary-dim) transparent; 
}

.fc-checklist::-webkit-scrollbar { width: 4px; }
.fc-checklist::-webkit-scrollbar-thumb { background: var(--primary-dim); border-radius: 4px; }

.fc-cl-date-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 8px 6px;
    font-size: 10px;
    font-weight: 800;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.fc-cl-date-sep { flex: 1; height: 1.5px; background: rgba(15, 23, 42, 0.04); }

.fc-cl-date-today-tag { 
    font-size: 9px; 
    font-weight: 800; 
    color: #fff; 
    background: var(--primary); 
    padding: 2px 8px; 
    border-radius: 6px; 
}

.fc-cl-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    margin-bottom: 6px;
    border: 1px solid transparent;
}

.fc-cl-item:hover { 
    background: #fff; 
    border-color: var(--border);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transform: translateY(-2px);
}

.fc-cl-dot-wrap {
    width: 20px;
    height: 20px;
    border-radius: 7px;
    border: 2px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.fc-cl-item:hover .fc-cl-dot-wrap { border-color: var(--primary); }

.fc-cl-dot { width: 8px; height: 8px; border-radius: 2.5px; }

.fc-cl-info { flex: 1; min-width: 0; }

.fc-cl-name { 
    font-size: 13px; 
    font-weight: 700; 
    color: var(--text-main); 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
}

.fc-cl-name.inactive { text-decoration: line-through; color: var(--text-dim); }

.fc-cl-date-lbl { 
    font-size: 11px; 
    color: var(--text-muted); 
    font-weight: 600; 
    margin-top: 1px; 
}

.fc-cl-emoji { font-size: 16px; flex-shrink: 0; line-height: 1; }

/* Category Breakdown */
.fc-cat-breakdown { padding: 4px 20px 20px; }

.fc-cat-bar-item { margin-bottom: 14px; }
.fc-cat-bar-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.fc-cat-bar-name { font-size: 11px; font-weight: 700; color: var(--text-muted); }
.fc-cat-bar-num  { font-size: 11px; font-weight: 800; color: var(--text-main); }

.fc-cat-bar-track { 
    height: 6px; 
    background: rgba(15, 23, 42, 0.03); 
    border-radius: 10px; 
    overflow: hidden; 
}

.fc-cat-bar-fill  { 
    height: 100%; 
    border-radius: 10px; 
    transition: width 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); 
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* ─── Calendar Area ─── */
.fc-cal-area {
    background: var(--card);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--glass-shadow);
    min-width: 0;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.fc-cal-branding {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
}

.fc-cal-logo {
    height: 40px;
    width: auto;
    object-fit: contain;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.05));
}

.fc-cal-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 16px;
}

.fc-cal-top-left { display: flex; align-items: center; gap: 16px; }

.fc-cal-today-btn {
    padding: 8px 16px;
    border-radius: 10px;
    border: 1.5px solid var(--border);
    background: #fff;
    color: var(--text-muted);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: inherit;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.fc-cal-today-btn:hover { 
    border-color: var(--primary); 
    color: var(--primary); 
    background: var(--primary-dim); 
    transform: translateY(-1px);
}

.fc-cal-month-label { 
    font-size: 20px; 
    font-weight: 800; 
    color: var(--text-main); 
    letter-spacing: -0.02em; 
}

.fc-cal-nav { display: flex; gap: 6px; }

.fc-cal-nav button {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1.5px solid var(--border);
    background: #fff;
    color: var(--text-muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s ease;
}

.fc-cal-nav button:hover { 
    border-color: var(--primary); 
    color: var(--primary); 
    background: var(--primary-dim); 
    transform: translateY(-1px);
}

/* FullCalendar Customizations */
#sfcCal .fc-col-header-cell {
    background: rgba(15, 23, 42, 0.02);
    padding: 16px 8px;
    font-size: 11px;
    font-weight: 800;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border-color: var(--border) !important;
}

#sfcCal .fc-daygrid-day {
    border-color: var(--border) !important;
    transition: all 0.2s ease;
}

#sfcCal .fc-daygrid-day:hover { background: rgba(var(--primary-rgb), 0.02); }
#sfcCal .fc-daygrid-day.fc-day-today { background: rgba(var(--primary-rgb), 0.04) !important; }

#sfcCal .fc-daygrid-day-number { 
    font-size: 13px; 
    font-weight: 700; 
    color: var(--text-muted); 
    padding: 10px; 
}

#sfcCal .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
    background: var(--primary);
    color: #fff;
    border-radius: 8px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin: 8px;
    font-weight: 800;
    box-shadow: 0 4px 10px rgba(var(--primary-rgb), 0.3);
}

#sfcCal .fc-event {
    border: none !important;
    border-radius: 8px !important;
    padding: 6px 10px !important;
    margin: 2px 4px !important;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

#sfcCal .fc-event:hover { 
    transform: scale(1.04) translateY(-2px); 
    box-shadow: 0 8px 20px rgba(0,0,0,0.15); 
    z-index: 5;
}

#sfcCal .fc-event-title { font-size: 11px; font-weight: 800; letter-spacing: 0.01em; }

#sfcCal .festival-event { 
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%) !important; 
}

#sfcCal .festival-event.religious     { background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%) !important; }
#sfcCal .festival-event.national      { background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%) !important; }
#sfcCal .festival-event.international { background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%) !important; }
#sfcCal .festival-event.awareness     { background: linear-gradient(135deg, #059669 0%, #34d399 100%) !important; }
#sfcCal .festival-event.cultural      { background: linear-gradient(135deg, #be185d 0%, #f472b6 100%) !important; }
#sfcCal .festival-event.inactive      { opacity: 0.4; filter: grayscale(80%); }

/* ─── Popover ─── */
.fc-festival-popover {
    position: fixed;
    z-index: 10000;
    width: 320px;
    max-width: 93vw;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    animation: glassIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.fc-fp-header { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 20px; }

.fc-fp-emoji-box { 
    width: 52px; 
    height: 52px; 
    border-radius: 16px; 
    flex-shrink: 0; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 26px; 
}

.fc-fp-name { 
    font-size: 18px; 
    font-weight: 800; 
    color: var(--text-main); 
    line-height: 1.2; 
    margin-bottom: 6px; 
}

.fc-fp-tags { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }

.fc-fp-row { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    font-size: 13px; 
    color: var(--text-muted); 
    font-weight: 600; 
    margin-bottom: 10px; 
}

.fc-fp-row i { 
    width: 18px; 
    text-align: center; 
    font-size: 14px; 
    color: var(--primary); 
}

.fc-fp-desc { 
    font-size: 12px; 
    color: var(--text-muted); 
    line-height: 1.6; 
    margin-bottom: 18px; 
    padding: 12px; 
    background: rgba(15, 23, 42, 0.04); 
    border-radius: 12px; 
    font-style: italic;
}

.fc-fp-actions { 
    display: flex; 
    gap: 8px; 
    padding-top: 16px; 
    border-top: 1px solid var(--border); 
}

.fc-fp-btn {
    flex: 1;
    padding: 10px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    border-radius: 10px;
    border: 1.5px solid var(--border);
    background: #fff;
    color: var(--text-main);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s ease;
    font-family: inherit;
}

.fc-fp-btn:hover { 
    background: var(--primary); 
    border-color: var(--primary); 
    color: #fff; 
    transform: translateY(-2px); 
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.2);
}

.fc-fp-btn.danger:hover { 
    background: #ef4444; 
    border-color: #ef4444; 
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

.fc-fp-inactive-badge { 
    font-size: 9px; 
    padding: 3px 8px; 
    border-radius: 6px; 
    background: rgba(239, 68, 68, 0.1); 
    color: #ef4444; 
    font-weight: 800; 
}

/* Category Badges */
.cat-badge { 
    display: inline-flex; 
    align-items: center; 
    padding: 3px 10px; 
    border-radius: 7px; 
    font-size: 10px; 
    font-weight: 800; 
    text-transform: uppercase; 
    letter-spacing: 0.05em; 
}

.cat-badge.religious     { background: rgba(124, 58, 237, 0.1); color: #7c3aed; }
.cat-badge.national      { background: rgba(245, 158, 11, 0.1); color: #b45309; }
.cat-badge.international { background: rgba(14, 165, 233, 0.1); color: #0284c7; }
.cat-badge.awareness     { background: rgba(16, 185, 129, 0.1); color: #059669; }
.cat-badge.cultural      { background: rgba(236, 72, 153, 0.1); color: #be185d; }

/* ─── Modals ─── */
.fc-modal-overlay { 
    position: fixed; 
    inset: 0; 
    background: rgba(15, 23, 42, 0.4); 
    z-index: 9998; 
    display: none; 
    align-items: center; 
    justify-content: center; 
    backdrop-filter: blur(12px); 
    transition: all 0.3s ease;
}

.fc-modal-overlay.open { display: flex; animation: glassIn 0.4s ease; }

.fc-modal-box {
    background: var(--card);
    backdrop-filter: blur(24px);
    border-radius: var(--radius-lg);
    padding: 32px 32px 28px;
    width: 600px;
    max-width: 95vw;
    max-height: 92vh;
    overflow-y: auto;
    border: 1px solid var(--glass-border);
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.2);
    transform: translateY(0);
}

/* ─── Live preview card at the top of the festival modal ─── */
.sf-preview-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    margin-bottom: 22px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(99,102,241,0.02));
    border: 1px solid rgba(99,102,241,0.18);
    transition: border-color .2s, background .2s;
}
.sf-preview-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: rgba(255,255,255,.7);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(99,102,241,0.15);
}
.sf-preview-body { flex: 1; min-width: 0; }
.sf-preview-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.sf-preview-name {
    font-size: 16px;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -.2px;
}
.sf-preview-cat {
    font-size: 9.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: 2px 9px;
    border-radius: 99px;
    background: rgba(15,23,42,.08);
    color: var(--text-muted);
}
.sf-preview-cat[data-cat="religious"]    { background: rgba(217,70,239,.12);  color: #c026d3; }
.sf-preview-cat[data-cat="national"]     { background: rgba(239,68,68,.12);   color: #dc2626; }
.sf-preview-cat[data-cat="international"]{ background: rgba(59,130,246,.12);  color: #2563eb; }
.sf-preview-cat[data-cat="awareness"]    { background: rgba(16,185,129,.12);  color: #059669; }
.sf-preview-cat[data-cat="cultural"]     { background: rgba(245,158,11,.12);  color: #d97706; }
.sf-preview-date {
    font-size: 11.5px;
    color: var(--text-dim);
    margin-top: 3px;
}
.sf-preview-desc {
    font-size: 11.5px;
    color: var(--text-dim);
    margin-top: 4px;
    line-height: 1.5;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ─── Emoji picker ─── */
.sf-emoji-search-wrap {
    position: relative;
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
}
.sf-emoji-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-dim);
    font-size: 12px;
    pointer-events: none;
}
.sf-emoji-search {
    flex: 1;
    padding: 9px 12px 9px 34px;
    border: 1px solid var(--glass-border);
    border-radius: 10px;
    font-size: 13px;
    background: var(--card2, rgba(15,23,42,0.03));
    color: var(--text-main);
    outline: none;
    transition: border-color .15s;
    font-family: inherit;
}
.sf-emoji-search:focus { border-color: var(--primary); }
.sf-emoji-custom {
    flex-shrink: 0;
}
.sf-emoji-custom-input {
    width: 130px;
    padding: 9px 12px;
    border: 1px dashed var(--glass-border);
    border-radius: 10px;
    font-size: 13px;
    background: var(--card2, rgba(15,23,42,0.03));
    outline: none;
    font-family: inherit;
    text-align: center;
}
.sf-emoji-custom-input:focus { border-color: var(--primary); border-style: solid; }

.sf-emoji-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-bottom: 8px;
}
.sf-emoji-tab {
    padding: 6px 10px;
    border: 1px solid var(--glass-border);
    background: transparent;
    color: var(--text-muted);
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    font-family: inherit;
    white-space: nowrap;
}
.sf-emoji-tab:hover {
    background: rgba(99,102,241,.06);
    border-color: rgba(99,102,241,.25);
    color: var(--text-main);
}
.sf-emoji-tab.active {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}

.sf-emoji-grid {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 4px;
    max-height: 180px;
    overflow-y: auto;
    padding: 8px;
    border: 1px solid var(--glass-border);
    border-radius: 10px;
    background: var(--card2, rgba(15,23,42,0.03));
}
.sf-emoji-cell {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid transparent;
    background: transparent;
    border-radius: 8px;
    font-size: 20px;
    cursor: pointer;
    transition: transform .12s cubic-bezier(.22,1,.36,1), background .12s, border-color .12s;
    line-height: 1;
}
.sf-emoji-cell:hover {
    background: rgba(255,255,255,.8);
    transform: scale(1.12);
    border-color: rgba(99,102,241,.3);
}
.sf-emoji-cell.selected {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
    transform: scale(1.06);
    box-shadow: 0 4px 12px rgba(99,102,241,.4);
}
.sf-emoji-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 18px;
    color: var(--text-dim);
    font-size: 12px;
    font-style: italic;
}

@media (max-width: 600px) {
    .sf-emoji-search-wrap { flex-direction: column; }
    .sf-emoji-custom-input { width: 100%; }
    .sf-emoji-grid { grid-template-columns: repeat(8, 1fr); }
}

.fc-modal-title { 
    font-size: 20px; 
    font-weight: 800; 
    color: var(--text-main); 
    margin-bottom: 30px; 
    display: flex; 
    align-items: center; 
    gap: 16px; 
}

.fc-modal-icon {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 8px 16px rgba(var(--primary-rgb), 0.3);
}

.fc-field { margin-bottom: 20px; }

.fc-label { 
    font-size: 11px; 
    font-weight: 800; 
    color: var(--text-muted); 
    text-transform: uppercase; 
    letter-spacing: 0.08em; 
    margin-bottom: 10px; 
    display: block; 
}

.fc-input {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    font-size: 14px;
    color: var(--text-main);
    background: rgba(15, 23, 42, 0.02);
    font-family: inherit;
    box-sizing: border-box;
    transition: all 0.3s ease;
    font-weight: 600;
}

.fc-input:focus { 
    outline: none; 
    border-color: var(--primary); 
    background: #fff; 
    box-shadow: 0 0 0 4px var(--primary-dim); 
}

.fc-input-valid {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12) !important;
    background: #fff;
}

.fc-input-invalid {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
    background: #fff;
}

.fc-validation-msg {
    display: none;
    margin-top: 8px;
    font-size: 11px;
    font-weight: 700;
    color: #10b981;
    align-items: center;
    gap: 6px;
}

.fc-validation-msg.show {
    display: inline-flex;
}

.fc-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.fc-modal-footer { 
    display: flex; 
    gap: 12px; 
    justify-content: flex-end; 
    margin-top: 32px; 
    padding-top: 24px; 
    border-top: 1px solid var(--border); 
}

/* ─── Buttons ─── */
.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff !important;
    border: none;
    border-radius: 12px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    font-family: inherit;
    box-shadow: 0 10px 20px -5px rgba(var(--primary-rgb), 0.4);
}

.btn-primary:hover { 
    transform: translateY(-3px) scale(1.02); 
    box-shadow: 0 15px 30px -5px rgba(var(--primary-rgb), 0.5);
}

.btn-sec {
    background: #fff;
    color: var(--text-main);
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    font-family: inherit;
}

.btn-sec:hover { 
    border-color: var(--primary); 
    color: var(--primary); 
    background: var(--primary-dim); 
}

.btn-icon {
    background: #fff;
    color: var(--text-muted);
    border: 1.5px solid var(--border);
    border-radius: 12px;
    padding: 11px 18px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-icon:hover { 
    border-color: var(--primary); 
    color: var(--primary); 
    background: var(--primary-dim);
}

/* ─── Toast ─── */
.fc-toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 100000;
    padding: 16px 24px;
    border-radius: 16px;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Empty State */
.fc-empty {
    background: var(--card);
    backdrop-filter: blur(12px);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    padding: 80px 40px;
    text-align: center;
    box-shadow: var(--glass-shadow);
    animation: fadeInRise 0.8s ease;
}

.fc-empty-icon {
    font-size: 64px;
    color: var(--primary);
    margin-bottom: 24px;
    opacity: 0.2;
}

.fc-empty-title {
    font-size: 22px;
    font-weight: 800;
    color: var(--text-main);
    margin-bottom: 12px;
}

.fc-empty-sub {
    font-size: 15px;
    color: var(--text-muted);
    max-width: 400px;
    margin: 0 auto 32px;
    line-height: 1.6;
}

/* ─── Responsive ─── */
@media(max-width: 1200px) {
    .fc-dashboard-body { grid-template-columns: 260px 1fr; gap: 16px; }
}

@media(max-width: 992px) {
    .fc-dashboard-body { grid-template-columns: 1fr; }
    .fc-sidebar { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
}

@media(max-width: 640px) {
    .fc-sidebar { grid-template-columns: 1fr; }
    .fc-dh-stats { justify-content: center; width: 100%; }
    .fc-stat-chip { flex: 1; min-width: 120px; }
    .fc-grid-2 { grid-template-columns: 1fr; }
    .fc-dashboard-header { flex-direction: column; align-items: stretch; text-align: center; }
    .fc-dh-left { align-items: center; }
    .fc-dh-actions { justify-content: center; }
}
</style>
@endpush

@section('content')

{{-- DASHBOARD HEADER --}}
<div class="fc-dashboard-header">
    <div class="fc-dh-left">
        <div class="fc-dh-title">
            <div class="fc-dh-title-icon"><i class="fa-solid fa-calendar-star"></i></div>
            Festival Calendar
        </div>
        <div class="fc-dh-sub">{{ $monthLabel }} — Manage and publish festivals for clients</div>
    </div>
    <div class="fc-dh-stats">
        <div class="fc-stat-chip">
            <div class="fc-stat-chip-val" style="color:var(--primary)">{{ $festivals->count() }}</div>
            <div class="fc-stat-chip-lbl">Total</div>
        </div>
        <div class="fc-stat-chip">
            <div class="fc-stat-chip-val" style="color:#10b981">{{ $festivals->where('is_active', true)->count() }}</div>
            <div class="fc-stat-chip-lbl">Active</div>
        </div>
        <div class="fc-stat-chip">
            <div class="fc-stat-chip-val" style="color:#f59e0b">{{ $totalSelections }}</div>
            <div class="fc-stat-chip-lbl">Selections</div>
        </div>
        <div class="fc-stat-chip">
            <div class="fc-stat-chip-val" style="color:#6366f1">{{ $clients->count() }}</div>
            <div class="fc-stat-chip-lbl">Clients</div>
        </div>
    </div>
    <div class="fc-dh-actions">
        <div class="fc-view-tabs">
            <button class="fc-view-tab active" onclick="setView('dayGridMonth',this)">Month</button>
            <button class="fc-view-tab" onclick="setView('dayGridWeek',this)">Week</button>
            <button class="fc-view-tab" onclick="setView('listMonth',this)">List</button>
        </div>

        <button type="button" class="btn-sec" onclick="shareFestivalCalendarAsImage()" title="Export and Share Festival Calendar as Image">
            <i class="fa-solid fa-share-nodes" style="color:var(--primary)"></i> Share as Image
        </button>

        <input type="file" id="importFile" accept=".xlsx,.xls,.csv" style="display:none" onchange="uploadImportFile(this.files)">
        <button class="btn-primary" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Add Festival</button>
    </div>
</div>

{{-- DASHBOARD BODY --}}
<div class="fc-dashboard-body">

    {{-- LEFT SIDEBAR --}}
    <div class="fc-sidebar">

        {{-- Search + Category filters --}}
        <div class="fc-sb-card">
            <div class="fc-sb-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="searchFestivals" placeholder="Search events..." oninput="applyFilters()">
            </div>
            <div class="fc-sb-card-head" style="padding-bottom:12px">
                <div class="fc-sb-card-title"><i class="fa-solid fa-layer-group"></i> Categories</div>
            </div>
            <div class="fc-cat-list">
                <div class="fc-cat-item active" data-cat="" onclick="setCatFilter(this)">
                    <div class="fc-cat-item-dot" style="background:var(--primary)"></div>
                    <span class="fc-cat-item-name">All Events</span>
                    <span class="fc-cat-item-count" id="cnt-all">{{ $festivals->count() }}</span>
                </div>
                <div class="fc-cat-item" data-cat="religious" onclick="setCatFilter(this)">
                    <div class="fc-cat-item-dot" style="background:#7c3aed"></div>
                    <span class="fc-cat-item-name">Religious</span>
                    <span class="fc-cat-item-count" id="cnt-religious">0</span>
                </div>
                <div class="fc-cat-item" data-cat="national" onclick="setCatFilter(this)">
                    <div class="fc-cat-item-dot" style="background:#b45309"></div>
                    <span class="fc-cat-item-name">National</span>
                    <span class="fc-cat-item-count" id="cnt-national">0</span>
                </div>
                <div class="fc-cat-item" data-cat="international" onclick="setCatFilter(this)">
                    <div class="fc-cat-item-dot" style="background:#0284c7"></div>
                    <span class="fc-cat-item-name">International</span>
                    <span class="fc-cat-item-count" id="cnt-international">0</span>
                </div>
                <div class="fc-cat-item" data-cat="awareness" onclick="setCatFilter(this)">
                    <div class="fc-cat-item-dot" style="background:#059669"></div>
                    <span class="fc-cat-item-name">Awareness</span>
                    <span class="fc-cat-item-count" id="cnt-awareness">0</span>
                </div>
                <div class="fc-cat-item" data-cat="cultural" onclick="setCatFilter(this)">
                    <div class="fc-cat-item-dot" style="background:#be185d"></div>
                    <span class="fc-cat-item-name">Cultural</span>
                    <span class="fc-cat-item-count" id="cnt-cultural">0</span>
                </div>
            </div>
        </div>

        {{-- Festival Checklist --}}
        <div class="fc-sb-card">
            <div class="fc-sb-card-head" style="padding-bottom:12px">
                <div class="fc-sb-card-title"><i class="fa-solid fa-calendar-check"></i> Upcoming</div>
                <div style="display:flex;align-items:center;gap:8px">
                    <button class="fc-sb-card-action" onclick="goToToday()">Today</button>
                    <button class="fc-sb-card-action" onclick="openAddModal()">Add New</button>
                </div>
            </div>
            <div class="fc-checklist" id="sidebarChecklist">
                <div style="text-align:center;padding:32px 16px;color:var(--text-dim);font-size:13px;font-weight:600">
                    <i class="fa-solid fa-circle-notch fa-spin" style="display:block;font-size:24px;margin-bottom:12px;opacity:0.5"></i>
                    Syncing calendar...
                </div>
            </div>
        </div>

        {{-- Category Breakdown --}}
        <div class="fc-sb-card">
            <div class="fc-sb-card-head" style="padding-bottom:14px">
                <div class="fc-sb-card-title"><i class="fa-solid fa-chart-pie"></i> Distribution</div>
            </div>
            <div class="fc-cat-breakdown">
                @php
                    $catData = [
                        'religious'     => ['color'=>'#7c3aed','count'=>$festivals->where('category','religious')->count()],
                        'national'      => ['color'=>'#b45309','count'=>$festivals->where('category','national')->count()],
                        'international' => ['color'=>'#0284c7','count'=>$festivals->where('category','international')->count()],
                        'awareness'     => ['color'=>'#059669','count'=>$festivals->where('category','awareness')->count()],
                        'cultural'      => ['color'=>'#be185d','count'=>$festivals->where('category','cultural')->count()],
                    ];
                    $maxCat = max(array_column($catData,'count') ?: [1]);
                @endphp
                @foreach($catData as $catName => $catInfo)
                <div class="fc-cat-bar-item">
                    <div class="fc-cat-bar-row">
                        <span class="fc-cat-bar-name" style="color:{{ $catInfo['color'] }}">{{ ucfirst($catName) }}</span>
                        <span class="fc-cat-bar-num">{{ $catInfo['count'] }}</span>
                    </div>
                    <div class="fc-cat-bar-track">
                        <div class="fc-cat-bar-fill" style="background:{{ $catInfo['color'] }};width:{{ $maxCat > 0 ? round($catInfo['count']/$maxCat*100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- MAIN CALENDAR --}}
    @if($festivals->isEmpty())
    <div class="fc-empty">
        <i class="fa-solid fa-calendar-plus fc-empty-icon" style="opacity:0.05"></i>
        <div class="fc-empty-title">No festivals scheduled for {{ $monthLabel }}</div>
        <div class="fc-empty-sub">Populate your calendar with events so clients can select them for their content strategy.</div>
        <div style="display:flex;gap:12px;justify-content:center;margin-top:24px">
            <button class="btn-sec" onclick="document.getElementById('importFile').click()"><i class="fa-solid fa-file-import"></i> Import List</button>
            <button class="btn-primary" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Create Festival</button>
        </div>
    </div>
    @else
    <div class="fc-cal-area">
        <div class="fc-cal-branding">
            <img src="{{ asset('images/logo.png') }}" alt="TimeFrame Logo" class="fc-cal-logo">
        </div>
        <div class="fc-cal-top">
            <div class="fc-cal-top-left">
                <div class="fc-cal-nav">
                    <button onclick="calNav('prev')" title="Previous Month"><i class="fa-solid fa-chevron-left"></i></button>
                    <button onclick="calNav('next')" title="Next Month"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
                <div class="fc-cal-month-label" id="calMonthLabel">{{ $monthLabel }}</div>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <button type="button" class="fc-cal-today-btn" onclick="shareFestivalCalendarAsImage()" title="Share Calendar Image">
                    <i class="fa-solid fa-camera" style="color:var(--primary)"></i> Share Image
                </button>
                <button class="fc-cal-today-btn" onclick="goToToday()">
                    <i class="fa-solid fa-bullseye"></i> Today
                </button>
            </div>
        </div>
        <div id="sfcCal"></div>
    </div>
    @endif

</div>

{{-- Add / Edit Modal --}}
<div class="fc-modal-overlay" id="sfModal">
    <div class="fc-modal-box">
        <input type="hidden" id="sfId">
        <div class="fc-modal-title" id="sfTitle">
            <div class="fc-modal-icon"><i class="fa-solid fa-calendar-plus"></i></div>
            Create New Festival
        </div>

        {{-- Live preview tile --}}
        <div class="sf-preview-card" id="sfPreviewCard">
            <div class="sf-preview-icon" id="sfPreviewIcon">✨</div>
            <div class="sf-preview-body">
                <div class="sf-preview-row">
                    <span class="sf-preview-name" id="sfPreviewName">Untitled festival</span>
                    <span class="sf-preview-cat" id="sfPreviewCat" data-cat="">Pick a category</span>
                </div>
                <div class="sf-preview-date" id="sfPreviewDate">No date selected</div>
                <div class="sf-preview-desc" id="sfPreviewDesc"></div>
            </div>
        </div>

        <div class="fc-grid-2">
            <div class="fc-field">
                <label class="fc-label">Festival Name</label>
                <input type="text" class="fc-input" id="sfName" placeholder="e.g. Christmas Eve">
                <div id="sfNameOk" class="fc-validation-msg"><i class="fa-solid fa-circle-check"></i> Okay</div>
            </div>
            <div class="fc-field">
                <label class="fc-label">Event Date</label>
                <input type="date" class="fc-input" id="sfDate">
                <div id="sfDateOk" class="fc-validation-msg"><i class="fa-solid fa-circle-check"></i> Okay</div>
            </div>
        </div>

        <div class="fc-field">
            <label class="fc-label">Category</label>
            <select class="fc-input" id="sfCategory">
                <option value="">-- Choose Category --</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
            <div id="sfCategoryOk" class="fc-validation-msg"><i class="fa-solid fa-circle-check"></i> Okay</div>
        </div>

        {{-- Curated emoji picker --}}
        <div class="fc-field">
            <label class="fc-label">Choose an icon</label>

            <div class="sf-emoji-search-wrap">
                <i class="fa-solid fa-magnifying-glass sf-emoji-search-icon"></i>
                <input type="text" class="sf-emoji-search" id="sfEmojiSearch" placeholder="Search icons (e.g. love, fire, food)…">
                <div class="sf-emoji-custom">
                    <input type="text" class="sf-emoji-custom-input" id="sfEmoji" placeholder="or paste custom" maxlength="5" aria-label="Custom emoji">
                </div>
            </div>

            <div class="sf-emoji-tabs" id="sfEmojiTabs">
                <button type="button" class="sf-emoji-tab active" data-group="suggested">⭐ Suggested</button>
                <button type="button" class="sf-emoji-tab" data-group="religious">🕊️ Religious</button>
                <button type="button" class="sf-emoji-tab" data-group="national">🇮🇳 National</button>
                <button type="button" class="sf-emoji-tab" data-group="cultural">🎭 Cultural</button>
                <button type="button" class="sf-emoji-tab" data-group="awareness">💚 Awareness</button>
                <button type="button" class="sf-emoji-tab" data-group="seasonal">❄️ Seasonal</button>
                <button type="button" class="sf-emoji-tab" data-group="food">🍰 Food</button>
                <button type="button" class="sf-emoji-tab" data-group="symbols">✨ Symbols</button>
            </div>

            <div class="sf-emoji-grid" id="sfEmojiGrid">
                {{-- Populated by JS based on the active tab + search filter --}}
            </div>

            <div id="sfEmojiOk" class="fc-validation-msg"><i class="fa-solid fa-circle-check"></i> Okay</div>
        </div>

        <div class="fc-field">
            <label class="fc-label">Description <span style="font-weight:500;color:var(--text-dim);text-transform:none;letter-spacing:0;font-size:10px">(Visible to clients)</span></label>
            <textarea class="fc-input" id="sfDesc" rows="3" placeholder="Add context or details about this festival..."></textarea>
            <div id="sfDescOk" class="fc-validation-msg"><i class="fa-solid fa-circle-check"></i> Okay</div>
        </div>
        <div class="fc-modal-footer">
            <button class="btn-sec" onclick="closeSfModal()">Discard</button>
            <button class="btn-primary" id="sfSaveBtn" onclick="saveFestival()">
                <i class="fa-solid fa-circle-check"></i> Publish Event
            </button>
        </div>
    </div>
</div>

{{-- Template Modal --}}
<div class="fc-modal-overlay" id="templateModal">
    <div class="fc-modal-box" style="width:480px">
        <div class="fc-modal-title">
            <div class="fc-modal-icon"><i class="fa-solid fa-file-csv"></i></div>
            Import Guidelines
        </div>
        <div style="font-size:14px;color:var(--text-muted);line-height:1.6;margin-bottom:20px">
            Use a CSV or XLSX file with the following column headers in the first row:
            <div style="background:rgba(15, 23, 42, 0.04);padding:10px 14px;border-radius:10px;margin:12px 0;font-family:monospace;font-weight:700;color:var(--text-main);font-size:12px">
                name, date, emoji, description, category
            </div>
            <strong>Allowed categories:</strong> religious, national, international, awareness, cultural.
        </div>
        <div class="fc-modal-footer">
            <button class="btn-primary" onclick="closeTemplateModal()">Got it</button>
        </div>
    </div>
</div>

{{-- ============ SHARE AS IMAGE MODAL ============ --}}
<div class="fc-modal-overlay" id="shareCalendarModal">
    <div class="fc-modal-box" style="width:720px;max-width:95vw;padding:24px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
            <div style="font-size:18px;font-weight:800;color:var(--text-main);display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-camera-retro" style="color:var(--primary)"></i>
                <span id="shareModalTitle">Share Festival Calendar</span>
            </div>
            <span onclick="closeShareModal()" style="font-size:24px;cursor:pointer;color:var(--text-dim);line-height:1">&times;</span>
        </div>

        <div id="shareLoadingState" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;gap:12px">
            <i class="fa-solid fa-circle-notch fa-spin" style="font-size:32px;color:var(--primary)"></i>
            <span style="font-size:14px;font-weight:700;color:var(--text-muted)">Generating high-resolution calendar image...</span>
        </div>

        <div id="sharePreviewState" style="display:none">
            <div style="max-height:55vh;overflow-y:auto;border-radius:12px;border:1px solid var(--border);background:#fff;padding:8px;box-shadow:inset 0 2px 8px rgba(0,0,0,0.05);margin-bottom:18px;text-align:center">
                <img id="shareImagePreview" src="" alt="Festival Calendar Preview" style="max-width:100%;height:auto;border-radius:8px;display:inline-block">
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding-top:14px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="closeShareModal()">Close</button>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <button type="button" class="btn-sec" id="btnCopyImage" onclick="copyCalendarImage()">
                        <i class="fa-solid fa-copy"></i> Copy Image
                    </button>
                    <button type="button" class="btn-sec" id="btnNativeShare" onclick="triggerNativeShare()" style="display:none">
                        <i class="fa-solid fa-share-nodes"></i> Share via Apps
                    </button>
                    <button type="button" class="btn-primary" onclick="downloadCalendarImage()">
                        <i class="fa-solid fa-download"></i> Download PNG
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
const csrf     = document.querySelector('meta[name="csrf-token"]').content;
const allFests = @json($festivals);
const isAdmin  = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};
const FEST_URL = isAdmin ? '{{ url("/admin/festivals") }}' : '{{ url("/strategist/festivals") }}';
const API_URL  = isAdmin ? '{{ route("admin.festival-events.api") }}' : '{{ route("strategist.festival-events.api") }}';
let editMode = false, editId = null, cal = null, activeCat = '';

const catColors = {
    religious:'#7c3aed', national:'#b45309', international:'#0284c7',
    awareness:'#059669', cultural:'#be185d'
};

const festivalValidation = {
    name:      { required: true, max: 120, message: 'Festival name is required (max 120 characters).' },
    date:      { required: true,           message: 'Event date is required.' },
    emoji:     { required: false, max: 10, message: 'Emoji must be at most 10 characters.' },
    category:  { required: false, max: 60, message: 'Category must be at most 60 characters.' },
    desc:      { required: false, max: 500, message: 'Description must be at most 500 characters.' }
};

document.addEventListener('DOMContentLoaded', () => {
    attachFestivalValidationListeners();
    initEmojiPicker();
    wireLivePreview();
    const el = document.getElementById('sfcCal');
    if (!el) { initSidebar(); return; }
    cal = new FullCalendar.Calendar(el, {
        initialView: 'dayGridMonth',
        headerToolbar: false,
        height: 'auto',
        contentHeight: 'auto',
        events: API_URL,
        eventClick(info)  { showPopover(info.event, info.jsEvent); },
        dateClick(info)   { document.getElementById('sfDate').value = info.dateStr; openAddModal(); },
        datesSet(info) {
            const lbl = document.getElementById('calMonthLabel');
            if (lbl) lbl.textContent = info.view.currentStart.toLocaleDateString('en', {month:'long', year:'numeric'});
            applyFilters();
        },
        // Richer event tooltip + category-aware tinting
        eventDidMount(info) {
            const p = info.event.extendedProps || {};
            const dateStr = info.event.start
                ? info.event.start.toLocaleDateString('en', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })
                : '';
            const lines = [
                (p.emoji || '') + ' ' + (info.event.title.replace(p.emoji || '', '').trim()),
                dateStr,
                p.category ? ('Category: ' + p.category) : '',
                p.description ? p.description : '',
                p.is_active === false ? '(Inactive)' : '',
            ].filter(Boolean);
            info.el.title = lines.join('\n');
            if (p.category) info.el.setAttribute('data-category', p.category);
        }
    });
    cal.render();
    initSidebar();
});

/* ═════════════════ EMOJI PICKER ═════════════════ */
const SF_EMOJI_GROUPS = {
    suggested: ['✨','🎉','🎊','🪔','🎄','🎁','❤️','🔥','🌟','🙏','🕯️','📅','🎂','🎈','🌸','🌼'],
    religious: ['🕉️','🙏','🪔','📿','🛕','☪️','🕌','✝️','⛪','🕯️','☸️','☯️','✡️','🌙','⭐','🪷','🔱','🛐','🕊️','🌺'],
    national:  ['🇮🇳','🚩','🎌','🪖','🛡️','⚔️','🦅','🏛️','📜','🕊️','🎺','🥁','🎖️','🏵️','👑','🌐','🗽','📯'],
    cultural:  ['🎭','💃','🕺','🪘','🥁','🎺','🎻','🎷','🪕','🎶','🎵','🪗','🎤','🪅','🎨','🖼️','🪕','🎟️','🎫','🎀'],
    awareness: ['💚','💙','💜','🤍','🧡','💛','❤️‍🩹','🎗️','🧠','🌍','🕊️','💪','🌱','♻️','🚭','💧','🩸','🩺','🦻','♿'],
    seasonal:  ['❄️','⛄','☃️','🌨️','🌧️','☔','🌞','🌻','🌷','🌸','🍂','🍁','🌾','🌳','🌴','🌵','🎄','🎃','🦃','🪺'],
    food:      ['🎂','🍰','🧁','🍪','🍩','🍭','🍬','🥮','🍡','🍢','🥘','🍛','🍜','🍲','🥟','🍘','🍙','🍚','🍣','🥗'],
    symbols:   ['✨','⭐','🌟','💫','✴️','❇️','💥','🔥','💎','👑','🎀','🎁','🎉','🎊','🪅','🪩','🏆','🥇','🥈','🥉'],
};
const SF_CATEGORY_SUGGEST = {
    religious: 'religious',
    national: 'national',
    international: 'awareness',
    awareness: 'awareness',
    cultural: 'cultural',
};
let sfActiveEmojiGroup = 'suggested';
let sfEmojiSearchTerm = '';

function initEmojiPicker() {
    const tabs = document.querySelectorAll('#sfEmojiTabs .sf-emoji-tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            sfActiveEmojiGroup = tab.dataset.group;
            renderEmojiGrid();
        });
    });
    const search = document.getElementById('sfEmojiSearch');
    if (search) {
        search.addEventListener('input', (e) => {
            sfEmojiSearchTerm = (e.target.value || '').toLowerCase().trim();
            renderEmojiGrid();
        });
    }
    const custom = document.getElementById('sfEmoji');
    if (custom) {
        custom.addEventListener('input', () => {
            // Sync the picker selection to whatever the user typed
            document.querySelectorAll('#sfEmojiGrid .sf-emoji-cell').forEach(c => c.classList.remove('selected'));
            updatePreview();
        });
    }
    const catSel = document.getElementById('sfCategory');
    if (catSel) {
        catSel.addEventListener('change', () => {
            const group = SF_CATEGORY_SUGGEST[catSel.value] || 'suggested';
            const tab = document.querySelector('#sfEmojiTabs .sf-emoji-tab[data-group="' + group + '"]');
            if (tab) tab.click();
            updatePreview();
        });
    }
    renderEmojiGrid();
}

function renderEmojiGrid() {
    const grid = document.getElementById('sfEmojiGrid');
    if (!grid) return;
    const list = SF_EMOJI_GROUPS[sfActiveEmojiGroup] || [];
    const filtered = sfEmojiSearchTerm
        ? Object.values(SF_EMOJI_GROUPS).flat().filter(e => e.includes(sfEmojiSearchTerm))
        : list;
    if (filtered.length === 0) {
        grid.innerHTML = '<div class="sf-emoji-empty">No icons match "' + sfEmojiSearchTerm + '"</div>';
        return;
    }
    const current = (document.getElementById('sfEmoji')?.value || '').trim();
    grid.innerHTML = filtered.map(e =>
        '<button type="button" class="sf-emoji-cell ' + (e === current ? 'selected' : '') + '" data-emoji="' + e + '">' + e + '</button>'
    ).join('');
    grid.querySelectorAll('.sf-emoji-cell').forEach(cell => {
        cell.addEventListener('click', () => {
            const v = cell.dataset.emoji;
            document.getElementById('sfEmoji').value = v;
            grid.querySelectorAll('.sf-emoji-cell').forEach(c => c.classList.remove('selected'));
            cell.classList.add('selected');
            updatePreview();
            // Trigger validation listener
            document.getElementById('sfEmoji').dispatchEvent(new Event('input'));
        });
    });
}

/* ═════════════════ LIVE PREVIEW ═════════════════ */
function wireLivePreview() {
    ['sfName', 'sfDate', 'sfEmoji', 'sfCategory', 'sfDesc'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        const evt = (el.tagName === 'SELECT' || el.type === 'date') ? 'change' : 'input';
        el.addEventListener(evt, updatePreview);
    });
    updatePreview();
}
function updatePreview() {
    const name  = (document.getElementById('sfName')?.value || '').trim();
    const date  = (document.getElementById('sfDate')?.value || '').trim();
    const emoji = (document.getElementById('sfEmoji')?.value || '').trim();
    const cat   = (document.getElementById('sfCategory')?.value || '').trim();
    const desc  = (document.getElementById('sfDesc')?.value || '').trim();

    document.getElementById('sfPreviewIcon').textContent = emoji || '✨';
    document.getElementById('sfPreviewName').textContent = name || 'Untitled festival';
    const catEl = document.getElementById('sfPreviewCat');
    catEl.textContent = cat ? cat.charAt(0).toUpperCase() + cat.slice(1) : 'Pick a category';
    catEl.setAttribute('data-cat', cat);

    const dateEl = document.getElementById('sfPreviewDate');
    if (date) {
        const d = new Date(date + 'T00:00:00');
        dateEl.textContent = d.toLocaleDateString('en', { weekday:'short', month:'short', day:'numeric', year:'numeric' });
    } else {
        dateEl.textContent = 'No date selected';
    }
    document.getElementById('sfPreviewDesc').textContent = desc;
}

function fieldEl(key) {
    return {
        name: document.getElementById('sfName'),
        date: document.getElementById('sfDate'),
        emoji: document.getElementById('sfEmoji'),
        category: document.getElementById('sfCategory'),
        desc: document.getElementById('sfDesc')
    }[key];
}

function okEl(key) {
    return {
        name: document.getElementById('sfNameOk'),
        date: document.getElementById('sfDateOk'),
        emoji: document.getElementById('sfEmojiOk'),
        category: document.getElementById('sfCategoryOk'),
        desc: document.getElementById('sfDescOk')
    }[key];
}

function setFestivalFieldState(key, isValid, showOkay, forceInvalid) {
    const input = fieldEl(key);
    const okay  = okEl(key);
    if (!input) return;

    input.classList.remove('fc-input-valid', 'fc-input-invalid');
    if (showOkay && isValid) {
        input.classList.add('fc-input-valid');
    } else if (forceInvalid && !isValid) {
        input.classList.add('fc-input-invalid');
    }

    if (okay) {
        okay.classList.toggle('show', Boolean(showOkay && isValid));
    }
}

function validateFestivalField(key, forceInvalid) {
    const cfg = festivalValidation[key];
    const input = fieldEl(key);
    if (!cfg || !input) return { valid: true, showOkay: false };

    const raw = (input.value || '').trim();
    let valid = true;
    let showOkay = false;

    if (cfg.required) {
        valid = raw.length > 0;
        showOkay = valid;
    } else {
        if (raw.length === 0) {
            valid = true;
            showOkay = false;
        } else {
            valid = true;
            showOkay = true;
        }
    }

    if (valid && cfg.max && raw.length > cfg.max) {
        valid = false;
        showOkay = false;
    }

    if (key === 'date' && raw) {
        const parsed = new Date(raw + 'T00:00:00');
        if (Number.isNaN(parsed.getTime())) {
            valid = false;
            showOkay = false;
        }
    }

    setFestivalFieldState(key, valid, showOkay, forceInvalid);
    return { valid, showOkay };
}

function validateFestivalForm(forceInvalid) {
    const order = ['name', 'date', 'emoji', 'category', 'desc'];
    for (const key of order) {
        const res = validateFestivalField(key, forceInvalid);
        if (!res.valid) {
            return { valid: false, key, message: festivalValidation[key].message };
        }
    }
    return { valid: true };
}

function clearFestivalValidationState() {
    ['name', 'date', 'emoji', 'category', 'desc'].forEach((key) => {
        setFestivalFieldState(key, true, false, false);
    });
}

function attachFestivalValidationListeners() {
    const wire = (key, events) => {
        const input = fieldEl(key);
        if (!input) return;
        events.forEach((ev) => {
            input.addEventListener(ev, () => validateFestivalField(key, false));
        });
    };

    wire('name', ['input', 'blur']);
    wire('date', ['change', 'blur']);
    wire('emoji', ['input', 'blur']);
    wire('category', ['change']);
    wire('desc', ['input', 'blur']);
}

function buildEvents() {
    return allFests.map(f => ({
        id: String(f.id),
        title: (f.emoji || '') + ' ' + f.name,
        start: f.date, end: f.date,
        classNames: ['festival-event', f.category || '', f.is_active ? '' : 'inactive'],
        extendedProps: { ...f }
    }));
}

function setView(view, btn) {
    document.querySelectorAll('.fc-view-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    if (cal) cal.changeView(view);
}

function parseFestivalDate(raw) {
    if (!raw) return null;
    if (raw instanceof Date) return Number.isNaN(raw.getTime()) ? null : raw;

    const text = String(raw).trim();
    if (!text) return null;

    const normalized = text.includes('T') ? text : (text + 'T00:00:00');
    const parsed = new Date(normalized);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function goToToday() {
    calNav('today');
    document.querySelectorAll('.fc-festival-popover').forEach(x => x.remove());
    applyFilters();

    const checklist = document.getElementById('sidebarChecklist');
    const tag = checklist?.querySelector('.fc-cl-date-today-tag');
    if (tag) {
        tag.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else if (checklist) {
        checklist.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function calNav(action) {
    if (!cal) return;
    if (action === 'prev')  cal.prev();
    else if (action === 'next')  cal.next();
    else cal.today();
}

function setCatFilter(item) {
    document.querySelectorAll('.fc-cat-item').forEach(i => i.classList.remove('active'));
    item.classList.add('active');
    activeCat = item.dataset.cat;
    applyFilters();
}

function applyFilters() {
    const q = (document.getElementById('searchFestivals')?.value || '').trim().toLowerCase();

    if (cal) {
        cal.getEvents().forEach(ev => {
            const name = (ev.extendedProps.name || '').toLowerCase();
            const cat  = ev.extendedProps.category || '';
            let vis = true;
            if (q && !name.includes(q)) vis = false;
            if (activeCat && cat !== activeCat) vis = false;
            ev.setProp('display', vis ? 'auto' : 'none');
        });
    }

    buildChecklist();
}

function initSidebar() {
    updateCategoryCounts();
    buildChecklist();
}

function updateCategoryCounts() {
    ['religious','national','international','awareness','cultural'].forEach(c => {
        const el = document.getElementById('cnt-' + c);
        if (el) el.textContent = allFests.filter(f => f.category === c).length;
    });
    const allEl = document.getElementById('cnt-all');
    if (allEl) allEl.textContent = allFests.length;
}

function buildChecklist() {
    const today = new Date(); today.setHours(0,0,0,0);
    const q     = (document.getElementById('searchFestivals')?.value || '').trim().toLowerCase();

    let list = allFests.filter(f => {
        const d = parseFestivalDate(f.date);
        if (!d) return false;
        d.setHours(0,0,0,0);
        if (activeCat && f.category !== activeCat) return false;
        if (q && !(f.name||'').toLowerCase().includes(q)) return false;
        return d >= today;
    }).sort((a,b) => {
        const da = parseFestivalDate(a.date);
        const db = parseFestivalDate(b.date);
        return (da ? da.getTime() : 0) - (db ? db.getTime() : 0);
    });

    const el = document.getElementById('sidebarChecklist');
    if (!el) return;

    if (!list.length) {
        el.innerHTML = '<div style="text-align:center;padding:40px 20px;color:var(--text-dim);font-size:13px;font-style:italic"><i class="fa-solid fa-calendar-xmark" style="display:block;font-size:32px;opacity:.1;margin-bottom:12px;color:var(--primary)"></i>No upcoming events</div>';
        return;
    }

    const groups = {};
    list.forEach(f => {
        const d = parseFestivalDate(f.date);
        if (!d) return;
        const key = d.toLocaleDateString('en',{month:'long',year:'numeric'});
        (groups[key] = groups[key] || []).push(f);
    });

    let html = '';
    const curMonth = new Date().toLocaleDateString('en',{month:'long',year:'numeric'});
    Object.entries(groups).forEach(([month, fests]) => {
        const isCurrent = month === curMonth;
        html += '<div class="fc-cl-date-header"><span>' + month + '</span><span class="fc-cl-date-sep"></span>' + (isCurrent ? '<span class="fc-cl-date-today-tag">Current</span>' : '') + '</div>';
        fests.forEach(f => {
            const color   = catColors[f.category] || 'var(--primary)';
            const d       = parseFestivalDate(f.date);
            if (!d) return;
            const isToday = d.toDateString() === today.toDateString();
            const dayStr  = d.toLocaleDateString('en',{weekday:'short',day:'numeric',month:'short'});
            const fJson   = JSON.stringify(f).replace(/\\/g,'\\\\').replace(/'/g, "\\'");
            html += '<div class="fc-cl-item" onclick="showSidebarPopover(' + "'" + fJson + "'" + ')">';
            html += '<div class="fc-cl-dot-wrap"><div class="fc-cl-dot" style="background:' + color + '"></div></div>';
            html += '<div class="fc-cl-info"><div class="fc-cl-name' + (!f.is_active ? ' inactive' : '') + '">' + f.name + '</div>';
            html += '<div class="fc-cl-date-lbl">' + (isToday ? '<strong style="color:var(--primary)">Today</strong>' : dayStr) + '</div></div>';
            html += '<span class="fc-cl-emoji">' + (f.emoji || '') + '</span></div>';
        });
    });
    el.innerHTML = html;
}

function showPopover(event, jsEvent) { showPopoverData(event.extendedProps, event.id, jsEvent); }
function showSidebarPopover(fJson) {
    const f = JSON.parse(fJson);
    const fakeEv = { clientX: window.innerWidth/2, clientY: window.innerHeight/2 };
    showPopoverData(f, f.id, fakeEv);
}
function showPopoverData(p, id, jsEvent) {
    document.querySelectorAll('.fc-festival-popover').forEach(x => x.remove());
    const color = catColors[p.category] || 'var(--primary)';
    const pop = document.createElement('div');
    pop.className = 'fc-festival-popover';
    const name = (p.name||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const desc = (p.description||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const editArgs = [
        id,
        JSON.stringify(p.name||''),
        JSON.stringify(p.date||''),
        JSON.stringify(p.emoji||''),
        JSON.stringify(p.category||''),
        JSON.stringify(p.description||'')
    ].join(',');
    pop.innerHTML =
        '<div class="fc-fp-header">' +
        '<div class="fc-fp-emoji-box" style="background:' + color + '18;border:2px solid ' + color + '22"><span style="font-size:22px">' + (p.emoji||'') + '</span></div>' +
        '<div><div class="fc-fp-name">' + name + '</div><div class="fc-fp-tags">' +
        (p.category ? '<span class="cat-badge ' + p.category + '">' + p.category + '</span>' : '') +
        (!p.is_active ? '<span class="fc-fp-inactive-badge">INACTIVE</span>' : '') +
        '</div></div></div>' +
        '<div class="fc-fp-row"><i class="fa-regular fa-calendar"></i><span>' + (parseFestivalDate(p.date) ? parseFestivalDate(p.date).toLocaleDateString('en',{weekday:'long',year:'numeric',month:'long',day:'numeric'}) : 'Date unavailable') + '</span></div>' +
        '<div class="fc-fp-row"><i class="fa-solid fa-users"></i><span>' + (p.selections_count||0) + ' selected</span></div>' +
        (desc ? '<div class="fc-fp-desc">' + desc + '</div>' : '') +
        '<div class="fc-fp-actions">' +
        '<button class="fc-fp-btn" onclick="openEditModalFromProps(' + editArgs + ')"><i class="fa-solid fa-pen"></i> Edit</button>' +
        '<button class="fc-fp-btn" onclick="toggleFestival(' + id + ')" style="color:' + (p.is_active?'#ef4444':'#059669') + '"><i class="fa-solid fa-' + (p.is_active?'eye-slash':'eye') + '"></i> ' + (p.is_active?'Hide':'Show') + '</button>' +
        '<button class="fc-fp-btn danger" onclick="deleteFestival(' + id + ')"><i class="fa-solid fa-trash"></i></button>' +
        '</div>';
    document.body.appendChild(pop);
    let top = jsEvent.clientY + 14, left = jsEvent.clientX - 150;
    const r = pop.getBoundingClientRect();
    if (left < 10) left = 10;
    if (left + 300 > window.innerWidth - 10) left = window.innerWidth - 310;
    if (top + r.height > window.innerHeight - 10) top = jsEvent.clientY - r.height - 14;
    pop.style.top = top + 'px'; pop.style.left = left + 'px';
    
    const close = () => {
        pop.style.opacity='0'; pop.style.transform='scale(.95)';
        setTimeout(() => pop.remove(), 150);
        document.removeEventListener('keydown', kh);
        setTimeout(() => document.removeEventListener('click', oh), 10);
    };
    const kh = e => { if (e.key==='Escape') close(); };
    const oh = e => { if (!pop.contains(e.target)) close(); };
    document.addEventListener('keydown', kh);
    setTimeout(() => document.addEventListener('click', oh), 10);
}

function openAddModal() {
    editMode = false; editId = null;
    document.getElementById('sfTitle').innerHTML = '<div class="fc-modal-icon"><i class="fa-solid fa-calendar-plus"></i></div> Create New Festival';
    ['sfName','sfEmoji','sfDesc'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('sfCategory').value = '';
    document.getElementById('sfDate').value = '';
    // Reset picker to Suggested + clear search
    const sfSearch = document.getElementById('sfEmojiSearch');
    if (sfSearch) sfSearch.value = '';
    sfEmojiSearchTerm = '';
    sfActiveEmojiGroup = 'suggested';
    document.querySelectorAll('#sfEmojiTabs .sf-emoji-tab').forEach(t =>
        t.classList.toggle('active', t.dataset.group === 'suggested')
    );
    renderEmojiGrid();
    clearFestivalValidationState();
    document.getElementById('sfModal').classList.add('open');
    updatePreview();
    setTimeout(() => document.getElementById('sfName').focus(), 80);
}
function openEditModalFromProps(id, name, date, emoji, category, desc) {
    editMode = true; editId = id;
    document.getElementById('sfTitle').innerHTML = '<div class="fc-modal-icon"><i class="fa-solid fa-pen"></i></div> Edit Festival';
    document.getElementById('sfName').value     = name;
    document.getElementById('sfDate').value     = date;
    document.getElementById('sfEmoji').value    = emoji;
    document.getElementById('sfCategory').value = category;
    document.getElementById('sfDesc').value     = desc;
    // Re-render picker so the current emoji shows as selected; switch to relevant group
    const sfSearch = document.getElementById('sfEmojiSearch');
    if (sfSearch) sfSearch.value = '';
    sfEmojiSearchTerm = '';
    sfActiveEmojiGroup = SF_CATEGORY_SUGGEST[category] || 'suggested';
    document.querySelectorAll('#sfEmojiTabs .sf-emoji-tab').forEach(t =>
        t.classList.toggle('active', t.dataset.group === sfActiveEmojiGroup)
    );
    renderEmojiGrid();
    clearFestivalValidationState();
    validateFestivalForm(false);
    document.getElementById('sfModal').classList.add('open');
    updatePreview();
    document.querySelectorAll('.fc-festival-popover').forEach(p => p.remove());
}
function closeSfModal() { document.getElementById('sfModal').classList.remove('open'); }

async function saveFestival() {
    const check = validateFestivalForm(true);
    if (!check.valid) {
        showToast(check.message, 'error');
        fieldEl(check.key)?.focus();
        return;
    }

    const name = document.getElementById('sfName').value.trim();
    const date = document.getElementById('sfDate').value;
    const btn = document.getElementById('sfSaveBtn');
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    const payload = { name, date,
        emoji:       document.getElementById('sfEmoji').value,
        category:    document.getElementById('sfCategory').value,
        description: document.getElementById('sfDesc').value
    };
    const url    = editMode ? FEST_URL + '/' + editId : FEST_URL;
    const method = editMode ? 'PUT' : 'POST';
    try {
        const res  = await fetch(url, { method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) {
            closeSfModal();
            showToast('Festival saved successfully!', 'success');
            setTimeout(() => location.reload(), 800);
        } else { showToast(data.message || 'Error saving.', 'error'); }
    } catch(e) { showToast('Network error.', 'error'); }
    finally { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Publish Event'; }
}

async function toggleFestival(id) {
    try {
        const res  = await fetch(FEST_URL + '/' + id + '/toggle', { method:'PATCH', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} });
        const data = await res.json();
        if (data.success) {
            showToast('Festival status updated.', 'success');
            setTimeout(() => location.reload(), 500);
        }
    } catch(e) { showToast('Network error.', 'error'); }
}

async function deleteFestival(id) {
    if (!confirm('Delete this festival permanently?')) return;
    try {
        const res  = await fetch(FEST_URL + '/' + id, { method:'DELETE', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} });
        const data = await res.json();
        if (data.success) {
            showToast('Festival deleted.', 'success');
            setTimeout(() => location.reload(), 500);
        }
    } catch(e) { showToast('Network error.', 'error'); }
}

async function uploadImportFile(files) {
    if (!files?.length) return;
    const btn = document.getElementById('importBtn');
    const orig = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';
    const fd = new FormData(); fd.append('file', files[0]);
    try {
        const res  = await fetch(FEST_URL + '/import', { method:'POST', headers:{'X-CSRF-TOKEN':csrf}, body:fd });
        const data = await res.json();
        if (data.success) { showToast('Import successful!', 'success'); setTimeout(() => location.reload(), 1200); }
        else { showToast(data.message || 'Import failed.', 'error'); }
    } catch(e) { showToast('Network error.', 'error'); }
    finally { btn.disabled = false; btn.innerHTML = orig; document.getElementById('importFile').value = ''; }
}

function showToast(msg, type) {
    type = type || 'info';
    const bg = { success:'#10b981', error:'#ef4444', info:'#6366f1' };
    const ic = { success:'circle-check', error:'circle-xmark', info:'info-circle' };
    const t = document.createElement('div');
    t.className = 'fc-toast';
    t.style.cssText = 'background:' + (bg[type]||bg.info) + ';opacity:0;transform:translateY(12px)';
    t.innerHTML = '<i class="fa-solid fa-' + (ic[type]||'info-circle') + '"></i> ' + msg;
    document.body.appendChild(t);
    requestAnimationFrame(function(){ t.style.opacity='1'; t.style.transform='translateY(0)'; });
    setTimeout(function(){ t.style.opacity='0'; t.style.transform='translateY(12px)'; setTimeout(function(){t.remove();},300); }, 3200);
}

function openTemplateModal()  { document.getElementById('templateModal').classList.add('open'); }
function closeTemplateModal() { document.getElementById('templateModal').classList.remove('open'); }
function closeShareModal()    { document.getElementById('shareCalendarModal').classList.remove('open'); }

// ============ SHARE AS IMAGE LOGIC ============
let generatedImageBlob = null;
let generatedImageDataUrl = null;
let currentMonthName = '';

async function shareFestivalCalendarAsImage() {
    const calArea = document.querySelector('.fc-cal-area');
    if (!calArea) {
        showToast('Calendar is empty or not loaded.', 'error');
        return;
    }

    const modal = document.getElementById('shareCalendarModal');
    const loadingState = document.getElementById('shareLoadingState');
    const previewState = document.getElementById('sharePreviewState');
    const previewImg = document.getElementById('shareImagePreview');
    const monthLabelEl = document.getElementById('calMonthLabel');
    currentMonthName = monthLabelEl ? monthLabelEl.textContent.trim() : 'Festival-Calendar';

    document.getElementById('shareModalTitle').textContent = `Share Festival Calendar — ${currentMonthName}`;

    loadingState.style.display = 'flex';
    previewState.style.display = 'none';
    modal.classList.add('open');

    // Temporarily hide navigation buttons for clean export look
    const navButtons = calArea.querySelectorAll('.fc-cal-nav, .fc-cal-today-btn');
    navButtons.forEach(el => el.style.visibility = 'hidden');

    try {
        const canvas = await html2canvas(calArea, {
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
            onclone: (clonedDoc) => {
                const clonedArea = clonedDoc.querySelector('.fc-cal-area');
                if (clonedArea) {
                    clonedArea.style.padding = '24px';
                    clonedArea.style.borderRadius = '0px';
                    clonedArea.style.boxShadow = 'none';
                    clonedArea.style.background = '#ffffff';
                }
            }
        });

        navButtons.forEach(el => el.style.visibility = 'visible');

        generatedImageDataUrl = canvas.toDataURL('image/png');
        previewImg.src = generatedImageDataUrl;

        canvas.toBlob(blob => {
            generatedImageBlob = blob;
            loadingState.style.display = 'none';
            previewState.style.display = 'block';

            const nativeBtn = document.getElementById('btnNativeShare');
            if (navigator.canShare && navigator.canShare({ files: [new File([blob], 'test.png', { type: 'image/png' })] })) {
                nativeBtn.style.display = 'inline-flex';
            } else {
                nativeBtn.style.display = 'none';
            }
        }, 'image/png');

    } catch (err) {
        navButtons.forEach(el => el.style.visibility = 'visible');
        console.error('Canvas capture failed:', err);
        loadingState.style.display = 'none';
        closeShareModal();
        showToast('Failed to generate image. Please try again.', 'error');
    }
}

function downloadCalendarImage() {
    if (!generatedImageDataUrl) return;
    const safeName = currentMonthName.replace(/[^a-zA-Z0-9_-]/g, '-');
    const a = document.createElement('a');
    a.href = generatedImageDataUrl;
    a.download = `festival-calendar-${safeName}.png`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    showToast('Download started!', 'success');
}

async function copyCalendarImage() {
    if (!generatedImageBlob) return;
    try {
        if (navigator.clipboard && window.ClipboardItem) {
            await navigator.clipboard.write([
                new ClipboardItem({ 'image/png': generatedImageBlob })
            ]);
            showToast('Image copied to clipboard!', 'success');
        } else {
            showToast('Direct copy not supported on this browser. Please use Download.', 'info');
        }
    } catch (err) {
        console.error('Copy failed:', err);
        showToast('Could not copy image. Please download instead.', 'error');
    }
}

async function triggerNativeShare() {
    if (!generatedImageBlob) return;
    try {
        const safeName = currentMonthName.replace(/[^a-zA-Z0-9_-]/g, '-');
        const file = new File([generatedImageBlob], `festival-calendar-${safeName}.png`, { type: 'image/png' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                title: `Festival Calendar — ${currentMonthName}`,
                text: `Here is the Festival & Event Calendar for ${currentMonthName}.`,
                files: [file]
            });
        }
    } catch (err) {
        if (err.name !== 'AbortError') {
            console.error('Native share failed:', err);
        }
    }
}

document.addEventListener('keydown', function(e){ if (e.key==='Escape') { closeSfModal(); closeTemplateModal(); closeShareModal(); } });
</script>
@endpush
