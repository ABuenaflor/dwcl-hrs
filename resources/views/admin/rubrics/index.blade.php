<x-layouts.app title="Rubrics & ranks">
    <x-page-header title="Rubrics & academic ranks" subtitle="Ranking criteria and rank thresholds are data — revise them when the Faculty Manual changes." />

    <div class="grid gap-6 lg:grid-cols-2">
        @foreach ($rubrics as $rubric)
            <a href="{{ route('admin.rubrics.show', $rubric) }}" class="card card-hover group p-6">
                <div class="flex items-start justify-between gap-3">
                    <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-icon name="scale" /></span>
                    <x-badge :tone="$rubric->is_active ? 'success' : 'neutral'">{{ $rubric->is_active ? 'Active' : 'Inactive' }}</x-badge>
                </div>
                <p class="mt-4 text-xs font-semibold tracking-wider text-slate-400 uppercase">{{ $rubric->level->label() }}</p>
                <h2 class="mt-1 text-lg font-semibold text-slate-900 group-hover:text-brand-700">{{ $rubric->name }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $rubric->items_count }} criteria · {{ number_format($rubric->total_points) }} points maximum</p>
                <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-700">Edit criteria <x-icon name="arrow-right" class="size-4 transition-transform group-hover:translate-x-1" /></span>
            </a>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.ranks.update') }}" class="card mt-8 p-6">
        @csrf @method('PUT')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Academic ranks</h2>
                <p class="text-sm text-slate-500">The recommended rank is the highest one whose minimum the final total reaches.</p>
            </div>
            <button class="btn btn-primary"><span class="btn-label">Save ranks</span></button>
        </div>

        <div class="mt-6 grid gap-8 lg:grid-cols-2">
            @foreach (App\Enums\Level::rankable() as $level)
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-700">{{ $level->label() }}</h3>
                    <div class="overflow-hidden rounded-xl border border-slate-200">
                        <table class="table-clean">
                            <thead><tr><th>Rank</th><th class="w-36 text-right">Minimum points</th></tr></thead>
                            <tbody>
                                @foreach ($ranks->get($level->value, collect()) as $rank)
                                    <tr>
                                        <td class="py-2"><input name="ranks[{{ $rank->id }}][name]" value="{{ $rank->name }}" class="form-control border-transparent bg-transparent shadow-none hover:border-slate-200 focus:bg-white"></td>
                                        <td class="py-2"><input type="number" step="0.01" min="0" name="ranks[{{ $rank->id }}][min_points]" value="{{ $rank->min_points + 0 }}" class="form-control text-right"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 rounded-xl bg-slate-50 p-4">
            <p class="text-sm font-medium text-slate-700">Add a rank</p>
            <div class="mt-3 grid gap-3 sm:grid-cols-4">
                <select name="new[level]" class="form-control">
                    <option value="">Level…</option>
                    @foreach (App\Enums\Level::rankable() as $level)<option value="{{ $level->value }}">{{ $level->label() }}</option>@endforeach
                </select>
                <input name="new[name]" class="form-control sm:col-span-2" placeholder="Rank name">
                <input type="number" step="0.01" min="0" name="new[min_points]" class="form-control" placeholder="Min. points">
            </div>
        </div>
    </form>
</x-layouts.app>
