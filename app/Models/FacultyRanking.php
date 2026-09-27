<?php

namespace App\Models;

use App\Enums\RankingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacultyRanking extends Model
{
    protected $fillable = [
        'user_id', 'rubric_id', 'cycle', 'status', 'sr_total', 'drc_total', 'final_total',
        'current_rank_id', 'recommended_rank_id', 'certificate_no', 'submitted_at', 'approved_at', 'certified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RankingStatus::class,
            'sr_total' => 'float',
            'drc_total' => 'float',
            'final_total' => 'float',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'certified_at' => 'datetime',
        ];
    }

    /** Ranking cycles run every three years, starting on the current school year. */
    public static function currentCycle(): string
    {
        $start = now()->month >= 6 ? now()->year : now()->year - 1;

        return $start.'-'.($start + 3);
    }

    /** The best available total: council score, else DRC, else self-rating. */
    public function effectiveTotal(): float
    {
        return $this->final_total ?? $this->drc_total ?? $this->sr_total;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function currentRank(): BelongsTo
    {
        return $this->belongsTo(AcademicRank::class, 'current_rank_id');
    }

    public function recommendedRank(): BelongsTo
    {
        return $this->belongsTo(AcademicRank::class, 'recommended_rank_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(FacultyRankingScore::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(RankingReview::class)->latest('id');
    }
}
