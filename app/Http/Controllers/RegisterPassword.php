<?php

namespace App\Http\Controllers;

use App\Events\UserRegisteredPasswordEvent;
use App\Exceptions\CustomException;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use function App\Helpers\errorResponse;
use function App\Helpers\successResponse;

class RegisterPassword extends Controller
{
    public function store(Request $request)
    {
        $user = User::whereEmail($request->email)->firstOrFail();
        if (!$user->hasVerifiedEmail()) {
            return errorResponse('NOT_VERIFIED_EMAIL', 400);
        }

        throw_if(!Password::tokenExists($user, $request->token), CustomException::class,'TOKEN_NEED',401);
        throw_if(!empty($user->password), CustomException::class, trans('messages.PASSWORD_CREATED_BEFORE'), 400);

        $attributes = $request->validate([
            'email' => 'required',
            'password' => 'required |string|confirmed',
            'code' => ['sometimes', 'nullable', 'exists:referrals,code'],
            'rule' => 'required'
        ]);

        $attributes['password'] = Hash::make($attributes['password']);
        $attributes['status'] = 'PRIMARY_AUTH_DONE';
        unset($attributes['rule']);

        DB::transaction(function () use ($attributes, $user) {
            $user->fill($attributes)->save();


            if (!empty($attributes['code'])) {
                $referral = Referral::where('code', $attributes['code'])
                    ->orWhere('link', $attributes['code'])
                    ->first();
                $referral->user
                    ->referringUsers()
                    ->attach($user->id);
            }
        });

        event(new UserRegisteredPasswordEvent($user));

        $token = $user->createToken('user');

        return successResponse(['token' => $token->plainTextToken], 200, ['message' => 'SUBMITTED']);
    }
}
