<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\Kavenegar;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Order
     */
    private $order;

    /**
     * Create a new notification instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        //
        $this->order = $order;
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
            ->subject(trans('email.ACCEPTED_ORDER_TITLE', ['type' => $this->order->type]). ' - ' . trans('email.RAMZARZAN'))
            ->markdown('notifications.order',
                [
                    'order' => $this->order,
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
            'title' =>trans(
                'email.ACCEPTED_ORDER_TITLE',
                ['type' => trans("email.attributes.{$this->order->type}")
                ]
            ),
            'body' => trans(
                'email.ACCEPTED_ORDER',
                [
                    'amount' =>  $this->order->isType('buy') ?
                        (float) $this->order->amount :
                        (float) $this->order->amount * $this->order->price
                    ,
                    'coin' => $this->order->isType('buy') ?
                        $this->order->market->coin->code :
                        $this->order->market->quote->code
                ]
            ),
        ];
    }

    public function toKavenegar($notifable)
    {
        return [
            'items' => [
                $this->order->isType('buy') ?
                    (float) $this->order->amount :
                    (float) $this->order->amount * $this->order->price,
                $this->order->isType('buy') ?
                    $this->order->market->coin->code :
                    $this->order->market->quote->code
            ],
            'method' => 'ACCEPTEDORDER'
        ];
    }
}
