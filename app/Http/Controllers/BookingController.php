<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TrackSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    /**
     * GET-вхід для кнопки «Забронювати» у гостя: middleware auth відправляє на вхід,
     * а після входу користувач повертається сюди й далі — до блоку бронювання сесії.
     */
    public function start(TrackSession $trackSession): RedirectResponse
    {
        return redirect()->to(route('sessions.show', $trackSession).'#booking')
            ->with('success', 'Ви в системі — тепер підтвердіть бронювання.');
    }

    /**
     * Подання бронювання. Перевірка й запис — в одній транзакції з блокуванням рядка сесії
     * (SELECT ... FOR UPDATE), тож одночасні запити не перевищать місткість.
     */
    public function store(Request $request, TrackSession $trackSession): RedirectResponse
    {
        $user = $request->user();

        $error = DB::transaction(function () use ($user, $trackSession): ?string {
            $session = TrackSession::whereKey($trackSession->id)->lockForUpdate()->firstOrFail();

            if ($session->status !== 'published') {
                return 'Цей заїзд не приймає бронювання.';
            }
            if (! $session->starts_at->isFuture()) {
                return 'Заїзд уже розпочався — бронювання закрите.';
            }

            $booking = Booking::firstOrNew(['user_id' => $user->id, 'track_session_id' => $session->id]);
            if ($booking->exists && $booking->status === 'active') {
                return 'У вас уже є активне бронювання на цей заїзд.';
            }
            if ($session->activeBookings()->count() >= $session->capacity) {
                return 'На жаль, вільних місць не залишилось.';
            }

            // Нова заявка або повторна активація скасованої (пара user+session унікальна)
            $booking->status = 'active';
            $booking->save();

            return null;
        });

        return redirect()->to(route('sessions.show', $trackSession).'#booking')
            ->with($error ? 'error' : 'success', $error ?? 'Місце заброньовано! Деталі — в особистому кабінеті.');
    }

    /**
     * Скасування власного активного бронювання — звільняє одне місце.
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        Gate::authorize('cancel', $booking);

        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Бронювання скасовано, місце звільнено.');
    }
}
