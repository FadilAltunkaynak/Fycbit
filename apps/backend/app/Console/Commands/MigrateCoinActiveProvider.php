<?php

namespace App\Console\Commands;

use App\Enums\NetworkBase;
use App\Model\Coin;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use Illuminate\Console\Command;

class MigrateCoinActiveProvider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate-coin-active-provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate coin active provider';

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
     * @return void
     */
    public function handle()
    {
        Coin::query()
        ->with(['coin_network', 'coin_network.network'])
        ->where([
            'currency_type' => CURRENCY_TYPE_CRYPTO,
            'active_provider' => null
        ])->chunk(50, [$this, 'updateCoinsProvider']);
    }

    public  function updateCoinsProvider($coins)
    {
        foreach($coins as $coin)
        {
            $oldNetwork = $coin?->coin_network[0] ?? null
                ? $coin?->coin_network[0]?->network?->base_type ?? $coin?->network ?? 0
                : $coin?->network ?? 0;

            $provider   = NetworkBase::tryFrom($oldNetwork)?->getProvider();

            if($provider) $coin->update([ 'active_provider' => $provider ]);
        }
    }
}
