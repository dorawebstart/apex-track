@props(['title', 'heading', 'lead'])

{{-- Розділений екран: темна сцена з ідеєю ліворуч, світла форма праворуч --}}
<x-layouts.app :title="$title">
    <section class="grid min-h-screen lg:grid-cols-2">
        <div class="relative hidden overflow-hidden bg-[radial-gradient(ellipse_at_30%_30%,_#1a2550_0%,_#070b1a_70%)] lg:block">
            <div class="flex h-full flex-col justify-end px-12 pb-20 pt-32 xl:px-20">
                <p class="eyebrow text-apex-300">APEX TRACK</p>
                <p class="mt-6 max-w-md font-display text-4xl font-medium leading-tight text-white">Ваш слот пілота чекає в боксах.</p>
                <ul class="mt-10 max-w-md border-t border-white/10 text-slate-400">
                    <li class="border-b border-white/10 py-4">Бронювання в один клік</li>
                    <li class="border-b border-white/10 py-4">Усі заїзди — в особистому кабінеті</li>
                    <li class="border-b border-white/10 py-4">Скасування без дзвінків</li>
                </ul>
            </div>
        </div>

        <div class="flex items-center bg-paper-50 px-4 pb-16 pt-32 text-ink-900 sm:px-10 xl:px-20">
            <div class="mx-auto w-full max-w-md" data-reveal="up">
                <h1 class="text-4xl font-medium">{{ $heading }}</h1>
                <p class="mt-3 text-ink-600">{{ $lead }}</p>
                <div class="mt-10">{{ $slot }}</div>
            </div>
        </div>
    </section>
</x-layouts.app>
