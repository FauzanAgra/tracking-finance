<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDebtRequest extends FormRequest
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
            'person_name' => ['required', 'string', 'max:150'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'type' => ['required', 'string', Rule::in(['receivable', 'payable'])],
            'currency' => ['required', 'string', Rule::in(['IDR', 'USD', 'USDT'])],
            'total_amount' => ['required', 'numeric', 'gt:0'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
