{{-- resources/views/components/client-branding.blade.php --}}
@props(['client', 'size' => '22px', 'width' => null, 'radius' => '5px', 'showName' => false, 'nameStyle' => '', 'fit' => 'contain'])

@php
    $boxWidth = $width ?? 'calc(' . $size . ' * 1.75)';
    $logoUrl = null;
    if ($client && !empty($client->logo)) {
        $logoUrl = str_starts_with($client->logo, 'http') ? $client->logo : asset('storage/' . ltrim($client->logo, '/'));
    }
@endphp

@if($client)
    <span class="client-branding-wrapper" style="display: inline-flex; align-items: center; gap: 8px; vertical-align: middle;">
        @if($logoUrl)
            <span class="client-branding-logo-box" style="display: inline-flex; align-items: center; justify-content: center; width: {{ $boxWidth }}; height: {{ $size }}; border-radius: {{ $radius }}; overflow: hidden; flex-shrink: 0; background: var(--db-card-bg-subtle, var(--card2, #F8FAFC)); border: 1px solid var(--db-card-border, var(--border, #E2E8F0)); padding: 2px 4px; box-sizing: border-box;">
                <img src="{{ $logoUrl }}"
                     alt="{{ $client->name ?? 'Client' }}"
                     style="width: 100%; height: 100%; object-fit: {{ $fit }}; object-position: center; display: block;"
                     class="client-branding-logo"
                     loading="lazy"
                     onerror="this.parentElement.innerHTML='<span style=\'font-size:calc({{ $size }} * 0.55);line-height:1\'>{{ addslashes($client->emoji ?? '🏢') }}</span>';">
            </span>
        @else
            <span class="client-branding-emoji"
                  style="display: inline-flex; align-items: center; justify-content: center; width: {{ $boxWidth }}; height: {{ $size }}; border-radius: {{ $radius }}; background: var(--db-card-bg-subtle, var(--card2, #F8FAFC)); border: 1px solid var(--db-card-border, var(--border, #E2E8F0)); font-size: calc({{ $size }} * 0.6); flex-shrink: 0; overflow: hidden; box-sizing: border-box;">
                {!! $client->emoji ?? '<i class="fas fa-building" style="opacity: 0.5; font-size: 11px;"></i>' !!}
            </span>
        @endif

        @if($showName)
            <span class="client-branding-name" style="{{ $nameStyle }}">{{ $client->name }}</span>
        @endif
    </span>
@else
    <span class="client-branding-none" style="color: var(--text3, #94A3B8); opacity: 0.5; display: inline-flex; align-items: center; justify-content: center;">
        <i class="fas fa-building" style="font-size: {{ $size }};"></i>
    </span>
@endif
