<?php

namespace Modules\DemoTrade\Providers;

use Modules\DemoTrade\Events\OrderHasPlaced;
use Modules\DemoTrade\Listeners\StartProcessingOrder;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderHasPlaced::class => [
            StartProcessingOrder::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
