<?php


namespace App\Classes;


use Binance\BinanceApiException;
use Binance\InvalidArgumentException;
use Binance\RuntimeException;
use Illuminate\Support\Facades\Log;

class MyBinanceApi extends \Binance\API
{

    /**
     * @var array
     */
    public $ticker = [];
    protected $caOverride = true;

    /**
     * @return array|array[]
     */
    public function getLimitsFromHeader()
    {
        $headers = $this->lastRequest['header'] ?? [];
        $limits = [];

        foreach ($headers as $key => $header) {
            if ($key === 'x-mbx-used-weight-1m' || $key === 'X-MBX-USED-WEIGHT-1M') {
                $limits['WEIGHT_m1'] = $header;
            }
            if ($key === 'x-mbx-order-count-10s' || $key === 'X-MBX-ORDER-COUNT-10S') {
                $limits['ORDERS_s10'] = $header;
            }
            if ($key === 'x-mbx-order-count-1d' || $key === 'X-MBX-ORDER-COUNT-1D') {
                $limits['ORDERS_d1'] = $header;
            }
        }

        return $limits;

    }

    public function setTimeOffset($diff)
    {
        $this->info['timeOffset'] = $diff;
    }

    /*
     * {
  "e": "executionReport",        // Event type
  "E": 1499405658658,            // Event time
  "s": "ETHBTC",                 // Symbol
  "c": "mUvoqJxFIILMdfAW5iGSOW", // Client order ID
  "S": "BUY",                    // Side
  "o": "LIMIT",                  // Order type
  "f": "GTC",                    // Time in force
  "q": "1.00000000",             // Order quantity
  "p": "0.10264410",             // Order price
  "P": "0.00000000",             // Stop price
  "F": "0.00000000",             // Iceberg quantity
  "g": -1,                       // OrderListId
  "C": "",                       // Original client order ID; This is the ID of the order being canceled
  "x": "NEW",                    // Current execution type
  "X": "NEW",                    // Current order status
  "r": "NONE",                   // Order reject reason; will be an error code.
  "i": 4293153,                  // Order ID
  "l": "0.00000000",             // Last executed quantity
  "z": "0.00000000",             // Cumulative filled quantity
  "L": "0.00000000",             // Last executed price
  "n": "0",                      // Commission amount
  "N": null,                     // Commission asset
  "T": 1499405658657,            // Transaction time
  "t": -1,                       // Trade ID
  "I": 8641984,                  // Ignore
  "w": true,                     // Is the order on the book?
  "m": false,                    // Is this trade the maker side?
  "M": false,                    // Ignore
  "O": 1499405658657,            // Order creation time
  "Z": "0.00000000",             // Cumulative quote asset transacted quantity
  "Y": "0.00000000",             // Last quote asset transacted quantity (i.e. lastPrice * lastQty)
  "Q": "0.00000000"              // Quote Order Qty
}*/


    protected function executionHandler(\stdClass $json)
    {
        Log::info(json_encode($json));
        return [
            "symbol" => $json->s,
            "side" => $json->S,
            "orderType" => $json->o,
            "quantity" => $json->q,
            "orderPrice" => $json->p,
            "executionType" => $json->x,
            "orderStatus" => $json->X,
            "rejectReason" => $json->r,
            "orderId" => $json->i,
            "clientOrderId" => $json->c,
            "orderTime" => $json->T,
            "eventTime" => $json->E,
            "marketPrice" => $json->L,
            "commission_asset" => $json->N,
            "commission_amount" => $json->n
        ];
    }

    public function tradesAll(callable $callback)
    {

        $loop = \React\EventLoop\Factory::create();
        $react = new \React\Socket\Connector($loop);
        $connector = new \Ratchet\Client\Connector($loop, $react);


        // $this->info[$symbol]['tradesCallback'] = $callback;

        $endpoint = strtolower('!bookTicker');
        $this->subscriptions[$endpoint] = true;

        $connector($this->returnWsEndpoint() . '!bookTicker')->then(function ($ws) use ($callback, $loop, $endpoint) {
            $ws->on('message', function ($data) use ($ws, $callback, $loop, $endpoint) {
                if ($this->subscriptions[$endpoint] === false) {
                    //$this->subscriptions[$endpoint] = null;
                    $loop->stop();
                    return; //return $ws->close();
                }
                $json = json_decode($data, true);
//                $symbol = $json['s'];
//                $price = $json['p'];
//                $quantity = $json['q'];
//                $timestamp = $json['T'];
//                $maker = $json['m'] ? 'true' : 'false';
//                $trades = [
//                    "price" => $price,
//                    "quantity" => $quantity,
//                    "timestamp" => $timestamp,
//                    "maker" => $maker,
//                ];
                // $this->info[$symbol]['tradesCallback']($this, $symbol, $trades);
                call_user_func($callback, $this, $json);
            });
            $ws->on('close', function ($code = null, $reason = null) use ($loop) {
                // WPCS: XSS OK.
                $loop->stop();
                $msg = "trades() WebSocket Connection closed! ({$code} - {$reason}).";
                throw new RuntimeException($msg);
            });
        }, function ($e) use ($loop) {
            // WPCS: XSS OK.
            $loop->stop();
            $msg = "trades() Could not connect: {$e->getMessage()}.";
            throw new RuntimeException($msg);
        });

        $loop->run();
    }

