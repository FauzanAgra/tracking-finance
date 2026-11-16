<?php

namespace App\Console\Commands;

use App\Services\MarketPriceService;
use Illuminate\Console\Command;

class RefreshMarketPricesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investments:refresh-prices {--force-manual : Overwrite manual price overrides}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh market prices for all investment assets and USD/IDR exchange rate';

    /**
     * Execute the console command.
     */
    public function handle(MarketPriceService $marketPriceService): int
    {
        $result = $marketPriceService->refreshAll(null, (bool) $this->option('force-manual'));

        $this->info("Market prices refreshed: {$result['updated']} updated, {$result['failed']} failed, {$result['skipped_manual']} manual skipped.");

        return self::SUCCESS;
    }
}
