@extends('layouts.app')

@section('content')

{{-- Success/Error Flash --}}
@if(session('success'))
<div class="cd-toast" id="flashMsg">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    <span onclick="this.parentElement.remove()" style="cursor:pointer;margin-left:auto;opacity:0.6">&times;</span>
</div>
@endif
@if(session('error'))
<div class="cd-toast cd-toast-error" id="flashMsgErr">
    <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
    <span onclick="this.parentElement.remove()" style="cursor:pointer;margin-left:auto;opacity:0.6">&times;</span>
</div>
@endif

@php
    $editClientErrorFields = ['name', 'category', 'emoji', 'color', 'logo', 'cta_video', 'footer_image', 'website', 'notes', 'contact_person', 'contact_email', 'contact_phone'];
    $socialLinkErrorFields = ['platform', 'url', 'label'];
    $hasEditClientErrors = $errors->hasAny($editClientErrorFields);
    $hasSocialLinkErrors = $errors->hasAny($socialLinkErrorFields);
    $hasCreateLoginErrors = old('_form') === 'create_client_login';
    $hasMonthlyScheduleErrors = in_array(old('_form'), ['monthly_schedule_create', 'monthly_schedule_update'], true);
@endphp

{{-- ===== BACK + BREADCRUMB ===== --}}
<div class="topbar">
    <div class="cd-header-main">
        <a href="{{ route('admin.clients') }}" class="cd-back-btn" title="Back to clients" aria-label="Back to clients">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="cd-header-logo" style="--client-color:{{ $client->color ?: 'var(--primary)' }}">
            @if($client->logo)
                <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }} logo">
            @elseif($client->emoji)
                <span>{{ $client->emoji }}</span>
            @else
                <span>{{ strtoupper(substr($client->name, 0, 1)) }}</span>
            @endif
        </div>
        <div class="cd-header-copy">
            <div class="cd-header-title-row">
                <h1 class="page-title">{{ $client->name }}</h1>
                @if(!$client->is_active)
                    <span class="cd-badge-inactive"><i class="fa-solid fa-circle-pause"></i> Inactive</span>
                @else
                    <span class="cd-badge-active"><i class="fa-solid fa-circle-check"></i> Active</span>
                @endif
            </div>
            <div class="page-subtitle cd-header-meta">
                @if($client->category)
                    <span><i class="fa-solid fa-layer-group"></i> {{ $client->category }}</span>
                @endif
                <span><i class="fa-regular fa-calendar"></i> Client since {{ $client->created_at->format('M Y') }}</span>
            </div>
        </div>
    </div>
    <div class="cd-header-actions">
        @if($clientLogins->isEmpty())
            <button class="btn-sec cd-topbar-btn" style="background:var(--primary-dim);color:var(--primary);border-color:var(--primary)" onclick="document.getElementById('createLoginModal').classList.add('show')">
                <i class="fa-solid fa-user-plus"></i> Create Login
            </button>
        @else
            <button class="btn-sec cd-topbar-btn" style="background:rgba(16,185,129,.1);color:#059669;border-color:#059669" onclick="document.getElementById('manageLoginModal').classList.add('show')">
                <i class="fa-solid fa-user-check"></i> Manage Logins
                <span style="background:rgba(5,150,105,.18);padding:1px 7px;border-radius:5px;font-size:10px;font-weight:800;margin-left:2px">{{ $clientLogins->count() }}</span>
            </button>
        @endif
        <button class="btn-sec cd-topbar-btn" onclick="document.getElementById('manageSocialLinksModal').classList.add('show')">
            <i class="fa-solid fa-link"></i> Social Links
        </button>
        <button class="btn-sec cd-topbar-btn" onclick="document.getElementById('collateralModal').classList.add('show')">
            <i class="fa-solid fa-photo-film"></i> Collateral
        </button>
        <a href="{{ route('admin.visits', ['tab' => 'client', 'client_id' => $client->id]) }}" class="btn-sec cd-topbar-btn" title="View & Log Visits for {{ $client->name }}">
            <i class="fa-solid fa-person-walking-luggage"></i> Visits
            @if($clientVisits->count() > 0)
                <span style="background:var(--primary-dim);color:var(--primary);padding:1px 6px;border-radius:5px;font-size:10px;font-weight:800;margin-left:2px">{{ $clientVisits->count() }}</span>
            @endif
        </a>
        <button class="btn-sec cd-topbar-btn" onclick="document.getElementById('editClientModal').classList.add('show')">
            <i class="fa-solid fa-pen-to-square"></i> Edit Client
        </button>
    </div>
</div>

{{-- ============ EDIT CLIENT MODAL ============ --}}
<div class="modal-overlay" id="editClientModal">
    <div class="modal" style="width:600px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title">Edit Client</div>
            <span class="modal-close" onclick="document.getElementById('editClientModal').classList.remove('show')">&times;</span>
        </div>

        <form id="editClientForm" method="POST" action="{{ route('admin.clients.update', $client) }}" enctype="multipart/form-data" autocomplete="off">
            @csrf
            @method('PUT')
            <input type="hidden" name="_from_detail" value="1">

            {{-- Logo Upload --}}
            <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:20px">
                <div class="cl-logo-preview" id="editLogoPreview" onclick="document.getElementById('editLogoInput').click()" style="width:210px;height:120px;border-radius:14px;border:2px dashed var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;overflow:hidden;background:var(--bg);transition:all .2s">
                    @if($client->logo)
                        <img id="editLogoPreviewImg" src="{{ asset('storage/' . $client->logo) }}" alt="" style="width:100%;height:100%;object-fit:cover">
                    @else
                        <div id="editLogoPlaceholder" style="display:flex;flex-direction:column;align-items:center;gap:4px;color:var(--text3);font-size:10px;font-weight:600">
                            <i class="fa-solid fa-camera" style="font-size:18px"></i>
                            <span>Upload Logo</span>
                        </div>
                    @endif
                </div>
                <input type="file" name="logo" id="editLogoInput" accept="image/*" style="display:none" onchange="if(this.files[0]){var r=new FileReader();r.onload=function(e){var p=document.getElementById('editLogoPreview');p.innerHTML='<img src=\''+e.target.result+'\' style=\'width:100%;height:100%;object-fit:cover\'>'};r.readAsDataURL(this.files[0])}">
                <div style="font-size:11px;color:var(--text3);text-align:center;margin-top:6px">JPG, PNG, SVG &bull; Max 2MB</div>
                <div class="cd-field-feedback" data-field="logo" style="text-align:center"></div>
            </div>

            {{-- Basic Info --}}
            <div style="font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.4px;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px"><i class="fa-solid fa-building" style="font-size:13px;color:var(--text3)"></i> Basic Information</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group" style="grid-column:1/-1">
                    <label>Client Name <span style="color:#EF4444">*</span></label>
                    <input type="text" name="name" required value="{{ $client->name }}">
                    <div class="cd-field-feedback" data-field="name"></div>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" value="{{ $client->category }}">
                    <div class="cd-field-feedback" data-field="category"></div>
                </div>
                <div class="form-group">
                    <label>Emoji</label>
                    <input type="text" name="emoji" value="{{ $client->emoji }}" maxlength="10" style="font-size:18px">
                    <div class="cd-field-feedback" data-field="emoji"></div>
                </div>
                <div class="form-group">
                    <label>Brand Color</label>
                    <div style="display:flex;align-items:center;gap:10px">
                        <input type="color" name="color" value="{{ $client->color ?: '#4F6DF0' }}" style="width:40px;height:36px;border:1px solid var(--border);border-radius:8px;padding:2px;cursor:pointer;background:var(--bg)">
                    </div>
                    <div class="cd-field-feedback" data-field="color"></div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                        <input type="checkbox" name="is_active" value="1" {{ $client->is_active ? 'checked' : '' }} style="accent-color:var(--primary)">
                        <span style="font-weight:500;color:var(--text2)">Active</span>
                    </label>
                </div>
            </div>

            {{-- Brand Collateral Assets --}}
            <div style="font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.4px;margin:18px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px"><i class="fa-solid fa-photo-film" style="font-size:13px;color:var(--text3)"></i> Brand Collateral (CTA Video & Footer Image)</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label><i class="fa-solid fa-video" style="color:var(--primary)"></i> CTA Video</label>
                    <input type="file" name="cta_video" accept="video/*" style="font-size:11.5px">
                    @if($client->cta_video)
                        <div style="margin-top:6px;font-size:11px;color:var(--text2);display:flex;align-items:center;justify-content:space-between">
                            <span title="{{ basename($client->cta_video) }}" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px">
                                <i class="fa-solid fa-check" style="color:#059669"></i> {{ basename($client->cta_video) }}
                            </span>
                            <label style="display:flex;align-items:center;gap:4px;color:#EF4444;cursor:pointer;font-size:11px">
                                <input type="checkbox" name="delete_cta_video" value="1"> Remove
                            </label>
                        </div>
                    @endif
                    <div class="cd-field-feedback" data-field="cta_video"></div>
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-image" style="color:#EC4899"></i> Footer Image</label>
                    <input type="file" name="footer_image" accept="image/*" style="font-size:11.5px">
                    @if($client->footer_image)
                        <div style="margin-top:6px;font-size:11px;color:var(--text2);display:flex;align-items:center;justify-content:space-between">
                            <span title="{{ basename($client->footer_image) }}" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:160px">
                                <i class="fa-solid fa-check" style="color:#059669"></i> {{ basename($client->footer_image) }}
                            </span>
                            <label style="display:flex;align-items:center;gap:4px;color:#EF4444;cursor:pointer;font-size:11px">
                                <input type="checkbox" name="delete_footer_image" value="1"> Remove
                            </label>
                        </div>
                    @endif
                    <div class="cd-field-feedback" data-field="footer_image"></div>
                </div>
            </div>

            {{-- Contact Info --}}
            <div style="font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.4px;margin:18px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px"><i class="fa-solid fa-address-book" style="font-size:13px;color:var(--text3)"></i> Contact Information</div>
            <div style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.15);border-radius:10px;padding:12px 14px;font-size:12px;color:#6366F1;display:flex;align-items:flex-start;gap:8px">
                <i class="fa-solid fa-info-circle" style="margin-top:1px"></i>
                <span>Manage multiple contacts (Owner, Marketing, Billing, etc.) using the <strong>Contacts</strong> card in the sidebar. The primary contact's details are shown there.</span>
            </div>
            <input type="hidden" name="contact_person" value="{{ $client->contact_person }}">
            <input type="hidden" name="contact_email"  value="{{ $client->contact_email }}">
            <input type="hidden" name="contact_phone"  value="{{ $client->contact_phone }}">

            {{-- Website & Social Links --}}
            <div style="font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.4px;margin:18px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px"><i class="fa-solid fa-share-nodes" style="font-size:13px;color:var(--text3)"></i> Website & Social Links</div>
            <div style="display:grid;grid-template-columns:1fr;gap:14px">
                <div class="form-group">
                    <label><i class="fa-solid fa-globe" style="color:#6366F1"></i> Website</label>
                    <input type="url" name="website" value="{{ $client->website }}">
                    <div class="cd-field-feedback" data-field="website"></div>
                </div>
                <div style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.15);border-radius:10px;padding:12px 14px;font-size:12px;color:#6366F1;display:flex;align-items:flex-start;gap:8px">
                    <i class="fa-solid fa-info-circle" style="margin-top:1px"></i>
                    <span>Social media accounts can be managed using the <strong>Social Links</strong> button in the toolbar above.</span>
                </div>
            </div>

            {{-- Notes --}}
            <div style="font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.4px;margin:18px 0 10px;padding-bottom:6px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px"><i class="fa-solid fa-sticky-note" style="font-size:13px;color:var(--text3)"></i> Notes</div>
            <div class="form-group">
                <textarea name="notes" rows="3" style="resize:vertical">{{ $client->notes }}</textarea>
                <div class="cd-field-feedback" data-field="notes"></div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="document.getElementById('editClientModal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ UPLOAD / MANAGE COLLATERAL MODAL ============ --}}
