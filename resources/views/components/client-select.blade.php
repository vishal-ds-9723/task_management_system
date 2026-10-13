<style>
.ccs-selected {
    height: 42px;
    padding: 0 12px;
    border-radius: 10px;
    border: 1px solid var(--border, var(--dz-border, #e2e8f0));
    background: var(--card, var(--dz-surface, #ffffff));
    font-size: 13px;
    box-sizing: border-box;
}
</style>

@props(['clients', 'name' => 'client_id', 'value' => '', 'id' => null, 'onchange' => null, 'placeholder' => 'Select a client', 'width' => '100%', 'class' => ''])
@php
    $uid = uniqid('ccs_');
    $inputId = $id ?? $uid . '_input';
    $selectedClient = $clients->firstWhere('id', $value);
@endphp
<div class="custom-client-select" id="{{ $uid }}_wrapper" style="width: {{ $width ?? '100%' }};">
    <input type="hidden" name="{{ $name }}" id="{{ $inputId }}" value="{{ $value }}">

    <div class="ccs-selected {{ $class }}" id="{{ $uid }}_selected" onclick="toggleCcsDropdown('{{ $uid }}')">
        @if($selectedClient)
            @if($selectedClient->logo)
                <img src="{{ asset('storage/' . $selectedClient->logo) }}" class="ccs-sel-logo" alt="">
            @else
                <span class="ccs-sel-emoji">{{ $selectedClient->emoji }}</span>
            @endif
            <span class="ccs-sel-name">{{ $selectedClient->name }}</span>
        @else
            <span class="ccs-placeholder">{{ $placeholder }}</span>
        @endif
        <i class="fa-solid fa-chevron-down ccs-arrow"></i>
    </div>
    <div class="ccs-dropdown" id="{{ $uid }}_dropdown">
        <div class="ccs-search-wrap">
            <i class="fa-solid fa-magnifying-glass ccs-search-icon"></i>
            <input type="text" class="ccs-search" oninput="filterCcsOptions('{{ $uid }}', this.value)" placeholder="Search clients..." autocomplete="off">
        </div>
        <div class="ccs-options" id="{{ $uid }}_options">
            <div class="ccs-option {{ !$value ? 'selected' : '' }}" 
                 data-value="" 
                 data-logo="" 
                 data-emoji="" 
                 data-name="{{ $placeholder ?? 'Select a client' }}"
                 data-onchange="{{ $onchange ?? '' }}"
                 onclick="selectCcsOption(this, '{{ $uid }}')">
                <span class="ccs-opt-name" style="opacity:0.7">Clear selection</span>
            </div>
            @foreach($clients as $client)
                @php
                    $logoUrl = $client->logo ? asset('storage/' . $client->logo) : '';
                @endphp
                <div class="ccs-option {{ $value == $client->id ? 'selected' : '' }}" 
                     data-value="{{ $client->id }}"
                     data-logo="{{ $logoUrl }}"
                     data-emoji="{{ $client->emoji ?? '' }}"
                     data-name="{{ $client->name }}"
                     data-onchange="{{ $onchange ?? '' }}"
                     onclick="selectCcsOption(this, '{{ $uid }}')">
                    @if($client->logo)
                        <img src="{{ $logoUrl }}" alt="" class="ccs-logo">
                    @else
                        <span class="ccs-emoji">{{ $client->emoji }}</span>
                    @endif
                    <span class="ccs-opt-name">{{ $client->name }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

@once
<style>
.custom-client-select {
    position: relative;
    font-size: 13px;
    font-family: inherit;
    z-index: 10;
}
.custom-client-select.open {
    z-index: 99999 !important;
}
.ccs-selected {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    background: var(--card, var(--dz-surface, #ffffff));
    border: 1px solid var(--border, var(--dz-border, #cbd5e1));
    border-radius: 10px;
    padding: 8px 12px;
    cursor: pointer;
    transition: all .15s ease;
    user-select: none;
    min-height: 38px;
    color: var(--text1, var(--dz-text-main, #0f172a));
}
.ccs-selected:hover {
    border-color: var(--primary, var(--dz-primary, #4f46e5));
}
.ccs-selected.open {
    border-color: var(--primary, var(--dz-primary, #4f46e5));
    box-shadow: 0 0 0 2px rgba(79, 109, 240, .15);
    border-radius: 10px 10px 0 0;
    position: relative;
    z-index: 1001;
}
.ccs-placeholder {
    color: var(--text3, var(--dz-text-muted, #94a3b8));
    font-weight: 500;
    font-size: 13px;
}
.ccs-arrow {
    font-size: 10px;
    color: var(--text3, var(--dz-text-muted, #94a3b8));
    transition: transform .2s ease;
    flex-shrink: 0;
}
.ccs-selected.open .ccs-arrow {
    transform: rotate(180deg);
}
.ccs-sel-logo {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid var(--border, var(--dz-border, #e2e8f0));
}
.ccs-sel-emoji {
    font-size: 16px;
    flex-shrink: 0;
}
.ccs-sel-name {
    font-weight: 600;
    color: var(--text1, var(--dz-text-main, #0f172a));
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ccs-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--card, var(--dz-surface, #ffffff)) !important;
    border: 1px solid var(--primary, var(--dz-primary, #4f46e5)) !important;
    border-top: none !important;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, .15);
    z-index: 99999;
    overflow: hidden;
}
.ccs-dropdown.show {
    display: block !important;
}
.ccs-search-wrap {
    padding: 6px 8px;
    border-bottom: 1px solid var(--border, var(--dz-border, #e2e8f0));
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--card2, var(--dz-surface-sub, #f8fafc));
}
.ccs-search-icon {
    font-size: 11px;
    color: var(--text3, var(--dz-text-muted, #94a3b8));
}
.ccs-search {
    border: none !important;
    outline: none !important;
    background: transparent !important;
    font-size: 12px !important;
    color: var(--text1, var(--dz-text-main, #0f172a)) !important;
    width: 100% !important;
    font-family: inherit !important;
    padding: 4px 6px !important;
    box-shadow: none !important;
}
.ccs-search::placeholder {
    color: var(--text3, var(--dz-text-muted, #94a3b8));
}
.ccs-options {
    max-height: 220px;
    overflow-y: auto;
    background: var(--card, var(--dz-surface, #ffffff));
}
.ccs-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    cursor: pointer;
    transition: background .12s ease;
    background: var(--card, var(--dz-surface, #ffffff));
}
.ccs-option:hover {
    background: var(--primary-dim, var(--dz-surface-sub, #f1f5f9)) !important;
}
.ccs-option.selected {
    background: rgba(79, 109, 240, .1) !important;
}
.ccs-logo {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid var(--border, var(--dz-border, #e2e8f0));
}
.ccs-emoji {
    font-size: 16px;
    flex-shrink: 0;
    width: 22px;
    text-align: center;
}
.ccs-opt-name {
    font-size: 12.5px;
    font-weight: 500;
    color: var(--text1, var(--dz-text-main, #0f172a));
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
<script>
if (typeof window.toggleCcsDropdown === 'undefined') {
    window.toggleCcsDropdown = function(uid) {
        const wrapper = document.getElementById(uid + '_wrapper');
        const sel = document.getElementById(uid + '_selected');
        const dd = document.getElementById(uid + '_dropdown');
        if (!wrapper || !sel || !dd) return;
        
        document.querySelectorAll('.ccs-dropdown.show').forEach(el => {
            if (el.id !== uid + '_dropdown') {
                el.classList.remove('show');
                const otherWrapper = el.closest('.custom-client-select');
                if (otherWrapper) otherWrapper.classList.remove('open');
                const otherSel = otherWrapper ? otherWrapper.querySelector('.ccs-selected') : el.previousElementSibling;
                if (otherSel && otherSel.classList) otherSel.classList.remove('open');
            }
        });

        const isOpen = dd.classList.contains('show');
        if (isOpen) {
            dd.classList.remove('show');
            sel.classList.remove('open');
            wrapper.classList.remove('open');
        } else {
            dd.classList.add('show');
            sel.classList.add('open');
            wrapper.classList.add('open');
            const searchInput = dd.querySelector('.ccs-search');
            if (searchInput) {
                searchInput.value = '';
                window.filterCcsOptions(uid, '');
                setTimeout(() => searchInput.focus(), 60);
            }
        }
    };

    window.filterCcsOptions = function(uid, query) {
        const q = (query || '').toLowerCase();
        document.querySelectorAll('#' + uid + '_options .ccs-option').forEach(opt => {
            const name = (opt.dataset.name || opt.querySelector('.ccs-opt-name')?.textContent || '').toLowerCase();
            opt.style.display = name.includes(q) ? 'flex' : 'none';
        });
    };

    window.selectCcsOption = function(elementOrUid, valueOrUid, logoUrl, emoji, name, onchangeStr, legacyElement) {
        let uid, value, logo, em, clientName, onchange, element;

        if (elementOrUid && typeof elementOrUid === 'object' && elementOrUid.nodeType) {
            element = elementOrUid;
            uid = valueOrUid;
            value = element.dataset.value || '';
            logo = element.dataset.logo || '';
            em = element.dataset.emoji || '';
            clientName = element.dataset.name || '';
            onchange = element.dataset.onchange || '';
        } else {
            uid = elementOrUid;
            value = valueOrUid || '';
            logo = logoUrl || '';
            em = emoji || '';
            clientName = name || '';
            onchange = onchangeStr || '';
            element = legacyElement;
        }

        const wrapper = document.getElementById(uid + '_wrapper');
        if (!wrapper) return;
        const input = wrapper.querySelector('input[type="hidden"]');
        if (!input) return;

        const oldValue = input.value;
        input.value = value;
        
        const sel = document.getElementById(uid + '_selected');
        if (sel) {
            let iconHtml = '';
            if (logo) {
                iconHtml = '<img src="' + logo + '" class="ccs-sel-logo">';
            } else if (em) {
                iconHtml = '<span class="ccs-sel-emoji">' + em + '</span>';
            }
            
            if (value) {
                sel.innerHTML = iconHtml + '<span class="ccs-sel-name">' + clientName + '</span><i class="fa-solid fa-chevron-down ccs-arrow"></i>';
            } else {
                sel.innerHTML = '<span class="ccs-placeholder">' + clientName + '</span><i class="fa-solid fa-chevron-down ccs-arrow"></i>';
            }
            sel.classList.remove('open');
            sel.style.borderColor = '';
            sel.style.boxShadow = '';
        }
        
        wrapper.classList.remove('open');

        const optContainer = document.getElementById(uid + '_options');
        if (optContainer) {
            optContainer.querySelectorAll('.ccs-option').forEach(o => o.classList.remove('selected'));
        }
        if (element && element.classList) element.classList.add('selected');
        
        const dd = document.getElementById(uid + '_dropdown');
        if (dd) dd.classList.remove('show');
        
        if (oldValue !== value) {
            input.dispatchEvent(new Event('change', { bubbles: true }));
            if (onchange) {
                if (onchange.includes('this.form.submit()')) {
                    input.closest('form')?.submit();
                } else {
                    try {
                        const fn = new Function('event', onchange);
                        fn.call(input, new Event('change'));
                    } catch(e) { console.error('Error executing onchange', e); }
                }
            }
        }
    };

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-client-select')) {
            document.querySelectorAll('.ccs-dropdown.show').forEach(dd => {
                dd.classList.remove('show');
                const wrapper = dd.closest('.custom-client-select');
                if (wrapper) wrapper.classList.remove('open');
                const sel = wrapper ? wrapper.querySelector('.ccs-selected') : dd.previousElementSibling;
                if (sel && sel.classList) sel.classList.remove('open');
            });
        }
    });
}
</script>
@endonce
