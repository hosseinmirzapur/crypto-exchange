<?php

namespace App\Http\Controllers;

use App\Http\Resources\TradeResource;
use App\Models\Market;
use App\Models\Trade;
use App\Models\User;
use Carbon\Carbon;
use function App\Helpers\current_user;
use function App\Helpers\customRound;

class TradeController extends Controller
{

    public function index()
    {
        return TradeResource::collection(Trade::filter(['type', 'created_atFrom', 'created_atTo', 'price', 'amount'])
            ->when(current_user() instanceof User, function ($query) {
                $query->whereHas('order', function ($query) {
                    $query->where('user_id', '=', current_user()->id);
                });
            })->whereHas('order.market.coin', function ($query) {
                if (request()->has('coin')) {
                    $query->where('code', '=', request('coin'));
                }
            })->when(request()->has('market'), function ($query) {
                $query->whereHas('order.market', function ($query) {
                    $query->where('name', request('market'));
                });
            })->with(
                [
                    'order.market.coin',
                    'order.market.quote'
                ]
            )->latest()
            ->paginate(10)
        )->additional([
            'message' => trans('messages.SUCCESS'),
            'type' => 'success',
            'status' => 'success',
        ]);
    }

    public function daily(Market $market)
    {
        abort_if(!request()->has('from'), 404);

        $from = Carbon::createFromFormat('Y-m-d\TH:i:s', request('from'));
        $to = $from->copy()->addDay();

        $dates = \App\Models\Trade::query()->hourly()
            ->where('created_at', '>', $from->toDateTimeString())
            ->whereDate('created_at', '<', $to->toDateTimeString())
            ->whereHas('order.market', function ($query) use ($market) {
                $query->where('id', $market->id);
            })
            ->pluck('sum(amount)', 'date');

        $periods = \Carbon\CarbonPeriod::since($from)->hours(1)->until($to)->toArray();
        $date_array = [];
        $sum = 0;


        foreach ($periods as $period) {
            $key = $period->format('Y-m-d H');
            $date_array[] = [
                'key' => $period->format('H'),
                'value' => isset($dates[$key]) ? customRound($dates[$key]) : 0
            ];
            $sum = $sum + ($dates[$key] ?? 0);
        }

        return \App\Helpers\successResponse(
            [
                'items' => $date_array,
                'sum' => customRound($sum)
            ],
            200,
        );
    }

    public function monthly(Market $market)
    {
        abort_if(!request()->has('from') || !request()->has('to'), 404);

        $from = Carbon::createFromFormat('Y-m-d\TH:i:s', request('from'));
        $to = Carbon::createFromFormat('Y-m-d\TH:i:s', request('to'));

        $dates = \App\Models\Trade::daily()
            ->whereDate('created_at', '>', $from)
            ->whereDate('created_at', '<', $to)
            ->whereHas('order.market', function ($query) use ($market) {
                $query->where('id', $market->id);
            })
            ->pluck('sum(amount)', 'date');

        $periods = \Carbon\CarbonPeriod::since($from)->days(1)->until($to)->toArray();
        $date_array = [];
        $sum = 0;


        foreach ($periods as $period) {
            $key = $period->format('Y-m-d');
            $date_array[] = [
                'key' => $key,
                'value' => isset($dates[$key]) ? customRound($dates[$key], 8) : 0
            ];
            $sum = $sum + ($dates[$key] ?? 0);
        }

        return \App\Helpers\successResponse(
            [
                'items' => $date_array,
                'sum' => customRound($sum)
            ],
            200,
        );
    }

    public function yearly(Market $market)
    {
        abort_if(!request()->has('from') || !request()->has('to'), 404);

        $from = Carbon::createFromFormat('Y-m-d\TH:i:s', request('from'));
        $to = Carbon::createFromFormat('Y-m-d\TH:i:s', request('to'));

        $dates = \App\Models\Trade::monthly()
            ->whereDate('created_at', '>', $from)
            ->whereDate('created_at', '<', $to)
            ->whereHas('order.market', function ($query) use ($market) {
                $query->where('id', $market->id);
            })
            ->pluck('sum(amount)', 'date');
        $periods = \Carbon\CarbonPeriod::since($from)->month(1)->until($to)->toArray();
        $date_array = [];
        $sum = 0;


        foreach ($periods as $period) {
            $key = $period->format('Y-m');
            $date_array[] = [
                'key' => $key,
                'value' => isset($dates[$key]) ? customRound($dates[$key], 8) : 0
            ];
            $sum = $sum + ($dates[$key] ?? 0);
        }

        return \App\Helpers\successResponse(
            [
                'items' => $date_array,
                'sum' => customRound($sum)
            ],
            200,
        );
    }

    public function abstract(Market $market = null)
    {
        $now = Carbon::now();
        $H = $now->copy()->subHour();
        $D = $now->copy()->subDay();
        $W = $now->copy()->subWeek();
        $M = $now->copy()->subMonth();
        $Y = $now->copy()->subYear();

        $HRecord = Trade::where('created_at', '<', $now->startOfDay())
            ->where('created_at', '>', $H->startOfDay())
            ->when(isset($market), function ($query) use ($market) {
              $query->whereHas('order.market', function ($query) use ($market) {
                  $query->where('id', $market->id);
              });
            })
            ->sum('amount');

        $DRecord = Trade::abstract($now, $D, $market)
            ->sum('amount');

        $WRecord = Trade::abstract($now, $W, $market)
            ->sum('amount');

        $MRecord = Trade::abstract($now, $M, $market)
            ->sum('amount');


        $YRecord = Trade::abstract($now, $Y, $market)
            ->sum('amount');

        return \App\Helpers\successResponse(
            [
                'hour' => customRound($HRecord),
                'day' => customRound($DRecord),
                'week' => customRound($WRecord),
                'month' => customRound($MRecord),
                'year' => customRound($YRecord),
            ],
        );

    }

    public function count()
    {
        return \App\Helpers\successResponse(
            ['count' => Trade::filter(['status'])->count('id')],
            200,
        );
    }

    public function gain(Market $market = null)
    {
        $gain = Trade::when(isset($market), function ($query) use ($market) {
                $query->whereHas('order.market', function ($query) use ($market) {
                    $query->where('id', $market->id);
                });
            })
            ->sum('gain');

        return \App\Helpers\successResponse(
            $gain,
        );

    }

    public function monthlyGain(Market $market = null)
    {
        abort_if(!request()->has('from') || !request()->has('to'), 404);

        $from = Carbon::createFromFormat('Y-m-d\TH:i:s', request('from'));
        $to = Carbon::createFromFormat('Y-m-d\TH:i:s', request('to'));

        $dates = \App\Models\Trade::daily()
            ->whereDate('created_at', '>', $from)
            ->whereDate('created_at', '<', $to)
            ->pluck('sum(gain)', 'date');
        $periods = \Carbon\CarbonPeriod::since($from)->days(1)->until($to)->toArray();
        $date_array = [];
        $sum = 0;


        foreach ($periods as $period) {
            $key = $period->format('Y-m-d');
            $date_array[] = [
                'key' => $key,
                'value' => isset($dates[$key]) ? customRound($dates[$key], 8) : 0
            ];
            $sum = $sum + ($dates[$key] ?? 0);
        }

        return \App\Helpers\successResponse(
            [
                'items' => $date_array,
                'sum' => customRound($sum)
            ],
            200,
        );
    }
}
