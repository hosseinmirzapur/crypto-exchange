<?php

namespace App\Jobs;

use App\Classes\Binance;
use App\Models\Coin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WeeklyPriceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Coin
     */
    private $coin;

    /**
     * Create a new job instance.
     *
     * @param Coin $coin
     */
    public function __construct(Coin $coin)
    {
        //
        $this->coin = $coin;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $price = [];

        if ($this->coin->code === 'USDT') {

            $prices = \App\Models\DollarPrice::where('created_at', '>', now()->subWeek())
                ->pluck('created_at', 'price');

            $price = $prices->map(function ($item) {
                return $item->timestamp;
            })->flip();

            if (empty($price)) {
                $price[now()->timestamp] = 0;
            }


        } else {
            $prices = (new Binance(false))
                ->weeklyPrices($this->coin);

            foreach ($prices as $k=>$v) {
                $price[$k] = $v['close'];
            }
        }


        if (!empty($prices)) {

            $this->coin
                ->weeklyPrice()
                ->updateOrCreate(
                    ['coin_id' => $this->coin->id],
                    [
                        'coin_id' => $this->coin->id,
                        'prices' => $price
                    ]
                );

        }
    }
}
