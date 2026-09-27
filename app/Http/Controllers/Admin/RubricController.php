<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Level;
use App\Enums\RankingStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicRank;
use App\Models\Rubric;
use App\Models\RubricItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Criteria are data, not code: HR can revise the rubric when the Faculty
 * Manual changes (thesis recommendation #2) without touching the system.
 */
class RubricController extends Controller
{
    public function index(): View
    {
        return view('admin.rubrics.index', [
            'rubrics' => Rubric::withCount('items')->orderBy('level')->get(),
            'ranks' => AcademicRank::orderBy('level')->orderBy('sort_order')->get()->groupBy(fn ($r) => $r->level->value),
        ]);
    }

    public function show(Rubric $rubric): View
    {
        return view('admin.rubrics.show', [
            'rubric' => $rubric,
            'tree' => $rubric->tree(),
            'parents' => $rubric->items()->get(['id', 'code', 'title', 'parent_id']),
            'inUse' => $rubric->rankings()->whereNotIn('status', [RankingStatus::Draft, RankingStatus::Certified])->exists(),
        ]);
    }

    public function storeItem(Request $request, Rubric $rubric): RedirectResponse
    {
        $data = $this->validated($request, $rubric);
        $data['sort_order'] = $rubric->items()->where('parent_id', $data['parent_id'] ?? null)->max('sort_order') + 1;
        $rubric->items()->create($data);

        return back()->with('toast', 'Criterion added.');
    }

    public function updateItem(Request $request, RubricItem $item): RedirectResponse
    {
        $data = $this->validated($request, $item->rubric, $item);
        $item->update($data);

        return back()->with('toast', 'Criterion updated.');
    }

    public function destroyItem(RubricItem $item): RedirectResponse
    {
        $item->delete();

        return back()->with('toast', 'Criterion removed.');
    }

    public function updateRanks(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ranks' => ['array'],
            'ranks.*.name' => ['required', 'string', 'max:100'],
            'ranks.*.min_points' => ['required', 'numeric', 'min:0'],
            'new.level' => ['nullable', Rule::enum(Level::class)],
            'new.name' => ['nullable', 'required_with:new.level', 'string', 'max:100'],
            'new.min_points' => ['nullable', 'required_with:new.name', 'numeric', 'min:0'],
        ]);

        foreach ($data['ranks'] ?? [] as $id => $rank) {
            AcademicRank::whereKey($id)->update($rank);
        }

        if (filled($data['new']['name'] ?? null)) {
            AcademicRank::create($data['new'] + [
                'sort_order' => AcademicRank::where('level', $data['new']['level'])->max('sort_order') + 1,
            ]);
        }

        return back()->with('toast', 'Academic ranks updated.');
    }

    private function validated(Request $request, Rubric $rubric, ?RubricItem $item = null): array
    {
        $data = $request->validate([
            'parent_id' => ['nullable', Rule::exists('rubric_items', 'id')->where('rubric_id', $rubric->id)],
            'code' => ['nullable', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:255'],
            'guide' => ['nullable', 'string', 'max:255'],
            'weight_percent' => ['nullable', 'numeric', 'between:0,100'],
            'credit_points' => ['nullable', 'numeric', 'min:0'],
            'credit_in_field' => ['nullable', 'numeric', 'min:0'],
            'credit_related' => ['nullable', 'numeric', 'min:0'],
            'max_points' => ['nullable', 'numeric', 'min:0'],
            'is_scorable' => ['boolean'],
        ]);

        abort_if($item && ($data['parent_id'] ?? null) == $item->id, 422, 'An item cannot be its own parent.');

        return $data + ['is_scorable' => false];
    }
}
