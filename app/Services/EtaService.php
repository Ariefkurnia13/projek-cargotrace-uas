<?php

namespace App\Services;

use App\Enums\DelaySeverity;
use App\Models\Shipment;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class EtaService
{
    protected int $groupThresholdHours;
    protected array $severityThresholds;

    public function __construct()
    {
        $this->groupThresholdHours = config('cargotrace.delay_threshold_hours', 12);
        $this->severityThresholds = config('cargotrace.severity_thresholds', [
            'low_max' => 6,
            'medium_max' => 24,
        ]);
    }

    /**
     * Calculate delay metrics by comparing Estimated Arrival against Expected Arrival.
     *
     * Deterministic rule:
     * - If estimated_arrival > expected_arrival, delay duration = difference in minutes.
     * - If delay_hours >= groupThresholdHours, flagged as delayed.
     * - Severity classified as NONE, LOW (<=6h), MEDIUM (>6-24h), HIGH (>24h).
     */
    public function evaluateDelay(CarbonInterface|string $expectedArrival, CarbonInterface|string $estimatedArrival): array
    {
        $expected = Carbon::parse($expectedArrival);
        $estimated = Carbon::parse($estimatedArrival);

        if ($estimated->lessThanOrEqualTo($expected)) {
            return [
                'is_delayed' => false,
                'delay_minutes' => 0,
                'delay_hours' => 0.0,
                'severity' => DelaySeverity::NONE,
                'threshold_hours' => $this->groupThresholdHours,
                'reason' => 'Shipment is on schedule. Estimated arrival is prior to or meets expected SLA arrival.',
            ];
        }

        $delayMinutes = $expected->diffInMinutes($estimated);
        $delayHours = round($delayMinutes / 60, 2);

        // Check if it breaches group threshold or starts registering delay
        $isDelayed = $delayHours >= $this->groupThresholdHours || $delayMinutes > 0;

        // Classify severity based on deterministic thresholds
        $severity = $this->classifySeverity($delayHours);

        $reason = sprintf(
            'Estimated arrival (%s) exceeds expected SLA arrival (%s) by %s hours. Exceeds group threshold of %s hours (Severity: %s).',
            $estimated->toIso8601String(),
            $expected->toIso8601String(),
            $delayHours,
            $this->groupThresholdHours,
            $severity->value
        );

        return [
            'is_delayed' => $isDelayed,
            'delay_minutes' => $delayMinutes,
            'delay_hours' => $delayHours,
            'severity' => $severity,
            'threshold_hours' => $this->groupThresholdHours,
            'reason' => $reason,
        ];
    }

    /**
     * Classify delay severity into LOW, MEDIUM, or HIGH
     */
    public function classifySeverity(float $delayHours): DelaySeverity
    {
        if ($delayHours <= 0) {
            return DelaySeverity::NONE;
        }

        if ($delayHours <= $this->severityThresholds['low_max']) {
            return DelaySeverity::LOW;
        }

        if ($delayHours <= $this->severityThresholds['medium_max']) {
            return DelaySeverity::MEDIUM;
        }

        return DelaySeverity::HIGH;
    }

    /**
     * Recalculate and update delay attributes on a Shipment model.
     */
    public function refreshShipmentDelay(Shipment $shipment, ?CarbonInterface $newEstimatedArrival = null): Shipment
    {
        if ($newEstimatedArrival) {
            $shipment->estimated_arrival = Carbon::parse($newEstimatedArrival);
        }

        $evaluation = $this->evaluateDelay($shipment->expected_arrival, $shipment->estimated_arrival);

        $shipment->is_delayed = $evaluation['is_delayed'];
        $shipment->delay_duration_minutes = $evaluation['delay_minutes'];
        $shipment->delay_severity = $evaluation['severity'];
        $shipment->delay_reason = $evaluation['reason'];
        $shipment->save();

        return $shipment;
    }

    public function getGroupThresholdHours(): int
    {
        return $this->groupThresholdHours;
    }

    public function setGroupThresholdHours(int $hours): self
    {
        $this->groupThresholdHours = $hours;
        return $this;
    }
}
