<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        $users = [
            // Admin Users
            [
                'name' => 'Elbildad Admin',
                'email' => 'admin.user@elbildad.com',
                'whatsapp_number' => '2348011112222',
                'role' => RoleEnum::ADMIN->value,
            ],
            // Agent Users
            [
                'name' => 'Elbildad Agent',
                'email' => 'agent.user@elbildad.com',
                'whatsapp_number' => '2348022223333',
                'role' => RoleEnum::AGENT->value,
            ],
            // Regular Users / Customers
            [
                'name' => 'Regular Customer',
                'email' => 'regular.user@elbildad.com',
                'whatsapp_number' => '2348033334444',
                'role' => RoleEnum::CUSTOMER->value,
            ]
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'whatsapp_number' => $userData['whatsapp_number'],
                    'password' => $password,
                ]
            );

            // Assign role using Spatie Role permissions
            $user->syncRoles([$userData['role']]);
        }
    }
}
