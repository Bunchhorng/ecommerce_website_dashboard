<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->shops()->exists();
    }

    public function view(User $user, Shop $shop): bool
    {
        return $user->belongsToShop($shop);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only a branch manager may update the branch profile/status.
     */
    public function update(User $user, Shop $shop): bool
    {
        return $user->ownsShop($shop);
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $user->isAdmin();
    }
}
