<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\AcceptedAuthNotification;
use App\Notifications\RejectedAuthNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use function App\Helpers\successResponse;

class UserController extends Controller
{
    public function index()
    {
        $builder = User::filter(['email', 'is_banned', 'status', 'created_atFrom', 'created_atTo'])
            ->with(
                [
                    'profile' => function ($query) {
                        foreach (['name', 'mobile'] as $item) {
                            if (request()->has($item)) {
                                $query->where($item, request($item));
                            }
                        }
                    },
                    'credits',
                    'accounts' => function ($query) {
                        return $query->where('status', 'ACCEPTED');
                    }
                ]
            );

        if (\request()->has('export') && \request('export') === 'excel') {
            return Excel::download(
                new UsersExport(
                    $builder
                ), 'users.xlsx');
        }

        return UserResource::collection(
            $builder->paginate(
                request('size', 10)
            )
        );
    }

    public function show($user)
    {
        $scope = \request('scope', 'id');

        $user = User::where($scope, $user)
            ->firstOrFail();

        return successResponse(
            new UserResource(
                $user->load(
                    [
                        'profile',
                        'coins',
                        'accounts',
                        'settings',
                        'documents'
                    ]
                )
            ),
        );
    }

    public function ban(User $user)
    {
        $user->update(['is_banned' => 1]);
        return successResponse(trans('messages.USER_BANNED'));
    }

    public function unBan(User $user)
    {
        $user->update(['is_banned' => 0]);
        return successResponse(trans('messages.USER_UNBANNED'));
    }

    public function update(User $user, Request $request)
    {
        $attribute = $request->validate(['status' => ['required', Rule::in(User::STATUS)]]);

        if (!$user->accounts()->where('status', 'ACCEPTED')->exists() && $attribute['status'] === 'ACCEPTED') {
            $attribute['status'] = 'CONFIRMED_IDENTITY';
        }


        $user->update($attribute);
        if (isset($attribute['status'])) {
            switch ($attribute['status']) {
                case 'ACCEPTED':
                    $user->notify((new AcceptedAuthNotification()));
                    break;
                case "CONFLICT":
                case "REJECTED":
                    $user->notify((new RejectedAuthNotification()));
                    break;
            }
        }

        return successResponse(
            $user,
        );
    }

    public function count()
    {
        return successResponse(
            ['count' => User::filter(['status'])->count('id')],
            200,
        );
    }

    public function setting(User $user, Request $request)
    {
        $attributes = $request->validate(
            [
                'setting_value' => [
                    'required',
                    Rule::in(['EMAIL', 'SMS'])
                    ]
            ]
        );

        $user->settings()
            ->key('OTP')
            ->updateOrCreate(
                [
                    'setting_key' => 'OTP',
                ],
                [
                    'setting_value' => $attributes['setting_value']
                ]
            );

        return successResponse();
    }
}
