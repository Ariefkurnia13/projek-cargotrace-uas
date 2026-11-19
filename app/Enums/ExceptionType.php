<?php

namespace App\Enums;

enum ExceptionType: string
{
    case CUSTOMS_HOLD = 'CUSTOMS_HOLD';
    case WEATHER_DELAY = 'WEATHER_DELAY';
    case PORT_CONGESTION = 'PORT_CONGESTION';
    case DAMAGED = 'DAMAGED';
    case MISROUTED = 'MISROUTED';
    case DOCUMENTATION_ISSUE = 'DOCUMENTATION_ISSUE';

    public function label(): string
    {
        return match($this) {
            self::CUSTOMS_HOLD => 'Customs Hold',
            self::WEATHER_DELAY => 'Weather Delay',
            self::PORT_CONGESTION => 'Port Congestion',
            self::DAMAGED => 'Cargo Damaged',
            self::MISROUTED => 'Misrouted Shipment',
            self::DOCUMENTATION_ISSUE => 'Documentation Issue',
        };
    }

    public function defaultSeverity(): DelaySeverity
    {
        return match($this) {
            self::CUSTOMS_HOLD => DelaySeverity::HIGH,
            self::PORT_CONGESTION => DelaySeverity::MEDIUM,
            self::WEATHER_DELAY => DelaySeverity::MEDIUM,
            self::DAMAGED => DelaySeverity::HIGH,
            self::MISROUTED => DelaySeverity::HIGH,
            self::DOCUMENTATION_ISSUE => DelaySeverity::LOW,
        };
    }
}
