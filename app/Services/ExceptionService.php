<?php

namespace App\Services;

use App\Enums\DelaySeverity;
use App\Enums\ExceptionStatus;
use App\Enums\ExceptionType;
use App\Models\InAppNotification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExceptionService
{
    public function __construct(
        protected EtaService $etaService,
        protected AuditService $auditService
    ) {}

    /**
     * Create a new exception for a shipment.
     */
    public function createException(
        Shipment $shipment,
        ExceptionType|string $type,
        string $description,
        User $reporter,
        ?DelaySeverity $severity = null,
        float $impactDelayHours = 0.0,
        ?string $rootCause = null
    ): ShipmentException {
        $exceptionType = is_string($type) ? ExceptionType::from($type) : $type;
        $severity = $severity ?? $exceptionType->defaultSeverity();

        return DB::transaction(function () use ($shipment, $exceptionType, $description, $reporter, $severity, $impactDelayHours, $rootCause) {
            $exception = ShipmentException::create([
                'shipment_id' => $shipment->id,
                'exception_type' => $exceptionType,
                'status' => ExceptionStatus::OPEN,
                'severity' => $severity,
                'reported_by' => $reporter->id,
                'detected_at' => now(),
                'description' => $description,
                'root_cause' => $rootCause,
                'impact_delay_hours' => $impactDelayHours,
            ]);

            // If impact delay is positive, adjust estimated arrival and recalculate ETA
            if ($impactDelayHours > 0) {
                $newEstimated = $shipment->estimated_arrival->addHours($impactDelayHours);
                $this->etaService->refreshShipmentDelay($shipment, $newEstimated);
            }

            // Automatically log an EXCEPTION tracking event
            $shipment->trackingEvents()->create([
                'location_id' => $shipment->current_location_id ?? $shipment->origin_id,
                'created_by' => $reporter->id,
                'event_status' => $shipment->current_status,
                'event_type' => 'EXCEPTION',
                'event_time' => now(),
                'location_name' => $shipment->currentLocation?->name ?? 'En Route Checkpoint',
                'latitude' => $shipment->currentLocation?->latitude ?? 0,
                'longitude' => $shipment->currentLocation?->longitude ?? 0,
                'description' => "[EXCEPTION REPORTED] {$exceptionType->label()}: {$description}",
                'exception_id' => $exception->id,
            ]);

            // Notify customer in-app
            InAppNotification::create([
                'user_id' => $shipment->customer_id,
                'title' => "Exception Alert: Shipment {$shipment->shipment_number}",
                'message' => "An issue has been flagged ({$exceptionType->label()}): {$description}",
                'type' => 'EXCEPTION',
                'link' => "/shipments/{$shipment->id}",
            ]);

            // Audit Log
            $this->auditService->log(
                action: 'EXCEPTION_OPENED',
                entity: $exception,
                newValues: $exception->toArray(),
                actor: $reporter
            );

            return $exception;
        });
    }

    /**
     * Transition exception status (OPEN -> INVESTIGATING -> RESOLVED).
     */
    public function updateStatus(
        ShipmentException $exception,
        ExceptionStatus|string $newStatus,
        User $actor,
        ?string $notes = null,
        ?string $rootCause = null
    ): ShipmentException {
        $targetStatus = is_string($newStatus) ? ExceptionStatus::from($newStatus) : $newStatus;

        if (!$exception->status->canTransitionTo($targetStatus)) {
            throw new InvalidArgumentException(
                "Illegal exception transition from [{$exception->status->value}] to [{$targetStatus->value}]."
            );
        }

        $oldValues = $exception->toArray();

        $updateData = [
            'status' => $targetStatus,
        ];

        if ($notes) {
            $updateData['resolution_notes'] = $notes;
        }

        if ($rootCause) {
            $updateData['root_cause'] = $rootCause;
        }

        if ($targetStatus === ExceptionStatus::RESOLVED) {
            $updateData['resolved_at'] = now();
            $updateData['resolved_by'] = $actor->id;
        }

        $exception->update($updateData);

        // Add tracking event to indicate investigation or resolution
        $shipment = $exception->shipment;
        $shipment->trackingEvents()->create([
            'location_id' => $shipment->current_location_id ?? $shipment->origin_id,
            'created_by' => $actor->id,
            'event_status' => $shipment->current_status,
            'event_type' => $targetStatus === ExceptionStatus::RESOLVED ? 'NORMAL' : 'EXCEPTION',
            'event_time' => now(),
            'location_name' => $shipment->currentLocation?->name ?? 'Operational Hub',
            'latitude' => $shipment->currentLocation?->latitude ?? 0,
            'longitude' => $shipment->currentLocation?->longitude ?? 0,
            'description' => "[EXCEPTION {$targetStatus->value}] {$exception->exception_type->label()}: " . ($notes ?? 'Status updated'),
            'exception_id' => $exception->id,
        ]);

        if ($targetStatus === ExceptionStatus::RESOLVED) {
            InAppNotification::create([
                'user_id' => $shipment->customer_id,
                'title' => "Exception Resolved: {$shipment->shipment_number}",
                'message' => "The exception ({$exception->exception_type->label()}) has been resolved. Notes: {$notes}",
                'type' => 'INFO',
                'link' => "/shipments/{$shipment->id}",
            ]);
        }

        $this->auditService->log(
            action: 'EXCEPTION_STATUS_UPDATED',
            entity: $exception,
            oldValues: $oldValues,
            newValues: $exception->fresh()->toArray(),
            actor: $actor
        );

        return $exception;
    }
}
