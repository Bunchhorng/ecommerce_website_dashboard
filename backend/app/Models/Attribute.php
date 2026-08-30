<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'type', 'is_filterable'])]
class Attribute extends Model
{
    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
        ];
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}