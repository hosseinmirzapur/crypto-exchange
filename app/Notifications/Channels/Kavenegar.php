<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Kavenegar\Laravel\Facade as KavenegarProvider;

class Kavenegar
{

    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toKavenegar($notifiable);
        KavenegarProvider::VerifyLookup(
            $notifiable->profile->mobile,
            $message['items'][0] ?? '',
            $message['items'][1] ?? '',
            $message['items'][2] ?? '',
            $message['method']
        );
    }
}
