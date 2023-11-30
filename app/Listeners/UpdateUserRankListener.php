<?php

namespace App\Listeners;

use App\Events\UpdateUserRankEvent;
use App\Models\Rank;
use App\Models\Trade;

class UpdateUserRankListener
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
     * @param UpdateUserRankEvent $event
     * @return void
     */
    public function handle(UpdateUserRankEvent $event)
    {
        $user = $event->user;
        $trades = Trade::query()
            ->selectRaw('amount * price_toman as cost')
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereDate('created_at', '>', now()->subMonthsNoOverflow(3))
            ->get();
        $sum = $trades->sum('cost');

        $rank = Rank::query()
            ->where('criterion', '>=', $sum)
            ->orWhere('id', 4)
            ->orderBy('criterion')
            ->first();

        $user->rank()
            ->associate($rank);
        $user->save();
    }
}
