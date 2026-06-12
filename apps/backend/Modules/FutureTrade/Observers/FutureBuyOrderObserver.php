<?php

namespace Modules\FutureTrade\Observers;

use Modules\FutureTrade\Entities\FutureBuy;


class FutureBuyOrderObserver
{
    /**
     * Handle the FutureBuy "created" event.
     *
     * @param  FutureBuy  $futureTrade
     * @return void
     */
    public function created(FutureBuy $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureBuy "updated" event.
     *
     * @param  FutureBuy  $futureTrade
     * @return void
     */
    public function updated(FutureBuy $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureBuy "deleted" event.
     *
     * @param  FutureBuy  $futureTrade
     * @return void
     */
    public function deleted(FutureBuy $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureBuy "restored" event.
     *
     * @param  FutureBuy  $futureTrade
     * @return void
     */
    public function restored(FutureBuy $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureBuy "force deleted" event.
     *
     * @param  FutureBuy  $futureTrade
     * @return void
     */
    public function forceDeleted(FutureBuy $futureTrade)
    {
        //
    }
}
