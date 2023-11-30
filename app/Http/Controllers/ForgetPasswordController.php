<?php

namespace App\Http\Controllers;

use App\Events\EmailRegistered;
use App\Events\ForgetPasswordEmailConfirmationEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use function App\Helpers\errorResponse;
use function App\Helpers\successResponse;

class ForgetPasswordController extends Controller
{
    public function show(Request $request)
    {
        $attribute = $request->validate(['email' => 'required|email|exists:users,email']);
        $user = User::whereEmail($attribute['email'])->firstOrFail();
        event(new ForgetPasswordEmailConfirmationEvent($user));
        return successResponse(['method' => $user->current_otp], 200, ['message' => trans('messages.CODE_SENT')]);
    }

    public function update(Request $request)
    {
        $attribute = $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|numeric'
        ]);
        $user = User::whereEmail($attribute['email'])
            ->first();
        $code = $user->getSentCode('CHANGE_PASSWORD');
        $token = Password::createToken($user);
        if (!isset($code) || !Hash::check($attribute['code'], $code)) {
            return errorResponse(trans('messages.WRONG_CODE'), 400);
        }
        return successResponse([
            'token' => $token
        ]);
    }

    public function otp()
    {

    }
}
