<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;

class ShippingMethodController extends Controller
{
    public function index()
    {
        return ShippingMethodResource::collection(
            ShippingMethod::where('is_active', true)->orderBy('price')->get()
        );
    }
}
