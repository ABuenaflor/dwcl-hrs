<x-layouts.auth title="Employee access" heading="Request an employee account" subheading="The HRDO verifies your employment before activating the account. You'll get access to faculty self-rating once approved." wide>
    <form method="POST" action="{{ route('register.employee') }}" class="space-y-5"
          x-data="{ campus: '{{ old('campus_id') }}' }">
        @csrf
        <div class="grid gap-4 sm:grid-cols-3">
            <x-field name="first_name" label="First name" required autofocus />
            <x-field name="middle_name" label="Middle name" />
            <x-field name="last_name" label="Last name" required />
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="email" label="Institutional email" type="email" required />
            <x-field name="employee_no" label="Employee number" placeholder="e.g. DWCL-0123" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="campus_id" label="Campus" type="select" :options="$campuses->pluck('name', 'id')" x-model="campus" required />
            <x-field name="department_id" label="Department" required>
                <select id="f_department_id" name="department_id" class="form-control @error('department_id') is-invalid @enderror" required>
                    <option value="">Select…</option>
                    @foreach ($departments as $d)
                        <option value="{{ $d->id }}" data-campus="{{ $d->campus_id }}" x-show="! campus || campus == '{{ $d->campus_id }}'" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </x-field>
        </div>
        <div class="grid gap-4 sm:grid-cols-3">
            <x-field name="employment_type" label="Employment" type="select" :options="collect(App\Enums\EmploymentType::cases())->mapWithKeys(fn ($e) => [$e->value => $e->label()])" required />
            <x-field name="designation" label="Designation" placeholder="e.g. Instructor" />
            <x-field name="date_hired" label="Date hired" type="date" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="password" label="Password" type="password" autocomplete="new-password" required />
            <x-field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" required />
        </div>
        <button class="btn btn-primary w-full py-3"><span class="btn-label">Submit request</span></button>
    </form>
    <p class="mt-6 text-sm text-slate-500">Already approved? <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:underline">Sign in</a></p>
</x-layouts.auth>
