<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VisaRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'visa_admin']);
        $agentRole = Role::firstOrCreate(['name' => 'visa_agent']);

        // Create test Visa Admin
        $visaAdmin = User::firstOrCreate(
            ['email' => 'visa_admin@elbildad.ng'],
            [
                'name' => 'Visa Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $visaAdmin->assignRole($adminRole);

        // Create test Visa Agent
        $visaAgent = User::firstOrCreate(
            ['email' => 'visa_agent@elbildad.ng'],
            [
                'name' => 'Visa Agent',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $visaAgent->assignRole($agentRole);
    }
}
