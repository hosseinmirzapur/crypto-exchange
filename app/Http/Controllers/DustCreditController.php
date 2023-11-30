<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Coin;
use App\Models\Market;
use App\Services\Price\Fee;
use App\Services\Price\Price;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function App\Helpers\current_user;

class DustCreditController extends Controller
{
    public function index()
    {

        $btcMarket = Market::where('name', 'BTCUSDT')
            ->first();

        $dustInUsdt = $btcMarket->price * 0.001;

        $credits = current_user()
            ->credits()
            ->selectRaw('credits.*, markets.price')
            ->leftJoin('markets', function ($join) {
                $join->on('credits.coin_id', 'markets.coin_id')
                    ->where('markets.price', '>', 0);
            })->whereRaw('?/markets.price > (credits.credit - credits.blocked)', [$dustInUsdt])
            ->get();


        return \App\Helpers\successResponse(
            $credits->map(
                function ($i) {
                    $i->price = (new Price())
                        ->withPrice($i->price)
                        ->withFee(new Fee(['constant', 'rank', 'binance']))
                        ->getPrice();
                    return $i;
                }
            )
        );


    }

    public function update(Request $request)
    {
        $btcMarket = Market::where('name', 'BTCUSDT')
            ->first();

        $dustInUsdt = $btcMarket->price * 0.001;
        $tomanCoin = Coin::where('code', 'TOMAN')->first();

        $dusts = current_user()
            ->credits()
            ->selectRaw('credits.*, markets.price')
            ->leftJoin('markets', function ($join) {
                $join->on('credits.coin_id', 'markets.coin_id')
                    ->where('markets.price', '>', 0);
            })->whereRaw('?/markets.price > (credits.credit - credits.blocked)', [$dustInUsdt])
            ->get('id');


        throw_if(empty($request->dusts), CustomException::class, trans('messages.API_CONNECTION_PROBLEM'));

        $tomanSummation = 0;

        foreach ($dusts as $dust) {
            if (in_array($dust->id, \request('dust'))) {
                $price = (new Price())
                    ->withPrice($dust->price)
                    ->withFee(new Fee(['constant', 'rank', 'binance']))
                    ->changeToToman()
                    ->getPrice();
                $tomanSummation += $dust->credit * $price;
            }
        }

        current_user()->addCredit($tomanSummation, $tomanCoin);

        return \App\Helpers\successResponse(
        );

    }
}
