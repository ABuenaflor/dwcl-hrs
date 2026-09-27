<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\Role;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationFlowTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'first_name' => 'Alex', 'last_name' => 'Rivera',
            'profile' => [
                'date_of_birth' => '1995-04-10', 'sex' => 'male', 'civil_status' => 'single', 'citizenship' => 'Filipino',
                'contact_number' => '09171234567', 'residential_address' => 'Legazpi City', 'tin_no' => '123-456-789',
            ],
            'education' => [
                'secondary' => ['school' => 'DWCL High School'],
                'college' => ['school' => 'DWCL', 'course' => 'BS Computer Science', 'year_graduated' => 2016],
            ],
            'years_experience' => 4, 'trainings_count' => 3, 'license' => 'LET Passer',
            'resume' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
            'documents' => [['type' => 'certificate', 'file' => UploadedFile::fake()->create('cert.pdf', 50, 'application/pdf')]],
        ], $overrides);
    }

    public function test_applicant_can_apply_and_is_ranked(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $posting = JobPosting::factory()->create();
        $applicant = User::factory()->create();

        $this->actingAs($applicant)->post(route('applicant.apply.store', $posting), $this->payload())
            ->assertSessionHasNoErrors()->assertRedirect();

        $application = Application::firstOrFail();
        $this->assertSame(1, $application->saw_rank);
        $this->assertCount(2, $application->documents);
        $this->assertSame('123-456-789', $applicant->profile->tin_no);
        // Government IDs are encrypted at rest.
        $this->assertNotSame('123-456-789', \DB::table('applicant_profiles')->value('tin_no'));
        $this->assertCount(1, $admin->notifications);

        // A second submission for the same vacancy cannot create a duplicate (legacy double-insert bug).
        $this->post(route('applicant.apply.store', $posting), $this->payload());
        $this->assertSame(1, Application::count());
    }

    public function test_hr_moves_applicant_through_pipeline_and_applicant_accepts_offer(): void
    {
        $admin = User::factory()->admin()->create();
        $posting = JobPosting::factory()->create();
        $applicant = User::factory()->create();
        $application = $applicant->applications()->create(['job_posting_id' => $posting->id, 'status' => ApplicationStatus::Submitted]);

        $this->actingAs($admin)->patch(route('admin.applications.status', $application), ['status' => 'hired'])
            ->assertSessionHasErrors('status'); // cannot skip stages

        $this->patch(route('admin.applications.status', $application), ['status' => 'shortlisted']);
        $this->patch(route('admin.applications.status', $application), ['status' => 'interview', 'interview_at' => now()->addDay()->format('Y-m-d H:i')]);
        $this->patch(route('admin.applications.status', $application), ['status' => 'offered']);
        $this->assertSame(ApplicationStatus::Offered, $application->fresh()->status);

        $this->actingAs($applicant)->post(route('applicant.applications.respond', $application), ['decision' => 'accept']);
        $this->assertSame(ApplicationStatus::Hired, $application->fresh()->status);
        $this->assertCount(3, $applicant->notifications);
    }

    public function test_documents_are_private(): void
    {
        Storage::fake('local');
        $posting = JobPosting::factory()->create();
        $owner = User::factory()->create();
        $this->actingAs($owner)->post(route('applicant.apply.store', $posting), $this->payload());
        $doc = Application::firstOrFail()->documents()->first();

        $this->get(route('documents.show', $doc))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('documents.show', $doc))->assertForbidden();
        $this->actingAs(User::factory()->role(Role::Drc)->create())->get(route('documents.show', $doc))->assertForbidden();
    }

    public function test_roles_are_confined_to_their_areas(): void
    {
        $applicant = User::factory()->create();
        $this->actingAs($applicant)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($applicant)->get(route('faculty.dashboard'))->assertForbidden();

        $drc = User::factory()->role(Role::Drc)->create();
        $this->actingAs($drc)->get(route('admin.rankings.index'))->assertOk();
        $this->actingAs($drc)->get(route('admin.postings.index'))->assertForbidden();
    }
}
