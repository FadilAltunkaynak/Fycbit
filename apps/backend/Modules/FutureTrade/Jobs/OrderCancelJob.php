<?php

namespace Modules\FutureTrade\Jobs;

use App\Http\Repositories\OrderRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\FutureTrade\Services\OrderService\OrderService;

class OrderCancelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private int $user_id,
        private int $order_id,
        private int $order_type
    ) {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(OrderService $service)
    {
        $service->orderCancelProcess(
            $this->user_id,
            $this->order_id,
            $this->order_type
        );
    }
}
