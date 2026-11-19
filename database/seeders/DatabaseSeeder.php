<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with complete realistic operational scenarios.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CarrierLocationSeeder::class,
            ShipmentScenarioSeeder::class,
        ]);
    }
}
