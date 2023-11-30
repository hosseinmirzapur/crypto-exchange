<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class TransactionNotification extends Notification implements ShouldQueue
{
    use Queueable;



    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * Create a new notification instance.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        //
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
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
            ->theme(App::isLocale('fa') ? 'default-rtl' : 'default')
            ->subject(($this->transaction->status === 'ACCEPTED' ? trans('email.ACCEPTED_TRANSACTION') : trans('email.REJECTED_TRANSACTION')) . ' - ' . trans('email.RAMZARZAN'))
            ->markdown('notifications.transaction',
                [
                    'transaction' => $this->transaction,
                    'user' => $notifiable
                ]
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->transaction->status === 'ACCEPTED' ? trans('email.ACCEPTED_TRANSACTION') : trans('email.REJECTED_TRANSACTION') ,
            'body' => trans("email.{$this->transaction->status}_{$this->transaction->type}_TRANSACTION"),
        ];
    }

    public function toKavenegar($notifable)
    {
        return [
            'items' => [
                trans("email.attributes.{$this->transaction->type}")
            ],
            'method' => $this->transaction->status === 'ACCEPTED' ? 'ACCEPTEDTRANSACTION' : 'REJECTEDTRANSACTION'
        ];
    }
}
