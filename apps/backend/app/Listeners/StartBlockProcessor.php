<?php

namespace App\Listeners;

use App\Events\NetworkCreated;
use App\Http\Services\Evm\EvmWalletService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StartBlockProcessor implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(private EvmWalletService $evmWalletService)
    {
    }

    /**
     * Handle the event.
     *
     * @param  App\Events\NetworkCreated  $event
     * @return void
     */
    public function handle(NetworkCreated $event)
    {
        $blockProcessingResponse = $this->evmWalletService->startBlockProcessor($event->network->id);
        if (!$blockProcessingResponse['success']) {
            storeException('Block processing start error', $blockProcessingResponse['message']);
        }
    }
}
