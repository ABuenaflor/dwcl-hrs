<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\EmploymentType;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name', 'middle_name', 'last_name', 'email', 'password', 'role', 'status', 'phone',
        'employee_no', 'campus_id', 'department_id', 'academic_rank_id', 'employment_type',
        'designation', 'date_hired', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'date_hired' => 'date',
            'password' => 'hashed',
            'role' => Role::class,
            'status' => AccountStatus::class,
            'employment_type' => EmploymentType::class,
        ];
    }

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getFullNameAttribute(): string
    {
        $middle = $this->middle_name ? ' '.mb_substr($this->middle_name, 0, 1).'.' : '';

        return "{$this->first_name}{$middle} {$this->last_name}";
    }

    public function getInitialsAttribute(): string
    {
        return mb_strtoupper(mb_substr($this->first_name, 0, 1).mb_substr($this->last_name, 0, 1));
    }

    public function hasRole(Role ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::Active;
    }

    /** Where this user lands after signing in. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            Role::Applicant => route('applicant.dashboard'),
            Role::Faculty => route('faculty.dashboard'),
            default => route('admin.dashboard'),
        };
    }

    public function scopeRole(Builder $query, Role $role): Builder
    {
        return $query->where('role', $role);
    }

    public function campus(): BelongsTo
    {
        return $this->belongsTo(Campus::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function academicRank(): BelongsTo
    {
        return $this->belongsTo(AcademicRank::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(ApplicantProfile::class);
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class)->orderByRaw(
            "case level when 'doctorate' then 1 when 'masters' then 2 when 'college' then 3 when 'secondary' then 4 else 5 end"
        );
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function rankings(): HasMany
    {
        return $this->hasMany(FacultyRanking::class);
    }
}
