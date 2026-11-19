<?php

namespace App\Enums;

enum DelaySeverity: string
{
    case NONE = 'NONE';
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';

    public function label(): string
    {
        return match($this) {
            self::NONE => 'On Schedule',
            self::LOW => 'Low Delay (≤ 6h)',
            self::MEDIUM => 'Medium Delay (6h - 24h)',
            self::HIGH => 'Critical Delay (> 24h)',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::NONE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::LOW => 'bg-blue-50 text-blue-700 border-blue-200',
            self::MEDIUM => 'bg-amber-100 text-amber-800 border-amber-300 font-semibold',
            self::HIGH => 'bg-red-100 text-red-800 border-red-300 font-bold animate-pulse',
        };
    }
}
