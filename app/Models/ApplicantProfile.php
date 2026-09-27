<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicantProfile extends Model
{
    protected $fillable = [
        'user_id', 'date_of_birth', 'place_of_birth', 'sex', 'civil_status', 'citizenship', 'religion',
        'height_cm', 'weight_kg', 'blood_type', 'pagibig_no', 'philhealth_no', 'tin_no', 'sss_no',
        'contact_number', 'residential_address', 'permanent_address', 'father_name', 'mother_maiden_name',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            // Government-issued numbers are personal data: keep them encrypted at rest.
            'pagibig_no' => 'encrypted',
            'philhealth_no' => 'encrypted',
            'tin_no' => 'encrypted',
            'sss_no' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
