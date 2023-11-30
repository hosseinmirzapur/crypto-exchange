<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Account;
use App\Notifications\AcceptedAuthNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function App\Helpers\current_user;
use function App\Helpers\successResponse;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = current_user()
            ->accounts()
            ->filter(['status'])
            ->get();
        return successResponse(
            $accounts,
            200,
        );
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'card_number' => 'required||string|size:16|unique:accounts,card_number',
            'sheba' => 'required||string|max:26|unique:accounts,sheba',
            'account_number' => 'required|string|unique:accounts,account_number'
        ]);
        $attributes['status'] = 'PENDING';
        $account = current_user()
            ->accounts()
            ->create($attributes);
        return successResponse(
            $account,
            200,
            ['message' => trans('messages.ACCOUNT_CREATED')]
        );
    }

    public function update(Request $request, Account $account)
    {
        $attributes = $request->validate(
            [
                'card_number' => ['required', 'string', 'size:16', Rule::unique('accounts', 'card_number')->ignore($account, 'card_number')],
                'sheba' => ['required', 'string', 'max:26', Rule::unique('accounts', 'sheba')->ignore($account, 'sheba')],
                'account_number' => ['required', 'string', Rule::unique('accounts', 'account_number')->ignore($account, 'account_number')],
            ]
        );

        $account->update($attributes + ['status' => 'PENDING']);
        $user = $account->user;
        if (!$user->accounts()->where('status', 'ACCEPTED')->exists() && $user->status === 'ACCEPTED') {
            $user->update(['status' => 'CONFIRMED_IDENTITY']);
        }

        return successResponse(
            $account,
        );
    }

    public function status(Request $request, Account $account)
    {
        $attributes = $request->validate(
            ['status' => ['required', Rule::in(Account::STATUS)]]
        );
        $account->update($attributes);
        if ($account->user->status === 'CONFIRMED_IDENTITY' && $attributes['status'] === 'ACCEPTED') {
            $account->user()->update(['status' => 'ACCEPTED']);
            $account->user->notify((new AcceptedAuthNotification()));
        }

        return successResponse(
            $account,
        );
    }

    public function destroy(Account $account)
    {
        $count = current_user()->accounts()->count();
        throw_if($count < 2, CustomException::class, trans('messages.MIN_ACCOUNT'), 400);
        if ($account->status !== 'ACCEPTED') {
            $account->forceDelete();
        } else {
            $account->delete();
        }
        return successResponse(
            [],
            200,
            ['message' => trans('messages.ACCOUNT_DELETED')]
        );
    }
}
