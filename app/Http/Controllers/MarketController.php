<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketResource;
use App\Models\Coin;
use App\Models\CryptoNetwork;
use App\Models\Market;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function App\Helpers\successResponse;

class MarketController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'user:admin', 'can:ability,"UPDATE_MARKET"'])
            ->only(['update']);
        $this->middleware(['can:ability,"CREATE_MARKET"'])->only('store');
    }

    public function network(Market $market)
    {
        return successResponse(
            CryptoNetwork::whereHas('coin.markets', function ($query) use ($market) {
                $query->where('id', $market->id);
            })->filter(['status', 'deposit_status', 'withdraw_status'])
                ->get()
        );

    }


    public function index()
    {
        $coin = Coin::where('code', request('quote','TOMAN'))
            ->first();

        $coins = [];
        if (\request()->has('marketType')) {
            $coins = Coin::where('code', 'like', '%'.\request('marketType').'%')
                ->orWhere('label', 'like', '%'.\request('marketType').'%')
                ->pluck('id')
            ;
        }

        return MarketResource::collection(
            Market::filter(['status', 'name'])
                ->when(\request()->has('marketType'), function ($query) use ($coins) {
                    $query->whereIn('coin_id', $coins);
                })
                ->whereHas('quote', function (Builder $query) use ($coin) {
                    $query->where('id', $coin->id);
                })->with(
                    ['coin', 'quote']
                )
                ->paginate(\request('size', 10))
        )->additional([
            'message' => 'success',
            'type' => 'success',
            'status' => '200'
        ]);
    }

    public function activeCoin()
    {
        return successResponse(
            Market::with(
                ['coin', 'quote']
            )->where('status', 'ACTIVATED')
                ->get()
        );
    }

    public function show(Market $market)
    {
        return successResponse(
            new MarketResource($market)
        );
    }

    public function update(Request $request, Market $market)
    {
        $attributes = $request->validate([
            'status' => ['required', Rule::in(Market::STATUS)]
        ]);

        $market->update($attributes);
        return successResponse(
            $market
        );
    }


    public function marketListWithGain()
    {
        return successResponse(
            Market::query()
                ->selectRaw('SUM(trades.gain) as gain, coin_id')
                ->where('quote_id', 1)
                ->leftJoin('orders', function ($join) {
                    $join->on('orders.market_id','markets.id');
                })
                ->leftJoin('trades', function ($join) {
                    $join->on('trades.order_id','orders.id');
                })->groupBy('coin_id')
                ->get()
                ->makeHidden(['buyingPrice', 'sellingPrice'])
        );
    }


}
