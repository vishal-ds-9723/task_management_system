{{-- resources/views/components/stat-card.blade.php --}}
@props(['color' => 'var(--primary)', 'icon' => 'fas fa-chart-bar', 'label', 'value', 'sub' => '', 'subColor' => ''])

<div class="stat-card" style="--accent-c:{{ $color }}">
    <div class="s-icon">
        @if(Str::startsWith($icon, 'fa'))
            <i class="{{ $icon }}" style="color:{{ $color }};opacity:0.25"></i>
        @else
            {{ $icon }}
        @endif
    </div>
    <div class="s-label">{{ $label }}</div>
    <div class="s-value">{{ $value }}</div>
    @if($sub)
        <div class="s-sub" style="color:{{ $subColor ?: $color }}">
            <span style="font-size:13px">{{ Str::startsWith($sub, ['↑','↓','●','•']) ? '' : '•' }}</span>
            {{ $sub }}
        </div>
    @endif
</div>
