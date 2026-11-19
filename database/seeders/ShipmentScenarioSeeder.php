<?php

namespace Database\Seeders;

use App\Enums\DelaySeverity;
use App\Enums\ExceptionStatus;
use App\Enums\ExceptionType;
use App\Enums\ShipmentStatus;
use App\Models\Carrier;
use App\Models\InAppNotification;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ShipmentScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@cargotrace.test')->first();
        $operator = User::where('email', 'operator@cargotrace.test')->first();
        $customer1 = User::where('email', 'customer1@cargotrace.test')->first();
        $customer2 = User::where('email', 'customer2@cargotrace.test')->first();

        $maersk = Carrier::where('code', 'MSK')->first();
        $cma = Carrier::where('code', 'CMA')->first();
        $dhl = Carrier::where('code', 'DHL-GF')->first();

        $jakarta = Location::where('code', 'IDJKT')->first();
        $singapore = Location::where('code', 'SGSIN')->first();
        $suez = Location::where('code', 'EGSUZ')->first();
        $rotterdam = Location::where('code', 'NLRTM')->first();
        $hamburg = Location::where('code', 'DEHAM')->first();
        $shanghai = Location::where('code', 'CNSHA')->first();
        $losAngeles = Location::where('code', 'USLAX')->first();
        $sydney = Location::where('code', 'AUSYD')->first();

        /*
        |--------------------------------------------------------------------------
        | SCENARIO 1: GROUP A MAIN BENCHMARK (SE Asia -> Europe, Customs Hold)
        | Threshold: 12 Hours | Status: IN_TRANSIT | Delayed by 16.5 Hours
        |--------------------------------------------------------------------------
        */
        $shipment1 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-2026-EU01'],
            [
                'customer_id' => $customer1->id,
                'carrier_id' => $maersk->id,
                'origin_id' => $jakarta->id,
                'destination_id' => $rotterdam->id,
                'current_location_id' => $suez->id,
                'cargo_type' => 'HIGH-TECH ELECTRONICS',
                'total_weight_kg' => 14500.00,
                'total_volume_cbm' => 48.50,
                'current_status' => ShipmentStatus::IN_TRANSIT,
                'expected_departure' => Carbon::now()->subDays(6)->setTime(8, 0),
                'actual_departure' => Carbon::now()->subDays(6)->setTime(9, 30),
                'expected_arrival' => Carbon::now()->addDays(5)->setTime(12, 0),
                'estimated_arrival' => Carbon::now()->addDays(6)->setTime(4, 30), // 16.5 hours delay!
                'is_delayed' => true,
                'delay_duration_minutes' => 990, // 16.5 hours
                'delay_severity' => DelaySeverity::MEDIUM,
                'delay_reason' => 'Estimated arrival exceeds SLA expected arrival by 16.5 hours. Exceeds Group A threshold of 12 hours due to Customs Inspection & Clearance Audit at Suez Transit.',
                'notes' => 'Priority semiconductor consignment with temperature & tamper monitoring.',
            ]
        );

        $shipment1->items()->createMany([
            ['item_name' => 'Industrial Microcontrollers', 'sku' => 'MCU-9002-XT', 'package_type' => 'PALLET', 'quantity' => 120, 'weight_kg' => 8500, 'volume_cbm' => 28, 'declared_value' => 250000],
            ['item_name' => 'Automotive Sensor Modules', 'sku' => 'SENS-AUTO-4X', 'package_type' => 'CARTON', 'quantity' => 300, 'weight_kg' => 6000, 'volume_cbm' => 20.5, 'declared_value' => 180000],
        ]);

        // Historical Chronological Tracking Events
        $shipment1->trackingEvents()->createMany([
            [
                'location_id' => $jakarta->id,
                'created_by' => $operator->id,
                'event_status' => ShipmentStatus::CREATED,
                'event_type' => 'NORMAL',
                'event_time' => Carbon::now()->subDays(7)->setTime(10, 0),
                'location_name' => $jakarta->name,
                'latitude' => $jakarta->latitude,
                'longitude' => $jakarta->longitude,
                'description' => 'Shipment booking generated and export declaration registered.',
            ],
            [
                'location_id' => $jakarta->id,
                'created_by' => $operator->id,
                'event_status' => ShipmentStatus::BOOKED,
                'event_type' => 'NORMAL',
                'event_time' => Carbon::now()->subDays(6)->setTime(14, 0),
                'location_name' => $jakarta->name,
                'latitude' => $jakarta->latitude,
                'longitude' => $jakarta->longitude,
                'description' => 'Container load confirmed aboard Maersk Mc-Kinney Moller.',
            ],
            [
                'location_id' => $jakarta->id,
                'created_by' => $operator->id,
                'event_status' => ShipmentStatus::PICKED_UP,
                'event_type' => 'NORMAL',
                'event_time' => Carbon::now()->subDays(6)->setTime(18, 0),
                'location_name' => $jakarta->name,
                'latitude' => $jakarta->latitude,
                'longitude' => $jakarta->longitude,
                'description' => 'Containers gated in at Tanjung Priok International Terminal.',
            ],
            [
                'location_id' => $jakarta->id,
                'created_by' => $operator->id,
                'event_status' => ShipmentStatus::IN_TRANSIT,
                'event_type' => 'DEPARTURE',
                'event_time' => Carbon::now()->subDays(6)->setTime(21, 30),
                'location_name' => $jakarta->name,
                'latitude' => $jakarta->latitude,
                'longitude' => $jakarta->longitude,
                'description' => 'Vessel departed Tanjung Priok on schedule toward Suez maritime corridor.',
            ],
            [
                'location_id' => $singapore->id,
                'created_by' => $operator->id,
                'event_status' => ShipmentStatus::IN_TRANSIT,
                'event_type' => 'CHECKPOINT',
                'event_time' => Carbon::now()->subDays(4)->setTime(6, 15),
                'location_name' => $singapore->name,
                'latitude' => $singapore->latitude,
                'longitude' => $singapore->longitude,
                'description' => 'Bunkering and transshipment checkpoint cleared at Port of Singapore.',
            ],
            [
                'location_id' => $suez->id,
                'created_by' => $operator->id,
                'event_status' => ShipmentStatus::IN_TRANSIT,
                'event_type' => 'CHECKPOINT',
                'event_time' => Carbon::now()->subHours(18),
                'location_name' => $suez->name,
                'latitude' => $suez->latitude,
                'longitude' => $suez->longitude,
                'description' => 'Vessel anchored in convoy assembly zone awaiting regulatory clearance.',
            ],
        ]);

        // Create Main Benchmark Exception: CUSTOMS_HOLD
        $exception1 = ShipmentException::create([
            'shipment_id' => $shipment1->id,
            'exception_type' => ExceptionType::CUSTOMS_HOLD,
            'status' => ExceptionStatus::OPEN,
            'severity' => DelaySeverity::MEDIUM,
            'reported_by' => $operator->id,
            'detected_at' => Carbon::now()->subHours(16),
            'description' => 'Transit customs inspection audit invoked regarding HS code certification for dual-use electronic microcontrollers.',
            'root_cause' => 'Discrepancy in declared tariff classification sub-code between commercial invoice and EU border safety declaration.',
            'impact_delay_hours' => 16.5,
        ]);

        // Add corresponding EXCEPTION tracking event
        $shipment1->trackingEvents()->create([
            'location_id' => $suez->id,
            'created_by' => $operator->id,
            'event_status' => ShipmentStatus::IN_TRANSIT,
            'event_type' => 'EXCEPTION',
            'event_time' => Carbon::now()->subHours(16),
            'location_name' => $suez->name,
            'latitude' => $suez->latitude,
            'longitude' => $suez->longitude,
            'description' => '[EXCEPTION REPORTED] Customs Hold: Container flagged for secondary inspection protocol.',
            'exception_id' => $exception1->id,
        ]);

        // Customer Notification
        InAppNotification::create([
            'user_id' => $customer1->id,
            'title' => 'Customs Hold Alert: CT-2026-EU01',
            'message' => 'Shipment #CT-2026-EU01 has encountered a Customs Hold at Suez Transit. ETA adjusted by +16.5 hours.',
            'type' => 'EXCEPTION',
            'link' => "/shipments/{$shipment1->id}",
        ]);

        /*
        |--------------------------------------------------------------------------
        | SCENARIO 2: DELIVERED SHIPMENT (Terminal Lifecycle Example)
        | Fully completed timeline, terminal status DELIVERED
        |--------------------------------------------------------------------------
        */
        $shipment2 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-2026-EU02'],
            [
                'customer_id' => $customer1->id,
                'carrier_id' => $maersk->id,
                'origin_id' => $jakarta->id,
                'destination_id' => $hamburg->id,
                'current_location_id' => $hamburg->id,
                'cargo_type' => 'PROCESSED COFFEE & TEA',
                'total_weight_kg' => 22000.00,
                'total_volume_cbm' => 60.00,
                'current_status' => ShipmentStatus::DELIVERED,
                'expected_departure' => Carbon::now()->subDays(20),
                'actual_departure' => Carbon::now()->subDays(20)->addHours(2),
                'expected_arrival' => Carbon::now()->subDays(2),
                'estimated_arrival' => Carbon::now()->subDays(2)->subHours(3),
                'actual_arrival' => Carbon::now()->subDays(2)->subHours(2),
                'is_delayed' => false,
                'delay_duration_minutes' => 0,
                'delay_severity' => DelaySeverity::NONE,
                'delay_reason' => 'Shipment completed within scheduled SLA timeframe.',
                'notes' => 'Specialty Arabica bean export in hermetic bags.',
            ]
        );

        $shipment2->items()->create([
            'item_name' => 'Single Origin Toraja Coffee Beans',
            'sku' => 'COF-TRJ-01',
            'package_type' => 'CONTAINER',
            'quantity' => 1,
            'weight_kg' => 22000,
            'volume_cbm' => 60,
            'declared_value' => 140000,
        ]);

        $shipment2->trackingEvents()->createMany([
            ['location_id' => $jakarta->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::CREATED, 'event_type' => 'NORMAL', 'event_time' => Carbon::now()->subDays(22), 'location_name' => $jakarta->name, 'description' => 'Export booking confirmed.'],
            ['location_id' => $jakarta->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::BOOKED, 'event_type' => 'NORMAL', 'event_time' => Carbon::now()->subDays(21), 'location_name' => $jakarta->name, 'description' => 'Container sealed at dry port.'],
            ['location_id' => $jakarta->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::PICKED_UP, 'event_type' => 'NORMAL', 'event_time' => Carbon::now()->subDays(20), 'location_name' => $jakarta->name, 'description' => 'Loaded onto vessel.'],
            ['location_id' => $jakarta->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::IN_TRANSIT, 'event_type' => 'DEPARTURE', 'event_time' => Carbon::now()->subDays(20)->addHours(2), 'location_name' => $jakarta->name, 'description' => 'Vessel underway.'],
            ['location_id' => $hamburg->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::DELIVERED, 'event_type' => 'ARRIVAL', 'event_time' => Carbon::now()->subDays(2)->subHours(2), 'location_name' => $hamburg->name, 'description' => 'Customs released and consigned to Hamburg distribution facility.'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | SCENARIO 3: GROUP B SCENARIO (Asia -> North America, Weather Delay)
        | Shanghai -> Los Angeles | Threshold: 18h | Delay: 26h (HIGH severity)
        |--------------------------------------------------------------------------
        */
        $shipment3 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-2026-US01'],
            [
                'customer_id' => $customer2->id,
                'carrier_id' => $cma->id,
                'origin_id' => $shanghai->id,
                'destination_id' => $losAngeles->id,
                'current_location_id' => $shanghai->id,
                'cargo_type' => 'CONSUMER APPAREL',
                'total_weight_kg' => 18400.00,
                'total_volume_cbm' => 54.00,
                'current_status' => ShipmentStatus::IN_TRANSIT,
                'expected_departure' => Carbon::now()->subDays(2),
                'actual_departure' => Carbon::now()->subDays(2)->addHours(4),
                'expected_arrival' => Carbon::now()->addDays(8),
                'estimated_arrival' => Carbon::now()->addDays(9)->addHours(2), // 26h delay!
                'is_delayed' => true,
                'delay_duration_minutes' => 1560,
                'delay_severity' => DelaySeverity::HIGH,
                'delay_reason' => 'Delay of 26 hours exceeds Group B threshold (18h) due to severe Pacific gale weather re-routing.',
                'notes' => 'Seasonal retail inventory destined for US West Coast distribution centers.',
            ]
        );

        $weatherEx = ShipmentException::create([
            'shipment_id' => $shipment3->id,
            'exception_type' => ExceptionType::WEATHER_DELAY,
            'status' => ExceptionStatus::INVESTIGATING,
            'severity' => DelaySeverity::HIGH,
            'reported_by' => $operator->id,
            'detected_at' => Carbon::now()->subHours(24),
            'description' => 'Tropical cyclone warning in Pacific maritime sector required vessel speed reduction and 150 nautical mile detour.',
            'impact_delay_hours' => 26.0,
        ]);

        $shipment3->trackingEvents()->createMany([
            ['location_id' => $shanghai->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::CREATED, 'event_type' => 'NORMAL', 'event_time' => Carbon::now()->subDays(3), 'location_name' => $shanghai->name, 'description' => 'Booking accepted at Yangshan Deep-Water Port.'],
            ['location_id' => $shanghai->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::IN_TRANSIT, 'event_type' => 'DEPARTURE', 'event_time' => Carbon::now()->subDays(2)->addHours(4), 'location_name' => $shanghai->name, 'description' => 'Vessel departed outward bound.'],
            ['location_id' => $shanghai->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::IN_TRANSIT, 'event_type' => 'EXCEPTION', 'event_time' => Carbon::now()->subHours(24), 'location_name' => 'North Pacific Sea Sector', 'description' => '[EXCEPTION] Pacific storm avoidance speed reduction in effect.', 'exception_id' => $weatherEx->id],
        ]);

        /*
        |--------------------------------------------------------------------------
        | SCENARIO 4: GROUP C SCENARIO (SE Asia -> Australia, Documentation Issue)
        | Jakarta -> Sydney | Status: PICKED_UP | Resolved Exception
        |--------------------------------------------------------------------------
        */
        $shipment4 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-2026-AU01'],
            [
                'customer_id' => $customer2->id,
                'carrier_id' => $dhl->id,
                'origin_id' => $jakarta->id,
                'destination_id' => $sydney->id,
                'current_location_id' => $jakarta->id,
                'cargo_type' => 'FURNITURE & TIMBER',
                'total_weight_kg' => 8900.00,
                'total_volume_cbm' => 32.00,
                'current_status' => ShipmentStatus::PICKED_UP,
                'expected_departure' => Carbon::now()->addDays(1),
                'expected_arrival' => Carbon::now()->addDays(10),
                'estimated_arrival' => Carbon::now()->addDays(10)->addHours(4),
                'is_delayed' => false,
                'delay_duration_minutes' => 240,
                'delay_severity' => DelaySeverity::LOW,
                'delay_reason' => 'Delay of 4 hours is well within Group C threshold of 24 hours.',
                'notes' => 'Teak furniture consignment with verified fumigation certificate.',
            ]
        );

        $docEx = ShipmentException::create([
            'shipment_id' => $shipment4->id,
            'exception_type' => ExceptionType::DOCUMENTATION_ISSUE,
            'status' => ExceptionStatus::RESOLVED,
            'severity' => DelaySeverity::LOW,
            'reported_by' => $operator->id,
            'resolved_by' => $operator->id,
            'detected_at' => Carbon::now()->subHours(10),
            'resolved_at' => Carbon::now()->subHours(2),
            'description' => 'Fumigation stamp number mismatched with quarantine database record.',
            'root_cause' => 'Typo on paper certificate barcode scan.',
            'resolution_notes' => 'Re-certified digital timber quarantine certificate issued by Agriculture Quarantine Agency.',
            'impact_delay_hours' => 4.0,
        ]);

        $shipment4->trackingEvents()->createMany([
            ['location_id' => $jakarta->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::CREATED, 'event_type' => 'NORMAL', 'event_time' => Carbon::now()->subDays(1), 'location_name' => $jakarta->name, 'description' => 'Cargo received at logistics warehouse.'],
            ['location_id' => $jakarta->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::BOOKED, 'event_type' => 'NORMAL', 'event_time' => Carbon::now()->subHours(18), 'location_name' => $jakarta->name, 'description' => 'Vessel berth allocated.'],
            ['location_id' => $jakarta->id, 'created_by' => $operator->id, 'event_status' => ShipmentStatus::PICKED_UP, 'event_type' => 'NORMAL', 'event_time' => Carbon::now()->subHours(8), 'location_name' => $jakarta->name, 'description' => 'Documentation rectified and container staged for vessel loading.'],
        ]);
    }
}
