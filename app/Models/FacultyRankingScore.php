<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultyRankingScore extends Model
{
    protected $fillable = [
        'faculty_ranking_id', 'rubric_item_id', 'sr_points', 'drc_points', 'final_points',
        'evidence_path', 'evidence_name',
    ];

    protected function casts(): array
    {
        return ['sr_points' => 'float', 'drc_points' => 'float', 'final_points' => 'float'];
    }

    public function ranking(): BelongsTo
    {
        return $this->belongsTo(FacultyRanking::class, 'faculty_ranking_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(RubricItem::class, 'rubric_item_id');
    }
}
