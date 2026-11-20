{{-- 4 інформаційні картки статистики (без рамок — тонкі лінії) --}}
<dl class="grid grid-cols-2 gap-y-10 border-y border-ink-900/10 py-10 lg:grid-cols-4">
    @foreach ([
        ['sessions', 'усього заїздів'],
        ['published', 'опубліковано'],
        ['users', 'користувачів'],
        ['activeBookings', 'активних бронювань'],
    ] as [$key, $caption])
        <div class="lg:border-l lg:border-ink-900/10 lg:pl-8 lg:first:border-0 lg:first:pl-0">
            <dd class="stat-num" data-count-to="{{ $stats[$key] }}">{{ $stats[$key] }}</dd>
            <dt class="eyebrow mt-3 text-ink-600">{{ $caption }}</dt>
        </div>
    @endforeach
</dl>
