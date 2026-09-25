<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\Shop;
use App\Models\User;

class OrderItemPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Branch staff may only see order lines fulfilled by their own branch.
     */
    public function view(User $user, OrderItem $orderItem): bool
    {
        if ($orderItem->shop_id === null) {
            return $user->isAdmin();
        }

        return $user->belongsToShop(Shop::findOrFail($orderItem->shop_id));
    }
}
