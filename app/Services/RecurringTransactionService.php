<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecurringTransactionService
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    /**
     * Create a new recurring transaction template.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): RecurringTransaction
    {
        $startDate = Carbon::parse($data['start_date']);
        $nextRunDate = ! empty($data['next_run_date']) ? Carbon::parse($data['next_run_date']) : $startDate;

        return RecurringTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $data['wallet_id'],
            'destination_wallet_id' => in_array($data['type'], ['transfer'], true) ? ($data['destination_wallet_id'] ?? null) : null,
            'category_id' => in_array($data['type'], ['income', 'expense'], true) ? ($data['category_id'] ?? null) : null,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'fee' => $data['fee'] ?? 0,
            'frequency' => $data['frequency'] ?? 'monthly',
            'start_date' => $startDate->toDateString(),
            'next_run_date' => $nextRunDate->toDateString(),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Toggle the active status of a recurring transaction.
     */
    public function toggleActive(RecurringTransaction $recurring): RecurringTransaction
    {
        $recurring->update(['is_active' => ! $recurring->is_active]);

        return $recurring->fresh();
    }

    /**
     * Delete a recurring transaction template.
     */
    public function delete(RecurringTransaction $recurring): void
    {
        $recurring->delete();
    }

    /**
     * Process all recurring transactions due on or before the given date.
     *
     * @return array{processed: int, failed: int}
     */
    public function processDue(?Carbon $today = null): array
    {
        $date = $today ?? Carbon::today();
        $dueItems = RecurringTransaction::query()->due($date)->with(['user', 'wallet'])->get();

        $processed = 0;
        $failed = 0;

        foreach ($dueItems as $recurring) {
            try {
                DB::transaction(function () use ($recurring, $date): void {
                    // Record real transaction
                    $payload = [
                        'wallet_id' => $recurring->wallet_id,
                        'destination_wallet_id' => $recurring->destination_wallet_id,
                        'category_id' => $recurring->category_id,
                        'amount' => $recurring->amount,
                        'fee' => $recurring->fee,
                        'transaction_date' => $recurring->next_run_date->toDateString(),
                        'description' => ($recurring->description ? $recurring->description.' ' : '').'[Otomatis Rutin]',
                    ];

                    match ($recurring->type) {
                        'income' => $this->transactionService->recordIncome($recurring->user, $payload),
                        'expense' => $this->transactionService->recordExpense($recurring->user, $payload),
                        'transfer' => $this->transactionService->recordTransfer($recurring->user, $payload),
                        default => throw new DomainException("Tipe transaksi berulang '{$recurring->type}' tidak dikenali."),
                    };

                    // Advance next_run_date based on frequency
                    $currentRun = Carbon::parse($recurring->next_run_date);
                    $nextDate = match ($recurring->frequency) {
                        'daily' => $currentRun->copy()->addDay(),
                        'weekly' => $currentRun->copy()->addWeek(),
                        'monthly' => $currentRun->copy()->addMonth(),
                        default => $currentRun->copy()->addMonth(),
                    };

                    $recurring->update([
                        'last_run_date' => $recurring->next_run_date->toDateString(),
                        'next_run_date' => $nextDate->toDateString(),
                    ]);
                });

                $processed++;
            } catch (Throwable $e) {
                Log::error("Gagal memproses transaksi rutin ID #{$recurring->id}: ".$e->getMessage());
                $failed++;
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
        ];
    }
}
