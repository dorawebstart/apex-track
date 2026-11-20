<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TrackSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackSessionController extends Controller
{
    /**
     * Каталог («Гараж»): лише опубліковані сесії; пошук, фільтри класу, марки й дат, пагінація по 9.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'brand' => ['nullable', 'string', 'max:50'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ], [
            'to.after_or_equal' => 'Дата «до» має бути не раніше дати «від».',
        ]);

        $sessions = TrackSession::published()
            ->with('category')
            ->withCount('activeBookings')
            ->when($filters['q'] ?? null, fn (Builder $q, string $search) => $this->search($q, $search))
            ->when($filters['category'] ?? null, fn (Builder $q, string $slug) => $q->whereHas('category', fn (Builder $c) => $c->where('slug', $slug)))
            ->when($filters['brand'] ?? null, fn (Builder $q, string $brand) => $q->where('brand', $brand))
            ->when($filters['from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('starts_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('starts_at', '<=', $to))
            ->orderBy('starts_at')
            ->paginate(9)
            ->withQueryString();

        return view('sessions.index', [
            'sessions' => $sessions,
            'categories' => Category::orderBy('id')->get(),
            'brands' => TrackSession::published()->distinct()->orderBy('brand')->pluck('brand'),
            'filters' => $filters,
        ]);
    }

    /**
     * Підказки для поля пошуку (JSON): до 6 опублікованих сесій, схожих за назвою або моделлю.
     */
    public function suggest(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $items = $this->search(TrackSession::published(), $search)
            ->orderBy('starts_at')
            ->limit(6)
            ->get(['id', 'title', 'car_model', 'track_name', 'starts_at'])
            ->map(fn (TrackSession $s): array => [
                'title' => $s->title,
                'subtitle' => $s->car_model.' · '.$s->track_name.' · '.$s->starts_at->translatedFormat('j F'),
                'url' => route('sessions.show', $s),
            ]);

        return response()->json($items);
    }

    /**
     * Детальна сторінка. Неопубліковані сесії бачить лише адміністратор, іншим — 404.
     */
    public function show(TrackSession $trackSession): View
    {
        abort_if($trackSession->status !== 'published' && ! auth()->user()?->isAdmin(), 404);

        $trackSession->load('category')->loadCount('activeBookings');

        $userBooking = auth()->user()?->bookings()->where('track_session_id', $trackSession->id)->first();

        return view('sessions.show', ['session' => $trackSession, 'userBooking' => $userBooking]);
    }

    /**
     * Пошук за назвою сесії або моделлю авто.
     *
     * @param  Builder<TrackSession>  $query
     * @return Builder<TrackSession>
     */
    private function search(Builder $query, string $search): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('title', 'like', "%{$search}%")
            ->orWhere('car_model', 'like', "%{$search}%"));
    }
}
