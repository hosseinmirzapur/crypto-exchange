<?php

namespace App\Listeners;

use App\Events\OrderUpdateEvent;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateOrderListener
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
     * @param OrderUpdateEvent $event
     * @return void
     */
    public function handle(OrderUpdateEvent $event)
    {
//        $report = $event->order;
//        Log::info("order update event response ==============>", $report);
//
//        $order = Order::where('binance_order_id', $report['orderId'])
//            ->first();
//        if (!isset($order)) {
//            Log::alert('SUSPICIOUS_TRADE');
//        } else {
//
//            $transaction = $order->transactions()
//                ->where('type', 'WITHDRAW')
//                ->first();
//
//            // todo must withdraw - not work currently
//
//            $payment = $transaction->payment;
//        $payment->update([
//            'withdraw_id' => $id
//        ]);


//        }


    }
}
