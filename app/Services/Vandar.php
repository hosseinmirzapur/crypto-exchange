<?php


namespace App\Services;


use App\Exceptions\CustomException;
use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class Vandar
{

    protected $api_key;
    protected $callback;
    const PORTAL_BASE_URL = "https://ipg.vandar.io/v3/";
    const TOKEN_URL = "https://ipg.vandar.io/api/v3/send";
    const VERIFY_URL = "https://ipg.vandar.io/api/v3/verify";
    const PRE_VERIFY = "https://vandar.io/api/ipg/2step/transaction";
    const LOGIN_URL = "https://api.vandar.io/v3/login";
    const REFRESH_LOGIN = "https://api.vandar.io/v3/refreshtoken";
    protected $verify_withdraw_url;
    protected $withdraw_url;
    protected $is_private_portal;
    protected $isToman = true;

    public function __construct()
    {
        $this->api_key = \config('services.vandar.api_key');
        $this->callback = \config('services.vandar.callback');
        $this->verify_withdraw_url = "https://api.vandar.io/v2.1/" . env('VANDAR_BUSINESS') . "/developers/settlement";
        $this->withdraw_url = "https://api.vandar.io/v3/business/" . env('VANDAR_BUSINESS') . "/settlement/store";
        $this->is_private_portal = \config('services.vandar.is_private');
    }

    /**
     * @param $amount
     * @param $card_number
     * @return string
     */
    public function getToken($amount, $card_number): string
    {
        $response = Http::post(static::TOKEN_URL, [
            'api_key' => $this->api_key,
            'amount' => $this->isToman ? $amount * 10 : $amount,
            'callback_url' => $this->callback,
            'valid_card_number' => $card_number
        ]);

        if ($response->failed()) {
            $errors = $response->json('errors');
            throw new CustomException($errors[0] ?? '', 400);
        }

        return $response->json('token');
    }

    /**
     * return
     * {
     *  "status": 1,
     *  "amount": "10000",
     *  "transId": 155058785697,
     *  "refnumber": "GmshtyjwKSuZXT81+6o9nKIkOcW*****PY05opjBoF",
     *  "trackingCode": "23***6",
     *  "factorNumber": null,
     *  "mobile": null,
     *  "description": "description",
     *  "cardNumber": "603799******6299",
     *  "CID": "ECC1F6931DDC1B8A0892293774836F3FFAC4A3C9D34997405F340FCC1BDDED82",
     *  "paymentDate": "2019-02-19 18:21:50",
     *  "message": "Confirm requierd"
     *  }
     *
     * @param $token
     * @return array|mixed
     */
    public function preVerify($token)
    {
        $response = Http::post(static::PRE_VERIFY, [
            'api_key' => $this->api_key,
            'token' => $token
        ]);

        if ($response->failed()) {
            $errors = $response->json('errors');
            throw new CustomException($errors[0] ?? '', 400);
        }

        return $response->json();
    }


    /**
     * return
     * {
     *   "status": 1,
     *   "amount": "1000.00",
     *   "realAmount": 500,
     *   "wage": "500",
     *   "transId": 159178352177,
     *   "factorNumber": "12345",
     *   "mobile": "09123456789",
     *   "description": "description",
     *   "cardNumber": "603799******7999",
     *   "paymentDate": "2020-06-10 14:36:30",
     *   "cid": null,
     *   "message": "ok"
     * }
     *
     * @param $token
     * @return array
     */
    public function verify($token): array
    {
        $response = Http::post(static::VERIFY_URL, [
            'api_key' => $this->api_key,
            'token' => $token
        ]);

        if ($response->failed()) {
            $errors = $response->json('errors');
            throw new CustomException($errors[0] ?? trans('messages.BANK_PROBLEM'), 400);
        }

        return $response->json();
    }

    public function getPortalAddress($token): string
    {
        return static::PORTAL_BASE_URL . $token;
    }


    /**
     * login in vandar
     * @param string $mobile
     * @param string $password
     * @return string
     */
    public function login(string $mobile, string $password): string
    {
        $response = Http::post(static::LOGIN_URL, [
            'mobile' => $mobile,
            'password' => $password,
        ]);

        if (!$response->successful()) {
            throw new CustomException(trans('messages.UNAUTHENTICATED'), Response::HTTP_UNAUTHORIZED);
        }

        $config = Config::vandar($response->json());

        return $config->value;
    }

    /**
     * @param $amount string|numeric rial
     * @param $sheba string
     * @param null $trackId
     * @param bool $convertToToman
     * @return array|mixed
     */
    public function withdraw($amount, $sheba, $trackId = null, bool $convertToToman = true)
    {
        $token = $this->getLoginToken();

        $response = Http::withToken($token)
            ->post($this->withdraw_url, [
                'amount' => $amount * ($convertToToman ? 10 : 1),
                'iban' => $sheba,
                'track_id' => $trackId ?? Str::uuid()
            ]);

        if ($response->failed()) {
            $msg = $response->json('error');

            throw new CustomException($msg, 400);
        }

        return $response->json();
    }

    protected function getLoginToken(): string
    {
        $config = Config::typeOf('VANDAR')
            ->where('key', 'TOKEN')
            ->first();

        throw_if(!isset($config) || Carbon::now()->gt(Carbon::parse($config->updated_at)->addDays(3)),
            CustomException::class,
            'REFRESH_TOKEN');

        return $config->value;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function verifyWithdraw($id)
    {
        $token = $this->getLoginToken();

        $response = Http::withToken($token)
            ->get($this->verify_withdraw_url . '/' . $id);

        if ($response->failed()) {
            $msg = $response->json('errors');
            throw new CustomException($msg[0] ?? '', $response->getState());
        }

        return ($response->json())['data']['settlement'];
    }

    public function calculateCommission($price, $type = 'WITHDRAW')
    {
        $commission = 0;
        if ($type === 'WITHDRAW') {
            do {
                $commission += $this->calculateWithdrawWage($price);
            } while ($price - 50000000 > 0);
        } else {
            $commission = $this->calculatePortalWage($price);
        }

        return $commission;
    }

    protected function calculateWithdrawWage($price)
    {
        $wage = 0.0002 * $price;

        if ($wage < 1000) {
            return 1000;
        }

        if ($wage > 5000) {
            return 5000;
        }
        return $wage;
    }

    protected function calculatePortalWage($price)
    {
        $wage = 0.01 * $price;

        if ($this->is_private_portal && $wage > 4000) {
            return 4000;
        } elseif ($this->is_private_portal && $wage > 3000) {
            return 3000;
        }

        return $wage;
    }
}
