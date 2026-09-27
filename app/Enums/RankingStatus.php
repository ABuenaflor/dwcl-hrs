<?php

namespace App\Enums;

/**
 * Faculty ranking workflow from the Faculty Manual (thesis Fig. 8):
 * self-rating → DRC review → CRTC/BERTC review → VP endorsement →
 * President's approval (recorded by HRDO) → certificate of rank.
 */
enum RankingStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case DrcReviewed = 'drc_reviewed';
    case CrtcReviewed = 'crtc_reviewed';
    case Endorsed = 'endorsed';
    case Approved = 'approved';
    case Certified = 'certified';
    case Returned = 'returned';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Awaiting DRC',
            self::DrcReviewed => 'Awaiting Council',
            self::CrtcReviewed => 'Awaiting Endorsement',
            self::Endorsed => 'Awaiting Approval',
            self::Approved => 'Approved',
            self::Certified => 'Certified',
            self::Returned => 'Returned to Faculty',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Returned => 'danger',
            self::Approved, self::Certified => 'success',
            default => 'warning',
        };
    }

    public function step(): int
    {
        return match ($this) {
            self::Draft, self::Returned => 0,
            self::Submitted => 1,
            self::DrcReviewed => 2,
            self::CrtcReviewed => 3,
            self::Endorsed => 4,
            self::Approved => 5,
            self::Certified => 6,
        };
    }

    public function isEditableByFaculty(): bool
    {
        return in_array($this, [self::Draft, self::Returned], true);
    }

    /** The role expected to act on a ranking in this status. Admin may always act. */
    public function actor(): ?Role
    {
        return match ($this) {
            self::Submitted => Role::Drc,
            self::DrcReviewed => Role::Crtc,
            self::CrtcReviewed => Role::Vp,
            self::Endorsed, self::Approved => Role::Admin,
            default => null,
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Submitted => self::DrcReviewed,
            self::DrcReviewed => self::CrtcReviewed,
            self::CrtcReviewed => self::Endorsed,
            self::Endorsed => self::Approved,
            self::Approved => self::Certified,
            default => null,
        };
    }

    /** Which score column the acting committee fills in, if any. */
    public function scoreColumn(): ?string
    {
        return match ($this) {
            self::Submitted => 'drc_points',
            self::DrcReviewed => 'final_points',
            default => null,
        };
    }

    public function forwardLabel(): ?string
    {
        return match ($this) {
            self::Submitted => 'Save & forward to Council',
            self::DrcReviewed => 'Save & forward for endorsement',
            self::CrtcReviewed => 'Endorse to the President',
            self::Endorsed => "Record President's approval",
            self::Approved => 'Issue certificate of rank',
            default => null,
        };
    }

    public function canBeReturned(): bool
    {
        return in_array($this, [self::Submitted, self::DrcReviewed, self::CrtcReviewed, self::Endorsed], true);
    }

    /** @return list<self> Stages shown in the progress tracker. */
    public static function timeline(): array
    {
        return [
            self::Draft, self::Submitted, self::DrcReviewed, self::CrtcReviewed,
            self::Endorsed, self::Approved, self::Certified,
        ];
    }
}
