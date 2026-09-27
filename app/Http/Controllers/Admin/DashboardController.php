<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\RankingStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\FacultyRanking;
use App\Models\JobPosting;
use App\Models\User;
use App\Support\Charts;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Committee members and VPs land on their review queue.
        $awaiting = FacultyRanking::query()
            ->whereIn('status', collect(RankingStatus::cases())->filter(
                fn (RankingStatus $s) => $s->actor() !== null && ($user->isAdmin() || $s->actor() === $user->role)
            )->all())
            ->with(['user.department', 'rubric'])
            ->oldest('updated_at')
            ->take(8)
            ->get();

        if (! $user->isAdmin()) {
            return view('admin.dashboard-reviewer', ['awaiting' => $awaiting]);
        }

        return view('admin.dashboard', [
            'stats' => [
                'open_postings' => JobPosting::acceptingApplications()->count(),
                'applications_month' => Application::where('created_at', '>=', now()->startOfMonth())->count(),
                'in_pipeline' => Application::whereIn('status', [ApplicationStatus::Shortlisted, ApplicationStatus::Interview, ApplicationStatus::Offered])->count(),
                'pending_accounts' => User::where('status', AccountStatus::Pending)->count(),
            ],
            'recent' => Application::with(['user', 'jobPosting'])->latest()->take(6)->get(),
            'awaiting' => $awaiting,
            'charts' => [
                'trend' => Charts::applicationsTrend(),
                'status' => Charts::applicationsByStatus(),
                'department' => Charts::applicationsBy('department'),
                'campus' => Charts::applicationsBy('campus'),
            ],
        ]);
    }
}
