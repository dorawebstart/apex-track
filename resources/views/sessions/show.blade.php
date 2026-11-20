<x-layouts.app :title="$session->title">
    @push('head')
        <link rel="preload" as="image" href="{{ $session->image_url }}" fetchpriority="high">
    @endpush

    <section class="mx-auto max-w-7xl px-4 pt-28 sm:px-6 lg:px-8">
        <a href="{{ route('sessions.index') }}" class="text-sm text-slate-400 hover:text-apex-300">← До гаража</a>

        <div class="relative mt-4 overflow-hidden rounded-[2.5rem] ring-1 ring-white/10 shadow-2xl shadow-black/60">
            <img src="{{ $session->image_url }}" alt="{{ $session->car_model }}" width="1920" height="1080"
                 fetchpriority="high" decoding="async" class="aspect-[16/9] w-full object-cover lg:aspect-[21/9]">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-night-950 via-night-950/10 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-6 sm:p-10">
                <span class="chip">{{ $session->category->name }}</span>
                @if ($session->status !== 'published')
                    <span class="chip ml-2 !bg-amber-500/15 !text-amber-300 !ring-amber-400/40">{{ \App\Models\TrackSession::STATUSES[$session->status] }}</span>
                @endif
                <h1 class="mt-3 text-3xl font-bold sm:text-5xl">{{ $session->title }}</h1>
                <p class="mt-2 text-lg text-apex-300">{{ $session->car_model }} · {{ $session->track_name }}</p>
            </div>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-[1.3fr_1fr]">
            {{-- Анімований спідометр: стрілка і лічильник 0 → максимальна швидкість --}}
            <div class="card p-6 sm:p-10" data-reveal="left">
                <h2 class="text-xl font-bold">Характеристики</h2>
                <div class="mt-6 grid items-center gap-8 sm:grid-cols-[1fr_auto]">
                    <div class="relative mx-auto w-full max-w-sm" data-speedometer data-speed="{{ $session->top_speed_kmh }}" data-max="400">
                        <svg viewBox="0 0 200 120" class="w-full" aria-hidden="true">
                            <path d="M20 110 A80 80 0 0 1 180 110" fill="none" stroke="#212b57" stroke-width="14" stroke-linecap="round"/>
                            <path d="M20 110 A80 80 0 0 1 180 110" fill="none" stroke="url(#apexGrad)" stroke-width="14" stroke-linecap="round"
                                  pathLength="100" stroke-dasharray="100" stroke-dashoffset="100" data-arc/>
                            <defs>
                                <linearGradient id="apexGrad" x1="0" x2="1">
                                    <stop offset="0" stop-color="#0fa89d"/><stop offset="1" stop-color="#7ff0e6"/>
                                </linearGradient>
                            </defs>
                            <g data-needle style="transform-origin: 100px 110px; transform: rotate(-90deg)">
                                <line x1="100" y1="110" x2="100" y2="42" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
                            </g>
                            <circle cx="100" cy="110" r="7" fill="#19d3c5"/>
                        </svg>
                        <p class="-mt-4 text-center font-display text-5xl font-bold tabular-nums"><span data-counter>0</span></p>
                        <p class="text-center text-xs font-bold uppercase tracking-widest text-slate-400">км/год максимум</p>
                    </div>
                    <dl class="grid grid-cols-2 gap-4 sm:grid-cols-1">
                        <div class="rounded-2xl bg-night-800 p-4">
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Потужність</dt>
                            <dd class="font-display text-2xl font-bold"><span data-count-to="{{ $session->horsepower }}">0</span> к.с.</dd>
                        </div>
                        <div class="rounded-2xl bg-night-800 p-4">
                            <dt class="text-xs uppercase tracking-wider text-slate-400">0–100 км/год</dt>
                            <dd class="font-display text-2xl font-bold"><span data-count-to="{{ $session->zero_to_100 }}" data-decimals="1">0</span> с</dd>
                        </div>
                    </dl>
                </div>

                <h2 class="mt-10 text-xl font-bold">Про заїзд</h2>
                <p class="mt-3 whitespace-pre-line leading-relaxed text-slate-300">{{ $session->description }}</p>
            </div>

            <aside id="booking" class="card h-fit p-6 sm:p-8 lg:sticky lg:top-24" data-reveal="right">
                <h2 class="text-xl font-bold">Розклад і місця</h2>
                <dl class="mt-6 space-y-4 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Початок</dt><dd class="text-right font-semibold">{{ $session->starts_at->translatedFormat('j F Y, H:i') }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Завершення</dt><dd class="text-right font-semibold">{{ $session->ends_at->translatedFormat('j F Y, H:i') }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Трек</dt><dd class="font-semibold">{{ $session->track_name }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Максимум пілотів</dt><dd class="font-semibold">{{ $session->capacity }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Активних бронювань</dt><dd class="font-semibold" data-booked>{{ $session->active_bookings_total }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-400">Вільних місць</dt><dd class="font-display text-lg font-bold text-apex-300" data-available>{{ $session->available_seats }}</dd></div>
                </dl>

                <div class="mt-2 h-2 overflow-hidden rounded-full bg-night-800">
                    <div class="h-full rounded-full bg-apex-500 transition-all duration-700"
                         style="width: {{ $session->capacity ? round($session->active_bookings_total / $session->capacity * 100) : 0 }}%"></div>
                </div>

                <div class="mt-8">
                    @includeIf('sessions.partials.booking-actions', ['session' => $session])
                    @if (! view()->exists('sessions.partials.booking-actions'))
                        <p class="rounded-2xl bg-night-800 p-4 text-sm text-slate-400">Бронювання з'явиться після входу в систему.</p>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</x-layouts.app>
