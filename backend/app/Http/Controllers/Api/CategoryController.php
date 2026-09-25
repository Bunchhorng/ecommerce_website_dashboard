<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        return ['data' => Cache::remember('categories:tree', 86400, function () {
            $categories = Category::with(['children' => function ($q) {
                $q->with('children');
            }])
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return CategoryResource::collection($categories)->resolve();
        })];
    }
}
