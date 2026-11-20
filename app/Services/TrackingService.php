<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrackingService
{
    public function addEvent(Shipment $shipment, User $operator, array $data): TrackingEvent
    {
        $eventTime = Carbon::parse($data['event_time']);

        // Validasi Kronologis: Waktu event baru tidak boleh mendahului event terakhir yang tercatat (Slide 07)
        $latestEvent = $shipment->trackingEvents()->latest('event_time')->first();
        if ($latestEvent && $eventTime->lt($latestEvent->event_time)) {
            throw new InvalidArgumentException("Historical integrity violation: Event timestamp [{$eventTime->toDateTimeString()}] cannot precede the latest event [{$latestEvent->event_time->toDateTimeString()}].");
        }

        return DB::transaction(function () use ($shipment, $operator, $data, $eventTime) {
            $locationId = $data['location_id'] ?? null;
            $locationName = $data['location_name'] ?? null;
            $lat = $data['latitude'] ?? null;
            $lng = $data['longitude'] ?? null;

            if ($locationId && (! $locationName || ! $lat)) {
                $location = Location::find($locationId);
                if ($location) {
                    $locationName = $location->name;
                    $lat = $location->latitude;
                    $lng = $location->longitude;
                }
            }

            // Simpan event baru (Append-only, tidak pernah menimpa)
            $event = $shipment->trackingEvents()->create([
                'location_id' => $locationId,
                'created_by' => $operator->id,
                'event_status' => $data['event_status'] ?? $shipment->current_status,
                'event_type' => $data['event_type'] ?? 'CHECKPOINT',
                'event_time' => $eventTime,
                'location_name' => $locationName ?? 'In Transit Waypoint',
                'latitude' => $lat,
                'longitude' => $lng,
                'description' => $data['description'],
                'exception_id' => $data['exception_id'] ?? null,
            ]);

            // Perbarui lokasi terkini kapal/kargo
            if ($locationId) {
                $shipment->update(['current_location_id' => $locationId]);
            }

            return $event;
        });
    }

    public function getTimeline(Shipment $shipment): Collection
    {
        return $shipment->trackingEvents()
            ->with(['location', 'creator'])
            ->orderBy('event_time', 'asc')
            ->get();
    }
}