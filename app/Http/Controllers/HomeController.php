<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TrackSession;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Головна: слайдер актуальних сесій (опубліковані, з майбутньою датою), ключові цифри сервісу
     * та по одній сесії кожного класу для блоків «текст + фото».
     */
    public function index(): View
    {
        $sessions = TrackSession::upcoming()
            ->with('category')
            ->withCount('activeBookings')
            ->orderBy('starts_at')
            ->take(8)
            ->get();

        $published = TrackSession::published();

        return view('home', [
            'sessions' => $sessions,
            'categories' => Category::whereHas('trackSessions', fn ($q) => $q->upcoming())->orderBy('id')->get(),
            'highlights' => $sessions->unique('category_id')->take(3)->values(),
            'stats' => [
                'cars' => (clone $published)->distinct()->count('car_model'),
                'tracks' => (clone $published)->distinct()->count('track_name'),
                'maxHorsepower' => (int) (clone $published)->max('horsepower'),
                'upcoming' => TrackSession::upcoming()->count(),
            ],
        ]);
    }
}
