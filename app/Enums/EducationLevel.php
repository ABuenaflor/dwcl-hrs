<?php

namespace App\Enums;

enum EducationLevel: string
{
    case Elementary = 'elementary';
    case Secondary = 'secondary';
    case College = 'college';
    case Masters = 'masters';
    case Doctorate = 'doctorate';

    public function label(): string
    {
        return match ($this) {
            self::College => "College / Bachelor's",
            self::Masters => "Master's",
            default => ucfirst($this->value),
        };
    }

    /** Ordinal used as the raw value of the SAW "education" criterion. */
    public function points(): int
    {
        return match ($this) {
            self::Elementary => 1,
            self::Secondary => 2,
            self::College => 3,
            self::Masters => 4,
            self::Doctorate => 5,
        };
    }
}
