<?php

namespace App\Models;

use App\Enums\RankingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankingReview extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['faculty_ranking_id', 'user_id', 'from_status', 'to_status', 'remarks'];

    protected function casts(): array
    {
        return ['from_status' => RankingStatus::class, 'to_status' => RankingStatus::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
