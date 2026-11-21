<?php

namespace App\Services;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShipmentService
{
    // Peta Alur Status yang Sah (Slide 06)
    protected const ALLOWED_TRANSITIONS = [
        'CREATED' => ['BOOKED'],
        'BOOKED' => ['PICKED_UP'],
        'PICKED_UP' => ['IN_TRANSIT'],
        'IN_TRANSIT' => ['DELIVERED'],
        'DELIVERED' => [], // Status terminal tidak boleh pindah lagi
    ];

    public function transitionStatus(Shipment $shipment, ShipmentStatus $targetStatus, User $operator, ?string $notes = null): Shipment
    {
        $currentStatusStr = is_object($shipment->current_status) 
            ? $shipment->current_status->value 
            : $shipment->current_status;
            
        $targetStatusStr = is_object($targetStatus) 
            ? $targetStatus->value 
            : $targetStatus;

        // 1. Validasi Terminal Status (DELIVERED tidak boleh kembali ke awal)
        if ($currentStatusStr === ShipmentStatus::DELIVERED->value) {
            throw new InvalidArgumentException("Illegal transition: Shipment is in terminal status [DELIVERED] and cannot be modified.");
        }

        // 2. Validasi Transisi Bertahap (Tidak boleh melompat status)
        $allowed = self::ALLOWED_TRANSITIONS[$currentStatusStr] ?? [];
        if (! in_array($targetStatusStr, $allowed, true)) {
            throw new InvalidArgumentException("Illegal status transition from [{$currentStatusStr}] to [{$targetStatusStr}]. Valid next status: [" . implode(', ', $allowed) . "].");
        }

        return DB::transaction(function () use ($shipment, $targetStatus, $targetStatusStr, $operator, $notes) {
            $updateData = ['current_status' => $targetStatus];

            // Otomatis mencatat waktu departure saat mulai jalan
            if ($targetStatus === ShipmentStatus::IN_TRANSIT && ! $shipment->actual_departure) {
                $updateData['actual_departure'] = Carbon::now();
            }

            // Otomatis mencatat waktu kedatangan saat sampai
            if ($targetStatus === ShipmentStatus::DELIVERED && ! $shipment->actual_arrival) {
                $updateData['actual_arrival'] = Carbon::now();
            }

            $shipment->update($updateData);

            // Menambahkan catatan event pelacakan otomatis
            $shipment->trackingEvents()->create([
                'location_id' => $shipment->current_location_id ?? $shipment->origin_id,
                'created_by' => $operator->id,
                'event_status' => $targetStatus,
                'event_type' => $targetStatus === ShipmentStatus::DELIVERED ? 'ARRIVAL' : 'CHECKPOINT',
                'event_time' => Carbon::now(),
                'location_name' => optional($shipment->currentLocation)->name ?? optional($shipment->origin)->name ?? 'Waypoint',
                'latitude' => optional($shipment->currentLocation)->latitude ?? optional($shipment->origin)->latitude,
                'longitude' => optional($shipment->currentLocation)->longitude ?? optional($shipment->origin)->longitude,
                'description' => $notes ?: "Shipment status updated to {$targetStatusStr} by {$operator->name}.",
            ]);

            return $shipment;
        });
    }

        public function createShipment(array $data, User $creator): Shipment
    {
        if (empty($data['shipment_number'])) {
            $data['shipment_number'] = 'CT-' . date('Y') . '-' . strtoupper(\Illuminate\Support\Str::random(6));
        }
        $data['current_status'] = ShipmentStatus::CREATED;
        $data['current_location_id'] = $data['origin_id'];
        $data['estimated_arrival'] = $data['expected_arrival'];

        return Shipment::create($data);
    }
}