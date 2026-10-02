@props(['festival', 'isSelectionAllowed' => true])

@php
    $cat = $festival->category ?? 'default';
    $catIcon = fn($c) => match($c) {
        'religious'=>'fa-hands-praying','national'=>'fa-flag','international'=>'fa-globe',
        'awareness'=>'fa-ribbon','cultural'=>'fa-masks-theater',default=>'fa-calendar-star'
    };
    $daysUntil = now()->startOfDay()->diffInDays($festival->date->startOfDay(), false);
@endphp

<div class="fest-card {{ $festival->is_selected ? 'selected' : '' }}" data-category="{{ $cat }}">
    <div class="fest-card-header">
        <div class="fest-card-icon">
            <i class="fa-solid {{ $catIcon($cat) }}"></i>
        </div>
        <div class="fest-card-info">
            <div class="fest-card-name">{{ $festival->name }}</div>
            <div class="fest-card-meta">
                <span class="fest-card-date">{{ $festival->date->format('D, d M') }}</span>
                <span class="fest-card-cat">{{ ucfirst($cat) }}</span>
            </div>
        </div>
    </div>
    <div class="fest-card-body">
        @if($daysUntil >= 0)
            <div class="fest-days-pill">
                {{ $daysUntil == 0 ? 'Today' : ($daysUntil == 1 ? 'Tomorrow' : "In {$daysUntil} Days") }}
            </div>
        @endif
        @if($festival->description)
            <div class="fest-card-desc">{{ $festival->description }}</div>
        @endif
    </div>
    <div class="fest-card-footer">
        @if($isSelectionAllowed)
            @if($festival->is_selected)
                <button class="fest-btn edit" onclick="openModal({{ $festival->id }})">Edit</button>
                <button class="fest-btn remove" onclick="removeFestival({{ $festival->id }})">Remove</button>
            @else
                <button class="fest-btn primary" onclick="openModal({{ $festival->id }})">Select Festival</button>
            @endif
        @else
            <div style="text-align:center; width:100%; font-size:11px; color:var(--muted); font-weight:700; padding:10px; background:var(--card2); border-radius:10px;">
                Selection Restricted
            </div>
        @endif
    </div>
</div>

