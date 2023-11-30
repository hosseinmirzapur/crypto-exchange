<?php

namespace App\Models;

use App\Traits\Filter;
use App\Traits\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CryptoNetwork extends Model
{
    use HasFactory, Status, Filter;

    public $timestamps = false;

    const STATUS = ['ACTIVATED', 'DISABLED'];

    protected $fillable = [
        'name', 'network', 'withdraw_fee', 'withdraw_min', 'minConfirm', 'withdraw_max', 'regex', 'memo_regex', 'withdraw_status', 'deposit_status', 'status', 'address', 'memo'
    ];

    public function getAddressImageAttribute()
    {
        if (!empty($this->address)) {
            return \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
                ->format('svg')
                ->backgroundColor(255, 255, 255)
                ->generate($this->address);
        }
        return '';
    }

    public function getmemoImageAttribute()
    {
        if (!empty($this->memo)) {
            return \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
                ->format('svg')
                ->backgroundColor(255, 255, 255)
                ->generate($this->memo);
        }
        return '';
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class);
    }

    public static function updateConfig()
    {


        $coins = Coin::where('status', '!=', 'DISABLED')
            ->get();
        $binance = new \App\Classes\Binance(false);
        $binance->binance->useServerTime();
        sleep(2);
        $apiCoins = $binance->coins();

        DB::table('coins')
            ->where('code', '!=', 'TOMAN')
            ->Where('status', '!=', 'DISABLED')
            ->update(
                ['status' => 'DISABLED_BY_BINANCE']
            );

        foreach ($coins as $coin) {
            foreach ($apiCoins as $apiCoin) {
                if ($apiCoin['coin'] === $coin->code) {
                    Coin::where('id', $coin->id)
                        ->update([
                        'amount' => $apiCoin['free'],
                        'name' => $apiCoin['name'],
                        'label' => !empty($coin->label) ? $coin->label : $apiCoin['name'],
                        'status' => 'ACTIVATED'
                    ]);

                    $networks = $apiCoin['networkList'];

                    foreach ($networks as $network) {

                        $cryptoNetwork = CryptoNetwork::where('coin_id', $coin->id)
                            ->where('network', $network['network'])
                            ->first();

                        if (isset($cryptoNetwork)) {
                            $cryptoNetwork->update([
                                'network' => $network['network'],
                                'name' => $network['name'],
                                'withdraw_fee' => $network['withdrawFee'],
                                'withdraw_min' => $network['withdrawMin'],
                                'withdraw_max' => $network['withdrawMax'],
                                'regex' => $network['addressRegex'],
                                'memo_regex' => $network['memoRegex'],
                                'withdraw_status' => $network['withdrawEnable'] ? 'ACTIVATED' : 'DISABLED',
                                'deposit_status' => $network['depositEnable'] ? 'ACTIVATED' : 'DISABLED',
                                'status' => $cryptoNetwork->status === 'ACTIVATED' ? 'ACTIVATED' : 'DISABLED'
                            ]);
                        } else {
                            $coin->networks()
                                ->create(
                                [
                                    'network' => $network['network'],
                                    'name' => $network['name'],
                                    'withdraw_fee' => $network['withdrawFee'],
                                    'withdraw_min' => $network['withdrawMin'],
                                    'withdraw_max' => $network['withdrawMax'],
                                    'regex' => $network['addressRegex'],
                                    'memo_regex' => $network['memoRegex'],
                                    'withdraw_status' => $network['withdrawEnable'] ? 'ACTIVATED' : 'DISABLED',
                                    'deposit_status' => $network['depositEnable'] ? 'ACTIVATED' : 'DISABLED',
                                    'status' => 'ACTIVATED'
                                ]
                            );
                        }

                    }


                }
            }

        }

        Config::updateOrCreate(
            [
                'type' => 'SCHEDULE',
                'key' => 'ALL_COINS'
            ],
            [
                'type' => 'SCHEDULE',
                'key' => 'ALL_COINS',
                'value' => now()
            ]
        );
    }


}
