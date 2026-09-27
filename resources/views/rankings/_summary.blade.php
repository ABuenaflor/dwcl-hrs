{{-- Workflow tracker + totals for a faculty ranking. --}}
@php
    $council = $ranking->rubric->level->councilName();
    $labels = ['Self-rating', 'DRC review', $council.' review', 'VP endorsement', "President's approval", 'Certificate'];
    $returned = $ranking->status === App\Enums\RankingStatus::Returned;
@endphp
<section class="card p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold tracking-wider text-slate-400 uppercase">Cycle {{ $ranking->cycle }} · {{ $ranking->rubric->level->label() }}</p>
            <p class="mt-1 font-semibold text-slate-900">{{ $ranking->rubric->name }}</p>
        </div>
        <x-badge :tone="$ranking->status->tone()" class="px-3 py-1 text-sm">{{ $ranking->status->label() }}</x-badge>
    </div>
    <div class="mt-6">
        <x-steps :steps="$labels" :current="$ranking->status->step()" :failed="$returned" />
    </div>

    <dl class="mt-6 grid grid-cols-2 gap-3 border-t border-slate-100 pt-6 sm:grid-cols-4">
        @foreach ([['Self-rating', $ranking->sr_total], ['DRC', $ranking->drc_total], [$council, $ranking->final_total]] as [$label, $value])
            <div class="rounded-xl bg-slate-50 p-3">
                <dt class="text-xs text-slate-500">{{ $label }} total</dt>
                <dd class="mt-1 text-xl font-semibold text-slate-900 tabular-nums">{{ $value === null ? '—' : number_format($value, 2) }}</dd>
            </div>
        @endforeach
        <div class="rounded-xl bg-gradient-to-br from-brand-700 to-brand-900 p-3 text-white">
            <dt class="text-xs text-brand-200">Recommended rank</dt>
            <dd class="mt-1 text-sm font-semibold">{{ $ranking->recommendedRank?->name ?? '—' }}</dd>
            @if ($ranking->currentRank)<dd class="text-[11px] text-brand-200">from {{ $ranking->currentRank->name }}</dd>@endif
        </div>
    </dl>
</section>
