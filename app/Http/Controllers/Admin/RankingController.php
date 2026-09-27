<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Level;
use App\Enums\RankingStatus;
use App\Http\Controllers\Controller;
use App\Models\FacultyRanking;
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
        $filters = $request->only(['q', 'level', 'status', 'cycle']);

        $rankings = FacultyRanking::query()
            ->where('status', '!=', RankingStatus::Draft)
            ->with(['user.department', 'rubric', 'recommendedRank', 'currentRank'])
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->whereHas('user', fn ($u) => $u
                ->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%")))
            ->when(Level::tryFrom($filters['level'] ?? ''), fn ($q, $level) => $q->whereHas('rubric', fn ($r) => $r->where('level', $level)))
            ->when(RankingStatus::tryFrom($filters['status'] ?? ''), fn ($q, $s) => $q->where('status', $s))
            ->when($filters['cycle'] ?? null, fn ($q, $c) => $q->where('cycle', $c))
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.rankings.index', [
            'rankings' => $rankings,
            'filters' => $filters,
            'cycles' => FacultyRanking::distinct()->orderByDesc('cycle')->pluck('cycle'),
        ]);
    }

    public function show(Request $request, FacultyRanking $ranking, RubricCalculator $calculator, RankingWorkflow $workflow): View
    {
        abort_if($ranking->status === RankingStatus::Draft, 404);
        $ranking->load(['user.department', 'user.campus', 'rubric', 'recommendedRank', 'currentRank', 'reviews.user']);

        $canAct = $workflow->canAct($request->user(), $ranking);

        return view('admin.rankings.show', RankingScores::viewData($ranking, $calculator) + [
            'canAct' => $canAct,
            'column' => $canAct ? $ranking->status->scoreColumn() : null,
        ]);
    }

    /** Save the acting committee's scores (if its stage scores) and forward. */
    public function update(Request $request, FacultyRanking $ranking, RankingWorkflow $workflow): RedirectResponse
    {
        abort_unless($workflow->canAct($request->user(), $ranking), 403);
        $request->validate(['remarks' => ['nullable', 'string', 'max:2000']]);

        if ($column = $ranking->status->scoreColumn()) {
            RankingScores::save($request, $ranking, $column);
            $workflow->recalculate($ranking);
        }

        if (! $request->boolean('forward')) {
            return back()->with('toast', 'Scores saved.');
        }

        try {
            $workflow->forward($ranking, $request->user(), $request->input('remarks'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['ranking' => $e->getMessage()]);
        }

        return redirect()->route('admin.rankings.index')->with('toast', "{$ranking->user->name}'s ranking: {$ranking->status->label()}.");
    }

    public function return(Request $request, FacultyRanking $ranking, RankingWorkflow $workflow): RedirectResponse
    {
        $data = $request->validate(['remarks' => ['required', 'string', 'max:2000']]);

        try {
            $workflow->return($ranking, $request->user(), $data['remarks']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['ranking' => $e->getMessage()]);
        }

        return redirect()->route('admin.rankings.index')->with('toast', 'Returned to the faculty member with your remarks.');
    }

    public function certificate(FacultyRanking $ranking): View
    {
        abort_unless($ranking->status === RankingStatus::Certified, 404);

        return view('admin.rankings.certificate', [
            'ranking' => $ranking->load(['user.department', 'user.campus', 'recommendedRank', 'rubric']),
        ]);
    }
}
