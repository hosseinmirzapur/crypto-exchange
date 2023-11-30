<?php


namespace App\Traits;


use App\Models\Config;

trait AutoTrade
{


    public function isAutoTrade()
    {
        $config = Config::key('AUTO_TRADE')
            ->first();
        $now = now()->timestamp;

        return (!isset($config) || $config->value === 'AUTO') &&
            cache('ORDERS_LIMIT') < $now &&
            cache('WEIGHT_LIMIT') < $now;
    }

}
