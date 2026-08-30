<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 50, 500);
        $address = [
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address_line1' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->countryCode(),
        ];

        return [
            'order_number' => 'SV-' . date('Y') . '-' . strtoupper(Str::random(6)),
            'user_id' => User::factory(),
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_UNPAID,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'tax_amount' => round($subtotal * 0.1, 2),
            'shipping_amount' => 0,
            'total' => round($subtotal * 1.1, 2),
            'currency' => 'USD',
            'shipping_address' => $address,
            'billing_address' => $address,
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'customer_name' => fake()->name(),
            'placed_at' => now(),
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_DELIVERED,
            'payment_status' => Order::PAYMENT_PAID,
        ]);
    }
}