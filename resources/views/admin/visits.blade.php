@extends('layouts.app')

@section('content')

{{-- Success / Error Flash Toasts --}}
@if(session('success'))
<div class="cv-toast" id="flashMsg">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    <span onclick="this.parentElement.remove()" style="cursor:pointer;margin-left:auto;opacity:0.6">&times;</span>
</div>
@endif
@if(session('error'))
<div class="cv-toast cv-toast-error" id="flashMsgErr">
    <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
    <span onclick="this.parentElement.remove()" style="cursor:pointer;margin-left:auto;opacity:0.6">&times;</span>
</div>
@endif

{{-- ============ HERO / HEADER ============ --}}
<div class="cv-hero">
    <div class="cv-hero-content">
        <div class="cv-hero-text">
            <div class="cv-hero-title">
                <i class="fa-solid fa-person-walking-luggage" style="color:var(--primary);margin-right:8px"></i>
                Client & Lead Visits
            </div>
            <div class="cv-hero-sub">Track in-person meetings, sales pitches, client reviews, and follow-up updates</div>
        </div>
        <div class="cv-hero-actions">
            <button class="btn-primary" onclick="openCreateVisitModal('client')">
                <i class="fa-solid fa-plus"></i> Add Client Visit
            </button>
        </div>
    </div>
</div>

{{-- ============ KPI METRICS ============ --}}
<div class="cv-metrics">
    <div class="cv-metric" style="--mc:#3B82F6;--mg:linear-gradient(135deg,#3B82F6,#60A5FA)">
        <div class="cv-metric-icon"><i class="fa-solid fa-handshake"></i></div>
        <div class="cv-metric-body">
            <div class="cv-metric-val">{{ $totalVisits }}</div>
            <div class="cv-metric-lbl">Total Visits Logged</div>
        </div>
        <div class="cv-metric-glow"></div>
    </div>

    <div class="cv-metric" style="--mc:#8B5CF6;--mg:linear-gradient(135deg,#8B5CF6,#A78BFA)">
        <div class="cv-metric-icon"><i class="fa-solid fa-bullseye"></i></div>
        <div class="cv-metric-body">
            <div class="cv-metric-val">{{ $leadVisitsCount }}</div>
            <div class="cv-metric-lbl">Lead Visits ({{ $convertedCount }} Converted · {{ $conversionRate }}%)</div>
        </div>
        <div class="cv-metric-glow"></div>
    </div>

    <div class="cv-metric" style="--mc:#10B981;--mg:linear-gradient(135deg,#10B981,#34D399)">
        <div class="cv-metric-icon"><i class="fa-solid fa-building"></i></div>
        <div class="cv-metric-body">
            <div class="cv-metric-val">{{ $clientVisitsCount }}</div>
            <div class="cv-metric-lbl">Active Client Visits</div>
        </div>
        <div class="cv-metric-glow"></div>
    </div>

    <div class="cv-metric" style="--mc:#F59E0B;--mg:linear-gradient(135deg,#F59E0B,#FBBF24)">
        <div class="cv-metric-icon"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="cv-metric-body">
            <div class="cv-metric-val">{{ $upcomingCount }}</div>
            <div class="cv-metric-lbl">Scheduled & Upcoming</div>
        </div>
        <div class="cv-metric-glow"></div>
    </div>

    <div class="cv-metric" style="--mc:#EF4444;--mg:linear-gradient(135deg,#EF4444,#F87171)">
        <div class="cv-metric-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
        <div class="cv-metric-body">
            <div class="cv-metric-val">{{ $followUpsCount }}</div>
            <div class="cv-metric-lbl">Follow-ups Due</div>
        </div>
        <div class="cv-metric-glow"></div>
    </div>
</div>

{{-- ============ SUB-TABS NAVIGATION ============ --}}
<div class="cv-tabs-bar">
    <div class="cv-tabs">
        <a href="{{ route('admin.visits', array_merge(request()->except(['tab', 'page']), ['tab' => 'all'])) }}" class="cv-tab {{ $tab === 'all' ? 'active' : '' }}">
            <i class="fa-solid fa-layer-group"></i> All Visits
            <span class="cv-tab-count">{{ $totalVisits }}</span>
        </a>
        <a href="{{ route('admin.visits', array_merge(request()->except(['tab', 'page']), ['tab' => 'lead'])) }}" class="cv-tab cv-tab-lead {{ $tab === 'lead' ? 'active' : '' }}">
            <i class="fa-solid fa-bullseye"></i> Lead Visits
            <span class="cv-tab-count">{{ $leadVisitsCount }}</span>
        </a>
        <a href="{{ route('admin.visits', array_merge(request()->except(['tab', 'page']), ['tab' => 'client'])) }}" class="cv-tab cv-tab-client {{ $tab === 'client' ? 'active' : '' }}">
            <i class="fa-solid fa-building"></i> Client Visits
            <span class="cv-tab-count">{{ $clientVisitsCount }}</span>
        </a>
        <a href="{{ route('admin.visits', array_merge(request()->except(['tab', 'page']), ['tab' => 'upcoming'])) }}" class="cv-tab {{ $tab === 'upcoming' ? 'active' : '' }}">
            <i class="fa-solid fa-calendar-days"></i> Upcoming
            <span class="cv-tab-count">{{ $upcomingCount }}</span>
        </a>
        <a href="{{ route('admin.visits', array_merge(request()->except(['tab', 'page']), ['tab' => 'follow_ups'])) }}" class="cv-tab {{ $tab === 'follow_ups' ? 'active' : '' }}">
            <i class="fa-solid fa-bell"></i> Follow-ups
            <span class="cv-tab-count" style="background:#EF4444;color:#fff">{{ $followUpsCount }}</span>
        </a>
    </div>
</div>

