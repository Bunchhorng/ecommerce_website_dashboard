<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Mirror the frontend COUPONS / ADMIN_COUPONS mock.
     */
    public function run(): void
    {
        $coupons = [
            ['WELCOME10', 'percentage', 10, 50, 5000, 1, 3821, '2026-12-31 23:59:59', true],
            ['SAVE20', 'fixed', 20, 100, 2000, 1, 1404, '2026-11-30 23:59:59', true],
            ['SUMMER25', 'percentage', 25, 150, 1000, 1, 1000, '2026-08-31 23:59:59', false],
            ['FREESHIP', 'fixed', 10, 75, 3000, 1, 2211, '2026-12-31 23:59:59', true],
            ['VIP15', 'percentage', 15, 200, 500, 1, 87, '2027-03-31 23:59:59', true],
        ];

        foreach ($coupons as [$code, $type, $value, $min, $limit, $perUser, $used, $expires, $active]) {
            Coupon::create([
                'code' => $code,
                'type' => $type,
                'value' => $value,
                'min_order_amount' => $min,
                'max_discount_amount' => null,
                'usage_limit' => $limit,
                'per_user_limit' => $perUser,
                'used_count' => $used,
                'starts_at' => null,
                'expires_at' => $expires,
                'is_active' => $active,
            ]);
        }
    }
}