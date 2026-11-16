<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'currency' => ['required', 'string', Rule::in(['IDR', 'USD', 'USDT'])],
            'target_amount' => ['required', 'numeric', 'gt:0'],
            'target_date' => ['nullable', 'date'],
            'icon' => ['nullable', 'string', 'max:50'],
        ];
    }
}
