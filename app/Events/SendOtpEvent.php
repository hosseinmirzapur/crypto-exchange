<?php

namespace App\Events;

use App\Models\Code;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendOtpEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $code;
    public $position;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param string $position
     */
    public function __construct(User $user, $position = "OTP_LOGIN_USER")
    {
        $this->user = $user;
        $this->position = $position;
        $this->code = Code::generateCode($user, $position);
    }
}
