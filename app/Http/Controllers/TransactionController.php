<?php

namespace App\Http\Controllers;

use App\Events\SendOtpEvent;
use App\Exceptions\CustomException;
use App\Exports\TransactionsExport;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Jobs\CheckTxIdJob;
use App\Models\Admin;
use App\Models\Coin;
use App\Models\CryptoPayment;
use App\Models\ManualPayment;
use App\Models\OnlinePayment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Vandar;
use App\Traits\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use function App\Helpers\current_user;
use function App\Helpers\errorResponse;
use function App\Helpers\successResponse;

class TransactionController extends Controller
{
    use Status;

    public function store(TransactionRequest $request, $type)
    {
        $type = strtoupper($type);

        $attributes = $request->validated();

        $user = current_user();
        $coin = Coin::find($request->coin_id);

        if (
            $type === 'WITHDRAW' &&
            !$user->hasCredit($coin, $attributes['amount'])
        ) {
            return errorResponse(trans("messages.NOT_ENOUGH_CREDIT"), 400);
        }

        if ($type === 'WITHDRAW' && !current_user()->checkOtp('code', 'WITHDRAW_OTP')) {
            throw new CustomException(trans('messages.WRONG_CODE'), 400);
        }

        $attributes['fee'] = 0;
        $attributes['status'] = "PENDING";

        if ($coin->isToman) {

            if ($type === 'WITHDRAW') {
                $transaction = DB::transaction(function () use ($attributes, $coin) {
                    $user = current_user();
                    $transaction = $user
                        ->transactions()
                        ->create($attributes + ['type' => 'WITHDRAW']);

                    if ($user->hasCredit($coin, $transaction->amount)) {
                        $credit = $user->credit($coin);
                        $credit->blockCredit($transaction->amount);
                    }

                    return $transaction;
                });

                return successResponse(
                    $transaction,
                );
            } else {

                $transaction = DB::transaction(function () use ($request, $attributes, $type, $user, $coin) {

                    if ($request->payment_method !== 'ONLINE') {
                        $path = $attributes['image']->store($type);
                        $payment = ManualPayment::create([
                            'image' => $path
                        ]);
                    } else {
                        $payment = OnlinePayment::create([
                            'gateway' => $attributes['gateway']
                        ]);
                    }

                    return $payment->transaction()
                        ->create([
                            'user_id' => $user->id,
                            'amount' => $request->amount,
                            'type' => $request->type,
                            'account_id' => $request->account_id,
                            'payment_method' => $request->payment_method,
                            'coin_id' => $coin->id,
                            'status' => (!$coin->isToman && $type === 'DEPOSIT') ? 'DEACTIVATED' : 'PENDING'
                        ]);
                });

                $array = $transaction->toArray();

                if ($transaction->payment_method === 'ONLINE') {
                    $array = array_merge($array,
                        [
                            'link' => route('portal', $transaction->id)
                        ]
                    );
                }

                return successResponse(
                    $array,
                );

            }

        } else {

            $transaction = DB::transaction(function () use ($request, $attributes, $coin, $user, $type) {

                if ($type === 'WITHDRAW') {
                    $payment = CryptoPayment::create(
                        $request->only(['address', 'memo', 'crypto_network_id'])
                    );

                    $credit = current_user()
                        ->credit($coin);

                    $credit->blockCredit($attributes['amount']);

                } else {

                    $payment = CryptoPayment::create(
                        $request->only(['address', 'memo', 'crypto_network_id', 'amount'])
                    );
                }

                $transaction = $payment->transaction()
                    ->create([
                        'user_id' => $user->id,
                        'amount' => $request->amount,
                        'type' => $request->type,
                        'account_id' => $request->account_id,
                        'payment_method' => $request->payment_method,
                        'coin_id' => $coin->id,
                        'status' => (!$coin->isToman && $type === 'DEPOSIT') ? 'DEACTIVATED' : 'PENDING'
                    ]);


                if ($type === 'DEPOSIT') {
                    CheckTxIdJob::dispatch($transaction)
                        ->delay(now()->addMinutes(60));
                }

                return $transaction;

            });

            return successResponse(
                $transaction
            );
        }
    }

