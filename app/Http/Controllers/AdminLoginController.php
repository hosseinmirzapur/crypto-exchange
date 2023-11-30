<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use function App\Helpers\current_user;
use function App\Helpers\errorResponse;
use function App\Helpers\successResponse;

class AdminLoginController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!auth('admin')->attempt($request->only('email', 'password'))) {
            return errorResponse(trans("messages.LOGIN_WRONG"));
        }

        $token = auth('admin')->user()
            ->createToken('admin')
            ->plainTextToken;
        return successResponse(
            ['token' => $token]
        );

    }

    public function destroy()
    {
        return auth()->user()->currentAccessToken()->delete();
//        auth('admin')->logout();
    }
}
