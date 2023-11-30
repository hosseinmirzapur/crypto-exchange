<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function App\Helpers\successResponse;

class UserAccountController extends Controller
{

    public function index(User $user)
    {
        $accounts = $user->accounts;
        return successResponse(
            $accounts,
            200,
        );
    }

    public function store(Request $request, User $user)
    {
        $attributes = $request->validate([
            'card_number' => 'required|size:16|unique:accounts',
            'sheba' => 'required|max:26|unique:accounts',
            'account_number' => 'required|unique:accounts',
            'status' => ['required', Rule::in(Account::STATUS)]
        ]);
        $account = $user
            ->accounts()
            ->create($attributes);
        return successResponse(
            $account,
            200,
            ['message' => trans('messages.ACCOUNT_CREATED')]
        );
    }


}
