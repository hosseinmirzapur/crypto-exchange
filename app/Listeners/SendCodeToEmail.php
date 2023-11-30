<?php

namespace App\Listeners;

use App\Events\EmailRegistered;
use App\Models\Code;
use App\Notifications\SendCodeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendCodeToEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param EmailRegistered $event
     * @return void
     */
    public function handle(EmailRegistered $event)
    {
        $user = $event->user;
        $code = Code::generateCode($user, 'CONFIRM_REGISTERED_EMAIL');
        $user->notify(new SendCodeNotification($code,'CONFIRM_REGISTERED_EMAIL'));
    }
}
