<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'user_id', 'job_posting_id', 'status', 'years_experience', 'work_experience', 'trainings',
        'trainings_count', 'skills', 'license', 'cover_letter', 'saw_score', 'saw_rank', 'hr_notes',
        'interview_at', 'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'years_experience' => 'float',
            'saw_score' => 'float',
            'interview_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ApplicationScore::class);
    }
}
