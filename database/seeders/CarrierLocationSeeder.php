<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use Illuminate\Database\Seeder;

class CarrierLocationSeeder extends Seeder
{
    public function run(): void
    {
        // Carriers
        $carriers = [
            [
                'code' => 'MSK',
                'name' => 'Maersk Line',
                'contact_email' => 'ops@maersk.com',
                'phone' => '+45 33 63 33 63',
                'service_type' => 'OCEAN',
                'is_active' => true,
            ],
            [
                'code' => 'CMA',
                'name' => 'CMA CGM Group',
                'contact_email' => 'booking@cma-cgm.com',
                'phone' => '+33 4 88 91 90 00',
                'service_type' => 'OCEAN',
                'is_active' => true,
            ],
            [
                'code' => 'MSC',
                'name' => 'Mediterranean Shipping Company',
                'contact_email' => 'dispatch@msc.com',
                'phone' => '+41 22 703 8888',
                'service_type' => 'OCEAN',
                'is_active' => true,
            ],
            [
                'code' => 'DHL-GF',
                'name' => 'DHL Global Forwarding',
                'contact_email' => 'track@dhl.com',
                'phone' => '+49 228 1820',
                'service_type' => 'AIR',
                'is_active' => true,
            ],
            [
                'code' => 'ONE',
                'name' => 'Ocean Network Express',
                'contact_email' => 'support@one-line.com',
                'phone' => '+65 6835 1500',
                'service_type' => 'OCEAN',
                'is_active' => true,
            ],
        ];

        foreach ($carriers as $c) {
            Carrier::updateOrCreate(['code' => $c['code']], $c);
        }

        // Global Locations & Key Hubs (with exact coordinates for Leaflet.js mapping)
        $locations = [
            [
                'code' => 'IDJKT',
                'name' => 'Port of Tanjung Priok (Jakarta)',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
                'latitude' => -6.1033,
                'longitude' => 106.8833,
                'timezone' => 'Asia/Jakarta',
                'is_hub' => true,
            ],
            [
                'code' => 'SGSIN',
                'name' => 'Port of Singapore',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'latitude' => 1.2644,
                'longitude' => 103.8220,
                'timezone' => 'Asia/Singapore',
                'is_hub' => true,
            ],
            [
                'code' => 'EGSUZ',
                'name' => 'Suez Canal Transit Checkpoint',
                'city' => 'Port Said',
                'country' => 'Egypt',
                'latitude' => 31.2653,
                'longitude' => 32.3019,
                'timezone' => 'Africa/Cairo',
                'is_hub' => false,
            ],
            [
                'code' => 'NLRTM',
                'name' => 'Port of Rotterdam',
                'city' => 'Rotterdam',
                'country' => 'Netherlands',
                'latitude' => 51.9244,
                'longitude' => 4.4777,
                'timezone' => 'Europe/Amsterdam',
                'is_hub' => true,
            ],
            [
                'code' => 'DEHAM',
                'name' => 'Port of Hamburg',
                'city' => 'Hamburg',
                'country' => 'Germany',
                'latitude' => 53.5488,
                'longitude' => 9.9872,
                'timezone' => 'Europe/Berlin',
                'is_hub' => true,
            ],
            [
                'code' => 'CNSHA',
                'name' => 'Port of Shanghai (Yangshan)',
                'city' => 'Shanghai',
                'country' => 'China',
                'latitude' => 30.6300,
                'longitude' => 122.0650,
                'timezone' => 'Asia/Shanghai',
                'is_hub' => true,
            ],
            [
                'code' => 'USLAX',
                'name' => 'Port of Los Angeles',
                'city' => 'Los Angeles',
                'country' => 'United States',
                'latitude' => 33.7432,
                'longitude' => -118.2673,
                'timezone' => 'America/Los_Angeles',
                'is_hub' => true,
            ],
            [
                'code' => 'AUSYD',
                'name' => 'Port Botany (Sydney)',
                'city' => 'Sydney',
                'country' => 'Australia',
                'latitude' => -33.9712,
                'longitude' => 151.2185,
                'timezone' => 'Australia/Sydney',
                'is_hub' => true,
            ],
        ];

        foreach ($locations as $loc) {
            Location::updateOrCreate(['code' => $loc['code']], $loc);
        }
    }
}
