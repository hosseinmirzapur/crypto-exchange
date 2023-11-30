<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthenticationRequest;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function App\Helpers\current_user;
use function App\Helpers\successResponse;

class AuthenticationController extends Controller
{
    public function index()
    {
        /** @var Profile $profile */
        $profile = current_user()->profile;
        return successResponse(
            isset($profile) ?
                $profile->append('persian_birthday') :
                ''
        );
    }

    public function store(AuthenticationRequest $request)
    {
        $profile = current_user()
            ->profile()
            ->updateOrCreate(['user_id' => auth()->id()], $request->validated());
        current_user()->update(['status' => 'SECONDARY_AUTH_DONE']);
        $profileArray = $profile->toArray();
        return successResponse(
            array_merge($profileArray, ['user_status' => current_user()->status]),
            200,
            ['message' => trans('messages.PROFILE_CREATED')]
        );
    }

    public function update(Request $request)
    {
        $profile = Profile::where('user_id', auth()->id())
            ->firstOrFail();

        if (current_user()->status === 'CONFLICT' || current_user()->status === 'REJECTED') {
            $attributes = $request->validate([
                'name' => 'required',
                'national_code' => ['required','string','size:10',Rule::unique('profiles','national_code')->ignore($profile->id)],
                'birthday' => 'required',
                'mobile' => ['required','string','size:11',  Rule::unique('profiles','mobile')->ignore($profile->id)],
                'address' => 'required',
                'phone' => 'required',
            ]);
        } else {
            $attributes = $request->validate([
                'mobile' => ['required','string','size:11', Rule::unique('profiles','mobile')->ignore($profile)],
                'address' => 'required',
                'phone' => 'required',
            ]);
        }


        $profile->update(
            $attributes
        );

        if (!$profile->wasChanged('mobile') && $profile->user->status !== 'RESEND_OTP') {
            $profile->user()
                ->update(['status' => 'OTP_DONE']);
        } else {
            $profile->user()
                ->update(['status' => 'RESEND_OTP']);
        }

        $profileArray = $profile->toArray();
        return successResponse(
            array_merge($profileArray, ['user_status' => current_user()->status]),
            200,
            ['message' => trans('messages.PROFILE_UPDATED')]
        );
    }

}
