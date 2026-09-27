<?php

namespace App\Enums;

enum Level: string
{
    case BasicEd = 'basic_ed';
    case Tertiary = 'tertiary';
    case NonAcademic = 'non_academic';

    public function label(): string
    {
        return match ($this) {
            self::BasicEd => 'Basic Education',
            self::Tertiary => 'Tertiary (College)',
            self::NonAcademic => 'Non-Academic',
        };
    }

    /** The committee that performs final scoring for this level. */
    public function councilName(): string
    {
        return $this === self::BasicEd ? 'BERTC' : 'CRTC';
    }

    /** @return list<self> Levels whose faculty go through ranking. */
    public static function rankable(): array
    {
        return [self::BasicEd, self::Tertiary];
    }
}
