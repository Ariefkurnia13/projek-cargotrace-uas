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
            self::NONE => 'On Time',
            self::LOW => 'Low Delay (<6h)',
            self::MEDIUM => 'Medium Delay (6-24h)',
            self::HIGH => 'Critical Delay (>24h)',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::NONE => 'bg-emerald-900/40 text-emerald-300 border-emerald-800',
            self::LOW => 'bg-amber-900/40 text-amber-300 border-amber-800',
            self::MEDIUM => 'bg-orange-900/40 text-orange-300 border-orange-800',
            self::HIGH => 'bg-rose-900/40 text-rose-300 border-rose-800',
        };
    }
}