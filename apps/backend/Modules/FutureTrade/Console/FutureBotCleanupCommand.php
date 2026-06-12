<?php

namespace Modules\FutureTrade\Console;

use Illuminate\Console\Command;
use Modules\FutureTrade\Entities\FutureBuy;
use Modules\FutureTrade\Entities\FutureSell;
use Modules\FutureTrade\Entities\FutureTrade;

class FutureBotCleanupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'future:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean bot buy/sell/trade data older than given hours.';

    protected $signature = 'future:clean {--hours=1 : Delete bot data older than this hour threshold}';

    public function handle()
    {
        $hours = (int) $this->option('hours');
        if ($hours < 1) {
           $hours = 1;
        }

        $cutoff = now()->subHours($hours);

        $deletedTrades = FutureTrade::query()
            ->where('is_bot', 1)
            ->where('created_at', '<=', $cutoff)
            ->delete();

        $deletedBuys = FutureBuy::query()
            ->where('is_bot', 1)
            ->where('created_at', '<=', $cutoff)
            ->delete();

        $deletedSells = FutureSell::query()
            ->where('is_bot', 1)
            ->where('created_at', '<=', $cutoff)
            ->delete();

        return 0;
    }
}
