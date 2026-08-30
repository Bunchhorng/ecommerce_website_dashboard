<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Mirror the frontend SHIPPING_METHODS mock.
     */
    public function run(): void
    {
        $methods = [
            ['Standard Shipping', 'standard', 'Free over $100 · 5–7 business days', 0.00, 5, 7],
            ['Express Shipping', 'express', '2–3 business days, tracked', 9.99, 2, 3],
            ['Same Day Delivery', 'same-day', 'Order before 1 PM · local cities only', 19.99, 1, 1],
        ];

        foreach ($methods as [$name, $code, $description, $price, $min, $max]) {
            ShippingMethod::create([
                'name' => $name,
                'code' => $code,
                'description' => $description,
                'price' => $price,
                'estimated_days_min' => $min,
                'estimated_days_max' => $max,
                'is_active' => true,
            ]);
        }
    }
}