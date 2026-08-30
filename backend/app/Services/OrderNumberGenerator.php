<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;

class OrderNumberGenerator
{
    /**
     * Generate a unique order number. Collisions are left to the caller to retry once.
     */
    public static function next(): string
    {
        $count = (int) Order::withTrashed()->count();

        return 'SV-'.date('Y').'-'.str_pad((string) ($count + random_int(1, 999)), 6, '0', STR_PAD_LEFT);
    }
}
