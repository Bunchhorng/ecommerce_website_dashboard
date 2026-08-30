<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Services\CatalogService;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(protected CatalogService $catalog)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'q', 'category', 'brand', 'colors', 'sizes', 'min', 'max',
            'rating', 'stock', 'sort', 'page', 'perPage',
        ]);

        $products = $this->catalog->filtered($filters);

        return [
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ];
    }

    public function show(string $slug)
    {
        $product = $this->catalog->findBySlug($slug);

        if ($product === null) {
            abort(404, 'Product not found.');
        }

        return new ProductDetailResource($product);
    }

    public function featured(Request $request)
    {
        $limit = (int) $request->query('limit', 8);

        return ProductResource::collection($this->catalog->featured($limit));
    }

    public function facets()
    {
        return ['data' => $this->catalog->facets()];
    }
}
