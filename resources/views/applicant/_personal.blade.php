{{-- Personal data sheet. Expects $user with profile loaded. --}}
@php $p = $user->profile; @endphp
<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-3">
        <x-field name="first_name" label="First name" :value="$user->first_name" required />
        <x-field name="middle_name" label="Middle name" :value="$user->middle_name" />
        <x-field name="last_name" label="Last name" :value="$user->last_name" required />
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-field name="profile[date_of_birth]" label="Date of birth" type="date" :value="$p?->date_of_birth" required />
        <x-field name="profile[place_of_birth]" label="Place of birth" :value="$p?->place_of_birth" />
        <x-field name="profile[sex]" label="Sex" type="select" :value="$p?->sex" :options="['male' => 'Male', 'female' => 'Female']" required />
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-field name="profile[civil_status]" label="Civil status" type="select" :value="$p?->civil_status" :options="['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'separated' => 'Separated']" required />
        <x-field name="profile[citizenship]" label="Citizenship" :value="$p?->citizenship ?? 'Filipino'" required />
        <x-field name="profile[religion]" label="Religion" :value="$p?->religion" />
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-field name="profile[height_cm]" label="Height (cm)" type="number" step="0.1" :value="$p?->height_cm" />
        <x-field name="profile[weight_kg]" label="Weight (kg)" type="number" step="0.1" :value="$p?->weight_kg" />
        <x-field name="profile[blood_type]" label="Blood type" type="select" :value="$p?->blood_type" :options="array_combine($bt = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], $bt)" />
    </div>

    <div class="border-t border-slate-100 pt-6">
        <h3 class="text-sm font-semibold text-slate-900">Contact</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            <x-field name="profile[contact_number]" label="Mobile number" type="tel" :value="$p?->contact_number" placeholder="09XX XXX XXXX" required />
            <x-field class="sm:col-span-2" name="profile[residential_address]" label="Residential address" :value="$p?->residential_address" required />
        </div>
        <x-field class="mt-4" name="profile[permanent_address]" label="Permanent address" :value="$p?->permanent_address" hint="Leave blank if the same as residential." />
    </div>

    <div class="border-t border-slate-100 pt-6">
        <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-900">Government IDs <x-badge tone="success" :dot="false"><x-icon name="shield" class="size-3" /> Encrypted</x-badge></h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-4">
            <x-field name="profile[sss_no]" label="SSS" :value="$p?->sss_no" />
            <x-field name="profile[tin_no]" label="TIN" :value="$p?->tin_no" />
            <x-field name="profile[philhealth_no]" label="PhilHealth" :value="$p?->philhealth_no" />
            <x-field name="profile[pagibig_no]" label="Pag-IBIG" :value="$p?->pagibig_no" />
        </div>
    </div>

    <div class="border-t border-slate-100 pt-6">
        <h3 class="text-sm font-semibold text-slate-900">Family background</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <x-field name="profile[father_name]" label="Father's full name" :value="$p?->father_name" />
            <x-field name="profile[mother_maiden_name]" label="Mother's maiden name" :value="$p?->mother_maiden_name" />
        </div>
    </div>
</div>
