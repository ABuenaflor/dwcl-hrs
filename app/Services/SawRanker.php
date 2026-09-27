<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\SawCriterion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Simple Additive Weighting over the applicants of one job posting.
 *
 *   1. Build the decision matrix x[i][j] (applicant i, criterion j).
 *   2. Normalize each column:  benefit r = x / max(x),  cost r = min(x) / x.
 *   3. Preference value       V[i] = Σ w[j] · r[i][j]   (weights re-scaled to sum to 1).
 *   4. Rank by V descending; ties share a rank (1, 2, 2, 4).
 */
class SawRanker
{
    /**
     * @return array{
     *     criteria: Collection<int, SawCriterion>,
     *     weights: array<int, float>,
     *     rows: list<array{application: Application, raw: array<int, float>, normalized: array<int, float>, weighted: array<int, float>, score: float, rank: int}>
     * }
     */
    public function evaluate(JobPosting $posting): array
    {
        $criteria = $posting->criteria()->get();
        $applications = $posting->applications()
            ->where('status', '!=', ApplicationStatus::Withdrawn)
            ->with(['user:id,first_name,last_name,email', 'user.educations:id,user_id,level', 'scores'])
            ->withCount(['documents as certificate_count' => fn ($q) => $q->where('type', 'certificate')])
            ->oldest('id')
            ->get();

        $weights = $this->normalizedWeights($criteria);

        $raw = [];
        foreach ($applications as $app) {
            foreach ($criteria as $c) {
                $raw[$app->id][$c->id] = $this->rawValue($c, $app);
            }
        }

        $rows = [];
        foreach ($applications as $app) {
            $normalized = $weighted = [];
            foreach ($criteria as $c) {
                $column = array_column($raw, $c->id);
                $normalized[$c->id] = $this->normalize($raw[$app->id][$c->id], $column, $c->isCost());
                $weighted[$c->id] = $normalized[$c->id] * $weights[$c->id];
            }

            $rows[] = [
                'application' => $app,
                'raw' => $raw[$app->id],
                'normalized' => $normalized,
                'weighted' => $weighted,
                'score' => round(array_sum($weighted), 6),
                'rank' => 0,
            ];
        }

        // Highest score first; earlier application wins display order on ties.
        usort($rows, fn ($a, $b) => [$b['score'], $a['application']->id] <=> [$a['score'], $b['application']->id]);

        foreach ($rows as $i => &$row) {
            $row['rank'] = ($i > 0 && $row['score'] === $rows[$i - 1]['score']) ? $rows[$i - 1]['rank'] : $i + 1;
        }
        unset($row);

        return ['criteria' => $criteria, 'weights' => $weights, 'rows' => $rows];
    }

    /** Recompute and persist the cached score/rank of every application in the posting. */
    public function rerank(JobPosting $posting): void
    {
        $result = $this->evaluate($posting);

        DB::transaction(function () use ($posting, $result) {
            $posting->applications()->where('status', ApplicationStatus::Withdrawn)
                ->update(['saw_score' => null, 'saw_rank' => null]);

            foreach ($result['rows'] as $row) {
                $app = $row['application'];
                if ($app->saw_score !== $row['score'] || $app->saw_rank !== $row['rank']) {
                    $app->forceFill(['saw_score' => $row['score'], 'saw_rank' => $row['rank']])->saveQuietly();
                }
            }
        });
    }

    /** Value of criterion $c for application $app before normalization. */
    public function rawValue(SawCriterion $c, Application $app): float
    {
        if (! $c->isAuto()) {
            return (float) ($app->scores->firstWhere('saw_criterion_id', $c->id)?->value ?? 0);
        }

        return match ($c->key) {
            'experience' => (float) $app->years_experience,
            'education' => (float) ($app->user->educations->max(fn ($e) => $e->level->points()) ?? 0)
                + (filled($app->license) ? 1 : 0),
            'trainings' => (float) $app->trainings_count + (int) ($app->certificate_count ?? $app->documents()->where('type', 'certificate')->count()),
            default => 0.0,
        };
    }

    /** @param  array<int, float>  $column */
    public function normalize(float $value, array $column, bool $cost = false): float
    {
        if ($cost) {
            $positives = array_filter($column, fn ($v) => $v > 0);
            if ($value <= 0) {
                return 1.0; // zero cost is the ideal value
            }

            return $positives ? min($positives) / $value : 0.0;
        }

        $max = $column ? max($column) : 0;

        return $max > 0 ? $value / $max : 0.0;
    }

    /**
     * @param  Collection<int, SawCriterion>  $criteria
     * @return array<int, float>
     */
    public function normalizedWeights(Collection $criteria): array
    {
        $sum = $criteria->sum('weight');

        return $criteria->mapWithKeys(fn (SawCriterion $c) => [
            $c->id => $sum > 0 ? $c->weight / $sum : 0.0,
        ])->all();
    }
}
