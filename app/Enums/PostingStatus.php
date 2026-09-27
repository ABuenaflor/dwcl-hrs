<?php

namespace App\Enums;

enum PostingStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function tone(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Open => 'success',
            self::Closed => 'danger',
        };
    }
}
