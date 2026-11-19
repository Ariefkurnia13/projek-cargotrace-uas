<?php

namespace App\Enums;

enum ExceptionStatus: string
{
    case OPEN = 'OPEN';
    case INVESTIGATING = 'INVESTIGATING';
    case RESOLVED = 'RESOLVED';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Open',
            self::INVESTIGATING => 'Investigating',
            self::RESOLVED => 'Resolved',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::OPEN => 'bg-rose-100 text-rose-800 border-rose-300 font-semibold',
            self::INVESTIGATING => 'bg-amber-100 text-amber-800 border-amber-300',
            self::RESOLVED => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        };
    }

    public function canTransitionTo(self $targetStatus): bool
    {
        return match($this) {
            self::OPEN => in_array($targetStatus, [self::INVESTIGATING, self::RESOLVED], true),
            self::INVESTIGATING => $targetStatus === self::RESOLVED,
            self::RESOLVED => false, // Final state for exception
        };
    }
}
