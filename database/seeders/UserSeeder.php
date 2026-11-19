<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Administrator
        User::updateOrCreate(
            ['email' => 'admin@cargotrace.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Password123!'),
                'role' => UserRole::ADMIN,
                'phone' => '+62 811-0001-999',
                'company_name' => 'CargoTrace Global HQ',
            ]
        );

        // 2. Logistics Operator
        User::updateOrCreate(
            ['email' => 'operator@cargotrace.test'],
            [
                'name' => 'Budi Operator',
                'password' => Hash::make('Password123!'),
                'role' => UserRole::OPERATOR,
                'phone' => '+62 812-3344-5566',
                'company_name' => 'CargoTrace Control Tower',
            ]
        );

        // 3. Customer 1: PT Nusantara Manufacturing (Owner of Group A shipments)
        User::updateOrCreate(
            ['email' => 'customer1@cargotrace.test'],
            [
                'name' => 'Siti Logistics Manager',
                'password' => Hash::make('Password123!'),
                'role' => UserRole::CUSTOMER,
                'phone' => '+62 813-9988-7711',
                'company_name' => 'PT Nusantara Manufacturing',
            ]
        );

        // 4. Customer 2: Indo Global Exporters (Owner of Group B/C shipments)
        User::updateOrCreate(
            ['email' => 'customer2@cargotrace.test'],
            [
                'name' => 'Rian Supply Officer',
                'password' => Hash::make('Password123!'),
                'role' => UserRole::CUSTOMER,
                'phone' => '+62 815-4422-1133',
                'company_name' => 'Indo Global Exporters',
            ]
        );
    }
}
