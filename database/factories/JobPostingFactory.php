<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\JobCategory;
use App\Enums\PostingStatus;
use App\Models\Campus;
use App\Models\Department;
use App\Models\JobPosting;
use App\Models\SawCriterion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobPosting>
 */
class JobPostingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'campus_id' => Campus::factory(),
            'department_id' => fn (array $attrs) => Department::create([
                'campus_id' => $attrs['campus_id'], 'name' => fake()->unique()->company(), 'level' => 'tertiary',
            ])->id,
            'category' => JobCategory::Academic,
            'employment_type' => EmploymentType::FullTime,
            'slots' => 1,
            'description' => fake()->paragraph(),
            'qualifications' => ["Bachelor's degree in a related field", 'At least 2 years of experience'],
            'status' => PostingStatus::Open,
        ];
    }

    /** Attach the default SAW criteria, as the admin form does. */
    public function configure(): static
    {
        return $this->afterCreating(function (JobPosting $posting) {
            foreach (SawCriterion::DEFAULTS as $i => $criterion) {
                $posting->criteria()->create($criterion + ['sort_order' => $i, 'type' => 'benefit']);
            }
        });
    }
}
