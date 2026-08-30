<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'name' => fake()->words(2, true),
            'sku' => 'SKU-' . Str::upper(Str::random(10)),
            'price' => fake()->randomFloat(2, 20, 200),
            'is_default' => false,
            'is_active' => true,
        ];
    }
}