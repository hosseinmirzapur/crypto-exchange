<?php

namespace App\Models;

use App\Exceptions\CustomException;
use App\Services\Price\Fee;
use App\Traits\Filter;
use App\Traits\IsAdmin;
use App\Traits\Periodic;
use App\Traits\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, Status, Filter, IsAdmin, Periodic;

    protected $perPage = 10;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'status',
        'rank_id',
        'is_banned',
        'google_2fa'
    ];

    const STATUS = [
        'PRIMARY_AUTH_DONE',
        'SECONDARY_AUTH_DONE',
        'OTP_DONE',
        "CONFIRMED_IDENTITY",
        "PENDING",
        "ACCEPTED",
        "CONFLICT",
        "REJECTED",
        'RESEND_OTP'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_2fa'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_banned' => 'boolean'
    ];

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = Str::lower($value);
    }


    public function getCurrentOtpAttribute()
    {
        return $this->settings()
            ->key('OTP')
            ->value('setting_value') ?? 'EMAIL';
    }

    public function getRankCorrectionFactorAttribute()
    {
        $lastRank = Rank::first();
        $coins = Coin::all();

        return $coins->mapWithKeys(function ($coin) use ($lastRank) {
            $c_buy = 1 + (new Fee(['constant', 'binance'], $coin))->fee();
            $c_sell = 1 + (new Fee(['constant', 'binance'], $coin))->fee();
            return [
                $coin->code =>
                    [
                        ($c_buy + $this->rank->fee) / ($c_buy + $lastRank->fee),
                        ($c_sell - $this->rank->fee) / ($c_sell - $lastRank->fee)
                    ]
            ];
        });
    }

    public function getNetAssetsInTomanAttribute()
    {
        $credits = Credit::query()
            ->with(
                ['markets' => function ($query) {
                    return $query->where('quote_id', 1);
                }
                ]
            )->whereHas('user', function ($query) {
                $query->where('user_id', $this->id);
            })
            ->get();

        $toman = Credit::where('coin_id', 1)
            ->whereHas('user', function ($query) {
                $query->where('user_id', $this->id);
            })->first();
        $credit = $credits->reduce(function ($carry, $item) {
            $price = isset($item->markets[0]) ? $item->markets[0]->sellingPrice : 0;
            return $carry + $item->credit * $price;
        });
        return ($credit ?? 0) + (isset($toman) ? $toman->credit : 0);
    }


    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    //todo must change

    public function getSentCode($position)
    {
        return $this->codes()
            ->where('position', $position)
            ->where('expire_at', '>', now()->toDateTime())
            ->latest()
            ->value('code');
    }

    public function codes()
    {
        return $this->hasMany(Code::class)
            ->latest();
    }

    /**
     * @return HasOne
     */
    public function referral()
    {
        return $this->hasOne(Referral::class);
    }

    public function addReferral()
    {
        return $this->referral()
            ->firstOrNew()
            ->generateCode()
            ->save();
    }


    /**
     * @return HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function checkOtp($attribute, $position)
    {
        $otpMethod = $this->settings()
            ->key('OTP')
            ->value('setting_value') ?? 'EMAIL';

        if ($otpMethod === 'GOOGLE') {
            $google2fa = app('pragmarx.google2fa');

            if (!$google2fa->verifyGoogle2FA($this->google_2fa ?? '', request($attribute))) {
                return false;
            }

        } else {

            $code = $this->getSentCode($position);

            if (!isset($code)) {
                return false;
            }

            if (!Hash::check(request($attribute), $code)) {
                return false;
            }
        }
        return  true;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function coins()
    {
        return $this->belongsToMany(Coin::class, 'credits')
            ->using(Credit::class)
            ->as('credit')
            ->withPivot(['credit', 'blocked']);
    }

    public function credits()
    {
        return $this->hasMany(Credit::class);
    }

    public function hasEnoughCredit($amount, $coin = 'TOMAN'): bool
    {
        $coin = $this->coins()
            ->where('code', $coin)
            ->first();

        return isset($coin) && $coin->credit->hasEnoughCredit($amount);

    }

    public function hasCredit(Coin $coin, $amount) {

        $credit = $this
            ->credit($coin);

        return isset($credit) ? $credit->hasEnoughCredit($amount) : false;
    }

    public function throwNotEnoughCredit(Coin $coin, $amount = null)
    {
        throw_if($this->hasCredit($coin, $amount),
            CustomException::class,
            trans('messages.NOT_ENOUGH_CREDIT'),
            400
        );
    }

    public function addCredit($amount, Coin $coin = null)
    {
        if (!isset($coin)) {
            $coin = Coin::whereCode('TOMAN')
                ->first();
        }

        $credit = $this->credit($coin);

        if (!isset($credit)) {
            return $this->credits()
                ->create([
                    'coin_id' => $coin->id,
                    'credit' => $amount
                ]);
        }

        return $credit->add($amount);
    }

    public function credit(Coin $coin)
    {
        return $this->credits()
            ->where('coin_id', $coin->id)
            ->first();
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function finnotech()
    {
        return $this->hasMany(Finnotech::class);
    }

    public function referringUsers()
    {
        return $this->belongsToMany(
            User::class,
            'referring_users',
            'user_id',
            'referring_user_id',
            'id',
            'id'
        );
    }

    public function referredUsers()
    {
        return $this->belongsToMany(
            User::class,
            'referring_users',
            'referring_user_id',
            'user_id',
            'id',
            'id'
        );
    }
}
