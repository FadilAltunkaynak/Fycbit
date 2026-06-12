<?php

namespace Modules\FutureTrade\Observers;

use Modules\FutureTrade\Entities\FutureCoinPair;


class FutureCoinPairObserver
{
    /**
     * Handle the FutureCoinPair "created" event.
     *
     * @param  FutureCoinPair  $futureTrade
     * @return void
     */
    public function created(FutureCoinPair $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureCoinPair "updated" event.
     *
     * @param  FutureCoinPair  $futureTrade
     * @return void
     */
    public function updated(FutureCoinPair $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureCoinPair "deleted" event.
     *
     * @param  FutureCoinPair  $futureTrade
     * @return void
     */
    public function deleted(FutureCoinPair $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureCoinPair "restored" event.
     *
     * @param  FutureCoinPair  $futureTrade
     * @return void
     */
    public function restored(FutureCoinPair $futureTrade)
    {
        //
    }

    /**
     * Handle the FutureCoinPair "force deleted" event.
     *
     * @param  FutureCoinPair  $futureTrade
     * @return void
     */
    public function forceDeleted(FutureCoinPair $futureTrade)
    {
        //
    }
}
