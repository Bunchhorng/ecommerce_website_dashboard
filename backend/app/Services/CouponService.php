<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function validate(?User $user, string $code, float $subtotal): Coupon
    {
        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [mb_strtoupper(trim($code))])
            ->first();

        if ($coupon === null || !$coupon->is_active) {
            throw ValidationException::withMessages([
                'code' => ['This coupon is not valid.'],
            ]);
        }

        if ($coupon->expires_at !== null && !$coupon->expires_at->isFuture()) {
            throw ValidationException::withMessages([
                'code' => ['This coupon has expired.'],
            ]);
        }

        if ($coupon->starts_at !== null && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'code' => ['This coupon is not yet active.'],
            ]);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages([
                'code' => ['This coupon has reached its usage limit.'],
            ]);
        }

        if ($coupon->min_order_amount !== null && (float) $coupon->min_order_amount > $subtotal) {
            throw ValidationException::withMessages([
                'code' => ['This coupon requires a minimum order amount of ' . number_format((float) $coupon->min_order_amount, 2) . '.'],
            ]);
        }

        if ($user !== null && $coupon->per_user_limit !== null) {
            $used = $coupon->usages()->where('user_id', $user->id)->count();
            if ($used >= $coupon->per_user_limit) {
                throw ValidationException::withMessages([
                    'code' => ['You have already used this coupon.'],
                ]);
            }
        }

        return $coupon;
    }

    public function discountFor(Coupon $coupon, float $subtotal): float
    {
        if ($coupon->type === 'percentage') {
            $discount = round($subtotal * (float) $coupon->value / 100, 2);
            if ($coupon->max_discount_amount !== null) {
                $discount = min($discount, (float) $coupon->max_discount_amount);
            }

            return min($discount, $subtotal);
        }

        return min((float) $coupon->value, $subtotal);
    }

    public function applyUsage(Coupon $coupon, Order $order, ?User $user): void
    {
        $coupon->increment('used_count');

        $coupon->usages()->create([
            'coupon_id' => $coupon->id,
            'user_id' => $user?->id,
            'order_id' => $order->id,
            'redeemed_at' => now(),
        ]);
    }
}
