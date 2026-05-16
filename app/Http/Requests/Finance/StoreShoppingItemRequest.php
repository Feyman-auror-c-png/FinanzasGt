<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShoppingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'estimated_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'category' => ['required', Rule::in(['produce', 'dairy', 'meat', 'pantry', 'cleaning', 'other'])],
        ];
    }
}
