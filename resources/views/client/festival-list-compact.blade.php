@php
    $grouped = $festivals->groupBy(fn($f) => $f->date->format('F Y'));
@endphp

@if($festivals->count())
    @foreach($grouped as $month => $monthFestivals)
        <div class="gc-month-group">
            <div class="gc-month-label">
                <i class="fa-solid fa-calendar"></i> {{ $month }}
            </div>
            <div class="gc-list">
                @foreach($monthFestivals as $festival)
                    @php
                        $daysAway = (int) now()->startOfDay()->diffInDays($festival->date->startOfDay(), false);
                        $isSelected = $festival->selected !== null;
                        $selectedTypes = $isSelected ? ($festival->selected->content_types ?? []) : [];
                        $selectedNotes = $isSelected ? ($festival->selected->notes ?? '') : '';
                    @endphp
                    <div class="gc-card {{ $isSelected ? 'gc-card-selected' : '' }}" data-festival-id="{{ $festival->id }}">
                        <div class="gc-card-emoji">{{ $festival->emoji ?? '🎉' }}</div>
                        <div class="gc-card-body">
                            <div class="gc-card-name">{{ $festival->name }}</div>
                            <div class="gc-card-info">
                                <span class="gc-card-date">
                                    <i class="fa-solid fa-calendar-day"></i>
                                    {{ $festival->date->format('M d') }}
                                </span>
                                @if($festival->category)
                                    <span style="font-size: 9px; font-weight: 700; text-transform: uppercase; opacity: 0.6;">
                                        {{ $festival->category }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="gc-card-days {{ $daysAway == 0 ? 'gc-card-days-today' : ($daysAway <= 7 && $daysAway > 0 ? 'gc-card-days-soon' : ($daysAway > 0 ? 'gc-card-days-upcoming' : 'gc-card-days-past')) }}">
                            @if($daysAway == 0)
                                Today
                            @elseif($daysAway == 1)
                                Tomorrow
                            @elseif($daysAway > 0)
                                {{ $daysAway }}d
                            @else
                                {{ abs($daysAway) }}d ago
                            @endif
                        </div>
                        <div style="display: flex; gap: 4px; flex-shrink: 0;">
                            @if($isSelected)
                                <button class="gc-card-btn gc-card-btn-selected" onclick="openGCModal({{ $festival->id }}, '{{ e($festival->name) }}', {{ json_encode($selectedTypes) }}, '{{ e($selectedNotes) }}')">
                                    <i class="fa-solid fa-check"></i> Selected
                                </button>
                                <button class="gc-card-btn" onclick="deselectGCFestival({{ $festival->id }})" style="color: #dc2626; border-color: #dc2626;">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            @else
                                <button class="gc-card-btn" onclick="openGCModal({{ $festival->id }}, '{{ e($festival->name) }}', [], '')">
                                    <i class="fa-solid fa-plus"></i> Select
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@else
    <div class="gc-empty">
        <div class="gc-empty-icon">
            <i class="fa-solid fa-calendar-xmark"></i>
        </div>
        <div class="gc-empty-title">No festivals found</div>
        <div class="gc-empty-text">Adjust your filters or check back later</div>
    </div>
@endif
