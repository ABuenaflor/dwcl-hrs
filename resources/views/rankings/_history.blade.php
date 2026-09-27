<section class="card p-6">
    <h2 class="text-sm font-semibold text-slate-900">Review history</h2>
    @if ($ranking->reviews->isEmpty())
        <p class="mt-3 text-sm text-slate-500">Nothing yet — the trail starts when the self-rating is submitted.</p>
    @else
        <ol class="relative mt-5 space-y-5 border-l border-slate-200 pl-5">
            @foreach ($ranking->reviews as $r)
                <li class="relative">
                    <span @class(['absolute top-1 -left-[26px] size-3 rounded-full ring-4 ring-white', 'bg-rose-500' => $r->to_status === App\Enums\RankingStatus::Returned, 'bg-brand-600' => $r->to_status !== App\Enums\RankingStatus::Returned])></span>
                    <p class="text-sm font-medium text-slate-900">{{ $r->to_status->label() }}</p>
                    <p class="text-xs text-slate-500">{{ $r->user?->name ?? 'System' }} · {{ $r->created_at->format('M j, Y g:i A') }}</p>
                    @if ($r->remarks)<p class="mt-1.5 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">{{ $r->remarks }}</p>@endif
                </li>
            @endforeach
        </ol>
    @endif
</section>
