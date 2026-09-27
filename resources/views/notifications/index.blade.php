<x-layouts.app title="Notifications">
    <x-page-header title="Notifications">
        <form method="POST" action="{{ route('notifications.read-all') }}">@csrf
            <button class="btn btn-secondary"><x-icon name="check" class="size-4" /> Mark all read</button>
        </form>
    </x-page-header>

    <div class="card divide-y divide-slate-100 overflow-hidden">
        @forelse ($notifications as $n)
            <a href="{{ route('notifications.open', $n->id) }}" class="flex items-start gap-4 px-5 py-4 transition hover:bg-slate-50 {{ $n->read_at ? '' : 'bg-brand-50/40' }}">
                <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $n->read_at ? 'bg-slate-200' : 'bg-brand-600' }}"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-slate-900">{{ $n->data['title'] }}</p>
                    <p class="text-sm text-slate-600">{{ $n->data['message'] }}</p>
                </div>
                <span class="shrink-0 text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
            </a>
        @empty
            <x-empty icon="bell" title="No notifications yet" message="Updates about your applications and reviews will appear here." />
        @endforelse
    </div>
    <div class="mt-6">{{ $notifications->links() }}</div>
</x-layouts.app>
