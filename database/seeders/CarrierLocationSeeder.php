<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use Illuminate\Database\Seeder;

class CarrierLocationSeeder extends Seeder
{
    public function run(): void
    {
        // Master Carrier Internasional (Slide 03 & 20)
        Carrier::updateOrCreate(['code' => 'MSK'], [
            'name' => 'Maersk Line',
            'service_type' => 'OCEAN',
            'contact_email' => 'ops@maersk.com',
            'contact_phone' => '+45 33 63 33 63',
            'is_active' => true,
        ]);

        Carrier::updateOrCreate(['code' => 'CMA'], [
            'name' => 'CMA CGM Group',
            'service_type' => 'OCEAN',
            'contact_email' => 'support@cma-cgm.com',
            'contact_phone' => '+33 4 88 91 90 00',
            'is_active' => true,
        ]);

        Carrier::updateOrCreate(['code' => 'DHL-GF'], [
            'name' => 'DHL Global Forwarding',
            'service_type' => 'MULTIMODAL',
            'contact_email' => 'customercare@dhl.com',
            'contact_phone' => '+49 228 18 20',
            'is_active' => true,
        ]);

        // Master Pelabuhan Dunia dengan Koordinat Nyata (Slide 09)
        Location::updateOrCreate(['code' => 'IDJKT'], [
            'name' => 'Port of Tanjung Priok',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'latitude' => -6.1022,
            'longitude' => 106.8833,
        ]);

        Location::updateOrCreate(['code' => 'SGSIN'], [
            'name' => 'Port of Singapore',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'latitude' => 1.2644,
            'longitude' => 103.8400,
        ]);

        Location::updateOrCreate(['code' => 'EGSUZ'], [
            'name' => 'Suez Canal Transit Port',
            'city' => 'Suez',
            'country' => 'Egypt',
            'latitude' => 29.9668,
            'longitude' => 32.5498,
        ]);

        Location::updateOrCreate(['code' => 'NLRTM'], [
            'name' => 'Port of Rotterdam',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'latitude' => 51.9547,
            'longitude' => 4.1488,
        ]);
    }
}