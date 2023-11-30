<?php

namespace App\Models;

use App\Classes\Binance;
use App\Services\Price\Fee;
use App\Services\Price\Price;
use App\Traits\ChangeToToman;
use App\Traits\Filter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    use HasFactory, Filter, ChangeToToman;

    const STATUS = ['ACTIVATED', 'DISABLED', 'DISABLED_BY_BINANCE'];
    protected $fillable = ['name', 'coin_id', 'quote_id', 'status', 'min_notional', 'max_amount', 'min_amount', 'custom_max_amount', 'custom_min_amount', 'amount_step', 'price'];
    const QUOTE_COINS = ['TOMAN', 'USDT'];
    public $timestamps = false;

    protected $with = ['quote', 'coin'];

    protected $hidden = ['price'];

    protected $appends = ['buyingPrice', 'sellingPrice', 'final_min_amount', 'final_max_amount'];

    protected $casts = [
        'min_amount' => 'float',
        'max_amount' => 'float'
        ];

    public function getModifiedNameAttribute()
    {
        if ($this->quote->code === 'TOMAN') {
            return $this->coin->getMarket();
        }
        return $this->name;
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class);
    }

    public function quote()
    {
        return $this->belongsTo(Coin::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getMinNotionalAttribute($value)
    {
        if ($this->quote->isToman) {
            return (new Price())
                ->withPrice($value)
                ->changeToToman()
                ->getPrice();
        }
        return $value;
    }

    public function getBuyingPriceAttribute()
    {
        $fee = new Fee(['constant', 'rank', 'binance'],$this->coin);
        $price = (new Price())
            ->withPrice($this->price)
            ->withFee($fee);

        return $price->getPrice();
    }

    public function getSellingPriceAttribute()
    {
        $fee = new Fee(['constant', 'rank', 'binance'], $this->coin);
        $price = (new Price('SELL'))
            ->withPrice($this->price)
            ->withFee($fee);

        return $price->getPrice();
    }

    public function getModifiedMarketAttribute()
    {
        $coin = Coin::where('code', 'USDT')->first();
        return $this->where('coin_id', $this->coin_id)
            ->where('quote_id', $coin->id)
            ->firstOrFail();
    }

    public function getFinalMaxAmountAttribute()
    {
        if (!isset($this->max_amount) || !isset($this->custom_max_amount)) {
            return 0;
        }

        return $this->getMaxAmountLimitation();

    }

    public function getFinalMinAmountAttribute()
    {
        if (!isset($this->min_amount) || !isset($this->custom_min_amount)) {
            return 0;
        }

        return $this->getMinAmountLimitation();
    }

    public function isOrderable()
    {
        $coin = $this->coin()->value('code');
        return !in_array(
            $coin,
            static::QUOTE_COINS
        );
    }

    public function updateConfig($symbols)
    {
        if (!isset($symbols[$this->name]) || $symbols[$this->name]['status'] !== 'TRADING') {
            $this->update(['status' => 'DISABLED_BY_BINANCE']);
            return;
        }

        $filters = $symbols[$this->name]['filters'];
        $filter_array = [];

        foreach ($filters as $filter) {
            if ($filter['filterType'] && $filter['filterType'] === 'MIN_NOTIONAL') {
                $filter_array['min_notional'] = $filter['minNotional'];
            }

            if ($filter['filterType'] && $filter['filterType'] === 'MARKET_LOT_SIZE') {
                $filter_array['max_amount'] = $filter['maxQty'];
                $filter_array['min_amount'] = $filter['minQty'];
                $filter_array['amount_step'] = $filter['stepSize'];
            }
        }
        $this->update($filter_array);
    }

    public function getMaxAmountLimitation()
    {
        if ($this->max_amount > $this->custom_max_amount) {
            return $this->custom_max_amount > 0 ? $this->custom_max_amount : $this->max_amount;
        } else {
            return $this->max_amount > 0 ? $this->max_amount : $this->custom_max_amount;
        }
    }

    public function getMinAmountLimitation()
    {
        if ($this->min_amount >= $this->custom_min_amount && $this->min_amount > 0) {
            return $this->min_amount;
        } elseif ($this->min_amount <= $this->custom_min_amount && $this->custom_min_amount > 0) {
            return $this->custom_min_amount;
        } else {
            return 0;
        }
    }

    public function price($forceUsdt = false)
    {
        if ($this->coin->code === 'USDT') {
            return $forceUsdt ? 1 : $this->toToman(1);
        }

        if ($this->quote->isToman) {
            $market_name = $this->coin
                ->getMarket();

            $usdtPrice = Binance::price($market_name);

            return $forceUsdt ? $usdtPrice : $this->toToman($usdtPrice);
        }

        return Binance::price($this->name);
    }

    public function findMaxLimit()
    {
        $max = $this->max_amount;

        if ($this->custom_max_amount < $max) {
            $max = $this->custom_max_amount;
        }

        return $max;
    }

    public function findMinLimit()
    {
        $min = $this->min_amount;
        if ( $this->custom_min_amount > $min ) {
            $min = $this->custom_min_amount;
        }

        return $min;
    }

}
