<?php

namespace App\Http\Controllers;

use App\Events\UpdateSettingEvent;
use App\Exceptions\CustomException;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use function App\Helpers\current_user;
use function App\Helpers\successResponse;

class SettingController extends Controller
{

    public function store(Request $request)
    {

        $validation = [
            'setting_key' => "required",
            'setting_value' => ["required", function ($attribute, $value, $fail) {
                if (\request('setting_key') === 'OTP' && !in_array($value, Setting::TWO_FACTOR)) {
                    $fail(trans('validation.in'));
                }
            }]
        ];

        $attributes = $request->validate($validation);

        if ($attributes['setting_key'] === 'OTP') {

            throw_if(!\request()->has('code'), CustomException::class,
                trans('validation.required',
                    ['attribute' => trans('validation.attributes.code')]
                )
            );

            if ($attributes['setting_value'] === 'GOOGLE') {
                $google2fa = app('pragmarx.google2fa');

                if (!$google2fa->verifyGoogle2FA($this->google_2fa ?? '', request($attributes['setting_value']))) {
                    throw new CustomException(trans('messages.WRONG_CODE'));
                }

            } else {

                $code = current_user()->getSentCode('SETTING_OTP');

                if (!isset($code) || !Hash::check(request('code'), $code)) {
                    throw new CustomException(trans('messages.WRONG_CODE'));
                }
            }
        }

        $user = current_user();
        $setting = $user->settings()
            ->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'setting_key' => $attributes['setting_key']
                ],
                $attributes
            );
        return successResponse(
            $setting,
            200,
            ['message' => trans('messages.SETTING_CONFIGURED')]
        );
    }

    public function otp(Request $request)
    {
        $attributes = $request->validate([
            'setting_key' => ['required', Rule::in(['SMS', 'EMAIL'])]
        ]);
        event(new UpdateSettingEvent(current_user(), $attributes['setting_key']));
        return successResponse();
    }

    public function index()
    {
        return successResponse(
            current_user()
                ->settings()
                ->get(),
            200,
        );
    }

    public function show($key)
    {
        return successResponse(
            current_user()->settings()->key($key)->first(),
            200,
        );
    }

    public function password(Request $request)
    {
        // todo add password_old

        $attributes = $request->validate([
            'password_old' => [
                Rule::requiredIf(isset(current_user()->password)),
                'string',
                function ($attribute, $value, $fail) {
                    if (isset(current_user()->password) && !Hash::check($value, current_user()->password)) {
                    $fail(trans('messages.WRONG_OLD_PASSWORD'));
                }
            }],
            'password' => ['required', 'confirmed', 'string']
        ]);

        $attributes['password'] = Hash::make($attributes['password']);

        current_user()->update($attributes);

        return successResponse(
            trans('messages.PASSWORD_UPDATED'),
            200,
            ['message' => trans('messages.PASSWORD_UPDATED')]
        );
    }
}
