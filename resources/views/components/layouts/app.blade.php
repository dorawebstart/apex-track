<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' — ' : '' }}{{ config('app.name') }}</title>
    <meta name="description" content="APEX TRACK — оренда спортивних автомобілів і болідів для заїздів на гоночних треках.">
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">
    <header class="fixed inset-x-0 top-0 z-50 bg-night-950/80 backdrop-blur-lg" data-header>
        <div class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="text-sm font-bold tracking-[0.4em] text-white">
                APEX<span class="text-apex-400">·</span>TRACK
            </a>

            <nav class="hidden items-center gap-7 text-[13px] font-semibold md:flex" aria-label="Головне меню">
                <a href="{{ route('home') }}" @class(['link-underline', 'text-apex-300' => request()->routeIs('home')])>Головна</a>
                <a href="{{ route('sessions.index') }}" @class(['link-underline', 'text-apex-300' => request()->routeIs('sessions.*')])>Гараж</a>
                @auth
                    @if (Route::has('cabinet'))
                        <a href="{{ route('cabinet') }}" @class(['link-underline', 'text-apex-300' => request()->routeIs('cabinet')])>Кабінет</a>
                    @endif
                    @if (auth()->user()->isAdmin() && Route::has('admin.dashboard'))
                        <a href="{{ route('admin.dashboard') }}" @class(['link-underline', 'text-apex-300' => request()->routeIs('admin.*')])>Адмінпанель</a>
                    @endif
                @endauth
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                @auth
                    <span class="text-[13px] text-slate-300">{{ auth()->user()->name }}</span>
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn-ghost !px-4 !py-1.5 !text-xs">Вийти</button>
                        </form>
                    @endif
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn-ghost !px-4 !py-1.5 !text-xs">Вхід</a>
                        <a href="{{ route('register') }}" class="btn-primary !px-4 !py-1.5 !text-xs">Реєстрація</a>
                    @endif
                @endauth
            </div>

            <button type="button" class="rounded-xl p-1.5 text-white ring-1 ring-white/15 md:hidden" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" aria-label="Відкрити меню">
                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/></svg>
            </button>
        </div>

        <div id="mobile-menu" class="mx-4 hidden rounded-3xl bg-night-900/95 p-6 ring-1 ring-white/10 backdrop-blur md:hidden" data-menu>
            <nav class="flex flex-col gap-4 font-semibold">
                <a href="{{ route('home') }}">Головна</a>
                <a href="{{ route('sessions.index') }}">Гараж</a>
                @auth
                    @if (Route::has('cabinet')) <a href="{{ route('cabinet') }}">Кабінет</a> @endif
                    @if (auth()->user()->isAdmin() && Route::has('admin.dashboard')) <a href="{{ route('admin.dashboard') }}">Адмінпанель</a> @endif
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-ghost w-full">Вийти</button></form>
                    @endif
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn-ghost">Вхід</a>
                        <a href="{{ route('register') }}" class="btn-primary">Реєстрація</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    @if (session('success') || session('error'))
        <div class="fixed inset-x-0 top-16 z-40 mx-auto w-full max-w-xl px-4" data-flash>
            <div @class([
                'flex items-start gap-3 rounded-2xl px-5 py-4 text-sm font-semibold shadow-2xl ring-1 backdrop-blur',
                'bg-apex-500/15 text-apex-300 ring-apex-400/40' => session('success'),
                'bg-rose-500/15 text-rose-200 ring-rose-400/40' => session('error'),
            ]) role="status">
                <span class="grow">{{ session('success') ?? session('error') }}</span>
                <button type="button" class="opacity-70 hover:opacity-100" data-flash-close aria-label="Закрити">✕</button>
            </div>
        </div>
    @endif

    <main class="grow">
        {{ $slot }}
    </main>

    <footer class="border-t border-white/5 bg-night-950">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 text-sm text-slate-400 sm:px-6 md:grid-cols-3 lg:px-8">
            <div>
                <p class="font-display text-base font-bold tracking-[0.35em] text-white">APEX<span class="text-apex-400">·</span>TRACK</p>
                <p class="mt-3 max-w-xs">Оренда суперкарів, гіперкарів і болідів Формули 1 для заїздів на гоночних треках Європи.</p>
            </div>
            <div class="flex flex-col gap-2">
                <a href="{{ route('sessions.index') }}" class="hover:text-apex-300">Гараж і розклад</a>
                <a href="{{ route('sessions.index', ['category' => 'track-only']) }}" class="hover:text-apex-300">Трекові авто та боліди</a>
            </div>
            <p class="md:text-right">Курсова робота · група 54 · {{ now()->year }}<br>Фото авто: Wikimedia Commons (CC BY-SA)</p>
        </div>
    </footer>
</body>
</html>
