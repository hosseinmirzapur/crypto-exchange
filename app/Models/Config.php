<?php

namespace App\Models;

use App\Services\ExchangeConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Config extends Model
{
    protected $fillable = ['type', 'key', 'value'];
    const TYPE = ['MAIN', 'BINANCE', 'VANDAR'];

    public function scopeFindBykey(Builder $query, $key)
    {
        return $query->whereKey($key);
    }

    public function scopeKey($query, $key)
    {
        return $query->where('key', $key);
    }

    public function scopeTypeOf(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeBinance(Builder $query, $key)
    {
        return $query->whereKey($key)
            ->whereType('BINANCE');
    }

    public function scopeMain(Builder $query, $key)
    {
        return $query->where('key', $key)
            ->where('type', 'MAIN');
    }

    public static function constantFee()
    {
        $fee = static::typeOf('MAIN')
            ->where('key', 'CONSTANT_FEE')
            ->first();

        return !empty($fee) ? $fee->value : 0.001;
    }

    public static function updateBinanceConfig($exchange_info = null)
    {
        $binance_info = new ExchangeConfig($exchange_info);

        $limits = $binance_info->getLimits();

        foreach ($limits as $key => $limit) {
            $limit_array[] = static::updateOrCreate(
                [
                    'type' => 'BINANCE',
                    'key' => $key
                ],
                [
                    'type' => 'BINANCE',
                    'key' => $key,
                    'value' => $limit
                ]
            );

        }

        $coins = Coin::whereNotIn('code', ['TOMAN', 'USDT'])
            ->where('status', '!=', 'DISABLED')
            ->get();

        $toman = Coin::where('code', 'TOMAN')->first();
        $usdt = Coin::where('code', 'USDT')->first();

        DB::table('markets')
            ->where('status', '!=', 'DISABLED')
            ->where('name', '!=', 'USDTTOMAN')
            ->update(['status' => 'DISABLED_BY_BINANCE']);

        $market = $usdt->markets()
            ->where('quote_id', $toman->id)
            ->first();

        if (!isset($market)) {
            $usdt->markets()
                ->create(
                    [
                        'name' => $usdt->code . 'TOMAN',
                        'quote_id' => $toman->id,
                        'max_amount' => 200000,
                        'min_amount' => 5,
                        'amount_step' => 1,
                        'status' => 'ACTIVATED',
                        'price' => 0
                    ]
                );
        }


        $symbols = $binance_info->getCoinInfo();


        foreach ($coins as $coin) {
            $market = $coin->code . 'USDT';

            if (!isset($symbols[$market]) || $symbols[$market]['status'] !== 'TRADING') {
                continue;
            }

            $filter_array = [];
            $filter_array['max_amount'] = 0;
            $filter_array['min_amount'] = 0;
            $filter_array['amount_step'] = 0;

            foreach ($symbols[$market]['filters'] as $filter) {
                if ($filter['filterType'] === 'MIN_NOTIONAL') {
                    $filter_array['min_notional'] = $filter['minNotional'];
                }


                if ($filter['filterType'] === 'MARKET_LOT_SIZE' || $filter['filterType'] === 'LOT_SIZE') {
                    $filter_array['max_amount'] = ($filter_array['max_amount'] < $filter['maxQty'] && $filter_array['max_amount'] !== 0) ? (float)$filter_array['max_amount'] : (float)$filter['maxQty'];
                    $filter_array['min_amount'] = ($filter_array['min_amount'] > $filter['minQty'] && $filter_array['min_amount'] !== 0) ? (float)$filter_array['min_amount'] : (float)$filter['minQty'];
                    $filter_array['amount_step'] = ($filter_array['amount_step'] > $filter['stepSize'] && $filter_array['amount_step'] !== 0) ? (float)$filter_array['amount_step'] : (float)$filter['stepSize'];

                }
            }

            $markets = $coin->markets;

            $market = $markets->firstWhere('quote_id', $toman->id);

            if (!isset($market)) {
                $coin->markets()
                    ->create(
                        array_merge([
                            'name' => $coin->code . 'TOMAN',
                            'quote_id' => $toman->id,
                            'status' => 'ACTIVATED',
                            'price' => 0
                        ],
                            $filter_array)
                    );
            } elseif ($market->status !== 'DISABLED') {
                $market->update(
                        array_merge([
                            'name' => $coin->code . 'TOMAN',
                            'quote_id' => $toman->id,
                            'status' => 'ACTIVATED',
                        ],
                            $filter_array)
                    );
            }


            $market = $markets->firstWhere('quote_id', $usdt->id);


            if (!isset($market)) {
                $coin->markets()
                    ->create(
                        array_merge([
                            'name' => $coin->code . 'USDT',
                            'quote_id' => $usdt->id,
                            'status' => 'ACTIVATED',
                            'price' => 0,
                        ],
                            $filter_array)
                    );
            } elseif ($market->status !== 'DISABLED') {
                $market->update(
                    array_merge([
                        'name' => $coin->code . 'USDT',
                        'quote_id' => $usdt->id,
                        'status' => 'ACTIVATED'
                    ],
                        $filter_array)
                );
            }
        }

        Config::updateOrCreate(
            [
                'type' => 'SCHEDULE',
                'key' => 'EXCHANGE_INFO'
            ],
            [
                'type' => 'SCHEDULE',
                'key' => 'EXCHANGE_INFO',
                'value' => now()
            ]
        );

    }

    public
    static function vandar($response)
    {
        return static::updateOrCreate(
            [
                'type' => 'VANDAR',
                'key' => 'TOKEN'
            ],
            [
                'type' => 'VANDAR',
                'key' => 'TOKEN',
                'value' => $response['access_token']
            ],
        );
    }


}
