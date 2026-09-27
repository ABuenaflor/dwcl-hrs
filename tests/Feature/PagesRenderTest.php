<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Application;
use App\Models\FacultyRanking;
use App\Models\JobPosting;
use App\Models\Rubric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test: every screen renders for the roles that can see it, against
 * the full demo dataset.
 */
class PagesRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_and_auth_pages(): void
    {
        $posting = JobPosting::first();

        foreach (['/', '/careers', '/careers?q=Instructor&type=full_time', "/careers/{$posting->id}", '/login', '/register', '/register/employee'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_applicant_pages(): void
    {
        $applicant = User::where('email', 'applicant@example.com')->firstOrFail();
        $application = $applicant->applications()->firstOrFail();
        $other = JobPosting::whereNot('id', $application->job_posting_id)->firstOrFail();

        $this->actingAs($applicant);
        foreach (['/applicant', '/applicant/profile', "/applicant/applications/{$application->id}", "/applicant/apply/{$other->id}", '/notifications', '/account/password'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_faculty_pages(): void
    {
        foreach (['faculty@dwcl.edu.ph', 'teacher@dwcl.edu.ph'] as $email) {
            $faculty = User::where('email', $email)->firstOrFail();
            $this->actingAs($faculty)->get('/faculty')->assertOk();
            $this->get(route('faculty.rankings.show', $faculty->rankings()->first()))->assertOk()->assertSee('Grand total');
        }
    }

    public function test_admin_pages(): void
    {
        $this->actingAs(User::where('role', Role::Admin)->firstOrFail());
        $posting = JobPosting::first();
        $ranking = FacultyRanking::where('status', '!=', 'draft')->first();

        foreach ([
            '/admin', '/admin/postings', '/admin/postings/create', "/admin/postings/{$posting->id}/edit",
            "/admin/postings/{$posting->id}/ranking", '/admin/applications', '/admin/applications?sort=latest&status=submitted',
            '/admin/applications/'.Application::first()->id, '/admin/rankings', "/admin/rankings/{$ranking->id}",
            '/admin/users', '/admin/users?status=pending', '/admin/users/create', '/admin/rubrics',
            '/admin/rubrics/'.Rubric::first()->id, '/admin/rubrics/'.Rubric::latest('id')->first()->id,
            '/admin/setup', '/admin/reports', '/admin/reports?from=2020-01-01',
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/admin/reports/applications.csv')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_reviewer_dashboard(): void
    {
        $this->actingAs(User::where('role', Role::Drc)->firstOrFail())
            ->get('/admin')->assertOk()->assertSee('Awaiting your action');
    }
}
