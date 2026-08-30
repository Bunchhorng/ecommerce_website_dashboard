<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,
            'parent_id' => $this->parent_id,
            'children' => $this->whenLoaded('children', function () {
                return self::collection($this->children->filter(fn ($child) => $child->is_active)->values());
            }),
        ];
    }
}
