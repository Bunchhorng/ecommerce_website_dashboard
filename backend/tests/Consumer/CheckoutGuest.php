<?php

namespace Tests\Consumer;

use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;

/**
 * Shared helper for building a realistic consumer cart and running checkout.
 */
trait CheckoutGuest
{
    use WithFaker;

    /** @var Product */
    protected $product;

    /** @var int */
    protected $variantId;

    /** @var int */
    protected $shippingId;

    protected function productWithStock(int $stock = 10, ?float $price = null): Product
    {
        $product = Product::factory()->withVariant($price, $stock)->create();

        $this->product = $product;
        $this->variantId = (int) $product->variants()->first()->id;

        return $product;
    }

    protected function seedShipping(): int
    {
        if ($this->shippingId === null) {
            $this->shippingId = (int) ShippingMethod::factory()->create(['price' => 5.00])->id;
        }

        return $this->shippingId;
    }

    protected function authAs(User $user)
    {
        Sanctum::actingAs($user);
    }

    protected function addressPayload(): array
    {
        return [
            'full_name' => 'Test Buyer',
            'phone' => '+1 555 0100',
            'address_line1' => '123 Test St',
            'address_line2' => '',
            'city' => 'Austin',
            'state' => 'TX',
            'postal_code' => '73301',
            'country' => 'US',
        ];
    }

    /**
     * Begin checkout for the current authenticated user / session cart.
     */
    protected function beginCheckout(?string $sessionId = null, array $overrides = [])
    {
        $payload = array_merge([
            'shipping_method_id' => $this->seedShipping(),
            'payment_method' => 'card',
            'address' => $this->addressPayload(),
        ], $overrides);

        return $this->withHeader('X-Session-Id', $sessionId ?? 'test-session')
            ->postJson('/api/checkout', $payload);
    }
}