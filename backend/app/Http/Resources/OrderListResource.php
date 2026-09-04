<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $itemsCount = $this->relationLoaded('items')
            ? $this->items->sum('quantity')
            : ($this->order_items_count ?? null);

        return [
            'id' => (int) $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'total' => (float) $this->total,
            'placed_at' => $this->placed_at?->toISOString(),
            'items_count' => $itemsCount,
        ];
    }
}
