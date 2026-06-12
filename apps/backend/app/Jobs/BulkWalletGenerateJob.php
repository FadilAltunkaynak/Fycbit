<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Http\Services\WalletService;
use App\Traits\ResponseHandlerTrait;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\P2P\Entities\P2PWallet;
use Modules\P2P\Http\Service\WalletService as P2PWalletService;

class BulkWalletGenerateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ResponseHandlerTrait;

    public function __construct(protected $id, protected $type)
    {
    }

    public function handle()
    {
        return $this->handlerGeneralResponse(function () {
            (new WalletService())->bulkWalletGenerate($this->id, $this->type);

            $isP2pEnabled = settings('p2p_module');
            $isP2pFileExist = class_exists(P2PWallet::class);

            if ($isP2pEnabled || $isP2pFileExist)
                (new P2PWalletService())->bulkWalletGenerate($this->id, $this->type);

            return $this->responseData(true);
        });
    }
}
