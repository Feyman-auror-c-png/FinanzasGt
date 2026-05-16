<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'merchant' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
