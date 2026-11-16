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
