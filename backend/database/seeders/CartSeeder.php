<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * A sample persisted cart for one customer and one anonymous session.
     */
    public function run(): void
    {
        $cart = Cart::create(['user_id' => User::where('email', 'marcus.lee@example.com')->value('id')]);
        $cartItems = [
            'aurora-white' => 2,
            'aerolite-wh-42' => 1,
        ];

        foreach ($cartItems as $sku => $qty) {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_variant_id' => ProductVariant::where('sku', $sku)->value('id'),
                'quantity' => $qty,
            ]);
        }

        $guest = Cart::create(['user_id' => null, 'session_id' => 'guest-9f8e7d6c5b']);
        CartItem::create([
            'cart_id' => $guest->id,
            'product_variant_id' => ProductVariant::where('sku', 'ruby-01')->value('id'),
            'quantity' => 1,
        ]);
    }
}