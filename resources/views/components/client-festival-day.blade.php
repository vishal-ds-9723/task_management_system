@props(['date', 'dayFestivals', 'isToday', 'isCurrentMonth', 'catIcon'])

<div class="fest-day {{ !$isCurrentMonth ? 'muted' : '' }} {{ $isToday ? 'today' : '' }}" onclick="showDayDetails('{{ $date->format('Y-m-d') }}', '{{ $date->format('d F Y') }}')">
    <div class="fest-day-num">{{ $date->format('d') }}</div>

    @if($dayFestivals->isNotEmpty())
        <div class="fest-day-events">
            @foreach($dayFestivals as $festival)
                <div class="fest-dot {{ $festival->is_selected ? 'selected' : '' }}"></div>
            @endforeach
        </div>

        {{-- Hover Overlay --}}
        <div class="fest-day-overlay">
            <div class="fest-day-overlay-icon">
                <i class="fa-solid {{ $catIcon($dayFestivals->first()->category ?? 'default') }}"></i>
            </div>
            <div class="fest-day-overlay-name">
                {{ $dayFestivals->count() }} Festival{{ $dayFestivals->count() > 1 ? 's' : '' }}
            </div>
        </div>
    @endif
</div>

