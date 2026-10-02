@extends('layouts.app')

@section('content')
@php
    $isAdmin = auth()->user()->role === 'admin';
    $storeRoute = $isAdmin ? route('admin.shoot-days.store') : route('strategist.shoot-days.store');
    $backRoute = $isAdmin ? route('admin.dashboard') : route('strategist.dashboard');
    $currentRoute = $isAdmin ? route('admin.shoot-days') : route('strategist.shoot-days');
    $statusFilter = request('status');
    $typeFilter = request('type');
    $clientFilter = request('client_id');
    $searchQuery = request('search');
@endphp

<div class="dash-wrapper">
    <div class="dash-container">
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="asd-toast">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                <span onclick="this.parentElement.remove()" style="cursor:pointer;margin-left:auto;opacity:0.6">&times;</span>
            </div>
        @endif

        {{-- Header --}}
        <div class="asd-hero">
            <div class="asd-hero-left">
                <div class="asd-hero-title">
                    <i class="fas fa-camera" style="color:var(--primary);margin-right:8px"></i>
                    Photoshoot & Production Schedule
                </div>
                <div class="asd-hero-sub">Schedule photo & video shoot days, track status, and coordinate client production</div>
            </div>
            <div class="asd-hero-actions">
                <button type="button" class="btn-primary asd-schedule-btn" onclick="openScheduleShootModal()">
                    <i class="fa-solid fa-plus"></i> Schedule Photoshoot
                </button>
                <a href="{{ $backRoute }}" class="btn-back"><i class="fa-solid fa-chevron-left"></i> Dashboard</a>
            </div>
        </div>

        {{-- Status Summary KPI Cards --}}
        <div class="asd-status-bar">
            <div class="asd-status-badge asd-scheduled">
                <span class="asd-badge-icon"><i class="fas fa-calendar-check"></i></span>
                <span class="asd-badge-num">{{ $shootDaysScheduled ?? 0 }}</span>
                <span class="asd-badge-label">Scheduled</span>
            </div>
            <div class="asd-status-badge asd-inprogress">
                <span class="asd-badge-icon"><i class="fas fa-clock"></i></span>
                <span class="asd-badge-num">{{ $shootDaysInProgress ?? 0 }}</span>
                <span class="asd-badge-label">In Progress</span>
            </div>
            <div class="asd-status-badge asd-overdue">
                <span class="asd-badge-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <span class="asd-badge-num">{{ $shootDaysOverdue ?? 0 }}</span>
                <span class="asd-badge-label">Overdue</span>
            </div>
            <div class="asd-status-badge asd-completed">
                <span class="asd-badge-icon"><i class="fas fa-check-circle"></i></span>
                <span class="asd-badge-num">{{ $shootDaysCompleted ?? 0 }}</span>
                <span class="asd-badge-label">Completed</span>
            </div>
        </div>

        {{-- Filter & Search Toolbar --}}
        <div class="asd-toolbar">
            <form method="GET" action="{{ $currentRoute }}" class="asd-filter-form">
                <div class="asd-search-box">
                    <i class="fa-solid fa-magnifying-glass asd-search-icon"></i>
                    <input type="text" name="search" value="{{ $searchQuery }}" placeholder="Search title, location, notes, client..." class="asd-search-input">
                </div>

                <select name="type" class="asd-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="photo" {{ $typeFilter === 'photo' ? 'selected' : '' }}>Photo Only</option>
                    <option value="video" {{ $typeFilter === 'video' ? 'selected' : '' }}>Video Only</option>
                    <option value="both" {{ $typeFilter === 'both' ? 'selected' : '' }}>Photo & Video</option>
                </select>

                <select name="status" class="asd-select" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ $statusFilter === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="in_progress" {{ $statusFilter === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                @if(isset($clients) && $clients->count())
                <select name="client_id" class="asd-select" onchange="this.form.submit()">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ $clientFilter == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                @endif

                @if($searchQuery || $typeFilter || $statusFilter || $clientFilter)
                    <a href="{{ $currentRoute }}" class="asd-clear-btn" title="Clear filters">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </form>
            <div class="asd-count-badge">
                {{ $shootDays->total() }} photoshoot{{ $shootDays->total() !== 1 ? 's' : '' }}
            </div>
        </div>

        {{-- Shoots Table --}}
        <div class="card asd-table-card">
            <table class="asd-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Scheduled By</th>
                        <th>Shoot Date</th>
                        <th>Time</th>
                        <th>Duration</th>
                        <th>Client</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shootDays as $shoot)
                        @php
                            $updateUrl = $isAdmin ? route('admin.shoot-days.update', $shoot) : route('strategist.shoot-days.update', $shoot);
                            $deleteUrl = $isAdmin ? route('admin.shoot-days.destroy', $shoot) : route('strategist.shoot-days.destroy', $shoot);
                        @endphp
                        <tr class="asd-table-row {{ $shoot->shoot_date && $shoot->shoot_date->isToday() ? 'asd-row-today' : '' }} asd-status-{{ $shoot->status }}">
                            <td class="asd-table-type">
                                <span class="asd-type-badge asd-type-{{ $shoot->type }}" title="{{ ucfirst($shoot->type) }}">
                                    {!! $shoot->type === 'video' ? '<i class="fas fa-video"></i>' : ($shoot->type === 'photo' ? '<i class="fas fa-camera"></i>' : '<i class="fas fa-camera-retro"></i>') !!}
                                </span>
                            </td>
                            <td class="asd-table-title">
                                <strong>{{ $shoot->title }}</strong>
                            </td>
                            <td>
                                <span class="asd-creator-pill">
                                    <i class="fa-solid fa-user"></i> {{ $shoot->creator->name ?? 'Team' }}
                                </span>
                            </td>
                            <td class="asd-table-date">
                                <strong>{{ $shoot->shoot_date ? $shoot->shoot_date->format('M d, Y') : '—' }}</strong>
                                <div style="font-size:11px;color:var(--text3)">{{ $shoot->shoot_date ? $shoot->shoot_date->format('l') : '' }}</div>
                            </td>
                            <td class="asd-table-time">
                                @if($shoot->start_date)
                                    {{ \Carbon\Carbon::parse($shoot->start_date)->format('g:i A') }}
                                @else
                                    <span class="asd-na">—</span>
                                @endif
                            </td>
                            <td>
                                @if($shoot->number_of_days)
                                    <span style="font-size:11.5px;font-weight:600">{{ $shoot->number_of_days }} day{{ $shoot->number_of_days > 1 ? 's' : '' }}</span>
                                @else
                                    <span class="asd-na">—</span>
                                @endif
                            </td>
                            <td class="asd-table-client">
                                @if($shoot->client)
                                    <div style="display:inline-flex;align-items:center;gap:6px">
                                        @if($shoot->client->logo)
                                            <img src="{{ asset('storage/' . $shoot->client->logo) }}" style="width:20px;height:20px;border-radius:4px;object-fit:cover">
                                        @elseif($shoot->client->emoji)
                                            <span>{{ $shoot->client->emoji }}</span>
                                        @else
                                            <i class="fas fa-building" style="color:var(--primary)"></i>
                                        @endif
                                        <span style="font-weight:600">{{ $shoot->client->name }}</span>
                                    </div>
                                @else
                                    <span class="asd-na">—</span>
                                @endif
                            </td>
                            <td class="asd-table-location">
                                @if($shoot->location)
                                    <i class="fa-solid fa-location-dot" style="color:#8B5CF6;font-size:11px;margin-right:2px"></i>
                                    {{ $shoot->location }}
                                @else
                                    <span class="asd-na">—</span>
                                @endif
                            </td>
                            <td class="asd-table-status">
                                <span class="asd-status-badge-inline asd-status-{{ $shoot->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $shoot->status)) }}
                                </span>
                            </td>
                            <td class="asd-table-notes">
                                @if($shoot->notes)
                                    <span title="{{ $shoot->notes }}">{{ Str::limit($shoot->notes, 30) }}</span>
                                @else
                                    <span class="asd-na">—</span>
                                @endif
                            </td>
                            <td class="asd-table-action">
                                <form action="{{ $updateUrl }}" method="POST" style="display:inline">
                                    @csrf @method('PATCH')
                                    @if($shoot->status === 'scheduled')
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="asd-action-btn asd-action-start" title="Mark In Progress"><i class="fa-solid fa-play"></i></button>
                                    @elseif($shoot->status === 'in_progress')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="asd-action-btn asd-action-done" title="Mark Completed"><i class="fa-solid fa-check"></i></button>
                                    @endif
                                </form>
                                <form action="{{ $deleteUrl }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this photoshoot?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="asd-action-btn asd-action-delete" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align:center;padding:48px 20px">
                                <div style="color:var(--text3);font-size:14px">
                                    <i class="fas fa-camera" style="font-size:36px;opacity:0.4;display:block;margin-bottom:12px;color:var(--primary)"></i>
                                    <strong>No photoshoots found</strong>
                                    <p style="margin:6px 0 16px;font-size:12.5px;color:var(--text3)">Schedule photo and video shoots for active clients or projects.</p>
                                    <button type="button" class="btn-primary" onclick="openScheduleShootModal()">
                                        <i class="fa-solid fa-plus"></i> Schedule Photoshoot
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($shootDays->hasPages())
            <div style="margin-top:20px;display:flex;justify-content:center">
                {{ $shootDays->links() }}
            </div>
        @endif
    </div>
</div>

{{-- ============ SCHEDULE PHOTOSHOOT MODAL ============ --}}
<div class="modal-overlay" id="scheduleShootModal">
    <div class="modal" style="width:580px;max-height:92vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title" style="display:flex;align-items:center;gap:8px">
                <i class="fas fa-camera" style="color:var(--primary)"></i>
                <span>Schedule a Photoshoot</span>
            </div>
            <span class="modal-close" onclick="closeModal('scheduleShootModal')">&times;</span>
        </div>

        <form method="POST" action="{{ $storeRoute }}" autocomplete="off" id="scheduleShootForm">
            @csrf

            <div class="form-group" style="margin-bottom:14px">
                <label>Photoshoot Title <span style="color:#EF4444">*</span></label>
                <input type="text" name="title" required placeholder="e.g. Summer Collection Video & Stills, Factory Product Shoot">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Shoot Type <span style="color:#EF4444">*</span></label>
                    <select name="type" required>
                        <option value="photo">Photo Shoot</option>
                        <option value="video">Video Shoot</option>
                        <option value="both" selected>Both (Photo & Video)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Client <span style="color:var(--text3);font-size:11px;font-weight:400">(Optional)</span></label>
                    <select name="client_id" class="form-control">
                        <option value="">— Select Client (Optional) —</option>
                        @if(isset($clients))
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Shoot Date <span style="color:#EF4444">*</span></label>
                    <input type="date" name="shoot_date" required value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label>Start Time</label>
                    <input type="time" name="start_date">
                </div>
                <div class="form-group">
                    <label>Number of Days</label>
                    <input type="number" name="number_of_days" min="1" max="30" value="1" placeholder="1">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label>Location / Studio Address <span style="color:var(--text3);font-size:11px;font-weight:400">(Optional)</span></label>
                <input type="text" name="location" placeholder="e.g. Studio 4A, Bandra West / Client Factory, Pune">
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label>Shoot Brief & Gear Notes <span style="color:var(--text3);font-size:11px;font-weight:400">(Optional)</span></label>
                <textarea name="notes" rows="3" placeholder="Equipment needed, shot list references, models, client contact on-site, call time..."></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="closeModal('scheduleShootModal')">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Schedule Shoot
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .dash-wrapper { min-height:100vh; background:var(--body-bg); }
    .dash-container { max-width:1300px; margin:0 auto; padding:24px; }

    /* Toast */
    .asd-toast { display:flex; align-items:center; gap:10px; background:linear-gradient(135deg,#10B98115,#10B98108); border:1px solid #10B98130; color:#059669; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:600; margin-bottom:16px; }

    /* Hero */
    .asd-hero { background:linear-gradient(135deg,rgba(79,109,240,0.08) 0%,rgba(139,92,246,0.08) 100%); border:1px solid var(--border); border-radius:16px; padding:20px 24px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; }
    .asd-hero-title { font-size:22px; font-weight:800; color:var(--text); letter-spacing:-0.3px; display:flex; align-items:center; }
    .asd-hero-sub { font-size:13px; color:var(--text3); margin-top:3px; }
    .asd-hero-actions { display:flex; align-items:center; gap:10px; }
    .btn-back { display:inline-flex; align-items:center; gap:6px; padding:9px 14px; background:var(--card); border:1px solid var(--border); border-radius:8px; color:var(--text); text-decoration:none; font-size:12.5px; font-weight:600; transition:all 0.2s; }
    .btn-back:hover { background:var(--card2); transform:translateX(-2px); }

    /* KPI Bar */
    .asd-status-bar { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px; margin-bottom:20px; }
    .asd-status-badge { display:flex; flex-direction:column; align-items:center; gap:6px; padding:14px 12px; background:var(--card); border:1px solid var(--border); border-radius:12px; border-left:4px solid transparent; transition:all .2s; }
    .asd-status-badge:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(0,0,0,0.05); }
    .asd-status-badge.asd-scheduled { border-left-color:var(--blue); }
    .asd-status-badge.asd-inprogress { border-left-color:var(--primary); }
    .asd-status-badge.asd-overdue { border-left-color:var(--red); }
    .asd-status-badge.asd-completed { border-left-color:var(--teal); }
    .asd-badge-icon { font-size:20px; color:var(--text2); }
    .asd-badge-num { font-size:22px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; }
    .asd-badge-label { font-size:11px; color:var(--text3); text-transform:uppercase; font-weight:700; letter-spacing:.3px; }

    /* Filter Toolbar */
    .asd-toolbar { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
    .asd-filter-form { display:flex; align-items:center; gap:10px; flex-wrap:wrap; flex:1; }
    .asd-search-box { position:relative; min-width:220px; flex:1; }
    .asd-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text3); font-size:13px; }
    .asd-search-input { width:100%; padding:8px 12px 8px 34px; font-size:12.5px; border-radius:8px; border:1px solid var(--border); background:var(--card2); color:var(--text); }
    .asd-select { padding:8px 12px; font-size:12px; border-radius:8px; border:1px solid var(--border); background:var(--card2); color:var(--text); font-weight:600; cursor:pointer; }
    .asd-clear-btn { width:32px; height:32px; border-radius:8px; background:rgba(239,68,68,0.1); color:#EF4444; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:13px; }
    .asd-count-badge { font-size:12px; color:var(--text3); font-weight:600; }

    /* Table */
    .asd-table-card { overflow-x:auto; border-radius:14px; border:1px solid var(--border); background:var(--card); }
    .asd-table { width:100%; border-collapse:collapse; text-align:left; }
    .asd-table thead { border-bottom:2px solid var(--border); background:var(--card2); }
    .asd-table thead th { padding:12px 14px; font-size:11px; font-weight:700; color:var(--text2); text-transform:uppercase; letter-spacing:0.4px; white-space:nowrap; }
    .asd-table tbody tr { border-bottom:1px solid var(--border); transition:all 0.15s; }
    .asd-table tbody tr:hover { background:var(--card2); }
    .asd-table tbody tr.asd-row-today { background:rgba(204,49,14,0.05); border-left:3px solid var(--primary); }
    .asd-table tbody tr.asd-status-completed { opacity:0.75; }
    .asd-table td { padding:12px 14px; font-size:12.5px; color:var(--text); }

    .asd-table-type { text-align:center; width:50px; }
    .asd-type-badge { font-size:15px; padding:5px 9px; border-radius:8px; display:inline-block; }
    .asd-type-badge.asd-type-video { background:rgba(139,92,246,0.12); color:#8B5CF6; }
    .asd-type-badge.asd-type-photo { background:rgba(245,158,11,0.12); color:#D97706; }
    .asd-type-badge.asd-type-both { background:rgba(59,182,246,0.12); color:#2563EB; }

    .asd-creator-pill { font-size:11.5px; font-weight:700; color:var(--primary); background:rgba(99,102,241,0.08); padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px; }
    .asd-table-date strong { font-size:12.5px; }
    .asd-table-time { font-family:'JetBrains Mono',monospace; font-size:11.5px; font-weight:600; }
    .asd-status-badge-inline { display:inline-block; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; }
    .asd-status-badge-inline.asd-status-scheduled { background:rgba(59,182,246,0.12); color:var(--blue); }
    .asd-status-badge-inline.asd-status-in_progress { background:rgba(204,49,14,0.12); color:var(--primary); }
    .asd-status-badge-inline.asd-status-completed { background:rgba(16,185,129,0.12); color:var(--teal); }
    .asd-status-badge-inline.asd-status-cancelled { background:rgba(107,114,128,0.12); color:var(--text3); }

    .asd-action-btn { background:none; border:none; color:var(--text2); cursor:pointer; font-size:13px; padding:6px 6px; transition:all 0.2s; border-radius:6px; display:inline-block; }
    .asd-action-btn:hover { color:var(--primary); transform:scale(1.2); }
    .asd-action-start { color:var(--primary); }
    .asd-action-done { color:var(--teal); }
    .asd-action-delete { color:var(--text3); }
    .asd-action-delete:hover { color:var(--red); }
    .asd-na { color:var(--text3); }
</style>

<script>
function openScheduleShootModal() {
    document.getElementById('scheduleShootModal').classList.add('show');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

/**
 * SHOOT DAYS AJAX - Status Updates & Delete Without Page Refresh
 */
document.addEventListener('DOMContentLoaded', function() {
    setupShootDayActions();
});

function setupShootDayActions() {
    document.querySelectorAll('form[action*="shoot"][method="POST"]').forEach(form => {
        if (form.querySelector('input[name="_method"][value="PATCH"]')) {
            form.addEventListener('submit', handleShootDayUpdateAJAX);
        }
        if (form.querySelector('input[name="_method"][value="DELETE"]')) {
            form.removeAttribute('onsubmit');
            form.addEventListener('submit', handleShootDayDeleteAJAX);
        }
    });
}

async function handleShootDayUpdateAJAX(e) {
    e.preventDefault();
    const form = e.target;
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: formData
        });
        if (!response.ok) throw new Error('Update failed');
        await refreshShootDaysContent();
        if (typeof ajax !== 'undefined') ajax.showSuccess('Photoshoot updated!');
    } catch (error) {
        console.error('Update error:', error);
        form.submit();
    }
}

async function handleShootDayDeleteAJAX(e) {
    e.preventDefault();
    if (!confirm('Delete this photoshoot?')) return;
    const form = e.target;
    const row = form.closest('tr');
    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            body: formData
        });
        if (!response.ok) throw new Error('Delete failed');
        if (row) {
            row.style.transition = 'opacity 0.3s, transform 0.3s';
            row.style.opacity = '0';
            row.style.transform = 'translateX(20px)';
            setTimeout(() => row.remove(), 300);
        }
        if (typeof ajax !== 'undefined') ajax.showSuccess('Photoshoot removed!');
    } catch (error) {
        console.error('Delete error:', error);
        form.submit();
    }
}

async function refreshShootDaysContent() {
    try {
        const response = await fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        });
        if (!response.ok) return;
        const html = await response.text();
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');

        const newStatusBar = newDoc.querySelector('.asd-status-bar');
        const curStatusBar = document.querySelector('.asd-status-bar');
        if (newStatusBar && curStatusBar) curStatusBar.innerHTML = newStatusBar.innerHTML;

        const newTable = newDoc.querySelector('.asd-table tbody');
        const curTable = document.querySelector('.asd-table tbody');
        if (newTable && curTable) curTable.innerHTML = newTable.innerHTML;

        setupShootDayActions();
    } catch (error) {
        console.error('Refresh error:', error);
    }
}
</script>
@endsection
