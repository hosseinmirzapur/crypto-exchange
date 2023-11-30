<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Jobs\CheckOnlinePaymentJob;
use App\Models\OnlinePayment;
use App\Models\Transaction;
use App\Services\Vandar;
use Illuminate\Http\Request;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;

class OnlinePaymentPortalController extends Controller
{

    public function store(Transaction $transaction)
    {
        if ($transaction->status !== 'PENDING') {
            return redirect(env('VANDAR_FRONT_CALLBACK') . DIRECTORY_SEPARATOR . $transaction->id);
        }

        $payment = $transaction->payment;
        $transaction->update(['status' => 'SENT']);
        CheckOnlinePaymentJob::dispatch($transaction)
            ->delay(now()->addMinutes(30));

        if ($payment->portal === 'vandar') {
            $vandar = new Vandar();
            $token = $vandar->getToken($transaction->amount, $transaction->account->card_number);
            $transaction->payment()
                ->update([
                    'token' => $token
                ]);
            return redirect($vandar->getPortalAddress($token));

        } else {
            $invoice = (new Invoice())
                ->amount($transaction->amount)
                ->detail('name', 'zarinpal payment test');
            return Payment::purchase($invoice, function ($drive, $transactionId) use ($transaction) {
                $transaction->payment()
                    ->update(
                        [
                            'token' => $transactionId
                        ]
                    );
            }
            )->pay()
                ->render();
        }


    }

    public function update(Request $request)
    {
        $token = $request->has('Authority') ?
            $request->query('Authority') :
            $request->query('token');


        $payment = OnlinePayment::where('token', $token)
            ->latest()
            ->first();
        $transaction = $payment->transaction;

        if ($transaction->status !== 'SENT') {
            return redirect(env('VANDAR_FRONT_CALLBACK') . DIRECTORY_SEPARATOR . $transaction->id);
        }

        if ($payment->gateway === 'vandar') {

            if ($request->query('payment_status') !== 'OK') {

                $transaction->api_status = 'REJECTED';
                $transaction->reject();

                return redirect(env('VANDAR_FRONT_CALLBACK') . DIRECTORY_SEPARATOR . $transaction->id);
            }


            try {
                $vandar = new Vandar();
                $response = $vandar->verify($token);
                $payment->update([
                    'commission' => $response['wage'],
                    'transaction_id' => $response['transId']
                ]);
                $transaction->accept();
                $transaction
                    ->user
                    ->addCredit($transaction->amount);


            } catch (CustomException $e) {
                $transaction->reject();
            }


        } else {
            if ($request->query('Status') !== 'OK') {

                $transaction->api_status = 'REJECTED';
                $transaction->reject();

                return redirect(env('VANDAR_FRONT_CALLBACK') . DIRECTORY_SEPARATOR . $transaction->id);
            }

            try {
                $receipt = Payment::amount($transaction->amount)
                    ->transactionId($token)
                    ->verify();

                $payment->update(
                    [
                        'ref_number' => $receipt->getReferenceId()
                    ]
                );
                $transaction->accept();
                $transaction
                    ->user
                    ->addCredit($transaction->amount);

            } catch (InvalidPaymentException $exception) {
                $transaction->reject();
                $msg = $exception->getMessage();
            }
        }
        $query = isset($msg) ? "?msg={$msg}" : '';

        $transaction->notify();

        return redirect(env('VANDAR_FRONT_CALLBACK') . DIRECTORY_SEPARATOR . $transaction->id . $query);
    }
}
