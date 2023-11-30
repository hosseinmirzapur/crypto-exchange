<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Admin;
use App\Models\Finnotech;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use function App\Helpers\current_user;
use function App\Helpers\successResponse;

class FinnotechController extends Controller
{
    public function show(User $user)
    {
        return successResponse(
            $user->finnotech()
                ->latest()
                ->firstOrFail(),
        );
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'type' => 'required',
            'items' => 'required'
        ]);

        if (current_user() instanceof Admin) {

            $finnotech = Finnotech::query()->updateOrCreate(
                [
                    'user_id' => current_user()->isAdmin() ? null : current_user()->id,
                    'type' => $attributes['type']
                ],
                [
                    'user_id' => current_user()->isAdmin() ? null : current_user()->id,
                    'type' => $attributes['type'],
                    'items' => $attributes['items']
                ],

            );
        }
        return successResponse(
            $finnotech
        );
    }

    public function login()
    {
        $user = current_user();
        if (!$user->isAdmin()) {
            $user->load('profile');
        }
        return successResponse(
            $user
        );
    }

    public function token()
    {
        if (current_user()->isAdmin()) {
            $id = \request('user');
            $finnotech = Finnotech::where('type', \request('type', 'CARD_TOKEN'))
                ->when(isset($id), function ($query) use ($id) {
                    $query->where('user_id', $id);
                })
                ->latest()
                ->first();

            throw_if(!isset($finnotech) && isset($id),
                CustomException::class,
            trans('messages.FINNOTECH_FAILED')
            );

            return successResponse(
                $finnotech,
            );
        }

        $finnotech = current_user()
            ->finnotech()
            ->where('type', \request('type', 'AUTH_CODE_REQUEST'))
            ->latest()
            ->firstOrFail();

        return successResponse(
            $finnotech,
        );
    }

    public function saveToken()
    {
        \request()->validate(
            [
                'type' => Rule::requiredIf(!current_user()->isAdmin()),
                'items' => 'required'
            ]
        );

        $finnotech = Finnotech::updateOrCreate(
            [
                'user_id' => current_user()->isAdmin() ? (\request('user_id') ?? null) : current_user()->id,
                'type' => current_user()->isAdmin() ? (\request('type') ?? 'CARD_TOKEN') : \request('type')
            ],
            [
                'user_id' => current_user()->isAdmin() ? (\request('user_id') ?? null) : current_user()->id,
                'type' => current_user()->isAdmin() ? (\request('type') ?? 'CARD_TOKEN') : \request('type'),
                'items' => \request('items')
            ]
        );

        if (!current_user()->isAdmin() && \request('type') !== 'AUTH_CODE_REQUEST') {
            if (!in_array(current_user()->status, ['SECONDARY_AUTH_DONE', 'RESEND_OTP'])) {
                throw new CustomException('messages.CAN_NOT_MODIFIED');
            }
            current_user()->update([
                'status' => 'OTP_DONE'
            ]);
        }

        return successResponse(
            $finnotech,
        );
    }
}
