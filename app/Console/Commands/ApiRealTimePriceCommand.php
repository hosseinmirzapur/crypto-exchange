<?php

namespace App\Console\Commands;

use App\Events\RealTimePriceEvent;
use App\Models\Coin;
use App\Models\DollarPrice;
use App\Models\Market;
use App\Models\Rank;
use Illuminate\Console\Command;

class ApiRealTimePriceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apiRealTimePrice:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run real time price v1.1';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $markets = Market::whereHas('quote', function ($query) {
            $query->where('code', 'USDT');
        })->where('status', '!=', 'DISABLED_BY_BINANCE')
            ->get();

        $toman = coin::where('code', 'TOMAN')->first();

        $markets_array = $markets->map(function ($i) {
            return $i->name;
        })->toArray();

        $timestamp = 0;

        (new \App\Classes\Binance(false))
            ->binance
            ->tickerSymbols(
                $markets_array,
                function ($api, $symbol, $ticker) use ($markets, $toman, &$timestamp) {
                    $tetherPrice = $this->getTetherPrice();

                    $ranks = cache()->remember('ranks', now()->addMinutes(30), function () {
                        return Rank::pluck('fee')->toArray();
                    });

                    $constantFee = cache()->remember('constant-fee', now()->addSeconds(10), function () {
                        return Coin::query()->select(['constant_fee', 'code'])
                            ->pluck('constant_fee', 'code');
                    });

                    if ($timestamp < now()->timestamp) {
                        $ticker += [
                            'USDTUSDT' => [
                                "market" => 'USDTUSDT',
                                "priceChange" => $tetherPrice['changes'],
                                "percentChange" => $tetherPrice['changes'] / $tetherPrice['price'] * 100,
                                'price' => 1
                            ]
                        ];

                        if (!empty($ticker)) {
                            foreach ($markets as $market) {
                                if (!isset($ticker[$market->name])) {
                                    continue;
                                }
                                $item = $ticker[$market->name];
                                $market->update(['price' => $item['price']]);
                                if ($market->quote->id !== $toman->id) {
                                    Market::query()->where('coin_id', $market->coin_id)
                                        ->where('quote_id', $toman->id)
                                        ->update(['price' => $item['price'] * $tetherPrice['price']]);

                                }
                            }
                        }

                        $prices_item = [];
                        foreach ($ticker as $price) {
                            $price_array = [];
                            $price_array["priceChange"] = (float)$price["priceChange"];
                            $price_array["percentChange"] = (float)$price["percentChange"];

                            $price_array['coin'] = substr($price['market'], 0, -4);
                            $price_array['buying'] = (1 + (0.001 + $constantFee[$price_array['coin']] ?? 0.003 + $ranks[0])) * $price['price'];
                            $price_array['selling'] = (1 - (0.001 + $constantFee[$price_array['coin']] ?? 0.003 + $ranks[0])) * $price['price'];
                            $price_array['tomanBuying'] = $price_array['buying'] * $tetherPrice['price'];
                            $price_array['tomanSelling'] = $price_array['selling'] * $tetherPrice['price'];
                            $prices_item[] = $price_array;
                        }

                        event(new RealTimePriceEvent($prices_item));

                        $timestamp = now()->timestamp + 5;
                    }

                });

    }

    protected function getTetherPrice()
    {
        return \cache()->remember('tether-price', now()->addSeconds(30), function () {
            $d2 = DollarPrice::query()
                ->latest()
                ->value('price');

            $d1 = DollarPrice::query()
                ->where('created_at', '>', now()->subDay())
                ->value('price');

            return [
                'changes' => $d2 - $d1,
                'price' => $d2
            ];
        });

    }
}
