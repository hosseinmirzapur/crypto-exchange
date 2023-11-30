<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class ChangePasswordController extends Controller
{
    public function index(Request $request)
    {
        $attributes = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|confirmed',
            'token' => 'required'
        ]);

        $user = User::whereEmail($attributes['email'])->firstOrFail();
        throw_if(!Password::tokenExists($user, $request->token), AuthorizationException::class);

        $attributes['password'] = Hash::make($attributes['password']);
        $user->fill($attributes)->save();
        $user->tokens()->delete();
        return \App\Helpers\successResponse($user, 200);
    }
}
