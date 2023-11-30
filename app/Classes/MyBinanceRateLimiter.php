<?php


namespace App\Classes;


use App\Exceptions\BinanceRateLimitException;
use App\Models\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class MyBinanceRateLimiter
{
    private $api;
    private $requests_rate_limit;
    private $orders_rate_limit;
    private $orders_daily_limit;
    private $requests_rate_limit_count;
    private $orders_rate_limit_count;
    private $orders_daily_limit_count;

    public function __construct(MyBinanceApi $api, $rate_limits)
    {
        $this->api = $api;
        $this->init($rate_limits);


    }

    private function init($rate_limits)
    {

        foreach ($rate_limits as $k => $v) {
            switch ($k) {
                case 'WEIGHT_m1' :
                    $this->requests_rate_limit = round($v * 0.95);
                    break;
                case 'ORDERS_s10' :
                    $this->orders_rate_limit = round($v * 0.9);
                    break;
                case 'ORDERS_d1' :
                    $this->orders_daily_limit = round($v * 0.98);
                    break;
            }
        }

        $header = $this->api->getHeader();
        $this->requests_rate_limit_count = $header['x-mbx-used-weight-1m'];
        $this->orders_rate_limit_count = $header['x-mbx-order-count-10s'];
        $this->orders_daily_limit_count = $header['x-mbx-order-count-1d'];

    }

    public function check()
    {
        if ($this->requests_rate_limit >= $this->requests_rate_limit_count) {
            $this->requestsPerMinuteExceeded();
        }
        if ($this->orders_rate_limit >= $this->orders_rate_limit_count) {
            $this->ordersPerTenSecondExceeded();
        }
        if ($this->orders_daily_limit >= $this->orders_daily_limit_count) {
            $this->ordersPerDayExceeded();
        }
    }

    private function requestsPerMinuteExceeded()
    {

    }

    private function ordersPerTenSecondExceeded()
    {

    }

    private function ordersPerDayExceeded()
    {

    }

}
