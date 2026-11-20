<x-admin-layout title="Заїзди" heading="Заїзди">
    <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm font-semibold">
        @foreach (['' => 'Усі'] + \App\Models\TrackSession::STATUSES as $value => $label)
            <a href="{{ route('admin.sessions.index', array_filter(['status' => $value])) }}" @class([
                'pb-1 transition',
                'shadow-[inset_0_-2px_0_#0fa89d]' => request('status', '') === $value,
                'text-ink-600 hover:text-ink-900' => request('status', '') !== $value,
            ])>{{ $label }}</a>
        @endforeach
    </div>

    <div class="mt-8 overflow-x-auto">
        <table class="w-full min-w-[960px] text-left text-sm">
            <thead class="eyebrow text-ink-600">
                <tr class="border-b border-ink-900/15">
                    <th class="py-4 pr-4 font-bold">Заїзд</th>
                    <th class="py-4 pr-4 font-bold">Клас</th>
                    <th class="py-4 pr-4 font-bold">Початок</th>
                    <th class="py-4 pr-4 font-bold">Місця</th>
                    <th class="py-4 pr-4 font-bold">Статус</th>
                    <th class="py-4 text-right font-bold">Дії</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sessions as $s)
                    <tr class="border-b border-ink-900/10 align-middle">
                        <td class="py-4 pr-4">
                            <div class="flex items-center gap-4">
                                <img src="{{ $s->thumb_url }}" alt="" width="80" height="56" loading="lazy" class="h-14 w-20 shrink-0 rounded-xl object-cover">
                                <div>
                                    <p class="font-semibold">{{ $s->title }}</p>
                                    <p class="text-ink-600">{{ $s->car_model }} · {{ $s->track_name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 pr-4">{{ $s->category->name }}</td>
                        <td class="whitespace-nowrap py-4 pr-4">{{ $s->starts_at->translatedFormat('j M Y, H:i') }}</td>
                        <td class="py-4 pr-4"><b>{{ $s->active_bookings_count }}</b> / {{ $s->capacity }}</td>
                        <td class="py-4 pr-4">@include('admin.partials.status-badge', ['status' => $s->status])</td>
                        <td class="py-4">
                            <div class="flex flex-wrap items-center justify-end gap-x-4 gap-y-2 font-semibold">
                                <a href="{{ route('sessions.show', $s) }}" class="hover:text-apex-700">Переглянути</a>
                                <a href="{{ route('admin.sessions.bookings', $s) }}" class="hover:text-apex-700">Бронювання ({{ $s->bookings_count }})</a>
                                <a href="{{ route('admin.sessions.edit', $s) }}" class="hover:text-apex-700">Редагувати</a>
                                @foreach (['published' => 'Опублікувати', 'archived' => 'В архів', 'draft' => 'У чернетку'] as $status => $action)
                                    @continue($s->status === $status || ($status === 'draft' && $s->status !== 'published'))
                                    <form method="POST" action="{{ route('admin.sessions.status', $s) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button class="hover:text-apex-700">{{ $action }}</button>
                                    </form>
                                @endforeach
                                <form method="POST" action="{{ route('admin.sessions.destroy', $s) }}"
                                      data-confirm="{{ $s->bookings_count ? "Заїзд «{$s->title}» має бронювання — його буде перенесено в архів. Продовжити?" : "Видалити заїзд «{$s->title}» назавжди?" }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-rose-600 hover:underline">Видалити</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-12 text-center text-ink-600">Заїздів не знайдено.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-10">{{ $sessions->links() }}</div>
</x-admin-layout>
