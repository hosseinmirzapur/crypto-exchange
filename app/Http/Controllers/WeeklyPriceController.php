<?php

namespace App\Http\Controllers;

use App\Jobs\WeeklyPriceJob;
use App\Models\Coin;
use App\Models\WeeklyPrice;
use function App\Helpers\successResponse;

class WeeklyPriceController extends Controller
{

    public function store()
    {
        $coins = Coin::where('status', 'ACTIVATED')
            ->where('code', '!=', 'TOMAN')
            ->get();


        foreach ($coins as $key => $coin) {

            WeeklyPriceJob::dispatch($coin)
                ->delay(now()->addSeconds($key * 20));

        }
        return successResponse(
            'SUCCESS',
            201
        );

    }

    public function index()
    {
        return \App\Helpers\successResponse(
            WeeklyPrice::with('coin')
            ->get()
        );
    }

}
