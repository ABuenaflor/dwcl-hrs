<x-layouts.app :title="$application->jobPosting->title">
    @php
        $S = \App\Enums\ApplicationStatus::class;
        $posting = $application->jobPosting;
        $flow = ['Submitted', 'Shortlisted', 'Interview', 'Offer', 'Hired'];
        $at = match ($application->status) { $S::Submitted => 0, $S::Shortlisted => 1, $S::Interview => 2, $S::Offered => 3, default => 5 };
        $stopped = in_array($application->status, [$S::Withdrawn, $S::Rejected, $S::Declined], true);
    @endphp

    <x-page-header :title="$posting->title" :subtitle="$posting->department->name.' · '.$posting->campus->name" :back="route('applicant.dashboard')">
        <x-badge :tone="$application->status->tone()" class="px-3 py-1 text-sm">{{ $application->status->label() }}</x-badge>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card p-6">
                <h2 class="text-sm font-semibold text-slate-900">Progress</h2>
                @if ($stopped)
                    <p class="mt-3 text-sm text-slate-600">
                        @switch($application->status)
                            @case($S::Withdrawn) You withdrew this application on {{ $application->decided_at?->format('M j, Y') }}. @break
                            @case($S::Declined) You declined the offer on {{ $application->decided_at?->format('M j, Y') }}. @break
                            @default Thank you for your interest. HRDO has decided to proceed with other candidates for this position.
                        @endswitch
                    </p>
                @else
                    <div class="mt-6"><x-steps :steps="$flow" :current="$at" /></div>
                @endif

                @if ($application->status === $S::Interview && $application->interview_at)
                    <div class="mt-6 flex items-center gap-4 rounded-xl bg-brand-50 p-4">
                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-white text-center shadow-sm">
                            <span class="text-[10px] font-semibold text-rose-600 uppercase">{{ $application->interview_at->format('M') }}</span>
                            <span class="-mt-1 text-lg font-bold text-slate-900">{{ $application->interview_at->format('j') }}</span>
                        </span>
                        <div class="text-sm">
                            <p class="font-semibold text-slate-900">Interview scheduled</p>
                            <p class="text-slate-600">{{ $application->interview_at->format('l, F j \a\t g:i A') }} · HRDO, {{ $posting->campus->name }}</p>
                        </div>
                    </div>
                @endif

                @if ($application->status === $S::Offered)
                    <div class="mt-6 rounded-xl border border-gold-300 bg-gold-50 p-5">
                        <p class="flex items-center gap-2 font-semibold text-slate-900"><x-icon name="sparkles" class="size-5 text-gold-600" /> You've received a job offer</p>
                        <p class="mt-1 text-sm text-slate-600">Please accept or decline. HRDO will contact you about contract terms once you accept.</p>
                        <div class="mt-4 flex gap-3">
                            <form method="POST" action="{{ route('applicant.applications.respond', $application) }}">@csrf
                                <input type="hidden" name="decision" value="accept">
                                <button class="btn btn-success"><span class="btn-label">Accept offer</span></button>
                            </form>
                            <button type="button" class="btn btn-secondary" x-data x-on:click="$dispatch('open-modal', 'decline')">Decline</button>
                        </div>
                    </div>
                    <x-modal name="decline" title="Decline this offer?">
                        <p class="text-sm text-slate-600">This can't be undone. HRDO will offer the position to the next-ranked applicant.</p>
                        <form method="POST" action="{{ route('applicant.applications.respond', $application) }}" class="mt-6 flex justify-end gap-3">@csrf
                            <input type="hidden" name="decision" value="decline">
                            <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal')">Cancel</button>
                            <button class="btn btn-danger"><span class="btn-label">Decline offer</span></button>
                        </form>
                    </x-modal>
                @endif
            </section>

            <section class="card p-6">
                <h2 class="text-sm font-semibold text-slate-900">What you submitted</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-3">
                    <div><dt class="text-slate-500">Experience</dt><dd class="font-medium text-slate-900">{{ $application->years_experience }} years</dd></div>
                    <div><dt class="text-slate-500">Trainings</dt><dd class="font-medium text-slate-900">{{ $application->trainings_count }}</dd></div>
                    <div><dt class="text-slate-500">License</dt><dd class="font-medium text-slate-900">{{ $application->license ?: '—' }}</dd></div>
                </dl>
                @foreach (['work_experience' => 'Work experience', 'trainings' => 'Seminars & trainings', 'skills' => 'Special skills', 'cover_letter' => 'Cover letter'] as $field => $label)
                    @if ($application->{$field})
                        <div class="mt-5"><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-1 text-sm whitespace-pre-line text-slate-800">{{ $application->{$field} }}</p></div>
                    @endif
                @endforeach
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card p-6">
                <h2 class="text-sm font-semibold text-slate-900">Documents</h2>
                <ul class="mt-4 space-y-2">
                    @foreach ($application->documents as $doc)
                        <li>
                            <a href="{{ route('documents.show', $doc) }}" target="_blank" class="flex items-center gap-3 rounded-lg p-2 transition hover:bg-slate-50">
                                <span class="grid size-9 place-items-center rounded-lg bg-slate-100 text-slate-500"><x-icon name="file" class="size-4" /></span>
                                <span class="min-w-0 text-sm">
                                    <span class="block truncate font-medium text-slate-900">{{ $doc->original_name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $doc->typeLabel() }} · {{ Number::fileSize($doc->size) }}</span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
            @unless ($application->status->isClosed() || $application->status === $S::Offered)
                <section class="card p-6">
                    <h2 class="text-sm font-semibold text-slate-900">Withdraw application</h2>
                    <p class="mt-1 text-sm text-slate-500">You'll be removed from this vacancy's ranking.</p>
                    <button type="button" class="btn btn-secondary mt-4 w-full text-rose-600" x-data x-on:click="$dispatch('open-modal', 'withdraw')">Withdraw</button>
                </section>
                <x-modal name="withdraw" title="Withdraw your application?">
                    <p class="text-sm text-slate-600">You won't be considered for {{ $posting->title }} and can't re-apply to this vacancy.</p>
                    <form method="POST" action="{{ route('applicant.applications.withdraw', $application) }}" class="mt-6 flex justify-end gap-3">@csrf
                        <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal')">Keep application</button>
                        <button class="btn btn-danger"><span class="btn-label">Withdraw</span></button>
                    </form>
                </x-modal>
            @endunless
        </aside>
    </div>
</x-layouts.app>
