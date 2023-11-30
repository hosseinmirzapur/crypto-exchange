<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Config;
use App\Models\OnlinePayment;
use App\Models\Transaction;
use App\Services\Vandar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use function App\Helpers\successResponse;

class VandarController extends Controller
{
    public function login(Request $request)
    {
        $attributes = $request->validate([
            'mobile' => 'required|string',
            'password' => 'required',

        ]);

        (new Vandar())
            ->login($attributes['mobile'], $attributes['password']);

        return successResponse();
    }


    public function send(Transaction $transaction)
    {
        abort_if($transaction->status !== 'PENDING', 404);
        $transaction->load('payment');
        $vandar = new Vandar();
        $portal = $vandar->getPortalAddress($transaction->payment->token);

        return redirect($portal);
    }

    public function callback(Request $request)
    {
        if (!$request->has('token')) {
            throw new \App\Exceptions\CustomException(trans('messages.UNAUTHORIZED'), 402);
        }

        $transaction = Transaction::with('payment')
            ->whereHasMorph(
                'payment',
                [OnlinePayment::class],
                function ($query) {
                    $query->where('token', \request('token'));
                })->firstOrFail();


        try {
            $vandar = new \App\Services\Vandar();
            $preVerifyResponse = $vandar->preVerify($request->token);
            if (request('payment_status') !== 'OK') {
                $transaction->status = 'FAILED';
                if (strtolower(\hash('sha256', $transaction->account->card_number)) === strtolower($preVerifyResponse['cid'])) {
                    $transaction->status = 'CONFLICT';
                }

                $transaction->payment
                    ->update(
                        [
                            'transaction_id' => $preVerifyResponse['transId'],
                            'commission' => $preVerifyResponse['wage'] ?? 0
                        ]
                    );

                return redirect(env('VANDAR_FRONT_CALLBACK') . '/' . $transaction->id);
            }

            $res = $vandar->verify($request->token);

            $transaction->payment
                ->update(
                    [
                        'transaction_id' => $preVerifyResponse['transId'],
                        'commission' => $res['wage'],
                        'ref_number' => $res['ref_number']
                    ]
                );
            return redirect(env('VANDAR_FRONT_CALLBACK') . '/' . $transaction->id);
        } catch (CustomException $e) {
            $transaction->update(['status' => 'FAILED']);
            return redirect(env('VANDAR_FRONT_CALLBACK') . '/' . $transaction->id);
        }
    }
}
