{{--
    Rubric score sheet.
    $tree, $scores, $subtotals — from RankingScores::viewData()
    $edit     — score column being edited ('sr_points' | 'drc_points' | 'final_points' | null)
    $evidence — 'upload' (faculty), 'view' (committee) or null
--}}
@php
    $tertiary = $ranking->rubric->level === App\Enums\Level::Tertiary;
    $council = $ranking->rubric->level->councilName();
    $columns = ['sr_points' => 'SR', 'drc_points' => 'DRC', 'final_points' => $council];
    $prefill = $edit && $edit !== 'sr_points';
@endphp
<div class="card overflow-hidden" x-data="rubricSheet(@js($edit))" x-init="recalc()">
    <div class="overflow-x-auto">
        <table class="table-clean min-w-[860px]">
            <thead>
                <tr>
                    <th class="w-[42%]">Criteria</th>
                    <th class="text-right">{{ $tertiary ? 'In field' : 'Weight %' }}</th>
                    <th class="text-right">{{ $tertiary ? 'Related' : 'Credit pts' }}</th>
                    <th class="text-right">Max</th>
                    @foreach ($columns as $col => $label)
                        <th @class(['w-24 text-right', 'bg-gold-50! text-gold-700!' => $edit === $col])>{{ $label }}</th>
                    @endforeach
                    @if ($evidence)<th class="w-40">Evidence</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach ($tree as $node)
                    @include('rankings._row', ['node' => $node, 'depth' => 0])
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-brand-900 text-white">
                    <td class="px-4 py-4 font-semibold" colspan="4">Grand total</td>
                    @foreach ($columns as $col => $label)
                        <td class="px-4 py-4 text-right text-base font-semibold tabular-nums">
                            @if ($edit === $col)
                                <span x-text="fmt(total)"></span>
                            @else
                                {{ $scores->whereNotNull($col)->isEmpty() ? '—' : number_format($tree->sum(fn ($n) => $subtotals[$col][$n->id] ?? 0), 2) }}
                            @endif
                        </td>
                    @endforeach
                    @if ($evidence)<td></td>@endif
                </tr>
            </tfoot>
        </table>
    </div>
    @if ($prefill)
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50 px-4 py-3 text-sm">
            <span class="text-slate-500">Tip: start from the previous column and adjust only what the evidence doesn't support.</span>
            <button type="button" class="btn btn-secondary btn-sm" x-on:click="copyFrom('{{ $edit === 'final_points' ? 'drc_points' : 'sr_points' }}')">
                <x-icon name="undo" class="size-4" /> Copy {{ $edit === 'final_points' ? 'DRC' : 'SR' }} scores
            </button>
        </div>
    @endif
</div>

@once
    @push('scripts')
        <script>
            // Mirrors App\Services\RubricCalculator: subtotal = own points + children, capped at max.
            document.addEventListener('alpine:init', () => {
                Alpine.data('rubricSheet', (column) => ({
                    column,
                    total: 0,
                    fmt: (n) => n.toFixed(2),
                    recalc() {
                        if (!this.column) return;
                        const rows = [...this.$root.querySelectorAll('tr[data-node]')];
                        const byParent = {};
                        rows.forEach((r) => (byParent[r.dataset.parent || 0] ??= []).push(r));
                        const sub = (row) => {
                            const input = row.querySelector('input[data-points]');
                            let sum = input ? parseFloat(input.value) || 0 : 0;
                            (byParent[row.dataset.node] ?? []).forEach((c) => (sum += sub(c)));
                            if (row.dataset.max !== '') sum = Math.min(sum, parseFloat(row.dataset.max));
                            const out = row.querySelector('[data-subtotal]');
                            if (out) out.textContent = sum.toFixed(2);
                            return sum;
                        };
                        this.total = (byParent[0] ?? []).reduce((t, r) => t + sub(r), 0);
                    },
                    copyFrom(source) {
                        this.$root.querySelectorAll('input[data-points]').forEach((input) => {
                            const v = input.closest('tr').dataset[source === 'sr_points' ? 'sr' : 'drc'];
                            if (v !== '') input.value = v;
                        });
                        this.recalc();
                    },
                }));
            });
        </script>
    @endpush
@endonce
