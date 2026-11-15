<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\User;
use App\Models\Wallet;
use DomainException;
use Illuminate\Support\Facades\DB;

class DebtService
{
    public function __construct(
        private readonly WalletService $walletService,
    ) {}

    /**
     * Create a new debt (payable) or receivable record.
     *
     * @param  array<string, mixed>  $data
     */
    public function createDebt(User $user, array $data): Debt
    {
        return Debt::create([
            'user_id' => $user->id,
            'person_name' => $data['person_name'],
            'phone_number' => $data['phone_number'] ?? null,
            'type' => $data['type'],
            'currency' => $data['currency'] ?? 'IDR',
            'total_amount' => $data['total_amount'],
            'written_off_amount' => 0,
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'] ?? null,
            'status' => 'unpaid',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Record an installment payment, mutate the chosen wallet atomically, and update debt status.
     *
     * @param  array<string, mixed>  $data
     */
    public function recordPayment(Debt $debt, array $data): DebtPayment
    {
        return DB::transaction(function () use ($debt, $data): DebtPayment {
            $lockedDebt = Debt::query()->lockForUpdate()->findOrFail($debt->id);

            if (in_array($lockedDebt->status, ['paid', 'written_off'], true)) {
                throw new DomainException('Pinjaman ini sudah lunas atau diikhlaskan.');
            }

            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new DomainException('Nominal cicilan harus lebih besar dari nol.');
            }

            $alreadyPaid = (float) $lockedDebt->payments()->sum('amount');
            $remaining = max(0, (float) $lockedDebt->total_amount - $alreadyPaid - (float) $lockedDebt->written_off_amount);

            if ($amount - $remaining > 0.0001) {
                throw new DomainException('Nominal cicilan melebihi sisa tagihan yang tersisa.');
            }

            $wallet = Wallet::query()
                ->where('user_id', $lockedDebt->user_id)
                ->findOrFail($data['wallet_id']);

            if ($lockedDebt->type === 'receivable') {
                $this->walletService->credit($wallet, $amount);
            } else {
                $this->walletService->debit($wallet, $amount);
            }

            $payment = DebtPayment::create([
                'debt_id' => $lockedDebt->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'payment_date' => $data['payment_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $newRemaining = $remaining - $amount;
            $lockedDebt->status = $newRemaining <= 0.0001 ? 'paid' : 'partially_paid';
            $lockedDebt->save();

            return $payment;
        });
    }

    /**
     * Write off the remaining debt/receivable balance cleanly without altering wallet balances.
     */
    public function writeOffDebt(Debt $debt, ?string $notes = null): Debt
    {
        return DB::transaction(function () use ($debt, $notes): Debt {
            $lockedDebt = Debt::query()->lockForUpdate()->findOrFail($debt->id);

            if (in_array($lockedDebt->status, ['paid', 'written_off'], true)) {
                throw new DomainException('Pinjaman ini sudah berstatus lunas atau diikhlaskan.');
            }

            $alreadyPaid = (float) $lockedDebt->payments()->sum('amount');
            $remaining = max(0, (float) $lockedDebt->total_amount - $alreadyPaid);

            $lockedDebt->written_off_amount = $remaining;
            $lockedDebt->status = 'written_off';

            if ($notes !== null && trim($notes) !== '') {
                $lockedDebt->notes = trim(($lockedDebt->notes ? $lockedDebt->notes.' | ' : '').'Diikhlaskan: '.trim($notes));
            }

            $lockedDebt->save();

            return $lockedDebt;
        });
    }

    /**
     * Delete a debt record only if no installment payments have been recorded.
     */
    public function deleteDebt(Debt $debt): void
    {
        if ($debt->payments()->exists()) {
            throw new DomainException('Catatan yang sudah memiliki histori cicilan tidak dapat dihapus.');
        }

        $debt->delete();
    }
}
