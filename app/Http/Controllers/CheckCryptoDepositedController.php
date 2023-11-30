<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use function App\Helpers\successResponse;

class CheckCryptoDepositedController extends Controller
{
    public function update(Transaction $transaction)
    {
        throw_if(
            !$transaction->isAutoTrade(),
            CustomException::class,
            trans('messages.AUTO_TRADE_IS_OFF')
        );

        throw_if(
            !$transaction->isType('DEPOSIT') || $transaction->payment_method !== 'CRYPTO',
            CustomException::class,
            trans('messages.MUST_CRYPTO')
        );

        DB::transaction(
            function () use ($transaction) {
                $transaction = $transaction->checkCryptoDeposited();
                $transaction
                    ->user
                    ->addCredit(
                        $transaction
                            ->amount,
                        $transaction->coin
                    );
            }
        );
        $transaction->notify();

        return successResponse(
            $transaction
        );
    }

}
