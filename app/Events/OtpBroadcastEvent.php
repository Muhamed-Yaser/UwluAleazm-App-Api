<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OtpBroadcastEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $otp;
    public $user_id;

    /**
     * Create a new event instance.
     */
    public function __construct($otp , $user_id)
    {
        $this->otp = $otp;
        $this->user_id = $user_id;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('otp-channel.' . $this->user_id);
    }
    public function broadcastAs()
    {
        // Name of the event
        return 'otp-event';
    }
}
