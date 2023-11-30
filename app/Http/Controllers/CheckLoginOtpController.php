<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use function App\Helpers\current_user;
use function App\Helpers\errorResponse;
use function App\Helpers\successResponse;

class CheckLoginOtpController extends Controller
{

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'code' => 'required',
            'email' => 'required'
        ]);

        $user = User::whereEmail($attributes['email'])
            ->first();

        $setting = $user->settings()
            ->key('OTP')
            ->value('setting_value');

        if (isset($setting) && $setting === 'GOOGLE') {
            $google2fa = app('pragmarx.google2fa');
            $a = (string) $attributes['code'];
            if (!$google2fa->verifyGoogle2FA($user->google_2fa, $a)) {
                return errorResponse(trans("messages.WRONG_CODE"),   400);
            }
        } else {
            /** @var User $user */
            $code = $user->getSentCode('OTP_LOGIN_USER');
            if (!isset($code) || !Hash::check($attributes['code'], $code)) {
                return errorResponse(trans("messages.WRONG_CODE"), 400);
            }
        }

        Auth::login($user);
        $token = auth()
            ->user()
            ->createToken('user');

        return successResponse(
            ['token' => $token->plainTextToken],
        );


    }

}
