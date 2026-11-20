<x-admin-layout title="Бронювання заїзду" heading="Бронювання: {{ $session->title }}">
    <div class="flex flex-wrap items-end justify-between gap-6">
        <dl class="flex flex-wrap gap-x-12 gap-y-4">
            <div><dd class="text-3xl font-light">{{ $session->active_bookings_count }} / {{ $session->capacity }}</dd><dt class="eyebrow mt-1 text-ink-600">активних / місць</dt></div>
            <div><dd class="text-3xl font-light">{{ $session->available_seats }}</dd><dt class="eyebrow mt-1 text-ink-600">вільно</dt></div>
            <div><dd class="pt-2">@include('admin.partials.status-badge', ['status' => $session->status])</dd><dt class="eyebrow mt-1 text-ink-600">{{ $session->starts_at->translatedFormat('j F Y, H:i') }}</dt></div>
        </dl>
        <div class="flex gap-3">
            <a href="{{ route('admin.sessions.edit', $session) }}" class="btn border border-ink-900/20 hover:border-ink-900">Редагувати заїзд</a>
            @if ($bookings->isNotEmpty())
                <a href="{{ route('admin.sessions.bookings.export', $session) }}" class="btn-dark">Експорт CSV</a>
            @endif
        </div>
    </div>

    @if ($bookings->isEmpty())
        <p class="mt-12 border-t border-ink-900/10 pt-10 text-ink-600">На цей заїзд ще ніхто не забронював місце.</p>
    @else
        <div class="mt-10 overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="eyebrow text-ink-600">
                    <tr class="border-b border-ink-900/15">
                        <th class="py-4 pr-4 font-bold">#</th>
                        <th class="py-4 pr-4 font-bold">Пілот</th>
                        <th class="py-4 pr-4 font-bold">Пошта</th>
                        <th class="py-4 pr-4 font-bold">Подано</th>
                        <th class="py-4 font-bold">Статус</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        <tr class="border-b border-ink-900/10">
                            <td class="py-4 pr-4 text-ink-600">{{ $loop->iteration }}</td>
                            <td class="py-4 pr-4 font-semibold">{{ $booking->user->name }}</td>
                            <td class="py-4 pr-4">{{ $booking->user->email }}</td>
                            <td class="py-4 pr-4">{{ $booking->created_at->translatedFormat('j F Y, H:i') }}</td>
                            <td @class(['py-4 font-semibold', 'text-apex-700' => $booking->status === 'active', 'text-ink-600' => $booking->status !== 'active'])>{{ \App\Models\Booking::STATUSES[$booking->status] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin-layout>
