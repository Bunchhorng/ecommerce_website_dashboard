<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_only_active_products_with_meta(): void
    {
        Product::factory()->create(['is_active' => true, 'price' => 10]);
        Product::factory()->create(['is_active' => false, 'price' => 20]);

        $response = $this->getJson('/api/catalog/products?perPage=10')
            ->assertOk();

        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame(10.0, (float) $response->json('data.0.price'));
        $this->assertArrayHasKey('slug', $response->json('data.0'));
    }

    public function test_filters_by_category_and_brand(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        Product::factory()->withVariant()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'price' => 10]);
        Product::factory()->withVariant()->create(['price' => 99]);

        $this->getJson("/api/catalog/products?category={$category->slug}&perPage=10")
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->getJson("/api/catalog/products?brand={$brand->slug}&perPage=10")
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_price_range_and_sort(): void
    {
        Product::factory()->create(['price' => 10]);
        Product::factory()->create(['price' => 50]);
        Product::factory()->create(['price' => 100]);

        $asc = $this->getJson('/api/catalog/products?min=20&max=90&sort=price-asc&perPage=10')
            ->assertOk();

        $this->assertSame(1, $asc->json('meta.total'));
        $this->assertSame(50.0, (float) $asc->json('data.0.price'));

        $sorted = $this->getJson('/api/catalog/products?sort=price-desc&perPage=10')->assertOk();
        $prices = collect($sorted->json('data'))->pluck('price')->map(fn ($p) => (float) $p)->values();
        $this->assertSame($prices->sortDesc()->values()->all(), $prices->all());
    }

    public function test_search_matches_name(): void
    {
        Product::factory()->create(['name' => 'Ruby Wireless Mouse', 'price' => 10]);
        Product::factory()->create(['name' => 'Sapphire Keyboard', 'price' => 20]);

        $this->getJson('/api/catalog/products?q=wireless&perPage=10')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    public function test_featured_only_returns_featured(): void
    {
        Product::factory()->featured()->create(['price' => 10]);
        Product::factory()->create(['price' => 20]);

        $this->getJson('/api/catalog/featured')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_facets_return_tracked_groupings(): void
    {
        $a = Brand::factory()->create();
        $b = Brand::factory()->create();
        Product::factory()->create(['brand_id' => $a->id]);
        Product::factory()->create(['brand_id' => $a->id]);
        Product::factory()->create(['brand_id' => $b->id]);

        $response = $this->getJson('/api/catalog/facets')->assertOk();

        $this->assertArrayHasKey('brands', $response->json('data'));
        $this->assertArrayHasKey('categories', $response->json('data'));
        $this->assertArrayHasKey('colors', $response->json('data'));
        $this->assertArrayHasKey('sizes', $response->json('data'));

        $brand = collect($response->json('data.brands'))->firstWhere('slug', $a->slug);
        $this->assertSame(2, $brand['count']);
    }

    public function test_product_detail_exposes_variants_and_gallery(): void
    {
        $product = Product::factory()->withVariant(price: 25.99, stock: 4)->create();

        $this->getJson("/api/catalog/products/{$product->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $product->slug)
            ->assertJsonCount(1, 'data.variants')
            ->assertJsonStructure(['data' => ['variants', 'gallery', 'attributes']]);
    }

    public function test_product_detail_404_for_unknown_slug(): void
    {
        $this->getJson('/api/catalog/products/does-not-exist')->assertStatus(404);
    }

    public function test_categories_and_brands_index(): void
    {
        Category::factory()->create();
        Brand::factory()->create();

        $this->getJson('/api/categories')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/brands')->assertOk()->assertJsonCount(1, 'data');
    }
}