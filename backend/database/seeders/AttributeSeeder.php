<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Color + Size attributes and the values used across the catalog's variants.
     */
    public function run(): void
    {
        $colors = [
            'Black' => '#111827',
            'White' => '#FFFFFF',
            'Silver' => '#D1D5DB',
            'Midnight' => '#0F172A',
            'Sand' => '#E7D8C9',
            'Rose' => '#F43F5E',
            'Green' => '#166534',
            'Navy' => '#1E3A8A',
            'Charcoal' => '#374151',
            'Beige' => '#EAD9C8',
            'Ocean' => '#0C4A6E',
            'Tan' => '#B45309',
            'Oak' => '#92400E',
        ];

        $sizes = ['US 9', 'US 10', 'US 11', 'S', 'M', 'L', '30 ml'];

        $color = Attribute::create([
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'color',
            'is_filterable' => true,
        ]);

        foreach ($colors as $value => $swatch) {
            AttributeValue::create([
                'attribute_id' => $color->id,
                'value' => $value,
                'swatch_color' => $swatch,
            ]);
        }

        $size = Attribute::create([
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'size',
            'is_filterable' => true,
        ]);

        foreach ($sizes as $value) {
            AttributeValue::create([
                'attribute_id' => $size->id,
                'value' => $value,
                'swatch_color' => null,
            ]);
        }
    }
}