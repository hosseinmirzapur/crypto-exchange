<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class SendCodeNotification extends Notification
{
    use Queueable;

    public $code;
    public $position;

    /**
     * Create a new notification instance.
     *
     * @param $code
     * @param $position
     */
    public function __construct($code, $position)
    {
        $this->code = $code;
        $this->position = $position;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->theme( App::isLocale('fa') ? 'default-rtl' : 'default')
            ->subject(trans('email.SEND_CODE_TITLE'). ' - ' . trans('email.RAMZARZAN'))
            ->greeting(trans('email.GREETING'))
            ->line(trans('email.'.$this->position, ['attribute' => env('FRONT_URL')]))
            ->line(trans('email.VERIFICATION_CODE'))
            ->action($this->code,null)
            ->line(trans('email.NOTICE'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
