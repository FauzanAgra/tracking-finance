<?php

namespace App\Console\Commands;

use App\Services\AlertService;
use Illuminate\Console\Command;

class CheckSmartAlertsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:check-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa batas kuota budget, jatuh tempo utang, dan target impian lalu kirimkan notifikasi cerdas.';

    /**
     * Execute the console command.
     */
    public function handle(AlertService $alertService): int
    {
        $this->info('Memeriksa peringatan dan pengingat keuangan cerdas...');
        $count = $alertService->checkAllAlerts();
        $this->info("Pemeriksaan selesai. {$count} notifikasi baru dikirimkan.");

        return Command::SUCCESS;
    }
}
