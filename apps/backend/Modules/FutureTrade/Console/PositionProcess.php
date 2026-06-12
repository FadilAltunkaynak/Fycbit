<?php

namespace Modules\FutureTrade\Console;

use App\Model\CoinPair;
use Illuminate\Console\Command;
use Modules\FutureTrade\Entities\FutureCoinPair;
use Modules\FutureTrade\Entities\FuturePrice;
use Modules\FutureTrade\Events\TodayChangeBroadcastEvent;
use Modules\FutureTrade\Services\ExternalPriceServices\FutureTickerService;
use Modules\FutureTrade\Services\FuturePositionServices\FuturePositionService;

class PositionProcess extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'future:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command process all open position stat with mark price.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(FuturePositionService $service, FutureTickerService $tickerService)
    {
        $second = 5;
        while (true) {
            $this->runFutureProcess($service, $tickerService);
            sleep($second);
        }
    }

    /**
     * Execute the process here.
     *
     * @return mixed
     */
    public function runFutureProcess(FuturePositionService $service, FutureTickerService $tickerService)
    {
        FutureCoinPair::statusActive()->chunk(50, function ($pairs) use ($service, $tickerService) {
            $pairs->map(function ($pair) use ($service, $tickerService) {
                $symbol = $pair->code ?: ($pair->base_coin_code . $pair->trade_coin_code);
                $ticker = $tickerService->getPrice($symbol);
                $index_price_value = ($ticker['price'] ?? 0);

                if ($index_price_value <= 0) {
                    $coinPair = CoinPair::where([
                        'parent_coin_id' => $pair->trade_coin_id,
                        'child_coin_id' => $pair->base_coin_id,
                    ])->first();

                    $index_price_value = ($coinPair?->price ?? 0);
                }

                if ($index_price_value <= 0) {
                    return;
                }

                $contract_price_value = (cache_service()->getMarketPrice($pair->id) ?? 0);
                if ($contract_price_value <= 0) {
                    $contract_price_value = $index_price_value;
                }

                $scale = $pair->trade_decimal ?: 8;
                $index_price = (string) $index_price_value;
                $contract_price = (string) $contract_price_value;
                $mark_price = bcdivx(bcaddx($index_price, $contract_price, $scale), '2', $scale);

                cache_service()->setIndexPrice(
                    pair_id: $pair->id,
                    index_price: $index_price
                );

                cache_service()->setMarkPrice(
                    pair_id: $pair->id,
                    mark_price: $mark_price
                );

                FuturePrice::updateOrCreate(
                    ['future_coin_pair_id' => $pair->id],
                    [
                        'future_coin_pair_uid' => $pair->uid,
                        'mark_price' => $mark_price,
                        'index_price' => $index_price,
                    ]
                );

                rescue(fn() => TodayChangeBroadcastEvent::dispatch($pair->id));

                $service->processPositionWithMarkPrice(
                    coin_pair: $pair,
                    mark_price: $mark_price
                );
            });
        });
    }
}
