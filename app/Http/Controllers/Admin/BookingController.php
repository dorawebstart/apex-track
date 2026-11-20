<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\TrackSession;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Бронювання на конкретну сесію: таблиця та експорт у CSV.
 */
class BookingController extends Controller
{
    public function index(TrackSession $trackSession): View
    {
        $trackSession->loadCount('activeBookings');

        return view('admin.sessions.bookings', [
            'session' => $trackSession,
            'bookings' => $trackSession->bookings()->with('user')->latest()->get(),
        ]);
    }

    public function export(TrackSession $trackSession): StreamedResponse
    {
        $bookings = $trackSession->bookings()->with('user')->latest()->get();

        return response()->streamDownload(function () use ($bookings): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM, щоб Excel коректно показав кирилицю
            fputcsv($out, ['Пілот', 'Пошта', 'Подано', 'Статус'], ';');
            foreach ($bookings as $booking) {
                fputcsv($out, [
                    $booking->user->name,
                    $booking->user->email,
                    $booking->created_at->format('d.m.Y H:i'),
                    Booking::STATUSES[$booking->status],
                ], ';');
            }
            fclose($out);
        }, "bookings-session-{$trackSession->id}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
