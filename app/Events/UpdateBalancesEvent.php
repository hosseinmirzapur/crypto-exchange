<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateBalancesEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $balances;


    /**
     * Create a new event instance.
     *
     * @param $balances
     */
    public function __construct($balances)
    {

        $this->balances = $balances;
    }


}
