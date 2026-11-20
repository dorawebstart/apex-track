<x-admin-layout title="Редагування" heading="Редагування: {{ $session->title }}">
    <form method="POST" action="{{ route('admin.sessions.update', $session) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        @include('admin.sessions.form')
    </form>
</x-admin-layout>
