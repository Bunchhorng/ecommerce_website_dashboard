<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Explicit policy registration for the shop/branch authorization layer.
        // (Single-brand, multi-branch model: super admins manage every branch;
        //  staff are scoped to the branch they belong to.)
        Gate::policy(\App\Models\Shop::class, \App\Policies\ShopPolicy::class);
        Gate::policy(\App\Models\Product::class, \App\Policies\ProductPolicy::class);
        Gate::policy(\App\Models\Inventory::class, \App\Policies\InventoryPolicy::class);
        Gate::policy(\App\Models\OrderItem::class, \App\Policies\OrderItemPolicy::class);

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

            return $frontendUrl.'/auth/reset-password'
                .'?token='.$token
                .'&email='.urlencode($notifiable->getEmailForPasswordReset());
        });
    }
}
