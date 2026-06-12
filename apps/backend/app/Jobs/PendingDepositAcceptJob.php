<?php

namespace App\Jobs;

use App\Enums\DepositeStatus;
use App\Http\Services\Evm\EvmWalletService;
use App\Model\DepositeTransaction;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

class PendingDepositAcceptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Summary of __construct
     * @param DepositeTransaction $transaction
     */
    public function __construct(protected DepositeTransaction $transaction)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->transaction->update(['is_admin_receive' => DepositeStatus::PROCESSING->value,]);
            $response = (new EvmWalletService)->acceptDepositFromUser($this->transaction->id);

            if (!is_success($response))
                throw new Exception($response['message']);

        } catch (Exception $e) {
            $this->transaction->update([
                'is_admin_receive' => DepositeStatus::PENDING->value,
                'reject_note' => $e->getMessage(),
            ]);
            storeLog(processExceptionMsg($e), 'error');
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        storeException('PendingDepositAcceptJob', json_encode($exception));
        $this->transaction->update([
            'is_admin_receive' => DepositeStatus::PENDING->value,
            'reject_note' => $exception->getMessage() ?? _("Failed for unknown reason."),
        ]);
        storeLog(processExceptionMsg($exception), 'error');
    }
}
