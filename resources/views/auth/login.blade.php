<x-auth-layout title="Вхід" heading="Вхід" lead="Увійдіть, щоб бронювати заїзди та бачити свої бронювання.">
    <form method="POST" action="{{ route('login') }}" class="space-y-6" novalidate>
        @csrf
        <x-field name="email" label="Електронна пошта" type="email" autocomplete="email" required autofocus />
        <x-field name="password" label="Пароль" type="password" autocomplete="current-password" required />
        <label class="flex items-center gap-3 text-sm text-ink-600">
            <input type="checkbox" name="remember" class="size-4 rounded accent-apex-600"> Запам'ятати мене
        </label>
        <button class="btn-dark w-full">Увійти</button>
    </form>
    <p class="mt-8 text-sm text-ink-600">Ще немає облікового запису? <a href="{{ route('register') }}" class="link-underline font-bold text-ink-900">Зареєструватися</a></p>
</x-auth-layout>
