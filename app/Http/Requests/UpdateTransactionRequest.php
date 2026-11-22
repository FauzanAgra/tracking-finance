<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Transaction|null $transaction */
        $transaction = $this->route('transaction');

        return $transaction !== null && $transaction->user_id === $this->user()?->id;
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
            'type' => ['required', 'string', Rule::in(['income', 'expense', 'transfer', 'currency_exchange'])],
            'wallet_id' => [
                'required',
                'integer',
                Rule::exists('wallets', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'destination_wallet_id' => [
                'nullable',
                'required_if:type,transfer,currency_exchange',
                'integer',
                'different:wallet_id',
                Rule::exists('wallets', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'destination_amount' => [
                'nullable',
                'required_if:type,currency_exchange',
                'numeric',
                'gt:0',
            ],
            'category_id' => [
                'nullable',
                'required_if:type,income,expense',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'amount' => ['required', 'numeric', 'gt:0'],
            'fee' => ['nullable', 'numeric', 'min:0'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
