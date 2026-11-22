<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Console\Command;

class TakeNetWorthSnapshotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:snapshot-net-worth {--user= : ID user spesifik yang ingin disnapshot}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ambil snapshot kekayaan bersih (Net Worth) harian pengguna untuk riwayat grafik';

    /**
     * Execute the console command.
     */
    public function handle(DashboardService $dashboardService): int
    {
        $userId = $this->option('user');

        $query = User::query();
        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();
        if ($users->isEmpty()) {
            $this->warn('Tidak ada user ditemukan untuk disnapshot.');

            return self::SUCCESS;
        }

        $this->info("Memproses snapshot Net Worth untuk {$users->count()} pengguna...");

        $count = 0;
        foreach ($users as $user) {
            $snapshot = $dashboardService->takeSnapshot($user);
            $this->line(" [OK] User #{$user->id} ({$user->name}): Rp ".number_format($snapshot->net_worth_idr, 0, ',', '.'));
            $count++;
        }

        $this->info("Selesai! {$count} snapshot berhasil disimpan.");

        return self::SUCCESS;
    }
}
