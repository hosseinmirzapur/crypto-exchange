<?php

namespace App\Http\Controllers;

use App\Events\SendOtpEvent;
use App\Http\Resources\OtpResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;
use function App\Helpers\current_user;
use function App\Helpers\errorResponse;
use function App\Helpers\successResponse;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if (!auth('api')->attempt($request->only('email', 'password'))) {
            return errorResponse(trans("messages.LOGIN_WRONG"));
        }

        $user = current_user();
        $otp = $user->settings()
            ->key('OTP')
            ->value('setting_value');

        $user->method = $otp ?? "EMAIL";
        if ($user->method === 'SMS') {
            $user->load('profile');
        }

        event(new SendOtpEvent($user));

        $user->token = Password::createToken($user);

        return successResponse(
            new OtpResource($user),
            200,
            ['message' => trans('messages.OTP_NEED')]
        );
    }

    public function show()
    {
        $user = current_user()
            ->load('profile', 'coins', 'rank', 'settings', 'credits')
            ->setAppends(['rankCorrectionFactor', 'netAssetsInToman']);

        return successResponse(
            new UserResource($user)
        );
    }

    public function destroy()
    {
        auth()->user()
            ->currentAccessToken()
            ->delete();
        return successResponse();
    }

    public function checkUser()
    {
        return successResponse(
            current_user(),
        );
    }

}
