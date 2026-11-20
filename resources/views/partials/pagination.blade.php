@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-center gap-2" aria-label="Пагінація">
        @if ($paginator->onFirstPage())
            <span class="rounded-full px-4 py-2 text-sm text-slate-600 ring-1 ring-white/5">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-full px-4 py-2 text-sm ring-1 ring-white/10 hover:ring-apex-400" aria-label="Попередня">←</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-slate-500">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="rounded-full bg-apex-500 px-4 py-2 text-sm font-bold text-night-950">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="rounded-full px-4 py-2 text-sm ring-1 ring-white/10 hover:ring-apex-400">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-full px-4 py-2 text-sm ring-1 ring-white/10 hover:ring-apex-400" aria-label="Наступна">→</a>
        @else
            <span class="rounded-full px-4 py-2 text-sm text-slate-600 ring-1 ring-white/5">→</span>
        @endif
    </nav>
@endif
