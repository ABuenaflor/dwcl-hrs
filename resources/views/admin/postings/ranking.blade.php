<x-layouts.print :title="'SAW ranking — '.$posting->title" :back="route('admin.postings.index')">
    @php
        $criteria = $result['criteria'];
        $weights = $result['weights'];
        $rows = $result['rows'];
        $n = fn ($v, $d = 3) => rtrim(rtrim(number_format($v, $d), '0'), '.') ?: '0';
    @endphp

    <div class="card p-6 sm:p-8 print:border-0 print:p-0">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-6">
            <div>
                <p class="text-xs font-semibold tracking-wider text-gold-600 uppercase">Applicant ranking · Simple Additive Weighting</p>
                <h1 class="mt-1 text-2xl font-semibold text-slate-900">{{ $posting->title }}</h1>
                <p class="text-sm text-slate-500">{{ $posting->department->name }} · {{ $posting->campus->name }} · {{ $posting->slots }} {{ Str::plural('opening', $posting->slots) }}</p>
            </div>
            <div class="text-right text-xs text-slate-500">
                <p>Divine Word College of Legazpi — HRDO</p>
                <p>Generated {{ now()->format('M j, Y g:i A') }}</p>
            </div>
        </div>

        @if (! $rows)
            <x-empty icon="users" title="No applicants yet" />
        @else
            {{-- Final ranking --}}
            <h2 class="mt-8 text-sm font-semibold text-slate-900">Ranked applicants (V<sub>i</sub> = Σ w<sub>j</sub> · r<sub>ij</sub>)</h2>
            <div class="mt-3 overflow-x-auto">
                <table class="table-clean">
                    <thead><tr><th class="w-14">Rank</th><th>Applicant</th><th>Status</th><th class="w-1/3">Preference value</th></tr></thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr @class(['bg-gold-50/60' => $row['rank'] <= $posting->slots])>
                                <td><span @class(['grid size-8 place-items-center rounded-full text-sm font-bold', 'bg-gold-500 text-brand-950' => $row['rank'] <= $posting->slots, 'bg-slate-100 text-slate-600' => $row['rank'] > $posting->slots])>{{ $row['rank'] }}</span></td>
                                <td>
                                    <a href="{{ route('admin.applications.show', $row['application']) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $row['application']->user->name }}</a>
                                    <p class="text-xs text-slate-500">{{ $row['application']->user->email }}</p>
                                </td>
                                <td><x-badge :tone="$row['application']->status->tone()">{{ $row['application']->status->label() }}</x-badge></td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-brand-600" style="width: {{ $row['score'] * 100 }}%"></div></div>
                                        <span class="w-14 text-right font-semibold tabular-nums">{{ number_format($row['score'], 4) }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-slate-500">Highlighted rows fall within the number of openings. The ranking supports — it does not replace — the HRDO's final decision.</p>

            {{-- Decision matrix --}}
            <h2 class="mt-10 text-sm font-semibold text-slate-900">Decision matrix — raw values x<sub>ij</sub> → normalized r<sub>ij</sub></h2>
            <div class="mt-3 overflow-x-auto">
                <table class="table-clean text-xs">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            @foreach ($criteria as $c)
                                <th class="text-right normal-case">{{ $c->name }}<br><span class="font-normal text-slate-400">w = {{ $n($weights[$c->id]) }} · {{ $c->type }}</span></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td class="font-medium whitespace-nowrap text-slate-900">{{ $row['application']->user->name }}</td>
                                @foreach ($criteria as $c)
                                    <td class="text-right tabular-nums">
                                        <span class="text-slate-900">{{ $n($row['raw'][$c->id], 2) }}</span>
                                        <span class="block text-slate-400">→ {{ $n($row['normalized'][$c->id]) }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="bg-slate-50">
                            <td class="font-semibold text-slate-600">max x<sub>j</sub></td>
                            @foreach ($criteria as $c)
                                <td class="text-right font-semibold text-slate-600 tabular-nums">{{ $n(collect($rows)->max(fn ($r) => $r['raw'][$c->id]), 2) }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs leading-5 text-slate-500">
                Benefit criteria: r<sub>ij</sub> = x<sub>ij</sub> / max(x<sub>j</sub>). Cost criteria: r<sub>ij</sub> = min(x<sub>j</sub>) / x<sub>ij</sub>.
                <em>Auto</em> criteria come from the application (years of experience; education level 1–5 +1 with license; trainings + certificates).
                <em>Manual</em> criteria are HRDO ratings on a 0–10 scale; unrated counts as 0.
            </p>
        @endif
    </div>
</x-layouts.print>
