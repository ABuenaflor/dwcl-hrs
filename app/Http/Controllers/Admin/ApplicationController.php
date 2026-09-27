<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\EmploymentType;
use App\Enums\JobCategory;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Campus;
use App\Models\Department;
use App\Models\JobPosting;
use App\Notifications\Alert;
use App\Services\SawRanker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'posting', 'campus', 'department', 'category', 'type', 'status', 'sort']);

        $applications = self::filtered($filters)
            ->with(['user:id,first_name,last_name,email', 'jobPosting:id,title,campus_id,department_id', 'jobPosting.department:id,name', 'jobPosting.campus:id,name'])
            ->when(($filters['sort'] ?? 'rank') === 'rank',
                fn ($q) => $q->orderBy('job_posting_id')->orderByRaw('saw_rank is null')->orderBy('saw_rank'),
                fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        return view('admin.applications.index', [
            'applications' => $applications,
            'filters' => $filters,
            'postings' => JobPosting::orderBy('title')->get(['id', 'title']),
            'campuses' => Campus::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    /** Shared by the list page and CSV export. */
    public static function filtered(array $filters): Builder
    {
        return Application::query()
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->whereHas('user', fn ($u) => $u
                ->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")))
            ->when($filters['posting'] ?? null, fn ($q, $v) => $q->where('applications.job_posting_id', $v))
            ->when(ApplicationStatus::tryFrom($filters['status'] ?? ''), fn ($q, $v) => $q->where('applications.status', $v))
            ->when(
                array_filter([
                    'campus_id' => $filters['campus'] ?? null,
                    'department_id' => $filters['department'] ?? null,
                    'category' => JobCategory::tryFrom($filters['category'] ?? '')?->value,
                    'employment_type' => EmploymentType::tryFrom($filters['type'] ?? '')?->value,
                ]),
                fn ($q, $where) => $q->whereHas('jobPosting', fn ($p) => $p->where($where)),
            )
            ->when(isset($filters['from']), fn ($q) => $q->whereDate('applications.created_at', '>=', $filters['from']))
            ->when(isset($filters['to']), fn ($q) => $q->whereDate('applications.created_at', '<=', $filters['to']));
    }

    public function show(Application $application, SawRanker $saw): View
    {
        $application->load(['user.profile', 'user.educations', 'jobPosting.criteria', 'jobPosting.campus', 'jobPosting.department', 'documents', 'scores']);

        $criteria = $application->jobPosting->criteria;

        return view('admin.applications.show', [
            'application' => $application,
            'criteria' => $criteria,
            'raw' => $criteria->mapWithKeys(fn ($c) => [$c->id => $saw->rawValue($c, $application)]),
            'applicantCount' => $application->jobPosting->applications()->where('status', '!=', ApplicationStatus::Withdrawn)->count(),
        ]);
    }

    /** HR / dean ratings for the manual SAW criteria (skills, interview). */
    public function scores(Request $request, Application $application, SawRanker $saw): RedirectResponse
    {
        $criteria = $application->jobPosting->criteria()->where('source', 'manual')->get();

        $rules = [];
        foreach ($criteria as $c) {
            $rules["scores.{$c->id}"] = ['nullable', 'numeric', 'min:0', 'max:'.($c->scale_max ?? 100)];
        }
        $data = $request->validate($rules + ['hr_notes' => ['nullable', 'string', 'max:5000']], [], ['scores.*' => 'rating']);

        DB::transaction(function () use ($application, $criteria, $data, $request) {
            foreach ($criteria as $c) {
                $value = $data['scores'][$c->id] ?? null;
                if ($value === null) {
                    $application->scores()->where('saw_criterion_id', $c->id)->delete();

                    continue;
                }
                $application->scores()->updateOrCreate(
                    ['saw_criterion_id' => $c->id],
                    ['value' => $value, 'rated_by' => $request->user()->id],
                );
            }
            $application->update(['hr_notes' => $data['hr_notes'] ?? null]);
        });

        $saw->rerank($application->jobPosting);

        return back()->with('toast', 'Ratings saved. Rank is now #'.$application->fresh()->saw_rank.'.');
    }

    public function status(Request $request, Application $application): RedirectResponse
    {
        $allowed = array_map(fn ($s) => $s->value, $application->status->nextForHr());

        $data = $request->validate([
            'status' => ['required', Rule::in($allowed)],
            'interview_at' => ['nullable', 'required_if:status,interview', 'date', 'after:now'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = ApplicationStatus::from($data['status']);
        $application->update([
            'status' => $status,
            'interview_at' => $data['interview_at'] ?? $application->interview_at,
            'decided_at' => $status->isClosed() ? now() : null,
        ]);

        $message = match ($status) {
            ApplicationStatus::Shortlisted => 'Good news — you have been shortlisted.',
            ApplicationStatus::Interview => 'You are invited to an interview on '.$application->interview_at->format('M j, Y g:i A').'.',
            ApplicationStatus::Offered => 'You have received a job offer. Please accept or decline it in the portal.',
            ApplicationStatus::Hired => 'Welcome to DWCL! HRDO will contact you about onboarding.',
            default => 'Thank you for your interest. We have decided to move forward with other candidates.',
        };

        $application->user->notify(new Alert(
            "{$application->jobPosting->title}: {$status->label()}",
            trim($message.' '.($data['message'] ?? '')),
            route('applicant.applications.show', $application),
            $status->tone() === 'neutral' ? 'info' : $status->tone(),
        ));

        return back()->with('toast', "Moved to “{$status->label()}” and the applicant was notified.");
    }
}
