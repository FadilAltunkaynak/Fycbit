<?php

namespace Modules\IcoLaunchpad\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\IcoLaunchpad\Http\Services\IcoTokenBuyService;

class BuyTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        storeException('tokenBuy job', 'start');
        $service = new IcoTokenBuyService();
        $response = $service->tokenBuyJob($this->data);
        storeException('BuyTokenJob response ->', json_encode($response));
        storeException('tokenBuy job', 'end');
    }
}
