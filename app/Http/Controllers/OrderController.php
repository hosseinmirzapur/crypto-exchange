<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Services\Price\Fee;
use App\Services\Price\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\Helpers\current_user;
use function App\Helpers\successResponse;

class OrderController extends Controller
{
    public function store(OrderRequest $request)
    {
        $request->validated();
        $market = $request->market;

        $fee = new Fee(['constant', 'binance', 'rank'], $market->coin);
        $price = (new Price($request->type))
            ->withFee($fee)
            ->withMarket($market)
            ->getPrice();

        if (!current_user()->hasCredit(
            $request->type === 'BUY' ?
                $market->quote :
                $market->coin,
            $request->type === 'BUY' ?
                $request->amount * $price :
                $request->amount)
        ) {
            throw new CustomException(trans("messages.NOT_ENOUGH_CREDIT"), 400);
        }


        $order = DB::transaction(function () use ($request, $market, $price, $fee) {

            $order = new Order([
                'type' => $request->type,
                'amount' => $request->amount,
                'price' => $price,
                'fee' => $fee->fee(),
                'status' => 'PENDING'
            ]);
            $order->market()
                ->associate($market)
                ->save();

            $order->blockCreditAfterOrder();

            return $order;
        });



        DB::transaction(function () use ($order) {
            if ($order->type === "SELL") {
                $order->sell();
            } else {
                $order->buy();
            }
        });

        return successResponse(
            $order
        );
    }

    public function update(Order $order, $type, Request $request)
    {
        throw_if(!$order->isPending(), CustomException::class, trans('messages.PROCESSING_FINISHED'));

        $order = DB::transaction(function () use ($order, $type) {
            if ($type === 'accept') {
                $order->accept();
                $order->handleAcceptedOrder(true);
            }

            if ($type == 'reject') {
                $order->reject();
                $order->handleRejectedOrder();
            }

            return $order;
        });

        return successResponse(
            $order
        );
    }

    public function index()
    {
        $with = ['market.coin', 'market.quote'];
        if (current_user()->isAdmin()) {
            $with[] = 'user';
        }

        return OrderResource::collection(
            Order::query()->when(!current_user()->isAdmin(), function ($query) {
                $query->where('user_id', current_user()->id);
            })->filter(['type', 'created_atFrom', 'created_atTo', 'status','created_at'])
                ->whereHas('market.coin', function ($query) {
                    if (request()->has('coin')) {
                        $query->where('code', '=', request('coin'));
                    }
                })->when(\request()->has('email'), function ($query) {
                    $user_ids = User::where('email', 'like', '%' . \request('email') . '%')->pluck('id');
                    $query->whereIn('user_id', $user_ids);
                })
                ->with($with)
                ->when(
                    !\request()->has('orderBy'),
                    function ($query) {
                        $query->latest();
                    }
                )
                ->paginate(\request('size',10))
        )->additional([
            'message' => 'SUCCESS',
            'type' => 'success',
            'status' => 'success',
        ]);
    }


    public function cancel(Order $order)
    {
        throw_if($order->status !== 'PENDING', CustomException::class, trans('messages.MUST_PENDING'));
        $order->update(['status' => 'REJECTED_BY_USER']);
        $order->handleRejectedOrder();
        return successResponse(
            $order
        );
    }



    public function show(Order $order)
    {
        return successResponse(
            $order
        );
    }

    public function count()
    {
        return successResponse(
            ['count' => Order::filter(['status'])->count('id')],
            200,
        );
    }

}
