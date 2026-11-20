{{-- Картка сесії без рамки (для світлих секцій): фото, клас, назва, короткий опис, дата, вільні місця, «Детальніше» --}}
<article class="group flex h-full flex-col">
    <a href="{{ route('sessions.show', $session) }}" class="relative block overflow-hidden rounded-[2rem]" tabindex="-1">
        <img src="{{ $session->thumb_url }}" alt="{{ $session->car_model }}" width="1280" height="800"
             loading="lazy" decoding="async"
             class="aspect-[4/3] w-full object-cover transition duration-700 group-hover:scale-105">
        @if ($session->available_seats === 0)
            <span class="absolute right-4 top-4 rounded-full bg-ink-900/80 px-3 py-1 text-xs font-bold text-white backdrop-blur">Місць немає</span>
        @endif
    </a>
    <div class="flex grow flex-col pt-6">
        <p class="eyebrow text-apex-700">{{ $session->category->name }} · {{ $session->car_model }}</p>
        <h3 class="mt-3 text-xl font-medium leading-snug">{{ $session->title }}</h3>
        <p class="mt-3 grow leading-relaxed text-ink-600">{{ Str::limit($session->description, 110) }}</p>
        <div class="mt-5 flex items-center justify-between border-t border-ink-900/10 pt-4 text-sm">
            <span class="font-semibold">{{ $session->starts_at->translatedFormat('j F Y') }}</span>
            @if ($session->available_seats > 0)
                <span class="text-ink-600">вільно <b class="text-ink-900">{{ $session->available_seats }}</b> {{ trans_choice('місце|місця|місць', $session->available_seats) }}</span>
            @else
                <span class="font-semibold text-rose-600">заповнено</span>
            @endif
        </div>
        <a href="{{ route('sessions.show', $session) }}" class="link-underline mt-5 self-start text-sm font-bold uppercase tracking-wider">Детальніше ›</a>
    </div>
</article>
