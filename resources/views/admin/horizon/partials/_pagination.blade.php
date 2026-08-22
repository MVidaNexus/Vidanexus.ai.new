@php
    /**
     * Compact dark-theme pagination for the Horizon admin views.
     *
     * Renders nothing when the result set fits on one page so the section
     * doesn't show an empty footer. Uses the same CSS vars as the rest of
     * the dashboard so it adapts to light/dark themes automatically.
     *
     * Receives the standard $paginator variable from Laravel.
     */
    $isRtl = strtolower(view()->shared('locale', app()->getLocale())) === 'ar';
@endphp

@if ($paginator->hasPages())
    <style>
        .hz-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            font-family: inherit;
            user-select: none;
        }
        .hz-pagination__summary {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            margin: 0 12px;
            letter-spacing: 0.02em;
        }
        .hz-pagination__btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 12px;
            border-radius: 10px;
            border: 1px solid var(--glass-border, rgba(255,255,255,0.08));
            background: var(--glass-bg, rgba(255,255,255,0.03));
            color: var(--text-main, #e2e8f0);
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease, transform 0.15s ease, color 0.15s ease;
        }
        .hz-pagination__btn:hover {
            background: rgba(14, 165, 233, 0.12);
            border-color: rgba(14, 165, 233, 0.35);
            color: var(--primary-cyan, #0ea5e9);
        }
        .hz-pagination__btn--icon {
            padding: 0;
            width: 36px;
        }
        .hz-pagination__btn--active {
            background: var(--primary-cyan, #0ea5e9);
            border-color: var(--primary-cyan, #0ea5e9);
            color: #fff;
            box-shadow: 0 4px 16px rgba(14, 165, 233, 0.25);
            cursor: default;
        }
        .hz-pagination__btn--active:hover {
            background: var(--primary-cyan, #0ea5e9);
            color: #fff;
        }
        .hz-pagination__btn[aria-disabled="true"],
        .hz-pagination__btn.is-disabled {
            opacity: 0.35;
            pointer-events: none;
        }
        .hz-pagination__btn i { font-size: 10px; }
        .hz-pagination__dots {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            color: var(--text-muted);
            font-size: 13px;
            letter-spacing: 2px;
        }
    </style>

    <nav role="navigation" aria-label="Pagination" class="hz-pagination" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="hz-pagination__btn hz-pagination__btn--icon is-disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                <i class="fas {{ $isRtl ? 'fa-chevron-right' : 'fa-chevron-left' }}"></i>
            </span>
        @else
            <a class="hz-pagination__btn hz-pagination__btn--icon" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                <i class="fas {{ $isRtl ? 'fa-chevron-right' : 'fa-chevron-left' }}"></i>
            </a>
        @endif

        {{-- Numbered pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="hz-pagination__dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="hz-pagination__btn hz-pagination__btn--active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="hz-pagination__btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a class="hz-pagination__btn hz-pagination__btn--icon" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                <i class="fas {{ $isRtl ? 'fa-chevron-left' : 'fa-chevron-right' }}"></i>
            </a>
        @else
            <span class="hz-pagination__btn hz-pagination__btn--icon is-disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                <i class="fas {{ $isRtl ? 'fa-chevron-left' : 'fa-chevron-right' }}"></i>
            </span>
        @endif

        @if (method_exists($paginator, 'total'))
            <span class="hz-pagination__summary">
                Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}
            </span>
        @endif
    </nav>
@endif
