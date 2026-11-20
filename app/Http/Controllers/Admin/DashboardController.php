<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Category;
use App\Models\TrackSession;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Адмінпанель: навігація та загальна інформація (показники, останні бронювання, найближчі сесії).
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => $this->numbers(),
            'latestBookings' => Booking::with(['user', 'trackSession'])->latest()->take(8)->get(),
            'nextSessions' => TrackSession::upcoming()->withCount('activeBookings')->orderBy('starts_at')->take(5)->get(),
        ]);
    }

    /**
     * Статистика: 4 показники, топ-5 сесій за активними бронюваннями, активні бронювання за класами авто.
     */
    public function stats(): View
    {
        return view('admin.stats', [
            'stats' => $this->numbers(),
            'top' => TrackSession::with('category')->withCount('activeBookings')
                ->orderByDesc('active_bookings_count')->orderBy('starts_at')->take(5)->get(),
            'byCategory' => Category::orderBy('id')->get()->map(fn (Category $c): array => [
                'name' => $c->name,
                'active' => Booking::where('status', 'active')
                    ->whereHas('trackSession', fn ($q) => $q->where('category_id', $c->id))->count(),
            ]),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ]);
    }

    /**
     * @return array{sessions: int, published: int, users: int, activeBookings: int}
     */
    private function numbers(): array
    {
        return [
            'sessions' => TrackSession::count(),
            'published' => TrackSession::published()->count(),
            'users' => User::count(),
            'activeBookings' => Booking::where('status', 'active')->count(),
        ];
    }
}
