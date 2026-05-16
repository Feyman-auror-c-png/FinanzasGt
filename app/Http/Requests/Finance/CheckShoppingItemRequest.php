<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class CheckShoppingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }
}