<div class="modal-overlay" id="collateralModal">
    <div class="modal" style="width:560px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-photo-film" style="color:var(--primary)"></i> Brand Collateral (CTA Video & Footer Image)</div>
            <span class="modal-close" onclick="document.getElementById('collateralModal').classList.remove('show')">&times;</span>
        </div>

        <form method="POST" action="{{ route('admin.clients.update', $client) }}" enctype="multipart/form-data" autocomplete="off">
            @csrf
            @method('PUT')
            <input type="hidden" name="_from_detail" value="1">
            <input type="hidden" name="name" value="{{ $client->name }}">
            <input type="hidden" name="category" value="{{ $client->category }}">
            <input type="hidden" name="emoji" value="{{ $client->emoji }}">
            <input type="hidden" name="color" value="{{ $client->color }}">
            <input type="hidden" name="website" value="{{ $client->website }}">
            <input type="hidden" name="notes" value="{{ $client->notes }}">
            <input type="hidden" name="contact_person" value="{{ $client->contact_person }}">
            <input type="hidden" name="contact_email" value="{{ $client->contact_email }}">
            <input type="hidden" name="contact_phone" value="{{ $client->contact_phone }}">
            @if($client->is_active)
                <input type="hidden" name="is_active" value="1">
            @endif

            <div style="display:flex;flex-direction:column;gap:18px">
                {{-- CTA Video Field --}}
                <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                        <label style="font-size:13px;font-weight:700;color:var(--text);margin:0;display:flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-video" style="color:var(--primary)"></i> CTA Video
                        </label>
                        @if($client->cta_video)
                            <span style="font-size:11px;color:#059669;font-weight:600;background:rgba(16,185,129,0.12);padding:2px 8px;border-radius:4px">
                                <i class="fa-solid fa-circle-check"></i> Present
                            </span>
                        @endif
                    </div>
                    <p style="font-size:11.5px;color:var(--text3);margin:0 0 10px">Upload MP4, MOV, AVI, WEBM, or MKV video (Max 100MB).</p>
                    <input type="file" name="cta_video" id="modalCtaVideoInput" accept="video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska,video/*" style="font-size:12px;width:100%;padding:8px;border:1px dashed var(--border);border-radius:8px;background:var(--card)">
                    @if($client->cta_video)
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;padding-top:8px;border-top:1px dashed var(--border)">
                            <span style="font-size:11.5px;color:var(--text2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:240px">
                                <i class="fa-regular fa-file-video"></i> {{ basename($client->cta_video) }}
                            </span>
                            <label style="display:flex;align-items:center;gap:4px;color:#EF4444;cursor:pointer;font-size:11.5px;font-weight:600">
                                <input type="checkbox" name="delete_cta_video" value="1" style="accent-color:#EF4444"> Remove
                            </label>
                        </div>
                    @endif
                    <div class="cd-field-feedback" data-field="cta_video"></div>
                </div>

                {{-- Footer Image Field --}}
                <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:16px">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                        <label style="font-size:13px;font-weight:700;color:var(--text);margin:0;display:flex;align-items:center;gap:6px">
                            <i class="fa-solid fa-image" style="color:#EC4899"></i> Footer Image
                        </label>
                        @if($client->footer_image)
                            <span style="font-size:11px;color:#059669;font-weight:600;background:rgba(16,185,129,0.12);padding:2px 8px;border-radius:4px">
                                <i class="fa-solid fa-circle-check"></i> Present
                            </span>
                        @endif
                    </div>
                    <p style="font-size:11.5px;color:var(--text3);margin:0 0 10px">Upload JPG, PNG, WEBP, or SVG image (Max 10MB).</p>
                    <input type="file" name="footer_image" id="modalFooterImageInput" accept="image/*" style="font-size:12px;width:100%;padding:8px;border:1px dashed var(--border);border-radius:8px;background:var(--card)">
                    @if($client->footer_image)
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;padding-top:8px;border-top:1px dashed var(--border)">
                            <span style="font-size:11.5px;color:var(--text2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:240px">
                                <i class="fa-regular fa-file-image"></i> {{ basename($client->footer_image) }}
                            </span>
                            <label style="display:flex;align-items:center;gap:4px;color:#EF4444;cursor:pointer;font-size:11.5px;font-weight:600">
                                <input type="checkbox" name="delete_footer_image" value="1" style="accent-color:#EF4444"> Remove
                            </label>
                        </div>
                    @endif
                    <div class="cd-field-feedback" data-field="footer_image"></div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="document.getElementById('collateralModal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-check"></i> Save Collateral
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ ADD SOCIAL ACCOUNTS (multi-row) MODAL ============ --}}
<div class="modal-overlay" id="cdSocialBuilderModal">
    <div class="modal" style="width:640px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title">
                <i class="fa-solid fa-share-nodes" style="color:var(--primary)"></i>
                <span>Add Social Accounts</span>
            </div>
            <span class="modal-close" onclick="cdCloseSocialBuilder()">&times;</span>
        </div>

        <p style="font-size:12.5px;color:var(--text2);margin:0 0 14px">Add one or more accounts. Pick the platform from the dropdown, paste the URL, and an optional username/label. Click <strong>+ Another</strong> to add more rows.</p>

        <form id="cdSocialBuilderForm" autocomplete="off">
            <div id="cdSocialBuilderRows"></div>

            <button type="button" class="cd-row-add-btn" onclick="cdAddSocialRow()">
                <i class="fa-solid fa-plus"></i> Another row
            </button>

            <div id="cdSocialBuilderError" style="color:#EF4444;font-size:12px;margin-top:12px;display:none"></div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="cdCloseSocialBuilder()">Cancel</button>
                <button type="submit" class="btn-primary" id="cdSocialBuilderSubmit">
                    <i class="fa-solid fa-check"></i> Save All
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ EDIT SINGLE SOCIAL LINK MODAL ============ --}}
<div class="modal-overlay" id="cdSocialEditModal">
    <div class="modal" style="width:480px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title">
                <i class="fa-solid fa-pen" style="color:var(--primary)"></i>
                <span>Edit Social Link</span>
            </div>
            <span class="modal-close" onclick="cdCloseSocialEdit()">&times;</span>
        </div>

        <form id="cdSocialEditForm" autocomplete="off">
            <input type="hidden" id="cdSocialEditId">

            <div class="form-group">
                <label>Platform</label>
                <select id="cdSocialEditPlatform" required>
                    @foreach(['instagram','facebook','twitter','linkedin','youtube','tiktok','whatsapp'] as $p)
                        <option value="{{ $p }}">{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>URL</label>
                <input type="url" id="cdSocialEditUrl" placeholder="https://instagram.com/your-handle" required>
            </div>

            <div class="form-group">
                <label>Label / handle <span style="color:var(--text3);font-weight:400;font-size:11px">(optional)</span></label>
                <input type="text" id="cdSocialEditLabel" maxlength="100" placeholder="e.g. @main, Marketing Account">
            </div>

            <div id="cdSocialEditError" style="color:#EF4444;font-size:12px;margin-top:10px;display:none"></div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="cdCloseSocialEdit()">Cancel</button>
                <button type="submit" class="btn-primary" id="cdSocialEditSubmit">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ ADD / EDIT CONTACT MODAL ============ --}}
