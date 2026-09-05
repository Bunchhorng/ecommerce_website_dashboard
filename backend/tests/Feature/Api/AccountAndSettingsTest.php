<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Review;
use App\Models\Setting;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_their_reviews(): void
    {
        $user = User::factory()->create();
        $product = \App\Models\Product::factory()->withVariant()->create(['price' => 29.99]);

        $order = Order::factory()->delivered()->create(['user_id' => $user->id]);
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $product->variants()->first()->id,
            'product_name' => $product->name,
            'variant_label' => 'Default',
            'sku' => $product->variants()->first()->sku,
            'unit_price' => 29.99,
            'quantity' => 1,
            'line_total' => 29.99,
        ]);

        Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'title' => 'Loved it',
            'body' => 'Great product.',
            'status' => Review::STATUS_APPROVED,
            'verified' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/account/reviews')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.rating', 5)
            ->assertJsonPath('data.0.product.name', $product->name);
    }

    public function test_admin_can_read_and_write_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/settings')
            ->assertOk()
            ->assertJsonPath('data.storeName', 'E-KHMER');

        $this->actingAs($admin, 'sanctum')
            ->putJson('/api/admin/settings', [
                'storeName' => 'My Shop',
                'lowStockThreshold' => 3,
                'emailLowStockAlerts' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.storeName', 'My Shop')
            ->assertJsonPath('data.lowStockThreshold', 3)
            ->assertJsonPath('data.emailLowStockAlerts', false);

        $this->assertSame('My Shop', Setting::get('storeName'));
    }

    public function test_admin_can_delete_a_review(): void
    {
        $user = User::factory()->create();
        $product = \App\Models\Product::factory()->withVariant()->create(['price' => 39.99]);
        $order = Order::factory()->delivered()->create(['user_id' => $user->id]);

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 4,
            'title' => 'Nice',
            'body' => 'Good.',
            'status' => Review::STATUS_APPROVED,
            'verified' => true,
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/admin/reviews/{$review->id}")
            ->assertOk();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_tracking_events_recorded_on_transition(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        TrackingEvent::create([
            'order_id' => $order->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/admin/orders/{$order->id}/transition", ['status' => Order::STATUS_CONFIRMED])
            ->assertOk();

        $this->assertNotNull(
            TrackingEvent::where('order_id', $order->id)->where('status', Order::STATUS_CONFIRMED)->first()
        );
    }
}
