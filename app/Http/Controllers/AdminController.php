<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use function App\Helpers\current_user;
use function App\Helpers\successResponse;

class AdminController extends Controller
{
    public function index()
    {
        return AdminResource::collection(
            Admin::with(['roles'])
                ->filter(
                    [
                        'name',
                        'lastname',
                        'status',
                        'mobile',
                        'email',
                        'create_at'
                    ]
                )
                ->paginate(
                    request('size', 10)
                )
        )->additional([
            "type" => "success",
            "status" => 200,
            "message" => "success",
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => ['required', 'max:255'],
            'lastname' => ['required', 'max:255'],
            'email' => ['required', 'max:255', Rule::unique('admins', 'email')],
            'password' => ['required', 'string'],
            'mobile' => ['required', 'max:255', Rule::unique('admins', 'mobile')],
            'role' => ['required', 'exists:roles,id']
        ]);

        $attributes['status'] = 'ACTIVATED';
        $attributes['password'] = Hash::make($attributes['password']);

        $admin = Admin::create($attributes);
        $admin->roles()->sync($attributes['role']);

        return successResponse(
            $admin,
            201,
        );
    }

    public function show(Admin $admin)
    {
        return successResponse(
            $admin->load('roles'),
        );
    }

    public function update(Request $request, Admin $admin)
    {
        $validationRule = $admin->name === 'admin' ?
            [
                'lastname' => ['required', 'max:255'],
                'password' => ['string'],
                'email' => ['required', 'max:255', Rule::unique('admins', 'email')->ignore($admin)],
                'mobile' => ['required', 'max:255', Rule::unique('admins', 'mobile')->ignore($admin), 'size:11'],
            ] :
            [
                'name' => ['required', 'max:255'],
                'lastname' => ['required', 'max:255'],
                'password' => ['string'],
                'email' => ['required', 'max:255', Rule::unique('admins', 'email')->ignore($admin)],
                'mobile' => ['required', 'max:255', Rule::unique('admins', 'mobile')->ignore($admin), 'size:11'],
                'status' => ['required', Rule::in(Admin::STATUS)],
                'role' => ['required', 'exists:roles,id']
            ];

        $attributes = $request->validate($validationRule);

        if (empty($attributes['password'])) {
            unset($attributes['password']);
        } else {
            $attributes['password'] = Hash::make($attributes['password']);
        }

//        throw_if($admin->name === 'admin', CustomException::class, trans('messages.CAN_NOT_MODIFIED'), 400);

        $admin->update($attributes);

        if (isset($attributes['role'])) {
            $admin->roles()
                ->sync($attributes['role']);
        }

        return successResponse(
            $admin,
        );

    }

    public function destroy(Admin $admin)
    {
        return successResponse(
            $admin->delete(),
            204,
        );
    }

    public function init()
    {
        return successResponse(
            current_user()
                ->with(['roles.abilities'])
                ->first()
        );
    }

    public function activate(Admin $admin)
    {
        return successResponse(
            $admin->update(
                ['status' => 'ACTIVATED']
            )
        );
    }

    public function deactivate(Admin $admin)
    {
        return successResponse(
            $admin->update(
                ['status' => 'DISABLED']
            )
        );
    }


}
