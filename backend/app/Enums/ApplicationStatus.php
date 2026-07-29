<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case RevisionRequested = 'revision_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * Get the label for the status in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Diajukan',
            self::UnderReview => 'Sedang Ditinjau',
            self::RevisionRequested => 'Butuh Revisi',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Check if the status is final (Approved or Rejected).
     */
    public function isFinal(): bool
    {
        return match ($this) {
            self::Approved, self::Rejected => true,
            default => false,
        };
    }

    /**
     * Get the allowed statuses this status can transition to.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Submitted],
            self::Submitted => [self::UnderReview, self::RevisionRequested, self::Approved, self::Rejected],
            self::UnderReview => [self::RevisionRequested, self::Approved, self::Rejected],
            self::RevisionRequested => [self::Submitted, self::Draft],
            self::Approved, self::Rejected => [],
        };
    }
}
