<?php

namespace Tests\Feature\Api;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInventoryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_inventory_routes_require_authentication(): void
    {
        $this->getJson('/api/admin/inventory')->assertStatus(401);
    }

    public function test_customer_is_forbidden_from_inventory_routes(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/admin/inventory')
            ->assertStatus(403);
    }

    public function test_inventory_index_returns_stock_meta(): void
    {
        $product = Product::factory()->withVariant(price: 50, stock: 12)->create(['name' => 'Wireless Mouse']);
        $variant = $product->variants()->first();

        $response = $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/inventory')
            ->assertOk();

        $this->assertSame(1, $response->json('meta.total'));

        $inventory = $response->json('data.0');
        $this->assertSame($variant->id, $inventory['product_variant_id']);
        $this->assertSame('Wireless Mouse', $inventory['product']['name']);
        $this->assertSame(12, $inventory['quantity']);
        $this->assertSame(0, $inventory['reserved_quantity']);
        $this->assertSame(12, $inventory['available_quantity']);
        $this->assertFalse($inventory['is_low_stock']);
    }

    public function test_inventory_index_filters_by_stock_status(): void
    {
        Product::factory()->withVariant(stock: 20)->create();
        Product::factory()->withVariant(stock: 2)->create();
        Product::factory()->withVariant(stock: 0)->create();

        $auth = fn () => $this->actingAs($this->admin(), 'sanctum');

        $auth()->getJson('/api/admin/inventory?stock_status=out')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.quantity', 0);

        $auth()->getJson('/api/admin/inventory?stock_status=low')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $auth()->getJson('/api/admin/inventory?stock_status=in')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_inventory_index_searches_by_product_name_and_sku(): void
    {
        Product::factory()->withVariant(stock: 5)->create(['name' => 'Wireless Mouse']);
        $product = Product::factory()->withVariant(stock: 5)->create(['name' => 'Mechanical Keyboard']);
        $variant = $product->variants()->first();

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/inventory?q=wireless')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/inventory?q='.$variant->sku)
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_transactions_returns_ledger_for_inventory(): void
    {
        $product = Product::factory()->withVariant(price: 50, stock: 10)->create();
        $variant = $product->variants()->first();
        $inventory = Inventory::where('product_variant_id', $variant->id)->firstOrFail();

        $service = app(InventoryService::class);
        $service->reserve($variant->id, 3);
        $service->deduct($variant->id, 3);
        $service->release($variant->id, 1);
        $service->adjust($variant->id, 15);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson("/api/admin/inventory/{$inventory->id}/transactions")
            ->assertOk()
            ->assertJsonPath('meta.total', 4)
            ->assertJsonStructure([
                'data' => [[
                    'id', 'inventory_id', 'type', 'quantity', 'balance_after',
                    'reference', 'note', 'created_by', 'created_at',
                ]],
            ]);
    }

    public function test_transactions_filters_by_type(): void
    {
        $product = Product::factory()->withVariant(stock: 10)->create();
        $variant = $product->variants()->first();
        $inventory = Inventory::where('product_variant_id', $variant->id)->firstOrFail();

        $service = app(InventoryService::class);
        $service->reserve($variant->id, 3);
        $service->deduct($variant->id, 3);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson("/api/admin/inventory/{$inventory->id}/transactions?type=deduct")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'deduct');
    }

    public function test_admin_stock_edit_logs_adjust_transaction_with_creator(): void
    {
        $admin = $this->admin();
        $product = Product::factory()->withVariant(stock: 10)->create();
        $variant = $product->variants()->first();
        $inventory = Inventory::where('product_variant_id', $variant->id)->firstOrFail();

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/admin/products/{$product->id}", [
                'name' => $product->name,
                'variants' => [[
                    'id' => $variant->id,
                    'quantity' => 15,
                ]],
            ])->assertOk();

        $ledger = InventoryTransaction::where('inventory_id', $inventory->id)
            ->where('type', 'adjust')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(5, $ledger->quantity);
        $this->assertSame(15, $ledger->balance_after);
        $this->assertSame($admin->id, $ledger->created_by);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/admin/inventory/{$inventory->id}/transactions")
            ->assertOk()
            ->assertJsonPath('data.0.type', 'adjust')
            ->assertJsonPath('data.0.quantity', 5)
            ->assertJsonPath('data.0.created_by.id', $admin->id);
    }
}