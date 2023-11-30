<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use function App\Helpers\current_user;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = request()->has('all') ?
            current_user()->notifications() :
            current_user()->unreadNotifications();
        return NotificationResource::collection(
            $notifications->paginate(5)

        )->additional([
            'message' => trans('messages.SUCCESS'),
            'type' => 'success',
            'status' => 'success',
        ]);
    }

    public function patch(Notification $notification)
    {
        $notification->update(['read_at' => now()]);

        return \App\Helpers\successResponse();
    }

    public function count()
    {
        return \App\Helpers\successResponse(
            [
                'count' => current_user()->unreadNotifications()
                    ->count()
            ]
        );
    }

}
