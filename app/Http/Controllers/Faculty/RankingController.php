<?php

namespace App\Http\Controllers\Faculty;

use App\Enums\Level;
use App\Enums\RankingStatus;
use App\Http\Controllers\Controller;
use App\Models\FacultyRanking;
use App\Models\Rubric;
use App\Services\RankingWorkflow;
use App\Services\RubricCalculator;
use App\Support\RankingScores;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class RankingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['department', 'campus', 'academicRank']);
        $rankings = $user->rankings()->with(['rubric', 'recommendedRank'])->latest()->get();
        $cycle = FacultyRanking::currentCycle();

        return view('faculty.dashboard', [
            'user' => $user,
            'rankings' => $rankings,
            'cycle' => $cycle,
            'current' => $rankings->firstWhere('cycle', $cycle),
            'eligible' => in_array($user->department?->level, Level::rankable(), true),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user()->load('department');
        $level = $user->department?->level;

        abort_unless(in_array($level, Level::rankable(), true), 422, 'Only academic faculty can be ranked.');
        $rubric = Rubric::activeFor($level) ?? abort(422, 'No active rubric for your level yet.');

        $ranking = $user->rankings()->firstOrCreate(
            ['cycle' => FacultyRanking::currentCycle()],
            ['rubric_id' => $rubric->id, 'status' => RankingStatus::Draft, 'current_rank_id' => $user->academic_rank_id],
        );

        return redirect()->route('faculty.rankings.show', $ranking);
    }

    public function show(Request $request, FacultyRanking $ranking, RubricCalculator $calculator): View
    {
        $this->authorizeOwner($request, $ranking);
        $ranking->load(['rubric', 'recommendedRank', 'currentRank', 'reviews.user']);

        return view('faculty.ranking', RankingScores::viewData($ranking, $calculator) + [
            'editable' => $ranking->status->isEditableByFaculty(),
        ]);
    }

    public function update(Request $request, FacultyRanking $ranking, RankingWorkflow $workflow): RedirectResponse
    {
        $this->authorizeOwner($request, $ranking);
        abort_unless($ranking->status->isEditableByFaculty(), 422, 'This ranking is under review and can no longer be edited.');

        RankingScores::save($request, $ranking, 'sr_points', withEvidence: true);
        $workflow->recalculate($ranking);

        if ($request->boolean('submit')) {
            return $this->submit($request, $ranking, $workflow);
        }

        return back()->with('toast', 'Self-rating saved as draft.');
    }

    public function submit(Request $request, FacultyRanking $ranking, RankingWorkflow $workflow): RedirectResponse
    {
        $this->authorizeOwner($request, $ranking);

        try {
            $workflow->submit($ranking, $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['ranking' => $e->getMessage()]);
        }

        return redirect()->route('faculty.rankings.show', $ranking)
            ->with('toast', 'Submitted to the Department Ranking Committee.');
    }

    public function certificate(Request $request, FacultyRanking $ranking): View
    {
        $this->authorizeOwner($request, $ranking);
        abort_unless($ranking->status === RankingStatus::Certified, 404);

        return view('admin.rankings.certificate', [
            'ranking' => $ranking->load(['user.department', 'user.campus', 'recommendedRank', 'rubric']),
        ]);
    }

    private function authorizeOwner(Request $request, FacultyRanking $ranking): void
    {
        abort_unless($ranking->user_id === $request->user()->id, 404);
    }
}
