<?php


namespace App\Services;

use App\Exceptions\CustomException;
use Kavenegar\Laravel\Facade as Kavenegar;

class SMS
{

    protected $to;
    protected $token;
    protected $position;


    public function __construct($to, $token, $position)
    {
        $this->to = $to;
        $this->token = $token;
        $this->position = $position;
    }

    public static function handle($to, $code, $position)
    {
        $sms = new static($to, $code, $position);

        switch ($position) {
            case 'SETTING_OTP' :
                $method = 'CHANGEOTP';
                $arg = [$code, 120];
                break;
            case 'CHANGE_PASSWORD' :
                $method = 'FORGETPASS';
                $arg = [$code, 120];
                break;
            case 'OTP_LOGIN_USER' :
                $method = 'LOGIN';
                $arg = [$code, 120];
                break;
            case 'CONFIRM_REGISTERED_EMAIL' :
                $method = 'REGISTER';
                $arg = [$code, 120];
                break;
            case 'WITHDRAW_OTP' :
                $method = 'WITHDRAW';
                $arg = [$code, 120];
                break;
            case 'SUCCESS_AUTH' :
                $method = 'SUCCESS_AUTH';
                $arg = [];
                break;
            case 'FAILED_AUTH' :
                $method = 'FAILED_AUTH';
                $arg = [];
                break;
            default:
                throw new CustomException('CONNECTION_PROBLEM', 400);
        }


        Kavenegar::VerifyLookup(
            $to,
            $arg[0],
            $arg[1],
            '',
            $method
        );
    }

}
