<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $applications = $user->applications()->with('jobPosting.department', 'jobPosting.campus')->latest()->get();

        return view('applicant.dashboard', [
            'applications' => $applications,
            'hasProfile' => $user->profile()->exists(),
            'recommended' => JobPosting::acceptingApplications()
                ->whereNotIn('id', $applications->pluck('job_posting_id'))
                ->with(['campus', 'department'])
                ->latest()->take(3)->get(),
        ]);
    }
}
