<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'is_default' => (bool) $this->is_default,
        ];
    }

    public static function fromArray(array $snapshot): array
    {
        return [
            'full_name' => $snapshot['full_name'] ?? null,
            'phone' => $snapshot['phone'] ?? null,
            'address_line1' => $snapshot['address_line1'] ?? null,
            'address_line2' => $snapshot['address_line2'] ?? null,
            'city' => $snapshot['city'] ?? null,
            'state' => $snapshot['state'] ?? null,
            'postal_code' => $snapshot['postal_code'] ?? null,
            'country' => $snapshot['country'] ?? null,
        ];
    }
}
