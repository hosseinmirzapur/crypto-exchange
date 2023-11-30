<?php

namespace App\Models;

use App\Exceptions\CustomException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class WeeklyPrice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'prices' => 'array'
    ];

    public function coin()
    {
        return $this->belongsTo(Coin::class);
    }

    public function getHistoryFromBinance()
    {

    }

    public function getTetherHistoryFromCoinGeeko()
    {

    }
}
