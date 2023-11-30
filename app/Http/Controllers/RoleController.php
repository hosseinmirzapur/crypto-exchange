<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Http\Resources\RoleResource;
use App\Models\Ability;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function App\Helpers\successResponse;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:ability,"LIST_ROLES"')->only('index');
        $this->middleware('can:ability,"CREATE_ROLES"')->only('store');
        $this->middleware('can:ability,"UPDATE_ROLES"')->only('update');
        $this->middleware('can:ability,"DETAILS_ROLES"')->only('show');

    }

    public function index()
    {
        return RoleResource::collection(
            Role::withCount(['abilities'])
                ->paginate(\request('size', 10))
        )->additional([
            'message' => 'success',
            'type' => 'success',
            'status' => 'success',
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate(
            [
                'name' => 'required',
                'icon' => 'sometimes',
                'color' => 'sometimes',
                'label' => 'required',
                'abilities' => 'sometimes|nullable'
            ]
        );

        $attributes['status'] = 'ACTIVATED';

        $role = Role::create($attributes);

        $abilities = Ability::whereIn('name', $attributes['abilities'])
            ->pluck('id');
        $role->abilities()
            ->sync($abilities);

        return successResponse(
            $role,
            201,
            ['message' => trans('messages.SUCCESSFUL_CREATED')]
        );
    }

    public function show(Role $role)
    {
        $role->load('abilities');
        return \App\Helpers\successResponse(
            $role,
        );
    }

    public function update(Request $request, Role $role)
    {
        throw_if( Str::upper($request->name) === 'ADMIN', CustomException::class, trans('messages.CAN_NOT_MODIFIED'));

        $attributes = $request->validate([
            'name' => 'required',
            'icon' => 'required',
            'color' => 'sometimes',
            'label' => 'sometimes',
            'status' => 'required',
            'abilities' => 'sometimes|nullable'
        ]);

        $role->update($attributes);

        if ($request->has('abilities')) {
            $abilities = Ability::whereIn('name', $attributes['abilities'])
                ->pluck('id');
            $role->abilities()
                ->sync($abilities);
        }

        return successResponse(
            $role,
            200,
            ['message' => trans('messages.SUCCESSFUL_UPDATED')]
        );
    }

    public function destroy(Role $role)
    {
        return successResponse(
            $role->delete(),
            204,
        );
    }


}
