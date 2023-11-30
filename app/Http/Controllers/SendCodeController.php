<?php

namespace App\Http\Controllers;

use App\Events\EmailRegistered;
use App\Models\User;
use Illuminate\Http\Request;

class SendCodeController extends Controller
{
    public function store(Request $request)
    {
        $attributes = $request->validate([
            'email' => ['required', 'exists:users,email'],
        ]);

        $user = User::whereEmail($request->email)
            ->first();

        event(new EmailRegistered($user));

        return \App\Helpers\successResponse([], 200, ['message' => trans('messages.CODE_SENT')]);
    }


}
