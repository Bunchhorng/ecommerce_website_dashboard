<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id', 'image_path', 'alt_text', 'sort_order', 'is_cover'])]
class ProductImage extends Model
{
    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'image_id');
    }
}