<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentType;
use App\Enums\JobCategory;
use App\Enums\PostingStatus;
use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Department;
use App\Models\JobPosting;
use App\Models\SawCriterion;
use App\Services\SawRanker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JobPostingController extends Controller
{
    public function index(Request $request): View
    {
        $status = PostingStatus::tryFrom($request->string('status'));

        return view('admin.postings.index', [
            'postings' => JobPosting::with(['campus', 'department'])
                ->withCount('applications')
                ->when($status, fn ($q) => $q->where('status', $status))
                ->when($request->string('q')->toString(), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
                ->latest()
                ->paginate(12)
                ->withQueryString(),
            'status' => $status,
        ]);
    }

    public function create(): View
    {
        $posting = new JobPosting([
            'status' => PostingStatus::Open, 'slots' => 1, 'qualifications' => [''],
            'category' => JobCategory::Academic, 'employment_type' => EmploymentType::FullTime,
        ]);

        return view('admin.postings.form', $this->formData($posting) + [
            'criteria' => collect(SawCriterion::DEFAULTS)->map(fn ($c) => new SawCriterion($c + ['type' => 'benefit'])),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data) {
            $posting = JobPosting::create($data['posting'] + ['created_by' => $request->user()->id]);
            foreach ($data['criteria'] as $i => $criterion) {
                $default = collect(SawCriterion::DEFAULTS)->firstWhere('key', $criterion['key']);
                $posting->criteria()->create(array_merge($default, $criterion, ['sort_order' => $i, 'type' => 'benefit']));
            }
        });

        return redirect()->route('admin.postings.index')->with('toast', 'Vacancy posted.');
    }

    public function edit(JobPosting $posting): View
    {
        return view('admin.postings.form', $this->formData($posting) + ['criteria' => $posting->criteria]);
    }

    public function update(Request $request, JobPosting $posting, SawRanker $saw): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($posting, $data) {
            $posting->update($data['posting']);
            foreach ($data['criteria'] as $criterion) {
                $posting->criteria()->where('key', $criterion['key'])->update(['weight' => $criterion['weight']]);
            }
        });

        // Weights changed → the ranking changes.
        $saw->rerank($posting);

        return redirect()->route('admin.postings.index')->with('toast', 'Vacancy updated and applicants re-ranked.');
    }

    public function destroy(JobPosting $posting): RedirectResponse
    {
        if ($posting->applications()->exists()) {
            $posting->update(['status' => PostingStatus::Closed]);

            return back()->with('toast', 'This vacancy has applicants, so it was closed instead of deleted.');
        }

        $posting->delete();

        return back()->with('toast', 'Vacancy deleted.');
    }

    /** The full SAW decision matrix for a vacancy — the transparent basis of the shortlist. */
    public function ranking(JobPosting $posting, SawRanker $saw): View
    {
        return view('admin.postings.ranking', [
            'posting' => $posting->load(['campus', 'department']),
            'result' => $saw->evaluate($posting),
        ]);
    }

    private function formData(JobPosting $posting): array
    {
        return [
            'posting' => $posting,
            'campuses' => Campus::orderBy('name')->get(),
            'departments' => Department::with('campus')->orderBy('name')->get(),
        ];
    }

    /** @return array{posting: array, criteria: list<array{key: string, weight: float}>} */
    private function validated(Request $request): array
    {
        $keys = array_column(SawCriterion::DEFAULTS, 'key');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'category' => ['required', Rule::enum(JobCategory::class)],
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'schedule' => ['nullable', 'string', 'max:60'],
            'slots' => ['required', 'integer', 'between:1,100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'qualifications' => ['required', 'array', 'min:1'],
            'qualifications.*' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(PostingStatus::class)],
            'closes_at' => ['nullable', 'date'],
            'criteria' => ['required', 'array'],
            'criteria.*.key' => ['required', Rule::in($keys)],
            'criteria.*.weight' => ['required', 'numeric', 'between:0,100'],
        ], [], ['qualifications.*' => 'qualification', 'criteria.*.weight' => 'weight']);

        $data['qualifications'] = array_values(array_filter($data['qualifications'], 'filled'));
        if (! $data['qualifications']) {
            throw ValidationException::withMessages(['qualifications' => 'Add at least one qualification.']);
        }

        // Weights are entered as percentages in the form and stored as fractions.
        $criteria = array_map(fn ($c) => ['key' => $c['key'], 'weight' => round($c['weight'] / 100, 4)], $data['criteria']);
        if (abs(array_sum(array_column($criteria, 'weight')) - 1) > 0.001) {
            throw ValidationException::withMessages(['criteria' => 'Criteria weights must add up to 100%.']);
        }

        return ['posting' => collect($data)->except('criteria')->all(), 'criteria' => $criteria];
    }
}
