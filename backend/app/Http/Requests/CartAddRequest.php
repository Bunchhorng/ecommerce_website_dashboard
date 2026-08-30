<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['integer', 'min:1', 'max:99'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('quantity')) {
            $this->merge(['quantity' => 1]);
        }
    }
}
