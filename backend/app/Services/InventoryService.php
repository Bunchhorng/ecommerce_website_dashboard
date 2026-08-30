<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Get the currently available quantity for a variant (never negative).
     */
    public function available(int $variantId): int
    {
        $inventory = Inventory::firstOrCreate(
            ['product_variant_id' => $variantId],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );

        return max((int) $inventory->quantity - (int) $inventory->reserved_quantity, 0);
    }

    /**
     * Atomically reserve stock for a single variant.
     */
    public function reserve(int $variantId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return true;
        }

        return DB::transaction(function () use ($variantId, $quantity): bool {
            $inventory = Inventory::where('product_variant_id', $variantId)->lockForUpdate()->first();

            if ($inventory === null) {
                $inventory = Inventory::create([
                    'product_variant_id' => $variantId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            $available = (int) $inventory->quantity - (int) $inventory->reserved_quantity;

            if ($available < $quantity) {
                return false;
            }

            $inventory->reserved_quantity = (int) $inventory->reserved_quantity + $quantity;
            $inventory->save();

            $this->log($inventory, 'reserve', $quantity, $available, 'VARIANT:'.$variantId, 'Checkout reservation');

            return true;
        });
    }

    /**
     * Atomically reserve stock for several variants at once; never partial-reserve.
     */
    public function reserveMany(array $items): bool
    {
        return DB::transaction(function () use ($items): bool {
            foreach ($items as $variantId => $quantity) {
                if (! $this->reserve((int) $variantId, (int) $quantity)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Return previously reserved stock back to the pool.
     */
    public function release(int $variantId, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($variantId, $quantity): void {
            $inventory = Inventory::where('product_variant_id', $variantId)->lockForUpdate()->first();

            if ($inventory === null) {
                return;
            }

            $inventory->reserved_quantity = max((int) $inventory->reserved_quantity - $quantity, 0);
            $inventory->save();

            $this->log($inventory, 'release', $quantity, (int) $inventory->quantity - (int) $inventory->reserved_quantity, 'VARIANT:'.$variantId, 'Reservation released');
        });
    }

    /**
     * Release reserved stock for several variants at once.
     */
    public function releaseMany(array $items): void
    {
        DB::transaction(function () use ($items): void {
            foreach ($items as $variantId => $quantity) {
                $this->release((int) $variantId, (int) $quantity);
            }
        });
    }

    /**
     * Permanently deduct stock on successful payment completion.
     */
    public function deduct(int $variantId, int $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($variantId, $quantity): void {
            $inventory = Inventory::where('product_variant_id', $variantId)->lockForUpdate()->first();

            if ($inventory === null) {
                return;
            }

            $inventory->quantity = max((int) $inventory->quantity - $quantity, 0);
            $inventory->reserved_quantity = max((int) $inventory->reserved_quantity - $quantity, 0);
            $inventory->sold_count = (int) $inventory->sold_count + $quantity;
            $inventory->save();

            $balanceAfter = (int) $inventory->quantity - (int) $inventory->reserved_quantity;
            $this->log($inventory, 'deduct', $quantity, $balanceAfter, 'VARIANT:'.$variantId, 'Order payment confirmed');
        });
    }

    /**
     * Permanently deduct reserved stock for several variants at once.
     */
    public function deductMany(array $items): void
    {
        DB::transaction(function () use ($items): void {
            foreach ($items as $variantId => $quantity) {
                $this->deduct((int) $variantId, (int) $quantity);
            }
        });
    }

    /**
     * Adjust total stock up or down for a variant.
     */
    public function adjust(int $variantId, int $newQuantity): void
    {
        DB::transaction(function () use ($variantId, $newQuantity): void {
            $inventory = Inventory::where('product_variant_id', $variantId)->lockForUpdate()->first();

            if ($inventory === null) {
                $inventory = Inventory::create([
                    'product_variant_id' => $variantId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            $current = (int) $inventory->quantity;
            $delta = $newQuantity - $current;

            $inventory->quantity = max($newQuantity, 0);
            $inventory->save();

            $this->log($inventory, 'adjust', $delta, (int) $inventory->quantity, 'VARIANT:'.$variantId, 'Stock adjusted');
        });
    }

    private function log(Inventory $inventory, string $type, int $quantity, int $balanceAfter, ?string $reference, ?string $note): void
    {
        InventoryTransaction::create([
            'inventory_id' => $inventory->id,
            'type' => $type,
            'quantity' => $quantity,
            'balance_after' => $balanceAfter,
            'reference' => $reference,
            'note' => $note,
        ]);
    }
}
