<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CatalogService
{
    public function filtered(array $filters): LengthAwarePaginator
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with(['brand', 'category', 'images', 'variants.inventory', 'variants.attributeValues.value.attribute']);

        if (!empty($filters['q'])) {
            $term = mb_strtolower(trim((string) $filters['q']));
            $query->where(function (Builder $q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(short_description) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$term}%"]);
            });
        }

        if (!empty($filters['category'])) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', (string) $filters['category']));
        }

        if (!empty($filters['brand'])) {
            $query->whereHas('brand', fn (Builder $q) => $q->where('slug', (string) $filters['brand']));
        }

        if (isset($filters['min']) && $filters['min'] !== '' && $filters['min'] !== null) {
            $query->where('price', '>=', (float) $filters['min']);
        }

        if (isset($filters['max']) && $filters['max'] !== '' && $filters['max'] !== null) {
            $query->where('price', '<=', (float) $filters['max']);
        }

        if (isset($filters['rating']) && (int) $filters['rating'] > 0) {
            $query->where('rating_avg', '>=', (float) $filters['rating']);
        }

        $colors = $this->normaliseList($filters['colors'] ?? null);
        $sizes = $this->normaliseList($filters['sizes'] ?? null);

        if (!empty($colors)) {
            $query->where(function (Builder $q) use ($colors) {
                foreach ($colors as $value) {
                    $q->orWhereHas('variants.attributeValues.value', function (Builder $vq) use ($value) {
                        $vq->where('value', 'LIKE', "%{$value}%");
                    });
                }
            });
        }

        if (!empty($sizes)) {
            $query->where(function (Builder $q) use ($sizes) {
                foreach ($sizes as $value) {
                    $q->orWhereHas('variants.attributeValues.value', function (Builder $vq) use ($value) {
                        $vq->where('value', 'LIKE', "%{$value}%");
                    });
                }
            });
        }

        if (!empty($filters['stock']) && (bool) $filters['stock']) {
            $query->whereHas('variants', function (Builder $q) {
                $q->where('is_active', true)
                    ->whereHas('inventory', function (Builder $iq) {
                        $iq->whereRaw('quantity - reserved_quantity > 0');
                    });
            });
        }

        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price-asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price-desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating_avg', 'desc')->orderBy('rating_count', 'desc');
                break;
            case 'popularity':
                $query->orderBy('rating_count', 'desc');
                break;
            default:
                $query->orderByDesc('id');
        }

        $perPage = (int) ($filters['perPage'] ?? 12);
        $page = (int) ($filters['page'] ?? 1);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::with([
            'brand',
            'category',
            'images',
            'variants' => fn ($q) => $q->with(['inventory', 'attributeValues.value.attribute']),
        ])->where('is_active', true)->where('slug', $slug)->first();
    }

    public function featured(int $limit = 8): Collection
    {
        return Product::with(['brand', 'category', 'images', 'variants.inventory'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function facets(): array
    {
        $brands = \App\Models\Brand::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->map(fn ($brand) => [
                'slug' => $brand->slug,
                'name' => $brand->name,
                'count' => $brand->products_count,
            ])->all();

        $categories = \App\Models\Category::query()
            ->where('is_active', true)
            ->withCount('products')
            ->orderBy('name')
            ->get()
            ->map(fn ($cat) => [
                'slug' => $cat->slug,
                'name' => $cat->name,
                'count' => $cat->products_count,
            ])->all();

        $colors = $this->attributeFacet('color');
        $sizes = $this->attributeFacet('size');

        return [
            'brands' => $brands,
            'categories' => $categories,
            'colors' => $colors,
            'sizes' => $sizes,
        ];
    }

    public function search(string $term)
    {
        return $this->filtered(['q' => $term, 'perPage' => 20]);
    }

    protected function normaliseList($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(fn ($v) => trim((string) $v), $value), fn ($v) => $v !== ''));
        }

        if (is_string($value) && $value !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== ''));
        }

        return [];
    }

    protected function attributeFacet(string $slug): array
    {
        $attribute = Attribute::with(['values.variants'])->where('slug', $slug)->first();
        if ($attribute === null) {
            return [];
        }

        return $attribute->values
            ->map(fn ($value) => [
                'slug' => \Illuminate\Support\Str::slug($value->value),
                'name' => $value->value,
                'count' => $value->variants->count(),
            ])
            ->values()
            ->all();
    }
}
