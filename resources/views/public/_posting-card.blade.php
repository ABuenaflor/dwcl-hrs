<a href="{{ route('careers.show', $posting) }}" class="card card-hover group flex flex-col p-6">
    <div class="flex items-start justify-between gap-3">
        <x-badge :tone="$posting->category->value === 'academic' ? 'brand' : 'gold'">{{ $posting->category->label() }}</x-badge>
        <span class="text-xs text-slate-400">{{ $posting->created_at->diffForHumans() }}</span>
    </div>
    <h3 class="mt-4 text-lg font-semibold text-slate-900 transition group-hover:text-brand-700">{{ $posting->title }}</h3>
    <p class="mt-1 text-sm text-slate-500">{{ $posting->department->name }}</p>
    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-slate-500">
        <span class="inline-flex items-center gap-1"><x-icon name="map-pin" class="size-3.5" /> {{ $posting->campus->name }}</span>
        <span class="inline-flex items-center gap-1"><x-icon name="clock" class="size-3.5" /> {{ $posting->employment_type->label() }}</span>
        @if ($posting->closes_at)
            <span class="inline-flex items-center gap-1"><x-icon name="calendar" class="size-3.5" /> Until {{ $posting->closes_at->format('M j') }}</span>
        @endif
    </div>
    <span class="mt-auto inline-flex items-center gap-1 pt-6 text-sm font-semibold text-brand-700">
        View details <x-icon name="arrow-right" class="size-4 transition-transform duration-300 group-hover:translate-x-1" />
    </span>
</a>
