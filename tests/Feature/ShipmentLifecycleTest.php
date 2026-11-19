<?php

namespace Tests\Feature;

use App\Enums\ShipmentStatus;
use App\Enums\UserRole;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ShipmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected Shipment $shipment;
    protected ShipmentService $shipmentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shipmentService = app(ShipmentService::class);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR]);

        $carrier = Carrier::create(['code' => 'MSK', 'name' => 'Maersk', 'service_type' => 'OCEAN']);
        $origin = Location::create(['code' => 'IDJKT', 'name' => 'Jakarta', 'city' => 'Jakarta', 'country' => 'ID', 'latitude' => -6.1, 'longitude' => 106.8]);
        $dest = Location::create(['code' => 'NLRTM', 'name' => 'Rotterdam', 'city' => 'Rotterdam', 'country' => 'NL', 'latitude' => 51.9, 'longitude' => 4.4]);

        $this->shipment = Shipment::create([
            'shipment_number' => 'CT-LIFECYCLE-01',
            'customer_id' => $this->operator->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $dest->id,
            'current_status' => ShipmentStatus::CREATED,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5),
        ]);
    }

    public function test_valid_sequential_lifecycle_transitions(): void
    {
        // 1. CREATED -> BOOKED
        $this->shipmentService->transitionStatus($this->shipment, ShipmentStatus::BOOKED, $this->operator);
        $this->assertEquals(ShipmentStatus::BOOKED, $this->shipment->fresh()->current_status);

        // 2. BOOKED -> PICKED_UP
        $this->shipmentService->transitionStatus($this->shipment, ShipmentStatus::PICKED_UP, $this->operator);
        $this->assertEquals(ShipmentStatus::PICKED_UP, $this->shipment->fresh()->current_status);

        // 3. PICKED_UP -> IN_TRANSIT
        $this->shipmentService->transitionStatus($this->shipment, ShipmentStatus::IN_TRANSIT, $this->operator);
        $this->assertEquals(ShipmentStatus::IN_TRANSIT, $this->shipment->fresh()->current_status);
        $this->assertNotNull($this->shipment->fresh()->actual_departure);

        // 4. IN_TRANSIT -> DELIVERED
        $this->shipmentService->transitionStatus($this->shipment, ShipmentStatus::DELIVERED, $this->operator);
        $this->assertEquals(ShipmentStatus::DELIVERED, $this->shipment->fresh()->current_status);
        $this->assertNotNull($this->shipment->fresh()->actual_arrival);
    }

    public function test_illegal_status_jump_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Illegal status transition/i');

        // Cannot jump directly from CREATED to IN_TRANSIT
        $this->shipmentService->transitionStatus($this->shipment, ShipmentStatus::IN_TRANSIT, $this->operator);
    }

    public function test_terminal_status_cannot_be_reverted(): void
    {
        // Advance to terminal DELIVERED
        $this->shipment->update(['current_status' => ShipmentStatus::DELIVERED]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/terminal status/i');

        // Attempting to move backward from DELIVERED to IN_TRANSIT
        $this->shipmentService->transitionStatus($this->shipment, ShipmentStatus::IN_TRANSIT, $this->operator);
    }
}
