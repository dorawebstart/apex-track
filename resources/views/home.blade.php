<x-layouts.app>
    @if ($sessions->isNotEmpty())
        @push('head')
            <link rel="preload" as="image" href="{{ $sessions->first()->image_url }}" fetchpriority="high">
        @endpush
    @endif

    {{-- 1. HERO — фото на весь екран, текст поверх фото (як Porsche Motorsport), слайди з плавним переходом --}}
    <section class="relative h-[100svh] min-h-[620px] overflow-hidden bg-night-950" data-slider>
        @forelse ($sessions as $session)
            <article @class(['absolute inset-0 transition-opacity duration-1000', 'is-active' => $loop->first, 'opacity-0 pointer-events-none' => ! $loop->first])
                     data-slide data-category="{{ $session->category->slug }}" @if (! $loop->first) aria-hidden="true" @endif>
                <div class="img-shell absolute inset-0">
                    <img src="{{ $session->image_url }}" alt="{{ $session->car_model }} — {{ $session->title }}"
                         width="1920" height="1080" decoding="async" data-fade
                         @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif
                         class="size-full object-cover">
                </div>
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/40"></div>

                <div class="absolute inset-x-0 bottom-0">
                    <div class="mx-auto max-w-7xl px-4 pb-28 sm:px-6 lg:px-8">
                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/70">{{ $session->category->name }} · {{ $session->starts_at->translatedFormat('j F') }} · {{ $session->track_name }}</p>
                        <h1 class="mt-4 max-w-3xl text-4xl font-medium leading-[1.05] text-white sm:text-6xl lg:text-7xl">{{ $session->car_model }}.<br><span class="text-white/70">{{ $session->title }}</span></h1>
                        <div class="mt-8 flex flex-wrap items-center gap-6 text-sm font-semibold">
                            <a href="{{ route('sessions.show', $session) }}#booking" class="btn-primary">Забронювати заїзд</a>
                            <a href="{{ route('sessions.show', $session) }}" class="link-underline text-white">Детальніше</a>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <p class="flex h-full items-center justify-center text-slate-400">Найближчих заїздів поки немає — загляньте пізніше.</p>
        @endforelse

        {{-- Вкладки класів поверх фото --}}
        <div class="absolute inset-x-0 top-14 z-10">
            <div class="mx-auto flex max-w-7xl justify-end px-4 pt-6 sm:px-6 lg:px-8">
                <div class="flex flex-wrap gap-x-5 gap-y-2 rounded-full bg-black/30 px-5 py-2 backdrop-blur-md" role="tablist" aria-label="Клас авто">
                    <button type="button" class="text-[13px] font-semibold text-white/60 transition hover:text-white aria-selected:text-white" role="tab" aria-selected="true" data-filter="all">Усі</button>
                    @foreach ($categories as $category)
                        <button type="button" class="text-[13px] font-semibold text-white/60 transition hover:text-white aria-selected:text-white" role="tab" aria-selected="false" data-filter="{{ $category->slug }}">{{ $category->name }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        @if ($sessions->count() > 1)
            <div class="absolute inset-x-0 bottom-8 z-10">
                <div class="mx-auto flex max-w-7xl items-center justify-end gap-5 px-4 sm:px-6 lg:px-8">
                    <span class="text-xs font-semibold tabular-nums text-white/70" data-counter-label></span>
                    <div class="flex gap-2" data-dots></div>
                    <button type="button" class="grid size-10 place-items-center rounded-full text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white hover:text-night-950" data-prev aria-label="Попередній">←</button>
                    <button type="button" class="grid size-10 place-items-center rounded-full text-white ring-1 ring-white/30 backdrop-blur transition hover:bg-white hover:text-night-950" data-next aria-label="Наступний">→</button>
                </div>
            </div>
        @endif
    </section>

    {{-- 2. ІДЕЯ — світла секція, великий заголовок + цифри (без рамок) --}}
    <section class="bg-paper-50 text-ink-900">
        <div class="mx-auto max-w-7xl px-4 py-28 sm:px-6 lg:px-8 lg:py-36">
            <div class="grid gap-12 lg:grid-cols-[1.1fr_1fr] lg:gap-24">
                <div data-reveal="left">
                    <p class="eyebrow text-apex-700">Ідея APEX TRACK</p>
                    <h2 class="mt-6 text-4xl font-medium leading-[1.1] sm:text-5xl lg:text-6xl">Машина мрії — не у вітрині, а на треку</h2>
                </div>
                <div class="space-y-6 text-lg leading-relaxed text-ink-600 lg:pt-12" data-reveal="right">
                    <p>Суперкар у місті — це затори, «лежачі поліцейські» й обмеження 50 км/год. Справжній характер такого авто розкривається лише на гоночній трасі: з правильними траєкторіями, гальмуванням з 280 км/год і повною безпекою.</p>
                    <p>APEX TRACK збирає в одному місці авто, автодроми й інструкторів. Ви обираєте машину та дату, бронюєте слот пілота — решту організовуємо ми.</p>
                </div>
            </div>

            <dl class="mt-24 grid grid-cols-2 gap-y-14 border-t border-ink-900/10 pt-14 lg:grid-cols-4">
                @foreach ([
                    [$stats['cars'], 'моделей у гаражі'],
                    [$stats['tracks'], 'автодромів Європи'],
                    [$stats['maxHorsepower'], 'к.с. — найпотужніше авто'],
                    [$stats['upcoming'], 'найближчих заїздів'],
                ] as $i => [$value, $caption])
                    <div data-reveal="up" style="transition-delay: {{ $i * 120 }}ms">
                        <dd class="stat-num" data-count-to="{{ $value }}">{{ $value }}</dd>
                        <dt class="eyebrow mt-4 text-ink-600">{{ $caption }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- 3. КЛАСИ АВТО — чергування «текст ↔ фото» з повітрям --}}
    @if ($highlights->isNotEmpty())
        <section class="bg-paper-100 text-ink-900">
            <div class="mx-auto max-w-7xl space-y-28 px-4 py-28 sm:px-6 lg:space-y-40 lg:px-8 lg:py-36">
                @foreach ($highlights as $session)
                    @php($even = $loop->even)
                    <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-16">
                        <div @class(['lg:col-span-5', 'lg:order-2' => $even]) data-reveal="{{ $even ? 'right' : 'left' }}">
                            <p class="eyebrow text-apex-700">{{ $session->category->name }}</p>
                            <h3 class="mt-5 text-3xl font-medium leading-tight sm:text-4xl">{{ $session->car_model }}</h3>
                            <p class="mt-6 text-lg leading-relaxed text-ink-600">{{ $session->description }}</p>
                            <div class="mt-8 grid grid-cols-3 gap-6 border-t border-ink-900/10 pt-6">
                                <div><p class="text-2xl font-light">{{ $session->top_speed_kmh }}</p><p class="eyebrow mt-1 text-ink-600">км/год</p></div>
                                <div><p class="text-2xl font-light">{{ $session->horsepower }}</p><p class="eyebrow mt-1 text-ink-600">к.с.</p></div>
                                <div><p class="text-2xl font-light">{{ number_format($session->zero_to_100, 1, ',') }}</p><p class="eyebrow mt-1 text-ink-600">0–100, с</p></div>
                            </div>
                            <a href="{{ route('sessions.show', $session) }}" class="btn-dark mt-10">Дивитися заїзд →</a>
                        </div>
                        <div @class(['img-shell overflow-hidden rounded-[2.5rem] lg:col-span-7', 'lg:order-1' => $even]) data-reveal="zoom">
                            <img src="{{ $session->thumb_url }}" alt="{{ $session->car_model }}" width="1280" height="800" loading="lazy" decoding="async" data-fade
                                 class="aspect-[4/3] w-full object-cover lg:aspect-[16/11]">
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 4. ЧОМУ МИ — графітова секція, нумерований список з тонкими лініями --}}
    <section class="bg-graphite-900 text-white">
        <div class="mx-auto grid max-w-7xl gap-16 px-4 py-28 sm:px-6 lg:grid-cols-[1fr_1.4fr] lg:gap-24 lg:px-8 lg:py-36">
            <div class="lg:sticky lg:top-32 lg:h-fit" data-reveal="left">
                <p class="eyebrow text-apex-400">Чому APEX TRACK</p>
                <h2 class="mt-6 text-4xl font-medium leading-[1.1] sm:text-5xl">Не прокат. Трек-день під ключ.</h2>
                <p class="mt-6 text-lg text-slate-400">Звичайна оренда суперкара закінчується на парковці. Наша — на піт-лейні з вашим найкращим часом кола.</p>
            </div>
            <ol class="border-t border-white/10">
                @foreach ([
                    ['Справжні треки, а не дороги загального користування', 'Slovakia Ring, Hungaroring, Red Bull Ring, Брно, Познань і Монца. Жодних камер швидкості — лише апекси та прямі.'],
                    ['Інструктор у кожній серії', 'Досвідчений пілот поруч пояснює траєкторії й точки гальмування. Новачки починають з GT, досвідчені — переходять на гіперкари.'],
                    ['Від Ferrari Roma до болідів F1', 'Чотири класи авто в одному гаражі: можна пройти шлях від комфортного гран-турізмо до Ferrari F2004 Шумахера.'],
                    ['Чесні ліміти місць', 'Кількість пілотів на заїзд обмежена. Система контролює місця в реальному часі й не допустить «овербукінгу» навіть при одночасних заявках.'],
                    ['Усе в особистому кабінеті', 'Бронювання, статус і скасування в один клік. Скасували — місце одразу повертається іншим пілотам.'],
                ] as $i => [$heading, $text])
                    <li class="grid gap-3 border-b border-white/10 py-8 sm:grid-cols-[4rem_1fr]" data-reveal="up">
                        <span class="font-display text-sm text-apex-400">{{ sprintf('%02d', $i + 1) }}</span>
                        <div>
                            <h3 class="text-xl font-medium">{{ $heading }}</h3>
                            <p class="mt-3 leading-relaxed text-slate-400">{{ $text }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- 5. ЯК ЦЕ ПРАЦЮЄ — бірюзово-нічний градієнт, кроки по горизонталі --}}
    <section class="bg-gradient-to-br from-night-900 via-night-950 to-[#06302e] text-white">
        <div class="mx-auto max-w-7xl px-4 py-28 sm:px-6 lg:px-8">
            <p class="eyebrow text-apex-300" data-reveal="left">Як це працює</p>
            <div class="mt-14 grid gap-12 md:grid-cols-4">
                @foreach ([
                    ['Реєстрація', 'Створіть обліковий запис пілота — це хвилина.'],
                    ['Вибір авто', 'Фільтруйте гараж за класом, датою й назвою.'],
                    ['Бронювання', 'Один клік — і слот ваш, якщо є вільні місця.'],
                    ['Трек-день', 'Брифінг, шолом, інструктор — і на трасу.'],
                ] as $i => [$heading, $text])
                    <div class="border-t-2 border-apex-500/60 pt-6" data-reveal="up" style="transition-delay: {{ $i * 140 }}ms">
                        <p class="font-display text-5xl font-light text-white/20">{{ $i + 1 }}</p>
                        <h3 class="mt-4 text-lg font-medium">{{ $heading }}</h3>
                        <p class="mt-2 text-slate-400">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 6. НАЙБЛИЖЧІ ЗАЇЗДИ — світла секція, картки без рамок --}}
    @if ($sessions->isNotEmpty())
        <section class="bg-paper-50 text-ink-900">
            <div class="mx-auto max-w-7xl px-4 py-28 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-6" data-reveal="left">
                    <div>
                        <p class="eyebrow text-apex-700">Розклад</p>
                        <h2 class="mt-4 text-4xl font-medium">Найближчі заїзди</h2>
                    </div>
                    <a href="{{ route('sessions.index') }}" class="link-underline text-sm font-bold uppercase tracking-wider">Весь гараж ›</a>
                </div>
                <div class="mt-14 grid gap-x-8 gap-y-16 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($sessions->take(6) as $session)
                        <div data-reveal="up" style="transition-delay: {{ ($loop->index % 3) * 120 }}ms">
                            @include('partials.session-card', ['session' => $session])
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- 7. ЗАКЛИК — темна смуга --}}
    <section class="bg-night-950">
        <div class="mx-auto flex max-w-7xl flex-col items-start gap-8 px-4 py-24 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8" data-reveal="up">
            <h2 class="max-w-2xl text-3xl font-medium leading-tight sm:text-4xl">Готові відчути, на що здатна машина насправді?</h2>
            <a href="{{ route('sessions.index') }}" class="btn-primary shrink-0">Обрати авто →</a>
        </div>
    </section>
</x-layouts.app>
