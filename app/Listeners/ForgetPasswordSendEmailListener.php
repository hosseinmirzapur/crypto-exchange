<?php

namespace App\Listeners;

use App\Events\ForgetPasswordEmailConfirmationEvent;
use App\Exceptions\CustomException;
use App\Models\Code;
use App\Notifications\SendCodeNotification;
use App\Services\SMS;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ForgetPasswordSendEmailListener
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
     * @param ForgetPasswordEmailConfirmationEvent $event
     * @return void
     */
    public function handle(ForgetPasswordEmailConfirmationEvent $event)
    {
        $user = $event->user;
        $code = Code::generateCode($user, 'CHANGE_PASSWORD');
        switch ($event->user->settings()->key('OTP')->value('setting_value') ?? 'EMAIL') {
            case 'SMS' :
                SMS::handle($event->user->profile->mobile, $code, 'CHANGE_PASSWORD');
                break;
            case 'GOOGLE' :
                break;
            case 'EMAIL':
                $event->user->notify(new SendCodeNotification($code, 'CHANGE_PASSWORD'));
                break;
            default:
                throw new CustomException('WRONG_METHOD', 400);
        }

    }
}
