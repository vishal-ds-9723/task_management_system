<style>

    .ccs-selected {
    height: 42px;
    padding: 0 12px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: var(--card);
    font-size: 13px;
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

    


    <div class="ccs-selected {{ $class }}" id="{{ $uid }}_selected" onclick="toggleCcsDropdown('{{ $uid }}')" style="border:1px solid var(--border);background:var(--card2)">
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
            <div class="ccs-option {{ !$value ? 'selected' : '' }}" data-value="" onclick="selectCcsOption('{{ $uid }}', '', '', '', '{{ $placeholder ?? 'Select a client' }}', '{{ $onchange ?? '' }}', this)">
                <span class="ccs-opt-name" style="opacity:0.7">Clear selection</span>
            </div>
            @foreach($clients as $client)
                @php
                    $logoUrl = $client->logo ? asset('storage/' . $client->logo) : '';
                @endphp
                <div class="ccs-option {{ $value == $client->id ? 'selected' : '' }}" 
                     onclick="selectCcsOption('{{ $uid }}', '{{ $client->id }}', '{{ $logoUrl }}', '{{ $client->emoji }}', '{{ addslashes(str_replace('\'', '', $client->name)) }}', '{{ $onchange ?? '' }}', this)">
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
.custom-client-select{position:relative;font-size:13px;font-family:inherit;}
.ccs-selected{display:flex;align-items:center;justify-content:space-between;gap:8px;background:var(--card);border:1px solid var(--border);border-radius:10px;padding:8px 12px;cursor:pointer;transition:all .15s ease;user-select:none;min-height:38px}
.ccs-selected:hover{border-color:var(--primary)}
.ccs-selected.open{border-color:var(--primary);box-shadow:0 0 0 2px rgba(79,109,240,.15);border-radius:10px 10px 0 0;position:relative;z-index:1001;}
.ccs-placeholder{color:var(--text3);font-weight:500;font-size:13px}
.ccs-arrow{font-size:10px;color:var(--text3);transition:transform .2s ease;flex-shrink:0}
.ccs-selected.open .ccs-arrow{transform:rotate(180deg)}
.ccs-sel-logo{width:20px;height:20px;border-radius:6px;object-fit:cover;flex-shrink:0;border:1px solid var(--border)}
.ccs-sel-emoji{font-size:16px;flex-shrink:0}
.ccs-sel-name{font-weight:600;color:var(--text1);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ccs-dropdown{display:none;position:absolute;top:100%;left:0;right:0;background:var(--card);border:1px solid var(--primary);border-top:none;border-radius:0 0 10px 10px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:9999;overflow:hidden}
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
</style>
<script>
if (typeof window.toggleCcsDropdown === 'undefined') {
    window.toggleCcsDropdown = function(uid) {
        const sel = document.getElementById(uid + '_selected');
        const dd = document.getElementById(uid + '_dropdown');
        
        document.querySelectorAll('.ccs-dropdown.show').forEach(el => {
            if (el.id !== uid + '_dropdown') {
                el.classList.remove('show');
                el.previousElementSibling.classList.remove('open');
            }
        });

        const isOpen = dd.classList.contains('show');
        if (isOpen) {
            dd.classList.remove('show');
            sel.classList.remove('open');
        } else {
            dd.classList.add('show');
            sel.classList.add('open');
            const searchInput = dd.querySelector('.ccs-search');
            if(searchInput) {
                searchInput.value = '';
                window.filterCcsOptions(uid, '');
                setTimeout(() => searchInput.focus(), 50);
            }
        }
    };

    window.filterCcsOptions = function(uid, query) {
        const q = query.toLowerCase();
        document.querySelectorAll('#' + uid + '_options .ccs-option').forEach(opt => {
            const name = (opt.querySelector('.ccs-opt-name')?.textContent || '').toLowerCase();
            opt.style.display = name.includes(q) ? 'flex' : 'none';
        });
    };

    window.selectCcsOption = function(uid, value, logoUrl, emoji, name, onchangeStr, element) {
        const wrapper = document.getElementById(uid + '_wrapper');
        const input = wrapper.querySelector('input[type="hidden"]');
        const oldValue = input.value;
        input.value = value;
        
        const sel = document.getElementById(uid + '_selected');
        let iconHtml = '';
        if (logoUrl) {
            iconHtml = '<img src="' + logoUrl + '" class="ccs-sel-logo">';
        } else if (emoji) {
            iconHtml = '<span class="ccs-sel-emoji">' + emoji + '</span>';
        }
        
        if (value) {
            sel.innerHTML = iconHtml + '<span class="ccs-sel-name">' + name + '</span><i class="fa-solid fa-chevron-down ccs-arrow"></i>';
        } else {
            sel.innerHTML = '<span class="ccs-placeholder">' + name + '</span><i class="fa-solid fa-chevron-down ccs-arrow"></i>';
        }
        
        document.querySelectorAll('#' + uid + '_options .ccs-option').forEach(o => o.classList.remove('selected'));
        if (element) element.classList.add('selected');
        
        document.getElementById(uid + '_dropdown').classList.remove('show');
        sel.classList.remove('open');
        
        if (oldValue !== value) {
            input.dispatchEvent(new Event('change', { bubbles: true }));
            if (onchangeStr) {
                if (onchangeStr.includes('this.form.submit()')) {
                    input.closest('form').submit();
                } else if (onchangeStr !== '') {
                    try {
                        const fn = new Function('event', onchangeStr);
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
                dd.previousElementSibling.classList.remove('open');
            });
        }
    });
}
</script>
@endonce
