<x-layouts.app title="Applicants">
    <x-page-header title="Applicants" subtitle="Ranked by SAW preference value within each vacancy.">
        <a href="{{ route('admin.reports.applications.csv', request()->only(['posting', 'campus', 'department', 'status'])) }}" class="btn btn-secondary"><x-icon name="download" class="size-4" /> Export CSV</a>
    </x-page-header>

    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-6" data-no-loading x-data x-on:change="$el.requestSubmit()">
        <div class="relative lg:col-span-2">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name or email" class="form-control pl-9">
        </div>
        <select name="posting" class="form-control lg:col-span-2">
            <option value="">All vacancies</option>
            @foreach ($postings as $p)<option value="{{ $p->id }}" @selected(($filters['posting'] ?? '') == $p->id)>{{ $p->title }}</option>@endforeach
        </select>
        <select name="status" class="form-control">
            <option value="">Any status</option>
            @foreach (App\Enums\ApplicationStatus::cases() as $s)<option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>@endforeach
        </select>
        <select name="sort" class="form-control">
            <option value="rank" @selected(($filters['sort'] ?? 'rank') === 'rank')>Sort: SAW rank</option>
            <option value="latest" @selected(($filters['sort'] ?? '') === 'latest')>Sort: newest</option>
        </select>
        <details class="sm:col-span-2 lg:col-span-6" @if (array_filter(\Illuminate\Support\Arr::only($filters, ['campus', 'department', 'category', 'type']))) open @endif>
            <summary class="cursor-pointer text-xs font-medium text-slate-500 select-none hover:text-brand-700">More filters — campus, department, job type</summary>
            <div class="mt-3 grid gap-3 sm:grid-cols-4">
                <select name="campus" class="form-control">
                    <option value="">All campuses</option>
                    @foreach ($campuses as $c)<option value="{{ $c->id }}" @selected(($filters['campus'] ?? '') == $c->id)>{{ $c->name }}</option>@endforeach
                </select>
                <select name="department" class="form-control">
                    <option value="">All departments</option>
                    @foreach ($departments as $d)<option value="{{ $d->id }}" @selected(($filters['department'] ?? '') == $d->id)>{{ $d->name }}</option>@endforeach
                </select>
                <select name="category" class="form-control">
                    <option value="">Academic &amp; non-academic</option>
                    @foreach (App\Enums\JobCategory::cases() as $c)<option value="{{ $c->value }}" @selected(($filters['category'] ?? '') === $c->value)>{{ $c->label() }}</option>@endforeach
                </select>
                <select name="type" class="form-control">
                    <option value="">Full &amp; part time</option>
                    @foreach (App\Enums\EmploymentType::cases() as $t)<option value="{{ $t->value }}" @selected(($filters['type'] ?? '') === $t->value)>{{ $t->label() }}</option>@endforeach
                </select>
            </div>
        </details>
    </form>

    <div class="card overflow-hidden">
        @if ($applications->isEmpty())
            <x-empty icon="users" title="No applicants match" message="Try clearing a filter.">
                <a href="{{ route('admin.applications.index') }}" class="btn btn-secondary">Clear filters</a>
            </x-empty>
        @else
            <div class="overflow-x-auto">
                <table class="table-clean">
                    <thead><tr><th class="w-16">Rank</th><th>Applicant</th><th>Vacancy</th><th>SAW score</th><th>Applied</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($applications as $app)
                            <tr class="cursor-pointer" onclick="if (! event.target.closest('a')) this.querySelector('a').click()">
                                <td>
                                    @if ($app->saw_rank)
                                        <span @class(['grid size-8 place-items-center rounded-full text-xs font-bold', 'bg-gold-500 text-brand-950' => $app->saw_rank === 1, 'bg-brand-50 text-brand-700' => $app->saw_rank > 1])>#{{ $app->saw_rank }}</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.applications.show', $app) }}" class="flex items-center gap-3">
                                        <x-avatar :user="$app->user" size="size-8" />
                                        <span><span class="block font-medium text-slate-900">{{ $app->user->name }}</span><span class="block text-xs text-slate-500">{{ $app->user->email }}</span></span>
                                    </a>
                                </td>
                                <td><p class="text-slate-800">{{ $app->jobPosting->title }}</p><p class="text-xs text-slate-500">{{ $app->jobPosting->department->name }}</p></td>
                                <td class="w-40">
                                    @if ($app->saw_score !== null)
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-600" style="width: {{ $app->saw_score * 100 }}%"></div></div>
                                            <span class="text-xs font-semibold tabular-nums">{{ number_format($app->saw_score, 3) }}</span>
                                        </div>
                                    @else — @endif
                                </td>
                                <td class="text-slate-500">{{ $app->created_at->format('M j, Y') }}</td>
                                <td><x-badge :tone="$app->status->tone()">{{ $app->status->label() }}</x-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <div class="mt-6">{{ $applications->links() }}</div>
</x-layouts.app>
