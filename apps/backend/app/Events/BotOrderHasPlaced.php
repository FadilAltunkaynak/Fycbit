<?php

namespace App\Events;

use App\Model\Buy;
use App\Model\Sell;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class BotOrderHasPlaced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    /**
     * Create a new event instance.
     *
     * @param $order
     */
    public function __construct(Buy | Sell $order)
    {
        $this->order = $order;
    }
}
