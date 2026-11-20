<x-admin-layout title="Статистика">
    @include('admin.partials.stat-cards')

    <div class="mt-14 grid gap-16 lg:grid-cols-[1.3fr_1fr]">
        <div>
            <h2 class="text-xl font-medium">Топ-5 заїздів за активними бронюваннями</h2>
            <table class="mt-4 w-full text-left text-sm">
                <thead class="eyebrow text-ink-600">
                    <tr class="border-b border-ink-900/15">
                        <th class="py-3 pr-4 font-bold">#</th>
                        <th class="py-3 pr-4 font-bold">Заїзд</th>
                        <th class="py-3 pr-4 font-bold">Клас</th>
                        <th class="py-3 text-right font-bold">Активні / місць</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($top as $s)
                        <tr class="border-b border-ink-900/10">
                            <td class="py-4 pr-4 text-ink-600">{{ $loop->iteration }}</td>
                            <td class="py-4 pr-4"><a href="{{ route('admin.sessions.bookings', $s) }}" class="font-semibold hover:text-apex-700">{{ $s->title }}</a></td>
                            <td class="py-4 pr-4">{{ $s->category->name }}</td>
                            <td class="py-4 text-right"><b>{{ $s->active_bookings_count }}</b> / {{ $s->capacity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-6 text-sm text-ink-600">Скасованих бронювань за весь час: <b class="text-ink-900">{{ $cancelled }}</b></p>
        </div>

        <div>
            <h2 class="text-xl font-medium">Активні бронювання за класами авто</h2>
            {{-- Графік (Chart.js); таблиця нижче — доступна альтернатива й запасний варіант без JS --}}
            <div class="mt-6 h-72"><canvas data-chart='@json($byCategory)' aria-label="Діаграма активних бронювань за класами авто" role="img"></canvas></div>
            <table class="mt-6 w-full text-sm">
                @foreach ($byCategory as $row)
                    <tr class="border-b border-ink-900/10"><td class="py-2">{{ $row['name'] }}</td><td class="py-2 text-right font-semibold">{{ $row['active'] }}</td></tr>
                @endforeach
            </table>
        </div>
    </div>
</x-admin-layout>
