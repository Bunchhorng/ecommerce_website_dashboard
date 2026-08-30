<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')
            ->with(['children' => function ($q) {
                $q->withCount('products')->with('children');
            }])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(AdminCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function update(AdminCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Contains sub-categories; delete those first.',
            ], 422);
        }

        $category->delete();

        return response()->json(['data' => ['message' => 'Category deleted.']]);
    }
}
