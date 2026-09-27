<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RubricItem extends Model
{
    protected $fillable = [
        'rubric_id', 'parent_id', 'code', 'title', 'guide', 'weight_percent', 'credit_points',
        'credit_in_field', 'credit_related', 'max_points', 'is_scorable', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_scorable' => 'boolean',
            'weight_percent' => 'float',
            'credit_points' => 'float',
            'credit_in_field' => 'float',
            'credit_related' => 'float',
            'max_points' => 'float',
        ];
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(Rubric::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }
}
