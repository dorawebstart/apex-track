<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TrackSession;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Головна: слайдер актуальних сесій (опубліковані, з майбутньою датою).
     */
    public function index(): View
    {
        $sessions = TrackSession::upcoming()
            ->with('category')
            ->withCount('activeBookings')
            ->orderBy('starts_at')
            ->take(8)
            ->get();

        return view('home', [
            'sessions' => $sessions,
            'categories' => Category::whereHas('trackSessions', fn ($q) => $q->upcoming())->orderBy('id')->get(),
        ]);
    }
}
