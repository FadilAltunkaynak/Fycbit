<?php

namespace Modules\FutureTrade\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\FutureTrade\DataObject\FutureOrderData;
use Modules\FutureTrade\Repositories\WalletRepository\FutureWalletRepository;
use Modules\FutureTrade\Services\OrderService\OrderService;

class OrderCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private FutureOrderData $order
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrderService $service, FutureWalletRepository $walletRepository)
    {
        rescue(fn()=> $service->orderJobProcess($this->order));
    }
}
