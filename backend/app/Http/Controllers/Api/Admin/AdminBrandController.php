<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminBrandController extends Controller
{
    public function index(Request $request)
    {
        return BrandResource::collection(Brand::withCount('products')->orderBy('name')->get());
    }

    public function store(AdminBrandRequest $request)
    {
        $brand = Brand::create($request->validated());
        Cache::forget('brands:active');

        return (new BrandResource($brand))->response()->setStatusCode(201);
    }

    public function update(AdminBrandRequest $request, Brand $brand)
    {
        $brand->update($request->validated());
        Cache::forget('brands:active');

        return new BrandResource($brand);
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        Cache::forget('brands:active');

        return response()->json(['data' => ['message' => 'Brand deleted.']]);
    }
}
