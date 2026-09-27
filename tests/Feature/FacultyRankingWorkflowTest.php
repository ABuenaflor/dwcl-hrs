<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\Level;
use App\Enums\RankingStatus;
use App\Enums\Role;
use App\Models\Department;
use App\Models\FacultyRanking;
use App\Models\Rubric;
use App\Models\User;
use App\Services\RubricCalculator;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RubricSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FacultyRankingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $faculty;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([OrganizationSeeder::class, RubricSeeder::class]);

        $this->faculty = User::factory()->role(Role::Faculty)->create([
            'department_id' => Department::where('code', 'JHS')->value('id'),
        ]);
    }

    public function test_rubric_caps_limit_subtotals(): void
    {
        $rubric = Rubric::activeFor(Level::BasicEd);
        $seminars = $rubric->items()->where('code', '1.2.2')->first();
        $children = $seminars->children()->pluck('id');

        // Claim 20 points on each of the 4 seminar rows — the 1.2.2 cap is 25.
        $points = $children->mapWithKeys(fn ($id) => [$id => 20])->all();
        $result = app(RubricCalculator::class)->calculate($rubric->tree(), $points);

        $this->assertSame(25.0, $result['subtotals'][$seminars->id]);
        $this->assertSame(25.0, $result['total']);
    }

    public function test_full_workflow_from_self_rating_to_certificate(): void
    {
        Storage::fake('local');
        [$drc, $crtc, $vp, $admin] = array_map(
            fn (Role $r) => User::factory()->role($r)->create(),
            [Role::Drc, Role::Crtc, Role::Vp, Role::Admin],
        );

        // Faculty starts and fills a self-rating with evidence.
        $this->actingAs($this->faculty)->post(route('faculty.rankings.store'))->assertRedirect();
        $ranking = FacultyRanking::firstOrFail();
        $item = $ranking->rubric->items()->where('is_scorable', true)->where('title', 'like', 'Full Time%')->first();

        $this->put(route('faculty.rankings.update', $ranking), [
            'points' => [$item->id => 24],
            'evidence' => [$item->id => UploadedFile::fake()->create('service-record.pdf', 100, 'application/pdf')],
            'submit' => 1,
        ])->assertRedirect(route('faculty.rankings.show', $ranking));

        $ranking->refresh();
        $this->assertSame(RankingStatus::Submitted, $ranking->status);
        $this->assertSame(24.0, $ranking->sr_total);
        Storage::disk('local')->assertExists($ranking->scores()->first()->evidence_path);

        // Faculty can no longer edit; CRTC cannot act before the DRC.
        $this->put(route('faculty.rankings.update', $ranking), ['points' => [$item->id => 90]])->assertStatus(422);
        $this->actingAs($crtc)->put(route('admin.rankings.update', $ranking), ['forward' => 1])->assertForbidden();

        // DRC lowers the score and forwards.
        $this->actingAs($drc)->put(route('admin.rankings.update', $ranking), ['points' => [$item->id => 20], 'forward' => 1])->assertRedirect();
        $this->assertSame(RankingStatus::DrcReviewed, $ranking->fresh()->status);
        $this->assertSame(20.0, $ranking->fresh()->drc_total);

        $this->actingAs($crtc)->put(route('admin.rankings.update', $ranking), ['points' => [$item->id => 20], 'forward' => 1]);
        $this->actingAs($vp)->put(route('admin.rankings.update', $ranking), ['forward' => 1]);
        $this->actingAs($admin)->put(route('admin.rankings.update', $ranking), ['forward' => 1]);
        $this->assertSame(RankingStatus::Approved, $ranking->fresh()->status);
        $this->actingAs($admin)->put(route('admin.rankings.update', $ranking), ['forward' => 1]);

        $ranking->refresh();
        $this->assertSame(RankingStatus::Certified, $ranking->status);
        $this->assertNotNull($ranking->certificate_no);
        $this->assertSame(6, $ranking->reviews()->count()); // submitted → … → certified
        $this->assertSame($ranking->recommended_rank_id, $this->faculty->fresh()->academic_rank_id);

        $this->actingAs($this->faculty)->get(route('faculty.rankings.certificate', $ranking))
            ->assertOk()->assertSee('Certificate of Rank')->assertSee($ranking->certificate_no);
        $this->assertCount(5, $this->faculty->notifications);
    }

    public function test_committee_can_return_a_ranking_with_remarks(): void
    {
        $drc = User::factory()->role(Role::Drc)->create();
        $this->actingAs($this->faculty)->post(route('faculty.rankings.store'));
        $ranking = FacultyRanking::firstOrFail();
        $this->post(route('faculty.rankings.submit', $ranking));

        $this->actingAs($drc)->post(route('admin.rankings.return', $ranking), ['remarks' => 'Attach your TOR.'])->assertRedirect();

        $this->assertSame(RankingStatus::Returned, $ranking->fresh()->status);
        $this->actingAs($this->faculty)->get(route('faculty.rankings.show', $ranking))->assertSee('Attach your TOR.');
    }

    public function test_pending_employee_cannot_sign_in_until_approved(): void
    {
        $admin = User::factory()->admin()->create();
        $pending = User::factory()->role(Role::Faculty)->pending()->create(['email' => 'new@dwcl.edu.ph']);

        $this->post(route('login'), ['email' => 'new@dwcl.edu.ph', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($admin)->patch(route('admin.users.status', $pending), ['status' => 'active']);
        $this->assertSame(AccountStatus::Active, $pending->fresh()->status);
    }
}
