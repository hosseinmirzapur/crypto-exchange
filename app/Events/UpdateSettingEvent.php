<?php

namespace App\Events;

use App\Models\Code;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateSettingEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $method;
    public $user;
    public $code;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param $method
     */
    public function __construct(User $user, $method)
    {
        $this->user = $user;
        $this->code = Code::generateCode($user, 'SETTING_OTP');
        $this->method = $method;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