    public function addTxId(Request $request, Transaction $transaction)
    {
        throw_if($transaction->payment_method !== 'CRYPTO', CustomException::class, trans('messages.MUST_CRYPTO'), 400);
        throw_if($transaction->status !== 'DEACTIVATED', CustomException::class, trans('messages.DONE_BEFORE'), 400);

        $attributes = $request->validate([
            'tx_id' => [
                'required',
                function ($attribute, $value, $fail) use ($transaction) {
                    if (Transaction::query()->where('coin_id', $transaction->coin_id)
                        ->whereHasMorph('payment', CryptoPayment::class, function ($query) use ($value) {
                            $query->where('tx_id', $value);
                        })
                        ->exists()) {
                        $fail(trans('validation.unique'));
                    }
                }
            ]
        ]);

        DB::transaction(function () use ($transaction, $attributes) {

            $transaction->update(['status' => 'PENDING']);

            $transaction->payment()
                ->update($attributes);

        });

        return successResponse(
            $transaction,
        );
    }

    public function otp()
    {
        event(new SendOtpEvent(current_user(), 'WITHDRAW_OTP'));
        return successResponse(
            [
                'method' => current_user()->current_otp
            ]
        );
    }

    public function index()
    {
        $with = ['account', 'payment', 'coin'];
        if (current_user() instanceof Admin) {
            $with[] = 'user.profile';
        }

        $builder = Transaction::query()->when(current_user() instanceof User, function ($query) {
            $query->where('user_id', current_user()->id);
        })->when(\request()->has('email'), function ($query) {
            $user_ids = User::where('email', 'like', '%' . \request('email') . '%')->pluck('id');
            $query->whereIn('user_id', $user_ids);
        })->when(request()->has('coinType'), function ($query) {
            $toman = Coin::where('code', 'TOMAN')->first();
            if (request('coinType') === 'toman') {
                $query->where('coin_id', $toman->id);
            } else {
                $query->where('coin_id', '!=', $toman->id);
            }
        })->filter(
            ['type', 'coin_id', 'created_atFrom', 'created_atTo', 'status']
        )->with($with)
            ->when(
                !\request()->has('orderBy'),
                function ($query) {
                    $query->latest();
                }
            );

        if (\request()->has('export') && \request('export')) {
            return Excel::download(new TransactionsExport($builder), 'transactions.xlsx');
        }

        return TransactionResource::collection(
            $builder->paginate(\request('size', 10))
        )->additional([
            'message' => trans('messages.success'),
            'type' => 'success',
            'status' => 'success',
        ]);
    }


    public function show(Transaction $transaction)
    {
        return (new TransactionResource(
            $transaction->load(['payment', 'coin', 'account'])
        ))->additional([
            'message' => trans('messages.success'),
            'type' => 'success',
            'status' => 'success',
        ]);
    }

