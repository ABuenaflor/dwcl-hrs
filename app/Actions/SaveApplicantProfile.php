<?php

namespace App\Actions;

use App\Enums\EducationLevel;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Personal data sheet + educational background, shared by the profile page
 * and the application wizard so applicants only ever type it once.
 */
class SaveApplicantProfile
{
    public static function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:80'],
            'middle_name' => ['nullable', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'profile.date_of_birth' => ['required', 'date', 'before:-15 years'],
            'profile.place_of_birth' => ['nullable', 'string', 'max:255'],
            'profile.sex' => ['required', Rule::in(['male', 'female'])],
            'profile.civil_status' => ['required', Rule::in(['single', 'married', 'widowed', 'separated'])],
            'profile.citizenship' => ['required', 'string', 'max:60'],
            'profile.religion' => ['nullable', 'string', 'max:60'],
            'profile.height_cm' => ['nullable', 'numeric', 'between:50,250'],
            'profile.weight_kg' => ['nullable', 'numeric', 'between:20,300'],
            'profile.blood_type' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'profile.pagibig_no' => ['nullable', 'string', 'max:30'],
            'profile.philhealth_no' => ['nullable', 'string', 'max:30'],
            'profile.tin_no' => ['nullable', 'string', 'max:30'],
            'profile.sss_no' => ['nullable', 'string', 'max:30'],
            'profile.contact_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,}$/'],
            'profile.residential_address' => ['required', 'string', 'max:255'],
            'profile.permanent_address' => ['nullable', 'string', 'max:255'],
            'profile.father_name' => ['nullable', 'string', 'max:150'],
            'profile.mother_maiden_name' => ['nullable', 'string', 'max:150'],
        ];

        foreach (EducationLevel::cases() as $level) {
            $key = "education.{$level->value}";
            $required = $level === EducationLevel::Secondary ? 'required' : 'nullable';
            $rules["{$key}.school"] = [$required, 'string', 'max:150'];
            $rules["{$key}.course"] = ['nullable', 'string', 'max:150'];
            $rules["{$key}.inclusive_dates"] = ['nullable', 'string', 'max:40'];
            $rules["{$key}.year_graduated"] = ['nullable', 'integer', 'between:1950,'.(date('Y') + 1)];
            $rules["{$key}.honors"] = ['nullable', 'string', 'max:150'];
        }

        return $rules;
    }

    public static function attributes(): array
    {
        return [
            'profile.date_of_birth' => 'date of birth',
            'profile.contact_number' => 'contact number',
            'profile.residential_address' => 'residential address',
            'education.secondary.school' => 'secondary school',
        ];
    }

    public function __invoke(User $user, array $data): void
    {
        $user->update(collect($data)->only(['first_name', 'middle_name', 'last_name'])->all());
        $user->profile()->updateOrCreate([], $data['profile']);

        foreach (EducationLevel::cases() as $level) {
            $row = $data['education'][$level->value] ?? [];

            if (blank($row['school'] ?? null)) {
                $user->educations()->where('level', $level)->delete();

                continue;
            }

            $user->educations()->updateOrCreate(['level' => $level], $row);
        }
    }
}
