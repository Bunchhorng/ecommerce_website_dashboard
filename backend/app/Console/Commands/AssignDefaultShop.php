<?php

namespace App\Console\Commands;

use App\Models\Shop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssignDefaultShop extends Command
{
    protected $signature = 'shop:assign-default
                            {--name=Main Store : Name for the default shop}
                            {--slug=main : Slug for the default shop}
                            {--code=MAIN : Short code for the default shop}';

    protected $description = 'Create (if needed) a default shop and assign existing shop-less rows to it (non-destructive)';

    public function handle(): int
    {
        $shop = Shop::firstOrCreate(
            ['slug' => $this->option('slug')],
            [
                'name' => $this->option('name'),
                'code' => $this->option('code'),
                'status' => Shop::STATUS_ACTIVE,
                'is_default' => true,
                'country' => 'KH',
            ],
        );

        // If a different shop already held is_default, this one now takes over.
        Shop::where('id', '!=', $shop->id)->update(['is_default' => false]);
        $shop->update(['is_default' => true]);

        $this->info("Default shop: #{$shop->id} {$shop->name} (slug={$shop->slug})");

        $counts = [
            'products' => DB::table('products')->whereNull('shop_id')->update(['shop_id' => $shop->id]),
            'inventories' => DB::table('inventories')->whereNull('shop_id')->update(['shop_id' => $shop->id]),
            'inventory_transactions' => DB::table('inventory_transactions')->whereNull('shop_id')->update(['shop_id' => $shop->id]),
            'order_items' => DB::table('order_items')->whereNull('shop_id')->update(['shop_id' => $shop->id]),
        ];

        foreach ($counts as $table => $updated) {
            $this->line("  {$table}: {$updated} row(s) assigned to shop #{$shop->id}");
        }

        $this->info('Done. No records were deleted.');

        return self::SUCCESS;
    }
}
