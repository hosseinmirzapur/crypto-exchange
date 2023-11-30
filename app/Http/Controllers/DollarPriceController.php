<?php

namespace App\Http\Controllers;

use App\Models\DollarPrice;
use App\Models\Market;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use function App\Helpers\successResponse;

class DollarPriceController extends Controller
{
    public function show()
    {
        return \App\Helpers\successResponse(
            ['price' => DollarPrice::getLastPrice()]
        );
    }

    public function index()
    {
        $prices = DB::table('dollar_prices', 'a')
            ->leftJoin('dollar_prices', function (\Illuminate\Database\Query\Builder $join) {
                $join->on('dollar_prices.id', '=', DB::raw('a.id - 1'));
            })->select('a.*', DB::raw('a.changes/dollar_prices.price as percent'))
            ->latest()
            ->paginate(\request('size', 10));

        $c = collect([
            "type" => "success",
            "status" => 200,
            "message" => "success",
        ]);
        return response()->json($c->merge($prices));
    }

    public function store(Request $request)
    {
        $request->validate(['price' => 'required']);

        $last_price = DollarPrice::latest()->first()->price ?? 0;

        $dollar = DollarPrice::create([
            'price' => $request->price,
            'changes' => $request->price - $last_price
        ]);

        Cache::put('tether-price', [
            'price' => $request->price,
            'changes' => $request->price - DollarPrice::query()
                    ->where('created_at', '>', now()->subDay())
                    ->value('price')
        ]);

        Market::query()
            ->where('name', 'USDTTOMAN')
            ->update(['price' => $request->price]);

        return successResponse(
            $dollar
        );
    }

    public function tether()
    {
        return \App\Helpers\successResponse(
            ['price' => \cache('tether-price')['price'] ?? 1],
        );
    }
}
