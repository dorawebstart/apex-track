<x-admin-layout title="Огляд" heading="Вітаємо, {{ auth()->user()->name }}">
    @include('admin.partials.stat-cards')

    <div class="mt-14 grid gap-16 lg:grid-cols-2">
        <div>
            <div class="flex items-baseline justify-between">
                <h2 class="text-xl font-medium">Найближчі сесії</h2>
                <a href="{{ route('admin.sessions.index') }}" class="link-underline text-sm font-semibold">Усі сесії ›</a>
            </div>
            <ul class="mt-4">
                @forelse ($nextSessions as $s)
                    <li class="rule-row">
                        <div>
                            <a href="{{ route('admin.sessions.bookings', $s) }}" class="font-semibold hover:text-apex-700">{{ $s->title }}</a>
                            <p class="text-sm text-ink-600">{{ $s->starts_at->translatedFormat('j F, H:i') }} · {{ $s->track_name }}</p>
                        </div>
                        <span class="whitespace-nowrap text-sm"><b>{{ $s->active_bookings_count }}</b> / {{ $s->capacity }}</span>
                    </li>
                @empty
                    <li class="py-5 text-ink-600">Опублікованих майбутніх сесій немає.</li>
                @endforelse
            </ul>
        </div>

        <div>
            <h2 class="text-xl font-medium">Останні бронювання</h2>
            <ul class="mt-4">
                @forelse ($latestBookings as $b)
                    <li class="rule-row">
                        <div>
                            <p class="font-semibold">{{ $b->user->name }}</p>
                            <p class="text-sm text-ink-600">{{ $b->trackSession->title }} · {{ $b->created_at->translatedFormat('j F, H:i') }}</p>
                        </div>
                        <span @class(['text-sm font-semibold', 'text-apex-700' => $b->status === 'active', 'text-ink-600' => $b->status !== 'active'])>{{ \App\Models\Booking::STATUSES[$b->status] }}</span>
                    </li>
                @empty
                    <li class="py-5 text-ink-600">Бронювань ще немає.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-admin-layout>
