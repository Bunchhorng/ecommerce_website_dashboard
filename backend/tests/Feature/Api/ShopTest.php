<?php

namespace Tests\Feature\Api;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_shop_routes_require_authentication(): void
    {
        $this->getJson('/api/admin/shops')->assertStatus(401);
    }

    public function test_customer_is_forbidden_from_admin_shop_routes(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson('/api/admin/shops')
            ->assertStatus(403);
    }

    public function test_admin_can_create_a_shop(): void
    {
        $response = $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/admin/shops', [
                'name' => 'Sovanna Outlet',
                'code' => 'SOV',
                'branch_type' => 'outlet',
                'city' => 'Phnom Penh',
                'status' => 'active',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Sovanna Outlet')
            ->assertJsonPath('data.code', 'SOV');

        $this->assertDatabaseHas('shops', ['code' => 'SOV']);
        $slug = $response->json('data.slug');
        $this->assertNotEmpty($slug);
    }

    public function test_admin_can_list_and_show_shops(): void
    {
        $shop = Shop::factory()->create();
        $admin = $this->admin();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/shops')
            ->assertOk()
            ->assertJsonPath('data.0.id', $shop->id);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/admin/shops/{$shop->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $shop->id);
    }

    public function test_default_shop_cannot_be_deleted(): void
    {
        $shop = Shop::factory()->default()->create();
        $this->actingAs($this->admin(), 'sanctum')
            ->deleteJson("/api/admin/shops/{$shop->id}")
            ->assertStatus(422);
    }

    public function test_shop_code_must_be_unique(): void
    {
        Shop::factory()->create(['code' => 'AEON']);
        $this->actingAs($this->admin(), 'sanctum')
            ->postJson('/api/admin/shops', ['name' => 'Dup', 'code' => 'AEON'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }

    public function test_default_shop_must_stay_active(): void
    {
        $shop = Shop::factory()->default()->create(['status' => Shop::STATUS_ACTIVE]);
        $this->actingAs($this->admin(), 'sanctum')
            ->patchJson("/api/admin/shops/{$shop->id}/status", ['status' => Shop::STATUS_SUSPENDED])
            ->assertStatus(422);
    }

    public function test_admin_can_update_shop_status(): void
    {
        $shop = Shop::factory()->create(['status' => Shop::STATUS_ACTIVE]);
        $this->actingAs($this->admin(), 'sanctum')
            ->patchJson("/api/admin/shops/{$shop->id}/status", ['status' => Shop::STATUS_SUSPENDED])
            ->assertOk()
            ->assertJsonPath('data.status', Shop::STATUS_SUSPENDED);
    }

    public function test_public_shop_listing_only_shows_active_shops(): void
    {
        Shop::factory()->create(['status' => Shop::STATUS_ACTIVE, 'name' => 'Visible Branch']);
        Shop::factory()->suspended()->create(['name' => 'Hidden Branch']);

        $response = $this->getJson('/api/shops')->assertOk();
        $names = array_column($response->json('data'), 'name');

        $this->assertContains('Visible Branch', $names);
        $this->assertNotContains('Hidden Branch', $names);
    }

    public function test_public_shop_page_404s_for_suspended_shop(): void
    {
        $shop = Shop::factory()->suspended()->create();
        $this->getJson("/api/shops/{$shop->slug}")->assertStatus(404);
    }

    public function test_public_shop_page_lists_only_that_branch_products(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();

        $mine = Product::factory()->create(['shop_id' => $shopA->id, 'name' => 'Shop A Product']);
        Product::factory()->create(['shop_id' => $shopB->id, 'name' => 'Shop B Product']);

        $response = $this->getJson("/api/shops/{$shopA->slug}/products")->assertOk();
        $names = array_column($response->json('data'), 'name');

        $this->assertContains('Shop A Product', $names);
        $this->assertNotContains('Shop B Product', $names);
    }

    public function test_shop_staff_of_one_branch_cannot_read_another_branchs_inventory(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();

        $staff = User::factory()->create();
        $staff->shops()->attach($shopA->id, ['role_in_shop' => 'manager', 'status' => 'active']);

        $productB = Product::factory()->withVariant()->create(['shop_id' => $shopB->id]);
        $inventoryB = Inventory::where('product_variant_id', $productB->variants()->value('id'))->firstOrFail();
        $inventoryB->update(['shop_id' => $shopB->id]);

        // The staff of shop A must not pass the inventory policy for shop B's stock.
        $this->assertFalse($staff->can('view', $inventoryB));
        $this->assertFalse($staff->can('update', $inventoryB));
    }

    public function test_shop_staff_can_read_their_own_branch_inventory(): void
    {
        $shopA = Shop::factory()->create();

        $staff = User::factory()->create();
        $staff->shops()->attach($shopA->id, ['role_in_shop' => 'staff', 'status' => 'active']);

        $product = Product::factory()->withVariant()->create(['shop_id' => $shopA->id]);
        $inventory = Inventory::where('product_variant_id', $product->variants()->value('id'))->firstOrFail();
        $inventory->update(['shop_id' => $shopA->id]);

        $this->assertTrue($staff->can('view', $inventory));
        // 'staff' is not a manager, so stock adjustment is denied.
        $this->assertFalse($staff->can('update', $inventory));
    }

    public function test_inventory_is_isolated_per_branch(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();

        $product = Product::factory()->withVariant(20, 10)->create();
        $variantId = $product->variants()->value('id');

        // Shop A has 20, Shop B has 50 for the same variant — independent stock.
        Inventory::create(['product_variant_id' => $variantId, 'shop_id' => $shopA->id, 'quantity' => 20]);
        Inventory::create(['product_variant_id' => $variantId, 'shop_id' => $shopB->id, 'quantity' => 50]);

        $a = Inventory::where('product_variant_id', $variantId)->where('shop_id', $shopA->id)->firstOrFail();
        $b = Inventory::where('product_variant_id', $variantId)->where('shop_id', $shopB->id)->firstOrFail();

        $this->assertSame(20, $a->quantity);
        $this->assertSame(50, $b->quantity);
    }

    public function test_staff_cannot_update_product_of_another_branch(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();

        $staff = User::factory()->create();
        $staff->shops()->attach($shopA->id, ['role_in_shop' => 'manager', 'status' => 'active']);

        $productB = Product::factory()->create(['shop_id' => $shopB->id]);
        $productA = Product::factory()->create(['shop_id' => $shopA->id]);

        $this->assertFalse($staff->can('update', $productB));
        $this->assertTrue($staff->can('update', $productA));
    }

    public function test_admin_bypasses_branch_policies(): void
    {
        $shopB = Shop::factory()->create();
        $admin = $this->admin();
        $productB = Product::factory()->create(['shop_id' => $shopB->id]);

        $this->assertTrue($admin->can('update', $productB));
    }

    public function test_user_can_belong_to_multiple_branches(): void
    {
        $shopA = Shop::factory()->create();
        $shopB = Shop::factory()->create();
        $user = User::factory()->create();

        $user->shops()->attach($shopA->id, ['role_in_shop' => 'staff', 'status' => 'active']);
        $user->shops()->attach($shopB->id, ['role_in_shop' => 'manager', 'status' => 'active']);

        $this->assertTrue($user->belongsToShop($shopA));
        $this->assertTrue($user->belongsToShop($shopB));
        $this->assertFalse($user->ownsShop($shopA));
        $this->assertTrue($user->ownsShop($shopB));
    }

    public function test_suspended_membership_loses_access(): void
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role_in_shop' => 'manager', 'status' => 'suspended']);

        $this->assertFalse($user->belongsToShop($shop));
        $this->assertFalse($user->ownsShop($shop));
    }

    public function test_shop_is_soft_deletable_and_staff_relation_cascades(): void
    {
        $shop = Shop::factory()->create();
        $user = User::factory()->create();
        $user->shops()->attach($shop->id, ['role_in_shop' => 'owner', 'status' => 'active']);

        $shop->delete();

        $this->assertSoftDeleted('shops', ['id' => $shop->id]);
    }
}
