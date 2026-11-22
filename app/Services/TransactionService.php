<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use DomainException;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(
        private readonly WalletService $walletService,
    ) {}

    /**
     * Record an income transaction and credit the target wallet atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordIncome(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data): Transaction {
            $wallet = Wallet::query()
                ->where('user_id', $user->id)
                ->findOrFail($data['wallet_id']);

            $this->walletService->credit($wallet, $data['amount']);

            return Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'category_id' => $data['category_id'] ?? null,
                'type' => 'income',
                'amount' => $data['amount'],
                'fee' => 0,
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'] ?? null,
                'attachment_path' => $data['attachment_path'] ?? null,
            ]);
        });
    }

    /**
     * Record an expense transaction and debit the source wallet atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordExpense(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data): Transaction {
            $wallet = Wallet::query()
                ->where('user_id', $user->id)
                ->findOrFail($data['wallet_id']);

            $this->walletService->debit($wallet, $data['amount']);

            return Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'category_id' => $data['category_id'] ?? null,
                'type' => 'expense',
                'amount' => $data['amount'],
                'fee' => 0,
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'] ?? null,
                'attachment_path' => $data['attachment_path'] ?? null,
            ]);
        });
    }

    /**
     * Record a same-currency transfer between two user wallets atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordTransfer(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data): Transaction {
            $fromWallet = Wallet::query()
                ->where('user_id', $user->id)
                ->findOrFail($data['wallet_id']);

            $toWallet = Wallet::query()
                ->where('user_id', $user->id)
                ->findOrFail($data['destination_wallet_id']);

            $fee = (float) ($data['fee'] ?? 0);

            $this->walletService->transferSameCurrency($fromWallet, $toWallet, $data['amount'], $fee);

            return Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $fromWallet->id,
                'destination_wallet_id' => $toWallet->id,
                'category_id' => null,
                'type' => 'transfer',
                'amount' => $data['amount'],
                'destination_amount' => $data['amount'],
                'fee' => $fee,
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'] ?? null,
                'attachment_path' => $data['attachment_path'] ?? null,
            ]);
        });
    }

    /**
     * Record a cross-currency exchange transfer (e.g. IDR -> USD/USDT) atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordCurrencyExchange(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data): Transaction {
            $fromWallet = Wallet::query()
                ->where('user_id', $user->id)
                ->findOrFail($data['wallet_id']);

            $toWallet = Wallet::query()
                ->where('user_id', $user->id)
                ->findOrFail($data['destination_wallet_id']);

            $fee = (float) ($data['fee'] ?? 0);

            $exchangeRate = $this->walletService->transferCurrencyExchange(
                $fromWallet,
                $toWallet,
                $data['amount'],
                $data['destination_amount'],
                $fee,
            );

            return Transaction::create([
                'user_id' => $user->id,
                'wallet_id' => $fromWallet->id,
                'destination_wallet_id' => $toWallet->id,
                'category_id' => null,
                'type' => 'currency_exchange',
                'amount' => $data['amount'],
                'destination_amount' => $data['destination_amount'],
                'exchange_rate' => $exchangeRate,
                'fee' => $fee,
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'] ?? null,
                'attachment_path' => $data['attachment_path'] ?? null,
            ]);
        });
    }

    /**
     * Delete a transaction and reverse its wallet balance mutation atomically.
     */
    public function deleteTransaction(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $wallet = Wallet::query()->findOrFail($transaction->wallet_id);

            match ($transaction->type) {
                'income' => $this->walletService->debit($wallet, $transaction->amount),
                'expense' => $this->walletService->credit($wallet, $transaction->amount),
                'transfer', 'currency_exchange' => $this->reverseTransfer($transaction, $wallet),
                default => throw new DomainException('Tipe transaksi ini belum mendukung penghapusan otomatis.'),
            };

            $transaction->delete();
        });
    }

    /**
     * Update an existing transaction: reverse previous balance mutation, update columns, and apply new balance mutation atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data): Transaction {
            // 1. Reverse old balance mutation
            $oldWallet = Wallet::query()->findOrFail($transaction->wallet_id);
            match ($transaction->type) {
                'income' => $this->walletService->debit($oldWallet, $transaction->amount),
                'expense' => $this->walletService->credit($oldWallet, $transaction->amount),
                'transfer', 'currency_exchange' => $this->reverseTransfer($transaction, $oldWallet),
                default => throw new DomainException('Tipe transaksi ini belum mendukung pembaruan otomatis.'),
            };

            // 2. Prepare new data & calculate exchange_rate if needed
            $type = $data['type'];
            $amount = (float) $data['amount'];
            $fee = (float) ($data['fee'] ?? 0);
            $destinationAmount = isset($data['destination_amount']) && $data['destination_amount'] !== null && $data['destination_amount'] !== ''
                ? (float) $data['destination_amount']
                : null;

            $exchangeRate = null;
            if ($type === 'currency_exchange' && $destinationAmount !== null && $destinationAmount > 0) {
                $exchangeRate = round($amount / $destinationAmount, 8);
            }

            // 3. Update the transaction model
            $transaction->update([
                'type' => $type,
                'wallet_id' => $data['wallet_id'],
                'destination_wallet_id' => in_array($type, ['transfer', 'currency_exchange'], true) ? ($data['destination_wallet_id'] ?? null) : null,
                'category_id' => in_array($type, ['income', 'expense'], true) ? ($data['category_id'] ?? null) : null,
                'amount' => $amount,
                'destination_amount' => $destinationAmount,
                'exchange_rate' => $exchangeRate,
                'fee' => $fee,
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'] ?? null,
            ]);

            $transaction->refresh();

            // 4. Apply new balance mutation
            $newWallet = Wallet::query()->findOrFail($transaction->wallet_id);
            match ($transaction->type) {
                'income' => $this->walletService->credit($newWallet, $transaction->amount),
                'expense' => $this->walletService->debit($newWallet, $transaction->amount),
                'transfer' => $this->applyTransfer($transaction, $newWallet),
                'currency_exchange' => $this->applyCurrencyExchange($transaction, $newWallet),
                default => throw new DomainException('Tipe transaksi ini belum didukung.'),
            };

            return $transaction;
        });
    }

    /**
     * Apply same-currency transfer mutation.
     */
    private function applyTransfer(Transaction $transaction, Wallet $fromWallet): void
    {
        $toWallet = Wallet::query()->findOrFail($transaction->destination_wallet_id);
        $this->walletService->transferSameCurrency($fromWallet, $toWallet, (float) $transaction->amount, (float) $transaction->fee);
    }

    /**
     * Apply cross-currency exchange transfer mutation.
     */
    private function applyCurrencyExchange(Transaction $transaction, Wallet $fromWallet): void
    {
        $toWallet = Wallet::query()->findOrFail($transaction->destination_wallet_id);
        $this->walletService->transferCurrencyExchange(
            $fromWallet,
            $toWallet,
            (float) $transaction->amount,
            (float) $transaction->destination_amount,
            (float) $transaction->fee
        );
    }

    /**
     * Reverse a same-currency transfer back to the source wallet.
     */
    private function reverseTransfer(Transaction $transaction, Wallet $fromWallet): void
    {
        $destinationWallet = Wallet::query()->findOrFail($transaction->destination_wallet_id);
        $receivedAmount = (float) ($transaction->destination_amount ?? $transaction->amount);
        $refundAmount = (float) $transaction->amount + (float) $transaction->fee;

        $this->walletService->debit($destinationWallet, $receivedAmount);
        $this->walletService->credit($fromWallet, $refundAmount);
    }
}
