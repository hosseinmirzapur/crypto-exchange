<?php


namespace App\Classes;


use App\Models\Coin;
use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use function App\Helpers\successResponse;

class Binance
{
    public $binance;
    public $rateLimits;
    public $rateLimiter;
    const THRESHOLD = 10;

    public function __construct($test = null)
    {
        $this->binance = new MyBinanceApi(
            \config('services.binance.key'),
            \config('services.binance.secret'),
            isset($test) ? $test : \config('services.binance.test', true),
        );
        $this->binance->useServerTime();
        sleep(1);

//
        $this->setRateLimiter();

    }

    private function setRateLimiter()
    {

        $this->rateLimits = cache()
            ->remember(
                'BINANCE_LIMITS',
                now()->addMinutes(60),
                function () {

                    $this->rateLimits = Config::where('type', 'BINANCE')->pluck('value', 'key')
                        ->toArray();

                    if (empty($this->rateLimits)) {
                        Config::updateBinanceConfig($this->binance->exchangeInfo());
                        $this->rateLimits = Config::where('type', 'BINANCE')
                            ->pluck('value', 'key')
                            ->map(function ($item) {
                                return $item * (1 - (static::THRESHOLD));
                            })->toArray();
                    }
                }
            );


    }

    public function __destruct()
    {
        $limits = $this->binance
            ->getLimitsFromHeader();


        foreach ($limits as $key => $limit) {

            if ($key === 'WEIGHT_m1') {
                if (\cache('WEIGHT_LIMIT') && \cache('WEIGHT_LIMIT') < now()->timestamp) {
                    Cache::put('WEIGHT_LIMIT', now()->addSeconds(60));
                }
            }

            if ($key === 'ORDERS_s10') {
                if (\cache('ORDERS_LIMIT') && \cache('ORDERS_LIMIT') < now()->timestamp) {
                    Cache::put('ORDERS_LIMIT', now()->addSeconds(10));
                }
            }

            if ($key === 'ORDERS_d1') {
                if (\cache('ORDERS_LIMIT') && \cache('ORDERS_LIMIT') < now()->timestamp) {
                    Cache::put(
                        'ORDERS_LIMIT',
                        now()->endOfDay()->timestamp
                    );
                }
            }

        }


    }

    /**
     * USER_DATA
     * weight 10
     * @return array
     * @throws \Exception
     */
    public function account()
    {
        return $this->binance->account();
    }

    public function getBalances()
    {
        return successResponse(
            $this->account()['balances'],
        );
    }

    /**
     * @param $symbol
     * @return string
     */
    public static function price($symbol)
    {
        return (new static())->binance
            ->price(strtoupper($symbol));
    }

    public function prices()
    {
        return $this->binance->prices();
    }

    /*
     * {
  "symbol": "BTCUSDT",
  "orderId": 28,
  "orderListId": -1, //Unless OCO, value will be -1
  "clientOrderId": "6gCrw2kRUAF9CvJDGP16IP",
  "transactTime": 1507725176595,
  "price": "0.00000000",
  "origQty": "10.00000000",
  "executedQty": "10.00000000",
  "cummulativeQuoteQty": "10.00000000",
  "status": "FILLED",
  "timeInForce": "GTC",
  "type": "MARKET",
  "side": "SELL",
  "fills": [
    {
      "price": "4000.00000000",
      "qty": "1.00000000",
      "commission": "4.00000000",
      "commissionAsset": "USDT"
    },
    {
      "price": "3999.00000000",
      "qty": "5.00000000",
      "commission": "19.99500000",
      "commissionAsset": "USDT"
    },
    {
      "price": "3998.00000000",
      "qty": "2.00000000",
      "commission": "7.99600000",
      "commissionAsset": "USDT"
    },
    {
      "price": "3997.00000000",
      "qty": "1.00000000",
      "commission": "3.99700000",
      "commissionAsset": "USDT"
    },
    {
      "price": "3995.00000000",
      "qty": "1.00000000",
      "commission": "3.99500000",
      "commissionAsset": "USDT"
    }
  ]
}*/

