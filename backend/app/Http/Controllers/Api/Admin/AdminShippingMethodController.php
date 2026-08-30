<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminShippingMethodRequest;
use App\Http\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class AdminShippingMethodController extends Controller
{
    public function index(Request $request)
    {
        return ShippingMethodResource::collection(ShippingMethod::orderBy('price')->get());
    }

    public function store(AdminShippingMethodRequest $request)
    {
        $method = ShippingMethod::create($request->validated());

        return (new ShippingMethodResource($method))->response()->setStatusCode(201);
    }

    public function update(AdminShippingMethodRequest $request, ShippingMethod $method)
    {
        $method->update($request->validated());

        return new ShippingMethodResource($method);
    }

    public function destroy(ShippingMethod $method)
    {
        $method->delete();

        return response()->json(['data' => ['message' => 'Shipping method deleted.']]);
    }
}
