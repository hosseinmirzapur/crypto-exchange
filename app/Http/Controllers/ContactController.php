<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'user:admin'])
            ->only('update');
    }

    public function index()
    {
        return ContactResource::collection(
            Contact::filter(['name', 'is_seen', 'email', 'created_atFrom', 'created_atTo'])
                ->paginate(
                    \request('size', 10)
                )
        )->additional(
            [
                'message' => 'success',
                'type' => 'success',
                'status' => '200'
            ]
        );

    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => 'required',
            'email' => ['required', 'email'],
            'description' => ['required', 'max:255'],
        ]);
        $attributes['is_seen'] = true;
        return \App\Helpers\successResponse(
            Contact::create($attributes),
            201,
            ['message' => trans('messages.success')]
        );
    }

    public function update(Contact $contact)
    {
        $contact->toggle();
        return \App\Helpers\successResponse(
            $contact,
            200,
            ['message' => trans('messages.success')]
        );
    }
}
