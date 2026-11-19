<?php

namespace Tests\Feature;

use App\Enums\ShipmentStatus;
use App\Enums\UserRole;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\TrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TrackingEventTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected Shipment $shipment;
    protected Location $origin;
    protected Location $midpoint;
    protected Location $dest;
    protected TrackingService $trackingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trackingService = app(TrackingService::class);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR]);

        $carrier = Carrier::create(['code' => 'MSK', 'name' => 'Maersk', 'service_type' => 'OCEAN']);
        $this->origin = Location::create(['code' => 'IDJKT', 'name' => 'Jakarta', 'city' => 'Jakarta', 'country' => 'ID', 'latitude' => -6.1, 'longitude' => 106.8]);
        $this->midpoint = Location::create(['code' => 'SGSIN', 'name' => 'Singapore', 'city' => 'Singapore', 'country' => 'SG', 'latitude' => 1.2, 'longitude' => 103.8]);
        $this->dest = Location::create(['code' => 'NLRTM', 'name' => 'Rotterdam', 'city' => 'Rotterdam', 'country' => 'NL', 'latitude' => 51.9, 'longitude' => 4.4]);

        $this->shipment = Shipment::create([
            'shipment_number' => 'CT-TRACK-01',
            'customer_id' => $this->operator->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->dest->id,
            'current_location_id' => $this->origin->id,
            'current_status' => ShipmentStatus::IN_TRANSIT,
            'expected_departure' => now()->subDays(2),
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5),
        ]);
    }

    public function test_tracking_events_are_appended_historically_without_overwriting(): void
    {
        $event1 = $this->trackingService->addEvent($this->shipment, $this->operator, [
            'location_id' => $this->origin->id,
            'event_status' => ShipmentStatus::IN_TRANSIT,
            'event_type' => 'DEPARTURE',
            'event_time' => now()->subDay(),
            'description' => 'Vessel departed origin port.',
        ]);

        $event2 = $this->trackingService->addEvent($this->shipment, $this->operator, [
            'location_id' => $this->midpoint->id,
            'event_status' => ShipmentStatus::IN_TRANSIT,
            'event_type' => 'CHECKPOINT',
            'event_time' => now(),
            'description' => 'Vessel anchored in Singapore for refueling.',
        ]);

        // Assert both events coexist chronologically
        $this->assertDatabaseCount('tracking_events', 2);
        $timeline = $this->trackingService->getTimeline($this->shipment);

        $this->assertEquals(2, $timeline->count());
        $this->assertEquals($event1->id, $timeline[0]->id);
        $this->assertEquals($event2->id, $timeline[1]->id);

        // Assert shipment's current location was updated to Singapore
        $this->assertEquals($this->midpoint->id, $this->shipment->fresh()->current_location_id);
    }

    public function test_tracking_event_cannot_have_preceding_timestamp_than_latest(): void
    {
        $this->trackingService->addEvent($this->shipment, $this->operator, [
            'location_id' => $this->origin->id,
            'event_status' => ShipmentStatus::IN_TRANSIT,
            'event_type' => 'DEPARTURE',
            'event_time' => now(),
            'description' => 'Event at current time.',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/cannot precede/i');

        // Illegal: Trying to add an event with timestamp 2 hours in the past
        $this->trackingService->addEvent($this->shipment, $this->operator, [
            'location_id' => $this->origin->id,
            'event_status' => ShipmentStatus::IN_TRANSIT,
            'event_type' => 'CHECKPOINT',
            'event_time' => now()->subHours(2),
            'description' => 'Illegal retroactive event.',
        ]);
    }
}
