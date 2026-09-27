<?php

namespace App\Models;

use App\Enums\Level;
use Illuminate\Database\Eloquent\Model;

class AcademicRank extends Model
{
    protected $fillable = ['level', 'name', 'min_points', 'sort_order'];

    protected function casts(): array
    {
        return ['level' => Level::class, 'min_points' => 'float'];
    }

    /** Highest rank on this level whose threshold the given total reaches. */
    public static function forPoints(Level $level, float $points): ?self
    {
        return static::where('level', $level)
            ->where('min_points', '<=', $points)
            ->orderByDesc('min_points')
            ->first();
    }
}