    /**
     * @param $symbols
     * @param callable|null $callback
     */
    public function tickerSymbols($symbols, callable $callback = null)
    {
        if (is_null($callback)) {
            throw new InvalidArgumentException("You must provide a valid callback");
        }
        if (!is_array($symbols)) {
            $symbols = [
                $symbols,
            ];
        }

        $loop = \React\EventLoop\Factory::create();
        $react = new \React\Socket\Connector($loop);
        $connector = new \Ratchet\Client\Connector($loop, $react);
        foreach ($symbols as $symbol) {
            $endpoint = strtolower($symbol) . '@ticker';
            $this->subscriptions[$endpoint] = true;
            $connector($this->returnWsEndpoint() . $endpoint)->then(function ($ws) use ($callback, $symbol, $loop, $endpoint) {
                $ws->on('message', function ($data) use ($ws, $loop, $callback, $endpoint, $symbol) {
                    if ($this->subscriptions[$endpoint] === false) {
                        $loop->stop();
                        return;
                    }
                    $json = json_decode($data);
                    $this->ticker[$symbol] = $this->tickerReturnHandler($json);
                    call_user_func($callback, $this, $symbol, $this->ticker);
                });
                $ws->on('close', function ($code = null, $reason = null) use ($symbol, $loop) {
                    // WPCS: XSS OK.
                    $loop->stop();
                    $msg = "kline({$symbol}) WebSocket Connection closed! ({$code} - {$reason})";
                    throw new RuntimeException($msg);
                });
            }, function ($e) use ($loop, $symbol) {
                // WPCS: XSS OK.
                $loop->stop();
                $msg = "kline({$symbol})) Could not connect: {$e->getMessage()}";
                throw new RuntimeException($msg);
            });
        }
        $loop->run();
    }

    protected function tickerReturnHandler($json)
    {
        return [
            "market" => $json->s,
            "priceChange" => $json->p,
            "percentChange" => $json->P,
            'price' => $json->c
        ];
    }

    protected function returnWsEndpoint(): string
    {
        return $this->useTestnet ? $this->streamTestnet : $this->stream;
    }

    public function marketSell($symbol, $quantity, $flags = [])
    {
        return $this->order("SELL", $symbol, $quantity, 0, "MARKET", $flags);
    }

    /**
     * candlesticks get the candles for the given intervals
     * 1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
     *
     * $candles = $api->candlesticks("BNBBTC", "5m");
     *
     * @param $symbol string to query
     * @param $interval string to request
     * @param $limit int limit the amount of candles
     * @param $startTime string request candle information starting from here
     * @param $endTime string request candle information ending here
     * @return array containing the response
     * @throws BinanceApiException
     */
    public function candlesticks(string $symbol, string $interval = "5m", int $limit = null, $startTime = null, $endTime = null)
    {
        if (!isset($this->charts[$symbol])) {
            $this->charts[$symbol] = [];
        }

        $opt = [
            "symbol" => $symbol,
            "interval" => $interval,
        ];

        if ($limit) {
            $opt["limit"] = $limit;
        }

        if ($startTime) {
            $opt["startTime"] = $startTime;
        }

        if ($endTime) {
            $opt["endTime"] = $endTime;
        }

        $response = $this->httpRequest("v3/klines", "GET", $opt);

        if (is_array($response) === false) {
            return [];
        }

        if (count($response) === 0) {
            $msg = "warning: klines returned empty array, usually a blip in the connection or server";
            throw new RuntimeException($msg);
        }

        $ticks = $this->chartData($symbol, $interval, $response);
        $this->charts[$symbol][$interval] = $ticks;
        return $ticks;
    }


}