<div class="modal-overlay" id="cdContactModal">
    <div class="modal" style="width:480px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title">
                <i class="fa-solid fa-address-book" style="color:var(--primary)"></i>
                <span id="cdContactModalTitle">Add Contact</span>
            </div>
            <span class="modal-close" onclick="cdCloseContactModal()">&times;</span>
        </div>

        <form id="cdContactForm" autocomplete="off">
            <input type="hidden" name="contact_id" id="cdContactId" value="">

            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" id="cdContactName" maxlength="255" placeholder="e.g. Jane Smith">
            </div>

            <div class="form-group">
                <label>Role <span style="color:var(--text3);font-weight:400;font-size:11px">(optional)</span></label>
                <input type="text" name="role" id="cdContactRole" maxlength="100" placeholder="e.g. Marketing Director, Owner, Billing">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="cdContactEmail" maxlength="255" placeholder="jane@example.com">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" id="cdContactPhone" maxlength="50" inputmode="tel" placeholder="+1 555 123 4567">
                </div>
            </div>

            <label style="display:flex;align-items:center;gap:8px;margin-top:6px;font-size:13px;cursor:pointer">
                <input type="checkbox" name="is_primary" id="cdContactPrimary" value="1" style="accent-color:var(--primary)">
                <span>Set as primary contact</span>
            </label>

            <div id="cdContactError" style="color:#EF4444;font-size:12px;margin-top:10px;display:none"></div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="cdCloseContactModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="cdContactSubmit">
                    <i class="fa-solid fa-check"></i> Save Contact
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ CREATE CLIENT LOGIN MODAL ============ --}}
<div class="modal-overlay" id="createLoginModal">
    <div class="modal" style="width:480px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-user-plus" style="color:var(--primary)"></i> Create Client Login</div>
            <span class="modal-close" onclick="document.getElementById('createLoginModal').classList.remove('show')">&times;</span>
        </div>

        <div style="background:var(--primary-dim);border-radius:10px;padding:12px 14px;margin-bottom:18px;font-size:12px;color:var(--primary);display:flex;align-items:flex-start;gap:8px">
            <i class="fa-solid fa-info-circle" style="margin-top:1px"></i>
            <span>This will create a login for <strong>{{ $client->name }}</strong>. The client will be able to view published content and select festivals for new posts.</span>
        </div>

        <form id="createClientLoginForm" method="POST" action="{{ route('admin.clients.create-login', $client) }}" autocomplete="off">
            @csrf
            <input type="hidden" name="_form" value="create_client_login">
            <div style="display:flex;flex-direction:column;gap:14px">
                <div class="form-group">
                    <label>Full Name <span style="color:#EF4444">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $client->contact_person) }}" placeholder="e.g. {{ $client->contact_person ?: 'John Doe' }}">
                    <div class="create-login-feedback" data-field="name"></div>
                </div>
                <div class="form-group">
                    <label>Email <span style="color:#EF4444">*</span></label>
                    <input type="email" name="email" required value="{{ old('email', $client->contact_email) }}" placeholder="e.g. {{ $client->contact_email ?: 'client@example.com' }}">
                    <div class="create-login-feedback" data-field="email"></div>
                </div>
                <div class="form-group">
                    <label>Password <span style="font-size:11px;color:var(--text3);font-weight:400">(Optional - Default: password)</span></label>
                    <div style="position:relative">
                        <input type="password" name="password" minlength="6" id="createLoginPassword" placeholder="Enter password or leave for default">
                        <button type="button" onclick="togglePassword('createLoginPassword', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;padding:4px">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="create-login-feedback" data-field="password"></div>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;padding-top:16px;border-top:1px solid var(--border)">
                <button type="button" class="btn-sec" onclick="document.getElementById('createLoginModal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-user-plus"></i> Create Login
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MANAGE CLIENT LOGINS MODAL ============ --}}
<div class="modal-overlay" id="manageLoginModal">
    <div class="modal" style="width:560px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-users-gear" style="color:var(--primary)"></i> Client Logins</div>
            <span class="modal-close" onclick="document.getElementById('manageLoginModal').classList.remove('show')">&times;</span>
        </div>

        {{-- Existing Logins --}}
        @foreach($clientLogins as $loginUser)
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:12px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:36px;height:36px;border-radius:10px;background:{{ $loginUser->avatar_color ?? 'var(--primary)' }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700">{{ strtoupper(substr($loginUser->name, 0, 1)) }}</div>
                    <div>
                        <div style="font-size:13px;font-weight:700;color:var(--text)">{{ $loginUser->name }}</div>
                        <div style="font-size:11.5px;color:var(--text3)">{{ $loginUser->email }}</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:6px">
                    @if($loginUser->last_active_at)
                        <span style="font-size:10px;color:{{ $loginUser->isOnline() ? '#059669' : 'var(--text3)' }};font-weight:600">
                            <i class="fa-solid fa-circle" style="font-size:6px;vertical-align:middle"></i>
                            {{ $loginUser->isOnline() ? 'Online' : 'Last active ' . $loginUser->last_active_at->diffForHumans() }}
                        </span>
                    @else
                        <span style="font-size:10px;color:var(--text3);font-weight:600">Never logged in</span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admin.clients.update-login', [$client, $loginUser]) }}" autocomplete="off" style="display:flex;flex-direction:column;gap:10px">
                @csrf
                @method('PUT')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div class="form-group">
                        <label style="font-size:11px">Name</label>
                        <input type="text" name="name" required value="{{ $loginUser->name }}" style="font-size:12.5px;padding:8px 10px">
                    </div>
                    <div class="form-group">
                        <label style="font-size:11px">Email</label>
                        <input type="email" name="email" required value="{{ $loginUser->email }}" style="font-size:12.5px;padding:8px 10px">
                    </div>
                </div>
                <div class="form-group">
                    <label style="font-size:11px">New Password <span style="color:var(--text3);font-weight:400">(leave blank to keep current)</span></label>
                    <input type="password" name="password" minlength="6" placeholder="Enter new password..." style="font-size:12.5px;padding:8px 10px">
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding-top:8px">
                    <button
                        type="button"
                        style="background:none;border:none;color:#EF4444;font-size:12px;font-weight:600;cursor:pointer;padding:4px 8px;border-radius:6px;transition:all .15s"
                        onmouseover="this.style.background='#FEF2F2'"
                        onmouseout="this.style.background='none'"
                        onclick="if(confirm('Remove this login? This cannot be undone.')){document.getElementById('delete-login-{{ $loginUser->id }}').submit();}"
                    >
                        <i class="fa-solid fa-trash-can"></i> Remove
                    </button>
                    <button type="submit" class="btn-primary" style="font-size:12px;padding:7px 14px">
                        <i class="fa-solid fa-check"></i> Save
                    </button>
                </div>
            </form>
            <form id="delete-login-{{ $loginUser->id }}" method="POST" action="{{ route('admin.clients.delete-login', [$client, $loginUser]) }}" style="display:none">
                @csrf
                @method('DELETE')
            </form>
        </div>
        @endforeach

        {{-- Add Another Login --}}
        <div style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px">
            <button type="button" class="btn-sec" style="width:100%;justify-content:center" onclick="document.getElementById('manageLoginModal').classList.remove('show');document.getElementById('createLoginModal').classList.add('show')">
                <i class="fa-solid fa-plus"></i> Add Another Login
            </button>
        </div>
    </div>
</div>

{{-- ============ MANAGE SOCIAL MEDIA LINKS MODAL ============ --}}
<div class="modal-overlay" id="manageSocialLinksModal">
    <div class="modal" style="width:600px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-share-nodes" style="color:var(--primary)"></i> Social Media Links</div>
            <span class="modal-close" onclick="document.getElementById('manageSocialLinksModal').classList.remove('show')">&times;</span>
        </div>

        @php
            $platforms = [
                'instagram' => ['name' => 'Instagram', 'icon' => 'fa-instagram', 'color' => '#E1306C'],
                'facebook' => ['name' => 'Facebook', 'icon' => 'fa-facebook-f', 'color' => '#1877F2'],
                'twitter' => ['name' => 'Twitter/X', 'icon' => 'fa-x-twitter', 'color' => '#000000'],
                'linkedin' => ['name' => 'LinkedIn', 'icon' => 'fa-linkedin-in', 'color' => '#0077B5'],
                'youtube' => ['name' => 'YouTube', 'icon' => 'fa-youtube', 'color' => '#FF0000'],
                'tiktok' => ['name' => 'TikTok', 'icon' => 'fa-tiktok', 'color' => '#000000'],
                'whatsapp' => ['name' => 'WhatsApp', 'icon' => 'fa-whatsapp', 'color' => '#25D366'],
            ];
        @endphp

        {{-- Existing Links Grouped by Platform --}}
        @foreach($platforms as $platformId => $platformInfo)
            @php
                $platformLinks = $client->getSocialLinksByPlatform($platformId);
            @endphp
            <div style="margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border)">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                    <i class="fa-brands {{ $platformInfo['icon'] }}" style="font-size:18px;color:{{ $platformInfo['color'] }}"></i>
                    <h4 style="margin:0;font-size:13px;font-weight:700;color:var(--text)">{{ $platformInfo['name'] }}</h4>
                    <span style="margin-left:auto;font-size:11px;color:var(--text3);background:var(--bg);padding:2px 8px;border-radius:4px">{{ $platformLinks->count() }} account{{ $platformLinks->count() !== 1 ? 's' : '' }}</span>
                </div>

                @if($platformLinks->count() > 0)
                    @foreach($platformLinks as $link)
                        <div style="background:var(--bg);border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:8px;display:flex;gap:10px;align-items:flex-start">
                            <div style="flex:1;min-width:0">
                                <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener" style="color:var(--primary);font-size:12px;font-weight:600;text-decoration:none;word-break:break-all">{{ $link->url }}</a>
                                    @if($link->is_primary)
                                        <span style="font-size:9px;font-weight:700;background:var(--primary);color:#fff;padding:2px 6px;border-radius:3px">Primary</span>
                                    @endif
                                </div>
                                @if($link->label)
                                    <div style="font-size:11px;color:var(--text3)">Label: {{ $link->label }}</div>
                                @endif
                            </div>
                            <div style="display:flex;gap:6px">
                                <form method="POST" action="{{ route('admin.clients.delete-social-link', [$client, $link]) }}" onsubmit="return confirm('Remove this link?')" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:#EF4444;cursor:pointer;padding:4px 8px;border-radius:6px;font-size:12px;transition:all .15s" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:12px;color:var(--text3);font-size:12px;background:var(--bg);border-radius:8px">
                        No accounts added
                    </div>
                @endif

                {{-- Add New Link Form --}}
                <form method="POST" action="{{ route('admin.clients.add-social-link', $client) }}" class="social-link-form" data-platform="{{ $platformId }}" style="margin-top:10px;padding:12px;background:var(--card);border:1px dashed var(--border);border-radius:8px">
                    @csrf
                    <input type="hidden" name="platform" value="{{ $platformId }}">
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
                        <div style="flex:1;min-width:200px">
                            <label style="font-size:11px;font-weight:600;color:var(--text3)">URL</label>
                            <input type="url" name="url" placeholder="https://..." required style="width:100%;padding:7px 10px;font-size:12px;border:1px solid var(--border);border-radius:6px;background:var(--card2)">
                            <div class="social-field-feedback" data-field="url"></div>
                        </div>
                        <div style="flex:0 1 120px">
                            <label style="font-size:11px;font-weight:600;color:var(--text3)">Username</label>
                            <input type="text" name="label" placeholder="e.g. Main Account" style="width:100%;padding:7px 10px;font-size:12px;border:1px solid var(--border);border-radius:6px;background:var(--card2)">
                            <div class="social-field-feedback" data-field="label"></div>
                        </div>
                        <button type="submit" class="btn-primary" style="font-size:11px;padding:7px 12px;white-space:nowrap">
                            <i class="fa-solid fa-plus"></i> Add
                        </button>
                    </div>
                    <div class="social-field-feedback" data-field="platform"></div>
                </form>
            </div>
        @endforeach

        <div style="padding-top:12px">
            <button type="button" class="btn-sec" style="width:100%;justify-content:center" onclick="document.getElementById('manageSocialLinksModal').classList.remove('show')">
                <i class="fa-solid fa-times"></i> Close
            </button>
        </div>
    </div>
</div>

{{-- ===== CLIENT INFO HEADER ===== --}}
<div class="cd-info-grid">
    {{-- Left: Overview Card --}}
    <div class="cd-overview-card" style="--brand:{{ $client->color }}">
        <div class="cd-overview-accent" style="background:{{ $client->color }}"></div>

        {{-- Progress Ring --}}
        <div class="cd-progress-ring-wrap">
            <svg class="cd-progress-ring" width="90" height="90" viewBox="0 0 90 90">
                <circle cx="45" cy="45" r="38" stroke="var(--border)" stroke-width="6" fill="none"/>
                <circle cx="45" cy="45" r="38" stroke="{{ $client->color }}" stroke-width="6" fill="none"
                    stroke-dasharray="{{ 2 * 3.14159 * 38 }}"
                    stroke-dashoffset="{{ 2 * 3.14159 * 38 * (1 - $completionPct/100) }}"
                    stroke-linecap="round" transform="rotate(-90 45 45)"
                    style="transition:stroke-dashoffset .8s ease"/>
            </svg>
            <div class="cd-progress-ring-text">
                <span class="cd-ring-pct">{{ $completionPct }}%</span>
                <span class="cd-ring-label">Complete</span>
            </div>
        </div>

        {{-- Stats --}}
        <div class="cd-overview-stats">
            <div class="cd-ov-stat">
                <div class="cd-ov-val" style="color:{{ $client->color }}">{{ $taskStats['active'] }}</div>
                <div class="cd-ov-label">Active Tasks</div>
            </div>
            <div class="cd-ov-stat">
                <div class="cd-ov-val" style="color:var(--teal)">{{ $taskStats['completed'] }}</div>
                <div class="cd-ov-label">Completed</div>
            </div>
            <div class="cd-ov-stat">
                <div class="cd-ov-val" style="color:#EF4444">{{ $taskStats['overdue'] }}</div>
                <div class="cd-ov-label">Overdue</div>
            </div>
            <div class="cd-ov-stat">
                <div class="cd-ov-val" style="color:var(--purple)">{{ $actionStats['total'] }}</div>
                <div class="cd-ov-label">Action Items</div>
            </div>
        </div>
    </div>

    {{-- Middle: Contacts + Social --}}
    <div class="cd-contact-card">
        <div class="cd-contacts-head">
            <h3 class="cd-section-title"><i class="fa-solid fa-address-book"></i> Contacts</h3>
            <button type="button" class="cd-contacts-add-btn" onclick="cdOpenContactModal()" title="Add a new contact">
                <i class="fa-solid fa-plus"></i> Add
            </button>
        </div>

        @php $contacts = $client->contacts; @endphp

        @if($contacts->isNotEmpty())
            <div class="cd-contacts-list" id="cd-contacts-list">
                @foreach($contacts as $contact)
                    <div class="cd-contact-item {{ $contact->is_primary ? 'is-primary' : '' }}" data-contact-id="{{ $contact->id }}">
                        <div class="cd-contact-item-head">
                            <div class="cd-contact-name-row">
                                <span class="cd-contact-name">{{ $contact->name ?: 'Unnamed' }}</span>
                                @if($contact->role)
                                    <span class="cd-contact-role">{{ $contact->role }}</span>
                                @endif
                                @if($contact->is_primary)
                                    <span class="cd-primary-tag" title="Primary contact">PRIMARY</span>
                                @endif
                            </div>
                            <div class="cd-contact-actions">
                                @if(! $contact->is_primary)
                                    <button type="button" class="cd-icon-btn" onclick="cdSetPrimaryContact({{ $contact->id }})" title="Set as primary">
                                        <i class="fa-regular fa-star"></i>
                                    </button>
                                @endif
                                <button type="button" class="cd-icon-btn" onclick="cdOpenContactModal({{ $contact->id }})" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button type="button" class="cd-icon-btn cd-icon-btn-danger" onclick="cdDeleteContact({{ $contact->id }})" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @if($contact->email || $contact->phone)
                            <div class="cd-contact-channels">
                                @if($contact->email)
                                    <a href="mailto:{{ $contact->email }}" class="cd-contact-channel">
                                        <i class="fa-solid fa-envelope"></i> {{ $contact->email }}
                                    </a>
                                @endif
                                @if($contact->phone)
                                    <a href="tel:{{ $contact->phone }}" class="cd-contact-channel">
                                        <i class="fa-solid fa-phone"></i> {{ $contact->phone }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="cd-no-info" id="cd-contacts-empty"><i class="fa-solid fa-user-plus"></i> No contacts yet — click <strong>Add</strong> above</div>
        @endif

        <div class="cd-online-presence">
            <div class="cd-presence-head">
                <h4 class="cd-presence-title"><i class="fa-solid fa-share-nodes"></i> Online Presence</h4>
                <span class="cd-presence-count">{{ ($client->website ? 1 : 0) + $client->socialMediaLinks->count() }}</span>
                <button type="button" class="cd-presence-add-btn" onclick="cdOpenSocialBuilder()" title="Add accounts">
                    <i class="fa-solid fa-plus"></i> Add
                </button>
            </div>

            @if($client->website || $client->socialMediaLinks->count())
                <div class="cd-presence-grid">
                    @if($client->website)
                        @php
                            $host = parse_url($client->website, PHP_URL_HOST) ?: $client->website;
                            $host = preg_replace('/^www\./', '', $host);
                        @endphp
                        <a href="{{ $client->website }}" target="_blank" rel="noopener"
                           class="cd-presence-tile" style="--brand:#6366F1">
                            <span class="cd-presence-icon"><i class="fa-solid fa-globe"></i></span>
                            <span class="cd-presence-text">
                                <span class="cd-presence-platform">Website</span>
                                <span class="cd-presence-handle">{{ $host }}</span>
                            </span>
                            <button type="button" class="cd-presence-copy" data-copy="{{ $client->website }}" title="Copy URL" onclick="event.preventDefault(); event.stopPropagation(); cdCopyToClipboard(this);">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </a>
                    @endif

                    @foreach($client->socialMediaLinks as $link)
                        @php
                            $icon = $link->getPlatformIcon();
                            $display = $link->label ?: ('@' . preg_replace('#^https?://(www\.)?#', '', rtrim($link->url, '/')));
                            if (mb_strlen($display) > 28) $display = mb_substr($display, 0, 26) . '…';
                        @endphp
                        <a href="{{ $link->url }}" target="_blank" rel="noopener"
                           class="cd-presence-tile has-actions" style="--brand:{{ $icon['color'] }}"
                           data-link-id="{{ $link->id }}"
                           data-platform="{{ $link->platform }}"
                           data-url="{{ $link->url }}"
                           data-label="{{ $link->label }}"
                           title="{{ $link->url }}">
                            <span class="cd-presence-icon"><i class="fa-brands {{ $icon['icon'] }}"></i></span>
                            <span class="cd-presence-text">
                                <span class="cd-presence-platform">{{ ucfirst($link->platform) }}</span>
                                <span class="cd-presence-handle">{{ $display }}</span>
                            </span>
                            <div class="cd-presence-actions">
                                <button type="button" class="cd-presence-action" data-copy="{{ $link->url }}" title="Copy URL" onclick="event.preventDefault(); event.stopPropagation(); cdCopyToClipboard(this);">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                                <button type="button" class="cd-presence-action" title="Edit" onclick="event.preventDefault(); event.stopPropagation(); cdEditSocialLink({{ $link->id }});">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button type="button" class="cd-presence-action cd-presence-action-danger" title="Delete" onclick="event.preventDefault(); event.stopPropagation(); cdDeleteSocialLink({{ $link->id }});">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="cd-no-info cd-presence-empty">
                    <i class="fa-solid fa-link-slash"></i>
                    No website or social links yet — use the <strong>Social Links</strong> button above to add some
                </div>
            @endif
        </div>
    </div>

    {{-- Right: Notes --}}
    <div class="cd-notes-card">
        <h3 class="cd-section-title"><i class="fa-solid fa-sticky-note"></i> Client Notes</h3>
        @if($client->notes)
            <div class="cd-notes-text">{{ $client->notes }}</div>
        @else
            <div class="cd-no-info"><i class="fa-solid fa-note-sticky"></i> No notes added</div>
        @endif

        {{-- Quick stats breakdown --}}
        <div class="cd-mini-stats">
            <div class="cd-mini-row">
                <span>In Progress</span>
                <span class="cd-mini-val">{{ $taskStats['in_progress'] }}</span>
            </div>
            <div class="cd-mini-row">
                <span>In Review</span>
                <span class="cd-mini-val">{{ $taskStats['in_review'] }}</span>
            </div>
            <div class="cd-mini-row">
                <span>Action Items Pending</span>
                <span class="cd-mini-val">{{ $actionStats['pending'] }}</span>
            </div>
            <div class="cd-mini-row">
                <span>Action Items Overdue</span>
                <span class="cd-mini-val" style="color:#EF4444">{{ $actionStats['overdue'] }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ===== BRAND COLLATERAL & MEDIA ASSETS (CTA Video & Footer Image) ===== --}}
<div class="cd-collateral-section" style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
        <div>
            <h3 style="margin:0;font-size:16px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-photo-film" style="color:var(--primary);font-size:18px"></i>
                Brand Collateral & Assets
            </h3>
            <div style="font-size:12px;color:var(--text3);margin-top:3px">
                CTA Video and Footer Image collateral for {{ $client->name }}'s marketing and design creatives
            </div>
        </div>
        <button type="button" class="btn-sec" onclick="document.getElementById('collateralModal').classList.add('show')" style="font-size:12px;padding:8px 14px;display:inline-flex;align-items:center;gap:6px">
            <i class="fa-solid fa-cloud-arrow-up"></i> Upload / Replace
        </button>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(300px, 1fr));gap:18px">
        {{-- Card 1: CTA Video --}}
        <div style="background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:18px;display:flex;flex-direction:column;justify-content:space-between">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:32px;height:32px;border-radius:8px;background:rgba(99,102,241,0.12);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:14px">
                            <i class="fa-solid fa-video"></i>
                        </div>
                        <div>
                            <div style="font-size:14px;font-weight:700;color:var(--text)">CTA Video</div>
                            <div style="font-size:11px;color:var(--text3)">Call-To-Action video asset</div>
                        </div>
                    </div>
                    @if($client->cta_video)
                        <span style="font-size:10.5px;font-weight:700;background:rgba(16,185,129,0.12);color:#059669;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px">
                            <i class="fa-solid fa-circle-check"></i> Available
                        </span>
                    @else
                        <span style="font-size:10.5px;font-weight:600;background:var(--bg);color:var(--text3);padding:3px 8px;border-radius:6px;border:1px solid var(--border)">
                            Not Uploaded
                        </span>
                    @endif
                </div>

                @if($client->cta_video)
                    <div style="border-radius:10px;overflow:hidden;background:#000;border:1px solid var(--border);margin-bottom:12px">
                        <video controls preload="metadata" style="width:100%;max-height:220px;display:block;outline:none" src="{{ asset('storage/' . $client->cta_video) }}">
                            Your browser does not support HTML5 video.
                        </video>
                    </div>
                @else
                    <div style="border:2px dashed var(--border);border-radius:10px;padding:30px 16px;text-align:center;background:var(--bg);margin-bottom:12px">
                        <div style="width:44px;height:44px;border-radius:50%;background:rgba(99,102,241,0.1);color:var(--primary);display:inline-flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:8px">
                            <i class="fa-solid fa-video-slash"></i>
                        </div>
                        <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:3px">No CTA Video</div>
                        <div style="font-size:11.5px;color:var(--text3);margin-bottom:12px">Upload a CTA video clip for this client</div>
                        <button type="button" class="btn-sec" onclick="document.getElementById('collateralModal').classList.add('show')" style="font-size:11px;padding:6px 12px;display:inline-flex;align-items:center;gap:5px">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Upload Video
                        </button>
                    </div>
                @endif
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding-top:10px;border-top:1px solid var(--border);margin-top:auto">
                <div style="font-size:11.5px;color:var(--text2);display:flex;align-items:center;gap:6px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $client->cta_video ? basename($client->cta_video) : 'No file' }}">
                    <i class="fa-regular fa-file-video" style="color:var(--primary)"></i>
                    <span>{{ $client->cta_video ? basename($client->cta_video) : 'No video file' }}</span>
                </div>
                @if($client->cta_video)
                    <a href="{{ asset('storage/' . $client->cta_video) }}" download="{{ basename($client->cta_video) }}" class="btn-primary" style="font-size:11.5px;padding:6px 12px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;white-space:nowrap">
                        <i class="fa-solid fa-download"></i> Download Video
                    </a>
                @else
                    <button type="button" class="btn-sec" disabled style="font-size:11.5px;padding:6px 12px;opacity:0.5;cursor:not-allowed;display:inline-flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-download"></i> Download
                    </button>
                @endif
            </div>
        </div>

        {{-- Card 2: Footer Image --}}
        <div style="background:var(--card2);border:1px solid var(--border);border-radius:12px;padding:18px;display:flex;flex-direction:column;justify-content:space-between">
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:32px;height:32px;border-radius:8px;background:rgba(236,72,153,0.12);color:#EC4899;display:flex;align-items:center;justify-content:center;font-size:14px">
                            <i class="fa-solid fa-image"></i>
                        </div>
                        <div>
                            <div style="font-size:14px;font-weight:700;color:var(--text)">Footer Image</div>
                            <div style="font-size:11px;color:var(--text3)">Standard creative footer asset</div>
                        </div>
                    </div>
                    @if($client->footer_image)
                        <span style="font-size:10.5px;font-weight:700;background:rgba(16,185,129,0.12);color:#059669;padding:3px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px">
                            <i class="fa-solid fa-circle-check"></i> Available
                        </span>
                    @else
                        <span style="font-size:10.5px;font-weight:600;background:var(--bg);color:var(--text3);padding:3px 8px;border-radius:6px;border:1px solid var(--border)">
                            Not Uploaded
                        </span>
                    @endif
                </div>

                @if($client->footer_image)
                    <div style="border-radius:10px;overflow:hidden;background:var(--bg);border:1px solid var(--border);height:220px;display:flex;align-items:center;justify-content:center;padding:10px;margin-bottom:12px">
                        <img src="{{ asset('storage/' . $client->footer_image) }}" alt="{{ $client->name }} footer image" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:6px">
                    </div>
                @else
                    <div style="border:2px dashed var(--border);border-radius:10px;padding:30px 16px;text-align:center;background:var(--bg);margin-bottom:12px">
                        <div style="width:44px;height:44px;border-radius:50%;background:rgba(236,72,153,0.1);color:#EC4899;display:inline-flex;align-items:center;justify-content:center;font-size:18px;margin-bottom:8px">
                            <i class="fa-solid fa-image"></i>
                        </div>
                        <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:3px">No Footer Image</div>
                        <div style="font-size:11.5px;color:var(--text3);margin-bottom:12px">Upload a brand footer image for templates</div>
                        <button type="button" class="btn-sec" onclick="document.getElementById('collateralModal').classList.add('show')" style="font-size:11px;padding:6px 12px;display:inline-flex;align-items:center;gap:5px">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Upload Image
                        </button>
                    </div>
                @endif
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding-top:10px;border-top:1px solid var(--border);margin-top:auto">
                <div style="font-size:11.5px;color:var(--text2);display:flex;align-items:center;gap:6px;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $client->footer_image ? basename($client->footer_image) : 'No file' }}">
                    <i class="fa-regular fa-file-image" style="color:#EC4899"></i>
                    <span>{{ $client->footer_image ? basename($client->footer_image) : 'No image file' }}</span>
                </div>
                @if($client->footer_image)
                    <a href="{{ asset('storage/' . $client->footer_image) }}" download="{{ basename($client->footer_image) }}" class="btn-primary" style="font-size:11.5px;padding:6px 12px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;white-space:nowrap">
                        <i class="fa-solid fa-download"></i> Download Image
                    </a>
                @else
                    <button type="button" class="btn-sec" disabled style="font-size:11.5px;padding:6px 12px;opacity:0.5;cursor:not-allowed;display:inline-flex;align-items:center;gap:6px">
                        <i class="fa-solid fa-download"></i> Download
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== CLIENT VISITS & MEETINGS ===== --}}
<div style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:22px;margin-bottom:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
        <div>
            <h3 style="margin:0;font-size:16px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px">
                <i class="fa-solid fa-person-walking-luggage" style="color:var(--primary);font-size:18px"></i>
                Client Visits & Meeting Logs
            </h3>
            <div style="font-size:12px;color:var(--text3);margin-top:3px">
                In-person & virtual meeting logs, strategy visits, and follow-ups for {{ $client->name }}
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <a href="{{ route('admin.visits', ['tab' => 'client', 'client_id' => $client->id]) }}" class="btn-primary" style="font-size:12px;padding:7px 14px;display:inline-flex;align-items:center;gap:6px;text-decoration:none">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Visits Hub
            </a>
        </div>
    </div>

    @if($clientVisits->isNotEmpty())
        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:14px">
            @foreach($clientVisits->take(4) as $v)
                @php
                    $statusMeta = $v->status_details;
                    $modeMeta = $v->meeting_mode_details;
                @endphp
                <div style="background:var(--card2);border:1px solid var(--border);border-radius:10px;padding:14px;display:flex;flex-direction:column;justify-content:space-between;gap:8px">
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                            <span style="font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:5px;color:{{ $statusMeta['color'] }};background:{{ $statusMeta['bg'] }}">
                                <i class="{{ $statusMeta['icon'] }}"></i> {{ $statusMeta['label'] }}
                            </span>
                            <span style="font-size:11px;color:var(--text3)"><i class="{{ $modeMeta['icon'] }}"></i> {{ $modeMeta['label'] }}</span>
                        </div>
                        <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:4px">
                            {{ $v->purpose ?: 'General Client Visit' }}
                        </div>
                        <div style="font-size:11.5px;color:var(--text2);margin-bottom:6px">
                            <i class="fa-regular fa-calendar" style="color:var(--primary);margin-right:4px"></i> {{ $v->visit_date->format('M d, Y · h:i A') }}
                        </div>
                        @if($v->summary)
                            <div style="font-size:11.5px;color:var(--text3);line-height:1.4">
                                {{ Str::limit($v->summary, 80) }}
                            </div>
                        @endif
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid var(--border);font-size:11px;color:var(--text3)">
                        <span>By: <strong>{{ $v->visitor ? $v->visitor->name : 'Admin' }}</strong></span>
                        <a href="{{ route('admin.visits', ['tab' => 'client', 'search' => $client->name]) }}" style="color:var(--primary);text-decoration:none;font-weight:600">Details &rarr;</a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align:center;padding:24px 16px;background:var(--bg);border-radius:10px;border:1px dashed var(--border)">
            <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:4px">No Visits Logged Yet for this Client</div>
            <div style="font-size:11.5px;color:var(--text3);margin-bottom:12px">Log an in-person meeting, monthly strategy review, or onboarding session.</div>
            <a href="{{ route('admin.visits', ['tab' => 'client', 'client_id' => $client->id]) }}" class="btn-sec" style="font-size:11.5px;padding:6px 12px;text-decoration:none;display:inline-flex;align-items:center;gap:6px">
                <i class="fa-solid fa-plus"></i> Log Visit in Visits Hub
            </a>
        </div>
    @endif
