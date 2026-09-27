<?php

namespace App\Support;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

/**
 * Aggregations for the dashboard and reports, returned as
 * ['labels' => [...], 'data' => [...]] ready for Chart.js.
 */
class Charts
{
    public static function applicationsTrend(int $months = 6, ?Builder $scope = null): array
    {
        $start = now()->subMonths($months - 1)->startOfMonth();
        $counts = ($scope ?? Application::query())
            ->where('applications.created_at', '>=', $start)
            ->get(['applications.created_at'])
            ->countBy(fn ($a) => $a->created_at->format('Y-m'));

        $labels = $data = [];
        foreach (CarbonPeriod::create($start, '1 month', now()->startOfMonth()) as $month) {
            $labels[] = $month->format('M Y');
            $data[] = $counts->get($month->format('Y-m'), 0);
        }

        return compact('labels', 'data');
    }

    public static function applicationsByStatus(?Builder $scope = null): array
    {
        $counts = ($scope ?? Application::query())
            ->toBase()
            ->selectRaw('applications.status, count(*) as total')
            ->groupBy('applications.status')
            ->pluck('total', 'status');

        $labels = $data = [];
        foreach (ApplicationStatus::cases() as $status) {
            if ($counts->has($status->value)) {
                $labels[] = $status->label();
                $data[] = (int) $counts[$status->value];
            }
        }

        return compact('labels', 'data');
    }

    /** @param  'department'|'campus'  $dimension */
    public static function applicationsBy(string $dimension, ?Builder $scope = null): array
    {
        $table = $dimension === 'campus' ? 'campuses' : 'departments';

        $rows = ($scope ?? Application::query())
            ->toBase()
            ->join('job_postings', 'job_postings.id', '=', 'applications.job_posting_id')
            ->join($table, "{$table}.id", '=', "job_postings.{$dimension}_id")
            ->selectRaw("{$table}.name as label, count(*) as total")
            ->groupBy("{$table}.name")
            ->orderByDesc('total')
            ->get();

        return ['labels' => $rows->pluck('label')->all(), 'data' => $rows->pluck('total')->map(fn ($v) => (int) $v)->all()];
    }
}
