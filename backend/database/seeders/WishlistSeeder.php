<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    /**
     * A wishlist per customer with a few saved products.
     */
    public function run(): void
    {
        $wishes = [
            'olivia.bennett@example.com' => ['pulse-smartwatch-pro', 'meridian-slim-fit-blazer', 'serene-wooden-desk-lamp'],
            'marcus.lee@example.com' => ['aurora-wireless-headphones', 'orbit-everyday-backpack', 'nova-linen-shirt', 'vertex-4k-action-camera'],
            'priya.shah@example.com' => ['lumiere-hydra-glow-serum', 'cascade-leather-tote'],
            'jake.miller@example.com' => ['aerolite-running-shoes', 'stride-court-sneakers', 'orbit-everyday-backpack'],
            'elena.rodriguez@example.com' => ['velvet-matte-lipstick-ruby', 'aurora-wireless-headphones'],
            'dan.okafor@example.com' => ['pulse-smartwatch-pro', 'cascade-leather-tote', 'serene-wooden-desk-lamp'],
        ];

        foreach (User::where('role', User::ROLE_CUSTOMER)->get() as $user) {
            $wishlist = Wishlist::create(['user_id' => $user->id]);
            $products = \App\Models\Product::whereIn('slug', array_values($wishes[$user->email] ?? []))->get();

            foreach ($products as $product) {
                WishlistItem::firstOrCreate([
                    'wishlist_id' => $wishlist->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}