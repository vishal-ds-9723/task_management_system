@extends('layouts.app')

@section('content')
<div class="cl-hero">
    <div class="cl-hero-content">
        <div class="cl-hero-text">
            <div class="cl-hero-title">Client Overview</div>
            <div class="cl-hero-sub">Manage and monitor your agency's client portfolio</div>
        </div>
        <button class="cl-hero-btn" onclick="openAddClient()"><i class="fa-solid fa-plus"></i> New Client</button>
    </div>
</div>

{{-- Success Flash --}}
@if(session('success'))
<div class="cl-toast" id="flashMsg">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    <span onclick="this.parentElement.remove()" style="cursor:pointer;margin-left:auto;opacity:0.6">&times;</span>
</div>
@endif

{{-- ===== STATS DASHBOARD ===== --}}
<div class="cl-metrics">
    <div class="cl-metric" style="--mc:#3B82F6;--mg:linear-gradient(135deg,#3B82F6,#60A5FA)">
        <div class="cl-metric-icon"><i class="fa-solid fa-building"></i></div>
        <div class="cl-metric-body">
            <div class="cl-metric-val">{{ $totalClients }}</div>
            <div class="cl-metric-lbl">Active Clients</div>
        </div>
        <div class="cl-metric-glow"></div>
    </div>
    <div class="cl-metric" style="--mc:#8B5CF6;--mg:linear-gradient(135deg,#8B5CF6,#A78BFA)">
        <div class="cl-metric-icon"><i class="fa-solid fa-list-check"></i></div>
        <div class="cl-metric-body">
            <div class="cl-metric-val">{{ $totalActiveTasks }}</div>
            <div class="cl-metric-lbl">Active Tasks</div>
        </div>
        <div class="cl-metric-glow"></div>
    </div>
    <div class="cl-metric" style="--mc:#10B981;--mg:linear-gradient(135deg,#10B981,#34D399)">
        <div class="cl-metric-icon"><i class="fa-solid fa-chart-line"></i></div>
        <div class="cl-metric-body">
            <div class="cl-metric-val">{{ $avgCompletion }}%</div>
            <div class="cl-metric-lbl">Avg. Completion</div>
            <div class="cl-metric-bar"><div class="cl-metric-bar-fill" style="width:{{ $avgCompletion }}%;background:linear-gradient(90deg,#10B981,#34D399)"></div></div>
        </div>
        <div class="cl-metric-glow"></div>
    </div>
    <div class="cl-metric" style="--mc:#F59E0B;--mg:linear-gradient(135deg,#F59E0B,#FBBF24);{{ $topClient ? 'cursor:pointer' : '' }}" {!! $topClient ? 'onclick="window.location.href=\'' . route('admin.clients.show', $topClient) . '\'"' : '' !!}>
        <div class="cl-metric-icon"><i class="fa-solid fa-trophy"></i></div>
        <div class="cl-metric-body">
            @if($topClient)
                <div class="cl-metric-val">{{ $topClient->name }}</div>
                <div class="cl-metric-lbl">{{ $topClient->active_tasks }} tasks · {{ $topClient->completion_pct }}% done</div>
            @else
                <div class="cl-metric-val">—</div>
                <div class="cl-metric-lbl">No top client yet</div>
            @endif
        </div>
        <div class="cl-metric-glow"></div>
    </div>
</div>

{{-- ===== TOOLBAR: Search + Filters ===== --}}
<div id="clientsResultsArea">
<div class="cl-toolbar">
    <form method="GET" action="{{ route('admin.clients') }}" class="cl-toolbar-form" id="clientsFilterForm">
        <div class="cl-search-wrap">
            <i class="fa-solid fa-magnifying-glass cl-search-icon"></i>
            <input type="text" name="search" id="clientsSearchInput" value="{{ $search }}" placeholder="Search clients..." class="cl-search-input" autocomplete="off">
        </div>
        @if($categories->count())
        <select name="category" class="cl-filter-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ $categoryFilter === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        @endif
        <select name="sort" class="cl-filter-select" onchange="this.form.submit()">
            <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Sort: A–Z</option>
            <option value="tasks" {{ $sortBy === 'tasks' ? 'selected' : '' }}>Sort: Most Tasks</option>
            <option value="completion" {{ $sortBy === 'completion' ? 'selected' : '' }}>Sort: Completion %</option>
            <option value="recent" {{ $sortBy === 'recent' ? 'selected' : '' }}>Sort: Newest</option>
        </select>
        <label class="cl-toggle-inactive">
            <input type="checkbox" name="inactive" value="1" {{ $showInactive ? 'checked' : '' }} onchange="this.form.submit()">
            <span>Show Inactive</span>
        </label>
        @if($search || $categoryFilter || $sortBy !== 'name' || $showInactive)
            <a href="{{ route('admin.clients') }}" class="cl-clear-btn" title="Clear filters">
                <i class="fa-solid fa-xmark"></i>
            </a>
        @endif
    </form>
    <div class="cl-toolbar-count">
        {{ $clients->count() }} client{{ $clients->count() !== 1 ? 's' : '' }}
    </div>
</div>

