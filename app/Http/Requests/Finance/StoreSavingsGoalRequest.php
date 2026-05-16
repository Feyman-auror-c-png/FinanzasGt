<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'saved_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'target_date' => ['required', 'date_format:Y-m-d'],
            'color' => ['required', 'string', 'max:20'],
        ];
    }
}
