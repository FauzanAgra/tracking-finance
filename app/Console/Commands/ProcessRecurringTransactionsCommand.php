<?php

namespace App\Console\Commands;

use App\Services\RecurringTransactionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:process-recurring {--date= : Tanggal acuan proses (format: YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan transaksi rutin / berulang yang telah jatuh tempo';

    /**
     * Execute the console command.
     */
    public function handle(RecurringTransactionService $service): int
    {
        $dateOpt = $this->option('date');
        $targetDate = $dateOpt ? Carbon::parse($dateOpt) : Carbon::today();

        $this->info("Menjalankan transaksi rutin yang jatuh tempo pada atau sebelum: {$targetDate->toDateString()}");

        $result = $service->processDue($targetDate);

        $this->info("Hasil: {$result['processed']} transaksi berhasil dieksekusi, {$result['failed']} gagal.");

        return self::SUCCESS;
    }
}
