<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use function App\Helpers\current_user;
use function App\Helpers\errorResponse;

class Google2faController extends Controller
{

    public function store()
    {
        $google2fa = app('pragmarx.google2fa');

        $secret = $google2fa->generateSecretKey();
        if (empty($secret)) {
            return errorResponse(
                "GOOGLE_2FA_ERROR",
                400);
        }
        $user = current_user();
        $user->update([
            'google_2fa' => $secret
        ]);

        $qr_image = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );


        return \App\Helpers\successResponse(
            [
                'secret_key' => $secret,
                'qr_image' =>  $qr_image
            ],
            201,
        );
    }

    public function verify(Request $request)
    {
        $attributes = $request->validate(['code' =>'required']);

        $google2fa = app('pragmarx.google2fa');
        if (!$google2fa->verifyGoogle2FA(current_user()->google_2fa, $attributes['code'])) {
            return errorResponse(trans("messages.WRONG_CODE"), 400);
        }
        current_user()->settings()->key('OTP')->update(['setting_value' => 'GOOGLE']);
        return \App\Helpers\successResponse(
            null,
            200,
            ['message' => trans('messages.GOOGLE_2FA_SUCCESS')]
        );
    }

}
