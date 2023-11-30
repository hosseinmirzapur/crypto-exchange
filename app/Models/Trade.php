<?php

namespace App\Models;

use App\Traits\Filter;
use App\Traits\Periodic;
use App\Traits\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Trade extends Model
{
    use HasFactory, Filter, Periodic;


    protected $fillable = [
        'amount',
        'price',
        'type',
        'commission',
        'binance_trade_id',
        'commission_asset',
        'commission_amount',
        'price_toman',
        'gain'
    ];
    protected $casts = [
        'price_toman' => 'float',
        'gain' => 'float'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // todo put it in ...
    public static function getPriceWeightedAverage($array, $type = 'price')
    {
        $t = 0;
        $b = 0;
        foreach ($array as $item) {
            $t += $item->qty * $item->$type;
            $b += $item->qty;
        }

        return $t / $b;
    }

}
