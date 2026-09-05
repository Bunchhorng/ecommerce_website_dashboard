<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_path' => $this->image_path,
            'alt_text' => $this->alt_text,
            'sort_order' => (int) $this->sort_order,
            'is_cover' => (bool) $this->is_cover,
        ];
    }
}