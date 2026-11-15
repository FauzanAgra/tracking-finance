<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use DomainException;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Resolve default Material Symbol icon name based on wallet type.
     */
    public static function defaultIconForType(string $type): string
    {
        return match ($type) {
            'bank' => 'account_balance',
            'cash' => 'payments',
            'e_wallet' => 'qr_code_2',
            'investment_cash' => 'trending_up',
            default => 'wallet',
        };
    }

    /**
     * Create a new wallet for the user inside an atomic database transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function createWallet(User $user, array $data): Wallet
    {
        return DB::transaction(function () use ($user, $data): Wallet {
            $isDefault = (bool) ($data['is_default'] ?? false);

            // If this is the user's very first wallet or explicitly marked default,
            // ensure only this wallet carries is_default = true.
            if ($isDefault || ! $user->wallets()->exists()) {
                $user->wallets()->update(['is_default' => false]);
                $isDefault = true;
            }

            return $user->wallets()->create([
                'name' => $data['name'],
                'type' => $data['type'],
                'currency' => $data['currency'] ?? 'IDR',
                'balance' => $data['balance'] ?? 0,
                'account_number' => $data['account_number'] ?? null,
                'color' => $data['color'] ?? '#1E4D38',
                'icon' => $data['icon'] ?? self::defaultIconForType($data['type']),
                'is_default' => $isDefault,
                'is_active' => $data['is_active'] ?? true,
            ]);
        });
    }

    /**
     * Update an existing wallet inside an atomic database transaction.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateWallet(Wallet $wallet, array $data): Wallet
    {
        return DB::transaction(function () use ($wallet, $data): Wallet {
            $isDefault = array_key_exists('is_default', $data)
                ? (bool) $data['is_default']
                : $wallet->is_default;

            if ($isDefault && ! $wallet->is_default) {
                Wallet::where('user_id', $wallet->user_id)
                    ->where('id', '!=', $wallet->id)
                    ->update(['is_default' => false]);
            }

            $type = $data['type'] ?? $wallet->type;

            $wallet->update([
                'name' => $data['name'] ?? $wallet->name,
                'type' => $type,
                'currency' => $data['currency'] ?? $wallet->currency,
                'balance' => array_key_exists('balance', $data) ? $data['balance'] : $wallet->balance,
                'account_number' => array_key_exists('account_number', $data) ? $data['account_number'] : $wallet->account_number,
                'color' => $data['color'] ?? $wallet->color,
                'icon' => $data['icon'] ?? self::defaultIconForType($type),
                'is_default' => $isDefault,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $wallet->is_active,
            ]);

            return $wallet->fresh();
        });
    }

    /**
     * Set the given wallet as the user's default wallet atomically.
     */
    public function setDefaultWallet(Wallet $wallet): Wallet
    {
        return DB::transaction(function () use ($wallet): Wallet {
            Wallet::where('user_id', $wallet->user_id)
                ->where('id', '!=', $wallet->id)
                ->update(['is_default' => false]);

            $wallet->update(['is_default' => true]);

            return $wallet->fresh();
        });
    }

    /**
     * Delete a wallet if it has no linked transactions or debt payments.
     *
     * @throws DomainException
     */
    public function deleteWallet(Wallet $wallet): void
    {
        DB::transaction(function () use ($wallet): void {
            if ($wallet->transactions()->exists() || $wallet->debtPayments()->exists()) {
                throw new DomainException('Dompet yang sudah memiliki riwayat transaksi atau cicilan tidak dapat dihapus.');
            }

            $wasDefault = $wallet->is_default;
            $userId = $wallet->user_id;

            $wallet->delete();

            // Promote another active wallet as default if the deleted one was default.
            if ($wasDefault) {
                $nextWallet = Wallet::where('user_id', $userId)->orderBy('id')->first();
                if ($nextWallet) {
                    $nextWallet->update(['is_default' => true]);
                }
            }
        });
    }

    /**
     * Add funds to a wallet atomically with row-level locking.
     *
     * @throws DomainException
     */
    public function credit(Wallet $wallet, float|string $amount): Wallet
    {
        $numericAmount = (float) $amount;

        if ($numericAmount <= 0) {
            throw new DomainException('Nominal penambahan saldo harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($wallet, $numericAmount): Wallet {
            /** @var Wallet $locked */
            $locked = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            $newBalance = round((float) $locked->balance + $numericAmount, 4);
            $locked->update(['balance' => $newBalance]);

            return $locked->fresh();
        });
    }

    /**
     * Deduct funds from a wallet atomically after validating sufficient balance.
     *
     * @throws DomainException
     */
    public function debit(Wallet $wallet, float|string $amount): Wallet
    {
        $numericAmount = (float) $amount;

        if ($numericAmount <= 0) {
            throw new DomainException('Nominal pengurangan saldo harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($wallet, $numericAmount): Wallet {
            /** @var Wallet $locked */
            $locked = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            if ((float) $locked->balance < $numericAmount) {
                throw new DomainException("Saldo dompet {$locked->name} tidak mencukupi.");
            }

            $newBalance = round((float) $locked->balance - $numericAmount, 4);
            $locked->update(['balance' => $newBalance]);

            return $locked->fresh();
        });
    }

    /**
     * Transfer funds between two wallets of the same currency atomically.
     *
     * @throws DomainException
     */
    public function transferSameCurrency(Wallet $from, Wallet $to, float|string $amount, float|string $fee = 0): void
    {
        if ($from->id === $to->id) {
            throw new DomainException('Dompet asal dan dompet tujuan tidak boleh sama.');
        }

        if ($from->currency !== $to->currency) {
            throw new DomainException('Gunakan konversi kurs untuk transfer antar mata uang berbeda.');
        }

        $numericAmount = (float) $amount;
        $numericFee = (float) $fee;

        if ($numericAmount <= 0 || $numericFee < 0) {
            throw new DomainException('Nominal transfer harus lebih dari 0 dan biaya admin tidak boleh negatif.');
        }

        DB::transaction(function () use ($from, $to, $numericAmount, $numericFee): void {
            // Lock in deterministic ID order to avoid deadlocks.
            $ids = [$from->id, $to->id];
            sort($ids);
            $locked = Wallet::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            /** @var Wallet $lockedFrom */
            $lockedFrom = $locked->get($from->id);
            /** @var Wallet $lockedTo */
            $lockedTo = $locked->get($to->id);

            $totalDeduct = $numericAmount + $numericFee;
            if ((float) $lockedFrom->balance < $totalDeduct) {
                throw new DomainException("Saldo dompet {$lockedFrom->name} tidak mencukupi untuk transfer dan biaya admin.");
            }

            $lockedFrom->update(['balance' => round((float) $lockedFrom->balance - $totalDeduct, 4)]);
            $lockedTo->update(['balance' => round((float) $lockedTo->balance + $numericAmount, 4)]);
        });
    }

    /**
     * Transfer funds between two wallets of different currencies and return the effective exchange rate.
     *
     * @throws DomainException
     */
    public function transferCurrencyExchange(
        Wallet $from,
        Wallet $to,
        float|string $amountOut,
        float|string $amountIn,
        float|string $fee = 0
    ): float {
        if ($from->id === $to->id) {
            throw new DomainException('Dompet asal dan dompet tujuan tidak boleh sama.');
        }

        $out = (float) $amountOut;
        $in = (float) $amountIn;
        $numericFee = (float) $fee;

        if ($out <= 0 || $in <= 0 || $numericFee < 0) {
            throw new DomainException('Nominal keluar dan masuk harus lebih dari 0.');
        }

        DB::transaction(function () use ($from, $to, $out, $in, $numericFee): void {
            $ids = [$from->id, $to->id];
            sort($ids);
            $locked = Wallet::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            /** @var Wallet $lockedFrom */
            $lockedFrom = $locked->get($from->id);
            /** @var Wallet $lockedTo */
            $lockedTo = $locked->get($to->id);

            $totalDeduct = $out + $numericFee;
            if ((float) $lockedFrom->balance < $totalDeduct) {
                throw new DomainException("Saldo dompet {$lockedFrom->name} tidak mencukupi untuk konversi.");
            }

            $lockedFrom->update(['balance' => round((float) $lockedFrom->balance - $totalDeduct, 4)]);
            $lockedTo->update(['balance' => round((float) $lockedTo->balance + $in, 4)]);
        });

        return round($out / $in, 6);
    }
}
