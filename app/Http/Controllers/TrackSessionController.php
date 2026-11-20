<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TrackSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackSessionController extends Controller
{
    /**
     * Каталог («Гараж»): лише опубліковані сесії; пошук, фільтр класу та дат, пагінація по 9.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $sessions = TrackSession::published()
            ->with('category')
            ->withCount('activeBookings')
            ->when($filters['q'] ?? null, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('starts_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('starts_at', '<=', $to))
            ->orderBy('starts_at')
            ->paginate(9)
            ->withQueryString();

        return view('sessions.index', [
            'sessions' => $sessions,
            'categories' => Category::orderBy('id')->get(),
            'filters' => $filters,
        ]);
    }

    /**
     * Детальна сторінка. Неопубліковані сесії бачить лише адміністратор, іншим — 404.
     */
    public function show(TrackSession $trackSession): View
    {
        abort_if($trackSession->status !== 'published' && ! auth()->user()?->isAdmin(), 404);

        $trackSession->load('category')->loadCount('activeBookings');

        return view('sessions.show', ['session' => $trackSession]);
    }
}
