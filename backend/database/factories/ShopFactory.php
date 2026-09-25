<?php

namespace Database\Factories;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company().' Store';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'code' => strtoupper(Str::random(6)),
            'branch_type' => 'storefront',
            'description' => $this->faker->sentence(),
            'phone' => $this->faker->numerify('+855########'),
            'email' => $this->faker->unique()->companyEmail(),
            'address_line' => $this->faker->streetAddress(),
            'mall' => $this->faker->randomElement(['AEON Mall Sen Sok', 'Sovanna Market', 'Central Market']),
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'postal_code' => $this->faker->numerify('12###'),
            'country' => 'KH',
            'status' => Shop::STATUS_ACTIVE,
            'is_default' => false,
            'commission_rate' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Shop::STATUS_PENDING,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Shop::STATUS_SUSPENDED,
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
