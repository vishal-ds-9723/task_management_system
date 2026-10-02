@if ($paginator->hasPages())
<nav class="pg-nav" role="navigation" aria-label="Pagination">
    <style>
        .pg-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            gap: 12px;
            flex-wrap: wrap;
            padding: 4px 0;
        }
        .pg-info {
            font-size: 13px;
            color: var(--text2, #64748b);
            font-weight: 600;
        }
        .pg-info strong {
            color: var(--text, #1e293b);
        }
        .pg-links {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .pg-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border-radius: 10px;
            background: var(--card, #fff);
            border: 1px solid var(--border, #e2e8f0);
            color: var(--text2, #64748b);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .pg-btn:hover:not(.pg-btn-disabled) {
            background: var(--primary-dim, rgba(79,70,229,0.08));
            border-color: var(--primary, #4f46e5);
            color: var(--primary, #4f46e5);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79,70,229,0.12);
        }
        .pg-btn-active {
            background: var(--primary, #4f46e5) !important;
            border-color: var(--primary, #4f46e5) !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(79,70,229,0.25);
        }
        .pg-btn-disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: var(--bg, #f8fafc);
        }
        .pg-dots {
            color: var(--text3, #94a3b8);
            font-weight: 800;
            padding: 0 2px;
        }
        @media (max-width: 768px) {
            .pg-nav { justify-content: center; text-align: center; flex-direction: column; }
            .pg-info { order: 2; margin-top: 8px; }
            .pg-links { order: 1; }
        }
    </style>

    {{-- Showing Results Info (Left Side) --}}
    <div class="pg-info">
        Showing 
        @if ($paginator->firstItem())
            <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong>
        @else
            0
        @endif
        of <strong>{{ number_format($paginator->total()) }}</strong> results
    </div>

    {{-- Pagination Links (Right Side) --}}
    <div class="pg-links">
        {{-- First Page --}}
        @if ($paginator->onFirstPage())
            <span class="pg-btn pg-btn-disabled" aria-disabled="true" title="First Page"><i class="fa-solid fa-angle-double-left"></i></span>
        @else
            <a href="{{ $paginator->url(1) }}" class="pg-btn" title="First Page"><i class="fa-solid fa-angle-double-left"></i></a>
        @endif

        {{-- Previous Page --}}
        @if ($paginator->onFirstPage())
            <span class="pg-btn pg-btn-disabled" aria-disabled="true" title="Previous"><i class="fa-solid fa-chevron-left"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pg-btn" rel="prev" title="Previous"><i class="fa-solid fa-chevron-left"></i></a>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pg-dots">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pg-btn pg-btn-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pg-btn">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pg-btn" rel="next" title="Next"><i class="fa-solid fa-chevron-right"></i></a>
        @else
            <span class="pg-btn pg-btn-disabled" aria-disabled="true" title="Next"><i class="fa-solid fa-chevron-right"></i></span>
        @endif

        {{-- Last Page --}}
        @if ($paginator->currentPage() < $paginator->lastPage())
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="pg-btn" title="Last Page"><i class="fa-solid fa-angle-double-right"></i></a>
        @else
            <span class="pg-btn pg-btn-disabled" aria-disabled="true" title="Last Page"><i class="fa-solid fa-angle-double-right"></i></span>
        @endif
    </div>
</nav>
@endif
