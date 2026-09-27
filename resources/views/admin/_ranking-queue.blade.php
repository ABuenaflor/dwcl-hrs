@forelse ($awaiting as $r)
    <a href="{{ route('admin.rankings.show', $r) }}" class="flex items-center gap-3 border-b border-slate-100 px-6 py-3 transition last:border-0 hover:bg-slate-50">
        <x-avatar :user="$r->user" size="size-9" />
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-slate-900">{{ $r->user->name }}</p>
            <p class="truncate text-xs text-slate-500">{{ $r->user->department?->name }} · waiting {{ $r->updated_at->diffForHumans(null, true) }}</p>
        </div>
        <x-badge :tone="$r->status->tone()">{{ $r->status->label() }}</x-badge>
    </a>
@empty
    <x-empty icon="check-circle" title="Nothing waiting on you" message="New submissions will appear here." />
@endforelse
