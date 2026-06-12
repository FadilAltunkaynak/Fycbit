<?php

namespace App\Console\Commands;

use App\Services\TradingBotServices\BotOrderService;
use Illuminate\Console\Command;

class TradingBotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:bot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Default trading bot that place buy and sell order ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private BotOrderService $botOrderService,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        while (true) {
            $botEnableStatus = settings('enable_bot_trade') ?? 0;

            if (!$botEnableStatus) {
                sleep(30);
                continue;
            }

            $this->botOrderService->getCoinPairsAndProcess(get_super_admin_id());
            sleep(1);
        }
    }
}
