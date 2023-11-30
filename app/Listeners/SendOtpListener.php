<?php

namespace App\Listeners;

use App\Events\SendOtpEvent;
use App\Exceptions\CustomException;
use App\Notifications\SendCodeNotification;
use App\Services\SMS;

class SendOtpListener
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
     * @param SendOtpEvent $event
     * @return void
     */
    public function handle(SendOtpEvent $event)
    {
        switch ($event->user->settings()->key('OTP')->value('setting_value') ?? 'EMAIL') {
            case 'SMS' :
                SMS::handle($event->user->profile->mobile, $event->code, $event->position);
                break;
            case 'GOOGLE' :
                break;
            case 'EMAIL':
                $event->user->notify(new SendCodeNotification($event->code, $event->position));
                break;
            default:
                throw new CustomException('WRONG_METHOD', 400);
        }
    }
}
