<?php

namespace App\Listeners;

use App\Events\UpdateBalancesEvent;
use App\Models\Admin;
use App\Models\Coin;
use App\Notifications\UsdtCreditNotification;

class UpdateBalancesListener
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
     * @param UpdateBalancesEvent $event
     * @return void
     */
    public function handle(UpdateBalancesEvent $event)
    {
        foreach ($event->balances as $coin => $balance) {

            if (!isset($balance['available'])) {
                continue;
            }

            Coin::where('code', $coin)
                ->update(
                    [
                        'amount' => $balance['available']
                    ]
                );
            $maxBalance = 100;
            if ($coin === 'USDT' && $balance['available'] < $maxBalance) {
                $admin = Admin::find(1);
                $admin->notify(new UsdtCreditNotification($maxBalance));
            }
        }
    }
}
