<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\EducationLevel;
use App\Models\JobPosting;
use App\Models\User;
use App\Services\SawRanker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SawRankerTest extends TestCase
{
    use RefreshDatabase;

    private function apply(JobPosting $posting, float $years, EducationLevel $edu, int $trainings, array $manual = [], ?string $license = null)
    {
        $user = User::factory()->create();
        $user->educations()->create(['level' => $edu, 'school' => 'DWCL']);
        $app = $user->applications()->create([
            'job_posting_id' => $posting->id, 'status' => ApplicationStatus::Submitted,
            'years_experience' => $years, 'trainings_count' => $trainings, 'license' => $license,
        ]);
        foreach ($manual as $key => $value) {
            $app->scores()->create(['saw_criterion_id' => $posting->criteria->firstWhere('key', $key)->id, 'value' => $value]);
        }

        return $app;
    }

    public function test_it_normalizes_by_column_maximum_and_sums_weighted_values(): void
    {
        $posting = JobPosting::factory()->create()->load('criteria');

        $a = $this->apply($posting, 10, EducationLevel::Masters, 4, ['tech_skills' => 8, 'soft_skills' => 10, 'interview' => 6]);
        $b = $this->apply($posting, 5, EducationLevel::Doctorate, 8, ['tech_skills' => 10, 'soft_skills' => 5, 'interview' => 10], 'LET Passer');

        app(SawRanker::class)->rerank($posting);

        // Weights: experience .30, education .20, trainings .10, tech .20, soft .10, interview .10
        // A: .3·1 + .2·(4/6) + .1·(4/8) + .2·(8/10) + .1·1 + .1·(6/10) = 0.803333
        // B: .3·.5 + .2·1 + .1·1 + .2·1 + .1·.5 + .1·1 = 0.800000
        $this->assertEqualsWithDelta(0.803333, $a->fresh()->saw_score, 0.00001);
        $this->assertEqualsWithDelta(0.8, $b->fresh()->saw_score, 0.00001);
        $this->assertSame(1, $a->fresh()->saw_rank);
        $this->assertSame(2, $b->fresh()->saw_rank);
    }

    public function test_ties_share_a_rank_and_withdrawn_applicants_are_excluded(): void
    {
        $posting = JobPosting::factory()->create()->load('criteria');

        $a = $this->apply($posting, 3, EducationLevel::College, 2);
        $b = $this->apply($posting, 3, EducationLevel::College, 2);
        $c = $this->apply($posting, 9, EducationLevel::Doctorate, 9);
        $c->update(['status' => ApplicationStatus::Withdrawn]);

        app(SawRanker::class)->rerank($posting);

        $this->assertSame(1, $a->fresh()->saw_rank);
        $this->assertSame(1, $b->fresh()->saw_rank);
        $this->assertNull($c->fresh()->saw_rank);
        // Without the withdrawn applicant, both hit every column maximum on the auto criteria.
        $this->assertEqualsWithDelta(0.6, $a->fresh()->saw_score, 0.00001);
    }

    public function test_cost_criteria_prefer_lower_values(): void
    {
        $ranker = new SawRanker;

        $this->assertSame(1.0, $ranker->normalize(10, [10, 20, 40], cost: true));
        $this->assertSame(0.25, $ranker->normalize(40, [10, 20, 40], cost: true));
        $this->assertSame(0.0, $ranker->normalize(5, [0, 0], cost: false));
    }
}
