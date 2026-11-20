<x-layouts.app title="Гараж">
    {{-- Темна шапка з фільтрами --}}
    <section class="bg-[radial-gradient(ellipse_at_80%_0%,_#1a2550_0%,_#070b1a_60%)]">
        <div class="mx-auto max-w-7xl px-4 pb-14 pt-32 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2 lg:items-end" data-reveal="left">
                <div>
                    <p class="eyebrow text-apex-300">Гараж APEX</p>
                    <h1 class="mt-5 text-4xl font-medium leading-tight sm:text-6xl">Оберіть авто для заїзду</h1>
                </div>
                <p class="max-w-lg text-lg text-slate-400 lg:justify-self-end">Усі опубліковані трек-сесії: суперкари, гран-турізмо, гіперкари та трекові боліди на автодромах Європи.</p>
            </div>

            {{-- Фільтри застосовуються одразу після зміни (data-autosubmit); Enter у пошуку — теж --}}
            <form method="GET" action="{{ route('sessions.index') }}" class="mt-12" data-autosubmit>
                <div class="relative" data-suggest data-suggest-url="{{ route('sessions.suggest') }}">
                    <label for="q" class="sr-only">Пошук за назвою або моделлю</label>
                    <svg class="pointer-events-none absolute left-6 top-1/2 size-5 -translate-y-1/2 text-white/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5" stroke-linecap="round"/></svg>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Пошук: Ferrari, F40, Ring…"
                           class="w-full rounded-full border-0 bg-white/[0.06] py-5 pl-16 pr-6 text-lg text-white ring-1 ring-white/10 placeholder:text-white/40 focus:bg-white/[0.09] focus:ring-2 focus:ring-apex-500 focus:outline-none"
                           autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="q-suggestions">
                    <div id="q-suggestions" role="listbox" hidden
                         class="absolute inset-x-0 top-full z-30 mt-3 max-h-[60vh] overflow-y-auto rounded-3xl bg-paper-50 p-3 text-ink-900 shadow-2xl shadow-black/50"></div>
                </div>

                <div class="mt-8 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <fieldset class="flex flex-wrap gap-x-6 gap-y-3">
                        <legend class="sr-only">Клас авто</legend>
                        @foreach (collect([['', 'Усі класи']])->concat($categories->map(fn ($c) => [$c->slug, $c->name])) as [$slug, $name])
                            <label class="cursor-pointer">
                                <input type="radio" name="category" value="{{ $slug }}" class="peer sr-only" @checked(($filters['category'] ?? '') === $slug)>
                                <span class="block pb-1 text-sm font-semibold text-white/50 transition hover:text-white peer-checked:text-white peer-checked:shadow-[inset_0_-2px_0_#19d3c5] peer-focus-visible:outline-2 peer-focus-visible:outline-apex-400">{{ $name }}</span>
                            </label>
                        @endforeach
                    </fieldset>

                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <label for="brand" class="sr-only">Марка</label>
                        <select id="brand" name="brand" class="rounded-full border-0 bg-white/[0.06] py-2.5 pl-4 pr-10 text-white ring-1 ring-white/10 focus:ring-2 focus:ring-apex-500 [&>option]:text-ink-900">
                            <option value="">Усі марки</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand }}" @selected(($filters['brand'] ?? '') === $brand)>{{ $brand }}</option>
                            @endforeach
                        </select>
                        <div class="flex items-center gap-2 rounded-full bg-white/[0.06] py-1 pl-4 pr-1 ring-1 ring-white/10">
                            <span class="text-white/50">Період</span>
                            <label for="from" class="sr-only">Дата від</label>
                            <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="rounded-full border-0 bg-transparent px-2 py-1.5 text-white [color-scheme:dark] focus:ring-2 focus:ring-apex-500">
                            <span class="text-white/40">—</span>
                            <label for="to" class="sr-only">Дата до</label>
                            <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="rounded-full border-0 bg-transparent px-2 py-1.5 text-white [color-scheme:dark] focus:ring-2 focus:ring-apex-500">
                        </div>
                        @if (array_filter($filters))
                            <a href="{{ route('sessions.index') }}" class="link-underline px-2 font-semibold text-white">Скинути фільтри</a>
                        @endif
                        <noscript><button class="btn-primary !py-2">Знайти</button></noscript>
                    </div>
                </div>
                @if ($errors->any())
                    <p class="mt-4 text-sm text-rose-300">{{ $errors->first() }}</p>
                @endif
            </form>
        </div>
    </section>

    {{-- Світла сітка результатів --}}
    <section class="bg-paper-50 text-ink-900">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <p class="eyebrow text-ink-600">Знайдено сесій: <span class="text-ink-900">{{ $sessions->total() }}</span></p>

            @if ($sessions->isEmpty())
                <div class="py-24 text-center">
                    <p class="font-display text-3xl font-medium">Нічого не знайдено</p>
                    <p class="mt-3 text-ink-600">Спробуйте змінити запит або скинути фільтри.</p>
                    <a href="{{ route('sessions.index') }}" class="btn-dark mt-8">Скинути фільтри</a>
                </div>
            @else
                <div class="mt-10 grid gap-x-8 gap-y-16 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($sessions as $session)
                        <div data-reveal="up" style="transition-delay: {{ ($loop->index % 3) * 100 }}ms">
                            @include('partials.session-card', ['session' => $session])
                        </div>
                    @endforeach
                </div>
                <div class="mt-20">{{ $sessions->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.app>
