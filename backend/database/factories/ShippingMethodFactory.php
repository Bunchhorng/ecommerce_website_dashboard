<?php

namespace Database\Factories;

use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'code' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 0, 20),
            'estimated_days_min' => fake()->numberBetween(1, 3),
            'estimated_days_max' => fake()->numberBetween(4, 10),
            'is_active' => true,
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
        ]);
    }
}