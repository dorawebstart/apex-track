<span @class([
    'inline-flex items-center gap-1.5 whitespace-nowrap text-sm font-semibold',
    'text-apex-700' => $status === 'published',
    'text-amber-600' => $status === 'draft',
    'text-ink-600' => $status === 'archived',
])><span @class([
    'size-2 rounded-full',
    'bg-apex-600' => $status === 'published',
    'bg-amber-500' => $status === 'draft',
    'bg-ink-600/40' => $status === 'archived',
])></span>{{ \App\Models\TrackSession::STATUSES[$status] }}</span>
