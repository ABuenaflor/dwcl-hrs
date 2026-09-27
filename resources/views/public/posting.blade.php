<x-layouts.public :title="$posting->title">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('careers.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-brand-700"><x-icon name="arrow-left" class="size-4" /> All vacancies</a>

        <div class="mt-6 grid gap-8 lg:grid-cols-3">
            <article class="lg:col-span-2">
                <div class="flex flex-wrap gap-2">
                    <x-badge :tone="$posting->category->value === 'academic' ? 'brand' : 'gold'">{{ $posting->category->label() }}</x-badge>
                    <x-badge tone="neutral">{{ $posting->employment_type->label() }}</x-badge>
                    @unless ($posting->isAcceptingApplications())<x-badge tone="danger">Not accepting applications</x-badge>@endunless
                </div>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900">{{ $posting->title }}</h1>
                <p class="mt-2 text-slate-500">{{ $posting->department->name }} · {{ $posting->campus->name }}</p>

                @if ($posting->description)
                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-slate-900">About the role</h2>
                        <p class="mt-3 leading-7 whitespace-pre-line text-slate-600">{{ $posting->description }}</p>
                    </div>
                @endif

                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-slate-900">Qualifications</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($posting->qualifications as $q)
                            <li class="flex gap-3 text-slate-600">
                                <span class="mt-0.5 grid size-5 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"><x-icon name="check" class="size-3.5" /></span>
                                {{ $q }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mt-8 rounded-2xl border border-brand-100 bg-brand-50/50 p-6">
                    <h2 class="flex items-center gap-2 font-semibold text-slate-900"><x-icon name="scale" class="size-5 text-brand-700" /> How applicants are evaluated</h2>
                    <p class="mt-1 text-sm text-slate-600">Every application is scored on the same criteria and ranked using Simple Additive Weighting.</p>
                    <div class="mt-5 space-y-3">
                        @foreach ($posting->criteria as $c)
                            <div>
                                <div class="flex justify-between text-sm"><span class="font-medium text-slate-700">{{ $c->name }}</span><span class="text-slate-500 tabular-nums">{{ round($c->weight * 100) }}%</span></div>
                                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-white">
                                    <div class="h-full rounded-full bg-brand-600" style="width: {{ $c->weight * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>

            <aside>
                <div class="card sticky top-24 p-6">
                    <dl class="space-y-4 text-sm">
                        @foreach ([['map-pin', 'Campus', $posting->campus->name], ['building', 'Department', $posting->department->name], ['clock', 'Schedule', $posting->schedule ?? $posting->employment_type->label()], ['users', 'Openings', $posting->slots], ['calendar', 'Apply until', $posting->closes_at?->format('F j, Y') ?? 'Until filled']] as [$icon, $label, $value])
                            <div class="flex gap-3">
                                <x-icon :name="$icon" class="mt-0.5 size-4 shrink-0 text-slate-400" />
                                <div><dt class="text-slate-500">{{ $label }}</dt><dd class="font-medium text-slate-900">{{ $value }}</dd></div>
                            </div>
                        @endforeach
                    </dl>
                    <div class="mt-6 border-t border-slate-100 pt-6">
                        @if ($existing)
                            <a href="{{ route('applicant.applications.show', $existing) }}" class="btn btn-secondary w-full">View my application</a>
                            <p class="mt-2 text-center text-xs text-slate-500">Status: {{ $existing->status->label() }}</p>
                        @elseif (! $posting->isAcceptingApplications())
                            <button class="btn btn-secondary w-full" disabled>Applications closed</button>
                        @elseif (auth()->check() && ! auth()->user()->hasRole(App\Enums\Role::Applicant))
                            <p class="text-center text-sm text-slate-500">Sign in with an applicant account to apply.</p>
                        @else
                            <a href="{{ route('applicant.apply', $posting) }}" class="btn btn-gold w-full py-3">Apply for this position <x-icon name="arrow-right" class="size-4" /></a>
                            @guest<p class="mt-2 text-center text-xs text-slate-500">You'll be asked to sign in or create an account.</p>@endguest
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-layouts.public>