</div>

{{-- ===== MONTHLY CONTENT SCHEDULE ===== --}}
@php
    $isCarriedForward = $effectiveSchedule && !$exactSchedule;
    $monthlySchedule = $effectiveSchedule; // For display purposes

    // Count completed/in-progress tasks by type for the SELECTED month
    $selectedMonthDate = \Carbon\Carbon::create($currentYear, $currentMonth, 1);
    $monthStart = $selectedMonthDate->copy()->startOfMonth();
    $monthEnd = $selectedMonthDate->copy()->endOfMonth();
    $monthTasks = \App\Models\Task::where('client_id', $client->id)
        ->where(function($q) use ($monthStart, $monthEnd) {
            $q->whereBetween('deadline', [$monthStart, $monthEnd])
              ->orWhereBetween('post_date', [$monthStart, $monthEnd])
              ->orWhereBetween('created_at', [$monthStart, $monthEnd]);
        })
        ->selectRaw("type, status, COUNT(*) as cnt")
        ->groupBy('type', 'status')
        ->get();

    // Map task types to schedule keys
    $typeMap = [
        'post' => 'posts', 'reel' => 'reels', 'story' => 'stories',
        'carousel' => 'carousel', 'video' => 'videos',
    ];
    $progress = [];
    foreach ($typeMap as $taskType => $scheduleKey) {
        $done = $monthTasks->where('type', $taskType)->whereIn('status', ['completed', 'published'])->sum('cnt');
        $active = $monthTasks->where('type', $taskType)->whereNotIn('status', ['completed', 'published'])->sum('cnt');
        $progress[$scheduleKey] = ['done' => $done, 'active' => $active, 'total' => $done + $active];
    }
    // "other" type catches everything not mapped
    $mappedTypes = array_keys($typeMap);
    $otherDone = $monthTasks->whereNotIn('type', $mappedTypes)->whereIn('status', ['completed', 'published'])->sum('cnt');
    $otherActive = $monthTasks->whereNotIn('type', $mappedTypes)->whereNotIn('status', ['completed', 'published'])->sum('cnt');
    $progress['other'] = ['done' => $otherDone, 'active' => $otherActive, 'total' => $otherDone + $otherActive];
