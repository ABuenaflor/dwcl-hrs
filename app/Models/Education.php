<?php

namespace App\Models;

use App\Enums\EducationLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    protected $table = 'educations';

    protected $fillable = ['user_id', 'level', 'school', 'course', 'inclusive_dates', 'year_graduated', 'honors'];

    protected function casts(): array
    {
        return ['level' => EducationLevel::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
