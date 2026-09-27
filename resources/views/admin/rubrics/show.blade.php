<x-layouts.app :title="$rubric->name">
    @php $tertiary = $rubric->level === App\Enums\Level::Tertiary; @endphp
    <x-page-header :title="$rubric->name" :subtitle="$rubric->level->label().' · '.$rubric->description" :back="route('admin.rubrics.index')">
        <button type="button" class="btn btn-primary" x-data x-on:click="$dispatch('edit-item', { parent_id: '' })"><x-icon name="plus" class="size-4" /> Add criterion</button>
    </x-page-header>

    @if ($inUse)
        <div class="card mb-6 flex gap-3 border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-900">
            <x-icon name="alert" class="size-5 shrink-0" />
            Rankings using this rubric are under review. Editing points or caps changes their recalculated totals the next time a committee saves.
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-clean">
                <thead>
                    <tr>
                        <th>Criterion</th>
                        <th class="text-right">{{ $tertiary ? 'In field' : 'Weight %' }}</th>
                        <th class="text-right">{{ $tertiary ? 'Related' : 'Credit pts' }}</th>
                        <th class="text-right">Max (cap)</th>
                        <th class="text-center">Scored</th>
                        <th class="w-28"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tree as $node)
                        @include('admin.rubrics._node', ['node' => $node, 'depth' => 0])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- One modal for add + edit; the action URL switches with the item. --}}
    <div x-data="{
            item: {},
            get editing() { return !! this.item.id },
            get action() { return this.editing ? '{{ url('admin/rubric-items') }}/' + this.item.id : '{{ route('admin.rubrics.items.store', $rubric) }}' },
         }"
         x-on:edit-item.window="item = { is_scorable: true, ...$event.detail }; $dispatch('open-modal', 'item')">
        <x-modal name="item" title="Criterion" max-width="max-w-2xl">
            <form method="POST" :action="action" class="space-y-4">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
                <div class="grid gap-4 sm:grid-cols-4">
                    <div><label class="form-label">Code</label><input name="code" x-model="item.code" class="form-control" placeholder="1.2.3"></div>
                    <div class="sm:col-span-3"><label class="form-label">Title <span class="text-rose-500">*</span></label><input name="title" x-model="item.title" class="form-control" required></div>
                </div>
                <div><label class="form-label">Parent</label>
                    <select name="parent_id" x-model="item.parent_id" class="form-control">
                        <option value="">— Top level —</option>
                        @foreach ($parents as $p)<option value="{{ $p->id }}">{{ $p->code ? $p->code.' ' : '' }}{{ Str::limit($p->title, 70) }}</option>@endforeach
                    </select>
                </div>
                <div><label class="form-label">Scoring guide</label><input name="guide" x-model="item.guide" class="form-control" placeholder="e.g. 2 pts / 8 hrs"></div>
                <div class="grid gap-4 sm:grid-cols-3">
                    @if ($tertiary)
                        <div><label class="form-label">Credit — in field</label><input type="number" step="0.01" name="credit_in_field" x-model="item.credit_in_field" class="form-control"></div>
                        <div><label class="form-label">Credit — related</label><input type="number" step="0.01" name="credit_related" x-model="item.credit_related" class="form-control"></div>
                    @else
                        <div><label class="form-label">Weight %</label><input type="number" step="0.01" name="weight_percent" x-model="item.weight_percent" class="form-control"></div>
                        <div><label class="form-label">Credit points</label><input type="number" step="0.01" name="credit_points" x-model="item.credit_points" class="form-control"></div>
                    @endif
                    <div><label class="form-label">Max points (cap)</label><input type="number" step="0.01" name="max_points" x-model="item.max_points" class="form-control"></div>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="is_scorable" value="0">
                    <input type="checkbox" name="is_scorable" value="1" x-model="item.is_scorable" class="rounded border-slate-300 text-brand-700 focus:ring-brand-500">
                    Faculty enter points on this row (uncheck for headings that only total their children)
                </label>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close-modal')">Cancel</button>
                    <button class="btn btn-primary"><span class="btn-label" x-text="editing ? 'Save changes' : 'Add criterion'"></span></button>
                </div>
            </form>
        </x-modal>
    </div>
</x-layouts.app>
