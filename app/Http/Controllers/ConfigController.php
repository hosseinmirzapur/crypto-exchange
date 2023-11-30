<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function show()
    {
        return \App\Helpers\successResponse(
            Config::typeOf(request('type', 'MAIN'))
                ->key(request('key'))
                ->firstOrFail()
        );
    }

    public function main()
    {
        return \App\Helpers\successResponse(
            Config::typeOf('MAIN')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $attributes = $request->validate(
            [
                'type' => 'required',
                'key' => 'required',
                'value' => 'required'
            ]);
        return \App\Helpers\successResponse(
            Config::updateOrCreate(
                [
                    'type' => $attributes['type'],
                    'key' => $attributes['key']
                ],
                [
                    'type' => $attributes['type'],
                    'key' => $attributes['key'],
                    'value' => $attributes['value']
                ]
            ),
        );
    }
}
