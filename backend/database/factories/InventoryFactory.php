<?php

namespace Database\Factories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_variant_id' => \App\Models\ProductVariant::factory(),
            'quantity' => fake()->numberBetween(0, 100),
            'reserved_quantity' => 0,
            'low_stock_threshold' => 5,
            'sold_count' => 0,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }
}