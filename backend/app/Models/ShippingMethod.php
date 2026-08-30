<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'code', 'description', 'price', 'estimated_days_min', 'estimated_days_max', 'is_active'])]
class ShippingMethod extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}