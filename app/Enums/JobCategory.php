<?php

namespace App\Enums;

enum JobCategory: string
{
    case Academic = 'academic';
    case NonAcademic = 'non_academic';

    public function label(): string
    {
        return match ($this) {
            self::Academic => 'Academic',
            self::NonAcademic => 'Non-Academic',
        };
    }
}
