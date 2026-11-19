<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case CREATED = 'CREATED';
    case BOOKED = 'BOOKED';
    case PICKED_UP = 'PICKED_UP';
    case IN_TRANSIT = 'IN_TRANSIT';
    case DELIVERED = 'DELIVERED';

    /**
     * Define strictly allowed sequential status transitions.
     * Prevents illegal jumps (e.g. CREATED -> IN_TRANSIT)
     * and illegal backwards movements from terminal state (DELIVERED).
     */
    public function allowedNextStatuses(): array
    {
        return match($this) {
            self::CREATED => [self::BOOKED],
            self::BOOKED => [self::PICKED_UP],
            self::PICKED_UP => [self::IN_TRANSIT],
            self::IN_TRANSIT => [self::DELIVERED],
            self::DELIVERED => [], // Terminal status
        };
    }

    public function canTransitionTo(self $targetStatus): bool
    {
        return in_array($targetStatus, $this->allowedNextStatuses(), true);
    }

    public function isTerminal(): bool
    {
        return $this === self::DELIVERED;
    }

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
            self::CREATED => 'bg-slate-100 text-slate-800 border-slate-200',
            self::BOOKED => 'bg-sky-100 text-sky-800 border-sky-200',
            self::PICKED_UP => 'bg-amber-100 text-amber-800 border-amber-200',
            self::IN_TRANSIT => 'bg-blue-600 text-white border-blue-700 animate-pulse',
            self::DELIVERED => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        };
    }

    public function stepIndex(): int
    {
        return match($this) {
            self::CREATED => 1,
            self::BOOKED => 2,
            self::PICKED_UP => 3,
            self::IN_TRANSIT => 4,
            self::DELIVERED => 5,
        };
    }
}
