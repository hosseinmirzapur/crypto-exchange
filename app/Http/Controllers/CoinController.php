<?php

namespace App\Http\Controllers;

use App\Http\Resources\CoinResource;
use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function App\Helpers\current_user;
use function App\Helpers\errorResponse;
use function App\Helpers\successResponse;

class CoinController extends Controller
{

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'code' => 'required|unique:coins,label',
            'max_amount' => 'nullable',
            'min_amount' => 'nullable',

            'status' => ['sometimes', Rule::in(Coin::STATUS)
            ]
        ]);
        return Coin::create($attributes);
    }

    public function index()
    {
        $status = \request('status');
        $withdrawStatus = \request('withdraw_status');
        $depositStatus = \request('deposit_status');

        $coins = Coin::query()->when(
            isset($status),
            function ($query) use ($status) {
                $query->where('status', $status);
            })->with([
            'networks' => function ($builder) {
                if (\request()->has('deposit_status')) {
                    $builder->where('deposit_status', \request('deposit_status'));
                }
                if (\request()->has('withdraw_status')) {
                    $builder->where('withdraw_status', \request('withdraw_status'));
                }
                if (!current_user()->isAdmin()) {
                    $builder->where('status', 'ACTIVATED');
                }
            }
        ])
            ->paginate(10);

        return CoinResource::collection($coins)
            ->additional([
                'type' => 'success',
                'status' => 200,
            ]);
    }

    public function update(Request $request, Coin $coin)
    {
        $attributes = $request->validate([
            'code' => 'required|unique:coins,label',
            'label' => 'nullable',
            'status' => ['sometimes', Rule::in(Coin::STATUS)],
            'constant_fee' => ['sometimes']
        ]);

        $coin
            ->fill($attributes)
            ->save();

        return successResponse(
            $coin
        );
    }

    // todo must check in binance
    public function activate(Coin $coin)
    {
        if ($coin->status === "ACTIVATED")
            return errorResponse("ALREADY_ACTIVATED", 400);
        $coin->update(['status' => "ACTIVATED"]);
        return successResponse(
            $coin
        );
    }

    public function deactivate(Coin $coin)
    {
        if ($coin->status === "DISABLED")
            return errorResponse("ALREADY_DISABLED", 400);
        $coin->update(['status' => "DISABLED"]);
        return successResponse(
            $coin
        );
    }


}
