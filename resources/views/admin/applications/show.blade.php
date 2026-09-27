<x-layouts.app :title="$application->user->name">
    @php
        $user = $application->user;
        $p = $user->profile;
        $posting = $application->jobPosting;
        $scores = $application->scores->keyBy('saw_criterion_id');
        $next = $application->status->nextForHr();
    @endphp

    <x-page-header :title="$user->full_name" :subtitle="'Applied for '.$posting->title.' · '.$application->created_at->format('M j, Y')" :back="route('admin.applications.index', ['posting' => $posting->id])">
        <a href="{{ route('admin.postings.ranking', $posting) }}" class="btn btn-secondary"><x-icon name="scale" class="size-4" /> Vacancy ranking</a>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- SAW summary --}}
            <section class="card overflow-hidden">
                <div class="flex flex-wrap items-center gap-6 bg-gradient-to-r from-brand-800 to-brand-950 p-6 text-white">
                    <div class="grid size-20 place-items-center rounded-2xl bg-white/10 text-center ring-1 ring-white/15">
                        <span class="text-[10px] font-semibold tracking-wider text-brand-200 uppercase">Rank</span>
                        <span class="-mt-2 text-3xl font-bold">{{ $application->saw_rank ?? '—' }}</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-brand-200">SAW preference value</p>
                        <p class="text-3xl font-semibold tabular-nums">{{ $application->saw_score !== null ? number_format($application->saw_score, 4) : '—' }}</p>
                        <p class="text-sm text-brand-200">out of {{ $applicantCount }} active {{ Str::plural('applicant', $applicantCount) }}</p>
                    </div>
                    <x-badge :tone="$application->status->tone()" class="px-3 py-1 text-sm">{{ $application->status->label() }}</x-badge>
                </div>

                <form method="POST" action="{{ route('admin.applications.scores', $application) }}" class="p-6">
                    @csrf @method('PUT')
                    <h2 class="text-sm font-semibold text-slate-900">Criteria</h2>
                    <p class="text-xs text-slate-500">Auto criteria come from the application. Rate the manual ones after screening / interview (0–10).</p>
                    <div class="mt-4 divide-y divide-slate-100">
                        @foreach ($criteria as $c)
                            <div class="flex items-center gap-4 py-3">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900">{{ $c->name }} <span class="ml-1 text-xs font-normal text-slate-400">{{ round($c->weight * 100) }}%</span></p>
                                    <p class="text-xs text-slate-500">{{ $c->description }}</p>
                                </div>
                                @if ($c->isAuto())
                                    <span class="rounded-lg bg-slate-50 px-3 py-1.5 text-sm font-semibold text-slate-700 tabular-nums">{{ rtrim(rtrim(number_format($raw[$c->id], 2), '0'), '.') ?: 0 }}</span>
                                @else
                                    <div x-data="{ v: '{{ old("scores.{$c->id}", $scores->get($c->id)?->value) }}' }" class="flex items-center gap-2">
                                        <input type="range" min="0" max="{{ $c->scale_max ?? 10 }}" step="0.5" x-model="v" class="hidden w-28 accent-brand-700 sm:block" aria-label="{{ $c->name }}">
                                        <input type="number" name="scores[{{ $c->id }}]" x-model="v" min="0" max="{{ $c->scale_max ?? 10 }}" step="0.5" placeholder="—" class="form-control w-20 px-2 py-1.5 text-right @error("scores.{$c->id}") is-invalid @enderror">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <x-field class="mt-4" name="hr_notes" label="HR notes (internal)" type="textarea" rows="3" :value="$application->hr_notes" />
                    <div class="mt-4 flex justify-end"><button class="btn btn-primary"><span class="btn-label">Save ratings &amp; re-rank</span></button></div>
                </form>
            </section>

            {{-- Qualifications --}}
            <section class="card p-6">
                <h2 class="text-sm font-semibold text-slate-900">Qualifications</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-3">
                    <div><dt class="text-slate-500">Experience</dt><dd class="font-medium text-slate-900">{{ $application->years_experience }} years</dd></div>
                    <div><dt class="text-slate-500">Seminars / trainings</dt><dd class="font-medium text-slate-900">{{ $application->trainings_count }}</dd></div>
                    <div><dt class="text-slate-500">License</dt><dd class="font-medium text-slate-900">{{ $application->license ?: '—' }}</dd></div>
                </dl>
                <div class="mt-6">
                    <p class="text-sm text-slate-500">Education</p>
                    <ul class="mt-2 space-y-2">
                        @forelse ($user->educations as $e)
                            <li class="flex gap-3 text-sm">
                                <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-700"><x-icon name="graduation" class="size-4" /></span>
                                <span><span class="block font-medium text-slate-900">{{ $e->level->label() }}{{ $e->course ? ' — '.$e->course : '' }}</span>
                                <span class="block text-slate-500">{{ $e->school }}{{ $e->year_graduated ? ', '.$e->year_graduated : '' }}{{ $e->honors ? ' · '.$e->honors : '' }}</span></span>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">No education entered.</li>
                        @endforelse
                    </ul>
                </div>
                @foreach (['work_experience' => 'Work experience', 'trainings' => 'Seminars & trainings', 'skills' => 'Special skills', 'cover_letter' => 'Cover letter'] as $field => $label)
                    @if ($application->{$field})
                        <div class="mt-5"><p class="text-sm text-slate-500">{{ $label }}</p><p class="mt-1 text-sm whitespace-pre-line text-slate-800">{{ $application->{$field} }}</p></div>
                    @endif
                @endforeach
            </section>
        </div>

        <aside class="space-y-6">
            {{-- Pipeline --}}
            <section class="card p-6">
                <h2 class="text-sm font-semibold text-slate-900">Move application</h2>
                @if ($next)
                    <form method="POST" action="{{ route('admin.applications.status', $application) }}" class="mt-4 space-y-4" x-data="{ status: '{{ old('status', $next[0]->value) }}' }">
                        @csrf @method('PATCH')
                        <div class="grid gap-2">
                            @foreach ($next as $s)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3 text-sm transition" :class="status === '{{ $s->value }}' ? 'border-brand-600 bg-brand-50/60 ring-1 ring-brand-600' : 'border-slate-200 hover:border-slate-300'">
                                    <input type="radio" name="status" value="{{ $s->value }}" x-model="status" class="text-brand-700 focus:ring-brand-500">
                                    <span class="font-medium text-slate-900">{{ $s->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div x-show="status === 'interview'" x-collapse>
                            <x-field name="interview_at" label="Interview date & time" type="datetime-local" :value="$application->interview_at" />
                        </div>
                        <x-field name="message" label="Message to applicant" type="textarea" rows="3" placeholder="Optional — added to the notification." />
                        <button class="btn btn-primary w-full"><span class="btn-label">Update &amp; notify</span></button>
                    </form>
                @else
                    <p class="mt-2 text-sm text-slate-500">
                        {{ $application->status === App\Enums\ApplicationStatus::Offered ? 'Waiting for the applicant to accept or decline.' : 'This application is closed.' }}
                    </p>
                @endif
            </section>

            {{-- Contact --}}
            <section class="card p-6">
                <h2 class="text-sm font-semibold text-slate-900">Contact &amp; personal</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex gap-3"><x-icon name="mail" class="size-4 shrink-0 text-slate-400" /><a href="mailto:{{ $user->email }}" class="text-brand-700 hover:underline">{{ $user->email }}</a></div>
                    @if ($p?->contact_number)<div class="flex gap-3"><x-icon name="phone" class="size-4 shrink-0 text-slate-400" />{{ $p->contact_number }}</div>@endif
                    @if ($p?->residential_address)<div class="flex gap-3"><x-icon name="map-pin" class="size-4 shrink-0 text-slate-400" />{{ $p->residential_address }}</div>@endif
                </dl>
                @if ($p)
                    <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-xs">
                        @foreach (['Born' => $p->date_of_birth?->format('M j, Y').($p->date_of_birth ? ' ('.$p->date_of_birth->age.')' : ''), 'Sex' => ucfirst($p->sex ?? ''), 'Civil status' => ucfirst($p->civil_status ?? ''), 'Citizenship' => $p->citizenship] as $label => $value)
                            <div><dt class="text-slate-500">{{ $label }}</dt><dd class="font-medium text-slate-800">{{ $value ?: '—' }}</dd></div>
                        @endforeach
                    </dl>
                @endif
            </section>

            {{-- Documents --}}
            <section class="card p-6">
                <h2 class="text-sm font-semibold text-slate-900">Documents</h2>
                <ul class="mt-4 space-y-1">
                    @foreach ($application->documents as $doc)
                        <li>
                            <a href="{{ route('documents.show', $doc) }}" target="_blank" class="flex items-center gap-3 rounded-lg p-2 transition hover:bg-slate-50">
                                <span class="grid size-9 place-items-center rounded-lg bg-slate-100 text-slate-500"><x-icon name="file" class="size-4" /></span>
                                <span class="min-w-0 text-sm">
                                    <span class="block truncate font-medium text-slate-900">{{ $doc->original_name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $doc->typeLabel() }} · {{ Number::fileSize($doc->size) }}</span>
                                </span>
                                <x-icon name="external" class="ml-auto size-4 text-slate-300" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        </aside>
    </div>
</x-layouts.app>
