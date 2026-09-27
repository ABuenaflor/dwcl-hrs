<?php

namespace App\Enums;

/**
 * Mirrors the hiring flow in the thesis (Fig. 4 / Fig. 7): screening →
 * shortlist → interview → offer, where the applicant may accept or decline.
 */
enum ApplicationStatus: string
{
    case Submitted = 'submitted';
    case Shortlisted = 'shortlisted';
    case Interview = 'interview';
    case Offered = 'offered';
    case Hired = 'hired';
    case Declined = 'declined';   // applicant turned the offer down
    case Rejected = 'rejected';   // not selected
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Interview => 'For Interview',
            self::Declined => 'Offer Declined',
            self::Rejected => 'Not Selected',
            default => ucfirst($this->value),
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Submitted => 'info',
            self::Shortlisted, self::Interview => 'brand',
            self::Offered => 'warning',
            self::Hired => 'success',
            self::Declined, self::Rejected, self::Withdrawn => 'neutral',
        };
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Hired, self::Declined, self::Rejected, self::Withdrawn], true);
    }

    /** @return list<self> Statuses HR can move an application to from this one. */
    public function nextForHr(): array
    {
        return match ($this) {
            self::Submitted => [self::Shortlisted, self::Rejected],
            self::Shortlisted => [self::Interview, self::Rejected],
            self::Interview => [self::Offered, self::Rejected],
            self::Offered => [self::Hired, self::Declined],
            default => [],
        };
    }
}
