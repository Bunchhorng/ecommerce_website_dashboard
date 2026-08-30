<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\Request;

class AdminCouponController extends Controller
{
    public function index(Request $request)
    {
        $paginator = Coupon::orderByDesc('id')->paginate(15);

        return [
            'data' => CouponResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function store(AdminCouponRequest $request)
    {
        $coupon = Coupon::create($request->validated());

        return (new CouponResource($coupon))->response()->setStatusCode(201);
    }

    public function update(AdminCouponRequest $request, Coupon $coupon)
    {
        $coupon->update($request->validated());

        return new CouponResource($coupon);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json(['data' => ['message' => 'Coupon deleted.']]);
    }
}
