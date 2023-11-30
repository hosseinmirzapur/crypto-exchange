<?php

namespace App\Models;

use App\Classes\Binance;
use App\Events\UpdateUserRankEvent;
use App\Exceptions\CustomException;
use App\Notifications\OrderNotification;
use App\Services\Price\Price;
use App\Traits\AutoTrade;
use App\Traits\Filter;
use App\Traits\Status;
use App\Traits\Trade;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use function App\Helpers\current_user;

class Order extends Model
{
    use HasFactory, Status, Trade, Filter, AutoTrade;

    const STATUS = ['DEACTIVATED', 'PENDING', 'ACCEPTED', 'REJECTED', 'REJECTED_BY_USER', 'EXPIRED'];
    const PAYMENT_METHODS = ['WALLET', 'ONLINE', 'CARD'];
    const API_STATUS = ['PARTIALLY_FILLED', 'FILLED', 'CANCELED', 'PENDING_CANCEL', 'REJECTED', 'EXPIRED'];
    const EXCHANGE_ORDER_FEE = 0.001;

    protected $fillable = [
        'market_id',
        'type',
        'payment_method',
        'fee',
        'amount',
        'price',
        'bill_number',
        'status',
        'api_status',

    ];

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = strtoupper($value);
    }

    public function getCreatedAtAttribute($value)
    {
        return App::isLocale('fa') ?
            (new Carbon($value))->setTimezone('Asia/Tehran')->toDateTimeString() :
            $value;
    }

    protected function apiOrderStatus($status)
    {
        switch ($status) {
            case 'PARTIALLY_FILLED':
                return "PENDING";

            case 'CANCELED':
            case 'PENDING_CANCEL':
            case  'REJECTED':
                return "REJECTED";

            case 'EXPIRED':
                return "EXPIRED";

            case 'FILLED';
                return "ACCEPTED";
        }
    }

    public function isType($type)
    {
        return $this->type === strtoupper($type);
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function cost($price = null)
    {
        return $this->amount * ($price ?? $this->price);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function buy()
    {

        if ($this->isAutoTrade()) {
            if ($this->market->isOrderable()) {
                $this->orderByApi();
            } else {
                $this->update(['status' => 'ACCEPTED']);

                $this->addTrade(
                    [
                        [
                            'price' => 1,
                            'qty' => $this->amount,
                            'commission' => 0,
                            'commissionAsset' => '',
                        ]
                    ]
                );
            }
        }


        if ($this->isAccepted()) {
            $this->handleAcceptedOrder();
        }
    }

    public function handleAcceptedOrder($withTrade = false)
    {
        if ($this->isType('buy')) {
            $credit =
                $this->user
                    ->credit(
                        $this->market->quote
                    );
            $credit->unBlockCredit($this->cost());
            $this->user
                ->addCredit($this->amount, $this->market->coin);
        }

        if ($this->isType('sell')) {
            $credit = $this->user
                ->credit(
                    $this->market->coin
                );

            $credit->unBlockCredit($this->amount);
            $this->user
                ->addCredit($this->cost(), $this->market->quote);
        }

        if ($withTrade) {
            $this->addTrade(
                [
                    [
                        'price' => $this->market->price(true),
                        'qty' => $this->amount,
                        'commission' => 0,
                        'commissionAsset' => '',
                    ]
                ]
            );
        }
        $this->notify();
    }

    public function blockCreditAfterOrder(): void
    {

        if ($this->isType('buy')) {
            $credit = current_user()
                ->credit($this->market->quote);
            $credit->blockCredit($this->cost());
        }

        if ($this->isType('sell')) {
            $credit = current_user()
                ->credit($this->market->coin);
            $credit->blockCredit($this->amount);
        }

    }


    public function sell()
    {
        if ($this->isAutoTrade()) {
            if ($this->market->isOrderable()) {
                $this->orderByApi();
            } else {
                $this->update(['status' => 'ACCEPTED']);
                $this->addTrade(
                    [
                        [
                            'price' => 1,
                            'qty' => $this->amount,
                            'commission' => 0,
                            'commissionAsset' => '',
                        ]
                    ]
                );
            }
        }

        if ($this->isAccepted()) {
            $this->handleAcceptedOrder();
        }
    }

    public function orderByApi()
    {
        $result = ($this->isType('sell'))
            ? (new Binance())->orderSell(
                $this->market->modifiedName,
                $this->amount,
            )
            : (new Binance())->orderBuy(
                $this->market->modifiedName,
                $this->modifiedAmount()
            );

        throw_if(empty($result), CustomException::class, trans('messages.API_CONNECTION_PROBLEM'), 400);

        $trades = [];

        if ($result['status'] === 'FILLED') {

            $trades = $result['fills'] ?? [];

            $this->addTrade($trades);

            $this->update(
                [
                    'binance_order_id' => $result['orderId'] ?? null,
                    'api_status' => $result['status'],
                    'bill_number' => $result['orderId'] ?? null,
                    'status' => $this->apiOrderStatus($result['status'])
                ]
            );
        }

        return $trades;
    }

    protected function addTrade(array $trades)
    {
        $insert_array = [];
        if (!empty($trades)) {
            foreach ($trades as $trade) {
                $insert_array[] = $this->tradeHandler($trade);
            }
            $trades = $this->trades()
                ->createMany($insert_array);
        }
        return $trades;
    }

    protected function tradeHandler(array $trade)
    {
        $tradePrice = (new Price())
            ->withPrice($trade['price'])
            ->changeToToman()
            ->getPrice();

        if ($this->isType('buy')) {
            $netPrice = $this->price - $tradePrice;
        } else {
            $netPrice = $tradePrice - $this->price;
        }
        $profit = $netPrice * $trade['qty'];

        $referredUser = $this->user
            ->referredUsers()
            ->first();

        if (isset($referredUser) && $profit > 0) {
            $referredFee = Config::typeOf('MAIN')
                    ->key('REFERRAL_COMMISSION')
                    ->first() ?? 0.3;

            $toman = Coin::where('code', 'TOMAN')
                ->first();

            $referredUser->addCredit($profit * $referredFee, $toman);
            $referredUser->transactions()
                ->create([
                    'coin_id' => $toman->id,
                    'payment_method' => 'REFERRAL',
                    'amount' => $profit * $referredFee,
                    'status' => 'ACCEPTED',
                    'type' => 'DEPOSIT',
                ]);

            $profit = $profit * (1 - $referredFee);

        }

        return [
            'price' => $trade['price'],
            'price_toman' => $tradePrice,
            'binance_trade_id' => $trade['tradeId'] ?? 0,
            'amount' => $trade['qty'],
            'commission_amount' => $trade['commission'],
            'commission_asset' => $trade['commissionAsset'],
            'type' => $this->type,
            'gain' => $profit,
        ];
    }

    /**
     * modify amount by
     */
    protected function modifiedAmount()
    {
        $market = $this->market->modifiedMarket;

        $precision = strlen(substr(strrchr($market->amount_step, "."), 1));

        return ceil($this->amount / (1 - static::EXCHANGE_ORDER_FEE) * (10 ** $precision)) / (10 ** $precision);
    }

    public function handleRejectedOrder()
    {
        if ($this->isType('buy')) {
            $credit = $this->user
                ->credit($this->market->quote);
            $credit->unBlockCredit($this->cost(), false);
            return;
        }

        if ($this->isType('sell')) {
            $credit = $this->user
                ->credit($this->market->coin);
            $credit->unBlockCredit($this->amount, false);
            return;
        }
    }

    protected static function booted()
    {
        self::creating(function ($order) {
            $order->user_id = current_user()->id;
            event(new UpdateUserRankEvent(current_user()));
        });
    }

    public function notify() : void
    {
        $this->user
            ->notify(
                new OrderNotification($this)
            );

    }
}
