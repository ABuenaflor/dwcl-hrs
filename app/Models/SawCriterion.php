<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SawCriterion extends Model
{
    protected $table = 'saw_criteria';

    protected $fillable = [
        'job_posting_id', 'key', 'name', 'description', 'weight', 'type', 'source', 'scale_max', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['weight' => 'float', 'scale_max' => 'float'];
    }

    /**
     * Default criteria copied onto every new posting. Weights follow the
     * thesis (Appendix C) with "trainings & certificates" split out, since
     * the legacy system already scored seminars and uploaded certificates.
     */
    public const DEFAULTS = [
        ['key' => 'experience', 'name' => 'Work Experience', 'weight' => 0.30, 'source' => 'auto',
            'description' => 'Years of relevant work experience declared in the application.'],
        ['key' => 'education', 'name' => 'Educational Attainment', 'weight' => 0.20, 'source' => 'auto',
            'description' => 'Highest level completed (1 Elementary … 5 Doctorate), +1 with a professional license.'],
        ['key' => 'trainings', 'name' => 'Trainings & Certificates', 'weight' => 0.10, 'source' => 'auto',
            'description' => 'Seminars/trainings attended plus uploaded certificates.'],
        ['key' => 'tech_skills', 'name' => 'Technical Skills', 'weight' => 0.20, 'source' => 'manual', 'scale_max' => 10,
            'description' => 'HR / dean rating of job-specific skills (0–10).'],
        ['key' => 'soft_skills', 'name' => 'Soft Skills', 'weight' => 0.10, 'source' => 'manual', 'scale_max' => 10,
            'description' => 'Communication, teamwork and values fit (0–10).'],
        ['key' => 'interview', 'name' => 'Interview', 'weight' => 0.10, 'source' => 'manual', 'scale_max' => 10,
            'description' => 'Initial / screening interview rating (0–10).'],
    ];

    public function isAuto(): bool
    {
        return $this->source === 'auto';
    }

    public function isCost(): bool
    {
        return $this->type === 'cost';
    }

    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class);
    }
}
