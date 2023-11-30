<?php

namespace App\Http\Controllers;

use App\Classes\Binance;
use App\Events\RealTimePriceEvent;
use App\Http\Resources\RealTimePriceResource;
use App\Models\Coin;
use Illuminate\Http\Request;

class RealTimePriceController extends Controller
{

    public function index()
    {
        $binance = new \App\Classes\Binance(true);
        $binance->binance
            ->miniTicker(function ($api, $ticker) {

                $a = RealTimePriceResource::collection($ticker);
                dd($a);
//                event(new RealTimePriceEvent($ticker));
            });
    }

    public function show($coin)
    {
        $coin = Coin::where('code', $coin)
            ->orWhere('label', $coin)
            ->first();
        return $coin;
//
//        try {
//                (new Binance())->kline($coin->getMarket(),'1m');
//        } catch (\Exception $e) {
//            return \App\Helpers\errorResponse($e->getMessage(), 400);
//        }
    }
}
