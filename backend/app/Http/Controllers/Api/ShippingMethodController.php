<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Cache;

class ShippingMethodController extends Controller
{
    public function index()
    {
        return ['data' => Cache::remember('shipping_methods:active', 86400, function () {
            return ShippingMethodResource::collection(
                ShippingMethod::where('is_active', true)->orderBy('price')->get()
            )->resolve();
        })];
    }
}
