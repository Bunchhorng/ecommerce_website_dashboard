<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed one admin and six customer accounts.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@shopverse.dev',
            'password' => 'password',
            'role' => 'admin',
            'phone' => '+1 (415) 555-0100',
            'newsletter' => false,
            'email_verified_at' => now(),
        ]);

        $customers = [
            ['name' => 'Olivia Bennett', 'email' => 'olivia.bennett@example.com', 'phone' => '+1 (415) 555-0123', 'newsletter' => true],
            ['name' => 'Marcus Lee', 'email' => 'marcus.lee@example.com', 'phone' => '+1 (555) 010-0002', 'newsletter' => true],
            ['name' => 'Priya Shah', 'email' => 'priya.shah@example.com', 'phone' => '+1 (555) 010-0003', 'newsletter' => false],
            ['name' => 'Jake Miller', 'email' => 'jake.miller@example.com', 'phone' => '+1 (555) 010-0004', 'newsletter' => true],
            ['name' => 'Elena Rodriguez', 'email' => 'elena.rodriguez@example.com', 'phone' => '+1 (555) 010-0005', 'newsletter' => false],
            ['name' => 'Dan Okafor', 'email' => 'dan.okafor@example.com', 'phone' => '+1 (555) 010-0006', 'newsletter' => true],
        ];

        foreach ($customers as $customer) {
            User::create([
                'name' => $customer['name'],
                'email' => $customer['email'],
                'password' => 'password',
                'role' => User::ROLE_CUSTOMER,
                'phone' => $customer['phone'],
                'newsletter' => $customer['newsletter'],
                'email_verified_at' => now(),
            ]);
        }
    }
}