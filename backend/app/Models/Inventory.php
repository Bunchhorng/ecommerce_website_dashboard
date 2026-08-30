<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_variant_id', 'quantity', 'reserved_quantity', 'low_stock_threshold', 'sold_count'])]
class Inventory extends Model
{
    use HasFactory;
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max($this->quantity - $this->reserved_quantity, 0);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->available_quantity <= $this->low_stock_threshold;
    }
}