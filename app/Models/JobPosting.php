<?php

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\JobCategory;
use App\Enums\PostingStatus;
use Database\Factories\JobPostingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    /** @use HasFactory<JobPostingFactory> */
    use HasFactory;

    protected $fillable = [
        'title', 'campus_id', 'department_id', 'category', 'employment_type', 'schedule',
        'slots', 'description', 'qualifications', 'status', 'closes_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'qualifications' => 'array',
            'closes_at' => 'date',
            'category' => JobCategory::class,
            'employment_type' => EmploymentType::class,
            'status' => PostingStatus::class,
        ];
    }

    /** Postings applicants can currently see and apply to. */
    public function scopeAcceptingApplications(Builder $query): Builder
    {
        return $query->where('status', PostingStatus::Open)
            ->where(fn (Builder $q) => $q->whereNull('closes_at')->orWhereDate('closes_at', '>=', today()));
    }

    public function isAcceptingApplications(): bool
    {
        return $this->status === PostingStatus::Open
            && ($this->closes_at === null || $this->closes_at->endOfDay()->isFuture());
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(SawCriterion::class)->orderBy('sort_order');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
