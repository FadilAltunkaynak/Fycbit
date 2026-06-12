<?php

namespace App\Console\Commands;

use App\Enums\DepositCollectionStatus;
use App\Enums\NetworkBase;
use App\Http\Repositories\CustomTokenRepository;
use App\Jobs\PendingDepositAcceptJob;
use App\Model\DepositeTransaction;
use App\Traits\ResponseHandlerTrait;
use Illuminate\Console\Command;

class AdjustCustomTokenDeposit extends Command
{
    use ResponseHandlerTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adjust-token-deposit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adjust token deposit';

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
    public function handle()
    {

        $transactions = DepositeTransaction::with('networkInfo')
            ->whereHas('networkInfo', function ($query) {
                $query->whereIn('base_type', NetworkBase::customNetworkGroup());
            })
            ->where([
                'address_type' => ADDRESS_TYPE_EXTERNAL,
                'is_admin_receive' => DepositCollectionStatus::PENDING->value,
            ])->get();

        foreach ($transactions as $transaction) {
            PendingDepositAcceptJob::dispatch($transaction);
        }
    }
}
