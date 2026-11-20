@props(['title', 'heading' => null])

{{-- Каркас адмінпанелі: темна смуга із заголовком і навігацією, світлий робочий простір --}}
<x-layouts.app :title="$title.' · Адмінпанель'">
    <section class="bg-night-950 pt-14">
        <div class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <p class="eyebrow text-apex-300">Адмінпанель</p>
                    <h1 class="mt-3 text-3xl font-medium sm:text-4xl">{{ $heading ?? $title }}</h1>
                </div>
                <a href="{{ route('admin.sessions.create') }}" class="btn-primary !py-2.5">+ Нова сесія</a>
            </div>
            <nav class="mt-8 flex gap-7 overflow-x-auto text-sm font-semibold" aria-label="Розділи адмінпанелі">
                @foreach ([
                    ['admin.dashboard', 'Огляд', 'admin.dashboard'],
                    ['admin.sessions.index', 'Сесії', 'admin.sessions.*'],
                    ['admin.stats', 'Статистика', 'admin.stats'],
                ] as [$route, $label, $pattern])
                    <a href="{{ route($route) }}" @class([
                        'whitespace-nowrap pb-3 transition',
                        'text-white shadow-[inset_0_-2px_0_#19d3c5]' => request()->routeIs($pattern),
                        'text-white/50 hover:text-white' => ! request()->routeIs($pattern),
                    ])>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </section>

    <section class="bg-paper-50 text-ink-900">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            {{ $slot }}
        </div>
    </section>
</x-layouts.app>
