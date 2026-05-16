<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'bank' => ['required', 'string', 'max:255'],
            'credit_limit' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'due_day' => ['required', 'integer', 'min:1', 'max:31'],
        ];
    }
}
