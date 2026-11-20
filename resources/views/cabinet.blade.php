<x-layouts.app title="Особистий кабінет">
    <section class="bg-[radial-gradient(ellipse_at_80%_0%,_#1a2550_0%,_#070b1a_60%)]">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 pb-16 pt-36 sm:px-6 lg:grid-cols-2 lg:items-end lg:px-8">
            <div>
                <p class="eyebrow text-apex-300">Особистий кабінет</p>
                <h1 class="mt-5 text-4xl font-medium sm:text-5xl">{{ $user->name }}</h1>
            </div>
            <dl class="grid grid-cols-3 gap-6 text-white lg:justify-self-end">
                <div><dd class="font-display text-4xl font-light">{{ $bookings->where('status', 'active')->count() }}</dd><dt class="eyebrow mt-2 text-slate-400">активних</dt></div>
                <div><dd class="font-display text-4xl font-light">{{ $bookings->where('status', 'cancelled')->count() }}</dd><dt class="eyebrow mt-2 text-slate-400">скасованих</dt></div>
                <div><dd class="font-display text-4xl font-light">{{ $bookings->count() }}</dd><dt class="eyebrow mt-2 text-slate-400">усього</dt></div>
            </dl>
        </div>
    </section>

    <section class="bg-paper-50 text-ink-900">
        <div class="mx-auto grid max-w-7xl gap-16 px-4 py-20 sm:px-6 lg:grid-cols-[1fr_2.4fr] lg:px-8">
            <div>
                <p class="eyebrow text-ink-600">Обліковий запис</p>
                <dl class="mt-2">
                    <div class="rule-row"><dt class="text-ink-600">Ім'я</dt><dd class="font-semibold">{{ $user->name }}</dd></div>
                    <div class="rule-row"><dt class="text-ink-600">Пошта</dt><dd class="break-all font-semibold">{{ $user->email }}</dd></div>
                    <div class="rule-row"><dt class="text-ink-600">Роль</dt><dd class="font-semibold">{{ $user->isAdmin() ? 'Адміністратор' : 'Пілот' }}</dd></div>
                    <div class="rule-row"><dt class="text-ink-600">З нами з</dt><dd class="font-semibold">{{ $user->created_at->translatedFormat('j F Y') }}</dd></div>
                </dl>
            </div>

            <div>
                <p class="eyebrow text-ink-600">Мої бронювання</p>
                @if ($bookings->isEmpty())
                    <div class="mt-8 border-t border-ink-900/10 pt-10">
                        <p class="font-display text-2xl font-medium">Бронювань поки немає</p>
                        <p class="mt-2 text-ink-600">Оберіть авто в гаражі та забронюйте перший заїзд.</p>
                        <a href="{{ route('sessions.index') }}" class="btn-dark mt-6">До гаража →</a>
                    </div>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[640px] text-left">
                            <thead class="eyebrow text-ink-600">
                                <tr class="border-b border-ink-900/15">
                                    <th class="py-4 pr-4 font-bold">Заїзд</th>
                                    <th class="py-4 pr-4 font-bold">Дата заїзду</th>
                                    <th class="py-4 pr-4 font-bold">Подано</th>
                                    <th class="py-4 pr-4 font-bold">Статус</th>
                                    <th class="py-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bookings as $booking)
                                    @php($s = $booking->trackSession)
                                    <tr class="border-b border-ink-900/10 align-top">
                                        <td class="py-5 pr-4">
                                            <a href="{{ route('sessions.show', $s) }}" class="font-semibold hover:text-apex-700">{{ $s->title }}</a>
                                            <p class="mt-1 text-sm text-ink-600">{{ $s->car_model }} · {{ $s->track_name }}</p>
                                        </td>
                                        <td class="py-5 pr-4 text-sm">{{ $s->starts_at->translatedFormat('j F Y, H:i') }}</td>
                                        <td class="py-5 pr-4 text-sm text-ink-600">{{ $booking->created_at->translatedFormat('j F Y') }}</td>
                                        <td class="py-5 pr-4">
                                            <span @class([
                                                'inline-flex items-center gap-2 text-sm font-semibold',
                                                'text-apex-700' => $booking->status === 'active',
                                                'text-ink-600' => $booking->status === 'cancelled',
                                            ])><span @class(['size-2 rounded-full', 'bg-apex-600' => $booking->status === 'active', 'bg-ink-600/40' => $booking->status === 'cancelled'])></span>{{ \App\Models\Booking::STATUSES[$booking->status] }}</span>
                                        </td>
                                        <td class="py-5 text-right">
                                            @can('cancel', $booking)
                                                <form method="POST" action="{{ route('bookings.destroy', $booking) }}" data-confirm="Скасувати бронювання на «{{ $s->title }}»?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="text-sm font-bold text-rose-600 underline-offset-4 hover:underline">Скасувати</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts.app>