    /**
     *TRADE
     * weight 1
     *
     * {
     * "symbol": "BTCUSDT",
     * "orderId": 28,
     * "orderListId": -1, //Unless OCO, value will be -1
     * "clientOrderId": "6gCrw2kRUAF9CvJDGP16IP",
     * "transactTime": 1507725176595,
     * "price": "0.00000000",
     * "origQty": "10.00000000",
     * "executedQty": "10.00000000",
     * "cummulativeQuoteQty": "10.00000000",
     * "status": "FILLED",
     * "timeInForce": "GTC",
     * "type": "MARKET",
     * "side": "SELL"
     * }
     *
     *
     * @param $symbol
     * @param $quantity
     * @param bool $isQuote
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderBuy($symbol, $quantity, $isQuote = false)
    {
        $flags = [];
        if ($isQuote) {
            $flags['isQuoteOrder'] = true;
        }
        return $this->binance
            ->marketBuy($symbol, $quantity, $flags);
    }

    public function orderSell($symbol, $quantity, $isQuote = false)
    {
        $flags = [];
        if ($isQuote) {
            $flags['isQuoteOrder'] = true;
        }
        return $this->binance
            ->marketSell($symbol, $quantity, $flags);
    }

    public function orderStatus($symbol, $order_id)
    {
        return $this->binance
            ->orderStatus($symbol, $order_id);
    }

    /**
     * TRADE
     * weight 1
     *
     * {
     * "symbol": "LTCBTC",
     * "origClientOrderId": "myOrder1",
     * "orderId": 4,
     * "orderListId": -1, //Unless part of an OCO, the value will always be -1.
     * "clientOrderId": "cancelMyOrder1",
     * "price": "2.00000000",
     * "origQty": "1.00000000",
     * "executedQty": "0.00000000",
     * "cummulativeQuoteQty": "0.00000000",
     * "status": "CANCELED",
     * "timeInForce": "GTC",
     * "type": "LIMIT",
     * "side": "BUY"
     * }
     *
     *
     * @param $coin
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder($coin, $orderId)
    {
        return successResponse(
            $this->binance
                ->cancel($coin, $orderId)
        );
    }


    public static function exchangeInfo()
    {
        $binance = new static(false);
        return $binance->binance->exchangeInfo();
    }


    /**********************************
     * not work on test
     *
     * */

    public function tradeFee()
    {
        return $this->binance
            ->commissionFee();
    }

    /**
     * find all types of network
     */
    public function coins()
    {
        $coins = $this->binance
            ->coins();
        return $coins;
    }

    public function setTimeOffset($diff = 0)
    {
        $this->binance->setTimeOffset($diff);
    }

    /**
     * USER_DATA
     * weight 1
     * @param $coin
     * @param null $network
     * @return \Illuminate\Http\JsonResponse
     */
    public function depositAddress($coin, $network = null)
    {
        return successResponse(
            $this->binance
                ->depositAddress($coin, $network)
        );
    }

    /**
     * USER_SATA
     * weight 1
     *
     *                 ,{
     * "id":"7213fea8e94b4a5593d507237e5a555b"
     * }
     *
     * @param $coin
     * @param $address
     * @param $amount
     * @param $network
     * @param null $tag
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdraw($coin, $amount, $network, $address, $tag = null)
    {
        return $this->binance
            ->withdraw($coin, $address, $amount, $tag, '', false, $network);
    }

    /**
     * @param null $asset 'BTC'
     * @param array $params
     * @return array
     */
    public function withdrawHistory($asset = null, $params = [])
    {
        $history = $this->binance
            ->withdrawHistory($asset, $params);
        return $history['withdrawList'];
    }

    /**
     * @param null $asset 'BTC'
     * @param array $params
     * @return array
     */
    public function depositHistory($asset = null, $params = [])
    {
        return $this->binance
            ->depositHistory($asset, $params);
    }

    public function weeklyPrices(Coin $coin)
    {
        return $this->binance
            ->candlesticks(
                $coin->getMarket(),
                '4h',
                100,
                Carbon::now()->subWeek()->valueOf(),
                Carbon::now()->valueOf()
            );
    }


    /*****************************
     * websockets
     * */


    /**
     * @param $coins array
     * @param $interval string
     * @throws \Exception
     */
    public function chartSocket($coins, $interval)
    {
        $this->binance->chart($coins, $interval, function ($api, $symbol, $chart) {
            echo "{$symbol} chart update\n";
            echo "<pre>";
            print_r($chart);
            echo "</pre>";
        });
    }

    public function kline($symbols, $interval)
    {
        $this->binance->kline($symbols, $interval, function ($api, $symbol, $chart) {
            echo "{$symbol} chart update\n";
            echo "<pre>";
            print_r($chart);
            echo "</pre>";
        });
    }

    public function book()
    {
        $this->binance->bookTicker(function ($api, $ticker) {
            print_r($ticker);
        });
    }

    public function trades($market)
    {
        $this->binance->trades($market, function ($api, $symbol, $trades) {
            echo "{$symbol} trades update" . PHP_EOL;
            print_r($trades);
        });
    }

    public function miniTicker()
    {
        $this->binance
            ->miniTicker(
                function ($api, $ticker) {
                    print_r($ticker);
                }
            );
    }

    public function tradesAll()
    {
        $this->binance->tradesAll(function ($api, $trades) {
            echo " trades update" . PHP_EOL;
            print_r($trades);
        });
    }

    public function __call($name, $arguments)
    {
        echo $name;
        return true;
    }

    public static function __callStatic($name, $arguments)
    {
        return $name;
//        return true;
    }

}
