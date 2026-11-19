<?php

namespace App\Services;

use App\Enums\ShipmentStatus;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrackingService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Record a new tracking event for a shipment.
     * Guaranteed historical append: never overwrites past records.
     */
    public function addEvent(
        Shipment $shipment,
        User $actor,
        array $data
    ): TrackingEvent {
        return DB::transaction(function () use ($shipment, $actor, $data) {
            $eventTime = isset($data['event_time']) ? Carbon::parse($data['event_time']) : now();

            // Validate that event timestamp does not regress behind existing latest event
            $latestEvent = $shipment->trackingEvents()->latest('event_time')->first();
            if ($latestEvent && $eventTime->lessThan($latestEvent->event_time)) {
                throw new InvalidArgumentException(
                    "Tracking event timestamp ({$eventTime}) cannot precede the latest recorded event timestamp ({$latestEvent->event_time})."
                );
            }

            $location = null;
            if (!empty($data['location_id'])) {
                $location = Location::find($data['location_id']);
            }

            $eventStatus = isset($data['event_status']) 
                ? (is_string($data['event_status']) ? ShipmentStatus::from($data['event_status']) : $data['event_status'])
                : $shipment->current_status;

            $event = TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'location_id' => $location?->id ?? $data['location_id'] ?? null,
                'created_by' => $actor->id,
                'event_status' => $eventStatus,
                'event_type' => $data['event_type'] ?? 'NORMAL',
                'event_time' => $eventTime,
                'location_name' => $data['location_name'] ?? ($location?->name ?? 'En Route'),
                'latitude' => $data['latitude'] ?? ($location?->latitude ?? null),
                'longitude' => $data['longitude'] ?? ($location?->longitude ?? null),
                'description' => $data['description'],
                'exception_id' => $data['exception_id'] ?? null,
            ]);

            // Update shipment's current location from latest valid location event
            if ($location) {
                $shipment->update([
                    'current_location_id' => $location->id,
                ]);
            }

            // Audit
            $this->auditService->log(
                action: 'TRACKING_EVENT_LOGGED',
                entity: $event,
                newValues: $event->toArray(),
                actor: $actor
            );

            return $event;
        });
    }

    /**
     * Fetch chronological timeline for a shipment.
     */
    public function getTimeline(Shipment $shipment)
    {
        return $shipment->trackingEvents()
            ->with(['location', 'creator', 'exception'])
            ->orderBy('event_time', 'asc')
            ->get();
    }
}
