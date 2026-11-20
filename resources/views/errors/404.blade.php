<x-layouts.app title="Сторінку не знайдено">
    <section class="mx-auto flex min-h-[70vh] max-w-3xl flex-col items-center justify-center px-4 pt-24 text-center">
        <p class="font-display text-8xl font-bold text-apex-500/30">404</p>
        <h1 class="mt-4 text-3xl font-bold">Сход з траси</h1>
        <p class="mt-3 text-slate-400">Такої сторінки або заїзду не існує. Можливо, його вже приховано.</p>
        <a href="{{ route('sessions.index') }}" class="btn-primary mt-8">Повернутися в гараж</a>
    </section>
</x-layouts.app>
