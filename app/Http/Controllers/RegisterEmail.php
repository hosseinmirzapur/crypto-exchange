<?php

namespace App\Http\Controllers;

use App\Events\EmailRegistered;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use function App\Helpers\errorResponse;
use function App\Helpers\successResponse;

class RegisterEmail extends Controller
{
    public function store(Request $request)
    {
        $attribute = $request->validate(['email' => [
            'required',
            'email',
            function($attribute, $value, $fail) {
                $user = User::where('email',$value)->first();
                if (isset($user) && !empty($user->password)) {
                    $fail( trans('validation.unique') );
                }
            }
        ]]);
        $user = User::updateOrCreate(['email' => $attribute['email']], $attribute);
        event(new EmailRegistered($user));
        return successResponse($user, 201, ['message' => trans('messages.EMAIL_REGISTERED')]);
    }

    public function update(Request $request)
    {
        $attribute = $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|numeric'
        ]);
        $user = User::whereEmail($attribute['email'])
            ->first();
        $code = $user->getSentCode('CONFIRM_REGISTERED_EMAIL');
        if (!isset($code) || !Hash::check($attribute['code'], $code)) {
            return errorResponse(trans('messages.WRONG_CODE'), 400);
        }
        $user->markEmailAsVerified();
        $token = Password::createToken($user);
        return successResponse(['user' => $user, 'token' => $token], 200, ['message' => trans('messages.EMAIL_VERIFIED')]);
    }


}
