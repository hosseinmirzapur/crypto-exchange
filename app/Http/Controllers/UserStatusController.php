<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AcceptedAuthNotification;
use App\Notifications\RejectedAuthNotification;
use Illuminate\Support\Facades\DB;
use function App\Helpers\successResponse;

class UserStatusController extends Controller
{
    public function accept(User $user)
    {
        DB::transaction(function () use ($user) {
            if ($user->accounts()->where('status', 'ACCEPTED')->exists()) {
                $user->update(['status' => 'ACCEPTED']);
                $user->notify((new AcceptedAuthNotification()));
            } else {
                $user->update(['status' => "CONFIRMED_IDENTITY"]);
            }

            if (!$user->referral()->exists()) {
                $user->addReferral();
            }
        });

        return successResponse(
            trans('messages.success'),
        );
    }

    public function reject(User $user)
    {
        $user->reject();
        $user->notify((new RejectedAuthNotification()));
        return successResponse(
            trans('messages.success'),
        );
    }
}
