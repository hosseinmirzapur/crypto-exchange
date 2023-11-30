<?php

namespace App\Console\Commands;

use App\Events\RealTimePriceEvent;
use App\Models\Coin;
use App\Models\Config;
use App\Models\DollarPrice;
use App\Models\Market;
use App\Models\Rank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RealTimePriceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'realTimePrice:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'run real time price';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $markets = Market::whereHas('quote', function ($query) {
            $query->where('code', 'USDT');
        })->with('coin')
            ->where('status', 'ACTIVATED')
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
                    $tetherPrice = \cache()->remember('tether-price', now()->addSeconds(5), function () {
                        return $this->getTetherPrice();
                    });
                    $ranks = cache()->remember('ranks', now()->addMinutes(30), function () {
                        return Rank::pluck('fee')->toArray();
                    });
                    $dollarPrice = cache()->remember('dollar-price', now()->addMinutes(30), function () {
                        return DollarPrice::latest()->value('price') ?? 30000;
                    });
                    $constantFee = cache()->rememberForever('CONSTANT_FEE', function () {
                        $fee = Config::typeOf('MAIN')
                            ->where('key', 'CONSTANT_FEE')
                            ->first();
                        return !empty($fee) ? $fee->value : 0.003;
                    });

                    if ($timestamp < now()->timestamp) {

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
                                        ->update(['price' => $item['price'] * $tetherPrice['price'] * $dollarPrice]);

                                }
                            }
                        }


                        $prices = [];
                        $ticker += [
                            'USDTTOMAN' => [
                                "market" => 'USDTTOMAN',
                                "priceChange" => $tetherPrice['change'],
                                "percentChange" => $tetherPrice['change'] / $tetherPrice['price'] * 100,
                                'price' => $tetherPrice['price']
                            ]
                        ];

                        $prices_item = [];
                        foreach ($ticker as $price) {

                            $price_array = $price;

                            $price_array['buying'] = (1 + (0.001 + $constantFee + $ranks[0])) * $price['price'];
                            $price_array['selling'] = (1 - (0.001 + $constantFee + $ranks[0])) * $price['price'];
                            $price_array['tomanBuying'] = $price_array['buying'] * $tetherPrice['price'] * $dollarPrice;
                            $price_array['tomanSelling'] = $price_array['selling'] * $tetherPrice['price'] * $dollarPrice;
                            $prices_item[] = $price_array;
                        }

                        event(new RealTimePriceEvent($prices_item));

                        $timestamp = now()->timestamp + 5;
                    }

                });

    }

    protected function getTetherPrice()
    {
        $response = Http::withHeaders(['accept' => 'application/json'])
            ->get('https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=usd&include_24hr_change=true');
        if ($response->successful()) {
            $res = json_decode($response, true);
            return [
                'price' => $res['tether']['usd'],
                'change' => $res['tether']['usd_24h_change']
            ];
        }

        return 1;
    }
}