@endphp
<div style="background:var(--card);border:1px solid var(--border);border-radius:14px;padding:24px;margin-bottom:20px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px">
        <h3 style="margin:0;font-size:16px;font-weight:800;color:var(--text);display:flex;align-items:center;gap:8px">
            <i class="fa-solid fa-calendar-days" style="color:var(--primary);font-size:18px"></i>
            Monthly Content Schedule
        </h3>

        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap">
            <form method="GET" action="{{ url()->current() }}" style="display:flex; gap:6px; align-items:center">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <select name="month" onchange="this.form.submit()" style="font-size:12px; padding:6px 10px; border-radius:8px; border:1px solid var(--border); background:var(--card2); color:var(--text); font-weight:600">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
                <select name="year" onchange="this.form.submit()" style="font-size:12px; padding:6px 10px; border-radius:8px; border:1px solid var(--border); background:var(--card2); color:var(--text); font-weight:600">
                    @for($y=now()->year - 1; $y<=now()->year + 2; $y++)
                        <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </form>

            <button type="button" onclick="document.getElementById('monthlyScheduleModal').classList.add('show')" class="btn-primary" style="font-size:12px;padding:8px 14px;gap:6px;display:flex;align-items:center">
                <i class="fa-solid fa-pencil"></i> {{ $exactSchedule ? 'Edit Schedule' : 'Set Schedule' }}
            </button>
        </div>
    </div>

    @if($monthlySchedule)
        @php
            $contentTypes = [
                'posts' => ['icon' => 'fa-pen-to-square', 'label' => 'Posts', 'color' => '#3B82F6'],
                'reels' => ['icon' => 'fa-film', 'label' => 'Reels', 'color' => '#F97316'],
                'stories' => ['icon' => 'fa-book-open', 'label' => 'Stories', 'color' => '#10B981'],
                'carousel' => ['icon' => 'fa-images', 'label' => 'Carousels', 'color' => '#EC4899'],
                'videos' => ['icon' => 'fa-video', 'label' => 'Videos', 'color' => '#8B5CF6'],
                'other' => ['icon' => 'fa-ellipsis', 'label' => 'Other', 'color' => '#6B7280'],
            ];
            $total = $monthlySchedule->getTotalContent();
            $totalDone = collect($progress)->sum('done');
            $totalActive = collect($progress)->sum('active');
            $overallPct = $total > 0 ? min(100, round(($totalDone / $total) * 100)) : 0;
        @endphp

        {{-- Header with overall progress --}}
        <div style="background:linear-gradient(135deg, var(--primary)06 0%, var(--purple)06 100%);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:14px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;flex-wrap:wrap;gap:6px">
                <div>
                    <div style="font-size:14px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        {{ \Carbon\Carbon::create(null, $currentMonth, 1)->format('F Y') }}
                        @if($isCarriedForward)
                            <span style="font-size:10px;font-weight:600;background:rgba(245,158,11,.12);color:#D97706;padding:2px 8px;border-radius:5px;display:inline-flex;align-items:center;gap:4px">
                                <i class="fa-solid fa-arrow-rotate-left" style="font-size:8px"></i> Using Strategy from {{ $monthlySchedule->getMonthYearLabel() }}
                            </span>
                        @endif
                    </div>
                    <div style="font-size:11.5px;color:var(--text3);margin-top:3px">
                        <strong>{{ $totalDone }}</strong> completed · <strong>{{ $totalActive }}</strong> in progress · <strong>{{ $total }}</strong> planned
                    </div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:26px;font-weight:800;color:{{ $overallPct >= 80 ? '#10B981' : ($overallPct >= 50 ? '#F59E0B' : 'var(--primary)') }};line-height:1">{{ $overallPct }}%</div>
                    <div style="font-size:10px;color:var(--text3);margin-top:2px">Completion</div>
                </div>
            </div>
            {{-- Overall progress bar --}}
            <div style="height:8px;background:var(--bg);border-radius:4px;overflow:hidden;position:relative">
                @if($total > 0)
                    <div style="height:100%;width:{{ min(100, ($totalDone / $total) * 100) }}%;background:linear-gradient(90deg,#10B981,#059669);border-radius:4px;position:absolute;left:0;top:0;transition:width .4s ease"></div>
                    <div style="height:100%;width:{{ min(100, (($totalDone + $totalActive) / $total) * 100) }}%;background:rgba(59,130,246,0.25);border-radius:4px;position:absolute;left:0;top:0;z-index:0"></div>
                @endif
            </div>
            <div style="display:flex;gap:14px;margin-top:6px;font-size:10px;color:var(--text3)">
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:#10B981;margin-right:3px"></span> Done</span>
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:rgba(59,130,246,0.4);margin-right:3px"></span> In Progress</span>
                <span><span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:var(--bg);border:1px solid var(--border);margin-right:3px"></span> Remaining</span>
            </div>
        </div>

        {{-- Content type grid with progress --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(120px, 1fr));gap:10px">
            @foreach($contentTypes as $key => $type)
                @php
                    $planned = $monthlySchedule->$key;
                    $done = $progress[$key]['done'] ?? 0;
                    $active = $progress[$key]['active'] ?? 0;

                    // Skip if no planned or completed content
                    if ($planned == 0 && $done == 0) continue;

                    $pct = $planned > 0 ? min(100, round(($done / $planned) * 100)) : ($done > 0 ? 100 : 0);
                    $statusColor = $planned == 0 ? 'var(--text3)' : ($pct >= 100 ? '#10B981' : ($pct >= 50 ? '#F59E0B' : $type['color']));
                @endphp
                <div class="cd-ct-item {{ $planned > 0 ? 'cd-ct-active' : '' }}" style="position:relative;overflow:hidden">
                    @if($planned > 0)
                        <div style="position:absolute;bottom:0;left:0;right:0;height:{{ $pct }}%;background:{{ $type['color'] }}10;transition:height .4s ease;z-index:0"></div>
                    @endif
                    <div style="position:relative;z-index:1">
                        <div style="font-size:18px;margin-bottom:4px;color:{{ $statusColor }}"><i class="fa-solid {{ $type['icon'] }}"></i></div>
                        <div style="font-size:16px;font-weight:800;color:{{ $statusColor }};line-height:1">
                            {{ $done }}<span style="font-size:11px;font-weight:600;color:var(--text3)">/{{ $planned }}</span>
                        </div>
                        <div style="font-size:9.5px;color:var(--text3);margin-top:4px;font-weight:600;letter-spacing:.3px">{{ $type['label'] }}</div>
                        @if($active > 0)
                            <div style="font-size:9px;color:{{ $type['color'] }};margin-top:2px;font-weight:600">{{ $active }} active</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="background:rgba(99,102,241,.06);border:2px dashed rgba(99,102,241,.3);border-radius:12px;padding:24px;text-align:center">
            <div style="font-size:32px;margin-bottom:8px">📅</div>
            <div style="font-size:13px;font-weight:700;color:var(--text);margin-bottom:4px">No schedule set for {{ \Carbon\Carbon::create(null, $currentMonth, 1)->format('F Y') }}</div>
            <div style="font-size:12px;color:var(--text2);margin-bottom:16px">Add a monthly content plan to track your content strategy</div>
            <button type="button" onclick="document.getElementById('monthlyScheduleModal').classList.add('show')" class="btn-primary" style="padding:8px 16px;font-size:12px">
                <i class="fa-solid fa-plus"></i> Create Schedule
            </button>
        </div>
    @endif
</div>

{{-- ===== MONTHLY SCHEDULE MODAL ===== --}}
<div class="modal-overlay" id="monthlyScheduleModal">
    <div class="modal" style="width:500px;max-height:90vh;overflow-y:auto">
        <div class="modal-head">
            <div class="modal-title"><i class="fa-solid fa-calendar-days" style="color:var(--primary)"></i> Monthly Content Schedule</div>
            <span class="modal-close" onclick="document.getElementById('monthlyScheduleModal').classList.remove('show')">&times;</span>
        </div>

        @if($exactSchedule)
            <form id="monthlyScheduleForm" method="POST" action="{{ route('admin.clients.update-monthly-schedule', [$client, $exactSchedule]) }}" style="display:flex;flex-direction:column;gap:14px">
                @csrf
                @method('PATCH')
                <input type="hidden" name="_form" value="monthly_schedule_update">
                <div class="form-group">
                    <label style="font-weight:700">Editing Schedule for {{ $exactSchedule->getMonthYearLabel() }}</label>
                </div>
        @else
            <form id="monthlyScheduleForm" method="POST" action="{{ route('admin.clients.create-monthly-schedule', $client) }}" style="display:flex;flex-direction:column;gap:14px">
                @csrf
                <input type="hidden" name="_form" value="monthly_schedule_create">
                <div class="form-group">
                    <label style="font-weight:700">Set Schedule for Month & Year</label>
                    <div style="display:flex;gap:10px">
                        <select name="month" required style="flex:1">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ (int) old('month', $currentMonth) === $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                            @endfor
                        </select>
                        <select name="year" required style="flex:1">
                            @for ($y = now()->year; $y <= now()->year + 2; $y++)
                                <option value="{{ $y }}" {{ (int) old('year', $currentYear) === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="schedule-feedback" data-field="month"></div>
                    <div class="schedule-feedback" data-field="year"></div>
                </div>
        @endif

        <div style="background:var(--bg);border-radius:10px;padding:12px;margin-bottom:4px;border-left:3px solid var(--primary)">
            <div style="font-size:11px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Planned Content</div>
            <div style="font-size:12px;color:var(--text2)">Enter how many of each content type you plan to create this month</div>
        </div>

        @php
            $contentTypes = [
                'posts' => ['icon' => 'fa-pen-to-square', 'label' => 'Posts'],
                'reels' => ['icon' => 'fa-film', 'label' => 'Reels'],
                'stories' => ['icon' => 'fa-book-open', 'label' => 'Stories'],
                'carousel' => ['icon' => 'fa-images', 'label' => 'Carousels'],
                'videos' => ['icon' => 'fa-video', 'label' => 'Videos'],
                'other' => ['icon' => 'fa-ellipsis', 'label' => 'Other'],
            ];
        @endphp

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            @foreach($contentTypes as $key => $type)
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:6px;margin-bottom:6px">
                        <i class="fa-solid {{ $type['icon'] }}" style="font-size:14px;color:var(--primary)"></i>
                        <span style="font-weight:600;font-size:12px">{{ $type['label'] }}</span>
                    </label>
                    <input type="number" name="{{ $key }}" min="0" value="{{ old($key, $monthlySchedule ? $monthlySchedule->$key : 0) }}" placeholder="0" style="width:100%;text-align:center;font-size:14px;font-weight:600;padding:10px 12px">
                    <div class="schedule-feedback" data-field="{{ $key }}"></div>
                </div>
            @endforeach
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
            <button type="button" class="btn-sec" onclick="document.getElementById('monthlyScheduleModal').classList.remove('show')">Cancel</button>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-check"></i> {{ $exactSchedule ? 'Update Schedule' : 'Create Schedule' }}
            </button>
        </div>
            </form>
    </div>
</div>

{{-- ===== POST INSIGHTS (Organic vs Paid) ===== --}}
@include('admin.partials.post-insights')

{{-- ===== TABS: Tasks / Action Items / Calendar ===== --}}
<div class="cd-tabs-bar">
    <a href="{{ route('admin.clients.show', ['client' => $client->id, 'tab' => 'tasks']) }}"
       class="cd-tab {{ $tab === 'tasks' ? 'active' : '' }}">
        <i class="fa-solid fa-list-check"></i> Tasks
        <span class="cd-tab-count">{{ $taskStats['total'] }}</span>
    </a>
    <a href="{{ route('admin.clients.show', ['client' => $client->id, 'tab' => 'action-items']) }}"
       class="cd-tab {{ $tab === 'action-items' ? 'active' : '' }}">
        <i class="fa-solid fa-flag"></i> Action Items
        <span class="cd-tab-count">{{ $actionStats['total'] }}</span>
    </a>
    <a href="{{ route('admin.clients.calendar', $client) }}"
       class="cd-tab">
        <i class="fa-solid fa-calendar"></i> Calendar
    </a>
</div>

{{-- ===== FILTERS TOOLBAR ===== --}}
<div class="cd-filters">
    <form method="GET" action="{{ route('admin.clients.show', $client) }}" class="cd-filters-form">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="cl-search-wrap">
            <i class="fa-solid fa-magnifying-glass cl-search-icon"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search {{ $tab === 'tasks' ? 'tasks' : 'action items' }}..." class="cl-search-input">
        </div>
        @if($tab === 'tasks')
            <select name="status" class="cl-filter-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="todo" {{ $status === 'todo' ? 'selected' : '' }}>To Do</option>
                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="in_review" {{ $status === 'in_review' ? 'selected' : '' }}>In Review</option>
                <option value="revision" {{ $status === 'revision' ? 'selected' : '' }}>Revision</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
            <select name="priority" class="cl-filter-select" onchange="this.form.submit()">
                <option value="">All Priorities</option>
                <option value="urgent" {{ $priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ $priority === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>Low</option>
            </select>
        @else
            <select name="status" class="cl-filter-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Done</option>
            </select>
        @endif
        <select name="sort" class="cl-filter-select" onchange="this.form.submit()">
            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Sort: Newest</option>
            @if($tab === 'tasks')
                <option value="deadline" {{ $sort === 'deadline' ? 'selected' : '' }}>Sort: Deadline</option>
                <option value="priority" {{ $sort === 'priority' ? 'selected' : '' }}>Sort: Priority</option>
                <option value="status" {{ $sort === 'status' ? 'selected' : '' }}>Sort: Status</option>
            @endif
        </select>
        <div style="display:flex;align-items:center;gap:6px">
            <select id="perPageSelect" name="per_page" class="cl-filter-select" style="max-width:110px">
                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 / page</option>
                <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 / page</option>
                <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / page</option>
                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / page</option>
                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / page</option>
                <option value="custom" {{ !in_array($perPage, [10, 15, 25, 50, 100]) ? 'selected' : '' }}>Custom...</option>
            </select>
            <input type="number" id="customPerPage" name="per_page_custom" value="{{ !in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : '' }}" 
                   placeholder="Qty" class="cl-search-input" 
                   style="max-width:65px; display:{{ !in_array($perPage, [10, 15, 25, 50, 100]) ? 'block' : 'none' }}; min-height:38px !important; padding:4px 8px !important;">
        </div>
        @if($search || $status || $priority || $sort !== 'newest' || $perPage != 15)
            <a href="{{ route('admin.clients.show', ['client' => $client->id, 'tab' => $tab]) }}" class="cl-clear-btn" title="Clear filters">
                <i class="fa-solid fa-xmark"></i>
            </a>
        @endif
    </form>
    {{-- Removed redundant count from here as requested --}}
</div>

    <div id="tasksListContainer">
        {{-- ===== TASKS TAB ===== --}}
        @if($tab === 'tasks')
        <div class="cd-items-list">
            @forelse($tasks as $task)
                <a href="{{ route('admin.tasks.show', $task) }}" class="cd-task-row cd-task-link cd-row-st-{{ $task->status }}">
                    {{-- Status Indicator --}}
                    <div class="cd-task-status cd-status-{{ $task->status }}" style="margin-bottom:0">
                        @switch($task->status)
                            @case('todo') <i class="fa-solid fa-circle"></i> @break
                            @case('in_progress') <i class="fa-solid fa-spinner fa-spin-pulse"></i> @break
                            @case('in_review') <i class="fa-solid fa-eye"></i> @break
                            @case('revision') <i class="fa-solid fa-rotate-left"></i> @break
                            @case('completed') <i class="fa-solid fa-circle-check"></i> @break
                            @default <i class="fa-solid fa-circle"></i>
                        @endswitch
                    </div>
        
                    {{-- Task Info --}}
                    <div class="cd-task-info">
                        <div class="cd-task-title">{{ $task->title }}</div>
                        <div class="cd-task-meta">
                            @if($task->type)
                                <span class="cd-tag cd-tag-{{ $task->type }}">{{ ucfirst($task->type) }}</span>
                            @endif
                            @if($task->platform && is_array($task->platform))
                                @foreach($task->platform as $p)
                                    <span class="cd-platform-tag">{{ $p }}</span>
                                @endforeach
                            @endif
                        </div>
                        {{-- Mobile Details Layout --}}
                        <div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap;font-size:11px">
                            <div class="cd-task-priority cd-priority-{{ $task->priority }}" style="flex-shrink:0">
                                {{ ucfirst($task->priority) }}
                            </div>
                            @if($task->deadline)
                                <div class="cd-task-deadline {{ $task->isOverdue() ? 'cd-overdue' : '' }}" style="flex-shrink:0">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $task->deadline->format('M d') }}
                                    @if($task->isOverdue())
                                        <span class="cd-overdue-badge">Overdue</span>
                                    @endif
                                </div>
                            @endif
                            @if($task->assignee)
                                <div class="cd-task-assignee" style="flex-shrink:0">
                                    <div class="cd-assignee-avatar" style="background:{{ $client->color }}20;color:{{ $client->color }}">
                                        {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $task->assignee->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
        
                    {{-- Comments count (Desktop) --}}
                    <div class="cd-task-comments" style="display:none">
                        <i class="fa-regular fa-comment"></i> {{ $task->comments->count() }}
                    </div>
        
                    {{-- Arrow --}}
                    <div class="cd-row-arrow"><i class="fa-solid fa-chevron-right"></i></div>
                </a>
            @empty
                <div class="cd-empty">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>No tasks found{{ $search || $status || $priority ? ' matching your filters' : '' }}</span>
                </div>
            @endforelse
        </div>
        @if($tasks->hasPages())
        <div style="margin-top:24px">
            {{ $tasks->links('vendor.pagination.custom') }}
        </div>
        @endif
        @endif
        
        {{-- ===== ACTION ITEMS TAB ===== --}}
        @if($tab === 'action-items')
        <div class="cd-items-list">
            @forelse($actionItems as $item)
                <div class="cd-task-row cd-row-st-{{ $item->status === 'done' ? 'completed' : $item->status }}">
                    {{-- Status --}}
                    <div class="cd-task-status cd-ai-status-{{ $item->status }}" style="margin-bottom:0">
                        @switch($item->status)
                            @case('pending') <i class="fa-solid fa-clock"></i> @break
                            @case('in_progress') <i class="fa-solid fa-spinner fa-spin-pulse"></i> @break
                            @case('done') <i class="fa-solid fa-circle-check"></i> @break
                        @endswitch
                    </div>
        
                    {{-- Info --}}
                    <div class="cd-task-info">
                        <div class="cd-task-title">{{ $item->title }}</div>
                        @if($item->description)
                            <div class="cd-task-desc">{{ Str::limit($item->description, 80) }}</div>
                        @endif
                        @if($item->notes->count())
                            <div class="cd-ai-notes-count">
                                <i class="fa-solid fa-sticky-note"></i> {{ $item->notes->count() }} note{{ $item->notes->count() > 1 ? 's' : '' }}
                            </div>
                        @endif
                        {{-- Mobile Details Layout --}}
                        <div style="display:flex;gap:10px;margin-top:8px;flex-wrap:wrap;font-size:11px">
                            <div class="cd-task-priority cd-priority-{{ $item->priority }}" style="flex-shrink:0">
                                {{ ucfirst($item->priority) }}
                            </div>
                            @if($item->due_at)
                            <div class="cd-task-deadline {{ $item->status !== 'done' && $item->due_at->isPast() ? 'cd-overdue' : '' }}" style="flex-shrink:0">
                                <i class="fa-regular fa-calendar"></i>
                                {{ $item->due_at->format('M d') }}
                                @if($item->status !== 'done' && $item->due_at->isPast())
                                    <span class="cd-overdue-badge">Overdue</span>
                                @endif
                            </div>
                            @endif
                            @if($item->assignee)
                                <div class="cd-task-assignee" style="flex-shrink:0">
                                    <div class="cd-assignee-avatar" style="background:{{ $client->color }}20;color:{{ $client->color }}">
                                        {{ strtoupper(substr($item->assignee->name, 0, 1)) }}
                                    </div>
                                    <span>{{ $item->assignee->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
        
                    {{-- Status Badge (Desktop) --}}
                    <div class="cd-ai-status-badge cd-ai-badge-{{ $item->status }}" style="display:none">
                        {{ $item->status === 'in_progress' ? 'In Progress' : ucfirst($item->status) }}
                    </div>
                </div>
            @empty
                <div class="cd-empty">
                    <i class="fa-solid fa-flag"></i>
                    <span>No action items found{{ $search || $status ? ' matching your filters' : '' }}</span>
                </div>
            @endforelse
        </div>
        
        @endif
    </div>

@push('styles')<link rel="stylesheet" href="{{ asset('css/pages/admin-client-detail.css') }}">@endpush

@php
    // Pre-compute outside @json to avoid Blade's naive bracket counter choking on nested [].
    $cdContactsById = $client->contacts->mapWithKeys(function ($c) {
        return [$c->id => [
            'id'         => $c->id,
            'name'       => $c->name,
            'email'      => $c->email,
            'phone'      => $c->phone,
            'role'       => $c->role,
            'is_primary' => (bool) $c->is_primary,
        ]];
    });
    $cdContactRoutes = [
        'update'      => route('admin.clients.contacts.update',      [$client->id, 0]),
        'destroy'     => route('admin.clients.contacts.destroy',     [$client->id, 0]),
        'set_primary' => route('admin.clients.contacts.set-primary', [$client->id, 0]),
    ];
@endphp

<script>
// ─── Contacts AJAX wiring ──────────────────────────────────────────
window.CdContactsData = {
    csrf: '{{ csrf_token() }}',
    storeUrl: '{{ route('admin.clients.contacts.store', $client) }}',
    contactsById: {!! $cdContactsById->toJson() !!},
    routes: {
        update:     @json($cdContactRoutes['update']),
        destroy:    @json($cdContactRoutes['destroy']),
        setPrimary: @json($cdContactRoutes['set_primary']),
    },
};

function cdReplacePlaceholderId(url, id) {
    // Routes were generated with id=0 above; swap in the real id.
    return url.replace(/\/0(?=$|\/)/, '/' + id);
}

// ─── Online Presence: builder + per-tile edit/delete ──────────────
window.CdSocialPlatforms = ['instagram','facebook','twitter','linkedin','youtube','tiktok','whatsapp'];
window.CdSocialRoutes = {
    batch:  '{{ route('admin.clients.social-links.batch', $client) }}',
    update: '{{ route('admin.clients.update-social-link', [$client, 0]) }}',
    delete: '{{ route('admin.clients.delete-social-link', [$client, 0]) }}',
};

function cdSocialRouteWithId(url, id) {
    return url.replace(/\/0(?=$|\/)/, '/' + id);
}

function cdOpenSocialBuilder() {
    const modal = document.getElementById('cdSocialBuilderModal');
    const rows = document.getElementById('cdSocialBuilderRows');
    const err  = document.getElementById('cdSocialBuilderError');
    rows.innerHTML = '';
    err.style.display = 'none';
    cdAddSocialRow();
    modal.classList.add('show');
}

function cdCloseSocialBuilder() {
    document.getElementById('cdSocialBuilderModal').classList.remove('show');
}

function cdAddSocialRow() {
    const wrap = document.getElementById('cdSocialBuilderRows');
    const row = document.createElement('div');
    row.className = 'cd-social-row';
    const opts = window.CdSocialPlatforms.map(p => `<option value="${p}">${p.charAt(0).toUpperCase() + p.slice(1)}</option>`).join('');
    row.innerHTML = `
        <div class="cd-social-row-grid">
            <select class="cd-row-platform" required>${opts}</select>
            <input type="url" class="cd-row-url" placeholder="https://..." required>
            <input type="text" class="cd-row-label" placeholder="Label (optional)" maxlength="100">
            <button type="button" class="cd-row-remove" title="Remove row" onclick="this.closest('.cd-social-row').remove(); cdRefreshRowRemoveButtons();">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    `;
    wrap.appendChild(row);
    cdRefreshRowRemoveButtons();
    // Auto-focus the new URL field
    setTimeout(() => row.querySelector('.cd-row-url').focus(), 50);
}

function cdRefreshRowRemoveButtons() {
    const rows = document.querySelectorAll('#cdSocialBuilderRows .cd-social-row');
    rows.forEach((r, i) => {
        const btn = r.querySelector('.cd-row-remove');
        if (!btn) return;
        btn.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
    });
}

document.getElementById('cdSocialBuilderForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const err = document.getElementById('cdSocialBuilderError');
    const submit = document.getElementById('cdSocialBuilderSubmit');
    err.style.display = 'none';

    const rows = Array.from(document.querySelectorAll('#cdSocialBuilderRows .cd-social-row'));
    const payload = [];
    for (const r of rows) {
        const platform = r.querySelector('.cd-row-platform').value;
        const url      = r.querySelector('.cd-row-url').value.trim();
        const label    = r.querySelector('.cd-row-label').value.trim();
        if (!url) continue; // skip empty rows
        payload.push({ platform, url, label: label || null });
    }
    if (payload.length === 0) {
        err.textContent = 'Add at least one row with a URL.';
        err.style.display = 'block';
        return;
    }

    submit.disabled = true;
    try {
        const res = await fetch(window.CdSocialRoutes.batch, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CdContactsData.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ links: payload }),
        });
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            err.textContent = (data.errors && Object.values(data.errors).flat().join(' ')) || data.message || 'Could not save.';
            err.style.display = 'block';
            return;
        }
        window.location.reload();
    } catch (e) {
        err.textContent = 'Network error.';
        err.style.display = 'block';
    } finally {
        submit.disabled = false;
    }
});

// ─── Single-link edit ──────────────────────────────────────────────
function cdEditSocialLink(linkId) {
    const tile = document.querySelector(`.cd-presence-tile[data-link-id="${linkId}"]`);
    if (!tile) return;
    document.getElementById('cdSocialEditId').value      = linkId;
    document.getElementById('cdSocialEditPlatform').value = tile.dataset.platform || 'instagram';
    document.getElementById('cdSocialEditUrl').value      = tile.dataset.url || '';
    document.getElementById('cdSocialEditLabel').value    = tile.dataset.label || '';
    document.getElementById('cdSocialEditError').style.display = 'none';
    document.getElementById('cdSocialEditModal').classList.add('show');
}

function cdCloseSocialEdit() {
    document.getElementById('cdSocialEditModal').classList.remove('show');
}

document.getElementById('cdSocialEditForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const err  = document.getElementById('cdSocialEditError');
    const submit = document.getElementById('cdSocialEditSubmit');
    err.style.display = 'none';

    const id       = document.getElementById('cdSocialEditId').value;
    const platform = document.getElementById('cdSocialEditPlatform').value;
    const url      = document.getElementById('cdSocialEditUrl').value.trim();
    const label    = document.getElementById('cdSocialEditLabel').value.trim();

    if (!id || !url) { err.textContent = 'URL is required.'; err.style.display = 'block'; return; }

    submit.disabled = true;
    try {
        const res = await fetch(cdSocialRouteWithId(window.CdSocialRoutes.update, id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CdContactsData.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ platform, url, label: label || null }),
        });
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            err.textContent = (data.errors && Object.values(data.errors).flat().join(' ')) || data.message || 'Could not save.';
            err.style.display = 'block';
            return;
        }
        window.location.reload();
    } catch (e) {
        err.textContent = 'Network error.';
        err.style.display = 'block';
    } finally {
        submit.disabled = false;
    }
});

