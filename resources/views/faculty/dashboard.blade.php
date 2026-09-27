<x-layouts.app title="My ranking">
    <x-page-header title="Faculty ranking" :subtitle="'Current cycle: '.$cycle">
        @if ($eligible && ! $current)
            <form method="POST" action="{{ route('faculty.rankings.store') }}">@csrf
                <button class="btn btn-primary"><x-icon name="plus" class="size-4" /><span class="btn-label">Start self-rating</span></button>
            </form>
        @elseif ($current)
            <a href="{{ route('faculty.rankings.show', $current) }}" class="btn btn-primary">{{ $current->status->isEditableByFaculty() ? 'Continue self-rating' : 'View current ranking' }} <x-icon name="arrow-right" class="size-4" /></a>
        @endif
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card p-6">
            <div class="flex items-center gap-4">
                <x-avatar :user="$user" size="size-14" class="text-base" />
                <div>
                    <p class="font-semibold text-slate-900">{{ $user->full_name }}</p>
                    <p class="text-sm text-slate-500">{{ $user->designation ?? 'Faculty' }}</p>
                </div>
            </div>
            <dl class="mt-6 space-y-3 text-sm">
                @foreach ([['Department', $user->department?->name], ['Campus', $user->campus?->name], ['Employment', $user->employment_type?->label()], ['Current rank', $user->academicRank?->name], ['With DWCL since', $user->date_hired?->format('F Y')]] as [$label, $value])
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">{{ $label }}</dt><dd class="text-right font-medium text-slate-900">{{ $value ?? '—' }}</dd></div>
                @endforeach
            </dl>
        </section>

        <div class="space-y-6 lg:col-span-2">
            @unless ($eligible)
                <div class="card flex gap-4 p-5">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-700"><x-icon name="info" /></span>
                    <p class="text-sm text-slate-600">Faculty ranking covers teaching faculty of Basic Education and the College. Your department isn't a teaching unit, so there's nothing to file here. Contact the HRDO if this is wrong.</p>
                </div>
            @endunless

            @if ($current)
                @include('rankings._summary', ['ranking' => $current])
            @elseif ($eligible)
                <div class="card overflow-hidden">
                    <div class="bg-gradient-to-br from-brand-800 to-brand-950 p-6 text-white">
                        <x-icon name="award" class="size-8 text-gold-400" />
                        <h2 class="mt-4 text-lg font-semibold">The {{ $cycle }} ranking cycle is open</h2>
                        <p class="mt-1 text-sm text-brand-100/80">Rate yourself against the Faculty Manual criteria and attach proof for each claim. Your ranking committee reviews the evidence, not memory.</p>
                    </div>
                    <ol class="grid gap-4 p-6 text-sm sm:grid-cols-3">
                        <li><span class="font-semibold text-slate-900">1. Self-rate</span><p class="text-slate-500">Enter points per criterion; caps apply automatically.</p></li>
                        <li><span class="font-semibold text-slate-900">2. Attach evidence</span><p class="text-slate-500">Certificates, diplomas and designations as PDF or image.</p></li>
                        <li><span class="font-semibold text-slate-900">3. Submit</span><p class="text-slate-500">DRC → Council → VP → President. You're notified at each step.</p></li>
                    </ol>
                </div>
            @endif

            <section class="card overflow-hidden">
                <h2 class="border-b border-slate-100 px-6 py-4 text-sm font-semibold text-slate-900">Ranking history</h2>
                @forelse ($rankings as $r)
                    <a href="{{ route('faculty.rankings.show', $r) }}" class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4 transition last:border-0 hover:bg-slate-50">
                        <div>
                            <p class="font-medium text-slate-900">Cycle {{ $r->cycle }}</p>
                            <p class="text-xs text-slate-500">{{ $r->rubric->level->label() }} · {{ number_format($r->effectiveTotal(), 2) }} pts · {{ $r->recommendedRank?->name ?? 'No rank yet' }}</p>
                        </div>
                        <x-badge :tone="$r->status->tone()">{{ $r->status->label() }}</x-badge>
                    </a>
                @empty
                    <x-empty icon="award" title="No rankings yet" />
                @endforelse
            </section>
        </div>
    </div>
</x-layouts.app>
