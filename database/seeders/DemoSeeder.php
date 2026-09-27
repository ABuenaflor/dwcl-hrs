<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\EducationLevel;
use App\Enums\EmploymentType;
use App\Enums\JobCategory;
use App\Enums\Level;
use App\Enums\PostingStatus;
use App\Enums\RankingStatus;
use App\Enums\Role;
use App\Models\AcademicRank;
use App\Models\Department;
use App\Models\FacultyRanking;
use App\Models\JobPosting;
use App\Models\Rubric;
use App\Models\SawCriterion;
use App\Models\User;
use App\Services\RankingWorkflow;
use App\Services\SawRanker;
use Illuminate\Database\Seeder;

/**
 * Sample data so every screen has something to show. Not run in production.
 * All demo accounts use the password "password".
 */
class DemoSeeder extends Seeder
{
    public function run(SawRanker $saw, RankingWorkflow $workflow): void
    {
        if (JobPosting::exists()) {
            return;
        }

        $dept = fn (string $code) => Department::where('code', $code)->firstOrFail();
        $staff = fn (string $email, string $first, string $last, Role $role, ?string $code = null, array $extra = []) => User::create([
            'email' => $email, 'first_name' => $first, 'last_name' => $last, 'password' => 'password',
            'role' => $role, 'status' => AccountStatus::Active,
            'department_id' => $code ? $dept($code)->id : null,
            'campus_id' => $code ? $dept($code)->campus_id : null,
        ] + $extra);

        $admin = User::where('role', Role::Admin)->first();
        $staff('drc@dwcl.edu.ph', 'Dana', 'Reyes', Role::Drc, 'SOECS', ['designation' => 'Department Ranking Committee Chair']);
        $staff('crtc@dwcl.edu.ph', 'Carlo', 'Tan', Role::Crtc, 'SEAS', ['designation' => 'College Rank & Tenure Council']);
        $staff('vp@dwcl.edu.ph', 'Victoria', 'Palma', Role::Vp, null, ['designation' => 'Vice President for Academic Affairs']);

        $tertiaryRank = AcademicRank::where('level', Level::Tertiary)->orderBy('sort_order')->first();
        $basicRank = AcademicRank::where('level', Level::BasicEd)->orderBy('sort_order')->first();
        $faculty = $staff('faculty@dwcl.edu.ph', 'Francis', 'Villanueva', Role::Faculty, 'SOECS', [
            'employee_no' => 'DWCL-0142', 'employment_type' => EmploymentType::FullTime, 'designation' => 'Instructor',
            'academic_rank_id' => $tertiaryRank->id, 'date_hired' => now()->subYears(6),
        ]);
        $teacher = $staff('teacher@dwcl.edu.ph', 'Bea', 'Santos', Role::Faculty, 'JHS', [
            'employee_no' => 'DWCL-0311', 'employment_type' => EmploymentType::FullTime, 'designation' => 'Mathematics Teacher',
            'academic_rank_id' => $basicRank->id, 'date_hired' => now()->subYears(4),
        ]);
        $nurse = $staff('nursing@dwcl.edu.ph', 'Nina', 'Dela Cruz', Role::Faculty, 'SON', [
            'employee_no' => 'DWCL-0207', 'employment_type' => EmploymentType::FullTime, 'designation' => 'Clinical Instructor',
            'academic_rank_id' => $tertiaryRank->id, 'date_hired' => now()->subYears(9),
        ]);
        User::create([
            'email' => 'newhire@dwcl.edu.ph', 'first_name' => 'Paolo', 'last_name' => 'Mendoza', 'password' => 'password',
            'role' => Role::Faculty, 'status' => AccountStatus::Pending, 'department_id' => $dept('SBMA')->id,
            'campus_id' => $dept('SBMA')->campus_id, 'employment_type' => EmploymentType::PartTime,
        ]);

        $this->seedHiring($admin, $dept, $saw);
        $this->seedRankings($faculty, $teacher, $nurse, $workflow);
    }

    private function seedHiring(User $admin, callable $dept, SawRanker $saw): void
    {
        $postings = [
            ['Instructor — Computer Science', 'SOECS', JobCategory::Academic, EmploymentType::FullTime, 'Mon–Fri, 7:30 AM – 4:30 PM',
                ["Master's degree in Computer Science or IT (or on-going)", 'At least 2 years of teaching or industry experience', 'Proficiency in programming, databases and web development']],
            ['Junior High School Mathematics Teacher', 'JHS', JobCategory::Academic, EmploymentType::FullTime, 'Mon–Fri, 7:00 AM – 4:00 PM',
                ['Bachelor of Secondary Education major in Mathematics', 'LET passer', 'Experience in K–12 curriculum is an advantage']],
            ['Clinical Instructor', 'SON', JobCategory::Academic, EmploymentType::PartTime, 'Rotating hospital duty',
                ['Registered Nurse with valid PRC license', "Master's units in Nursing", 'At least 3 years of clinical experience']],
            ['Property and Supply Officer', 'PSO', JobCategory::NonAcademic, EmploymentType::FullTime, 'Mon–Sat, 8:00 AM – 5:00 PM',
                ["Bachelor's degree in Business Administration or related field", 'Inventory and procurement experience', 'Proficient in spreadsheets']],
        ];

        foreach ($postings as $i => [$title, $code, $category, $type, $schedule, $quals]) {
            $department = $dept($code);
            $posting = JobPosting::create([
                'title' => $title, 'campus_id' => $department->campus_id, 'department_id' => $department->id,
                'category' => $category, 'employment_type' => $type, 'schedule' => $schedule,
                'slots' => $i === 1 ? 2 : 1, 'qualifications' => $quals, 'status' => PostingStatus::Open,
                'closes_at' => now()->addWeeks(3 + $i), 'created_by' => $admin->id,
                'description' => "Divine Word College of Legazpi is looking for a dedicated {$title} to join the {$department->name}.",
            ]);
            foreach (SawCriterion::DEFAULTS as $order => $c) {
                $posting->criteria()->create($c + ['sort_order' => $order, 'type' => 'benefit']);
            }

            $count = [6, 5, 4, 3][$i];
            for ($n = 0; $n < $count; $n++) {
                $this->seedApplicant($posting, $n === 0 && $i === 0 ? 'applicant@example.com' : null);
            }

            $saw->rerank($posting);
        }
    }

