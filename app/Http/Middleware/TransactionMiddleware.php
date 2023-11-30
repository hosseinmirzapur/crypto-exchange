<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomException;
use App\Models\Coin;
use App\Models\Transaction;
use Closure;
use Illuminate\Http\Request;

class TransactionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $coin = Coin::findOrFail($request->coin_id);



        if ($coin->code === 'TOMAN' && $request->payment_method === 'CRYPTO') {
            throw new CustomException(
                trans('messages.CONNECTION_PROBLEM'),
                400
            );
        }
        if ($coin->code !== 'TOMAN' && !in_array($request->payment_method, Transaction::PAYMENT_METHOD)) {
            throw new CustomException(
                trans('messages.CONNECTION_PROBLEM'),
                400
            );
        }


        return $next($request);
    }
}
