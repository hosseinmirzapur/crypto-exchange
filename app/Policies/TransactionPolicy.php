<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;


    public function viewAny(User $current_user, User $user)
    {
        return $user->is($current_user);
    }

    public function show(User $user, Transaction $transaction)
    {
        return $user->id === $transaction->user_id;
    }

    public function update(User $user, Transaction $transaction)
    {
        return $user->id === $transaction->user_id;
    }

    public function before($user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

}
