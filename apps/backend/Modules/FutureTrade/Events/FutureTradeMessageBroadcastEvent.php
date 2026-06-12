<?php

namespace Modules\FutureTrade\Events;

use App\Model\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FutureTradeMessageBroadcastEvent implements ShouldBroadcastNow
{
    use SerializesModels, Dispatchable;
    private $channel = 'usernotification_';
    private $event = 'receive_notification';
    public $broadcastData;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        public int $user_id,
        public string $message,
    ) {
        $notification_details = Notification::create([
            'user_id' => $user_id,
            'title' => $message,
            'notification_body' => $message
        ]);

        $this->broadcastData = success(
            messageOrData: $message,
            topLevelData: [
                'notification_details' => $notification_details,
                'user_id' => $user_id,
            ]
        );
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel($this->channel.$this->user_id);
    }

    /**
    * The event's broadcast name.
    *
    * @return string
    */
    public function broadcastAs()
    {
        return $this->event;
    }

    public function broadcastWith()
    {
        return $this->broadcastData;
    }

    public function shouldQueue()
    {
        return false;
    }
}