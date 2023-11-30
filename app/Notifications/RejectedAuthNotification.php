<?php

namespace App\Notifications;

use App\Notifications\Channels\Kavenegar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class RejectedAuthNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->theme(App::isLocale('fa') ? 'default-rtl' : 'default')
            ->subject(trans('email.REJECTED_AUTH_TITLE') . ' - ' . trans('email.RAMZARZAN'))
            ->greeting(trans('email.GREETING'))
            ->line(trans('email.REJECTED_AUTH'))
            ->line(trans('email.NOTICE'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => trans('email.REJECTED_AUTH_TITLE'),
            'body' => trans('email.REJECTED_AUTH'),
        ];
    }


    public function toKavenegar($notifable)
    {
        return [
            'items' => ['است'],
            'method' => 'REJECTEDAUTH',
        ];
    }
}
