<?php

namespace Modules\P2P\Providers;

use Modules\P2P\Entities\POrder;
use Modules\P2P\Entities\POrderChat;
use Illuminate\Auth\Events\Registered;
use Modules\P2P\Entities\PGiftCardOrder;
use Modules\P2P\Observers\OpenTradeObserver;
use Modules\P2P\Observers\TradeChatObserver;
use Modules\P2P\Observers\OpenGiftCardTradeObserver;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Registered::class => [
        //     SendEmailVerificationNotification::class,
        // ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        POrder::observe(OpenTradeObserver::class);
        POrderChat::observe(TradeChatObserver::class);
        PGiftCardOrder::observe(OpenGiftCardTradeObserver::class);
    }
}
