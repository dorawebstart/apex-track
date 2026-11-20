{{-- Картка сесії: зображення, назва, клас, короткий опис, дата, вільні місця, «Детальніше» --}}
<article class="card group flex h-full flex-col overflow-hidden transition duration-500 hover:-translate-y-1 hover:ring-apex-500/40">
    <a href="{{ route('sessions.show', $session) }}" class="relative block overflow-hidden" tabindex="-1">
        <img src="{{ $session->thumb_url }}" alt="{{ $session->car_model }}" width="1280" height="720"
             loading="lazy" decoding="async"
             class="aspect-[16/10] w-full object-cover transition duration-700 group-hover:scale-105">
        <span class="chip absolute left-4 top-4 bg-night-950/70 backdrop-blur">{{ $session->category->name }}</span>
    </a>
    <div class="flex grow flex-col p-6">
        <p class="text-xs font-bold uppercase tracking-wider text-apex-300">{{ $session->car_model }}</p>
        <h3 class="mt-2 text-lg font-bold leading-snug">{{ $session->title }}</h3>
        <p class="mt-3 grow text-sm text-slate-400">{{ Str::limit($session->description, 110) }}</p>
        <div class="mt-5 flex items-center justify-between text-sm">
            <span class="text-slate-300">📅 {{ $session->starts_at->translatedFormat('j F Y') }}</span>
            @if ($session->available_seats > 0)
                <span class="rounded-full bg-apex-500/10 px-3 py-1 font-bold text-apex-300">{{ $session->available_seats }} {{ trans_choice('місце|місця|місць', $session->available_seats) }}</span>
            @else
                <span class="rounded-full bg-rose-500/10 px-3 py-1 font-bold text-rose-300">Місць немає</span>
            @endif
        </div>
        <a href="{{ route('sessions.show', $session) }}" class="btn-ghost mt-5 w-full">Детальніше</a>
    </div>
</article>