    public function accept(Request $request, Transaction $transaction)
    {
        throw_if($transaction->status !== "PENDING", CustomException::class, trans('messages.PROCESSING_FINISHED'), 400);

        $request->validate(
            [
                'payment_method' => Rule::requiredIf(function () use ($transaction) {
                    return $transaction->isWithdraw() && $transaction->coin->isToman;
                }),
                'ref_id' => Rule::requiredIf(function () use ($transaction) {
                    return $transaction->isWithdraw() && $transaction->coin->isToman && $transaction->payment_method === 'TRANSFORM';
                }),
                'tx_id' => Rule::requiredIf(function () use ($transaction) {
                    return $transaction->isWithdraw() && $transaction->payment_method === 'CRYPTO';
                }),
                'amount' => Rule::requiredIf(function () use ($transaction) {
                    return !$transaction->isWithdraw() && $transaction->payment_method === 'CRYPTO';
                })
            ]
        );

        $transaction = DB::transaction(function () use ($transaction, $request) {


            if ($transaction->isWithdraw()) {

                if ($transaction->coin->isToman) {
                    $transaction->tomanWithdraw($request);
                } else {
                    $transaction->manualCryptoWithdraw();
                }

                return $transaction;
            }

            $transaction->accept();
            $transaction->user
                ->addCredit(
                    $transaction->coin->isToman ?
                        $transaction->amount :
                        $request->amount,
                    $transaction->coin
                );

            if (!$transaction->coin->isToman) {
                $transaction->update([
                    'amount' => $request->amount
                ]);
            }

            return $transaction;
        });

        $transaction->notify();

        return successResponse(
            $transaction,
        );
    }

    public function acceptAuto(Transaction $transaction)
    {
        throw_if($transaction->status !== "PENDING", CustomException::class, trans('messages.PROCESSING_FINISHED'), 400);
        DB::transaction(function () use ($transaction) {
            $transaction->withdrawByBinance();
        });

        return successResponse(
            $transaction
        );
    }

    public function reject(Transaction $transaction)
    {
        throw_if($transaction->status !== "PENDING", CustomException::class, trans('messages.PROCESSING_FINISHED'));
        DB::transaction(function () use ($transaction) {
            $transaction->reject();
            if ($transaction->isWithdraw()) {
                $transaction->credit
                    ->unBlockCredit($transaction->amount, false);
            }
        });

        $transaction->notify();
        return successResponse(
            $transaction,
        );
    }

    public function conflict(Transaction $transaction)
    {
        throw_if($transaction->status !== "PENDING", CustomException::class, trans('messages.PROCESSING_FINISHED'));
        throw_if($transaction->type !== "DEPOSIT", CustomException::class, trans("messages.WRONG_TYPE"));

        $transaction->update([
            'status' => 'CONFLICT'
        ]);

        $transaction->notify();
        return successResponse(
            $transaction
        );
    }

    // todo name must change
    public function checkTomanWithdraw(Transaction $transaction)
    {
        throw_if($transaction->type !== 'WITHDRAW' || $transaction->payment_method !== 'ONLINE',
            CustomException::class,
            trans('messages.JUST_ONLINE_WITHDRAW'),
            400);

        $payment = $transaction->payment;
        $response = (new Vandar())->verifyWithdraw($payment->settlement_id);

        throw_if(
            $response['status'] === 'PENDING',
            CustomException::class,
            trans('messages.PROCESS_NOT_COMPLETE')
        );

        if ($response['status'] == 'FAILED') {

            $transaction->update([
                    'status' => 'REJECTED',
                    'api_status' => $response['status']
                ]
            );
            $transaction->payment()->update(['transaction_id' => $response['gateway_transaction_id']]);

        } elseif ($response['status'] == 'Done') {

            $transaction->update([
                    'status' => 'ACCEPTED',
                    'api_status' => $response['status']
                ]
            );
            $transaction->payment()->update(
                ['transaction_id' => $response['gateway_transaction_id']]
            );
            $transaction->notify();
        }

        return successResponse(
            $response
        );
    }

    public function count()
    {
        return successResponse(
            ['count' => Transaction::filter(['status'])->count('id')],
            200,
        );
    }

    public function checkCryptoHasWithdrawn(Transaction $transaction)
    {
        throw_if(!$transaction->isAutoTrade(), CustomException::class, trans('messages.AUTO_TRADE_IS_OFF'));

        return successResponse(
            DB::transaction(
                function () use ($transaction) {
                    $transaction
                        ->checkCryptoHasWithdrawn();

                    $transaction
                        ->credit
                        ->unblockCredit(
                            $transaction
                                ->amount
                        );
                    $transaction->notify();
                    return $transaction;
                }
            )
        );

    }

}
