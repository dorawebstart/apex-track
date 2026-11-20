<x-admin-layout title="Нова сесія">
    <form method="POST" action="{{ route('admin.sessions.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
        @include('admin.sessions.form')
    </form>
</x-admin-layout>
