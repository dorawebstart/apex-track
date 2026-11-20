<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CabinetController extends Controller
{
    /**
     * Особистий кабінет: дані користувача та лише ВЛАСНІ бронювання (через зв'язок User::bookings).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('cabinet', [
            'user' => $user,
            'bookings' => $user->bookings()->with('trackSession.category')->latest()->get(),
        ]);
    }
}