{{-- ============ FILTER & SEARCH TOOLBAR ============ --}}
<div class="cv-toolbar">
    <form method="GET" action="{{ route('admin.visits') }}" class="cv-toolbar-form" id="visitsFilterForm">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <div class="cv-search-wrap">
            <i class="fa-solid fa-magnifying-glass cv-search-icon"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search company, lead, purpose, notes..." class="cv-search-input" autocomplete="off">
        </div>

        <select name="status" class="cv-filter-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="follow_up_needed" {{ $status === 'follow_up_needed' ? 'selected' : '' }}>Follow-up Needed</option>
            <option value="converted" {{ $status === 'converted' ? 'selected' : '' }}>Converted</option>
            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>

        <select name="mode" class="cv-filter-select" onchange="this.form.submit()">
            <option value="">All Meeting Modes</option>
            <option value="in_person" {{ $mode === 'in_person' ? 'selected' : '' }}>In-Person</option>
            <option value="client_office" {{ $mode === 'client_office' ? 'selected' : '' }}>Client Office</option>
            <option value="agency_office" {{ $mode === 'agency_office' ? 'selected' : '' }}>Agency Office</option>
            <option value="virtual_call" {{ $mode === 'virtual_call' ? 'selected' : '' }}>Virtual / Video Call</option>
            <option value="on_site" {{ $mode === 'on_site' ? 'selected' : '' }}>On-Site</option>
        </select>

        <select name="visitor_id" class="cv-filter-select" onchange="this.form.submit()">
            <option value="">All Visitors</option>
            @foreach($teamMembers as $member)
                <option value="{{ $member->id }}" {{ $visitorId == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
            @endforeach
        </select>

        @if($clients->count())
        <select name="client_id" class="cv-filter-select" onchange="this.form.submit()">
            <option value="">All Clients</option>
            @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ $clientId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        @endif

        <select name="sort" class="cv-filter-select" onchange="this.form.submit()">
            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Sort: Newest Visit Date</option>
            <option value="visit_date_asc" {{ $sort === 'visit_date_asc' ? 'selected' : '' }}>Sort: Chronological (Upcoming first)</option>
            <option value="follow_up" {{ $sort === 'follow_up' ? 'selected' : '' }}>Sort: Follow-up Date</option>
            <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Sort: Oldest</option>
        </select>

        @if($search || $status || $visitorId || $clientId || $mode || $sort !== 'newest')
            <a href="{{ route('admin.visits', ['tab' => $tab]) }}" class="cv-clear-btn" title="Clear filters">
                <i class="fa-solid fa-xmark"></i>
            </a>
        @endif
    </form>
    <div class="cv-toolbar-count">
        {{ $visits->total() }} record{{ $visits->total() !== 1 ? 's' : '' }}
    </div>
</div>

{{-- ============ VISITS LIST / GRID ============ --}}
<div class="cv-grid">
    @forelse($visits as $visit)
        @php
            $isLead = $visit->visit_type === 'lead';
            $statusMeta = $visit->status_details;
            $modeMeta = $visit->meeting_mode_details;
            $latestUpdate = $visit->updates->first();
            $updateCount = $visit->updates->count();
        @endphp
        <div class="cv-card {{ $isLead ? 'cv-card-lead' : 'cv-card-client' }}" onclick="openViewTimelineModal({{ $visit->id }})">
            {{-- Accent bar --}}
            <div class="cv-card-accent" style="background:{{ $isLead ? '#8B5CF6' : ($visit->client ? ($visit->client->color ?: '#3B82F6') : '#3B82F6') }}"></div>

            {{-- Top Header Row --}}
            <div class="cv-card-head">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    @if($isLead)
                        <span class="cv-pill-lead"><i class="fa-solid fa-bullseye"></i> LEAD VISIT</span>
                    @else
                        <span class="cv-pill-client"><i class="fa-solid fa-building"></i> CLIENT VISIT</span>
                    @endif

                    <span class="cv-badge-status" style="color:{{ $statusMeta['color'] }};background:{{ $statusMeta['bg'] }}">
                        <i class="{{ $statusMeta['icon'] }}"></i> {{ $statusMeta['label'] }}
                    </span>
                </div>

                <div class="cv-card-menu" onclick="event.stopPropagation()">
                    <button type="button" class="cv-menu-trigger" title="Quick actions" onclick="this.nextElementSibling.classList.toggle('show')">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <div class="cv-dropdown-menu">
                        <button type="button" onclick="openViewTimelineModal({{ $visit->id }})"><i class="fa-solid fa-timeline"></i> Timeline & Updates</button>
                        <button type="button" onclick="openAddUpdateModal({{ $visit->id }}, '{{ addslashes($visit->display_name) }}')"><i class="fa-solid fa-plus-circle"></i> Add Update</button>
                        <button type="button" onclick="openEditVisitModal({{ $visit->id }})"><i class="fa-solid fa-pen"></i> Edit Details</button>
                        @if($isLead && $visit->status !== 'converted')
                            <button type="button" onclick="openConvertLeadModal({{ $visit->id }}, '{{ addslashes($visit->company_name ?: $visit->lead_name) }}', '{{ addslashes($visit->contact_person ?: $visit->lead_name) }}', '{{ $visit->contact_email }}', '{{ $visit->contact_phone }}')">
                                <i class="fa-solid fa-user-check" style="color:#059669"></i> Convert to Client
                            </button>
                        @endif
                        <hr style="margin:4px 0;border:none;border-top:1px solid var(--border)">
                        <button type="button" style="color:#EF4444" onclick="confirmDeleteVisit({{ $visit->id }}, '{{ addslashes($visit->display_name) }}')"><i class="fa-solid fa-trash"></i> Delete Visit</button>
                    </div>
                </div>
            </div>

            {{-- Main Identity --}}
            <div class="cv-card-identity">
                <div class="cv-card-avatar" style="background:{{ $isLead ? 'rgba(139,92,246,0.12)' : 'rgba(59,130,246,0.12)' }};color:{{ $isLead ? '#8B5CF6' : '#2563EB' }}">
                    @if(!$isLead && $visit->client && $visit->client->logo)
                        <img src="{{ asset('storage/' . $visit->client->logo) }}" alt="{{ $visit->client->name }}">
                    @elseif(!$isLead && $visit->client && $visit->client->emoji)
                        <span>{{ $visit->client->emoji }}</span>
                    @else
                        <i class="{{ $isLead ? 'fa-solid fa-bullseye' : 'fa-solid fa-building' }}"></i>
                    @endif
                </div>
                <div class="cv-card-names">
                    <h4 class="cv-name-title">{{ $visit->display_name }}</h4>
                    @if($visit->purpose)
                        <div class="cv-purpose-tag"><i class="fa-solid fa-tag"></i> {{ $visit->purpose }}</div>
                    @endif
                </div>
            </div>

            {{-- Info Rows --}}
            <div class="cv-info-list">
                {{-- Date & Time --}}
                <div class="cv-info-row">
                    <i class="fa-regular fa-calendar-days cv-info-icon" style="color:var(--primary)"></i>
                    <span class="cv-info-text">
                        <strong>{{ $visit->visit_date->format('M d, Y · h:i A') }}</strong>
                        <span class="cv-date-relative">({{ $visit->visit_date->diffForHumans() }})</span>
                    </span>
                </div>

                {{-- Meeting Mode / Location --}}
                <div class="cv-info-row">
                    <i class="{{ $modeMeta['icon'] }} cv-info-icon" style="color:#8B5CF6"></i>
                    <span class="cv-info-text">
                        {{ $modeMeta['label'] }}
                        @if($visit->location)
                            <span style="color:var(--text3)">· {{ $visit->location }}</span>
                        @endif
                    </span>
                </div>

                {{-- Contact Person & Channels --}}
                @if($visit->display_contact !== '—' || $visit->contact_phone || $visit->contact_email)
                    <div class="cv-info-row">
                        <i class="fa-solid fa-user cv-info-icon" style="color:#10B981"></i>
                        <span class="cv-info-text">
                            {{ $visit->display_contact }}
                            @if($visit->contact_phone)
                                <a href="tel:{{ $visit->contact_phone }}" class="cv-channel-link" onclick="event.stopPropagation()"><i class="fa-solid fa-phone"></i> {{ $visit->contact_phone }}</a>
                            @endif
                            @if($visit->contact_email)
                                <a href="mailto:{{ $visit->contact_email }}" class="cv-channel-link" onclick="event.stopPropagation()"><i class="fa-solid fa-envelope"></i> {{ $visit->contact_email }}</a>
                            @endif
                        </span>
                    </div>
                @endif

                {{-- Visited By --}}
                @if($visit->visitor)
                    <div class="cv-info-row">
                        <i class="fa-solid fa-user-tie cv-info-icon" style="color:#F59E0B"></i>
                        <span class="cv-info-text">Visited by <strong>{{ $visit->visitor->name }}</strong></span>
                    </div>
                @endif
            </div>

            {{-- Summary / Discussion Snippet --}}
            @if($visit->summary || $visit->discussion_points)
                <div class="cv-card-summary">
                    <i class="fa-solid fa-quote-left cv-quote-icon"></i>
                    <span>{{ Str::limit($visit->summary ?: $visit->discussion_points, 120) }}</span>
                </div>
            @endif

            {{-- Follow-up alert if present --}}
            @if($visit->next_follow_up)
                <div class="cv-followup-pill {{ $visit->next_follow_up->isPast() ? 'is-past' : '' }}">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Next Follow-up: <strong>{{ $visit->next_follow_up->format('M d, Y') }}</strong> ({{ $visit->next_follow_up->diffForHumans() }})</span>
                </div>
            @endif

            {{-- Bottom Footer with Updates Summary and Quick Actions --}}
            <div class="cv-card-footer" onclick="event.stopPropagation()">
                <div class="cv-updates-counter" onclick="openViewTimelineModal({{ $visit->id }})" title="View complete updates history">
                    <i class="fa-solid fa-timeline" style="color:var(--primary)"></i>
                    <span><strong>{{ $updateCount }}</strong> update{{ $updateCount !== 1 ? 's' : '' }}</span>
                    @if($latestUpdate)
                        <span style="font-size:10px;color:var(--text3);margin-left:4px">· Latest {{ $latestUpdate->created_at->diffForHumans() }}</span>
                    @endif
                </div>

                <div class="cv-card-actions">
                    <button type="button" class="btn-sec cv-action-btn" onclick="openAddUpdateModal({{ $visit->id }}, '{{ addslashes($visit->display_name) }}')" title="Add Follow-up / Update">
                        <i class="fa-solid fa-comment-medical"></i> Update
                    </button>
                    @if($isLead && $visit->status !== 'converted')
                        <button type="button" class="btn-primary cv-action-btn cv-btn-convert" onclick="openConvertLeadModal({{ $visit->id }}, '{{ addslashes($visit->company_name ?: $visit->lead_name) }}', '{{ addslashes($visit->contact_person ?: $visit->lead_name) }}', '{{ $visit->contact_email }}', '{{ $visit->contact_phone }}')" title="Convert to Client">
                            <i class="fa-solid fa-award"></i> Convert
                        </button>
                    @else
                        <button type="button" class="btn-sec cv-action-btn" onclick="openViewTimelineModal({{ $visit->id }})" title="View Details">
                            <i class="fa-solid fa-chevron-right"></i> Details
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="cv-empty-state">
            <div class="cv-empty-icon">
                <i class="fa-solid fa-person-walking-luggage"></i>
            </div>
            <div class="cv-empty-title">
                @if($search || $status || $visitorId || $clientId || $mode)
                    No visits match your current filters
                @else
                    No visits logged yet in this section
                @endif
            </div>
            <div class="cv-empty-desc">
                Log meetings, field visits, discovery pitches, and follow-ups with leads or active clients.
            </div>
            <div style="display:flex;gap:10px;justify-content:center;margin-top:16px;flex-wrap:wrap">
                <button class="btn-primary" onclick="openCreateVisitModal('client')">
                    <i class="fa-solid fa-plus"></i> Add Client Visit
                </button>
            </div>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
<div style="margin-top:24px">
    {{ $visits->links() }}
</div>

{{-- ========================================================================= --}}
{{-- ============================== MODALS =================================== --}}
{{-- ========================================================================= --}}

{{-- ============ 1. CREATE / LOG VISIT MODAL ============ --}}
<div class="modal-overlay" id="createVisitModal">
    <div class="modal" style="width:640px;max-height:92vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title" id="createVisitModalTitle">
                <i class="fa-solid fa-person-walking-luggage" style="color:var(--primary)"></i>
                <span>Add Client Visit</span>
            </div>
            <span class="modal-close" onclick="closeModal('createVisitModal')">&times;</span>
        </div>

        <form method="POST" action="{{ route('admin.visits.store') }}" autocomplete="off" id="createVisitForm">
            @csrf

            {{-- Type Toggle Pill (Lead vs Existing Client) --}}
            <div class="cv-form-type-picker">
                <label class="cv-type-choice">
                    <input type="radio" name="visit_type" value="client" id="typeChoiceClient" checked onchange="toggleVisitTypeForm('client')">
                    <div class="cv-choice-card">
                        <i class="fa-solid fa-building"></i>
                        <div>
                            <strong>Existing Client</strong>
                            <span>Visit to an active client in your system</span>
                        </div>
                    </div>
                </label>

                <label class="cv-type-choice">
                    <input type="radio" name="visit_type" value="lead" id="typeChoiceLead" onchange="toggleVisitTypeForm('lead')">
                    <div class="cv-choice-card">
                        <i class="fa-solid fa-bullseye"></i>
                        <div>
                            <strong>New Client / Lead</strong>
                            <span>Pitch / discovery meeting with a prospective client</span>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Section 1: Target Entity --}}
            <div class="cv-form-section-head">
                <i class="fa-solid fa-address-card"></i> Entity & Contact Details
            </div>

            {{-- Client Selector (Shown when visit_type === 'client') --}}
            <div id="formSectionClient" class="form-group" style="margin-bottom:14px">
                <label>Select Client <span style="color:#EF4444">*</span></label>
                <select name="client_id" id="createClientSelect" class="form-control" onchange="autoFillClientInfo(this)">
                    <option value="">-- Choose an existing client --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}"
                            data-person="{{ $c->contact_person }}"
                            data-email="{{ $c->contact_email }}"
                            data-phone="{{ $c->contact_phone }}"
                            data-name="{{ $c->name }}">
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Lead Form Fields (Shown when visit_type === 'lead') --}}
            <div id="formSectionLead" style="display:none;margin-bottom:14px">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group">
                        <label>Company / Brand Name <span style="color:#EF4444">*</span></label>
                        <input type="text" name="company_name" id="createLeadCompany" placeholder="e.g. Apex Innovations">
                    </div>
                    <div class="form-group">
                        <label>Lead / Contact Name</label>
                        <input type="text" name="lead_name" id="createLeadName" placeholder="e.g. John Smith">
                    </div>
                </div>
            </div>

            {{-- Contact Person, Phone, Email --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact_person" id="createContactPerson" placeholder="Primary contact">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="contact_phone" id="createContactPhone" placeholder="+91 98765 43210">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="contact_email" id="createContactEmail" placeholder="contact@example.com">
                </div>
            </div>

            {{-- Section 2: Visit Logistics --}}
            <div class="cv-form-section-head" style="margin-top:16px">
                <i class="fa-solid fa-location-crosshairs"></i> Visit Logistics & Purpose
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Visit Date & Time <span style="color:#EF4444">*</span></label>
                    <input type="datetime-local" name="visit_date" required value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
                <div class="form-group">
                    <label>Meeting Mode <span style="color:#EF4444">*</span></label>
                    <select name="meeting_mode" required>
                        <option value="in_person">In-Person</option>
                        <option value="client_office">Client Office</option>
                        <option value="agency_office">Agency Office</option>
                        <option value="virtual_call">Virtual / Video Call</option>
                        <option value="on_site">On-Site / Location</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Visited By / Attendee <span style="color:#EF4444">*</span></label>
                    <select name="visited_by" required>
                        @foreach($teamMembers as $m)
                            <option value="{{ $m->id }}" {{ $m->id === auth()->id() ? 'selected' : '' }}>
                                {{ $m->name }} ({{ ucfirst($m->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Visit Purpose <span style="color:#EF4444">*</span></label>
                    <input type="text" name="purpose" list="purposeOptions" required placeholder="e.g. Discovery Pitch, Strategy Review, Shoot Day">
                    <datalist id="purposeOptions">
                        <option value="Initial Discovery & Pitch">
                        <option value="Proposal & Pricing Discussion">
                        <option value="Contract Signing / Onboarding">
                        <option value="Monthly Strategy Review">
                        <option value="Campaign Performance Review">
                        <option value="Shoot Day / Production Coordination">
                        <option value="Relationship & Retention Visit">
                        <option value="Issue Resolution / Escalation">
                    </datalist>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label>Location / Address <span style="color:var(--text3);font-size:11px;font-weight:400">(Optional)</span></label>
                <input type="text" name="location" placeholder="e.g. 402 Business Tower, Sector 62 / Zoom Link">
            </div>

            {{-- Section 3: Outcomes & Follow-up --}}
            <div class="cv-form-section-head" style="margin-top:16px">
                <i class="fa-solid fa-list-check"></i> Discussion & Follow-up
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Initial Status <span style="color:#EF4444">*</span></label>
                    <select name="status" required>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="follow_up_needed">Follow-up Needed</option>
                        <option value="converted">Converted to Client</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Next Follow-up Date <span style="color:var(--text3);font-size:11px;font-weight:400">(Optional)</span></label>
                    <input type="date" name="next_follow_up">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label>Meeting Summary & Agenda Notes</label>
                <textarea name="summary" rows="3" placeholder="Key discussion points, requirements, client feedback, or agreed action items..."></textarea>
            </div>

            {{-- Actions --}}
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="closeModal('createVisitModal')">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Save Visit Record
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ 2. EDIT VISIT MODAL ============ --}}
<div class="modal-overlay" id="editVisitModal">
    <div class="modal" style="width:640px;max-height:92vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title">
                <i class="fa-solid fa-pen-to-square" style="color:var(--primary)"></i>
                <span>Edit Visit Details</span>
            </div>
            <span class="modal-close" onclick="closeModal('editVisitModal')">&times;</span>
        </div>

        <form method="POST" id="editVisitForm" enctype="multipart/form-data" autocomplete="off">
            @csrf
            @method('PUT')

            <input type="hidden" name="visit_type" id="editVisitType">

            <div id="editClientSection" class="form-group" style="margin-bottom:14px">
                <label>Client</label>
                <select name="client_id" id="editClientId" class="form-control">
                    <option value="">-- Choose Client --</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="editLeadSection" style="display:none;margin-bottom:14px">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group">
                        <label>Company / Brand Name</label>
                        <input type="text" name="company_name" id="editCompanyName">
                    </div>
                    <div class="form-group">
                        <label>Lead Name</label>
                        <input type="text" name="lead_name" id="editLeadName">
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact_person" id="editContactPerson">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="contact_phone" id="editContactPhone">
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="contact_email" id="editContactEmail">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Visit Date & Time</label>
                    <input type="datetime-local" name="visit_date" id="editVisitDate" required>
                </div>
                <div class="form-group">
                    <label>Meeting Mode</label>
                    <select name="meeting_mode" id="editMeetingMode" required>
                        <option value="in_person">In-Person</option>
                        <option value="client_office">Client Office</option>
                        <option value="agency_office">Agency Office</option>
                        <option value="virtual_call">Virtual / Video Call</option>
                        <option value="on_site">On-Site / Location</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Visited By</label>
                    <select name="visited_by" id="editVisitedBy" required>
                        @foreach($teamMembers as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ ucfirst($m->role) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Visit Purpose</label>
                    <input type="text" name="purpose" id="editPurpose" list="purposeOptions" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label>Location / Address</label>
                <input type="text" name="location" id="editLocation">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" id="editStatus" required>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="follow_up_needed">Follow-up Needed</option>
                        <option value="converted">Converted to Client</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Next Follow-up Date</label>
                    <input type="date" name="next_follow_up" id="editNextFollowUp">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label>Meeting Summary & Agenda Notes</label>
                <textarea name="summary" id="editSummary" rows="3"></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="closeModal('editVisitModal')">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ 3. VIEW TIMELINE & UPDATES DRAWER MODAL ============ --}}
<div class="modal-overlay" id="visitTimelineModal">
    <div class="modal" style="width:720px;max-height:92vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title" style="display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-timeline" style="color:var(--primary)"></i>
                <span id="vtModalTitle">Visit Timeline & History</span>
            </div>
            <span class="modal-close" onclick="closeModal('visitTimelineModal')">&times;</span>
        </div>

        {{-- Top Summary Card --}}
        <div id="vtTopCard" style="background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:18px">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px">
                <div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span id="vtTypePill"></span>
                        <span id="vtStatusBadge"></span>
                    </div>
                    <h3 id="vtEntityName" style="margin:8px 0 4px;font-size:17px;font-weight:800;color:var(--text)"></h3>
                    <div id="vtPurpose" style="font-size:12px;color:var(--primary);font-weight:600"></div>
                </div>
                <div style="text-align:right">
                    <button type="button" class="btn-primary" style="font-size:11.5px;padding:6px 12px" onclick="triggerAddUpdateFromTimeline()">
                        <i class="fa-solid fa-plus-circle"></i> Add Update
                    </button>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:10px;margin-top:14px;padding-top:12px;border-top:1px solid var(--border);font-size:12px;color:var(--text2)">
                <div><i class="fa-regular fa-calendar" style="color:var(--primary);margin-right:6px"></i> <span id="vtDate"></span></div>
                <div><i class="fa-solid fa-location-dot" style="color:#8B5CF6;margin-right:6px"></i> <span id="vtLocation"></span></div>
                <div><i class="fa-solid fa-user-tie" style="color:#F59E0B;margin-right:6px"></i> <span id="vtVisitor"></span></div>
                <div><i class="fa-solid fa-clock-rotate-left" style="color:#EC4899;margin-right:6px"></i> <span id="vtNextFollowUp"></span></div>
            </div>

            <div id="vtSummaryWrap" style="margin-top:12px;padding:10px 12px;background:var(--bg);border-radius:8px;font-size:12.5px;color:var(--text);line-height:1.5;display:none">
                <strong style="color:var(--text3);font-size:11px;text-transform:uppercase;display:block;margin-bottom:2px">Initial Notes & Agenda:</strong>
                <span id="vtSummaryText"></span>
            </div>
        </div>

        {{-- Inline Quick Add Update Bar --}}
        <div style="background:var(--card);border:1px dashed var(--border);border-radius:12px;padding:14px;margin-bottom:20px">
            <h5 style="margin:0 0 10px;font-size:13px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:6px">
                <i class="fa-solid fa-comment-medical" style="color:var(--primary)"></i> Log New Follow-up or Status Update
            </h5>
            <form id="vtInlineUpdateForm" onsubmit="submitInlineVisitUpdate(event)">
                <input type="hidden" id="vtInlineVisitId">
                <div style="display:grid;grid-template-columns:140px 150px 1fr;gap:8px;margin-bottom:8px">
                    <select id="vtInlineType" class="form-control" style="font-size:12px;padding:6px 8px">
                        <option value="note">Note</option>
                        <option value="call">Phone Call</option>
                        <option value="meeting">Meeting</option>
                        <option value="proposal">Proposal</option>
                        <option value="follow_up">Follow-up</option>
                        <option value="status_change">Status Change</option>
                    </select>
                    <select id="vtInlineStatus" class="form-control" style="font-size:12px;padding:6px 8px">
                        <option value="">Status: Unchanged</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="follow_up_needed">Follow-up Needed</option>
                        <option value="converted">Converted to Client</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <input type="date" id="vtInlineFollowUp" class="form-control" style="font-size:12px;padding:6px 8px" title="Next follow up date">
                </div>
                <div style="display:flex;gap:8px">
                    <input type="text" id="vtInlineNote" required placeholder="Write update note, call outcome, client response..." style="flex:1;font-size:12px;padding:8px 10px;border-radius:8px;border:1px solid var(--border);background:var(--card2)">
                    <button type="submit" class="btn-primary" style="font-size:12px;padding:8px 14px;white-space:nowrap">
                        <i class="fa-solid fa-paper-plane"></i> Post
                    </button>
                </div>
            </form>
        </div>

        {{-- Timeline Log Section --}}
        <div>
            <h4 style="font-size:14px;font-weight:800;color:var(--text);margin-bottom:14px;display:flex;align-items:center;gap:6px">
                <i class="fa-solid fa-list-timeline" style="color:var(--primary)"></i> Update History & Timeline
            </h4>
            <div id="vtTimelineList" class="cv-timeline">
                {{-- Populated dynamically via JS --}}
            </div>
        </div>
    </div>
</div>

{{-- ============ 4. ADD QUICK UPDATE MODAL ============ --}}
<div class="modal-overlay" id="addUpdateModal">
    <div class="modal" style="width:500px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title">
                <i class="fa-solid fa-comment-medical" style="color:var(--primary)"></i>
                <span>Add Visit Update</span>
            </div>
            <span class="modal-close" onclick="closeModal('addUpdateModal')">&times;</span>
        </div>

        <form method="POST" id="addUpdateForm" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <div style="background:var(--primary-dim);padding:10px 12px;border-radius:8px;font-size:12.5px;color:var(--primary);margin-bottom:14px">
                Adding update for: <strong id="updateModalEntityName"></strong>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-group">
                    <label>Update Type</label>
                    <select name="update_type" required>
                        <option value="note">General Note</option>
                        <option value="call">Phone Call Log</option>
                        <option value="meeting">Follow-up Discussion</option>
                        <option value="proposal">Proposal / Quote Sent</option>
                        <option value="follow_up">Action Item / Follow-up</option>
                        <option value="status_change">Status Update</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Update Status</label>
                    <select name="status">
                        <option value="">-- Keep Current Status --</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="follow_up_needed">Follow-up Needed</option>
                        <option value="converted">Converted to Client</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label>Next Follow-up Date <span style="color:var(--text3);font-size:11px;font-weight:400">(Optional)</span></label>
                <input type="date" name="next_follow_up">
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label>Update Description / Discussion Outcome <span style="color:#EF4444">*</span></label>
                <textarea name="note" rows="3" required placeholder="Details about this follow-up, client conversation, changes in requirements, etc..."></textarea>
            </div>

            <div class="form-group" style="margin-bottom:14px">
                <label><i class="fa-solid fa-paperclip"></i> Attachment <span style="color:var(--text3);font-size:11px;font-weight:400">(Optional)</span></label>
                <input type="file" name="attachment" style="font-size:12px">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="closeModal('addUpdateModal')">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Post Update
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ 5. CONVERT LEAD TO CLIENT MODAL ============ --}}
<div class="modal-overlay" id="convertLeadModal">
    <div class="modal" style="width:520px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title">
                <i class="fa-solid fa-award" style="color:#10B981"></i>
                <span>Convert Lead to Active Client</span>
            </div>
            <span class="modal-close" onclick="closeModal('convertLeadModal')">&times;</span>
        </div>

        <form method="POST" id="convertLeadForm" autocomplete="off">
            @csrf
            <div style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);padding:12px 14px;border-radius:10px;font-size:12.5px;color:#059669;margin-bottom:16px;display:flex;align-items:flex-start;gap:8px">
                <i class="fa-solid fa-circle-check" style="margin-top:2px"></i>
                <span>This will create a new official Client profile and mark the lead visit as <strong>Converted</strong>.</span>
            </div>

            <div class="form-group" style="margin-bottom:12px">
                <label>Official Client Name <span style="color:#EF4444">*</span></label>
                <input type="text" name="name" id="convertClientName" required>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div class="form-group">
                    <label>Category / Industry</label>
                    <input type="text" name="category" placeholder="e.g. Healthcare, Retail">
                </div>
                <div class="form-group">
                    <label>Brand Color</label>
                    <input type="color" name="color" value="#4F6DF0" style="width:100%;height:38px;padding:2px;border:1px solid var(--border);border-radius:8px;cursor:pointer;background:var(--bg)">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact_person" id="convertContactPerson">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="contact_phone" id="convertContactPhone">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:12px">
                <label>Email</label>
                <input type="email" name="contact_email" id="convertContactEmail">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="closeModal('convertLeadModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background:#10B981;border-color:#10B981">
                    <i class="fa-solid fa-check"></i> Convert & Create Client
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ 6. DELETE VISIT MODAL ============ --}}
<div class="modal-overlay" id="deleteVisitModal">
    <div class="modal" style="width:420px;text-align:center;padding:32px 24px">
        <div style="font-size:36px;color:#EF4444;margin-bottom:12px"><i class="fa-solid fa-trash-can"></i></div>
        <h3 style="margin:0 0 6px;font-size:17px;font-weight:800;color:var(--text)">Delete Visit Record?</h3>
        <p style="font-size:13px;color:var(--text3);margin:0 0 20px">
            Are you sure you want to delete the visit for <strong id="deleteVisitName" style="color:var(--text)"></strong> and all its update history? This cannot be undone.
        </p>
        <form id="deleteVisitForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display:flex;gap:10px;justify-content:center">
                <button type="button" class="btn-sec" onclick="closeModal('deleteVisitModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background:#EF4444;border-color:#EF4444">
                    <i class="fa-solid fa-trash"></i> Confirm Delete
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- ============================== STYLES =================================== --}}
{{-- ========================================================================= --}}
<style>
/* Toast notification */
.cv-toast { display:flex; align-items:center; gap:10px; background:linear-gradient(135deg,#10B98115,#10B98108); border:1px solid #10B98130; color:#059669; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:600; margin-bottom:16px; animation:cv-slideDown .3s ease; }
.cv-toast-error { background:linear-gradient(135deg,#EF444415,#EF444408); border-color:#EF444430; color:#DC2626; }
@keyframes cv-slideDown { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }

/* Hero Section */
.cv-hero { background:linear-gradient(135deg,rgba(79,109,240,0.08) 0%,rgba(139,92,246,0.08) 100%); border:1px solid var(--border); border-radius:16px; padding:22px 26px; margin-bottom:20px; }
.cv-hero-content { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; }
.cv-hero-title { font-size:22px; font-weight:800; color:var(--text); letter-spacing:-0.3px; display:flex; align-items:center; }
.cv-hero-sub { font-size:13px; color:var(--text3); margin-top:3px; }
.cv-hero-actions { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }

/* Metrics Grid */
.cv-metrics { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin-bottom:20px; }
.cv-metric { position:relative; background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px; display:flex; align-items:center; gap:14px; overflow:hidden; transition:all .2s ease; }
.cv-metric:hover { transform:translateY(-2px); box-shadow:0 4px 14px rgba(0,0,0,0.06); }
.cv-metric-icon { width:44px; height:44px; border-radius:12px; background:var(--mg); color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.cv-metric-val { font-size:22px; font-weight:800; color:var(--text); line-height:1; }
.cv-metric-lbl { font-size:11.5px; color:var(--text3); margin-top:4px; font-weight:600; }
.cv-metric-glow { position:absolute; right:-20px; bottom:-20px; width:60px; height:60px; border-radius:50%; background:var(--mc); opacity:0.06; filter:blur(16px); }

/* Tabs Bar */
.cv-tabs-bar { margin-bottom:16px; }
.cv-tabs { display:flex; align-items:center; gap:8px; overflow-x:auto; padding-bottom:4px; }
.cv-tab { display:inline-flex; align-items:center; gap:8px; padding:9px 16px; border-radius:10px; font-size:13px; font-weight:600; color:var(--text2); background:var(--card); border:1px solid var(--border); text-decoration:none; transition:all .15s ease; white-space:nowrap; }
.cv-tab:hover { color:var(--text); background:var(--card2); }
.cv-tab.active { background:var(--primary); color:#fff; border-color:var(--primary); box-shadow:0 2px 8px rgba(79,109,240,0.3); }
.cv-tab-count { font-size:11px; font-weight:700; background:rgba(0,0,0,0.08); color:inherit; padding:1px 7px; border-radius:6px; }
.cv-tab.active .cv-tab-count { background:rgba(255,255,255,0.25); color:#fff; }

/* Toolbar */
.cv-toolbar { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
.cv-toolbar-form { display:flex; align-items:center; gap:10px; flex-wrap:wrap; flex:1; }
.cv-search-wrap { position:relative; min-width:240px; flex:1; }
.cv-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text3); font-size:13px; }
.cv-search-input { width:100%; padding:8px 12px 8px 34px; font-size:12.5px; border-radius:8px; border:1px solid var(--border); background:var(--card2); color:var(--text); }
.cv-filter-select { padding:8px 12px; font-size:12px; border-radius:8px; border:1px solid var(--border); background:var(--card2); color:var(--text); font-weight:600; cursor:pointer; }
.cv-clear-btn { width:32px; height:32px; border-radius:8px; background:rgba(239,68,68,0.1); color:#EF4444; display:flex; align-items:center; justify-content:center; text-decoration:none; font-size:13px; }
.cv-toolbar-count { font-size:12px; color:var(--text3); font-weight:600; }

/* Cards Grid */
.cv-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:18px; }
.cv-card { position:relative; background:var(--card); border:1px solid var(--border); border-radius:14px; padding:18px; display:flex; flex-direction:column; gap:12px; cursor:pointer; transition:all .2s ease; overflow:hidden; }
.cv-card:hover { transform:translateY(-3px); box-shadow:0 6px 20px rgba(0,0,0,0.08); border-color:rgba(79,109,240,0.4); }
.cv-card-accent { position:absolute; top:0; left:0; right:0; height:4px; }
.cv-card-head { display:flex; align-items:center; justify-content:space-between; gap:8px; }

/* Pills & Badges */
.cv-pill-lead { font-size:10px; font-weight:800; background:rgba(139,92,246,0.15); color:#8B5CF6; padding:3px 8px; border-radius:6px; letter-spacing:0.4px; }
.cv-pill-client { font-size:10px; font-weight:800; background:rgba(59,130,246,0.15); color:#2563EB; padding:3px 8px; border-radius:6px; letter-spacing:0.4px; }
.cv-badge-status { font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:5px; }

/* Identity & Names */
.cv-card-identity { display:flex; align-items:center; gap:12px; }
.cv-card-avatar { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; overflow:hidden; }
.cv-card-avatar img { width:100%; height:100%; object-fit:cover; }
.cv-card-names { flex:1; min-width:0; }
.cv-name-title { margin:0; font-size:15px; font-weight:800; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cv-purpose-tag { font-size:11.5px; color:var(--text3); margin-top:2px; display:flex; align-items:center; gap:4px; }

/* Info List */
.cv-info-list { display:flex; flex-direction:column; gap:6px; background:var(--bg); border:1px solid var(--border); border-radius:10px; padding:10px 12px; font-size:12px; }
.cv-info-row { display:flex; align-items:center; gap:8px; color:var(--text2); }
.cv-info-icon { font-size:12px; width:16px; text-align:center; flex-shrink:0; }
.cv-info-text { flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cv-date-relative { font-size:11px; color:var(--text3); font-weight:400; margin-left:4px; }
.cv-channel-link { color:var(--text2); text-decoration:none; margin-left:8px; font-weight:600; display:inline-flex; align-items:center; gap:4px; }
.cv-channel-link:hover { color:var(--primary); }

/* Summary Snippet */
.cv-card-summary { font-size:12px; color:var(--text2); line-height:1.4; background:var(--card2); border-left:3px solid var(--primary); padding:6px 10px; border-radius:0 6px 6px 0; display:flex; align-items:flex-start; gap:6px; }
.cv-quote-icon { font-size:10px; color:var(--text3); margin-top:2px; }

/* Follow-up pill */
.cv-followup-pill { font-size:11.5px; font-weight:600; color:#D97706; background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.2); padding:6px 10px; border-radius:8px; display:flex; align-items:center; gap:6px; }
.cv-followup-pill.is-past { color:#EF4444; background:rgba(239,68,68,0.1); border-color:rgba(239,68,68,0.2); }

/* Card Footer */
.cv-card-footer { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:auto; padding-top:12px; border-top:1px solid var(--border); }
.cv-updates-counter { font-size:11.5px; color:var(--text2); cursor:pointer; display:flex; align-items:center; gap:5px; }
.cv-updates-counter:hover { color:var(--primary); }
.cv-card-actions { display:flex; align-items:center; gap:6px; }
.cv-action-btn { font-size:11px; padding:6px 10px; border-radius:7px; gap:4px; }
.cv-btn-convert { background:#10B981; border-color:#10B981; color:#fff; }
.cv-btn-convert:hover { background:#059669; }

/* Empty state */
.cv-empty-state { grid-column:1/-1; background:var(--card); border:1px dashed var(--border); border-radius:16px; padding:48px 24px; text-align:center; }
.cv-empty-icon { width:60px; height:60px; border-radius:50%; background:var(--primary-dim); color:var(--primary); font-size:26px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:14px; }
.cv-empty-title { font-size:16px; font-weight:800; color:var(--text); margin-bottom:4px; }
.cv-empty-desc { font-size:13px; color:var(--text3); max-width:440px; margin:0 auto; }

/* Dropdown Menu */
.cv-card-menu { position:relative; }
.cv-menu-trigger { background:none; border:none; color:var(--text3); font-size:14px; cursor:pointer; padding:4px 8px; border-radius:6px; }
.cv-menu-trigger:hover { background:var(--card2); color:var(--text); }
.cv-dropdown-menu { display:none; position:absolute; right:0; top:100%; background:var(--card); border:1px solid var(--border); border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.15); z-index:20; min-width:180px; padding:6px; }
.cv-dropdown-menu.show { display:block; }
.cv-dropdown-menu button { width:100%; text-align:left; background:none; border:none; padding:8px 12px; font-size:12px; font-weight:600; color:var(--text); border-radius:6px; cursor:pointer; display:flex; align-items:center; gap:8px; }
.cv-dropdown-menu button:hover { background:var(--card2); color:var(--primary); }

/* Form Type Picker */
.cv-form-type-picker { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px; }
.cv-type-choice input { display:none; }
.cv-choice-card { border:2px solid var(--border); border-radius:12px; padding:14px; display:flex; align-items:center; gap:12px; cursor:pointer; background:var(--bg); transition:all .15s ease; }
.cv-choice-card i { font-size:22px; color:var(--text3); }
.cv-choice-card strong { display:block; font-size:13px; color:var(--text); }
.cv-choice-card span { font-size:11px; color:var(--text3); }
.cv-type-choice input:checked + .cv-choice-card { border-color:var(--primary); background:var(--primary-dim); }
.cv-type-choice input:checked + .cv-choice-card i { color:var(--primary); }

.cv-form-section-head { font-size:12px; font-weight:700; color:var(--text2); text-transform:uppercase; letter-spacing:.4px; margin-bottom:12px; padding-bottom:6px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:6px; }

/* Timeline Drawer Styles */
.cv-timeline { position:relative; padding-left:24px; display:flex; flex-direction:column; gap:16px; margin-top:10px; }
.cv-timeline::before { content:''; position:absolute; left:7px; top:8px; bottom:8px; width:2px; background:var(--border); }
.cv-tl-item { position:relative; display:flex; flex-direction:column; gap:4px; background:var(--card2); border:1px solid var(--border); border-radius:10px; padding:12px 14px; }
.cv-tl-dot { position:absolute; left:-24px; top:12px; width:16px; height:16px; border-radius:50%; background:#fff; border:3px solid var(--primary); transform:translateX(-50%); }
.cv-tl-head { display:flex; align-items:center; justify-content:space-between; gap:8px; font-size:11.5px; }
.cv-tl-tag { font-weight:700; display:inline-flex; align-items:center; gap:4px; }
.cv-tl-time { color:var(--text3); font-size:11px; }
.cv-tl-note { font-size:12.5px; color:var(--text); line-height:1.45; margin-top:2px; }
.cv-tl-footer { display:flex; align-items:center; gap:12px; font-size:11px; color:var(--text3); margin-top:4px; padding-top:6px; border-top:1px dashed var(--border); }
</style>

{{-- ========================================================================= --}}
{{-- ============================ JAVASCRIPT ================================= --}}
{{-- ========================================================================= --}}
<script>
// Auto-dismiss toasts
const flash = document.getElementById('flashMsg');
if (flash) setTimeout(() => flash.remove(), 4000);
const flashErr = document.getElementById('flashMsgErr');
if (flashErr) setTimeout(() => flashErr.remove(), 6000);

// Close open dropdowns when clicking outside
document.addEventListener('click', () => {
    document.querySelectorAll('.cv-dropdown-menu.show').forEach(m => m.classList.remove('show'));
});

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

// Open Log Visit Modal
function openCreateVisitModal(type = 'client') {
    if (type === 'lead') {
        document.getElementById('typeChoiceLead').checked = true;
        toggleVisitTypeForm('lead');
    } else {
        document.getElementById('typeChoiceClient').checked = true;
        toggleVisitTypeForm('client');
    }
    document.getElementById('createVisitModal').classList.add('show');
}

function toggleVisitTypeForm(type) {
    const clientSec = document.getElementById('formSectionClient');
    const leadSec = document.getElementById('formSectionLead');
    const titleSpan = document.getElementById('createVisitModalTitle').querySelector('span');

    if (type === 'lead') {
        clientSec.style.display = 'none';
        leadSec.style.display = 'block';
        titleSpan.textContent = 'Add Client Visit — New Client';
        document.getElementById('createClientSelect').required = false;
        document.getElementById('createLeadCompany').required = true;
    } else {
        clientSec.style.display = 'block';
        leadSec.style.display = 'none';
        titleSpan.textContent = 'Add Client Visit — Existing Client';
        document.getElementById('createClientSelect').required = true;
        document.getElementById('createLeadCompany').required = false;
    }
}

function autoFillClientInfo(select) {
    const opt = select.selectedOptions[0];
    if (opt && opt.value) {
        document.getElementById('createContactPerson').value = opt.dataset.person || '';
        document.getElementById('createContactEmail').value = opt.dataset.email || '';
        document.getElementById('createContactPhone').value = opt.dataset.phone || '';
    }
}

// Open Quick Add Update Modal
function openAddUpdateModal(visitId, entityName) {
    document.getElementById('addUpdateForm').action = '/admin/visits/' + visitId + '/updates';
    document.getElementById('updateModalEntityName').textContent = entityName;
    document.getElementById('addUpdateModal').classList.add('show');
}

// Open Convert Lead Modal
function openConvertLeadModal(visitId, companyName, contactPerson, contactEmail, contactPhone) {
    document.getElementById('convertLeadForm').action = '/admin/visits/' + visitId + '/convert';
    document.getElementById('convertClientName').value = companyName || '';
    document.getElementById('convertContactPerson').value = contactPerson || '';
    document.getElementById('convertContactEmail').value = contactEmail || '';
    document.getElementById('convertContactPhone').value = contactPhone || '';
    document.getElementById('convertLeadModal').classList.add('show');
}

// Open Delete Confirm Modal
function confirmDeleteVisit(visitId, name) {
    document.getElementById('deleteVisitForm').action = '/admin/visits/' + visitId;
    document.getElementById('deleteVisitName').textContent = name;
    document.getElementById('deleteVisitModal').classList.add('show');
}

// Open Edit Visit Modal
function openEditVisitModal(visitId) {
    fetch('/admin/visits/' + visitId, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        const v = data.visit;
        document.getElementById('editVisitForm').action = '/admin/visits/' + v.id;
        document.getElementById('editVisitType').value = v.visit_type;

        if (v.visit_type === 'lead') {
            document.getElementById('editClientSection').style.display = 'none';
            document.getElementById('editLeadSection').style.display = 'block';
            document.getElementById('editCompanyName').value = v.company_name || '';
            document.getElementById('editLeadName').value = v.lead_name || '';
        } else {
            document.getElementById('editClientSection').style.display = 'block';
            document.getElementById('editLeadSection').style.display = 'none';
            document.getElementById('editClientId').value = v.client_id || '';
        }

        document.getElementById('editContactPerson').value = v.contact_person || '';
        document.getElementById('editContactPhone').value = v.contact_phone || '';
        document.getElementById('editContactEmail').value = v.contact_email || '';
        
        // Format visit_date for datetime-local
        if (v.visit_date) {
            const dt = new Date(v.visit_date);
            const pad = num => String(num).padStart(2, '0');
            const formatted = `${dt.getFullYear()}-${pad(dt.getMonth() + 1)}-${pad(dt.getDate())}T${pad(dt.getHours())}:${pad(dt.getMinutes())}`;
            document.getElementById('editVisitDate').value = formatted;
        }

        document.getElementById('editMeetingMode').value = v.meeting_mode || 'in_person';
        document.getElementById('editVisitedBy').value = v.visited_by || '';
        document.getElementById('editPurpose').value = v.purpose || '';
        document.getElementById('editLocation').value = v.location || '';
        document.getElementById('editStatus').value = v.status || 'scheduled';
        document.getElementById('editNextFollowUp').value = v.next_follow_up ? v.next_follow_up.split('T')[0] : '';
        document.getElementById('editSummary').value = v.summary || '';

        const attachEl = document.getElementById('editCurrentAttachment');
        if (attachEl) {
            if (v.attachment) {
                attachEl.innerHTML = `<span style="color:#059669"><i class="fa-solid fa-file"></i> ${v.attachment.split('/').pop()}</span> <label style="margin-left:10px;color:#EF4444;cursor:pointer"><input type="checkbox" name="delete_attachment" value="1"> Remove file</label>`;
            } else {
                attachEl.innerHTML = '';
            }
        }

        document.getElementById('editVisitModal').classList.add('show');
    })
    .catch(err => {
        console.error(err);
        alert('Failed to load visit details.');
    });
}

// Open View Timeline & Updates Modal
let currentTimelineVisitId = null;
function openViewTimelineModal(visitId) {
    currentTimelineVisitId = visitId;
    document.getElementById('vtInlineVisitId').value = visitId;
    document.getElementById('vtInlineNote').value = '';

    fetch('/admin/visits/' + visitId, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        const v = data.visit;
        const statusMeta = data.status_details;
        const modeMeta = data.meeting_mode_details;

        document.getElementById('vtModalTitle').textContent = `Timeline — ${data.display_name}`;
        document.getElementById('vtEntityName').textContent = data.display_name;
        document.getElementById('vtPurpose').textContent = v.purpose ? `Purpose: ${v.purpose}` : '';

        // Pills
        document.getElementById('vtTypePill').innerHTML = v.visit_type === 'lead'
            ? `<span class="cv-pill-lead"><i class="fa-solid fa-bullseye"></i> LEAD VISIT</span>`
            : `<span class="cv-pill-client"><i class="fa-solid fa-building"></i> CLIENT VISIT</span>`;

        document.getElementById('vtStatusBadge').innerHTML = `<span class="cv-badge-status" style="color:${statusMeta.color};background:${statusMeta.bg}"><i class="${statusMeta.icon}"></i> ${statusMeta.label}</span>`;

        document.getElementById('vtDate').textContent = new Date(v.visit_date).toLocaleString('en-US', { dateStyle:'medium', timeStyle:'short' });
        document.getElementById('vtLocation').textContent = `${modeMeta.label} ${v.location ? '(' + v.location + ')' : ''}`;
        document.getElementById('vtVisitor').textContent = v.visitor ? v.visitor.name : 'Not assigned';
        document.getElementById('vtNextFollowUp').textContent = v.next_follow_up ? `Follow-up: ${new Date(v.next_follow_up).toLocaleDateString('en-US', { dateStyle:'medium' })}` : 'No follow-up set';

        const summaryWrap = document.getElementById('vtSummaryWrap');
        if (v.summary || v.discussion_points) {
            summaryWrap.style.display = 'block';
            document.getElementById('vtSummaryText').textContent = v.summary || v.discussion_points;
        } else {
            summaryWrap.style.display = 'none';
        }

        // Render timeline
        renderTimelineList(v.updates || []);

        document.getElementById('visitTimelineModal').classList.add('show');
    })
    .catch(err => {
        console.error(err);
        alert('Failed to load visit timeline.');
    });
}

function renderTimelineList(updates) {
    const listEl = document.getElementById('vtTimelineList');
    if (!updates.length) {
        listEl.innerHTML = '<div style="color:var(--text3);font-size:12px;padding:12px 0">No updates logged yet. Post an update above!</div>';
        return;
    }

    const typeIcons = {
        call: { label: 'Phone Call', icon: 'fa-solid fa-phone', color: '#3B82F6' },
        meeting: { label: 'Meeting', icon: 'fa-solid fa-handshake', color: '#8B5CF6' },
        proposal: { label: 'Proposal', icon: 'fa-solid fa-file-invoice-dollar', color: '#10B981' },
        status_change: { label: 'Status Update', icon: 'fa-solid fa-arrows-rotate', color: '#F59E0B' },
        follow_up: { label: 'Follow-up Note', icon: 'fa-solid fa-calendar-check', color: '#EC4899' },
        note: { label: 'Note', icon: 'fa-solid fa-comment-dots', color: '#6366F1' },
    };

    listEl.innerHTML = updates.map(u => {
        const meta = typeIcons[u.update_type] || typeIcons.note;
        const timeStr = new Date(u.created_at).toLocaleString('en-US', { dateStyle:'medium', timeStyle:'short' });
        const userName = u.user ? u.user.name : 'System';

        return `
            <div class="cv-tl-item">
                <div class="cv-tl-dot" style="border-color:${meta.color}"></div>
                <div class="cv-tl-head">
                    <span class="cv-tl-tag" style="color:${meta.color}"><i class="${meta.icon}"></i> ${meta.label}</span>
                    <span class="cv-tl-time">${timeStr}</span>
                </div>
                <div class="cv-tl-note">${escapeHtml(u.note)}</div>
                <div class="cv-tl-footer">
                    <span><i class="fa-solid fa-user-circle"></i> ${escapeHtml(userName)}</span>
                    ${u.status_to ? `<span style="font-weight:700"><i class="fa-solid fa-circle-check"></i> Status: ${u.status_to.replace(/_/g, ' ')}</span>` : ''}
                    ${u.next_follow_up ? `<span><i class="fa-regular fa-calendar-check"></i> Follow-up: ${new Date(u.next_follow_up).toLocaleDateString()}</span>` : ''}
                    ${u.attachment ? `<a href="/storage/${u.attachment}" target="_blank" download style="color:var(--primary);text-decoration:none"><i class="fa-solid fa-paperclip"></i> View File</a>` : ''}
                </div>
            </div>
        `;
    }).join('');
}

function triggerAddUpdateFromTimeline() {
    document.getElementById('vtInlineNote').focus();
}

function submitInlineVisitUpdate(e) {
    e.preventDefault();
    const visitId = document.getElementById('vtInlineVisitId').value;
    const note = document.getElementById('vtInlineNote').value;
    const updateType = document.getElementById('vtInlineType').value;
    const status = document.getElementById('vtInlineStatus').value;
    const nextFollowUp = document.getElementById('vtInlineFollowUp').value;

    if (!note.trim()) return;

    fetch('/admin/visits/' + visitId + '/updates', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            update_type: updateType,
            note: note,
            status: status || null,
            next_follow_up: nextFollowUp || null,
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            document.getElementById('vtInlineNote').value = '';
            // Refresh timeline modal
            openViewTimelineModal(visitId);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Failed to post update.');
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}
</script>

@endsection
