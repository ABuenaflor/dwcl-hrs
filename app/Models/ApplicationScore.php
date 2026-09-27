<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationScore extends Model
{
    protected $fillable = ['application_id', 'saw_criterion_id', 'value', 'rated_by'];

    protected function casts(): array
    {
        return ['value' => 'float'];
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(SawCriterion::class, 'saw_criterion_id');
    }
}
