<?php

namespace App\Services;

use App\Models\RubricItem;
use Illuminate\Support\Collection;

/**
 * Totals a faculty member's points over a rubric tree. A node's subtotal
 * is its own points (if scorable) plus its children's subtotals, capped at
 * the node's `max_points` — so e.g. seminars can never exceed 25 points
 * however many are claimed, exactly as the Faculty Manual prescribes.
 */
class RubricCalculator
{
    /**
     * @param  Collection<int, RubricItem>  $roots  tree from Rubric::tree()
     * @param  array<int, float|null>  $points  rubric_item_id => points
     * @return array{total: float, subtotals: array<int, float>}
     */
    public function calculate(Collection $roots, array $points): array
    {
        $subtotals = [];
        $total = 0.0;

        foreach ($roots as $root) {
            $total += $this->subtotal($root, $points, $subtotals);
        }

        return ['total' => round($total, 2), 'subtotals' => $subtotals];
    }

    private function subtotal(RubricItem $item, array $points, array &$subtotals): float
    {
        $sum = $item->is_scorable ? (float) ($points[$item->id] ?? 0) : 0.0;

        foreach ($item->children as $child) {
            $sum += $this->subtotal($child, $points, $subtotals);
        }

        if ($item->max_points !== null) {
            $sum = min($sum, $item->max_points);
        }

        return $subtotals[$item->id] = round($sum, 2);
    }
}
