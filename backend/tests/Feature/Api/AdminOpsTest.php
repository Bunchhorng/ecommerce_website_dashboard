<?php

namespace Tests\Feature\Api;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOpsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    protected function order(array $attributes = []): Order
    {
        $customer = User::factory()->create(['name' => 'Olivia Bennett']);

        return Order::factory()->create(array_merge([
            'user_id' => $customer->id,
            'customer_name' => 'Olivia Bennett',
            'total' => 100,
            'placed_at' => now(),
        ], $attributes));
    }

    public function test_dashboard_requires_authentication_and_admin_role(): void
    {
        $this->getJson('/api/admin/dashboard/overview')->assertStatus(401);
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/admin/dashboard/overview')
            ->assertStatus(403);
    }

    public function test_dashboard_overview_returns_metrics_and_series(): void
    {
        $order = $this->order(['status' => Order::STATUS_DELIVERED, 'payment_status' => Order::PAYMENT_PAID]);
        Payment::create([
            'order_id' => $order->id,
            'method' => 'card',
            'status' => Payment::STATUS_COMPLETED,
            'transaction_id' => 'TXN-1',
            'amount' => 100,
            'paid_at' => now(),
        ]);
        $product = Product::factory()->withVariant(stock: 0)->create();
        Inventory::where('product_variant_id', $product->variants()->first()->id)
            ->update(['quantity' => 0]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/dashboard/overview?range=30')
            ->assertOk()
            ->assertJsonPath('data.range', '30')
            ->assertJsonStructure([
                'data' => [
                    'metrics' => [
                        'total_revenue', 'today_revenue', 'week_revenue', 'month_revenue',
                        'revenue_delta', 'orders_count', 'orders_delta', 'pending_orders',
                        'processing_orders', 'completed_orders', 'cancelled_orders',
                        'customers_count', 'customers_delta', 'total_products',
                        'total_categories', 'total_brands', 'low_stock_products',
                        'out_of_stock_products',
                    ],
                    'revenue_trend' => [['date', 'revenue']],
                    'orders_trend' => [['date', 'orders']],
                    'status_distribution' => [['status', 'count']],
                    'payment_status_distribution' => [['status', 'count']],
                    'sales_by_category',
                    'top_selling_products',
                    'low_stock',
                    'recent_customers',
                    'recent_reviews',
                    'recent_payments',
                ],
            ]);
    }

    public function test_dashboard_accepts_custom_range(): void
    {
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/dashboard/overview?range=custom&from='.$from->toDateString().'&to='.$to->toDateString())
            ->assertOk()
            ->assertJsonPath('data.range', 'custom');
    }

    public function test_payments_index_filters_and_show_returns_transactions(): void
    {
        $completed = $this->order(['payment_status' => Order::PAYMENT_PAID]);
        Payment::create([
            'order_id' => $completed->id,
            'method' => 'card',
            'status' => Payment::STATUS_COMPLETED,
            'transaction_id' => 'TXN-100',
            'amount' => 80,
            'paid_at' => now(),
        ]);

        $pending = $this->order();
        $payment = Payment::create([
            'order_id' => $pending->id,
            'method' => 'bank',
            'status' => Payment::STATUS_PENDING,
            'transaction_id' => 'TXN-200',
            'amount' => 50,
        ]);

        PaymentTransaction::create([
            'payment_id' => $payment->id,
            'type' => 'capture',
            'status' => 'pending',
            'amount' => 50,
            'reference' => 'REF-1',
        ]);

        $auth = fn () => $this->actingAs($this->admin(), 'sanctum');

        $auth()->getJson('/api/admin/payments')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.customer_name', 'Olivia Bennett');

        $auth()->getJson('/api/admin/payments?status=pending')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.method', 'bank');

        $auth()->getJson('/api/admin/payments?q=TXN-100')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.transaction_id', 'TXN-100');

        $auth()->getJson("/api/admin/payments/{$payment->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.transactions')
            ->assertJsonPath('data.transactions.0.type', 'capture');
    }

    public function test_shipments_can_be_listed_be_filtered_and_updated(): void
    {
        $shipping = ShippingMethod::factory()->create();
        $order = $this->order(['shipping_amount' => 5]);

        $shipment = Shipment::create([
            'order_id' => $order->id,
            'shipping_method_id' => $shipping->id,
            'carrier' => 'DHL',
            'tracking_number' => 'TRACK-1',
            'status' => Shipment::STATUS_PENDING,
            'address_snapshot' => $order->shipping_address,
        ]);

        $auth = fn () => $this->actingAs($this->admin(), 'sanctum');

        $auth()->getJson('/api/admin/shipments')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.carrier', 'DHL');

        $auth()->getJson('/api/admin/shipments?status=shipped')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $auth()->getJson('/api/admin/shipments?q=TRACK')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $auth()->putJson("/api/admin/shipments/{$shipment->id}", [
            'tracking_number' => 'TRACK-2',
            'carrier' => 'FedEx',
            'status' => Shipment::STATUS_DELIVERED,
        ])->assertOk()
            ->assertJsonPath('data.status', 'delivered')
            ->assertJsonPath('data.tracking_number', 'TRACK-2')
            ->assertJsonPath('data.carrier', 'FedEx');

        $this->assertNotNull($shipment->fresh()->shipped_at);
        $this->assertNotNull($shipment->fresh()->delivered_at);

        $auth()->putJson("/api/admin/shipments/{$shipment->id}", [
            'status' => 'bogus',
        ])->assertStatus(422);
    }

    public function test_shipment_update_sets_shipped_at_only_when_shipped(): void
    {
        $shipping = ShippingMethod::factory()->create();
        $shipment = Shipment::create([
            'order_id' => $this->order()->id,
            'shipping_method_id' => $shipping->id,
            'status' => Shipment::STATUS_PENDING,
            'address_snapshot' => [],
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->putJson("/api/admin/shipments/{$shipment->id}", [
                'status' => Shipment::STATUS_SHIPPED,
            ])->assertOk();

        $this->assertNotNull($shipment->fresh()->shipped_at);
        $this->assertNull($shipment->fresh()->delivered_at);

        $this->actingAs($this->admin(), 'sanctum')
            ->putJson("/api/admin/shipments/{$shipment->id}", [
                'status' => Shipment::STATUS_PENDING,
            ])->assertOk();

        $this->assertNull($shipment->fresh()->shipped_at);
    }

    public function test_notifications_can_be_listed_marked_read_and_deleted(): void
    {
        $admin = $this->admin();

        $admin->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\OrderStatusNotification',
            'data' => ['title' => 'New order', 'message' => 'Order placed.'],
        ]);
        $read = $admin->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\OrderStatusNotification',
            'data' => ['title' => 'Old order', 'message' => 'Shipped.'],
            'read_at' => now()->subHour(),
        ]);

        $auth = fn () => $this->actingAs($admin, 'sanctum');

        $auth()->getJson('/api/admin/notifications')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.unread_count', 1)
            ->assertJsonStructure(['data' => [['id', 'type', 'title', 'message', 'read_at', 'created_at']]]);

        $auth()->getJson('/api/admin/notifications?filter=unread')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $auth()->getJson('/api/admin/notifications?filter=read')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $auth()->getJson('/api/admin/notifications/unread-count')
            ->assertOk()
            ->assertJsonPath('data.unread_count', 1);

        $auth()->postJson("/api/admin/notifications/{$read->id}/read")
            ->assertOk();
        $this->assertNotNull($read->fresh()->read_at);

        $auth()->postJson('/api/admin/notifications/all/read')
            ->assertOk();
        $this->assertSame(0, $admin->unreadNotifications()->count());

        $auth()->deleteJson("/api/admin/notifications/{$read->id}")
            ->assertNoContent();
    }

    public function test_notification_actions_require_ownership(): void
    {
        $admin = $this->admin();
        $other = $this->admin();

        $foreign = $other->notifications()->create([
            'id' => Str::uuid(),
            'type' => 'App\Notifications\OrderStatusNotification',
            'data' => ['title' => 'Other', 'message' => 'Other user.'],
        ]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/notifications/{$foreign->id}/read")
            ->assertStatus(404);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/admin/notifications/{$foreign->id}")
            ->assertStatus(404);
    }

    public function test_report_summary_returns_aggregates(): void
    {
        $order = $this->order(['status' => Order::STATUS_DELIVERED, 'payment_status' => Order::PAYMENT_PAID, 'total' => 120]);
        Order::factory()->create(['status' => Order::STATUS_CANCELLED, 'payment_status' => Order::PAYMENT_UNPAID, 'total' => 40, 'placed_at' => now()->subDays(2)]);
        Order::factory()->create(['status' => Order::STATUS_REFUNDED, 'payment_status' => Order::PAYMENT_REFUNDED, 'total' => 30, 'placed_at' => now()->subDays(3)]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/reports/summary')
            ->assertOk()
            ->assertJsonPath('data.revenue', 120)
            ->assertJsonPath('data.orders_count', 3)
            ->assertJsonPath('data.customers_count', 3)
            ->assertJsonPath('data.refunded', 30)
            ->assertJsonStructure([
                'data' => [
                    'revenue', 'items_revenue', 'refunded', 'orders_count',
                    'customers_count', 'units_sold', 'avg_order_value',
                    'payment_methods', 'low_stock_count',
                ],
            ]);
    }

    public function test_report_csv_exports_download(): void
    {
        $this->order();

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/reports/orders.csv')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/reports/products.csv')
            ->assertOk();

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/reports/payments.csv')
            ->assertOk();
    }

    public function test_products_bulk_status_update(): void
    {
        $a = Product::factory()->create(['is_active' => true]);
        $b = Product::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin(), 'sanctum')
            ->patchJson('/api/admin/products', [
                'ids' => [$a->id, $b->id],
                'is_active' => false,
            ])->assertOk()
            ->assertJsonPath('data.updated', 2);

        $this->assertFalse($a->fresh()->is_active);
        $this->assertFalse($b->fresh()->is_active);
    }

    public function test_products_bulk_status_requires_valid_payload(): void
    {
        $this->actingAs($this->admin(), 'sanctum')
            ->patchJson('/api/admin/products', ['ids' => [], 'is_active' => true])
            ->assertStatus(422);
    }

    public function test_deleted_products_can_be_listed_and_restored(): void
    {
        $product = Product::factory()->create();
        $product->delete();

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/products?deleted=true')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/products/{$product->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id);

        $this->assertNull($product->fresh()->deleted_at);
    }

    public function test_store_rejects_duplicate_skus_within_variant_list(): void
    {
        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/admin/products', [
                'name' => 'T-Shirt',
                'variants' => [
                    ['name' => 'Small', 'sku' => 'TS-RED-S'],
                    ['name' => 'Large', 'sku' => 'ts-red-s'],
                ],
            ])->assertStatus(422)
            ->assertJsonValidationErrors('variants');
    }

    public function test_store_rejects_sku_used_by_another_product(): void
    {
        $other = Product::factory()->withVariant()->create();
        $existingSku = $other->variants()->first()->sku;

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/admin/products', [
                'name' => 'New Product',
                'variants' => [
                    ['name' => 'Default', 'sku' => $existingSku],
                ],
            ])->assertStatus(422)
            ->assertJsonValidationErrors('variants');
    }

    public function test_update_rejects_duplicate_sku_against_other_products_but_allows_own(): void
    {
        $own = Product::factory()->withVariant()->create();
        $ownSku = $own->variants()->first()->sku;
        $other = Product::factory()->withVariant()->create();
        $otherSku = $other->variants()->first()->sku;

        $this->actingAs($this->admin(), 'sanctum')
            ->putJson("/api/admin/products/{$own->id}", [
                'name' => $own->name,
                'variants' => [
                    ['id' => $own->variants()->first()->id, 'sku' => $ownSku],
                ],
            ])->assertOk();

        $this->actingAs($this->admin(), 'sanctum')
            ->putJson("/api/admin/products/{$own->id}", [
                'name' => $own->name,
                'variants' => [
                    ['id' => $own->variants()->first()->id, 'sku' => $otherSku],
                ],
            ])->assertStatus(422)
            ->assertJsonValidationErrors('variants');
    }
}
