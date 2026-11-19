<?php

namespace App\Services;

use App\Enums\ShipmentStatus;
use App\Models\InAppNotification;
use App\Models\Shipment;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ShipmentService
{
    public function __construct(
        protected TrackingService $trackingService,
        protected EtaService $etaService,
        protected AuditService $auditService
    ) {}

    /**
     * Generate unique sequential/randomized shipment tracking number.
     * Example: CT-202611-8492
     */
    public function generateShipmentNumber(): string
    {
        do {
            $number = 'CT-' . now()->format('Ym') . '-' . strtoupper(Str::random(5));
        } while (Shipment::where('shipment_number', $number)->exists());

        return $number;
    }

    /**
     * Create a new shipment with its items, initial tracking event, and delay evaluation.
     */
    public function createShipment(array $data, User $creator): Shipment
    {
        return DB::transaction(function () use ($data, $creator) {
            $expectedDeparture = Carbon::parse($data['expected_departure']);
            $expectedArrival = Carbon::parse($data['expected_arrival']);
            $estimatedArrival = isset($data['estimated_arrival']) 
                ? Carbon::parse($data['estimated_arrival']) 
                : $expectedArrival->copy();

            // Evaluate initial delay status
            $delayEval = $this->etaService->evaluateDelay($expectedArrival, $estimatedArrival);

            $shipment = Shipment::create([
                'shipment_number' => $data['shipment_number'] ?? $this->generateShipmentNumber(),
                'customer_id' => $data['customer_id'],
                'carrier_id' => $data['carrier_id'],
                'origin_id' => $data['origin_id'],
                'destination_id' => $data['destination_id'],
                'current_location_id' => $data['origin_id'],
                'cargo_type' => $data['cargo_type'] ?? 'GENERAL',
                'total_weight_kg' => $data['total_weight_kg'] ?? 0,
                'total_volume_cbm' => $data['total_volume_cbm'] ?? 0,
                'current_status' => ShipmentStatus::CREATED,
                'expected_departure' => $expectedDeparture,
                'expected_arrival' => $expectedArrival,
                'estimated_arrival' => $estimatedArrival,
                'is_delayed' => $delayEval['is_delayed'],
                'delay_duration_minutes' => $delayEval['delay_minutes'],
                'delay_severity' => $delayEval['severity'],
                'delay_reason' => $delayEval['reason'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Add shipment items if provided
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $shipment->items()->create([
                        'item_name' => $item['item_name'],
                        'sku' => $item['sku'] ?? null,
                        'package_type' => $item['package_type'] ?? 'CARTON',
                        'quantity' => $item['quantity'] ?? 1,
                        'weight_kg' => $item['weight_kg'] ?? 0,
                        'volume_cbm' => $item['volume_cbm'] ?? 0,
                        'declared_value' => $item['declared_value'] ?? 0,
                        'is_hazardous' => !empty($item['is_hazardous']),
                    ]);
                }
            }

            // Create initial tracking milestone
            $shipment->trackingEvents()->create([
                'location_id' => $shipment->origin_id,
                'created_by' => $creator->id,
                'event_status' => ShipmentStatus::CREATED,
                'event_type' => 'NORMAL',
                'event_time' => now(),
                'location_name' => $shipment->origin?->name ?? 'Origin Facility',
                'latitude' => $shipment->origin?->latitude ?? 0,
                'longitude' => $shipment->origin?->longitude ?? 0,
                'description' => 'Shipment manifest generated and registered in CargoTrace platform.',
            ]);

            // Notify Customer
            InAppNotification::create([
                'user_id' => $shipment->customer_id,
                'title' => "Shipment Created: {$shipment->shipment_number}",
                'message' => "Your shipment #{$shipment->shipment_number} has been registered and is awaiting carrier booking.",
                'type' => 'STATUS_CHANGE',
                'link' => "/shipments/{$shipment->id}",
            ]);

            // Audit
            $this->auditService->log(
                action: 'SHIPMENT_CREATED',
                entity: $shipment,
                newValues: $shipment->toArray(),
                actor: $creator
            );

            return $shipment;
        });
    }

    /**
     * Validate and transition shipment status.
     * Enforces the lifecycle flow:
     * CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT -> DELIVERED
     */
    public function transitionStatus(
        Shipment $shipment,
        ShipmentStatus|string $targetStatusInput,
        User $actor,
        ?string $description = null,
        ?int $locationId = null
    ): Shipment {
        $targetStatus = is_string($targetStatusInput) 
            ? ShipmentStatus::from($targetStatusInput) 
            : $targetStatusInput;

        $currentStatus = $shipment->current_status;

        // Terminal state enforcement: DELIVERED cannot transition back
        if ($currentStatus->isTerminal()) {
            throw new InvalidArgumentException(
                "Shipment is in terminal status [{$currentStatus->value}] and cannot be modified without special administrative authorization."
            );
        }

        // Validate sequential lifecycle progression
        if (!$currentStatus->canTransitionTo($targetStatus)) {
            $allowed = implode(', ', array_map(fn($s) => $s->value, $currentStatus->allowedNextStatuses()));
            throw new InvalidArgumentException(
                "Illegal status transition: Cannot transition shipment {$shipment->shipment_number} from [{$currentStatus->value}] to [{$targetStatus->value}]. Allowed next step(s): [{$allowed}]."
            );
        }

        return DB::transaction(function () use ($shipment, $currentStatus, $targetStatus, $actor, $description, $locationId) {
            $oldValues = $shipment->toArray();

            $updates = [
                'current_status' => $targetStatus,
            ];

            // Update operational timestamps
            if ($targetStatus === ShipmentStatus::IN_TRANSIT && !$shipment->actual_departure) {
                $updates['actual_departure'] = now();
            }

            if ($targetStatus === ShipmentStatus::DELIVERED && !$shipment->actual_arrival) {
                $updates['actual_arrival'] = now();
                $updates['current_location_id'] = $shipment->destination_id;
            } elseif ($locationId) {
                $updates['current_location_id'] = $locationId;
            }

            $shipment->update($updates);

            // Record tracking event
            $loc = $shipment->currentLocation ?? ($targetStatus === ShipmentStatus::DELIVERED ? $shipment->destination : $shipment->origin);
            $eventDesc = $description ?? match($targetStatus) {
                ShipmentStatus::BOOKED => "Cargo space booked with carrier {$shipment->carrier->name}.",
                ShipmentStatus::PICKED_UP => "Cargo picked up from origin by {$shipment->carrier->name}.",
                ShipmentStatus::IN_TRANSIT => "Consignment departed from origin and is currently in transit.",
                ShipmentStatus::DELIVERED => "Consignment successfully delivered to destination and signed off.",
                default => "Status transitioned to {$targetStatus->label()}.",
            };

            $this->trackingService->addEvent($shipment, $actor, [
                'location_id' => $loc?->id,
                'event_status' => $targetStatus,
                'event_type' => $targetStatus === ShipmentStatus::DELIVERED ? 'ARRIVAL' : ($targetStatus === ShipmentStatus::IN_TRANSIT ? 'DEPARTURE' : 'CHECKPOINT'),
                'event_time' => now(),
                'location_name' => $loc?->name ?? 'Checkpoint',
                'latitude' => $loc?->latitude,
                'longitude' => $loc?->longitude,
                'description' => $eventDesc,
            ]);

            // Notify customer in-app
            InAppNotification::create([
                'user_id' => $shipment->customer_id,
                'title' => "Shipment {$targetStatus->label()}: {$shipment->shipment_number}",
                'message' => "Shipment #{$shipment->shipment_number} status updated to {$targetStatus->label()}.",
                'type' => 'STATUS_CHANGE',
                'link' => "/shipments/{$shipment->id}",
            ]);

            // Audit
            $this->auditService->log(
                action: 'STATUS_TRANSITION',
                entity: $shipment,
                oldValues: $oldValues,
                newValues: $shipment->fresh()->toArray(),
                actor: $actor
            );

            return $shipment->fresh();
        });
    }

    /**
     * Update editable shipment metadata
     */
    public function updateShipment(Shipment $shipment, array $data, User $actor): Shipment
    {
        $oldValues = $shipment->toArray();

        $shipment->update(array_filter([
            'cargo_type' => $data['cargo_type'] ?? null,
            'carrier_id' => $data['carrier_id'] ?? null,
            'total_weight_kg' => $data['total_weight_kg'] ?? null,
            'total_volume_cbm' => $data['total_volume_cbm'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]));

        if (!empty($data['estimated_arrival'])) {
            $this->etaService->refreshShipmentDelay($shipment, Carbon::parse($data['estimated_arrival']));
        }

        $this->auditService->log(
            action: 'SHIPMENT_UPDATED',
            entity: $shipment,
            oldValues: $oldValues,
            newValues: $shipment->fresh()->toArray(),
            actor: $actor
        );

        return $shipment;
    }
}
