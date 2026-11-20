<x-auth-layout title="Реєстрація" heading="Реєстрація пілота" lead="Створіть обліковий запис — це займе хвилину.">
    <form method="POST" action="{{ route('register') }}" class="space-y-6" novalidate>
        @csrf
        <x-field name="name" label="Ім'я" autocomplete="name" required autofocus />
        <x-field name="email" label="Електронна пошта" type="email" autocomplete="email" required />
        <x-field name="password" label="Пароль (мінімум 8 символів)" type="password" autocomplete="new-password" required />
        <x-field name="password_confirmation" label="Повторіть пароль" type="password" autocomplete="new-password" required />
        <button class="btn-dark w-full">Створити обліковий запис</button>
    </form>
    <p class="mt-8 text-sm text-ink-600">Уже є обліковий запис? <a href="{{ route('login') }}" class="link-underline font-bold text-ink-900">Увійти</a></p>
</x-auth-layout>
