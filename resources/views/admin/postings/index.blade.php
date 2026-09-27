<x-layouts.app title="Vacancies">
    <x-page-header title="Vacancies" subtitle="Post positions, tune how applicants are weighted, and see the ranked shortlist.">
        <a href="{{ route('admin.postings.create') }}" class="btn btn-primary"><x-icon name="plus" class="size-4" /> Post a vacancy</a>
    </x-page-header>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.postings.index') }}" @class(['chip', 'is-active' => ! $status])>All</a>
            @foreach (App\Enums\PostingStatus::cases() as $s)
                <a href="{{ route('admin.postings.index', ['status' => $s->value]) }}" @class(['chip', 'is-active' => $status === $s])>{{ $s->label() }}</a>
            @endforeach
        </div>
        <form method="GET" class="relative" data-no-loading>
            @if ($status)<input type="hidden" name="status" value="{{ $status->value }}">@endif
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search title…" class="form-control w-64 pl-9">
        </form>
    </div>

    <div class="card overflow-hidden">
        @if ($postings->isEmpty())
            <x-empty icon="briefcase" title="No vacancies found">
                <a href="{{ route('admin.postings.create') }}" class="btn btn-primary">Post a vacancy</a>
            </x-empty>
        @else
            <div class="overflow-x-auto">
                <table class="table-clean">
                    <thead><tr><th>Position</th><th>Department</th><th>Type</th><th class="text-center">Applicants</th><th>Closes</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($postings as $p)
                            <tr>
                                <td>
                                    <p class="font-medium text-slate-900">{{ $p->title }}</p>
                                    <p class="text-xs text-slate-500">{{ $p->category->label() }} · {{ $p->slots }} {{ Str::plural('slot', $p->slots) }}</p>
                                </td>
                                <td class="text-slate-600">{{ $p->department->name }}<p class="text-xs text-slate-400">{{ $p->campus->name }}</p></td>
                                <td class="text-slate-600">{{ $p->employment_type->label() }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.applications.index', ['posting' => $p->id]) }}" class="inline-grid min-w-8 place-items-center rounded-full bg-brand-50 px-2 py-0.5 text-sm font-semibold text-brand-700 hover:bg-brand-100">{{ $p->applications_count }}</a>
                                </td>
                                <td class="text-slate-600">{{ $p->closes_at?->format('M j, Y') ?? '—' }}</td>
                                <td><x-badge :tone="$p->status->tone()">{{ $p->status->label() }}</x-badge></td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('admin.postings.ranking', $p) }}" class="btn btn-ghost btn-sm" title="SAW ranking"><x-icon name="scale" class="size-4" /> Ranking</a>
                                    <a href="{{ route('admin.postings.edit', $p) }}" class="btn btn-ghost btn-sm" title="Edit"><x-icon name="pencil" class="size-4" /></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <div class="mt-6">{{ $postings->links() }}</div>
</x-layouts.app>
