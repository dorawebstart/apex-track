<x-layouts.app title="Гараж">
    {{-- Темна шапка з фільтрами --}}
    <section class="bg-[radial-gradient(ellipse_at_80%_0%,_#1a2550_0%,_#070b1a_60%)]">
        <div class="mx-auto max-w-7xl px-4 pb-16 pt-36 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2 lg:items-end" data-reveal="left">
                <div>
                    <p class="eyebrow text-apex-300">Гараж APEX</p>
                    <h1 class="mt-5 text-4xl font-medium leading-tight sm:text-6xl">Оберіть авто для заїзду</h1>
                </div>
                <p class="max-w-lg text-lg text-slate-400 lg:justify-self-end">Усі опубліковані трек-сесії: суперкари, гран-турізмо, гіперкари та трекові боліди на автодромах Європи.</p>
            </div>

            <form method="GET" action="{{ route('sessions.index') }}" class="mt-14 grid gap-6 border-t border-white/10 pt-8 md:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_1fr_1fr_auto] md:items-end">
                <div class="relative" data-suggest data-suggest-url="{{ route('sessions.suggest') }}">
                    <label for="q" class="label">Пошук за назвою</label>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Напр., F40 або Ring" class="input"
                           autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="q-suggestions">
                    <ul id="q-suggestions" role="listbox" hidden
                        class="absolute inset-x-0 top-full z-30 mt-2 overflow-hidden rounded-2xl bg-white py-2 text-ink-900 shadow-2xl shadow-black/40"></ul>
                </div>
                <div>
                    <label for="category" class="label">Клас авто</label>
                    <select id="category" name="category" class="input">
                        <option value="">Усі класи</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="brand" class="label">Марка</label>
                    <select id="brand" name="brand" class="input">
                        <option value="">Усі марки</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand }}" @selected(($filters['brand'] ?? '') === $brand)>{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="from" class="label">Дата від</label>
                    <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="input [color-scheme:dark]">
                </div>
                <div>
                    <label for="to" class="label">Дата до</label>
                    <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="input [color-scheme:dark]">
                </div>
                <div class="flex gap-3">
                    <button class="btn-primary">Знайти</button>
                    <a href="{{ route('sessions.index') }}" class="btn-ghost text-white">Скинути</a>
                </div>
                @if ($errors->any())
                    <p class="text-sm text-rose-300 md:col-span-2 lg:col-span-6">{{ $errors->first() }}</p>
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
