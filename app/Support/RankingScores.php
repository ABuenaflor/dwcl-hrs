<?php

namespace App\Support;

use App\Models\FacultyRanking;
use App\Services\RubricCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Reading and writing one score column (SR, DRC or council) of a ranking.
 * Shared by the faculty self-rating form and the committee review form.
 */
class RankingScores
{
    public const COLUMNS = ['sr_points', 'drc_points', 'final_points'];

    public static function viewData(FacultyRanking $ranking, RubricCalculator $calculator): array
    {
        $tree = $ranking->rubric->tree();
        $scores = $ranking->scores()->get()->keyBy('rubric_item_id');

        $subtotals = [];
        foreach (self::COLUMNS as $column) {
            $subtotals[$column] = $calculator->calculate($tree, $scores->map->{$column}->all())['subtotals'];
        }

        return [
            'ranking' => $ranking,
            'tree' => $tree,
            'scores' => $scores,
            'subtotals' => $subtotals,
        ];
    }

    public static function save(Request $request, FacultyRanking $ranking, string $column, bool $withEvidence = false): void
    {
        $items = $ranking->rubric->items()->where('is_scorable', true)->get()->keyBy('id');

        $rules = ['points' => ['array']];
        foreach ($items as $id => $item) {
            $rules["points.{$id}"] = ['nullable', 'numeric', 'min:0', 'max:'.($item->max_points ?? 1000)];
            if ($withEvidence) {
                $rules["evidence.{$id}"] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];
            }
        }
        $request->validate($rules, [], ['points.*' => 'points', 'evidence.*' => 'evidence']);

        DB::transaction(function () use ($request, $ranking, $column, $items, $withEvidence) {
            $existing = $ranking->scores()->get()->keyBy('rubric_item_id');

            foreach ($items as $id => $item) {
                $value = $request->input("points.{$id}");
                $file = $withEvidence ? $request->file("evidence.{$id}") : null;
                $score = $existing->get($id);

                if ($score === null && blank($value) && ! $file) {
                    continue;
                }

                $score ??= $ranking->scores()->make(['rubric_item_id' => $id]);
                $score->{$column} = blank($value) ? null : round((float) $value, 2);

                if ($file) {
                    if ($score->evidence_path) {
                        Storage::disk('local')->delete($score->evidence_path);
                    }
                    $score->evidence_path = $file->store("evidence/{$ranking->id}", 'local');
                    $score->evidence_name = $file->getClientOriginalName();
                }

                $score->save();
            }
        });
    }
}
