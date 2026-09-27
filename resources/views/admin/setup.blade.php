<x-layouts.app title="Campuses & departments">
    <x-page-header title="Campuses & departments" subtitle="The level of a department decides which ranking rubric its faculty use." />

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card p-6">
            <h2 class="text-base font-semibold text-slate-900">Campuses</h2>
            <ul class="mt-4 space-y-2">
                @foreach ($campuses as $c)
                    <li x-data="{ edit: false }" class="rounded-xl border border-slate-200 p-3">
                        <div x-show="! edit" class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-medium text-slate-900">{{ $c->name }}</p>
                                <p class="text-xs text-slate-500">{{ $c->description }} · {{ $c->departments_count }} {{ Str::plural('department', $c->departments_count) }}</p>
                            </div>
                            <button type="button" class="btn btn-ghost btn-sm" x-on:click="edit = true"><x-icon name="pencil" class="size-4" /></button>
                        </div>
                        <form x-show="edit" x-cloak method="POST" action="{{ route('admin.setup.campuses.update', $c) }}" class="space-y-2">@csrf @method('PUT')
                            <input name="name" value="{{ $c->name }}" class="form-control" required>
                            <input name="description" value="{{ $c->description }}" class="form-control" placeholder="Description">
                            <div class="flex justify-end gap-2"><button type="button" class="btn btn-ghost btn-sm" x-on:click="edit = false">Cancel</button><button class="btn btn-primary btn-sm">Save</button></div>
                        </form>
                    </li>
                @endforeach
            </ul>
            <form method="POST" action="{{ route('admin.setup.campuses.store') }}" class="mt-4 space-y-2 border-t border-slate-100 pt-4">@csrf
                <p class="text-sm font-medium text-slate-700">Add campus</p>
                <input name="name" class="form-control" placeholder="Campus name" required>
                <input name="description" class="form-control" placeholder="Description (optional)">
                <button class="btn btn-secondary w-full"><x-icon name="plus" class="size-4" /> Add campus</button>
            </form>
        </section>

        <section class="card overflow-hidden lg:col-span-2">
            <div class="border-b border-slate-100 p-6">
                <h2 class="text-base font-semibold text-slate-900">Departments &amp; offices</h2>
                <form method="POST" action="{{ route('admin.setup.departments.store') }}" class="mt-4 grid gap-2 sm:grid-cols-12">@csrf
                    <input name="name" class="form-control sm:col-span-4" placeholder="Department name" required>
                    <input name="code" class="form-control sm:col-span-2" placeholder="Code">
                    <select name="campus_id" class="form-control sm:col-span-2" required>
                        @foreach ($campuses as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                    <select name="level" class="form-control sm:col-span-2" required>
                        @foreach (App\Enums\Level::cases() as $l)<option value="{{ $l->value }}">{{ $l->label() }}</option>@endforeach
                    </select>
                    <button class="btn btn-primary sm:col-span-2"><x-icon name="plus" class="size-4" /> Add</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="table-clean">
                    <thead><tr><th>Name</th><th>Campus</th><th>Level</th><th class="text-center">People</th><th class="text-center">Vacancies</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($departments as $d)
                            <tr x-data="{ edit: false }">
                                <td>
                                    <template x-if="! edit"><div><p class="font-medium text-slate-900">{{ $d->name }}</p>@if ($d->code)<p class="text-xs text-slate-500">{{ $d->code }}</p>@endif</div></template>
                                    <template x-if="edit">
                                        <form id="dept-{{ $d->id }}" method="POST" action="{{ route('admin.setup.departments.update', $d) }}" class="grid gap-2">@csrf @method('PUT')
                                            <input name="name" value="{{ $d->name }}" class="form-control" required>
                                            <input name="code" value="{{ $d->code }}" class="form-control" placeholder="Code">
                                        </form>
                                    </template>
                                </td>
                                <td>
                                    <span x-show="! edit" class="text-slate-600">{{ $d->campus?->name }}</span>
                                    <select x-show="edit" x-cloak form="dept-{{ $d->id }}" name="campus_id" class="form-control">
                                        @foreach ($campuses as $c)<option value="{{ $c->id }}" @selected($d->campus_id === $c->id)>{{ $c->name }}</option>@endforeach
                                    </select>
                                </td>
                                <td>
                                    <span x-show="! edit"><x-badge :tone="match ($d->level) { App\Enums\Level::BasicEd => 'gold', App\Enums\Level::Tertiary => 'brand', default => 'neutral' }">{{ $d->level->label() }}</x-badge></span>
                                    <select x-show="edit" x-cloak form="dept-{{ $d->id }}" name="level" class="form-control">
                                        @foreach (App\Enums\Level::cases() as $l)<option value="{{ $l->value }}" @selected($d->level === $l)>{{ $l->label() }}</option>@endforeach
                                    </select>
                                </td>
                                <td class="text-center text-slate-600 tabular-nums">{{ $d->users_count }}</td>
                                <td class="text-center text-slate-600 tabular-nums">{{ $d->job_postings_count }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <span x-show="! edit">
                                        <button type="button" class="btn btn-ghost btn-sm" x-on:click="edit = true" title="Edit"><x-icon name="pencil" class="size-4" /></button>
                                        @if (! $d->users_count && ! $d->job_postings_count)
                                            <form method="POST" action="{{ route('admin.setup.departments.destroy', $d) }}" class="inline" x-on:submit="if (! confirm('Delete {{ addslashes($d->name) }}?')) $event.preventDefault()">@csrf @method('DELETE')
                                                <button class="btn btn-ghost btn-sm text-slate-400 hover:text-rose-600" title="Delete"><x-icon name="trash" class="size-4" /></button>
                                            </form>
                                        @endif
                                    </span>
                                    <span x-show="edit" x-cloak>
                                        <button type="button" class="btn btn-ghost btn-sm" x-on:click="edit = false">Cancel</button>
                                        <button form="dept-{{ $d->id }}" class="btn btn-primary btn-sm">Save</button>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-layouts.app>
