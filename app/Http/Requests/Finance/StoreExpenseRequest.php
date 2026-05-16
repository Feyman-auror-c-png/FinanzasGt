<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'category' => ['required', Rule::in(['food', 'transport', 'education', 'rent', 'health', 'entertainment', 'clothing', 'services', 'other'])],
            'note' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
