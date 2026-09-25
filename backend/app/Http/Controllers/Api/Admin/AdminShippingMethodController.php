<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminShippingMethodRequest;
use App\Http\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminShippingMethodController extends Controller
{
    public function index(Request $request)
    {
        return ShippingMethodResource::collection(ShippingMethod::orderBy('price')->get());
    }

    public function store(AdminShippingMethodRequest $request)
    {
        $method = ShippingMethod::create($request->validated());
        Cache::forget('shipping_methods:active');

        return (new ShippingMethodResource($method))->response()->setStatusCode(201);
    }

    public function update(AdminShippingMethodRequest $request, ShippingMethod $method)
    {
        $method->update($request->validated());
        Cache::forget('shipping_methods:active');

        return new ShippingMethodResource($method);
    }

    public function destroy(ShippingMethod $method)
    {
        $method->delete();
        Cache::forget('shipping_methods:active');

        return response()->json(['data' => ['message' => 'Shipping method deleted.']]);
    }
}
