<?php

namespace App\Http\Controllers;

use App\Events\SendOtpEvent;
use App\Models\User;
use Illuminate\Http\Request;
use function App\Helpers\successResponse;

class ResendOptController extends Controller
{
    public function index(Request $request)
    {
        $attributes = $request->validate(['email' => 'required|email']);

        $user = User::whereEmail($attributes['email'])
            ->firstOrFail();

        event(new SendOtpEvent($user));

        return successResponse(
            [],
            200,
            ['message' => trans('messages.CODE_SENT')]
        );

    }
}