async function cdDeleteSocialLink(linkId) {
    if (! window.confirm('Delete this social link?')) return;
    try {
        const res = await fetch(cdSocialRouteWithId(window.CdSocialRoutes.delete, linkId), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CdContactsData.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!res.ok) { alert('Could not delete.'); return; }
        window.location.reload();
    } catch (e) { alert('Network error.'); }
}

// Copy-to-clipboard for Online Presence tiles
function cdCopyToClipboard(btn) {
    const text = btn.getAttribute('data-copy');
    if (!text) return;
    const showOk = () => {
        const icon = btn.querySelector('i');
        if (!icon) return;
        const original = icon.className;
        icon.className = 'fa-solid fa-check';
        btn.classList.add('copied');
        setTimeout(() => { icon.className = original; btn.classList.remove('copied'); }, 1200);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(showOk).catch(() => fallback());
    } else { fallback(); }
    function fallback() {
        const ta = document.createElement('textarea');
        ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
        document.body.appendChild(ta); ta.select();
        try { document.execCommand('copy'); showOk(); } catch (e) {}
        document.body.removeChild(ta);
    }
}

function cdOpenContactModal(contactId) {
    const modal = document.getElementById('cdContactModal');
    const title = document.getElementById('cdContactModalTitle');
    const err   = document.getElementById('cdContactError');
    err.style.display = 'none';
    err.textContent = '';

    document.getElementById('cdContactId').value      = contactId || '';
    document.getElementById('cdContactName').value    = '';
    document.getElementById('cdContactRole').value    = '';
    document.getElementById('cdContactEmail').value   = '';
    document.getElementById('cdContactPhone').value   = '';
    document.getElementById('cdContactPrimary').checked = false;

    if (contactId && window.CdContactsData.contactsById[contactId]) {
        const c = window.CdContactsData.contactsById[contactId];
        title.textContent = 'Edit Contact';
        document.getElementById('cdContactName').value      = c.name || '';
        document.getElementById('cdContactRole').value      = c.role || '';
        document.getElementById('cdContactEmail').value     = c.email || '';
        document.getElementById('cdContactPhone').value     = c.phone || '';
        document.getElementById('cdContactPrimary').checked = !!c.is_primary;
    } else {
        title.textContent = 'Add Contact';
    }

    modal.classList.add('show');
    setTimeout(() => document.getElementById('cdContactName').focus(), 100);
}

