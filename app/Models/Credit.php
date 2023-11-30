<?php

namespace App\Models;

use App\Exceptions\CustomException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Credit extends Pivot
{
    use HasFactory;

    protected $table = 'credits';

    protected $fillable = ['credit', 'blocked', 'coin_id'];
    protected $casts = [
        'credit' => 'float',
        'blocked'=> 'float'
    ];

    public function add($value)
    {
        $this->credit += $value;
        $this->save();
        return $this;
    }

    public function subtract($value)
    {
        throw_if(!$this->hasEnoughCredit($value), CustomException::class, trans('messages.NOT_ENOUGH_CREDIT'), 400);
        $this->credit -= $value;
        $this->save();
        return $this;
    }

    public function hasEnoughCredit($amount) {
        return  ($this->credit - $this->blocked) - $amount >= 0;
    }

    public function blockCredit($cost)
    {
        throw_if(!$this->hasEnoughCredit($cost), CustomException::class, trans('messages.NOT_ENOUGH_CREDIT'), 400);
        $this->blocked += $cost;
        $this->save();
    }

    /**
     * @param $cost
     * @param bool $both credit and block
     */
    public function unBlockCredit($cost, $both = true)
    {
        $this->blocked -= $cost;
        if ($both) {
            $this->subtract($cost);
        }
        $this->save();
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class);
    }

    public function markets()
    {
        return $this->hasMany(Market::class, 'coin_id', 'coin_id');
    }
}
