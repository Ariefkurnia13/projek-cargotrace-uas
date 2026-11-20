<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\Location;
use Illuminate\Database\Seeder;

class CarrierLocationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Master Carrier Internasional (Slide 03 & 20)
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

        // 2. Master Pelabuhan Dunia Lengkap (Slide 09 & 23)
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

        Location::updateOrCreate(['code' => 'DEHAM'], [
            'name' => 'Port of Hamburg',
            'city' => 'Hamburg',
            'country' => 'Germany',
            'latitude' => 53.5324,
            'longitude' => 9.9644,
        ]);

        Location::updateOrCreate(['code' => 'CNSHA'], [
            'name' => 'Port of Shanghai',
            'city' => 'Shanghai',
            'country' => 'China',
            'latitude' => 31.2304,
            'longitude' => 121.4737,
        ]);

        Location::updateOrCreate(['code' => 'USLAX'], [
            'name' => 'Port of Los Angeles',
            'city' => 'Los Angeles',
            'country' => 'United States',
            'latitude' => 33.7432,
            'longitude' => -118.2673,
        ]);

        Location::updateOrCreate(['code' => 'AUSYD'], [
            'name' => 'Port Botany (Sydney)',
            'city' => 'Sydney',
            'country' => 'Australia',
            'latitude' => -33.9744,
            'longitude' => 151.2131,
        ]);
    }
}