<x-layouts.app title="Reports">
    <x-page-header title="Reports" subtitle="Hiring and ranking insight for decision-makers. Filter, then export or print.">
        <a href="{{ route('admin.reports.applications.csv', array_filter($filters)) }}" class="btn btn-secondary"><x-icon name="download" class="size-4" /> Applications CSV</a>
        <a href="{{ route('admin.reports.rankings.csv') }}" class="btn btn-secondary"><x-icon name="download" class="size-4" /> Rankings CSV</a>
        <button onclick="window.print()" class="btn btn-primary"><x-icon name="printer" class="size-4" /> Print</button>
    </x-page-header>

    <form method="GET" class="card no-print mb-6 grid gap-3 p-4 sm:grid-cols-3 lg:grid-cols-6" data-no-loading x-data x-on:change="$el.requestSubmit()">
        <div><label class="form-label text-xs">From</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control"></div>
        <div><label class="form-label text-xs">To</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control"></div>
        <div><label class="form-label text-xs">Campus</label>
            <select name="campus" class="form-control"><option value="">All</option>@foreach ($campuses as $c)<option value="{{ $c->id }}" @selected(($filters['campus'] ?? '') == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
        <div><label class="form-label text-xs">Department</label>
            <select name="department" class="form-control"><option value="">All</option>@foreach ($departments as $d)<option value="{{ $d->id }}" @selected(($filters['department'] ?? '') == $d->id)>{{ $d->name }}</option>@endforeach</select></div>
        <div><label class="form-label text-xs">Vacancy</label>
            <select name="posting" class="form-control"><option value="">All</option>@foreach ($postingOptions as $p)<option value="{{ $p->id }}" @selected(($filters['posting'] ?? '') == $p->id)>{{ $p->title }}</option>@endforeach</select></div>
        <div class="flex items-end"><a href="{{ route('admin.reports.index') }}" class="btn btn-ghost w-full">Reset</a></div>
    </form>

    <div class="reveal grid gap-4 sm:grid-cols-3">
        <x-stat label="Applications" :value="$total" icon="file" />
        <x-stat label="Hired" :value="$hired" icon="check-circle" tone="success" />
        <x-stat label="Hire rate" :value="$total ? round($hired / $total * 100, 1).'%' : '—'" icon="chart" tone="gold" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="card p-6 lg:col-span-2">
            <h2 class="text-sm font-semibold text-slate-900">Applications per month (12 months)</h2>
            <div class="mt-4"><x-chart type="bar" :data="$charts['trend']" /></div>
        </section>
        <section class="card p-6"><h2 class="text-sm font-semibold text-slate-900">By department</h2><div class="mt-4"><x-chart :data="$charts['department']" horizontal height="h-72" /></div></section>
        <section class="card p-6"><h2 class="text-sm font-semibold text-slate-900">By status</h2><div class="mt-4"><x-chart type="doughnut" :data="$charts['status']" height="h-72" /></div></section>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="card overflow-hidden">
            <h2 class="border-b border-slate-100 px-6 py-4 text-sm font-semibold text-slate-900">Recent vacancies</h2>
            <table class="table-clean">
                <thead><tr><th>Vacancy</th><th class="text-right">Applicants</th><th class="text-right">Hired / slots</th></tr></thead>
                <tbody>
                    @forelse ($postings as $p)
                        <tr>
                            <td><a href="{{ route('admin.postings.ranking', $p) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $p->title }}</a><p class="text-xs text-slate-500">{{ $p->department->name }}</p></td>
                            <td class="text-right tabular-nums">{{ $p->applications_count }}</td>
                            <td class="text-right tabular-nums">{{ $p->hired_count }} / {{ $p->slots }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-slate-500">No vacancies yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        <section class="card overflow-hidden">
            <h2 class="border-b border-slate-100 px-6 py-4 text-sm font-semibold text-slate-900">Faculty rankings by stage</h2>
            <table class="table-clean">
                <thead><tr><th>Stage</th><th class="text-right">Count</th><th class="text-right">Avg. points</th></tr></thead>
                <tbody>
                    @forelse ($rankingSummary as $row)
                        @php $s = App\Enums\RankingStatus::from($row->status instanceof App\Enums\RankingStatus ? $row->status->value : $row->status); @endphp
                        <tr>
                            <td><x-badge :tone="$s->tone()">{{ $s->label() }}</x-badge></td>
                            <td class="text-right tabular-nums">{{ $row->total }}</td>
                            <td class="text-right tabular-nums">{{ number_format($row->average, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-slate-500">No rankings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</x-layouts.app>
