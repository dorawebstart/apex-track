<x-layouts.app :title="$session->title">
    @push('head')
        <link rel="preload" as="image" href="{{ $session->image_url }}" fetchpriority="high">
    @endpush

    {{-- Фото на весь екран, дата й назва поверх фото (як formulaauto) --}}
    <section class="relative h-[88svh] min-h-[560px] overflow-hidden bg-night-950">
        <div class="img-shell absolute inset-0">
            <img src="{{ $session->image_url }}" alt="{{ $session->car_model }}" width="1920" height="1080"
                 fetchpriority="high" decoding="async" data-fade class="size-full object-cover">
        </div>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-black/45"></div>

        <div class="absolute inset-0 flex flex-col items-center justify-center px-4 pt-14 text-center">
            @if ($session->status !== 'published')
                <span class="mb-6 rounded-full bg-amber-400 px-4 py-1.5 text-xs font-bold text-ink-900">{{ \App\Models\TrackSession::STATUSES[$session->status] }}</span>
            @endif
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-white/80">{{ $session->starts_at->translatedFormat('j F Y') }} · {{ $session->track_name }}</p>
            <h1 class="mt-5 max-w-5xl text-4xl font-medium uppercase leading-[1.05] tracking-[0.02em] text-white sm:text-6xl lg:text-7xl">{{ $session->car_model }}</h1>
            <a href="#booking" class="link-underline mt-8 text-sm font-semibold text-white">Розклад і бронювання ↓</a>
        </div>

        <a href="{{ route('sessions.index') }}" class="absolute left-4 top-20 z-10 rounded-full bg-black/30 px-4 py-2 text-xs font-semibold text-white backdrop-blur-md transition hover:bg-black/50 sm:left-8">← До гаража</a>
    </section>

    {{-- Світла секція: назва й опис ліворуч, великі цифри та розклад праворуч --}}
    <section class="bg-paper-50 text-ink-900">
        <div class="mx-auto grid max-w-7xl gap-16 px-4 py-24 sm:px-6 lg:grid-cols-2 lg:gap-24 lg:px-8">
            <div data-reveal="left">
                <p class="eyebrow text-apex-700">{{ $session->category->name }} · {{ $session->brand }}</p>
                <h2 class="mt-5 text-3xl font-medium leading-tight sm:text-5xl">{{ $session->title }}</h2>
                <p class="mt-8 whitespace-pre-line text-lg leading-relaxed text-ink-600">{{ $session->description }}</p>
            </div>

            <div data-reveal="right">
                <dl class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <dd class="stat-num" data-count-to="{{ $session->top_speed_kmh }}">{{ $session->top_speed_kmh }}</dd>
                        <dt class="eyebrow mt-4 text-ink-600">км/год</dt>
                    </div>
                    <div>
                        <dd class="stat-num" data-count-to="{{ $session->horsepower }}">{{ $session->horsepower }}</dd>
                        <dt class="eyebrow mt-4 text-ink-600">к.с.</dt>
                    </div>
                    <div>
                        <dd class="stat-num"><span data-count-to="{{ $session->zero_to_100 }}" data-decimals="1">{{ number_format($session->zero_to_100, 1, ',') }}</span><span class="text-3xl"> с</span></dd>
                        <dt class="eyebrow mt-4 text-ink-600">0–100 км/год</dt>
                    </div>
                </dl>

                <div id="booking" class="mt-16 scroll-mt-28">
                    <p class="eyebrow text-ink-600">Розклад і місця</p>
                    <dl class="mt-2">
                        <div class="rule-row"><dt class="text-ink-600">Початок</dt><dd class="text-right font-semibold">{{ $session->starts_at->translatedFormat('j F Y, H:i') }}</dd></div>
                        <div class="rule-row"><dt class="text-ink-600">Завершення</dt><dd class="text-right font-semibold">{{ $session->ends_at->translatedFormat('j F Y, H:i') }}</dd></div>
                        <div class="rule-row"><dt class="text-ink-600">Автодром</dt><dd class="font-semibold">{{ $session->track_name }}</dd></div>
                        <div class="rule-row"><dt class="text-ink-600">Максимум пілотів</dt><dd class="font-semibold">{{ $session->capacity }}</dd></div>
                        <div class="rule-row"><dt class="text-ink-600">Активних бронювань</dt><dd class="font-semibold" data-booked>{{ $session->active_bookings_total }}</dd></div>
                        <div class="rule-row"><dt class="text-ink-600">Вільних місць</dt><dd class="font-display text-2xl font-medium text-apex-700" data-available>{{ $session->available_seats }}</dd></div>
                    </dl>
                    <div class="mt-6 h-1 overflow-hidden rounded-full bg-ink-900/10">
                        <div class="h-full rounded-full bg-apex-600 transition-all duration-700"
                             style="width: {{ $session->capacity ? round($session->active_bookings_total / $session->capacity * 100) : 0 }}%"></div>
                    </div>

                    <div class="mt-10">
                        @include('sessions.partials.booking-actions')
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Графітова секція: що входить у заїзд --}}
    <section class="bg-graphite-900 text-white">
        <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <p class="eyebrow text-apex-400" data-reveal="left">Що входить у заїзд</p>
            <div class="mt-12 grid gap-x-12 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Брифінг', 'Теорія траси, прапори, траєкторії й правила піт-лейну перед виїздом.'],
                    ['Інструктор', 'Досвідчений пілот поруч у перших серіях і розбір помилок після заїзду.'],
                    ['Екіпіровка', 'Шолом, підшолом і рукавички. Для трекових авто — комбінезон і HANS.'],
                    ['Телеметрія', 'Час кожного кола та графіки швидкості — на згадку й для прогресу.'],
                ] as $i => [$heading, $text])
                    <div class="border-t border-white/15 pt-6" data-reveal="up" style="transition-delay: {{ $i * 120 }}ms">
                        <h3 class="text-lg font-medium">{{ $heading }}</h3>
                        <p class="mt-3 leading-relaxed text-slate-400">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
