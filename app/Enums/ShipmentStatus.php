<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case CREATED = 'CREATED';
    case BOOKED = 'BOOKED';
    case PICKED_UP = 'PICKED_UP';
    case IN_TRANSIT = 'IN_TRANSIT';
    case DELIVERED = 'DELIVERED';

    public function badgeClass(): string
    {
        return match($this) {
            self::CREATED => 'bg-slate-700 text-slate-200 border-slate-600',
            self::BOOKED => 'bg-indigo-900/60 text-indigo-300 border-indigo-700',
            self::PICKED_UP => 'bg-purple-900/60 text-purple-300 border-purple-700',
            self::IN_TRANSIT => 'bg-blue-900/60 text-blue-300 border-blue-700',
            self::DELIVERED => 'bg-emerald-900/60 text-emerald-300 border-emerald-700',
        };
    }
}