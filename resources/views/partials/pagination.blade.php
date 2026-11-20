{{-- Пагінація для світлих секцій --}}
@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="Пагінація">
        @if ($paginator->onFirstPage())
            <span class="px-4 py-2 text-sm text-ink-900/30">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-full px-4 py-2 text-sm hover:bg-ink-900/5" aria-label="Попередня">←</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-ink-600">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="rounded-full bg-ink-900 px-4 py-2 text-sm font-bold text-white">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="rounded-full px-4 py-2 text-sm hover:bg-ink-900/5">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-full px-4 py-2 text-sm hover:bg-ink-900/5" aria-label="Наступна">→</a>
        @else
            <span class="px-4 py-2 text-sm text-ink-900/30">→</span>
        @endif
    </nav>
@endif
