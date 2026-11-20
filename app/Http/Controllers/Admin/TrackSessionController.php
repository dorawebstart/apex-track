<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackSessionRequest;
use App\Http\Requests\UpdateTrackSessionRequest;
use App\Models\Category;
use App\Models\TrackSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Адміністративний CRUD трек-сесій. Доступ — лише через middleware auth + admin (routes/web.php).
 */
class TrackSessionController extends Controller
{
    public function index(Request $request): View
    {
        $sessions = TrackSession::with('category')
            ->withCount(['activeBookings', 'bookings'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sessions.index', ['sessions' => $sessions]);
    }

    public function create(): View
    {
        return view('admin.sessions.create', [
            'session' => new TrackSession(['status' => 'draft', 'capacity' => 8]),
            'categories' => Category::orderBy('id')->get(),
        ]);
    }

    public function store(StoreTrackSessionRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $data['image_path'] = TrackSession::storeImage($file->getRealPath(), $file->extension());
        }

        $session = TrackSession::create($data);

        return redirect()->route('admin.sessions.index')->with('success', "Сесію «{$session->title}» створено.");
    }

    public function edit(TrackSession $trackSession): View
    {
        return view('admin.sessions.edit', [
            'session' => $trackSession->loadCount('activeBookings'),
            'categories' => Category::orderBy('id')->get(),
        ]);
    }

    public function update(UpdateTrackSessionRequest $request, TrackSession $trackSession): RedirectResponse
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $old = $trackSession->image_path;
            $data['image_path'] = TrackSession::storeImage($file->getRealPath(), $file->extension());
            TrackSession::deleteImage($old);
        }

        $trackSession->update($data);

        return redirect()->route('admin.sessions.index')->with('success', "Зміни в «{$trackSession->title}» збережено.");
    }

    /**
     * Сесію з бронюваннями фізично не видаляємо — переводимо в архів.
     */
    public function destroy(TrackSession $trackSession): RedirectResponse
    {
        if ($trackSession->bookings()->exists()) {
            $trackSession->update(['status' => 'archived']);

            return back()->with('error', "«{$trackSession->title}» має бронювання, тому її перенесено в архів замість видалення.");
        }

        TrackSession::deleteImage($trackSession->image_path);
        $trackSession->delete();

        return back()->with('success', "Сесію «{$trackSession->title}» видалено.");
    }

    /**
     * Керування статусом публікації: чернетка / опубліковано / архів.
     */
    public function updateStatus(Request $request, TrackSession $trackSession): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(TrackSession::STATUSES))]]);

        $trackSession->update($data);

        return back()->with('success', "Статус «{$trackSession->title}»: ".TrackSession::STATUSES[$data['status']].'.');
    }
}
