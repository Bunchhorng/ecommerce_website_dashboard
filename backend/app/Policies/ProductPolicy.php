<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Shop;
use App\Models\User;

class ProductPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Only a manager/owner of the product's branch may edit a product.
     */
    public function update(User $user, Product $product): bool
    {
        $shopId = $product->shop_id;

        if ($shopId === null) {
            return $user->isAdmin();
        }

        return $user->ownsShop(Shop::findOrFail($shopId));
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }

    public function restore(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}
