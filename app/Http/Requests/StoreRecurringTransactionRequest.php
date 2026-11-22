<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurringTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'type' => ['required', 'string', Rule::in(['expense', 'income', 'transfer'])],
            'wallet_id' => [
                'required',
                'integer',
                Rule::exists('wallets', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'destination_wallet_id' => [
                'nullable',
                'required_if:type,transfer',
                'integer',
                'different:wallet_id',
                Rule::exists('wallets', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'category_id' => [
                'nullable',
                'required_if:type,expense,income',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'frequency' => ['required', 'string', Rule::in(['daily', 'weekly', 'monthly'])],
            'start_date' => ['required', 'date'],
            'next_run_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
