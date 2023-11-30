<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\NotifyByAdminNotification;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function store(Request $request, User $user)
    {
        $request->validate([
           'title' => 'required',
           'body' => 'required'
        ]);

        $user->notify(
            new NotifyByAdminNotification(
                $request->title,
                $request->body
            )
        );
        return \App\Helpers\successResponse(
            'MESSAGE_SENT',
            201
        );
    }
}
