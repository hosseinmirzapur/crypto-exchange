<?php

namespace App\Models;

use App\Traits\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DollarPrice extends Model
{
    use HasFactory, Filter;

    protected $fillable = ['price', 'changes'];

    public function scopeChange(Builder $query, $change)
    {
        if (!is_array($change) || !isset($change[1])) {
            return;
        }
        return $query->whereChange('price', '>', $change[0])
            ->where('change', '<', $change[1]);
    }

    public function scopeLastPrice(Builder $query)
    {
        return $query->latest();
    }

    public static function getLastPrice()
    {
        return cache()->remember('dollar-price', now()->addMinutes(10), function () {
            return static::lastPrice()
                    ->value('price')
                ?? 30000;
        }
        );
    }

    protected static function booted()
    {
        self::created(function ($dollarPrice) {
            Market::query()
                ->where('name', 'USDTTOMAN')
                ->update(
                    [
                        'price' => $dollarPrice->price
                    ]
                );
        });
    }


}
