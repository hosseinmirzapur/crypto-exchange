<?php

namespace App\Listeners;

use App\Events\UserRegisteredPasswordEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserInitListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UserRegisteredPasswordEvent $event
     * @return void
     */
    public function handle(UserRegisteredPasswordEvent $event)
    {
        $event->user
            ->settings()
            ->createMany([
                    [
                        "setting_key" => "OTP",
                        "setting_value" => "EMAIL"
                    ],
                    [
                        "setting_key" => "LANGUAGE",
                        "setting_value" => "FA"
                    ]
                ]
            );

    }
}
