<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvestmentTradeRequest extends FormRequest
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
        $userId = $this->user()->id;

        return [
            'asset_type' => ['required', 'string', Rule::in(['idx_stock', 'us_stock', 'crypto'])],
            'symbol' => ['required', 'string', 'max:30'],
            'name' => ['nullable', 'string', 'max:150'],
            'currency' => ['nullable', 'string', Rule::in(['IDR', 'USD', 'USDT'])],
            'type' => ['required', 'string', Rule::in(['buy', 'sell', 'dividend'])],
            'lots' => ['nullable', 'required_if:asset_type,idx_stock', 'numeric', 'gt:0'],
            'quantity' => ['nullable', 'required_unless:asset_type,idx_stock', 'numeric', 'gt:0'],
            'price_per_unit' => ['required', 'numeric', 'gt:0'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'wallet_id' => [
                'nullable',
                'integer',
                Rule::exists('wallets', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'transaction_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
