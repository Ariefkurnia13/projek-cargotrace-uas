<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case CREATED = 'CREATED';
    case BOOKED = 'BOOKED';
    case PICKED_UP = 'PICKED_UP';
    case IN_TRANSIT = 'IN_TRANSIT';
    case DELIVERED = 'DELIVERED';

    public function label(): string
    {
        return match($this) {
            self::CREATED => 'Created',
            self::BOOKED => 'Booked',
            self::PICKED_UP => 'Picked Up',
            self::IN_TRANSIT => 'In Transit',
            self::DELIVERED => 'Delivered',
        };
    }

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

    /**
     * Memeriksa apakah status merupakan status akhir / terminal (Slide 06)
     */
    public function isTerminal(): bool
    {
        return $this === self::DELIVERED;
    }

    /**
     * Index tahapan status untuk stepper progress bar (Slide 06)
     */
    public function stepIndex(): int
    {
        return match($this) {
            self::CREATED => 0,
            self::BOOKED => 1,
            self::PICKED_UP => 2,
            self::IN_TRANSIT => 3,
            self::DELIVERED => 4,
        };
    }

    /**
     * Status berikutnya yang sah menurut state machine (Slide 06)
     */
    public function allowedNextStatuses(): array
    {
        return match($this) {
            self::CREATED => [self::BOOKED],
            self::BOOKED => [self::PICKED_UP],
            self::PICKED_UP => [self::IN_TRANSIT],
            self::IN_TRANSIT => [self::DELIVERED],
            self::DELIVERED => [],
        };
    }
}