function cdCloseContactModal() {
    document.getElementById('cdContactModal').classList.remove('show');
}

document.getElementById('cdContactForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const submitBtn = document.getElementById('cdContactSubmit');
    const err = document.getElementById('cdContactError');
    err.style.display = 'none';

    const contactId = document.getElementById('cdContactId').value;
    const payload = {
        name:       document.getElementById('cdContactName').value.trim(),
        role:       document.getElementById('cdContactRole').value.trim(),
        email:      document.getElementById('cdContactEmail').value.trim(),
        phone:      document.getElementById('cdContactPhone').value.trim(),
        is_primary: document.getElementById('cdContactPrimary').checked ? 1 : 0,
    };

    if (!payload.name && !payload.email && !payload.phone) {
        err.textContent = 'Please provide at least a name, email, or phone.';
        err.style.display = 'block';
        return;
    }

    const url = contactId
        ? cdReplacePlaceholderId(window.CdContactsData.routes.update, contactId)
        : window.CdContactsData.storeUrl;
    const method = contactId ? 'PATCH' : 'POST';

    submitBtn.disabled = true;
    try {
        const res = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CdContactsData.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });
        if (!res.ok) {
            const data = await res.json().catch(() => ({}));
            err.textContent = (data.errors && Object.values(data.errors).flat().join(' ')) || data.message || 'Could not save contact.';
            err.style.display = 'block';
            return;
        }
        // Simplest: reload to re-render the contacts list with the new state
        window.location.reload();
    } catch (e) {
        err.textContent = 'Network error.';
        err.style.display = 'block';
    } finally {
        submitBtn.disabled = false;
    }
});

async function cdDeleteContact(contactId) {
    if (! window.confirm('Delete this contact?')) return;
    try {
        const res = await fetch(cdReplacePlaceholderId(window.CdContactsData.routes.destroy, contactId), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CdContactsData.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!res.ok) { alert('Could not delete.'); return; }
        window.location.reload();
    } catch (e) { alert('Network error.'); }
}

async function cdSetPrimaryContact(contactId) {
    try {
        const res = await fetch(cdReplacePlaceholderId(window.CdContactsData.routes.setPrimary, contactId), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CdContactsData.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!res.ok) { alert('Could not set primary.'); return; }
        window.location.reload();
    } catch (e) { alert('Network error.'); }
}

const HAS_EDIT_CLIENT_ERRORS = @json($hasEditClientErrors);
const HAS_SOCIAL_LINK_ERRORS = @json($hasSocialLinkErrors);
const HAS_CREATE_LOGIN_ERRORS = @json($hasCreateLoginErrors);
const HAS_MONTHLY_SCHEDULE_ERRORS = @json($hasMonthlyScheduleErrors);
const OLD_SOCIAL_PLATFORM = @json(old('platform'));
const OLD_SOCIAL_URL = @json(old('url'));
const OLD_SOCIAL_LABEL = @json(old('label'));
const EDIT_SERVER_ERRORS = {
    name: @json($errors->first('name')),
    category: @json($errors->first('category')),
    emoji: @json($errors->first('emoji')),
    color: @json($errors->first('color')),
    logo: @json($errors->first('logo')),
    website: @json($errors->first('website')),
    notes: @json($errors->first('notes')),
    contact_person: @json($errors->first('contact_person')),
    contact_email: @json($errors->first('contact_email')),
    contact_phone: @json($errors->first('contact_phone')),
};
const SOCIAL_SERVER_ERRORS = {
    platform: @json($errors->first('platform')),
    url: @json($errors->first('url')),
    label: @json($errors->first('label')),
};
const CREATE_LOGIN_SERVER_ERRORS = {
    name: @json($errors->first('name')),
    email: @json($errors->first('email')),
    password: @json($errors->first('password')),
};
const MONTHLY_SCHEDULE_SERVER_ERRORS = {
    month: @json($errors->first('month')),
    year: @json($errors->first('year')),
    posts: @json($errors->first('posts')),
    reels: @json($errors->first('reels')),
    stories: @json($errors->first('stories')),
    carousel: @json($errors->first('carousel')),
    videos: @json($errors->first('videos')),
    guides: @json($errors->first('guides')),
    collections: @json($errors->first('collections')),
    other: @json($errors->first('other')),
};

function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function isHttpUrl(value) {
    try {
        const parsed = new URL(value);
        return parsed.protocol === 'http:' || parsed.protocol === 'https:';
    } catch (e) {
        return false;
    }
}

const editClientForm = document.getElementById('editClientForm');
const editClientFields = editClientForm ? {
    logo: editClientForm.querySelector('[name="logo"]'),
    name: editClientForm.querySelector('[name="name"]'),
    category: editClientForm.querySelector('[name="category"]'),
    emoji: editClientForm.querySelector('[name="emoji"]'),
    color: editClientForm.querySelector('[name="color"]'),
    contact_person: editClientForm.querySelector('[name="contact_person"]'),
    contact_phone: editClientForm.querySelector('[name="contact_phone"]'),
    contact_email: editClientForm.querySelector('[name="contact_email"]'),
    website: editClientForm.querySelector('[name="website"]'),
    notes: editClientForm.querySelector('[name="notes"]'),
} : {};

function getEditFeedbackEl(fieldName) {
    return editClientForm?.querySelector(`.cd-field-feedback[data-field="${fieldName}"]`) || null;
}

function setEditFieldVisual(fieldName, state) {
    const field = editClientFields[fieldName];
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

function setEditFeedback(fieldName, message, state) {
    const el = getEditFeedbackEl(fieldName);
    if (!el) return;
    el.style.display = 'block';
    el.textContent = state === 'success' ? ('✓ ' + message) : message;
    el.classList.remove('is-success', 'is-error');
    el.classList.add(state === 'success' ? 'is-success' : 'is-error');
}

function clearEditFeedback(fieldName) {
    const el = getEditFeedbackEl(fieldName);
    if (!el) return;
    el.style.display = 'none';
    el.textContent = '';
    el.classList.remove('is-success', 'is-error');
}

function validateEditClientField(fieldName, showSuccess = true) {
    const field = editClientFields[fieldName];
    if (!field) return true;
    const value = typeof field.value === 'string' ? field.value.trim() : '';

    const markError = (msg) => {
        setEditFieldVisual(fieldName, 'error');
        setEditFeedback(fieldName, msg, 'error');
        return false;
    };
    const markSuccess = (msg) => {
        setEditFieldVisual(fieldName, 'success');
        if (showSuccess) setEditFeedback(fieldName, msg, 'success');
        else clearEditFeedback(fieldName);
        return true;
    };
    const clearNeutral = () => {
        setEditFieldVisual(fieldName, null);
        clearEditFeedback(fieldName);
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
        if (!/^\+?[0-9\s\-\(\)]{7,20}$/.test(value)) return markError('Phone must be 7-20 chars using digits, spaces, +, -, or parentheses.');
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
        const ext = (file.name.split('.').pop() || '').toLowerCase();
        const allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!allowed.includes(ext)) return markError('Logo must be JPG, JPEG, PNG, GIF, WEBP, or SVG.');
        if (file.size > (2048 * 1024)) return markError('Logo must be 2MB or smaller.');
        return markSuccess('Logo file is valid.');
    }

    return true;
}

function validateEditClientForm(showSuccess = true) {
    const fieldsToCheck = ['name', 'category', 'emoji', 'color', 'contact_person', 'contact_phone', 'contact_email', 'website', 'notes', 'logo'];
    let valid = true;
    fieldsToCheck.forEach((fieldName) => {
        if (!validateEditClientField(fieldName, showSuccess)) valid = false;
    });
    return valid;
}

const allowedSocialPlatforms = ['instagram', 'facebook', 'twitter', 'linkedin', 'youtube', 'tiktok', 'whatsapp'];
const socialForms = document.querySelectorAll('.social-link-form');

function getSocialFeedbackEl(form, fieldName) {
    return form.querySelector(`.social-field-feedback[data-field="${fieldName}"]`);
}

