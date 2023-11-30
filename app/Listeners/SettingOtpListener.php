<?php

namespace App\Listeners;

use App\Events\UpdateSettingEvent;
use App\Exceptions\CustomException;
use App\Notifications\SendCodeNotification;
use App\Services\SMS;

class SettingOtpListener
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
     * @param UpdateSettingEvent $event
     * @return void
     */
    public function handle(UpdateSettingEvent $event)
    {
        switch ($event->method) {
            case 'SMS' :
                SMS::handle($event->user->profile->mobile, $event->code, 'SETTING_OTP');
                break;
            case 'GOOGLE' :
                break;
            case 'EMAIL':
                $event->user->notify(new SendCodeNotification($event->code,'SETTING_OTP'));
                break;
            default:
                throw new CustomException('WRONG_METHOD', 400);

        }
    }
}
