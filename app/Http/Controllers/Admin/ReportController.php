<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\RankingStatus;
use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Department;
use App\Models\FacultyRanking;
use App\Models\JobPosting;
use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $scope = ApplicationController::filtered($filters);

        return view('admin.reports.index', [
            'filters' => $filters,
            'total' => (clone $scope)->count(),
            'hired' => (clone $scope)->where('status', ApplicationStatus::Hired)->count(),
            'charts' => [
                'trend' => Charts::applicationsTrend(12, clone $scope),
                'status' => Charts::applicationsByStatus(clone $scope),
                'department' => Charts::applicationsBy('department', clone $scope),
                'campus' => Charts::applicationsBy('campus', clone $scope),
            ],
            'postings' => JobPosting::withCount([
                'applications',
                'applications as hired_count' => fn ($q) => $q->where('status', ApplicationStatus::Hired),
            ])->with('department')->latest()->take(10)->get(),
            'rankingSummary' => FacultyRanking::query()
                ->selectRaw('status, count(*) as total, avg(coalesce(final_total, drc_total, sr_total)) as average')
                ->groupBy('status')->get(),
            'campuses' => Campus::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'postingOptions' => JobPosting::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function applicationsCsv(Request $request): StreamedResponse
    {
        $rows = ApplicationController::filtered($this->filters($request))
            ->with(['user', 'jobPosting.department', 'jobPosting.campus'])
            ->orderBy('job_posting_id')->orderBy('saw_rank')
            ->lazy();

        return $this->csv('applications', [
            'Applicant', 'Email', 'Position', 'Department', 'Campus', 'Status', 'SAW Score', 'SAW Rank', 'Years Exp.', 'Date Applied',
        ], $rows->map(fn ($a) => [
            $a->user->name, $a->user->email, $a->jobPosting->title, $a->jobPosting->department->name,
            $a->jobPosting->campus->name, $a->status->label(), $a->saw_score, $a->saw_rank,
            $a->years_experience, $a->created_at->toDateString(),
        ]));
    }

    public function rankingsCsv(): StreamedResponse
    {
        $rows = FacultyRanking::where('status', '!=', RankingStatus::Draft)
            ->with(['user.department', 'rubric', 'currentRank', 'recommendedRank'])
            ->orderBy('cycle')->lazy();

        return $this->csv('faculty-rankings', [
            'Faculty', 'Department', 'Level', 'Cycle', 'Status', 'SR Total', 'DRC Total', 'Final Total', 'Current Rank', 'Recommended Rank', 'Certificate No.',
        ], $rows->map(fn ($r) => [
            $r->user->name, $r->user->department?->name, $r->rubric->level->label(), $r->cycle, $r->status->label(),
            $r->sr_total, $r->drc_total, $r->final_total, $r->currentRank?->name, $r->recommendedRank?->name, $r->certificate_no,
        ]));
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'campus' => ['nullable', 'integer'],
            'department' => ['nullable', 'integer'],
            'posting' => ['nullable', 'integer'],
            'status' => ['nullable', 'string'],
        ]);
    }

    private function csv(string $name, array $header, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM so Excel reads UTF-8 names correctly
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $name.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
