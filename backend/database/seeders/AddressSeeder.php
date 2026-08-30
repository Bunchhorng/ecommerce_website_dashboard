<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Two addresses per customer (one default) matching the frontend mock shape.
     */
    public function run(): void
    {
        $rows = [
            'olivia.bennett@example.com' => [
                ['Home', '482 Meridian Avenue, Apt 3B', '', 'San Francisco', 'CA', '94110'],
                ['Work', '900 Bryant Street, Suite 220', '', 'San Francisco', 'CA', '94103'],
            ],
            'marcus.lee@example.com' => [
                ['Home', '128 Baltic Street, Apt 4R', '', 'Brooklyn', 'NY', '11201'],
                ['Work', '71 Greene Street, Floor 3', '', 'New York', 'NY', '10013'],
            ],
            'priya.shah@example.com' => [
                ['Home', '2301 Queen Anne Ave N, Apt 9', '', 'Seattle', 'WA', '98109'],
                ['Work', '500 Terry Avenue N, Suite 200', '', 'Seattle', 'WA', '98109'],
            ],
            'jake.miller@example.com' => [
                ['Home', '12 Oak Hollow Lane', '', 'Austin', 'TX', '73301'],
                ['Work', '4200 Airport Blvd, Suite 110', '', 'Austin', 'TX', '78722'],
            ],
            'elena.rodriguez@example.com' => [
                ['Home', '88 Biscayne Boulevard, Apt 1502', '', 'Miami', 'FL', '33132'],
                ['Work', '200 South Biscayne Blvd, Suite 700', '', 'Miami', 'FL', '33131'],
            ],
            'dan.okafor@example.com' => [
                ['Home', '4410 N Sheridan Road, Unit 2E', '', 'Chicago', 'IL', '60613'],
                ['Work', '333 W Wacker Drive, Suite 900', '', 'Chicago', 'IL', '60606'],
            ],
        ];

        foreach (User::where('role', 'customer')->get() as $user) {
            foreach ($rows[$user->email] ?? [] as $i => [$label, $line1, $line2, $city, $state, $zip]) {
                Address::create([
                    'user_id' => $user->id,
                    'label' => $label,
                    'full_name' => $user->name,
                    'phone' => $user->phone,
                    'address_line1' => $line1,
                    'address_line2' => $line2,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $zip,
                    'country' => 'US',
                    'is_default' => $i === 0,
                ]);
            }
        }
    }
}