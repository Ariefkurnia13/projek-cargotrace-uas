<?php

namespace Tests\Feature;

use App\Enums\ShipmentStatus;
use App\Enums\UserRole;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customerA;
    protected User $customerB;
    protected User $operator;
    protected User $admin;
    protected Shipment $shipmentA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerA = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $this->customerB = User::factory()->create(['role' => UserRole::CUSTOMER]);
        $this->operator = User::factory()->create(['role' => UserRole::OPERATOR]);
        $this->admin = User::factory()->create(['role' => UserRole::ADMIN]);

        $carrier = Carrier::create([
            'code' => 'MSK',
            'name' => 'Maersk Line',
            'service_type' => 'OCEAN',
        ]);

        $loc1 = Location::create([
            'code' => 'IDJKT',
            'name' => 'Jakarta',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'latitude' => -6.1,
            'longitude' => 106.8,
        ]);

        $loc2 = Location::create([
            'code' => 'NLRTM',
            'name' => 'Rotterdam',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'latitude' => 51.9,
            'longitude' => 4.4,
        ]);

        $this->shipmentA = Shipment::create([
            'shipment_number' => 'CT-TEST-001',
            'customer_id' => $this->customerA->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $loc1->id,
            'destination_id' => $loc2->id,
            'current_status' => ShipmentStatus::CREATED,
            'expected_departure' => now(),
            'expected_arrival' => now()->addDays(5),
            'estimated_arrival' => now()->addDays(5),
        ]);
    }

    public function test_customer_can_view_own_shipment(): void
    {
        $response = $this->actingAs($this->customerA)->get("/shipments/{$this->shipmentA->id}");
        $response->assertStatus(200);
        $response->assertSee('CT-TEST-001');
    }

    public function test_customer_cannot_view_another_customers_shipment(): void
    {
        $response = $this->actingAs($this->customerB)->get("/shipments/{$this->shipmentA->id}");
        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_create_shipment_form(): void
    {
        $response = $this->actingAs($this->customerA)->get('/shipment/create');
        $response->assertStatus(403);
    }

    public function test_operator_and_admin_can_view_any_shipment(): void
    {
        $responseOperator = $this->actingAs($this->operator)->get("/shipments/{$this->shipmentA->id}");
        $responseOperator->assertStatus(200);

        $responseAdmin = $this->actingAs($this->admin)->get("/shipments/{$this->shipmentA->id}");
        $responseAdmin->assertStatus(200);
    }
}
