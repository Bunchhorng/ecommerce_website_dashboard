<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProductRequest;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category', 'images', 'variants.inventory']);

        if ($request->filled('q')) {
            $term = mb_strtolower(trim((string) $request->q));
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$term}%"]);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', (int) $request->brand_id);
        }

        if ($request->filled('stock_status') && $request->stock_status !== 'all') {
            if ($request->stock_status === 'in') {
                $query->whereHas('variants', fn ($q) => $q->where('is_active', true)
                    ->whereHas('inventory', fn ($i) => $i->whereRaw('quantity - reserved_quantity > 0')));
            } elseif ($request->stock_status === 'out') {
                $query->whereDoesntHave('variants', fn ($q) => $q->where('is_active', true)
                    ->whereHas('inventory', fn ($i) => $i->whereRaw('quantity - reserved_quantity > 0')));
            }
        }

        $paginator = $query->orderByDesc('id')->paginate(15);

        return [
            'data' => ProductResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function store(AdminProductRequest $request)
    {
        $data = $this->productData($request);

        $product = Product::create($data);

        if ($request->filled('variants')) {
            $this->createVariants($product, $request->input('variants'));
        }

        return (new ProductDetailResource(
            $this->loadDetail($product)
        ))->response()->setStatusCode(201);
    }

    public function show(Product $product)
    {
        return new ProductDetailResource($this->loadDetail($product));
    }

    public function update(AdminProductRequest $request, Product $product)
    {
        $data = $this->productData($request);

        if (isset($data['slug']) && $data['slug'] !== $product->slug) {
            $data['slug'] = $this->uniqueSlug(Product::class, $data['slug'], $product->id);
        }

        $product->update($data);

        if ($request->has('variants')) {
            $this->syncVariants($product, $request->input('variants') ?? []);
        }

        return new ProductDetailResource($this->loadDetail($product));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['data' => ['message' => 'Product deleted.']]);
    }

    protected function productData(AdminProductRequest $request): array
    {
        $data = $request->only([
            'name', 'slug', 'category_id', 'brand_id', 'short_description',
            'description', 'price', 'compare_at_price', 'sku', 'weight',
            'is_featured', 'is_active',
        ]);

        foreach ([
            'is_featured' => false,
            'is_active' => true,
            'price' => 0,
        ] as $key => $default) {
            if (!array_key_exists($key, $data) || $data[$key] === null) {
                $data[$key] = $default;
            }
        }

        if (!empty($data['is_featured'])) {
            $data['is_featured'] = true;
        }
        if (!empty($data['is_active'])) {
            $data['is_active'] = true;
        }

        return $data;
    }

    protected function createVariants(Product $product, array $variants): void
    {
        foreach ($variants as $variantData) {
            $variant = $product->variants()->create([
                'name' => $variantData['name'] ?? null,
                'sku' => $variantData['sku'] ?? null,
                'price' => $variantData['price'] ?? null,
                'compare_at_price' => $variantData['compare_at_price'] ?? null,
                'is_active' => $variantData['is_active'] ?? true,
            ]);

            Inventory::create([
                'product_variant_id' => $variant->id,
                'quantity' => (int) ($variantData['quantity'] ?? 0),
                'reserved_quantity' => 0,
                'low_stock_threshold' => 5,
            ]);

            $this->attachAttributes($variant, $variantData['attributes'] ?? []);
        }
    }

    protected function syncVariants(Product $product, array $variants): void
    {
        $existing = $product->variants()->get();
        $referencedIds = [];

        foreach ($variants as $variantData) {
            $variant = null;

            if (!empty($variantData['id'])) {
                $variant = $existing->firstWhere('id', (int) $variantData['id']);
            }

            if ($variant === null && !empty($variantData['sku'])) {
                $variant = $existing->first(fn ($v) => $v->sku === $variantData['sku']);
            }

            if ($variant === null) {
                $variant = $product->variants()->create([
                    'name' => $variantData['name'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'price' => $variantData['price'] ?? null,
                    'compare_at_price' => $variantData['compare_at_price'] ?? null,
                    'is_active' => true,
                ]);

                Inventory::firstOrCreate(
                    ['product_variant_id' => $variant->id],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'low_stock_threshold' => 5]
                );
            } else {
                $variant->update([
                    'name' => $variantData['name'] ?? $variant->name,
                    'sku' => $variantData['sku'] ?? $variant->sku,
                    'price' => array_key_exists('price', $variantData) ? $variantData['price'] : $variant->price,
                    'compare_at_price' => array_key_exists('compare_at_price', $variantData) ? $variantData['compare_at_price'] : $variant->compare_at_price,
                    'is_active' => $variantData['is_active'] ?? $variant->is_active,
                ]);

                if (array_key_exists('quantity', $variantData)) {
                    Inventory::updateOrCreate(
                        ['product_variant_id' => $variant->id],
                        ['quantity' => (int) $variantData['quantity']],
                    );
                }
            }

            $referencedIds[] = $variant->id;

            if (array_key_exists('attributes', $variantData)) {
                VariantAttributeValue::where('product_variant_id', $variant->id)->delete();
                $this->attachAttributes($variant, $variantData['attributes'] ?? []);
            }
        }

        foreach ($existing as $variant) {
            if (!in_array($variant->id, $referencedIds, true)) {
                $variant->delete();
            }
        }
    }

    protected function attachAttributes(ProductVariant $variant, array $attributes): void
    {
        foreach ($attributes as $entry) {
            $attributeName = $entry['attribute'] ?? null;
            $attributeValue = $entry['value'] ?? null;

            if ($attributeName === null || $attributeValue === null) {
                continue;
            }

            $attribute = Attribute::where('slug', Str::slug($attributeName))
                ->orWhere('name', $attributeName)
                ->first();

            if ($attribute === null) {
                $attribute = Attribute::create([
                    'name' => (string) $attributeName,
                    'slug' => Str::slug((string) $attributeName),
                    'type' => 'text',
                    'is_filterable' => false,
                ]);
            }

            $value = AttributeValue::firstOrCreate(
                ['attribute_id' => $attribute->id, 'value' => (string) $attributeValue],
                ['swatch_color' => null]
            );

            VariantAttributeValue::firstOrCreate([
                'product_variant_id' => $variant->id,
                'attribute_value_id' => $value->id,
            ]);
        }
    }

    protected function loadDetail(Product $product): Product
    {
        return $product->load([
            'brand',
            'category',
            'images',
            'variants' => fn ($q) => $q->with(['inventory', 'attributeValues.value.attribute']),
        ]);
    }

    protected function uniqueSlug(string $model, string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $suffix = 1;

        while ($model::where('slug', $slug)
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
