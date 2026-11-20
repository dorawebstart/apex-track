<x-layouts.app>
    @if ($sessions->isNotEmpty())
        @push('head')
            <link rel="preload" as="image" href="{{ $sessions->first()->image_url }}" fetchpriority="high">
        @endpush
    @endif

    {{-- HERO: «сцена» зі слайдером актуальних сесій --}}
    <section class="relative overflow-hidden bg-[radial-gradient(ellipse_at_center,_#1a2550_0%,_#070b1a_65%)] pt-24" data-slider>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs font-bold uppercase tracking-[0.3em] text-slate-400">Актуальні заїзди</p>
                <div class="flex flex-wrap gap-2" role="tablist" aria-label="Клас авто">
                    <button type="button" class="rounded-full px-4 py-2 text-sm font-semibold transition aria-selected:bg-white aria-selected:text-night-950 text-slate-300 ring-1 ring-white/10 hover:text-white" role="tab" aria-selected="true" data-filter="all">Усі</button>
                    @foreach ($categories as $category)
                        <button type="button" class="rounded-full px-4 py-2 text-sm font-semibold transition aria-selected:bg-white aria-selected:text-night-950 text-slate-300 ring-1 ring-white/10 hover:text-white" role="tab" aria-selected="false" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
                    @endforeach
                </div>
            </div>

            @forelse ($sessions as $session)
                <article class="slide" data-slide data-category="{{ $session->category->slug }}" @if (! $loop->first) hidden @endif>
                    <div class="relative mt-6 overflow-hidden rounded-[2.5rem] ring-1 ring-white/10 shadow-2xl shadow-black/60">
                        <img src="{{ $session->image_url }}" alt="{{ $session->car_model }} — {{ $session->title }}"
                             width="1920" height="1080" decoding="async"
                             @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif
                             class="slide-image aspect-[16/9] w-full object-cover sm:aspect-[21/9]">
                        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-night-950 via-night-950/20 to-transparent"></div>
                        <span class="chip absolute left-6 top-6 backdrop-blur">{{ $session->category->name }}</span>
                        <span class="absolute right-6 top-6 rounded-full bg-night-950/70 px-4 py-1.5 text-xs font-bold backdrop-blur">
                            {{ $session->starts_at->translatedFormat('j F') }} · {{ $session->track_name }}
                        </span>
                    </div>

                    <div class="relative z-10 -mt-20 grid gap-6 px-2 pb-4 sm:-mt-28 sm:px-8 md:grid-cols-2 md:items-end">
                        <div>
                            <h1 class="text-3xl font-bold uppercase leading-tight text-white sm:text-5xl">{{ $session->car_model }}</h1>
                            <div class="mt-4 flex gap-6 text-sm font-bold uppercase tracking-wider">
                                <a href="{{ route('sessions.show', $session) }}" class="link-underline">Детальніше ›</a>
                                <a href="{{ route('sessions.show', $session) }}#booking" class="link-underline text-apex-300">Забронювати ›</a>
                            </div>
                        </div>
                        <p class="max-w-md text-slate-300 md:justify-self-end">{{ Str::limit($session->description, 150) }}</p>
                    </div>
                </article>
            @empty
                <div class="card my-16 p-12 text-center text-slate-400">Найближчих заїздів поки немає — загляньте пізніше.</div>
            @endforelse

            @if ($sessions->count() > 1)
                <div class="flex items-center justify-center gap-6 py-8">
                    <button type="button" class="rounded-full p-3 ring-1 ring-white/15 transition hover:ring-apex-400" data-prev aria-label="Попередній">←</button>
                    <div class="flex gap-2" data-dots></div>
                    <button type="button" class="rounded-full p-3 ring-1 ring-white/15 transition hover:ring-apex-400" data-next aria-label="Наступний">→</button>
                </div>
            @endif
        </div>
    </section>

    {{-- Призначення системи --}}
    <section class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
            <div data-reveal="left">
                <p class="chip">Що таке APEX TRACK</p>
                <h2 class="mt-5 text-3xl font-bold sm:text-4xl">Сядьте за кермо машини мрії — на справжньому треку</h2>
            </div>
            <p class="text-lg text-slate-300" data-reveal="right">
                Ми орендуємо суперкари, гіперкари та боліди Формули 1 для трек-днів на автодромах Європи.
                Оберіть авто й дату, забронюйте слот пілота — інструктор, шолом і телеметрія вже чекають у боксах.
            </p>
        </div>

        <div class="mt-16 grid gap-6 md:grid-cols-3">
            @foreach ([
                ['01', 'Оберіть авто', 'Гараж із фільтрами за класом, датою та назвою — від Ferrari Roma до Red Bull RB7.'],
                ['02', 'Забронюйте слот', 'Кількість місць обмежена: система не дасть перевищити ліміт навіть при одночасних запитах.'],
                ['03', 'Виїжджайте на трек', 'Брифінг, серії з інструктором і ваш найкращий час кола в особистому кабінеті.'],
            ] as $i => [$num, $heading, $text])
                <div class="card p-8" data-reveal="up" style="transition-delay: {{ $i * 120 }}ms">
                    <p class="font-display text-4xl font-bold text-apex-500/40">{{ $num }}</p>
                    <h3 class="mt-4 text-lg font-bold">{{ $heading }}</h3>
                    <p class="mt-2 text-slate-400">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Блок актуальних сесій (не менше трьох) --}}
    @if ($sessions->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between gap-4" data-reveal="left">
                <h2 class="text-2xl font-bold sm:text-3xl">Найближчі заїзди</h2>
                <a href="{{ route('sessions.index') }}" class="btn-primary shrink-0">Весь гараж →</a>
            </div>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($sessions->take(6) as $session)
                    <div data-reveal="up" style="transition-delay: {{ ($loop->index % 3) * 120 }}ms">
                        @include('partials.session-card', ['session' => $session])
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