function setSocialFieldVisual(form, fieldName, state) {
    const field = form.querySelector(`[name="${fieldName}"]`);
    if (!field || fieldName === 'platform') return;

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

function setSocialFeedback(form, fieldName, message, state) {
    const el = getSocialFeedbackEl(form, fieldName);
    if (!el) return;
    el.style.display = 'block';
    el.textContent = state === 'success' ? ('✓ ' + message) : message;
    el.classList.remove('is-success', 'is-error');
    el.classList.add(state === 'success' ? 'is-success' : 'is-error');
}

function clearSocialFeedback(form, fieldName) {
    const el = getSocialFeedbackEl(form, fieldName);
    if (!el) return;
    el.style.display = 'none';
    el.textContent = '';
    el.classList.remove('is-success', 'is-error');
}

function validateSocialField(form, fieldName, showSuccess = true) {
    const field = form.querySelector(`[name="${fieldName}"]`);
    const value = field ? (typeof field.value === 'string' ? field.value.trim() : '') : '';

    const markError = (msg) => {
        setSocialFieldVisual(form, fieldName, 'error');
        setSocialFeedback(form, fieldName, msg, 'error');
        return false;
    };
    const markSuccess = (msg) => {
        setSocialFieldVisual(form, fieldName, 'success');
        if (showSuccess) setSocialFeedback(form, fieldName, msg, 'success');
        else clearSocialFeedback(form, fieldName);
        return true;
    };
    const clearNeutral = () => {
        setSocialFieldVisual(form, fieldName, null);
        clearSocialFeedback(form, fieldName);
        return true;
    };

    if (fieldName === 'platform') {
        if (!value) return markError('Please choose a social platform.');
        if (!allowedSocialPlatforms.includes(value)) return markError('Selected social platform is not valid.');
        return markSuccess('Platform looks good.');
    }
    if (fieldName === 'url') {
        if (!value) return markError('Social link URL is required.');
        if (!isHttpUrl(value)) return markError('URL must start with http:// or https://');
        if (value.length > 500) return markError('URL must be 500 characters or fewer.');
        return markSuccess('URL looks valid.');
    }
    if (fieldName === 'label') {
        if (!value) return clearNeutral();
        if (value.length > 100) return markError('Username/label must be 100 characters or fewer.');
        return markSuccess('Username/label looks good.');
    }

    return true;
}

function validateSocialForm(form, showSuccess = true) {
    const fields = ['platform', 'url', 'label'];
    let valid = true;
    fields.forEach((fieldName) => {
        if (!validateSocialField(form, fieldName, showSuccess)) valid = false;
    });
    return valid;
}

if (editClientForm) {
    editClientForm.addEventListener('submit', function(e) {
        if (!validateEditClientForm(true)) {
            e.preventDefault();
        }
    });

    editClientFields.name?.addEventListener('input', () => validateEditClientField('name', false));
    editClientFields.name?.addEventListener('blur', () => validateEditClientField('name', true));
    editClientFields.category?.addEventListener('blur', () => validateEditClientField('category', true));
    editClientFields.emoji?.addEventListener('blur', () => validateEditClientField('emoji', true));
    editClientFields.color?.addEventListener('input', () => validateEditClientField('color', true));
    editClientFields.contact_person?.addEventListener('blur', () => validateEditClientField('contact_person', true));
    editClientFields.contact_phone?.addEventListener('input', () => validateEditClientField('contact_phone', false));
    editClientFields.contact_phone?.addEventListener('blur', () => validateEditClientField('contact_phone', true));
    editClientFields.contact_email?.addEventListener('input', () => validateEditClientField('contact_email', false));
    editClientFields.contact_email?.addEventListener('blur', () => validateEditClientField('contact_email', true));
    editClientFields.website?.addEventListener('input', () => validateEditClientField('website', false));
    editClientFields.website?.addEventListener('blur', () => validateEditClientField('website', true));
    editClientFields.notes?.addEventListener('input', () => validateEditClientField('notes', false));
    editClientFields.notes?.addEventListener('blur', () => validateEditClientField('notes', true));
    editClientFields.logo?.addEventListener('change', () => validateEditClientField('logo', true));
}

socialForms.forEach((form) => {
    form.addEventListener('submit', function(e) {
        if (!validateSocialForm(form, true)) {
            e.preventDefault();
        }
    });

    const urlInput = form.querySelector('[name="url"]');
    const labelInput = form.querySelector('[name="label"]');
    urlInput?.addEventListener('input', () => validateSocialField(form, 'url', false));
    urlInput?.addEventListener('blur', () => validateSocialField(form, 'url', true));
    labelInput?.addEventListener('input', () => validateSocialField(form, 'label', false));
    labelInput?.addEventListener('blur', () => validateSocialField(form, 'label', true));
});

const createLoginForm = document.getElementById('createClientLoginForm');
const createLoginFields = createLoginForm ? {
    name: createLoginForm.querySelector('[name="name"]'),
    email: createLoginForm.querySelector('[name="email"]'),
    password: createLoginForm.querySelector('[name="password"]'),
} : {};

function getCreateLoginFeedbackEl(fieldName) {
    return createLoginForm?.querySelector(`.create-login-feedback[data-field="${fieldName}"]`) || null;
}

function setCreateLoginFieldVisual(fieldName, state) {
    const field = createLoginFields[fieldName];
    if (!field) return;

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

function setCreateLoginFeedback(fieldName, message, state) {
    const el = getCreateLoginFeedbackEl(fieldName);
    if (!el) return;
    el.style.display = 'block';
    el.textContent = state === 'success' ? ('✓ ' + message) : message;
    el.classList.remove('is-success', 'is-error');
    el.classList.add(state === 'success' ? 'is-success' : 'is-error');
}

function clearCreateLoginFeedback(fieldName) {
    const el = getCreateLoginFeedbackEl(fieldName);
    if (!el) return;
    el.style.display = 'none';
    el.textContent = '';
    el.classList.remove('is-success', 'is-error');
}

function validateCreateLoginField(fieldName, showSuccess = true) {
    const field = createLoginFields[fieldName];
    if (!field) return true;
    const value = typeof field.value === 'string' ? field.value.trim() : '';

    const markError = (msg) => {
        setCreateLoginFieldVisual(fieldName, 'error');
        setCreateLoginFeedback(fieldName, msg, 'error');
        return false;
    };
    const markSuccess = (msg) => {
        setCreateLoginFieldVisual(fieldName, 'success');
        if (showSuccess) setCreateLoginFeedback(fieldName, msg, 'success');
        else clearCreateLoginFeedback(fieldName);
        return true;
    };

    if (fieldName === 'name') {
        if (!value) return markError('Full name is required.');
        if (value.length > 255) return markError('Full name must be 255 characters or fewer.');
        return markSuccess('Full name looks good.');
    }

    if (fieldName === 'email') {
        if (!value) return markError('Email is required.');
        const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        if (!emailOk) return markError('Please enter a valid email address.');
        if (value.length > 255) return markError('Email must be 255 characters or fewer.');
        return markSuccess('Email looks valid.');
    }

    if (fieldName === 'password') {
        if (!value) return markError('Password is required.');
        if (value.length < 6) return markError('Password must be at least 6 characters.');
        if (value.length > 100) return markError('Password must be 100 characters or fewer.');
        return markSuccess('Password length is valid.');
    }

    return true;
}

function validateCreateLoginForm(showSuccess = true) {
    const fields = ['name', 'email', 'password'];
    let valid = true;
    fields.forEach((fieldName) => {
        if (!validateCreateLoginField(fieldName, showSuccess)) valid = false;
    });
    return valid;
}

const monthlyScheduleForm = document.getElementById('monthlyScheduleForm');
const monthlyScheduleFields = monthlyScheduleForm ? {
    month: monthlyScheduleForm.querySelector('[name="month"]'),
    year: monthlyScheduleForm.querySelector('[name="year"]'),
    posts: monthlyScheduleForm.querySelector('[name="posts"]'),
    reels: monthlyScheduleForm.querySelector('[name="reels"]'),
    stories: monthlyScheduleForm.querySelector('[name="stories"]'),
    carousel: monthlyScheduleForm.querySelector('[name="carousel"]'),
    videos: monthlyScheduleForm.querySelector('[name="videos"]'),
    guides: monthlyScheduleForm.querySelector('[name="guides"]'),
    collections: monthlyScheduleForm.querySelector('[name="collections"]'),
    other: monthlyScheduleForm.querySelector('[name="other"]'),
} : {};

function getScheduleFeedbackEl(fieldName) {
    return monthlyScheduleForm?.querySelector(`.schedule-feedback[data-field="${fieldName}"]`) || null;
}

function setScheduleFieldVisual(fieldName, state) {
    const field = monthlyScheduleFields[fieldName];
    if (!field) return;

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

function setScheduleFeedback(fieldName, message, state) {
    const el = getScheduleFeedbackEl(fieldName);
    if (!el) return;
    el.style.display = 'block';
    el.textContent = state === 'success' ? ('✓ ' + message) : message;
    el.classList.remove('is-success', 'is-error');
    el.classList.add(state === 'success' ? 'is-success' : 'is-error');
}

function clearScheduleFeedback(fieldName) {
    const el = getScheduleFeedbackEl(fieldName);
    if (!el) return;
    el.style.display = 'none';
    el.textContent = '';
    el.classList.remove('is-success', 'is-error');
}

function validateScheduleField(fieldName, showSuccess = true) {
    const field = monthlyScheduleFields[fieldName];
    if (!field) return true;
    const value = typeof field.value === 'string' ? field.value.trim() : '';

    const markError = (msg) => {
        setScheduleFieldVisual(fieldName, 'error');
        setScheduleFeedback(fieldName, msg, 'error');
        return false;
    };
    const markSuccess = (msg) => {
        setScheduleFieldVisual(fieldName, 'success');
        if (showSuccess) setScheduleFeedback(fieldName, msg, 'success');
        else clearScheduleFeedback(fieldName);
        return true;
    };

    if (fieldName === 'month') {
        if (!value) return markError('Month is required.');
        const month = Number(value);
        if (!Number.isInteger(month) || month < 1 || month > 12) return markError('Month must be between 1 and 12.');
        return markSuccess('Month looks valid.');
    }

    if (fieldName === 'year') {
        if (!value) return markError('Year is required.');
        const year = Number(value);
        const maxYear = new Date().getFullYear() + 10;
        if (!Number.isInteger(year) || year < 2020 || year > maxYear) {
            return markError(`Year must be between 2020 and ${maxYear}.`);
        }
        return markSuccess('Year looks valid.');
    }

    const labels = {
        posts: 'Posts',
        reels: 'Reels',
        stories: 'Stories',
        carousel: 'Carousels',
        videos: 'Videos',
        guides: 'Guides',
        collections: 'Collections',
        other: 'Other',
    };

    if (labels[fieldName]) {
        if (!value) return markError(`${labels[fieldName]} is required.`);
        if (!/^\d+$/.test(value)) return markError(`${labels[fieldName]} must be a whole number 0 or more.`);
        return markSuccess(`${labels[fieldName]} value is valid.`);
    }

    return true;
}

function validateMonthlyScheduleForm(showSuccess = true) {
    const fields = ['month', 'year', 'posts', 'reels', 'stories', 'carousel', 'videos', 'guides', 'collections', 'other'];
    let valid = true;
    fields.forEach((fieldName) => {
        if (!validateScheduleField(fieldName, showSuccess)) valid = false;
    });
    return valid;
}

if (createLoginForm) {
    createLoginForm.addEventListener('submit', function(e) {
        if (!validateCreateLoginForm(true)) {
            e.preventDefault();
        }
    });

    createLoginFields.name?.addEventListener('input', () => validateCreateLoginField('name', false));
    createLoginFields.name?.addEventListener('blur', () => validateCreateLoginField('name', true));
    createLoginFields.email?.addEventListener('input', () => validateCreateLoginField('email', false));
    createLoginFields.email?.addEventListener('blur', () => validateCreateLoginField('email', true));
    createLoginFields.password?.addEventListener('input', () => validateCreateLoginField('password', false));
    createLoginFields.password?.addEventListener('blur', () => validateCreateLoginField('password', true));
}

if (monthlyScheduleForm) {
    monthlyScheduleForm.addEventListener('submit', function(e) {
        if (!validateMonthlyScheduleForm(true)) {
            e.preventDefault();
        }
    });

    Object.keys(monthlyScheduleFields).forEach((fieldName) => {
        const field = monthlyScheduleFields[fieldName];
        if (!field) return;
        field.addEventListener('input', () => validateScheduleField(fieldName, false));
        field.addEventListener('blur', () => validateScheduleField(fieldName, true));
    });
}

if (HAS_EDIT_CLIENT_ERRORS) {
    document.getElementById('editClientModal')?.classList.add('show');
    Object.entries(EDIT_SERVER_ERRORS).forEach(([fieldName, message]) => {
        if (!message) return;
        setEditFieldVisual(fieldName, 'error');
        setEditFeedback(fieldName, message, 'error');
    });
}

if (HAS_SOCIAL_LINK_ERRORS) {
    document.getElementById('manageSocialLinksModal')?.classList.add('show');
    let targetForm = null;
    if (OLD_SOCIAL_PLATFORM) {
        targetForm = document.querySelector(`.social-link-form[data-platform="${OLD_SOCIAL_PLATFORM}"]`);
    }
    if (!targetForm) targetForm = document.querySelector('.social-link-form');

    if (targetForm) {
        if (OLD_SOCIAL_URL) {
            const urlInput = targetForm.querySelector('[name="url"]');
            if (urlInput) urlInput.value = OLD_SOCIAL_URL;
        }
        if (OLD_SOCIAL_LABEL) {
            const labelInput = targetForm.querySelector('[name="label"]');
            if (labelInput) labelInput.value = OLD_SOCIAL_LABEL;
        }

        Object.entries(SOCIAL_SERVER_ERRORS).forEach(([fieldName, message]) => {
            if (!message) return;
            setSocialFieldVisual(targetForm, fieldName, 'error');
            setSocialFeedback(targetForm, fieldName, message, 'error');
        });
    }
}

if (HAS_CREATE_LOGIN_ERRORS) {
    document.getElementById('createLoginModal')?.classList.add('show');
    Object.entries(CREATE_LOGIN_SERVER_ERRORS).forEach(([fieldName, message]) => {
        if (!message) return;
        setCreateLoginFieldVisual(fieldName, 'error');
        setCreateLoginFeedback(fieldName, message, 'error');
    });
}

if (HAS_MONTHLY_SCHEDULE_ERRORS) {
    document.getElementById('monthlyScheduleModal')?.classList.add('show');
    Object.entries(MONTHLY_SCHEDULE_SERVER_ERRORS).forEach(([fieldName, message]) => {
        if (!message) return;
        setScheduleFieldVisual(fieldName, 'error');
        setScheduleFeedback(fieldName, message, 'error');
    });
}

// AJAX Live Search & Filter
const filterForm = document.querySelector('.cd-filters-form');
const searchInput = filterForm?.querySelector('input[name="search"]');
const perPageSelect = document.getElementById('perPageSelect');
const customPerPage = document.getElementById('customPerPage');
let filterTimer;

const triggerFilter = () => {
    if (!filterForm) return;
    
    const formData = new FormData(filterForm);
    if (perPageSelect && perPageSelect.value === 'custom') {
        const val = customPerPage.value.trim();
        if (val && parseInt(val) > 0) formData.set('per_page', val);
        else formData.set('per_page', '15');
    }
    
    const params = new URLSearchParams(formData);
    const baseUrl = filterForm.action;
    const url = `${baseUrl}${baseUrl.includes('?') ? '&' : '?'}${params.toString()}`;
    const ajaxUrl = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now();

    window.history.pushState({ path: url }, '', url);

    const container = document.getElementById('tasksListContainer');
    if (container) {
        container.style.pointerEvents = 'none';
        container.style.opacity = '0.6';
    }

    fetch(ajaxUrl, { headers: { 'Accept': 'text/html' } })
    .then(r => {
        if (!r.ok) throw new Error('Response not OK');
        return r.text();
    })
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.getElementById('tasksListContainer');
        const currentContainer = document.getElementById('tasksListContainer');
        
        if (newContent && currentContainer) {
            currentContainer.innerHTML = newContent.innerHTML;
        } else {
            window.location.reload();
        }
    })
    .catch(err => {
        console.error('Filter failed:', err);
        window.location.reload();
    })
    .finally(() => {
        const container = document.getElementById('tasksListContainer');
        if (container) {
            container.style.pointerEvents = 'auto';
            container.style.opacity = '1';
        }
    });
};

if (searchInput) {
    searchInput.addEventListener('input', () => {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(triggerFilter, 500);
    });
}

filterForm?.querySelectorAll('select:not(#perPageSelect)').forEach(sel => {
    sel.addEventListener('change', triggerFilter);
});

if (perPageSelect) {
    perPageSelect.addEventListener('change', () => {
        if (perPageSelect.value === 'custom') {
            customPerPage.style.display = 'block';
            customPerPage.focus();
        } else {
            customPerPage.style.display = 'none';
            triggerFilter();
        }
    });
}

if (customPerPage) {
    customPerPage.addEventListener('input', () => {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(triggerFilter, 800);
    });
}

// Handle pagination clicks
document.addEventListener('click', (e) => {
    const link = e.target.closest('.cd-pagination-wrap a');
    if (link) {
        e.preventDefault();
        const url = link.href;
        const ajaxUrl = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now();
        
        window.history.pushState({ path: url }, '', url);
        
        const container = document.getElementById('tasksListContainer');
        if (container) {
            container.style.pointerEvents = 'none';
            container.style.opacity = '0.6';
        }
        
        fetch(ajaxUrl, { headers: { 'Accept': 'text/html' } })
        .then(r => {
            if (!r.ok) throw new Error('Response not OK');
            return r.text();
        })
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.getElementById('tasksListContainer');
            const currentContainer = document.getElementById('tasksListContainer');
            
            if (newContent && currentContainer) {
                currentContainer.innerHTML = newContent.innerHTML;
                currentContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                window.location.href = url;
            }
        })
        .catch(err => {
            console.error('Pagination failed:', err);
            window.location.href = url;
        })
        .finally(() => {
            const container = document.getElementById('tasksListContainer');
            if (container) {
                container.style.pointerEvents = 'auto';
                container.style.opacity = '1';
            }
        });
    }
});

// Auto-dismiss flash messages
const flash = document.getElementById('flashMsg');
if (flash) setTimeout(() => flash.remove(), 4000);
const flashErr = document.getElementById('flashMsgErr');
if (flashErr) setTimeout(() => flashErr.remove(), 6000);
</script>
@endsection
