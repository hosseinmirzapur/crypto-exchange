<?php

namespace App\Http\Controllers;

use App\Models\Coin;
use App\Models\CryptoNetwork;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function App\Helpers\successResponse;

class CryptoNetworkController extends Controller
{
    public function index()
    {
        return successResponse(
            Coin::with('networks')->get()
        );
    }

    public function show(Coin $coin)
    {
        return successResponse(
            $coin->networks()
                ->filter(['status', 'deposit_status', 'withdraw_status'])
                ->get()
        );
    }

    public function store()
    {
        CryptoNetwork::updateConfig();
        return successResponse();

    }

    public function update(Request $request, CryptoNetwork $cryptoNetwork)
    {
        $request->validate(
            [
                'status' => [
                    Rule::in(CryptoNetwork::STATUS),
                    function ($attribute, $value, $fail) use ($cryptoNetwork) {
                        if ($value === 'ACTIVATED' && $cryptoNetwork->withdraw_status !== 'ACTIVATED') {
                            $fail(trans('validation.status', ['attribute' => 'وضعیت برداشت', 'status' => 'فعال']));
                        }
                        if ($value === 'ACTIVATED' && $cryptoNetwork->deposit_status !== 'ACTIVATED') {
                            $fail(trans('validation.status', ['attribute' => 'وضعیت واریز', 'status' => 'فعال']));
                        }
                        if ($value === 'ACTIVATED' && empty($cryptoNetwork->address)) {
                            $fail(trans('validation.exists', ['attribute' => 'ادرس کیف پول']));
                        }
                    }
                ]
            ]
        );


        return successResponse(
            $cryptoNetwork->update(['status' => $request->status])
        );
    }

    public function address(CryptoNetwork $cryptoNetwork)
    {
        return successResponse(
            [
                'address' => $cryptoNetwork->address,
                'address_image' => (string)$cryptoNetwork->addressImage,
                'memo' => $cryptoNetwork->memo,
                'memo_image' => (string)$cryptoNetwork->memoImage,
            ]
        );
    }

    public function updateAddress(CryptoNetwork $cryptoNetwork)
    {
        $cryptoNetwork->update(request()->only(['address', 'memo']));
        return successResponse(
            $cryptoNetwork
        );
    }


}
