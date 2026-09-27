<x-layouts.app :title="'Apply — '.$posting->title">
    @php
        $steps = ['Personal', 'Education', 'Experience', 'Documents', 'Review'];
        // Reopen on the first step that has a server-side error.
        $errorStep = collect($errors->keys())->map(fn ($k) => match (true) {
            str_starts_with($k, 'education') => 1,
            in_array(explode('.', $k)[0], ['years_experience', 'work_experience', 'trainings', 'trainings_count', 'skills', 'license', 'cover_letter']) => 2,
            in_array(explode('.', $k)[0], ['resume', 'documents']) => 3,
            default => 0,
        })->min() ?? 0;
    @endphp

    <x-page-header :title="$posting->title" :subtitle="$posting->department->name.' · '.$posting->campus->name" :back="route('careers.show', $posting)" />

    <form method="POST" action="{{ route('applicant.apply.store', $posting) }}" enctype="multipart/form-data" novalidate
          x-data="{
              step: {{ $errorStep }},
              total: {{ count($steps) }},
              docs: [],
              go(n) {
                  if (n > this.step && ! this.valid()) return;
                  this.step = n;
                  window.scrollTo({ top: 0, behavior: 'smooth' });
              },
              valid() {
                  const fields = [...this.$refs['step' + this.step].querySelectorAll('input, select, textarea')];
                  const bad = fields.find((f) => ! f.checkValidity());
                  if (bad) { bad.reportValidity(); return false; }
                  return true;
              },
          }"
          x-on:submit="if (! valid()) $event.preventDefault()">
        @csrf

        <div class="card mb-6 px-6 py-5">
            <ol class="flex w-full items-start">
                @foreach ($steps as $i => $label)
                    <li class="relative flex flex-1 flex-col items-center">
                        @if ($i > 0)<span class="absolute top-3.5 right-1/2 left-[-50%] h-0.5 bg-slate-200"><span class="block h-full bg-brand-600 transition-all duration-500" :style="'width:' + (step >= {{ $i }} ? 100 : 0) + '%'"></span></span>@endif
                        <button type="button" x-on:click="go({{ $i }})" class="relative z-[1] grid size-8 place-items-center rounded-full text-xs font-semibold ring-4 ring-white transition-all duration-300"
                                :class="step > {{ $i }} ? 'bg-brand-700 text-white' : (step === {{ $i }} ? 'bg-gold-500 text-brand-950 scale-110' : 'bg-slate-200 text-slate-500')">
                            <span x-show="step <= {{ $i }}">{{ $i + 1 }}</span>
                            <x-icon name="check" class="size-4" x-show="step > {{ $i }}" x-cloak />
                        </button>
                        <span class="mt-2 hidden text-xs font-medium sm:block" :class="step === {{ $i }} ? 'text-slate-900' : 'text-slate-500'">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="card p-6 sm:p-8">
            <section x-ref="step0" x-show="step === 0" x-transition:enter="transition duration-300 ease-(--ease-out-soft)" x-transition:enter-start="opacity-0 translate-x-4">
                <h2 class="text-lg font-semibold text-slate-900">Personal information</h2>
                <p class="mb-6 text-sm text-slate-500">Pre-filled from your profile — changes here update it too.</p>
                @include('applicant._personal')
            </section>

            <section x-ref="step1" x-show="step === 1" x-cloak x-transition:enter="transition duration-300 ease-(--ease-out-soft)" x-transition:enter-start="opacity-0 translate-x-4">
                <h2 class="text-lg font-semibold text-slate-900">Educational background</h2>
                <p class="mb-6 text-sm text-slate-500">Your highest level completed counts toward the <em>Educational Attainment</em> criterion.</p>
                @include('applicant._education')
            </section>

            <section x-ref="step2" x-show="step === 2" x-cloak x-transition:enter="transition duration-300 ease-(--ease-out-soft)" x-transition:enter-start="opacity-0 translate-x-4" class="space-y-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Experience &amp; qualifications</h2>
                    <p class="text-sm text-slate-500">Be accurate — HRDO verifies these against your documents.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-field name="years_experience" label="Years of relevant experience" type="number" step="0.5" min="0" max="60" value="0" required />
                    <x-field name="trainings_count" label="Seminars / trainings attended" type="number" min="0" max="200" value="0" required />
                    <x-field name="license" label="Professional license" placeholder="e.g. LET Passer, RN, CPA" />
                </div>
                <x-field name="work_experience" label="Work experience" type="textarea" placeholder="Position, employer and years — one per line." />
                <x-field name="trainings" label="Seminars & trainings" type="textarea" placeholder="Title, organizer, date — one per line." />
                <x-field name="skills" label="Special skills" type="textarea" rows="3" placeholder="e.g. Laravel, statistics, curriculum design…" />
                <x-field name="cover_letter" label="Cover letter" type="textarea" rows="5" hint="Optional." />
            </section>

            <section x-ref="step3" x-show="step === 3" x-cloak x-transition:enter="transition duration-300 ease-(--ease-out-soft)" x-transition:enter-start="opacity-0 translate-x-4" class="space-y-5">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Documents</h2>
                    <p class="text-sm text-slate-500">PDF, JPG or PNG, up to 5 MB each. Certificates add to the <em>Trainings &amp; Certificates</em> criterion.</p>
                </div>

                <x-field name="resume" label="Résumé / CV" required>
                    <label class="flex cursor-pointer items-center gap-4 rounded-xl border-2 border-dashed border-slate-300 p-5 transition hover:border-brand-400 hover:bg-brand-50/40"
                           x-data="{ name: '' }">
                        <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-icon name="upload" /></span>
                        <span class="flex-1 text-sm">
                            <span class="block font-medium text-slate-900" x-text="name || 'Choose your résumé'"></span>
                            <span class="block text-slate-500">PDF or Word, max 5 MB</span>
                        </span>
                        <input type="file" name="resume" accept=".pdf,.doc,.docx" required class="sr-only" x-on:change="name = $event.target.files[0]?.name ?? ''">
                    </label>
                </x-field>

                <div>
                    <div class="flex items-center justify-between">
                        <p class="form-label mb-0">Supporting documents</p>
                        <button type="button" class="btn btn-secondary btn-sm" x-on:click="docs.push(Date.now())" x-bind:disabled="docs.length >= 10"><x-icon name="plus" class="size-4" /> Add document</button>
                    </div>
                    <div class="mt-3 space-y-3">
                        <template x-for="(id, i) in docs" :key="id">
                            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 p-3 sm:flex-row sm:items-center"
                                 x-transition:enter="transition duration-300" x-transition:enter-start="opacity-0 -translate-y-2">
                                <select :name="`documents[${i}][type]`" class="form-control sm:w-56" required>
                                    @foreach (App\Models\ApplicationDocument::TYPES as $value => $label)
                                        @continue($value === 'resume')
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="file" :name="`documents[${i}][file]`" accept=".pdf,.jpg,.jpeg,.png" required
                                       class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100">
                                <button type="button" class="btn-ghost rounded-lg p-2 text-slate-400 hover:text-rose-600" x-on:click="docs.splice(i, 1)" aria-label="Remove"><x-icon name="trash" class="size-4" /></button>
                            </div>
                        </template>
                        <p x-show="! docs.length" class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">Transcript, PRC license, certificates of trainings — add as many as apply.</p>
                    </div>
                    @error('documents.*')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </section>

            <section x-ref="step4" x-show="step === 4" x-cloak x-transition:enter="transition duration-300 ease-(--ease-out-soft)" x-transition:enter-start="opacity-0 translate-x-4">
                <h2 class="text-lg font-semibold text-slate-900">Review &amp; submit</h2>
                <p class="text-sm text-slate-500">You're applying for:</p>
                <div class="mt-4 rounded-xl border border-brand-100 bg-brand-50/50 p-5">
                    <p class="font-semibold text-slate-900">{{ $posting->title }}</p>
                    <p class="text-sm text-slate-600">{{ $posting->department->name }} · {{ $posting->campus->name }} · {{ $posting->employment_type->label() }}</p>
                </div>
                <ul class="mt-6 space-y-2 text-sm text-slate-600">
                    <li class="flex gap-2"><x-icon name="check-circle" class="size-4 shrink-0 text-emerald-600" /> Your profile will be updated with the details you entered.</li>
                    <li class="flex gap-2"><x-icon name="check-circle" class="size-4 shrink-0 text-emerald-600" /> You'll be notified at every stage: shortlist, interview and offer.</li>
                    <li class="flex gap-2"><x-icon name="check-circle" class="size-4 shrink-0 text-emerald-600" /> You can withdraw at any time before a final decision.</li>
                </ul>
                <label class="mt-6 flex items-start gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700">
                    <input type="checkbox" required class="mt-0.5 rounded border-slate-300 text-brand-700 focus:ring-brand-500">
                    I certify that the information is true and correct, and I consent to DWCL processing it for recruitment under the Data Privacy Act of 2012.
                </label>
            </section>

            <div class="mt-8 flex items-center justify-between border-t border-slate-100 pt-6">
                <button type="button" class="btn btn-ghost" x-show="step > 0" x-on:click="go(step - 1)"><x-icon name="arrow-left" class="size-4" /> Back</button>
                <span x-show="step === 0"></span>
                <button type="button" class="btn btn-primary" x-show="step < total - 1" x-on:click="go(step + 1)">Continue <x-icon name="arrow-right" class="size-4" /></button>
                <button type="submit" class="btn btn-gold" x-show="step === total - 1" x-cloak><span class="btn-label">Submit application</span></button>
            </div>
        </div>
    </form>
</x-layouts.app>
