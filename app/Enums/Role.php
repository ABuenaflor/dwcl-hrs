<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';         // HRDO coordinator / director
    case Drc = 'drc';             // Department / School Ranking Committee
    case Crtc = 'crtc';           // College Rank & Tenure Council / BERTC
    case Vp = 'vp';               // VPAA / VPBE — endorsement
    case Faculty = 'faculty';
    case Applicant = 'applicant';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'HRDO Administrator',
            self::Drc => 'Department Ranking Committee',
            self::Crtc => 'Rank & Tenure Council',
            self::Vp => 'Vice President (VPAA / VPBE)',
            self::Faculty => 'Faculty',
            self::Applicant => 'Applicant',
        };
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::Admin, self::Drc, self::Crtc, self::Vp], true);
    }

    public function isReviewer(): bool
    {
        return in_array($this, [self::Drc, self::Crtc, self::Vp], true);
    }
}
