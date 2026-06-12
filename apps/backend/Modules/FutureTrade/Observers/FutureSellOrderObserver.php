<?php

namespace Modules\FutureTrade\Observers;

use Modules\FutureTrade\Entities\FutureSell;


class FutureSellOrderObserver
{
    /**
     * Handle the FutureSell "created" event.
     *
     * @param  FutureSell  $futureTrade
     * @return void
     */
    public function created(FutureSell $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureSell "updated" event.
     *
     * @param  FutureSell  $futureTrade
     * @return void
     */
    public function updated(FutureSell $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureSell "deleted" event.
     *
     * @param  FutureSell  $futureTrade
     * @return void
     */
    public function deleted(FutureSell $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureSell "restored" event.
     *
     * @param  FutureSell  $futureTrade
     * @return void
     */
    public function restored(FutureSell $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureSell "force deleted" event.
     *
     * @param  FutureSell  $futureTrade
     * @return void
     */
    public function forceDeleted(FutureSell $futureTrade)
    {
        //
    }
}
