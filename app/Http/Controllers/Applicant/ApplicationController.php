<?php

namespace App\Http\Controllers\Applicant;

use App\Actions\SaveApplicantProfile;
use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\JobPosting;
use App\Models\User;
use App\Notifications\Alert;
use App\Services\SawRanker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function create(Request $request, JobPosting $posting): View|RedirectResponse
    {
        if ($existing = $request->user()->applications()->where('job_posting_id', $posting->id)->first()) {
            return redirect()->route('applicant.applications.show', $existing)->with('toast', 'You already applied for this position.');
        }

        abort_unless($posting->isAcceptingApplications(), 404);

        return view('applicant.apply', [
            'posting' => $posting->load(['campus', 'department']),
            'user' => $request->user()->load(['profile', 'educations']),
        ]);
    }

    public function store(Request $request, JobPosting $posting, SaveApplicantProfile $saveProfile, SawRanker $saw): RedirectResponse
    {
        abort_unless($posting->isAcceptingApplications(), 404);
        $user = $request->user();

        $data = $request->validate(SaveApplicantProfile::rules() + [
            'years_experience' => ['required', 'numeric', 'between:0,60'],
            'work_experience' => ['nullable', 'string', 'max:5000'],
            'trainings' => ['nullable', 'string', 'max:5000'],
            'trainings_count' => ['required', 'integer', 'between:0,200'],
            'skills' => ['nullable', 'string', 'max:3000'],
            'license' => ['nullable', 'string', 'max:120'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'resume' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'documents' => ['nullable', 'array', 'max:10'],
            'documents.*.file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'documents.*.type' => ['required', Rule::in(array_keys(ApplicationDocument::TYPES))],
        ], [], SaveApplicantProfile::attributes() + ['documents.*.file' => 'document']);

        $application = DB::transaction(function () use ($user, $posting, $data, $request, $saveProfile) {
            $saveProfile($user, $data);

            $application = $user->applications()->firstOrCreate(
                ['job_posting_id' => $posting->id],
                collect($data)->only(['years_experience', 'work_experience', 'trainings', 'trainings_count', 'skills', 'license', 'cover_letter'])
                    ->put('status', ApplicationStatus::Submitted)->all(),
            );

            $files = [['file' => $request->file('resume'), 'type' => 'resume']];
            foreach ($request->file('documents', []) as $i => $doc) {
                $files[] = ['file' => $doc['file'], 'type' => $data['documents'][$i]['type']];
            }

            foreach ($files as $f) {
                $application->documents()->create([
                    'type' => $f['type'],
                    'original_name' => $f['file']->getClientOriginalName(),
                    'path' => $f['file']->store("applications/{$application->id}", 'local'),
                    'size' => $f['file']->getSize(),
                ]);
            }

            return $application;
        });

        $saw->rerank($posting);

        Notification::send(
            User::role(Role::Admin)->where('status', AccountStatus::Active)->get(),
            new Alert('New application', "{$user->name} applied for {$posting->title}.", route('admin.applications.show', $application)),
        );

        return redirect()->route('applicant.applications.show', $application)
            ->with('toast', 'Application submitted. We will notify you as it progresses.');
    }

    public function show(Request $request, Application $application): View
    {
        $this->authorizeOwner($request, $application);

        return view('applicant.application', [
            'application' => $application->load(['jobPosting.campus', 'jobPosting.department', 'documents']),
        ]);
    }

    public function withdraw(Request $request, Application $application, SawRanker $saw): RedirectResponse
    {
        $this->authorizeOwner($request, $application);
        abort_if($application->status->isClosed(), 422, 'This application is already closed.');

        $application->update(['status' => ApplicationStatus::Withdrawn, 'decided_at' => now()]);
        $saw->rerank($application->jobPosting);

        return back()->with('toast', 'Application withdrawn.');
    }

    /** The applicant accepts or declines a job offer (thesis Fig. 4). */
    public function respond(Request $request, Application $application): RedirectResponse
    {
        $this->authorizeOwner($request, $application);
        abort_unless($application->status === ApplicationStatus::Offered, 422);

        $accept = $request->validate(['decision' => ['required', Rule::in(['accept', 'decline'])]])['decision'] === 'accept';
        $status = $accept ? ApplicationStatus::Hired : ApplicationStatus::Declined;
        $application->update(['status' => $status, 'decided_at' => now()]);

        Notification::send(
            User::role(Role::Admin)->where('status', AccountStatus::Active)->get(),
            new Alert(
                $accept ? 'Offer accepted' : 'Offer declined',
                $application->user->name.($accept ? ' accepted' : ' declined')." the offer for {$application->jobPosting->title}.",
                route('admin.applications.show', $application),
                $accept ? 'success' : 'warning',
            ),
        );

        return back()->with('toast', $accept ? 'Congratulations! HRDO will contact you about onboarding.' : 'You declined the offer.');
    }

    private function authorizeOwner(Request $request, Application $application): void
    {
        abort_unless($application->user_id === $request->user()->id, 404);
    }
}
