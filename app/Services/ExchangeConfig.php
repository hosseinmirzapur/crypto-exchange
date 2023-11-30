<?php


namespace App\Services;


use App\Classes\Binance;

class ExchangeConfig
{

    protected $exchangeInfo;

    public function __construct($exchangeInfo = null)
    {
        $this->exchangeInfo = $exchangeInfo ?? Binance::exchangeInfo();
    }

    public function getCoinInfo(): array
    {
        return $this->exchangeInfo['symbols'];
    }

    public function getLimits() : array
    {
        $limits = $this->exchangeInfo['rateLimits'];
        $array_limit = [];

        foreach ($limits as $limit) {
            $k = $this->changeLimitTypeKey($limit['rateLimitType'])
                . '_' . $this->changeLimitIntervalKey($limit['interval'])
                . $limit['intervalNum'];

            $v = $limit['limit'];

            $array_limit[$k] = $v;
        }


        return $array_limit;
    }

    protected function changeLimitTypeKey($key): string
    {
        switch ($key) {
            case "REQUEST_WEIGHT":
                return "WEIGHT";
            case "RAW_REQUESTS":
                return "RAW";
            default :
                return $key;
        }
    }

    protected function changeLimitIntervalKey($key): string
    {
        switch ($key) {
            case "MINUTE":
                return "m";
            case "SECOND":
                return "s";
            case "DAY":
                return "d";
            default :
                return $key;
        }
    }

}
