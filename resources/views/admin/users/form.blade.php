<x-layouts.app :title="$user->exists ? 'Edit account' : 'New account'">
    <x-page-header :title="$user->exists ? $user->name : 'New account'" :subtitle="$user->exists ? $user->email : 'Create an HRDO, committee or faculty account.'" :back="route('admin.users.index')" />

    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="grid gap-6 lg:grid-cols-3"
          x-data="{ role: '{{ old('role', $user->role?->value) }}', campus: '{{ old('campus_id', $user->campus_id) }}' }">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div class="space-y-6 lg:col-span-2">
            <section class="card space-y-5 p-6">
                <h2 class="text-base font-semibold text-slate-900">Identity</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-field name="first_name" label="First name" :value="$user->first_name" required />
                    <x-field name="middle_name" label="Middle name" :value="$user->middle_name" />
                    <x-field name="last_name" label="Last name" :value="$user->last_name" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field name="email" label="Email" type="email" :value="$user->email" required />
                    <x-field name="phone" label="Phone" :value="$user->phone" />
                </div>
            </section>

            <section class="card space-y-5 p-6" x-show="role !== 'applicant'" x-collapse>
                <h2 class="text-base font-semibold text-slate-900">Employment</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field name="employee_no" label="Employee number" :value="$user->employee_no" />
                    <x-field name="designation" label="Designation" :value="$user->designation" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-field name="campus_id" label="Campus" type="select" :value="$user->campus_id" :options="$campuses->pluck('name', 'id')" x-model="campus" />
                    <x-field name="department_id" label="Department">
                        <select id="f_department_id" name="department_id" class="form-control @error('department_id') is-invalid @enderror">
                            <option value="">None</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" x-show="! campus || campus == '{{ $d->campus_id }}'" @selected(old('department_id', $user->department_id) == $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </x-field>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-field name="employment_type" label="Employment" type="select" :value="$user->employment_type" :options="collect(App\Enums\EmploymentType::cases())->mapWithKeys(fn ($e) => [$e->value => $e->label()])" />
                    <x-field name="academic_rank_id" label="Current academic rank" type="select" :value="$user->academic_rank_id" placeholder="None"
                             :options="$ranks->mapWithKeys(fn ($r) => [$r->id => $r->name.' ('.$r->level->label().')'])" />
                    <x-field name="date_hired" label="Date hired" type="date" :value="$user->date_hired" />
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card space-y-5 p-6">
                <h2 class="text-base font-semibold text-slate-900">Access</h2>
                <x-field name="role" label="Role" type="select" :value="$user->role" :placeholder="false" x-model="role"
                         :options="collect(App\Enums\Role::cases())->mapWithKeys(fn ($r) => [$r->value => $r->label()])" required />
                <x-field name="status" label="Status" type="select" :value="$user->status" :placeholder="false"
                         :options="collect(App\Enums\AccountStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])" required />
            </section>
            <section class="card space-y-5 p-6">
                <h2 class="text-base font-semibold text-slate-900">{{ $user->exists ? 'Reset password' : 'Password' }}</h2>
                <x-field name="password" label="Password" type="password" autocomplete="new-password" :required="! $user->exists" :hint="$user->exists ? 'Leave blank to keep the current password.' : null" />
                <x-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" :required="! $user->exists" />
            </section>
            <button class="btn btn-primary w-full py-3"><span class="btn-label">{{ $user->exists ? 'Save changes' : 'Create account' }}</span></button>
        </aside>
    </form>
</x-layouts.app>
