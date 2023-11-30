<?php

namespace App\Models;

use App\Classes\Binance;
use App\Exceptions\CustomException;
use App\Notifications\TransactionNotification;
use App\Services\Vandar;
use App\Traits\AutoTrade;
use App\Traits\Filter;
use App\Traits\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, Status, Filter, AutoTrade;

    const TYPE = ['DEPOSIT', 'WITHDRAW'];
    const PAYMENT_METHOD = ['TRANSFER', 'ONLINE', 'CARD', 'CRYPTO', 'WALLET', 'REFERRAL'];
    const STATUS = ["REJECTED", "DEACTIVATED", "PENDING", "CONFLICT", 'SENT', "ACCEPTED", "FAILED", 'EXPIRED'];
    const PAYMENT_TYPE = ['MANUAL', 'CRYPTO', 'ONLINE'];
    const WITHDRAW_HISTORY_STATUS = ['EMAIL_SENT', 'CANCELLED', 'AWAITING_APPROVAL', 'REJECTED', 'PROCESSING', 'FAILURE', 'COMPLETED'];
    const DEPOSIT_HISTORY_STATUS = ['PENDING', 'SUCCESS'];

    protected $casts = ['amount' => 'float'];

    protected $perPage = 10;


    protected $fillable = [
        'user_id',
        'order_id',
        'payment_type',
        'payment_id',
        'coin_id',
        'payment_method',
        'amount',
        'status',
        'type',
        'api_status',
        'account_id'
    ];

    public function apiDepositStatusMessagesForCrypto()
    {
        return [
            0 => 'PENDING',
            1 => 'SUCCESS',
            6 => 'CREDITED_BUT_CANNOT_WITHDRAW',
        ];
    }

    public function apiWithdrawStatusMessagesForCrypto()
    {
        return [
            0 => 'EMAIL_SENT',
            1 => 'CANCELLED',
            2 => 'AWAITING_APPROVAL',
            3 => 'REJECTED',
            4 => 'PROCESSING',
            5 => 'FAILURE',
            6 => 'COMPLETED'
        ];
    }

    public function getCreatedAtAttribute($value)
    {
        return App::isLocale('fa') ?
            (new Carbon($value))->setTimezone('Asia/Tehran')->toDateTimeString() :
            $value;
    }

    public function setWithdrawStatus($status)
    {
        $this->api_status = $this->apiWithdrawStatusMessagesForCrypto();

        $status = static::WITHDRAW_HISTORY_STATUS[$status];
        if (in_array($status, ['EMAIL_SENT', 'AWAITING_APPROVAL', 'PROCESSING'])) {
            $this->status = "PENDING";
            return;
        }
        if (in_array($status, ['CANCELLED', 'REJECTED', 'FAILURE'])) {
            $this->status = "REJECTED";
            return;
        }
        if (in_array($status, ['COMPLETED'])) {
            $this->status = "ACCEPTED";
            return;
        }
    }

    public function setDepositStatus($status)
    {
        switch ($status) {
            case "PENDING" :
                $this->status = 'PENDING';
                return;
            case "SUCCESS" :
                $this->status = "SUCCESS";
                return;
        }
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = strtoupper($value);
    }

    public function getCredit()
    {
        $coin = Coin::find($this->coin_id);
        return $this->user->credit($coin);
    }

    public function getCreditAttribute()
    {
        $coin = Coin::find($this->coin_id);
        return $this->user->credit($coin);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function payment()
    {
        return $this->morphTo(__FUNCTION__, 'payment_type', 'payment_id');
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class);
    }

    public function isWithdraw()
    {
        return $this->type === 'WITHDRAW';
    }

    public function isType($type)
    {
        return $this->type === Str::upper($type);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function notify()
    {
        $this->user
            ->notify(
                new TransactionNotification($this)
            );
    }

    public function checkCryptoDeposited()
    {
        throw_if(
            $this->status !== 'PENDING',
            CustomException::class,
            trans('messages.PROCESSING_FINISHED'
            ), 400);

        $history_array = cache()->remember('DEPOSIT_HISTORY',
            now()->addSeconds(30),
            function () {
                return (new Binance())
                    ->depositHistory();
            }
        );

        if (empty($history_array)) {
            Log::alert('empty deposit history array');
            throw new CustomException(trans('messages.PROCESSING'), 400);
        }
        foreach ($history_array as $item) {
            if ($item['txId'] === $this->payment->tx_id) {

                if (
                    $item['coin'] === $this->coin->code
                ) {
                    $this->update([
                        'amount' => $item['amount'],
                        'status' => 'ACCEPTED'
                    ]);

                    return $this;
                }

                $this->update(['status' => 'CONFLICT']);
                throw new CustomException(trans('messages.DEPOSIT_CONFLICT'), 400);
            }
        }

        throw new CustomException(trans('messages.PROCESSING'), 400);
    }

    public function checkCryptoHasWithdrawn()
    {
        throw_if(
            $this->status !== 'SENT',
            CustomException::class,
            trans('messages.PROCESSING_FINISHED'
            ), 400);

        $history_array = cache()->remember('withdraw_HISTORY',
            now()->addSeconds(30),
            function () {
                return (new Binance())
                    ->withdrawHistory();
            }
        );

        if (empty($history_array)) {
            Log::alert('empty withdraw history array');
            throw new CustomException(trans('messages.PROCESSING'), 400);
        }

        foreach ($history_array as $item) {
            if ($item['id'] === $this->payment->withdraw_id) {

                $this->accept();
                $this->payment
                    ->update([
                        'tx_id' => $item['txId'],
                        'commission' => $item['transactionFee']
                    ]);


                return $this;
            }
        }

        throw new CustomException(trans('messages.PROCESSING'), 400);
    }


    public function paymentByReceipt($type = 'card')
    {
        $attributes = request()->validate([
            'image' => 'required|file'
        ]);
        $path = $attributes['image']->store($type);
        ManualPayment::create([
            'account_id' => request('account_id'),
            'image' => $path
        ]);

        return $this;
    }

    public function tomanWithdraw(Request $request)
    {
        if ($request->payment_method === 'ONLINE') {
            $this->withdrawByPortal();
        } else {
            $this->withdrawByTransfer($request);
        }

    }

    public function withdrawByPortal()
    {

        $response = (new Vandar())
            ->withdraw(
                $this->amount,
                $this->account->sheba,
                $this->id
            );

        $payment = OnlinePayment::create([
            'settlement_id' => $response['id'],
            'transaction_id' => $response['transaction_id'],
            'commission' => $response['wage_toman'],
        ]);

        $this->update(
            [
                'payment_type' => OnlinePayment::class,
                'payment_id' => $payment->id,
                'payment_method' => 'ONLINE',
                'status' => 'SENT'
            ]
        );
    }

    public function withdrawByTransfer(Request $request)
    {
        $this->credit
            ->unBlockCredit($this->amount);

        $payment = ManualPayment::create(
            [
                'ref_id' => $request->ref_id
            ]
        );

        $this->update(
            [
                'payment_type' => ManualPayment::class,
                'payment_id' => $payment->id,
                'payment_method' => 'TRANSFER',
                'status' => 'ACCEPTED'
            ]
        );
    }

    public function withdrawByBinance()
    {
        throw_if(!$this->isAutoTrade(), CustomException::class, trans('messages.AUTO_TRADE_IS_OFF'));

        $this->update(['status' => 'SENT']);

        $payment = $this->payment;
        $result = (new Binance())
            ->withdraw(
                $this->coin->code,
                $this->amount,
                $payment->network->network,
                $payment->address,
                $payment->memo ?? null
            );

        $payment->update([
            'withdraw_id' => $result['id']
        ]);
    }

    public function manualCryptoWithdraw()
    {
        $this->accept();
        $this->credit
            ->unBlockCredit($this->amount);

        $this->payment()
            ->update(
                [
                    'tx_id' => \request('tx_id')
                ]
            );
    }

}
