<x-layouts.app title="Faculty ranking">
    <x-page-header title="Faculty ranking" subtitle="Self-ratings moving through DRC → Council → VP → President.">
        @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.reports.rankings.csv') }}" class="btn btn-secondary"><x-icon name="download" class="size-4" /> Export CSV</a>
        @endif
    </x-page-header>

    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5" data-no-loading x-data x-on:change="$el.requestSubmit()">
        <div class="relative lg:col-span-2">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Faculty name" class="form-control pl-9">
        </div>
        <select name="level" class="form-control">
            <option value="">All levels</option>
            @foreach (App\Enums\Level::rankable() as $l)<option value="{{ $l->value }}" @selected(($filters['level'] ?? '') === $l->value)>{{ $l->label() }}</option>@endforeach
        </select>
        <select name="status" class="form-control">
            <option value="">Any stage</option>
            @foreach (App\Enums\RankingStatus::cases() as $s)
                @continue($s === App\Enums\RankingStatus::Draft)
                <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
        <select name="cycle" class="form-control">
            <option value="">All cycles</option>
            @foreach ($cycles as $c)<option value="{{ $c }}" @selected(($filters['cycle'] ?? '') === $c)>{{ $c }}</option>@endforeach
        </select>
    </form>

    <div class="card overflow-hidden">
        @if ($rankings->isEmpty())
            <x-empty icon="award" title="No submitted rankings" message="Faculty submissions will appear here once filed." />
        @else
            <div class="overflow-x-auto">
                <table class="table-clean">
                    <thead><tr><th>Faculty</th><th>Level / cycle</th><th class="text-right">SR</th><th class="text-right">DRC</th><th class="text-right">Final</th><th>Recommended rank</th><th>Stage</th></tr></thead>
                    <tbody>
                        @foreach ($rankings as $r)
                            @php $mine = $r->status->actor() && (auth()->user()->isAdmin() || $r->status->actor() === auth()->user()->role); @endphp
                            <tr class="cursor-pointer" onclick="if (! event.target.closest('a')) this.querySelector('a').click()">
                                <td>
                                    <a href="{{ route('admin.rankings.show', $r) }}" class="flex items-center gap-3">
                                        <x-avatar :user="$r->user" size="size-8" />
                                        <span><span class="block font-medium text-slate-900">{{ $r->user->name }}</span><span class="block text-xs text-slate-500">{{ $r->user->department?->name }}</span></span>
                                    </a>
                                </td>
                                <td class="text-slate-600">{{ $r->rubric->level->label() }}<p class="text-xs text-slate-400">{{ $r->cycle }}</p></td>
                                <td class="text-right tabular-nums">{{ number_format($r->sr_total, 2) }}</td>
                                <td class="text-right tabular-nums">{{ $r->drc_total !== null ? number_format($r->drc_total, 2) : '—' }}</td>
                                <td class="text-right font-semibold tabular-nums">{{ $r->final_total !== null ? number_format($r->final_total, 2) : '—' }}</td>
                                <td class="text-slate-700">{{ $r->recommendedRank?->name ?? '—' }}</td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <x-badge :tone="$r->status->tone()">{{ $r->status->label() }}</x-badge>
                                        @if ($mine)<span class="size-2 rounded-full bg-gold-500" title="Awaiting your action"></span>@endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <div class="mt-6">{{ $rankings->links() }}</div>
</x-layouts.app>
