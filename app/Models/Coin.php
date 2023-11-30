<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Coin extends Model
{
    use HasFactory;

    public $timestamps = false;
    const STATUS = ['ACTIVATED', 'DISABLED', 'DISABLED_BY_BINANCE'];
    const CAN_NOT_ORDERED = ['USDT'];
    protected $hidden = ['amount'];

    protected $fillable = ['code', 'label', 'name', 'status', 'amount', 'constant_fee'];

    protected $appends = ['logo'];

    public function getLogoAttribute()
    {
        $path = '/coins_logo/' . $this->code . '.png';
        $logo = '/logo/logo.png';
        return Storage::exists($path) ?
            Storage::url($path) :
            Storage::url($logo);
    }

    public function credits() {
        return $this->belongsToMany(Coin::class, 'credits');
    }

    public function markets()
    {
        return $this->hasMany(Market::class);
    }

    public function getMarket($quoteAsset = "USDT")
    {
        return $this->code . $quoteAsset;
    }

    public function networks()
    {
        return $this->hasMany(CryptoNetwork::class);
    }

    public function weeklyPrice()
    {
        return $this->hasOne(WeeklyPrice::class);
    }

    public function canOrdered()
    {
        return !in_array($this->code, static::CAN_NOT_ORDERED);
    }

    public function getIsTomanAttribute() {
        return $this->code === 'TOMAN';
    }
}
