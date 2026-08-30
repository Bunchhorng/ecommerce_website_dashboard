<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CouponValidateRequest;
use App\Http\Resources\CouponResource;
use App\Services\CouponService;

class CouponController extends Controller
{
    public function __construct(protected CouponService $coupons)
    {
    }

    public function validate(CouponValidateRequest $request)
    {
        $coupon = $this->coupons->validate(
            $request->user(),
            $request->input('code'),
            (float) $request->input('subtotal')
        );

        return (new CouponResource($coupon))
            ->additional(['subtotal' => $request->input('subtotal')]);
    }
}
