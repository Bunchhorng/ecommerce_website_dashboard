<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function index()
    {
        return ['data' => Cache::remember('brands:active', 86400, function () {
            return BrandResource::collection(
                Brand::where('is_active', true)->orderBy('name')->get()
            )->resolve();
        })];
    }
}
