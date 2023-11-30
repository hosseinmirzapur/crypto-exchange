<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function App\Helpers\successResponse;

class UserProfileController extends Controller
{

    public function show(User $user)
    {
        $profile = $user->profile;
        return successResponse(
            $profile,
        );
    }

    public function update(Request $request, User $user)
    {
        $attributes = $request->validate([
                'name' => 'required',
                'national_code' => [
                    'required',
                    'string',
                    'size:10',
                    Rule::unique('profiles', 'national_code')
                        ->ignore($user->profile->id)
                ],
                'mobile' => [
                    'required',
                    'string',
                    'size:11',
                    Rule::unique('profiles', 'mobile')->ignore($user->profile->id)
                ],
                'address' => 'required',
                'phone' => 'required',
            ]
        );

        $user->profile()
            ->update($attributes);

        return successResponse(
            $user,
            200,
            ['message' => trans('messages.PROFILE_UPDATED')]
        );
    }
}
