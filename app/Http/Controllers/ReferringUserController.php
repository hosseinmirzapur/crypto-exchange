<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use function App\Helpers\current_user;

class ReferringUserController extends Controller
{
    public function index(User $user = null)
    {
        $user = $user ?? current_user();
        return UserResource::collection(
            $user->referringUsers()
                ->with(
                    [
                        'profile',
                        'credits',
                        'accounts'
                    ]
                )->paginate(10)
        )->additional(
            [
                'status' => 200,
                'message' => 'success',
                'type' => 'success'
            ]
        );

    }

    public function count()
    {
        return \App\Helpers\successResponse(
            [
                'users' => current_user()->referringUsers()
                    ->count()
            ]
        );
    }

    public function sumCommission()
    {
        return \App\Helpers\successResponse(
            ['commission' => current_user()
                ->transactions()
                ->where('type', 'REFERRAL')
                ->sum('amount')]
        );
    }
}
