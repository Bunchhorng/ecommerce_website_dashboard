<?php

namespace Tests\Feature\Api;

use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Consumer\CheckoutGuest;
use Tests\TestCase;

/**
 * Regression tests for fixes delivered in fix/qa-hotfixes.
 */
class QaHotfixTest extends TestCase
{
    use RefreshDatabase, CheckoutGuest;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_cancelling_paid_order_refunds_and_restores_stock(): void
    {
        $user = User::factory()->create();
        $this->productWithStock(10, 100.00);
        $this->authAs($user);

        $this->postJson('/api/cart', ['product_variant_id' => $this->variantId, 'quantity' => 2])->assertCreated();
        $orderNumber = $this->beginCheckout()->json('data.order_number');

        $this->postJson("/api/checkout/$orderNumber/confirm", ['transaction_id' => 'PAY-TEST-1'])->assertOk();

        $inventory = Inventory::where('product_variant_id', $this->variantId)->firstOrFail();
        $this->assertSame(8, (int) $inventory->quantity);
        $this->assertSame(2, (int) $inventory->sold_count);

        $this->postJson("/api/orders/$orderNumber/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.payment_status', 'refunded')
            ->assertJsonPath('data.payment.status', 'refunded');

        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $this->assertDatabaseHas('payment_transactions', [
            'payment_id' => $order->payment->id,
            'type' => 'refund',
            'status' => 'success',
        ]);

        $inventory->refresh();
        $this->assertSame(10, (int) $inventory->quantity);
        $this->assertSame(0, (int) $inventory->sold_count);
    }

    public function test_admin_transition_to_cancelled_releases_reservation(): void
    {
        $this->productWithStock(10, 100.00);
        $this->authAs(User::factory()->create());

        $this->postJson('/api/cart', ['product_variant_id' => $this->variantId, 'quantity' => 3])->assertCreated();
        $this->beginCheckout()->assertStatus(201);

        $order = Order::where('status', Order::STATUS_PENDING)->latest('id')->firstOrFail();
        $this->assertSame(3, (int) Inventory::where('product_variant_id', $this->variantId)->firstOrFail()->reserved_quantity);

        $this->actingAs($this->admin(), 'sanctum')
            ->putJson("/api/admin/orders/{$order->id}/transition", ['status' => 'cancelled'])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertSame(0, (int) Inventory::where('product_variant_id', $this->variantId)->firstOrFail()->reserved_quantity);
    }

    public function test_admin_transition_to_cancelled_refunds_paid_order_and_restores_stock(): void
    {
        $this->productWithStock(10, 100.00);
        $this->authAs(User::factory()->create());

        $this->postJson('/api/cart', ['product_variant_id' => $this->variantId, 'quantity' => 2])->assertCreated();
        $orderNumber = $this->beginCheckout()->json('data.order_number');
        $this->postJson("/api/checkout/$orderNumber/confirm", ['transaction_id' => 'PAY-TEST-1'])->assertOk();

        $inventory = Inventory::where('product_variant_id', $this->variantId)->firstOrFail();
        $this->assertSame(8, (int) $inventory->quantity);

        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $this->actingAs($this->admin(), 'sanctum')
            ->putJson("/api/admin/orders/{$order->id}/transition", ['status' => 'cancelled'])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.payment_status', 'refunded')
            ->assertJsonPath('data.payment.status', 'refunded');

        $inventory->refresh();
        $this->assertSame(10, (int) $inventory->quantity);
        $this->assertSame(0, (int) $inventory->sold_count);
    }

    public function test_admin_refund_transition_restores_stock(): void
    {
        $this->productWithStock(10, 100.00);
        $this->authAs(User::factory()->create());

        $this->postJson('/api/cart', ['product_variant_id' => $this->variantId, 'quantity' => 5])->assertCreated();
        $orderNumber = $this->beginCheckout()->json('data.order_number');
        $this->postJson("/api/checkout/$orderNumber/confirm", ['transaction_id' => 'PAY-TEST-1'])->assertOk();

        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $inventory = Inventory::where('product_variant_id', $this->variantId)->firstOrFail();
        $this->assertSame(5, (int) $inventory->quantity);

        $this->actingAs($this->admin(), 'sanctum')
            ->putJson("/api/admin/orders/{$order->id}/transition", ['status' => 'refunded'])
            ->assertOk()
            ->assertJsonPath('data.status', 'refunded')
            ->assertJsonPath('data.payment.status', 'refunded');

        $inventory->refresh();
        $this->assertSame(10, (int) $inventory->quantity);
        $this->assertSame(0, (int) $inventory->sold_count);
    }

    public function test_coupon_usage_is_released_when_order_is_cancelled(): void
    {
        $user = User::factory()->create();
        $this->productWithStock(30, 100.00);
        $coupon = Coupon::factory()->create(['code' => 'WELCOME10', 'type' => 'percentage', 'value' => 10]);
        $this->authAs($user);

        $this->postJson('/api/cart', ['product_variant_id' => $this->variantId, 'quantity' => 2])->assertCreated();
        $orderNumber = $this->beginCheckout(overrides: ['coupon_code' => 'WELCOME10'])->json('data.order_number');

        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $this->assertSame(1, (int) $coupon->fresh()->used_count);
        $this->assertDatabaseHas('coupon_usages', ['coupon_id' => $coupon->id, 'order_id' => $order->id]);

        $this->postJson("/api/orders/$orderNumber/cancel")->assertOk();

        $this->assertSame(0, (int) $coupon->fresh()->used_count);
        $this->assertDatabaseMissing('coupon_usages', ['coupon_id' => $coupon->id, 'order_id' => $order->id]);
    }

    public function test_coupon_usage_is_released_on_reservation_expiry(): void
    {
        $user = User::factory()->create();
        $this->productWithStock(30, 100.00);
        $coupon = Coupon::factory()->create(['code' => 'WELCOME10', 'type' => 'percentage', 'value' => 10]);
        $this->authAs($user);

        $this->postJson('/api/cart', ['product_variant_id' => $this->variantId, 'quantity' => 2])->assertCreated();
        $orderNumber = $this->beginCheckout(overrides: ['coupon_code' => 'WELCOME10'])->json('data.order_number');

        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $this->assertSame(1, (int) $coupon->fresh()->used_count);

        $order->forceFill(['placed_at' => now()->subMinutes(30)])->save();

        app(CheckoutService::class)->expireStaleReservations();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(0, (int) $coupon->fresh()->used_count);
        $this->assertDatabaseMissing('coupon_usages', ['coupon_id' => $coupon->id, 'order_id' => $order->id]);
    }

    public function test_negative_variant_quantity_is_rejected(): void
    {
        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/admin/products', [
                'name' => 'Bad Qty Product',
                'price' => 10,
                'variants' => [[
                    'name' => 'Default',
                    'price' => 10,
                    'quantity' => -5,
                ]],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('variants.0.quantity');
    }

    public function test_negative_variant_price_is_rejected(): void
    {
        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/admin/products', [
                'name' => 'Bad Price Product',
                'price' => 10,
                'variants' => [[
                    'name' => 'Default',
                    'price' => -5,
                    'quantity' => 5,
                ]],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('variants.0.price');
    }

    public function test_login_with_legacy_plaintext_hash_returns_invalid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'legacy@example.com']);
        DB::table('users')->where('id', $user->id)->update(['password' => 'admin12345']);

        $this->postJson('/api/auth/login', [
            'email' => 'legacy@example.com',
            'password' => 'admin12345',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Invalid credentials.');
    }

    public function test_mock_payment_confirm_is_blocked_in_production_mode(): void
    {
        $user = User::factory()->create();
        $this->productWithStock(10, 100.00);
        $this->authAs($user);

        $this->postJson('/api/cart', ['product_variant_id' => $this->variantId, 'quantity' => 1])->assertCreated();
        $orderNumber = $this->beginCheckout()->json('data.order_number');

        config(['ecommerce.payment_mode' => 'production']);

        $this->postJson("/api/checkout/$orderNumber/confirm", ['transaction_id' => 'PAY-TEST'])
            ->assertStatus(422)
            ->assertJsonPath('errors.message.0', 'Online payment confirmation is disabled until a payment gateway is configured.');

        $inventory = Inventory::where('product_variant_id', $this->variantId)->firstOrFail();
        $this->assertSame(10, (int) $inventory->quantity);
        $this->assertSame(1, (int) $inventory->reserved_quantity);

        config(['ecommerce.payment_mode' => 'sandbox']);

        $this->postJson("/api/checkout/$orderNumber/confirm", ['transaction_id' => 'PAY-TEST'])->assertOk();
    }
}