{{-- ===== CLIENT GRID ===== --}}
<div class="client-grid" id="clientGrid">
    @forelse($clients as $client)
        <div class="client-card cl-card-v2 {{ !$client->is_active ? 'cl-card-inactive' : '' }}" style="--brand:{{ $client->color }}" onclick="window.location='{{ route('admin.clients.show', $client) }}'">
            <div class="cl-v2-accent"></div>
            <div class="cc-actions">
                <button class="cc-action-btn" onclick='event.stopPropagation(); openEditClient(@json($client))' title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
                <button class="cc-action-btn cc-action-danger" onclick="event.stopPropagation(); confirmDeleteClient({{ $client->id }}, '{{ addslashes($client->name) }}')" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
            </div>
            @if(!$client->is_active)
                <span class="cl-badge-inactive"><i class="fa-solid fa-circle-pause" style="font-size:9px"></i> Inactive</span>
            @endif
            <div class="cl-v2-header">
                @if($client->logo)
                    <span class="cl-v2-logo"><img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }} logo"></span>
                @else
                    <div class="cl-v2-avatar">
                        {{ $client->emoji ?: strtoupper(substr($client->name, 0, 1)) }}
                    </div>
                @endif
                <div class="cl-v2-identity">
                    <div class="cl-v2-name">{{ $client->name }}</div>
                    @if($client->category)
                        <span class="cl-v2-cat">{{ $client->category }}</span>
                    @endif
                </div>
            </div>
            <div class="cl-v2-body">
                <div class="cl-v2-progress">
                    <div class="cl-v2-progress-top">
                        <span class="cl-v2-progress-lbl">Progress</span>
                        <span class="cl-v2-progress-pct" style="color:{{ $client->completion_pct >= 80 ? 'var(--teal)' : ($client->completion_pct >= 50 ? '#F59E0B' : 'var(--text3)') }}">{{ $client->completion_pct }}%</span>
                    </div>
                    <div class="cl-v2-track"><div class="cl-v2-fill" style="width:{{ $client->completion_pct }}%;background:{{ $client->color }}"></div></div>
                </div>
                <div class="cl-v2-stats">
                    <div class="cl-v2-pill" style="--pc:{{ $client->color }}">
                        <span class="cl-v2-pill-num">{{ $client->active_tasks }}</span>
                        <span class="cl-v2-pill-lbl">Active</span>
                    </div>
                    <div class="cl-v2-pill" style="--pc:var(--text3)">
                        <span class="cl-v2-pill-num">{{ $client->total_tasks }}</span>
                        <span class="cl-v2-pill-lbl">Total</span>
                    </div>
                    <div class="cl-v2-pill" style="--pc:var(--teal)">
                        <span class="cl-v2-pill-num">{{ $client->completed_tasks }}</span>
                        <span class="cl-v2-pill-lbl">Done</span>
                    </div>
                </div>
                <div class="cl-v2-footer">
                    <div class="cl-v2-socials">
                        @php $platformIcons = ['instagram'=>['fa-brands fa-instagram','#E4405F'],'facebook'=>['fa-brands fa-facebook','#1877F2'],'twitter'=>['fa-brands fa-x-twitter','#000'],'linkedin'=>['fa-brands fa-linkedin','#0A66C2'],'youtube'=>['fa-brands fa-youtube','#FF0000'],'tiktok'=>['fa-brands fa-tiktok','#000'],'whatsapp'=>['fa-brands fa-whatsapp','#25D366']]; @endphp
                        @if($client->website)
                            <a href="{{ $client->website }}" target="_blank" rel="noopener" class="cl-v2-social" style="color:#6366F1" title="Website" onclick="event.stopPropagation()"><i class="fa-solid fa-globe"></i></a>
                        @endif
                        @foreach($client->socialMediaLinks->unique('platform')->take(3) as $link)
                            @php $iconInfo = $platformIcons[$link->platform] ?? ['fa-solid fa-link', '#888']; @endphp
                            <a href="{{ $link->url }}" target="_blank" rel="noopener" class="cl-v2-social" style="color:{{ $iconInfo[1] }}" title="{{ ucfirst($link->platform) }}" onclick="event.stopPropagation()"><i class="{{ $iconInfo[0] }}"></i></a>
                        @endforeach
                    </div>
                    <div class="cl-v2-date">
                        @if($client->active_action_items > 0)
                            <span class="cl-v2-flag"><i class="fa-solid fa-flag"></i> {{ $client->active_action_items }}</span>
                        @endif
                        <span><i class="fa-regular fa-calendar"></i> {{ $client->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="cl-empty-state">
            <div class="cl-empty-icon">
                <i class="fa-solid fa-building"></i>
            </div>
            <div class="cl-empty-title">
                @if($search || $categoryFilter)
                    No clients match your filters
                @else
                    No clients added yet
                @endif
            </div>
            <div class="cl-empty-desc">
                @if($search || $categoryFilter)
                    Try adjusting your search or filters
                @else
                    Add your first client to get started
                @endif
            </div>
            @if(!$search && !$categoryFilter)
                <button class="btn-primary" onclick="openAddClient()" style="margin-top:12px">
                    <i class="fa-solid fa-plus"></i> Add Client
                </button>
            @endif
        </div>
    @endforelse
</div>
</div>

{{-- ============ ADD / EDIT CLIENT MODAL ============ --}}
<div class="modal-overlay" id="clientModal">
    <div class="modal" style="width:600px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title" id="clientModalTitle">Add New Client</div>
            <span class="modal-close" onclick="closeClientModal()">&times;</span>
        </div>

        <form id="clientForm" method="POST" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <input type="hidden" name="_method" id="clientFormMethod" value="POST">

            {{-- Logo Upload --}}
            <div class="cl-logo-upload" style="margin-bottom:20px">
                <div class="cl-logo-preview" id="logoPreview" onclick="document.getElementById('logoInput').click()">
                    <img id="logoPreviewImg" src="" alt="" style="display:none">
                    <div id="logoPlaceholder" class="cl-logo-placeholder">
                        <i class="fa-solid fa-camera"></i>
                        <span>Upload Logo</span>
                    </div>
                </div>
                <input type="file" name="logo" id="logoInput" accept="image/*" style="display:none" onchange="previewLogo(this)">
                <div style="font-size:11px;color:var(--text3);text-align:center;margin-top:6px">JPG, PNG, SVG &bull; Max 2MB</div>
                <div class="client-field-feedback" data-field="logo" style="text-align:center"></div>
            </div>

            {{-- Basic Info --}}
            <div class="cl-form-section-title"><i class="fa-solid fa-building"></i> Basic Information</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group" style="grid-column:1/-1">
                    <label for="clientName">Client Name <span style="color:#EF4444">*</span></label>
                    <input type="text" id="clientName" name="name" required placeholder="e.g. Acme Corp">
                    <div class="client-field-feedback" data-field="name"></div>
                </div>
                <div class="form-group">
                    <label for="clientCategory">Category</label>
                    <input type="text" id="clientCategory" name="category" placeholder="e.g. SaaS, E-commerce">
                    <div class="client-field-feedback" data-field="category"></div>
                </div>
                <div class="form-group">
                    <label for="clientEmoji">Emoji</label>
                    <input type="text" id="clientEmoji" name="emoji" placeholder="e.g. 🚀" maxlength="10" style="font-size:18px">
                    <div class="client-field-feedback" data-field="emoji"></div>
                </div>
                <div class="form-group">
                    <label for="clientColor">Brand Color</label>
                    <div class="color-pick-wrap">
                        <input type="color" id="clientColor" name="color" value="#4F6DF0" class="color-input">
                        <span id="colorHex" class="color-hex">#4F6DF0</span>
                    </div>
                    <div class="client-field-feedback" data-field="color"></div>
                </div>
                <div class="form-group" id="activeToggleGroup" style="display:none">
                    <label>Status</label>
                    <label class="toggle-label">
                        <input type="checkbox" name="is_active" id="clientActive" value="1" checked>
                        <span class="toggle-text" id="activeText">Active</span>
                    </label>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="cl-form-section-title" style="margin-top:18px"><i class="fa-solid fa-address-book"></i> Contact Information</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label for="clientContactPerson">Contact Person</label>
                    <input type="text" id="clientContactPerson" name="contact_person" placeholder="e.g. John Doe">
                    <div class="client-field-feedback" data-field="contact_person"></div>
                </div>
                <div class="form-group">
                    <label for="clientContactPhone">Phone</label>
                    <input type="tel" id="clientContactPhone" name="contact_phone" placeholder="e.g. +1 234 567 890" inputmode="tel">
                    <div class="client-field-feedback" data-field="contact_phone"></div>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label for="clientContactEmail">Email</label>
                    <input type="email" id="clientContactEmail" name="contact_email" placeholder="e.g. client@example.com">
                    <div class="client-field-feedback" data-field="contact_email"></div>
                </div>
                <div class="form-group" style="grid-column:1/-1;margin-top:4px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;background:var(--bg);padding:10px;border-radius:10px;border:1px dashed var(--border)">
                        <input type="checkbox" name="create_login" value="1" style="width:16px;height:16px;accent-color:var(--primary)">
                        <span style="font-size:12px;font-weight:600;color:var(--text2)">Create login automatically for this client (Password: password)</span>
                    </label>
                </div>
            </div>

            {{-- Website & Social Links --}}
            <div class="cl-form-section-title" style="margin-top:18px"><i class="fa-solid fa-share-nodes"></i> Website & Social Links</div>
            <div style="display:grid;grid-template-columns:1fr;gap:14px">
                <div class="form-group">
                    <label for="clientWebsite"><i class="fa-solid fa-globe" style="color:#6366F1"></i> Website</label>
                    <input type="url" id="clientWebsite" name="website" placeholder="https://example.com">
                    <div class="client-field-feedback" data-field="website"></div>
                </div>
                <div style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.15);border-radius:10px;padding:12px 14px;font-size:12px;color:#6366F1;display:flex;align-items:flex-start;gap:8px">
                    <i class="fa-solid fa-info-circle" style="margin-top:1px"></i>
                    <span>Social media accounts (Instagram, Facebook, etc.) can be managed from the <strong>client detail page</strong> using the <strong>Social Links</strong> button after creating the client.</span>
                </div>
            </div>

            {{-- Brand Collateral Assets --}}
            <div class="cl-form-section-title" style="margin-top:18px"><i class="fa-solid fa-photo-film"></i> Brand Collateral (CTA Video & Footer Image)</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label for="clientCtaVideo"><i class="fa-solid fa-video" style="color:var(--primary)"></i> CTA Video</label>
                    <input type="file" id="clientCtaVideo" name="cta_video" accept="video/*" style="font-size:11.5px">
                    <div id="clientCtaVideoInfo" style="font-size:11px;color:var(--text3);margin-top:4px">MP4, MOV, WEBM &bull; Max 100MB</div>
                    <div class="client-field-feedback" data-field="cta_video"></div>
                </div>
                <div class="form-group">
                    <label for="clientFooterImage"><i class="fa-solid fa-image" style="color:#EC4899"></i> Footer Image</label>
                    <input type="file" id="clientFooterImage" name="footer_image" accept="image/*" style="font-size:11.5px">
                    <div id="clientFooterImageInfo" style="font-size:11px;color:var(--text3);margin-top:4px">JPG, PNG, SVG &bull; Max 10MB</div>
                    <div class="client-field-feedback" data-field="footer_image"></div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="cl-form-section-title" style="margin-top:18px"><i class="fa-solid fa-sticky-note"></i> Notes</div>
            <div class="form-group">
                <textarea id="clientNotes" name="notes" rows="3" placeholder="Internal notes about this client..." style="resize:vertical"></textarea>
                <div class="client-field-feedback" data-field="notes"></div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="closeClientModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="clientSubmitBtn">
                    <i class="fa-solid fa-plus"></i> <span id="clientSubmitText">Create Client</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ DELETE CONFIRM MODAL ============ --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal" style="width:400px;text-align:center;padding:36px 30px">
        <div style="font-size:40px;margin-bottom:10px"><i class="fas fa-exclamation-triangle" style="color:#F59E0B"></i></div>
        <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:6px">Delete Client?</div>
        <div style="font-size:13px;color:var(--text3);margin-bottom:20px">
            This will permanently delete <b id="deleteClientName"></b> and cannot be undone.
        </div>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div style="display:flex;gap:10px;justify-content:center">
                <button type="button" class="btn-sec" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-primary" style="background:#EF4444;box-shadow:0 2px 8px rgba(239,68,68,0.25)">
                    <i class="fa-solid fa-trash-can"></i> Delete
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Toast */
    .cl-toast { display:flex; align-items:center; gap:10px; background:linear-gradient(135deg,#10B98115,#10B98108); border:1px solid #10B98130; color:#059669; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:600; margin-bottom:16px; animation:cl-slideDown .3s ease; }
    @keyframes cl-slideDown { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }

    .client-field-feedback {
        display:none;
        margin-top:5px;
        font-size:11px;
        font-weight:600;
        line-height:1.35;
        color:var(--red);
    }
    .client-field-feedback.is-success { color:#16a34a; }
    .client-field-feedback.is-error { color:var(--red); }

    /* ===== HERO HEADER ===== */
    .cl-hero { background:linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%); border-radius:18px; padding:28px 32px; margin-bottom:22px; position:relative; overflow:hidden; }
    .cl-hero::before { content:''; position:absolute; top:-40%; right:-10%; width:300px; height:300px; background:radial-gradient(circle, rgba(239,68,68,.15) 0%, transparent 70%); border-radius:50%; }
    .cl-hero::after { content:''; position:absolute; bottom:-60%; left:20%; width:400px; height:400px; background:radial-gradient(circle, rgba(99,102,241,.1) 0%, transparent 70%); border-radius:50%; }
    .cl-hero-content { display:flex; align-items:center; justify-content:space-between; position:relative; z-index:1; }
    .cl-hero-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:26px; font-weight:800; color:#fff; letter-spacing:-.3px; }
    .cl-hero-sub { font-size:13.5px; color:rgba(255,255,255,.55); margin-top:4px; font-weight:500; }
    .cl-hero-btn { background:linear-gradient(135deg, var(--primary), #F87171); color:#fff; border:none; border-radius:12px; padding:11px 22px; font-family:'Inter',sans-serif; font-size:13.5px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:7px; transition:all .25s ease; box-shadow:0 4px 16px rgba(239,68,68,.3); }
    .cl-hero-btn:hover { transform:translateY(-2px) scale(1.02); box-shadow:0 8px 24px rgba(239,68,68,.4); }
    .cl-hero-btn:active { transform:translateY(0) scale(.98); }

    /* ===== METRIC CARDS ===== */
    .cl-metrics { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
    .cl-metric { display:flex; align-items:center; gap:14px; background:var(--card); border:1px solid var(--border); border-radius:16px; padding:18px 20px; position:relative; overflow:hidden; transition:all .3s cubic-bezier(.4,0,.2,1); }
    .cl-metric::before { content:''; position:absolute; left:0; top:0; bottom:0; width:3.5px; background:var(--mg, var(--mc)); border-radius:16px 0 0 16px; }
    .cl-metric:hover { border-color:color-mix(in srgb, var(--mc) 35%, transparent); transform:translateY(-3px); box-shadow:0 12px 32px color-mix(in srgb, var(--mc) 10%, transparent); }
    .cl-metric-icon { width:46px; height:46px; border-radius:13px; background:var(--mg, var(--mc)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; box-shadow:0 4px 12px color-mix(in srgb, var(--mc) 25%, transparent); }
    .cl-metric-glow { position:absolute; top:-20px; right:-20px; width:80px; height:80px; background:radial-gradient(circle, color-mix(in srgb, var(--mc) 6%, transparent) 0%, transparent 70%); border-radius:50%; pointer-events:none; }
    .cl-metric-body { flex:1; min-width:0; }
    .cl-metric-val { font-size:22px; font-weight:800; color:var(--text); font-family:'Plus Jakarta Sans',sans-serif; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .cl-metric-lbl { font-size:11.5px; color:var(--text3); font-weight:600; margin-top:2px; }
    .cl-metric-bar { height:5px; background:var(--bg); border-radius:3px; margin-top:8px; overflow:hidden; }
    .cl-metric-bar-fill { height:100%; border-radius:3px; transition:width .8s cubic-bezier(.4,0,.2,1); }

    /* Toolbar */
    .cl-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; background:var(--card); border:1px solid var(--border); border-radius:14px; padding:10px 16px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,.03); }
    .cl-toolbar-form { display:flex; align-items:center; gap:10px; flex:1; flex-wrap:wrap; }
    .cl-search-wrap { position:relative; flex:1; min-width:180px; max-width:300px; }
    .cl-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text3); pointer-events:none; }
    .cl-search-input { width:100%; padding:9px 12px 9px 34px; border:1px solid var(--border); border-radius:10px; font-size:13px; background:var(--bg); color:var(--text); transition:all .2s; }
    .cl-search-input:focus { outline:none; border-color:var(--primary); background:var(--card); box-shadow:0 0 0 3px rgba(239,68,68,.08); }
    .cl-filter-select { padding:9px 30px 9px 12px; border:1px solid var(--border); border-radius:10px; font-size:13px; background:var(--bg); color:var(--text); cursor:pointer; appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%2394A3B8'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 10px center; }
    .cl-filter-select:focus { outline:none; border-color:var(--primary); }
    .cl-toggle-inactive { display:flex; align-items:center; gap:6px; font-size:12px; color:var(--text2); cursor:pointer; white-space:nowrap; font-weight:500; }
    .cl-toggle-inactive input { accent-color:var(--primary); }
    .cl-clear-btn { width:32px; height:32px; border-radius:8px; border:1px solid var(--border); background:var(--bg); color:var(--text3); display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:12px; transition:all .15s; text-decoration:none; }
    .cl-clear-btn:hover { background:#EF444412; color:#EF4444; border-color:#EF4444; }
    .cl-toolbar-count { font-size:12px; color:var(--text3); font-weight:600; white-space:nowrap; background:var(--bg); padding:5px 12px; border-radius:8px; }

    /* ===== V2 CARD ===== */
    .cl-card-v2 { position:relative; overflow:hidden; border-radius:16px; border:1px solid var(--border); background:var(--card); box-shadow:0 2px 8px rgba(0,0,0,.04); transition:all .3s cubic-bezier(.4,0,.2,1); cursor:pointer; display:flex; flex-direction:column; }
    .cl-card-v2:hover { transform:translateY(-5px); box-shadow:0 20px 48px color-mix(in srgb, var(--brand) 14%, rgba(0,0,0,.1)), 0 4px 14px rgba(0,0,0,.04); border-color:color-mix(in srgb, var(--brand) 40%, var(--border)); }
    .cl-card-inactive { opacity:.5; filter:grayscale(.4); }
    .cl-badge-inactive { position:absolute; top:16px; left:16px; background:#EF444415; color:#EF4444; font-size:10px; font-weight:700; padding:3px 10px; border-radius:6px; text-transform:uppercase; letter-spacing:.3px; z-index:4; display:flex; align-items:center; gap:4px; }

    /* Brand accent strip */
    .cl-v2-accent { height:3px; background:linear-gradient(90deg, var(--brand), color-mix(in srgb, var(--brand) 35%, transparent)); width:100%; flex-shrink:0; }

    /* V2 Header */
    .cl-v2-header { display:flex; align-items:center; gap:14px; padding:16px 18px 14px; border-bottom:1px solid var(--border); position:relative; background:linear-gradient(135deg, color-mix(in srgb, var(--brand) 5%, var(--card)) 0%, var(--card) 80%); }
    .cl-v2-logo { display:flex; align-items:center; justify-content:center; height:72px; width:112px; padding:8px; box-sizing:border-box; border-radius:12px; overflow:hidden; border:1.5px solid color-mix(in srgb, var(--brand) 18%, var(--border)); flex-shrink:0; box-shadow:0 2px 8px rgba(0,0,0,.07); background:color-mix(in srgb, var(--brand) 4%, var(--card)); }
    .cl-v2-logo img { display:block; width:100%; height:100%; object-fit:contain; object-position:center; }
    .cl-v2-avatar { height:72px; width:112px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0; font-weight:800; background:color-mix(in srgb, var(--brand) 10%, var(--bg)); border:1.5px solid color-mix(in srgb, var(--brand) 22%, var(--border)); color:var(--brand); letter-spacing:-.5px; }
    .cl-v2-identity { flex:1; min-width:0; }
    .cl-v2-name { font-size:15px; font-weight:800; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-.2px; font-family:'Plus Jakarta Sans',sans-serif; }
    .cl-v2-cat { display:inline-flex; align-items:center; font-size:10px; font-weight:700; color:var(--brand); background:color-mix(in srgb, var(--brand) 10%, transparent); border:1px solid color-mix(in srgb, var(--brand) 22%, transparent); padding:2px 8px; border-radius:6px; margin-top:5px; text-transform:uppercase; letter-spacing:.35px; }

    /* V2 Body */
    .cl-v2-body { padding:14px 18px 0; flex:1; display:flex; flex-direction:column; }

    /* V2 Progress */
    .cl-v2-progress { margin-bottom:14px; }
    .cl-v2-progress-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
    .cl-v2-progress-lbl { font-size:11px; color:var(--text3); font-weight:600; text-transform:uppercase; letter-spacing:.4px; }
    .cl-v2-progress-pct { font-size:13px; font-weight:800; font-family:'Plus Jakarta Sans',sans-serif; }
    .cl-v2-track { height:7px; background:var(--bg); border-radius:4px; overflow:hidden; }
    .cl-v2-fill { height:100%; border-radius:4px; transition:width .7s cubic-bezier(.4,0,.2,1); min-width:3px; }

    /* V2 Stat Pills */
    .cl-v2-stats { display:flex; gap:8px; margin-bottom:14px; }
    .cl-v2-pill { flex:1; display:flex; flex-direction:column; align-items:center; gap:3px; padding:10px 6px; background:color-mix(in srgb, var(--pc) 6%, var(--bg)); border-radius:10px; border:1px solid color-mix(in srgb, var(--pc) 12%, var(--border)); transition:all .2s ease; }
    .cl-v2-pill:hover { background:color-mix(in srgb, var(--pc) 12%, var(--bg)); transform:translateY(-2px); box-shadow:0 4px 12px color-mix(in srgb, var(--pc) 12%, transparent); }
    .cl-v2-pill-num { font-size:18px; font-weight:800; color:var(--pc); font-family:'Plus Jakarta Sans',sans-serif; line-height:1; }
    .cl-v2-pill-lbl { font-size:9px; font-weight:700; color:var(--text3); text-transform:uppercase; letter-spacing:.5px; }

    /* V2 Footer */
    .cl-v2-footer { display:flex; align-items:center; justify-content:space-between; padding:10px 0 14px; border-top:1px solid var(--border); margin-top:auto; }
    .cl-v2-socials { display:flex; gap:4px; }
    .cl-v2-social { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:12px; background:var(--bg); border:1px solid var(--border); text-decoration:none; transition:all .2s; }
    .cl-v2-social:hover { transform:translateY(-2px) scale(1.1); box-shadow:0 4px 10px rgba(0,0,0,.1); background:var(--card); }
    .cl-v2-date { display:flex; align-items:center; gap:8px; font-size:11px; color:var(--text3); }
    .cl-v2-date i { font-size:10px; }
    .cl-v2-flag { display:inline-flex; align-items:center; gap:3px; background:#F59E0B18; color:#F59E0B; font-size:10px; font-weight:700; padding:2px 8px; border-radius:6px; border:1px solid #F59E0B22; }

    /* Card hover actions */
    .client-card { position:relative; }
    .cc-actions { position:absolute; top:14px; right:14px; display:flex; gap:4px; opacity:0; transition:opacity .2s ease; z-index:3; }
    .client-card:hover .cc-actions { opacity:1; }
    .cc-action-btn { width:30px; height:30px; border-radius:9px; border:none; background:rgba(255,255,255,.92); backdrop-filter:blur(10px); color:#475569; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:11px; transition:all .2s ease; box-shadow:0 2px 8px rgba(0,0,0,.1); }
    .cc-action-btn:hover { background:#fff; color:var(--primary); transform:scale(1.1); box-shadow:0 4px 14px rgba(0,0,0,.15); }
    .cc-action-danger:hover { color:#EF4444; }

    /* Card entrance animation */
    @keyframes cl-cardIn { from { opacity:0; transform:translateY(20px) scale(.97); } to { opacity:1; transform:translateY(0) scale(1); } }
    .client-card { animation:cl-cardIn .45s ease both; }
    .client-card:nth-child(1) { animation-delay:.04s; }
    .client-card:nth-child(2) { animation-delay:.08s; }
    .client-card:nth-child(3) { animation-delay:.12s; }
    .client-card:nth-child(4) { animation-delay:.18s; }
    .client-card:nth-child(5) { animation-delay:.24s; }
    .client-card:nth-child(6) { animation-delay:.3s; }
    .client-card:nth-child(n+7) { animation-delay:.35s; }

    /* Modal UI polish */
    #clientModal .modal { width:min(760px, calc(100vw - 32px)) !important; max-height:92vh !important; overflow-y:auto; border-radius:18px; border:1px solid #e8edf5; box-shadow:0 24px 80px rgba(15,23,42,.18), 0 8px 24px rgba(15,23,42,.08); padding:0 !important; background:linear-gradient(180deg,#ffffff 0%,#fcfdff 100%); }
    #clientModal .modal-head { position:sticky; top:0; z-index:4; display:flex; align-items:center; justify-content:space-between; padding:18px 22px; border-bottom:1px solid #edf2f7; background:rgba(255,255,255,.95); backdrop-filter:saturate(140%) blur(8px); }
    #clientModal .modal-title { font-size:18px; font-weight:800; letter-spacing:.1px; }
    #clientModal .modal-close {
        width:34px; height:34px; border-radius:10px;
        display:inline-flex; align-items:center; justify-content:center;
        border:1px solid var(--border); color:var(--text3);
        transition:all .15s ease;
    }
    #clientModal .modal-close:hover { background:#EF444415; color:#EF4444; border-color:#fecaca; }
    #clientModal form { padding:18px 22px 20px; }

    /* Form sections and fields */
    .cl-form-section-title {
        font-size:11.5px;
        font-weight:800;
        color:#475569;
        text-transform:uppercase;
        letter-spacing:.45px;
        margin:16px 0 10px;
        padding:0 0 8px;
        border-bottom:1px solid #eef2f7;
        display:flex;
        align-items:center;
        gap:7px;
    }
    .cl-form-section-title i { font-size:12px; color:#64748b; }

    #clientModal .form-group { margin-bottom:2px; }
    #clientModal .form-group label {
        display:block;
        font-size:12px;
        font-weight:700;
        color:#334155;
        margin-bottom:7px;
    }
    #clientModal .form-group input,
    #clientModal .form-group textarea,
    #clientModal .form-group select {
        width:100%;
        min-height:42px;
        border:1px solid #dbe3ee;
        border-radius:11px;
        background:#fff;
        font-size:13px;
        color:#0f172a;
        padding:10px 12px;
        transition:all .18s ease;
        box-sizing:border-box;
    }
    #clientModal .form-group textarea { min-height:94px; line-height:1.45; }
    #clientModal .form-group input::placeholder,
    #clientModal .form-group textarea::placeholder { color:#94a3b8; }
    #clientModal .form-group input:focus,
    #clientModal .form-group textarea:focus,
    #clientModal .form-group select:focus {
        outline:none;
        border-color:var(--primary);
        box-shadow:0 0 0 4px rgba(204,49,14,.10);
        background:#fff;
    }

    /* Logo upload block */
    .cl-logo-upload { margin-bottom:14px !important; padding:10px 0 4px; }
    .cl-logo-preview {
        width:210px; height:120px; border-radius:16px;
        width:210px; height:120px; border-radius:16px;
        border:2px dashed #d7e0ec;
        background:linear-gradient(135deg,#f8fbff 0%,#f4f7fc 100%);
    }
    .cl-logo-preview:hover { border-color:var(--primary); background:#fff7f5; }
    .cl-logo-placeholder { font-size:10.5px; gap:5px; }
    .cl-logo-placeholder i { font-size:18px; color:#64748b; }

    /* Color picker + toggles */
    .color-pick-wrap { gap:8px; }
    .color-input { width:44px; height:40px; border-radius:10px; border:1px solid #dbe3ee; }
    .color-hex {
        background:#f8fafc;
        border:1px solid #e2e8f0;
        border-radius:8px;
        padding:6px 8px;
        font-size:12px;
        color:#334155;
    }
    .toggle-label {
        min-height:42px;
        border:1px solid #dbe3ee;
        border-radius:11px;
        padding:0 10px;
        background:#fff;
    }

    /* Sticky action area */
    #clientModal form > div[style*="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;padding-top:16px;border-top:1px solid var(--border)"] {
        position:sticky;
        bottom:-1px;
        margin:18px -22px -20px !important;
        padding:12px 22px calc(12px + env(safe-area-inset-bottom)) !important;
        border-top:1px solid #e9edf4 !important;
        background:rgba(255,255,255,.96);
        backdrop-filter:blur(6px);
        z-index:5;
    }
    #clientModal .btn-sec,
    #clientModal .btn-primary {
        min-height:42px;
        border-radius:10px;
        padding:0 14px;
        font-weight:700;
    }


    /* Logo Upload */
    .cl-logo-upload { display:flex; flex-direction:column; align-items:center; }
    .cl-logo-preview { width:210px; height:120px; border-radius:14px; border:2px dashed var(--border); display:flex; align-items:center; justify-content:center; cursor:pointer; overflow:hidden; transition:all 0.2s ease; background:var(--bg); }
    .cl-logo-preview { width:210px; height:120px; border-radius:14px; border:2px dashed var(--border); display:flex; align-items:center; justify-content:center; cursor:pointer; overflow:hidden; transition:all 0.2s ease; background:var(--bg); }
    .cl-logo-preview:hover { border-color:var(--primary); background:var(--primary-dim); }
    .cl-logo-preview img { width:100%; height:100%; object-fit:cover; }
    .cl-logo-placeholder { display:flex; flex-direction:column; align-items:center; gap:4px; color:var(--text3); font-size:10px; font-weight:600; }
    .cl-logo-placeholder i { font-size:18px; }

    /* Color Picker */
    .color-pick-wrap { display:flex; align-items:center; gap:10px; }
    .color-input { width:40px; height:36px; border:1px solid var(--border); border-radius:8px; padding:2px; cursor:pointer; background:var(--bg); }
    .color-hex { font-size:13px; font-weight:600; color:var(--text2); font-family:'DM Mono',monospace; }

    /* Toggle */
    .toggle-label { display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; }
    .toggle-text { font-weight:500; color:var(--text2); }

    /* Responsive */
    @media (max-width:1200px) {
        .cl-metrics { grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width:768px) {
        .cl-hero { padding:22px 20px; border-radius:14px; margin-bottom:16px; }
        .cl-hero-content { flex-direction:column; align-items:stretch; gap:14px; }
        .cl-hero-title { font-size:22px; }
        .cl-hero-btn { width:100%; justify-content:center; }
        .cl-metrics { grid-template-columns:1fr 1fr; gap:10px; }

        .cl-toolbar { flex-direction:column; align-items:stretch; gap:10px; padding:12px; border-radius:12px; }
        .cl-toolbar-form { flex-direction:column; align-items:stretch; gap:8px; }
        .cl-search-wrap, .cl-filter-select, .cl-toggle-inactive, .cl-clear-btn { width:100%; max-width:none; }
        .cl-search-input, .cl-filter-select { min-height:42px; font-size:14px; }
        .cl-toggle-inactive { min-height:42px; border:1px solid var(--border); border-radius:10px; padding:0 12px; background:var(--bg); }
        .cl-clear-btn { height:42px; border-radius:10px; }
        .cl-toolbar-count { text-align:center; }

        .client-grid { gap:12px; }
        .cl-card-v2 { border-radius:12px; }
        .cl-v2-header { padding:14px 16px 12px; gap:10px; }
        .cl-v2-logo, .cl-v2-avatar { height:90px; width:210px; border-radius:12px; font-size:30px; }
        .cl-v2-logo, .cl-v2-avatar { height:90px; width:210px; border-radius:12px; font-size:30px; }
        .cl-v2-name { font-size:14px; }
        .cl-v2-body { padding:14px 16px 0; }
        .cl-v2-stats { gap:6px; }
        .cl-v2-pill { padding:8px 4px; }
        .cl-v2-pill-num { font-size:15px; }
        .cl-v2-footer { padding:10px 0 14px; }

        .cc-actions { opacity:1; top:10px; right:10px; }
        .cc-action-btn { width:32px; height:32px; font-size:12px; }

        #clientModal .modal { width:calc(100vw - 20px) !important; max-width:calc(100vw - 20px) !important; max-height:88vh !important; margin:10px; border-radius:12px; padding:14px; box-sizing:border-box; }
        #clientModal .modal form > div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns:1fr !important; gap:10px !important; }
        #clientModal .modal-head { position:sticky; top:0; background:var(--card); z-index:3; padding-bottom:8px; }
        #clientModal .form-group input, #clientModal .form-group textarea, #clientModal .form-group select { min-height:42px; font-size:14px; }
        #clientModal .form-group textarea { min-height:96px; }
        #clientModal .cl-form-section-title { margin-top:14px !important; }
        #clientModal .btn-sec, #clientModal .btn-primary { min-height:42px; }
        #clientModal .cl-logo-preview { width:175px; height:100px; border-radius:12px; }
        #clientModal .cl-logo-preview { width:175px; height:100px; border-radius:12px; }

        #deleteModal .modal { width:calc(100vw - 24px) !important; max-width:calc(100vw - 24px) !important; padding:24px 16px !important; margin:12px; box-sizing:border-box; border-radius:12px; }
    }

    @media (max-width:480px) {
        .topbar { margin-bottom:12px; }
        .page-title { font-size:20px; line-height:1.2; }
        .page-subtitle { font-size:12px; }
        .cl-metrics { grid-template-columns:1fr; gap:8px; }
        .cl-metric { padding:12px 14px; }
        .cl-metric-icon { width:36px; height:36px; font-size:14px; }
        .cl-metric-val { font-size:17px; }
        .cl-toolbar { padding:10px; margin-bottom:12px; }
        .cl-v2-header { padding:12px 14px 10px; }
        .cl-v2-body { padding:12px 14px 0; }
        .cl-v2-stats { gap:5px; }
        .cl-v2-pill { padding:7px 4px; border-radius:8px; }
        .cl-v2-pill-num { font-size:14px; }
        .cl-v2-footer { padding:8px 0 12px; }
        #clientModal .modal { width:calc(100vw - 12px) !important; max-width:calc(100vw - 12px) !important; margin:6px; padding:12px; }
    }

    /* Empty State */
    .cl-empty-state { grid-column:1/-1; text-align:center; padding:60px 30px; background:var(--card); border:1px solid var(--border); border-radius:16px; }
    .cl-empty-icon { width:64px; height:64px; border-radius:16px; margin:0 auto 16px; background:linear-gradient(135deg, var(--primary-dim), var(--bg)); display:flex; align-items:center; justify-content:center; font-size:26px; color:var(--text3); }
    .cl-empty-title { font-size:16px; font-weight:700; color:var(--text); margin-bottom:6px; }
    .cl-empty-desc { font-size:13px; color:var(--text3); }
</style>


<script>
const clientBaseUrl = '{{ url("admin/clients") }}';
const clientFormEl = document.getElementById('clientForm');
const clientValidationFields = {
    logo: document.getElementById('logoInput'),
    name: document.getElementById('clientName'),
    category: document.getElementById('clientCategory'),
    emoji: document.getElementById('clientEmoji'),
    color: document.getElementById('clientColor'),
    contact_person: document.getElementById('clientContactPerson'),
    contact_phone: document.getElementById('clientContactPhone'),
    contact_email: document.getElementById('clientContactEmail'),
    website: document.getElementById('clientWebsite'),
    notes: document.getElementById('clientNotes'),
};

function getClientFeedbackEl(fieldName) {
    return clientFormEl?.querySelector(`.client-field-feedback[data-field="${fieldName}"]`) || null;
}

function setClientFieldVisual(fieldName, state) {
    const field = clientValidationFields[fieldName];
    if (!field || fieldName === 'logo') return;

    if (state === 'error') {
        field.style.borderColor = 'var(--red)';
        field.style.boxShadow = '0 0 0 2px rgba(239,68,68,0.12)';
        return;
    }
    if (state === 'success') {
        field.style.borderColor = '#16a34a';
        field.style.boxShadow = '0 0 0 2px rgba(22,163,74,0.12)';
        return;
    }

    field.style.borderColor = '';
    field.style.boxShadow = '';
}

function setClientFeedback(fieldName, message, state) {
    const el = getClientFeedbackEl(fieldName);
    if (!el) return;
    el.style.display = 'block';
    el.textContent = state === 'success' ? ('✓ ' + message) : message;
    el.classList.remove('is-success', 'is-error');
    el.classList.add(state === 'success' ? 'is-success' : 'is-error');
}

function clearClientFeedback(fieldName) {
    const el = getClientFeedbackEl(fieldName);
    if (!el) return;
    el.style.display = 'none';
    el.textContent = '';
    el.classList.remove('is-success', 'is-error');
}

function clearClientValidationUI() {
    Object.keys(clientValidationFields).forEach((fieldName) => {
        setClientFieldVisual(fieldName, null);
        clearClientFeedback(fieldName);
    });
}

function isHttpUrl(value) {
    try {
        const parsed = new URL(value);
        return parsed.protocol === 'http:' || parsed.protocol === 'https:';
    } catch (e) {
        return false;
    }
}

function validateClientModalField(fieldName, showSuccess = true) {
    const field = clientValidationFields[fieldName];
    if (!field) return true;
    const value = typeof field.value === 'string' ? field.value.trim() : '';

    const markError = (msg) => {
        setClientFieldVisual(fieldName, 'error');
        setClientFeedback(fieldName, msg, 'error');
        return false;
    };
    const markSuccess = (msg) => {
        setClientFieldVisual(fieldName, 'success');
        if (showSuccess) setClientFeedback(fieldName, msg, 'success');
        else clearClientFeedback(fieldName);
        return true;
    };
    const clearNeutral = () => {
        setClientFieldVisual(fieldName, null);
        clearClientFeedback(fieldName);
        return true;
    };

    if (fieldName === 'name') {
        if (!value) return markError('Client name is required.');
        if (value.length > 255) return markError('Client name must be 255 characters or fewer.');
        return markSuccess('Client name looks good.');
    }

    if (fieldName === 'category') {
        if (!value) return clearNeutral();
        if (value.length > 255) return markError('Category must be 255 characters or fewer.');
        return markSuccess('Category looks good.');
    }

    if (fieldName === 'emoji') {
        if (!value) return clearNeutral();
        if (value.length > 10) return markError('Emoji must be 10 characters or fewer.');
        return markSuccess('Emoji looks good.');
    }

    if (fieldName === 'color') {
        if (!value) return clearNeutral();
        if (!/^#[0-9A-Fa-f]{6}$/.test(value)) return markError('Brand color must be a valid hex like #4F6DF0.');
        return markSuccess('Brand color looks good.');
    }

    if (fieldName === 'contact_person') {
        if (!value) return clearNeutral();
        if (value.length > 255) return markError('Contact person must be 255 characters or fewer.');
        return markSuccess('Contact person looks good.');
    }

    if (fieldName === 'contact_phone') {
        if (!value) return clearNeutral();
        const phoneOk = /^\+?[0-9\s\-\(\)]{7,20}$/.test(value);
        if (!phoneOk) return markError('Phone must be 7-20 chars using digits, spaces, +, -, or parentheses.');
        if (value.length > 50) return markError('Phone must be 50 characters or fewer.');
        return markSuccess('Phone looks good.');
    }

    if (fieldName === 'contact_email') {
        if (!value) return clearNeutral();
        const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        if (!emailOk) return markError('Please enter a valid email address.');
        if (value.length > 255) return markError('Email must be 255 characters or fewer.');
        return markSuccess('Email looks valid.');
    }

    if (fieldName === 'website') {
        if (!value) return clearNeutral();
        if (!isHttpUrl(value)) return markError('Website must start with http:// or https://');
        if (value.length > 255) return markError('Website must be 255 characters or fewer.');
        return markSuccess('Website URL looks valid.');
    }

    if (fieldName === 'notes') {
        if (!value) return clearNeutral();
        if (value.length > 2000) return markError('Notes must be 2000 characters or fewer.');
        return markSuccess('Notes length is valid.');
    }

    if (fieldName === 'logo') {
        const file = field.files && field.files[0] ? field.files[0] : null;
        if (!file) return clearNeutral();
        const maxBytes = 2048 * 1024;
        const fileExt = (file.name.split('.').pop() || '').toLowerCase();
        const allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!allowedExt.includes(fileExt)) {
            return markError('Logo must be JPG, PNG, GIF, WEBP, or SVG.');
        }
        if (file.size > maxBytes) {
            return markError('Logo must be 2MB or smaller.');
        }
        return markSuccess('Logo file is valid.');
    }

    return true;
}

function validateClientModal(showSuccess = true) {
    const fieldsToCheck = ['name', 'category', 'emoji', 'color', 'contact_person', 'contact_phone', 'contact_email', 'website', 'notes', 'logo'];
    let valid = true;
    fieldsToCheck.forEach((fieldName) => {
        if (!validateClientModalField(fieldName, showSuccess)) valid = false;
    });
    return valid;
}

function openAddClient() {
    document.getElementById('clientModalTitle').textContent = 'Add New Client';
    document.getElementById('clientForm').action = '{{ route("admin.clients.store") }}';
    document.getElementById('clientFormMethod').value = 'POST';
    document.getElementById('clientSubmitText').textContent = 'Create Client';
    document.getElementById('clientSubmitBtn').querySelector('i').className = 'fa-solid fa-plus';
    document.getElementById('activeToggleGroup').style.display = 'none';
    resetClientForm();
    document.getElementById('clientModal').classList.add('show');
}

function openEditClient(client) {
    event.stopPropagation();
    clearClientValidationUI();
    document.getElementById('clientModalTitle').textContent = 'Edit Client';
    document.getElementById('clientForm').action = clientBaseUrl + '/' + client.id;
    document.getElementById('clientFormMethod').value = 'PUT';
    document.getElementById('clientSubmitText').textContent = 'Save Changes';
    document.getElementById('clientSubmitBtn').querySelector('i').className = 'fa-solid fa-check';
    document.getElementById('activeToggleGroup').style.display = '';

    document.getElementById('clientName').value = client.name || '';
    document.getElementById('clientCategory').value = client.category || '';
    document.getElementById('clientEmoji').value = client.emoji || '';
    document.getElementById('clientColor').value = client.color || '#4F6DF0';
    document.getElementById('colorHex').textContent = client.color || '#4F6DF0';
    document.getElementById('clientActive').checked = client.is_active ? true : false;
    document.getElementById('activeText').textContent = client.is_active ? 'Active' : 'Inactive';

    // Contact & website
    document.getElementById('clientWebsite').value = client.website || '';
    document.getElementById('clientContactPerson').value = client.contact_person || '';
    document.getElementById('clientContactEmail').value = client.contact_email || '';
    document.getElementById('clientContactPhone').value = client.contact_phone || '';
    document.getElementById('clientNotes').value = client.notes || '';

    // Logo preview
    const imgEl = document.getElementById('logoPreviewImg');
    const placeholder = document.getElementById('logoPlaceholder');
    if (client.logo) {
        imgEl.src = '/storage/' + client.logo;
        imgEl.style.display = 'block';
        placeholder.style.display = 'none';
    } else {
        imgEl.style.display = 'none';
        placeholder.style.display = 'flex';
    }

    // Collateral info
    document.getElementById('clientCtaVideo').value = '';
    document.getElementById('clientFooterImage').value = '';
    document.getElementById('clientCtaVideoInfo').innerHTML = client.cta_video ? '<span style="color:#059669;font-weight:600"><i class="fa-solid fa-circle-check"></i> Current: ' + client.cta_video.split("/").pop() + '</span>' : 'MP4, MOV, WEBM &bull; Max 100MB';
    document.getElementById('clientFooterImageInfo').innerHTML = client.footer_image ? '<span style="color:#059669;font-weight:600"><i class="fa-solid fa-circle-check"></i> Current: ' + client.footer_image.split("/").pop() + '</span>' : 'JPG, PNG, SVG &bull; Max 10MB';

    // Show current edit data validity immediately with the same red/green rules.
    ['name', 'category', 'emoji', 'color', 'contact_person', 'contact_phone', 'contact_email', 'website', 'notes']
        .forEach((fieldName) => validateClientModalField(fieldName, true));

    document.getElementById('clientModal').classList.add('show');
}

function closeClientModal() {
    document.getElementById('clientModal').classList.remove('show');
}

function resetClientForm() {
    document.getElementById('clientName').value = '';
    document.getElementById('clientCategory').value = '';
    document.getElementById('clientEmoji').value = '';
    document.getElementById('clientColor').value = '#4F6DF0';
    document.getElementById('colorHex').textContent = '#4F6DF0';
    document.getElementById('logoInput').value = '';
    document.getElementById('logoPreviewImg').style.display = 'none';
    document.getElementById('logoPlaceholder').style.display = 'flex';
    document.getElementById('clientCtaVideo').value = '';
    document.getElementById('clientFooterImage').value = '';
    document.getElementById('clientCtaVideoInfo').innerHTML = 'MP4, MOV, WEBM &bull; Max 100MB';
    document.getElementById('clientFooterImageInfo').innerHTML = 'JPG, PNG, SVG &bull; Max 10MB';
    document.getElementById('clientActive').checked = true;
    // Contact & website
    document.getElementById('clientWebsite').value = '';
    document.getElementById('clientContactPerson').value = '';
    document.getElementById('clientContactEmail').value = '';
    document.getElementById('clientContactPhone').value = '';
    document.getElementById('clientNotes').value = '';
    clearClientValidationUI();
}

function previewLogo(input) {
    const imgEl = document.getElementById('logoPreviewImg');
    const placeholder = document.getElementById('logoPlaceholder');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imgEl.src = e.target.result;
            imgEl.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function confirmDeleteClient(id, name) {
    document.getElementById('deleteClientName').textContent = name;
    document.getElementById('deleteForm').action = clientBaseUrl + '/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('clientColor').addEventListener('input', function() {
    document.getElementById('colorHex').textContent = this.value.toUpperCase();
});

document.getElementById('clientActive').addEventListener('change', function() {
    document.getElementById('activeText').textContent = this.checked ? 'Active' : 'Inactive';
});

// Close modals on overlay click
document.getElementById('clientModal').addEventListener('click', function(e) { if (e.target === this) closeClientModal(); });
document.getElementById('deleteModal').addEventListener('click', function(e) { if (e.target === this) closeDeleteModal(); });

// Auto-dismiss flash
const flash = document.getElementById('flashMsg');
if (flash) setTimeout(() => flash.remove(), 4000);

let clientsSearchDebounceTimer = null;
let clientsSearchRequestController = null;

function initClientsLiveSearch() {
    const form = document.getElementById('clientsFilterForm');
    const searchInput = document.getElementById('clientsSearchInput');
    const resultsArea = document.getElementById('clientsResultsArea');
    if (!form || !searchInput || !resultsArea) return;

    const buildUrlFromForm = () => {
        const params = new URLSearchParams(new FormData(form));
        params.delete('page');
        const query = params.toString();
        return form.action + (query ? ('?' + query) : '');
    };

    const setLoadingState = (isLoading) => {
        resultsArea.style.opacity = isLoading ? '0.65' : '1';
        resultsArea.style.pointerEvents = isLoading ? 'none' : 'auto';
    };

    const renderClientsResponse = (html, url) => {
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        const nextArea = parsed.getElementById('clientsResultsArea');
        if (!nextArea) return;

        resultsArea.innerHTML = nextArea.innerHTML;
        window.history.replaceState({}, '', url);
        initClientsLiveSearch();
    };

    const fetchAndRender = (url) => {
        if (clientsSearchRequestController) clientsSearchRequestController.abort();
        clientsSearchRequestController = new AbortController();

        setLoadingState(true);

        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: clientsSearchRequestController.signal,
        })
            .then((response) => response.text())
            .then((html) => renderClientsResponse(html, url))
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    console.error('Clients live search failed:', error);
                }
            })
            .finally(() => setLoadingState(false));
    };

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        clearTimeout(clientsSearchDebounceTimer);
        fetchAndRender(buildUrlFromForm());
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(clientsSearchDebounceTimer);
        clientsSearchDebounceTimer = setTimeout(() => {
            fetchAndRender(buildUrlFromForm());
        }, 300);
    });
}

initClientsLiveSearch();

if (clientFormEl) {
    clientFormEl.addEventListener('submit', function(e) {
        if (!validateClientModal(true)) {
            e.preventDefault();
        }
    });

    clientValidationFields.name?.addEventListener('input', () => validateClientModalField('name', false));
    clientValidationFields.name?.addEventListener('blur', () => validateClientModalField('name', true));
    clientValidationFields.category?.addEventListener('blur', () => validateClientModalField('category', true));
    clientValidationFields.emoji?.addEventListener('blur', () => validateClientModalField('emoji', true));
    clientValidationFields.color?.addEventListener('input', () => validateClientModalField('color', true));
    clientValidationFields.contact_person?.addEventListener('blur', () => validateClientModalField('contact_person', true));
    clientValidationFields.contact_phone?.addEventListener('input', () => validateClientModalField('contact_phone', false));
    clientValidationFields.contact_phone?.addEventListener('blur', () => validateClientModalField('contact_phone', true));
    clientValidationFields.contact_email?.addEventListener('input', () => validateClientModalField('contact_email', false));
    clientValidationFields.contact_email?.addEventListener('blur', () => validateClientModalField('contact_email', true));
    clientValidationFields.website?.addEventListener('input', () => validateClientModalField('website', false));
    clientValidationFields.website?.addEventListener('blur', () => validateClientModalField('website', true));
    clientValidationFields.notes?.addEventListener('input', () => validateClientModalField('notes', false));
    clientValidationFields.notes?.addEventListener('blur', () => validateClientModalField('notes', true));
    clientValidationFields.logo?.addEventListener('change', () => validateClientModalField('logo', true));
}

// Live Animation on Load
document.addEventListener('DOMContentLoaded', () => {
    // 1. Animate Progress Bars
    const progressBars = document.querySelectorAll('.cl-metric-bar-fill, .cl-v2-fill');
    progressBars.forEach(bar => {
        const targetWidth = bar.style.width;
        bar.style.transition = 'none';
        bar.style.width = '0%';
        
        // Force reflow
        void bar.offsetWidth;
        
        bar.style.transition = ''; // Restore CSS transition
        setTimeout(() => {
            bar.style.width = targetWidth;
        }, 150);
    });

    // 2. Animate Numbers
    const numberElements = document.querySelectorAll('.cl-metric-val, .cl-v2-pill-num, .cl-v2-progress-pct');
    numberElements.forEach(el => {
        const originalText = el.innerText.trim();
        const match = originalText.match(/^([0-9,.]+)(.*)$/); 
        
        if (match && !isNaN(parseFloat(match[1].replace(/,/g, '')))) {
            const endVal = parseFloat(match[1].replace(/,/g, ''));
            const suffix = match[2] || '';
            const isInt = endVal % 1 === 0 && !match[1].includes('.');
            const duration = 1500; // 1.5s duration
            
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                
                // easeOutQuart
                const easeOut = 1 - Math.pow(1 - progress, 4);
                const currentVal = easeOut * endVal;
                
                el.innerText = (isInt ? Math.floor(currentVal) : currentVal.toFixed(1)) + suffix;
                
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                } else {
                    el.innerText = originalText; // Ensure exact final value
                }
            };
            window.requestAnimationFrame(step);
        }
    });
});
</script>
@endsection
