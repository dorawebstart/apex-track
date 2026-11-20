{{-- Блок бронювання. Усі обмеження повторно перевіряються на сервері (BookingController). --}}
@php
    $isActive = isset($userBooking) && $userBooking?->status === 'active';
    $reason = match (true) {
        $session->status === 'archived' => 'Сесію перенесено в архів — бронювання закрите.',
        $session->status !== 'published' => 'Сесія ще не опублікована.',
        ! $session->starts_at->isFuture() => 'Заїзд уже розпочався.',
        $session->available_seats === 0 => 'Усі місця заброньовано.',
        default => null,
    };
@endphp

@guest
    @if ($reason)
        <p class="font-semibold text-rose-600">{{ $reason }}</p>
    @else
        <a href="{{ route('bookings.start', $session) }}" class="btn-dark w-full sm:w-auto">Забронювати місце →</a>
        <p class="mt-3 text-sm text-ink-600">Спершу потрібно увійти або зареєструватися — після цього повернемо вас сюди.</p>
    @endif
@else
    @if ($isActive)
        <p class="flex items-center gap-2 font-semibold text-apex-700"><span class="size-2 rounded-full bg-apex-600"></span>Ви заброньовані на цей заїзд</p>
        <form method="POST" action="{{ route('bookings.destroy', $userBooking) }}" class="mt-4"
              data-confirm="Скасувати бронювання на «{{ $session->title }}»? Місце стане доступним іншим пілотам.">
            @csrf
            @method('DELETE')
            <button class="btn-ghost text-ink-900">Скасувати бронювання</button>
        </form>
    @elseif ($reason)
        <p class="font-semibold text-rose-600">{{ $reason }}</p>
    @else
        <form method="POST" action="{{ route('bookings.store', $session) }}"
              data-confirm="Забронювати місце на «{{ $session->title }}» ({{ $session->starts_at->translatedFormat('j F, H:i') }})?">
            @csrf
            <button class="btn-dark w-full sm:w-auto">{{ $userBooking ? 'Забронювати знову →' : 'Забронювати місце →' }}</button>
        </form>
    @endif
@endguest