    private function seedApplicant(JobPosting $posting, ?string $email): void
    {
        $user = User::factory()->create($email ? ['email' => $email, 'first_name' => 'Alex', 'last_name' => 'Rivera'] : []);
        $user->profile()->create([
            'date_of_birth' => fake()->dateTimeBetween('-45 years', '-22 years'), 'sex' => fake()->randomElement(['male', 'female']),
            'civil_status' => 'single', 'citizenship' => 'Filipino', 'contact_number' => '09'.fake()->numerify('#########'),
            'residential_address' => fake()->randomElement(['Legazpi City', 'Daraga', 'Tabaco City', 'Ligao City']).', Albay',
        ]);

        $highest = fake()->randomElement([EducationLevel::College, EducationLevel::College, EducationLevel::Masters, EducationLevel::Doctorate]);
        foreach ([EducationLevel::Secondary, EducationLevel::College, EducationLevel::Masters, EducationLevel::Doctorate] as $level) {
            if ($level->points() > $highest->points()) {
                break;
            }
            $user->educations()->create([
                'level' => $level, 'school' => fake()->randomElement(['Divine Word College of Legazpi', 'Bicol University', 'Aquinas University', 'University of Santo Tomas-Legazpi']),
                'course' => $level === EducationLevel::Secondary ? null : fake()->randomElement(['BS Computer Science', 'BS Education', 'BS Nursing', 'BS Business Administration']),
                'year_graduated' => fake()->numberBetween(2005, 2024),
            ]);
        }

        $status = fake()->randomElement([
            ApplicationStatus::Submitted, ApplicationStatus::Submitted, ApplicationStatus::Shortlisted, ApplicationStatus::Interview,
        ]);
        $application = $user->applications()->create([
            'job_posting_id' => $posting->id, 'status' => $status,
            'years_experience' => fake()->randomFloat(1, 0, 12), 'trainings_count' => fake()->numberBetween(0, 12),
            'work_experience' => fake()->sentence(12), 'skills' => fake()->words(6, true),
            'license' => fake()->boolean(60) ? fake()->randomElement(['LET Passer', 'Registered Nurse', 'CPA']) : null,
            'interview_at' => $status === ApplicationStatus::Interview ? now()->addDays(fake()->numberBetween(1, 10))->setTime(9, 0) : null,
        ]);
        $application->forceFill(['created_at' => now()->subDays(fake()->numberBetween(0, 150))])->saveQuietly();

        if ($status !== ApplicationStatus::Submitted) {
            foreach ($posting->criteria()->where('source', 'manual')->get() as $c) {
                $application->scores()->create(['saw_criterion_id' => $c->id, 'value' => fake()->numberBetween(5, 10)]);
            }
        }
    }

    private function seedRankings(User $faculty, User $teacher, User $nurse, RankingWorkflow $workflow): void
    {
        $cycle = FacultyRanking::currentCycle();

        foreach ([[$faculty, RankingStatus::Submitted], [$nurse, RankingStatus::DrcReviewed], [$teacher, RankingStatus::Draft]] as [$user, $target]) {
            $rubric = Rubric::activeFor($user->department->level);
            $ranking = $user->rankings()->create([
                'rubric_id' => $rubric->id, 'cycle' => $cycle, 'status' => RankingStatus::Draft, 'current_rank_id' => $user->academic_rank_id,
            ]);

            foreach ($rubric->items()->where('is_scorable', true)->inRandomOrder()->take(12)->get() as $item) {
                $points = min($item->max_points ?? 10, $item->credit_in_field ?? fake()->numberBetween(1, 10));
                $ranking->scores()->create([
                    'rubric_item_id' => $item->id,
                    'sr_points' => $points,
                    'drc_points' => $target === RankingStatus::DrcReviewed ? max(0, $points - fake()->numberBetween(0, 2)) : null,
                ]);
            }
            $workflow->recalculate($ranking);

            if ($target !== RankingStatus::Draft) {
                $ranking->update(['status' => $target, 'submitted_at' => now()->subDays(5)]);
                $ranking->reviews()->create(['user_id' => $user->id, 'from_status' => RankingStatus::Draft, 'to_status' => RankingStatus::Submitted]);
            }
        }
    }
}
