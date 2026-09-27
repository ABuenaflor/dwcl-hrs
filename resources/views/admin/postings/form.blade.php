<x-layouts.app :title="$posting->exists ? 'Edit vacancy' : 'Post a vacancy'">
    <x-page-header :title="$posting->exists ? 'Edit vacancy' : 'Post a vacancy'" :back="route('admin.postings.index')">
        @if ($posting->exists)
            <button type="button" class="btn btn-secondary text-rose-600" x-data x-on:click="$dispatch('open-modal', 'delete-posting')"><x-icon name="trash" class="size-4" /> Delete</button>
        @endif
    </x-page-header>

    @php
        $weights = old('criteria', $criteria->map(fn ($c) => ['key' => $c->key, 'weight' => round($c->weight * 100, 2)])->values()->all());
        $quals = old('qualifications', $posting->qualifications ?: ['']);
    @endphp

    <form method="POST" action="{{ $posting->exists ? route('admin.postings.update', $posting) : route('admin.postings.store') }}"
          class="grid gap-6 lg:grid-cols-3"
          x-data="{ weights: @js(array_map(fn ($w) => (float) $w['weight'], $weights)), quals: @js(array_values($quals)), campus: '{{ old('campus_id', $posting->campus_id) }}',
                    get sum() { return Math.round(this.weights.reduce((a, b) => a + (parseFloat(b) || 0), 0) * 100) / 100 } }">
        @csrf
        @if ($posting->exists) @method('PUT') @endif

        <div class="space-y-6 lg:col-span-2">
            <section class="card space-y-5 p-6">
                <h2 class="text-base font-semibold text-slate-900">Position</h2>
                <x-field name="title" label="Job title" :value="$posting->title" placeholder="e.g. Instructor — Computer Science" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field name="campus_id" label="Campus" type="select" :value="$posting->campus_id" :options="$campuses->pluck('name', 'id')" x-model="campus" required />
                    <x-field name="department_id" label="Department / office" required>
                        <select id="f_department_id" name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                            <option value="">Select…</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" x-show="! campus || campus == '{{ $d->campus_id }}'" @selected(old('department_id', $posting->department_id) == $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </x-field>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-field name="category" label="Institutional role" type="select" :value="$posting->category" :placeholder="false" :options="collect(App\Enums\JobCategory::cases())->mapWithKeys(fn ($e) => [$e->value => $e->label()])" required />
                    <x-field name="employment_type" label="Employment" type="select" :value="$posting->employment_type" :placeholder="false" :options="collect(App\Enums\EmploymentType::cases())->mapWithKeys(fn ($e) => [$e->value => $e->label()])" required />
                    <x-field name="slots" label="Openings" type="number" min="1" max="100" :value="$posting->slots" required />
                </div>
                <x-field name="schedule" label="Work schedule" :value="$posting->schedule" placeholder="e.g. Mon–Fri, 8:00 AM – 5:00 PM" />
                <x-field name="description" label="Description" type="textarea" rows="5" :value="$posting->description" />
            </section>

            <section class="card p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-900">Qualifications</h2>
                    <button type="button" class="btn btn-secondary btn-sm" x-on:click="quals.push(''); $nextTick(() => $el.closest('section').querySelector('li:last-child input').focus())"><x-icon name="plus" class="size-4" /> Add</button>
                </div>
                <ul class="mt-4 space-y-2">
                    <template x-for="(q, i) in quals" :key="i">
                        <li class="flex items-center gap-2">
                            <span class="grid size-6 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600"><x-icon name="check" class="size-3.5" /></span>
                            <input type="text" name="qualifications[]" x-model="quals[i]" class="form-control" placeholder="e.g. LET passer">
                            <button type="button" class="btn-ghost rounded-lg p-2 text-slate-400 hover:text-rose-600" x-on:click="quals.splice(i, 1)" x-show="quals.length > 1" aria-label="Remove"><x-icon name="x" class="size-4" /></button>
                        </li>
                    </template>
                </ul>
                @error('qualifications')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card space-y-5 p-6">
                <h2 class="text-base font-semibold text-slate-900">Publishing</h2>
                <x-field name="status" label="Status" type="select" :value="$posting->status" :placeholder="false" :options="collect(App\Enums\PostingStatus::cases())->mapWithKeys(fn ($e) => [$e->value => $e->label()])" required />
                <x-field name="closes_at" label="Accept applications until" type="date" :value="$posting->closes_at" hint="Leave blank to keep open until filled." />
            </section>

            <section class="card p-6">
                <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900"><x-icon name="scale" class="size-5 text-brand-700" /> SAW criteria weights</h2>
                <p class="mt-1 text-xs text-slate-500">How much each criterion counts toward the applicant's score. Must total 100%.</p>
                <div class="mt-5 space-y-4">
                    @foreach ($criteria as $i => $c)
                        <div>
                            <input type="hidden" name="criteria[{{ $i }}][key]" value="{{ $c->key }}">
                            <div class="flex items-center justify-between gap-2">
                                <label for="w{{ $i }}" class="text-sm font-medium text-slate-700">{{ $c->name }}
                                    <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-500 uppercase">{{ $c->source }}</span>
                                </label>
                                <div class="flex items-center gap-1">
                                    <input id="w{{ $i }}" type="number" name="criteria[{{ $i }}][weight]" x-model.number="weights[{{ $i }}]" min="0" max="100" step="1" class="form-control w-16 px-2 py-1 text-right text-sm">
                                    <span class="text-sm text-slate-400">%</span>
                                </div>
                            </div>
                            <input type="range" min="0" max="100" x-model.number="weights[{{ $i }}]" class="mt-2 w-full accent-brand-700" aria-label="{{ $c->name }} weight">
                            <p class="text-[11px] text-slate-400">{{ $c->description }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 flex items-center justify-between rounded-lg px-3 py-2 text-sm font-semibold transition-colors"
                     :class="sum === 100 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">
                    <span>Total</span><span x-text="sum + '%'"></span>
                </div>
                @error('criteria')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
            </section>

            <button class="btn btn-primary w-full py-3" :disabled="sum !== 100"><span class="btn-label">{{ $posting->exists ? 'Save & re-rank applicants' : 'Publish vacancy' }}</span></button>
        </aside>
    </form>

    @if ($posting->exists)
        <x-modal name="delete-posting" title="Delete this vacancy?">
            <p class="text-sm text-slate-600">Vacancies with applicants are closed instead of deleted so their records are kept.</p>
            <form method="POST" action="{{ route('admin.postings.destroy', $posting) }}" class="mt-6 flex justify-end gap-3">@csrf @method('DELETE')
                <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal')">Cancel</button>
                <button class="btn btn-danger"><span class="btn-label">Delete</span></button>
            </form>
        </x-modal>
    @endif
</x-layouts.app>
