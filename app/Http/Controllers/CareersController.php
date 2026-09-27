<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Enums\JobCategory;
use App\Models\Campus;
use App\Models\Department;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareersController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'postings' => JobPosting::acceptingApplications()->with(['campus', 'department'])->latest()->take(6)->get(),
            'openCount' => JobPosting::acceptingApplications()->count(),
            'departmentCount' => Department::count(),
            'campusCount' => Campus::count(),
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'campus' => ['nullable', 'integer'],
            'department' => ['nullable', 'integer'],
            'category' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
        ]);

        $postings = JobPosting::acceptingApplications()
            ->with(['campus', 'department'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")))
            ->when($filters['campus'] ?? null, fn ($q, $v) => $q->where('campus_id', $v))
            ->when($filters['department'] ?? null, fn ($q, $v) => $q->where('department_id', $v))
            ->when(JobCategory::tryFrom($filters['category'] ?? ''), fn ($q, $v) => $q->where('category', $v))
            ->when(EmploymentType::tryFrom($filters['type'] ?? ''), fn ($q, $v) => $q->where('employment_type', $v))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('public.careers', [
            'postings' => $postings,
            'filters' => $filters,
            'campuses' => Campus::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, JobPosting $posting): View
    {
        abort_unless($posting->isAcceptingApplications() || $request->user()?->isAdmin(), 404);

        $posting->load(['campus', 'department', 'criteria']);

        return view('public.posting', [
            'posting' => $posting,
            'existing' => $request->user()?->applications()->where('job_posting_id', $posting->id)->first(),
        ]);
    }
}
