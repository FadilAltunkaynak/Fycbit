<?php

namespace App\Console\Commands;

use App\Http\Services\Evm\EvmWalletService;
use Illuminate\Console\Command;

class TokenBlockDepositCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token-block-deposit-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command check block by block transaction';

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
     * @return int
     */
    public function handle()
    {
        storeBotException('TokenBlockDepositCommand called', date('Y-m-d H:i:s'));
        $service = (new EvmWalletService)->callBlockDepositCommand();
    }
}
