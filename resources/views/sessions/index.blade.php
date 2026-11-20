<x-layouts.app title="Гараж">
    <section class="mx-auto max-w-7xl px-4 pt-32 sm:px-6 lg:px-8">
        <div data-reveal="left">
            <p class="chip">Гараж APEX</p>
            <h1 class="mt-4 text-3xl font-bold sm:text-5xl">Оберіть авто для заїзду</h1>
            <p class="mt-3 max-w-2xl text-slate-400">Усі опубліковані трек-сесії: суперкари, гран-турізмо, гіперкари та трекові боліди.</p>
        </div>

        <form method="GET" action="{{ route('sessions.index') }}" class="card mt-10 grid gap-4 p-5 md:grid-cols-[2fr_1fr_1fr_1fr_auto] md:items-end">
            <div>
                <label for="q" class="label">Пошук за назвою</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Напр., F40 або Ring" class="input">
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
                <label for="from" class="label">Дата від</label>
                <input id="from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="input [color-scheme:dark]">
            </div>
            <div>
                <label for="to" class="label">Дата до</label>
                <input id="to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="input [color-scheme:dark]">
            </div>
            <div class="flex gap-2">
                <button class="btn-primary">Знайти</button>
                <a href="{{ route('sessions.index') }}" class="btn-ghost" title="Скинути фільтри">Скинути</a>
            </div>
            @if ($errors->any())
                <p class="text-sm text-rose-300 md:col-span-5">{{ $errors->first() }}</p>
            @endif
        </form>

        <p class="mt-8 text-sm text-slate-400">Знайдено сесій: <span class="font-bold text-white">{{ $sessions->total() }}</span></p>

        @if ($sessions->isEmpty())
            <div class="card mt-6 p-12 text-center">
                <p class="text-lg font-bold">Нічого не знайдено</p>
                <p class="mt-2 text-slate-400">Спробуйте змінити запит або скинути фільтри.</p>
            </div>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($sessions as $session)
                    <div data-reveal="up" style="transition-delay: {{ ($loop->index % 3) * 100 }}ms">
                        @include('partials.session-card', ['session' => $session])
                    </div>
                @endforeach
            </div>
            <div class="mt-12">{{ $sessions->links() }}</div>
        @endif
    </section>
</x-layouts.app>
