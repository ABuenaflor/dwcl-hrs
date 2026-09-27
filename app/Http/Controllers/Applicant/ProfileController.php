<?php

namespace App\Http\Controllers\Applicant;

use App\Actions\SaveApplicantProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['profile', 'educations']);

        return view('applicant.profile', ['user' => $user]);
    }

    public function update(Request $request, SaveApplicantProfile $save): RedirectResponse
    {
        $data = $request->validate(SaveApplicantProfile::rules(), [], SaveApplicantProfile::attributes());

        $save($request->user(), $data);

        return back()->with('toast', 'Profile saved.');
    }
}
